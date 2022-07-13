<?php
namespace App\Http\Controllers\front;
use App\Http\Controllers\Controller;
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

    public function Booking() {
        $jobcard_type = Master::where('type','job_card_type')
                        ->whereIn('key',array('Free','Paid'))
                        ->select('key','value')->get();
        $store = Store::select('id',DB::raw('concat(store.name,"-",store.store_type) as name'))->get();

        $data = [
            'store' => $store,
            'jobcard_type'  =>  $jobcard_type,
           'layout'=>'front.layout'
        ];
        
        return view('front.service_booking',$data);
    }

    public function Booking_DB(Request $request) {
         $validator = Validator::make($request->all(),[
           'name'=>'required',
           'mobile'=>'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10|max:10',
           'pick_drop'    =>  'required',
           'pickup_drop'     => 'required_if:pick_drop,yes'
        ],
        [
            'name.required'=> 'This is required.', 
            'mobile.required'=> 'This is required.', 
            'pick_drop.required'=> 'This is required.',
            'pickup_drop.*'    =>  'This is required',
            'mobile.regex'=> 'Mobile No contains digits only.',
            'mobile.max'=> 'Mobile No may not be greater than 10 digits.',
            'mobile.min'=> 'Mobile No must be at least 10 digits.',          
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        try{
          if (!empty($request->input('mobile'))) {
                  $validator = Validator::make($request->all(),[
                    'mobile'=>'required|regex:/^([6-9][0-9]{9}[\s\-\+\(\)]*)$/|min:10|max:10',
                    ],
                    [
                            'mobile.required'=> 'This field is required.',
                            'mobile.regex'=> 'Mobile No is invalid.',
                            'mobile.max'=> 'Mobile No may not be greater than 10 digits.',
                            'mobile.min'=> 'Mobile No must at least 10 digits.',
                    ]);

                    if ($validator->fails()) {
                        return back()->withErrors($validator)->withInput();
                    }
                }


           $name = $request->input('name');
           $mobile = $request->input('mobile');
           $getdata = ServiceBooking::where('name',$name)->where('mobile',$mobile)->where('status','Pending')->first();
           if ($getdata) {
              return back()->with('error','This request is already added.')->withInput();
           }
           $picupdrop = $request->input('pick_drop');
           $pickup_drop  = $request->input('pickup_drop');
           if ($picupdrop == 'no') {
               $pickndrop = '0';
           }else{
            if ($pickup_drop[0] == 'pickup') {
                 $pickndrop = '1';
            }
            if ($pickup_drop[0] == 'drop') {
                 $pickndrop = '2';
            }
             if (!empty($pickup_drop[1]) && !empty($pickup_drop[0])) {
                 $pickndrop = '3';
            }
           }  
              $booking = ServiceBooking::insertGetId([
                  'name' => $name,
                  'mobile' => $mobile,
                  'status'  => 'Pending',
                  'call_type' => 'Website',
                  'pickup_drop' => $pickndrop
              ]);

             if(empty($booking)) {
                 return back()->with('error','Some Error Occurred.')->withInput();
             }else{
              return redirect('/service/prebooking')->with('success','Successfully Created.');
             }
           
        }catch(\Illuminate\Database\QueryException $ex) {
            return redirect('/service/prebooking')->with('error','some error occurred'.$ex->getMessage());
        }
    }

    public function ServiceStatus() {
       $data = [
           'layout'=>'front.layout'
        ];
        
        return view('front.service_status',$data);
    }

    public function CheckServiceStatus(Request $request) {
      $frame = $request->input('frame');
      $tag = $request->input('tag');
      $service = '';
      $registration = $request->input('registration');
      if ($frame && empty($registration)) {
        $service = ServiceModel::where('frame',$frame)->first();
      }
      if ($registration && empty($frame)) {
        $service = ServiceModel::where('registration',$registration)->first();
      }
      if ($frame && $registration) {
        $service = ServiceModel::where('frame',$frame)->where('registration',$registration)->first();
      }

      if ($service != '' && empty($tag)) {
         $data = Job_card::where('job_card.service_id',$service->id)
                          ->leftJoin('service','job_card.service_id','service.id')
                          ->leftJoin('customer','customer.id','service.customer_id')
                          ->leftJoin('product','product.id','service.product_id')
                          ->leftJoin('store','store.id','job_card.store_id')
                          ->where('job_card.status','<>','Closed')
                          ->select(
                            'service.frame',
                            'service.registration',
                            'service.sale_date',
                            'customer.name as customer_name',
                            DB::raw('concat(store.name,"-",store.store_type) as store_name'),
                            DB::raw('concat(product.model_category,"-",product.model_name,"-",product.model_variant) as product_name'),
                            'job_card.tag',
                            'job_card.customer_status',
                            'job_card.job_card_type',
                            'job_card.vehicle_km',
                            'job_card.service_duration',
                            'job_card.estimated_delivery_time',
                            'job_card.service_in_time',
                            'job_card.service_out_time',
                            'job_card.service_status',
                            'job_card.wash',
                            'job_card.fi_status',  
                            'job_card.invoice_no',  
                            'job_card.hirise_amount',  
                            'job_card.payment_status',  
                            'job_card.pay_letter', 
                            'job_card.status', 
                            'job_card.oilinfornt_customer' 
                          )->get();
          return response()->json($data); 
      }elseif ($service && !empty($tag)) {
         $data = Job_card::where('job_card.service_id',$service->id)
                          ->leftJoin('service','job_card.service_id','service.id')
                          ->leftJoin('customer','customer.id','service.customer_id')
                          ->leftJoin('product','product.id','service.product_id')
                          ->leftJoin('store','store.id','job_card.store_id')
                          ->where('job_card.status','<>','Closed')
                          ->where('job_card.tag',$tag)
                          ->select(
                            'service.frame',
                            'service.registration',
                            'service.sale_date',
                            'customer.name as customer_name',
                            DB::raw('concat(store.name,"-",store.store_type) as store_name'),
                            DB::raw('concat(product.model_category,"-",product.model_name,"-",product.model_variant) as product_name'),
                            'job_card.tag',
                            'job_card.customer_status',
                            'job_card.job_card_type',
                            'job_card.vehicle_km',
                            'job_card.service_duration',
                            'job_card.estimated_delivery_time',
                            'job_card.service_in_time',
                            'job_card.service_out_time',
                            'job_card.service_status',
                            'job_card.wash',
                            'job_card.fi_status',  
                            'job_card.invoice_no',  
                            'job_card.hirise_amount',  
                            'job_card.payment_status',  
                            'job_card.pay_letter', 
                            'job_card.status', 
                            'job_card.oilinfornt_customer' 
                          )->get();
          return response()->json($data); 
      }elseif ($tag && $service == null) {
         $data = Job_card::leftJoin('service','job_card.service_id','service.id')
                          ->leftJoin('customer','customer.id','service.customer_id')
                          ->leftJoin('product','product.id','service.product_id')
                          ->leftJoin('store','store.id','job_card.store_id')
                          ->where('job_card.status','<>','Closed')
                          ->where('job_card.tag',$tag)
                          ->select(
                            'service.frame',
                            'service.registration',
                            'service.sale_date',
                            'customer.name as customer_name',
                            DB::raw('concat(store.name,"-",store.store_type) as store_name'),
                            DB::raw('concat(product.model_category,"-",product.model_name,"-",product.model_variant) as product_name'),
                            'job_card.tag',
                            'job_card.customer_status',
                            'job_card.job_card_type',
                            'job_card.vehicle_km',
                            'job_card.service_duration',
                            'job_card.estimated_delivery_time',
                            'job_card.service_in_time',
                            'job_card.service_out_time',
                            'job_card.service_status',
                            'job_card.wash',
                            'job_card.status',
                            'job_card.fi_status',  
                            'job_card.invoice_no',  
                            'job_card.hirise_amount',  
                            'job_card.payment_status',  
                            'job_card.pay_letter',  
                            'job_card.oilinfornt_customer' 
                          )->get();
          return response()->json($data); 
      }else{
        return response()->json($service); 
      }
    }
}
