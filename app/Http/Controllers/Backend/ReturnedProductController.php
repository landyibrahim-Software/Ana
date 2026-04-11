<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ReturnedProduct;
use App\Models\ReturnedItem;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Http\Request;
use DB;

class ReturnedProductController extends Controller
{
    // Show all returns
    public function index(Request $request)
    {
        $status = $request->get('status');
        $query = ReturnedProduct::with(['customer', 'order']);

        if ($status) {
            $query->where('status', $status);
        }

        $returns = $query->orderBy('created_at', 'DESC')->paginate(15);
        return view('backend.returned.index', compact('returns', 'status'));
    }

    // Show create form
    public function create()
    {
        $orders = Order::where('order_status', 'complete')
            ->with(['customer', 'orderItems.product'])
            ->orderBy('created_at', 'DESC')
            ->get();

        return view('backend.returned.create', compact('orders'));
    }

    // Store return
    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'return_reason' => 'required|string|min:5',
            'refund_amount' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $order = Order::find($validated['order_id']);
            $customer = $order->customer;

            // Create return
            $return = ReturnedProduct::create([
                'order_id' => $order->id,
                'customer_id' => $customer->id,
                'return_date' => now()->toDateString(),
                'return_reason' => $validated['return_reason'],
                'refund_amount' => $validated['refund_amount'],
                'status' => 'pending',
            ]);

            // Create returned items for ALL order items
            foreach ($order->orderItems as $item) {
                ReturnedItem::create([
                    'returned_product_id' => $return->id,
                    'product_id' => $item->product_id,
                    'quantity_returned' => $item->quantity,
                    'meters_returned' => $item->meters,
                    'refund_price' => $item->unitcost * $item->quantity,
                ]);
            }

            DB::commit();

            return redirect()->route('returned.index')
                ->with('message', 'بەرگەڕاندن تۆمار کرا');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'خۆیەتی: ' . $e->getMessage());
        }
    }

    // Show return details
    public function show($id)
    {
        $return = ReturnedProduct::with(['customer', 'order', 'returnedItems.product'])->find($id);
        
        if (!$return) {
            return redirect()->route('returned.index')->with('error', 'نەدۆزرایەوە');
        }

        return view('backend.returned.show', compact('return'));
    }

    // Approve return - restore inventory and refund
    public function approve($id)
    {
        $return = ReturnedProduct::with(['returnedItems', 'customer'])->find($id);

        if (!$return || $return->status !== 'pending') {
            return back()->with('error', 'ناتوانیت ئەم کردارە بکەی');
        }

        DB::beginTransaction();

        try {
            // Restore all products
            foreach ($return->returnedItems as $item) {
                $product = Product::find($item->product_id);
                if ($product) {
                    $product->increment('product_store', $item->quantity_returned);
                }
            }

            // Refund customer
            $return->customer->decrement('due', $return->refund_amount);

            // Update return status
            $return->update(['status' => 'approved']);

            DB::commit();

            return back()->with('message', 'پەسەند کرا - پاشگەزی دا بە کڕیار');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'خۆیەتی: ' . $e->getMessage());
        }
    }

    // Reject return
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