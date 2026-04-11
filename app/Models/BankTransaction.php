<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankTransaction extends Model
{
    use HasFactory;

    protected $table = 'bank_transactions';
    protected $guarded = [];
    public $timestamps = true;

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'transaction_date' => 'date',
    ];

    /**
     * Spend money from bank
     */
    public static function addSpend($amount, $description)
    {
        $currentBalance = BankBalance::getCurrentBalance();
        $newBalance = $currentBalance - $amount;

        self::create([
            'transaction_type' => 'spend',
            'amount' => $amount,
            'description' => $description,
            'balance_after' => $newBalance,
            'transaction_date' => now()->toDateString(),
        ]);

        BankBalance::updateBalance($newBalance);
        return $newBalance;
    }

    /**
     * Receive money to bank
     */
    public static function addReceive($amount, $description)
    {
        $currentBalance = BankBalance::getCurrentBalance();
        $newBalance = $currentBalance + $amount;

        self::create([
            'transaction_type' => 'receive',
            'amount' => $amount,
            'description' => $description,
            'balance_after' => $newBalance,
            'transaction_date' => now()->toDateString(),
        ]);

        BankBalance::updateBalance($newBalance);
        return $newBalance;
    }

    /**
     * Get transactions by date
     */
    public static function getByDate($date)
    {
        return self::whereDate('transaction_date', $date)
            ->orderBy('created_at', 'DESC')
            ->get();
    }
}