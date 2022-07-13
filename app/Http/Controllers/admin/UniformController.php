<?php

namespace App\Http\Controllers\admin;
use Illuminate\Http\Request;
use \stdClass;
use \App\Model\Store;
use \App\Custom\CustomHelpers;

use App\Model\Users;
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
use\App\Model\UserDetails;
use\App\Model\Uniform;
use\App\Model\UniformIssue;
use Config;
use Mail;


class UniformController extends Controller {
    public function __construct() {
        //$this->middleware('auth');
    }

    public function uniform_unique_bill_no(Request $request){
        $x=$request->input('bill');
        $billno = Uniform::where('uniform_bill_no',$x)->get()->first();
        $result =0;
        if(isset($billno)){
            $result=1;
        }
        return response()->json($result);
    }

    public function create_uniform(){
            $store = Store::whereIn('id',CustomHelpers::user_store())
                    ->select('id',DB::raw('concat(store.name,"-",store.store_type) as name'))->get();
            $data=array('layout'=>'layouts.main','store'    =>  $store->toArray());
            return view('admin.uniform.create_uniform',$data);   
        }

     public function create_uniformDb(Request $request){
            try {
                $validator = Validator::make($request->all(),
                    [
                        'uniform_name'=>'required',
                        'uniform_size'=>'required',
                        'uniform_color'=>'required',
                        'uniform_desc'=>'required',
                        'uniform_bill_no'=>'required',
                        'store_name'=>'required',
                        'uniform_purchase_date'=>'required|before:tomorrow|after:1900-01-01',
                        'uniform_value'=>'required',
                        'uniform_photo' => 'required_with|image|mimes:jpeg,png,jpg,gif,svg|max:'.CustomHelpers::getfilesize(),
                        'uniform_bill_ph' => 'mimes:jpeg,png,jpg,gif,pdf,svg|max:'.CustomHelpers::getfilesize()
                    ],
                    [
                        'uniform_name.required'=>'This Field is required',
                        'uniform_size.required'=>'This Field is required',
                        'uniform_color.required'=>'This Field is required',
                        'uniform_desc.required'=>'This Field is required',
                        'uniform_bill_no.required'=>'This Field is required',
                        'uniform_purchase_date.required'=>'This Field is required',
                        'uniform_value.required'=>'This Field is required',
                        'uniform_photo.required_with'=>'Image accept only jpeg,png,jpg format',
                        'uniform_bill_ph.required_with'=>'Field accept only jpeg,png,jpg,pdf format'
                    ]
                    );
                    $errors = $validator->errors();

                    if ($validator->fails()) 
                    {
                        return redirect('/admin/uniform/create')->withErrors($errors);
                    }
                    else{
                       
                        
                        $id = Uniform::orderBy('uniform_id', 'DESC')->first();
                       
                        if($id==null){
                            $last_id=1;
                        }else{
                             $last_id=$id->uniform_id+1;
                        }

                        $uniformcode = "UNI/".$last_id;
                        
                        $uniform_image_file = '';
                        $uniform_bill_file = '';
                        $file = $request->file('uniform_photo');
                        $billfile = $request->file('uniform_bill_ph');
                        if(isset($file) || $file != null){
                            $destinationPath = public_path().'/upload/uniform/';
                            $filenameWithExt = $request->file('uniform_photo')->getClientOriginalName();
                            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                            $extension = $request->file('uniform_photo')->getClientOriginalExtension();
                            $uniform_image_file = $filename.'_'.time().'.'.$extension;
                            $path = $file->move($destinationPath, $uniform_image_file);
                        }else{
                            $uniform_image_file = '';
                        }

                        if(isset($billfile) || $billfile != null){
                            $destinationPath = public_path().'/upload/uniform/';
                            $filenameWithExt = $request->file('uniform_bill_ph')->getClientOriginalName();
                            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                            $extension = $request->file('uniform_bill_ph')->getClientOriginalExtension();
                            $uniform_bill_file = $filename.'_'.time().'.'.$extension;
                            $path = $billfile->move($destinationPath, $uniform_bill_file);
                        }else{
                            $uniform_bill_file = '';
                        }

                        $timestamp = date('Y-m-d G:i:s');
                        $uniform=Uniform::insertGetId([
                            'uniform_id'=>NULL,
                            'store_id'  =>  $request->input('store_name'),
                            'name'=>$request->input('uniform_name'),
                            'size'=>$request->input('uniform_size'),
                            'uniform_bill_no'=>$request->input('uniform_bill_no'),
                            'color'=>$request->input('uniform_color'),
                            'description'=>$request->input('uniform_desc'),
                            'uniform_code'=>$uniformcode,
                            'purchase_date'=>date("Y-m-d", strtotime($request->input('uniform_purchase_date'))),
                            'uniform_value'=>$request->input('uniform_value'),
                            'uniform_photo_upload'=>$uniform_image_file,
                            'uniform_bill_upload'=>$uniform_bill_file,
                            'created_by'=>Auth::id(),
                            'created_at'=>$timestamp
    
                        ]);
                        if($uniform==NULL) 
                        {
                           DB::rollback();
                            return redirect('/admin/uniform/create')->with('error','Some Unexpected Error occurred.');
                        }
                        else{
                               
                            CustomHelpers::userActionLog($action='Create Uniform',$uniform,0);
                            return redirect('/admin/uniform/create')->with('success','Successfully Created Uniform.');
                        }
                    }
            }  catch(\Illuminate\Database\QueryException $ex) {
                return redirect('/admin/uniform/create')->with('error','some error occurred'.$ex->getMessage());
            }
        }

