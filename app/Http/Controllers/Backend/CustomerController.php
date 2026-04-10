<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use Intervention\Image\Facades\Image;
use App\Models\Payment;
use Carbon\Carbon;

class CustomerController extends Controller
{
     public function AllCustomer(){

        $customer = Customer::latest()->get();
        return view('backend.customer.all_customer',compact('customer'));
    } // End Method 
    
public function ShowCustomer($id)
{
    // Load customer with relationships
    $customer = Customer::findOrFail($id);
    $customer->load(['orders', 'payments']);

    // CARD 1: Count distinct orders
    $order_count = $customer->orders->count();
    
    // CARD 2: previous_due + all order sub_totals
    $total_spent = $customer->previous_due;
    foreach ($customer->orders as $order) {
        $total_spent += $order->sub_total;
    }
    
    // CARD 3: Total Paid = all Payment records only (NOT order->pay)
    $total_paid_all = Payment::where('customer_id', $customer->id)->sum('payment_amount');
    
    // CARD 4: Total Due
    $total_due = max($total_spent - $total_paid_all, 0);

    return view('backend.customer.show_customer', compact(
        'customer', 
        'order_count', 
        'total_spent', 
        'total_paid_all', 
        'total_due'
    ));
}
    public function AddCustomer(){
         return view('backend.customer.add_customer');
    } // End Method 


     public function StoreCustomer(Request $request){

        $validateData = $request->validate([
            'name' => 'required|max:200',
            'email' => 'required|unique:customers|max:200',
            'phone' => 'required|max:200',
            'address' => 'required|max:400',
            'shopname' => 'required|max:200',
            'image' => 'required',  
        ]);
 
        $image = $request->file('image');
        $name_gen = hexdec(uniqid()).'.'.$image->getClientOriginalExtension();
        Image::make($image)->resize(300,300)->save('upload/customer/'.$name_gen);
        $save_url = 'upload/customer/'.$name_gen;

        Customer::insert([

            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'shopname' => $request->shopname,
            'city' => $request->city,
            'image' => $save_url,
            'previous_due' => $request->previous_due ?? 0,
            'created_at' => Carbon::now(), 

        ]);

         $notification = array(
            'message' => 'Customer Inserted Successfully',
            'alert-type' => 'success'
        );

        return redirect()->route('all.customer')->with($notification); 
    } // End Method 


 public function EditCustomer($id){

        $customer = Customer::findOrFail($id);
        return view('backend.customer.edit_customer',compact('customer'));

    } // End Method 


     public function UpdateCustomer(Request $request){

        $customer_id = $request->id;

        if ($request->file('image')) {

        $image = $request->file('image');
        $name_gen = hexdec(uniqid()).'.'.$image->getClientOriginalExtension();
        Image::make($image)->resize(300,300)->save('upload/customer/'.$name_gen);
        $save_url = 'upload/customer/'.$name_gen;

        Customer::findOrFail($customer_id)->update([

            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'shopname' => $request->shopname,
            'city' => $request->city,
            'image' => $save_url,
            'created_at' => Carbon::now(), 

        ]);

         $notification = array(
            'message' => 'Customer Updated Successfully',
            'alert-type' => 'success'
        );

        return redirect()->route('all.customer')->with($notification); 
             
        } else{

            Customer::findOrFail($customer_id)->update([

            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'shopname' => $request->shopname,
            'city' => $request->city, 
            'created_at' => Carbon::now(), 

        ]);

         $notification = array(
            'message' => 'Customer Updated Successfully',
            'alert-type' => 'success'
        );

        return redirect()->route('all.customer')->with($notification); 

        } // End else Condition  


    } // End Method 


 public function DeleteCustomer($id){

        $customer_img = Customer::findOrFail($id);
        $img = $customer_img->image;
        unlink($img);

        Customer::findOrFail($id)->delete();

        $notification = array(
            'message' => 'Customer Deleted Successfully',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification); 

    } // End Method 

public function PaymentCustomer(Request $request){
    
    $customer_id = $request->customer_id;
    $payment_amount = $request->payment_amount;
    
    $customer = Customer::findOrFail($customer_id);
    
    // Create payment record ONLY - DO NOT CHANGE previous_due
    Payment::create([
        'customer_id' => $customer_id,
        'payment_amount' => $payment_amount,
        'payment_date' => now(),
    ]);

    $notification = array(
        'message' => 'Payment of $' . number_format($payment_amount, 2) . ' Recorded Successfully',
        'alert-type' => 'success'
    );

    return redirect()->route('customer.show', $customer_id)->with($notification);
}

}