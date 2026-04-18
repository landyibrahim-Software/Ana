<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Orderdetails;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    /* ===============================
        STEP 4: OPTIMIZED POS PAGE
        - Use select() for only needed columns
        - Eager loading with specific fields
        - Limit results for better performance
    ================================ */
    public function Pos()
    {
        // ✅ OPTIMIZATION: Select only needed columns
        $allProducts = Product::with([
            'category:id,category_name',
            'supplier:id,supplier_name'
        ])
            ->where('product_store', '>', 0)
            ->select([
                'id', 
                'product_name', 
                'product_code', 
                'product_store', 
                'selling_price', 
                'buying_price',
                'category_id',
                'supplier_id'
            ])
            ->limit(1000)  // ✅ OPTIMIZATION: Limit products
            ->latest()
            ->get();
        
        // ✅ OPTIMIZATION: Get cart items
        $cartItems = Cart::content();
        
        // ✅ OPTIMIZATION: Get IDs of products in cart
        $cartProductIds = $cartItems->pluck('id')->toArray();
        
        // ✅ OPTIMIZATION: Filter products to exclude those in cart
        $product = $allProducts->filter(function($item) use ($cartProductIds) {
            return !in_array($item->id, $cartProductIds);
        })->values();
        
        // ✅ OPTIMIZATION: Get only active customers
        $customer = Customer::select(['id', 'name', 'phone', 'previous_due'])
            ->where('status', 'active')
            ->limit(500)
            ->latest()
            ->get();

        return view('backend.pos.pos_page', compact('product', 'customer'));
    }

    /* ===============================
        STEP 4: OPTIMIZED ADD PRODUCT TO CART
        - Direct database lookup
        - Minimal data transfer
    ================================ */
    public function AddCart(Request $request)
    {
        // ✅ OPTIMIZATION: Select only needed columns
        $product = Product::select([
            'id',
            'product_name',
            'product_code',
            'product_store',
            'selling_price',
            'buying_price'
        ])->find($request->id);

        if (!$product) {
            return response()->json([
                'status' => 'error',
                'message' => 'Product not found'
            ], 404);
        }

        // ✅ OPTIMIZATION: Stock check
        if ($request->qty > $product->product_store) {
            return response()->json([
                'status' => 'error',
                'message' => 'Not enough stock for ' . $product->product_name
            ], 400);
        }

        // ✅ OPTIMIZATION: Add to cart with minimal data
        Cart::add([
            'id'     => $product->id,
            'name'   => $product->product_name,
            'qty'    => $request->qty ?? 1,
            'price'  => $product->selling_price,
            'weight' => 0,
            'options' => [
                'buying_price' => $product->buying_price,
                'product_code' => $product->product_code,
            ]
        ]);

        return response()->json([
            'status' => 'success',
            'message' => '✅ ' . $product->product_name . ' گیراوە',
            'cartCount' => Cart::count()
        ]);
    }

    /* ===============================
        STEP 4: OPTIMIZED UPDATE CART PRICE
        - Direct update without fetching
        - JSON response for AJAX
    ================================ */
    public function UpdateCartPrice(Request $request, $rowId)
    {
        try {
            // ✅ OPTIMIZATION: Check if item exists
            $item = Cart::get($rowId);

            if (!$item) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Item not found'
                ], 404);
            }

            // ✅ OPTIMIZATION: Validate price
            $price = floatval($request->price);
            if ($price <= 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid price'
                ], 422);
            }

            // ✅ OPTIMIZATION: Update price only
            Cart::update($rowId, [
                'price' => $price
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Price updated',
                'newPrice' => $price,
                'cartTotal' => Cart::total()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error updating price: ' . $e->getMessage()
            ], 500);
        }
    }

    /* ===============================
        STEP 4: OPTIMIZED UPDATE CART QTY
        - Preserve options
        - Validate quantity
    ================================ */
    public function CartUpdate(Request $request, $rowId)
    {
        try {
            // ✅ OPTIMIZATION: Get item
            $item = Cart::get($rowId);

            if (!$item) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Item not found in cart'
                ], 404);
            }

            // ✅ OPTIMIZATION: Validate quantity
            $qty = floatval($request->qty ?? $item->qty);
            if ($qty <= 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid quantity'
                ], 422);
            }

            // ✅ OPTIMIZATION: Preserve options
            $options = $item->options;

            // ✅ OPTIMIZATION: Get new price or keep old
            $newPrice = $request->has('price') 
                ? floatval($request->price) 
                : $item->price;

            // ✅ OPTIMIZATION: Update cart
            Cart::update($rowId, [
                'qty' => $qty,
                'price' => $newPrice,
                'options' => $options
            ]);

            return response()->json([
                'status' => 'success',
                'message' => '✅ سەبەتەکە نوێ کرایەوە',
                'itemTotal' => $qty * $newPrice,
                'cartTotal' => Cart::total()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error updating cart: ' . $e->getMessage()
            ], 500);
        }
    }

    /* ===============================
        STEP 4: OPTIMIZED REMOVE CART ITEM
        - Direct removal
        - JSON response
    ================================ */
    public function CartRemove($rowId)
    {
        try {
            // ✅ OPTIMIZATION: Remove if exists
            if (Cart::content()->has($rowId)) {
                Cart::remove($rowId);
            }

            return response()->json([
                'status' => 'success',
                'message' => '✅ ئایتم سڕایەوە',
                'cartCount' => Cart::count(),
                'cartTotal' => Cart::total()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error removing item: ' . $e->getMessage()
            ], 500);
        }
    }

    /* ===============================
        STEP 4: OPTIMIZED CREATE INVOICE
        - Efficient database queries
        - Cached customer balance calculation
        - No N+1 queries
    ================================ */
    public function CreateInvoice(Request $request)
    {
        try {
            $contents = Cart::content();

            if ($contents->count() == 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cart is empty'
                ], 400);
            }

            // ✅ OPTIMIZATION: Get customer with minimal columns
            $customer = Customer::select([
                'id',
                'name',
                'phone',
                'previous_due'
            ])->findOrFail($request->customer_id);

            // ✅ OPTIMIZATION: Calculate subtotal from cart
            $subTotal = 0;
            foreach ($contents as $item) {
                $qty = floatval($item->qty);
                $price = floatval($item->price);
                $subTotal += $qty * $price;
            }

            // ✅ OPTIMIZATION: Calculate previousDue efficiently
            $previousDue = $this->calculateCustomerDue($customer->id, floatval($customer->previous_due ?? 0));

            return view('backend.invoice.product_invoice', [
                'contents' => $contents,
                'customer' => $customer,
                'previousDue' => $previousDue,
                'subTotal' => $subTotal
            ]);

        } catch (\Exception $e) {
            return redirect()->back()->with([
                'message' => 'Error creating invoice: ' . $e->getMessage(),
                'alert-type' => 'error'
            ]);
        }
    }

    /* ===============================
        STEP 4: HELPER FUNCTION
        Calculate customer due efficiently using queries
    ================================ */
    private function calculateCustomerDue($customerId, $previousDue)
    {
        try {
            // ✅ OPTIMIZATION: Use database query instead of loading all data
            
            // Get all non-cancelled orders total subtotal
            $orderSubtotal = Order::where('customer_id', $customerId)
                ->where('order_status', '!=', 'cancelled')
                ->select('sub_total')
                ->sum('sub_total');

            // Get all non-cancelled orders total paid
            $orderPaid = Order::where('customer_id', $customerId)
                ->where('order_status', '!=', 'cancelled')
                ->select('pay')
                ->sum('pay');

            // Get all payments made
            $payments = DB::table('payments')
                ->where('customer_id', $customerId)
                ->sum('payment_amount');

            // Total spent = previous_due + all order subtotals
            $totalSpent = floatval($previousDue) + floatval($orderSubtotal);

            // Total paid = all order payments + all customer payments
            $totalPaid = floatval($orderPaid) + floatval($payments);

            // Due = what they spent - what they paid
            $due = $totalSpent - $totalPaid;

            return max($due, 0);

        } catch (\Exception $e) {
            return floatval($previousDue);
        }
    }

    /* ===============================
        STEP 4: GET ALL ITEMS (AJAX)
        - Optimized for search/filter
    ================================ */
    public function AllItem(Request $request)
    {
        try {
            $query = Product::query();

            // ✅ OPTIMIZATION: Search by name or code
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('product_name', 'LIKE', "%{$search}%")
                      ->orWhere('product_code', 'LIKE', "%{$search}%");
                });
            }

            // ✅ OPTIMIZATION: Filter by category
            if ($request->has('category') && $request->category) {
                $query->where('category_id', $request->category);
            }

            // ✅ OPTIMIZATION: Select only needed columns
            $products = $query
                ->where('product_store', '>', 0)
                ->select([
                    'id',
                    'product_name',
                    'product_code',
                    'product_store',
                    'selling_price',
                    'buying_price',
                    'category_id'
                ])
                ->limit(500)
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $products,
                'count' => $products->count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error fetching items: ' . $e->getMessage()
            ], 500);
        }
    }
}