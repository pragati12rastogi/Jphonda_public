<?php

namespace App\Http\Controllers\admin;
use Illuminate\Http\Request;
use \stdClass;
use \App\Model\Store;
use \App\Model\Unload;
use \App\Model\DamageClaim;
use \App\Model\DamageDetails; 
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
use \App\Model\AMCProduct;
use \App\Model\PartShortage;
use \App\Model\PaymentRequest;
use \App\Model\AmcBookletIssue;
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
use \App\Model\SecurityModel;
use \App\Model\Part;
use \App\Model\PartStock;
use \App\Model\OtcSale;
use \App\Model\OtcSaleDetail;
use \App\Model\FuelModel;
use \App\Model\AMCModel;
use \App\Model\HJCModel;
use \App\Model\ServiceModel;
use \App\Model\Master;
use \App\Model\EwMaster;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Auth;
use Hash;
use \Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\Import;

class OTCSaleController extends Controller
{
    public function __construct()
    {
        //$this->middleware('auth');
    }
    public function dashboard(){
        return view('admin.dashboard',['layout' => 'layouts.main']);
    }


    //Additonal Services
    public function OtcSale() {
        // $accessories = Accessories::All();
        // $payData = Payment::where('booking_id',$id)->get();
        $store = Store::whereIn('id',CustomHelpers::user_store())
                    ->select('id',DB::raw('concat(store.name,"-",store.store_type) as name'))->get();
        $model_name = ProductModel::where('type','product')
                            ->whereRaw('isforsale = 1')
                            ->orderBy('model_name','ASC')
                            ->groupBy('model_name')->get(['model_name']);
        $amcProduct = AMCProduct::All();
        $customer = Customers::select('name','mobile','aadhar_id','voter_id','id')->get();

        $data = array(
            // 'bookingData' => $bookingData,
            // 'accessories' => $accessories,
            'amcProduct' => $amcProduct,
            'store' => $store,
            'customer'  =>  $customer,
            'model_name'    => ($model_name->toArray()) ? $model_name->toArray() : array() ,
            'layout' => 'layouts.main'
        );
         return view('admin.sale.otc_sale',$data);
    }

    public function search_frame_number(Request $request) {
       $text = $request->input('text');
       $frame = DB::table('sale_order')->where('product_frame_number', 'Like', "$text")
                ->leftJoin('sale','sale.id','sale_order.sale_id')
                ->leftJoin('customer','customer.id','sale.customer_id')
                ->leftJoin('product','product.id','sale.product_id')
                ->select('sale.id as sale_id','product.model_name','customer.name','customer.mobile')
                ->first();
       return response()->json($frame);
    }
    
