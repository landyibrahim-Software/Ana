    <div class="left-side-menu">

                <div class="h-100" data-simplebar>

                    <!-- User box -->
                

                    <!--- Sidemenu -->
                    <div id="sidebar-menu">

                        <ul id="side-menu">

                            <li class="menu-title">سەردانیکردن</li>
                
          <li>
            <a href="{{ url('/dashboard') }}">
               <i class="mdi mdi-view-dashboard-outline"></i>
                <span> داشبۆرد </span>
            </a>
        </li>


        @if(Auth::user()->can('pos.menu'))
           <li>
            <a href="{{ route('pos') }}">
                <span class="badge bg-pink float-end">Hot</span>
               <i class="mdi mdi-view-dashboard-outline"></i>
                <span> فرۆشتن </span>
            </a>
        </li>
        @endif




                            <li class="menu-title mt-2">بەشەکان</li>
    

                           
 @if(Auth::user()->can('employee.menu'))
    <li>
        <a href="#sidebarEcommerce" data-bs-toggle="collapse">
            <i class="mdi mdi-cart-outline"></i>
            <span> بەڕێوەبردنی کارمەند  </span>
            <span class="menu-arrow"></span>
        </a>
        <div class="collapse" id="sidebarEcommerce">
            <ul class="nav-second-level">
                @if(Auth::user()->can('employee.all'))
                <li>
                    <a href="{{ route('all.employee') }}">کارمەندەکان</a>
                </li>
                @endif
                @if(Auth::user()->can('employee.add'))
                <li>
                    <a href="{{ route('add.employee') }}">زیادکردنی کارمەند</a>
                </li>
               @endif
            </ul>
        </div>
    </li>
@endif
                            
 @if(Auth::user()->can('customer.menu'))
            <li>
                <a href="#sidebarCrm" data-bs-toggle="collapse">
                    <i class="mdi mdi-account-multiple-outline"></i>
                    <span> بەڕێوەبردنی کڕیار  </span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse" id="sidebarCrm">
                    <ul class="nav-second-level">
     @if(Auth::user()->can('customer.all'))
    <li>
        <a href="{{ route('all.customer') }}">کڕیارەکان</a>
    </li>
    @endif
     @if(Auth::user()->can('customer.add'))
    <li>
        <a href="{{ route('add.customer') }}">زیادکردنی کڕیار</a>
    </li>
    @endif
                         
                    </ul>
                </div>
            </li>
      @endif

 @if(Auth::user()->can('supplier.menu'))
        <li>
            <a href="#sidebarEmail" data-bs-toggle="collapse">
                <i class="mdi mdi-email-multiple-outline"></i>
                <span> بەڕێوەبردنی دابینکەر </span>
                <span class="menu-arrow"></span>
            </a>
            <div class="collapse" id="sidebarEmail">
                <ul class="nav-second-level">
                    <li>
                        <a href="{{ route('all.supplier') }}"> دابینکەران</a>
                    </li>
                    <li>
                        <a href="{{ route('add.supplier') }}">زیادکردنی دابینکەر</a>
                    </li>
                    
                </ul>
            </div>
        </li>
@endif


 @if(Auth::user()->can('attendence.menu'))
        <li>
            <a href="#attendence" data-bs-toggle="collapse">
                <i class="mdi mdi-email-multiple-outline"></i>
                <span> ئامادەبونی کارمەندەکان </span>
                <span class="menu-arrow"></span>
            </a>
            <div class="collapse" id="attendence">
                <ul class="nav-second-level">
                    <li>
                        <a href="{{ route('employee.attend.list') }}">لیستی ئامادەبونی کارمەندەکان </a>
                    </li>
                
                </ul>
            </div>
        </li>

@endif
 @if(Auth::user()->can('category.menu'))
        <li>
            <a href="#category" data-bs-toggle="collapse">
                <i class="mdi mdi-email-multiple-outline"></i>
                <span> جۆرەکان </span>
                <span class="menu-arrow"></span>
            </a>
            <div class="collapse" id="category">
                <ul class="nav-second-level">
                    <li>
                        <a href="{{ route('all.category') }}">هەموو جۆرەکان </a>
                    </li>
                
                </ul>
            </div>
        </li>
@endif

@if(Auth::user()->can('category.menu'))
        <li>
            <a href="#code" data-bs-toggle="collapse">
                <i class="mdi mdi-email-multiple-outline"></i>
                <span> کۆدەکان </span>
                <span class="menu-arrow"></span>
            </a>
            <div class="collapse" id="code">
                <ul class="nav-second-level">
                    <li>
                        <a href="{{ route('all.code') }}">هەموو کۆدەکان </a>
                    </li>
                    <li>
                        <a href="{{ route('add.code') }}">زیادکردنی کۆد </a>
                    </li>
                
                </ul>
            </div>
        </li>
@endif

 @if(Auth::user()->can('product.menu'))
         <li>
            <a href="#product" data-bs-toggle="collapse">
                <i class="mdi mdi-email-multiple-outline"></i>
                <span> بەرهەمەکان  </span>
                <span class="menu-arrow"></span>
            </a>
            <div class="collapse" id="product">
                <ul class="nav-second-level">
                    <li>
                        <a href="{{ route('all.product') }}">هەموو بەرهەمەکان </a>
                    </li>

                     <li>
                        <a href="{{ route('add.product') }}">زیادکردنی بەرهەم </a>
                    </li>
                     <li>
                        <a href="{{ route('import.product') }}"> هاوردەکردنی بەرهەم </a>
                    </li>
                
                </ul>
            </div>
        </li>

