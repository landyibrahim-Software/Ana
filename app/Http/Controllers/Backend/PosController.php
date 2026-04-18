<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Customer;
use Cart;

public function Pos()
{
    try {
        // ✅ Load products with category
        $product = Product::with('category:id,category_name')
            ->where('product_store', '>', 0)
            ->select([
                'id', 
                'product_name', 
                'product_code', 
                'product_store', 
                'selling_price', 
                'buying_price',
                'category_id',
                'product_image'
            ])
            ->latest()
            ->get();
        
        $customer = Customer::select(['id', 'name', 'phone', 'previous_due'])
            ->latest()
            ->get();

        return view('backend.pos.pos_page', compact('product', 'customer'));

    } catch (\Exception $e) {
        return redirect()->back()->with([
            'message' => 'Error: ' . $e->getMessage(),
            'alert-type' => 'error'
        ]);
    }
}

    public function AddCart(Request $request)
    {
        try {
            $product = Product::find($request->id);

            if (!$product) {
                return redirect()->back()->with([
                    'message' => 'Product not found',
                    'alert-type' => 'error'
                ]);
            }

            if ($request->qty > $product->product_store) {
                return redirect()->back()->with([
                    'message' => 'Not enough stock',
                    'alert-type' => 'error'
                ]);
            }

            Cart::add([
                'id' => $product->id,
                'name' => $product->product_name,
                'qty' => $request->qty ?? 1,
                'price' => $product->selling_price,
                'weight' => 0,
                'options' => [
                    'buying_price' => $product->buying_price,
                ]
            ]);

            return redirect()->back()->with([
                'message' => '✅ ' . $product->product_name . ' گیراوە',
                'alert-type' => 'success'
            ]);

        } catch (\Exception $e) {
            return redirect()->back()->with([
                'message' => 'Error: ' . $e->getMessage(),
                'alert-type' => 'error'
            ]);
        }
    }

    public function UpdateCartPrice(Request $request, $rowId)
    {
        try {
            $item = Cart::get($rowId);
            if (!$item) {
                return response()->json(['status' => 'error'], 404);
            }

            Cart::update($rowId, ['price' => floatval($request->price)]);
            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function CartUpdate(Request $request, $rowId)
    {
        try {
            $item = Cart::get($rowId);
            if (!$item) {
                return redirect()->back()->with(['message' => 'Item not found', 'alert-type' => 'error']);
            }

            $options = $item->options ?? [];
            $price = $request->has('price') ? floatval($request->price) : $item->price;

            Cart::update($rowId, [
                'qty' => floatval($request->qty ?? $item->qty),
                'price' => $price,
                'options' => $options
            ]);

            return redirect()->back()->with([
                'message' => '✅ سەبەتەکە نوێ کرایەوە',
                'alert-type' => 'success'
            ]);

        } catch (\Exception $e) {
            return redirect()->back()->with(['message' => 'Error: ' . $e->getMessage(), 'alert-type' => 'error']);
        }
    }

    public function CartRemove($rowId)
    {
        try {
            if (Cart::content()->has($rowId)) {
                Cart::remove($rowId);
            }
            return redirect()->back()->with([
                'message' => '✅ ئایتم سڕایەوە',
                'alert-type' => 'success'
            ]);

        } catch (\Exception $e) {
            return redirect()->back()->with(['message' => 'Error: ' . $e->getMessage(), 'alert-type' => 'error']);
        }
    }

    public function CreateInvoice(Request $request)
    {
        try {
            $contents = Cart::content();

            if ($contents->count() == 0) {
                return redirect()->back()->with([
                    'message' => 'Cart is empty',
                    'alert-type' => 'error'
                ]);
            }

            $customer = Customer::find($request->customer_id);

            $subTotal = 0;
            foreach ($contents as $item) {
                $subTotal += floatval($item->qty) * floatval($item->price);
            }

            return view('backend.invoice.product_invoice', [
                'contents' => $contents,
                'customer' => $customer,
                'previousDue' => floatval($customer->previous_due ?? 0),
                'subTotal' => $subTotal
            ]);

        } catch (\Exception $e) {
            return redirect()->back()->with([
                'message' => 'Error: ' . $e->getMessage(),
                'alert-type' => 'error'
            ]);
        }
    }

    public function AllItem(Request $request)
    {
        try {
            $products = Product::where('product_store', '>', 0)
                ->select(['id', 'product_name', 'product_code', 'product_store', 'selling_price'])
                ->get();

            return response()->json(['status' => 'success', 'data' => $products]);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}