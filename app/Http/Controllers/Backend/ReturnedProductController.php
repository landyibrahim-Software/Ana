<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Customer;
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
        $customers = Customer::whereHas('orders', function($q) {
            $q->where('order_status', 'complete');
        })->orderBy('name')->get();

        return view('backend.returned.create', compact('customers'));
    }

    public function getCustomerOrders($customerId)
    {
        $orderItems = Orderdetails::whereHas('order', function($q) use ($customerId) {
            $q->where('customer_id', $customerId)->where('order_status', 'complete');
        })
        ->with(['product', 'order'])
        ->get();

        $items = [];
        foreach ($orderItems as $item) {
            $colors = [];
            if ($item->selected_colors) {
                $colors = json_decode($item->selected_colors, true) ?? [];
            }

            $items[] = [
                'id' => $item->id,
                'order_id' => $item->order_id,
                'product_id' => $item->product_id,
                'product_name' => $item->product->product_name ?? 'Unknown',
                'product_code' => $item->product->product_code ?? '',
                'product_image' => $item->product->product_image ?? '',
                'quantity' => $item->quantity,
                'meters' => floatval($item->meters),
                'unitcost' => floatval($item->unitcost),
                'selected_colors' => $colors,
                'total' => floatval($item->total),
                'invoice_no' => $item->order->invoice_no,
                'order_date' => $item->order->order_date,
            ];
        }

        return response()->json($items);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'return_reason' => 'nullable|string',
            'refund_amount' => 'required|numeric|min:0',
            'returned_items' => 'required|array',
        ]);

        DB::beginTransaction();

        try {
            $customer = Customer::find($validated['customer_id']);

            $return = ReturnedProduct::create([
                'customer_id' => $customer->id,
                'order_id' => 0,
                'return_date' => now()->toDateString(),
                'return_reason' => $validated['return_reason'] ?? 'بەرگەڕاندن',
                'refund_amount' => $validated['refund_amount'],
                'status' => 'pending',
            ]);

            foreach ($validated['returned_items'] as $itemData) {
                if (isset($itemData['returned_colors']) && is_array($itemData['returned_colors'])) {
                    $totalReturnedMeters = 0;

                    foreach ($itemData['returned_colors'] as $colorName => $metersReturned) {
                        if ($metersReturned > 0) {
                            $totalReturnedMeters += floatval($metersReturned);
                        }
                    }

                    if ($totalReturnedMeters > 0) {
                        ReturnedItem::create([
                            'returned_product_id' => $return->id,
                            'product_id' => $itemData['product_id'],
                            'quantity_returned' => 1,
                            'meters_returned' => $totalReturnedMeters,
                            'refund_price' => $validated['refund_amount'],
                        ]);
                    }
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
            foreach ($return->returnedItems as $item) {
                $product = Product::find($item->product_id);
                if ($product) {
                    $product->increment('product_store', 1);
                }
            }

            $return->customer->decrement('due', $return->refund_amount);
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