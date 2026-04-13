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

    <!-- RIGHT : CUSTOMER + META -->
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
    // Get meter data
    $totalMeters = floatval($item->meters ?? $item->quantity ?? 0);
    $selectedColors = [];
    $totalRolls = 0;
    
    // Decode selected colors if they exist
    if($item->selected_colors) {
        $selectedColors = json_decode($item->selected_colors, true) ?? [];
        
        // Calculate total rolls
        foreach($selectedColors as $color) {
            $totalRolls += intval($color['rolls'] ?? 0);
        }
    }
    
    // Calculate row total: total_meters × unit_price
    $rowTotal = $totalMeters * floatval($item->unitcost ?? 0);
    $subTotal += $rowTotal;
@endphp

<tr>
    <td>{{ $sl++ }}</td>
    <td>
        <strong>{{ $item->product->product_name ?? 'Deleted Product' }}</strong>
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
        <p>قەرزی ماوە: <b id="remaining-due">{{ number_format(($grandTotal - ($order->pay ?? 0)), 2) }}</b></p>
    </div>
</div>

<!-- ✅ BUTTONS (Not printed) -->
<div class="mt-4 text-end d-print-none">
    <button onclick="window.print()" class="btn btn-primary waves-effect waves-light">
        <i class="fa fa-print"></i> چاپکردن
    </button>
    <button type="button" class="btn btn-success waves-effect waves-light" data-bs-toggle="modal" data-bs-target="#whatsappModal">
        <i class="fab fa-whatsapp"></i> واتساپ
    </button>
</div>

</div>
</div>
</div>
</div>

<!-- Add html2canvas library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<!-- ✅ SINGLE WHATSAPP MODAL -->
<div class="modal fade" id="whatsappModal" tabindex="-1" aria-labelledby="whatsappModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="whatsappModalLabel">
                    <i class="fab fa-whatsapp"></i> پسوڵە بۆ واتساپ بنێرە
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label"><strong>ژمارەی مۆبایل:</strong></label>
                    <input 
                        type="tel" 
                        id="whatsappPhone" 
                        class="form-control" 
                        placeholder="07812345678 یان +964781234567"
                        value="{{ $order->customer->phone ?? '' }}"
                    >
                    <small class="text-muted">نمبەری دروست بنووسە</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">داخستن</button>
                <button type="button" class="btn btn-success" id="sendBtn" onclick="sendInvoiceAsImage()">
                    <i class="fab fa-whatsapp"></i> بنێرە
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ✅ WHATSAPP SCRIPT -->
<script>
function sendInvoiceAsImage() {
    let phone = document.getElementById('whatsappPhone').value.trim();
    const btn = document.getElementById('sendBtn');
    const originalText = btn.innerHTML;
    
    if (!phone) {
        alert('⚠️ تکایە ژمارەی مۆبایل تێبنێ!');
        return;
    }
    
    phone = phone.replace(/[\s\-()]/g, '');
    
    if (!phone.startsWith('+')) {
        if (phone.startsWith('0')) {
            phone = '+964' + phone.substring(1);
        } else {
            phone = '+' + phone;
        }
    }
    
    if (!/^\+\d{10,15}$/.test(phone)) {
        alert('⚠️ ژمارەی مۆبایل نادروستە!\nفۆرمات: 07812345678');
        return;
    }
    
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> لە درەنوویت...';
    btn.disabled = true;
    
    const invoiceContent = document.getElementById('invoiceContent');
    
    html2canvas(invoiceContent, {
        scale: 2,
        backgroundColor: '#ffffff',
        useCORS: true,
        logging: false
    }).then(canvas => {
        // Convert to image
        const image = canvas.toDataURL('image/png');
        
        // Download the image
        const link = document.createElement('a');
        link.href = image;
        link.download = 'invoice_{{ $order->id }}.png';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        // Show message to user
        alert('✅ وێنەی پسوڵە داونلۆد بووە!\n\nئێستا بۆ واتساپ بڕۆ و بێتاپە دریزا بکە');
        
        // Open WhatsApp
        setTimeout(() => {
            const message = encodeURIComponent(
                `سڵاو {{ $order->customer->name }}!\n\n` +
                `ئەم پسوڵەی دێ:\n` +
                `ژمارە: #{{ $order->id }}\n` +
                `کۆی: ${{ number_format($grandTotal, 2) }}\n\n` +
                `بێتاپە دریزا بکە 👇\n\n` +
                `سوپاس! 🙏`
            );
            
            window.open(`https://wa.me/${phone.substring(1)}?text=${message}`, '_blank');
            
            // Close modal and reset button
            setTimeout(() => {
                const modal = bootstrap.Modal.getInstance(document.getElementById('whatsappModal'));
                modal.hide();
                btn.innerHTML = originalText;
                btn.disabled = false;
            }, 500);
        }, 1000);
    }).catch(error => {
        alert('خرابی: ' + error.message);
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}

document.getElementById('whatsappPhone').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        sendInvoiceAsImage();
    }
});
</script>

@endsection