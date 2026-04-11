@extends('admin_dashboard')
@section('admin')

<style>
    .product-table {
        background: white;
        border-radius: 10px;
        overflow: hidden;
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

    .return-input {
        width: 80px;
        padding: 5px;
        border: 1px solid #dee2e6;
        border-radius: 4px;
    }

    .summary-box {
        background: #e8f4f8;
        border-left: 4px solid #2196f3;
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
                                    <option value="{{ $customer->id }}" data-due="{{ $customer->due }}">
                                        {{ $customer->name }} - بڕی قەرز: ${{ number_format($customer->due, 2) }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Products Table -->
                            <div id="productsTableContainer" style="display:none;">
                                <div class="table-responsive product-table mb-4">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr class="table-header">
                                                <th>وێنە</th>
                                                <th>ناوی ئایتم</th>
                                                <th>کۆدی ئایتم</th>
                                                <th>رەنگەکان</th>
                                                <th>نرخی متر</th>
                                                <th>کۆی متر</th>
                                                <th>متری بەرگەڕاندۆ</th>
                                                <th>کۆی پاشگەزی</th>
                                            </tr>
                                        </thead>
                                        <tbody id="productsTableBody">
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <hr style="display:none;" id="separatorLine">

                            <!-- Return Info Section -->
                            <div id="returnInfoSection" style="display:none;">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label"><strong>هۆی بەرگەڕاندن</strong></label>
                                        <textarea name="return_reason" class="form-control" rows="3" 
                                                  placeholder="بۆچی کڕیار بەرگەڕاند..."></textarea>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label"><strong>بڕی پاشگەزی (دۆلار)</strong></label>
                                        <input type="number" name="refund_amount" class="form-control" 
                                               step="0.01" min="0" value="0">
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
    const tableContainer = document.getElementById('productsTableContainer');
    const tableBody = document.getElementById('productsTableBody');
    const separatorLine = document.getElementById('separatorLine');
    const returnInfoSection = document.getElementById('returnInfoSection');
    const submitBtn = document.getElementById('submitBtn');
    
    if (!customerId) {
        tableContainer.style.display = 'none';
        separatorLine.style.display = 'none';
        returnInfoSection.style.display = 'none';
        submitBtn.style.display = 'none';
        return;
    }

    tableBody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">لە لادا کردنی بەرهەمەکان...</td></tr>';

    fetch(`/returned/customer/${customerId}/orders`)
        .then(response => response.json())
        .then(items => {
            tableBody.innerHTML = '';
            
            if (items.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="8" class="text-center text-warning">ئایتمێک نیە</td></tr>';
                return;
            }

            items.forEach((item, index) => {
                const colors = item.selected_colors || [];
                const colorHtml = colors.map(color => 
                    `<span class="color-badge">${color.name}: ${color.meter}م</span>`
                ).join('');

                const totalMeters = item.meters || 0;
                const refundTotal = (totalMeters * item.unitcost).toFixed(2);

                const row = `
                    <tr>
                        <td>
                            <img src="{{ asset('${item.product_image}') }}" style="width:40px; height:30px; border-radius:4px;">
                        </td>
                        <td><strong>${item.product_name}</strong></td>
                        <td>${item.product_code}</td>
                        <td>${colorHtml || '<span class="text-muted">بێ رەنگ</span>'}</td>
                        <td>$${parseFloat(item.unitcost).toFixed(2)}</td>
                        <td>${parseFloat(totalMeters).toFixed(2)}م</td>
                        <td>
                            <input type="number" 
                                   name="returned_items[${index}][returned_meters]" 
                                   class="return-input" 
                                   step="0.01" 
                                   min="0" 
                                   max="${totalMeters}" 
                                   value="0"
                                   onchange="calculateRefund()">
                            <small class="text-muted">/ ${parseFloat(totalMeters).toFixed(2)}م</small>
                        </td>
                        <td>
                            <span id="refund-${index}" class="text-danger font-weight-bold">$0.00</span>
                        </td>
                    </tr>
                    <input type="hidden" name="returned_items[${index}][product_id]" value="${item.product_id}">
                    <input type="hidden" name="returned_items[${index}][unit_cost]" value="${item.unitcost}">
                `;

                tableBody.insertAdjacentHTML('beforeend', row);
            });

            tableContainer.style.display = 'block';
            separatorLine.style.display = 'block';
            returnInfoSection.style.display = 'block';
            submitBtn.style.display = 'inline-block';
        })
        .catch(error => {
            console.error('Error:', error);
            tableBody.innerHTML = '<tr><td colspan="8" class="text-center text-danger">خۆیەتی لە لادا کردنی دراوا</td></tr>';
        });
}

function calculateRefund() {
    const inputs = document.querySelectorAll('input[name*="returned_meters"]');
    let totalRefund = 0;

    inputs.forEach((input, index) => {
        const unitCost = document.querySelector(`input[name="returned_items[${index}][unit_cost]"]`);
        const returnedMeters = parseFloat(input.value) || 0;
        const unitCostValue = parseFloat(unitCost.value) || 0;
        const refund = returnedMeters * unitCostValue;

        document.getElementById(`refund-${index}`).textContent = '$' + refund.toFixed(2);
        totalRefund += refund;
    });

    // Update refund_amount automatically
    document.querySelector('input[name="refund_amount"]').value = totalRefund.toFixed(2);
}
</script>

@endsection