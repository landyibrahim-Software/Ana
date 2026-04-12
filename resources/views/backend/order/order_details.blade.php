@extends('admin_dashboard')
@section('admin')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>

<style>
.color-badge {
    display: inline-block;
    background: #e9ecef;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    margin: 2px;
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
                            <li class="breadcrumb-item"><a href="javascript: void(0);">وردەکاری داواکاری </a></li>
                        </ol>
                    </div>
                    <h4 class="page-title"> وردەکاری داواکاری</h4>
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
                                            <label for="firstname" class="form-label"> وێنەی کڕیار</label>
                                            <img id="showImage" src="{{ asset($order->customer->image ) }}" class="rounded-circle avatar-lg img-thumbnail" alt="profile-image">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="firstname" class="form-label">ناوی کڕیار</label>
                                            <p class="text-danger"> {{ $order->customer->name }} </p>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="firstname" class="form-label">ئیمەیڵی کڕیار</label>
                                            <p class="text-danger"> {{ $order->customer->email }} </p>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="firstname" class="form-label">ژمارەی کڕیار</label>
                                            <p class="text-danger"> {{ $order->customer->phone }} </p>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="firstname" class="form-label">بەرواری داواکاری </label>
                                            <p class="text-danger"> {{ $order->order_date }} </p>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="firstname" class="form-label">پسوڵەی داواکاری </label>
                                            <p class="text-danger"> {{ $order->id }} </p>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="firstname" class="form-label">شێوازی پارەدان </label>
                                            <p class="text-danger"> {{ $order->payment_status }} </p>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="firstname" class="form-label">بڕی پارە </label>
                                            <p class="text-danger"> {{ $order->pay }} </p>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="firstname" class="form-label">پارەی قەرز </label>
                                            <p class="text-danger"> {{ $order->due }} </p>
                                        </div>
                                    </div>
                                </div> <!-- end row -->
                                
                                <div class="text-end">
                                    <button type="submit" class="btn btn-success waves-effect waves-light mt-2"><i class="mdi mdi-content-save"></i> داواکاری تەواوبوو </button>
                                    <button type="button" class="btn btn-danger waves-effect waves-light mt-2" data-bs-toggle="modal" data-bs-target="#cancelOrderModal">
                                        <i class="mdi mdi-delete me-1"></i> لابردنی داواکاری
                                    </button>
                                </div>
                            </form>
                        </div>
                        <!-- end settings content-->

                        <!-- NEW TABLE WITH COLORS AND METERS -->
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <table class="table table-bordered text-center">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>ئایتم</th>
                                                <th>رەنگەکان</th>
                                                <th>نرخی متر</th>
                                                <th>کۆی متر</th>
                                                <th>کۆی گشتی</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $sl = 1;
                                                $subTotal = 0;
                                            @endphp

                                            @foreach($orderItem as $item)
                                            @php
                                                $rowTotal = ($item->meters ?? $item->quantity) * $item->unitcost;
                                                $subTotal += $rowTotal;
                                            @endphp
                                            <tr>
                                                <td>{{ $sl++ }}</td>
                                                <td><strong>{{ optional($item->product)->product_name ?? 'Deleted Product' }}</strong></td>
                                                <td>
                                                    @if($item->selected_colors)
                                                        @php $colors = json_decode($item->selected_colors, true); @endphp
                                                        @foreach($colors as $color)
                                                            <span class="color-badge">
                                                                {{ $color['name'] }}: {{ $color['meter'] }}م
                                                            </span>
                                                        @endforeach
                                                    @else
                                                        <span class="text-muted">بێ رەنگ</span>
                                                    @endif
                                                </td>
                                                <td>{{ number_format($item->unitcost, 2) }}</td>
                                                <td>{{ number_format($item->meters ?? $item->quantity, 2) }}</td>
                                                <td>{{ number_format($rowTotal, 2) }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
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

<!-- CANCEL ORDER MODAL -->
<div class="modal fade" id="cancelOrderModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">لابردنی داواکاری - هەڵبژێرە ئایتمەکان</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('order.cancel') }}" id="cancelForm">
                @csrf
                <input type="hidden" name="order_id" value="{{ $order->id }}">
                
                <div class="modal-body">
                    <!-- ITEMS SELECTION -->
                    <h6 class="mb-3"><i class="mdi mdi-checkbox-marked me-1"></i> ئایتمەکان کە دەتەوێ لابیهێنیت:</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">هەڵبژێرە</th>
                                    <th>ئایتم</th>
                                    <th>رەنگەکان</th>
                                    <th>نرخ</th>
                                    <th>کۆی مترە</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($orderItem as $item)
                                <tr>
                                    <td>
                                        <input type="checkbox" name="rejected_items[]" value="{{ $item->id }}" class="form-check-input item-checkbox" data-item-cost="{{ ($item->meters ?? $item->quantity) * $item->unitcost }}">
                                    </td>
                                    <td><strong>{{ optional($item->product)->product_name ?? 'Deleted' }}</strong></td>
                                    <td>
                                        @if($item->selected_colors)
                                            @php $colors = json_decode($item->selected_colors, true); @endphp
                                            @foreach($colors as $color)
                                                <span class="color-badge">{{ $color['name'] }}: {{ $color['meter'] }}م</span>
                                            @endforeach
                                        @else
                                            <span class="text-muted">بێ رەنگ</span>
                                        @endif
                                    </td>
                                    <td>{{ number_format($item->unitcost, 2) }}</td>
                                    <td>{{ number_format($item->meters ?? $item->quantity, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <hr>

                    <!-- REFUND SECTION -->
                    <h6 class="mb-3"><i class="mdi mdi-cash-refund me-1"></i> پارە کەمکردن:</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">بڕی کەمکردن (خۆکارانە)</label>
                                <input type="number" id="autoRefundAmount" class="form-control" step="0.01" readonly>
                                <small class="text-muted">ئەم بڕە بە خۆکارانەوە حیساب دەکرێت لە پووچاندن</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">یان بە دەستی بنووسە:</label>
                                <input type="number" id="manualRefundAmount" name="refund_amount" class="form-control" step="0.01" placeholder="0.00">
                                <small class="text-muted">ئەگەر دەتەوێ جیاوازی برۆ بنووسە</small>
                            </div>
                        </div>
                    </div>

                    <!-- REFUND FROM SECTION -->
                    <div class="mb-3">
                        <label class="form-label"><strong>کەمکردن لە:</strong></label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="refund_from" value="due" id="refund_from_due" checked>
                            <label class="form-check-label" for="refund_from_due">
                                لە قەرزی کڕیار (Due): <strong>{{ number_format($order->due, 2) }}</strong>
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="refund_from" value="paid" id="refund_from_paid">
                            <label class="form-check-label" for="refund_from_paid">
                                بیگری کردی (Paid): <strong>{{ number_format($order->pay, 2) }}</strong>
                            </label>
                        </div>
                    </div>

                    <!-- REFUND SUMMARY -->
                    <div class="alert alert-info">
                        <p class="mb-1"><strong>خوێندکاری:</strong></p>
                        <p class="mb-0">ئایتمە پووچاندکان: <strong id="cancelItemCount">0</strong></p>
                        <p class="mb-0">کۆی کەمکردن: <strong id="cancelRefundAmount">0.00</strong></p>
                        <p class="mb-0">دوایی قەرز: <strong id="cancelNewDue">{{ number_format($order->due, 2) }}</strong></p>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">داخستن</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="mdi mdi-check me-1"></i> لابردنی داواکاری پشتڕاستکردن
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const itemCheckboxes = document.querySelectorAll('.item-checkbox');
    const autoRefundInput = document.getElementById('autoRefundAmount');
    const manualRefundInput = document.getElementById('manualRefundAmount');
    const refundFromRadios = document.querySelectorAll('input[name="refund_from"]');
    
    const currentDue = {{ $order->due }};
    const currentPaid = {{ $order->pay }};

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
            newDue = currentDue - refundAmount;
        } else if (refundFrom === 'paid') {
            newDue = currentDue + refundAmount;
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