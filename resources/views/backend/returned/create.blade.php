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
    }

    .table-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
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
        padding: 10px;
        border-radius: 6px;
        margin-bottom: 10px;
    }

    .color-input-group label {
        display: flex;
        align-items: center;
        margin-bottom: 8px;
        cursor: pointer;
    }

    .color-input-group input[type="checkbox"] {
        margin-right: 10px;
        cursor: pointer;
    }

    .color-input-group input[type="number"] {
        width: 100px;
        padding: 5px;
        border: 1px solid #dee2e6;
        border-radius: 4px;
    }

    .return-info-section {
        background: #e8f4f8;
        border-left: 4px solid #2196f3;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .product-row-expanded {
        background: #f8f9fa;
        padding: 15px;
        border-left: 4px solid #667eea;
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
            <div class="col-lg-12">
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

                            <!-- Products Section -->
                            <div id="productsContainer" style="display:none;">
                                <h5 class="mb-3">مێژووی کڕین</h5>
                                <div id="productsListContainer"></div>
                            </div>

                            <!-- Return Info -->
                            <div id="returnInfoSection" style="display:none;">
                                <hr>
                                <div class="return-info-section">
                                    <h6>زانیاری بەرگەڕاندن</h6>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label"><strong>هۆی بەرگەڕاندن</strong></label>
                                            <textarea name="return_reason" class="form-control" rows="3" 
                                                      placeholder="بۆچی کڕیار بەرگەڕاند..."></textarea>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label"><strong>بڕی پاشگەزی (دۆلار)</strong></label>
                                            <input type="number" name="refund_amount" id="refundAmount" class="form-control" 
                                                   step="0.01" min="0" value="0" required>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Buttons -->
                            <div class="text-end mt-4">
                                <a href="{{ route('returned.index') }}" class="btn btn-secondary">لابردن</a>
                                <button type="submit" class="btn btn-success" id="submitBtn" style="display:none;">تۆمار بکە</button>
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
    const listContainer = document.getElementById('productsListContainer');
    const returnInfoSection = document.getElementById('returnInfoSection');
    const submitBtn = document.getElementById('submitBtn');
    
    if (!customerId) {
        container.style.display = 'none';
        returnInfoSection.style.display = 'none';
        submitBtn.style.display = 'none';
        return;
    }

    listContainer.innerHTML = '<p class="text-muted">لە لادا کردنی مێژووی کڕین...</p>';

    fetch(`/returned/customer/${customerId}/orders`)
        .then(response => response.json())
        .then(items => {
            listContainer.innerHTML = '';
            
            if (items.length === 0) {
                listContainer.innerHTML = '<p class="text-warning">ئایتمێک نیە</p>';
                return;
            }

            items.forEach((item, index) => {
                addProductCard(item, index);
            });

            container.style.display = 'block';
            returnInfoSection.style.display = 'block';
            submitBtn.style.display = 'inline-block';
        })
        .catch(error => {
            console.error('Error:', error);
            listContainer.innerHTML = '<p class="text-danger">خۆیەتی لە لادا کردنی دراوا</p>';
        });
}

function addProductCard(item, index) {
    const container = document.getElementById('productsListContainer');
    
    // Build colors HTML
    let colorsHtml = '';
    if (item.colors && item.colors.length > 0) {
        item.colors.forEach(color => {
            colorsHtml += `
                <div class="color-input-group">
                    <label>
                        <input type="checkbox" 
                               name="returned_items[${index}][returned_colors][${color.name}]" 
                               value="${color.meter}"
                               onchange="calculateRefund()">
                        <strong>${color.name}</strong> - ${color.meter}م
                    </label>
                    <div style="margin-left: 30px;">
                        <input type="number" 
                               name="returned_items[${index}][returned_colors_meters][${color.name}]" 
                               class="form-control"
                               step="0.01"
                               min="0"
                               max="${color.meter}"
                               placeholder="متری بەرگەڕاندۆ"
                               disabled
                               onchange="calculateRefund()">
                        <small class="text-muted">/ ${color.meter}م @ $${parseFloat(item.unitcost).toFixed(2)}/م</small>
                    </div>
                </div>
            `;
        });
    }

    const totalMeters = item.meters || 0;
    const totalPrice = (totalMeters * item.unitcost).toFixed(2);

    const html = `
        <div class="product-table" style="margin-bottom: 20px;">
            <table class="table mb-0">
                <tbody>
                    <tr>
                        <td style="width: 80px;">
                            <img src="{{ asset('${item.product_image}') }}" 
                                 style="width: 60px; height: 50px; border-radius: 4px; object-fit: cover;">
                        </td>
                        <td>
                            <strong>${item.product_name}</strong><br>
                            <small class="text-muted">کۆد: ${item.product_code}</small><br>
                            <small class="text-muted">پسوڵە: #${item.invoice_no}</small>
                        </td>
                        <td class="text-center">
                            <strong>نرخی متر</strong><br>
                            $${parseFloat(item.unitcost).toFixed(2)}
                        </td>
                        <td class="text-center">
                            <strong>کۆی متر</strong><br>
                            ${totalMeters}م
                        </td>
                        <td class="text-center">
                            <strong>کۆی نرخ</strong><br>
                            $${totalPrice}
                        </td>
                    </tr>
                </tbody>
            </table>
            
            <div class="product-row-expanded">
                <h6>رەنگەکان - بەرگەڕاندەکان هەڵبژێرە</h6>
                ${colorsHtml}
            </div>
        </div>

        <input type="hidden" name="returned_items[${index}][product_id]" value="${item.product_id}">
        <input type="hidden" name="returned_items[${index}][unit_cost]" value="${item.unitcost}">
    `;

    container.insertAdjacentHTML('beforeend', html);
}

function calculateRefund() {
    let totalRefund = 0;
    
    // Get all returned items
    const form = document.getElementById('returnForm');
    const formData = new FormData(form);
    
    // Iterate through all returned color inputs
    const returnedMetersInputs = form.querySelectorAll('input[name*="returned_colors_meters"]');
    
    returnedMetersInputs.forEach(input => {
        const isChecked = input.parentElement.querySelector('input[type="checkbox"]').checked;
        if (isChecked) {
            const meters = parseFloat(input.value) || 0;
            const unitCostInput = input.closest('.product-table').querySelector('input[name*="unit_cost"]');
            const unitCost = parseFloat(unitCostInput.value) || 0;
            totalRefund += meters * unitCost;
        }
    });

    document.getElementById('refundAmount').value = totalRefund.toFixed(2);
}

// Enable/disable meter input when checkbox changes
document.addEventListener('change', function(e) {
    if (e.target.type === 'checkbox' && e.target.name.includes('returned_colors[')) {
        const meterInput = e.target.parentElement.querySelector('input[type="number"]');
        if (meterInput) {
            meterInput.disabled = !e.target.checked;
            if (!e.target.checked) {
                meterInput.value = '0';
            }
            calculateRefund();
        }
    }
});
</script>

@endsection