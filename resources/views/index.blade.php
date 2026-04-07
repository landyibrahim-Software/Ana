@extends('admin_dashboard')
@section('admin')

@php
use App\Models\Order;
use App\Models\Orderdetails;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Customer;
use Carbon\Carbon;
// Total Stock Value (Buying Price * Quantity in stock)
$totalStockValue = Product::sum(\DB::raw('product_store * buying_price'));
// Today
$today = date('Y-m-d');

// Totals (KEEPING YOUR EXISTING CALCULATIONS)
$totalPaid = Order::sum('pay');
$totalDue  = Order::sum('due');

// Calculate Profit and Loss (KEEPING YOUR EXISTING CALCULATIONS)
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

// Monthly paid data for chart (KEEPING YOUR EXISTING CALCULATIONS)
$monthlyPaid = [];
for ($month = 1; $month <= 12; $month++) {
    $monthlyPaid[] = Order::whereMonth('order_date', $month)
        ->whereYear('order_date', date('Y'))
        ->sum('pay');
}

// ========== NEW ADDITIONS ========== //

// Expenses data (assuming 'amount' column in expenses table)
$totalExpenses = Expense::sum('amount');
$todayExpenses = Expense::whereDate('date', $today)->sum('amount');
$monthlyExpenses = Expense::whereMonth('date', date('m'))
    ->whereYear('date', date('Y'))
    ->sum('amount');

// Recent expenses
$recentExpenses = Expense::orderBy('created_at', 'desc')->take(5)->get();

// Low stock products (assuming 'product_store' is stock column)
$lowStockProducts = Product::where('product_store', '<=', 10)->orderBy('product_store', 'asc')->take(5)->get();

// Today's orders count
$todayOrders = Order::whereDate('order_date', $today)->count();

// Today's total sales
$todaySales = Order::whereDate('order_date', $today)->sum('total');

// Top customers by total spending
$topCustomers = Order::select('customer_id', \DB::raw('SUM(pay) as total_spent'))
    ->groupBy('customer_id')
    ->with('customer')
    ->orderBy('total_spent', 'desc')
    ->take(5)
    ->get();

// Best selling products this month
$bestSellingProducts = Orderdetails::select('product_id', \DB::raw('SUM(quantity) as total_sold'))
    ->whereHas('order', function($query) {
        $query->whereMonth('order_date', date('m'))
              ->whereYear('order_date', date('Y'));
    })
    ->groupBy('product_id')
    ->with('product')
    ->orderBy('total_sold', 'desc')
    ->take(5)
    ->get();

@endphp

<!-- Enhanced Dashboard Styles -->
<style>
/* Your existing styles - KEPT */
.card.widget-rounded-circle {
    border-radius: 12px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    transition: transform 0.2s ease;
}
.card.widget-rounded-circle:hover {
    transform: translateY(-5px);
}
.card .avatar-lg {
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    width: 50px;
    height: 50px;
    border-radius: 50%;
}
.table-centered td, .table-centered th {
    vertical-align: middle !important;
}
.badge {
    font-size: 0.85rem;
    padding: 0.35em 0.65em;
}

