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
                                            <li class="breadcrumb-item"><a href="javascript: void(0);">دەستکاری بەرهەم</a></li>
                                            
                                        </ol>
                                    </div>
                                    <h4 class="page-title">دەستکاری بەرهەم</h4>
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
        <form id="myForm" method="post" action="{{ route('product.update') }}" enctype="multipart/form-data">
        	@csrf

            <input type="hidden" name="id" value="{{ $product->id }}">

            <h5 class="mb-4 text-uppercase"><i class="mdi mdi-account-circle me-1"></i> دەستکاری بەرهەم</h5>

            <div class="row">


    <div class="col-md-6">
        <div class="form-group mb-3">
            <label for="firstname" class="form-label">ناوی بەرهەم</label>
            <input type="text" name="product_name" class="form-control" value="{{ $product->product_name }}"   >
           
        </div>
    </div>


              <div class="col-md-6">
        <div class="form-group mb-3">
            <label for="firstname" class="form-label">جۆری بەرهەم </label>
            <select name="category_id" class="form-select" id="example-select">
                    <option selected disabled >جۆری بەرهەم هەڵبژێرە </option>
                    @foreach($category as $cat)
        <option value="{{ $cat->id }}" {{ $cat->id == $product->category_id ? 'selected' : ''  }} >{{ $cat->category_name }}</option>
                     @endforeach
                </select>
           
        </div>
    </div>

          <div class="col-md-6">
        <div class="form-group mb-3">
            <label for="firstname" class="form-label">دابینکەر </label>
            <select name="supplier_id" class="form-select" id="example-select">
                    <option selected disabled >دابینکەر هەڵبژێرە </option>
                    @foreach($supplier as $sup)
        <option value="{{ $sup->id }}"  {{ $sup->id == $product->supplier_id ? 'selected' : ''  }}>{{ $sup->name }}</option>
                     @endforeach
                </select>
           
        </div>
    </div>

          <!-- ADD CODE DROPDOWN HERE -->
          <div class="col-md-6">
        <div class="form-group mb-3">
            <label for="code_id" class="form-label">کۆد <span class="text-danger">*</span></label>
            <select name="code_id" class="form-select" id="code_id">
                    <option selected disabled >کۆدێک هەڵبژێرە</option>
                    @foreach($codes as $code)
        <option value="{{ $code->id }}" {{ $code->id == $product->code_id ? 'selected' : '' }}>{{ $code->code_name }}</option>
                     @endforeach
                </select>
           
        </div>
    </div>
    <!-- END CODE DROPDOWN -->



              <div class="col-md-6">
        <div class="form-group mb-3">
            <label for="firstname" class="form-label">کۆدی بەرهەم    </label>
            <input type="text" name="product_code" class="form-control "  value="{{ $product->product_code }}"   >
            
           </div>
        </div>


     
              <div class="col-md-6">
        <div class="form-group mb-3">
            <label for="firstname" class="form-label">کۆگای بەرهەم    </label>
            <input type="text" name="product_garage" class="form-control "   value="{{ $product->product_garage }}"  >
            
           </div>
        </div>


              <div class="col-md-6">
        <div class="form-group mb-3">
            <label for="firstname" class="form-label">تۆپ    </label>
            <input type="text" name="product_store" class="form-control "  value="{{ $product->product_store }}"   >
            
           </div>
        </div>



    


              <div class="col-md-6">
        <div class="form-group mb-3">
            <label for="firstname" class="form-label">بەرواری کڕین   </label>
            <input type="date" name="buying_date" class="form-control "  value="{{ $product->buying_date }}"   >
            
           </div>
        </div>

    
              <div class="col-md-6">
        <div class="form-group mb-3">
            <label for="firstname" class="form-label"> تا چەند بمێنێتەوە</label>
            <input type="date" name="expire_date" class="form-control "  value="{{ $product->expire_date }}"   >
            
           </div>
        </div>

    
              <div class="col-md-6">
        <div class="form-group mb-3">
            <label for="firstname" class="form-label">نرخی کڕین   </label>
            <input type="text" name="buying_price" class="form-control "  value="{{ $product->buying_price }}"   >
            
           </div>
        </div>


    
              <div class="col-md-6">
        <div class="form-group mb-3">
            <label for="firstname" class="form-label">نرخی فرۆشتن    </label>
            <input type="text" name="selling_price" class="form-control "  value="{{ $product->selling_price }}"   >
            
           </div>
        </div>


     <!-- START COLORS SECTION -->
     <div class="col-12">
        <div class="form-group mb-3">
            <label class="form-label">ڕەنگەکان <span class="text-danger">*</span></label>
            <div id="colors-container">
                <!-- Colors will be added here dynamically -->
                @foreach($product->colors as $index => $color)
                    <div class="row mb-2" id="color-existing-{{ $color->id }}">
                        <div class="col-md-5">
                            <input type="text" value="{{ $color->color_name }}" 
                                   class="form-control" placeholder="ڕەنگ (مثال: سور، شین)" disabled>
                        </div>
                        <div class="col-md-5">
                            <input type="number" value="{{ $color->meters }}" 
                                   class="form-control meters-input" placeholder="متەر" 
                                   step="0.01" disabled>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-sm btn-danger" 
                                    onclick="removeExistingColor({{ $color->id }})">
                                <i class="mdi mdi-trash-can"></i>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
            <button type="button" class="btn btn-sm btn-success" id="add-color-btn">
                <i class="mdi mdi-plus"></i> ڕەنگ زیادبکە
            </button>
        </div>
    </div>

    <!-- Total Meters Display -->
    <div class="col-md-6">
        <div class="form-group mb-3">
            <label for="total_meters" class="form-label">کۆ متەر</label>
            <input type="text" class="form-control" id="total_meters" readonly value="{{ $product->colors->sum('meters') }}">
        </div>
    </div>
    <!-- END COLORS SECTION -->



   <div class="col-md-12">
