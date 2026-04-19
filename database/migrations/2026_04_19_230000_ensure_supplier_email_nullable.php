<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The original suppliers migration defines `email` as NOT NULL with no default.
 * However the StoreSupplier form never submits an email value, so the column
 * was often absent from databases built from older project versions.
 *
 * This migration adds a nullable `email` column if one does not already exist,
 * which prevents the AllSupplier SELECT from throwing "Unknown column 'email'".
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('suppliers', 'email')) {
            Schema::table('suppliers', function (Blueprint $table) {
                $table->string('email')->nullable()->after('name');
            });
        }
    }

    public function down(): void
    {
        // Only drop the column if it is nullable (i.e. added by this migration).
        // Columns that were already NOT NULL pre-date this migration and should
        // not be removed on rollback.
        if (Schema::hasColumn('suppliers', 'email')) {
            Schema::table('suppliers', function (Blueprint $table) {
                $table->dropColumn('email');
            });
        }
    }
};
