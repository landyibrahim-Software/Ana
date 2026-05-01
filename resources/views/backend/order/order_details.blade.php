@extends('admin_dashboard')
@section('admin')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>

<style>
.info-card {
    border-radius: 12px;
    padding: 20px;
    border: none;
    transition: all 0.3s ease;
}

.info-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
}

.info-label {
    font-size: 0.9rem;
    color: #666;
    font-weight: 500;
    margin-bottom: 8px;
    display: block;
}

.info-value {
    font-size: 1.1rem;
    font-weight: 600;
    color: #2c3e50;
}

.kpi-card {
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    overflow: hidden;
    transition: all 0.3s ease;
    border: none;
}

.kpi-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.15);
}

.status-badge {
    padding: 8px 16px;
    border-radius: 25px;
    font-weight: 600;
    display: inline-block;
    font-size: 0.9rem;
}

@media print {
    .page-title-box, 
    .breadcrumb, 
    .btn, 
    form, 
    .text-end,
    .action-buttons {
        display: none !important;
    }
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    body {
        padding: 20px !important;
    }
}
</style>

<div class="content">
    <!-- Start Content-->
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('pending.order') }}">داواکاریەکان</a></li>
                            <li class="breadcrumb-item active">وردەکاری #{{ $order->id }}</li>
                        </ol>
                    </div>
                    <h4 class="page-title">وردەکاری داواکاری #{{ $order->id }}</h4>
                </div>
            </div>
        </div>     
        <!-- end page title -->

        <!-- ACTION BUTTONS -->
        <div class="row mb-4 action-buttons">
            <div class="col-12">
                <div class="d-flex gap-2 justify-content-end">
                    <button onclick="window.print()" class="btn btn-outline-primary btn-lg">
                        <i class="mdi mdi-printer me-2"></i> چاپکردن
                    </button>
                </div>
            </div>
        </div>

        <!-- CUSTOMER INFO CARDS -->
        <div class="row g-3 mb-4">
            <!-- Customer Image & Name -->
            <div class="col-md-6">
                <div class="card info-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <div class="text-center">
                        @if($order->customer && $order->customer->image)
                            <img src="{{ asset($order->customer->image) }}" class="rounded-circle mb-3" style="width: 100px; height: 100px; object-fit: cover; border: 4px solid white;">
                        @else
                            <img src="https://via.placeholder.com/100?text=No+Image" class="rounded-circle mb-3" style="width: 100px; height: 100px; object-fit: cover; border: 4px solid white;">
                        @endif
                        <h4 style="margin-bottom: 5px;">{{ $order->customer->name ?? 'نەناسراو' }}</h4>
                        <p style="opacity: 0.9; margin-bottom: 0;">کڕیار</p>
                    </div>
                </div>
            </div>

            <!-- Phone Number -->
            <div class="col-md-6">
                <div class="card info-card">
                    <span class="info-label"><i class="mdi mdi-phone me-2" style="color: #667eea;"></i>ژمارەی پەیوەندی</span>
                    <p class="info-value mb-0">{{ $order->customer->phone ?? 'نیە' }}</p>
                </div>
            </div>
        </div>

        <!-- ORDER INFO CARDS -->
        <div class="row g-3 mb-4">
            <!-- Order ID -->
            <div class="col-md-3">
                <div class="card info-card" style="border-left: 4px solid #0d6efd;">
                    <span class="info-label"><i class="mdi mdi-receipt me-2" style="color: #0d6efd;"></i>ژمارەی پسوڵە</span>
                    <p class="info-value mb-0" style="color: #0d6efd;">#{{ $order->id }}</p>
                </div>
            </div>

            <!-- Order Date -->
            <div class="col-md-3">
                <div class="card info-card" style="border-left: 4px solid #28a745;">
                    <span class="info-label"><i class="mdi mdi-calendar me-2" style="color: #28a745;"></i>بەرواری داواکاری</span>
                    <p class="info-value mb-0" style="color: #28a745;">{{ \Carbon\Carbon::parse($order->order_date)->format('Y-m-d') }}</p>
                </div>
            </div>

            <!-- Payment Method -->
            <div class="col-md-3">
                <div class="card info-card" style="border-left: 4px solid #ffc107;">
                    <span class="info-label"><i class="mdi mdi-cash me-2" style="color: #ffc107;"></i>شێوازی پارەدان</span>
                    <p class="info-value mb-0">
                        @if($order->payment_status == 'HandCash')
                            <span class="status-badge" style="background: #d1ecf1; color: #0c5460;">دەستی</span>
                        @elseif($order->payment_status == 'Cheque')
                            <span class="status-badge" style="background: #fff3cd; color: #856404;">چەک</span>
                        @elseif($order->payment_status == 'Bank')
                            <span class="status-badge" style="background: #d4edda; color: #155724;">بانک</span>
                        @else
                            <span class="status-badge">{{ $order->payment_status }}</span>
                        @endif
                    </p>
                </div>
            </div>

            <!-- Order Status -->
            <div class="col-md-3">
                <div class="card info-card" style="border-left: 4px solid #dc3545;">
                    <span class="info-label"><i class="mdi mdi-information me-2" style="color: #dc3545;"></i>دۆخی داواکاری</span>
                    <p class="info-value mb-0">
                        @if($order->order_status == 'pending')
                            <span class="status-badge" style="background: #f8d7da; color: #721c24;">چاوەروانی</span>
                        @elseif($order->order_status == 'complete')
                            <span class="status-badge" style="background: #d4edda; color: #155724;">تەواو</span>
                        @elseif($order->order_status == 'cancelled')
                            <span class="status-badge" style="background: #f5f5f5; color: #666;">لابردراو</span>
                        @else
                            <span class="status-badge">{{ $order->order_status }}</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <!-- FINANCIAL INFO - KPI CARDS -->
        <div class="row g-3 mb-4">
            <!-- Sub Total -->
            <div class="col-md-3">
                <div class="card kpi-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <div class="card-body text-center">
                        <i class="mdi mdi-cart" style="font-size: 2rem; opacity: 0.5;"></i>
                        <h5 style="margin-top: 10px; margin-bottom: 5px;">کۆی کاڵا</h5>
                        <h3 style="margin: 0;">${{ number_format($order->sub_total, 2) }}</h3>
                    </div>
                </div>
            </div>

            <!-- Paid Amount -->
            <div class="col-md-3">
                <div class="card kpi-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white;">
                    <div class="card-body text-center">
                        <i class="mdi mdi-cash-check" style="font-size: 2rem; opacity: 0.5;"></i>
                        <h5 style="margin-top: 10px; margin-bottom: 5px;">پارەی دراو</h5>
                        <h3 style="margin: 0;">${{ number_format($order->pay, 2) }}</h3>
                    </div>
                </div>
            </div>

            <!-- Due Amount -->
            <div class="col-md-3">
                <div class="card kpi-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">
                    <div class="card-body text-center">
                        <i class="mdi mdi-alert-circle" style="font-size: 2rem; opacity: 0.5;"></i>
                        <h5 style="margin-top: 10px; margin-bottom: 5px;">پارەی قەرز</h5>
                        <h3 style="margin: 0;">${{ number_format($order->due, 2) }}</h3>
                    </div>
                </div>
            </div>

            <!-- Items Count -->
            <div class="col-md-3">
                <div class="card kpi-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white;">
                    <div class="card-body text-center">
                        <i class="mdi mdi-package" style="font-size: 2rem; opacity: 0.5;"></i>
                        <h5 style="margin-top: 10px; margin-bottom: 5px;">ئایتمەکان</h5>
                        <h3 style="margin: 0;">{{ $orderItem->count() }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- ORDER ITEMS TABLE -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">
                            <i class="mdi mdi-package-multiple me-2" style="color: #0d6efd;"></i>ئایتمە کڕیاریەکان
                        </h5>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr style="background: linear-gradient(45deg, #0d6efd, #6610f2); color: white;">
                                        <th>#</th>
                                        <th>ناوی بەرهەم</th>
                                        <th class="text-center">بڕ</th>
                                        <th class="text-end">نرخ</th>
                                        <th class="text-end">کۆی</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $sl = 1;
                                        $calculatedTotal = 0;
                                    @endphp

                                    @forelse($orderItem as $item)
                                    @php
                                        $quantity = floatval($item->quantity ?? 0);
                                        $rowTotal = $quantity * floatval($item->unitcost);
                                        $calculatedTotal += $rowTotal;
                                    @endphp
                                    <tr>
                                        <td><strong>{{ $sl++ }}</strong></td>
                                        <td>
                                            <strong>{{ optional($item->product)->product_name ?? 'سڕاودەتوانی بەرهەم' }}</strong>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark">{{ number_format($quantity, 2) }}</span>
                                        </td>
                                        <td class="text-end">
                                            <strong>${{ number_format($item->unitcost, 2) }}</strong>
                                        </td>
                                        <td class="text-end">
                                            <strong style="color: #0d6efd;">${{ number_format($rowTotal, 2) }}</strong>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            <i class="mdi mdi-inbox" style="font-size: 2rem; opacity: 0.5;"></i>
                                            <p class="mt-2">ئایتم نیە</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="text-end">
                                    <h5 style="color: #666; margin-bottom: 0;">
                                        کۆی کاڵا: 
                                        <strong style="color: #0d6efd; font-size: 1.2rem;">
                                            ${{ number_format($calculatedTotal, 2) }}
                                        </strong>
                                    </h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CANCEL ORDER SECTION (Only if not cancelled) -->
        @if($order->order_status !== 'cancelled')
        <div class="row mb-4">
            <div class="col-12">
                <div class="alert alert-warning" role="alert" style="border-radius: 12px; border-left: 4px solid #ffc107;">
                    <h5 class="alert-heading mb-2">
                        <i class="mdi mdi-information me-2"></i>ئەگەر دەتەوێ داواکاری لابدە
                    </h5>
                    <p class="mb-0">
                        لە خوار کلیک دواتر دەتوانی ئایتمەکان هەڵبژێریت و بڕی کەمکردن بنوسیت
                    </p>
                    <button type="button" class="btn btn-warning btn-lg mt-3" data-bs-toggle="modal" data-bs-target="#cancelOrderModal">
                        <i class="mdi mdi-delete-alert me-2"></i>داواکاری لابردن
                    </button>
                </div>
            </div>
        </div>
        @endif

    </div> <!-- container -->
</div> <!-- content -->

<!-- CANCEL ORDER MODAL - Only show if not cancelled -->
@if($order->order_status !== 'cancelled')
<div class="modal fade" id="cancelOrderModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl">
        <div class="modal-content" style="border-radius: 12px; box-shadow: 0 15px 50px rgba(0,0,0,0.15); border: none;">
            
            <!-- MODAL HEADER -->
            <div class="modal-header" style="background: linear-gradient(135deg, #f5576c 0%, #f093fb 100%); color: white; border: none; border-radius: 12px 12px 0 0;">
                <h5 class="modal-title" style="font-weight: 600; font-size: 1.2rem;">
                    <i class="mdi mdi-delete-alert me-2"></i> داواکاری لابردن
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form method="POST" action="{{ route('order.cancel') }}" id="cancelForm">
                @csrf
                <input type="hidden" name="order_id" value="{{ $order->id }}">
                
                <!-- MODAL BODY -->
                <div class="modal-body" style="padding: 25px;">

                    <!-- SECTION 1: ITEMS SELECTION -->
                    <div class="mb-4">
                        <h6 class="mb-3" style="color: #2c3e50; font-weight: 600;">
                            <i class="mdi mdi-checkbox-marked me-2" style="color: #f5576c;"></i> ئایتمەکان کە دەتەوێ لایببەیت
                        </h6>
                        <div class="table-responsive">
                            <table class="table table-hover" style="margin-bottom: 0;">
                                <thead style="background-color: #f8f9fa;">
                                    <tr>
                                        <th style="width: 50px; text-align: center;"><i class="mdi mdi-checkbox-blank-outline"></i></th>
                                        <th>ناوی ئایتم</th>
                                        <th style="text-align: right;">نرخ</th>
                                        <th style="text-align: right;">بڕ</th>
                                        <th style="text-align: right;">کۆی</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($orderItem as $item)
                                    @php
                                        $quantity = floatval($item->quantity ?? 0);
                                        $itemTotal = $quantity * floatval($item->unitcost);
                                    @endphp
                                    <tr>
                                        <td style="text-align: center;">
                                            <input type="checkbox" name="rejected_items[]" value="{{ $item->id }}" class="form-check-input item-checkbox" data-item-cost="{{ $itemTotal }}">
                                        </td>
                                        <td><strong>{{ optional($item->product)->product_name ?? 'سڕاودەتوانی' }}</strong></td>
                                        <td style="text-align: right;">${{ number_format($item->unitcost, 2) }}</td>
                                        <td style="text-align: right;">{{ number_format($quantity, 2) }}</td>
                                        <td style="text-align: right;"><strong>${{ number_format($itemTotal, 2) }}</strong></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <hr style="border-top: 2px dashed #e9ecef;">

                    <!-- SECTION 2: REFUND CALCULATION -->
                    <div class="mb-4">
                        <h6 class="mb-3" style="color: #2c3e50; font-weight: 600;">
                            <i class="mdi mdi-cash-refund me-2" style="color: #f5576c;"></i> پارە کەمکردن
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label" style="color: #555; font-weight: 500;">بڕی کەمکردن (خۆکارانە)</label>
                                    <input type="number" id="autoRefundAmount" class="form-control" style="border-radius: 8px; border: 2px solid #e9ecef;" step="0.01" readonly>
                                    <small class="text-muted" style="display: block; margin-top: 8px;">ئەم بڕە بە خۆکارانەوە حیساب دەکرێت</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label" style="color: #555; font-weight: 500;">یان بە دەستی بنووسە</label>
<input type="number" id="manualRefundAmount" name="refund_amount" class="form-control" style="border-radius: 8px; border: 2px solid #e9ecef;" step="0.01" placeholder="0.00">
                                    <small class="text-muted" style="display: block; margin-top: 8px;">ئەگەر دەتەوێ جیاوازی بڕ بنووسە</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 3: REFUND FROM -->
                    <div class="mb-4">
                        <h6 class="mb-3" style="color: #2c3e50; font-weight: 600;">
                            <i class="mdi mdi-source-branch me-2" style="color: #f5576c;"></i> کەمکردن لە کوێ
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check" style="padding: 12px 15px; background: #f8f9fa; border-radius: 8px; margin-bottom: 10px;">
                                    <input class="form-check-input" type="radio" name="refund_from" value="due" id="refund_from_due" checked>
                                    <label class="form-check-label" for="refund_from_due" style="margin-left: 8px; cursor: pointer; margin-bottom: 0;">
                                        <strong>لە قەرزی کڕیار (Due)</strong><br>
                                        <span style="color: #666; font-size: 0.9rem;">پارەی قەرز: <strong style="color: #f5576c;">${{ number_format($order->due, 2) }}</strong></span>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check" style="padding: 12px 15px; background: #f8f9fa; border-radius: 8px; margin-bottom: 10px;">
                                    <input class="form-check-input" type="radio" name="refund_from" value="paid" id="refund_from_paid">
                                    <label class="form-check-label" for="refund_from_paid" style="margin-left: 8px; cursor: pointer; margin-bottom: 0;">
                                        <strong>پارەی دراو (Paid)</strong><br>
                                        <span style="color: #666; font-size: 0.9rem;">پارەی دراو: <strong style="color: #27ae60;">${{ number_format($order->pay, 2) }}</strong></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr style="border-top: 2px dashed #e9ecef;">

                    <!-- SECTION 4: SUMMARY CARDS -->
                    <div class="row">
                        <div class="col-md-6 col-lg-3">
                            <div class="card kpi-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center; border: none;">
                                <div style="font-size: 2.5rem; font-weight: 700; margin-bottom: 5px;" id="cancelItemCount">0</div>
                                <div style="font-size: 0.95rem; opacity: 0.9;">ئایتمە سڕاوەکان</div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <div class="card kpi-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 20px; text-align: center; border: none;">
                                <div style="font-size: 2.5rem; font-weight: 700; margin-bottom: 5px;">$<span id="cancelRefundAmount">0.00</span></div>
                                <div style="font-size: 0.95rem; opacity: 0.9;">کۆی کەمکردن</div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <div class="card kpi-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 20px; text-align: center; border: none;">
                                <div style="font-size: 2.5rem; font-weight: 700; margin-bottom: 5px;">$<span id="cancelNewDue">{{ number_format($order->due, 2) }}</span></div>
                                <div style="font-size: 0.95rem; opacity: 0.9;">دوایی قەرز</div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- MODAL FOOTER -->
                <div class="modal-footer" style="border-top: 2px solid #f0f0f0; padding: 20px;">
                    <button type="button" class="btn btn-secondary btn-lg" data-bs-dismiss="modal" style="border-radius: 8px;">
                        <i class="mdi mdi-close me-1"></i> داخستن
                    </button>
                    <button type="submit" class="btn btn-danger btn-lg" style="border-radius: 8px; background: linear-gradient(135deg, #f5576c 0%, #f093fb 100%); border: none;">
                        <i class="mdi mdi-check-circle me-1"></i> پشتڕاستکردن و لابردن
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- CANCEL ORDER JAVASCRIPT -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const itemCheckboxes = document.querySelectorAll('.item-checkbox');
    const autoRefundInput = document.getElementById('autoRefundAmount');
    const manualRefundInput = document.getElementById('manualRefundAmount');
    const refundFromRadios = document.querySelectorAll('input[name="refund_from"]');
    
    const currentDue = parseFloat('{{ $order->due }}');
    const currentPaid = parseFloat('{{ $order->pay }}');
    const orderSubtotal = parseFloat('{{ $order->sub_total }}');

    function updateRefundCalculations() {
        let totalRefund = 0;
        let checkedCount = 0;

        itemCheckboxes.forEach(checkbox => {
            if (checkbox.checked) {
                totalRefund += parseFloat(checkbox.dataset.itemCost || 0);
                checkedCount++;
            }
        });

        // Update auto refund
        autoRefundInput.value = totalRefund.toFixed(2);

        // Use manual if entered, otherwise use auto
        const refundAmount = parseFloat(manualRefundInput.value) || totalRefund;

        // Update summary
        document.getElementById('cancelItemCount').textContent = checkedCount;
        document.getElementById('cancelRefundAmount').textContent = refundAmount.toFixed(2);

        // Calculate new due based on selected option
        const refundFrom = document.querySelector('input[name="refund_from"]:checked').value;
        let newDue = currentDue;

        if (refundFrom === 'due') {
            // Refund from DUE: Just reduce the due by refund amount
            newDue = currentDue - refundAmount;
        } else if (refundFrom === 'paid') {
            // Refund from PAID: Only reduce due by order subtotal
            newDue = currentDue - orderSubtotal;
        }

        newDue = Math.max(0, newDue);
        
        document.getElementById('cancelNewDue').textContent = newDue.toFixed(2);
    }

    // Update when checkboxes change
    itemCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateRefundCalculations);
    });

    // Update when manual refund changes
    manualRefundInput.addEventListener('input', updateRefundCalculations);

    // Update when refund from option changes
    refundFromRadios.forEach(radio => {
        radio.addEventListener('change', updateRefundCalculations);
    });

    // Initial calculation
    updateRefundCalculations();
});
</script>
@endif

@endsection