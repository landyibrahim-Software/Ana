@extends('admin_dashboard')
@section('admin')

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>

<style>
body {
    font-family: 'Segoe UI', sans-serif;
}

.card {
    border-radius: 18px;
    box-shadow: 0 8px 22px rgba(0,0,0,.08);
    border: none;
}

.table thead th {
    background: linear-gradient(45deg, #0d6efd, #6610f2);
    color: #fff;
    text-align: center;
    font-size: 15px;
    padding: 12px;
}

.table td {
    vertical-align: middle;
    text-align: center;
    padding: 12px;
}

.table input.form-control {
    height: 50px;
    font-size: 18px;
    text-align: center;
    border-radius: 14px;
    background-color: #fff !important;
    color: #333 !important;
}

.table input.form-control:focus {
    background-color: #fff !important;
    color: #333 !important;
    border-color: #0d6efd !important;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25) !important;
}

.total-box {
    border-radius: 20px;
    background: linear-gradient(135deg, #6610f2, #0d6efd);
}

.total-box h1 {
    font-size: 42px;
    font-weight: bold;
}

.price-below-cost {
    border: 2px solid red !important;
    background-color: #f8d7da !important;
    color: red !important;
    font-weight: bold;
}

.barcode-badge {
    padding: 10px 20px;
    border-radius: 30px;
    font-weight: bold;
    background: #f1f3f5;
}

.scanned-highlight {
    animation: flash 0.8s ease-in-out;
    background-color: #d1e7dd !important;
}

@keyframes flash {
    0% { background-color: #fff; }
    50% { background-color: #d1e7dd; }
    100% { background-color: #fff; }
}

.select2-container .select2-selection--single {
    height: 50px;
    border-radius: 14px;
    font-size: 16px;
    padding: 10px;
}

.select2-selection__arrow {
    height: 50px !important;
}

.product-img {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 8px;
}

.price-field {
    height: 50px !important;
}

.qty-field {
    height: 50px !important;
    text-align: center;
    font-weight: bold;
}

.empty-state {
    text-align: center;
    padding: 40px;
    color: #6c757d;
}

.empty-state i {
    font-size: 48px;
    margin-bottom: 20px;
    opacity: 0.5;
}
</style>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<div class="content">
<div class="container-fluid">

{{-- TITLE --}}
<div class="row mb-4">
    <div class="col">
        <h3 class="fw-bold">🛒 بەشی فرۆشتن</h3>
    </div>
</div>

{{-- POS TABLE --}}
<div class="row">
<div class="col-12">
<div class="card mb-4">
<div class="card-body">

@php 
    $allcart = \Gloudemans\Shoppingcart\Facades\Cart::content();
@endphp

@if($allcart && $allcart->count() > 0)
<div class="table-responsive">
<table class="table table-bordered align-middle">
<thead>
<tr>
    <th>ناوی بەرهەم</th>
    <th width="100">مەترە</th>
    <th width="130">نرخی متر</th>
    <th width="140">کۆی گشتی نرخ</th>
    <th width="100">کردار</th>
</tr>
</thead>
<tbody>

@foreach($allcart as $cart)
<tr class="cart-row" data-rowid="{{ $cart->rowId }}" data-product-id="{{ $cart->id }}">

    {{-- ITEM NAME --}}
    <td class="fw-bold">
        {{ $cart->name }}
    </td>

    {{-- QUANTITY/METER --}}
    <td>
        <input type="number" 
               class="form-control qty-field"
               value="{{ $cart->qty }}"
               data-rowid="{{ $cart->rowId }}"
               min="1"
               step="0.01"
               onchange="updateCartQty(this)">
    </td>

    {{-- UNIT PRICE --}}
    <td>
        <input type="number"
               class="form-control price-field"
               value="{{ $cart->price }}"
               data-rowid="{{ $cart->rowId }}"
               data-buying="{{ isset($cart->options['buying_price']) ? $cart->options['buying_price'] : 0 }}"
               min="0"
               step="0.01"
               onchange="updateTotalPrice(this)"
               oninput="updateTotalPrice(this)">
        <div class="price-alert text-danger fw-bold" style="font-size: 11px;"></div>
    </td>

    {{-- TOTAL PRICE --}}
    <td class="price-total fw-bold">
        {{ number_format($cart->qty * $cart->price, 2) }}
    </td>

    {{-- ACTIONS --}}
    <td>
        <form method="POST" action="{{ url('/cart-update/'.$cart->rowId) }}" style="display:inline;" onsubmit="saveQtyAndPrice(event)">
            @csrf
            <input type="hidden" name="qty" id="qty-input-{{ $cart->rowId }}" value="{{ $cart->qty }}">
            <input type="hidden" name="price" id="price-input-{{ $cart->rowId }}" value="{{ $cart->price }}">
            <button type="submit" class="btn btn-success btn-sm px-3" title="Save">
                <i class="fas fa-check"></i>
            </button>
        </form>
        <a href="{{ url('/cart-remove/'.$cart->rowId) }}" class="btn btn-danger btn-sm px-3" title="Remove">
            <i class="fas fa-trash"></i>
        </a>
    </td>

</tr>
@endforeach

</tbody>
</table>
</div>
@else
<div class="empty-state">
    <i class="fas fa-shopping-cart"></i>
    <h5>سەبەتەکە بەتاڵە</h5>
    <p>لە خوار بەرهەمێک زیادبکە</p>
</div>
@endif

</div>
</div>
</div>
</div>

{{-- TOTAL + CUSTOMER --}}
<div class="row mb-4">

@php
$subTotal = 0;
if($allcart && $allcart->count() > 0) {
    foreach($allcart as $c){
        $subTotal += $c->qty * $c->price;
    }
}
@endphp

<div class="col-md-6">
<div class="total-box text-white text-center p-4 h-100">
<h5>کۆی گشتی ئایتم {{ $allcart ? $allcart->count() : 0 }}</h5>
<h1 id="grand-total">{{ number_format($subTotal, 2) }}</h1>
</div>
</div>

<div class="col-md-6">
<div class="card h-100">
<div class="card-body">

<form method="POST" action="{{ url('/create-invoice') }}" id="invoice-form">
@csrf

<label class="fw-bold mb-2">کڕیار</label>
<select name="customer_id" class="form-select customer-select mb-3" required>
<option value="">-- کڕیار هەڵبژێرە --</option>
@forelse($customer as $cus)
<option value="{{ $cus->id }}">{{ $cus->name }}</option>
@empty
<option disabled>کڕیار نیە</option>
@endforelse
</select>

<button type="submit" class="btn btn-primary btn-lg w-100" {{ ($allcart && $allcart->count() > 0) ? '' : 'disabled' }}>
پسوڵە دروستبکە
</button>

</form>

</div>
</div>
</div>

</div>

{{-- BARCODE + PRODUCTS --}}
<div class="row">
<div class="col-12">
<div class="card">
<div class="card-body">

<div class="text-center mb-4">
<h4 class="fw-bold">📦 سکانی بارکۆد</h4>
<span class="barcode-badge">
Last Scan: <span id="last-barcode">None</span>
</span>
</div>

@if($product && count($product) > 0)
<div class="table-responsive">
<table class="table table-hover">
<thead>
    <tr>
        <th width="70">وێنە</th>
        <th>ناوی بەرهەم</th>
        <th>جۆر</th>
        <th>نرخی فرۆشتن</th>
        <th>دستدا</th>
        <th width="80">کردار</th>
    </tr>
</thead>
<tbody>
@foreach($product as $item)
    @if($item && is_object($item) && isset($item->id))
    <tr data-code="{{ $item->product_code ?? '' }}">
    <td>
        @if($item->product_image && file_exists(public_path($item->product_image)))
            <img src="{{ asset($item->product_image) }}" class="product-img" alt="{{ $item->product_name ?? 'Product' }}">
        @else
            <img src="https://via.placeholder.com/50?text=No+Image" class="product-img" alt="No Image">
        @endif
    </td>
    <td class="fw-bold">{{ $item->product_name ?? 'Unknown' }}</td>
    <td>{{ optional($item->category)->category_name ?? '-' }}</td>
    <td class="text-success fw-bold">{{ $item->selling_price ?? 0 }}</td>
    <td>{{ $item->product_store ?? 0 }}</td>
    <td>
        <form method="POST" action="{{ url('/add-cart') }}" style="display:inline;">
        @csrf
        <input type="hidden" name="id" value="{{ $item->id }}">
        <input type="hidden" name="qty" value="1">
        <button type="submit" class="btn btn-success btn-sm">
        <i class="fas fa-plus-square"></i>
        </button>
        </form>
    </td>
    </tr>
    @endif
@endforeach
</tbody>
</table>
</div>
@else
<div class="empty-state">
    <i class="fas fa-box"></i>
    <h5>بەرهەم نیە</h5>
</div>
@endif

</div>
</div>
</div>
</div>

</div>
</div>

{{-- JS LOGIC --}}
<script>
/* CUSTOMER SEARCH */
$('.customer-select').select2({
    placeholder: "🔍 کڕیار بدۆزەرەوە",
    allowClear: true,
    width: '100%'
});

/* UPDATE CART QTY */
function updateCartQty(input) {
    const rowId = input.dataset.rowid;
    const row = document.querySelector(`tr[data-rowid="${rowId}"]`);
    if (!row) return;
    
    let qty = parseFloat(input.value) || 0;
    if (qty < 0) {
        input.value = 0;
        qty = 0;
    }
    input.value = qty;
    
    updateTotalPrice(row.querySelector('.price-field'));
}

/* UPDATE TOTAL PRICE */
function updateTotalPrice(priceInput) {
    const row = priceInput.closest('tr');
    if (!row) return;
    
    const qtyInput = row.querySelector('.qty-field');
    if (!qtyInput) return;
    
    const qty = parseFloat(qtyInput.value) || 0;
    const unitPrice = parseFloat(priceInput.value) || 0;
    const totalPrice = qty * unitPrice;
    
    const priceDisplay = row.querySelector('.price-total');
    if (priceDisplay) {
        priceDisplay.innerText = totalPrice.toFixed(2);
    }
    
    const buyingPrice = parseFloat(priceInput.dataset.buying) || 0;
    const alertBox = priceInput.nextElementSibling;
    
    if (unitPrice < buyingPrice) {
        priceInput.classList.add('price-below-cost');
        if (alertBox) alertBox.innerText = 'ژێر مایە';
    } else {
        priceInput.classList.remove('price-below-cost');
        if (alertBox) alertBox.innerText = '';
    }
    
    updateGrandTotal();
}

/* UPDATE GRAND TOTAL */
function updateGrandTotal() {
    let grandTotal = 0;
    document.querySelectorAll('.price-total').forEach(el => {
        const value = parseFloat(el.innerText) || 0;
        grandTotal += value;
    });
    const grandTotalEl = document.getElementById('grand-total');
    if (grandTotalEl) {
        grandTotalEl.innerText = grandTotal.toFixed(2);
    }
}

/* SAVE QTY AND PRICE BEFORE FORM SUBMIT */
function saveQtyAndPrice(event) {
    event.preventDefault();
    
    const form = event.target;
    const rowId = form.action.split('/').pop();
    const row = document.querySelector(`tr[data-rowid="${rowId}"]`);
    
    if (!row) {
        form.submit();
        return;
    }
    
    const qtyInput = row.querySelector('.qty-field');
    const priceInput = row.querySelector('.price-field');
    
    if (qtyInput && priceInput) {
        document.getElementById(`qty-input-${rowId}`).value = parseFloat(qtyInput.value) || 0;
        document.getElementById(`price-input-${rowId}`).value = parseFloat(priceInput.value) || 0;
    }
    
    form.submit();
}

/* PRICE FIELD CHANGE */
document.querySelectorAll('.price-field').forEach(input => {
    input.addEventListener('change', function() {
        updateTotalPrice(this);
    });
    input.addEventListener('input', function() {
        updateTotalPrice(this);
    });
});

/* QUANTITY FIELD CHANGE */
document.querySelectorAll('.qty-field').forEach(input => {
    input.addEventListener('input', function() {
        updateCartQty(this);
    });
    input.addEventListener('change', function() {
        updateCartQty(this);
    });
});

/* BARCODE SCANNER - FIXED: Allow input in fields */
let barcode = '';
let timer = null;

document.addEventListener('keydown', e => {
    // FIXED: Allow INPUT fields to receive input for quantity/price
    if (e.target.tagName === 'INPUT') {
        // Only handle Enter key for barcode, allow all other keys
        if (e.key !== 'Enter') {
            return;
        }
    }
    
    if(timer) clearTimeout(timer);
    
    if(e.key === 'Enter'){
        e.preventDefault();
        if(barcode.length > 3) handleBarcode(barcode);
        barcode = '';
        return;
    }
    
    if(e.key !== 'Shift' && e.key.length === 1){
        barcode += e.key;
    }
    
    timer = setTimeout(()=> barcode='', 200);
});

function handleBarcode(code){
    const lastBarcodeEl = document.getElementById('last-barcode');
    if (lastBarcodeEl) {
        lastBarcodeEl.innerText = code;
    }
    
    let found = false;
    
    document.querySelectorAll('tr[data-code]').forEach(row => {
        if(row.dataset.code && row.dataset.code === code){
            row.classList.add('scanned-highlight');
            const form = row.querySelector('form');
            if(form) form.submit();
            found = true;
            setTimeout(()=>row.classList.remove('scanned-highlight'),800);
        }
    });
    
    if(!found){
        alert('❌ بەرهەم نەدۆزرایەوە');
    }
}

/* Initialize on page load */
document.addEventListener('DOMContentLoaded', function() {
    updateGrandTotal();
});
</script>

@endsection