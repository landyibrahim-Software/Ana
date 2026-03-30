<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Customer;
use Gloudemans\Shoppingcart\Facades\Cart;

class PosController extends Controller
{
    /* ===============================
        POS PAGE
    ================================ */
    public function Pos()
    {
        $product  = Product::latest()->get();
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

    // 🔥 MERGE old options
    $options = $item->options->toArray();

    Cart::update($rowId, [
        'qty' => $request->qty,
        'options' => $options
    ]);

    return redirect()->back()->with([
        'message' => 'Cart Updated Successfully',
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

        foreach ($contents as $item) {
            $product = Product::find($item->id);

            if (!$product) {
                return redirect()->back()->with([
                    'message' => 'Product not found',
                    'alert-type' => 'error'
                ]);
            }

            if ($item->qty > $product->product_store) {
                return redirect()->back()->with([
                    'message' => 'Not enough stock for ' . $product->product_name,
                    'alert-type' => 'error'
                ]);
            }

            // Reduce stock
            $product->product_store -= $item->qty;
            $product->save();
        }

        // IMPORTANT: destroy AFTER everything
        Cart::destroy();

        return view('backend.invoice.product_invoice', compact('contents', 'customer'));
    }
}
