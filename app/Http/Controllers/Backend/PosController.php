<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Orderdetails;
use Gloudemans\Shoppingcart\Facades\Cart;

class PosController extends Controller
{
    /* ===============================
        POS PAGE
    ================================ */
    public function Pos()
    {
        // Load ALL products with relationships (FIXED: removed 'code', 'colors')
        $allProducts = Product::with('category', 'supplier')
                              ->where('product_store', '>', 0)
                              ->latest()
                              ->get();
        
        // Get cart items
        $cartItems = Cart::content();
        
        // Get IDs of products in cart
        $cartProductIds = $cartItems->pluck('id')->toArray();
        
        // Filter products to exclude those in cart
        $product = $allProducts->filter(function($item) use ($cartProductIds) {
            return !in_array($item->id, $cartProductIds);
        })->values(); // Reset keys
        
        // Get all customers
        $customer = Customer::latest()->get();

        return view('backend.pos.pos_page', compact('product', 'customer'));
    }

    /* ===============================
        ADD PRODUCT TO CART
    ================================ */
    public function AddCart(Request $request)
    {
        $product = Product::find($request->id);

        if (!$product) {
            return redirect()->back()->with([
                'message' => 'Product not found',
                'alert-type' => 'error'
            ]);
        }

        // Stock check
        if ($request->qty > $product->product_store) {
            return redirect()->back()->with([
                'message' => 'Not enough stock for ' . $product->product_name,
                'alert-type' => 'error'
            ]);
        }

        Cart::add([
            'id'     => $product->id,
            'name'   => $product->product_name,
            'qty'    => $request->qty ?? 1,
            'price'  => $product->selling_price,
            'weight' => 0,
            'options' => [
                'buying_price' => $product->buying_price,
            ]
        ]);

        return redirect()->back()->with([
            'message' => '✅ ' . $product->product_name . ' گیراوە',
            'alert-type' => 'success'
        ]);
    }

    /* ===============================
        UPDATE CART PRICE
    ================================ */
    public function UpdateCartPrice(Request $request, $rowId)
    {
        $item = Cart::get($rowId);

        if (!$item) {
            return response()->json(['status' => 'error'], 404);
        }

        Cart::update($rowId, [
            'price' => (float) $request->price
        ]);

        return response()->json(['status' => 'success']);
    }

    /* ===============================
        UPDATE CART QTY
    ================================ */
    public function CartUpdate(Request $request, $rowId)
    {
        $item = Cart::get($rowId);

        if (!$item) {
            return redirect()->back()->with([
                'message' => 'Item not found in cart.',
                'alert-type' => 'error'
            ]);
        }

        // IMPORTANT: Preserve ALL existing options
        $options = [];
        
        // Keep buying_price (original option)
        if (isset($item->options['buying_price'])) {
            $options['buying_price'] = $item->options['buying_price'];
        }

        // Get the new price from the request, or keep the old one
        $newPrice = $request->has('price') ? floatval($request->price) : $item->price;

        // Update cart - with NEW PRICE support (FIXED: removed color_data handling)
        Cart::update($rowId, [
            'qty' => $request->qty ?? $item->qty,
            'price' => $newPrice,
            'options' => $options
        ]);

        return redirect()->back()->with([
            'message' => '✅ سەبەتەکە نوێ کرایەوە',
            'alert-type' => 'success'
        ]);
    }

    /* ===============================
        REMOVE CART ITEM
    ================================ */
    public function CartRemove($rowId)
    {
        if (Cart::content()->has($rowId)) {
            Cart::remove($rowId);
        }

        return redirect()->back()->with([
            'message' => '✅ ئایتم سڕایەوە',
            'alert-type' => 'success'
        ]);
    }

    /* ===============================
        CREATE INVOICE & UPDATE STOCK
    ================================ */
    public function CreateInvoice(Request $request)
    {
        $contents = Cart::content();
        $customer = Customer::findOrFail($request->customer_id);

        if ($contents->count() == 0) {
            return redirect()->back()->with([
                'message' => 'Cart is empty',
                'alert-type' => 'error'
            ]);
        }

        // Calculate total from cart (FIXED: removed color/meters calculation)
        $subTotal = 0;
        
        foreach ($contents as $item) {
            $qty = $item->qty;
            $subTotal += $qty * $item->price;
        }

        // ✅ Calculate previousDue (what customer owes)
        // Step 1: Get customer's previous due from initial setup
        $total_spent = floatval($customer->previous_due ?? 0);
        
        // Step 2: Add all ACTIVE (non-cancelled) orders' subtotals
        foreach ($customer->orders as $order) {
            if ($order->order_status != 'cancelled') {
                $total_spent += floatval($order->sub_total ?? 0);
            }
        }
        
        // Step 3: Calculate total paid from all ACTIVE orders
        $total_paid_all = floatval(\App\Models\Payment::where('customer_id', $customer->id)->sum('payment_amount') ?? 0);
        foreach ($customer->orders as $order) {
            if ($order->order_status != 'cancelled') {
                $total_paid_all += floatval($order->pay ?? 0);
            }
        }
        
        // Step 4: previousDue = total_spent - total_paid (what customer still owes)
        $previousDue = max($total_spent - $total_paid_all, 0);

        // Return product_invoice WITHOUT creating order
        return view('backend.invoice.product_invoice', compact('contents', 'customer', 'previousDue', 'subTotal'));
    }
}