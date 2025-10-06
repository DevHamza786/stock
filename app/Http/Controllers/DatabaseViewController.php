<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseViewController extends Controller
{
    /**
     * Display the database viewer dashboard
     */
    public function index()
    {
        $tables = $this->getAllTables();

        return view('database-viewer.index', compact('tables'));
    }

    /**
     * Get all tables in the database
     */
    public function getAllTables()
    {
        $tables = [];

        try {
            // Get all table names
            $tableNames = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");

            foreach ($tableNames as $table) {
                $tableName = $table->name;

                // Get column information
                $columns = DB::select("PRAGMA table_info($tableName)");

                // Get row count
                $rowCount = DB::table($tableName)->count();

                // Get sample data (first 5 rows)
                $sampleData = DB::table($tableName)->limit(5)->get();

                $tables[] = [
                    'name' => $tableName,
                    'columns' => $columns,
                    'row_count' => $rowCount,
                    'sample_data' => $sampleData
                ];
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Error fetching tables: ' . $e->getMessage());
            $tables = [];
        }

        return $tables;
    }

    /**
     * View specific table data
     */
    public function viewTable(Request $request, $tableName)
    {
        $search = $request->get('search', '');
        $page = $request->get('page', 1);
        $perPage = 20;

        try {
            // Get column information
            $columns = DB::select("PRAGMA table_info($tableName)");

            // Build query
            $query = DB::table($tableName);

            // Apply search if provided
            if (!empty($search)) {
                $columnNames = array_column($columns, 'name');
                $query->where(function($q) use ($search, $columnNames) {
                    foreach ($columnNames as $column) {
                        $q->orWhere($column, 'LIKE', "%$search%");
                    }
                });
            }

            // Get total count for pagination
            $totalCount = $query->count();

            // Get paginated data
            $data = $query->skip(($page - 1) * $perPage)
                         ->take($perPage)
                         ->get();

            // Calculate pagination
            $totalPages = ceil($totalCount / $perPage);

            return view('database-viewer.table-view', compact(
                'tableName',
                'columns',
                'data',
                'search',
                'page',
                'totalPages',
                'totalCount',
                'perPage'
            ));

        } catch (\Exception $e) {
            return redirect()->route('database-viewer.index')
                           ->with('error', 'Error viewing table: ' . $e->getMessage());
        }
    }

    /**
     * Execute custom SQL query
     */
    public function executeQuery(Request $request)
    {
        $query = $request->input('sql_query', '');
        $results = [];
        $columns = [];
        $error = null;

        if (empty($query)) {
            return response()->json([
                'success' => false,
                'error' => 'Please enter a SQL query'
            ]);
        }

        try {
            // Only allow SELECT queries for security
            if (!preg_match('/^\s*SELECT\s+/i', $query)) {
                throw new \Exception('Only SELECT queries are allowed for security reasons');
            }

            $results = DB::select($query);

            if (!empty($results)) {
                $columns = array_keys((array) $results[0]);
            }

            return response()->json([
                'success' => true,
                'results' => $results,
                'columns' => $columns,
                'row_count' => count($results)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get table structure/schema
     */
    public function getTableSchema($tableName)
    {
        try {
            // Get column information
            $columns = DB::select("PRAGMA table_info($tableName)");

            // Get foreign key information
            $foreignKeys = DB::select("PRAGMA foreign_key_list($tableName)");

            // Get indexes
            $indexes = DB::select("PRAGMA index_list($tableName)");

            // Get table creation SQL
            $createSQL = DB::select("SELECT sql FROM sqlite_master WHERE type='table' AND name=?", [$tableName]);

            return response()->json([
                'success' => true,
                'columns' => $columns,
                'foreign_keys' => $foreignKeys,
                'indexes' => $indexes,
                'create_sql' => $createSQL[0]->sql ?? null
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Export table data to CSV
     */
    public function exportTable($tableName)
    {
        try {
            $data = DB::table($tableName)->get();
            $columns = Schema::getColumnListing($tableName);

            $filename = $tableName . '_export_' . date('Y-m-d_H-i-s') . '.csv';

            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function() use ($data, $columns) {
                $file = fopen('php://output', 'w');

                // Write headers
                fputcsv($file, $columns);

                // Write data
                foreach ($data as $row) {
                    fputcsv($file, (array) $row);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Export failed: ' . $e->getMessage());
        }
    }
}
