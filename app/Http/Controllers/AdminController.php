<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Spatie\DbDumper\Databases\MySql;
class AdminController extends Controller
{
     public function AdminDestroy(Request $request) {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

         $notification = array(
            'message' => 'Admin Logout Successfully',
            'alert-type' => 'info'
        );


        return redirect('/logout')->with($notification);
    } // End Method 


    public function AdminLogoutPage(){

        return view('admin.admin_logout');

    }// End Method 



    public function AdminProfile(){

        $id = Auth::user()->id;
        $adminData = User::find($id);
        return view('admin.admin_profile_view',compact('adminData'));
    }// End Method 


    public function AdminProfileStore(Request $request){

        $id = Auth::user()->id;
        $data = User::find($id);
        $data->name = $request->name;
        $data->email = $request->email;
        $data->phone = $request->phone;

        if ($request->file('photo')) {
           $file = $request->file('photo');
           @unlink(public_path('upload/admin_image/'.$data->photo));
           $filename = date('YmdHi').$file->getClientOriginalName();
           $file->move(public_path('upload/admin_image'),$filename);
           $data['photo'] = $filename;
        }
            
        $data->save();

        $notification = array(
            'message' => 'Admin Profile Updated Successfully',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification);


    }// End Method 


    public function ChangePassword(){
        return view('admin.change_password');
    }// End Method 



    public function UpdatePassword(Request $request){

        /// Validation 
        $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|confirmed',

        ]);

        /// Match The Old Password 
        if (!Hash::check($request->old_password, auth::user()->password)) {

             $notification = array(
            'message' => 'Old Password Dones not Match!!',
            'alert-type' => 'error'
             ); 
            return back()->with($notification);
           
        }

        //// Update The New Password 

        User::whereId(auth()->user()->id)->update([
            'password' => Hash::make($request->new_password)
        ]);

            $notification = array(
            'message' => 'Password Change Successfully',
            'alert-type' => 'success'
             ); 
            return back()->with($notification);

    }// End Method 

   /////////////////// Admin User All Method /////////////


    public function AllAdmin(){

        $alladminuser = User::latest()->get();
        return view('backend.admin.all_admin',compact('alladminuser'));
    }// End Method 

    public function AddAdmin(){

        $roles = Role::all();
        return view('backend.admin.add_admin',compact('roles'));
    }// End Method 


    public function StoreAdmin(Request $request){

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->password = Hash::make($request->password);
        $user->save();

        if ($request->roles) {
            $user->assignRole($request->roles);
        }

        $notification = array(
            'message' => 'New Admin User Created Successfully',
            'alert-type' => 'success'
        );

        return redirect()->route('all.admin')->with($notification);  

    }// End Method 


    public function EditAdmin($id){

        $roles = Role::all();
        $adminuser = User::findOrFail($id);
        return view('backend.admin.edit_admin',compact('roles','adminuser'));

    }// End Method 


    public function UpdateAdmin(Request $request){

        $admin_id = $request->id;

        $user = User::findOrFail($admin_id);
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone; 
        $user->save();

        $user->roles()->detach();
        if ($request->roles) {
            $user->assignRole($request->roles);
        }

        $notification = array(
            'message' => 'Admin User Updated Successfully',
            'alert-type' => 'success'
        );

        return redirect()->route('all.admin')->with($notification);  

    }// End Method 



    public function DeleteAdmin($id){

        $user = User::findOrFail($id);
        if (!is_null($user)) {
            $user->delete();
        }

        $notification = array(
            'message' => 'Admin User Deleted Successfully',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification); 

    }// End Method 

  public function DatabaseBackup()
{
    $path = storage_path('app/db_backups');

    if (!File::exists($path)) {
        File::makeDirectory($path, 0755, true);
    }

    $files = File::files($path);

    return view('admin.db_backup', compact('files'));
}

public function BackupNow()
{
    $dbName = env('DB_DATABASE');
    $dbUser = env('DB_USERNAME');
    $dbPass = env('DB_PASSWORD');
    $dbHost = env('DB_HOST', '127.0.0.1');

    $fileName = 'backup_' . date('Y_m_d_His') . '.sql';
    $backupDir = storage_path('app/db_backups');
    $filePath = $backupDir . DIRECTORY_SEPARATOR . $fileName;

    // Ensure directory exists
    if (!file_exists($backupDir)) {
        mkdir($backupDir, 0755, true);
    }

    // Correct mysqldump path for XAMPP on Windows
    $mysqldump = 'C:\\xampp\\mysql\\bin\\mysqldump.exe';

    // Build command
    $command = "\"{$mysqldump}\" --host={$dbHost} --user={$dbUser} " .
               ($dbPass !== '' ? "--password={$dbPass} " : "") .
               "--databases {$dbName} --single-transaction --routines --triggers --add-drop-database --add-drop-table > \"{$filePath}\"";

    // Execute command
    exec($command, $output, $result);

    // Check if backup created successfully
    if ($result !== 0 || !file_exists($filePath) || filesize($filePath) === 0) {
        $errorMessage = "Backup failed";
        if (!empty($output)) {
            $errorMessage .= ": " . implode(" | ", $output);
        }
        return back()->with('error', $errorMessage);
    }

    return back()->with('success', 'Backup created: ' . $fileName);
}

public function DownloadDatabase($filename)
{
    $path = storage_path('app/db_backups/' . $filename);

    if (!file_exists($path)) {
        return back()->with('error', 'File not found');
    }

    return response()->download($path);
}

public function DeleteDatabase($filename)
{
    $path = storage_path('app/db_backups/' . $filename);

    if (file_exists($path)) {
        unlink($path);
    }

    return back()->with('success', 'Backup deleted');
}
}



 