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
use App\Model\Hsrp;
use \App\Model\SaleAccessories;
use \App\Model\SaleFinance;
use \App\Model\Scheme;
use \App\Model\Accessories;
use \App\Model\MasterAccessories;
use \App\Model\Master;
use \App\Model\HiRise;
use \App\Model\Insurance;
use \App\Model\SaleOrder;
use \App\Model\RtoModel;
use \App\Model\Payment;
use \App\Model\OrderPendingModel;
use \App\Model\BookingModel;
use \App\Model\BestDealSale;
use \App\Model\RtoFileSubmission;
use \App\Model\RtoFileSubmissionDetails;
use \App\Model\RcCorrectionRequest;
use \App\Model\RtoSummary;
use \App\Model\PaymentRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Auth;
use Hash;
use PDF;
use \Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\Import;


class RtoController extends Controller
{
    public function __construct()
    {
        //$this->middleware('auth');
    }
    public function dashboard(){
        return view('admin.dashboard',['layout' => 'layouts.main']);
    }


    public function createRto($id) {

        $checkins = Insurance::where('sale_id',$id)->first();
        if(!isset($checkins->id))
        {
            return redirect('/admin/sale/list')->with('error','Insurance Not Found.');
        }
        if(empty($checkins->policy_number)){
            return redirect('/admin/sale/insurance/'.$id)->with('error','Please Fill Insurance Firstly');
        }
        $bookingData = Sale::leftJoin('product','sale.product_id','product.id')
                        ->leftJoin('store','sale.store_id','store.id')
                        ->leftJoin('customer','sale.customer_id','customer.id')
                        ->leftJoin('sale_finance_info','sale_finance_info.sale_id','sale.id')
                        ->get(['sale.*','customer.name as customer_name','product.model_category',
                        'product.model_variant','product.model_name','store.name as store_name',
                        'sale_finance_info.status as finance_status','sale.customer_pay_type'])
                        ->where('id',$id)->first();
        $rtoData = RtoModel::where("sale_id",$id)->get()->first();
         $data  = array(
            'bookingData' => $bookingData, 
            'rtoData' => $rtoData,
            'layout' => 'layouts.main', 
        );
        return view('admin.sale.create_rto',$data);
    }

