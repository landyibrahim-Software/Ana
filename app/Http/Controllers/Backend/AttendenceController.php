<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendence; 
use App\Models\Employee; 
use Carbon\Carbon;

class AttendenceController extends Controller
{
    public function EmployeeAttendenceList(){

        $allData = Attendence::select('date')->groupBy('date')->orderBy('id','desc')->get();
        return view('backend.attendence.view_employee_attend',compact('allData'));

    } // End Method 

    public function AddEmployeeAttendence(){
        $employees = Employee::all();
        return view('backend.attendence.add_employee_attend',compact('employees'));
    }// End Method 


    public function EmployeeAttendenceStore(Request $request){

       $date = date('Y-m-d', strtotime($request->date));

       Attendence::where('date', $date)->delete();
  $records = [];
        $now     = now();
        foreach ($request->employee_id as $index => $employeeId) {
            $statusKey = 'attend_status' . $index;
            $records[] = [
                'date'           => $date,
                'employee_id'    => $employeeId,
                'attend_status'  => $request->$statusKey,
                'created_at'     => $now,
                'updated_at'     => $now,
            ];
             if (!empty($records)) {
            Attendence::insert($records);
        }

         $notification = array(
            'message' => 'Data Inseted Successfully',
            'alert-type' => 'success'
        );

        return redirect()->route('employee.attend.list')->with($notification); 
        }}


    public function EditEmployeeAttendence($date){
         $employees = Employee::all();
         $editData = Attendence::where('date',$date)->get();
         return view('backend.attendence.edit_employee_attend',compact('employees','editData'));

    }// End Method 


    public function ViewEmployeeAttendence($date){

         $details = Attendence::where('date',$date)->get();
    return view('backend.attendence.details_employee_attend',compact('details'));


    }// End Method 


}
 