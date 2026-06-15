<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class Ctr__Test extends Controller
{
    public function act__index()
    {
        $subdomain = request()->attributes->get('tenant_subdomain');
        $database = DB::connection()->getDatabaseName();
        
        return "Core Test Controller working!<br>Subdomain: {$subdomain}<br>Database: {$database}";
    }

    public function act__db()
    {
        try {
            $tables = DB::select('SHOW TABLES');
            return response()->json([
                'status' => 'success',
                'database' => DB::connection()->getDatabaseName(),
                'tables_count' => count($tables)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
