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
                                    <h4 class="page-title">چآوەڕوانی قەرز</h4>
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
                                <th>بەرواری ئۆردەر</th>
                                <th>پارەدان</th>
                                <th>کۆی گشتی</th>
                                <th>پارەی دراو</th>
                                <th>قەرز</th>
                                <th>کردار</th>
                            </tr>
                        </thead>
                    
    
        <tbody>
        	@foreach($alldue as $key=> $item)
            <tr>
                <td>{{ $key+1 }}</td>
                <td> <img src="{{ asset($item->customer->image) }}" style="width:50px; height: 40px;"> </td>
                <td>{{ $item['customer']['name'] }}</td>
                <td>{{ $item->order_date }}</td>
                <td>{{ $item->payment_status }}</td>
               <td> <span class="btn btn-info waves-effect waves-light"> {{  $item->total  }}</span> </td>
                <td> <span class="btn btn-warning waves-effect waves-light"> {{ round($item->pay) }}</span> </td>
               <td> <span class="btn btn-danger waves-effect waves-light"> {{ round($item->due) }}</span> </td>
                <td>
<a href="{{ route('order.details',$item->id) }}" class="btn btn-blue rounded-pill waves-effect waves-light"> وردەکای </a> 

  <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#signup-modal" id="{{ $item->id }}" onclick="orderDue(this.id)" >قەرزەکە بدە </button> 

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



                 <!-- Signup modal content -->
<div id="signup-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-body"> 
                <div class="text-center mt-2 mb-4 ">
                        <div class="auth-logo">
                            
                            <h3> کۆی گشتی قەرزەکە بدە </h3>
                        </div>
                </div>
 

  <form class="px-3" method="post" action="{{ route('update.due') }}">
                    @csrf
    
    <input type="hidden" name="id" id="order_id">
    <input type="hidden" name="pay" id="pay">

       <div class="mb-3">
             <label for="username" class="form-label">ئیستا پارە بدە</label>
     <input class="form-control" type="text" name="due" id="due"  >
          </div>
 

                    <div class="mb-3 text-center">
     <button class="btn btn-primary" type="submit">قەرز ئەبدەیتکردنەوە  </button>
                    </div>

                </form>

            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->


<script type="text/javascript">
        
        function orderDue(id) {
            $.ajax({
                type: 'GET',
                url: '/order/due/'+id,
                dataType: 'json',
                success:function(data){
                    // console.log(data)
                    $('#due').val(data.due);
                    $('#pay').val(data.pay);
                    $('#order_id').val(data.id);
                }
            })
        }


</script>


@endsection 