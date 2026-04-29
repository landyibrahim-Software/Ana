<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Orderdetails;
use Carbon\Carbon; 
use App\Models\Payment;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class OrderController extends Controller
{

    /**
     * Create final invoice and save order
     */
    public function FinalInvoice(Request $request)
    {
        // ✅ Validate input
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'sub_total' => 'required|numeric',
            'pay' => 'required|numeric',
            'payment_status' => 'required',
            'items' => 'required|array',
        ]);

        $customer = Customer::findOrFail($request->customer_id);

        $totalProducts = $request->total_products ?? count($request->items ?? []);
        $subTotal = floatval($request->sub_total);
        $pay = floatval($request->pay);
        $currentOrderTotal = $subTotal;

        // Current order due = total - pay
        $currentOrderDue = $currentOrderTotal - $pay;

        // ✅ CORRECT: Use the previousDue passed from product_invoice
        $customerDue = floatval($request->due ?? 0);

        DB::beginTransaction();
        try {
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
            ]);

            // ✅ OPTIMIZATION: Use batch insert instead of loop
            $orderDetails = [];
            $productUpdates = [];

            foreach ($request->items as $item) {
                $quantity = floatval($item['quantity'] ?? 0);
                $unitcost = floatval($item['unitcost']);
                $unitTotal = $quantity * $unitcost;

                // Prepare order detail for batch insert
                $orderDetails[] = [
                    'order_id'   => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity'   => $quantity,
                    'unitcost'   => $unitcost,
                    'total'      => $unitTotal,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // Collect product IDs and quantities for batch update
                $productUpdates[$item['product_id']] = ($productUpdates[$item['product_id']] ?? 0) + $quantity;
            }

            // ✅ Batch insert order details (much faster than loop)
            Orderdetails::insert($orderDetails);

            // ✅ OPTIMIZATION: Update stock using raw SQL (single query)
            foreach ($productUpdates as $productId => $totalQty) {
                Product::where('id', $productId)
                    ->update(['product_store' => DB::raw("product_store - " . (int) $totalQty)]);
            }

            // ✅ CORRECT: Only add the ORDER DUE to customer due
            $newCustomerDue = max(0, ($customer->due ?? 0) + $currentOrderDue);
            
            $customer->update([
                'due' => $newCustomerDue,
                'total_paid' => ($customer->total_paid ?? 0) + $pay,
                'total_orders' => ($customer->total_orders ?? 0) + 1,
                'updated_at' => now()
            ]);

            DB::commit();

            // Clear cart
            Cart::destroy();

            // Redirect to print invoice
            return redirect()->route('print.invoice', $order->id)
                ->with(['message' => '✅ داواکاری بە سەرکەوتی تۆمارکرا', 'alert-type' => 'success']);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with([
                'message' => 'هەڵە: ' . $e->getMessage(),
                'alert-type' => 'danger'
            ]);
        }
    }

    /**
     * Print invoice view
     */
