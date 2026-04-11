@extends('admin_dashboard')
@section('admin')

<style>
.invoice-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    direction: rtl;
}

.brand-area {
    display: flex;
    align-items: center;
    gap: 15px;
}

.brand-text h2 {
    font-family: 'Georgia', serif;
    font-weight: bold;
    margin: 0;
    font-size: 28px;
}

.brand-text small {
    display: block;
    font-size: 13px;
    color: #555;
}

.phone-list i {
    margin-right: 5px;
    color: #0d6efd;
}

.customer-info {
    display: flex;
    justify-content: space-between;
    direction: rtl;
    margin-bottom: 20px;
}

.customer-info .left, .customer-info .right {
    width: 48%;
}

.customer-info p {
    margin: 3px 0;
    font-size: 16px;
}

.text-right {
    text-align: right;
}

.badge-status {
    font-size: 14px;
    padding: 5px 10px;
}

.color-badge {
    display: inline-block;
    background: #e9ecef;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    margin: 2px;
}
</style>

<div class="content">
<div class="container-fluid">

<div class="card">
<div class="card-body">

<!-- HEADER -->
<div class="invoice-header">

    <!-- LEFT: Logo + Brand -->
    <div class="brand-area">
        <img src="{{ asset('backend/assets/images/nza.png') }}" height="90">
        <div class="brand-text">
            <h2>کوتاڵی نزا</h2>
            <div class="phone-list">
                <small><i class="fas fa-phone"></i> 07708130060 &nbsp; <i class="fas fa-phone"></i> 07501792101</small>
            </div>
        </div>
    </div>

    <!-- RIGHT: Invoice Info -->
    <div class="text-right">
        <p><strong>بەرواری پسوڵە:</strong> {{ \Carbon\Carbon::parse($order->order_date)->format('d-m-Y') }}</p>
        <p><strong>ژمارەی پسوڵە:</strong> {{ $order->id }}</p>
    </div>

</div>

<hr>

<!-- CUSTOMER INFO -->
<div class="customer-info">

    <!-- RIGHT SIDE -->
    <div class="right text-right">
        <p><strong>ناوی کڕیار:</strong> {{ $order->customer->name }}</p>
        <p><strong>ناوی فرۆشگا:</strong> {{ $order->customer->shopname ?? '—' }}</p>
        <p><strong>مۆبایل:</strong> {{ $order->customer->phone ?? '—' }}</p>
    </div>

</div>

<!-- ITEMS TABLE -->
<div class="table-responsive mt-4">
<table class="table table-bordered text-center">
<thead>
<tr>
    <th>#</th>
    <th>ئایتم</th>
    <th>رەنگەکان</th>
    <th>نرخی متر</th>
    <th>کۆی متر</th>
    <th>کۆی گشتی</th>
</tr>
</thead>

<tbody>
@php
    $sl = 1;
    $subTotal = 0;
@endphp

@foreach($order->orderItems as $item)
@php
    $rowTotal = ($item->meters ?? $item->quantity) * $item->unitcost;
    $subTotal += $rowTotal;
@endphp
<tr>
    <td>{{ $sl++ }}</td>
    <td><strong>{{ optional($item->product)->product_name ?? 'Deleted Product' }}</strong></td>
    <td>
        @if($item->selected_colors)
            @php $colors = json_decode($item->selected_colors, true); @endphp
            @foreach($colors as $color)
                <span class="color-badge">
                    {{ $color['name'] }}: {{ $color['meter'] }}م
                </span>
            @endforeach
        @else
            <span class="text-muted">بێ رەنگ</span>
        @endif
    </td>
    <td>{{ number_format($item->unitcost, 2) }}</td>
    <td>{{ number_format($item->meters ?? $item->quantity, 2) }}</td>
    <td>{{ number_format($rowTotal, 2) }}</td>
</tr>
@endforeach
</tbody>

</table>
</div>

<!-- TOTAL SUMMARY -->
<!-- Use variables passed from controller -->
<div class="row mt-3">
    <div class="col-sm-6"></div>
    <div class="col-sm-6 text-end">
        <p>قەرزی پێشوو: <b>{{ number_format($previousDue, 2) }}</b></p>
        <p>کۆی کاڵا: <b>{{ number_format($subTotal, 2) }}</b></p>
        <h4>کۆی گشتی: <b>{{ number_format($grandTotal, 2) }}</b></h4>
        <p>پارەی دراو: <b>{{ number_format($order->pay, 2) }}</b></p>
        <p>قەرزی ماوە: <b>{{ number_format($grandTotal - $order->pay, 2) }}</b></p>
    </div>
</div>

<!-- PRINT -->
<div class="mt-4 text-end d-print-none">
    <button onclick="window.print()" class="btn btn-primary">چاپکردن</button>
</div>

</div>
</div>
</div>
</div>

@endsection