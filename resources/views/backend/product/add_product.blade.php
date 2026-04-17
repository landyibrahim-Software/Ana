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

  <!-- IMAGE INPUT WITH CAMERA OPTION -->
<div class="col-md-12">
    <div class="form-group mb-3">
        <label for="image" class="form-label">وێنە <span class="text-danger">*</span></label>
        <div class="input-group">
            <input type="file" name="product_image" id="image" class="form-control" accept="image/*">
            <button class="btn btn-primary" type="button" id="cameraBtn" title="Open Camera">
                <i class="mdi mdi-camera"></i> کامێرا
            </button>
        </div>
        <small class="text-muted d-block mt-2">یان فایل هەڵبژێرە یان کامێرا بکەوێتە کار</small>
    </div>
</div>

<!-- HIDDEN CAMERA INPUT -->
<input type="file" id="cameraInput" style="display: none;" accept="image/*" capture="camera">

<!-- CAMERA MODAL -->
<div class="modal fade" id="cameraModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">وێنە لە کامێرا</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <video id="cameraVideo" style="width: 100%; max-width: 500px; border: 2px solid #ddd; border-radius: 8px;"></video>
                <canvas id="cameraCanvas" style="display: none;"></canvas>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">داخستن</button>
                <button type="button" class="btn btn-danger" id="stopCameraBtn">
                    <i class="mdi mdi-stop"></i>  کامێرا وەستان
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
            alert('  کامێرا ئیش ناکات: ' + error.message);
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
                product_garage: {
                    required : true,
                }, 
                product_store: {
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
                product_garage: {
                    required : 'Please Enter Product Garage',
                },
                product_store: {
                    required : 'Please Enter Product Store',
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

@endsection