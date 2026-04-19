<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // Columns shared between `orders` and `orders_archive` used for archival INSERT.
    private const SHARED_COLUMNS = [
        'id', 'customer_id', 'order_date', 'order_status', 'total_products',
        'sub_total', 'invoice_no', 'total', 'payment_status', 'pay', 'due',
        'created_at', 'updated_at',
    ];

    public function up(): void
    {
        // Guard: if the table already exists the migration ran (or partially ran)
        // previously — skip creation to keep `php artisan migrate` from failing.
        if (Schema::hasTable('orders_archive')) {
            return;
        }

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

            $table->index('customer_id');
            $table->index('order_date');
            $table->index('order_status');
            $table->index('payment_status');
        });

        // Archive orders older than 2 years.  Wrapped in try/catch so that a
        // data error never prevents the schema migration from completing.
        try {
            // Use PHP Carbon for the cutoff date — avoids MySQL-only STR_TO_DATE().
            $cutoff = \Carbon\Carbon::now()->subYears(2)->format('Y-m-d');

            $count = DB::table('orders')->where('order_date', '<', $cutoff)->count();

            if ($count > 0) {
                $cols = implode(', ', self::SHARED_COLUMNS);

                // Explicit column list prevents failures when orders has extra
                // columns (grain, grain_price, metter_price) not in orders_archive.
                DB::statement(
                    "INSERT INTO orders_archive ({$cols}) SELECT {$cols} FROM orders WHERE order_date < ?",
                    [$cutoff]
                );

                DB::statement('DELETE FROM orders WHERE order_date < ?', [$cutoff]);

                \Log::info("Archived {$count} old orders to orders_archive.");
            }
        } catch (\Exception $e) {
            \Log::warning('orders_archive: data archival skipped — ' . $e->getMessage());
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('orders_archive');
    }
};