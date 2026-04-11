<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankBalance extends Model
{
    use HasFactory;

    protected $table = 'bank_balance';
    protected $guarded = [];
    public $timestamps = true;

    /**
     * Get current bank balance
     */
    public static function getCurrentBalance()
    {
        return self::first()->total_balance ?? 0;
    }

    /**
     * Update balance
     */
    public static function updateBalance($newBalance)
    {
        return self::first()->update(['total_balance' => $newBalance]);
    }
}