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
use \App\Model\PartShortage;
use \App\Model\ShortageDetails;
use \App\Model\Sale;
use \App\Model\SaleAccessories;
use \App\Model\SaleFinance;
use \App\Model\Scheme;
use \App\Model\Accessories;
use \App\Model\MasterAccessories;
use \App\Model\HiRise;
use \App\Model\Insurance;
use \App\Model\SaleOrder;
use \App\Model\RtoModel;
use \App\Model\Payment;
use \App\Model\OrderPendingModel;
use \App\Model\BookingModel;
use \App\Model\BestDealSale;
use \App\Model\ProductImages;
use \App\Model\AdditionalServices;
use \App\Model\AdditionalServicesAccessories;
use \App\Model\Settings;
use \App\Model\Master;
use \App\Model\AMCProduct;
use \App\Model\FinanceCompany;
use \App\Model\FinancierExecutive;
use \App\Model\InsuranceCompany;
use \App\Model\VehicleAddonStock;
use \App\Model\VehicleAddon;
use \App\Model\CallSetting;
use \App\Model\CallAssignSetting;
use \App\Model\Calling;
use \App\Model\CallData;
use \App\Model\CallPriority;
use \App\Model\HR_Leave;
use \App\Model\SubDealerMaster;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Auth;
use Hash;
use \Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\Import;
use Intervention\Image\ImageManagerStatic as Image;

class MastersController extends Controller
{
    public function __construct() {
        //$this->middleware('auth');
    }
    public function dashboard(){
        return view('admin.dashboard',['layout' => 'layouts.main']);
    }

    public function setting() {
        $product=ProductModel::leftjoin('product_details','product_details.product_id','product.id')
        ->where('product.type','=','product')->where('product.isforsale','1')->whereIn('product.model_category', ['MC','SC',])->select(['product.*','product_details.id as product_details_id'])->groupBy('product.id')->get();

        $used_product = BestDealSale::leftjoin('product','product.id','best_deal_sale.product_id')
                                       ->where('best_deal_sale.status','Ok')
                                        ->where('best_deal_sale.repaire_status','allok')
                                        ->where('best_deal_sale.sale_id','0')
                                         ->where('best_deal_sale.tos','best_deal')
                                        ->where('best_deal_sale.selling_price','>',0)
                                        ->where('best_deal_sale.status','<>','Pending')
                                        ->select('best_deal_sale.id as id',
                                        DB::raw('IF(best_deal_sale.product_id = 0,best_deal_sale.model,product.model_name) as model'),        
                                        DB::raw('IF(best_deal_sale.product_id = 0,best_deal_sale.variant,product.model_variant) as variant'),        
                                        DB::raw('IF(best_deal_sale.product_id = 0,best_deal_sale.color,product.color_code) as color')
                                       
            )->get();

        $setting = Settings::get();
        $pass=[];
        $setting=$setting->toArray();
        
        for($i=0;$i<count($setting);$i++)
        {
            $pass+=[$setting[$i]['name'] => $setting[$i]['value']];
        }

        $data=array(
            'layout'=>'layouts.main',
            'settings'=>$pass,
            'product'=>$product,
            'used_product'=>$used_product
        );
         return view('admin.setting.setting_form', $data);
    }

    public function settingaddform(Request $request) {
        $validateZero = [];
        $validateZeroMsg = [];
            $this->validate($request,array_merge($validateZero,[     

            'garage_charge'=>'required|numeric|min:0',      
            'garage_charges_after'=>'required|numeric|min:0',      
        ]),array_merge($validateZeroMsg,[
           
            'garage_charge.required' => 'This field is required.',
            'garage_charges_after.required' => 'This field is required.'

        ]));
        try
        {
            $id=Auth::id();
            $input = $request->all();
            unset($input['userAlloweds']);
            unset($input['sub']);
            foreach($input as $key=>$val)
            {
                if($key=='New_Product'){
                    $val = implode(',', $request->input('New_Product'));
                }
                if($key=='Used_Product'){
                    $val = implode(',', $request->input('Used_Product'));
                }
                if($key=='_token')
                    continue;                    
                $count = Settings::where('name',$key)->count();

                if($count==0)
                {
                   $var= Settings::insert([
                        'name'=>$key,
                        'value'=>$val
                    ]);
                }
                else
                {
                    $var=Settings::where('name',$key)->update([
                        'updated_at'=>date('Y-m-d G:i:s'),
                        'value'=>$val,
                        
                    ]);

                }
            }
          
            return redirect('/admin/settings')->with('success', 'Settings has been updated!');
        }
        catch(\Illuminate\Database\QueryException $ex) {
            return redirect('/admin/settings')->with('error','some error occurred'.$ex->getMessage());
      }

    }


