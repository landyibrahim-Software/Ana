<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Orderdetails;
use App\Models\Product;
use App\Models\ReturnedProduct;
use App\Models\ReturnedItem;
use Illuminate\Http\Request;
use DB;

class ReturnedProductController extends Controller
{
    // Show all returns
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

    // Show create form
    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        return view('backend.returned.create', compact('customers'));
    }

    // Get customer orders (AJAX) - Shows purchase history
    public function getCustomerOrders($customerId)
    {
        $orderItems = Orderdetails::whereHas('order', function($q) use ($customerId) {
            $q->where('customer_id', $customerId)
              ->where('order_status', 'complete');
        })
        ->with(['product', 'order'])
        ->get()
        ->map(function($item) {
            $colors = [];
            if ($item->selected_colors) {
                $colorsData = json_decode($item->selected_colors, true);
                if (is_array($colorsData)) {
                    foreach ($colorsData as $color) {
                        $colors[] = [
                            'name' => $color['name'] ?? 'Unknown',
                            'meter' => (float)($color['meter'] ?? 0),
                        ];
                    }
                }
            }

            return [
                'id' => $item->id,
                'order_id' => $item->order_id,
                'product_id' => $item->product_id,
                'product_name' => $item->product->product_name ?? 'Unknown',
                'product_code' => $item->product->product_code ?? '',
                'product_image' => $item->product->product_image ?? 'images/default.jpg',
                'quantity' => $item->quantity ?? 1,
                'meters' => (float)($item->meters ?? 0),
                'unitcost' => (float)($item->unitcost ?? 0),
                'colors' => $colors,
                'invoice_no' => $item->order->invoice_no ?? 'N/A',
                'order_date' => $item->order->order_date ?? '',
            ];
        });

        return response()->json($orderItems);
    }

    // Store return
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'return_reason' => 'nullable|string',
            'refund_amount' => 'required|numeric|min:0',
            'returned_items' => 'required|array|min:1',
            'returned_items.*.product_id' => 'required|exists:products,id',
            'returned_items.*.returned_colors' => 'nullable|array',
        ]);

        DB::beginTransaction();

        try {
            $customer = Customer::find($validated['customer_id']);

            // Create return
            $return = ReturnedProduct::create([
                'customer_id' => $customer->id,
                'order_id' => 0,
                'return_date' => now()->toDateString(),
                'return_reason' => $validated['return_reason'] ?? 'بەرگەڕاندن',
                'refund_amount' => $validated['refund_amount'],
                'status' => 'pending',
            ]);

            // Process each returned item
            foreach ($validated['returned_items'] as $index => $item) {
                $returnedColors = $item['returned_colors'] ?? [];
                $totalReturnedMeters = 0;

                // Calculate total meters from colors
                foreach ($returnedColors as $colorName => $meters) {
                    if ($meters > 0) {
                        $totalReturnedMeters += (float)$meters;
                    }
                }

                if ($totalReturnedMeters > 0) {
                    ReturnedItem::create([
                        'returned_product_id' => $return->id,
                        'product_id' => $item['product_id'],
                        'quantity_returned' => 1,
                        'meters_returned' => $totalReturnedMeters,
                        'refund_price' => $validated['refund_amount'],
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('returned.index')
                ->with('message', 'بەرگەڕاندن تۆمار کرا بە سەرکەوتی');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'خۆیەتی: ' . $e->getMessage());
        }
    }

    // Show return details
    public function show($id)
    {
        $return = ReturnedProduct::with(['customer', 'returnedItems.product'])->find($id);
        
        if (!$return) {
            return redirect()->route('returned.index')->with('error', 'نەدۆزرایەوە');
        }

        return view('backend.returned.show', compact('return'));
    }

    // Approve return
    public function approve($id)
    {
        $return = ReturnedProduct::with(['returnedItems', 'customer'])->find($id);

        if (!$return || $return->status !== 'pending') {
            return back()->with('error', 'ناتوانیت ئەم کردارە بکەی');
        }

        DB::beginTransaction();

        try {
            // 1. Restore inventory
            foreach ($return->returnedItems as $item) {
                $product = Product::find($item->product_id);
                if ($product) {
                    $product->increment('product_store', $item->meters_returned);
                }
            }

            // 2. Refund customer
            $return->customer->decrement('due', $return->refund_amount);

            // 3. Mark as approved
            $return->update(['status' => 'approved']);

            DB::commit();

            return back()->with('message', 'پەسەند کرا - پاشگەزی بە کڕیار دا');
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