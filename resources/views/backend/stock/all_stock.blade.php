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
                            <li class="breadcrumb-item">
                                <a href="{{ route('import.product') }}" class="btn btn-info btn-sm rounded-pill">
                                    <i class="mdi mdi-download me-1"></i> هاوردەکردن
                                </a>  
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('export') }}" class="btn btn-danger btn-sm rounded-pill">
                                    <i class="mdi mdi-upload me-1"></i> هەناردە
                                </a>  
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('add.product') }}" class="btn btn-primary btn-sm rounded-pill">
                                    <i class="mdi mdi-plus me-1"></i> زیادکردنی بەرهەم
                                </a>  
                            </li>
                        </ol>
                    </div>
                    <h4 class="page-title">هەموو بەرهەمەکان</h4>
                </div>
            </div>
        </div>     
        <!-- end page title --> 

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        
                        <!-- ✅ SEARCH & FILTER SECTION -->
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <form method="GET" action="{{ route('stock.manage') }}" class="form-inline">
                                    <div class="input-group w-100">
                                        <input type="text" 
                                               name="search" 
                                               class="form-control" 
                                               placeholder="جوری بەرهەم یان کۆد..."
                                               value="{{ request('search') }}">
                                        <button class="btn btn-primary" type="submit">
                                            <i class="mdi mdi-magnify"></i> گەڕان
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- ✅ PRODUCTS TABLE -->
                        <table id="basic-datatable" class="table dt-responsive nowrap w-100">
                            <thead>
                                <tr>
                                    <th>ژمارە</th>
                                    <th>وێنە</th>
                                    <th>ناوی بەرهەم</th>
                                    <th>جۆر</th>
                                    <th>دابینکەر</th>
                                    <th>کۆدی بەرهەم</th>
                                    <th>کۆگا</th>
                                    <th>دەرزەن</th>
                                    <th>نرخی کڕین</th>
                                    <th>نرخی فرۆشتن</th>
                                    <th>کردار</th>
                                </tr>
                            </thead>
                      
                            <tbody>
                                @forelse($product as $key => $item)
                                    <tr>
                                        <td>{{ ($product->currentPage() - 1) * $product->perPage() + $key + 1 }}</td>
                                        <td>
                                            @if($item->product_image && file_exists(public_path($item->product_image)))
                                                <img src="{{ asset($item->product_image) }}" 
                                                     style="width:50px; height: 40px; border-radius: 4px;"
                                                     alt="Product Image">
                                            @else
                                                <img src="https://via.placeholder.com/50?text=No+Image" 
                                                     style="width:50px; height: 40px; border-radius: 4px;"
                                                     alt="No Image">
                                            @endif
                                        </td>
                                        <td>
                                            <strong>{{ $item->product_name }}</strong>
                                        </td>
                                        <td>
                                            @if($item->category)
                                                <span class="badge bg-secondary">{{ $item->category->category_name }}</span>
                                            @else
                                                <span class="badge bg-light">نەناسراو</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($item->supplier)
                                                {{ $item->supplier->name }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <code>{{ $item->product_code ?? '-' }}</code>
                                        </td>
                                        <td>
                                            {{ $item->product_garage ?? '-' }}
                                        </td>
                                        <td>
                                            @if($item->product_store > 20)
                                                <button class="btn btn-success btn-sm" disabled>{{ $item->product_store }}</button>
                                            @elseif($item->product_store > 5)
                                                <button class="btn btn-warning btn-sm" disabled>{{ $item->product_store }}</button>
                                            @else
                                                <button class="btn btn-danger btn-sm" disabled>{{ $item->product_store }}</button>
                                            @endif
                                        </td>
                                        <td>
                                            <strong>${{ number_format($item->buying_price, 2) }}</strong>
                                        </td>
                                        <td>
                                            <strong>${{ number_format($item->selling_price, 2) }}</strong>
                                        </td>
                                        <td>
                                            <a href="{{ route('edit.product', $item->id) }}" 
                                               class="btn btn-primary btn-sm rounded-pill" 
                                               title="دروست کردن">
                                                <i class="mdi mdi-pencil"></i>
                                            </a>
                                            <a href="{{ route('barcode.product', $item->id) }}" 
                                               class="btn btn-info btn-sm rounded-pill" 
                                               title="بارکۆد">
                                                <i class="mdi mdi-barcode"></i>
                                            </a>
                                            <a href="{{ route('delete.product', $item->id) }}" 
                                               class="btn btn-danger btn-sm rounded-pill" 
                                               id="delete" 
                                               title="سڕینەوە">
                                                <i class="mdi mdi-trash-can"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="11" class="text-center text-muted py-4">
                                            <i class="mdi mdi-inbox" style="font-size: 2rem; opacity: 0.5;"></i>
                                            <p class="mt-2">بەرهەم نەدۆزرایەوە</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                        <!-- ✅ PAGINATION LINKS -->
                        @if($product->count() > 0)
                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <small class="text-muted">
                                        <i class="mdi mdi-information"></i>
                                        نیشاندان {{ $product->firstItem() }} تا {{ $product->lastItem() }} 
                                        (ژمارە کۆ: {{ $product->total() }})
                                    </small>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="d-flex justify-content-end">
                                        {{ $product->links('pagination::bootstrap-4') }}
                                    </div>
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