    public function createRto_db(Request $request) {
        try {
            $saleInfo = Sale::where('id',$request->input('sale_id'))->first();
            if(!isset($saleInfo->id)){
                return back()->with('error','Sale Information Not Found.')->withInput();
            }
            $arr = []; $arr_msg = [];
            if(in_array($saleInfo->customer_pay_type,['finance','self_finance'])){
                $arr['rto_finance'] =  'required';
                $arr_msg['rto_finance'] =  'This is required.';
            }
            $this->validate($request,array_merge($arr,[
                'rto_type'=>'required',
                'rto_amount'=>'required',
                'rto_app_no'=>'required',
            ]),array_merge($arr_msg,[
                'rto_type.required'=> 'This is required.',
                'rto_amount.required'=> 'This is required.',  
                'rto_app_no.required'=> 'This is required.',  
            ]));

            DB::beginTransaction();

            $customerData = Customers::where('id',$request->input('customer_id'))->get()->first();
            $getdata = RtoModel::where('sale_id', $request->input('sale_id'))->first();
            if (isset($getdata->id)) {
                return back()->with('error','You are not authorized for update');
            }else{
                $add_data = [];
                if(count($arr) > 1){
                    $add_data['rto_finance'] = $request->input('rto_finance');
                }
                $rtodata = RtoModel::insertGetId(
                    array_merge($add_data,[
                        'sale_id'=> $request->input('sale_id'),
                        'customer_id'=>$request->input('customer_id'),
                        'rto_type'=>$request->input('rto_type'),
                        'rto_amount'=>$request->input('rto_amount'),
                        'application_number'=>$request->input('rto_app_no'),
                        'created_by' => Auth::id()
                    ])
                );
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
            }

            if($rtodata==NULL) {
                DB::rollback();
                return redirect('/admin/sale/rto/'.$request->input('sale_id'))->with('error','some error occurred');
            } else{
                /* Add action Log */
                CustomHelpers::userActionLog($action='Create RTO',$rtodata,$request->input('customer_id'));
                DB::commit();
                return redirect('/admin/sale/rto/'.$request->input('sale_id'))->with('success','Successfully Created RTO .');
             }    
        
        }  catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return redirect('/admin/sale/rto/'.$request->input('sale_id'))->with('error','some error occurred'.$ex->getMessage());
        }
    }


    public function rtoPendingList() {
         $data = array(
            'layout' => 'layouts.main'
        );
        return view('admin.rto_pending_list',$data);
    }

    public function rtoPendingList_api(Request $request,$type) {
         if ($type == '1') {
            $type = ($type == '1') ? ['1'] : '';
        }elseif($type == '0'){
            $type = ($type == '0') ? ['0'] : '';
        }
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
        
      $api_data = RtoModel::Join('sale','rto.sale_id','sale.id')
                            ->leftJoin('sale_finance_info','sale_finance_info.sale_id','sale.id')
                ->where('sale.tos','new')
                ->where('rto.main_type','rto')
            ->whereIn('rto.approve',$type)
            ->select(
                'rto.id',
                'rto.main_type',
                'rto.rto_finance',
                'rto.application_number',
                'rto.rto_type',
                'rto.rto_amount',
                'rto.approve',
                'rto.type',
                'sale.sale_no',
                'sale_finance_info.status as finance_status'
            );
            if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('rto.rto_finance','like',"%".$serach_value."%")
                    ->orwhere('rto.application_number','like',"%".$serach_value."%")
                    ->orwhere('rto.main_type','like',"%".$serach_value."%")
                    ->orwhere('rto.rto_type','like',"%".$serach_value."%")
                    ->orwhere('rto.rto_amount','like',"%".$serach_value."%")
                    ->orwhere('rto.approve','like',"%".$serach_value."%")
                    ->orwhere('rto.type','like',"%".$serach_value."%")                    
                    ->orwhere('sale.sale_no','like',"%".$serach_value."%");
                });
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                'rto.id',
                'rto.rto_finance',
                'rto.main_type',
                'rto.application_number',
                'rto.rto_type',
                'rto.rto_amount',
                'rto.approve',
                'rto.type',
                'sale.sale_no'               
                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('rto.id','desc');      
        
        $count = count($api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

    public function rtoHsrpPendingList_api(Request $request) {
       $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
        
        $api_data= HSRP::Join('rto','rto.type_id','hsrp.id')
                ->leftJoin('customer','rto.customer_id','customer.id')
                ->leftJoin('store','store.id','hsrp.id')
                ->where('rto.main_type','<>','rto')
                ->where('hsrp.verified','1')
                ->select(
                    'rto.id',
                    'rto.main_type',
                    'rto.registration_number',
                    'rto.application_number',
                    'rto.rto_type',
                    'rto.rto_amount',
                    'rto.approve',
                    'customer.name',
                    'customer.mobile',
                    'hsrp.frame',
                    'hsrp.type as hsrp_type',
                    'hsrp.fueltype',
                    'hsrp.vechicle_type',
                    'hsrp.oem'
                );
            if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('rto.registration_number','like',"%".$serach_value."%")
                    ->orwhere('rto.application_number','like',"%".$serach_value."%")
                    ->orwhere('rto.rto_type','like',"%".$serach_value."%")
                    ->orwhere('rto.rto_amount','like',"%".$serach_value."%")
                    ->orwhere('rto.approve','like',"%".$serach_value."%")
                    ->orwhere('hsrp.oem','like',"%".$serach_value."%")                    
                    ->orwhere('hsrp.vechicle_type','like',"%".$serach_value."%");
                });
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                    'customer.name',
                    'rto.main_type',
                    'customer.mobile',
                    'hsrp.frame',
                    'hsrp.type',
                    'hsrp.fueltype',
                    'hsrp.vechicle_type',
                    'rto.rto_amount',
                    'rto.application_number',
                    'rto.approve'
                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('rto.id','desc');      
        
        $count = count($api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

    public function rtoListData(Request $request){
        $rto_id  = $request->input('rto_id');
        try{
            $check = RtoModel::where('id',$rto_id)->first();
            if($check->id && $check->main_type == 'rto'){
                $data = Sale::where('sale.id',$check->sale_id)
                            ->leftJoin('store','store.id','sale.store_id')
                            ->leftJoin('customer','customer.id','sale.customer_id')
                            ->leftJoin('rto','rto.sale_id','sale.id')
                            ->leftJoin('sale_order','sale_order.sale_id','sale.id')
                            ->leftJoin('product','product.id','sale.product_id')
                            ->leftJoin('product_details','product_details.frame','sale_order.product_frame_number')
                            ->leftJoin('locality','locality.id','customer.location')
                            ->where('rto.id',$check->id)
                            ->where('rto.main_type','rto')
                            ->select(
                                'rto.id as rto_id',
                                'store.name as store_name',
                                'customer.name as customer_name',
                                'customer.relation_type',
                                'customer.relation',
                                'customer.address',
                                'locality.name as location',
                                'customer.mobile',
                                'sale.sale_no',
                                'sale.ex_showroom_price',
                                'sale.customer_pay_type',
                                'rto.rto_type',
                                'rto.rto_amount',
                                'rto.rto_finance',
                                'rto.main_type',
                                'rto.application_number',
                                'sale_order.product_frame_number',
                                'product_details.engine_number',
                                'product.model_name'
                            )
                            ->first();
                return response()->json($data->toArray());
            }else{
                $data = RtoModel::where('rto.id',$rto_id)
                        ->leftJoin('hsrp',function($join){
                            $join->on('hsrp.id','=','rto.type_id')
                            ->where('rto.main_type','hsrp');
                        })
                        ->leftJoin('customer','customer.id','rto.customer_id')
                        ->leftJoin('locality','locality.id','customer.location')
                        ->leftJoin('store','hsrp.store_id','store.id')
                        ->select('rto.id as rto_id','store.name as store_name','customer.name as customer_name','customer.mobile','customer.address','locality.name as location','hsrp.frame as product_frame_number','hsrp.engine as engine_number','rto.rto_amount','rto.application_number','customer.relation_type','customer.relation','rto.main_type')->first();
                     return response()->json($data->toArray());
            }

        }catch(\Illuminate\Database\QueryException $ex) {
            return response()->json("Some error Occured. ".$ex->getMessage(),401);
        }
    }

    public function rtoApproval($id) {
        try {
             $timestamp = date('Y-m-d G:i:s');
             $CheckRto = RtoModel::where('id',$id)->first();
             if ($CheckRto->main_type == 'rto') {
                $rto = RtoModel::leftJoin('sale','rto.sale_id','sale.id')->where('rto.id',$id)
                    ->select('sale.store_id','rto.customer_id')->get()->first();
             }elseif ($CheckRto->main_type == 'hsrp' || $CheckRto->main_type == 'new') {
                 $rto = RtoModel::where('rto.id',$id)->leftJoin('hsrp','hsrp.id','rto.type_id')
                    ->where('rto.main_type','hsrp')->select('hsrp.store_id','rto.customer_id','rto.type_id')->first();
                if ($CheckRto->main_type == 'hsrp') {
                    $hsrp = HSRP::where('id',$rto->type_id)->first();
                    $checkPaymet = PaymentRequest::where('type','hsrp')->where('type_id',$hsrp->id)->where('status','Done')->first();
                    if ($checkPaymet == null) {
                        return  array('type' => 'error', 'msg' => 'Payment not complete for this RTO.', 'main_type' => $CheckRto->main_type);
                    }
                }
             }
              
            $checkStore = CustomHelpers::CheckAuthStore($rto['store_id']);
                
            if ($checkStore)
            {
                $rtodata = RtoModel::where('id',$id)->update([
                        'approve' => 1,
                        'approved_by' => Auth::id(),
                        'approval_date' => $timestamp
                    ]);

                if($rtodata == NULL) {
                    return  array('type' => 'error', 'msg' => 'Something went wrong.', 'main_type' => $CheckRto->main_type);
                } else{
                    /* Add action Log */
                    $action='RTO Approved';
                    CustomHelpers::userActionLog($action,$id,$rto['customer_id']);
                     return  array('type' => 'success' , 'msg' => 'RTO Successfully Approved', 'main_type' => $CheckRto->main_type);
                }
            }else{
                  return  array('type' => 'error', 'msg' => 'You Are Not Authorized to Approve this Rto', 'main_type' => $CheckRto->main_type);
            }
            
        }catch(\Illuminate\Database\QueryException $ex) {
           return  array('type' => 'error', 'msg' => 'Something went wrong.'.$ex.'', 'main_type' => $CheckRto->main_type);
        }
    }

    public function rtoRejectApproval(Request $request){
        try{
            DB::beginTransaction();

            $rto_id = $request->input('rto_id');
            $check_rto = RtoModel::where('id',$rto_id)->first();
            if(!isset($check_rto->id)){
                return response()->json('Rto Data Not Found.',401);
            }
            // check rto should not be aproved
            if($check_rto->approve == 1){
                return response()->json('Rto Aleady Approved. So, Should not be Rejected.',401);
            }
            // remove application number
            $update_rto = RtoModel::where('id',$rto_id)
                                    ->update([
                                        'application_number'    =>  null,
                                        'approve' => 2
                                    ]);
            if(!$update_rto){
                return response()->json("Something Wen't Wrong.",401);
            }

        }
        catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return response()->json('Some Issue Occured'.$ex->getMessage(),401);
        }
        /* Add action Log */
        CustomHelpers::userActionLog($action='RTO Rejected',$rto_id,$check_rto->customer_id);
        DB::commit();
        return response()->json(array('type' => 'success','main_type' => $check_rto['main_type']));
    }

    public function rtoList() {
        $data = array(
            'layout' => 'layouts.main'
        );
        return view('admin.rto_list',$data);
    }

    public function rtoList_api(Request $request) {
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
        $api_data= Sale::leftJoin('rto', 'rto.sale_id','sale.id')
                ->where('sale.tos','new')
                ->where('rto.application_number', null)
                ->where('sale.status','!=','cancelled')
            ->select(
                'sale.id',
                'sale.sale_no'
            );

            if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('sale.sale_no','like',"%".$serach_value."%")                    
                    ;
                });
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                'sale.id',
                'sale.sale_no'               
                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('sale.id','desc');      
        
        $count = count($api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
       
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

    public function rtoPayment_list() {
        $data = array(
            'layout' => 'layouts.main'
        );
        return view('admin.rto_payment_list',$data);
    }

    public function rtoPaymentList_api(Request $request,$type) {
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;

        $api_data= RtoModel::Join('sale','rto.sale_id','sale.id')
                            ->leftJoin('rto_summary','rto_summary.rto_id','rto.id')
                            ->leftJoin('sale_order','sale_order.sale_id','sale.id')
                            ->leftJoin('sale_finance_info','sale_finance_info.sale_id','sale.id')
        ->where('sale.tos','new')
        ->where('rto.main_type','rto')
        ->where('rto.approve','1')
        ->select(
            'rto.id',
            'rto.rto_finance',
            'rto.application_number',
            'rto.rto_type',
            'rto.rto_amount',
            'rto.approve',
            'rto.type',
            'sale.sale_no',
            'rto.amount',
            'rto.penalty_charge',
            'sale.total_amount',
            'rto_summary.customer_name',
            'sale_order.product_frame_number as frame',
            'sale_finance_info.status as finance_status'
        );
        if ($type == 'complete') {
            $api_data = $api_data->whereRaw('rto.amount = rto.rto_amount');
        }elseif ($type == 'pending') {
              $api_data= RtoModel::Join('sale','rto.sale_id','sale.id')
              ->leftJoin('rto_summary','rto_summary.rto_id','rto.id')
               ->leftJoin('sale_order','sale_order.sale_id','sale.id')
               ->leftJoin('sale_finance_info','sale_finance_info.sale_id','sale.id')
              ->where('sale.tos','new')
              ->where('rto.approve','1')
              ->where('rto.main_type','rto')
            ->select(
                'rto.id',
                'rto.rto_finance',
                'rto.rto_type',
                'rto.application_number',
                'rto.rto_amount',
                'rto.approve',
                'rto.type',
                'rto_summary.customer_name',
                'rto.amount',
                'rto.penalty_charge',
                'sale.sale_no',
                'sale_order.product_frame_number as frame',
                'sale_finance_info.status as finance_status'
            )
            ->whereNull('rto.amount');
            $api_data = $api_data->whereNull('rto.amount');
        }
    
            if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('sale.sale_no','like',"%".$serach_value."%")
                    ->orwhere('sale.sale_date','like',"%".$serach_value."%")
                    ->orwhere('sale.total_amount','like',"%".$serach_value."%")
                    ->orwhere('sale.payment_amount','like',"%".$serach_value."%");
                });
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                'rto.rto_finance',
                'rto.rto_type',
                'rto.application_number',
                'rto.rto_amount',
                'rto.type',
                'rto_summary.customer_name',
                'rto.amount',
                'rto.penalty_charge',
                'rto.approve',
                'sale.sale_no',
                'sale_order.product_frame_number',
                'sale_finance_info.status'
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

    public function RtoHsrpPaymentList_api(Request $request) {
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
        
        $api_data= RtoModel::leftJoin('hsrp','rto.type_id','hsrp.id')
                ->leftJoin('customer','rto.customer_id','customer.id')
                ->where('rto.main_type','<>','rto')
                ->where('hsrp.verified','1')
                ->where('rto.approve','1')
                ->select(
                    'rto.id',
                    'rto.main_type',
                    'rto.registration_number',
                    'rto.application_number',
                    'rto.rto_type',
                    'rto.rto_amount',
                    'rto.approve',
                    'customer.name',
                    'customer.mobile',
                    'hsrp.frame',
                    'hsrp.type as hsrp_type',
                    'rto.amount',
                    'rto.penalty_charge',
                    'hsrp.oem'
                );
            if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('rto.registration_number','like',"%".$serach_value."%")
                    ->orwhere('rto.application_number','like',"%".$serach_value."%")
                    ->orwhere('rto.rto_type','like',"%".$serach_value."%")
                    ->orwhere('rto.rto_amount','like',"%".$serach_value."%")
                    ->orwhere('rto.approve','like',"%".$serach_value."%")
                    ->orwhere('hsrp.oem','like',"%".$serach_value."%")                    
                    ->orwhere('hsrp.vechicle_type','like',"%".$serach_value."%");
                });
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                    'customer.name',
                    'rto.main_type',
                    'customer.mobile',
                    'hsrp.frame',
                    'hsrp.type',
                    'rto.amount',
                    'rto.penalty_charge',
                    'rto.rto_amount',
                    'rto.application_number',
                    'rto.approve'            
                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('rto.id','desc');      
        
        $count = count($api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

    public function rtoPayment($id) {
            $rto_id  = $id;
            try{
                $check = RtoModel::where('id',$rto_id)->first();
                if($check->id){
                   if ($check->main_type == 'hsrp' || $check->main_type == 'new') {
                        $data = RtoModel::leftJoin('customer','customer.id','rto.customer_id')
                                ->where('rto.id',$check->id)
                                ->select(
                                    'rto.id',
                                    'customer.name as customer_name',
                                    'rto.penalty_charge',
                                    'rto.rto_amount',
                                    'rto.amount',
                                    'rto.application_number'
                                )
                                ->first();
                    return response()->json($data->toArray());
                   }else{
                     $data = Sale::where('sale.id',$check->sale_id)
                                ->leftJoin('customer','customer.id','sale.customer_id')
                                ->leftJoin('rto','rto.sale_id','sale.id')
                                ->leftJoin('sale_order','sale_order.sale_id','sale.id')
                                ->where('rto.id',$check->id)
                                ->select(
                                    'rto.id',
                                    'customer.name as customer_name',
                                    'sale.sale_no',
                                    'rto.sale_id',
                                    'rto.penalty_charge',
                                    'rto.rto_amount',
                                    'rto.amount',
                                    'rto.application_number',
                                    'sale_order.product_frame_number'
                                )
                                ->first();
                    return response()->json($data->toArray());
                   }
                }else{
                    return response()->json('Rto Not Found.',401);
                }
    
            }catch(\Illuminate\Database\QueryException $ex) {
                return response()->json("Some error Occured. ".$ex->getMessage(),401);
            }
        
    }

    public function rtoPayment_Db(Request $request) {
         try { 
            // $this->validate($request,[
            //     'amt'=>'required',
            //     'pcharge'=>'required',
            // ],[
            //     'amt.required'=> 'This is required.', 
            //     'pcharge.required'=> 'This is required.', 
            // ]);

            $rto_id = $request->input('id');
            $rtodata = RtoModel::where('id',$rto_id)->first();
            if ($rtodata['approve'] == '1') {
                  if ($request->input('total_amt') == $request->input('amt')) {
                        $rto = RtoModel::where('id',$request->input('id'))->update(
                            [
                                'amount' => $request->input('amt'),
                                'penalty_charge' => $request->input('pcharge'),
                            ]
                        );
                        if($rto == NULL) {
                            return array('type' => 'errro', 'main_type' => $rtodata->main_type);
                        } else{
                            /* Add action Log */
                            CustomHelpers::userActionLog($action='RTO Payment',$rto_id,$rtodata['customer_id']);
                             return array('type' => 'success', 'main_type' => $rtodata->main_type);
                         } 
                    }else{
                        return array('type' => 'validation_error', 'main_type' => $rtodata->main_type);
                     }
            }else{
                 return array('type' => 'approve_error', 'main_type' => $rtodata->main_type);
            }
         
        
        }  catch(\Illuminate\Database\QueryException $ex) {
            return array('type' => 'error', 'main_type' => $rtodata->main_type);
        } 

    }

    public function rtoFile_list() {
         $data = array(
            'layout' => 'layouts.main'
        );
        return view('admin.rto_file_list',$data);
    }

    public function rtoFileList_api(Request $request, $type) {
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
        if ($type == 'uploaded') {
              $api_data = RtoModel::Join('sale','rto.sale_id','sale.id')
              ->where('sale.tos','new')
              ->where('rto.main_type','rto')
              ->select(
                'rto.id',
                'rto.rto_finance',
                'rto.application_number',
                'rto.rto_type',
                'rto.rto_amount',
                'rto.approve',
                'rto.type',
                'sale.sale_no',
                'rto.amount',
                'rto.penalty_charge',
                'rto.file_uploaded',
                'rto.uploaded_date',
                'sale.total_amount'
            )
            ->where('rto.file_uploaded','=', 1);
        }elseif ($type == 'pending') {
              $api_data= RtoModel::Join('sale','rto.sale_id','sale.id')
              ->where('sale.tos','new')
              ->where('rto.main_type','rto')
              ->select(
                'rto.id',
                'rto.rto_finance',
                'rto.application_number',
                'rto.rto_type',
                'rto.rto_amount',
                'rto.approve',
                'rto.type',
                'rto.amount',
                'rto.file_uploaded',
                'rto.penalty_charge',
                'sale.sale_no'
            )
            ->where('rto.file_uploaded','=', 0);
        }
    
            if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('sale.sale_no','like',"%".$serach_value."%")
                    ->orwhere('sale.sale_date','like',"%".$serach_value."%")
                    ->orwhere('sale.total_amount','like',"%".$serach_value."%")
                    ->orwhere('sale.payment_amount','like',"%".$serach_value."%")                   
                    ;
                });
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
               'rto.id',
                'rto.rto_finance',
                'rto.application_number',
                'rto.rto_type',
                'rto.rto_amount',
                'rto.approve',
                'rto.type',
                'rto.amount',
                'rto.file_uploaded',
                'rto.penalty_charge',
                'sale.sale_no'
                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('sale.id','desc');      
        
        $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        $queries = DB::getQueryLog();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

    public function rtoFile_upload(Request $request) {
        
       try { 
            $timestamp=date('Y-m-d G:i:s');
            $rto_id = $request->input('id');
            $status = $request->input('file_status');
            if ($status == null) {
                return  array('type' => 'error', 'msg' => 'File Uploaded field is required.');
            }
            $check = RtoModel::where('id',$rto_id)->first();
            if(!isset($check->id)){
                return  array('type' => 'error', 'msg' => 'Id dose not exist.');
            }
            $rto = RtoModel::where('id',$rto_id)->update(
                [
                    'file_uploaded'=>$status,
                    'uploaded_date'=>$timestamp
                ]
            );
            if($rto == NULL) {
                return  array('type' => 'error', 'msg' => 'Something went wrong.');
            }
            else{
                /* Add action Log */
                CustomHelpers::userActionLog($action='RTO File Upload',$rto_id,$check->customer_id);
                return  array('type' => 'success', 'msg' => 'File uploaded Successfully.');
            }        
        }  catch(\Illuminate\Database\QueryException $ex) {
            return  array('type' => 'error', 'msg' => 'Something went wrong'.$ex.' .');
        }
    }
    
   
    public function rtoFile_submission() {
        $rtoData = RtoModel::select("rto.*","sale.sale_no as sale_no","sale.sale_date",
                                "rto_summary.customer_name","rto_summary.mobile",
                                "rto_summary.address")
        ->join("sale",function($join){
            $join->on("rto.sale_id","=","sale.id")
                ->where("rto.approve","=","1")
                ->where("rto.file_uploaded","=","1")
                ->where("rto.file_submission","=","0");
                // ->whereNotNull("rto.amount",null);
        })
        ->join("rto_summary",function($join){
            $join->on("rto.id","=","rto_summary.rto_id")
            ->where('rto_summary.currentStatus',"=", 1);
        })
        ->get();        
        $agent_name = Master::where('type','agent_name')->orderBy('order_by')->get();
         $data = array(
            'rtoData' => $rtoData,
            'agent_name'    =>  $agent_name,
            'layout' => 'layouts.main'
        );
        return view('admin.rto_file_submission',$data);
    }

    public function rtoFileSubmission_Db(Request $request) {
        try { 
            $timestamp=date('Y-m-d');
            $this->validate($request,[
                'agent_name'=>'required',
                'rto_id'=>'required',
            ],[
                'agent_name.required'=> 'This is required.',  
                'rto_id.required'=> 'This is required.',  
            ]);
            DB::beginTransaction();
            $count = count($request->input('rto_id'));
              $rtofile_id = RtoFileSubmission::insertGetId(
                [
                    'agent_name'=>$request->input('agent_name'),
                    'submission_date'=>$timestamp,
                ]);

           if ($rtofile_id) {
                for ($i=0; $i < $count ; $i++) { 
              
                $rtofile = RtoFileSubmissionDetails::insertGetId(
                [
                    'file_submission_id'=>$rtofile_id,
                    'rto_id'=>$request->input('rto_id')[$i]
                ]);
                if ($rtofile) {
                $updateRto = RtoModel::where('id',$request->input('rto_id')[$i])->update([
                        'file_submission' => 1
                ]);
                }
            }
           }
          if($updateRto ==NULL) {
              DB::rollback();
                   return redirect('/admin/rto/file/submission')->with('error','some error occurred');
            } else{
                /* Add action Log */
                CustomHelpers::userActionLog($action='RTO File Submission',$rtofile_id);
                DB::commit();
                return redirect('/admin/rto/file/submission')->with('success','RTO file submitted ! ');
             }    
        
        }  catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return redirect('/admin/rto/file/submission')->with('error','some error occurred'.$ex->getMessage());
        }
    }

    public function rtoFileSubmission_list() {
        $data = array(
            'layout' => 'layouts.main'
        );
        return view('admin.rto_file_submission_list',$data);
    }

    public function rtoFileSubmissionList_api(Request $request) {
        DB::enableQueryLog();
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
       
        $api_data= RtoFileSubmissionDetails::leftJoin('rto_file_submission','rto_file_submission_details.file_submission_id','rto_file_submission.id')
            ->select(
                'rto_file_submission.id',
                'rto_file_submission.agent_name',
                'rto_file_submission.submission_date',
                DB::raw('count(rto_file_submission_details.rto_id) as rtocount')
            )
            ->groupBy('rto_file_submission_details.file_submission_id');
          
            if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('rto_file_submission.id','like',"%".$serach_value."%")
                    ->orwhere('rto_file_submission.agent_name','like',"%".$serach_value."%")
                    // ->orwhere('rto.application_number','like',"%".$serach_value."%")
                    // ->orwhere('rto.rto_amount','like',"%".$serach_value."%")                   
                    ->orwhere('rto_file_submission.submission_date','like',"%".$serach_value."%")                   
                    ;
                });
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                'rto_file_submission.id',
                'rto_file_submission.agent_name',
                // 'rto.application_number',
                'rto_file_submission_details.rto_id',
                // 'rto.rto_amount',
                'rto_file_submission.submission_date'
                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('rto_file_submission.id','desc');      
        
        $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        $queries = DB::getQueryLog();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

    public function rtoFileSubmissionList_view($id) {
        $rtoData = RtoFileSubmissionDetails::select("rto_file_submission_details.*","rto.rto_amount","rto.application_number","rto.rc_correction_id","rto.rto_type","sale.sale_date","sale.sale_no","rto_summary.customer_name","rto_summary.mobile","rto_summary.address")
        ->join("rto_file_submission",function($join) use($id){
            $join->on("rto_file_submission_details.file_submission_id","=","rto_file_submission.id")
                ->where("rto_file_submission.id","=", $id);
        })
        ->join("rto",function($join){
            $join->on("rto_file_submission_details.rto_id","=","rto.id");
        })
        ->join("sale",function($join){
            $join->on("rto.sale_id","=","sale.id");
        })
        ->join("rto_summary",function($join){
            $join->on("rto.id","=","rto_summary.rto_id")
            ->where('rto_summary.currentStatus',"=", 1);
        })
        ->get();
        $rtoFileData = RtoFileSubmission::select('agent_name','submission_date')
                 ->where('id', $id)
                 ->get();
        $data = array(
            'rtoFileData' => $rtoFileData,
            'rtoData' => $rtoData,
            'layout' => 'layouts.main'
        );
        return view('admin.rto_file_submission_view',$data);
    }

    public function rtoFileSubmissionPDF_print($id) {
         $data['rtoData'] = RtoFileSubmissionDetails::select("rto_file_submission_details.*","rto.rto_amount","rto.application_number","rto.rto_type","sale.sale_date","sale.sale_no","rto_summary.customer_name","rto_summary.mobile","rto_summary.address")
        ->join("rto_file_submission",function($join) use($id){
            $join->on("rto_file_submission_details.file_submission_id","=","rto_file_submission.id")
                ->where("rto_file_submission.id","=", $id);
        })
        ->join("rto",function($join){
            $join->on("rto_file_submission_details.rto_id","=","rto.id");
        })
        ->join("sale",function($join){
            $join->on("rto.sale_id","=","sale.id");
        })
        ->join("rto_summary",function($join){
            $join->on("rto.id","=","rto_summary.rto_id")
            ->where('rto_summary.currentStatus',"=", 1);
        })
        ->get();
        $data['rtoFileData'] = RtoFileSubmission::select('agent_name','submission_date')
                 ->where('id', $id)
                 ->get();


        $pdfFilePath = "internal_order.pdf";
         $pdf = PDF::loadView('admin.file_submission_print', $data);
         return $pdf->stream($pdfFilePath);
    }
        //rto number plate upload excel and listing
    public function numberPlate(Request $request)
    {
        $data = array(
            'layout' => 'layouts.main'
        );
        return view('admin.rto.pendingPlate',$data);
    }
    public function numberPlate_list(Request $request) {
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;

        $api_data = RtoModel::join("sale","rto.sale_id","=","sale.id")
                    ->where('rto.main_type','rto')
                    // ->where('rto.approve',0)
                    // ->where('rto.file_uploaded',0)
                    // ->where('rto.file_submission',0)
            ->select(
                'rto.id',
                'rto.main_type',
                'rto.application_number',
                'rto.rto_amount',
                'rto.registration_number',
                'rto.amount',
                'rto.penalty_charge',
                'rto.front_lid',
                'rto.rear_lid',
                'sale.sale_no'
            );
    
            if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('sale.sale_no','like',"%".$serach_value."%")
                    ->orwhere('rto.application_number','like',"%".$serach_value."%")
                    ->orwhere('rto.rto_amount','like',"%".$serach_value."%")
                    ->orwhere('rto.registration_number','like',"%".$serach_value."%")
                    ->orwhere('rto.amount','like',"%".$serach_value."%")                   
                    // ->orwhere('rto.penality_charge','like',"%".$serach_value."%")
                    ->orwhere('rto.front_lid','like',"%".$serach_value."%")
                    ->orwhere('rto.main_type','like',"%".$serach_value."%")
                    ->orwhere('rto.rear_lid','like',"%".$serach_value."%");
                });
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
               'rto.id',
                'rto.main_type',
                'rto.application_number',
                'rto.rto_type',
                'rto.rto_amount',
                'rto.registration_number',
                'rto.amount',
                'rto.penalty_charge',
                'rto.front_lid',
                'rto.rear_lid',
                'sale.sale_no'
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

    public function HsrpNumberPlate_list(Request $request) {
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;

        $api_data = RtoModel::join("customer","rto.customer_id","=","customer.id")
                    ->where('rto.main_type','<>','rto')
                    // ->where('rto.approve',0)
                    // ->where('rto.file_uploaded',0)
                    // ->where('rto.file_submission',0)
            ->select(
                'rto.id',
                'rto.main_type',
                'rto.registration_number',
                'rto.application_number',
                'rto.rto_amount',
                'rto.type',
                'rto.amount',
                'rto.penalty_charge',
                'rto.front_lid',
                'rto.rear_lid',
                'customer.name'
            );
    
            if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('customer.name','like',"%".$serach_value."%")
                    ->orwhere('rto.application_number','like',"%".$serach_value."%")
                    ->orwhere('rto.rto_amount','like',"%".$serach_value."%")
                    ->orwhere('rto.registration_number','like',"%".$serach_value."%")
                    ->orwhere('rto.amount','like',"%".$serach_value."%")                   
                    // ->orwhere('rto.penality_charge','like',"%".$serach_value."%")
                    ->orwhere('rto.front_lid','like',"%".$serach_value."%")
                    ->orwhere('rto.main_type','like',"%".$serach_value."%")
                    ->orwhere('rto.rear_lid','like',"%".$serach_value."%");
                });
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                'customer.name',
                'rto.main_type',
                'rto.application_number',
                'rto.rto_type',
                'rto.rto_amount',
                'rto.registration_number',
                'rto.amount',
                'rto.penalty_charge',
                'rto.front_lid',
                'rto.rear_lid'
                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('rto.id','desc');      
        
        $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

    public function checkExcelFile($file_ext){
        $allowed_extension = array('xls','xlsx','xlt','xltm','xltx','xlsm');
       
        return in_array($file_ext,$allowed_extension) ? true : false;
    }
    public function numberPlateImport(Request $request)
    {
        $validator = Validator::make($request->all(),[    
            'fileUpload'  => 'required', 
            'startRow'=>'required|numeric'
        ],[
            'startRow.required'=> 'This field is required.',
            'startRow.numeric'=> 'This field contain only Number.',
            'fileUpload.required' => 'This field is required.'
        ]);
        if($request->hasFile('fileUpload'))
        {
            $validator->after(function ($validator) use ($request){
                if($this->checkExcelFile($request->file('fileUpload')->getClientOriginalExtension()) == false) {
                    //return validator with error by file input name
                    $validator->errors()->add('fileUpload', 'The file must be a file of type: xls, xlsx, xlt, xltm, xltx, xlsm');
                }
            });
        }
        if ($validator->fails()) {
            return redirect('admin/rto/numberPlate/pending')
                        ->withErrors($validator)
                        ->withInput();
        }

        DB::beginTransaction();
        try{
            if($request->file('fileUpload'))
            {
                $index = array('REGISTRATION NO' => 1,'CHASSIS / VIN / FRAME' => 2,'FRONT LID' => 5,'REAR LID' =>6);
                $path = $request->file('fileUpload');      
                $data = Excel::toArray(new Import(),$path);
                $error = '';
                $frame_error = '';

                if(count($data) > 0 && $data)
                {
                    //validation index correct position or not  (pending)
                    $i = $request->input('startRow');  
                    $out= $this->validate_excel_format($i,$data[0][$i-2],$index);
                    if($out['column_name_err'] == 0)
                    {
                        foreach($data[0] as $key => $value) {
                            // $key = $i;
                            if($key >= $i-1) {
                                $sr_no = $value[0];
                                $registration = $value[$index['REGISTRATION NO']];
                                $frame = $value[$index['CHASSIS / VIN / FRAME']];
                                $front = $value[$index['FRONT LID']];
                                $rear = $value[$index['REAR LID']];
                                if(empty($frame)) {
                                    break;
                                }
                                $findRtoFrame = RtoModel::leftJoin('sale_order','sale_order.sale_id','rto.sale_id')->where('rto.main_type','rto')->where('sale_order.product_frame_number',$frame)->first();

                                $findHsrpFrame = RtoModel::leftJoin('hsrp','hsrp.id','rto.type_id')->where('rto.main_type','hsrp')->where('hsrp.frame',$frame)->first();
                                
                                if(isset($findRtoFrame->registration_number) || isset($findHsrpFrame->registration_number)) {
                                    $frame_error = $frame_error.'Frame Number Already Updated for this Serial Number '.$sr_no.'  ';
                                }
                                else{
                                    $RtoUpdate = RtoModel::withoutTimestamps()->leftJoin('sale_order','sale_order.sale_id','rto.sale_id')
                                                    ->whereNull('registration_number')
                                                    ->whereNull('front_lid')
                                                    ->whereNull('rear_lid')
                                                    ->where('rto.main_type','rto')
                                                    ->where('sale_order.product_frame_number',$frame)
                                                    ->update([
                                                        'registration_number' => $registration,
                                                        'front_lid' =>  $front,
                                                        'rear_lid' =>  $rear,
                                                    ]);
                                    $HsrpUpdate = RtoModel::withoutTimestamps()->leftJoin('hsrp','hsrp.id','rto.type_id')
                                                    ->whereNull('rto.registration_number')
                                                    ->whereNull('rto.front_lid')
                                                    ->whereNull('rto.rear_lid')
                                                    ->where('rto.main_type','hsrp')
                                                    ->where('hsrp.frame',$frame)
                                                    ->update([
                                                        'registration_number' => $registration,
                                                        'front_lid' =>  $front,
                                                        'rear_lid' =>  $rear,
                                                    ]);
                                    if(!$RtoUpdate || !$HsrpUpdate) {
                                        $frame_error = $frame_error.'Frame Number Will Not be Updated for this Serial Number '.$sr_no.', Please Check Manually And Fix It.  ';
                                    }
                                }
                            }

                        }
                    }
                    else{ 
                        $error = $error.$out['error'];  
                    }
                }
                else{
                    DB::rollback();
                        return back()->with('error','Error, Check your file should not be empty!!')->withInput();
                }
            }
            else{
                DB::rollback();
                return back()->with('error','Error, some error occurred Please Try again!!')->withInput();
            }
        }
        catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return redirect('/admin/rto/numberPlate/pending')->with('error','some error occurred'.$ex->getMessage())->withInput();
        }
        if(!empty($error))
        {
            DB::rollback();
            return redirect('/admin/rto/numberPlate/pending')->with('error',$error)->withInput();
        }
        DB::commit();
        if(empty($frame_error))
        {
            return redirect('/admin/rto/numberPlate/pending')->with('success','Successfully Updated');
        }
        return redirect('/admin/rto/numberPlate/pending')->with('success','Successfully Updated, but some frame could not be find, such as :-'.$frame_error);

    }

    public function validate_excel_format($row,$a,$column_name,$sheet="")
    {
        ini_set('max_execution_time', 18000);
        $index=0;
        $row = $row-2;
        $column_name_with_index = $column_name;
        $column_name = array_keys($column_name);
        $column_name_err=0;
        $char = '0';
        $side=0;
        $error="";
        $data_inserted=0;
        $header = array();
        if(count($a)>=count($column_name))
        {
            foreach($column_name_with_index as $key1 => $val1)
            {
                $heading = preg_replace("/\r|\n|\t|\s+/", " ", $a[$val1]);
                if($key1 == $heading)
                {
                    $header[$key1] = $val1;
                }
                else{
                    $column_name_err++;
                    $error=$error."Column Name not in provided format. Error At  ".$this->getNameFromNumber($val1)."".$row.".";
                }
                $index++;
                // $char++;
            } 
            if(count($column_name) == count($header)){
                $column_name_err = 0;
                $error="";
            }
        }
        else
        {
            $column_name_err++;
            $error=$error."Required Column Name not in provided Sheet. Please Correct Sheet and try again";
            $side=1;
        }
        $data = array(
            'column_name_err' => $column_name_err,
            'error' =>  $error,
            'columnCount'  =>  $side,
            'header'    =>  $header
        );
        return $data;
        //return $header;
    }
    function getNameFromNumber($num) {
        $numeric = $num % 26;
        $letter = chr(65 + $numeric);
        $num2 = intval($num / 26);
        if ($num2 > 0) {
            return $this->getNameFromNumber($num2 - 1) . $letter;
        } else {
            return $letter;
        }
    }

    public function rtoRc_list() {
        $data = array(
            'layout' => 'layouts.main'
        );
        return view('admin.rto.rc_list',$data);
    }

    public function rtoRcList_api(Request $request,$tab) {
        DB::enableQueryLog();
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
            if($tab == 'pending')
            {
            $api_data = RtoModel::leftJoin('sale','rto.sale_id','sale.id')
            ->select(
                'rto.id',
                'rto.rto_finance',
                'rto.application_number',
                'rto.rto_type',
                'rto.rto_amount',
                'rto.approve',
                'rto.type',
                'sale.sale_no',
                'rto.amount',
                'rto.front_lid',
                'rto.rear_lid',
                'rto.rc_number',
                'sale.total_amount',
                'sale.sale_date'
            )
            ->where('rto.approve',1)
            ->where('rto.file_uploaded',1)
            // ->whereNotNull('rto.amount')
            // ->where(function($query) {
            //     $query->whereNull('rto.rear_lid')
            //     ->orwhereNull('rto.rear_lid')
            //     ->orwhereNull('rto.rc_number');
            // });
            // ->where('rto.rear_lid',null)
            // ->where('rto.front_lid',null);
            ->whereNull('rto.rc_number');
            }
            else{
            $api_data = RtoModel::leftJoin('sale','rto.sale_id','sale.id')
            ->select(
                'rto.id',
                'rto.rto_finance',
                'rto.application_number',
                'rto.rto_type',
                'rto.rto_amount',
                'rto.approve',
                'rto.type',
                'sale.sale_no',
                'rto.amount',
                'rto.front_lid',
                'rto.rear_lid',
                'rto.rc_number',
                'sale.total_amount',
                'sale.sale_date'
            )
            ->where('rto.approve',1)
            // ->whereNotNull('rto.amount')
            ->where('rto.file_uploaded',1)
            // ->whereNotNull('rto.rear_lid')
            // ->whereNotNull('rto.front_lid')
            ->whereNotNull('rto.rc_number');
            }
            
    
            if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('sale.sale_no','like',"%".$serach_value."%")
                    ->orwhere('sale.sale_date','like',"%".$serach_value."%")
                    ->orwhere('rto.rto_finance','like',"%".$serach_value."%")
                    ->orwhere('rto.application_number','like',"%".$serach_value."%")
                    ->orwhere('rto.rto_type','like',"%".$serach_value."%")
                    ->orwhere('rto.amount','like',"%".$serach_value."%")                   
                    ->orwhere('rto.front_lid','like',"%".$serach_value."%")
                    ->orwhere('rto.rear_lid','like',"%".$serach_value."%")
                    ->orwhere('rto.rc_number','like',"%".$serach_value."%");
                });
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                'rto.id',
                'rto.rto_finance',
                'rto.application_number',
                'rto.rto_type',
                'rto.rto_amount',
                'rto.approve',
                'rto.type',
                'rto.amount',
                'rto.front_lid',
                'rto.rear_lid',
                'rto.rc_number',
                'sale.sale_no',
                'sale.sale_date'
                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('rto.id','desc');      
        
        $count = $api_data->count();
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        $queries = DB::getQueryLog();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

     public function HsrpRcList_api(Request $request) {
        DB::enableQueryLog();
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
            $api_data = RtoModel::select(
                'rto.id',
                'rto.rto_finance',
                'rto.application_number',
                'rto.rto_type',
                'rto.rto_amount',
                'rto.approve',
                'rto.main_type',
                'rto.amount',
                'rto.front_lid',
                'rto.rear_lid',
                'rto.rc_number'
            )
            ->where('rto.approve',1)
            ->where('main_type','hsrp');            
    
            if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('rto.rto_finance','like',"%".$serach_value."%")
                    ->orwhere('rto.application_number','like',"%".$serach_value."%")
                    ->orwhere('rto.rto_type','like',"%".$serach_value."%")
                    ->orwhere('rto.amount','like',"%".$serach_value."%")                   
                    ->orwhere('rto.front_lid','like',"%".$serach_value."%")
                    ->orwhere('rto.rear_lid','like',"%".$serach_value."%")
                    ->orwhere('rto.rc_number','like',"%".$serach_value."%");
                });
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                'rto.id',
                'rto.rto_finance',
                'rto.application_number',
                'rto.rto_type',
                'rto.rto_amount',
                'rto.approve',
                'rto.main_type',
                'rto.amount',
                'rto.front_lid',
                'rto.rear_lid',
                'rto.rc_number',
                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('rto.id','desc');      
        
        $count = $api_data->count();
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        $queries = DB::getQueryLog();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

    public function getRcNumber($id) {
         $data =  RtoModel::where('id',$id)
                    ->select("id","rc_number",'receiving_date')->first();
        return response()->json($data);
    }

    public function updateRcNumber(Request $request) {
        try {
            $this->validate($request,[
                'rc_number'=>'required',
                'receiving_date'    =>  'required'
            ],[
                'rc_number.required'=> 'This is required.', 
                'receiving_date.required'=> 'This is required.', 
            ]);
            $rto_id = $request->input('rto_id');
            $check = RtoModel::where('id',$rto_id)->first();
            if(!isset($check->id)){
                return 'errro';
            }
            $rcData = RtoModel::where('id',$request->input('rto_id'))->update(
                [
                    'rc_number'=>$request->input('rc_number'),
                    'receiving_date'=>CustomHelpers::showDate($request->input('receiving_date'),'Y-m-d'),
                ]
            );

           
            if($rcData==NULL) 
            {
                //DB::rollback();
                return 'error';
            }
            else{
                /* Add action Log */
                CustomHelpers::userActionLog($action='RTO RC Receive',$rto_id,$check->customer_id);
                return 'success'; 
             }    
        
        }  catch(\Illuminate\Database\QueryException $ex) {
            return 'error';
        }
    }

    public function rcCorrectionRequest($id) {
        $getid = rcCorrectionRequest::where('rto_id',$id)->first();
        if(isset($getid->id)){
            return back()->with('error','Correction request already send.');
        }else{
         $rtoData = RtoModel::select("rto.*","sale.hypo","sale.sale_no","customer.name","customer.relation_type",'customer.relation','customer.address','customer.mobile','sale_order.product_frame_number','rto.created_by','rto.approved_by','creadeUser.name as create_staff_name','approveUser.name as approve_staff_name')
        ->join("sale",function($join){
            $join->on("rto.sale_id","=","sale.id");
        })
        ->join("customer",function($join){
            $join->on("rto.customer_id","=","customer.id");
        })
        ->join("sale_order",function($join){
            $join->on("rto.sale_id","=","sale_order.sale_id");
        })
        ->join("users as creadeUser",function($join){
            $join->on("rto.created_by","=","creadeUser.id");
        })
        ->join("users as approveUser",function($join){
            $join->on("rto.approved_by","=","approveUser.id");
        })
        ->where('rto.id',$id)
        ->get()
        ->first();
        if ($rtoData['sale_id'] > 0) {
             $financer_name = RtoModel::select('sale_finance_info.name as financer_name')
        ->join("sale_finance_info",function($join){
            $join->on("rto.sale_id","=","sale_finance_info.sale_id");
        })
        ->where('rto.id',$id)
        ->get()
        ->first();
        }else{
            $financer_name = "";
        }
    }
         $data = array(
            'financer_name' => $financer_name,
            'rtoData' => $rtoData,
            'layout' => 'layouts.main'
        );
        return view('admin.rto.rc_correction_request',$data);
    }

    public function rcCorrectionRequest_Db(Request $request) {
        try 
        { 
            $this->validate($request,[
                'payment_amount'=>'required',
                'correction_reason'=>'required',
            ],[
                'payment_amount.required'=> 'This is required.',  
                'correction_reason.required'=> 'This is required.',  
            ]);
            DB::beginTransaction();
            $rto_id = $request->input('rto_id');
            $rtoData = RtoModel::select("rto.*","sale.hypo","sale.sale_no","sale.store_id",
                        "customer.name as customer_name","customer.relation_type",'customer.relation',
                        'customer.address','customer.mobile','sale_order.product_frame_number','rto.created_by',
                        'rto.approved_by','creadeUser.name as create_staff_name','approveUser.name as approve_staff_name')
                ->join("sale",function($join){
                    $join->on("rto.sale_id","=","sale.id");
                })
                ->join("customer",function($join){
                    $join->on("rto.customer_id","=","customer.id");
                })
                ->join("sale_order",function($join){
                    $join->on("rto.sale_id","=","sale_order.sale_id");
                })
                ->join("users as creadeUser",function($join){
                    $join->on("rto.created_by","=","creadeUser.id");
                })
                ->join("users as approveUser",function($join){
                    $join->on("rto.approved_by","=","approveUser.id");
                })
                ->where('rto.id',$rto_id)
                ->get()
                ->first();
            if ($request->input('mistake_by') == 'staff'){
                if ( $request->input('mistake_create') == "" && $request->input('mistake_approve') == "") {
                    DB::rollback();
                    return redirect('/admin/rto/rc/correction/request/'.$rto_id)->with('error','Please check Enter By or Approved By or both ');
                    
                }elseif ($request->input('mistake_create') != "" && $request->input('mistake_approve') == "") {
                    $mistake_create = $request->input('mistake_create');
                    $mistake_approve = 0;
                }elseif ($request->input('mistake_create') == "" && $request->input('mistake_approve') != "") {
                    $mistake_create = 0;
                    $mistake_approve = $request->input('mistake_approve');
                }else{
                    $mistake_create = 0;
                    $mistake_approve = 0;
                }
            }else{
                $mistake_create = 0;
                    $mistake_approve = 0;
            }
            if($rtoData->customer_name == $request->input('customer') && $rtoData->address == $request->input('address') && $rtoData->relation_type == $request->input('relation_type') && $rtoData->relation == $request->input('relation_name')){
                DB::rollback();
                return redirect('/admin/rto/rc/correction/request/'.$rto_id)->with('error','Please change any field for your correction !');
            }else{
                if ($request->input('customer_checkbox') == 'on'){
                    $customer = $request->input('customer');
                }else{$customer = null;}
                if($request->input('add_checkbox') == 'on'){
                    $address = $request->input('address');
                } else{$address = null;}
                if($request->input('rt_checkbox') == 'on'){
                    $relation_type = $request->input('relation_type');
                } else{$relation_type = null;}
                if($request->input('rn_checkbox') == 'on'){
                    $relation_name = $request->input('relation_name');
                }else{ $relation_name = null;}
                if($request->input('hypo_checkbox') == 'on'){
                    $hypo = $request->input('hypo');
                }else{$hypo = null;}

                if ($request->input('payment_amount')) {
                    $amount = $request->input('payment_amount');
                }
                
                $request = RcCorrectionRequest::insertGetId(
                    [
                        'rto_id'=>$request->input('rto_id'),
                        'payment_amount'=>$amount,
                        'correction_reason'=>$request->input('correction_reason'),
                        'mistake_by'=>$request->input('mistake_by'),
                        'mistake_createdby'=> $mistake_create,
                        'mistake_approvedby'=> $mistake_approve,
                        'customer'=>$customer,
                        'address'=>$address,
                        'mobile' => $request->input('mobile'),
                        'relation_type'=>$relation_type,
                        'relation_name'=>$relation_name,
                        'frame_number'=>$rtoData->product_frame_number,
                        'hypo'=>$hypo,
                        'status' => 'pending'
                    ]
                );
            }
            if($request == NULL) {
                DB::rollback();
                return redirect('/admin/rto/rc/correction/request/'.$rto_id)->with('error','some error occurred');
            } 
            else{

                    $payment = PaymentRequest::insertGetId([
                        'store_id' => $rtoData['store_id'],
                        'type_id' => $request,
                        'type' => 'RcCorrection',
                        'amount' => $amount,
                        'status' => 'pending'
                    ]);

                    if ($payment == NULL) {
                        DB::rollback();
                        return redirect('/admin/rto/rc/correction/request/'.$rto_id)->with('error','some error occurred.');
                    }else{
                        /* Add action Log */
                        CustomHelpers::userActionLog($action='RTO RC Correction Request',$request,$rtoData->customer_id);
                        DB::commit();
                        return redirect('/admin/rto/rc/list')->with('success','RC correction request send. ');
                }
            }    
        
        }  catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return redirect('/admin/rto/rc/correction/request/'.$rto_id)->with('error','some error occurred'.$ex->getMessage());
        }
    }

    public function rcCorrectionRequest_list() {
         $data = array(
            'layout' => 'layouts.main'
        );
        return view('admin.rto.rc_correction_request_list',$data);
    }

    public function rcCorrectionRequestList_api(Request $request) {
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
            $api_data = RcCorrectionRequest::select('rc_correction_request.id','rc_correction_request.mistake_by','rc_correction_request.mistake_createdby','rc_correction_request.customer','rc_correction_request.address','rc_correction_request.relation_type','rc_correction_request.relation_name','rc_correction_request.frame_number','rc_correction_request.hypo','rc_correction_request.payment_amount','rc_correction_request.correction_reason','rc_correction_request.status',DB::raw('sum(payment.amount) as amount'))
            ->leftJoin('payment',function($join){
                $join->on('payment.type_id','=','rc_correction_request.id')
                    ->where('payment.type','RcCorrection')
                    ->where('payment.status','<>','cancelled');
            })
            ->groupBy('rc_correction_request.id');
            
    
            if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('rc_correction_request.mistake_by','like',"%".$serach_value."%")
                    ->orwhere('rc_correction_request.mistake_createdby','like',"%".$serach_value."%") 
                    ->orwhere('rc_correction_request.customer','like',"%".$serach_value."%")
                    ->orwhere('rc_correction_request.relation_type','like',"%".$serach_value."%")
                    ->orwhere('rc_correction_request.relation_name','like',"%".$serach_value."%")
                    // ->orwhere('rc_correction_request.frame_numbe','like',"%".$serach_value."%")
                    ->orwhere('rc_correction_request.hypo','like',"%".$serach_value."%")
                    ->orwhere('rc_correction_request.payment_amount','like',"%".$serach_value."%")
                    ->orwhere('rc_correction_request.correction_reason','like',"%".$serach_value."%")
                    ->orwhere('rc_correction_request.status','like',"%".$serach_value."%")                   
                    ;
                });
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                'rc_correction_request.id',
                'rc_correction_request.mistake_by',
                'rc_correction_request.mistake_createdby',
                'rc_correction_request.customer',
                'rc_correction_request.address',
                'rc_correction_request.relation_type',
                'rc_correction_request.relation_name',
                'rc_correction_request.frame_number',
                'rc_correction_request.hypo',
                'rc_correction_request.payment_amount',
                'rc_correction_request.correction_reason',
                'rc_correction_request.status'
                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('rc_correction_request.id','desc');      
        
        $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

    public function rcCorrectionRequest_Approve($id){
         try {
            $reqData = RcCorrectionRequest::where('id',$id)->get()->first();
            $rtoData = RtoSummary::where('rto_id',$reqData->rto_id)->get()->first();
            $customer = RtoModel::where('id',$reqData->rto_id)->get('customer_id')->first();
            $cust_id = $customer->customer_id;
           if ($reqData) {

                $rcupdate = RcCorrectionRequest::where('id',$id)->update(
                [
                    'status'=> 'approved'
                ]
            );
        
            if ($rcupdate > 0) {
                 $rtoUpdate = RtoModel::where('id',$reqData->rto_id)->update(
                [
                    'rc_correction_id' => $id,
                    'file_submission' => 0
                ]);

                 if ($rtoUpdate > 0) {
                     $rtoSummeryUpdate = RtoSummary::where('rto_id',$reqData->rto_id)->update(
                        [
                            'currentStatus' => 0
                        ]);

                     if ($rtoSummeryUpdate > 0) {
                            $addSummery_id = RtoSummary::insertGetId(
                                [
                                    'rto_id' => $rtoData->rto_id,
                                    'customer_name' => $rtoData->customer_name,
                                    'relation_type' => $rtoData->relation_type,
                                    'relation' => $rtoData->relation,
                                    'mobile' => $rtoData->mobile,
                                    'address' => $rtoData->address,
                                    'currentStatus' => 1,
                                    'created_by' => Auth::id()
                                ]
                            );
                        if ($addSummery_id > 0) {
                            if ($reqData->customer == null){
                                $customer = $rtoData->customer_name;
                            }else{$customer = $reqData->customer;}
                            if($reqData->address == null){
                                $address = $rtoData->address;
                            } else{$address = $reqData->address;}
                            if($reqData->relation_type == null){
                                $relation_type = $rtoData->relation_type;
                            } else{$relation_type = $reqData->relation_type;}
                            if($reqData->relation_name == null){
                                $relation_name = $rtoData->relation;
                            }else{ $relation_name = $reqData->relation_name;}
                            
                              $addSummery = RtoSummary::where('id',$addSummery_id)->update(
                                [
                                    'rto_id' => $reqData->rto_id,
                                    'customer_name' => $customer,
                                    'relation_type' => $relation_type,
                                    'relation' => $relation_name,
                                    'mobile' => $rtoData->mobile,
                                    'address' => $address
                                ]
                            );
                              if ($addSummery > 0) {
                                  $updateCustomer = Customers::where('id',$cust_id)->update(
                                    [
                                        'name' => $customer,
                                        'relation_type' => $relation_type,
                                        'relation' => $relation_name,
                                        'mobile' => $rtoData->mobile,
                                        'address' => $address
                                    ]
                                );
                                /* Add action Log */
                                CustomHelpers::userActionLog($action='RC Correction Request Approved',$reqData->id,$cust_id);
                                  DB::commit();
                                  return 'success';
                              }else{
                                DB::rollback();
                                return 'error';
                              }                            
                        }else{
                            DB::rollback();
                            return 'error';
                        }
                     }else{
                        DB::rollback();
                        return 'error';
                     }
                 }else{
                    DB::rollback();
                   return 'error';
                 }
            }else{
                DB::rollback();
                return 'error';
            }
        }else{
            DB::rollback();
            return 'error';
        }
           
     }  catch(\Illuminate\Database\QueryException $ex) {
        return 'error';
     }
    }

    public function rcCorrectionRequest_Pay($id) {
        $correctionData = RcCorrectionRequest::leftJoin('rto','rto.id','rc_correction_request.rto_id')
        ->leftJoin('sale','sale.id','rto.sale_id')
        ->select("rc_correction_request.*",'sale.store_id')
        // ->join("users as creadeUser",function($join){
        //     $join->on("rc_correction_request.mistake_createdby","=","creadeUser.id");
        // })
        // ->join("users as approveUser",function($join){
        //     $join->on("rc_correction_request.mistake_approvedby","=","approveUser.id");
        // })
        ->where('rc_correction_request.id',$id)
        ->get()
        ->first();
        $paid = Payment::where('type_id',$id)->where('type','RcCorrection')->where('status','<>','cancelled')->sum('amount');
        $pay_mode = Master::where('type','payment_mode')->get();
        $data = array(
            'paid' => $paid,
            'pay_mode'  =>  $pay_mode,
            'correctionData' => $correctionData,
            'layout' => 'layouts.main'
        );
        return view('admin.rto.rc_correction_request_pay',$data);
    }

    public function rcCorrectionRequestPay_DB(Request $request) {
         try {
            $timestamp = date('Y-m-d G:i:s');
            $this->validate($request,[
                'amount'=>'required',
                'payment_mode'=>'required',
                'transaction_number'=>'required',
                'transaction_charges'=>'required',
                'receiver_bank_detail'=>'required',
            ],[
                'amount.required'=> 'This field is required.', 
                'payment_mode.required'=> 'This field is required.', 
                'transaction_number.required'=> 'This field is required.', 
                'transaction_charges.required'=> 'This field is required.', 
                'receiver_bank_detail.required'=> 'This field is required.', 
            ]);
            if ($request->input('boucher_signed') == 'yes') {
                $voucher = $request->input('boucher_signed');
            }else{
                $voucher = 'no'; 
            } 
            $total_amount = RcCorrectionRequest::where('id',$request->input('rc_correction_id'))->select('payment_amount')->first();
            $paid_amount = Payment::where('type_id',$request->input('rc_correction_id'))->where('type','RcCorrection')->where('payment','<>','cancelled')->sum('amount');
            $paid_amount = $paid_amount + $request->input('amount');
            if ($total_amount->payment_amount < $paid_amount) {
                return redirect('/admin/rto/rc/correction/request/pay/'.$request->input('rc_correction_id'))->with('error','Your Amount too more .');
            }elseif($request->input('amount') == 0){
                return redirect('/admin/rto/rc/correction/request/pay/'.$request->input('rc_correction_id'))->with('error','Your Amount should be greater than 0 .');
            }
            else{
                if($request->input('payment_mode') == 'Cash')
                {
                    $arr    =   ['status'   =>  'received'];
                }
                $arr = [];
                $paydata = Payment::insertGetId(
                array_merge($arr,[
                    'type_id'=> $request->input('rc_correction_id'),
                    'type' => 'RcCorrection',
                    'payment_mode' => $request->input('payment_mode'),
                    'transaction_number' => $request->input('transaction_number'),
                    'transaction_charges' => $request->input('transaction_charges'),
                    'receiver_bank_detail' => $request->input('receiver_bank_detail'),
                    'amount' => $request->input('amount'),
                    'store_id' => $request->input('store_id'),
                    'voucher_signed' => $voucher
                ])
                );
            }
                if($paydata == NULL) {
                       return redirect('/admin/rto/rc/correction/request/pay/'.$request->input('rc_correction_id'))->with('error','some error occurred'.$ex->getMessage());
                } else{
                      return redirect('/admin/rto/rc/correction/request/pay/'.$request->input('rc_correction_id'))->with('success','Amount Successfully Paid .');
                }
               
        
        }  catch(\Illuminate\Database\QueryException $ex) {
            return redirect('/admin/rto/rc/correction/request/pay/'.$request->input('rc_correction_id'))->with('error','some error occurred'.$ex->getMessage());
        }
    }

    public function rcCorrectionRequestPay_Detail($id) {
        $correctionData = RcCorrectionRequest::where('id',$id)->get();
        $payData = Payment::where('type_id',$id)->where('type','RcCorrection')->get();
        $data = array(
            'correctionData' => $correctionData,
            'payData' => $payData,
            'layout' => 'layouts.main'
        );
        return view('admin.rto.rc_currection_pay_detail',$data);
    }

    //RTO Update
    public function rtoUpdate($id) {

        $checkrto = RtoModel::where('sale_id',$id)->first();
        if(empty($checkrto))
        {
            return redirect('/admin/sale/list')->with('error','Please Fill RTO Firstly');
        }
        if($checkrto->approve == 1)
        {
            return redirect('/admin/sale/list')->with('error','Error, RTO not be update, because RTO has been Approved.');
        } 
        $bookingData = Sale::leftJoin('product','sale.product_id','product.id')
                        ->leftJoin('store','sale.store_id','store.id')
                        ->leftJoin('customer','sale.customer_id','customer.id')
                        ->get(['sale.*','customer.name as customer_name','product.model_category','product.model_variant','product.model_name','store.name as store_name'])->where('id',$id)->first();
        // $rtoData = RtoModel::where("sale_id",$id)->get()->first();
         $data  = array(
            'bookingData' => $bookingData, 
            'rtoData' => $checkrto,
            'layout' => 'layouts.main', 
        );
        return view('admin.sale.update_rto',$data);
    }

    public function rtoUpdateDB(Request $request) {
        try {
            $this->validate($request,[
                'rto_type'=>'required',
                'rto_amount'=>'required',
                'rto_finance'=>'required',
                'rto_app_no'=>'required',
            ],[
                'rto_type.required'=> 'This is required.',
                'rto_amount.required'=> 'This is required.',  
                'rto_finance.required'=> 'This is required.',  
                'rto_app_no.required'=> 'This is required.',  
            ]);
            $customerData = Customers::where('id',$request->input('customer_id'))->get()->first();
            $getdata = RtoModel::where('sale_id', $request->input('sale_id'))->get('id')->first();
            if ($getdata['id'] > 0) {
                $rtodata = RtoModel::where('sale_id',$request->input('sale_id'))->update(
                    [
                        'customer_id'=>$request->input('customer_id'),
                        'rto_type'=>$request->input('rto_type'),
                        'rto_amount'=>$request->input('rto_amount'),
                        'rto_finance'=>$request->input('rto_finance'),
                        'application_number'=>$request->input('rto_app_no'),
                    ]
                );
            }else{
                $rtodata = RtoModel::insertGetId(
                    [
                        'sale_id'=> $request->input('sale_id'),
                        'customer_id'=>$request->input('customer_id'),
                        'rto_type'=>$request->input('rto_type'),
                        'rto_amount'=>$request->input('rto_amount'),
                        'rto_finance'=>$request->input('rto_finance'),
                        'application_number'=>$request->input('rto_app_no'),
                        'created_by' => Auth::id(),
                    ]
                );
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
            }

            if($rtodata==NULL) {
                   return redirect('/admin/sale/rto/'.$request->input('sale_id'))->with('error','some error occurred');
            } else{
                  return redirect('/admin/sale/rto/'.$request->input('sale_id'))->with('success','Successfully Created RTO .');
             }    
        
        }  catch(\Illuminate\Database\QueryException $ex) {
            return redirect('/admin/sale/rto/'.$request->input('sale_id'))->with('error','some error occurred'.$ex->getMessage());
        }
    }

    public function rtoDifferenceAmountList() {
        $data = array(
           'layout' => 'layouts.main'
       );
       return view('admin.rto.rtoDifferenceList',$data);
   }

   public function rtoDifferenceAmountList_api(Request $request) {
        
       $search = $request->input('search');
       $serach_value = $search['value'];
       $start = $request->input('start');
       $limit = $request->input('length');
       $offset = empty($start) ? 0 : $start ;
       $limit =  empty($limit) ? 10 : $limit ;
       
        $api_data = RtoModel::Join('sale','rto.sale_id','sale.id')
                            ->leftJoin('rto_summary','rto_summary.rto_id','rto.id')
                            ->leftJoin('sale_order','sale_order.sale_id','sale.id')
            ->where('sale.tos','new')
            ->whereNotNull('rto.application_number')
            ->select(
                'rto.id',
                'sale.sale_no',
                'rto_summary.customer_name',
                'sale_order.product_frame_number as frame',
                'rto.rto_type',
                'rto.application_number',
                'sale.register_price as rto_amount_in_sale',
                'rto.rto_amount',
                DB::raw("(sale.register_price-rto.rto_amount) as difference_rto_amount")
            );
    
            if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('sale.sale_no','like',"%".$serach_value."%")
                    ->orwhere('rto_summary.customer_name','like',"%".$serach_value."%")
                    ->orwhere('sale_order.product_frame_number','like',"%".$serach_value."%")
                    ->orwhere('rto.rto_type','like',"%".$serach_value."%")
                    ->orwhere('rto.application_number','like',"%".$serach_value."%")
                    ->orwhere('sale.register_price','like',"%".$serach_value."%")
                    ->orwhere('rto.rto_amount','like',"%".$serach_value."%")
                    ->orwhere(DB::raw("(sale.register_price-rto.rto_amount)"),'like',"%".$serach_value."%");
                });
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                    'sale.sale_no',
                    'rto_summary.customer_name',
                    'sale_order.product_frame_number',
                    'rto.rto_type',
                    'rto.application_number',
                    'sale.register_price',
                    'rto.rto_amount',
                    DB::raw("(sale.register_price-rto.rto_amount)")
                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('rto.id','desc');     
       
       $count = $api_data->count();
       $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
       $array['recordsTotal'] = $count;
       $array['recordsFiltered'] = $count;
       $array['data'] = $api_data; 
       return json_encode($array);
   }

   public function RtoView($id) {
        $checkrto = RtoModel::where('id',$id)->first();
        if (empty($checkrto)) {
            return redirect('/admin/rto/rc/list')->with('error','Recorde not found.');
        }
      $rto = RtoModel::leftJoin('users','users.id','rto.approved_by')
              ->leftJoin('rto_summary','rto.id','rto_summary.rto_id')
              ->where('rto_summary.currentStatus','1')
              ->where('rto.id',$id)
              ->select('rto.*','rto_summary.customer_name as name','rto_summary.mobile', DB::raw('concat(users.name," ",ifnull( users.middle_name," ")," ",ifnull( users.last_name," ")) as approved_by'),'rto_summary.numberPlateStatus','rto_summary.rcStatus','rto_summary.numberPlateDeliveryDate','rto_summary.rcDeliveryDate')
              ->first();
        $frameData = '';
       if ($rto['main_type'] == 'hsrp') {
           $frameData = RtoModel::leftJoin('hsrp','hsrp.id','rto.type_id')
                    ->where('rto.main_type','hsrp')
                    ->where('rto.id',$id)
                    ->select('hsrp.frame')->first();
       }
       if ($rto['main_type'] == 'rto') {
           $frameData = RtoModel::leftJoin('sale','sale.id','rto.sale_id')
                    ->leftJoin('sale_order','sale_order.sale_id','rto.sale_id')
                    ->where('rto.main_type','rto')
                    ->where('rto.id',$id)
                    ->where('rto.approve','1')
                    ->select('sale_order.product_frame_number as frame','sale.sale_no')->first();

       }

      $data = [
        'frameData' => $frameData,
        'rto' => $rto,
        'layout' => 'layouts.main'
      ];
      return view('admin.rto.rto_view',$data);
   }

   public function UpdateDeliveryStatus(Request $request) {
        try{
            DB::beginTransaction();
            $rcDeliverd = '';
            $plateNoDeliverd = '';

            $user = Users::where('id',Auth::id())->select('user_type','role')->first();
            $userType = $user->user_type;
            $userRole = $user->role;

            $id = $request->input('rto_id');
            $numberPlateDelivery = $request->input('numberPlateDelivery');
            $rcDelivery = $request->input('rcDelivery');
          
            $checkBoth = RtoModel::where('rto.id',$id)
                        ->leftJoin('rto_summary',function($join) use($id){
                            $join->on('rto_summary.rto_id',DB::raw($id))
                                    ->where('rto_summary.currentStatus',1);
                        })
                        ->where(function($query) {
                            $query->whereNotNull('rto.registration_number')
                                    ->orwhereNotNull('rto.rc_number');
                        })
                        ->select('rto.id as rto_id','rto_summary.id as rto_summary_id','rto.registration_number','rto.rc_number','rto_summary.numberPlateStatus','rto_summary.rcStatus')->first();
           
            if(empty($checkBoth->rto_id) || empty($checkBoth->rto_summary_id)) {
                return redirect('/admin/rto/rc/view/'.$id.'')->with('error','RC Number and Number Plate not found.')->withInput();
            } 

            $checkDelivery = RtoModel::where('rto.id',$id)
                        ->leftJoin('rto_summary',function($join) use($id){
                            $join->on('rto_summary.rto_id',DB::raw($id))
                                    ->where('rto_summary.currentStatus',1);
                        })
                        ->where(function($query) {
                            $query->whereNotNull('rto.registration_number')
                                    ->orwhereNotNull('rto.rc_number');
                        })
                        ->where('rto_summary.numberPlateStatus','1')
                        ->where('rto_summary.rcStatus','1')
                        ->select('rto.id as rto_id','rto_summary.id as rto_summary_id','rto.registration_number','rto.rc_number','rto_summary.numberPlateStatus','rto_summary.rcStatus')->first();

            if($checkDelivery) {
                return redirect('/admin/rto/rc/view/'.$id.'')->with('error','RC Number and Number Plate already delivered.')->withInput();
            }  
            $updateData = [];
            if($numberPlateDelivery == 'yes' && ($checkBoth->numberPlateStatus == 0 || $checkBoth->numberPlateStatus == 1))
            {
                $updateData['numberPlateStatus']    =   1;
                 $updateData['numberPlateDeliveryDate']   =   date('Y-m-d');
            }
            elseif($numberPlateDelivery == 'no' ){
                if($checkBoth->numberPlateStatus == 1)
                {
                    if($userType != 'superadmin' && $userRole != 'Superadmin'){
                        return redirect('/admin/rto/rc/view/'.$id.'')->with('error','Number Plate already delivered, if you want to change please contact superadmin.')->withInput();
                    }
                }
                $updateData['numberPlateStatus']    =   0;
            }
            if($rcDelivery == 'yes' && ($checkBoth->rcStatus == 0 || $checkBoth->rcStatus == 1))
            {
                $updateData['rcStatus']   =   1;
                $updateData['rcDeliveryDate']   =   date('Y-m-d');
            }
            elseif($rcDelivery == 'no'){
                if($checkBoth->rcStatus == 1)
                {
                    if($userType != 'superadmin' && $userRole != 'Superadmin'){
                        return redirect('/admin/rto/rc/view/'.$id.'')->with('error','RC already delivered, if you want to change please contact superadmin.')->withInput();
                    }
                }
                $updateData['rcStatus']    =   0;
            }

            //if delivery for rc 
            if($rcDelivery == 'yes') {
                //check RC correction Requested or not
                $checkRcCorrectionReq = RtoModel::where('rto.id',$id)
                                        ->leftJoin('rc_correction_request',function($join){
                                            $join->on('rc_correction_request.rto_id','rto.id')
                                                    ->where('rc_correction_request.status','approved')
                                                    ->where('rc_correction_request.id',DB::raw('(select max(id) from rc_correction_request where rto_id = rto.id)'));
                                        })->select('rc_correction_request.id')->first();
                
                if(!empty($checkRcCorrectionReq->id)) {
                    //check RC 'correction request' payment paid or not, when RC Delivered to customer
                    $checkRcCorrection = RtoModel::where('rto.id',$id)
                                            ->leftJoin('rc_correction_request',function($join) use($checkRcCorrectionReq){
                                                $join->on('rc_correction_request.rto_id','rto.id')
                                                        ->where('rc_correction_request.status','approved')
                                                        ->where('rc_correction_request.id',$checkRcCorrectionReq->id);

                                            })
                                            ->leftJoin('payment',function($join) use($checkRcCorrectionReq) {
                                                $join->on('payment.type_id',DB::raw($checkRcCorrectionReq->id))
                                                    ->where('payment.type','RcCorrection');
                                            })
                                            ->select('rto.id','rc_correction_request.payment_amount as correctionAmount',
                                                DB::raw('IFNULL(sum(payment.amount),0) as paidAmount'))
                                            ->first();
                    
                    if($checkRcCorrection->paidAmount != $checkRcCorrection->correctionAmount) {
                        return redirect('/admin/rto/rc/view/'.$id.'')->with('error','Amount Should be paid for RC Correction.')->withInput(); 
                    }
                }
            }

            if($updateData) {
                $updateRtoSummary = RtoSummary::where('rto_id',$id)->whereRaw('currentStatus',1)->update($updateData);
                if ($updateRtoSummary == null) {
                    DB::rollback();
                     return redirect('/admin/rto/rc/view/'.$id.'')->with('error','Something went wrong.')->withInput(); 
                }
                // rto done
                $updateRto = RtoModel::where('id',$id)->update(['approve' => 3]);
                if ($updateRto == null) {
                    DB::rollback();
                     return redirect('/admin/rto/rc/view/'.$id.'')->with('error','Something went wrong.')->withInput(); 
                }

            }
        }
        catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return redirect('/admin/rto/rc/view/'.$id.'')->with('error','Something went wrong '.$ex)->withInput();
        }
        DB::commit();
        return redirect('/admin/rto/rc/view/'.$id.'')->with('success','Successfully Delivered')->withInput();
   }

}
