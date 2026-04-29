<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Add opening_balance to customers.
     *
     * opening_balance stores the customer's debt that existed BEFORE
     * any orders were created in this system (i.e., the balance entered
     * manually when the customer account was created).
     *
     * For existing customers we backfill it as:
     *   opening_balance = MAX(0, due - (ordersTotal - ordersPaid - paymentsPaid))
     *
     * This is only accurate if customers.due is currently correct.
     * Customers whose due was already zeroed by a previous cancelOrder bug
     * will need their opening_balance corrected manually.
     */
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'opening_balance')) {
                $table->decimal('opening_balance', 16, 2)->default(0)->after('due');
            }
        });

        // Backfill: opening_balance = MAX(0, due - net_from_orders)
        // net_from_orders = ordersTotal - ordersPaid - paymentsPaid
        DB::statement("
            UPDATE customers c
            SET c.opening_balance = GREATEST(0,
                CAST(c.due AS DECIMAL(16,2))
                - COALESCE((
                    SELECT SUM(CAST(sub_total AS DECIMAL(16,2)))
                    FROM orders
                    WHERE customer_id = c.id AND order_status != 'cancelled'
                  ), 0)
                + COALESCE((
                    SELECT SUM(CAST(pay AS DECIMAL(16,2)))
                    FROM orders
                    WHERE customer_id = c.id AND order_status != 'cancelled'
                  ), 0)
                + COALESCE((
                    SELECT SUM(CAST(payment_amount AS DECIMAL(16,2)))
                    FROM payments
                    WHERE customer_id = c.id AND payment_status = 'completed'
                  ), 0)
            )
        ");
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'opening_balance')) {
                $table->dropColumn('opening_balance');
            }
        });
    }
};
