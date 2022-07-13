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
use \App\Model\HiRise;
use \App\Model\Insurance;
use \App\Model\SaleOrder;
use \App\Model\RtoModel;
use \App\Model\Payment;
use \App\Model\OrderPendingModel;
use \App\Model\BookingModel;
use \App\Model\Bay;
use \App\Model\Master;
use Illuminate\Support\Facades\DB;
use Auth;
use Hash;
use \Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\Import;
use App\Model\BayAllocation;
use PhpParser\Node\Stmt\Foreach_;

class BayController extends Controller
{
    public function __construct()
    {
        //$this->middleware('auth');
    }
    
    public function createBay() {
        DB::enableQueryLog();
        $machanicdata = Bay::rightJoin('users','users.id','bay.user_id')->where('users.role','Employee')->where('bay.user_id',NULL)->select('users.name as username','users.id as user_id')->get();
        $store = Store::whereIn('id',CustomHelpers::user_store())
                    ->select('id',DB::raw('concat(store.name,"-",store.store_type) as name'))->get();
        $type = Master::where('type','job_card_type')->get();
        $data = array(
            'type' => $type,
            'machanicdata' => $machanicdata,
            'store'=>$store,
            'layout' => 'layouts.main', 
        );
       return view('admin.bay.create_bay',$data);
    }

    public function createBay_DB(Request $request) {
        try {
            $this->validate($request,[
                'name'=>'required|unique:bay',
                'type'=>'required',
                'mechanic'=>'required',
                'start_time'=>'required',
                'end_time'=>'required',
                'status'=>'required',
                'store_name'=>'required',
                'skils'=>'required',
            ],[
                'name.required'=> 'This field is required.', 
                'type.required'=> 'This field is required.',
                'mechanic.required'=> 'This field is required.',
                'start_time.required'=> 'This field is required.',
                'end_time.required'=> 'This field is required.',
                'status.required'=> 'This field is required.',
                'store_name.required'=>'This field is required.',
                'skils.required'=>'This field is required.',
            ]);
            $start_time = $request->input('start_time');
            $skils = $request->input('skils');
            $start_time  = str_replace(' ','',$start_time);
            $end_time = $request->input('end_time');
            $end_time  = str_replace(' ','',$end_time);
              $baydata = Bay::insertGetId(
                [
                    'name' => $request->input('name'),
                    'type' => $request->input('type'),
                    'store_id'=>$request->input('store_name'),
                    'user_id'=> $request->input('mechanic'),
                    'status'=> $request->input('status'),
                    'skils'=>$request->input('skils'),
                    'start_time' => $start_time,
                    'end_time' => $end_time
                ]);
              if ($baydata == NULL) {
                   return redirect('/admin/bay/create')->with('error','Something went wrong .');
              }else{
                 /* Add action Log */
                  CustomHelpers::userActionLog($action='Create Bay',$baydata,0);
                 return redirect('/admin/bay/create')->with('success','Bay Successfully added .');
              }
        }  catch(\Illuminate\Database\QueryException $ex) {
            return redirect('/admin/bay/create')->with('error','some error occurred'.$ex->getMessage());
        }
    }

    public function bayList() {
       $data = array(
            'layout' => 'layouts.main', 
        );
       return view('admin.bay.bay_list',$data);
    }
    
