<?php

namespace App\Http\Controllers\admin;
use Illuminate\Http\Request;
use \stdClass;
use \App\Model\Store;
use \App\Model\ProductModel;
use \App\Model\ProductDetails;
use \App\Model\Customers;
use \App\Custom\CustomHelpers;
use App\Model\DamageDetail;
use \App\Model\Stock;
use App\Model\Users;
use \App\Model\PurchaseOrderRequest;
use \App\Model\Countries;
use \App\Model\City;
use \App\Model\State;
use \App\Model\PartRequest;
use \App\Model\AMCProduct;
use \App\Model\Master;
use \App\Model\Sale;
use \App\Model\PartStock;
use \App\Model\ServiceBooking;
use\App\Model\WarrantyInvoice;
use \App\Model\Parts;
use \App\Model\PartWarranty;
use \App\Model\SaleAccessories;
use \App\Model\SaleFinance;
use \App\Model\Scheme;
use \App\Model\AmcBookletIssue;
use \App\Model\ServiceChecklistMaster;
use \App\Model\ExtendedWarranty;
use \App\Model\Accessories;
use \App\Model\MasterAccessories;
use \App\Model\HiRise;
use \App\Model\Insurance;
use \App\Model\SaleOrder;
use \App\Model\RtoModel;
use \App\Model\Payment;
use \App\Model\Settings;
use \App\Model\OrderPendingModel;
use \App\Model\BookingModel;
use \App\Model\BestDealSale;
use \App\Model\PDI;
use \App\Model\JobcardSubtype;
use \App\Model\PDI_details;
use \App\Model\FinalInspection;
use\App\Model\ServiceModel;
use\App\Model\CustomerDetails;
use\App\Model\HJCModel;
use\App\Model\SellingDealer;
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
use \App\Model\ServicePartRequest;
use\App\Model\TestRide;
use\App\Model\Feedback;
use\App\Model\AMCModel;
use\App\Model\ServiceCharge;
use\App\Model\Pickup;
use\App\Model\JobcardDetail;
use Illuminate\Support\Facades\Storage;
use Config;
use Mail;
use File;



class JobCardController extends Controller {
    public function __construct() {
        //$this->middleware('auth');
        // date_default_timezone_set('Asia/Kolkata');
    }
  

    public function jobCardList()
    {

        $store = Store::whereIn('id',CustomHelpers::user_store())
                    ->select('id',DB::raw('concat(store.name,"-",store.store_type) as name'))->get();
        $data = [
            'store'=>$store,
            'layout' => 'layouts.main'
        ];
        
        return view('admin.service.jobCardList',$data);
    }
    public function jobCardList_api(Request $request) {
        $search = $request->input('search');
        $store_name = $request->input('store_name');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start;
        $limit =  empty($limit) ? 10 : $limit;

            $store = Store::whereIn('id',CustomHelpers::user_store())
                    ->select('id')->get()->toArray();

            $api_data = Job_card::join('service','job_card.service_id','service.id')
                    ->whereIn('job_card.store_id',$store)
                    ->select('job_card.id',
                            'job_card.tag',
                            'job_card.store_id',
                            'job_card.in_time',
                            'job_card.out_time',
                            'job_card.job_card_type',
                            'job_card.customer_status',
                            'job_card.vehicle_km',
                            'job_card.service_duration',
                            'job_card.status',
                            'job_card.estimated_delivery_time',
                            'service.frame',
                            'service.created_at',
                            'job_card.service_status',
                            'job_card.jobcard_create',
                            'service.registration',
                            'service.customer_id',
                            'service.selling_dealer_id',
                            'service.product_id',
                            'service.manufacturing_year',
                            // 'service.vehicle_type',
                            'service.sale_date'
                          );
            
            DB::enableQueryLog();
            if(!empty($serach_value))
            {
               $api_data->where(function($query) use ($serach_value){
                        $query->where('tag','like',"%".$serach_value."%")
                        ->orwhere('service.frame','like',"%".$serach_value."%")
                        ->orwhere('service.registration','like',"%".$serach_value."%")
                        ->orwhere('in_time','like',"%".$serach_value."%")
                        ->orwhere('out_time','like',"%".$serach_value."%")
                        ->orwhere('job_card_type','like',"%".$serach_value."%")
                        ->orwhere('customer_status','like',"%".$serach_value."%")
                        ->orwhere('vehicle_km','like',"%".$serach_value."%")
                        ->orwhere('service_duration','like',"%".$serach_value."%")
                        ->orwhere('estimated_delivery_time','like',"%".$serach_value."%");
                    });
               
            }
             if(!empty($store_name))
            {
               $api_data->where(function($query) use ($store_name){
                        $query->where('job_card.store_id','like',"%".$store_name."%");
                    });
               
            }

            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                    'tag',
                    'service.frame',
                    'service.registration',
                    'in_time',
                    'out_time',
                    'job_card_type',
                    'customer_status',
                    'vehicle_km',
                    'service_duration',
                    'estimated_delivery_time'
                ];
                
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('service.created_at','desc');   
        
        $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray(); 
        foreach ($api_data as &$value) {
            if ($value['tag']) {
                $user = Users::where('id',Auth::id())->get('role')->first();
                $value['role'] = $user->role;
            }
        }      
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

    public function jobcard_screen1(Request $request, $jobCardId) {
        
        $jobcard_type = Master::where('type','job_card_type')->select('key','value')->get();
        $jobCard = job_card::leftJoin('service','job_card.service_id','service.id')
                ->leftjoin('job_card_details','job_card.id','job_card_details.job_card_id')
          ->where('job_card.id',$jobCardId)->select(
          'job_card.*','service.customer_id', 'service.sale_id', 'service.frame', 'service.registration','service.selling_dealer_id', 'service.product_id','service.sale_date','service.manufacturing_year','job_card_details.insurance','job_card_details.insurance_date','job_card_details.insurance_available_type','job_card.jobcard_create')->get()->first();
          if ($jobCard['jobcard_create'] == '1') {
              return redirect('/admin/service/jobcard/list')->with('error','Error, Jobcard already created.');
          }
        $service_id = $jobCard['service_id'];
        
        $customermob = Customers::leftjoin('service','customer.id','service.customer_id')
        ->leftjoin('customer_details','customer.id','customer_details.customer_id')->where('customer.id',$jobCard['customer_id'])->where('customer_details.recent','1')->get()->first();

         $altphone = Customers::leftjoin('service','customer.id','service.customer_id')->where('customer.id',$jobCard['customer_id'])->get()->first();
        if ($customermob) {
           $mobile = $customermob['mobile'];
        }else{
          $mobile = $altphone['alt_mobile'];
        }

        $history_service = ServiceModel::Join('job_card','job_card.service_id','service.id')
                        ->leftJoin('feedback','feedback.jobcard_id','job_card.id')
                        ->where('job_card.service_id','=',$service_id)
                        ->where('job_card.id','<>',$jobCardId)
                        ->where(DB::raw('date(job_card.created_at)'),'<','CURRENT_DATE')
                        ->orderBy('job_card.created_at','desc')
                        ->select('service.id','service.customer_id','service.frame','service.registration','job_card.service_suggest',
                                'job_card.created_at','job_card.hirise_amount','job_card.vehicle_km','feedback.feedback')
                        ->get();
        $settingAMC = Settings::where('name','AMC')->get()->first();
        $same_type = Settings::where('name','same_amc_service')->get()->first();
        $getAMC = AMCModel::where('service_id',$service_id)->get(); 
        $amc = count($getAMC);
        $amc_product = AMCProduct::all();
        $repeatJC = Job_card::where('service_id',$service_id)->where('id','<>',$jobCardId)->get();
        $getInsurance = Insurance::where('sale_id',$jobCard['sale_id'])->get()->first();
        if(isset($getInsurance->id))
        {
            $insdate = $getInsurance->insurance_date;
            $date = strtotime($insdate);
            $new_date = strtotime('+ '.$getInsurance->policy_tenure.' year', $date);
            $renewl_date = date('Y-m-d', $new_date);
        }
        else{
            $renewl_date = date('Y-m-d');
        }

        $free_subtype = JobcardSubtype::whereRaw(DB::raw('FIND_IN_SET("Free",jobcard_subtype.type)'))->get();
        $general_subtype = JobcardSubtype::whereRaw(DB::raw('FIND_IN_SET("General",jobcard_subtype.type)'))->get();
        $acc_subtype = JobcardSubtype::whereRaw(DB::raw('FIND_IN_SET("Accident",jobcard_subtype.type)'))->get();
        $serviceCharge = ServiceCharge::where('job_card_id',$jobCardId)->where('charge_type','Discount')->where('promo_code','<>',null)->get();
        if (count($serviceCharge)>0) {
           $charges = $serviceCharge;
        }else{
          $charges = '';
        }

        if($jobCard['jobcard_create'] == 0 && $jobCard['customer_id'] != 0 && $jobCard['registration'] != null && $jobCard['frame'] != null && $jobCard['product_id'] != null && $jobCard['selling_dealer_id'] != null && $jobCard['sale_date'] != null && $jobCard['manufacturing_year'] != null){
          $data = [
              'history_service'   =>  $history_service,
              'jobcard_type' =>  $jobcard_type,
              'repeatJC' => $repeatJC,
              'jobCard' => $jobCard,
              'mobile' => $mobile,
              'renewl_date' => $renewl_date,
              'amc' => $amc,
              'amc_product' => $amc_product,
              'getInsurance' => $getInsurance,
              'settingAMC' => $settingAMC,
              'same_type' => $same_type,
              'jobCardId' =>  $jobCardId,
              'free_subtype' => $free_subtype,
              'general_subtype' => $general_subtype,
              'acc_subtype' => $acc_subtype,
              'charges' => $charges,
              'layout' => 'layouts.main'
          ];
          return view('admin.service.jobcard_screen1',$data);  
        }else{
           return back()->with('error','Error, Please update detail .');
      }
    }

