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
use \App\Model\Factory;
use \App\Model\JobcardSubtype;
use \App\Model\AMCProduct;
use \App\Model\PartShortage;
use \App\Model\Master;
use \App\Model\ShortageDetails;
use \App\Model\Sale;
use \App\Model\PartStock;
use \App\Model\ServiceBooking;
use\App\Model\WarrantyInvoice;
use \App\Model\Parts;
use \App\Model\Payment;
use \App\Model\PartWarranty;
use \App\Model\SaleAccessories;
use \App\Model\SaleFinance;
use \App\Model\Scheme;
use \App\Model\ServiceChecklistMaster;
use \App\Model\ExtendedWarranty;
use \App\Model\Accessories;
use \App\Model\MasterAccessories;
use \App\Model\HiRise;
use \App\Model\Insurance;
use \App\Model\SaleOrder;
use \App\Model\RtoModel;
use \App\Model\Settings;
use \App\Model\OrderPendingModel;
use \App\Model\BookingModel;
use \App\Model\BestDealSale;
use \App\Model\PDI;
use \App\Model\PDI_details;
use \App\Model\FinalInspection;
use\App\Model\ServiceModel;
use\App\Model\CustomerDetails;
use\App\Model\HJCModel;
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
use Config;
use Mail;



class Service extends Controller {
  
    public function guradDashboard(){

        $data = [
            'layout' => 'layouts.main'
        ];
        
        return view('admin.service.guard',$data);
    }
    public function createBooking($id=null)
    {
      if ($id) {
        $booking = ServiceBooking::where('id',$id)->get()->first();
      }else{
        $booking = '';
      }
        $jobcard_type = Master::where('type','job_card_type')
                        ->whereIn('key',array('Free','Paid'))
                        ->select('key','value')->get();
        $store = Store::whereIn('id',CustomHelpers::user_store())
                    ->select('id',DB::raw('concat(store.name,"-",store.store_type) as name'))->get();

        $data = [
            'store'=>$store,
            'jobcard_type'  =>  $jobcard_type,
            'booking' => $booking,
            'layout' => 'layouts.main'
        ];
        
        return view('admin.service.create_booking',$data);
    }

    public function createTag() {
       $state = State::where('country_id','105')->get('state_code')->toArray();
        $store = Store::whereIn('id',CustomHelpers::user_store())
                    ->select('id',DB::raw('concat(store.name,"-",store.store_type) as name'))->get();

        $data = [
            'store'=>$store,
            'state' => $state,
            'layout' => 'layouts.main'
        ];
        return view('admin.service.createtag',$data);
    }

    public function searchcity(Request $request) {
     $state_code = $request->input('state_code');
     $city_code = $request->input('city_code');
     $getdata = State::leftjoin('cities','states.id','cities.state_id')->where('states.state_code',$state_code)->where('cities.rto_code',$city_code)->get()->first();
       if ($getdata) {
         return response()->json($getdata);
       }else{
        return 0;
       }
    }


