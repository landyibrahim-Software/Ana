<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Merge previous_due into due column and remove previous_due from all tables
     */
    public function up(): void
    {
        try {
            // ✅ STEP 1: Update ORDERS table - Add previous_due to due
            DB::table('orders')
                ->update([
                    'due' => DB::raw('CAST(due AS DECIMAL(10,2)) + CAST(COALESCE(previous_due, 0) AS DECIMAL(10,2))')
                ]);
            
            \Log::info('✅ Orders table: Merged previous_due into due');

        } catch (\Exception $e) {
            \Log::error('❌ Error updating orders table: ' . $e->getMessage());
            throw $e;
        }

        try {
            // ✅ STEP 2: Update CUSTOMERS table - Add previous_due to due
            DB::table('customers')
                ->update([
                    'due' => DB::raw('CAST(due AS DECIMAL(10,2)) + CAST(COALESCE(previous_due, 0) AS DECIMAL(10,2))')
                ]);
            
            \Log::info('✅ Customers table: Merged previous_due into due');

        } catch (\Exception $e) {
            \Log::error('❌ Error updating customers table: ' . $e->getMessage());
            throw $e;
        }

        try {
            // ✅ STEP 3: Update ORDERS_ARCHIVE table - Add previous_due to due
            if (Schema::hasTable('orders_archive')) {
                DB::table('orders_archive')
                    ->update([
                        'due' => DB::raw('CAST(due AS DECIMAL(10,2)) + CAST(COALESCE(previous_due, 0) AS DECIMAL(10,2))')
                    ]);
                
                \Log::info('✅ Orders archive table: Merged previous_due into due');
            }

        } catch (\Exception $e) {
            \Log::error('❌ Error updating orders_archive table: ' . $e->getMessage());
            throw $e;
        }

        try {
            // ✅ STEP 4: Drop previous_due column from ORDERS table
            Schema::table('orders', function (Blueprint $table) {
                if (Schema::hasColumn('orders', 'previous_due')) {
                    $table->dropColumn('previous_due');
                }
            });
            
            \Log::info('✅ Orders table: Dropped previous_due column');

        } catch (\Exception $e) {
            \Log::error('❌ Error dropping previous_due from orders: ' . $e->getMessage());
            throw $e;
        }

        try {
            // ✅ STEP 5: Drop previous_due column from CUSTOMERS table
            Schema::table('customers', function (Blueprint $table) {
                if (Schema::hasColumn('customers', 'previous_due')) {
                    $table->dropColumn('previous_due');
                }
            });
            
            \Log::info('✅ Customers table: Dropped previous_due column');

        } catch (\Exception $e) {
            \Log::error('❌ Error dropping previous_due from customers: ' . $e->getMessage());
            throw $e;
        }

        try {
            // ✅ STEP 6: Drop previous_due column from ORDERS_ARCHIVE table
            if (Schema::hasTable('orders_archive')) {
                Schema::table('orders_archive', function (Blueprint $table) {
                    if (Schema::hasColumn('orders_archive', 'previous_due')) {
                        $table->dropColumn('previous_due');
                    }
                });
                
                \Log::info('✅ Orders archive table: Dropped previous_due column');
            }

        } catch (\Exception $e) {
            \Log::error('❌ Error dropping previous_due from orders_archive: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     * If you rollback, this restores the previous_due column
     */
    public function down(): void
    {
        try {
            // ✅ Restore previous_due column to ORDERS table
            Schema::table('orders', function (Blueprint $table) {
                if (!Schema::hasColumn('orders', 'previous_due')) {
                    $table->decimal('previous_due', 10, 2)->nullable()->default(0)->after('due');
                }
            });
            
            \Log::info('✅ Rollback: Restored previous_due column to orders');

        } catch (\Exception $e) {
            \Log::error('❌ Error restoring previous_due to orders: ' . $e->getMessage());
            throw $e;
        }

        try {
            // ✅ Restore previous_due column to CUSTOMERS table
            Schema::table('customers', function (Blueprint $table) {
                if (!Schema::hasColumn('customers', 'previous_due')) {
                    $table->decimal('previous_due', 10, 2)->nullable()->default(0)->after('due');
                }
            });
            
            \Log::info('✅ Rollback: Restored previous_due column to customers');

        } catch (\Exception $e) {
            \Log::error('❌ Error restoring previous_due to customers: ' . $e->getMessage());
            throw $e;
        }

        try {
            // ✅ Restore previous_due column to ORDERS_ARCHIVE table
            if (Schema::hasTable('orders_archive')) {
                Schema::table('orders_archive', function (Blueprint $table) {
                    if (!Schema::hasColumn('orders_archive', 'previous_due')) {
                        $table->decimal('previous_due', 10, 2)->nullable()->default(0)->after('due');
                    }
                });
                
                \Log::info('✅ Rollback: Restored previous_due column to orders_archive');
            }

        } catch (\Exception $e) {
            \Log::error('❌ Error restoring previous_due to orders_archive: ' . $e->getMessage());
            throw $e;
        }
    }
};