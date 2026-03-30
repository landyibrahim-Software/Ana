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
   <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#signup-modal">زیادکردنی بەش</button> 
                                        </ol>
                                    </div>
                                    <h4 class="page-title">هەموو بەشەکان</h4>
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
                                <th>Sl</th> 
                                <th>ناوی بەش </th>
                                <th>کردار</th>
                            </tr>
                        </thead>
                    
    
        <tbody>
        	@foreach($category as $key=> $item)
            <tr>
                <td>{{ $key+1 }}</td> 
                <td>{{ $item->category_name }}</td> 
                <td>
<a href="{{ route('edit.category',$item->id) }}" class="btn btn-blue rounded-pill waves-effect waves-light">دەستکاریکردن</a>
<a href="{{ route('delete.category',$item->id) }}" class="btn btn-danger rounded-pill waves-effect waves-light" id="delete">سڕینەوە</a>

                </td>
            </tr>
            @endforeach
        </tbody>
                    </table>

                </div> <!-- end card body-->
            </div> <!-- end card -->
        </div><!-- end col-->
    </div>
    <!-- end row-->


                      
                        
                    </div> <!-- container -->

                </div> <!-- content -->



        <!-- Signup modal content -->
<div id="signup-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-body"> 

  <form class="px-3" method="post" action="{{ route('category.store') }}">
                    @csrf

                    <div class="mb-3">
             <label for="username" class="form-label">ناوی بەش</label>
     <input class="form-control" type="text" name="category_name" placeholder="زیادکردنی بەش">
                    </div>
 

                    <div class="mb-3 text-center">
                        <button class="btn btn-primary" type="submit">تۆمارکردن</button>
                    </div>

                </form>

            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->


@endsection 