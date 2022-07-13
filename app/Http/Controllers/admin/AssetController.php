<?php

namespace App\Http\Controllers\admin;
use Illuminate\Http\Request;
use \stdClass;
use \App\Model\Store;
use \App\Model\Unload;
use \App\Model\DamageClaim;
use \App\Model\DamageDetails;
use \App\Model\ProductModel;
use \App\Model\ProductDetails;
use \App\Model\Waybill;
use \App\Model\Loader;
use \App\Model\StockMovement;
use \App\Model\Customers;
use \App\Model\StockMovementDetails;
use \App\Custom\CustomHelpers;
use App\Model\DamageDetail;
use \App\Model\Stock;
use App\Model\Users;
use \App\Model\Countries;
use \App\Model\City;
use \App\Model\State;
use \App\Model\Factory;
use \App\Model\AMCProduct;
use \App\Model\PartShortage;
use \App\Model\ShortageDetails;
use \App\Model\Sale;
use\App\Model\WarrantyInvoice;
use \App\Model\Parts;
use \App\Model\PartWarranty;
use \App\Model\SaleAccessories;
use \App\Model\SaleFinance;
use \App\Model\Scheme;
use \App\Model\ExtendedWarranty;
use \App\Model\Accessories;
use \App\Model\MasterAccessories;
use \App\Model\HiRise;
use \App\Model\Insurance;
use \App\Model\SaleOrder;
use \App\Model\RtoModel;
use \App\Model\Payment;
use \App\Model\Settings;
use \App\Model\OrderPendingModel;
use \App\Model\BookingModel;
use \App\Model\BestDealSale;
use \App\Model\RtoFileSubmission;
use \App\Model\RtoFileSubmissionDetails;
use \App\Model\RcCorrectionRequest;
use \App\Model\RtoSummary;
use \App\Model\PDI;
use \App\Model\PDI_details;
use \App\Model\FinalInspection;
use\App\Model\ServiceModel;
use\App\Model\CustomerDetails;
use\App\Model\HJCModel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Auth;
use Hash;
use PDF;
use \Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\Import;
use DateTime;
use \App\Model\Job_card;
use \App\Model\Bay;
use \App\Model\BayAllocation;
use \App\Model\ServicePartRequest;
use\App\Model\TestRide;
use\App\Model\Feedback;
use\App\Model\AMCModel;
use\App\Model\ServiceCharge;
use\App\Model\Pickup;
use\App\Model\JobcardDetail;
use\App\Model\UserDetails;
use\App\Model\AssetCategory;
use\App\Model\Assets;
use\App\Model\AssetAssign;
use\App\Model\AssetDisposal;
use Config;
use Mail;


class AssetController extends Controller {
    public function __construct() {
        //$this->middleware('auth');
    }

    public function assets_unique_bill_no(Request $request){
        $x=$request->input('bill');
        $billno = Assets::where('asset_bill_no',$x)->get()->first();
        $result =0;
        if(isset($billno)){
            $result=1;
        }
        return response()->json($result);
    }

    public function create_assets(){
            $asset_category = DB::table('assets_category')->get();
            $store = Store::whereIn('id',CustomHelpers::user_store())
                    ->select('id',DB::raw('concat(store.name,"-",store.store_type) as name'))->get();
            $data=array('layout'=>'layouts.main','asset_category'=>$asset_category,'store'    =>  $store->toArray());
            return view('admin.assets.create_assets',$data);   
        }

