<?php

namespace App\Http\Controllers\admin;
use Illuminate\Http\Request;
use \stdClass;
use \App\Model\Store;

use \App\Custom\CustomHelpers;
use \App\Model\SaleAccessories;
use \App\Model\RtoModel;
use \App\Model\Payment;
use \App\Model\OrderPendingModel;

use \App\Model\Calling;
use \App\Model\CallingSummery;
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
use \App\Model\Users;
use \App\Model\HR_Leave;
use \App\Model\HolidayFestival;
use \App\Model\CallPriority;
use App\Model\Settings;

class CallSettingController extends Controller
{
    public function __construct()
    {
        //$this->middleware('auth');
    }

    public function callUsers() {

        $users = Users::whereIn('users.user_type',['admin','superadmin'])
                            ->select('users.id','users.name')
                            ->where('status',1)
                            ->orderBy('users.id')
                            ->get();
        $call_type = [
            'ThankYou' => 'Thank You',
            'PSF'   =>  'PSF',
            'Service'   =>  'Service',
            'Enquiry'   =>  'Enquiry',
            'Insurance' =>  'Insurance'        
        ];
        $data = [
            'layout' => 'layouts.main',
            'users' =>  $users,
            'call_type' =>  $call_type
        ];
        return view('admin.call.call_setting.call_users',$data);
    }

