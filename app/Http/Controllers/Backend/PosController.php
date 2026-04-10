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
        // Load products with relationships to avoid null issues
        $product  = Product::with('category', 'code', 'colors')
                           ->where('product_store', '>', 0)
                           ->latest()
                           ->get();
        
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
            'message' => 'Product added to cart',
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

    // Update cart - THIS IS THE KEY PART
    Cart::update($rowId, [
        'qty' => $request->qty ?? $item->qty,
        'price' => $item->price, // Don't change price
        'options' => $options    // Keep all options
    ]);

    return redirect()->back()->with([
        'message' => '✅ Cart Updated Successfully',
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
            'message' => 'Cart item removed',
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
    $totalMetersAll = 0;
    
    foreach ($contents as $item) {
        $totalMeters = $item->options['total_meters'] ?? 0;
        $subTotal += $totalMeters * $item->price;
        $totalMetersAll += $totalMeters;
    }

    // Add previous due
    $previousDue = $customer->due + $customer->previous_due;
    $grandTotal = $subTotal + $previousDue;

    // Create Order
    $order = Order::create([
        'customer_id'    => $customer->id,
        'order_date'     => now()->format('Y-m-d'),
        'total_products' => $contents->count(),
        'sub_total'      => $subTotal,
        'invoice_no'     => 'INV-' . time(),
        'total'          => $grandTotal,
        'payment_status' => 'pending',
        'pay'            => 0,
        'due'            => $grandTotal,
        'order_status'   => 'pending',
        'metter_price'   => 0,
    ]);

    // Create Order Details with color info
    foreach ($contents as $item) {
        $product = Product::find($item->id);

        if (!$product) {
            return redirect()->back()->with([
                'message' => 'Product not found: ' . $item->name,
                'alert-type' => 'error'
            ]);
        }

        if ($item->qty > $product->product_store) {
            return redirect()->back()->with([
                'message' => 'Not enough stock for ' . $product->product_name,
                'alert-type' => 'error'
            ]);
        }

        $totalMeters = $item->options['total_meters'] ?? 0;
        $selectedColors = json_encode($item->options['selected_colors'] ?? []);
        $itemTotal = $totalMeters * $item->price;

        Orderdetails::create([
            'order_id'        => $order->id,
            'product_id'      => $product->id,
            'quantity'        => $item->qty,
            'unitcost'        => $item->price,
            'meters'          => $totalMeters,
            'selected_colors' => $selectedColors,
            'metter_price'    => 0,
            'total'           => $itemTotal,
        ]);

        // Reduce stock
        $product->product_store -= $item->qty;
        $product->save();
    
    }

    // Clear cart
    Cart::destroy();

    return view('backend.invoice.product_invoice', compact('order', 'contents', 'customer'));
}
}