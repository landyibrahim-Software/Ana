<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnedProduct extends Model
{
    use HasFactory;

    protected $table = 'returned_products';
    protected $guarded = [];
    public $timestamps = true;

    protected $casts = [
        'return_date' => 'date',
        'refund_amount' => 'decimal:2',
    ];

    /**
     * Relationship: Get the order
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Relationship: Get the customer
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Relationship: Get all returned items
     */
    public function returnedItems()
    {
        return $this->hasMany(ReturnedItem::class);
    }

    /**
     * Approve return - restore inventory & refund
     */
    public function approve()
    {
        // Restore all returned items to inventory
        foreach ($this->returnedItems as $item) {
            $product = Product::find($item->product_id);
            if ($product) {
                // Increase quantity back
                $product->increment('product_store', $item->quantity_returned);
                
                // If meters exist, add back to product_colors
                if ($item->meters_returned) {
                    // You can add logic here to restore meters to specific colors
                }
            }
        }

        // Update status
        $this->update(['status' => 'approved']);

        return true;
    }

    /**
     * Reject return
     */
    public function reject()
    {
        $this->update(['status' => 'rejected']);
        return true;
    }
}