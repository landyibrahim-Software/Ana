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
        $request->validate([
            'customer_id'    => 'required|exists:customers,id',
            'sub_total'      => 'required|numeric',
            'pay'            => 'required|numeric',
            'payment_status' => 'required',
            'items'          => 'required|array',
        ]);

        $customer = Customer::findOrFail($request->customer_id);

        $totalProducts     = $request->total_products ?? count($request->items ?? []);
        $subTotal          = floatval($request->sub_total);
        $pay               = floatval($request->pay);
        $currentOrderTotal = $subTotal;
        $currentOrderDue   = $currentOrderTotal - $pay;

        DB::beginTransaction();
        try {
            $order = Order::create([
                'customer_id'    => $customer->id,
                'order_date'     => now(),
                'order_status'   => 'pending',
                'total_products' => $totalProducts,
                'sub_total'      => $subTotal,
                'invoice_no'     => 'MSK' . mt_rand(10000000, 99999999),
                'total'          => $currentOrderTotal,
                'payment_status' => $request->payment_status,
                'pay'            => $pay,
                'due'            => $currentOrderDue,
            ]);

            $orderDetails   = [];
            $productUpdates = [];

            foreach ($request->items as $item) {
                $quantity  = floatval($item['quantity'] ?? 0);
                $unitcost  = floatval($item['unitcost']);
                $unitTotal = $quantity * $unitcost;

                $orderDetails[] = [
                    'order_id'   => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity'   => $quantity,
                    'unitcost'   => $unitcost,
                    'total'      => $unitTotal,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $productUpdates[$item['product_id']] = ($productUpdates[$item['product_id']] ?? 0) + $quantity;
            }

            Orderdetails::insert($orderDetails);

            foreach ($productUpdates as $productId => $totalQty) {
                Product::where('id', $productId)
                    ->update(['product_store' => DB::raw("product_store - " . (int) $totalQty)]);
            }

            $newCustomerDue = max(0, ($customer->due ?? 0) + $currentOrderDue);

            $customer->update([
                'due'          => $newCustomerDue,
                'total_paid'   => ($customer->total_paid ?? 0) + $pay,
                'total_orders' => ($customer->total_orders ?? 0) + 1,
                'updated_at'   => now(),
            ]);

            DB::commit();
            Cart::destroy();

            return redirect()->route('print.invoice', $order->id)
                ->with(['message' => '✅ داواکاری بە سەرکەوتی تۆمارکرا', 'alert-type' => 'success']);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with([
                'message'    => 'هەڵە: ' . $e->getMessage(),
                'alert-type' => 'danger',
            ]);
        }
    }

    /**
     * Print invoice view
     */
    public function PrintInvoice($id)
    {
        $order = Order::with([
            'customer:id,name,phone,due,address',
            'orderDetails.product:id,product_name,product_code,selling_price',
        ])->findOrFail($id);

        $subTotal    = floatval($order->sub_total ?? 0);
        $orderDue    = floatval($order->due ?? 0);
        $customerDue = floatval($order->customer->due ?? 0);

        return view('backend.invoice.print_invoice', compact('order', 'subTotal', 'orderDue', 'customerDue'));
    }

    /**
     * Show pending orders with pagination
     */
    public function PendingOrder()
    {
        $orders = Order::where('order_status', 'pending')
            ->with('customer:id,name,phone,due,image')
            ->select(['id', 'customer_id', 'order_date', 'order_status', 'payment_status', 'pay', 'due', 'invoice_no', 'created_at'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('backend.order.pending_order', compact('orders'));
    }

    /**
     * Show complete orders with pagination
     */
    public function CompleteOrder()
    {
        $orders = Order::where('order_status', 'complete')
            ->with('customer:id,name,phone,due,image')
            ->select(['id', 'customer_id', 'order_date', 'order_status', 'payment_status', 'pay', 'due', 'invoice_no', 'created_at'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('backend.order.complete_order', compact('orders'));
    }

    /**
     * Show order details
     */
    public function OrderDetails($order_id)
    {
        $order = Order::with([
            'customer:id,name,phone,due,address',
            'orderDetails.product:id,product_name,product_code,selling_price,buying_price',
        ])->findOrFail($order_id);

        $orderItem = $order->orderDetails;

        return view('backend.order.order_details', compact('order', 'orderItem'));
    }

    /**
     * Update order status to complete
     */
    public function OrderStatusUpdate(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:orders,id',
        ]);

        $order_id = $request->id;

        DB::beginTransaction();
        try {
            DB::update('
                UPDATE products p
                INNER JOIN orderdetails od ON p.id = od.product_id
                SET p.product_store = p.product_store - od.quantity
                WHERE od.order_id = ? AND od.quantity > 0
            ', [$order_id]);

            Order::findOrFail($order_id)->update([
                'order_status' => 'complete',
                'updated_at'   => now(),
            ]);

            DB::commit();

            return redirect()->route('pending.order')->with([
                'message'    => '✅ داواکاری بە سەرکەوتی تەواو کرا',
                'alert-type' => 'success',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with([
                'message'    => 'هەڵە: ' . $e->getMessage(),
                'alert-type' => 'danger',
            ]);
        }
    }

    /**
     * Show all products with pagination & search
     */
    public function StockManage(Request $request)
    {
        $query = Product::with('category:id,category_name', 'supplier:id,name')
            ->select(['id', 'product_name', 'product_code', 'product_store', 'buying_price', 'selling_price', 'category_id', 'supplier_id', 'product_image', 'product_garage', 'created_at']);

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
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
            'orderDetails.product:id,product_name,product_code,selling_price',
        ])->findOrFail($order_id);

        $subTotal = floatval($order->sub_total ?? 0);
        $orderDue = floatval($order->due ?? 0);

        $pdf = Pdf::loadView('backend.invoice.print_invoice', compact('order', 'subTotal', 'orderDue'))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont'          => 'DejaVu Sans',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => false,
                'tempDir'              => storage_path('app/temp'),
                'chroot'               => public_path(),
            ]);

        return $pdf->download('invoice_' . $order->invoice_no . '.pdf');
    }

    /**
     * Show pending dues with pagination
     */
    public function PendingDue()
    {
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
    public function OrderDueAjax($id)
    {
        $order = Order::select(['id', 'customer_id', 'invoice_no', 'sub_total', 'pay', 'due'])
            ->with('customer:id,name,phone')
            ->findOrFail($id);

        return response()->json($order);
    }

    /**
     * Update order due amount (customer pays some/all of a due)
     */
    public function UpdateDue(Request $request)
    {
        $request->validate([
            'id'  => 'required|exists:orders,id',
            'due' => 'required|numeric|min:0',
            'pay' => 'required|numeric|min:0',
        ]);

        $order_id   = $request->id;
        $due_amount = floatval($request->due);

        DB::beginTransaction();
        try {
            $allorder = Order::findOrFail($order_id);
            $maindue  = floatval($allorder->due);
            $maindpay = floatval($allorder->pay);

            // Reduce the order's remaining due; add the payment to order's paid amount
            $paid_due = max(0, $maindue - $due_amount);
            $paid_pay = $maindpay + $due_amount;

            $allorder->update([
                'due'        => $paid_due,
                'pay'        => $paid_pay,
                'updated_at' => now(),
            ]);

            // Recalculate the customer's total running due from scratch
            $affectedCustomer = $allorder->customer;
            if ($affectedCustomer) {
                $ordersTotal  = $affectedCustomer->orders()->where('order_status', '!=', 'cancelled')->sum('sub_total') ?? 0;
                $ordersPaid   = $affectedCustomer->orders()->where('order_status', '!=', 'cancelled')->sum('pay') ?? 0;
                $paymentsPaid = Payment::where('customer_id', $affectedCustomer->id)
                                    ->where('payment_status', 'completed')
                                    ->sum('payment_amount') ?? 0;

                $newCustomerDue = max(0,
                    floatval($ordersTotal)
                    - floatval($ordersPaid)
                    - floatval($paymentsPaid)
                );

                $affectedCustomer->update(['due' => $newCustomerDue, 'updated_at' => now()]);
            }

            DB::commit();

            return redirect()->route('pending.due')->with([
                'message'    => '✅ بڕی دێتە پشتەوە بە سەرکەوتی نوێ کرایەوە',
                'alert-type' => 'success',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with([
                'message'    => 'هەڵە: ' . $e->getMessage(),
                'alert-type' => 'danger',
            ]);
        }
    }

    /**
     * Cancel order and restore stock + recalculate customer balance
     */
    public function cancelOrder(Request $request)
    {
        $request->validate([
            'order_id'       => 'required|exists:orders,id',
            'rejected_items' => 'required|array',
            'refund_from'    => 'required|in:due,paid',
        ]);

        $order    = Order::findOrFail($request->order_id);
        $customer = $order->customer;

        if (!$customer) {
            return redirect()->back()->with([
                'message'    => 'هەڵەیەک روویدا: کڕیار نەدۆزرایەوە',
                'alert-type' => 'danger',
            ]);
        }

        $rejectedItemIds = $request->rejected_items;
        $refundAmount    = floatval($request->refund_amount ?? 0);

        DB::beginTransaction();
        try {
            // Calculate refund value from rejected items if not manually provided
            if ($refundAmount == 0) {
                $refundAmount = Orderdetails::whereIn('id', $rejectedItemIds)
                    ->selectRaw('SUM(quantity * unitcost) as total')
                    ->value('total') ?? 0;
            }

            $totalQuantityRestored = 0;

            // Restore product stock for each cancelled item
            $quantities = Orderdetails::whereIn('id', $rejectedItemIds)
                ->select('product_id', DB::raw('SUM(quantity) as total_qty'))
                ->groupBy('product_id')
                ->get();

            foreach ($quantities as $item) {
                Product::where('id', $item->product_id)
                    ->update(['product_store' => DB::raw("product_store + " . (int) $item->total_qty)]);

                $totalQuantityRestored += $item->total_qty;
            }

            // Zero-out the cancelled order details
            Orderdetails::whereIn('id', $rejectedItemIds)
                ->update(['quantity' => 0, 'updated_at' => now()]);

            // Mark order as cancelled FIRST so the recalculation below excludes it
            $order->update([
                'order_status'   => 'cancelled',
                'payment_status' => 'cancelled',
                'updated_at'     => now(),
            ]);

            // Recalculate customer due from scratch (cancelled order is now excluded)
            $ordersTotal  = $customer->orders()->where('order_status', '!=', 'cancelled')->sum('sub_total') ?? 0;
            $ordersPaid   = $customer->orders()->where('order_status', '!=', 'cancelled')->sum('pay') ?? 0;
            $paymentsPaid = Payment::where('customer_id', $customer->id)
                                ->where('payment_status', 'completed')
                                ->sum('payment_amount') ?? 0;

            $totalPaidAll = floatval($ordersPaid) + floatval($paymentsPaid);
            $totalDue     = max(floatval($ordersTotal) - $totalPaidAll, 0);

            $customer->update([
                'due'        => $totalDue,
                'total_paid' => $totalPaidAll,
                'updated_at' => now(),
            ]);

            DB::commit();

            return redirect()->back()->with([
                'message'    => "✅ داواکاری بە سەرکەوتی لابرێت - {$totalQuantityRestored} بڕ و {$refundAmount} $ گێڕایەوە",
                'alert-type' => 'success',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with([
                'message'    => 'هەڵەیەک روویدا: ' . $e->getMessage(),
                'alert-type' => 'danger',
            ]);
        }
    }
}