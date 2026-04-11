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
                                </div> <!-- end card body-->
                            </div> <!-- end card -->
                        </div><!-- end col-->
                    </div>
                </div> <!-- end card-->
            </div> <!-- end col -->
        </div>
        <!-- end row-->
    </div> <!-- container -->
</div> <!-- content -->

@endsection