    public function jobcard_screen2(Request $request, $jobCardId) {
        $jobCard = job_card::leftJoin('service','job_card.service_id','service.id')->where('job_card.id',$jobCardId)->select(
          'job_card.*','job_card.oilinfornt_customer','service.customer_id', 'service.sale_id', 'service.frame', 'service.registration','service.selling_dealer_id', 'service.product_id','service.sale_date','service.manufacturing_year','job_card.jobcard_create')->first();
         if ($jobCard['jobcard_create'] == '1') {
           return redirect('/admin/service/jobcard/list')->with('error','Error, Jobcard already created.');
        }
        $service_id = $jobCard['service_id'];

            $servicedetail = ServiceModel::leftjoin('product','product.id','service.product_id')
                            ->where('service.id',$service_id)
                            ->select('product.model_category as vehicle_type')
                            ->first();
          $checklist[] = '';
          if ($jobCard['job_card_type'] == 'Free') {
            $sub_id = $jobCard['sub_type'];
            $type = JobcardSubtype::where('id',$sub_id)->get()->first();
            $vehicle_type = $servicedetail['vehicle_type'];
            if ($type) {
              $checklist[] = ServiceChecklistMaster::where('service_type',$type['name'])->where('showin','1')->where('vehicle_type',$vehicle_type)->get();
            }
          }if ($jobCard['job_card_type'] == 'Paid') {
            $type = $jobCard['job_card_type'];
            $vehicle_type = $servicedetail['vehicle_type'];
            $checklist[] = ServiceChecklistMaster::where('service_type',$type)->where('showin','1')->where('vehicle_type',$vehicle_type)->get();
          } if ($jobCard['job_card_type'] == 'General') {
            $sub_id = $jobCard['sub_type'];
            $subid = explode(',', $sub_id);
            $type = JobcardSubtype::whereIn('id',$subid)->get()->toArray();
            $vehicle_type = $servicedetail['vehicle_type'];
              if (count($type) > 0) {
               for ($i = 0; $i < count($type) ; $i++) { 
                $checklist[] = ServiceChecklistMaster::where('service_type',$type[$i]['name'])->where('showin','1')->where('vehicle_type',$vehicle_type)->get();
              }
            }
          }
         $PartRequest = ServicePartRequest::where('job_card_id',$jobCardId)->get();

        if( $jobCard['job_card_type'] != null && $jobCard['customer_id'] != 0 && $jobCard['registration'] != null && $jobCard['frame'] != null && $jobCard['product_id'] != null && $jobCard['selling_dealer_id'] != null && $jobCard['sale_date'] != null && $jobCard['manufacturing_year'] != null){
          $data = [
              'jobCard' => $jobCard,
              'checklist' => $checklist,
              'jobCardId' =>  $jobCardId,
              'PartRequest' => $PartRequest,
              'layout' => 'layouts.main'
          ];
          return view('admin.service.jobcard_screen2',$data);  
        }else{
           return redirect('/admin/service/create/jobcard/'.$jobCardId)->with('error','Error, Please firstly complete first screen .');
      }
    }



    public function jobcard_screen3(Request $request, $jobCardId) {
        $ServiceCharge = Master::where('type','service_vas')->get();
        $jobCard = job_card::leftJoin('service','job_card.service_id','service.id')->where('job_card.id',$jobCardId)->select(
          'job_card.*','service.customer_id', 'service.sale_id', 'service.frame', 'service.registration','service.selling_dealer_id', 'service.product_id','service.sale_date','service.manufacturing_year','job_card.jobcard_create')->first();
        $service_id = $jobCard['service_id'];
        if ($jobCard['jobcard_create'] == '1') {
           return redirect('/admin/service/jobcard/list')->with('error','Error, Jobcard already created.');
        }
         if ($jobCard->oilinfornt_customer == null) {
           return redirect('/admin/service/create/jobcard/screen2/'.$jobCardId)->with('error','Error,Please firstly complete second screen.');
        }
        $settingAMC = Settings::where('name','AMC')->get()->first();
        $same_type = Settings::where('name','same_amc_service')->get()->first();
        $getAMC = AMCModel::where('service_id',$service_id)->get(); 
        $amc = count($getAMC);
        $amc_product = AMCProduct::all();
      
        if($jobCard['job_card_type'] != null && $jobCard['customer_id'] != 0 && $jobCard['registration'] != null && $jobCard['frame'] != null && $jobCard['product_id'] != null && $jobCard['selling_dealer_id'] != null && $jobCard['sale_date'] != null && $jobCard['manufacturing_year'] != null){
          $data = [
              'jobCard' => $jobCard,
              'amc' => $amc, 
              'amc_product' => $amc_product,
              'settingAMC' => $settingAMC,
              'same_type' => $same_type,
              'jobCardId' =>  $jobCardId,
              'ServiceCharge' => $ServiceCharge,
              'layout' => 'layouts.main'
          ];
          return view('admin.service.jobcard_screen3',$data);  
        }else{
            return redirect('/admin/service/create/jobcard/screen2/'.$jobCardId)->with('error','Error, Please firstly complete second screen .');
      }
    }


    public function checkAMC(Request $request) {
      $JcId = $request->input('JcId');
      $type = $request->input('type');
      $setting = Settings::where('name',$type)->get()->first();
      $service = Job_card::where('id',$JcId)->get()->first();
      $getAMC = AMCModel::where('service_id',$service['service_id'])->get();
      if (count($getAMC) > 0) {
        return 'success';
      }else if($setting['value'] == 'allowed'){
        return 'allowed';
      }else{
        return 'error';
      }
    }

    public function AmcProduct(Request $request) {
      $id = $request->input('amc_prod_id');
      $product = AMCProduct::where('id',$id)->get()->first();
       return response()->json($product);
    }

    public function checkHJC(Request $request) {
      $JcId = $request->input('JcId');
      $type = $request->input('type');
      $service = Job_card::where('id',$JcId)->get()->first();
      $getHJC = HJCModel::where('service_id',$service['service_id'])->where(DB::raw('DATEDIFF(end_date,CURDATE())'),'>',0)->get();
      if (count($getHJC) > 0) {
        return 'success';
      }else{
        return 'error';
      }
    }

    public function GetServiceCheck_list(Request $request) {
      $service_type = $request->input('service_type');
      $vehicle_type = $request->input('vehicle_type');
      $checklist = ServiceChecklistMaster::where('service_type',$service_type)->where('vehicle_type',$vehicle_type)->where('showin','2')->get();
       return response()->json($checklist);
    }


