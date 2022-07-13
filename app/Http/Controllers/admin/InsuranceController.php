<?php

namespace App\Http\Controllers\admin;
use Illuminate\Http\Request;
use \stdClass;
use \App\Model\Store;
use \App\Model\ProductModel;
use \App\Model\Customers;
use \App\Custom\CustomHelpers;
use \App\Model\Stock;
use App\Model\Users;
use \App\Model\PaymentRequest;
use \App\Model\Sale;
use \App\Model\Insurance;
use \App\Model\SaleOrder;
use \App\Model\SaleDiscount;
use \App\Model\Payment;
use \App\Model\OrderPendingModel;
use \App\Model\BookingModel;
use \App\Model\BestDealSale;
use \App\Model\ServiceModel;
use \App\Model\Master;
use \App\Model\ApprovalRequest;
use \App\Model\ApprovalSetting;
use \App\Model\RefundRequest;
use \App\Model\Settings;
use \App\Model\CancelRequest;
use \App\Model\FuelStockModel;
use \App\Model\CustomerDetails;
use \App\Model\InsuranceCompany;
use \App\Model\InsuranceRenewal;
use \App\Model\InsuranceRenewalOrder;
use \App\Model\InsuranceRenewalDetail;
use\App\Model\SellingDealer;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Auth;
use Hash;
use \Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\Import;
use Mail;
use File;

class InsuranceController extends Controller
{
    public function __construct() {
        //$this->middleware('auth');
    }

    public function InsuranceRenewal() {
        $model_name = ProductModel::where('type','product')->orderBy('model_name','ASC')->groupBy('model_name')->get(['model_name']);
        $ins_company = InsuranceCompany::all();
        $cpa_company = InsuranceCompany::where('cpa','1')->get();
        $store = Store::whereIn('id',CustomHelpers::user_store())
                    ->select('id',DB::raw('concat(store.name,"-",store.store_type) as name'))->get();
        $selling_dealer = SellingDealer::all();
        $customer = Customers::orderBy('id','desc')->get();
       $data  = array(
            'model_name' => $model_name,
            'ins_company' => $ins_company,
            'store' => $store,
            'cpa_company' => $cpa_company,
            'customer' => $customer,
            'selling_dealer' => $selling_dealer,
            'layout' => 'layouts.main', 
       );
       return view('admin.insurance.insurance_renewal',$data);
    }

    public function InsuranceSearchFrame(Request $request) {
       $text = $request->input('text');
       $framedata = SaleOrder::where('sale_order.product_frame_number', 'Like', "$text")
                ->leftJoin('sale','sale.id','sale_order.sale_id')
                ->leftJoin('customer','customer.id','sale.customer_id')
                ->leftJoin('insurance','sale.id','insurance.sale_id')
                ->leftJoin('insurance_data','sale.id','insurance_data.sale_id')
                ->leftJoin('insurance_renewal_detail','insurance_renewal_detail.id','insurance_data.insurance_detail_id')
                ->leftJoin('product','product.id','sale.product_id')
                ->select('sale.id as sale_id','product.model_name','product.model_variant','product.color_code','customer.name','customer.mobile','insurance.*','customer.id as customer_id','insurance_renewal_detail.od_policy_end_date','insurance_renewal_detail.tp_policy_end_date','insurance_renewal_detail.cpa_policy_end_date')
                ->first();
        if ($framedata == NULL) {
            $search_frame = InsuranceRenewalDetail::where('insurance_renewal_detail.frame_number', 'Like', "$text")
                ->leftJoin('insurance_data','insurance_data.id','insurance_renewal_detail.insurance_data_id')
                ->leftJoin('customer','customer.id','insurance_data.customer_id')
                ->leftJoin('product','product.id','insurance_renewal_detail.product_id')
                ->select('product.model_name','product.model_variant','product.color_code','customer.name','customer.mobile','insurance_renewal_detail.*','customer.id as customer_id','insurance_data.sale_id')
                ->first();
                if ($search_frame) {
                    return response()->json($search_frame); 
                }else{
                    return response()->json($search_frame); 
                }
        }else{
           return response()->json($framedata); 
        }
    }

    public function SearchODPolicyNumber(Request $request) {
      $policy_number = $request->input('text');
      $searchpolicy = InsuranceRenewalDetail::where('insurance_renewal_detail.od_old_policy_number', 'Like', "$policy_number")
                ->leftJoin('insurance_data','insurance_data.insurance_detail_id','insurance_renewal_detail.id')
                ->leftJoin('customer','customer.id','insurance_data.customer_id')
                ->leftJoin('product','product.id','insurance_renewal_detail.product_id')
                ->select('product.model_name','product.model_variant','product.color_code','customer.name','customer.id as customer_id','customer.mobile','insurance_renewal_detail.*')
                ->first();

        if ($searchpolicy == NULL) {
            $policyNumber = Insurance::where('insurance.policy_number','Like',"$policy_number")
                            ->leftJoin('customer','insurance.customer_id','customer.id')
                            ->leftJoin('insurance_data','insurance_data.id','insurance.insurance_data_id')
                            ->leftJoin('sale','insurance.sale_id','sale.id')
                            ->leftJoin('product','product.id','sale.product_id')
                            ->where('insurance.insurance_type','OD')
                            ->select('product.model_name','product.model_variant','product.color_code','customer.name','insurance.*','customer.mobile','insurance.insurance_data_id','sale.id as sale_id','customer.id as customer_id')->get()->first();

            if ($policyNumber['sale_id'] == NULL || $policyNumber == NULL) {
                 $searchNumber = Insurance::where('insurance.policy_number','Like',"$policy_number")
                            ->leftJoin('insurance_data','insurance_data.id','insurance.insurance_data_id')
                            ->leftJoin('customer','insurance_data.customer_id','customer.id')
                            ->leftJoin('insurance_renewal_detail','insurance_data.insurance_detail_id','insurance_renewal_detail.id')
                            ->leftJoin('product','product.id','insurance_renewal_detail.product_id')
                            ->where('insurance.insurance_type','OD')
                            ->select('product.model_name','product.model_variant','product.color_code','customer.name','insurance_data.*','customer.mobile','insurance.insurance_data_id','insurance.bestdeal_id','customer.id as customer_id')->get()->first();
                if ($searchNumber['bestdeal_id']) {
                         $polyNumber = Insurance::where('insurance.policy_number','Like',"$policy_number")
                            ->leftJoin('best_deal_sale','best_deal_sale.id','insurance.bestdeal_id')
                            ->leftJoin('customer','insurance.customer_id','customer.id')
                            ->leftJoin('product','product.id','best_deal_sale.product_id')
                            ->where('insurance.insurance_type','OD')
                            ->select('product.model_name','product.model_variant','product.color_code','customer.name','insurance.*','customer.mobile','insurance.insurance_data_id','customer.id as customer_id')->get()->first();   
                        return response()->json($polyNumber);
                    }
                     return response()->json($searchNumber);
            }else{
              return response()->json($policyNumber);  
            } 
        }else{
         return response()->json($searchpolicy);
        }
    }

