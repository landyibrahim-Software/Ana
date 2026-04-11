<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\BankBalance;
use App\Models\BankTransaction;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BankController extends Controller
{
    /**
     * Show bank dashboard
     */
    public function index(Request $request)
    {
        // Get current balances
        $currentBalance = BankBalance::getCurrentBalance();
        $currentBalanceIQD = BankBalance::getCurrentBalanceIQD();

        // Get selected date (default: today)
        $selectedDate = $request->get('date') ?? now()->toDateString();

        // Get USD transactions for selected date
        $transactions = BankTransaction::whereDate('transaction_date', $selectedDate)
            ->where('currency', 'USD')
            ->orderBy('created_at', 'DESC')
            ->get();

        // Get IQD transactions for selected date
        $transactionsIQD = BankTransaction::whereDate('transaction_date', $selectedDate)
            ->where('currency', 'IQD')
            ->orderBy('created_at', 'DESC')
            ->get();

        // Get USD balance on selected date
        $dateBalance = BankTransaction::whereDate('transaction_date', '<=', $selectedDate)
            ->where('currency', 'USD')
            ->orderBy('transaction_date', 'DESC')
            ->orderBy('created_at', 'DESC')
            ->first();

        $balanceOnDate = $dateBalance->balance_after ?? 0;

        // Get IQD balance on selected date
        $dateBalanceIQD = BankTransaction::whereDate('transaction_date', '<=', $selectedDate)
            ->where('currency', 'IQD')
            ->orderBy('transaction_date', 'DESC')
            ->orderBy('created_at', 'DESC')
            ->first();

        $balanceOnDateIQD = $dateBalanceIQD->balance_after ?? 0;

        // Get today's USD summary
        $todaySpend = BankTransaction::whereDate('transaction_date', now()->toDateString())
            ->where('currency', 'USD')
            ->where('transaction_type', 'spend')
            ->sum('amount');

        $todayReceive = BankTransaction::whereDate('transaction_date', now()->toDateString())
            ->where('currency', 'USD')
            ->where('transaction_type', 'receive')
            ->sum('amount');

        // Get today's IQD summary
        $todaySpendIQD = BankTransaction::whereDate('transaction_date', now()->toDateString())
            ->where('currency', 'IQD')
            ->where('transaction_type', 'spend')
            ->sum('amount');

        $todayReceiveIQD = BankTransaction::whereDate('transaction_date', now()->toDateString())
            ->where('currency', 'IQD')
            ->where('transaction_type', 'receive')
            ->sum('amount');

        return view('backend.bank.index', compact(
            'currentBalance',
            'currentBalanceIQD',
            'transactions',
            'transactionsIQD',
            'selectedDate',
            'balanceOnDate',
            'balanceOnDateIQD',
            'todaySpend',
            'todayReceive',
            'todaySpendIQD',
            'todayReceiveIQD'
        ));
    }

    /**
     * Add USD spend transaction
     */
    public function addSpend(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|min:3',
        ]);

        try {
            BankTransaction::addSpend($validated['amount'], $validated['description']);

            return redirect()->back()->with([
                'message' => 'پارە کەم کرا بە سەرکەوتی',
                'alert-type' => 'success'
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with([
                'message' => 'هەڵەیەک روویدا',
                'alert-type' => 'danger'
            ]);
        }
    }

    /**
     * Add USD receive transaction
     */
    public function addReceive(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|min:3',
        ]);

        try {
            BankTransaction::addReceive($validated['amount'], $validated['description']);

            return redirect()->back()->with([
                'message' => 'پارە زیاد کرا بە سەرکەوتی',
                'alert-type' => 'success'
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with([
                'message' => 'هەڵەیەک روویدا',
                'alert-type' => 'danger'
            ]);
        }
    }

    /**
     * Add IQD spend transaction
     */
    public function addSpendIQD(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|min:3',
        ]);

        try {
            BankTransaction::addSpendIQD($validated['amount'], $validated['description']);

            return redirect()->back()->with([
                'message' => 'دینار کەم کرا بە سەرکەوتی',
                'alert-type' => 'success'
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with([
                'message' => 'هەڵەیەک روویدا',
                'alert-type' => 'danger'
            ]);
        }
    }

    /**
     * Add IQD receive transaction
     */
    public function addReceiveIQD(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|min:3',
        ]);

        try {
            BankTransaction::addReceiveIQD($validated['amount'], $validated['description']);

            return redirect()->back()->with([
                'message' => 'دینار زیاد کرا بە سەرکەوتی',
                'alert-type' => 'success'
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with([
                'message' => 'هەڵەیەک روویدا',
                'alert-type' => 'danger'
            ]);
        }
    }
}