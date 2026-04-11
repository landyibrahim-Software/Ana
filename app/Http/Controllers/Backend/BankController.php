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
        // Get current balance
        $currentBalance = BankBalance::getCurrentBalance();

        // Get selected date (default: today)
        $selectedDate = $request->get('date') ?? now()->toDateString();

        // Get transactions for selected date
        $transactions = BankTransaction::whereDate('transaction_date', $selectedDate)
            ->orderBy('created_at', 'DESC')
            ->get();

        // Get balance on selected date
        $dateBalance = BankTransaction::whereDate('transaction_date', '<=', $selectedDate)
            ->orderBy('transaction_date', 'DESC')
            ->orderBy('created_at', 'DESC')
            ->first();

        $balanceOnDate = $dateBalance->balance_after ?? 0;

        // Get today's summary
        $todaySpend = BankTransaction::whereDate('transaction_date', now()->toDateString())
            ->where('transaction_type', 'spend')
            ->sum('amount');

        $todayReceive = BankTransaction::whereDate('transaction_date', now()->toDateString())
            ->where('transaction_type', 'receive')
            ->sum('amount');

        return view('backend.bank.index', compact(
            'currentBalance',
            'transactions',
            'selectedDate',
            'balanceOnDate',
            'todaySpend',
            'todayReceive'
        ));
    }

    /**
     * Add spend transaction
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
     * Add receive transaction
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
}