     public function SearchTPPolicyNumber(Request $request) {
      $policy_number = $request->input('text');
      $searchpolicy = InsuranceRenewalDetail::where('insurance_renewal_detail.tp_old_policy_number', 'Like', "$policy_number")
                ->leftJoin('insurance_data','insurance_data.insurance_detail_id','insurance_renewal_detail.id')
                ->leftJoin('customer','customer.id','insurance_data.customer_id')
                ->leftJoin('product','product.id','insurance_renewal_detail.product_id')
                ->select('product.model_name','product.model_variant','product.color_code','customer.name','customer.mobile','insurance_renewal_detail.*','customer.id as customer_id')
                ->first();

        if ($searchpolicy == NULL) {
            $policyNumber = Insurance::where('insurance.policy_number','Like',"$policy_number")
                            ->leftJoin('customer','insurance.customer_id','customer.id')
                            ->leftJoin('insurance_data','insurance_data.id','insurance.insurance_data_id')
                            ->leftJoin('sale','insurance.sale_id','sale.id')
                            ->leftJoin('product','product.id','sale.product_id')
                            ->where('insurance.insurance_type','TP')
                            ->select('product.model_name','product.model_variant','product.color_code','customer.name','insurance.*','customer.mobile','insurance.insurance_data_id','sale.id as sale_id','customer.id as customer_id')->get()->first();

            if ($policyNumber['sale_id'] == NULL || $policyNumber == NULL) {
                 $searchNumber = Insurance::where('insurance.policy_number','Like',"$policy_number")
                            ->leftJoin('insurance_data','insurance_data.id','insurance.insurance_data_id')
                            ->leftJoin('customer','insurance_data.customer_id','customer.id')
                            ->leftJoin('insurance_renewal_detail','insurance_data.insurance_detail_id','insurance_renewal_detail.id')
                            ->leftJoin('product','product.id','insurance_renewal_detail.product_id')
                            ->where('insurance.insurance_type','TP')
                            ->select('product.model_name','product.model_variant','product.color_code','customer.name','insurance_data.*','customer.mobile','insurance.insurance_data_id','insurance.bestdeal_id','customer.id as customer_id')->get()->first();
                if ($searchNumber['bestdeal_id']) {
                         $polyNumber = Insurance::where('insurance.policy_number','Like',"$policy_number")
                            ->leftJoin('best_deal_sale','best_deal_sale.id','insurance.bestdeal_id')
                            ->leftJoin('customer','insurance.customer_id','customer.id')
                            ->leftJoin('product','product.id','best_deal_sale.product_id')
                            ->where('insurance.insurance_type','TP')
                            ->select('product.model_name','product.model_variant','product.color_code','customer.name','insurance.*','customer.mobile','insurance.insurance_data_id','customer.id as customer_id')->get()->first();   
                        return response()->json($polyNumber);

                    }
                     return response()->json($searchNumber);

            }else{
              return response()->json($policyNumber);  
            } 

        }else{
         return response()->json($searchpolicy);
        }
    }

    public function InsuranceRenewal_DB(Request $request) {
        $validator = Validator::make($request->all(),[
           'frame_number'  =>  'required',
           'tp'  =>  'required',
           // 'cpa'  =>  'required',
           'od'  =>  'required',
           'od_tenure'  =>  'required',
           'od_end_date'  =>  'required_if:od,Yes',
           'tp_end_date'=>'required_if:tp,Yes',
           'ins_company'=>'required_if:tp,Yes',
           'cpa_end_date'=>'required_if:cpa,Yes',
           'cpa_ins_company'=>'required_if:cpa,Yes',
           'total_amount' => 'required',
           // 'cpa_tenure'=>'required',
           'tp_tenure'=>'required',
           'old_od_policy_number'=>'required',
           'old_tp_policy_number'=>'required',
           'km_read'=>'required',
           'store_name'=>'required',
           // 'od_ins_company'=>'required_if:od,No',
        ],
        [
            'frame_number.required'  =>  'This Field is required',
            'tp.required'  =>  'This Field is required',
            'od.required'  =>  'This Field is required',
            'od_tenure.required'  =>  'This Field is required',
            'od_end_date.required'  =>  'This Field is required',
            // 'cpa.required'  =>  'This Field is required',
            'tp_end_date.required' => 'This Field is required',
            'ins_company.required' => 'This Field is required',
            'cpa_end_date.required' => 'This Field is required',
            'cpa_ins_company.required' => 'This Field is required',
            'total_amount.required' => 'This Field is required',
            // 'cpa_tenure' => 'This field is required',
            'tp_tenure' => 'This field is required',
            'old_od_policy_number' => 'This field is required',
            'old_tp_policy_number' => 'This field is required',
            'km_read' => 'This field is required',
            // 'od_ins_company' => 'This field is required',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        try{
           DB::beginTransaction();
              $frame = $request->input('frame_number');
              $od = $request->input('od');
              $od_end_date = $request->input('od_end_date');
              $od_tenure = $request->input('od_tenure');
              $od_company = $request->input('od_ins_company');
              $tp = $request->input('tp');
              $tp_end_date = $request->input('tp_end_date');
              $tp_tenure = $request->input('tp_tenure');
              $tp_end_date = $request->input('tp_end_date');
              $tp_company = $request->input('ins_company');
              $cpa = $request->input('cpa');
              $cpa_tenure = $request->input('cpa_tenure');
              $cpa_end_date = $request->input('cpa_end_date');
              $cpa_company = $request->input('cpa_ins_company');
              $store_id = $request->input('store_name');
              $product_id = $request->input('prod_name');
              $customer_id = $request->input('customer_id');
              $sale_date = $request->input('sale_date');
              $old_od_policy_number = $request->input('old_od_policy_number');
              $old_tp_policy_number = $request->input('old_tp_policy_number');
              $selling_dealer = $request->input('selling_dealer');
              $km_read = $request->input('km_read');
              $total_amount = $request->input('total_amount');
              $old_customer = $request->input('customer');
            if (!empty($tp_tenure) && $od_tenure != $tp_tenure ) {
                return back()->with('error_msg','Please select OD tenure equal of TP tenure.')->withInput();
            }
         
            if ((!empty($cpa) && !empty($cpa_tenure)) && $cpa_tenure > $od_tenure) {
                return back()->with('err_msg','Please select CPA tenure equal or less than of OD tenure.')->withInput();
            }
            $insurance_data_id = '';
            $curdate = strtotime(date('Y-m-d'));
            $mydate = strtotime($od_end_date);
            if($curdate > $mydate) {
                $breakin = '1';
            }else{
                $breakin = '0';
            }
              
            $searchFrame = CustomHelpers::SearchFrameNumber($frame);
            $seachOdPolicy = CustomHelpers::SearchODPolicyNumber($old_od_policy_number);
            $seachTpPolicy = CustomHelpers::SearchTpPolicyNumber($old_tp_policy_number);
            if (!empty($searchFrame) && $searchFrame['customer_id'] != $customer_id && empty($request->input('resold'))) {
              return back()->with('error_msg_resold','Please chack resold.')->withInput(); 
                return back()->with('error','If you want to change customer, Please chack resold.')->withInput(); 
            }
            if (!empty($searchFrame)  && $seachOdPolicy == NULL && $seachTpPolicy == NULL) {
                if($searchFrame['insurance_data_id']){
                  $end_date = InsuranceRenewalDetail::where('insurance_data_id',$searchFrame['insurance_data_id'])->orderBy('id','desc')->get()->first();
                  if ($end_date) {
                       $od_date = $end_date['od_policy_end_date'];
                       $curr_date = date('Y-m-d');
                       $getmonth = CustomHelpers::getMonth($od_date,$curr_date);
                       if($getmonth < 6 && $od_date != null){
                        return redirect('/admin/insurance/renewal')->with('error','This frame number is not valid for insurance renewal,because your policy end date is under form 6 months.')->withInput();
                       }
                  }
                }   
            }
            if ((!empty($seachOdPolicy)  && $searchFrame == NULL) || (!empty($seachTpPolicy)  && $searchFrame == NULL)) {
                return redirect('/admin/insurance/renewal')->with('error','Policy number not match with provided frame number.')->withInput();
            }

            // if (!empty($seachOdPolicy)  && $searchFrame == NULL && $seachTpPolicy == NULL) {
            //     return redirect('/admin/insurance/renewal')->with('error','Wrong old OD policy number and Frame is added same for TP.');
            // }
            $sale_id = 0;

                if (!empty($seachOdPolicy)) {
                    $sale_id = $seachOdPolicy['sale_id'];
                    $product_id = $seachOdPolicy['product_id'];
                    $customer = $seachOdPolicy['customer_id'];
                    $insurance_data_id = $seachOdPolicy['insurance_data_id'];
                    $Instype = 'renew';
                }

                 if (!empty($seachTpPolicy)) {
                    $sale_id = $seachTpPolicy['sale_id'];
                    $product_id = $seachTpPolicy['product_id'];
                    $customer = $seachTpPolicy['customer_id'];
                    $insurance_data_id = $seachTpPolicy['insurance_data_id'];
                    $Instype = 'renew';
                }
                  if ($searchFrame ) {
                    $sale_id = $searchFrame['sale_id'];
                    $product_id = $searchFrame['product_id'];
                    $Instype = 'renew';
                    $customer = $searchFrame['customer_id'];
                    $insurance_data = InsuranceRenewal::where('sale_id',$sale_id)->get()->first();
                    if ($insurance_data == NULL) {
                        $sale_id = $searchFrame['sale_id'];
                    }else{
                        $insurance_data_id = $insurance_data['id'];
                    }
                }else{
                   $sale_id = 0;
                }
                $InsertDetailsData = '';
                if ($searchFrame == NULL  && $seachOdPolicy == NULL && $seachTpPolicy == NULL) {
                     $Instype = 'new';
                    $validator = Validator::make($request->all(),[
                       'model_name'=>'required',
                       'model_variant'=>'required',
                       'prod_name'=>'required',
                       'customer_id'=>'required',
                       'sale_date'=>'required',
                       'selling_dealer'=>'required',
                    ],
                    [
                        'model_name' =>' This field is required',
                        'model_variant' => 'This field is required',
                        'prod_name' => 'This field is required',
                        'customer_id' => 'This field is required',
                        'sale_date' => 'This field is required',
                        'selling_dealer' => 'This field is required',
                    ]);
                    if ($validator->fails()) {
                        return back()->withErrors($validator)->withInput();
                    }
            } 
            $insert = '';
            if (($searchFrame == NULL  && $seachOdPolicy == NULL && $seachTpPolicy == NULL) || ((!empty($searchFrame)  && $seachOdPolicy == NULL && $seachTpPolicy == NULL)) || (!empty($searchFrame) && $seachOdPolicy == NULL )) {
                    $InsertDetailsData = [
                        'frame_number' => $frame,
                        'insurance_data_id' => ($insurance_data_id != '') ? $insurance_data_id: 0,
                        'product_id' => $product_id,
                        'od_old_policy_number' => $old_od_policy_number,
                        'tp_old_policy_number' => $old_tp_policy_number,
                        'selling_dealer' => $selling_dealer,
                        'od_policy_end_date' => ($od_end_date != '') ? date('Y-m-d', strtotime($od_end_date)): null,
                        'tp_policy_end_date' => ($tp_end_date != '') ? date('Y-m-d', strtotime($od_end_date)): null,
                        'cpa_policy_end_date' => ($cpa_end_date != '') ? date('Y-m-d', strtotime($cpa_end_date)): null,
                    ];

                    if ($InsertDetailsData != '') {
                       $insert = InsuranceRenewalDetail::insertGetId($InsertDetailsData);
                       if ($insert == NULL) {
                           DB::rollback();
                           return redirect('/admin/insurance/renewal')->with('error','Something went wrong');
                       }
                    }
                    
            }

            if ($insurance_data_id == NULL || !empty($request->input('resold'))) {
                 $InsertData = [
                        'store_id' => $store_id,
                        'customer_id' => $customer_id,
                        'sale_id' => ($sale_id != '') ? $sale_id: 0,
                        'type' => $Instype,
                        'insurance_detail_id' => ($insert != '') ? $insert: 0,
                    ] ;
                  $insurance_data_id = InsuranceRenewal::insertGetId($InsertData);
                        if ($insurance_data_id == NULL) {
                            DB::rollback();
                            return redirect('/admin/insurance/renewal')->with('error','Something went wrong');
                        }
                      if ($insert && $insurance_data_id != null) {
                         $update = InsuranceRenewalDetail::where('id',$insert)->update([
                            'insurance_data_id' => $insurance_data_id
                          ]);
                          if ($update == null) {
                            DB::rollback();
                            return redirect('/admin/insurance/renewal')->with('error','Something went wrong');
                          }
                      }
                    
            }            
            if (!empty($cpa) && !empty($cpa_tenure)) {
               $cpaTenure = $cpa_tenure;
            }else{
              $cpaTenure = '';
            }
            if (!empty($cpa) && !empty($cpa_company)) {
               $cpaCompany = $cpa_company;
            }else{
              $cpaCompany = '';
            }
                if ($od) {
                    $odData = Insurance::insertGetId([
                        'insurance_data_id' => $insurance_data_id,
                        'policy_tenure' => $od_tenure,
                        'insurance_date' => date('Y-m-d'),
                        'insurance_co' => $od_company,
                        'insurance_type' => 'OD',
                        'cpa_company' => $cpaCompany,
                        'cpa_tenure' => $cpaTenure,
                        'sale_id' => ($sale_id != '') ? $sale_id: 0,
                        'customer_id' => $customer_id,
                        'type' => $Instype 
                    ]);
                    if ($odData == NULL) {
                        DB::rollback();
                        return redirect('/admin/insurance/renewal')->with('error','Something went wrong');
                    }
                }
                if ($tp) {
                    $tpData = Insurance::insertGetId([
                        'insurance_data_id' => $insurance_data_id,
                        'policy_tenure' => $od_tenure,
                        'insurance_date' => date('Y-m-d'),
                        'insurance_co' => $od_company,
                        'insurance_type' => 'TP',
                        'sale_id' => ($sale_id != '') ? $sale_id: 0,
                        'customer_id' => $customer_id,
                        'type' => $Instype 
                    ]);
                    if ($tpData == NULL) {
                       DB::rollback();
                       return redirect('/admin/insurance/renewal')->with('error','Something went wrong');
                    }
                }

                $orderData = InsuranceRenewalOrder::insertGetId([
                    'insurance_data_id' => $insurance_data_id,
                    'insurance_detail_id' => ($insert != '') ? $insert: 0,
                    'total_amount' => $total_amount,
                    'od_insurance_id' => $odData,
                    'tp_insurance_id' => $tpData,
                    'km_read' => $km_read,
                    'breakin' => $breakin
                ]);

                if ($orderData == NULL) {
                       DB::rollback();
                       return redirect('/admin/insurance/renewal')->with('error','Something went wrong');
                }else{
                    $type = 'insurance_renewal';
                    $payment_req = CustomHelpers::PaymentRequest($store_id,$orderData,$type,$total_amount);
                    if ($payment_req == null) {
                        DB::rollback();
                        return redirect('/admin/insurance/renewal')->with('error','Something went wrong');
                    }else{
                        CustomHelpers::userActionLog($action = 'Purchased Insurance Renewal',$orderData,$customer_id,'Purchase');
                        DB::commit();
                        return redirect('/admin/insurance/payment/'.$orderData)->with('success','Insurance renewal created successfully.');
                    } 
                } 
         
                    
        }catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return redirect('/admin/insurance/renewal')->with('error','some error occurred'.$ex->getMessage());
        }

    }