/* NEW STYLES */
.stats-card {
    border-left: 4px solid;
    transition: all 0.3s ease;
}
.stats-card:hover {
    transform: translateX(5px);
}
.stats-icon {
    font-size: 24px;
    opacity: 0.8;
}
.small-card {
    height: 100%;
    border: none;
    border-radius: 10px;
    overflow: hidden;
}
.small-card:hover {
    box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
}
.progress-thin {
    height: 6px;
    border-radius: 3px;
}
.product-img-sm {
    width: 40px;
    height: 40px;
    object-fit: cover;
    border-radius: 6px;
    border: 1px solid #dee2e6;
}
.customer-avatar {
    width: 45px;
    height: 45px;
    background: linear-gradient(45deg, #6c5ce7, #a29bfe);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    border-radius: 50%;
}
.dashboard-section-title {
    position: relative;
    padding-bottom: 10px;
    margin-bottom: 20px;
}
.dashboard-section-title:after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 50px;
    height: 3px;
    background: linear-gradient(to right, #4a81d4, #6c5ce7);
    border-radius: 3px;
}
.chart-container {
    position: relative;
    height: 300px;
    width: 100%;
}
</style>

<div class="content">
    <div class="container-fluid">

        <!-- PAGE TITLE -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="page-title-box">
                    <h4 class="page-title" style="font-weight: 600; color: #2c3e50;"> داشبۆردی کوتاڵی نزا </h4>
                    <p class="text-muted mb-0">تێڕوانی عام</p>
                </div>
            </div>
        </div>

        <!-- ========== ROW 1: MAIN KPI CARDS (YOUR EXISTING + NEW) ========== -->
        <div class="row g-3 mb-4">

            <!-- Total Paid (YOUR EXISTING) -->
            <div class="col-md-6 col-xl-3">
                <div class="card widget-rounded-circle bg-primary text-white">
                    <div class="card-body d-flex align-items-center">
                        <div class="avatar-lg rounded-circle bg-white text-primary me-3">
                            <i class="mdi mdi-cash-multiple"></i>
                        </div>
                        <div>
                            <h4 class="mb-1">{{ number_format($totalPaid,2) }}</h4>
                            <p class="mb-0 opacity-75">کۆی گشتی پارەی دراو</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Due (YOUR EXISTING) -->
            <div class="col-md-6 col-xl-3">
                <div class="card widget-rounded-circle bg-warning text-white">
                    <div class="card-body d-flex align-items-center">
                        <div class="avatar-lg rounded-circle bg-white text-warning me-3">
                            <i class="mdi mdi-alert-circle-outline"></i>
                        </div>
                        <div>
                            <h4 class="mb-1">{{ number_format($totalDue,2) }}</h4>
                            <p class="mb-0 opacity-75">کۆی گشتی قەرز</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gross Profit (YOUR EXISTING - MOVED HERE) -->
            <div class="col-md-6 col-xl-3">
                <div class="card widget-rounded-circle bg-success text-white">
                    <div class="card-body d-flex align-items-center">
                        <div class="avatar-lg rounded-circle bg-white text-success me-3">
                            <i class="mdi mdi-trending-up"></i>
                        </div>
                        <div>
                            <h4 class="mb-1">{{ number_format($profit,2) }}</h4>
                            <p class="mb-0 opacity-75">قازانج</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Loss (YOUR EXISTING - MOVED HERE) -->
            <div class="col-md-6 col-xl-3">
                <div class="card widget-rounded-circle bg-danger text-white">
                    <div class="card-body d-flex align-items-center">
                        <div class="avatar-lg rounded-circle bg-white text-danger me-3">
                            <i class="mdi mdi-trending-down"></i>
                        </div>
                        <div>
                            <h4 class="mb-1">{{ number_format($loss,2) }}</h4>
                            <p class="mb-0 opacity-75">زەرەر</p>
                        </div>
                    </div>
                </div>
            </div>

        </div> <!-- end row -->

        <!-- ========== ROW 2: QUICK STATS (NEW) ========== -->
        <div class="row g-3 mb-4">
            
            <!-- Today's Sales -->
            <div class="col-6 col-md-4 col-lg-2">
                <div class="card small-card border-start border-primary">
                    <div class="card-body p-3">
                        <div class="text-center">
                            <div class="stats-icon text-primary mb-2">
                                <i class="mdi mdi-cart"></i>
                            </div>
                            <h6 class="text-muted mb-1">فرۆشتنی ئەمڕۆ</h6>
                            <h4 class="mb-0">{{ number_format($todaySales,2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Today's Orders -->
            <div class="col-6 col-md-4 col-lg-2">
                <div class="card small-card border-start border-info">
                    <div class="card-body p-3">
                        <div class="text-center">
                            <div class="stats-icon text-info mb-2">
                                <i class="mdi mdi-receipt"></i>
                            </div>
                            <h6 class="text-muted mb-1">داواکاری ئەمڕۆ</h6>
                            <h4 class="mb-0">{{ $todayOrders }}</h4>
                        </div>
                    </div>
                </div>
            </div>

 <!-- Total Stock Value -->
<div class="col">
    <div class="card small-card border-start border-success">
        <div class="card-body p-3">
            <div class="text-center">
                <div class="stats-icon text-success mb-2">
                    <i class="mdi mdi-warehouse"></i>
                </div>
                <h6 class="text-muted mb-1">بەهای کاڵای ناو مەخزەن</h6>
                <h4 class="mb-0">{{ number_format($totalStockValue, 2) }}</h4>
            </div>
        </div>
    </div>
</div>

            <!-- Total Expenses -->
            <div class="col-6 col-md-4 col-lg-2">
                <div class="card small-card border-start border-secondary">
                    <div class="card-body p-3">
                        <div class="text-center">
                            <div class="stats-icon text-secondary mb-2">
                                <i class="mdi mdi-cash-remove"></i>
                            </div>
                            <h6 class="text-muted mb-1">کۆی گشتی خەرجی</h6>
                            <h4 class="mb-0">{{ number_format($totalExpenses,2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Net Profit (Profit - Expenses) -->
            <div class="col-6 col-md-4 col-lg-2">
                <div class="card small-card border-start border-success">
                    <div class="card-body p-3">
                        <div class="text-center">
                            <div class="stats-icon text-success mb-2">
                                <i class="mdi mdi-chart-line"></i>
                            </div>
                            <h6 class="text-muted mb-1">خێر-مەسروفات</h6>
                            <h4 class="mb-0">{{ number_format($profit - $totalExpenses,2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>

        </div> <!-- end row -->

        <!-- ========== ROW 3: CHART & TOP CUSTOMERS ========== -->
        <div class="row mb-4">
            <!-- Monthly Paid Chart (YOUR EXISTING) -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <h4 class="dashboard-section-title">کۆی گشتی پارەی مانگانە ({{ date('Y') }})</h4>
                        <div class="chart-container">
                            <canvas id="monthly-paid-chart" height="250"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Customers (NEW) -->
            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h4 class="dashboard-section-title">باشترین کڕیار</h4>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>کڕیار</th>
                                        <th class="text-end">کۆی گشتی کڕین</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($topCustomers as $customerData)
                                        @if($customerData->customer)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="customer-avatar me-2">
                                                        {{ substr($customerData->customer->name, 0, 1) }}
                                                    </div>
                                                    <div>
                                                        <strong>{{ $customerData->customer->name }}</strong>
                                                        <br>
                                                        <small class="text-muted">{{ $customerData->customer->email ?? 'No email' }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-end">
                                                <strong class="text-success">${{ number_format($customerData->total_spent,2) }}</strong>
                                            </td>
                                        </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div> <!-- end row -->

        <!-- ========== ROW 4: EXPENSES & LOW STOCK ========== -->
        <div class="row mb-4">
            <!-- Recent Expenses (NEW) -->
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h4 class="dashboard-section-title">خەرجی تازە</h4>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>وردەکاری</th>
                                        <th class="text-end">بڕ</th>
                                        <th>بەروار</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentExpenses as $expense)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="mdi mdi-cash-remove text-danger me-2"></i>
                                                <span>{{ $expense->details ?? 'Expense' }}</span>
                                            </div>
                                        </td>
                                        <td class="text-end text-danger">
                                            <strong>-${{ number_format($expense->amount,2) }}</strong>
                                        </td>
                                        <td>
                                            <small>{{ \Carbon\Carbon::parse($expense->created_at)->format('d M Y') }}</small>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Low Stock Alert (NEW) -->
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h4 class="dashboard-section-title">ئاگاداری کەمبونەوە</h4>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>بەرهەم</th>
                                        <th>عدد</th>
                                        <th>دۆخ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($lowStockProducts as $product)
                                    @php
                                        $stockPercentage = ($product->product_store / 10) * 100;
                                        $statusColor = $product->product_store <= 3 ? 'danger' : ($product->product_store <= 5 ? 'warning' : 'info');
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($product->product_image)
                                                <img src="{{ asset($product->product_image) }}" 
                                                     class="product-img-sm me-2" 
                                                     alt="{{ $product->product_name }}">
                                                @endif
                                                <span class="text-truncate" style="max-width: 150px;">
                                                    {{ $product->product_name }}
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <strong class="text-{{ $statusColor }}">{{ $product->product_store }}</strong>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="progress progress-thin flex-grow-1 me-2">
                                                    <div class="progress-bar bg-{{ $statusColor }}" 
                                                         role="progressbar" 
                                                         style="width: {{ min($stockPercentage, 100) }}%">
                                                    </div>
                                                </div>
                                                @if($product->product_store <= 3)
                                                <span class="badge bg-danger">گرنگ</span>
                                                @elseif($product->product_store <= 5)
                                                <span class="badge bg-warning">کەم</span>
                                                @else
                                                <span class="badge bg-info">ئاگاداری</span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div> <!-- end row -->

        <!-- ========== ROW 5: BEST SELLING PRODUCTS (NEW) ========== -->
        <div class="row mb-4">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="dashboard-section-title">باشترین بەرهەمی فرۆشراوی مانگ</h4>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>بەرهەم</th>
                                        <th>کۆد</th>
                                        <th>جۆر</th>
                                        <th class="text-center">دەرزەن فرۆشراو</th>
                                        <th class="text-center">چەند ماوە</th>
                                        
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($bestSellingProducts as $item)
                                        @if($item->product)
                                        @php
                                            // Estimate revenue (unitcost * quantity)
                                            $revenue = $item->unitcost * $item->total_sold;
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($item->product->product_image)
                                                    <img src="{{ asset($item->product->product_image) }}" 
                                                         class="product-img-sm me-2" 
                                                         alt="{{ $item->product->product_name }}">
                                                    @endif
                                                    <span>{{ $item->product->product_name }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark">{{ $item->product->product_code }}</span>
                                            </td>
                                            <td>
                                                {{ $item->product->category->name ?? 'N/A' }}
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-primary">{{ $item->total_sold }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-{{ $item->product->product_store > 10 ? 'success' : 'warning' }}">
                                                    {{ $item->product->product_store }}
                                                </span>
                                            </td>
                                            
                                        </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div> <!-- end row -->

        <!-- ========== ROW 6: RECENT ORDERS TABLE (YOUR EXISTING - UNCHANGED) ========== -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="dashboard-section-title">دۆخی فرۆشتنەکان</h4>
                        <div class="table-responsive">
                            <table class="table table-bordered table-centered table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>ژمارەی پسوڵە</th>
                                        <th>کڕیار</th>
                                        <th>ئایتم</th>
                                        <th>کۆی گشتی</th>
                                        <th>پارەی دراو</th>
                                        <th>قەرز</th>
                                        <th>قازانج/زەرەر</th>
                                        <th>شێوازی پارەدان</th>
                                        <th>دۆخ</th>
                                        <th>بەروار</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($orders->sortByDesc('created_at')->take(10) as $order)
                                    @php
                                        $itemCount = $order->orderItems->sum('quantity');
                                        $orderProfit = 0;
                                        foreach($order->orderItems as $item) {
                                            $buyingPrice = $item->product->buying_price ?? 0;
                                            $sellingPrice = $item->unitcost;
                                            $orderProfit += ($sellingPrice - $buyingPrice) * $item->quantity;
                                        }
                                    @endphp
                                    <tr>
                                        <td>{{ $order->invoice_no }}</td>
                                        <td>{{ $order->customer->name ?? 'N/A' }}</td>
                                        <td>{{ $itemCount }}</td>
                                        <td>${{ number_format($order->total,2) }}</td>
                                        <td>${{ number_format($order->pay,2) }}</td>
                                        <td>${{ number_format($order->due,2) }}</td>
                                        <td>
                                            @if($orderProfit >= 0)
                                                <span class="badge bg-success">+${{ number_format($orderProfit,2) }}</span>
                                            @else
                                                <span class="badge bg-danger">-${{ number_format(abs($orderProfit),2) }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $order->payment_status ?? 'N/A' }}</td>
                                        <td>
                                            @if($order->order_status == 'complete')
                                                <span class="badge bg-success">تەواو</span>
                                            @else
                                                <span class="badge bg-warning">چاوەروانی</span>
                                            @endif
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($order->order_date)->format('d-M-Y') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div> <!-- table-responsive -->
                    </div>
                </div>
            </div>
        </div> <!-- end row -->

    </div> <!-- container -->
</div> <!-- content -->

<!-- Chart.js Script (YOUR EXISTING - UNCHANGED) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('monthly-paid-chart').getContext('2d');
const monthlyPaidChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
        datasets: [{
            label: 'Paid Amount ($)',
            data: @json($monthlyPaid),
            backgroundColor: '#4a81d4',
            borderRadius: 6,
            borderSkipped: false,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: { 
                mode: 'index', 
                intersect: false,
                backgroundColor: 'rgba(0,0,0,0.7)',
                padding: 12,
                cornerRadius: 6,
            }
        },
        scales: {
            y: { 
                beginAtZero: true,
                grid: {
                    drawBorder: false,
                }
            },
            x: {
                grid: {
                    display: false
                }
            }
        }
    }
});
</script>

@endsection