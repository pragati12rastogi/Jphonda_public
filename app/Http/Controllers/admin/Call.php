<?php

namespace App\Http\Controllers\admin;
use Illuminate\Http\Request;
use \stdClass;
use \App\Model\Store;

use \App\Custom\CustomHelpers;
use \App\Model\SaleAccessories;
use \App\Model\RtoModel;
use \App\Model\RtoSummary;

use \App\Model\Payment;
use \App\Model\OrderPendingModel;
use \App\Model\FinanceCompany;
use \App\Model\OtcSale;
use \App\Model\Insurance;
use \App\Model\ServiceModel;

use \App\Model\Calling;
use \App\Model\CallingSummery;
use \App\Model\CallSummaryDetails;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use DateTime;
use Auth;
use Hash;
use PDF;
use \Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\Import;
use App\Model\Job_card;
use App\Model\Pickup;
use \App\Model\Parts;
use \App\Model\PartStock;
use \App\Model\ServiceBooking;
use \App\Model\CallData;
use \App\Model\CallSetting;
use \App\Model\CallAssignSetting;
use \App\Model\CallDataDetails;
use \App\Model\CallDataRecord;
use App\Model\CallStatusMaster;
use App\Model\Customers;
use \App\Model\Users;
use \App\Model\UserMaster;
use \App\Model\HR_Leave;
use \App\Model\HolidayFestival;
use App\Model\Master;
use App\Model\Sale;
use App\Model\Settings;
use \App\Custom\FirebaseNotification;

class Call extends Controller
{
    public function __construct()
    {
        //$this->middleware('auth');
    }
    public function forSale(){
        return view('admin.call.forSale',['layout' => 'layouts.main']);
    }

