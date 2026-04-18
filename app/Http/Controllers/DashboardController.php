<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Product;
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
            case 'yesterday':
                $startDate = Carbon::yesterday()->startOfDay();
                $endDate = Carbon::yesterday()->endOfDay();
                break;
            case 'last_week':
                $startDate = Carbon::now()->subWeek()->startOfDay();
                break;
            case 'last_month':
                $startDate = Carbon::now()->subMonth()->startOfDay();
                break;
            case 'last_year':
                $startDate = Carbon::now()->subYear()->startOfDay();
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

        // ===== 1. TOTAL PAID (Filtered by Date) - EXCLUDE CANCELLED =====
        $orderPayments = Order::where('order_status', '!=', 'cancelled')
            ->whereBetween(DB::raw("STR_TO_DATE(order_date, '%Y-%m-%d')"), [$startDate, $endDate])
            ->sum('pay');

        $customerPayments = Payment::whereBetween('payment_date', [$startDate, $endDate])
            ->sum('payment_amount');

        $totalPaid = $orderPayments + $customerPayments;

        // ===== 2. TOTAL DUE (Current Remaining Balance) - EXCLUDE CANCELLED =====
        $totalDue = 0;
        $customers = Customer::all();
        foreach ($customers as $customer) {
            $total_spent = $customer->previous_due;
            foreach ($customer->orders()->where('order_status', '!=', 'cancelled')->get() as $order) {
                $total_spent += $order->sub_total;
            }
            
            $total_paid = Payment::where('customer_id', $customer->id)->sum('payment_amount');
            foreach ($customer->orders()->where('order_status', '!=', 'cancelled')->get() as $order) {
                $total_paid += ($order->pay ?? 0);
            }
            
            $customer_due = max($total_spent - $total_paid, 0);
            $totalDue += $customer_due;
        }

        // ===== 3. PROFIT & LOSS (From Filtered Orders) - EXCLUDE CANCELLED =====
        $filteredOrders = Order::where('order_status', '!=', 'cancelled')
            ->whereBetween(DB::raw("STR_TO_DATE(order_date, '%Y-%m-%d')"), [$startDate, $endDate])
            ->with('orderItems.product')
            ->get();

        $profit = 0;
        $loss = 0;

        foreach ($filteredOrders as $order) {
            foreach ($order->orderItems as $item) {
                $buyingPrice = $item->product->buying_price ?? 0;
                $sellingPrice = $item->unitcost;
                // FIXED: Use quantity instead of meters
                $quantity = $item->quantity;
                $orderProfit = ($sellingPrice - $buyingPrice) * $quantity;

                if ($orderProfit > 0) {
                    $profit += $orderProfit;
                } else {
                    $loss += abs($orderProfit);
                }
            }
        }

        // ===== 4. SUPPLIER PAYMENTS (Filtered by Date) =====
        $totalSupplierPayment = SupplierPayment::whereBetween('payment_date', [$startDate, $endDate])
            ->sum('payment_amount');

        // ===== 5. STOCK VALUE =====
        // FIXED: Changed from product_colors to use product_store directly
        $totalStockValue = DB::selectOne("
            SELECT SUM(CAST(p.product_store AS DECIMAL(10,2)) * CAST(p.buying_price AS DECIMAL(10,2))) as total
            FROM products p
        ")->total ?? 0;

        // ===== 6. TODAY'S SALES & ORDERS - EXCLUDE CANCELLED =====
        $today = date('Y-m-d');
        $todaySales = Order::where('order_status', '!=', 'cancelled')
            ->whereDate('order_date', $today)
            ->sum('sub_total');
        
        $todayOrders = Order::where('order_status', '!=', 'cancelled')
            ->whereDate('order_date', $today)
            ->count();
        
        $todayExpenses = Expense::whereDate('date', $today)->sum('amount');

        // ===== 7. TOTAL EXPENSES =====
        $totalExpenses = Expense::sum('amount');

        // ===== 8. OTHER DATA =====
        $monthlyPaid = [];
        for ($month = 1; $month <= 12; $month++) {
            $monthlyPaid[] = Order::where('order_status', '!=', 'cancelled')
                ->whereMonth('order_date', $month)
                ->whereYear('order_date', date('Y'))
                ->sum('pay');
        }

        $recentExpenses = Expense::orderBy('created_at', 'desc')->take(5)->get();
        $lowStockProducts = Product::where('product_store', '<=', 10)->orderBy('product_store', 'asc')->take(5)->get();

        $topCustomers = Order::where('order_status', '!=', 'cancelled')
            ->select('customer_id', DB::raw('SUM(pay) as total_spent'))
            ->groupBy('customer_id')
            ->with('customer')
            ->orderBy('total_spent', 'desc')
            ->take(5)
            ->get();

        // ===== BEST SELLING PRODUCTS - EXCLUDE CANCELLED =====
        // FIXED: Changed from od.meters to od.quantity
        $bestSellingProducts = DB::table('orderdetails as od')
            ->join('products as p', 'od.product_id', '=', 'p.id')
            ->join('orders as o', 'od.order_id', '=', 'o.id')
            ->leftJoin('categories as c', 'p.category_id', '=', 'c.id')
            ->selectRaw('
                p.id,
                p.product_name,
                p.product_code,
                p.product_image,
                c.category_name,
                p.product_store,
                SUM(CAST(od.quantity AS DECIMAL(10,2))) as total_meters_sold,
                p.buying_price,
                p.category_id
            ')
            ->where('o.order_status', '!=', 'cancelled')
            ->whereRaw('MONTH(od.created_at) = MONTH(NOW())')
            ->whereRaw('YEAR(od.created_at) = YEAR(NOW())')
            ->groupBy('od.product_id', 'p.id', 'p.product_name', 'p.product_code', 'p.product_image', 'c.category_name', 'p.product_store', 'p.buying_price', 'p.category_id')
            ->orderByRaw('SUM(CAST(od.quantity AS DECIMAL(10,2))) DESC')
            ->limit(10)
            ->get();

        $recentSupplierPayments = SupplierPayment::whereBetween('payment_date', [$startDate, $endDate])
            ->with('supplier')
            ->orderBy('payment_date', 'desc')
            ->take(5)
            ->get();

        // EXCLUDE CANCELLED ORDERS FROM RECENT ORDERS TABLE
        $orders = Order::where('order_status', '!=', 'cancelled')
            ->with(['orderItems.product','customer'])
            ->get();

        // Pass all data to view
        return view('index', compact(
            'totalPaid', 'totalDue', 'profit', 'loss',
            'totalStockValue', 'todayOrders', 'todayExpenses', 'todaySales',
            'monthlyPaid', 'topCustomers', 'recentExpenses', 'lowStockProducts',
            'bestSellingProducts', 'filterType',
            'orders', 'totalExpenses',
            'startDate', 'endDate', 'totalSupplierPayment', 'recentSupplierPayments'
        ));
    }
}