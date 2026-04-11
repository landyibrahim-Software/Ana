<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnedItem extends Model
{
    use HasFactory;

    protected $table = 'returned_items';
    protected $guarded = [];
    public $timestamps = true;

    protected $casts = [
        'meters_returned' => 'decimal:2',
        'original_price' => 'decimal:2',
        'refund_price' => 'decimal:2',
    ];

    /**
     * Relationship: Get the returned product
     */
    public function returnedProduct()
    {
        return $this->belongsTo(ReturnedProduct::class);
    }

    /**
     * Relationship: Get the product
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}