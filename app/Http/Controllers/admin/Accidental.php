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
use \App\Model\PartStock;
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
use\App\Model\ServiceCharge;
use Mail;



class Accidental extends Controller
{
    public function __construct() {
        //$this->middleware('auth');
    }

    public function AccidentalJobCardList() {
        $data = [
            'layout' => 'layouts.main'
        ];
        
        return view('admin.service.accidental.acc_job_card_list',$data);
    }

    public function AccidentalJobCardList_api(Request $request) {
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;

           DB::enableQueryLog();
            $api_data = Job_card::join('service','service.id','job_card.service_id')
                            ->where('job_card.job_card_type','Accident')
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
                        ->orwhere('service.frame','like',"%".$serach_value."%")
                        ->orwhere('service.registration','like',"%".$serach_value."%")
                        // ->orwhere('job_card.in_time','like',"%".$serach_value."%")
                        // ->orwhere('job_card.out_time','like',"%".$serach_value."%")
                        ->orwhere('job_card.job_card_type','like',"%".$serach_value."%")
                        ->orwhere('job_card.customer_status','like',"%".$serach_value."%")
                        ->orwhere('job_card.service_status','like',"%".$serach_value."%")
                        // ->orwhere('job_card.vehicle_km','like',"%".$serach_value."%")
                        
                        // ->orwhere('job_card.service_in_time','like',"%".$serach_value."%")
                        // ->orwhere('job_card.service_out_time','like',"%".$serach_value."%")
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
                    // 'vehicle_km',
                    // 'service_in_time',
                    // 'service_out_time'
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

     public function AccidentalPartUpdateDetails(Request $request){       
        $jobcard_id = $request->input('jobcard_id');
        $estimated_date = $request->input('estimated_date');
        $estimated_time = $request->input('estimated_time');
        $survey = $request->input('survey');

        $drop = date('Y-m-d', strtotime($estimated_date));
        $start_time  = str_replace(' ','',$estimated_time);
        $date_time = $drop." ".$start_time;

        // $jobcardids = Job_card::where('id','=',$jobcard_id)->get()->first();
        $jobcardids = Job_card::where('job_card.id',$jobcard_id)->leftJoin('service','service.id','job_card.service_id')->select('job_card.*','service.customer_id')->first();
        $store_id = $jobcardids->store_id;
        $checkStore = CustomHelpers::CheckAuthStore($store_id);

        if(!$survey)
        {
            return 'Survey is required.';
        }
        if(!$jobcardids){

            return 'Job_card id not exists .';
        }
        else
        {
            if ($checkStore) {

              if($survey=='Yes'){ 
                    $pickupdetails=Job_card::where('id','=',$jobcard_id)->update([
                    'estimated_delivery_time'=>$date_time                                            
                ]);

               if($pickupdetails == NULL) {
                    return 'error';
                } 
                else{    
                     /* Add action Log */
                    CustomHelpers::userActionLog($action='Accidental Survey Start',$jobcard_id,$jobcardids['customer_id']);            
                    return 'success';               
                }
            }
         }else{
            return 'You Are Not Authorized For Update Details This JobCard';
         }

        }
        
    }

    public function AccidentalPartUpdateStatus(Request $request){

         $jobcard_id=$request->input('JobCardId');
         // $jobcardids=Job_card::where('id',$jobcard_id)->get()->first();
         $jobcardids = Job_card::where('job_card.id',$jobcard_id)->leftJoin('service','service.id','job_card.service_id')->select('job_card.*','service.customer_id')->first();
         $store_id=$jobcardids->store_id;
         $checkStore = CustomHelpers::CheckAuthStore($store_id);
        if(!$jobcardids){

            return 'Job_card id not exists .';
        }
        else
        {   if($checkStore)
            {
             $status='done';
             $jobcarddetails=Job_card::where('id','=',$jobcard_id)->update([
                    'service_status'=>$status                                            
                ]);

               if($jobcarddetails == NULL) {
                    return 'error';
                } 
                else{  
                 /* Add action Log */
                  CustomHelpers::userActionLog($action='Service Survey Done',$jobcard_id,$jobcardids['customer_id']);              
                    return 'success';               
                }
            }
            else{
                 return 'You Are Not Authorized For This JobCard.';
            }
        }

    }

       public function AccidentalPartUpdateStart(Request $request){

         $jobcard_id=$request->input('JobCardId');
         // $jobcardids=Job_card::where('id','=',$jobcard_id)->get()->first();
         $jobcardids = Job_card::where('job_card.id',$jobcard_id)->leftJoin('service','service.id','job_card.service_id')->select('job_card.*','service.customer_id')->first();
         $store_id=$jobcardids->store_id;
         $checkStore = CustomHelpers::CheckAuthStore($store_id);
        if(!$jobcardids){

            return 'Job_card id not exists .';
        }
        else
        {
            if($checkStore)
            {
             $status = 'start';
             $jobcarddetails = Job_card::where('id','=',$jobcard_id)->update([
                    'service_status'=>$status                                            
                ]);

               if($jobcarddetails == NULL) {
                    return 'error';
                } 
                else{
                 /* Add action Log */
                  CustomHelpers::userActionLog($action='Service Start',$jobcard_id,$jobcardids['customer_id']);                
                    return 'success';               
                }
            }
            else{
                 return 'You Are Not Authorized For This JobCard.';
            }
        }

    }

    public function AccidentalPartRequest($id) {
        $getpart = ServicePartRequest::leftjoin('part','service_part_request.part_id','part.id')->select('service_part_request.*','part.part_number')
        ->where('service_part_request.job_card_id',$id)
        ->where('service_part_request.deleted_at',NULL)
        ->get();

        $get_service_id = job_card::where('id',$id)->select('service_id')->first();
        $service_id = $get_service_id->service_id;

       $service_status = Job_card::where('id',$id)->select('service_status')->get()->first();

       $customer = ServiceModel::where('service.id',$service_id)
       ->Join('customer','customer.id','service.customer_id')
       ->select('customer.name','customer.mobile')
       ->first();

        if(!$service_status) {
             return redirect('/admin/accidental/jobcard/list')->with('error','Jobcard not exists.')->withInput();
        }
        if($getpart) {
            $data = [
                'job_card_id' =>  $id,
                'getpart' => $getpart,
                'customer' => $customer,
                'service_status'=>$service_status,
                'layout' => 'layouts.main'
            ];
            
            return view('admin.service.accidental.part_request',$data);
        } else {
            return redirect('/admin/accidental/jobcard/list')->with('error','Jobcard not exists.')->withInput();
        }
    }

    public function AccidentalPartRequest_DB(Request $request, $id) {
        $validator = Validator::make($request->all(),[
            // 'customer_confirmation'   =>  'required',
            'part_name.*'    =>  'required',
            'part_qty.*'    =>  'required',
            'customer_conf.*' =>  'required'
            
        ],
        [
            // 'customer_confirmation.required'  =>  'This Field is required',
            'part_name.*.required'  =>  'This Field is required',
            'part_qty.*.required'  =>  'This Field is required',
             'customer_conf.*.required'    =>  'This Field is required'
            
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        try{
            DB::beginTransaction();
              $jobCardId = $id;
             // $service_status=Job_card::where('id',$id)->where('service_status','=','done')->get()->first();
             $service_status = Job_card::where('job_card.id',$jobCardId)->leftJoin('service','service.id','job_card.service_id')->select('job_card.*','service.customer_id')->first();
             if($service_status['status'] == 'Done') {
                 return redirect('/admin/accidental/jobcard/list')->with('error','You cannot send parts request.')->withInput();
             } 
                $count = count($request->input('part_name'));
                for($i = 0; $i < $count ; $i++)
                {
                    $arr = [];
                    if ($request->input('customer_conf')[$i] == 'yes') {
                      $approved = 1;
                    }else{
                      $approved = 0;
                    }
                    if($request->input('customer_conf')[$i] == 'no')
                    {
                        $arr = [
                            'call_status'   =>  $request->input('call_status')[$i]
                        ];
                    }

                    $data = array_merge($arr,[
                        'job_card_id' => $jobCardId,
                        'part_name' =>  $request->input('part_name')[$i],
                        'qty' =>  $request->input('part_qty')[$i],
                        'confirmation' =>  'customer',
                        'approved' => $approved,
                        'approved_by' => Auth::id()
                    ]);
                    $insert = ServicePartRequest::insertGetId($data);
                    if(empty($insert)) {
                        DB::rollback();
                        return back()->with('error',"Something Wen't Wrong.")->withInput();
                    }
                }
                // $update_job_card = Job_card::where('id',$jobCardId)
                //                     ->update([
                //                         'customer_confirmation' =>  $request->input('customer_confirmation')
                //                     ]);

        }catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return redirect('/admin/accidental/part/request/'.$id)->with('error','some error occurred'.$ex->getMessage());
        }
        /* Add action Log */
        CustomHelpers::userActionLog($action='Accidental Part Request',$jobCardId,$service_status['customer_id']);
                  
        DB::commit();
        return redirect('/admin/accidental/jobcard/list')->with('success','Successfully Requested.');
    }

    public function AccidentalPartRequest_Remove(Request $request) {
        try{ 
            DB::beginTransaction();
            $req_id = $request->input('part_req_id');
            $getReq = ServicePartRequest::where('id',$req_id)->get()->first();

            $getJobcard  = job_card::where('id',$getReq['job_card_id'])->get()->first();
            if ($getJobcard['service_status'] == 'done') {
                return 'Sorry this jobcard is done you can not delete part !';
            }else{
                $id = ServicePartRequest::find($req_id);
                $data = $id->delete();
                if(empty($data)) {
                    DB::rollback();
                    return 'error';
                }else{

                    if ($getReq['part_id'] != 0 || $getReq['part_id'] != null) {
                        $part_id = $getReq['part_id'];
                        $store_id = $getJobcard['store_id'];
                        $qty = $getReq['assign_qty'];
                        $updatePartStock = PartStock::where('part_id',$part_id)->where('store_id',$store_id);
                        $inc = $updatePartStock->increment('quantity',$qty);
                        if ($inc == null) {
                            DB::rollback();
                            return 'error';
                        }else{
                            DB::commit();
                            return 'success';
                        }
                    }
                    DB::commit();
                    return 'success';
                }
            }
            // if ($getReq['part_id'] == 0) {
               // $id = ServicePartRequest::find($req_id);
               // $data = $id->delete();
            // }else{
            //     $getprice = ServicePartRequest::leftJoin('part_details','service_part_request.part_id','part_details.id')
            //     ->leftJoin('part','part.id','part_details.part_id')
            //     ->where('service_part_request.id',$req_id)
            //     ->select('part.price','service_part_request.qty')
            //     ->first();
            //     $price = $getprice['qty']*$getprice['price'];
            //     $getAmount = job_card::where('id',$getReq['job_card_id'])->get()->first();
            //     $total_amount = $getAmount['total_amount'];

            //     $update = job_card::where('id',$getReq['job_card_id'])->update([
            //         'total_amount' => $total_amount-$price
            //     ]);
            //     if ($update) {
            //         $id = ServicePartRequest::find($req_id);
            //         $data = $id->delete();
            //     }
            // }
               
            }catch(\Illuminate\Database\QueryException $ex) {
            return 'error';
        }
        
    }

    public function PartRequest_List() {

    	$data = [
            'layout' => 'layouts.main'
        ];
        return view('admin.service.accidental.part_req_list',$data);
    }

    public function PartRequestList_Api(Request $request) {
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
        DB::enableQueryLog();
        $api_data = Job_card::join('service_part_request','service_part_request.job_card_id','job_card.id')
        ->where('job_card.job_card_type','Accident')
        ->where('service_part_request.deleted_at',NULL)
        ->whereNotNull('service_part_request.job_card_id')
        ->select('job_card.id',
                'job_card.tag',
                'job_card.job_card_type',
                'service_part_request.confirmation',
                // 'job_card.customer_confirmation',
                DB::raw('count(service_part_request.job_card_id) as no_of_part'),
                'job_card.service_in_time', 
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
                    'job_card_type',
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

    public function PartAssign($JcId) {
        $tag = job_card::where('id',$JcId)->select('job_card.*')->first();
        $bayInfo = ServicePartRequest::where('service_part_request.deleted_at',NULL)
            ->where('service_part_request.job_card_id',$JcId)
            ->where('service_part_request.part_id','0')
            ->select(
                'service_part_request.part_name',
                'service_part_request.qty',
                'service_part_request.id'
            )
            ->orderBy('service_part_request.created_at','asc')->get();
        $partdetails = ServicePartRequest::leftjoin('part','part.id','service_part_request.part_id')
            ->where('service_part_request.deleted_at',NULL)
            ->where('service_part_request.part_id','<>',0)
            ->where('service_part_request.job_card_id',$JcId)
            ->select(
                'part.part_number',
                'service_part_request.part_name',
                'service_part_request.qty',
                'service_part_request.id',
                'service_part_request.confirmation',
                'part.price'
            )
            ->orderBy('service_part_request.created_at','asc')->get();

        $data = [
            'job_card_id' =>  $JcId,
            'partInfo'  =>  $bayInfo,
            'partdetails' => $partdetails,
            'tag' => $tag,
            'layout' => 'layouts.main'
        ];
        
        return view('admin.service.accidental.partAssign',$data);
    }

    public function PartAssign_DB(Request $request,$jobCardId) {
        try{
            DB::beginTransaction();
            $part_count = $request->input('part_count');
            $job_card_id = $request->input('jobcard_id');
            $getJc = job_card::where('id',$job_card_id)->select('total_amount')->get()->first();
            // $total_partprice = 0; 
            for($i = 0 ; $i < $part_count ; $i++) {
                $part_no  = $request->input('part_number'.$i);
                $part_price  = $request->input('part_price'.$i);
                $request_id  = $request->input('part_request_id'.$i);
                $qty  = $request->input('part_qty'.$i);
                
                $getdata = Parts::where('part.part_number',$part_no)
                            ->select(
                                    'part.id'    
                                )
                            ->first();
                if($getdata) {
                    $update = ServicePartRequest::where('id',$request_id)
                    ->update([
                        'part_id'  =>  $getdata->id,
                        'assign_qty'  =>  $qty
                        ]);
                }else{
                    // DB::rollback();
                    return back()->with('error','Part not assigned');
                }

                // $total_partprice = $part_price+$total_partprice;
            }
            // Add amount in jobcard
             // $jobcard =  job_card::where('id',$job_card_id)->update([
             //                'total_amount' => $getJc->total_amount+$total_partprice
             //            ]);

            if ($update == NULL) {
                DB::rollback();
                return back()->with('error','Part not assigned');
            }else{
                DB::commit();
                return redirect('/admin/accidental/part/request')->with('success','Successfully Assigned.');
            }

        }catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return back()->with('error','some error occurred'.$ex->getMessage());
        }
     
    }
    public function Part_Approve(Request $request, $id=NULL) {
        try{
            if ($id != NULL) {
                $update = ServicePartRequest::where('id',$id)->update([
                    'approved' => 1,
                    'confirmation' => 'insurance',
                    'approved_by' => Auth::id()
                ]);
            }

            $jobCardId = $request->input('JobCardId');
            $service = Job_card::where('job_card.id',$jobCardId)->leftJoin('service','service.id','job_card.service_id')->select('service.customer_id')->first();

            if ($jobCardId) {
               $update = ServicePartRequest::where('job_card_id',$jobCardId)->where('deleted_at',NULL)->where('part_id','<>',0)->update([
                'approved' => 1,
                'confirmation' => 'insurance',
                'approved_by' => Auth::id()
               ]);
            }
             if ($update == NULL) {
                   return 'error';
               }else{

                /* Add action Log */
                CustomHelpers::userActionLog($action='Part Approved',$jobCardId,$service['customer_id']);
                return 'success';
               }
        }catch(\Illuminate\Database\QueryException $ex) {
            return 'error';
        }
    }

     public function PartCustomer_Approve($id) {
        try{
            if ($id != NULL) {
                $service = Job_card::where('job_card.id',$id)->leftJoin('service','service.id','job_card.service_id')->select('service.customer_id')->first();

                $update = ServicePartRequest::where('id',$id)->update([
                    'approved' => 1,
                    'confirmation' => 'customer',
                    'approved_by' => Auth::id()
                ]);
            }
             if ($update == NULL) {
                   return 'error';
               }else{
                 /* Add action Log */
                  CustomHelpers::userActionLog($action='Part Approve By Customer',$id,$service['customer_id']);
                return 'success';
               }
        }catch(\Illuminate\Database\QueryException $ex) {
            return 'error';
        }
    }

    public function PartApprove_List($id) {
        $partdata = ServicePartRequest::leftjoin('job_card','service_part_request.job_card_id','job_card.id')
        ->leftJoin('part','service_part_request.part_id','part.id')
        ->where('job_card.job_card_type','Accident')
        ->where('service_part_request.deleted_at',NULL)
        ->where('service_part_request.part_id','<>','0')
        ->where('service_part_request.job_card_id',$id)
        ->select('service_part_request.id',
                'job_card.tag',
                'service_part_request.part_name',
                'service_part_request.qty',
                'part.part_number as part_no', 
                'service_part_request.approved',
                'service_part_request.confirmation',
                'part.price',
                'part_return_status')
        ->get();
        $data = [
            'partdata' => $partdata,
            'layout' => 'layouts.main'
        ];
        return view('admin.service.accidental.part_approve_list',$data);
    }

    // public function PartApproveList_Api(Request $request) {
    //     $search = $request->input('search');
    //     $serach_value = $search['value'];
    //     $start = $request->input('start');
    //     $limit = $request->input('length');
    //     $offset = empty($start) ? 0 : $start ;
    //     $limit =  empty($limit) ? 10 : $limit ;
    //     DB::enableQueryLog();
    //     $api_data = ServicePartRequest::leftjoin('job_card','service_part_request.job_card_id','job_card.id')
    //     ->leftJoin('part_details','service_part_request.part_id','part_details.id')
    //     ->leftJoin('part','part_details.part_id','part.id')
    //     ->where('job_card.job_card_type','Accident')
    //     ->where('service_part_request.deleted_at','0')
    //     ->where('service_part_request.part_id','<>','0')
    //     ->whereNotNull('service_part_request.job_card_id')
    //     ->select('service_part_request.id',
    //             'job_card.tag',
    //             'service_part_request.part_name',
    //             'service_part_request.qty',
    //             'part_details.part_no', 
    //             'service_part_request.approved',
    //             'part.price',
    //             'part_return_status');
            
    //         if(!empty($serach_value))
    //         {
    //            $api_data->where(function($query) use ($serach_value){
    //                     $query->where('job_card.tag','like',"%".$serach_value."%")
    //                     ->orwhere('part.price','like',"%".$serach_value."%")
    //                     ->orwhere('part_details.part_no','like',"%".$serach_value."%")
    //                     ->orwhere('service_part_request.qty','like',"%".$serach_value."%")
    //                     ->orwhere('service_part_request.part_name','like',"%".$serach_value."%")
    //                     ->orwhere('service_part_request.part_return_status','like',"%".$serach_value."%")
    //                     ->orwhere('service_part_request.id','like',"%".$serach_value."%");
    //                 });
               
    //         }
    //         if(isset($request->input('order')[0]['column']))
    //         {
    //             $data = [
    //                 'tag',
    //                 'part_no',
    //                 'part_name',
    //                 'qty',
    //                 'price',
    //                 'approved',
    //                 'part_return_status'
    //             ];
                
    //             $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
    //             $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
    //         }
    //         else
    //             $api_data->orderBy('service_part_request.created_at','desc');   
        
    //     $count = count( $api_data->get()->toArray());
    //     $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
    //     //print_r(DB::getQueryLog());die;
    //     $array['recordsTotal'] = $count;
    //     $array['recordsFiltered'] = $count;
    //     $array['data'] = $api_data; 
    //     return json_encode($array);
    // }

    public function Part_Return($id) {
        try{
            if ($id) {
                $getJc = ServicePartRequest::where('id',$id)->first();
                $service = Job_card::where('job_card.id',$getJc['job_card_id'])->leftJoin('service','service.id','job_card.service_id')->select('service.customer_id')->first();

               $update = ServicePartRequest::where('id',$id)->where('deleted_at',NULL)->where('part_id','<>',0)->update([
                'part_return_status' => 'return'
               ]);
            }
             if ($update == NULL) {
                   return 'error';
               }else{
                /* Add action Log */
                  CustomHelpers::userActionLog($action='Part Return In Accidental',$id,$service['customer_id']);
                return 'success';
               }
        }catch(\Illuminate\Database\QueryException $ex) {
            return 'error';
        }
    }

    public function Part_Receive($id) {
         try{
            if ($id) {
            $data = ServicePartRequest::leftjoin('part_details','service_part_request.part_id','part_details.id')
            ->leftjoin('part','part_details.part_id','part.id')
            ->leftjoin('job_card','job_card.id','service_part_request.job_card_id')
            ->where('service_part_request.id',$id)
            ->select('part.price','service_part_request.qty','service_part_request.job_card_id','job_card.total_amount','service_part_request.part_name','part_details.part_no')
            ->first();
            $service = Job_card::where('job_card.id',$data['job_card_id'])->leftJoin('service','service.id','job_card.service_id')->select('service.customer_id')->first();

            $amount = $data['price']*$data['qty'];
            $part_no = $data['part_no'];
            $part_name = $data['part_name']; 
            if ($data) {
                $update = ServicePartRequest::where('id',$id)->where('deleted_at',NULL)->where('part_id','<>',0)->update([
                'part_return_status' => 'received'
               ]);

                $jobUpdate = job_card::where('id',$data['job_card_id'])->update([
                    'total_amount' => $data['total_amount']-$amount
                ]);

            }
             if ($update && $jobUpdate) {

                $user_id = Auth::id();
                $users = Users::where('id','=',$user_id)->get()->first();                               
                if($users)
                {
                    $user_name=$users->name;
                    $email=$users->email;
                    Mail::send('admin.emails.part_receive', ['name' => $user_name,'amount'=>$amount,'part_no'=>$part_no,'part_name'=>$part_name], function($message) use ($email,$user_name)
                    {
                        $message->to($email, $user_name)->subject('Part Receive!');
                    });
                }   

                    /* Add action Log */
                  CustomHelpers::userActionLog($action='Part Receive In Accidental',$id,$service['customer_id']);
                   return 'success';
               }else{
                return 'error';
               }
            }
               
        }catch(\Illuminate\Database\QueryException $ex) {
            return 'error';
        }
    }

     public function PartReceive_List() {
        $data = [
            'layout' => 'layouts.main'
        ];
        return view('admin.service.accidental.part_return_list',$data);
    }

    public function PartReceiveList_Api(Request $request) {
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
        DB::enableQueryLog();
        $api_data = ServicePartRequest::leftjoin('job_card','service_part_request.job_card_id','job_card.id')
        ->leftJoin('part_details','service_part_request.part_id','part_details.id')
        ->leftJoin('part','part_details.part_id','part.id')
        ->where('job_card.job_card_type','Accident')
        ->where('service_part_request.deleted_at',NULL)
        ->where('service_part_request.part_id','<>','0')
        ->where('service_part_request.part_return_status','<>',NULL)
        ->whereNotNull('service_part_request.job_card_id')
        ->select('service_part_request.id',
                'job_card.tag',
                'service_part_request.part_name',
                'service_part_request.qty',
                'part_details.part_no', 
                'service_part_request.approved',
                'part.price',
                'part.part_number',
                'part_return_status');
            
            if(!empty($serach_value))
            {
               $api_data->where(function($query) use ($serach_value){
                        $query->where('job_card.tag','like',"%".$serach_value."%")
                        ->orwhere('part.price','like',"%".$serach_value."%")
                        ->orwhere('part.part_number','like',"%".$serach_value."%")
                        ->orwhere('service_part_request.qty','like',"%".$serach_value."%")
                        ->orwhere('service_part_request.part_name','like',"%".$serach_value."%")
                        ->orwhere('service_part_request.part_return_status','like',"%".$serach_value."%")
                        ->orwhere('service_part_request.id','like',"%".$serach_value."%");
                    });
               
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                    'tag',
                    'part_number',
                    'part_name',
                    'qty',
                    'price',
                    'approved',
                    'part_return_status'
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

     public function JobcardApprove_List() {
        $data = [
            'layout' => 'layouts.main'
        ];
        return view('admin.service.accidental.jc_approve_list',$data);
    }

    public function JobcardApproveList_Api(Request $request) {
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
        DB::enableQueryLog();
        $api_data = Job_card::join('service_part_request','service_part_request.job_card_id','job_card.id')
        ->where('job_card.job_card_type','Accident')
        ->where('service_part_request.deleted_at',NULL)
        ->where('service_part_request.part_id','<>','0')
        ->whereNotNull('service_part_request.job_card_id')
        ->select('job_card.id',
                'job_card.tag',
                'service_part_request.confirmation',
                DB::raw('count(service_part_request.job_card_id) as no_of_part'),
                'job_card.service_in_time', 
                'job_card.service_out_time')
        ->groupBy('job_card.id');
            
            if(!empty($serach_value))
            {
               $api_data->where(function($query) use ($serach_value){
                        $query->where('job_card.tag','like',"%".$serach_value."%")
                        // ->orwhere('job_card.customer_confirmation','like',"%".$serach_value."%")
                        ->orwhere('count(service_part_request.id)','like',"%".$serach_value."%")
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

    public function PartEstimation($id) {
        $jobcard = job_card::where('id',$id)->get()->first();

        $service_id = $jobcard['service_id'];

        $service_status = Job_card::where('id',$id)->select('service_status')->get()->first();

         $customer = ServiceModel::where('service.id',$service_id)->Join('customer','customer.id','service.customer_id')->select('customer.name','customer.mobile')->first();

        $getpart = ServicePartRequest::leftjoin('part','part.id','service_part_request.part_id')
        ->select('service_part_request.*','part.part_number',DB::raw('IFNULL((part.price),0) as price'))
        ->where('service_part_request.job_card_id',$id)->where('service_part_request.deleted_at',NULL)->get();

        $getcharge = ServiceCharge::where('job_card_id',$id)->where('charge_type','Charge')->where('deleted_at',NULL)->get();
         if($getcharge) {
            $getcharge = $getcharge->toArray();
        }
        $chargedata =array();
        foreach ($getcharge as $key => $value) {
            $chargedata[$value['sub_type']] = $value;
        }
        $data = [
            'job_card_id' => $id,
            'getpart' => $getpart,
            'customer' => $customer,
            'chargedata' => $chargedata,
            'jobcard' => $jobcard,
            'layout' => 'layouts.main'
        ];
        
        return view('admin.service.accidental.part_estimation',$data);
    }

    public function PartEstimation_DB(Request $request, $id) {
        $validator = Validator::make($request->all(),[
            // 'customer_confirmation'=> 'required',
            'part_name.*' => 'required',
            'part_qty.*' => 'required',
            'charge_type.*' => 'required',
            'charge.*' => 'required',
        ],
        [
            // 'customer_confirmation.required'  =>  'This Field is required',
            'part_name.*.required'  =>  'This Field is required',
            'part_qty.*.required'  =>  'This Field is required',
            'charge_type.*.required'  =>  'This Field is required',
            'charge.*.required'  =>  'This Field is required',
            
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        try{
            DB::beginTransaction();
              $jobCardId = $id;
              $service = Job_card::where('job_card.id',$jobCardId)->leftJoin('service','service.id','job_card.service_id')->select('job_card.*','service.customer_id')->first();
               if ($service['status'] == 'Done') {
                  return redirect('/admin/accidental/part/estimation/'.$jobCardId)->with('error','Job card is already done.')->withInput();
              }

              $fork_repair = $request->input('fork_repair');
              $fork_type = $request->input('fork_type');
              $fork_charge = $request->input('fork_charge');
              $fork_qty = $request->input('fork_qty');
              if ($fork_type == 'old' && empty($fork_charge)) {
                  return redirect('/admin/accidental/part/estimation/'.$jobCardId)->with('error','Please enter the fork charge.')->withInput();
              }
              if ($fork_type == 'new' && empty($fork_qty)) {
                  return redirect('/admin/accidental/part/estimation/'.$jobCardId)->with('error','Please enter the fork quantity.')->withInput();
              }
             
              $chasis_repair = $request->input('chasis_repair');
              $chasis_type = $request->input('chasis_type');
              $chasis_charge = $request->input('chasis_charge');
              $chasis_qty = $request->input('chasis_qty');
              if ($chasis_type == 'old' && empty($chasis_charge)) {
                  return redirect('/admin/accidental/part/estimation/'.$jobCardId)->with('error','Please enter the chasis charge.')->withInput();
              }
              if ($chasis_type == 'new' && empty($chasis_qty)) {
                  return redirect('/admin/accidental/part/estimation/'.$jobCardId)->with('error','Please enter the chasis quantity.')->withInput();
              }
              

              $service_status=Job_card::where('id',$id)->where('service_status','=','done')->get()->first();
             if($service_status) {
                 return redirect('/admin/accidental/jobcard/list')->with('error','You cannot send parts estimation.')->withInput();
             }  

            $jobcard_id = [
                'job_card_id'   =>  $jobCardId
            ];
            $count = count($request->input('part_name'));
            if($count){
            for($i = 0; $i < $count ; $i++)
                {
                    $arr = [];
                    if ($request->input('customer_conf')[$i] == 'yes') {
                      $approved = 1;
                    }else{
                      $approved = 0;
                    }
                    if($request->input('customer_conf')[$i] == 'no')
                    {
                        $arr = [
                            'call_status'   =>  $request->input('call_status')[$i]
                        ];
                    }
                    $data = array_merge($arr,[
                        'job_card_id' => $jobCardId,
                        'part_name' =>  $request->input('part_name')[$i],
                        'qty' =>  $request->input('part_qty')[$i],
                        'confirmation' =>  'customer',
                        'approved' => $approved,
                        'approved_by' => Auth::id()
                    ]);

                    $getpartdata = ServicePartRequest::where('job_card_id',$jobCardId)->where('deleted_at',NULL)->where('part_name',$request->input('part_name')[$i])->get()->first();

                    if ($getpartdata['id']) {
                        $part = ServicePartRequest::where('job_card_id',$jobCardId)->where('id',$getpartdata['id'])->update($data);
                    }else{
                         $part = ServicePartRequest::insertGetId($data);
                    }
                    if(empty($part)) {
                        DB::rollback();
                        return back()->with('error',"Something Wen't Wrong.")->withInput();
                    }
                }
            }
           
            $chargecount = count($request->input('charge_type'));
            if ($chargecount) {
                for($i = 0; $i < $chargecount ; $i++)
                {
                    $chargedata = array_merge($jobcard_id,[
                        'charge_type' => 'Charge',
                        'sub_type' =>  $request->input('charge_type')[$i],
                        'amount' =>  $request->input('charge')[$i]
                    ]);

                    $getchargedata = ServiceCharge::where('job_card_id',$jobCardId)->where('charge_type','Charge')->where('sub_type',$request->input('charge_type')[$i])->where('deleted_at',NULL)->get()->first();

                    if ($getchargedata['id']) {
                        $chdata = ServiceCharge::where('job_card_id',$jobCardId)->where('id',$getchargedata['id'])->update($chargedata);
                    }else{
                         $chdata = ServiceCharge::insertGetId($chargedata);
                    }
                    
                    if(empty($chdata)) {
                        DB::rollback();
                        return back()->with('error',"Something Wen't Wrong.")->withInput();
                    }
                }
            }

            if ($fork_type == 'old') {
                //insert service
                $data = array_merge($jobcard_id,[
                        'charge_type' => 'Charge',
                        'sub_type' =>  $fork_repair,
                        'amount' =>  $fork_charge
                    ]);
                     $getchargedata = ServiceCharge::where('job_card_id',$jobCardId)->where('charge_type','Charge')->where('sub_type',$fork_repair)->where('deleted_at',NULL)->get()->first();

                    if ($getchargedata['id']) {
                        $chdata = ServiceCharge::where('job_card_id',$jobCardId)->where('id',$getchargedata['id'])->update($data);
                    }else{
                         $chdata = ServiceCharge::insertGetId($data);
                    }
                    
                    if(empty($chdata)) {
                        DB::rollback();
                        return back()->with('error',"Something Wen't Wrong.")->withInput();
                    }
            }if($fork_type == 'new'){
                //part insert
                $data = array_merge($jobcard_id,[
                        'part_name' =>  $fork_repair,
                        'qty' =>  $fork_qty
                    ]);
                    $getchargedata = ServiceCharge::where('job_card_id',$jobCardId)->where('charge_type','Charge')->where('sub_type',$fork_repair)->where('deleted_at',NULL)->get()->first();

                    if ($getchargedata['id']) {
                        $part = ServiceCharge::where('job_card_id',$jobCardId)->where('id',$getchargedata['id'])->delete();
                    }

                    $getpartdata = ServicePartRequest::where('job_card_id',$jobCardId)->where('deleted_at',NULL)->where('part_name',$fork_repair)->get()->first();

                    if ($getpartdata['id']) {
                        $part = ServicePartRequest::where('job_card_id',$jobCardId)->where('id',$getpartdata['id'])->update($data);
                    }else{
                         $part = ServicePartRequest::insertGetId($data);
                    }
                    if(empty($part)) {
                        DB::rollback();
                        return back()->with('error',"Something Wen't Wrong.")->withInput();
                    }
            }

            if ($chasis_type == 'old') {
                // service insert
                $data = array_merge($jobcard_id,[
                        'charge_type' => 'Charge',
                        'sub_type' =>  $chasis_repair,
                        'amount' =>  $chasis_charge
                    ]);
                     $getchargedata = ServiceCharge::where('job_card_id',$jobCardId)->where('charge_type','Charge')->where('sub_type',$chasis_repair)->where('deleted_at',NULL)->get()->first();

                    if ($getchargedata['id']) {
                        $chdata = ServiceCharge::where('job_card_id',$jobCardId)->where('id',$getchargedata['id'])->update($data);
                    }else{
                         $chdata = ServiceCharge::insertGetId($data);
                    }
                    
                    if(empty($chdata)) {
                        DB::rollback();
                        return back()->with('error',"Something Wen't Wrong.")->withInput();
                    }
            }if ($chasis_type == 'new') {
                // part insert
                $data = array_merge($jobcard_id,[
                        'part_name' =>  $chasis_repair,
                        'qty' =>  $chasis_qty
                    ]);

                    $getchargedata = ServiceCharge::where('job_card_id',$jobCardId)->where('charge_type','Charge')->where('sub_type',$chasis_repair)->get()->first();

                    if ($getchargedata['id']) {
                        $chdata = ServiceCharge::where('job_card_id',$jobCardId)->where('id',$getchargedata['id'])->where('deleted_at',NULL)->delete();
                    }

                   $getpartdata = ServicePartRequest::where('job_card_id',$jobCardId)->where('deleted_at',NULL)->where('part_name',$chasis_repair)->get()->first();

                    if ($getpartdata['id']) {
                        $part = ServicePartRequest::where('job_card_id',$jobCardId)->where('id',$getpartdata['id'])->update($data);
                    }else{
                         $part = ServicePartRequest::insertGetId($data);
                    }
                    if(empty($part)) {
                        DB::rollback();
                        return back()->with('error',"Something Wen't Wrong.")->withInput();
                    }
            }
            // $update_job_card = Job_card::where('id',$jobCardId)->update([
            //         'customer_confirmation' =>  $request->input('customer_confirmation')
            //     ]);

            if ($part == NULL) {
                DB::rollback();
                return redirect('/admin/accidental/part/estimation/'.$id)->with('error','Something went wrong.');
             }else{
                /* Add action Log */
                  CustomHelpers::userActionLog($action='Part Estimation',$id,$service['customer_id']);
                DB::commit();
                return redirect('/admin/accidental/part/estimation/'.$id)->with('success','Part Estimation Added Successfully.');
             } 

        }catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return redirect('/admin/accidental/part/estimation/'.$id)->with('error','some error occurred'.$ex->getMessage());
        }
       
    }


    public function Part_Issue(Request $request) {
         try{
            $jobCardId = $request->input('JobCardId');
            
            if ($jobCardId) {
                $partdata = ServicePartRequest::where('job_card_id',$jobCardId)->whereNull('deleted_at')->get();

                $getpart = ServicePartRequest::where('job_card_id',$jobCardId)->whereNull('deleted_at')->where('part_id','<>',0)->get();
                if (count($partdata) == count($getpart))  {
                    
                       $update = ServicePartRequest::where('job_card_id',$jobCardId)->whereNull('deleted_at')->where('part_id','<>',0)->update([
                        'part_issue' => 'yes'
                       ]);
                       if ($update == NULL) {
                           return 'error';
                       }else{
                        return 'success';
                       }
                }else{
                    return 'Part not assign';
                }
            }
             
        }catch(\Illuminate\Database\QueryException $ex) {
            return 'error';
        }
    }
}