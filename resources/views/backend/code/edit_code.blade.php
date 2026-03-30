@extends('admin_dashboard')
@section('admin')

<div class="content">
    <div class="container-fluid">
        
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <h4 class="page-title">دەستکاریکردنی کۆد</h4>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="{{ route('code.update') }}">
                            @csrf
                            <input type="hidden" name="id" value="{{ $code->id }}">
                            
                            <div class="mb-3">
                                <label for="code_name" class="form-label">ناوی کۆد <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('code_name') is-invalid @enderror" 
                                       id="code_name" name="code_name" placeholder="ناوی کۆد" value="{{ $code->code_name }}" required>
                                @error('code_name')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="category_id" class="form-label">بەش <span class="text-danger">*</span></label>
                                <select class="form-control @error('category_id') is-invalid @enderror" 
                                        id="category_id" name="category_id" required>
                                    <option value="">بەشێک هەڵبژێرە</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ $code->category_id == $cat->id ? 'selected' : '' }}>
                                            {{ $cat->category_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <button type="submit" class="btn btn-primary">نوێکردن</button>
                                <a href="{{ route('all.code') }}" class="btn btn-secondary">بگەڕێ</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection