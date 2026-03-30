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
    <th>تؤپ</th>
    <th>نرخی متر</th>
    <th> متر</th>
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
    $qtyTotal   = $item->metter * $item->price;
    $subTotal  += $qtyTotal;
@endphp

<tr>
    <td>{{ $sl++ }}</td>
    <td>{{ $item->name }}</td>
    <td>{{ $item->qty }}</td>
    <td>{{ number_format($item->price,2) }}</td>
    <td>{{ number_format($item->metter,2) }}</td>
    <td>{{ number_format($qtyTotal,2) }}</td>
</tr>
@endforeach
</tbody>
</table>
</div>

@php
    $previousDue = $customer->orders->sum('due');
    $grandTotal = $subTotal + $previousDue;
@endphp

<!-- TOTAL -->
<div class="row mt-3" style="direction: rtl;">
    <div class="col-12 text-end">
        <p> قەرزی پێشوو: <b>USD{{ number_format($previousDue,2) }}</b></p>
        <h3> کۆی گشتی: <b>USD{{ number_format($grandTotal,2) }} </b></h3>
        <p>قەرزی ماوە: <b id="remaining-due">USD{{ number_format($grandTotal,2) }}</b></p>
    </div>
</div>


<!-- ACTIONS -->
<div class="mt-4 text-end">
    <button class="btn btn-primary" onclick="window.print()">چاپکردن</button>
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#paymentModal">
        دروستکردنی پسوڵە
    </button>
</div>

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
<input type="hidden" name="order_status" value="pending">
<input type="hidden" name="total_products" value="{{ count($contents) }}">
<input type="hidden" name="sub_total" value="{{ $subTotal }}">
<input type="hidden" name="total" value="{{ $grandTotal }}">

<div class="mb-3">
    <label>جۆری پارەدان</label>
    <select name="payment_status" class="form-select" required>
        <option value="HandCash">دەستی</option>
        <option value="Cheque">چەک</option>
        <option value="Due">قەرز</option>
    </select>
</div>

<div class="mb-3">
    <label>پارەدان</label>
    <input type="number" name="pay" id="pay-amount" class="form-control" value="0" min="0"
       step="0.01" >
</div>

{{-- SEND ITEMS --}}
@foreach($contents as $index => $item)
    <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $item->id }}">
    <input type="hidden" name="items[{{ $index }}][quantity]" value="{{ $item->qty }}">
    <input type="hidden" name="items[{{ $index }}][unitcost]" value="{{ $item->price }}">
    <input type="hidden" name="items[{{ $index }}][metter]" value="{{ $item->metter }}">
@endforeach

<div class="text-center mt-3">
    <p>USDقەرزی ماوە: <b id="dynamic-remaining">{{ number_format($grandTotal,2) }}</b></p>
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
