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
        $balance = self::first();
        
        // If no record exists, create one
        if (!$balance) {
            $balance = self::create(['total_balance' => 0.00]);
        }
        
        return $balance->total_balance ?? 0;
    }

    /**
     * Update balance safely
     */
    public static function updateBalance($newBalance)
    {
        $record = self::first();
        
        // If no record exists, create one
        if (!$record) {
            return self::create(['total_balance' => $newBalance]);
        }
        
        // Update existing record
        $record->update(['total_balance' => $newBalance]);
        return $record;
    }
}