<div class="form-group mb-3">
        <label for="example-fileinput" class="form-label">وێنەی بەرهەم</label>
        <input type="file" name="product_image" id="image" class="form-control">
         
    </div>
 </div> <!-- end col -->


   <div class="col-md-12">
<div class="mb-3">
        <label for="example-fileinput" class="form-label"> </label>
        <img id="showImage" src="{{ asset($product->product_image) }}" class="rounded-circle avatar-lg img-thumbnail"
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
    $(document).ready(function (){
        $('#myForm').validate({
            rules: {
                product_name: {
                    required : true,
                }, 
                category_id: {
                    required : true,
                }, 
                supplier_id: {
                    required : true,
                },
                code_id: {
                    required : true,
                }, 
                product_code: {
                    required : true,
                }, 
                product_garage: {
                    required : true,
                }, 
                product_store: {
                    required : true,
                }, 
                buying_date: {
                    required : true,
                }, 
                expire_date: {
                    required : true,
                }, 
                buying_price: {
                    required : true,
                }, 
                selling_price: {
                    required : true,
                }, 
                product_image: {
                    required : true,
                },  
            },
            messages :{
                product_name: {
                    required : 'Please Enter Product Name',
                }, 
                category_id: {
                    required : 'Please Select Category',
                },
                supplier_id: {
                    required : 'Please Select Supplier',
                },
                code_id: {
                    required : 'Please Select Code',
                },
                product_code: {
                    required : 'Please Enter Product Code',
                },
                product_garage: {
                    required : 'Please Enter Product Garage',
                },
                product_store: {
                    required : 'Please Enter Product Store',
                },
                buying_date: {
                    required : 'Please Slect Buying Date',
                },
                expire_date: {
                    required : 'Please Slect Expire Date',
                },
                buying_price: {
                    required : 'Please Enter Buying Price',
                },
                selling_price: {
                    required : 'Please Enter Selling Price',
                },
                product_image: {
                    required : 'Please Select Product Image',
                }, 

            },
            errorElement : 'span', 
            errorPlacement: function (error,element) {
                error.addClass('invalid-feedback');
                element.closest('.form-group').append(error);
            },
            highlight : function(element, errorClass, validClass){
                $(element).addClass('is-invalid');
            },
            unhighlight : function(element, errorClass, validClass){
                $(element).removeClass('is-invalid');
            },
        });
    });
    
</script>


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

<!-- COLORS JAVASCRIPT -->
<script type="text/javascript">
let colorCount = 0;

document.getElementById('add-color-btn').addEventListener('click', function() {
    colorCount++;
    const html = `
        <div class="row mb-2" id="color-new-${colorCount}">
            <div class="col-md-5">
                <input type="text" name="colors[${colorCount}][color_name]" 
                       class="form-control" placeholder="ڕەنگ (مثال: سور، شین)" required>
            </div>
            <div class="col-md-5">
                <input type="number" name="colors[${colorCount}][meters]" 
                       class="form-control meters-input" placeholder="متەر" 
                       step="0.01" required onchange="calculateTotalMeters()">
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-sm btn-danger" 
                        onclick="removeColor(${colorCount})">
                    <i class="mdi mdi-trash-can"></i>
                </button>
            </div>
        </div>
    `;
    document.getElementById('colors-container').insertAdjacentHTML('beforeend', html);
});

function removeColor(id) {
    const colorDiv = document.getElementById(`color-new-${id}`);
    if(colorDiv) {
        colorDiv.remove();
        calculateTotalMeters();
    }
}

function removeExistingColor(id) {
    const colorDiv = document.getElementById(`color-existing-${id}`);
    if(colorDiv) {
        colorDiv.remove();
        calculateTotalMeters();
    }
}

function calculateTotalMeters() {
    const inputs = document.querySelectorAll('.meters-input');
    let total = 0;
    inputs.forEach(input => {
        total += parseFloat(input.value) || 0;
    });
    document.getElementById('total_meters').value = total.toFixed(2);
}

// Calculate total on page load
document.addEventListener('DOMContentLoaded', function() {
    calculateTotalMeters();
});
</script>
<!-- END COLORS JAVASCRIPT -->

@endsection