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
                                            <li class="breadcrumb-item"><a href="javascript: void(0);">زیادکردنی بەرهەم</a></li>
                                            
                                        </ol>
                                    </div>
                                    <h4 class="page-title">زیادکردنی بەرهەم</h4>
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
        <form id="myForm" method="post" action="{{ route('product.store') }}" enctype="multipart/form-data">
        	@csrf

            <h5 class="mb-4 text-uppercase"><i class="mdi mdi-account-circle me-1"></i> زیادکردنی بەرهەم</h5>

            <div class="row">


    <div class="col-md-6">
        <div class="form-group mb-3">
            <label for="firstname" class="form-label">ناوی بەرهەم</label>
            <input type="text" name="product_name" class="form-control"   >
           
        </div>
    </div>


              <div class="col-md-6">
        <div class="form-group mb-3">
            <label for="firstname" class="form-label">جۆرەکەی </label>
            <select name="category_id" class="form-select" id="example-select">
                    <option selected disabled >دیاریکردنی جۆر </option>
                    @foreach($category as $cat)
        <option value="{{ $cat->id }}">{{ $cat->category_name }}</option>
                     @endforeach
                </select>
           
        </div>
    </div>

          <div class="col-md-6">
        <div class="form-group mb-3">
            <label for="firstname" class="form-label">دابینکەر </label>
            <select name="supplier_id" class="form-select" id="example-select">
                    <option selected disabled >دیاریکردنی دابینکەر </option>
                    @foreach($supplier as $sup)
        <option value="{{ $sup->id }}">{{ $sup->name }}</option>
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
        <option value="{{ $code->id }}">{{ $code->code_name }}</option>
                     @endforeach
                </select>
           
        </div>
    </div>
    <!-- END CODE DROPDOWN -->




           <!--    <div class="col-md-6">
        <div class="form-group mb-3">
            <label for="firstname" class="form-label">Product Code    </label>
            <input type="text" name="product_code" class="form-control "   >
            
           </div>
        </div> -->


     
              <div class="col-md-6">
        <div class="form-group mb-3">
            <label for="firstname" class="form-label">ناونیشانی کۆگا </label>
            <input type="text" name="product_garage" class="form-control "   >
            
           </div>
        </div>


              <div class="col-md-6">
        <div class="form-group mb-3">
            <label for="firstname" class="form-label">تۆپ  </label>
            <input type="text" name="product_store" class="form-control "   >
            
           </div>
        </div>



    


              <div class="col-md-6">
        <div class="form-group mb-3">
            <label for="firstname" class="form-label">بەرواری کڕین   </label>
            <input type="date" name="buying_date" class="form-control "   >
            
           </div>
        </div>

    
              <div class="col-md-6">
        <div class="form-group mb-3">
            <label for="firstname" class="form-label">تا چەند بمێنێتەوە    </label>
            <input type="date" name="expire_date" class="form-control "   >
            
           </div>
        </div>

    
              <div class="col-md-6">
        <div class="form-group mb-3">
            <label for="firstname" class="form-label">نرخی کڕین   </label>
            <input type="text" name="buying_price" class="form-control "   >
            
           </div>
        </div>


    
              <div class="col-md-6">
        <div class="form-group mb-3">
            <label for="firstname" class="form-label">نرخی فرۆشتن    </label>
            <input type="text" name="selling_price" class="form-control "   >
            
           </div>
        </div>


     <!-- START COLORS SECTION -->
     <div class="col-12">
        <div class="form-group mb-3">
            <label class="form-label">ڕەنگەکان <span class="text-danger">*</span></label>
            <div id="colors-container">
                <!-- Colors will be added here dynamically -->
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
            <input type="text" class="form-control" id="total_meters" readonly value="0">
        </div>
    </div>
    <!-- END COLORS SECTION -->



  <!-- IMAGE INPUT WITH CAMERA OPTION -->
<div class="col-md-12">
    <div class="form-group mb-3">
        <label for="image" class="form-label">وێنە <span class="text-danger">*</span></label>
        <div class="input-group">
            <input type="file" name="product_image" id="image" class="form-control" accept="image/*">
            <button class="btn btn-primary" type="button" id="cameraBtn" title="Open Camera">
                <i class="mdi mdi-camera"></i> کیمێرا
            </button>
        </div>
        <small class="text-muted d-block mt-2">یان فایڵ هەڵبژێرە یان کیمێرا بکەبێتە بکار</small>
    </div>
