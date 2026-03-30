<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->integer('customer_id');
            $table->string('order_date');
            $table->string('order_status');
            $table->string('total_products');
            $table->string('sub_total')->nullable();
            $table->decimal('grain')->nullable();
            $table->decimal('grain_price')->nullable();
            
            $table->string('invoice_no')->nullable();
            $table->string('total')->nullable();
            $table->string('payment_status')->nullable();
            $table->string('pay')->nullable();
            $table->string('due')->nullable();
            $table->timestamps();
            Schema::table('orders', function (Blueprint $table) {
        $table->dropColumn([
            'vat',
            'vat_rate',
            'tax_amount'
        ]);
    });
        });
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
          Schema::table('orders', function (Blueprint $table) {
       $table->decimal('grain', 10, 2)->nullable(false)->change();
            $table->decimal('grain_price', 10, 2)->nullable(false)->change();
    });
    }
};
