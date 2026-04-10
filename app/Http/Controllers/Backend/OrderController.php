<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Orderdetails;
use Carbon\Carbon; 
use Gloudemans\Shoppingcart\Facades\Cart;
use DB;
use Barryvdh\DomPDF\Facade\Pdf;

class OrderController extends Controller
{

public function FinalInvoice(Request $request)
{
    $customer = Customer::findOrFail($request->customer_id);

    $totalProducts = $request->total_products;
    $subTotal = $request->sub_total;
    $pay = $request->pay;
    $currentOrderTotal = $subTotal; // total for this order only

    // Current order due = total - pay
    $currentOrderDue = $currentOrderTotal - $pay;

    // Save the order
    $order = Order::create([
        'customer_id'    => $customer->id,
        'order_date'     => now(),
        'order_status'   => 'pending',
        'total_products' => $totalProducts,
        'sub_total'      => $subTotal,
        'invoice_no'     => 'MSK'.mt_rand(10000000,99999999),
        'total'          => $currentOrderTotal,
        'payment_status' => $request->payment_status,
        'pay'            => $pay,
        'due'            => $currentOrderDue,
        'metter_price'   => 0,
    ]);

    // Save order items WITH meters and colors
    foreach ($request->items as $item) {
        $meters = $item['meters'] ?? 0;
        $unitTotal = $meters * $item['unitcost']; // meters × price, not qty × price
        $selectedColors = $item['selected_colors'] ?? '[]';

        Orderdetails::create([
            'order_id'        => $order->id,
            'product_id'      => $item['product_id'],
            'quantity'        => $item['quantity'],
            'unitcost'        => $item['unitcost'],
            'meters'          => $meters,
            'selected_colors' => $selectedColors,
            'metter_price'    => 0,
            'total'           => $unitTotal,
        ]);
    }

    // **Update customer's total due**
    // customer->due = previous total due + current order due
    $customer->update([
        'due' => $customer->due + $currentOrderDue
    ]);

    // Clear cart
    Cart::destroy();

    // Redirect to print invoice
    return redirect()->route('print.invoice', $order->id);
}





public function PrintInvoice($id)
{
    // Load order with customer and order items
     $order = Order::with([
        'customer',
        'orderItems.product' // ✅ THIS LINE FIXES EVERYTHING
    ])->findOrFail($id);


    // Calculate previous due: all orders before this one for this customer
    $previousDue = Order::where('customer_id', $order->customer_id)
                        ->where('id', '<', $order->id)
                        ->sum('due');

    // Grand total = current order subtotal + previous due
    $grandTotal = $order->sub_total + $previousDue;

    return view('backend.invoice.print_invoice', compact('order', 'previousDue', 'grandTotal'));
}







    public function PendingOrder(){

        $orders = Order::where('order_status','pending')->get();
        return view('backend.order.pending_order',compact('orders'));

    }// End Method 

     public function CompleteOrder(){

        $orders = Order::where('order_status','complete')->get();
        return view('backend.order.complete_order',compact('orders'));

    }// End Method 


    public function OrderDetails($order_id){

        $order = Order::where('id',$order_id)->first();

        $orderItem = Orderdetails::with('product')->where('order_id',$order_id)->orderBy('id','DESC')->get();
        return view('backend.order.order_details',compact('order','orderItem'));

    }// End Method 


    public function OrderStatusUpdate(Request $request){

        $order_id = $request->id;


    $product = Orderdetails::where('order_id',$order_id)->get();
        foreach($product as $item){
           Product::where('id',$item->product_id)
                ->update(['product_store' => DB::raw('product_store-'.$item->quantity) ]);
        }

     Order::findOrFail($order_id)->update(['order_status' => 'complete']);

         $notification = array(
            'message' => 'Order Done Successfully',
            'alert-type' => 'success'
        ); 

        return redirect()->route('pending.order')->with($notification);


    }// End Method 


    public function StockManage(){

    $product = Product::latest()->get();
    return view('backend.stock.all_stock',compact('product'));

    }// End Method 


    public function OrderInvoice($order_id){

         $order = Order::where('id',$order_id)->first();

        $orderItem = Orderdetails::with('product')->where('order_id',$order_id)->orderBy('id','DESC')->get();
$pdf = Pdf::loadView(
        'backend.order.order_invoice',
        compact('order', 'orderItem')
    )
    ->setPaper('a4', 'portrait')
    ->setOptions([
        'defaultFont' => 'DejaVu Sans',
        'isHtml5ParserEnabled' => true,
        'isRemoteEnabled' => true,
        'tempDir' => storage_path('app/temp'),
        'chroot' => public_path(),
    ]);

         return $pdf->download('invoice.pdf');

    }// End Method 


    public function PendingDue(){

        $alldue = Order::where('due','>','0')->orderBy('id','DESC')->get();
        return view('backend.order.pending_due',compact('alldue'));
    }// End Method 


    public function OrderDueAjax($id){

        $order = Order::findOrFail($id);
        return response()->json($order);

    }// End Method 


    public function UpdateDue(Request $request){

        $order_id = $request->id;
        $due_amount = $request->due;
        $pay_amount = $request->pay;

        $allorder = Order::findOrFail($order_id);
        $maindue = $allorder->due;
        $maindpay = $allorder->pay;
 
        $paid_due = $maindue - $due_amount;
        $paid_pay = $maindpay + $due_amount;

        Order::findOrFail($order_id)->update([
            'due' => $paid_due,
            'pay' => $paid_pay, 
        ]);

         $notification = array(
            'message' => 'Due Amount Updated Successfully',
            'alert-type' => 'success'
        ); 

        return redirect()->route('pending.due')->with($notification);


    }// End Method 


}
 