</div>

<!-- HIDDEN CAMERA INPUT -->
<input type="file" id="cameraInput" style="display: none;" accept="image/*" capture="camera">

<!-- CAMERA MODAL -->
<div class="modal fade" id="cameraModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">وێنە لە کیمێرا</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <video id="cameraVideo" style="width: 100%; max-width: 500px; border: 2px solid #ddd; border-radius: 8px;"></video>
                <canvas id="cameraCanvas" style="display: none;"></canvas>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">داخستن</button>
                <button type="button" class="btn btn-danger" id="stopCameraBtn">
                    <i class="mdi mdi-stop"></i> کیمێرا بستن
                </button>
                <button type="button" class="btn btn-success" id="captureBtn">
                    <i class="mdi mdi-camera-iris"></i> وێنە بگیرە
                </button>
            </div>
        </div>
    </div>
</div>

<!-- IMAGE PREVIEW -->
<div class="col-md-12">
    <div class="mb-3">
        <label class="form-label"> </label>
        <img id="showImage" src="{{  url('upload/no_image.jpg') }}" class="rounded-circle avatar-lg img-thumbnail"
                alt="profile-image">
    </div>
</div>

<!-- CAMERA JAVASCRIPT -->
<script type="text/javascript">
    let cameraStream = null;
    let cameraModal = null;

    document.addEventListener('DOMContentLoaded', function() {
        cameraModal = new bootstrap.Modal(document.getElementById('cameraModal'));
        
        // Camera Button Click
        document.getElementById('cameraBtn').addEventListener('click', function() {
            openCamera();
        });

        // Capture Button
        document.getElementById('captureBtn').addEventListener('click', function() {
            takePhoto();
        });

        // Stop Camera Button
        document.getElementById('stopCameraBtn').addEventListener('click', function() {
            stopCamera();
        });

        // File Input Change (for regular file selection)
        document.getElementById('image').addEventListener('change', function(e) {
            handleImagePreview(e);
        });
    });

    // Open Camera
    function openCamera() {
        cameraModal.show();
        const video = document.getElementById('cameraVideo');
        
        // Check browser support
        navigator.mediaDevices.getUserMedia({
            video: { 
                facingMode: 'environment',
                width: { ideal: 1280 },
                height: { ideal: 720 }
            },
            audio: false
        })
        .then(function(stream) {
            cameraStream = stream;
            video.srcObject = stream;
            video.play();
        })
        .catch(function(error) {
            alert('کیمێرا بەکار ناتوانرێت: ' + error.message);
            cameraModal.hide();
        });
    }

    // Stop Camera
    function stopCamera() {
        if(cameraStream) {
            cameraStream.getTracks().forEach(track => track.stop());
            cameraStream = null;
        }
        cameraModal.hide();
    }

    // Take Photo
    function takePhoto() {
        const video = document.getElementById('cameraVideo');
        const canvas = document.getElementById('cameraCanvas');
        const ctx = canvas.getContext('2d');

        // Set canvas size to match video
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;

        // Draw video frame to canvas
        ctx.drawImage(video, 0, 0);

        // Convert canvas to blob and create file
        canvas.toBlob(function(blob) {
            // Create a File object from blob
            const timestamp = new Date().getTime();
            const file = new File([blob], `camera_photo_${timestamp}.jpg`, { type: 'image/jpeg' });

            // Create DataTransfer to set file input
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            
            // Set the file to input
            document.getElementById('image').files = dataTransfer.files;

            // Update preview
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('showImage').src = e.target.result;
            };
            reader.readAsDataURL(blob);

            // Stop camera and close modal
            stopCamera();

            // Show success message
            alert('وێنە بە سەرکەوتی گیرایەوە');
        }, 'image/jpeg', 0.95);
    }

    // Handle Image Preview (for file input)
    function handleImagePreview(e) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('showImage').src = e.target.result;
        }
        reader.readAsDataURL(e.target.files['0']);
    }
</script>
<!-- END CAMERA JAVASCRIPT -->



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
        <div class="row mb-2" id="color-${colorCount}">
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
    const colorDiv = document.getElementById(`color-${id}`);
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

// Add first color field by default when page loads
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('add-color-btn').click();
});
</script>
<!-- END COLORS JAVASCRIPT -->

@endsection