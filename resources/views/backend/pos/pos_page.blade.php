@extends('admin_dashboard')
@section('admin')

{{-- ================= STYLES ================= --}}
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

.colors-box {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 15px;
    max-height: 350px;
    overflow-y: auto;
}

.all-colors-item {
    display: flex;
    align-items: center;
    margin-bottom: 15px;
    gap: 10px;
    padding: 14px;
    border-radius: 8px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: 2px solid #667eea;
}

.all-colors-item input[type="checkbox"] {
    width: 24px;
    height: 24px;
    cursor: pointer;
    flex-shrink: 0;
    accent-color: white;
}

.all-colors-item label {
    font-weight: 700;
    color: white;
    font-size: 15px;
    margin: 0;
    cursor: pointer;
    flex: 1;
}

.color-item {
    display: flex;
    align-items: center;
    margin-bottom: 15px;
    gap: 10px;
    padding: 12px;
    border-radius: 8px;
    background: white;
    border: 2px solid #dee2e6;
}

.color-item:hover {
    background: #f8f9fa;
    border-color: #0d6efd;
}

.color-item input[type="checkbox"] {
    width: 22px;
    height: 22px;
    cursor: pointer;
    flex-shrink: 0;
}

.color-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
    min-width: 100px;
}

.color-name {
    font-weight: 600;
    color: #333;
    font-size: 14px;
}

.color-available {
    font-size: 12px;
    color: #6c757d;
}

.color-input-group {
    display: flex;
    gap: 8px;
    align-items: center;
    flex: 1;
    margin-left: 10px;
}

.color-input-group label {
    font-size: 12px;
    font-weight: 500;
    color: #6c757d;
    margin: 0;
    min-width: 50px;
}

.customer-meter {
    width: 70px;
    height: 36px;
    font-size: 14px;
    padding: 5px;
    border: 2px solid #dee2e6;
    border-radius: 6px;
    text-align: center;
    background-color: white;
    cursor: text;
}

.customer-meter:disabled {
    background-color: #e9ecef;
    cursor: not-allowed;
    color: #6c757d;
}

.customer-meter:enabled {
    background-color: #fff;
    border-color: #0d6efd;
}

