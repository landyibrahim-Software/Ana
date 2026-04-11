<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders';

    protected $fillable = [
        'customer_id',
        'order_date',
        'order_status',
        'total_products',
        'sub_total',
        'invoice_no',
        'total',
        'payment_status',
        'pay',
        'due',
        'previous_due',
        'metter_price',
    ];

    // 🔗 Order → Customer
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    // 🔗 Order → Order Details
    public function orderItems()
    {
        return $this->hasMany(Orderdetails::class, 'order_id', 'id');
    }
    /**
 * Relationship: Get returned products from this order
 */

}