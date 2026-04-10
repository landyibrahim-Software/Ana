<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orderdetails', function (Blueprint $table) {
            // Add meters column if it doesn't exist
            if (!Schema::hasColumn('orderdetails', 'meters')) {
                $table->decimal('meters', 10, 2)->nullable()->default(0)->after('unitcost');
            }
            
            // Add selected_colors column if it doesn't exist
            if (!Schema::hasColumn('orderdetails', 'selected_colors')) {
                $table->longText('selected_colors')->nullable()->after('meters');
            }
            
            // Add metter_price column if it doesn't exist
            if (!Schema::hasColumn('orderdetails', 'metter_price')) {
                $table->decimal('metter_price', 10, 2)->nullable()->default(0)->after('selected_colors');
            }
            
            // Add total column if it doesn't exist (for row total)
            if (!Schema::hasColumn('orderdetails', 'total')) {
                $table->decimal('total', 10, 2)->nullable()->default(0)->after('metter_price');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orderdetails', function (Blueprint $table) {
            // Drop columns if they exist
            if (Schema::hasColumn('orderdetails', 'meters')) {
                $table->dropColumn('meters');
            }
            if (Schema::hasColumn('orderdetails', 'selected_colors')) {
                $table->dropColumn('selected_colors');
            }
            if (Schema::hasColumn('orderdetails', 'metter_price')) {
                $table->dropColumn('metter_price');
            }
            if (Schema::hasColumn('orderdetails', 'total')) {
                $table->dropColumn('total');
            }
        });
    }
};