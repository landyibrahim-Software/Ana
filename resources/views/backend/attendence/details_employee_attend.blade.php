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
                                <th>ڕەسم</th>
                                <th>ناوی کارمەند</th>
                                <th>بەروار</th>
                                <th>دۆخی ئامادەبون</th> 
                            </tr>
                        </thead>
                    
    
        <tbody>
        	@foreach($details as $key=> $item)
            <tr>
                <td>{{ $key+1 }}</td>
                <td> <img src="{{ asset($item->employee->image) }}" style="width:50px; height: 40px;"> </td>
                <td>{{ $item['employee']['name'] }}</td>
                <td>{{ date('Y-m-d',strtotime($item->date))  }}</td>
                <td>{{  $item->attend_status }}</td> 
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