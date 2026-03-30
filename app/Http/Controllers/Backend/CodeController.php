<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Code;
use App\Models\Category;
use Carbon\Carbon;

class CodeController extends Controller
{
    public function AllCode(){
        $code = Code::with('category')->latest()->get();
        return view('backend.code.all_code',compact('code'));
    }

    public function AddCode(){
        $categories = Category::all();
        return view('backend.code.add_code',compact('categories'));
    }

    public function StoreCode(Request $request){
        Code::insert([
            'code_name' => $request->code_name,
            'category_id' => $request->category_id,
            'created_at' => Carbon::now(),
        ]);

        $notification = array(
            'message' => 'Code Inserted Successfully',
            'alert-type' => 'success'
        );

        return redirect()->route('all.code')->with($notification);
    }

    public function EditCode($id){
        $code = Code::findOrFail($id);
        $categories = Category::all();
        return view('backend.code.edit_code',compact('code','categories'));
    }

   public function UpdateCode(Request $request){
    $code_id = $request->id;

    Code::findOrFail($code_id)->update([
        'code_name' => $request->code_name,
        'category_id' => $request->category_id,
    ]);

    $notification = array(
        'message' => 'کۆد بە سەرکەوتووی نوێ کرایەوە',
        'alert-type' => 'success'
    );

    return redirect()->route('all.code')->with($notification);
}

    public function DeleteCode($id){
        Code::findOrFail($id)->delete();

        $notification = array(
            'message' => 'Code Deleted Successfully',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification);
    }

    public function GetCodesByCategory($categoryId){
        $codes = Code::where('category_id',$categoryId)->get();
        return response()->json($codes);
    }
}