    public function InsurancePayment($id) {
        $getdata = InsuranceRenewalOrder::where('id',$id)->get()->first();
        $paid = Payment::where('type_id',$id)->where('type','insurance_renewal')->where('status','<>','cancelled')->sum('amount');
        if ($getdata) {
            $data  = array(
                'getdata' => $getdata,
                'paid' => $paid,
                'id' => $id,
                'layout' => 'layouts.main', 
           );
           return view('admin.insurance.insurance_payment',$data);
       }else{
           return redirect('/admin/insurance/renewal')->with('error','Id does not exist.'); 
       }
    }

    public function InsuranceOrder($id) {
        $getdata = InsuranceRenewalOrder::where('id',$id)->get()->first();
        if ($getdata == NULL) {
           return redirect('/admin/insurance/renewal')->with('error','Id does not exist.');
        }
        $oddata = Insurance::where('id',$getdata['od_insurance_id'])->get()->first();
        $tpdata = Insurance::where('id',$getdata['tp_insurance_id'])->get()->first();

        $paid = Payment::where('type_id',$id)->where('type','insurance_renewal')->where('status','received')->sum('amount');

        $approval = ApprovalRequest::where('type_id',$id)->where('type','insurance_renewal')->whereIn('sub_type',['insurance_renewal_edit','insurance_renewal_cancel'])->orderBy('id','desc')->get()->first();

        if ($getdata['total_amount'] == $paid || $getdata['total_amount'] < $paid) {
             $data  = array(
                'getdata' => $getdata,
                'paid' => $paid,
                'approval' => $approval,
                'id' => $id,
                'layout' => 'layouts.main', 
           );
           return view('admin.insurance.insurance_order',$data);
           
        }else{
            return redirect('/admin/insurance/payment/'.$id)->with('error','Full payment not received in account.'); 
        }
    }

