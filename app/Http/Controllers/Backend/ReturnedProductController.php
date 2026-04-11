<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Orderdetails;
use App\Models\Product;
use App\Models\ReturnedProduct;
use App\Models\ReturnedItem;
use Illuminate\Http\Request;
use DB;

class ReturnedProductController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status');
        $query = ReturnedProduct::with(['customer', 'returnedItems.product']);

        if ($status) {
            $query->where('status', $status);
        }

        $returns = $query->orderBy('created_at', 'DESC')->paginate(15);
        return view('backend.returned.index', compact('returns', 'status'));
    }

    public function create()
    {
        // Get all completed orders
        $orders = Order::where('order_status', 'complete')
            ->with('customer')
            ->orderBy('order_date', 'DESC')
            ->get();

        return view('backend.returned.create', compact('orders'));
    }

    // Get items from order for AJAX
    public function getOrderItems($orderId)
    {
        $order = Order::find($orderId);
        if (!$order) {
            return response()->json([], 404);
        }

        $items = Orderdetails::where('order_id', $orderId)
            ->with('product')
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->id,
                    'order_id' => $item->order_id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->product_name ?? 'Unknown',
                    'quantity' => $item->quantity,
                    'meters' => floatval($item->meters),
                    'unitcost' => floatval($item->unitcost),
                    'selected_colors' => json_decode($item->selected_colors, true) ?? [],
                ];
            });

        return response()->json($items);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'order_id' => 'required|exists:orders,id',
            'return_date' => 'required|date',
            'return_reason' => 'required|string',
            'refund_amount' => 'required|numeric|min:0',
            'returned_items' => 'required|array',
        ]);

        DB::beginTransaction();

        try {
            // Create return record
            $return = ReturnedProduct::create([
                'customer_id' => $validated['customer_id'],
                'order_id' => $validated['order_id'],
                'return_date' => $validated['return_date'],
                'return_reason' => $validated['return_reason'],
                'refund_amount' => $validated['refund_amount'],
                'status' => 'pending',
            ]);

            // Add returned items
            foreach ($validated['returned_items'] as $itemData) {
                if (isset($itemData['product_id']) && $itemData['product_id']) {
                    ReturnedItem::create([
                        'returned_product_id' => $return->id,
                        'product_id' => $itemData['product_id'],
                        'quantity_returned' => $itemData['quantity_returned'] ?? 1,
                        'meters_returned' => $itemData['meters_returned'] ?? 0,
                        'refund_price' => $itemData['refund_price'] ?? 0,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('returned.index')
                ->with('message', 'بەرگەڕاندن تۆمار کرا بە سەرکەوتی');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'خۆیەتی: ' . $e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        $return = ReturnedProduct::with(['customer', 'returnedItems.product'])->find($id);

        if (!$return) {
            return redirect()->route('returned.index')->with('error', 'نەدۆزرایەوە');
        }

        return view('backend.returned.show', compact('return'));
    }

    public function approve($id)
    {
        $return = ReturnedProduct::with(['returnedItems', 'customer'])->find($id);

        if (!$return || $return->status !== 'pending') {
            return back()->with('error', 'ناتوانیت ئەم کردارە بکەی');
        }

        DB::beginTransaction();

        try {
            // Restore inventory
            foreach ($return->returnedItems as $item) {
                $product = Product::find($item->product_id);
                if ($product) {
                    $product->increment('product_store', 1);
                }
            }

            // Reduce customer due
            $return->customer->decrement('due', $return->refund_amount);

            // Mark as approved
            $return->update(['status' => 'approved']);

            DB::commit();

            return back()->with('message', 'پەسەند کرا - پاشگەزی بە کڕیار دا');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'خۆیەتی: ' . $e->getMessage());
        }
    }

    public function reject($id)
    {
        $return = ReturnedProduct::find($id);

        if (!$return || $return->status !== 'pending') {
            return back()->with('error', 'ناتوانیت ئەم کردارە بکەی');
        }

        $return->update(['status' => 'rejected']);
        return back()->with('message', 'ڕێت کرا');
    }
}