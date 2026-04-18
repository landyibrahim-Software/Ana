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

@media print {
    .d-print-none {
        display: none !important;
    }
    body {
        background: white;
    }
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
        <img src="{{ asset('backend/assets/images/nza.png') }}" height="90" alt="کوتاڵی نزا">
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
<tr style="background: linear-gradient(45deg, #0d6efd, #6610f2); color: white;">
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
    $calculatedSubTotal = 0;
@endphp

@foreach($order->orderDetails as $item)
@php
    // FIXED: Use simple quantity instead of meters
    $quantity = floatval($item->quantity ?? 0);
    
    // Calculate row total: quantity × unitcost
    $rowTotal = $quantity * floatval($item->unitcost ?? 0);
    $calculatedSubTotal += $rowTotal;
@endphp

<tr>
    <td>{{ $sl++ }}</td>
    <td>
        <strong>{{ $item->product->product_name ?? 'Product' }}</strong>
    </td>
    <td>
        {{ number_format($quantity, 2) }}
    </td>
    <td>{{ number_format($item->unitcost ?? 0, 2) }}</td>
    <td>{{ number_format($rowTotal, 2) }}</td>
</tr>
@endforeach
</tbody>
</table>
</div>

<!-- TOTAL SUMMARY -->
<div class="row mt-3" style="direction: rtl;">
    <div class="col-12 text-end">
        <p style="font-size: 16px; margin: 10px 0;">
            <strong>قەرزی پێشوو:</strong> 
            <b style="color: #f5576c;">{{ number_format($previousDue, 2) }}</b>
        </p>
        <hr>
        <h3 style="margin: 10px 0; color: #667eea;">
            <strong>کۆی کاڵا:</strong> 
            <b>{{ number_format($calculatedSubTotal, 2) }}</b>
        </h3>
        <h2 style="margin: 10px 0; color: #43e97b; font-weight: 700;">
            <strong>کۆی گشتی:</strong> 
            <b>{{ number_format($calculatedSubTotal + $previousDue, 2) }}</b>
        </h2>
        <hr>
        <p style="font-size: 16px; margin: 10px 0;">
            <strong>پارەی دراو:</strong> 
            <b style="color: #4facfe;">{{ number_format($order->pay ?? 0, 2) }}</b>
        </p>
        <p style="font-size: 16px; margin: 10px 0;">
            <strong>قەرزی ماوە:</strong> 
            <b style="color: #ff6b6b;">{{ number_format((($calculatedSubTotal + $previousDue) - ($order->pay ?? 0)), 2) }}</b>
        </p>
    </div>
</div>

</div>
</div>
</div>
</div>

<!-- BUTTONS -->
<div class="mt-4 text-end d-print-none">
    <button onclick="window.print()" class="btn btn-primary me-2">
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
    var phone = "{{ $order->customer->phone ?? '' }}";
    var name = "{{ $order->customer->name }}";
    var orderId = "{{ $order->id }}";
    var total = "{{ number_format($calculatedSubTotal + $previousDue, 2) }}";
    var btn = document.getElementById('whatsappBtn');
    
    if (!phone || phone.trim() === '') {
        alert('⚠️ ژمارەی مۆبایل نەدۆزرایەوە!');
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
    
    // Get invoice content
    var invoiceCard = document.getElementById('invoiceContent');
    
    // Convert to image
    html2canvas(invoiceCard, {
        scale: 2,
        backgroundColor: '#ffffff',
        useCORS: true,
        logging: false
    }).then(function(canvas) {
        try {
            // Download image
            var link = document.createElement('a');
            link.href = canvas.toDataURL('image/png');
            link.download = 'invoice_' + orderId + '.png';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            // Message
            var msg = encodeURIComponent(
                'سڵاو ' + name + '!\n\n' +
                '📋 پسوڵەی دێ:\n' +
                'ژمارە: #' + orderId + '\n' +
                'کۆی: $' + total + '\n\n' +
                'وێنەی دریزا بکە 👇\n\n' +
                'سوپاس! 🙏'
            );
            
            // Open WhatsApp after 500ms
            setTimeout(function() {
                window.open('https://wa.me/' + phone + '?text=' + msg, '_blank');
                
                // Reset button after 2 seconds
                setTimeout(function() {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fab fa-whatsapp"></i> واتساپ';
                }, 2000);
            }, 500);
            
        } catch(e) {
            console.error('Error:', e);
            alert('خرابی: ' + e.message);
            btn.disabled = false;
            btn.innerHTML = '<i class="fab fa-whatsapp"></i> واتساپ';
        }
    }).catch(function(error) {
        console.error('Canvas Error:', error);
        alert('خرابی: وێنە دروستنەکردن');
        btn.disabled = false;
        btn.innerHTML = '<i class="fab fa-whatsapp"></i> واتساپ';
    });
}
</script>

@endsection