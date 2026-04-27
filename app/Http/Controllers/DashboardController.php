<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\SupplierPayment;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        try {
            // Get filter type from request
            $filterType = $request->get('filter', 'today');
            
            // Define date range based on filter
            $startDate = Carbon::now()->startOfDay();
            $endDate = Carbon::now()->endOfDay();
            
            if ($filterType === 'yesterday') {
                $startDate = Carbon::yesterday()->startOfDay();
                $endDate = Carbon::yesterday()->endOfDay();
            } elseif ($filterType === 'last_week') {
                $startDate = Carbon::now()->subWeek()->startOfWeek();
                $endDate = Carbon::now()->endOfWeek();
            } elseif ($filterType === 'last_month') {
                $startDate = Carbon::now()->subMonth()->startOfMonth();
                $endDate = Carbon::now()->subMonth()->endOfMonth();
            } elseif ($filterType === 'last_year') {
                $startDate = Carbon::now()->subYear()->startOfYear();
                $endDate = Carbon::now()->subYear()->endOfYear();
            } elseif ($filterType === 'custom') {
                $startDate = $request->get('start_date') ? Carbon::parse($request->get('start_date'))->startOfDay() : $startDate;
                $endDate = $request->get('end_date') ? Carbon::parse($request->get('end_date'))->endOfDay() : $endDate;
            }

            // ✅ TOTAL PAID
            $totalPaid = Order::whereBetween('created_at', [$startDate, $endDate])->sum('pay') ?? 0;

// ✅ TOTAL DUE (Orders in date range only)
$totalDue = Order::whereBetween('created_at', [$startDate, $endDate])->sum('due') ?? 0;

           // ✅ PROFIT CALCULATION — single SQL query instead of PHP loop
            $profitRow = DB::selectOne("
                SELECT
                    SUM(CASE WHEN CAST(od.unitcost AS DECIMAL(15,4)) > CAST(p.buying_price AS DECIMAL(15,4))
                        THEN (CAST(od.unitcost AS DECIMAL(15,4)) - CAST(p.buying_price AS DECIMAL(15,4)))
                             * CAST(od.quantity AS DECIMAL(15,4))
                        ELSE 0 END) AS profit,
                    SUM(CASE WHEN CAST(od.unitcost AS DECIMAL(15,4)) <= CAST(p.buying_price AS DECIMAL(15,4))
                        THEN (CAST(p.buying_price AS DECIMAL(15,4)) - CAST(od.unitcost AS DECIMAL(15,4)))
                             * CAST(od.quantity AS DECIMAL(15,4))
                        ELSE 0 END) AS loss
                FROM orderdetails od
                INNER JOIN orders o ON od.order_id = o.id
                INNER JOIN products p ON od.product_id = p.id
                WHERE o.created_at BETWEEN ? AND ?
                  AND o.order_status != 'cancelled'
            ", [$startDate, $endDate]);
            $profit = floatval($profitRow->profit ?? 0);
            $loss   = floatval($profitRow->loss   ?? 0);

            // ✅ TOTAL SUPPLIER PAYMENT
            $totalSupplierPayment = SupplierPayment::whereBetween('payment_date', [$startDate, $endDate])
                ->sum('payment_amount') ?? 0;

            // ✅ TOTAL STOCK VALUE
            $totalStockValue = Product::all()->sum(function ($product) {
                $buyingPrice = floatval($product->buying_price ?? 0);
                $store = floatval($product->product_store ?? 0);
                return $buyingPrice * $store;
            });

            // ✅ TODAY'S SALES
            $todaySales = Order::whereDate('created_at', Carbon::today())
                ->sum('sub_total') ?? 0;

            // ✅ TODAY'S ORDERS COUNT
            $todayOrders = Order::whereDate('created_at', Carbon::today())->count() ?? 0;

            // ✅ TOTAL EXPENSES
            $totalExpenses = Expense::whereBetween('created_at', [$startDate, $endDate])
                ->sum('amount') ?? 0;

            // ✅ RECENT ORDERS (Last 10) - SAFE LOAD
            $orders = Order::whereBetween('created_at', [$startDate, $endDate])
                ->with('customer')
                ->with('orderDetails.product')
                ->latest()
                ->limit(10)
                ->get();

            // ✅ TOP CUSTOMERS - SAFE LOAD
            $topCustomers = Order::whereBetween('created_at', [$startDate, $endDate])
                ->select('customer_id', DB::raw('SUM(sub_total) as total_spent'))
                ->with('customer')
                ->groupBy('customer_id')
                ->orderByDesc('total_spent')
                ->limit(5)
                ->get();

            // ✅ RECENT EXPENSES - SAFE
            $recentExpenses = Expense::whereBetween('created_at', [$startDate, $endDate])
                ->latest()
                ->limit(5)
                ->get();

            // ✅ RECENT SUPPLIER PAYMENTS - SAFE
            $recentSupplierPayments = SupplierPayment::whereBetween('payment_date', [$startDate, $endDate])
                ->with('supplier')
                ->latest()
                ->limit(5)
                ->get();

            // ✅ LOW STOCK PRODUCTS
            $lowStockProducts = Product::where('product_store', '<', 10)
                ->latest()
                ->limit(10)
                ->get();

            // ✅ BEST SELLING PRODUCTS - SAFE
            $bestSellingProducts = Order::whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->with('orderDetails.product.category')
                ->get()
                ->flatMap(function ($order) {
                    return $order->orderDetails ?? [];
                })
                ->groupBy('product_id')
                ->map(function ($items) {
                    $first = $items->first();
                    if (!$first || !$first->product) {
                        return null;
                    }
                    return (object) [
                        'product_id' => $first->product_id,
                        'product_name' => $first->product->product_name ?? 'N/A',
                        'product_code' => $first->product->product_code ?? 'N/A',
                        'product_image' => $first->product->product_image ?? null,
                        'category_name' => $first->product->category->category_name ?? 'N/A',
                        'total_sold' => $items->sum('quantity'),
                        'product_store' => $first->product->product_store ?? 0,
                    ];
                })
                ->filter()
                ->sortByDesc('total_sold')
                ->values()
                ->take(10);

           
            // ✅ MONTHLY PAID DATA — single GROUP BY query instead of 12 separate queries
            $monthlyRows = DB::select("
                SELECT MONTH(created_at) AS month, SUM(CAST(pay AS DECIMAL(15,4))) AS amount
                FROM orders
                WHERE YEAR(created_at) = ?
                GROUP BY MONTH(created_at)
            ", [Carbon::now()->year]);
            $monthlyPaidMap = [];
            foreach ($monthlyRows as $row) {
                $monthlyPaidMap[(int)$row->month] = floatval($row->amount);
            }
            $monthlyPaid = [];
             for ($m = 1; $m <= 12; $m++) {
                $monthlyPaid[] = $monthlyPaidMap[$m] ?? 0.0;
            }

            return view('index', [
                'filterType' => $filterType,
                'orders' => $orders,
                'totalPaid' => floatval($totalPaid),
                'totalDue' => floatval($totalDue),
                'profit' => floatval($profit),
                'loss' => floatval($loss),
                'totalSupplierPayment' => floatval($totalSupplierPayment),
                'totalStockValue' => floatval($totalStockValue),
                'todaySales' => floatval($todaySales),
                'todayOrders' => intval($todayOrders),
                'totalExpenses' => floatval($totalExpenses),
                'topCustomers' => $topCustomers,
                'recentExpenses' => $recentExpenses,
                'recentSupplierPayments' => $recentSupplierPayments,
                'lowStockProducts' => $lowStockProducts,
                'bestSellingProducts' => $bestSellingProducts,
                'monthlyPaid' => $monthlyPaid,
            ]);

        } catch (\Exception $e) {
            \Log::error('Dashboard Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            
            return view('index', [
                'filterType' => 'today',
                'orders' => collect(),
                'totalPaid' => 0,
                'totalDue' => 0,
                'profit' => 0,
                'loss' => 0,
                'totalSupplierPayment' => 0,
                'totalStockValue' => 0,
                'todaySales' => 0,
                'todayOrders' => 0,
                'totalExpenses' => 0,
                'topCustomers' => collect(),
                'recentExpenses' => collect(),
                'recentSupplierPayments' => collect(),
                'lowStockProducts' => collect(),
                'bestSellingProducts' => collect(),
                'monthlyPaid' => [0,0,0,0,0,0,0,0,0,0,0,0],
            ]);
        }
    }
}