@extends('admin_dashboard')
@section('admin')

<div class="content">
    <div class="container-fluid">
        
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <a href="{{ route('add.code') }}" class="btn btn-primary">زیادکردنی کۆد</a>
                        </ol>
                    </div>
                    <h4 class="page-title">هەموو کۆدەکان</h4>
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
                                    <th>#</th>
                                    <th>ناوی کۆد</th>
                                    <th>بەش</th>
                                    <th>کردار</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($code as $key => $item)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $item->code_name }}</td>
                                    <td>{{ $item->category->category_name ?? 'N/A' }}</td>
                                    <td>
                                        <a href="{{ route('edit.code', $item->id) }}" class="btn btn-sm btn-blue">دەستکاریکردن</a>
                                        <a href="{{ route('delete.code', $item->id) }}" class="btn btn-sm btn-danger" onclick="return confirm('دڵنیایت؟')">سڕینەوە</a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center">هیچ کۆدێک نیە</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection