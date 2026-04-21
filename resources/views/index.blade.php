@extends('admin_dashboard')
@section('admin')

<!-- Enhanced Dashboard Styles -->
<style>
/* COLORFUL KPI CARDS */
.kpi-card {
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    overflow: hidden;
    transition: all 0.3s ease;
    border: none;
}

.kpi-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.15);
}

.kpi-card .card-body {
    padding: 25px;
}

.kpi-card h4 {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 10px;
}

.kpi-card p {
    font-size: 0.95rem;
    margin-bottom: 0;
    opacity: 0.9;
}

/* Gradient Backgrounds */
.kpi-gradient-1 {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.kpi-gradient-2 {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
}

.kpi-gradient-3 {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    color: white;
}

.kpi-gradient-4 {
    background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
    color: white;
}

.kpi-gradient-5 {
    background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
    color: white;
}

.kpi-gradient-6 {
    background: linear-gradient(135deg, #30cfd0 0%, #330867 100%);
    color: white;
}

.kpi-icon {
    font-size: 3rem;
    opacity: 0.2;
    position: absolute;
    top: -10px;
    right: 20px;
}

/* BEAUTIFUL FILTER STYLES */
.filter-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.2);
}

.filter-section h6 {
    color: white;
    font-weight: 600;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.filter-section h6 i {
    font-size: 1.2rem;
}

.filter-select {
    border-radius: 8px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    padding: 10px 12px;
    background: white;
    color: #333;
    font-weight: 500;
    transition: all 0.3s ease;
}

.filter-select:focus {
    border-color: white;
    box-shadow: 0 0 10px rgba(255, 255, 255, 0.5);
}

.filter-inputs {
    display: flex;
    gap: 10px;
    align-items: flex-end;
}

.filter-inputs input {
    border-radius: 8px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    padding: 10px 12px;
    background: white;
    color: #333;
    font-weight: 500;
    flex: 1;
}

.filter-inputs input:focus {
    border-color: white;
    outline: none;
}

.filter-btn {
    background: white;
    color: #667eea;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.filter-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.date-range-display {
    color: rgba(255, 255, 255, 0.9);
    font-size: 0.9rem;
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1px solid rgba(255, 255, 255, 0.2);
}

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
                    <h4 class="page-title" style="font-weight: 600; color: #2c3e50;">  Anna Group Dashboard  </h4>
                    <p class="text-muted mb-0">تێڕوانی عام</p>
                </div>
            </div>
        </div>

        <!-- ========== BEAUTIFUL DATE FILTER ========== -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="filter-section">
                    <form method="GET" action="{{ route('dashboard') }}" id="filterForm">
                        <h6><i class="mdi mdi-calendar-range"></i> بەروار هەڵبژێرە</h6>
                        
                        <div class="row g-3">
                            <!-- Filter Dropdown -->
                            <div class="col-md-6">
                                <label style="color: white; font-weight: 500; display: block; margin-bottom: 8px;">
                                    <i class="mdi mdi-filter"></i> فیلتەر
                                </label>
                                <select name="filter" id="filterType" class="form-control filter-select" onchange="handleFilterChange()">
                                    <option value="today" {{ request('filter') == 'today' ? 'selected' : '' }}>📅 ئەمڕۆ</option>
                                    <option value="yesterday" {{ request('filter') == 'yesterday' ? 'selected' : '' }}>📆 دوێنێ</option>
                                    <option value="last_week" {{ request('filter') == 'last_week' ? 'selected' : '' }}>📊 هەفتەی پێشوو</option>
                                    <option value="last_month" {{ request('filter') == 'last_month' ? 'selected' : '' }}>📈 مانگی پێشوو</option>
                                    <option value="last_year" {{ request('filter') == 'last_year' ? 'selected' : '' }}>📉 ساڵی پێشوو</option>
                                    <option value="custom" {{ request('filter') == 'custom' ? 'selected' : '' }}>📋 دیاریکردن</option>
                                </select>
                            </div>

                            <!-- Custom Date Range -->
                            <div class="col-md-6" id="customDateRange" style="display: {{ request('filter') == 'custom' ? 'block' : 'none' }};">
                                <div class="filter-inputs">
                                    <div style="flex: 1;">
                                        <label style="color: white; font-weight: 500; display: block; margin-bottom: 8px; font-size: 0.9rem;">
                                            <i class="mdi mdi-calendar-start"></i> سەرەتا
                                        </label>
                                        <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                                    </div>
                                    <div style="flex: 1;">
                                        <label style="color: white; font-weight: 500; display: block; margin-bottom: 8px; font-size: 0.9rem;">
                                            <i class="mdi mdi-calendar-end"></i> کۆتایی
                                        </label>
                                        <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                                    </div>
                                    <button type="submit" class="filter-btn">✓ گەڕان</button>
                                </div>
                            </div>
                        </div>

                        <!-- Date Range Display -->
                        <div class="date-range-display" id="dateRangeDisplay">
                            <i class="mdi mdi-information"></i>
                            @if(request('filter') == 'today')
                                ئەمڕۆ: {{ \Carbon\Carbon::now()->format('Y-m-d') }}
                            @elseif(request('filter') == 'yesterday')
                                دوێنێ: {{ \Carbon\Carbon::yesterday()->format('Y-m-d') }}
                            @elseif(request('filter') == 'last_week')
                                ئەم هێمێی ({{ \Carbon\Carbon::now()->subWeek()->startOfWeek()->format('Y-m-d') }} بۆ {{ \Carbon\Carbon::now()->endOfWeek()->format('Y-m-d') }})
                            @elseif(request('filter') == 'last_month')
                                مانگی دواتر ({{ \Carbon\Carbon::now()->subMonth()->startOfMonth()->format('Y-m-d') }} بۆ {{ \Carbon\Carbon::now()->endOfMonth()->format('Y-m-d') }})
                            @elseif(request('filter') == 'last_year')
                                ساڵی دواتر ({{ \Carbon\Carbon::now()->subYear()->startOfYear()->format('Y-m-d') }} بۆ {{ \Carbon\Carbon::now()->endOfYear()->format('Y-m-d') }})
                            @elseif(request('filter') == 'custom')
                                دیاریکراو ({{ request('start_date') }} بۆ {{ request('end_date') }})
                            @else
                                ئەمڕۆ: {{ \Carbon\Carbon::now()->format('Y-m-d') }}
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
        function handleFilterChange() {
            var filterType = document.getElementById('filterType').value;
            if (filterType === 'custom') {
                document.getElementById('customDateRange').style.display = 'block';
            } else {
                document.getElementById('customDateRange').style.display = 'none';
                document.getElementById('filterForm').submit();
            }
        }
        </script>

        <!-- ========== ROW 1: COLORFUL KPI CARDS ========== -->
        <div class="row g-3 mb-4">

            <!-- Total Paid -->
            <div class="col-md-6 col-lg-4">
                <div class="card kpi-card kpi-gradient-1 position-relative">
                    <i class="mdi mdi-cash-multiple kpi-icon"></i>
                    <div class="card-body">
                        <h4>${{ number_format($totalPaid ?? 0, 2) }}</h4>
                        <p>کۆی گشتی پارەی دراو<br><small style="opacity: 0.7; font-size: 0.8rem;">(داواکاری + پارەدانی کڕیار)</small></p>
                    </div>
                </div>
            </div>

            <!-- Total Due -->
            <div class="col-md-6 col-lg-4">
                <div class="card kpi-card kpi-gradient-2 position-relative">
                    <i class="mdi mdi-alert-circle kpi-icon"></i>
                    <div class="card-body">
                        <h4>${{ number_format($totalDue ?? 0, 2) }}</h4>
                        <p>کۆی گشتی قەرز<br><small style="opacity: 0.7; font-size: 0.8rem;">(داواکاری + کڕیار)</small></p>
                    </div>
                </div>
            </div>

            <!-- Gross Profit -->
            <div class="col-md-6 col-lg-4">
                <div class="card kpi-card kpi-gradient-3 position-relative">
                    <i class="mdi mdi-trending-up kpi-icon"></i>
                    <div class="card-body">
                        <h4>${{ number_format($profit ?? 0, 2) }}</h4>
                        <p>قازانج</p>
                    </div>
                </div>
            </div>

            <!-- Total Loss -->
            <div class="col-md-6 col-lg-4">
                <div class="card kpi-card kpi-gradient-4 position-relative">
                    <i class="mdi mdi-trending-down kpi-icon"></i>
                    <div class="card-body">
                        <h4>${{ number_format($loss ?? 0, 2) }}</h4>
                        <p>زەرەر</p>
                    </div>
                </div>
            </div>

            <!-- Supplier Payments -->
            <div class="col-md-6 col-lg-4">
                <div class="card kpi-card kpi-gradient-5 position-relative">
                    <i class="mdi mdi-bank-transfer kpi-icon"></i>
                    <div class="card-body">
                        <h4>${{ number_format($totalSupplierPayment ?? 0, 2) }}</h4>
                        <p>پارەدانی دابینکەر</p>
                    </div>
                </div>
            </div>

            <!-- Stock Value -->
            <div class="col-md-6 col-lg-4">
                <div class="card kpi-card kpi-gradient-6 position-relative">
                    <i class="mdi mdi-warehouse kpi-icon"></i>
                    <div class="card-body">
                        <h4>${{ number_format($totalStockValue ?? 0, 2) }}</h4>
                        <p>بەهای کاڵای ناو مەخزەن</p>
                    </div>
                </div>
            </div>

        </div> <!-- end row -->

        <!-- ========== ROW 2: COLORFUL QUICK STATS ========== -->
        <div class="row g-3 mb-4">
            
            <!-- Today's Sales -->
            <div class="col-md-6 col-lg-4">
                <div class="card kpi-card kpi-gradient-1 position-relative">
                    <i class="mdi mdi-cart kpi-icon"></i>
                    <div class="card-body">
                        <h4>${{ number_format($todaySales ?? 0, 2) }}</h4>
                        <p>فرۆشتنی ئەمڕۆ</p>
                    </div>
                </div>
            </div>

            <!-- Today's Orders -->
            <div class="col-md-6 col-lg-4">
                <div class="card kpi-card kpi-gradient-3 position-relative">
                    <i class="mdi mdi-receipt kpi-icon"></i>
                    <div class="card-body">
                        <h4>{{ $todayOrders ?? 0 }}</h4>
                        <p>داواکاری ئەمڕۆ</p>
                    </div>
                </div>
            </div>

            <!-- Total Expenses -->
            <div class="col-md-6 col-lg-4">
                <div class="card kpi-card kpi-gradient-5 position-relative">
                    <i class="mdi mdi-cash-remove kpi-icon"></i>
                    <div class="card-body">
                        <h4>${{ number_format($totalExpenses ?? 0, 2) }}</h4>
                        <p>کۆی گشتی خەرجی</p>
                    </div>
                </div>
            </div>

            <!-- Net Profit -->
            <div class="col-md-6 col-lg-4">
                <div class="card kpi-card kpi-gradient-2 position-relative">
                    <i class="mdi mdi-chart-line kpi-icon"></i>
                    <div class="card-body">
                        <h4>${{ number_format(($profit ?? 0) - ($totalExpenses ?? 0), 2) }}</h4>
                        <p>خێر-مەسروفات</p>
                    </div>
                </div>
            </div>

        </div> <!-- end row -->

        <!-- ========== ROW 3: CHART & TOP CUSTOMERS ========== -->
        <div class="row mb-4">
            <!-- Monthly Paid Chart -->
            <div class="col-lg-8">
                <div class="card" style="border-radius: 12px; box-shadow: 0 5px 20px rgba(0,0,0,0.08); border: none; border-top: 4px solid #667eea;">
                    <div class="card-body">
                        <h4 class="dashboard-section-title">کۆی گشتی پارەی مانگانە ({{ date('Y') }})</h4>
                        <div class="chart-container">
                            <canvas id="monthly-paid-chart" height="250"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Customers -->
            <div class="col-lg-4">
                <div class="card" style="border-radius: 12px; box-shadow: 0 5px 20px rgba(0,0,0,0.08); border: none; border-top: 4px solid #f5576c; height: 100%;">
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
                                    @forelse($topCustomers ?? [] as $customerData)
                                        @if($customerData->customer)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="customer-avatar me-2">
                                                        {{ substr($customerData->customer->name ?? 'U', 0, 1) }}
                                                    </div>
                                                    <div>
                                                        <strong>{{ $customerData->customer->name ?? '-' }}</strong>
                                                        <br>
                                                        <small class="text-muted">{{ $customerData->customer->phone ?? 'No Phone' }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-end">
                                                <strong class="text-success">${{ number_format($customerData->total_spent ?? 0, 2) }}</strong>
                                            </td>
                                        </tr>
                                        @endif
                                    @empty
                                    <tr>
                                        <td colspan="2" class="text-center text-muted">کڕیار نیە</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div> <!-- end row -->

        <!-- ========== ROW 4: EXPENSES & SUPPLIER PAYMENTS ========== -->
        <div class="row mb-4">
            <!-- Recent Expenses -->
            <div class="col-lg-6">
                <div class="card" style="border-radius: 12px; box-shadow: 0 5px 20px rgba(0,0,0,0.08); border: none; border-top: 4px solid #fa709a; height: 100%;">
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
                                    @forelse($recentExpenses ?? [] as $expense)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="mdi mdi-cash-remove text-danger me-2"></i>
                                                <span>خەرجی</span>
                                            </div>
                                        </td>
                                        <td class="text-end text-danger">
                                            <strong>-${{ number_format($expense->amount ?? 0, 2) }}</strong>
                                        </td>
                                        <td>
                                            <small>{{ \Carbon\Carbon::parse($expense->created_at)->format('d M Y') }}</small>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">خەرجی نیە</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SUPPLIER PAYMENTS TABLE -->
            <div class="col-lg-6">
                <div class="card" style="border-radius: 12px; box-shadow: 0 5px 20px rgba(0,0,0,0.08); border: none; border-top: 4px solid #4facfe; height: 100%;">
                    <div class="card-body">
                        <h4 class="dashboard-section-title">پارەدانی دابینکەر</h4>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>تۆمار</th>
                                        <th class="text-end">بڕ</th>
                                        <th>بەروار</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentSupplierPayments ?? [] as $payment)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="mdi mdi-bank-transfer text-success me-2"></i>
                                                <span>{{ $payment->supplier->supplier_name ?? 'نەناسراو' }}</span>
                                            </div>
                                        </td>
                                        <td class="text-end text-success">
                                            <strong>+${{ number_format($payment->payment_amount ?? 0, 2) }}</strong>
                                        </td>
                                        <td>
                                            <small>{{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}</small>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">پارەدانی نیە</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div> <!-- end row -->

        <!-- ========== ROW 5: LOW STOCK ========== -->
        <div class="row mb-4">
            <div class="col-lg-12">
                <div class="card" style="border-radius: 12px; box-shadow: 0 5px 20px rgba(0,0,0,0.08); border: none; border-top: 4px solid #43e97b;">
                    <div class="card-body">
                        <h4 class="dashboard-section-title">ئاگاداری کەمبونەوە</h4>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>بەرهەم</th>
                                        <th>بڕ</th>
                                        <th>دۆخ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($lowStockProducts ?? [] as $product)
                                    @php
                                        $stockPercentage = ($product->product_store / 10) * 100;
                                        $statusColor = $product->product_store <= 3 ? 'danger' : ($product->product_store <= 5 ? 'warning' : 'info');
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($product->product_image && file_exists(public_path($product->product_image)))
                                                <img src="{{ asset($product->product_image) }}" 
                                                     class="product-img-sm me-2" 
                                                     alt="{{ $product->product_name }}">
                                                @else
                                                <img src="https://via.placeholder.com/40?text=No" class="product-img-sm me-2" alt="No Image">
                                                @endif
                                                <span class="text-truncate" style="max-width: 150px;">
                                                    {{ $product->product_name ?? '-' }}
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <strong class="text-{{ $statusColor }}">{{ number_format($product->product_store ?? 0, 2) }}</strong>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="progress progress-thin flex-grow-1 me-2" style="height: 6px;">
                                                    <div class="progress-bar bg-{{ $statusColor }}" 
                                                         role="progressbar" 
                                                         style="width: {{ min($stockPercentage, 100) }}%"
                                                         aria-valuenow="{{ min($stockPercentage, 100) }}" 
                                                         aria-valuemin="0" 
                                                         aria-valuemax="100">
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
                                    @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">بەرهەمی کەم نیە</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div> <!-- end row -->

        <!-- ========== ROW 6: BEST SELLING PRODUCTS ========== -->
        <div class="row mb-4">
            <div class="col-lg-12">
                <div class="card" style="border-radius: 12px; box-shadow: 0 5px 20px rgba(0,0,0,0.08); border: none; border-top: 4px solid #30cfd0;">
                    <div class="card-body">
                        <h4 class="dashboard-section-title">باشترین بەرهەمی فرۆشراوی مانگ</h4>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>بەرهەم</th>
                                        <th>کۆد</th>
                                        <th>جۆر</th>
                                        <th class="text-center">فرۆشتنی مانگ</th>
                                        <th class="text-center">ماوە</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($bestSellingProducts ?? [] as $item)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($item->product_image && file_exists(public_path($item->product_image)))
                                                <img src="{{ asset($item->product_image) }}" 
                                                     class="product-img-sm me-2" 
                                                     alt="{{ $item->product_name ?? '-' }}">
                                                @else
                                                <img src="https://via.placeholder.com/40?text=No" class="product-img-sm me-2" alt="No Image">
                                                @endif
                                                <span>{{ $item->product_name ?? '-' }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark">{{ $item->product_code ?? '-' }}</span>
                                        </td>
                                        <td>
                                            {{ $item->category_name ?? 'N/A' }}
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-primary">{{ number_format($item->total_sold ?? 0, 2) }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-{{ ($item->product_store ?? 0) > 50 ? 'success' : 'warning' }}">
                                                {{ number_format($item->product_store ?? 0, 2) }}
                                            </span>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">بەرهەم نیە</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div> <!-- end row -->

        <!-- ========== ROW 7: RECENT ORDERS TABLE ========== -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card" style="border-radius: 12px; box-shadow: 0 5px 20px rgba(0,0,0,0.08); border: none; border-top: 4px solid #667eea;">
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
                                    @forelse($orders ?? [] as $order)
                                    @php
                                        $itemCount = $order->orderDetails ? $order->orderDetails->sum('quantity') : 0;
                                        $orderProfit = 0;
                                        
                                        if($order->orderDetails) {
                                            foreach($order->orderDetails as $item) {
                                                $buyingPrice = floatval($item->product->buying_price ?? 0);
                                                $sellingPrice = floatval($item->unitcost ?? 0);
                                                $quantity = floatval($item->quantity ?? 0);
                                                
                                                $orderProfit += ($sellingPrice - $buyingPrice) * $quantity;
                                            }
                                        }
                                    @endphp
                                    <tr>
                                        <td><strong>#{{ $order->id }}</strong></td>
                                        <td>{{ $order->customer->name ?? 'N/A' }}</td>
                                        <td>{{ $itemCount }}</td>
                                        <td><strong>${{ number_format($order->sub_total ?? 0, 2) }}</strong></td>
                                        <td class="text-success"><strong>${{ number_format($order->pay ?? 0, 2) }}</strong></td>
                                        <td class="text-danger"><strong>${{ number_format($order->due ?? 0, 2) }}</strong></td>
                                        <td>
                                            @if($orderProfit >= 0)
                                                <span class="badge bg-success">+${{ number_format($orderProfit, 2) }}</span>
                                            @else
                                                <span class="badge bg-danger">-${{ number_format(abs($orderProfit), 2) }}</span>
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
                                        <td><small>{{ \Carbon\Carbon::parse($order->order_date)->format('d-M-Y') }}</small></td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="10" class="text-center text-muted">داواکاری نیە</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div> <!-- end row -->

    </div> <!-- container -->
</div> <!-- content -->

<!-- Chart.js Script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const chartData = {!! json_encode($monthlyPaid ?? [0,0,0,0,0,0,0,0,0,0,0,0]) !!};
        
        const ctx = document.getElementById('monthly-paid-chart');
        if (ctx) {
            const monthlyPaidChart = new Chart(ctx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
                    datasets: [{
                        label: 'Paid Amount ($)',
                        data: chartData,
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
        }
    });
</script>

@endsection