        public function uniform_list(){

            $store = Store::whereIn('id',CustomHelpers::user_store())
                    ->select('id',DB::raw('concat(store.name,"-",store.store_type) as name'))->get();
            $data=array('store'=>$store,'layout'=>'layouts.main');
            return view('admin.uniform.uniform_summary',$data);    
        }

        public function uniform_list_api(Request $request){
            $search = $request->input('search');
            $store_name=$request->input('store_name');
            $serach_value = $search['value'];
            $start = $request->input('start');
            $limit = $request->input('length');
            $offset = empty($start) ? 0 : $start ;
            $limit =  empty($limit) ? 10 : $limit ;
            
            $store = Store::whereIn('id',CustomHelpers::user_store())
                    ->select('id')->get()->toArray();
            $userlog = Uniform::whereIn('uniform.store_id',$store)
            ->leftJoin('store','uniform.store_id','store.id')
            ->select('uniform.uniform_id','uniform.name','uniform.size',
            'uniform.uniform_bill_no','uniform.color','uniform.description','uniform.uniform_value','uniform.uniform_code','uniform.uniform_photo_upload','uniform.uniform_bill_upload','uniform.allot_status',DB::raw('concat(store.name,"-",store.store_type) as store_name'));
    
            if(!empty($serach_value))
            {
                $userlog = $userlog->where('uniform.name','LIKE',"%".$serach_value."%")
                            ->orwhere('size','LIKE',"%".$serach_value."%")
                            ->orwhere('uniform_bill_no','LIKE',"%".$serach_value."%")
                            ->orwhere('color_number','LIKE',"%".$serach_value."%")
                            ->orwhere('description','LIKE',"%".$serach_value."%")
                            ->orwhere('uniform_value','LIKE',"%".$serach_value."%")
                            ->orwhere('uniform_code','LIKE',"%".$serach_value."%")
                            ->orwhere('store.name','like',"%".$serach_value."%")
                            ;
            }
            if(!empty($store_name))
            {
               $userlog->where(function($query) use ($store_name){
                        $query->where('uniform.store_id','like',"%".$store_name."%");
                    });
               
            }
    
            $count = $userlog->count();
            $userlog = $userlog->offset($offset)->limit($limit);
    
            if(isset($request->input('order')[0]['column'])){
                $data = ['uniform_code','store_name','name','color','size',
                'uniform_bill_no','description','uniform_value','allot_status','uniform_photo_upload','uniform_bill_upload'];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $userlog->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
            {
                $userlog->orderBy('uniform_id','desc');
            }
            $userlogdata = $userlog->get();
            
            $array['recordsTotal'] = $count;
            $array['recordsFiltered'] = $count ;
            $array['data'] = $userlogdata; 
            return json_encode($array);
      
        }

     public function uniform_issue_to_employee(){
         $store = Store::whereIn('id',CustomHelpers::user_store())
                    ->select('id',DB::raw('concat(store.name,"-",store.store_type) as name'))->get();
        $employee = Users::get();
        $data=array('layout'=>'layouts.main','emp_for_asset'=>$employee,'store'=>$store);
        return view('admin.uniform.uniform_assign',$data);  
     }

    public function filter_uniform_code_api(Request $request){
        $assets=Uniform::where('store_id',$request->input('assets'))
        ->where('allot_bit','<>',1)
        ->where('allot_status','<>','Disposed')
        ->pluck('uniform_code','uniform_id');
        return response()->json($assets);
    }

     public function filter_user_api(Request $request){


        $storeId=$request->input('assets');
        // $assets = Users::where('id','>','0')->whereRaw(DB::raw('FIND_IN_SET('.$storeId.',users.store_id)'))
        //     ->pluck('name','id');
         $assets = Users::select(
                            DB::raw('concat(users.name," ",ifnull( users.middle_name," ")," ",ifnull( users.last_name," ")) as name'),
                            'id'
                        )->pluck('name','id');
        return response()->json($assets);
    }

     public function uniform_issue_to_employeeDb(Request $request){
        try {

            $validator = Validator::make($request->all(),
                [
                    'uniform_code'=>'required',
                    'uniform_emp'=>'required',
                    'store_name'=>'required',
                    'uniform_form'=>'mimes:pdf|max:'.CustomHelpers::getfilesize()
                    
                ],
                [
                    'uniform_code.required'=>'This Field is required',
                    'uniform_emp.required'=>'This Field is required',
                    'store_name.required'=>'This Field is required',
                    'uniform_form.required_with'=>'Field accept only jpeg,png,jpg,pdf format'
                  
                    ]
                );
                $errors = $validator->errors();

                if ($validator->fails()) 
                {
                    return redirect('/admin/uniform/issue/employee')->withErrors($errors);
                }
                else{
                    $formfile = $request->file('uniform_form');
                     if(isset($formfile) || $formfile != null){
                            $destinationPath = public_path().'/upload/uniform/form';
                            $filenameWithExt = $request->file('uniform_form')->getClientOriginalName();
                            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                            $extension = $request->file('uniform_form')->getClientOriginalExtension();
                            $asset_form = $filename.'_'.time().'.'.$extension;
                            $path = $formfile->move($destinationPath, $asset_form);
                        }else{
                            $asset_form = '';
                        }
 
                    $timestamp = date('Y-m-d G:i:s');
                    $assets=UniformIssue::insertGetId([
                        'id'=>NULL,
                        'uniform_id'=>$request->input('uniform_code'),
                        'employee_id'=>$request->input('uniform_emp'),
                        'store_id'  =>  $request->input('store_name'),
                        'uniform_form'=>$asset_form,
                        'created_by'=>Auth::id(),
                        'created_at'=>$timestamp
                    ]);
                    $upd_asset = Uniform::where('uniform_id',$request->input('uniform_code'))
                    ->update(['allot_bit'=>1,
                    'allot_status'=>'Assigned']);

                    if($assets==NULL) 
                    {
                       DB::rollback();
                        return redirect('/admin/uniform/issue/employee')->with('error','Some Unexpected Error occurred.');
                    }
                    else{
                        
                        CustomHelpers::userActionLog($action='Uniform Issued',$assets,0);
                        return redirect('/admin/uniform/issue/employee')->with('success','Uniform Issued Successfully.');
                    }
                }
        }  catch(\Illuminate\Database\QueryException $ex) {
            return redirect('/master/uniform/issue/employee')->with('error','some error occurred'.$ex->getMessage());
        }
    }

    public function uniform_issue_to_employee_list(){

         $store = Store::whereIn('id',CustomHelpers::user_store())
                    ->select('id',DB::raw('concat(store.name,"-",store.store_type) as name'))->get();
        $data=array( 'store'=>$store,'layout'=>'layouts.main');
            return view('admin.uniform.uniform_issue_list',$data);
    }
    public function uniform_issue_to_employee_api(Request $request){
        $search = $request->input('search');
        $store_name=$request->input('store_name');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
        
         $store = Store::whereIn('id',CustomHelpers::user_store())
                    ->select('id')->get()->toArray();
        $userlog = UniformIssue::where('uniform.allot_status','<>','Disposed')
        ->leftjoin('uniform','uniform.uniform_id','uniform_issue.uniform_id')
        ->leftjoin('users','users.id','uniform_issue.employee_id')
         ->leftJoin('store','users.store_id','store.id')
        ->whereIn('users.store_id',$store)
        ->select('uniform_issue.id',
        'uniform.name','uniform.color','uniform.size','uniform.uniform_value','uniform.uniform_code'
        ,DB::raw("CONCAT(users.name,'-',users.emp_id) as employee"),
        'uniform.allot_status','uniform_issue.uniform_form','uniform_issue.status', DB::raw('concat(store.name,"-",store.store_type) as store_name'));

        if(!empty($serach_value))
        {
            $userlog = $userlog->where('uniform.name','LIKE',"%".$serach_value."%")
                        ->orwhere('color','LIKE',"%".$serach_value."%")
                        ->orwhere('uniform_value','LIKE',"%".$serach_value."%")
                        ->orwhere('uniform_code','LIKE',"%".$serach_value."%")
                        ->orwhere('users.name','LIKE',"%".$serach_value."%")
                        ->orwhere('users.emp_id','LIKE',"%".$serach_value."%")
                        ->orwhere('allot_status','LIKE',"%".$serach_value."%")
                        ->orwhere('uniform_issue.status','LIKE',"%".$serach_value."%")
                        ->orwhere('store.name','like',"%".$serach_value."%")

                        ;
        }
         if(!empty($store_name))
            {
               $userlog->where(function($query) use ($store_name){
                        $query->where('uniform_issue.store_id','like',"%".$store_name."%");
                    });
               
            }

        $count = $userlog->count();
        $userlog = $userlog->offset($offset)->limit($limit);

        if(isset($request->input('order')[0]['column'])){
            $data = ['employee','uniform.uniform_code','store_name','uniform.name','uniform.color','uniform.size','uniform.uniform_value','uniform_issue.status','uniform_issue.uniform_form'];
            $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
            $userlog->orderBy($data[$request->input('order')[0]['column']], $by);
        }
        else
        {
            $userlog->orderBy('uniform_issue.id','desc');
        }
        $userlogdata = $userlog->get();
        
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count ;
        $array['data'] = $userlogdata; 
        return json_encode($array);
  
    }

    public function uniform_view($id){
        $uniform=Uniform::where('uniform_id',$id)
                 ->leftJoin('store','store.id','uniform.store_id')
                 ->select('uniform.*',DB::raw('concat(store.name,"-",store.store_type) as store_name'))->get()->first();
        
        if($uniform){
            $data=array('uniform'=>$uniform,'layout'=>'layouts.main','id'=>$id);
        return view('admin.uniform.uniform_view',$data); 
        }else{
          return redirect('/admin/uniform/list/')->with('error','Uniform not Exist.');
        }    
    }

     public function update_uniform($id){

        $store = Store::whereIn('id',CustomHelpers::user_store())
                    ->select('id',DB::raw('concat(store.name,"-",store.store_type) as name'))->get();
        $uniform=Uniform::where('uniform_id',$id)->get()->first();
        
        if($uniform){
           $data=array('store'=>$store,'uniform'=>$uniform,'layout'=>'layouts.main','id'=>$id);
            return view('admin.uniform.update_uniform',$data);  
        }else{
          return redirect('/admin/uniform/list/')->with('error','Uniform not Exist.');
        } 

    }

    public function update_uniformDb(Request $request,$id){
            
            try {
                $validator = Validator::make($request->all(),
                    [
                        'uniform_name'=>'required',
                        'uniform_size'=>'required',
                        'uniform_color'=>'required',
                        'store_name'=>'required',
                        'uniform_desc'=>'required',
                        'uniform_bill_no'=>'required',
                        'uniform_purchase_date'=>'required',
                        'uniform_value'=>'required',
                    ],
                    [
                        'uniform_name.required'=>'This Field is required',
                        'uniform_size.required'=>'This Field is required',
                        'uniform_color.required'=>'This Field is required',
                        'store_name.required'   =>  'This Field is required',
                        'uniform_desc.required'=>'This Field is required',
                        'uniform_bill_no.required'=>'This Field is required',
                        'uniform_purchase_date.required'=>'This Field is required',
                        'uniform_value.required'=>'This Field is required',
                    ]
                    );
                    $errors = $validator->errors();
                    if ($validator->fails()) 
                    {
                        return redirect('/admin/uniform/edit/'.$id)->withErrors($errors);
                    }
                    else{
                       
                        $asset_image_file ='';
                        $asset_bill_file ='';
                        $file = $request->file('upd_uniform_photo');
                        $billfile = $request->file('upd_uniform_bill');
                        
                        if(!isset($file)||$file == null){
                            $asset_image_file = $request->input('old_image');
                        }else{
                            $destinationPath = public_path().'/upload/uniform/';
                            $filenameWithExt = $request->file('upd_uniform_photo')->getClientOriginalName();
                            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                            $extension = $request->file('upd_uniform_photo')->getClientOriginalExtension();
                            $asset_image_file = $filename.'_'.time().'.'.$extension;
                            $path = $file->move($destinationPath, $asset_image_file);
                            File::delete($destinationPath.$request->input('old_image'));
                        }

                        if(!isset($billfile)||$billfile == null){
                            $asset_bill_file = $request->input('old_bill');
                        }else{
                            $destinationPath = public_path().'/upload/uniform/';
                            $filenameWithExt = $request->file('upd_uniform_bill')->getClientOriginalName();
                            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                            $extension = $request->file('upd_uniform_bill')->getClientOriginalExtension();
                            $asset_bill_file = $filename.'_'.time().'.'.$extension;
                            $path = $billfile->move($destinationPath, $asset_bill_file);
                            File::delete($destinationPath.$request->input('old_bill'));
                        }

                        
                        $dept=Uniform::where('uniform_id',$id)->update([
                            
                            'name'=>$request->input('uniform_name'),
                            'size'=>$request->input('uniform_size'),
                            'store_id'  =>  $request->input('store_name'),
                            'uniform_bill_no'=>$request->input('uniform_bill_no'),
                            'color'=>$request->input('uniform_color'),
                            'description'=>$request->input('uniform_desc'),
                            'purchase_date'=>date("Y-m-d", strtotime($request->input('uniform_purchase_date'))),
                            'uniform_value'=>$request->input('uniform_value'),
                            'uniform_photo_upload'=>$asset_image_file,
                            'uniform_bill_upload'=>$asset_bill_file
                        ]);
                        if($dept==NULL) 
                        {
                           DB::rollback();
                           return redirect('/admin/uniform/edit/'.$id)->with('error','Some Unexpected Error occurred.');
                        }
                        else{ 

                            CustomHelpers::userActionLog($action='Uniform Update',$id,0); 
                            return redirect('/admin/uniform/edit/'.$id)->with('success','Successfully Updated Uniform.');
                        }
                    }
            }  catch(\Illuminate\Database\QueryException $ex) {
                return redirect('/admin/uniform/edit/'.$id)->with('error','some error occurred'.$ex->getMessage());
            }
        }
}

