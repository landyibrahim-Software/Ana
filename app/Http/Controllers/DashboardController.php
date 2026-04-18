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
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $filterType = $request->input('filter', 'today');
        $customStartDate = $request->input('start_date');
        $customEndDate = $request->input('end_date');
        $cacheKey = 'dashboard_' . $filterType . '_' . md5($customStartDate . $customEndDate) . '_' . auth()->id();

        if (Cache::has($cacheKey)) {
            $cachedData = Cache::get($cacheKey);
            $cachedData['cached'] = true;
            return view('index', $cachedData);
        }

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

        try {
            // ✅ TOTAL PAID
            $orderPayments = Order::where('order_status', '!=', 'cancelled')
                ->whereBetween(DB::raw("STR_TO_DATE(order_date, '%Y-%m-%d')"), [$startDate, $endDate])
                ->select('pay')
                ->sum('pay');

            $customerPayments = Payment::whereBetween('payment_date', [$startDate, $endDate])
                ->select('payment_amount')
                ->sum('payment_amount');

            $totalPaid = floatval($orderPayments) + floatval($customerPayments);

            // ✅ TOTAL DUE
            $totalDue = Order::where('order_status', '!=', 'cancelled')
                ->select('due')
                ->sum('due');
            $totalDue = floatval($totalDue ?? 0);

            // ✅ PROFIT & LOSS
            $profitLoss = $this->calculateProfitAndLoss($startDate, $endDate);
            $profit = $profitLoss['profit'];
            $loss = $profitLoss['loss'];

            // ✅ SUPPLIER PAYMENTS
            $totalSupplierPayment = SupplierPayment::whereBetween('payment_date', [$startDate, $endDate])
                ->select('payment_amount')
                ->sum('payment_amount');

            // ✅ STOCK VALUE
            $totalStockValue = $this->calculateStockValue();

            // ✅ TODAY'S DATA
            $today = date('Y-m-d');
            
            $todaySales = Order::where('order_status', '!=', 'cancelled')
                ->whereDate('order_date', $today)
                ->select('sub_total')
                ->sum('sub_total');
            
            $todayOrders = Order::where('order_status', '!=', 'cancelled')
                ->whereDate('order_date', $today)
                ->count();
            
            $todayExpenses = Expense::whereDate('date', $today)
                ->select('amount')
                ->sum('amount');

            // ✅ TOTAL EXPENSES
            $totalExpenses = Expense::select('amount')->sum('amount');

            // ✅ MONTHLY PAID
            $monthlyPaid = $this->getMonthlyPaidData();

            // ✅ RECENT EXPENSES (FIXED: removed 'description')
            $recentExpenses = Expense::select(['id', 'amount', 'date', 'created_at'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            // ✅ LOW STOCK PRODUCTS
            $lowStockProducts = Product::where('product_store', '<=', 10)
                ->select(['id', 'product_name', 'product_code', 'product_store', 'selling_price', 'buying_price'])
                ->orderBy('product_store', 'asc')
                ->limit(5)
                ->get();

            // ✅ TOP CUSTOMERS
            $topCustomers = Order::where('order_status', '!=', 'cancelled')
                ->select('customer_id', DB::raw('SUM(pay) as total_spent'))
                ->groupBy('customer_id')
                ->with('customer:id,name,phone')
                ->orderBy('total_spent', 'desc')
                ->limit(5)
                ->get();

            // ✅ BEST SELLING PRODUCTS
            $bestSellingProducts = $this->getBestSellingProducts();

            // ✅ RECENT SUPPLIER PAYMENTS
            $recentSupplierPayments = SupplierPayment::whereBetween('payment_date', [$startDate, $endDate])
                ->with('supplier:id,supplier_name')
                ->select(['id', 'supplier_id', 'payment_amount', 'payment_date', 'created_at'])
                ->orderBy('payment_date', 'desc')
                ->limit(5)
                ->get();

            // ✅ RECENT ORDERS
            $orders = Order::where('order_status', '!=', 'cancelled')
                ->with([
                    'orderItems:id,order_id,product_id,quantity,unitcost',
                    'orderItems.product:id,product_name,product_code',
                    'customer:id,name,phone'
                ])
                ->select(['id', 'customer_id', 'order_date', 'sub_total', 'pay', 'due', 'order_status', 'payment_status'])
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get();

            // ✅ Prepare data array
            $data = [
                'totalPaid' => $totalPaid,
                'totalDue' => $totalDue,
                'profit' => $profit,
                'loss' => $loss,
                'totalStockValue' => $totalStockValue,
                'todayOrders' => $todayOrders,
                'todayExpenses' => $todayExpenses,
                'todaySales' => $todaySales,
                'monthlyPaid' => $monthlyPaid,
                'topCustomers' => $topCustomers,
                'recentExpenses' => $recentExpenses,
                'lowStockProducts' => $lowStockProducts,
                'bestSellingProducts' => $bestSellingProducts,
                'filterType' => $filterType,
                'orders' => $orders,
                'totalExpenses' => $totalExpenses,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'totalSupplierPayment' => $totalSupplierPayment,
                'recentSupplierPayments' => $recentSupplierPayments,
                'cached' => false
            ];

            // ✅ Cache for 1 hour
            Cache::put($cacheKey, $data, 3600);

            return view('index', $data);

        } catch (\Exception $e) {
            return view('index', [
                'totalPaid' => 0,
                'totalDue' => 0,
                'profit' => 0,
                'loss' => 0,
                'totalStockValue' => 0,
                'todayOrders' => 0,
                'todayExpenses' => 0,
                'todaySales' => 0,
                'monthlyPaid' => array_fill(0, 12, 0),
                'topCustomers' => collect(),
                'recentExpenses' => collect(),
                'lowStockProducts' => collect(),
                'bestSellingProducts' => collect(),
                'filterType' => $filterType,
                'orders' => collect(),
                'totalExpenses' => 0,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'totalSupplierPayment' => 0,
                'recentSupplierPayments' => collect(),
                'cached' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function calculateProfitAndLoss($startDate, $endDate)
    {
        try {
            $orderItems = DB::table('orderdetails as od')
                ->join('orders as o', 'od.order_id', '=', 'o.id')
                ->join('products as p', 'od.product_id', '=', 'p.id')
                ->select(
                    'od.quantity',
                    'od.unitcost',
                    'p.buying_price'
                )
                ->where('o.order_status', '!=', 'cancelled')
                ->whereBetween(DB::raw("STR_TO_DATE(o.order_date, '%Y-%m-%d')"), [$startDate, $endDate])
                ->get();

            $profit = 0;
            $loss = 0;

            foreach ($orderItems as $item) {
                $buyingPrice = floatval($item->buying_price ?? 0);
                $sellingPrice = floatval($item->unitcost);
                $quantity = floatval($item->quantity);
                
                $itemProfit = ($sellingPrice - $buyingPrice) * $quantity;

                if ($itemProfit > 0) {
                    $profit += $itemProfit;
                } else {
                    $loss += abs($itemProfit);
                }
            }

            return [
                'profit' => floatval($profit),
                'loss' => floatval($loss)
            ];

        } catch (\Exception $e) {
            return ['profit' => 0, 'loss' => 0];
        }
    }

    private function calculateStockValue()
    {
        try {
            $stockValue = DB::table('products')
                ->select(DB::raw('SUM(CAST(product_store AS DECIMAL(10,2)) * CAST(buying_price AS DECIMAL(10,2))) as total'))
                ->value('total');

            return floatval($stockValue ?? 0);

        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getMonthlyPaidData()
    {
        try {
            $monthlyData = Order::where('order_status', '!=', 'cancelled')
                ->whereYear('order_date', date('Y'))
                ->selectRaw('MONTH(order_date) as month, SUM(pay) as total')
                ->groupBy(DB::raw('MONTH(order_date)'))
                ->pluck('total', 'month')
                ->toArray();

            $monthlyPaid = [];
            for ($month = 1; $month <= 12; $month++) {
                $monthlyPaid[] = floatval($monthlyData[$month] ?? 0);
            }

            return $monthlyPaid;

        } catch (\Exception $e) {
            return array_fill(0, 12, 0);
        }
    }

    private function getBestSellingProducts()
    {
        try {
            $bestSellingProducts = DB::table('orderdetails as od')
                ->join('products as p', 'od.product_id', '=', 'p.id')
                ->join('orders as o', 'od.order_id', '=', 'o.id')
                ->leftJoin('categories as c', 'p.category_id', '=', 'c.id')
                ->select(
                    'p.id',
                    'p.product_name',
                    'p.product_code',
                    'p.product_image',
                    'c.category_name',
                    'p.product_store',
                    'p.buying_price',
                    DB::raw('SUM(CAST(od.quantity AS DECIMAL(10,2))) as total_sold')
                )
                ->where('o.order_status', '!=', 'cancelled')
                ->whereMonth('od.created_at', now()->month)
                ->whereYear('od.created_at', now()->year)
                ->groupBy('od.product_id', 'p.id', 'p.product_name', 'p.product_code', 'p.product_image', 'c.category_name', 'p.product_store', 'p.buying_price')
                ->orderByRaw('SUM(CAST(od.quantity AS DECIMAL(10,2))) DESC')
                ->limit(10)
                ->get();

            return $bestSellingProducts;

        } catch (\Exception $e) {
            return collect();
        }
    }
}