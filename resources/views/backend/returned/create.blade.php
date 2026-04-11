@extends('admin_dashboard')
@section('admin')

<style>
    .form-section {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 20px;
        border-left: 5px solid #667eea;
    }

    .form-section h5 {
        color: #333;
        margin-bottom: 15px;
        font-weight: 600;
    }

    .item-row {
        background: white;
        padding: 15px;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        margin-bottom: 10px;
    }

    .btn-remove-item {
        background: #dc3545;
        color: white;
        border: none;
        padding: 8px 15px;
        border-radius: 5px;
        cursor: pointer;
        font-weight: 600;
    }

    .btn-remove-item:hover {
        background: #c82333;
    }

    .info-box {
        background: #e3f2fd;
        border-left: 4px solid #2196f3;
        padding: 12px;
        border-radius: 4px;
        margin-bottom: 15px;
    }
</style>

<div class="content">
    <div class="container-fluid">

        <!-- PAGE TITLE -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="page-title-box">
                    <h4 class="page-title">
                        <i class="mdi mdi-plus me-2"></i> بەرگەڕاندنی نوێ
                    </h4>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-10">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('returned.store') }}" method="POST" id="returnForm">
                            @csrf

                            <!-- ORDER SELECTION -->
                            <div class="form-section">
                                <h5>پسوڵە هەڵبژێرە</h5>
                                <div class="mb-3">
                                    <label class="form-label">پسوڵە</label>
                                    <select name="order_id" id="orderSelect" class="form-control @error('order_id') is-invalid @enderror" required onchange="loadOrderItems()">
                                        <option value="">-- پسوڵە هەڵبژێرە --</option>
                                        @foreach($orders as $order)
                                        <option value="{{ $order->id }}" data-customer="{{ $order->customer_id }}">
                                            #{{ $order->invoice_no }} - {{ $order->customer->name ?? 'نەناسراو' }} ({{ \Carbon\Carbon::parse($order->order_date)->format('d-m-Y') }})
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('order_id')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Order Info Box -->
                                <div id="orderInfoBox" style="display:none;">
                                    <div class="info-box">
                                        <strong>ناوی کڕیار:</strong> <span id="displayCustomerName"></span><br>
                                        <strong>کۆی پسوڵە:</strong> $<span id="displayOrderTotal">0.00</span><br>
                                        <strong>پارەی دراو:</strong> $<span id="displayOrderPay">0.00</span>
                                    </div>
                                </div>

                                <input type="hidden" name="customer_id" id="customerId">
                            </div>

                            <!-- RETURN DETAILS -->
                            <div class="form-section">
                                <h5>زانیاری بەرگەڕاندن</h5>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">بەرواری بەرگەڕاندن</label>
                                        <input type="date" name="return_date" class="form-control @error('return_date') is-invalid @enderror" 
                                               value="{{ now()->toDateString() }}" required>
                                        @error('return_date')
                                        <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">بڕی پاشگەزی (دۆلار)</label>
                                        <input type="number" name="refund_amount" class="form-control @error('refund_amount') is-invalid @enderror" 
                                               step="0.01" min="0" value="0" required>
                                        @error('refund_amount')
                                        <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">هۆی بەرگەڕاندن</label>
                                    <textarea name="return_reason" class="form-control @error('return_reason') is-invalid @enderror" 
                                              rows="3" placeholder="بۆچی کڕیار بەرگەڕاند..." required></textarea>
                                    @error('return_reason')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">یادداشت (ئختیاری)</label>
                                    <textarea name="notes" class="form-control" rows="2" placeholder="یادداشت..."></textarea>
                                </div>
                            </div>

                            <!-- RETURNED ITEMS -->
                            <div class="form-section">
                                <h5>بەرهەمە بەرگەڕاندووەکان</h5>
                                <div id="itemsContainer"></div>
                                <button type="button" class="btn btn-sm btn-success" onclick="addItemRow()" id="addItemBtn" style="display:none;">
                                    <i class="mdi mdi-plus me-1"></i> بەرهەم زیاد بکە
                                </button>
                            </div>

                            <!-- BUTTONS -->
                            <div class="text-end">
                                <a href="{{ route('returned.index') }}" class="btn btn-secondary">لابردن</a>
                                <button type="submit" class="btn btn-success">
                                    <i class="mdi mdi-check me-1"></i> تۆمار بکە
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
let itemIndex = 0;

