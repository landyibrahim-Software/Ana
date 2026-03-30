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

   <a href="{{ route('import.product') }}" class="btn btn-info rounded-pill waves-effect waves-light">هاوردەکردن </a>  
   &nbsp;&nbsp;&nbsp;
   <a href="{{ route('export') }}" class="btn btn-danger rounded-pill waves-effect waves-light">هەناردە </a>  
   &nbsp;&nbsp;&nbsp;

      <a href="{{ route('add.product') }}" class="btn btn-primary rounded-pill waves-effect waves-light">زیادکردنی بەرهەم </a>  
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
                     
                    
                    <table id="basic-datatable" class="table dt-responsive nowrap w-100">
                        <thead>
                            <tr>
                                <th>Sl</th>
                                <th>وێنە</th>
                                <th>ناو</th>
                                <th>جۆر</th>
                                <th>دابینکەر</th>
                                <th>کۆد</th>
                                <th>عدد</th> 
                            </tr>
                        </thead>
                    
    
        <tbody>
        	@foreach($product as $key=> $item)
            <tr>
                <td>{{ $key+1 }}</td>
                <td> <img src="{{ asset($item->product_image) }}" style="width:50px; height: 40px;"> </td>
                <td>{{ $item->product_name }}</td>  
                <td>{{ optional($item->category)->category_name ?? 'N/A' }}</td>
                <td>{{ optional($item->supplier)->name ?? 'N/A' }}</td> 
                <td>{{ $item->product_code }}</td>
                <td> <button class="btn btn-warning waves-effect waves-light">{{ $item->product_store }}</button> </td>
      
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