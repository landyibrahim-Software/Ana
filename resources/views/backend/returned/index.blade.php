@extends('admin_dashboard')
@section('admin')

<style>
    .status-badge {
        padding: 5px 10px;
        border-radius: 5px;
        font-size: 0.85rem;
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

    .card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
</style>

<div class="content">
    <div class="container-fluid">

        <!-- PAGE TITLE -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right">
                        <a href="{{ route('returned.create') }}" class="btn btn-primary">
                            <i class="mdi mdi-plus me-1"></i> بەرگەڕاندنی نوێ
                        </a>
                    </div>
                    <h4 class="page-title">
                        <i class="mdi mdi-undo me-2"></i> بەرگەڕاندنی بەرهەمەکان
                    </h4>
                </div>
            </div>
        </div>

        <!-- FILTER -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form method="GET" action="{{ route('returned.index') }}" class="d-flex gap-2">
                            <select name="status" class="form-control" style="max-width: 250px;">
                                <option value="">هەموو دۆخەکان</option>
                                <option value="pending" {{ $status == 'pending' ? 'selected' : '' }}>چاوەروانی</option>
                                <option value="approved" {{ $status == 'approved' ? 'selected' : '' }}>پەسەند کراو</option>
                                <option value="rejected" {{ $status == 'rejected' ? 'selected' : '' }}>ڕێت کراو</option>
                            </select>
                            <button type="submit" class="btn btn-primary">
                                <i class="mdi mdi-magnify me-1"></i> گەڕان
                            </button>
                            <a href="{{ route('returned.index') }}" class="btn btn-secondary">
                                <i class="mdi mdi-refresh me-1"></i> لابردن
                            </a>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- RETURNS TABLE -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">لیستی بەرگەڕاندنەکان</h5>
                    </div>
                    <div class="card-body">
                        @if($returns->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>کڕیار</th>
                                        <th>ژمارەی پسوڵە</th>
                                        <th>بەرواری بەرگەڕاندن</th>
                                        <th>هۆی بەرگەڕاندن</th>
                                        <th>بڕی پاشگەزی</th>
                                        <th>دۆخ</th>
                                        <th>کردارەکان</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $sl = 1; @endphp
                                    @foreach($returns as $return)
                                    <tr>
                                        <td>{{ $sl++ }}</td>
                                        <td>
                                            <strong>{{ $return->customer->name ?? 'نەناسراو' }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark">{{ $return->order->invoice_no ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            {{ \Carbon\Carbon::parse($return->return_date)->format('d-m-Y') }}
                                        </td>
                                        <td>
                                            <small>{{ substr($return->return_reason, 0, 30) }}...</small>
                                        </td>
                                        <td>
                                            <strong class="text-danger">${{ number_format($return->refund_amount, 2) }}</strong>
                                        </td>
                                        <td>
                                            <span class="status-badge status-{{ $return->status }}">
                                                @if($return->status == 'pending')
                                                    چاوەروانی
                                                @elseif($return->status == 'approved')
                                                    پەسەند کراو
                                                @else
                                                    ڕێت کراو
                                                @endif
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('returned.show', $return->id) }}" class="btn btn-sm btn-info">
                                                <i class="mdi mdi-eye"></i> ووردەکاری
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- PAGINATION -->
                        <div class="mt-3">
                            {{ $returns->links() }}
                        </div>
                        @else
                        <div class="alert alert-info text-center">
                            <i class="mdi mdi-information-outline me-2"></i>
                            هیچ بەرگەڕاندنێک نیە
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection