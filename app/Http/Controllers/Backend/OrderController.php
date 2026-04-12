<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Orderdetails;
use App\Models\ProductColor;
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
        'previous_due'   => $previousDue,
        'metter_price'   => 0,
    ]);

    // Save order items WITH meters and colors + UPDATE STOCK
    foreach ($request->items as $item) {
        $meters = $item['meters'] ?? 0;
        $unitTotal = $meters * $item['unitcost'];
        $selectedColors = $item['selected_colors'] ?? '[]';
        
        // Count rolls from colors
        $totalRolls = 0;
        if (!empty($item['selected_colors'])) {
            $colors = json_decode($item['selected_colors'], true);
            foreach ($colors as $color) {
                $totalRolls += intval($color['rolls'] ?? 0);
            }
        }

        // Save order detail
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

        // 🔥 REDUCE COLOR METERS FROM PRODUCTCOLOR TABLE
        if (!empty($item['selected_colors'])) {
            $colors = json_decode($item['selected_colors'], true);
            
            foreach ($colors as $color) {
                // Find the color record and reduce meters
                \App\Models\ProductColor::where('product_id', $item['product_id'])
                    ->where('id', $color['id'])
                    ->decrement('meters', floatval($color['meter'] ?? 0));
            }
        }
        
        // 🔥 REDUCE تۆپ (ROLLS) FROM PRODUCT STORE BY ROLLS COUNT
        if ($totalRolls > 0) {
            $product = Product::find($item['product_id']);
            if ($product) {
                $product->product_store -= $totalRolls;
                $product->save();
            }
        }
    }

    // ✅ CORRECT: Only add the ORDER DUE to customer due
    // If pay > subtotal, the order due is negative, so customer credit increases
    // This is correct!
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
    // Load order with customer and order items
    $order = Order::with([
        'customer',
        'orderItems.product'
    ])->findOrFail($id);

    // ✅ CORRECT: Use the previous_due stored in this order when it was created
    $previousDue = $order->previous_due;
    
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

/**
 * Cancel Order and Restore Stock + Customer Balance
 */
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
                    $itemTotal = ($orderDetail->meters ?? $orderDetail->quantity) * $orderDetail->unitcost;
                    $refundAmount += $itemTotal;
                }
            }
        }

        $totalRollsRestored = 0;
        $totalMetersRestored = 0;

        // Process each rejected item - RESTORE STOCK & ROLLS
        foreach ($rejectedItemIds as $itemId) {
            $orderDetail = Orderdetails::find($itemId);
            
            if ($orderDetail) {
                $product = Product::find($orderDetail->product_id);
                
                if ($product && $orderDetail->selected_colors) {
                    // Restore colors and meters
                    $colors = json_decode($orderDetail->selected_colors, true);
                    if (is_array($colors)) {
                        $totalRolls = 0;
                        
                        foreach ($colors as $color) {
                            // ✅ RESTORE METER TO SPECIFIC COLOR
                            if (isset($color['id'])) {
                                ProductColor::where('product_id', $orderDetail->product_id)
                                    ->where('id', $color['id'])
                                    ->increment('meters', floatval($color['meter'] ?? 0));
                                
                                // Track meters restored
                                $totalMetersRestored += floatval($color['meter'] ?? 0);
                            }
                            
                            // ✅ COUNT ROLLS TO RESTORE (EXACT ROLLS THAT WERE SOLD)
                            $rollsInThisColor = intval($color['rolls'] ?? 0);
                            $totalRolls += $rollsInThisColor;
                        }
                        
                        // ✅ RESTORE ROLLS TO PRODUCT STORE (EXACTLY WHAT WAS SOLD)
                        if ($totalRolls > 0) {
                            $product->increment('product_store', $totalRolls);
                            $product->save();
                            $totalRollsRestored += $totalRolls;
                        }
                    }
                } elseif ($product) {
                    // If no selected colors, just restore basic quantity
                    $quantityToRestore = intval($orderDetail->quantity ?? 0);
                    if ($quantityToRestore > 0) {
                        $product->increment('product_store', $quantityToRestore);
                        $product->save();
                        $totalRollsRestored += $quantityToRestore;
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

        // ✅ IMPROVED MESSAGE showing rolls and meters restored
        return redirect()->back()->with([
            'message' => "✅ داواکاری بە سەرکەوتی لابرێت - {$totalRollsRestored} تۆپ ({$totalMetersRestored}م) و {$refundAmount} $ گێڕایەوە",
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