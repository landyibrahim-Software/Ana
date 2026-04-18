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

/* Payment Modal Input Styling */
.modal-body input.form-control,
.modal-body select.form-select {
    background-color: #fff !important;
    color: #333 !important;
    border: 2px solid #dee2e6 !important;
    font-size: 16px !important;
    padding: 10px 12px !important;
}

.modal-body input.form-control:focus,
.modal-body select.form-select:focus {
    background-color: #fff !important;
    color: #333 !important;
    border-color: #0d6efd !important;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25) !important;
    outline: none !important;
}

.modal-body input.form-control:hover {
    border-color: #0d6efd !important;
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
            <strong>ناوی فرۆشگا:</strong> {{ $customer->shopname ?? '—' }}
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
                    <i class="fas fa-phone"></i> 07501561887
                    &nbsp;&nbsp;
                    <i class="fas fa-phone"></i> 07701561887
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
    <th>دەرزەن</th>
    <th>نرخی دەرزەن</th>
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
    // FIXED: Use simple quantity instead of meters
    $quantity = floatval($item->qty);
    
    // Calculate row total: quantity × unit_price
    $rowTotal = $quantity * floatval($item->price);
    $subTotal += $rowTotal;
@endphp

<tr>
    <td>{{ $sl++ }}</td>
    <td>
        <strong>{{ $item->name }}</strong>
    </td>
    <td>
        {{ number_format($quantity, 2) }}
    </td>
    <td>{{ number_format($item->price, 2) }}</td>
    <td>{{ number_format($rowTotal, 2) }}</td>
</tr>
@endforeach
</tbody>
</table>
</div>

<!-- Previous Due calculated in controller - use it directly -->
@php
    $grandTotal = $subTotal + $previousDue;
@endphp

<!-- TOTAL -->
<div class="row mt-3" style="direction: rtl;">
    <div class="col-12 text-end">
        <p style="font-size: 16px; margin: 10px 0;">
            <strong>قەرزی پێشوو:</strong> 
            <b style="color: #f5576c;">{{ number_format($previousDue, 2) }}</b>
        </p>
        <hr>
        <h3 style="margin: 10px 0; color: #667eea;">
            <strong>کۆی کاڵا:</strong> 
            <b>{{ number_format($subTotal, 2) }}</b>
        </h3>
        <h2 style="margin: 10px 0; color: #43e97b; font-weight: 700;">
            <strong>کۆی گشتی:</strong> 
            <b>{{ number_format($grandTotal, 2) }}</b>
        </h2>
        <hr>
        <p style="font-size: 16px; margin: 10px 0;">
            <strong>قەرزی ماوە:</strong> 
            <b id="remaining-due" style="color: #ff6b6b;">{{ number_format($grandTotal, 2) }}</b>
        </p>
    </div>
</div>

<!-- ACTIONS -->
<div class="mt-4 text-end">
    <button class="btn btn-primary" onclick="window.print()">
        <i class="mdi mdi-printer"></i> چاپکردن
    </button>
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#paymentModal">
        <i class="mdi mdi-cash-check"></i> پارەدان
    </button>
</div>

</div>
</div>
</div>
</div>

<!-- PAYMENT MODAL -->
<div class="modal fade" id="paymentModal" tabindex="-1">
<div class="modal-dialog">
<div class="modal-content">
<div class="modal-header">
    <h5 class="modal-title">پسوڵەی {{ $customer->name }}</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">

<form method="POST" action="{{ route('final.invoice') }}" id="paymentForm">
@csrf

<!-- REQUIRED ORDER DATA -->
<input type="hidden" name="customer_id" value="{{ $customer->id }}">
<input type="hidden" name="order_date" value="{{ date('Y-m-d') }}">
<input type="hidden" name="order_status" value="complete">
<input type="hidden" name="total_products" value="{{ count($contents) }}">
<input type="hidden" name="sub_total" value="{{ $subTotal }}">
<input type="hidden" name="total" value="{{ $grandTotal }}">
<input type="hidden" name="payment_status" value="pending">
<input type="hidden" name="previous_due" value="{{ $previousDue }}">

<!-- SEND ITEMS WITH SIMPLE QUANTITY (NO COLORS/METERS) -->
@foreach($contents as $index => $item)
    <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $item->id }}">
    <input type="hidden" name="items[{{ $index }}][quantity]" value="{{ $item->qty }}">
    <input type="hidden" name="items[{{ $index }}][unitcost]" value="{{ $item->price }}">
@endforeach

<div class="mb-3">
    <label class="form-label"><strong>جۆری پارەدان</strong></label>
    <select name="payment_method" class="form-select" required>
        <option value="">-- هەڵبژێرە --</option>
        <option value="HandCash">دەستی</option>
        <option value="Cheque">چەک</option>
        <option value="Bank">بانک</option>
    </select>
</div>

<div class="mb-3">
    <label class="form-label"><strong>پارەی دراو</strong></label>
    <input 
        type="number" 
        name="pay" 
        id="pay-amount" 
        class="form-control" 
        value="0" 
        min="0" 
        max="{{ $grandTotal }}"
        step="0.01" 
        required
        autofocus="autofocus"
        placeholder="پارەی دراو بنووسە"
        autocomplete="off"
        inputmode="decimal">
</div>

<div class="mb-3 p-3" style="background: #f8f9fa; border-radius: 8px;">
    <p style="margin: 0; font-size: 16px;">
        <strong>قەرزی ماوە:</strong> 
        <b id="dynamic-remaining" style="color: #ff6b6b; font-size: 18px;">{{ number_format($grandTotal, 2) }}</b>
    </p>
</div>

<div class="text-center mt-4">
    <button type="submit" class="btn btn-success btn-lg w-100">
        <i class="mdi mdi-check-circle"></i> تەواوبوو
    </button>
</div>

</form>

</div>
</div>
</div>

<!-- JS -->
<script>
// Wait for modal to be shown before focusing
document.getElementById('paymentModal').addEventListener('shown.bs.modal', function() {
    const payInput = document.getElementById('pay-amount');
    if (payInput) {
        payInput.focus();
        payInput.select();
    }
});

const payInput = document.getElementById('pay-amount');
const dynamicRemaining = document.getElementById('dynamic-remaining');
const remainingDueText = document.getElementById('remaining-due');
const totalAmount = {{ $grandTotal }};

if (payInput) {
    payInput.addEventListener('input', function () {
        let pay = parseFloat(this.value) || 0;
        if (pay < 0) pay = 0;
        if (pay > totalAmount) pay = totalAmount;
        let remaining = totalAmount - pay;
        dynamicRemaining.innerText = remaining.toFixed(2);
        remainingDueText.innerText = remaining.toFixed(2);
    });

    // Clear default value on focus
    payInput.addEventListener('focus', function() {
        if (this.value === '0') {
            this.value = '';
        }
    });

    // Set value on blur if empty
    payInput.addEventListener('blur', function() {
        if (this.value === '' || this.value === '0') {
            this.value = '0';
            let remaining = totalAmount;
            dynamicRemaining.innerText = remaining.toFixed(2);
            remainingDueText.innerText = remaining.toFixed(2);
        }
    });
}
</script>

@endsection