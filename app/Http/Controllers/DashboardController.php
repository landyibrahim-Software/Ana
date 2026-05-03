<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Payment;
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

            // ✅ TOTAL PAID — invoice payments (orders.pay, non-cancelled) + show-customer payments (payments table)
            $totalPaidOrders   = Order::whereBetween('created_at', [$startDate, $endDate])
                ->where('order_status', '!=', 'cancelled')
                ->sum('pay') ?? 0;
            $totalPaidCustomer = Payment::whereBetween('payment_date', [$startDate, $endDate])
                ->where('payment_status', 'completed')
                ->sum('payment_amount') ?? 0;
            $totalPaid = floatval($totalPaidOrders) + floatval($totalPaidCustomer);

            // ✅ TOTAL DUE — real-time outstanding balance across all customers (customers.due is
            //    always kept up-to-date by both FinalInvoice and PaymentCustomer, while orders.due
            //    is never reduced after a show-customer payment, so it must NOT be used here)
            $totalDue = Customer::where('due', '>', 0)->sum('due') ?? 0;

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

            // ✅ TOTAL STOCK VALUE — single SQL aggregation, no PHP loop
            $totalStockValue = Product::selectRaw(
                'COALESCE(SUM(CAST(buying_price AS DECIMAL(15,4)) * CAST(product_store AS DECIMAL(15,4))), 0) as total'
            )->value('total') ?? 0;

            // ✅ TODAY'S SALES + COUNT — single query instead of two
            $todayRow = Order::whereDate('created_at', Carbon::today())
                ->where('order_status', '!=', 'cancelled')
                ->selectRaw('COALESCE(SUM(sub_total), 0) as sales, COUNT(*) as cnt')
                ->first();
            $todaySales  = floatval($todayRow->sales ?? 0);
            $todayOrders = intval($todayRow->cnt ?? 0);

            // ✅ TOTAL EXPENSES
            $totalExpenses = Expense::whereBetween('created_at', [$startDate, $endDate])
                ->sum('amount') ?? 0;

            // ✅ RECENT ORDERS (Last 10) - restrict columns to avoid loading unused data
            $orders = Order::whereBetween('created_at', [$startDate, $endDate])
                ->with([
                    'customer:id,name,phone',
                    'orderDetails:id,order_id,product_id,quantity,unitcost',
                    'orderDetails.product:id,buying_price',
                ])
                ->select(['id', 'customer_id', 'invoice_no', 'order_date', 'order_status', 'payment_status', 'sub_total', 'pay', 'due', 'created_at'])
                ->latest()
                ->limit(10)
                ->get();

            // ✅ TOP CUSTOMERS - restrict customer columns
            $topCustomers = Order::whereBetween('created_at', [$startDate, $endDate])
                ->select('customer_id', DB::raw('SUM(sub_total) as total_spent'))
                ->with('customer:id,name,phone')
                ->groupBy('customer_id')
                ->orderByDesc('total_spent')
                ->limit(5)
                ->get();

            // ✅ RECENT EXPENSES - restrict to columns the view uses
            $recentExpenses = Expense::whereBetween('created_at', [$startDate, $endDate])
                ->select(['id', 'amount', 'created_at'])
                ->latest()
                ->limit(5)
                ->get();

            // ✅ RECENT SUPPLIER PAYMENTS - restrict columns: view uses supplier.name, payment_amount, payment_date only
            $recentSupplierPayments = SupplierPayment::whereBetween('payment_date', [$startDate, $endDate])
                ->select(['id', 'supplier_id', 'payment_amount', 'payment_date'])
                ->with('supplier:id,name')
                ->latest()
                ->limit(5)
                ->get();

            // ✅ LOW STOCK PRODUCTS - restrict columns
            $lowStockProducts = Product::where('product_store', '<', 10)
                ->select(['id', 'product_name', 'product_code', 'product_image', 'product_store'])
                ->latest()
                ->limit(10)
                ->get();

            // ✅ BEST SELLING PRODUCTS — direct SQL GROUP BY (avoids loading all orders into PHP)
            $bestSellingProducts = collect(DB::select("
                SELECT p.id as product_id, p.product_name, p.product_code, p.product_image,
                       CAST(p.product_store AS DECIMAL(15,4)) as product_store,
                       COALESCE(c.category_name, 'N/A') as category_name,
                       SUM(CAST(od.quantity AS DECIMAL(15,4))) as total_sold
                FROM orderdetails od
                INNER JOIN orders o ON od.order_id = o.id
                INNER JOIN products p ON od.product_id = p.id
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE MONTH(o.created_at) = ? AND YEAR(o.created_at) = ?
                  AND o.order_status != 'cancelled'
                GROUP BY p.id, p.product_name, p.product_code, p.product_image, p.product_store, c.category_name
                ORDER BY total_sold DESC
                LIMIT 10
            ", [Carbon::now()->month, Carbon::now()->year]));

           
            // ✅ MONTHLY PAID DATA — UNION both orders.pay and payments.payment_amount (exclude cancelled)
            $monthlyRows = DB::select("
                SELECT month, SUM(amount) AS amount FROM (
                    SELECT MONTH(created_at) AS month,
                           SUM(CAST(pay AS DECIMAL(15,4))) AS amount
                    FROM orders
                    WHERE YEAR(created_at) = ?
                      AND order_status != 'cancelled'
                    GROUP BY MONTH(created_at)
                    UNION ALL
                    SELECT MONTH(payment_date) AS month,
                           SUM(CAST(payment_amount AS DECIMAL(15,4))) AS amount
                    FROM payments
                    WHERE YEAR(payment_date) = ?
                      AND payment_status = 'completed'
                    GROUP BY MONTH(payment_date)
                ) AS combined
                GROUP BY month
            ", [Carbon::now()->year, Carbon::now()->year]);
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