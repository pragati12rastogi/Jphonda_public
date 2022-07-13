<?php

namespace App\Http\Controllers\front;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App;
use Illuminate\Routing\Route;
use App\Model\Job_card;
use App\Model\ServiceModel;
use App\Model\Insurance;
use App\Model\InsuranceBooking;
use App\Model\Sale;
use App\Model\RtoModel;
use App\Model\Hsrp;
use App\Model\SaleOrder;
use App\Model\EnquiryService;
use DB;
use \App\Model\Product;
use \App\Model\ProductModel;
use \App\Model\ProductImages;
use \App\Model\Settings;
use \App\Model\Subscribe;
use \App\Model\Scheme;



class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    
    public function index()
    {

        $product_setting = Settings::where('name','New_Product')->get()->first();
        $used_product_setting = Settings::where('name','Used_Product')->get()->first();
      
        $new_product=explode(",",$product_setting['value']);
        $used_products=explode(",",$used_product_setting['value']);
        $product = ProductModel::leftjoin('product_details','product_details.product_id','product.id')    
                          ->whereIn('product.id',$new_product)
                          ->leftJoin('product_images',function($join){
                                        $join->on('product_images.product_id','product.id')
                                        ->where('product_images.default_image','1');
                                })
                           ->leftJoin('product_images as img',function($join){
                                        $join->on('img.product_id','product.id')
                                        ->where('img.default_image','0');
                                })
                          ->select(['product_images.id as  product_images_id','product.*','product_images.*','product.id as product_id','product_details.id as product_details_id','product_details.frame','product_details.engine_number',
                                'img.image as product_notdefault_image'])->groupBy('product.id')->orderBy('product_images.default_image','desc')->get();

        $used_product = ProductModel::leftjoin('product_details','product_details.product_id','product.id')
                           ->leftJoin('product_images',function($join){
                                        $join->on('product_images.product_id','product.id')
                                        ->where('product_images.default_image','1');
                                })
                            ->leftJoin('product_images as img',function($join){
                                        $join->on('img.product_id','product.id')
                                        ->where('img.default_image','0');
                                })
                          ->whereIn('product.id',$used_products)
                          ->select(['product_images.id as  product_images_id','product.*','product_images.*','product.id as product_id','product_details.id as product_details_id','product_details.frame','product_details.engine_number',
                                'img.image as product_notdefault_image'])->groupBy('product.id')->orderBy('product_images.default_image','desc')->get();
                     
        $scheme = Scheme::get();
        $data=array(
            'product'=>$product,
            'used_product'=>$used_product,
            'scheme'=>$scheme,
            'layout'=>'front.layout'
        );
        return view('front.index', $data);  
    }

    public function checkServiceDueDate(Request $request)
    {
        $this->validate($request,[
            'sdd-mobile'=>'required|numeric|digits:10',
            'sdd-frame'=>'required_without_all:sdd-reg',
            'sdd-reg'=>'required_without_all:sdd-frame'
        ],[
            'sdd-mobile.required'=> 'This Field is required.',
            'sdd-mobile.numeric'=> 'This Field must be Numeric.',
            'sdd-mobile.digits'=> 'This Field Length must be 10.',
            'sdd-frame.required_without_all'=> 'This Field is required When Register # Not Fill.',
            'sdd-reg.required_without_all'=> 'This Field is required When Frame # Not Fill.'
        ]);


        try{
            DB::beginTransaction();

            $mobile = $request->input('sdd-mobile');
            $frame = $request->input('sdd-frame');
            $reg = $request->input('sdd-reg');
            $arr = [$frame,$reg];

            $find = ServiceModel::Join('job_card',function($join) use($arr){
                                    $join->on('job_card.service_id','service.id');
                                })
                                ->whereNotNull('job_card.tag')
                                ->select(
                                    'service.sale_date as sale_date',
                                    'job_card.*'
                                )
                                ->orderBy('job_card.created_at','desc');

            if(!empty($frame) && !empty($reg)){
                $find = $find->where('service.frame',$frame)
                                ->where('service.registration',$reg);
            }
            elseif(!empty($frame)){
                $find = $find->where('service.frame',$frame);
            }
            elseif(!empty($reg)){
                $find = $find->where('service.registration',$reg);
            }
            $find = $find->first();
            $next_date = '';
            if(isset($find->service_id))
            {
                // step 1 :- calculate next date using sub type service
                if($find->job_card_type == 'General')
                {
                    if($find->sub_type == 'Complimentary Service - Fs1 Punched' || $find->sub_type == 'Complimentary Service - Fs2 Punched' || $find->sub_type == 'Complimentary Service - Fs3 Punched' || $find->sub_type == 'Complimentary Service - Fs4 Punched')
                    {
                        $next_date = date('Y-m-d',strtotime('+1 months',strtotime($find->created_at)));
                    }
                }  

                // step 3 :- calculate next due date using last service date
                if(empty($next_date))
                {
                    $next_date = date('Y-m-d',strtotime('+1 months',strtotime($find->created_at)));
                }
            
                if(!empty($next_date))
                {
                    // insert enquiry service
                    $data = [
                        'type'  =>  'service_due_date',
                        'mobile'    =>  $mobile,
                        'frame' =>  $frame,
                        'reg_no'    =>  $reg
                    ];
                    $insert = EnquiryService::insertGetId($data);
                    
                    DB::commit();
                    return array(1,'Your Next Service Date is :- '.$next_date);
                }
                else{
                    DB::rollback();
                    return response()->json("We are not able to find next service date, please Contact Us.",422);
                }
            }
            else{
                DB::rollback();
                return response()->json("Enter Frame or Registration # Couldn't Find.",422);
            }
        }
        catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return response()->json('Something went wrong.'.$ex->getMessage(),422);
        }
        DB::rollback();
        return response()->json('Something went wrong.',422);

    }

    public function checkInsuranceDueDate(Request $request)
    {
        $this->validate($request,[
            'idd-mobile'=>'required|numeric|digits:10',
            'idd-frame'=>'required_without_all:idd-reg',
            'idd-reg'=>'required_without_all:idd-frame'
        ],[
            'idd-mobile.required'=> 'This Field is required.',
            'idd-mobile.numeric'=> 'This Field must be Numeric.',
            'idd-mobile.digits'=> 'This Field Length must be 10.',
            'idd-frame.required_without_all'=> 'This Field is required When Register # Not Fill.',
            'idd-reg.required_without_all'=> 'This Field is required When Frame # Not Fill.'
        ]);

        try{
            DB::beginTransaction();

            $mobile = $request->input('idd-mobile');
            $frame = $request->input('idd-frame');
            $reg = $request->input('idd-reg');
            $arr = [$frame,$reg];

            $find = Sale::leftJoin('rto',function($join) use($arr){
                                    $join->on('rto.sale_id','sale.id');
                                })
                                ->leftJoin('sale_order',function($join) use($arr){
                                    $join->on('sale_order.sale_id','sale.id');
                                })
                                ->join('insurance','insurance.sale_id','sale.id')
                                ->where('insurance.insurance_type','OD')
                                ->select(
                                    'sale_order.id as sale_order_id',
                                    'rto.id as rto_id',
                                    'insurance.policy_tenure as duration',
                                    'insurance.insurance_date as start_date'
                                )
                                ->orderBy('insurance.created_at','desc');

            if(!empty($frame) && !empty($reg)){
                $find = $find->where('sale_order.product_frame_number',$frame)
                                ->where('rto.registration_number',$reg);
            }
            elseif(!empty($frame)){
                $find = $find->where('sale_order.product_frame_number',$frame);
            }
            elseif(!empty($reg)){
                $find = $find->where('rto.registration_number',$reg);
            }
            $find = $find->first();
            
            if((isset($find->duration)) ? ((!empty($find->sale_order_id) || !empty($find->rto_id)) ? true : false ) : false )
            {
                // insert enquiry service
                $data = [
                    'type'  =>  'insurance_due_date',
                    'mobile'    =>  $mobile,
                    'frame' =>  $frame,
                    'reg_no'    =>  $reg
                ];
                $insert = EnquiryService::insertGetId($data);
                
                $duration = $find->duration;
                $date = $find->start_date;
                $end_date = date('Y-m-d',strtotime($duration.'year',strtotime($date)));
                DB::commit();
                return array(1,'Your Insurance Expiry Date is :- '.$end_date,$duration,$date,$end_date);
            }
            else{
                DB::rollback();
                return response()->json("Enter Frame or Registration # Couldn't Find.",422);
            }
        }
        catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return response()->json('Something went wrong.'.$ex->getMessage(),422);
        }
        DB::rollback();
        return response()->json('Something went wrong.',422);


    }

    public function regInsuranceBooking(Request $request)
    {
        
        $this->validate($request,[
            'ib-name'=>'required',
            'ib-mobile'=>'required|numeric|digits:10',
            'ib-frame'=>'required_without_all:ib-reg',
            'ib-reg'=>'required_without_all:ib-frame'
        ],[
            'ib-name.required'=> 'This Field is required.',
            'ib-mobile.required'=> 'This Field is required.',
            'ib-mobile.numeric'=> 'This Field must be Numeric.',
            'ib-mobile.digits'=> 'This Field Length must be 10.',
            'ib-frame.required_without_all'=> 'This Field is required When Register # Not Fill.',
            'ib-reg.required_without_all'=> 'This Field is required When Frame # Not Fill.'
        ]);
            
        try{
            DB::beginTransaction();

            $data = [
                'name'  =>  $request->input('ib-name'),
                'mobile'  =>  $request->input('ib-mobile'),
                'frame'  =>  $request->input('ib-frame'),
                'reg_no'  =>  $request->input('ib-reg')
            ];

            $insur_insert = InsuranceBooking::insertGetId($data);
            if(!$insur_insert)
            {
                DB::rollback();
                return response()->json('Something Went Wrong',401);
            }
        }
        catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return response()->json('Something went wrong.'.$ex->getMessage(),401);
        }
        DB::commit();
        return array(1,'Insurance booking done successfully. Please go to nearest "JPhonda" store for further processing.');
    }

    public function CheckRcPlate() {
        $data = [
            'layout'=>'front.layout'
        ];
        return view('front.check_rc_plate', $data);  
    }

    public function CheckRcStatus(Request $request) {
        $frame = $request->input('frame');
        $app_no = $request->input('application_number');
        if ($frame && empty($app_no)) {
            $data = SaleOrder::where('sale_order.product_frame_number', 'Like', "$frame")
                    ->leftJoin('sale','sale.id','sale_order.sale_id')
                    ->leftJoin('rto','rto.sale_id','sale_order.sale_id')
                    ->leftjoin('store','sale.store_id','store.id')
                    ->where('rto.main_type','rto')
                    ->select('rto.main_type','rto.registration_number','rto.application_number','rto.rc_number','rto.receiving_date','rto.rto_trans_status','rto.front_lid','rto.rear_lid','sale.sale_no','sale_order.product_frame_number as frame','rto.registration_number','rto.approve',DB::raw('concat(store.name,"-",store.store_type) as store_name'))
                    ->get()->first();
            if ($data == null) {
                $data = Hsrp::where('hsrp.frame', 'Like', "$frame")
                    ->leftJoin('rto','rto.type_id','hsrp.id')
                    ->leftjoin('store','hsrp.store_id','store.id')
                    ->where('rto.main_type','hsrp')
                    ->select('rto.main_type','rto.registration_number','rto.application_number','rto.rc_number','rto.receiving_date','rto.rto_trans_status','rto.front_lid','rto.rear_lid','hsrp.frame','rto.registration_number','rto.approve',DB::raw('concat(store.name,"-",store.store_type) as store_name'))
                    ->get()->first();
            }
        }
        if ($app_no && empty($frame)) {
         $data = SaleOrder::where('rto.application_number','Like',"$app_no")
                    ->leftJoin('sale','sale.id','sale_order.sale_id')
                    ->leftJoin('rto','rto.sale_id','sale_order.sale_id')
                    ->leftjoin('store','sale.store_id','store.id')
                    ->where('rto.main_type','rto')
                    ->select('rto.main_type','rto.registration_number','rto.application_number','rto.rc_number','rto.receiving_date','rto.rto_trans_status','rto.front_lid','rto.rear_lid','sale.sale_no','sale_order.product_frame_number as frame','rto.registration_number','rto.approve',DB::raw('concat(store.name,"-",store.store_type) as store_name'))
                    ->get()->first();
            if ($data == null) {
                $data = Hsrp::where('rto.application_number','Like',"$app_no")
                    ->leftJoin('rto','rto.type_id','hsrp.id')
                    ->leftjoin('store','hsrp.store_id','store.id')
                    ->where('rto.main_type','hsrp')
                    ->select('rto.main_type','rto.registration_number','rto.application_number','rto.rc_number','rto.receiving_date','rto.rto_trans_status','rto.front_lid','rto.rear_lid','hsrp.frame','rto.registration_number','rto.approve',DB::raw('concat(store.name,"-",store.store_type) as store_name'))
                    ->get()->first();
            }
        }
        if (!empty($frame) && !empty($app_no)) {
            $data = SaleOrder::where('sale_order.product_frame_number', 'Like', "$frame")
                    ->where('rto.application_number','Like',"$app_no")
                    ->leftJoin('sale','sale.id','sale_order.sale_id')
                    ->leftJoin('rto','rto.sale_id','sale_order.sale_id')
                    ->leftjoin('store','sale.store_id','store.id')
                    ->where('rto.main_type','rto')
                    ->select('rto.main_type','rto.registration_number','rto.application_number','rto.rc_number','rto.receiving_date','rto.rto_trans_status','rto.front_lid','rto.rear_lid','sale.sale_no','sale_order.product_frame_number as frame','rto.registration_number','rto.approve',DB::raw('concat(store.name,"-",store.store_type) as store_name'))
                    ->get()->first();
                if ($data == null) {
                    $data = Hsrp::where('hsrp.frame', 'Like', "$frame")
                    ->where('rto.application_number','Like',"$app_no")
                    ->leftJoin('rto','rto.type_id','hsrp.id')
                    ->leftjoin('store','hsrp.store_id','store.id')
                    ->where('rto.main_type','hsrp')
                    ->select('rto.main_type','rto.registration_number','rto.application_number','rto.rc_number','rto.receiving_date','rto.rto_trans_status','rto.front_lid','rto.rear_lid','hsrp.frame','rto.registration_number','rto.approve',DB::raw('concat(store.name,"-",store.store_type) as store_name'))
                    ->get()->first();
                }
        }

         return response()->json($data); 
        
    }

     public function ProductGetDetails(Request $request)
    {
        $product_id = $request->input('id');
        $product_data = ProductImages::where('product_id','=',$product_id)->orderBy('default_image','desc')->get();
         return response()->json($product_data);

    } 

        public function SubscribeEmail(Request $request)
    {

        $this->validate($request,[
            'subscribe_email'=>'required'
        ],[
            'subscribe_email.required'=> 'This Field is required.',
        ]);
            
        try{
            DB::beginTransaction();
            $check=Subscribe::where('email',$request->input('subscribe_email'))->get()->first();

            if($check){
                 DB::rollback();
              return response()->json('Already Subscribed',401);
            }else{
                $data = [
                'email'  =>  $request->input('subscribe_email')
                ];

                $insur_insert = Subscribe::insertGetId($data);
                if(!$insur_insert)
                {
                    DB::rollback();
                    return response()->json('Something Went Wrong',401);
                }
            }
            
        }
        catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return response()->json('Something went wrong.'.$ex->getMessage(),401);
        }
        DB::commit();
        return array(1,'Subscribed Succesfully.');
    }
}
