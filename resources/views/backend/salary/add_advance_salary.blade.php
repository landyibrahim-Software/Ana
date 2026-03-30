@extends('admin_dashboard')
@section('admin')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>

 <div class="content">

                    <!-- Start Content-->
                    <div class="container-fluid">

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    <div class="page-title-right">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="javascript: void(0);">مووچەی پێشوەختە زیاد بکە</a></li>
                                            
                                        </ol>
                                    </div>
                                    <h4 class="page-title">مووچەی پێشوەختە زیاد بکە</h4>
                                </div>
                            </div>
                        </div>     
                        <!-- end page title -->

<div class="row">
    

  <div class="col-lg-8 col-xl-12">
<div class="card">
    <div class="card-body">
                                    
                                      
                                         
                                           

    <!-- end timeline content-->

    <div class="tab-pane" id="settings">
        <form method="post" action="{{ route('advance.salary.store') }}" >
        	@csrf

            <h5 class="mb-4 text-uppercase"><i class="mdi mdi-account-circle me-1"></i> مووچەی پێشوەختە زیاد بکە</h5>

            <div class="row">

 
 <div class="col-md-6">
        <div class="mb-3">
            <label for="firstname" class="form-label">ناوی کارمند    </label>
           <select name="employee_id" class="form-select @error('employee_id') is-invalid @enderror" id="example-select">
                    <option selected disabled >کارمند هەڵبژێرە </option>
                    @foreach($employee as $item)
                    <option value="{{  $item->id }}">{{  $item->name }}</option>
                    @endforeach
                </select> 
        </div>
    </div>


 <div class="col-md-6">
        <div class="mb-3">
            <label for="firstname" class="form-label">مانگی مووچە    </label>
           <select name="month" class="form-select @error('month') is-invalid @enderror" id="example-select">
                    <option selected disabled >مانگ هەڵبژێرە </option>
                    <option value="January">January</option>
                    <option value="February">February</option>
                    <option value="March">March</option>
                    <option value="April">April</option>
                    <option value="May">May</option>
                    <option value="Jun">Jun</option>
                    <option value="July">July</option>
                    <option value="August">August</option>
                    <option value="September">September</option>
                    <option value="October">October</option>
                    <option value="November">November</option>
                    <option value="December">December</option> 
                </select>
                 @error('month')
      <span class="text-danger"> {{ $message }} </span>
            @enderror
         
        </div>
    </div>


      <div class="col-md-6">
        <div class="mb-3">
            <label for="firstname" class="form-label">ساڵی مووچە    </label>
           <select name="year" class="form-select @error('year') is-invalid @enderror" id="example-select">
                    <option selected disabled >سال هەڵبژێرە </option>
                    <option value="2022">2026</option>
                    <option value="2023">2027</option>
                    <option value="2024">2028</option>
                    <option value="2025">2029</option>
                    <option value="2026">2030</option>
                </select>
                 @error('year')
      <span class="text-danger"> {{ $message }} </span>
            @enderror
         
        </div>
    </div>


 <div class="col-md-6">
        <div class="mb-3">
            <label for="firstname" class="form-label">مووچەی پێشوەختە    </label>
            <input type="text" name="advance_salary" class="form-control @error('advance_salary') is-invalid @enderror"   >
             @error('advance_salary')
      <span class="text-danger"> {{ $message }} </span>
            @enderror
        </div>
    </div>
 


            </div> <!-- end row -->
 
        
            
            <div class="text-end">
                <button type="submit" class="btn btn-success waves-effect waves-light mt-2"><i class="mdi mdi-content-save"></i> تۆمارکردن</button>
            </div>
        </form>
    </div>
    <!-- end settings content-->
    
                                       
                                    </div>
                                </div> <!-- end card-->

                            </div> <!-- end col -->
                        </div>
                        <!-- end row-->

                    </div> <!-- container -->

                </div> <!-- content -->

 


@endsection