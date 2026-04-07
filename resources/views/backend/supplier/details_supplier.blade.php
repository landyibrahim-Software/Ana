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
                                            <li class="breadcrumb-item"><a href="javascript: void(0);">زانیاری دابینکەر</a></li>
                                            
                                        </ol>
                                    </div>
                                    <h4 class="page-title">زانیاری دابینکەر</h4>
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
        <form method="post" action="{{ route('supplier.update') }}" enctype="multipart/form-data">
        	@csrf

           <input type="hidden" name="id" value="{{ $supplier->id }}"> 

            <h5 class="mb-4 text-uppercase"><i class="mdi mdi-account-circle me-1"></i> زانیاری دابینکەر</h5>

            <div class="row">


    <div class="col-md-6">
        <div class="mb-3">
            <label for="firstname" class="form-label">ناوی دابینکەر</label>
            <p class="text-danger">{{ $supplier->name }}</p>
        </div>
    </div>


              <div class="col-md-6">
        <div class="mb-3">
            <label for="firstname" class="form-label">ئیمەیڵی دابینکەر</label>
               <p class="text-danger">{{ $supplier->email }}</p>
            
        </div>
    </div>




              <div class="col-md-6">
        <div class="mb-3">
            <label for="firstname" class="form-label">ژمارەی مۆبایلی دابینکەر    </label>
             <p class="text-danger">{{ $supplier->phone }}</p>
        </div>
    </div>


      <div class="col-md-6">
        <div class="mb-3">
            <label for="firstname" class="form-label">ناونیشانی دابینکەر    </label>
             <p class="text-danger">{{ $supplier->address }}</p>
        </div>
    </div>



      <div class="col-md-6">
        <div class="mb-3">
            <label for="firstname" class="form-label">ناوی کۆمپانیای دابینکەر    </label>
             <p class="text-danger">{{ $supplier->shopname }}</p>
        </div>
    </div>


      <div class="col-md-6">
        <div class="mb-3">
            <label for="firstname" class="form-label">جۆری دابینکەر   </label>
             <p class="text-danger">{{ $supplier->type }}</p>
        </div>
    </div>

 
    


 <div class="col-md-6">
        <div class="mb-3">
            <label for="firstname" class="form-label">ناوی هەژماری دابینکەر    </label>
         

             <p class="text-danger">{{ $supplier->account_holder }}</p>
        </div>
    </div>

     <div class="col-md-6">
        <div class="mb-3">
            <label for="firstname" class="form-label">ژمارەی هەژماری دابینکەر    </label>
           
              <p class="text-danger">{{ $supplier->account_number }}</p>
        </div>
    </div>

      <div class="col-md-6">
        <div class="mb-3">
            <label for="firstname" class="form-label">ناوی بانک    </label>
            
              <p class="text-danger">{{ $supplier->bank_name }}</p>
        </div>
    </div>


      <div class="col-md-6">
        <div class="mb-3">
            <label for="firstname" class="form-label">لقی بانک    </label>
           
             <p class="text-danger">{{ $supplier->bank_branch }}</p>
        </div>
    </div>


     <div class="col-md-6">
        <div class="mb-3">
            <label for="firstname" class="form-label">شاری دابینکەر    </label>
            <p class="text-danger">{{ $supplier->city }}</p>
        </div>
    </div>

 

   <div class="col-md-12">
<div class="mb-3">
        <label for="example-fileinput" class="form-label"> </label>
        <img id="showImage" src="{{  asset($supplier->image) }}" class="rounded-circle avatar-lg img-thumbnail"
                alt="profile-image">
    </div>
 </div> <!-- end col -->



            </div> <!-- end row -->
 
         
        </form>
    </div>
    <!-- end settings content-->
    
                                       
                                    </div>
                                </div> <!-- end card-->

                            </div> <!-- end col -->
                        </div>
                        <!-- end row-->

        <!-- Payment Section -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card border-primary">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fa fa-money"></i> پارە گێڕاندن بە دابینکەر</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('supplier.payment.store') }}">
                            @csrf
                            <div class="row">
                                <input type="hidden" name="supplier_id" value="{{ $supplier->id }}">
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">مبلغ پارە (دۆلار)</label>
                                        <input type="number" name="payment_amount" class="form-control" style="height: 45px;" step="0.01" min="0" placeholder="مبلغی پارە بنووسە" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">&nbsp;</label>
                                        <button type="submit" class="btn btn-primary waves-effect waves-light w-100" style="height: 45px;">
                                            <i class="fa fa-check-circle"></i> پارە گێڕاندن
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payments Table -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">مێژوی پارەدانەکان</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>ژمارە</th>
                                        <th>مبلغ</th>
                                        <th>بەروار</th>
                                        <th>کردار</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $totalPaid = 0;
                                    @endphp
                                    @forelse($supplier->payments ?? [] as $payment)
                                        @php
                                            $totalPaid += $payment->payment_amount;
                                        @endphp
                                        <tr>
                                            <td>#{{ $payment->id }}</td>
                                            <td>${{ number_format($payment->payment_amount, 2) }}</td>
                                            <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('Y-m-d H:i') }}</td>
                                            <td>
                                                <a href="{{ route('supplier.payment.delete', $payment->id) }}" class="btn btn-danger btn-sm" onclick="return confirm('آیا مطمئن هستید؟')">
                                                    <i class="fa fa-trash"></i> سڕینەوە
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">هیچ پارەدانێک نەدۆزرایەوە</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <th colspan="1">کۆی پارە</th>
                                        <th colspan="3">${{ number_format($totalPaid, 2) }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

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