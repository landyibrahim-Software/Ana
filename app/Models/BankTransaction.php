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
     * Spend USD from bank
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
            'currency' => 'USD',
        ]);

        BankBalance::updateBalance($newBalance);
        return $newBalance;
    }

    /**
     * Receive USD to bank
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
            'currency' => 'USD',
        ]);

        BankBalance::updateBalance($newBalance);
        return $newBalance;
    }

    /**
     * Spend IQD from bank
     */
    public static function addSpendIQD($amount, $description)
    {
        $currentBalance = BankBalance::getCurrentBalanceIQD();
        $newBalance = $currentBalance - $amount;

        self::create([
            'transaction_type' => 'spend',
            'amount' => $amount,
            'description' => $description,
            'balance_after' => $newBalance,
            'transaction_date' => now()->toDateString(),
            'currency' => 'IQD',
        ]);

        BankBalance::updateBalanceIQD($newBalance);
        return $newBalance;
    }

    /**
     * Receive IQD to bank
     */
    public static function addReceiveIQD($amount, $description)
    {
        $currentBalance = BankBalance::getCurrentBalanceIQD();
        $newBalance = $currentBalance + $amount;

        self::create([
            'transaction_type' => 'receive',
            'amount' => $amount,
            'description' => $description,
            'balance_after' => $newBalance,
            'transaction_date' => now()->toDateString(),
            'currency' => 'IQD',
        ]);

        BankBalance::updateBalanceIQD($newBalance);
        return $newBalance;
    }

    /**
     * Get transactions by date and currency
     */
    public static function getByDateAndCurrency($date, $currency = 'USD')
    {
        return self::whereDate('transaction_date', $date)
            ->where('currency', $currency)
            ->orderBy('created_at', 'DESC')
            ->get();
    }
}