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
    $customer = Customer::with('orders', 'payments')->findOrFail($id);

    $total_orders = 0;
    $total_paid   = 0;

    foreach ($customer->orders as $order) {

        // SAFETY: convert nulls to 0
        $qty         = $order->qty ?? 0;
        $price       = $order->price ?? 0;
        $paid        = $order->pay ?? 0;

        // CALCULATE ORDER TOTAL
        $order_total = ($qty * $price);

        // ATTACH for Blade
        $order->total_amount = $order_total;
        $order->paid_amount  = $paid;

        // GLOBAL TOTALS
        $total_orders += $order_total;
        $total_paid   += $paid;
    }

    // Card 1: Total Money = Previous Due ONLY
    $total_money = $customer->previous_due;

    // Card 2: Total Paid = All payments made
    $total_paid_all = $customer->payments->sum('payment_amount');

    // Card 3: Total Due = Previous Due - All Payments
    $total_due = max($total_money - $total_paid_all, 0);

    return view(
        'backend.customer.show_customer',
        compact('customer', 'total_paid_all', 'total_due', 'total_money')
    );
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
 