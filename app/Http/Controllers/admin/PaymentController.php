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
use \App\Model\RtoFileSubmission;
use \App\Model\RtoFileSubmissionDetails;
use \App\Model\RcCorrectionRequest;
use \App\Model\RtoSummary;
use \App\Model\PDI;
use \App\Model\PDI_details;
use \App\Model\FinalInspection;
use\App\Model\ServiceModel;
use\App\Model\CustomerDetails;
use\App\Model\PartDetails;
use \App\Model\Parts;
use \App\Model\Master;
use \App\Model\SecurityModel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Auth;
use Hash;
use PDF;
use \Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\Import;
use DateTime;
use \App\Model\Job_card;
use \App\Model\Bay;
use \App\Model\BayAllocation;
use \App\Model\PaymentRequest;
use \App\Model\ServicePartRequest;
use\App\Model\TestRide;
use\App\Model\Feedback;
use\App\Model\ServiceCharge;
use Mail;



class PaymentController extends Controller
{
    public function __construct() {
        //$this->middleware('auth');
    }

    public function PaymentRequest_list() {
        $data = [
            'layout' => 'layouts.main'
        ];
        
        return view('admin.payment.payment_request_list',$data);
    }

    public function PaymentRequestList_api(Request $request) {
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;

        $pay_status = $request->input('pay_status');
        $type = $request->input('req_type');
        $type_id = $request->input('req_type_id');

            $api_data = PaymentRequest::leftJoin('payment',function($join){
                            $join->on(DB::raw('payment.type = payment_request.type and payment.payment_request_id'),'=','payment_request.id')
                            ->where('payment.status','<>','cancelled');
                        })
                    ->leftJoin('sale','sale.id','payment_request.type_id')
                    ->leftJoin('hsrp','hsrp.id','payment_request.type_id')
                    ->leftJoin('challan','challan.id','payment_request.type_id')
                    ->leftJoin('booking','booking.id','payment_request.type_id')
                    ->leftJoin('security_amount','security_amount.id','payment_request.type_id')
                    ->leftJoin('otc_sale','otc_sale.id','payment_request.type_id')
                    ->leftJoin('rc_correction_request','rc_correction_request.id','payment_request.type_id')
                    ->leftJoin('insurance_renewal','insurance_renewal.id','payment_request.type_id')
                    ->leftJoin('customer as sale_cust','sale_cust.id','sale.customer_id')
                    ->leftJoin('customer as hsrp_cust','hsrp_cust.id','hsrp.customer_id')
                    ->leftJoin('customer as challan_cust','challan_cust.id','challan.customer_id')
                    ->leftJoin('customer as otcsale_cust','otcsale_cust.id','otc_sale.customer_id')
                    ->leftJoin('customer as insur_cust','insur_cust.id','insurance_renewal.customer_id')
                    ->leftJoin('rto_summary','rto_summary.rto_id','rc_correction_request.rto_id')
                    ->whereIn('payment_request.store_id',CustomHelpers::user_store())
                    ->select(
                    'payment_request.id',
                    'payment_request.type',
                    'payment_request.type_id',
                    DB::raw("IF(payment_request.type='sale',sale_cust.name, 
                            IF(payment_request.type='booking',booking.name,
                                IF(payment_request.type='security',security_amount.name,
                                    IF(payment_request.type='otcsale',otcsale_cust.name,
                                        IF(payment_request.type='otcsale',otcsale_cust.name,
                                            IF(payment_request.type='hsrp',hsrp_cust.name,
                                                IF(payment_request.type='challan',challan_cust.name,
                                                    IF(payment_request.type='insurance_renewal',insur_cust.name,
                                                    'NONE'
                                                   )
                                                )
                                            )
                                        )
                                    )
                                )
                            )
                    ) as customer_name"),
                    DB::raw("IF(payment_request.type='sale',sale.sale_date, 
                            IF(payment_request.type='booking',date(booking.created_at),
                                IF(payment_request.type='security',date(security_amount.created_at),
                                    IF(payment_request.type='otcsale',date(otcsale_cust.created_at),
                                        IF(payment_request.type='hsrp',date(hsrp.updated_at),
                                        IF(payment_request.type='challan',date(challan.created_at),
                                                IF(payment_request.type='RcCorrection',date(rc_correction_request.created_at),
                                                    IF(payment_request.type='insurance_renewal',insurance_renewal.sale_date,
                                                    'NONE'
                                                    )
                                                )
                                            )    
                                        )
                                    )
                                )
                            )
                    ) as date"),
                    DB::raw('IFNULL(sum(payment.amount),0) as paid_amount'),
                    'payment_request.amount',
                    'payment_request.status') 
                ->groupBy('payment_request.id');
            
            if(!empty($type) && !empty($type_id)){
                $api_data = $api_data->where('payment_request.type',$type)
                            ->where('payment_request.type_id',$type_id);
            }
            if($pay_status == 'Pending'){
                $api_data = $api_data->where('payment_request.status','Pending');
            }
            else if($pay_status == 'Done'){
                $api_data = $api_data->where('payment_request.status','Done');
            }
           
            if(!empty($serach_value))
            {
               $api_data->where(function($query) use ($serach_value){
                        $query->where('payment_request.id','like',"%".$serach_value."%")
                        ->orwhere('payment_request.type','like',"%".$serach_value."%")
                        ->orwhere(DB::raw("IF(payment_request.type='sale',sale_cust.name, 
                                    IF(payment_request.type='booking',booking.name,
                                        IF(payment_request.type='security',security_amount.name,
                                            IF(payment_request.type='otcsale',otcsale_cust.name,
                                                IF(payment_request.type='RcCorrection',rto_summary.customer_name,
                                                    IF(payment_request.type='insurance_renewal',insur_cust.name,
                                                    'NONE'
                                                    )
                                                )
                                            )
                                        )
                                    )
                            )"),'like',"%".$serach_value."%")
                            ->orwhere(DB::raw("IF(payment_request.type='sale',sale.sale_date, 
                                        IF(payment_request.type='booking',date(booking.created_at),
                                            IF(payment_request.type='security',date(security_amount.created_at),
                                                IF(payment_request.type='otcsale',date(otcsale_cust.created_at),
                                                    IF(payment_request.type='RcCorrection',date(rc_correction_request.created_at),
                                                        IF(payment_request.type='insurance_renewal',insurance_renewal.sale_date,
                                                        'NONE'
                                                        )
                                                    )
                                                )
                                            )
                                        )
                                )"),'like',"%".$serach_value."%")
                        ->orwhere('payment_request.amount','like',"%".$serach_value."%")
                        ->orwhere('payment_request.status','like',"%".$serach_value."%");
                    });
               
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                    'payment_request.id',
                    'payment_request.type',
                    'payment_request.type_id',
                    'payment_request.amount',
                    'payment_request.status'
                ];
                
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('payment_request.id','desc');   
        
        $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();

        foreach($api_data as $value)
       {
           if ($value['type'] == 'sale') {
               $amount = Payment::where('type','sale')
                            ->where('sale_id',$value['type_id'])
                            ->sum('amount');
                $value['paid_amount'] = $amount;
           }  
       }
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

    public function Payment_Request($id) {
        $payment = PaymentRequest::where('id',$id)->get()->first();
        if(!isset($payment['id'])){
            return back()->with('error','Payment Request Not Found.');
        }
        $auth_store = CustomHelpers::user_store();
        if(!in_array($payment->store_id,$auth_store)){
            return back()->with('error','Payment Request Not In Your Store.');
        }
        $pay_mode = Master::where('type','payment_mode')->select('key','value')->get();
        $receiver_bank = Master::where('type','receiver_bank')->select('key','value')->get();
        // if ($payment['type'] == 'sale') {
        //     //  $paid = Payment::where('sale_id',$payment['type_id'])->where('type',$payment['type'])->where('status','<>','cancelled')->sum('amount');
        //      $paid = Payment::where('payment_request_id',$id)->where('status','<>','cancelled')->sum('amount');
        // }else{
        //     $paid = Payment::where('type_id',$payment['type_id'])->where('type',$payment['type'])->where('status','<>','cancelled')->sum('amount');
        // }
        $paid = Payment::where('payment_request_id',$id)->where('status','<>','cancelled')->sum('amount');
        if ($payment['type'] == 'security') {
            $details = SecurityModel::where('id',$payment['type_id'])->get()->first();
        }else if ($payment['type'] == 'booking') {
            $details = BookingModel::where('id',$payment['type_id'])->get()->first();
        } else if ($payment['type'] == 'RcCorrection') {
            $details = RcCorrectionRequest::leftJoin('rto','rto.id','rc_correction_request.rto_id')
                ->leftJoin('sale','sale.id','rto.sale_id')
                ->select("rc_correction_request.*",'sale.store_id')
                ->where('rc_correction_request.id',$payment['type_id'])
                ->get()
                ->first();
        } else if ($payment['type'] == 'sale') {
            $details = Sale::where('id',$payment['type_id'])->get()->first();
        }else{
            $details = '';
        }
       $data = [
        'payment' => $payment,
        'pay_mode' => $pay_mode,
        'receiver_bank' => $receiver_bank,
        'paid' => $paid,
        'details' => $details,
        'layout' => 'layouts.main'
        ];
        
        return view('admin.payment.payment_request',$data);
    }

     public function PaymentRequest_DB(Request $request) {
         try {
            $timestamp = date('Y-m-d G:i:s');
            $this->validate($request,[
                'amount'=>'required',
                'payment_mode'=>'required',
                'transaction_number'=>'required|unique:payment,transaction_number',
                'transaction_charges'=>'required',
                'receiver_bank_detail'=>'required_unless:payment_mode,Cash',
                'pay_date'  =>  'required'
            ],[
                'amount.required'=> 'This field is required.', 
                'payment_mode.required'=> 'This field is required.', 
                'transaction_number.required'=> 'This field is required.', 
                'transaction_charges.required'=> 'This field is required.', 
               'receiver_bank_detail.required_unless'=> 'This field is required.', 
               'pay_date.required'  =>  'This Field is required'
            ]);
                
            DB::beginTransaction();
            $payment = PaymentRequest::where('id',$request->input('payment_request_id'))->get()->first();
            if(!isset($payment['id'])){
                DB::rollback();
                return back()->with('error','Payment Request Not Found.')->withInput();
            }
            // if ($payment['type'] == 'sale') {
            //      $paid = Payment::where('type',$payment['type'])->where('sale_id',$payment['type_id'])->sum('amount');
            // }else{
            //      $paid = Payment::where('type',$payment['type'])->where('type_id',$payment['type_id'])->sum('amount');
            // }
            $paid = Payment::where('payment_request_id',$request->input('payment_request_id'))->where('status','<>','cancelled')->sum('amount');
            $paidAmount = $paid+$request->input('amount');

            if ($request->input('boucher_signed') == 'yes') {
                $voucher = $request->input('boucher_signed');
            }else{
                $voucher = 'no'; 
            }
            if ($payment['type'] == 'sale') {
                $sale_id = $payment['type_id'];
                $type_id = $payment['type_id'];
            }else{
                $sale_id = 0;
                $type_id = $payment['type_id'];
            }

              $arr = [];
                if(in_array($request->input('payment_mode'),CustomHelpers::pay_mode_received())) {
                    $arr = ['status' => 'received'];

                    // $total_amount = SecurityModel::where('id',$payment['type_id'])->select('total_amount')->first();  
                    // $payment_amount = $total_amount['total_amount'] + $request->input('amount');
                    // $update = SecurityModel::where('id',$payment['type_id'])->update([
                    //     'total_amount' => $payment_amount
                    // ]);                  
                }else{
                        $arr    =   [
                            'receiver_bank_detail' => $request->input('receiver_bank_detail')
                        ];
                    }


            if ($payment['amount'] != 0  && $payment['amount'] < $paidAmount) {
                DB::rollback();
                return back()->with('error','Your amount is too more !');
            }else{
                $paydata = array_merge($arr,[
                    'type_id'=> $type_id,
                    'sale_id'=> $sale_id,
                    'type' => $payment['type'],
                    'store_id' => $payment['store_id'],
                    'voucher_signed' => $voucher,
                    'payment_mode' => $request->input('payment_mode'),
                    'payment_request_id' => $request->input('payment_request_id'),
                    'transaction_number' => $request->input('transaction_number'),
                    'transaction_charges' => $request->input('transaction_charges'),
                    'amount' => $request->input('amount'),
                    'payment_date'  =>  $request->input('pay_date')
                ]);


            }

                $AddData = Payment::insertGetId($paydata);
                if($AddData == NULL) {
                    DB::rollback();
                       return redirect('/admin/payment/request/'.$request->input('payment_request_id'))->with('error','some error occurred'.$ex->getMessage());
                } else {
                    if ($payment['amount'] == $paidAmount) {
                         $update = PaymentRequest::where('id',$request->input('payment_request_id'))->update(['status' => 'Done']);
                         if ($update == NULL) {
                             DB::rollback();
                             return redirect('/admin/payment/request/'.$request->input('payment_request_id'))->with('error','some error occurred'.$ex->getMessage());
                         }
                    }
                    DB::commit();
                     /* Add action Log */
                    CustomHelpers::userActionLog($action='Add Payment',$request->input('payment_request_id'),0);
                    return redirect('/admin/payment/request/'.$request->input('payment_request_id'))->with('success','Amount Successfully Paid .');
                }
               
        
        }  catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return redirect('/admin/payment/request/'.$request->input('payment_request_id'))->with('error','some error occurred'.$ex->getMessage());
        }
    }

    public function Payment_Details($id) {
        $payment = PaymentRequest::where('id',$id)->get()->first();
        $payData = Payment::where('payment_request_id',$id)->where('type_id',$payment['type_id'])->where('type',$payment['type'])->get();
        $data = array(
            'payment' => $payment,
            'payData' => $payData,
            'layout' => 'layouts.main'
        );
         return view('admin.payment.payment_detail',$data);
    }

    
}