@extends('admin_dashboard')
@section('admin')

<style>
    .product-card {
        background: #f8f9fa;
        border: 2px solid #dee2e6;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .color-input {
        display: flex;
        align-items: center;
        margin-bottom: 10px;
        gap: 10px;
    }

    .color-input label {
        min-width: 100px;
        font-weight: 600;
    }

    .color-input input {
        flex: 1;
        max-width: 150px;
    }

    .color-input span {
        color: #666;
        font-size: 0.9rem;
    }

    .btn-remove-product {
        background: #dc3545;
        color: white;
        border: none;
        padding: 8px 12px;
        border-radius: 5px;
        cursor: pointer;
    }

    .btn-remove-product:hover {
        background: #c82333;
    }

    .summary-box {
        background: #fff3cd;
        border: 2px solid #ffc107;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
    }
</style>

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
            <div class="col-lg-10">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('returned.store') }}" method="POST" id="returnForm">
                            @csrf

                            <!-- Customer Selection -->
                            <div class="mb-4">
                                <label class="form-label"><strong>کڕیار هەڵبژێرە</strong></label>
                                <select name="customer_id" id="customerSelect" class="form-control form-select" required onchange="loadCustomerOrders()">
                                    <option value="">-- کڕیار هەڵبژێرە --</option>
                                    @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}">
                                        {{ $customer->name }} - بڕی قەرز: ${{ number_format($customer->due, 2) }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Products List -->
                            <div class="mb-4">
                                <label class="form-label"><strong>بەرهەمەکان</strong></label>
                                <div id="productsContainer"></div>
                            </div>

                            <hr>

                            <!-- Return Info -->
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label"><strong>هۆی بەرگەڕاندن</strong></label>
                                    <textarea name="return_reason" class="form-control" rows="3" 
                                              placeholder="بۆچی کڕیار بەرگەڕاند..." required></textarea>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label"><strong>بڕی پاشگەزی (دۆلار)</strong></label>
                                    <input type="number" name="refund_amount" class="form-control" 
                                           step="0.01" min="0" value="0" required>
                                </div>
                            </div>

                            <!-- Buttons -->
                            <div class="text-end mt-4">
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
function loadCustomerOrders() {
    const customerId = document.getElementById('customerSelect').value;
    const container = document.getElementById('productsContainer');
    
    if (!customerId) {
        container.innerHTML = '';
        return;
    }

    container.innerHTML = '<p class="text-muted">لە لادا کردنی بەرهەمەکان...</p>';

    fetch(`/returned/customer/${customerId}/orders`)
        .then(response => response.json())
        .then(items => {
            container.innerHTML = '';
            
            if (items.length === 0) {
                container.innerHTML = '<p class="text-warning">ئایتمێک نیە</p>';
                return;
            }

            items.forEach((item, index) => {
                addProductCard(item, index);
            });
        })
        .catch(error => {
            console.error('Error:', error);
            container.innerHTML = '<p class="text-danger">خۆیەتی لە لادا کردنی دراوا</p>';
        });
}

function addProductCard(item, index) {
    const container = document.getElementById('productsContainer');
    const colors = item.selected_colors || [];
    
    let colorHtml = '';
    colors.forEach(color => {
        colorHtml += `
            <div class="color-input">
                <label>${color.name}</label>
                <input type="number" name="returned_items[${index}][returned_colors][${color.name}]" 
                       class="form-control" step="0.01" min="0" max="${color.meter}" 
                       placeholder="متری بەرگەڕاندۆ" value="0">
                <span>/ ${color.meter}م</span>
            </div>
        `;
    });

    const html = `
        <div class="product-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0">${item.product_name}</h6>
                <button type="button" class="btn-remove-product" onclick="this.parentElement.parentElement.remove()">حذف</button>
            </div>
            
            <input type="hidden" name="returned_items[${index}][product_id]" value="${item.product_id}">
            
            <p class="text-muted mb-2">کۆی متر سەلماو: ${item.meters}م</p>
            
            <div class="ms-3">
                ${colorHtml}
            </div>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', html);
}
</script>

@endsection