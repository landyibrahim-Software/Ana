@extends('admin_dashboard')
@section('admin')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>


 <div class="content">

                    <!-- Start Content-->
                    <div class="container-fluid">

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    <div class="page-title-right">
                                        <ol class="breadcrumb m-0">
   <li class="breadcrumb-item"><a  class="btn btn-primary rounded-pill waves-effect waves-light" href="{{ route('all.product') }}">گەڕانەوە </a></li>
                                            
                                        </ol>
                                    </div>
                                    <h4 class="page-title">باڕکۆدی بەرهەم</h4>
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
       

            <h5 class="mb-4 text-uppercase"><i class="mdi mdi-account-circle me-1"></i> باڕکۆدی بەرهەم</h5>

            <div class="row">


    <div class="col-md-6">
        <div class="form-group mb-3">
            <label for="firstname" class="form-label">کۆدی بەرهەم</label>
             <h3>{{ $product->product_code }}</h3>
           
        </div>
    </div>
 
   @php
  $generator = new Picqer\Barcode\BarcodeGeneratorHTML();
   @endphp


              <div class="col-md-6">
        <div class="form-group mb-3">
            <label for="firstname" class="form-label">باڕکۆدی بەرهەم    </label>
           <h3> {!! $generator->getBarcode($product->product_code,$generator::TYPE_CODE_128)  !!} </h3>
            
           </div>
        </div>
            </div> <!-- end row --> 
       <!-- Hidden inputs to store data for printing -->
            <input type="hidden" id="product-code" value="{{ $product->product_code }}">
            <input type="hidden" id="product-name" value="{{ $product->product_name }}">
            <input type="hidden" id="product-price" value="{{ $product->price ?? '' }}">
            
            <!-- Print Button -->
            <button id="print-barcode" class="btn btn-primary">
                چاپکردنی باڕکۆد
            </button>
        </div>
    </div>
</div>

<script>
document.getElementById('print-barcode').addEventListener('click', function () {
    var productCode = document.getElementById('product-code').value;
    var productName = document.getElementById('product-name').value;
    var productPrice = document.getElementById('product-price').value;

    var printWindow = window.open('', '_blank', 'width=500,height=400');

    printWindow.document.write(`
        <html>
        <head>
            <title>Print Barcode</title>
            <style>
                body { font-family: Arial; text-align: center; padding: 20px; }
            </style>
        </head>
        <body>
            <h3>Product Barcode</h3>
            <svg id="barcode"></svg>

            <p><strong>Code:</strong> ${productCode}</p>
            <p>${productName}</p>
            ${productPrice ? `<p><strong>Price:</strong> $${productPrice}</p>` : ''}

            <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"><\/script>
            <script>
                JsBarcode("#barcode", "${productCode}", {
                    format: "CODE128",
                    width: 2,
                    height: 60
,
                    displayValue: false
                });

                window.onload = function() {
                    window.print();
                }
            <\/script>
        </body>
        </html>
    `);

    printWindow.document.close();
});
</script>

      



@endsection