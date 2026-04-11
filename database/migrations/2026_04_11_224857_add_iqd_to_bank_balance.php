<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bank_balance', function (Blueprint $table) {
            // Add IQD balance column if it doesn't exist
            if (!Schema::hasColumn('bank_balance', 'total_balance_iqd')) {
                $table->decimal('total_balance_iqd', 15, 2)->default(0.00);
            }
        });

        Schema::table('bank_transactions', function (Blueprint $table) {
            // Add currency column if it doesn't exist
            if (!Schema::hasColumn('bank_transactions', 'currency')) {
                $table->string('currency', 3)->default('USD');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bank_balance', function (Blueprint $table) {
            if (Schema::hasColumn('bank_balance', 'total_balance_iqd')) {
                $table->dropColumn('total_balance_iqd');
            }
        });

        Schema::table('bank_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('bank_transactions', 'currency')) {
                $table->dropColumn('currency');
            }
        });
    }
};