<?php
  
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Backend\EmployeeController;
use App\Http\Controllers\Backend\CustomerController;
use App\Http\Controllers\Backend\SupplierController;
use App\Http\Controllers\Backend\AttendenceController;
use App\Http\Controllers\Backend\CategoryController;
use App\Http\Controllers\Backend\ProductController;
use App\Http\Controllers\Backend\ExpenseController;
use App\Http\Controllers\Backend\PosController;
use App\Http\Controllers\Backend\OrderController;
use App\Http\Controllers\Backend\RoleController;
use App\Http\Controllers\Backend\SupplierPaymentController;
 
/* 
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/**
 * ============================================================
 * PUBLIC ROUTES (No Authentication Required)
 * ============================================================
 */

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

require __DIR__.'/auth.php';

/**
 * ============================================================
 * AUTHENTICATED ROUTES (All routes below require auth)
 * ============================================================
 */

Route::middleware('auth')->group(function () {

    // ============================================================
    // DASHBOARD & PROFILE
    // ============================================================
    
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('verified')
        ->name('dashboard');

    Route::get('/admin/logout', [AdminController::class, 'AdminDestroy'])->name('admin.logout');
    Route::get('/logout', [AdminController::class, 'AdminLogoutPage'])->name('admin.logout.page');

    Route::get('/admin/profile', [AdminController::class, 'AdminProfile'])->name('admin.profile');
    Route::post('/admin/profile/store', [AdminController::class, 'AdminProfileStore'])->name('admin.profile.store');

    Route::get('/change/password', [AdminController::class, 'ChangePassword'])->name('change.password');
    Route::post('/update/password', [AdminController::class, 'UpdatePassword'])->name('update.password');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ============================================================
    // EMPLOYEE ROUTES
    // ============================================================
    
    Route::controller(EmployeeController::class)->group(function(){
        Route::get('/all/employee', 'AllEmployee')
            ->name('all.employee')
            ->middleware('permission:employee.all');
        Route::get('/add/employee', 'AddEmployee')
            ->name('add.employee')
            ->middleware('permission:employee.add');
        Route::post('/store/employee', 'StoreEmployee')->name('employee.store');
        Route::get('/edit/employee/{id}', 'EditEmployee')->name('edit.employee');
        Route::post('/update/employee', 'UpdateEmployee')->name('employee.update');
        Route::get('/delete/employee/{id}', 'DeleteEmployee')->name('delete.employee');
    });

    // ============================================================
    // CUSTOMER ROUTES
    // ============================================================
    
    Route::controller(CustomerController::class)->group(function(){
        Route::get('/all/customer', 'AllCustomer')->name('all.customer');
        Route::get('/add/customer', 'AddCustomer')->name('add.customer');
        Route::post('/store/customer', 'StoreCustomer')->name('customer.store');
        Route::get('/edit/customer/{id}', 'EditCustomer')->name('edit.customer');
        Route::post('/update/customer', 'UpdateCustomer')->name('customer.update');
        Route::get('/delete/customer/{id}', 'DeleteCustomer')->name('delete.customer');
        Route::get('/customer/show/{id}', 'ShowCustomer')->name('customer.show');
        Route::post('/customer/payment', 'PaymentCustomer')->name('payment.customer');
    });

    // ============================================================
    // SUPPLIER ROUTES
    // ============================================================
    
    Route::controller(SupplierController::class)->group(function(){
        Route::get('/all/supplier', 'AllSupplier')->name('all.supplier');
        Route::get('/add/supplier', 'AddSupplier')->name('add.supplier');
        Route::post('/store/supplier', 'StoreSupplier')->name('supplier.store');
        Route::get('/edit/supplier/{id}', 'EditSupplier')->name('edit.supplier');
        Route::post('/update/supplier', 'UpdateSupplier')->name('supplier.update');
        Route::get('/delete/supplier/{id}', 'DeleteSupplier')->name('delete.supplier');
        Route::get('/details/supplier/{id}', 'DetailsSupplier')->name('details.supplier');
    });

    Route::post('supplier/payment/store', [SupplierPaymentController::class, 'store'])->name('supplier.payment.store');
    Route::get('supplier/payment/delete/{id}', [SupplierPaymentController::class, 'delete'])->name('supplier.payment.delete');

    // ============================================================
    // ATTENDANCE ROUTES
    // ============================================================
    
    Route::controller(AttendenceController::class)->group(function(){
        Route::get('/employee/attend/list', 'EmployeeAttendenceList')->name('employee.attend.list'); 
        Route::get('/add/employee/attend', 'AddEmployeeAttendence')->name('add.employee.attend'); 
        Route::post('/employee/attend/store', 'EmployeeAttendenceStore')->name('employee.attend.store'); 
        Route::get('/edit/employee/attend/{date}', 'EditEmployeeAttendence')->name('employee.attend.edit'); 
        Route::get('/view/employee/attend/{date}', 'ViewEmployeeAttendence')->name('employee.attend.view'); 
    });

    // ============================================================
    // CATEGORY ROUTES
    // ============================================================
    
    Route::controller(CategoryController::class)->group(function(){
        Route::get('/all/category', 'AllCategory')->name('all.category');
        Route::post('/store/category', 'StoreCategory')->name('category.store');  
        Route::get('/edit/category/{id}', 'EditCategory')->name('edit.category');
        Route::post('/update/category', 'UpdateCategory')->name('category.update'); 
        Route::get('/delete/category/{id}', 'DeleteCategory')->name('delete.category'); 
    });

    // ============================================================
    // PRODUCT ROUTES
    // ============================================================
    
    Route::controller(ProductController::class)->group(function(){
        Route::get('/all/product', 'AllProduct')->name('all.product');
        Route::get('/add/product', 'AddProduct')->name('add.product');
        Route::post('/store/product', 'StoreProduct')->name('product.store');
        Route::get('/edit/product/{id}', 'EditProduct')->name('edit.product');
        Route::post('/update/product', 'UdateProduct')->name('product.update');
        Route::get('/delete/product/{id}', 'DeleteProduct')->name('delete.product');
        Route::get('/barcode/product/{id}', 'BarcodeProduct')->name('barcode.product');
        Route::get('/import/product', 'ImportProduct')->name('import.product');
        Route::get('/export', 'Export')->name('export');
        Route::post('/import', 'Import')->name('import');
    });

    // ============================================================
    // EXPENSE ROUTES
    // ============================================================
    
    Route::controller(ExpenseController::class)->group(function(){
        Route::get('/add/expense', 'AddExpense')->name('add.expense');
        Route::post('/store/expense', 'StoreExpense')->name('expense.store');
        Route::get('/today/expense', 'TodayExpense')->name('today.expense');
        Route::get('/edit/expense/{id}', 'EditExpense')->name('edit.expense');
        Route::post('/update/expense', 'UpdateExpense')->name('expense.update');
        Route::get('/month/expense', 'MonthExpense')->name('month.expense');
        Route::get('/year/expense', 'YearExpense')->name('year.expense');
    });

    // ============================================================
    // POS ROUTES (Point of Sale)
    // ============================================================
    
    Route::controller(PosController::class)->group(function(){
        Route::get('/pos', 'Pos')->name('pos');
        Route::post('/add-cart', 'AddCart')->name('add.cart');
        Route::post('/update-cart-price/{rowId}', 'UpdateCartPrice')->name('update.cart.price');
        Route::post('/cart-update/{rowId}', 'CartUpdate')->name('cart.update');
        Route::get('/cart-remove/{rowId}', 'CartRemove')->name('cart.remove');
        Route::post('/create-invoice', 'CreateInvoice')->name('create.invoice');
        Route::get('/all-items', 'AllItem')->name('all.item');
        Route::get('/search-products', 'SearchProducts')->name('search.products');
        Route::get('/search-customers', 'SearchCustomers')->name('search.customers');
        Route::get('/get-customer/{id}', 'GetCustomer')->name('get.customer');
    });

    // ============================================================
    // ORDER ROUTES
    // ============================================================
    
    Route::controller(OrderController::class)->group(function(){
        // Invoice Routes
        Route::get('/print-invoice/{id}', 'PrintInvoice')->name('print.invoice');
        Route::post('/final-invoice', 'FinalInvoice')->name('final.invoice');
        Route::get('/invoice/pdf/{order_id}', 'GenerateInvoicePDF')->name('invoice.pdf');
               Route::get('/invoice/payment/{cartTotal}/{customer_id}', 'ShowInvoicePayment')->name('invoice.payment');

        // Order Status Routes
        Route::get('/pending/order', 'PendingOrder')->name('pending.order');
        Route::get('/order/details/{order_id}', 'OrderDetails')->name('order.details');
        Route::post('/order/status/update', 'OrderStatusUpdate')->name('order.status.update');
        Route::post('/order/cancel', 'cancelOrder')->name('order.cancel');

        // Stock Routes
        Route::get('/stock', 'StockManage')->name('stock.manage');

        // Due Routes
        Route::get('/order/due/{id}', 'OrderDueAjax')->name('order.due.ajax');
        Route::post('/update/due', 'UpdateDue')->name('update.due');
    });

    // ============================================================
    // PERMISSION ROUTES
    // ============================================================
    
    Route::controller(RoleController::class)->group(function(){
        Route::get('/all/permission', 'AllPermission')->name('all.permission');
        Route::get('/add/permission', 'AddPermission')->name('add.permission');
        Route::post('/store/permission', 'StorePermission')->name('permission.store');
        Route::get('/edit/permission/{id}', 'EditPermission')->name('edit.permission');
        Route::post('/update/permission', 'UpdatePermission')->name('permission.update');
        Route::get('/delete/permission/{id}', 'DeletePermission')->name('delete.permission');
    });

    // ============================================================
    // ROLE ROUTES
    // ============================================================
    
    Route::controller(RoleController::class)->group(function(){
        Route::get('/all/roles', 'AllRoles')->name('all.roles');
        Route::get('/add/roles', 'AddRoles')->name('add.roles');
        Route::post('/store/roles', 'StoreRoles')->name('roles.store');
        Route::get('/edit/roles/{id}', 'EditRoles')->name('edit.roles');
        Route::post('/update/roles', 'UpdateRoles')->name('roles.update');
        Route::get('/delete/roles/{id}', 'DeleteRoles')->name('delete.roles');
    });

    // ============================================================
    // ROLE PERMISSION ROUTES
    // ============================================================
    
    Route::controller(RoleController::class)->group(function(){
        Route::get('/add/roles/permission', 'AddRolesPermission')->name('add.roles.permission');
        Route::post('/role/permission/store', 'StoreRolesPermission')->name('role.permission.store');
        Route::get('/all/roles/permission', 'AllRolesPermission')->name('all.roles.permission');
        Route::get('/admin/edit/roles/{id}', 'AdminEditRoles')->name('admin.edit.roles');
        Route::post('/role/permission/update/{id}', 'RolePermissionUpdate')->name('role.permission.update');
        Route::get('/admin/delete/roles/{id}', 'AdminDeleteRoles')->name('admin.delete.roles');
    });

    // ============================================================
    // ADMIN USER ROUTES
    // ============================================================
    
    Route::controller(AdminController::class)->group(function(){
        Route::get('/all/admin', 'AllAdmin')->name('all.admin');
        Route::get('/add/admin', 'AddAdmin')->name('add.admin');
        Route::post('/store/admin', 'StoreAdmin')->name('admin.store');
        Route::get('/edit/admin/{id}', 'EditAdmin')->name('edit.admin');
        Route::post('/update/admin', 'UpdateAdmin')->name('admin.update');
        Route::get('/delete/admin/{id}', 'DeleteAdmin')->name('delete.admin');
    });

    // ============================================================
    // DATABASE BACKUP ROUTES
    // ============================================================
    
    Route::controller(AdminController::class)->group(function(){
        Route::get('/database/backup', 'DatabaseBackup')->name('database.backup');
        Route::get('/backup/now', 'BackupNow')->name('backup.now');
        Route::get('/backup/download/{filename}', 'DownloadDatabase')->name('backup.download');
        Route::get('/backup/delete/{filename}', 'DeleteDatabase')->name('backup.delete');
    });

    // ============================================================
    // BANK ROUTES
    // ============================================================
    
    Route::controller(\App\Http\Controllers\Backend\BankController::class)->group(function(){
        Route::get('/bank', 'index')->name('bank.index');
        Route::post('/bank/spend', 'addSpend')->name('bank.spend');
        Route::post('/bank/receive', 'addReceive')->name('bank.receive');
        Route::post('/bank/spend-iqd', 'addSpendIQD')->name('bank.spend.iqd');
        Route::post('/bank/receive-iqd', 'addReceiveIQD')->name('bank.receive.iqd');
    });

}); // End Authenticated Middleware