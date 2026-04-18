@extends('admin_dashboard')
@section('admin')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>

<style>
.kpi-card {
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    overflow: hidden;
    transition: all 0.3s ease;
    border: none;
}

@media print {
    .page-title-box, 
    .breadcrumb, 
    .btn, 
    form, 
    .text-end {
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
                            <li class="breadcrumb-item"><a href="javascript: void(0);">وردەکاری داواکاری</a></li>
                        </ol>
                    </div>
                    <h4 class="page-title">وردەکاری داواکاری #{{ $order->id }}</h4>
                </div>
            </div>
        </div>     
        <!-- end page title -->

        <!-- PRINT BUTTON -->
        <div class="row mb-3">
            <div class="col-12 text-end">
                <button onclick="window.print()" class="btn btn-primary">
                    <i class="mdi mdi-printer me-1"></i> چاپکردن
                </button>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8 col-xl-12">
                <div class="card">
                    <div class="card-body">
                        <div class="tab-pane" id="settings">
                            <form method="post" action="{{ route('order.status.update') }}" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="id" value="{{ $order->id }}">
                                <h5 class="mb-4 text-uppercase"><i class="mdi mdi-account-circle me-1"></i> وردەکاری داواکاری</h5>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="firstname" class="form-label">وێنەی کڕیار</label>
                                            @if($order->customer && $order->customer->image)
                                                <img id="showImage" src="{{ asset($order->customer->image) }}" class="rounded-circle avatar-lg img-thumbnail" alt="profile-image">
                                            @else
                                                <img src="https://via.placeholder.com/100?text=No+Image" class="rounded-circle avatar-lg img-thumbnail" alt="profile-image">
                                            @endif
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="firstname" class="form-label">ناوی کڕیار</label>
                                            <p class="text-danger"><strong>{{ $order->customer->name ?? 'نەناسراو' }}</strong></p>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="firstname" class="form-label">ئیمەیڵی کڕیار</label>
                                            <p class="text-danger"><strong>{{ $order->customer->email ?? 'N/A' }}</strong></p>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="firstname" class="form-label">ژمارەی کڕیار</label>
                                            <p class="text-danger"><strong>{{ $order->customer->phone ?? 'N/A' }}</strong></p>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="firstname" class="form-label">بەرواری داواکاری</label>
                                            <p class="text-danger"><strong>{{ \Carbon\Carbon::parse($order->order_date)->format('Y-m-d') }}</strong></p>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="firstname" class="form-label">پسوڵەی داواکاری</label>
                                            <p class="text-danger"><strong>#{{ $order->id }}</strong></p>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="firstname" class="form-label">شێوازی پارەدان</label>
                                            <p class="text-danger">
                                                @if($order->payment_status == 'HandCash')
                                                    <span class="badge bg-info">دەستی</span>
                                                @elseif($order->payment_status == 'Cheque')
                                                    <span class="badge bg-warning">چەک</span>
                                                @elseif($order->payment_status == 'Bank')
                                                    <span class="badge bg-success">بانک</span>
                                                @else
                                                    <strong>{{ $order->payment_status }}</strong>
                                                @endif
                                            </p>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="firstname" class="form-label">بڕی پارەی دراو</label>
                                            <p class="text-success"><strong>${{ number_format($order->pay, 2) }}</strong></p>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="firstname" class="form-label">پارەی قەرز</label>
                                            <p class="text-danger"><strong>${{ number_format($order->due, 2) }}</strong></p>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="firstname" class="form-label">کۆی کاڵا</label>
                                            <p class="text-info"><strong>${{ number_format($order->sub_total, 2) }}</strong></p>
                                        </div>
                                    </div>
                                </div> <!-- end row -->
                                
                                <div class="text-end">
                                    @if($order->order_status !== 'cancelled')
                                        <button type="submit" class="btn btn-success waves-effect waves-light mt-2">
                                            <i class="mdi mdi-content-save me-1"></i> داواکاری تەواوبوو
                                        </button>
                                        <button type="button" class="btn btn-danger waves-effect waves-light mt-2" data-bs-toggle="modal" data-bs-target="#cancelOrderModal">
                                            <i class="mdi mdi-delete me-1"></i> لابردنی داواکاری
                                        </button>
                                    @else
                                        <span class="badge bg-danger" style="font-size: 1.1rem;">ئەم داواکاریە لابردراوە</span>
                                    @endif
                                </div>
                            </form>
                        </div>
                        <!-- end settings content-->

                        <!-- ORDER ITEMS TABLE - FIXED FOR NEW SYSTEM -->
                        <div class="col-12 mt-4">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="mb-3">ئایتمە کڕیاریەکان</h5>
                                    <table class="table table-bordered text-center">
                                        <thead>
                                            <tr style="background: linear-gradient(45deg, #0d6efd, #6610f2); color: white;">
                                                <th>#</th>
                                                <th>ناوی بەرهەم</th>
                                                <th>بڕ</th>
                                                <th>نرخ</th>
                                                <th>کۆی گشتی</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $sl = 1;
                                                $subTotal = 0;
                                            @endphp

                                            @forelse($orderItem as $item)
                                            @php
                                                // FIXED: Use simple quantity instead of meters
                                                $quantity = floatval($item->quantity ?? 0);
                                                $rowTotal = $quantity * floatval($item->unitcost);
                                                $subTotal += $rowTotal;
                                            @endphp
                                            <tr>
                                                <td><strong>{{ $sl++ }}</strong></td>
                                                <td><strong>{{ optional($item->product)->product_name ?? 'سڕاودەتوانی بەرهەم' }}</strong></td>
                                                <td>{{ number_format($quantity, 2) }}</td>
                                                <td>${{ number_format($item->unitcost, 2) }}</td>
                                                <td><strong>${{ number_format($rowTotal, 2) }}</strong></td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-3">ئایتم نیە</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                    <div class="text-end mt-2">
                                        <h5>کۆی کاڵا: <strong style="color: #0d6efd;">${{ number_format($subTotal, 2) }}</strong></h5>
                                    </div>
                                </div>
                            </div> <!-- end card -->
                        </div><!-- end col-->
                    </div>
                </div> <!-- end card-->
            </div> <!-- end col -->
        </div>
        <!-- end row-->
    </div> <!-- container -->
</div> <!-- content -->

<!-- BEAUTIFUL CANCEL ORDER MODAL -->
<div class="modal fade" id="cancelOrderModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl">
        <div class="modal-content" style="border-radius: 12px; box-shadow: 0 15px 50px rgba(0,0,0,0.15); border: none;">
            
            <!-- MODAL HEADER -->
            <div class="modal-header" style="background: linear-gradient(135deg, #f5576c 0%, #f093fb 100%); color: white; border: none; border-radius: 12px 12px 0 0;">
                <h5 class="modal-title" style="font-weight: 600; font-size: 1.2rem;">
                    <i class="mdi mdi-delete me-2"></i> لابردنی داواکاری
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
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 8px;">
                        <i class="mdi mdi-close me-1"></i> داخستن
                    </button>
                    <button type="submit" class="btn btn-danger" style="border-radius: 8px; background: linear-gradient(135deg, #f5576c 0%, #f093fb 100%); border: none;">
                        <i class="mdi mdi-check me-1"></i> پشتڕاستکردن و لابردن
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

@endsection