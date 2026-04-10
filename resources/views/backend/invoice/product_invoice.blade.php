@extends('admin_dashboard')
@section('admin')

<style>
.invoice-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    direction: rtl;
}

.brand-area{
    display:flex;
    align-items:center;
    gap:15px;
    direction:ltr;
}

.brand-text h2{
    font-family:'Georgia', serif;
    font-weight:bold;
    margin:0;
}

.brand-text small{
    display:block;
    font-size:13px;
    color:#555;
}

.phone-list i{
    margin-left:5px;
    color:#0d6efd;
}

.invoice-meta p{
    margin:0;
    font-size:14px;
    line-height:1.8;
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

<div class="row mb-2">
    <div class="col-12 text-end">
        <h4 class="page-title">پسوڵەی کڕیار</h4>
    </div>
</div>

<div class="card">
<div class="card-body" style="direction:rtl">

<!-- HEADER -->
<div class="invoice-header mb-3">

    <!-- RIGHT : CUSTOMER + META -->
    <div class="invoice-meta text-end">
        <p>
            <strong>بەرواری پسوڵە:</strong>
            {{ date('Y/m/d') }}
        </p>

        <p class="mt-2">
            <strong>ناوی کڕیار:</strong> {{ $customer->name }}
        </p>

        <p>
            <strong>نا��ی فرۆشگا:</strong> {{ $customer->shopname ?? '—' }}
        </p>

        <p>
            <strong>ژمارەی مۆبایل:</strong> {{ $customer->phone ?? '—' }}
        </p>
    </div>

    <!-- LEFT : BRAND -->
    <div class="brand-area">
        <img src="{{ asset('backend/assets/images/nza.png') }}" height="90">

        <div class="brand-text">
            <h2>کوتاڵی نزا</h2>

            <div class="phone-list">
                <small>
                    <i class="fas fa-phone"></i> 07708130060
                    &nbsp;&nbsp;
                    <i class="fas fa-phone"></i> 07501792101
                </small>
            </div>
        </div>
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

@foreach($contents as $item)
@php
    // Get meter data from cart options
    $totalMeters = floatval($item->options['total_meters'] ?? 0);
    $selectedColors = $item->options['selected_colors'] ?? [];
    
    // Calculate row total: total_meters × unit_price
    $rowTotal = $totalMeters * floatval($item->price);
    $subTotal += $rowTotal;
@endphp

<tr>
    <td>{{ $sl++ }}</td>
    <td>
        <strong>{{ $item->name }}</strong>
    </td>
    <td>
        @if(is_array($selectedColors) && count($selectedColors) > 0)
            @foreach($selectedColors as $color)
                <span class="color-badge">
                    {{ $color['name'] ?? $color['color_name'] }}: {{ $color['meter'] }}م
                </span>
            @endforeach
        @else
            <span class="text-muted">بێ رەنگ</span>
        @endif
    </td>
    <td>{{ number_format($item->price, 2) }}</td>
    <td>{{ number_format($totalMeters, 2) }}</td>
    <td>{{ number_format($rowTotal, 2) }}</td>
</tr>
@endforeach
</tbody>
</table>
</div>

@php
    $previousDue = $customer->due + $customer->previous_due;
    $grandTotal = $subTotal + $previousDue;
@endphp

<!-- TOTAL -->
<div class="row mt-3" style="direction: rtl;">
    <div class="col-12 text-end">
        <p>قەرزی پێشوو: <b>{{ number_format($previousDue, 2) }}</b></p>
        <h3>کۆی کاڵا: <b>{{ number_format($subTotal, 2) }}</b></h3>
        <h3>کۆی گشتی: <b>{{ number_format($grandTotal, 2) }}</b></h3>
        <p>قەرزی ماوە: <b id="remaining-due">{{ number_format($grandTotal, 2) }}</b></p>
    </div>
</div>

<!-- ACTIONS -->
<div class="mt-4 text-end">
    <button class="btn btn-primary" onclick="window.print()">چاپکردن</button>
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#paymentModal">
        پارەدان
    </button>
</div>

</div>
</div>
</div>
</div>

<!-- PAYMENT MODAL -->
<div class="modal fade" id="paymentModal">
<div class="modal-dialog">
<div class="modal-content">
<div class="modal-body">

<h4 class="text-center mb-3">پسوڵەی {{ $customer->name }}</h4>

<form method="POST" action="{{ route('final.invoice') }}">
@csrf

<!-- REQUIRED ORDER DATA -->
<input type="hidden" name="customer_id" value="{{ $customer->id }}">
<input type="hidden" name="order_date" value="{{ date('Y-m-d') }}">
<input type="hidden" name="order_status" value="complete">
<input type="hidden" name="total_products" value="{{ count($contents) }}">
<input type="hidden" name="sub_total" value="{{ $subTotal }}">
<input type="hidden" name="total" value="{{ $grandTotal }}">
<input type="hidden" name="payment_status" value="pending">

<!-- SEND ITEMS WITH METERS AND COLORS -->
@foreach($contents as $index => $item)
    @php
        $itemTotalMeters = floatval($item->options['total_meters'] ?? 0);
        $itemSelectedColors = $item->options['selected_colors'] ?? [];
        $itemSelectedColorsJson = json_encode($itemSelectedColors);
    @endphp
    <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $item->id }}">
    <input type="hidden" name="items[{{ $index }}][quantity]" value="{{ $item->qty }}">
    <input type="hidden" name="items[{{ $index }}][unitcost]" value="{{ $item->price }}">
    <input type="hidden" name="items[{{ $index }}][meters]" value="{{ $itemTotalMeters }}">
    <input type="hidden" name="items[{{ $index }}][selected_colors]" value="{{ $itemSelectedColorsJson }}">
@endforeach

<div class="mb-3">
    <label>جۆری پارەدان</label>
    <select name="payment_method" class="form-select" required>
        <option value="">-- هەڵبژێرە --</option>
        <option value="HandCash">دەستی</option>
        <option value="Cheque">چەک</option>
        <option value="Bank">بانک</option>
    </select>
</div>

<div class="mb-3">
    <label>پارەی دراو</label>
    <input type="number" name="pay" id="pay-amount" class="form-control" value="0" min="0" step="0.01" required>
</div>

<div class="text-center mt-3">
    <p>قەرزی ماوە: <b id="dynamic-remaining">{{ number_format($grandTotal, 2) }}</b></p>
    <button type="submit" class="btn btn-primary">تەواوبوو</button>
</div>

</form>

</div>
</div>
</div>

<!-- JS -->
<script>
const payInput = document.getElementById('pay-amount');
const dynamicRemaining = document.getElementById('dynamic-remaining');
const remainingDueText = document.getElementById('remaining-due');
const totalAmount = {{ $grandTotal }};

payInput.addEventListener('input', function () {
    let pay = parseFloat(this.value) || 0;
    if (pay < 0) pay = 0;
    if (pay > totalAmount) pay = totalAmount;
    let remaining = totalAmount - pay;
    dynamicRemaining.innerText = remaining.toFixed(2);
    remainingDueText.innerText = remaining.toFixed(2);
});
</script>

@endsection