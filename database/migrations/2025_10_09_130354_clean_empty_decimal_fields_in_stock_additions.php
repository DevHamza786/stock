<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Convert empty strings to NULL for decimal fields in stock_additions
        DB::table('stock_additions')->whereRaw("length = ''")->update(['length' => null]);
        DB::table('stock_additions')->whereRaw("height = ''")->update(['height' => null]);
        DB::table('stock_additions')->whereRaw("weight = ''")->update(['weight' => null]);
        DB::table('stock_additions')->whereRaw("diameter = ''")->update(['diameter' => null]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse this data cleanup
    }
};
