@extends('admin_dashboard')
@section('admin')

<style>
    .status-badge {
        padding: 8px 12px;
        border-radius: 5px;
        font-size: 0.95rem;
        font-weight: 600;
    }

    .status-pending {
        background: #ffc107;
        color: #000;
    }

    .status-approved {
        background: #28a745;
        color: white;
    }

    .status-rejected {
        background: #dc3545;
        color: white;
    }

    .detail-card {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 15px;
    }

    .detail-card label {
        font-weight: 600;
        color: #555;
        margin-bottom: 5px;
    }

    .detail-card p {
        margin: 0;
        color: #333;
    }
</style>

<div class="content">
    <div class="container-fluid">

        <!-- PAGE TITLE -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right">
                        <a href="{{ route('returned.index') }}" class="btn btn-secondary">
                            <i class="mdi mdi-arrow-left me-1"></i> گەڕاوە
                        </a>
                    </div>
                    <h4 class="page-title">
                        <i class="mdi mdi-undo me-2"></i> ووردەکاری بەرگەڕاندن
                    </h4>
                </div>
            </div>
        </div>

        <!-- RETURN INFO -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="detail-card">
                                    <label>کڕیار</label>
                                    <p>{{ $return->customer->name ?? 'نەناسراو' }}</p>
                                </div>
                                <div class="detail-card">
                                    <label>ژمارەی پسوڵە</label>
                                    <p>#{{ $return->order->invoice_no ?? 'N/A' }}</p>
                                </div>
                                <div class="detail-card">
                                    <label>بەرواری بەرگەڕاندن</label>
                                    <p>{{ \Carbon\Carbon::parse($return->return_date)->format('d-m-Y') }}</p>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="detail-card">
                                    <label>دۆخ</label>
                                    <p>
                                        <span class="status-badge status-{{ $return->status }}">
                                            @if($return->status == 'pending')
                                                چاوەروانی
                                            @elseif($return->status == 'approved')
                                                پەسەند کراو
                                            @else
                                                ڕێت کراو
                                            @endif
                                        </span>
                                    </p>
                                </div>
                                <div class="detail-card">
                                    <label>بڕی پاشگەزی</label>
                                    <p><strong class="text-danger">${{ number_format($return->refund_amount, 2) }}</strong></p>
                                </div>
                            </div>
                        </div>

                        <div class="detail-card">
                            <label>هۆی بەرگەڕاندن</label>
                            <p>{{ $return->return_reason }}</p>
                        </div>

                        @if($return->notes)
                        <div class="detail-card">
                            <label>یادداشت</label>
                            <p>{{ $return->notes }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- RETURNED ITEMS -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="mb-3">بەره��مە بەرگەڕاندووەکان</h5>
                        @if($return->returnedItems->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>بەرهەم</th>
                                        <th>دەرزەن</th>
                                        <th>متر</th>
                                        <th>نرخی پاشگەزی</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($return->returnedItems as $item)
                                    <tr>
                                        <td>
                                            <strong>{{ $item->product->product_name ?? 'حذفراو' }}</strong>
                                        </td>
                                        <td>{{ $item->quantity_returned }}</td>
                                        <td>{{ number_format($item->meters_returned ?? 0, 2) }}</td>
                                        <td>${{ number_format($item->refund_price, 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <p class="text-muted">هیچ بەرهەمێک نیە</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- ACTIONS -->
        @if($return->status == 'pending')
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="mb-3">کردارەکان</h5>
                        <form method="POST" style="display: inline;">
                            @csrf
                            <button formaction="{{ route('returned.approve', $return->id) }}" class="btn btn-success">
                                <i class="mdi mdi-check me-1"></i> پەسەند بکە
                            </button>
                            <button formaction="{{ route('returned.reject', $return->id) }}" class="btn btn-danger">
                                <i class="mdi mdi-close me-1"></i> ڕێت بکە
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @endif

    </div>
</div>

@endsection