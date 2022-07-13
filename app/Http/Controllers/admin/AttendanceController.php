<?php

namespace App\Http\Controllers\admin;

use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Model\Userlog;
use App\Model\Store;
use App\Model\Users;
use\App\Model\Attendance;
use \App\Model\ShiftTiming;
use\App\Model\UserDetails;
use \App\Custom\CustomHelpers;
use Auth;
class AttendanceController extends Controller
{
 
    public function late_earlygoing_list() {

        $store = Store::whereIn('id',CustomHelpers::user_store())
                    ->select('id',DB::raw('concat(store.name,"-",store.store_type) as name'))->get();
        $users= Users::select('emp_id as id','name','middle_name','last_name')->get();
        $data=array('users'=>$users,'store'=>$store,'layout'=>'layouts.main');
        return view('admin.hr.late_earlygoing_list', $data); 
    }

    public function late_earlygoing_list_api(Request $request){
        $search = $request->input('search');
        $user_name=$request->input('store_name');
        $store_name = $request->input('storename');
        $year=$request->input('year');
        $month=$request->input('month');
        $date=$request->input('date');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
        $store = CustomHelpers::user_store();
        
        $user = Auth::user();
        $user_type = $user['user_type'];
        $role = $user['role'];
        if($user_type=="superadmin" || $role=='HRDManager'){

          $api_data= Attendance::leftJoin('users','attendance.emp_id','users.emp_id')
            ->leftJoin('store','attendance.store_id','store.id')
            ->leftjoin('user_details','user_details.user_id','users.id')
            ->leftjoin('shift_timing','user_details.shift_timing_id','shift_timing.id')
            ->orwhere('attendance.late_by','!=','00:00:00')
            ->orwhere('attendance.early_going','!=','00:00:00')
            ->select(
                 DB::raw('concat(users.name," ",ifnull( users.middle_name," ")," ",ifnull( users.last_name," ")) as name'),
                'users.emp_id',
                'attendance.id',
                'users.id as user_id',
                'attendance.status',
                'attendance.intime',
                'attendance.outtime',
                'attendance.totaltime',
                'attendance.late_by',
                'attendance.half_day',
                'attendance.early_going',
                'attendance.late_early_status',
                'attendance.approve',
                'attendance.date as attendance_date',
                DB::raw("MonthName(attendance.date) date"),
                DB::raw("DATEDIFF(now(),attendance.date) as days"),
                DB::raw('concat(store.name,"-",store.store_type) as store_name')
                
            );

        }else{

            $api_data= Attendance::leftJoin('users','attendance.emp_id','users.emp_id')
            ->leftJoin('store','attendance.store_id','store.id')
            ->whereIn('users.store_id',$store)
            ->orwhere('attendance.late_by','!=','00:00:00')
            ->orwhere('attendance.early_going','!=','00:00:00')
            ->select(
                 DB::raw('concat(users.name," ",ifnull( users.middle_name," ")," ",ifnull( users.last_name," ")) as name'),
                'users.emp_id',
                'users.id as user_id',
                'attendance.status',
                'attendance.id',
                'attendance.intime',
                'attendance.outtime',
                'attendance.totaltime',
                'attendance.half_day',
                'attendance.late_by',
                'attendance.early_going',
                'attendance.late_early_status',
                'attendance.approve',
                'attendance.date as attendance_date',
                 DB::raw("MonthName(attendance.date) date"),
                 DB::raw("DATEDIFF(now(),attendance.date) as days"),
                 DB::raw('concat(store.name,"-",store.store_type) as store_name')
            );
        }

             if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('attendance.id','like',"%".$serach_value."%")
                    ->orwhere('users.name','like',"%".$serach_value."%")
                    ->orwhere('attendance.status','like',"%".$serach_value."%")
                    ->orwhere('attendance.intime','like',"%".$serach_value."%")
                    ->orwhere('attendance.outtime','like',"%".$serach_value."%")
                    ->orwhere('attendance.totaltime','like',"%".$serach_value."%")
                    ->orwhere('attendance.late_by','like',"%".$serach_value."%")
                    ->orwhere('attendance.early_going','like',"%".$serach_value."%")
                    ->orwhere('attendance.emp_id','like',"%".$serach_value."%")
                    ->orwhere('attendance.half_day','like',"%".$serach_value."%")
                    ->orwhere('attendance.late_early_status','like',"%".$serach_value."%")
                    ->orwhere('store.name','like',"%".$serach_value."%")
                    
                    ;
                });
            }
             if(!empty($store_name))
            {
               $api_data->where(function($query) use ($store_name){
                        $query->where('attendance.store_id','=',$store_name);
                    });
            }

            if(isset($year) )
            {
               $api_data->where(function($query) use ($year){
                        $query->whereYear('attendance.date', date($year));
                    });
            }
             if(isset($month) )
            {
               $api_data->where(function($query) use ($month){
                        $query->whereMonth('attendance.date', date($month));
                    });
            }
            if(isset($month) && empty($year))
            {
               $api_data->where(function($query) use ($month){
                        $query->whereYear('attendance.date', date('Y'))
                        ->whereMonth('attendance.date', date($month));
                    });
            }

            if(isset($date) )
            {
               $api_data->where(function($query) use ($date){
                        $query->where('attendance.date', date($date));
                    });
            }
             if(isset($user_name) )
            {
               $api_data->where(function($query) use ($user_name){
                        $query->where('attendance.emp_id',$user_name);
                    });               
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                    'users.emp_id',
                    'name',
                    'attendance.status',
                    'attendance.intime',
                    'attendance.outtime',
                    'attendance.totaltime',
                    'attendance.late_by',
                    'attendance.early_going',
                    'attendance.half_day',
                    'attendance.date',
                    'store_name',
                    'attendance.late_early_status'
                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else 
                $api_data->orderBy('attendance.id','desc');      

        $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 

        return json_encode($array);
    }

    public function late_earlygoing_approve(Request $request)
    {
        $id = $request->input('id');
        $late = $request->input('late');
        $early_going = $request->input('early_going');
       
        $getcheck = Attendance::where('id',$id)->get()->first();
        
        if($getcheck){
            if($late!='' && $early_going!=''){
                 $data = [
                   'approve'=>$late.",".$early_going
                ];
            }else if($late!=''){
                $data = [
                   'approve'=>$late
                ];
            }else if ($early_going!='') {
                 $data = [
                    'approve'=>$early_going
                ];
            }
            $attendance = Attendance::where('id',$id)->update($data);
            if($attendance){
                CustomHelpers::userActionLog($action='Late Early going Approve',$id,0);
                return 'success'; 
              }else{
                return 'error'; 
              }
        }else{
            return 'Id not Exist';
        }    
    }

}