    public function InsuranceOrder_DB(Request $request) {
        $validator = Validator::make($request->all(),[
           'amount'  =>  'required',
           'start_date'  =>  'required',
        ],
        [
            'amount.required'  =>  'This Field is required',
            'start_date.required'  =>  'This Field is required',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        try{

            $id = $request->input('id');
            $start_date = $request->input('start_date');
            $od_policy_number = $request->input('od_policy_number');
            $tp_policy_number = $request->input('tp_policy_number');
            $cpa_policy_number = $request->input('cpa_policy_number');

            $orderData = InsuranceRenewalOrder::where('id',$id)->get()->first();
            $insurance_data = InsuranceRenewal::where('id',$orderData['insurance_data_id'])->get()->first();

            $oddata = Insurance::where('id',$orderData['od_insurance_id'])->get()->first();
            $tpdata = Insurance::where('id',$orderData['tp_insurance_id'])->get()->first();
     
            if (($oddata['policy_tenure'] != null || $oddata['policy_tenure'] != 0 || $oddata['insurance_co'] != null || $oddata['insurance_co'] != 0) && empty($od_policy_number) ) {
                return back()->with('error_msg_od','This field is required.')->withInput();
            }
             if (($tpdata['policy_tenure'] != null || $tpdata['policy_tenure'] != 0 || $tpdata['insurance_co'] != null || $tpdata['insurance_co'] != 0) && empty($tp_policy_number)) {
               return back()->with('error_msg_tp','This field is required.')->withInput();
            } if (($oddata['cpa_company'] != null || $oddata['cpa_company'] != 0 || $oddata['cpa_tenure'] != null || $oddata['cpa_tenure'] != 0) && empty($cpa_policy_number)) {
               return back()->with('error_msg_cpa','This field is required.')->withInput();
            }

            if ($orderData['approved'] != 0) {
                 return redirect('/admin/insurance/order/'.$id)->with('error','Insurance renewal approval request in processing.');
            }
            if ($orderData['total_amount'] == $orderData['final_amount']) {
                return redirect('/admin/insurance/order/'.$id)->with('error','Insurance renewal order already created.');
            }

            $payment = Payment::where('type_id',$id)->where('type','insurance_renewal')->where('status','<>','cancelled')->sum('amount');

            $approval = ApprovalRequest::where('type_id',$id)->where('type','insurance_renewal')->whereIn('sub_type',['insurance_renewal_cancel','insurance_renewal_edit'])->where('status','Pending')->get()->first();

            if ($approval) {
                return redirect('/admin/insurance/order/'.$id)->with('error','Firstly approve request.')->withInput();
            }
            DB::beginTransaction();
            if ($request->input('amount') != $orderData['total_amount']) {
                  $update = InsuranceRenewalOrder::where('id',$id)->update([
                    'final_amount' => $request->input('amount')
                ]);
                if ($update == NULL) {
                    DB::rollback();
                    return redirect('/admin/insurance/order/'.$id)->with('error','Something went wrong.');
                }else{
                     CustomHelpers::userActionLog($action = 'Insurance Renewal amount mismatch',$id,$insurance_data['customer_id']);
                    DB::commit();
                    return redirect('/admin/insurance/order/'.$id)->with('error','Your amount is mismatch.');
                }
            }else{
                $update = InsuranceRenewalOrder::where('id',$id)->update([
                    'final_amount' => $request->input('amount')
                ]);
                if ($update == NULL) {
                    DB::rollback();
                    return redirect('/admin/insurance/order/'.$id)->with('error','Something went wrong.');
                }else{
                    if ($orderData['od_insurance_id']) {
                        $updateOd = Insurance::where('id',$orderData['od_insurance_id'])->update([
                            'policy_number' => $od_policy_number,
                            'cpa_policy_number' => $cpa_policy_number,
                            'start_date' => ($start_date != '') ? date('Y-m-d', strtotime($start_date)): null,
                            'status' => 'Done'
                        ]);
                        if ($updateOd == NULL) {
                            DB::rollback();
                            return redirect('/admin/insurance/order/'.$id)->with('error','Something went wrong.');
                        }
                    }

                      if ($orderData['tp_insurance_id']) {
                        $updatetp = Insurance::where('id',$orderData['tp_insurance_id'])->update([
                            'policy_number' => $tp_policy_number,
                            'start_date' => ($start_date != '') ? date('Y-m-d', strtotime($start_date)): null,
                            'status' => 'Done'
                        ]);
                        if ($updatetp == NULL) {
                            DB::rollback();
                            return redirect('/admin/insurance/order/'.$id)->with('error','Something went wrong.');
                        }
                    }

                    $checkFrame = $this->CheckFrame($id);
                     if ($checkFrame != '0') {
                         $call_data = CustomHelpers::UpdateCallData($checkFrame);
                         if ($call_data == '0') {
                          DB::rollback();
                          return redirect('/admin/insurance/order/'.$id)->with('error','Something went wrong.');
                         }
                     }
                     
                     CustomHelpers::userActionLog($action ='Insurance renewal order',$id,$insurance_data['customer_id']);
                      DB::commit();
                       return redirect('/admin/insurance/order/'.$id)->with('success','Order created successfully.');
                }
            }

        }catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return redirect('/admin/insurance/order/'.$id)->with('error','some error occurred'.$ex->getMessage());
        }
    }


    public function InsuranceApprovalRequest_cancel($id) {
        try{
            $insuranceOrder = InsuranceRenewalOrder::where('id',$id)->get()->first();
            $insurance = InsuranceRenewal::where('id',$insuranceOrder['insurance_data_id'])->get()->first();
            $getdata = ApprovalRequest::where('type_id',$id)->where('type','insurance_renewal')->whereIn('sub_type',['insurance_renewal_edit','insurance_renewal_cancel'])->where('status','Pending')->get()->first();
            if ($getdata) { 
                return redirect('/admin/insurance/order/'.$id)->with('error','Approval request already send.');
            }else{
                $insert = ApprovalRequest::insertGetId([
                    'store_id' => $insurance['store_id'],
                    'type' => 'insurance_renewal',
                    'sub_type' => 'insurance_renewal_cancel',
                    'type_id' => $id,
                    'sub_type_id' => $id,
                    'status' => 'Pending',
                    'level1' => Auth::id()
                ]);
                if ($insert == NULL) {
                  return redirect('/admin/insurance/order/'.$id)->with('error','Something went wrong.');
                }else{
                    CustomHelpers::userActionLog($action='Insurance Renewal cancel request',$id,$insurance['customer_id']);
                 return redirect('/admin/insurance/order/'.$id)->with('success','Approval request send to cancel insurance renewal.');
                }
            }
        }catch(\Illuminate\Database\QueryException $ex) {
            return redirect('/admin/insurance/order/'.$id)->with('error','some error occurred'.$ex->getMessage());
        }
    }

      public function InsuranceApprovalRequest_edit($id) {
        try{
             $insuranceOrder = InsuranceRenewalOrder::where('id',$id)->get()->first();
            $insurance = InsuranceRenewal::where('id',$insuranceOrder['insurance_data_id'])->get()->first();
            $getdata = ApprovalRequest::where('type_id',$id)->where('type','insurance_renewal')->whereIn('sub_type',['insurance_renewal_edit','insurance_renewal_cancel'])->where('status','Pending')->get()->first();
            if ($getdata) { 
                return redirect('/admin/insurance/order/'.$id)->with('error','Approval request already send.');
            }else{
                $insert = ApprovalRequest::insertGetId([
                    'store_id' => $insurance['store_id'],
                    'type' => 'insurance_renewal',
                    'sub_type' => 'insurance_renewal_edit',
                    'type_id' => $id,
                    'sub_type_id' => $id,
                    'status' => 'Pending',
                    'level1' => Auth::id()
                ]);
                if ($insert == NULL) {
                  return redirect('/admin/insurance/order/'.$id)->with('error','Something went wrong.');
                }else{
                    CustomHelpers::userActionLog($action='Insurance Renewal edit request ',$id,$insurance['customer_id']);
                    return redirect('/admin/insurance/order/'.$id)->with('success','Approval request send to edit insurance renewal.');
                }
            }
        }catch(\Illuminate\Database\QueryException $ex) {
            return redirect('/admin/insurance/order/'.$id)->with('error','some error occurred'.$ex->getMessage());
        }
    }


    public function InsuranceBreakIn($id) {
        $getdata = InsuranceRenewalOrder::where('id',$id)->get()->first();
        $amount = $getdata['total_amount'];
        $OrderData = InsuranceRenewalOrder::where('id',$id)->where('final_amount',$amount)->get()->first();
        if ($OrderData) {
            $data  = array(
                'getdata' => $getdata,
                'id' => $id,
                'layout' => 'layouts.main', 
           );
           return view('admin.insurance.insurance_breakin',$data);
        }else{
            return redirect('/admin/insurance/order/'.$id)->with('error','Order not created.');
        }      
    }

    public function InsuranceBreakIn_DB(Request $request, $id) {
      $ImageSize = Settings::where('name','image_size')->get()->first();
        $size = $ImageSize['value'];
         if(!isset($size)){
              return redirect('/admin/insurance/breakin/'.$id)->with('error','Image Size For Validation Master Not Found.');
        }
        $mailId = Settings::where('name','breakin_mail')->get()->first();
        $to = $mailId['value'];
         if(!isset($to)){
              return redirect('/admin/insurance/breakin/'.$id)->with('error','Mail id for send mail Master Not Found.');
        }
         $validator = Validator::make($request->all(),[
           'image1'  =>  'required|max:'.$size.'',
           'image2'  =>  'required|max:'.$size.'',
           'image3'  =>  'required|max:'.$size.'',
           'image4'  =>  'required|max:'.$size.'',
           'image5'  =>  'required|max:'.$size.'',
           'image6'  =>  'required|max:'.$size.'',
           'image7'  =>  'required|max:'.$size.'',
           'image8'  =>  'required|max:'.$size.'',
        ],
        [
            'image1.required'  =>  'This Field is required',
            'image2.required'  =>  'This Field is required',
            'image3.required'  =>  'This Field is required',
            'image4.required'  =>  'This Field is required',
            'image5.required'  =>  'This Field is required',
            'image6.required'  =>  'This Field is required',
            'image7.required'  =>  'This Field is required',
            'image8.required'  =>  'This Field is required',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        try{
            DB::beginTransaction();
            $orderdata = InsuranceRenewalOrder::where('id',$id)->get()->first();
            $getdata = InsuranceRenewal::where('id',$orderdata['insurance_data_id'])->get()->first();
            if ($orderdata['status'] == 'Done' || $orderdata['status'] == 'Cancelled') {
               return redirect('/admin/insurance/breakin/'.$id)->with('error','Insurance renewal already '.$orderdata['status'].'.');
            }
            if($request->hasFile('image1')) {
                $file = $request->file('image1');
                $destinationPath = public_path().'/images/insurance';
                $image1 = $file->getClientOriginalName();
            }
            if($request->hasFile('image2')) {
                $file = $request->file('image2');
                $destinationPath = public_path().'/images/insurance';
                $image2 = $file->getClientOriginalName();
            } 
            if($request->hasFile('image3')) {
                $file = $request->file('image3');
                $destinationPath = public_path().'/images/insurance';
                $image3 = $file->getClientOriginalName();
            }
            if($request->hasFile('image4')) {
                $file = $request->file('image4');
                $destinationPath = public_path().'/images/insurance';
                $image4 = $file->getClientOriginalName();
            }
            if($request->hasFile('image5')) {
                $file = $request->file('image5');
                $destinationPath = public_path().'/images/insurance';
                $image5 = $file->getClientOriginalName();
            }
            if($request->hasFile('image6')) {
                $file = $request->file('image6');
                $destinationPath = public_path().'/images/insurance';
                $image6 = $file->getClientOriginalName();
            }
            if($request->hasFile('image7')) {
                $file = $request->file('image7');
                $destinationPath = public_path().'/images/insurance';
                $image7 = $file->getClientOriginalName();
            }
            if($request->hasFile('image8')) {
                $file = $request->file('image8');
                $destinationPath = public_path().'/images/insurance';
                $image8 = $file->getClientOriginalName();
            }
            $destinationPath = public_path().'/images/insurance';
            $fileName = ['image1' => $image1, 'image2' => $image2, 'image3' => $image3, 'image4' => $image4, 'image5' => $image5, 'image6' => $image6, 'image7' => $image7, 'image8' => $image8]; 
            $hasDuplicates = count($fileName) > count(array_unique($fileName)); 
            if ($hasDuplicates == 1) {
              return redirect('/admin/insurance/breakin/'.$id)->with('error','This file is already exist.');
            }
            for ($i = 1; $i <= count($fileName); $i++ ) {
              $file = $request->file('image'.$i);
              $image = $file->getClientOriginalName();
              $file->move($destinationPath,$image);
            }

            $user_id = Auth::id();
            $users = Users::where('id','=',$user_id)->get()->first();

            $user_name = $users->name;
            $from = $users->email;  
            $crm_name = 'JPHonda';
            $data = [
                'image1' => env('APP_URL')."/images/insurance/".$image1,
                'image2' => env('APP_URL')."/images/insurance/".$image2,
                'image3' => env('APP_URL')."/images/insurance/".$image3,
                'image4' => env('APP_URL')."/images/insurance/".$image4,
                'image5' => env('APP_URL')."/images/insurance/".$image5,
                'image6' => env('APP_URL')."/images/insurance/".$image6,
                'image7' => env('APP_URL')."/images/insurance/".$image7,
                'image8' => env('APP_URL')."/images/insurance/".$image8
            ];
            if ($data) {
                Mail::send('admin.emails.insurance_mail', ['name' => $crm_name,'data' => $data], function($message) use ($from,$user_name,$to,$crm_name)
                        {
                            $message->from($from, $user_name)->to($to, $crm_name)->subject('Vehicle Number');
                        });
                if ($orderdata['breakin'] == '1') {
                    $breakin = '2';
                }else{
                    $breakin = '1';
                }
                $update = InsuranceRenewalOrder::where('id',$id)->update([
                    'status' => 'Done',
                    'breakin' => $breakin
                ]);
                if ($update == NULL) {
                  DB::rollback();
                    return redirect('/admin/insurance/breakin/'.$id)->with('error','Something went wrong.');
                }else{
                     CustomHelpers::userActionLog($action='Insurance Renewal breakin',$id,$getdata['customer_id']);
                     DB::commit();
                    return redirect('/admin/insurance/breakin/'.$id)->with('success','BreakIn added successfully.');
                }
            }else{
              DB::rollback();
                return redirect('/admin/insurance/breakin/'.$id)->with('error','Something went wrong.');
            }

        }catch(\Illuminate\Database\QueryException $ex) {
          DB::rollback();
            return redirect('/admin/insurance/breakin/'.$id)->with('error','some error occurred'.$ex->getMessage());
        }
    }

   public function CheckFrame($id){
      $orderData = InsuranceRenewalOrder::where('id',$id)->get()->first();
      if ($orderData['insurance_detail_id']) {
         $getFrame = InsuranceRenewalDetail::where('id',$orderData['insurance_detail_id'])->get()->first();
        return $getFrame['frame_number'];
      }elseif($orderData['insurance_data_id']){
        $getFrame = InsuranceRenewal::leftJoin('sale_order','sale_order.sale_id','insurance_data.sale_id')
            ->where('insurance_data.id',$orderData['insurance_data_id'])
            ->select('sale_order.product_frame_number')
            ->get()->first();
            return $getFrame['product_frame_number'];
      }else{
        return '0';
      }
    }

    public function InsuranceRenewal_cancel($id) {
        try{
            $orderData = InsuranceRenewalOrder::where('id',$id)->get()->first();
             $getdata = InsuranceRenewal::where('id',$orderData['insurance_data_id'])->get()->first();
            $approval = ApprovalRequest::where('type_id',$id)->where('type','insurance_renewal')->where('sub_type','insurance_renewal_cancel')->where('status','Pending')->get()->first();
            if ($approval) {
                return redirect('/admin/insurance/renewal/list')->with('error','Firstly approve request.');
            }
            if ($orderData['approved'] != '2') {
                return redirect('/admin/insurance/renewal/list')->with('error','Insurance renewal is not for cancel.');
            }
            DB::beginTransaction();
            $cancel = InsuranceRenewalOrder::where('id',$id)->update([
                'status' => 'Cancelled'
            ]);
            if ($cancel == null) {
                DB::rollback();
                return redirect('/admin/insurance/renewal/list')->with('error','Something went wrong.');
            }else{

                if ($orderData['od_insurance_id']) {
                    $od_update = Insurance::where('id',$orderData['od_insurance_id'])->update([
                        'status' => 'Cancelled'
                    ]);
                    if ($od_update == NULL) {
                        DB::rollback();
                        return redirect('/admin/insurance/renewal/list')->with('error','Something went wrong.');
                    }
                }

                 if ($orderData['tp_insurance_id']) {
                    $tp_update = Insurance::where('id',$orderData['tp_insurance_id'])->update([
                        'status' => 'Cancelled'
                    ]);
                    if ($tp_update == NULL) {
                        DB::rollback();
                        return redirect('/admin/insurance/renewal/list')->with('error','Something went wrong.');
                    }
                }
                 $type = 'insurance_renewal_refund';
                $payment_req = CustomHelpers::PaymentRequest($getdata['store_id'],$id,$type,$orderData['total_amount']);

                if ($payment_req == null) {
                     DB::rollback();
                    return redirect('/admin/insurance/renewal/list')->with('error','Something went wrong');
                }else{
                     CustomHelpers::userActionLog($action='Insurance renewal cancel',$id,$getdata['customer_id']);
                     DB::commit();
                    return redirect('/admin/insurance/renewal/list')->with('success','Insurance renewal cancelled.');
                }
            }
        }catch(\Illuminate\Database\QueryException $ex) {
             DB::rollback();
            return redirect('/admin/insurance/renewal/list')->with('error','some error occurred'.$ex->getMessage());
        }
    }
 
    public function InsuranceRenewal_edit($id) {
        $Insdata = InsuranceRenewalOrder::leftJoin('insurance as od_insurance',function($join){
                        $join->on('od_insurance.id','=','insurance_renewal_order.od_insurance_id')
                            ->where('od_insurance.insurance_type','OD');
                    })
                    ->leftJoin('insurance as tp_insurance',function($join){
                        $join->on('tp_insurance.id','=','insurance_renewal_order.tp_insurance_id')
                            ->where('tp_insurance.insurance_type','TP');
                    })
                    ->leftJoin('insurance_data','insurance_data.id','insurance_renewal_order.insurance_data_id')
                    ->leftJoin('customer','customer.id','insurance_data.customer_id')
                    ->leftJoin('insurance_renewal_detail','insurance_data.insurance_detail_id','insurance_renewal_detail.id')
                    ->select(
                            'insurance_data.store_id',
                            'insurance_renewal_order.id',
                            'insurance_renewal_order.total_amount',
                            'insurance_renewal_order.breakin',
                            'insurance_renewal_order.km_read',
                            'od_insurance.start_date',
                            'od_insurance.policy_tenure as od_tenure',
                            'od_insurance.insurance_co as od_company',
                            'od_insurance.cpa_tenure',
                            'od_insurance.cpa_company',
                            'tp_insurance.policy_tenure as tp_tenure',
                            'tp_insurance.insurance_co as tp_company',
                            'insurance_renewal_detail.frame_number',
                            'insurance_renewal_detail.od_policy_end_date',
                            'insurance_renewal_detail.tp_policy_end_date',
                            'insurance_renewal_detail.cpa_policy_end_date',
                            'insurance_renewal_detail.od_old_policy_number',
                            'insurance_renewal_detail.tp_old_policy_number',
                            'insurance_renewal_order.status',
                            'insurance_renewal_order.approved')
            ->where('insurance_renewal_order.id',$id)
            ->get()
            ->first();
        if($Insdata['frame_number']){
          $frame = $Insdata['frame_number'];
        }else{
           $frame_number =  InsuranceRenewalOrder::leftJoin('insurance_data','insurance_renewal_order.insurance_data_id','insurance_data.id')
                    ->leftJoin('sale_order','insurance_data.sale_id','sale_order.sale_id')
                    ->select(
                            'sale_order.product_frame_number')
            ->where('insurance_renewal_order.id',$id)
            ->get()
            ->first();
            $frame = $frame_number['product_frame_number'];
        }

        $ins_company = InsuranceCompany::all();
        $cpa_company = InsuranceCompany::where('cpa','1')->get();
        $approval = ApprovalRequest::where('type_id',$id)->where('type','insurance_renewal')->where('sub_type','insurance_renewal_edit')->where('status','Pending')->get()->first();
        $store = Store::whereIn('id',CustomHelpers::user_store())
                    ->select('id',DB::raw('concat(store.name,"-",store.store_type) as name'))->get();
        if ($approval) {
            return redirect('/admin/insurance/renewal/list')->with('error','Firstly approve request.');
        }
        if ($Insdata['approved'] == '1') {
            $data  = array(
                'frame' => $frame,
                'store' => $store,
                'Insdata' => $Insdata,
                'ins_company' => $ins_company,
                'id' => $id,
                'cpa_company' => $cpa_company,
                'layout' => 'layouts.main', 
            );
           return view('admin.insurance.insurance_renewal_edit',$data);   
        }else{
            return redirect('/admin/insurance/renewal/list')->with('error','Insurance renewal is not for edit.');
        }
        
    }

    public function InsuranceRenewal_update(Request $request, $id) {
         $validator = Validator::make($request->all(),[
           'tp'  =>  'required',
           // 'cpa'  =>  'required',
           'od'  =>  'required',
           'frame_number'  =>  'required',
           'od_tenure'  =>  'required_if:od,No',
           'od_end_date'  =>  'required_if:od,Yes',
           'tp_end_date'=>'required_if:tp,Yes',
           'ins_company'=>'required_if:tp,Yes',
           'od_company'=>'required_if:od,No',
           'cpa_end_date'=>'required_if:cpa,Yes',
           'cpa_ins_company'=>'required_if:cpa,Yes',
           'total_amount' => 'required',
           // 'cpa_tenure'=>'required_if:cpa,No',
           'tp_tenure'=>'required_if:tp,No',
           'old_od_policy_number'=>'required', 
           'old_tp_policy_number'=>'required',
           'km_read'=>'required',
        ],
        [
            'frame_number.required'  =>  'This Field is required',
            'tp.required'  =>  'This Field is required',
            'od.required'  =>  'This Field is required',
            'od_tenure.required'  =>  'This Field is required',
            'od_end_date.required'  =>  'This Field is required',
            // 'cpa.required'  =>  'This Field is required',
            'tp_end_date.required'  =>  'This Field is required',
            'ins_company.required'  =>  'This Field is required',
            'cpa_end_date.required'  =>  'This Field is required',
            'cpa_ins_company.required'  =>  'This Field is required',
            'total_amount.required'  =>  'This Field is required',
            // 'cpa_tenure'=> 'This field is required',
            'tp_tenure'=> 'This field is required',
            'od_company'=> 'This field is required',
            'old_od_policy_number'=>'This field is required',
            'old_tp_policy_number'=>'This field is required',
            'km_read'=>'This field is required',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        try{
        DB::beginTransaction();
        $od_tenure = $request->input('od_tenure');
        $tp_tenure = $request->input('tp_tenure');
        $cpa_tenure = $request->input('cpa_tenure');
        $tp_end_date = $request->input('tp_end_date');
        $cpa_end_date = $request->input('cpa_end_date');
        $od_end_date = $request->input('od_end_date');
        $od_end_date = $request->input('od_end_date');
        $old_od_policy_number = $request->input('old_od_policy_number');
        $old_tp_policy_number = $request->input('old_tp_policy_number');
        $km_read = $request->input('km_read');
        $amount = $request->input('total_amount');
        $od_company = $request->input('od_company');
        $tp_company = $request->input('ins_company');
        $cpa_company = $request->input('cpa_ins_company');

        if (!empty($tp_tenure) && $od_tenure != $tp_tenure ) {
            return back()->with('error_msg','Please select OD tenure equal of TP tenure.')->withInput();
        }
        if (!empty($cpa_tenure) && $cpa_tenure > $od_tenure) {
            return back()->with('err_msg','Please select CPA tenure equal or less than of OD tenure.')->withInput();
        }

        $orderdata = InsuranceRenewalOrder::where('id',$id)->get()->first();
        $getdata = InsuranceRenewal::where('id',$orderdata['insurance_data_id'])->get()->first();
        $InsDetail = InsuranceRenewalDetail::where('insurance_data_id',$orderdata['id'])->get()->first();
        if ($InsDetail) {
            $InsDetailsData = InsuranceRenewalDetail::where('insurance_data_id',$orderdata['id'])->update([
                    'od_old_policy_number' => $old_od_policy_number,
                    'tp_old_policy_number' => $old_tp_policy_number,
                    'od_policy_end_date' => ($od_end_date != '') ? date('Y-m-d', strtotime($od_end_date)): null,
                    'tp_policy_end_date' => ($tp_end_date != '') ? date('Y-m-d', strtotime($od_end_date)): null,
                    'cpa_policy_end_date' => ($cpa_end_date != '') ? date('Y-m-d', strtotime($od_end_date)): null,
                ]);
            if ($InsDetailsData == NULL) {
                DB::rollback();
                return redirect('/admin/insurance/renewal/update/'.$id)->with('error','Something went wrong');
            }
        }


        $updateOrder = InsuranceRenewalOrder::where('id',$id)->update([
            'total_amount' => $amount,
            'km_read' => $km_read
        ]);
        if ($updateOrder == NULL) {
            DB::rollback();
            return redirect('/admin/insurance/renewal/update/'.$id)->with('error','Something went wrong');
        }else{
            if ($orderdata['od_insurance_id'] > 0) {
                $od_update = Insurance::where('id',$orderdata['od_insurance_id'])->update([
                    'policy_tenure' => $od_tenure,
                    'insurance_co' => $od_company,
                    'cpa_company' => $cpa_company,
                    'cpa_tenure' => $cpa_tenure
                ]);
                if ($od_update == NULL) {
                    DB::rollback();
                    return redirect('/admin/insurance/renewal/update/'.$id)->with('error','Something went wrong');
                }
            }

            if ($orderdata['tp_insurance_id'] > 0) {
                $tp_update = Insurance::where('id',$orderdata['od_insurance_id'])->update([
                    'policy_tenure' => $tp_tenure,
                    'insurance_co' => $tp_company,
                ]);
                if($od_update == NULL) {
                    DB::rollback();
                    return redirect('/admin/insurance/renewal/update/'.$id)->with('error','Something went wrong');
                }
            }

            $insUpdate = InsuranceRenewalOrder::where('id',$id)->update([
                'approved' => '0'
            ]);

            if ($insUpdate == NULL) {
                DB::rollback();
                return redirect('/admin/insurance/renewal/update/'.$id)->with('error','Something went wrong');
            }

            if ($orderdata['total_amount'] > $request->input('total_amount')) {
                $RefAmount = $orderdata['total_amount'] - $amount;
                 $type = 'insurance_renewal_refund';
                 $payment_refund = CustomHelpers::PaymentRequest($getdata['store_id'],$id,$type,$RefAmount);

                if ($payment_refund == null) {
                     DB::rollback();
                    return redirect('/admin/insurance/renewal/update/'.$id)->with('error','Something went wrong');
                }else{
                    CustomHelpers::userActionLog($action='Insurance renewal edit',$id,$getdata['customer_id']);
                     DB::commit();
                    return redirect('/admin/insurance/order/'.$id)->with('success','Insurance renewal updated successfully.');
                }

            }
            if ($orderdata['total_amount'] < $request->input('total_amount')) {
                 $RefAmount = $amount - $orderdata['total_amount'];
                 $type = 'insurance_renewal';
                 $payment_refund = CustomHelpers::PaymentRequest($getdata['store_id'],$id,$type,$RefAmount);

                if ($payment_refund == null) {
                     DB::rollback();
                    return redirect('/admin/insurance/renewal/update/'.$id)->with('error','Something went wrong');
                }else{
                    CustomHelpers::userActionLog($action='Insurance renewal edit',$id,$getdata['customer_id']);
                     DB::commit();
                   return redirect('/admin/insurance/payment/'.$id)->with('success','Insurance renewal updated successfully.');
                }
            }

             if ($orderdata['total_amount'] == $request->input('total_amount')) {
                CustomHelpers::userActionLog($action='Insurance renewal edit',$id,$getdata['customer_id']);
                DB::commit();
                   return redirect('/admin/insurance/order/'.$id)->with('success','Insurance renewal updated successfully.');
             }
        }

        }catch(\Illuminate\Database\QueryException $ex) {
             DB::rollback();
            return redirect('/admin/insurance/renewal/update/'.$id)->with('error','some error occurred'.$ex->getMessage());
        }
    }

    public function InsuranceRenewal_list() {
         $data  = [
                'layout' => 'layouts.main', 
           ];
        return view('admin.insurance.insurance_renewal_list',$data);
        
    }

    public function InsuranceRenewalList_api(Request $request) {
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
        $api_data = InsuranceRenewalOrder::leftJoin('insurance as od_insurance',function($join){
                        $join->on('od_insurance.id','=','insurance_renewal_order.od_insurance_id')
                            ->where('od_insurance.insurance_type','OD');
                    })
                    ->leftJoin('insurance as tp_insurance',function($join){
                        $join->on('tp_insurance.id','=','insurance_renewal_order.tp_insurance_id')
                            ->where('tp_insurance.insurance_type','TP');
                    })
                    ->leftJoin('approval_request as approval_request_edit',function($join){
                        $join->on('approval_request_edit.type_id','=','insurance_renewal_order.id')
                            ->where('approval_request_edit.type','insurance_renewal')
                            ->where('approval_request_edit.sub_type','insurance_renewal_edit')->orderBy('approval_request_edit.id','desc');
                    })
                    ->leftJoin('approval_request as approval_request_cancel',function($join){
                        $join->on('approval_request_cancel.type_id','=','insurance_renewal_order.id')
                            ->where('approval_request_cancel.type','insurance_renewal')
                            ->where('approval_request_cancel.sub_type','insurance_renewal_cancel')->orderBy('approval_request_cancel.id','desc');
                    })
                    ->leftJoin('payment',function($join){
                        $join->on('payment.type_id','=','insurance_renewal_order.id')
                            ->where('payment.type','insurance_renewal')
                            ->where('payment.status','<>','cancelled');
                    })
                    ->leftJoin('insurance_data','insurance_data.id','insurance_renewal_order.insurance_data_id')
                    ->leftJoin('customer','customer.id','insurance_data.customer_id')
                    ->leftJoin('insurance_renewal_detail',function($join){
                        $join->on('insurance_data.insurance_detail_id','insurance_renewal_detail.id');
                    })
                    ->leftJoin('insurance_company as od_company','od_company.id','od_insurance.insurance_co')
                    ->leftJoin('insurance_company as cpa_company','cpa_company.id','od_insurance.cpa_company')
                    ->leftJoin('insurance_company as tp_company','tp_company.id','tp_insurance.insurance_co')
                    ->select(
                            'insurance_renewal_order.id',
                            'insurance_renewal_order.total_amount',
                            'insurance_renewal_order.final_amount',
                            'insurance_renewal_order.breakin',
                            'approval_request_edit.sub_type as edit_type',
                            'od_insurance.start_date',
                            'od_insurance.policy_tenure as od_tenure',
                            'od_insurance.insurance_co as od_company',
                            'od_insurance.cpa_tenure',
                            'od_insurance.cpa_company',
                            'tp_insurance.policy_tenure as tp_tenure',
                            'tp_insurance.insurance_co as tp_company',
                            'approval_request_cancel.sub_type as cancel_type',
                            'insurance_renewal_detail.od_policy_end_date',
                            'insurance_renewal_detail.tp_policy_end_date',
                            'insurance_renewal_detail.cpa_policy_end_date',
                            'insurance_renewal_order.status',
                            'insurance_renewal_order.approved',
                            'od_company.name as od_company_name',
                            'tp_company.name as tp_company_name',
                            'cpa_company.name as cpa_company_name',
                            'insurance_renewal_detail.frame_number',
                            DB::raw('sum(payment.amount) as payment_amount'))
                    ->groupBy('insurance_renewal_order.id');
            
           
            if(!empty($serach_value)) {
               $api_data->where(function($query) use ($serach_value){
                        $query->where('insurance_renewal_detail.frame_number','like',"%".$serach_value."%")
                        ->orwhere('od_insurance.policy_tenure','like',"%".$serach_value."%")
                        ->orwhere('tp_insurance.policy_tenure','like',"%".$serach_value."%")
                        ->orwhere('insurance_renewal_detail.od_policy_end_date','like',"%".$serach_value."%")
                        ->orwhere('tp_company.name','like',"%".$serach_value."%")
                        ->orwhere('od_insurance.cpa_tenure','like',"%".$serach_value."%")
                        ->orwhere('od_insurance.cpa_company','like',"%".$serach_value."%")
                        ->orwhere('insurance_renewal_order.breakin','like',"%".$serach_value."%")
                        ->orwhere('insurance_renewal_order.approved','like',"%".$serach_value."%")
                        ->orwhere('od_insurance.start_date','like',"%".$serach_value."%")
                        ->orwhere('insurance_renewal_order.final_amount','like',"%".$serach_value."%")
                        ->orwhere('insurance_renewal_order.total_amount','like',"%".$serach_value."%")
                        ->orwhere('payment.amount','like',"%".$serach_value."%")
                        // ->orwhere('insurance_data.status','like',"%".$serach_value."%")
                        ;
                    });
               
            }
            if(isset($request->input('order')[0]['column'])) {
                $data = [
                    'insurance_renewal_detail.frame_number',
                    'od_insurance.policy_tenure',
                    'insurance_renewal_detail.od_policy_end_date',
                    'od_company.name',
                    'tp_insurance.policy_tenure',
                    'insurance_renewal_detail.tp_policy_end_date',
                    'tp_company.name',
                    'od_insurance.cpa_tenure',
                    'insurance_renewal_detail.cpa_policy_end_date',
                    'cpa_company.name',
                    'od_insurance.start_date',
                    'insurance_renewal_order.breakin',
                    'insurance_renewal_order.total_amount',
                    'insurance_renewal_order.total_amount',
                    'insurance_renewal_order.status',
                    // 'insurance_renewal_order.breakin',
                ];
                
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            } else{
                $api_data->orderBy('insurance_renewal_order.id','desc');   
            }
        
        $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        foreach ($api_data as &$value) {
            if ($value['id']) {
                $oddate = $value['od_policy_end_date'];
                $tpdate = $value['tp_policy_end_date'];
                $cpadate = $value['cpa_policy_end_date'];
                $start_date = $value['start_date'];
                if ($oddate != null) {
                    $value['od_policy_end_date'] = date('d-m-Y', strtotime($oddate));
                }else{
                    $value['od_policy_end_date'] = '';
                }
                if ($tpdate != null) {
                    $value['tp_policy_end_date'] = date('d-m-Y', strtotime($tpdate));
                }else{
                    $value['tp_policy_end_date'] = '';
                }

                if ($cpadate != null) {
                    $value['cpa_policy_end_date'] = date('d-m-Y', strtotime($cpadate));
                }else{
                    $value['cpa_policy_end_date'] = '';
                }
                if ($start_date != null) {
                    $value['start_date'] = date('d-m-Y', strtotime($start_date));
                }else{
                    $value['start_date'] = '';
                }
            }

              if($value['frame_number']){
                  $value['frame'] = $value['frame_number'];
                }else{
                   $frame_number =  InsuranceRenewalOrder::leftJoin('insurance_data','insurance_renewal_order.insurance_data_id','insurance_data.id')
                            ->leftJoin('sale_order','insurance_data.sale_id','sale_order.sale_id')
                            ->select(
                                    'sale_order.product_frame_number')
                    ->where('insurance_renewal_order.id',$value['id'])
                    ->get()
                    ->first();
                     $value['frame'] = $frame_number['product_frame_number'];
                }
        } 
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }


    public function InsuranceRenewal_view($id) {
        $Insdata = InsuranceRenewalOrder::leftJoin('insurance as od_insurance',function($join){
                        $join->on('od_insurance.id','=','insurance_renewal_order.od_insurance_id')
                            ->where('od_insurance.insurance_type','OD');
                    })
                    ->leftJoin('insurance as tp_insurance',function($join){
                        $join->on('tp_insurance.id','=','insurance_renewal_order.tp_insurance_id')
                            ->where('tp_insurance.insurance_type','TP');
                    })
                    ->leftJoin('insurance_data','insurance_data.id','insurance_renewal_order.insurance_data_id')
                    ->leftJoin('sale','sale.id','insurance_data.sale_id')
                    ->leftJoin('product','sale.product_id','product.id')
                    ->leftJoin('customer','customer.id','insurance_data.customer_id')
                    ->leftJoin('insurance_renewal_detail','insurance_data.insurance_detail_id','insurance_renewal_detail.id')
                    ->leftJoin('insurance_company as od_company','od_company.id','od_insurance.insurance_co')
                    ->leftJoin('insurance_company as cpa_company','cpa_company.id','od_insurance.cpa_company')
                    ->leftJoin('insurance_company as tp_company','tp_company.id','tp_insurance.insurance_co')
                     ->leftJoin('payment',function($join){
                        $join->on('payment.type_id','=','insurance_renewal_order.id')
                            ->where('payment.type','insurance_renewal')
                            ->where('payment.status','<>','cancelled');
                    })
                     ->leftJoin('selling_dealer',function($join){
                        $join->on('selling_dealer.id','=','insurance_renewal_detail.selling_dealer');
                    })
                    ->select(
                            'insurance_data.store_id',
                            'insurance_data.sale_id',
                            'insurance_data.customer_id',
                            'insurance_renewal_order.id',
                            'insurance_renewal_order.insurance_data_id',
                            'insurance_renewal_order.total_amount',
                            'insurance_renewal_order.breakin',
                            'insurance_renewal_order.km_read',
                            'od_insurance.start_date',
                            'sale.sale_no',
                            'customer.name',
                            'customer.mobile',
                            'od_insurance.policy_tenure as od_tenure',
                            'od_insurance.cpa_tenure',
                            'tp_insurance.policy_number as tp_policy_number',
                            'od_insurance.policy_number as od_policy_number',
                            'od_insurance.cpa_policy_number',
                            'tp_insurance.policy_tenure as tp_tenure',
                            'insurance_renewal_detail.frame_number',
                            'insurance_renewal_detail.od_policy_end_date',
                            'insurance_renewal_detail.tp_policy_end_date',
                            'insurance_renewal_detail.cpa_policy_end_date',
                            'insurance_renewal_detail.od_old_policy_number',
                            'insurance_renewal_detail.tp_old_policy_number',
                            'insurance_renewal_order.status',
                            'od_company.name as od_company_name',
                            'tp_company.name as tp_company_name',
                            'cpa_company.name as cpa_company_name',
                            'od_insurance.start_date',
                            'selling_dealer.name as selling_dealer_name',
                            DB::raw('sum(payment.amount) as payment_amount'),
                            'sale.product_id',
                            DB::raw('concat(product.model_category,"-",product.model_name,"-",product.model_variant) as product_name'),
                            'insurance_renewal_order.approved')
            ->where('insurance_renewal_order.id',$id)
            ->groupBy('insurance_renewal_order.id')
            ->get()
            ->first(); 

        if ($Insdata['product_id'] == null) {
          $get = InsuranceRenewalDetail::leftJoin('product','insurance_renewal_detail.product_id','product.id')
          ->where('insurance_renewal_detail.insurance_data_id',$Insdata['insurance_data_id'])
          ->select(  DB::raw('concat(product.model_category,"-",product.model_name,"-",product.model_variant) as product_name'))->get()->first();
          $product_name = $get['product_name'];
        }else{
          $product_name = $Insdata['product_name'];
        }
        if(!empty($Insdata['frame_number']) && $Insdata['sale_id'] == NULL){
          $frame = $Insdata['frame_number'];
        }else{
           $frame_number =  InsuranceRenewalOrder::leftJoin('insurance_data','insurance_renewal_order.insurance_data_id','insurance_data.id')
                    ->leftJoin('sale_order','insurance_data.sale_id','sale_order.sale_id')
                    ->select(
                            'sale_order.product_frame_number')
            ->where('insurance_renewal_order.id',$id)
            ->get()
            ->first();
            $frame = $frame_number['product_frame_number'];
        }
       if ($Insdata['sale_id'] != null) {
          $od_data = Insurance::leftJoin('sale','sale.id','insurance.sale_id')
                          ->where('insurance.sale_id',$Insdata['sale_id'])
                          ->where('insurance.customer_id',$Insdata['customer_id'])
                          ->where('insurance.insurance_type','OD')
                          ->where('insurance.policy_number','<>',null)
                          ->select('insurance.policy_number as old_od_policy_number','insurance.cpa_policy_number')->get()->first();
          $tp_data = Insurance::leftJoin('sale','sale.id','insurance.sale_id')
                          ->where('insurance.sale_id',$Insdata['sale_id'])
                          ->where('insurance.customer_id',$Insdata['customer_id'])
                          ->where('insurance.policy_number','<>',null)
                          ->where('insurance.insurance_type','TP')
                          ->select('insurance.policy_number as old_tp_policy_number')->get()->first();
          $old_od_policy_number = $od_data['old_od_policy_number'];
          $old_cpa_policy_number = $od_data['cpa_policy_number'];
          $old_tp_policy_number = $tp_data['old_tp_policy_number'];
       }else{
          $old_od_policy_number = $Insdata['od_old_policy_number'];
          $old_cpa_policy_number = '';
          $old_tp_policy_number = $Insdata['tp_old_policy_number'];
       }

  
            $data  = array(
              'old_od_policy_number' => $old_od_policy_number,
              'old_cpa_policy_number' => $old_cpa_policy_number,
              'old_tp_policy_number' => $old_tp_policy_number,
                'frame' => $frame,
                'Insdata' => $Insdata,
                'product_name' => $product_name,
                'id' => $id,
                'layout' => 'layouts.main', 
            );
           return view('admin.insurance.insurance_renewal_view',$data);   
        
    }

}