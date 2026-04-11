<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ReturnedProduct;
use App\Models\ReturnedItem;
use App\Models\Product;
use Illuminate\Http\Request;

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
        // Get only completed orders
        $orders = Order::where('order_status', 'complete')
            ->with('customer')
            ->orderBy('created_at', 'DESC')
            ->get();

        return view('backend.returned.create', compact('orders'));
    }

    /**
     * Get order items for selected order (AJAX)
     */
    public function getOrderItems($orderId)
    {
        $order = Order::with('orderItems.product')->find($orderId);

        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        $items = $order->orderItems->map(function ($item) {
            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product->product_name ?? 'Unknown',
                'quantity' => $item->quantity,
                'meters' => $item->meters ?? 0,
                'unitcost' => $item->unitcost,
            ];
        });

        return response()->json($items);
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
                    'original_price' => 0, // Will be updated
                    'refund_price' => $item['refund_price'],
                ]);
            }

            return redirect()->route('returned.index')
                ->with(['message' => 'بەرگەڕاندن تۆمار کرا بە سەرکەوتی', 'alert-type' => 'success']);
        } catch (\Exception $e) {
            return redirect()->back()
                ->with(['message' => 'هەڵەیەک روویدا: ' . $e->getMessage(), 'alert-type' => 'danger']);
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
     * Approve return
     */
    public function approve($id)
    {
        $return = ReturnedProduct::find($id);

        if (!$return) {
            return redirect()->back()->with(['message' => 'نەدۆزرایەوە', 'alert-type' => 'danger']);
        }

        try {
            $return->approve();
            return redirect()->back()->with(['message' => 'بەرگەڕاندن پەسەند کرا', 'alert-type' => 'success']);
        } catch (\Exception $e) {
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
            $return->reject();
            return redirect()->back()->with(['message' => 'بەرگەڕاندن ڕێت کرا', 'alert-type' => 'danger']);
        } catch (\Exception $e) {
            return redirect()->back()->with(['message' => 'هەڵە: ' . $e->getMessage(), 'alert-type' => 'danger']);
        }
    }
}