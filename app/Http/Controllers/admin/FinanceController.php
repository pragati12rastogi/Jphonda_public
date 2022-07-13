<?php

namespace App\Http\Controllers\admin;
use Illuminate\Http\Request;
use \stdClass;
use \App\Model\Store;
use \App\Model\Unload;
use \App\Model\ProductModel;
use \App\Model\ProductDetails;
use \App\Custom\CustomHelpers;

use App\Model\Users;
use \App\Model\Sale;
use \App\Model\SaleAccessories;
use \App\Model\SaleFinance;
use \App\Model\RtoModel;
use \App\Model\FinanceDetail;
use \App\Model\FinanceCompany;
use \App\Model\ApprovalRequest;
use \App\Model\PaymentRequest;
use \App\Model\RcCorrectionRequest;
use \App\Model\SubDealer;
use \App\Model\FinanceTaAdjustment;
use \App\Model\FinanceTaPayment;
use \App\Model\FinanceTaAdjustmentInterest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Auth;
use Hash;
use PDF;
use \Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\Import;
use App\Model\Settings;

class FinanceController extends Controller
{
    public function __construct() {
        //$this->middleware('auth');
    }
    public function saleFinancer_list(){
        $saledata = SaleFinance::leftJoin('sale', 'sale_finance_info.sale_id','sale.id')->get();
        $store = Store::whereIn('id',CustomHelpers::user_store())
                    ->select('id',DB::raw('concat(store.name,"-",store.store_type) as name'))->get();
        $company_name = FinanceCompany::select('id','company_name')->orderBy('id','ASC')->get();
        $check = CustomHelpers::getFinanceRate();
        if(!$check[0]){
            return back()->with('error',$check[1]);
        }
        $data = [
            'saledata'=>$saledata,
            'store'=>$store,
            'company_name'  =>  $company_name,
            'layout' => 'layouts.main'
        ];
        return view('admin.finance.sale_financer_list',$data);
    }

