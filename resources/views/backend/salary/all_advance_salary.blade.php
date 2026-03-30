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
      <a href="{{ route('add.advance.salary') }}" class="btn btn-primary rounded-pill waves-effect waves-light">مووچەی پێشوەختە زیاد بکە </a>  
                                        </ol>
                                    </div>
                                    <h4 class="page-title">هەموو موچە زیادکراوەکان</h4>
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
                                <th>مانگ</th>
                                <th>مووچە</th>
                                <th>مووچەی پێشوەختە</th>
                                <th>کردار</th>
                            </tr>
                        </thead>
                    
    
        <tbody>
        	@foreach($salary as $key=> $item)
            <tr>
                <td>{{ $key+1 }}</td>
                
                <td> <img src="{{ asset(optional($item->employee)->image) }}" style="width:50px; height: 40px;"> </td>
                <td>{{ optional($item->employee)->name ?? 'N/A' }}</td>

                <td>{{ $item->month }}</td>
                <td>{{ optional($item->employee)->salary }}</td>
                <td>

                    @if($item->advance_salary == NULL )
                        <p>No Advance</p>
                    @else
                     {{ $item->advance_salary }}
                    @endif

                  </td>
                <td>
<a href="{{ route('edit.advance.salary',$item->id) }}" class="btn btn-blue rounded-pill waves-effect waves-light">دەستکاری</a>
<a href="{{ route('delete.employee',$item->id) }}" class="btn btn-danger rounded-pill waves-effect waves-light" id="delete">سڕینەوە</a>

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