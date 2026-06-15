<?php

/**
 * Global function to check user permissions
 * 
 * @param string $rule_name Rule name (e.g., 'ctr__users/act__edit' or 'delete_docs')
 * @return bool
 */
function get_rule($rule_name)
{
    global $connection;
    static $permissions_cache = null;
    static $group_hierarchy = null;

    // 1. Super Admin Bypass
    if (isset($_SESSION['users_group_id']) && $_SESSION['users_group_id'] == 1) {
        return true;
    }
    
    if (isset($_SESSION['sh_login']) && $_SESSION['sh_login'] === 'admin') {
        return true;
    }

    // If no user is logged in, deny access
    if (!isset($_SESSION['users_group_id'])) {
        return false;
    }

    $group_id = (int)$_SESSION['users_group_id'];

    // 2. Load group hierarchy (Lazy Loading)
    if ($group_hierarchy === null) {
        $group_hierarchy = [];
        $result = $connection->query("SELECT users_group_id, parent_users_group_id FROM users_group WHERE del=0");
        while ($row = $result->fetch_assoc()) {
            $group_hierarchy[$row['users_group_id']] = $row['parent_users_group_id'];
        }
    }

    // 3. Load permissions into cache (Lazy Loading)
    if ($permissions_cache === null) {
        $permissions_cache = [];
        
        // Fetch all rules for all groups
        $result = $connection->query("SELECT users_group_id, rule, access FROM users_group_rules");
        while ($row = $result->fetch_assoc()) {
            if (!isset($permissions_cache[$row['users_group_id']])) {
                $permissions_cache[$row['users_group_id']] = [];
            }
            $permissions_cache[$row['users_group_id']][$row['rule']] = (int)$row['access'];
        }
    }

    // Normalize the input rule_name (remove prefixes if present)
    $rule_normalized = $rule_name;
    if (strpos($rule_name, '/') !== false) {
        list($ctr_part, $act_part) = explode('/', $rule_name, 2);
        $ctr_clean = str_replace('ctr__', '', $ctr_part);
        $act_clean = str_replace('act__', '', $act_part);
        $rule_normalized = $ctr_clean . '/' . $act_clean;
    } elseif (strpos($rule_name, 'ctr__') === 0) {
        $rule_normalized = str_replace('ctr__', '', $rule_name);
    }

    // 4. Check permissions with hierarchy
    $current_group = $group_id;
    $checked_groups = [];
    
    while ($current_group !== null && !in_array($current_group, $checked_groups)) {
        $checked_groups[] = $current_group;
        
        // Check exact match for current group
        if (isset($permissions_cache[$current_group][$rule_normalized])) {
            return (bool)$permissions_cache[$current_group][$rule_normalized];
        }
        
        // Check wildcard match for current group
        if (strpos($rule_normalized, '/') !== false) {
            list($ctr, $act) = explode('/', $rule_normalized, 2);
            $wildcard_rule = $ctr . '/*';
            
            if (isset($permissions_cache[$current_group][$wildcard_rule])) {
                return (bool)$permissions_cache[$current_group][$wildcard_rule];
            }
        }
        
        // Move to parent group
        $current_group = isset($group_hierarchy[$current_group]) ? $group_hierarchy[$current_group] : null;
        if ($current_group == 0) $current_group = null; // 0 means no parent
    }

    // 5. Default Deny
    return false;
}

/**
 * Alias for get_rule to check specific controller/action
 * @param string $ctr Controller name
 * @param string $act Action name
 * @return bool
 */
function checkrule($ctr, $act)
{
    return get_rule($ctr . '/' . $act);
}
