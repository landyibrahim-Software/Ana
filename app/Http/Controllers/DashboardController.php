<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductColor;
use App\Models\Expense;
use App\Models\Orderdetails;
use App\Models\SupplierPayment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Get filter type from request (default = 'today')
        $filterType = $request->input('filter', 'today');
        $customStartDate = $request->input('start_date');
        $customEndDate = $request->input('end_date');

        // Create date range based on filter
        $startDate = null;
        $endDate = Carbon::now();

        switch($filterType) {
            case 'today':
                $startDate = Carbon::now()->startOfDay();
                $endDate = Carbon::now()->endOfDay();
                break;
            case 'weekly':
                $startDate = Carbon::now()->startOfWeek();
                $endDate = Carbon::now()->endOfWeek();
                break;
            case 'yearly':
                $startDate = Carbon::now()->startOfYear();
                $endDate = Carbon::now()->endOfYear();
                break;
            case 'custom':
                if ($customStartDate && $customEndDate) {
                    $startDate = Carbon::parse($customStartDate)->startOfDay();
                    $endDate = Carbon::parse($customEndDate)->endOfDay();
                } else {
                    $startDate = Carbon::now()->startOfDay();
                }
                break;
            default:
                $startDate = Carbon::now()->startOfDay();
        }

        // ===== TOTAL PAID (FILTERED BY DATE) =====
        $totalPaid = Order::whereBetween(DB::raw("STR_TO_DATE(order_date, '%Y-%m-%d')"), [$startDate, $endDate])
            ->sum('pay');

        // ===== TOTAL DUE - COMBINED =====
        // Total due from orders (filtered)
        $totalOrderDue = Order::whereBetween(DB::raw("STR_TO_DATE(order_date, '%Y-%m-%d')"), [$startDate, $endDate])
            ->sum('due');

        // Total due from customers (previous dues - NOT filtered by date)
        $customerPreviousDue = Customer::sum('previous_due');
        
        // COMBINED TOTAL DUE
        $totalDue = $totalOrderDue + $customerPreviousDue;

        // ===== TOTAL PAID TO SUPPLIERS (FILTERED BY DATE) =====
        $totalSupplierPayment = SupplierPayment::whereBetween('payment_date', [$startDate, $endDate])
            ->sum('payment_amount');

        // ===== PRODUCT TOTAL METERS =====
        $productsWithMeters = DB::table('product_colors')
            ->join('products', 'product_colors.product_id', '=', 'products.id')
            ->select('products.id', 'products.product_name', DB::raw('SUM(product_colors.meters) as total_meters'))
            ->groupBy('products.id', 'products.product_name')
            ->orderBy('total_meters', 'desc')
            ->get();

        // ===== KEEP YOUR EXISTING CALCULATIONS =====
        $totalStockValue = Product::sum(DB::raw('product_store * buying_price'));
        $today = date('Y-m-d');

        $orders = Order::with(['orderItems.product','customer'])->get();

        $profit = 0;
        $loss = 0;

        foreach ($orders as $order) {
            foreach ($order->orderItems as $item) {
                $buyingPrice = $item->product->buying_price ?? 0;
                $sellingPrice = $item->unitcost;
                $quantity = $item->quantity;
                $orderProfit = ($sellingPrice - $buyingPrice) * $quantity;

                if ($orderProfit > 0) {
                    $profit += $orderProfit;
                } else {
                    $loss += abs($orderProfit);
                }
            }
        }

        $monthlyPaid = [];
        for ($month = 1; $month <= 12; $month++) {
            $monthlyPaid[] = Order::whereMonth('order_date', $month)
                ->whereYear('order_date', date('Y'))
                ->sum('pay');
        }

        $totalExpenses = Expense::sum('amount');
        $todayExpenses = Expense::whereDate('date', $today)->sum('amount');
        $monthlyExpenses = Expense::whereMonth('date', date('m'))
            ->whereYear('date', date('Y'))
            ->sum('amount');

        $recentExpenses = Expense::orderBy('created_at', 'desc')->take(5)->get();
        $lowStockProducts = Product::where('product_store', '<=', 10)->orderBy('product_store', 'asc')->take(5)->get();
        $todayOrders = Order::whereDate('order_date', $today)->count();
        $todaySales = Order::whereDate('order_date', $today)->sum('total');

        $topCustomers = Order::select('customer_id', DB::raw('SUM(pay) as total_spent'))
            ->groupBy('customer_id')
            ->with('customer')
            ->orderBy('total_spent', 'desc')
            ->take(5)
            ->get();

        $bestSellingProducts = Orderdetails::select('product_id', DB::raw('SUM(quantity) as total_sold'))
            ->whereHas('order', function($query) {
                $query->whereMonth('order_date', date('m'))
                      ->whereYear('order_date', date('Y'));
            })
            ->groupBy('product_id')
            ->with('product')
            ->orderBy('total_sold', 'desc')
            ->take(5)
            ->get();

        // Recent supplier payments
        $recentSupplierPayments = SupplierPayment::whereBetween('payment_date', [$startDate, $endDate])
            ->with('supplier')
            ->orderBy('payment_date', 'desc')
            ->take(5)
            ->get();

        // Pass all data to view
        return view('index', compact(
            'totalPaid', 'totalDue', 'profit', 'loss',
            'totalStockValue', 'todayOrders', 'todayExpenses', 'todaySales',
            'monthlyPaid', 'topCustomers', 'recentExpenses', 'lowStockProducts',
            'bestSellingProducts', 'filterType',
            'productsWithMeters', 'orders', 'totalExpenses', 'monthlyExpenses',
            'startDate', 'endDate', 'totalSupplierPayment', 'recentSupplierPayments'
        ));
    }
}