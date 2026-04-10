<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add code_id column if it doesn't exist
        if (!Schema::hasColumn('products', 'code_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->unsignedBigInteger('code_id')->nullable()->after('category_id');
                $table->foreign('code_id')->references('id')->on('codes')->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        // Safe rollback - only drop if exists
        if (Schema::hasColumn('products', 'code_id')) {
            Schema::table('products', function (Blueprint $table) {
                // Try to drop foreign key if it exists
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $foreignKeys = $sm->listTableForeignKeys('products');
                foreach ($foreignKeys as $fk) {
                    if ($fk->getLocalColumns() == ['code_id']) {
                        $table->dropForeign(['code_id']);
                        break;
                    }
                }
                $table->dropColumn('code_id');
            });
        }
    }
};