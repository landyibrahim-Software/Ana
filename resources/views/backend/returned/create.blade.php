@extends('admin_dashboard')
@section('admin')

<style>
    .product-table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        border-radius: 8px;
        overflow: hidden;
        margin-bottom: 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .product-table thead {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        font-weight: 600;
    }

    .product-table th {
        padding: 15px;
        text-align: center;
        border-bottom: 2px solid #dee2e6;
    }

    .product-table td {
        padding: 12px 15px;
        border-bottom: 1px solid #dee2e6;
    }

    .product-table tbody tr:hover {
        background: #f8f9fa;
    }

    .color-badge {
        display: inline-block;
        background: #e9ecef;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        margin: 2px;
    }

    .color-return-box {
        background: #f8f9fa;
        padding: 10px;
        border-radius: 6px;
        margin-bottom: 8px;
        border-left: 3px solid #667eea;
    }

    .color-return-box label {
        display: flex;
        align-items: center;
        margin-bottom: 8px;
        cursor: pointer;
        font-weight: 500;
    }

    .color-return-box input[type="checkbox"] {
        width: 18px;
        height: 18px;
        margin-right: 10px;
        cursor: pointer;
    }

    .meter-input {
        width: 110px;
        padding: 6px 8px;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        margin-top: 5px;
    }

    .return-info-box {
        background: #e8f4f8;
        border-left: 4px solid #2196f3;
        padding: 20px;
        border-radius: 8px;
        margin-top: 30px;
        margin-bottom: 20px;
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

                            <!-- Orders Table -->
                            <div id="ordersContainer" style="display:none;">
                                <h5 class="mb-3">
                                    <i class="mdi mdi-history me-2"></i> مێژووی کڕین
                                </h5>
                                <div class="table-responsive">
                                    <table class="product-table">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>ئایتم</th>
                                                <th>رەنگەکان</th>
                                                <th>نرخی متر</th>
                                                <th>کۆی متر</th>
                                                <th>کۆی گشتی</th>
                                                <th>بەرگەڕاندن</th>
                                            </tr>
                                        </thead>
                                        <tbody id="ordersTableBody">
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Return Info -->
                            <div id="returnInfoSection" style="display:none;">
                                <div class="return-info-box">
                                    <h6 class="mb-3">زانیاری بەرگەڕاندن</h6>
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
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Buttons -->
                            <div class="text-end">
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
document.getElementById('customerSelect').addEventListener('change', loadCustomerOrders);

function loadCustomerOrders() {
    const customerId = document.getElementById('customerSelect').value;
    const container = document.getElementById('ordersContainer');
    const tableBody = document.getElementById('ordersTableBody');
    const returnInfoSection = document.getElementById('returnInfoSection');
    const submitBtn = document.getElementById('submitBtn');

    if (!customerId) {
        container.style.display = 'none';
        returnInfoSection.style.display = 'none';
        submitBtn.style.display = 'none';
        return;
    }

    tableBody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">لە لادا کردنی دراوا...</td></tr>';

    fetch(`/returned/customer/${customerId}/orders`)
        .then(response => response.json())
        .then(items => {
            tableBody.innerHTML = '';

            if (items.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="7" class="text-center text-warning">ئایتمێک نیە</td></tr>';
                container.style.display = 'block';
                return;
            }

            items.forEach((item, index) => {
                addTableRow(item, index);
            });

            container.style.display = 'block';
            returnInfoSection.style.display = 'block';
            submitBtn.style.display = 'inline-block';
            attachCheckboxListeners();
        })
        .catch(error => {
            console.error('Error:', error);
            tableBody.innerHTML = '<tr><td colspan="7" class="text-center text-danger">خۆیەتی</td></tr>';
        });
}

function addTableRow(item, index) {
    const tableBody = document.getElementById('ordersTableBody');
    
    // Colors display
    let colorsDisplay = '';
    if (item.selected_colors && item.selected_colors.length > 0) {
        item.selected_colors.forEach(color => {
            colorsDisplay += `<span class="color-badge">${color.name}: ${color.meter}م</span>`;
        });
    } else {
        colorsDisplay = '<span class="text-muted">بێ رەنگ</span>';
    }

    // Colors return inputs
    let colorsReturn = '';
    if (item.selected_colors && item.selected_colors.length > 0) {
        item.selected_colors.forEach(color => {
            colorsReturn += `
                <div class="color-return-box">
                    <label>
                        <input type="checkbox" class="color-checkbox"
                               data-color="${color.name}"
                               data-unit-cost="${item.unitcost}">
                        <strong>${color.name}</strong> - ${color.meter}م
                    </label>
                    <input type="number"
                           name="returned_items[${index}][returned_colors][${color.name}]"
                           class="form-control meter-input color-meter"
                           step="0.01"
                           min="0"
                           max="${color.meter}"
                           disabled
                           data-unit-cost="${item.unitcost}">
                    <small class="text-muted">/ ${color.meter}م @ $${parseFloat(item.unitcost).toFixed(2)}/م</small>
                </div>
            `;
        });
    }

    const totalPrice = (item.meters * item.unitcost).toFixed(2);

    const row = `
        <tr>
            <td>${index + 1}</td>
            <td><strong>${item.product_name}</strong><br><small class="text-muted">${item.product_code}</small></td>
            <td>${colorsDisplay}</td>
            <td>$${parseFloat(item.unitcost).toFixed(2)}</td>
            <td>${parseFloat(item.meters).toFixed(2)}م</td>
            <td>$${totalPrice}</td>
            <td style="max-width: 300px;">
                <div style="max-height: 200px; overflow-y: auto;">
                    ${colorsReturn}
                </div>
                <input type="hidden" name="returned_items[${index}][product_id]" value="${item.product_id}">
                <input type="hidden" name="returned_items[${index}][unit_cost]" value="${item.unitcost}">
            </td>
        </tr>
    `;

    tableBody.insertAdjacentHTML('beforeend', row);
}

function attachCheckboxListeners() {
    const checkboxes = document.querySelectorAll('.color-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const colorReturnBox = this.closest('.color-return-box');
            const meterInput = colorReturnBox.querySelector('.color-meter');
            
            if (this.checked) {
                meterInput.disabled = false;
                meterInput.focus();
            } else {
                meterInput.disabled = true;
                meterInput.value = '0';
            }
            calculateRefund();
        });
    });

    const meterInputs = document.querySelectorAll('.color-meter');
    meterInputs.forEach(input => {
        input.addEventListener('input', calculateRefund);
    });
}

function calculateRefund() {
    let totalRefund = 0;
    const checkboxes = document.querySelectorAll('.color-checkbox:checked');

    checkboxes.forEach(checkbox => {
        const colorReturnBox = checkbox.closest('.color-return-box');
        const meterInput = colorReturnBox.querySelector('.color-meter');
        const meters = parseFloat(meterInput.value) || 0;
        const unitCost = parseFloat(meterInput.getAttribute('data-unit-cost')) || 0;
        totalRefund += meters * unitCost;
    });

    document.getElementById('refundAmount').value = totalRefund.toFixed(2);
}
</script>

@endsection