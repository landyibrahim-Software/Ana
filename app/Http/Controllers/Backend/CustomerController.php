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
    public function AllCustomer()
    {
        $customer = Customer::select(['id','name','phone','city','shopname','image','created_at'])
            ->latest()
            ->paginate(50);
        return view('backend.customer.all_customer', compact('customer'));
    } // End Method

    public function ShowCustomer($id)
    {
        $customer = Customer::findOrFail($id);

        // ✅ OPTIMIZATION: Single aggregate query instead of 3 separate queries
        $orderStats = $customer->orders()
            ->where('order_status', '!=', 'cancelled')
            ->selectRaw('COUNT(*) as order_count, COALESCE(SUM(sub_total), 0) as orders_total, COALESCE(SUM(pay), 0) as orders_paid')
            ->first();

        $order_count  = intval($orderStats->order_count ?? 0);
        $orders_total = floatval($orderStats->orders_total ?? 0);
        $orders_paid  = floatval($orderStats->orders_paid ?? 0);

        // Card 3 part (B): Paid later from Show Customer page (payments table)
        $payments_paid = Payment::where('customer_id', $customer->id)
            ->where('payment_status', 'completed')
            ->sum('payment_amount') ?? 0;

        // Card 2: total spent/owed = previous_due + purchases after system
        $total_spent = floatval($customer->previous_due ?? 0) + floatval($orders_total);

        // Card 3: total paid = orders.pay + payments.payment_amount
        $total_paid_all = floatval($orders_paid) + floatval($payments_paid);

        // Card 4: remaining due
        $total_due = max($total_spent - $total_paid_all, 0);

        return view('backend.customer.show_customer', compact(
            'customer',
            'order_count',
            'total_spent',
            'total_paid_all',
            'total_due'
        ));
    }

    public function AddCustomer()
    {
        return view('backend.customer.add_customer');
    } // End Method

    public function StoreCustomer(Request $request)
    {
        $request->validate([
            'name'     => 'required|max:200',
            'phone'    => 'required|max:200',
            'address'  => 'required|max:400',
            'shopname' => 'required|max:200',
            'image'    => 'required',
            'due'      => 'nullable|numeric|min:0',
        ]);

        $image    = $request->file('image');
        $name_gen = hexdec(uniqid()) . '.' . $image->getClientOriginalExtension();
        Image::make($image)->resize(300, 300)->save('upload/customer/' . $name_gen);
        $save_url = 'upload/customer/' . $name_gen;

        $openingBalance = floatval($request->due ?? 0);

        Customer::insert([
            'name'         => $request->name,
            'phone'        => $request->phone,
            'address'      => $request->address,
            'shopname'     => $request->shopname,
            'city'         => $request->city,
            'image'        => $save_url,
            'previous_due' => $openingBalance,
            'due'          => $openingBalance,
            'created_at'   => Carbon::now(),
        ]);

        $notification = [
            'message'    => 'Customer Inserted Successfully',
            'alert-type' => 'success',
        ];

        return redirect()->route('all.customer')->with($notification);
    } // End Method

    public function EditCustomer($id)
    {
        $customer = Customer::findOrFail($id);
        return view('backend.customer.edit_customer', compact('customer'));
    } // End Method

    public function UpdateCustomer(Request $request)
    {
        $customer_id = $request->id;
        // ✅ OPTIMIZATION: Fetch once, reuse for both branches
        $customer = Customer::findOrFail($customer_id);

        if ($request->file('image')) {

            $image    = $request->file('image');
            $name_gen = hexdec(uniqid()) . '.' . $image->getClientOriginalExtension();
            Image::make($image)->resize(300, 300)->save('upload/customer/' . $name_gen);
            $save_url = 'upload/customer/' . $name_gen;

            $newPreviousDue = floatval($request->due ?? 0);

            // ✅ OPTIMIZATION: Single aggregate instead of 2 separate sum() queries
            $stats = $customer->orders()
                ->where('order_status', '!=', 'cancelled')
                ->selectRaw('COALESCE(SUM(sub_total), 0) as orders_total, COALESCE(SUM(pay), 0) as orders_paid')
                ->first();

            $ordersTotal  = floatval($stats->orders_total ?? 0);
            $ordersPaid   = floatval($stats->orders_paid  ?? 0);
            $paymentsPaid = floatval(Payment::where('customer_id', $customer_id)
                ->where('payment_status', 'completed')
                ->sum('payment_amount') ?? 0);

            $newDue = max(0, $newPreviousDue + $ordersTotal - $ordersPaid - $paymentsPaid);

            $customer->update([
                'name'         => $request->name,
                'phone'        => $request->phone,
                'address'      => $request->address,
                'shopname'     => $request->shopname,
                'city'         => $request->city,
                'image'        => $save_url,
                'previous_due' => $newPreviousDue,
                'due'          => $newDue,
                'updated_at'   => Carbon::now(),
            ]);

        } else {

            $customer->update([
                'name'       => $request->name,
                'phone'      => $request->phone,
                'address'    => $request->address,
                'shopname'   => $request->shopname,
                'city'       => $request->city,
                'created_at' => Carbon::now(),
            ]);

        } // End else Condition

        $notification = [
            'message'    => 'Customer Updated Successfully',
            'alert-type' => 'success',
        ];

        return redirect()->route('all.customer')->with($notification);
    } // End Method

    public function DeleteCustomer($id)
    {
        // ✅ OPTIMIZATION: Fetch once, reuse for both unlink and delete
        $customer = Customer::findOrFail($id);
        unlink($customer->image);
        $customer->delete();

        $notification = [
            'message'    => 'Customer Deleted Successfully',
            'alert-type' => 'success',
        ];

        return redirect()->back()->with($notification);
    } // End Method

    public function PaymentCustomer(Request $request)
    {
        $request->validate([
            'customer_id'    => 'required|exists:customers,id',
            'payment_amount' => 'required|numeric|min:0.01',
        ]);

        $customer_id    = (int) $request->customer_id;
        $payment_amount = floatval($request->payment_amount);

        $customer = Customer::findOrFail($customer_id);

        // 1) Save payment record (pay later)
        Payment::create([
            'customer_id'    => $customer_id,
            'payment_amount' => $payment_amount,
            'payment_date'   => now(),
            'payment_status' => 'completed',
        ]);

        // 2) Recalculate Cards logic
        // ✅ OPTIMIZATION: Single aggregate instead of 2 separate sum() queries
        $orderStats = $customer->orders()
            ->where('order_status', '!=', 'cancelled')
            ->selectRaw('COALESCE(SUM(sub_total), 0) as orders_total, COALESCE(SUM(pay), 0) as orders_paid')
            ->first();

        $orders_total = floatval($orderStats->orders_total ?? 0);
        $orders_paid  = floatval($orderStats->orders_paid  ?? 0);

        $payments_paid = floatval(Payment::where('customer_id', $customer_id)
            ->where('payment_status', 'completed')
            ->sum('payment_amount') ?? 0);

        $total_spent    = floatval($customer->previous_due ?? 0) + $orders_total; // Card2
        $total_paid_all = $orders_paid + $payments_paid;                          // Card3
        $total_due      = max($total_spent - $total_paid_all, 0);                 // Card4

        // 3) Update cached fields (so other pages show correct values)
        $customer->update([
            'due'         => $total_due,
            'total_paid'  => $total_paid_all,
            'total_spent' => $total_spent,
        ]);

        return redirect()->route('customer.show', $customer_id)->with([
            'message'    => 'Payment of $' . number_format($payment_amount, 2) . ' Recorded Successfully',
            'alert-type' => 'success',
        ]);
    }
}