    public function bayList_api(Request $request) {
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
        
      $api_data= Bay::leftJoin('users','bay.user_id','users.id')
                ->leftJoin('store','bay.store_id','store.id')
            ->select(
                'bay.id',
                'bay.name',
                'bay.type',
                'bay.start_time',
                'bay.end_time',
                DB::Raw('concat(store.name,"-",store.store_type) as store_name'),
                'bay.status',
                'bay.skils',
                'users.name as username'
            )
            -> groupBy('bay.id');
            $api_data->whereIn('bay.store_id',explode(',',Auth::user()->store_id));

            if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('bay.name','like',"%".$serach_value."%")
                    ->orwhere('store.name','like',"%".$serach_value."%")
                    ->orwhere('users.name','like',"%".$serach_value."%")
                    ->orwhere('bay.type','like',"%".$serach_value."%")                    
                    ->orwhere('bay.status','like',"%".$serach_value."%")
                    ->orwhere('bay.skils','like',"%".$serach_value."%")
                    ->orwhere('bay.start_time','like',"%".$serach_value."%")
                    ->orwhere('bay.end_time','like',"%".$serach_value."%")                    
                    ;
                });
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
               
                'bay.name',
                'store.name',
                'users.name',
                'bay.type',
                'bay.start_time',
                'bay.end_time',
                'bay.status',
                'bay.skils'
                
                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('bay.id','asc');      
        
        $count = count($api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }
    public function bayLeave(Request $request)
    {
        $bay_id = $request->input('bay_id');
        $date = $request->input('date');
        $date = CustomHelpers::showDate($date,'Y-m-d');
        // echo $bay_id.'  '.$date;die;
        // $date = date('Y-m-d');
        // $date = '2020-03-23';
        $current_time = date('H:i:s');
        try{
            DB::beginTransaction();
            $bay = BayAllocation::where('bay_id',$bay_id)
                    ->where('date',$date)->where(DB::raw('time(start_time)'),'>',$current_time)
                    ->where('status','pending')
                    ->select('id','start_time','end_time','date','job_card_id',
                            DB::raw('TIMEDIFF(end_time,start_time) as duration'))
                   ->orderBy(DB::raw('TIMEDIFF(end_time,start_time)'))->get();
            $updateBay = Bay::where('id',$bay_id)->update(['status'=>'inactive']);
            $baydata = Bay::where('id',$bay_id)->first();
            
            // print_r($bay);die;
            
            if(!empty($bay))
            {
                $error = 0;
                foreach($bay as $key => $val){
                    $flag = 0;
                    $estimate_start_time = $bay[$key]->start_time;
                    $estimate_end_time = $bay[$key]->end_time;
                    $jobCardId = $bay[$key]->job_card_id;
                    $duration = $bay[$key]->duration;
                    // $duration  = gmdate('H:i:s',$sduration);
                    $d1 = new DateTime(date('Y-m-d').' '.$duration);
                    $rtime1 = CustomHelpers::roundUpToMinuteInterval($d1,CustomHelpers::min_duration());
                    $duration = $rtime1->format('H:i');
                    //convert time to duration
                    $arr1 = explode(':',$duration);
                    $h = $arr1[0];
                    $m = $arr1[1];
                    $duration = (($h*60)+$m)*60;
                    $duration = gmdate('H:i',$duration);

                    $service = new Service();
                    $fix_alloc = $service->findExactSlotBay([],$estimate_start_time,$estimate_end_time,$jobCardId,$date,$baydata->store_id);
                    // $fix_alloc = 's';
                    if($fix_alloc == 'success')
                    {
                        $delete_pre_alloc = BayAllocation::where('id',$bay[$key]->id)->delete();
                        $flag = 1;
                    }
                    else{
                        $dynamic_alloc = $this->dynamic_alloc($jobCardId,$duration,$date,$current_time);
                        if($dynamic_alloc == 'success')
                        {
                            $delete_pre_alloc = BayAllocation::where('id',$bay[$key]->id)->delete();
                            $flag = 1;
                        }
                    }

                    if($flag == 0)
                    {
                        $error = 1;
                        break;
                    }

                }
                if($error == 1)
                {
                    DB::commit();
                    return redirect('/admin/bay/list')->with('error','Some Duration not be auto allocated pls go manually and update it. ');
                }
            }

        }
        catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return redirect('/admin/bay/list')->with('error','some error occurred'.$ex->getMessage());
        }
        DB::commit();
         /* Add action Log */
        CustomHelpers::userActionLog($action='Add Bay Leave',$bay_id,0);
        return back()->with('success','Successfully Leave Updated');

    }

    public function dynamic_alloc($jobCardId,$duration,$date,$current_time,$store_id)
    {

        $first_q = "SELECT `b`.`job_card_id` AS `job_card_id`,`b`.`id` AS `id`,`b`.`name` AS `name`,`b`.`store_id` AS `store_id`,`b`.`bay_status` AS `bay_status`,
                    `b`.`start` AS `start`,`b`.`end` AS `end`,`b`.`start_time` AS `start_time`,`b`.`end_time` AS `end_time`,`b`.`date` AS `date`,
                    TIMEDIFF(`b`.`end_time`, `b`.`start_time`) AS `booking_duration`,
                    IF(
                        `b`.`job_card_id` IS NULL,
                        'free',
                        'booked'
                    ) AS `status`
                FROM
                    (
                    SELECT
                        `b`.`job_card_id` AS `job_card_id`,
                        `bay`.`id` AS `id`,
                        `bay`.`name` AS `name`,
                        `bay`.`store_id` AS `store_id`,
                        `bay`.`status` AS `bay_status`,
                        `bay`.`start_time` AS `start`,
                        IF(
                            `bay`.`extended_date` = @date,
                            CAST(
                                IFNULL(
                                    `bay`.`extended_time`,
                                    `bay`.`end_time`
                                ) AS TIME
                            ),
                            `bay`.`end_time`
                        ) AS `end`,
                        `b`.`start_time` AS `start_time`,
                        `b`.`end_time` AS `end_time`,
                        `b`.`date` AS `date`
                    FROM
                        (
                            `bay_allocation` `b`
                        JOIN `bay` ON(
                                `bay`.`id` = `b`.`bay_id` AND `bay`.`status` = 'active'
                            )
                        )
                    WHERE
                        `b`.`date` = @date
                    UNION ALL
                SELECT NULL AS
                    `job_card_id`,
                    `bay`.`id` AS `id`,
                    `bay`.`name` AS `name`,
                    `bay`.`store_id` AS `store_id`,
                    `bay`.`status` AS `bay_status`,
                    `bay`.`start_time` AS `start`,
                    IF(
                        `bay`.`extended_date` = @date,
                        CAST(
                            IFNULL(
                                `bay`.`extended_time`,
                                `bay`.`end_time`
                            ) AS TIME
                        ),
                        `bay`.`end_time`
                    ) AS `end`,
                    CONCAT(@date, ' ', `bay`.`start_time`) AS `start_time`,
                    CONCAT(@date, ' ', `bay`.`start_time`) AS `end_time`,
                    @date AS `date`
                FROM
                    `bay`
                WHERE
                    bay.status = 'active'
                UNION ALL
                SELECT NULL AS
                    `job_card_id`,
                    `bay`.`id` AS `id`,
                    `bay`.`name` AS `name`,
                    `bay`.`store_id` AS `store_id`,
                    `bay`.`status` AS `bay_status`,
                    `bay`.`start_time` AS `start`,
                    IF(
                        `bay`.`extended_date` = @date,
                        CAST(
                            IFNULL(
                                `bay`.`extended_time`,
                                `bay`.`end_time`
                            ) AS TIME
                        ),
                        `bay`.`end_time`
                    ) AS `end`,
                    CONCAT(
                        @date,
                        ' ',
                        IF(
                            `bay`.`extended_date` = @date,
                            CAST(
                                IFNULL(
                                    `bay`.`extended_time`,
                                    `bay`.`end_time`
                                ) AS TIME
                            ),
                            `bay`.`end_time`
                        )
                    ) AS `start_time`,
                    CONCAT(
                        @date,
                        ' ',
                        IF(
                            `bay`.`extended_date` = @date,
                            CAST(
                                IFNULL(
                                    `bay`.`extended_time`,
                                    `bay`.`end_time`
                                ) AS TIME
                            ),
                            `bay`.`end_time`
                        )
                    ) AS `end_time`,
                    @date AS `date`
                FROM
                    `bay`
                WHERE
                    bay.status = 'active'
                ) `b`
                ORDER BY
                    `b`.`start_time`,
                    `b`.`end_time`"
        ;

        $statement = DB::statement(DB::raw("set @date = '".$date."'"));
        $statement = DB::statement(DB::raw("set @cur_time = '".$date." ".$current_time."'"));
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
                                        if(@cur_time >@prev_end_time, @cur_time , @prev_end_time) AS from_time,
                                        t1.start_time as to_time,
                                        @prev_end_time:=timestamp(t1.end_time) as next_from_time

                                from(
                                        SELECT
                                            @i := @i +1 AS auto_assign_id,
                                            aab.*
                                        FROM
                                            (".$first_q.") aab
                                            
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
                    // print_r($cal_duration);die();
                    if (isset($cal_duration[2])) {
                        $min = $cal_duration[2];
                    }else{
                        $min = 0;
                    }
                    $end_time = strtotime('+'.$cal_duration[0].' hour +'.$cal_duration[1].' minutes +'.$min.' seconds',strtotime($date.' '.$start_time));
                    
                    // echo date('Y-m-d H:i:s',$end_time);die;
                    // $end_time = date('H:i:s',strtotime($cal_duration));
                    $bay_allocation_data = [
                        'bay_id'    =>  $findtime[0]->id,
                        'start_time'    =>  date('Y-m-d H:i:s',strtotime('+1 minutes',strtotime($date.' '.$start_time))),
                        'end_time'  =>  date('Y-m-d H:i:s',$end_time),
                        'date'  =>  $date,
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
    public function bayUpdate($id)  {
        $check = Bay::where('id',$id)->first();
        if ($check == null) {
            return redirect('/admin/bay/list')->with('error','Bay not found.');
        }
        $baydata =  Bay::leftJoin('users','bay.user_id','users.id')->where('bay.id',$id)->select('bay.*','users.name as username','users.id as user_id')->get()->first()->toArray();
        $machanicdata = Users::where('role','Employee')->get();
        $type = Master::where('type','job_card_type')->get();   
        $store = Store::whereIn('id',CustomHelpers::user_store())->get();
        $data = array(
            'machanicdata' => $machanicdata,
            'baydata' => $baydata,
            'type' => $type,
            'store'=>$store->toArray(),
            'layout' => 'layouts.main', 
        );
       return view('admin.bay.update_bay',$data);
    }

    public function bayUpdate_DB(Request $request,$id) {
          //print_r($request->input());die();
        try {
            $this->validate($request,[
                'name'=>'required',
                'type'=>'required',
                'mechanic'=>'required',
                'start_time'=>'required',
                'end_time'=>'required',
                'status'=>'required',
                'store_name'=>'required',
                'skils'=>'required',
            ],[
                'name.required'=> 'This field is required.', 
                'type.required'=> 'This field is required.',
                'mechanic.required'=> 'This field is required.',
                'start_time.required'=> 'This field is required.',
                'end_time.required'=> 'This field is required.',
                'status.required'=> 'This field is required.',
                'store_name.required'   =>  'This Field is required',
                'skils.required'   =>  'This Field is required',
            ]);
            $getUser = Bay::where('user_id',$request->input('mechanic'))->where('id','<>',$id)->get();
            //print_r($getUser);
            if (count($getUser) > 0) {
                $user = $getUser->first();
                $update = Bay::where('id',$user['id'])->update([
                    'user_id' => NULL
                ]);
            }
            $start_time = $request->input('start_time');
            $start_time  = str_replace(' ','',$start_time);
            $end_time = $request->input('end_time');
            $end_time  = str_replace(' ','',$end_time);
            $baydata = Bay::where('id',$id)->update(
                [
                    'name' => $request->input('name'),
                    'type' => $request->input('type'),
                    'user_id'=> $request->input('mechanic'),
                    'status'=> $request->input('status'),
                    'store_id'=> $request->input('store_name'),
                    'skils'=> $request->input('skils'),
                    'start_time' => $start_time,
                    'end_time' => $end_time
                ]);
          //  print_r($baydata);die();
              if ($baydata == NULL) {
                   return redirect('/admin/bay/update/'.$id.'')->with('error','Something went wrong .');
              }else{
                if ($baydata && count($getUser)> 0) {
                    /* Add action Log */
                   CustomHelpers::userActionLog($action='Bay Update',$id,0);
                     return redirect('/admin/bay/update/'.$id.'')->with('success','Bay Updated Successfully, and mechanic is deallocate');
                }else{
                     /* Add action Log */
                   CustomHelpers::userActionLog($action='Bay Update',$id,0);
                     return redirect('/admin/bay/update/'.$id.'')->with('success','Bay Updated Successfully.');
                }
                
              }
        }  catch(\Illuminate\Database\QueryException $ex) {
            return redirect('/admin/bay/update/'.$id.'')->with('error','some error occurred'.$ex->getMessage());
        }
    }
  
}
/*
try{
    }catch(\Illuminate\Database\QueryException $ex) {
            return back()->with('error','Something went wrong.'.$ex->getMessage())->withInput();
        }