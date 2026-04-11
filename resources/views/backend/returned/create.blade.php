@extends('admin_dashboard')
@section('admin')

<div class="content">
    <div class="container-fluid">

        <div class="row mb-4">
            <div class="col-12">
                <div class="page-title-box">
                    <h4 class="page-title">بەرگەڕاندنی نوێ</h4>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('returned.store') }}" method="POST">
                            @csrf

                            <!-- Order Selection -->
                            <div class="mb-3">
                                <label class="form-label"><strong>پسوڵە هەڵبژێرە</strong></label>
                                <select name="order_id" class="form-control form-select" required onchange="showOrderInfo()">
                                    <option value="">-- پسوڵە هەڵبژێرە --</option>
                                    @foreach($orders as $order)
                                    <option value="{{ $order->id }}" 
                                            data-customer="{{ $order->customer->name }}"
                                            data-total="{{ $order->total }}"
                                            data-pay="{{ $order->pay }}">
                                        #{{ $order->invoice_no }} - {{ $order->customer->name }} - ${{ $order->total }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Order Info Display -->
                            <div id="orderInfo" class="alert alert-info" style="display: none;">
                                <p><strong>کڕیار:</strong> <span id="customerName"></span></p>
                                <p><strong>کۆی پسوڵە:</strong> $<span id="orderTotal"></span></p>
                                <p><strong>پارەی دراو:</strong> $<span id="orderPay"></span></p>
                            </div>

                            <hr>

                            <!-- Return Reason -->
                            <div class="mb-3">
                                <label class="form-label"><strong>هۆی بەرگەڕاندن</strong></label>
                                <textarea name="return_reason" class="form-control" rows="3" 
                                          placeholder="بۆ چی کڕیار بەرگەڕاند..." required></textarea>
                            </div>

                            <!-- Refund Amount -->
                            <div class="mb-3">
                                <label class="form-label"><strong>بڕی پاشگەزی (دۆلار)</strong></label>
                                <input type="number" name="refund_amount" class="form-control" 
                                       step="0.01" min="0" value="0" required>
                            </div>

                            <div class="text-end">
                                <a href="{{ route('returned.index') }}" class="btn btn-secondary">لابردن</a>
                                <button type="submit" class="btn btn-success">تۆمار بکە</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
function showOrderInfo() {
    const select = document.querySelector('select[name="order_id"]');
    const option = select.options[select.selectedIndex];
    
    if (!option.value) {
        document.getElementById('orderInfo').style.display = 'none';
        return;
    }
    
    document.getElementById('customerName').textContent = option.getAttribute('data-customer');
    document.getElementById('orderTotal').textContent = option.getAttribute('data-total');
    document.getElementById('orderPay').textContent = option.getAttribute('data-pay');
    document.getElementById('orderInfo').style.display = 'block';
}
</script>

@endsection