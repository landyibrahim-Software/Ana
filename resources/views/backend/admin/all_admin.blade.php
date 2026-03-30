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
      <a href="{{ route('add.admin') }}" class="btn btn-primary rounded-pill waves-effect waves-light"> زیادکردنی ئادمین </a>  
                                        </ol>
                                    </div>
                                    <h4 class="page-title">هەموو ئادمینەکان <span class="btn btn-danger">{{ count($alladminuser) }}</span> </h4>
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
                                <th>وێنە</th>
                                <th>ناو</th>
                                <th>ئیمەیڵ</th>
                                <th>ژمارەی تەلەفۆن</th> 
                                <th>ڕۆڵ</th> 
                                <th>کردار</th>
                            </tr>
                        </thead>
                    
    
        <tbody>
        	@foreach($alladminuser as $key=> $item)
            <tr>
                <td>{{ $key+1 }}</td>
                <td> <img src="{{ (!empty($item->photo)) ? url('upload/admin_image/'.$item->photo) : url('upload/no_image.jpg') }}" style="width:50px; height: 40px;"> </td>
                <td>{{ $item->name }}</td>
                <td>{{ $item->email }}</td>
                <td>{{ $item->phone }}</td> 
                <td> 
                @foreach($item->roles as $role)
<span class="badge badge-pill bg-danger"> {{ $role->name }} </span>
                @endforeach
                 </td> 
                <td>
<a href="{{ route('edit.admin',$item->id) }}" class="btn btn-blue rounded-pill waves-effect waves-light">دەستکاریکردن</a>
<a href="{{ route('delete.admin',$item->id) }}" class="btn btn-danger rounded-pill waves-effect waves-light" id="delete">سڕینەوە</a>

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


@endsection 