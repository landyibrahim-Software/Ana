<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'payment_amount',
        'payment_date',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}