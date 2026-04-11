@extends('admin_dashboard')
@section('admin')

<div class="content">
    <div class="container-fluid">

        <div class="row mb-4">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right">
                        <a href="{{ route('returned.create') }}" class="btn btn-primary">بەرگەڕاندنی نوێ</a>
                    </div>
                    <h4 class="page-title">بەرگەڕاندنی بەرهەمەکان</h4>
                </div>
            </div>
        </div>

        <!-- Filter -->
        <div class="row mb-3">
            <div class="col-12">
                <form method="GET" class="d-flex gap-2">
                    <select name="status" class="form-control" style="max-width: 200px;">
                        <option value="">هەموو دۆخەکان</option>
                        <option value="pending" {{ $status == 'pending' ? 'selected' : '' }}>چاوەروانی</option>
                        <option value="approved" {{ $status == 'approved' ? 'selected' : '' }}>پەسەند کراو</option>
                        <option value="rejected" {{ $status == 'rejected' ? 'selected' : '' }}>ڕێت کراو</option>
                    </select>
                    <button type="submit" class="btn btn-primary">گەڕان</button>
                    <a href="{{ route('returned.index') }}" class="btn btn-secondary">لابردن</a>
                </form>
            </div>
        </div>

        <!-- Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        @if($returns->count() > 0)
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>کڕیار</th>
                                    <th>پسوڵە</th>
                                    <th>بەرواری بەرگەڕاندن</th>
                                    <th>پاشگەزی</th>
                                    <th>دۆخ</th>
                                    <th>کردار</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $sl = 1; @endphp
                                @foreach($returns as $return)
                                <tr>
                                    <td>{{ $sl++ }}</td>
                                    <td>{{ $return->customer->name }}</td>
                                    <td>#{{ $return->order->invoice_no }}</td>
                                    <td>{{ $return->return_date }}</td>
                                    <td>${{ number_format($return->refund_amount, 2) }}</td>
                                    <td>
                                        <span class="badge 
                                            @if($return->status == 'pending') bg-warning
                                            @elseif($return->status == 'approved') bg-success
                                            @else bg-danger @endif">
                                            @if($return->status == 'pending') چاوەروانی
                                            @elseif($return->status == 'approved') پەسەند کراو
                                            @else ڕێت کراو @endif
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('returned.show', $return->id) }}" class="btn btn-sm btn-info">دیتان</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        {{ $returns->links() }}
                        @else
                        <p class="text-muted text-center">بەرگەڕاندنێک نیە</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection