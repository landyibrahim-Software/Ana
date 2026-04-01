<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['product_name', 'code_id', 'category_id', 'unit_price', 'quantity', 'status'];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function code()
    {
        return $this->belongsTo(Code::class, 'code_id', 'id');
    }
    public function colors()
{
    return $this->hasMany(ProductColor::class, 'product_id', 'id');
}
}