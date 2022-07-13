<?php

namespace App\Http\Controllers\admin;
use Illuminate\Http\Request;
use \stdClass;
use \App\Model\Store;
use \App\Custom\CustomHelpers;
use App\Model\Users;
use \App\Model\PaymentRequest;
use \App\Model\Sale;
use \App\Model\SaleDiscount;
use \App\Model\BestDealSale;
use \App\Model\ApprovalRequest;
use \App\Model\ApprovalSetting;
use \App\Model\InsuranceRenewalOrder;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Auth;
use Hash;
use \Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\Import;
use App\Model\Insurance;
use \App\Model\OtcSale;
use \App\Model\OtcSaleDetail;
use App\Model\InsuranceRenewalDetail;
use App\Model\ProductModel;
use App\Model\SaleOrder;
use App\Model\ServiceCharge;
use Illuminate\Database\Eloquent\Model;

class ApprovalController extends Model
{
    public function __construct()
    {
        //$this->middleware('auth');
    }
    public function approval_list(){
        $store = Store::whereIn('id',CustomHelpers::user_store())
                    ->select('id',DB::raw('concat(store.name,"-",store.store_type) as name'))->get();
        $data = array(
                'storeData' => $store,
                'layout' => 'layouts.main'
                );
        return view('admin.approve_setting.approval_list',$data);
    }
    public function approval_list_api(Request $request) {
        $store_id = $request->input('store_id');
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
        
        $auth_id = Auth::id();
        $store = Store::whereIn('id',CustomHelpers::user_store())->select('id')->get()->toArray();
        $api_data = ApprovalRequest::join('approval_setting','approval_setting.type','approval_request.sub_type')
                ->whereIn('approval_request.store_id',$store)
                ->where(function($query) use($auth_id){
                    $query->whereRaw('FIND_IN_SET('.$auth_id.',approval_setting.level3)')
                    ->orwhereRaw('FIND_IN_SET('.$auth_id.',approval_setting.level2)')
                    ->orwhereRaw('FIND_IN_SET('.$auth_id.',approval_setting.level1)');
                })
                ->leftjoin('store','store.id','approval_request.store_id')
                ->select('approval_request.id',
                'approval_request.type',
                'approval_request.sub_type',
                'approval_setting.name',
                DB::raw('IF(approval_request.level3 = 0,"NONE",IFNULL((select name from users where id = approval_request.level3),"NONE")) level3'),        
                DB::raw('IF(approval_request.level2 = 0,"NONE",IFNULL((select name from users where id = approval_request.level2),"NONE")) level2'),       
                DB::raw('IF(approval_request.level1 = 0,"NONE",IFNULL((select name from users where id = approval_request.level1),"NONE")) level1'),
                'approval_request.status',
                'approval_request.store_id',
                'approval_request.created_at',
                'approval_request.level1 as l1_id',
                'approval_request.level2 as l2_id',
                'approval_request.level3 as l3_id',
                DB::raw("".$auth_id." as my_id"),
                DB::raw("IF(FIND_IN_SET(".$auth_id.",approval_setting.level1) = 0, 'NO', 'YES') as setting_l1"),
                DB::raw("IF(FIND_IN_SET(".$auth_id.",approval_setting.higher) = 0, 'NO', 'YES') as setting_higher")
                // 'approval_setting.level1 as setting_l1',
                // 'approval_setting.higher as setting_higher',
                    
                );
           
            if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('approval_setting.name','like',"%".$serach_value."%")
                    ->orwhere(DB::raw('IF(approval_request.level3 = 0,"NONE",IFNULL((select name from users where id = approval_request.level3),"NONE"))'),'like',"%".$serach_value."%")
                    ->orwhere(DB::raw('IF(approval_request.level2 = 0,"NONE",IFNULL((select name from users where id = approval_request.level2),"NONE"))'),'like',"%".$serach_value."%")
                    ->orwhere(DB::raw('IF(approval_request.level1 = 0,"NONE",IFNULL((select name from users where id = approval_request.level1),"NONE"))'),'like',"%".$serach_value."%")
                    ->orwhere('approval_request.status','like',"%".$serach_value."%")
                    ->orwhere('approval_request.created_at','like',"%".$serach_value."%");
                });
            }
             if(!empty($store_id))
            {
               $api_data->where(function($query) use ($store_id){
                        $query->where('approval_request.store_id','like',"%".$store_id."%");
                    });
               
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                    'approval_setting.name',
                    'approval_request.level3',
                    'approval_request.level2',
                    'approval_request.level1',
                    'approval_request.status',
                    'approval_request.created_at'
                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('approval_request.created_at','desc');      
        
        $count = $api_data->count();
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

    public function approval_list_info(Request $request){
        $requestId = $request->input('requestId');
        try{
            $check = ApprovalRequest::where('id',$requestId)->first();
            if(!isset($check->id)){
                return response()->json('Wrong Requested Id.',401);
            }
            $type = $check->type;
            $type_id = $check->type_id;
    
            $sub_type = $check->sub_type;
            $sub_type_id = $check->sub_type_id;

            $response_arr = [];

            if($type == 'sale'){
                $sale = Sale::where('sale.id',$type_id)
                        ->leftJoin('customer','customer.id','sale.customer_id')
                        ->leftJoin('product','product.id','sale.product_id')
                        ->select('sale.id','sale.sale_no','sale.sale_date','sale.total_amount','sale.balance',
                        'customer.name',
                        DB::raw("concat(product.model_name,'-',product.model_variant,'-',product.color_code) as product")
                        )
                        ->first();
                if(!isset($sale->id)){
                    return response()->json('Sale Data Not Found.',401);
                }
                $response_arr['Sale #'] = $sale->sale_no;
                $response_arr['Sale Date'] = $sale->sale_date;
                $response_arr['Product Info'] = $sale->product;
                $response_arr['Customer Name'] = $sale->name;
                $response_arr['Total Sale Amount'] = $sale->total_amount;
                $response_arr['Total Balance Amount'] = $sale->balance;

                if($sub_type == 'sale_discount')
                {
                    $discount = SaleDiscount::where('id',$sub_type_id)->first();
                    if(!isset($discount->id)){
                        return response()->json("Sale Discount Data Not Found.",401);
                    }
                    $response_arr['Discount Type']  =   $discount->discount_type;
                    $response_arr['amount']  =   $discount->amount;
                }

                if($sub_type == 'bestdeal_sale_discount')
                {
                    $bds = BestDealSale::where('id',$sub_type_id)->select('id','value')->first();
                    if(!isset($bds->id)){
                        return response()->json("BestDeal Sale Discount Data Not Found.",401);
                    }
                    $response_arr['Discount Type']  =   'BestDeal Sale Discount';
                    $response_arr['amount']  =   $bds->value;
                }
            }elseif($type == 'bestdeal'){
                $bd = BestDealSale::where('id',$type_id)->first();
                if(!isset($bd->id)){
                    return response()->json('BestDeal Data Not Found.',401);
                }
                $response_arr['Name']   =   $bd->name;
                $response_arr['Position']   =   $bd->position;
                $response_arr['Mobile #']   =   $bd->number;
                $response_arr['Address']   =   $bd->address;
                if(!empty($bd->aadhar)){
                    $response_arr['Aadhar']   =   $bd->aadhar;
                }
                if(!empty($bd->aadhar)){
                    $response_arr['Voter']   =   $bd->voter;
                }
                $response_arr['PAN']   =   $bd->pan;
                $response_arr['K.M.']   =   $bd->km;
                $response_arr['Maker']   =   $bd->maker;

                $response_arr['Model']   =   $bd->model;
                $response_arr['Variant']   =   $bd->variant;
                $response_arr['Color Code']   =   $bd->color;

                if($bd->product_id == 0){
                    $get = ProductModel::where('id',$bd->product_id)
                                    ->select(
                                        'model_name',
                                        'model_variant',
                                        'color_code'
                                    )->first();
                    if(isset($get->model_name)){
                        $response_arr['Model']   =   $get->model_name;
                        $response_arr['Variant']   =   $get->model_variant;
                        $response_arr['Color Code']   =   $get->color_code;
                    }
                }

                $response_arr['Frame #']   =   $bd->frame;
                $response_arr['Registration #']   =   $bd->register_no;
                $response_arr['RC Status']   =   $bd->rc_status;
                $response_arr['Price']   =   $bd->value;
                
            }
            elseif($type == 'service'){
                if($sub_type == 'service_discount')
                {
                    $check = ServiceCharge::where('id',$sub_type_id)->first();
                    if(!isset($check->id)){
                        return response()->json('Service Discount Data Not Found.',401);
                    }
                    $response_arr['Discount Type'] = $check->sub_type;
                    $response_arr['Amount'] = $check->amount;
                }
            }
            elseif($type == 'insurance_renewal'){
                // echo "hii";die;
                $check = InsuranceRenewalOrder::where('insurance_renewal_order.id',$type_id)
                                    ->leftJoin('insurance as od_ins','od_ins.id','insurance_renewal_order.od_insurance_id')
                                    ->leftJoin('insurance as tp_ins','tp_ins.id','insurance_renewal_order.tp_insurance_id')
                                    ->leftJoin('insurance_data','insurance_data.id','insurance_renewal_order.insurance_data_id')
                                    ->leftJoin('customer','customer.id','insurance_data.customer_id')
                                    ->select(
                                        'insurance_renewal_order.id',
                                        'insurance_data.id as insurance_data_id',
                                        'insurance_data.sale_id',
                                        'customer.name',
                                        'od_ins.policy_tenure as od_pt',
                                        'tp_ins.policy_tenure as tp_pt'
                                    )->first();
                if(!isset($check->id)){
                    return response()->json('Insurance Renewal Data not Found.',401);
                }

                $response_arr['Customer Name'] = $check->name;
                $product_id = 0;
                if($check->sale_id > 0){
                    $sale = Sale::where('id',$check->sale_id)->select('product_id')->first();
                    if(!isset($sale->product_id)){
                        return response()->json('Something Went Wrong, Product Data Not Found.',401);
                    }
                    $order = SaleOrder::where('sale_id',$check->sale_id)->select('product_frame_number')->first();
                    if(!isset($order->product_frame_number)){
                        return response()->json('Something Went Wrong, Product Data Not Found.',401);
                    }
                    $response_arr['Frame #'] = $order->product_frame_number;
                    
                }else{
                    $ir_details = InsuranceRenewalDetail::where('insurance_data_id',$check->insurance_data_id)
                                    ->select('frame_number','product_id')->first();
                    if(!isset($ir_details->frame_number)){
                        return response()->json('Something Went Wrong, Inusrance Renewal Details Data Not Found.',401);
                    }
                    $response_arr['Frame #'] = $ir_details->frame_number;
                    $product_id = $ir_details->product_id;
                }
                $product = ProductModel::where('id',$product_id)
                                ->selectRaw("concat(product.model_name,'-',product.model_variant,'-',product.color_code) as product")->first();
                if(!isset($product->product)){
                    return response()->json('Something Went Wrong, Product Data Not Found.',401);
                }
                $response_arr['Product Name'] = $product->product;

                $response_arr['OD Policy Tenure'] = $check->od_pt;
                $response_arr['TP Policy Tenure'] = $check->tp_pt;
            }elseif ($type == 'otcsale') {
                DB::enableQueryLog();
                $otcsale = OtcSale::where('otc_sale.id',$type_id)
                        ->leftJoin('customer','customer.id','otc_sale.customer_id')
                        ->leftJoin('sale_order','sale_order.sale_id','otc_sale.sale_id')
                        ->leftjoin('store','store.id','otc_sale.store_id')
                        ->leftJoin('otc_sale_detail as osd_part',function($join){
                                    $join->on('osd_part.otc_sale_id','=','otc_sale.id')
                                    ->where('osd_part.type','Part');
                        })
                        ->leftjoin('part','part.id','osd_part.part_id')
                        ->select('otc_sale.id','sale_order.product_frame_number','otc_sale.total_amount','otc_sale.balance',
                        'customer.name','otc_sale.ew_cost','otc_sale.ew_duration','otc_sale.amc_cost','otc_sale.hjc_cost',
                        DB::raw("concat(store.name,'-',store.store_type) as storename"),DB::raw('GROUP_CONCAT(Concat(part.name,"(",osd_part.qty,")")) as parts')
                        )->first();
                if(!isset($otcsale->id)){
                    return response()->json('OTC Sale Data Not Found.',401);
                }

                $response_arr['Frame #'] = $otcsale->product_frame_number;
                $response_arr['Store Name'] = $otcsale->storename;
                $response_arr['Accessories'] = $otcsale->parts;
                $response_arr['Customer Name'] = $otcsale->name;
                
                if(isset($otcsale->hjc_cost)){                        
                    $response_arr['HJC'] = $otcsale->hjc_cost;
                }
                if(isset($otcsale->ew_cost)){                        
                    $response_arr['Extended Warrenty Cost'] = $otcsale->ew_cost;
                    $response_arr['Extended Warrenty Duration'] = $otcsale->ew_duration;
                }
                if(isset($otcsale->amc_cost)){                        
                    $response_arr['AMC'] = $otcsale->amc_cost;
                }
                if($sub_type == 'otc_sale_discount')
                {
                    $discount = SaleDiscount::where('id',$sub_type_id)->first();
                    if(!isset($discount->id)){
                        return response()->json("Sale Discount Data Not Found.",401);
                    }
                    
                    $response_arr['Discount Amount']  =   $discount->amount;
                }
                $response_arr['Total Sale Amount'] = $otcsale->total_amount;
                $response_arr['Total Balance Amount'] = $otcsale->balance;
                        
            }
            if(count($response_arr) > 0){
                return response()->json([true,$response_arr]);
            }
            return response()->json("Something Went Wrong, Not Found Any Details.",401);

        }
        catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return response()->json('Something went wrong.'.$ex->getMessage(),401);
        }
    }

    public function approveDB(Request $request){
        $requestId = $request->input('requestId');
        try{
            DB::beginTransaction();
            $check_request = ApprovalRequest::where('id',$requestId)
                                ->first();
            // ----------------- validate       ---------------------------
            if(!isset($check_request->id))
            {
                return 'Request Invalid';
            }
            if($check_request->status != 'Pending' )
            {
                return 'Already Approved';
            }
            $check_auth = CustomHelpers::check_approve_auth($check_request->type);
            if(!$check_auth)
            {
                return 'You Are Not Authorized.';
            }
            // ------------------------------------
            $approve_request = CustomHelpers::approve_request($check_request->id,$check_request->type);
            if(!$approve_request[0])
            {
                DB::rollback();
                return response()->json($approve_request[1],401);
            }
            $status=  $approve_request[2];
            if($check_request->type == 'best_deal_purchase' && $status == 'Approved')
            {
                $get_pur_sale_id = BestDealSale::where('id',$check_request->type_id)
                                            ->select('purchase_sale_id','value','status')
                                            ->where('status','Pending')
                                            ->first();
                if(!isset($get_pur_sale_id->purchase_sale_id))
                {
                    DB::rollback();
                    return 'Best Deal Not-Found.';
                }
                if($get_pur_sale_id->purchase_sale_id > 0 )
                {   
                    $bd_value = $get_pur_sale_id->value;
                    // sale check in sale_discount have any approveal required ?
                    $get_sale = Sale::where('sale.id',$get_pur_sale_id->purchase_sale_id)
                                            ->leftjoin("sale_discount",function($join){
                                                $join->on("sale_discount.type_id","=","sale.id")
                                                        ->where('sale_discount.type','sale')
                                                        ->where('sale_discount.discount_type','Normal');
                                            })
                                            ->leftjoin("approval_request",function($join){
                                                $join->on("approval_request.type_id","=","sale_discount.id")
                                                        ->where('approval_request.type','sale_discount');
                                            })
                                            ->select('sale.balance',
                                                        'approval_request.status',
                                                        'sale.id as sale_id',
                                                        'sale_discount.amount as discount_am',
                                                        'sale.store_id as store'
                                            )->first();
                    if($get_sale->status != 'Pending')  // if sale dscount it's approved or not requested for approval
                    {
                        $balance = $get_sale->balance-$bd_value;
                        if($get_sale->status == 'Approved' || empty($get_sale->status))
                        {
                            $balance = $balance-$get_sale->discount_am;
                            //update in sale
                            $updateSale = Sale::where('id',$get_sale->sale_id)
                                                ->update(['balance' => $balance]);
                            $insertPaymentReq = PaymentRequest::insertGetId([
                                                    'store_id'  =>  $get_sale->store,
                                                    'type'  =>  'sale',
                                                    'type_id'   =>  $get_pur_sale_id->purchase_sale_id,
                                                    'amount'    =>  $balance
                                                ]);
                            if(!$insertPaymentReq)
                            {
                                DB::rollback();
                                return response()->json('Something Went Wrong');
                            }
                        }
                    }
                    // else{      // if sale discount requested for approval and still not approved

                    // }
                }
            }
            elseif($check_request->type == 'sale_discount' && $status == 'Approved'){
                $sale_discount = SaleDiscount::where('id',$check_request->type_id)
                                                ->where('type','sale')
                                                ->where('discount_type','Normal')
                                                ->where('status','pending')
                                                ->select('type_id','id','amount as discount_am')->first();
                if(!isset($sale_discount->id))
                {
                    DB::rollback();
                    return response()->json('Sale Discount Not-Found.',401);
                }
                // sale check have bestdeal ?
                $get_sale = Sale::where('sale.id',$sale_discount->type_id)
                            ->leftjoin("best_deal_sale",function($join){
                                $join->on("best_deal_sale.purchase_sale_id","=","sale.id")
                                        ->where('best_deal_sale.tos','best_deal');
                            })
                            ->leftjoin("approval_request",function($join){
                                $join->on("approval_request.type_id","=","best_deal_sale.id")
                                        ->where('approval_request.type','best_deal_purchase');
                            })
                            ->select('sale.balance',
                                        'approval_request.status','sale.id as sale_id','best_deal_sale.value',
                                        'sale.store_id as store'
                            )->first();
                $balance = $get_sale->balance;
                $bd_value = $get_sale->value;
                if($get_sale->status == 'Approved')
                {
                        $balance = $balance-$sale_discount->discount_am;
                       
                        $balance = $balance-$bd_value;
                        //update in sale
                        $updateSale = Sale::where('id',$get_sale->sale_id)
                                                ->update(['balance' => $balance]);
                        
                        $insertPaymentReq = PaymentRequest::insertGetId([
                            'store_id'  =>  $get_sale->store,
                            'type'  =>  'sale',
                            'type_id'   =>  $get_sale->sale_id,
                            'amount'    =>  $balance
                        ]);
                        if(!$insertPaymentReq)
                        {
                            DB::rollback();
                            return response()->json('Something Went Wrong');
                        }
                        $update_discount = SaleDiscount::where('id',$sale_discount->id)
                                                            ->update(['status'  =>  'approve']);
                }elseif($get_sale->status == 'Pending'){
                    $update_discount = SaleDiscount::where('id',$sale_discount->id)
                                                            ->update(['status'  =>  'approve']);
                }elseif(empty($get_sale->status)){
                    $balance = $balance-$sale_discount->discount_am;
                    //update in sale
                    $updateSale = Sale::where('id',$get_sale->sale_id)
                                            ->update(['balance' => $balance]);
                    $update_discount = SaleDiscount::where('id',$sale_discount->id)
                                            ->update(['status'  =>  'approve']);
                    $updatePaymentReq = PaymentRequest::where('type','sale')
                                        ->where('type_id',$sale_discount->type_id)
                                        ->update([
                                            'store_id'  =>  $get_sale->store,
                                            'amount'    =>  $balance
                                        ]);
                }
            }
            elseif($check_request->type == 'bestdeal_sale_discount' && $status == 'Approved'){
                $sale_discount = SaleDiscount::where('id',$check_request->type_id)
                                                ->where('status','pending')
                                                ->select('type_id','id','amount as discount_am')->first();
                if(!isset($sale_discount->id))
                {
                    DB::rollback();
                    return response()->json('Best Deal Sale Discount Not-Found.',401);
                }
                // sale check have bestdeal ?
                $get_sale = Sale::where('id',$sale_discount->type_id)
                            ->select('sale.balance',
                                'sale.id as sale_id')->first();
                $balance = $get_sale->balance;

                $balance = $balance-$sale_discount->discount_am;
                
                //update in sale
                $updateSale = Sale::where('id',$get_sale->sale_id)
                                            ->update(['balance' => $balance]);
                $updatePaymentReq = PaymentRequest::where('type','sale')->where('type_id',$get_sale->sale_id)
                                    ->update(['amount'  =>  $balance]);

                if(!$updatePaymentReq)
                {
                    DB::rollback();
                    return response()->json('Something Went Wrong');
                }
                $update_discount = SaleDiscount::where('id',$sale_discount->id)
                                                            ->update(['status'  =>  'approve']);
                
            }
            elseif($check_request->type == 'service_discount' && $status == 'Approved'){
                $service_discount = ServiceCharge::where('id',$check_request->type_id)
                                                ->where('status','pending')
                                                ->first();
                if(!isset($service_discount->id))
                {
                    DB::rollback();
                    return response()->json('Service Discount Not-Found.',401);
                }
               
                //update in service charge
                $update_service = ServiceCharge::where('id',$service_discount->id)
                                            ->update(['status' => 'approved']);
                $updatePaymentReq = PaymentRequest::where('type','service_discount')
                                        ->where('type_id',$service_discount->id)
                                    ->update(['status'  =>  'Approved']);

                if(!$updatePaymentReq)
                {
                    DB::rollback();
                    return response()->json('Something Went Wrong');
                }
            }


        }catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return response()->json('Something went wrong.'.$ex->getMessage(),401);
        }
        DB::commit();
        return response()->json('success');
    }
    public function cancelDB(Request $request){
        $requestId = $request->input('requestId');
        try{
            DB::beginTransaction();
            $check_request = ApprovalRequest::where('id',$requestId)
                                ->first();
            // ----------------- validate       ---------------------------
            if(!isset($check_request->id))
            {
                return 'Invalid Request.';
            }
            if($check_request->status == 'Cancelled' )
            {
                return 'Already Cancelled.';
            }
            if($check_request->status == 'Approved' )
            {
                return 'After Approved Will Not Be Cancelled It.';
            }
            $check_auth = CustomHelpers::check_cancel_auth($check_request->type);
            if(!$check_auth)
            {
                return 'You Are Not Authorized.';
            }
            // ------------------------------------
            $cancel_request = CustomHelpers::cancel_request($check_request->id,$check_request->type);
            if(!$cancel_request[0])
            {
                DB::rollback();
                return response()->json($cancel_request[1],401);
            }
            $status = $cancel_request[2];
            if($check_request->type == 'best_deal_purchase' && $status == 'Cancelled')
            {
                $bestdeal = BestDealSale::where('id',$check_request->type_id)
                                            ->select('status','id','purchase_sale_id')
                                            ->where('tos','best_deal')
                                            ->where('status','Pending')->first();
                if(!isset($bestdeal->id))
                {
                    DB::rollback();
                    return 'Best Deal Not-Found.';
                }
                $get_sale = Sale::where('id',$bestdeal->purchase_sale_id)
                                    ->first();
                if(isset($get_sale->id)){

                    $updatePayReq = PaymentRequest::where('type','sale')->where('type_id',$get_sale->id)
                                                    ->update(['amount'  =>  $get_sale->balance]);
                }
                                                
                $update_bestdeal = BestDealSale::where('id',$bestdeal->id)->update([
                        'status'    =>  'Cancel'
                ]);
                if(!$update_bestdeal)
                {
                    DB::rollback();
                    return response()->json('Something Went Wrong');
                }
            }
            elseif($check_request->type == 'sale_discount' && $status == 'Cancelled'){
                $sale_discount = SaleDiscount::where('id',$check_request->type_id)
                                                ->where('status','pending')
                                                ->select('type_id','id')->first();
                if(!isset($sale_discount->id))
                {
                    DB::rollback();
                    return 'Sale Discount Not-Found.';
                }
                $update_discount = SaleDiscount::where('id',$sale_discount->id)
                                                    ->update(['status'  =>  'cancel']);
            }
            elseif($check_request->type == 'bestdeal_sale_discount' && $status == 'Cancelled'){
                $sale_discount = SaleDiscount::where('id',$check_request->type_id)
                                                ->where('status','pending')
                                                ->select('type_id','id')->first();
                if(!isset($sale_discount->id))
                {
                    DB::rollback();
                    return 'Best Deal Sale Discount Not-Found.';
                }
                $update_discount = SaleDiscount::where('id',$sale_discount->id)
                                                    ->update(['status'  =>  'cancel']);
            }
            elseif($check_request->type == 'service_discount' && $status == 'Cancelled'){
                $service_discount = ServiceCharge::where('id',$check_request->type_id)
                                                ->where('status','pending')
                                                ->first();
                if(!isset($service_discount->id))
                {
                    DB::rollback();
                    return response()->json('Service Discount Not-Found.',401);
                }
               
                $updatePaymentReq = PaymentRequest::where('type','service_discount')
                                        ->where('type_id',$service_discount->id)
                                    ->update(['status'  =>  'Cancelled']);

                if(!$updatePaymentReq)
                {
                    DB::rollback();
                    return response()->json('Something Went Wrong');
                }
            }

        }catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return response()->json('Something went wrong.'.$ex->getMessage(),401);
        }
        DB::commit();
        return response()->json('success');
    }

    public function newapproveDB(Request $request){
        $requestId = $request->input('requestId');
        try{
            DB::beginTransaction();
            $check_request = ApprovalRequest::where('id',$requestId)
                                ->first();
            // ----------------- validate       ---------------------------
            if(!isset($check_request->id))
            {
                return 'Request Invalid';
            }
            if($check_request->status != 'Pending' )
            {
                return 'Already Approved';
            }
            $check_auth = CustomHelpers::check_approve_auth($check_request->sub_type);
            if(!$check_auth)
            {
                return 'You Are Not Authorized.';
            }
            // ------------------------------------
            $approve_request = CustomHelpers::approve_request($check_request->id,$check_request->sub_type);
            if(!$approve_request[0])
            {
                DB::rollback();
                return response()->json($approve_request[1],401);
            }
            $status=  $approve_request[2];
            if($check_request->sub_type == 'best_deal_purchase' && $status == 'Approved')
            {
                $get_pur_sale_id = BestDealSale::where('id',$check_request->sub_type_id)
                                                ->where('status','Pending')
                                            // ->select('purchase_sale_id','value','status')
                                            ->first();
                if(!isset($get_pur_sale_id->id))
                {
                    DB::rollback();
                    return 'Best Deal Not-Found.';
                }
                $update_bestdeal = BestDealSale::where('id',$get_pur_sale_id->id)
                                                        ->update([
                                                            'approve'   =>  1
                                                        ]);

                if($get_pur_sale_id->purchase_sale_id > 0 )   // if bestdeal crated using sale
                {   
                    $cal = $this->calculate_for_sale($get_pur_sale_id->purchase_sale_id);
                    if(!$cal)
                    {
                        DB::rollback();
                        return response()->json('Something Went Wrong');
                    }
                }
                
            }
            elseif($check_request->sub_type == 'sale_discount' && $status == 'Approved'){
                $sale_discount = SaleDiscount::where('id',$check_request->sub_type_id)
                                                ->where('type','sale')
                                                ->where('discount_type','Normal')
                                                ->where('status','pending')
                                                ->first();
                if(!isset($sale_discount->id))
                {
                    DB::rollback();
                    return response()->json('Sale Discount Not-Found.',401);
                }

                $update_discount = SaleDiscount::where('id',$sale_discount->id)
                                                            ->update(['status'  =>  'approve']);

                $cal = $this->calculate_for_sale($sale_discount->type_id);
                if(!$cal)
                {
                    DB::rollback();
                    return response()->json('Something Went Wrong');
                }                                

            }
            elseif($check_request->sub_type == 'service_discount' && $status == 'Approved'){
                $service_discount = ServiceCharge::where('id',$check_request->sub_type_id)
                                                ->where('status','pending')
                                                ->first();
                if(!isset($service_discount->id))
                {
                    DB::rollback();
                    return response()->json('Service Discount Not-Found.',401);
                }
               
                //update in service charge
                $update_service = ServiceCharge::where('id',$service_discount->id)
                                            ->update(['status' => 'approved']);

            }elseif($check_request->sub_type == 'insurance_renewal_cancel' && $status == 'Approved'){
                $insurance_renewal = InsuranceRenewalOrder::where('id',$check_request->sub_type_id)
                                                ->where('approved','0')
                                                ->first();
                if(!isset($insurance_renewal->id))
                {
                    DB::rollback();
                    return response()->json('Insurance Renewal Not-Found.',401);
                }
               
                //update in service charge
                $update_service = InsuranceRenewalOrder::where('id',$insurance_renewal->id)
                                            ->update([
                                                'approved' => '2',
                                                'final_amount' => 0
                                            ]);

            }
            elseif($check_request->sub_type == 'insurance_renewal_edit' && $status == 'Approved'){
                $insurance_renewal = InsuranceRenewalOrder::where('id',$check_request->sub_type_id)
                                                ->where('approved','0')
                                                ->first();
                if(!isset($insurance_renewal->id))
                {
                    DB::rollback();
                    return response()->json('Insurance Renewal Not-Found.',401);
                }
               
                //update in service charge
                $update_service = InsuranceRenewalOrder::where('id',$insurance_renewal->id)
                                            ->update([
                                                'approved' => '1',
                                                 'final_amount' => 0
                                            ]);

            }elseif($check_request->sub_type == 'otc_sale_discount' && $status == 'Approved'){
                $otcsale_discount = SaleDiscount::where('id',$check_request->sub_type_id)
                                                ->where('type','otcsale')
                                                ->where('discount_type','otcsaleDiscount')
                                                ->where('status','pending')
                                                ->first();
                                                
                if(!isset($otcsale_discount->id))
                {
                    DB::rollback();
                    return response()->json('OTC Sale Discount Not-Found.',401);
                }

                $update_discount = SaleDiscount::where('id',$otcsale_discount->id)
                                                            ->update(['status'  =>  'approve']);

                $cal = $this->calculate_for_otcsale($otcsale_discount->type_id);

                if(!$cal)
                {
                    DB::rollback();
                    return response()->json('Something Went Wrong');
                }                                

            }



        }catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return response()->json('Something went wrong.'.$ex->getMessage(),401);
        }
        DB::commit();
        return response()->json('success');
    }
    public function newcancelDB(Request $request){
        $requestId = $request->input('requestId');
        $type = $request->input('type');
        $sub_type = $request->input('sub_type');
        $cancel_case = $request->input('cancel_case');
        $exchange_value = $request->input('exchange_value');
        try{
            DB::beginTransaction();
            $check_request = ApprovalRequest::where('id',$requestId)
                                ->first();
            // ----------------- validate       ---------------------------
            if(!isset($check_request->id))
            {
                return 'Invalid Request.';
            }
            if($check_request->status == 'Cancelled' )
            {
                return 'Already Cancelled.';
            }
            if($check_request->status == 'Approved' )
            {
                return 'After Approved Will Not Be Cancelled It.';
            }
            $check_auth = CustomHelpers::check_cancel_auth($check_request->sub_type);
            if(!$check_auth)
            {
                return 'You Are Not Authorized.';
            }
            // ------------------------------------
            $cancel_request = CustomHelpers::cancel_request($check_request->id,$check_request->sub_type);
            if(!$cancel_request[0])
            {
                DB::rollback();
                return response()->json($cancel_request[1],401);
            }

            $status = $cancel_request[2];
            if($check_request->sub_type == 'best_deal_purchase' && $status == 'Cancelled')
            {
                $bestdeal = BestDealSale::where('id',$check_request->sub_type_id)
                                            ->where('tos','best_deal')
                                            ->where('status','Pending')->first();
                if(!isset($bestdeal->id))
                {
                    DB::rollback();
                    return 'Best Deal Not-Found.';
                }
                $saleId = $bestdeal->purchase_sale_id;
                $update_bestdeal = BestDealSale::where('id',$bestdeal->id)->update([
                    'approve'   =>  2
                ]);

                if($saleId > 0 )   // if bestdeal crated using sale
                {   
                    if($cancel_case == 'SaleWithoutBestDeal' && $check_request->type == 'sale'){
                        // update type_of_sale in sale
                        $update_tos = Sale::where('id',$saleId)
                                            ->update([
                                                'type_of_sale'  =>  1
                                            ]);
                    }elseif($cancel_case == 'Exchange' && $check_request->type == 'sale'){
                        // update type_of_sale in sale
                        $update_tos = Sale::where('id',$saleId)
                        ->update([
                            'type_of_sale'  =>  2
                        ]);
                        $update_balSale = Sale::where('id',$saleId)
                                                ->decrement('balance',$exchange_value);
                        // update type_of_sale in bestdealsale
                        $update_bestdeal = BestDealSale::where('id',$bestdeal->id)
                                                        ->update([
                                                            'tos'   =>  'exchange',
                                                            'value' =>  $exchange_value,
                                                            'approve'   =>  1
                                                        ]);
                    }
                    if($cancel_case != 'UpdateBestDeal'){
                        $cal = $this->calculate_for_sale($saleId);
    
                        if(!$cal)
                        {
                            DB::rollback();
                            return response()->json('Something Went Wrong.');
                        }
                    }
                }
                
            }
            elseif($check_request->sub_type == 'sale_discount' && $status == 'Cancelled'){
                $sale_discount = SaleDiscount::where('id',$check_request->sub_type_id)
                                                ->where('status','pending')
                                                ->first();
                if(!isset($sale_discount->id))
                {
                    DB::rollback();
                    return 'Sale Discount Not-Found.';
                }
                $update_discount = SaleDiscount::where('id',$sale_discount->id)
                                                        ->update(['status'  =>  'cancel']);

                $cal = $this->calculate_for_sale($sale_discount->type_id);

                if(!$cal)
                {
                    DB::rollback();
                    return response()->json('Something Went Wrong');
                }
            }
            elseif($check_request->sub_type == 'service_discount' && $status == 'Cancelled'){
                $service_discount = ServiceCharge::where('id',$check_request->sub_type_id)
                                            ->where('status','pending')
                                            ->first();
                if(!isset($service_discount->id))
                {
                    DB::rollback();
                    return response()->json('Service Discount Not-Found.',401);
                }

                //update in service charge
                $update_service = ServiceCharge::where('id',$service_discount->id)
                            ->update(['status' => 'cancel']);
            }
            elseif($check_request->sub_type == 'otc_sale_discount' && $status == 'Cancelled'){
                $otcsale_discount = SaleDiscount::where('id',$check_request->sub_type_id)
                                                ->where('status','pending')
                                                ->first();
                if(!isset($otcsale_discount->id))
                {
                    DB::rollback();
                    return 'OTC Sale Discount Not-Found.';
                }
                $update_discount = SaleDiscount::where('id',$otcsale_discount->id)
                                                        ->update(['status'  =>  'cancel']);

                $cal = $this->calculate_for_otcsale($otcsale_discount->type_id);

                if(!$cal)
                {
                    DB::rollback();
                    return response()->json('Something Went Wrong');
                }
            }

        }catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return response()->json('Something went wrong.'.$ex->getMessage(),401);
        }
        DB::commit();
        return response()->json('success');
    }

    public function calculate_for_sale($sale_id)
    {
        $check_approval_req = ApprovalRequest::where('type','sale')
                                    ->where('type_id',$sale_id)
                                    ->where('status','Pending')->first();
        if(!isset($check_approval_req->id)){   // still not pending request any for sale

            $get_sub_type = ApprovalRequest::where('type','sale')->where('type_id',$sale_id)
                                                ->where('status','Approved')->get();
            $sale = Sale::where('id',$sale_id)->first();
            $amount = $sale->balance;
            foreach($get_sub_type as $key => $val)
            {
                if($val->sub_type == 'best_deal_purchase'){
                    $get_value = BestDealSale::where('id',$val->sub_type_id)->first();
                    $amount = $amount-$get_value->value;
                }elseif($val->sub_type == 'sale_discount'){
                    $get_amount = SaleDiscount::where('id',$val->sub_type_id)
                                                ->where('status','approve')->first();
                    $amount = $amount-$get_amount->amount;
                }
            }
            $updateSale = Sale::where('id',$sale_id)
                                    ->update(['balance' => $amount]);
            $insertPaymentReq = PaymentRequest::insertGetId([
                                                        'store_id'  =>  $sale->store_id,
                                                        'type'  =>  'sale',
                                                        'type_id'   =>  $sale_id,
                                                        'amount'    =>  $amount
                                                    ]);
            if($insertPaymentReq){
                return true;
            }
            return false;
        }
        return true;
    }  

    public function approvalSetting_list(){
        $data = array(
                'layout' => 'layouts.main'
                );
        return view('admin.approve_setting.approve_setting_list',$data);
    }
    public function approvalSettingList_api(Request $request) {
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
        
        $auth_id = Auth::id();
        $api_data = ApprovalSetting::leftjoin('store','store.id','approval_setting.store_id')
                ->where(function($query) use($auth_id){
                    $query->whereRaw('FIND_IN_SET('.$auth_id.',approval_setting.level3)')
                    ->orwhereRaw('FIND_IN_SET('.$auth_id.',approval_setting.higher)')
                    ->orwhereRaw('FIND_IN_SET('.$auth_id.',approval_setting.level2)')
                    ->orwhereRaw('FIND_IN_SET('.$auth_id.',approval_setting.level1)');
                })
                ->select('approval_setting.id',
                'approval_setting.type',
                'approval_setting.name',
                DB::raw('IF(approval_setting.level3 = 0,"NONE",IFNULL((select name from users where id = approval_setting.level3),"NONE")) level3'),        
                DB::raw('IF(approval_setting.level2 = 0,"NONE",IFNULL((select name from users where id = approval_setting.level2),"NONE")) level2'),       
                DB::raw('IF(approval_setting.level1 = 0,"NONE",IFNULL((select name from users where id = approval_setting.level1),"NONE")) level1'),
                DB::raw('IF(approval_setting.higher = 0,"NONE",IFNULL((select name from users where id = approval_setting.higher),"NONE")) higher'),
                'approval_setting.status',
                'approval_setting.store_id',
                'approval_setting.created_at',
                'approval_setting.level1 as l1_id',
                'approval_setting.level2 as l2_id',
                'approval_setting.level3 as l3_id',
                DB::raw("".$auth_id." as my_id"),
                DB::raw("IF(FIND_IN_SET(".$auth_id.",approval_setting.level1) = 0, 'NO', 'YES') as setting_l1"),
                DB::raw("IF(FIND_IN_SET(".$auth_id.",approval_setting.higher) = 0, 'NO', 'YES') as setting_higher")
                // 'approval_setting.level1 as setting_l1',
                // 'approval_setting.higher as setting_higher',
                    
                );
           
            if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('approval_setting.name','like',"%".$serach_value."%")
                    ->orwhere(DB::raw('IF(approval_setting.level3 = 0,"NONE",IFNULL((select name from users where id = approval_setting.level3),"NONE"))'),'like',"%".$serach_value."%")
                    ->orwhere(DB::raw('IF(approval_setting.level2 = 0,"NONE",IFNULL((select name from users where id = approval_setting.level2),"NONE"))'),'like',"%".$serach_value."%")
                    ->orwhere(DB::raw('IF(approval_setting.level1 = 0,"NONE",IFNULL((select name from users where id = approval_setting.level1),"NONE"))'),'like',"%".$serach_value."%")
                    ->orwhere('approval_setting.status','like',"%".$serach_value."%")
                    ->orwhere('approval_setting.created_at','like',"%".$serach_value."%");
                });
            }
             if(!empty($store_id))
            {
               $api_data->where(function($query) use ($store_id){
                        $query->where('approval_setting.store_id','like',"%".$store_id."%");
                    });
               
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                    'approval_setting.name',
                    'approval_setting.level3',
                    'approval_setting.level2',
                    'approval_setting.level1',
                    'approval_setting.higher',
                    'approval_setting.status',
                    'approval_setting.created_at'
                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('approval_setting.created_at','desc');      
        
        $count = $api_data->count();
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

    public function approvalSetting_update($id) {
        $auth_id = Auth::id();
         $setting = ApprovalSetting::leftjoin('store','store.id','approval_setting.store_id')
         ->where('approval_setting.id',$id)
            ->where(function($query) use($auth_id){
                $query->whereRaw('FIND_IN_SET('.$auth_id.',approval_setting.level3)')
                ->orwhereRaw('FIND_IN_SET('.$auth_id.',approval_setting.higher)')
                ->orwhereRaw('FIND_IN_SET('.$auth_id.',approval_setting.level2)')
                ->orwhereRaw('FIND_IN_SET('.$auth_id.',approval_setting.level1)');
            })
                ->select('approval_setting.id',
                'approval_setting.type',
                'approval_setting.name',
                DB::raw('IF(approval_setting.level3 = 0,"NONE",IFNULL((select name from users where id = approval_setting.level3),"NONE")) level3'),        
                DB::raw('IF(approval_setting.level2 = 0,"NONE",IFNULL((select name from users where id = approval_setting.level2),"NONE")) level2'),       
                DB::raw('IF(approval_setting.level1 = 0,"NONE",IFNULL((select name from users where id = approval_setting.level1),"NONE")) level1'),
                DB::raw('IF(approval_setting.higher = 0,"NONE",IFNULL((select name from users where id = approval_setting.higher),"NONE")) higher'),
                'approval_setting.status',
                'approval_setting.store_id',
                'approval_setting.higher',
                'approval_setting.created_at',
                'approval_setting.level1 as l1_id',
                'approval_setting.level2 as l2_id',
                'approval_setting.level3 as l3_id',
                DB::raw("".$auth_id." as my_id"),
                DB::raw("IF(FIND_IN_SET(".$auth_id.",approval_setting.level1) = 0, 'NO', 'YES') as setting_l1"),
                DB::raw("IF(FIND_IN_SET(".$auth_id.",approval_setting.higher) = 0, 'NO', 'YES') as setting_higher")                    
                )->first();
                $users = Users::all();
                $data = array(
                    'setting' => $setting, 
                    'users' => $users,
                    'layout' => 'layouts.main'
                );
            return view('admin.approve_setting.approve_setting_edit',$data);
    }

    public function approvalSettingUpdate_DB(Request $request) {
         try {
             
            $id = $request->input('setting_id');
            if ($request->input('level1')) {
                 $level1 = implode(',', $request->input('level1'));
            }else{
                $level1 = 0;
            }

            if ($request->input('level2')) {
                 $level2 = implode(',', $request->input('level2'));
            }else{
                 $level2 = 0;
            }

            if ($request->input('level3')) {
                $level3 = implode(',', $request->input('level3'));
            }else{
                 $level3 = 0;
            }

            if ($request->input('higher')) {
                $higher = implode(',', $request->input('higher'));
            }else{
                $higher = 0;
            }
           
           
           
            

            $update = ApprovalSetting::where('id',$id)->update([
                'name' => $request->input('name'),
                'level1' => $level1,
                'level2' => $level2,
                'level3' => $level3,
                'higher' => $higher
            ]);

            if ($update == NULL) {
               return redirect('/admin/setting/approve/update/'.$id.'')->with('error','some error occurred');
            }else{
                return redirect('/admin/setting/approve/update/'.$id.'')->with('success','Updated successfully');
            }

          

        }  catch(\Illuminate\Database\QueryException $ex) {
            return redirect('/admin/setting/approve/update/'.$id.'')->with('error','some error occurred'.$ex->getMessage());
        }
    }

    public function calculate_for_otcsale($otcsale_id)
    {
        $check_approval_req = ApprovalRequest::where('type','otcsale')
                                    ->where('type_id',$otcsale_id)
                                    ->where('status','Pending')->first();
        if(!isset($check_approval_req->id)){   // still not pending request any for otcsale

            $get_sub_type = ApprovalRequest::where('type','otcsale')->where('type_id',$otcsale_id)
                                                ->where('status','Approved')->get();

            $otcsale = OtcSale::where('id',$otcsale_id)->first();
            $amount = $otcsale->balance;
            foreach($get_sub_type as $key => $val)
            {
                if($val->sub_type == 'otc_sale_discount'){
                    $get_amount = SaleDiscount::where('id',$val->sub_type_id)
                                                ->where('status','approve')->first();
                    $amount = $amount-$get_amount->amount;
                }
            }

            $updateOtcSale = OtcSale::where('id',$otcsale_id)
                                    ->update(['balance' => $amount]);
            $insertPaymentReq = PaymentRequest::insertGetId([
                                                        'store_id'  =>  $otcsale->store_id,
                                                        'type'  =>  'otcsale',
                                                        'type_id'   =>  $otcsale_id,
                                                        'amount'    =>  $amount
                                                    ]);
            if($insertPaymentReq){
                return true;
            }
            return false;
        }
        return true;
    }
}
