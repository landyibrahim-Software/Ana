<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateOrdersArchiveTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // ✅ Create archive table with EXACT same columns as orders
        Schema::create('orders_archive', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedInteger('customer_id');
            $table->string('order_date');
            $table->string('order_status');
            $table->string('total_products');
            $table->decimal('sub_total', 10, 2)->nullable()->default(0);
            $table->string('invoice_no')->nullable();
            $table->decimal('total', 10, 2)->nullable()->default(0);
            $table->string('payment_status')->nullable();
            $table->decimal('pay', 10, 2)->nullable()->default(0);
            $table->decimal('due', 10, 2)->nullable()->default(0);
            $table->decimal('previous_due', 10, 2)->nullable()->default(0);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->decimal('total_returned', 15, 2)->nullable()->default(0);
            $table->string('refund_status')->nullable()->default('none');

            // Add indexes for faster queries
            $table->index('customer_id');
            $table->index('order_date');
            $table->index('order_status');
            $table->index('payment_status');
        });

        // ✅ Check if there are old records to archive
        $oldOrdersCount = DB::table('orders')
            ->whereRaw("STR_TO_DATE(order_date, '%Y-%m-%d') < DATE_SUB(NOW(), INTERVAL 2 YEAR)")
            ->count();

        if ($oldOrdersCount > 0) {
            // ✅ Move old orders to archive
            DB::statement("
                INSERT INTO orders_archive 
                SELECT * FROM orders 
                WHERE STR_TO_DATE(order_date, '%Y-%m-%d') < DATE_SUB(NOW(), INTERVAL 2 YEAR)
            ");

            // ✅ Delete old orders from main table
            DB::statement("
                DELETE FROM orders 
                WHERE STR_TO_DATE(order_date, '%Y-%m-%d') < DATE_SUB(NOW(), INTERVAL 2 YEAR)
            ");

            \Log::info('Archived ' . $oldOrdersCount . ' old orders to orders_archive table');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // ✅ Restore data if rollback
        DB::statement("
            INSERT INTO orders 
            SELECT * FROM orders_archive
        ");

        // Drop archive table
        Schema::dropIfExists('orders_archive');
    }
}