    public function JobCardScreen1_DB(Request $request,$jobCardId) {
        $validator = Validator::make($request->all(),[
           'job_card_type'  =>  'required',
           'customer_status'  =>  'required',
           'frame_km'  =>  'required',
        ],
        [
            'job_card_type.required'  =>  'This Field is required',
            'customer_status.required'  =>  'This Field is required',
            'frame_km.required'  =>  'This Field is required',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        try{
            DB::beginTransaction();
            $getJc = Job_card::where('id',$jobCardId)->get()->first();
            $store_id = $getJc['store_id'];
            $service = ServiceModel::where('id',$getJc->service_id)->get()->first();
            $job_card_type = $request->input('job_card_type');

            if ($job_card_type) {
               $JCtype = CustomHelpers::JobCardTypeChanges($job_card_type,$jobCardId);
            }

            $arr = [];
            if ($request->input('acc_sub_type')) {
               $sub_type = JobcardSubtype::where('id',$request->input('acc_sub_type'))->get()->first();

            if($sub_type['name'] == 'Best Deal') {
                //check frame in best deal
                $get_ser = BestDealSale::where(function($query) use ($service){
                                    $query->where('frame',$service->frame)
                                        ->orwhere('register_no',$service->registration);
                            })->first();
                if(isset($get_ser->id))
                {
                    $updateJobCardId = BestDealSale::where('id',$get_ser->id)
                                                    ->update(['job_card_id' => $getJc->service_id]);
                }
                else{
                    return back()->with('error','Error, Best Deal not Found for this Service Id. ')->withInput();
                }
            }

            }
            
            if ($job_card_type == 'General') {
               if (!empty($request->input('sub_type')) && !empty($request->input('old_job_card'))) {
                $sub_type = implode(',', $request->input('sub_type'));
                  
                   $arr = ['sub_type' => $sub_type,'old_jobcard' => $request->input('old_job_card')];
               }else if(!empty($request->input('sub_type'))) {
                $sub_type = implode(',', $request->input('sub_type'));
                 $arr = ['sub_type' => $sub_type];
               }else{
                return back()->with('error','Sub type require.')->withInput();
               }
            }

            if ($job_card_type == 'Free') {
               if (!empty($request->input('free_sub_type'))) {
                $arr = ['sub_type' => $request->input('free_sub_type')];
               }else{
                return back()->with('error','Sub type require.')->withInput();
               }
            }

            if ($job_card_type == 'AMC' && !empty($request->input('only_wash')) && $request->input('only_wash') == 'Yes') {
                  $getJc = Job_card::where('id',$jobCardId)->get()->first();
                  $getService = Job_card::where('service_id',$getJc['service_id'])->where('job_card_type','AMC')->where('only_wash',$request->input('only_wash'))->get();
                  if (count($getService) > 2) {
                     return back()->with('error','Washed already 2 times so can not create job card.')->withInput();
                  }else{
                     $only_wash = $request->input('only_wash');
                  }
            }else{
              $only_wash = 'No';
            }


                if ($job_card_type == 'Paid' && !empty($request->input('mejor_job'))) {
                        $mejor_job = $request->input('mejor_job');
                        $sub_type = NULL;
                    }else{
                      $mejor_job = 'No';

                    }


            if ($request->input('insurance') == 'Yes') {

                $getInsurance = Insurance::where('sale_id',$service['sale_id'])
                                    ->get()->first();
                                    
                if (isset($getInsurance['insurance_date'])) {
                    $insdate = $getInsurance['insurance_date'];
                              
                    $date = strtotime($insdate);
                    $new_date = strtotime('+ '.$getInsurance['policy_tenure'].' year', $date);
                    $renewl_date = date('Y-m-d', $new_date);
                }else{
                   $renewl_date = date('Y-m-d', strtotime($request->input('renewal_date')));
                }
            }

            if ($request->input('mobile')) {
                //Search mobile number in customer 
                $customer = job_card::leftJoin('service','job_card.service_id','service.id')
                        ->leftJoin('customer','customer.id','service.customer_id')
                        ->where('customer.mobile', $request->input('mobile'))->where('job_card.id',$jobCardId)
                        ->select('job_card.*','service.customer_id')->first();
                    if ($customer) {
                       
                    }else{
                        //Search mobile number in customer details
                        $getcustomer = job_card::leftJoin('service','job_card.service_id','service.id')
                        ->leftJoin('customer_details','customer_details.customer_id','service.customer_id')
                        ->where('customer_details.mobile', $request->input('mobile'))->where('job_card.id',$jobCardId)
                        ->select('job_card.*','service.customer_id as customer_id','customer_details.id as customer_details_id')->first();

                        if ($getcustomer) {
                            //Update recent mark in customer detail
                          $custUpdate = CustomerDetails::where('customer_id',$getcustomer->customer_id)->update([
                            'recent' => 0
                          ]);
                          if ($custUpdate) {
                             $mobileupdate = CustomerDetails::where('id',$getcustomer->customer_details_id)->update([
                                'recent' => 1
                              ]);
                          }
                          // if($mobileupdate == NULL){
                          //       DB::rollback();
                          //       return back()->with('error','Mobile number not updated 1.')->withInput();
                          // }
                        }else{
                            //Update alt mobile number in customer
                            $mobupdate = Customers::where('id',$request->input('customer_id'))->update([
                            'alt_mobile' => $request->input('mobile')
                          ]);
                        

                          // if ($mobupdate == NULL) {
                          //       DB::rollback();
                          //       return back()->with('error','Mobile number not updated 2.')->withInput();
                          // }
                      }
                    
                    }
            }
             if ($job_card_type == 'Accident' || $only_wash == 'Yes') {
              if ($request->input('acc_sub_type')) {
                 $arr = ['sub_type' => $request->input('acc_sub_type')];
              }else{
                $arr = [];
              }
            }

               $insertdata = array_merge($arr, [
                'job_card_type'    => $job_card_type,
                'customer_status'  =>  $request->input('customer_status'),
                'vehicle_km'  =>  $request->input('frame_km'),
                'only_wash' => $only_wash,
                'mejor_job' => $mejor_job,
                ]);

                $insertJobCard = tap(Job_card::where('id',$jobCardId))->update($insertdata)->first();
            if ($insertJobCard && $job_card_type != 'Accident') {
              $getjobdetail = JobcardDetail::where('job_card_id',$jobCardId)->get()->first();
              if ($request->input('insurance') == 'Yes') {
                if ($getjobdetail) {
                  $update = JobcardDetail::where('job_card_id',$jobCardId)->update([
                      'job_card_id' => $jobCardId,
                      'insurance' => $request->input('insurance'),
                      'insurance_date' => $renewl_date,
                      'insurance_available_type' => NULL
                  ]);
                }else{
                  $update = JobcardDetail::insertGetId([
                      'job_card_id' => $jobCardId,
                      'insurance' => $request->input('insurance'),
                      'insurance_date' => $renewl_date,
                      'insurance_available_type' => NULL
                  ]);
                }
                  
               } else if ($request->input('insurance') == 'No') {
                if ($getjobdetail) {
                  $update = JobcardDetail::where('job_card_id',$jobCardId)->update([
                      'job_card_id' => $jobCardId,
                      'insurance_date' => NULL,
                      'insurance' => $request->input('insurance'),
                      'insurance_available_type' => $request->input('available_type')
                  ]);
                }else{
                  $update = JobcardDetail::insertGetId([
                      'job_card_id' => $jobCardId,
                      'insurance_date' => NULL,
                      'insurance' => $request->input('insurance'),
                      'insurance_available_type' => $request->input('available_type')
                  ]);
                }
                
               }else{
                return back()->with('error','Something went wrong.')->withInput();
               }
            }

            if ($insertJobCard && $request->input('job_card_type') == 'HJC') {
                $disc = CustomHelpers::getHJCDiscount();
      
                      if ($request->input('part_discount')) {
                        if (empty($request->input('part_promo'))) {
                          DB::rollback();
                          return back()->with('error','Please enter promo code')->withInput();
                        }else{
                          $discdata = ['promo_code' => $request->input('part_promo'),
                                       'job_card_id' => $jobCardId,
                                        'charge_type' => 'Discount',
                                        'sub_type' => $request->input('part_discount'),
                                        'discount_rate' => $disc['part']
                                      ];
                          $Discount_charge = ServiceCharge::insertGetId($discdata);
                        }
                      }

                      if ($request->input('service_discount') == 'Service') {
                        if (empty($request->input('service_promo'))) {
                          DB::rollback();
                          return back()->with('error','Please enter promo code')->withInput();
                        }else{
                          $discdata = ['job_card_id' => $jobCardId,
                                        'promo_code' => $request->input('service_promo'),
                                        'charge_type' => 'Discount',
                                        'sub_type' => $request->input('service_discount'),
                                        'discount_rate' => $disc['Service']
                                      ];
                          $Discount_charge = ServiceCharge::insertGetId($discdata);
                        }
                      }
                      if ($request->input('labour_discount')) {
                        if (empty($request->input('labour_promo'))) {
                          DB::rollback();
                          return back()->with('error','Please enter promo code')->withInput();
                        }else{
                          $discdata = ['job_card_id' => $jobCardId,
                                        'promo_code' => $request->input('labour_promo'),
                                        'charge_type' => 'Discount',
                                        'sub_type' => $request->input('labour_discount'),
                                        'discount_rate' => $disc['labour']
                                      ];
                          $Discount_charge = ServiceCharge::insertGetId($discdata);
                        }
                      }
                      if ($request->input('engine_discount')) {
                        if (empty($request->input('engine_promo'))) {
                          DB::rollback();
                          return back()->with('error','Please enter promo code')->withInput();
                        }else{
                          $discdata = ['job_card_id' => $jobCardId,
                                        'promo_code' => $request->input('engine_promo'),
                                        'charge_type' => 'Discount',
                                        'sub_type' => $request->input('engine_discount'),
                                        'discount_rate' => $disc['Engine Oil']
                                      ];
                          $Discount_charge = ServiceCharge::insertGetId($discdata);
                        }
                      }
                      if ($request->input('pickup_discount')) {
                        if (empty($request->input('pickup_promo'))) {
                          DB::rollback();
                          return back()->with('error','Please enter promo code')->withInput();
                        }else{
                          $discdata = ['job_card_id' => $jobCardId,
                                        'promo_code' => $request->input('pickup_promo'),
                                        'charge_type' => 'Discount',
                                        'sub_type' => $request->input('pickup_discount'),
                                        'discount_rate' => $disc['Pickup']
                                      ];
                          $Discount_charge = ServiceCharge::insertGetId($discdata);
                        }
                      }
                
               if ($Discount_charge == NULL) {
                DB::rollback();
                 return back()->with('error','Something went wrong.')->withInput();
               }
            }
           
           if(empty($insertJobCard) ) {
               DB::rollback();
               return back()->with('error','Some Error Occurred.')->withInput();
           }

        }catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return redirect('/admin/service/create/jobcard/'.$jobCardId)->with('error','some error occurred'.$ex->getMessage());
        }
        /* Add action Log */
        CustomHelpers::userActionLog($action='Createion Jobcard First Screen) ',$jobCardId,$service['customer_id']);
        DB::commit();
        return redirect('/admin/service/create/jobcard/screen2/'.$jobCardId)->with('success','Job card basic requirment submitted !');
    }

    public function JobCardScreen2_DB(Request $request,$jobCardId) {
      if ($request->file('audio')) {
         $ad = $this->SaveAudio($request->file('audio'));
      }
        $validator = Validator::make($request->all(),[
           'estimate_hour'  =>  'required',
           'estimate_delivery'  =>  'required',
           'oilinfornt_customer'  =>  'required',
           'part_name.*'  =>  'required',
           'part_qty.*'  =>  'required'
        ],
        [
            
            'estimate_hour.required'  =>  'This Field is required',
            'estimate_delivery.required'  =>  'This Field is required',
            'oilinfornt_customer.required'  =>  'This Field is required',
            'part_name.*.required'  =>  'This Field is required',
            'part_qty.*.required'  =>  'This Field is required'
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        try{
            date_default_timezone_set("Asia/Calcutta");
            DB::beginTransaction();
            $checklist = [];
            $getJc = Job_card::where('id',$jobCardId)->get()->first();
            $job_card_type = $getJc['job_card_type'];
            $store_id = $getJc['store_id'];
            $service = ServiceModel::leftjoin('product','product.id','service.product_id')
                              ->where('service.id',$getJc->service_id)->select('service.*','product.model_category as vehicle_type')->first();
          
            $estimate_hour = $request->input('estimate_hour');
            $estimate_hour  = str_replace(' ','',$estimate_hour);
            $estimate_hour  = str_replace('PM','',$estimate_hour);
            $estimate_hour  = str_replace('AM','',$estimate_hour);
            $estimate_hour = gmdate('H:i',$estimate_hour);
            $d1 = new DateTime(date('Y-m-d').' '.$estimate_hour);
            $rtime1 = CustomHelpers::roundUpToMinuteInterval($d1,CustomHelpers::min_duration());
            $estimate_hour = $rtime1->format('H:i');
            //convert time to duration
            $arr1 = explode(':',$estimate_hour);
            $h = $arr1[0];
            $m = $arr1[1];
            $estimate_hour = (($h*60)+$m)*60;
          
            $estimate_delivery = $request->input('estimate_delivery');
            $estimate_delivery  = str_replace(' ','',$estimate_delivery);
            $estimate_delivery  = str_replace('PM','',$estimate_delivery);
            $estimate_delivery  = str_replace('AM','',$estimate_delivery);
       
            $arr = [];
            $d = new DateTime(date('Y-m-d').' '.$estimate_delivery);
            $rtime = CustomHelpers::roundUpToMinuteInterval($d,CustomHelpers::min_duration());
            $estimate_delivery = $rtime->format('H:i');
           

            if ($job_card_type == 'General') {
              $vehicle_type = $service['vehicle_type'];
              $sub_id = $getJc['sub_type'];
              $subid = explode(',', $sub_id);
              $type = JobcardSubtype::whereIn('id',$subid)->get()->toArray();
              if (count($type) > 0) {
                for ($i = 0; $i < count($type) ; $i++) { 
                  $checklist[] = ServiceChecklistMaster::where('service_type',$type[$i]['name'])->where('showin','1')->where('vehicle_type',$vehicle_type)->get();
                }
              }
               if (empty($request->input('checklist')) && (isset($checklist) && count($checklist[0]) > 0)) {
                      return back()->with('error','check list is required.')->withInput();
                    }else{
                      if (isset($checklist) && count($checklist[0]) > 0) {
                        $checklistReq = $request->input('checklist');
                        $count = count($checklistReq);
                        for ($i = 0; $i < $count ; $i++) { 
                           $getcheck = ServiceChecklistMaster::where('id',$checklistReq[$i])->get()->first();
                           if($getcheck['action'] == 'Replace'){
                            $listupdate = PartRequest::insertGetId([
                              'model_name' => $getcheck['name'],
                              'store_id' => $getJc['store_id']
                            ]);

                            if ($listupdate == NULL) {
                              DB::rollback();
                               return back()->with('error','Something went wrong.')->withInput();
                            }
                           }
                        }
                }
              }
            }

            if ($job_card_type == 'Free') {
              $sub_id = $getJc['sub_type'];
              $type = JobcardSubtype::where('id',$sub_id)->get()->first();
              $vehicle_type = $service['vehicle_type'];
              $checklist = ServiceChecklistMaster::where('service_type',$type['name'])->where('showin','1')->where('vehicle_type',$vehicle_type)->get();
                  if (empty($request->input('checklist')) && count($checklist) > 0) {
                    return back()->with('error','check list is required.')->withInput();
                  }else{
                    if (count($checklist) > 0) {
                    $checklistReq = $request->input('checklist');
                    $count = count($checklistReq);
                    for ($i = 0; $i < $count ; $i++) { 
                       $getcheck = ServiceChecklistMaster::where('id',$checklistReq[$i])->get()->first();
                       if($getcheck['action'] == 'Replace'){
                        $update = PartRequest::insertGetId([
                          'model_name' => $getcheck['name'],
                          'store_id' => $getJc['store_id']
                        ]);

                        if ($update == NULL) {
                          DB::rollback();
                           return back()->with('error','Something went wrong.')->withInput();
                        }
                       }
                    }
                  }
                }
            }

                if ($job_card_type == 'Paid') {
                    $type = $getJc['job_card_type'];
                    $vehicle_type = $service['vehicle_type'];
                    $checklist[] = ServiceChecklistMaster::where('service_type',$type)->where('showin','1')->where('vehicle_type',$vehicle_type)->get();
                    if (empty($request->input('checklist')) && count($checklist) > 1) {
                      return back()->with('error','check list is required.')->withInput();
                    }else{
                      if (count($checklist) > 1) {
                      $checklistReq = $request->input('checklist');
                      $count = count($checklistReq);
                      for ($i = 0; $i < $count ; $i++) { 
                         $getcheck = ServiceChecklistMaster::where('id',$checklistReq[$i])->get()->first();
                         if($getcheck['action'] == 'Replace'){
                          $checkupdate = PartRequest::insertGetId([
                            'model_name' => $getcheck['name'],
                            'store_id' => $getJc['store_id']
                          ]);
                          if ($checkupdate == NULL) {
                            DB::rollback();
                             return back()->with('error','Something went wrong.')->withInput();
                          }
                         }
                      }
                    }
                  }
                }


            if ($request->input('part_name')) {
              $partname = $request->input('part_name');
              $partqty = $request->input('part_qty');
               $part_request = $this->PartRequest($partname, $partqty, $jobCardId);
               if ($part_request == 0) {
                 DB::rollback();
                 return back()->with('error','Something went wrong.')->withInput();
               }
            }
              
            if ($job_card_type == 'Accident'){
               $insertdata = [
                'job_card_type'    => $job_card_type,
                'oilinfornt_customer'    =>  $request->input('oilinfornt_customer')
                ];
                $insertJobCard = Job_card::where('id',$jobCardId)->update($insertdata);

            }else{
                $insertdata = array_merge($arr,[
                'customer_status' => $getJc['customer_status'],
                'service_duration'  =>  gmdate('H:i:s',$estimate_hour),
                'oilinfornt_customer'    =>  $request->input('oilinfornt_customer'),
                'estimated_delivery_time'  =>  date('Y-m-d H:i:s',strtotime($estimate_delivery))
            ]);
           $data = [
                'customer_status' => $getJc['customer_status'],
               'service_duration'  =>  $estimate_hour,
               'oilinfornt_customer'    =>  $request->input('oilinfornt_customer'),
               'estimated_delivery_time'  =>  $estimate_delivery,
           ];
          // $buffer_time = '-1800 seconds';  // 0.5 hour  //only case of when customer status is drooping.
            $startValidation= time();
            $default_start_time = strtotime('08:30:00');
            if($default_start_time >= $startValidation){
                $startValidation = strtotime('08:30:00');
            }
                $durationValidation = gmdate('H:i:s',$estimate_hour);
            if($data['customer_status'] == 'droping')
            {
                $endValidation= $startValidation+$estimate_hour+2400;
            }
            if($data['customer_status'] == 'waiting')
            {
                $endValidation= $startValidation+$estimate_hour+1800;
            }
            $endValidation = strtotime('-5 minutes',$endValidation);
            if ($endValidation > strtotime($estimate_delivery)) {
                       DB::rollback();
                       return back()->with('error','Please enter right estimate delivery time.')->withInput();
            }
            $estimate_start_time =  strtotime('-'.$data['service_duration'].' seconds',strtotime($data['estimated_delivery_time']));
           if($getJc['customer_status'] == 'droping')
           {
               $buffer_time = '-2400 seconds'; 
               $allocation_start_time = strtotime($buffer_time,$estimate_start_time);
               $estimate_start_time = $allocation_start_time;
               $buffer_end_time = '+'.($data['service_duration']+1200).'seconds';
               $allocation_end_time = strtotime($buffer_end_time,$estimate_start_time);
           }
           if($data['customer_status'] == 'waiting')
           {
               $buffer_time = '-1800 seconds'; 
               $allocation_start_time = strtotime($buffer_time,$estimate_start_time);
               $estimate_start_time = $allocation_start_time;
               $buffer_end_time = '+'.($data['service_duration']+1200).'seconds';
               $allocation_end_time = strtotime($buffer_end_time,$estimate_start_time);
           }
           $estimate_start_time = date('Y-m-d H:i:s',strtotime('+1 minutes',$estimate_start_time));

           $estimate_end_time = date('Y-m-d H:i:s',$allocation_end_time);
           $assignSlot = $this->findExactSlotBay($data,$estimate_start_time,$estimate_end_time,$jobCardId,date('Y-m-d'),$store_id);
            $insertJobCard = Job_card::where('id',$jobCardId)->update($insertdata);

           if($assignSlot == 'success') {
           } else{
                $data['estimate_start_time'] = $estimate_start_time;
                $data['estimated_delivery_time'] = $estimate_end_time;
                $duration = gmdate('H:i:s',strtotime($data['estimated_delivery_time'])-strtotime($data['estimate_start_time']));

                $assignSlot = $this->findDynamicSlot($jobCardId,$duration,$store_id);

                if($assignSlot != 'success') {
                    DB::commit();
                    return redirect('/admin/service/create/jobcard/screen3/'.$jobCardId)->with('success','Successfully Updated, Sry, we are not able to find BAY for given estimation servicing time. Pls do it manually.');
                }

           }
          }
            
           if(empty($insertJobCard))
           {
               DB::rollback();
               return back()->with('error','Something went wrong.')->withInput();
           }

        }catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return redirect('/admin/service/create/jobcard/screen2/'.$jobCardId)->with('error','some error occurred'.$ex->getMessage());
        }
        /* Add action Log */
        CustomHelpers::userActionLog($action='Createion Jobcard Second Screen',$jobCardId,$service['customer_id']);
        DB::commit();
        return redirect('/admin/service/create/jobcard/screen3/'.$jobCardId)->with('success','Second screen created successfully.');
    }

    public function JobCardScreen3_DB(Request $request,$jobCardId) {
        try{
            DB::beginTransaction();
            $getJc = Job_card::where('id',$jobCardId)->get()->first();
            $store_id = $getJc['store_id'];
            $service = ServiceModel::where('id',$getJc->service_id)->get()->first();

            if (!empty($request->input('amc_type')) && $request->input('amc_type') == 'same_service') {
               $job_card_type = 'AMC';
            }else{
              $job_card_type = $getJc['job_card_type'];
            }

              $purchase_amc = $request->input('purchase_amc');
                    if (!empty($purchase_amc)) {
                      $setting = Settings::where('name','AMC')->get()->first();
                      $getAMC = AMCModel::where('service_id',$service['id'])->where(DB::raw('DATEDIFF(end_date,CURDATE())'),'>',0)->get()->first();
                      $amc_charge = $request->input('amc_amount');
                      $start_date = date('Y-m-d');
                      $date = strtotime($start_date);
                      $new_date = strtotime('+ 1 year', $date);
                      $end_date = date('Y-m-d', $new_date);
                      $amc_product = AMCProduct::where('id',$request->input('amc_prod'))->get()->first();
                      // Booklet Issue
                      if ($amc_product) {
                            $bookletIssue = AmcBookletIssue::insertGetId([
                                'type' => 'Service',
                                'total_booklet' => $amc_product['duration'],
                                'type_id' => $jobCardId,
                                'store_id'  =>  $store_id,
                                'status' => 'Pending'
                            ]);
                            if ($bookletIssue == NULL) {
                                DB::rollback();
                                return back()->with('error','Something went wrong !')->withInput();
                            }
                        
                    }

                      if ($getAMC && $getAMC->service_allowed >= $getAMC->service_taken && $setting->value == 'allowed') {
                          $amcdata = AMCModel::where('id',$getAMC->id)->update([
                                'service_id' => $service['id'],
                                'start_date' => $start_date,
                                'end_date' => $end_date,
                                'service_allowed' => $amc_product->service_allowed,
                                'service_taken' => $getAMC->service_taken+1,
                                'allowed_washing' => $amc_product->washing,
                                'amount' => $amc_charge
                            ]);
                          if ($amcdata == NULL) {
                            DB::rollback();
                            return back()->with('error','Something went wrong !');
                          }
                          // else{
                          //   DB::commit();
                          //   return redirect('/admin/service/create/jobcard/screen3/'.$jobCardId)->with('success','AMC purchesed successfully !');
                          // }
                      }else{
                         $amcdata = AMCModel::insertGetId([
                                'service_id' => $service['id'],
                                'start_date' => $start_date,
                                'end_date' => $end_date,
                                'service_allowed' => $amc_product->service_allowed,
                                'service_taken' => 1,
                                'allowed_washing' => $amc_product->washing,
                                'amount' => $amc_charge
                            ]);
                            if ($amcdata) {
                               $charge = ServiceCharge::insertGetId([
                                  'job_card_id' => $jobCardId,
                                  'charge_type' => 'Charge',
                                  'sub_type' => 'AMC',
                                  'amount' => $amc_charge
                               ]);

                               if ($charge == NULL) {
                                DB::rollback();
                                return back()->with('error','Something went wrong !');
                               }else{
                                  $update = Job_card::where('id',$jobCardId)->update(['job_card_type' => $job_card_type]);
                                  if ($charge == NULL) {
                                    DB::rollback();
                                    return back()->with('error','Something went wrong !');
                                  }
                                  // else{
                                  //   DB::commit();
                                  //   return redirect('/admin/service/create/jobcard/screen3/'.$jobCardId)->with('success','AMC purchesed successfully !');
                                  // }
                               }
                            }else{
                              DB::rollback();
                              return back()->with('error','Something went wrong.')->withInput(); 
                            }
                      }
                    }

                if ($job_card_type == 'HJC' ) {
                      $getHJC = HJCModel::where('service_id',$service['id'])->where(DB::raw('DATEDIFF(end_date,CURDATE())'),'>',0)->get();
                      $hjc_charge = '500';
                      $start_date = date('Y-m-d');
                      $date = strtotime($start_date);
                      $new_date = strtotime('+ 1 year', $date);
                      $end_date = date('Y-m-d', $new_date);
                      if (count($getHJC) == 0 ) {
                          if (!empty($request->input('purchase_hjc'))) {
                             $insert = HJCModel::insertGetId([
                                'service_id' => $service['id'],
                                'start_date' => $start_date,
                                'end_date' => $end_date
                            ]);
                            if ($insert) {
                               $charge = ServiceCharge::insertGetId([
                                  'job_card_id' => $jobCardId,
                                  'charge_type' => 'Charge',
                                  'sub_type' => 'HJC',
                                  'amount' => $hjc_charge
                               ]);
                               if ($charge == NULL) {
                                DB::rollback();
                                return back()->with('error','Something went wrong !');
                               }
                               // else{
                               //   DB::commit();
                               //   return redirect('/admin/service/create/jobcard/screen3/'.$jobCardId)->with('success','HJC purchesed successfully !');
                               // }
                            }else{
                              DB::rollback();
                              return back()->with('error','Something went wrong.')->withInput();
                            }
                          }else{
                            DB::rollback();
                            return back()->with('error','HJC purchase require.')->withInput();
                          }
                      }else{
                        DB::rollback();
                        return back()->with('error','HJC already purchesed !');
                    }
                      
                }

              if(!empty($request->input('purchase_ew'))) {
                $start_date = date('Y-m-d');
                $date = strtotime($start_date);
                $new_date = strtotime('+ 1 year', $date);
                $end_date = date('Y-m-d', $new_date);

                    $checkew = ExtendedWarranty::leftJoin('service','service.id','extended_warranty.service_id')
                        ->where('extended_warranty.service_id',$service['id'])
                        ->where(DB::raw('TIMESTAMPDIFF(YEAR, service.sale_date, CURDATE())'),'>=',1)
                        ->get()
                        ->first();
                    $ew_charge = 500;
                    if ($checkew == NULL) {
                        $insertew = ExtendedWarranty::insertGetId([
                            'service_id' => $service['id'],
                            'start_date' => $start_date,
                            'end_date' => $end_date,
                            'amount' => $ew_charge
                         ]);
                         if($insertew) {
                               $charge = ServiceCharge::insertGetId([
                                  'job_card_id' => $jobCardId,
                                  'charge_type' => 'Charge',
                                  'sub_type' => 'Extended Warranty',
                                  'amount' => $ew_charge
                               ]);
                               if ($charge == NULL) {
                                DB::rollback();
                                return back()->with('error','Something went wrong !');
                               }
                               // else{
                               //   DB::commit();
                               //   return redirect('/admin/service/create/jobcard/screen3/'.$jobCardId)->with('success','Extended warranty purchesed successfully !');
                               // }
                            }else{
                              DB::rollback();
                              return back()->with('error','Something went wrong !');
                            }
                    }else{
                        DB::rollback();
                        return back()->with('error','Extended Warranty already purchesed !');
                    }
            }
            if ($request->input('service_vas')) {
                $service_vas = $request->input('service_vas');
                $count = count($service_vas);
                for ($i = 0; $i < $count ; $i++) { 
                   $getdata = Master::where('type','service_vas')->where('value',$service_vas[$i])->get()->first();
                   $pr = json_decode($getdata->details);
                   $price = $pr->price;
                   if ($service_vas[$i] == 'PICK N DROP') {
                    $pick = $request->input('pick');
                    $drop = $request->input('drop');
                       
                        if (!empty($pick) && empty($drop)) {
                          $type = $pick;
                        }elseif (empty($pick) && !empty($drop)) {
                          $type = $drop;
                         }elseif (!empty($pick) && !empty($drop)) {
                          $type = $pick.','.$drop;
                        }else{
                           DB::rollback();
                           return back()->with('error','Please check pick/drop or both !');
                        }

                        $pickupInsert = Pickup::insertGetId([
                          'job_card_id' => $jobCardId,
                          'type' => $type
                        ]);
                        if ($pickupInsert == NULL) {
                          DB::rollback();
                          return back()->with('error','Value not added !');
                        }
                    }
                   $insert = ServiceCharge::insertGetId([
                    'job_card_id' => $jobCardId,
                    'charge_type' => 'Charge',
                    'sub_type' => $service_vas[$i],
                    'amount' => $price
                   ]);
                   if ($insert == NULL) {
                      DB::rollback();
                        return back()->with('error','Value not added !');
                   }

                }
            }

            $update = Job_card::where('id',$jobCardId)->update([
              'jobcard_create' => '1'
            ]);
            if ($update == null) {
               DB::rollback();
              return redirect('/admin/service/create/jobcard/screen3/'.$jobCardId)->with('error','some error occurred');
            }else{
              /* Add action Log */
              CustomHelpers::userActionLog($action='Createion Jobcard Third Screen) ',$jobCardId,$service['customer_id']);
              DB::commit();
               return redirect('/admin/service/jobcard/list')->with('success','Job card created successfully !');
            }

        }catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return redirect('/admin/service/create/jobcard/'.$jobCardId)->with('error','some error occurred'.$ex->getMessage());
        }
    }


    public function bay_time_validate($bay_id,$new_start_time,$new_end_time,$interval)
    {
        $get_bay_time = Bay::where('id',$bay_id)
                                ->select('start_time',
                                    DB::raw('IF(extended_date = current_date,extended_time,end_time) as end_time')
                                )->first();
        $d = new DateTime(date('Y-m-d H:i'));
        $s2 = CustomHelpers::roundToNearestMinuteInterval($d,$interval);
        $c_time = $s2->format('Y-m-d H:i:s');

        $str_ctime = strtotime($c_time);
        $stime = strtotime($new_start_time);
        $etime = strtotime($new_end_time);
        $str_bay_stime = strtotime($get_bay_time->start_time);
        $str_bay_etime = strtotime($get_bay_time->end_time);

        $diff = $etime-$stime;

        if($diff < $interval)
        {
            return 'Difference b/w Start Time and End Time should be minimum '.$interval.' m';
        }
        if($stime < $str_bay_stime)
        {
            return 'Start Time should be greater to Bay Start Time';
        }
        if($str_ctime > $stime)
        {
            return 'Starting Time should be greater to Current Time';
        }
        if($etime > $str_bay_etime)
        {
            return 'End Time should be less to Bay End Time';
        }
        // return $str_ctime.' < '.$stime;
        return 'success';
    }

    public function findExactSlotBay($data,$estimate_start_time,$estimate_end_time,$jobCardId,$date,$store_id)
    {
       $data['estimate_start_time'] = $estimate_start_time;
        $data['estimated_delivery_time'] = $estimate_end_time;
        // return $data;
             DB::enableQueryLog();

             $statement = DB::statement(DB::raw("set @date = '".$date."'"));
             $statement = DB::statement(DB::raw("set @store = ".$store_id));

             $findtime = DB::select(DB::raw("SELECT * FROM(
                            (SELECT bay.id,bay.name,
                                SUM(TIMEDIFF(b.end_time, b.start_time)) AS total_time
                                FROM bay_allocation AS b
                                RIGHT JOIN bay ON bay.id = b.bay_id and bay.store_id = @store
                                JOIN users ON users.id = ".Auth::id()."
                                WHERE bay.status = 'active' AND IFNULL(DATE(b.date),@date) = @date 
                                AND FIND_IN_SET(bay.store_id, users.store_id) 
                                AND bay.status = 'active'
                                GROUP BY bay.id
                            ) AS t1
                            LEFT JOIN(
                                SELECT COUNT(b.id) AS booked,b.bay_id
                                FROM bay_allocation AS b
                                WHERE
                                (
                                    (
                                        TIMESTAMP('".$data['estimate_start_time']."') BETWEEN b.start_time AND b.end_time
                                    ) OR(
                                        TIMESTAMP('".$data['estimated_delivery_time']."') BETWEEN b.start_time AND b.end_time
                                    )OR(
                                        b.start_time BETWEEN TIMESTAMP('".$data['estimate_start_time']."') AND TIMESTAMP('".$data['estimated_delivery_time']."')
                                    )OR(
                                        b.end_time BETWEEN TIMESTAMP('".$data['estimate_start_time']."') AND TIMESTAMP('".$data['estimated_delivery_time']."')
                                    )
                                ) 
                                AND IFNULL(DATE(b.date),@date) = @date
                                GROUP BY b.bay_id
                            ) AS t2
                        ON
                            t1.id = t2.bay_id 
                    )   
                    ORDER BY total_time ASC"));
           // print_r(DB::getQueryLog());die();
            $findtime = $findtime;
         
            if(!empty($findtime) && $findtime[0]->booked == NULL)
            {
                if($findtime[0])
                {
                    // validation of start time and end time according to BAY
                    $interval = CustomHelpers::min_duration();
                    // validation
                    $validate = $this->bay_time_validate($findtime[0]->id,$estimate_start_time,$estimate_end_time,$interval);
                    if($validate == 'success')
                    {
                        $bay_allocation_data = [
                            'bay_id'    =>  $findtime[0]->id,
                            'start_time'    =>  $data["estimate_start_time"],
                            'end_time'  =>  $data["estimated_delivery_time"],
                            'date'  =>  $date,
                            'status'    =>  'pending'
                        ];
                        $insert = BayAllocation::insertGetId(array_merge($bay_allocation_data,[ 
                                            'job_card_id'   =>  $jobCardId])
                                        );
                        
                        if($insert)
                        {
                            return 'success';
                        }
                    }
                }
            }
        // die;
        //  print_r($findtime);die;
        return 'not-allocate';
    }
    public function findExactSlot($data,$estimate_start_time,$totalBay,$jobCardId)
    {
         echo 'not use but it"s working';die;
        $data['estimate_start_time'] = $estimate_start_time;
        $data['estimated_delivery_time'] = date('Y-m-d H:i:s',strtotime($data['estimated_delivery_time']));
      
        // echo date('H:i:s',strtotime($data['estimated_delivery_time'])-strtotime($data['estimate_start_time']));
        // print_r($data);die;
        
             DB::enableQueryLog();


        $findtime = DB::select(DB::raw("SELECT * FROM(
                                        (SELECT bay.id,bay.name,
                                            SUM(TIMEDIFF(b.end_time, b.start_time)) AS total_time
                                            FROM bay_allocation AS b
                                            RIGHT JOIN bay ON bay.id = b.bay_id
                                            JOIN users ON users.id = ".Auth::id()."
                                            WHERE bay.status = 'active' AND IFNULL(DATE(b.date),CURRENT_DATE) = CURRENT_DATE 
                                            AND FIND_IN_SET(bay.store_id, users.store_id) 
                                            AND bay.status = 'active'
                                            GROUP BY bay.id
                                        ) AS t1
                                        LEFT JOIN(
                                            SELECT COUNT(b.id) AS booked,b.bay_id
                                            FROM bay_allocation AS b
                                            WHERE
                                            (
                                                (
                                                    TIMESTAMP('".$data['estimate_start_time']."') BETWEEN b.start_time AND b.end_time
                                                ) OR(
                                                    TIMESTAMP('".$data['estimated_delivery_time']."') BETWEEN b.start_time AND b.end_time
                                                )OR(
                                                    b.start_time BETWEEN TIMESTAMP('".$data['estimate_start_time']."') AND TIMESTAMP('".$data['estimated_delivery_time']."')
                                                )OR(
                                                    b.end_time BETWEEN TIMESTAMP('".$data['estimate_start_time']."') AND TIMESTAMP('".$data['estimated_delivery_time']."')
                                                )
                                            ) 
                                            AND IFNULL(DATE(b.date),CURRENT_DATE) = CURRENT_DATE
                                            GROUP BY b.bay_id
                                        ) AS t2
                                    ON
                                        t1.id = t2.bay_id 
                                )   
                                t2.booked is null
                                ORDER BY total_time ASC")
                                );


                                // ->where('date',DB::raw('CURRENT_DATE'))
            // echo "<br>findtime : - ";
       // print_r(DB::getQueryLog());die();
            $findtime = $findtime;
            // print_r($findtime);die;
            if(!empty($findtime))
            {
                if($findtime[0])
                {
                    $bay_allocation_data = [
                        'bay_id'    =>  $findtime[0]->id,
                        'start_time'    =>  $data["estimate_start_time"],
                        'end_time'  =>  $data["estimated_delivery_time"],
                        'date'  =>  date('Y-m-d'),
                        'status'    =>  'pending'
                    ];
                    $insert = BayAllocation::insertGetId(array_merge($bay_allocation_data,[
                                        'job_card_id'   =>  $jobCardId])
                                    );
                    
                    if($insert)
                    {
                        return 'success';
                    }
                }
            }
        // die;
        //  print_r($findtime);die;
        return 'not-allocate';
    }
    public function findDynamicSlot($jobCardId,$duration,$store_id) {
        // echo $estimate_start_time;die;
        // $data['estimate_start_time'] = $estimate_start_time;
        // $data['estimated_delivery_time'] = date('Y-m-d H:i:s',strtotime($data['estimate_service_out_time']));
        // $data['estimated_delivery_time'] = $estimate_end_time;
        // print_r($data);die;
        // $duration = gmdate('H:i:s',strtotime($data['estimated_delivery_time'])-strtotime($data['estimate_start_time']));
            // DB::enableQueryLog();
        // echo $duration;die;
        $statement = DB::statement(DB::raw("set @i = 0"));
        $statement = DB::statement(DB::raw("set @j = -1"));
        $statement = DB::statement(DB::raw("set @prev_end_time = ''"));
        $statement = DB::statement(DB::raw("set @temp = 0"));
        $statement = DB::statement(DB::raw("set @duration = '".$duration."'"));
        $statement = DB::statement(DB::raw("set @user_id = ".Auth::id()));
        $statement = DB::statement(DB::raw("set @store = ".$store_id));
            
      

            $findtime = DB::select(DB::raw("select auto_assign.job_card_id,auto_assign.id,auto_assign.name,
                                auto_assign.store_id,auto_assign.bay_status,auto_assign.start,auto_assign.end,
                                auto_assign.start_time,auto_assign.end_time,auto_assign.date,
                                auto_assign.start_to_end_duration,auto_assign.from_time,auto_assign.to_time,
                                TIMEDIFF(to_time, from_time) AS free_duration,
                                auto_assign.next_from_time
                            from (
                                select t1.auto_assign_id,t1.job_card_id,t1.id,t1.name,t1.store_id,
                                t1.bay_status,t1.start,t1.end,t1.start_time,t1.end_time,t1.date,
                                        if(@temp = t1.id,@prev_end_time,@prev_end_time:='') as change_prev_end_time,
                                        if(@temp=t1.id,@temp,@temp:=t1.id) as current_bay_id,
                                        timediff(t1.start_time,if(@prev_end_time='',t1.start_time,@prev_end_time)) as start_to_end_duration,
                                        @prev_end_time as basic_from_time,
                                        if(CURRENT_TIMESTAMP>@prev_end_time,CURRENT_TIMESTAMP, @prev_end_time) AS from_time,
                                        t1.start_time as to_time,
                                        @prev_end_time:=timestamp(t1.end_time) as next_from_time

                                from(
                                        SELECT
                                            @i := @i +1 AS auto_assign_id,
                                            aab.*
                                        FROM
                                            `auto_assign_bay1` aab
                                        where aab.store_id = @store
                                        order by aab.id,aab.start_time,aab.date ASC 
                                    ) t1

                                order by t1.id,t1.start_time, date asc
                            ) auto_assign
                            join users on users.id = @user_id
                                where time(timediff(to_time,from_time)) > time('00:00:00')
                                and time(timediff(to_time,from_time)) >= time(@duration)
                                and auto_assign.bay_status = 'active'
                                and find_in_set(auto_assign.store_id,users.store_id)
                            order by auto_assign.from_time asc
                "));
                       
          // print_r(DB::getQueryLog());die();        
            $findtime = $findtime;
            // print_r($findtime);die;
            if(!empty($findtime))
            {
                if($findtime[0])
                {
                    $start_time = explode('.',explode(' ',$findtime[0]->from_time)[1])[0];
                    $cal_duration = explode(':',$duration);
                    
                    $end_time = strtotime('+'.$cal_duration[0].' hour +'.$cal_duration[1].' minutes',strtotime($start_time));
                    // $end_time = date('H:i:s',strtotime($cal_duration));
                    $bay_allocation_data = [
                        'bay_id'    =>  $findtime[0]->id,
                        'start_time'    =>  date('Y-m-d H:i',strtotime('+1 minutes',strtotime($start_time))),
                        'end_time'  =>  date('Y-m-d H:i',$end_time),
                        'date'  =>  date('Y-m-d'),
                        'status'    =>  'pending'
                    ];
                    // print_r($bay_allocation_data);die;
                    $insert = BayAllocation::insertGetId(array_merge($bay_allocation_data,[
                                        'job_card_id'   =>  $jobCardId])
                                    );
                    
                    if($insert)
                    {
                        return 'success';
                    }
                }
            }
        // die;
        //  print_r($findtime);die;
        return 'not-allocate';
    }
    //custom solution for auto assign jobCard to Bay (current not use)

    public function findExactSlot1($data,$estimate_start_time,$totalBay,$jobCardId)
    {
        echo "under construction";die;
        // echo $estimate_start_time;die;
        $data['estimate_start_time'] = $estimate_start_time;
        $data['estimated_delivery_time'] = date('Y-m-d H:i:s',strtotime($data['estimated_delivery_time']));
        // print_r($data);die;
        foreach($totalBay as $key => $val)
        {
            // DB::enableQueryLog();
            $findtime = BayAllocation::where('job_card_id','<>',$jobCardId)->where('bay_id',$val['id'])->where(function($query) use ($data){
                                    $query->where(function($query) use($data){
                                        $query->where('start_time','<',$data["estimate_start_time"])
                                                ->where('end_time','>',$data["estimate_start_time"]);
                                    })
                                    ->orwhere(function($query) use($data){
                                        $query->where('start_time','<',$data["estimated_delivery_time"])
                                                ->where('end_time','>',$data["estimated_delivery_time"]);
                                    });
                                })
                                ->where('status','pending')
                                ->where('date',DB::raw('CURRENT_DATE'))
                                ->get();
            // echo "<br>findtime : - ";
            $findtime = $findtime->toArray();
            
            if(empty($findtime))
            {
                // echo "<br>".$val['id'];
                $pre_exist = BayAllocation::where('job_card_id',$jobCardId)->where('date',DB::raw('CURRENT_DATE'))->first();
                $bay_allocation_data = [
                    'bay_id'    =>  $val['id'],
                    'start_time'    =>  $data["estimate_start_time"],
                    'end_time'  =>  $data["estimated_delivery_time"],
                    'date'  =>  date('Y-m-d'),
                    'status'    =>  'pending'
                ];
                if($pre_exist)
                {
                    $insert = BayAllocation::where('id',$pre_exist['id'])->update($bay_allocation_data);
                }
                else{
                    $insert = BayAllocation::insertGetId(array_merge($bay_allocation_data,[
                                    'job_card_id'   =>  $jobCardId])
                                );
                }
                if($insert)
                {
                     return 'success';
                }
            }
        }
        // die;
        //  print_r($findtime);die;
        return 'not-allocate';
    }
    ///
    //floor supervisor
    public function bayList()
    {
        $data = [
            'layout' => 'layouts.main'
        ];
        
        return view('admin.service.bayList',$data);
    }

    public function floorSupervisorBuyWorkStart(Request $request) {   
        $jobCardId = $request->input('JobCardId');
         $check = BayAllocation::leftjoin('job_card','job_card.id','bay_allocation.job_card_id')->where('bay_allocation.job_card_id',$jobCardId)->get()->first();
         if ($check) {
            $getcheck = Job_card::where('id',$jobCardId)->whereNull('service_in_time')->first();
            if($getcheck)
            {
                $serviceIn = Job_card::where('id',$jobCardId)->update([
                    'wash' => $request->input('wash_status'),
                    'service_in_time' => DB::raw('CURRENT_TIMESTAMP')]
                );
                if($serviceIn) {
                    return 'success';
                }else{
                  return 'error';
                }
            } else{
                return 'This Service Already In';
            }
      }else{
         return 'Bay not allocated, Please bay allocate !';
      }
    }

    public function updateFrame($id) {
        $user = Users::where('id',Auth::id())->first();
        $job_card = job_card::leftjoin('service','job_card.service_id','service.id')->where('job_card.id',$id)
                    ->leftjoin('product','service.product_id','product.id')
                    ->where('job_card.id',$id)
                    ->select('job_card.*','service.selling_dealer_id','service.frame','service.registration','service.customer_id','service.product_id','product.model_name','product.model_variant','product.color_code','service.sale_date','service.manufacturing_year', DB::raw('TIMESTAMPDIFF( MONTH, service.sale_date, now() ) % 12 as month'))
                            ->first();
        $srID = $job_card->service_id;
        
        $allJobCard = job_card::leftjoin('service','job_card.service_id','service.id')->where('service.id',$srID)->select('job_card.*','service.frame','service.customer_id','service.registration','service.sale_id','service.selling_dealer_id')->get();
        $count = count($allJobCard);
        $customer = Customers::select('name','mobile','aadhar_id','voter_id','id')->get();
        $model_name = ProductModel::where('type','product')->orderBy('model_name','ASC')->groupBy('model_name')->get(['model_name']);
        $sellingDealer = SellingDealer::all();
      
        $state = State::where('country_id','105')->get('state_code')->toArray();
        if (($job_card['service_status'] == 'pending') && ($user->role == 'Superadmin' || $user->role == 'Receptionist')) {
            $data = array(
            'job_card' => $job_card,
            'state' => $state,
            'model_name'    =>  ($model_name->toArray())? $model_name->toArray() : array(),
            'customer' => $customer,
            'count' => $count,
            'sellingDealer' => $sellingDealer,
            'layout' => 'layouts.main'
        );
         return view('admin.service.update_frame',$data);
        }else{
          return redirect('/admin/service/jobcard/list')->with('error','You are not authorized user for update job card.');
        }
        
    }

    public function updateFrame_DB(Request $request, $Id) {
          $validator = Validator::make($request->all(),[
           'frame'  =>  'required_without_all:registration|min:17|max:17',
           'registration'  =>  'required_without_all:frame',
           'model_name' =>'required',
           'model_variant' =>'required_without_all:model_name',
           'prod_name' =>'required_without_all:model_variant',
           'customer'  =>  'required',
           'sale_date'  =>  'required',
           'selling_dealer_id'  =>  'required',
           'manufacturing_year'  =>  'required'
        ],
        [
            'frame.required_without_all'  =>  'This Field is required',
            'registration.required_without_all'  =>  'This Field is required',
            'model_name.required_without_all'  =>  'This Field is required',
            'model_variant.required_without_all'  =>  'This Field is required',
            'prod_name.required_without_all'  =>  'This Field is required',
            'customer.required'  =>  'This Field is required',
            'sale_date.required'  =>  'This Field is required',
            'selling_dealer_id.required'  =>  'This Field is required',
            'manufacturing_year.required'  =>  'This Field is required',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        try{
           $service = job_card::leftjoin('service','job_card.service_id','service.id')->where('job_card.id',$Id)->select('job_card.*','service.frame','service.customer_id','service.registration','service.sale_id','service.product_id')->first();
            $srID = $service->service_id;
            $allJobCard = job_card::leftjoin('service','job_card.service_id','service.id')->where('service.id',$srID)->select('job_card.*','service.frame','service.customer_id','service.registration','service.sale_id')->get();
            $count = count($allJobCard);

            // if ($service->service_status == 'done' || ($service->sale_id && $service->customer_id) || ($count > 1 && !empty($service->frame) && !empty($service->product_id))) { 
             if ($service->service_status == 'done' || ($service->sale_id && $service->customer_id && $count > 1 && !empty($service->frame) && !empty($service->product_id))) {
               return redirect('/admin/service/update/frame/'.$Id.'')->with('error','Already up to date');
            } else{
                
              $frame = $request->input('frame');
              if (!empty($request->input('reg_no') == 'available')) {
                 if (empty($request->input('registration'))) {
                       return back()->with('state_error','This Field is required .')->withInput();
                 }else{
                      $reg = $request->input('registration');
                      $len = strlen(trim($reg,'_'));
                      if ($len < 10) {
                         return back()->with('state_error','The registration must be at least 10 characters.')->withInput();
                      }else{
                         $reg = $request->input('registration');
                      }  
                 }
                  $reg = $request->input('registration');
              }else{
                 $reg = $request->input('reg_no');
              }

              $state_code = mb_substr($request->input('registration'), 0, 2);
              $checkstate = State::where('state_code',$state_code)->get()->first();
              if ($reg != 'NA') {
                 if ($checkstate == NULL) {
                    return back()->with('state_error','State code not available .')->withInput();

                  }else{
                    $city_code = mb_substr($request->input('registration'), 2, 2);
                    $checkcity = City::where('state_id',$checkstate['id'])->where('rto_code',$city_code)->get()->first();
                    if ($checkcity == NULL) {
                      return back()->with('city_error','City code not available .')->withInput();
                    }
                  }
              }

                // check frame
                $check_frame = ServiceModel::where('frame',$frame)->where('id','<>',$srID)->first();
                if(isset($check_frame->id))
                {
                    return back()->with('error','Error, This Frame # Already Exist, Please Enter Correct Frame #.')->withInput();
                }
                // check registration
                $check_reg = ServiceModel::where('registration',$reg)->where('registration','<>','NA')->where('id','<>',$srID)->first();
                if(isset($check_reg->id))
                {
                    return back()->with('error','Error, This Registration # Already Exist, Please Enter Correct Registration #.')->withInput();
                }
                if ($request->input('reg_no') != 'available') {
                   $reg = NULL;
                }

                if ($request->input('frame') || $request->input('registration')) {
                    $frameupdate = ServiceModel::where('id',$srID)->update([
                        'frame' => $request->input('frame'),
                        'registration' => $reg,
                        'product_id' => $request->input('prod_name'),
                        'sale_date' => date('Y-m-d',strtotime($request->input('sale_date'))),
                        'customer_id' => $request->input('customer'),
                        'selling_dealer_id' => $request->input('selling_dealer_id'),
                         'manufacturing_year' => $request->input('manufacturing_year')
                    ]); 
               }
               if ($frameupdate) {
                      /* Add action Log */
                      CustomHelpers::userActionLog($action='Update Service ',$srID,$request->input('customer'));
                      return redirect('/admin/service/update/frame/'.$Id.'')->with('success','Frame and customer Updated Successfully !');
                    }else{
                       return redirect('/admin/service/update/frame/'.$Id.'')->with('error','some error occurred');
                    } 
                   
            } 
       }catch(\Illuminate\Database\QueryException $ex) {
            return redirect('/admin/service/update/frame/'.$Id.'')->with('error','some error occurred'.$ex->getMessage());
        }
    }

     public function PartRequest($partname, $partqty, $job_card_id) {
        try{
            DB::beginTransaction();
            $getPart = ServicePartRequest::where('job_card_id',$job_card_id)->get();
            $getJobCard = Job_card::where('id',$job_card_id)->get()->first();
            
                $count = count($partname);
                for($i = 0; $i < $count ; $i++)
                {
                    $arr = [];
                    $data = array_merge($arr,[
                        'job_card_id'   =>  $job_card_id,
                        'part_name' =>  $partname[$i],
                        'qty'   =>  $partqty[$i],
                        'confirmation' =>  'customer',
                        'approved' => '1',
                        'approved_by' => Auth::id()
                    ]);
                    $insert = ServicePartRequest::insertGetId($data);
                    if(empty($insert)) {
                       return 0;
                    }
                }
        }catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return 0;
        }
        DB::commit();
        return 1;
    }

    public function SaveAudio(Request $request) {
      try{
          $id = $_POST['id'];
          $service = Job_card::where('job_card.id',$id)->leftjoin('service','job_card.service_id','service.id')->select('service.customer_id')->first();
          $file = $_FILES['blob']['name'];
          $tempName = $_FILES['blob']['tmp_name'];
          $allowed = array('mp3', 'ogg', 'flac');
          $destinationPath = public_path().'/upload/audio/'.$_FILES['blob']['name'];
          $target_path = public_path().'/upload/audio/';
          if (!is_dir($target_path)) {
              mkdir($target_path, 0777, true);
          }

          if(isset($_FILES['blob']) && $_FILES['blob']['error'] == 0){
            $extension = pathinfo($_FILES['blob']['name'], PATHINFO_EXTENSION);
            if(!in_array(strtolower($extension), $allowed)){
              return array('type' => 'error', 'msg' => 'Something went wrong.');
            }
            if(move_uploaded_file($_FILES['blob']['tmp_name'], $destinationPath)){
                if ($id) {
                $update = Job_card::where('id',$id)->update([
                  'recording' => $file
                ]);
                if ($update == null) {
                  return array('type' => 'error', 'msg' => 'Something went wrong.');
                }else{
                  /* Add action Log */
                CustomHelpers::userActionLog($action='Jobcard Recording',$id,$service['customer_id']);
                  return array('type' => 'success', 'msg' => 'Recording uploaded successfully.');
                }
             }
            }
          }
      }catch(\Illuminate\Database\QueryException $ex) {
        return array('type' => 'error', 'msg' => 'Something went wrong'.$ex.'.');
      }   
    }

}