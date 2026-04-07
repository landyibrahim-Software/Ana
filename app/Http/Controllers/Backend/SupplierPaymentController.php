<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\SupplierPayment;
use Illuminate\Http\Request;

class SupplierPaymentController extends Controller
{
    public function store(Request $request)
    {
        SupplierPayment::create([
            'supplier_id' => $request->supplier_id,
            'payment_amount' => $request->payment_amount,
            'payment_date' => now(),
        ]);

        $notification = array(
            'message' => 'Payment Recorded Successfully',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification);
    }

    public function delete($id)
    {
        SupplierPayment::findOrFail($id)->delete();

        $notification = array(
            'message' => 'Payment Deleted Successfully',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification);
    }
}