    public function callUsers_api(Request $request, $tab) {
        //DB::enableQueryLog();
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;


        $api_data = CallAssignSetting::leftJoin('users','call_assign_setting.user_id','users.id')
            ->select(
                'call_assign_setting.id',
                'call_assign_setting.type',
                'users.name as user_name',
                DB::raw("(select group_concat(concat(store.name,'-',store.store_type)) from store where FIND_IN_SET(store.id,users.store_id) ) as store_name"),
                'call_assign_setting.noc'
            );
            // print_r($api_data->get()->toArray());die;
            // print_r($api_data);die;
            if($tab == 'thankyou')
            {
                $api_data = $api_data->where(function($query) {
                    $query->where('call_assign_setting.type','ThankYou');
                });
            }
            if($tab == 'psf'){
                $api_data = $api_data->where(function($query) {
                    $query->where('call_assign_setting.type','PSF');
                });
            }
            if($tab == 'service'){
                $api_data = $api_data->where(function($query) {
                    $query->where('call_assign_setting.type','Service');
                });
            }
            if($tab == 'enquiry'){
                $api_data = $api_data->where(function($query) {
                    $query->where('call_assign_setting.type','Enquiry');
                });
            }
            if($tab == 'insurance'){
                $api_data = $api_data->where(function($query) {
                    $query->where('call_assign_setting.type','Insurance');
                });
            }
            if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('users.name','like',"%".$serach_value."%")
                    ->orwhere(DB::raw("(select concat(store.name,'-',store.store_type) from store where FIND_IN_SET(store.id,users.store_id) )"),'like',"%".$serach_value."%")
                    ->orwhere('call_assign_setting.noc','like',"%".$serach_value."%")  
                    ;
                });
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                    'user_name',
                    'store_name',
                    'noc'
                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                 $api_data->orderBy('user_name','asc');      
        
        $count = $api_data->count();
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return response()->json($array);
    }

    public function callUsersUpdateNoc(Request $request){
        try{
            DB::beginTransaction();
            $call_setting_id = $request->input('call_setting_id');
            $call_type = $request->input('call_type');
            $noc = $request->input('noc');

            $valid_type = ['ThankYou','PSF','Service','Enquiry','Insurance'];
            if(!in_array($call_type,$valid_type)){
                return response()->json([false,'Wrong Call Type.'],401);
            }
            $find = CallAssignSetting::where('id',$call_setting_id)->where('type',$call_type)->first();
            if(!isset($find->id)){
                return response()->json([false,'Call Users Data Not Found.'],401);
            }
            $update_data = [
                'noc'   =>  $noc
            ];
            $update_callSetting = CallAssignSetting::where('id',$call_setting_id)
                                            ->update($update_data);
            if(!$update_callSetting){
                DB::rollback();
                return response()->json([false,'Something Went Wrong, Call Setting Not Update.'],401);
            }
        }
        catch(\Illuminate\Database\QueryException $ex) {
            DB::rollBack();
            return response()->json([false,'some error occurred'.$ex->getMessage()],401);
        }
        /* Add action Log */
        CustomHelpers::userActionLog($action='Call Setting',$call_setting_id,0);
        DB::commit();
        return response()->json([true,'Call Setting Updated Successfully.']);
    }

    public function callUsersAddUsers(Request $request){
        try{
            $validator = Validator::make($request->all(),[
                'call_type' =>  'required',
                'select_users.*'    =>  'required',
                'noc.*' =>  'required'
            ],[
                'call_type.required' => 'This Field is Required.',
                'select_users.*.required' => 'This Field is Required.',
                'noc.*.required'    =>  'This Field is Required.'
            ]);

            $users = $request->input('select_users');
            $call_type = $request->input('call_type');
            $arr = [$users,$call_type];

            $validator->after(function ($validator) use($arr) {
                $find = CallAssignSetting::whereIn('user_id',$arr[0])->where('type',$arr[1])->pluck('user_id')->toArray();
                if(isset($find[0])){
                    foreach($arr[0] as $k => $v){
                        $check = in_array($v,$find);
                        if($check){
                            $validator->errors()
                            ->add('select_users.'.$k, 'This User Already in List.');
                        }
                    }
                }     
            });
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            DB::beginTransaction();
            $noc = $request->input('noc');

            $valid_type = ['ThankYou','PSF','Service','Enquiry','Insurance'];
            if(!in_array($call_type,$valid_type)){
                return back()->with('error','Select Correct Call Type.')->withInput();
            }
            $insertData = [];
            foreach($users as $key => $val){
                $insertData[] = [
                    'type'  =>  $call_type,
                    'user_id'   =>  $val,
                    'noc'   =>  $noc[$key]
                ];
            }
            $insert = CallAssignSetting::insert($insertData);
            
        }
        catch(\Illuminate\Database\QueryException $ex) {
            DB::rollBack();
            return back()->with('error','some error occurred'.$ex->getMessage())->withInput();
        }
        /* Add action Log */
        CustomHelpers::userActionLog($action='Call Setting Add User',0,0);
        DB::commit();
        return back()->with('success','Successfully Added.');
    }

    public function callPriority() {

        $call_users = CallAssignSetting::groupBy('user_id')->orderBy('user_id')->pluck('user_id')->toArray();
        $users = Users::whereIn('users.id',$call_users)
                            ->where('status',1)
                            ->select('users.id','users.name')
                            ->orderBy('users.id')
                            ->get();
        $data = [
            'layout' => 'layouts.main',
            'users' =>  $users
        ];
        return view('admin.call.call_setting.callPriority',$data);
    }
    public function callPriority_DB(Request $request){
        try{
            $validator = Validator::make($request->all(),[
                'select_users'    =>  'required'
            ],[
                'select_users.required' => 'This Field is Required.'
            ]);

            $user = $request->input('select_users');
            $items = $request->input('items');

            $validator->after(function ($validator) use($items) {
                if(empty($items)){
                    $validator->errors()
                    ->add('items', 'Required to Priority Data.');
                }elseif(count($items) <= 1){
                    $validator->errors()
                    ->add('items', 'Priority Data Should be Greater than 1.');
                }
            });
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            DB::beginTransaction();

            $valid_type = ['ThankYou','PSF','Service','Enquiry','Insurance'];
            $updateData = [];
            foreach($items as $k => $call_type){
                if(!in_array($call_type,$valid_type)){
                    return back()->with('error',$call_type.' :- Not Found in Master.')->withInput();
                }
                $updateData[$call_type] = $k+1;
            }
            // upfate & insert
            foreach($updateData as $call_type => $priority){
                $find = CallPriority::where('user_id',$user)->where('call_type',$call_type) 
                                        ->first();
                if(isset($find->id)){
                    // upadte
                    $updatePriority = CallPriority::where('user_id',$user)->where('call_type',$call_type) 
                                        ->update(['priority'    =>  $priority]);
                }else{
                    // insert
                    $insertPriority = CallPriority::insertGetId([
                        'user_id'   =>  $user,
                        'call_type' =>  $call_type,
                        'priority'  =>  $priority
                    ]);
                }
            } 
        }
        catch(\Illuminate\Database\QueryException $ex) {
            DB::rollBack();
            return back()->with('error','some error occurred'.$ex->getMessage())->withInput();
        }
        /* Add action Log */
        CustomHelpers::userActionLog($action='Call Priority Setting',0,0);
        DB::commit();
        return back()->with('success','Successfully Updated Call Priority.')->withInput();
    }
    public function getCallType(Request $request){
        try{
            $user = $request->input('user');
            if(!$user){
                return response()->json('Compulsory, to Select User.',401);
            }
            $get = CallAssignSetting::where('user_id',$user)->groupBy('type')
                                ->orderBy('type','ASC')->pluck('type')->toArray();
            if(isset($get[0])){
                $call_priority = CallPriority::where('user_id',$user)->whereIn('call_type',$get)->orderBy('priority')->pluck('call_type')->toArray();
                $data = [];
                foreach($get as $in => $call_type){
                    $find = array_search($call_type,$call_priority);
                    if(is_numeric($find)){
                        if($find >= 0 ){
                            array_splice( $data, $find, 0, $call_type ); 
                        }
                    }else{
                        array_push($data,$call_type);
                    }
                }
                return response()->json([true,$data]);
            }else{
                return response()->json([false,'Not-Found Any Call Type.']);
            }
        }
        catch(\Illuminate\Database\QueryException $ex) {
            return response()->json('some error occurred'.$ex->getMessage(),401);
        }
    }
    public function autoCallAssign(){
        $today = date('Y-m-d');
        try{
            $call_type = ['PSF','ThankYou','Service','Enquiry','Insurance'];
            // $call_type = ['Enquiry','Insurance'];
            $call_data = [];
            $get_user = Users::where('status',1)
                                ->leftjoin('call_assign_setting','call_assign_setting.user_id','users.id')
                                ->leftJoin('hr__leave',function($join){
                                    $join->on('hr__leave.user_id','=','users.emp_id')
                                        ->whereBetween(DB::raw('CURRENT_DATE'),['hr__leave.start_date','end_date']);
                                })
                                ->whereIn('call_assign_setting.type',$call_type)
                                ->whereNull('hr__leave.id')
                                ->whereNotNull('call_assign_setting.id')
                                ->select(
                                    'users.id as user_id',
                                    'call_assign_setting.type',
                                    'call_assign_setting.noc'
                                )->groupBy('call_assign_setting.type')
                                ->groupBy('call_assign_setting.user_id')->get()->toArray();
            // print_r($get_user);die;
            $user_data = [];
            foreach($get_user as $k => $v){
                if(isset($user_data[$v['type']])){
                    $user_data[$v['type']][$v['user_id']] = ['noc' => $v['noc']];
                }else{
                    $user_data[$v['type']] = [];
                    $user_data[$v['type']][$v['user_id']] = ['noc' => $v['noc']];
                }
            }
            // print_r($user_data);die;
            foreach($user_data as $call_type => $user_info){
                // find in calling data
                $find_calling = Calling::where('call_type',$call_type)
                                        ->where('assigned_to',0)
                                        ->where('call_status','pending')
                                        ->pluck('id')
                                        ->toArray();
                $no_calling = count($find_calling);
                $no_users = count($user_info);
                // if($no_calling > 0 && $call_type == 'PSF'){
                if($no_calling > 0){
                    if($no_calling >= $no_users){
                        // echo $no_calling.'<br>';
                        $each_user = intval($no_calling/$no_users);
                        $pending_call = $no_calling-$each_user*$no_users;
                        foreach($user_info as $user_id => $oth_info){
                            $range = $each_user;
                            if($each_user > $oth_info['noc']){
                                $range = $oth_info['noc'];
                                $pending_call = $pending_call+$each_user-$oth_info['noc'];
                            }
                            $user_data[$call_type][$user_id]['calling_id'] = array_slice($find_calling,0,$range);
                            array_splice($find_calling,0,$range);
                        }
                        // print_r($user_data[$call_type]);
                        if($pending_call > 0){
                            $this_arr = $this->divideCallEqual_calling($find_calling,$user_data,$pending_call,$call_type);
                            $user_data[$call_type] = $this_arr;
                            // echo $pending_call.'<br>';die;
                            // print_r($user_data);die;
                        }
                    }else{
                        $this_arr = $this->divideCallEqual_calling($find_calling,$user_data,$no_calling,$call_type);
                        $user_data[$call_type] = $this_arr;
                    }
                }

                // find in call data
                $find_call = CallData::where('call_type',$call_type)
                                        ->where('assigned_to',0)
                                        ->where('status','Pending')
                                        ->pluck('id')
                                        ->toArray();
                // print_r($find_call);die;
                // print_r($user_info);
                $no_call = count($find_call);
                $no_users = count($user_info);
                // print_r($find_calling);die;
                // if($no_call > 0 && $call_type == 'PSF'){
                if($no_call > 0){
                    if($no_call >= $no_users){
                        // echo $no_call.'<br>';
                        $each_user = intval($no_call/$no_users);
                        // echo $each_user.'<br>';
                        $pending_call = $no_call-$each_user*$no_users;
                        $user_full = [];
                        // print_r($pending_call);
                        foreach($user_info as $user_id => $oth_info){
                            if(!in_array($user_id,$user_full) && count($find_call) > 0){
                                $old_assign = 0;
                                if(isset($user_data[$call_type][$user_id]['call_id'])){
                                    $old_assign = count($user_data[$call_type][$user_id]['call_id']); 
                                }else{
                                    $user_data[$call_type][$user_id]['call_id'] = [];
                                }
                                $noc = $oth_info['noc'];
                                // echo $noc.'<br>';
                                if($noc > $old_assign){
                                    $current_noc = $noc-$old_assign;
                                    $range = $each_user;
                                    if($each_user > $current_noc){
                                        $range = $current_noc;
                                        $pending_call = $pending_call+$each_user-$current_noc;
                                    }
                                    // echo $range.'<br>';
                                    $new_arr = array_slice($find_call,0,$range);
                                    // print_r($new_arr);
                                    $user_data[$call_type][$user_id]['call_id'] = array_merge($user_data[$call_type][$user_id]['call_id'],$new_arr);

                                    array_splice($find_call,0,$range);
                                }
                                else{
                                    array_push($user_full,$user_id);
                                }
                            }
                        }
                        if($no_users > count($user_full)){
                            if($pending_call > 0){
                                $this_arr = $this->divideCallEqual_call($find_call,$user_data,$pending_call,$call_type);
                                $user_data[$call_type] = $this_arr;
                            }
                        }
                    }else{
                        $this_arr = $this->divideCallEqual_call($find_call,$user_data,$no_call,$call_type);
                        $user_data[$call_type] = $this_arr;
                    }
                }

                // save in database
                foreach($user_data[$call_type] as $user_id => $oth_info){
                    // print_r($oth_info);
                    $calling_ids = [];
                    if(isset($oth_info['calling_id'])){
                        $calling_ids = $oth_info['calling_id'];
                        // for calling data
                        $assign_calling = Calling::whereIn('id',$calling_ids)
                                            ->update([
                                                'assigned_to'   =>  $user_id
                                            ]);
                    }
                    $call_ids = [];
                    if(isset($oth_info['call_id'])){
                        $call_ids = $oth_info['call_id'];
                        // for calling data
                        $assign_call = CallData::whereIn('id',$call_ids)
                                            ->update([
                                                'assigned_to'   =>  $user_id
                                            ]);
                    }
                }
            }
        }
        catch(\Illuminate\Database\QueryException $ex) {
            return response()->json('some error occurred'.$ex->getMessage(),401);
        }
        // print_r($user_data);
        // die;
        return $user_data;
    }

    public function divideCallEqual_calling($find_calling,$user_data,$pending_call,$call_type){
        // print_r($user_data);die;
        $sub_data = $user_data[$call_type];
        $user_full = [];
        $total_user = count($user_data[$call_type]);
        while($pending_call > 0){
            foreach($sub_data as $user_id => $oth_info){
                if(!in_array($user_id,$user_full) && $pending_call > 0){
                    if(!isset($sub_data[$user_id])){
                        $sub_data[$user_id] = $oth_info;
                    }
                    $old_assign = 0;
                    if(isset($oth_info['calling_id'])){
                        $old_assign = count($sub_data[$user_id]['calling_id']); 
                    }else{
                        $sub_data[$user_id]['calling_id'] = [];
                    }
                    $noc = $oth_info['noc'];
                    if($noc > $old_assign){
                        $new_arr = array_slice($find_calling,0,1);
                        array_push($sub_data[$user_id]['calling_id'],$new_arr[0]);
                        array_splice($find_calling,0,1);
                        $pending_call--;
                    }
                    else{
                        array_push($user_full,$user_id);
                    }
                }
            }
            if($total_user == count($user_full)){
                break;
            }
        }
        // print_r($user_full);
        // echo $pending_call;
        // print_r($sub_data);die;
        return $sub_data;
    }
    public function divideCallEqual_call($find_calling,$user_data,$pending_call,$call_type){
        // print_r($user_data);die;
        $sub_data = $user_data[$call_type];
        $user_full = [];
        $total_user = count($user_data[$call_type]);
        while($pending_call > 0){
            foreach($sub_data as $user_id => $oth_info){
                if(!in_array($user_id,$user_full) && $pending_call > 0){
                    if(!isset($sub_data[$user_id])){
                        $sub_data[$user_id] = $oth_info;
                    }
                    $old_assign = 0;
                    if(isset($oth_info['call_id'])){
                        $old_assign = count($sub_data[$user_id]['call_id']); 
                    }else{
                        $sub_data[$user_id]['call_id'] = [];
                    }
                    $noc = $oth_info['noc'];
                    if($noc > $old_assign){
                        $new_arr = array_slice($find_calling,0,1);
                        array_push($sub_data[$user_id]['call_id'],$new_arr[0]);
                        array_splice($find_calling,0,1);
                        $pending_call--;
                    }
                    else{
                        array_push($user_full,$user_id);
                    }
                }
            }
            if($total_user == count($user_full)){
                break;
            }
        }
        // print_r($user_full);
        // echo $pending_call;
        // print_r($sub_data);die;
        return $sub_data;
    }

    // manual call allowed setting
    public function manualCallAllowed() {

        $allowed_user = Settings::where('name','ManuallyCallAllowed')->pluck('value')->toArray();
        if(count($allowed_user) > 0){
            if(!empty($allowed_user[0])){
                $allowed_user = explode(',',$allowed_user[0]);
            }else{
                $allowed_user = [];
            }
        }

        $users = Users::where('status',1)
                        ->whereIn('user_type',['superadmin','admin'])
                        ->select('users.id','users.name')
                        ->orderBy('users.id')
                        ->get();
        $data = [
            'layout' => 'layouts.main',
            'users' =>  $users,
            'allowed_user'  =>  $allowed_user
        ];
        return view('admin.call.call_setting.manualCallAllowed',$data);
    }
    public function manualCallAllowed_DB(Request $request){
        try{
            $user_ids = $request->input('users');
            if(!empty($user_ids)){
                $user_ids = join(',',$user_ids);
            }
            DB::beginTransaction();
            $setting_id = 0;
            $allowed_user = Settings::where('name','ManuallyCallAllowed')->select('id','value')->first();
            if(isset($allowed_user->id)){
                // update
                $setting_id = $allowed_user->id;
                $updateSetting = Settings::where('id',$setting_id)
                                            ->update([
                                                'value' =>  $user_ids
                                            ]);
            }else{
                // insert
                $setting_id = Settings::insertGetId([
                    'name'  =>  'ManuallyCallAllowed',
                    'value' =>  $user_ids,
                    'comment'   =>  'in value enter user_id comma seperated'
                ]);
            }
        }
        catch(\Illuminate\Database\QueryException $ex) {
            DB::rollBack();
            return back()->with('error','some error occurred'.$ex->getMessage())->withInput();
        }
        /* Add action Log */
        CustomHelpers::userActionLog($action='Manually Call Allowed Setting',$setting_id,0);
        DB::commit();
        return back()->with('success','Successfully Updated.')->withInput();
    }
}   
