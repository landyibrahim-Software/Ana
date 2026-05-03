<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use Intervention\Image\Facades\Image;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use App\Exports\ProductExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ProductImport;


class ProductController extends Controller
{
    public function AllProduct()
    {
        $product = Product::with(['category:id,category_name', 'supplier:id,name'])
            ->select([
                'id', 'product_name', 'product_code', 'product_garage', 'product_store',
                'buying_price', 'selling_price', 'product_image', 'category_id', 'supplier_id', 'created_at',
            ])
            ->latest()
            ->get();

        return view('backend.product.all_product', compact('product'));
    }

    public function AddProduct()
    {
        $category = Category::select('id', 'category_name')->latest()->get();
        $supplier = Supplier::select('id', 'name')->latest()->get();

        return view('backend.product.add_product', compact('category', 'supplier'));
    }

    public function StoreProduct(Request $request)
    {
        $pcode = IdGenerator::generate([
            'table'  => 'products',
            'field'  => 'product_code',
            'length' => 4,
            'prefix' => 'PC',
        ]);

        if (empty($pcode)) {
            $pcode = 'PC' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
        }

        $image    = $request->file('product_image');
        $name_gen = hexdec(uniqid()) . '.' . $image->getClientOriginalExtension();
        Image::make($image)->resize(300, 300)->save('upload/product/' . $name_gen);
        $save_url = 'upload/product/' . $name_gen;

        Product::create([
            'product_name'  => $request->product_name,
            'category_id'   => $request->category_id,
            'supplier_id'   => $request->supplier_id,
            'product_code'  => $pcode,
            'product_garage' => $request->product_garage,
            'product_store' => $request->product_store,
            'buying_price'  => $request->buying_price,
            'selling_price' => $request->selling_price,
            'product_image' => $save_url,
        ]);

        $notification = [
            'message'    => 'Product Inserted Successfully',
            'alert-type' => 'success',
        ];

        return redirect()->route('all.product')->with($notification);
    }

    public function EditProduct($id)
    {
        $product  = Product::findOrFail($id);
        $category = Category::select('id', 'category_name')->latest()->get();
        $supplier = Supplier::select('id', 'name')->latest()->get();

        return view('backend.product.edit_product', compact('product', 'category', 'supplier'));
    }

    public function UdateProduct(Request $request)
    {
        $product_id = $request->id;
        $product    = Product::findOrFail($product_id);

        if ($request->file('product_image')) {
            $image    = $request->file('product_image');
            $name_gen = hexdec(uniqid()) . '.' . $image->getClientOriginalExtension();
            Image::make($image)->resize(300, 300)->save('upload/product/' . $name_gen);
            $save_url = 'upload/product/' . $name_gen;

            $product->update([
                'product_name'   => $request->product_name,
                'category_id'    => $request->category_id,
                'supplier_id'    => $request->supplier_id,
                'product_code'   => $request->product_code,
                'product_garage' => $request->product_garage,
                'product_store'  => $request->product_store,
                'buying_price'   => $request->buying_price,
                'selling_price'  => $request->selling_price,
                'product_image'  => $save_url,
            ]);
        } else {
            $product->update([
                'product_name'   => $request->product_name,
                'category_id'    => $request->category_id,
                'supplier_id'    => $request->supplier_id,
                'product_code'   => $request->product_code,
                'product_garage' => $request->product_garage,
                'product_store'  => $request->product_store,
                'buying_price'   => $request->buying_price,
                'selling_price'  => $request->selling_price,
            ]);
        }

        $notification = [
            'message'    => 'Product Updated Successfully',
            'alert-type' => 'success',
        ];

        return redirect()->route('all.product')->with($notification);
    }

    public function DeleteProduct($id)
    {
        $product = Product::findOrFail($id);
        unlink($product->product_image);
        $product->delete();

        $notification = [
            'message'    => 'Product Deleted Successfully',
            'alert-type' => 'success',
        ];

        return redirect()->back()->with($notification);
    }

    public function BarcodeProduct($id)
    {
        $product = Product::findOrFail($id);

        return view('backend.product.barcode_product', compact('product'));
    }

    public function ImportProduct()
    {
        return view('backend.product.import_product');
    }

    public function Export()
    {
        return Excel::download(new ProductExport, 'products.xlsx');
    }

    public function Import(Request $request)
    {
        Excel::import(new ProductImport, $request->file('import_file'));

        $notification = [
            'message'    => 'Product Imported Successfully',
            'alert-type' => 'success',
        ];

        return redirect()->back()->with($notification);
    }
}