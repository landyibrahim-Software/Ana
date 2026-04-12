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
        // Load ALL products with relationships
        $allProducts = Product::with('category', 'code', 'colors')
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

        // ALWAYS add/update color data if sent
        if ($request->has('color_data') && !empty($request->color_data)) {
            $colorData = json_decode($request->color_data, true);
            
            if (is_array($colorData)) {
                $options['selected_colors'] = $colorData['selected_colors'] ?? [];
                $options['total_meters'] = $colorData['total_meters'] ?? 0;
            }
        } else {
            // If no color data sent, preserve existing color data
            if (isset($item->options['selected_colors'])) {
                $options['selected_colors'] = $item->options['selected_colors'];
            }
            if (isset($item->options['total_meters'])) {
                $options['total_meters'] = $item->options['total_meters'];
            }
        }

        // Get the new price from the request, or keep the old one
        $newPrice = $request->has('price') ? floatval($request->price) : $item->price;

        // Update cart - with NEW PRICE support
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

    // Calculate total from cart with meters
    $subTotal = 0;
    
    foreach ($contents as $item) {
        $totalMeters = $item->options['total_meters'] ?? 0;
        $subTotal += $totalMeters * $item->price;
    }

    // ✅ FIX: Use customer's current due directly from database
    // This already accounts for all previous orders, payments, and cancellations
    $previousDue = floatval($customer->due ?? 0);

    // Return product_invoice WITHOUT creating order
    return view('backend.invoice.product_invoice', compact('contents', 'customer', 'previousDue', 'subTotal'));
}
}