    public function SalefinancerList_api(Request $request) {
        $sale_date = $request->input('sale_date');
        $store_name = $request->input('store_name');
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;

        $tab = $request->input('tabVal');

        $saledate = SaleFinance::leftJoin('sale', 'sale_finance_info.sale_id','sale.id')->select('sale.sale_date')->get();
        $store = Store::whereIn('id',CustomHelpers::user_store())
                    ->select('id')->get()->toArray();
        $api_data = SaleFinance::leftJoin('sale', 'sale_finance_info.sale_id','sale.id')
            ->leftJoin('customer', 'sale.customer_id','customer.id')
            ->leftJoin('product', 'sale.product_id','product.id')
            ->leftJoin('store', 'store.id','sale.store_id')
            ->leftJoin('finance_company', 'finance_company.id','sale_finance_info.company')
            ->leftJoin('financier_executive', 'financier_executive.id','sale_finance_info.finance_executive_id')
            ->leftJoin('sale_finance_detail', 'sale_finance_detail.finance_id','sale_finance_info.id')
            ->whereIn('sale.sale_date',$saledate)
            ->whereIn('sale.store_id',$store)
            ->whereRaw('sale_finance_info.status <> 2')
            ->select(
                'sale_finance_info.id',
                'sale.sale_no',
                DB::raw('concat(store.name,"-",store.store_type) as store_name'),
                'sale.sale_date',
                'sale.customer_pay_type',
                'product.model_name',
                'product.model_variant',
                'customer.name as customer_name',
                'financier_executive.executive_name as name',
                'finance_company.company_name as company',
                'sale_finance_info.loan_amount',
                'sale_finance_info.do',
                // 'sale_finance_info.bank_name', 
                'sale_finance_info.disbursement_amount',
                'sale_finance_info.disbursement_date',
                'sale_finance_info.pending_disbursement_amount',
                DB::raw('sum(sale_finance_detail.intrest_amount) as sum_intrest_amount'),
                DB::raw('sum(sale_finance_detail.delay_day) as sum_delay_day')
            )->groupBy('sale_finance_info.id');
        if($tab == 'pending'){
            $api_data = $api_data->whereRaw('sale_finance_info.do > sale_finance_info.disbursement_amount');
        }elseif($tab == 'complete'){
            $api_data = $api_data->whereRaw('sale_finance_info.do <= sale_finance_info.disbursement_amount');
        }
            if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('sale.sale_no','like',"%".$serach_value."%")                  
                    ->orwhere('customer.name','like',"%".$serach_value."%")                  
                    ->orwhere('financier_executive.executive_name','like',"%".$serach_value."%")                  
                    ->orwhere('finance_company.company_name','like',"%".$serach_value."%")              
                    ->orwhere('sale_finance_info.do','like',"%".$serach_value."%")           
                    ->orwhere('sale_finance_info.disbursement_amount','like',"%".$serach_value."%")     
                    ->orwhere('sale_finance_info.disbursement_date','like',"%".$serach_value."%")       
                    ->orwhere('sale_finance_info.pending_disbursement_amount','like',"%".$serach_value."%")                  
                    ;
                });
            }
            if(!empty($sale_date)) {
               $api_data->where(function($query) use ($sale_date){
                        $query->where('sale.sale_date','like',"%".$sale_date."%");
                    });
            }
             if(!empty($store_name)) {
               $api_data->where(function($query) use ($store_name){
                        $query->where('sale.store_id','like',"%".$store_name."%");
                    });
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                'sale_finance_info.id',
                'sale.sale_no',
                'store_name',
                'financier_executive.executive_name',
                'finance_company.company_name',
                'sale_finance_info.do',
                // 'sale_finance_info.bank_name', 
                'sale_finance_info.disbursement_amount',
                'sale_finance_info.disbursement_date',
                'sale_finance_info.pending_disbursement_amount', 
                'customer.name',
                'sale.sale_date',

                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('sale_finance_info.id','desc');      
        
        $count = count($api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        $arr = CustomHelpers::getFinanceRate();
        $arr = $arr[1];
        foreach ($api_data as &$value) {
            if ($value['do'] != $value['disbursement_amount'] && $value['pending_disbursement_amount'] == 0) {
                $date1 = $value['sale_date'];
                $date2 = date("Y-m-d");
                $day = CustomHelpers::getDay($date1,$date2);
                $value['delay_day'] = CustomHelpers::getFinanceDay($day);
                $rate = $arr['interest'];
                $yday = $arr['yearDay'];
                $value['pending_interest'] = ROUND($value['do']*$value['delay_day']*$rate/$yday, 2);
                $value['total_interest'] = ROUND($value['do']*$value['delay_day']*$rate/$yday, 2);
            }else{
                $date1 = $value['sale_date'];
                $date2 = date("Y-m-d");
                $day = CustomHelpers::getDay($date1,$date2);
                $rate = $arr['interest'];
                $yday = $arr['yearDay'];
                $total_day = CustomHelpers::getFinanceDay($day);
                $value['delay_day'] =   0;
                if($total_day > 0){
                    $value['delay_day'] =  $total_day-$value['sum_delay_day'];
                }
                $value['pending_interest'] = ROUND(($value['pending_disbursement_amount']*$value['delay_day']*$rate/$yday), 2);
                $value['total_interest'] = ROUND(($value['pending_disbursement_amount']*$value['delay_day']*$rate/$yday)+$value['sum_intrest_amount'], 2);
            }
        }
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

    public function financer_list(){
        $data = [
            'layout' => 'layouts.main'
        ];
        return view('admin.finance.financer_list',$data);
    }

    public function financerList_api(Request $request) {
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;

        $api_data = SaleFinance::leftJoin('sale', 'sale_finance_info.sale_id','sale.id')
            ->leftJoin('customer', 'sale.customer_id','customer.id')
            ->leftJoin('finance_company', 'finance_company.id','sale_finance_info.company')
            ->leftJoin('financier_executive', 'financier_executive.id','sale_finance_info.finance_executive_id')
            ->leftJoin('product', 'sale.product_id','product.id')
            ->select(
                'sale_finance_info.id',
                 DB::raw('count(sale.id) as no_of_sale'),
                'sale.sale_date',
                'product.model_name',
                'product.model_variant',
                'customer.name as customer_name',
                'financier_executive.executive_name as name',
                'finance_company.company_name as company',
                // 'sale_finance_info.bank_name',
                'sale_finance_info.disbursement_amount',
                'sale_finance_info.disbursement_date',
                'sale_finance_info.pending_disbursement_amount', 
                 DB::raw('SUM(sale_finance_info.loan_amount) as total_amount'),
                 DB::raw('SUM(sale_finance_info.disbursement_amount) as totalDa'),
                 DB::raw('SUM(sale_finance_info.pending_disbursement_amount) as totalPd')
                )
                ->groupBy('sale_finance_info.company');

            if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('sale_finance_info.id','like',"%".$serach_value."%")                  
                    ->orwhere('finance_company.company_name','like',"%".$serach_value."%")                  
                    ->orwhere('sale.sale_date','like',"%".$serach_value."%")                  
                    ->orwhere('product.model_name','like',"%".$serach_value."%")                  
                    ->orwhere('product.model_variant','like',"%".$serach_value."%");
                    // ->orwhere(DB::raw('count(sale.id)'),'like',"%".$serach_value."%")
                    // ->orwhere(DB::raw('SUM(sale_finance_info.loan_amount)'),'like',"%".$serach_value."%")
                    // ->orwhere(DB::raw('SUM(sale_finance_info.disbursement_amount)'),'like',"%".$serach_value."%")
                    // ->orwhere(DB::raw('SUM(sale_finance_info.pending_disbursement_amount)'),'like',"%".$serach_value."%");
                });
            }

            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                'id',
                'company',
                'sale_date',
                'model_name',
                'model_variant',
                'no_of_sale',
                'total_amount',
                'totalDa',   
                'totalPd'
                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('sale_finance_info.id','desc');      
        
        $count = count($api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
       
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

    public function getDisbursementAmount($id) {
        $getamount = SaleFinance::where('id',$id)->select('id','disbursement_amount','disbursement_date','pending_disbursement_amount')->first();
       return response()->json($getamount);
    }

    public function getDisbursementAmount_update(Request $request,$id) {
        try {
            $get_setting_percent = Settings::where('name','TaAdjustmentInterestPercentage')
                                    ->select('value')->first();
            if(!isset($get_setting_percent->value)){
                return response()->json('Interest Percentage Master Not Found.',401);
            }
            $interest_percentage = $get_setting_percent->value;
            $getamount = SaleFinance::Join('sale','sale_finance_info.sale_id','sale.id')
                ->where('sale_finance_info.id',$id)->select('sale_finance_info.disbursement_amount',
                'sale_finance_info.do','sale_finance_info.id','sale_finance_info.company as finance_company_id',
                'sale_finance_info.loan_amount','sale.sale_date','sale_finance_info.pending_disbursement_amount','sale_finance_info.extra_amount')
                ->first();
            
            if(!isset($getamount->id)){
                return response()->json('Sale Finance Info Not Found.',401);
            }
            if($request->input('disb_amount') <= 0){
                return response()->json('Amount Should be greater than 0.',401);
            }
            if(!is_numeric($getamount['finance_company_id'])){
                return response()->json('Finance Company Not Found.',401);
            }
            $check = CustomHelpers::getFinanceRate();
            if(!$check[0]){
                return response()->json('Interest Percentage Master Not Found.',401);
            }
            $arr = $check[1];
            DB::beginTransaction();
            $total_amount = $getamount['disbursement_amount'] + $request->input('disb_amount');

            $pd = $getamount['do'] - $total_amount;
            $extra_amount = $getamount['extra_amount'];
            $today_ea = 0;
            //if pending disbursement gone in -ve then add that value in extra_amount and replace pending =0
            if($pd < 0){
                $today_ea = abs($pd);
                $extra_amount = $extra_amount+abs($pd);
                $pd = 0;
            }
            $interest_for_amount = empty($getamount['pending_disbursement_amount'])? $getamount['do'] : $getamount['pending_disbursement_amount'];

            
            $rate = $arr['interest'];
            $yday = $arr['yearDay'];

            $delayday = CustomHelpers::getDay($request->input('disb_date'),$getamount['sale_date']);

            $getfinance = FinanceDetail::where('finance_id',$id)->orderBy('id', 'desc')->select('id','disb_date')->first();
            
            // if ($getamount['do'] >= $total_amount) {
                $financeupdate = SaleFinance::where('id',$id)->update(
                [
                    'disbursement_amount' => $total_amount,
                    'pending_disbursement_amount' => $pd,
                    'extra_amount'  =>  $extra_amount,
                    'disbursement_date' => $request->input('disb_date')
                ]);
                $payment_type = $request->input('payment_type');
                if ((!empty($getfinance['id'])?($getfinance['id'] > 0) : false)) {

                        $dday = CustomHelpers::getDay($request->input('disb_date'),$getfinance['disb_date']);

                        $intrest_amount = CustomHelpers::Price($interest_for_amount*$rate*$dday/$yday);
                        $financedetail = FinanceDetail::insertGetId(
                        [
                            'finance_id' => $id,
                            'disb_amount' => $request->input('disb_amount'),
                            'disb_date' => $request->input('disb_date'),
                            'delay_day' => $dday,
                            'pending_disb_amount' => $pd,
                            'extra_amount'  =>  $today_ea,
                            'intrest_amount' => $intrest_amount,
                            'payment_into'  =>  $payment_type
                        ]
                        );
                        if(!$financedetail) {
                            DB::rollback();
                            return response()->json("Something Wen't Wrong.",401);
                        } 
                }else{
                        $dday = CustomHelpers::getFinanceDay($delayday);

                        $intrest_amount = CustomHelpers::Price($interest_for_amount*$rate*$dday/$yday);
                        $financedetail = FinanceDetail::insertGetId(
                        [
                            'finance_id' => $id,
                            'disb_amount' => $request->input('disb_amount'),
                            'disb_date' => $request->input('disb_date'),
                            'delay_day' => $dday,
                            'pending_disb_amount' => $pd,
                            'extra_amount'  =>  $today_ea,
                            'intrest_amount' => $intrest_amount,
                            'payment_into'  =>  $payment_type
                        ]
                        );
                        if(!$financedetail) {
                            DB::rollback();
                            return response()->json("Something Wen't Wrong.",401);
                        } 
                }

            // }else{
            //     return 'verror';
            // }
            $date =  $request->input('disb_date');
            $getLastBal = FinanceTaPayment::where('finance_company_id',$getamount->finance_company_id)
                                                    ->whereRaw('(select max(id) from finance_ta_payment where finance_company_id = '.$getamount->finance_company_id.') = id')
                                                    ->first();

            $req_amount = $request->input('disb_amount');
            $pending_req_amount = $req_amount;
            
            $finance_ta_pay = [
                'finance_company_id'    =>  $getamount->finance_company_id,
                'sale_finance_id'   =>  $getamount->id,
                'type'  =>  'DB',
                'payment_date'  =>  $date,
                'payment_into'  =>  'TA'
            ];
            $finance_company_info = FinanceCompany::where('id',$getamount->finance_company_id)
                                        ->first();
            if(!isset($finance_company_info->id)){
                DB::rollback();
                return response()->json('Finance Company Not Found.',401);
            }
            $debit_in_ta = 0;
            if($payment_type == 'TA'){
                if(isset($getLastBal->id)){
                    if($getLastBal->balance_amount > 0 && $getLastBal->balance_amount >= $pending_req_amount ){
                        // $balance = $getLastBal->balance_amount-$pending_req_amount;
                        // amount debit and update in ta payment pending_amount column       
                        $deb_res = $this->debitTaPayment($pending_req_amount,$finance_ta_pay,$getLastBal->balance_amount,$finance_company_info,0,$interest_percentage);
                        if(!$deb_res[0]){
                            DB::rollback();
                            return response()->json($deb_res[1],401);
                        }
                        $res = $this->reCalculateTaPayment($finance_ta_pay,$finance_company_info,[],$interest_percentage);
                        if(!$res[0]){
                            DB::rollback();
                            return response()->json($res[1],401);
                        }
                        $debit_in_ta = 1;
                        $pending_req_amount = 0;
                    }else{
                        DB::rollback();
                        return response()->json($pending_req_amount." Amount is to More. , So couldn't Debit Operation.",401);
                    }
                }else{
                    DB::rollback();
                    return response()->json('Requested Amount still not Available.',401);
                }
                
            }elseif($payment_type = 'Account'){
                // insert in ta_payment
                $finance_ta_pay['payment_into'] =   'Account';
                $finance_ta_pay['amount'] =   $req_amount;
                $finance_ta_pay['balance_amount'] =   0;
                if(isset($getLastBal->id)){
                  $finance_ta_pay['balance_amount'] =   $getLastBal->balance_amount;
                }
                $this->insert_ta_payment($finance_ta_pay,$finance_ta_pay['balance_amount']);

            }else{
                DB::rollback();
                return response()->json('Select Coorect Payment Into.',401);
            }
            
        }  catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return response()->json('some error occurred'.$ex->getMessage(),401);
        }
        /* Add action Log */
        CustomHelpers::userActionLog($action='Update Finance Disbursement Amount',$financedetail,0);
        if($payment_type == 'TA' && $debit_in_ta == 1){
            CustomHelpers::userActionLog($action='Debit TA Payment',0,0);
        }
        DB::commit();
        return response()->json([true,'Successfully Updated.']);
    }

    public function finance_details($id) {
        $financedata = SaleFinance::leftJoin('sale','sale.id','sale_finance_info.sale_id')
                            ->where('sale_finance_info.id',$id)
                            ->select('sale_finance_info.loan_amount','sale.sale_no')->first();
        $details = SaleFinance::leftJoin('sale', 'sale_finance_info.sale_id','sale.id')
            ->leftJoin('customer', 'sale.customer_id','customer.id')
            ->leftJoin('sale_finance_detail', 'sale_finance_detail.finance_id','sale_finance_info.id')
            ->leftJoin('finance_company', 'finance_company.id','sale_finance_info.company')
            ->leftJoin('financier_executive', 'financier_executive.id','sale_finance_info.finance_executive_id')
            ->where('sale_finance_detail.finance_id',$id)
            ->select(
                'sale_finance_info.id',
                'sale.sale_no',
                'sale.sale_date',
                'customer.name as customer_name',
                'financier_executive.executive_name as name',
                'finance_company.company_name as company',
                'sale_finance_info.loan_amount',
                // 'sale_finance_info.bank_name', 
                'sale_finance_info.disbursement_amount',
                'sale_finance_info.disbursement_date',
                'sale_finance_detail.disb_amount',
                'sale_finance_detail.pending_disb_amount',
                'sale_finance_detail.intrest_amount',
                'sale_finance_detail.delay_day',
                'sale_finance_info.pending_disbursement_amount',
                'sale_finance_info.loan_amount as total_amount'
            )->get();

         $data = [
            'financedata' => $financedata,
            'details'=>$details,
            'layout' => 'layouts.main'
        ];
        return view('admin.finance.financer_details',$data);

    }

    public function sale_finance_convert(Request $request){

        $sale_finance_info_id = $request->input('sale_finance_info_id');
        $convert = $request->input('convert');

        $this->validate($request,[
            'convert' => 'required',
            'finance_company_name' => 'required_if:convert,ChangeFinancer',
            'finance_name' => 'required_if:convert,ChangeFinancer',
            'loan_amount'=>'required_if:convert,ChangeFinancer'.(($convert == 'ChangeFinancer')? '|gt:0' : '' ),
            'do'=>'required_if:convert,ChangeFinancer'.(($convert == 'ChangeFinancer')? '|gt:0' : '' ),
            'dp'=>'required_if:convert,ChangeFinancer'.(($convert == 'ChangeFinancer')? '|gt:0' : '' ),
            'roi'=>'required_if:convert,ChangeFinancer',
            'payout'=>'required_if:convert,ChangeFinancer',
            'pf'=>'required_if:convert,ChangeFinancer',
            'emi'=>'required_if:convert,ChangeFinancer'.(($convert == 'ChangeFinancer')? '|gt:0' : '' ),
            'monthAddCharge'=>'required_if:convert,ChangeFinancer',
            'sd'=>'required_if:convert,ChangeFinancer'
        ],[
            'convert.required'=> 'This field is required.',
            'finance_company_name.required_if'=> 'This field is required.',
            'finance_name.required_if'=> 'This field is required.',
            'loan_amount.required_if'=> 'This field is required.',
            'loan_amount.gt'=> 'This Field must be greater than 0.',
            'do.required_if'=> 'This field is required.',
            'do.gt'=> 'This Field must be greater than 0.',
            'dp.required_if'=> 'This field is required.',
            'dp.gt'=> 'This Field must be greater than 0.',
            'roi.required_if'=> 'This field is required.',
            'payout.required_if'=> 'This field is required.',
            'pf.required_if'=> 'This field is required.',
            'emi.required_if'=> 'This field is required.',
            'emi.gt'=> 'This Field must be greater than 0.',
            'monthAddCharge.required_if'=> 'This field is required.',
            'sd.required_if'=> 'This field is required.',
        ]);

        try{
            DB::beginTransaction();

            $checkSaleFinance = SaleFinance::where('id',$sale_finance_info_id)->first();
            if(!isset($checkSaleFinance->sale_id)){
                return response()->json('Sale Finance Information Not Found.',401);
            }
            $sale_id = $checkSaleFinance->sale_id;
            $check_sale = Sale::where('id',$sale_id)->first();
            if(!isset($check_sale->id)){
                return response()->json('Sale Information Not Found.',401);
            }
            // when user choose option Convert to Cash
            if($convert == 'ConvertToCash'){

                $bal_amount = $checkSaleFinance->do-$check_sale->hypo;
                $new_balance = $check_sale->balance+$bal_amount;
                // update in sale
                $updateSale = Sale::where('id',$sale_id)
                                        ->update([
                                            'customer_pay_type' =>  'cash',
                                            'balance'   =>  $new_balance
                                        ]);
                // update status in sale finance info
                $update_sale_finance_info = SaleFinance::where('id',$sale_finance_info_id)
                                                        ->update([
                                                            'status'    =>  2
                                                        ]);
                
            }
            elseif($convert == 'ChangeFinancer')
            {
                // update in sale_finance_info
                $finance = array(
                    // 'name'  =>  $request->input('finance_name'),
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
                    'sd'  =>  $request->input('sd'),
                    'status'    =>  1

                );
                $updateSaleFinance = SaleFinance::where('id',$sale_finance_info_id)->update($finance);
                $new_sale_bal = ($check_sale->balance+$checkSaleFinance->do)-$request->input('do');
                $bal_amount = $checkSaleFinance->do-$request->input('do');
                // update in sale
                $updateSale = Sale::where('id',$sale_id)
                                            ->update([
                                                'balance'  =>  $new_sale_bal
                                            ]);
                // check rto 
                $check_rto = RtoModel::where('sale_id',$sale_id)->first();
                if(isset($check_rto->id)){
                    // case 1 :- If rto created and going to approve: then we will show message rto created.  And will update rto 
                    if($check_rto->approve == 0){
                        $updateRtoFinance = RtoModel::where('id',$check_rto->id)    
                                                            ->update([
                                                                'rto_finance' => $finance['company']
                                                            ]);
                    }

                    // case 2   :-  If rto approve and payment id not done: need reapproval for rto approval and
                                    //  for that payment option will not show before approval. 
                                    // At time of rto payment will show red mark that financier is changed and will not be able to do payment.
                    if($check_rto->approve == 1 && ($check_rto->rto_amount != $check_rto->amount)){
                        // reset approval in rto
                        $updateRtoApproval = RtoModel::where('id',$check_rto->id)
                                                        ->update([
                                                            'approve'   =>  0,
                                                            'rto_finance' => $finance['company']
                                                        ]);
                    }

                    // case 3 :- If payment is done: then we will do entry for rc correction. 
                                // Mistake by will be an old financier name. Amount is 1200
                    if($check_rto->rto_amount == $check_rto->amount){

                        $rcCorrectionReq = RcCorrectionRequest::insertGetId(
                            [
                                'rto_id'=>$check_rto->id,
                                'payment_amount'=> CustomHelpers::rcCorrectionAmount(),
                                'correction_reason'=>'Financer is not Approved',
                                'mistake_by'=>'financer',
                                'hypo'=> $finance['company'],
                                'status' => 'pending'
                            ]
                        );
                    }
                }
            }

            if($bal_amount > 0){
                // check approval any of pending
                $check_approval_req = ApprovalRequest::where('type','sale')
                    ->where('type_id',$sale_id)
                    ->where('status','Pending')->first();
                if(!isset($check_approval_req->id)){   // still not pending request any for sale
                        $insertPaymentReq = PaymentRequest::insertGetId([
                        'store_id'  =>  $check_sale->store_id,
                        'type'  =>  'sale',
                        'type_id'   =>  $sale_id,
                        'amount'    =>  $bal_amount
                    ]);
                }
            }

        }catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return response()->json('Something went wrong.'.$ex->getMessage(),401);
        }
        DB::commit();
        return response()->json('success');
    }

    // short and excess sale finance list

    public function saleFinancerShortExcess_list(){
        $saledata = SaleFinance::leftJoin('sale', 'sale_finance_info.sale_id','sale.id')->get();
        $store = Store::whereIn('id',CustomHelpers::user_store())
                    ->select('id',DB::raw('concat(store.name,"-",store.store_type) as name'))->get();
        $total_am = [];
        // total amount DO Amount in active tab
        $active = SaleFinance::whereRaw('disbursement_amount = 0')->where('status','<>',2)->sum('do');  
        $total_am['active'] = ['do' =>  $active];
        // total amount DO Amount, Received Amount ,Pending Amount in Short tab
        $short_data = SaleFinance::whereRaw('disbursement_amount > 0')
                                    ->where('status','<>',2)
                                    ->whereRaw('sale_finance_info.do > sale_finance_info.disbursement_amount')
                                    ->select(
                                        DB::raw('IFNULL(sum(do),0) as sum_do'),
                                        DB::raw('IFNULL(sum(disbursement_amount),0) as sum_da'),
                                        DB::raw('IFNULL(sum(pending_disbursement_amount),0) as sum_pda')
                                    )->first();
        $total_am['short']  =   [
                                        'do'    =>  $short_data->sum_do,
                                        'da'    =>  $short_data->sum_da,
                                        'pda'    =>  $short_data->sum_pda
        ];
        // total amount DO Amount, Received Amount in active tab
        $excess_data = SaleFinance::whereRaw('disbursement_amount > 0')
                                ->whereRaw('sale_finance_info.do < sale_finance_info.disbursement_amount')
                                ->where('status','<>',2)
                                    ->select(
                                        DB::raw('IFNULL(sum(do),0) as sum_do'),
                                        DB::raw('IFNULL(sum(disbursement_amount),0) as sum_da'),
                                        DB::raw('IFNULL(sum(pending_disbursement_amount),0) as sum_pda')
                                    )->first();
        $total_am['excess']  =   [
                                        'do'    =>  $excess_data->sum_do,
                                        'da'    =>  $excess_data->sum_da,
                                        'pda'    =>  $excess_data->sum_pda
        ];

        $data = [
            'saledata'=>$saledata,
            'store'=>$store,
            'total_am'  =>  $total_am,
            'layout' => 'layouts.main'
        ];
        return view('admin.finance.short_excess_list',$data);
    }

    public function saleFinancerShortExcessList_api(Request $request) {
        $sale_date = $request->input('sale_date');
        $store_name = $request->input('store_name');
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;

        $tab = $request->input('tabVal');

        $saledate = SaleFinance::leftJoin('sale', 'sale_finance_info.sale_id','sale.id')->select('sale.sale_date')->get();
        $store = Store::whereIn('id',CustomHelpers::user_store())
                    ->select('id')->get()->toArray();
        $api_data = SaleFinance::leftJoin('sale', 'sale_finance_info.sale_id','sale.id')
            ->leftJoin('customer', 'sale.customer_id','customer.id')
            ->leftJoin('product', 'sale.product_id','product.id')
            ->leftJoin('store', 'store.id','sale.store_id')
            ->leftJoin('sale_finance_detail', 'sale_finance_detail.finance_id','sale_finance_info.id')
            ->leftJoin('finance_company', 'finance_company.id','sale_finance_info.company')
            ->leftJoin('financier_executive', 'financier_executive.id','sale_finance_info.finance_executive_id')
            ->whereIn('sale.sale_date',$saledate)
            ->whereIn('sale.store_id',$store)
            ->whereRaw('sale_finance_info.status <> 2')
            ->select(
                'sale_finance_info.id',
                'sale.sale_no',
                DB::raw('concat(store.name,"-",store.store_type) as store_name'),
                'sale.sale_date',
                'sale.customer_pay_type',
                'product.model_name',
                'product.model_variant',
                'customer.name as customer_name',
                'financier_executive.executive_name as name',
                'finance_company.company_name as company',
                'sale_finance_info.loan_amount',
                'sale_finance_info.do',
                // 'sale_finance_info.bank_name', 
                'sale_finance_info.disbursement_amount',
                'sale_finance_info.disbursement_date',
                'sale_finance_info.pending_disbursement_amount',
                DB::raw('sum(sale_finance_detail.intrest_amount) as sum_intrest_amount'),
                DB::raw('sum(sale_finance_detail.delay_day) as sum_delay_day')
            )->groupBy('sale_finance_info.id');
        if($tab == 'short'){
            $api_data = $api_data->whereRaw('sale_finance_info.do > sale_finance_info.disbursement_amount')
                                    ->whereRaw('sale_finance_info.disbursement_amount > 0');
        }elseif($tab == 'excess'){
            $api_data = $api_data->whereRaw('sale_finance_info.do < sale_finance_info.disbursement_amount');
        }
        elseif($tab == 'active'){
            $api_data = $api_data->whereRaw('sale_finance_info.disbursement_amount = 0');
        }
            if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('sale.sale_no','like',"%".$serach_value."%")                  
                    ->orwhere('customer.name','like',"%".$serach_value."%")                  
                    ->orwhere('financier_executive.executive_name','like',"%".$serach_value."%")                  
                    ->orwhere('finance_company.company_name','like',"%".$serach_value."%")              
                    ->orwhere('sale_finance_info.do','like',"%".$serach_value."%")           
                    ->orwhere('sale_finance_info.disbursement_amount','like',"%".$serach_value."%")     
                    ->orwhere('sale_finance_info.disbursement_date','like',"%".$serach_value."%")       
                    ->orwhere('sale_finance_info.pending_disbursement_amount','like',"%".$serach_value."%")                  
                    ;
                });
            }
            if(!empty($sale_date)) {
               $api_data->where(function($query) use ($sale_date){
                        $query->where('sale.sale_date','like',"%".$sale_date."%");
                    });
            }
             if(!empty($store_name)) {
               $api_data->where(function($query) use ($store_name){
                        $query->where('sale.store_id','like',"%".$store_name."%");
                    });
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                'sale_finance_info.id',
                'sale.sale_no',
                'store_name',
                'financier_executive.executive_name',
                'finance_company.company_name',
                'sale_finance_info.do',
                // 'sale_finance_info.bank_name', 
                'sale_finance_info.disbursement_amount',
                'sale_finance_info.disbursement_date',
                'sale_finance_info.pending_disbursement_amount', 
                'customer.name',
                'sale.sale_date',

                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('sale_finance_info.id','desc');      
        
        $count = count($api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        // calculate total amount according to tab
        $total_amount_query =  SaleFinance::Join('sale','sale.id','sale_finance_info.sale_id')
                                ->where('sale_finance_info.status','<>',2);
        if(!empty($sale_date)) {
            $total_amount_query->where(function($query) use ($sale_date){
                $query->where('sale.sale_date','like',"%".$sale_date."%");
                });
        }
        if(!empty($store_name)) {
            $total_amount_query->where(function($query) use ($store_name){
                     $query->where('sale.store_id','like',"%".$store_name."%");
                 });
         }
        $total_am = [];
        if($tab == 'active'){
            // total amount DO Amount in active tab
            $active = $total_amount_query->whereRaw('disbursement_amount = 0')->sum('do');  
            $total_am['active'] = ['do' =>  $active];
        }elseif($tab == 'short'){
            // total amount DO Amount, Received Amount ,Pending Amount in Short tab
            $short_data = $total_amount_query->whereRaw('disbursement_amount > 0')
                                        ->whereRaw('sale_finance_info.do > sale_finance_info.disbursement_amount')
                                        ->select(
                                            DB::raw('IFNULL(sum(do),0) as sum_do'),
                                            DB::raw('IFNULL(sum(disbursement_amount),0) as sum_da'),
                                            DB::raw('IFNULL(sum(pending_disbursement_amount),0) as sum_pda')
                                        )->first();
            $total_am['short']  =   [
                                            'do'    =>  $short_data->sum_do,
                                            'da'    =>  $short_data->sum_da,
                                            'pda'    =>  $short_data->sum_pda
            ];
        }elseif($tab == 'excess'){
            // total amount DO Amount, Received Amount in active tab
            $excess_data = $total_amount_query->whereRaw('disbursement_amount > 0')
                                    ->whereRaw('sale_finance_info.do < sale_finance_info.disbursement_amount')
                                        ->select(
                                            DB::raw('IFNULL(sum(do),0) as sum_do'),
                                            DB::raw('IFNULL(sum(disbursement_amount),0) as sum_da'),
                                            DB::raw('IFNULL(sum(pending_disbursement_amount),0) as sum_pda')
                                        )->first();
            $total_am['excess']  =   [
                                            'do'    =>  $excess_data->sum_do,
                                            'da'    =>  $excess_data->sum_da,
                                            'pda'    =>  $excess_data->sum_pda
            ];
        }
        $arr = CustomHelpers::getFinanceRate();
        $arr = $arr[1];
        foreach ($api_data as &$value) {
            if ($value['do'] != $value['disbursement_amount'] && $value['pending_disbursement_amount'] == 0) {
                $date1 = $value['sale_date'];
                $date2 = date("Y-m-d");
                $day = CustomHelpers::getDay($date1,$date2);
                $value['delay_day'] = CustomHelpers::getFinanceDay($day);
                $rate = $arr['interest'];
                $yday = $arr['yearDay'];
                $value['interest'] = ROUND($value['do']*$value['delay_day']*$rate/$yday, 2);
            }else{
                $date1 = $value['sale_date'];
                $date2 = date("Y-m-d");
                $day = CustomHelpers::getDay($date1,$date2);
                $rate = $arr['interest'];
                $yday = $arr['yearDay'];
                $value['delay_day'] =  $day-$value['sum_delay_day'];
                $value['interest'] = ROUND(($value['pending_disbursement_amount']*$value['delay_day']*$rate/$yday)+$value['sum_intrest_amount'], 2);
            }
            $value['total_am'] = $total_am;
        }
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

    // Finance Ta Adjusment
    public function ta_adjustment(Request $request){
        $finance_company_name = FinanceCompany::orderBy('id')->get();
        $sub_dealer = SubDealer::orderBy('id')->get();
        $sale_no = Sale::where('customer_pay_type','finance')
                        ->join('sale_finance_info','sale_finance_info.sale_id','sale.id')
                        ->leftJoin('finance_ta_adjustment','finance_ta_adjustment.sale_finance_id','sale_finance_info.id')
                        ->whereRaw('sale_finance_info.status <> 2')
                        ->whereNull('finance_ta_adjustment.sale_finance_id')
                        ->select(
                            'sale_finance_info.id as sale_finance_id',
                            'sale.sale_no',
                            'sale.id as sale_id'
                        )
                        ->get();
        $data = [
            'finance_company'   =>  $finance_company_name,
            'sub_dealer'    =>  $sub_dealer,
            'sale_no'   =>  $sale_no,
            'layout' => 'layouts.main'
        ];
        return view('admin.finance.ta_adjustment',$data);
    }
    public function ta_adjustmentDB(Request $request){
        $adjustment = $request->input('adjustment_type');
        // echo $adjustment;die;
        $validator = Validator::make($request->all(),[
            'adjustment_type'   =>  'required',
            'company_name'  =>  'required',
            'date'  =>  'required',
            'interest_amount'  =>  'required_if:adjustment,interest'.((in_array($adjustment,['interest'])) ?'|numeric|min:0|not_in:0' : ''),
            'payment_into'  =>  'required_if:adjustment,payout,interest',
            'sale_finance_id'  =>  'required_if:adjustment,payout',
            'amount'  =>  'required_if:adjustment,trans,sub_dealer,other,payout'.((in_array($adjustment,['trans','sub_dealer','other','payout'])) ?'|numeric|min:0|not_in:0' : '') ,
            'from_date'  =>  'required_if:adjustment,interest,payout'.((in_array($adjustment,['interest','payout'])) ?'|date' : '') ,
            'to_date'  =>  'required_if:adjustment,interest,payout'.((in_array($adjustment,['interest','payout'])) ?'|date|after_or_equal:from_date' : '') ,
            'sub_dealer_name'  =>  'required_if:adjustment,sub_dealer',
            'amount_type'  =>  'required_if:adjustment,other',
            'remark'  =>  'required_if:adjustment,other'
        ],
        [
            'adjustment_type.required'  =>  'This Field is required',
            'company_name.required'  =>  'This Field is required',
            'date.required'  =>  'This Field is required',
            'sale_finance_id.required_if'  =>  'This Field is required',
            'interest_amount.required_if'  =>  'This Field is required',
            'payment_into.required_if'  =>  'This Field is required',
            'amount.required_unless'  =>  'This Field is required',
            'from_date.required_if'  =>  'This Field is required',
            'to_date.required_if'  =>  'This Field is required',
            'sub_dealer_name.required_if'  =>  'This Field is required',
            'amount_type.required_if'  =>  'This Field is required',
            'remark.required_if'  =>  'This Field is required',
        ]);
        $data['from'] = $request->input('from_date');
        $data['to'] = $request->input('to_date');
        $data['at'] = $request->input('adjustment_type');
        $data['fci'] = $request->input('company_name');
        $data['ia'] = $request->input('interest_amount');
        $data['a'] = $request->input('amount');
        $validator->after(function ($validator) use($data) {
            if ($data['at'] == 'interest') {
                if($data['ia'] <= 0)
                {
                    $validator->errors()->add('interest_amount', 'Interest Amount Should be greater than zero.');
                }
                // else{
                //     $sumofinterest = FinanceTaAdjustmentInterest::where('finance_company_id',$data['fci'])
                //                                             ->whereBetween('date',[$data['from'],$data['to']])
                //                                             ->where('give',0)
                //                                             ->sum('interest_amount');
                //     if($sumofinterest != $data['ia']){
                //         $validator->errors()->add('interest_amount', 'Interest Amount Should be equal to '.$sumofinterest.'.');
                //     }
                // }
            }else{
                if($data['a'] <= 0)
                {
                    $validator->errors()->add('amount', 'Amount Should be greater than zero.');
                }
            }
        });
        $info_msg = '';
        if($data['at'] == 'interest'){
            $sumofinterest = FinanceTaAdjustmentInterest::where('finance_company_id',$data['fci'])
                                                            ->whereBetween('date',[$data['from'],$data['to']])
                                                            ->where('give',0)
                                                            ->sum('interest_amount');
            if($sumofinterest != $data['ia']){
                $info_msg = 'There is some difference in interest amount According to Our Calculation Interest Amount '.$sumofinterest.'.';
            }
        }
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        try
        {
            $table = array('finance_ta_adjustment','finance_ta_payment');
            CustomHelpers::resetIncrement($table);
            $get_setting_percent = Settings::where('name','TaAdjustmentInterestPercentage')
                                    ->select('value')->first();
            if(!isset($get_setting_percent->value)){
                return back()->with('error','Interest Percentage Master Not Found.')->withInput();
            }
            $interest_percentage = $get_setting_percent->value;
            DB::beginTransaction();
            $adjustment_type    =   $request->input('adjustment_type');    
            $company_name   =   $request->input('company_name');   
            $date   =   $request->input('date');   
            $interest_amount    =   $request->input('interest_amount');    
            $amount =   $request->input('amount'); 
            $from_date  =   $request->input('from_date');  
            $to_date    =   $request->input('to_date');    
            $sub_dealer_name    =   $request->input('sub_dealer_name');    
            $amount_type    =   $request->input('amount_type');    
            $remark =   $request->input('remark'); 
            $payment_into =   $request->input('payment_into'); 
            $sale_finance_id =   $request->input('sale_finance_id'); 

            $items = $request->input('items');
            $present_order = 0;
            if(in_array($adjustment_type,['trans','interest']) && isset($items[0])){
                foreach($items as $k => $v){
                    if($v != 'present'){
                        $order = $k+1;
                        $ta_adjust_id = $v;
                        FinanceTaAdjustment::where('id',$ta_adjust_id)
                                                ->update(['orderby' =>  $order]);
                    }else{
                        $present_order = $k+1;
                    }
                }
            }

            $insertData = [
                'finance_company_id'    =>  $company_name,
                'amount'    =>  $amount,
                'pending_amount'    =>  $amount,
                'date'  =>  $date,
                'payment_into'  =>  'TA'
            ];
            if($adjustment_type == 'trans'){
                $insertData['type'] = 'Trans';
                $insertData['amount_type'] = 'CR';
                $insertData['orderby'] = $present_order;
            }elseif($adjustment_type == 'interest'){
                $insertData['type'] = 'Interest';
                $insertData['amount'] = $interest_amount;
                $insertData['pending_amount'] = $interest_amount;
                $insertData['interest_period_from'] = $from_date;
                $insertData['interest_period_to'] = $to_date;
                $insertData['amount_type'] = 'CR';
                $insertData['payment_into'] = $payment_into;
                $insertData['orderby'] = $present_order;
            }elseif($adjustment_type == 'sub_dealer'){
                $insertData['type'] = 'SubDealer';
                $insertData['sub_dealer_id'] = $sub_dealer_name;
                $insertData['amount_type'] = 'DB';
            }elseif($adjustment_type == 'other'){
                $insertData['type'] = 'Others';
                $insertData['amount_type'] = $amount_type;
                $insertData['remark'] = $remark;
            }elseif($adjustment_type == 'payout'){
                $insertData['type'] = 'PayOut';
                $insertData['interest_period_from'] = $from_date;
                $insertData['sale_finance_id'] = $sale_finance_id;
                $insertData['interest_period_to'] = $to_date;
                $insertData['amount_type'] = 'DB';
                $insertData['payment_into'] = $payment_into;
            }
            // insert in finance_ta_adjustment
            $insert_adjustment = FinanceTaAdjustment::insertGetId($insertData);
            if(!$insert_adjustment){
                DB::rollback();
                return back()->input('error','Some Error Occurred.')->withInput();
            }

            // insert in finance_ta_payment
            $finance_ta_pay_data = [
                'finance_company_id'    =>  $company_name,
                'type'  =>  $insertData['amount_type'],
                'amount'    =>  $insertData['amount'],
                'payment_date'  =>  $insertData['date'],
                'payment_into'  =>  $insertData['payment_into'],
                'ta_adjustment_id'  =>  $insert_adjustment
            ];
            $prev_bal = FinanceTaPayment::where('finance_company_id',$company_name)
                                        ->whereRaw('id = (select max(id) from finance_ta_payment where finance_company_id = '.$company_name.')')
                                        // ->orderBy('date')
                                        ->first();
            $finance_company_info = FinanceCompany::where('id',$company_name)
                                        ->first();
            if(!isset($finance_company_info->id)){
                DB::rollback();
                return back()->with('Finance Company Not Found.')->withInput();
            }
            $balance = 0; 
            if(isset($prev_bal->id)){
                if($insertData['payment_into'] == 'TA'){   // if payment_into is 'Account' then in ta_payment balance_amount shouldn't change. 
                    if($insertData['amount_type'] == 'CR'){
                        $balance = $prev_bal->balance_amount+$insertData['amount'];
                        $this->insert_ta_payment($finance_ta_pay_data,$balance);
                        $res = $this->reCalculateTaPayment($finance_ta_pay_data,$finance_company_info,$insertData,$interest_percentage);
                        if(!$res[0]){
                            DB::rollback();
                            return back()->with('error',$res[1])->withInput();
                        }
                        // if(in_array($insertData['type'],['Trans','Interest'])){
                        //     // print_r($res);die;
                        // }
                    }elseif($insertData['amount_type'] == 'DB'){
                        if($prev_bal->balance_amount > 0 && $prev_bal->balance_amount >= $insertData['amount'] ){
                            // $balance = $prev_bal->balance_amount-$insertData['amount'];
                            // amount debit and update in ta payment pending_amount column
                            $deb_res = $this->debitTaPayment($insertData['amount'],$finance_ta_pay_data,$prev_bal->balance_amount,$finance_company_info,0,$interest_percentage);
                            if(!$deb_res[0]){
                                DB::rollback();
                                return back()->with('error',$deb_res[1])->withInput();
                            }
                            $res = $this->reCalculateTaPayment($finance_ta_pay_data,$finance_company_info,[],$interest_percentage);
                            if(!$res[0]){
                                DB::rollback();
                                return back()->with('error',$res[1])->withInput();
                            }
                        }else{
                            DB::rollback();
                            return back()->with('error',$insertData['amount']." Amount is to More. , So couldn't Debit Operation.")->withInput();
                        }
                    }else{
                        DB::rollback();
                        return back()->with('error',"Some Error Occured.")->withInput();
                    }
                }else{
                    $balance = $prev_bal->balance_amount;
                    $this->insert_ta_payment($finance_ta_pay_data,$balance);
                }
            }else{   //when first time credit for finance company
                if($insertData['amount_type'] == 'CR'){
                    $balance = $insertData['amount'];
                    $this->insert_ta_payment($finance_ta_pay_data,$balance);
                }elseif($insertData['amount_type'] == 'DB'){
                    DB::rollback();
                    return back()->with('error',"Balance Amount is Nill, So couldn't Debit Operation.")->withInput();
                }
            }

        }
        catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return back()->with('error','some error occurred'.$ex->getMessage())->withInput();
        }
        /* Add action Log */
        CustomHelpers::userActionLog($action='Create Finance Ta Adjustment',$insert_adjustment,0);
        DB::commit();
        return back()->with('success','Successfully Submitted. '.$info_msg);
    }
    // get data when select date of trans
    public function ta_getData(Request $request){
        $date = $request->input('date');
        $company_id = $request->input('company_name');

        $check = FinanceTaAdjustment::where('date',$date)->where('amount_type','CR')
                        ->where('finance_company_id',$company_id)
                        ->leftJoin('finance_company','finance_company.id','finance_ta_adjustment.finance_company_id')
                    ->select(
                        'finance_ta_adjustment.id',
                        'finance_company.company_name',
                        'finance_ta_adjustment.amount'
                    )
                    ->get();
        if(isset($check[0])){
            return response()->json($check);
        }
        return response()->json(['not-found']);
    }
    // amount Debit into ta_adjustment
    public function debitTaPayment($req_amount,$finance_ta_pay_data,$balance,$finance_company_info,$interest_calculation=0,$interest_percentage){
        $amount = $req_amount;
        $adjustment_data = [];
        while($amount != 0){
            $getfirst = FinanceTaAdjustment::where('finance_company_id',$finance_ta_pay_data['finance_company_id'])
                                            ->where('amount_type','CR')
                                            ->where('payment_into','TA')
                                            ->whereIn('type',['Trans','Interest'])
                                            ->whereRaw('pending_amount > 0')
                                            ->where('date','<=',$finance_ta_pay_data['payment_date'])
                                            ->orderBy('date','ASC')
                                            ->orderBy('orderby','ASC')
                                            ->first();
            array_push($adjustment_data,$getfirst);
            
            if(!isset($getfirst->id)){
                return array(false,'Debit Amount not available till given date.');
            }
            $pending_amount = 0;$ta_pay_amount = 0;
            if($amount <= $getfirst->pending_amount){
                // for ta payment
                $balance = $balance-$amount;
                $ta_pay_amount = $amount;
                // for ta adjustment
                $pending_amount = $getfirst->pending_amount-$amount;
                $amount = 0;
            }else{
                // for ta payment
                $balance = $balance-$getfirst->pending_amount;
                $ta_pay_amount = $getfirst->pending_amount;
                //for ta adjustment
                $amount = $amount-$getfirst->pending_amount;
                $pending_amount = 0;
            }
            // insert in ta payment table
            $finance_ta_pay_data['amount'] = $ta_pay_amount;
            $this->insert_ta_payment($finance_ta_pay_data,$balance,$getfirst->id);

            // update in ta adjustment
            $update = FinanceTaAdjustment::where('id',$getfirst->id)
                        ->update(['pending_amount' => $pending_amount]);
            
            if($interest_calculation == 1){
                $interest_cal = $this->calculate_interest($finance_ta_pay_data,$finance_company_info,$getfirst,$interest_percentage);
                if(!$interest_cal[0]){
                    return $interest_cal;
                }
            }
        }
        if($interest_calculation == 1){
            foreach($adjustment_data as $key => $val){
                if($val->type == 'Interest'){
                    $from = $val->interest_period_from;
                    $to = $val->interest_period_to;
                    // update in ta_adjustment_interest
                    FinanceTaAdjustmentInterest::where('date','>=',$from)
                    ->where('date','<=',$to)
                    ->where('finance_company_id',$val->finance_company_id)
                    ->update(['give' => 1]);
                }
            }
        }
        return array(true);
    }
    // interest calculate only for debit case
    public function calculate_interest($pay_data,$finance_company_info,$credit_data,$interest_percentage){
        
        $interest_after_days = $finance_company_info->interest_after_days;
        $credit_date = $credit_data->date;
        $pay_date = $pay_data['payment_date'];

        $diff_date = CustomHelpers::getDay($pay_date,$credit_date);
        $exceed_days = $diff_date-$interest_after_days;    // if gretaer 0 then exceed otherwise not exceed
        if($exceed_days < 0){
            return [true];
        }

        $check = FinanceTaAdjustmentInterest::where('finance_company_id',$finance_company_info->id)
                                                ->where('ta_adjustment_id',$credit_data->id)
                                                ->where('date','<=',$pay_data['payment_date'])
                                                ->orderBy('date','DESC')->limit(2)
                                                ->get();

        $start_date = date('Y-m-d',strtotime('+'.$interest_after_days.' days', strtotime($credit_date)));
        $end_date = $pay_date;
        // print_r($check->toArray());die;
        // $prev_interest = 0;
        if(!isset($check[0])){
            // call function, in for loop by date insert it
        }else{
            if(strtotime($check[0]->date) == strtotime($pay_date)){  // if payment date data already exist in ta_adjustment_interest 
                if(isset($check[1])){
                    $start_date = $check[1]->date;
                    // $end_date = $pay_date;
                }
                // $prev_interest = $check[0]->interest_amount;
            }else{
                $start_date = $check[0]->date;
                // $end_date = $pay_date;
            }
        }
        // calculate new interest start date to end date and save it.
        $new_date_diff = CustomHelpers::getDay($end_date,$start_date);

        for($i = 0; $i < $new_date_diff; $i++){
            $thisdate = date('Y-m-d',strtotime('+'.($i).' days',strtotime($start_date)));
            $getTotalDebitAmount = FinanceTaPayment::where('finance_company_id',$finance_company_info->id)
                                ->where('adjusted_id',$credit_data->id)
                                ->where('payment_date','<=',$thisdate)
                                ->where('payment_into','TA')
                                ->where('type','DB')->sum('amount');

            $amount = $credit_data->amount-$getTotalDebitAmount;
            $interest = $interest_percentage;  // 13%
            $year_days = CustomHelpers::getDayInYear();
            
            $interest_amount = $amount*$interest*1/$year_days;

            $data = [
                'finance_company_id'    =>  $finance_company_info->id,
                'ta_adjustment_id'  =>  $credit_data->id,
                'interest_amount'   =>  round($interest_amount),
                'date'  =>  $thisdate
            ];
            $checkData = FinanceTaAdjustmentInterest::where('finance_company_id',$finance_company_info->id)
                                    ->where('ta_adjustment_id',$credit_data->id)
                                    ->where('date',$data['date'])               
                                    ->first();
            // update
            if(isset($checkData->id)){
                $data['interest_amount'] = round($interest_amount);
                $updateInterest = FinanceTaAdjustmentInterest::where('id',$checkData->id)
                                                        ->update($data);
            }else{   // insert
                FinanceTaAdjustmentInterest::insertGetId($data);
            }
        }
        return [true];

    } 
    // insert in ta_payment
    public function insert_ta_payment($data,$bal,$adjusted_id=0){
        $data['balance_amount'] = $bal;
        $data['adjusted_id'] = $adjusted_id;
        $insert_payment = FinanceTaPayment::insertGetId($data);
        if(!$insert_payment){
            return [false,"Something  wen't wrong, Finance Ta Payment Can't be Inserted."];
        }
        return [true,$insert_payment];
    }
    // reset calculation in ta payment 
    public function reCalculateTaPayment($insertData,$finance_company_info,$taAdjustmentData=[],$interest_percentage){
        $getdata = FinanceTaPayment::where('finance_company_id',$insertData['finance_company_id'])
                                        ->whereRaw('payment_date >= "'.$insertData['payment_date'].'"')
                                        // ->where('payment_into','TA')->where('type','DB')
                                        ->orderBy('payment_date','ASC')
                                        ->get();
                                       
        if(!isset($getdata[0])){
            return [1];
        }
        $all_id = [];
        foreach($getdata as $k => $v){
            array_push($all_id,$v->id);
        }
        // get and delete interest data
        $deleteInterest = FinanceTaAdjustmentInterest::where('finance_company_id',$finance_company_info->id)
                                                ->where('date','>=',$insertData['payment_date'])
                                                ->delete();
        // print_r($total_db);die;
        // delete all ta_pay_id
        FinanceTaPayment::whereIn('id',$all_id)->delete();

        // after delete reset autoincrement ids
        $arr = ['finance_ta_adjustment','finance_ta_payment','ta_adjustment_interest'];
        CustomHelpers::resetIncrement($arr);

        // get last balance amount in ta_payment  after delete id's
        $last_amount_arr = FinanceTaPayment::where('finance_company_id',$insertData['finance_company_id'])
                                ->orderBy('id','DESC')->first();
             
        $last_amount = 0;   // last balance_amount in ta_payment
        if(isset($last_amount_arr->id)){
            $last_amount = $last_amount_arr->balance_amount;
        }
        // reset pending payment in ta_adjustment
        $recover = $this->RecoverTaPayment($getdata,$last_amount,$finance_company_info,$interest_percentage);

        if(!$recover[0]){
            return $recover;
        }

        // if type is interest and amount type CR and payment_into is TA
        if(count($taAdjustmentData) > 1){
            if($taAdjustmentData['type'] == 'Interest'){
                $from = $taAdjustmentData['interest_period_from'];
                $to = $taAdjustmentData['interest_period_to'];
                // update in ta_adjustment_interest
                FinanceTaAdjustmentInterest::where('date','>=',$from)
                ->where('date','<=',$to)
                ->where('finance_company_id',$taAdjustmentData['finance_company_id'])
                ->update(['give' => 1]);
            }
        }

        return $recover;
    }
    public function RecoverTaPayment($payData,$last_amount,$finance_company_info,$interest_percentage){
        // reset all pending amount in ta_adjustment according adjusted_id
        foreach($payData as $k => $v){
            // when type is Debit and payment_into TA then update pending_amount in ta_adjustment.
            if($v->type == 'DB' && $v->payment_into == 'TA'){
                $adjusted_id = $v->adjusted_id;
                $recoverUpdate = FinanceTaAdjustment::where('id',$adjusted_id)
                                                ->increment('pending_amount',$v->amount);
                if(!$recoverUpdate){
                    return array(false,"Something Wen't Wrong, Ta Adjustment Data Can't Update.");
                }
            }
        }
        // this loop for insert in ta_payment sequentially
        foreach($payData as $k => $v){
            $finance_ta_pay_data = [
                'finance_company_id'    =>  $v->finance_company_id,
                'ta_adjustment_id'  =>  $v->ta_adjustment_id,
                'sale_finance_id'   =>  $v->sale_finance_id,
                'type'  =>  $v->type,
                'amount'    =>  $v->amount,
                'payment_date'  =>  $v->payment_date,
                'payment_into'  =>  $v->payment_into,
                'created_at'    =>  $v->created_at
            ];
            // when type is Debit and payment_into TA then insert in ta_payment.
            if($v->type == 'DB' && $v->payment_into == 'TA'){
                $db_pay = $this->debitTaPayment($v->amount,$finance_ta_pay_data,$last_amount,$finance_company_info,1,$interest_percentage);
                if(!$db_pay[0]){
                    return $db_pay;
                }
                $last_amount = $last_amount-$v->amount;
            }
            elseif(($v->type == 'DB' || $v->type == 'CR') && $v->payment_into == 'Account'){
                // when type is Debit and payment_into is Account then only insert in ta_payment 
                $db_pay = $this->insert_ta_payment($finance_ta_pay_data,$last_amount);
                if(!$db_pay[0]){
                    return $db_pay;
                }
            }
            elseif($v->type == 'CR' && $v->payment_into == 'TA'){
                // when type is Credit and payment_into is TA then only insert in ta_payment 
                $last_amount = $last_amount+$finance_ta_pay_data['amount'];
                $cr_pay = $this->insert_ta_payment($finance_ta_pay_data,$last_amount);
                if(!$cr_pay[0]){
                    return $cr_pay;
                }
            }
        }
        return [true];
    }
    // fincance payout listing
    public function finance_payout_list(){
        $data = [
            'layout' => 'layouts.main'
        ];
        return view('admin.finance.finance_payout_list',$data);
    }

    public function financePayOutList_api(Request $request) {
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;

        $api_data = Sale::where('sale.customer_pay_type','finance')
                    ->join('sale_finance_info','sale_finance_info.sale_id','sale.id')
                    ->leftJoin('finance_company','finance_company.id','sale_finance_info.company')
                    ->leftJoin('financier_executive','financier_executive.id','sale_finance_info.finance_executive_id')
                    ->leftJoin('finance_ta_adjustment','finance_ta_adjustment.sale_finance_id','sale_finance_info.id')
                    ->whereRaw('sale_finance_info.status <> 2')
                    // ->whereRaw('sale_finance_info.pending_disbursement_amount > 0')
                    ->select(
                        'sale_finance_info.id as id',
                        'sale.sale_no',
                        'finance_company.company_name',
                        'financier_executive.executive_name',
                        'sale_finance_info.payout as actual_payout',
                        'finance_company.payout as theoretical_payout',
                        DB::raw('IFNULL(finance_ta_adjustment.amount,0) as receipt_payout')
                    );

            if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('sale.sale_no','like',"%".$serach_value."%")                  
                    ->orwhere('finance_company.company_name','like',"%".$serach_value."%")
                    ->orwhere('financier_executive.executive_name','like',"%".$serach_value."%")
                    ->orwhere('sale_finance_info.payout','like',"%".$serach_value."%")
                    ->orwhere('finance_company.payout','like',"%".$serach_value."%")
                    ->orwhere('finance_ta_adjustment.amount','like',"%".$serach_value."%");
                });
            }

            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                    'sale.sale_no',
                    'finance_company.company_name',
                    'financier_executive.executive_name',
                    'sale_finance_info.payout',
                    'finance_company.payout',
                    'finance_ta_adjustment.amount'
                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('sale_finance_info.id','desc');      
        
        $count = $api_data->count();
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
       
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

    // fincance payout listing
    public function finance_interest_list(){
        $data = [
            'layout' => 'layouts.main'
        ];
        return view('admin.finance.finance_interest_list',$data);
    }

    public function financeInterestList_api(Request $request) {
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;

        $check = CustomHelpers::getFinanceRate();
        $arr = $check[1];
        $discountDay = CustomHelpers::getDiscountday();
        // DB::enableQueryLog();
        // make query for get take column values
        $statement = DB::statement(DB::raw("set @rate = ".$arr['interest']));
        $statement = DB::statement(DB::raw("set @dis_day = ".$discountDay));
        $statement = DB::statement(DB::raw("set @year_day = ".$arr['yearDay']));
        $statement = DB::statement(DB::raw("set @delay_day = 0"));
        $statement = DB::statement(DB::raw("set @total_day = 0"));
        $statement = DB::statement(DB::raw("set @old_delay_day = 0"));
        $statement = DB::statement(DB::raw("set @old_interest_amount = 0"));
        $statement = DB::statement(DB::raw("set @pda = 0"));
        $statement = DB::statement(DB::raw("set @dis_day1 = 0"));

        
        $query_for_interest = "select sum(interest.total_day),sum(interest.old_delay_day),sum(interest.delay_day),
            sum(interest.old_interest_amount),sum(interest.pda),sum(interest.total_interest_amount) as total_interest_amount,
            interest.company_id,sum(interest.disbursement_amount)	
        from (
            SELECT 
                sfi.id as sale_finance_id,
                sfi.sale_id,
                (@total_day:=DATEDIFF(CURRENT_DATE,sale.sale_date)-@dis_day) as total_day,
                (@old_delay_day:=IFNULL((select sum(delay_day) from sale_finance_detail where sale_finance_detail.finance_id = sfi.id),0) ) as old_delay_day,
                @delay_day:=IF( @total_day>0 , @total_day-@old_delay_day , 0) as delay_day,
                (@old_interest_amount:=IFNULL((select sum(intrest_amount) from sale_finance_detail where sale_finance_detail.finance_id = sfi.id),0) ) as old_interest_amount,
                (@pda:=IF(sfi.pending_disbursement_amount = 0, sfi.do, sfi.pending_disbursement_amount) ) as pda,
                round(((@pda * @delay_day * @rate / @year_day)+ @old_interest_amount),2) as total_interest_amount,
                sfi.name as executive_id,
                sfi.company as company_id,
                sfi.disbursement_amount
                
            FROM `sale_finance_info` sfi 
                    join sale on sale.id = sfi.sale_id
                where sfi.status <> 2
            order by sfi.company
        )  interest
        group by interest.company_id";
        
        // make query for get give column values
        $query_for_ta_interest = "select sum(total_day),sum(old_delay_day),sum(delay_day),
            sum(old_interest_amount),sum(pending_amount),sum(total_interest_amount) as total_interest_amount,
            ta_interest.finance_company_id
            from (
            SELECT 
                fta.id as ta_adjustment_id,
                fta.finance_company_id,
                (@dis_day1:=fc.interest_after_days) as discount_day,
                (@total_day:=DATEDIFF(CURRENT_DATE,fta.date)-@dis_day1) as total_day,
                (@old_delay_day:=(select count(sub_tai.date) from ta_adjustment_interest sub_tai where sub_tai.ta_adjustment_id = fta.id and sub_tai.finance_company_id = fc.id) ) as old_delay_day,
                (@delay_day:=IF( @total_day>0 , @total_day-@old_delay_day , 0)) as delay_day,
                (@old_interest_amount:=IFNULL((select sum(sub_tai.interest_amount) from ta_adjustment_interest sub_tai where sub_tai.ta_adjustment_id = fta.id and sub_tai.finance_company_id = fc.id),0) ) as old_interest_amount,
                (@pa:= fta.pending_amount ) as pending_amount,
                round(((@pa * @delay_day * @rate / @year_day)+ @old_interest_amount ),2) as total_interest_amount
                
            FROM `finance_ta_adjustment` fta
                    join finance_company fc on fta.finance_company_id = fc.id 
                where fta.type in ('Trans','Interest')
                GROUP BY fta.id
            order by fta.finance_company_id
                ) ta_interest
                group by ta_interest.finance_company_id";
        
        $api_data = FinanceCompany::
                    leftjoin(DB::raw("(".$query_for_interest.") interest"),function($join){
                        $join->on("interest.company_id","=","finance_company.id");
                    })
                    ->leftjoin(DB::raw("(".$query_for_ta_interest.") ta_interest"),function($join){
                        $join->on("ta_interest.finance_company_id","=","finance_company.id");
                    })
                    ->select(
                        'finance_company.id',
                        'finance_company.company_name',
                        DB::raw('IFNULL(interest.total_interest_amount,0) as take'),
                        DB::raw('IFNULL(ta_interest.total_interest_amount,0) as give'),
                        // DB::raw("IFNULL((select interest.total_interest_amount from (".$query_for_interest.") interest where interest.company_id = finance_company.id),0) as take"),
                        DB::raw("IFNULL(ta_interest.total_interest_amount-interest.total_interest_amount,0) as actual")
                    );
            if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('finance_company.company_name','like',"%".$serach_value."%");              
                });
            }

            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                    'finance_company.company_name',
                    'take',
                    'give',
                    'actual'
                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('finance_company.id','asc');      
        
        $count = $api_data->count();
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
       
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

    public function finance_ta_payment_list(){
        $data = [
            'layout' => 'layouts.main'
        ];
        return view('admin.finance.ta_payment_list',$data);
    }

    public function finance_ta_paymentList_api(Request $request) {
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
        
        $api_data = FinanceTaPayment::where('finance_ta_payment.payment_into','=','TA')
                     ->leftJoin('finance_company','finance_company.id','finance_ta_payment.finance_company_id')
                    ->select(
                        'finance_company.company_name',
                        'finance_ta_payment.type',
                        'finance_ta_payment.amount',
                        'finance_ta_payment.balance_amount',
                        'finance_ta_payment.payment_date',
                        'finance_ta_payment.payment_into'
                    );

            if(!empty($serach_value))
            {
                 $api_data->where(function($query) use ($serach_value){
                        $query->where('finance_ta_payment.type','like',"%".$serach_value."%")
                        ->orwhere('finance_ta_payment.amount','like',"%".$serach_value."%")
                        ->orwhere('finance_ta_payment.balance_amount','like',"%".$serach_value."%")
                        ->orwhere('finance_ta_payment.payment_date','like',"%".$serach_value."%")
                        ->orwhere('finance_ta_payment.payment_into','like',"%".$serach_value."%")
                        ->orwhere('finance_company.company_name','like',"%".$serach_value."%")
                        ;
                    });
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                    'finance_company.company_name',
                    'finance_ta_payment.type',
                    'finance_ta_payment.amount',
                    'finance_ta_payment.balance_amount',
                    'finance_ta_payment.payment_date',
                    'finance_ta_payment.payment_into'
                
                ];
                
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('finance_ta_payment.id','desc');   
        
        $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

    public function finance_ta_adjustment_list(){
        $data = [
            'layout' => 'layouts.main'
        ];
        return view('admin.finance.ta_adjustment_list',$data);
    }

    public function finance_ta_adjustmentList_api(Request $request) {
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
        
        $api_data = FinanceTaAdjustment::
                     leftJoin('finance_company','finance_company.id','finance_ta_adjustment.finance_company_id')
                     ->leftJoin('sale_finance_info','sale_finance_info.id','finance_ta_adjustment.sale_finance_id')
                     ->leftJoin('sale','sale.id','sale_finance_info.sale_id')
                    ->select(
                        'sale.sale_no',
                        'finance_company.company_name',
                        'finance_ta_adjustment.type',
                        'finance_ta_adjustment.amount_type',
                        'finance_ta_adjustment.amount',
                        'finance_ta_adjustment.pending_amount',
                        'finance_ta_adjustment.date',
                        'finance_ta_adjustment.payment_into',
                        'finance_ta_adjustment.interest_period_from',
                        'finance_ta_adjustment.interest_period_to'
                    );

            if(!empty($serach_value))
            {
                 $api_data->where(function($query) use ($serach_value){
                        $query->where('finance_ta_adjustment.amount_type','like',"%".$serach_value."%")
                        ->orwhere('sale.sale_no','like',"%".$serach_value."%")
                        ->orwhere('finance_ta_adjustment.amount','like',"%".$serach_value."%")
                        ->orwhere('finance_ta_adjustment.type','like',"%".$serach_value."%")
                        ->orwhere('finance_ta_adjustment.interest_period_from','like',"%".$serach_value."%")
                        ->orwhere('finance_ta_adjustment.interest_period_to','like',"%".$serach_value."%")
                        ->orwhere('finance_ta_adjustment.pending_amount','like',"%".$serach_value."%")
                        ->orwhere('finance_ta_adjustment.date','like',"%".$serach_value."%")
                        ->orwhere('finance_company.company_name','like',"%".$serach_value."%")
                        ->orwhere('finance_ta_adjustment.payment_into','like',"%".$serach_value."%")
                        ;
                    });
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                        'finance_company.company_name',
                        'sale.sale_no',
                        'finance_ta_adjustment.type',
                        'finance_ta_adjustment.amount_type',
                        'finance_ta_adjustment.amount',
                        'finance_ta_adjustment.pending_amount',
                        'finance_ta_adjustment.date',
                        'finance_ta_adjustment.payment_into',
                        'finance_ta_adjustment.interest_period_from',
                        'finance_ta_adjustment.interest_period_to'
                
                ];
                
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('finance_ta_adjustment.id','desc');   
        
        $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

}
