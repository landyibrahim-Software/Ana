@extends('admin_dashboard')
@section('admin')

<div class="content">
    <div class="container-fluid">

        <!-- Page Title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex justify-content-between align-items-center">
                    <h4 class="page-title">All Database Backups</h4>

                    <a href="{{ route('backup.now') }}"
                       class="btn btn-primary rounded-pill">
                        Backup Now
                    </a>
                </div>
            </div>
        </div>

        <!-- Backup Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">

                        {{-- Notifications --}}
                        @if(session('message'))
                            <div class="alert alert-{{ session('alert-type','success') }}">
                                {{ session('message') }}
                            </div>
                        @endif

                        <table class="table table-bordered table-striped w-100">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>File Name</th>
                                    <th>Size</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($files as $key => $file)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>

                                        <td>
                                            {{ $file->getFilename() }}
                                        </td>

                                        <td>
                                            {{ number_format($file->getSize() / 1024 / 1024, 2) }} MB
                                        </td>

                                        <td>
                                            {{-- DOWNLOAD --}}
                                            <a href="{{ route('backup.download', $file->getRelativePathname()) }}"
                                               class="btn btn-info btn-sm rounded-pill">
                                                داگرتن
                                            </a>

                                            {{-- DELETE --}}
                                            <a href="{{ route('backup.delete', $file->getRelativePathname()) }}"
                                               class="btn btn-danger btn-sm rounded-pill"
                                               onclick="return confirm('دڵنیای لە سڕینەوە؟')">
                                                سڕینەوە
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">
                                            No backups found
                                        </td>
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