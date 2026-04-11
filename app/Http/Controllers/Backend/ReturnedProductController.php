<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ReturnedProduct;
use App\Models\ReturnedItem;
use App\Models\Product;
use App\Models\Customer;
use App\Models\BankBalance;
use App\Models\BankTransaction;
use Illuminate\Http\Request;
use DB;

class ReturnedProductController extends Controller
{
    /**
     * Show all returned products
     */
    public function index(Request $request)
    {
        $status = $request->get('status');
        
        $query = ReturnedProduct::with(['customer', 'order', 'returnedItems']);

        if ($status) {
            $query->where('status', $status);
        }

        $returns = $query->orderBy('return_date', 'DESC')->paginate(20);

        return view('backend.returned.index', compact('returns', 'status'));
    }

    /**
     * Show form to create new return
     */
    public function create()
    {
        // Get only completed orders that haven't been fully returned
        $orders = Order::where('order_status', 'complete')
            ->with(['customer', 'orderItems.product'])
            ->orderBy('created_at', 'DESC')
            ->get();

        return view('backend.returned.create', compact('orders'));
    }

    /**
     * Get order items for selected order (AJAX)
     */
    public function getOrderItems($orderId)
    {
        $order = Order::with(['customer', 'orderItems.product'])->find($orderId);

        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        $customer = $order->customer;
        $items = $order->orderItems->map(function ($item) {
            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product->product_name ?? 'Unknown',
                'quantity' => $item->quantity,
                'meters' => $item->meters ?? 0,
                'unitcost' => $item->unitcost,
                'selected_colors' => $item->selected_colors,
            ];
        });

        return response()->json([
            'order' => [
                'invoice_no' => $order->invoice_no,
                'customer_id' => $order->customer_id,
                'customer_name' => $customer->name,
                'total_paid' => $order->pay,
                'total_amount' => $order->total,
            ],
            'items' => $items
        ]);
    }

    /**
     * Store return
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'customer_id' => 'required|exists:customers,id',
            'return_date' => 'required|date',
            'return_reason' => 'required|string|min:5',
            'refund_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'returned_items' => 'required|array|min:1',
            'returned_items.*.product_id' => 'required|exists:products,id',
            'returned_items.*.quantity_returned' => 'required|numeric|min:1',
            'returned_items.*.meters_returned' => 'nullable|numeric|min:0',
            'returned_items.*.refund_price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            // Create return record
            $return = ReturnedProduct::create([
                'order_id' => $validated['order_id'],
                'customer_id' => $validated['customer_id'],
                'return_date' => $validated['return_date'],
                'return_reason' => $validated['return_reason'],
                'refund_amount' => $validated['refund_amount'],
                'notes' => $validated['notes'],
                'status' => 'pending',
            ]);

            // Create returned items
            foreach ($validated['returned_items'] as $item) {
                ReturnedItem::create([
                    'returned_product_id' => $return->id,
                    'product_id' => $item['product_id'],
                    'quantity_returned' => $item['quantity_returned'],
                    'meters_returned' => $item['meters_returned'] ?? null,
                    'original_price' => 0,
                    'refund_price' => $item['refund_price'],
                ]);
            }

            DB::commit();

            return redirect()->route('returned.index')
                ->with(['message' => 'بەرگەڕاندن تۆمار کرا بە سەرکەوتی', 'alert-type' => 'success']);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with(['message' => 'هەڵەیەک روویدا: ' . $e->getMessage(), 'alert-type' => 'danger'])
                ->withInput();
        }
    }

    /**
     * Show return details
     */
    public function show($id)
    {
        $return = ReturnedProduct::with(['customer', 'order', 'returnedItems.product'])->find($id);

        if (!$return) {
            return redirect()->route('returned.index')->with(['message' => 'نەدۆزرایەوە', 'alert-type' => 'danger']);
        }

        return view('backend.returned.show', compact('return'));
    }

    /**
     * Approve return - Restore inventory & refund customer
     */
    public function approve($id)
    {
        $return = ReturnedProduct::with(['customer', 'order', 'returnedItems'])->find($id);

        if (!$return) {
            return redirect()->back()->with(['message' => 'نەدۆزرایەوە', 'alert-type' => 'danger']);
        }

        if ($return->status !== 'pending') {
            return redirect()->back()->with(['message' => 'دۆخی بەرگەڕاندن ناتوانیت بگۆڕی', 'alert-type' => 'warning']);
        }

        DB::beginTransaction();

        try {
            // 1. Restore inventory for all returned items
            foreach ($return->returnedItems as $item) {
                $product = Product::find($item->product_id);
                if ($product) {
                    // Increase quantity back
                    $product->increment('product_store', $item->quantity_returned);
                }
            }

            // 2. Update customer due (reduce by refund amount)
            $customer = Customer::find($return->customer_id);
            $customer->decrement('due', $return->refund_amount);

            // 3. Update order
            $order = Order::find($return->order_id);
            $order->total_returned += $return->refund_amount;
            $order->refund_status = 'partial';
            $order->save();

            // 4. Add to Bank as spend (refund transaction)
            if ($return->refund_amount > 0) {
                $currentBalance = BankBalance::getCurrentBalance();
                $newBalance = $currentBalance - $return->refund_amount;

                BankTransaction::create([
                    'transaction_type' => 'spend',
                    'amount' => $return->refund_amount,
                    'description' => 'بەرگەڕاندنی پاشگەزی - پسوڵە #' . $order->invoice_no,
                    'balance_after' => $newBalance,
                    'transaction_date' => now()->toDateString(),
                ]);

                BankBalance::updateBalance($newBalance);
            }

            // 5. Update return status
            $return->update(['status' => 'approved']);

            DB::commit();

            return redirect()->back()->with(['message' => 'بەرگەڕاندن پەسەند کرا - پاشگەزی بە کڕیار دا', 'alert-type' => 'success']);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with(['message' => 'هەڵە: ' . $e->getMessage(), 'alert-type' => 'danger']);
        }
    }

    /**
     * Reject return
     */
    public function reject($id)
    {
        $return = ReturnedProduct::find($id);

        if (!$return) {
            return redirect()->back()->with(['message' => 'نەدۆزرایەوە', 'alert-type' => 'danger']);
        }

        try {
            $return->update(['status' => 'rejected']);
            return redirect()->back()->with(['message' => 'بەرگەڕاندن ڕێت کرا', 'alert-type' => 'warning']);
        } catch (\Exception $e) {
            return redirect()->back()->with(['message' => 'هەڵە: ' . $e->getMessage(), 'alert-type' => 'danger']);
        }
    }
}