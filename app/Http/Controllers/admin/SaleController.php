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
use \App\Model\AmcBookletIssue;
use \App\Model\PaymentRequest;
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
use \App\Model\InsuranceData;
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
use \App\Model\AMC;
use \App\Model\HJCModel;
use \App\Model\ServiceModel;
use \App\Model\Master;
use \App\Model\EwMaster;
use \App\Model\SaleRto;
use \App\Model\ApprovalRequest;
use \App\Model\ApprovalSetting;
use \App\Model\RefundRequest;
use \App\Model\Settings;
use \App\Model\CancelRequest;
use \App\Model\FuelStockModel;
use \App\Model\CustomerDetails;
use \App\Model\InsuranceCompany;
use \App\Model\VehicleAddonStock;
use \App\Model\VehicleAddon;
use \App\Model\FinanceCompany;
use \App\Model\FinancierExecutive;
use \App\Model\RtoFileSubmission;
use \App\Model\OtcModel;
use \App\Model\SalePendingAddon;
use \App\Model\RtoFileSubmissionDetails;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Auth;
use Hash;
use \Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\Import;
use App\Model\CallData;
use App\Model\CallDataDetails;
use App\Model\CallDataRecord;
use Mail;

class SaleController extends Controller
{
    public function __construct()
    {
        //$this->middleware('auth');
    }
    public function dashboard(){
        return view('admin.dashboard',['layout' => 'layouts.main']);
    }
    public function sale(){
      
        $sr_no = Sale::select(DB::raw('IFNULL(count(id),1) as max_id'))->first();
        // $customer = Customers::leftJoin('countries','countries.id','customer.country')
        //                 ->leftJoin('states','states.id','customer.state')
        //                 ->leftJoin('cities','cities.id','customer.city')
        //                 ->select('countries.name as country_name','states.name as state_name'
        //                 ,'cities.city as city_name','customer.*')->get();
        $customer = Customers::all();
        // $product = ProductModel::select('id','basic_price',
        //                 DB::raw('concat(model_name,"-",model_variant,"-",color_code) as prodName'))
        //                 ->where('type','product')->where('isforsale',1)->orderBy('model_name','ASC')->get();//not being used
        $state = State::all();
        $scheme = Scheme::all();
        
        $store = Store::whereIn('id',CustomHelpers::user_store())
                    ->select('id',DB::raw('concat(store.name,"-",store.store_type) as name'))->get();
        
        $model_name = ProductModel::where('type','product')->orderBy('model_name','ASC')
                            ->whereRaw('isforsale = 1')
                            ->groupBy('model_name')->get(['model_name']);
        $company_name = FinanceCompany::orderBy('id','ASC')->get();

        $sale_user = Users::where('role','SaleExecutive')->get(['id','name']);
        $insur_company = InsuranceCompany::orderBy('id')->get();
        $cpa_company = InsuranceCompany::where("cpa",1)->orderBy('id')->get();
        $maker = Master::where('type','prod_maker')->orderBy('order_by')->get();
        // $maker = array('Honda','Bajaj','Hero','TVS');
        $data = array(
            'layout' => 'layouts.main',
            'csd'   =>  CustomHelpers::csd_amount(),
            'insur_company'  =>  $insur_company,
            'sr_no' =>  'Sale/'.($sr_no['max_id']+1),
            'model_name'    =>  ($model_name->toArray())? $model_name->toArray() : array(),
            'customer' => ($customer->toArray()) ? $customer->toArray() : array() ,
            // 'product' => ($product->toArray()) ? $product->toArray() : array() ,
            'state' =>  ($state->toArray()) ? $state->toArray()  :  array() ,
            'scheme'    =>  $scheme,
            'store'    =>  $store->toArray(),
            'sale_user' =>  ($sale_user->toArray()) ? $sale_user->toArray() : array() ,
            'company_name' =>  $company_name,
            'cpa_company'   =>  $cpa_company,
            'maker' =>  $maker
        );
        return view('admin.sale.sale',$data);
    }
    public function saleDB(Request $request)
    {
        DB::beginTransaction();
        
        $csd_am = CustomHelpers::csd_amount();
        // best deal validation
        if($request->input('tos')   ==  'best_deal')
        {
            $f = BestDealSale::where('id',$request->input('best-id'))->where('purchase_sale_id',0)->first();
            if(!($request->input('best-id')) || !($request->input('best-error') == 'success') || !isset($f->id))
            {   
                return back()->with('error','Please Fill Best Deal Form All Details.')->withInput();
            }
        }

        // validate Financier Mobile Number will not be equal Customer Mobile Number
        if($request->input('customer_pay_type') == 'finance')
        {
            $find_mobile = FinancierExecutive::where('id',$request->input('finance_name'))
                                                ->whereRaw("FIND_IN_SET(".$request->input('mobile').",mobile_numbers)")
                                                ->first();
            if(isset($find_mobile->id))
            {
                return back()->with('error','Error, Financier Mobile Number and Customer Mobile Number should not be Same.')->withInput();
            }
        }

        // check product stock
        $checkProduct = Stock::where('product_id',$request->input('prod_name'))
                                ->where('store_id',$request->input('store_name'))
                                ->first();
        if(!isset($checkProduct->id)){
            return back()->with('error','Stock Not Available for Product.')->withInput();
        }else{
            if($request->input('select_customer') == 'booking'){
                if($checkProduct->booking_qty <= 0){
                    return back()->with('error','Booking Stock Not Available for Product.')->withInput();
                }
            }else{
                if($checkProduct->quantity <= 0){
                    return back()->with('error','Stock Not Available for Product.')->withInput();
                }
            }
        }

        
        $total_accessories = 0;
        $total_accessories_qty = 0;
        // $ExShowroomPrice = round($request->input('showroomPriceHidden'));
        $product_data = ProductModel::where('id',$request->input('prod_name'))->first();
        if(!isset($product_data->basic_price)){
            return back()->with('error','Product Data Not Found.')->withInput();
        }
        $ExShowroomPrice = round(($product_data->basic_price*28/100)+$product_data->basic_price);
        //accessories
        $count_accessories = ($request->input('accessories'))? count($request->input('accessories')) : 0 ;
        $count_other_acc = ($request->input('other_part_no'))? count($request->input('other_part_no')) : 0 ;
        $accessory = $request->input('accessories');
        $validateZero = [];
        $validateZeroMsg = [];
        $other_part_data = [];  //for the others accessories
        $part_data = [];  //for the others accessories
        for($j = 0 ; $j < $count_other_acc ; $j++){
            $total_accessories_qty  += intval($request->input('other_part_qty')[$j]) ;
            $total_accessories   +=  floatval($request->input('other_part_amount')[$j]);
            // check part # exist in part table ? validate it.
            $checkPartNo = Part::where('part_number',$request->input('other_part_no')[$j])->first();
            if(!isset($checkPartNo->id)){
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
                return back()->with('error',$request->input('show_accessories_partNo_'.$accessory[$i]).' Not Found that Part #.')->withInput();
            }
            $part_data[$i]['part_id'] = $request->input('accessories_partNo_'.$accessory[$i]);
            $part_data[$i]['amount'] = floatval($request->input('accessories_amount_'.$accessory[$i]));
            $part_data[$i]['qty'] = intVal($request->input('accessories_qty_'.$accessory[$i]));
            $part_data[$i]['part_desc'] = null;

            $total_accessories_qty  +=  intVal($request->input('accessories_qty_'.$accessory[$i]));
            $total_accessories   +=  floatval($request->input('accessories_amount_'.$accessory[$i]));
            $validateZero['accessories_amount_'.$accessory[$i]] =   'required|gt:0'; 
            $validateZero['accessories_qty_'.$accessory[$i]] =   'required|gt:0';
            $validateZeroMsg['accessories_amount_'.$accessory[$i].'.gt'] =   "'".$request->input('accessories_name_'.$accessory[$i])."' amount should be greater than 0"; 
            $validateZeroMsg['accessories_qty_'.$accessory[$i].'.gt'] =   " '".$request->input('accessories_name_'.$accessory[$i])."' Quantity should be greater than 0";
        }
        $all_accessory = array_merge($part_data,$other_part_data);
        //discount
        $exch_best_val = ($request->input('tos') != 'new') ?floatval($request->input('exchange_value')) : 0.0 ;
        $do = ($request->input('customer_pay_type') == 'finance') ?floatval($request->input('do')) : 0.0 ;
        $discount = floatval($request->input('discount_amount'));
        if($request->input('discount') == 'scheme'){
            $scheme_id = $request->input('scheme');
            $get_scheme = Scheme::where('id',$scheme_id)->first();
            if(!isset($get_scheme->id)){
                return back()->with('error','Scheme Master Not Found.')->withInput();
            }
            $discount = $get_scheme->amount;
        }
        // elseif($request->input('discount') == 'normal' && $discount > 1000){
        //     $discount = 0;
        // }
        $total_discount = $exch_best_val+$do+$discount;
        //total selected amount calculate
        $csd = 0;
        if($request->input('csd_check') && $request->input('sale_type') == 'corporate')
        {
            $csd = floatval($ExShowroomPrice * $csd_am/100);      // in csd amount
            $total_discount = round($total_discount + $csd);
        }
        $total_amount = $ExShowroomPrice+(floatval($request->input('reg_fee_road_tax')))
                            +(($request->input('s_insurance') == 'zero_dep') ? floatval($request->input('zero_dep_cost')) : 0.0 )
                            +(($request->input('s_insurance') == 'comprehensive') ? floatval($request->input('comprehensive_cost')) : 0.0 )
                            +(($request->input('s_insurance') == 'ltp_5_yr') ? floatval($request->input('ltp_5_yr_cost')) : 0)
                            +(($request->input('s_insurance') == 'ltp_zero_dep') ? floatval($request->input('ltp_zero_dep_cost')) : 0.0 )
                            +((!empty($request->input('cpa_cover'))) ? floatval($request->input('cpa_cover_cost')) : 0.0 )
                            +($total_accessories)
                            +(($request->input('customer_pay_type') != 'cash') ? floatval($request->input('hypo_cost')) : 0.0 )
                            +((!empty($request->input('amc'))) ? floatval($request->input('amc_cost')) : 0.0 )
                            +((!empty($request->input('ew'))) ? floatval($request->input('ew_cost')) : 0.0 )
                            +((!empty($request->input('hjc'))) ? floatval($request->input('hjc_cost')) : 0.0 );
                            
        
        // sale rto handling charges and affidavit charges add in total amount

        $handling_charge =  ($request->input('rto') == 'normal')?$request->input('handling_charge'): 0;
        $affidavit_cost    =  ($request->input('rto') == 'normal' && $request->input('affidavit') == 'affidavit')?$request->input('affidavit_cost') : 0;
        
        $total_amount =  $total_amount+$handling_charge+$affidavit_cost;

        //  add validation :- accessories mandatory when connection not selected
        $acc_flag = 0;
        if($request->input('acc_filter') != 'connection'){
            $acc_flag = 1;
            $validateZeroMsg['total_accessories_amount.gt'] =   'Accessories Field is required';
        }
        
        // validate vehicle addon stock
        $addon = VehicleAddon::whereRaw("key_name <> 'owner_manual'")->pluck('key_name')->toArray();
        if(!isset($addon[0])){
            DB::rollback();
            return back('error','Vehicle Addon Stock Master Not Found.')->withInput();
        }
        $model_name = str_replace(' ','_',strtolower($product_data->model_name));
        $new_addon_om = 'owner_manual_'.$model_name;
        array_push($addon,$new_addon_om);

        $addon_data = [];
        $any_addon_pending = 0;
        foreach($addon as $k => $v){
            $flag = 1;
            $in_data = [];
            if($v == 'saree_guard'){
                if($product_data->model_category == 'SC'){
                    $flag = 0;
                }
            }
            if($flag == 1){
                $check = VehicleAddonStock::where('vehicle_addon_key',$v)
                                                ->whereRaw('qty >= 1')
                                                ->where('store_id',$request->input('store_name'))->first();
                $in_data['type']   =  'addon';
                $in_data['type_name']   =  $v;
                if(isset($check->id)){
                    $in_data['stock_status']   =  1;
                    $addon_data[$v] = $in_data;
                }else{
                    $in_data['stock_status']   =  0;
                    $addon_data[$v] = $in_data;
                    $any_addon_pending = 1;
                    // DB::rollback();
                    // return back()->with('error','Stock Not Available for '.$v.' Addon.')->withInput();
                }
            }
        }

        $gte_cost = 'gt';
        $self_insurance = 0;
        if($request->input('insur_type') == 'self'){
            $gte_cost = 'gte';
            $self_insurance = 1;
        }

        $this->validate($request,array_merge($validateZero,[     
            // 'sale_no'=>'required',
            'sale_date'=>'required|date',
            'model_name'=>'required',
            'model_variant'=>'required',
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
            'tos'=>'required',
            'exchange_model'=>'required_unless:tos,new',
            'exchange_yom'=>'required_unless:tos,new',
            'exchange_register'=>'required_unless:tos,new',
            'exchange_value'=>'required_unless:tos,new'.(($request->input('tos') != 'new')? '|gt:0' : '' ),
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
            'ex_showroom_price'=>'required|gt:0|in:'.$ExShowroomPrice,
            'rto'   =>  'required',
            'handling_charge'   =>  'required_if:rto,normal'.(($request->input('rto') == 'normal')? '|gt:0' : '' ).'',
            'affidavit_cost'    =>  'required_if:affidavit,affidavit'.(($request->input('affidavit') == 'affidavit')? '|gt:0' : '' ).'',
            'permanent_temp'    =>  'required',
            'reg_fee_road_tax'=>'required|gt:0',
            'fancy_no'  =>  'required_if:fancy,Yes',
            'fancy_date'  =>  'required_if:fancy,Yes',
            'insur_c'   =>  'required',
            'zero_dep_cost'=>'required_if:s_insurance,zero_dep'.(($request->input('s_insurance') == 'zero_dep')? '|'.$gte_cost.':0' : '' ).'',
            'comprehensive_cost'=>'required_if:s_insurance,comprehensive'.(($request->input('s_insurance') == 'comprehensive')? '|'.$gte_cost.':0' : '' ).'',
            'ltp_5_yr_cost'=>'required_if:s_insurance,ltp_5_yr'.(($request->input('s_insurance') == 'ltp_5_yr')? '|'.$gte_cost.':0' : '' ).'',
            'ltp_zero_dep_cost'=>'required_if:s_insurance,ltp_zero_dep'.(($request->input('s_insurance') == 'ltp_zero_dep')? '|'.$gte_cost.':0' : '' ).'',
            'cpa_company'=>'required_if:cpa_cover,cpa_cover',
            'cpa_duration'=>'required_if:cpa_cover,cpa_cover'.(!empty($request->input('cpa_cover'))? '|integer' : '' ).'',
            'cpa_cover_cost'=>'required_if:cpa_cover,cpa_cover'.(!empty($request->input('cpa_cover'))? '|gt:0' : '' ).'',
            'total_accessories_amount'=>'required'.(($acc_flag == 1) ? '|gt:1' : ''),
            'accessories_cost'=>'required|in:'.$total_accessories,
            'hypo_cost'=>'required_unless:customer_pay_type,cash'.(($request->input('customer_pay_type') != 'cash')? '|gt:0' : '' ).'',
            'amc_cost'=>'required_if:amc,amc'.(!empty($request->input('amc'))? '|gt:0' : '' ).'',
            'ew_duration'=>'required_if:ew,ew'.(!empty($request->input('ew'))? '|gt:0' : '' ).'',
            'ew_cost'=>'required_if:ew,ew'.(!empty($request->input('ew'))? '|gt:0' : '' ).'',
            'hjc_cost'=>'required_if:hjc,hjc'.(!empty($request->input('hjc'))? '|gt:0' : '' ).'',
            'discount'=>'required',
            'discount_amount'=>'required|in:'.$discount,
            'scheme'=>'required_if:discount,scheme',
            'scheme_remark'=>'required_if:discount,scheme',
            'total_calculation'=>'required|in:'.$total_amount,
            'balance'   =>  'required|lte:'.($total_amount-$total_discount)
        ]),array_merge($validateZeroMsg,[
            // 'sale_no.required'  =>  'This Field is required',
            'sale_date.required'    =>  'Sale Date Field is required',
            'sale_date.date'    =>  'Sale Date Field is only date format',
            'model_name.required'    => 'Model Name Field is required' ,
            'model_variant.required'    => 'Model Variant Field is required' ,
            'prod_name.required'    => 'Product Name Field is required' ,
            'store_name.required'   =>  'Store Name Field is required',
            'sale_type.required'   =>  'Sale Type Field is required',
            'care_of.required_if'=>'Care Of Field is required',
            'gst.required_if'=>'GST Field is required',
            'select_customer.required'  =>  'This Field is required',
            'enter_customer_name.required_unless'   =>  'Customer Name Field is required',
            'select_customer_name.required_if'  =>  'Customer Name Field is required',
            'pre_booking.required_if'   =>  'Pre Booking Field is required',
            'relation_type.required' =>'Relation Type Field is required',
            'relation.required' =>  'Relation Name Field is required',
            'aadhar.required_without_all'=> 'Aadhar Field is required, When You Not Fill Voter Number.',
            'voter.required_without_all'=> 'Voter Field is required, When You Not Fill Aadhar Number.',
            'address.required'   =>  'Address Field is required',
            'state.required'    =>  'State Field is required',
            'city.required' =>  'City Field is required',
            'pin.required' =>  'Pin Code Field is required',
            // 'email.required'    =>  'This Field is required',
            'mobile.required'   =>  'Mobile # Field is required',
            'sale_executive.required' =>  'Sale Executive Field is required'   ,
            'tos.required'  =>  'Type Of Sale Field is required',
            'exchange_model.required_unless' =>  'Model Name Field is required',
            'exchange_yom.required_unless'  =>  'Year Of Manufacturing Field is required',
            'exchange_register.required_unless'  =>  'Register # Field is required',
            'exchange_value.required_unless' =>  'This Field is required',
            'exchange_value.gt'    =>  'This Field must be greater than 0.',
            'customer_pay_type.required'    =>  'This Field is required',
            'finance_name.required_if' =>  'Finance Name Field is required',
            'finance_company_name.required_if'  =>  'Finance Company Name  Field is required',
            'loan_amount.required_if'  =>  'Loan Amount Field is required',
            'loan_amount.gt'    =>  'Loan Amount Field must be greater than 0.',
            'dp.gt'    =>  'Down Payment Field must be greater than 0.',
            'emi.gt'    =>  'EMI Field must be greater than 0.',
            'do.required_if'    =>  'DO Amount Field is required',
            'dp.required_if' =>  'Down Payment Field is required',
            // 'los.required_if'   =>  'This Field is required',
            'roi.required_if'  =>  'ROI Field is required',
            'payout.required_if'    =>  'Payout Field is required',
            'pf.required_if'   =>  'PF Field is required',
            'emi.required_if'   =>  'EMI Field is required',
            'monthAddCharge.required_if'    =>  'This Field is required',    
            'sd.required_if'   =>  'This Field is required',
            'self_finance_company.required_if'  =>  'Company Name Field is required',
            'self_finance_amount.required_if'    =>  'Loan Amount Field is required',
            'self_finance_amount.gt'    =>  'Loan Amount Field must be greater than 0.',
            // 'self_finance_bank.required_if' =>  'This Field is required',
            'ex_showroom_price.required' =>  'Ex-Showroom Price Field is required',
            'ex_showroom_price.gt' =>  'Ex-Showroom Price will be Greater than zero.',
            'ex_showroom_price.in' =>  'Ex-Showroom Price is equal to basic price + 28% GST',
            'rto'   =>  'RTO Field is required',
            'hadling_charge.required' =>  'Handling Charge Field is required',
            'hadling_charge.gt' =>  'Handling Charge Field must be greater than 0',
            'affidavit.required' =>  'Affidavit Cost Field is required',
            'affidavit.gt' =>  'Affidavit Cost Field must be greater than 0',
            'permanent_temp.required' =>  'RTO Type Field is required',

            'reg_fee_road_tax.required' =>  'Registration Fee Field is required',
            'reg_fee_road_tax.gt' =>  'Registration Fee Field must be greater than 0',
            'fancy_no.required_if'    =>  'Fancy # Field is required.',
            'fancy_date.required_if'    =>  'Fancy Date Field is required.',
            'insur_c.required'    =>  'Insurance Company Field is required',
            'zero_dep_cost.required_if'    =>  'This Field is required',
            'zero_dep_cost.gt'    =>  'This Field must be greater than 0',
            'zero_dep_cost.gte'    =>  'This Field must be greater equal to 0',
            'comprehensive_cost.required_if'    =>  'This Field is required',
            'comprehensive_cost.gt'    =>  'This Field must be greater than 0',
            'comprehensive_cost.gte'    =>  'This Field must be greater equal to 0',
            'ltp_5_yr_cost.required_if'    =>  'This Field is required',
            'ltp_5_yr_cost.gt'    =>  'This Field must be greater than 0',
            'ltp_5_yr_cost.gte'    =>  'This Field must be greater equal to 0',
            'ltp_zero_dep_cost.required_if' =>  'This Field is required',
            'ltp_zero_dep_cost.gt'    =>  'This Field must be greater than 0',
            'ltp_zero_dep_cost.gte'    =>  'This Field must be greater equal to 0',
            'cpa_company.required_if'    =>  'This Field is required',
            'cpa_duration.required_if'  =>  'This Field is required',
            'cpa_cover_cost.required_if' =>  'This Field is required',
            'cpa_cover_cost.gt'    =>  'This Field must be greater than 0',
            'total_accessories_amount.required' =>  'This Field is required',
            // 'total_accessories_amount.gt' =>  'Accessories Field is required',
            'accessories_cost.required' =>  'This Field is required',
            'accessories_cost.in' =>  'This Field is equal to total Accessories which you selected',
            'hypo_cost.required_unless' =>  'Hypothecation Field is required',
            'amc_cost.required_unless'   =>  'AMC Cost Field is required',
            'amc_cost.gt'    =>  'AMC Cost Field must be greater than 0',
            'ew_duration.required_if'   =>  'EW Duration Field is required',
            'ew_duration.gt'    =>  ' EW Duration must be greater than 0',
            'ew_cost.required_if'   =>  'EW Cost Field is required',
            'ew_cost.gt'    =>  'EW Cost Field must be greater than 0',
            'hjc_cost.required_if'   =>  'HJC Cost Field is required',
            'hjc_cost.gt'    =>  'HJC Cost Field must be greater than 0',
            'discount.required' =>  'Discount Field is required',
            'discount_amount.required'   =>  'Discount Field is required',
            'discount_amount.in'   =>  'Discount Amount should be equeal to '.$discount,
            'scheme.required_if'   =>  'Scheme Field is required',
            'scheme_remark.required_if'    =>  'Remark Field is required',
            'total_calculation.required' =>  'This Field is required',
            'total_calculation.in' =>  'Total amount will be equal to '.$total_amount,
            // 'total_calculation.in' =>  'Total amount will be equal to whole amount which you have entered',
            'balance'   =>  'This Feild is required',
            'balance.lte'   =>  'Balance Amount must be less to Total Amount'
        ]));        
        // die;
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
            }
            $booking_id = $request->input('booking_id');
            $booking_number = $request->input('booking_number');
            $sr_no = Sale::select(DB::raw('IFNULL(count(id),1) as max_id'))->first();
            $max = $sr_no->max_id+1;
            $sale_no = 'Sale-'.$max;
            $sale_date = $request->input('sale_date');
            $customerFlag = $request->input('select_customer');
            $booking_cust_id = '';
            $mobile_no = $request->input('mobile');
            $customer_name = '';
            if($request->input('select_customer') == 'booking'){
                if($request->input('aadhar'))
                {
                    $customerData = Customers::where('aadhar_id',$request->input('aadhar'))->first(); 
                    if(isset($customerData->id)){
                        $booking_cust_id = $customerData->id;
                    }
                }
                if(empty($booking_cust_id))
                {
                    if($request->input('voter'))
                    {
                        $customerData = Customers::where('voter_id',$request->input('voter'))->first(); 
                        if(isset($customerData->id)){
                            $booking_cust_id = $customerData->id;
                        }
                    }
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
                    'name'  =>  CustomHelpers::capitalize($request->input('enter_customer_name'),'name'),
                    'aadhar_id'  =>  $request->input('aadhar'),
                    'voter_id'  =>  $request->input('voter'),
                    'relation_type' =>  $request->input('relation_type'),
                    'relation' =>  CustomHelpers::capitalize($request->input('relation'),'name'),
                    'email_id' =>  $request->input('email'),
                    'mobile' =>  $request->input('mobile'),
                    'dob'   =>  $request->input('dob'),
                    'country' =>  105,
                    'state' =>  $request->input('state'),
                    'city' =>  $request->input('city'),
                    'location' =>  $request->input('location'),
                    'address' =>  CustomHelpers::capitalize($request->input('address'),'sentence'),
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
                    'mobile'=>$request->input('mobile'),
                    'email_id'=>$request->input('email'),
                    'relation_type'=>$request->input('relation_type'),
                    'relation'=>CustomHelpers::capitalize($request->input('relation'),'name'),
                    'dob'   =>  $request->input('dob'),
                    'country'=>$request->input('country'),
                    'state'=>$request->input('state'),
                    'city'=>$request->input('city'),
                    'location' =>  $request->input('location'),
                    'address'=>CustomHelpers::capitalize($request->input('address'),'sentence'),
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
                    }elseif($request->input('aadhar') != $cust->aadhar_id){
                        return back()->with('error','Aadhar Number Will not be Changed When Customer Already Associate with Sale.')->withInput();
                    }
                    if(empty($cust->voter_id))
                    {
                        $checkVoter = CustomHelpers::checkCustomer($customerId,$request->input('aadhar'),$request->input('voter'));
                        if(!empty($checkVoter))
                        {
                            return back()->with('error','Error, Cutomer Already Exist for this Voter Number.')->withInput();
                        }
                        $data = array_merge($data,['voter_id'=> $request->input('voter')]);
                    }elseif($request->input('voter') != $cust->voter_id){
                        return back()->with('error','Voter Number Will not be Changed When Customer Already Associate with Sale.')->withInput();
                    }
                }
            
