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

.rolls-badge {
    display: inline-block;
    background: #d4edda;
    color: #155724;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    margin: 2px;
    font-weight: 600;
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
    <th>تۆپ</th>
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
    
    // Calculate total rolls from selected colors
    $totalRolls = 0;
    if($item->selected_colors) {
        $colors = json_decode($item->selected_colors, true);
        foreach($colors as $color) {
            $totalRolls += intval($color['rolls'] ?? 0);
        }
    }
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
    <td>
        @if($totalRolls > 0)
            <span class="rolls-badge">{{ $totalRolls }} تۆپ</span>
        @else
            <span class="text-muted">—</span>
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

<!-- PRINT & WHATSAPP -->
<div class="mt-4 text-end d-print-none">
    <button onclick="window.print()" class="btn btn-primary">
        <i class="mdi mdi-printer me-1"></i> چاپکردن
    </button>
    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#whatsappModal">
        <i class="mdi mdi-whatsapp me-1"></i> ویتسئاپ
    </button>
</div>

<!-- WHATSAPP MODAL -->
<div class="modal fade" id="whatsappModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">بۆ ویتسئاپ بنێرە</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label"><strong>نمبەری مۆبایل (بە +964 دەست بکە)</strong></label>
                    <input type="text" id="whatsappPhone" class="form-control" placeholder="مثال: +964781234567" value="{{ $order->customer->phone ?? '' }}">
                </div>
                <small class="text-muted">بە ویتسئاپ کۆپی بنێرە</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">داخستن</button>
                <button type="button" class="btn btn-success" onclick="sendToWhatsapp()">بنێرە</button>
            </div>
        </div>
    </div>
</div>

<script>
function sendToWhatsapp() {
    const phone = document.getElementById('whatsappPhone').value.trim();
    
    if (!phone) {
        alert('تکایە نمبەری مۆبایل تێبنێ');
        return;
    }
    
    // Invoice details
    const orderId = "{{ $order->id }}";
    const customerName = "{{ $order->customer->name }}";
    const grandTotal = "{{ number_format($grandTotal, 2) }}";
    const message = encodeURIComponent(
        `سڵاو ${customerName}!\n\n` +
        `ئەم پسوڵەی تێدا:\n` +
        `ژمارەی پسوڵە: #${orderId}\n` +
        `کۆی گشتی: ${grandTotal}\n\n` +
        `لەتێپەڕی ویتسئاپ دا:\n` +
        `{{ route('print.invoice', $order->id) }}\n\n` +
        `سوپاس!`
    );
    
    // Open WhatsApp with message
    window.open(`https://wa.me/${phone}?text=${message}`, '_blank');
    
    // Close modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('whatsappModal'));
    modal.hide();
}
</script>

</div>
</div>
</div>
</div>

@endsection