    public function get_accessories(Request $request) {
 
        $model_name = $request->input('model_name');
        $store_id = $request->input('store_id');
       
        $getAccessories =  MasterAccessories::leftJoin('part',function($join){
                                    $join->on(DB::raw("FIND_IN_SET(part.id,master_accessories.part_id)"),">",DB::raw('0'));
                                })
                                    ->leftJoin('part_stock',function($join) use($store_id){
                                        $join->on('part.id','=','part_stock.part_id')
                                                ->where('part_stock.store_id',DB::raw($store_id));
                                })
                                ->where(function($query) use($model_name){
                                    $query->where('master_accessories.model_name',$model_name)
                                            ->orwhereNull('master_accessories.model_name');
                                })
                                ->where('master_accessories.part_id','<>','')
                                ->whereNotNull('master_accessories.part_id')
                            // ->where('part.type','Accessory')
                            ->whereRaw('IFNULL(part_stock.quantity,0) > 0')
                            ->select('part.id',
                                    // 'part.nid',
                                    DB::raw("IFNULL(master_accessories.accessories_name,part.name) as name"),
                                    'part.part_number as part_no',
                                    DB::raw('1 as qty'),'price as mrp','unit_price' ,
                                    'master_accessories.id as nid',
                                    'master_accessories.connection','master_accessories.hop','master_accessories.full',
                                    'master_accessories.model_name',
                                    'master_accessories.accessories_name',
                                    'master_accessories.part_id as accessories_id',
                                    DB::raw("IFNULL(part_stock.quantity,0) as sqty"))
                                ->orderBy('nid','ASC')
                                ->get();
        return response()->json($getAccessories);
    }
    public function get_accessoriespartNo(Request $request)
    {
        echo 'not used';die;
        $accessories_name = $request->input('accessories_name');
        $getAccessories =  Part::where('name',$accessories_name)
                        ->select('part.id','part.nid','part.name','part.part_number as part_no',
                        DB::raw('1 as qty'),'price as mrp','unit_price')
                            ->get();
        return response()->json($getAccessories);

    }
    public function OtcSale_DB(Request $request) {
        //accessories
        $count_accessories = ($request->input('accessories'))? count($request->input('accessories')) : 0 ;
        $count_other_acc = ($request->input('other_part_no'))? count($request->input('other_part_no')) : 0 ;
        $accessory = $request->input('accessories');
        $validateZero = [];
        $validateZeroMsg = [];
        $other_part_data = [];  //for the others accessories
        $part_data = [];  //for the others accessories
        for($j = 0 ; $j < $count_other_acc ; $j++){
            // check part # exist in part table ? validate it.
            $checkPartNo = Part::where('part_number',$request->input('other_part_no')[$j])->first();
            if(!isset($checkPartNo->id)){
                DB::rollback();
                return back()->with('error',$request->input('other_part_no')[$j].' Not Found that Part #.')->withInput();
            }
            $other_part_data[$j]['part_id'] = $checkPartNo->id; // part_id
            $other_part_data[$j]['amount'] = $checkPartNo->price; // price
            $other_part_data[$j]['qty'] = intval($request->input('other_part_qty')[$j]); // price
            $other_part_data[$j]['part_desc'] = 'Other'; // part desc
        }
        if($count_other_acc > 0){
            $validateZero['other_part_no.*'] =   'required'; 
            $validateZeroMsg['other_part_no.*.required'] =   "Other's Accessories Field Should Not Empty.";
        }
        // return back()->with('error','test')->withInput();die;
        for($i = 0 ; $i < $count_accessories ; $i++)
        {
            // check part # exist in part table ? validate it.
            $checkPartId = Part::where('id',$request->input('accessories_partNo_'.$accessory[$i]))->first();
            if(!isset($checkPartId->id)){
                DB::rollback();
                return back()->with('error',$request->input('show_accessories_partNo_'.$accessory[$i]).' Not Found that Part #.')->withInput();
            }
            $part_data[$i]['part_id'] = $request->input('accessories_partNo_'.$accessory[$i]);
            $part_data[$i]['amount'] = floatval($request->input('accessories_amount_'.$accessory[$i]));
            $part_data[$i]['qty'] = intVal($request->input('accessories_qty_'.$accessory[$i]));
            $part_data[$i]['part_desc'] = null;

            $validateZero['accessories_amount_'.$accessory[$i]] =   'required|gt:0'; 
            $validateZero['accessories_qty_'.$accessory[$i]] =   'required|gt:0';
            $validateZeroMsg['accessories_amount_'.$accessory[$i].'.gt'] =   "'".$request->input('accessories_name_'.$accessory[$i])."' amount should be greater than 0"; 
            $validateZeroMsg['accessories_qty_'.$accessory[$i].'.gt'] =   " '".$request->input('accessories_name_'.$accessory[$i])."' Quantity should be greater than 0";
        }
        $all_accessory = array_merge($part_data,$other_part_data);
        Validator::make($request->all(), array_merge($validateZero,[
            'customer_name' => 'required',
            // 'number' => 'required|numeric',
            // 'model_name' => 'required',
            'store_name' => 'required',
            'ew_cost' => 'required_if:ew,ew'.(($request->input('ew')) ? '|gt:0' : ''),
            'ew_duration' => 'required_if:ew,ew'.(($request->input('ew')) ? '|gt:0' : ''),
            'amc_cost' => 'required_if:amc,amc'.(($request->input('amc')) ? '|gt:0' : ''),
            'hjc_cost' => 'required_if:hjc,hjc'.(($request->input('hjc')) ? '|gt:0' : ''),
        ]),
        array_merge($validateZeroMsg,[
            'customer_name.required'    => 'This Feild is required',
            'store_name.required'    => 'This Feild is required',
            // 'number.required'    => 'This Feild is required',
            // 'number.numeric'    => 'Please Enter only numeric value',
            // 'model_name.required'    => 'This Feild is required',
            'ew_cost.required'    => 'This Feild is required',
            'ew_duration.required'    => 'This Feild is required',
            'ew_cost.gt'    => 'The Amount must be greater than 0',
            'amc_cost.required'    => 'This Feild is required',
            'amc_cost.gt'    => 'The Amount must be greater than 0',
            'hjc_cost.required'    => 'This Feild is required',
            'hjc_cost.gt'    => 'The Amount must be greater than 0'
        ]))->validate();
        try{
            $table = ['otc_sale','otc_sale_detail','sale_discount'];
            CustomHelpers::resetIncrement($table);
            $customer_id = 0;
            DB::beginTransaction(); 
            if ((!empty($request->input('amc')) || !empty($request->input('hjc')) || !empty($request->input('ew'))) && empty($request->input('frame_number'))) {
                DB::rollback();
                return back()->with('error','Please enter frame number !')->withInput();
            }
            if ($request->input('frame_number')) {
                $getsale = SaleOrder::where('product_frame_number',$request->input('frame_number'))->get()->first();
                if(!isset($getsale['sale_id'])){
                    DB::rollback();
                    return back()->with('error','Sale Order Record Not Found.')->withInput();
                }
                $sale_id = $getsale['sale_id'];
                $customer = Sale::where('id',$sale_id)->get()->first();
                $customer_id = $customer['customer_id'];
            }
           

            // $sale = Sale::where('id',$request->input('sale_id'))->get()->first();
            // if(!isset($sale['store_id'])){
            //     return back()->with('error','Sale Record Not Found.')->withInput();
            // }
            $store_id = $request->input('store_name');

            // else{
            //     $total_amount =  $request->input('all_total_amount_input');
            //     $balance = $request->input('otc_balance_input');
            // }

            // $otcsale = OtcSale::where('sale_id',$request->input('sale_id'))->get()->first();
            // if (isset($otcsale['sale_id'])) {
            //     $otc_sale_id = $otcsale['id'];
            // }else{
            // print_r($request->input('otc_sale_discount') >= 1000);
            $total_amount =  $request->input('all_total_amount_input');
            $balance = $request->input('otc_balance_input');

            if($request->input('otc_sale_discount') >= 1000)
            {
                $balance = $balance+$request->input('otc_sale_discount');
            }
            
            if($balance<0){
                DB::rollback();
                return back()->with('error','Discount cannot be more that total amount.')->withInput();
            }
            
            
                $array = [
                        'sale_id'   =>  (($request->input('sale_id') != '') ? $request->input('sale_id') : null),
                        'customer_id'  =>  $request->input('customer_name'),
                        'store_id'  =>  $store_id,
                        // 'mobile'  =>  $request->input('number'),
                        'ew_cost'  =>  (($request->input('ew')) ? $request->input('ew_cost') : null ),
                        'ew_duration'  =>  (($request->input('ew')) ? $request->input('ew_duration') : null ),
                        'amc_cost'  =>  (($request->input('amc')) ? $request->input('amc_cost') : null),
                        'hjc_cost'  =>  (($request->input('hjc')) ? $request->input('hjc_cost') : null ),
                        'total_amount'  => $total_amount,
                        'balance'  =>  $balance,
                        'created_by'=> Auth::id()
                    ];
            
            $otc_sale_id = OtcSale::insertGetId($array);

            if ($request->input('hjc_voucher') && $request->input('hjc')) {
                $disc = CustomHelpers::getHJCDiscount();
                $discAmt = $request->input('hjc_cost')*$disc['Accessories']/100;
                $insert = SaleDiscount::insertGetId([
                    'amount' => $discAmt,
                    'type' => 'otcsale',
                    'voucher_code' => $request->input('hjc_voucher'),
                    'type_id' => $otc_sale_id,
                    'discount_type'  => 'HJC',
                ]);

                if ($insert == NULL) {
                    DB::rollback();
                    return back()->with('error','Something went wrong !')->withInput();
                }
                // $total_amount =  $request->input('all_total_amount_input')-$discAmt;
                // $balance = $balance-$discAmt;

                // $upd_otc = OtcSale::where('id',$otc_sale_id)->update([
                //     'total_amount'  => $total_amount,
                //     'balance'  =>  $balance
                // ]);
            }
            // print_r($total_amount);
            // print_r($balance);die();
            $insert_approval = 0;

            if($request->input('otc_sale_discount') != null && $request->input('otc_sale_discount')> 0){
                $discount_data = [
                    'type'  =>  'otcsale',
                    'type_id'   =>  $otc_sale_id,
                    'discount_type'  => 'otcsaleDiscount',
                    'amount'  =>  $request->input('otc_sale_discount')
                ];
                if($request->input('otc_sale_discount') >= 1000)
                {
                    $discount_data = array_merge($discount_data,['status' => 'pending']);
                }else{
                    $discount_data = array_merge($discount_data,['status' => 'approve']);
                }
                
                $discount  =  SaleDiscount::insertGetId($discount_data);

                // if normal discount is more than 0 then required to approval so, insert in approval request 
                if($request->input('otc_sale_discount') >= 1000)
                {
                    $ins_approval = CustomHelpers::otcsale_disocunt_insert_approval($discount,$request->input('store_name'),$otc_sale_id);
                    $insert_approval = 1;
                }  
            }
            
            // }
            // fill in otc sale details ---------
            if(count($all_accessory) > 0){
                $total_accessories = $all_accessory;
            }
            foreach($total_accessories as $k => $v){
                $oth_acc_data = [
                    'otc_sale_id'   =>  $otc_sale_id,
                    'type'  =>  'Part',
                    'part_id'   =>  $v['part_id'],
                    'part_desc' =>  $v['part_desc'],
                    'qty'   =>  $v['qty'],
                    'amount'    =>  $v['amount'],
                    'with_sale' =>  2,
                    'otc_status'    =>  1
                ];
                $check_oth_acc_qty = PartStock::where('part_id',$v['part_id'])->where('store_id',$request->input('store_name'))
                                        ->where('quantity','>=',$v['qty'])->first();
                if(!isset($check_oth_acc_qty->id))
                {
                    $part_no = Part::where('id',$v['part_id'])->select('part_number')->first();
                    DB::rollback();
                    return back()->with('error','Error, Stock not available for '.$part_no->part_number.' Part Number, Please Enter Correct Quantity.')->withInput();
                }
                $updatePartStock = PartStock::where('part_id',$v['part_id'])->where('store_id',$request->input('store_name'));
                $inc = $updatePartStock->increment('sale_qty',$v['qty']);
                $dec = $updatePartStock->decrement('quantity',$v['qty']);
                
                $oth_accessoriesInsert = OtcSaleDetail::insertGetId($oth_acc_data);
            }
            // ----------
            if ($otc_sale_id && $insert_approval == 0) {
                $pay = PaymentRequest::insertGetId([
                    'type' => 'otcsale',
                    'type_id' => $otc_sale_id,
                    'store_id'  =>  $store_id,
                    'amount' => $balance,
                    'status' => 'pending'
                ]);
                if ($pay == NULL) {
                    DB::rollback();
                    return back()->with('error','Something went wrong !')->withInput();
                }
            }
            
            $otcdetail = OtcSaleDetail::where('otc_sale_id',$otc_sale_id)->orderBy('id','desc')->get()->first();
            if ($otcdetail) {
                $flagQty = $otcdetail['with_sale']+1;
            }else{
                $flagQty = 1;
            }

            if ($request->input('amc') == 'amc' ) {
                if(empty($request->input('amc_product'))){
                    DB::rollback();
                    return back()->with('error','Please enter amc product !')->withInput();
                }else{
                    $check_amc_sale = AMCModel::where('sale_id',$sale_id)->where(DB::raw('DATEDIFF(end_date,CURDATE())'),'>',0)->get();
                    if(count($check_amc_sale)== 0){
                        $service = ServiceModel::where('frame',$request->input('frame_number'))->get()->first();
                        $amc_product = AMCProduct::where('id',$request->input('amc_product'))->get()->first();
                        if ($amc_product['min_price'] > $request->input('amc_cost') || $amc_product['max_price'] < $request->input('amc_cost')) {
                            DB::rollback();
                            return back()->with('error','AMC amount is not right. Please enter amount between '.$amc_product['min_price'].'-'.$amc_product['max_price'].'')->withInput();
                        }
                        if ($request->input('amc_product')) {
                            $getPro = AMCProduct::where('id',$request->input('amc_product'))->get()->first();
                                $bookletIssue = AmcBookletIssue::insertGetId([
                                    'type' => 'otcsale',
                                    'total_booklet' => $getPro['duration'],
                                    'type_id' => $otc_sale_id,
                                    'store_id'  =>  $store_id,
                                    'status' => 'Pending'
                                ]);
                                if ($bookletIssue == NULL) {
                                    DB::rollback();
                                    return back()->with('error','Something went wrong !')->withInput();
                                }
                            
                        }
                        $start_date = date('Y-m-d');
                        $date = strtotime($start_date);
                        $new_date = strtotime('+ '.$request->input('amc_product').' year', $date);
                        $end_date = date('Y-m-d', $new_date);

                        if ($service) {
                            $checkAMC = AMCModel::where('service_id',$service['id'])->where(DB::raw('DATEDIFF(end_date,CURDATE())'),'>',0)->get();
                            if (count($checkAMC) == 0) {
                                
                                $insertAmc = AMCModel::insertGetId([
                                    'service_id' => $service['id'],
                                    'sale_id' => $sale_id,
                                    'start_date' => $start_date,
                                    'end_date' => $end_date,
                                    'amount' => $request->input('amc_cost'),
                                    'service_allowed' => $amc_product->service_allowed,
                                    'service_taken' => 1,
                                    'allowed_washing' => $amc_product->washing
                                ]);
                                if ($insertAmc) {
                                    $details = OtcSaleDetail::insertGetId([
                                        'otc_sale_id' => $otc_sale_id,
                                        'type' => 'AMC',
                                        'with_sale' => $flagQty,
                                        'amount' => $request->input('amc_cost')
                                    ]);
                                }else{
                                    DB::rollback();
                                    return back()->with('error','Something went wrong !')->withInput();
                                }
                            }else{
                                DB::rollback();
                                return back()->with('error','AMC already purchased !')->withInput();
                            }
                        }else{
                            $insertService = ServiceModel::insertGetId([
                                'sale_id' => $sale_id,
                                'customer_id' => $customer_id,
                                'frame' => $request->input('frame_number')
                            ]);
                            if ($insertService) {
                                $insertAmc = AMCModel::insertGetId([
                                    'service_id' => $insertService,
                                    'sale_id' => $sale_id,
                                    'start_date' => $start_date,
                                    'end_date' => $end_date,
                                    'amount' => $request->input('amc_cost'),
                                    'service_allowed' => $amc_product->service_allowed,
                                    'service_taken' => 1,
                                    'allowed_washing' => $amc_product->washing
                                ]);
                                if ($insertAmc) {
                                    $details = OtcSaleDetail::insertGetId([
                                        'otc_sale_id' => $otc_sale_id,
                                        'type' => 'AMC',
                                        'with_sale' => $flagQty,
                                        'amount' => $request->input('amc_cost')
                                    ]);
                                }else{
                                    DB::rollback();
                                    return back()->with('error','Something went wrong !')->withInput();
                                }
                            }else{
                                DB::rollback();
                                return back()->with('error','Something went wrong !')->withInput();
                            }
                        }
                    }else{
                        DB::rollback();
                        return back()->with('error','AMC already purchased !')->withInput();
                    }
                }
            }

            if ($request->input('hjc') == 'hjc') {

                if(!empty($request->input('hjc')) && empty($request->input('frame_number'))){
                    DB::rollback();
                    return back()->with('error','Please enter frame number !')->withInput();
                }else{
                    $check_hjc_sale = HJCModel::where('sale_id',$sale_id)->where(DB::raw('DATEDIFF(end_date,CURDATE())'),'>',0)->get();
                    if(count($check_hjc_sale)== 0){
                        $service = ServiceModel::where('frame',$request->input('frame_number'))->get()->first();
                        $start_date = date('Y-m-d');
                        $date = strtotime($start_date);
                        $new_date = strtotime('+ 1 year', $date);
                        $end_date = date('Y-m-d', $new_date);

                        if ($service) {
                            $checkHJC = HJCModel::where('service_id',$service['id'])->where(DB::raw('DATEDIFF(end_date,CURDATE())'),'>',0)->get();
                            if (count($checkHJC) == 0) {
                                
                                $insertHjc = HJCModel::insertGetId([
                                    'service_id' => $service['id'],
                                    'sale_id' => $sale_id,
                                    'start_date' => $start_date,
                                    'end_date' => $end_date,
                                    'amount' => $request->input('hjc_cost')
                                ]);

                                if ($insertHjc) {
                                    $details = OtcSaleDetail::insertGetId([
                                        'otc_sale_id' => $otc_sale_id,
                                        'type' => 'HJC',
                                        'with_sale' => $flagQty,
                                        'amount' => $request->input('hjc_cost')
                                    ]);
                                }else{
                                    DB::rollback();
                                    return back()->with('error','Something went wrong !')->withInput();
                                }

                            }else{
                                DB::rollback();
                                return back()->with('error','HJC already purchased !')->withInput();
                            }
                        }else{
                            $insertService = ServiceModel::insertGetId([
                                'sale_id' => $sale_id,
                                'customer_id' => $customer_id,
                                'frame' => $request->input('frame_number')
                            ]);
                            if ($insertService) {
                                $insertHjc = HJCModel::insertGetId([
                                    'service_id' => $insertService,
                                    'sale_id' => $sale_id,
                                    'start_date' => $start_date,
                                    'end_date' => $end_date,
                                    'amount' => $request->input('hjc_cost')
                                ]);

                                if ($insertHjc) {
                                    $details = OtcSaleDetail::insertGetId([
                                        'otc_sale_id' => $otc_sale_id,
                                        'type' => 'HJC',
                                        'with_sale' => $flagQty,
                                        'amount' => $request->input('hjc_cost')
                                    ]);
                                }else{
                                    DB::rollback();
                                    return back()->with('error','Something went wrong !')->withInput();
                                }
                            }else{
                                DB::rollback();
                                return back()->with('error','Something went wrong !')->withInput();
                            }
                        }
                    }else{
                        DB::rollback();
                        return back()->with('error','HJC already purchased !')->withInput();
                    }
                }
            }

            if(!empty($request->input('ew'))) {
                // check ew created at sale 
                $check_hjc_sale = ExtendedWarranty::where('sale_id',$sale_id)->get();
                if(count($check_hjc_sale)>0){
                    DB::rollback();
                    return back()->with('error','Extended Warranty already purchased !')->withInput();
                }else{
                    // checking if sale made in a year
                    $checksale_date = Sale::where('id',$sale_id)->where(DB::raw('TIMESTAMPDIFF(MONTH, sale_date, CURDATE())'),'<=',12)->get();
                    if(count($checksale_date)>0){
                        $service = ServiceModel::where('frame',$request->input('frame_number'))->get()->first();
                        $start_date = date('Y-m-d');
                        $date = strtotime($start_date);
                        $new_date = strtotime('+ '.$request->input('ew_duration').' year', $date);
                        $end_date = date('Y-m-d', $new_date);

                        if ($service) {
                            // $checkew = ExtendedWarranty::leftJoin('service','service.id','extended_warranty.service_id')
                            //     ->where('extended_warranty.service_id',$service['id'])
                            //     ->where(DB::raw('TIMESTAMPDIFF(YEAR, service.sale_date, CURDATE())'),'>=',1)
                            //     ->get()
                            //     ->first();
                            $checkew = ExtendedWarranty::where('service_id',$service['id'])->where(DB::raw('DATEDIFF(end_date,CURDATE())'),'>',0)->get()->first();
                            if ($checkew == NULL) {
                                
                                $insertew = ExtendedWarranty::insertGetId([
                                    'service_id' => $service['id'],
                                    'sale_id' => $sale_id,
                                    'start_date' => $start_date,
                                    'end_date' => $end_date,
                                    'amount' => $request->input('ew_cost')
                                ]);
                                if ($insertew) {
                                    $details = OtcSaleDetail::insertGetId([
                                        'otc_sale_id' => $otc_sale_id,
                                        'type' => 'Extended Warranty',
                                        'with_sale' => $flagQty,
                                        'amount' => $request->input('ew_cost')
                                    ]);
                                }else{
                                    DB::rollback();
                                    return back()->with('error','Something went wrong !')->withInput();
                                }
                            }else{
                                DB::rollback();
                                return back()->with('error','Extended Warranty already purchased !')->withInput();
                            }
                        }else{
                            $insertService = ServiceModel::insertGetId([
                                'sale_id' => $sale_id,
                                'customer_id' => $customer_id,
                                'frame' => $request->input('frame_number')
                            ]);
                            if ($insertService) {
                                $insertew = ExtendedWarranty::insertGetId([
                                    'service_id' => $insertService,
                                    'sale_id' => $sale_id,
                                    'start_date' => $start_date,
                                    'end_date' => $end_date,
                                    'amount' => $request->input('ew_cost')
                                ]);
                                if ($insertew) {
                                    $details = OtcSaleDetail::insertGetId([
                                        'otc_sale_id' => $otc_sale_id,
                                        'type' => 'Extended Warranty',
                                        'with_sale' => $flagQty,
                                        'amount' => $request->input('ew_cost')
                                    ]);
                                }else{
                                    DB::rollback();
                                    return back()->with('error','Something went wrong !')->withInput();
                                }
                            }else{
                                DB::rollback();
                                return back()->with('error','Something went wrong !')->withInput();
                            }
                        }
                    }else{
                        DB::rollback();
                        return back()->with('error','Extended Warranty Cannot Purchased After Year Of Sale !!!')->withInput();
                    }
                    
                }
            }

        }
        catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return back()->with('error','Something went wrong.'.$ex->getMessage())->withInput();
        }
        /* Add action Log */
        CustomHelpers::userActionLog($action='Purchased OTC',$otc_sale_id,$customer_id,'Purchase');
        DB::commit();
        return back()->with('success','OTC Sale created successfully');
    }

    public function OtcSale_list() {
        $data = array(
            'layout' => 'layouts.main'
        );
        return view('admin.otc_sale_list',$data);
    }

    public function OtcSaleList_api(Request $request) {
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
            

           $api_data = OtcSale::select('otc_sale.id','sale.sale_no','otc_sale.pay_later','customer.name','customer.mobile','otc_sale.ew_cost','otc_sale.ew_duration','otc_sale.amc_cost','otc_sale.hjc_cost','otc_sale.total_amount',DB::raw('sum(payment.amount) as amount'),'sale_discount.amount as jphonda_discount','otc_sale.balance')
            ->leftJoin('sale','otc_sale.sale_id','=','sale.id')
            ->leftJoin('customer','customer.id','otc_sale.customer_id')
            ->leftJoin('payment',function($join){
                $join->on('payment.type_id','=','otc_sale.id')
                    ->where('payment.type','otcsale')
                    ->where('payment.status','<>','cancelled');
            })
            ->leftJoin('sale_discount',function($join){
                $join->on('sale_discount.type_id','=','otc_sale.id')
                    ->where('sale_discount.type','otcsale')
                    ->where('sale_discount.discount_type','PayByJPHonda');
            })
            ->groupBy('otc_sale.id');
            if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                $query->where('sale.sale_no','like',"%".$serach_value."%")
                ->orwhere('customer.name','like',"%".$serach_value."%")
                ->orwhere('customer.mobile','like',"%".$serach_value."%")
                ->orwhere('otc_sale.ew_cost','like',"%".$serach_value."%")    
                ->orwhere('otc_sale.ew_duration','like',"%".$serach_value."%")
                ->orwhere('otc_sale.amc_cost','like',"%".$serach_value."%")
                ->orwhere('otc_sale.pay_later','like',"%".$serach_value."%")
                ->orwhere('otc_sale.hjc_cost','like',"%".$serach_value."%")
                ->orwhere('otc_sale.balance','like',"%".$serach_value."%")
                ->orwhere('otc_sale.total_amount','like',"%".$serach_value."%");
                });
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                'otc_sale.id',
                'sale.sale_no',
                'customer.name',
                'customer.mobile',
                'otc_sale.ew_cost',
                'otc_sale.ew_duration',
                'otc_sale.amc_cost',
                'otc_sale.hjc_cost',
                'otc_sale.total_amount',
                'otc_sale.total_amount',
                'otc_sale.pay_later',
                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('otc_sale.id','desc');      
        
        $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

    public function OtcSale_view($id){
        $servicesData = OtcSale::leftJoin('sale','otc_sale.sale_id','sale.id')
                                ->leftJoin('customer','customer.id','otc_sale.customer_id')
            ->select('otc_sale.id','sale.sale_no','customer.name','customer.mobile','otc_sale.ew_cost','otc_sale.ew_duration','otc_sale.amc_cost','otc_sale.hjc_cost','otc_sale.total_amount','otc_sale.balance')->where('otc_sale.id',$id)->first();

        $accessriesData = OtcSaleDetail::leftJoin('part','otc_sale_detail.part_id','part.id')
        ->select('otc_sale_detail.id','part.name','otc_sale_detail.qty','otc_sale_detail.amount','part.part_number as part_no','otc_sale_detail.type')->where('otc_sale_detail.otc_sale_id',$id)->get();

        $discount = SaleDiscount::where('type_id',$id)->select('*',
            DB::raw('(Case when discount_type = "HJC" then "HJC Discount" When discount_type = "otcsaleDiscount" then concat("OTC Sale Discount","(",status,")") END) as mod_dis_type')
            )->get()->toArray();
        $data = array(
            'servicesData' => $servicesData,
            'accessriesData' => $accessriesData,
            'saleDiscount'  =>$discount,
            'layout' => 'layouts.main'
        );
        return view('admin.otc_sale_view',$data);

    }

    public function additionalServices_pay($id){
        $pay_mode = DB::table('master')->where('type','payment_mode')->get();
        $servicesData = AdditionalServices::leftJoin('sale','additional_services.sale_id','sale.id')
            ->select('additional_services.id','sale.sale_no','additional_services.name','additional_services.mobile','additional_services.ew_cost','additional_services.ew_duration','additional_services.amc_cost','additional_services.hjc_cost','additional_services.total_amount')->where('additional_services.id',$id)->first();
        $paid = DB::table('payment')->where('type_id',$id)->where('type','additional_services')->where('status','<>','cancelled')->sum('amount');
         $data = array(
            'servicesData' => $servicesData,
            'paid' => $paid,
            'pay_mode' => $pay_mode,
            'layout' => 'layouts.main'
        );
        return view('admin.additional_services_pay',$data);
    }

    public function additionalServicesPay_Db(Request $request) {
         //print_r($request->input());die();
         try {
            $timestamp=date('Y-m-d G:i:s');
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
            $total_amount = AdditionalServices::where('id',$request->input('additional_services_id'))->select('total_amount')->first();
            $paid_amount = Payment::where('type_id',$request->input('additional_services_id'))->where('type','additional_services')->where('status','<>','cancelled')->sum('amount');
            $paid_amount = $paid_amount + $request->input('amount');
            if ($total_amount->total_amount < $paid_amount) {
                return redirect('/admin/sale/additional/services/pay/'.$request->input('additional_services_id'))->with('error','Your Amount too more .');
            }else{
                 $arr =[];
                    if($request->input('payment_mode') == 'Cash')
                    {
                        $arr = [
                            'status'    =>  'received'
                        ];
                    }
                    $paydata = Payment::insertGetId(
                    array_merge($arr,[
                        'type_id'=> $request->input('additional_services_id'),
                        'type' => 'additional_services',
                        'payment_mode' => $request->input('payment_mode'),
                        'transaction_number' => $request->input('transaction_number'),
                        'transaction_charges' => $request->input('transaction_charges'),
                        'receiver_bank_detail' => $request->input('receiver_bank_detail'),
                        'amount' => $request->input('amount'),
                    ])
                    );
                   
                // $paydata = Payment::insertGetId(
                // [
                //     'type_id'=> $request->input('additional_services_id'),
                //     'type' => 'additional_services',
                //     'payment_mode' => $request->input('payment_mode'),
                //     'transaction_number' => $request->input('transaction_number'),
                //     'transaction_charges' => $request->input('transaction_charges'),
                //     'receiver_bank_detail' => $request->input('receiver_bank_detail'),
                //     'amount' => $request->input('amount'),
                // ]
                // );
            }
                if($paydata == NULL) {
                       return redirect('/admin/sale/additional/services/pay/'.$request->input('additional_services_id'))->with('error','some error occurred'.$ex->getMessage());
                } else{
                      return redirect('/admin/sale/additional/services/pay/'.$request->input('additional_services_id'))->with('success','Amount Successfully Paid .');
                }
               
        
        }  catch(\Illuminate\Database\QueryException $ex) {
            return redirect('/admin/sale/additional/services/pay/'.$request->input('additional_services_id'))->with('error','some error occurred'.$ex->getMessage());
        }
    }

    public function OtcSalePay_Details($id) {
        $otcsale = OtcSale::where('id',$id)->get()->first();
        $payData = Payment::where('type_id',$id)->where('type','otcsale')->get();
        $data = array(
            'otcsale' => $otcsale,
            'payData' => $payData,
            'layout' => 'layouts.main'
        );
         return view('admin.sale.otcsale_pay_detail',$data);
    }


    //Update OTC
    public function otcUpdate(Request $request,$id)
    {
        // $checkPendingItem = Sale::where('id',$id)->select('pending_item')->first();
        // // print_r($checkPendingItem);die;
        // if(empty($checkPendingItem->pending_item))
        // {
        //     return back()->with('error','Please Fill Pending Item Section Firstly');
        // }
        $saleNo = Sale::where('id',$id)->first('sale_no');
        $oldData = DB::table('otc')->where('sale_id',$id)->first();
        if(empty($oldData))
        {
            return back()->with('error','Error, Please Firstly Create OTC.');
        }
        $data = array(   
            'oldData' => $oldData,    
            'sale_id' => $id,    
            'sale_no'   =>  $saleNo->sale_no,
            'layout' => 'layouts.main'
        );
        // print_r($data);die;
        return view('admin.sale.otcUpdate',$data);

    }
    public function otcUpdateDB(Request $request) {
        //print_r($request->input());die();
         try {
            $this->validate($request,[
                'invoice_no' => 'required',
                'date' => 'required',
                'amount' => 'required'
            ],[
                'invoice_no.required'=> 'This field is required.',
                'date.required'=> 'This field is required.',
                'amount.required'=> 'This field is required.'
            ]);
            DB::beginTransaction();
            // print_r($request->input());die;
            $sale_id = $request->input('sale_id');
            
            $check = DB::table('otc')->where('sale_id',$sale_id)->first();
            if($check)
            {
                $update = DB::table('otc')->where('sale_id',$sale_id)
                            ->update([
                                'invoice_no'    =>  $request->input('invoice_no'),
                                'date'  =>  CustomHelpers::showDate($request->input('date'),'Y-m-d'),
                                'amount'    =>  $request->input('amount')
                            ]);
                // return back()->with('error','Error, You Are Not Authorized For Update This Page');

            }
           
        }  catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return redirect('/admin/sale/update/otc/'.$request->input('sale_id'))->with('error','some error occurred'.$ex->getMessage());
        }
        DB::commit();
        return redirect('/admin/sale/update/otc/'.$request->input('sale_id'))->with('success','Successfully Updated.');

    }
    

    //Security Amount
    public function security_amount_create() {
        $user = Users::where('id',Auth::Id())->get()->first();
        $store = Store::whereIn('id',CustomHelpers::user_store())->get();

        $data = array(
            'user' => $user,
            'store' => $store,
            'layout' => 'layouts.main'
        );
        return view('admin.sale.security_amount_create',$data);
    }

    public function salenumber_search(Request $request) {
        $text = $request->input('text');
        $sale_number = DB::table('sale')->where('sale_no', 'Like', "$text")
                ->leftJoin('customer','customer.id','sale.customer_id')
                ->select('sale.id as sale_id','customer.name','customer.mobile')
                ->first();
       return response()->json($sale_number);
    }

    public function security_amount_create_DB(Request $request) {
       //print_r($request->input());
          try {
            $this->validate($request,[
                'name'=>'required',
                'number'=>'required',
                'store'=>'required',
                'reason'=>'required',
            ],[
                'name.required'=> 'This field is required.', 
                'number.required'=> 'This field is required.',
                'store.required'=> 'This field is required.',
                'reason.required'=> 'This field is required.',
            ]);
            $no = "SECTY-";
            $security_no = CustomHelpers::generateSecurityNo($no);
            if ($request->input('sale_id')) {
                $sale_id = $request->input('sale_id');
            }else{
                $sale_id = 0;
            }
                $security = SecurityModel::insertGetId(
                [
                    'security_number' => $security_no,
                    'sale_id' => $sale_id,
                    'name'=> $request->input('name'),
                    'mobile' => $request->input('number'),
                    'reason' => $request->input('reason'),
                    'store_id' => $request->input('store')
                ]);

                if($security == NULL) {
                       return redirect('/admin/sale/security/amount')->with('error','some error occurred')->withInput();
                } else{
                      return redirect('/admin/sale/security/amount')->with('success','Security amount added Successfully.');
                }

        }  catch(\Illuminate\Database\QueryException $ex) {
            return redirect('/admin/sale/security/amount')->with('error','some error occurred'.$ex->getMessage());
        }
    }

    public function security_amount_pay($id) {
        $securityData = SecurityModel::where("id",$id)->get(); 
        $paid = DB::table('payment')->where('type','security')->where('type_id',$id)->where('status','<>','cancelled')->sum('security_amount');
        $pay_mode = DB::table('master')->where('type','payment_mode')->get();
        $data = array(
            'securityData' => $securityData,
            'paid'    =>  $paid,
            'pay_mode'  =>  $pay_mode,
            'layout' => 'layouts.main'
        );
        return view('admin.sale.security_pay',$data);
    }

    public function security_amount_pay_DB(Request $request) {
        //print_r($request->input());die();
         try {
            $timestamp=date('Y-m-d G:i:s');
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
            if ($request->input('sale_id')) {
                $sale_id = $request->input('sale_id');
            }else{
                $sale_id = 0;
            }
            $total_amount = SecurityModel::where('id',$request->input('security_id'))->select('total_amount')->first();
                $arr = [];
                if($request->input('payment_mode') == 'Cash') {
                    $arr = ['status' => 'received'];
                    $payment_amount = $total_amount->total_amount + $request->input('amount');
                    $update = SecurityModel::where('id',$request->input('security_id'))->update([
                        'total_amount' => $payment_amount
                    ]);
                    
                }
                $paydata = Payment::insertGetId(
                array_merge($arr,[
                    'type_id'=> $request->input('security_id'),
                    'type' => 'security',
                    'payment_mode' => $request->input('payment_mode'),
                    'transaction_number' => $request->input('transaction_number'),
                    'transaction_charges' => $request->input('transaction_charges'),
                    'receiver_bank_detail' => $request->input('receiver_bank_detail'),
                    'security_amount' => $request->input('amount'),
                    'store_id' => $request->input('store_id'),
                    'amount' => 0
                ])
                );
                
                
                // print_r($paydata);die;
                if($paydata == NULL) {
                       return redirect('/admin/sale/security/amount/pay/'.$request->input('security_id'))->with('error','some error occurred'.$ex->getMessage());
                } else{
                      return redirect('/admin/sale/security/amount/pay/'.$request->input('security_id'))->with('success','Amount Successfully Paid .');
                }
               
        
        }  catch(\Illuminate\Database\QueryException $ex) {
            return redirect('/admin/sale/security/amount/pay/'.$request->input('security_id'))->with('error','some error occurred'.$ex->getMessage());
        }
    }

    public function security_payment_details($id) {
        $securityData = SecurityModel::where('id',$id)->get();
        $payData = Payment::where('type_id',$id)->where('type','security')->get();
       // print_r($payData);die();
        $data = array(
            'securityData' => $securityData,
            'payData' => $payData,
            'layout' => 'layouts.main'
        );
         return view('admin.sale.security_paymet_detail',$data);
    }

    public function security_amount_refund(Request $request) {
        //print_r($request->input());
         try { 
            $total_amount = SecurityModel::where('id',$request->input('id'))->select('total_amount')->first();
            $receive_amount = Payment::where('type_id',$request->input('id'))->where('type','security')->where('status','<>','cancelled')->sum('amount');
            if (($total_amount['total_amount'] == $receive_amount) && ($total_amount['total_amount'] == $request->input('refund'))) {
                 $security = SecurityModel::where('id',$request->input('id'))->update(
                    [
                        'refund_amount'=>$request->input('refund'),
                        'refund_comment'=>$request->input('comment'),
                    ]
                );
                if($security == NULL)  {
                    return 'error';
                } else{
                    return 'success'; 
                 }   
            }else{
                return 'verror';
            }
       
        }  catch(\Illuminate\Database\QueryException $ex) {
            return 'error';
        }
    }

    public function sale_refund($id) {
        $saleData = Sale::where("id",$id)->get()->first(); 
        $paid = DB::table('payment')->where('type','sale')->where('sale_id',$id)->sum('amount');
        $pay_mode = DB::table('master')->where('type','payment_mode')->select('key','value')->get();
        $data = array(
            'saleData' => $saleData, 
            'paid' => $paid,
            'pay_mode'  =>  $pay_mode,
            'layout' => 'layouts.main'
        );
        return view('admin.sale.refund_payment',$data);
    }

    public function sale_refund_db(Request $request) {
         //print_r($request->input('sale_id'));die();
         try {
            $timestamp=date('Y-m-d G:i:s');
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
            DB::beginTransaction();
                $total_amount = Sale::where('id',$request->input('sale_id'))->select('balance','payment_amount','refunded_amount')->first();
                $refund_amount = Payment::where('type','extraSaleRefund')->where('sale_id',$request->input('sale_id'))->where('status','<>','cancelled')->sum('amount');
                // echo "<br>".$paid_amount.'<br>'.$request->input('amount').'<br>';

                $refund_amount = $refund_amount + $request->input('amount'); 
                $extra_amount = $total_amount['payment_amount'] - $total_amount['balance']; 
                if ($extra_amount < $refund_amount) {
                    return redirect('/admin/sale/refund/'.$request->input('sale_id'))->with('error','Your Amount too more .');
                }else{
                    $arr =[];
                    if($request->input('payment_mode') == 'Cash')
                    {
                        $arr = [
                            'status'    =>  'received'
                        ];
                    }
                    $paydata = Payment::insertGetId(
                    array_merge($arr,[
                        'sale_id'=> $request->input('sale_id'),
                        'type' => 'extraSaleRefund',
                        'payment_mode' => $request->input('payment_mode'),
                        'transaction_number' => $request->input('transaction_number'),
                        'transaction_charges' => $request->input('transaction_charges'),
                        'receiver_bank_detail' => $request->input('receiver_bank_detail'),
                        'amount' => $request->input('amount'),
                        'store_id' => $request->input('store_id'),
                    ])
                    );
                    $refunded_amount = $total_amount['refunded_amount'] + $request->input('amount');
                    $update = Sale::where('id',$request->input('sale_id'))->update([
                        'refunded_amount' => $refunded_amount
                    ]);
                    if($paydata == NULL) {
                        DB::rollback();
                        return redirect('/admin/sale/refund/'.$request->input('sale_id'))->with('error','some error occurred');
                    }

                }
            
        }  catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return redirect('/admin/sale/refund/'.$request->input('sale_id'))->with('error','some error occurred'.$ex->getMessage());
        }
        DB::commit();
        return redirect('/admin/sale/refund/'.$request->input('sale_id'))->with('success','Amount Refunded Successfully  .');
    }


    //  public function PayLetter_allow(Request $request) {
    //    try {
    //        $otc_sale_id = $request->input('otc_sale_id');
    //         if (empty($otc_sale_id)) {
    //             return array('msg' => 'Something went wrong !', 'type' => 'error', 'data' => NULL);
    //         }else{
                
    //         }
    //     }  catch(\Illuminate\Database\QueryException $ex) {
    //          return array('msg' => 'Something went wrong !', 'type' => 'error', 'data' => NULL);
    //     }
    // }

     public function getJpHondaDiscount($id) {
        $getdiscount = SaleDiscount::where('type_id',$id)->where('type','otcsale')->where('discount_type','PayByJPHonda')->get()->first();
         return response()->json($getdiscount);
    }

    public function AddJpHonda_discount(Request $request) {
        try {
            DB::beginTransaction();
           $otc_sale_id = $request->input('otc_sale_id');
           if (empty($request->input('pay_discount'))) {
                return array('msg' => 'Please select any one.', 'type' => 'error', 'data' => NULL);
           }else{
            if ($request->input('pay_discount') == '1') {
                $update = OtcSale::where('id',$otc_sale_id)->update([
                  'pay_later' => '1',
                  ]);
           
                if ($update == NULL) {
                    DB::rollback();
                    return array('msg' => 'Something went wrong.', 'type' => 'error', 'data' => NULL);
                }else{
                    DB::commit();
                    return array('msg' => 'Pay later allowed successfully.', 'type' => 'success', 'data' => NULL);
                }
            }

            if ($request->input('pay_discount') == 'PayByJpHonda') {
                
                $checkCharge = SaleDiscount::where('type_id',$otc_sale_id)->where('type','otcsale')->where('discount_type','PayByJPHonda')->first();
                if (isset($checkCharge->id)) {
                    DB::rollback();
                  return array('msg' => 'Discount already added.', 'type' => 'error', 'data' => NULL);
                }else{
                    $amount = $request->input('amount');
                $insert = SaleDiscount::insertGetId([
                  'type_id' => $otc_sale_id,
                  'type' => 'otcsale',
                  'discount_type' => 'PayByJPHonda',
                  'amount' => '100'
                  ]);
                 if ($insert == NULL) {
                     DB::rollback();
                    return array('msg' => 'Something went wrong.', 'type' => 'error', 'data' => NULL);
                }else{
                    $paymet = PaymentRequest::where('type','otcsale')->where('type_id',$otc_sale_id)->first();
                    if(isset($paymet->id)){
                        $update = PaymentRequest::where('type','otcsale')->where('type_id',$otc_sale_id)->update([
                            'amount' => 0
                        ]);
    
                        if ($update == NULL) {
                            DB::rollback();
                            return array('msg' => 'Something went wrong.', 'type' => 'error', 'data' => NULL);
                        }else{
                            DB::commit();
                            return array('msg' => 'Discount added successfully.', 'type' => 'success', 'data' => NULL);
                        }  
                    }
                    DB::commit();
                    return array('msg' => 'Discount added successfully.', 'type' => 'success', 'data' => NULL);
                }
              } 
            }
           }
            
        }  catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
             return array('msg' => 'Something went wrong !'.$ex->getMessage().'', 'type' => 'error', 'data' => NULL);
        }
        DB::rollback();
        return array('msg' => 'Something went wrong.', 'type' => 'error', 'data' => NULL);
    }

    public function OtcSalePendingItem_list(){
        
        $data = [
            'layout' => 'layouts.main'
        ];
        return view('admin.sale.otcsale_pending_item_list',$data);
    }

    public function OtcSalePendingItem_api(Request $request) {

        // $search = $request->input('search');
        // $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;

        $tab = $request->input('tabVal');

        $store = Store::whereIn('id',CustomHelpers::user_store())
                    ->select('id')->get()->toArray();
        $api_data = OtcSale::leftJoin('sale', 'otc_sale.sale_id','sale.id')
                            ->whereIn('sale.status',['pending','done'])
                            ->leftJoin('otc_sale_detail','otc_sale_detail.otc_sale_id','otc_sale.id')
                            ->leftJoin('customer','customer.id','otc_sale.customer_id')
                            ->leftJoin('part','part.id','otc_sale_detail.part_id')
                            ->leftJoin('store','store.id','otc_sale.store_id')
                            ->whereIn('otc_sale.store_id',$store)
                            ->whereRaw('otc_sale_detail.with_sale = 1')
            ->select(
                'otc_sale.id',
                'store.name',
                'sale.sale_no',
                'customer.name as customer_name',
                DB::raw("GROUP_CONCAT( CONCAT(part.name,'( ',part.part_number,' )') , '' ) as part_details")

            )->groupBy('otc_sale.id');
        // print_r($api_data->get()->toArray());die;
        if($tab == 'pending'){
            $api_data = $api_data->where('otc_sale_detail.otc_status',0);
        }elseif($tab == 'complete'){
            $api_data = $api_data->where('otc_sale_detail.otc_status',1);
        }

            if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('sale.sale_no','like',"%".$serach_value."%")        
                    ->where('store.name','like',"%".$serach_value."%")          
                    ->where('customer.name','like',"%".$serach_value."%")          
                    ;
                });
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                'sale.sale_no',
                'store.name',
                'customer.name'
                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('otc_sale.id','desc');      
        
        $count = $api_data->count();
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

    public function get_otcsale_pendingItem(Request $request){
        try{
            $otc_sale_id = $request->input('otc_sale_id');
            $check = OtcSale::where('id',$otc_sale_id)->first();
            if(isset($check->id)){
                $get = OtcSaleDetail::where('otc_sale_detail.otc_sale_id',$check->id)
                                        ->where('otc_sale_detail.otc_status',0)
                                        ->where('otc_sale_detail.with_sale',1)
                                        ->leftJoin('part','part.id','otc_sale_detail.part_id')
                                        ->select(
                                            'otc_sale_detail.otc_sale_id',
                                            'otc_sale_detail.id',
                                            'otc_sale_detail.qty',
                                            'otc_sale_detail.amount',
                                            'part.name',
                                            'part.part_number'
                                        )
                                        ->get();
                if(!isset($get[0])){
                    return response()->json('Pending Item Not Found',401);
                }
                return response()->json($get);
            }else{
                return response()->json('Pending Item Not Found',401);
            }
        }
        catch(\Illuminate\Database\QueryException $ex) {
            return response()->json('Something went wrong. '.$ex->getMessage(),401);
        }
    }

    public function otcsale_pendingItemDB(Request $request){
        try{
            $otc_sale_id = $request->input('otc_sale_id');
            $otc_sale_detail_ids = $request->input('otc_sale_detail_ids');
            if(!isset($otc_sale_detail_ids[0])){
                return response()->json('Required to Select minimum One Part.',401);
            }
            $all_ids = [];
            foreach($otc_sale_detail_ids as $k => $v){
                $all_ids[$k]    =  intval($v);
            }
            // return response()->json($all_ids,401);

            $update_otc_status = OtcSaleDetail::whereIn('id',$all_ids)
                                                ->where('with_sale',1)
                                                ->update(['otc_status' => 1]);
            if($update_otc_status){
                return response()->json('Successfully OTC Created.');
            }else{
                return response()->json('Something Went Wrong.',401);
            }

        }catch(\Illuminate\Database\QueryException $ex) {
            return response()->json('Something went wrong. '.$ex->getMessage(),401);
        }
    }   

}