public function PrintInvoice($id)
{
    // ✅ Load order with customer and order details (eager load)
    $order = Order::with([
        'customer:id,name,phone,due,address',
        'orderDetails.product:id,product_name,product_code,selling_price'
    ])->findOrFail($id);

    // Calculate only current order totals
    $subTotal = floatval($order->sub_total ?? 0);
    $orderDue = floatval($order->due ?? 0);

    // ✅ ADD THIS — customer's total due after this order
    $customerDue = floatval($order->customer->due ?? 0);

    return view('backend.invoice.print_invoice', compact('order', 'subTotal', 'orderDue', 'customerDue'));
}

    /**
     * ISSUE #1: Show pending orders with pagination
     */
    public function PendingOrder(){
        // ✅ FIX: Paginate orders (20 per page) with eager load
        $orders = Order::where('order_status', 'pending')
            ->with('customer:id,name,phone,due,image') // Eager load customer
            ->select(['id', 'customer_id', 'order_date', 'order_status', 'payment_status', 'pay', 'due', 'invoice_no', 'created_at'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('backend.order.pending_order', compact('orders'));
    }

    /**
     * ISSUE #2: Show complete orders with pagination
     */
    public function CompleteOrder(){
        // ✅ FIX: Paginate orders (20 per page) with eager load
        $orders = Order::where('order_status', 'complete')
            ->with('customer:id,name,phone,due,image')
            ->select(['id', 'customer_id', 'order_date', 'order_status', 'payment_status', 'pay', 'due', 'invoice_no', 'created_at'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('backend.order.complete_order', compact('orders'));
    }

    /**
     * ISSUE #3: Show order details - FIX N+1 QUERY
     */
    public function OrderDetails($order_id){
        // ✅ FIX: Use eager load to prevent N+1 queries
        $order = Order::with([
            'customer:id,name,phone,due,address',
            'orderDetails.product:id,product_name,product_code,selling_price,buying_price'
        ])->findOrFail($order_id);
        
        // Get order items from the already-loaded relationship
        $orderItem = $order->orderDetails;
        
        return view('backend.order.order_details', compact('order', 'orderItem'));
    }

    /**
     * ISSUE #4: Update order status - OPTIMIZE STOCK UPDATE
     */
    public function OrderStatusUpdate(Request $request){
        $request->validate([
            'id' => 'required|exists:orders,id'
        ]);

        $order_id = $request->id;
        
        DB::beginTransaction();
        try {
            // ✅ FIX: Use single query with join instead of loop
            DB::update('
                UPDATE products p
                INNER JOIN orderdetails od ON p.id = od.product_id
                SET p.product_store = p.product_store - od.quantity
                WHERE od.order_id = ? AND od.quantity > 0
            ', [$order_id]);
            
            Order::findOrFail($order_id)->update([
                'order_status' => 'complete',
                'updated_at' => now()
            ]);

            DB::commit();
            
            $notification = [
                'message' => '✅ داواکاری بە سەرکەوتی تەواو کرا',
                'alert-type' => 'success'
            ]; 
            
            return redirect()->route('pending.order')->with($notification);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with([
                'message' => 'هەڵە: ' . $e->getMessage(),
                'alert-type' => 'danger'
            ]);
        }
    }

    /**
     * ISSUE #5: Show all products - ADD PAGINATION & SEARCH
     */
    public function StockManage(Request $request){
        // ✅ FIX: Paginate products (50 per page) and add search
        $query = Product::with('category:id,category_name', 'supplier:id,name')
            ->select(['id', 'product_name', 'product_code', 'product_store', 'buying_price', 'selling_price', 'category_id', 'supplier_id', 'product_image', 'product_garage', 'created_at']);
        
        // ✅ Add search functionality
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('product_name', 'LIKE', "%{$search}%")
                  ->orWhere('product_code', 'LIKE', "%{$search}%");
            });
        }
        
        $product = $query->latest()->paginate(50);
        
        return view('backend.stock.all_stock', compact('product'));
    }

    /**
     * Generate PDF Invoice
     */
    public function GenerateInvoicePDF($order_id)
    {
        $order = Order::with([
            'customer:id,name,phone,due,address',
            'orderDetails.product:id,product_name,product_code,selling_price'
        ])->findOrFail($order_id);
        
        $subTotal = floatval($order->sub_total ?? 0);
$orderDue = floatval($order->due ?? 0);

$pdf = PDF::loadView('backend.invoice.print_invoice', compact('order', 'subTotal', 'orderDue'))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont' => 'DejaVu Sans',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => false,
                'tempDir' => storage_path('app/temp'),
                'chroot' => public_path(),
            ]);

        return $pdf->download('invoice_' . $order->invoice_no . '.pdf');
    }

    /**
     * ISSUE #7: Show pending dues with pagination
     */
    public function PendingDue(){
        // ✅ FIX: Add pagination and eager load customer
        $alldue = Order::where('due', '>', 0)
            ->with('customer:id,name,phone,due,address')
            ->select(['id', 'customer_id', 'order_date', 'invoice_no', 'sub_total', 'pay', 'due', 'created_at'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('backend.order.pending_due', compact('alldue'));
    }

    /**
     * Get order details via AJAX
     */
    public function OrderDueAjax($id){
        $order = Order::select(['id', 'customer_id', 'invoice_no', 'sub_total', 'pay', 'due'])
            ->with('customer:id,name,phone')
            ->findOrFail($id);
        
        return response()->json($order);
    }

    /**
     * Update order due amount
     */
    public function UpdateDue(Request $request){
        $request->validate([
            'id' => 'required|exists:orders,id',
            'due' => 'required|numeric|min:0',
            'pay' => 'required|numeric|min:0'
        ]);

        $order_id = $request->id;
        $due_amount = floatval($request->due);
        $pay_amount = floatval($request->pay);

        DB::beginTransaction();
        try {
            $allorder = Order::findOrFail($order_id);
            $maindue = floatval($allorder->due);
            $maindpay = floatval($allorder->pay);
     
            $paid_due = max(0, $maindue - $due_amount);
            $paid_pay = $maindpay + $due_amount;

            $updatedOrder = Order::findOrFail($order_id);
            $updatedOrder->update([
                'due'        => $paid_due,
                'pay'        => $paid_pay,
                'updated_at' => now()
            ]);

            // Recalculate the customer's total running due from scratch
            $affectedCustomer = $updatedOrder->customer;
            if ($affectedCustomer) {
                $ordersTotal  = $affectedCustomer->orders()->where('order_status', '!=', 'cancelled')->sum('sub_total') ?? 0;
                $ordersPaid   = $affectedCustomer->orders()->where('order_status', '!=', 'cancelled')->sum('pay') ?? 0;
                $paymentsPaid = Payment::where('customer_id', $affectedCustomer->id)
                                    ->where('payment_status', 'completed')
                                    ->sum('payment_amount') ?? 0;
                $newCustomerDue = max(0,
                    floatval($affectedCustomer->previous_due ?? 0)
                    + floatval($ordersTotal)
                    - floatval($ordersPaid)
                    - floatval($paymentsPaid)
                );
                $affectedCustomer->update(['due' => $newCustomerDue, 'updated_at' => now()]);
            }

            DB::commit();

            $notification = [
                'message' => '✅ بڕی دێتە پشتەوە بە سەرکەوتی نوێ کرایەوە',
                'alert-type' => 'success'
            ]; 

            return redirect()->route('pending.due')->with($notification);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with([
                'message' => 'هەڵە: ' . $e->getMessage(),
                'alert-type' => 'danger'
            ]);
        }
    }

    /**
     * ISSUE #8: Cancel order and restore stock + customer balance
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
                $refundAmount = Orderdetails::whereIn('id', $rejectedItemIds)
                    ->selectRaw('SUM(quantity * unitcost) as total')
                    ->value('total') ?? 0;
            }

            $totalQuantityRestored = 0;

            // ✅ OPTIMIZATION: Batch restore stock using raw SQL
            $quantities = Orderdetails::whereIn('id', $rejectedItemIds)
                ->select('product_id', DB::raw('SUM(quantity) as total_qty'))
                ->groupBy('product_id')
                ->get();

            foreach ($quantities as $item) {
                Product::where('id', $item->product_id)
                    ->update(['product_store' => DB::raw("product_store + " . (int) $item->total_qty)]);
                
                $totalQuantityRestored += $item->total_qty;
            }

            // ✅ Mark items as cancelled
            Orderdetails::whereIn('id', $rejectedItemIds)
                ->update(['quantity' => 0, 'updated_at' => now()]);

            // Mark order as cancelled FIRST so recalculation excludes it
            $order->update([
                'order_status'   => 'cancelled',
                'payment_status' => 'cancelled',
                'updated_at'     => now()
            ]);

            // Recalculate customer fields from scratch (same logic as PaymentCustomer)
            $ordersTotal  = $customer->orders()->where('order_status', '!=', 'cancelled')->sum('sub_total') ?? 0;
            $ordersPaid   = $customer->orders()->where('order_status', '!=', 'cancelled')->sum('pay') ?? 0;
            $paymentsPaid = Payment::where('customer_id', $customer->id)
                                ->where('payment_status', 'completed')
                                ->sum('payment_amount') ?? 0;
            $totalSpent    = floatval($customer->previous_due ?? 0) + floatval($ordersTotal);
            $totalPaidAll  = floatval($ordersPaid) + floatval($paymentsPaid);
            $totalDue      = max($totalSpent - $totalPaidAll, 0);

            $customer->update([
                'due'          => $totalDue,
                'total_paid'   => $totalPaidAll,
                'total_spent'  => $totalSpent,
                'total_orders' => $customer->orders()->where('order_status', '!=', 'cancelled')->count(),
                'updated_at'   => now(),
            ]);

            DB::commit();

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