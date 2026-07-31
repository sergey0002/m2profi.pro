<?php
// Mock session
$_SESSION = [];
$_SESSION['users_group_id'] = 2; // Test group

// Mock DB Connection
class MockResult {
    private $data;
    private $index = 0;
    public function __construct($data) { $this->data = $data; }
    public function fetch_assoc() {
        if ($this->index < count($this->data)) {
            return $this->data[$this->index++];
        }
        return null;
    }
}

class MockStmt {
    private $result;
    public function __construct($result) { $this->result = $result; }
    public function bind_param($types, ...$vars) {}
    public function execute() {}
    public function get_result() { return $this->result; }
    public function close() {}
}

class MockConnection {
    public $queries = [];
    public function prepare($query) {
        $this->queries[] = $query;
        // Return mock data based on query
        if (strpos($query, 'SELECT rule, access') !== false) {
            return new MockStmt(new MockResult([
                ['rule' => 'ctr__users/act__edit', 'access' => 1],
                ['rule' => 'ctr__posts/*', 'access' => 1],
                ['rule' => 'delete_docs', 'access' => 0]
            ]));
        }
        return new MockStmt(new MockResult([]));
    }
}

$connection = new MockConnection();

// Include the function
require_once __DIR__ . '/../fw/functions/permissions.php';

// Test Cases
echo "Testing RBAC Logic...\n";

// 1. Test Exact Match (Allowed)
$res = get_rule('ctr__users/act__edit');
echo "Test 1 (Exact Match Allowed): " . ($res ? "PASS" : "FAIL") . "\n";

// 2. Test Exact Match (Denied)
$res = get_rule('delete_docs');
echo "Test 2 (Exact Match Denied): " . (!$res ? "PASS" : "FAIL") . "\n";

// 3. Test Wildcard Match
$res = get_rule('ctr__posts/act__delete');
echo "Test 3 (Wildcard Match): " . ($res ? "PASS" : "FAIL") . "\n";

// 4. Test Default Deny
$res = get_rule('ctr__unknown/act__index');
echo "Test 4 (Default Deny): " . (!$res ? "PASS" : "FAIL") . "\n";

// 5. Test Super Admin
$_SESSION['users_group_id'] = 1;
// Reset cache (hacky, but needed since function uses static cache)
// In a real test we might need a way to reset, but for now we just check if it returns true immediately
// Actually, static cache persists, but super admin check is BEFORE cache check.
$res = get_rule('delete_docs'); // Should be true for admin even if 0 in DB (though DB mock isn't hit)
echo "Test 5 (Super Admin): " . ($res ? "PASS" : "FAIL") . "\n";

// 6. Test checkrule alias
$res = checkrule('ctr__users', 'act__edit');
echo "Test 6 (checkrule alias): " . ($res ? "PASS" : "FAIL") . "\n";

echo "Done.\n";
