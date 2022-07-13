<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;
use \App\Model\Store;
use Illuminate\Support\Facades\Validator;
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
use \App\Model\Sale;
use \App\Model\Factory;
use \App\Model\PartShortage;
use \App\Model\ShortageDetails;
use \App\Model\Locality;
use \App\Model\VehicleAddon;
use \App\Model\VehicleAddonStock;
use \App\Model\UnloadAddon;
use \App\Model\CustomerDetails;
use \App\Model\FinanceCompany;
use \App\Model\SellingDealer;
use \App\Model\SubDealerMaster;
use \App\Http\Controllers\Product;
use DB;
use Auth;
use Hash;
use \Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\Import;
use App\Model\SmPending;
use Mail;
class Customer extends Controller
{
    public function dashboard(){
        return view('admin.dashboard',['layout' => 'layouts.main']);
    }

    //For fetching states
    public function getStates($id)
    {
        $states = State::where("country_id",$id)
                    ->pluck("name","id");
        return response()->json($states);
    }

    //For fetching cities
    public function getCities($id)
    {
        $cities= City::where("state_id",$id)
                    ->pluck("city","id");
        return response()->json($cities);
    }
    public function getLocation($id)
    {
        $local= Locality::where("city_id",$id)
                    ->pluck("name","id");
        return response()->json($local);
    }
    
    public function registration(Request $request){
        $for = 'sale';
        if($request->input('for') != 'sale'){
            $for = $request->input('for');
        }
        $countries= Countries::get();
        $data = array(
            'for'   =>  $for,
            'countries'=>$countries,
            'layout'=>'layouts.main'
        );
        return view('admin.CustomerRegistration',$data);
    }

    public function registration_Db(Request $request){        
        try {
            $timestamp=date('Y-m-d G:i:s');
            $val = []; $msg = [];
            if($request->input('cust_for') == 'sale'){
                $val['aadhar'] =    'required_without_all:voter';
                $val['voter'] =    'required_without_all:aadhar';
                $msg['aadhar.required_without_all'] = 'This Field is required, When You Not Fill Voter Number.';
                $msg['voter.required_without_all'] = 'This Field is required, When You Not Fill Aadhar Number.';
            }
            $this->validate($request,array_merge($val,[
                'name'=>'required',
                'number'=>'required',
                'relation_type'=>'required_if:cust_for,sale',
                'relation'=>'required_if:cust_for,sale',
                // 'email'=>'required',
                'address'=>'required',
                // 'password'=>'required',
                'pin_code'=>'required_if:cust_for,sale',
                'country'=>'required',
                'state'=>'required',
                'city'=>'required',
                'location'=>'required',

            ]),array_merge($msg,[
                'name.required'=> 'This is required.',
                'number.required'=> 'This is required.',
                'relation_type.required_if'=> 'This Field is required.',
                'relation.required_if'=> 'This Field is required.',
                // 'email.required'=> 'This is required.',
                'address.required'=> 'This is required.',
                'pin_code.required_if'=> 'This Field is required.',
                'country.required'=> 'This is required.',
                'state.required'=> 'This is required.',
                'city.required'=> 'This is required.',
                'location.required'=> 'This is required.',
                // 'password.required'=> 'This is required.'   
            ]));
            if(!empty($val)){
                $check = CustomHelpers::checkCustomer(null,$request->input('aadhar'),$request->input('voter'));
                if(!empty($check))
                {
                    return back()->with('error','Error, Cutomer Already Exist for this Aadhar Number OR Voter Number')->withInput();
                }
            }
            
            $customer = Customers::insertGetId(
                [
                    'id'=>NULL,
                    'aadhar_id'=>$request->input('aadhar'),
                    'voter_id'=>$request->input('voter'),
                    'name'=>CustomHelpers::capitalize($request->input('name'),'name'),
                    'mobile'=>$request->input('number'),
                    'relation_type'=>$request->input('relation_type'),
                    'relation'=>CustomHelpers::capitalize($request->input('relation'),'name'),
                    'email_id'=>$request->input('email'),
                    // 'password'=>Hash::make($request->input('password')),
                    'country'=>$request->input('country'),
                    'state'=>$request->input('state'),
                    'city'=>$request->input('city'),
                    'location'=>$request->input('location'),
                    'address'=>CustomHelpers::capitalize($request->input('address'),'sentence'),
                    'reference'=>CustomHelpers::capitalize($request->input('reference'),'name'),
                    'pin_code'=>$request->input('pin_code'),
                    'created_by'=>Auth::id(),
                    'created_at'=>$timestamp,
                ]
            );
            if($customer==NULL) 
            {
                DB::rollback();
                return back()->with('error','Some Unexpected Error occurred.')->withInput();
            }
            else{
                return back()->with('success','Successfully Created Customer Form.'); 
             }    
        
        }  catch(\Illuminate\Database\QueryException $ex) {
            return back()->with('error','some error occurred'.$ex->getMessage())->withInput();
        }
    }

    public function customer_list() {
        $data=array('layout'=>'layouts.main');
        return view('admin.customer_list', $data); 
    }