@endif
 @if(Auth::user()->can('orders.menu'))
 <li>
    <a href="#orders" data-bs-toggle="collapse">
        <i class="mdi mdi-email-multiple-outline"></i>
        <span> ئەرشیف  </span>
        <span class="menu-arrow"></span>
    </a>
    <div class="collapse" id="orders">
        <ul class="nav-second-level">
            <li>
                <a href="{{ route('pending.order') }}"> ئەرشیف ئۆردەر  </a>
            </li>

             <li>
                <a href="{{ route('complete.order') }}">  ئەرشیف تەواو بوو</a>
            </li>

            <li>
                <a href="{{ route('pending.due') }}"> ئەرشیفی  قەرز </a>
            </li>
            
        
        </ul>
    </div>
</li>
@endif
 @if(Auth::user()->can('stock.menu'))
 <li>
    <a href="#stock" data-bs-toggle="collapse">
        <i class="mdi mdi-email-multiple-outline"></i>
        <span> بەڕێوەبردنی کۆگا   </span>
        <span class="menu-arrow"></span>
    </a>
    <div class="collapse" id="stock">
        <ul class="nav-second-level">
            <li>
                <a href="{{ route('stock.manage') }}">کۆگا </a>
            </li>
 
        
        </ul>
    </div>
</li>
@endif
 @if(Auth::user()->can('roles.menu'))
 <li>
    <a href="#permission" data-bs-toggle="collapse">
        <i class="mdi mdi-email-multiple-outline"></i>
        <span> پلەبەندی و ڕێگەپێدان    </span>
        <span class="menu-arrow"></span>
    </a>
    <div class="collapse" id="permission">
        <ul class="nav-second-level">
            <li>
                <a href="{{ route('all.permission') }}">ڕیگەپێدان  </a>
            </li>

            <li>
                <a href="{{ route('all.roles') }}">هەموو پلەبەندیەکان </a>
            </li>

             <li>
                <a href="{{ route('add.roles.permission') }}"> پلەبەندی لە ناو ڕیگەپێدان </a>
            </li>

             <li>
                <a href="{{ route('all.roles.permission') }}">هەموو یاساکانی بەڕیوەبردن لە پلە بەندی </a>
            </li>
 
        
        </ul>
    </div>
</li>

@endif
 @if(Auth::user()->can('admin.menu'))
 <li>
    <a href="#admin" data-bs-toggle="collapse">
        <i class="mdi mdi-email-multiple-outline"></i>
        <span> ڕێکخستنی بەکارهێنەری بەڕێوبەر    </span>
        <span class="menu-arrow"></span>
    </a>
    <div class="collapse" id="admin">
        <ul class="nav-second-level">
            <li>
                <a href="{{ route('all.admin') }}">بەڕێوبەرەکان </a>
            </li>

            <li>
                <a href="{{ route('add.admin') }}">زیادکردنی سەرپەرشتیار </a>
            </li> 
        
        </ul>
    </div>
</li>
@endif

                             
                          

                            <li class="menu-title mt-2">تایبەت بە کۆمپانیا</li>

 @if(Auth::user()->can('expense.menu'))
                        <li>
                            <a href="#sidebarAuth" data-bs-toggle="collapse">
                                <i class="mdi mdi-account-circle-outline"></i>
                                <span>خەرجیەکان </span>
                                <span class="menu-arrow"></span>
                            </a>
                            <div class="collapse" id="sidebarAuth">
    <ul class="nav-second-level">
        <li>
            <a href="{{ route('add.expense') }}">زیادکردنی خەرج</a>
        </li>
        <li>
            <a href="{{ route('today.expense') }}">خەرجی ئەمڕۆ</a>
        </li>
        <li>
            <a href="{{ route('month.expense') }}">خەرجی مانگانە</a>
        </li>
        <li>
            <a href="{{ route('year.expense') }}">خەرجی ساڵانە</a>
        </li>
        
    </ul>
                            </div>
                        </li>

@endif
<li class="side-nav-item">
    <a data-bs-toggle="collapse" href="#sidebarBank" aria-expanded="false" aria-controls="sidebarBank" class="side-nav-link">
        <i class="mdi mdi-bank"></i>
        <span>بانکی فرۆشگا</span>
        <span class="menu-arrow"></span>
    </a>
    <div class="collapse" id="sidebarBank">
        <ul class="side-nav-second-level">
            <li>
                <a href="{{ route('bank.index') }}">
                    <i class="mdi mdi-cash-multiple me-2"></i>سەرمایە و لیکدان
                </a>
            </li>
        </ul>
    </div>
</li>


    <li>
                            <a href="#backup" data-bs-toggle="collapse">
                                <i class="mdi mdi-account-circle-outline"></i>
                                <span>Database Backup  </span>
                                <span class="menu-arrow"></span>
                            </a>
                            <div class="collapse" id="backup">
    <ul class="nav-second-level">
        <li>
            <a href="{{ route('database.backup') }}">Database Backup </a>
        </li> 
        
    </ul>
           </div>
          </li>



                         

                         
 
                          

                               
                                    </ul>
                                </div>
                            </li>
                        </ul>

                    </div>
                    <!-- End Sidebar -->

                    <div class="clearfix"></div>

                </div>
                <!-- Sidebar -left -->

            </div>