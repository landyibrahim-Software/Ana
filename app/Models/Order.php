<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders';

    protected $fillable = [
    'customer_id',
        'user_id',
        'cancelled_by',
        'order_date',
        'order_status',
        'total_products',
    'sub_total',
    'invoice_no',
    'total',
    'payment_status',
    'pay',
    'due',
    'metter_price',
    'grain',
    'grain_price',
];

protected $casts = [
    'due' => 'decimal:2',
    'pay' => 'decimal:2',
    'sub_total' => 'decimal:2',
    'total' => 'decimal:2',
];

    // 🔗 Order → Customer
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function cancellingUser()
    {
        return $this->belongsTo(User::class, 'cancelled_by', 'id');
    }

    // 🔗 Order → Order Details
    public function orderItems()
    {
        return $this->hasMany(Orderdetails::class, 'order_id', 'id');
    }
    public function orderDetails()
{
    return $this->hasMany(Orderdetails::class, 'order_id');
}
}