     public function create_assetsDb(Request $request){
            try {
                $validator = Validator::make($request->all(),
                    [
                        'assets_category'=>'required',
                        'assets_name'=>'required',
                        'assets_brand'=>'required',
                        'assets_number'=>'required',
                        'assets_desc'=>'required',
                        'assets_bill_no'=>'required',
                        'store_name'=>'required',
                        'assets_purchase_date'=>'required|before:tomorrow|after:1900-01-01',
                        'assets_value'=>'required',
                        'assets_photo' => 'required_with|image|mimes:jpeg,png,jpg,gif,svg|max:'.CustomHelpers::getfilesize(),
                        'assets_bill_ph' => 'mimes:jpeg,png,jpg,gif,pdf,svg|max:'.CustomHelpers::getfilesize()
                    ],
                    [
                        'assets_category.required'=>'This Field is required',
                        'assets_name.required'=>'This Field is required',
                        'assets_brand.required'=>'This Field is required',
                        'store_name.required'   =>  'This Field is required',
                        'assets_number.required'=>'This Field is required',
                        'assets_desc.required'=>'This Field is required',
                        'assets_bill_no.required'=>'This Field is required',
                        'assets_purchase_date.required'=>'This Field is required',
                        'assets_value.required'=>'This Field is required',
                        'assets_photo.required_with'=>'Image accept only jpeg,png,jpg format',
                        'assets_bill_ph.required_with'=>'Field accept only jpeg,png,jpg,pdf format'
                    ]
                    );
                    $errors = $validator->errors();

                    if ($validator->fails()) 
                    {
                        return redirect('/admin/assets/create')->withErrors($errors);
                    }
                    else{
                        $categoryN= AssetCategory::where('ac_id',$request->input('assets_category'))->get(['category_name'])->first();
                        $cur_id =Assets::where('asset_category_id',$request->input('assets_category'))->get([DB::raw('Count(asset_category_id) as asset_counter')])->first();
                        $as_name= substr($request->input('assets_name'),0,5);
                        $as_cat= substr($categoryN->category_name,0,3);
                        $ass_count = (($cur_id['asset_counter']+1)<10) ? "0".($cur_id['asset_counter']+1) :($cur_id['asset_counter']+1);
                        $assetcode = "ASS/".$as_cat."/".$ass_count;
                        
                        $asset_image_file = '';
                        $asset_bill_file = '';
                        $file = $request->file('assets_photo');
                        $billfile = $request->file('assets_bill_ph');
                        if(isset($file) || $file != null){
                            $destinationPath = public_path().'/upload/assets/';
                            $filenameWithExt = $request->file('assets_photo')->getClientOriginalName();
                            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                            $extension = $request->file('assets_photo')->getClientOriginalExtension();
                            $asset_image_file = $filename.'_'.time().'.'.$extension;
                            $path = $file->move($destinationPath, $asset_image_file);
                        }else{
                            $asset_image_file = '';
                        }

                        if(isset($billfile) || $billfile != null){
                            $destinationPath = public_path().'/upload/assets/';
                            $filenameWithExt = $request->file('assets_bill_ph')->getClientOriginalName();
                            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                            $extension = $request->file('assets_bill_ph')->getClientOriginalExtension();
                            $asset_bill_file = $filename.'_'.time().'.'.$extension;
                            $path = $billfile->move($destinationPath, $asset_bill_file);
                        }else{
                            $asset_bill_file = '';
                        }

                        $timestamp = date('Y-m-d G:i:s');
                        $assets=Assets::insertGetId([
                            'asset_id'=>NULL,
                            'asset_category_id'=>$request->input('assets_category'),
                            'store_id'  =>  $request->input('store_name'),
                            'name'=>$request->input('assets_name'),
                            'brand'=>$request->input('assets_brand'),
                            'asset_bill_no'=>$request->input('assets_bill_no'),
                            'model_number'=>$request->input('assets_number'),
                            'description'=>$request->input('assets_desc'),
                            'asset_code'=>$assetcode,
                            'purchase_date'=>date("Y-m-d", strtotime($request->input('assets_purchase_date'))),
                            'asset_value'=>$request->input('assets_value'),
                            'asset_photo_upload'=>$asset_image_file,
                            'asset_bill_upload'=>$asset_bill_file,
                            'created_by'=>Auth::id(),
                            'created_at'=>$timestamp
    
                        ]);
                        if($assets==NULL) 
                        {
                           DB::rollback();
                            return redirect('/admin/assets/create')->with('error','Some Unexpected Error occurred.');
                        }
                        else{
                            
                            CustomHelpers::userActionLog($action='Create Assets',$assets,0);
                            return redirect('/admin/assets/create')->with('success','Successfully Created Assets.');
                        }
                    }
            }  catch(\Illuminate\Database\QueryException $ex) {
                return redirect('/admin/assets/create')->with('error','some error occurred'.$ex->getMessage());
            }
        }

         public function assets_list(){

            $store = Store::whereIn('id',CustomHelpers::user_store())
                    ->select('id',DB::raw('concat(store.name,"-",store.store_type) as name'))->get();
            $data=array('store'=>$store,'layout'=>'layouts.main');
            return view('admin.assets.assets_summary',$data);    
        }

