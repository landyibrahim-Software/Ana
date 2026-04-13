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

<div class="row mb-2">
    <div class="col-12 text-end">
        <h4 class="page-title">پسوڵەی کڕیار</h4>
    </div>
</div>

<div class="card">
<div class="card-body" style="direction:rtl" id="invoiceContent">

<!-- HEADER -->
<div class="invoice-header mb-3">
    <div class="invoice-meta text-end">
        <p>
            <strong>بەرواری پسوڵە:</strong>
            {{ \Carbon\Carbon::parse($order->order_date)->format('Y/m/d') }}
        </p>
        <p class="mt-2">
            <strong>ناوی کڕیار:</strong> {{ $order->customer->name }}
        </p>
        <p>
            <strong>ناوی فرۆشگا:</strong> {{ $order->customer->shopname ?? '—' }}
        </p>
        <p>
            <strong>ژمارەی مۆبایل:</strong> {{ $order->customer->phone ?? '—' }}
        </p>
    </div>

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
    $totalMeters = floatval($item->meters ?? $item->quantity ?? 0);
    $selectedColors = [];
    $totalRolls = 0;
    
    if($item->selected_colors) {
        $selectedColors = json_decode($item->selected_colors, true) ?? [];
        foreach($selectedColors as $color) {
            $totalRolls += intval($color['rolls'] ?? 0);
        }
    }
    
    $rowTotal = $totalMeters * floatval($item->unitcost ?? 0);
    $subTotal += $rowTotal;
@endphp

<tr>
    <td>{{ $sl++ }}</td>
    <td>
        <strong>{{ $item->product->product_name ?? 'Product' }}</strong>
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
    <td>
        @if($totalRolls > 0)
            <span class="rolls-badge">{{ $totalRolls }} تۆپ</span>
        @else
            <span class="text-muted">—</span>
        @endif
    </td>
    <td>{{ number_format($item->unitcost ?? 0, 2) }}</td>
    <td>{{ number_format($totalMeters, 2) }}</td>
    <td>{{ number_format($rowTotal, 2) }}</td>
</tr>
@endforeach
</tbody>
</table>
</div>

<!-- TOTAL SUMMARY -->
<div class="row mt-3" style="direction: rtl;">
    <div class="col-12 text-end">
        <p>قەرزی پێشوو: <b>{{ number_format($previousDue, 2) }}</b></p>
        <h3>کۆی کاڵا: <b>{{ number_format($subTotal, 2) }}</b></h3>
        <h3>کۆی گشتی: <b>{{ number_format($grandTotal, 2) }}</b></h3>
        <p>پارەی دراو: <b>{{ number_format($order->pay ?? 0, 2) }}</b></p>
        <p>قەرزی ماوە: <b>{{ number_format(($grandTotal - ($order->pay ?? 0)), 2) }}</b></p>
    </div>
</div>

</div>
</div>
</div>
</div>

<!-- BUTTONS -->
<div class="mt-4 text-end d-print-none">
    <button onclick="window.print()" class="btn btn-primary">
        <i class="fa fa-print"></i> چاپکردن
    </button>
    <button id="whatsappBtn" onclick="sendWhatsApp()" class="btn btn-success">
        <i class="fab fa-whatsapp"></i> واتساپ
    </button>
</div>

<!-- WHATSAPP SCRIPT -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<script>
function sendWhatsApp() {
    var phone = "{{ $order->customer->phone }}";
    var name = "{{ $order->customer->name }}";
    var orderId = "{{ $order->id }}";
    var total = "{{ number_format($grandTotal, 2) }}";
    var btn = document.getElementById('whatsappBtn');
    
    if (!phone) {
        alert('ژمارەی مۆبایل نەدۆزرایەوە');
        return;
    }
    
    // Disable button
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> لە درەنوویت...';
    
    // Clean phone
    phone = phone.replace(/[\s-()]/g, '');
    if (phone.startsWith('0')) {
        phone = '964' + phone.substring(1);
    }
    if (!phone.startsWith('+')) {
        phone = '+' + phone;
    }
    
    // Message to send
    var msg = encodeURIComponent(
        'سڵاو ' + name + '!\n\n' +
        '📋 پسوڵەی دێ:\n' +
        'ژمارە: #' + orderId + '\n' +
        'کۆی: $' + total + '\n\n' +
        'وێنەی دریزا بکە 👇\n\n' +
        'سوپاس! 🙏'
    );
    
    // Step 1: Convert invoice to image
    var invoiceCard = document.getElementById('invoiceContent');
    
    html2canvas(invoiceCard, {
        scale: 2,
        backgroundColor: '#ffffff',
        useCORS: true,
        logging: false
    }).then(function(canvas) {
        // Step 2: Download image
        var link = document.createElement('a');
        link.href = canvas.toDataURL('image/png');
        link.download = 'invoice_' + orderId + '.png';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        // Step 3: Open WhatsApp
        window.open('https://wa.me/' + phone + '?text=' + msg, '_blank');
        
        // Reset button after 2 seconds
        setTimeout(function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="fab fa-whatsapp"></i> واتساپ';
        }, 2000);
        
    }).catch(function(error) {
        alert('خرابی: ' + error);
        btn.disabled = false;
        btn.innerHTML = '<i class="fab fa-whatsapp"></i> واتساپ';
    });
}
</script>

@endsection