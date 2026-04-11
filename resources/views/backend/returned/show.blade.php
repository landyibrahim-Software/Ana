@extends('admin_dashboard')
@section('admin')

<div class="content">
    <div class="container-fluid">

        <div class="row mb-4">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right">
                        <a href="{{ route('returned.index') }}" class="btn btn-secondary">گەڕاوە</a>
                    </div>
                    <h4 class="page-title">ووردەکاری بەرگەڕاندن</h4>
                </div>
            </div>
        </div>

        <!-- Info -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>کڕیار:</strong> {{ $return->customer->name }}</p>
                                <p><strong>پسوڵە:</strong> #{{ $return->order->invoice_no }}</p>
                                <p><strong>بەرواری:</strong> {{ $return->return_date }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>دۆخ:</strong> 
                                    <span class="badge @if($return->status == 'pending') bg-warning @elseif($return->status == 'approved') bg-success @else bg-danger @endif">
                                        @if($return->status == 'pending') چاوەروانی
                                        @elseif($return->status == 'approved') پەسەند کراو
                                        @else ڕێت کراو @endif
                                    </span>
                                </p>
                                <p><strong>پاشگەزی:</strong> ${{ number_format($return->refund_amount, 2) }}</p>
                            </div>
                        </div>
                        <p><strong>هۆی:</strong></p>
                        <p>{{ $return->return_reason }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Items -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5>بەرهەمەکان</h5>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>بەرهەم</th>
                                    <th>دەرزەن</th>
                                    <th>متر</th>
                                    <th>نرخ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($return->returnedItems as $item)
                                <tr>
                                    <td>{{ $item->product->product_name }}</td>
                                    <td>{{ $item->quantity_returned }}</td>
                                    <td>{{ number_format($item->meters_returned, 2) }}</td>
                                    <td>${{ number_format($item->refund_price, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        @if($return->status == 'pending')
        <div class="row">
            <div class="col-12">
                <form method="POST" style="display: inline;">
                    @csrf
                    <button formaction="{{ route('returned.approve', $return->id) }}" class="btn btn-success">
                        ✓ پەسەند بکە (پاشگەزی + ئێنوێنتوری)
                    </button>
                    <button formaction="{{ route('returned.reject', $return->id) }}" class="btn btn-danger">
                        ✕ ڕێت بکە
                    </button>
                </form>
            </div>
        </div>
        @endif

    </div>
</div>

@endsection