        public function assets_list_api(Request $request){
            $search = $request->input('search');
            $store_name=$request->input('store_name');
            $serach_value = $search['value'];
            $start = $request->input('start');
            $limit = $request->input('length');
            $offset = empty($start) ? 0 : $start ;
            $limit =  empty($limit) ? 10 : $limit ;
            
            $store = Store::whereIn('id',CustomHelpers::user_store())
                    ->select('id')->get()->toArray();
            $userlog = Assets::leftjoin('assets_category','assets.asset_category_id','assets_category.ac_id')
             ->whereIn('assets.store_id',$store)
            ->leftJoin('store','assets.store_id','store.id')
            ->select('assets.asset_id','assets_category.category_name','assets.name','assets.brand',
            'assets.asset_bill_no','assets.model_number','assets.description','assets.asset_value','assets.asset_code','assets.asset_photo_upload','assets.asset_bill_upload','assets.allot_status',DB::raw('concat(store.name,"-",store.store_type) as store_name'));
    
            if(!empty($serach_value))
            {
                $userlog = $userlog->where('assets_category.category_name','LIKE',"%".$serach_value."%")
                            ->orwhere('name','LIKE',"%".$serach_value."%")
                            ->orwhere('brand','LIKE',"%".$serach_value."%")
                            ->orwhere('asset_bill_no','LIKE',"%".$serach_value."%")
                            ->orwhere('model_number','LIKE',"%".$serach_value."%")
                            ->orwhere('description','LIKE',"%".$serach_value."%")
                            ->orwhere('asset_value','LIKE',"%".$serach_value."%")
                            ->orwhere('asset_code','LIKE',"%".$serach_value."%")
                            ->orwhere('store.name','like',"%".$serach_value."%")
                            ;
            }
            if(!empty($store_name))
            {
               $userlog->where(function($query) use ($store_name){
                        $query->where('assets.store_id','like',"%".$store_name."%");
                    });
               
            }
    
            $count = $userlog->count();
            $userlog = $userlog->offset($offset)->limit($limit);
    
            if(isset($request->input('order')[0]['column'])){
                $data = ['asset_id','assets_category.category_name','name','brand',
                'asset_bill_no','model_number','description','asset_value','store_name','asset_code'];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $userlog->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
            {
                $userlog->orderBy('asset_id','desc');
            }
            $userlogdata = $userlog->get();
            
            $array['recordsTotal'] = $count;
            $array['recordsFiltered'] = $count ;
            $array['data'] = $userlogdata; 
            return json_encode($array);
      
        }

        public function assets_view($id){
        // $asset_category = DB::table('assets_category')->get();
        $assets=DB::table('assets')->where('asset_id',$id)
        ->leftjoin('assets_category','assets.asset_category_id','assets_category.ac_id')
        ->get()->first();
        
        $data=array('assets'=>$assets,'layout'=>'layouts.main','id'=>$id);
        return view('admin.assets.assets_view',$data); 
    }

     public function update_assets($id){
            $asset_category = DB::table('assets_category')->get();
             $store = Store::whereIn('id',CustomHelpers::user_store())
                    ->select('id',DB::raw('concat(store.name,"-",store.store_type) as name'))->get();
            $assets=Assets::where('asset_id',$id)->get()->first();
            $data=array('store'=>$store,'assets'=>$assets,'layout'=>'layouts.main','id'=>$id,'asset_category'=>$asset_category);
            return view('admin.assets.update_assets',$data);   
        }
        public function update_assetsDb(Request $request,$id){
            
            try {
                $validator = Validator::make($request->all(),
                    [
                        'assets_category'=>'required',
                        'assets_name'=>'required',
                        'assets_brand'=>'required',
                        'assets_number'=>'required',
                        'store_name'=>'required',
                        'assets_desc'=>'required',
                        'assets_bill_no'=>'required',
                        'assets_purchase_date'=>'required',
                        'assets_value'=>'required',
                    ],
                    [
                        'assets_category.required'=>'This Field is required',
                        'assets_name.required'=>'This Field is required',
                        'assets_brand.required'=>'This Field is required',
                        'assets_number.required'=>'This Field is required',
                        'store_name.required'   =>  'This Field is required',
                        'assets_desc.required'=>'This Field is required',
                        'assets_bill_no.required'=>'This Field is required',
                        'assets_purchase_date.required'=>'This Field is required',
                        'assets_value.required'=>'This Field is required',
                    ]
                    );
                    $errors = $validator->errors();
                    if ($validator->fails()) 
                    {
                        return redirect('/admin/assets/edit/'.$id)->withErrors($errors);
                    }
                    else{
                        $check_cat = Assets::where('asset_id',$id)->get()->first();
                        $assetcode="";
                        if($check_cat['asset_category_id'] == $request->input('assets_category')){
                            $assetcode = $check_cat['asset_code'];
                        }else{
                            $categoryN= AssetCategory::where('ac_id',$request->input('assets_category'))->get(['category_name'])->first();
                            $cur_id =Assets::where('asset_category_id',$request->input('assets_category'))->get([DB::raw('Count(asset_category_id) as asset_counter')])->first();
                            $as_name= substr($request->input('assets_name'),0,5);
                            $as_cat= substr($categoryN->category_name,0,3);
                            $ass_count = (($cur_id['asset_counter']+1)<10) ? "0".($cur_id['asset_counter']+1) :($cur_id['asset_counter']+1);
                            $assetcode = "ASS/".$as_cat."/".$ass_count;

                        }

                        $asset_image_file ='';
                        $asset_bill_file ='';
                        $file = $request->file('upd_assets_photo');
                        $billfile = $request->file('upd_assets_bill');
                        
                        if(!isset($file)||$file == null){
                            $asset_image_file = $request->input('old_image');
                        }else{
                            $destinationPath = public_path().'/upload/assets/';
                            $filenameWithExt = $request->file('upd_assets_photo')->getClientOriginalName();
                            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                            $extension = $request->file('upd_assets_photo')->getClientOriginalExtension();
                            $asset_image_file = $filename.'_'.time().'.'.$extension;
                            $path = $file->move($destinationPath, $asset_image_file);
                            File::delete($destinationPath.$request->input('old_image'));
                        }

                        if(!isset($billfile)||$billfile == null){
                            $asset_bill_file = $request->input('old_bill');
                        }else{
                            $destinationPath = public_path().'/upload/assets/';
                            $filenameWithExt = $request->file('upd_assets_bill')->getClientOriginalName();
                            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                            $extension = $request->file('upd_assets_bill')->getClientOriginalExtension();
                            $asset_bill_file = $filename.'_'.time().'.'.$extension;
                            $path = $billfile->move($destinationPath, $asset_bill_file);
                            File::delete($destinationPath.$request->input('old_bill'));
                        }

                        
                        $dept=Assets::where('asset_id',$id)->update([
                            
                            'asset_category_id'=>$request->input('assets_category'),
                            'name'=>$request->input('assets_name'),
                            'brand'=>$request->input('assets_brand'),
                            'store_id'  =>  $request->input('store_name'),
                            'asset_bill_no'=>$request->input('assets_bill_no'),
                            'model_number'=>$request->input('assets_number'),
                            'description'=>$request->input('assets_desc'),
                            'asset_code'=>$assetcode,
                            'purchase_date'=>date("Y-m-d", strtotime($request->input('assets_purchase_date'))),
                            'asset_value'=>$request->input('assets_value'),
                            'asset_photo_upload'=>$asset_image_file,
                            'asset_bill_upload'=>$asset_bill_file
                        ]);
                        if($dept==NULL) 
                        {
                           DB::rollback();
                           return redirect('/admin/assets/edit/'.$id)->with('error','Some Unexpected Error occurred.');
                        }
                        else{
                             
                            CustomHelpers::userActionLog($action='Assets Update',$id,0);

                            return redirect('/admin/assets/edit/'.$id)->with('success','Successfully Updated Assets.');
                        }
                    }
            }  catch(\Illuminate\Database\QueryException $ex) {
                return redirect('/admin/assets/edit/'.$id)->with('error','some error occurred'.$ex->getMessage());
            }
        }

    public function asset_issue_to_employee(){
         $store = Store::whereIn('id',CustomHelpers::user_store())
                    ->select('id',DB::raw('concat(store.name,"-",store.store_type) as name'))->get();
        $asset_category = DB::table('assets_category')->get();
        $employee = DB::table('users')->get();
        $data=array('layout'=>'layouts.main','asset_category'=>$asset_category,'emp_for_asset'=>$employee,'store'=>$store);
        return view('admin.assets.assign_assets',$data);   
     
    }

    public function filter_asset_code_api(Request $request){
        $assets=DB::table('assets')->where('asset_category_id',$request->input('assets'))
        ->where('store_id',$request->input('store'))
        ->where('allot_bit','<>',1)
        ->where('allot_status','<>','Disposed')
        ->pluck('asset_code','asset_id');
        return response()->json($assets);
    }

     public function filter_user_api(Request $request){


        $storeId=$request->input('assets');
        $assets = Users::where('id','>','0')->whereRaw(DB::raw('FIND_IN_SET('.$storeId.',users.store_id)'))
            ->pluck('name','id');


        return response()->json($assets);
    }

    public function asset_assign_form(Request $request){

        try {
            
            $this->validate($request,[
                'assets_category'=>'required',
                'assets_code'=>'required',
                'store_name'=>'required',
                'assets_emp'=>'required',
                'assets_from_date'=>'required',
                // 'assets_to_date'=>'required'
               
            ],
            [
                'assets_category.required'=>'This Field is required',
                'assets_code.required'=>'This Field is required',
                'store_name.required'=>'This Field is required',
                'assets_emp.required'=>'This Field is required',
                'assets_from_date.required'=>'This Field is required',
                // 'assets_to_date.required'=>'This Field is required'
            ]);

            $emp =Users::where('users.id',$request->input('assets_emp'))
            ->leftJoin('store','store.id','users.store_id')
            ->select('users.id',
                'users.name',
                'users.emp_id',
                'users.phone',
                DB::raw('concat(store.name,"-",store.store_type) as store_name'))->get()->first();

            $category = AssetCategory::where('ac_id',$request->input('assets_category'))->get()->first();
            $code = Assets::where('asset_id',$request->input('assets_code'))->get()->first();
            $last_id = Assets::get()->last()->asset_id;

            $store = Store::where('id',$request->input('store_name'))->get()->first();


            $auto_gen_no ="";
            if($last_id < 10){
                $auto_gen_no = "00".$last_id;
            }else if($last_id < 100){
                $auto_gen_no = "0".$last_id;
            }else{
                $auto_gen_no = $last_id;
            }
            $form_no = "PPML/ASS/".$auto_gen_no;
             
            $allprev_Assign_Asset = AssetAssign::where('employee_id',$request->input('assets_emp'))
            ->leftjoin('assets','asset_assign.asset_id','assets.asset_id')
            ->leftjoin('assets_category','asset_assign.asset_category_id','assets_category.ac_id')
            ->get()->toArray();
            
            if(isset($emp) && isset($category) && isset($code)){
                $data = [
                    'foo' => 'bar',
                    'emp'=>$emp,
                    'category'=>$category,
                    'code'=>$code,
                    'form'=>$form_no,
                    'store'=>$store,
                    'recieved'=>date("d-m-Y",strtotime($request->input('assets_from_date'))),
                    'recieved_by'=>date('d-m-Y')
                ];
                $pdfFilePath = "asset form.pdf";
                 $pdf = PDF::loadView('admin.assets.assetformTemplate',$data);
                 return $pdf->stream($pdfFilePath);
              
    
            }else{

                $message="No format exist!!";
                return redirect('/admin/assets/assign/generate/form')->with('error',$message);
        
            }
            
        } catch (\Illuminate\Database\QueryException $ex) {
            return redirect('/admin/assets/assign/generate/form')->with('error','some error occurred'.$ex->getMessage());  
        }
        
    }

      public function asset_issue_to_employeeDb(Request $request){
        try {

            $validator = Validator::make($request->all(),
                [
                    'assets_category'=>'required',
                    'assets_code'=>'required',
                    'assets_emp'=>'required',
                    'store_name'=>'required',
                    'assets_from_date'=>'required',
                    'assets_form'=>'mimes:pdf|max:'.CustomHelpers::getfilesize()
                   // 'assets_to_date'=>'required'
                    
                ],
                [
                    'assets_category.required'=>'This Field is required',
                    'assets_code.required'=>'This Field is required',
                    'assets_emp.required'=>'This Field is required',
                    'store_name.required'=>'This Field is required',
                    'assets_from_date.required'=>'This Field is required',
                    'assets_form.required_with'=>'Field accept only jpeg,png,jpg,pdf format'
                    // 'assets_to_date.required'=>'This Field is required'
                    // 'assets_form.required'=>'This field is required'
                    ]
                );
                $errors = $validator->errors();

                if ($validator->fails()) 
                {
                    return redirect('/admin/assets/assign/employee')->withErrors($errors);
                }
                else{
                    $formfile = $request->file('assets_form');
                     if(isset($formfile) || $formfile != null){
                            $destinationPath = public_path().'/upload/assets/form';
                            $filenameWithExt = $request->file('assets_form')->getClientOriginalName();
                            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                            $extension = $request->file('assets_form')->getClientOriginalExtension();
                            $asset_form = $filename.'_'.time().'.'.$extension;
                            $path = $formfile->move($destinationPath, $asset_form);
                        }else{
                            $asset_form = '';
                        }
 
                    $timestamp = date('Y-m-d G:i:s');
                    $assets=AssetAssign::insertGetId([
                        'aa_id'=>NULL,
                        'asset_category_id'=>$request->input('assets_category'),
                        'asset_id'=>$request->input('assets_code'),
                        'employee_id'=>$request->input('assets_emp'),
                        'store_id'  =>  $request->input('store_name'),
                        'from_date'=>date("Y-m-d", strtotime($request->input('assets_from_date'))),
                        'to_date'=>date("Y-m-d", strtotime($request->input('assets_to_date'))),
                        'asset_form'=>$asset_form,
                        'created_by'=>Auth::id(),
                        'created_at'=>$timestamp
                    ]);
                    $upd_asset = Assets::where('asset_id',$request->input('assets_code'))
                    ->update(['allot_bit'=>1,
                    'allot_status'=>'Assigned']);

                    if($assets==NULL) 
                    {
                       DB::rollback();
                        return redirect('/admin/assets/assign/employee')->with('error','Some Unexpected Error occurred.');
                    }
                    else{
                           
                        CustomHelpers::userActionLog($action='Asset Assign',$assets,0);
                        return redirect('/admin/assets/assign/employee')->with('success','Asset Assigned Successfully.');
                    }
                }
        }  catch(\Illuminate\Database\QueryException $ex) {
            return redirect('/master/assets/assign/employee')->with('error','some error occurred'.$ex->getMessage());
        }
    }

    public function asset_issue_to_employee_list(){

         $store = Store::whereIn('id',CustomHelpers::user_store())
                    ->select('id',DB::raw('concat(store.name,"-",store.store_type) as name'))->get();
        $data=array( 'store'=>$store,'layout'=>'layouts.main');
            return view('admin.assets.assign_asset_list',$data);
    }
    public function asset_issue_to_employee_api(Request $request){
        $search = $request->input('search');
        $store_name=$request->input('store_name');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
        
         $store = Store::whereIn('id',CustomHelpers::user_store())
                    ->select('id')->get()->toArray();
        $userlog = AssetAssign::where('assets.allot_status','<>','Disposed')
        ->leftjoin('assets_category','asset_assign.asset_category_id','assets_category.ac_id')
        ->leftjoin('assets','assets.asset_id','asset_assign.asset_id')
        ->leftjoin('users','users.id','asset_assign.employee_id')
         ->leftJoin('store','users.store_id','store.id')
        ->whereIn('users.store_id',$store)
        ->select('asset_assign.aa_id',
        'assets_category.category_name','assets.name',
        'assets.model_number','assets.asset_value','assets.asset_code'
        ,DB::raw("CONCAT(users.name,'-',users.emp_id) as employee"),
        'asset_assign.from_date','asset_assign.to_date','assets.allot_status','asset_assign.asset_form','asset_assign.status', DB::raw('concat(store.name,"-",store.store_type) as store_name'));

        if(!empty($serach_value))
        {
            $userlog = $userlog->where('assets_category.category_name','LIKE',"%".$serach_value."%")
                        ->orwhere('assets.name','LIKE',"%".$serach_value."%")
                        ->orwhere('model_number','LIKE',"%".$serach_value."%")
                        ->orwhere('asset_value','LIKE',"%".$serach_value."%")
                        ->orwhere('asset_code','LIKE',"%".$serach_value."%")
                        ->orwhere('users.name','LIKE',"%".$serach_value."%")
                        ->orwhere('users.emp_id','LIKE',"%".$serach_value."%")
                        ->orwhere('from_date','LIKE',"%".$serach_value."%")
                        ->orwhere('to_date','LIKE',"%".$serach_value."%")
                        ->orwhere('allot_status','LIKE',"%".$serach_value."%")
                        ->orwhere('asset_assign.status','LIKE',"%".$serach_value."%")
                        ->orwhere('store.name','like',"%".$serach_value."%")

                        ;
        }
         if(!empty($store_name))
            {
               $userlog->where(function($query) use ($store_name){
                        $query->where('asset_assign.store_id','like',"%".$store_name."%");
                    });
               
            }

        $count = $userlog->count();
        $userlog = $userlog->offset($offset)->limit($limit);

        if(isset($request->input('order')[0]['column'])){
            $data = ['asset_assign.aa_id','assets_category.category_name','store_name','assets.name'
            ,'assets.model_number','assets.asset_value','assets.asset_code','employee'
            ,'asset_assign.from_date','asset_assign.to_date','allot_status','asset_assign.status'];
            $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
            $userlog->orderBy($data[$request->input('order')[0]['column']], $by);
        }
        else
        {
            $userlog->orderBy('asset_assign.aa_id','desc');
        }
        $userlogdata = $userlog->get();
        
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count ;
        $array['data'] = $userlogdata; 
        return json_encode($array);
  
    }

    public function return_asset($id){
        $assets=AssetAssign::where('aa_id',$id)
        ->leftjoin('assets_category','asset_assign.asset_category_id','assets_category.ac_id')
        ->leftjoin('assets','assets.asset_id','asset_assign.asset_id')
        ->leftjoin('users','users.id','asset_assign.employee_id')
        ->select('assets_category.category_name',
            'assets.name','assets.asset_code','asset_assign.asset_id',
            DB::raw("CONCAT(users.name,'-',users.emp_id) as employee"))
        ->get()->first();
        $employee = Users::get();
        $data=array('assets'=>$assets,'layout'=>'layouts.main','id'=>$id,'employ'=>$employee);
        return view('admin.assets.return_asset',$data);   
    }  

      public function return_asset_db($id,Request $request){
         try {

            $validator = Validator::make($request->all(),
                [
                    'employee'=>'required',
                    'return_date'=>'required'
                ],
                [
                    'employee.required'=>'This Field is required',
                    'return_date.required'=>'This Field is required'
                    
                    ]
                );
                $errors = $validator->errors();

                if ($validator->fails()) 
                {
                    return redirect('/admin/asset/return/'.$id)->withErrors($errors);
                }
                else{
                    $aa=AssetAssign::where('aa_id',$id)->get()->first();
                    $timestamp = date('Y-m-d G:i:s');
                    $assets=AssetAssign::where('aa_id',$id)->update([
                        'return_to'=>$request->input('employee'),
                        'return_date'=>date("Y-m-d",strtotime($request->input('return_date'))),
                        'status'=>'Returned',
                        'updated_at'=>$timestamp
                    ]);
                    $upd_asset =Assets::where('asset_id',$aa['asset_id'])
                    ->update(['allot_bit'=>0,
                    'allot_status'=>'not assign']);

                    if($assets==NULL) 
                    {
                       DB::rollback();
                        return redirect('/admin/assets/assign/employee/list')->with('error','Some Unexpected Error occurred.');
                    }
                    else{
                        
                        CustomHelpers::userActionLog($action='Asset Return',$id,0);
                        return redirect('/admin/assets/assign/employee/list')->with('success','Asset Returned Successfully on '.date("d-m-Y",strtotime($request->input('return_date'))).'.');
                    }
                }
        }  catch(\Illuminate\Database\QueryException $ex) {
            return redirect('/admin/assets/assign/employee/list')->with('error','some error occurred'.$ex->getMessage());
        } 
    }  

    public function asset_disposal(){
        $asset_category = DB::table('assets_category')
        ->get();
         $store = Store::whereIn('id',CustomHelpers::user_store())
                    ->select('id',DB::raw('concat(store.name,"-",store.store_type) as name'))->get();
        $data=array('layout'=>'layouts.main','asset_category'=>$asset_category,'store'=>$store);
        return view('admin.assets.asset_disposal',$data);   
     
    }

    public function asset_disposal_db(Request $request){
        try {
            $validator = Validator::make($request->all(),
                [
                    'assets_category'=>'required',
                    'assets_code'=>'required',
                    'assets_disposal_date'=>'required|before:tomorrow|after:1900-01-01',
                    'assets_disposal_to'=>'required',
                    'store_name'=>'required',
                    'assets_disposal_reason'=>'required'
                   
                ],
                [
                    'assets_category.required'=>'This Field is required',
                    'assets_code.required'=>'This Field is required',
                    'assets_disposal_date.required'=>'This Field is required',
                    'assets_disposal_to.required'=>'This Field is required',
                    'store_name.required'   =>  'This Field is required',
                    'assets_disposal_reason.required'=>'This Field is required'
                    ]
                );
                $errors = $validator->errors();

                if ($validator->fails()) 
                {
                    return redirect('/admin/assets/disposal')->withErrors($errors);
                }
                else{

                    $timestamp = date('Y-m-d G:i:s');
                    $assets=AssetDisposal::insertGetId([
                        'ad_id'=>NULL,
                        'asset_category_id'=>$request->input('assets_category'),
                        'asset_id'=>$request->input('assets_code'),
                        'store_id'  =>  $request->input('store_name'),
                        'disposal_on'=>date('Y-m-d',strtotime($request->input('assets_disposal_date'))),
                        'disposal_to'=>$request->input('assets_disposal_to'),
                        'disposal_reason'=>$request->input('assets_disposal_reason'),
                        'created_by'=>Auth::id(),
                        'created_at'=>$timestamp
                    ]);
                    $upd_asset =Assets::where('asset_id',$request->input('assets_code'))
                    ->update(['allot_bit'=>0,
                    'allot_status'=>'Disposed']);
                    if($assets==NULL) 
                    {
                       DB::rollback();
                        return redirect('/admin/assets/disposal')->with('error','Some Unexpected Error occurred.');
                    }
                    else{
                        
                        CustomHelpers::userActionLog($action='Asset Disposed',$assets,0);
                        return redirect('/admin/assets/disposal')->with('success','Asset Disposed Successfully.');
                    }
                }
        }  catch(\Illuminate\Database\QueryException $ex) {
            return redirect('/admin/assets/disposal')->with('error','some error occurred'.$ex->getMessage());
        }
    } 

    public function asset_disposal_list(){

         $store = Store::whereIn('id',CustomHelpers::user_store())
                    ->select('id',DB::raw('concat(store.name,"-",store.store_type) as name'))->get();

        $data=array('store'=>$store,'layout'=>'layouts.main');
        return view('admin.assets.asset_disposal_list',$data); 
    }
    public function asset_disposal_list_api(Request $request){
        $search = $request->input('search');
        $store_name=$request->input('store_name');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
        
        $store = Store::whereIn('id',CustomHelpers::user_store())
                    ->select('id')->get()->toArray();
        $userlog = AssetDisposal::leftjoin('assets_category','asset_disposal.asset_category_id','assets_category.ac_id')
        ->leftjoin('assets','assets.asset_id','asset_disposal.asset_id')
        ->whereIn('asset_disposal.store_id',$store)
        ->leftJoin('store','assets.store_id','store.id')
        ->leftjoin('users','users.id','asset_disposal.disposal_to')
        ->select('asset_disposal.ad_id',
        'assets_category.category_name','assets.asset_code',
        DB::raw("CONCAT(assets.name,'-',assets.model_number) as asset"),
        'assets.asset_value',
        // 'asset_disposal.disposal_to as employee',
         DB::raw('concat(users.name," ",ifnull( users.middle_name," ")," ",ifnull( users.last_name," ")) as employee'),
        DB::raw('concat(store.name,"-",store.store_type) as store_name'),
        'asset_disposal.disposal_on','asset_disposal.disposal_reason');

        if(!empty($serach_value))
        {
            $userlog = $userlog->where('assets_category.category_name','LIKE',"%".$serach_value."%")
                        ->orwhere('assets.name','LIKE',"%".$serach_value."%")
                        ->orwhere('assets.model_number','LIKE',"%".$serach_value."%")
                        ->orwhere('assets.asset_value','LIKE',"%".$serach_value."%")
                        ->orwhere('assets.asset_code','LIKE',"%".$serach_value."%")
                        // ->orwhere('asset_disposal.disposal_to','LIKE',"%".$serach_value."%")
                        ->orwhere('users.name','LIKE',"%".$serach_value."%")
                        ->orwhere('disposal_on','LIKE',"%".$serach_value."%")
                        ->orwhere('disposal_to','LIKE',"%".$serach_value."%")
                        ->orwhere('disposal_reason','LIKE',"%".$serach_value."%")
                        ;
        }
        if(!empty($store_name))
            {
               $userlog->where(function($query) use ($store_name){
                        $query->where('asset_disposal.store_id','like',"%".$store_name."%");
                    });
               
            }

        $count = $userlog->count();
        $userlog = $userlog->offset($offset)->limit($limit);

        if(isset($request->input('order')[0]['column'])){
            $data = ['asset_disposal.ad_id','assets_category.category_name',
            'asset','assets.asset_value','store_name','assets.asset_code','employee'
            ,'asset_disposal.disposal_on',
            'asset_disposal.disposal_reason'];
            $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
            $userlog->orderBy($data[$request->input('order')[0]['column']], $by);
        }
        else
        {
            $userlog->orderBy('asset_disposal.ad_id','desc');
        }
        $userlogdata = $userlog->get();
        
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count ;
        $array['data'] = $userlogdata; 
        return json_encode($array);
  
    }
     public function create_assets_category() {
         return view('admin.assets.assets_category',['layout' => 'layouts.main']);
    } 

        public function create_assets_categoryDb(Request $request) {
       // print_r($request->input());
          try {
            $timestamp=date('Y-m-d G:i:s');
            $this->validate($request,[
                'category_name'=>'required',

            ],[
                'category_name.required'=> 'This is required.'  
            ]);
            $customer = AssetCategory::insertGetId(
                [
                    'ac_id'=>NULL,
                    'category_name'=>$request->input('category_name')
                ]
            );
            if($customer==NULL) 
            {
                DB::rollback();
                return redirect('/admin/assets/category')->with('error','Some Unexpected Error occurred.');
            }
            else{
                CustomHelpers::userActionLog($action='Create Assets category',$customer,0);
                return redirect('/admin/assets/category')->with('success','Successfully Created Assets category.'); 
             }    
        
    }  catch(\Illuminate\Database\QueryException $ex) {
        return redirect('/admin/assets/category')->with('error','some error occurred'.$ex->getMessage());
    }
    }

    public function assets_category_list(){
         return view('admin.assets.assets_category_list',['layout' => 'layouts.main']);
    }
    
    public function assets_category_list_api(Request $request) {
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
        
      $api_data= AssetCategory::
                        select(
                            'ac_id',
                            'category_name'
                            );
            if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('assets_category.ac_id','like',"%".$serach_value."%")
                    ->orwhere('assets_category.category_name','like',"%".$serach_value."%")
                    ;
                });
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                    'ac_id',
                    'category_name'
                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('assets_category.ac_id','asc');      
        
        $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }
}