    public function customer_data(Request $request){
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
        
      $api_data= Customers::leftJoin('states','customer.state','states.id')
                            ->leftJoin('cities','customer.city','cities.id')
                            ->leftJoin('countries','customer.country','countries.id')
            ->select(
                'customer.id',
                'customer.aadhar_id',
                'customer.voter_id',
                'customer.name',
                'customer.email_id',
                'customer.mobile',
                'customer.country',
                'customer.state',
                'customer.city',
                'customer.address',
                'customer.reference',
                'states.name as sname',
                'countries.name as cname',
                'cities.city as city_name',
                'customer.created_at'
            );
            if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('customer.name','like',"%".$serach_value."%")
                    ->orwhere('customer.aadhar_id','like',"%".$serach_value."%")
                    ->orwhere('customer.voter_id','like',"%".$serach_value."%")
                    ->orwhere('customer.email_id','like',"%".$serach_value."%")
                    ->orwhere('customer.mobile','like',"%".$serach_value."%")
                    ->orwhere('states.name','like',"%".$serach_value."%")
                    ->orwhere('countries.name','like',"%".$serach_value."%")
                    ->orwhere('cities.city','like',"%".$serach_value."%")
                    ->orwhere('customer.created_at','like',"%".$serach_value."%")
                    
                    ;
                });
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                'customer.name',
                'customer.mobile',
                'customer.aadhar_id',
                'customer.voter_id',
                'customer.email_id',
                'sname',
                'city_name',
                'customer.address',
                'customer.reference',
                'customer.created_at'
                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('customer.id','desc');      
        
        $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

    public function customer_update($id) {
        $countries= Countries::all();
        $customerdata = Customers::leftJoin('states','customer.state','=','states.id')
                        ->leftJoin('cities','customer.city','=','cities.id')
                        ->leftJoin('countries','customer.country','=','countries.id')
                        ->leftJoin('locality','customer.location','=','locality.id')
                        ->get(['customer.*','states.name as sname','cities.city as city_name',
                                'countries.name as cname',
                                'locality.name as location_name'])->where('id',$id);
        $sale = Sale::where('customer_id',$id)->first();
        $flag=0;
        if(empty($sale))
        {
            $flag = 1;
        }
        $data = array(
            'countries'=>$countries,
            'customerdata'=>$customerdata,
            'sale'  =>  $sale,
            'flag'  =>  $flag,
            'layout'=>'layouts.main'
        );
        return view('admin.update_customer',$data);
    }

    public function customer_update_Db(Request $request,$id){
         try {
            DB::beginTransaction();
            $val = []; $msg = [];
            if($request->input('cust_for') == 'sale'){
                $val['aadhar'] =    'required_without_all:voter';
                $val['voter'] =    'required_without_all:aadhar';
                $msg['aadhar.required_without_all'] = 'This Field is required, When You Not Fill Voter Number.';
                $msg['voter.required_without_all'] = 'This Field is required, When You Not Fill Aadhar Number.';
            }
            $timestamp=date('Y-m-d G:i:s');
            $this->validate($request,array_merge($val,[
                'name'=>'required',
                'number'=>'required',
                'relation_type'=>'required_if:cust_for,sale',
                'relation'=>'required_if:cust_for,sale',
                // 'email'=>'required',
                'address'=>'required',
                'pin_code'=>'required_if:cust_for,sale',
                'country'=>'required',
                'state'=>'required',
                'city'=>'required',
                'location'=>'required',

            ]),array_merge($msg,[
                'name.required'=> 'This is required.',
                'number.required'=> 'This is required.',
                'relation_type.required_if'=> 'This Field is required.',
                'relation.required_if'=> 'This Field is required.',
                // 'email.required'=> 'This is required.',
                'address.required'=> 'This Field is required.',
                'pin_code.required_if'=> 'This Field is required.',
                'country.required'=> 'This Field is required.',
                'state.required'=> 'This Field is required.',
                'city.required'=> 'This Field is required.',
                'location.required'=> 'This is required.'   
            ]));
            $sale = Sale::where('customer_id',$id)->first();
            $cust = Customers::where('id',$id)->first();
            
            $data  = [
                // 'name'=>$request->input('name'),
                'mobile'=>$request->input('number'),
                'email_id'=>$request->input('email'),
                'relation_type'=>$request->input('relation_type'),
                'relation'=>CustomHelpers::capitalize($request->input('relation'),'name'),
                'country'=>$request->input('country'),
                'state'=>$request->input('state'),
                'city'=>$request->input('city'),
                'location'=>$request->input('location'),
                'address'=>CustomHelpers::capitalize($request->input('address'),'sentence'),
                'reference'=>CustomHelpers::capitalize($request->input('reference'),'name'),
                'pin_code'=>$request->input('pin_code'),
                'created_by'=>Auth::id()
            ];
            if(!empty($request->input('aadhar')) || !empty($request->input('voter')) ){
                $checkCustomer = CustomHelpers::checkCustomer($id,$request->input('aadhar'),$request->input('voter'));
                if(empty($sale))
                {
                    if(empty($checkCustomer))
                    {
                        $data = array_merge($data,['aadhar_id'=> $request->input('aadhar'),
                                                    'voter_id' => $request->input('voter')]);
                    }
                    else{
                        return back()->with('error','Error, Cutomer Already Exist for this Aadhar Number OR Voter Number')->withInput();
                    }
                }
                else{
                    if(empty($cust->aadhar_id))
                    {
                        $checkAadhar = CustomHelpers::checkCustomer($id,$request->input('aadhar'),$request->input('voter'));
                        if(!empty($checkAadhar))
                        {
                            return back()->with('error','Error, Cutomer Already Exist for this Aadhar Number.')->withInput();
                        }
                        $data = array_merge($data,['aadhar_id'=> $request->input('aadhar')]);
                    }elseif($request->input('aadhar') != $cust->aadhar_id){
                        return back()->with('error','Aadhar Number Will not be Changed When Customer Already Associate with Sale.')->withInput();
                    }
                    if(empty($cust->voter_id))
                    {
                        $checkVoter = CustomHelpers::checkCustomer($id,$request->input('aadhar'),$request->input('voter'));
                        if(!empty($checkVoter))
                        {
                            return back()->with('error','Error, Cutomer Already Exist for this Voter Number.')->withInput();
                        }
                        $data = array_merge($data,['voter_id'=> $request->input('voter')]);
                    }elseif($request->input('voter') != $cust->voter_id){
                        return back()->with('error','Voter Number Will not be Changed When Customer Already Associate with Sale.')->withInput();
                    }
                }
            }

            $customer = Customers::where('id',$id)->update($data);
            
            if($customer)
            {
                $customer_details = CustomerDetails::insertGetId([
                    'customer_id'   =>  $id,
                    'relation_type' =>  $cust->relation_type,
                    'relation' =>  CustomHelpers::capitalize($cust->relation,'name'),
                    'email_id'=>$cust->email,
                    'mobile'=>$cust->mobile,
                    'country'=>$cust->country,
                    'state'=>$cust->state,
                    'city'=>$cust->city,
                    'location'=>$cust->location,
                    'address'=>CustomHelpers::capitalize($cust->address,'sentence'),
                    'pin_code'=>$cust->pin_code,
                    'reference'=>CustomHelpers::capitalize($cust->reference,'name'),
                    'created_by'=>Auth::id()
                ]);

            }  
        
        }  catch(\Illuminate\Database\QueryException $ex) {
            return redirect('/admin/customer/update/'.$id)->with('error','some error occurred'.$ex->getMessage());
        }

        DB::commit();

        /* Add action Log */
        CustomHelpers::userActionLog($action='Customer Updated',$id,$id);

        return redirect('/admin/customer/update/'.$id)->with('success','Customer Updated Successfully.'); 
    }

    public function unload_data(){
        // $factory = Factory::all()->toArray();
        $store_id = explode(',',Auth::user()->store_id);
        $store = Store::whereIn('id',$store_id)
                ->select(
                    'id',
                    'store_type',
                    DB::raw('concat(store_type,"-",name) as name')
                )
                ->orderBy('store_type','ASC')
                ->get()->toArray();
        $data = array(
            // 'factory' => $factory,
            'store' => $store,
            'layout'=>'layouts.main'
        );
        return view('admin.UnloadData',$data);
    }
   
    public function unloadView(){
        $data = array('layout'=>'layouts.main');
        return view('admin.unloadView',$data);   
    }
    public function unloadView_api(Request $request){
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;

        $store_id = explode(',',Auth::user()->store_id);
            $api_data= Unload::leftJoin('store','unload.store','store.id')
                        ->leftJoin('factory','factory.id','unload.factory')
                ->whereIn('store.id',$store_id)
            ->select(
                DB::raw('unload.id as id'),
                DB::raw('unload.load_referenace_number as refer'),
                DB::raw('concat(store.name,"-",store.store_type) as store'),
                DB::raw('unload.unloading_date as unload_date'),
                DB::raw('unload.transporter_name as trans'),
                DB::raw('unload.truck_number'),
                DB::raw('factory.name as factory'),
                DB::raw('unload.status as status'),
                DB::raw("(select product.id from product left join product_details on product_details.product_id = product.id 
                            where product.type = 'battery' and product_details.unload_id = unload.id and 
                            product_details.store_id = unload.store LIMIT 1) as product_details_id"),
                DB::raw("(select sum(qty) as qty from unload_addon where unload_id = unload.id and addon_name = 'owner_manual') as owner_manual_sum")    
            );
            if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('unload.id','like',"%".$serach_value."%")
                    ->orwhere('unload.load_referenace_number','like',"%".$serach_value."%")
                    ->orwhere('store.name','like',"%".$serach_value."%")
                    ->orwhere('unload.unloading_date','like',"%".$serach_value."%")
                    ->orwhere('unload.transporter_name','like',"%".$serach_value."%")
                    ->orwhere('unload.truck_number','like',"%".$serach_value."%")
                    ->orwhere('unload.factory','like',"%".$serach_value."%")
                    ->orwhere('unload.status','like',"%".$serach_value."%");
                });
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                    //'unload.id as id',
                    'unload.load_referenace_number',
                    'store.name',
                    'unload.unloading_date',
                    'unload.transporter_name',
                    'unload.factory',
                    'unload.truck_number',
                    'unload.status'
                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('id','desc');      
        
        $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);  
    }
    // public function unloadViewDel(Request $request,$id)
    // {
    //     $del = Unload::where("id",$id)->delete();
    //     if($del)
    //     {
    //         return back()->with('success','Data deleted successfully');
    //     }
    //     return back()->with('error','Data deleted Unsuccessfully');;
    // }

     public function unload_update($id) {


        $upload = Unload::where('id',$id)->get()->first();
        $factory = Factory::all()->toArray();
        $store_id = CustomHelpers::user_store();

        $store = Store::whereIn('id',$store_id)
                ->where('id',$upload->store)
                ->select(
                    'id',
                    'store_type',
                    DB::raw('concat(store_type,"-",name) as name')
                )
                ->orderBy('store_type','ASC')
                ->get()->toArray();
        if($upload)
        {
            $data = array(
                'upload'  => $upload,
                'factory' => $factory,
                'store'   => $store,
                'layout'  =>'layouts.main'
            );
            return view('admin.update_unload',$data);
        }
         else
            {
                return redirect('admin/unload/data/list')->with('error','No Form For his Id Exists.');
            }
    }

    public function unload_update_Db(Request $request,$id){

         $uploaddata =  Unload::where('id',$id)->get()->first();
         $old_store_id = $uploaddata->store;
         $old_qty_sc = $uploaddata->qty_sc;
         $old_qty_mc = $uploaddata->qty_mc;
         $old_quantity= $old_qty_sc + $old_qty_mc;
         $old_shortage_mirror_set= $old_quantity-$uploaddata->mirror_set;
         $old_shortage_toolkits= $old_quantity-$uploaddata->toolkits;
        //  $old_shortage_owners_manual= $old_quantity-$uploaddata->owners_manual;
         $old_shortage_first_aid_kit= $old_quantity-$uploaddata->first_aid_kit;
         $old_shortage_battery= $old_quantity-$uploaddata->battery;
         $old_shortage_qty_keys= $old_quantity-$uploaddata->qty_keys;
         $old_shortage_saree_guard= $old_qty_mc-$uploaddata->saree_guard;
         $factory=$uploaddata->factory;
        $validateZero = [];
        $validateZeroMsg = [];

        $this->validate($request,array_merge($validateZero,[     
           
            // 'loadRefNum'=>'required',
            'store'=>'required|in:'.$old_store_id,
            'unloadDate'=>'required',
            // 'truckNumber'=>'required',
            // 'transporterName' => 'required',
            'driverName' => 'required',
            'mirror_set' => 'required|lte:'.$old_quantity,
            'tool_kit' => 'required|lte:'.$old_quantity,
            // 'owner_manual' => 'required|lte:'.$old_quantity,
            'first_aid' => 'required|lte:'.$old_quantity,
            'battery' => 'required|lte:'.$old_quantity,
            'saree' => 'required|lte:'.$old_qty_mc,
            'qty_keys' => 'required|lte:'.$old_quantity,
            // 'status' => 'required',
            'damagedVehicle' => 'required',
            // 'factory' => 'required',     
            // 'invoiceDate' => 'required'      
        ]),array_merge($validateZeroMsg,[
           
            // 'loadRefNum.required'=> 'This field is required.',
            'store.required'=> 'This field is required.',
            'store.in'=> "Store couldn't be change.",
            'unloadDate.required'=> 'This field is required.',
            // 'truckNumber.required'=> 'This field is required.',
            // 'transporterName.required'=> 'This field is required.',
            'driverName.required'=> 'This field is required.',
            'mirror_set.required'=> 'This Field must be greater than :field',
            'tool_kit.required'=> 'This Field must be greater than :field',
            // 'owner_manual.required'=> 'This Field must be greater than :field',
            'first_aid.required'=> 'This Field must be greater than :field',
            'battery.required'=> 'This Field must be greater than :field',
            'saree.required'=> 'This Field must be greater than :field',
            'qty_keys.required'=> 'This Field must be greater than :field',
            // 'status.required'=> 'This field is required.',
            'damagedVehicle.required'=> 'This field is required.',
            // 'factory.required'=> 'This field is required.',
            // 'invoiceDate.required' => 'This field is required.'

        ]));


        try{
            DB::beginTransaction();
            //  //----------- trim() ---------------
            // $loadRefNum = trim($request->input('loadRefNum'));
            // $truckNumber = trim($request->input('truckNumber'));
            // $transporterName = trim($request->input('transporterName'));
            $driverName = trim($request->input('driverName'));
            //------------------------------------
             $update_array = array(

                    // 'load_referenace_number'=>$loadRefNum,
                    'store'=>$request->input('store'),
                    'unloading_date'=>date('Y-m-d',strtotime($request->input('unloadDate'))),
                    // 'truck_number'=>$truckNumber,
                    // 'transporter_name'=>CustomHelpers::capitalize($transporterName,'name'),
                    'driver_name'=>CustomHelpers::capitalize($driverName,'name'),
                    // 'factory'=>$request->input('factory'),
                    'mirror_set'=>$request->input('mirror_set'),
                    'toolkits'=>$request->input('tool_kit'),
                    // 'owners_manual'=>$request->input('owner_manual'),
                    'first_aid_kit'=>$request->input('first_aid'),
                    'battery'=>$request->input('battery'),
                    'saree_guard'=>$request->input('saree'),
                    'qty_keys'=>$request->input('qty_keys'),
                    // 'status'    => $request->input('status'),
                    'damaged_vehicle'=>$request->input('damagedVehicle'),
                    // 'invoice_number'=>$truckNumber,
                    // 'invoice_date'  =>CustomHelpers::showDate($request->input('invoiceDate'),'Y-m-d H:i:s')
                    
                );
            
        
            $upload = Unload::where('id',$id)->update($update_array);   
            $UnloadId = $id;
            $date = Carbon::now();

            //  update unload vehicle addon stock
            $prod_data = [
            'mirror' => ['inc' =>  $request->input('mirror_set'),'dec'  =>  $uploaddata->mirror_set],
            'toolkit' => ['inc' =>  $request->input('tool_kit') ,'dec'  =>  $uploaddata->toolkits],
            // 'owner_manual' => ['inc'    =>  $request->input('owner_manual') ,'dec'  =>  $uploaddata->owners_manual],
            'first_aid_kit' => ['inc'   =>  $request->input('first_aid') ,'dec'  =>  $uploaddata->first_aid_kit],
            'saree_guard' => ['inc' =>  $request->input('saree') ,'dec'  =>  $uploaddata->saree_guard],
            'bike_keys' => ['inc'   =>  $request->input('qty_keys') ,'dec'  =>  $uploaddata->qty_keys]        
            ];
            $store_id = $request->input('store');
            foreach($prod_data as $k => $v){
                $col = $k;
                $inc_qty = $v['inc'];
                $dec_qty = $v['dec'];

                $find = VehicleAddonStock::where('vehicle_addon_key',$col)
                                                ->where('store_id',$store_id)->first();
                if(isset($find->id)){
                    $dec_update = VehicleAddonStock::where('id',$find->id)
                                                    ->decrement('qty',$dec_qty);
                    $inc_update = VehicleAddonStock::where('id',$find->id)
                                                    ->increment('qty',$inc_qty);
                }else{
                    $insert =   VehicleAddonStock::insertGetId([
                        'vehicle_addon_key' =>  $col,
                        'qty'   =>  $inc_qty,
                        'sale_qty'  =>  0,
                        'store_id'  =>  $store_id
                    ]);
                }

            }

            $factarray = array(
                 array('mirror_set','scmc',$old_shortage_mirror_set), 
                 array('tool_kit','scmc',$old_shortage_toolkits),
                 //  array('owner_manual','scmc',$old_shortage_owners_manual),
                 array('first_aid','scmc',$old_shortage_first_aid_kit),
                 array('battery','scmc',$old_shortage_battery),
                 array('qty_keys','scmc',$old_shortage_qty_keys), 
                 array('saree','mc',$old_shortage_saree_guard)
            );

            foreach ($factarray as $key) {
                if ($key['1'] == 'scmc') {
                    $quantity = $old_qty_sc + $old_qty_mc;
                }
                if($key['1'] == 'mc'){
                    $quantity = $old_qty_mc;
                }
                if($key['1'] == 'sc'){
                    $quantity = $old_qty_sc;
                }
                $shortage = $quantity - $request->input($key['0']);
                $actual_sortage = $shortage - $key[2];

                $factdata =  PartShortage::where('factory_id',$factory)->where('part_type',$key['0'])->get()->first();

                if($actual_sortage > 0)
                {
                   if (!empty($factdata)) {
                        
                        $added_qty = $factdata->shortage_qty + $actual_sortage;
                        $shortagedata = PartShortage::where('factory_id',$factory)->where('part_type',$key['0'])->update(
                            [
                                'shortage_qty'=>$added_qty
                            ]
                        );
                        $updateShortageDetails = ShortageDetails::where('unload_id',$UnloadId)
                                                    ->where('part_shortage_id',$factdata->id)
                                                    ->update(['receive_qty' => $shortage]);
                    }
                     // insert
                    else{

                            $shortagedata = PartShortage::insertGetId(
                            [
                                'factory_id'=>$factory,
                                'part_type'=>$key['0'],
                                'shortage_qty'=>$shortage
                            ]
                            );

                            if ($shortagedata) {
                           $sdata = ShortageDetails::insertGetId(
                            [
                                'receive_date'=>$date,
                                'receive_qty'=>$shortage,
                                'part_shortage_id'=>$shortagedata,
                                'unload_id'=>$UnloadId,
                                'created_by' => Auth::id()
                            ]
                            ); 

                            }
                        }
                }elseif($actual_sortage == 0){
                    if (!empty($factdata)) {
                        $updateShortageDetails = ShortageDetails::where('unload_id',$UnloadId)
                                                    ->where('part_shortage_id',$factdata->id)
                                                    ->update(['receive_qty' => 0]);
                    }
                }
            }
            
            if($upload==NULL) 
            {
                DB::rollback();
                return redirect('/admin/unload/data/edit/')->with('error','Some Unexpected Error occurred.');
            }else{
                /* Add action Log */
                CustomHelpers::userActionLog($action='Update Unload',$UnloadId);
                DB::commit();
                return redirect('/admin/unload/data/list/')->with('success','Unload Data updated successfully.'); 
            }    

        } catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return redirect('/admin/unload/data/list')->with('error','some error occurred'.$ex->getMessage())->withInput();
        }
    }

    public function getUnloadAddon(Request $request){

        try{
            // DB::beginTransaction();

            $unload = $request->input('unload_id');
            $get_addon = UnloadAddon::where('unload_id',$unload)
                                        ->where('addon_name','owner_manual')
                                        ->get();
            
            if(isset($get_addon[0])){

                $m = [];
                foreach($get_addon as $k => $v){
                    $m[] = $v->model;
                }
                $model = ProductModel::where('type','product')->whereNotIn('model_name',$m)
                                        ->groupBy('model_name')->pluck('model_name');
                return response()->json(array($get_addon,$model));
            }
            else{
                return response()->json("Enter Unload Id Couldn't Find.",422);
            }
        }
        catch(\Illuminate\Database\QueryException $ex) {
            return response()->json('Something went wrong.'.$ex->getMessage(),422);
        }
        return response()->json('Something went wrong.',422);
    }
    public function uploadUnloadAddon(Request $request)
    {
        $unload = $request->input('unload_id');
        $add_more = $request->input('model');
        $more_qty = $request->input('model_qty');
        $get_addon = UnloadAddon::where('unload_id',$unload)
                                        ->where('addon_name','owner_manual')
                                        ->get();
        $oldUnloadQty = UnloadAddon::where('unload_id',$unload)
                                        ->where('addon_name','owner_manual')
                                        ->sum('qty');
        if($oldUnloadQty > 0){
            return response()->json("You will Never Update It.",422);
        }
        $unload_data = Unload::where('id',$unload)->first();
        try{
            DB::beginTransaction();
            
            if(isset($unload_data->id)) //check isset            
            {
                // sum total owner manual
                $total_om = 0;
                $old_total_om = 0;
                $old_model_om_qty = [];  // om standfor owner_manual
                // -----
                $ins_id = [];
                $check_model = [];
                foreach($get_addon as $k => $v){
                    $model_name = $v->model;
                    if(in_array($model_name,$check_model)){
                        DB::rollback();
                        return response()->json("'".$model_name." Should be Unique.'.",422);
                    }else{
                        array_push($check_model,$model_name);
                    }
                    $update = UnloadAddon::where('id',$v->id)->update(['qty' => $request->input($v->id)]);
                    array_push($ins_id,$v->id);
                    $total_om = $total_om+$request->input($v->id);
                    $old_total_om = $old_total_om+$v->qty;
                    $old_model_om_qty[$model_name] = $v->qty;
                }
                //insert new models if add more
                if(isset($add_more[0])){
                    foreach($add_more as $k1 => $v1){
                        $model_name = $v1;
                        if(in_array($model_name,$check_model)){
                            DB::rollback();
                            return response()->json("'".$model_name." Should be Unique.'.",422);
                        }else{
                            array_push($check_model,$model_name);
                        }
                        $ins = UnloadAddon::insertGetId([
                            'unload_id' =>  $unload,
                            'addon_name'    =>  'owner_manual',
                            'model' =>  $v1,
                            'qty'   =>  $more_qty[$k1]
                        ]);
                        $total_om = $total_om+$more_qty[$k1];
                        array_push($ins_id,$ins);
                    }
                }
                
                // update stock
                $find_stock = VehicleAddonStock::where('vehicle_addon_key','owner_manual')
                                            ->where('store_id',$unload_data->store)->first();
                if(isset($find_stock->id)){
                    $dec_update = VehicleAddonStock::where('id',$find_stock->id)
                                                    ->decrement('qty',$old_total_om);
                    $inc_update = VehicleAddonStock::where('id',$find_stock->id)
                                                    ->increment('qty',$total_om);
                }else{
                    $insert =   VehicleAddonStock::insertGetId([
                        'vehicle_addon_key' =>  'owner_manual',
                        'qty'   =>  $total_om,
                        'sale_qty'  =>  0,
                        'store_id'  =>  $unload_data->store
                    ]);
                }

                // update shortage stock
                $scmc = $unload_data->qty_sc+$unload_data->qty_mc;
                $old_receive = $old_total_om;
                $old_shortage_om = $scmc-$old_receive;
                $factarray = array(
                    array('owner_manual','scmc',$old_shortage_om)
                );
   
                foreach ($factarray as $key) {
                    if ($key['1'] == 'scmc') {
                        $quantity = $scmc;
                    }
                    if($key['1'] == 'mc'){
                        $quantity = $unload_data->qty_mc;
                    }
                    if($key['1'] == 'sc'){
                        $quantity = $unload_data->qty_sc;
                    }
                    $shortage = $quantity - $total_om;
                    $actual_sortage = $shortage - $key[2];
   
                       $factdata =  PartShortage::where('factory_id',$unload_data->factory)->where('part_type',$key['0'])->get()->first();
    
                    if($actual_sortage > 0)
                    {
                      if (!empty($factdata)) {
                           
                           $added_qty = $factdata->shortage_qty + $actual_sortage;
                           $shortagedata = PartShortage::where('factory_id',$unload_data->factory)->where('part_type',$key['0'])->update(
                               [
                                   'shortage_qty'=>$added_qty
                               ]
                           );
                           $part_short_id= $factdata->id;
                           
                       }
                        // insert
                       else{
   
                            $shortagedata = PartShortage::insertGetId(
                               [
                                   'factory_id'=>$unload_data->factory,
                                   'load_referenace_number'    =>  $unload_data->id,
                                   'part_type'=>$key['0'],
                                   'shortage_qty'=>$shortage
                               ]
                           );
                            $part_short_id= $shortagedata;
                            if ($shortagedata == 0) {
                                DB::rollback();
                                return response()->json('Some Unexpected Error occurred.',422);
                           }
                       }

                        $ShortageDetails = ShortageDetails::where('unload_id',$unload_data->id)
                                                       ->where('part_shortage_id',$factdata->id)->first();
                        if($ShortageDetails != null){
                            $updateShortageDetails = ShortageDetails::where('unload_id',$unload_data->id)
                                                       ->where('part_shortage_id',$factdata->id)
                                                       ->update(['receive_qty' => $shortage]);
                        }else{
                            $sdata = ShortageDetails::insertGetId(
                               [
                                   'receive_date'=>$unload_data->unloading_date,
                                   'receive_qty'=>$shortage,
                                   'part_shortage_id'=>$part_short_id,
                                   'unload_id'=>$unload_data->id,
                                   'created_by' => Auth::id()
                               ]
                            );
                        }
                        
                    }
                    
                }

                // get all unload addon data
                $all_owner_manual =  UnloadAddon::where('unload_id',$unload)
                                        ->where('addon_name','owner_manual')
                                        ->whereRaw('qty > 0')
                                        ->get()->toArray();
                foreach($all_owner_manual as $k => $row){
                    $addon_name = $row['addon_name'];
                    $model = str_replace(' ','_',strtolower($row['model']));
                    $new_key = $addon_name.'_'.$model;

                    $new_qty = $row['qty'];
                    $old_qty = 0;
                    if(isset($old_model_om_qty[$row['model']])){
                        $old_qty = $old_model_om_qty[$row['model']];
                    }

                    $checkStock = VehicleAddonStock::where('vehicle_addon_key',$new_key)
                                                ->where('store_id',$unload_data->store)
                                                ->first();
                    if(isset($checkStock->id)){
                        if($old_qty > 0){
                            $dec_update = VehicleAddonStock::where('id',$checkStock->id)
                                                            ->decrement('qty',$old_qty);
                        }
                        $inc_update = VehicleAddonStock::where('id',$checkStock->id)
                                                        ->increment('qty',$new_qty);
                    }else{
                        $insert =   VehicleAddonStock::insertGetId([
                            'vehicle_addon_key' =>  $new_key,
                            'qty'   =>  $new_qty,
                            'sale_qty'  =>  0,
                            'store_id'  =>  $unload_data->store
                        ]);
                    }
                }

            }
            else{
                DB::rollback();
                return response()->json("Enter Unload Id Couldn't Find.",422);
            }
        }
        catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return response()->json('Something went wrong.'.$ex->getMessage(),422);
        }
        DB::commit();
        return response()->json([1,'Successfully Updated.']);


    }

    public function damage_claim(){
        echo "under construction";die;
        $store = Store::all()->toArray();
        $reference_no = Unload::leftJoin('damage_claim','damage_claim.unload_id','<>','unload.id')
        ->select('unload.id as unload_id','unload.load_referenace_number as unload_ref_no')
        ->get()->toArray();
        $data = array(
            'store' => $store,
            'reference_no' => $reference_no,
            'layout'=>'layouts.main'
        );
        return view('admin.damage_claim',$data);
    }
    public function damage_claim_storeDB(Request $request)
    {
        echo "under construction";die;

        $this->validate($request,[     
            'loadRefNumber'=>'required',
            'claimAmount'=>'required',
            'sett'=>'required',
            'mail' => 'required',
            'est' => 'required',
            'form' => 'required',
            'hirise' => 'required'
        ]);

        try{
            $id=Auth::id();
            $date = Carbon::now();
            if($request->input('recievedDate'))
                $receiveddate=CustomHelpers::showDate($request->input('recievedDate'),'Y-m-d');
            else 
                $receiveddate =NULL; 

            if($request->input('mailDate'))
                $mailDate=CustomHelpers::showDate($request->input('mailDate'),'Y-m-d');
            else 
                $mailDate =NULL; 

            if($request->input('estDate'))
                $estDate=CustomHelpers::showDate($request->input('estDate'),'Y-m-d');
            else 
                $estDate =NULL; 
                
            if($request->input('updateDate'))
                $updateDate=CustomHelpers::showDate($request->input('updateDate'),'Y-m-d');
            else 
                $updateDate =NULL; 

            if($request->input('billDate'))
                $billDate=CustomHelpers::showDate($request->input('billDate'),'Y-m-d');
            else 
                $billDate =NULL; 
            $id = DamageClaim::insertGetId(
                [
                    'unload_id'=>$request->input('loadRefNumber'),
                    'claim_amount'=>$request->input('claimAmount'),
                    'settlement'=>$request->input('sett'),
                    'received_amount'=>$request->input('recievedAmount'),
                    'received_date'=>$receiveddate,
                    'mail_sent'=>$request->input('mail'),
                    'mail_date'=>$mailDate,
                    'est_sent'=>$request->input('est'),
                    'est_date'=>$estDate,
                    'claim_form_uploaded'=>$request->input('form'),
                    'uploaded_date'=>$updateDate,
                    'hirise_bill'=>$request->input('hirise'),
                    'bill_date'=>$billDate
                    
                ]
            );
            } catch(\Illuminate\Database\QueryException $ex) {
                    return redirect('/admin/damage/claim')->with('error','some error occurred'.$ex->getMessage())->withInput();
            }

            return redirect('/admin/damage/claim')->with("success","Damage Claim created successfully.");
    }

    public function damageView(){
        $data=array('layout'=>'layouts.main');
        return view('admin.damageView',$data);   
    }
    public function damageView_api(Request $request,$status){
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
        $store_id = explode(',',Auth::user()->store_id);
        if($status == 'active')
        {
            
            $api_data= DamageClaim::leftJoin('unload','unload.id','damage_claim.unload_id')
            ->leftJoin('store','unload.store','store.id')
            ->where('damage_claim.status','pending')
            ->whereIn('store.id',$store_id)
            ->select(
                DB::raw('unload.load_referenace_number'),
                DB::raw('concat(store.name,"-",store.store_type) as name'),
                'damage_claim.*',
                DB::raw('(select count(id) from damage_details where damage_claim_id = damage_claim.id) as no_of_claim'),
                DB::raw('unload.damaged_vehicle')
                );
            if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('unload.load_referenace_number','like',"%".$serach_value."%")
                    ->orwhere('store.name','like',"%".$serach_value."%")
                    ->orwhere('damage_claim.claim_amount','like',"%".$serach_value."%")
                    ->orwhere('damage_claim.settlement','like',"%".$serach_value."%")
                    ->orwhere('damage_claim.received_amount','like',"%".$serach_value."%")
                    ->orwhere('damage_claim.received_date','like',"%".$serach_value."%")
                    ->orwhere('damage_claim.mail_sent','like',"%".$serach_value."%")
                    ->orwhere('damage_claim.est_sent','like',"%".$serach_value."%")
                    ->orwhere('damage_claim.claim_form_uploaded','like',"%".$serach_value."%")
                    ->orwhere('damage_claim.hirise_bill','like',"%".$serach_value."%");
                });
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                    'damage_claim.id',
                    'unload.load_referenace_number',
                    'store.name',
                    'damage_claim.claim_amount',
                    'damage_claim.settlement',
                    'damage_claim.received_amount',
                    'damage_claim.received_date',
                    'damage_claim.mail_sent',
                    'damage_claim.est_sent',
                    'damage_claim.claim_form_uploaded',
                    'damage_claim.hirise_bill'
                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('damage_claim.id','desc');      
        }
        elseif($status == 'settle')
        {
            $api_data= DamageClaim::leftJoin('unload','unload.id','damage_claim.unload_id')
            ->leftJoin('store','unload.store','store.id')
            ->where('damage_claim.status','done')
            ->whereIn('store.id',$store_id)
            ->select(
                DB::raw('unload.load_referenace_number'),
                DB::raw('concat(store.name,"-",store.store_type) as name'),
                'damage_claim.*'
                );
            if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('unload.load_referenace_number','like',"%".$serach_value."%")
                    ->orwhere('store.name','like',"%".$serach_value."%")
                    ->orwhere('damage_claim.claim_amount','like',"%".$serach_value."%")
                    ->orwhere('damage_claim.settlement','like',"%".$serach_value."%")
                    ->orwhere('damage_claim.received_amount','like',"%".$serach_value."%")
                    ->orwhere('damage_claim.received_date','like',"%".$serach_value."%")
                    ->orwhere('damage_claim.mail_sent','like',"%".$serach_value."%")
                    ->orwhere('damage_claim.est_sent','like',"%".$serach_value."%")
                    ->orwhere('damage_claim.claim_form_uploaded','like',"%".$serach_value."%")
                    ->orwhere('damage_claim.hirise_bill','like',"%".$serach_value."%");
                });
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                    'damage_claim.id',
                    'unload.load_referenace_number',
                    'store.name',
                    'damage_claim.claim_amount',
                    'damage_claim.settlement',
                    'damage_claim.received_amount',
                    'damage_claim.received_date',
                    'damage_claim.mail_sent',
                    'damage_claim.est_sent',
                    'damage_claim.claim_form_uploaded',
                    'damage_claim.hirise_bill'
                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('damage_claim.id','desc');      
        }
        
        $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);  
    }
    public function damageclaim_view($id){
        $api_data= DamageClaim::leftJoin('unload','unload.id','damage_claim.unload_id')
            //->leftJoin('store','unload.store','store.id')
            ->where('damage_claim.id',$id)
            ->select(
                DB::raw('unload.load_referenace_number'),
                //DB::raw('store.name'),
                'damage_claim.*'
                )->first();
        $claim_details=DamageDetails::where('damage_claim_id',$id)
                ->leftJoin('product_details','product_details.id','damage_details.product_details_id')
                ->leftJoin('product','product.id','product_details.product_id')
                //->where('product_details.status','damage')
                ->select(['damage_details.*',
                        DB::raw("concat(product.model_category,'-',product.model_name,'-',product.model_variant,'-',product.color_code) as product_name"),
                        'product_details.frame'
                        ])->get();    
        
        if(!isset($api_data->id) || !isset($claim_details[0])){
            return redirect('/admin/damage/claim/list')->with("error","Not Found Claim Details.");
        }
        $data=[
            'layout'=>'layouts.main',
            'api_data'=>$api_data->toArray(),
            'claim_details'=>$claim_details->toArray()
        ];
        return view('admin.damage_claim_view',$data);   
    }
    public function damageclaim_view_api(Request $request, $id){
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
        $api_data= DamageDetail::leftJoin('product_details','product_details.id','damage_details.product_details_id')
            ->leftJoin('damage_claim','damage_claim.id','damage_details.damage_claim_id')
            ->leftJoin('product','product.id','product_details.product_id')
            ->where('damage_details.damage_claim_id',$id)
            ->select(
                DB::raw('damage_details.id as id'),
                DB::raw("concat(product.model_category,'-',product.model_name,'-',product.model_variant,'-',product.color_code) as product_name"),
                DB::raw('product_details.frame'),
                DB::raw('product_details.engine_number'),
                DB::raw('damage_details.status'),
                DB::raw('damage_details.est_amount'),
                DB::raw('damage_details.invoice'),
                DB::raw('IFNULL(damage_details.damage_desc,"") as damage_desc'),
                DB::raw('damage_details.jc'),
                DB::raw('damage_details.repair_amount'),
                DB::raw('damage_details.repair_completion_date'),
                DB::raw('(select count(id) from damage_details where damage_claim_id = '.$id.') as no_of_claim'),
                DB::raw('(select unload.damaged_vehicle from unload where unload.id = damage_claim.unload_id ) as damaged_vehicle')
                );

            if(!empty($serach_value))
                {
                    $api_data->where(function($query) use ($serach_value){
                        $query->where('damage_details.invoice','like',"%".$serach_value."%")
                        ->orwhere('product_details.frame','like',"%".$serach_value."%")
                        ->orwhere('damage_details.status','like',"%".$serach_value."%")
                        ->orwhere('damage_details.est_amount','like',"%".$serach_value."%")
                        ->orwhere('damage_details.damage_desc','like',"%".$serach_value."%")
                        ->orwhere('damage_details.jc','like',"%".$serach_value."%")
                        ->orwhere('damage_details.repair_amount','like',"%".$serach_value."%")
                        ->orwhere('damage_details.repair_completion_date','like',"%".$serach_value."%");
                    });
                }
                if(isset($request->input('order')[0]['column']))
                {
                    $data = [
                            'damage_details.invoice',
                            'product_detials.frame',
                            'damage_details.status',
                            'damage_details.est_amount',
                            'damage_details.damage_desc',
                            'damage_details.jc',
                            'damage_details.repair_amount',
                            'damage_details.repair_completion_date'
                    ];
                    $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                    $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
                }
                else
                    $api_data->orderBy('damage_details.id','desc');      
            
            
            $count = count( $api_data->get()->toArray());
            $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
            $array['recordsTotal'] = $count;
            $array['recordsFiltered'] = $count;
            $array['data'] = $api_data; 
            return json_encode($array); 
       
    }
    public function damageForm(Request $request, $damage_claim_id,$damageClaimDetId)
    {
       DB::beginTransaction();
        if ($request->isMethod('post')) {
            
            $this->validate($request,[
                'est_amount' => 'required',
                'description'   =>  'required',
                'status'    =>  'required'
            ],[
                'est_amount.required' => 'This Field is Required',
                'description.required'  =>  'This Field is Required',
                'status.required'   =>  'This Field is Required',
                'status.string'   =>  'The Status must be selected'
            ]);
            $damageDetailId = $damageClaimDetId; 
            $est_amount = $request->input('est_amount'); 
            $desc = $request->input('description'); 
            $rep_amount = $request->input('rep_amount');
            $rep_date = $request->input('rep_date');
            $invoice = $request->input('invoice');
            if($rep_date)
            {
                $rep_date = CustomHelpers::showDate($rep_date,'Y-m-d G:i:s');
            }
            else
            {
                $rep_date = null;
            }
            $getOld = DamageClaim::where('id',$damage_claim_id)
                            ->first(['claim_amount','repair_amount'])->toArray();
            $getOldClaim = $getOld['claim_amount'];
            $getOldRepair = $getOld['repair_amount'];
            $getOlddet  = DamageDetail::where('id',$damageDetailId)
                            ->first(['est_amount','repair_amount','status'])->toArray();
            if($getOlddet['status'] == 'Repaired' && $request->input('status') != 'Repaired'){
                return back()->with('error','After Repaired, Status Will not be Changed.')->withInput();
            }
            $getOldEst = $getOlddet['est_amount'];
            $getOldDetRepair = $getOlddet['repair_amount'];
            $newClaim = ($getOldClaim - $getOldEst)+ $request->input('est_amount');
            $newRepair = ($getOldRepair - $getOldDetRepair)+ $request->input('rep_amount');

            if($damage_claim_id && $damageDetailId && $est_amount && $desc )
            {
                $updateDamageClaimDetail = DamageDetail::where('id',$damageDetailId)
                                            ->update(['est_amount'=>$est_amount,
                                                        'damage_desc' => $desc,
                                                        'repair_amount' => $rep_amount,
                                                        'repair_completion_date' => $rep_date,
                                                        'status'    =>  $request->input('status'),
                                                        'invoice'   =>  $invoice
                                                    ]);
                if($updateDamageClaimDetail)
                {
                    $updateClaimAmount = DamageClaim::where('id',$damage_claim_id)
                                        ->update(['claim_amount' => $newClaim,
                                                    'repair_amount' => $newRepair]);
                    if($updateClaimAmount)
                    {
                        DB::commit();
                        return back()->with('success','Successfully Updated.');
                    }
                    else
                    {
                        DB::rollBack();
                        return back()->with('error','Try Again');

                    }
                }
                else
                {
                        DB::rollBack();
                        return back()->with('error','Try Again');

                }
            }

        }
        $get_damageDetail = DamageDetail::leftJoin('product_details','product_details.id','damage_details.product_details_id')
                            ->leftJoin('product','product.id','product_details.product_id')
                            ->leftJoin('damage_claim','damage_claim.id','damage_details.damage_claim_id')
                            ->where('damage_claim.status','<>','done')
                            ->where('damage_details.id',$damageClaimDetId)
                            ->where('damage_details.damage_claim_id',$damage_claim_id)
                            ->select(
                                DB::raw('concat(product.model_category,"-",product.model_name,"-",product.model_variant) as product_name'),
                                DB::raw('product.color_code as color_code'),
                                DB::raw('product_details.frame'),
                                DB::raw('product_details.engine_number'),
                                DB::raw('damage_details.id as damage_detail_id'),
                                DB::raw('damage_details.status as proStatus'),
                                'damage_details.*'
                            )->get();

        if(count($get_damageDetail) > 0)
        {
            $getDataForNewEntry = $get_damageDetail->toArray();
            
            $data = array(
                'reuqestData'  =>  $getDataForNewEntry[0],
                'damage_claim_id'   =>  $damage_claim_id,
                'layout'    =>  'layouts.main',
                'url'   =>  '/admin/damage/claim/view/update/'.$damage_claim_id.'/'.$damageClaimDetId                
            );             
            return view('admin.damageFormUpdate',$data);
        }
        else{
            return back()->with('error','Try Again');
        }
    }
    
    public function damageViewDel(Request $request)
    {
        echo "Not Permited";die;
        //return "true";
        return $request->input('claim_id');
        // echo "under construction";die;
        // $del = DamageClaim::where("id",$id)->delete();
        // if($del)
        // {
        //     return back()->with('success','Data deleted successfully');
        // }
        // return back()->with('error','Data deleted Unsuccessfully');;
    }
    public function damageclaimUpdate($damageClaimId)
    {
        $claimData = DamageClaim::leftJoin('unload','unload.id','damage_claim.unload_id')
                                ->where('damage_claim.id',$damageClaimId)
                                ->select(
                                    DB::raw('unload.load_referenace_number as refer'),
                                    'damage_claim.*',
                                    'unload.damaged_vehicle'
                                )->first();
        if(!isset($claimData->id)){
            return back()->with('error','Damage Claim Not Found.');
        }
        $checkdd = DamageDetails::where('damage_claim_id',$damageClaimId)->count();
        if($checkdd != $claimData->damaged_vehicle){
            return back()->with('error','Firstly Claim All Product.');
        }
        if($claimData->toArray()['invoice'] == null || $claimData->toArray()['status'] != 'done' )
        {
            $data = array(
                'claimData' => $claimData->toArray(),
                'damageClaimId' =>  $damageClaimId,
                'layout'=>'layouts.main'
            );
            return view('admin.damage_claim',$data);  
        }     
        return redirect('/admin/damage/claim/list')->with('success',"That's data has been claimed");
         
    }
    public function damageclaimUpdate_DB(Request $request,$damageClaimId)
    {
        $validate = []; $msg = [];
        if($request->input('sett') == 'Claim'){
            $validate = [
                'mailDate'  =>  'required_if:mail,Yes',
                'estDate'  =>  'required_if:est,Yes',
                'uploaded'  =>  'required_if:form,Yes',
                'billDate'  =>  'required_if:hirise,Yes'
            ];
            $msg = [
                'mailDate.required_if'    =>  'This Field is Required',
                'estDate.required_if'    =>  'This Field is Required',
                'uploaded.required_if'    =>  'This Field is Required',
                'billDate.required_if'    =>  'This Field is Required'
            ];
        }
        $this->validate($request,array_merge($validate,[     
            'loadRefNumber'=>'required',
            'claimAmount'=>'required'
        ]),array_merge($msg,[
            'loadRefNumber.required'    =>  'This Field is Required',
            'claimAmount.required'    =>  'This Field is Required'
        ]));

        try{
            DB::beginTransaction();
            $id=Auth::id();
            $dc = DamageClaim::where('id',$damageClaimId)->first();
            if(!isset($dc->id)){
                return back()->with('error','Not Found Damage Claim.')->withInput();
            }
            // check if mail/est/form/hirise already yes then should not be update No
            if($dc->mail_sent == 'Yes' && $request->input('mail') == 'No'){
                return back()->with('error','You Should not be Update Mail Sent Information.')->withInput();
            } 
            if($dc->est_sent == 'Yes' && $request->input('est') == 'No'){
                return back()->with('error','You Should not be Update Estimation Information.')->withInput();
            } 
            if($dc->claim_form_uploaded == 'Yes' && $request->input('form') == 'No'){
                return back()->with('error','You Should not be Update Claim For Uploaded Information.')->withInput();
            } 
            if($dc->hirise_bill == 'Yes' && $request->input('hirise') == 'No'){
                return back()->with('error','You Should not be Update Hirise Bill Information.')->withInput();
            } 

            if($request->input('recievedDate'))
                $receiveddate=CustomHelpers::showDate($request->input('recievedDate'),'Y-m-d');
            else 
                $receiveddate =NULL; 

            if($request->input('mail')  == 'Yes')
                $mailDate=CustomHelpers::showDate($request->input('mailDate'),'Y-m-d');
            else 
                $mailDate =NULL; 

            if($request->input('est')  == 'Yes')
                $estDate=CustomHelpers::showDate($request->input('estDate'),'Y-m-d');
            else 
                $estDate =NULL; 
                
            if($request->input('form')  == 'Yes')
                $updateDate=CustomHelpers::showDate($request->input('uploaded'),'Y-m-d');
            else 
                $updateDate =NULL; 

            if($request->input('hirise')  == 'Yes')
                $billDate=CustomHelpers::showDate($request->input('billDate'),'Y-m-d');
            else 
                $billDate =NULL; 
            $id = DamageClaim::where('id',$damageClaimId)
                ->update(
                [
                    'settlement'=>$request->input('sett'),
                    'received_amount'=>$request->input('recievedAmount'),
                    'received_date'=>$receiveddate,
                    'mail_sent'=>$request->input('mail'),
                    'mail_date'=>$mailDate,
                    'est_sent'=>$request->input('est'),
                    'est_date'=>$estDate,
                    'claim_form_uploaded'=>$request->input('form'),
                    'uploaded_date'=>$updateDate,
                    'hirise_bill'=>$request->input('hirise'),
                    'bill_date'=>$billDate,
                    'status' =>   $request->input('status'),
                ]
            );
            
            // check all invoice entered or not  but if settlement is Cash then not be check all invoice
            if($request->input('status') == 'done'){
                $checkAllInvoice = DamageDetails::where('damage_claim_id',$dc->id)->whereNull('invoice')->first();

                if(!isset($checkAllInvoice->id)){
    
                    $getData = DamageDetail::where('damage_claim_id',$damageClaimId)->select('product_details_id')->get()->toArray();
                    for($i = 0 ; $i < count($getData) ; $i++)
                    {
                        $getProStore = ProductDetails::where('id',$getData[$i]['product_details_id'])
                                                        ->select(['product_id','store_id'])->first();
                        $updateProStatus = ProductDetails::where('id',$getData[$i]['product_details_id'])
                                                            ->update(['status'=>'ok']);
                        
                        $getoldData = Stock::where('product_id',$getProStore['product_id'])
                                        ->where('store_id',$getProStore['store_id'])
                                        ->select(['quantity','damage_quantity'])->first();
                        $updateStock = Stock::where('product_id',$getProStore['product_id'])
                                    ->where('store_id',$getProStore['store_id'])
                                    ->update([
                                        'quantity'  =>  $getoldData['quantity']+1,
                                        'damage_quantity'   =>  $getoldData['damage_quantity']-1
                                    ]);
                    }
                }
                elseif(isset($checkAllInvoice->id)){
                    DB::rollback();
                    return back()->with('error','You Should Update Invoice # for all Product.')->withInput();
                }
            }
            } catch(\Illuminate\Database\QueryException $ex) {
                DB::rollback();
                return back()->with('error','some error occurred'.$ex->getMessage())->withInput();
            }
            DB::commit();
            return back()->with("success","Updated successfully.");
    }
    public function stock_movement(){
        $product = ProductModel::where('type','product')->where('isforsale',1)
                                    ->groupBy('model_category')
                                    ->groupBy('model_name')
                                    ->groupBy('model_variant')
                                    ->groupBy('color_code')
                                    ->get();

        // $productdetail = ProductModel::leftJoin('product_details','product.id','product_details.product_id')
        //                 ->where('type','product')
        //             ->get(['product.*','product_details.id as product_detail_id','product_details.frame'
        //             ,'product_details.engine_number'
        //             ,'product_details.status']);
        $store = Store::select(
                                'id',
                                DB::raw("concat(name,' - ',store_type) as name")
                            )
                        ->whereNotIn('store_type',['SubDealer','CoDealer'])
                        ->where('active',1)->get();
        $to_store = Store::whereIn('store_type',['SubDealer','CoDealer'])->where('active',1)
                            ->select('id','store_type','name')->get()->toArray();
        $codealer = SellingDealer::select('id','name')->get()->toArray();
        $subdealer = SubDealerMaster::select('id',
                            DB::raw("concat(actual_name,' - ',display_name) as name")
                        )
                        ->get()->toArray();
        $data = array(
            'store' => $store->toArray(),
            'product' => $product->toArray(),
            // 'product_detail'   =>  $productdetail->toArray(),
            'to_store'  =>  $to_store,
            'codealer'  =>  $codealer,
            'subdealer' =>  $subdealer,
            'layout'=>'layouts.main'
        );
       
        return view('admin.stock_movement',$data);
    }
    public function stock_movement_storeDB(Request $request)
    {
        $count = count($request->input('stockQuantity'));
        $request->session()->put('countAddMore',$count);
        
        $validator = Validator::make($request->all(),[
            'stockProduct.*'=>'required',
            'stockQuantity.*'=>'required|numeric|gt:0',
            'fromStore'=>'required',
            'toStore'=>'required',
        ],
        [
            'stockQuantity.*.required'=>'The Quantity Field is required',
            'stockProduct.*.required'=>'The Product Field is required',
        ]);
        $arr = [];
        $arr[0] = $request->input('toStore');
        $arr[1] = $request->input('codealer');
        $arr[2] = $request->input('subdealer');
        $product_data = $request->input('stockProduct');
        $qty = $request->input('stockQuantity');
        $fromStore = $request->input('fromStore');

        $validator->after(function ($validator) use($arr,$product_data,$qty,$fromStore) {
            if ($arr[0] > 0) {
                $check = Store::where('id',$arr[0])->select('store_type')->first();
                if($check->store_type == 'SubDealer')
                {
                    if(empty($arr[2]) || $arr[2] <= 0){
                        $validator->errors()->add('subdealer', 'This Field is Required.');
                    }
                }elseif($check->store_type == 'CoDealer')
                {
                    if(empty($arr[1]) || $arr[1] <= 0){
                        $validator->errors()->add('codealer', 'This Field is Required.');
                    }
                }else{
                    $validator->errors()->add('toStore', 'Select Valid Store.');
                }
            }
            if(isset($product_data[0])){
                for($i = 0 ; $i < count($product_data); $i++){
                    $request_qty = $qty[$i];
                    if($request_qty > 0){
                        $checkAvailQty = Stock::where('stock.product_id',$product_data[$i])
                                ->where('stock.store_id',$fromStore)->select('id','quantity')->first();
                        if(!isset($checkAvailQty->id)){
                            $validator->errors()->add('stockProduct.'.$i, 'This Product Stock Not Available.');
                        }else{
                            $get_from_pending_qty = SmPending::where('to_store',$fromStore)
                                                    ->where('product_id',$product_data[$i])
                                                    ->sum('smdQty');
                            $stock_real_qty = $checkAvailQty->quantity-$get_from_pending_qty;
                            if($stock_real_qty < $request_qty){
                                $validator->errors()->add('stockQuantity.'.$i, 'Quantity Should be Less or Equal to '.$get_from_pending_qty.'.');
                            }
                        }
                    }
                }
            }else{
                $validator->errors()->add('stockProduct.0', 'This Field is Required.');
            }
        });

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        try{

            $table = ['stock_movement','stock_movement_details'];
            CustomHelpers::resetIncrement($table);

            $auth_id = Auth::id();
            $date = Carbon::now();
            $total_product = count($request->input('stockQuantity'));
            DB::beginTransaction();

            $toStore = $arr[0];
            $codealer = $arr[1];
            $subdealer = $arr[2];
            $dealer = '';
            $sm_col = '';
            $check = Store::where('id',$toStore)->select('store_type')->first();
            if($check->store_type == 'SubDealer')
            {
               $dealer = 'SubDealer';
               $sm_col = 'subdealer';
            }elseif($check->store_type == 'CoDealer')
            {
                $dealer = 'CoDealer';
                $sm_col = 'codealer';
            }
            $req_qty = array_sum($qty);
            $sm_id = 0;
            // print_r($$sm_col);die;
            $PreStockMovement = StockMovement::where('stock_movement.from_store',$fromStore)
                                ->where('stock_movement.to_store',$toStore)
                                ->whereRaw($sm_col.' = '.$$sm_col)
                                ->where('stock_movement.status','pending')
                                ->select('stock_movement.id as stockMoveId','stock_movement.quantity')->first();
            if(isset($PreStockMovement->stockMoveId))
            {
                $PreStockMovement = $PreStockMovement->toArray();
                $sm_id = $PreStockMovement['stockMoveId'];
                $update_sm = StockMovement::where('id',$PreStockMovement['stockMoveId'])
                        ->update(['quantity'=>$PreStockMovement['quantity']+$req_qty,
                                    'created_at'    =>  DB::raw('CURRENT_TIMESTAMP')]);
                if(!$update_sm)
                {
                    DB::rollback();
                    return back()->with('error','Stock Movement Not Update.')->withInput();
                }
            }
            else{
                $sm_id = StockMovement::insertGetId([
                        'quantity' =>  $req_qty,
                        'from_store'    =>  $fromStore,
                        'to_store' =>  $toStore,
                        $sm_col =>  $$sm_col,
                        'status' =>  'pending'
                ]);
                if(!$sm_id)
                {
                    DB::rollback();
                    return back()->with('error','Stock Movement Not Saved.')->withInput();
                }
            }
            $sm_id = intval($sm_id);
            // print_r($sm_id);die;
            if($sm_id > 0)
            {
                for($i=0; $i < count($product_data); $i++){
                    $req_qty = $qty[$i];
                    for($j = 0 ; $j < $req_qty ; $j++){
                        $product_det_id = StockMovementDetails::insertGetId([
                            'stock_movement_id' => $sm_id,
                            'product_id'    =>  $product_data[$i],
                            'product_details_id'    =>  0,
                            'status'    =>  'pending',
                            'quantity'  =>  1,
                            'created_by'    =>  $auth_id
                        ]);
                    }

                    // update in stock

                    $fromStock = Stock::where('product_id',$product_data[$i])
                                        ->where('store_id',$fromStore)
                                        ->decrement('quantity',$req_qty);

                    $existToStore = Stock::where('product_id',$product_data[$i])
                            ->where('store_id',$toStore)->select('id','quantity')->first();
                    if(isset($existToStore->quantity))
                    {
                        $toStock = Stock::where('id',$existToStore->id)->increment('quantity',$req_qty);
                        if(!$toStock){
                            DB::rollback();
                            return back()->with('error','Stock Not Updated')->withInput();
                        }
                    }
                    else
                    {
                        $insert_stock = Stock::insertGetId([
                            'product_id'    =>  $product_data[$i],
                            'quantity'  =>  $req_qty,
                            'min_qty'   =>  0,
                            'store_id'  =>  $toStore
                        ]);
                        if(!$insert_stock){
                            DB::rollback();
                            return back()->with('error','Stock Not Updated')->withInput();
                        }
                    }
                }
            }else{
                DB::rollback();
                return back()->with('error','Something Went Wrong.')->withInput();
            }
        } catch(\Illuminate\Database\QueryException $ex) {
            DB::rollBack();
            return back()->with('error','some error occurred'.$ex->getMessage())->withInput();
        }
        CustomHelpers::userActionLog($action='Stock Allocation',$sm_id,0,'Stock Movement');
        DB::commit();
        return back()->with("success","Stock Movement created successfully.");
    }

    public function stockView(){
       echo "not use";die;
        $data=array('layout'=>'layouts.main');
        return view('admin.stockView',$data);   
    }
    public function stockView_api(Request $request){
        echo "not use";die;
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
        
        $user_role_q = Users::where('id',Auth::Id())->first(['user_type','role']);
        $user_role = $user_role_q->toArray()['role'];
        $user_type = $user_role_q->toArray()['user_type'];
        $store_id = explode(',',Auth::user()->store_id);
        
      
            $api_data= StockMovement::
            leftJoin('store as from_store','from_store.id','stock_movement.from_store')
            ->leftJoin('store as to_store','to_store.id','stock_movement.to_store')
            ->leftJoin('waybill','waybill.id','stock_movement.waybill_id')
            ->leftJoin('loader','loader.id','stock_movement.loader_id')
            ->whereIn('store.id',$store_id)
            ;
           
                if($user_type == 'superadmin')
                {
                    $api_data=  $api_data->leftJoin('users',function($join) use($user_type){
                        $join->on('user_type',DB::raw('\''.$user_type.'\''));
                    });
                }
                else if($user_type == 'admin')
                {
                    $api_data=  $api_data->leftJoin('users',function($join) use($user_type){
                        $join->on('users.store_id','stock_movement.from_store');
                    });
                    
                }
                $api_data = $api_data->where('users.id',Auth::Id())
                    ->select(
                    DB::raw('stock_movement.id as id'),
                    DB::raw('stock_movement.quantity as quan'),
                    DB::raw('from_store.name as fromstore'),
                    DB::raw('to_store.name as tostore'),
                    DB::raw('waybill.waybill_number as waybill_no'),
                    DB::raw('loader.truck_number as loader_truck'),
                    DB::raw('stock_movement.schedule_date as schedule_date'),
                    DB::raw('stock_movement.status as status'),
                    DB::raw('users.role as user_role'),
                    DB::raw('users.user_type as type'),
                    DB::raw('users.store_id as selfstoreid')
                );

            if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->
                    where('stock_movement.quantity','like',"%".$serach_value."%")
                    ->orwhere('from_store.name','like',"%".$serach_value."%")
                    ->orwhere('to_store.name','like',"%".$serach_value."%")
                    ->orwhere('waybill.waybill_number','like',"%".$serach_value."%")
                    ->orwhere('loader.truck_number','like',"%".$serach_value."%")
                    ->orwhere('stock_movement.schedule_date','like',"%".$serach_value."%")
                    ->orwhere('stock_movement.status','like',"%".$serach_value."%");
                });
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                'from_store.name as fromstore',
                'to_store.name as tostore',
                'stock_movement.quantity as quan',
                'waybill.waybill_number as waybill_no',
                'loader.truck_number as loader_truck',
                'stock_movement.schedule_date as schedule_date',
                'stock_movement.status as status'
                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('id','desc');      
        
        $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);  
    }
    public function stockViewDel(Request $request,$id)
    {
        echo "not use";die;
        $del = StockMovement::where("id",$id)->delete();
        if($del)
        {
            $del1=StockMovementDetails::where("stock_movement_id",$id)->delete();
            if($del1)
                return back()->with('success','Data deleted successfully');
            else
                return back()->with('error','Data deleted Unsuccessfully');
        }
        return back()->with('error','Data deleted Unsuccessfully');
    }


    public function booking(){
        return view('admin.booking',['layout' => 'layouts.main']);
    }
    public function create_store(){
        return view('admin.create_store',['layout' => 'layouts.main']);
    }

    public function factory_list(){
         return view('admin.factory_data',['layout' => 'layouts.main']);
    }
    public function loader_list(){
         return view('admin.loader_data',['layout' => 'layouts.main']);
    }

    public function factory_list_api(Request $request) {
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
        
      $api_data= Factory::
                select(
                'factory.id',
                'factory.name',
                'factory.created_at'

                );
            if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('factory.name','like',"%".$serach_value."%")
                    ->orwhere('factory.created_at','like',"%".$serach_value."%")
                    ;
                });
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                'factory.id',
                'factory.name',
                'factory.created_at'
                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('factory.id','asc');      
        
        $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }



    public function loader_list_api(Request $request) {
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
        
         $api_data=Loader::select(
                                'loader.id',
                                'loader.type',
                                'loader.capacity',
                                'loader.truck_number',
                                'loader.status'
                            );
            if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('loader.type','like',"%".$serach_value."%")
                    ->orwhere('loader.truck_number','like',"%".$serach_value."%")
                    ->orwhere('loader.capacity','like',"%".$serach_value."%")
                    ->orwhere('loader.status','like',"%".$serach_value."%")
                    ;
                });
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                'loader.id',
                'loader.type',
                'loader.truck_number',
                'loader.capacity',
                'loader.status'
                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('loader.id','asc');      
        
        $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

    public function loader_update($id){

            $details=Loader::where('id',$id)->get();
            if($details->toArray())
            {
                $data=array(
                'id'=>$id,
                'loader'=>$details,
                'layout'=>'layouts.main'
                
                );
                return view('admin.update_loader', $data); 
            }
            else{
                 return redirect('/admin/loader/list/')->with('error','Id not exists');
            }
    }

    public function loader_updateDb(Request $request,$id)
    {
            try {
            
            $this->validate($request,[
            'loader_capacity'=>'required',
            'status'=>'required',
            
            ],[
            'loader_capacity.required'=> 'This Field is required.',
            'status.required'=> 'This Field is required..',
            ]);

            $details=Loader::where('id',$id)->get();
            if(!$details){
                 return redirect('/admin/loader/list/')->with('error','Id not exists');
            }
            else
            {
                $loaderdetails = Loader::where('id',$id)->update(
                [
                    'status'=>$request->input('status'),
                    'capacity'=>$request->input('loader_capacity')
                ]);
                
                if($loaderdetails == NULL) {
                    return redirect('/admin/loader/list/')->with('error','some error occurred');
                    } 
                else{                
                    return redirect('/admin/loader/list/')->with('success','Successfully Updated Loader details');           
                    }
            }

            }catch(\Illuminate\Database\QueryException $ex) {
            return redirect('/admin/loader/list/')->with('error','some error occurred');
        }
    }

    public function shortage_list(){
        $shortage = PartShortage:: groupBy('part_type')
                   ->selectRaw('*, sum(shortage_qty) as qty')
                   ->get();
        $data = array(
            'shortage' => $shortage,
            'layout' => 'layouts.main'
             );
         return view('admin.shortage_list',$data);
    }

    public function get_shortage_qty($id){
        $data = array(
            'lrn' => Unload::select("load_referenace_number","id")->get(),
            'qty' => PartShortage::where("id",$id)
                    ->select("shortage_qty","id","part_type")->get()
         );
        return response()->json($data);
    }

    public function update_shortage_qty(Request $request){
        try {
            DB::beginTransaction();
            
            $timestamp=date('Y-m-d G:i:s');
            $this->validate($request,[
                'lrn_id'=>'required',
                'qty'=>'required',
            ],[  
                'lrn_id.required'=> 'This is required.',  
                'qty.required'=> 'This is required.',   
            ]);

            $getdata = PartShortage::where('id',$request->input('id'))->first();
            if(!isset($getdata->id)){
                return back()->with('error','Shortage Data Not Found.');
            }
            if ($getdata->shortage_qty >= $request->input('qty')) {
                $newqty = $getdata->shortage_qty - $request->input('qty');
                 $shortagedata = PartShortage::where('id',$request->input('id'))->update(
                [
                    'shortage_qty'=>$newqty,
                ]);
                $user_id = Auth::Id();
               $receivedata = ShortageDetails::insertGetId(
                [
                    'unload_id'=>$getdata->load_referenace_number,
                    'part_shortage_id'=>$request->input('id'),
                    'receive_qty'=>$request->input('qty'),
                    'receive_date'=>$timestamp,
                    'created_by'=>Auth::id()
                ]);
                if($shortagedata==NULL || $receivedata == null) 
                {
                    DB::rollback();
                    return redirect('/admin/shortage/list')->with('error','Some Unexpected Error occurred.');
                }
            }else{
                return back()->with('error','Received Qty Should not be greater Shortage Qty.');
            }
        
        }  catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return redirect('/admin/shortage/list')->with('error','some error occurred'.$ex->getMessage());
        }
        /* Add action Log */
        CustomHelpers::userActionLog($action='Update Shortage Quantity',$getdata->id);
        DB::commit();
        return redirect('/admin/shortage/list')->with('success','Successfully Updated Quantity .'); 

    }

    public function show_shortage_log($id) {
        $data['data'] = ShortageDetails::leftJoin('part_shortage','shortage_details.part_shortage_id','=','part_shortage.id')->leftJoin('unload','part_shortage.load_referenace_number','=','unload.id')->get(['shortage_details.*','part_shortage.part_type as type','part_shortage.shortage_qty as shortage_qty','unload.load_referenace_number as lrn'])->where('part_shortage_id',$id);
        return json_encode($data);

    }

    public function shortage_list_api(Request $request){
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
        
      $api_data= PartShortage::leftJoin('factory','part_shortage.factory_id','factory.id')
            ->leftJoin('unload','part_shortage.load_referenace_number','unload.id')
            ->select(
                'part_shortage.id',
                'part_shortage.part_type',
                'part_shortage.shortage_qty',
                'factory.name as name',
                'unload.load_referenace_number as lrn_no',
                'part_shortage.created_at'
            );
            if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('factory.name','like',"%".$serach_value."%")
                    ->orwhere('part_shortage.part_type','like',"%".$serach_value."%")
                    ->orwhere('part_shortage.shortage_qty','like',"%".$serach_value."%")
                    ->orwhere('part_shortage.created_at','like',"%".$serach_value."%")
                    ->orwhere('unload.load_referenace_number','like',"%".$serach_value."%")
                    ;
                });
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                'part_shortage.id',
                'factory.name',
                'unload.load_referenace_number',
                'part_shortage.part_type',
                'part_shortage.shortage_qty',
                
                'part_shortage.created_at'
                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('part_shortage.id','asc');      
        
        $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

    public function createFactory() {
         return view('admin.create_factory',['layout' => 'layouts.main']);
    }

    public function createFactory_db(Request $request) {
          try {
            $timestamp=date('Y-m-d G:i:s');
            $this->validate($request,[
                'factory_name'=>'required',

            ],[
                'factory_name.required'=> 'This is required.'  
            ]);
            $customer = Factory::insertGetId(
                [
                    'id'=>NULL,
                    'name'=>$request->input('factory_name'),
                    'created_at'=>$timestamp,
                ]
            );
            if($customer==NULL) 
            {
                DB::rollback();
                return redirect('/admin/factory/create')->with('error','Some Unexpected Error occurred.');
            }
            else{
                return redirect('/admin/factory/create')->with('success','Successfully Created Factory.'); 
             }    
        
    }  catch(\Illuminate\Database\QueryException $ex) {
        return redirect('/admin/factory/create')->with('error','some error occurred'.$ex->getMessage());
    }
    }

  

  

   

       

}