    public function Finance_Executive($id=null) {
        $user = Users::where('id',Auth::Id())->get()->first();
        $company = FinanceCompany::all();
        $store = Store::whereIn('id',CustomHelpers::user_store())->get();
        $executive = FinancierExecutive::where('id',$id)->get()->first();
        $data = array(
            'company' => $company,
            'store' => $store,
            'user' => $user,
            'executive' => $executive,
            'layout' => 'layouts.main'
        );
        return view('admin.master.finance_company',$data);
    }



    public function financerList_api(Request $request) {
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
            $api_data = FinancierExecutive::leftjoin('finance_company','finance_company.id','financier_executive.finance_company_id')
                ->leftjoin('store','financier_executive.store_id','store.id')
                ->select('financier_executive.id',
                    'financier_executive.executive_name',
                    'financier_executive.mobile_numbers',
                    'finance_company.company_name',
                    'finance_company.payout',
                    'finance_company.trade_type',
                    'finance_company.amount',
                    DB::raw('concat(store.name,"-",store.store_type) as store_name')
                );

            
            if(!empty($serach_value))
            {
               $api_data->where(function($query) use ($serach_value){
                        $query->where('financier_executive.id','like',"%".$serach_value."%")
                        ->orwhere('financier_executive.executive_name','like',"%".$serach_value."%")
                        ->orwhere('financier_executive.mobile_numbers','like',"%".$serach_value."%")
                        ->orwhere('finance_company.company_name','like',"%".$serach_value."%")
                        ->orwhere('finance_company.trade_type','like',"%".$serach_value."%")
                        ->orwhere('finance_company.amount','like',"%".$serach_value."%")
                        ->orwhere('finance_company.payout','like',"%".$serach_value."%")
                        ->orwhere('store.name','like',"%".$serach_value."%");
                    });
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [

                    'financier_executive.executive_name',
                    'financier_executive.mobile_numbers',
                    'finance_company.company_name',
                    'finance_company.payout',
                    'finance_company.trade_type',
                    'finance_company.amount',
                    'store.name'
                
                ];
                
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('financier_executive.id','desc');   
        
        $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }
    public function GetFinance_Exicutive($id) {
        $getfinancer = FinanceCompany::leftjoin('financier_executive','finance_company.id','financier_executive.finance_company_id')
                ->leftjoin('store','financier_executive.store_id','store.id')
                ->where('financier_executive.finance_company_id',$id)
                ->select('financier_executive.id',
                    'financier_executive.executive_name',
                    'financier_executive.mobile_numbers',
                    'finance_company.company_name',
                    'finance_company.payout',
                    'finance_company.trade_type',
                    'finance_company.amount',
                    DB::raw('concat(store.name,"-",store.store_type) as store_name'))->get();
        return response()->json($getfinancer);
    }

    public function FinanceExecutive_DB(Request $request) {
         $validator = Validator::make($request->all(),[
                'company_name'=>'required',
                'financer'=>'required',
                'store'=>'required',
                'number1'=>'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10|max:10',
                'number2'=>'nullable|regex:/^([0-9\s\-\+\(\)]*)$/|min:10|max:10',

        ],
        [
                'company_name.required'=> 'This field is required.',
                'financer.required'=> 'This field is required.',
                'store.required'=> 'This field is required.',
                'number1.required'=> 'This field is required.',
                'number2.required'=> 'This field is required.',
                'number1.regex'=> 'First Mobile No contains digits only.',
                'number2.regex'=> 'Second Mobile No contains digits only.',
                'number1.max'=> 'First Mobile No may not be greater than 10 digits.',
                'number2.max'=> 'First Mobile No may not be greater than 10 digits.',
                'number1.min'=> 'First Mobile No must be at least 10 digits.',
                'number2.min'=> 'Second Mobile No must at least 10 digits.',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {

        if (!empty($request->input('number1'))) {
                  $validator = Validator::make($request->all(),[
                'number1'=>'required|regex:/^([6-9][0-9]{9}[\s\-\+\(\)]*)$/|min:10|max:10',
        ],
        [
                'number1.required'=> 'This field is required.',
                'number1.regex'=> 'First Mobile No contains digits only.',
                'number1.max'=> 'First Mobile No may not be greater than 10 digits.',
                'number1.min'=> 'First Mobile No must at least 10 digits.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
    }
            
        if (!empty($request->input('number2'))) {
                  $validator = Validator::make($request->all(),[
                 'number2'=>'required|regex:/^([6-9][0-9]{9}[\s\-\+\(\)]*)$/|min:10|max:10',
        ],
        [
                'number2.required'=> 'This field is required.',
                'number2.regex'=> 'Second Mobile No contains digits only.',
                'number2.max'=> 'Second Mobile No may not be greater than 10 digits.',
                'number2.min'=> 'Second Mobile No must at least 10 digits.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
                 $data =  $request->input('number1').','.$request->input('number2');
            }else{
                 $data =  $request->input('number1');
            }
      
             if (!empty($request->input('executive_id'))) {
                $id = $request->input('executive_id');
                 $update = FinancierExecutive::where('id',$id)->update([
                        // 'finance_company_id' => $request->input('company_name'),
                        'executive_name'=> $request->input('financer'),
                        'store_id' => $request->input('store'),
                        'mobile_numbers' => $data
                    ]);
                 if($update == NULL ) {
                    return redirect('/admin/master/finance/executive/'.$id)->with('error','some error occurred')->withInput();
                } else{

                    CustomHelpers::userActionLog($action='Financer Executive Update',$id,0);

                    return redirect('/admin/master/finance/executive/'.$id)->with('success',' Financer Updated Successfully.');
                
                }
             }else{

                if(!$request->input('company_name')){

                    return redirect('/admin/master/finance/executive/')->with('error','Company Name is required')->withInput();
                }else{

                    $master = FinancierExecutive::insertGetId([
                    'finance_company_id' => $request->input('company_name'),
                    'executive_name'=> $request->input('financer'),
                    'store_id' => $request->input('store'),
                    'mobile_numbers' => $data
                    ]);
                    if($master == NULL ) {
                        return redirect('/admin/master/finance/executive')->with('error','some error occurred')->withInput();
                    } else{
                        return redirect('/admin/master/finance/executive')->with('success',' Financer added Successfully.');
                    
                    }
                
                }

                
             }

        }  catch(\Illuminate\Database\QueryException $ex) {
            return redirect('/admin/master/finance/company')->with('error','some error occurred'.$ex->getMessage());
        }
    }

    public function setting_email() {
        $setting = Settings::get();
        $pass=[];
        $setting=$setting->toArray();
        
        for($i=0;$i<count($setting);$i++)
        {
            $pass+=[$setting[$i]['name'] => $setting[$i]['value']];
        }
        $data=array(
            'layout'=>'layouts.main',
            'settings'=>$pass
        );
         return view('admin.setting.setting_email', $data);
    }

    public function setting_email_DB(Request $request) {
        //print_r($request->input());die();

       
        try
        {
            $id=Auth::id();
            $input = $request->all();
            unset($input['userAlloweds']);
            unset($input['sub']);
            foreach($input as $key=>$val)
            {
                if($key=='_token')
                    continue;                    
                $count = Settings::where('name',$key)->count();
                if($count==0)
                {
                   $var= Settings::insert([
                        'name'=>$key,
                        'value'=>$val
                    ]);
                }
                else
                {
                    $var=Settings::where('name',$key)->update([
                        'updated_at'=>date('Y-m-d G:i:s'),
                        'value'=>$val,
                        
                    ]);

                }
            }
          
            return redirect('/admin/setting/email')->with('success', 'Email Setting has been updated!');
        }
        catch(\Illuminate\Database\QueryException $ex) {
            return redirect('/admin/setting/email')->with('error','some error occurred'.$ex->getMessage());
      }

    }
    public function master_list(){

        $master = Master::select('type')->groupBy('type')->get()->whereNotIn('type',['company_name','financier_executive','insurance_company','selling_dealer']);
        $data = array('master'=>$master,'layout'=>'layouts.main');
         return view('admin.master.master_list', $data);
    }

    public function master_list_api(Request $request) {
        
        $search = $request->input('search');
        $store_name = $request->input('store_name');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
        
        $api_data = Master::select(
                            'Id',
                            DB::raw('upper(replace(type,"_"," ")) as type'),
                            'key',
                            'value',
                            'details'
                        )->whereNotIn('type',['company_name','financier_executive','insurance_company','selling_dealer']);
        if(!empty($serach_value))
        {
            $api_data->where(function($query) use ($serach_value){
                $query->where('master.type','like',"%".$serach_value."%")
                ->orwhere('master.key','like',"%".$serach_value."%")
                ->orwhere('master.value','like',"%".$serach_value."%")
                ->orwhere('master.details','like',"%".$serach_value."%");
            });
        }
        if(!empty($store_name))
        {
           $api_data->where(function($query) use ($store_name){
                    $query->where('master.type','like',"%".$store_name."%");
                });
        }
        if(isset($request->input('order')[0]['column']))
        {
            $data = [
                    'Id',
                    'type',
                    'key',
                    'value',
                    'details'
            ];
            $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
            $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
        }
        else
            $api_data->orderBy('master.Id','asc');

        $count = count($api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

    public function master_accessories_list(){
         return view('admin.master.accessories_list',['layout' => 'layouts.main']);
    }

     public function master_accessories_list_api(Request $request) {
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;

      $api_data=MasterAccessories::leftjoin("part",\DB::raw("FIND_IN_SET(part.id,master_accessories.part_id)"),">",\DB::raw("'0'"))
                            ->select(
                               'master_accessories.id',
                               'master_accessories.model_name',
                               'master_accessories.accessories_name',
                               DB::raw('group_concat(part.name," (",part.price,") (",part.part_number,") ") as part'),

                               'master_accessories.connection',
                               'master_accessories.hop',
                               'master_accessories.full',
                               'master_accessories.discount_offset_priority'
                                
                            )->groupBy('master_accessories.id');

            if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('master_accessories.model_name','like',"%".$serach_value."%")
                    ->orwhere('master_accessories.accessories_name','like',"%".$serach_value."%")
                    ->orwhere('master_accessories.connection','like',"%".$serach_value."%")
                    ->orwhere('master_accessories.hop','like',"%".$serach_value."%")
                    ->orwhere('master_accessories.full','like',"%".$serach_value."%")
                    ->orwhere('part.name','like',"%".$serach_value."%")
                    ->orwhere('part.part_number','like',"%".$serach_value."%")
                    ->orwhere('part.price','like',"%".$serach_value."%")
                    ->orwhere('master_accessories.discount_offset_priority','like',"%".$serach_value."%")
                    ;
                });
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                    // 'master_accessories.id',
                    'master_accessories.model_name',
                    'master_accessories.accessories_name',
                    'part',
                    'master_accessories.connection',
                    'master_accessories.hop',
                    'master_accessories.full',
                    'master_accessories.discount_offset_priority'
              
                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('master_accessories.id','asc');      
        
        $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();

        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

    public function amc_product_list(){

      return view('admin.master.amc_product_list',['layout' => 'layouts.main']);
         
    }

     public function amc_product_list_api(Request $request) {
        
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
        
        
        $api_data = AMCProduct::select(
                            'id',
                            'name',
                            'duration',
                            'service_allowed',
                            'washing',
                            'price',
                            'min_price',
                            'max_price'

                            
                        );
            if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('amc_product.name','like',"%".$serach_value."%")
                    ->orwhere('amc_product.duration','like',"%".$serach_value."%")
                    ->orwhere('amc_product.service_allowed','like',"%".$serach_value."%")
                    ->orwhere('amc_product.washing','like',"%".$serach_value."%")
                    ->orwhere('amc_product.price','like',"%".$serach_value."%")
                    ->orwhere('amc_product.min_price','like',"%".$serach_value."%")
                    ->orwhere('amc_product.max_price','like',"%".$serach_value."%");
                });
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                        'id',
                        'name',
                        'duration',
                        'service_allowed',
                        'washing',
                        'price',
                        'min_price',
                        'max_price'
                        
                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else            
                $api_data->orderBy('amc_product.id','asc');



        $count = count($api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

     public function createVehicle() {
         return view('admin.create_vehicle',['layout' => 'layouts.main']);
    }

     public function createVehicle_db(Request $request) {
            
        $validator = Validator::make($request->all(),[
                'model_category'=>'required',
                'model_name' => 'required',
                'model_varient'=>'required',
                'color_code'=>'required',
        ],
        [
                'model_category.required'=> 'This is required.',  
                'model_name.required'=> 'This is required.',  
                'model_varient.required'=> 'This is required.',  
                'color_code.required'=> 'This is required.'
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        try {

            $check_data=ProductModel::where('model_name','like',"%".$request->input('model_name')."%")
            ->where('model_variant','like',"%".$request->input('model_varient')."%")
            ->where('color_code','like',"%".$request->input('color_code')."%")
            ->get()->first();


            if($check_data){

                 return redirect('/admin/master/product/create')->with('error','This Product Already exists.')->with('model_category', $request->input('model_category'))->with('model_name', $request->input('model_name'))->with('model_varient', $request->input('model_varient'))->with('color_code', $request->input('color_code'));
            }else{

                $customer = ProductModel::insertGetId(
                    [
                        'model_category'=>$request->input('model_category'),
                        'model_name'=>$request->input('model_name'),
                        'model_variant'=>$request->input('model_varient'),
                        'color_code'=>$request->input('color_code')
                    ]
                  );

               if($customer==NULL) 
                 {
                    DB::rollback();
                    return redirect('/admin/master/product/create')->with('error','Some Unexpected Error occurred.');
                 }
                else{

                    CustomHelpers::userActionLog($action='Create Product',$customer,0);

                    return redirect('/admin/master/product/create')->with('success','Successfully Created Product.'); 
                 }   
                }
            
        
        }  catch(\Illuminate\Database\QueryException $ex) {
           return redirect('/admin/master/product/create')->with('error','some error occurred'.$ex->getMessage());
    }
    }

    public function vehicle_list(){
            
             return view('admin.master.vehicle_list', ['layout'=>'layouts.main']);
     }

     public function vehicle_list_api(Request $request) {
        $search = $request->input('search');
        $store_name = $request->input('store_name');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;

      $api_data=ProductModel::where('product.type','=','product')
                            ->whereIn('product.model_category', ['MC','SC',])
                            ->select(
                                'product.id',
                                'product.model_category',
                                'product.model_name',
                                'product.model_variant',
                                'product.color_code',
                                'product.isforsale'
                            );

            if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('product.model_category','like',"%".$serach_value."%")
                    ->orwhere('product.model_name','like',"%".$serach_value."%")
                    ->orwhere('product.model_variant','like',"%".$serach_value."%")
                    ->orwhere('product.color_code','like',"%".$serach_value."%")
                    ->orwhere('product.isforsale','like',"%".$serach_value."%")
                    ;
                });
            }
            if(!empty($store_name))
            {
                if($store_name=='no'){

                     $api_data->where(function($query) use ($store_name){
                        $query->where('product.isforsale','=',"0");
                    });

                }elseif($store_name=='yes'){

                     $api_data->where(function($query) use ($store_name){
                        $query->where('product.isforsale','=',"1");
                    });

                }
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                'product.id',
                'product.model_category',
                'product.model_name',
                'product.model_variant',
                'product.color_code',
                'product.isforsale'
              
                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('product.id','asc');      
        
        $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();

        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

    public function master_insurance_company_list(){
            
             return view('admin.master.insurance_company_list', ['layout'=>'layouts.main']);
     }

     public function master_insurance_company_list_api(Request $request) {
        $search = $request->input('search');
        $store_name = $request->input('store_name');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;

      $api_data=InsuranceCompany::
                            select(
                                'Id',
                                'name',
                                'cpa'
                            );

            if(!empty($serach_value))
            {
                if($serach_value=='no' || $serach_value=='0' ){
                   

                    $api_data->where(function($query) use ($serach_value){
                        $query->where('insurance_company.cpa','=',"0");
                    });
                }elseif($serach_value=='yes' || $serach_value=='1'){

                     $api_data->where(function($query) use ($serach_value){
                        $query->where('insurance_company.cpa','=',"1");
                    });
               }elseif($serach_value!='no' || $serach_value!='yes')
                 $api_data->where(function($query) use ($serach_value){
                    $query->where('insurance_company.name','like',"%".$serach_value."%");
                });

            }
            if(!empty($store_name))
            {
                if($store_name=='no'){

                     $api_data->where(function($query) use ($store_name){
                        $query->where('insurance_company.cpa','=',"0");
                    });

                }elseif($store_name=='yes'){

                     $api_data->where(function($query) use ($store_name){
                        $query->where('insurance_company.cpa','=',"1");
                    });

                }
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                'insurance_company.Id',
                'insurance_company.name',
                'insurance_company.cpa'
              
                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('insurance_company.Id','asc');      
        
        $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();

        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

    public function vehicleAddon_stocklist(){
            
             return view('admin.master.vehicle_addon_stocklist', ['layout'=>'layouts.main']);
     }

     public function vehicleAddon_stocklistapi(Request $request) {
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;

        $api_data=VehicleAddonStock::leftjoin('store','vehicle_addon_stock.store_id','store.id')
                            ->select(
                                'vehicle_addon_stock.id',
                                DB::raw("UPPER(REPLACE(vehicle_addon_stock.vehicle_addon_key,'_',' ')) AS prod_name"),
                                'vehicle_addon_stock.qty',
                                'vehicle_addon_stock.sale_qty',
                                 DB::raw('concat(store.name,"-",store.store_type) as store_name')
                            );
        // print_r($api_data);die;
        if(!empty($serach_value))
        {
            $api_data->where(function($query) use ($serach_value){
                    $query->where('vehicle_addon_stock.vehicle_addon_key','like',"%".$serach_value."%")
                    ->orwhere(DB::raw("UPPER(REPLACE('_',' ',vehicle_addon_stock.vehicle_addon_key))"),'like',"%".$serach_value."%")
                    ->orwhere('vehicle_addon_stock.qty','like',"%".$serach_value."%")
                    ->orwhere('vehicle_addon_stock.sale_qty','like',"%".$serach_value."%")
                     ->orwhere('store.name','like',"%".$serach_value."%")
                    ;
            });
        }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                    'prod_name',
                    'vehicle_addon_stock.qty',
                    'vehicle_addon_stock.sale_qty',
                    'store_name'
                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('vehicle_addon_stock.store_id','asc')
                            ->orderBy('vehicle_addon_stock.vehicle_addon_key','ASC');      
        
        $count = $api_data->count();
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();

        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

    public function subdealer_masterlist(){
            
        return view('admin.master.subdealer_masterlist', ['layout'=>'layouts.main']);
     }

     public function subdealer_masterlistapi(Request $request) {
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;

      $api_data=SubDealerMaster::
                            select(
                                'id',
                                'display_name',
                                'actual_name'
                            );

            if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('sub_dealer_master.display_name','like',"%".$serach_value."%")
                     ->orwhere('sub_dealer_master.actual_name','like',"%".$serach_value."%")
                    ;
                });
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                      'display_name',
                      'actual_name'
              
                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('sub_dealer_master.id','asc');      
        
        $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();

        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

    public function finance_company_list(){
         return view('admin.finance_company_list',['layout' => 'layouts.main']);
    }

    public function finance_company_list_api(Request $request) {
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;

      $api_data = FinanceCompany::select(
                                'finance_company.Id',
                                'finance_company.company_name',
                                'finance_company.payout',
                                'finance_company.trade_type',
                                'finance_company.amount'
                            );

            if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('finance_company.company_name','like',"%".$serach_value."%")
                    ->orwhere('finance_company.payout','like',"%".$serach_value."%")
                    ->orwhere('finance_company.trade_type','like',"%".$serach_value."%")
                    ->orwhere('finance_company.amount','like',"%".$serach_value."%")
                    ;
                });
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                'finance_company.Id',
                'finance_company.company_name',
                'finance_company.payout',
                'finance_company.trade_type',
                'finance_company.amount'
              
                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('finance_company.Id','asc');      
        
        $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();

        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

    public function GetFinance_Company($id) {
        $getdata = FinanceCompany::where('id',$id)->get()->first();
         return response()->json($getdata);
    }

    public function UpdateFinanceCompany_DB(Request $request) {
        $payout = $request->input('payout');
        $id = $request->input('id');
        $trade_type = $request->input('trade_type');
        $amount = $request->input('amount');
        if (empty($trade_type)) {
            return array('type' => 'errors', 'msg' => 'Trade Type is required');
        }
        if (empty($payout)) {
            return array('type' => 'errors', 'msg' => 'Payout is required');
        }if (empty($amount)) {
            return array('type' => 'errors', 'msg' => 'Amount is required');
        }else{
            $update = FinanceCompany::where('id',$id)->update([
                'payout' => $payout,
                'trade_type' => $trade_type,
                'amount' => $amount
            ]);

            if ($update == NULL) {
                 return array('type' => 'error', 'msg' => 'Something went wrong.');
            }else{

                 CustomHelpers::userActionLog($action='Finance Executive Update',$id,0);
                 return array('type' => 'success', 'msg' => 'Company details updated successfully.');
            }
        }
    }

    public function vehicle_view($id) {

        $product_data=ProductModel::leftjoin('product_details','product_details.product_id','product.id')
                            ->leftjoin('store','product_details.store_id','store.id')
                            ->leftjoin('unload','product_details.unload_id','unload.id')
                            ->where('product.type','=','product')
                            ->where('product.id',$id)
                            ->select(
                                'product.*',
                                'product_details.*',
                                DB::raw('concat(store.name,"-",store.store_type) as store_name'),
                                'unload.load_referenace_number'
                            )->get()->first();
         if(!$product_data){
            return redirect('/admin/master/product/list')->with('error','Id not exist.');
        }
       else{

         $data=array(
            'product_data'=>$product_data,
            'layout' => 'layouts.main'
           );

          return view('admin.master.vehicle_view',$data);
       }
    }

    public function vehicle_Uploadpic(Request $request){
        $ImageSize = Settings::where('name','image_size')->get()->first();
        $size = $ImageSize['value'];
         if(!isset($size)){
              return response()->json(['messages' => 'Image Size For Validation Master Not Found.'],422);
        }
        $product_id   = $request->input('product_id');

        $allowed_extension = array('jpeg,jpg,png');
        $img_valid = []; $req = [];
        $front_img = $request->input('pre_front_img'); 
        if(empty($front_img)){
            array_push($req,'front');
        }
        if(empty($front_img)){
            $img_valid['front'] =  'dimensions:ratio=1/1|required:'.join(',',$req);
        }
        // $validator = Validator::make($request->all(),$img_valid);
       
        $validator = Validator::make($request->all(),[
                'front'=>'required|max:'.$size.'|dimensions:min_width=300,min_height=300',
                
        ],
        [
                'front.required'=> 'This is required.',  
                'front.dimensions'=> 'Image min size should be 300x300', 
               
        ]);
        $validator->sometimes('front', 'mimes:jpeg,jpg,png', function ($input) use($allowed_extension) {
            $extension="";
            if(isset($input->front))
            $extension = $input->front->getClientOriginalExtension();
            if(in_array($extension,$allowed_extension))
                return false;
            else
                return true;
        });

        if ($validator->fails()) {
            return response()->json($validator->errors(),422);
        }

        try{

            if (!empty($request->hasFile('front'))) {
                $validator = Validator::make($request->all(),[
                           'front'=>'required|dimensions:ratio=1/1',
                    ],
                    [
                            'front.dimensions'=> 'Image height and width must be equal.',
                    ]);

                    if ($validator->fails()) {
                         return response()->json($validator->errors(),422);
                    }
                }

            // image validation :- check image dublicate

            $files_name = [];

            $get_old_image_name = ProductImages::where('product_id',$product_id)->pluck('image');
            if(isset($get_old_image_name[0])){
                $files_name = $get_old_image_name->toArray();
            }
            // return response()->json($files_name,422);

            if($request->hasFile('front')){
                $file = $request->file('front');
                $filenameWithExt=$file->getClientOriginalName();
                $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME); 
                $extension = $file->getClientOriginalExtension();    
                $fileNameToStore = $filename.'_'.$product_id.'.'.$extension;     
                if(in_array($fileNameToStore,$files_name)){
                    return response()->json(['messages' => 'This image is already uploaded.'],422);
                }
                array_push($files_name,$fileNameToStore);
            }
            // valiudation end ----------------------
            // start saving--------------
            DB::beginTransaction();
            if($request->hasFile('front'))
            {
                    $file = $request->file('front');

                    $filenameWithExt=$file->getClientOriginalName();
                    $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME); 
                    $extension = $file->getClientOriginalExtension();    
                    $fileNameToStore = $filename.'_'.$product_id.'.'.$extension; 

                    $destinationPath = public_path().'/upload/product';
                    $destinationPaths = public_path().'/upload/product/resized';
                    
                    $frontimg=$fileNameToStore;
                    $width = Image::make($file)->width();
                    $height = Image::make($file)->height();
                    
                    $img = Image::make($file)
                        ->resize(300, 300)
                        ->save($destinationPaths.'/'. $fileNameToStore);

                    $file->move($destinationPath,$fileNameToStore);

                    $insert = ProductImages::insertGetId([
                        'product_id' => $product_id,
                        'image' => $frontimg
                        ]);

            }
            if(empty($insert)) {
                       DB::rollback();
                       return response()->json(['messages' => 'Some Error Occurred.'],422);
            }else{
                    DB::commit();
                    CustomHelpers::userActionLog($action='Product Image Upload',$insert,0);
                    return 'Image Uploaded Successfully.';
            }

        }
        catch(\Illuminate\Database\QueryException $ex) {
            DB::commit();
            return response()->json('some error occurred'.$ex->getMessage(),422);
        }
    }

    public function uploadpic_data(Request $request)
    {
        $product_id = $request->input('product_id');
        $product_data = ProductImages::where('product_id','=',$product_id)->get();
         return response()->json($product_data);

    }  

    public function uploadpic_delete(Request $request)
    {   
        $picId = $request->input('id');
        $getcheck = ProductImages::where('id',$picId)->get()->first();

        if($getcheck)
        {
             $getimage=$getcheck->image;
             $image_path = public_path().'/upload/product/'.$getimage;
             $resized_image_path = public_path().'/upload/product/resized/'.$getimage;
             $picdate = ProductImages::where('id',$picId)->delete();
            if(empty($picdate)) {
                    return 'error';
                }else{
                    
                    if(\File::exists($image_path)) {
                      \File::delete($image_path);
                     } 
                     if(\File::exists($resized_image_path)) {
                      \File::delete($resized_image_path);
                     }

                    CustomHelpers::userActionLog($action='Product Uploaded Image delete',$picId,0);
                    return 'success';
                }
        }else{

            return 'No Data';
        }

    } 

     public function uploadpic_setdefault(Request $request)
    {   
        $picId = $request->input('id');
        $productId = $request->input('product_id');

        $getcheck = ProductImages::where('id',$picId)->get()->first();

        if($getcheck)
        {
            $productdata = ProductImages::where('product_id',$productId)->update(
                [
                'default_image' =>'0'
            ]);
             $picdata = ProductImages::where('id',$picId)->update(
                [
                'default_image' =>'1'
            ]);
            if(($picdata==null) || ($productdata==null)) {
                    return 'error';
                }else{

                    CustomHelpers::userActionLog($action='Product Uploaded Image Set Default',$picId,0);
                    return 'success';
                }
        }else{
            return 'No Data';
        }

    }

    public function city_list(){

        return view('admin.master.city_list',['layout' => 'layouts.main']);
    }

    public function city_list_api(Request $request){

        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
        
        $api_data=City::leftjoin('states','cities.state_id','states.id')
                            ->select(
                                'cities.id',
                                'cities.city',
                                'states.name as state'
                            );
        if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('cities.city','like',"%".$serach_value."%")
                    ->orwhere('states.name','like',"%".$serach_value."%");
                });
            }
        if(isset($request->input('order')[0]['column']))
            {
                $data = [
                'cities.id',
                'cities.city',
                'state'
                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
        else
                $api_data->orderBy('cities.id','asc');      
        
        $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }   

}

