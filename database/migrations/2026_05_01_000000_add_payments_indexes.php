<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add indexes to the payments table.
     * The payments table is queried by customer_id + payment_status on every
     * ShowCustomer and PaymentCustomer request, and by payment_date on the
     * dashboard date-range aggregation, but had no indexes yet.
     */
    public function up(): void
    {
        if (Schema::hasTable('payments')) {
            Schema::table('payments', function (Blueprint $table) {
                // Single-column indexes
                try { $table->index('customer_id',     'idx_payments_customer_id');     } catch (\Exception $e) {}
                try { $table->index('payment_status',  'idx_payments_payment_status');  } catch (\Exception $e) {}
                try { $table->index('payment_date',    'idx_payments_payment_date');    } catch (\Exception $e) {}
                // Composite index for the most common filter pattern:
                // WHERE customer_id = ? AND payment_status = 'completed'
                try { $table->index(['customer_id', 'payment_status'], 'idx_payments_customer_status'); } catch (\Exception $e) {}
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('payments')) {
            Schema::table('payments', function (Blueprint $table) {
                foreach ([
                    'idx_payments_customer_id',
                    'idx_payments_payment_status',
                    'idx_payments_payment_date',
                    'idx_payments_customer_status',
                ] as $idx) {
                    try { $table->dropIndex($idx); } catch (\Exception $e) {}
                }
            });
        }
    }
};