                $customer = Customers::where('id',$customerId)->update($data);
                if($customer)
                {
                    $customer_details = CustomerDetails::insertGetId([
                        'customer_id'   =>  $customerId,
                        'relation_type' =>  $cust->relation_type,
                        'relation' =>  CustomHelpers::capitalize($cust->relation,'name'),
                        'email_id'=>$cust->email,
                        'mobile'=>$cust->mobile,
                        'dob'   =>  $cust->dob,
                        'country'=>$cust->country,
                        'state'=>$cust->state,
                        'city'=>$cust->city,
                        'location'=>$cust->location,
                        'address'=>CustomHelpers::capitalize($cust->address,'sentence'),
                        'pin_code'=>$cust->pin_code,
                    ]);
                }
                // if($customer==NULL) 
                // {
                //     DB::rollback();
                //     return back()->with('error','Some Unexpected Error occurred.')->withInput();
                // }
            }
            $balance = round($request->input('balance'));
            if($request->input('discount') == 'normal' && $request->input('discount_amount') > 1000)
            {
                $balance = round($balance+$request->input('discount_amount'));
            }
            if($request->input('tos') == 'best_deal'){
                $balance = $balance+$request->input('exchange_value');
            }
            $type_of_sale = 1;
            if($request->input('tos') == 'exchange'){
                $type_of_sale = 2;
            }elseif($request->input('tos') == 'best_deal'){
                $type_of_sale = 3;
            }
            

            $saleInsert = [
                        'sale_executive'    =>  $request->input('sale_executive'),
                        'product_id'  =>  $request->input('prod_name'),
                        'sale_no'    =>  $sale_no,
                        'sale_type'    =>  $request->input('sale_type'),
                        'sale_date'  =>  $sale_date,
                        'store_id'  =>  $request->input('store_name'),
                        'ref_name'  =>  CustomHelpers::capitalize($request->input('reference'),'name'),
                        'ref_relation'  =>  CustomHelpers::capitalize($request->input('ref_relation'),'name'),
                        'ref_mobile'  =>  $request->input('ref_mobile'),
                        'customer_id'  =>  $customerId,
                        'customer_pay_type'  =>  $request->input('customer_pay_type'),
                        'tos'  =>  'new',
                        'type_of_sale'  =>  $type_of_sale,
                        'exchange_model' => ($request->input('tos') != 'new') ? $request->input('exchange_model') : null,
                        'exchange_yom'  =>  ($request->input('tos') != 'new') ? $request->input('exchange_yom') : null,
                        'exchange_value'  =>  ($request->input('tos') != 'new') ? $request->input('exchange_value') : null,
                        'exchange_register_no'  =>  ($request->input('tos') != 'new') ? $request->input('exchange_register') : null,
                        'ex_showroom_price'  =>  $request->input('ex_showroom_price'),
                        'register_price'  =>  $request->input('reg_fee_road_tax'),
                        'accessories_value'  =>  $request->input('accessories_cost'),
                        'hypo'  =>  ($request->input('customer_pay_type') != 'cash') ? $request->input('hypo_cost') : null,
                        'total_amount'  =>  round($request->input('total_calculation')),
                        'balance'  =>  $balance,
                        'pending_item'  =>  ($any_addon_pending == 1)?'yes':'no'
            ];
            
            $saleInsertId = Sale::insertGetId($saleInsert);

            // unlaod addon stock reduce
            foreach($addon_data as $k => $v){
                // insert in sale_pending_addon
                $spa_data = ['sale_id'  =>  $saleInsertId];
                $spa_data = array_merge($spa_data,$v);
                
                if($v['stock_status'] == 0){
                    $spa_insert = SalePendingAddon::insertGetId($spa_data);
                }
                else{
                    // update in vehicle addon stock
                    $dec_inQty = VehicleAddonStock::where('vehicle_addon_key',$k)
                                                ->where('store_id',$saleInsert['store_id'])
                                                ->decrement('qty',1);
                    $inc_inSale = VehicleAddonStock::where('vehicle_addon_key',$k)
                                                ->where('store_id',$saleInsert['store_id'])
                                                ->increment('sale_qty',1);
                    // if deducted owner manual with model then deduct in owner manual
                    if($new_addon_om == $k){
                        $dec_inQty = VehicleAddonStock::where('vehicle_addon_key','owner_manual')
                                                ->where('store_id',$saleInsert['store_id'])
                                                ->decrement('qty',1);
                        $inc_inSale = VehicleAddonStock::where('vehicle_addon_key','owner_manual')
                                                ->where('store_id',$saleInsert['store_id'])
                                                ->increment('sale_qty',1);
                    }
                }
                
            }

            // insert in sale rto---------------
            $sale_rto_data = [
                'sale_id'   =>  $saleInsertId,
                'rto'   =>  $request->input('rto'),
                'handling_charge'   =>  ($request->input('rto') == 'normal')?$request->input('handling_charge'): 0,
                'affidavit_cost'    =>  ($request->input('rto') == 'normal' && $request->input('affidavit') == 'affidavit')?$request->input('affidavit_cost') : 0,
                'rto_type'  =>  $request->input('permanent_temp'),
                'fancy_no_receipt'  =>  $request->input('fancy_no'),
                'fancy_date'  =>  $request->input('fancy_date')
            ];
            $sale_rto = SaleRto::insertGetId($sale_rto_data);
            //  -------------------------------

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
            }else{
                $discount_data = array_merge($discount_data,['status' => 'approve']);
            }
            $discount  =  SaleDiscount::insertGetId($discount_data);
            if($request->input('sale_type') == 'corporate' && $csd > 0)
            {
                $csd_discount_id  =  SaleDiscount::insertGetId([
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
            // $insur_data_id = InsuranceData::insertGetId($insurance_data);
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

            if($request->input('tos') == 'exchange' )
            {
                $tos = [    
                    'tos'   =>  $request->input('tos'),
                    'purchase_sale_id'   =>  $saleInsertId,
                    'model' => $request->input('exchange_model') ,
                    'yom'  =>  $request->input('exchange_yom') ,
                    'value'  =>  $request->input('exchange_value') ,
                    'register_no'  =>   $request->input('exchange_register') 
                ];
                BestDealSale::insertGetId($tos);
            }
            elseif($request->input('tos') == 'best_deal'){
                $update = BestDealSale::where('id',$request->input('best-id'))
                            ->update(['purchase_sale_id' => $saleInsertId]);
            }

            $total_accessories = [];
            if(count($all_accessory) > 0){
                $total_accessories = $all_accessory;
            }
            // $accessory = $request->input('accessories');
            $otc_sale_id = OtcSale::insertGetId([
                'customer_id' =>  $customerId,
                'sale_id'   =>  $saleInsertId,
                'store_id'  =>  $request->input('store_name'),
                'with_sale' =>  1,
                'created_by'=>$request->input('sale_executive')
            ]);
            if(!$otc_sale_id){
                DB::rollback();
                return back()->with('error','Error, Something Went Wrong.')->withInput();
            }
            $total_am = 0;
            // for other accessories  other_part_data
            $pending_item = [];
            foreach($total_accessories as $k => $v){
                $oth_acc_data = [
                    'otc_sale_id'   =>  $otc_sale_id,
                    'type'  =>  'Part',
                    'part_id'   =>  $v['part_id'],
                    'part_desc' =>  $v['part_desc'],
                    'qty'   =>  $v['qty'],
                    'amount'    =>  $v['amount'],
                    'with_sale' =>  1
                ];
                $check_oth_acc_qty = PartStock::where('part_id',$v['part_id'])->where('store_id',$request->input('store_name'))
                                        ->where('quantity','>=',$v['qty'])->first();
                if(!isset($check_oth_acc_qty->id))
                {
                    array_push($pending_item,$v['part_id']);
                    $oth_acc_data = array_merge($oth_acc_data,['otc_status' => 0]);
                    // $part_no = Part::where('id',$v['part_id'])->select('part_number')->first();
                    // DB::rollback();
                    // return back()->with('error','Error, Stock not available for '.$part_no->part_number.' Part Number, Please Enter Correct Quantity.')->withInput();
                }else{
                    $updatePartStock = PartStock::where('part_id',$v['part_id'])->where('store_id',$request->input('store_name'));
                    $inc = $updatePartStock->increment('sale_qty',$v['qty']);
                    $dec = $updatePartStock->decrement('quantity',$v['qty']);
                    $oth_acc_data = array_merge($oth_acc_data,['otc_status' => 1]);
                }
                
                $total_am = $total_am+$v['amount'];
                $oth_accessoriesInsert = OtcSaleDetail::insertGetId($oth_acc_data);
            }
            if(count($pending_item) > 0){
                $ids = join(',',$pending_item);
                $insert_in_pending_item = OrderPendingModel::insertGetId([
                    'sale_id'   =>  $saleInsertId,
                    'accessories_id'    =>  $ids,
                    'other' =>  ''
                ]);
                Sale::where('id',$saleInsertId)->update([
                    'pending_item'  =>  'yes'
                ]);
            }
            
            //update total amount in otc sale
            OtcSale::where('id',$otc_sale_id)->update(['total_amount' => $total_am]);

            if($request->input('customer_pay_type') == 'finance')
            {
                $finance = array(
                    'sale_id'    =>  $saleInsertId,
                    'finance_executive_id'  =>  $request->input('finance_name'),
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
                                ->where('type','booking')->sum('amount');
                $pending_payment = $pending_payment-$old_pay;
                Payment::where('booking_id',$booking_id)->whereNull('sale_id')->where('type','booking')
                            ->update(['sale_id'=>$saleInsertId,
                                    'type'  =>  'sale']); 
                Sale::where('id',$saleInsertId)->update(['payment_amount' => $old_pay]);  
                $bookingRow = BookingModel::where('id',$booking_id)->where('status','booked')->first();
                Stock::where('product_id',$bookingRow['product_id'])->where('store_id',$bookingRow['store_id'])->decrement('booking_qty',1);
                Stock::where('product_id',$bookingRow['product_id'])->where('store_id',$bookingRow['store_id'])->increment('sale_qty',1);
                BookingModel::where('id',$booking_id)->where('status','booked')
                                ->update(['status' => 'sale']);
                
            }else{    
                Stock::where('product_id',$request->input('prod_name'))->where('store_id',$request->input('store_name'))->increment('sale_qty',1);
                Stock::where('product_id',$request->input('prod_name'))->where('store_id',$request->input('store_name'))->decrement('quantity',1);
            }
            
            $type = 'sale';
            $call_type = 'thankyou';
            $call_type1 = 'psf';
            // $sale_date = $request->input('sale_date');
            $sale_id = $saleInsertId;
            $store_id = $request->input('store_name');
            // print($store_id);die();
            $next_call_date = date('Y-m-d', strtotime('+2 day', strtotime($sale_date)));
            $next_call_date1 = date('Y-m-d', strtotime('+4 day', strtotime($sale_date)));
            if ($saleInsertId) {
                $thanku = CustomHelpers::CreateCalling($type,$call_type,$store_id,$sale_id,$next_call_date);
                
                $psf = CustomHelpers::CreateCalling($type,$call_type1,$store_id,$sale_id,$next_call_date1);
                
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
                // Booklet Issue
                 $bookletIssue = AmcBookletIssue::insertGetId([
                    'type' => 'sale',
                    'type_id' => $saleInsertId,
                    'total_booklet' => 1,
                    'store_id'  =>  $request->input('store_name'),
                    'status' => 'Pending'
                ]);
                if ($bookletIssue == NULL) {
                    DB::rollback();
                     return back()->with('error','Something went wrong.')->withInput();
                }

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


            // insert in payment request   when type of sale is new
            if($request->input('tos') != 'best_deal' && $insert_approval == 0)
            {
                $payment_req = PaymentRequest::insertGetId([
                    'store_id'  =>  $request->input('store_name'),
                    'type'  =>  'sale',
                    'type_id'   =>  $saleInsertId,
                    'amount'    =>  $pending_payment,
                    'status'    =>  'pending'
                ]);
            }
            elseif($request->input('tos') == 'best_deal'){
                // insert in approval request
                $approve_insert = CustomHelpers::best_deal_insert_approval($request->input('best-id'),$request->input('store_name'),'sale',$saleInsertId);
            }   

            // Call Close if mobile find in call data
            $find_call = CallData::where('mobile',$mobile_no)->select('id','status')->first();
            if(isset($find_call->id)){
                $call_data_id = $find_call->id;
                $call_data_status = $find_call->status;
                if($call_data_status == 'Pending'){
                    $get_max_id = CallDataDetails::where('call_data_id',$call_data_id)->max('id');
                    if($get_max_id){
                        $cd_details = CallDataDetails::where('id',$get_max_id)->pluck('query_status')->toArray();
                        if(count($cd_details) > 0){
                            if($cd_details[0] == 'Open'){
                                $update_cd_details = CallDataDetails::where('id',$get_max_id)
                                                ->update([
                                                    'closing_date'  => date('Y-m-d'),
                                                    'query_status'  =>  'Closed'
                                                ]);
                            }
                        }
                        // insert in call record
                        $insert_cdr = CallDataRecord::insertGetId([
                            'call_data_id'  =>  $call_data_id,
                            'call_data_details_id'  =>  $get_max_id,
                            'call_status'   =>  'SaleClosed',
                            'remark'    =>  'Close By Sale Record'
                        ]);
                    }
                    $update_cd = CallData::where('id',$call_data_id)
                                            ->update(['status'  =>  'Done']);
                }
            }


        }catch(\Illuminate\Database\QueryException $ex) {
        DB::rollback();
            return back()->with('error','Something went wrong.'.$ex->getMessage())->withInput();
        }
        /* Add action Log */
        CustomHelpers::userActionLog($action='Purchased Vehicle',$saleInsertId,$customerId,'Purchase');
        DB::commit();
        return redirect('/admin/sale/pay/'.$sale_id)->with('success','Sale Created Successfully.');
        
    }
    public function checkPartNumber(Request $request){
        $part_no = $request->input('part_no');
        try{
            $check = Part::where('part_number',$part_no)->first();
            if(isset($check->id)){
                $res = ['success',$check->toArray()];
                return response()->json($res);
            }
            return response()->json('Part # Not Found.',401);

        }catch(\Illuminate\Database\QueryException $ex) {
            return response()->json('Something went wrong.'.$ex->getMessage(),401);
        }
    }
    public function sale_best_dealDB(Request $request)
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
            $arr1 = array('msg'=>'Error, One Section Should be Required in Insurance Information When Insurance Status is YES',
                            'best_id'    =>  $request->input('best_deal_id'));
            return response()->json($arr1);
        }

        $this->validate($request,array_merge($validation,[     
            'owner_name'=>'required',
            'owner_pos'=>'required',
            'mobile_number'=>'required',
            'address'=>'required',
            'aadhar'=>'required_without_all:voter',
            'voter'=>'required_without_all:aadhar',
            'km'=>'required',
            'pan'=>'required',
            'make'=>'required',
            'model'=>'required',
            'variant'=>'required',
            'color'=>'required',
            'yom'=>'required',
            'sale_date'=>'required',
            'frame'=>'required',
            'register'=>'required',
            'hypo'=>'required',
            'hypo_bank'=>'required_if:hypo,yes',
            'hypo_noc'=>'required_if:hypo,yes',
            'warrenty'=>'required',
            // 'warrenty_start'=>'required_if:warrenty,yes',
            'warrenty_end'=>'required_if:warrenty,yes',
            'insurance'=>'required',
            'key'=>'required',
            'purchase'=>'required|gt:0',
            'rc_status'=>'required',
        ]),array_merge($val_msg,[
            // 'sale_no.required'  =>  'This Field is required',
            'sale_date.required'    =>  'This Field is required',
            'sale_date.date'    =>  'This Field is only date format',
            'owner_name'=>'required',
            'owner_pos.required'=>'This Field is required',
            'mobile_number.required'=>'This Field is required',
            'address.required'=>'This Field is required',
            'aadhar.required'=>'This Field is required',
            'voter.required'=>'This Field is required',
            'km.required'=>'This Field is required',
            'pan.required'=>'This Field is required',
            'make.required'=>'This Field is required',
            'model.required'=>'This Field is required',
            'variant.required'=>'This Field is required',
            'color.required'=>'This Field is required',
            'yom.required'=>'This Field is required',
            'sale_date.required'=>'This Field is required',
            'frame.required'=>'This Field is required',
            'register.required'=>'This Field is required',
            'hypo.required'=>'This Field is required',
            'hypo_bank.required_if'=>'This Field is required',
            'hypo_noc.required_if'=>'This Field is required',
            'warrenty.required'=>'This Field is required',
            // 'warrenty_start.required_if'=>'This Field is required',
            'warrenty_end.required_if'=>'This Field is required',
            'insurance.required'=>'This Field is required',
            'key.required'=>'This Field is required',
            'purchase.required'=>'This Field is required',
            'purchase.gt'=>'Purchase Price Should be grater than zero',
            'rc_status.required'=>'This Field is required',

        ]));
        try{
            DB::beginTransaction();

            $data = [
                'tos'   =>  'best_deal',
                'name' => CustomHelpers::capitalize($request->input('owner_name'),'name'),
                'position' => $request->input('owner_pos'),
                'number'  => $request->input('mobile_number'),
                'address'    => CustomHelpers::capitalize($request->input('address'),'sentence'),
                'aadhar' => $request->input('aadhar'),
                'voter'  => $request->input('voter'),
                'km' => $request->input('km'),
                'pan'    => $request->input('pan'),
                'maker'   => $request->input('make'),
                'yom'    => $request->input('yom'),
                'dos'  => $request->input('sale_date'),
                'frame'  => $request->input('frame'),
                'register_no'   => $request->input('register'),
                'no_of_key'    => $request->input('key'),
                'value'   => $request->input('purchase'),
                'rc_status'  => $request->input('rc_status')
            ];

            $hypo   = $request->input('hypo');
            if($hypo == 'yes')
            {
                $data = array_merge($data,[
                    'hypo_bank'  => $request->input('hypo_bank'),
                    'hypo_noc'   => $request->input('hypo_noc')
                ]);
            }
            $warrenty   = $request->input('warrenty');
            if($warrenty == 'yes')
            {
                $data = array_merge($data,[
                    'warrenty_end'   => $request->input('warrenty_end')
                ]);
            }
            $insurance  = $request->input('insurance');

            $model = $request->input('model');
            $variant   = $request->input('variant');
            $color = $request->input('color');
            $product_id = $request->input('product_id');
            if($product_id)
            {
                $data = array_merge($data,[
                    'product_id' => $request->input('product_id')
                ]);
            }
            else{
                $data = array_merge($data,[
                    'model' => $model,
                    'variant' => $variant,
                    'color' => $color
                ]);
            }
            
            if($request->input('best_deal_id'))
            {
                $update = BestDealSale::where('id',$request->input('best_deal_id'))
                                            ->update($data);
                if($update)
                {   
                    $insert = $request->input('best_deal_id');
                    // insert in insurance
                    $insurance  = $request->input('best-insurance');
                    if($insurance == 'yes' && $insert)
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
                            $insert_od_cpa = Insurance::where('bestdeal_id',$insert)->where('insurance_type','OD')->update($od_cpa);
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
                            $insert_tp = Insurance::where('bestdeal_id',$insert)->where('insurance_type','TP')->update($tp);
                        }
                    }
                }
            }
            else{
                $insert = BestDealSale::insertGetId($data);   
                // insert in insurance
                $insurance  = $request->input('best-insurance');
                if($insurance == 'yes' && $insert)
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
            if(!$insert)
            {
                DB::rollback();
                $arr2 = array('msg'=>'Something went wrong','best_id' => $insert);
                return response()->json($arr2);
            }

        }
        catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            $arr1 = array('msg'=>'Something went wrong.'.$ex->getMessage(),'best_id'    =>  $request->input('best_deal_id'));
            return response()->json($arr1);
        }
        DB::commit();
        $arr = array('msg'=>'success','best_id' => $insert);
        return response()->json($arr);

    }

    public function sale_best_deal_getProductInfo(Request $request)
    {   
        $make = $request->input('make');
        $frame = $request->input('frame');
        $reg = $request->input('reg');
        
        // find in sale

        $find = SaleOrder::join('rto','rto.sale_id','sale_order.sale_id')
                                ->join('product_details','product_details.frame','sale_order.product_frame_number')
                                ->join('product','product.id','product_details.product_id')
                            ->where('rto.registration_number',$reg)
                            ->where('sale_order.product_frame_number',$frame)
                            ->select('product.model_name','product.model_variant',
                                        'product.color_code',
                                        'product.id'
                                )->first();
        if(!isset($find[0]))
        {
            $find   = ServiceModel::where(function($query) use($frame,$reg) {
                            $query->where('service.frame',$frame)
                            ->orWhere('service.registration',$reg);
                    })
                    ->join('product','product.id','service.product_id')
                    ->select('product.model_name','product.model_variant',
                                        'product.color_code',
                                        'product.id'
                    )->first();
        }
        return response()->json($find);
        
    }

    public function getCustomer(Request $request)
    {
        $customerId = $request->input('customerId');
        $cust = Customers::where('id',$customerId)->first();
        return response()->json($cust);
    }
    public function getAadharVoterCustomer(Request $request)
    {
        $aadhar = $request->input('aadhar');
        $voter = $request->input('voter');
        if($aadhar){
            $cust = Customers::where('aadhar_id',$aadhar)->first();
        }
        elseif($voter){
            $cust = Customers::where('voter_id',$voter)->first();
        }
        else{
            $cust = array();
        }
        return response()->json($cust);
    }
    public function getAllModel(Request $request)
    {
        $model_var = ProductModel::where('type','product')->orderBy('model_name','ASC')->groupBy('model_name')
                        ->pluck('model_name');
        return response()->json($model_var);

    }
    public function getAllModelVariant(Request $request)
    {
        $model_name = $request->input('model_name');
        $model_var = ProductModel::where('model_name',$model_name)->where('type','product')
                        ->orderBy('model_variant','ASC')->groupBy('model_variant')
                        ->whereNotNull('model_variant')->where('model_variant','<>','')
                        ->get();
        return response()->json($model_var);

    }
    public function getAllModelColorCode(Request $request)
    {
        $model_var = $request->input('model_var');
        $model_name = $request->input('model_name');

        $model_color = ProductModel::where('model_variant',$model_var)
                        ->where('model_name',$model_name)->where('type','product')
                        ->whereNotNull('color_code')->where('color_code','<>','')
                        ->orderBy('model_variant','ASC')
                        ->get();
        return response()->json($model_color);

    }
    public function getModelVariant(Request $request)
    {
        $model_name = $request->input('model_name');
        $model_var = ProductModel::where('model_name',$model_name)->where('type','product')
                        ->where('isforsale',1)
                        ->orderBy('model_variant','ASC')->groupBy('model_variant')
                        ->get();
        return response()->json($model_var);

    }
    public function getModelColorCode(Request $request)
    {
        $model_var = $request->input('model_var');
        $model_name = $request->input('model_name');

        $model_color = ProductModel::where('model_variant',$model_var)
                        ->where('model_name',$model_name)->where('type','product')
                        ->where('isforsale',1)
                        ->orderBy('model_variant','ASC')
                        ->get();
        return response()->json($model_color);

    }
    public function financerExecutive(Request $request)
    {
        $company_name = $request->input('finance_company_name');
        // $pid = DB::table('master')->where('type','company_name')->where('key',$company_name)->first(['id']);
        $q = FinancierExecutive::where('finance_company_id',$company_name)
                                ->whereIn('store_id',CustomHelpers::user_store())->get();

        return response()->json($q);
    }
    public function checkbooking_number($bookingNo)
    {

        $find =  BookingModel::where('booking_number',$bookingNo)->first();
        if(isset($find->id))
        {
            if($find->status != 'booked'){
                return 'used';
            }
            return $find;
            // $useInPay = Payment::where('booking_id',$find->id)->where('sale_id',null)->first();
            // if(empty($useInPay))
            // {
            //     return 'used';
            // }
            // else
            // {
            //     return response()->json($find);
            // }
        }
        else
        {
            return 'no-found';
        }
    }
    public function productInfo(Request $request)
    {
        $prodId = $request->input('prodId');
        $storeId = $request->input('storeId');

        $getqty = Stock::leftJoin('product','product.id','stock.product_id')
                    ->where('product_id',$prodId)->where('quantity','>',0)->where('store_id',$storeId)
                    ->select(
                        'product.basic_price',
                        'stock.quantity'
                    )->first();
        $arr = (($getqty)?$getqty->toArray():array());
        return response()->json($arr);

    }
    public function productInfoAtUpdate(Request $request)
    {
        $prodId = $request->input('prodId');
        $storeId = $request->input('storeId');
        $oldSaleProductId = $request->input('oldSaleProductId');
        $oldSaleStoreId = $request->input('oldSaleStoreId');

        $getqty = Stock::leftJoin('product','product.id','stock.product_id')
                    ->where('stock.product_id',$prodId)->where('stock.store_id',$storeId)
                    ->select(
                        'product.basic_price',
                        'stock.quantity'
                    )->get();
        
        if($prodId == $oldSaleProductId && $storeId == $oldSaleStoreId)
        {
            $getqty = $getqty->first();
        }
        else{
            $getqty = $getqty->where('quantity','>',0)->first();
        }

        if(isset($getqty->id)){
            $getqty = $getqty->toArray();
        }
        $arr = (($getqty)?$getqty:array());
        return response()->json($arr);

    }
    public function findAccessories(Request $request)
    {
        $prodId = $request->input('prodId');
        $store_id = $request->input('store_id');
        $filter = $request->input('filter');
        $getCat = ProductModel::select('model_name')->where('id',$prodId)->first()->toArray();
        
        // $union =  MasterAccessories::leftJoin('part','part.name','master_accessories.accessories_name')
        //                     ->leftJoin('part_stock',function($join) use($store_id){
        //                         $join->on('part.id','=','part_stock.part_id')
        //                                 ->where('part_stock.store_id',DB::raw($store_id));
        //                 })
        //                 ->where('master_accessories.model_name','like',$getCat['model_name'])
        //                 ->where('part.type','Accessory')
        //                 // ->whereRaw('IFNULL(part_stock.quantity,0) > 0')
        //                 ->select('part.id','part.nid','part.name','part.part_number as part_no',
        //                             DB::raw('1 as qty'),'price as mrp','unit_price' 
        //                         ,'master_accessories.connection','master_accessories.hop','master_accessories.full'
        //                         ,'master_accessories.model_name','master_accessories.accessories_name',
        //                         'master_accessories.part_id as accessories_id',DB::raw("IFNULL(part_stock.quantity,0) as sqty"));
        $getAccessories =  MasterAccessories::leftJoin('part',function($join){
                                    $join->on(DB::raw("FIND_IN_SET(part.id,master_accessories.part_id)"),">",DB::raw('0'));
                                })
                                    ->leftJoin('part_stock',function($join) use($store_id){
                                        $join->on('part.id','=','part_stock.part_id')
                                                ->where('part_stock.store_id',DB::raw($store_id));
                                })
                                ->where(function($query) use($getCat){
                                    $query->where('master_accessories.model_name','like',$getCat['model_name'])
                                            ->orwhereNull('master_accessories.model_name');
                                })
                            // ->where('part.type','Accessory')
                            ->whereRaw('master_accessories.part_id <> ""')
                            ->whereNotNull('master_accessories.part_id')
                            // ->whereRaw('IFNULL(part_stock.quantity,0) > 0')
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
                                // ->unionAll($union)
                                ->orderBy('nid','ASC')
                                ->get();
        // print_r($getAccessories->toArray());die;
        return response()->json($getAccessories);

    }
    public function accessoriespartNo(Request $request)
    {
        echo "not used";die;
        $accessories_name = $request->input('accessories_name');
        
       
        $getAccessories =  Part::where('name',$accessories_name)
                        ->select('part.id','part.nid','part.name','part.part_number as part_no',
                        DB::raw('1 as qty'),'price as mrp','unit_price')
                            ->get();
        return response()->json($getAccessories);

    }
    public function findBookingNo(Request $request)
    {
        $booking_no = $request->input('booking_no');
        $find =  BookingModel::where('booking_number',$booking_no)
                        // ->where('status','booked')
                        ->first();
        if(!empty($find))
        {   
            if($find->status != 'booked'){
                return 'used';
            }
            return response()->json($find);
            // $useInPay = Payment::where('booking_id',$find->id)->whereNull('sale_id')->first();
            // $useInPay = Payment::where('booking_id',$find->id)->where('type','booking')->first();
            // if(isset($useInPay->id))
            // {
            //     return response()->json($find);
            // }
            // else
            // {
            //     return response()->json($find);
            // }
        }
        else
        {
            return 'no-found';
        }

    }

    public function createHirise($id) {
         $saleData = Sale::where("id",$id)->get()->first();
         $hiriseData = Hirise::where("sale_id",$id)->get()->first();
         $extendedWarrantyData = ExtendedWarranty::where("sale_id",$id)->get()->first();
         $data  = array(
            'saleData' => $saleData, 
            'hiriseData' => $hiriseData,
            'extendedWarrantyData' => $extendedWarrantyData,
            'layout' => 'layouts.main', 
        );
        return view('admin.sale.create_hirise',$data);
    }

     public function createInvoice($id) {
         $saleData = Sale::where("id",$id)->get()->first();
         $hiriseData = Hirise::where("sale_id",$id)->get()->first();
         $extendedWarrantyData = ExtendedWarranty::where("sale_id",$id)->get()->first();
         $data  = array(
            'saleData' => $saleData, 
            'hiriseData' => $hiriseData,
            'extendedWarrantyData' => $extendedWarrantyData,
            'layout' => 'layouts.main', 
        );
        return view('admin.sale.ew_invoice',$data);
    }

    public function createHirise_DB(Request $request) {
        try {
            $this->validate($request,[
                'sale_id'=>'required',
                'amount'=>'required',
                'invoice'=>'required',
                // 'extended_warranty_invoice'=>'required_if:ew_invoice,yes',
            ],[
                'sale_id.required'=> 'This is required.',
                'amount.required'=> 'This is required.',  
                'invoice.required'=> 'This is required.',  
                // 'extended_warranty_invoice.required_if'=> 'This is required.',  
            ]);

            DB::beginTransaction();

            $getdata = Hirise::where('sale_id', $request->input('sale_id'))->get('id')->first();
            if ($request->input('amount') == $request->input('showroom_price')) {
                if ($getdata['id'] > 0) {

                    return back()->with('error','You are not authorized for update');
            }
            else{
                $sale = Sale::where('id',$request->input('sale_id'))->first();
                if(!isset($sale->id)){
                    return back()->with('error','Sale Not Found.')->withInput();
                }
                    $hirisedata = HiRise::insertGetId(
                    [
                        'sale_id'=>$request->input('sale_id'),
                        'amount'=>$request->input('amount'),
                        'invoice'=>$request->input('invoice'),
                    ]
                ); 

                // if($request->input('ew_invoice') == 'yes' && !empty($request->input('extended_warranty_invoice'))){

                //     $extended_warrantydata = ExtendedWarranty::where('sale_id', $request->input('sale_id'))->first();
                //      if($extended_warrantydata){
                //          $extended_warranty = ExtendedWarranty::where('sale_id',$request->input('sale_id'))->update(
                //                                 [ 
                //                                     'invoice_number'=>$request->input('extended_warranty_invoice'),
                //                                 ]);
                //     }
                // }

            }
            }else{
                DB::rollback();
                return redirect('/admin/sale/hirise/'.$request->input('sale_id'))->with('error','Please enter rigth amount.');
            }
            
            if($hirisedata == NULL) {
                DB::rollback();
                return redirect('/admin/sale/hirise/'.$request->input('sale_id'))->with('error','some error occurred');
            } else{
                /* Add action Log */
                CustomHelpers::userActionLog($action='Create Hirise',$hirisedata,$sale->customer_id);
                DB::commit();
                return redirect('/admin/sale/hirise/'.$request->input('sale_id'))->with('success','Successfully Created Hirise Form.');
             }    
        
        }  catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return redirect('/admin/sale/hirise/'.$request->input('sale_id'))->with('error','some error occurred'.$ex->getMessage());
        }
    }

     public function createEwInvoice_DB(Request $request) {
        try {
            $this->validate($request,[
                'sale_id'=>'required',
                'extended_warranty_invoice'=>'required_if:ew_invoice,yes',
            ],[
                'sale_id.required'=> 'This is required.',  
                'extended_warranty_invoice.required_if'=> 'This is required.',  
            ]);

            DB::beginTransaction();

            $getdata = ExtendedWarranty::where('sale_id', $request->input('sale_id'))->get('id')->first();
            // print_r($getdata->toArray());die;
            if (!isset($getdata->id)) {
                return back()->with('error','Extended Warranty Data Not Found.')->withInput();
                // return back()->with('error','You are not authorized for update');
            }
            else{
                $sale = Sale::where('id',$request->input('sale_id'))->first();
                if(!isset($sale->id)){
                    return back()->with('error','Sale Not Found.')->withInput();
                }
                if($request->input('ew_invoice') == 'yes' && !empty($request->input('extended_warranty_invoice'))){

                    $extended_warrantydata = ExtendedWarranty::where('sale_id', $request->input('sale_id'))->first();
                     if(isset($extended_warrantydata->id)){
                         if(!empty($extended_warrantydata->invoice_number)){
                            return back()->with('error','You are not authorized for update.')->withInput();
                         }
                         $extended_warranty = ExtendedWarranty::where('sale_id',$request->input('sale_id'))->update([ 
                                                'invoice_number'=>$request->input('extended_warranty_invoice'),
                                            ]);

                         if($extended_warranty==NULL) {
                            DB::rollback();
                            return redirect('/admin/sale/ew/invoice/'.$request->input('sale_id'))->with('error','some error occurred');
                        } else{
                            /* Add action Log */
                            // CustomHelpers::userActionLog($action='Create Hirise',$hirisedata,$sale->customer_id);
                            DB::commit();
                            return redirect('/admin/sale/ew/invoice/'.$request->input('sale_id'))->with('success','Successfully Updated Ew Invoice.');
                         } 
                    }
                }

            }
           
               
        
        }  catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return redirect('/admin/sale/ew/invoice/'.$request->input('sale_id'))->with('error','some error occurred'.$ex->getMessage());
        }
    }


    // public function createRto($id) {
    //     $bookingData = Sale::leftJoin('product','sale.product_id','product.id')
    //                     ->leftJoin('store','sale.store_id','store.id')
    //                     ->leftJoin('customer','sale.customer_id','customer.id')
    //                     ->get(['sale.*','customer.name as customer_name','product.model_category','product.model_variant','product.model_name','store.name as store_name'])->where('id',$id)->first();
    //     $rtoData = RtoModel::where("sale_id",$id)->get()->first();
    //      $data  = array(
    //         'bookingData' => $bookingData, 
    //         'rtoData' => $rtoData,
    //         'layout' => 'layouts.main', 
    //     );
    //     return view('admin.create_rto',$data);
    // }

    // public function createRto_db(Request $request) {
    //     try {
    //         $this->validate($request,[
    //             'rto_type'=>'required',
    //             'rto_amount'=>'required',
    //             'rto_finance'=>'required',
    //             'rto_app_no'=>'required',
    //         ],[
    //             'rto_type.required'=> 'This is required.',
    //             'rto_amount.required'=> 'This is required.',  
    //             'rto_finance.required'=> 'This is required.',  
    //             'rto_app_no.required'=> 'This is required.',  
    //         ]);
    //         $getdata = RtoModel::where('sale_id', $request->input('sale_id'))->get('id')->first();
    //         if ($getdata['id'] > 0) {
    //             $rtodata = RtoModel::where('sale_id',$request->input('sale_id'))->update(
    //                 [
    //                     'customer_id'=>$request->input('customer_id'),
    //                     'rto_type'=>$request->input('rto_type'),
    //                     'rto_amount'=>$request->input('rto_amount'),
    //                     'rto_finance'=>$request->input('rto_finance'),
    //                     'application_number'=>$request->input('rto_app_no'),
    //                 ]
    //             );
    //         }else{
    //             $rtodata = RtoModel::insertGetId(
    //                 [
    //                     'sale_id'=>$request->input('sale_id'),
    //                     'customer_id'=>$request->input('customer_id'),
    //                     'rto_type'=>$request->input('rto_type'),
    //                     'rto_amount'=>$request->input('rto_amount'),
    //                     'rto_finance'=>$request->input('rto_finance'),
    //                     'application_number'=>$request->input('rto_app_no'),
    //                     'created_by' => Auth::id(),
    //                 ]
    //             );
    //         }

    //         if($rtodata==NULL) {
    //                return redirect('/admin/sale/rto/'.$request->input('sale_id'))->with('error','some error occurred');
    //         } else{
    //               return redirect('/admin/sale/rto/'.$request->input('sale_id'))->with('success','Successfully Created RTO .');
    //          }    
        
    //     }  catch(\Illuminate\Database\QueryException $ex) {
    //         return redirect('/admin/sale/rto/'.$request->input('sale_id'))->with('error','some error occurred'.$ex->getMessage());
    //     }
    // }

    public function createInsurance($id) {
        $checkOrder = SaleOrder::where('sale_id',$id)->first();
        if(!isset($checkOrder->id))
        {
            return redirect('/admin/sale/order/'.$id)->with('error','Please Fill Order Firstly');
        }
        $bookingData = Sale::where("id",$id)->get()->first();
        $insData = Insurance::where("sale_id",$id)
                    ->select('insurance.*',
                        DB::raw("group_concat(concat(insurance_type,'-',policy_tenure,' year') SEPARATOR ' ') as insur_type"),
                        DB::raw("(select name from insurance_company where id = insurance.insurance_co) as insur_company_name"),   
                        DB::raw("(select name from insurance_company where id = insurance.cpa_company) as cpa_company_name")   
                    )
                    ->orderBy('id')
                    ->groupBy('sale_id')
                    ->get()->first();
        $data  = array(
            'bookingData' => $bookingData, 
            'insData' => $insData,
            'layout' => 'layouts.main', 
        );
        return view('admin.sale.create_insurance',$data);
    }

    public function createInsurance_db(Request $request) {
        try {

            $arr = [];
            $arr_msg = [];
            $getdata = Insurance::where('sale_id', $request->input('sale_id'))
                                    ->first();

            if(!isset($getdata->id)){
                return back()->with('error','Insurance Not-Found For This Sale-No.')->withInput();
            }
            else{
                if($getdata->insurance_co != $getdata->cpa_company && !empty($getdata->cpa_company))
                {
                    $arr['cpa-ins_policy'] = 'required';  
                    
                    $arr_msg['cpa-ins_policy.required'] = 'This Field is required';
                }
            }
            
            $this->validate($request,array_merge($arr,[
                'ins_co'=>'required',
                'ins_type'=>'required',
                'ins_amount'=>'required',
                'ins_policy'=>'required'
            ]),array_merge($arr_msg,[
                'ins_co.required'=> 'This Field is required.',
                'ins_type.required'=> 'This Field is required.',  
                'ins_amount.required'=> 'This Field is required.',  
                'ins_policy.required'=> 'This Field is required.',  
            ]));
            $sale_data = Sale::where('id',$request->input('sale_id'))
                                ->whereIn('sale.store_id',CustomHelpers::user_store())
                                ->select('id','customer_id')->first();
            if(!isset($sale_data->id)){
                return back()->with('error','Sale Not Found.');
            }
            if (isset($getdata->id)) {
                if(!empty($getdata->policy_number)){
                    return back()->with('error','You are not authorized for update');
                }
                else{
                    DB::beginTransaction();
    
                    $data = [];
                    // check cpa policy number filled or not
                    if(count($arr) > 0){
                        $data['cpa_policy_number']  =  $request->input('cpa-ins_policy');
                    }else{
                        $data['cpa_policy_number']  =  $request->input('ins_policy');
                    }
                    $check_insur = Insurance::where('sale_id',$request->input('sale_id'))->where('type','new')
                                        ->pluck('id')->toArray();
                    if(!isset($check_insur[0])){
                        return back()->with('error','Insurance Not Found.')->withInput();
                    }   
                    $insdata = Insurance::whereIn('id',$check_insur)->update(
                        array_merge($data,[
                            'policy_number'=>$request->input('ins_policy'),
                            'created_by' => Auth::id()
                        ])
                    );
                    if($insdata==NULL) {
                        DB::rollback();
                           return redirect('/admin/sale/insurance/'.$request->input('sale_id'))->with('error','some error occurred');
                    } else{
                        /* Add action Log */
                        CustomHelpers::userActionLog($action='Create Insurance',$sale_data->id,$sale_data->customer_id);
                        DB::commit();
                        return redirect('/admin/sale/rto/'.$request->input('sale_id'))->with('success','Successfully Created Insurance Form.');
                    }    
                }

            }
        
        }  catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return redirect('/admin/sale/insurance/'.$request->input('sale_id'))->with('error','some error occurred'.$ex->getMessage());
        }
    }

    public function sale_list() {
         return view('admin.sale.sale_list',['layout' => 'layouts.main']);
    }

    public function sale_list_api(Request $request,$type) {
        //echo $type; die();
       // if ($type == 'all') {
         //   $type = ($type == 'all') ? ['pending','cancelled','cancelRequest','done'] : '';
       // }elseif($type == 'done'){
       //     $type = ($type == 'done') ? ['done'] : '';
        //}elseif ($type == 'pending') {
        //    $type = ($type == 'pending') ? ['pending'] : '';
        //}
        DB::enableQueryLog();
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
         
        $api_data= Sale::leftJoin('product','sale.product_id','product.id')
            ->leftJoin('payment',function($join){
                $join->on(DB::raw('payment.type = "sale" and payment.sale_id'),'=','sale.id') ->where('payment.status','<>','cancelled');
            })
            ->leftJoin('customer','sale.customer_id','customer.id')
            ->leftJoin('rto','rto.sale_id','sale.id')
            ->leftJoin('sale_order','sale_order.sale_id','sale.id')
            ->leftJoin('insurance','insurance.sale_id','sale.id')
            ->leftJoin('users','users.id',DB::raw(Auth::id()))
            ->select(
                'sale.id',
                'customer.name',
                'sale.status',
                'sale.sale_no',
                'sale.sale_date',
                'sale.refunded_amount',
                'sale.balance',
                'rto.approve',
                // DB::raw('IFNULL(sum(payment.amount),0) as amount'),
                DB::raw('IFNULL((select sum(payment.amount) from payment where sale_id = sale.id and type = "sale" and status <>  "cancelled"),0) as amount'),
                DB::raw('concat(product.model_category,"-",product.model_name,"-",product.model_variant) as product_name'),
                'users.user_type',
                'users.role'
            )
            -> groupBy('sale.id');
         if($type == 'done'){
            $api_data = $api_data->where('sale.status','done')
                        ->where(function($query) {
                            $query->where('sale_order.product_frame_number','<>',' ');
                        });
         }elseif($type == 'pending'){
            $api_data = $api_data->where('sale.status','pending')
            ->where(function($query) {
                   $query->whereNull('insurance.policy_number')
                    ->orwhereNull('sale_order.product_frame_number');
            });
         }
        //  elseif ($type == 'all') {
            
        //  }

            $api_data->whereIn('sale.store_id',CustomHelpers::user_store())->where('sale.tos','new');
            // if(Auth::user()->user_type != 'superadmin' && Auth::user()->role != 'Superadmin')
            // {
            //     $api_data->whereIn('sale.store_id',explode(',',Auth::user()->store_id));
            // }
            if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('product.model_name','like',"%".$serach_value."%")
                    ->orwhere('product.model_variant','like',"%".$serach_value."%")
                    ->orwhere('product.color_code','like',"%".$serach_value."%")
                    ->orwhere('sale.sale_no','like',"%".$serach_value."%")
                    ->orwhere('customer.name','like',"%".$serach_value."%")
                    ->orwhere('sale.sale_date','like',"%".$serach_value."%")
                    ->orwhere('sale.balance','like',"%".$serach_value."%")
                    // ->orwhere('IFNULL(sum(payment.amount),0)','like',"%".$serach_value."%")
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
        $queries = DB::getQueryLog();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

    public function fillPendingDetails(Request $request, $sale_id)
    {
        $redirect = CustomHelpers::checkSaleNextTab($sale_id);
        return redirect($redirect);
    }
    public function sale_pay($id) {
        $saleData = Sale::where("id",$id)->get(); 
        $paid = DB::table('payment')->where('status','<>','cancelled')->where('type','sale')->where('sale_id',$id)->sum('amount');
        $pending = DB::table('payment')->where('status','pending')->where('type','sale')->where('sale_id',$id)->sum('amount');
        $secAmt = DB::table('payment')->where('type','sale')->where('sale_id',$id)->where('status','<>','cancelled')->sum('security_amount');
        // $pay_mode = DB::table('master')->where('type','payment_mode')->select('key','value')->get();
        $pay_req_id = PaymentRequest::where('type','sale')->where('type_id',$id)->first();
        $data = array(
            'saleData' => $saleData, 
            'paid' => $paid,
            'secAmt'    =>  $secAmt,
            'pending_am' =>  $pending,
            'pay_req_id'    =>  $pay_req_id,
            // 'pay_mode'  =>  $pay_mode,
            'layout' => 'layouts.main'
        );
        return view('admin.sale.pay_order',$data);
    }

    public function sale_pay_db(Request $request) {
         try {
            $timestamp=date('Y-m-d G:i:s');
            $this->validate($request,[
                'amount'=>'required',
                'payment_mode'=>'required',
                'transaction_number'=>'required',
                'transaction_charges'=>'required',
                'receiver_bank_detail'=>'required_unless:payment_mode,Cash',
                // 'security_amount'   =>  'required'
            ],[
                'amount.required'=> 'This field is required.', 
                'payment_mode.required'=> 'This field is required.', 
                'transaction_number.required'=> 'This field is required.', 
                'transaction_charges.required'=> 'This field is required.', 
                'receiver_bank_detail.required_unless'=> 'This field is required.', 
                // 'security_amount.required'=> 'This field is required.'
            ]);
            DB::beginTransaction();
           
                $total_amount = Sale::where('id',$request->input('sale_id'))->select('balance','payment_amount')->first();
                $paid_amount = Payment::where('type','sale')->where('sale_id',$request->input('sale_id'))->where('status','<>','cancelled')->sum('amount');
                // echo "<br>".$paid_amount.'<br>'.$request->input('amount').'<br>';die();
                $paid_amount = $paid_amount + $request->input('amount'); 

                if ($total_amount['balance'] < $paid_amount) {
                    return redirect('/admin/sale/pay/'.$request->input('sale_id'))->with('error','Your Amount too more .');
                }else{
                    $arr =[];
                    if(in_array($request->input('payment_mode'),CustomHelpers::pay_mode_received()))
                    {
                        $arr = [
                            'status'    =>  'received'
                        ];
                        $payment_amount = $total_amount->payment_amount + $request->input('amount');
                        $update = Sale::where('id',$request->input('sale_id'))->update([
                            'payment_amount' => $payment_amount
                        ]);
                    }
                    if($request->input('payment_mode') != 'Cash')
                    {
                        $arr    =   [
                            'receiver_bank_detail' => $request->input('receiver_bank_detail')
                        ];
                    }
                    $paydata = Payment::insertGetId(
                    array_merge($arr,[
                        'sale_id'=> $request->input('sale_id'),
                        'type' => 'sale',
                        'payment_mode' => $request->input('payment_mode'),
                        'transaction_number' => $request->input('transaction_number'),
                        'transaction_charges' => $request->input('transaction_charges'),
                        'amount' => $request->input('amount'),
                        'store_id' => $request->input('store_id')
                    ])
                    );
                   
                    if($paydata == NULL) {
                        DB::rollback();
                        return redirect('/admin/sale/pay/'.$request->input('sale_id'))->with('error','some error occurred');
                    }

                }
            
        }  catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return redirect('/admin/sale/pay/'.$request->input('sale_id'))->with('error','some error occurred'.$ex->getMessage());
        }
        DB::commit();
        return redirect('/admin/sale/pay/'.$request->input('sale_id'))->with('success','Amount Successfully Paid .');

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
    public function security_amount_list(Request $request){
        return view('admin.sale.security_amount_list',['layout' => 'layouts.main']);

    }
    public function security_amount_list_api(Request $request) {      
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
        
        $api_data= SecurityModel::leftJoin('sale','sale.id','security_amount.sale_id')
            ->leftJoin('store','store.id','security_amount.store_id')
            ->leftJoin('users','users.id',DB::raw(Auth::id()))
            ->leftJoin('payment',function($join){
            $join->on(DB::raw('payment.type = "security" and payment.type_id'),'=','security_amount.id')
                    ->where('payment.status','<>','cancelled');
             })
            ->select(
                'security_amount.id',
                'sale.status',
                'store.name as store_name',
                'sale.sale_no',
                'security_amount.name',
                'security_amount.mobile',
                'security_amount.reason',
                'security_amount.security_number',
                'security_amount.total_amount',
                'security_amount.refund_amount',
                DB::raw('sum(payment.amount) as security_amount'),
                'users.user_type',
                'users.role'
            )
            -> groupBy('security_amount.id');
            // if(Auth::user()->user_type != 'superadmin' && Auth::user()->role != 'superadmin')
            // {
            //     $api_data->whereIn('sale.store_id',explode(',',Auth::user()->store_id));
            // }
            if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('sale.sale_no','like',"%".$serach_value."%")
                    ->orwhere('security_amount.name','like',"%".$serach_value."%")
                    ->orwhere('security_amount.mobile','like',"%".$serach_value."%")
                    ->orwhere('security_amount.security_number','like',"%".$serach_value."%")
                    ->orwhere('security_amount.reason','like',"%".$serach_value."%")
                    ->orwhere('payment.amount','like',"%".$serach_value."%")
                    ->orwhere('store.name','like',"%".$serach_value."%")                    
                    ;
                });
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                'security_amount.id',
                'security_amount.security_number',
                'sale.sale_no',
                'store.name',
                'security_amount.name',
                'security_amount.mobile',
                'security_amount.reason',
                'security_amount.total_amount',
                'security_amount.refund_amount',
                DB::raw('sum(payment.amount) as security_amount'),
                'sale.status',
                'users.user_type',
                'users.role'
                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('security_amount.id','desc');      
        
        $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    

    }
    public function sale_order($id) {

        $saleData = Sale::leftJoin('product','sale.product_id','product.id')->leftJoin('store','sale.store_id','store.id')
                        ->leftJoin('customer','sale.customer_id','customer.id')
                        ->get(['sale.*','customer.name as customer_name','product.model_category','product.model_variant','product.model_name','store.name as store_name','store.store_type'])->where('id',$id)->first();
        $paid_amount = Payment::where('sale_id',$id)->where('status','received')->where('type','sale')->sum('amount');
        if($saleData->balance > $paid_amount)
        {
            return redirect('/admin/sale/pay/'.$id)->with('error','Full payment not received in account.');
        }
        $frame_no = ProductDetails::where('product_id',$saleData['product_id'])
                                    ->where('store_id',$saleData['store_id'])
                                    ->where('status','ok')
                                    ->orderBy('manufacture_date')
                                    ->select('frame',
                                        DB::raw('DATEDIFF(CURRENT_DATE,manufacture_date) as frame_duration')
                                    )->get();
        $accessories = Part::select('part.id','part.name','part.part_number as part_no',
                                DB::raw('1 as qty'), 'part.price as mrp', 'part.unit_price')->get();
        $orderData = SaleOrder::where('sale_id', $id)->leftJoin('product_details','product_details.frame','sale_order.battery_number')
                                ->select('sale_order.*','product_details.id as battery_id')
                                ->get()->first();
        // $pending_item = OrderPendingModel::leftJoin('accessories','order_pending_item.accessories_id','accessories.id')->get('order_pending_item.*')->where('order_id',$orderData['id']);
        // if(!empty($pending_item))
        // {
        //     $pending_item = $pending_item->toArray();
        //     $pending_item = array_column($pending_item,'accessories_id');
        // }else{
        //     $pending_item = (!empty($pending_item)) ? $pending_item->toArray() : array();
        // }

        $battery = ProductModel::join('product_details','product_details.product_id','product.id')
                                    ->where('product.type','battery')
                                    ->where('store_id',$saleData['store_id'])
                                    ->where('product_details.status','<>','sale')
                                    ->where('product_details.move_status',0)
                                    ->whereIn('product_details.store_id',CustomHelpers::user_store())
                                    ->select(
                                        'product_details.id',
                                        'product_details.frame',
                                        DB::raw('TIMESTAMPDIFF( YEAR, product_details.manufacture_date, now() ) as year'),
                                        DB::raw('TIMESTAMPDIFF( MONTH, product_details.manufacture_date, now() ) % 12 as month'),
                                        DB::raw('ROUND(TIMESTAMPDIFF( DAY, product_details.manufacture_date, now() ) % 30.4375) as day')
                                    )->orderBy('product_details.manufacture_date','asc')
                                    ->get();
        $tyre = Master::where('type','tyre')->orderBy('order_by','ASC')->get();
        $data = array(
            'saleData' => $saleData, 
            'battery'   =>  $battery,
            'tyre'  =>  $tyre,
            'frame_no' => $frame_no,
            'accessories' => $accessories,
            'orderData' => (!empty($orderData)) ? $orderData->toArray() : array(),
            // 'pending_item' => $pending_item,
            'layout' => 'layouts.main'
        );
         return view('admin.sale.sale_order',$data);
        // if ($bookingData['total_amount'] == $paid_amount) {
        //      return view('admin.booking_order',$data);
        // }else{
        //      return redirect('/admin/booking/pay/'.$id);
        // }
        
    }

    public function sale_order_db(Request $request) {
         try {
            $timestamp = date('Y-m-d G:i:s');
            $this->validate($request,[
                'frame'=>'required',
                'product_name'=>'required',
                'store_name'=>'required',
                'battery_no'=>'required',
                'key_no'=>'required',
                'tyre_make'=>'required',
                'front_tyre_no'=>'required',
                'rear_tyre_no'=>'required'
            ],[
                'frame.required'=> 'This field is required.', 
                'product_name.required'=> 'This field is required.', 
                'store_name.required'=> 'This field is required.', 
                'battery_no.required'=> 'This field is required.', 
                'key_no.required'=> 'This field is required.', 
                'tyre_make.required'=> 'This field is required.',
                'front_tyre_no.required'=> 'This field is required.', 
                'rear_tyre_no.required'=> 'This field is required.'
            ]);
           
            DB::beginTransaction();
            $battery_pd_id = $request->input('battery_no');
            $battery_no = ProductDetails::where('id',$battery_pd_id)->select('frame','product_details.product_id'
                                ,'product_details.store_id')->first();
            // $request->input('battery_no') = $battery_no;
            $sale = Sale::where('id',$request->input('sale_id'))->first();
            if(!isset($sale->id)){
                return back()->with('error','Sale Not Found.')->withInput();
            }
            $order_id = SaleOrder::where('sale_id',$request->input('sale_id'))->get('id')->first();
                if ($order_id == NULL) {
                     $orderData = SaleOrder::insertGetId(
                    [
                        'sale_id'=> $request->input('sale_id'),
                        'customer_id' => $request->input('customer_id'),
                        'store_id' => $request->input('store_id'),
                        'amount' => $request->input('total_amount'),
                        'product_frame_number' => $request->input('frame'),
                        'battery_number' => $battery_no->frame,
                        'key_number' => $request->input('key_no'),
                        'tyre_make' => $request->input('tyre_make'),
                        'front_tyre_no' => $request->input('front_tyre_no'),
                        'rear_tyre_no' => $request->input('rear_tyre_no'),
                        'quantity' => 1,
                        'order_date' => $timestamp,
                        'created_by' => Auth::id(),
                    ]);
                    $updateProductDetails = ProductDetails::where('frame',$request->input('frame'))
                    ->update([
                        'status'    =>  'sale'
                    ]);
                    ProductDetails::where('frame',$battery_no->frame)
                    ->update([
                        'status'    =>  'sale'
                    ]);
                    // change sale status
                    Sale::where('id',$request->input('sale_id'))->update(['status'=>'done']);
                    //stock battery update
                    Stock::where('product_id',$battery_no->product_id)
                            ->where('store_id',$battery_no->store_id)
                            ->increment('sale_qty',1);
                    Stock::where('product_id',$battery_no->product_id)
                            ->where('store_id',$battery_no->store_id)
                            ->decrement('quantity',1);

                if($orderData == NULL || $updateProductDetails == NULL) {
                    DB::rollback();
                    return redirect('/admin/sale/order/'.$request->input('sale_id'))->with('error','some error occurred');
                } 
                /* Add action Log */
                CustomHelpers::userActionLog($action='Create Order',$orderData,$sale->customer_id);
            }else{

                return back()->with('error','You are not authorized for update');
               
              }
            
        }  catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return redirect('/admin/sale/order/'.$request->input('sale_id'))->with('error','some error occurred'.$ex->getMessage());
        }
        DB::commit();
        return redirect('/admin/sale/order/'.$request->input('sale_id'))->with('success','Order Created Successfully .');

    }

    //Sale Cancel
    public function saleCancelRequest(Request $request) {
        $saleId = $request->input('SaleId');
        $desc = $request->input('desc');
        $date = $request->input('date');
        DB::beginTransaction();
        $checkall = $this->checkall($saleId);
        if($checkall)
        {
            $checkStore = CustomHelpers::CheckAuthStore($checkall['store_id']);

            if ($checkStore)
            {
                if($checkall['sale_status'] == 'cancelled')
                {
                    return 'Info, This Sale Already Cancelled';
                }
                elseif($checkall['sale_status'] == 'cancelRequest')
                {
                    return 'Error, You have Already Requested for this Sale';
                }
                if(!$checkall['payment_id'])
                {
                    // direct sale cancel
                    $saleCancel = $this->saleCancelDirect($saleId,$desc,$date);
                    if($saleCancel != 'success-direct')
                    {
                        DB::rollBack();
                        return $saleCancel;
                    }
                    DB::commit();
                    $this->cancelSaleMailSend($saleId,'cancel');
                    return $saleCancel;
                }
                elseif(!$checkall['sale_order_id'])
                {
                    // request to sale cancel (case - have a payment but not create order)
                    $saleRequestCancel = $this->saleRequestCancel($saleId,$desc,$date);
                    if($saleRequestCancel != 'success-request')
                    {
                        DB::rollBack();
                        return $saleRequestCancel;
                    }
                    DB::commit();
                    $this->cancelSaleMailSend($saleId,'request');
                    return $saleRequestCancel;
                }
                elseif(!$checkall['hirise_id'])
                {
                    if(!$checkall['insurance_id'])
                    {
                        // request to sale cancel
                        $saleRequestCancel = $this->saleRequestCancel($saleId,$desc,$date);
                        if($saleRequestCancel != 'success-request')
                        {
                            DB::rollBack();
                            return $saleRequestCancel;
                        }
                        DB::commit();
                        $this->cancelSaleMailSend($saleId,'request');
                        return $saleRequestCancel;
                    }
                    if(!$checkall['rto_id'])
                    {
                        // request to sale cancel
                        $saleRequestCancel = $this->saleRequestCancel($saleId,$desc,$date);
                        if($saleRequestCancel != 'success-request')
                        {
                            DB::rollBack();
                            return $saleRequestCancel;
                        }
                        DB::commit();
                        $this->cancelSaleMailSend($saleId,'request');
                        return $saleRequestCancel;
                    }
                    else{
                        if(!$checkall['rto_approve'])   //when approve = 0
                        {
                            // request to sale cancel
                            $saleRequestCancel = $this->saleRequestCancel($saleId,$desc,$date);
                            if($saleRequestCancel != 'success-request')
                            {
                                DB::rollBack();
                                return $saleRequestCancel;
                            }
                            DB::commit();
                            $this->cancelSaleMailSend($saleId,'request');
                            return $saleRequestCancel;
                        }
                        else{
                            return 'this sale will not be cancel because RTO hase been approved';
                        }
                    }
                }
                elseif(!$checkall['insurance_id'])
                {
                    // request to sale cancel
                    $saleRequestCancel = $this->saleRequestCancel($saleId,$desc,$date);
                    if($saleRequestCancel != 'success-request')
                    {
                        DB::rollBack();
                        return $saleRequestCancel;
                    }
                    DB::commit();
                    $this->cancelSaleMailSend($saleId,'request');
                    return $saleRequestCancel;

                }
                elseif(!$checkall['rto_id'])
                {
                    // request to sale cancel
                    $saleRequestCancel = $this->saleRequestCancel($saleId,$desc,$date);
                    if($saleRequestCancel != 'success-request')
                    {
                        DB::rollBack();
                        return $saleRequestCancel;
                    }
                    DB::commit();
                    $this->cancelSaleMailSend($saleId,'request');
                    return $saleRequestCancel;
                }

                if($checkall['rto_id'])
                {
                    if(!$checkall['rto_approve'])   //when approve = 0
                    {
                        // request to sale cancel
                        $saleRequestCancel = $this->saleRequestCancel($saleId,$desc,$date);
                        if($saleRequestCancel != 'success-request')
                        {
                            DB::rollBack();
                            return $saleRequestCancel;
                        }
                        DB::commit();
                        $this->cancelSaleMailSend($saleId,'request');
                        return $saleRequestCancel;
                    }
                    else{
                        return 'this sale will not be cancel because RTO hase been approved';
                    }
                }
                
            }
            else{
             return 'You Are Not Authorized For Cancel This Sale';
            }

        }
        else{
            return 'This Sale number was not found';
        }
        return 'Something Went Wrong';
    }
    public function cancelSaleMailSend($saleId,$type){
        $sale = Sale::where('id',$saleId)->first();
        $storeName = Store::where('id',$sale->store_id)->first();
        $user_name = Auth::user()->name;
        $to_info = Settings::where('name','sale_cancel_email')->first();
        $email_view = 'admin.emails.saleCancel';
        $subject = 'Sale Cancel';
        if($type == 'request'){
            $email_view = 'admin.emails.saleCancelRequest';
            $subject = 'Sale Cancel Request';
        }
        if(isset($to_info->id)){
            $email = $to_info->value;
            Mail::send($email_view, ['sale' =>  $sale,'store'=>$storeName->name,'amount'=>$sale->balance,'sale_no'=>$sale->sale_no], function($message) use ($email,$subject)
            {
                $message->to($email,'JP Honda')->subject($subject);
            });
        }
    }
    public function saleCancelDirect($saleId,$desc,$datetime)
    {
        try{
            $date = date('Y-m-d',strtotime($datetime));
            $arr = [
                    'request_id'    =>  'Sale-'.$saleId.'-'.$date,
                    'cancel_type'    =>  'sale',
                    'type_id'   =>  $saleId,
                    'desc'  =>  $desc,
                    'user_id'   =>  Auth::Id(),
                    'request_date'  =>  $datetime,
                    'type'  =>  'auto',
                    'status'    =>  'approve'
            ];
            $insertId = CancelRequest::insertGetId($arr);
            $updateSale = Sale::where('id',$saleId)->update(['status'=>'cancelled']);
            $getProductIdAndStoreId = Sale::where('id',$saleId)
                                    ->select('sale.product_id','sale.store_id')
                                ->first();

            // Update stock product data
            Stock::where('product_id',$getProductIdAndStoreId->product_id)
                        ->where('store_id',$getProductIdAndStoreId->store_id)->increment('quantity',1);
            Stock::where('product_id',$getProductIdAndStoreId->product_id)
                        ->where('store_id',$getProductIdAndStoreId->store_id)->decrement('sale_qty',1);
            
            // update vehicle addon stock
            $this->updateVehicleAddonStock($getProductIdAndStoreId,$saleId);

            // cancel all payment request for this sale id
            $this->cancelPaymentRequest('sale',$saleId);

            if($insertId && $updateSale)
            {
                return 'success-direct';
            }
            else
            {
                return $insertId.'---'.$updateSale;
            }
        }  catch(\Illuminate\Database\QueryException $ex) {
            return 'some error occurred'.$ex->getMessage();
        }
        
    }
    // update Vehicle_addon_stock after cancenl sale
    public function updateVehicleAddonStock($getProductIdAndStoreId,$saleId){
        $getProduct = ProductModel::where('id',$getProductIdAndStoreId->product_id)->first();
        $model_name = str_replace(' ','_',strtolower($getProduct->model_name));
        $new_addon_om = 'owner_manual_'.$model_name;
            // update Stock for Vehicle Addon
            $pending_addon = SalePendingAddon::where('type','addon')->where('sale_id',$saleId)
                                                ->where('stock_status',0)->whereNull('issue_date')->get();
            $pending_addon_ids = []; $pending_addon_name = [];
            foreach($pending_addon as $key => $val){
                array_push($pending_addon_ids,$val->id);
                array_push($pending_addon_name,$val->type_name);
            }
            if(count($pending_addon_ids) > 0){
                // delete pending addon items
                $delete_sale_pending_addon = SalePendingAddon::whereIn('id',$pending_addon_ids)
                                                ->delete();
            }
            $addon = VehicleAddon::whereRaw("key_name <> 'owner_manual'")
                                    ->whereNotIn('key_name',$pending_addon_name)->pluck('key_name')->toArray();
            if(!in_array($new_addon_om,$pending_addon_name)){
                array_push($addon,$new_addon_om);
            }
            foreach($addon as $k => $v){
                $flag = 1;
                if($v == 'saree_guard'){
                    if($getProduct->model_category == 'SC'){
                        $flag = 0;
                    }
                }
                if($flag == 1){
                    $inc_inQty = VehicleAddonStock::where('vehicle_addon_key',$v)
                                                    ->where('store_id',$getProductIdAndStoreId->store_id)
                                                    ->increment('qty',1);
                    $dec_inQty = VehicleAddonStock::where('vehicle_addon_key',$v)
                                                    ->where('store_id',$getProductIdAndStoreId->store_id)
                                                    ->decrement('sale_qty',1);
                    if($v == $new_addon_om){
                        // if owner manual with model are match then also deduct in owner_manual
                        $inc_inQty = VehicleAddonStock::where('vehicle_addon_key','owner_manual')
                                                    ->where('store_id',$getProductIdAndStoreId->store_id)
                                                    ->increment('qty',1);
                        $dec_inQty = VehicleAddonStock::where('vehicle_addon_key','owner_manual')
                                                    ->where('store_id',$getProductIdAndStoreId->store_id)
                                                    ->decrement('sale_qty',1);
                    }
                }
            }
    }
    public function saleRequestCancel($saleId,$desc,$datetime)
    {
        try{
            $date = date('Y-m-d',strtotime($datetime));
            $arr = [
                    'request_id'    =>  'Sale-'.$saleId.'-'.$date,
                    'cancel_type'   =>  'sale',
                    'type_id'   =>  $saleId,
                    'desc'  =>  $desc,
                    'user_id'   =>  Auth::Id(),
                    'request_date'  =>  $datetime,
                    'type'  =>  'custom',
                    'status'    =>  'pending'
            ];
            $insertId = CancelRequest::insertGetId($arr);
            $updateSale = Sale::where('id',$saleId)->update(['status'=>'cancelRequest']);
            if($insertId && $updateSale)
            {
                return 'success-request';
            }
            else
            {
                return 'Try Again, Internal Error'; 
            }
        }  catch(\Illuminate\Database\QueryException $ex) {
                return 'some error occurred'.$ex->getMessage();
            }
    }
    public function checkSale($saleId)
    {
        $check = Sale::where('id',$saleId)->first();
        if($check)
        {
            return 1;
        }
        else
        {
            return 0;
        }
    }
    public function checkall($saleId)
    {
        $check = Sale::leftJoin('payment',DB::raw('payment.type = "sale" and payment.sale_id'),'sale.id')
                        ->leftJoin('sale_order','sale_order.sale_id','sale.id')
                        ->leftJoin('hirise','hirise.sale_id','sale.id')
                        ->leftJoin('insurance','insurance.sale_id','sale.id')
                        ->leftJoin('rto','rto.sale_id','sale.id')
                        ->where('sale.id',$saleId)
                        ->select('sale.id',
                                'sale.status as sale_status',
                                'payment.id as payment_id',
                                'sale_order.id as sale_order_id',
                                'hirise.id as hirise_id',
                                'insurance.id as insurance_id',
                                'rto.id as rto_id',
                                'rto.approve as rto_approve',
                                'sale.store_id'
                                    )
                        ->first();
        if(isset($check->id))
        {
            return $check->toArray();
        }
        else
        {
            return 0;
        }
    }
    public function checkRto($saleId)
    {
        $check  = RtoModel::where('sale_id',$saleId)
                    ->select('approve')
                    ->first();
        if($check)
        {
            return $check->approve;
        }
        else{
            return 'no-found';
        }
    }

    // Pending Items 
    public function createPendingItem(Request $request,$id)
    {
        $checkOrder = SaleOrder::where('sale_id',$id)->first();
        if(!isset($checkOrder->id))
        {
            return redirect('/admin/sale/order/'.$id)->with('error','Please Fill Order Firstly');
        }
        $sale = Sale::where('id',$id)->select('sale_no','pending_item','product_id')->first();
        if(!isset($sale->sale_no)){
            return back()->with('error','Sale Not Found.');
        }
        $product_info = ProductModel::where('id',$sale->product_id)->select('id','model_category','model_name')->first();
        if(!isset($product_info->id)){
            return back()->with('error','Product Data Not Found.');
        }
        $accessories = OtcSale::where('otc_sale.sale_id',$id)
                        ->where('otc_sale.with_sale',1)
                        ->leftJoin('otc_sale_detail','otc_sale_detail.otc_sale_id','otc_sale.id')
                        ->where('otc_sale_detail.type','Part')
                        ->leftJoin('part',function($join){
                            $join->on('part.id','otc_sale_detail.part_id');
                        })
                        ->select('otc_sale_detail.id',
                            'otc_sale.sale_id',    
                            'otc_sale_detail.part_id',
                            'part.name as accessories_name',
                            'otc_sale_detail.qty',
                            // DB::raw('IF(otc_sale_detail.part_id = 0, otc_sale_detail.part_desc,part.name) as accessories_name'),
                            // DB::raw('IF(otc_sale_detail.part_id = 0, "1",otc_sale_detail.qty) as qty'), 
                            'otc_sale_detail.amount'
                        )
                        ->get();
        $other_item = ['Sale Invoice','Service Book','Bag','Key Ring','Warranty Card'];
        $pending_item = OrderPendingModel::where('sale_id',$id)->first();
       
        // other pending items
        $other_pending = SalePendingAddon::where('sale_id',$id)->where('type','other')
                            ->whereNull('issue_date')->pluck('type_name')->toArray();
        $other_pending_issued = SalePendingAddon::where('sale_id',$id)->where('type','other')
                            ->whereNotNull('issue_date')->pluck('type_name')->toArray();
        
        // addon pending items
        // $addon_pending_all = SalePendingAddon::where('sale_id',$id)->where('type','addon')->get()->toArray();

        $vehicle_category = $product_info->model_category;
        // Get addon according to vehicle category
        $addon_all = VehicleAddon::whereRaw(DB::raw('find_in_set("'.$vehicle_category.'",vehicle_category)'))
                            ->whereNotIn('key_name',['owner_manual'])->select('key_name','prod_name')
                            ->get()->toArray();
        $addon_om = [
            'key_name' => str_replace(' ','_',strtolower('owner_manual_'.$product_info->model_name)),
            'prod_name' => strtoupper('Owner Manual '.$product_info->model_name)
        ];
        array_push($addon_all,$addon_om);
        // print_r($addon_all);die;
        $addon_pending_label = SalePendingAddon::where('sale_id',$id)->where('type','addon')
                            ->where('stock_status',0)->whereNull('issue_date')->pluck('type_name')->toArray();
        $addon_pending = SalePendingAddon::where('sale_id',$id)->where('type','addon')
                            ->where('stock_status',1)
                            ->whereNull('issue_date')->pluck('type_name')->toArray();
        // $pending_item = array();
        // $pending_other = array();
       
        $pending_item_all = (($pending_item)? $pending_item->toArray() : array());
        $pending_item = (($pending_item_all)?$pending_item_all['accessories_id'] : '');
        $pending_item = (($pending_item)? explode(',',$pending_item) : array() );

        $pending_other = $other_pending;
        // print_r($pending_other);die;
        $fuel = FuelModel::where('type_id',$id)->where('fuel_mode','sale')->first();
      
        $data = array(
            'addon_all' =>  $addon_all,
            'addon_pending_label' =>  $addon_pending_label,
            'addon_pending' =>  $addon_pending,
            'other_pending_issued'  =>  $other_pending_issued,
            'sale_no'   =>  $sale->sale_no,
            'select_pending_item'  =>  $sale->pending_item,
            'accessories' => $accessories,
            'pending_item' => $pending_item,    
            'pending_other' => $pending_other,    
            'other_item' => $other_item,    
            'fuel' => $fuel,
            'sale_id' => $id,    
            'layout' => 'layouts.main'
        );
        return view('admin.sale.pendingItem',$data);

    }
    public function createPendingItem_db(Request $request) {
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
            $data[1] = $request->input('addon');
            $data[2] = $request->input('other');

            // $validator->after(function ($validator) use($data) {
            //     if ($data[0] == 'yes') {
            //         if(empty($data[1]) && empty($data[2]))
            //         {
            //             $validator->errors()->add('addon', 'Any One Field is required, If you have to Choose Yes.')
            //             ->add('other', 'Any One Field is required, If you have to Choose Yes.');
            //         }
            //     }
            // });

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            DB::beginTransaction();
            
            $select_pi = $request->input('select_pending_item');
            $sale_id = $request->input('sale_id');
            $sale_data = Sale::where('id',$sale_id)->select('pending_item','store_id','customer_id','id')->first();
            if(!isset($sale_data->id)){
                return back()->with('error','Sale Not Found.');
            }
            // if(!empty($prePendingCheck->pending_item))
            // {
            //     return back()->with('error','Error, You Are Not Authorized For Update This Page');
            // }
            $other_pi = $request->input('other');
            $other_pi = !empty($other_pi)? $other_pi : [];
            $addon_pi = $request->input('addon');
            $addon_pi = !empty($addon_pi)? $addon_pi : [];
            $all_pi = array_merge($other_pi,$addon_pi);
            // print_r($all_pi);die;
            // check other addons exist in sale_pending_addon
            $check_other = SalePendingAddon::where('sale_id',$sale_id)
                            ->get()->toArray();
            // print_r($check_other);die;
            $pending_other_name = [];$pending_other_data = [];$del_other_ids = [];
            foreach($check_other as $ind => $oth_data){
                array_push($pending_other_name,$oth_data['type_name']);
                $pending_other_data[$oth_data['type_name']] = $oth_data;

                if(empty($oth_data['issue_date']) && $oth_data['stock_status'] == 1){
                    
                    if(!in_array($oth_data['type_name'],$all_pi)){
                        array_push($del_other_ids,$oth_data['id']);
                    }
                }
            }
            // delete which not selected
            if(count($del_other_ids) > 0){
                SalePendingAddon::whereIn('id',$del_other_ids)->delete();
            }
            // insert new one
            foreach($all_pi as $in => $name){
                if(in_array($name,$pending_other_name)){ // if already exist
                    // then check it's already issued ?
                    $issue_date = $pending_other_data[$name]['issue_date'];
                    if(!empty($issue_date)){
                        DB::rollBack();
                        return back()->with('error',$name.', Already Issued.');
                    }
                }else{ // when it's find new pending item
                    $in_data = [
                        'sale_id'   =>  $sale_id,
                        'type_name' =>  $name,
                        'stock_status'  =>  1
                    ];
                    if(in_array($name,$addon_pi)){
                        $in_data['type'] = 'addon';
                    }
                    if(in_array($name,$other_pi)){
                        $in_data['type'] = 'other';
                    }
                    $new_pi = SalePendingAddon::insertGetId($in_data);
                }
            }
            // for fuel
            $checkFuel = FuelModel::where('type_id',$sale_id)->where('fuel_mode','sale')
                                ->where('fuel_type','Petrol')
                                    ->first();
            if(!isset($checkFuel->id)){

                $checkfuelStock = FuelStockModel::where('fuel_type','Petrol')
                                            ->where('store_id',$sale_data->store_id)->first();
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
                    'store_id' => $sale_data->store_id,
                    'type_id'   =>  $sale_id,
                    'fuel_mode'    =>  'sale',
                    'fuel_type'    =>  'Petrol',
                    'requested_by' => Auth::id(),
                    'quantity' =>  $request->input('fuel'),
                    'status'    =>  'approved'
                ]);
                $fuelStock = FuelStockModel::where('id',$checkfuelStock->id)->decrement('quantity',$request->input('fuel'));
            }else{
                $old_fuel = $checkFuel->quantity;
                $new_fuel = $request->input('fuel');
                if($old_fuel != $new_fuel){
                    DB::rollBack();
                    return back()->with('error','Fuel Quantity Will Not be Upadte.');
                }
                if(count($all_pi) == 0 ){
                    DB::rollBack();
                    return back()->with('error','Please Select Any, If u want to Update.');
                }
            }
               
           
        }  catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return redirect('/admin/sale/pending/item/'.$request->input('sale_id'))->with('error','some error occurred'.$ex->getMessage());
        }
        /* Add action Log */
        CustomHelpers::userActionLog($action='Create Pending Item',$sale_data->id,$sale_data->customer_id);
        DB::commit();
        return redirect('/admin/sale/pending/item/'.$request->input('sale_id'))->with('success','Successfully Submitted');

    }

    //OTC
    public function createOtc(Request $request,$id)
    {
        $checkPendingItem = Sale::where('id',$id)->select('pending_item')->first();
        if(empty($checkPendingItem->pending_item))
        {
            return back()->with('error','Please Fill Pending Item Section Firstly');
        }
        $saleNo = Sale::where('id',$id)->first('sale_no');
        // $oldData = OtcModel::where('sale_id',$id)->first();
        // get model
        $model = Sale::where('sale.id',$id)
                ->join('product','product.id','sale.product_id')->first('product.model_name');
        // $pending_part_id = [];
        // $pendingItem = OrderPendingModel::where('sale_id',$id)->first();
        // if(isset($pendingItem->id)){
        //     $pending_part_ids = $pendingItem->accessories_id;
        //     if(!empty($pending_part_ids)){
        //         $pending_part_id = explode(',',$pending_part_ids);
        //     }
        // }
        // sale discount
        $sale_discount = SaleDiscount::where('type','sale')->where('type_id',$id)->where('status','approve')->sum('amount');
        $otc_sale = OtcSale::where('sale_id',$id)->first();
        $otc_accessories = [];$otc_accessories_total = [];
        if(isset($otc_sale->id)){
            $find_accessories = OtcSaleDetail::where('otc_sale_detail.otc_sale_id',$otc_sale->id)
                                ->where('otc_sale_detail.type','Part')
                                // ->whereNotIn('otc_sale_detail.part_id',$pending_part_id)
                                ->where('otc_sale_detail.otc_status',1)
                                ->where('otc_sale_detail.with_sale',1)
                                ->leftJoin('master_accessories',function($join) use($model){
                                    $join->on(DB::raw("FIND_IN_SET(otc_sale_detail.part_id,master_accessories.part_id)"),">",DB::raw('0'))
                                    ->where('master_accessories.model_name',$model->model_name);
                                })
                                ->Join('part',function($join){
                                    $join->on('part.id','otc_sale_detail.part_id');
                                })
                                ->select('otc_sale_detail.id as otc_sale_detail_id',
                                    'otc_sale_detail.otc_sale_id',
                                    'otc_sale_detail.qty',
                                    'otc_sale_detail.amount',
                                    'part.name','part.part_number',
                                    'master_accessories.discount_offset_priority as dop'
                                )->orderBy('master_accessories.discount_offset_priority','ASC')
                                ->get();
            if(isset($find_accessories[0])){
                $total_amount = 0;$total_pending_discount = 0; $total_give_discount = 0;
                foreach($find_accessories as $k => $v){
                    $otc_accessories[$k]['name'] = $v->name;
                    $otc_accessories[$k]['part_number'] = $v->part_number;
                    $otc_accessories[$k]['amount'] = $v->amount;
                    $amount = $v->amount;
                    $total_amount = $total_amount+$amount;
                    
                    $pending_amount = 0;
                    $discount_percent = 0;
                    $one_percent = 0;
                    $give_discount = 0;

                    if($sale_discount > 0){
                        // get 1 % of amount
                        $one_percent = $amount/100;
                        if($amount > $sale_discount){    //  some % discount
                            $pending_amount = $amount-$sale_discount;
                            $give_discount = $sale_discount;
                        }
                        else{  // discount 100 %
                            $pending_amount = 0;
                            $give_discount = $amount;
                        }
                        $discount_percent = round($give_discount/$one_percent,2);   // in %
                        $total_give_discount = $total_give_discount+$give_discount;  // in amount
                    }
                    $otc_accessories[$k]['discount_percent'] = $discount_percent;
                    $otc_accessories[$k]['pending_amount'] = $pending_amount;

                    $sale_discount = $sale_discount-$give_discount;
                    $total_pending_discount = $sale_discount;
                }
                $otc_accessories_total = [
                    'total_amount'  =>  $total_amount,
                    'total_pending_discount'    =>  $total_pending_discount,
                    'total_give_discount'   =>  $total_give_discount,
                    'balance'   =>  $total_amount-$total_give_discount
                ];
            }
        }   

        $otc = OtcModel::where('otc_sale_id',$otc_sale->id)
                            ->where('sale_id',$id)
                            ->orderBy('id')
                            ->get();
        $all_otc_part_id = [];
        if(isset($otc[0])){
            foreach($otc as $k => $v){
                array_push($all_otc_part_id,$v->otc_sale_detail_ids);
            }
            $all_otc_part_id = join(',',$all_otc_part_id);
            $all_otc_part_id = explode(',',$all_otc_part_id);
        }
        $check_new_invoice = OtcSaleDetail::whereNotIn('otc_sale_detail.id',$all_otc_part_id)
                                    ->where('otc_sale_detail.otc_sale_id',$otc_sale->id)
                                    ->where('otc_sale_detail.otc_status',1)
                                    ->select(
                                        DB::raw("GROUP_CONCAT(otc_sale_detail.id , '') as new_invoice_osd_id"),
                                        DB::raw("GROUP_CONCAT(otc_sale_detail.part_id , '') as new_invoice_part_id")
                                        )
                                    ->groupBy('otc_sale_detail.otc_sale_id')
                                    ->first();
        // print_r($all_otc_part_id);
        // print_r($check_new_invoice);die;

        $data = array(   
            'oldData' => $otc,    
            'check_new_invoice' => $check_new_invoice,
            'sale_id' => $id,    
            'sale_no'   =>  $saleNo->sale_no,
            'sale_discount' =>  $sale_discount,
            'otc_sale'  =>  $otc_sale,
            'otc_accessories'   =>  $otc_accessories,
            'otc_accessories_total'   =>  $otc_accessories_total,
            'layout' => 'layouts.main'
        );
        return view('admin.sale.otc',$data);

    }
    public function createOtc_db(Request $request) {
         try {
            $timestamp = date('Y-m-d G:i:s');
            $multi_invoice = $request->input('multi_invoice');
            if($multi_invoice != 'new_invoice'){
                return back()->with('error','You Already Filled that Invoice.')->withInput();
            }
            $this->validate($request,[
                'invoice_no' => 'required|unique:otc,invoice_no',
                'date' => 'required',
                'amount' => 'required'
            ],[
                'invoice_no.required'=> 'This field is required.',
                'date.required'=> 'This field is required.',
                'amount.required'=> 'This field is required.'
            ]);
            DB::beginTransaction();
            $sale_id = $request->input('sale_id');
            $sale = Sale::where('id',$sale_id)->first();
            // get otc_Sale and otc_sale_details
            if(!isset($sale->id)){
                return back()->with('error','Sale Not Found.')->withInput();
            }
            $otc_sale = OtcSale::where('sale_id',$sale_id)->where('with_sale',1)->first();
            if(!isset($otc_sale->id)){
                return back()->with('error','OTC Sale Not Found.')->withInput();
            }
            $otc = OtcModel::where('otc_sale_id',$otc_sale->id)
                            ->where('sale_id',$sale_id)
                            ->orderBy('id')
                            ->get();
            $all_otc_part_id = [];
            if(isset($otc[0])){
                foreach($otc as $k => $v){
                    array_push($all_otc_part_id,$v->otc_sale_detail_ids);
                }
                $all_otc_part_id = join(',',$all_otc_part_id);
                $all_otc_part_id = explode(',',$all_otc_part_id);
            }
            $check_new_invoice = OtcSaleDetail::whereNotIn('otc_sale_detail.id',$all_otc_part_id)
                                ->where('otc_sale_detail.otc_sale_id',$otc_sale->id)
                                ->where('otc_sale_detail.otc_status',1)
                                ->select(
                                    DB::raw("GROUP_CONCAT(otc_sale_detail.id , '') as new_invoice_osd_id"),
                                    DB::raw("GROUP_CONCAT(otc_sale_detail.part_id , '') as new_invoice_part_id")
                                    )
                                ->groupBy('otc_sale_detail.otc_sale_id')
                                ->first();
           
            if(!isset($check_new_invoice->new_invoice_osd_id)){
                return back()->with('error','OTC Sale Not Found.')->withInput();
            }
            // $check = OtcModel::where('sale_id',$sale_id)->first();
            // if($check)
            // {
            //     return back()->with('error','Error, You Are Not Authorized For Update This Page');
            // }
            // else{
                $insert = OtcModel::insertGetId([
                    'sale_id'   =>  $sale_id,
                    'otc_sale_id'   =>  $otc_sale->id,
                    'otc_sale_detail_ids'   =>  $check_new_invoice->new_invoice_osd_id,
                    'invoice_no'    =>  $request->input('invoice_no'),
                    'date'  =>  CustomHelpers::showDate($request->input('date'),'Y-m-d'),
                    'amount'    =>  $request->input('amount')
                ]);
            // }
           
        }  catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return redirect('/admin/sale/otc/'.$request->input('sale_id'))->with('error','some error occurred'.$ex->getMessage());
        }
        /* Add action Log */
        CustomHelpers::userActionLog($action='Create OTC',$insert,$sale->customer_id);
        DB::commit();
        return redirect('/admin/sale/otc/'.$request->input('sale_id'))->with('success','Successfully Submitted');

    }

    // cancel sale request listing
    public function cancelSale_list() {
        return view('admin.sale.cancelSale_list',['layout' => 'layouts.main']);
   }

   public function cancelSale_list_api(Request $request) {
    //    $type = ($type == 'all') ? ['pending','cancelled','cancelRequest','done'] : '';
       $search = $request->input('search');
       $serach_value = $search['value'];
       $start = $request->input('start');
       $limit = $request->input('length');
       $offset = empty($start) ? 0 : $start ;
       $limit =  empty($limit) ? 10 : $limit ;
       DB::enableQueryLog();
     $api_data= cancelRequest::// ->leftJoin('sale',DB::raw('cancel_request.cancel_type = "sale" and sale.id'),'=','cancel_request.type_id')
                leftJoin('users','users.id','cancel_request.user_id')
                ->leftJoin('sale',function($join){
                    $join->on('cancel_request.type_id','sale.id');
                })
                ->leftJoin('payment as paymentIn',function($join){
                    $join->on(DB::raw('paymentIn.type = "sale" and paymentIn.sale_id'),'=','sale.id')
                        ->where('paymentIn.status','received');
                })
                ->leftJoin('users as user','user.id',DB::raw(Auth::id()))
                ->whereIn('sale.store_id',CustomHelpers::user_store())
                ->where('cancel_request.cancel_type','sale')
           ->select(
               'cancel_request.id',
            //    DB::raw("group_concat(cancel_request.id SEPARATOR ',') as all_cancel_id"),
               'sale.id as saleId',
               'sale.status',
               'sale.sale_no',
               'cancel_request.request_id as req_num',
               'cancel_request.desc as reason',
               'cancel_request.request_date as req_date',
               'cancel_request.status as cancel_status',
               'cancel_request.type as cancel_type',
               'users.name',
                DB::raw('IFNULL(sum(paymentIn.amount),0) as payIn'),
                'user.user_type',
                'user.role',
                DB::raw('IFNULL((select sum(amount) from payment where type = "saleRefund" and type_id = cancel_request.id and status = "received"),0) as payOut')
           )
           -> groupBy('sale.id');

           if(!empty($serach_value))
           {
               $api_data->where(function($query) use ($serach_value){
                   $query->where('cancel_request.request_id','like',"%".$serach_value."%")
                   ->orwhere('sale.sale_no','like',"%".$serach_value."%")
                   ->orwhere('cancel_request.desc','like',"%".$serach_value."%")
                   ->orwhere('users.name','like',"%".$serach_value."%")
                   ->orwhere('paymentIn.amount','like',"%".$serach_value."%")
                   ->orwhere('cancel_request.request_date','like',"%".$serach_value."%")
                   ->orwhere('sale.status','like',"%".$serach_value."%")                    
                   ;
               });
           }
           if(isset($request->input('order')[0]['column']))
           {
               $data = [
               'cancel_request.request_id',
               'sale.sale_no',
               'cancel_request.desc',
               'users.name',
               'cancel_request.request_date',
               'payment.amount',
               'payment.amount',
               'sale.status',
               ];
               $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
               $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
           }
           else
               $api_data->orderBy('cancel_request.request_date','desc');      
       
       $count = count($api_data->get());
       $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
    //    foreach($api_data as $key => $val)
    //    {
    //        $cancel_id = $val['id'];
    //        $refund_amount = Payment::where('type','saleRefund')
    //                         ->where('type_id',$cancel_id)
    //                         ->sum('amount');
    //         $api_data[$key]->payOut =  $refund_amount;  
    //    }
       $array['recordsTotal'] = $count;
       $array['recordsFiltered'] = $count;
       $array['data'] = $api_data; 
       return json_encode($array);
   }
   public function cancelSaleApprove(Request $request) {
        $req_id = $request->input('reqId');
        DB::beginTransaction();
        $cancelSale = cancelRequest::where('id',$req_id)->where('cancel_type','sale')
                        ->where('type','custom')->where('status','pending')->first();
        if(!isset($cancelSale->id))
        {
            return 'Not found any data, for this requested Number.';
        }
        $sale = Sale::where('id',$cancelSale->type_id)->first();
        if(!isset($sale->id)){
            return 'Sale Not Found.';
        }
        // Cancelled All payment Request for this sale
        $this->cancelPaymentRequest('sale',$sale->id);

        $updateCancelSale = cancelRequest::where('id',$req_id)
                        ->where('cancel_type','sale')
                    ->update(['status' => 'approve']);
        $updateSale = Sale::where('id',$cancelSale->type_id)->where('status','cancelRequest')
                        ->update(['status' => 'cancelled']);
        $getProductIdAndStoreId = Sale::where('sale.id',$cancelSale->type_id)
                                    ->leftJoin('sale_order','sale_order.sale_id','sale.id')
                                    ->select('sale.product_id','sale.store_id','sale_order.id as sale_order_id'
                                    ,'sale_order.product_frame_number','sale_order.battery_number')
                                ->first();
        //  recover battery stock 
        if($getProductIdAndStoreId->sale_order_id)
        {
            $updateProductDetails = ProductDetails::where('frame',$getProductIdAndStoreId->product_frame_number)
                                    ->update([
                                        'status'    =>  'ok'
                                    ]);
        }
        
        // recover stock product data
        Stock::where('product_id',$getProductIdAndStoreId->product_id)
                ->where('store_id',$getProductIdAndStoreId->store_id)->increment('quantity',1);
        Stock::where('product_id',$getProductIdAndStoreId->product_id)
                ->where('store_id',$getProductIdAndStoreId->store_id)->decrement('sale_qty',1);

        // update Stock for Vehicle Addon
        // update vehicle addon stock
        $this->updateVehicleAddonStock($getProductIdAndStoreId,$cancelSale->type_id);

        // for battery stock
        if(!empty($getProductIdAndStoreId->battery_number))
        {
            $getBatteryInfo = ProductDetails::where('frame',$getProductIdAndStoreId->battery_number)
                                                ->first();
            if(!isset($getBatteryInfo->id)){
                DB::rollback();
                return 'error';
            }
            $updateProductDetails = ProductDetails::where('id',$getBatteryInfo->id)
                                    ->update([
                                        'status'    =>  'ok'
                                    ]);
            Stock::where('product_id',$getBatteryInfo->product_id)
                ->where('store_id',$getBatteryInfo->store_id)->increment('quantity',1);
            Stock::where('product_id',$getBatteryInfo->product_id)
                ->where('store_id',$getBatteryInfo->store_id)->decrement('sale_qty',1);
        }
        
        if($updateCancelSale && $updateSale)
        {
            /* Add action Log */
            CustomHelpers::userActionLog($action='Cancel Sale Approve',$cancelSale->id,$sale->customer_id);
            DB::commit();
            return 'success';
        }
        DB::rollback();
        return 'error';
   }

    // cancelled payment request if exist
    public function cancelPaymentRequest($type,$type_id){
        $check = PaymentRequest::where('type',$type)
                                    ->where('type_id',$type_id)
                                    ->pluck('id');
        if(isset($check[0])){
            PaymentRequest::whereIn('id',$check)->where('status','Pending')
                                    ->update(['status' => 'Cancel']);
        }
    }    

   //cancel sale refund page
   public function refundMoney($saleId)
   {
        $saleData = Sale::where("sale.id",$saleId)->where('sale.status','cancelled')
                        ->leftJoin('cancel_request',DB::raw('cancel_request.cancel_type = "sale" and sale.id'),'=','cancel_request.type_id')
                        // ->leftJoin('payment as paymentIn','paymentIn.sale_id','sale.id')
                        ->leftJoin('payment as paymentIn',function($join){
                            $join->on(DB::raw('paymentIn.sale_id'),'=','sale.id')
                                ->where('paymentIn.type','sale');
                        })
                        ->select('cancel_request.id as req_id',
                            'cancel_request.request_id as req_num',
                            'sale.balance as sale_total_amount',
                            DB::raw('sum(paymentIn.amount) as paid_amount'),
                            // DB::raw('sum(paymentOut.amount) as refund_amount'),
                            'sale.*')->first(); 
        $paid = Payment::where('type','saleRefund')
                        ->where('type_id',$saleData->req_id)
                        ->get();
        $refunded_amount = Payment::where('type','saleRefund')
                        ->where('type_id',$saleData->req_id)
                        ->sum('amount');
        $refund_amount = Payment::where('type','sale')
                        ->where('sale_id',$saleId)
                        ->where('status','received')
                        ->sum('amount');      
        $pay_mode = DB::table('master')->where('type','payment_mode')->get();

        $data = array(
            'saleData' => $saleData, 
            'paid' => $paid,
            'pay_mode'  =>  $pay_mode,
            'refund_amount' =>  $refund_amount,
            'refunded_amount' =>  $refunded_amount,
            'layout' => 'layouts.main'
        );
        return view('admin.sale.refundPay',$data);

   }
   public function refundMoneyDB(Request $request)
   {
    try {
        $timestamp=date('Y-m-d G:i:s');
        $this->validate($request,[
            'amount'=>'required',
            'payment_mode'=>'required',
            'transaction_number'=>'required',
            'transaction_charges'=>'required',
            'receiver_bank_detail'=>'required_unless:payment_mode,Cash',
        ],[
            'amount.required'=> 'This field is required.', 
            'payment_mode.required'=> 'This field is required.', 
            'transaction_number.required'=> 'This field is required.', 
            'transaction_charges.required'=> 'This field is required.', 
            'receiver_bank_detail.required_unless'=> 'This field is required.', 
        ]);
        DB::beginTransaction();
        // $total_amount = Sale::where('id',$request->input('sale_id'))->get('total_amount')->first();
        $total_amount = Payment::where('sale_id',$request->input('sale_id'))->where('type','sale')
                        ->where('status','received')->sum('amount');
        $paid_amount = Payment::where('type_id',$request->input('requested_id'))
                                ->where('type','saleRefund')->sum('amount');
        if ($total_amount < $paid_amount+intval($request->input('amount'))) {
            return redirect('/admin/cancelSale/refundPage/'.$request->input('sale_id'))->with('error','Your Amount too more .')->withInput();
        }else{
            $paydata = Payment::insertGetId(
            [
                'type' => 'saleRefund',
                'sale_id'   =>  $request->input('sale_id'),
                'type_id' => $request->input('requested_id'),
                'payment_mode' => $request->input('payment_mode'),
                'transaction_number' => $request->input('transaction_number'),
                'transaction_charges' => $request->input('transaction_charges'),
                'receiver_bank_detail' => $request->input('receiver_bank_detail'),
                'amount' => $request->input('amount'),
                'store_id' => $request->input('store_id'),
                'status'    =>  'received',
                'payment_type'  =>  'Refund'
            ]
            );
            // update in sale
            Sale::where('id',$request->input('sale_id'))->update([
                'refunded_amount'   =>  $paid_amount+intval($request->input('amount'))
            ]);
            if($paydata == NULL) {
                DB::rollback();
                   return redirect('/admin/cancelSale/refundPage/'.$request->input('sale_id'))->with('error','some error occurred')->withInput();
            } else{
                /* Add action Log */
                CustomHelpers::userActionLog($action='Refund Money',$request->input('requested_id'));
                if($total_amount == $paid_amount+intval($request->input('amount')))
                {
                    DB::commit();
                    return redirect('/admin/cancel/sale/list')->with('success','Money has been Successfully refunded');
                }
                DB::commit();
                return redirect('/admin/cancelSale/refundPage/'.$request->input('sale_id'))->with('success','Amount Successfully Paid .');
            }

        }
        
    }  catch(\Illuminate\Database\QueryException $ex) {
        DB::rollback();
        return redirect('/admin/cancelSale/refundPage/'.$request->input('sale_id'))->with('error','some error occurred'.$ex->getMessage());
    }
   }
   public function refundRequestMoneyDB(Request $request)
   {
    try {
        $timestamp=date('Y-m-d G:i:s');
        $this->validate($request,[
            'acc_num'=>'required|numeric',
            'acc_name'=>'required',
            'ifsc'=>'required',
        ],[
            'acc_num.required'=> 'This field is required.', 
            'acc_num.numeric'=> 'This field contain only only numeric.', 
            'acc_name.required'=> 'This field is required.', 
            'ifsc.required'=> 'This field is required.', 
        ]);
        // $total_amount = Sale::where('id',$request->input('sale_id'))->get('total_amount')->first();
        $total_amount = Payment::where('sale_id',$request->input('sale_no'))->where('type','sale')
                        ->where('status','received')->sum('amount');
        $paid_amount = Payment::where('type_id',$request->input('requested_id'))
                                ->where('type','saleRefund')->sum('amount');
        if ($total_amount < $paid_amount+intval($request->input('req_money'))) {
            return redirect('/admin/cancelSale/refundPage/'.$request->input('sale_no'))->with('error','Your Requested Amount too more.')->withInput();
        }else{
            $paydata = DB::table('refund_request')->insertGetId(
            [
                'cancel_req_id' => $request->input('requested_id'),
                'req_money' => $request->input('req_money'),
                'account_num' => $request->input('acc_num'),
                'ifsc' => $request->input('ifsc'),
                'acc_name' => $request->input('acc_name')
            ]
            );
            if($paydata == NULL) {
                   return redirect('/admin/cancelSale/refundPage/'.$request->input('sale_no'))->with('error','some error occurred')->withInput();
            } else{
                return redirect('/admin/cancelSale/refundPage/'.$request->input('sale_no'))->with('success','Amount Successfully Requested .');
            }

        }
        
    }  catch(\Illuminate\Database\QueryException $ex) {
        return redirect('/admin/cancelSale/refundPage/'.$request->input('sale_no'))->with('error','some error occurred'.$ex->getMessage());
    }
   }

   public function online_req_pay_list() {
       $pay_mode = DB::table('master')->where('type','payment_mode')->where('key','<>','Cash')->select('key','value')->get();
       $data = [
        'pay_mode'  =>  $pay_mode,   
        'layout' => 'layouts.main'
            ];
    return view('admin.sale.online_pay_req_list',$data);
    }

    public function online_req_pay_api(Request $request) 
    {
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
        DB::enableQueryLog();
        $api_data= DB::table('cancel_request')->Join('refund_request','refund_request.cancel_req_id','cancel_request.id')
            ->leftJoin('users','users.id',DB::raw(Auth::id()))
            ->select(
                'refund_request.id',
                'cancel_request.cancel_type',
                'refund_request.acc_name',
                'refund_request.ifsc',
                'refund_request.account_num',
                'refund_request.req_money',
                'refund_request.pay_mode',
                'refund_request.bank_name',
                'refund_request.trans_charge',
                'refund_request.trans_num',
                'refund_request.paid_date',
                'users.user_type',
                'users.role'
            );

        if(!empty($serach_value))
        {
            $api_data->where(function($query) use ($serach_value){
                $query->where('cancel_request.request_id','like',"%".$serach_value."%")
                ->orwhere('refund_request.acc_name','like',"%".$serach_value."%")
                ->orwhere('refund_request.ifsc','like',"%".$serach_value."%")                    
                ->orwhere('refund_request.req_money','like',"%".$serach_value."%")  
                ->orwhere('refund_request.bank_name','like',"%".$serach_value."%")                    
                ->orwhere('refund_request.pay_mode','like',"%".$serach_value."%")                    
                ->orwhere('refund_request.trans_charge','like',"%".$serach_value."%")                    
                ->orwhere('refund_request.trans_num','like',"%".$serach_value."%")                
                ->orwhere('refund_request.paid_date','like',"%".$serach_value."%")                    
                ;
            });
        }
        if(isset($request->input('order')[0]['column']))
        {
            $data = [
             'cancel_request.cancel_type',
                'refund_request.acc_name',
                'refund_request.ifsc',
                'refund_request.account_num',
                'refund_request.req_money',
                'refund_request.bank_name',
                'refund_request.pay_mode',
                'refund_request.trans_charge',
                'refund_request.trans_num',
                'refund_request.paid_date',
            ];
            $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
            $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
        }
        else
            $api_data->orderBy('cancel_request.request_date','desc');      
    
        $count = $api_data->count();
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }
   public function online_req_pay_db(Request $request)
   {

        $refundId = $request->input('refundId');
        $payment_mode = $request->input('payment_mode');
        $transaction_number = $request->input('transaction_number');
        $transaction_charges = $request->input('transaction_charges');
        $bank_name = $request->input('bank_name');

        Validator::make($request->all(), [
            'payment_mode' => 'required',
            'transaction_number' => 'required',
            'transaction_charges' => 'required|numeric',
            'bank_name' => 'required'
        ],
        [
            'payment_mode.required'    => 'This Feild is required',
            'transaction_number.required'    => 'This Feild is required',
            'transaction_charges.required'    => 'This Feild is required',
            'transaction_charges.numeric'    => 'Please Enter only numeric value',
            'bank_name.required'    => 'This Feild is required',
        ])->validate();
        try{
            DB::beginTransaction();
            $check = RefundRequest::where('id',$refundId)->first();
            if(!isset($check->id)){
                return back()->with('error','Wrong Requested Id.')->withInput();
            }
            if(!empty($check->paid_date)){
                return back()->with('error','You Already Paid It.')->withInput();
            }
            $data = [
                'bank_name' =>  $bank_name,
                'trans_charge'   =>  $transaction_charges,
                'trans_num' =>  $transaction_number,
                'pay_mode'  =>  $payment_mode,
                'paid_date' =>  date('Y-m-d')
            ];
            $update = DB::table('refund_request')->where('id',$refundId)->update($data);
        }
        catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return back()->with('error','Something went wrong.'.$ex->getMessage())->withInput();
        }
        /* Add action Log */
        CustomHelpers::userActionLog($action='Online Payment Request',$refundId);
        DB::commit();
        return back()->with('success','Submitted Successfully');
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

    public function cancelInsuranceList() {
        $data = array(
            'layout' => 'layouts.main'
        );
        return view('admin.sale.cancel_insurance',$data);
    }

    public function cancelInsuranceList_api(Request $request) {
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
        
      $api_data= Insurance::leftJoin('sale','insurance.sale_id','sale.id')
            ->select(
                'insurance.id',
                'insurance.policy_tenure',
                'insurance.insurance_date',
                 DB::raw("(select name from insurance_company where id = insurance.insurance_co) as insurance_co") ,  
                'insurance.insurance_type',
                'insurance.insurance_amount',
                'insurance.policy_number',
                'insurance.type',
                'sale.sale_no',
                'sale.status'
            )->where('sale.status', 'cancelled');
            if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('sale.sale_no','like',"%".$serach_value."%")   
                    ->orwhere(DB::raw("(select name from insurance_company where id = insurance.insurance_co)"),'like',"%".$serach_value."%")                    
                    ->orwhere('insurance.insurance_type','like',"%".$serach_value."%")                    
                    ->orwhere('insurance.insurance_amount','like',"%".$serach_value."%")                    
                    ->orwhere('insurance.policy_number','like',"%".$serach_value."%")                    
                    ->orwhere('insurance.type','like',"%".$serach_value."%")                    
                    ;
                });
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                'insurance.id',
                'insurance.insurance_co',
                'insurance.insurance_type',
                'insurance.insurance_amount',
                'insurance.policy_number',
                'insurance.type'
                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('insurance.id','desc');      
        
        $count = $api_data->count();
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

    // public function rtoPendingList() {
    //      $data = array(
    //         'layout' => 'layouts.main'
    //     );
    //     return view('admin.rto_pending_list',$data);
    // }

    // public function rtoPendingList_api(Request $request,$type) {
    //      if ($type == '1') {
    //         $type = ($type == '1') ? ['1'] : '';
    //     }elseif($type == '0'){
    //         $type = ($type == '0') ? ['0'] : '';
    //     }
    //     $search = $request->input('search');
    //     $serach_value = $search['value'];
    //     $start = $request->input('start');
    //     $limit = $request->input('length');
    //     $offset = empty($start) ? 0 : $start ;
    //     $limit =  empty($limit) ? 10 : $limit ;
        
    //   $api_data= RtoModel::leftJoin('sale','rto.sale_id','sale.id')
    //         ->whereIn('rto.approve',$type)
    //         ->select(
    //             'rto.id',
    //             'rto.rto_finance',
    //             'rto.application_number',
    //             'rto.rto_type',
    //             'rto.rto_amount',
    //             'rto.approve',
    //             'rto.type',
    //             'sale.sale_no'
    //         );
    //         if(!empty($serach_value))
    //         {
    //             $api_data->where(function($query) use ($serach_value){
    //                 $query->where('rto.rto_finance','like',"%".$serach_value."%")
    //                 ->orwhere('rto.application_number','like',"%".$serach_value."%")                    
    //                 ->orwhere('rto.rto_type','like',"%".$serach_value."%")                    
    //                 ->orwhere('rto.rto_amount','like',"%".$serach_value."%")                    
    //                 ->orwhere('rto.approve','like',"%".$serach_value."%")                   
    //                 ->orwhere('rto.type','like',"%".$serach_value."%")                    
    //                 ->orwhere('sale.sale_no','like',"%".$serach_value."%")                    
    //                 ;
    //             });
    //         }
    //         if(isset($request->input('order')[0]['column']))
    //         {
    //             $data = [
    //             'rto.id',
    //             'rto.rto_finance',
    //             'rto.application_number',
    //             'rto.rto_type',
    //             'rto.rto_amount',
    //             'rto.approve',
    //             'rto.type',
    //             'sale.sale_no'               
    //             ];
    //             $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
    //             $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
    //         }
    //         else
    //             $api_data->orderBy('rto.id','desc');      
        
    //     $count = count($api_data->get()->toArray());
    //     $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
    //     $array['recordsTotal'] = $count;
    //     $array['recordsFiltered'] = $count;
    //     $array['data'] = $api_data; 
    //     return json_encode($array);
    // }

    // public function rtoApproval($id) {
    //     try {
    //      $timestamp=date('Y-m-d G:i:s');
    //         $rtodata = RtoModel::where('id',$id)->update(
    //         [
    //             'approve' => 1,
    //             'approval_date' => $timestamp
    //         ]
    //         );
    //         if($rtodata == NULL) {
    //             return 'Error';
    //         } else{
    //             return 'Success';
    //         }
        
    //     }  catch(\Illuminate\Database\QueryException $ex) {
    //         return 'some error occurred';
    //     }
    // }


    // public function rtoList() {
    //     $data = array(
    //         'layout' => 'layouts.main'
    //     );
    //     return view('admin.rto_list',$data);
    // }

    // public function rtoList_api(Request $request) {
      
    //     $search = $request->input('search');
    //     $serach_value = $search['value'];
    //     $start = $request->input('start');
    //     $limit = $request->input('length');
    //     $offset = empty($start) ? 0 : $start ;
    //     $limit =  empty($limit) ? 10 : $limit ;
    //     $api_data= Sale::leftJoin('rto', 'rto.sale_id','sale.id')
    //         ->where('rto.application_number', null)
    //         ->where('sale.status','!=','cancelled')
    //         ->select(
    //             'sale.id',
    //             'sale.sale_no'
    //         );

    //         if(!empty($serach_value))
    //         {
    //             $api_data->where(function($query) use ($serach_value){
    //                 $query->where('sale.sale_no','like',"%".$serach_value."%")                    
    //                 ;
    //             });
    //         }
    //         if(isset($request->input('order')[0]['column']))
    //         {
    //             $data = [
    //             'sale.id',
    //             'sale.sale_no'               
    //             ];
    //             $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
    //             $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
    //         }
    //         else
    //             $api_data->orderBy('sale.id','desc');      
        
    //     $count = count($api_data->get()->toArray());
    //     $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
       
    //     $array['recordsTotal'] = $count;
    //     $array['recordsFiltered'] = $count;
    //     $array['data'] = $api_data; 
    //     return json_encode($array);
    // }



    public function salePayment_list() {
        $data = array(
            'layout' => 'layouts.main'
        );
        return view('admin.sale_payment_list',$data);
    }

    public function salePaymentList_api(Request $request,$type) {
        DB::enableQueryLog();
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;

        $api_data = Sale::leftJoin('product','sale.product_id','product.id')
                        ->leftJoin('customer','customer.id','sale.customer_id')
                    ->select(
                        'sale.id',
                        // 'sale.status',
                        'sale.sale_no',
                        'customer.name',
                        'sale.sale_date',
                        'sale.balance as total_amount',
                        'sale.payment_amount'
                    );

        if ($type == 'complete') {
            $api_data = $api_data->whereRaw('sale.balance = sale.payment_amount');
        }elseif ($type == 'pending') {
            $api_data = $api_data->where('sale.status','<>','cancelled')
                                    ->whereRaw('sale.balance > sale.payment_amount');
        }
    
            if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('sale.sale_no','like',"%".$serach_value."%")
                    ->orwhere('customer.name','like',"%".$serach_value."%")
                    ->orwhere('sale.sale_date','like',"%".$serach_value."%")
                    ->orwhere('sale.balance','like',"%".$serach_value."%")
                    ->orwhere('sale.payment_amount','like',"%".$serach_value."%");
                });
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                'sale.id',
                'customer.name',
                'sale.sale_date',
                'sale.balance',
                'sale.payment_amount'
                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('sale.id','desc');      
        
        $count = $api_data->count();
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
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
         try {
            $timestam = date('Y-m-d G:i:s');
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

    public function paymentConfirmation_list() {
        $admin = Users::where('id',Auth::id())->get()->first();
        if(in_array($admin->role,['Superadmin','Accountant'])){
            $admin_id = $admin->id;
        }else{
            $admin_id = '';
        }
        $data = array(
            'admin' => $admin_id,
            'layout' => 'layouts.main'
        );
         return view('admin.sale.payment_confirmation_list',$data);
    }

    public function paymentConfirmationList_api(Request $request) {
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;

        $type = $request->input('type');
        $auth_store = CustomHelpers::user_store();
        $api_data = Payment::leftJoin('sale', 'payment.sale_id','sale.id')
                    ->whereNotIn('payment.payment_mode',CustomHelpers::pay_mode_received())
                    ->whereIn('payment.store_id',$auth_store)
            ->select(
                'payment.id',
                'payment.type',
                'payment.payment_mode',
                'payment.transaction_number',
                'payment.transaction_charges',
                'payment.receiver_bank_detail',
                'payment.amount',
                'payment.security_amount',
                'payment.status'
        );
        
        if($type == 'pending'){
            $api_data = $api_data->where('payment.status','pending');
        }
        elseif($type == 'received'){
            $api_data = $api_data->where('payment.status','received');
        }
        elseif($type == 'cancelled'){
            $api_data = $api_data->where('payment.status','cancelled');
        }

            if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('payment.type','like',"%".$serach_value."%")                  
                    ->orwhere('payment.id','like',"%".$serach_value."%")                  
                    ->orwhere('payment.payment_mode','like',"%".$serach_value."%")
                    ->orwhere('payment.transaction_number','like',"%".$serach_value."%")
                    ->orwhere('payment.transaction_charges','like',"%".$serach_value."%")
                    ->orwhere('payment.receiver_bank_detail','like',"%".$serach_value."%")
                    ->orwhere('payment.amount','like',"%".$serach_value."%")
                    ->orwhere('payment.status','like',"%".$serach_value."%");
                });
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                    
                    'payment.type',
                    'payment.payment_mode',
                    'payment.transaction_number',
                    'payment.transaction_charges',
                    'payment.receiver_bank_detail',
                    'payment.amount',
                    'payment.status',
                    'payment.id',

                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('payment.id','desc');      
        
        $count = $api_data->count();
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

    public function paymentConfirmation_received($id) {
        try {
             DB::beginTransaction();
            $user = Users::where('id',Auth::id())
                            ->whereIn('role',['Superadmin','Accountant'])
                            ->first();
            if(!isset($user->id)){
                return response()->json([false,'You Are Not Authorized.']);
            }
            
            $getStatus = Payment::where('id',$id)->get()->first();
            
             $checkStore = CustomHelpers::CheckAuthStore($getStatus['store_id']);
            if ($checkStore) {
          
                if($getStatus['status'] == 'pending'){
                $data = Payment::where('id',$id)->update([
                    'status' => 'received'
                ]);

                if ($getStatus['type'] == 'sale') {
                        $saledata = Sale::where('id',$getStatus['sale_id'])->get()->first();
                        $amount = $saledata['payment_amount'] + $getStatus['amount'];
                        $saleUpdate = Sale::where('id',$getStatus['sale_id'])->update([
                            'payment_amount' => $amount
                        ]);
                }

                if ($getStatus['type'] == 'security') {
                        $security = SecurityModel::where('id',$getStatus['type_id'])->get()->first();
                        $amount = $security['total_amount']+$getStatus['amount'];
                        $securityUpdate = SecurityModel::where('id',$getStatus['type_id'])->update([
                            'total_amount' => $amount
                        ]);
                        if ($securityUpdate == NULL) {
                            DB::rollback();
                            return response()->json([false,'Something Went Wrong.']);
                        }
                }

                    if ($getStatus['type'] == 'booking') {
                    $bookdata = Payment::where('booking_id',$getStatus['booking_id'])->where('status','pending')->get();
                    if (count($bookdata) == 0) {
                        $booking = BookingModel::where('id',$getStatus['booking_id'])->get()->first();
                        if ($booking != NULL ) {
                            $calldate = date('Y-m-d');
                            $type = 'booking';
                            $calltype = 'thankyou';
                            $storeId = $booking['store_id'];
                            $next_call_date = date('Y-m-d', strtotime('+2 day', strtotime($calldate)));
                            $redirect = CustomHelpers::CreateCalling($type,$calltype,$storeId,$getStatus['booking_id'],$next_call_date);
                        
                        }
                        
                    }
                    }
                    if($data == NULL) {
                        DB::rollback();
                        return [false,'Something Went Wrong.'];
                    } else{
                        /* Add action Log */
                            CustomHelpers::userActionLog($action='Payment Confirmation Received',$id);
                            DB::commit();
                        return [true,'Successfully Received.'];
                    }
                }else{
                    return response()->json([false,'Something Went Wrong.']);
                }
            }else{
                return [false,'This Request Not In Your Store.'];
           }
           
           }  catch(\Illuminate\Database\QueryException $ex) {
               return response()->json([false,'Something Went Wrong '.$ex->getMessage()]);
           }
    }

    public function paymentConfirmation_cancel($id) {
        try {
            $user = Users::where('id',Auth::id())
                            ->whereIn('role',['Superadmin','Accountant'])
                            ->first();
            if(!isset($user->id)){
                return response()->json([false,'You Are Not Authorized.']);
            }
            $getStatus = Payment::where('id',$id)->get()->first();
            $checkStore = CustomHelpers::CheckAuthStore($getStatus['store_id']);
            if ($checkStore) {
                if($getStatus->status == 'pending'){
                   $data = Payment::where('id',$id)->update(
                   [
                       'status' => 'cancelled'
                   ]
                   );
                   if($data == NULL) {
                       return response()->json([false,'Something Went Wrong.']);
                   } else{
                       /* Add action Log */
                       CustomHelpers::userActionLog($action='Payment Confirmation Rejected',$id);
                       return response()->json([true,'Payment Successfully Cancelled.']);
                   }
                }else{
                    return response()->json([false,'Something Went Wrong.']);
                }
            }else{
                return response()->json([false,'You Are Not Authorized.']);
           }
           
        }catch(\Illuminate\Database\QueryException $ex) {
               return response()->json([false,'Someting Went Wrong.']);
        }
    }



    // sale update
    public function saleUpdate(Request $request, $id){
        // echo "Under Construction";die;
        $salePay = Payment::where('type','sale')->where('sale_id',$id)->sum('amount');
        if($salePay > 0)
        {
            return redirect('/admin/sale/list')->with('error','This Sale will not be editable, because this sale already associate with payment.');
        }
        $check_approval = ApprovalRequest::where('type','sale')->where('type_id',$id)->where('status','Approved')->first();
        if(isset($check_approval->id)){
            return back()->with('error','Sale will not be Update After Approval of Any Request.');
        }
       
        $sale = Sale::where('sale.id',$id)
                ->leftJoin('sale_finance_info',function($join){
                    $join->on('sale_finance_info.sale_id','=','sale.id')
                        ->whereNull('sale_finance_info.deleted_at');
                })
                // ->leftJoin('sale_accessories','sale_accessories.sale_id','sale.id')     
                ->leftJoin('product','product.id','sale.product_id')
                ->leftJoin('amc',function($join){
                    $join->on('amc.sale_id','=','sale.id')
                        ->whereNull('amc.deleted_at');
                })
                ->leftJoin('hjc',function($join){
                    $join->on('hjc.sale_id','=','sale.id')
                        ->whereNull('hjc.deleted_at');
                })
                ->leftJoin('extended_warranty',function($join){
                    $join->on('extended_warranty.sale_id','=','sale.id')
                            ->whereNull('extended_warranty.deleted_at');
                })
                ->leftJoin('sale_rto','sale_rto.sale_id','sale.id')
                ->leftJoin('best_deal_sale',function($join){
                    $join->on('best_deal_sale.purchase_sale_id','sale.id')
                            ->whereIn('best_deal_sale.tos',['exchange','best_deal']);
                })
                ->select('sale.*',
                    'sale_finance_info.finance_executive_id as finance_name','sale_finance_info.company as finance_company_name',
                    'sale_finance_info.loan_amount as loan_amount','sale_finance_info.do','sale_finance_info.dp',
                    'sale_finance_info.los','sale_finance_info.roi','sale_finance_info.payout','sale_finance_info.pf'
                    ,'sale_finance_info.emi','sale_finance_info.mac as monthAddCharge','sale_finance_info.sd',
                    'sale_finance_info.company as self_finance_company','sale_finance_info.loan_amount as self_finance_amount',
                    'product.basic_price','product.model_name','product.model_variant','product.color_code',
                    'amc.amount as amc_cost',
                    'hjc.amount as hjc_cost',
                    'extended_warranty.amount as ew_cost',
                    DB::raw('TIMESTAMPDIFF(YEAR,extended_warranty.start_date,extended_warranty.end_date) as ew_duration'),
                    'sale_rto.rto','sale_rto.handling_charge','sale_rto.affidavit_cost','sale_rto.rto_type',
                    'sale_rto.fancy_no_receipt as fancy_no','sale_rto.fancy_date',
                    'best_deal_sale.tos as type_of_sale','sale.exchange_model as exBest_model','best_deal_sale.yom as exBest_yom',
                    'best_deal_sale.register_no as exBest_register_no','best_deal_sale.value as exBest_value'
                )   
                ->first(); 
        // get insurance
        $insurance = Insurance::where('sale_id',$id)->where('type','new')->get();
        if(!isset($insurance[0])){
            return back()->with('error','Something went wrong, Insurance Data Not Found.');
        }
        // get discount
        $arr = ['Normal','Scheme'];
        $discount = SaleDiscount::where('type','sale')->where('type_id',$id)->whereIn('discount_type',$arr)->first();
        $csd = SaleDiscount::where('type','sale')->where('type_id',$id)->where('discount_type','CSD')->first();

        $queryForAccessories = OtcSale::where('otc_sale.sale_id',$id)
                        ->where('otc_sale.with_sale',1)
                        ->leftJoin('otc_sale_detail','otc_sale_detail.otc_sale_id','otc_sale.id')
                        ->leftJoin('part','part.id','otc_sale_detail.part_id')
                        ->where('otc_sale_detail.type','Part')
                        ->where('otc_sale_detail.part_id','>',0)
                        ->select(
                            'otc_sale_detail.part_id',
                            'part.part_number',
                            'otc_sale_detail.qty','otc_sale_detail.amount'
                        )
                        ->orderBy('part_id','ASC');
        $all_accessories_ids =  $queryForAccessories->pluck('part_id')->toArray();   
        $allOtcAccessories = $queryForAccessories->get()->toArray();

        $qForAccessories = MasterAccessories::Join('part',function($join){
                                $join->on(DB::raw("FIND_IN_SET(part.id,master_accessories.part_id)"),">",DB::raw('0'));
                            })
                            ->where('model_name',$sale->model_name)
                        ->whereIn('part.id',$all_accessories_ids)
                        ->whereRaw('master_accessories.part_id <> ""')
                        ->whereNotNull('master_accessories.part_id')
                        ->select(
                            'master_accessories.id as nid',
                            'part.id as part_no',
                            'part.part_number'
                        )
                        ->orderBy('master_accessories.id','ASC');
        
        $accessories = $qForAccessories->pluck('nid');
        $accessories_data = $qForAccessories->get()->toArray();
        $other_accessories = []; $temp_acc = [];

        foreach($accessories_data as $q => $w){
            $pos = array_search($w['part_no'],$all_accessories_ids);
            $accessories_data[$q]['qty'] = $allOtcAccessories[$pos]['qty'];
            $accessories_data[$q]['amount'] = $allOtcAccessories[$pos]['amount'];
            array_push($temp_acc,$w['part_no']);
        }
        $j = 0;
        foreach($allOtcAccessories as $i => $v){
            if(!in_array($v['part_id'],$temp_acc)){
                $other_accessories[$j]['part_no'] = $allOtcAccessories[$i]['part_number'];
                $other_accessories[$j]['qty'] = $allOtcAccessories[$i]['qty'];
                $other_accessories[$j]['amount'] = $allOtcAccessories[$i]['amount'];
                $j++;
            }
        }
        // print_r($allOtcAccessories);die;
       
        $customer = Customers::all();
   
        $state = State::all();
        $scheme = Scheme::all();
        $store = Store::whereIn('id',CustomHelpers::user_store())->get();
        
        $model_name = ProductModel::where('type','product')
                    ->where('isforsale',1)->orderBy('model_name','ASC')->groupBy('model_name')->get(['model_name']);
        $company_name = FinanceCompany::orderBy('id','ASC')->get();

        $sale_user = Users::where('role','SaleExecutive')->get(['id','name']);
        $insur_company = InsuranceCompany::orderBy('id')->get();
        $cpa_company = InsuranceCompany::where('cpa',1)->orderBy('id')->get();
        $maker = Master::where('type','prod_maker')->orderBy('order_by')->get();
        $approval_req = ApprovalRequest::where('type','sale')->where('type_id',$id)->first();

        $data = array(
            'layout' => 'layouts.main',
            'insurance' =>  $insurance,
            'csd_discount'   =>  CustomHelpers::csd_amount(),
            'customer' => ($customer->toArray()) ? $customer->toArray() : array() ,
            // 'product' => ($product->toArray()) ? $product->toArray() : array() ,
            'state' =>  ($state->toArray()) ? $state->toArray()  :  array() ,
            'scheme'    =>  $scheme,
            'insur_company' =>  $insur_company,
            'cpa_company' =>  $cpa_company,
            'discount'  =>  $discount,
            'csd'   =>  $csd,
            'maker' =>  $maker,
            'model_name'    =>  $model_name,
            'company_name'  =>  $company_name,
            'sale'    =>  $sale->toArray(),
            'accessories'   =>  $accessories->toArray(),
            'accessories_data'  =>  $accessories_data,
            'other_accessories' =>  $other_accessories,
            'store'    =>  $store->toArray(),
            'sale_user' =>  ($sale_user->toArray()) ? $sale_user->toArray() : array(),
            'approval_req'  =>  $approval_req
        );
        return view('admin.sale.saleUpdate',$data);
    }
    public function saleUpdateDB(Request $request,$saleId)
    {
        DB::beginTransaction();
        $csd_am = CustomHelpers::csd_amount();
        // check any approval for any request
        $check_approval = ApprovalRequest::where('type','sale')->where('type_id',$saleId)->where('status','Approved')->first();
        if(isset($check_approval->id)){
            return back()->with('error','Sale will not be Update After Approval of Any Request.')->withInput();
        }

        if($request->input('customer_pay_type') == 'finance')
        {
            $find_mobile = FinancierExecutive::where('id',$request->input('finance_name'))
                                                ->whereRaw("FIND_IN_SET(".$request->input('mobile').",mobile_numbers)")
                                                ->first();
            if(isset($find_mobile->id))
            {
                return back()->with('error','Error, Financier Mobile Number and Customer Mobile Number should not be Same.')->withInput();
            }
        }

        $oldSaleData = Sale::where('id',$saleId)->where('status','pending')->first();
        if(!isset($oldSaleData->id)){
            return back()->with('error','Sale Not Found.')->withInput();
        }
        $changeProdStore = 0; // product change flag value
        // check product id will change or not
        if($oldSaleData->product_id != $request->input('prod_name') || $oldSaleData->store_id != $request->input('store_name')){
            // check new product stock
            $changeProdStore = 1;
            $checkProduct = Stock::where('product_id',$request->input('prod_name'))
                                    ->where('store_id',$request->input('store_name'))
                                    ->first();
            if(!isset($checkProduct->id)){
                return back()->with('error','Stock Not Available for Product.')->withInput();
            }else{
                if($checkProduct->quantity <= 0){
                    return back()->with('error','Stock Qty Not Available for Product.')->withInput();
                }
            }
        }

        $product_data = ProductModel::where('id',$request->input('prod_name'))->first();
        if(!isset($product_data->basic_price)){
            return back()->with('error','Product Data Not Found.')->withInput();
        }
        $ExShowroomPrice = round(($product_data->basic_price*28/100)+$product_data->basic_price);
        
        //accessories
        $total_accessories = 0;
        $total_accessories_qty = 0;

        $count_accessories = ($request->input('accessories'))? count($request->input('accessories')) : 0 ;
        $count_other_acc = ($request->input('other_part_no'))? count($request->input('other_part_no')) : 0 ;
        $accessory = $request->input('accessories');
        $validateZero = [];
        $validateZeroMsg = [];
        $other_part_data = [];  //for the others accessories
        $part_data = [];  //for the others accessories
        for($j = 0 ; $j < $count_other_acc ; $j++){
            $total_accessories_qty  += intval($request->input('other_part_qty')[$j]) ;
            $total_accessories   +=  floatval($request->input('other_part_amount')[$j]);
            // check part # exist in part table ? validate it.
            $checkPartNo = Part::where('part_number',$request->input('other_part_no')[$j])->first();
            if(!isset($checkPartNo->id)){
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
                return back()->with('error',$request->input('show_accessories_partNo_'.$accessory[$i]).' Not Found that Part #.')->withInput();
            }
            $part_data[$i]['part_id'] = $request->input('accessories_partNo_'.$accessory[$i]);
            $part_data[$i]['amount'] = floatval($request->input('accessories_amount_'.$accessory[$i]));
            $part_data[$i]['qty'] = intVal($request->input('accessories_qty_'.$accessory[$i]));
            $part_data[$i]['part_desc'] = null;

            $total_accessories_qty  +=  intVal($request->input('accessories_qty_'.$accessory[$i]));
            $total_accessories   +=  floatval($request->input('accessories_amount_'.$accessory[$i]));
            $validateZero['accessories_amount_'.$accessory[$i]] =   'required|gt:0'; 
            $validateZero['accessories_qty_'.$accessory[$i]] =   'required|gt:0';
            $validateZeroMsg['accessories_amount_'.$accessory[$i].'.gt'] =   "'".$request->input('accessories_name_'.$accessory[$i])."' amount should be greater than 0"; 
            $validateZeroMsg['accessories_qty_'.$accessory[$i].'.gt'] =   " '".$request->input('accessories_name_'.$accessory[$i])."' Quantity should be greater than 0";
        }
        $all_accessory = array_merge($part_data,$other_part_data);
        //discount
        // $exch_best_val = ($request->input('tos') != 'new' || $request->input('tos') != 'best_deal') ?floatval($request->input('exchange_value')) : 0.0 ;
        $exch_best_val = ($request->input('tos') != 'new') ?floatval($request->input('exchange_value')) : 0.0 ;
        // if sale is bestdeal check amount should not be changed
        if($request->input('tos') == 'best_deal'){
            $old_price = BestDealSale::where('purchase_sale_id',$saleId)->where('status','Pending')->where('tos','best_deal')->first();
            if(isset($old_price->value)){
                if($old_price->value != $request->input('exchange_value')){
                    return back()->with('error','BestDeal Amount Should Not Be Changed.')->withInput();
                }
                if($old_price->approve == 1){
                    return back()->with('error','BestDeal Approved, so it can not be changed.')->withInput();
                }
                if($old_price->approve == 2){
                    return back()->with('error','BestDeal Rejected, so it can not be changed.')->withInput();
                }
            }else{
                return back()->with('error','BestDeal Not Found.')->withInput();
            }
        }

        $do = ($request->input('customer_pay_type') == 'finance') ?floatval($request->input('do')) : 0.0 ;
        $discount = floatval($request->input('discount_amount')) ;

        if($request->input('discount') == 'scheme'){
            $scheme_id = $request->input('scheme');
            $get_scheme = Scheme::where('id',$scheme_id)->first();
            if(!isset($get_scheme->id)){
                return back()->with('error','Scheme Master Not Found.')->withInput();
            }
            $discount = $get_scheme->amount;
        }

        $total_discount = $exch_best_val+$do+$discount;

        //total selected amount calculate
        $csd = 0;
        if($request->input('csd_check') && $request->input('sale_type') == 'corporate')
        {
            $csd = floatval($ExShowroomPrice * $csd_am/100);      // in csd amount
            $total_discount = round($total_discount + $csd);
        }
        $total_amount = $ExShowroomPrice+(floatval($request->input('reg_fee_road_tax')))
                            +(($request->input('s_insurance') == 'zero_dep') ? floatval($request->input('zero_dep_cost')) : 0.0 )
                            +(($request->input('s_insurance') == 'comprehensive') ? floatval($request->input('comprehensive_cost')) : 0.0 )
                            +(($request->input('s_insurance') == 'ltp_5_yr') ? floatval($request->input('ltp_5_yr_cost')) : 0)
                            +(($request->input('s_insurance') == 'ltp_zero_dep') ? floatval($request->input('ltp_zero_dep_cost')) : 0.0 )
                            +((!empty($request->input('cpa_cover'))) ? floatval($request->input('cpa_cover_cost')) : 0.0 )
                            +($total_accessories)
                            +(($request->input('customer_pay_type') != 'cash') ? floatval($request->input('hypo_cost')) : 0.0 )
                            +((!empty($request->input('amc'))) ? floatval($request->input('amc_cost')) : 0.0 )
                            +((!empty($request->input('ew'))) ? floatval($request->input('ew_cost')) : 0.0 )
                            +((!empty($request->input('hjc'))) ? floatval($request->input('hjc_cost')) : 0.0 );
        
        // sale rto handling charges and affidavit charges add in total amount

        $handling_charge =  ($request->input('rto') == 'normal')?$request->input('handling_charge'): 0;
        $affidavit_cost    =  ($request->input('rto') == 'normal' && $request->input('affidavit') == 'affidavit')?$request->input('affidavit_cost') : 0;
        
        $total_amount =  $total_amount+$handling_charge+$affidavit_cost;

        //  add validation :- accessories mandatory when connection not selected
        $acc_flag = 0;
        if($request->input('acc_filter') != 'connection'){
            // $validateZero['total_accessories_amount']   =  'gt';
            $acc_flag = 1;
            $validateZeroMsg['total_accessories_amount.gt'] =   'Accessories Field is required';
        }
        
        if($changeProdStore == 1){
            // validate vehicle addon stock
            $addon = VehicleAddon::pluck('key_name');
            if(!isset($addon[0])){
                DB::rollback();
                return back('error','Vehicle Addon Stock Master Not Found.')->withInput();
            }
            $old_addon_data = [];  // for old addon stock
            $oldProductData = ProductModel::where('id',$oldSaleData->product_id)
                                                ->first();
            if(!isset($oldProductData->id)){
                return back()->with('error','Product Data Not Found.')->withInput();
            }
            $old_pending_addon_stock = SalePendingAddon::where('sale_id',$saleId)
                                        ->where('type','addon')->where('stock_status',0)
                                        ->pluck('type_name')->toArray();
            // delete old pending addon 
            $del_old_pending_addon_stock = SalePendingAddon::where('sale_id',$saleId)
                                        ->where('type','addon')->where('stock_status',0)
                                        ->delete();
            // for new addon stock
            $addon_data = [];
            $any_addon_pending = 0;
            foreach($addon as $k => $v){
                $flag = 1;
                if(!in_array($v,$old_pending_addon_stock)){
                    $old_addon_data[$v] = 1;
                }
                if($v == 'saree_guard'){
                    if($product_data->model_category == 'SC'){
                        $flag = 0;
                    }
                    if($oldProductData->model_category == 'SC' && !in_array($v,$old_pending_addon_stock)){
                        $old_addon_data[$v] = 0;
                    }
                }
                if($flag == 1){
                    $check = VehicleAddonStock::where('vehicle_addon_key',$v)
                                                    ->whereRaw('qty >= 1')
                                                    ->where('store_id',$request->input('store_name'))->first();
                    $in_data['type']   =  'addon';
                    $in_data['type_name']   =  $v;
                    if(isset($check->id)){
                        $in_data['stock_status']   =  1;
                        $addon_data[$v] = $in_data;
                    }else{
                        $in_data['stock_status']   =  0;
                        $addon_data[$v] = $in_data;
                        $any_addon_pending = 1;
                        // DB::rollback();
                        // return back()->with('error','Stock Not Available for '.$v.' Addon.')->withInput();
                    }
                }
            }
        }
        // for insurance , if customer pay type is self_finance then insurance price may be zero or greater.
        $gte_cost = 'gt';
        $self_insurance = 0;
        if($request->input('insur_type') == 'self'){
            $gte_cost = 'gte';
            $self_insurance = 1;
        }

        $this->validate($request,array_merge($validateZero,[     
            // 'sale_no'=>'required',
            'sale_date'=>'required|date',
            'model_name'=>'required',
            'model_variant'=>'required',
            'prod_name'=>'required',
            'store_name'=>'required',
            'sale_type'=>'required',
            'care_of'=>'required_if:sale_type,corporate',
            'gst'=>'required_if:sale_type,corporate',
            'select_customer_name'=>'required',
            'relation_type'=>'required',
            'relation'=>'required',
            'aadhar'=>'required_without_all:voter',
            'voter'=>'required_without_all:aadhar',
            'address'=>'required',
            'state'=>'required',
            'city'=>'required',
            'pin'=>'required',
            'mobile'=>'required',
            'sale_executive'=>'required',
            'tos'=>'required',
            'exchange_model'=>'required_unless:tos,new',
            'exchange_yom'=>'required_unless:tos,new',
            'exchange_register'=>'required_unless:tos,new',
            'exchange_value'=>'required_unless:tos,new'.(($request->input('tos') != 'new')? '|gt:0' : '' ),
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
            'ex_showroom_price'=>'required|in:'.$ExShowroomPrice,
            'rto'   =>  'required',
            'handling_charge'   =>  'required_if:rto,normal'.(($request->input('rto') == 'normal')? '|gt:0' : '' ).'',
            'affidavit_cost'    =>  'required_if:affidavit,affidavit'.(($request->input('affidavit') == 'affidavit')? '|gt:0' : '' ).'',
            'permanent_temp'    =>  'required',
            'reg_fee_road_tax'=>'required|gt:0',
            'fancy_no'  =>  'required_if:fancy,Yes',
            'fancy_date'  =>  'required_if:fancy,Yes',
            'insur_c'   =>  'required',
            'zero_dep_cost'=>'required_if:s_insurance,zero_dep'.(($request->input('s_insurance') == 'zero_dep')? '|'.$gte_cost.':0' : '' ).'',
            'comprehensive_cost'=>'required_if:s_insurance,comprehensive'.(($request->input('s_insurance') == 'comprehensive')? '|'.$gte_cost.':0' : '' ).'',
            'ltp_5_yr_cost'=>'required_if:s_insurance,ltp_5_yr'.(($request->input('s_insurance') == 'ltp_5_yr')? '|'.$gte_cost.':0' : '' ).'',
            'ltp_zero_dep_cost'=>'required_if:s_insurance,ltp_zero_dep'.(($request->input('s_insurance') == 'ltp_zero_dep')? '|'.$gte_cost.':0' : '' ).'',
            'cpa_company'=>'required_if:cpa_cover,cpa_cover',
            'cpa_duration'=>'required_if:cpa_cover,cpa_cover'.(!empty($request->input('cpa_cover'))? '|integer' : '' ).'',
            'cpa_cover_cost'=>'required_if:cpa_cover,cpa_cover'.(!empty($request->input('cpa_cover'))? '|gt:0' : '' ).'',
            'total_accessories_amount'=>'required'.(($acc_flag == 1) ? '|gt:1' : ''),
            'accessories_cost'=>'required|in:'.$total_accessories,
            'hypo_cost'=>'required_unless:customer_pay_type,cash'.(($request->input('customer_pay_type') != 'cash')? '|gt:0' : '' ).'',
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
            'sale_date.required'    =>  'Sale Date Field is required',
            'sale_date.date'    =>  'Sale Date Field is only date format',
            'model_name.required'    => 'Model Name Field is required' ,
            'model_variant.required'    => 'Model Variant Field is required' ,
            'prod_name.required'    => 'Product Name Field is required' ,
            'store_name.required'   =>  'Store Name Field is required',
            'sale_type.required'   =>  'Sale Type Field is required',
            'care_of.required_if'=>'Care Of Field is required',
            'gst.required_if'=>'GST Field is required',
            'select_customer.required'  =>  'This Field is required',
            'enter_customer_name.required_unless'   =>  'Customer Name Field is required',
            'select_customer_name.required_if'  =>  'Customer Name Field is required',
            'pre_booking.required_if'   =>  'Pre Booking Field is required',
            'relation_type.required' =>'Relation Type Field is required',
            'relation.required' =>  'Relation Name Field is required',
            'aadhar.required_without_all'=> 'Aadhar Field is required, When You Not Fill Voter Number.',
            'voter.required_without_all'=> 'Voter Field is required, When You Not Fill Aadhar Number.',
            'address.required'   =>  'Address Field is required',
            'state.required'    =>  'State Field is required',
            'city.required' =>  'City Field is required',
            'pin.required' =>  'Pin Code Field is required',
            // 'email.required'    =>  'This Field is required',
            'mobile.required'   =>  'Mobile # Field is required',
            'sale_executive.required' =>  'Sale Executive Field is required'   ,
            'tos.required'  =>  'Type Of Sale Field is required',
            'exchange_model.required_unless' =>  'Model Name Field is required',
            'exchange_yom.required_unless'  =>  'Year Of Manufacturing Field is required',
            'exchange_register.required_unless'  =>  'Register # Field is required',
            'exchange_value.required_unless' =>  'This Field is required',
            'exchange_value.gt'    =>  'This Field must be greater than 0.',
            'customer_pay_type.required'    =>  'This Field is required',
            'finance_name.required_if' =>  'Finance Name Field is required',
            'finance_company_name.required_if'  =>  'Finance Company Name  Field is required',
            'loan_amount.required_if'  =>  'Loan Amount Field is required',
            'loan_amount.gt'    =>  'Loan Amount Field must be greater than 0.',
            'dp.gt'    =>  'Down Payment Field must be greater than 0.',
            'emi.gt'    =>  'EMI Field must be greater than 0.',
            'do.required_if'    =>  'DO Amount Field is required',
            'dp.required_if' =>  'Down Payment Field is required',
            // 'los.required_if'   =>  'This Field is required',
            'roi.required_if'  =>  'ROI Field is required',
            'payout.required_if'    =>  'Payout Field is required',
            'pf.required_if'   =>  'PF Field is required',
            'emi.required_if'   =>  'EMI Field is required',
            'monthAddCharge.required_if'    =>  'This Field is required',    
            'sd.required_if'   =>  'This Field is required',
            'self_finance_company.required_if'  =>  'Company Name Field is required',
            'self_finance_amount.required_if'    =>  'Loan Amount Field is required',
            'self_finance_amount.gt'    =>  'Loan Amount Field must be greater than 0.',
            // 'self_finance_bank.required_if' =>  'This Field is required',
            'ex_showroom_price.required' =>  'Ex-Showroom Price Field is required',
            'ex_showroom_price.in' =>  'Ex-Showroom Price is equal to basic price + 28% GST',
            'rto'   =>  'RTO Field is required',
            'hadling_charge.required' =>  'Handling Charge Field is required',
            'hadling_charge.gt' =>  'Handling Charge Field must be greater than 0',
            'affidavit.required' =>  'Affidavit Cost Field is required',
            'affidavit.gt' =>  'Affidavit Cost Field must be greater than 0',
            'permanent_temp.required' =>  'RTO Type Field is required',

            'reg_fee_road_tax.required' =>  'Registration Fee Field is required',
            'reg_fee_road_tax.gt' =>  'Registration Fee Field must be greater than 0',
            'fancy_no.required_if'    =>  'Fancy # Field is required.',
            'fancy_date.required_if'    =>  'Fancy Date Field is required.',
            'insur_c.required'    =>  'Insurance Company Field is required',
            'zero_dep_cost.required_if'    =>  'This Field is required',
            'zero_dep_cost.gt'    =>  'This Field must be greater than 0',
            'zero_dep_cost.gte'    =>  'This Field must be greater equal to 0',
            'comprehensive_cost.required_if'    =>  'This Field is required',
            'comprehensive_cost.gt'    =>  'This Field must be greater than 0',
            'comprehensive_cost.gte'    =>  'This Field must be greater equal to 0',
            'ltp_5_yr_cost.required_if'    =>  'This Field is required',
            'ltp_5_yr_cost.gt'    =>  'This Field must be greater than 0',
            'ltp_5_yr_cost.gte'    =>  'This Field must be greater equal to 0',
            'ltp_zero_dep_cost.required_if' =>  'This Field is required',
            'ltp_zero_dep_cost.gt'    =>  'This Field must be greater than 0',
            'ltp_zero_dep_cost.gte'    =>  'This Field must be greater equal to 0',
            'cpa_company.required_if'    =>  'This Field is required',
            'cpa_duration.required_if'  =>  'This Field is required',
            'cpa_cover_cost.required_if' =>  'This Field is required',
            'cpa_cover_cost.gt'    =>  'This Field must be greater than 0',
            'total_accessories_amount.required' =>  'This Field is required',
            // 'total_accessories_amount.gt' =>  'Accessories Field is required',
            'accessories_cost.required' =>  'This Field is required',
            'accessories_cost.in' =>  'This Field is equal to total Accessories which you selected',
            'hypo_cost.required_unless' =>  'Hypothecation Field is required',
            'amc_cost.required_unless'   =>  'AMC Cost Field is required',
            'amc_cost.gt'    =>  'AMC Cost Field must be greater than 0',
            'ew_duration.required_if'   =>  'EW Duration Field is required',
            'ew_duration.gt'    =>  ' EW Duration must be greater than 0',
            'ew_cost.required_if'   =>  'EW Cost Field is required',
            'ew_cost.gt'    =>  'EW Cost Field must be greater than 0',
            'hjc_cost.required_if'   =>  'HJC Cost Field is required',
            'hjc_cost.gt'    =>  'HJC Cost Field must be greater than 0',
            'discount.required' =>  'Discount Field is required',
            'discount_amount.required'   =>  'Discount Field is required',
            'scheme.required_if'   =>  'Scheme Field is required',
            'scheme_remark.required_if'    =>  'Remark Field is required',
            'total_calculation.required' =>  'This Field is required',
            'total_calculation.in' =>  'Total amount will be equal to whole amount which you have entered',
            'balance'   =>  'This Feild is required',
            'balance.lte'   =>  'Balance Amount must be less to Total Amount'

        ]));
        try{
            
            $sale_date = $request->input('sale_date');
            $customerFlag = $request->input('select_customer');

            $customerId = intval($request->input('select_customer_name'));
            $saleCust = Sale::where('customer_id',$customerId)->first();
            $cust = Customers::where('id',$customerId)->first();
            $customer_name = $cust->name;
                
                $data  = [
                    'mobile'=>$request->input('mobile'),
                    'email_id'=>$request->input('email'),
                    'relation_type'=>$request->input('relation_type'),
                    'relation'=>CustomHelpers::capitalize($request->input('relation')),
                    'country'=>$request->input('country'),
                    'state'=>$request->input('state'),
                    'city'=>$request->input('city'),
                    'location'=>$request->input('location'),
                    'address'=>CustomHelpers::capitalize($request->input('address'),'sentence'),
                    'pin_code'=>$request->input('pin'),
                ];
                if($request->input('sale_type') == 'corporate')
                {
                    $data = array_merge($data,[
                            'care_of'=>$request->input('care_of'),
                            'gst'=>$request->input('gst')
                        ]);
                }
                $checkCustomer = CustomHelpers::checkCustomer($customerId,$request->input('aadhar'),$request->input('voter'));
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
                        'relation' =>  CustomHelpers::capitalize($cust->relation),
                        'email_id'=>$cust->email_id,
                        'mobile'=>$cust->mobile,
                        'dob'   =>  $cust->dob,
                        'country'=>$cust->country,
                        'state'=>$cust->state,
                        'city'=>$cust->city,
                        'location'=>$cust->location,
                        'address'=>CustomHelpers::capitalize($cust->address,'sentence'),
                        'pin_code'=>$cust->pin_code,
                    ]);
                }
                // if($customer==NULL) 
                // {
                //     DB::rollback();
                //     return back()->with('error','Some Unexpected Error occurred.')->withInput();
                // }

            $getOldProdStoreId = $oldSaleData;
            if($changeProdStore == 1 )
            {
                //update old store Quantity
                $updateOldStock_dec = Stock::where('product_id',$getOldProdStoreId->product_id)
                    ->where('store_id',$getOldProdStoreId->store_id)
                    ->decrement('sale_qty',1);
                $updateOldStock_inc = Stock::where('product_id',$getOldProdStoreId->product_id)
                    ->where('store_id',$getOldProdStoreId->store_id)
                    ->increment('quantity',1);
                //update new store Quantity
                $updateNewStock_dec = Stock::where('product_id',$request->input('prod_name'))
                    ->where('store_id',$request->input('store_name'))
                    ->decrement('quantity',1);
                $updateNewStock_inc = Stock::where('product_id',$request->input('prod_name'))
                    ->where('store_id',$request->input('store_name'))
                    ->increment('sale_qty',1);
                
                if(!$updateNewStock_dec || !$updateNewStock_inc || !$updateOldStock_dec || !$updateOldStock_inc)
                {
                    DB::rollback();
                    return back()->with('error','Something Went Wrong.');
                }
            }

            $balance = round($request->input('balance'));
            if($request->input('discount') == 'normal' && $request->input('discount_amount') >= 1000)
            {
                $balance = round($balance+$request->input('discount_amount'));
            }
            if($request->input('tos') == 'best_deal'){
                $balance = $balance+$request->input('exchange_value');
            }
            $type_of_sale = 1;
            if($request->input('tos') == 'exchange'){
                $type_of_sale = 2;
            }elseif($request->input('tos') == 'best_deal'){
                $type_of_sale = 3;
            }

            $saleUpdateData = [
                        'sale_executive'    =>  $request->input('sale_executive'),
                        'product_id'  =>  $request->input('prod_name'),
                        'store_id'  =>  $request->input('store_name'),
                        'sale_type'    =>  $request->input('sale_type'),
                        'ref_name'  =>  CustomHelpers::capitalize($request->input('reference'),'name'),
                        'ref_relation'  =>  CustomHelpers::capitalize($request->input('ref_relation'),'name'),
                        'ref_mobile'  =>  $request->input('ref_mobile'),
                        'customer_id'  =>  $customerId,
                        'customer_pay_type'  =>  $request->input('customer_pay_type'),
                        'tos'  =>  'new',
                        'type_of_sale'  =>  $type_of_sale,
                        'exchange_model' => ($request->input('tos') != 'new') ? $request->input('exchange_model') : null,
                        'exchange_yom'  =>  ($request->input('tos') != 'new') ? $request->input('exchange_yom') : null,
                        'exchange_value'  =>  ($request->input('tos') != 'new') ? $request->input('exchange_value') : null,
                        'exchange_register_no'  =>  ($request->input('tos') != 'new') ? $request->input('exchange_register') : null,
                        'ex_showroom_price'  =>  $request->input('ex_showroom_price'),
                        'register_price'  =>  $request->input('reg_fee_road_tax'),
                        'accessories_value'  =>  $request->input('accessories_cost'),
                        'hypo'  =>  ($request->input('customer_pay_type') != 'cash') ? $request->input('hypo_cost') : null,
                        'total_amount'  =>  round($request->input('total_calculation')),
                        'balance'  =>  $balance
            ];
            if($changeProdStore == 1){
                $saleUpdateData['pending_item'] = ($any_addon_pending == 1)?'yes':'no';
            }

            $saleUpdate = Sale::where('id',$saleId)->update($saleUpdateData);
            if($changeProdStore == 1){
                // unlaod addon stock reduce
                foreach($addon as $k => $v){
                    if(isset($old_addon_data[$v])){
                        // update old Addon Stock
                        if($old_addon_data[$v] > 0){
                            $old_dec = VehicleAddonStock::where('vehicle_addon_key',$v)
                            ->where('store_id',$oldSaleData->store_id)
                            ->decrement('sale_qty',$old_addon_data[$v]);
                            $old_inc = VehicleAddonStock::where('vehicle_addon_key',$v)
                            ->where('store_id',$oldSaleData->store_id)
                            ->increment('qty',$old_addon_data[$v]);
                        }
                    }
                    if(isset($addon_data[$v])){
                        // insert in sale_pending_addon
                        $spa_data = ['sale_id'  =>  $saleId];
                        $spa_data = array_merge($spa_data,$addon_data[$v]);
                        if($addon_data[$v]['stock_status'] == 0){
                            $spa_insert = SalePendingAddon::insertGetId($spa_data);
                        }else{
                            // update new addon stock
                            $dec_inQty = VehicleAddonStock::where('vehicle_addon_key',$v)
                                                        ->where('store_id',$request->input('store_name'))
                                                        ->decrement('qty',1);
                            $inc_inSale = VehicleAddonStock::where('vehicle_addon_key',$v)
                                                        ->where('store_id',$request->input('store_name'))
                                                        ->increment('sale_qty',1);
                        }
                    }
                }
            }

            // update in sale rto---------------
            $sale_rto_data = [
                // 'sale_id'   =>  $saleId,
                'rto'   =>  $request->input('rto'),
                'handling_charge'   =>  ($request->input('rto') == 'normal')?$request->input('handling_charge'): 0,
                'affidavit_cost'    =>  ($request->input('rto') == 'normal' && $request->input('affidavit') == 'affidavit')?$request->input('affidavit_cost') : 0,
                'rto_type'  =>  $request->input('permanent_temp')
            ];
            if($request->input('fancy') == 'Yes'){
                $sale_rto_data = array_merge($sale_rto_data,[
                                    'fancy_no_receipt'  =>  $request->input('fancy_no'),
                                    'fancy_date'  =>  $request->input('fancy_date')
                                ]);
            }else{
                $sale_rto_data = array_merge($sale_rto_data,[
                    'fancy_no_receipt'  =>  null,
                    'fancy_date'  =>  null
                ]);
            }
            $sale_rto = SaleRto::where('sale_id',$saleId)->update($sale_rto_data);
            //  -------------------------------

            //type of sale

            $pre_exist = BestDealSale::where('purchase_sale_id',$saleId)->where('status','Pending')->first();
            if($oldSaleData->type_of_sale == 3){
                if(!isset($pre_exist->id)){
                    DB::rollBack();
                    return back()->with('error','Previous BestDeal Sale Record Not Found.')->withInput();
                }
            }
            if($oldSaleData->type_of_sale == 2){
                if(!isset($pre_exist->id)){
                    DB::rollBack();
                    return back()->with('error','Previous Exchange Sale Record Not Found.')->withInput();
                }
            }
            if($request->input('tos') != 'new')
            {
                $tos = [
                    'tos'   =>  $request->input('tos'),
                    'model' => $request->input('exchange_model') ,
                    'yom'  =>  $request->input('exchange_yom') ,
                    'value'  =>  $request->input('exchange_value') ,
                    'register_no'  =>   $request->input('exchange_register') 
                ];
                // $pre_exist = BestDealSale::where('sale_id',$saleId)->first();
                if(empty($pre_exist))
                {
                    BestDealSale::insertGetId($tos);
                }
                else
                {
                    BestDealSale::where('id',$pre_exist->id)->update($tos);
                }
            }
            elseif(!empty($pre_exist))
            {
                BestDealSale::where('id',$pre_exist->id)->update(['deleted_at'  =>  date('Y-m-d')]);
            }

            // discount Update
            $discount_data = [
                'type'  =>  'sale',
                'type_id'   =>  $saleId,
                'discount_type'  =>  $request->input('discount'),
                'scheme_id'  =>  ($request->input('discount') == 'scheme') ? $request->input('scheme') : 0,
                'remark'  =>  ($request->input('discount') == 'scheme') ? $request->input('scheme_remark') : null,
                'amount'  =>  $request->input('discount_amount')
            ];
            if($request->input('discount') == 'normal' && $request->input('discount_amount') >= 1000)
            {
                $discount_data = array_merge($discount_data,['status' => 'pending']);
            }else{
                $discount_data = array_merge($discount_data,['status' => 'approve']);
            }
            $checkDiscount = SaleDiscount::where('type','sale')->where('type_id',$saleId)->whereIn('discount_type',['Normal','Scheme'])
                                    ->whereNull('deleted_at')
                                    ->first();
            if(isset($checkDiscount->id)){
                $discountupdate  =  SaleDiscount::where('id',$checkDiscount->id)->update($discount_data);
                $discount = $checkDiscount->id;
            }else{
                $discount  =  SaleDiscount::insertGetId($discount_data);
            }
            $insert_approval = 0;
            // check according previous sale discount have approval request and after update amount is less than 1000, then approval should deleted
            if($request->input('discount') == 'normal' && $request->input('discount_amount') < 1000)
            {
                $ins_approval = CustomHelpers::sale_disocunt_delete_approval($discount,$request->input('store_name'),$saleId);
                $insert_approval = 0;
            }elseif($request->input('discount') == 'normal' && $request->input('discount_amount') >= 1000){
                $insert_approval = 1;
                // insert and update
                if(isset($checkDiscount->id)){
                    // check and insert request
                    $update_approval = CustomHelpers::sale_disocunt_check_insert_approval($discount,$saleUpdateData['store_id'],$saleId);
                }
            }

            // check csd discount
            $checkcsd = SaleDiscount::where('type','sale')->where('type_id',$saleId)->whereIn('discount_type',['CSD'])
                                ->whereNull('deleted_at')->whereIn('status',['pending','approve'])
                                ->first();
            if(isset($checkcsd->id)){
                if($checkcsd->status == 'approve'){
                    DB::rollback();
                    return back()->with('error','CSD Amount will not be changed after Approval.')->withInput();
                }
            }
            if($request->input('sale_type') == 'corporate' && $csd > 0)
            {
                $csdData  = [
                    'type'  =>  'sale',
                    'type_id'   =>  $saleId,
                    'discount_type' =>  'CSD',
                    'amount'    =>  $csd
                ];
                if(isset($checkcsd->id)){
                    $csdupdate  =  SaleDiscount::where('id',$checkcsd->id)->update($csdData);
                }else{
                    $csdinsert  =  SaleDiscount::insertGetId($csdData);
                }
            }elseif(isset($checkcsd->id)){
                $csddelete  =  SaleDiscount::where('id',$checkcsd->id)->update([    
                        'deleted_at' =>  date('Y-m-d H:i:s'),
                        'status'    =>  'delete'
                    ]);
            }

            // insert in insurance_data
            $insurance_data = [
                'store_id'  =>  $saleUpdateData['store_id'],
                'sale_id'   =>  $saleId,
                'customer_id'   =>  $saleUpdateData['customer_id'],
                'type'  =>  'new'
            ];
            $old_insur_data = InsuranceData::where('customer_id',$saleUpdateData['customer_id'])->first();
            $insur_data_id = 0;
            if(isset($old_insur_data->id)){
                $insur_data_id = $old_insur_data->id;
            }else{
                $insur_data_id = InsuranceData::insertGetId($insurance_data);
            }
            // if($oldSaleData->customer_id != $saleUpdateData['customer_id']){
            //     if(isset($old_insur_data->id)){
            //         $update_insur = InsuranceData::where('id',$old_insur_data->id)
            //                             ->update($insurance_data);
            //         $insur_data_id = $old_insur_data->id;
            //     }
            // }
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
            $old_od_data = Insurance::where('sale_id',$saleId)->where('type','new')->where('status','Done')
                            ->where('customer_id',$oldSaleData->customer_id)->where('insurance_type','OD')->first();
            $old_tp_data = Insurance::where('sale_id',$saleId)->where('type','new')->where('status','Done')
                            ->where('customer_id',$oldSaleData->customer_id)->where('insurance_type','TP')->first();
            if(!isset($old_od_data->id) || !isset($old_tp_data)){
                DB::rollback();
                return back()->with('error','Insurance Record Not Found.')->withInput();
            }
            $common_ins_data = [
                'customer_id'   =>  $saleUpdateData['customer_id'],
                'sale_id'   =>  $saleId,
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
            }else{
                $od_ins_data = array_merge($od_ins_data,[
                    'cpa_company'   =>  null,
                    'cpa_tenure'   =>  null,
                    'cpa_amount'   =>  null
                ]);
            }
            // third party data insert
            $tp_ins_data = array_merge($common_ins_data,[
                'policy_tenure' =>  $tp,
                'insurance_type'    =>  'TP'
            ]);
            // check
            $update_od_insurance = Insurance::where('id',$old_od_data->id)->update($od_ins_data);
            $update_tp_insurance = Insurance::where('id',$old_tp_data->id)->update($tp_ins_data);

            // -------------- start otc sale update
            // get old otc sale data
            $old_otc = OtcSale::where('sale_id',$saleId)->where('store_id',$oldSaleData->store_id)
                                ->where('otc_sale.with_sale',1)
                                ->first();
            if(!isset($old_otc->id)){
                DB::rollBack();
                return back()->with('error','Previous Accessories Not Found.')->withInput();
            }
            $old_otc_detail = OtcSaleDetail::where('otc_sale_id',$old_otc->id)
                                ->where('with_sale',1)->get();
            // update new otc sale when store_id are same
            $total_accessories = [];
            if(count($all_accessory) > 0){
                $total_accessories = $all_accessory;
            }
            $otc_sale_id = $old_otc->id;
            if(!$otc_sale_id){
                DB::rollback();
                return back()->with('error','Error, Something Went Wrong.')->withInput();
            }
            $part_stock_new_store_id = $saleUpdateData['store_id'];
            $part_stock_old_store_id = $oldSaleData['store_id'];
            if($oldSaleData->store_id == $saleUpdateData['store_id']){  // when store not changed

                // check and update accessories
                $new_part_ids = [];$new_acc_data = [];
                foreach($total_accessories as $ind1 => $new_data){
                    array_push($new_part_ids,$new_data['part_id']);
                    $new_acc_data[$new_data['part_id']] = $new_data;
                }
                $ins_acc = [];$del_acc_id = [];$del_osd_data = [];$old_acc_id = []; 
                foreach($old_otc_detail as $ind => $old_data){
                    if(in_array($old_data->part_id,$new_part_ids)){
                        // if part_id already exist
                        array_push($old_acc_id,$old_data->part_id);
                    }else{
                        // if old part_id not entered
                        array_push($del_acc_id,$old_data->part_id);
                    }
                    $del_osd_data[$old_data->part_id] = $old_data;
                }
                // get new insert accessoires data
                if(count($old_acc_id) > 0 || count($del_acc_id) > 0){
                    foreach($new_part_ids as $i => $part_id){
                        if(in_array($part_id,$old_acc_id) || in_array($part_id,$del_acc_id)){
                            // nothing do
                        }else{
                            // new part id which insert
                            array_push($ins_acc,$new_acc_data[$part_id]);
                        }
                    }
                }
                if(count($old_otc_detail) > 0){   // if previous accessories are not sale
                    $total_accessories = $ins_acc;
                }

            }else{  // when store_id will be changed
                $del_acc_id = [];$del_osd_data = [];
                foreach($old_otc_detail as $ind => $old_data){

                    array_push($del_acc_id,$old_data->part_id);
                    $del_osd_data[$old_data->part_id] = $old_data;
                }
            }

            // update in new accessories
            foreach($total_accessories as $in => $val){
                $acc_data = [
                    'otc_sale_id'   =>  $otc_sale_id,
                    'type'  =>  'Part',
                    'part_id'   =>  $val['part_id'],
                    'part_desc' =>  $val['part_desc'],
                    'qty'   =>  $val['qty'],
                    'amount'    =>  $val['amount'],
                    'with_sale' =>  1
                ];
                $check_acc_qty = PartStock::where('part_id',$val['part_id'])->where('store_id',$part_stock_new_store_id)
                                        ->where('quantity','>=',$val['qty'])->first();
                if(!isset($check_acc_qty->id))
                {
                    $acc_data = array_merge($acc_data,['otc_status' => 0]);
                }else{
                    $updatePartStock = PartStock::where('part_id',$val['part_id'])->where('store_id',$part_stock_new_store_id);
                    $inc = $updatePartStock->increment('sale_qty',$val['qty']);
                    $dec = $updatePartStock->decrement('quantity',$val['qty']);
                    $acc_data = array_merge($acc_data,['otc_status' => 1]);
                }
                if(count($del_acc_id) > 0){   // if delete accessories any
                    $update_osd = OtcSaleDetail::where('id',$del_osd_data[$del_acc_id[0]]->id)
                                                    ->update($acc_data);
                    // recover part stock if otc_status = 1
                    if($del_osd_data[$del_acc_id[0]]->otc_status == 1){
                        $update_inc = PartStock::where('part_id',$del_acc_id[0])
                                                ->where('store_id',$part_stock_old_store_id)
                                                ->increment('quantity',$del_osd_data[$del_acc_id[0]]->qty);
                        $update_dec = PartStock::where('part_id',$del_acc_id[0])
                                                ->where('store_id',$part_stock_old_store_id)
                                                ->decrement('sale_qty',$del_osd_data[$del_acc_id[0]]->qty);
                    }
                    array_splice($del_acc_id,0,1);
                }else{
                    $oth_accessoriesInsert = OtcSaleDetail::insertGetId($acc_data);
                }
            }
            // if remaining delete acc_id
            if(count($del_acc_id) > 0){   // if delete accessories any
                foreach($del_acc_id as $i => $part_id){

                    $update_osd = OtcSaleDetail::where('id',$del_osd_data[$part_id]->id)
                                                    ->delete();
                    // recover part stock if otc_status = 1
                    if($del_osd_data[$part_id]->otc_status == 1){
                        $update_inc = PartStock::where('part_id',$part_id)
                                                ->where('store_id',$part_stock_old_store_id)
                                                ->increment('quantity',$del_osd_data[$part_id]->qty);
                        $update_dec = PartStock::where('part_id',$part_id)
                                                ->where('store_id',$part_stock_old_store_id)
                                                ->decrement('sale_qty',$del_osd_data[$part_id]->qty);
                    }
                    array_splice($del_acc_id,$i,1);
                }
            }

            $check_osd = OtcSaleDetail::where('otc_sale_id',$otc_sale_id)->where('with_sale',1)
                                ->select(
                                    DB::raw("group_concat(part_id, '') as part_ids"),
                                    DB::raw("sum(amount) as total_amount")
                                )->groupBy('otc_sale_id')->first();
            $total_acc_amount = 0;
            if(isset($check_osd->total_amount)){
                $total_acc_amount = $check_osd->total_amount;
            }
            $otc_sale_update = OtcSale::where('id',$otc_sale_id)->update([
                'customer_id'   =>  $customerId,
                'store_id'  =>  $request->input('store_name'),
                'with_sale' =>  1,
                'total_amount'    =>  $total_acc_amount
            ]);

            // check any pending items
            $check_old_pending = OrderPendingModel::where('sale_id',$saleId)->first();
            if(isset($check_osd->part_ids)){
                $ids = $check_osd->part_ids;
                if(isset($check_old_pending->id)){
                    $pending_item = OrderPendingModel::where('id',$check_old_pending->id)->update([
                        'accessories_id'    =>  $ids
                    ]);
                }else{
                    $pending_item = OrderPendingModel::insertGetId([
                        'sale_id'   =>  $saleId,
                        'accessories_id'    =>  $ids
                    ]);
                }
                Sale::where('id',$saleId)->update([
                    'pending_item'  =>  'yes'
                ]);
            }else{
                if(isset($check_old_pending->id)){
                    $pending_item = OrderPendingModel::where('id',$check_old_pending->id)->update([
                        'accessories_id'    =>  ''
                    ]);
                }
                Sale::where('id',$saleId)->update([
                    'pending_item'  =>  'no'
                ]);
            }
            // -------end otc sale update
            $checkfinance = SaleFinance::where('sale_id',$saleId)->where('status',0)->whereNull('deleted_at')->first();
            if($oldSaleData->customer_pay_type != 'cash'){
                if(!isset($checkfinance->id)){
                    DB::rollBack();
                    return back()->with('error','Previous Finance Record Not Found.')->withInput();
                }
            }
            if($request->input('customer_pay_type') == 'finance')
            {
                    $finance = array(
                        'sale_id'    =>  $saleId,
                        'finance_executive_id'  =>  $request->input('finance_name'),
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
                    if(!isset($checkfinance->id))
                    {
                        $financeInsert = SaleFinance::insertGetId($finance);
                    }
                    elseif(isset($checkfinance->id))
                    {
                        $financeInsert = SaleFinance::where('id',$checkfinance->id)->update($finance);
                    }
            }
            elseif($request->input('customer_pay_type') == 'self_finance')
            {
                    $self_finance = array(
                        'sale_id'    =>  $saleId,
                        'company'  =>  $request->input('self_finance_company'),
                        'loan_amount'  =>  $request->input('self_finance_amount'),
                        'do'    =>  0,
                        'dp'    =>  0,
                        'los'   =>  null,
                        'roi'   =>  0,
                        'payout'    =>  0,
                        'pf'    =>  0,
                        'mac'   =>  0,
                        'sd'    =>  null
                    );
                    if(!isset($checkfinance->id))
                    {
                        $self_financeInsert = SaleFinance::insertGetId($self_finance);
                    }
                    elseif(isset($checkfinance->id))
                    {
                        $self_financeInsert = SaleFinance::where('id',$checkfinance->id)->update($self_finance);
                    }
            }elseif($request->input('customer_pay_type') == 'cash')
            {
                if(isset($checkfinance->id))
                {
                    SaleFinance::where('id',$checkfinance->id)->update(['deleted_at'    =>  date('Y-m-d H:i:s')]);
                }
            }

            // create amc
            $check_amc = AMC::where('sale_id',$saleId)->first();
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
                    'sale_id'   =>  $saleId,
                    'start_date'    =>  $start_date,
                    'end_date'  =>  $end_date,
                    'service_allowed'   =>  $service_allowed->value,
                    'amount'    =>  $request->input('amc_cost')
                ];
                if(isset($check_amc->id)){
                    // update it
                    $update_amc = AMC::where('id',$check_amc->id)->update($amc_data);
                }else{
                    $amc = AMC::insertGetId($amc_data);
                }
            }else{
                if(isset($check_amc->id)){
                    $del_amc = AMC::where('id',$check_amc->id)->delete();
                }
            }

            // create ew
            $check_ew = ExtendedWarranty::where('sale_id',$saleId)->first();
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
                    return back()->with('error','Master Not Found.')->withInput();
                }
                $ew = [
                    'sale_id'   =>  $saleId,
                    'start_date'    =>  $start_date,
                    'end_date'  =>  $end_date,
                    'km'    =>  $find_km->km,
                    'amount'    =>  $request->input('ew_cost')
                ];
                if(isset($check_ew->id)){
                    // update it
                    $update_ew = ExtendedWarranty::where('id',$check_ew->id)->update($ew);
                }else{
                    $ew = ExtendedWarranty::insertGetId($ew);
                }
            }else{
                if(isset($check_ew->id)){
                    $del_ew = ExtendedWarranty::where('id',$check_ew->id)->delete();
                }
            }
            /// create hjc
            $check_hjc = HJCModel::where('sale_id',$saleId)->first();
            if($request->input('hjc'))
            {
                $start_date = date('Y-m-d',strtotime('+1 year',strtotime($sale_date)));
                $end_date = date('Y-m-d', strtotime('+1 year',strtotime($start_date)));
                $hjc = [
                    'sale_id'   =>  $saleId,
                    'start_date'    =>  $start_date,
                    'end_date'  =>  $end_date,
                    'amount'    =>  $request->input('hjc_cost')
                ];
                if(isset($check_hjc->id)){
                    // update it
                    $update_hjc = HJCModel::where('id',$check_hjc->id)->update($hjc);
                }else{
                    $hjc = HJCModel::insertGetId($hjc);
                }
            }else{
                if(isset($check_hjc->id)){
                    $del_hjc = HJCModel::where('id',$check_hjc->id)->delete();
                }
            }

            // insert in payment request   when type of sale is new
            if($request->input('tos') != 'best_deal' && $insert_approval == 0)
            {
                $in_data = [
                    'store_id'  =>  $request->input('store_name'),
                        'type'  =>  'sale',
                        'type_id'   =>  $saleId,
                        'amount'    =>  $balance,
                        'status'    =>  'Pending'
                ];
                if($oldSaleData->type_of_sale == 3){
                    $delete_best_deal_approval = CustomHelpers::best_deal_delete_approval($pre_exist->id,$request->input('store_name'),'sale',$saleId);
                }
                // check payment request
                $check_payment = PaymentRequest::where('type','sale')->where('type_id',$saleId)
                                    ->where('store_id',$oldSaleData->store_id)->first();
                if(isset($check_payment->id))
                {
                    $payment_req = PaymentRequest::where('id',$check_payment->id)->update($in_data);
                }else{
                    $payment_req = PaymentRequest::insertGetId($in_data);
                }
                
            }
            elseif($request->input('tos') == 'best_deal'){
                // insert in approval request
                // $approve_insert = CustomHelpers::best_deal_insert_approval($request->input('best-id'),$request->input('store_name'),'sale',$saleId);
            }

        }catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return back()->with('error','Something went wrong.'.$ex->getMessage())->withInput();
        }
        /* Add action Log */
        CustomHelpers::userActionLog($action='Sale Update',$saleId,$saleUpdateData['customer_id']);
        DB::commit();
        return back()->with('success','Sale Updated Successfully.');
    }

    public function sale_view($id) {
       $saleData = Sale::select("sale.*","booking.booking_number","rto.rto_amount","rto.rc_number",
            "rto.rto_finance","rto.application_number","rto.rto_type","rto.registration_number","rto.penalty_charge",
            "rto.file_submission","rto.approve","rto.approval_date","rto.file_uploaded","rto.uploaded_date",
            "rto.front_lid","rto.rear_lid","rto.receiving_date","hirise.invoice","hirise.amount",
            "sale_order.product_frame_number","sale_order.battery_number","sale_order.front_tyre_no",
            "sale_order.rear_tyre_no","sale_order.key_number","sale_order.tyre_make","sale_order.order_date",
            "sale_order.quantity","sale_order.amount","insurance.policy_number",
            DB::raw("(select name from insurance_company where id = insurance.insurance_co) as insurance_co"),
            "insurance.insurance_type",
            "insurance.insurance_amount","customer.name","customer.relation_type","customer.relation",
            "customer.aadhar_id","customer.voter_id","customer.pin_code","customer.reference","customer.address",
            "customer.mobile","customer.email_id",
            DB::raw('concat(product.model_category,"-",product.model_name,"-",product.model_variant) as product_name'),"store.name as store_name",
            "otc.amount as otc_amount","otc.date as otc_date","otc.invoice_no as otc_invoice",
            "order_pending_item.other as pending_other","order_pending_item.accessories_id",
            // DB::raw('sum(payment.amount) as total_payment')
            DB::raw('(select sum(payment.amount) from payment where sale_id = sale.id and type = "sale" and status <>  "cancelled") as total_payment '),
            DB::raw('IFNULL(fuel.quantity,0) as fuel_qty'),
            DB::raw('(select sum(amount) from sale_discount where type_id = sale.id and type = "sale" group by type_id ) as sale_discount_am'),
            'sale_finance_info.name as financer_name','sale_finance_info.company','sale_finance_info.loan_amount','sale_finance_info.disbursement_amount','sale_finance_info.pending_disbursement_amount',
            'product.model_category','product.model_name','product.model_variant','product.color_code','product.basic_price'
        )
        ->leftJoin("sale_order",function($join) use($id){
            $join->on("sale_order.sale_id","=","sale.id");
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
        ->leftJoin("sale_finance_info",function($join){
            $join->on("sale.id","=","sale_finance_info.sale_id");
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
        ->leftJoin("fuel",function($join){
            $join->on("fuel.type_id","=","sale.id")
                ->where('fuel.fuel_mode','sale');
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
            ->whereNotNull("payment.booking_id")
            ->whereNotNull("payment.sale_id")
            ->where("payment.type","sale");
        }) 
        ->where('sale.id',$id)
        ->first();
        if(!isset($saleData->id)){
            return back()->with('error','Sale Data Not Found.');
        }
        $saleData = $saleData->toArray();

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
        return view('admin.sale.sale_view',$data);
    }

    //order Update
    public function orderUpdate(Request $request, $id)
    {
        $saleData = Sale::leftJoin('product','sale.product_id','product.id')->leftJoin('store','sale.store_id','store.id')
                        ->leftJoin('customer','sale.customer_id','customer.id')
                        ->get(['sale.*','customer.name as customer_name','product.model_category',
                        'product.model_variant','product.model_name','store.name as store_name'])
                        ->where('id',$id)->first();
        // $paid_amount = DB::table('payment')->where('sale_id',$id)->where('status','received')->where('type','sale')->sum('amount');
        $checkOrder = SaleOrder::where('sale_id',$id)->first();
        if(empty($checkOrder))
        {
            return redirect('/admin/sale/list')->with('error','Error, Firstly Create Order');
        }
        $checkInsurance = Insurance::where('sale_id',$id)->first();
        if(isset($checkInsurance->id))
        {
            if(!empty($checkInsurance->policy_number)){

                return redirect('/admin/sale/list')->with('error','Error, Order will not be updated, because Insurance has been created.');
            }
        }

        $frame_no = ProductDetails::where('product_id',$saleData['product_id'])->orderBy('manufacture_date')
                                    ->select('frame',
                                        DB::raw('DATEDIFF(CURRENT_DATE,manufacture_date) as frame_duration')
                                    )
                                    ->where(function($query) {
                                        $query->where('status','ok')
                                                ->orwhere('status','sale');
                                    })->get();
        $tyre = Master::where('type','tyre')->orderBy('order_by','ASC')->get();
        $data = array(
            'saleData' => $saleData, 
            'frame_no' => $frame_no,
            'tyre'  =>  $tyre,
            // 'accessories' => $accessories,
            'orderData' => (!empty($checkOrder)) ? $checkOrder->toArray() : array(),
            // 'pending_item' => $pending_item,
            'layout' => 'layouts.main'
        );
         return view('admin.sale.sale_orderUpdate',$data);
    }
    public function orderUpdateDB(Request $request) {
         try {
             $sale_id = $request->input('sale_id');
            $timestamp = date('Y-m-d G:i:s');
            $this->validate($request,[
                'frame'=>'required',
                'product_name'=>'required',
                'store_name'=>'required',
                'battery_no'=>'required',
                'key_no'=>'required',
                'tyre_make'=>'required',
                'front_tyre_no'=>'required',
                'rear_tyre_no'=>'required'
            ],[
                'frame.required'=> 'This field is required.', 
                'product_name.required'=> 'This field is required.', 
                'store_name.required'=> 'This field is required.', 
                'battery_no.required'=> 'This field is required.', 
                'key_no.required'=> 'This field is required.', 
                'tyre_make.required'=> 'This field is required.',  
                'front_tyre_no.required'=> 'This field is required.', 
                'rear_tyre_no.required'=> 'This field is required.'
            ]);
            DB::beginTransaction();
            $order_id = SaleOrder::where('sale_id',$sale_id)->first();
                if (empty($order_id)) {
                    
                    return redirect('/admin/sale/list')->with('error','Firstly Create Order'); 
                    $updateProductDetails = ProductDetails::where('frame',$request->input('frame'))
                    ->update([
                        'status'    =>  'sale'
                    ]);
                }else{
                // return back()->with('error','You are not authorized for update');

                 $updateData = SaleOrder::WithoutTimestamps()->where('id',$order_id->id)->update(
                    [
                        'sale_id'=> $request->input('sale_id'),
                        'customer_id' => $request->input('customer_id'),
                        'store_id' => $request->input('store_id'),
                        'amount' => $request->input('total_amount'),
                        'product_frame_number' => $request->input('frame'),
                        'battery_number' => $request->input('battery_no'),
                        'key_number' => $request->input('key_no'),
                        'tyre_make' => $request->input('tyre_make'),
                        'front_tyre_no' => $request->input('front_tyre_no'),
                        'rear_tyre_no' => $request->input('rear_tyre_no')
                    ]);
                
                if(!empty($updateData)) {
                    if($order_id->product_frame_number != $request->input('frame'))
                    {
                        $updateNewProduct = ProductDetails::where('frame',$request->input('frame'))->update(
                            [
                                'status'    =>  'sale'
                            ]);
                        $updateOldProduct = ProductDetails::where('frame',$request->input('frame'))->update(
                                [
                                    'status'    =>  'ok'
                                ]);
                        if(empty($updateNewProduct) || empty($updateOldProduct))
                        {
                            DB::rollback();
                            return back()->with('error','Error, Occured Some Issue')->withInput();
                        }
                    }
                }
              }
            
        }  catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return redirect('/admin/sale/update/order/'.$request->input('sale_id'))->with('error','some error occurred'.$ex->getMessage());
        }
        DB::commit();
        return redirect('/admin/sale/update/order/'.$request->input('sale_id'))->with('success','Order Updated Successfully .');

    }

    //hirise Update
    public function hiriseUpdate(Request $request,$id)
    {
        $saleData = Sale::where("id",$id)->get()->first();
        $hiriseData = Hirise::where("sale_id",$id)->get()->first();
        $extendedWarrantyData = ExtendedWarranty::where("sale_id",$id)->get()->first();
        if(empty($hiriseData))
        {
            return redirect('/admin/sale/list')->with('error','Error, Firstly Create Hi-Rise.');
        }
        $data  = array(
           'saleData' => $saleData, 
           'hiriseData' => $hiriseData,
           'extendedWarrantyData' => $extendedWarrantyData,
           'layout' => 'layouts.main', 
       );
       return view('admin.sale.update_hirise',$data);
    }
    public function hiriseUpdateDB(Request $request) {
        try {
            $this->validate($request,[
                'sale_id'=>'required',
                'amount'=>'required',
                'invoice'=>'required',
                'extended_warranty_invoice'=>'required_if:ew_invoice,yes',
            ],[
                'sale_id.required'=> 'This is required.',
                'amount.required'=> 'This is required.',  
                'invoice.required'=> 'This is required.', 
                'extended_warranty_invoice.required_if'=> 'This is required.', 
            ]);
            DB::beginTransaction();
            $sale_id = $request->input('sale_id');
            $getdata = Hirise::where('sale_id', $sale_id)->first();
            if ($request->input('amount') == $request->input('showroom_price')) {
                if (empty($getdata)) {

                    return back()->with('error','Firstly Create Hi-Rise');
            }
            else{
                
                $hirisedata = HiRise::where('sale_id',$request->input('sale_id'))->update(
                                            [
                                                'amount'=>$request->input('amount'),
                                                'invoice'=>$request->input('invoice'),
                                            ]);
                if($request->input('ew_invoice') == 'yes'){

                    $extended_warrantydata = ExtendedWarranty::where('sale_id', $sale_id)->first();
                    if($extended_warrantydata){
                        $extended_warranty = ExtendedWarranty::where('sale_id',$request->input('sale_id'))->update(
                                               [ 
                                                   'invoice_number'=>$request->input('extended_warranty_invoice'),
                                               ]);
                    }
                }
                 
                  
            }
            }else{
                DB::rollback();
                return redirect('/admin/sale/update/hirise/'.$request->input('sale_id'))->with('error','Please enter rigth amount.');
            }
        
        }  catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return redirect('/admin/sale/hirise/'.$request->input('sale_id'))->with('error','some error occurred'.$ex->getMessage());
        }
        DB::commit();
        return back()->with('success','Successfully Updated Hi-Rise');
    }

    // Insurance Update
    public function insuranceUpdate(Request $request,$id)
    {
        $insData = Insurance::where("sale_id",$id)->first();
        if(empty($insData))
        {
            return redirect('/admin/sale/list')->with('error','Error, Firstly Create Insurance.');
        }
        $checkRto = RtoModel::where('sale_id',$id)->first();
        if(!empty($checkRto))
        {
            return back()->with('error','Insurance will not be Update, because RTO has been Created.');
        }

        $insData = Insurance::where("sale_id",$id)
                    ->select('insurance.*',
                        DB::raw("group_concat(concat(insurance_type,'-',policy_tenure,' year') SEPARATOR ' ') as insur_type"),    
                        DB::raw("(select name from insurance_company where id = insurance.insurance_co) as insur_company_name"),
                        DB::raw("(select name from insurance_company where id = insurance.cpa_company) as cpa_company_name")
                    )
                    ->orderBy('id')
                    ->groupBy('sale_id')
                    ->get()->first();
        $bookingData = Sale::where("id",$id)->first(); // sale Data
        $data  = array(
            'bookingData' => $bookingData, 
            'insData' => $insData,
            'layout' => 'layouts.main', 
        );
        return view('admin.sale.update_insurance',$data);
    }
    public function insuranceUpdateDB(Request $request) {
        
        
            $arr = [];
            $arr_msg = [];
            $getdata = Insurance::where('sale_id', $request->input('sale_id'))
                                    ->first();

            if(!isset($getdata->id)){
                return back()->with('error','Insurance Not-Found For This Sale-No.')->withInput();
            }
            else{
                if($getdata->insurance_co != $getdata->cpa_company && !empty($getdata->cpa_company))
                {
                    $arr['cpa-ins_policy'] = 'required';  
                    
                    $arr_msg['cpa-ins_policy.required'] = 'This Field is required';
                }
            }

        try {
            $this->validate($request,array_merge($arr,[
                'ins_co'=>'required',
                'ins_type'=>'required',
                'ins_amount'=>'required',
                'ins_policy'=>'required'
            ]),array_merge($arr_msg,[
                'ins_co.required'=> 'This Field is required.',
                'ins_type.required'=> 'This Field is required.',  
                'ins_amount.required'=> 'This Field is required.',  
                'ins_policy.required'=> 'This Field is required.',  
            ]));
            
            if(empty($getdata->policy_number)){
                    return back()->with('error','Error, Firstly Create Insurance.');
            }
            else{
                DB::beginTransaction();

                $data = [];
                // check cpa policy number filled or not
                if(count($arr) > 0){
                    $data['cpa_policy_number']  =  $request->input('cpa-ins_policy');
                }

                $insdata = Insurance::where('sale_id',$request->input('sale_id'))->update(
                        array_merge($data,[                            
                            // 'insurance_amount'=>$request->input('ins_amount'),
                            'policy_number'=>$request->input('ins_policy')
                        ])
                    );
                }
                            
            }  catch(\Illuminate\Database\QueryException $ex) {
                DB::rollback();
                return redirect('/admin/sale/update/insurance/'.$request->input('sale_id'))->with('error','some error occurred'.$ex->getMessage());
            }
            DB::commit();
            return back()->with('success','Suucessfully Updated Insurance');
    }

    public function pendingItemUpdate(Request $request,$id)
    {
        $checkOrder = SaleOrder::where('sale_id',$id)->first();
        if(empty($checkOrder))
        {
            return back()->with('error','Please Fill Order Firstly');
        }
        $checkPendingItem = Sale::where('id',$id)
                            ->first();

        if(empty($checkPendingItem->pending_item))
        {
            return back()->with('error','Error, Please Fill Pending Form Firstly.');
        }
        $sale = Sale::where('id',$id)->select('sale_no','store_id','pending_item')->first();
        $accessories = OtcSale::where('otc_sale.sale_id',$id)
                        ->leftJoin('otc_sale_detail','otc_sale_detail.otc_sale_id','otc_sale.id')
                        ->where('otc_sale_detail.type','Part')
                        ->where('otc_sale.with_sale',1)
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
        $pending_item = OrderPendingModel::where('sale_id',$id)->first();
        
        // other pending items
        $other_pending = SalePendingAddon::where('sale_id',$id)->where('type','other')
                            ->whereNull('issue_date')->get()->toArray();
        // addon pending items
        // $addon_pending_all = SalePendingAddon::where('sale_id',$id)->where('type','addon')->get()->toArray();
        $addon_pending_label = SalePendingAddon::where('sale_id',$id)->where('type','addon')
                            ->where('stock_status',0)->whereNull('issue_date')->get()->toArray();
        $addon_pending_all = SalePendingAddon::where('sale_id',$id)->where('type','addon')
                            ->where('stock_status',1)->get()->toArray();
        $addon_pending = SalePendingAddon::where('sale_id',$id)->where('type','addon')
                            ->where('stock_status',1)->whereNull('issue_date')->get()->toArray();
        // $pending_item = array();
        // $pending_other = array();
       
        $pending_item_all = (($pending_item)? $pending_item->toArray() : array());
        $pending_item = (($pending_item_all)?$pending_item_all['accessories_id'] : '');
        $pending_item = (($pending_item)? explode(',',$pending_item) : array() );

        $pending_other = $other_pending;

       // $fuel = FuelModel::where('type_id',$id)->where('type','sale')->where('store_id',$sale['store_id'])->first();
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
        return view('admin.sale.pendingItemUpdate',$data);
    }
    public function pendingItemUpdateDB(Request $request) {
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
            DB::beginTransaction();
            // if($request->input('select_pending_item') == 'yes')
            // {
                $sale_id = $request->input('sale_id');
                // echo $sale_id;die;
                $prePendingCheck = Sale::where('id',$sale_id)->select('store_id','pending_item')->first();
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
                                
                    // return back()->with('error','Error, You Are Not Authorized For Update This Page');
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
            return redirect('/admin/sale/update/pending/item/'.$request->input('sale_id'))->with('error','some error occurred'.$ex->getMessage());
        }
        DB::commit();
        return redirect('/admin/sale/update/pending/item/'.$request->input('sale_id'))->with('success','Successfully Updated');

    }

    //Update OTC
    public function otcUpdate(Request $request,$id)
    {
        // $checkPendingItem = Sale::where('id',$id)->select('pending_item')->first();
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
        return view('admin.sale.otcUpdate',$data);

    }
    public function otcUpdateDB(Request $request) {
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
          try {
            $this->validate($request,[
                'sale_no'   =>  'required',
                'name'=>'required',
                'number'=>'required',
                'store'=>'required',
                'reason'=>'required',
            ],[
                'sale_no.required'=> 'This field is required.', 
                'name.required'=> 'This field is required.', 
                'number.required'=> 'This field is required.',
                'store.required'=> 'This field is required.',
                'reason.required'=> 'This field is required.',
            ]);
            $sr_no = SecurityModel::select(DB::raw('IFNULL(count(id),1) as max_id'))->first();
            $max = $sr_no->max_id+1;
            $security_no = 'SECTY-'.$max;
            $sale_no = $request->input('sale_no');
            $user_store = CustomHelpers::user_store();
            $checkSaleNo = Sale::where('sale_no',$sale_no)
                            ->whereIn('store_id',$user_store)->first();
            if(!isset($checkSaleNo->id)){
                return back()->with('error','Sale Number Not Found.')->withInput();
            }
            $sale_id = $checkSaleNo->id;
            
             DB::beginTransaction();
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
                    DB::rollback();
                       return redirect('/admin/sale/security/amount')->with('error','some error occurred')->withInput();
                } else{
                    // $request = PaymentRequest::insertGetId([
                    //     'type_id' => $security,
                    //     'type' => 'security',
                    //     'amount' => 0,
                    //     'store_id' => $request->input('store'),
                    //     'status' => 'pending'
                    // ]);
                    // if ($request == NULL) {
                    //     DB::rollback();
                    //     return redirect('/admin/sale/security/amount')->with('error','some error occurred')->withInput();
                    // }
                        /* Add action Log */
                        CustomHelpers::userActionLog($action='Security Amount',$security);
                    DB::commit();
                    return redirect('/admin/sale/security/amount')->with('success','Security amount added Successfully.');
                    
                }

        }  catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return redirect('/admin/sale/security/amount')->with('error','some error occurred'.$ex->getMessage());
        }
    }

    public function security_amount_pay(Request $request) {
        $id = $request->input('id');
        $securityData = SecurityModel::where("id",$id)->get(); 
        if(!isset($securityData[0])){
            return back()->with('error','Security Amount Not Found.')->withInput();
        }
        $paid = DB::table('payment')->where('type','security')->where('type_id',$id)->where('status','<>','cancelled')->sum('amount');
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
         try {
            $timestamp=date('Y-m-d G:i:s');
            $this->validate($request,[
                'amount'=>'required',
                'payment_mode'=>'required',
                'transaction_number'=>'required',
                'transaction_charges'=>'required',
                'receiver_bank_detail'=>'required_unless:payment_mode,Cash',
            ],[
                'amount.required'=> 'This field is required.', 
                'payment_mode.required'=> 'This field is required.', 
                'transaction_number.required'=> 'This field is required.', 
                'transaction_charges.required'=> 'This field is required.', 
                'receiver_bank_detail.required_unless'=> 'This field is required.', 
            ]);
           
            $table = ['security_amount','payment'];
            CustomHelpers::resetIncrement($table);

            DB::beginTransaction();
            $old_amount = 0;
            $total_amount = SecurityModel::where('id',$request->input('security_id'))->select('total_amount')->first();
            if(!isset($total_amount->total_amount)){
                return back()->with('error','Security Amount Not Found')->withInput();
            }
            $old_amount = $total_amount->total_amount;
                $arr = [];
                if($request->input('payment_mode') == 'Cash') {
                    $arr = ['status' => 'received'];
                    $payment_amount = $old_amount + $request->input('amount');
                    $update = SecurityModel::where('id',$request->input('security_id'))->update([
                        'total_amount' => $payment_amount
                    ]);
                }else{
                    $arr['receiver_bank_detail'] = $request->input('receiver_bank_detail');
                }
                $paydata = Payment::insertGetId(
                    array_merge($arr,[
                        'type_id'=> $request->input('security_id'),
                        'type' => 'security',
                        'payment_mode' => $request->input('payment_mode'),
                        'transaction_number' => $request->input('transaction_number'),
                        'transaction_charges' => $request->input('transaction_charges'),
                        'amount' => $request->input('amount'),
                        'store_id' => $request->input('store_id'),
                        'payment_date'  =>  date('Y-m-d')
                    ])
                );
                
                if($paydata == NULL) {
                    DB::rollback();
                       return redirect('/admin/sale/security/amount/pay?id='.$request->input('security_id'))->with('error','some error occurred');
                } else{
                    DB::commit();
                      return redirect('/admin/sale/security/amount/pay?id='.$request->input('security_id'))->with('success','Amount Successfully Paid .');
                }
        }  catch(\Illuminate\Database\QueryException $ex) {
            DB::rollBack();
            return redirect('/admin/sale/security/amount/pay?id='.$request->input('security_id'))->with('error','some error occurred'.$ex->getMessage());
        }
    }

    public function security_payment_details($id) {
        $securityData = SecurityModel::where('id',$id)->get();
        $payData = Payment::where('type_id',$id)->where('type','security')->get();
        $data = array(
            'securityData' => $securityData,
            'payData' => $payData,
            'layout' => 'layouts.main'
        );
         return view('admin.sale.security_paymet_detail',$data);
    }

    public function security_amount_refund(Request $request) {
         try { 
             if(empty($request->input('refund')) || empty(trim($request->input('comment'))) ){
                 return response()->json(['required','All Fields are Required.']);
             }if($request->input('refund') <= 0){
                return response()->json(['required','Amount Should be greater than zero.']);
             }
             DB::beginTransaction();
            $total_amount = SecurityModel::where('id',$request->input('id'))->select('total_amount','refund_amount')->first();
            $receive_amount = Payment::where('type_id',$request->input('id'))->where('type','security')->where('status','<>','cancelled')->sum('amount');
            if($total_amount['refund_amount'] == $receive_amount){
                return response()->json(['error','You are Already Refunded Security Amount.']);
            }
            if (($total_amount['total_amount'] == $receive_amount) && ($total_amount['total_amount'] == $request->input('refund'))) {
                 $security = SecurityModel::where('id',$request->input('id'))->update(
                    [
                        'refund_amount'=>$request->input('refund'),
                        'refund_comment'=>$request->input('comment'),
                    ]
                );
                if($security == NULL)  {
                    DB::rollback();
                    return response()->json(['error','Something went wrong.']);
                } else{
                    DB::commit();
                    return response()->json(['success','Successfully Refunded.']); 
                 }   
            }else{
                DB::rollback();
                return response()->json(['error','Please enter the amount equal to total amount.']);
            }
       
        }  catch(\Illuminate\Database\QueryException $ex) {
            DB::rollBack();
            return response()->json(['error','Something went wrong.']);
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
                        'payment_type'  =>  'Refund'
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

      public function HandlingCharge_list() {
        $agent_name = RtoFileSubmission::where('agent_name','<>',null)->groupBy('agent_name')->get();
        $handling_type = SaleRto::where('rto','<>',null)->groupBy('rto')->get();
       $data = [
            'agent_name' => $agent_name,
            'handling_type' => $handling_type,
            'layout' => 'layouts.main'
        ];
        return view('admin.sale.handling_charge_list',$data);
    }

    public function HandlingChargeList_api(Request $request) {
        $handling_type = $request->input('handling_type');
        $agent_name = $request->input('agent_name');
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
        $agentname = RtoFileSubmission::select('agent_name')->get();
        $handlingtype = SaleRto::where('rto','<>',null)->select('rto')->get();
            $api_data = SaleRto::leftJoin('sale','sale_rto.sale_id','sale.id')
                        ->leftJoin('sale_order','sale_order.sale_id','sale_rto.sale_id')
                        ->leftJoin('rto','rto.sale_id','sale_rto.sale_id')
                        ->leftJoin('rto_file_submission_details','rto.id','rto_file_submission_details.rto_id')
                        ->leftJoin('rto_file_submission','rto_file_submission.id','rto_file_submission_details.file_submission_id')
                        ->whereIn('rto_file_submission.agent_name',$agentname)
                        ->whereIn('sale_rto.rto',$handlingtype)
                        ->select(
                            'sale.sale_no',
                            'sale.sale_date',
                            'rto_file_submission.agent_name',
                            'rto.rto_amount',
                            'sale_order.product_frame_number',
                            'sale_rto.rto',
                            'sale_rto.handling_charge',
                            'sale_rto.affidavit_cost',
                            'sale_rto.rto_type',
                            'sale_rto.fancy_no_receipt',
                            'sale_rto.fancy_date'
                        );
            if(!empty($serach_value))
            {
               $api_data->where(function($query) use ($serach_value){
                        $query->where('sale_rto.rto','like',"%".$serach_value."%")
                        ->orwhere('sale_rto.handling_charge','like',"%".$serach_value."%")
                        ->orwhere('sale_order.product_frame_number','like',"%".$serach_value."%")
                        ->orwhere('sale_rto.affidavit_cost','like',"%".$serach_value."%")
                        ->orwhere('sale_rto.rto_type','like',"%".$serach_value."%")
                        ->orwhere('rto.rto_amount','like',"%".$serach_value."%")
                        ->orwhere('rto_file_submission.agent_name','like',"%".$serach_value."%");
                    });
               
            }
             if(!empty($handling_type)) {
               $api_data->where(function($query) use ($handling_type){
                        $query->where('sale_rto.rto','like',"%".$handling_type."%");
                    });
            }
             if(!empty($agent_name)) {
               $api_data->where(function($query) use ($agent_name){
                        $query->where('rto_file_submission.agent_name','like',"%".$agent_name."%");
                    });
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                    'sale_order.product_frame_number',
                    'rto_file_submission.agent_name',
                    'sale_rto.rto_type',
                    'rto.rto_amount',
                    'sale_rto.rto',
                    'sale_rto.handling_charge',
                    'sale_rto.affidavit_cost',
                    'sale_rto.fancy_no_receipt',
                    'sale_rto.fancy_date'
                ];
                
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('sale_rto.id','desc');   
        
        $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array); 
    }

    // pending sale Items list
    public function salePendingAddonlist(){
        
        $data = [
            'layout' => 'layouts.main'
        ];
        return view('admin.sale.sale_pending_addon_list',$data);
    }

    public function salePendingAddon_api(Request $request) {

        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;

        $tab = $request->input('tabVal');

        $api_data = SalePendingAddon::join('sale','sale.id','sale_pending_addon.sale_id')
                                    ->leftJoin('rto','rto.sale_id','sale.id')
                                    ->leftjoin('store','store.id','sale.store_id')
                                    ->leftjoin('customer','customer.id','sale.customer_id')
                                    ->leftjoin('users','users.id','sale_pending_addon.issue_by')
                                    ->whereNull('rto.deleted_at')
                                    ->whereNull('sale.deleted_at')
                                    ->whereIn('sale.store_id',CustomHelpers::user_store())
            ->select(
                'sale.id as sale_id',
                'sale.sale_no',
                'store.name as store_name',
                DB::raw('UPPER(customer.name) as customer_name'),
                DB::raw("GROUP_CONCAT( UPPER(REPLACE(sale_pending_addon.type_name,'_',' ')), '' ) as addon_details"),
                'rto.application_number as rto_application_no',
                'sale_pending_addon.issue_date',
                'users.name as issue_by'
            )->groupBy('sale_pending_addon.sale_id');
        // print_r($api_data->get()->toArray());die;
        if($tab == 'pending'){
            $api_data = $api_data->whereNULL('sale_pending_addon.issue_date');
        }elseif($tab == 'complete'){
            $api_data = $api_data->whereNotNULL('sale_pending_addon.issue_date')
                                ->groupBy('sale_pending_addon.issue_date')
                                ->groupBy('sale_pending_addon.issue_by');
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
                $api_data->orderBy('sale.id','desc');      
        
        $count = $api_data->count();
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

    public function get_sale_pendingAddon(Request $request){
        try{
            $sale_id = $request->input('sale_id');
            $check = Sale::where('sale.id',$sale_id)
                        ->whereIn('sale.store_id',CustomHelpers::user_store())
                        ->leftjoin('rto','rto.sale_id','sale.id')
                        ->whereNull('rto.deleted_at')->whereNull('sale.deleted_at')
                        ->select('sale.id as sale_id','rto.id as rto_id')
                        ->first();
            if(isset($check->sale_id)){
                if(empty($check->rto_id) || $check->rto_id <= 0){
                    return response()->json('Firstly Complete RTO Process.',401);
                }
                $get = SalePendingAddon::where('sale_id',$sale_id)
                                        ->whereNull('issue_date')
                                        ->select(
                                            'sale_pending_addon.id',
                                            DB::raw("UPPER(REPLACE(sale_pending_addon.type_name,'_',' ')) AS name")
                                        )
                                        ->get();
                if(!isset($get[0])){
                    return response()->json('Pending Addon Not Found',401);
                }
                return response()->json($get);
            }else{
                return response()->json('Sale Not Found',401);
            }
        }
        catch(\Illuminate\Database\QueryException $ex) {
            return response()->json('Something went wrong. '.$ex->getMessage(),401);
        }
    }

    public function issue_pendingAddonDB(Request $request){
        try{
            $sale_id = $request->input('sale_id');
            $pending_addon_ids = $request->input('pending_addon_ids');
            if(!isset($pending_addon_ids[0])){
                return response()->json('Required to Select minimum One Item.',401);
            }
            $all_ids = $pending_addon_ids;
            DB::beginTransaction();

            $check = Sale::where('sale.id',$sale_id)
                        ->whereIn('sale.store_id',CustomHelpers::user_store())
                        ->leftjoin('rto','rto.sale_id','sale.id')
                        ->whereNull('rto.deleted_at')->whereNull('sale.deleted_at')
                        ->select('sale.id as sale_id','rto.id as rto_id','sale.customer_id','sale.product_id','sale.store_id')
                        ->first();
            if(isset($check->sale_id)){
                if(empty($check->rto_id) || $check->rto_id <= 0){
                    DB::rollBack();
                    return response()->json('Firstly Complete RTO Process.',401);
                }
                $product = ProductModel::where('id',$check->product_id)->select('model_name')->first();
                $addon_om = str_replace(' ','_',strtolower('owner_manual_'.$product->model_name));

                $get_pending = SalePendingAddon::whereIn('id',$all_ids)
                                                ->where('sale_id',$sale_id)
                                                ->where('type','addon')
                                                ->whereNull('issue_date')
                                                ->select('type_name')
                                                ->get()->toArray();
                foreach($get_pending as $key => $val){
                    $check_stock = VehicleAddonStock::where('vehicle_addon_key',$val['type_name'])
                                                        ->where('store_id',$check->store_id)->first();
                    $addon_name = strtoupper(str_replace('_',' ',$val['type_name']));
                    if(!isset($check_stock->id)){
                        DB::rollback();
                        return response()->json('Stock Not Available for '.$addon_name.' Addon.',401);
                    }
                    if($check_stock->qty <= 0){
                        DB::rollback();
                        return response()->json('Stock Not Available for '.$addon_name.' Addon.',401);
                    }
                    $dec_stock = VehicleAddonStock::where('vehicle_addon_key',$val['type_name'])
                                                    ->where('store_id',$check->store_id)
                                                    ->decrement('qty',1);
                    $inc_stock = VehicleAddonStock::where('vehicle_addon_key',$val['type_name'])
                                                    ->where('store_id',$check->store_id)
                                                    ->increment('sale_qty',1);
                    if($val['type_name'] == $addon_om){
                        $dec_stock = VehicleAddonStock::where('vehicle_addon_key','owner_manual')
                                                    ->where('store_id',$check->store_id)
                                                    ->decrement('qty',1);
                        $inc_stock = VehicleAddonStock::where('vehicle_addon_key','owner_manual')
                                                    ->where('store_id',$check->store_id)
                                                    ->increment('sale_qty',1);
                    }
                }
                $update_issue = SalePendingAddon::whereIn('id',$all_ids)
                                                ->where('sale_id',$sale_id)
                                                ->whereNull('issue_date')
                                                ->update([
                                                    'issue_date' => date('Y-m-d'),
                                                    'issue_by'  =>  Auth::id()
                                                ]);
                
                if($update_issue){
                    /* Add action Log */
                    CustomHelpers::userActionLog($action='Pending Addon Issue',$sale_id,$check->customer_id);
                    DB::commit();
                    return response()->json('Successfully Pending Addon Issued.');
                }else{
                    DB::rollBack();
                    return response()->json('Something Went Wrong.',401);
                }
                
            }else{
                DB::rollBack();
                return response()->json('Sale Not Found',401);
            }

        }catch(\Illuminate\Database\QueryException $ex) {
            DB::rollBack();
            return response()->json('Something went wrong. '.$ex->getMessage(),401);
        }
    } 


}