    public function createTagDB(Request $request)
    {
        $validator = Validator::make($request->all(),[
           'frame'  =>  'required_without_all:registration',
           'registration'  =>  'required_without_all:frame',
           'store_name'=>'required',
        ],
        [
            'frame.required_without_all'  =>  'This Field is required',
            'registration.required_without_all'  =>  'This Field is required',
            'store_name.required'=>'This field is required.',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        try{
            
           DB::beginTransaction();

            $frame = $request->input('frame');
            $reg_number = $request->input('registration');
            $customer_id = 0;
           
            $store_id = $request->input('store_name');
            $registration = preg_replace('/[^A-Za-z0-9\-]/', '', $reg_number);
            if($registration == 'UP32'){
                $registration = '';
            }

            if($registration == 'UP32' && empty($frame)){
                $registration = '';
                return back()->with('error','Please enter full registration.')->withInput();
            }

            $state_code = mb_substr($reg_number, 0, 2);
            $checkstate = State::where('state_code',$state_code)->get()->first();
            if ($checkstate == NULL) {
             return back()->with('state_error','State code not available .')->withInput();
            }else{
              $city_code = mb_substr($reg_number, 2, 2);
              $checkcity = City::where('state_id',$checkstate['id'])->where('rto_code',$city_code)->get()->first();
              if ($checkcity == NULL) {
                return back()->with('city_error','City code not available .')->withInput();
              }else{

             
           //Create Tag
           $count = job_card::where(DB::raw('DATE(in_time)'),DB::raw('CURRENT_DATE'))->where('store_id',$store_id)->select(DB::raw('count(id) as today_service'))->first();
           $count = ($count->today_service)+1;
           $today = str_replace('-','',date('Y-d'));
           $str = $count.'-S'.$store_id.'-'.$today;
           $tag = $str;
            // Search service data with frame and registration
           if ($frame) {
            $validator = Validator::make($request->all(),[
               'frame'  =>  'required_without_all:registration|alpha_num|min:6',
            ],['frame.required_without_all'  =>  'This Field is required',]);
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
               $servicedata = ServiceModel::where('frame', 'Like',"%{$frame}%")->select('service.id as service_id','service.customer_id')->get();
           }else{
             $validator = Validator::make($request->all(),[
               'registration'  =>  'required_without_all:frame|alpha_num|min:10|max:10',
            ],['registration.required_without_all'  =>  'This Field is required',]);
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
              $servicedata = ServiceModel::where('registration', $registration)->select('service.id as service_id','service.customer_id')->get();
           }

            if (count($servicedata) > 0) {
                if (count($servicedata) > 1) {
                    return back()->with('error','Please enter full frame or registration.')->withInput();
                }else{
                    $servicedata = $servicedata->first();
                    $service_id = $servicedata->service_id;
                    $customer_id = $servicedata->customer_id;
                }
            }else{
                //Search frame in sale
                if ($frame) {
                    $saledata = Sale::leftJoin('sale_order','sale_order.sale_id','sale.id')->where('sale_order.product_frame_number', 'Like', "%".$frame."%")->where('sale.status','<>','cancelled')->select('sale.id as sale_id','sale.customer_id')->get();
                }else{
                    $saledata = Sale::leftJoin('rto','rto.sale_id','sale.id')->where('rto.registration_number', 'Like', "%".$registration."%")->where('sale.status','<>','cancelled')->select('sale.id as sale_id','sale.customer_id')->get();
                }
                    
                    if (count($saledata) > 0) {
                        if (count($saledata) > 1) {
                            return back()->with('error','Please enter full frame or registration.')->withInput();
                        }else{
                            $saledata = $saledata->first();
                            $sale_id = $saledata->sale_id;
                            $customer_id = $saledata->customer_id;
                        }
                    }else{
                        $sale_id = 0;
                        $customer_id = 0;
                    }
                    //Insert service data
                    $insert = ServiceModel::insertGetId([
                        'sale_id' => $sale_id,
                        'customer_id' => $customer_id,
                        'frame'  => (($frame)?$frame:null),
                        'registration'  => (($registration)?$registration:null)
                        ]);
                    if ($insert) {
                        $service_id = $insert;
                    }else {
                       DB::rollback();
                       return back()->with('error','Some Error Occurred.')->withInput();
                    }
            }        

           if ($service_id) {

                $service_data = Job_card::where('service_id',$service_id)->where('store_id',$request->input('store_name'))->whereRaw('date(`created_at`)=CURRENT_DATE')
                        ->get()->first();
                $jobcard_id = isset($service_data->id)?$service_data->id:0;
                // for prebooking 
                $checkPreBooking = Job_card::where('service_id',$service_id)
                                                ->where('store_id',$request->input('store_name'))
                                                ->whereNull('tag')
                                                ->first();
                if(isset($checkPreBooking->id))
                {
                    $insertJobCard = Job_card::where('id',$checkPreBooking->id)
                                ->update([
                            'tag' => $tag,
                            'in_time' =>  date('Y-m-d H:i:s')
                           ]);
                }
                else{

                    if($jobcard_id)
                    {
                        
                            $insertJobCard = Job_card::where('id',$jobcard_id)
                                ->update([
                            'service_id' => $service_id,
                            'store_id'=>$request->input('store_name'),
                            'in_time' =>  date('Y-m-d H:i:s')
                           ]);
                
                    }
                    else{
    
                         $data = [
                            'tag' => $tag,
                            'service_id' => $service_id,
                            'store_id'=>$request->input('store_name'),
                            'in_time' =>  date('Y-m-d H:i:s')
                        ];
                       $insertJobCard = Job_card::insertGetId($data);
                    }
                }

                if(empty($insertJobCard)) {
                       DB::rollback();
                       return back()->with('error','Some Error Occurred.')->withInput();
                   }else{
                     /* Add action Log */
                      CustomHelpers::userActionLog($action='Create tag ',$insertJobCard,$customer_id);
                    DB::commit();
                    return redirect('/admin/service/create/tag')->with('success','Successfully Created.');
                   }
            }else{
                DB::rollback();
                return back()->with('error','Some Error Occurred.')->withInput();
            }
          }

         }
        }catch(\Illuminate\Database\QueryException $ex) {
            DB::commit();
            return redirect('/admin/service/create/tag')->with('error','some error occurred'.$ex->getMessage());
        }
    }

    public function createBooking_DB(Request $request)
    {
        $validator = Validator::make($request->all(),[
           'frame'  =>  'required_without_all:registration',
           'registration'  =>  'required_without_all:frame',
           'store_name'=>'required',
           'service_type'=>'required',
           'service_date'    =>  'required',
           'service_time'    =>  'required',
           'pick_drop'    =>  'required',
          'pickup_drop.*'    =>  'required_unless:pick_up,yes'
        ],
        [
            'frame.required_without_all'  =>  'This Field is required',
            'registration.required_without_all'  =>  'This Field is required',
            'store_name.required'=>'This field is required.',
            'service_type.required'=> 'This is required.', 
            'service_date.required_unless'=> 'This is required.', 
            'service_time.required'=> 'This is required.',
            'pick_drop.required'=> 'This is required.'
        
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        try{
           DB::beginTransaction();

           $frame = $request->input('frame');
           $registration = $request->input('registration');
           $picupdrop = $request->input('pick_drop');
           $pickup_drop  = $request->input('pickup_drop');
           $customer_id = 0;

           //Create Tag
           $count = job_card::where(DB::raw('DATE(in_time)'),DB::raw('CURRENT_DATE'))->select(DB::raw('count(id) as today_service'))->first();
          
           $count = ($count->today_service)+1;
           $today = str_replace('-','',date('Y-m-d'));
           $str = 'SER-'.$today.'-'.$count;
           $tag = $str;
            // Search service data with frame and registration
           if ($frame) {
               $servicedata = ServiceModel::where('frame', 'Like',"%{$frame}%")->select('service.id as service_id','service.customer_id')->get();
           }else{
              $servicedata = ServiceModel::where('registration', $registration)->select('service.id as service_id','service.customer_id')->get();
           }

            if (count($servicedata) > 0) {
                if (count($servicedata) > 1) {
                    return back()->with('error','Please enter full frame or registration.')->withInput();
                }else{
                    $servicedata = $servicedata->first();
                    $service_id = $servicedata->service_id;
                    $customer_id = $servicedata->customer_id;
                }
            }else{
                //Search frame in sale
                if ($frame) {
                    $saledata = Sale::leftJoin('sale_order','sale_order.sale_id','sale.id')->where('sale_order.product_frame_number', 'Like', "%".$frame."%")->where('sale.status','<>','cancelled')->select('sale.id as sale_id','sale.customer_id')->get();
                }else{
                    $saledata = Sale::leftJoin('rto','rto.sale_id','sale.id')->where('rto.registration_number', 'Like', "%".$registration."%")->where('sale.status','<>','cancelled')->select('sale.id as sale_id','sale.customer_id')->get();
                }
                    
                    if (count($saledata) > 0) {
                        if (count($saledata) > 1) {
                            return back()->with('error','Please enter full frame or registration.')->withInput();
                        }else{
                            $saledata = $saledata->first();
                            $sale_id = $saledata->sale_id;
                            $customer_id = $saledata->customer_id;
                        }
                    }else{
                        $sale_id = 0;
                        $customer_id = 0;
                    }
                    //Insert service data
                    $insert = ServiceModel::insertGetId([
                        'sale_id' => $sale_id,
                        'customer_id' => $customer_id,
                        'frame'  => (($frame)?$frame:null),
                        'registration'  => (($registration)?$registration:null)
                        ]);
                    if ($insert) {

                        $service_id = $insert;

                    }else {
                       DB::rollback();
                       return back()->with('error','Some Error Occurred.')->withInput();
                    }
            }        
            // Insert job card data
            if ($service_id) {

                    $data = [
                        'tag' => $tag,
                        'service_id' => $service_id,
                        'store_id'=>$request->input('store_name'),
                        'in_time' =>  date('Y-m-d H:i:s')
                    ];

                    $data_val = [
                        'service_id' => $service_id,
                        'store_id'=>$request->input('store_name'),
                        'job_card_type' =>$request->input('service_type'),
                        'service_date'  => $request->input('service_date'),
                        'status'=>'Booked',
                        'booking'=>'1'
                    ];  

                   $insertJobCard = Job_card::insertGetId($data_val);
                   $servicechargespickup = Config::get('constant.servicechargespickup');
                   $chargetype = 'Pickup';
                   if($insertJobCard)
                   {

                      if($picupdrop == 'yes')
                       {
                        
                        $service = ServiceCharge::insertGetId([
                        'job_card_id' => $insertJobCard,
                        'charge_type' => 'Charge',
                        'sub_type' => $chargetype,
                        'amount'  => $servicechargespickup
                        ]);
                        $pickup = Pickup::insertGetId([
                        'job_card_id' => $insertJobCard,
                         'type'  =>  join(',',$pickup_drop)

                        ]);
                      }
                   }

                   if ($request->input('booking_id')) {
                      $update = ServiceBooking::where('id',$request->input('booking_id'))->update([
                        'job_card_id' => $insertJobCard,
                        'status' => 'Booked'
                      ]);

                        $calldate = $request->input('service_date');
                        $type = 'service_booking';
                        $calltype = 'booking';
                        $next_call_date = date('Y-m-d', strtotime('+1 day', strtotime($calldate)));
                        $redirect = CustomHelpers::CreateCalling($type,$calltype,$request->input('store_name'),$request->input('booking_id'),$next_call_date);
                        
                        if ($redirect == NULL) {
                           DB::rollback();
                            return back()->with('error','Something went wrong.')->withInput();
                        }
                      if(empty($update)) {
                           DB::rollback();
                           return back()->with('error','Some Error Occurred.')->withInput();
                       }else{
                        /* Add action Log */
                      CustomHelpers::userActionLog($action='Create Service Pre Booking',$request->input('booking_id'),$customer_id);
                        DB::commit();
                        return redirect('/admin/service/create/booking/'.$request->input('booking_id').'')->with('success','Successfully Created.');
                       }
                   }

                   if(empty($insertJobCard)) {
                       DB::rollback();
                       return back()->with('error','Some Error Occurred.')->withInput();
                   }else{
                     /* Add action Log */
                    CustomHelpers::userActionLog($action='Create Service Pre Booking',$insertJobCard,$customer_id);
                    DB::commit();
                    return redirect('/admin/service/create/booking')->with('success','Successfully Created.');
                   }
            }else{
                DB::rollback();
                return back()->with('error','Some Error Occurred.')->withInput();
            }
        }catch(\Illuminate\Database\QueryException $ex) {
            DB::commit();
            return redirect('/admin/service/create/booking')->with('error','some error occurred'.$ex->getMessage());
        }
    }

    public function ServiceBooking_list()
    {
        $store = Store::whereIn('id',CustomHelpers::user_store())
                    ->select('id',DB::raw('concat(store.name,"-",store.store_type) as name'))->get();
        $data = [
            'store'=>$store,
            'layout' => 'layouts.main'
        ];
        
        return view('admin.service.bookingList',$data);
    }

    public function frameEntryList()
    {
        $data = [
            'layout' => 'layouts.main'
        ];
        
        return view('admin.service.frameEntryList',$data);
    }
    public function frameEntryList_api(Request $request) {
       
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;

           
            $api_data = Job_card::join('service','service.id','job_card.service_id')
                                ->select('job_card.id','tag','service.frame','service.registration','in_time','out_time');
            
            DB::enableQueryLog();
            if(!empty($serach_value))
            {
               $api_data->where(function($query) use ($serach_value){
                        $query->where('tag','like',"%".$serach_value."%")
                        ->orwhere('frame','like',"%".$serach_value."%")
                        ->orwhere('registration','like',"%".$serach_value."%")
                        ->orwhere('in_time','like',"%".$serach_value."%")
                        ->orwhere('out_time','like',"%".$serach_value."%");
                    });
               
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                    'tag',
                    'frame',
                    'registration',
                    'in_time',
                    'out_time'
                ];
                
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('job_card.in_time','asc');   
        
        $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();        
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

    public function ServiceBookingList_api(Request $request) {
        $search = $request->input('search');
        $store_name=$request->input('store_name');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;

            $store = Store::whereIn('id',CustomHelpers::user_store())
                    ->select('id')->get()->toArray();
            $api_data = Job_card::join('service','service.id','job_card.service_id')
                                ->leftjoin('pickup','pickup.job_card_id','job_card.id')
                                ->join('store','store.id','job_card.store_id')
                                ->where('job_card.status','Booked')
                                 ->where('job_card.booking','1')
                                ->whereIn('job_card.store_id',$store)
                                ->leftjoin('users','users.id','pickup.user_id')
                                ->select('job_card.id','service.frame','service.registration','pickup.pickup_time','pickup.drop_time','pickup.status','pickup.id as pickup_id',
                                   DB::raw('concat(users.name," ",ifnull( users.middle_name," ")," ",ifnull( users.last_name," ")) as name')
                              );

            
            if(!empty($serach_value))
            {
               $api_data->where(function($query) use ($serach_value){
                        $query->where('users.name','like',"%".$serach_value."%")
                        ->orwhere('service.frame','like',"%".$serach_value."%")
                        ->orwhere('service.registration','like',"%".$serach_value."%")
                        ->orwhere('pickup.pickup_time','like',"%".$serach_value."%")
                        ->orwhere('pickup.drop_time','like',"%".$serach_value."%")
                        ->orwhere('pickup.status','like',"%".$serach_value."%");
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

                    'service.frame',
                    'service.registration',
                    'pickup.pickup_time',
                    'pickup.drop_time',
                    'users.name',
                    'pickup.status'
                
                ];
                
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('job_card.id','asc');   
        
        $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
      
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

    public function BookingPickuptime(Request $request){

        $pickupid=$request->input('PickupId');
        $pickup = date('Y-m-d H:i:s');
        $pickupids = Pickup::where('id','=',$pickupid)->get()->first();
        if(!$pickupids){
             return 'Pickup id not exists .';
        } else {
          $service = Job_card::where('job_card.id',$pickupids['job_card_id'])->leftJoin('service','service.id','job_card.service_id')->select('service.customer_id')->first();
            $pickupdetails = Pickup::where('id','=',$pickupid)->update([
                        'pickup_time' => $pickup,
                        'user_id' => Auth::id()

            ]);

           if($pickupdetails == NULL) {
                return 'error';
            }else{ 
                  /* Add action Log */
                  CustomHelpers::userActionLog($action='Pri Booking Pickup Time',$pickupid,$service['customer_id']);               
                return 'success';               
            }
        }
          

    }

    public function BookingDroptime(Request $request){

        $pickupid=$request->input('PickupId');
        $drop_time= date('Y-m-d H:i:s');
        $pickupids=Pickup::where('id','=',$pickupid)->get()->first();

        if(!$pickupids){
             return 'Pickup id not exists .';
        }else {
            $service = Job_card::where('job_card.id',$pickupids['job_card_id'])->leftJoin('service','service.id','job_card.service_id')->select('service.customer_id')->first();
             $pickupdetails=Pickup::where('id','=',$pickupid)->update([
                        'drop_time'=>$drop_time                       
            ]);

           if($pickupdetails == NULL) {
                return 'error';
            } 
            else{
              /* Add action Log */
                  CustomHelpers::userActionLog($action='Pri Booking Drop Time',$pickupid,$service['customer_id']);                 
                return 'success';               
            }
        }
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

    public function createJobCard(Request $request, $jobCardId) {
        
        $jobcard_type = Master::where('type','job_card_type')->select('key','value')->get();
        $jobCard = job_card::leftJoin('service','job_card.service_id','service.id')->where('job_card.id',$jobCardId)->select(
          'job_card.*','service.customer_id', 'service.sale_id', 'service.frame', 'service.registration','service.selling_dealer_id', 'service.product_id','service.sale_date','service.manufacturing_year')->get()->first();
        $service_id = $jobCard->service_id;
        
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
        $repeatJC = Job_card::where('service_id',$service_id)->get();
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

        if($jobCard['job_card_type'] == null && $jobCard['customer_id'] != 0 && $jobCard['registration'] != null && $jobCard['frame'] != null && $jobCard['product_id'] != null && $jobCard['selling_dealer_id'] != null && $jobCard['sale_date'] != null && $jobCard['manufacturing_year'] != null){
          $data = [
              'history_service'   =>  $history_service,
              'jobcard_type' =>  $jobcard_type,
              'repeatJC' => $repeatJC,
              'jobCard' => $jobCard,
              'renewl_date' => $renewl_date,
              'amc' => $amc,
              'amc_product' => $amc_product,
              'getInsurance' => $getInsurance,
              'settingAMC' => $settingAMC,
              'same_type' => $same_type,
              'jobCardId' =>  $jobCardId,
              'layout' => 'layouts.main'
          ];
          return view('admin.service.create_job_card',$data);  
        }else{
           return back()->with('error','Error, Please update detail .');
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

    public function createJobCardDB(Request $request,$jobCardId) {
        $validator = Validator::make($request->all(),[
           'job_card_type'  =>  'required',
           'customer_status'  =>  'required',
           'frame_km'  =>  'required',
           'estimate_hour'  =>  'required',
           // 'estimate_delivery'  =>  'required',
           'oilinfornt_customer'  =>  'required'
        ],
        [
            'job_card_type.required'  =>  'This Field is required',
            'customer_status.required'  =>  'This Field is required',
            'frame_km.required'  =>  'This Field is required',
            'estimate_hour.required'  =>  'This Field is required',
            // 'estimate_delivery.required'  =>  'This Field is required',
            'oilinfornt_customer.required'  =>  'This Field is required'
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        try{
            date_default_timezone_set("Asia/Calcutta");
            DB::beginTransaction();

            $getJc = Job_card::where('id',$jobCardId)->get()->first();
            $store_id = $getJc['store_id'];
            $service = ServiceModel::where('id',$getJc->service_id)->get()->first();
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
            // echo $updateJobCardId;die;
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
            // print_r(gmdate('H:i',$estimate_hour));die;

            $estimate_delivery = $request->input('estimate_delivery');
            $estimate_delivery  = str_replace(' ','',$estimate_delivery);
            $estimate_delivery  = str_replace('PM','',$estimate_delivery);
            $estimate_delivery  = str_replace('AM','',$estimate_delivery);
            // echo $estimate_delivery;die;
            $arr = [];
            $d = new DateTime(date('Y-m-d').' '.$estimate_delivery);
            $rtime = CustomHelpers::roundUpToMinuteInterval($d,CustomHelpers::min_duration());
            $estimate_delivery = $rtime->format('H:i');
            // print_r($estimate_delivery);die;
            if (!empty($request->input('amc_type'))) {
               $job_card_type = 'AMC';
            }else{
              $job_card_type = $request->input('job_card_type');
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

               if (empty($request->input('checklist'))) {
                      return back()->with('error','check list require.')->withInput();
                    }else{
                      $checklist = $request->input('checklist');
                   
                      $count = count($checklist);
                      for ($i = 0; $i < $count ; $i++) { 
                         $getcheck = ServiceChecklistMaster::where('id',$checklist[$i])->get()->first();
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

            if ($job_card_type == 'Free') {
               if (!empty($request->input('free_sub_type'))) {
                $arr = ['sub_type' => $request->input('free_sub_type')];
                  if (empty($request->input('checklist'))) {
                    return back()->with('error','check list require.')->withInput();
                  }else{
                    $checklist = $request->input('checklist');
                    $count = count($checklist);
                    for ($i = 0; $i < $count ; $i++) { 
                       $getcheck = ServiceChecklistMaster::where('id',$checklist[$i])->get()->first();
                       
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

              $purchase_amc = $request->input('purchase_amc');
                    if (!empty($purchase_amc)) {

                      $setting = Settings::where('name','AMC')->get()->first();
                      $getAMC = AMCModel::where('service_id',$service['service_id'])->get()->first();
                      $amc_charge = $request->input('amc_amount');
                      $start_date = date('Y-m-d');
                      $date = strtotime($start_date);
                      $new_date = strtotime('+ 1 year', $date);
                      $end_date = date('Y-m-d', $new_date);
                      $amc_product = AMCProduct::where('id',$request->input('amc_prod'))->get()->first();

                      if ($getAMC && $getAMC->service_allowed >= $getAMC->service_taken && $setting->value == 'allowed') {
                          $amcdata = AMCModel::where('id',$getAMC->id)->update([
                                'service_id' => $service['service_id'],
                                'start_date' => $start_date,
                                'end_date' => $end_date,
                                'service_allowed' => $amc_product->service_allowed,
                                'service_taken' => $getAMC->service_taken+1,
                                'allowed_washing' => $amc_product->washing,
                                'amount' => $amc_charge
                            ]);
                      }else{
                         $amcdata = AMCModel::insertGetId([
                                'service_id' => $service['service_id'],
                                'start_date' => $start_date,
                                'end_date' => $end_date,
                                'service_allowed' => $amc_product->service_allowed,
                                'service_taken' => 1,
                                'allowed_washing' => $amc_product->washing,
                                'amount' => $amc_charge
                            ]);
                      }
                           
                        if ($amcdata) {
                           $charge = ServiceCharge::insertGetId([
                              'job_card_id' => $jobCardId,
                              'charge_type' => 'Charge',
                              'sub_type' => 'AMC',
                              'amount' => $amc_charge
                           ]);
                        }else{
                          DB::rollback();
                          return back()->with('error','Something went wrong.')->withInput(); 
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
                            }
                          }else{
                            return back()->with('error','HJC purchase require.')->withInput();
                          }
                          
                      }
                      
                }

                if ($job_card_type == 'Paid' && !empty($request->input('mejor_job'))) {
                        $mejor_job = $request->input('mejor_job');
                    }else{
                      $mejor_job = 'No';
                    }

                if ($job_card_type == 'Paid') {

                    if (empty($request->input('checklist'))) {
                      return back()->with('error','check list require.')->withInput();
                    }else{
                      $checklist = $request->input('checklist');
                   
                      $count = count($checklist);
                      for ($i = 0; $i < $count ; $i++) { 
                         $getcheck = ServiceChecklistMaster::where('id',$checklist[$i])->get()->first();
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
                             $update = CustomerDetails::where('id',$getcustomer->customer_details_id)->update([
                                'recent' => 1
                              ]);
                          }
                        //   else{
                        //         DB::rollback();
                        //         return back()->with('error','Some Error Occurred.')->withInput();
                        //   }
                        }else{
                            //Update alt mobile number in customer
                            $mobileupdate = Customers::where('id',$request->input('customer_id'))->update([
                            'alt_mobile' => $request->input('mobile')
                          ]);
                        

                        //   if ($mobileupdate) {
                        //   }else{
                        //         DB::rollback();
                        //         return back()->with('error','Some Error Occurred.')->withInput();
                        //   }
                      }
                    
                    }
            }
            // echo "test";die;
            // $buffer_time = '-2400 seconds';  // 0.5 hour  //only case of when customer status is drooping.
             if ($job_card_type == 'Accident' || $only_wash == 'Yes') {
              if ($request->input('acc_sub_type')) {
                 $sub_type = $request->input('acc_sub_type');
              }else{
                $sub_type = null;
              }
               $insertdata = [
                'job_card_type'    => $job_card_type,
                'customer_status'  =>  $request->input('customer_status'),
                'vehicle_km'  =>  $request->input('frame_km'),
                'only_wash' => $only_wash,
                'mejor_job' => $mejor_job,
                'sub_type' => $sub_type,
                'service_duration'  =>  gmdate('H:i:s',$estimate_hour),
                'oilinfornt_customer'    =>  $request->input('oilinfornt_customer')
                ];
                $insertJobCard = DB::table('job_card')->where('id',$jobCardId)->update($insertdata);

            }else{
              $insertdata = array_merge($arr,[
                'job_card_type'    =>  $job_card_type,
                'customer_status'  =>  $request->input('customer_status'),
                'vehicle_km'  =>  $request->input('frame_km'),
                'only_wash' => $only_wash,
                'mejor_job' => $mejor_job,
                'service_duration'  =>  gmdate('H:i:s',$estimate_hour),
                'oilinfornt_customer'    =>  $request->input('oilinfornt_customer'),
                'estimated_delivery_time'  =>  date('Y-m-d H:i:s',strtotime($estimate_delivery))
            ]);
           $data = [
               'job_card_type'    => $job_card_type,
               'customer_status'  =>  $request->input('customer_status'),
               'vehicle_km'  =>  $request->input('frame_km'),
               'service_duration'  =>  $estimate_hour,
               'oilinfornt_customer'    =>  $request->input('oilinfornt_customer'),
               'estimated_delivery_time'  =>  $estimate_delivery,
            //    'estimate_service_out_time'  =>  strtotime($buffer_time,strtotime($estimate_delivery))
           ];
          // $buffer_time = '-1800 seconds';  // 0.5 hour  //only case of when customer status is drooping.
            $startValidation= time();
            // print_r($startValidation);
            $default_start_time = strtotime('08:30:00');
            // echo '<br>'.$default_start_time;die;
            if($default_start_time >= $startValidation){
                $startValidation = strtotime('08:30:00');
            }
                $durationValidation = gmdate('H:i:s',$estimate_hour);
            //   print_r($durationValidation);die;
            if($data['customer_status'] == 'droping')
            {
                $endValidation= $startValidation+$estimate_hour+2400;
            }
            if($data['customer_status'] == 'waiting')
            {
                $endValidation= $startValidation+$estimate_hour+1800;
            }
            // echo date('H:i',$endValidation).'<br>'.$estimate_delivery.'<br>';
            // echo $endValidation.'<br>'.strtotime($estimate_delivery);
            $endValidation = strtotime('-5 minutes',$endValidation);
            // echo '<br>'.date('H:i',$endValidation);die;
            if ($endValidation > strtotime($estimate_delivery)) {
                       DB::rollback();
                       return back()->with('error','Please enter right estimate delivery time.')->withInput();
            }
        //   echo $exactDT; echo "<br>".$estimate_delivery;die();
        //    if ($exactDT != $estimate_delivery) {
        //        DB::rollback();
        //        return back()->with('error','Please enter right estimate delivery time.')->withInput();
        //    }else{
            $estimate_start_time =  strtotime('-'.$data['service_duration'].' seconds',strtotime($data['estimated_delivery_time']));
            // print_r($data);die;
            // echo date('H:i',$estimate_start_time);die;
           if($data['customer_status'] == 'droping')
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
            //    print_r($insertdata);
            //    echo 'buffer_time '.$buffer_time.'<br>'.'ast '.date('H:i',$allocation_start_time).'<br>'
            //         .'est '.date('H:i',$estimate_start_time).'<br>'.' bet '.$buffer_end_time.'<br>'.'aet '.date('H:i',$allocation_end_time);
            //         die;
           $estimate_start_time = date('Y-m-d H:i:s',strtotime('+1 minutes',$estimate_start_time));

           $estimate_end_time = date('Y-m-d H:i:s',$allocation_end_time);
            //    echo $estimate_start_time.'<br>';
            //    echo $estimate_end_time;die;
            //    print_r($insertdata);die;

        //    $totalBay = Bay::where('type',$data['job_card_type'])->get();
           $assignSlot = $this->findExactSlotBay($data,$estimate_start_time,$estimate_end_time,$jobCardId,date('Y-m-d'),$store_id);
            //    $assignSlot = '';
            // print_r($assignSlot);die;
            $insertJobCard = Job_card::where('id',$jobCardId)->update($insertdata);

            if ($insertJobCard) {
              if ($request->input('insurance') == 'Yes') {
                  $update = JobcardDetail::insertGetId([
                      'job_card_id' => $jobCardId,
                      'insurance' => $request->input('insurance'),
                      'insurance_date' => $renewl_date
                  ]);
               } else if ($request->input('insurance') == 'No') {
                $update = JobcardDetail::insertGetId([
                      'job_card_id' => $jobCardId,
                      'insurance' => $request->input('insurance'),
                      'insurance_available_type' => $request->input('available_type')
                  ]);
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
           if($assignSlot == 'success')
           {
           // print_r($insertdata);die();
            //    DB::commit();
           }
           else{
            //   echo "error";die;
                $data['estimate_start_time'] = $estimate_start_time;
                $data['estimated_delivery_time'] = $estimate_end_time;
                $duration = gmdate('H:i:s',strtotime($data['estimated_delivery_time'])-strtotime($data['estimate_start_time']));

                $assignSlot = $this->findDynamicSlot($jobCardId,$duration,$store_id);
                // print_r($assignSlot);die();

                if($assignSlot != 'success')
                {
                    // $insertJobCard = DB::table('job_card')->where('id',$jobCardId)->update($insertdata);
                    DB::commit();
                    return redirect('/admin/service/jobcard/list')->with('success','Successfully Updated, Sry, we are not able to find BAY for given estimation servicing time. Pls do it manually.');
                //    echo "Sry, we are not able to find BAY for given estimation servicing time. Pls do it manually.";die;
                }

           }
          }
        //    die;
            
           if(empty($insertJobCard))
           {
               DB::rollback();
               return back()->with('error','Some Error Occurred.')->withInput();
           }
        // }

        }catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return redirect('/admin/service/create/jobcard/'.$jobCardId)->with('error','some error occurred'.$ex->getMessage());
        }
        DB::commit();
        return redirect('/admin/service/jobcard/list')->with('success','Job card created successfully.');
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
                        $update_time = Job_card::where('id',$jobCardId)->update([
                            'est_service_in_time'=> $data['estimate_start_time'],
                            'est_service_out_time'=> $data['estimated_delivery_time']
                        ]);
                        
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
                    $update_time = Job_card::where('id',$jobCardId)->update([
                                        'est_service_in_time'=> $bay_allocation_data['start_time'],
                                        'est_service_out_time'=> $bay_allocation_data['end_time']
                                    ]);
                    
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
    public function bayList_api(Request $request) {
       
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
           
            $api_data = Job_card::join('service','service.id','job_card.service_id')
                            ->leftjoin('bay_allocation','bay_allocation.job_card_id','job_card.id')
                            ->leftjoin('bay','bay_allocation.bay_id','bay.id')
                            ->where('job_card.job_card_type','<>','Accident')
                            ->where('job_card.jobcard_create','1')
                            ->where('service.customer_id','<>','0')
                            ->select('job_card.id','job_card.tag','service.frame',
                                        'service.registration',
                                        'job_card.job_card_type',
                                        'job_card.jobcard_create',
                                        'job_card.customer_status',
                                        'job_card.vehicle_km',
                                        'job_card.service_duration',
                                        'job_card.only_wash',
                                        'job_card.status',
                                        'job_card.recording',
                                        'bay_allocation.id as bay_allocation_id',
                                        'bay_allocation.start_time as ba_stime',
                                        'bay_allocation.end_time as ba_etime',
                                        DB::raw('(select count(id) from service_part_request where bay_allocation_id = bay_allocation.id) as part_count'),
                                        DB::raw('concat(bay_allocation.date," ",job_card.estimated_delivery_time) as delivery_time')
                                    ,'job_card.service_in_time',
                                    'bay.name as bay_name',
                                    'bay.start_time',
                                    'bay.end_time',
                                    'job_card.service_out_time')
                            ->groupBy('bay_allocation.job_card_id');
            
            DB::enableQueryLog();
            if(!empty($serach_value))
            {
               $api_data->where(function($query) use ($serach_value){
                        $query->where('job_card.tag','like',"%".$serach_value."%")
                        ->orwhere('service.frame','like',"%".$serach_value."%")
                        ->orwhere('service.registration','like',"%".$serach_value."%")
                        ->orwhere('bay.name','like',"%".$serach_value."%")
                        ->orwhere('bay.start_time','like',"%".$serach_value."%")
                        ->orwhere('bay.end_time','like',"%".$serach_value."%")
                        ->orwhere('job_card.job_card_type','like',"%".$serach_value."%")
                        ->orwhere('job_card.customer_status','like',"%".$serach_value."%")
                        ->orwhere('job_card.vehicle_km','like',"%".$serach_value."%")
                        ->orwhere('job_card.estimated_delivery_time','like',"%".$serach_value."%")
                        ->orwhere('job_card.service_in_time','like',"%".$serach_value."%")
                        ->orwhere('job_card.service_out_time','like',"%".$serach_value."%");
                    });
               
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                    'tag',
                    'frame',
                    'registration',
                    'job_card_type',
                    'customer_status',
                    'vehicle_km',
                    'estimated_delivery_time',
                    'service_in_time',
                    'service_out_time'
                ];
                
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('job_card.id','desc');   
        
        $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

    public function get_schedule_bay_alloc(Request $request){
        $job_card_id = $request->input('job_card_id');
        $date = date('Y-m-d', strtotime('+1 day',strtotime(date('Y-m-d'))));
        $checkdata = BayAllocation::leftJoin('bay','bay.id','bay_allocation.bay_id')
                                    ->where('bay_allocation.job_card_id',$job_card_id)
                                    ->where('bay_allocation.date',$date)
                                    ->select('bay.name as bay_name','bay_allocation.*')
                                    ->first();
        
        return response()->json($checkdata);

    }

    public function floorSupervisorScheduleTask(Request $request)  {
        try{
            DB::beginTransaction();
            $job_card_id = $request->input('job_card_id');
            $sduration = $request->input('sduration');
            // $store = Job_card::where('id',$job_card_id)->get()->first();
           
            $store = Job_card::where('job_card.id',$job_card_id)->leftJoin('service','service.id','job_card.service_id')->select('job_card.*','service.customer_id')->first();
            // echo $job_card_id.'<br>'.strtotime($sduration);die;
            $store_id = $store['store_id'];
            $duration  = gmdate('H:i:s',$sduration);
            $d1 = new DateTime(date('Y-m-d').' '.$duration);
            $rtime1 = CustomHelpers::roundUpToMinuteInterval($d1,CustomHelpers::min_duration());
            $duration = $rtime1->format('H:i');
            //convert time to duration
            $arr1 = explode(':',$duration);
            $h = $arr1[0];
            $m = $arr1[1];
            $duration = (($h*60)+$m)*60;
            $duration = gmdate('H:i',$duration);

            // echo date('h:i:s',strtotime($sduration));die;
            $date = date('Y-m-d',strtotime('+1 day',strtotime(date('Y-m-d'))));
            $curr_time = Bay::select('start_time')->first();
            // echo $curr_time->start_time;die;
            $bay_controller  = new BayController();
            $dynamic_alloc = $bay_controller->dynamic_alloc($job_card_id,$duration,$date,$curr_time->start_time,$store_id);
            // print_r($dynamic_alloc);die;
            if($dynamic_alloc == 'success') {
              /* Add action Log */
                CustomHelpers::userActionLog($action='Schedule Service',$job_card_id,$store['customer_id']);
                DB::commit();
                return 'success';
            }else{
                DB::rollback();
                return 'bay_error';
            }
        }
        catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return 'some error occurred'.$ex->getMessage();
        }
        return 'error';
    }

    public function floorSupervisorBuyWorkStart(Request $request) {   
        $jobCardId = $request->input('JobCardId');
        $service = Job_card::where('job_card.id',$jobCardId)->leftJoin('service','service.id','job_card.service_id')->select('service.customer_id')->first();
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
                   /* Add action Log */
                  CustomHelpers::userActionLog($action='Service In',$jobCardId,$service['customer_id']);

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

    public function floorSupervisorBuyWorkEnd(Request $request)
    {   
        $jobCardId = $request->input('JobCardId');
        DB::enableQueryLog();
        $service = Job_card::where('job_card.id',$jobCardId)->leftJoin('service','service.id','job_card.service_id')->select('service.customer_id')->first();

        $getcheck = Job_card::leftjoin('service_charge','job_card.id','service_charge.job_card_id')
        ->leftJoin('service_part_request',function($join){
        $join->on('service_part_request.job_card_id','=','job_card.id')->where('service_part_request.part_id','<>',0);
         })
        ->where('job_card.id',$jobCardId)
        ->whereNotNull('job_card.service_in_time')
        ->whereNull('job_card.service_out_time')
        ->where('service_charge.charge_type','Charge')
        ->whereIn('service_charge.sub_type',array('Labour','Service'))
        ->get()
        ->first();
        if($getcheck)
        {
            $serviceOut = Job_card::where('id',$jobCardId)->update([
                'service_status' => 'done',
                'service_out_time' => DB::raw('CURRENT_TIMESTAMP')
            ]);
            if($serviceOut)
            {
               /* Add action Log */
                  CustomHelpers::userActionLog($action='Service Out',$jobCardId,$service['customer_id']);
                return 'success';
            }
        }
        else{
            return 'Service and Labour charge not added Or part not assigned!';
        }
        return 'error';
    }
    public function floorSupervisorCustomerConfirmation(Request $request)
    {
        $jobCardId = $request->input('JobCardId');
        $getcheck = Job_card::where('id',$jobCardId)->where('customer_confirmation','no')->first();
        if($getcheck)
        {
            $customer_cofirmation = Job_card::where('id',$jobCardId)->where('customer_confirmation','no')->update(['customer_confirmation' => 'yes']);
            if($customer_cofirmation)
            {
                return 'success';
            }
        }
        else{
            return "something wen't wrong";
        }
        return 'error';
        
    }
    public function partRequest($job_card_id) {

        $get_service_id = job_card::where('id',$job_card_id)->select('service_id','status')->first();
        if(!$get_service_id){
          return redirect('/admin/service/floor/supervisor')->with('error','ID not Exists.');
        }
        $service_id = $get_service_id['service_id'];

        $customer = ServiceModel::where('service.id',$service_id)->Join('customer','customer.id','service.customer_id')->select('customer.name','customer.mobile')->first();

        $getpart = ServicePartRequest::where('job_card_id',$job_card_id)->get();

        $partdetails = ServicePartRequest::leftjoin('part','part.id','service_part_request.part_id')
            ->where('service_part_request.deleted_at',NULL)
            ->where('service_part_request.job_card_id',$job_card_id)
            ->select(
              'service_part_request.*',
                'part.part_number',
                'service_part_request.part_name',
                'service_part_request.qty',
                'service_part_request.status',
                'service_part_request.assign_qty',
                'service_part_request.id',
                'part.price'
            )
            ->orderBy('service_part_request.created_at','asc')->get();

        if ($get_service_id['status'] == 'Closed') {
           return back()->with('error',"Job card already closed !");
        }else{
           $data = [
            'customer'  =>  $customer,
            'job_card_id' =>  $job_card_id,
            'getpart' => $partdetails,
            'layout' => 'layouts.main'
        ];
        
        return view('admin.service.partRequest',$data);
        }
       
    }
    public function partRequest_DB(Request $request,$job_card_id) {
        $validator = Validator::make($request->all(),[
            'part_name.*'    =>  'required',
            'part_qty.*'    =>  'required',
            
        ],
        [
            'part_name.*.required'  =>  'This Field is required',
            'part_qty.*.required'  =>  'This Field is required',
            
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        try{
            DB::beginTransaction();
            // $getbayallocId  =  BayAllocation::where('job_card_id',$job_card_id)->get()->first();
            // $bay_allocation_id = $getbayallocId['id'];
            $getPart = ServicePartRequest::where('job_card_id',$job_card_id)->get();
            // $getJobCard = Job_card::where('id',$job_card_id)->get()->first();
            $getJobCard = Job_card::where('job_card.id',$job_card_id)->leftJoin('service','service.id','job_card.service_id')->select('service.customer_id')->first();
            //Validation (Part request already added)
            // if (count($getPart) > 0) {
            //     DB::rollback();
            //     return back()->with('error',"Part request already send.")->withInput();
            // }else{
            if ($getJobCard['status'] == 'Closed') {
               return back()->with('error',"Job card already closed !");
            }else{
                $count = count($request->input('part_name'));
                for($i = 0; $i < $count ; $i++)
                {
                    $arr = [];
                    // if ($request->input('customer_conf')[$i] == 'yes') {
                    //   $approved = 1;
                    // }else{
                    //   $approved = 0;
                    // }
                    // if($request->input('customer_conf')[$i] == 'no')
                    // {
                    //     $arr = [
                    //         'call_status'   =>  $request->input('call_status')[$i]
                    //     ];

                    //     if ($request->input('call_status')[$i] == 'refused') {
                    //       $arr = [
                    //         'call_refuse_reason'   =>  $request->input('reason')[$i]
                    //       ];
                    //     }
                    // }
                    $data = array_merge($arr,[
                        'job_card_id'   =>  $job_card_id,
                        'part_name' =>  $request->input('part_name')[$i],
                        'qty'   =>  $request->input('part_qty')[$i],
                        // 'confirmation' =>  'customer',
                        // 'approved' => $approved,
                        // 'approved_by' => Auth::id()
                    ]);
                    $insert = ServicePartRequest::insertGetId($data);
                    if(empty($insert))
                    {
                        DB::rollback();
                        return back()->with('error',"Something Wen't Wrong.")->withInput();
                    }
                }
            }
        }catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return redirect('/admin/service/part/request/'.$job_card_id)->with('error','some error occurred'.$ex->getMessage());
        }
        /* Add action Log */
        CustomHelpers::userActionLog($action='Part Request',$job_card_id,$getJobCard['customer_id']);
        DB::commit();
        return redirect('/admin/service/part/request/'.$job_card_id)->with('success','Successfully Requested.');
    }


    public function CustomerConfirmation(Request $request) {
      try{
        $confirmation = $request->input('confirmation');
        $id = $request->input('id');
        $call_status = $request->input('call_status');
        $refused_reason = $request->input('refused_reason');
        if (empty($confirmation)) {
             return array('type' => 'error', 'msg' => 'Customer confirmation field required.');
          }
        if ($confirmation == 'yes') {
          $confirm = ServicePartRequest::where('id',$id)->update([
                      'confirmation' =>  'customer',
                      'approved' => 1,
                      'call_status' => null,
                      'call_refuse_reason' => null,
                      'approved_by' => Auth::id()
          ]);
          if ($confirm == null) {
            return array('type' => 'error', 'msg' => 'Something went wrong.');
          }else{
             return array('type' => 'success', 'msg' => 'Customer confirmation added successfully.');
          }
        }
         if ($confirmation == 'no') {
          if ($confirmation == 'no' && empty($call_status)) {
             return array('type' => 'error', 'msg' => 'Call status field required.');
          }elseif ($confirmation == 'no' && $call_status == 'refused' && empty($refused_reason)) {
            return array('type' => 'error', 'msg' => 'Refused reason field required.');
          }else{
            if ($call_status == 'refused') {
               $confirm = ServicePartRequest::where('id',$id)->update([
                        'confirmation' =>  'customer',
                        'call_status' => $call_status,
                        'call_refuse_reason' => $refused_reason
                  ]);
                  if ($confirm == null) {
                    return array('type' => 'error', 'msg' => 'Something went wrong.');
                  }else{
                     return array('type' => 'success', 'msg' => 'Customer confirmation added successfully.');
                  }
            }else{
               $confirm = ServicePartRequest::where('id',$id)->update([
                        'confirmation' =>  'customer',
                        'call_status' => $call_status
                  ]);
                  if ($confirm == null) {
                    return array('type' => 'error', 'msg' => 'Something went wrong.');
                  }else{
                     return array('type' => 'success', 'msg' => 'Customer confirmation added successfully.');
                  }
            }
          }
         
        }
      }catch(\Illuminate\Database\QueryException $ex) {
           return 'error';
        }
    }

    public function floorSupervisorJobcard(Request $request) {
       $jobcard_id = $request->input('JobCardId');
       $jobcard = Job_card::Join('master','master.key','job_card.job_card_type')
                            ->where('job_card.id','=',$jobcard_id)
                            ->where('master.type','job_card_type')->get('master.details')->first();
        return response()->json($jobcard);

    }

    public function floorSupervisorServicechargeCheck(Request $request) {
        $jobcard_id = $request->input('JobCardId');

        $servicecharge = Job_card::leftjoin('service_charge','service_charge.job_card_id','job_card.id')
                ->where('service_charge.job_card_id','=',$jobcard_id)
                ->whereIn('service_charge.charge_type',['Service','Labour'])
                ->select('service_charge.charge_type','service_charge.amount','service_charge.sub_type','job_card.no_labour_charge')
                ->get();
         return response()->json($servicecharge);

    }

    public function floorSupervisorServicecharge_DB(Request $request) {

        $jobcard_id = $request->input('JobCardId');
        $servicharge = $request->input('servicharge');
        $rows = count($request->input('amount'));

        $service = 'Service';
        $labour = 'Labour'; 

        $jobcard = Job_card::where('job_card.id',$jobcard_id)->leftJoin('service','service.id','job_card.service_id')->select('job_card.*','service.customer_id')->first();

        // $jobcard = Job_card::where('id','=',$jobcard_id)->get()->first();

        if(!$jobcard){
             return 'jobCard id not exists .';
        } else {
            
        $check = ServiceCharge::where('job_card_id',$jobcard_id)->where('charge_type','Charge')->where('sub_type','Service')->get()->first();


             if ($request->input('no_charge') == 1) {
                $servicedata = ServiceCharge:: insertGetId([ 'job_card_id' => $jobcard_id,'charge_type' => 'Labour','sub_type'=> 'labour','amount'=> 0 ]);

                $update = Job_card::where('id',$jobcard_id)->update([
                  'no_labour_charge' => $request->input('no_charge')
               ]);

                if ($update == null) {
                  return 'error';
                }
             }else{
              for($i = 0 ; $i < $rows ; $i++) {
                  $insert_pdi_detail = [ 
                    'job_card_id' =>$jobcard_id,
                    'charge_type' =>$labour,
                    'amount'   =>  $request->input('amount')[$i]['value'],
                    'sub_type'   =>  $request->input('laburtitle')[$i]['value']
                    ];
                  $insert = ServiceCharge::insertGetId($insert_pdi_detail);
              }
             }

           if($check){
              return 'Service Charge Exists.';
           }
           else{
               $servicedata = ServiceCharge:: insertGetId([ 'job_card_id' => $jobcard_id,'charge_type' => 'Charge','sub_type' => $service,'amount' => $servicharge ]);
           }  

          if($servicedata == NULL) {
            return 'error';
          } else{     
           /* Add action Log */
            CustomHelpers::userActionLog($action='Add Jobcard Charges',$jobcard_id,$jobcard['customer_id']);           
            return 'success';               
          }
     }
          
    }

    //store supervisor assign part's
    public function partRequest_list() {
        $data = [
            'layout' => 'layouts.main'
        ];
        
        return view('admin.service.part_request_list',$data);
    }
    public function PartRequestList_api(Request $request) {
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
        DB::enableQueryLog();
        $api_data = Job_card::leftjoin('service_part_request','service_part_request.job_card_id','job_card.id')
            ->whereNotNull('service_part_request.job_card_id')
            ->where('service_part_request.deleted_at',null)
            ->select('job_card.id',
                    'job_card.tag',
                    // 'job_card.customer_confirmation',
                    DB::raw('count(service_part_request.job_card_id) as no_of_part'),
                    'job_card.service_in_time', 
                    'job_card.job_card_type', 
                    'job_card.service_history', 
                    'job_card.service_out_time')
            ->groupBy('job_card.id');
            
           
            if(!empty($serach_value))
            {
               $api_data->where(function($query) use ($serach_value){
                        $query->where('job_card.tag','like',"%".$serach_value."%")
                        // ->orwhere('job_card.customer_confirmation','like',"%".$serach_value."%")
                        ->orwhere('service_part_request.id','like',"%".$serach_value."%")
                        ->orwhere('job_card.service_in_time','like',"%".$serach_value."%")
                        ->orwhere('job_card.service_out_time','like',"%".$serach_value."%");
                    });
               
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                    'id',
                    'tag',
                    // 'customer_confirmation',
                    DB::raw('count(service_part_request.id)'),
                    'service_in_time',
                    'job_card_type',
                    'service_out_time'
                ];
                
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('job_card.id','desc');   
        
        $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }
    public function servicePartAssign($id)
    {
        $tag = job_card::leftjoin('service','service.id','job_card.service_id')
                        ->leftjoin('store','job_card.store_id','store.id')
                        ->leftJoin('product','service.product_id','product.id')
                  ->where('job_card.id',$id)->select('job_card.*','service.manufacturing_year','product.model_name','product.model_variant','product.color_code','service.frame',DB::raw('concat(name,"-",store_type) as store_name'))->first();
        if(!$tag){
          return redirect('/admin/service/stock/part/request/list')->with('error','ID not Exists.');
        }

        $bayInfo = ServicePartRequest::leftjoin('job_card','service_part_request.job_card_id','job_card.id')
        ->where('service_part_request.part_id','0')
            ->select(
                // DB::raw('count(service_part_request) as part_count'),
                'service_part_request.part_name',
                'service_part_request.qty',
                'service_part_request.part_availability',
                'service_part_request.id'
            )
            ->where('service_part_request.job_card_id',$id)
            ->orderBy('service_part_request.created_at','asc')->get();

        $partdetails = ServicePartRequest::leftjoin('part','part.id','service_part_request.part_id')
            ->where('service_part_request.deleted_at',NULL)
            ->where('service_part_request.part_id','<>',0)
            ->where('service_part_request.job_card_id',$id)
            ->select(
                'part.part_number',
                'service_part_request.part_name',
                'service_part_request.qty',
                'service_part_request.part_availability',
                'service_part_request.status',
                'service_part_request.assign_qty',
                'service_part_request.id',
                'part.price'
            )
            ->orderBy('service_part_request.created_at','asc')->get();
            //For Part Availibity Status
          $available_type = CustomHelpers::GetPartAvailabilityStatus();
        $data = [
            'jobcardId' => $id,
            'partdetails' => $partdetails,
            'partInfo'  =>  $bayInfo,
            'tag' => $tag,
            'available_type' => $available_type,
            'layout' => 'layouts.main'
        ];
        
        return view('admin.service.partAssign',$data);
    }
    public function storeSupervisorGetPartInfo(Request $request) {
        $part_no  = $request->input('part_no');
        $store_id  = $request->input('store_id');
        $getdata = Parts::where('part_number',''.$part_no.'')->get()->first();
        if ($getdata) {
           $part_id = $getdata['id'];
           $partdata = Parts::leftjoin('part_stock','part.id','part_stock.part_id')
            ->leftJoin('part_cell','part_stock.cell_id','part_cell.id')
            ->leftjoin('part_row','part_row.id','part_cell.row_id')
            ->leftjoin('part_rack','part_rack.id','part_row.rack_id')
            ->where('part_stock.part_id',$part_id)
            ->where('part_stock.part_id',$part_id)
            ->where('part_stock.store_id',$store_id)
            ->select('part.price','part.name','part_cell.cell_name','part_row.row_name','part_rack.rack_name')->first();
        }
        return response()->json($partdata);

    }
    public function servicePartAssign_DB(Request $request, $job_card_id) {
        // try{
        //     DB::beginTransaction();
        //     $part_count = $request->input('part_count');
        //     $job_card_id = $request->input('jobcard_id');

        //     $getJc = job_card::where('id',$job_card_id)->get()->first();
      
        //     for($i = 0 ; $i < $part_count ; $i++)
        //     {
        //         $part_no  = $request->input('part_number'.$i);
        //         $part_price  = $request->input('part_price'.$i);
        //         $part_qty = $request->input('part_qty'.$i);
        //         $request_id  = $request->input('part_request_id'.$i);
        //         $getdata = Parts::where('part_number',$part_no)->get()->first();

        //         $checkEngine = Master::where('type','engine_oil_part')->where('key','Engine Oil')->whereRaw(DB::raw('FIND_IN_SET("'.$part_no.'",value)'))->get()->first();

        //         if ($checkEngine) {
        //             $getDiscount = ServiceCharge::where('job_card_id',$job_card_id)->where('charge_type','Discount')->where('sub_type','Engine Oil')->where('promo_code','<>',NULL)->get()->first();
        //             if ($getDiscount) {
        //                $discAmt =  floor($part_price*$part_qty*$getDiscount['discount_rate']/100);
        //                $addCharge = ServiceCharge::where('id',$getDiscount['id'])->where('job_card_id',$job_card_id)->update(['amount' => $discAmt]);
        //                if ($addCharge == NULL) {
        //                  DB::rollback();
        //                  return back()->with('error','Something went wrong !');
        //                }
        //             }
        //         }
        //         if($getdata) {
        //           $part_id =  $getdata['id'];
        //           $store_id = $getJc['store_id'];

        //           $check_part_qty = PartStock::where('part_id',$part_id)->where('store_id',$store_id)->where('quantity','>=',$part_qty)->first();
                  
        //            if(empty($check_part_qty)) {

        //               DB::rollback();
        //               return back()->with('error','Error, Stock not available for '.$part_no.' Part Number, Please Enter Correct Quantity.')->withInput();
        //             }else{
        //               $updatePartStock = PartStock::where('part_id',$part_id)->where('store_id',$store_id);
        //               $inc = $updatePartStock->increment('sale_qty',$part_qty);
        //               $dec = $updatePartStock->decrement('quantity',$part_qty);
        //             }

                 

        //             $update = ServicePartRequest::where('id',$request_id)
        //             ->update([
        //                 'part_id' => $getdata->id,
        //                 'assign_qty' => $part_qty,
        //                 'status' => 'Assigned'
        //                 ]);
        //         }else{
        //             DB::rollback();
        //             return back()->with('error','Part not assigned');
        //         }

        //         // $total_partprice = $part_price+$total_partprice;
        //     }
        //     // Add amount in jobcard
        //      // $jobcard =  job_card::where('id',$job_card_id)->update([
        //      //                'total_amount' => $getJc->total_amount+$total_partprice
        //      //            ]);
        //   if ($update) {
        //      DB::commit();
        //      return redirect('/admin/service/store/supervisor/bay/assign/part/'.$job_card_id)->with('success','Successfully Assigned.');
        //   }else{
        //     DB::rollback();
        //     return back()->with('error','Part not assign');
        //   }
        // }catch(\Illuminate\Database\QueryException $ex) {
        //     DB::rollback();
        //     return back()->with('error','some error occurred'.$ex->getMessage());
        // }
       
         try{
            DB::beginTransaction();
            // $count = $request->input('part_issue');
              $part_count = $request->input('part_count');
            $job_card_id = $request->input('jobcard_id');

            // $getJc = job_card::where('id',$job_card_id)->get()->first();
            $getJc = Job_card::where('job_card.id',$job_card_id)->leftJoin('service','service.id','job_card.service_id')->select('job_card.*','service.customer_id')->first();
      
            for($i = 0 ; $i < $part_count ; $i++) {
                $part_no  = $request->input('part_number'.$i);
                $part_price  = $request->input('price'.$i);
                $part_qty = $request->input('part_qty'.$i);
                $request_id  = $request->input('part_request_id'.$i);
                $getdata = Parts::where('part_number',$part_no)->get()->first();

                $checkEngine = Master::where('type','engine_oil_part')->where('key','Engine Oil')->whereRaw(DB::raw('FIND_IN_SET("'.$part_no.'",value)'))->get()->first();

                if ($checkEngine) {
                    $getDiscount = ServiceCharge::where('job_card_id',$job_card_id)->where('charge_type','Discount')->where('sub_type','Engine Oil')->where('promo_code','<>',NULL)->get()->first();
                    if ($getDiscount) {
                       $discAmt =  floor($part_price*$part_qty*$getDiscount['discount_rate']/100);
                       $addCharge = ServiceCharge::where('id',$getDiscount['id'])->where('job_card_id',$job_card_id)->update(['amount' => $discAmt]);
                       if ($addCharge == NULL) {
                         DB::rollback();
                         return back()->with('error','Something went wrong !');
                       }
                    }
                }
                if($getdata) {
                  $part_id =  $getdata['id'];
                  $store_id = $getJc['store_id'];

                  $check_part_qty = PartStock::where('part_id',$part_id)->where('store_id',$store_id)->where('quantity','>=',$part_qty)->first();
                  
                   if(empty($check_part_qty)) {

                       $Po = PurchaseOrderRequest::insertGetId([
                        'model_name' => $part_no,
                        'store_id' => $store_id
                       ]);
                       if ($Po == null) {
                         DB::rollback();
                        return back()->with('error','Something went wrong');
                       }

                       $update = ServicePartRequest::where('id',$request_id)->update([
                        'part_id' => $part_id,
                        'assign_qty' => 0,
                        'issue_qty' => 0,
                        'status' => 'Assigned',
                        // 'part_issue' => 'yes',
                        'part_availability' => $request->input('part_availability'.$i)
                        ]);

                    }else{
                      if ($request->input('part_availability'.$i) == '3' || $request->input('part_availability'.$i) == '4') {
                        $pid = $getdata->id;
                        $issue_qty = 0;
                        $available = $request->input('part_availability');
                      }else{
                        $updatePartStock = PartStock::where('part_id',$part_id)->where('store_id',$store_id);
                        $inc = $updatePartStock->increment('sale_qty',$part_qty);
                        $dec = $updatePartStock->decrement('quantity',$part_qty);
                        $pid = $getdata->id;
                        $issue_qty = $part_qty;
                        $available = $request->input('part_availability');

                      }
                       $update = ServicePartRequest::where('id',$request_id)->update([
                        'part_id' => $pid,
                        'assign_qty' => $issue_qty,
                        'issue_qty' => $issue_qty,
                        'status' => 'Assigned',
                        // 'part_issue' => 'yes',
                        'part_availability' => $request->input('part_availability')
                        ]);
                    }
                   
                    if ($update == null) {
                        DB::rollback();
                        return back()->with('error','Part not assign !');
                      }
                }else{
                    DB::rollback();
                    return back()->with('error','Part not available !');
                }
            }
          
        }catch(\Illuminate\Database\QueryException $ex) {
          DB::rollback();
            return redirect('/admin/service/store/assign/part/'.$job_card_id)->with('error','some error occurred'.$ex->getMessage());
        }
        /* Add action Log */
        CustomHelpers::userActionLog($action='Part Assign',$job_card_id,$getJc['customer_id']);
  
        DB::commit();
        return redirect('/admin/service/store/assign/part/'.$job_card_id)->with('success','Part Issue Successfully .');
    }
    public function createPDIDB(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'frame'   =>  'required',
            'model'   =>  'required',
            'color'   =>  'required',
            'variant'   =>  'required',
            'date_of_damage'   =>  'required',
            'damage_location'   =>  'required',
            'emp_name'   =>  'required',
            // 'employee_code'   =>  'required',
            'date_of_repair'   =>  'required',
            'repair_loc'   =>  'required',
            'desc'   =>  'required',
            'load_ref'  =>  'required_if:damage_reason,load_ref',
            'executive'  =>  'required_if:damage_reason,executive',
            'location'  =>  'required_if:damage_reason,location',
            'driver'  =>  'required_if:damage_reason,driver',
            'item_missing_qty'  =>  'required_if:damage_reason,item_missing_qty',
            'desc_damaged.*'   =>  'required',
            'part_amt.*'   =>  'required',
            // 'lab_amt.*'   =>  'required',
            'total.*'   =>  'required',
            'total_amount'  =>  'required|gt:0'
        ],
        [
            'frame.required'  =>  'This Field is required',
            'model.required'  =>  'This Field is required',
            'color.required'  =>  'This Field is required',
            'variant.required'  =>  'This Field is required',
            'date_of_damage.required'  =>  'This Field is required',
            'damage_location.required'  =>  'This Field is required',
            'emp_name.required'  =>  'This Field is required',
            // 'employee_code.required'  =>  'This Field is required',
            'date_of_repair.required'  =>  'This Field is required',
            'desc.required'  =>  'This Field is required',
            'load_ref.required_if'  =>  'This Field is required',
            'executive.required_if'  =>  'This Field is required',
            'location.required_if'  =>  'This Field is required',
            'driver.required_if'  =>  'This Field is required',
            'item_missing_qty.required_if'  =>  'This Field is required',
            'desc_damaged.*.required'  =>  'This Field is required',
            'part_amt.*.required'  =>  'This Field is required',
            // 'lab_amt.*.required'  =>  'This Field is required',
            'total.*.required'  =>  'This Field is required',
            'total_amount.required'  =>  'This Field is required',
            'total_amount.gt'  =>  'Amount should be greater than 0.',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        try{
           

        }catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return redirect('/admin/create/PDI')->with('error','some error occurred'.$ex->getMessage());
        }
        DB::commit();
        return redirect('/admin/create/PDI')->with('success','Successfully Created.');

    }

    public function pdiSummary(Request $request){
        $data = [
            'layout' => 'layouts.main'
        ];
        return view('admin.PDI.pdiSummary',$data);
    }
    public function pdiSummary_api(Request $request) {
       
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;

           
            $api_data = PDI::leftJoin('product_details','product_details.id','pdi.product_details_id')
                                ->leftJoin('product','product.id','product_details.product_id')
                                ->leftJoin('store as damage_store','damage_store.id','pdi.damage_location')
                                ->leftJoin('store as repair_store','repair_store.id','pdi.repair_location')
                                ->leftJoin('users as employee', 'employee.id','pdi.responsive_emp_id')
                                ->select(   
                                            'pdi.id',
                                            'product_details.frame',
                                            'product.model_name',
                                            'product.model_variant',
                                            'product.color_code',
                                            'pdi.date_of_damage',
                                            'damage_store.name as damage_location',
                                            'pdi.date_of_repair',
                                            'repair_store.name as repair_location',
                                            'employee.name as responsive_emp',
                                            'pdi.desc_of_accident',
                                            'pdi.hirise_invoice',
                                            DB::raw('IF(pdi.approved_status = 0, "Pending","Approve") as status')
                        );
            
            DB::enableQueryLog();
            if(!empty($serach_value))
            {
               $api_data->where(function($query) use ($serach_value){
                        $query->where('product_details.frame','like',"%".$serach_value."%")
                        ->orwhere('product.model_name','like',"%".$serach_value."%")
                        ->orwhere('product.model_variant','like',"%".$serach_value."%")
                        ->orwhere('product.color_code','like',"%".$serach_value."%")
                        ->orwhere('pdi.date_of_damage','like',"%".$serach_value."%")
                        ->orwhere('damage_store.name','like',"%".$serach_value."%")
                        ->orwhere('pdi.date_of_repair','like',"%".$serach_value."%")
                        ->orwhere('repair_store.name','like',"%".$serach_value."%")
                        ->orwhere('employee.name','like',"%".$serach_value."%")
                        ->orwhere('pdi.desc_of_accident','like',"%".$serach_value."%")
                        ->orwhere('pdi.approved_status','like',"%".((strpos('Pending',$serach_value) === FALSE )? '1' : '0')."%");
                    });
               
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                    'product_details.frame',
                    'product.model_name',
                    'product.model_variant',
                    'product.color_code',
                    'pdi.date_of_damage',
                    'damage_store.name as damage_location',
                    'pdi.date_of_repair',
                    'repair_store.name as repair_location',
                    'employee.name as resposive_emp',
                    'pdi.desc_of_accident',
                    'pdi.approved_status'
                ];
                
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('pdi.created_at','desc');   
        
        $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        // print_r(DB::getQueryLog());die;
        
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

    public function fiList() {
       $data = [
            'layout' => 'layouts.main'
        ];
        
        return view('admin.service.fi_List',$data);
    }

    public function fi_List_api(Request $request) {
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;

           
            $api_data = Job_card::leftjoin('service','service.id','job_card.service_id')
                ->select('job_card.id','job_card.tag','service.frame',
                            'service.registration',
                            'job_card.job_card_type',
                            'job_card.customer_status',
                            'job_card.vehicle_km',
                            'job_card.wash',
                            'job_card.service_duration',
                            // 'job_card.customer_confirmation',
                            'job_card.service_in_time',
                            'job_card.fi_status',
                            'job_card.recording',
                            'job_card.service_out_time');
            
            DB::enableQueryLog();
            if(!empty($serach_value))
            {
               $api_data->where(function($query) use ($serach_value){
                        $query->where('job_card.tag','like',"%".$serach_value."%")
                        ->orwhere('service.frame','like',"%".$serach_value."%")
                        ->orwhere('service.registration','like',"%".$serach_value."%")
                        ->orwhere('job_card.fi_status','like',"%".$serach_value."%")
                        ->orwhere('job_card.wash','like',"%".$serach_value."%")
                        ->orwhere('job_card.job_card_type','like',"%".$serach_value."%")
                        ->orwhere('job_card.customer_status','like',"%".$serach_value."%")
                        ->orwhere('job_card.vehicle_km','like',"%".$serach_value."%")
                        ->orwhere('job_card.estimated_delivery_time','like',"%".$serach_value."%")
                        ->orwhere('job_card.service_in_time','like',"%".$serach_value."%")
                        ->orwhere('job_card.service_out_time','like',"%".$serach_value."%");
                    });
               
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                    'tag',
                    'frame',
                    'registration',
                    'job_card_type',
                    'customer_status',
                    'vehicle_km',
                    'wash',
                    'estimated_delivery_time',
                    'service_in_time',
                    'fi_status',
                    'service_out_time'
                ];
                
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('job_card.id','desc');   
        
        $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

    public function fiWash($id) {
        try {
            $service = Job_card::where('job_card.id',$id)->leftJoin('service','service.id','job_card.service_id')->select('service.customer_id')->first();

            $data = Job_card::where('id',$id)->update([
                'wash' => 'yes'
            ]);

            if($data) {
              /* Add action Log */
                CustomHelpers::userActionLog($action='Washed.',$id,$service['customer_id']);
                return 'success';
            } else{
                return 'error';                
            }
           
        }  catch(\Illuminate\Database\QueryException $ex) {
            return 'error';
        }
    }

    public function getJobCard($id) {
        $getdata = job_card::leftjoin('service','job_card.service_id','service.id')
                    ->where('job_card.id',$id)->select('job_card.*')->first();
        $service_id = $getdata['service_id'];
        $servicedetail = ServiceModel::leftjoin('product','product.id','service.product_id')
                            ->where('service.id',$service_id)
                            ->select('product.model_category as vehicle_type')
                            ->first();
        if ($getdata['job_card_type'] == 'Free') {
          $sub_id = $getdata['sub_type'];
          $type = JobcardSubtype::where('id',$sub_id)->get()->first();
          $vehicle_type = $servicedetail['vehicle_type'];
          $checklist = ServiceChecklistMaster::where('service_type',$type['name'])->where('showin','1')->where('vehicle_type',$vehicle_type)->get();
        }else if ($getdata['job_card_type'] == 'Paid') {
          $type = $getdata['job_card_type'];
          $vehicle_type = $servicedetail['vehicle_type'];
          $checklist = ServiceChecklistMaster::where('service_type',$type)->where('showin','1')->where('vehicle_type',$vehicle_type)->get();
        }else if ($getdata['job_card_type'] == 'General') {
          $sub_id = $getdata['sub_type'];
          $type = JobcardSubtype::where('id',$sub_id)->get()->first();
          $vehicle_type = $servicedetail['vehicle_type'];
          $checklist = ServiceChecklistMaster::where('service_type',$type['name'])->where('showin','1')->where('vehicle_type',$vehicle_type)->get();
        }else{
          $checklist = [];
        }
        $data = array('jobcard' => $getdata, 'checklist' => $checklist );
        return response()->json($data);
    }

    public function fiApprove(Request $request) {
        try {
            $duration  = $request->input('duration');
            if (empty($request->input('fi_status'))) {
                return array('type' => 'error','msg' => 'Fi status is required.' );
            }if ($request->input('fi_status') == 'notok' && empty($request->input('remarks'))) {
               return array('type' => 'error','msg' => 'Remark is required.' );
            }
            $id = $request->input('id');
            if($request->input('fi_status') == 'notok') {
                $bay_alloc = $request->input('bay_alloc');
                if(empty($bay_alloc))
                return array('type' => 'error','msg' => 'Bay allocate is required.' );
            }
            DB::beginTransaction();
            $getdata = Job_card::where('job_card.id',$id)->leftJoin('service','service.id','job_card.service_id')->select('job_card.*','service.customer_id')->first();
            if ($getdata['fi_status'] == 'ok') {
                 return array('type' => 'error','msg' => 'Final inspection status is ok can not changed .' );
            }else{
                if ($request->input('fi_status') == 'notok') {
                    $update = job_card::where('id',$id)->update([
                    'service_status' => 'pending',
                    'fi_status' => $request->input('fi_status'),
                    'bay_alloc_required'    =>  $request->input('bay_alloc')
                    ]);
                }else{
                    $update = job_card::where('id',$id)->update([
                        'fi_status' => $request->input('fi_status'),
                        'service_done_date' => date('Y-m-d'),
                        'bay_alloc_required' =>  $request->input('bay_alloc')
                    ]);
                }
                if ($update) {
                     $insert = FinalInspection::insertGetId([
                        'job_card_id'   =>  $request->input('id'),
                        'remark' => $request->input('remarks'),
                        'status' => $request->input('fi_status'),
                        'bay_alloc' =>  $request->input('bay_alloc')
                    ]);
                }
                if($request->input('bay_alloc') == 'required' && $request->input('fi_status') == 'notok') {
                    // $bay_alloc_id = BayAllocation::where('job_card_id',$id)->select(DB::raw('max(id) as bay_alloc_id'))->first()->bay_alloc->id;


                    // $bay_alloc_for_fi = $this->bay_alloc_after_fi($id,$duration);
                    $duration = gmdate('H:i:s',$duration);
                    $d1 = new DateTime(date('Y-m-d').' '.$duration);
                    $rtime1 = CustomHelpers::roundUpToMinuteInterval($d1,CustomHelpers::min_duration());
                    $duration = $rtime1->format('H:i');
                    //convert time to duration
                    $arr1 = explode(':',$duration);
                    $h = $arr1[0];
                    $m = $arr1[1];
                    $duration = (($h*60)+$m)*60;

                    $bay_alloc_for_fi = $this->findDynamicSlot($id,$duration,$getdata['store_id']);
                    
                    if($bay_alloc_for_fi[0] == 'success')
                    {
                        DB::commit();
                        return array('type' => 'success','msg' => 'Final Inspection updated succefully' );
                    }else{
                        $update_bal_alloc =  BayAllocation::where('job_card_id',$id)->update(['fi_status'=>'pending']);

                        DB::commit();
                        return array('type' => 'success','msg' => 'Bay Can not be assign auto. goto manually and fix it .' );
                    }
                }
                if ($insert == NULL) {
                    DB::rollback();
                    return array('type' => 'error','msg' => 'Something went wrong.' );
                }else{
                  /* Add action Log */
                  CustomHelpers::userActionLog($action='Final Inspection Approve',$id,$getdata['customer_id']);
                    DB::commit();
                    return array('type' => 'success','msg' => 'Final Inspection updated succefully' );
                }
            }
        }  catch(\Illuminate\Database\QueryException $ex) {
            return array('type' => 'error','msg' => 'Something went wrong.' );
        }
    }

    public function bay_alloc_after_fi($jobCardId,$duration) {
        
        // $duration = gmdate('H:i:s',$duration);
            // DB::enableQueryLog();
        // echo $duration;die;
        $statement = DB::statement(DB::raw("set @i = 0"));
        $statement = DB::statement(DB::raw("set @j = -1"));
        $statement = DB::statement(DB::raw("set @prev_end_time = ''"));
        $statement = DB::statement(DB::raw("set @temp = 0"));
        $statement = DB::statement(DB::raw("set @duration = '".$duration."'"));
        $statement = DB::statement(DB::raw("set @user_id = ".Auth::id()));
            
      

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
                        'start_time'    =>  date('Y-m-d H:i:s',strtotime('+1 minutes',strtotime($start_time))),
                        'end_time'  =>  date('Y-m-d H:i:s',$end_time),
                        'date'  =>  date('Y-m-d'),
                        'status'    =>  'pending'
                    ];
                    // print_r($bay_allocation_data);die;
                    $insert = BayAllocation::insertGetId(array_merge($bay_allocation_data,[
                                        'job_card_id'   =>  $jobCardId])
                                    );
                    
                    if($insert)
                    {
                        return array('success',date('Y-m-d H:i:s',strtotime($start_time)),date('Y-m-d H:i:s',$end_time),$insert);
                    }
                }
            }
        // die;
        //  print_r($findtime);die;
        return 'not-allocate';
    }

    public function serviceHirise_list() {
        $data = [
            'layout' => 'layouts.main'
        ];
        
        return view('admin.service.fi_hirise',$data);
    }

    public function serviceHiriseList_api(Request $request) {
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;

           
            $api_data = Job_card::Join('service','service.id','job_card.service_id')
                    ->where('job_card.service_status','done')
                    ->where('job_card.fi_status','ok')
                    ->select('job_card.id','job_card.tag',
                                'service.frame',
                                'service.registration',
                                'job_card.job_card_type',
                                'job_card.customer_status',
                                'job_card.vehicle_km',
                                'job_card.wash',
                                'job_card.service_duration',
                                // 'job_card.customer_confirmation',
                                'job_card.invoice_no',
                                'job_card.hirise_amount',
                                'job_card.service_in_time',
                                'job_card.service_status',
                                'job_card.fi_status',
                                'job_card.service_out_time');
            
            DB::enableQueryLog();
            if(!empty($serach_value))
            {
               $api_data->where(function($query) use ($serach_value){
                        $query->where('job_card.tag','like',"%".$serach_value."%")
                        ->orwhere('service.frame','like',"%".$serach_value."%")
                        ->orwhere('service.registration','like',"%".$serach_value."%")
                        ->orwhere('job_card.fi_status','like',"%".$serach_value."%")
                        ->orwhere('job_card.wash','like',"%".$serach_value."%")
                        ->orwhere('job_card.job_card_type','like',"%".$serach_value."%")
                        ->orwhere('job_card.customer_status','like',"%".$serach_value."%")
                        ->orwhere('job_card.vehicle_km','like',"%".$serach_value."%")
                        ->orwhere('job_card.estimated_delivery_time','like',"%".$serach_value."%")
                        ->orwhere('job_card.service_in_time','like',"%".$serach_value."%")
                        ->orwhere('job_card.service_out_time','like',"%".$serach_value."%");
                    });
               
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                    'tag',
                    'frame',
                    'registration',
                    'job_card_type',
                    'customer_status',
                    'vehicle_km',
                    'wash',
                    'service_status',
                    'estimated_delivery_time',
                    'service_in_time',
                    'invoice_no',
                    'hirise_amount',
                    'fi_status',
                    'service_out_time'
                ];
                
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('job_card.id','desc');   
        
        $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array); 
    }

    public function updareServiceHirise(Request $request) {
        try {
            if (empty($request->input('amount')) || empty($request->input('invoice_no'))) {
               return 'verror';
            }else{
                $id = $request->input('id');
                $service = Job_card::where('job_card.id',$id)->leftJoin('service','service.id','job_card.service_id')->select('service.customer_id')->first();

                $update = job_card::where('id',$id)->update([
                    'invoice_no' => $request->input('invoice_no'),
                    'hirise_amount' => $request->input('amount')
                ]); 
                if ($update == NULL) {
                    return 'error';
                }else{
                  /* Add action Log */
                    CustomHelpers::userActionLog($action='Update Service Hirise',$id,$service['customer_id']);

                     return 'success';
                }
            }
        }  catch(\Illuminate\Database\QueryException $ex) {
            return 'error';
        }
    }

    public function servicePayment_list() {
        $data = [
            'layout' => 'layouts.main'
        ];
        
        return view('admin.service.service_payment_list',$data);
    }

    public function servicePaymentList_api(Request $request) {
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit = empty($limit) ? 10 : $limit ;

            $api_data = Job_card::Join('service','service.id','job_card.service_id')
                ->leftJoin('payment',function($join){
                $join->on(DB::raw('payment.type = "service" and payment.type_id'),'=','job_card.id')
                ->where('payment.status','received');
                 })
                ->leftjoin('service_part_request','service_part_request.job_card_id','job_card.id')
                ->leftjoin('part','part.id','service_part_request.part_id')
                ->leftjoin('service_charge','job_card.id','service_charge.job_card_id')
                ->where('job_card.service_status','done')
                ->where('job_card.fi_status','ok')
                ->where('service_charge.deleted_at',null)
                ->where('service_part_request.deleted_at',null)
                ->where('service_charge.charge_type','<>','Discount')
                ->where('service_part_request.approved','1')
                ->where('service_part_request.confirmation','<>','insurance')
                ->whereNotNull('job_card.invoice_no')
                ->select('job_card.id','job_card.tag','service.frame',
                    'service.registration',
                    'job_card.job_card_type',
                    'job_card.customer_status',
                    'job_card.vehicle_km',
                    'job_card.wash',
                    'job_card.service_duration',
                    'job_card.service_status',
                    'job_card.sub_type',
                    'job_card.invoice_no',
                    'job_card.hirise_amount',
                    'job_card.total_amount',
                    DB::raw('IFNULL(ROUND(TIMESTAMPDIFF( DAY, job_card.service_done_date, now() ) % 30.4375),0) as day'),
                    DB::raw('IFNULL(sum(payment.amount),0) as paid_amount'),
                    'job_card.service_in_time',
                    'job_card.fi_status',
                    'job_card.service_out_time')
                    ->groupBy('job_card.id');
            
            DB::enableQueryLog();
            if(!empty($serach_value))
            {
               $api_data->where(function($query) use ($serach_value){
                        $query->where('job_card.tag','like',"%".$serach_value."%")
                        ->orwhere('service.frame','like',"%".$serach_value."%")
                        ->orwhere('service.registration','like',"%".$serach_value."%")
                        ->orwhere('job_card.fi_status','like',"%".$serach_value."%")
                        ->orwhere('job_card.wash','like',"%".$serach_value."%")
                        ->orwhere('job_card.job_card_type','like',"%".$serach_value."%")
                        ->orwhere('job_card.customer_status','like',"%".$serach_value."%")
                        ->orwhere('job_card.vehicle_km','like',"%".$serach_value."%")
                        ->orwhere('job_card.estimated_delivery_time','like',"%".$serach_value."%")
                        ->orwhere('job_card.service_in_time','like',"%".$serach_value."%")
                        ->orwhere('job_card.service_out_time','like',"%".$serach_value."%");
                    });
               
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                    'tag',
                    'frame',
                    'registration',
                    'job_card_type',
                    'customer_status',
                    'vehicle_km',
                    'wash',
                    'service_status',
                    'estimated_delivery_time',
                    'service_in_time',
                    'invoice_no',
                    'hirise_amount',
                    'paid_amount',
                    'total_amount',
                    'fi_status',
                    'service_out_time'
                ];
                
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('job_card.id','desc');   
        
        $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();


        // add amount 
        foreach ($api_data as &$value) {
            $id = $value['id'];
            $job_card_type = $value['job_card_type'];
            $data = CustomHelpers::GetJCTotalAmount($id,$job_card_type);
            $value['total_amount'] = $data['total_amount'];
            $value['JpHonda_discount'] = $data['JpHonda_discount'];
            $value['garage_charge'] = $data['garaj_amount']; 
            $value['paid_amount'] = $data['paid_amount'];
            
        }

        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array); 
    }

    public function service_payment($id) {
        $pay_mode = Master::where('type','payment_mode')->select('key','value')->get();
        $jobcard = job_card::where('id',$id)->select('job_card.job_card_type','total_amount','job_card_type','sub_type','id','store_id','tag',
          DB::raw('IFNULL(ROUND(TIMESTAMPDIFF( DAY, service_done_date, now() ) % 30.4375),0) as day')
        )->first();
        $data = CustomHelpers::GetJCTotalAmount($id,$jobcard['job_card_type']);
            $total = $data['total_amount'];
            $JpHonda_discount = $data['JpHonda_discount'];
            $paid = $data['paid_amount'];
            $garaj_amount = $data['garaj_amount'];

        // $total_amount = $jobcard['total_amount']+$partPrice+$charge-$discount;
        $data = array(
            'jobcard' => $jobcard, 
            'paid' => $paid,
            'garaj_charge' => $garaj_amount,
            'total_amount' => $total,
            'jphonda_discount' => $JpHonda_discount,
            'pay_mode'  =>  $pay_mode,
            'layout' => 'layouts.main'
        );
        return view('admin.service.service_payment',$data);
    }

      public function servicePayment_DB(Request $request) {
         try {
            $timestamp = date('Y-m-d G:i:s');
            $this->validate($request,[
                'amount'=>'required',
                'payment_mode'=>'required',
                'transaction_number'=>'required|unique:payment,transaction_number',
                'transaction_charges'=>'required',
                'receiver_bank_detail'=>'required',
                // 'security_amount'   =>  'required'
            ],[
                'amount.required'=> 'This field is required.', 
                'payment_mode.required'=> 'This field is required.', 
                'transaction_number.required'=> 'This field is required.', 
                'transaction_charges.required'=> 'This field is required.', 
                'receiver_bank_detail.required'=> 'This field is required.', 
                // 'security_amount.required'=> 'This field is required.'
            ]);
            DB::beginTransaction();  
            $id = $request->input('jobcard_id');
                     
                $jobcard = job_card::where('id',$request->input('jobcard_id'))->select(DB::raw('IFNULL(ROUND(TIMESTAMPDIFF( DAY, service_done_date, now() ) % 30.4375),0) as day'),'total_amount','service_id','job_card_type','sub_type','payment_status')->first();

                if ($jobcard->payment_status == 'done') {
                  return redirect('/admin/service/payment/'.$request->input('jobcard_id'))->with('error','Payment already done !')->withInput();
                }

                $service = Job_card::where('job_card.id',$id)->leftJoin('service','service.id','job_card.service_id')->select('service.customer_id')->first();

                $data = CustomHelpers::GetJCTotalAmount($id,$jobcard['job_card_type']);
                $total = $data['total_amount'];
                $JpHonda_discount = $data['JpHonda_discount'];
                $paid = $data['paid_amount'];
                $garaj = $data['garaj_amount'];
                $partPrice = $data['partPrice'];
                $labourcharge = $data['labourcharge'];
                $service_charge = $data['service_charge'];
                $pickup_charge = $data['pickup_charge'];
                $charge = $data['charge'];

                $paid_amount = $paid + $request->input('amount'); 
                $totalAmt = $garaj+$total;
                $amount = $request->input('amount');               
                $sub_type = JobcardSubtype::where('id',$jobcard->sub_type)->get()->first();
           
                // if ($jobcard->job_card_type == 'PDI' || $sub_type['name'] == 'Best Deal' || $jobcard->job_card_type == 'Best Deal' || $JpHonda_discount == 100) {
                //   $totalAmt = 0;
                //   $amount = 0;
                // }


                if ($totalAmt < $paid_amount) {

                    return redirect('/admin/service/payment/'.$request->input('jobcard_id'))->with('error','Your amount is too more .');

                }else if ($totalAmt > $paid_amount && $total < $paid_amount) {
                  
                    return redirect('/admin/service/payment/'.$request->input('jobcard_id'))->with('error','Please enter amount with garaj charge .');

                }else{
                    if ($totalAmt == $paid_amount || $totalAmt > $paid_amount) {
                       $update = job_card::where('id',$request->input('jobcard_id'))->update([
                         'payment_status' => 'done',
                         'total_amount' => $totalAmt
                       ]);

                       // check if job card type open using best deal
                       if ($jobcard['job_card_type'] == 'Best Deal' || $sub_type['name'] == 'Best Deal') {
                         $check_arr = BestDealSale::where('job_card_id',$request->input('jobcard_id'))
                                               ->where('tos','best_deal')
                                               ->first();
                       if(isset($check_arr->id))
                       {
                           $sp = CustomHelpers::calculate_selling($check_arr,($garaj+$total));
   
                           $update_best_deal = BestDealSale::where('id',$check_arr->id)
                                               ->update([
                                                   'selling_price' => $sp,
                                                   'status' =>  'Ok'
                                               ]);
                       } 
                       }
                    }

                    $arr = [];
                    if($request->input('payment_mode') == 'Cash')
                    {
                        $arr = [
                            'status' => 'received'
                        ];
                    }
                    $paydata = Payment::insertGetId(
                    array_merge($arr,[
                        'type_id'=> $request->input('jobcard_id'),
                        'type' => 'service',
                        'payment_mode' => $request->input('payment_mode'),
                        'transaction_number' => $request->input('transaction_number'),
                        'transaction_charges' => $request->input('transaction_charges'),
                        'receiver_bank_detail' => $request->input('receiver_bank_detail'),
                        'amount' => $amount,
                        'store_id' => $request->input('store_id'),
                    ])
                    );
                    if($paydata == NULL) {
                        DB::rollback();
                        return redirect('/admin/service/payment/'.$request->input('jobcard_id'))->with('error','some error occurred');
                    }else{
                          if ($paydata && $jobcard['job_card_type'] == 'HJC') {

                            $update = CustomHelpers::updateServiceDiscount($id,$partPrice,$labourcharge,$service_charge,$pickup_charge,$charge);

                            if ($update == NULL) {
                               DB::rollback();
                               return redirect('/admin/service/payment/'.$request->input('jobcard_id'))->with('error','some error occurred');
                            }
                          }
                           /* Add action Log */
                         CustomHelpers::userActionLog($action='Service Payment',$id,0);
                         DB::commit();
                         return redirect('/admin/service/payment/'.$request->input('jobcard_id'))->with('success','Amount Successfully Paid .');
                    }

                }
            
        }  catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return redirect('/admin/service/payment/'.$request->input('jobcard_id'))->with('error','some error occurred'.$ex->getMessage());
        }
     
    }

    public function servicePayment_detail($id) {
        $job_card = job_card::where('id',$id)->first();
        $total = CustomHelpers::GetJCTotalAmount($id,$job_card['job_card_type']);
        $totalAmt = $total['total_amount'];
        $JpHonda_discount = $total['JpHonda_discount']; 
        $paid = $total['paid_amount'];
        $garaj = $total['garaj_amount'];
        $payData = Payment::where('type_id',$id)->where('status','<>','cancelled')->where('type','service')->get();

        $data = array(
            'job_card' => $job_card,
            'payData' => $payData,
            'totalAmt' => $totalAmt,
            'layout' => 'layouts.main'
        );
         return view('admin.service.service_paymet_detail',$data);
    }

    public function frameOutList() {
        $data = array(
            'layout' => 'layouts.main'
        );
        return view('admin.service.service_out_list',$data);
    }

    public function frameOutListApi(Request $request) {
       $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;

            $api_data = Job_card::Join('service','service.id','job_card.service_id')
                ->leftJoin('feedback','feedback.jobcard_id','job_card.id')
                ->leftJoin('payment',function($join){
                $join->on(DB::raw('payment.type = "service" and payment.type_id'),'=','job_card.id')
                    ->where('payment.status','received');
                 })
                ->where('job_card.service_status','done')
                ->where('job_card.status','Done')
                ->where('job_card.payment_status','done')
                ->orwhere('job_card.pay_letter',1)
                ->where('job_card.fi_status','ok')
                ->whereNotNull('job_card.invoice_no')
                ->select('job_card.id','job_card.tag','service.frame',
                    'service.registration',
                    'job_card.job_card_type',
                    'job_card.customer_status',
                    'job_card.vehicle_km',
                    'job_card.wash',
                    'job_card.service_duration',
                    'job_card.service_status',
                    'job_card.status',
                    'job_card.payment_status',
                    'job_card.pay_letter',
                    'job_card.invoice_no',
                    'job_card.hirise_amount',
                    'job_card.total_amount',
                    'feedback.jobcard_id',
                    'feedback.rating',
                    'feedback.customer_problem',
                    DB::raw('IFNULL(sum(payment.amount),0) as paid_amount'),
                    'job_card.service_in_time',
                    'job_card.fi_status',
                    'job_card.service_out_time')
                    ->groupBy('job_card.id');
                  //  ->havingRaw('sum(payment.amount) = job_card.total_amount');
            
            DB::enableQueryLog();
            if(!empty($serach_value))
            {
               $api_data->where(function($query) use ($serach_value){
                        $query->where('job_card.tag','like',"%".$serach_value."%")
                        ->orwhere('service.frame','like',"%".$serach_value."%")
                        ->orwhere('service.registration','like',"%".$serach_value."%")
                        ->orwhere('job_card.fi_status','like',"%".$serach_value."%")
                        ->orwhere('job_card.wash','like',"%".$serach_value."%")
                        ->orwhere('job_card.job_card_type','like',"%".$serach_value."%")
                        ->orwhere('job_card.customer_status','like',"%".$serach_value."%")
                        ->orwhere('job_card.vehicle_km','like',"%".$serach_value."%")
                        ->orwhere('job_card.estimated_delivery_time','like',"%".$serach_value."%")
                        ->orwhere('job_card.service_in_time','like',"%".$serach_value."%")
                        ->orwhere('job_card.service_out_time','like',"%".$serach_value."%");
                    });
               
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                    'tag',
                    'frame',
                    'registration',
                    'job_card_type',
                    'customer_status',
                    'vehicle_km',
                    'wash',
                    'service_status',
                    'estimated_delivery_time',
                    'service_in_time',
                    'invoice_no',
                    'hirise_amount',
                    'paid_amount',
                    'total_amount',
                    'fi_status',
                    'service_out_time'
                ];
                
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('job_card.id','desc');   
        
        $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
       // print_r(DB::getQueryLog());die;
        
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array); 
    }

    public function testRideList() {
        $data = array(
            'layout' => 'layouts.main'
        );
        return view('admin.service.test_ride_list',$data);
    }

    public function testRideList_api(Request $request) {
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
         DB::enableQueryLog();
        $api_data = Job_card::leftjoin('service','service.id','job_card.service_id')
            ->leftJoin(DB::raw('(SELECT t.tr_type, t.tr_in_time, t.tr_out_time, t.jobcard_id FROM test_ride t WHERE t.id IN(SELECT MAX(MAX.id) FROM test_ride MAX GROUP BY MAX.jobcard_id) )as test_ride'),function($join) {
                    $join->on('job_card.id','test_ride.jobcard_id');                 
            })
            ->where('job_card.payment_status','pending')
            ->whereNull('job_card.invoice_no')
            ->select('job_card.id',
            'job_card.tag',
            'service.frame',
            'service.registration',
            'job_card.job_card_type',
            'job_card.customer_status',
            'job_card.payment_status',
            // 'job_card.customer_confirmation',
            'test_ride.tr_type',
            'test_ride.tr_in_time',
            'job_card.total_amount',
            'test_ride.tr_out_time');
            
            if(!empty($serach_value))
            {
               $api_data->where(function($query) use ($serach_value){
                        $query->where('job_card.tag','like',"%".$serach_value."%")
                        ->orwhere('service.frame','like',"%".$serach_value."%")
                        ->orwhere('service.registration','like',"%".$serach_value."%")
                        ->orwhere('job_card.job_card_type','like',"%".$serach_value."%")
                        ->orwhere('job_card.customer_status','like',"%".$serach_value."%")
                        ->orwhere('test_ride.tr_in_time','like',"%".$serach_value."%")
                        ->orwhere('test_ride.tr_out_time','like',"%".$serach_value."%");
                    });
               
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                    'tag',
                    'frame',
                    'registration',
                    'job_card_type',
                    'customer_status',
                    'estimated_delivery_time',
                    'tr_type',
                    'tr_in_time',
                    'tr_out_time'
                ];
                
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('job_card.id','desc');   
        
       $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
       //print_r(DB::getQueryLog());die();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

    public function testRideOut(Request $request) {
        try {
            date_default_timezone_set("Asia/Calcutta");
            $out_time  = date("H:i:s");
            if (empty($request->input('type'))) {
               return 'verror';
            }else{
                $get = TestRide::where('jobcard_id',$request->input('jobcard_id'))->where('tr_type',$request->input('type'))->get();
                if (count($get) > 0) {
                    return 'found';
                }else{
                    $insert = TestRide::insertGetId([
                        'jobcard_id' => $request->input('jobcard_id'), 
                        'tr_out_time' => $out_time,
                        'tr_type' => $request->input('type')
                    ]); 
                    if ($insert == NULL) {
                        return 'error';
                    }else{
                         return 'success';
                    }
                }
            }
        }  catch(\Illuminate\Database\QueryException $ex) {
            return 'error';
        }
    }

    public function testRideIn($jobid) {
         try {
            date_default_timezone_set("Asia/Calcutta");
            $in_time  = date("H:i:s");
            if (empty($jobid)) {
               return 'verror';
            }else{
                $insert = TestRide::where('jobcard_id',$jobid)->update([
                    'tr_in_time' => $in_time
                ]); 
                if ($insert == NULL) {
                    return 'error';
                }else{
                     return 'success';
                }
            }
        }  catch(\Illuminate\Database\QueryException $ex) {
            return 'error';
        }
    }

    // public function testRideGet($id) {
    //     $trdata = TestRide::where('jobcard_id', $id)->get();
    //     print_r($trdata);
    // }

    public function feedback(Request $request) {
         try {
            if (empty($request->input('rate')) || empty($request->input('jobcard_id')) || empty($request->input('feedback'))) {
               return 'verror';
            }else{
                $get = Feedback::where('jobcard_id', $request->input('jobcard_id'))->get();
                if (count($get) > 0) {
                    return 'added';
                }else{
                     $insert = Feedback::insertGetId([
                        'jobcard_id' => $request->input('jobcard_id'),
                        'rating' => $request->input('rate'),
                        'feedback' => $request->input('feedback') ,
                        'rating_via' => Auth::id()
                    ]); 
                     if ($request->input('rate') >= 3) {
                        $update = job_card::where('id',$request->input('jobcard_id'))->update([
                        'out_time' =>  date('Y-m-d H:i:s'),
                        'status' => 'Closed'
                     ]);
                     }
                   
                    if ($insert == NULL) {
                        return 'error';
                    }else{
                         return 'success';
                    }
                } 
            }
        }  catch(\Illuminate\Database\QueryException $ex) {
            return 'error';
        }
    }

    public function crmFeedback(Request $request) {
         try {
            if (empty($request->input('problem'))) {
               return 'verror';
            }else{
                $update = Feedback::where('jobcard_id',$request->input('jobcard_id'))->update([
                    'customer_problem' => $request->input('problem'),
                    'user_id' => Auth::id()
                ]); 
                $jobcard_update = job_card::where('id',$request->input('jobcard_id'))->update([
                        'out_time' =>  date('Y-m-d H:i:s'),
                        'status' => 'Closed'
                     ]);
                if ($update == NULL) {
                    return 'error';
                }else{

                    $user_id = Auth::id();
                    $users = Users::where('id','=',$user_id)->get()->first();
                    $problem = $request->input('problem');
                   
                    if($users)
                    {
                        $user_name=$users->name;
                        $email=$users->email;
                        $crm_email = "rinki@dkinfosolution.com";    
                        $crm_name='JPHonda';

                        Mail::send('admin.emails.crm_feedback', ['name' => $user_name,'problem'=>$problem], function($message) use ($email,$user_name,$crm_email,$crm_name)
                        {
                            $message->from($email, $user_name)->to($crm_email, $crm_name)->subject('CRM Feedback!');
                        });
                    }
                     return 'success';
                }
            }
        }  catch(\Illuminate\Database\QueryException $ex) {
            return 'error';
        }
    }

    public function testRideView($id) {
         $testRide = TestRide::leftJoin('job_card','test_ride.jobcard_id','job_card.id')->leftJoin('service','service.id','job_card.service_id')->where('test_ride.jobcard_id',$id)->select('test_ride.*','job_card.tag','service.frame','service.registration')->get();
         $data = array(
            'testRide' => $testRide,
            'layout' => 'layouts.main' 
          );
        return view('admin.service.test_ride_view',$data); 
    }

    public function feedbackList() {
        $data = array(
            'layout' => 'layouts.main' 
          );
        return view('admin.service.feedback_list',$data); 
    }

    public function feedbackListApi(Request $request) {
      $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
         DB::enableQueryLog();
        $api_data = Feedback::leftJoin('job_card','feedback.jobcard_id','job_card.id')
            ->leftjoin('service','service.id','job_card.service_id')
            ->leftjoin('users as user','user.id','feedback.user_id')
            ->leftjoin('users as guard','guard.id','feedback.rating_via')
            ->select('feedback.id',
            'job_card.tag',
            'service.frame',
            'service.registration',
            'feedback.rating',
            'feedback.feedback',
            'feedback.customer_problem',
             DB::raw('concat(user.name," ",ifnull( user.middle_name," ")," ",ifnull( user.last_name," ")) as crmuser'),
              DB::raw('concat(guard.name," ",ifnull( guard.middle_name," ")," ",ifnull( guard.last_name," ")) as guardname')
            );
            
           
            if(!empty($serach_value))
            {
               $api_data->where(function($query) use ($serach_value){
                        $query->where('job_card.tag','like',"%".$serach_value."%")
                        ->orwhere('service.frame','like',"%".$serach_value."%")
                        ->orwhere('service.registration','like',"%".$serach_value."%")
                        ->orwhere('feedback.rating','like',"%".$serach_value."%")
                        ->orwhere('feedback.feedback','like',"%".$serach_value."%");
                    });
               
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                    'job_card.tag',
                    'service.frame',
                    'service.registration',
                    'feedback.rating',
                    'feedback.feedback',
                    'feedback.customer_problem',
                    'guardname',
                    'crmuser',
                ];
                
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('feedback.id','desc');   
        
       $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
       //print_r(DB::getQueryLog());die();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);  
    }

    public function JcdelayList() {
        $data = [
            'layout' => 'layouts.main'
        ];
        
        return view('admin.service.jc_delay_list',$data);
    }
    public function JcdelayList_api(Request $request) {
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start;
        $limit =  empty($limit) ? 10 : $limit;
        DB::enableQueryLog();
            $api_data = Job_card::leftjoin('service','job_card.service_id','service.id')
                    ->where('job_card.service_status','pending')
                    ->whereNotNull('job_card.service_out_time')
                    ->where(DB::raw('CURRENT_TIMESTAMP'),'>','job_card.service_out_time')
                    ->select('job_card.id',
                            'job_card.tag',
                            'job_card.service_in_time',
                            'job_card.service_out_time',
                            'job_card.job_card_type',
                            'job_card.customer_status',
                            'job_card.vehicle_km',
                            'job_card.service_duration',
                            'job_card.estimated_delivery_time',
                            'service.frame',
                            'job_card.service_status',
                            'service.registration');
            
            if(!empty($serach_value))
            {
               $api_data->where(function($query) use ($serach_value){
                        $query->where('tag','like',"%".$serach_value."%")
                        ->orwhere('service.frame','like',"%".$serach_value."%")
                        ->orwhere('service.registration','like',"%".$serach_value."%")
                        ->orwhere('service_in_time','like',"%".$serach_value."%")
                        ->orwhere('service_out_time','like',"%".$serach_value."%")
                        ->orwhere('job_card_type','like',"%".$serach_value."%")
                        ->orwhere('customer_status','like',"%".$serach_value."%")
                        ->orwhere('vehicle_km','like',"%".$serach_value."%")
                        ->orwhere('service_duration','like',"%".$serach_value."%")
                        ->orwhere('estimated_delivery_time','like',"%".$serach_value."%");
                    });
               
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                    'tag',
                    'service.frame',
                    'service.registration',
                    'service_in_time',
                    'service_out_time',
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
                $api_data->orderBy('job_card.in_time','desc');   
        
        $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray(); 
        // foreach ($api_data as &$value) {
        //     if ($value['tag']) {
        //         $user = Users::where('id',Auth::id())->get('role')->first();
        //         $value['role'] = $user->role;
        //     }
        // }     
        //print_r(DB::getQueryLog());die(); 
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

    public function JcdelayComment(Request $request) {
        try {
            if (empty($request->input('reason'))) {
               return 'verror';
            }else{
                $getjc = Feedback::where('jobcard_id',$request->input('jobcard_id'))->get();
                if (count($getjc) > 0) {
                    $comment = Feedback::where('jobcard_id',$request->input('jobcard_id'))->update([
                            'delay_reason' => $request->input('reason')
                        ]); 
                    if ($comment == NULL) {
                        return 'error';
                    }else{
                         return 'success';
                    }
                }else{
                    $comment = Feedback::insertGetId([
                        'jobcard_id' => $request->input('jobcard_id'),
                        'delay_reason' => $request->input('reason')
                    ]); 
                    if ($comment == NULL) {
                        return 'error';
                    }else{
                         return 'success';
                    }
                }
            }
        }  catch(\Illuminate\Database\QueryException $ex) {
            return 'error';
        }
    }

    public function getServiceDiscount($id) {
        $getdiscount = ServiceCharge::where('job_card_id',$id)->where('charge_type','Discount')->get();
         return response()->json($getdiscount);
    }

    public function serviceAdd_discount(Request $request) {
        try {
            if (empty($request->input('amount')) || empty($request->input('sub_type'))) {
               return 'verror';
            }else{
              $discAmt = $request->input('amount');
              $subType = $request->input('sub_type');
              $jobcard_id = $request->input('jobcard_id');
              $charge_type = 'Discount';

              $getjc = ServiceCharge::where('job_card_id',$jobcard_id)->where('charge_type',$charge_type)->where('sub_type',$subType)->get()->first();

                if ($getjc) {
                    return 'find';
                }else{
                  // $getJobCard = Job_card::where('id',$jobcard_id)->get()->first();
                  $getJobCard = Job_card::where('job_card.id',$jobcard_id)->leftJoin('service','service.id','job_card.service_id')->select('job_card.*','service.customer_id')->first();
                  $store_id = $getJobCard['store_id'];
                    $charges = CustomHelpers::ServiceChargeAdd($charge_type, $subType, $discAmt, $jobcard_id,$store_id);
                    if ($charges == null) {
                        return 'error';
                    }else{
                       /* Add action Log */
                        CustomHelpers::userActionLog($action='Add service Discount',$jobcard_id,$getJobCard['customer_id']);
                        return 'success';
                    }
                }
            }
        }  catch(\Illuminate\Database\QueryException $ex) {
            return 'error';
        }
    }

    public function bay_alloc_graph(Request $request)
    {
        $uStoreId = Auth::user()->store_id;
        $uStoreId_arr = explode(',',$uStoreId);
        // $store_id = 7;
        $store_id = $uStoreId_arr[0];
        if($request->input('store_id')){
            $store_id = $request->input('store_id');
        }

        $store = Store::select(['id as store_id','store_type','name as realname',
                                DB::raw('concat(name,"-",store_type) as name')])
                            ->orderBy('store_type','ASC')->whereIn('id',$uStoreId_arr)->get();
        
        $start_end = Bay::Join('users','users.id',DB::raw(Auth::id()))
                    ->whereRaw("find_in_set(bay.store_id,users.store_id)")
                    ->where('bay.store_id',$store_id)
                    ->select(
                        DB::raw('bay.start_time'),
                        DB::raw('max(IF(bay.extended_date = CURRENT_DATE,time(IFNULL(bay.extended_time,bay.end_time)),bay.end_time)) AS max_end_time'),
                        DB::raw('TIMEDIFF(max(IF(bay.extended_date = CURRENT_DATE,time(IFNULL(bay.extended_time,bay.end_time)),bay.end_time)),time(bay.start_time)) as time_diff')
                    )->first();
        if(empty($start_end['start_time']) || empty($start_end['max_end_time']) || empty($start_end['time_diff'])){
            $start_end['start_time'] = '08:30:00';
            $start_end['max_end_time'] = '18:00:00';
            $start_end['time_diff'] = '09:30:00';
        }
        $start = strtotime($start_end['start_time']);
        $end = strtotime($start_end['max_end_time']);
        $total_duration = $start_end['time_diff'];
        // echo $start_end['start_time'].'  '.$start_end['max_end_time'].'  '.$total_duration;die;
        //-----------------------
        $bay = Bay::Join('users','users.id',DB::raw(Auth::id()))
                    ->whereRaw("find_in_set(bay.store_id,users.store_id)")
                    ->where('bay.store_id',$store_id)
                    ->select(
                        DB::raw('bay.id'),
                        DB::raw('bay.name'),
                        DB::raw('bay.type'),
                        DB::raw('bay.user_id'),
                        DB::raw('bay.status'),
                        DB::raw('bay.start_time'),
                        DB::raw('IF(bay.extended_date = CURRENT_DATE,time(IFNULL(bay.extended_time,bay.end_time)),bay.end_time) AS end_time')
                        // DB::raw('max(IF(bay.extended_date = CURRENT_DATE,time(IFNULL(bay.extended_time,bay.end_time)),bay.end_time)) AS max_end_time')
                    )->get()->toArray();
        // print_r($bay->toArray());die;
        
        $duration_in_decimal = CustomHelpers::decimalHours($total_duration);
        // print_r($duration_in_decimal);die;
        $inter = 30;
        $min_duration = CustomHelpers::min_duration();
        // $total_duration = $duration_in_decimal;  
        $duartion_min = $duration_in_decimal*60;  // converting minutes
        $total_td = $duartion_min/$min_duration;
        $interval = '+'.$inter.' minutes';
        $colspan = $inter/$min_duration;
        
        $bay_alloc = DB::select("SELECT
                                auto_assign_bay1.*,
                                CAST(
                                    UNIX_TIMESTAMP(auto_assign_bay1.start_time) AS SIGNED INTEGER
                                ) AS unix_start,
                                CAST(
                                    UNIX_TIMESTAMP(auto_assign_bay1.end_time) AS SIGNED INTEGER
                                ) AS unix_end,
                                job_card.tag as tag
                            FROM
                                auto_assign_bay1
                            Join job_card on auto_assign_bay1.job_card_id = job_card.id
                            WHERE
                            auto_assign_bay1.job_card_id IS NOT NULL
                            and auto_assign_bay1.store_id = ".$store_id."
                            ORDER BY
                            auto_assign_bay1.id,
                            auto_assign_bay1.start_time ASC"
                        );
            // print_r($bay_alloc);die;
        $custom_arr = [];
        foreach($bay as $key => $val){
            $custom_arr[$key]['bay'] = $val;
            $custom_arr[$key]['data'] = [];
            foreach($bay_alloc as $key1 => $val1)
            {
                if($val1->id == $val['id'])
                {
                    $custom_arr[$key]['data'][] = $val1;
                }
            }
        }
        // echo strtotime(date('Y-m-d H:i:s'));
        // echo date('H:i:s',1584781630);
        // print_r($custom_arr);die;
        // $job_card = Job_card::leftJoin('bay_allocation',function($join){
        //                             $join->on('bay_allocation.job_card_id','=','job_card.id')
        //                                     ->where('bay_allocation.fi_status','<>','pending')
        //                                     ->whereNull('bay_allocation.fi_status');
        //                         })
        //                         ->whereNull('bay_allocation.job_card_id')
        //                         // ->where('bay_allocation.status','<>','pending')
        //                         ->select('job_card.id','job_card.tag')
        //                         ->get();
        $job_card = DB::select("SELECT job_card.id,job_card.tag FROM `job_card` left join (select * from bay_allocation WHERE bay_allocation.fi_status <> 'pending' or fi_status is null) b on b.job_card_id = job_card.id WHERE b.job_card_id is null and job_card.tag is not null and job_card.store_id = ".$store_id);

        // print_r($custom_arr);
        $date = new DateTime(date('Y-m-d H:i'));
        $rounded_current_time = CustomHelpers::roundToNearestMinuteInterval($date,$min_duration);
        // print_r($rounded_current_time);
        // print_r($rounded_current_time->format('Y-m-d H:i:s'));die;
        // die;
        $data = [
            'current_time'  =>  strtotime($rounded_current_time->format('Y-m-d H:i')),
            'job_card'  =>  $job_card,
            'store_id'  =>  $store_id,
            'store'    =>  $store,
            'bay'   =>  $bay,
            'bay_alloc' =>  $bay_alloc,
            'custom_arr'    =>  $custom_arr,
            'duartion_min'  =>  $duartion_min,
            'min_duration'  =>  $min_duration,
            'total_td'  =>  $total_td,
            'interval'  =>  $interval,
            'colspan'   =>  $colspan,
            'start' =>  $start,
            'end'   =>  $end,
            'layout' => 'layouts.main'
        ];
        // print_r($data);die;
        return view('admin.service.bay_alloc_graph_normal_table',$data);
    }
    public function bay_alloc_graph_old()
    {
        echo 'Under Construction';die;
        $total_duration = '09:30:00';  // 08:30 to 18:00
        $duration_in_decimal = CustomHelpers::decimalHours($total_duration);

        $inter = 30;
        $min_duration = 10;
        // $total_duration = $duration_in_decimal;  
        $duartion_min = $duration_in_decimal*60;  // 
        $total_td = $duartion_min/$min_duration;
        $interval = '+'.$inter.' minutes';
        $colspan = $inter/$min_duration;
        $start = strtotime('08:30:00');
        $end = strtotime('18:00:00');

        $data = [
            'duartion_min'  =>  $duartion_min,
            'total_td'  =>  $total_td,
            'interval'  =>  $interval,
            'colspan'   =>  $colspan,
            'start' =>  $start,
            'end'   =>  $end,
            'layout' => 'layouts.main'
        ];
        // print_r($data);die;
        return view('admin.service.bay_alloc_graph',$data);
    }

    public function bay_alloc_graph_DB(Request $request)
    {
        // print_r($request->input());die;
        $bay_id = $request->input('bay_id');
        $job_card_id = $request->input('job_card_id');
        $start_time = $request->input('start_time');
        $end_time = $request->input('end_time');

        try{
            $interval = CustomHelpers::min_duration();
            // validation
            $validate = $this->fix_time_validate($bay_id,$start_time,$end_time,$interval);
            if($validate != 'success')
            {
                return response()->json($validate);
            }
            

            $data = [
                'job_card_id'   =>  $job_card_id,
                'bay_id'    =>  $bay_id,
                'start_time'    =>  date('Y-m-d H:i',strtotime('+1 minutes',strtotime($start_time))),
                'end_time'    =>  date('Y-m-d H:i',strtotime($end_time)),
                'date'  =>  date('Y-m-d'),
                'status'    =>  'pending'
            ];
            $insert = BayAllocation::insertGetId($data);
        }
        catch(\Illuminate\Database\QueryException $ex) {
            return response()->json("Something Wen't Wrong. ".$ex);

        }
        return response()->json($insert);
    }
    public function bay_alloc_graph_delete_DB(Request $request)
    {
        $job_card_id = $request->input('job_card_id');
        try{
            $del = BayAllocation::where('job_card_id',$job_card_id)->whereRaw('date','CURRENT_DATE')->delete();
        }
        catch(\Illuminate\Database\QueryException $ex) {
            return response()->json("Something Wen't Wrong. ".$ex);
        }
        return response()->json($del);
    }
    public function bay_alloc_graph_edit_DB(Request $request)
    {
        // print_r($request->input());die;
        $job_card_id = $request->input('job_card_id');
        $new_start_time = $request->input('new_start_time');
        $new_end_time = $request->input('new_end_time');
        try{
            DB::enableQueryLog();
            $get_bay_id = BayAllocation::where('job_card_id',$job_card_id)->select('bay_id','id')->first();
            $bay_id = $get_bay_id->bay_id;
            $bay_alloc_id = $get_bay_id->id;
            // echo $bay_id.' '.$bay_alloc_id;die;
            $new_start_time  = str_replace(' ','',$new_start_time);
            $new_end_time  = str_replace(' ','',$new_end_time);
            $new_start_time = date('Y-m-d H:i',strtotime($new_start_time));
            $new_end_time = date('Y-m-d H:i',strtotime($new_end_time));
            
            $interval = CustomHelpers::min_duration();
            $d1 = new DateTime($new_start_time);
            $s1 = CustomHelpers::roundToNearestMinuteInterval($d1,$interval);
            $new_start_time = date('Y-m-d H:i:s',strtotime('+1 minutes',strtotime($s1->format('Y-m-d H:i'))));

            $d2 = new DateTime($new_end_time);
            $s2 = CustomHelpers::roundToNearestMinuteInterval($d2,$interval);
            $new_end_time = $s2->format('Y-m-d H:i:s');

            // validation
            $validate = $this->fix_time_validate($bay_id,$new_start_time,$new_end_time,$interval);
            if($validate != 'success')
            {
                return response()->json($validate);
            }

            // echo $bay_id.' / '.$bay_alloc_id.' / '.$new_start_time.' / '.$new_end_time;die;
        // DB::enableQueryLog();
            $findtime = DB::select(DB::raw("SELECT * FROM(
                (SELECT bay.id,bay.name,
                    SUM(TIMEDIFF(b.end_time, b.start_time)) AS total_time
                    FROM bay_allocation AS b
                    RIGHT JOIN bay ON (bay.id = b.bay_id and b.id <> ".$bay_alloc_id.")
                    JOIN users ON users.id = ".Auth::id()."
                    WHERE bay.status = 'active' AND IFNULL(DATE(b.date),CURRENT_DATE) = CURRENT_DATE 
                    AND FIND_IN_SET(bay.store_id, users.store_id) 
                    AND bay.status = 'active'
                    and bay.id = ".$bay_id."
                    GROUP BY bay.id
                ) AS t1
                LEFT JOIN(
                    SELECT COUNT(b.id) AS booked,b.bay_id
                    FROM bay_allocation AS b
                    WHERE
                    (
                        (
                            TIMESTAMP('".$new_start_time."') BETWEEN b.start_time AND b.end_time
                        ) OR(
                            TIMESTAMP('".$new_end_time."') BETWEEN b.start_time AND b.end_time
                        )OR(
                            b.start_time BETWEEN TIMESTAMP('".$new_start_time."') AND TIMESTAMP('".$new_end_time."')
                        )OR(
                            b.end_time BETWEEN TIMESTAMP('".$new_start_time."') AND TIMESTAMP('".$new_end_time."')
                        )
                    ) and b.id <> ".$bay_alloc_id."
                    AND IFNULL(DATE(b.date),CURRENT_DATE) = CURRENT_DATE
                    GROUP BY b.bay_id
                ) AS t2
            ON
                t1.id = t2.bay_id 
        )   
        ORDER BY total_time ASC"));
        // print_r(DB::getQueryLog());die;
        // print_r($findtime);die;
            if(!empty($findtime) && $findtime[0]->booked == NULL)
            {
                if($findtime[0])
                {
                    $data = [
                        'start_time'    =>  $new_start_time,
                        'end_time'  =>  $new_end_time
                    ];
                    $update = BayAllocation::where('job_card_id',$job_card_id)->whereNull('fi_status')
                        ->whereRaw('date','CURRENT_DATE')->update($data);
                }
            }
            else{
                $str = 'not available from '.$new_start_time.' to '.$new_end_time;
                return response()->json($str);
            }
        }
        catch(\Illuminate\Database\QueryException $ex) {
            return response()->json("Something Wen't Wrong. ".$ex);
        }
        if($update)
        {
            $update = [$job_card_id,strtotime($new_start_time),strtotime($new_end_time)];
            return response()->json($update);
        }
        else{
            return response()->json("Something wen't wrong.");
        }
    }

    public function fix_time_validate($bay_id,$new_start_time,$new_end_time,$interval)
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
    
    public function re_arrange_bay(Request $request)
    {
        try{
            DB::beginTransaction();
            $bay_id = $request->input('bay_id');
            // DB::enableQueryLog();
            $getData = BayAllocation::where('bay_id',$bay_id)->whereRaw('date = CURRENT_DATE')
                        ->where('start_time','>',DB::raw('CURRENT_TIMESTAMP'))
                        ->where('status','pending')
                        ->select('id','start_time','end_time','date',
                                DB::raw('timediff(end_time,start_time) as duration'))
                        ->orderBy('start_time')->get();
            
            if(isset($getData[0]))
            {
                $get_all_id = [];
                foreach($getData as $k => $v)
                {
                    $get_all_id[] = $v->id;
                }
                // print_r($get_all_id);die;
                $date = new DateTime(date('Y-m-d H:i'));
                $rounded_current_time = CustomHelpers::roundToNearestMinuteInterval($date);
                $start_time = date('Y-m-d H:i',strtotime('+1 minutes',strtotime($rounded_current_time->format('Y-m-d H:i'))));
                DB::enableQueryLog();

                $cal = BayAllocation::where('bay_id',$bay_id)->whereRaw('date = CURRENT_DATE')
                        ->where('start_time','<=',$start_time)
                        ->where('end_time','>=',$start_time)    
                        ->where('status','pending')
                        ->whereNotIn('id',$get_all_id)
                        ->select('id','start_time','end_time','date',
                                DB::raw('timediff(end_time,start_time) as duration'))
                        ->orderBy('start_time')->first();

                // print_r($cal);die;
                if(!empty($cal))
                {
                    $s1 = strtotime($start_time);
                    $s2 = strtotime($cal->end_time);
                    if($s2 > $s1)
                    {
                        $end = new DateTime($cal->end_time);
                        $interval  = CustomHelpers::min_duration();
                        $rounded_current_time = CustomHelpers::roundUpToMinuteInterval($end,$interval);
                        // $end = explode(':',$cal->end_time)[1];
                        // $cal = round($end / $interval) * $interval;
                        // echo $end.' / '.$interval.' / '.$cal;die;
                        $start_time = date('Y-m-d H:i',strtotime('+1 minutes',strtotime($rounded_current_time->format('Y-m-d H:i'))));
                    }
                }

                // print_r($start_time);die;
                
                foreach($getData as $key => $val)
                {
                    $duration = $val->duration;
                    $arr = explode(':',$duration);
                    $end_time = date('Y-m-d H:i',strtotime('+'.$arr[0].' hour +'.$arr[1].' minutes',strtotime($start_time)));
                    // echo '<br>'.$start_time.' / '.$duration.' / '.$end_time;
                    
                    //update query

                    $update = BayAllocation::where('id',$val->id)
                                            ->update(['start_time' => $start_time,
                                                        'end_time'  =>  $end_time        
                                                ]);


                    $start_time = date('Y-m-d H:i',strtotime('+1 minutes',strtotime($end_time)));
                }
            }
            else{
                return back()->with('error','There is no allocation to ReArrange');
            }
        }   
        catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return back()->with('error',"something wen't wrong ".$ex->getMessage());
        }
        DB::commit();
        return back()->with('success',"Successfully Re-Arranged Bay.");

    }


    public function viewJobCard(Request $request, $Id){
       $servicechaege = ServiceCharge::where('job_card_id','=',$Id)->select('service_charge.*')->get();
       $service_chaege_total=ServiceCharge::where('job_card_id','=',$Id)->where('charge_type','<>','Discount')->select('service_charge.*',DB::raw('sum(service_charge.amount) as total_amount'))->get()->first();

       $service_discount_total=ServiceCharge::where('job_card_id','=',$Id)->where('charge_type','Discount')->select('service_charge.*',DB::raw('sum(service_charge.amount) as total_amount'))->get()->first();

       $jobcard_type = DB::table('master')->where('type','job_card_type')->select('key','value')->get();
       $jobCard = job_card::leftJoin('service','job_card.service_id','service.id')
                  ->leftjoin('customer','service.customer_id','customer.id')
                  ->leftjoin('store','job_card.store_id','store.id')
                  ->leftjoin('product','product.id','service.product_id')
                  ->leftjoin('selling_dealer','service.selling_dealer_id','selling_dealer.Id')
                  ->leftJoin('sale',function($join){
                    $join->on('service.sale_date','sale.id');
                  })
                  ->leftJoin('job_card_details',function($join){
                    $join->on('job_card_details.job_card_id','job_card.id');
                  })
                ->where('job_card.id',$Id)
                ->select(
                  'job_card.*',
                  'service.customer_id',
                  'service.frame',
                  'service.registration',
                  DB::raw('concat(store.name,"-",store.store_type) as store_name'),
                  DB::raw('concat(product.model_category,"-",product.model_name,"-",product.model_variant) as product_name'),
                  'selling_dealer.name as selling_dealer_name',
                  'service.manufacturing_year',
                  'service.sale_date',
                  'sale.sale_no',
                  'customer.name as customer_name',
                  'customer.mobile',
                  'job_card_details.insurance',
                  'job_card_details.insurance_date',
                  'job_card_details.insurance_available_type'
                )
                ->get()
                ->first();

       // $part_details = ServicePartRequest::where('job_card_id','=',$Id)->Join('part_details','service_part_request.part_id','part_details.id')->Join('part','part_details.part_id','part.id')->select('service_part_request.part_name','service_part_request.part_id','part_details.part_no','part.price')->get();

        $part_details = ServicePartRequest::leftJoin('part','part.id','service_part_request.part_id')
                    ->select('service_part_request.part_name','part.part_number',DB::raw('sum(part.price*service_part_request.issue_qty) as partamount'),'part.price')
                    ->where('service_part_request.job_card_id',$Id)
                    ->get();

       $part_details_total = ServicePartRequest::leftJoin('part','part.id','service_part_request.part_id')
                    ->select(DB::raw('sum(part.price*service_part_request.issue_qty) as total_amount'))
                    ->where('service_part_request.job_card_id',$Id)
                    ->groupBy('service_part_request.job_card_id')
                    ->get()->first();
       if($jobCard)
       {
        if($jobCard->customer_status != null)
        {
           $data = [
            'jobcard_type' => $jobcard_type,
            'jobCard' => $jobCard,
            'servicharge' => $servicechaege,
            'service_chaege_total'=>$service_chaege_total,
            'part_details_total'=>$part_details_total,
            'service_discount_total' => $service_discount_total,
            'jobCardId' =>  $Id,
            'partdetails' => $part_details,
            'layout' => 'layouts.main'
        ];
          return view('admin.service.view_job_card',$data);
        }
        else
        {
          return redirect('/admin/service/jobcard/list')->with('error','JobCard not created');
        }
      }

      else
        {
          return redirect('/admin/service/jobcard/list')->with('error','JobCard not created');
        }
       
    }

    public function serviceDiscount_list() {
        $jobCard =  Job_card::whereNotNull('tag')->get();
        $data = [
            'jobCard'=>$jobCard,
            'layout' => 'layouts.main'
        ];
        
        return view('admin.service.discount_list',$data);
    }

    public function serviceDiscountList_api(Request $request) {
        $tag = $request->input('tag');
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
        $serviceTag = Job_card::where('tag','<>',null)->get('tag')->toArray();
        $api_data = ServiceCharge::leftJoin('job_card','service_charge.job_card_id','job_card.id')
            ->leftjoin('service','service.id','job_card.service_id')
            ->where('service_charge.charge_type','Discount')
            ->where('service_charge.charge_type','<>','Charge')
            ->whereIn('job_card.tag',$serviceTag)
            ->select('service_charge.id',
            'job_card.tag',
            'service.frame',
            'service.registration',
            'service_charge.charge_type',
            'service_charge.sub_type',
            'service_charge.status',
            'service_charge.amount');
            
           
            if(!empty($serach_value)) {
               $api_data->where(function($query) use ($serach_value){
                        $query->where('job_card.tag','like',"%".$serach_value."%")
                        ->orwhere('service.frame','like',"%".$serach_value."%")
                        ->orwhere('service.registration','like',"%".$serach_value."%")
                        ->orwhere('service_charge.charge_type','like',"%".$serach_value."%")
                        ->orwhere('service_charge.sub_type','like',"%".$serach_value."%")
                        ->orwhere('service_charge.amount','like',"%".$serach_value."%");
                    });
               
            }
            if(!empty($tag)) {
               $api_data->where(function($query) use ($tag){
                        $query->where('job_card.tag','like',"%".$tag."%");
                    });
            }

            if(isset($request->input('order')[0]['column'])) {
                $data = [
                    'job_card.tag',
                    'service.frame',
                    'service.registration',
                    'service_charge.charge_type',
                    'service_charge.sub_type',
                    'service_charge.amount',
                    'job_card.service_status'
                ];
                
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('service_charge.id','desc');   
        
       $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
       // print_r(DB::getQueryLog());die();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array); 
    }

    public function serviceDiscount_approve(Request $request) {
      try{
         $id = $request->input('id');
         $service = ServiceCharge::where('service_charge.id',$id)->leftjoin('job_card','job_card.id','service_charge.job_card_id')->leftJoin('service','service.id','job_card.service_id')->select('job_card.*','service.customer_id')->first();
         if ($service['status'] == 'Done') {
           return array('type' => 'error','msg' => 'Job card already done.');
         }
         if ($id) {
           $update = ServiceCharge::where('id',$id)->update([
              'status' => 'approved'
           ]);
           if ($update) {
                /* Add action Log */
                CustomHelpers::userActionLog($action='Service Discount Approve',$service['id'],$service['customer_id']);
              return array('type' => 'success','msg' => 'Discount approved successfully.');
           }else{
            return array('type' => 'error','msg' => 'Something went wrong.');
           }
         }
       }  catch(\Illuminate\Database\QueryException $ex) {
            return array('type' => 'error','msg' => 'Something went wrong.');
        }
    }
    //store supervisor assign part's
    public function PartRequstApproval_list()
    {
        $data = [
            'layout' => 'layouts.main'
        ];
        
        return view('admin.service.partrequstapprovalList',$data);
    }

    public function PartRequstApproval_list_api(Request $request){

        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
       
        $api_data = ServicePartRequest::Join('job_card','job_card.id','service_part_request.job_card_id')
            ->where('service_part_request.part_id','<>',0)
            ->where('service_part_request.approved',0)
            ->where('service_part_request.deleted_at',null)
            // ->whereNotNull('service_part_request.call_status')
            ->select('job_card.id',
                    'job_card.job_card_type',
                    'job_card.service_duration',
                    'job_card.estimated_delivery_time',
                     DB::raw("date(job_card.created_at) as date"),
                    'job_card.tag')
             ->groupBy('job_card.id');

            if(!empty($serach_value))
            {
               $api_data->where(function($query) use ($serach_value){
                        $query->where('job_card.job_card_type','like',"%".$serach_value."%")
                        ->orwhere('job_card.service_duration','like',"%".$serach_value."%")
                        ->orwhere('job_card.estimated_delivery_time','like',"%".$serach_value."%")
                        ->orwhere('job_card.tag','like',"%".$serach_value."%");
                    });
               
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                    'job_card.tag',
                    'job_card.job_card_type',
                    'job_card.service_duration',
                    'job_card.estimated_delivery_time',
                    'job_card.created_at'
                ];
                
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);

            }
            else
                $api_data->orderBy('job_card.id','desc');   
        
        $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();        
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

     public function PartRequstApproval_list_data($job_card_id) {

        $get_service_id = ServicePartRequest::where('job_card_id',$job_card_id)->select('id')->first();
        if(!$get_service_id) {
            return redirect('/admin/service/partrequest/approval/list/')->with('error','Job Card Id not Exists');
        }

        $customer = Job_card::leftjoin('service','job_card.service_id','service.id')
                ->leftjoin('customer','customer.id','service.customer_id')
                ->where('job_card.id',$job_card_id)
                ->select('customer.name','customer.mobile')
                ->first();

        $getpart = ServicePartRequest::where('job_card_id',$job_card_id)
                    ->leftjoin('part','part.id','service_part_request.part_id')
                    ->where('service_part_request.part_id','<>',0)
                    ->where('service_part_request.deleted_at',null)
                    ->where('service_part_request.approved',0)
                    ->select('service_part_request.*','part.price','part.part_number')
                    ->get();

         $partdata = ServicePartRequest::where('job_card_id',$job_card_id)
         ->leftjoin('part','part.id','service_part_request.part_id')
        ->where('service_part_request.part_id','<>',0)
        ->where('service_part_request.approved',1)
        ->where('service_part_request.deleted_at',null)
        ->where('service_part_request.confirmation','<>','customer')
        ->select('service_part_request.*','part.price','part.part_number')
        ->get();
        $data = [
            'partdata' => $partdata,
            'customer'  =>  $customer,
            'job_card_id' =>  $job_card_id,
            'getpart' => $getpart,
            'layout' => 'layouts.main'
        ];
        
        return view('admin.service.partrequstapprovalListData',$data);
    }

    public function PartRequstApproval_DB(Request $request) {
        $partRequestId = $request->input('part_request_id');
        $getcheck = ServicePartRequest::where('id',$partRequestId)->first();
        $service = Job_card::where('job_card.id',$getcheck['job_card_id'])->leftJoin('service','service.id','job_card.service_id')->select('service.customer_id')->first();
        if($getcheck)
        {
            $servicedata = ServicePartRequest::where('id',$partRequestId)->update([
                'confirmation' => 'manager',
                'approved' => 1,
                'approved_by' =>Auth::id(),
            ]);
            if($servicedata)
            {
                 /* Add action Log */
                  CustomHelpers::userActionLog($action='Part Approve',$getcheck['job_card_id'],$service['customer_id']);
                return 'success';
            }
        }
        else{
            return 'Id not Exists';
        }
        return 'error';
    }

    public function serviceCharge_detail($id) {
      $jobcard = job_card::where('id',$id)->select('job_card_type','status','job_card_type','sub_type','id','store_id','tag',DB::raw('IFNULL(ROUND(TIMESTAMPDIFF( DAY, service_done_date, now() ) % 30.4375),0) as day')
          )->first();
      $getRate = [];
      $AmcDisc = [];
      $data = CustomHelpers::GetJCTotalAmount($id,$jobcard['job_card_type']);
        $totalAmt = $data['total_amount'];
        $JpHonda_discount = $data['JpHonda_discount'];
        $paid_amount = $data['paid_amount'];
        $garaj_amount = $data['garaj_amount'];

      $masterEng = Master::where('type','engine_oil_part')->where('key','Engine Oil')->get()->first();
      $engin = explode(',', $masterEng['value']);

      $partData = ServicePartRequest::leftJoin('part','part.id','service_part_request.part_id')
              ->select('part.part_number','service_part_request.part_name','part.price','service_part_request.issue_qty')
              ->where('service_part_request.job_card_id',$id)
              ->where('service_part_request.deleted_at',NULL)
              ->where('service_part_request.approved','1')
              ->where('service_part_request.part_issue','yes')
              ->where('service_part_request.warranty_approve',0)
              ->where('service_part_request.tampered',null)
              ->where('service_part_request.confirmation','<>','insurance');

      $part = $partData->whereNotIn('part.part_number',$engin)->get();

      $EnginePart =  $partData->whereIn('part.part_number',$engin)->get();

      $service_charge = ServiceCharge::where('deleted_at',null)->where('charge_type','Charge')->where('sub_type','Service')->where('job_card_id',$id)->get();

      $labourcharge = ServiceCharge::where('deleted_at',null)->where('charge_type','Labour')->where('job_card_id',$id)->get();

      $charge = ServiceCharge::where('deleted_at',null)->where('charge_type','Charge')->whereNotIn('sub_type',['Pickup','Service'])->where('job_card_id',$id)->get();

      $pickup_charge = ServiceCharge::where('deleted_at',null)->where('charge_type','Charge')->where('sub_type','Pickup')->where('job_card_id',$id)->get();

      $getDiscount = ServiceCharge::where('charge_type','Discount')->where('status','approved')->where('deleted_at',null)->where('job_card_id',$id);


      if ($jobcard['job_card_type'] == 'AMC') {
         $AmcDisc = CustomHelpers::getAMCDiscount();
        $discount = $getDiscount->where('sub_type','<>','JpHonda')->sum('amount');
      }else if ($jobcard['job_card_type'] == 'HJC') {
        $getRate = CustomHelpers::HJCDiscountRate($id);
        $discount = $getDiscount->where('sub_type','Other')->sum('amount');
      }else{
        $discount = $getDiscount->where('sub_type','<>','JpHonda')->sum('amount');
      }
              

       $data = array(
        'job_card_type' => $jobcard['job_card_type'],
        'jobcard' => $jobcard, 
        'charges' => $charge,
        'service_charge' => $service_charge,
        'labourcharge' => $labourcharge,
        'pickup_charge' => $pickup_charge,
        'paid_amount' => $paid_amount,
        'discount' => $discount,
        'EnginePart' => $EnginePart,
        'partcharges' => $part, 
        'garaj_amount' => $garaj_amount,
        'getRate' => $getRate,
        'AmcDisc' => $AmcDisc,
         'layout' => 'layouts.main'
       );
       return view('admin.service.charge_detail',$data);
    }

    public function PartIssue_list() {
        $data = [
            'layout' => 'layouts.main'
        ];
        return view('admin.service.part_issue_list',$data);
    }

    public function PartIssueList_api(Request $request) {
       $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
        DB::enableQueryLog();
        $api_data = Job_card::join('service_part_request','service_part_request.job_card_id','job_card.id')
        ->where('service_part_request.deleted_at',NULL)
        // ->where('service_part_request.part_id','<>','0')
        ->where('service_part_request.approved','<>','0')
        ->whereNotNull('service_part_request.job_card_id')
        ->select('job_card.id',
                'job_card.tag',
                'job_card.job_card_type',
                'job_card.service_status',
                'service_part_request.warranty_approve',
                DB::raw('count(service_part_request.job_card_id) as no_of_part'),
                'job_card.service_in_time', 
                'job_card.service_out_time')
        ->groupBy('job_card.id');
            
            if(!empty($serach_value))
            {
               $api_data->where(function($query) use ($serach_value){
                        $query->where('job_card.tag','like',"%".$serach_value."%")
                        ->orwhere('job_card.job_card_type','like',"%".$serach_value."%")
                        ->orwhere('service_part_request.id','like',"%".$serach_value."%")
                        ->orwhere('job_card.service_status','like',"%".$serach_value."%")
                        ->orwhere('job_card.service_out_time','like',"%".$serach_value."%");
                    });
               
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                    'tag',
                    'job_card_type',
                    // 'customer_confirmation',
                    DB::raw('count(service_part_request.id)'),
                    'service_in_time',
                    'service_out_time'
                ];
                
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('service_part_request.created_at','desc');   
        
        $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }


     public function Part_issue($id) {
       $tag = job_card::leftjoin('service','service.id','job_card.service_id')
                        ->leftJoin('product','service.product_id','product.id')
                  ->where('job_card.id',$id)->select('job_card.*','service.manufacturing_year','product.model_name','product.model_variant','product.color_code','service.frame')->first();

         if(!$tag){

            return redirect('/admin/service/part/issue/list')->with('error','ID not Exists.');
         }
        $bayInfo = ServicePartRequest::leftjoin('job_card','service_part_request.job_card_id','job_card.id')
        ->leftjoin('part','part.id','service_part_request.part_id')
        ->where('service_part_request.part_issue','<>','yes')
        ->where('service_part_request.status','<>','Issue')
        ->where('service_part_request.approved','1')
        ->where('service_part_request.deleted_at',NULL)
        ->where('service_part_request.part_id','<>','0')
            ->select(
                'part.part_number',
                'service_part_request.part_name',
                'service_part_request.qty',
                'service_part_request.status',
                'service_part_request.issue_qty',
                'service_part_request.assign_qty',
                'service_part_request.id',
                'part.price'
            )
            ->where('service_part_request.job_card_id',$id)
            ->orderBy('service_part_request.created_at','asc')->get();

        $partdetails = ServicePartRequest::leftjoin('part','part.id','service_part_request.part_id')
            ->where('service_part_request.deleted_at',NULL)
            ->where('service_part_request.part_issue','yes')
            ->where('service_part_request.status','Issue')
            ->where('service_part_request.approved','1')
            ->where('service_part_request.job_card_id',$id)
            ->select(
                'part.part_number',
                'service_part_request.part_name',
                'service_part_request.qty',
                'service_part_request.status',
                'service_part_request.issue_qty',
                'service_part_request.id',
                'part.price'
            )
            ->orderBy('service_part_request.created_at','asc')->get();

        $data = [
            'jobcardId' => $id,
            'partdetails' => $partdetails,
            'partInfo'  =>  $bayInfo,
            'tag' => $tag,
            'layout' => 'layouts.main'
        ];
        
        return view('admin.service.part_issue',$data);
    }


    public function PartStoreGetPartInfo(Request $request) {
        $part_no  = $request->input('part_no');
        $store_id  = $request->input('store_id');
        $qty  = $request->input('qty');
        $getdata = Parts::where('part_number',''.$part_no.'')->get()->first();
        if ($getdata) {
           $part_id = $getdata['id'];

           $check_part_qty = PartStock::where('part_id',$part_id)->where('store_id',$store_id)->where('quantity','>=',$qty)->get()->first();
                  
             if(empty($check_part_qty)) {
               $other_qty = PartStock::leftjoin('store','part_stock.store_id','store.id')
               ->where('part_stock.part_id',$part_id)->where('part_stock.quantity','>=',$qty)->select('store.store_type','store.name')->first();
                if (!empty($other_qty)) {
                   $partdata = Parts::leftjoin('part_stock','part.id','part_stock.part_id')
                          ->leftJoin('store','part_stock.store_id','store.id')
                          ->leftJoin('part_cell','part_stock.cell_id','part_cell.id')
                          ->leftjoin('part_row','part_row.id','part_cell.row_id')
                          ->leftjoin('part_rack','part_rack.id','part_row.rack_id')
                          ->where('part_stock.part_id',$part_id)
                          ->select('part.price','part.name','part_cell.cell_name','part_row.row_name','part_rack.rack_name',DB::raw('concat(store.name,"-",store.store_type) as store_name'))->first();
                   return array('type' => 'availablity_other_location', 'data' => $partdata);
                }else{
                  return  array('type' => 'not_available');
                }
              }else{
                 $partdata = Parts::leftjoin('part_stock','part.id','part_stock.part_id')
                          ->leftJoin('part_cell','part_stock.cell_id','part_cell.id')
                          ->leftjoin('part_row','part_row.id','part_cell.row_id')
                          ->leftjoin('part_rack','part_rack.id','part_row.rack_id')
                          ->where('part_stock.part_id',$part_id)
                          ->where('part_stock.store_id',$store_id)
                          ->select('part.price','part.name','part_cell.cell_name','part_row.row_name','part_rack.rack_name')->first();
                return response()->json($partdata);
              }
        }else{
           return  array('type' => 'not_available');
        }
        
    }

    public function PartIssue_DB(Request $request) {
        // try{
        //     DB::beginTransaction();
        //     $count = $request->input('part_issue');
        //     $job_card_id = $request->input('jobcard_id');

        //     $getJc = job_card::where('id',$job_card_id)->get()->first();
      
        //     for($i = 0 ; $i < $count ; $i++) {
        //         $part_no  = $request->input('part_number'.$i);
        //         $part_price  = $request->input('price'.$i);
        //         $part_qty = $request->input('part_qty'.$i);
        //         $request_id  = $request->input('part_request_id'.$i);
        //         $getdata = Parts::where('part_number',$part_no)->get()->first();

        //         $checkEngine = Master::where('type','engine_oil_part')->where('key','Engine Oil')->whereRaw(DB::raw('FIND_IN_SET("'.$part_no.'",value)'))->get()->first();

        //         if ($checkEngine) {
        //             $getDiscount = ServiceCharge::where('job_card_id',$job_card_id)->where('charge_type','Discount')->where('sub_type','Engine Oil')->where('promo_code','<>',NULL)->get()->first();
        //             if ($getDiscount) {
        //                $discAmt =  floor($part_price*$part_qty*$getDiscount['discount_rate']/100);
        //                $addCharge = ServiceCharge::where('id',$getDiscount['id'])->where('job_card_id',$job_card_id)->update(['amount' => $discAmt]);
        //                if ($addCharge == NULL) {
        //                  DB::rollback();
        //                  return back()->with('error','Something went wrong !');
        //                }
        //             }
        //         }
        //         if($getdata) {
        //           $part_id =  $getdata['id'];
        //           $store_id = $getJc['store_id'];

        //           $check_part_qty = PartStock::where('part_id',$part_id)->where('store_id',$store_id)->where('quantity','>=',$part_qty)->first();
                  
        //            if(empty($check_part_qty)) {

        //                $Po = PurchaseOrderRequest::insertGetId([
        //                 'model_name' => $part_no,
        //                 'store_id' => $store_id
        //                ]);
        //                if ($Po == null) {
        //                  DB::rollback();
        //                 return back()->with('error','Something went wrong');
        //                }

        //                $update = ServicePartRequest::where('id',$request_id)->update([
        //                 'part_id' => $part_id,
        //                 'assign_qty' => 0,
        //                 'issue_qty' => 0,
        //                 'status' => 'Issue',
        //                 'part_issue' => 'yes',
        //                 'part_availability' => $request->input('part_availability'.$i)
        //                 ]);

        //             }else{
        //               if ($request->input('part_availability'.$i) == 'Linked Part Not available' || $request->input('part_availability'.$i) == 'Item Available-Linked Part Not Available') {
        //                 $pid = $getdata->id;
        //                 $issue_qty = 0;
        //                 $available = $request->input('part_availability');
        //               }else{
        //                 $updatePartStock = PartStock::where('part_id',$part_id)->where('store_id',$store_id);
        //                 $inc = $updatePartStock->increment('sale_qty',$part_qty);
        //                 $dec = $updatePartStock->decrement('quantity',$part_qty);
        //                 $pid = $getdata->id;
        //                 $issue_qty = $part_qty;
        //                 $available = NULL;

        //               }
        //                $update = ServicePartRequest::where('id',$request_id)->update([
        //                 'part_id' => $pid,
        //                 'assign_qty' => $issue_qty,
        //                 'issue_qty' => $issue_qty,
        //                 'status' => 'Issue',
        //                 'part_issue' => 'yes',
        //                 'part_availability' => 'Available'
        //                 ]);
        //             }
                   
        //             if ($update) {
        //                  DB::commit();
        //                  return redirect('/admin/service/part/issue/'.$job_card_id)->with('success','Part Issue Successfully .');
        //               }else{
        //                 DB::rollback();
        //                 return back()->with('error','Part not issue !');
        //               }
        //         }else{
        //             DB::rollback();
        //             return back()->with('error','Part not available !');
        //         }
        //     }
          
        // }catch(\Illuminate\Database\QueryException $ex) {
        //   DB::rollback();
        //     return redirect('/admin/service/part/issue/'.$job_card_id)->with('error','some error occurred'.$ex->getMessage());
        // }
      
       try{ 
          $validator = Validator::make($request->all(),[
            'part_issue'    =>  'required',
            
        ],
        [
            'part_issue.required'  =>  'This Field is required',
            
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
            $count = count($request->input('part_issue'));
            $job_card_id = $request->input('jobcard_id');
            $qty_error = '';
            // $getJc = job_card::where('id',$job_card_id)->get()->first();
            $getJc = Job_card::where('job_card.id',$job_card_id)->leftJoin('service','service.id','job_card.service_id')->select('job_card.*','service.customer_id')->first();
            for($i = 0 ; $i < $count ; $i++) {

              $partRequest = ServicePartRequest::leftjoin('part','part.id','service_part_request.part_id')
                        ->where('service_part_request.id',$request->input('part_issue')[$i])->select('service_part_request.*','part.part_number')->get()->first();

                       $update = ServicePartRequest::where('id',$request->input('part_issue')[$i])->where('assign_qty','<>',0)->where('approved','1')->update([
                        'issue_qty' => $partRequest['assign_qty'],
                        'status' => 'Issue',
                        'part_issue' => 'yes'
                        ]);
                       if ($partRequest['assign_qty'] == 0) {
                         $qty_error = $qty_error.$partRequest['part_number'];
                       }
                    }
                    if ($qty_error && $update) {
                      /* Add action Log */
                       CustomHelpers::userActionLog($action = 'Part Issue',$job_card_id,$getJc['customer_id']);
                       return redirect('/admin/service/part/issue/'.$job_card_id)->with('success','Part Issue Successfully, But some '.$qty_error.' part number not issue because quantity not available.');
                    }else if ($qty_error && $update == null) {
                      /* Add action Log */
                       CustomHelpers::userActionLog($action = 'Part Issue',$job_card_id,$getJc['customer_id']);
                       return redirect('/admin/service/part/issue/'.$job_card_id)->with('error',$qty_error.' part number not issue because quantity not available.');
                    }
                    else{
                      /* Add action Log */
                       CustomHelpers::userActionLog($action = 'Part Issue',$job_card_id,$getJc['customer_id']);
                      return redirect('/admin/service/part/issue/'.$job_card_id)->with('success','Part Issue Successfully .');
                    }
        }catch(\Illuminate\Database\QueryException $ex) {
            return redirect('/admin/service/part/issue/'.$job_card_id)->with('error','some error occurred'.$ex->getMessage());
        }
    }

    public function serviceDone_list() {
            $data = [
            'layout' => 'layouts.main'
        ];
        return view('admin.service.service_done',$data);
    }

    public function serviceDoneList_api(Request $request) {
      $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit = empty($limit) ? 10 : $limit ;

            $api_data = Job_card::Join('service','service.id','job_card.service_id')
                ->leftJoin('payment',function($join){
                $join->on(DB::raw('payment.type = "service" and payment.type_id'),'=','job_card.id')
                ->where('payment.status','received');
                 })
                ->leftjoin('service_part_request','service_part_request.job_card_id','job_card.id')
                ->leftjoin('part','part.id','service_part_request.part_id')
                ->leftjoin('service_charge','job_card.id','service_charge.job_card_id')
                ->where('job_card.service_status','done')
                ->where('job_card.fi_status','ok')
                ->where('job_card.payment_status','done')
                ->orwhere('job_card.pay_letter',1)
                ->where('service_charge.deleted_at',null)
                ->where('service_part_request.deleted_at',null)
                ->where('service_charge.charge_type','<>','Discount')
                ->where('service_part_request.approved','1')
                ->where('service_part_request.warranty_approve',0)
                ->where('service_part_request.confirmation','<>','insurance')
                ->whereNotNull('job_card.invoice_no')
                ->select('job_card.id','job_card.tag','service.frame',
                    'service.registration',
                    'job_card.job_card_type',
                    'job_card.customer_status',
                    'job_card.vehicle_km',
                    'job_card.wash',
                    'job_card.sub_type',
                    'job_card.service_duration',
                    'job_card.service_status',
                    'job_card.status',
                    'job_card.pay_letter',
                    'job_card.invoice_no',
                    'job_card.hirise_amount',
                    'job_card.total_amount',
                    'job_card.payment_status',
                    DB::raw('IFNULL(sum(payment.amount),0) as paid_amount'),
                    'job_card.service_in_time',
                    'job_card.fi_status',
                    'job_card.service_out_time')
                    ->groupBy('job_card.id');
            
            DB::enableQueryLog();
            if(!empty($serach_value))
            {
               $api_data->where(function($query) use ($serach_value){
                        $query->where('job_card.tag','like',"%".$serach_value."%")
                        ->orwhere('service.frame','like',"%".$serach_value."%")
                        ->orwhere('service.registration','like',"%".$serach_value."%")
                        ->orwhere('job_card.fi_status','like',"%".$serach_value."%")
                        ->orwhere('job_card.wash','like',"%".$serach_value."%")
                        ->orwhere('job_card.job_card_type','like',"%".$serach_value."%")
                        ->orwhere('job_card.customer_status','like',"%".$serach_value."%")
                        ->orwhere('job_card.vehicle_km','like',"%".$serach_value."%")
                        ->orwhere('job_card.estimated_delivery_time','like',"%".$serach_value."%")
                        ->orwhere('job_card.service_in_time','like',"%".$serach_value."%")
                        ->orwhere('job_card.service_out_time','like',"%".$serach_value."%");
                    });
               
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                    'tag',
                    'frame',
                    'registration',
                    'job_card_type',
                    'customer_status',
                    'vehicle_km',
                    'wash',
                    'service_status',
                    'estimated_delivery_time',
                    'service_in_time',
                    'invoice_no',
                    'hirise_amount',
                    'pay_letter',
                    'paid_amount',
                    'total_amount',
                    'fi_status',
                    'payment_status',
                    'service_out_time'
                ];
                
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('job_card.id','desc');   
        
        $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();


        // add amount 
           foreach ($api_data as &$value) {
            $id = $value['id'];
            $job_card_type = $value['job_card_type'];
            $data = CustomHelpers::GetJCTotalAmount($id,$job_card_type);
            $value['total_amount'] = $data['total_amount'];
            $value['JpHonda_discount'] = $data['JpHonda_discount'];
            $value['garage_charge'] = $data['garaj_amount']; 
            $value['paid_amount'] = $data['paid_amount'];
            
        }
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array); 
    }

    public function service_done(Request $request) {
       try {
           $jobCardId = $request->input('jobcard_id');
           $service = Job_card::where('job_card.id',$jobCardId)->leftJoin('service','service.id','job_card.service_id')->select('service.customer_id')->first();
            if (empty($jobCardId)) {
                return 'verror';
            }else{
                $update = Job_card::where('id',$jobCardId)->update([
                  'status' => 'Done',
                  // 'jobcard_done_date' => date('Y-m-d')
                  ]);
           
                if ($update == NULL) {
                    return 'error';
                }else{
                  /* Add action Log */
                  CustomHelpers::userActionLog($action='Service Done',$jobCardId,$service['customer_id']);
                    return 'success';
                }
            }
        }  catch(\Illuminate\Database\QueryException $ex) {
            return 'error';
        }
     
    }

    public function warranty_list() {
        $data = [
            'layout' => 'layouts.main'
        ];
        return view('admin.service.warranty_list',$data);
    }

     public function warrantyList_api(Request $request) {
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;

           DB::enableQueryLog();
            $api_data = Job_card::join('service','service.id','job_card.service_id')
                            ->whereIn('job_card.job_card_type',['General','Free'])
                            ->where('service.customer_id','<>','0')
                            ->select('job_card.id','job_card.tag','service.frame as frame',
                                        'service.registration as registration',
                                        'job_card.job_card_type',
                                        'job_card.customer_status',
                                        'job_card.vehicle_km',
                                        'job_card.service_duration',
                                        'job_card.estimated_delivery_time',
                                        'job_card.service_status',
                                        // 'job_card.customer_confirmation',
                                        'job_card.service_in_time',
                                        'job_card.service_out_time');
            
           
            if(!empty($serach_value))
            {
               $api_data->where(function($query) use ($serach_value){
                        $query->where('job_card.tag','like',"%".$serach_value."%")
                        ->orwhere('job_card.frame','like',"%".$serach_value."%")
                        ->orwhere('job_card.registration','like',"%".$serach_value."%")
                        ->orwhere('job_card.job_card_type','like',"%".$serach_value."%")
                        ->orwhere('job_card.customer_status','like',"%".$serach_value."%")
                        ->orwhere('job_card.service_status','like',"%".$serach_value."%")
                        ->orwhere('job_card.estimated_delivery_time','like',"%".$serach_value."%");
                    });
               
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                    'id',
                    'tag',
                    'frame',
                    'registration',
                    'job_card_type',
                    'customer_status',
                    'estimated_delivery_time',
                    'service_status',
                ];
                
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('job_card.id','desc');   
        
        $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

    public function warranty_part($id) {
       $partdata = ServicePartRequest::leftjoin('job_card','job_card.id','service_part_request.job_card_id')
       ->leftjoin('part','service_part_request.part_id','part.id')
       ->where('service_part_request.deleted_at',null)
       ->where('service_part_request.job_card_id',$id)
       ->select('job_card.*','part.part_number','part.price','service_part_request.part_name','service_part_request.qty','service_part_request.tampered','service_part_request.warranty_type','service_part_request.id as id','job_card.service_id')
       ->get();
      $data = [
        'partdata' => $partdata,
        'layout' => 'layouts.main'
        ];
       return view('admin.service.warranty_part',$data);
    }

    public function warrantyPart_update(Request $request) {
       try {
           $service = ServicePartRequest::leftjoin('job_card','job_card.id','service_part_request.job_card_id')
                    ->leftjoin('service','service.id','job_card.service_id')
                    ->where('service_part_request.id',$request->input('id'))
                    ->select('service.customer_id')->first();

            if (empty($request->input('radioValue'))) {
               return 'verror';
            }else if (!empty($request->input('radioValue')) && $request->input('radioValue') == 'no' && empty($request->input('warranty_type'))) {
               return 'verror';
            }
            else{
              DB::beginTransaction();
                if ($request->input('radioValue') == 'no' && $request->input('warranty_type') == 'Standard') {
                    $getdate = ServicePartRequest::leftjoin('job_card','job_card.id','service_part_request.job_card_id')
                    ->leftjoin('service','service.id','job_card.service_id')
                    ->leftjoin('product','service.product_id','product.id')
                    ->where('service_part_request.id',$request->input('id'))
                     ->select('job_card.service_id','product.st_warranty_duration','service.sale_date','service_part_request.*', DB::raw('TIMESTAMPDIFF( YEAR, service.sale_date, now() ) as year'))
                    ->get()
                    ->first();
                    if ($getdate['year'] > $getdate['st_warranty_duration']) {
                       return 'date';
                    }else{
                        $update = ServicePartRequest::where('id',$request->input('id'))->update([
                              'tampered' => $request->input('radioValue'),
                              'warranty_type' => $request->input('warranty_type')
                          ]); 
                      if ($update == NULL) {
                        DB::rollback();
                          return 'error';
                      }else{
                        DB::commit();
                        /* Add action Log */
                        CustomHelpers::userActionLog($action='Add Part Warranty',$request->input('id'),$service['customer_id']);
                        return 'success';
                      }
                            
                      }
                }else if ($request->input('radioValue') == 'no' && $request->input('warranty_type') == 'Extended'){
                    $getdate = ServicePartRequest::leftjoin('job_card','job_card.id','service_part_request.job_card_id')
                    ->leftjoin('service','service.id','job_card.service_id')
                    ->where('service_part_request.id',$request->input('id'))
                    ->select('job_card.service_id','service.sale_date','service_part_request.*'
                    )->get()->first();

                    $ewdate = ExtendedWarranty::where('service_id',$getdate['service_id'])->select(DB::raw('TIMESTAMPDIFF( YEAR, now(),end_date) as year'))->get()->first();
                    if ($ewdate['year'] < $request->input('year')) {
                       return 'date';
                    }else{
                        $update = ServicePartRequest::where('id',$request->input('id'))->update([
                              'tampered' => $request->input('radioValue'),
                              'warranty_type' => $request->input('warranty_type')
                          ]); 
                      if ($update == NULL) {
                        DB::rollback();
                          return 'error';
                      }else{
                         DB::commit();
                         /* Add action Log */
                        CustomHelpers::userActionLog($action='Add Part Warranty',$request->input('id'),$service['customer_id']);
                         return 'success';
                      }
                   }
                }else{
                    $update = ServicePartRequest::where('id',$request->input('id'))->update([
                            'tampered' => $request->input('radioValue'),
                            'warranty_type' => $request->input('warranty_type')
                        ]); 
                    if ($update == NULL) {
                      DB::rollback();
                        return 'error';
                    }else{
                      DB::commit();
                      /* Add action Log */
                        CustomHelpers::userActionLog($action='Add Part Warranty',$request->input('id'),$service['customer_id']);
                         return 'success';
                    }
                }
            }
        }  catch(\Illuminate\Database\QueryException $ex) {
          DB::rollback();
            return 'error';
        }
    }

    public function serviceHistory_list() {
      $data = [
        'layout' => 'layouts.main'
        ];
       return view('admin.service.service_history_list',$data);
    }

     public function serviceHistoryList_api(Request $request) {
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
        // DB::enableQueryLog();
        $api_data = Job_card::leftjoin('service_part_request','service_part_request.job_card_id','job_card.id')
            ->whereNotNull('service_part_request.job_card_id')
            ->where('job_card.job_card_type','<>','Accident')
            ->where('service_part_request.tampered','<>',null)
            ->where('service_part_request.tampered','no')
            ->select('job_card.id',
                    'job_card.tag',
                    'service_part_request.warranty_approve',
                    DB::raw('count(service_part_request.job_card_id) as no_of_part'),
                    'job_card.service_in_time', 
                    'job_card.job_card_type', 
                    'job_card.service_history', 
                    'job_card.service_out_time')
            ->groupBy('job_card.id');
            
           
            if(!empty($serach_value))
            {
               $api_data->where(function($query) use ($serach_value){
                        $query->where('job_card.tag','like',"%".$serach_value."%")
                        // ->orwhere('job_card.customer_confirmation','like',"%".$serach_value."%")
                        ->orwhere('service_part_request.id','like',"%".$serach_value."%")
                        ->orwhere('job_card.service_in_time','like',"%".$serach_value."%")
                        ->orwhere('job_card.service_out_time','like',"%".$serach_value."%");
                    });
               
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                    'tag',
                    // 'customer_confirmation',
                    DB::raw('count(service_part_request.id)'),
                    'service_in_time',
                    'job_card_type',
                    'service_out_time',
                    'service_history'
                ];
                
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('job_card.id','desc');   
        
        $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        //print_r(DB::getQueryLog());die;
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

    public function serviceHistory_update(Request $request) {
       try {
          $service = Job_card::where('job_card.id',$request->input('id'))->leftJoin('service','service.id','job_card.service_id')->select('service.customer_id')->first();

            if (empty($request->input('history'))) {
               return 'verror';
            }else{
              if ($request->input('history') == 'notok') {
                $update = Job_card::where('id',$request->input('id'))->update([
                        'service_history' => $request->input('history'),
                    ]);

                if ($update == NULL) {
                    return 'error';
                }else{
                    /* Add action Log */
                    CustomHelpers::userActionLog($action='Add Service History',$request->input('id'),$service['customer_id']);
                     return 'success';
                }
              }else{
               $update = Job_card::where('id',$request->input('id'))->update([
                        'service_history' => $request->input('history'),
                    ]); 
                if ($update == NULL) {
                    return 'error';
                }else{
                    /* Add action Log */
                     CustomHelpers::userActionLog($action='Add Service History',$request->input('id'),$service['customer_id']);
                     return 'success';
                }              
             }
          }
        }  catch(\Illuminate\Database\QueryException $ex) {
            return 'error';
        }
    }


    public function warrantyApprove_list() {
        $data = [
            'layout' => 'layouts.main'
        ];
        return view('admin.service.warranty_approve_list',$data);
    }

     public function warrantyListApprove_api(Request $request) {
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;

           DB::enableQueryLog();
            $api_data = ServicePartRequest::leftjoin('job_card','job_card.id','service_part_request.job_card_id')
                        ->leftjoin('part','part.id','service_part_request.part_id')
                        ->leftjoin('service','service.id','job_card.service_id')
                        ->whereIn('job_card.job_card_type',['General','Free'])
                        ->where('service.customer_id','<>','0')
                        ->where('job_card.service_history','ok')
                        ->where('service_part_request.tampered','no')
                        // ->where('service_part_request.tampered','<>',null)
                        ->where('service_part_request.deleted_at',null)
                        ->select(
                              'service_part_request.id',
                              'service_part_request.part_name',
                              'service_part_request.qty',
                              'service_part_request.tampered',
                              'service_part_request.warranty_type',
                              'service_part_request.warranty_approve',
                              'part.part_number',
                              'part.price',
                              'job_card.tag',
                              'service.frame as frame',
                              'service.registration as registration',
                              'job_card.job_card_type',
                              'job_card.customer_status',
                              'job_card.vehicle_km',
                              'job_card.service_duration',
                              'job_card.estimated_delivery_time',
                              'job_card.service_status',
                              'job_card.service_in_time',
                              'job_card.service_out_time');
            
           
            if(!empty($serach_value))
            {
               $api_data->where(function($query) use ($serach_value){
                        $query->where('job_card.tag','like',"%".$serach_value."%")
                        ->orwhere('service_part_request.qty','like',"%".$serach_value."%")
                        ->orwhere('service_part_request.warranty_type','like',"%".$serach_value."%")
                        ->orwhere('service_part_request.tampered','like',"%".$serach_value."%")
                        ->orwhere('service_part_request.warranty_approve','like',"%".$serach_value."%")
                        ->orwhere('part.price','like',"%".$serach_value."%")
                        ->orwhere('part.part_number','like',"%".$serach_value."%")
                        ->orwhere('service_part_request.part_name','like',"%".$serach_value."%")
                        ->orwhere('job_card.job_card_type','like',"%".$serach_value."%");
                    });
               
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                    
                    'job_card.tag',
                    'job_card.job_card_type',
                    'service_part_request.part_name',
                    'part.part_number',
                    'service_part_request.qty',
                    'part.price',
                    'service_part_request.tampered',
                    'service_part_request.warranty_type',
                    'service_part_request.warranty_approve'
                ];
                
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('job_card.id','desc');   
        
        $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

    public function warrantyPart_approve(Request $request) {
       try {
             $service = ServicePartRequest::leftjoin('job_card','job_card.id','service_part_request.job_card_id')
                    ->leftjoin('service','service.id','job_card.service_id')
                    ->where('service_part_request.id',$request->input('id'))
                    ->select('service.customer_id')->first();

            if (!empty($request->input('id'))) {
                    $update = ServicePartRequest::where('id',$request->input('id'))->update([
                            'warranty_approve' => '1'
                        ]); 
                    if ($update == NULL) {
                        return 'error';
                    }else{
                         $insert = PartWarranty::insertGetId([
                          'part_request_id' => $request->input('id')
                         ]);
                         if ($insert == NULL) {
                            return 'error';
                         }else{
                          /* Add action Log */
                          CustomHelpers::userActionLog($action='Part Warranty Approve',$request->input('id'),$service['customer_id']);
                          return 'success';
                        }
                    }
              
            }
        }  catch(\Illuminate\Database\QueryException $ex) {
            return 'error';
        }
    }

    public function UpdatePart_tag($id) {
        $partdata = ServicePartRequest::leftjoin('job_card','job_card.id','service_part_request.job_card_id')
       ->leftjoin('part','service_part_request.part_id','part.id')
       ->leftjoin('part_warranty','service_part_request.id','part_warranty.part_request_id')
       ->where('service_part_request.deleted_at',null)
       ->where('service_part_request.warranty_approve',1)
       ->where('service_part_request.job_card_id',$id)
       ->where('service_part_request.tampered','no')
       ->where('job_card.service_history','ok')
       ->select('job_card.*','part_warranty.tag as part_tag','part.part_number','part.price','service_part_request.part_name','service_part_request.qty','service_part_request.tampered','service_part_request.warranty_type','service_part_request.id as id')
       ->get();
      $data = [
        'partdata' => $partdata,
        'layout' => 'layouts.main'
        ];
       return view('admin.service.request_part_tag',$data);
    }

    public function getPart_tag(Request $request) {
      $data = PartWarranty::where('part_request_id',$request->input('id'))->get()->first();
       return response()->json($data);
    }

    public function UpdatePartTag_DB(Request $request) {
      try {
            if (empty($request->input('tag'))) {
              return 'verror';
            }else{
                    $update = PartWarranty::where('part_request_id',$request->input('id'))->update([
                            'tag' => $request->input('tag')
                        ]); 
                    if ($update == NULL) {
                        return 'error';
                    }else{
                         return 'success';
                    }
              
            }
        }  catch(\Illuminate\Database\QueryException $ex) {
            return 'error';
        }
    }

      public function warrantyHtr_list() {
        $data = [
            'layout' => 'layouts.main'
        ];
        return view('admin.service.warranty_htr_list',$data);
    }

     public function warrantyHtrList_api(Request $request) {
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;

           DB::enableQueryLog();
            $api_data = ServicePartRequest::leftjoin('job_card','job_card.id','service_part_request.job_card_id')
                        ->leftjoin('part','part.id','service_part_request.part_id')
                        ->leftjoin('service','service.id','job_card.service_id')
                        ->leftjoin('part_warranty','service_part_request.id','part_warranty.part_request_id')
                        ->whereIn('job_card.job_card_type',['General','Free'])
                        ->where('service.customer_id','<>','0')
                        ->where('job_card.service_history','ok')
                        ->where('service_part_request.tampered','no')
                        ->where('service_part_request.tampered','<>',null)
                        ->where('service_part_request.warranty_approve',1)
                         ->where('service_part_request.deleted_at',null)
                        ->select(
                              'service_part_request.id',
                              'service_part_request.part_name',
                              'service_part_request.qty',
                              'service_part_request.tampered',
                              'service_part_request.warranty_type',
                              'service_part_request.warranty_approve',
                              'part.part_number',
                              'part.price',
                              'job_card.tag',
                              'service.frame as frame',
                              'service.registration as registration',
                              'job_card.job_card_type',
                              'job_card.service_status',
                              'part_warranty.htr_number',
                              'part_warranty.invoice_id',
                              'part_warranty.approved',
                              'part_warranty.rejection',
                              'job_card.service_out_time');
            
           
            if(!empty($serach_value))
            {
               $api_data->where(function($query) use ($serach_value){
                        $query->where('job_card.tag','like',"%".$serach_value."%")
                        ->orwhere('service_part_request.qty','like',"%".$serach_value."%")
                        ->orwhere('service_part_request.warranty_type','like',"%".$serach_value."%")
                        ->orwhere('service_part_request.tampered','like',"%".$serach_value."%")
                        ->orwhere('service_part_request.warranty_approve','like',"%".$serach_value."%")
                        ->orwhere('part.price','like',"%".$serach_value."%")
                        ->orwhere('part_warranty.htr_number','like',"%".$serach_value."%")
                        ->orwhere('part.part_number','like',"%".$serach_value."%")
                        ->orwhere('service_part_request.part_name','like',"%".$serach_value."%")
                        ->orwhere('job_card.job_card_type','like',"%".$serach_value."%");
                    });
               
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                    
                    'job_card.tag',
                    'job_card.job_card_type',
                    'service_part_request.part_name',
                    'part.part_number',
                    'service_part_request.qty',
                    'part.price',
                    'service_part_request.tampered',
                    'service_part_request.warranty_type',
                    'part_warranty.htr_number',
                    'service_part_request.warranty_approve',
                    'part_warranty.approved',
                    'part_warranty.rejection',
                    
                ];
                
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('job_card.id','desc');   
        
        $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

    public function getPart_htr(Request $request) {
      $data = PartWarranty::where('part_request_id',$request->input('id'))->get()->first();
       return response()->json($data);
    }

    public function UpdatePartHtr_DB(Request $request) {
      try {
          $service = ServicePartRequest::leftjoin('job_card','job_card.id','service_part_request.job_card_id')
                    ->leftjoin('service','service.id','job_card.service_id')
                    ->where('service_part_request.id',$request->input('id'))
                    ->select('service.customer_id')->first();

            if (empty($request->input('htr'))) {
              return 'verror';
            }else{
                 $id = $request->input('id');
                 $htr = $request->input('htr');

               $data = PartWarranty::where('part_request_id',$id)->update([
                        'htr_number' => $htr                     
                      ]);

                if ($data == NULL) {
                    return 'error';
                }else{
                  /* Add action Log */
                  CustomHelpers::userActionLog($action='Update HTR Number',$request->input('id'),$service['customer_id']);
                     return 'success';
                }
              
            }
        }  catch(\Illuminate\Database\QueryException $ex) {
            return 'error';
        }
    }

      public function warrantyInvoice_create() {
         $invoicedata = PartWarranty::leftjoin('service_part_request','service_part_request.id','part_warranty.part_request_id')
                        ->leftjoin('job_card','job_card.id','service_part_request.job_card_id')
                        ->leftjoin('part','part.id','service_part_request.part_id')
                        ->leftjoin('service','service.id','job_card.service_id')
                        ->where('service_part_request.tampered','no')
                        ->where('service_part_request.tampered','<>',null)
                        ->where('service_part_request.warranty_approve',1)
                         ->where('service_part_request.deleted_at',null)
                         ->where('part_warranty.invoice_id',null)
                        ->select(
                              'part_warranty.*',
                              'service_part_request.part_name',
                              'service_part_request.qty',
                              'service_part_request.tampered',
                              'service_part_request.warranty_type',
                              'service_part_request.warranty_approve',
                              'part.part_number',
                              'part.price',
                              'job_card.tag as jctag',
                              'service.frame as frame',
                              'service.registration as registration',
                              'job_card.job_card_type',
                              'job_card.customer_status',
                              'job_card.vehicle_km',
                              'job_card.service_duration',
                              'job_card.estimated_delivery_time',
                              'job_card.service_status',
                              'job_card.service_in_time',
                              'part_warranty.htr_number',
                              'job_card.service_out_time')
                      ->get();
            
        $data = [
            'invoicedata' => $invoicedata,
            'layout' => 'layouts.main'
        ];
        return view('admin.service.warranty_invoice_create',$data);
    }

    public function warrantyInvoiceCreate_DB(Request $request){
        try{
          if (empty($request->input('warranty_part_id'))) {
            return  back()->with('error','Please check atleast one part.')->withInput();
          }else{
            $invoice = WarrantyInvoice::insertGetId([
            ]);
            if ($invoice == NULL) {
              DB::rollback();
              return  back()->with('error','Something went wrong.')->withInput();
            }else{
              $count = count($request->input('warranty_part_id'));
              for ($i = 0; $i < $count; $i++) { 
                 $update = PartWarranty::where('id',$request->input('warranty_part_id')[$i])->update([
                    'invoice_id' => $invoice
                 ]);
                 if ($update == NULL) {
                   DB::rollback();
                   return  back()->with('error','Something went wrong.')->withInput();
                 }
              }
                DB::commit();
                /* Add action Log */
                  CustomHelpers::userActionLog($action='Create Part Warranty Invoice',0,0);
                return redirect('/admin/service/warranty/invoice/create')->with('success','Invoice created successfully');

            }
          }

       }catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return redirect('/admin/service/warranty/invoice/create')->with('error','some error occurred'.$ex->getMessage());
        }
    }

    public function warrantyInvoice_list() {
        $data = [
            'layout' => 'layouts.main'
        ];
        return view('admin.service.warranty_invoice_list',$data);
    }

     public function warrantyInvoiceList_api(Request $request) {
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;

           DB::enableQueryLog();
            $api_data = WarrantyInvoice::where('id','<>',null)
                        ->select(
                          'warranty_invoice.id',
                          'warranty_invoice.invoice_number',
                          'warranty_invoice.courier_number',
                          'warranty_invoice.courier_amount',
                          'warranty_invoice.part_receive',
                          'warranty_invoice.invoice_receive',
                          'warranty_invoice.dispatch'
                        );
            
           
            if(!empty($serach_value))
            {
               $api_data->where(function($query) use ($serach_value){
                        $query->where('warranty_invoice.invoice_number','like',"%".$serach_value."%")
                        ->orwhere('warranty_invoice.courier_number','like',"%".$serach_value."%")
                        ->orwhere('warranty_invoice.courier_amount','like',"%".$serach_value."%")
                        ->orwhere('warranty_invoice.part_receive','like',"%".$serach_value."%")
                        ->orwhere('warranty_invoice.invoice_receive','like',"%".$serach_value."%")
                        ->orwhere('warranty_invoice.dispatch','like',"%".$serach_value."%");
                    });
               
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                    'warranty_invoice.invoice_number',
                    'warranty_invoice.courier_number',
                    'warranty_invoice.courier_amount',
                    'warranty_invoice.part_receive',
                    'warranty_invoice.invoice_receive',
                    'warranty_invoice.dispatch'
                ];
                
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('warranty_invoice.id','desc');   
        
        $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

    // public function warrantyInvoice_get(Request $request) {
    //   $data = WarrantyInvoice::where('id',$request->input('id'))->get()->first();
    //    return response()->json($data);
    // }

    public function warrantyInvoice_update($id) {
      $invoicedata = WarrantyInvoice::where('id',$id)->get()->first();
      $data = array(
        'invoicedata' => $invoicedata,
         'layout' => 'layouts.main'
        );
      return view('admin.service.update_warranty_invoice',$data);
    }

    public function warrantyInvoiceUpdate_DB(Request $request) {
      
      try {

         $this->validate($request,[
                 'invoice_number' =>'required',
                 'courier_number' =>'required',
                 'courier_amount' =>'required',
                 'part_receive'  =>  'required',
                 'invoice_receive'  =>  'required',
                 'dispatch'  =>  'required',
            ],[
                'invoice_number.required'  =>  'This Field is required',
                'courier_number.required'  =>  'This Field is required',
                'courier_amount.required'  =>  'This Field is required',
                'part_receive.required'  =>  'This Field is required',
                'invoice_receive.required'  =>  'This Field is required',
                'dispatch.required'  =>  'This Field is required',
            ]);

              $id = $request->input('id');
              $data = WarrantyInvoice::where('id',$id)->update([
                        'invoice_number' => $request->input('invoice_number'),                   
                        'courier_number' => $request->input('courier_number'),                   
                        'courier_amount' => $request->input('courier_amount'),                  
                        'part_receive' => $request->input('part_receive'),                  
                        'invoice_receive' => $request->input('invoice_receive'),                  
                        'dispatch' => $request->input('dispatch')                 
                      ]);
                if ($data == NULL) {
                    return redirect('/admin/service/warranty/invoice/update/'.$id.'')->with('error','Something went wrong !');
                }else{
                    /* Add action Log */
                    CustomHelpers::userActionLog($action='Update Part Warranty Invoice',$id,0);
                    return redirect('/admin/service/warranty/invoice/update/'.$id.'')->with('success','Invoice details updated successfully');
                }
             
         }catch(\Illuminate\Database\QueryException $ex) {
            return redirect('/admin/service/warranty/invoice/update/'.$id.'')->with('error','some error occurred'.$ex->getMessage());
        }
    }

    public function warranty_approve(Request $request) {
      try {
            if (!empty($request->input('id'))) {
                $update = PartWarranty::where('part_request_id',$request->input('id'))->update([
                         'approved' => 'yes'
                        ]); 
                    if ($update == NULL) {
                        return 'error';
                     }else{
                      return 'success';
                    }
            }
        }  catch(\Illuminate\Database\QueryException $ex) {
            return 'error';
        }
    }

    public function warranty_reject(Request $request) {
      try {
            if (!empty($request->input('id'))) {
                $update = PartWarranty::where('part_request_id',$request->input('id'))->update([
                         'rejection' => 'yes'
                        ]); 
                    if ($update == NULL) {
                        return 'error';
                     }else{
                      return 'success';
                    }
            }
        }  catch(\Illuminate\Database\QueryException $ex) {
            return 'error';
        }
    }


     public function PreBooking_list() {
        $data = [
            'layout' => 'layouts.main'
        ];
        return view('admin.service.pre_booking_list',$data);
    }

     public function PreBookingList_api(Request $request) {
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;

           DB::enableQueryLog();
            $api_data = ServiceBooking::where('id','<>',null)
                        ->select(
                          'service_booking.id',
                          'service_booking.name',
                          'service_booking.mobile',
                          'service_booking.status'
                        );
            
           
            if(!empty($serach_value))
            {
               $api_data->where(function($query) use ($serach_value){
                        $query->where('service_booking.id','like',"%".$serach_value."%")
                        ->orwhere('service_booking.name','like',"%".$serach_value."%")
                        ->orwhere('service_booking.mobile','like',"%".$serach_value."%")
                        ->orwhere('service_booking.status','like',"%".$serach_value."%");
                    });
               
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                    'service_booking.id',
                    'service_booking.name',
                    'service_booking.mobile',
                    'service_booking.status'
                ];
                
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('service_booking.id','desc');   
        
        $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

     public function servicePayLetter_list() {
        $data = [
            'layout' => 'layouts.main'
        ];
        
        return view('admin.service.pay_letter_list',$data);
    }

    public function servicePayLetterList_api(Request $request) {
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit = empty($limit) ? 10 : $limit ;

            $api_data = Job_card::Join('service','service.id','job_card.service_id')
                ->leftJoin('payment',function($join){
                $join->on(DB::raw('payment.type = "service" and payment.type_id'),'=','job_card.id')
                ->where('payment.status','received');
                 })
                ->leftjoin('service_part_request','service_part_request.job_card_id','job_card.id')
                ->leftjoin('part','part.id','service_part_request.part_id')
                ->leftjoin('service_charge','job_card.id','service_charge.job_card_id')
                ->where('job_card.service_status','done')
                ->where('job_card.fi_status','ok')
                ->where('service_charge.deleted_at',null)
                ->where('service_part_request.deleted_at',null)
                ->where('service_charge.charge_type','<>','Discount')
                ->where('service_part_request.approved','1')
                ->where('service_part_request.confirmation','<>','insurance')
                ->whereNotNull('job_card.invoice_no')
                ->select('job_card.id','job_card.tag','service.frame',
                    'service.registration',
                    'job_card.job_card_type',
                    'job_card.customer_status',
                    'job_card.vehicle_km',
                    'job_card.wash',
                    'job_card.service_duration',
                    'job_card.service_status',
                    'job_card.sub_type',
                    'job_card.invoice_no',
                    'job_card.hirise_amount',
                    'job_card.pay_letter',
                    'job_card.total_amount',
                    DB::raw('IFNULL(ROUND(TIMESTAMPDIFF( DAY, job_card.service_done_date, now() ) % 30.4375),0) as day'),
                    DB::raw('IFNULL(sum(payment.amount),0) as paid_amount'),
                    'job_card.service_in_time',
                    'job_card.fi_status',
                    'job_card.service_out_time')
                    ->groupBy('job_card.id');
            
            DB::enableQueryLog();
            if(!empty($serach_value))
            {
               $api_data->where(function($query) use ($serach_value){
                        $query->where('job_card.tag','like',"%".$serach_value."%")
                        ->orwhere('service.frame','like',"%".$serach_value."%")
                        ->orwhere('service.registration','like',"%".$serach_value."%")
                        ->orwhere('job_card.fi_status','like',"%".$serach_value."%")
                        ->orwhere('job_card.wash','like',"%".$serach_value."%")
                        ->orwhere('job_card.job_card_type','like',"%".$serach_value."%")
                        ->orwhere('job_card.customer_status','like',"%".$serach_value."%")
                        ->orwhere('job_card.vehicle_km','like',"%".$serach_value."%")
                        ->orwhere('job_card.estimated_delivery_time','like',"%".$serach_value."%")
                        ->orwhere('job_card.service_in_time','like',"%".$serach_value."%")
                        ->orwhere('job_card.service_out_time','like',"%".$serach_value."%");
                    });
               
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                    'tag',
                    'frame',
                    'registration',
                    'job_card_type',
                    'customer_status',
                    'vehicle_km',
                    'wash',
                    'service_status',
                    'estimated_delivery_time',
                    'service_in_time',
                    'invoice_no',
                    'hirise_amount',
                    'pay_letter',
                    'paid_amount',
                    'total_amount',
                    'fi_status',
                    'service_out_time'
                ];
                
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('job_card.id','desc');   
        
        $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();


        // add amount 
        foreach ($api_data as &$value) {
           $id = $value['id'];
             $value['paid_amount'] = Payment::where('type','service')->where('type_id',$id)->sum('amount');
             $garaj_charge = Settings::where('name', 'garage_charge')->get()->first();
             $garaj_charge_limit = Settings::where('name', 'garage_charges_after')->get()->first();
             $value['garaj_charge'] = $garaj_charge['value'];
             $value['garage_charges_after'] = $garaj_charge_limit['value'];

           if ($value['job_card_type'] == 'AMC') {
                   
                    $disc = CustomHelpers::getDiscount();

                    $part = job_card::leftjoin('service_part_request','job_card.id','service_part_request.job_card_id')
                    ->leftJoin('part','part.id','service_part_request.part_id')
                    ->select(DB::raw('sum(part.price*service_part_request.qty) as partamount'))
                    ->where('service_part_request.job_card_id',$id)
                    ->where('service_part_request.deleted_at',NULL)
                    ->where('service_part_request.approved','1')
                    ->where('service_part_request.part_issue','yes')
                    ->where('service_part_request.warranty_approve',0)
                    ->where('service_part_request.confirmation','<>','insurance')
                    ->groupBy('service_part_request.job_card_id')
                    ->get()->first();
                    $partPrice = $part->partamount-$part->partamount/$disc['part'];

                    $charge = ServiceCharge::where('deleted_at',null)->where('charge_type','Charge')->where('job_card_id',$id)->sum('amount');

                    $labourcharge = ServiceCharge::where('deleted_at',null)->where('charge_type','Labour')->where('job_card_id',$id)->sum('amount');
                    $labour = $labourcharge-$labourcharge/$disc['labour'];

                    $discount = ServiceCharge::where('charge_type','Discount')->where('status','approved')->where('deleted_at',null)->where('job_card_id',$id)->sum('amount');

                     $value['total_amount'] = $value['total_amount']+$labour+$partPrice+$charge-$discount;
                }else{
                  $part = job_card::leftjoin('service_part_request','job_card.id','service_part_request.job_card_id')
                  ->leftJoin('part','part.id','service_part_request.part_id')
                  ->select(DB::raw('sum(part.price*service_part_request.qty) as partamount'))
                  ->where('service_part_request.job_card_id',$id)
                  ->where('service_part_request.deleted_at',NULL)
                  ->where('service_part_request.approved','1')
                  ->where('service_part_request.warranty_approve',0)
                  ->where('service_part_request.part_issue','yes')
                  ->where('service_part_request.confirmation','<>','insurance')
                  ->groupBy('service_part_request.job_card_id')
                  ->get()->first();
                  $partPrice = $part['partamount'];

                  $charge = ServiceCharge::where('deleted_at',null)->whereIn('charge_type',['Charge','Labour'])->where('job_card_id',$id)->sum('amount');

                  $discount = ServiceCharge::where('charge_type','Discount')->where('status','approved')->where('deleted_at',null)->where('job_card_id',$id)->sum('amount');


                 $value['total_amount'] = $value['total_amount']+$partPrice+$charge-$discount;
                }
              }

        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array); 
    }

    public function servicePayLetter_allow(Request $request) {
       try {
           $jobCardId = $request->input('jobcard_id');

            if (empty($jobCardId)) {
                return array('msg' => 'Something went wrong !', 'type' => 'error', 'data' => NULL);
            }else{
                 $service = Job_card::where('job_card.id',$jobCardId)->leftJoin('service','service.id','job_card.service_id')->select('job_card.*','service.customer_id')->first();
                 if ($service['status'] == 'Done') {
                    return array('msg' => 'Jobcard is already done !', 'type' => 'error', 'data' => NULL);
                 }

                $update = Job_card::where('id',$jobCardId)->update([
                  'pay_letter' => 1,
                  ]);
           
                if ($update == NULL) {
                    return array('msg' => 'Something went wrong !', 'type' => 'error', 'data' => NULL);
                }else{
                   /* Add action Log */
                  CustomHelpers::userActionLog($action='Service Pay Letter Allowed',$jobCardId,$service['customer_id']);
                    return array('msg' => 'Pay letter allowed successfully !', 'type' => 'success', 'data' => NULL);
                }
            }
        }  catch(\Illuminate\Database\QueryException $ex) {
             return array('msg' => 'Something went wrong !', 'type' => 'error', 'data' => NULL);
        }
    }


    public function JpHondaDiscount_list() {
        $data = [
            'layout' => 'layouts.main'
        ];
        
        return view('admin.service.jphonda_discount',$data);
    }

    public function JpHondaDiscountList_api(Request $request) {
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
           
            $api_data = Job_card::Join('service','service.id','job_card.service_id')
                   ->leftJoin('service_charge',function($join){
                    $join->on('service_charge.job_card_id','=','job_card.id')->where('service_charge.charge_type','Discount')
                      ->where('service_charge.sub_type','JPHonda');
                     })
                    ->where('job_card.service_status','done')
                    ->where('job_card.invoice_no','<>',null)
                    ->where('job_card.fi_status','ok')
                    ->select('job_card.id','job_card.tag',
                                'service.frame',
                                'service.registration',
                                'job_card.job_card_type',
                                'job_card.customer_status',
                                'job_card.vehicle_km',
                                'job_card.wash',
                                'job_card.service_duration',
                                'service_charge.amount as discount_amount',
                                'job_card.invoice_no',
                                'job_card.hirise_amount',
                                'job_card.service_in_time',
                                'job_card.service_status',
                                'job_card.fi_status',
                                'job_card.service_out_time');
            
            DB::enableQueryLog();
            if(!empty($serach_value))
            {
               $api_data->where(function($query) use ($serach_value){
                        $query->where('job_card.tag','like',"%".$serach_value."%")
                        ->orwhere('service.frame','like',"%".$serach_value."%")
                        ->orwhere('service.registration','like',"%".$serach_value."%")
                        ->orwhere('job_card.fi_status','like',"%".$serach_value."%")
                        ->orwhere('job_card.wash','like',"%".$serach_value."%")
                        ->orwhere('job_card.job_card_type','like',"%".$serach_value."%")
                        ->orwhere('job_card.customer_status','like',"%".$serach_value."%")
                        ->orwhere('job_card.vehicle_km','like',"%".$serach_value."%")
                        ->orwhere('job_card.estimated_delivery_time','like',"%".$serach_value."%")
                        ->orwhere('job_card.service_in_time','like',"%".$serach_value."%")
                        ->orwhere('job_card.service_out_time','like',"%".$serach_value."%");
                    });
               
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                    'tag',
                    'frame',
                    'registration',
                    'job_card_type',
                    'customer_status',
                    'vehicle_km',
                    'wash',
                    'service_status',
                    'estimated_delivery_time',
                    'service_in_time',
                    'invoice_no',
                    'hirise_amount',
                    'fi_status',
                    'service_out_time'
                ];
                
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('job_card.id','desc');   
        
        $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array); 
    }

    public function JpHonda_discount(Request $request) {
     try {
           $jobCardId = $request->input('jobcard_id');
           $service = Job_card::where('job_card.id',$jobCardId)->leftJoin('service','service.id','job_card.service_id')->select('job_card.status','service.customer_id')->first();
           if ($service['status'] == 'Done') {
              return array('msg' => 'Job card is already done !', 'type' => 'error', 'data' => NULL);
           }

            if (empty($jobCardId) || empty($request->input('amount'))) {
                return array('msg' => 'Field is require !', 'type' => 'error', 'data' => NULL);
            }else{
                $checkCharge = ServiceCharge::where('job_card_id',$jobCardId)->where('charge_type','Discount')->where('sub_type','JpHonda')->get()->first();
                if ($checkCharge) {
                  return array('msg' => 'Discount already added !', 'type' => 'error', 'data' => NULL);
                }else{
                $insert = ServiceCharge::insertGetId([
                  'job_card_id' => $jobCardId,
                  'charge_type' => 'Discount',
                  'sub_type' => 'JpHonda',
                  'amount' => $request->input('amount')
                  ]);
                 if ($insert == NULL) {
                    return array('msg' => 'Something went wrong !', 'type' => 'error', 'data' => NULL);
                }else{
                  /* Add action Log */
                  CustomHelpers::userActionLog($action='JpHonda Discount',$jobCardId,$service['customer_id']);
                    return array('msg' => 'Discount added successfully !', 'type' => 'success', 'data' => NULL);
                }
              } 
            }
        }  catch(\Illuminate\Database\QueryException $ex) {
             return array('msg' => 'Something went wrong !', 'type' => 'error', 'data' => NULL);
        }
    }

  
    
}