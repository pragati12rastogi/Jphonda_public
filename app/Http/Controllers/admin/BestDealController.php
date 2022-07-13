<?php

namespace App\Http\Controllers\admin;
use Illuminate\Http\Request;
use \stdClass;
use \App\Model\Store;
use \App\Model\ProductModel;
use \App\Model\ExtendedWarranty;
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
use \App\Model\SaleDiscount;
use \App\Model\RtoModel;
use \App\Model\Payment;
use \App\Model\OrderPendingModel;
use \App\Model\BookingModel;
use \App\Model\BestDealSale;
use \App\Model\BestDealMaster;
use \App\Model\BestDealCheckList;
use \App\Model\SecurityModel;
use \App\Model\Part;
use \App\Model\PartStock;
use \App\Model\AdditionalServices;
use \App\Model\AdditionalServicesAccessories;
use \App\Model\FuelModel;
use \App\Model\AMC;
use \App\Model\HJCModel;
use \App\Model\ServiceModel;
use \App\Model\Master;
use \App\Model\EwMaster;
use \App\Model\OtcSale;
use \App\Model\PaymentRequest;
use \App\Model\OtcSaleDetail;
use \App\Model\BestDealSaleDocument;
use \App\Model\SaleRto;
use \App\Model\RtoSummary;
use \App\Model\InsuranceCompany;
use \App\Model\FuelStockModel;
use \App\Model\PollutionCertificate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Auth;
use Hash;
use \Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\Import;
use \App\Model\Job_card;
use \App\Model\BestDealPhoto;
use App\Model\FinanceCompany;
use App\Model\InsuranceData;
use App\Model\Settings;

class BestDealController extends Controller
{
    public function __construct()
    {
        //$this->middleware('auth');
    }
    public function create_best_deal(){
      
        
        $store = Store::whereIn('id',CustomHelpers::user_store())
                    ->select('id',DB::raw('concat(store.name,"-",store.store_type) as name'))->get();
        
        $model_name = ProductModel::where('type','product')->orderBy('model_name','ASC')->groupBy('model_name')->get(['model_name']);
        
        $sale_user = Users::where('role','SaleExecutive')->get(['id','name']);

        // $maker = array('Honda','Bajaj','Hero','TVS');
        $maker = Master::where('type','prod_maker')->orderBy('order_by')->get();
        $company_name = FinanceCompany::orderBy('id','ASC')->get();
        
        $insur_company = InsuranceCompany::orderBy('id')->get();

        $data = array(
            'layout' => 'layouts.main',
            'csd'   =>  CustomHelpers::csd_amount(),
            'model_name'    =>  ($model_name->toArray())? $model_name->toArray() : array(),
            'store'    =>  $store,
            'sale_user' =>  ($sale_user->toArray()) ? $sale_user->toArray() : array() ,
            'maker' =>  $maker,
            'company_name'  =>  $company_name,
            'insur_company' =>  $insur_company

        );
        return view('admin.bestdeal.create_best_deal',$data);
    }
    
