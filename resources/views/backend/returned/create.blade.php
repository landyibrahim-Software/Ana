@extends('admin_dashboard')
@section('admin')

<style>
    .order-section {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 15px;
    }

    .order-section h6 {
        color: #333;
        font-weight: 600;
        margin-bottom: 15px;
    }

    .product-table {
        background: white;
        border-radius: 10px;
        overflow: hidden;
        margin-bottom: 20px;
        border: 1px solid #dee2e6;
    }

    .product-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 15px;
        font-weight: 600;
    }

    .color-badge {
        display: inline-block;
        background: #e9ecef;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        margin: 2px;
    }

    .color-input-group {
        background: #f8f9fa;
        padding: 12px;
        border-radius: 6px;
        margin-bottom: 10px;
        border-left: 3px solid #667eea;
    }

    .color-input-group label {
        display: flex;
        align-items: center;
        margin-bottom: 10px;
        cursor: pointer;
        font-weight: 500;
    }

    .color-input-group input[type="checkbox"] {
        width: 18px;
        height: 18px;
        margin-right: 10px;
        cursor: pointer;
    }

    .color-input-group input[type="number"] {
        width: 120px;
        padding: 8px;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        font-weight: 600;
    }

    .meter-input-wrapper {
        margin-left: 30px;
        margin-top: 8px;
    }

    .return-info-section {
        background: #e8f4f8;
        border-left: 4px solid #2196f3;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .product-row-expanded {
        background: #f8f9fa;
        padding: 20px;
        border-top: 1px solid #dee2e6;
    }

    .product-info {
        display: flex;
        gap: 15px;
        align-items: flex-start;
    }

    .product-img {
        width: 80px;
        height: 70px;
        border-radius: 8px;
        object-fit: cover;
        border: 1px solid #dee2e6;
    }

    .product-details {
        flex: 1;
    }

    .product-details strong {
        font-size: 16px;
        display: block;
        margin-bottom: 5px;
    }

    .product-details small {
        display: block;
        color: #666;
        margin: 3px 0;
    }

    .product-stats {
        display: flex;
        gap: 20px;
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid #dee2e6;
    }

    .stat-box {
        text-align: center;
        flex: 1;
    }

    .stat-label {
        font-size: 12px;
        color: #666;
        margin-bottom: 5px;
    }

    .stat-value {
        font-size: 18px;
        font-weight: 700;
        color: #333;
    }

    .colors-section h6 {
        margin-bottom: 15px;
        font-weight: 600;
        color: #333;
    }

    .loading-spinner {
        text-align: center;
        padding: 20px;
    }
</style>