// Load order items when order is selected
function loadOrderItems() {
    const orderId = document.getElementById('orderSelect').value;
    
    if (!orderId) {
        document.getElementById('orderInfoBox').style.display = 'none';
        document.getElementById('itemsContainer').innerHTML = '';
        document.getElementById('addItemBtn').style.display = 'none';
        return;
    }

    // Show loading
    document.getElementById('itemsContainer').innerHTML = '<p class="text-muted">لە لادا کردنی ئایتمەکان...</p>';

    // Get order items via AJAX
    fetch(`/returned/${orderId}/items`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
                document.getElementById('itemsContainer').innerHTML = '';
                return;
            }

            // Set customer ID
            document.getElementById('customerId').value = data.order.customer_id;

            // Display order info
            document.getElementById('displayCustomerName').textContent = data.order.customer_name;
            document.getElementById('displayOrderTotal').textContent = parseFloat(data.order.total_amount).toFixed(2);
            document.getElementById('displayOrderPay').textContent = parseFloat(data.order.total_paid).toFixed(2);
            document.getElementById('orderInfoBox').style.display = 'block';

            // Clear and load items
            document.getElementById('itemsContainer').innerHTML = '';
            itemIndex = 0;

            if (data.items.length === 0) {
                document.getElementById('itemsContainer').innerHTML = '<p class="text-warning">ئایتمێک نیە</p>';
                document.getElementById('addItemBtn').style.display = 'none';
                return;
            }

            data.items.forEach(item => {
                addItemRow(item);
            });

            document.getElementById('addItemBtn').style.display = 'inline-block';
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('itemsContainer').innerHTML = '<p class="text-danger">خۆیەتی: ئایتمەکان لە لادا کرایەوە بەسەبارەی هەڵە</p>';
        });
}

// Add item row
function addItemRow(item = null) {
    const container = document.getElementById('itemsContainer');
    const rowId = 'item-row-' + itemIndex;
    const currentIndex = itemIndex;
    itemIndex++;
    
    const html = `
        <div class="item-row" id="${rowId}">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">بەرهەم</label>
                    <input type="hidden" name="returned_items[${currentIndex}][product_id]" value="${item ? item.product_id : ''}">
                    <input type="text" class="form-control" value="${item ? item.product_name : ''}" readonly>
                </div>

                <div class="col-md-2 mb-3">
                    <label class="form-label">دەرزەن بەرگەڕاندۆ</label>
                    <input type="number" name="returned_items[${currentIndex}][quantity_returned]" class="form-control" 
                           value="${item ? item.quantity : ''}" min="1" max="${item ? item.quantity : ''}" required>
                </div>

                <div class="col-md-2 mb-3">
                    <label class="form-label">متر بەرگەڕاندۆ</label>
                    <input type="number" name="returned_items[${currentIndex}][meters_returned]" class="form-control" 
                           step="0.01" value="${item ? (item.meters || 0) : '0'}" min="0">
                </div>

                <div class="col-md-2 mb-3">
                    <label class="form-label">پاشگەزی (دۆلار)</label>
                    <input type="number" name="returned_items[${currentIndex}][refund_price]" class="form-control" 
                           step="0.01" value="${item ? item.unitcost : ''}" min="0" required>
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn-remove-item w-100" onclick="document.getElementById('${rowId}').remove()">
                        حذف
                    </button>
                </div>
            </div>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', html);
}
</script>

@endsection