.customer-meter:focus {
    border-color: #0d6efd;
    outline: none;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

.color-remaining {
    font-weight: 600;
    color: #28a745;
    font-size: 13px;
    min-width: 70px;
    text-align: right;
}

.color-remaining.warning {
    color: #ff6b6b;
}

.price-total {
    font-weight: bold;
    color: #28a745;
    font-size: 16px;
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

.product-img {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 8px;
}

.meter-input {
    height: 50px;
    font-size: 18px;
    text-align: center;
    border-radius: 10px;
    border: 2px solid #dee2e6;
    background-color: #f8f9fa;
}

.price-field {
    height: 50px !important;
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
    <th>ناوی ئایتم</th>
    <th style="min-width: 500px;">مێترەی ھر رەنگ</th>
    <th width="120">کۆی گشتی مێتر</th>
    <th width="130">نرخی متر</th>
    <th width="140">کۆی گشتی نرخ</th>
    <th width="100">کردار</th>
</tr>
</thead>
<tbody>

@foreach($allcart as $cart)
@php 
    $cartProduct = \App\Models\Product::with('colors')->find($cart->id);
    $colors = ($cartProduct && $cartProduct->colors && $cartProduct->colors->count() > 0) ? $cartProduct->colors : collect();
    
    $savedColors = isset($cart->options['selected_colors']) ? $cart->options['selected_colors'] : [];
    $savedTotalMeters = isset($cart->options['total_meters']) ? $cart->options['total_meters'] : 0;
@endphp

<tr class="cart-row" data-rowid="{{ $cart->rowId }}" data-product-id="{{ $cart->id }}">

    {{-- ITEM NAME --}}
    <td class="fw-bold">
        {{ $cart->name }}
    </td>

    {{-- COLORS WITH INPUT FIELDS --}}
    <td>
        @if($colors && $colors->count() > 0)
        <div class="colors-box">
            
            {{-- ALL CHECKBOX --}}
            <div class="all-colors-item">
                <input type="checkbox" 
                       class="check-all"
                       id="check-all-{{ $cart->rowId }}"
                       data-rowid="{{ $cart->rowId }}"
                       onchange="selectAllColors(this)">
                <label for="check-all-{{ $cart->rowId }}">✓ ھەموو رەنگەکان</label>
            </div>

            {{-- INDIVIDUAL COLORS --}}
            @foreach($colors as $color)
            @php
                $isSelected = false;
                $savedMeter = 0;
                
                if(is_array($savedColors) && count($savedColors) > 0) {
                    foreach($savedColors as $saved) {
                        if(isset($saved['id']) && $saved['id'] == $color->id) {
                            $isSelected = true;
                            $savedMeter = isset($saved['meter']) ? $saved['meter'] : 0;
                            break;
                        }
                    }
                }
            @endphp
            
            <div class="color-item">
                <input type="checkbox" 
                       class="color-check"
                       data-color-id="{{ $color->id }}"
                       data-color-name="{{ $color->color_name }}"
                       data-available-meters="{{ $color->meters }}"
                       data-rowid="{{ $cart->rowId }}"
                       {{ $isSelected ? 'checked' : '' }}
                       onchange="updateColorMeters(this)">
                
                <div class="color-info">
                    <span class="color-name">{{ $color->color_name }}</span>
                    <span class="color-available">دستدا: {{ $color->meters }}م</span>
                </div>

                <div class="color-input-group">
                    <label>مێتر:</label>
                    <input type="number" 
                           class="customer-meter"
                           data-color-id="{{ $color->id }}"
                           data-rowid="{{ $cart->rowId }}"
                           data-available="{{ $color->meters }}"
                           placeholder="0"
                           min="0"
                           max="{{ $color->meters }}"
                           step="0.01"
                           value="{{ $savedMeter }}"
                           {{ $isSelected ? '' : 'disabled' }}
                           onchange="validateMeterInput(this)"
                           oninput="validateMeterInput(this)">
                    <span class="color-remaining" data-color-id="{{ $color->id }}">{{ ($color->meters - $savedMeter) }}م</span>
                </div>
            </div>
            @endforeach

        </div>
        @else
        <span class="text-muted">بێ رەنگ</span>
        @endif
    </td>

    {{-- TOTAL METER --}}
    <td>
        <input type="number" 
               class="form-control meter-input total-meter"
               value="{{ $savedTotalMeters }}"
               data-rowid="{{ $cart->rowId }}"
               readonly
               min="0"
               step="0.01">
    </td>

    {{-- UNIT PRICE --}}
    <td>
        <input type="number"
               class="form-control price-field"
               value="{{ $cart->price }}"
               data-rowid="{{ $cart->rowId }}"
               data-buying="{{ isset($cart->options['buying_price']) ? $cart->options['buying_price'] : 0 }}"
               data-original-price="{{ $cart->price }}"
               min="0"
               step="0.01"
               onchange="updateTotalPrice(this)"
               oninput="updateTotalPrice(this)">
        <div class="price-alert text-danger fw-bold" style="font-size: 11px;"></div>
    </td>

    {{-- TOTAL PRICE --}}
    <td class="price-total">
        {{ number_format($savedTotalMeters * $cart->price, 2) }}
    </td>

    {{-- ACTIONS --}}
    <td>
        <form method="POST" action="{{ url('/cart-update/'.$cart->rowId) }}" style="display:inline;" onsubmit="saveColorDataAndPriceBeforeSubmit(event)">
            @csrf
            <input type="hidden" name="qty" value="{{ $cart->qty }}">
            <input type="hidden" name="price" id="price-input-{{ $cart->rowId }}" value="{{ $cart->price }}">
            <input type="hidden" name="color_data" id="color-data-{{ $cart->rowId }}" value="">
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
        $totalMeters = isset($c->options['total_meters']) ? $c->options['total_meters'] : 0;
        $subTotal += $totalMeters * $c->price;
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

<input type="hidden" id="cart-data" name="cart_data" value="">

<button type="button" class="btn btn-primary btn-lg w-100" {{ ($allcart && $allcart->count() > 0) ? '' : 'disabled' }} onclick="prepareInvoiceData()">
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
        <th width="100">کردار</th>
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
    <td>
        <form method="POST" action="{{ url('/add-cart') }}" style="display:inline;">
        @csrf
        <input type="hidden" name="id" value="{{ $item->id }}">
        <input type="hidden" name="name" value="{{ $item->product_name ?? 'Unknown' }}">
        <input type="hidden" name="qty" value="1">
        <input type="hidden" name="price" value="{{ $item->selling_price ?? 0 }}">
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

/* SELECT ALL COLORS */
function selectAllColors(checkAllElement) {
    const rowId = checkAllElement.dataset.rowid;
    const row = document.querySelector(`tr[data-rowid="${rowId}"]`);
    
    if (!row) return;
    
    const isChecked = checkAllElement.checked;
    const allColorCheckboxes = row.querySelectorAll('.color-check');
    
    allColorCheckboxes.forEach(checkbox => {
        checkbox.checked = isChecked;
        updateColorMeters(checkbox);
    });
}

/* VALIDATE METER INPUT - PREVENT EXCEEDING AVAILABLE METERS */
function validateMeterInput(input) {
    const availableMeters = parseFloat(input.dataset.available) || 0;
    let inputValue = parseFloat(input.value) || 0;
    
    // Prevent negative values
    if (inputValue < 0) {
        input.value = 0;
        inputValue = 0;
    }
    
    // Prevent exceeding available meters
    if (inputValue > availableMeters) {
        input.value = availableMeters;
        inputValue = availableMeters;
        alert(`⚠️ زۆر زیات! تۆ نتوانی لە ${availableMeters}م بەتری ئەم رەنگە دانێن`);
    }
    
    // Update color meters
    updateColorMeters(input);
}

/* UPDATE COLOR METERS */
function updateColorMeters(element) {
    const rowId = element.dataset.rowid || (element.closest('.color-item') && element.closest('.color-item').querySelector('.color-check') ? element.closest('.color-item').querySelector('.color-check').dataset.rowid : null);
    if (!rowId) return;
    
    const row = document.querySelector(`tr[data-rowid="${rowId}"]`);
    if (!row) return;
    
    let totalMeters = 0;
    const colorItems = row.querySelectorAll('.color-item');
    
    colorItems.forEach(item => {
        const checkbox = item.querySelector('.color-check');
        const input = item.querySelector('.customer-meter');
        
        if (!checkbox || !input) return;
        
        const colorId = checkbox.dataset.colorId;
        const availableMeters = parseFloat(checkbox.dataset.availableMeters) || 0;
        const customerMeter = parseFloat(input.value) || 0;
        
        if (checkbox.checked) {
            input.disabled = false;
            input.max = availableMeters;
            totalMeters += customerMeter;
            
            const remaining = availableMeters - customerMeter;
            const remainingSpan = item.querySelector(`[data-color-id="${colorId}"].color-remaining`);
            if (remainingSpan) {
                remainingSpan.innerText = remaining.toFixed(2) + 'م';
                
                if (remaining < 0) {
                    remainingSpan.classList.add('warning');
                } else {
                    remainingSpan.classList.remove('warning');
                }
            }
        } else {
            input.disabled = true;
            input.value = '0';
            
            const remainingSpan = item.querySelector(`[data-color-id="${colorId}"].color-remaining`);
            if (remainingSpan) {
                remainingSpan.innerText = availableMeters.toFixed(2) + 'م';
                remainingSpan.classList.remove('warning');
            }
        }
    });
    
    const meterInput = row.querySelector('.total-meter');
    if (meterInput) {
        meterInput.value = totalMeters.toFixed(2);
    }
    
    const priceField = row.querySelector('.price-field');
    if (priceField) {
        updateTotalPrice(priceField);
    }
    
    updateGrandTotal();
}

/* UPDATE TOTAL PRICE */
function updateTotalPrice(priceInput) {
    const row = priceInput.closest('tr');
    if (!row) return;
    
    const meterInput = row.querySelector('.total-meter');
    if (!meterInput) return;
    
    const totalMeters = parseFloat(meterInput.value) || 0;
    const unitPrice = parseFloat(priceInput.value) || 0;
    const totalPrice = totalMeters * unitPrice;
    
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

/* SAVE COLOR DATA AND PRICE BEFORE FORM SUBMIT */
function saveColorDataAndPriceBeforeSubmit(event) {
    event.preventDefault();
    
    const form = event.target;
    const rowId = form.action.split('/').pop();
    const row = document.querySelector(`tr[data-rowid="${rowId}"]`);
    
    if (!row) {
        form.submit();
        return;
    }
    
    const selectedColors = [];
    let totalMeters = 0;
    
    row.querySelectorAll('.color-item').forEach(item => {
        const checkbox = item.querySelector('.color-check');
        const input = item.querySelector('.customer-meter');
        
        if (checkbox && checkbox.checked && input) {
            const meter = parseFloat(input.value) || 0;
            selectedColors.push({
                id: checkbox.dataset.colorId,
                name: checkbox.dataset.colorName,
                meter: meter
            });
            totalMeters += meter;
        }
    });
    
    const colorDataInput = form.querySelector('[name="color_data"]');
    if (colorDataInput) {
        colorDataInput.value = JSON.stringify({
            selected_colors: selectedColors,
            total_meters: totalMeters
        });
    }
    
    const priceInput = row.querySelector('.price-field');
    const priceHiddenInput = form.querySelector('[name="price"]');
    if (priceInput && priceHiddenInput) {
        priceHiddenInput.value = parseFloat(priceInput.value) || 0;
    }
    
    form.submit();
}

/* PREPARE INVOICE DATA */
function prepareInvoiceData() {
    const cartData = [];
    
    document.querySelectorAll('.cart-row').forEach(row => {
        const rowId = row.dataset.rowid;
        const productId = row.dataset.productId;
        const meterInput = row.querySelector('.total-meter');
        const priceInput = row.querySelector('.price-field');
        
        if (!meterInput || !priceInput) return;
        
        const totalMeters = parseFloat(meterInput.value) || 0;
        const unitPrice = parseFloat(priceInput.value) || 0;
        const productNameElement = row.querySelector('td.fw-bold');
        const productName = productNameElement ? productNameElement.innerText : 'Unknown';
        
        const selectedColors = [];
        const colorItems = row.querySelectorAll('.color-item');
        
        colorItems.forEach(item => {
            const checkbox = item.querySelector('.color-check');
            const input = item.querySelector('.customer-meter');
            
            if (checkbox && checkbox.checked && input) {
                selectedColors.push({
                    id: checkbox.dataset.colorId,
                    name: checkbox.dataset.colorName,
                    meter: parseFloat(input.value) || 0
                });
            }
        });
        
        if (selectedColors.length > 0) {
            cartData.push({
                rowId: rowId,
                productId: productId,
                name: productName,
                totalMeters: totalMeters,
                unitPrice: unitPrice,
                totalPrice: totalMeters * unitPrice,
                selectedColors: selectedColors
            });
        }
    });
    
    if (cartData.length === 0) {
        alert('❌ لە کم دوو ببە یەک رەنگ دیاری بکە');
        return;
    }
    
    document.getElementById('cart-data').value = JSON.stringify(cartData);
    document.getElementById('invoice-form').submit();
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

/* CUSTOMER METER INPUT */
document.querySelectorAll('.customer-meter').forEach(input => {
    input.addEventListener('input', function() {
        validateMeterInput(this);
    });
    input.addEventListener('change', function() {
        validateMeterInput(this);
    });
});

/* BARCODE SCANNER */
let barcode = '';
let timer = null;

document.addEventListener('keydown', e => {
    if (e.target.tagName === 'INPUT') return;
    
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
    document.querySelectorAll('.cart-row').forEach(row => {
        const firstCheckbox = row.querySelector('.color-check');
        if (firstCheckbox) {
            updateColorMeters(firstCheckbox);
        }
    });
    
    updateGrandTotal();
});
</script>

@endsection