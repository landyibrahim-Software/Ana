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
        padding: 5px 10px;
        border-radius: 5px;
        cursor: pointer;
    }

    .btn-remove-item:hover {
        background: #c82333;
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

                                <div class="mb-3">
                                    <label class="form-label">کڕیار</label>
                                    <input type="hidden" name="customer_id" id="customerId">
                                    <input type="text" id="customerName" class="form-control" readonly>
                                </div>
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
                                <button type="button" class="btn btn-sm btn-success" onclick="addItemRow()">
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
// Load order items when order is selected
function loadOrderItems() {
    const orderId = document.getElementById('orderSelect').value;
    const select = document.getElementById('orderSelect');
    const customerId = select.options[select.selectedIndex].getAttribute('data-customer');
    
    document.getElementById('customerId').value = customerId;

    // Clear previous items
    document.getElementById('itemsContainer').innerHTML = '';

    if (!orderId) {
        document.getElementById('customerName').value = '';
        return;
    }

    // Get order items via AJAX
    fetch(`/returned/${orderId}/items`)
        .then(response => response.json())
        .then(items => {
            items.forEach((item, index) => {
                addItemRow(item, index);
            });
        })
        .catch(error => console.error('Error:', error));
}

// Add item row
function addItemRow(item = null, index = 0) {
    const container = document.getElementById('itemsContainer');
    const rowId = 'item-row-' + Date.now() + Math.random();
    
    const html = `
        <div class="item-row" id="${rowId}">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">بەرهەم</label>
                    <input type="hidden" name="returned_items[${index}][product_id]" value="${item ? item.product_id : ''}">
                    <input type="text" class="form-control" value="${item ? item.product_name : ''}" readonly>
                </div>

                <div class="col-md-2 mb-3">
                    <label class="form-label">دەرزەن بەرگەڕاندۆ</label>
                    <input type="number" name="returned_items[${index}][quantity_returned]" class="form-control" 
                           value="${item ? item.quantity : ''}" min="1" max="${item ? item.quantity : ''}" required>
                </div>

                <div class="col-md-2 mb-3">
                    <label class="form-label">متر بەرگەڕاندۆ</label>
                    <input type="number" name="returned_items[${index}][meters_returned]" class="form-control" 
                           step="0.01" value="${item ? item.meters : '0'}" min="0">
                </div>

                <div class="col-md-2 mb-3">
                    <label class="form-label">پاشگەزی (دۆلار)</label>
                    <input type="number" name="returned_items[${index}][refund_price]" class="form-control" 
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

// Load customer name when order changes
document.getElementById('orderSelect').addEventListener('change', function() {
    const select = this;
    const customerName = select.options[select.selectedIndex].text.split(' - ')[1];
    document.getElementById('customerName').value = customerName || '';
});
</script>

@endsection