    public function create_best_dealDB(Request $request)
    {

        $validation = [];
        $val_msg = [];
        $insurance_check = [];
        if(!empty($request->input('od-best-insurance_policy')) || !empty($request->input('od-best-insurance_company')) || 
            !empty($request->input('od-best-insurance_policy_start')) || !empty($request->input('od-best-insurance_policy_end')))
            {
                $validation['od-best-insurance_policy'] = 'required_if:best-insurance,yes';  
                $validation['od-best-insurance_company'] = 'required_if:best-insurance,yes';  
                $validation['od-best-insurance_policy_start'] = 'required_if:best-insurance,yes';  
                $validation['od-best-insurance_policy_end'] = 'required_if:best-insurance,yes';  
                // msg
                $val_msg['od-best-insurance_policy.required_if'] = 'This Field is required';  
                $val_msg['od-best-insurance_company.required_if'] = 'This Field is required';  
                $val_msg['od-best-insurance_policy_start.required_if'] = 'This Field is required';  
                $val_msg['od-best-insurance_policy_end.required_if'] = 'This Field is required';  
                $insurance_check['od'] = 1;
            }
        if(!empty($request->input('ta-best-insurance_policy')) || !empty($request->input('ta-best-insurance_company')) || 
            !empty($request->input('ta-best-insurance_policy_start')) || !empty($request->input('ta-best-insurance_policy_end')))
            {
                $validation['ta-best-insurance_policy'] = 'required_if:best-insurance,yes';  
                $validation['ta-best-insurance_company'] = 'required_if:best-insurance,yes';  
                $validation['ta-best-insurance_policy_start'] = 'required_if:best-insurance,yes';  
                $validation['ta-best-insurance_policy_end'] = 'required_if:best-insurance,yes';  
                // msg
                $val_msg['ta-best-insurance_policy.required_if'] = 'This Field is required';  
                $val_msg['ta-best-insurance_company.required_if'] = 'This Field is required';  
                $val_msg['ta-best-insurance_policy_start.required_if'] = 'This Field is required';  
                $val_msg['ta-best-insurance_policy_end.required_if'] = 'This Field is required'; 
                $insurance_check['ta'] = 1; 
            }
        if(!empty($request->input('cpa-best-insurance_policy')) || !empty($request->input('cpa-best-insurance_company')) || 
            !empty($request->input('cpa-best-insurance_policy_start')) || !empty($request->input('cpa-best-insurance_policy_end')))
            {
                $validation['cpa-best-insurance_policy'] = 'required_if:best-insurance,yes';  
                $validation['cpa-best-insurance_company'] = 'required_if:best-insurance,yes';  
                $validation['cpa-best-insurance_policy_start'] = 'required_if:best-insurance,yes';  
                $validation['cpa-best-insurance_policy_end'] = 'required_if:best-insurance,yes';  
                // msg
                $val_msg['cpa-best-insurance_policy.required_if'] = 'This Field is required';  
                $val_msg['cpa-best-insurance_company.required_if'] = 'This Field is required';  
                $val_msg['cpa-best-insurance_policy_start.required_if'] = 'This Field is required';  
                $val_msg['cpa-best-insurance_policy_end.required_if'] = 'This Field is required';  
                $insurance_check['cpa'] = 1;
            }
        if(empty($validation) && $request->input('best-insurance') == 'yes')
        {
            return back()->with('error','Error, One Section Should be Required in Insurance Information When Insurance Status is YES')->withInput();
        }

        $this->validate($request,array_merge($validation,[     
            'store'=>'required',
            'best-owner_name'=>'required',
            'best-owner_pos'=>'required',
            'best-mobile_number'=>'required',
            'best-address'=>'required',
            'best-aadhar'=>'required_without_all:best-voter',
            'best-voter'=>'required_without_all:best-aadhar',
            'best-km'=>'required',
            'best-pan'=>'required',
            'best-make'=>'required',
            'best-model'=>'required',
            'best-variant'=>'required',
            'best-color'=>'required',
            'best-yom'=>'required',
            'best-sale_date'=>'required',
            'best-frame'=>'required',
            'best-register'=>'required',
            'best-hypo'=>'required',
            'best-hypo_bank'=>'required_if:best-hypo,yes',
            'best-hypo_noc'=>'required_if:best-hypo,yes',
            'best-warrenty'=>'required',
            'best-warrenty_end'=>'required_if:best-warrenty,yes',
            'best-insurance'=>'required',
            'best-key'=>'required',
            'best-purchase'=>'required|gt:0',
            'best-rc_status'=>'required',
        ]),array_merge($val_msg,[
            'store.required'  =>  'This Field is required',
            'best-sale_date.required'    =>  'This Field is required',
            'best-sale_date.date'    =>  'This Field is only date format',
            'best-owner_name'=>'required',
            'best-owner_pos.required'=>'This Field is required',
            'best-mobile_number.required'=>'This Field is required',
            'best-address.required'=>'This Field is required',
            'best-aadhar.required'=>'This Field is required',
            'best-voter.required'=>'This Field is required',
            'best-km.required'=>'This Field is required',
            'best-pan.required'=>'This Field is required',
            'best-make.required'=>'This Field is required',
            'best-model.required'=>'This Field is required',
            'best-variant.required'=>'This Field is required',
            'best-color.required'=>'This Field is required',
            'best-yom.required'=>'This Field is required',
            'best-sale_date.required'=>'This Field is required',
            'best-frame.required'=>'This Field is required',
            'best-register.required'=>'This Field is required',
            'best-hypo.required'=>'This Field is required',
            'best-hypo_bank.required_if'=>'This Field is required',
            'best-hypo_noc.required_if'=>'This Field is required',
            'best-warrenty.required'=>'This Field is required',
            // 'warrenty_start.required_if'=>'This Field is required',
            'best-warrenty_end.required_if'=>'This Field is required',
            'best-insurance.required'=>'This Field is required',
            'best-key.required'=>'This Field is required',
            'best-purchase.required'=>'This Field is required',
            'best-purchase.gt'=>'Purchase Price Should be grater than zero',
            'best-rc_status.required'=>'This Field is required',

        ]));
            // die;
        try{
            DB::beginTransaction();

            $data = [
                'tos'   =>  'best_deal',
                'name' => $request->input('best-owner_name'),
                'position' => $request->input('best-owner_pos'),
                'number'  => $request->input('best-mobile_number'),
                'address'    => $request->input('best-address'),
                'aadhar' => $request->input('best-aadhar'),
                'voter'  => $request->input('best-voter'),
                'km' => $request->input('best-km'),
                'pan'    => $request->input('best-pan'),
                'maker'   => $request->input('best-make'),
                'yom'    => $request->input('best-yom'),
                'dos'  => $request->input('best-sale_date'),
                'frame'  => $request->input('best-frame'),
                'register_no'   => $request->input('best-register'),
                'no_of_key'    => $request->input('best-key'),
                'value'   => $request->input('best-purchase'),
                'rc_status'  => $request->input('best-rc_status')
            ];
            // print_r($data);die;
            $hypo   = $request->input('best-hypo');
            if($hypo == 'yes')
            {
                $data = array_merge($data,[
                    'hypo_bank'  => $request->input('best-hypo_bank'),
                    'hypo_noc'   => $request->input('best-hypo_noc')
                ]);
            }
            $warrenty   = $request->input('best-warrenty');
            if($warrenty == 'yes')
            {
                $data = array_merge($data,[
                    'warrenty_end'   => $request->input('best-warrenty_end')
                ]);
            }
            $insurance  = $request->input('best-insurance');

            $model = $request->input('best-model');
            $variant   = $request->input('best-variant');
            $color = $request->input('best-color');
            $product_id = $request->input('best-product_id');
            if($product_id)
            {
                $data = array_merge($data,[
                    'product_id' => $request->input('best-product_id')
                ]);
            }
            else
            {
                $data = array_merge($data,[
                    'model' => $model,
                    'variant' => $variant,
                    'color' => $color
                ]);
            }
            // print_r($data);die;
        
            //update 
            //insert
            $insert = BestDealSale::insertGetId($data);   
            if(!$insert)
            {
                DB::rollback();
                return back()->with('error','Something Went Wrong')->withInput();
            }
            // approve request
            $approve_insert = CustomHelpers::best_deal_insert_approval($insert,$request->input('store'),'bestdeal',$insert);
            //  insurance insert
            $insurance  = $request->input('best-insurance');
            if($insurance == 'yes')
            {
                $od_cpa = [];
                if(isset($insurance_check['od']))
                {
                    $start = date_create($request->input('od-best-insurance_policy_start'));
                    $end_date = date('Y-m-d',strtotime('+1 days',strtotime($request->input('od-best-insurance_policy_end'))));
                    $end = date_create($end_date);
                    $diff = date_diff($end,$start);
                    $tenure = $diff->format("%Y");
                    $od_cpa = [
                        'bestdeal_id'   =>  $insert,
                        'policy_number'   => $request->input('od-best-insurance_policy'),
                        'insurance_co'  => $request->input('od-best-insurance_company'),
                        'insurance_type'    =>  'OD',
                        'insurance_date' => $start,
                        'policy_tenure' =>  $tenure
                    ];
                }
                if(isset($insurance_check['cpa']))
                {
                    $start = date_create($request->input('cpa-best-insurance_policy_start'));
                    $end_date = date('Y-m-d',strtotime('+1 days',strtotime($request->input('cpa-best-insurance_policy_end'))));
                    $end = date_create($end_date);
                    $diff = date_diff($end,$start);
                    $tenure = $diff->format("%Y");
                    $od_cpa = array_merge($od_cpa,[
                        'bestdeal_id'   =>  $insert,
                        'cpa_company'  => $request->input('cpa-best-insurance_company'),
                        'cpa_tenure' => $tenure,
                    ]);
                }
                if(isset($od_cpa['bestdeal_id']))
                {
                    $insert_od_cpa = Insurance::insertGetId($od_cpa);
                }
                if(isset($insurance_check['ta']))
                {
                    $start = date_create($request->input('ta-best-insurance_policy_start'));
                    $end_date = date('Y-m-d',strtotime('+1 days',strtotime($request->input('ta-best-insurance_policy_end'))));
                    $end = date_create($end_date);
                    $diff = date_diff($end,$start);
                    $tenure = $diff->format("%Y");
                    $tp = [
                        'bestdeal_id'   =>  $insert,
                        'policy_number'   => $request->input('ta-best-insurance_policy'),
                        'insurance_co'  => $request->input('ta-best-insurance_company'),
                        'insurance_type'    =>  'TP',
                        'insurance_date' => $start,
                        'policy_tenure' =>  $tenure
                    ];
                    $insert_tp = Insurance::insertGetId($tp);
                }
            }

        }
        catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return back()->with('error','Something Went Wrong. '.$ex->getMessage())->withInput();
        }
        DB::commit();
        return back()->with('success','Successfully Created. ');
    }

    public function best_deal_list()
    {
        $checklist = BestDealMaster::all();
        $data = [
            'checklist' =>  $checklist,
            'layout' => 'layouts.main'
        ];
        
        return view('admin.bestdeal.best_deal_list',$data);
    }
    public function best_deal_list_api(Request $request) {
       
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;

           
        $api_data = BestDealSale::leftjoin('product','product.id','best_deal_sale.product_id')
                    ->where('best_deal_sale.tos','best_deal')
                    // ->where('best_deal_sale.selling_price','<>',0)
                    // ->where('best_deal_sale.status','<>','Pending')
                    ->select('best_deal_sale.id as id',
                    DB::raw('IF(best_deal_sale.product_id = 0,best_deal_sale.model,product.model_name) as model'),        
                    DB::raw('IF(best_deal_sale.product_id = 0,best_deal_sale.variant,product.model_variant) as variant'),        
                    DB::raw('IF(best_deal_sale.product_id = 0,best_deal_sale.color,product.color_code) as color'),
                    'best_deal_sale.selling_price',
                    'best_deal_sale.dos',
                    'best_deal_sale.register_no',
                    'best_deal_sale.frame',
                    'best_deal_sale.address','best_deal_sale.position','best_deal_sale.number',
                    'best_deal_sale.name','best_deal_sale.aadhar','best_deal_sale.voter',
                    'best_deal_sale.km','best_deal_sale.pan','best_deal_sale.maker',
                    'best_deal_sale.yom','best_deal_sale.hypo_bank','best_deal_sale.hypo_noc',
                    'best_deal_sale.warrenty_end','best_deal_sale.insurance_policy_number',
                    'best_deal_sale.insurance_policy_start','best_deal_sale.insurance_policy_end',
                    'best_deal_sale.insurance_company','best_deal_sale.no_of_key',
                    'best_deal_sale.rc_status','best_deal_sale.value','best_deal_sale.profit',
                    'best_deal_sale.receive_amount','best_deal_sale.status','best_deal_sale.repaire_status',
                    'best_deal_sale.created_at',
                    'best_deal_sale.approve'
            );
            
            // DB::enableQueryLog();
            if(!empty($serach_value))
            {
               $api_data->where(function($query) use ($serach_value){
                        $query->where('variant','like',"%".$serach_value."%")
                        ->orwhere('model','like',"%".$serach_value."%")
                        ->orwhere('color','like',"%".$serach_value."%")
                        ->orwhere('value','like',"%".$serach_value."%")
                        // ->orwhere('selling_price','like',"%".$serach_value."%")
                        ->orwhere('dos','like',"%".$serach_value."%")
                        ->orwhere('register_no','like',"%".$serach_value."%")
                        ->orwhere('frame','like',"%".$serach_value."%")
                        ->orwhere('address','like',"%".$serach_value."%");
                        
                    });
               
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                    'model',
                    'variant',
                    'color',
                    'value',
                    // 'selling_price',
                    'dos',
                    'register_no',
                    'frame',
                    'address'
                ];
                
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('best_deal_sale.id','desc');   
        
        $count = $api_data->count();
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }
    
    public function best_dealUploadpic(Request $request){
        $ImageSize = Settings::where('name','image_size')->get()->first();
        $size = $ImageSize['value'];
         if(!isset($size)){
              return response()->json(['messages' => 'Image Size For Validation Master Not Found.'],422);
        }
        $best_deal_id   = $request->input('best_deal_id');

        $allowed_extension = array('jpeg,jpg,png');
        $img_valid = []; $req = [];
        $front_img = $request->input('pre_front_img'); 
        $back_img = $request->input('pre_back_img'); 
        $left_img = $request->input('pre_left_img'); 
        $right_img = $request->input('pre_right_img'); 
        if(empty($front_img)){
            array_push($req,'front');
        }
        if(empty($back_img)){
            array_push($req,'back');
        }
        if(empty($left_img)){
            array_push($req,'left');
        }
        if(empty($right_img)){
            array_push($req,'right');
        }
        if(empty($front_img)){
            $img_valid['front'] =   'max:'.$size.'|required_without_all:'.join(',',$req);
        }
        if(empty($back_img)){
            $img_valid['back'] =   'max:'.$size.'|required_without_all:'.join(',',$req);
        }
        if(empty($left_img)){
            $img_valid['left'] =   'max:'.$size.'|required_without_all:'.join(',',$req);
        }
        if(empty($right_img)){
            $img_valid['right'] =   'max:'.$size.'|required_without_all:'.join(',',$req);
        }
        $validator = Validator::make($request->all(),$img_valid);
        
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

            // image validation :- check image dublicate

            $files_name = [];

            $get_old_image_name = BestDealPhoto::where('best_deal_id',$best_deal_id)->pluck('image');
            if(isset($get_old_image_name[0])){
                $files_name = $get_old_image_name->toArray();
            }
            // return response()->json($files_name,422);

            if($request->hasFile('front')){
                $file = $request->file('front');
                $filenameWithExt=$file->getClientOriginalName();
                $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME); 
                $extension = $file->getClientOriginalExtension();    
                $fileNameToStore = $filename.'_'.$best_deal_id.'.'.$extension;     
                if(in_array($fileNameToStore,$files_name)){
                    return response()->json('Front Image Should be Unique Image.',422);
                }
                array_push($files_name,$fileNameToStore);
            }
            if($request->hasFile('back')){
                $file = $request->file('back');
                $filenameWithExt=$file->getClientOriginalName();
                $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME); 
                $extension = $file->getClientOriginalExtension();    
                $fileNameToStore = $filename.'_'.$best_deal_id.'.'.$extension;  
                if(in_array($fileNameToStore,$files_name)){
                    return response()->json('Back Image Should be Unique Image.',422);
                }
                array_push($files_name,$fileNameToStore);
            }
            if($request->hasFile('left')){
                $file = $request->file('left');
                $filenameWithExt=$file->getClientOriginalName();
                $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME); 
                $extension = $file->getClientOriginalExtension();    
                $fileNameToStore = $filename.'_'.$best_deal_id.'.'.$extension;   
                // return response()->json($filename,422);
                if(in_array($fileNameToStore,$files_name)){
                    return response()->json('Left Image Should be Unique Image.',422);
                }
                array_push($files_name,$fileNameToStore);
            }
            if($request->hasFile('right')){
                $file = $request->file('right');
                $filenameWithExt=$file->getClientOriginalName();
                $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME); 
                $extension = $file->getClientOriginalExtension();    
                $fileNameToStore = $filename.'_'.$best_deal_id.'.'.$extension;   
                if(in_array($fileNameToStore,$files_name)){
                    return response()->json('Right Image Should be Unique Image.',422);
                }
                array_push($files_name,$fileNameToStore);
            }
            // return response()->json($files_name,422);

            // valiudation end ----------------------
            // start saving--------------
            DB::beginTransaction();
            if($request->hasFile('front'))
            {
                    $file = $request->file('front');
                    $destinationPath = public_path().'/upload/bestdeal';
                    $filenameWithExt=$file->getClientOriginalName();
                    $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME); 
                    $extension = $file->getClientOriginalExtension();    
                    $fileNameToStore = $filename.'_'.$best_deal_id.'.'.$extension;   
                    $frontimg=$fileNameToStore;
                    $file->move($destinationPath,$fileNameToStore);
                    $insert = BestDealPhoto::insertGetId([
                        'best_deal_id' => $best_deal_id,
                        'image' => $frontimg,
                        'type'  => 'front'
                        ]);

            }
            if($request->hasFile('back')){
                    $file = $request->file('back');
                    $destinationPath = public_path().'/upload/bestdeal';
                    $filenameWithExt=$file->getClientOriginalName();
                    $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME); 
                    $extension = $file->getClientOriginalExtension();    
                    $fileNameToStore = $filename.'_'.$best_deal_id.'.'.$extension;   
                    $backimg=$fileNameToStore;
                    $file->move($destinationPath,$fileNameToStore);
                    $insert = BestDealPhoto::insertGetId([
                        'best_deal_id' => $best_deal_id,
                        'image' => $backimg,
                        'type'  => 'back'
                        ]);
            }if($request->hasFile('left')){
 
                    $file = $request->file('left');
                    $destinationPath = public_path().'/upload/bestdeal';
                    $filenameWithExt=$file->getClientOriginalName();
                    $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME); 
                    $extension = $file->getClientOriginalExtension();    
                    $fileNameToStore = $filename.'_'.$best_deal_id.'.'.$extension;   
                    $leftimg=$fileNameToStore;
                    $file->move($destinationPath,$fileNameToStore);
                    $insert = BestDealPhoto::insertGetId([
                        'best_deal_id' => $best_deal_id,
                        'image' => $leftimg,
                        'type'  => 'left'
                        ]);
            
            }if($request->hasFile('right')){

                    $file = $request->file('right');
                    $destinationPath = public_path().'/upload/bestdeal';
                    $filenameWithExt=$file->getClientOriginalName();
                    $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME); 
                    $extension = $file->getClientOriginalExtension();    
                    $fileNameToStore = $filename.'_'.$best_deal_id.'.'.$extension;   
                    $rightimg=$fileNameToStore;
                    $file->move($destinationPath,$fileNameToStore);
                    $insert = BestDealPhoto::insertGetId([
                        'best_deal_id' => $best_deal_id,
                        'image' => $rightimg,
                        'type'  => 'right'
                        ]);
            }
            if(empty($insert)) {
                       DB::rollback();
                       return response()->json('Some Error Occurred.',422);
            }else{
                    DB::commit();
                    return 'Image Uploaded Successfully.';
            }

        }
        catch(\Illuminate\Database\QueryException $ex) {
            DB::commit();
            return response()->json('some error occurred'.$ex->getMessage(),422);
            // return redirect('/admin/sale/create/best/deal/list')->with('error','some error occurred'.$ex->getMessage());
        }
    }    

    public function uploadpic_data(Request $request)
    {
        $best_deal_id = $request->input('best_deal_id');
        $servicechaege = BestDealPhoto::where('best_deal_id','=',$best_deal_id)->get();
         return response()->json($servicechaege);

    }

    public function uploadpic_delete(Request $request)
    {   
        $picId = $request->input('id');
        $getcheck = BestDealPhoto::where('id',$picId)->get()->first();

        if($getcheck)
        {
            $getimage=$getcheck->image;
             $image_path = public_path().'/upload/bestdeal/'.$getimage;
             $picdate = BestDealPhoto::where('id',$picId)->delete();
            if(empty($picdate)) {
                    return 'error';
                }else{
                    
                    if(\File::exists($image_path)) {
                      \File::delete($image_path);
                     }
                    return 'success';
                }
        }else{

            return 'No Data';
        }

    }


    // best deal inspection

    public function getInspection(Request $request)
    {
        $best_deal_id = $request->input('best_deal_id');
        // echo $best_deal_id;die;
        // DB::enableQueryLog();
        $data = BestDealMaster::select('best_deal_master.id as master_id',
                            'best_deal_master.name as master_name',
                            DB::raw('(select id from best_deal_checklist where best_deal_id = '.$best_deal_id.' and best_deal_master_id = best_deal_master.id) as bd_check_id')
                        )->get();
        $bestdeal = BestDealSale::where('id',$best_deal_id)->select('repaire_status')->first();
        // print_r(DB::getQueryLog());die;
        $alldata  = [
            'data'  =>  $data,
            'bestdeal'  =>  $bestdeal
        ];
        return response()->json($alldata);
    }
    public function inspectionDB(Request $request)
    {
        // print_r($request->input('ins_check'));die;
        $bd_master_id = $request->input('ins_check');
        if(!isset($bd_master_id[0]))
        {
            $bd_master_id = array();
        }
        $bd_id = $request->input('ins_bd_id');
        $repaire_status =  $request->input('repaire_status');
        try{
            DB::beginTransaction();
            $check_bd_id = BestDealSale::where('id',$bd_id)
                                        ->first();
            if(!isset($check_bd_id->id))
            {
                return "BestDeal isn't exists for this";
            }
            if($check_bd_id->status == 'sale')
            {
                return 'This BestDeal Already Sale';
            }
            $old_checklist_entry = BestDealCheckList::where('best_deal_id',$bd_id)
                                        ->select(DB::raw('group_concat(best_deal_master_id SEPARATOR ",") as old_master_id'))
                                        ->groupBy('best_deal_id')
                                        ->first();

            // print_r($old_checklist_entry);die;
            if(isset($old_checklist_entry->old_master_id))
            {
                $old_master_id = explode(',',$old_checklist_entry->old_master_id);
                $pre_exist = [];
                $new = [];
                // print_r($old_master_id);die;
                // update
                foreach($bd_master_id as $k => $v)
                {
                    if(!in_array($v,$old_master_id))
                    {
                        $new[] = $v;
                    }
                    elseif(in_array($v,$old_master_id))
                    {
                        $pre_exist[]    =   $v;
                        $ind = array_search($v,$old_master_id);
                        array_splice($old_master_id,$ind,1);
                    }
                }
                // new insert
                $insert_data = array();
                foreach($new as $key => $val)
                {
                    $insert_data[] = [
                        'best_deal_id'  =>  $bd_id,
                        'best_deal_master_id'   =>  $val,
                        'created_by'    =>  Auth::id()
                    ];
                }
                $checklist = BestDealCheckList::insert($insert_data);
                // if remaining which is delete
                if(isset($old_master_id[0]))
                {
                    $delete = BestDealCheckList::where('best_deal_id',$bd_id)
                                ->whereIn('best_deal_master_id',$old_master_id)
                                ->delete();
                }
            }
            else{
                // new insert
                $insert_data = array();
                foreach($bd_master_id as $key => $val)
                {
                    $insert_data[] = [
                        'best_deal_id'  =>  $bd_id,
                        'best_deal_master_id'   =>  $val,
                        'created_by'    =>  Auth::id()
                    ];
                }
                $checklist = BestDealCheckList::insert($insert_data);
            }

            // update repaire_status in best deal sale
            $update = BestDealSale::where('id',$bd_id)
                                    ->update(['repaire_status' => $repaire_status]);
            if($repaire_status == 'allok')
            {
                $sp = CustomHelpers::calculate_selling($check_bd_id,0);
   
                $update_best_deal = BestDealSale::where('id',$check_bd_id->id)
                                               ->update([
                                                   'selling_price' => $sp,
                                                   'status' =>  'Ok'
                                               ]);
            }

        }
        catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return 'Something '.$ex->getMessage();
        }
        DB::commit();
        return 'success';
    }

    // best deal inventory
    public function best_deal_inventory()
    {
        $checklist = BestDealMaster::all();
        $data = [
            'checklist' =>  $checklist,
            'layout' => 'layouts.main'
        ];
        
        return view('admin.bestdeal.inventory_list',$data);
    }
    public function best_deal_inventory_api(Request $request) {
       
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;

           
            $api_data = BestDealSale::leftjoin('product','product.id','best_deal_sale.product_id')
                                        ->where('best_deal_sale.tos','best_deal')
                                        ->where('best_deal_sale.selling_price','<>',0)
                                        ->where('best_deal_sale.status','<>','Pending')
                                        ->select('best_deal_sale.id as id',
                                        DB::raw('IF(best_deal_sale.product_id = 0,best_deal_sale.model,product.model_name) as model'),        
                                        DB::raw('IF(best_deal_sale.product_id = 0,best_deal_sale.variant,product.model_variant) as variant'),        
                                        DB::raw('IF(best_deal_sale.product_id = 0,best_deal_sale.color,product.color_code) as color'),
                                        'best_deal_sale.selling_price',
                                        'best_deal_sale.dos',
                                        'best_deal_sale.register_no',
                                        'best_deal_sale.frame',
                                        'best_deal_sale.address','best_deal_sale.position','best_deal_sale.number',
                                        'best_deal_sale.name','best_deal_sale.aadhar','best_deal_sale.voter',
                                        'best_deal_sale.km','best_deal_sale.pan','best_deal_sale.maker',
                                        'best_deal_sale.yom','best_deal_sale.no_of_key',
                                        'best_deal_sale.rc_status','best_deal_sale.value','best_deal_sale.profit',
                                        'best_deal_sale.receive_amount','best_deal_sale.status','best_deal_sale.repaire_status',
                                        'best_deal_sale.created_at'
            );
                                    
            
            // DB::enableQueryLog();
            if(!empty($serach_value))
            {
               $api_data->where(function($query) use ($serach_value){
                        $query->where('variant','like',"%".$serach_value."%")
                        ->orwhere('model','like',"%".$serach_value."%")
                        ->orwhere('color','like',"%".$serach_value."%")
                        ->orwhere('selling_price','like',"%".$serach_value."%")
                        ->orwhere('dos','like',"%".$serach_value."%")
                        ->orwhere('register_no','like',"%".$serach_value."%")
                        ->orwhere('frame','like',"%".$serach_value."%")
                        ->orwhere('address','like',"%".$serach_value."%");
                        
                    });
               
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                    'model',
                    'variant',
                    'color',
                    'selling_price',
                    'dos',
                    'register_no',
                    'frame',
                    'address'
                ];
                
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('best_deal_sale.id','asc');   
        
        $count = $api_data->count();
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

    // best deal inventory buy
    public function best_deal_inventory_buy(Request $request){
      
        $bestdeal_id = $request->input('bestdeal');
        $check = BestDealSale::where('best_deal_sale.id',$bestdeal_id)->where('best_deal_sale.tos','best_deal')
                                ->where('best_deal_sale.sale_id',0)
                                ->where('best_deal_sale.selling_price','>',0)->where('best_deal_sale.status','Ok')
                                ->leftjoin('product','product.id','best_deal_sale.product_id')
                                ->select('best_deal_sale.id',
                                        DB::raw('IF(best_deal_sale.product_id = 0,best_deal_sale.model,product.model_name) as model'),        
                                        DB::raw('IF(best_deal_sale.product_id = 0,best_deal_sale.variant,product.model_variant) as variant'),        
                                        DB::raw('IF(best_deal_sale.product_id = 0,best_deal_sale.color,product.color_code) as color'),
                                        'best_deal_sale.maker',        
                                        'best_deal_sale.product_id',        
                                        'best_deal_sale.selling_price',      
                                        'best_deal_sale.insurance_policy_start',      
                                        'best_deal_sale.insurance_policy_end'      
                                )
                                ->first();
        // print_r($check);die;
        if(!isset($check->id))
        {
            return back()->with('error','Error, Best Deal Not Found !!')->withInput();
        }
        $sr_no = Sale::select(DB::raw('IFNULL(count(id),1) as max_id'))->first();
        
        $customer = Customers::all();
        // $product = ProductModel::select('id','basic_price',
        //                 DB::raw('concat(model_name,"-",model_variant,"-",color_code) as prodName'))
        //                 ->where('type','product')->where('isforsale',1)->orderBy('model_name','ASC')->get();
        $state = State::all();
        //  print_r(Auth::user()->store_id);die;
        $scheme = Scheme::all();
        
        $store = Store::whereIn('id',CustomHelpers::user_store())
                    ->select('id',DB::raw('concat(store.name,"-",store.store_type) as name'))->get();
        
        // $model_name = ProductModel::where('type','product')->where('isforsale',1)->orderBy('model_name','ASC')->groupBy('model_name')->get(['model_name']);
        // print_r($model_name->toArray());die;
        // $company_name = DB::table('master')->where('type','company_name')->select('key','value')
        //                     ->orderBy('order_by','ASC')->get();
        $company_name = FinanceCompany::orderBy('id','ASC')->get();

        $sale_user = Users::where('role','SaleExecutive')->get(['id','name']);
        $insur_company = InsuranceCompany::orderBy('id')->get();
        $cpa_company = InsuranceCompany::where('cpa',1)
                        ->orderBy('id')->get();
        $maker = Master::where('type','prod_maker')->orderBy('order_by')->get();
        // $maker = array('Honda','Bajaj','Hero','TVS');

        $data = array(
            'layout' => 'layouts.main',
            'csd'   =>  CustomHelpers::csd_amount(),
            'insur_company'  =>  $insur_company,
            'sr_no' =>  'Sale/'.($sr_no['max_id']+1),
            'bestdeal'  =>  $check,
            // 'model_name'    =>  ($model_name->toArray())? $model_name->toArray() : array(),
            'customer' => ($customer->toArray()) ? $customer->toArray() : array() ,
            // 'product' => ($product->toArray()) ? $product->toArray() : array() ,
            'state' =>  ($state->toArray()) ? $state->toArray()  :  array() ,
            'scheme'    =>  $scheme,
            'store'    =>  $store->toArray(),
            'sale_user' =>  ($sale_user->toArray()) ? $sale_user->toArray() : array() ,
            'company_name' =>  $company_name,
            'maker' =>  $maker,
            'cpa_company'   =>  $cpa_company,
            'bestdeal_id'   =>  $bestdeal_id

        );
        return view('admin.bestdeal.inventory_buy',$data);
    }
    public function best_deal_inventory_buyDB(Request $request)
    {
        DB::beginTransaction();
        $bestdeal_id = $request->input('bestdeal');
        $csd_am = CustomHelpers::csd_amount();
        // best deal validation
        
        // validate Financier Mobile Number will not be equal Customer Mobile Number
        if($request->input('customer_pay_type') == 'finance')
        {
            $find_mobile = DB::table('master')->where('value',$request->input('finance_name'))->select('details')->first();
            if(isset($find_mobile->details))
            {
                $details = $find_mobile->details;
                $mob = json_decode($details,true);
                if(in_array($request->input('mobile'),$mob['mobile']))
                {
                    return back()->with('error','Error, Financier Mobile Number and Customer Mobile Number should not be Same.')->withInput();
                }
            }
        }
        
        // $total_discount = 0;
        // $total_amount = 0;
        $ExShowroomPrice = round($request->input('ex_showroom_price'));
        //accessories
        $validateZero = [];
        $validateZeroMsg = [];
        
        //discount
        // $exch_best_val = ($request->input('tos') != 'new') ?floatval($request->input('exchange_value')) : 0.0 ;
        $do = ($request->input('customer_pay_type') == 'finance') ?floatval($request->input('do')) : 0.0 ;
        $discount = floatval($request->input('discount_amount')) ;
        $total_discount = $do+$discount;
        // echo "<br>".$total_discount;
        //total selected amount calculate
        if($request->input('csd_check') && $request->input('sale_type') == 'corporate')
        {
            $csd = floatval($ExShowroomPrice * $csd_am/100);      // in csd amount
            $total_discount = round($total_discount + $csd);
        }

        $total_amount = $ExShowroomPrice+(($request->input('s_insurance') == 'zero_dep') ? floatval($request->input('zero_dep_cost')) : 0.0 )
                            +(($request->input('s_insurance') == 'comprehensive') ? floatval($request->input('comprehensive_cost')) : 0.0 )
                            +(($request->input('s_insurance') == 'ltp_5_yr') ? floatval($request->input('ltp_5_yr_cost')) : 0)
                            +(($request->input('s_insurance') == 'ltp_zero_dep') ? floatval($request->input('ltp_zero_dep_cost')) : 0.0 )
                            +((!empty($request->input('cpa_cover'))) ? floatval($request->input('cpa_cover_cost')) : 0.0 )
                            +(($request->input('customer_pay_type') != 'cash') ? floatval($request->input('hypo_cost')) : 0.0 )
                            +((!empty($request->input('amc'))) ? floatval($request->input('amc_cost')) : 0.0 )
                            +((!empty($request->input('ew'))) ? floatval($request->input('ew_cost')) : 0.0 )
                            +((!empty($request->input('hjc'))) ? floatval($request->input('hjc_cost')) : 0.0 );
                            
        // add rto transfer charges
        $total_amount = floatval($request->input('handling_charge'))+$total_amount;
        $hand_valid = 'gt:0';
        if($request->input('self_assited') == 'self'){
            $hand_valid = 'gte:0';
        }
        $insur_valid = '|gt:0';
        if($request->input('self_assited_ins') == 'self'){
            $insur_valid = '|gte:0';
        }

        if($request->input('pollution_cert') == 'yes'){
            $total_amount = $total_amount+$request->input('pollution_cert_cost');
        }

        $this->validate($request,array_merge($validateZero,[     
            // 'sale_no'=>'required',
            'sale_date'=>'required|date',
            'prod_name'=>'required',
            'store_name'=>'required',
            'sale_type'=>'required',
            'care_of'=>'required_if:sale_type,corporate',
            'gst'=>'required_if:sale_type,corporate',
            'select_customer'=>'required',
            'enter_customer_name'=>'required_unless:select_customer,exist_customer',
            'select_customer_name'=>'required_if:select_customer,exist_customer',
            'pre_booking'=>'required_if:select_customer,booking',
            'relation_type'=>'required',
            'relation'=>'required',
            'aadhar'=>'required_without_all:voter',
            'voter'=>'required_without_all:aadhar',
            'address'=>'required',
            'state'=>'required',
            'city'=>'required',
            'pin'=>'required',
            // 'email'=>'required',
            'mobile'=>'required',
            'sale_executive'=>'required',
            'customer_pay_type'=>'required',
            'finance_name'=>'required_if:customer_pay_type,finance',
            'finance_company_name'=>'required_if:customer_pay_type,finance',
            'loan_amount'=>'required_if:customer_pay_type,finance'.(($request->input('customer_pay_type') == 'finance')? '|gt:0' : '' ),
            'do'=>'required_if:customer_pay_type,finance',
            'dp'=>'required_if:customer_pay_type,finance'.(($request->input('customer_pay_type') == 'finance')? '|gt:0' : '' ),
            // 'los'=>'required_if:customer_pay_type,finance',
            'roi'=>'required_if:customer_pay_type,finance',
            'payout'=>'required_if:customer_pay_type,finance',
            'pf'=>'required_if:customer_pay_type,finance',
            'emi'=>'required_if:customer_pay_type,finance'.(($request->input('customer_pay_type') == 'finance')? '|gt:0' : '' ),
            'monthAddCharge'=>'required_if:customer_pay_type,finance',
            'sd'=>'required_if:customer_pay_type,finance',
            'self_finance_company'=>'required_if:customer_pay_type,self_finance',
            'self_finance_amount'=>'required_if:customer_pay_type,self_finance'.(($request->input('customer_pay_type') == 'self_finance')? '|gt:0' : '' ),
            // 'self_finance_bank'=>'required_if:customer_pay_type,self_finance',
            'ex_showroom_price'=>'required|gt:0',
            'self_assited'  =>  'required',
            'handling_charge'   =>  'required|'.$hand_valid,
            'self_assited_ins'  =>  'required',
            'insur_c'   =>  'required',
            'zero_dep_cost'=>'required_if:s_insurance,zero_dep'.(($request->input('s_insurance') == 'zero_dep')? $insur_valid : '' ).'',
            'comprehensive_cost'=>'required_if:s_insurance,comprehensive'.(($request->input('s_insurance') == 'comprehensive')? $insur_valid : '' ).'',
            'ltp_5_yr_cost'=>'required_if:s_insurance,ltp_5_yr'.(($request->input('s_insurance') == 'ltp_5_yr')? $insur_valid : '' ).'',
            'ltp_zero_dep_cost'=>'required_if:s_insurance,ltp_zero_dep'.(($request->input('s_insurance') == 'ltp_zero_dep')? $insur_valid : '' ).'',
            'cpa_company'=>'required_if:cpa_cover,cpa_cover',
            'cpa_duration'=>'required_if:cpa_cover,cpa_cover'.(!empty($request->input('cpa_cover'))? '|integer' : '' ).'',
            'cpa_cover_cost'=>'required_if:cpa_cover,cpa_cover'.(!empty($request->input('cpa_cover'))? '|gt:0' : '' ).'',
            'hypo_cost'=>'required_unless:customer_pay_type,cash'.(($request->input('customer_pay_type') != 'cash')? '|gt:0' : '' ).'',
            'pollution_cert_cost'=>'required_if:pollution_cert,yes'.(!empty($request->input('pollution_cert'))? '|gt:0' : '' ).'',
            'amc_cost'=>'required_if:amc,amc'.(!empty($request->input('amc'))? '|gt:0' : '' ).'',
            'ew_duration'=>'required_if:ew,ew'.(!empty($request->input('ew'))? '|gt:0' : '' ).'',
            'ew_cost'=>'required_if:ew,ew'.(!empty($request->input('ew'))? '|gt:0' : '' ).'',
            'hjc_cost'=>'required_if:hjc,hjc'.(!empty($request->input('hjc'))? '|gt:0' : '' ).'',
            'discount'=>'required',
            'discount_amount'=>'required',
            'scheme'=>'required_if:discount,scheme',
            'scheme_remark'=>'required_if:discount,scheme',
            'total_calculation'=>'required|in:'.$total_amount,
            'balance'   =>  'required|lte:'.($total_amount-$total_discount)
        ]),array_merge($validateZeroMsg,[
            // 'sale_no.required'  =>  'This Field is required',
            'sale_date.required'    =>  'This Field is required',
            'sale_date.date'    =>  'This Field is only date format',
            'prod_name.required'    => 'This Field is required' ,
            'store_name.required'   =>  'This Field is required',
            'sale_type.required'   =>  'This Field is required',
            'care_of.required_if'=>'This Field is required',
            'gst.required_if'=>'This Field is required',
            'select_customer.required'  =>  'This Field is required',
            'enter_customer_name.required_unless'   =>  'This Field is required',
            'select_customer_name.required_if'  =>  'This Field is required',
            'pre_booking.required_if'   =>  'This Field is required',
            'relation_type.required' =>'This Field is required',
            'relation.required' =>  'This Field is required',
            'aadhar.required_without_all'=> 'This Field is required, When You Not Fill Voter Number.',
            'voter.required_without_all'=> 'This Field is required, When You Not Fill Aadhar Number.',
            'address.required'   =>  'This Field is required',
            'state.required'    =>  'This Field is required',
            'city.required' =>  'This Field is required',
            'pin.required' =>  'This Field is required',
            // 'email.required'    =>  'This Field is required',
            'mobile.required'   =>  'This Field is required',
            'sale_executive.required' =>  'This Field is required'   ,
            'customer_pay_type.required'    =>  'This Field is required',
            'finance_name.required_if' =>  'This Field is required',
            'finance_company_name.required_if'  =>  'This Field is required',
            'loan_amount.required_if'  =>  'This Field is required',
            'loan_amount.gt'    =>  'This Field must be greater than 0.',
            'dp.gt'    =>  'This Field must be greater than 0.',
            'emi.gt'    =>  'This Field must be greater than 0.',
            'do.required_if'    =>  'This Field is required',
            'dp.required_if' =>  'This Field is required',
            // 'los.required_if'   =>  'This Field is required',
            'roi.required_if'  =>  'This Field is required',
            'payout.required_if'    =>  'This Field is required',
            'pf.required_if'   =>  'This Field is required',
            'emi.required_if'   =>  'This Field is required',
            'monthAddCharge.required_if'    =>  'This Field is required',    
            'sd.required_if'   =>  'This Field is required',
            'self_finance_company.required_if'  =>  'This Field is required',
            'self_finance_amount.required_if'    =>  'This Field is required',
            'self_finance_amount.gt'    =>  'This Field must be greater than 0.',
            // 'self_finance_bank.required_if' =>  'This Field is required',
            'ex_showroom_price.required' =>  'This Field is required',
            'ex_showroom_price.gt' =>  'Please Enter Correction Selling Price',
            'self_assited.required' =>  'This Field is required',
            'handling_charge.required' =>  'This Field is required',
            'handling_charge.gt' =>  'This Field must be greater than 0',
            'handling_charge.gte' =>  'This Field must be greater than equal 0',
            'self_assited_ins.required' =>  'This Field is required',
            'insur_c.required'    =>  'Insurance Company Field is required',
            'zero_dep_cost.required_if'    =>  'This Field is required',
            'zero_dep_cost.gt'    =>  'This Field must be greater than 0',
            'zero_dep_cost.gte'    =>  'This Field must be greater than equal 0',
            'comprehensive_cost.required_if'    =>  'This Field is required',
            'comprehensive_cost.gt'    =>  'This Field must be greater than 0',
            'comprehensive_cost.gte'    =>  'This Field must be greater than equal 0',
            'ltp_5_yr_cost.required_if'    =>  'This Field is required',
            'ltp_5_yr_cost.gt'    =>  'This Field must be greater than 0',
            'ltp_5_yr_cost.gte'    =>  'This Field must be greater than equal 0',
            'ltp_zero_dep_cost.required_if' =>  'This Field is required',
            'ltp_zero_dep_cost.gt'    =>  'This Field must be greater than 0',
            'ltp_zero_dep_cost.gte'    =>  'This Field must be greater than equal 0',
            'cpa_company.required_if'    =>  'This Field is required',
            'cpa_duration.required_if'  =>  'This Field is required',
            'cpa_cover_cost.required_if' =>  'This Field is required',
            'cpa_cover_cost.gt'    =>  'This Field must be greater than 0',
            'hypo_cost.required_unless' =>  'This Field is required',
            'pollution_cert_cost.required_if'   =>  'This Field is required',
            'pollution_cert_cost.gt'   =>  'This Field must be greater than :0',
            'amc_cost.required_if'   =>  'This Field is required',
            'amc_cost.gt'    =>  'This Field must be greater than :0',
            'ew_duration.required_if'   =>  'This Field is required',
            'ew_duration.gt'    =>  ' EW Duration must be greater than 0',
            'ew_cost.required_if'   =>  'This Field is required',
            'ew_cost.gt'    =>  'This Field must be greater than 0',
            'hjc_cost.required_if'   =>  'This Field is required',
            'hjc_cost.gt'    =>  'This Field must be greater than 0',
            'discount.required' =>  'This Field is required',
            'discount_amount.required'   =>  'This Field is required',
            'scheme.required_if'   =>  'This Field is required',
            'scheme_remark.required_if'    =>  'This Field is required',
            'total_calculation.required' =>  'This Field is required',
            'total_calculation.in' =>  'total amount will be equal to whole amount which you have entered',
            'balance'   =>  'This Feild is required',
            'balance.lte'   =>  'this price must be less to total amount'

        ]));
        try{
            if($request->input('select_customer') == 'booking')
            {
                $val = $this->checkbooking_number($request->input('booking_number'));
                if($val == 'used')
                {
                    return back()->with('error','this booking number already used, Please Enter another Booking Number')->withInput();
                }
                elseif($val == 'no-found')
                {
                    return back()->with('error','this booking number not found, Please Enter another Booking Number')->withInput();
                }
            }//die;
            // DB::enableQueryLog(); 
            // $booking = $request->input('booking');
            $booking_id = $request->input('booking_id');
            $booking_number = $request->input('booking_number');
            // $sale_no = $request->input('sale_no');
            $sr_no = Sale::select(DB::raw('IFNULL(count(id),1) as max_id'))->first();
            $max = $sr_no->max_id+1;
            $sale_no = 'Sale-'.$max;
            // echo $sale_no;die;
            $sale_date = $request->input('sale_date');
            $customerFlag = $request->input('select_customer');
            $booking_cust_id = '';
            $customer_name = '';
            if($request->input('aadhar'))
            {
                $customerData = Customers::where('aadhar_id',$request->input('aadhar'))->first(); 
                $booking_cust_id = $customerData['id'];
            }
            if(empty($booking_cust_id))
            {
                if($request->input('voter'))
                {
                    $customerData = Customers::where('voter_id',$request->input('voter'))->first(); 
                    $booking_cust_id = $customerData['id'];
                }
            }
            if($request->input('select_customer') == 'new_customer' || ($request->input('select_customer') == 'booking') && empty($booking_cust_id) )
            {
                $aadhar = $request->input('aadhar');
                $voter = $request->input('voter');
                $check = CustomHelpers::checkCustomer(null,$aadhar,$voter);
                if(!empty($check))
                {
                    return back()->with('error','Error, Cutomer Already Exist for this Aadhar Number OR Voter Number')->withInput();
                }
                $customer_name = $request->input('enter_customer_name');
                $nc = [
                    'name'  =>  $request->input('enter_customer_name'),
                    'aadhar_id'  =>  $request->input('aadhar'),
                    'voter_id'  =>  $request->input('voter'),
                    'relation_type' =>  $request->input('relation_type'),
                    'relation' =>  $request->input('relation'),
                    'email_id' =>  $request->input('email'),
                    'mobile' =>  $request->input('mobile'),
                    'dob'   =>  $request->input('dob'),
                    'country' =>  105,
                    'state' =>  $request->input('state'),
                    'city' =>  $request->input('city'),
                    'location' =>  $request->input('location'),
                    'address' =>  $request->input('address'),
                    'pin_code' =>  $request->input('pin')
                ];
                if($request->input('sale_type') == 'corporate')
                {
                    $nc = array_merge($nc,[
                            'care_of'=>$request->input('care_of'),
                            'gst'=>$request->input('gst')
                        ]);
                }
                

                $customerId = Customers::insertGetId($nc);
            }
            elseif($request->input('select_customer') == 'exist_customer' || ($request->input('select_customer') == 'booking') && !empty($booking_cust_id))
            {
                if($booking_cust_id)
                {
                    $customerId =   $booking_cust_id;
                }
                else{
                    $customerId = $request->input('select_customer_name');
                }
                $saleCust = Sale::where('customer_id',$customerId)->first();
                $cust = Customers::where('id',$customerId)->first();
                $customer_name = $cust->name;
                
                $data  = [
                    // 'name'=>$request->input('name'),
                    'mobile'=>$request->input('mobile'),
                    'email_id'=>$request->input('email'),
                    'relation_type'=>$request->input('relation_type'),
                    'relation'=>$request->input('relation'),
                    'dob'   =>  $request->input('dob'),
                    'country'=>$request->input('country'),
                    'state'=>$request->input('state'),
                    'city'=>$request->input('city'),
                    'location' =>  $request->input('location'),
                    'address'=>$request->input('address'),
                    'pin_code'=>$request->input('pin')
                ];
                if($request->input('sale_type') == 'corporate')
                {
                    $data = array_merge($data,[
                            'care_of'=>$request->input('care_of'),
                            'gst'=>$request->input('gst')
                        ]);
                }
                
                $checkCustomer = CustomHelpers::checkCustomer($customerId,$request->input('aadhar'),$request->input('voter'));
                // print_r($checkCustomer);die;
                if(empty($saleCust))
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
                        $checkAadhar = CustomHelpers::checkCustomer($customerId,$request->input('aadhar'),null);
                        if(!empty($checkAadhar))
                        {
                            return back()->with('error','Error, Cutomer Already Exist for this Aadhar Number.')->withInput();
                        }
                        $data = array_merge($data,['aadhar_id'=> $request->input('aadhar')]);
                    }
                    if(empty($cust->voter_id))
                    {
                        $checkVoter = CustomHelpers::checkCustomer($customerId,$request->input('aadhar'),$request->input('voter'));
                        if(!empty($checkVoter))
                        {
                            return back()->with('error','Error, Cutomer Already Exist for this Voter Number.')->withInput();
                        }
                        $data = array_merge($data,['voter_id'=> $request->input('voter')]);
                    }
                }
            
                $customer = Customers::where('id',$customerId)->update($data);
                // print_r($data);die;
                if($customer)
                {
                    $customer_details = DB::table('customer_details')->insertGetId([
                        'customer_id'   =>  $customerId,
                        'relation_type' =>  $cust->relation_type,
                        'relation' =>  $cust->relation,
                        'email_id'=>$cust->email,
                        'mobile'=>$cust->mobile,
                        'dob'   =>  $cust->dob,
                        'country'=>$cust->country,
                        'state'=>$cust->state,
                        'city'=>$cust->city,
                        'location'=>$cust->location,
                        'address'=>$cust->address,
                        'pin_code'=>$cust->pin_code,
                    ]);
                }
                // if($customer==NULL) 
                // {
                //     DB::rollback();
                //     return back()->with('error','Some Unexpected Error occurred.')->withInput();
                // }
                // die;
            }

            $balance = round($request->input('balance'));
            if($request->input('discount') == 'normal' && $request->input('discount_amount') >= 1000)
            {
                $balance = round($balance+$request->input('discount_amount'));
            }
            //   print_r($request->input());die;
            $saleInsert = [
                        'sale_executive'    =>  $request->input('sale_executive'),
                        'product_id'  =>  $request->input('prod_name'),
                        'sale_no'    =>  $sale_no,
                        'sale_type'    =>  $request->input('sale_type'),
                        'sale_date'  =>  $sale_date,
                        'store_id'  =>  $request->input('store_name'),
                        'ref_name'  =>  $request->input('reference'),
                        'ref_relation'  =>  $request->input('ref_relation'),
                        'ref_mobile'  =>  $request->input('ref_mobile'),
                        'customer_id'  =>  $customerId,
                        'customer_pay_type'  =>  $request->input('customer_pay_type'),
                        'tos'  =>  'bestdeal',
                        'ex_showroom_price'  =>  $request->input('ex_showroom_price'),
                        'hypo'  =>  ($request->input('customer_pay_type') != 'cash') ? $request->input('hypo_cost') : null,
                        'total_amount'  =>  round($request->input('total_calculation')),
                        'balance'  =>  $balance
                                
            ];
        
            $saleInsertId = Sale::insertGetId($saleInsert);
            //type of sale

            $sale_rto_type = 'normal';
            if($request->input('self_assited') == 'self'){
                $sale_rto_type = 'self';
            }
            // insert sale rto transfer charges
            $sale_rto_insert = SaleRto::insertGetId([
                'sale_id'   =>  $saleInsertId,
                'handling_charge'   =>  $request->input('handling_charge'),
                'rto'   =>  $sale_rto_type
            ]);

            // update in bestdealsale table 
            $update_bd = BestDealSale::where('id',$bestdeal_id)->update([
                                            'sale_id'=>$saleInsertId,
                                            'status'    =>  'Sale'
                                            ]); 
            if($update_bd == NULL) 
            {
                DB::rollback();
                return back()->with('error','Some Unexpected Error occurred.')->withInput();
            }

            // discount insert
            $discount_data = [
                'type'  =>  'sale',
                'type_id'   =>  $saleInsertId,
                'discount_type'  =>  $request->input('discount'),
                'scheme_id'  =>  ($request->input('discount') == 'scheme') ? $request->input('scheme') : 0,
                'remark'  =>  ($request->input('discount') == 'scheme') ? $request->input('scheme_remark') : null,
                'amount'  =>  $request->input('discount_amount')
            ];
            if($request->input('discount') == 'normal' && $request->input('discount_amount') >= 1000)
            {
                $discount_data = array_merge($discount_data,['status' => 'pending']);
            }
            $discount  =  SaleDiscount::insertGetId($discount_data);
            if($request->input('sale_type') == 'corporate')
            {
                $discount  =  SaleDiscount::insertGetId([
                    'type'  =>  'sale',
                    'type_id'   =>  $saleInsertId,
                    'discount_type' =>  'csd',
                    'amount'    =>  $csd
                ]);
            }

            $insert_approval = 0;
             // if normal discount is more than 0 then required to approval so, insert in approval request 
            // but check it when discount type is NORMAL
            if($request->input('discount') == 'normal' && $request->input('discount_amount') >= 1000)
            {
                $ins_approval = CustomHelpers::sale_disocunt_insert_approval($discount,$request->input('store_name'),$saleInsertId);
                $insert_approval = 1;
            }

            // insert in insurance_data
            $insurance_data = [
                'store_id'  =>  $saleInsert['store_id'],
                'sale_id'   =>  $saleInsertId,
                'customer_id'   =>  $saleInsert['customer_id'],
                'type'  =>  'new'
            ];
            $old_insur_data = InsuranceData::where('customer_id',$saleInsert['customer_id'])->first();
            $insur_data_id = 0;
            if(isset($old_insur_data->id)){
                $insur_data_id = $old_insur_data->id;
            }else{
                $insur_data_id = InsuranceData::insertGetId($insurance_data);
            }
            //insert insurance
            $od = 0;
            $tp = 0;
            $insur_am = 0;
            if($request->input('s_insurance') == 'zero_dep')
            {
                $od = 1;$tp = 5;
                $insur_am = $request->input('zero_dep_cost');
            }elseif($request->input('s_insurance') == 'comprehensive'){
                $od = 1;$tp = 5;
                $insur_am = $request->input('comprehensive_cost');
            }elseif($request->input('s_insurance') == 'ltp_5_yr'){
                $od = 5;$tp = 5;
                $insur_am = $request->input('ltp_5_yr_cost');
            }
            elseif($request->input('s_insurance') == 'ltp_zero_dep'){
                $od = 5;$tp = 5;
                $insur_am = $request->input('ltp_zero_dep_cost');
            }
            $self_insurance = 1;
            if($request->input('self_assited_ins') != 'self'){
                $self_insurance = 0;
            }
            $common_ins_data = [
                'customer_id'   =>  $customerId,
                'sale_id'   =>  $saleInsertId,
                'insurance_co'  =>  $request->input('insur_c'),
                'insurance_amount'  =>  $insur_am,
                'insurance_name'    =>  $request->input('s_insurance'),
                'type'  =>  'new',
                'start_date'    =>  date('Y-m-d'),
                'insurance_date'    =>  date('Y-m-d'),
                'status'    =>  'Done',
                'insurance_data_id' =>  $insur_data_id,
                'self_insurance'    =>  $self_insurance
            ];
            $od_ins_data = array_merge($common_ins_data,[
                'policy_tenure' =>  $od,
                'insurance_type'    =>  'OD'
            ]);
            if($request->input('cpa_cover'))
            {
                $cpa_company  =  $request->input('cpa_company');
                $cpa_amount  =  $request->input('cpa_cover_cost');
                $cpa_duration  = $request->input('cpa_duration');
                $od_ins_data = array_merge($od_ins_data,[
                    'cpa_company'   =>  $cpa_company,
                    'cpa_tenure'   =>  $cpa_duration,
                    'cpa_amount'   =>  $cpa_amount
                ]);
            }
            // third party data insert
            $tp_ins_data = array_merge($common_ins_data,[
                'policy_tenure' =>  $tp,
                'insurance_type'    =>  'TP'
            ]);
            $ins_od_insurance = Insurance::insertGetId($od_ins_data);
            $ins_tp_insurance = Insurance::insertGetId($tp_ins_data);


            if($request->input('customer_pay_type') == 'finance')
            {
                $finance = array(
                    'sale_id'    =>  $saleInsertId,
                    'name'  =>  $request->input('finance_name'),
                    'company'  =>  $request->input('finance_company_name'),
                    'loan_amount'  =>  $request->input('loan_amount'),
                    'do'  =>  $request->input('do'),
                    'dp'  =>  $request->input('dp'),
                    'los'  =>  $request->input('los'),
                    'roi'  =>  $request->input('roi'),
                    'payout'  =>  $request->input('payout'),
                    'pf'  =>  $request->input('pf'),
                    'emi'  =>  $request->input('emi'),
                    'mac'  =>  $request->input('monthAddCharge'),
                    'sd'  =>  $request->input('sd')
                );
                $financeInsert = SaleFinance::insertGetId($finance);
            }
            elseif($request->input('customer_pay_type') == 'self_finance')
            {
                $self_finance = array(
                    'sale_id'    =>  $saleInsertId,
                    'company'  =>  $request->input('self_finance_company'),
                    'loan_amount'  =>  $request->input('self_finance_amount'),
                    // 'bank_name'  =>  $request->input('self_finance_bank'),
                    
                );
                $self_financeInsert = SaleFinance::insertGetId($self_finance);
            }
            
            // if customer Pre-Booking exists
            $pending_payment = $balance;
            if($customerFlag == 'booking')
            {
                $old_pay = Payment::where('booking_id',$booking_id)->whereNull('sale_id')
                            ->where('type','booking')
                            ->sum('amount');
                $pending_payment = $pending_payment-$old_pay;
                Payment::where('booking_id',$booking_id)->whereNull('sale_id')
                            ->update(['sale_id'=>$saleInsertId,
                                    'type'  =>  'sale']); 
                Sale::where('id',$saleInsertId)->update(['payment_amount' => $old_pay]);  
                $bookingRow = BookingModel::where('id',$booking_id)->where('status','booked')->first();
                Stock::where('product_id',$bookingRow['product_id'])->where('store_id',$bookingRow['store_id'])->decrement('booking_qty',1);
                Stock::where('product_id',$bookingRow['product_id'])->where('store_id',$bookingRow['store_id'])->increment('quantity',1);
                BookingModel::where('id',$booking_id)->where('status','booked')
                                ->update(['status' => 'sale']);
                
            }   
            

            $type = 'bestdeal';
            $call_type = 'thankyou';
            $call_type1 = 'psf';
            $sale_date = $request->input('sale_date');
            $sale_id = $saleInsertId;
            $store_id = $request->input('store_name');
            // print($store_id);die();
            $next_call_date = date('Y-m-d', strtotime('+2 day', strtotime($sale_date)));
            $next_call_date1 = date('Y-m-d', strtotime('+4 day', strtotime($sale_date)));
            if ($saleInsertId) {
                $redirect = CustomHelpers::CreateCalling($type,$call_type,$store_id,$sale_id,$next_call_date);
                $redirect = CustomHelpers::CreateCalling($type,$call_type1,$store_id,$sale_id,$next_call_date1);
                // return redirect($redirect);
                // if ($redirect == NULL) {
                // DB::rollback();
                // //print_r($ex->getMessage());die;
                // return back()->with('error','Something went wrong.')->withInput();
                
                // }
            }

            // create Pollution certificate
            if($request->input('pollution_cert'))
            {
                $duration = Settings::where('name','PollutionCertificateDuration')->first();
                if(!isset($duration->value)){
                    DB::rollBack();
                    return back()->with('error','Pollution Certificate Duration Master Not Found.')->withInput();
                }
                $start_date = $sale_date;
                $end_date = date('Y-m-d', strtotime('+'.$duration->value.' year',strtotime($start_date)));
                $pollution_data = [
                    'sale_id'   =>  $sale_id,
                    'start_date'    =>  $start_date,
                    'end_date'  =>  $end_date,
                    'amount'    =>  $request->input('pollution_cert_cost')
                ];
                $pollution_cert = PollutionCertificate::insertGetId($pollution_data);
            }

            // create amc
            if($request->input('amc'))
            {
                $service_allowed = Settings::where('name','AMCServiceAllowed')->first();
                if(!isset($service_allowed->value)){
                    DB::rollBack();
                    return back()->with('error','AMC Master Not Found.')->withInput();
                }
                $start_date = date('Y-m-d',strtotime('+1 year',strtotime($sale_date)));
                $end_date = date('Y-m-d', strtotime('+1 year',strtotime($start_date)));
                $amc_data = [
                    'sale_id'   =>  $sale_id,
                    'start_date'    =>  $start_date,
                    'end_date'  =>  $end_date,
                    'service_allowed'   =>  $service_allowed->value,
                    'amount'    =>  $request->input('amc_cost')
                ];
                $amc = AMC::insertGetId($amc_data);
            }

            // create ew
            if($request->input('ew'))
            {
                $ew_year = $request->input('ew_duration');
                $product_info = ProductModel::where('id',$request->input('prod_name'))
                                ->select('id','st_warranty_duration','model_category')
                                ->first();
                $prod_duration = $product_info->st_warranty_duration;
                $start_date = date('Y-m-d',strtotime('+'.$prod_duration.' year',strtotime($sale_date)));
                $end_date = date('Y-m-d', strtotime('+'.$ew_year.' year',strtotime($start_date)));

                // get km
                $find_km = EwMaster::where('duration',$ew_year)
                                    ->where('category',$product_info->model_category)
                                    ->select('id','km')
                                    ->first();
                if(!isset($find_km->id)){
                    DB::rollback();
                    return back()->with('error','Extended Warranty Master Not Found.')->withInput();
                }
                $ew = [
                    'sale_id'   =>  $sale_id,
                    'start_date'    =>  $start_date,
                    'end_date'  =>  $end_date,
                    'km'    =>  $find_km->km,
                    'amount'    =>  $request->input('ew_cost')
                ];
                $ew = ExtendedWarranty::insertGetId($ew);
            }
            /// create hjc
            if($request->input('hjc'))
            {
                $start_date = date('Y-m-d',strtotime('+1 year',strtotime($sale_date)));
                $end_date = date('Y-m-d', strtotime('+1 year',strtotime($start_date)));
                $hjc = [
                    'sale_id'   =>  $sale_id,
                    'start_date'    =>  $start_date,
                    'end_date'  =>  $end_date,
                    'amount'    =>  $request->input('hjc_cost')
                ];
                $hjc = HJCModel::insertGetId($hjc);
            }

            if($insert_approval == 0){

                // insert in payment request
                $payment_req = PaymentRequest::insertGetId([
                    'store_id'  =>  $saleInsert['store_id'],
                    'type'  =>  'sale',
                    'type_id'   =>  $saleInsertId,
                    'amount'    =>  round($request->input('balance')),
                    'status'    =>  'Pending'
                ]);
            }

            // insert in rto
            $rtodata = RtoModel::insertGetId(
                [
                    'sale_id'=> $saleInsertId,
                    'customer_id'=>$customerId,
                    'approve'   =>  1,
                    'rto_amount'    =>  2500,
                    'file_uploaded' =>  1,
                    'created_by' => Auth::id()
                ]
            );
            $customerData = Customers::where('id',$customerId)->first();

            if ($rtodata) {
                $summary = RtoSummary::insertGetId(
                [
                    'rto_id' => $rtodata,
                    'customer_name'=>$customerData->name,
                    'relation_type'=>$customerData->relation_type,
                    'relation'=>$customerData->relation,
                    'mobile'=>$customerData->mobile,
                    'address'=>$customerData->address,
                    'created_by' => Auth::id(),
                    'numberPlateStatus' => 0,
                    'rcStatus' => 0,
                    'currentStatus' => 1
                ]
            ); 
            }

        }catch(\Illuminate\Database\QueryException $ex) {
        DB::rollback();
            //print_r($ex->getMessage());die;
            return back()->with('error','Something went wrong.'.$ex->getMessage())->withInput();
        }
        CustomHelpers::userActionLog($action='Create BestDeal Sale',$saleInsertId,$customerId,'Purchase');
        DB::commit();
        return redirect('/admin/sale/best/deal/inventory')->with('success','Successfully Inventory Buyed');
        
    }
    // best deal sale list
    public function BestDealSale_list() {
        return view('admin.bestdeal.bestdeal_sale_list',['layout' => 'layouts.main']);
   }

   public function BestDealSaleList_api(Request $request,$type) {
       $search = $request->input('search');
       $serach_value = $search['value'];
       $start = $request->input('start');
       $limit = $request->input('length');
       $offset = empty($start) ? 0 : $start ;
       $limit =  empty($limit) ? 10 : $limit ;
        
        if($type == 'done'){
           $api_data = Sale::leftJoin('product','sale.product_id','product.id')
           ->leftJoin('payment',function($join){
               $join->on(DB::raw('payment.type = "sale" and payment.sale_id'),'=','sale.id') ->where('payment.status','<>','cancelled');
           })
           ->leftJoin('customer','sale.customer_id','customer.id')
           ->leftJoin('sale_order','sale_order.sale_id','sale.id')
           ->leftJoin('insurance','insurance.sale_id','sale.id')
           ->where('sale.status','<>','cancelled')
           ->where('sale.tos','bestdeal')
           ->leftJoin('users','users.id',DB::raw(Auth::id()))
           ->select(
               'sale.id',
               'sale.status',
               'customer.name',
               'sale.sale_no',
               'sale.sale_date',
               'sale.balance',
               DB::raw('IFNULL(sum(payment.amount),0) as amount'),
               DB::raw('concat(product.model_category,"-",product.model_name,"-",product.model_variant) as product_name'),
               'users.user_type',
               'users.role'
           )
           -> groupBy('sale.id');
           $api_data->where(function($query) {
                  $query->where('insurance.policy_number','<>',' ')
                   ->where('sale_order.product_frame_number','<>',' ');
           });
            
        }elseif($type == 'pending'){
           $api_data= Sale::leftJoin('product','sale.product_id','product.id')
           ->leftJoin('payment',function($join){
               $join->on(DB::raw('payment.type = "sale" and payment.sale_id'),'=','sale.id') ->where('payment.status','<>','cancelled');
           })
           ->leftJoin('customer','sale.customer_id','customer.id')
           ->leftJoin('rto','rto.sale_id','sale.id')
           ->leftJoin('sale_order','sale_order.sale_id','sale.id')
           ->leftJoin('insurance','insurance.sale_id','sale.id')
           ->where('sale.status','<>','cancelled')
           ->where('sale.tos','bestdeal')
           ->leftJoin('users','users.id',DB::raw(Auth::id()))
           ->select(
               'sale.id',
               'customer.name',
               'sale.status',
               'sale.sale_no',
               'sale.sale_date',
               'sale.balance',
               'rto.approve',
               DB::raw('IFNULL(sum(payment.amount),0) as amount'),
               DB::raw('concat(product.model_category,"-",product.model_name,"-",product.model_variant) as product_name'),
               'users.user_type',
               'users.role'
           )
           -> groupBy('sale.id');
           $api_data->where(function($query) {
                  $query->whereNull('insurance.policy_number')
                   ->orwhereNull('sale_order.product_frame_number');
           });
        }elseif ($type == 'all') {
            $api_data= Sale::leftJoin('product','sale.product_id','product.id')
           ->leftJoin('payment',function($join){
               $join->on(DB::raw('payment.type = "sale" and payment.sale_id'),'=','sale.id')
               ->where('payment.status','<>','cancelled');
           })
           ->leftJoin('customer','sale.customer_id','customer.id')
           ->leftJoin('rto','rto.sale_id','sale.id')
           ->where('sale.tos','bestdeal')
           ->leftJoin('users','users.id',DB::raw(Auth::id()))
           ->select(
               'sale.id',
               'sale.status',
               'customer.name',
               'sale.sale_no',
               'sale.sale_date',
               'sale.refunded_amount',
               'sale.balance',
               'rto.approve',
               DB::raw('IFNULL(sum(payment.amount),0) as amount'),
               DB::raw('concat(product.model_category,"-",product.model_name,"-",product.model_variant,"-",product.color_code) as product_name'),
               'users.user_type',
               'users.role'
           )
           -> groupBy('sale.id');
        }

        // print_r($api_data->get());die;
           $api_data->whereIn('sale.store_id',CustomHelpers::user_store());
           if(!empty($serach_value))
           {
               $api_data->where(function($query) use ($serach_value){
                   $query->where(DB::raw('concat(product.model_category,"-",product.model_name,"-",product.model_variant,"-",product.color_code)'),'like',"%".$serach_value."%")
                   ->orwhere('sale.sale_no','like',"%".$serach_value."%")
                   ->orwhere('customer.name','like',"%".$serach_value."%")
                   ->orwhere('sale.sale_date','like',"%".$serach_value."%")
                   ->orwhere('sale.balance','like',"%".$serach_value."%")
                   ->orwhere('sale.status','like',"%".$serach_value."%");
               });
           }
           if(isset($request->input('order')[0]['column']))
           {
               $data = [
               'product.id',
               'sale.id',
               'customer.name',
               'sale.sale_date',
               'sale.balance',
               'sale.refunded_amount',
               'payment.amount',
               'sale.status',
               ];
               $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
               $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
           }
           else
               $api_data->orderBy('sale.id','desc');      
       
       $count = count( $api_data->get()->toArray());
       $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
       $array['recordsTotal'] = $count;
       $array['recordsFiltered'] = $count;
       $array['data'] = $api_data; 
       return json_encode($array);
   }

    public function fillPendingDetails(Request $request, $sale_id)
    {
        $redirect = CustomHelpers::checkBestDealSaleNextTab($sale_id);
        return redirect($redirect);
    }

    // best deal sale payment page
    public function sale_pay($id) {

        $saleData = Sale::where("id",$id)->where('tos','bestdeal')->get(); 
        $paid = DB::table('payment')->where('status','<>','cancelled')->where('type','sale')->where('sale_id',$id)->sum('amount');
        $secAmt = DB::table('payment')->where('type','sale')->where('sale_id',$id)->where('status','<>','cancelled')->sum('security_amount');
        // $pay_mode = DB::table('master')->where('type','payment_mode')->select('key','value')->get();

        $bookingData = Sale::where('id',$id)->get();
        $payData = Payment::where('sale_id',$id)->where('type','sale')->get();
        $data = array(
            'saleData' => $saleData, 
            'paid' => $paid,
            'secAmt'    =>  $secAmt,
            'bookingData'  =>  $bookingData,
            'payData'   =>  $payData,
            'layout' => 'layouts.main'
        );
        return view('admin.bestdeal.pay_details',$data);
    }

    // --------------- end best dela sale payment

    // bestdeal sale order page
    public function sale_document_page($id) {

        $saleData = Sale::leftJoin('store','sale.store_id','store.id')->where('sale.tos','bestdeal')
                            ->where('sale.id',$id)
                            ->select('sale.*')
                            ->get()
                            ->first();
        $paid_amount = DB::table('payment')->where('sale_id',$id)->where('status','received')->where('type','sale')->sum('amount');
        if($saleData->balance > $paid_amount && $saleData->pay_later == 0)
        {
            return back()->with('error','Please Firstly Pay full payment with Confirmation Payment');
        }
        $old_data = BestDealSaleDocument::where('sale_id',$saleData->id)->first();
        $data = array(
            'saleData' => $saleData, 
            'data' => $old_data,
            'layout' => 'layouts.main'
        );
         return view('admin.bestdeal.document_page',$data);
        
    }
    public function sale_document_page_db(Request $request)
    {

        $this->validate($request,[     
            'form29'=>'required',
            'form30'=>'required',
            'photo'=>'required',
            'impression'=>'required',
            'aadhar'=>'required',
            // 'pendency'=>'required'
        ],[
            'form29.required'    =>  'This Field is required',
            'form30.required'    =>  'This Field is required',
            'photo.required'    =>  'This Field is required',
            'impression.required'    =>  'This Field is required',
            'aadhar.required'    =>  'This Field is required',
            // 'pendency.required'    =>  'This Field is required'

        ]);

        try{
            $sale_id = $request->input('sale_id');
            $store_id = $request->input('store_id');

            $find = BestDealSaleDocument::where('sale_id',$sale_id)
                                            ->first();
            if(isset($find->id))
            {
                return back()->with('error','Error, You Are Not Authorized for Update !!')->withInput();
            }
            $data = [
                'sale_id'   =>  $sale_id,
                'form29'    =>  $request->input('form29'),
                'form30'    =>  $request->input('form30'),
                'impression'    =>  $request->input('impression'),
                'photo'    =>  $request->input('photo'),
                'aadhar'    =>  $request->input('aadhar'),
                'pendency'    =>  $request->input('pendency'),
            ];
            $insert = BestDealSaleDocument::insertGetId($data);

        }catch(\Illuminate\Database\QueryException $ex) {
        DB::rollback();
            //print_r($ex->getMessage());die;
            return back()->with('error','Something went wrong.'.$ex->getMessage())->withInput();
        }
        DB::commit();
        return redirect('/admin/bestdeal/sale/document/'.$sale_id)->with('success','Successfully Submited !!');

    }
    public function sale_pay_detail($id) {
        $bookingData = Sale::where('id',$id)->get();
        $payData = Payment::where('sale_id',$id)->where('type','sale')->get();
        $data = array(
            'bookingData' => $bookingData,
            'payData' => $payData,
            'layout' => 'layouts.main'
        );
         return view('admin.sale.paymet_detail',$data);
    }

    // create hirise and wings

    public function createHirise($id) {
        $saleData = Sale::where("id",$id)->get()->first();
        $hiriseData = Hirise::where("sale_id",$id)->get()->first();
        // $extendedWarrantyData = ExtendedWarranty::where("sale_id",$id)->get()->first();
        $data  = array(
           'saleData' => $saleData, 
           'hiriseData' => $hiriseData,
        //    'extendedWarrantyData' => $extendedWarrantyData,/
           'layout' => 'layouts.main', 
       );
       return view('admin.bestdeal.create_hirise',$data);
    }

    public function createHirise_db(Request $request) {
        
        //print_r($request->input());die();
        try {
            $this->validate($request,[
                'sale_id'=>'required',
                'name'=>'required_if:hirise,yes',
                'mobile'=>'required_if:hirise,yes',
                'address'=>'required_if:hirise,yes',
                'wing_invoice'=>'required_if:wings,yes',
            ],[
                'name.required'=> 'The Name field is required when Hirise Details Complete is yes.', 
                'mobile.required'=> 'The Name field is required when Hirise Details Complete is yes.', 
                'address.required'=> 'The Name field is required when Hirise Details Complete is yes.', 
                'wing_invoice.required'=> 'The Invoice field is required when Wings Billing Complete is yes.'
            ]);
            $getdata = Hirise::where('sale_id', $request->input('sale_id'))->first();
            DB::beginTransaction();
            if (isset($getdata->id)) {
                if(!empty($getdata->name)){
                    if($request->input('hirise') == 'no'){
                        return back()->with('error','You are not authorized for update Hirise.')->withInput();
                    }
                }elseif(!empty($getdata->wings_invoice)){
                    if($request->input('wings') == 'no'){
                        return back()->with('error','You are not authorized for update Wings.')->withInput();
                    }
                }
                elseif(!empty($getdata->name) && !empty($getdata->wings_invoice))
                {
                    return back()->with('error','You are not authorized for update');
                }

                if($request->input('hirise') == 'yes')
                {
                    if(empty($getdata->name))
                    {
                        $hirisedata =   Hirise::where('id',$getdata->id)
                                            ->update([
                                                'name'=>$request->input('name'),
                                                'mobile'=>$request->input('mobile'),
                                                'address'=>$request->input('address')
                                            ]);
                    }
                }
                if($request->input('wings') == 'yes')
                {
                    if(empty($getdata->name))
                    {
                        $hirisedata =   Hirise::where('id',$getdata->id)
                                            ->update([
                                                'wings_invoice' =>  $request->input('wing_invoice')
                                            ]);
                    }
                }
            }
            else{
                $ins_data = [];
                if($request->input('hirise') == 'yes')
                {
                    $ins_data = [
                        'name'=>$request->input('name'),
                        'mobile'=>$request->input('mobile'),
                        'address'=>$request->input('address')
                    ];
                }
                elseif($request->input('wings') == 'yes')
                {
                    $ins_data = [
                        'wings_invoice' =>  $request->input('wing_invoice')
                    ];
                }
                if(count($ins_data) > 0){

                    $hirisedata = HiRise::insertGetId(array_merge($ins_data,
                    [
                        'sale_id'=>$request->input('sale_id'),
                        
                    ])); 
                }else{
                    return back()->with('error','Select minimum one option Yes and fill fields.')->withInput();
                }

            }
            
            // if($hirisedata==NULL) {
            //     DB::rollback();
            //     return redirect('/admin/bestdeal/sale/hirise/'.$request->input('sale_id'))->with('error','some error occurred')->withInput();
            // } else{
                
            //  }    
        
        }  catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return redirect('/admin/bestdeal/sale/hirise/'.$request->input('sale_id'))->with('error','some error occurred'.$ex->getMessage())->withInput();
        }
        DB::commit();
        return redirect('/admin/bestdeal/sale/hirise/'.$request->input('sale_id'))->with('success','Successfully Updated Hirise Form.');
    }

    // bestdeal RTO
    public function createRto($id) {

        // $checkrto = DB::table('insurance')->where('sale_id',$id)->first();
        // if(empty($checkrto))
        // {
        //     return back()->with('error','Please Fill Insurance Firstly');
        // }
        $bookingData = Sale::where('sale.tos','bestdeal')
                            ->where('sale.id',$id)
                            ->leftJoin('customer','sale.customer_id','customer.id')
                            ->select('sale.*','customer.name as customer_name')
                            ->first();
        $rtoData = RtoModel::where("sale_id",$id)
                            ->leftJoin('rto_summary','rto.id','rto_summary.rto_id')
                            ->select('rto.*','rto.amount as actual_amount')
                            ->first();
        // print_r($rtoData);die;
         $data  = array(
            'bookingData' => $bookingData, 
            'rtoData' => $rtoData,
            'layout' => 'layouts.main', 
        );
        return view('admin.bestdeal.create_rto',$data);
    }

    public function createRto_db(Request $request) {
        //print_r($request->input());die();
        try {
            $this->validate($request,[
                // 'rto_type'=>'required',
                'rto_trans_status'=>'required',
                'rto_amount'=>'required_if:rto_trans_status,yes',
                // 'rto_finance'=>'required',
                // 'rto_app_no'=>'required',
            ],[
                // 'rto_type.required'=> 'This is required.',
                'rto_amount.required_if'=> 'This is required.',  
                'rto_trans_status.required'=> 'This is required.',  
                // 'rto_finance.required'=> 'This is required.',  
                // 'rto_app_no.required'=> 'This is required.',  
            ]);
            DB::beginTransaction();
            // $customerData = Customers::where('id',$request->input('customer_id'))->get()->first();
            $getdata = RtoModel::where('sale_id', $request->input('sale_id'))->get('id')->first();
            if ($getdata['id'] > 0) {
                $rtodata = RtoModel::where('id',$getdata['id'])->update(
                    [
                        // 'customer_id'=>$request->input('customer_id'),
                        // 'rto_type'=>$request->input('rto_type'),
                        'amount'=>$request->input('rto_amount'),
                        'rto_trans_status'=>$request->input('rto_trans_status'),
                        // 'rto_finance'=>$request->input('rto_finance'),
                        // 'application_number'=>$request->input('rto_app_no'),
                    ]
                );
            }

            if($rtodata==NULL) {
                    DB::rollback();
                   return redirect('/admin/bestdeal/sale/rto/'.$request->input('sale_id'))->with('error','some error occurred');
            } else{
                DB::commit();
                  return redirect('/admin/bestdeal/sale/rto/'.$request->input('sale_id'))->with('success','Successfully Updated .');
             }    
        
        }  catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return redirect('/admin/bestdeal/sale/rto/'.$request->input('sale_id'))->with('error','some error occurred'.$ex->getMessage());
        }
    }

    //  bestdeal pending items
    public function createPendingItem(Request $request,$id)
    {
        // $checkOrder = SaleOrder::where('sale_id',$id)->first();
        // if(empty($checkOrder))
        // {
        //     return back()->with('error','Please Fill Order Firstly');
        // }
        $sale = Sale::where('id',$id)->where('tos','bestdeal')->select('sale_no','pending_item')->first();
        // $accessories = OtcSale::where('otc_sale.sale_id',$id)
        //                 ->where('otc_sale.with_sale',1)
        //                 ->leftJoin('otc_sale_detail','otc_sale_detail.otc_sale_id','otc_sale.id')
        //                 ->where('otc_sale_detail.type','Part')
        //                 ->where('otc_sale_detail.with_sale',1)
        //                 ->leftJoin('part',function($join){
        //                     $join->on('part.id','otc_sale_detail.part_id');
        //                 })
        //                 ->select('otc_sale_detail.id',
        //                     'otc_sale.sale_id',    
        //                     'otc_sale_detail.part_id',
        //                     'part.name',
        //                     DB::raw('IF(otc_sale_detail.part_id = 0, otc_sale_detail.part_desc,part.name) as accessories_name'),
        //                     DB::raw('IF(otc_sale_detail.part_id = 0, "1",otc_sale_detail.qty) as qty'), 
        //                     'otc_sale_detail.amount'
        //                 )
        //                 ->get();
        // print_r($accessories);die;
        $other_item = ['Sale Invoice','Service Book','Bag','Key Ring','Warranty Card'];
        // print_r($accessories->toArray());die;
        $pending_item = OrderPendingModel::where('sale_id',$id)->first();

        // $pending_item = array();
        // $pending_other = array();
       
        $pending_item_all = (($pending_item)? $pending_item->toArray() : array());
        $pending_item = (($pending_item_all)?$pending_item_all['accessories_id'] : '');
        $pending_item = (($pending_item)? explode(',',$pending_item) : array() );
        // print_r($pending_item);die;
        $pending_other = (($pending_item_all)?$pending_item_all['other']: '');
        $pending_other = (($pending_other)? explode(',',$pending_other) : array() );
        $fuel = FuelModel::where('type_id',$id)->where('fuel_mode','sale')->first();
        
        //  print_r($sale->pending_item);die;
        // print_r($pending_other);die;
        $data = array(
            'sale_no'   =>  $sale->sale_no,
            'select_pending_item'  =>  $sale->pending_item,
            // 'accessories' => $accessories,
            'pending_item' => $pending_item,    
            'pending_other' => $pending_other,    
            'other_item' => $other_item,    
            'fuel' => $fuel,
            'sale_id' => $id,    
            'layout' => 'layouts.main'
        );
        return view('admin.bestdeal.pendingItem',$data);

    }
    public function createPendingItem_db(Request $request) {
        //print_r($request->input());die();
         try {
            $timestamp = date('Y-m-d G:i:s');
            $validator = Validator::make($request->all(),[
                'select_pending_item'   =>  'required',
                'fuel'   =>  'required|numeric|between:0,1',
            ],
            [
                'select_pending_item.required'  =>  'This Field is required',
                'fuel.required'  =>  'This Field is required',
            ]);
            $data[0] = $request->input('select_pending_item');
            // $data[1] = $request->input('pending_item');
            $data[2] = $request->input('other');

            $validator->after(function ($validator) use($data) {
                if ($data[0] == 'yes') {
                    if(empty($data[2]))
                    {
                        $validator->errors()
                        // ->add('pending_item', 'Any One Field is required, If you have to Choose Yes.')
                        ->add('other', 'Any One Field is required, If you have to Choose Yes.');
                    }
                }
            });

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            // print_r($request->input());die;
            DB::beginTransaction();
            // print_r($request->input());die;
            // if($request->input('select_pending_item') == 'yes')
            // {
                $sale_id = $request->input('sale_id');
                $prePendingCheck = Sale::where('id',$sale_id)->select('pending_item','store_id')->first();
                // if(!empty($prePendingCheck->pending_item))
                // {
                //     return back()->with('error','Error, You Are Not Authorized For Update This Page');
                // }
            if($request->input('select_pending_item') == 'yes')
            {
                // if (!empty($request->input('pending_item'))) {
                //         $acc = implode(',', $request->input('pending_item'));
                // }else{
                //     $acc = '';
                // }
                if (!empty($request->input('other'))) {
                        $oth = implode(',', $request->input('other'));
                }else{
                    $oth = '';
                }
                $check = OrderPendingModel::where('sale_id',$sale_id)->first();
                if(isset($check->id))
                {
                    $update = OrderPendingModel::where('sale_id',$sale_id)
                                ->update([
                                    'accessories_id'    =>  '',
                                    'other' =>  $oth
                                ]);
                    // return back()->with('error','Error, You Are Not Authorized For Update This Page');
                }
                else{
                    $insert = OrderPendingModel::insertGetId([
                        'sale_id'   =>  $sale_id,
                        'accessories_id'    =>  '',
                        // 'accessories_id'    =>  $acc,
                        'other' =>  $oth
                    ]);
                }
                $update = Sale::where('id',$sale_id)->update(['pending_item' => 'yes']);
            }
            else{
                $update = Sale::where('id',$sale_id)->update(['pending_item' => 'no']);
                $check = OrderPendingModel::where('sale_id',$sale_id)->first();
                if(isset($check->id)){
                    $update = OrderPendingModel::where('sale_id',$sale_id)
                    ->update([
                        'accessories_id'    =>  '',
                        'other' =>  ''
                    ]);
                }
            }
            // for fuel
            $checkFuel = FuelModel::where('type_id',$sale_id)->where('fuel_mode','sale')
                                ->where('fuel_type','Petrol')
                                    ->first();
            if(!isset($checkFuel->id)){

                $checkfuelStock = FuelStockModel::where('fuel_type','Petrol')
                                    ->where('store_id',$prePendingCheck->store_id)->first();
                if(isset($checkfuelStock->id)){
                    if($checkfuelStock->quantity < $request->input('fuel')){
                        DB::rollBack();
                        return back()->with('error','Fuel Stock is not Available.')->withInput();
                    }
                }else{
                    DB::rollBack();
                    return back()->with('error','Fuel Stock is not Available.')->withInput();
                }

                $insertFuel = FuelModel::insertGetId([
                    'store_id' => $prePendingCheck['store_id'],
                    'type_id'   =>  $sale_id,
                    'fuel_mode'    =>  'sale',
                    'fuel_type'    =>  'Petrol',
                    'requested_by' => Auth::id(),
                    'quantity' =>  $request->input('fuel'),
                    'status'    =>  'approved'
                ]);
            }else{
                $old_fuel = $checkFuel->quantity;
                $new_fuel = $request->input('fuel');
                if($old_fuel != $new_fuel){
                    DB::rollBack();
                    return back()->with('error','Fuel Quantity Will Not be Upadte.');
                }
            }
           
        }  catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return redirect('/admin/bestdeal/sale/pending/item/'.$request->input('sale_id'))->with('error','some error occurred'.$ex->getMessage());
        }
        DB::commit();
        return redirect('/admin/bestdeal/sale/pending/item/'.$request->input('sale_id'))->with('success','Successfully Updated.');

    }


    // update bestdeal sales

    //update sale

    public function update_bestdeal_sale(Request $request,$id){
      
        $salePay = Payment::where('type','sale')->where('type_id',$id)->sum('amount');
        if($salePay > 0)
        {
            return redirect('/admin/bestdeal/sale/list')->with('error','This Sale will not be editable, because this sale already associate with payment.');
        }
        $check = BestDealSale::where('best_deal_sale.sale_id',$id)->where('best_deal_sale.tos','best_deal')
                                ->where('best_deal_sale.selling_price','>',0)->where('best_deal_sale.status','Sale')
                                ->leftjoin('product','product.id','best_deal_sale.product_id')
                                ->select('best_deal_sale.id',
                                        DB::raw('IF(best_deal_sale.product_id = 0,best_deal_sale.model,product.model_name) as model'),        
                                        DB::raw('IF(best_deal_sale.product_id = 0,best_deal_sale.variant,product.model_variant) as variant'),        
                                        DB::raw('IF(best_deal_sale.product_id = 0,best_deal_sale.color,product.color_code) as color'),
                                        'best_deal_sale.maker',        
                                        'best_deal_sale.product_id',        
                                        'best_deal_sale.selling_price',      
                                        'best_deal_sale.insurance_policy_start',      
                                        'best_deal_sale.insurance_policy_end'      
                                )
                                ->first();
        if(!isset($check->id))
        {
            return back()->with('error','Error, Best Deal Not Found !!')->withInput();
        }
        $sale = Sale::where('sale.id',$id)->where('tos','bestdeal')
                                ->leftJoin('sale_finance_info','sale_finance_info.sale_id','sale.id')
                                // ->leftJoin('sale_accessories','sale_accessories.sale_id','sale.id')     
                                ->leftJoin('product','product.id','sale.product_id')
                                ->leftJoin('sale_discount',function($join){
                                    $join->on('sale_discount.type_id','sale.id')
                                            ->where('sale_discount.type','sale')
                                            ->whereIn('sale_discount.discount_type',array('Normal','Scheme'));
                                })
                                ->leftJoin('sale_rto',function($join){
                                    $join->on('sale_rto.sale_id','sale.id');
                                })
                                ->leftJoin('insurance',function($join){
                                    $join->on('insurance.sale_id','sale.id')
                                            ->where('insurance.insurance_type','OD');
                                })
                                ->leftJoin('amc',function($join){
                                    $join->on('amc.sale_id','sale.id');
                                })
                                ->leftJoin('pollution_certificate',function($join){
                                    $join->on('pollution_certificate.sale_id','sale.id');
                                })
                                ->leftJoin('hjc',function($join){
                                    $join->on('hjc.sale_id','sale.id');
                                })
                                ->leftJoin('extended_warranty',function($join){
                                    $join->on('extended_warranty.sale_id','sale.id');
                                })

                                ->select('sale.*',
                                    'sale_finance_info.name as finance_name','sale_finance_info.company as finance_company_name',
                                    'sale_finance_info.loan_amount as loan_amount','sale_finance_info.do','sale_finance_info.dp',
                                    'sale_finance_info.los','sale_finance_info.roi','sale_finance_info.payout','sale_finance_info.pf'
                                    ,'sale_finance_info.emi','sale_finance_info.mac as monthAddCharge','sale_finance_info.sd',
                                    'sale_finance_info.company as self_finance_company','sale_finance_info.loan_amount as self_finance_amount',
                                    'product.basic_price','product.model_name','product.model_variant','product.color_code',
                                    'sale_discount.discount_type','sale_discount.amount as discount_amount','sale_discount.scheme_id',
                                    'sale_discount.remark as scheme_remark',
                                    'insurance.policy_tenure','insurance.insurance_date','insurance.insurance_co','insurance.insurance_type',
                                    'insurance.insurance_name','insurance.insurance_amount','insurance.cpa_company','insurance.cpa_tenure',
                                    'insurance.cpa_amount','insurance.self_insurance',
                                    DB::raw('IFNULL(pollution_certificate.amount,0) as pollution_cert_amount'),
                                    DB::raw('IFNULL(amc.amount,0) as amc_amount'),
                                    DB::raw('IFNULL(hjc.amount,0) as hjc_amount'),
                                    DB::raw('TIMESTAMPDIFF(year,extended_warranty.start_date,extended_warranty.end_date) as ew_duration'),
                                    DB::raw('IFNULL(extended_warranty.amount,0) as ew_amount'),
                                    'sale_rto.handling_charge','sale_rto.rto as sale_rto_type'
                                )   
                                ->first();

        $csd = SaleDiscount::where('type_id',$id)->where('discount_type','CSD')
                                ->first();

        $tp_insur = Insurance::where('sale_id',$id)->where('insurance_type','TP')   
                                    ->first();

        $customer = Customers::all();

        $state = State::all();
        //  print_r(Auth::user()->store_id);die;
        $scheme = Scheme::all();
        
        $store = Store::whereIn('id',CustomHelpers::user_store())
                    ->select('id',DB::raw('concat(store.name,"-",store.store_type) as name'))->get();
        
        // print_r($model_name->toArray());die;
        // $company_name = DB::table('master')->where('type','company_name')->select('key','value')
                            // ->orderBy('order_by','ASC')->get();
        $company_name = FinanceCompany::orderBy('id','ASC')->get();
        $sale_user = Users::where('role','SaleExecutive')->get(['id','name']);
        $insur_company = InsuranceCompany::orderBy('id')->get();
        $cpa_company = InsuranceCompany::where('cpa',1)
                        ->orderBy('id')->get();
        $maker = Master::where('type','prod_maker')->orderBy('order_by')->get();
        // $maker = array('Honda','Bajaj','Hero','TVS');

        $data = array(
            'layout' => 'layouts.main',
            'csd'   =>  CustomHelpers::csd_amount(),
            'insur_company'  =>  $insur_company,
            'sale'  =>  $sale,
            'csd_dis' =>  $csd,
            'tp_insur'  =>  $tp_insur,
            'bestdeal'  =>  $check,
            'customer' => ($customer->toArray()) ? $customer->toArray() : array() ,
            'state' =>  ($state->toArray()) ? $state->toArray()  :  array() ,
            'scheme'    =>  $scheme,
            'store'    =>  $store->toArray(),
            'sale_user' =>  ($sale_user->toArray()) ? $sale_user->toArray() : array() ,
            'company_name' =>  $company_name,
            'maker' =>  $maker,
            'cpa_company'   =>  $cpa_company,
            'bestdeal_id'   =>  $check->id

        );
        return view('admin.bestdeal.update_bestdeal_sale',$data);
    }
    public function update_bestdeal_saleDB(Request $request)
    {
        DB::beginTransaction();
        // $bestdeal_id = $request->input('bestdeal');
        $csd_am = CustomHelpers::csd_amount();
        // best deal validation
        
        $sale_id = $request->input('sale_id');
        // print_r($request->input());die;
        // validate Financier Mobile Number will not be equal Customer Mobile Number
        if($request->input('customer_pay_type') == 'finance')
        {
            $find_mobile = DB::table('master')->where('value',$request->input('finance_name'))->select('details')->first();
            if(isset($find_mobile->details))
            {
                $details = $find_mobile->details;
                $mob = json_decode($details,true);
                if(in_array($request->input('mobile'),$mob['mobile']))
                {
                    return back()->with('error','Error, Financier Mobile Number and Customer Mobile Number should not be Same.')->withInput();
                }
            }
        }
     
        // $total_discount = 0;
        // $total_amount = 0;
        $ExShowroomPrice = round($request->input('ex_showroom_price'));
        //accessories
        $validateZero = [];
        $validateZeroMsg = [];
        
        //  echo $total_accessories;
        //discount
        // $exch_best_val = ($request->input('tos') != 'new') ?floatval($request->input('exchange_value')) : 0.0 ;
        $do = ($request->input('customer_pay_type') == 'finance') ?floatval($request->input('do')) : 0.0 ;
        $discount = floatval($request->input('discount_amount')) ;
        $total_discount = $do+$discount;
        // echo "<br>".$total_discount;
        //total selected amount calculate
        if($request->input('csd_check') && $request->input('sale_type') == 'corporate')
        {
            $csd = floatval($ExShowroomPrice * $csd_am/100);      // in csd amount
            $total_discount = round($total_discount + $csd);
        }

        $total_amount = $ExShowroomPrice+(($request->input('s_insurance') == 'zero_dep') ? floatval($request->input('zero_dep_cost')) : 0.0 )
                            +(($request->input('s_insurance') == 'comprehensive') ? floatval($request->input('comprehensive_cost')) : 0.0 )
                            +(($request->input('s_insurance') == 'ltp_5_yr') ? floatval($request->input('ltp_5_yr_cost')) : 0)
                            +(($request->input('s_insurance') == 'ltp_zero_dep') ? floatval($request->input('ltp_zero_dep_cost')) : 0.0 )
                            +((!empty($request->input('cpa_cover'))) ? floatval($request->input('cpa_cover_cost')) : 0.0 )
                            +(($request->input('customer_pay_type') != 'cash') ? floatval($request->input('hypo_cost')) : 0.0 )
                            +((!empty($request->input('amc'))) ? floatval($request->input('amc_cost')) : 0.0 )
                            +((!empty($request->input('ew'))) ? floatval($request->input('ew_cost')) : 0.0 )
                            +((!empty($request->input('hjc'))) ? floatval($request->input('hjc_cost')) : 0.0 );
                            
        $total_amount = floatval($request->input('handling_charge'))+$total_amount;
        $hand_valid = 'gt:0';
        if($request->input('self_assited') == 'self'){
            $hand_valid = 'gte:0';
        }
        $insur_valid = '|gt:0';
        if($request->input('self_assited_ins') == 'self'){
            $insur_valid = '|gte:0';
        }
        if($request->input('pollution_cert') == 'yes'){
            $total_amount = $total_amount+floatval($request->input('pollution_cert_cost'));
        }

        $this->validate($request,array_merge($validateZero,[     
            // 'sale_no'=>'required',
            'sale_date'=>'required|date',
            'prod_name'=>'required',
            'store_name'=>'required',
            'sale_type'=>'required',
            'care_of'=>'required_if:sale_type,corporate',
            'gst'=>'required_if:sale_type,corporate',
            // 'select_customer'=>'required',
            // 'enter_customer_name'=>'required_unless:select_customer,exist_customer',
            'select_customer_name'=>'required_if:select_customer,exist_customer',
            // 'pre_booking'=>'required_if:select_customer,booking',
            'relation_type'=>'required',
            'relation'=>'required',
            'aadhar'=>'required_without_all:voter',
            'voter'=>'required_without_all:aadhar',
            'address'=>'required',
            'state'=>'required',
            'city'=>'required',
            'pin'=>'required',
            // 'email'=>'required',
            'mobile'=>'required',
            'sale_executive'=>'required',
            'customer_pay_type'=>'required',
            'finance_name'=>'required_if:customer_pay_type,finance',
            'finance_company_name'=>'required_if:customer_pay_type,finance',
            'loan_amount'=>'required_if:customer_pay_type,finance'.(($request->input('customer_pay_type') == 'finance')? '|gt:0' : '' ),
            'do'=>'required_if:customer_pay_type,finance',
            'dp'=>'required_if:customer_pay_type,finance'.(($request->input('customer_pay_type') == 'finance')? '|gt:0' : '' ),
            // 'los'=>'required_if:customer_pay_type,finance',
            'roi'=>'required_if:customer_pay_type,finance',
            'payout'=>'required_if:customer_pay_type,finance',
            'pf'=>'required_if:customer_pay_type,finance',
            'emi'=>'required_if:customer_pay_type,finance'.(($request->input('customer_pay_type') == 'finance')? '|gt:0' : '' ),
            'monthAddCharge'=>'required_if:customer_pay_type,finance',
            'sd'=>'required_if:customer_pay_type,finance',
            'self_finance_company'=>'required_if:customer_pay_type,self_finance',
            'self_finance_amount'=>'required_if:customer_pay_type,self_finance'.(($request->input('customer_pay_type') == 'self_finance')? '|gt:0' : '' ),
            // 'self_finance_bank'=>'required_if:customer_pay_type,self_finance',
            'ex_showroom_price'=>'required|gt:0',
            'self_assited'  =>  'required',
            'handling_charge'   =>  'required|'.$hand_valid,
            'self_assited_ins'  =>  'required',
            'insur_c'   =>  'required',
            'zero_dep_cost'=>'required_if:s_insurance,zero_dep'.(($request->input('s_insurance') == 'zero_dep')? $insur_valid : '' ).'',
            'comprehensive_cost'=>'required_if:s_insurance,comprehensive'.(($request->input('s_insurance') == 'comprehensive')? $insur_valid : '' ).'',
            'ltp_5_yr_cost'=>'required_if:s_insurance,ltp_5_yr'.(($request->input('s_insurance') == 'ltp_5_yr')? $insur_valid : '' ).'',
            'ltp_zero_dep_cost'=>'required_if:s_insurance,ltp_zero_dep'.(($request->input('s_insurance') == 'ltp_zero_dep')? $insur_valid : '' ).'',
            'cpa_company'=>'required_if:cpa_cover,cpa_cover',
            'cpa_duration'=>'required_if:cpa_cover,cpa_cover'.(!empty($request->input('cpa_cover'))? '|integer' : '' ).'',
            'cpa_cover_cost'=>'required_if:cpa_cover,cpa_cover'.(!empty($request->input('cpa_cover'))? '|gt:0' : '' ).'',
            'hypo_cost'=>'required_unless:customer_pay_type,cash'.(($request->input('customer_pay_type') != 'cash')? '|gt:0' : '' ).'',
            'pollution_cert_cost'=>'required_if:pollution_cert,yes'.(!empty($request->input('pollution_cert'))? '|gt:0' : '' ).'',
            'amc_cost'=>'required_if:amc,amc'.(!empty($request->input('amc'))? '|gt:0' : '' ).'',
            'ew_duration'=>'required_if:ew,ew'.(!empty($request->input('ew'))? '|gt:0' : '' ).'',
            'ew_cost'=>'required_if:ew,ew'.(!empty($request->input('ew'))? '|gt:0' : '' ).'',
            'hjc_cost'=>'required_if:hjc,hjc'.(!empty($request->input('hjc'))? '|gt:0' : '' ).'',
            'discount'=>'required',
            'discount_amount'=>'required',
            'scheme'=>'required_if:discount,scheme',
            'scheme_remark'=>'required_if:discount,scheme',
            'total_calculation'=>'required|in:'.$total_amount,
            'balance'   =>  'required|lte:'.($total_amount-$total_discount)
        ]),array_merge($validateZeroMsg,[
            // 'sale_no.required'  =>  'This Field is required',
            'sale_date.required'    =>  'This Field is required',
            'sale_date.date'    =>  'This Field is only date format',
            'prod_name.required'    => 'This Field is required' ,
            'store_name.required'   =>  'This Field is required',
            'sale_type.required'   =>  'This Field is required',
            'care_of.required_if'=>'This Field is required',
            'gst.required_if'=>'This Field is required',
            // 'select_customer.required'  =>  'This Field is required',
            // 'enter_customer_name.required_unless'   =>  'This Field is required',
            'select_customer_name.required_if'  =>  'This Field is required',
            // 'pre_booking.required_if'   =>  'This Field is required',
            'relation_type.required' =>'This Field is required',
            'relation.required' =>  'This Field is required',
            'aadhar.required_without_all'=> 'This Field is required, When You Not Fill Voter Number.',
            'voter.required_without_all'=> 'This Field is required, When You Not Fill Aadhar Number.',
            'address.required'   =>  'This Field is required',
            'state.required'    =>  'This Field is required',
            'city.required' =>  'This Field is required',
            'pin.required' =>  'This Field is required',
            // 'email.required'    =>  'This Field is required',
            'mobile.required'   =>  'This Field is required',
            'sale_executive.required' =>  'This Field is required'   ,
            'customer_pay_type.required'    =>  'This Field is required',
            'finance_name.required_if' =>  'This Field is required',
            'finance_company_name.required_if'  =>  'This Field is required',
            'loan_amount.required_if'  =>  'This Field is required',
            'loan_amount.gt'    =>  'This Field must be greater than 0.',
            'dp.gt'    =>  'This Field must be greater than 0.',
            'emi.gt'    =>  'This Field must be greater than 0.',
            'do.required_if'    =>  'This Field is required',
            'dp.required_if' =>  'This Field is required',
            // 'los.required_if'   =>  'This Field is required',
            'roi.required_if'  =>  'This Field is required',
            'payout.required_if'    =>  'This Field is required',
            'pf.required_if'   =>  'This Field is required',
            'emi.required_if'   =>  'This Field is required',
            'monthAddCharge.required_if'    =>  'This Field is required',    
            'sd.required_if'   =>  'This Field is required',
            'self_finance_company.required_if'  =>  'This Field is required',
            'self_finance_amount.required_if'    =>  'This Field is required',
            'self_finance_amount.gt'    =>  'This Field must be greater than 0.',
            // 'self_finance_bank.required_if' =>  'This Field is required',
            'ex_showroom_price.required' =>  'This Field is required',
            'ex_showroom_price.gt' =>  'Please Enter Correction Selling Price',
            'self_assited.required' =>  'This Field is required',
            'handling_charge.required' =>  'This Field is required',
            'handling_charge.gt' =>  'This Field must be greater than 0',
            'handling_charge.gte' =>  'This Field must be greater than equal 0',
            'self_assited_ins.required' =>  'This Field is required',
            'insur_c.required'    =>  'Insurance Company Field is required',
            'zero_dep_cost.required_if'    =>  'This Field is required',
            'zero_dep_cost.gt'    =>  'This Field must be greater than 0',
            'zero_dep_cost.gte'    =>  'This Field must be greater than equal 0',
            'comprehensive_cost.required_if'    =>  'This Field is required',
            'comprehensive_cost.gt'    =>  'This Field must be greater than 0',
            'comprehensive_cost.gte'    =>  'This Field must be greater than equal 0',
            'ltp_5_yr_cost.required_if'    =>  'This Field is required',
            'ltp_5_yr_cost.gt'    =>  'This Field must be greater than 0',
            'ltp_5_yr_cost.gte'    =>  'This Field must be greater than equal 0',
            'ltp_zero_dep_cost.required_if' =>  'This Field is required',
            'ltp_zero_dep_cost.gt'    =>  'This Field must be greater than 0',
            'ltp_zero_dep_cost.gte'    =>  'This Field must be greater than equal 0',
            'cpa_company.required_if'    =>  'This Field is required',
            'cpa_duration.required_if'  =>  'This Field is required',
            'cpa_cover_cost.required_if' =>  'This Field is required',
            'cpa_cover_cost.gt'    =>  'This Field must be greater than 0',
            'hypo_cost.required_unless' =>  'This Field is required',
            'pollution_cert_cost.required_if'   =>  'This Field is required',
            'pollution_cert_cost.gt'    =>  'This Field must be greater than 0',
            'amc_cost.required_if'   =>  'This Field is required',
            'amc_cost.gt'    =>  'This Field must be greater than 0',
            'ew_duration.required_if'   =>  'This Field is required',
            'ew_duration.gt'    =>  ' EW Duration must be greater than 0',
            'ew_cost.required_if'   =>  'This Field is required',
            'ew_cost.gt'    =>  'This Field must be greater than 0',
            'hjc_cost.required_if'   =>  'This Field is required',
            'hjc_cost.gt'    =>  'This Field must be greater than 0',
            'discount.required' =>  'This Field is required',
            'discount_amount.required'   =>  'This Field is required',
            'scheme.required_if'   =>  'This Field is required',
            'scheme_remark.required_if'    =>  'This Field is required',
            'total_calculation.required' =>  'This Field is required',
            'total_calculation.in' =>  'total amount will be equal to whole amount which you have entered',
            'balance'   =>  'This Feild is required',
            'balance.lte'   =>  'this price must be less to total amount'

        ]));
        // echo '<pre>';
        //  print_r($request->input());die;
        try{
            
            $customerId = intval($request->input('select_customer_name'));
            $saleCust = Sale::where('customer_id',$customerId)->first();
            $cust = Customers::where('id',$customerId)->first();
            $customer_name = $cust->name;
                
                $data  = [
                    // 'name'=>$request->input('name'),
                    'mobile'=>$request->input('mobile'),
                    'email_id'=>$request->input('email'),
                    'relation_type'=>$request->input('relation_type'),
                    'relation'=>$request->input('relation'),
                    'country'=>$request->input('country'),
                    'state'=>$request->input('state'),
                    'city'=>$request->input('city'),
                    'address'=>$request->input('address'),
                    'pin_code'=>$request->input('pin'),
                ];
                $checkCustomer = CustomHelpers::checkCustomer($customerId,$request->input('aadhar'),$request->input('voter'));
                // print_r($checkCustomer);die;
                if(empty($saleCust))
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
                        $checkAadhar = CustomHelpers::checkCustomer($customerId,$request->input('aadhar'),null);
                        if(!empty($checkAadhar))
                        {
                            return back()->with('error','Error, Cutomer Already Exist for this Aadhar Number.')->withInput();
                        }
                        $data = array_merge($data,['aadhar_id'=> $request->input('aadhar')]);
                    }
                    if(empty($cust->voter_id))
                    {
                        $checkVoter = CustomHelpers::checkCustomer($customerId,$request->input('aadhar'),$request->input('voter'));
                        if(!empty($checkVoter))
                        {
                            return back()->with('error','Error, Cutomer Already Exist for this Voter Number.')->withInput();
                        }
                        $data = array_merge($data,['voter_id'=> $request->input('voter')]);
                    }
                }
            
                $customer = Customers::where('id',$customerId)->update($data);
                
                if($customer)
                {
                    $customer_details = DB::table('customer_details')->insertGetId([
                        'customer_id'   =>  $customerId,
                        'relation_type' =>  $cust->relation_type,
                        'relation' =>  $cust->relation,
                        'email_id'=>$cust->email_id,
                        'mobile'=>$cust->mobile,
                        'country'=>$cust->country,
                        'state'=>$cust->state,
                        'city'=>$cust->city,
                        'address'=>$cust->address,
                        'pin_code'=>$cust->pin_code,
                    ]);
                }
                

            $balance = round($request->input('balance'));
            if($request->input('discount') == 'normal' && $request->input('discount_amount') >= 1000)
            {
                $balance = round($balance+$request->input('discount_amount'));
            }
            //   print_r($request->input());die;
            $saleInsert = [
                        'sale_executive'    =>  $request->input('sale_executive'),
                        'product_id'  =>  $request->input('prod_name'),
                        'sale_type'    =>  $request->input('sale_type'),
                        'store_id'  =>  $request->input('store_name'),
                        'ref_name'  =>  $request->input('reference'),
                        'ref_relation'  =>  $request->input('ref_relation'),
                        'ref_mobile'  =>  $request->input('ref_mobile'),
                        'customer_id'  =>  $customerId,
                        'customer_pay_type'  =>  $request->input('customer_pay_type'),
                        'tos'  =>  'bestdeal',
                        'ex_showroom_price'  =>  $request->input('ex_showroom_price'),
                        'hypo'  =>  ($request->input('customer_pay_type') != 'cash') ? $request->input('hypo_cost') : null,
                        'total_amount'  =>  round($request->input('total_calculation')),
                        'balance'  =>  $balance
                                
            ];
        
            $saleInsertId = Sale::where('id',$sale_id)->update($saleInsert);
            $saleInsertId = $sale_id;
            $sale_rto_type = 'normal';
            if($request->input('self_assited') == 'self'){
                $sale_rto_type = 'self';
            }
            // print_r($request->input('handling_charge'));die;
            // insert sale rto transfer charges
            $sale_rto_update = SaleRto::where('sale_id',$sale_id)->update([
                'sale_id'   =>  $saleInsertId,
                'handling_charge'   =>  $request->input('handling_charge'),
                'rto'   =>  $sale_rto_type
            ]);

            //type of sale

            // discount update
            $discount_data = [
                'type'  =>  'sale',
                'type_id'   =>  $sale_id,
                'discount_type'  =>  $request->input('discount'),
                'scheme_id'  =>  ($request->input('discount') == 'scheme') ? $request->input('scheme') : 0,
                'remark'  =>  ($request->input('discount') == 'scheme') ? $request->input('scheme_remark') : null,
                'amount'  =>  $request->input('discount_amount')
            ];
            if($request->input('discount') == 'normal' && $request->input('discount_amount') >= 1000)
            {
                $discount_data = array_merge($discount_data,['status' => 'pending']);
            }
            $discount  =  SaleDiscount::where('type_id',$sale_id)
                                        ->whereIn('discount_type',array('Normal','Scheme'))
                                        ->update($discount_data);
            if($request->input('sale_type') == 'corporate')
            {
                $discount  =  SaleDiscount::where('type_id',$sale_id)
                            ->where('discount_type','CSD')->update([
                    'type'   =>  'sale',
                    'type_id'   =>  $sale_id,
                    'discount_type' =>  'csd',
                    'amount'    =>  $csd
                ]);
            }

             // if normal discount is more than 0 then required to approval so, insert in approval request 
            // but check it when discount type is NORMAL
            // if($request->input('discount') == 'normal' && $request->input('discount_amount') >= 1000)
            // {
            //     $insert_approval = CustomHelpers::sale_disocunt_insert_approval($discount,$request->input('store_name'));
            // }
            
            // insert in insurance_data
            $insurance_data = [
                'store_id'  =>  $saleInsert['store_id'],
                'sale_id'   =>  $saleInsertId,
                'customer_id'   =>  $saleInsert['customer_id'],
                'type'  =>  'new'
            ];
            $old_insur_data = InsuranceData::where('customer_id',$saleInsert['customer_id'])->first();
            $insur_data_id = 0;
            if(isset($old_insur_data->id)){
                $insur_data_id = $old_insur_data->id;
            }else{
                $insur_data_id = InsuranceData::insertGetId($insurance_data);
            }
            //insert insurance
            $od = 0;
            $tp = 0;
            $insur_am = 0;
            if($request->input('s_insurance') == 'zero_dep')
            {
                $od = 1;$tp = 5;
                $insur_am = $request->input('zero_dep_cost');
            }elseif($request->input('s_insurance') == 'comprehensive'){
                $od = 1;$tp = 5;
                $insur_am = $request->input('comprehensive_cost');
            }elseif($request->input('s_insurance') == 'ltp_5_yr'){
                $od = 5;$tp = 5;
                $insur_am = $request->input('ltp_5_yr_cost');
            }
            elseif($request->input('s_insurance') == 'ltp_zero_dep'){
                $od = 5;$tp = 5;
                $insur_am = $request->input('ltp_zero_dep_cost');
            }
            $self_insurance = 1;
            if($request->input('self_assited_ins') != 'self'){
                $self_insurance = 0;
            }
            $old_od_data = Insurance::where('sale_id',$saleInsertId)->where('type','new')->where('status','Done')
                            ->where('insurance_type','OD')->first();
            $old_tp_data = Insurance::where('sale_id',$saleInsertId)->where('type','new')->where('status','Done')
                            ->where('insurance_type','TP')->first();
            if(!isset($old_od_data->id) || !isset($old_tp_data->id)){
                DB::rollback();
                return back()->with('error','Insurance Record Not Found.')->withInput();
            }
            $common_ins_data = [
                'customer_id'   =>  $customerId,
                'sale_id'   =>  $sale_id,
                'insurance_co'  =>  $request->input('insur_c'),
                'insurance_amount'  =>  $insur_am,
                'insurance_name'    =>  $request->input('s_insurance'),
                'type'  =>  'new',
                'start_date'    =>  $old_od_data->start_date,
                'insurance_date'    =>  $old_od_data->insurance_date,
                'status'    =>  'Done',
                'insurance_data_id' =>  $insur_data_id,
                'self_insurance'    =>  $self_insurance
            ];
            $od_ins_data = array_merge($common_ins_data,[
                'policy_tenure' =>  $od,
                'insurance_type'    =>  'OD'
            ]);
            if($request->input('cpa_cover'))
            {
                $cpa_company  =  $request->input('cpa_company');
                $cpa_amount  =  $request->input('cpa_cover_cost');
                $cpa_duration  = $request->input('cpa_duration');
                $od_ins_data = array_merge($od_ins_data,[
                    'cpa_company'   =>  $cpa_company,
                    'cpa_tenure'   =>  $cpa_duration,
                    'cpa_amount'   =>  $cpa_amount
                ]);
            }
            // third party data insert
            $tp_ins_data = array_merge($common_ins_data,[
                'policy_tenure' =>  $tp,
                'insurance_type'    =>  'TP'
            ]);
            $ins_od_insurance = Insurance::where('id',$old_od_data->id)
                                    ->where('insurance_type','OD')->update($od_ins_data);
            $ins_tp_insurance = Insurance::where('id',$old_tp_data->id)
                                    ->where('insurance_type','TP')->update($tp_ins_data);
        
            // finance 

            $checkfinance = DB::table('sale_finance_info')->where('sale_id',$sale_id)->first();
            // die;
            if($request->input('customer_pay_type') == 'finance')
            {
                $finance = array(
                    'sale_id'    =>  $sale_id,
                    'name'  =>  $request->input('finance_name'),
                    'company'  =>  $request->input('finance_company_name'),
                    'loan_amount'  =>  $request->input('loan_amount'),
                    'do'  =>  $request->input('do'),
                    'dp'  =>  $request->input('dp'),
                    'los'  =>  $request->input('los'),
                    'roi'  =>  $request->input('roi'),
                    'payout'  =>  $request->input('payout'),
                    'pf'  =>  $request->input('pf'),
                    'emi'  =>  $request->input('emi'),
                    'mac'  =>  $request->input('monthAddCharge'),
                    'sd'  =>  $request->input('sd')
                );
                if(empty($checkfinance))
                    {
                        $financeInsert = SaleFinance::insertGetId($finance);
                    }
                    elseif(!empty($checkfinance))
                    {
                        $financeInsert = SaleFinance::where('sale_id',$sale_id)->update($finance);

                    }
            }
            elseif($request->input('customer_pay_type') == 'self_finance')
            {
                $self_finance = array(
                    'sale_id'    =>  $sale_id,
                    'company'  =>  $request->input('self_finance_company'),
                    'loan_amount'  =>  $request->input('self_finance_amount'),
                    // 'bank_name'  =>  $request->input('self_finance_bank'),
                    
                );
                if(empty($checkfinance))
                    {
                        $self_financeInsert = SaleFinance::insertGetId($self_finance);
                    }
                    elseif(!empty($checkfinance))
                    {
                        $self_financeInsert = SaleFinance::where('sale_id',$sale_id)->update($self_finance);

                    }
            }
            elseif($request->input('customer_pay_type') == 'cash')
            {
                if(!empty($checkfinance))
                {
                    DB::table('sale_finance_info')->where('sale_id',$sale_id)->delete();
                }
            }  
            
            $sale_date = $request->input('sale_date');
            $sale_id = $sale_id;

            // update Polllution Certificate
            $check_pc = PollutionCertificate::where('sale_id',$sale_id)->first();
            if($request->input('pollution_cert'))
            {
                $duration = Settings::where('name','PollutionCertificateDuration')->first();
                if(!isset($duration->value)){
                    DB::rollBack();
                    return back()->with('error','Pollution Certificate Duration Master Not Found.')->withInput();
                }
                $start_date = $sale_date;
                $end_date = date('Y-m-d', strtotime('+'.$duration->value.' year',strtotime($start_date)));
                $pc_data = [
                    'sale_id'   =>  $sale_id,
                    'start_date'    =>  $start_date,
                    'end_date'  =>  $end_date,
                    'amount'    =>  $request->input('pollution_cert_cost')
                ];
                if(isset($check_pc->id))
                {
                    PollutionCertificate::where('id',$check_pc->id)->update($pc_data);
                }else{
                    $pc = PollutionCertificate::insertGetId($pc_data);
                }
            }
            else
            {
                if(isset($check_pc->id))
                {
                    PollutionCertificate::where('id',$check_pc->id)->delete();
                }
            }
            // update amc
            $check_amc = AMC::where('sale_id',$sale_id)->first();
            if($request->input('amc'))
            {
                $service_allowed = Settings::where('name','AMCServiceAllowed')->first();
                if(!isset($service_allowed->value)){
                    DB::rollBack();
                    return back()->with('error','AMC Master Not Found.')->withInput();
                }
                $start_date = date('Y-m-d',strtotime('+1 year',strtotime($sale_date)));
                $end_date = date('Y-m-d', strtotime('+1 year',strtotime($start_date)));
                $amc_data = [
                    'sale_id'   =>  $sale_id,
                    'start_date'    =>  $start_date,
                    'end_date'  =>  $end_date,
                    'service_allowed'   =>  $service_allowed->value,
                    'amount'    =>  $request->input('amc_cost')
                ];
                if(isset($check_amc->id))
                {
                    AMC::where('id',$check_amc->id)->update($amc_data);
                }else{
                    $amc = AMC::insertGetId($amc_data);
                }
            }
            else
            {
                if(isset($check_amc->id))
                {
                    AMC::where('id',$check_amc->id)->delete();
                }
            }

            // create ew
            $check_ew = ExtendedWarranty::where('sale_id',$sale_id)->first();
            if($request->input('ew'))
            {
                $ew_year = $request->input('ew_duration');
                $product_info = ProductModel::where('id',$request->input('prod_name'))
                                ->select('id','st_warranty_duration','model_category')
                                ->first();
                $prod_duration = $product_info->st_warranty_duration;
                $start_date = date('Y-m-d',strtotime('+'.$prod_duration.' year',strtotime($sale_date)));
                $end_date = date('Y-m-d', strtotime('+'.$ew_year.' year',strtotime($start_date)));

                // get km
                $find_km = EwMaster::where('duration',$ew_year)
                                    ->where('category',$product_info->model_category)
                                    ->first();
                if(!isset($find_km->id)){
                    DB::rollback();
                    return back()->with('error','Extended Warranty Master Not Found.')->withInput();
                }
                $ew = [
                    'sale_id'   =>  $sale_id,
                    'start_date'    =>  $start_date,
                    'end_date'  =>  $end_date,
                    'km'    =>  $find_km->km,
                    'amount'    =>  $request->input('ew_cost')
                ];
                // print_r($ew);die;
                if(isset($check_ew->id))
                {
                    ExtendedWarranty::where('id',$check_ew->id)->update($ew);
                }else{
                    $ew = ExtendedWarranty::insertGetId($ew);
                }
                
            }
            else{
                if(isset($check_ew->id))
                {
                    ExtendedWarranty::where('id',$check_ew->id)->delete();
                }
            }
            /// create hjc
            $check_hjc = HJCModel::where('sale_id',$sale_id)->first();

            if($request->input('hjc'))
            {
                $start_date = date('Y-m-d',strtotime('+1 year',strtotime($sale_date)));
                $end_date = date('Y-m-d', strtotime('+1 year',strtotime($start_date)));
                $hjc = [
                    'sale_id'   =>  $sale_id,
                    'start_date'    =>  $start_date,
                    'end_date'  =>  $end_date,
                    'amount'    =>  $request->input('hjc_cost')
                ];
                if(isset($check_hjc->id))
                {
                    HJCModel::where('id',$check_hjc->id)->update($hjc);
                }else{
                    $hjc = HJCModel::insertGetId($hjc);
                }
            }else
            {
                if(isset($check_hjc->id))
                {
                    HJCModel::where('id',$check_hjc->id)->delete();
                }
            }

            // update in payment request
            $payment_req = PaymentRequest::where('type','sale')->where('type_id',$sale_id)->update([
                'store_id'  =>  $saleInsert['store_id'],
                'type'  =>  'sale',
                'type_id'   =>  $sale_id,
                'amount'    =>  round($request->input('balance')),
                'status'    =>  'pending'
            ]);

            // update in rto
            $rtodata = RtoModel::where('sale_id',$sale_id)->update(
                [
                    'sale_id'=> $sale_id,
                    'customer_id'=>$customerId,
                    'approve'   =>  1,
                    'rto_amount'    =>  2500,
                    'file_uploaded' =>  1,
                    'created_by' => Auth::id()
                ]
            );

        }catch(\Illuminate\Database\QueryException $ex) {
        DB::rollback();
            //print_r($ex->getMessage());die;
            return back()->with('error','Something went wrong.'.$ex->getMessage())->withInput();
        }
        CustomHelpers::userActionLog($action='Update BestDeal Sale',$sale_id,$customerId);

        DB::commit();
        return redirect('/admin/sale/best/deal/inventory')->with('success','Sale Successfully Updated.');
        
    }


    // update bestdeal sale document page
    public function update_document($id) {

        $saleData = Sale::leftJoin('store','sale.store_id','store.id')->where('sale.tos','bestdeal')
                            ->where('sale.id',$id)
                            ->select('sale.*')
                            ->get()
                            ->first();
        $paid_amount = DB::table('payment')->where('type_id',$id)->where('status','received')->where('type','sale')->sum('amount');
        if($saleData->balance > $paid_amount && $saleData->pay_later == 0)
        {
            return back()->with('error','Please Firstly Pay full payment with Confirmation Payment');
        }
        $old_data = BestDealSaleDocument::where('sale_id',$saleData->id)->first();
        if(!isset($old_data['id']))
        {
            return back()->with('error','Error, Firstly Create That Page !!');
        }
        $data = array(
            'saleData' => $saleData, 
            'data' => $old_data,
            'layout' => 'layouts.main'
        );
         return view('admin.bestdeal.update_document_page',$data);
        
    }
    public function update_document_db(Request $request)
    {

        $this->validate($request,[     
            'form29'=>'required',
            'form30'=>'required',
            'photo'=>'required',
            'impression'=>'required',
            'aadhar'=>'required',
            'pendency'=>'required'
        ],[
            'form29.required'    =>  'This Field is required',
            'form30.required'    =>  'This Field is required',
            'photo.required'    =>  'This Field is required',
            'impression.required'    =>  'This Field is required',
            'aadhar.required'    =>  'This Field is required',
            'pendency.required'    =>  'This Field is required'

        ]);

        try{
            $sale_id = $request->input('sale_id');
            $store_id = $request->input('store_id');

            $find = BestDealSaleDocument::where('sale_id',$sale_id)
                                            ->first();
            if(isset($find->id))
            {
                $data = [
                    'form29'    =>  $request->input('form29'),
                    'form30'    =>  $request->input('form30'),
                    'impression'    =>  $request->input('impression'),
                    'photo'    =>  $request->input('photo'),
                    'aadhar'    =>  $request->input('aadhar'),
                    'pendency'    =>  $request->input('pendency'),
                ];
                $update = BestDealSaleDocument::where('sale_id',$sale_id)
                                                ->update($data);
            }
            else{
                return back()->with('error','Error, Firstly Create That Page For This Sale Number !!')->withInput();
            }

        }catch(\Illuminate\Database\QueryException $ex) {
        DB::rollback();
            //print_r($ex->getMessage());die;
            return back()->with('error','Something went wrong.'.$ex->getMessage())->withInput();
        }
        DB::commit();
        return redirect('/admin/bestdeal/update/sale/document/'.$sale_id)->with('success','Successfully Updated !!');

    }

    // update bestdeal sale hirise page
    public function updateHirise($id)
    {
        $saleData = Sale::where("id",$id)->where('tos','bestdeal')->get()->first();
        $hiriseData = Hirise::where("sale_id",$id)->get()->first();
        // $extendedWarrantyData = ExtendedWarranty::where("sale_id",$id)->get()->first();
        if(!isset($saleData->id) || !isset($hiriseData->id))
        {
            return back()->with('error','Error, Firstly Create Hirise Page For This Sale Number !!')->withInput();
        }
        $data  = array(
           'saleData' => $saleData, 
           'hiriseData' => $hiriseData,
        //    'extendedWarrantyData' => $extendedWarrantyData,/
           'layout' => 'layouts.main', 
       );
       return view('admin.bestdeal.update_hirise',$data);
    }
    public function updateHirise_db(Request $request)
    {
        //print_r($request->input());die();
        try {
            $this->validate($request,[
                'sale_id'=>'required',
                'name'=>'required_if:hirise,yes',
                'mobile'=>'required_if:hirise,yes',
                'address'=>'required_if:hirise,yes',
                'wing_invoice'=>'required_if:wings,yes',
            ],[
                'name.required'=> 'The Name field is required when Hirise Details Complete is yes.', 
                'mobile.required'=> 'The Name field is required when Hirise Details Complete is yes.', 
                'address.required'=> 'The Name field is required when Hirise Details Complete is yes.', 
                'wing_invoice.required'=> 'The Invoice field is required when Wings Billing Complete is yes.'
            ]);
            $getdata = Hirise::where('sale_id', $request->input('sale_id'))->first();
            DB::beginTransaction();
            if (isset($getdata->id)) {

                // if(!empty($getdata->name) && !empty($getdata->wings_invoice))
                // {
                //     return back()->with('error','You are not authorized for update');
                // }

                if($request->input('hirise') == 'yes')
                {
                    if(empty($getdata->name))
                    {
                        $hirisedata =   Hirise::where('id',$getdata->id)
                                            ->update([
                                                'name'=>$request->input('name'),
                                                'mobile'=>$request->input('mobile'),
                                                'address'=>$request->input('address')
                                            ]);
                    }
                }
                if($request->input('wings') == 'yes')
                {
                    if(empty($getdata->name))
                    {
                        $hirisedata =   Hirise::where('id',$getdata->id)
                                            ->update([
                                                'wings_invoice' =>  $request->input('wing_invoice')
                                            ]);
                    }
                }
            }
            else{
                return back()->with('error','Error, Firstly Create Hirise Page For This Sale Number !!')->withInput();
            }   
        
        }  catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return redirect('/admin/bestdeal/update/sale/hirise/'.$request->input('sale_id'))->with('error','some error occurred'.$ex->getMessage())->withInput();
        }
        DB::commit();
        return redirect('/admin/bestdeal/update/sale/hirise/'.$request->input('sale_id'))->with('success','Successfully Updated.');
    
    }
    // update bestdeal sale RTO
    public function updateRto($id) {

        $bookingData = Sale::where('sale.tos','bestdeal')
                            ->where('sale.id',$id)->where('sale.tos','bestdeal')
                            ->leftJoin('customer','sale.customer_id','customer.id')
                            ->select('sale.*','customer.name as customer_name')
                            ->first();
        $rtoData = RtoModel::where("sale_id",$id)
                            ->leftJoin('rto_summary','rto.id','rto_summary.rto_id')
                            ->select('rto.*','rto.amount as actual_amount')
                            ->first();
        if(!isset($bookingData->id) || !isset($rtoData->id))
        {
            return back()->with('error','Error, Firstly Create RTO Page For This Sale Number !!')->withInput();
        }
         $data  = array(
            'bookingData' => $bookingData, 
            'rtoData' => $rtoData,
            'layout' => 'layouts.main', 
        );
        return view('admin.bestdeal.update_rto',$data);
    }

    public function updateRto_db(Request $request) {
        //print_r($request->input());die();
        try {
            $this->validate($request,[
                'rto_trans_status'=>'required',
                'rto_amount'=>'required_if:rto_trans_status,yes'
            ],[
                'rto_amount.required_if'=> 'This is required.',
                'rto_trans_status.required'=> 'This is required.'
            ]);
            DB::beginTransaction();
            // $customerData = Customers::where('id',$request->input('customer_id'))->get()->first();
            $getdata = RtoModel::where('sale_id', $request->input('sale_id'))->get('id')->first();
            if ($getdata['id'] > 0) {
                $rtodata = RtoModel::where('id',$getdata['id'])->update(
                    [
                        'amount'=>$request->input('rto_amount'),
                        'rto_trans_status'  =>  $request->input('rto_trans_status')
                    ]
                );
            }

            if($rtodata==NULL) {
                    DB::rollback();
                   return redirect('/admin/bestdeal/update/sale/rto/'.$request->input('sale_id'))->with('error','some error occurred');
            } else{
                DB::commit();
                  return redirect('/admin/bestdeal/update/sale/rto/'.$request->input('sale_id'))->with('success','Successfully Updated .');
             }    
        
        }  catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return redirect('/admin/bestdeal/update/sale/rto/'.$request->input('sale_id'))->with('error','some error occurred'.$ex->getMessage());
        }
    }

    // update bestdeal sale pendign item's
    public function updatePendingItem(Request $request,$id)
    {
        
        $checkPendingItem = Sale::where('id',$id)->where('tos','bestdeal')
                            ->first();

        // print_r($checkOrder);die;
        if(empty($checkPendingItem->pending_item))
        {
            return back()->with('error','Error, Please Fill Pending Form Firstly.');
        }
        $sale = $checkPendingItem;

        $accessories = OtcSale::where('otc_sale.sale_id',$id)
                        ->leftJoin('otc_sale_detail','otc_sale_detail.otc_sale_id','otc_sale.id')
                        ->where('otc_sale_detail.type','Part')
                        ->leftJoin('part',function($join){
                            $join->on('part.id','otc_sale_detail.part_id');
                        })
                        ->select('otc_sale_detail.id',
                            'otc_sale.sale_id',    
                            'otc_sale_detail.part_id',
                            'part.name',
                            DB::raw('IF(otc_sale_detail.part_id = 0, otc_sale_detail.part_desc,part.name) as accessories_name'),
                            DB::raw('IF(otc_sale_detail.part_id = 0, "1",otc_sale_detail.qty) as qty'), 
                            'otc_sale_detail.amount'
                        )
                        ->get();
        $other_item = ['Sale Invoice','Service Book','Bag','Key Ring','Warranty Card'];
        // print_r($accessories->toArray());die;
        $pending_item = OrderPendingModel::where('sale_id',$id)->first();

        // $pending_item = array();
        // $pending_other = array();
       
        $pending_item_all = (($pending_item)? $pending_item->toArray() : array());
        $pending_item = (($pending_item_all)?$pending_item_all['accessories_id'] : '');
        $pending_item = (($pending_item)? explode(',',$pending_item) : array() );
        // print_r($pending_item);die;
        $pending_other = (($pending_item_all)?$pending_item_all['other']: '');
        $pending_other = (($pending_other)? explode(',',$pending_other) : array() );

        //    $fuel = FuelModel::where('type_id',$id)->where('type','sale')->where('store_id',$sale['store_id'])->first();
        //  print_r($sale->pending_item);die;
        // print_r($pending_other);die;
        $data = array(
            'sale_no'   =>  $sale->sale_no,
            'select_pending_item'  =>  $sale->pending_item,
            'accessories' => $accessories,
            'pending_item' => $pending_item,    
            'pending_other' => $pending_other,    
            'other_item' => $other_item, 
            // 'fuel' => $fuel,   
            'sale_id' => $id,    
            'layout' => 'layouts.main'
        );
        return view('admin.bestdeal.pendingItemUpdate',$data);
    }
    public function updatePendingItem_db(Request $request) {
        //print_r($request->input());die();
         try {
            $validator = Validator::make($request->all(),[
                'select_pending_item'   =>  'required',
            ],
            [
                'select_pending_item.required'  =>  'This Field is required',
            ]);
            $data[0] = $request->input('select_pending_item');
            $data[1] = $request->input('pending_item');
            $data[2] = $request->input('other');

            $validator->after(function ($validator) use($data) {
                if ($data[0] == 'yes') {
                    if(empty($data[1]) && empty($data[2]))
                    {
                        $validator->errors()->add('pending_item', 'Any One Field is required, If you have to Choose Yes.')
                        ->add('other', 'Any One Field is required, If you have to Choose Yes.');
                    }
                }
            });

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            // print_r($request->input());die;
            DB::beginTransaction();
            // print_r($request->input());die;
            // if($request->input('select_pending_item') == 'yes')
            // {
                $sale_id = $request->input('sale_id');
                // echo $sale_id;die;
                $prePendingCheck = Sale::where('id',$sale_id)->where('tos','bestdeal')->select('store_id','pending_item')->first();
               // print_r($prePendingCheck);die();
                if(empty($prePendingCheck->pending_item))
                {
                    return back()->with('error','Error, Please Fill Pending Item Firstly.');
                }
            $check = OrderPendingModel::where('sale_id',$sale_id)->first();
            
            if($request->input('select_pending_item') == 'yes')
            {
                // if($prePendingCheck->pending_item == $request->input('select_pending_item'))
                // {

                // }
                // $fuel = FuelModel::where('type_id',$sale_id)->where('type','sale')->first();
                // if ($fuel) {
                //     $updatefuel = FuelModel::where('type_id',$sale_id)->where('type','Sale')->where('status','pending')->update([
                //         'quantity' => $request->input('fuel')
                //     ]);
                // }
                if (!empty($request->input('pending_item'))) {
                        $acc = implode(',', $request->input('pending_item'));
                }else{
                    $acc = '';
                }
                if (!empty($request->input('other'))) {
                        $oth = implode(',', $request->input('other'));
                }else{
                    $oth = '';
                }
                // $check = OrderPendingModel::where('sale_id',$sale_id)->first();
                if($check)
                {
                    $update = OrderPendingModel::where('sale_id',$sale_id)
                                ->update([
                                    'accessories_id'    =>  $acc,
                                    'other' =>  $oth
                                ]);
                                
                }
                else{
                    $insert = OrderPendingModel::insertGetId([
                        'sale_id'   =>  $sale_id,
                        'accessories_id'    =>  $acc,
                        'other' =>  $oth
                    ]);
                }
                $update = Sale::where('id',$sale_id)->update(['pending_item' => 'yes']);
            }
            else{
                if($check)
                {
                    $del = OrderPendingModel::where('sale_id',$sale_id)
                            ->delete();
                }
                $update = Sale::where('id',$sale_id)->update(['pending_item' => 'no']);
            }
           
        }  catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return redirect('/admin/bestdeal/update/sale/pending/item/'.$request->input('sale_id'))->with('error','some error occurred'.$ex->getMessage());
        }
        DB::commit();
        return redirect('/admin/bestdeal/update/sale/pending/item/'.$request->input('sale_id'))->with('success','Successfully Updated');

    }

    // bestdeal sale view

    public function bestdeal_sale_view($id) {
        $saleData = Sale::select("sale.*","booking.booking_number","rto.rto_amount","rto.rc_number",
            'rto.amount as actual_amount','rto.rto_trans_status',
            "rto.rto_finance","rto.application_number","rto.rto_type","rto.registration_number",
            "rto.penalty_charge","rto.file_submission","rto.approve","rto.approval_date","rto.file_uploaded",
            "rto.uploaded_date","rto.front_lid","rto.rear_lid","rto.receiving_date","hirise.invoice",
            "hirise.amount",'hirise.name as hirise_name','hirise.mobile as hirise_mobile','hirise.address as hirise_address',
            'hirise.wings_invoice',
            "insurance.policy_number",DB::raw("(select name from insurance_company where id = insurance.insurance_co) as insurance_co"),
            "insurance.insurance_type","insurance.insurance_amount","customer.name","customer.relation_type",
            "customer.relation","customer.aadhar_id","customer.voter_id","customer.pin_code",
            "customer.reference","customer.address","customer.mobile","customer.email_id",
            DB::raw('concat(product.model_category,"-",product.model_name,"-",product.model_variant) as product_name'),
            "store.name as store_name","otc.amount as otc_amount","otc.date as otc_date",
            "otc.invoice_no as otc_invoice","order_pending_item.other as pending_other",
            "order_pending_item.accessories_id",
            // DB::raw('sum(payment.amount) as total_payment'),
            DB::raw('(select sum(payment.amount) from payment where sale_id = sale.id and type = "sale" and status <>  "cancelled") as total_payment '),
            'best_deal_sale.frame','best_deal_sale.register_no','bestdeal_sale_document.form29',
            'bestdeal_sale_document.form30','bestdeal_sale_document.photo','bestdeal_sale_document.impression',
            'bestdeal_sale_document.aadhar','bestdeal_sale_document.pendency','bestdeal_sale_document.created_at as document_date'
        )
        ->leftJoin("best_deal_sale",function($join){
            $join->on("best_deal_sale.sale_id","=","sale.id");
        })
        ->leftJoin("bestdeal_sale_document",function($join){
            $join->on("bestdeal_sale_document.sale_id","=","sale.id");
        })
         ->leftJoin("rto",function($join){
             $join->on("sale.id","=","rto.sale_id");
         })
         ->leftJoin("customer",function($join){
             $join->on("customer.id","=","sale.customer_id");
         })
         ->leftJoin("store",function($join){
             $join->on("sale.store_id","=","store.id");
         })
         ->leftJoin("product",function($join){
             $join->on("product.id","=","sale.product_id");
         })
         ->leftJoin("insurance",function($join){
             $join->on("insurance.sale_id","=","sale.id");
         })
         ->leftJoin("hirise",function($join){
             $join->on("hirise.sale_id","=","sale.id");
         })
         ->leftJoin("otc",function($join){
             $join->on("otc.sale_id","=","sale.id");
         })
         ->leftJoin("payment",function($join){
             $join->on("payment.sale_id","=","sale.id")
             ->where("payment.type","sale")
             ->where("payment.status","<>","cancelled");
         })
         ->leftJoin("order_pending_item",function($join){
             $join->on("order_pending_item.sale_id","=","sale.id")
             ->where("sale.pending_item","yes");
         })
          ->leftJoin("booking",function($join){
             $join->on("payment.booking_id","=","booking.id")
             ->where("payment.booking_id",'<>',null)
             ->where("payment.sale_id",'<>',null)
             ->where("payment.type","sale");
         })
         ->where('sale.id',$id)
         ->get()
         ->first();
 
         $payData = Payment::where('sale_id',$id)->where('type','sale')->get();
         $securityData = Payment::where('sale_id',$id)->where('type','security')->sum('security_amount');
         if ($saleData['accessories_id']) {
             $acc_id = explode(',', $saleData['accessories_id']);
             $accessories = Part::whereIn('id',$acc_id)->get();
         }else{
             $accessories = [];
         }
         
         $data = array(
             'saleData' => $saleData,
             'payData' => $payData,
             'accData' => $accessories,
             'securityData' => $securityData,
             'layout' => 'layouts.main'
         );
         return view('admin.bestdeal.bestdeal_sale_view',$data);
     }

    public function sale_exchange_list()
    {
        $checklist = BestDealMaster::all();
        $data = [
            'checklist' =>  $checklist,
            'layout' => 'layouts.main'
        ];
        
        return view('admin.bestdeal.sale_exchange_list',$data);
    }
    public function sale_exchange_list_api(Request $request) {
       
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;

        
          $api_data =Sale::leftJoin('product','sale.product_id','product.id')
                       ->leftjoin('best_deal_sale','sale.id','best_deal_sale.purchase_sale_id')
                       ->leftJoin('customer','sale.customer_id','customer.id')
                    ->where('best_deal_sale.tos','exchange')
                    ->where('sale.type_of_sale','2')
                
                    ->select('sale.id',
                    'product.model_name',
                    'product.model_variant',
                    'product.color_code',
                    'sale.exchange_model',
                    'sale.exchange_yom',
                    'sale.exchange_register_no',
                    'sale.exchange_value',
                    'customer.name',
                    'sale.status',
                    'sale.sale_no',
                    'sale.sale_date',
                    'best_deal_sale.frame',
                    'best_deal_sale.register_no'

            );
            
            // DB::enableQueryLog();
            if(!empty($serach_value))
            {
               $api_data->where(function($query) use ($serach_value){
                        $query->where('product.model_name','like',"%".$serach_value."%")
                        ->orwhere('product.model_variant','like',"%".$serach_value."%")
                        ->orwhere('product.color_code','like',"%".$serach_value."%")
                        ->orwhere('value','like',"%".$serach_value."%")
                        // ->orwhere('selling_price','like',"%".$serach_value."%")
                        ->orwhere('customer.name','like',"%".$serach_value."%")
                        ->orwhere('best_deal_sale.register_no','like',"%".$serach_value."%")
                        ->orwhere('best_deal_sale.frame','like',"%".$serach_value."%")
                        ->orwhere('sale.exchange_model','like',"%".$serach_value."%")
                        ->orwhere('sale.sale_no','like',"%".$serach_value."%")
                        ->orwhere('sale.sale_date','like',"%".$serach_value."%")
                        ->orwhere('sale.exchange_yom','like',"%".$serach_value."%")
                        ->orwhere('sale.exchange_register_no','like',"%".$serach_value."%")
                        ->orwhere('sale.exchange_value','like',"%".$serach_value."%");
                        
                    });
               
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                    'sale.sale_no',
                    'product.model_name',
                    'product.model_variant',
                    'product.color_code',
                    'sale.exchange_model',
                    'sale.exchange_yom',
                    'sale.exchange_register_no',
                    'sale.exchange_value',
                    'customer.name',
                    'sale.sale_date',
                    'best_deal_sale.frame',
                    'best_deal_sale.register_no',
                    'sale.status'
                ];
                
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('best_deal_sale.id','asc');   
        
        $count = $api_data->count();
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

}   

