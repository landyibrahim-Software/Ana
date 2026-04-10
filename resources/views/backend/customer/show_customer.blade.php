@extends('admin_dashboard')
@section('admin')

<div class="content">
    <div class="container-fluid">
        
        <!-- Page Title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <a href="{{ route('all.customer') }}" class="btn btn-secondary rounded-pill waves-effect waves-light">
                                <i class="fa fa-arrow-left"></i> گەڕانەوە
                            </a>
                        </ol>
                    </div>
                    <h4 class="page-title">پڕۆفایلی کڕیار - {{ $customer->name }}</h4>
                </div>
            </div>
        </div>

        <!-- Customer Info -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <img src="{{ asset($customer->image) }}" class="rounded-circle avatar-lg img-thumbnail" alt="customer-image">
                        <h5 class="mt-3">{{ $customer->name }}</h5>
                        <p class="text-muted">{{ $customer->shopname }}</p>
                        <p><strong>ژمارە:</strong> {{ $customer->phone }}</p>
                    </div>
                </div>
            </div>

            <!-- Financial Summary Cards -->
            <div class="col-md-9">
                <div class="row">
                    <!-- Card 1: Total Orders -->
                    <div class="col-md-3">
                        <div class="card bg-info bg-opacity-10 border-info">
                            <div class="card-body">
                                <h6 class="card-title text-info mb-3">کۆی داواکاریەکان</h6>
                                <h3 class="text-info mb-0">{{ $order_count }}</h3>
                                <small class="text-muted">جموو داواکاریەکان</small>
                            </div>
                        </div>
                    </div>

                   <!-- Card 2: Total Money (Previous Due + Orders) -->
<div class="col-md-3">
    <div class="card bg-warning bg-opacity-10 border-warning">
        <div class="card-body">
            <h6 class="card-title text-warning mb-3">کۆی پێداویستی</h6>
            <h3 class="text-warning mb-0">${{ number_format($total_spent_display, 2) }}</h3>
            <small class="text-muted">قەرزی سەرەتایی + داواکاریەکان</small>
        </div>
    </div>
</div>

                    <!-- Card 3: Total Paid -->
                    <div class="col-md-3">
                        <div class="card bg-success bg-opacity-10 border-success">
                            <div class="card-body">
                                <h6 class="card-title text-success mb-3">کۆی پارەی دراو</h6>
                                <h3 class="text-success mb-0">${{ number_format($total_paid_all, 2) }}</h3>
                                <small class="text-muted">لە هەموو پارەدانەکان</small>
                            </div>
                        </div>
                    </div>

                    <!-- Card 4: Total Due Remaining -->
                    <div class="col-md-3">
                        <div class="card bg-danger bg-opacity-10 border-danger">
                            <div class="card-body">
                                <h6 class="card-title text-danger mb-3">کۆی قەرز</h6>
                                <h3 class="text-danger mb-0">${{ number_format($total_due, 2) }}</h3>
                                <small class="text-muted">چقدر قەرز مابوو</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-primary">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fa fa-money"></i> پارە وەربگرە</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('payment.customer') }}">
                            @csrf
                            <div class="row">
                                <input type="hidden" name="customer_id" value="{{ $customer->id }}">
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">مبلغ پارە (دۆلار)</label>
                                        <input type="number" name="payment_amount" class="form-control" style="height: 45px;" step="0.01" min="0" max="{{ $total_due }}" placeholder="مبلغی پارە بنوسە" required>
                                        <small class="text-muted">گەیاندی: ${{ number_format($total_due, 2) }}</small>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">&nbsp;</label>
                                        <button type="submit" class="btn btn-primary waves-effect waves-light w-100" style="height: 45px;">
                                            <i class="fa fa-check-circle"></i> قبوڵ کردنی پارە
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Orders & Payments Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">مێژوی داواکاریەکان و پارەدانەکان</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th style="color: white; background-color: #343a40;">جۆر</th>
                                        <th style="color: white; background-color: #343a40;">بڕ</th>
                                        <th style="color: white; background-color: #343a40;">دۆخ</th>
                                        <th style="color: white; background-color: #343a40;">بەرواری</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $combined_records = collect();
                                        
                                        // Add orders
                                        foreach ($customer->orders as $order) {
                                            $combined_records->push([
                                                'type' => 'order',
                                                'id' => $order->id,
                                                'amount' => $order->sub_total ?? 0,
                                                'date' => $order->created_at,
                                                'data' => $order
                                            ]);
                                        }
                                        
                                        // Add payments
                                        foreach ($customer->payments as $payment) {
                                            $combined_records->push([
                                                'type' => 'payment',
                                                'id' => $payment->id,
                                                'amount' => $payment->payment_amount,
                                                'date' => $payment->payment_date,
                                                'data' => $payment
                                            ]);
                                        }
                                        
                                        // Sort by date (newest first)
                                        $combined_records = $combined_records->sortByDesc('date');
                                    @endphp
                                    
                                    @forelse($combined_records as $record)
                                        @if($record['type'] == 'order')
                                            @php
                                                $order = $record['data'];
                                                $order_total = $order->sub_total ?? 0;
                                                $order_paid = $order->pay ?? 0;
                                                $order_due = max($order_total - $order_paid, 0);
                                                $is_paid = $order_due == 0;
                                            @endphp
                                            <tr>
                                                <td><span class="badge bg-info text-white">داواکاری #{{ $order->id }}</span></td>
                                                <td><strong>${{ number_format($order_total, 2) }}</strong></td>
                                                <td>
                                                    @if($is_paid)
                                                        <span class="badge bg-success text-white">✓ پارە دراو</span>
                                                    @else
                                                        <span class="badge bg-danger text-white">✗ قەرز</span>
                                                    @endif
                                                </td>
                                                <td>{{ $order->created_at->format('Y-m-d H:i') }}</td>
                                            </tr>
                                        @else
                                            <tr class="table-success">
                                                <td><span class="badge bg-success text-white">پارەدان</span></td>
                                                <td><strong class="text-success">${{ number_format($record['amount'], 2) }}</strong></td>
                                                <td><span class="badge bg-success text-white">✓ قبوڵ کرا</span></td>
                                                <td>{{ \Carbon\Carbon::parse($record['date'])->format('Y-m-d H:i') }}</td>
                                            </tr>
                                        @endif
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">هیچ داواکاری یان پارەدانێک نەدۆزرایەوە</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Print & Back Buttons -->
        <div class="row mt-4">
            <div class="col-12 text-end">
                <button onclick="window.print()" class="btn btn-info waves-effect waves-light">
                    <i class="fa fa-print"></i> چاپکردن
                </button>
                <a href="{{ route('all.customer') }}" class="btn btn-secondary waves-effect waves-light">
                    <i class="fa fa-arrow-left"></i> گەڕانەوە
                </a>
            </div>
        </div>

    </div>
</div>

@endsection