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
                                            <li class="breadcrumb-item"><a href="javascript: void(0);">دەستکاریکردنی کڕیار</a></li>
                                            
                                        </ol>
                                    </div>
                                    <h4 class="page-title">دەستکاریکردنی کڕیار</h4>
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
        <form method="post" action="{{ route('customer.update') }}" enctype="multipart/form-data">
        	@csrf

            <input type="hidden" name="id" value="{{ $customer->id }}">

            <h5 class="mb-4 text-uppercase"><i class="mdi mdi-account-circle me-1"></i> دەستکاریکردنی کڕیار</h5>

            <div class="row">


    <div class="col-md-6">
        <div class="mb-3">
            <label for="firstname" class="form-label">ناوی کڕیار</label>
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ $customer->name }}"  >
             @error('name')
      <span class="text-danger"> {{ $message }} </span>
            @enderror
        </div>
    </div>


            




              <div class="col-md-6">
        <div class="mb-3">
            <label for="firstname" class="form-label">ژمارە مۆبایلی کڕیار    </label>
            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"  value="{{ $customer->phone }}"   >
             @error('phone')
      <span class="text-danger"> {{ $message }} </span>
            @enderror
        </div>
    </div>


      <div class="col-md-6">
        <div class="mb-3">
            <label for="firstname" class="form-label">ناونیشانی کڕیار    </label>
            <input type="text" name="address" class="form-control @error('address') is-invalid @enderror"  value="{{ $customer->address }}"   >
             @error('address')
      <span class="text-danger"> {{ $message }} </span>
            @enderror
        </div>
    </div>



      <div class="col-md-6">
        <div class="mb-3">
            <label for="firstname" class="form-label">ناوی فرۆشگای کڕیار    </label>
            <input type="text" name="shopname" class="form-control @error('shopname') is-invalid @enderror"  value="{{ $customer->shopname }}"   >
             @error('shopname')
      <span class="text-danger"> {{ $message }} </span>
            @enderror
        </div>
    </div>



     <div class="col-md-6">
        <div class="mb-3">
            <label for="firstname" class="form-label">شاری کڕیار    </label>
            <input type="text" name="city" class="form-control @error('city') is-invalid @enderror" value="{{ $customer->city }}"    >
             @error('city')
      <span class="text-danger"> {{ $message }} </span>
            @enderror
        </div>
    </div>
<div class="col-md-6">
    <div class="mb-3">
       <label for="due" class="form-label">قەرزی پێشوتر (دۆلار)</label>
<input type="text" id="due" name="due" class="form-control @error('due') is-invalid @enderror" value="{{ $customer->previous_due ?? 0 }}" pattern="[0-9]*\.?[0-9]*" placeholder="0.00">
        @error('due')
        <span class="text-danger">{{ $message }}</span>
        @enderror
    </div>
</div>



   <div class="col-md-12">
<div class="mb-3">
        <label for="example-fileinput" class="form-label">وێنەی کڕیار</label>
        <input type="file" name="image" id="image" class="form-control @error('image') is-invalid @enderror">
          @error('image')
      <span class="text-danger"> {{ $message }} </span>
            @enderror
    </div>
 </div> <!-- end col -->


   <div class="col-md-12">
<div class="mb-3">
        <label for="example-fileinput" class="form-label"> </label>
        <img id="showImage" src="{{ asset($customer->image) }}" class="rounded-circle avatar-lg img-thumbnail"
                alt="profile-image">
    </div>
 </div> <!-- end col -->



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



<script type="text/javascript">
	
	$(document).ready(function(){
		$('#image').change(function(e){
			var reader = new FileReader();
			reader.onload =  function(e){
				$('#showImage').attr('src',e.target.result);
			}
			reader.readAsDataURL(e.target.files['0']);
		});
	});

</script>







@endsection