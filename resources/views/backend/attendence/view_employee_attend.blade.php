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
      <a href="{{ route('add.employee.attend') }}" class="btn btn-primary rounded-pill waves-effect waves-light">زیادکردنی ئامادەبوونی کارمەند </a>  
                                        </ol>
                                    </div>
                                    <h4 class="page-title">ئامادەبوونی سەرجەم کارمەندان</h4>
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
                                <th>بەروار</th>
                                <th>کردار</th>
                            </tr>
                        </thead>
                    
    
        <tbody>
        	@foreach($allData as $key=> $item)
            <tr>
                <td>{{ $key+1 }}</td> 
                <td>{{ date('Y-m-d', strtotime($item->date))  }}</td>
                <td>
<a href="{{ route('employee.attend.edit',$item->date) }}" class="btn btn-blue rounded-pill waves-effect waves-light">دەستکاریکردن</a>
<a href="{{ route('employee.attend.view',$item->date) }}" class="btn btn-danger rounded-pill waves-effect waves-light" >سەیرکردن</a>

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