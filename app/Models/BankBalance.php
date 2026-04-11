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
     * Get current USD balance
     */
    public static function getCurrentBalance()
    {
        $balance = self::first();
        
        if (!$balance) {
            $balance = self::create(['total_balance' => 0.00, 'total_balance_iqd' => 0.00]);
        }
        
        return $balance->total_balance ?? 0;
    }

    /**
     * Get current IQD balance
     */
    public static function getCurrentBalanceIQD()
    {
        $balance = self::first();
        
        if (!$balance) {
            $balance = self::create(['total_balance' => 0.00, 'total_balance_iqd' => 0.00]);
        }
        
        return $balance->total_balance_iqd ?? 0;
    }

    /**
     * Update USD balance
     */
    public static function updateBalance($newBalance)
    {
        $record = self::first();
        
        if (!$record) {
            return self::create(['total_balance' => $newBalance, 'total_balance_iqd' => 0.00]);
        }
        
        $record->update(['total_balance' => $newBalance]);
        return $record;
    }

    /**
     * Update IQD balance
     */
    public static function updateBalanceIQD($newBalance)
    {
        $record = self::first();
        
        if (!$record) {
            return self::create(['total_balance' => 0.00, 'total_balance_iqd' => $newBalance]);
        }
        
        $record->update(['total_balance_iqd' => $newBalance]);
        return $record;
    }
}