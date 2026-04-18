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

    $totalProducts = $request->total_products ?? count($request->items ?? []);
    $subTotal = $request->sub_total;
    $pay = $request->pay;
    $currentOrderTotal = $subTotal;

    // Current order due = total - pay
    $currentOrderDue = $currentOrderTotal - $pay;

    // ✅ CORRECT: Use the previousDue passed from product_invoice
    $previousDue = floatval($request->previous_due ?? 0);

    // Save the order (REMOVED: metter_price)
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
        'previous_due'   => $previousDue,
    ]);

    // FIXED: Save order items with SIMPLE quantity (NO colors/meters/metter_price)
    foreach ($request->items as $item) {
        $quantity = floatval($item['quantity'] ?? 0);
        $unitTotal = $quantity * floatval($item['unitcost']);

        // Save order detail (REMOVED: metter_price)
        Orderdetails::create([
            'order_id'        => $order->id,
            'product_id'      => $item['product_id'],
            'quantity'        => $quantity,
            'unitcost'        => $item['unitcost'],
            'total'           => $unitTotal,
        ]);

        // 🔥 REDUCE STOCK FROM PRODUCT STORE BY QUANTITY
        $product = Product::find($item['product_id']);
        if ($product) {
            $product->product_store -= $quantity;
            $product->save();
        }
    }

    // ✅ CORRECT: Only add the ORDER DUE to customer due
    // If pay > subtotal, the order due is negative, so customer credit increases
    $newCustomerDue = max(0, ($customer->due ?? 0) + $currentOrderDue);
    
    $customer->update([
        'due' => $newCustomerDue,
        'total_paid' => ($customer->total_paid ?? 0) + $pay,
        'total_orders' => ($customer->total_orders ?? 0) + 1
    ]);

    // Clear cart
    Cart::destroy();

    // Redirect to print invoice
    return redirect()->route('print.invoice', $order->id);
}

public function PrintInvoice($id)
{
    // Load order with customer and order details
    $order = Order::with([
        'customer',
        'orderDetails.product'
    ])->findOrFail($id);

    // Use the previous_due stored in this order
    $previousDue = floatval($order->previous_due ?? 0);
    
    // Calculate totals
    $subTotal = floatval($order->sub_total ?? 0);
    $grandTotal = $subTotal + $previousDue;

    return view('backend.invoice.print_invoice', compact('order', 'previousDue', 'grandTotal', 'subTotal'));
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


    public function GenerateInvoicePDF($order_id)
{
    $order = Order::with(['customer', 'orderDetails.product'])->findOrFail($order_id);
    
    $previousDue = $order->previous_due;
    $grandTotal = $order->sub_total + $previousDue;
    
    $pdf = PDF::loadView('backend.invoice.print_invoice', compact('order', 'previousDue', 'grandTotal'))
        ->setPaper('a4', 'portrait')
        ->setOptions([
            'defaultFont' => 'DejaVu Sans',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'tempDir' => storage_path('app/temp'),
            'chroot' => public_path(),
        ]);

    return $pdf->download('invoice_' . $order->id . '.pdf');
}


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

/**
 * Cancel Order and Restore Stock + Customer Balance
 */
public function cancelOrder(Request $request)
{
    $request->validate([
        'order_id' => 'required|exists:orders,id',
        'rejected_items' => 'required|array',
        'refund_from' => 'required|in:due,paid',
    ]);

    $order = Order::findOrFail($request->order_id);
    $customer = $order->customer;
    
    if (!$customer) {
        return redirect()->back()->with([
            'message' => 'هەڵەیەک روویدا: کڕیار نەدۆزرایەوە',
            'alert-type' => 'danger'
        ]);
    }
    
    $rejectedItemIds = $request->rejected_items;
    $refundAmount = floatval($request->refund_amount ?? 0);

    DB::beginTransaction();
    try {
        // If no manual refund, calculate from rejected items
        if ($refundAmount == 0) {
            foreach ($rejectedItemIds as $itemId) {
                $orderDetail = Orderdetails::find($itemId);
                if ($orderDetail) {
                    $itemTotal = floatval($orderDetail->quantity) * floatval($orderDetail->unitcost);
                    $refundAmount += $itemTotal;
                }
            }
        }

        $totalQuantityRestored = 0;

        // FIXED: Process each rejected item - RESTORE STOCK (simple quantity)
        foreach ($rejectedItemIds as $itemId) {
            $orderDetail = Orderdetails::find($itemId);
            
            if ($orderDetail) {
                $product = Product::find($orderDetail->product_id);
                
                if ($product) {
                    // Restore simple quantity to product store
                    $quantityToRestore = floatval($orderDetail->quantity ?? 0);
                    if ($quantityToRestore > 0) {
                        $product->increment('product_store', $quantityToRestore);
                        $product->save();
                        $totalQuantityRestored += $quantityToRestore;
                    }
                }

                // Mark item as cancelled (set quantity to 0)
                $orderDetail->update(['quantity' => 0]);
            }
        }

        // ✅ Handle refund from DUE or PAID
        $refundFrom = $request->refund_from;
        
        if ($refundFrom === 'due') {
            // Refund from DUE: Reduce customer's due
            $customer->update([
                'due' => max(0, ($customer->due ?? 0) - $refundAmount),
                'total_orders' => max(0, ($customer->total_orders ?? 0) - 1)
            ]);
        } else if ($refundFrom === 'paid') {
            // Refund from PAID: Only reduce total_paid
            $customer->update([
                'total_paid' => max(0, ($customer->total_paid ?? 0) - $refundAmount),
                'total_orders' => max(0, ($customer->total_orders ?? 0) - 1)
            ]);
        }

        // Mark order as cancelled
        $order->update([
            'order_status' => 'cancelled',
            'payment_status' => 'cancelled'
        ]);

        DB::commit();

        // ✅ IMPROVED MESSAGE showing quantity restored
        return redirect()->back()->with([
            'message' => "✅ داواکاری بە سەرکەوتی لابرێت - {$totalQuantityRestored} بڕ و {$refundAmount} $ گێڕایەوە",
            'alert-type' => 'success'
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()->with([
            'message' => 'هەڵەیەک روویدا: ' . $e->getMessage(),
            'alert-type' => 'danger'
        ]);
    }
}

}