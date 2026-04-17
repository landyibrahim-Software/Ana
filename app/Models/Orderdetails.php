<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Orderdetails extends Model
{
    protected $table = 'orderdetails';

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'unitcost',
        'meters',
        'selected_colors',
        'metter_price',
        'total',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unitcost' => 'decimal:2',
        'meters' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    // 🔗 Orderdetails → Order
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    // 🔗 Orderdetails → Product
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}