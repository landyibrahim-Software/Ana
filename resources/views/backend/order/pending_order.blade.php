@extends('admin_dashboard')
@section('admin')

<div class="content">

    <!-- Start Content-->
    <div class="container-fluid">
        
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                        </ol>
                    </div>
                    <h4 class="page-title">فەرمانە چاوەڕوانکراوەکان</h4>
                </div>
            </div>
        </div>     
        <!-- end page title --> 

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                         
                        <table id="basic-datatable" class="table dt-responsive nowrap w-100">
                            <thead>
                                <tr>
                                    <th>ژمارە</th>
                                    <th>وێنە</th>
                                    <th>ناو</th>
                                    <th>بەرواری ئۆردەر</th>
                                    <th>شێوازی پارەدان</th>
                                    <th>پسوڵە</th>
                                    <th>پارەی دراو</th>
                                    <th>دۆخ</th>
                                    <th>کردار</th>
                                </tr>
                            </thead>
                      
                            <tbody>
                                @forelse($orders as $key => $item)
                                    <tr>
                                        <td>{{ ($orders->currentPage() - 1) * $orders->perPage() + $key + 1 }}</td>
                                        <td>
                                            @if($item->customer && $item->customer->image)
                                                <img src="{{ asset($item->customer->image) }}" 
                                                     style="width:50px; height: 40px; border-radius: 4px;"
                                                     alt="Customer Image">
                                            @else
                                                <img src="https://via.placeholder.com/50?text=No+Image" 
                                                     style="width:50px; height: 40px; border-radius: 4px;"
                                                     alt="No Image">
                                            @endif
                                        </td>
                                        <td>
                                            <strong>{{ $item->customer->name ?? 'نەناسراو' }}</strong>
                                        </td>
                                        <td>
                                            {{ \Carbon\Carbon::parse($item->order_date)->format('Y-m-d') }}
                                        </td>
                                        <td>
                                            @if($item->payment_status == 'HandCash')
                                                <span class="badge bg-info">دەستی</span>
                                            @elseif($item->payment_status == 'Cheque')
                                                <span class="badge bg-warning">چەک</span>
                                            @elseif($item->payment_status == 'Bank')
                                                <span class="badge bg-success">بانک</span>
                                            @else
                                                <span class="badge bg-secondary">{{ $item->payment_status }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <strong>#{{ $item->id }}</strong>
                                        </td>
                                        <td>
                                            <strong class="text-success">${{ number_format($item->pay, 2) }}</strong>
                                        </td>
                                        <td>
                                            @if($item->order_status == 'pending')
                                                <span class="badge bg-danger">چاوەروانی</span>
                                            @elseif($item->order_status == 'complete')
                                                <span class="badge bg-success">تەواو</span>
                                            @else
                                                <span class="badge bg-secondary">{{ $item->order_status }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('order.details', $item->id) }}" 
                                               class="btn btn-primary btn-sm rounded-pill"
                                               title="وردەکاری ئۆردەر">
                                                <i class="mdi mdi-eye me-1"></i> وردەکاری
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-4">
                                            <i class="mdi mdi-inbox" style="font-size: 2rem; opacity: 0.5;"></i>
                                            <p class="mt-2">فەرمانی چاوەڕوانکراو نیە</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                        <!-- ✅ PAGINATION LINKS -->
                        @if($orders->count() > 0)
                            <div class="d-flex justify-content-between align-items-center mt-4">
                                <div>
                                    <small class="text-muted">
                                        نیشاندان {{ $orders->firstItem() }} تا {{ $orders->lastItem() }} 
                                        (ژمارە کۆ: {{ $orders->total() }})
                                    </small>
                                </div>
                                
                                <div class="d-flex justify-content-center">
                                    {{ $orders->links('pagination::bootstrap-4') }}
                                </div>
                            </div>
                        @endif

                    </div> <!-- end card body-->
                </div> <!-- end card -->
            </div><!-- end col-->
        </div>
        <!-- end row-->

    </div> <!-- container -->

</div> <!-- content -->

@endsection