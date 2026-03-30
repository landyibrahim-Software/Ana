@extends('admin_dashboard')
@section('admin')

{{-- ================= STYLES ================= --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>

<style>
body {
    font-family: 'Segoe UI', sans-serif;
}

/* CARD */
.card {
    border-radius: 18px;
    box-shadow: 0 8px 22px rgba(0,0,0,.08);
    border: none;
}

/* TABLE */
.table thead th {
    background: linear-gradient(45deg, #0d6efd, #6610f2);
    color: #fff;
    text-align: center;
    font-size: 15px;
}

.table td {
    vertical-align: middle;
    text-align: center;
    padding: 12px;
}

/* BIG INPUTS */
.table input.form-control {
    height: 50px;
    font-size: 18px;
    text-align: center;
    border-radius: 14px;
}

/* TOTAL */
.total-box {
    border-radius: 20px;
    background: linear-gradient(135deg, #6610f2, #0d6efd);
}
.total-box h1 {
    font-size: 42px;
    font-weight: bold;
}

/* PRICE ALERT */
.price-below-cost {
    border: 2px solid red !important;
    background-color: #f8d7da !important;
    color: red !important;
    font-weight: bold;
}

/* BARCODE */
.barcode-badge {
    padding: 10px 20px;
    border-radius: 30px;
    font-weight: bold;
    background: #f1f3f5;
}

/* SCAN EFFECT */
.scanned-highlight {
    animation: flash 0.8s ease-in-out;
    background-color: #d1e7dd !important;
}

@keyframes flash {
    0% { background-color: #fff; }
    50% { background-color: #d1e7dd; }
    100% { background-color: #fff; }
}

/* SELECT2 */
.select2-container .select2-selection--single {
    height: 50px;
    border-radius: 14px;
    font-size: 16px;
    padding: 10px;
}
.select2-selection__arrow {
    height: 50px !important;
}
</style>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<div class="content">
<div class="container-fluid">

{{-- ================= TITLE ================= --}}
<div class="row mb-4">
    <div class="col">
        <h3 class="fw-bold">🛒 بەشی فرۆشتن</h3>
    </div>
</div>

{{-- ================= POS TABLE (FULL WIDTH) ================= --}}
<div class="row">
<div class="col-12">
<div class="card mb-4">
<div class="card-body">

@php $allcart = Cart::content(); @endphp

<table class="table table-bordered align-middle">
<thead>
<tr>
    <th>ناوی ئایتم</th>
    <th width="120">تۆپ</th>
    <th width="160">نرخی متر</th>
    <th width="150">بەهای کۆتایی</th>
    <th width="120">کردار</th>
</tr>
</thead>
<tbody>

@foreach($allcart as $cart)
<form method="POST" action="{{ url('/cart-update/'.$cart->rowId) }}">
@csrf
<tr>

<td class="fw-bold">{{ $cart->name }}</td>

<td>
<input type="number" name="qty" value="{{ $cart->qty }}" min="1" class="form-control">
</td>

<td>
<input type="number"
class="form-control price-field"
value="{{ $cart->price }}"
data-rowid="{{ $cart->rowId }}"
data-buying="{{ $cart->options->buying_price ?? 0 }}"
min="0"
step="0.01">
<div class="price-alert text-danger fw-bold"></div>
</td>


@php
$subtotal = $cart->qty * $cart->price;
@endphp

<td class="fw-bold text-success">{{ number_format($subtotal,2) }}</td>

<td>
<button class="btn btn-success btn-sm px-3">
<i class="fas fa-check"></i>
</button>
<a href="{{ url('/cart-remove/'.$cart->rowId) }}" class="btn btn-danger btn-sm px-3">
<i class="fas fa-trash"></i>
</a>
</td>

</tr>
</form>
@endforeach

</tbody>
</table>

</div>
</div>
</div>
</div>

{{-- ================= TOTAL + CUSTOMER ================= --}}
<div class="row mb-4">

@php
$grandTotal = 0;
foreach($allcart as $c){
    $grandTotal += $c->qty * $c->price;
}
@endphp

<div class="col-md-6">
<div class="total-box text-white text-center p-4 h-100">
<h5>کۆی گشتی ئایتم {{ Cart::count() }}</h5>
<h1>{{ number_format($grandTotal,2) }}</h1>
</div>
</div>

<div class="col-md-6">
<div class="card h-100">
<div class="card-body">

<form method="POST" action="{{ url('/create-invoice') }}">
@csrf

<label class="fw-bold mb-2">کڕیار</label>
<select name="customer_id" class="form-select customer-select mb-3" required>
<option></option>
@foreach($customer as $cus)
<option value="{{ $cus->id }}">{{ $cus->name }}</option>
@endforeach
</select>

<button class="btn btn-primary btn-lg w-100" {{ Cart::count()==0?'disabled':'' }}>
پسوڵە دروستبکە
</button>

</form>

</div>
</div>
</div>

</div>

{{-- ================= BARCODE + PRODUCTS ================= --}}
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

<table class="table table-hover">
@foreach($product as $item)
<tr data-code="{{ $item->product_code }}">
<td width="70">
<img src="{{ asset($item->product_image) }}" width="45">
</td>
<td class="fw-bold">{{ $item->product_name }}</td>
<td width="90">
<form method="POST" action="{{ url('/add-cart') }}">
@csrf
<input type="hidden" name="id" value="{{ $item->id }}">
<input type="hidden" name="name" value="{{ $item->product_name }}">
<input type="hidden" name="qty" value="1">
<input type="hidden" name="price" value="{{ $item->selling_price }}">
<button class="btn btn-success btn-sm">
<i class="fas fa-plus-square"></i>
</button>
</form>
</td>
</tr>
@endforeach
</table>

</div>
</div>
</div>
</div>

</div>
</div>

{{-- ================= JS (ORIGINAL LOGIC KEPT) ================= --}}
<script>
/* CUSTOMER SEARCH */
$('.customer-select').select2({
    placeholder: "🔍 کڕیار بدۆزەرەوە",
    allowClear: true,
    width: '100%'
});

/* BARCODE */
let barcode = '';
let timer = null;

document.addEventListener('keydown', e => {
    if(timer) clearTimeout(timer);

    if(e.key === 'Enter'){
        e.preventDefault();
        if(barcode.length) handleBarcode(barcode);
        barcode = '';
        return;
    }

    if(e.key !== 'Shift'){
        barcode += e.key;
    }

    timer = setTimeout(()=> barcode='', 200);
});

function handleBarcode(code){
    document.getElementById('last-barcode').innerText = code;
    let found = false;

    document.querySelectorAll('tr[data-code]').forEach(row => {
        if(row.dataset.code === code){
            row.classList.add('scanned-highlight');
            row.querySelector('form').submit();
            found = true;
            setTimeout(()=>row.classList.remove('scanned-highlight'),800);
        }
    });

    if(!found){
        alert('❌ Item does not exist');
    }
}

/* PRICE WARNING + UPDATE (UNCHANGED) */
document.querySelectorAll('.price-field').forEach(input => {

    const alertBox = input.nextElementSibling;

    function check(){
        let buy = parseFloat(input.dataset.buying)||0;
        let sell = parseFloat(input.value)||0;

        if(sell < buy){
            input.classList.add('price-below-cost');
            alertBox.innerText = 'ژێر مایە';
        }else{
            input.classList.remove('price-below-cost');
            alertBox.innerText = '';
        }
    }

    check();
    input.addEventListener('input', check);

    input.addEventListener('change', ()=>{
        fetch(window.location.origin + '/cart/update-price/' + input.dataset.rowid,{
            method:'POST',
            headers:{
                'X-CSRF-TOKEN':'{{ csrf_token() }}',
                'Content-Type':'application/json'
            },
            body:JSON.stringify({price:input.value})
        }).then(()=>location.reload());
    });
});
</script>

@endsection
