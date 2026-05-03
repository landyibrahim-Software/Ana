<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Customer;
use Gloudemans\Shoppingcart\Facades\Cart;

class PosController extends Controller
{
    /**
     * Display POS page with paginated products and customers
     */
    public function Pos(Request $request)
    {
        try {
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
                    'product_image',
                    'created_at',
                ])
                ->latest()
                ->paginate(20);

            $customer = Customer::select([
                    'id',
                    'name',
                    'phone',
                    'due',
                    'image',
                    'created_at',
                ])
                ->latest()
                ->paginate(50);

            return view('backend.pos.pos_page', compact('product', 'customer'));

        } catch (\Exception $e) {
            return redirect()->back()->with([
                'message'    => 'خرابی: ' . $e->getMessage(),
                'alert-type' => 'danger',
            ]);
        }
    }

    /**
     * Search products via AJAX
     */
    public function SearchProducts(Request $request)
    {
        try {
            $search = $request->get('search', '');

            $products = Product::where('product_store', '>', 0)
                ->where(function ($query) use ($search) {
                    $query->where('product_name', 'LIKE', "%{$search}%")
                          ->orWhere('product_code', 'LIKE', "%{$search}%");
                })
                ->with('category:id,category_name')
                ->select([
                    'id',
                    'product_name',
                    'product_code',
                    'product_store',
                    'selling_price',
                    'buying_price',
                    'category_id',
                    'product_image',
                ])
                ->limit(20)
                ->get();

            return response()->json([
                'status' => 'success',
                'data'   => $products,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Search customers via AJAX
     */
    public function SearchCustomers(Request $request)
    {
        try {
            $search = $request->get('search', '');

            $customers = Customer::where(function ($query) use ($search) {
                    $query->where('name', 'LIKE', "%{$search}%")
                          ->orWhere('phone', 'LIKE', "%{$search}%");
                })
                ->select([
                    'id',
                    'name',
                    'phone',
                    'due',
                    'image',
                ])
                ->limit(20)
                ->get();

            return response()->json([
                'status' => 'success',
                'data'   => $customers,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Add product to cart
     */
    public function AddCart(Request $request)
    {
        try {
            $request->validate([
                'id'  => 'required|exists:products,id',
                'qty' => 'required|numeric|min:0.01',
            ]);

            $product = Product::select([
                'id',
                'product_name',
                'product_code',
                'product_store',
                'selling_price',
                'buying_price',
            ])->find($request->id);

            if (!$product) {
                return redirect()->back()->with([
                    'message'    => 'بەرهەم نەدۆزرایەوە',
                    'alert-type' => 'error',
                ]);
            }

            if ($request->qty > $product->product_store) {
                return redirect()->back()->with([
                    'message'    => 'عدد کافی نیە - دەستکاری: ' . $product->product_store,
                    'alert-type' => 'error',
                ]);
            }

            Cart::add([
                'id'      => $product->id,
                'name'    => $product->product_name,
                'qty'     => floatval($request->qty ?? 1),
                'price'   => floatval($product->selling_price),
                'weight'  => 0,
                'options' => [
                    'buying_price' => floatval($product->buying_price),
                    'product_code' => $product->product_code,
                ],
            ]);

            return redirect()->back()->with([
                'message'    => '✅ ' . $product->product_name . ' گیراوە',
                'alert-type' => 'success',
            ]);

        } catch (\Exception $e) {
            return redirect()->back()->with([
                'message'    => 'خرابی: ' . $e->getMessage(),
                'alert-type' => 'error',
            ]);
        }
    }

    /**
     * Update cart item price
     */
    public function UpdateCartPrice(Request $request, $rowId)
    {
        try {
            $request->validate([
                'price' => 'required|numeric|min:0',
            ]);

            $item = Cart::get($rowId);
            if (!$item) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Item not found',
                ], 404);
            }

            Cart::update($rowId, [
                'price' => floatval($request->price),
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'نرخ نوێ کرایەوە',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update cart item quantity and price
     */
    public function CartUpdate(Request $request, $rowId)
    {
        try {
            $request->validate([
                'qty'   => 'required|numeric|min:0.01',
                'price' => 'nullable|numeric|min:0',
            ]);

            $item = Cart::get($rowId);
            if (!$item) {
                return redirect()->back()->with([
                    'message'    => 'Item نەدۆزرایەوە',
                    'alert-type' => 'error',
                ]);
            }

            $options = $item->options ?? [];
            $price   = $request->has('price') ? floatval($request->price) : $item->price;

            Cart::update($rowId, [
                'qty'     => floatval($request->qty),
                'price'   => $price,
                'options' => $options,
            ]);

            return redirect()->back()->with([
                'message'    => '✅ سەبەتەکە نوێ کرایەوە',
                'alert-type' => 'success',
            ]);

        } catch (\Exception $e) {
            return redirect()->back()->with([
                'message'    => 'خرابی: ' . $e->getMessage(),
                'alert-type' => 'error',
            ]);
        }
    }

    /**
     * Remove item from cart
     */
    public function CartRemove($rowId)
    {
        try {
            if (Cart::content()->has($rowId)) {
                Cart::remove($rowId);
            }

            return redirect()->back()->with([
                'message'    => '✅ ئایتم سڕایەوە',
                'alert-type' => 'success',
            ]);

        } catch (\Exception $e) {
            return redirect()->back()->with([
                'message'    => 'خرابی: ' . $e->getMessage(),
                'alert-type' => 'error',
            ]);
        }
    }

    /**
     * Create invoice from cart
     */
    public function CreateInvoice(Request $request)
    {
        try {
            $request->validate([
                'customer_id' => 'required|exists:customers,id',
            ]);

            $contents = Cart::content();

            if ($contents->count() == 0) {
                return redirect()->back()->with([
                    'message'    => 'سەبەتەکە خالیە',
                    'alert-type' => 'error',
                ]);
            }

            $customer = Customer::select([
                'id',
                'name',
                'phone',
                'due',
                'address',
                'shopname',
            ])->find($request->customer_id);

            if (!$customer) {
                return redirect()->back()->with([
                    'message'    => 'کڕیار نەدۆزرایەوە',
                    'alert-type' => 'error',
                ]);
            }

            $subTotal = collect($contents)->sum(function ($item) {
                return floatval($item->qty) * floatval($item->price);
            });

            return view('backend.invoice.product_invoice', [
                'contents'    => $contents,
                'customer'    => $customer,
                'customerDue' => floatval($customer->due ?? 0),
                'subTotal'    => $subTotal,
            ]);

        } catch (\Exception $e) {
            return redirect()->back()->with([
                'message'    => 'خرابی: ' . $e->getMessage(),
                'alert-type' => 'error',
            ]);
        }
    }

    /**
     * Get all available products (for AJAX)
     */
    public function AllItem(Request $request)
    {
        try {
            $products = Product::where('product_store', '>', 0)
                ->select([
                    'id',
                    'product_name',
                    'product_code',
                    'product_store',
                    'selling_price',
                    'buying_price',
                ])
                ->latest()
                ->limit(100)
                ->get();

            return response()->json([
                'status' => 'success',
                'data'   => $products,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get customer details via AJAX
     */
    public function GetCustomer($id)
    {
        try {
            $customer = Customer::select([
                'id',
                'name',
                'phone',
                'due',
                'address',
                'shopname',
            ])->findOrFail($id);

            return response()->json([
                'status' => 'success',
                'data'   => $customer,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 404);
        }
    }
}