    public function forSale_api(Request $request,$tab) {
        DB::enableQueryLog();
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
            $api_data = RtoModel::leftJoin('rto_summary',DB::raw(' rto_summary.currentStatus = 1 and rto_summary.rto_id'),'rto.id')
                    ->leftJoin('call_summary','call_summary.type_id',DB::raw('rto.sale_id and call_summary.type = "sale"'))
            ->select(
                'rto.id',
                'rto.sale_id',
                'call_summary.id as call_summary_id',
                'rto_summary.customer_name',
                'rto_summary.mobile',
                'rto_summary.address',
                'rto_summary.numberPlateStatus',
                'rto_summary.rcStatus',
                'rto.registration_number',
                'rto.rc_number',
                'call_summary.call_status',
                'call_summary.remark',
                'call_summary.call_date'
            );
            if($tab == 'pending')
            {
                $api_data = $api_data->where(function($query) {
                    $query->where('rto_summary.numberPlateStatus',0)
                            ->orWhere('rto_summary.rcStatus',0);
                });
                $api_data = $api_data->where(function($query) {
                    $query->whereNotNull('rto.registration_number')
                            ->orwhereNotNull('rto.rc_number');
                });
                $api_data = $api_data->where(function($query){
                    $query->whereNull('next_call_date')
                            ->orwhereDate('next_call_date','<=',date('Y-m-d'));
                });
            }
            else{
                $api_data = $api_data->where(function($query) {
                    $query->where('rto_summary.numberPlateStatus',0)
                            ->orWhere('rto_summary.rcStatus',0)
                            ->orwhere('rto_summary.numberPlateStatus',1)
                            ->orWhere('rto_summary.rcStatus',1);
                });
                $api_data = $api_data->where(function($query) {
                    $query->whereNotNull('rto.registration_number')
                            ->orwhereNotNull('rto.rc_number');
                });
                // $api_data = $api_data->where(function($query){
                //     $query->whereDate('next_call_date','>',date('Y-m-d'));
                // });
            }

            // $api_data = $api_data->get()->toArray();
            // print_r(DB::getQueryLog());
            // print_r($api_data);die;
            
    
            if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('rto_summary.customer_name','like',"%".$serach_value."%")
                    ->orwhere('rto_summary.mobile','like',"%".$serach_value."%")
                    ->orwhere('rto.registration_number','like',"%".$serach_value."%")
                    ->orwhere('rto_summary.numberPlateStatus','like',"%".$serach_value."%")                   
                    ->orwhere('rto.rc_number','like',"%".$serach_value."%")                   
                    ->orwhere('rto_summary.rcStatus','like',"%".$serach_value."%")                   
                    ->orwhere('call_summary.call_date','like',"%".$serach_value."%")                   
                    ->orwhere('call_summary.call_status','like',"%".$serach_value."%")                   
                    ->orwhere('call_summary.remark','like',"%".$serach_value."%")                   
                    ;
                });
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                'rto_summary.customer_name',
                'rto_summary.mobile',
                'rto.registration_number',
                'rto_summary.numberPlateStatus',
                'rto.rc_number',
                'rto_summary.rcStatus',
                'call_summary.call_date',
                'call_summary.call_status',
                'call_summary.remark'
                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                if($tab == 'pending')
                {
                    $api_data->orderBy('call_summary.updated_at','asc');
                }
                else{
                    $api_data->orderBy('call_summary.next_call_date','desc');
                }
                // $api_data->orderBy('rto.id','desc');      
        
        $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        $queries = DB::getQueryLog();
        //print_r($queries);die();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }
    public function updateCall_DB(Request $request)
    {
        $this->validate($request,[
            'call_status'=>'required',
            'next_call_date'    =>  'required',
            'remark'    =>  'required'
        ],[
            'call_status.required'=> 'This is required.', 
            'next_call_date.required'=> 'This is required.', 
            'remark.required'=> 'This is required.'
        ]);
        $callStatus = $request->input('call_status');
        $next_call_date = $request->input('next_call_date');
        $remark = $request->input('remark');
        $saleId = $request->input('call_sale_id');
        $rtoId = $request->input('call_rto_id');
        $next = CustomHelpers::showDate($next_call_date,'Y-m-d');
        $first_call = new DateTime(date('Y-m-d'));
        $next_call = new DateTime(CustomHelpers::showDate($next_call_date,'Y-m-d'));
        $diff = $next_call->diff($first_call)->format("%a");
       
        
        DB::beginTransaction();
        try{
         if ($diff < 15) {
            return redirect('/admin/call/Sale/view?saleId='.$saleId.'&rtoId='.$rtoId.'&type=pending')->with('error','Please select date after 15 days ')->withInput();
        }else{
            $getPreCallData = CallingSummery::where('type','sale')->where('type_id',$saleId)->first();
            // print_r($getPreCallData);die;
            if(empty($getPreCallData))
            {
                $ins = [
                    'type'  =>  'sale',
                    'type_id'   =>  $saleId,
                    'call_date'     =>  date('Y-m-d'),
                    'call_status'   =>  $callStatus,
                    'remark'    =>  $remark,
                    'next_call_date'    =>  CustomHelpers::showDate($next_call_date,'Y-m-d')
                ]; 
                $callSummary = CallingSummery::insertGetId($ins);

                $ins = [ 
                    'call_summary_id'   =>  $callSummary,
                    'call_date'     =>  date('Y-m-d'),
                    'call_status'   =>  $callStatus,
                    'remark'    =>  $remark,
                    'next_call_date'    =>  CustomHelpers::showDate($next_call_date,'Y-m-d')
                ];
                $callSummaryDet = CallSummaryDetails::insertGetId($ins);

            }
            else
            {
                $call_id = $getPreCallData->id;
                $ins = [
                    'call_summary_id'   =>  $call_id,
                    'call_date'     =>  date('Y-m-d'),
                    'call_status'   =>  $callStatus,
                    'remark'    =>  $remark,
                    'next_call_date'    =>  CustomHelpers::showDate($next_call_date,'Y-m-d')
                ];
                $callSummaryDet = CallSummaryDetails::insertGetId($ins);
                // print_r($insert);die;
                $updateData = [
                    'call_date' =>  date('Y-m-d'),
                    'call_status'   =>  $callStatus,
                    'remark'    =>  $remark,
                    'next_call_date'    =>  CustomHelpers::showDate($next_call_date,'Y-m-d')
                ];
                $callSummary = CallingSummery::where('id',$call_id)->update($updateData);
            }  
        //    print_r($callSummary);
        //    print_r($callSummaryDet);
            // die;
            if($callSummary && $callSummaryDet)
            {
                DB::commit();
                return redirect('/admin/call/Sale/view?saleId='.$saleId.'&rtoId='.$rtoId.'&type=pending')->with('success','Success, Call Successfully Updated');
            }
            else{
                DB::rollback();
                return redirect('/admin/call/Sale/view?saleId='.$saleId.'&rtoId='.$rtoId.'&type=pending')->with('error','Error, Call UnSuccessfully Updated');

            }
          }
        }
        catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return redirect('/admin/call/Sale/view?saleId='.$saleId.'&rtoId='.$rtoId.'&type=pending')->with('error','Something went wrong '.$ex)->withInput();
        }
        return redirect('/admin/call/Sale/view?saleId='.$saleId.'&rtoId='.$rtoId.'&type=pending')->with('error','Something went wrong')->withInput();
        
    }
    public function updateDelivery_DB(Request $request)
    {

        try{
            DB::beginTransaction();
            // print_r(DB::table('users')->where('id',Auth::id())->select('user_type')->first());die;
            $user = Users::where('id',Auth::id())->select('user_type','role')->first();
            $userType = $user->user_type;
            $userRole = $user->role;
            // $this->validate($request,[
            //     'numberPlateDelivery'=>'required',
            //     'rcDelivery'=>'required'
            // ],[
            //     'numberPlateDelivery.required'=> 'This is required.', 
            //     'rcDelivery.required'=> 'This is required.'
            // ]);
            $saleId = $request->input('delivery_sale_id');
            $rtoId = $request->input('delivery_rto_id');
            $numberPlateDelivery = $request->input('numberPlateDelivery');
            $rcDelivery = $request->input('rcDelivery');
            
            $checkBoth = RtoModel::where('rto.id',$rtoId)->where('rto.sale_id',$saleId)
                        ->leftJoin('rto_summary',function($join) use($rtoId){
                            $join->on('rto_summary.rto_id',DB::raw($rtoId))
                                    ->where('rto_summary.currentStatus',1);
                        })
                        ->where(function($query) {
                            $query->whereNotNull('rto.registration_number')
                                    ->orwhereNotNull('rto.rc_number');
                        })
                        ->select('rto.id as rto_id','rto_summary.id as rto_summary_id','rto.registration_number','rto.rc_number','rto_summary.numberPlateStatus','rto_summary.rcStatus')->first();
            if(empty($checkBoth->rto_id) || empty($checkBoth->rto_summary_id))
            {
                return redirect('/admin/call/Sale/view?saleId='.$saleId.'&rtoId='.$rtoId.'&type=all')->with('error','This information not valid.')->withInput();
            }   
            $updateData = [];
            if($numberPlateDelivery == 'yes' && $checkBoth->numberPlateStatus == 0)
            {
                $updateData['numberPlateStatus']    =   1;
            }
            elseif($numberPlateDelivery == 'no' && $checkBoth->numberPlateStatus == 1){
                if($userType != 'superadmin' && $userRole != 'Superadmin'){
                    return redirect('/admin/call/Sale/view?saleId='.$saleId.'&rtoId='.$rtoId.'&type=all')->with('error','Number Plate already delivered, if you want to change pls contact superadmin.')->withInput();
                }
                $updateData['numberPlateStatus']    =   0;
            }
            if($rcDelivery == 'yes' && $checkBoth->rcStatus == 0)
            {
                $updateData['rcStatus']   =   1;
            }
            elseif($rcDelivery == 'no' && $checkBoth->rcStatus == 1){
                if($userType != 'superadmin' && $userRole != 'Superadmin'){
                    return redirect('/admin/call/Sale/view?saleId='.$saleId.'&rtoId='.$rtoId.'&type=all')->with('error','RC already delivered, if you want to change pls contact superadmin.')->withInput();
                }
                $updateData['rcStatus']    =   0;
            }

            //if delivery for rc 
            if($rcDelivery == 'yes')
            {
                //check RC correction Requested or not
                $checkRcCorrectionReq = RtoModel::where('rto.id',$rtoId)
                                        ->leftJoin('rc_correction_request',function($join){
                                            $join->on('rc_correction_request.rto_id','rto.id')
                                                    ->where('rc_correction_request.status','approved')
                                                    ->where('rc_correction_request.id',DB::raw('(select max(id) from rc_correction_request where rto_id = rto.id)'));
                                        })->select('rc_correction_request.id')->first();
                // print_r($checkRcCorrectionReq);die;
                if(!empty($checkRcCorrectionReq->id))
                {
                    //check RC 'correction request' payment paid or not, when RC Delivered to customer
                    $checkRcCorrection = RtoModel::where('rto.id',$rtoId)
                                            ->leftJoin('rc_correction_request',function($join) use($checkRcCorrectionReq){
                                                $join->on('rc_correction_request.rto_id','rto.id')
                                                        ->where('rc_correction_request.status','approved')
                                                        ->where('rc_correction_request.id',$checkRcCorrectionReq->id);

                                            })
                                            ->leftJoin('payment',function($join) use($checkRcCorrectionReq) {
                                                $join->on('payment.type_id',DB::raw($checkRcCorrectionReq->id))
                                                    ->where('payment.type','RcCorrection');
                                            })
                                            ->select('rto.id','rc_correction_request.payment_amount as correctionAmount',
                                                DB::raw('IFNULL(sum(payment.amount),0) as paidAmount'))
                                            ->first();
                    // print_r($checkRcCorrection);
                    if($checkRcCorrection->paidAmount != $checkRcCorrection->correctionAmount)
                    {
                        return redirect('/admin/call/Sale/view?saleId='.$saleId.'&rtoId='.$rtoId.'&type=all')->with('error','Amount Should be paid for RC Correction.')->withInput(); 
                    }
                }
            }

            $updateRtoSummary = RtoSummary::where('rto_id',$rtoId)->where('currentStatus',1)
                                ->update($updateData);
            
        }
        catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return redirect('/admin/call/Sale/view?saleId='.$saleId.'&rtoId='.$rtoId.'&type=all')->with('error','Something went wrong '.$ex)->withInput();
        }
        DB::commit();
        return redirect('/admin/call/Sale/view?saleId='.$saleId.'&rtoId='.$rtoId.'&type=all')->with('success','Successfully Delivered')->withInput();

    }
    public function forSale_view(Request $request)
    {
        $sale_id = $request->input('saleId');
        $rto_id = $request->input('rtoId');
        $type = $request->input('type');
        // print_r($sale_id);
        // print_r($rto_id);die;
        $api_data = RtoModel::leftJoin('rto_summary',DB::raw(' rto_summary.currentStatus = 1 and rto_summary.rto_id'),'rto.id')
                    ->leftJoin('call_summary','call_summary.type_id',DB::raw('rto.sale_id and call_summary.type = "sale"'));
                    $api_data->where('rto.sale_id',$sale_id)
                    ->where('rto.id',$rto_id);
            $api_data->select(
                'rto.id',
                'rto.sale_id',
                'call_summary.id as call_summary_id',
                'rto_summary.customer_name',
                'rto_summary.mobile',
                'rto_summary.address',
                'rto_summary.numberPlateStatus',
                'rto_summary.rcStatus',
                'rto.registration_number',
                'rto.rc_number',
                'call_summary.call_status',
                'call_summary.remark',
                'call_summary.call_date',
                'call_summary.next_call_date'
            )->orderBy('call_summary.updated_at','asc');
           
            $api_data = $api_data->first();
        
        if(empty($api_data))
        {
            return redirect('/admin/call/Sale')->with('error',"Error, Couldn't find Call's for Requested Sale");
        }
        $api_data = $api_data->toArray();
        // print_r($api_data);die;
        if(!empty($api_data['call_summary_id']))
        {
            $allLastCallData = CallSummaryDetails::where('call_summary_id',$api_data['call_summary_id'])
                                ->orderBy('created_at','desc')->get();
        }else{
            $allLastCallData = array();
        }
        
        $data = [
            'data'  =>  $api_data,
            'oldCallData' => $allLastCallData,
            'type'  =>  (($type)? $type : '' ),
            'layout' => 'layouts.main'
        ]; 
        //  print_r($data);die;

        return view('admin.call.forSaleView',$data);
    }
    public function nextCall(Request $request)
    {
        $sale_id = $request->input('saleId');
        $rto_id = $request->input('rtoId');
        $api_data = RtoModel::leftJoin('rto_summary',DB::raw(' rto_summary.currentStatus = 1 and rto_summary.rto_id'),'rto.id')
        ->leftJoin('call_summary','call_summary.type_id',DB::raw('rto.sale_id and call_summary.type = "sale"'));
                
        $api_data->select(
            'rto.id',
            'rto.sale_id',
            'call_summary.id as call_summary_id',
            'rto_summary.customer_name',
            'rto_summary.mobile',
            'rto_summary.address',
            'rto_summary.numberPlateStatus',
            'rto_summary.rcStatus',
            'rto.registration_number',
            'rto.rc_number',
            'call_summary.call_status',
            'call_summary.remark',
            'call_summary.call_date',
            'call_summary.next_call_date'
        )->orderBy('call_summary.updated_at','asc');
        $api_data->where(function($query) {
                            $query->where('rto_summary.numberPlateStatus',0)
                                    ->orWhere('rto_summary.rcStatus',0);
                        })
                        ->where(function($query) {
                            $query->whereNotNull('rto.registration_number')
                                    ->orwhereNotNull('rto.rc_number');
                        })
                        ->where(function($query){
                            $query->whereNull('next_call_date')
                                    ->orwhereDate('next_call_date','<=',date('Y-m-d'));
                        });
        
        $count = $api_data->count();
        // print_r($api_data->get());
        // print_r($api_data1);die;
        // print_r($api_data);die;
        
        if($count > 1){
            $api_data->where('rto.sale_id','<>',$sale_id)
                    ->where('rto.id','<>',$rto_id);
        }
        $api_data = $api_data->first();
        if(empty($api_data))
        {
            return back()->with('error',"Error, Couldn't find Call's for Next Sale");
        }
        $api_data = $api_data->toArray();
        $saleId = $api_data['sale_id'];
        $rtoId = $api_data['id'];
        return redirect('/admin/call/Sale/view?saleId='.$saleId.'&rtoId='.$rtoId.'&type=pending');
    }

    public function callList() {

        // $call_users = Users::where('users.role','Receptionist')
        //                     ->select('users.id','users.emp_id','users.name')
        //                     ->orderBy('users.id')
        //                     ->get();
        $callType = ['ThankYou','PSF','Service'];
        $call_users = CallAssignSetting::whereIn('type',$callType)
                            ->leftjoin('users','call_assign_setting.user_id','users.id')
                            ->select('users.id','users.emp_id',
                                DB::raw('Concat(users.name," ",IFNull(users.middle_name,"")," ",IFNull(users.last_name,""))as name'))
                            ->groupBy('users.id')
                            ->orderBy('users.id')
                            ->get();
        $auth_role = Auth::user()->role;
        $data = [
            'layout' => 'layouts.main',
            'call_users' => $call_users,
            'auth_role' =>  $auth_role
        ];
        return view('admin.call.call_summary',$data);
    }

    public function callList_api(Request $request, $tab) {
        //DB::enableQueryLog();
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;

        $auth_role = Auth::user()->role;
        $user_id = $request->input('user');
        $leave_date = [];
   
        if(!empty($user_id) || $user_id > 0){
            $emp_id = Users::where('id',$user_id)->pluck('emp_id')->toArray();
            
            $get_leaves = HR_Leave::where('user_id',$emp_id)
                                    ->where(function($query){
                                        $query->where('start_date','>=',DB::raw('CURRENT_DATE'))
                                            ->orwhere('end_date','>=',DB::raw('CURRENT_DATE'));
                                    })
                                    ->select('start_date','end_date')
                                    ->get()->toArray();
            foreach($get_leaves as $k => $v){
                $start_date = $v['start_date'];
                $end_date = $v['end_date'];
                $diff = CustomHelpers::getDay($start_date,$end_date);
                if(strtotime($start_date) >= strtotime(date('Y-m-d'))){
                    array_push($leave_date,$start_date);
                }
                for($i = 0 ; $i < $diff ; $i++){
                    $this_date = date('Y-m-d',strtotime("+1 Day",strtotime($start_date)));
                    if(strtotime($this_date) >= strtotime(date('Y-m-d'))){
                        array_push($leave_date,$this_date);
                    }
                    $start_date = $this_date;
                }
            }
        }

        $api_data = Calling::leftJoin('users','calling.assigned_to','users.id')
                                ->leftJoin('store','calling.store_id','store.id')
                                ->where(function($query) use($leave_date){
                                    $query->whereNull('calling.next_call_date')
                                            ->orwhereNotIn('calling.next_call_date',$leave_date);
                                })
            ->select(
                'calling.id',
                'users.name as username',
                DB::raw('concat(store.name,"-",store.store_type) as storename'),
                'calling.type',
                'calling.call_type',
                'calling.next_call_date',
                'calling.remark',
                'calling.call_status',
                'calling.status',
                'calling.type'
            );
            if($auth_role != 'Superadmin'){
                $api_data = $api_data->where('assigned_to',DB::raw(Auth::id()));
            }
            if($tab == 'thankyou')
            {
                $api_data = $api_data->where(function($query) {
                    $query->where('calling.call_type','thankyou');
                    // ->where('calling.call_status','pending');
                    // ->where('calling.next_call_date','<=',date('Y-m-d'));
                });
            }
            if($tab == 'psf'){
                $api_data = $api_data->where(function($query) {
                    $query->where('calling.call_type','psf');
                    // ->where('calling.call_status','pending')
                    // ->where('calling.next_call_date','<=',date('Y-m-d'));

                });
            }
            if($tab == 'booking'){
                $api_data = $api_data->where(function($query) {
                    $query->where('calling.call_type','booking');
                    // ->where('calling.call_status','pending')
                    // ->where('calling.next_call_date','<=',date('Y-m-d'));

                });
            }
            if($tab == 'all_pending'){
                $api_data = $api_data->where(function($query) {
                    $query->where('calling.call_status','pending');
                    // ->where('calling.next_call_date','<=',date('Y-m-d'));
                })->orderBy('calling.call_type','asc');
            }

            if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('users.name','like',"%".$serach_value."%")
                    ->orwhere('store.name','like',"%".$serach_value."%")
                    ->orwhere('calling.type','like',"%".$serach_value."%")
                    ->orwhere('calling.call_status','like',"%".$serach_value."%")   
                    ;
                });
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                'users.name',
                'store.name',
                'calling.type',
                'calling.call_status',
                'calling.type',
                'calling.next_call_date',
                'calling.remark'
                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                 $api_data->orderBy('calling.id','desc')->orderBy('calling.type');      
        
        $count = $api_data->count();
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return response()->json($array);
    }
    public function callLogAssign(Request $request){
        try{
            $call_ids = $request->input('assign_ids');
            if(!isset($call_ids[0])){   
                return back()->with('error','Select Minimum One Row.');
            }
            $user = $request->input('user');
            if($user <= 0 || empty($user)){   
                return back()->with('error','Required to Select User.');
            }
            $update_call_assign = Calling::whereIn('id',$call_ids)
                                        ->update(
                                            [
                                                'assigned_to' =>  $user,
                                                'assigned_by'   =>  Auth::id()
                                            ]
                                        );
            if(!$update_call_assign)
            {
                return back()->with('SomeThing Went Wrong');
            }
        }
        catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return back()->with('error','Something went wrong '.$ex->getMessage())->withInput();
        }
        /* Add action Log */
        CustomHelpers::userActionLog($action='Call Data Assign',0,0);
        DB::commit();
        return back()->with('success','Successfully Assigned.')->withInput();
    }

    public function callList_view(Request $request) {
        $id = $request->input('id');
        
        $call_data = Calling::where('id',$id)->first();
        if(!isset($call_data->id)){
            return back()->with('error','Call Record Not Found.');
        }
        if(Auth::user()->role != 'Superadmin'){
            if($call_data->assigned_to != Auth::id()){
                return back()->with('error','You are not authorized for this call.');
            }
        }
        if($call_data['type'] == 'sale_booking') {
            $calling = Calling::leftJoin("booking",function($join) use($id){
                $join->on("calling.type_id","=","booking.id")
                ->where('booking.status','<>','cancelled');
            })
            ->leftJoin("users",function($join){
                $join->on("calling.assigned_to","=","users.id");
            })
            ->leftJoin("store",function($join){
                $join->on("calling.store_id","=","store.id");
            })
            ->leftJoin("payment",function($join){
                $join->on("payment.booking_id","=","booking.id")
                ->where('payment.type','booking')
                ->whereNull('payment.sale_id')
                ->where('payment.status','received')
                ->groupBy('booking.id');
            })
            ->select("calling.*","booking.booking_number","booking.status","booking.name as customer_name","booking.mobile","users.name as username",'users.role',"store.name as store_name",DB::raw('sum(payment.amount) as received_amt'),
            DB::raw("(select sum(amount) from payment where payment.type = 'booking' and payment.sale_id is null and payment.booking_id = booking.id) as total_amt"),
            DB::raw("DATEDIFF(CURRENT_DATE,calling.dnd_date) AS dnd_day")
            )
            ->where('calling.id',$id)
            ->get()
            ->first();

        }if ($call_data['type'] == 'sale') {

            $calling = Calling::select("calling.*","sale.sale_no","sale.status","customer.id as cust_id","customer.name as customer_name","customer.mobile","users.name as username",'users.role',"store.name as store_name",'rto.registration_number','rto.rc_number','rto_summary.numberPlateStatus','rto_summary.numberPlateDeliveryDate','rto_summary.rcStatus','rto_summary.rcDeliveryDate','rto.id as rto_id','rto_summary.id as rto_summary_id')
                    ->leftJoin("sale",function($join) use($id){
                        $join->on("calling.type_id","=","sale.id")
                        ->where('sale.status','<>','cancelled');
                    })
                    ->leftJoin("rto",function($join) use($id){
                        $join->on("sale.id","=","rto.sale_id");
                    })
                    ->leftJoin("rto_summary",function($join) use($id){
                        $join->on("rto_summary.rto_id","=","rto.id")
                        ->where('rto_summary.currentStatus',1);
                    })
                    ->leftJoin("users",function($join){
                        $join->on("calling.assigned_to","=","users.id");
                    })
                    ->leftJoin("store",function($join){
                        $join->on("calling.store_id","=","store.id");
                    })
                    ->leftJoin("customer",function($join){
                        $join->on("sale.customer_id","=","customer.id");
                    })
                    ->where('calling.id',$id)
                    ->get()
                    ->first();
        }
        if ($call_data['type'] == 'bestdeal') {

            $calling = Calling::select("calling.*","sale.sale_no","sale.status","customer.id as cust_id","customer.name as customer_name","customer.mobile","users.name as username",'users.role',"store.name as store_name",'rto.registration_number','rto.rc_number','rto_summary.numberPlateStatus','rto_summary.numberPlateDeliveryDate','rto_summary.rcStatus','rto_summary.rcDeliveryDate','rto.id as rto_id','rto_summary.id as rto_summary_id')
                    ->leftJoin("best_deal_sale",function($join) use($id){
                        $join->on("calling.type_id","=","best_deal_sale.sale_id")
                        ->where('best_deal_sale.status','<>','Cancel');
                    })
                    ->leftJoin("sale",function($join) use($id){
                        $join->on("best_deal_sale.sale_id","=","sale.id")
                        ->where('sale.status','<>','cancelled');
                    })
                    ->leftJoin("rto",function($join) use($id){
                        $join->on("sale.id","=","rto.sale_id");
                    })
                    ->leftJoin("rto_summary",function($join) use($id){
                        $join->on("rto_summary.rto_id","=","rto.id")
                        ->where('rto_summary.currentStatus',1);
                    })
                    ->leftJoin("users",function($join){
                        $join->on("calling.assigned_to","=","users.id");
                    })
                    ->leftJoin("store",function($join){
                        $join->on("calling.store_id","=","store.id");
                    })
                    ->leftJoin("customer",function($join){
                        $join->on("sale.customer_id","=","customer.id");
                    })
                    ->where('calling.id',$id)
                    ->get()
                    ->first();
        }
        if ($call_data['type'] == 'service_booking') {
            $calling = Calling::leftJoin("service_booking",function($join) use($id){
                $join->on("calling.type_id","=","service_booking.id")
                ->where('service_booking.status','Booked');
            })
            ->leftJoin("users",function($join){
                $join->on("calling.assigned_to","=","users.id");
            })
            ->leftJoin("store",function($join){
                $join->on("calling.store_id","=","store.id");
            })
            ->leftJoin("job_card",function($join){
                $join->on("job_card.id","=","service_booking.job_card_id");
            })
            ->select("calling.*","service_booking.status","service_booking.name as customer_name","service_booking.mobile","users.name as username",'users.role',"store.name as store_name",'job_card.in_time','job_card.out_time',DB::raw("DATEDIFF(CURRENT_DATE,calling.dnd_date) AS dnd_day")
            )
            ->where('calling.id',$id)
            ->get()
            ->first();
        }if($call_data['type'] == 'service'){
              
            $calling = Calling::select("calling.*","customer.name as customer_name","customer.id as cust_id",
                  "customer.mobile","users.name as username",'users.role',"store.name as store_name")
                ->leftJoin("service",function($join) use($id){
                    $join->on("calling.type_id","=","service.id");
                })
                ->leftJoin("users",function($join){
                    $join->on("calling.assigned_to","=","users.id");
                })
                ->leftJoin("store",function($join){
                    $join->on("calling.store_id","=","store.id");
                })
                ->leftJoin("customer",function($join){
                    $join->on("service.customer_id","=","customer.id");
                })
                ->where('calling.id',$id)
                ->get()
                ->first();

            $job_card = Calling::Join('service','service.id','calling.type_id')
                ->join('job_card','job_card.service_id','service.id')
                ->select('job_card.id','job_card.tag')
                ->whereRaw('Date(job_card.service_date) > current_date ')
                ->where('job_card.status','Booked')
                ->get()->first();

            $jobcard_type = UserMaster::where('type','job_card_type')
                            ->whereIn('key',array('Free','Paid'))
                            ->select('key','value')->get();

            $store = Store::whereIn('id',CustomHelpers::user_store())
                            ->select('id',DB::raw('concat(store.name,"-",store.store_type) as name'))->get();
        
        }

        $csm_call_type =['thankyou'=>4,'psf'=>5,'booking'=>6]; 
        $status_allow_q = Settings::where('name','ManuallyCallAllowed')
                                ->whereRaw("FIND_IN_SET(".Auth::id().",value)")
                                ->pluck('value')->toArray();
        if(count($status_allow_q) > 0){
            $call_status = CallStatusMaster::select('key_name','value')
                                        ->whereRaw("FIND_IN_SET(".$csm_call_type[$calling['call_type']].",type)")
                                        ->get()->toArray();
        }else{
            $call_status = CallStatusMaster::where('manual_allow',0)
                                        ->whereRaw("FIND_IN_SET(".$csm_call_type[$calling['call_type']].",type)")
                                        ->select('key_name','value')->get()->toArray();
        }

        $timeline_cd = CallData::leftJoin('call_data_details',function($join){
                    $join->on('call_data_details.call_data_id','=','call_data.id')
                            ->whereRaw('call_data_details.id IN(select max(cdd.id) from call_data_details cdd where 
                                        cdd.call_data_id = call_data.id group By type)');
                })
                ->leftJoin('call_data_record',function($join){
                    $join->on('call_data_record.call_data_id','=','call_data.id')
                            ->whereRaw('call_data_record.id = (select max(cdr.id) from call_data_record cdr where 
                            cdr.call_data_id = call_data.id and cdr.call_data_details_id = call_data_details.id group by cdr.call_data_details_id)');
                })
                ->leftJoin('users','call_data_record.updated_by','users.id')
                ->whereNotNull('call_data_record.id')
                ->where("call_data.mobile",$calling['mobile'])
                ->select(
                    'users.name as updated_by',
                    // 'call_data.call_type',
                    DB::raw('(Case when call_data_details.type = 1 then "Enquiry" 
                        when call_data_details.type = 2 then "Insurance"
                        when call_data_details.type = 3 then "Service Enquiry" End)as call_type'),
                    'call_data_record.call_date',
                    'call_data_record.call_status',
                    'call_data_record.remark',
                    'call_data_record.next_call_date as summary_next_date',
                    'call_data.call_type as cd_type'
                )->get()->toArray();

        
        $inner_query1 = "select calling.type AS cd_type,
            calling.call_type,
            calling_summary.next_call_date AS summary_next_date,
            calling_summary.call_date,
            calling_summary.remark,
            calling_summary.call_status,
            users.name AS updated_by,
            booking.mobile AS booking_mobile,
            service_booking.mobile AS sb_mobile,
            sale.customer_id AS sale_cust_id,
            service.customer_id AS service_cust_id,
            calling_summary.id as csid,
            calling.id as calling_id,
            max(calling_summary.id) as maxid
            FROM calling 
            LEFT JOIN calling_summary ON calling.id = calling_summary.calling_id AND calling_summary.id =(
                SELECT
                MAX(cs.id)
                FROM
                calling_summary cs
                WHERE
                cs.calling_id = calling.id
                )
            LEFT JOIN sale ON sale.id = calling.type_id AND calling.type IN('sale', 'bestdeal')
            LEFT JOIN service ON service.id = calling.type_id AND calling.type = 'service'
            LEFT JOIN booking ON booking.id = calling.type_id AND calling.type = 'sale_booking'
            LEFT JOIN service_booking ON service_booking.id = calling.type_id AND calling.type = 'service_booking'
            LEFT JOIN users ON calling_summary.updated_by = users.id ";

        
        if(isset($calling['cust_id'])){

            $inner_query2 = $inner_query1." LEFT JOIN customer AS booking_customer ON booking_customer.mobile = booking.mobile LEFT JOIN customer AS serviceBooking_customer ON serviceBooking_customer.mobile = service_booking.mobile WHERE calling_summary.id IS NOT NULL AND(sale.customer_id = ".$calling["cust_id"]." OR service.customer_id = ".$calling["cust_id"]." OR booking.mobile = ".$calling["mobile"]." OR service_booking.mobile = ".$calling["mobile"].")
                GROUP by calling.call_type,calling_summary.id,calling.type 
                ORDER BY calling_summary.id desc,maxid desc ";
            
        
        }else if(isset($calling['mobile'])){

            $inner_query2 = $inner_query1." LEFT JOIN customer AS sale_customer ON sale_customer.id = sale.customer_id 
                LEFT JOIN customer AS service_customer ON service_customer.id = service.customer_id

                WHERE calling_summary.id IS NOT NULL AND(sale_customer.mobile = ".$calling["mobile"]." OR service_customer.mobile = ".$calling["mobile"]." OR booking.mobile = ".$calling["mobile"]." OR service_booking.mobile = ".$calling["mobile"].")
                GROUP by calling.call_type,calling_summary.id,calling.type 
                ORDER BY calling_summary.id desc,maxid desc ";
            
        }
        $timeline_call_obj =DB::select("select t1.* from (".$inner_query2.") as t1 group by t1.call_type,t1.cd_type");
        $timeline_call = json_decode(json_encode($timeline_call_obj),true);
        
        $timeline = array_merge($timeline_call,$timeline_cd);
        
        if(!empty($calling['id']))
        {
            $allLastCallData = CallingSummery::where('calling_id',$calling['id'])->orderBy('created_at','desc')->get();
        }else{
            $allLastCallData = array();
        }
        $holiday_list = CustomHelpers::getAllHolidays();
        $dnd_setting = CallSetting::where('type',$call_data->call_type)
                        ->first();
        $data = [
            'call_id'=>$calling['id'],
            'calling' => $calling,
            'allLastCallData' => $allLastCallData,
            'forbidden' =>  $holiday_list,
            'dnd_setting'   =>  $dnd_setting,
            'timeline' =>$timeline,
            'call_status'=>$call_status,
            'layout' => 'layouts.main'
        ]; 

        if($call_data['type'] == 'service'){
            $data2 = ['store' =>  $store,
            'job_card'  =>  (($job_card)?$job_card:array()),
            'jobcard_type'  =>  $jobcard_type];

            $data = array_merge($data,$data2);
        }
        return view('admin.call.calling_view',$data);
    }

    public function callList_ViewRcdelivery(Request $request,$id)
    {
           // $id = $request->input('id');
      DB::enableQueryLog();
        $call_data = Calling::where('id',$id)->select()->first();
         if ($call_data['type'] == 'booking') {
              $calling = Calling::leftJoin("booking",function($join) use($id){
            $join->on("calling.type_id","=","booking.id")
            ->where('booking.status','<>','cancelled');
        })
        ->leftJoin("users",function($join){
            $join->on("calling.assigned_to","=","users.id");
        })
        ->leftJoin("store",function($join){
            $join->on("calling.store_id","=","store.id");
        })
          ->leftJoin("payment",function($join){
            $join->on("payment.booking_id","=","booking.id")
            ->where('payment.type','booking')
            ->whereNull('payment.sale_id')
            ->where('payment.status','received')
            ->groupBy('booking.id');
        })
          ->select("calling.*","booking.booking_number","booking.status","booking.name as customer_name","booking.mobile","users.name as username",'users.role',"store.name as store_name",DB::raw('sum(payment.amount) as received_amt'),
            DB::raw("(select sum(amount) from payment where payment.type = 'booking' and payment.sale_id is null and payment.booking_id = booking.id) as total_amt")
            )
        ->where('calling.id',$id)
        ->get()
        ->first();

        }if ($call_data['type'] == 'sale') {
              $calling = Calling::select("calling.*","sale.sale_no","sale.status","customer.name as customer_name","customer.mobile","users.name as username",'users.role',"store.name as store_name",'rto.registration_number','rto.rc_number','rto_summary.numberPlateStatus','rto_summary.numberPlateDeliveryDate','rto_summary.rcStatus','rto_summary.rcDeliveryDate','rto.id as rto_id','rto_summary.id as rto_summary_id','product.model_name','sale_order.product_frame_number','customer.relation_type','sale.created_at as sale_date','sale.customer_pay_type','sale_finance_info.name as finance_name','customer.relation','sale.id as sale_id','rto_file_submission_details.created_at as rto_file_submission_date','rto.plate_receive_date')
        ->leftJoin("sale",function($join) use($id){
            $join->on("calling.type_id","=","sale.id")
            ->where('sale.status','<>','cancelled');
        })
        ->leftJoin("rto",function($join) use($id){
            $join->on("sale.id","=","rto.sale_id");
        })
        ->leftJoin("rto_file_submission_details",function($join) use($id){
            $join->on("rto.id","=","rto_file_submission_details.rto_id");
        })
        ->leftJoin("product",function($join) use($id){
            $join->on("sale.product_id","=","product.id");
        })
        ->leftJoin("sale_order",function($join) use($id){
            $join->on("sale.id","=","sale_order.sale_id");
        })
        ->leftJoin("sale_finance_info",function($join) use($id){
            $join->on("sale.id","=","sale_finance_info.sale_id");
        })
        ->leftJoin("rto_summary",function($join) use($id){
            $join->on("rto_summary.rto_id","=","rto.id")
            ->where('rto_summary.currentStatus',1);
        })
        ->leftJoin("users",function($join){
            $join->on("calling.assigned_to","=","users.id");
        })
        ->leftJoin("store",function($join){
            $join->on("calling.store_id","=","store.id");
        })
        ->leftJoin("customer",function($join){
            $join->on("sale.customer_id","=","customer.id");
        })
        ->where('calling.id',$id)
        ->get()
        ->first();
         }

         if(!empty($calling['id']))
              $job_card = Calling::leftJoin('service','calling.type_id','service.id')
                        ->leftjoin('job_card','job_card.service_id','service.id')
                        ->leftjoin('feedback','feedback.jobcard_id','job_card.id')
                         ->select('job_card.created_at as job_card_date','feedback.rating')
                        ->where('calling.id',$id)->orderBy('job_card.id','DESC')
                        ->get()->first();
            else{
                $job_card = array();
            }

        // print_r($calling);die();
        if(!empty($calling['id']))
        {
            $allLastCallData = CallingSummery::where('calling_id',$calling['id'])->orderBy('created_at','desc')->get();
        }else{
            $allLastCallData = array();
        }
        if(!empty($calling['id']))
        {
            $service_booking = ServiceBooking::where('call_type','CallLog')->where('call_id',$calling['id'])->orderBy('created_at','desc')->get()->first();
        }else{
            $service_booking = array();
        }
        if(!empty($calling->sale_id))
        {
             $store = Store::whereIn('id',CustomHelpers::user_store())
                    ->select('id')->get()->toArray();

             $accessories = OrderPendingModel::where('sale_id',$calling->sale_id)->select()->get()->first();
             $accessories_data=$accessories['accessories_id'];
             $accessories_id=explode(',',$accessories_data);
    
             if($accessories)
             {
                    $allPendingData = SaleAccessories::where('sale_accessories.sale_id',$calling->sale_id)->whereIn('sale_accessories.id',$accessories_id)->leftJoin('part_stock','sale_accessories.part_id','part_stock.part_id')
                        ->leftJoin('part',function($join){
                            $join->on('part.id','sale_accessories.part_id');
                        })
                       ->leftJoin("store",function($join) use($store){
                       $join->on('part_stock.store_id','store.id')
                         ->whereIn('part_stock.store_id',$store);
                       })
                        ->select('sale_accessories.id as sale_accessories_id',
                            'sale_accessories.sale_id',    
                            'sale_accessories.part_id',
                            'part_stock.quantity',
                            'part_stock.store_id',
                            DB::raw('IF(sale_accessories.part_id = 0, sale_accessories.accessories_desc,part.name) as accessories_name'),
                            DB::raw('IF(sale_accessories.part_id = 0, "1",sale_accessories.qty) as qty'), 
                            'sale_accessories.amount'
                        )
                        ->get();
             }
             else
             {
                  $allPendingData = array();
             }
    
        }else{
            $allPendingData = array();
        }
         // print_r($allPendingData);die();
        $data = [
            'call_id'=>$calling['id'],
            'calling' => $calling,
            'job_card'=>$job_card,
            'allLastCallData' => $allLastCallData,
            'allPendingData' => $allPendingData,
            'service_booking' =>$service_booking,
            'layout' => 'layouts.main'
        ]; 
        
        return view('admin.call.calling_view_rcdelivery',$data);
    }

    public function callLog_update(Request $request) {
        $this->validate($request,[
            // 'call_status'=>'required',
            'next_call_date'    =>  'required_unless:call_status,Closed',
            'remark'    =>  'required',
            'open_query'=> 'required_if:remain_open,1'
        ],[
            // 'call_status.required'=> 'This is required.', 
            'next_call_date.required_unless'=> 'This is required.', 
            'remark.required'=> 'This is required.',
            'open_query.required_if'=> 'This is required.'

        ]);

        $callStatus = $request->input('call_status');
        $next_call_date = $request->input('next_call_date');
        $remark = $request->input('remark');
        $type_id = $request->input('type_id');
        $type = $request->input('type');
        $call_type = $request->input('call_type');
        $id = $request->input('id');
        $next = CustomHelpers::showDate($next_call_date,'Y-m-d');
        $first_call = new DateTime(date('Y-m-d'));
        $next_call = new DateTime(CustomHelpers::showDate($next_call_date,'Y-m-d'));

        $calling_record =Calling::where('id',$id)->first();

        if(!isset($calling_record->id)){
            return back()->with('error','Call Log Not Found.')->withInput();
        }
        if($calling_record->call_status == 'done'){
            return back()->with('error',"This Call Log Already Completed. So, Couldn't Update It.")->withInput();
        }
        if($calling_record->assigned_to != Auth::id()){
            return back()->with('error',"You Are Not Authorized For This Call.")->withInput();
        }

        $remain_open = $request->input('remain_open');
        $open_query = $request->input('open_query');
        
        $diff = (strtotime($next) - strtotime(date('Y-m-d')))/ (60 * 60 * 24);
       

       $redirect_str = '/admin/call/log/view?id='.$id;
       // if($type == 'service')
       // {
       //      $redirect_str = '/admin/service/call/log/view?id='.$id;
       // }
       // else{
       //      $redirect_str = '/admin/call/log/view?id='.$id;
       // }
        
        DB::beginTransaction();
        try{
        if ($callStatus == 'Closed') {

            $updateData = [
                    'call_date' =>  date('Y-m-d'),
                    'status'   =>  $callStatus,
                    'call_status'   =>  'done',
                    'remark'    =>  $remark,
                    'next_call_date'    =>  CustomHelpers::showDate($next_call_date,'Y-m-d')

                ];

            $callSummary = Calling::where('id',$id)->update($updateData);
            $ins = [ 
                        'calling_id'   =>  $id,
                        'call_date'     =>  date('Y-m-d'),
                        'call_status'   =>  $callStatus,
                        'remark'    =>  $remark,
                        'updated_by' => Auth::id()
                    ];
            $callSummaryDet = CallingSummery::insertGetId($ins);

            if($call_type == 'psf' || $call_type == 'thankyou'){

                
                if($remain_open){
                    $insertData = [
                        'pid' => $id,
                        'store_id'=>$calling_record['store_id'],
                        'type'  =>  $type,
                        'type_id'   =>  $type_id,
                        'query' =>$open_query,
                        'next_call_date'    =>  CustomHelpers::showDate($next_call_date,'Y-m-d')
                    ];
                    $calltable = Calling::insertGetId($insertData);

                    if($calltable == 0)
                    {
                        DB::rollback();
                        return redirect($redirect_str)->with('error','Error, Remain Open Error Occurred');
                    }
                }
            }

              if($callSummary && $callSummaryDet)
                {
                    DB::commit();
                    return redirect($redirect_str)->with('success','Success, Call Successfully done');
                }
                else{
                    DB::rollback();
                    return redirect($redirect_str)->with('error','Error, Call UnSuccessfully Updated');

                } 

            

        }else{
            // if ($diff < 1) {
            //     return redirect($redirect_str)->with('error','Please select date after today  ')->withInput();
            // }else{
                $getPreCallData = Calling::where('id',$id)->first();
                // print_r($getPreCallData);die;
                if(empty($getPreCallData))
                {
                    $ins = [
                        'type'  =>  $type,
                        'type_id'   =>  $type_id,
                        'call_date'     =>  date('Y-m-d'),
                        'status'   =>  $callStatus,
                        'remark'    =>  $remark,
                        'next_call_date'    =>  CustomHelpers::showDate($next_call_date,'Y-m-d')
                    ]; 
                    $callSummary = Calling::insertGetId($ins);

                    $ins = [ 
                        'calling_id'   =>  $callSummary,
                        'call_date'     =>  date('Y-m-d'),
                        'call_status'   =>  $callStatus,
                        'remark'    =>  $remark,
                        'updated_by' => Auth::id(),
                        'next_call_date'    =>  CustomHelpers::showDate($next_call_date,'Y-m-d')
                    ];
                    $callSummaryDet = CallingSummery::insertGetId($ins);

                }
                else
                {
                    $call_id = $getPreCallData->id;
                    $ins = [
                        'calling_id'   =>  $call_id,
                        'call_date'     =>  date('Y-m-d'),
                        'call_status'   =>  $callStatus,
                        'remark'    =>  $remark,
                        'next_call_date'    =>  CustomHelpers::showDate($next_call_date,'Y-m-d'),
                        'updated_by' => Auth::id()
                    ];
                    $callSummaryDet = CallingSummery::insertGetId($ins);
                    $updateData = [
                        'call_date' =>  date('Y-m-d'),
                        'status'   =>  $callStatus,
                        'remark'    =>  $remark,
                        'next_call_date'    =>  CustomHelpers::showDate($next_call_date,'Y-m-d')
                    ];
                    $callSummary = Calling::where('id',$call_id)->update($updateData);
                }  
          
                if($callSummary && $callSummaryDet)
                {
                    DB::commit();
                    return redirect($redirect_str)->with('success','Success, Call Successfully Updated');
                }
                else{
                    DB::rollback();
                    return redirect($redirect_str)->with('error','Error, Call UnSuccessfully Updated');

                }
              }
            // }
        }
        catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return redirect($redirect_str)->with('error','Something went wrong '.$ex)->withInput();
        }
        // DB::commit();
        return redirect($redirect_str)->with('error','Something went wrong')->withInput();
    }

    public function nextCalllog(Request $request) {
        $assigned_to = $request->input('userid');
        $call_type = $request->input('call_type');
        $id = $request->input('id');
        $call_data = Calling::where('id',$id)->where('assigned_to',$assigned_to)->where('call_type', $call_type)->select()->first();
         if ($call_data['type'] == 'sale_booking') {
            $calling = Calling::select("calling.*","booking.booking_number","booking.status","booking.name as customer_name","booking.mobile","users.name as username",'users.role',"store.name as store_name")
            ->leftJoin("booking",function($join) use($assigned_to){
                $join->on("calling.type_id","=","booking.id")
                ->where('booking.status','<>','cancelled');
            })
            ->leftJoin("users",function($join){
                $join->on("calling.assigned_to","=","users.id");
            })
            ->leftJoin("store",function($join){
                $join->on("calling.store_id","=","store.id");
            })
            ->where('calling.assigned_to',$assigned_to)
            ->where('calling.call_type',$call_type)
            ->get();

        }if ($call_data['type'] == 'sale') {
              $calling = Calling::select("calling.*","sale.sale_no","sale.status","customer.name as customer_name","customer.mobile","users.name as username",'users.role',"store.name as store_name")
            ->leftJoin("sale",function($join) use($assigned_to){
                $join->on("calling.type_id","=","sale.id")
                ->where('sale.status','<>','cancelled');
            })
            ->leftJoin("users",function($join){
                $join->on("calling.assigned_to","=","users.id");
            })
            ->leftJoin("store",function($join){
                $join->on("calling.store_id","=","store.id");
            })
            ->leftJoin("customer",function($join){
                $join->on("sale.customer_id","=","customer.id");
            })
            ->where('calling.assigned_to',$assigned_to)
            ->where('calling.call_type',$call_type)
            ->get();

        }if ($call_data['type'] == 'bestdeal') {

            $calling = Calling::select("calling.*","sale.sale_no","sale.status","customer.id as cust_id","customer.name as customer_name","customer.mobile","users.name as username",'users.role',"store.name as store_name")
                    ->leftJoin("best_deal_sale",function($join) use($id){
                        $join->on("calling.type_id","=","best_deal_sale.sale_id")
                        ->where('best_deal_sale.status','<>','Cancel');
                    })
                    ->leftJoin("sale",function($join) use($id){
                        $join->on("best_deal_sale.sale_id","=","sale.id")
                        ->where('sale.status','<>','cancelled');
                    })
                    ->leftJoin("users",function($join){
                        $join->on("calling.assigned_to","=","users.id");
                    })
                    ->leftJoin("store",function($join){
                        $join->on("calling.store_id","=","store.id");
                    })
                    ->leftJoin("customer",function($join){
                        $join->on("sale.customer_id","=","customer.id");
                    })
                    ->where('calling.assigned_to',$assigned_to)
                    ->where('calling.call_type',$call_type)
                    ->get();
                    
        }
        if ($call_data['type'] == 'service_booking') {

            $calling = Calling::leftJoin("service_booking",function($join) use($id){
                $join->on("calling.type_id","=","service_booking.id")
                ->where('service_booking.status','Booked');
            })
            ->leftJoin("users",function($join){
                $join->on("calling.assigned_to","=","users.id");
            })
            ->leftJoin("store",function($join){
                $join->on("calling.store_id","=","store.id");
            })
            ->leftJoin("job_card",function($join){
                $join->on("job_card.id","=","service_booking.job_card_id");
            })
            ->select("calling.*","service_booking.status","service_booking.name as customer_name","service_booking.mobile","users.name as username",'users.role',"store.name as store_name")
            ->where('calling.assigned_to',$assigned_to)
            ->where('calling.call_type',$call_type)
            ->get();

        }if($call_data['type'] == 'service'){
              
            $calling = Calling::select("calling.*","customer.name as customer_name","customer.id as cust_id",
                  "customer.mobile","users.name as username",'users.role',"store.name as store_name")
                ->leftJoin("service",function($join) use($id){
                    $join->on("calling.type_id","=","service.id");
                })
                ->leftJoin("users",function($join){
                    $join->on("calling.assigned_to","=","users.id");
                })
                ->leftJoin("store",function($join){
                    $join->on("calling.store_id","=","store.id");
                })
                ->leftJoin("customer",function($join){
                    $join->on("service.customer_id","=","customer.id");
                })
                ->where('calling.assigned_to',$assigned_to)
                ->where('calling.call_type',$call_type)
                ->get();
                
        }
        
        $count = $calling->count();        
        if($count > 1){
            $calling = $calling->where('id','<>',$id)->first()->toArray();
            return redirect('/admin/call/log/view?id='.$calling['id'].'');
        }else{
            return back()->with('error',"Error, Couldn't find Call's for Next ");
        }
       
    }

    public function updateCallLogDelivery(Request $request) {
       //print_r($request->input());die();
        try{
            DB::beginTransaction();
            // print_r(DB::table('users')->where('id',Auth::id())->select('user_type')->first());die;
            $user = Users::where('id',Auth::id())->select('user_type','role')->first();
            $userType = $user->user_type;
            $userRole = $user->role;

            $saleId = $request->input('delivery_sale_id');
            $rtoId = $request->input('delivery_rto_id');
            $id = $request->input('id');
            $numberPlateDelivery = $request->input('numberPlateDelivery');
            $rcDelivery = $request->input('rcDelivery');
            if(!isset($rtoId)){
                return redirect('/admin/call/log/view?id='.$id.'')->with('error','RTO Not Created For This Sale.')->withInput();
            }
            $checkBoth = RtoModel::where('rto.id',$rtoId)->where('rto.sale_id',$saleId)
                        ->leftJoin('rto_summary',function($join) use($rtoId){
                            $join->on('rto_summary.rto_id',DB::raw($rtoId))
                                    ->where('rto_summary.currentStatus',1);
                        })
                        ->where(function($query) {
                            $query->whereNotNull('rto.registration_number')
                                    ->orwhereNotNull('rto.rc_number');
                        })
                        ->select('rto.id as rto_id','rto_summary.id as rto_summary_id','rto.registration_number','rto.rc_number','rto_summary.numberPlateStatus','rto_summary.rcStatus')->first();
            if(empty($checkBoth->rto_id) || empty($checkBoth->rto_summary_id))
            {
                return redirect('/admin/call/log/view?id='.$id.'')->with('error','This information not valid.')->withInput();
            }   
            $updateData = [];
            if($numberPlateDelivery == 'yes' && ($checkBoth->numberPlateStatus == 0 || $checkBoth->numberPlateStatus == 1))
            {
                $updateData['numberPlateStatus']    =   1;
            }
            elseif($numberPlateDelivery == 'no' ){
                if($checkBoth->numberPlateStatus == 1)
                {
                    if($userType != 'superadmin' && $userRole != 'Superadmin'){
                        return redirect('/admin/call/log/view?id='.$id.'')->with('error','Number Plate already delivered, if you want to change pls contact superadmin.')->withInput();
                    }
                }
                $updateData['numberPlateStatus']    =   0;
            }
            if($rcDelivery == 'yes' && ($checkBoth->rcStatus == 0 || $checkBoth->rcStatus == 1))
            {
                $updateData['rcStatus']   =   1;
            }
            elseif($rcDelivery == 'no'){
                if($checkBoth->rcStatus == 1)
                {
                    if($userType != 'superadmin' && $userRole != 'Superadmin'){
                        return redirect('/admin/call/log/view?id='.$id.'')->with('error','RC already delivered, if you want to change pls contact superadmin.')->withInput();
                    }
                }
                $updateData['rcStatus']    =   0;
            }

            //if delivery for rc 
            if($rcDelivery == 'yes')
            {
                //check RC correction Requested or not
                $checkRcCorrectionReq = RtoModel::where('rto.id',$rtoId)
                                        ->leftJoin('rc_correction_request',function($join){
                                            $join->on('rc_correction_request.rto_id','rto.id')
                                                    ->where('rc_correction_request.status','approved')
                                                    ->where('rc_correction_request.id',DB::raw('(select max(id) from rc_correction_request where rto_id = rto.id)'));
                                        })->select('rc_correction_request.id')->first();
                // print_r($checkRcCorrectionReq);die;
                if(!empty($checkRcCorrectionReq->id))
                {
                    //check RC 'correction request' payment paid or not, when RC Delivered to customer
                    $checkRcCorrection = RtoModel::where('rto.id',$rtoId)
                                            ->leftJoin('rc_correction_request',function($join) use($checkRcCorrectionReq){
                                                $join->on('rc_correction_request.rto_id','rto.id')
                                                        ->where('rc_correction_request.status','approved')
                                                        ->where('rc_correction_request.id',$checkRcCorrectionReq->id);

                                            })
                                            ->leftJoin('payment',function($join) use($checkRcCorrectionReq) {
                                                $join->on('payment.type_id',DB::raw($checkRcCorrectionReq->id))
                                                    ->where('payment.type','RcCorrection');
                                            })
                                            ->select('rto.id','rc_correction_request.payment_amount as correctionAmount',
                                                DB::raw('IFNULL(sum(payment.amount),0) as paidAmount'))
                                            ->first();
                    // print_r($checkRcCorrection);
                    if($checkRcCorrection->paidAmount != $checkRcCorrection->correctionAmount)
                    {
                        return redirect('/admin/call/log/view?id='.$id.'')->with('error','Amount Should be paid for RC Correction.')->withInput(); 
                    }
                }
            }

            if($updateData)
            {
                $updateRtoSummary = RtoSummary::where('rto_id',$rtoId)->whereRaw('currentStatus',1)->update($updateData);
            }
        }
        catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return redirect('/admin/call/log/view?id='.$id.'')->with('error','Something went wrong '.$ex)->withInput();
        }
        DB::commit();
        return redirect('/admin/call/log/view?id='.$id.'')->with('success','Successfully Delivered')->withInput();
    }

    //service psf call log view 
    
    public function service_callList_view(Request $request) {
        $id = $request->input('id');
      DB::enableQueryLog();
        $call_data = Calling::where('id',$id)->select()->first();

        $calling = Calling::select("calling.*","customer.name as customer_name",
              "customer.mobile","users.name as username",'users.role',"store.name as store_name")
        ->leftJoin("service",function($join) use($id){
            $join->on("calling.type_id","=","service.id");
        })
        ->leftJoin("users",function($join){
            $join->on("calling.assigned_to","=","users.id");
        })
        ->leftJoin("store",function($join){
            $join->on("calling.store_id","=","store.id");
        })
        ->leftJoin("customer",function($join){
            $join->on("service.customer_id","=","customer.id");
        })
        // ->whereRaw('find_in_set(calling.store_id,users.store_id)')
        // ->where('users.id',DB::raw(Auth::id()))
        ->where('calling.id',$id)
        ->get()
        ->first();
         
        if(!isset($calling['id']))
        {
            return back()->with('error','Error, Data Not Found');
        }
        // print_r($calling);die();
        if(!empty($calling['id']))
        {
            $allLastCallData = CallingSummery::where('calling_id',$calling['id'])->orderBy('created_at','desc')->get();
        }else{
            $allLastCallData = array();
        }
      //  print_r($allLastCallData);die();
      $job_card = Calling::Join('service','service.id','calling.type_id')
                    ->join('job_card','job_card.service_id','service.id')
                    ->select('job_card.id','job_card.tag')
                    ->whereRaw('Date(job_card.service_date) > current_date ')
                    ->where('job_card.status','Booked')
                    ->get()->first();
        // print_r($job_card);die;

      $jobcard_type = UserMaster::where('type','job_card_type')
                        ->whereIn('key',array('Free','Paid'))
                        ->select('key','value')->get();
        $store = Store::whereIn('id',CustomHelpers::user_store())
                        ->select('id',DB::raw('concat(store.name,"-",store.store_type) as name'))->get();
    

        $data = [
            'calling' => $calling,
            'store' =>  $store,
            'job_card'  =>  (($job_card)?$job_card:array()),
            'jobcard_type'  =>  $jobcard_type,
            'allLastCallData' => $allLastCallData,
            'layout' => 'layouts.main'
        ]; 
        
        return view('admin.call.calling_service_psf_view',$data);
    }

    public function service_call_book(Request $request)
    {
        $this->validate($request,[
            'service_type'=>'required',
            'service_date'    =>  'required',
            'service_time'    =>  'required',
            'pick_drop'    =>  'required',
            'pickup_drop.*'    =>  'required_unless:pick_up,yes'
        ],[
            'service_type.required'=> 'This is required.', 
            'service_date.required_unless'=> 'This is required.', 
            'service_time.required'=> 'This is required.',
            'pick_drop.required'=> 'This is required.'
        ]);
        try
        {
            $service_id  = $request->input('type_id');
            $call_type  = $request->input('call_type');
            $call_id  = $request->input('id');

            $service_type  = $request->input('service_type');
            $service_date  = $request->input('service_date');
            $service_time  = $request->input('service_time');
            $pick_drop  = $request->input('pick_drop');
            $pickup_drop  = $request->input('pickup_drop');
            $store_id  = $request->input('store_name');
            // print_r($pickup_drop);die;

            DB::beginTransaction();

            $job_card_data = [
                'service_id'    =>  $service_id,
                'store_id'  =>  $store_id,
                'job_card_type' =>  $service_type,
                'service_date'  =>  $service_date,
                'status'    =>  'Booked'
            ];
            $job_card_id = Job_card::insertGetId($job_card_data);
            if($pick_drop == 'yes')
            {
                $pickup_data = [
                    'job_card_id'   =>  $job_card_id,
                    'type'  =>  join(',',$pickup_drop)
                ];
                $pickup_insert = Pickup::insertGetId($pickup_data);
            }
            // bay allocation
            $duration = 0;
            if($service_type == 'Free')
            {
                $duration = 45;
            }
            else{
                $duration = 60;
            }
            $service_time  = str_replace(' ','',$service_time);
            $start_time =  $service_date.' '.$service_time;
            $interval  = CustomHelpers::min_duration();
            $stime = new DateTime($start_time);
            $r_stime = CustomHelpers::roundToNearestMinuteInterval($stime,$interval);
            $start_time = date('Y-m-d H:i',strtotime('+1 minutes',strtotime($r_stime->format('Y-m-d H:i'))));
            
            $end = date('Y-m-d H:i',strtotime('+'.$duration.' minutes', strtotime($start_time)));
            $etime = new DateTime($end);
            $r_etime = CustomHelpers::roundToNearestMinuteInterval($etime,$interval);
            $end_time = $r_etime->format('Y-m-d H:i');

            $duration = date('H:i',strtotime('+'.$duration.' minutes',strtotime('00:00:00')));
            // echo $start_time.'/'.$duration;die;
            $flag = 0;
            $service = new Service();
            $fix_alloc = $service->findExactSlotBay([],$start_time,$end_time,$job_card_id,$service_date,$store_id);
            // $fix_alloc = 's';
            if($fix_alloc == 'success')
            {
                $flag = 1;
            }
            else{
                $dynamic_alloc = $this->dynamic_alloc($job_card_id,$duration,$service_date,$service_date);
                if($dynamic_alloc == 'success')
                {
                    $flag = 1;
                }
            }

            
            if($flag == 0)
            {
                DB::rollback();
                return redirect('/admin/call/log/view?id='.$call_id)->with('error','Error, bay time is not Free.');
            }
        }
        catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return redirect('/admin/call/log/view?id='.$call_id)->with('error',"something wen't wrong ".$ex->getMessage());
        }
        DB::commit();
        return redirect('/admin/call/log/view?id='.$call_id)->with('success','Successfully Booked');

    }
    public function push_calldata_notification(Request $request){

        $user_id = Auth::user()->id;
        $userdata = Users::where("id",$user_id)->get()->first();
        $cal_log_id = $request->input("call_id");
        $cdd_id = $request->input("call_dd_id");
        $caller_name = $request->input("call_name");
        $caller_no = $request->input("call_number");
        $web_token = $request->input('TokenElem');
        // print_r($request->input("call_number"));die;
        $upd_user_web_token = Users::where("id",$user_id)->update([
            'web_token'=>$web_token
        ]);

        $call_detail= CallDataDetails::where('call_data_details.id',$cdd_id)
                                ->where('call_data_details.assigned_to',Auth::id())
                                ->whereNotIn('call_data_details.query_status',['Closed'])
                                ->get()->toArray();
        
        $call_data =array();
        if(count($call_detail)>0){
            if($userdata->firebase_token != null && $userdata->firebase_token != ""){
                $notify_msg = 'Call to '.$caller_name;

                $notification = array
                (
                'title' => 'Calling',
                'body' => $notify_msg,
                'click_action' => ".MainActivity"
                );
                $data = array
                (
                    "web_token" => $web_token,
                    "body" => "Calling",
                    "title" => $caller_no,
                    "content_available" => true,
                    "priority" => "high",
                    "recievername" => $caller_name,
                    "phonenumber" => $caller_no,
                    "notificationType"  => "calling"
                );
                $token = $userdata->firebase_token;
                $send_notification = FirebaseNotification::sendNotfication($notification,$data,$token);
                if($send_notification['failure'] == 1){
                    $call_data['status'] = 'error';
                    $call_data['message'] = $send_notification['results'][0]['error'];
                    $call_data['result'] = $send_notification;
                    return response()->json($call_data,401);
                }
                $call_data['status'] = 'success';
                $call_data['message'] = 'Calling....';
                $call_data['result'] = $send_notification;

                return response()->json($call_data);
            }else{
                $call_data['status'] = 'error';
                $call_data['message'] = 'You are not Login in Mobile.';
                return response()->json($call_data,401);
            }
        }else{
                $call_data['status'] = 'error';
                $call_data['message'] = 'You are not Authorized for this call.';
                return response()->json($call_data,401);
        }
        

    }

    public function push_calling_notification(Request $request){
        $user_id = Auth::user()->id;
        $userdata = Users::where("id",$user_id)->get()->first();
        $cal_log_id = $request->input("call_id");
        $caller_name = $request->input("call_name");
        $caller_no = $request->input("call_number");
        $web_token = $request->input('TokenElem');
        // print_r($request->input("call_number"));die;
        $upd_user_web_token = Users::where("id",$user_id)->update([
            'web_token'=>$web_token
        ]);

        $calling_detail= Calling::where('calling.id',$cal_log_id)
                                ->where('calling.assigned_to',Auth::id())
                                ->whereNotIn('calling.assigned_to',['done'])
                                ->get()->toArray();
        if(count($calling_detail) > 0){
            $call_data =array();
            if($userdata->firebase_token != null && $userdata->firebase_token != ""){
                $notify_msg = 'Call to '.$caller_name;

                $notification = array
                (
                'title' => 'Calling',
                'body' => $notify_msg,
                'click_action' => ".MainActivity"
                );
                $data = array
                (
                    "web_token" => $web_token,
                    "body" => "Calling",
                    "title" => $caller_no,
                    "content_available" => true,
                    "priority" => "high",
                    "recievername" => $caller_name,
                    "phonenumber" => $caller_no,
                    "notificationType"  => "calling"
                );
                $token = $userdata->firebase_token;
                $send_notification = FirebaseNotification::sendNotfication($notification,$data,$token);
                if($send_notification['failure'] == 1){
                    $call_data['status'] = 'error';
                    $call_data['message'] = $send_notification['results'][0]['error'];
                    $call_data['result'] = $send_notification;
                    return response()->json($call_data,401);
                }
                $call_data['status'] = 'success';
                $call_data['message'] = 'Calling....';
                $call_data['result'] = $send_notification;

                return response()->json($call_data);
            }else{
                $call_data['status'] = 'error';
                $call_data['message'] = 'You are not Login in Mobile.';
                return response()->json($call_data,401);
            }  
        }else{
            $call_data['status'] = 'error';
            $call_data['message'] = 'You are not Authorized for this call.';
            return response()->json($call_data,401);
        }
        

    }

    public function callLog_auto_update(Request $request) {
        $this->validate($request,[
            'call_status'=>'required',
            'next_call_date'    =>  'required_unless:call_status,Closed',
            'remark'    =>  'required'
        ],[
            'call_status.required'=> 'This is required.', 
            'next_call_date.required_unless'=> 'This is required.', 
            'remark.required'=> 'This is required.'
        ]);

        $callStatus = $request->input('call_status');
        $next_call_date = $request->input('next_call_date');
        $remark = $request->input('remark');
        $type_id = $request->input('type_id');
        $type = $request->input('type');
        $call_type = $request->input('call_type');
        $id = $request->input('id');
        $next = CustomHelpers::showDate($next_call_date,'Y-m-d');
        
        $remain_open = $request->input('remain_open');
        $open_query = $request->input('open_query');


        try{
            $auth_id = Auth::id();
            DB::beginTransaction();
            $getPreCallData = Calling::where('id',$id)->first();
                
            if(!isset($getPreCallData->id))
            {
                return response()->json(['message' => 'Call Record Not Found.'],401);
            }
            else
            {
                $call_id = $getPreCallData->id;
                // print_r($call_id);die();
                $ins = [
                    'calling_id'   =>  $call_id,
                    'call_date'     =>  date('Y-m-d'),
                    'call_status'   =>  $callStatus,
                    'remark'    =>  $remark,
                    'next_call_date'    =>  CustomHelpers::showDate($next_call_date,'Y-m-d')
                ];
                $callSummaryDet = CallingSummery::insertGetId($ins);
                $updateData = [
                    'call_date' =>  date('Y-m-d'),
                    'status'   =>  $callStatus,
                    'remark'    =>  $remark,
                    'next_call_date'    =>  CustomHelpers::showDate($next_call_date,'Y-m-d')
                ];
                $callSummary = Calling::where('id',$call_id)->update($updateData);

                if($call_type == 'psf' || $call_type == 'thankyou'){

                    if($remain_open){
                        $insertData = [
                            'pid' => $id,
                            'store_id'=>$getPreCallData['store_id'],
                            'type'  =>  $type,
                            'type_id'   =>  $type_id,
                            'query' =>$open_query,
                            'next_call_date'    =>  CustomHelpers::showDate($next_call_date,'Y-m-d')
                        ];
                        $calltable = Calling::insertGetId($insertData);

                        if($calltable == 0)
                        {
                            DB::rollback();
                            return response()->json(['message' => 'Error Occured To Remain Open The Call Log'],401);
                        }
                    }
                }

                if(!$callSummaryDet){
                    DB::rollback();
                    return response()->json(['message' => 'Call Record Not Updated.'],401);
                }
            } 
            
            $get_redirection = Calling::where('assigned_to',$auth_id)
                                        ->whereNotIn('id',[$id])
                                        ->where('call_status','pending')
                                        // ->where('type',$type)
                                        ->where('call_type',$getPreCallData['call_type'])
                                        ->where('store_id',$getPreCallData['store_id'])
                                        ->whereRaw('next_call_date <= CURRENT_DATE')
                                        ->orderBy('updated_at','asc')
                                        ->orderBy('call_type','asc')
                                        ->first();
            if(isset($get_redirection->id)){
                $redirection_data = [
                    'call_id' => $get_redirection->id
                ];
            }else{
                $redirection_data = [];
            }
        }
        catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return response()->json(['message' => 'Something went wrong. '.$ex->getMessage()],401);
        }
        DB::commit();
        $return_data = [
            'status'    =>  true,
            'redirect'  =>  $redirection_data,
            'message'   =>  'Successfully Updated.'
        ];
        return response()->json($return_data);
    }
    
    public function call_RcdeliveryServiceBook(Request $request){

        $call_id = $request->input('call_id');
        $name = $request->input('name');
        $mobile = $request->input('mobile');

        if(!$name){
             return 'Enter name .';
        }else if(!$mobile){
             return 'Enter mobile no .';
        }
        else{
            
            $service_booking = ServiceBooking::where('call_id',$call_id)->where('call_type','CallLog')
                                ->orderBy('created_at','desc')->get()->first();

            if($service_booking)
            {
                 return 'Service already Booked';

            }else{
                    
               $servicedata = ServiceBooking::insert(
                array(
                array(
                    'call_id' => $call_id,
                    'name'=>$name,
                    'status'=>'Booked',
                    'mobile'=>$mobile
                ),
                ));

               if($servicedata == NULL) {
                return 'error';
            } 
            else{                
                return 'success';               
            }

         }

        }

    }   

    public function callDataImport(Request $request){

        $call_type = [
            'Enquiry'   => 'Enquiry',
            'Insurance' =>  'Insurance'  
        ];

        $data = [
                    'layout' => 'layouts.main',
                    'call_type' =>  $call_type
                ];
        return view('admin.call.callDataImport',$data);
    }
    public function callDataImportDb(Request $request){
        
        try {
            $validator = Validator::make($request->all(),[
                'call_type'   =>  'required',
                'excel'   =>  'required'
            ],
            [
                'call_type.required'  =>  'This Field is required',
                'excel.required'  =>  'This Field is required'
            ]);
            $allowed_extension = array('xls','xlsx','xlt','xltm','xltx','xlsm');
            $validator->sometimes('excel', 'mimes:xls,xlsx,xlt,xltm,xltx,xlsm', function ($input) use($allowed_extension) {
                $extension="";
                if(isset($input->excel))
                $extension = $input->excel->getClientOriginalExtension();
                if(in_array($extension,$allowed_extension))
                    return false;
                else
                    return true;
            });

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            $table = ['call_data'];
            CustomHelpers::resetIncrement($table);
            DB::beginTransaction();
            if($request->file('excel'))
            {
                $path = $request->file('excel');      
                
                $data = Excel::toArray(new Import(),$path);
                if(count($data) > 0 && $data)
                {
                    $call_type = $request->input('call_type');
                    $import = $this->call_data_importing($data,$call_type);
                    if($import[0] != 'success')
                    {
                        return back()->with('error',$import[0])->withInput();
                    }
                }
                else{
                    return back()->with('error','Error, Check your file should not be empty!!')->withInput();
                }
            }
            else{
                return back()->with('error','Error, some error occurred Please Try again!!')->withInput();
            }
           
        }  catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return back()->with('error','some error occurred'.$ex->getMessage());
        }
        /* Add action Log */
        CustomHelpers::userActionLog($action='Call Data Import',0,0);
        DB::commit();
        return back()->with('success','Successfully Import Data Updated.');

    }

    public function call_data_importing($data,$call_type)
    {
        if (count($data) > 0 && $data) {
            $header = 0;
            $error = '';
            $success = '';
            $source_master = Master::where('type','call_source')
                                ->pluck('key')->toArray();
            foreach($data as $key => $value) {
                
                if (isset($value[0])) {
                    $accept_null = ['Enquiry'];
                    if(in_array($call_type,$accept_null)){
                        $optional_col = ['Date of Sale','Model','Frame #','Locality','City','Query','Campaign'];//'Model','Locality','City'
                        $column_name_format = array('Name','Mobile #','Source');
                    }else{
                        $optional_col = ['Date of Sale','Model','Locality','City','Query','Campaign'];
                        $column_name_format = array('Name','Mobile #','Frame #','Source');
                        //'Date of Sale','Model','Locality','City'
                    }
                    $productController = new Product();
                    $out = $this->validate_excel_format($value[0], $column_name_format,$optional_col);
                    $head = $out['header'];
                    
                    
                    if ($out['column_name_err'] != 0) {
                        $error = $error.$out['error'];
                        return [$error];
                    }
                    $cn = 0;
                    if (count($value) > 1) {
                        foreach($value as $in => $row) {
                            //--------------  trim() ---------------------
                            if($in > 0){
                                $data = [];$other_data = [];$all_data = [];
                                $data = [
                                    'name' => trim($value[$in][$head['Name']]),
                                    'mobile' => trim($value[$in][$head['Mobile #']]),
                                    'source' => trim($value[$in][$head['Source']])
                                ]; 
                                $source_match = 0;
                                foreach($source_master as $ind => $val){
                                    if(strcasecmp($data['source'],$val) == 0){
                                        $source_match = 1;
                                    }
                                }
                                $other_data = [
                                    'frame' => isset($head['Frame #']) ?trim($value[$in][$head['Frame #']]) : ''
                                    // 'date_of_sale' => isset($head['Date of Sale']) ? trim($value[$in][$head['Date of Sale']]) : '',

                                    // 'model' => isset($head['Model']) ? trim($value[$in][$head['Model']]) : '',
                                    // 'locality' => isset($head['Locality']) ? trim($value[$in][$head['Locality']]) : '',
                                    // 'city' => isset($head['City']) ? trim($value[$in][$head['City']]) : ''
                                ];
                                if(isset($head['Date of Sale'])){
                                   if(isset($value[$in][$head['Date of Sale']])){
                                        $add =['date_of_sale' =>trim($value[$in][$head['Date of Sale']])];
                                        if($add['date_of_sale'] !=''){
                                            $other_data = array_merge($other_data,$add);
                                        }
                                    } 
                                }
                                if(isset($head['Model'])){
                                    if(isset($value[$in][$head['Model']])){
                                        $add =['model' =>trim($value[$in][$head['Model']])];
                                        if($add['model'] !=''){
                                            $other_data = array_merge($other_data,$add);
                                        }
                                    }
                                }
                                if(isset($head['Locality'])){    
                                    if(isset($value[$in][$head['Locality']])){
                                        $add =['locality' =>trim($value[$in][$head['Locality']])];
                                        if($add['locality'] !=''){
                                            $other_data = array_merge($other_data,$add);
                                        }
                                    }
                                }
                                if(isset($head['City'])){
                                    if(isset($value[$in][$head['City']])){
                                        $add =['city' =>trim($value[$in][$head['City']])];
                                        if($add['city'] !=''){
                                            $other_data = array_merge($other_data,$add);
                                        }
                                    }
                                }
                                if(isset($head['Campaign'])){
                                    if(isset($value[$in][$head['Campaign']])){
                                        $add =['campaign' =>trim($value[$in][$head['Campaign']])];
                                        if($add['campaign'] !=''){
                                            $camp_data = $add;
                                        }
                                    }
                                }
                                if(!in_array($call_type,$accept_null)){

                                    $data = array_merge($data,$other_data);
                                    if(!empty($other_data['date_of_sale'])){
                                        $excel_date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp($data['date_of_sale']);
                                        $data['date_of_sale'] = date('Y-m-d',$excel_date);
                                    }
                                }/*else{
                                    $data['query'] =  trim($value[$in][$head['Query']]);
                                }*/
                                
                                if(array_keys($data,NULL) || array_keys($data,''))
                                {
                                    $cn++;
                                    if($cn>150)
                                        break;
                                    // print_r($optional_col);die();

                                    $error = CustomHelpers::errorFunction($head,$error,$value,$in,$optional_col,$column_name_format);  
                                    //return which column and which indexing value error
                                }else{
                                    if($source_match == 0){
                                        $error = $error.' "Source" Column Not Match from Master, row :- '.($in+1);
                                        break;
                                    }
                                    
                                    if(in_array($call_type,$accept_null)){
                                        $data = array_merge($data,$other_data);
                                    }
                                    $data['query'] =  isset($head['Query']) ? trim($value[$in][$head['Query']]) : '';
                                    // $record_data = [];
                                    // if(isset($data['query'])){
                                    //     $data['query'] =  isset($head['Query']) ? trim($value[$in][$head['Query']]) : '';
                                    //     unset($data['query']);
                                    // }
                                        
                                    $all_data = $data;
                                    if(!in_array($call_type,$accept_null)){  // when not select enquiry
                                        if(isset($data['frame'])){
                                            
                                            $find = CallData::where('call_type',$call_type)->where('frame',$all_data['frame'])->first();
                                            $update_insert = $this->update_call_data($find,$all_data,$error,$call_type,$camp_data);
                                            if(!$update_insert[0]){
                                                return [$error.' '.$update_insert[1]];
                                            }
                                        }else{
                                            $error = $error.'Frame Not Found, Something Went Wrong.';
                                        }
                                    }elseif(in_array($call_type,$accept_null)){
                                        // insert when frame not given  and if Enquiry selected
                                        // $all_data['call_type']  =   $call_type;
                                        // $insert = CallData::insertGetId($all_data);
                                        // if(!$insert){
                                        //     $error = $error."Call Data Not Inserted.";
                                        // }
                                        $find = CallData::where('call_type',$call_type)
                                                            ->where(function($query) use ($all_data){
                                                                $query->where('mobile',$all_data['mobile'])
                                                                    ->orwhereRaw('FIND_IN_SET('.$all_data['mobile'].', other_mobile)');
                                                            })
                                                            ->first();
                                        $update_insert = $this->update_call_data($find,$all_data,$error,$call_type,$camp_data);
                                        if(!$update_insert[0]){
                                            return [$error.' '.$update_insert[1]];
                                        }
                                    }
                                }
                            }
                        }
                    } else {
                        $error = $error."In your sheet have no data";
                    }
    
                    if ($out['columnCount'] == 1) {
                        $error = $error.$out['error'];
                    }    
                    if (!empty($error)) {
                        return array($error);
                    }
    
                } else {
                    $error = $error.' Error, Check your File should not be Empty';
                }
            }
            if (!empty($error)) {
                return array($error);
            }
        } else {
            return array('Error, Check your file should not be empty!!');
        }
        $ret_arr = array('success');
        return $ret_arr;
    }
    public function update_call_data($find,$all_data,$error,$call_type,$camp_data){

        // $default_arr = json_encode(array('CsdEnquiry' => "No", 'ServiceEnquiry' => "No"));
        $default_arr = '{}';
        $default_call_level = 3;
        if($call_type == 'Enquiry')
            $type_cdd = 1;
        elseif($call_type == 'Insurance')
            $type_cdd = 2;

        $find_call_users = CustomHelpers::find_users_callData($type_cdd,date('Y-m-d'));

        if(isset($find->id)){
            $call_data_id = $find->id;
            // check name and mobile number
            $old_name = explode(',',$find->name);
            $old_other_mobile = !empty($find->other_mobile)? explode(',',$find->other_mobile) : [];
            $old_mobile = $find->mobile;
            if($all_data['mobile'] != $old_mobile)
            {
                if(!in_array($old_mobile,$old_other_mobile)){
                    array_push($old_other_mobile,$old_mobile);
                    $all_data['other_mobile'] = implode(',',$old_other_mobile);
                }
            }
            // check for name
            if(!in_array($all_data['name'],$old_name)){
                array_push($old_name,$all_data['name']);
                $all_data['name'] = implode(',',$old_name);
            }
            // update it all record
            $all_data['updated_at'] = date('Y-m-d H:i:s',time()+1);
            $update = CallData::where('id',$find->id)->update($all_data);
            if(!$update){
                $error = $error."Call Data Not Update.";
            }
            // call data details
            $check_query = CallDataDetails::where('call_data_id',$call_data_id)
                                            ->whereIn('type',[1,2])
                                            ->where('query_status','Open')->first();
            
            $query = [];
            $campaign =[];
            if(!empty($all_data['query'])){
                array_push($query,$all_data['query']);
            }
            if(!empty($camp_data['campaign'])){
                array_push($campaign,$camp_data['campaign']);
            }
            if(isset($check_query->id)){
                if(!empty($check_query->query)){
                    array_push($query,$check_query->query);
                }
                if(!empty($check_query->campaign)){
                    array_push($campaign,$check_query->campaign);
                }
                $update_query = CallDataDetails::where('id',$check_query->id)
                                                ->update([
                                                    'type' => $type_cdd,
                                                    'query' =>  join(', ',$query),
                                                    'campaign' => join(', ',$campaign)
                                                ]);

            }else{

                $check_query = CallDataDetails::where('call_data_id',$call_data_id)
                                            ->whereIn('type',[1,2])
                                            ->where('query_status','Closed')
                                            ->first();

                // $find_call_users = CustomHelpers::find_callData_users();
                $find_call_users = CustomHelpers::find_users_callData($type_cdd,date('Y-m-d'));
                if($find_call_users[0]){
                    // assign users id
                    $all_data['assigned_to']  =   $find_call_users[1];
                }

                if(isset($check_query->id)){
                    $close_date = $check_query->closing_date;
                    $today = date('Y-m-d');
                    $diff = CustomHelpers::getMonth($today,$close_date);
                    
                    if($diff > 6){
                        $call_data_details = CallDataDetails::insertGetId([
                            'call_data_id'  =>  $call_data_id,
                            'type' => $type_cdd,
                            'query' =>  $all_data['query'],
                            'campaign' =>  $camp_data['campaign'],
                            'query_status'  =>  'Open',
                            'other_action'  =>  $default_arr,
                            'call_level'    =>  $default_call_level,
                            'assigned_to'   =>  $all_data['assigned_to'],
                            'assigned_by'   =>  Auth::id()
                        ]);
                    }
                }else{
                    $call_data_details = CallDataDetails::insertGetId([
                        'call_data_id'  =>  $call_data_id,
                        'type' => $type_cdd,
                        'query' =>  $all_data['query'],
                        'campaign' =>  $camp_data['campaign'],
                        'query_status'  =>  'Open',
                        'other_action'  =>  $default_arr,
                        'call_level'    =>  $default_call_level,
                        'assigned_to'   =>  $all_data['assigned_to'],
                        'assigned_by'   =>  Auth::id()
                    ]);
                }
            }
        }else{
            //  insert call data
            $all_data['call_type']  =   $call_type;
            // $find_call_users = CustomHelpers::find_callData_users();
            $find_call_users = CustomHelpers::find_users_callData($type_cdd,date('Y-m-d'));
            if($find_call_users[0]){
                // assign users id
                $all_data['assigned_to']  =   $find_call_users[1];
            }
            $insert = CallData::insertGetId($all_data);
            if(!$insert){
                $error = $error."Call Data Not Inserted.";
            }
            $call_data_id = $insert;
            
            $call_data_details = CallDataDetails::insertGetId([
                'call_data_id'  =>  $call_data_id,
                'type'          =>  $type_cdd,
                'query'         =>  $all_data['query'],
                'campaign'      =>  $camp_data['campaign'],
                'query_status'  =>  'Open',
                'other_action'  =>  $default_arr,
                'call_level'    =>  $default_call_level,
                'assigned_to'   =>  $all_data['assigned_to'],
                'assigned_by'   =>  Auth::id()
            ]);
        }
        if(!empty($error)){
            return [false,$error];
        }
        return [true,$call_data_id];

    }
    public function validate_excel_format($a,$column_name,$optional_col=[])
    {
        ini_set('max_execution_time', 18000);
        $index=0;
        $column_name_err=0;
        $char = '0';
        $side=0;
        $error="";
        $data_inserted=0;
        $header = array();
        $optional_header = [];
        // $column_name = array_merge($column_name,$optional_col);
        $productController = new Product();
        if(count($a)>=count($column_name))
        {
            foreach($a as $key => $val){
                $flag =0;   
                $val = trim($val);
                    if(!in_array($val,$column_name))
                    {
                        $column_name_err++;
                        $error=$error."Column Name not in provided format. Error At  ".$productController->getNameFromNumber($char)."1.";
                    }
                    else
                    {
                        $header[$val] = $key;
                    }
                    if(in_array($val,$optional_col)){
                        $optional_header[$val] = $key;
                    }
                    $index++;
                    $char++;
            }
            if(count($column_name) == count($header)){
                $column_name_err = 0;
                $error="";
                $header = array_merge($header,$optional_header);
            }
        }
        else
        {
            $column_name_err++;
            $error=$error."Required Column Name not in provided Sheet. Please Correct Sheet and try again";
            $side=1;
        }
        $data = array(
            'column_name_err' => $column_name_err,
            'error' =>  $error,
            'columnCount'  =>  $side,
            'header'    =>  $header
        );
        return $data;
        //return $header;
    }

    public function callDataList() {
        // $call_users = Users::where('users.role','Receptionist')
        //                     ->select('users.id','users.emp_id','users.name')
        //                     ->orderBy('users.id')
        //                     ->get();
        $callType = ['Enquiry','Insurance','Service'];
        $call_users = CallAssignSetting::whereIn('type',$callType)
                                    ->leftjoin('users','call_assign_setting.user_id','users.id')
                                    ->select('users.id','users.emp_id',
                                        DB::raw('Concat(users.name," ",IFNull(users.middle_name,"")," ",IFNull(users.last_name,""))as name'))
                                    ->groupBy('users.id')
                                    ->orderBy('users.id')
                                    ->get();
        $auth_role = Auth::user()->role;
        $data = [
            'layout' => 'layouts.main',
            'call_users' => $call_users,
            'auth_role' =>  $auth_role
        ];
        return view('admin.call.callData_summary',$data);
    }

    public function callDataList_api(Request $request, $tab) {
        //DB::enableQueryLog();
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;

        $auth_role = Auth::user()->role;
        $user_id = $request->input('user');
        $leave_date = [];
        // echo $user_id;
        if(!empty($user_id) || $user_id > 0){
            $emp_id = Users::where('id',$user_id)->pluck('emp_id')->toArray();
            // print_r($emp_id);
            $get_leaves = HR_Leave::where('user_id',$emp_id)
                                    ->where(function($query){
                                        $query->where('start_date','>=',DB::raw('CURRENT_DATE'))
                                            ->orwhere('end_date','>=',DB::raw('CURRENT_DATE'));
                                    })
                                    ->select('start_date','end_date')
                                    ->get()->toArray();
            // print_r($get_leaves);
            foreach($get_leaves as $k => $v){
                $start_date = $v['start_date'];
                $end_date = $v['end_date'];
                $diff = CustomHelpers::getDay($start_date,$end_date);
                if(strtotime($start_date) >= strtotime(date('Y-m-d'))){
                    array_push($leave_date,$start_date);
                }
                for($i = 0 ; $i < $diff ; $i++){
                    $this_date = date('Y-m-d',strtotime("+1 Day",strtotime($start_date)));
                    if(strtotime($this_date) >= strtotime(date('Y-m-d'))){
                        array_push($leave_date,$this_date);
                    }
                    $start_date = $this_date;
                }
            }
        }
        // print_r($leave_date);die;
        // DB::enableQueryLog();
            $api_data = CallData::leftJoin('call_data_details',function($join){
                                    $join->on('call_data_details.call_data_id','=','call_data.id')
                                            ->whereRaw('call_data_details.id  IN(select max(cdd.id) from call_data_details cdd where 
                                                        cdd.call_data_id = call_data.id group by type )');
                                })
                                ->leftJoin('users','call_data_details.assigned_to','users.id')
                                // ->leftJoin('call_data_details','call_data_details.call_data_id','call_data.id')
                                ->leftJoin('call_data_record',function($join){
                                    $join->on('call_data_record.call_data_id','=','call_data.id')
                                            ->whereRaw('call_data_record.call_data_details_id = call_data_details.id')
                                            ->whereRaw('call_data_record.id = (select max(cdd.id) from call_data_record cdd where 
                                                        cdd.call_data_id = call_data.id and call_data_details.id = cdd.call_data_details_id)');
                                })
            
            ->where(function($query) use($leave_date){
                $query->whereNull('call_data_record.next_call_date')
                        ->orwhereNotIn('call_data_record.next_call_date',$leave_date);
            })
            ->select(
                'call_data.id',
                'call_data_details.id as cdd_id',
                DB::raw('(Case when call_data_details.type = 1 then "Enquiry" 
                    when call_data_details.type = 2 then "Insurance"
                    when call_data_details.type = 3 then "Service Enquiry" End)as call_type'),
                DB::raw('Concat(users.name," ",Ifnull(users.middle_name,"")," ",users.last_name) as assign_to'),
                'call_data_details.query_status as cdd_status',
                'call_data.name as customer_name',
                'call_data.mobile as mobile',
                'call_data.source',
                'call_data_record.call_status',
                'call_data_record.remark',
                'call_data_record.next_call_date',
                'call_data.status'
            ); 
            
            if($auth_role != 'Superadmin'){
                $api_data = $api_data->where('call_data_details.assigned_to',Auth::id());
            }
            if($tab == 'enquiry')
            {
                $api_data = $api_data->where(function($query) {
                    $query->where('call_data.call_type','Enquiry')
                    ->where('call_data_details.type',1);
                        // ->where('call_data.status','Pending');
                        // ->orwhere('call_data_record.next_call_date','<=',date('Y-m-d'));
                });
            }
            if($tab == 'insurance'){
                $api_data = $api_data->where(function($query) {
                    $query->where('call_data.call_type','Insurance')
                    ->where('call_data_details.type',2);
                    // ->where('call_data.status','Pending')
                    // ->orwhere('call_data_record.next_call_date','<=',date('Y-m-d'));

                });
            }
             if($tab == 'all_pending'){
                $api_data = $api_data->where(function($query) {
                    $query->where('call_data.status','Pending')
                    ->orwhere('call_data_record.next_call_date','<=',date('Y-m-d'));
                });
            }
            if($tab == 'all'){
                $api_data = $api_data->where(function($query) {
                    // $query->where('call_data.status','Pending');
                    // ->orwhere('call_data_record.next_call_date','<=',date('Y-m-d'));
                })->orderBy('call_data.call_type','asc');
            }
            if($tab == 'service_enquiry'){
                $api_data = $api_data->where(function($query) {
                    $query->where('call_data_details.type',3);
                    // ->where('call_data.status','Pending')
                    // ->orwhere('call_data_record.next_call_date','<=',date('Y-m-d'));

                });
            }

            if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('users.name','like',"%".$serach_value."%")
                    ->orwhere('call_data.name','like',"%".$serach_value."%")
                    ->orwhere('call_data.mobile','like',"%".$serach_value."%")
                    ->orwhere('call_data.source','like',"%".$serach_value."%")   
                    ->orwhere('call_data_record.call_status','like',"%".$serach_value."%")   
                    ->orwhere('call_data_record.remark','like',"%".$serach_value."%")   
                    ->orwhere('call_data_record.next_call_date','like',"%".$serach_value."%")   
                    ->orwhere('call_data.status','like',"%".$serach_value."%")   
                    ;
                });
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                'assign_to',
                'call_data.call_type',
                'customer_name',
                'mobile',
                'source',
                'call_status',
                'remark',
                'next_call_date',
                'status'

                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                 $api_data->orderBy('call_data_details.call_level','asc');      
        
        $count = $api_data->count();
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        // print_r($api_data->get());die();
        // print_r(DB::getQueryLog());die();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data;
        
        return json_encode($array);
    }

    public function callData_view(Request $request) {
        
        $id = $request->input('id');
        $cdd = $request->input('cdd');
        $auth_id = Auth::id();
        $call_data = CallData::where('id',$id)->
                        select('call_data.*',
                        DB::raw('DATEDIFF(CURRENT_DATE,dnd_date) as dnd_day')
                        )->first();
        if(!isset($call_data->id)){
            return back()->with('error','Call Data Not Found.');
        }

        $call_data_details = CallDataDetails::where('id',$cdd)->select('*',
            DB::raw('(Case when call_data_details.type = 1 then "Enquiry" 
                            when call_data_details.type = 2 then "Insurance"
                            when call_data_details.type = 3 then "Service Enquiry" End)as cdd_type'))
                                            // ->whereIn('type',[1,2])
                                            ->orderBy('id', 'desc')->first();

        if(Auth::user()->role != 'Superadmin'){
            if($call_data_details->assigned_to != $auth_id) {
                return back()->with('error','You Are Not Authorized For this Page.');
            }
        }
        
        if(!isset($call_data_details->id)){
            return back()->with('error','Call Data Not Found.');
        }

        $type = array(1=>'Enquiry',2=>'Insurance',3=>'Service');
        $cdd_type = $type[$call_data_details->type];
        

        $dnd_setting = CallSetting::where('type',$cdd_type)
                        ->first();
        // get call details data for this call data id
        $call_data_record = CallDataRecord::where('call_data_id',$call_data->id)
                                ->where('call_data_details_id',$call_data_details->id)
                            ->orderBy('id','desc')->get()->toArray();
        $not_interested = true;
        $how_many_day_ni = 0;
        if(isset($call_data_record[0])){
            $last_record = $call_data_record[0];
            $ni_setting = CallSetting::where('type',$cdd_type)
                                            ->where('sub_type','NotInterested')->select('value')->first();
            if(!isset($ni_setting->value)){
                return back()->with('error','Call Setting Not Found.');
            }
            $how_many_day_ni = $ni_setting->value;
            if($last_record['call_status'] == 'NotInterested'){
                $diff_day = CustomHelpers::getDay($last_record['call_date'],date('Y-m-d'));
                if($diff_day < $how_many_day_ni){
                    $not_interested = false;
                }
            }
        }
        else{
            $ni_setting = CallSetting::where('type',$cdd_type)
                                            ->where('sub_type','NotInterested')->select('value')->first();
            if(!isset($ni_setting->value)){
                return back()->with('error','Call Setting Not Found.');
            }
            $how_many_day_ni = $ni_setting->value;
        }
        // print_r($how_many_day_ni);die();
        // get all call details data for using mobile number
        $mobile_no = $call_data->mobile;
        $timeline = CallData::where('call_data_details.assigned_to',Auth::id())
                    ->leftJoin('call_data_details',function($join){
                        $join->on('call_data_details.call_data_id','=','call_data.id')
                                // ->where('call_data_details.query_status','Open')
                                ->whereRaw('call_data_details.id IN(select max(cdd.id) from call_data_details cdd where 
                                            cdd.call_data_id = call_data.id group By type)');
                    })
                    ->leftJoin('call_data_record',function($join){
                        $join->on('call_data_record.call_data_id','=','call_data.id')
                                ->whereRaw('call_data_record.id = (select max(cdr.id) from call_data_record cdr where 
                                cdr.call_data_id = call_data.id and cdr.call_data_details_id = call_data_details.id group by cdr.call_data_details_id)');
                    })
                    ->leftJoin('users','call_data_record.updated_by','users.id')
                    ->whereNotNull('call_data_record.id')
                    ->where("call_data.mobile",$mobile_no)
                    ->select(
                        'users.name as updated_by',
                        // 'call_data.call_type',
                        DB::raw('(Case when call_data_details.type = 1 then "Enquiry" 
                            when call_data_details.type = 2 then "Insurance"
                            when call_data_details.type = 3 then "Service Enquiry" End)as call_type'),
                        'call_data_record.call_date',
                        'call_data_record.call_status',
                        'call_data_record.remark',
                        'call_data_record.next_call_date',
                        'call_data.call_type as cd_type'
                    )->get()->toArray();
        $status_allow_q = Settings::where('name','ManuallyCallAllowed')
                                ->whereRaw("FIND_IN_SET(".Auth::id().",value)")
                                ->pluck('value')->toArray();
        if(count($status_allow_q) > 0){
            $call_status = CallStatusMaster::select('key_name','value')
                                        ->whereRaw("FIND_IN_SET(".$call_data_details->type.",type)")
                                        ->get()->toArray();
        }else{
            $call_status = CallStatusMaster::where('manual_allow',0)
                                        ->whereRaw("FIND_IN_SET(".$call_data_details->type.",type)")
                                        ->select('key_name','value')->get()->toArray();
        }
        // print_r($call_status);die;
        $service_booking = ServiceBooking::where('call_id',$id)->where('call_type','CallData')
                        ->orderBy('created_at','desc')->first();
        // print_r($service_booking);die;

        $holiday_list = CustomHelpers::getAllHolidays();
        // additional disabled dates
        $ni_setting_start_day = date('Y-m-d',strtotime('+'.$how_many_day_ni.' Day',strtotime(date('Y-m-d'))));
        
        $company_name = FinanceCompany::orderBy('id','ASC')->get();
        $executive = Users::where('role','SaleExecutive')->get(['id','name']);

        $last_sale = Sale::leftjoin('customer','sale.customer_id','customer.id')
                    ->leftjoin('sale_order','sale_order.sale_id','sale.id')
                    ->select('sale.sale_no', 'sale.total_amount' ,'sale_order.product_frame_number')
                    ->where('customer.mobile',$mobile_no)
                    ->orderBy('sale.id','DESC')->first();

        $last_otc = OtcSale::leftjoin('customer','otc_sale.customer_id','customer.id')
                    ->leftjoin('sale_order','sale_order.sale_id','otc_sale.sale_id')
                    ->leftjoin('sale','otc_sale.sale_id','sale.id')
                    ->select('sale.sale_no', 'otc_sale.total_amount' ,'sale_order.product_frame_number')
                    ->where('customer.mobile',$mobile_no)
                    ->orderBy('otc_sale.id','DESC')->first();

        $last_insurance = Insurance::leftjoin('sale','insurance.sale_id','sale.id')
                    ->leftjoin('sale_order','sale_order.sale_id','insurance.sale_id')
                    ->leftjoin('customer','sale.customer_id','customer.id')
                    ->select('sale.sale_no', 'insurance.insurance_amount' ,'sale_order.product_frame_number')
                    ->where('customer.mobile',$mobile_no)
                    ->orderBy('insurance.id','DESC')->first();

        $last_service = ServiceModel::leftjoin('sale','service.sale_id','sale.id')
                    ->leftjoin('sale_order','sale_order.sale_id','service.sale_id')
                    ->leftjoin('customer','service.customer_id','customer.id')
                    ->leftjoin('job_card','service.id','job_card.service_id')
                    ->select('sale.sale_no', 'job_card.total_amount' ,'sale_order.product_frame_number','service.frame')
                    ->where('customer.mobile',$mobile_no)
                    ->orderBy('service.id','DESC')->first();

        // print_r($ni_setting_day);die;
        $data = [
            'call_data_id'=>$call_data->id,
            'call_data' => $call_data,
            'timeline' => $timeline,
            'call_data_record' =>  $call_data_record,
            'call_status' => $call_status,
            'service_booking'   =>  $service_booking,
            'forbidden' =>  $holiday_list,
            'dnd_setting'   =>  $dnd_setting,
            'call_data_details' =>  $call_data_details,
            'not_interested'    =>  $not_interested,
            'ni_setting_start_day'   =>  $ni_setting_start_day,
            'ni_setting'           => $ni_setting,
            'csd_service_enquiry'   =>  json_decode($call_data_details['other_action']),
            'company_name'=>$company_name,
            'last_sale'  => $last_sale, 
            'last_otc'  => $last_otc, 
            'last_insurance'  => $last_insurance,
            'last_service'  =>$last_service, 
            'layout' => 'layouts.main'
        ]; 
        return view('admin.call.callData_view',$data);
    }
    public function callDataLog_update(Request $request) {
        $this->validate($request,[
            'call_status'=>'required',
            'next_call_date'    =>  'required_unless:call_status,Closed',
            'remark'    =>  'required'
        ],[
            'call_status.required'=> 'This is required.', 
            'next_call_date.required_unless'=> 'This is required.', 
            'remark.required'=> 'This is required.'
        ]);

        $callStatus = $request->input('call_status');
        $next_call_date = $request->input('next_call_date');
        $remark = $request->input('remark');
        $call_data_id = $request->input('call_data_id');
        $call_data_details_id = $request->input('call_data_details_id');

        $call_duration = $request->input('call_duration');

        $call_data = CallData::where('id',$call_data_id)
                                ->first();
        if(!isset($call_data->id)){
            return back()->with('error','Call Data Not Found.')->withInput();
        }
        $call_data_details = CallDataDetails::where('id',$call_data_details_id)
                                ->where('call_data_id',$call_data_id)
                                ->first();
        if(!isset($call_data_details->id)){
            return back()->with('error','Call Data Details Not Found.')->withInput();
        }
        if($call_data_details->query_status == 'Closed'){
            return back()->with('error',"This Call Already Closed. So, Couldn't Update It.")->withInput();
        }
        // if($call_data->assigned_to != Auth::id()){
        //     return back()->with('error',"You Are Not Authorized For This Call.")->withInput();
        // }
        if($call_data_details->assigned_to != Auth::id()){
            return back()->with('error',"You Are Not Authorized For This Call.")->withInput();
        }

        $diff = CustomHelpers::getDay($next_call_date,date('Y-m-d'));
       
        // $redirect_str = '/admin/call/data/view?id='.$call_data_id;
        
        $table = ['call_data_record'];
        CustomHelpers::resetIncrement($table);
        try{
            DB::beginTransaction();
            $callData_details = [];
            $type = [1=>'Enquiry',2=>'Insurance',3=>'Service'];
            $call_type = $type[$call_data_details->type];

            $callData_record_data = [
                // 'type'  =>  $call_data->call_type,
                'type'  =>  $call_type,
                'call_data_id'  =>  $call_data_id,
                'call_data_details_id'  =>  $call_data_details_id,
                'call_date' =>  date('Y-m-d'),
                'call_status'   =>  $callStatus,
                'remark'    =>  $remark,
                'next_call_date'    =>  $next_call_date,
                'call_duration'  => $call_duration,
                'updated_by'    =>  Auth::id()
            ];
            $callData_update = [];
            if ($callStatus == 'Closed') {
                $callData_record_data['next_call_date'] = null;
                $callData_update['status'] = 'Done';

                $callData_details['closing_date']   =   date('Y-m-d');
                $callData_details['query_status']   =   'Closed';
            }else{
                if ($diff < 0) {
                    return back()->with('error','Please select valid Date.')->withInput();
                }
            }

            // check NotReachable 
            // if not reachable 5 times(will count according to calling dates, 
            // if call same day 10 times yet it will count 1) then that call record will close.
            $check_nr = CallDataDetails::where('id',$call_data_details_id)
                                        ->select('not_rechable','not_interested')->first();
            $not_rechable = 0; $not_interested = 0;
            if(isset($check_nr->not_rechable)){
                $not_rechable = $check_nr->not_rechable;
                $not_interested = $check_nr->not_interested;
            }

            if($callStatus == 'NotReachable'){
                // $not_rechable++;
                // $update_nr = CallDataDetails::where('id',$call_data_details_id)
                //                                         ->increment('not_rechable',1);
                $max_record = CallDataRecord::where('call_data_id',$call_data_id)
                                    ->where('call_data_details_id',$call_data_details_id)
                                    ->max('id');
                // print_r($max_record);die;
                if($max_record){
                    $last_record = CallDataRecord::where('id',$max_record)
                                    ->where('call_status','NotReachable')
                                    ->where('call_date',DB::raw('CURRENT_DATE'))
                                    ->select('id','call_status')->first();
                    if(!isset($last_record->id))
                    {
                    
                        $not_rechable++;
                        $update_nr = CallDataDetails::where('id',$call_data_details_id)
                                                        ->increment('not_rechable',1);
                    }
                }else{
                    $not_rechable++;
                    $update_nr = CallDataDetails::where('id',$call_data_details_id)
                                                    ->increment('not_rechable',1);
                }
                if($not_rechable >= 5){
                    // $callData_update['status'] = 'Done';
                    $callData_details['closing_date'] = date('Y-m-d');
                    $callData_details['query_status'] = 'Closed';
                }
            }


            // check NotInterested
            // If not interested 3 times then the insurance call will be closed.
            if($callStatus == 'NotInterested'){
                
                $not_interested++;
                $update_ni = CallDataDetails::where('id',$call_data_details_id)
                                                    ->increment('not_interested',1);
                
                if($not_interested >= 3){
                    // $callData_update['status'] = 'Done';
                    $callData_details['closing_date'] = date('Y-m-d');
                    $callData_details['query_status'] = 'Closed';
                }
            }
            

            // update in call_data_details
            if(count($callData_details) > 0){
                // update Call Data 
                
                $update_call_data_details = CallDataDetails::where('id',$call_data_details_id)
                                    ->update($callData_details);
                if(!$update_call_data_details){
                    DB::rollback();
                    return back()->with('error','Call Data Details Not Updated.')->withInput();
                }
            }

            $check_all_done = CallDataDetails::where('call_data_id',$call_data_id)->get()->toArray();
            $status_array = array_column($check_all_done, 'query_status');

            if(!in_array('Open', $status_array)){
                $callData_update['status'] = 'Done';
            }

            if(count($callData_update) > 0){
                // update Call Data 
                $update_call_data = CallData::where('id',$call_data_id)
                                    ->update($callData_update);

                if(!$update_call_data){
                    DB::rollback();
                    return back()->with('error','Call Data Not Updated.')->withInput();
                }
            }
            
            // insert in call data details
            $update_calldata_details = CallDataRecord::insertGetId($callData_record_data);
            if(!$update_calldata_details){
                DB::rollback();
                return back()->with('error','Call Data Record Not Updated.')->withInput();
            }
                
            $nextcall_data = CallData::where('call_data_details.assigned_to',Auth::id())
                                ->whereNotIn('call_data.status',['Done'])
                                ->whereNotIn('call_data.id',[$call_data_id])
                                ->leftJoin('call_data_details',function($join){
                                    $join->on('call_data_details.call_data_id','=','call_data.id')
                                            ->where('call_data_details.query_status','Open')
                                            ->whereRaw('call_data_details.id IN(select max(cdd.id) from call_data_details cdd where 
                                                        cdd.call_data_id = call_data.id group By type)');
                                })
                                ->leftJoin('users','call_data_details.assigned_to','users.id')
                                ->leftJoin('call_data_record',function($join){
                                    $join->on('call_data_record.call_data_id','=','call_data.id')
                                            ->whereRaw('call_data_record.id = (select max(cdr.id) from call_data_record cdr where 
                                                        cdr.call_data_id = call_data.id and cdr.call_data_details_id = call_data_details.id
                                                         group by cdr.call_data_id)');
                                })
                                ->where(function($query) use($call_data_details) {
                                    $query->where('call_data_details.type',$call_data_details->type)
                                        
                                        ->whereRaw('(call_data.status = "Pending" And call_data_record.next_call_date <="'.date("Y-m-d").'" or call_data_record.id is Null)');
                                })
                                ->orderBy('call_data_details.updated_at','asc')
            ->select(
                'call_data.id','call_data_details.id as call_d_id'
            )->limit(1)->get()->toArray();
            
        }
        catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return back()->with('error','Something went wrong '.$ex->getMessage())->withInput();
        } 
        /* Add action Log */
        CustomHelpers::userActionLog($action='Call Data Update',$call_data_id,0);
        DB::commit();
        if(count($nextcall_data) > 0){
            return redirect('/admin/call/data/view?id='.$nextcall_data[0]['id'].'&cdd='.$nextcall_data[0]['call_d_id'].'')->with('success','Last Call Status Successfully Updated.');
        }else{
            return back()->with('success',"All Calls Completed.");
        }
    }
    public function callData_auto_update(Request $request) {
        $this->validate($request,[
            'call_status'=>'required',
            'next_call_date'    =>  'required_unless:call_status,Done',
            'remark'    =>  'required'
        ],[
            'call_status.required'=> 'This is required.', 
            'next_call_date.required_unless'=> 'This is required.', 
            'remark.required'=> 'This is required.'
        ]);

        $callStatus = $request->input('call_status');
        $next_call_date = $request->input('next_call_date');
        $remark = $request->input('remark');
        $call_data_id = $request->input('call_data_id');
        $call_data_details_id = $request->input('call_data_details_id');

        $call_data = CallData::where('id',$call_data_id)
                                ->first();
        if(!isset($call_data->id)){
            return response()->json(['message'=>'Call Data Not Found.'],401);
        }
        $call_data_details = CallDataDetails::where('id',$call_data_details_id)
                                ->where('call_data_id',$call_data_id)
                                ->first();
        if(!isset($call_data_details->id)){
            return response()->json(['message'=>'Call Data Details Not Found.'],401);
        }
        if($call_data_details->query_status == 'Closed'){
            return response()->json(['message'=>"This Call Already Closed. So, Couldn't Update It."],401);
        }

        if($call_data_details->assigned_to != Auth::id()){
            return back()->with('error',"You Are Not Authorized For This Call.")->withInput();
        }
       
        $diff = CustomHelpers::getDay($next_call_date,date('Y-m-d'));
        
        $table = ['call_data_record'];
        CustomHelpers::resetIncrement($table);
        try{
            DB::beginTransaction();
            $type = [1=>'Enquiry',2=>'Insurance',3=>'Service'];
            $call_type = $type[$call_data_details->type];

            $callData_details = [];
            
            $callData_update = [];
            
            if ($diff < 0) {
                return response()->json(['message'=>"Please select valid Date."],401);
            }
            

            // check NotReachable 
            // if not reachable 5 times(will count according to calling dates, 
            // if call same day 10 times yet it will count 1) then that call record will close.
            $check_nr = CallDataDetails::where('id',$call_data_details_id)
                                        ->select('not_rechable','not_interested')->first();
            $not_rechable = 0; 
            if(isset($check_nr->not_rechable)){
                $not_rechable = $check_nr->not_rechable;
            }
            if($callStatus == 'NotReachable'){
                
                $max_record = CallDataRecord::where('call_data_id',$call_data_id)
                                    ->where('call_data_details_id',$call_data_details_id)
                                    ->max('id');
                // print_r($max_record);die;
                if($max_record){
                    $last_record = CallDataRecord::where('id',$max_record)
                                    ->where('call_status','NotReachable')
                                    ->where('call_date',DB::raw('CURRENT_DATE'))
                                    ->select('id','call_status')->first();
                    if(!isset($last_record->id))
                    {
                    
                        $not_rechable++;
                        $update_nr = CallDataDetails::where('id',$call_data_details_id)
                                                        ->increment('not_rechable',1);
                    }
                }else{
                    $not_rechable++;
                    $update_nr = CallDataDetails::where('id',$call_data_details_id)
                                                    ->increment('not_rechable',1);
                }
                if($not_rechable >= 5){
                    // $callData_update['status'] = 'Done';
                    $callData_details['closing_date'] = date('Y-m-d');
                    $callData_details['query_status'] = 'Closed';
                    $remark = $remark.","."So its closed now After 5 times of not reachable status.";
                }
            }

            // update in call_data_details
            if(count($callData_details) > 0){
                // update Call Data 
                $update_call_data_details = CallDataDetails::where('id',$call_data_details_id)
                                    ->update($callData_details);
                if(!$update_call_data_details){
                    DB::rollback();
                    return response()->json(['message'=>"Call Data Details Not Updated."],401);
                }
            }

            //code to check if all call details are closed 
            $check_all_done = CallDataDetails::where('call_data_id',$call_data_id)->get()->toArray();
            $status_array = array_column($check_all_done, 'query_status');

            if(!in_array('Open', $status_array)){
                $callData_update['status'] = 'Done';
            }

            // print_r(count($callData_details));die;
            if(count($callData_update) > 0){
                // update Call Data 
                $update_call_data = CallData::where('id',$call_data_id)
                                    ->update($callData_update);
                if(!$update_call_data){
                    DB::rollback();
                    return response()->json(['message'=>"Call Data Not Updated."],401);
                }
            }
            
            $callData_record_data = [
                // 'type'  =>  $call_data->call_type,
                'type'  =>  $call_type,
                'call_data_id'  =>  $call_data_id,
                'call_data_details_id'  =>  $call_data_details_id,
                'call_date' =>  date('Y-m-d'),
                'call_status'   =>  $callStatus,
                'remark'    =>  $remark,
                'next_call_date'    =>  $next_call_date,
                'auto_save' => 1,
                'updated_by'    =>  Auth::id()
            ];
            
            // insert in call data details
            $update_calldata_details = CallDataRecord::insertGetId($callData_record_data);
            if(!$update_calldata_details){
                DB::rollback();
                return response()->json(['message'=>"Call Data Record Not Updated."],401);
            }
            // for redirection
            $api_data = CallData::where('call_data_details.assigned_to',Auth::id())
                                ->whereNotIn('call_data.status',['Done'])
                                ->whereNotIn('call_data.id',[$call_data_id])
                                ->leftJoin('call_data_details',function($join){
                                    $join->on('call_data_details.call_data_id','=','call_data.id')
                                            ->where('call_data_details.query_status','Open')
                                            ->whereRaw('call_data_details.id IN(select max(cdd.id) from call_data_details cdd where 
                                                        cdd.call_data_id = call_data.id group By type)');
                                })
                                ->leftJoin('users','call_data_details.assigned_to','users.id')
                                ->leftJoin('call_data_record',function($join){
                                    $join->on('call_data_record.call_data_id','=','call_data.id')
                                            ->whereRaw('call_data_record.id = (select max(cdr.id) from call_data_record cdr where 
                                                        cdr.call_data_id = call_data.id and cdr.call_data_details_id = call_data_details.id
                                                         group by cdr.call_data_id)');
                                })
                                ->where(function($query) use($call_data_details) {
                                    // $query->where('call_data.call_type',$call_data->call_type)
                                    $query->where('call_data_details.type',$call_data_details->type)
                                            ->whereRaw('(call_data.status = "Pending" And call_data_record.next_call_date <="'.date("Y-m-d").'" or call_data_record.id is Null)');
                                        // ->where('call_data.status','Pending')
                                        // ->orwhere('call_data_record.next_call_date','<=',date('Y-m-d'));
                                })
                                ->orderBy('call_data_details.updated_at','asc')
            ->select(
                'call_data.id','call_data_details.id as call_d_id'
            )->limit(1)->get()->toArray();

            if(count($api_data) > 0){
                $redirection_data = [
                    'call_id' => $api_data[0]['id'],
                    'cdd_id'=> $api_data[0]['call_d_id']
                ];
            }else{  
                $redirection_data = [];
            }
        }
        catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return response()->json(['message'=>'Something went wrong '.$ex->getMessage()],401);
        }
        /* Add action Log */
        CustomHelpers::userActionLog($action='Call Data Update',$call_data_id,0);
        DB::commit();
        $return_data = [
            'status'    =>  true,
            'redirect'  =>  $redirection_data,
            'message'   =>  'Successfully Updated.'
        ];
        return response()->json($return_data);
    }
    public function callDataRequestEnquiry(Request $request){

        $call_data_id = $request->input('call_data_id');
        $call_data = CallData::where('id',$call_data_id)->first();
        if(!isset($call_data->id)){
            return back()->with('error','Call Data Not Found.')->withInput();
        }
        if($call_data->request_enquiry == 1){
            return back()->with('error',"This Call Already Requested for Enquiry.")->withInput();
        }
        if($call_data->assigned_to != Auth::id()){
            return back()->with('error',"You Are Not Authorized For This Call.")->withInput();
        }
        // $redirect_str = '/admin/call/data/view?id='.$call_data_id;
        try{
            DB::beginTransaction();

            $callData_update = [
                'request_enquiry'   =>  1,
                'request_approve_by'    =>  0
            ];
            // update Call Data 
            $update_call_data = CallData::where('id',$call_data_id)
                                    ->update($callData_update);
            if(!$update_call_data){
                DB::rollback();
                return back()->with('error','Call Data Not Updated.')->withInput();
            }
        }
        catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return back()->with('error','Something went wrong '.$ex)->withInput();
        }
        /* Add action Log */
        CustomHelpers::userActionLog($action='Call Data Request Enqiry',$call_data_id,0);
        DB::commit();
        return back()->with('success','Successfully Requested.')->withInput();
    }
    public function callDataServiceBook(Request $request){

        $this->validate($request,[
            'servicename'=>'required',
            'mobile'    =>  'required'
        ],[
            'name.required'=> 'This is required.', 
            'mobile.required'=> 'This is required.'
        ]);
        $call_id = $request->input('call_data_id');
        $name = $request->input('servicename');
        $mobile = $request->input('mobile');
        try{

            $service_booking = ServiceBooking::where('call_id',$call_id)->where('call_type','CallData')
                        ->orderBy('created_at','desc')->get()->first();
    
            if($service_booking)
            {
                 return back()->with('Service already Booked')->withInput();
            }else{       
                $servicedata = ServiceBooking::insertGetId([
                        'call_id' => $call_id,
                        'call_type' =>  'CallData',
                        'name'=>$name,
                        'status'=>'Booked',
                        'mobile'=>$mobile
                ]);
    
                if($servicedata == NULL) {
                return back()->with('Something Went Wrong.')->withInput();
                } 
            }
        }
        catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return back()->with('error','Something went wrong '.$ex)->withInput();
        }
        /* Add action Log */
        CustomHelpers::userActionLog($action='Call Data Service Booking',$call_id,0);
        DB::commit();
        return back()->with('success','Successfully Service Booked.')->withInput();

    } 
    public function nextCallDatalog(Request $request) {
        $type = [1=>'Enquiry',2=>'Insurance',3=>'Service'];
        $call_type = $type[$request->input('call_type')];
        $call_data_id = $request->input('id');
        DB::enableQueryLog();
        $api_data = CallData::leftJoin('call_data_details',function($join){
                                    $join->on('call_data_details.call_data_id','=','call_data.id')
                                            ->where('call_data_details.query_status','Open')
                                            ->whereRaw('call_data_details.id  IN(select max(cdd.id) from call_data_details cdd where 
                                                        cdd.call_data_id = call_data.id group by type)');
                                })
                                ->leftJoin('users','call_data_details.assigned_to','users.id')
                                ->where('call_data_details.assigned_to',Auth::id())
                                ->whereNotIn('call_data.status',['Done'])
                                ->whereNotIn('call_data.id',[$call_data_id])
                                // ->leftJoin('call_data_record',function($join){
                                //     $join->on('call_data_record.call_data_id','=','call_data.id')
                                //             ->whereRaw('call_data_record.id = (select max(cdd.id) from call_data_record cdd where 
                                //                         cdd.call_data_id = call_data.id group by cdd.call_data_id)');
                                // })
                                ->leftJoin('call_data_record',function($join){
                                    $join->on('call_data_record.call_data_id','=','call_data.id')
                                            ->whereRaw('call_data_record.id = (select max(cdr.id) from call_data_record cdr where 
                                                        cdr.call_data_id = call_data.id and cdr.call_data_details_id = call_data_details.id
                                                         group by cdr.call_data_id)');
                                })
                                ->orderBy('call_data_details.updated_at','asc')
            ->select(
                'call_data.id','call_data_details.id as call_d_id'
            );
            if($call_type == 'Enquiry')
            {
                $api_data = $api_data->where(function($query) {
                    $query->where('call_data.call_type','Enquiry')
                    ->where('call_data_details.type',1)
                    ->whereRaw('(call_data.status = "Pending" And call_data_record.next_call_date <="'.date("Y-m-d").'" or call_data_record.id is Null)');
                    // ->orwhere('call_data_record.next_call_date','<=',date('Y-m-d'));
                });
            }
            if($call_type == 'Insurance'){
                $api_data = $api_data->where(function($query) {
                    $query->where('call_data.call_type','Insurance')
                    ->where('call_data_details.type',2)
                    ->whereRaw('(call_data.status = "Pending" And call_data_record.next_call_date <="'.date("Y-m-d").'" or call_data_record.id is Null)');
                    // ->where('call_data.status','Pending')
                    // ->orwhere('call_data_record.next_call_date','<=',date('Y-m-d'));
                });
            }
            if($call_type == 'Service'){
                $api_data = $api_data->where(function($query) {
                    $query->where('call_data_details.type',3)
                    ->whereRaw('(call_data.status = "Pending" And call_data_record.next_call_date <="'.date("Y-m-d").'" or call_data_record.id is Null)');
                    // ->where('call_data.status','Pending')
                    // ->orwhere('call_data_record.next_call_date','<=',date('Y-m-d'));
                });
            }
        $api_data = $api_data->limit(1)->get()->toArray();
           // print_r(DB::getQueryLog());die();
        if(count($api_data) > 0){
            return redirect('/admin/call/data/view?id='.$api_data[0]['id'].'&cdd='.$api_data[0]['call_d_id'].'');
        }else{
            return back()->with('error',"Error, Couldn't find Call's for Next ");
        }
       
    }

    public function callDataRequestEnquiryList() {
        return view('admin.call.callDataRequestEnquiry',['layout' => 'layouts.main']);
    }

    public function callDataRequestEnquiryList_api(Request $request) {
        //DB::enableQueryLog();
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;

            $api_data = CallData::select(
                'call_data.id',
                'call_data.call_type',
                'call_data.name as customer_name',
                'call_data.mobile as mobile',
                'call_data.other_mobile',
                'call_data.source',
                'call_data.status'
            )
            ->where('call_data.request_enquiry',1)
            ->orderBy('call_data.call_type','asc');
           
            if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('users.name','like',"%".$serach_value."%")
                    ->orwhere('call_data.name','like',"%".$serach_value."%")
                    ->orwhere('call_data.mobile','like',"%".$serach_value."%")
                    ->orwhere('call_data.other_mobile','like',"%".$serach_value."%")
                    ->orwhere('call_data.source','like',"%".$serach_value."%")    
                    ->orwhere('call_data.status','like',"%".$serach_value."%")   
                    ;
                });
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                'assign_to',
                'customer_name',
                'mobile',
                'other_mobile',
                'source',
                'status'
                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                 $api_data->orderBy('call_data.id','desc');      
        
        $count = $api_data->count();
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        //print_r($queries);die();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

    public function callDataAssign(Request $request){
        try{
            $callData_ids = $request->input('assign_ids');

            if(!isset($callData_ids[0])){   
                return back()->with('error','Select Minimum One Row.');
            }
            $user = $request->input('user');
            if($user <= 0 || empty($user)){   
                return back()->with('error','Required to Select User.');
            }
            DB::beginTransaction();

            $get_cdd = CallDataDetails::whereIn('id',$callData_ids)->get()->toArray();
            $get_cd = array_column($get_cdd, 'call_data_id');

            $update_cdd = CallDataDetails::whereIn('id',$callData_ids)
                                        ->update(
                                            [
                                                'assigned_to' =>  $user,
                                                'assigned_by' =>  Auth::id()
                                            ]
                                        );
            $update_call_assign = CallData::whereIn('id',$get_cd)
                                        ->update(
                                            [
                                                'assigned_to' =>  $user
                                            ]
                                        );
            if(!$update_call_assign && $update_cdd)
            {
                DB::rollback();
                return back()->with('SomeThing Went Wrong');
            }
        }
        catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return back()->with('error','Something went wrong '.$ex)->withInput();
        }
        /* Add action Log */
        CustomHelpers::userActionLog($action='Call Data Assign',0,0);
        DB::commit();
        return back()->with('success','Successfully Assigned.')->withInput();
    }

    public function callDataUpdateDnd(Request $request){
        $this->validate($request,[
            'dnd_option.*'=>'required'
        ],[
            'dnd_option.required'=> 'This is required.'
        ]);
        $call_id = $request->input('call_data_id');
        $dnd_option = $request->input('dnd_option');
        if(!empty($dnd_option))
            $dnd_insert = implode(',', $dnd_option);
        else
            $dnd_insert = null;
        try{
            DB::beginTransaction();
            $update_call = CallData::where('id',$call_id)
                            ->update([
                                'dnd'   =>  $dnd_insert,
                                'dnd_date'  =>  date('Y-m-d')
                            ]);
            if(!$update_call){
                DB::rollback();
                return back()->with('error','Something went wrong.');
            }
    
        }catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return back()->with('error','Something went wrong '.$ex->getMessage())->withInput();
        }
        /* Add action Log */
        CustomHelpers::userActionLog($action='Call Data Update DND',$call_id,0);
        DB::commit();
        return back()->with('success','Successfully Updated.')->withInput();
    }
    public function callLogUpdateDnd(Request $request){
        $this->validate($request,[
            'dnd_option'=>'required'
        ],[
            'dnd_option.required'=> 'This is required.'
        ]);
        $call_id = $request->input('calling_id');
        $dnd_option = $request->input('dnd_option');
        
        try{

            $update_call = Calling::where('id',$call_id)
                            ->update([
                                'dnd'   =>  $dnd_option,
                                'dnd_date'  =>  date('Y-m-d')
                            ]);
            if(!$update_call){
                DB::rollback();
                return back()->with('error','Something went wrong.');
            }
    
        }catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return back()->with('error','Something went wrong '.$ex->getMessage())->withInput();
        }
        /* Add action Log */
        CustomHelpers::userActionLog($action='Calling Update DND',$call_id,0);
        DB::commit();
        return back()->with('success','Successfully Updated.')->withInput();
    }

    public function callDataCheckSold(Request $request){
        // $this->validate($request,[
        //     'sold_name'=>'required',
        //     'sold_mobile'=>'required',
        //     'sold_aadhar'=>'required'
        // ],[
        //     'sold_name.required'=> 'This is required.',
        //     'sold_mobile.required'=> 'This is required.',
        //     'sold_aadhar.required'=> 'This is required.'
        // ]);
        $call_id = $request->input('call_data_id');
        
        try{
            if($call_id > 0){
                $name = $request->input('sold_name');
                $mobile = $request->input('sold_mobile');
                $aadhar = $request->input('sold_aadhar');
                if(!empty($name) || !empty($mobile) || !empty($aadhar)){

                    $find = Customers::select('id');
                    if(!empty($name)){
                        $find = $find->where('name','like','%'.$name.'%');
                    }
                    if(!empty($mobile)){
                        $find = $find->where('mobile',$mobile);
                    }
                    if(!empty($aadhar)){
                        $find = $find->where('aadhar_id',$aadhar);
                    }
                    $find = $find->first();
                    if(isset($find->id)){
                        $getsale = Sale::where('customer_id',$find->id)->select('sale_no')->get()->toArray();
                        if(isset($getsale[0])){
                            return response()->json([true,$getsale]);
                        }
                        return response()->json([false,'Not Found Any Sale Data.']);
                    }else{
                        return response()->json([false,'Not Match Any Customer Information.']);
                    }
                }else{
                    return response()->json('Any One Field is Required to Search.',402);
                }
            }else{
                return response()->json('Something went wrong.',402);
            }
    
        }catch(\Illuminate\Database\QueryException $ex) {
            return response()->json('Something went wrong '.$ex->getMessage());
        }
        /* Add action Log */
        // CustomHelpers::userActionLog($action='',$call_id,0);
    }

    public function callDataCheckSoldDB(Request $request){
       
        $call_id = $request->input('call_data_id');
        
        try{
            DB::beginTransaction();
            if($call_id > 0){
                $check = CallData::where('id',$call_id)->first();
                if(isset($check->id)){
                    $updateCallData = CallData::where('id',$call_id)
                                        ->update(['status'  =>  'Done']);
                    
                    $get_cdd_id = CallDataDetails::where('call_data_id',$call_id)
                                                ->where('query_status','Open')->first();
                    if(isset($get_cdd_id->id)){
                        $update_cdd  = CallDataDetails::where('id',$get_cdd_id->id)
                                            ->update([
                                                'closing_date'  =>  date('Y-m-d'),
                                                'query_status'  =>  'Closed'
                                            ]);
                        $insert_record = CallDataRecord::insertGetId([
                            'call_data_details_id'  =>  $get_cdd_id->id,
                            'call_data_id'  =>  $call_id,
                            'call_status'   =>  'SoldClosed',
                            'remark'    =>  'Call Close by Add Sold Action',
                            'updated_by'    =>  Auth::id()
                        ]);
                    }else{
                        DB::rollback();
                        return response()->json('Call Already Closed.',402);
                    }
                   
                }else{
                    DB::rollback();
                    return response()->json('Not Found Call Data.',402);
                }
            }else{
                DB::rollback();
                return response()->json('Something went wrong.',402);
            }
    
        }catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return response()->json('Something went wrong '.$ex->getMessage());
        }
        /* Add action Log */
        CustomHelpers::userActionLog($action='Call Data Add Sold',$call_id,0);
        DB::commit();
        return response()->json('Successfully Added in Sold Call Data.');
    }
    public function callDataChangeLevelDB(Request $request){
        $this->validate($request,[
            'call_level'=>'required|numeric|gt:0|lt:4'
        ],[
            'call_level.required'=> 'This Field is required.'
        ]);
        $call_data_id = $request->input('call_data_id');
        $call_data_detail_id = $request->input('call_data_detail_id');

        $call_level = $request->input('call_level');
        try{
            DB::beginTransaction();
            $max = $call_data_detail_id;
            // CallDataDetails::where('call_data_id',$call_data_id)->max('id');
            if($max){
                // update Call Level
                $update = CallDataDetails::where('id',$max)
                                        ->update([
                                            'call_level'    =>  $call_level
                                        ]);
                if(!$update){
                    DB::rollback();
                    return back()->with('error','Something went wrong.');
                }
            }else{
                return back()->with('error','Call Data Details Not Found.');
            }
    
        }catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return back()->with('error','Something went wrong '.$ex->getMessage())->withInput();
        }
        /* Add action Log */
        CustomHelpers::userActionLog($action='Update Call Data Level',$max,0);
        DB::commit();
        return back()->with('success','Successfully Updated.')->withInput();
    }
    public function callDataCashFinacneDB(Request $request){
        $this->validate($request,[
            'cash_finance'=>'required|numeric|gt:0|lt:3',
            'finance_company_name' => 'required_if:cash_finance,==,2',
            'finance_name' => 'required_if:cash_finance,==,2'
        ],[
            'cash_finance.required'=> 'This Field is required.',
            'finance_company_name.required_if'=> 'This Field is required.',
            'finance_name.required_if'=> 'This Field is required.'
        ]);
        $call_data_id = $request->input('call_data_id');
        $call_data_detail_id = $request->input('call_data_detail_id');
        $cash_finance = $request->input('cash_finance');
        $company = $request->input('finance_company_name');
        $financeExecutive_name = $request->input('finance_name');
        try{
            DB::beginTransaction();
            $max = $call_data_detail_id;
            // CallDataDetails::where('call_data_id',$call_data_id)->max('id');
            if($max){
                // update Call Level
                $update = CallDataDetails::where('id',$max)
                                        ->update([
                                            'cash_finance'    =>  $cash_finance,
                                            'company_id' =>$company,
                                            'executive_id'=>$financeExecutive_name
                                        ]);
                if(!$update){
                    DB::rollback();
                    return back()->with('error','Something went wrong.');
                }
            }else{
                return back()->with('error','Call Data Details Not Found.');
            }
    
        }catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return back()->with('error','Something went wrong '.$ex->getMessage())->withInput();
        }
        /* Add action Log */
        CustomHelpers::userActionLog($action='Update Call Data Cash-Finance',$max,0);
        DB::commit();
        return back()->with('success','Successfully Updated.')->withInput();
    }
    public function callDataCsdEnquiryDB(Request $request){
        
        $call_data_id = $request->input('call_data_id');
        $call_data_detail_id = $request->input('call_data_detail_id');
        try{
            DB::beginTransaction();
            $max = $call_data_detail_id;
            if($max){
                // update Call Level
                $update = CallDataDetails::where('id',$max)
                                        ->update([
                                            'other_action->CsdEnquiry'    =>  'Yes'
                                        ]);
                if(!$update){
                    DB::rollback();
                    return back()->with('error','Something went wrong.');
                }
            }else{
                return back()->with('error','Call Data Details Not Found.');
            }
    
        }catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return back()->with('error','Something went wrong '.$ex->getMessage())->withInput();
        }
        /* Add action Log */
        CustomHelpers::userActionLog($action='Call Data CSD Enquiry Create.',$max,0);
        DB::commit();
        return back()->with('success','Enquiry Created Successfully.')->withInput();
    }
    public function callDataServiceEnquiryDB(Request $request){
        
        $call_data_id = $request->input('call_data_id');
        $call_data_details_id = $request->input('call_data_detail_id');
        try{
            DB::beginTransaction();
            $getcall_detail = CallDataDetails::where('id',$call_data_details_id)->get()->first();
            if($getcall_detail){
                // $find_call_users = CustomHelpers::find_callData_users();
                $find_call_users = CustomHelpers::find_users_callData(3,date('Y-m-d'));

                if($find_call_users[0]){
                    // assign users id
                    $all_data['assigned_to']  =   $find_call_users[1];
                }
                $insert_service = CallDataDetails::insertGetId([
                    'call_data_id'  =>  $getcall_detail['call_data_id'],
                    'assigned_to' =>    $all_data['assigned_to'],
                    'type' => 3,
                    'query' =>  $getcall_detail['query'],
                    'query_status'  =>  'Open',
                    'other_action'  => '{}'
                ]);
                // update Call Level
                $update = CallDataDetails::where('id',$getcall_detail['id'])
                                        ->update([
                                            'other_action->ServiceEnquiry'    =>  $insert_service
                                        ]);
                if(!$update){
                    DB::rollback();
                    return back()->with('error','Something went wrong.');
                }
            }else{
                return back()->with('error','Call Data Details Not Found.');
            }
    
        }catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return back()->with('error','Something went wrong '.$ex->getMessage())->withInput();
        }
        /* Add action Log */
        CustomHelpers::userActionLog($action='Call Data Service Enquiry Create.',$getcall_detail['id'],0);
        DB::commit();
        return back()->with('success','Enquiry Created Successfully.')->withInput();
    }
    public function callDataInsuredDB(Request $request){
        $call_data_id = $request->input('call_data_id');
        $call_data_detail_id = $request->input('call_data_detail_id');
        try{
                DB::beginTransaction();
                
                $get_cdd_detail = CallDataDetails::where('id',$call_data_detail_id)
                                                ->where('query_status','Open')->first();
                // update Call Level
                if(isset($get_cdd_detail)){
                        $update = CallDataDetails::where('id',$call_data_detail_id)
                                        ->update([
                                            'other_action->Insured'    =>  'Yes',
                                            'closing_date'  =>  date('Y-m-d'),
                                            'query_status'  =>  'Closed'
                                        ]);
                        if(!$update){
                            DB::rollback();
                            return back()->with('error','Something went wrong.');
                        }
                        $insert_record = CallDataRecord::insertGetId([
                            'call_data_details_id'  =>  $call_data_detail_id,
                            'call_data_id'  =>  $call_data_id,
                            'call_status'   =>  'InsuredClosed',
                            'remark'    =>  'Call Close by Add Insured Action',
                            'updated_by'    =>  Auth::id()
                        ]);
                }else{
                    DB::rollback();
                    return response()->json('Call Already Closed.',402);
                }
                
        }catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return back()->with('error','Something went wrong '.$ex->getMessage())->withInput();
        }
        /* Add action Log */
        CustomHelpers::userActionLog($action='Call Data Insured Create.',$call_data_detail_id,0);
        DB::commit();
        return back()->with('success','Insured Done Successfully.')->withInput();
    }
    public function callDataSaleEnquiryDB(Request $request){
        $call_data_id = $request->input('call_data_id');
        $call_data_detail_id = $request->input('call_data_detail_id');
        try{
            DB::beginTransaction();
            $max = $call_data_detail_id;
            if($max){
                // update Call Level
                $update = CallDataDetails::where('id',$max)
                                        ->update([
                                            'other_action->SaleEnquiry'    =>  'Yes'
                                        ]);
                if(!$update){
                    DB::rollback();
                    return back()->with('error','Something went wrong.');
                }
            }else{
                return back()->with('error','Call Data Details Not Found.');
            }
    
        }catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return back()->with('error','Something went wrong '.$ex->getMessage())->withInput();
        }
        /* Add action Log */
        CustomHelpers::userActionLog($action='Call Data Sale Enquiry Create.',$max,0);
        DB::commit();
        return back()->with('success','Enquiry Created Successfully.')->withInput();
    
    }

    public function transaction_by_customer(Request $request, $transactionType){
        $customer_mobile = $request->input('cust_number');
        DB::enableQueryLog();
        if($transactionType == 'sale'){

            $transactionDetails = Sale::leftjoin('customer','sale.customer_id','customer.id')
                    ->leftjoin('sale_order','sale_order.sale_id','sale.id')
                    ->leftjoin('product','product.id','sale.product_id')
                    ->select('sale.sale_no', 'sale.total_amount' ,'sale_order.product_frame_number',
                    DB::raw('Concat(product.model_name,"(",product.model_variant,")") as product_name'))
                    ->where('customer.mobile',$customer_mobile)
                    ->orderBy('sale.id','DESC')->get()->toArray();

            $head =['Sale Number','Total Amount','Frame Number','Product'];

        }elseif($transactionType == 'otcsale'){

            $transactionDetails = OtcSale::leftjoin('customer','otc_sale.customer_id','customer.id')
                    ->leftjoin('sale_order','sale_order.sale_id','otc_sale.sale_id')
                    ->leftjoin('sale','otc_sale.sale_id','sale.id')
                    ->leftjoin('otc_sale_detail','otc_sale_detail.otc_sale_id','otc_sale.id')
                    ->leftjoin('part','part.id','otc_sale_detail.part_id')
                    ->select('sale.sale_no', 'otc_sale.total_amount' ,'sale_order.product_frame_number',
                        DB::raw(' GROUP_CONCAT(part.name) as parts'))
                    ->where('customer.mobile',$customer_mobile)
                    ->orderBy('otc_sale.id','DESC')->get()->toArray();

            $head =['Sale Number','Total Amount','Frame Number','Parts'];
        }elseif($transactionType == 'service'){

            $transactionDetails = ServiceModel::leftjoin('sale','service.sale_id','sale.id')
                    ->leftjoin('sale_order','sale_order.sale_id','service.sale_id')
                    ->leftjoin('customer','service.customer_id','customer.id')
                    ->leftjoin('job_card','service.id','job_card.service_id')
                    ->leftjoin('product','product.id','service.product_id')
                    ->select('sale.sale_no', 'job_card.total_amount' ,'sale_order.product_frame_number','service.frame',DB::raw('Concat(product.model_name,"(",product.model_variant,")") as product_name'))
                    ->where('customer.mobile',$customer_mobile)
                    ->orderBy('service.id','DESC')->get()->toArray();

            $head =['Sale Number','Total Amount','Frame Number','Product'];

        }elseif($transactionType == 'insurance'){

            $transactionDetails = Insurance::leftjoin('sale','insurance.sale_id','sale.id')
                    ->leftjoin('sale_order','sale_order.sale_id','insurance.sale_id')
                    ->leftjoin('customer','sale.customer_id','customer.id')
                    ->leftjoin('insurance_company','insurance_company.id','insurance.insurance_co')
                    ->select('sale.sale_no', 'insurance.insurance_amount' ,'sale_order.product_frame_number','insurance_company.name',
                        DB::raw('DATE_FORMAT(insurance.insurance_date,"%d-%m-%Y") as insurance_date'),
                        'insurance.insurance_type',
                        'insurance.insurance_name',
                        DB::raw("(CASE WHEN insurance.self_insurance = 1 THEN 'Yes' ELSE 'No' END) as self_inc"))
                    ->where('customer.mobile',$customer_mobile)
                    ->orderBy('insurance.id','DESC')->get()->toArray();

            $head =['Sale Number','Total Amount','Frame Number','Company','Date','Type','Name','Self Insurance'];
        }
        // print_r(DB::getQueryLog());die();
        $data = array('head'=>$head,'body'=>$transactionDetails);
        echo json_encode($data);
    }
}   
