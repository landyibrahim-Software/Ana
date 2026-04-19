<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
return new class extends Migration
{
    /**
     * Add indexes to all critical tables so queries stay fast
     * even when the database grows to thousands of rows.
     *
     * Every index is wrapped in a try/catch so the migration is
     * safe to run even if some indexes were added manually before.
     */
    public function up(): void
    {
        // ----------------------------------------------------------
        // orders table
        // ----------------------------------------------------------
        Schema::table('orders', function (Blueprint $table) {
            try { $table->index('customer_id',   'idx_orders_customer_id');   } catch (\Exception $e) {}
            try { $table->index('order_status',  'idx_orders_order_status');  } catch (\Exception $e) {}
            try { $table->index('payment_status','idx_orders_payment_status');} catch (\Exception $e) {}
            try { $table->index('created_at',    'idx_orders_created_at');    } catch (\Exception $e) {}
            try { $table->index('due',           'idx_orders_due');           } catch (\Exception $e) {}
        });
        // ----------------------------------------------------------
        // orderdetails table  (the most-queried join table)
        // ----------------------------------------------------------
        Schema::table('orderdetails', function (Blueprint $table) {
            try { $table->index('order_id',   'idx_orderdetails_order_id');   } catch (\Exception $e) {}
            try { $table->index('product_id', 'idx_orderdetails_product_id'); } catch (\Exception $e) {}
        });
        // ----------------------------------------------------------
        // products table
        // ----------------------------------------------------------
        Schema::table('products', function (Blueprint $table) {
            try { $table->index('category_id',    'idx_products_category_id');    } catch (\Exception $e) {}
            try { $table->index('supplier_id',    'idx_products_supplier_id');    } catch (\Exception $e) {}
            try { $table->index('product_store',  'idx_products_product_store');  } catch (\Exception $e) {}
            try { $table->index('product_code',   'idx_products_product_code');   } catch (\Exception $e) {}
            try { $table->index('product_name',   'idx_products_product_name');   } catch (\Exception $e) {}
        });
        // ----------------------------------------------------------
        // customers table
        // ----------------------------------------------------------
        Schema::table('customers', function (Blueprint $table) {
            try { $table->index('phone', 'idx_customers_phone'); } catch (\Exception $e) {}
            try { $table->index('name',  'idx_customers_name');  } catch (\Exception $e) {}
        });
        // ----------------------------------------------------------
        // supplier_payments table (used in dashboard date-range)
        // ----------------------------------------------------------
        if (Schema::hasTable('supplier_payments')) {
            Schema::table('supplier_payments', function (Blueprint $table) {
                try { $table->index('payment_date', 'idx_supplier_payments_date');        } catch (\Exception $e) {}
                try { $table->index('supplier_id',  'idx_supplier_payments_supplier_id'); } catch (\Exception $e) {}
            });
        }
        // ----------------------------------------------------------
        // expenses table (used in dashboard date-range)
        // ----------------------------------------------------------
        if (Schema::hasTable('expenses')) {
            Schema::table('expenses', function (Blueprint $table) {
                try { $table->index('created_at', 'idx_expenses_created_at'); } catch (\Exception $e) {}
            });
        }
    }
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            foreach (['idx_orders_customer_id','idx_orders_order_status','idx_orders_payment_status','idx_orders_created_at','idx_orders_due'] as $idx) {
                try { $table->dropIndex($idx); } catch (\Exception $e) {}
            }
        });
        Schema::table('orderdetails', function (Blueprint $table) {
            foreach (['idx_orderdetails_order_id','idx_orderdetails_product_id'] as $idx) {
                try { $table->dropIndex($idx); } catch (\Exception $e) {}
            }
        });
        Schema::table('products', function (Blueprint $table) {
            foreach (['idx_products_category_id','idx_products_supplier_id','idx_products_product_store','idx_products_product_code','idx_products_product_name'] as $idx) {
                try { $table->dropIndex($idx); } catch (\Exception $e) {}
            }
        });
        Schema::table('customers', function (Blueprint $table) {
            foreach (['idx_customers_phone','idx_customers_name'] as $idx) {
                try { $table->dropIndex($idx); } catch (\Exception $e) {}
            }
        });
        if (Schema::hasTable('supplier_payments')) {
            Schema::table('supplier_payments', function (Blueprint $table) {
                foreach (['idx_supplier_payments_date','idx_supplier_payments_supplier_id'] as $idx) {
                    try { $table->dropIndex($idx); } catch (\Exception $e) {}
                }
            });
        }
        if (Schema::hasTable('expenses')) {
            Schema::table('expenses', function (Blueprint $table) {
                try { $table->dropIndex('idx_expenses_created_at'); } catch (\Exception $e) {}
            });
        }
    }
};