<div class="content">
    <div class="container-fluid">

        <div class="row mb-4">
            <div class="col-12">
                <div class="page-title-box">
                    <h4 class="page-title">
                        <i class="mdi mdi-undo me-2"></i> بەرگەڕاندنی نوێ
                    </h4>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('returned.store') }}" method="POST" id="returnForm">
                            @csrf

                            <!-- Customer Selection -->
                            <div class="mb-4">
                                <label class="form-label"><strong>کڕیار هەڵبژێرە</strong></label>
                                <select name="customer_id" id="customerSelect" class="form-control form-select" required>
                                    <option value="">-- کڕیار هەڵبژێرە --</option>
                                    @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}">
                                        {{ $customer->name }} - بڕی قەرز: ${{ number_format($customer->due, 2) }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Products Section -->
                            <div id="productsContainer" style="display:none;">
                                <h5 class="mb-3">
                                    <i class="mdi mdi-history me-2"></i> مێژووی کڕین
                                </h5>
                                <div id="productsListContainer"></div>
                            </div>

                            <!-- Return Info -->
                            <div id="returnInfoSection" style="display:none;">
                                <hr>
                                <div class="return-info-section">
                                    <h6>
                                        <i class="mdi mdi-information-outline me-2"></i> زانیاری بەرگەڕاندن
                                    </h6>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label"><strong>هۆی بەرگەڕاندن</strong></label>
                                            <textarea name="return_reason" class="form-control" rows="3" 
                                                      placeholder="بۆچی کڕیار بەرگەڕاند..."></textarea>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label"><strong>بڕی پاشگەزی (دۆلار)</strong></label>
                                            <div class="input-group">
                                                <span class="input-group-text">$</span>
                                                <input type="number" name="refund_amount" id="refundAmount" class="form-control" 
                                                       step="0.01" min="0" value="0" required readonly>
                                            </div>
                                            <small class="text-muted">دەبێت بە شێوەی ئۆتۆماتیک تێبگە</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Buttons -->
                            <div class="text-end mt-4">
                                <a href="{{ route('returned.index') }}" class="btn btn-secondary">
                                    <i class="mdi mdi-close me-1"></i> لابردن
                                </a>
                                <button type="submit" class="btn btn-success" id="submitBtn" style="display:none;">
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
// When customer is selected, load their orders
document.getElementById('customerSelect').addEventListener('change', function() {
    loadCustomerOrders();
});

function loadCustomerOrders() {
    const customerId = document.getElementById('customerSelect').value;
    const container = document.getElementById('productsContainer');
    const listContainer = document.getElementById('productsListContainer');
    const returnInfoSection = document.getElementById('returnInfoSection');
    const submitBtn = document.getElementById('submitBtn');
    
    if (!customerId) {
        container.style.display = 'none';
        returnInfoSection.style.display = 'none';
        submitBtn.style.display = 'none';
        return;
    }

    listContainer.innerHTML = '<div class="loading-spinner"><p class="text-muted">لە لادا کردنی مێژووی کڕین...</p></div>';

    fetch(`/returned/customer/${customerId}/orders`)
        .then(response => response.json())
        .then(items => {
            listContainer.innerHTML = '';
            
            if (items.length === 0) {
                listContainer.innerHTML = '<div class="alert alert-warning">ئایتمێک نیە</div>';
                container.style.display = 'block';
                return;
            }

            items.forEach((item, index) => {
                addProductCard(item, index);
            });

            container.style.display = 'block';
            returnInfoSection.style.display = 'block';
            submitBtn.style.display = 'inline-block';
            
            // Attach all event listeners after products are loaded
            attachAllEventListeners();
        })
        .catch(error => {
            console.error('Error:', error);
            listContainer.innerHTML = '<div class="alert alert-danger">خۆیەتی لە لادا کردنی دراوا</div>';
            container.style.display = 'block';
        });
}

function addProductCard(item, index) {
    const container = document.getElementById('productsListContainer');
    
    // Build colors HTML
    let colorsHtml = '';
    if (item.colors && item.colors.length > 0) {
        item.colors.forEach(color => {
            colorsHtml += `
                <div class="color-input-group" data-index="${index}" data-color="${color.name}">
                    <label>
                        <input type="checkbox" 
                               class="color-checkbox"
                               data-color-name="${color.name}"
                               data-unit-cost="${item.unitcost}">
                        <strong>${color.name}</strong> - ${color.meter}م
                    </label>
                    <div class="meter-input-wrapper">
                        <input type="number" 
                               name="returned_items[${index}][returned_colors_meters][${color.name}]" 
                               class="form-control color-meter-input"
                               step="0.01"
                               min="0"
                               max="${color.meter}"
                               placeholder="متری بەرگەڕاندۆ"
                               disabled
                               data-max-meter="${color.meter}"
                               data-unit-cost="${item.unitcost}">
                        <small class="text-muted">/ ${color.meter}م @ $${parseFloat(item.unitcost).toFixed(2)}/م</small>
                    </div>
                </div>
            `;
        });
    } else {
        colorsHtml = '<p class="text-muted">بێ رەنگ</p>';
    }

    const totalMeters = item.meters || 0;
    const totalPrice = (totalMeters * item.unitcost).toFixed(2);

    const html = `
        <div class="product-table">
            <div class="product-info" style="padding: 15px; background: white;">
                <img src="{{ asset('${item.product_image}') }}" 
                     class="product-img" 
                     alt="${item.product_name}">
                <div class="product-details" style="flex: 1;">
                    <strong>${item.product_name}</strong>
                    <small>کۆد: ${item.product_code}</small>
                    <small>پسوڵە: #${item.invoice_no}</small>
                    <small>بەرواری: ${item.order_date}</small>
                </div>
            </div>

            <div class="product-stats" style="padding: 15px; background: #f8f9fa; border-top: 1px solid #dee2e6;">
                <div class="stat-box">
                    <div class="stat-label">نرخی متر</div>
                    <div class="stat-value">$${parseFloat(item.unitcost).toFixed(2)}</div>
                </div>
                <div class="stat-box">
                    <div class="stat-label">کۆی متر</div>
                    <div class="stat-value">${totalMeters}م</div>
                </div>
                <div class="stat-box">
                    <div class="stat-label">کۆی نرخ</div>
                    <div class="stat-value">$${totalPrice}</div>
                </div>
            </div>
            
            <div class="product-row-expanded">
                <h6>
                    <i class="mdi mdi-palette me-2"></i> رەنگەکان - بەرگەڕاندەکان هەڵبژێرە
                </h6>
                ${colorsHtml}
            </div>
        </div>

        <input type="hidden" name="returned_items[${index}][product_id]" value="${item.product_id}">
        <input type="hidden" name="returned_items[${index}][unit_cost]" value="${item.unitcost}">
    `;

    container.insertAdjacentHTML('beforeend', html);
}

function attachAllEventListeners() {
    // Attach checkbox change listeners
    const checkboxes = document.querySelectorAll('.color-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', handleCheckboxChange);
    });

    // Attach meter input listeners
    const meterInputs = document.querySelectorAll('.color-meter-input');
    meterInputs.forEach(input => {
        input.addEventListener('input', calculateRefund);
        input.addEventListener('change', calculateRefund);
    });
}

function handleCheckboxChange(e) {
    const checkbox = e.target;
    const colorInputGroup = checkbox.closest('.color-input-group');
    const meterInput = colorInputGroup.querySelector('.color-meter-input');
    
    if (checkbox.checked) {
        meterInput.disabled = false;
        meterInput.focus();
    } else {
        meterInput.disabled = true;
        meterInput.value = '0';
    }
    
    calculateRefund();
}

function calculateRefund() {
    let totalRefund = 0;
    const checkboxes = document.querySelectorAll('.color-checkbox:checked');
    
    checkboxes.forEach(checkbox => {
        const colorInputGroup = checkbox.closest('.color-input-group');
        const meterInput = colorInputGroup.querySelector('.color-meter-input');
        const unitCost = parseFloat(meterInput.getAttribute('data-unit-cost')) || 0;
        const meters = parseFloat(meterInput.value) || 0;
        
        // Validate meters don't exceed max
        const maxMeter = parseFloat(meterInput.getAttribute('data-max-meter')) || 0;
        if (meters > maxMeter) {
            meterInput.value = maxMeter;
            totalRefund += maxMeter * unitCost;
        } else {
            totalRefund += meters * unitCost;
        }
    });

    document.getElementById('refundAmount').value = totalRefund.toFixed(2);
}
</script>

@endsection