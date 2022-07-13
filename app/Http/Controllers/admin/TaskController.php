<?php

namespace App\Http\Controllers\admin;
use Illuminate\Http\Request;
use \stdClass;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Auth;
use Hash;
use \App\Model\Users;
use \App\Model\Store;
use \App\Model\Task;
use \App\Model\TaskDetails;
use \App\Model\TaskReview;
use \App\Model\HolidayFestival;
use \App\Custom\CustomHelpers;
use \Carbon\Carbon;
use \App\Model\Settings;
use Mail;

class TaskController extends Controller
{
    public function __construct() {
        //$this->middleware('auth');
    }

    public function create_task(){

    	$users = Users::whereIn('user_type',array('admin','superadmin'))->get();
        return view('admin.task.create_task',['users'=>$users,'layout' => 'layouts.main']);
    }
    public function create_task_DB(Request $request)
    {

        $validateZero = [];
        $validateZeroMsg = [];
         $this->validate($request,array_merge($validateZero,[  
         	 'task'=>'required',
         	 'users'=>'required',
         	 'priority'=>'required',
         	 // 'date'=>'required',
         	 // 'time'=>'required',
         	 // 'recurrence'=>'required',
             'date'  =>  'required_without_all:recurrence',
             'recurrence'  =>  'required_without_all:date',
         	 'daily-every'=>'required_if:recurrence,Daily',
         	 'weekly-every'=>'required_if:recurrence,Weekly',
         	 'monthly-every'=>'required_if:recurrence,Monthly',
         	 'yearly-every'=>'required_if:recurrence,Yearly',
         	 'daily-every-days'=>'required_if:daily-every,EveryDays',
         	 'daily-regenerate-every-days'=>'required_if:daily-every,Regenerate',
         	 'weekly-every-days'=>'required_if:weekly-every,EveryWeekOn',
         	 'weekday'=>'required_if:weekly-every,EveryWeekOn',
         	 'weekly-regenerate-every-days'=>'required_if:weekly-every,Regenerate',
         	 'monthly-every-days'=>'required_if:monthly-every,Day',
         	 'monthly-every-month'=>'required_if:monthly-every,Day',
         	 'monthly-week'=>'required_if:monthly-every,SelectedWeek',
         	 'monthly-days'=>'required_if:monthly-every,SelectedWeek',
         	 'monthly-every-month-days'=>'required_if:monthly-every,SelectedWeek',
         	 'monthly-regenerate-every-days'=>'required_if:monthly-every,Regenerate',
         	 'yearly-month-name'=>'required_if:yearly-every,Every',
         	 'yearly-every-day'=>'required_if:yearly-every,Every',
         	 'yearly-week'=>'required_if:yearly-every,SelectedWeek',
         	 'yearly-days'=>'required_if:yearly-every,SelectedWeek',
         	 'yearly-month-week'=>'required_if:yearly-every,SelectedWeek',
         	 'yearly-regenerate-every-days'=>'required_if:yearly-every,Regenerate',

         ]),array_merge($validateZeroMsg,[
         	'task.required'  =>  'This Field is required',
         	'users.required'  =>  'This Field is required',
         	'priority.required'  =>  'This Field is required',
         	// 'date.required'  =>  'This Field is required',
         	// 'time.required'  =>  'This Field is required',
         	// 'recurrence.required'  =>  'This Field is required',
            'date.required_without_all'  =>  'This Field is required',
            'recurrence.required_without_all'  =>  'This Field is required',
         	'daily-every.required_if'  =>  'This cannot be blank',
         	'weekly-every.required_if'  =>  'This cannot be blank',
         	'monthly-every.required_if'  =>  'This cannot be blank',
         	'yearly-every.required_if'  =>  'This cannot be blank',
         	'daily-every-days.required_if'  =>  'This cannot be blank',
         	'daily-regenerate-every-days.required_if'  =>  'This cannot be blank',
         	'weekly-every-days.required_if'  =>  'This cannot be blank',
         	'weekly-regenerate-every-days.required_if'  =>  'This cannot be blank',
         	'monthly-every-days.required_if'  =>  'This cannot be blank',
         	'monthly-every-month.required_if'  =>  'This cannot be blank',
            'monthly-days.required_if'  =>  'This cannot be blank',
         	'monthly-week.required_if'  =>  'This cannot be blank',
         	'monthly-every-month-days.required_if'  =>  'This cannot be blank',
         	'monthly-regenerate-every-days.required_if'  =>  'This cannot be blank',
         	'yearly-month-name.required_if'  =>  'This cannot be blank',
         	'yearly-every-day.required_if'  =>  'This cannot be blank',
         	'yearly-week.required_if'  =>  'This cannot be blank',
         	'yearly-days.required_if'  =>  'This cannot be blank',
         	'yearly-month-week.required_if'  =>  'This cannot be blank',
         	'yearly-regenerate-every-days.required_if'  =>  'This cannot be blank',

          ]));  

           try{
           
            $date= date('Y-m-d');;
           	DB::beginTransaction();
             if($request->input('date')){
 			   $date=Carbon::createFromFormat('d/m/Y', $request->input('date'))->format('Y-m-d');
             }
            

            $data = [
            	'task_name' => $request->input('task'),
            	'user_id' => $request->input('users'),
            	'priority' => $request->input('priority'),
            	'task_date' =>$date,
            	'recurrence' => $request->input('recurrence'),
            	'created_by' =>Auth::id(),
                'status'=>1,
            ];

            $recurrence   = $request->input('recurrence');
            if($recurrence == 'Daily')
            {
              if($request->input('daily-every')=='EveryDays'){
	                $data = array_merge($data,[
	                    'every_number'   => $request->input('daily-every-days'),
	                    'recurrence_type' => $request->input('daily-every')
	                ]);
                }else if($request->input('daily-every')=='EveryWeekDay') {
                	$data = array_merge($data,[
	                    'recurrence_type' => $request->input('daily-every')
	                ]);
                }else if($request->input('daily-every')=='Regenerate') {
                	$data = array_merge($data,[
	                    'every_number'   => $request->input('daily-regenerate-every-days'),
	                    'recurrence_type' => $request->input('daily-every')
	                ]);
                }
            }else if($recurrence == 'Weekly'){

            	if($request->input('weekly-every')=='EveryWeekOn'){
	            	$data = array_merge($data,[
		                    'every_number'   => $request->input('weekly-every-days'),
		                    'recurrence_week' => implode(',', (array) $request->input('weekday')),
		                    'recurrence_type' => $request->input('weekly-every')
		                ]);
            	}else if($request->input('weekly-every')=='Regenerate') {
            		$data = array_merge($data,[
	                    'every_number'   => $request->input('weekly-regenerate-every-days'),
	                    'recurrence_type' => $request->input('weekly-every')
	                ]);
            	}
            }else if($recurrence == 'Monthly'){

            	if($request->input('monthly-every')=='Day'){
	            	$data = array_merge($data,[
		                    'every_number'   => $request->input('monthly-every-days'),
		                    'every_month_no'   => $request->input('monthly-every-month'),
		                    'recurrence_type' => $request->input('monthly-every')
		                ]);
            	}else if($request->input('monthly-every')=='SelectedWeek') {
            		$data = array_merge($data,[
	                    'week'   => $request->input('monthly-week'),
	                    'days'   => $request->input('monthly-days'),
	                    'every_month_no'   => $request->input('monthly-every-month-days'),
	                    'recurrence_type' => $request->input('monthly-every')
	                ]);
            	}else if($request->input('monthly-every')=='Regenerate') {
            		$data = array_merge($data,[
	                    'every_number'   => $request->input('monthly-regenerate-every-days'),
	                    'recurrence_type' => $request->input('monthly-every')
	                ]);
            	}
            }else if($recurrence == 'Yearly'){

            	if($request->input('yearly-every')=='Every'){
            		$data = array_merge($data,[
	                    'month' => $request->input('yearly-month-name'),
	                    'every_number' => $request->input('yearly-every-day'),
	                    'recurrence_type' => $request->input('yearly-every')
	                ]);
            	}else if($request->input('yearly-every')=='SelectedWeek') {
            		$data = array_merge($data,[
            		 'week' => $request->input('yearly-week'),
            		 'days' => $request->input('yearly-days'),
            		 'month' => $request->input('yearly-month-week'),
            		 'recurrence_type' => $request->input('yearly-every')
            		   ]);
            	}else if($request->input('yearly-every')=='Regenerate') {
            		$data = array_merge($data,[
	                    'every_number'   => $request->input('yearly-regenerate-every-days'),
	                    'recurrence_type' => $request->input('yearly-every')
	                ]);
            	}
            }else if(!$recurrence){
                $time='';
                if($request->input('time')){
                  $time=str_replace(' ','',$request->input('time'));
                  }

                 $data = array_merge($data,[
                        'task_time' =>$time,
                        // 'status' =>1
                    ]);
            }
            $insert = Task::insertGetId($data);
                  $data1=[

                    'task_id'=>$insert,
                    'status'=>'Pending',
                    'task_name'=>$data['task_name'],
                    'user_id'=>$data['user_id'],
                    'created_at'=>date('Y-m-d G:i:s'),
                    'task_date'=>$data['task_date'],
                  ];
                  $this->taskDetailsInsert($data1);
            if(!$insert)
            {
                DB::rollback();
                return back()->with('error','Something Went Wrong')->withInput();
            }  


           }catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return back()->with('error','Something went wrong.'.$ex->getMessage())->withInput();
        }
         DB::commit();
         CustomHelpers::userActionLog($action='Task Create',$insert,0);
         return back()->with('success','Successfully Task Created. ');
    }

    public function task_list(){

        $user = Auth::user();
        $user_type = $user['user_type'];
        if($user_type=="superadmin"){

            $data = array('layout'=>'layouts.main');
            return view('admin.task.task_list', $data);

        }else{

            return 'You are not authorised to access this page. Only Superadmin Allowed for this page.';
        }
    }

    public function task_list_api(Request $request) {
        
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
        
        $api_data = Task::leftjoin('users','task.user_id','users.id')
                    ->leftJoin('users as assigned','assigned.id','task.created_by')
        			->select(
                            'task.id',
                            'task.task_name',
                            'task.recurrence',
                            'task.recurrence_type',
                            'task.every_number',
                            'task.week',
                            'task.days',
                            'task.month',
                            'task.recurrence_week',
                            'task.every_month_no',
                            'task.status',
                            DB::raw('concat(users.name," ",ifnull( users.middle_name," ")," ",ifnull( users.last_name," ")) as name'),
                            DB::raw('concat(assigned.name," ",ifnull( assigned.middle_name," ")," ",ifnull( assigned.last_name," ")) as assignedfrom'),
                            'task.priority'
                        );
        if(!empty($serach_value))
        {
            $api_data->where(function($query) use ($serach_value){
                $query->where('name','like',"%".$serach_value."%")
                ->orwhere('task.task_name','like',"%".$serach_value."%")
                ->orwhere('assignedfrom','like',"%".$serach_value."%")
                ->orwhere('task.recurrence','like',"%".$serach_value."%")
                ->orwhere('task.recurrence_type','like',"%".$serach_value."%")
                ->orwhere('task.every_number','like',"%".$serach_value."%")
                ->orwhere('task.week','like',"%".$serach_value."%")
                ->orwhere('task.status','like',"%".$serach_value."%")
                ->orwhere('task.days','like',"%".$serach_value."%")
                ->orwhere('task.month','like',"%".$serach_value."%")
                ->orwhere('task.recurrence_week','like',"%".$serach_value."%")
                ->orwhere('task.every_month_no','like',"%".$serach_value."%")
                ->orwhere('task.priority','like',"%".$serach_value."%");
            });
        }
        if(isset($request->input('order')[0]['column']))
        {
            $data = [
            	   'task.task_name',
            	   'name',
                   'assignedfrom',
                   'task.priority',
                   'task.recurrence',
                   'task.recurrence_type',
                   'task.status',
                   'task.every_number',
                   'task.week',
                   'task.days',
                   'task.month',
                   'task.recurrence_week',
                   'task.every_month_no'
            ];
            $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
            $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
        }
        else
            $api_data->orderBy('task.id','desc');

        $count = count($api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

    public function create_recurrenceTask() {

        $tasks = Task::all();

        $holiday =HolidayFestival::get([DB::raw('GROUP_CONCAT(Date_Format(start_date,"%Y-%m-%d"))as fulldate')])->toArray();
        $arr_holiday = explode(',', $holiday[0]['fulldate']);
        $tomorrow = date('Y-m-d',strtotime("+1 days"));
        $day_a_tom = date('Y-m-d',strtotime("+2 days"));
        $timestamp = date('Y-m-d G:i:s');
        $datetoday = date('Y-m-d');

        $weekOff=Settings::where('name','WeekOffday')->select('value','name')->get()->first();
        $weekOffday=$weekOff['value'];

        foreach ($tasks as $task) {
            $status = TaskDetails::where('task_details.task_id',$task['id'])
            ->select('task_date','status','id')
            ->orderBy('id', 'DESC')->first();
            $status_task_date = $status['task_date'];
            $task_status= $status['status'];

            $data= [
                'task_id'=>$task['id'],
                'status'=>'Pending',
                'task_name'=>$task['task_name'],
                'user_id'=>$task['user_id'],
                'created_at'=>$timestamp
            ];

            if($task['recurrence'] == "Daily"){
                if(($task_status != "Pending" || $task_status != "ReOpen") && ($task_status == "Done" || $task_status == "Cancel" || $task_status == "Closed"|| $task_status == "Completed")){
                    
                    if($task['recurrence_type']=='EveryDays'){
                             $every_number=$task['every_number'];
                             $date = date('Y-m-d',strtotime('+'.$every_number.' days'));
                             $date_next_to =date('Y-m-d',strtotime("+1 days", strtotime($date)));

                        if(!in_array($date, $arr_holiday) && date("l",strtotime($date)) != $weekOffday){
                                // insert query tomorrow;
                                $data = array_merge($data,[
                                    'task_date' =>$date
                                    ]);
                             $this->taskDetailsInsert($data);

                        }else if(!in_array($date_next_to, $arr_holiday) && date("l",strtotime($date_next_to)) != $weekOffday){
                                 // insert query day after tomorrow;
                                $data = array_merge($data,[
                                    'task_date' =>$date_next_to
                                    ]);
                                $this->taskDetailsInsert($data);
                        }else{                            
                                // insert query day after 1 tomorrow;
                                $data = array_merge($data,[
                                    'task_date' =>date('Y-m-d',strtotime("+2 days", strtotime($date)))
                                    ]);
                                $this->taskDetailsInsert($data);    
                          }
                    }else if($task['recurrence_type']=='EveryWeekDay'){
                            $now = Carbon::now();
                            $date = $now->startOfWeek()->format('Y-m-d');
                            $next_date =date('Y-m-d',strtotime("+1 days", strtotime($date)));

                                // insert query tomorrow;
                        if(!in_array($date, $arr_holiday) && date("l",strtotime($date)) != $weekOffday){
                               
                                $data = array_merge($data,[
                                'task_date' =>$date
                                ]);
                                $this->taskDetailsInsert($data);
                        }else if(!in_array($next_date, $arr_holiday) && date("l",strtotime($next_date)) != $weekOffday){
                                 // insert query day after tomorrow;
                                $data = array_merge($data,[
                                    'task_date' =>$next_date
                                    ]);
                                $this->taskDetailsInsert($data);
                        }else{

                                $data = array_merge($data,[
                                'task_date' =>date('Y-m-d',strtotime("+1 days", strtotime($next_date)))
                                ]);
                                $this->taskDetailsInsert($data);
                            }
                    }else if($task['recurrence_type']=='Regenerate'){
                            $every_number=$task['every_number'];
                            $date = date('Y-m-d',strtotime('+'.$every_number.' days'));
                            $next_date = date('Y-m-d',strtotime("+1 days", strtotime($date)));
                        if(!in_array($date, $arr_holiday) && date("l",strtotime($date)) !=$weekOffday){

                                $data = array_merge($data,[
                                'task_date' =>$date
                                ]);
                                $this->taskDetailsInsert($data);
                        }else{

                                $data = array_merge($data,[
                                'task_date' =>$next_date
                                ]);
                                $this->taskDetailsInsert($data); 
                            }
                        }                  
                }
           }else if($task['recurrence'] == "Weekly"){
         
                if(($task_status != "Pending" || $task_status != "ReOpen") && ($task_status == "Done" || $task_status == "Cancel" || $task_status == "Closed"|| $task_status == "Completed")){

                    if($task['recurrence_type']=='EveryWeekOn'){
                            $week_no = $task['every_number'];
                            $week = $task['recurrence_week'];
                            $week_explode=explode(",",$week);
                            if($week_no==1){
                                $week_no='First';
                            }else if($week_no==2){
                                $week_no='Second';
                            }else if($week_no==3){
                                $week_no='Third';
                            }else if($week_no==4){
                                $week_no='Fourth ';
                            }else if($week_no==5){
                                $week_no='Fifth';
                            }
                        foreach ($week_explode as $key => $recurrence_week) {

                            $day= Carbon::parse($week_no.' '.$recurrence_week.' of this month')->format('l'); 
                            $next_day = date("Y-m-d",strtotime("next ".$day.""));
                            $present_day = date("Y-m-d",strtotime("this ".$day.""));

                            if($present_day > $timestamp){
                                $data = array_merge($data,[
                                    'task_date' =>$present_day
                                    ]);
                                $this->taskDetailsInsert($data);
                            }else{
                                $data = array_merge($data,[
                                    'task_date' =>$next_day
                                    ]);
                                $this->taskDetailsInsert($data);
                              }
                           }
                    }else if($task['recurrence_type']=='Regenerate'){
                            $week_no = $task['every_number'];
                            if($week_no==1){
                                $week_no='First';
                            }else if($week_no==2){
                                $week_no='Second';
                            }else if($week_no==3){
                                $week_no='Third';
                            }else if($week_no==4){
                                $week_no='Fourth ';
                            }else if($week_no==5){
                                $week_no='Fifth';
                            }

                            $day= Carbon::parse($week_no.' Monday of this month')->format('l'); 
                            $next_day = date("Y-m-d",strtotime("next ".$day.""));
                            $present_day = date("Y-m-d",strtotime("this ".$day.""));
                            if($present_day > $timestamp){

                               $data = array_merge($data,[
                                    'task_date' =>$present_day
                                    ]);
                                $this->taskDetailsInsert($data);
                            }else{
                                $data = array_merge($data,[
                                    'task_date' =>$next_day
                                    ]);
                                 $this->taskDetailsInsert($data);
                            }
                        }
                }
           }else if($task['recurrence'] == "Monthly"){
                if(($task_status != "Pending" || $task_status != "ReOpen") && ($task_status == "Done" || $task_status == "Cancel" || $task_status == "Closed"|| $task_status == "Completed")){
                    
                    if($task['recurrence_type']=='Day'){
                            $month = date('Y-m');
                            $every_number = $month.'-'.$task['every_number'];
                            $every_month=$task['every_month_no'];
                            $date = date('Y-m-d',strtotime('+'.$every_month.'months', strtotime($every_number)));
                            $data = array_merge($data,[
                                'task_date' =>$date
                                ]);
                            $this->taskDetailsInsert($data);

                      }else if($task['recurrence_type']=='SelectedWeek'){
                            $week_no = $task['week'];
                            $every_month_no = $task['every_month_no'];
                            $created_at = $task['created_at'];
                            $after_month = date('F Y',strtotime('+'.$every_month_no.' months', strtotime($created_at)));
                            $days = $task['days'];
                            $month_date= Carbon::parse($week_no.' '.$days.' of'.$after_month)->format('Y-m-d');   
                            $one_min_month = date('Y-m-d',strtotime("-1 days", strtotime($month_date)));
                            if(!in_array($one_min_month, $arr_holiday) && date("l",strtotime($one_min_month)) != $weekOffday)
                            {
                                $data = array_merge($data,[
                                'task_date' =>$one_min_month
                                ]);
                                $this->taskDetailsInsert($data);
                            }else{

                                $data = array_merge($data,[
                                'task_date' =>date('Y-m-d',strtotime("-1 days", strtotime($one_min_month)))
                                ]);
                                $this->taskDetailsInsert($data);
                            }
                      }else if($task['recurrence_type']=='Regenerate'){
                            $month_no = $task['every_number'];
                            $created_at = $task['created_at'];
                            $after_month = date('Y-m-d',strtotime('+'.$month_no.' months', strtotime($created_at)));
                            $one_min_month = date('Y-m-d',strtotime("-1 days", strtotime($after_month)));

                            if(!in_array($one_min_month, $arr_holiday) && date("l",strtotime($one_min_month)) != $weekOffday)
                                {
                                    $data = array_merge($data,[
                                    'task_date' =>$one_min_month
                                    ]);
                                    $this->taskDetailsInsert($data);   
                                }else{

                                    $data = array_merge($data,[
                                    'task_date' =>date('Y-m-d',strtotime("-1 days", strtotime($one_min_month)))
                                    ]);
                                    $this->taskDetailsInsert($data);
                                }

                      }
                }
           }else if($task['recurrence'] == "Yearly"){
             if(($task_status != "Pending" || $task_status != "ReOpen") && ($task_status == "Done" || $task_status == "Cancel" || $task_status == "Closed"|| $task_status == "Completed")){
                    if($task['recurrence_type']== "Every"){
                        // first record
                        $year = date('Y');
                        $nmonth = date("m", strtotime($task['month']));
                        $date = $year.'-'.$nmonth.'-'.$task['every_number'];

                        $data = array_merge($data,[
                                'task_date' =>$date
                                ]);
                            $this->taskDetailsInsert($data);
                    }else if($task['recurrence_type']=='SelectedWeek'){
                        $week = $task['week'];
                        $month = $task['month'];
                        $days = $task['days'];
                        $created_at = $task['created_at'];
                        $year=date('Y');
                        $month_date = date("Y-m-d",strtotime($week.' '.$days.' '.$month.' '.$year));   
                        $one_min_month = date('Y-m-d',strtotime("-1 days", strtotime($month_date)));
                        if(!in_array($one_min_month, $arr_holiday) && date("l",strtotime($one_min_month)) != $weekOffday)
                            {
                                $data = array_merge($data,[
                                'task_date' =>$one_min_month
                                ]);
                                $this->taskDetailsInsert($data);
                            }else{

                                $data = array_merge($data,[
                                'task_date' =>date('Y-m-d',strtotime("-1 days", strtotime($one_min_month)))
                                ]);
                                $this->taskDetailsInsert($data);
                            }
                    }else if($task['recurrence_type']=='Regenerate'){
                        $regenerate_date = $task['every_number'];
                        $created_at = $task['created_at'];
                        $year = date('Y-m-d',strtotime('+'.$regenerate_date.' years', strtotime($created_at)));
                        $one_min_year = date('Y-m-d',strtotime("-1 days", strtotime($year)));
                       
                         //yealy date not in holiday and dont be sunday
                        if(!in_array($year, $arr_holiday) && date("l",strtotime($year)) != $weekOffday)
                        {
                            $data = array_merge($data,[
                            'task_date' =>$year
                            ]);
                            $this->taskDetailsInsert($data);
                        }else if(!in_array($one_min_year, $arr_holiday) && date("l",strtotime($one_min_year)) != $weekOffday)
                        {
                            $data = array_merge($data,[
                            'task_date' =>$one_min_year
                            ]);
                            $this->taskDetailsInsert($data);
                        }else{
                            $data = array_merge($data,[
                            'task_date' =>date('Y-m-d',strtotime("-1 days", strtotime($one_min_year)))
                            ]);
                            $this->taskDetailsInsert($data);
                        }
                    }
             }
           }else if(($task['recurrence'] ==NULL) && ($task['task_date'] !='0000-00-00')){
                if($task_status != "Pending" || $task_status != "ReOpen"){
                    $date=$task['task_date'];
                    $date_next_to =date('Y-m-d',strtotime("+1 days", strtotime($date)));

                        if(!in_array($date, $arr_holiday) && date("l",strtotime($date)) != $weekOffday){
                                // insert query tomorrow;
                                $data = array_merge($data,[
                                    'task_date' =>$date
                                    ]);
                             $this->taskDetailsInsert($data);

                        }else if(!in_array($date_next_to, $arr_holiday) && date("l",strtotime($date_next_to)) != $weekOffday){
                                 // insert query day after tomorrow;
                                $data = array_merge($data,[
                                    'task_date' =>$date_next_to
                                    ]);
                                $this->taskDetailsInsert($data);
                        }else{                            
                                // insert query day after 1 tomorrow;
                                $data = array_merge($data,[
                                    'task_date' =>date('Y-m-d',strtotime("+2 days", strtotime($date)))
                                    ]);
                                $this->taskDetailsInsert($data);    
                          }
                }
           }
        }
    }
     function taskDetailsInsert($data){
        
            $timestamp = date('Y-m-d');
            $date = $data['task_date'];
            $next_date = date('Y-m-d',strtotime("1 days", strtotime($date)));
            $after_next_date = date('Y-m-d',strtotime("+1 days", strtotime($next_date)));
            $holiday =HolidayFestival::get([DB::raw('GROUP_CONCAT(Date_Format(start_date,"%Y-%m-%d"))as fulldate')])->toArray();
            $arr_holiday = explode(',', $holiday[0]['fulldate']);
            $weekOff=Settings::where('name','WeekOffday')->select('value','name')->get()->first();
            $weekOffday=$weekOff['value'];

            $checkstatus = Task::where('id',$data['task_id'])->get()->first();
            $checkdate = TaskDetails::where('task_details.task_id',$data['task_id'])->get()->last();
          
                if(($checkdate['task_date']!=$date) && ($checkstatus['status']==1) && ($checkdate['status']!='pending')){

                    if($date>=$timestamp){
                    if(!in_array($date, $arr_holiday) && date("l",strtotime($date)) != $weekOffday){
                         $data['task_date']=$date;
                    }else if(!in_array($next_date, $arr_holiday) && date("l",strtotime($next_date)) != $weekOffday){
                         $data['task_date']=$next_date;
                    }else{
                         $data['task_date']=$after_next_date;
                    }
                        $insert=TaskDetails::insertGetId($data);
                        if($insert){
                           
                           CustomHelpers::userActionLog($action='Task Details Add',$insert,0);

                            $created_by=Users::where('id',$checkstatus['created_by'])->first();
                            $created_bys = $created_by->name.' '.$created_by->middle_name.' '.$created_by->last_name;

                            $users=Users::where('id',$data['user_id'])->first();
                            $email = $users->email;
                            $username = $users->name.' '.$users->middle_name.' '.$users->last_name;
                            $email_view = 'admin.emails.task_create';
                            $subject = 'New task Assign Task#'.$insert;
                            $task_name=$data['task_name'];
                            $status=$data['status'];
                            $date=$data['task_date'];
                            $priority=$checkstatus['priority'];

                            if($email!=null){
                                
                               $sent= Mail::send($email_view, ['task_id' =>$insert,'task_name'=>$task_name,'status'=>$status,'task_date'=>$date,'priority'=>$priority,'username'=>$username,'created_by'=>$created_bys], function($message) use ($email,$subject,$username)
                                    {
                                        $message->to($email,$username)->subject($subject);
                                    });
                              
                             }
                        }

                   }
            }
     }

    public function task_detail_list(){
        
        $user = Auth::user();
        $user_type = $user['user_type'];

        if($user_type=='superadmin'){

            $data = array('layout'=>'layouts.main');
            return view('admin.task.task_detail_list', $data);
         
        }else{

            return 'You are not authorised to access this page. Only Superadmin Allowed for this page.';
        }

        
    }

    public function task_detail_list_api(Request $request) {
        
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
        
        $api_data = TaskDetails::leftjoin('task','task_details.task_id','task.id')
                    ->leftjoin('users','task_details.user_id','users.id')
                    ->leftJoin('users as assigned','assigned.id','task.created_by')
                    ->select(
                            'task_details.id',
                            'task_details.task_name',
                            'task_details.task_date',
                            'task_details.status',
                            'task_details.user_id',
                            'users.user_type',
                            DB::raw('concat(users.name," ",ifnull( users.middle_name," ")," ",ifnull( users.last_name," ")) as name'),
                            DB::raw('concat(assigned.name," ",ifnull( assigned.middle_name," ")," ",ifnull( assigned.last_name," ")) as assignedfrom'),
                            'task.priority'
                        );
                    // ->get()->toArray();
                    // print_r($api_data);die();
        if(!empty($serach_value))
        {
            $api_data->where(function($query) use ($serach_value){
                $query->where('name','like',"%".$serach_value."%")
                ->orwhere('task_details.task_name','like',"%".$serach_value."%")
                ->orwhere('assignedfrom.task_name','like',"%".$serach_value."%")
                ->orwhere('task_details.task_date','like',"%".$serach_value."%")
                ->orwhere('task_details.status','like',"%".$serach_value."%")
                ->orwhere('task.priority','like',"%".$serach_value."%");
            });
        }
        if(isset($request->input('order')[0]['column']))
        {
            $data = [
                    'task_details.id',
                   'task_details.task_name',
                   'name',
                   'assignedfrom',
                   'task.priority',
                   'task_details.task_date',
                   'task_details.status'
            ];
            $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
            $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
        }
        else
            $api_data->orderBy('task_details.id','desc');

        $count = count($api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

    public function task_detailConfirmation(Request $request)
    {
        $taskId = $request->input('taskId');
        $todaydate =date('Y-m-d');
        $getcheck = TaskDetails::where('id',$taskId)->first();
        if($getcheck)
        {
            if($getcheck->task_date>$todaydate){
                        return "Future task status can't update";
                }else{

            if($getcheck->status!='Completed'){

                 $customer_cofirmation = TaskDetails::where('id',$taskId)->update(['status' => 'Done']);
                    if($customer_cofirmation){
                        
                        $check=Task::where('id',$getcheck->task_id)->first();
                        $users=Users::where('id',$check->created_by)->first();
                        $email = $users->email;
                        $username = $users->name.' '.$users->middle_name.' '.$users->last_name;
                        $task_name=$getcheck->task_name;
                        $comment='';

                        $email_view = 'admin.emails.task_requestupdate';
                            $subject = 'Task#'.$getcheck->id.' Task status done';
                            if($email!=null){
                            
                             $sent= Mail::send($email_view, ['task_id' =>$taskId,'task_name'=>$task_name,'status'=>'Done','comment'=>$comment], function($message) use ($email,$subject,$username)
                                {
                                    $message->to($email,$username)->subject($subject);
                                });
                          
                            }
                    CustomHelpers::userActionLog($action='Task Details status Update',$customer_cofirmation,0);

                      return 'success';
                    }
             }else{
                    return 'This task status already change'; 
                    }
           }
        }
        else{
            return "something wen't wrong";
        }
        return 'error';
        
    }

     public function update_review_db(Request $request){
        try {

            $error =[];
            $msg=[];

            if(empty($request->input('status'))){
                $error = array_merge($error,array('Status is Required'));
            }
            if(count($error)>0){
                $data = [
                'error'=>$error];
                return response()->json($data);
            }

            $getcheck = TaskDetails::where('id',$request->input('task_id'))->first();
            $users=Users::where('id',$getcheck->user_id)->first();
            $email = $users->email;
            $username = $users->name.' '.$users->middle_name.' '.$users->last_name;
            $task_name=$getcheck->task_name;
            $comment='';

            if($request->input('status') == 'completed'){
                   $todaydate =date('Y-m-d');

                if($getcheck->task_date>$todaydate){
                        $error = array_merge($error,array("Future task status can't update"));
                }else{

                     $detail = [
                        'task_details_id' => $request->input('task_id'),
                        'reviewer_id' =>Auth::id(),
                        'review_date' =>date('Y-m-d'),
                        'status' =>$request->input('status'),
                    ];

                    $completed = TaskReview::insertGetId($detail);
                    if($completed == null){
                        DB::rollback();
                        $error = array_merge($error,array('Some Unexpected Error occurred.'));
                    }else{
                         $taskDetail = TaskDetails::where('id',$request->input('task_id'))
                         ->update([
                            'status' => $request->input('status'),
                         ]);

                         CustomHelpers::userActionLog($action='Task Details status Update',$request->input('task_id'),0);

                        $email_view = 'admin.emails.task_userupdate';
                        $subject = 'Task#'.$getcheck->id.' Task status Completed';

                        if($email!=null){
                            
                            $sent= Mail::send($email_view, ['task_id' =>$request->input('task_id'),'task_name'=>$task_name,'status'=>$request->input('status'),'comment'=>$comment], function($message) use ($email,$subject,$username)
                                {
                                    $message->to($email,$username)->subject($subject);
                                });
                          
                            }
                    CustomHelpers::userActionLog($action='Task Review Add',$completed,0);

                        $msg =['Task review added Successfully.'];
                    }
                }

                $data = ['msg'=>$msg,
                'error'=>$error];
                return response()->json($data);

            }else if($request->input('status') == 'cancel'){
           
                $detail = [
                        'task_details_id' => $request->input('task_id'),
                        'reviewer_id' =>Auth::id(),
                        'review_date' =>date('Y-m-d'),
                        'status' =>$request->input('status'),
                    ];

                $completed = TaskReview::insertGetId($detail);
              
                if($completed == null){
                    DB::rollback();
                    $error = array_merge($error,array('Some Unexpected Error occurred.'));
                }else{

                    CustomHelpers::userActionLog($action='Task Review Add',$completed,0);

                     $taskDetail = TaskDetails::where('id',$request->input('task_id'))
                     ->update([
                        'status' => $request->input('status'),
                     ]);

                    CustomHelpers::userActionLog($action='Task Details status Update',$request->input('task_id'),0);


                        $email_view = 'admin.emails.task_userupdate';
                        $subject = 'Task#'.$getcheck->id.' Task status Cancel';

                        if($email!=null){
                            
                            $sent= Mail::send($email_view, ['task_id' =>$request->input('task_id'),'task_name'=>$task_name,'status'=>$request->input('status'),'comment'=>$comment], function($message) use ($email,$subject,$username)
                                {
                                    $message->to($email,$username)->subject($subject);
                                });
                          
                            }

                    $msg =['Successfully Task Cancel Done.'];
                }
                $data = ['msg'=>$msg,
                'error'=>$error];
                return response()->json($data);
            }

            else if($request->input('status') != 'reassign'){
                   $todaydate =date('Y-m-d');
                   $getcheck = TaskDetails::where('id',$request->input('task_id'))->first();
                   
                if($getcheck->task_date>$todaydate){
                        $error = array_merge($error,array("Future task status can't update"));
                }else{

                     $detail = [
                        'task_details_id' => $request->input('task_id'),
                        'reviewer_id' =>Auth::id(),
                        'review_date' =>date('Y-m-d'),
                        'status' =>$request->input('status'),
                    ];

                    $completed = TaskReview::insertGetId($detail);
                    if($completed == null){
                        DB::rollback();
                        $error = array_merge($error,array('Some Unexpected Error occurred.'));
                    }else{
                         $taskDetail = TaskDetails::where('id',$request->input('task_id'))
                         ->update([
                            'status' => $request->input('status'),
                         ]);

                        CustomHelpers::userActionLog($action='Task Details status Update',$request->input('task_id'),0);

                        $email_view = 'admin.emails.task_userupdate';
                        $subject = 'Task#'.$getcheck->id.' Task status update';

                        if($email!=null){
                            
                            $sent= Mail::send($email_view, ['task_id' =>$request->input('task_id'),'task_name'=>$task_name,'status'=>$request->input('status'),'comment'=>$comment], function($message) use ($email,$subject,$username)
                                {
                                    $message->to($email,$username)->subject($subject);
                                });
                          
                            }

                        CustomHelpers::userActionLog($action='Task Review Add',$completed,0);
                    
                        $msg =['Task review added Successfully.'];
                    }
                }

                $data = ['msg'=>$msg,
                'error'=>$error];
                return response()->json($data);

            }else if($request->input('status') == 'reassign'){
                
                if(empty($request->input('comment'))){
                    $error = array_merge($error,array('Comment is Required'));
                }
                if(count($error)>0){
                    $data = ['error'=>$error];
                    return response()->json($data);
                }
                
                 $data = [
                        'task_details_id' => $request->input('task_id'),
                        'comment' => $request->input('comment'),
                        'reviewer_id' =>Auth::id(),
                        'review_date' =>date('Y-m-d'),
                        'status' =>$request->input('status'),
                    ];
                $completed = TaskReview::insertGetId($data);
                if($completed == null){
                    DB::rollback();
                    $error = array_merge($error,array('Some Unexpected Error occurred.'));
                }else{

                    $taskDetail = TaskDetails::where('id',$request->input('task_id'))
                            ->update([
                            'status' => $request->input('status')
                            ]);

                     CustomHelpers::userActionLog($action='Task Details status Update',$request->input('task_id'),0);

                        $email_view = 'admin.emails.task_reopen';
                        $subject = 'Task#'.$getcheck->id.' Task status Reassign';

                        if($email!=null){
                            
                            $sent= Mail::send($email_view, ['task_id' =>$request->input('task_id'),'task_name'=>$task_name,'status'=>$request->input('status'),'comment'=>$request->input('comment')], function($message) use ($email,$subject,$username)
                                {
                                    $message->to($email,$username)->subject($subject);
                                });
                          
                            }
                    CustomHelpers::userActionLog($action='Task Review Add',$completed,0);
                    $msg =['Task review added Successfully.'];
                }
                $data = ['msg'=>$msg,
                'error'=>$error];
                return response()->json($data);
            }

        } catch (\Illuminate\Database\QueryException $ex) {
            return redirect('/admin/task/detail/list')->with('error','some error occurred'.$ex->getMessage());
        }
    }

    public function update_taskstatus_db(Request $request){
        try {

            $error =[];
            $msg=[];

            if(empty($request->input('status'))){
                $error = array_merge($error,array('Status is Required'));
            }
            if(count($error)>0){
                $data = [
                'error'=>$error];
                return response()->json($data);
            }
            if(!empty($request->input('status'))){
                    $status=$request->input('status');
                    if($status=='Active'){
                         $status=1;
                    }else{
                         $status=0;
                    }
                     
             $getcheck = Task::where('id',$request->input('task_id'))->first();
             if($getcheck){

                    $completed = Task::where('id',$request->input('task_id'))
                     ->update([
                        'status' => $status,
                     ]);

                    if($completed == null){
                        DB::rollback();
                        $error = array_merge($error,array('Some Unexpected Error occurred.'));
                    }else{
                        CustomHelpers::userActionLog($action='Task Status Update',$request->input('task_id'),0);

                        $msg =['Task Status update Successfully.'];
                    }

                }else{
                  $error = array_merge($error,array('Id not exist..'));
                }               
                $data = ['msg'=>$msg,
                'error'=>$error];
                return response()->json($data);
            }

        } catch (\Illuminate\Database\QueryException $ex) {
            return redirect('/admin/task/list')->with('error','some error occurred'.$ex->getMessage());
        }
    }

     public function employee_task_list(){

        $data = array('layout'=>'layouts.main');
        return view('admin.task.employee_task_list', $data);
    }

    public function employee_task_list_api(Request $request) {
        
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
        
        $api_data = Task::leftjoin('users','task.user_id','users.id')
                    ->where('task.created_by',Auth::id())
                    ->select(
                            'task.*',
                            'task.id',
                            'task.task_name',
                            'task.recurrence',
                            'task.recurrence_type',
                            'task.every_number',
                            'task.week',
                            'task.days',
                            'task.month',
                            'task.recurrence_week',
                            'task.every_month_no',
                            'task.status',
                            DB::raw('concat(users.name," ",ifnull( users.middle_name," ")," ",ifnull( users.last_name," ")) as name'),
                            'task.priority'
                        );
        if(!empty($serach_value))
        {
            $api_data->where(function($query) use ($serach_value){
                $query->where('name','like',"%".$serach_value."%")
                ->orwhere('task.task_name','like',"%".$serach_value."%")
                ->orwhere('task.recurrence','like',"%".$serach_value."%")
                ->orwhere('task.recurrence_type','like',"%".$serach_value."%")
                ->orwhere('task.every_number','like',"%".$serach_value."%")
                ->orwhere('task.week','like',"%".$serach_value."%")
                ->orwhere('task.status','like',"%".$serach_value."%")
                ->orwhere('task.days','like',"%".$serach_value."%")
                ->orwhere('task.month','like',"%".$serach_value."%")
                ->orwhere('task.recurrence_week','like',"%".$serach_value."%")
                ->orwhere('task.every_month_no','like',"%".$serach_value."%")
                ->orwhere('task.priority','like',"%".$serach_value."%");
            });
        }
        if(isset($request->input('order')[0]['column']))
        {
            $data = [
                   'task.task_name',
                   'name',
                   'task.priority',
                   'task.recurrence'
                   
            ];
            $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
            $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
        }
        else
            $api_data->orderBy('task.id','desc');

        $count = count($api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

    public function task_recurrenceStop(Request $request)
    {
        $taskId = $request->input('taskId');
        $todaydate =date('Y-m-d');
        $getcheck = Task::where('id',$taskId)->first();
        if($getcheck)
        {
            if($getcheck->status==0){

               return 'This task Recurrence already Stop';
                
             }else{
                    
                $customer_cofirmation = Task::where('id',$taskId)->update(['status' => '0']);

                    if($customer_cofirmation){
                    CustomHelpers::userActionLog($action='Task Status Update',$taskId,0);
                    return 'success';
                    }
                }
        }
        else{
            return "Id not exist..";
        }
        return 'error';
        
    }

    public function employee_task_detail_list(){

        $data = array('layout'=>'layouts.main');
        return view('admin.task.employee_task_detail_list', $data);
    }

    public function employee_task_detail_list_api(Request $request) {
        
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
        
        $api_data = TaskDetails::leftjoin('task','task_details.task_id','task.id')
                    ->leftJoin('users','users.id','task.created_by')
                    ->where('task_details.user_id',Auth::id())
                    ->select(
                            'task_details.id',
                            'task_details.task_name',
                            'task_details.task_date',
                            'task_details.status',
                            'task_details.user_id',
                            'users.user_type',
                            'task.recurrence',
                            'task.recurrence_type',
                            DB::raw('concat(users.name," ",ifnull( users.middle_name," ")," ",ifnull( users.last_name," ")) as name'),
                            'task.priority'
                        );
                    // ->get()->toArray();
                     // print_r($api_data);die();
        if(!empty($serach_value))
        {
            $api_data->where(function($query) use ($serach_value){
                $query->where('name','like',"%".$serach_value."%")
                ->orwhere('task_details.task_name','like',"%".$serach_value."%")
                ->orwhere('task_details.task_date','like',"%".$serach_value."%")
                ->orwhere('task_details.status','like',"%".$serach_value."%")
                ->orwhere('task.priority','like',"%".$serach_value."%");
            });
        }
        if(isset($request->input('order')[0]['column']))
        {
            $data = [
                   'task_details.id',
                   'task_details.task_name',
                   'name',
                   'task.priority',
                   'task_details.task_date',
                   'task_details.status'
            ];
            $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
            $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
        }
        else
            $api_data->orderBy('task_details.id','desc');

        $count = count($api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
         $i = -1;
         foreach ($api_data as $datas) {
          $i++;
                 $recurrence= $datas['recurrence'];
                 $recurrence_type= $datas['recurrence_type'];
                 $date= $datas['task_date'];
                 if($recurrence!=null && $recurrence_type!=null){
                    if($date > date('Y-m-d')){
                     unset($api_data[$i]);
                   }
                   
                 } 
           } 
        $api_data = array_values($api_data);

        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

    public function employee_assignedtask(){
        
        $data = array('layout'=>'layouts.main');
        return view('admin.task.employee_assignedtask_list', $data);
    }
    
    public function employee_mytask(){
        
        $data = array('layout'=>'layouts.main');
        return view('admin.task.employee_mytask_list', $data);
    }

    public function employee_assignedtask_list_api(Request $request) {
        
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
        
        $api_data = TaskDetails::leftjoin('task','task_details.task_id','task.id')
                    ->leftjoin('users','task_details.user_id','users.id')
                    ->where('task.created_by',Auth::id())
                    ->select(
                            'task_details.id',
                            'task_details.task_name',
                            'task_details.task_date',
                            'task_details.status',
                            'task_details.user_id',
                            'users.user_type',
                            'task.recurrence',
                            'task.recurrence_type',
                            DB::raw('concat(users.name," ",ifnull( users.middle_name," ")," ",ifnull( users.last_name," ")) as name'),
                            'task.priority'
                        );
        if(!empty($serach_value))
        {
            $api_data->where(function($query) use ($serach_value){
                $query->where('name','like',"%".$serach_value."%")
                ->orwhere('task_details.task_name','like',"%".$serach_value."%")
                ->orwhere('task_details.task_date','like',"%".$serach_value."%")
                ->orwhere('task_details.status','like',"%".$serach_value."%")
                ->orwhere('task.priority','like',"%".$serach_value."%");
            });
        }
        if(isset($request->input('order')[0]['column']))
        {
            $data = [
                   'task_details.id',
                   'task_details.task_name',
                   'name',
                   'task.priority',
                   'task_details.task_date',
                   'task_details.status'
            ];
            $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
            $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
        }
        else
            $api_data->orderBy('task_details.id','desc');

        $count = count($api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
         $i = -1;
         foreach ($api_data as $datas) {
          $i++;
                 $recurrence= $datas['recurrence'];
                 $recurrence_type= $datas['recurrence_type'];
                 $date= $datas['task_date'];
                 if($recurrence!=null && $recurrence_type!=null){
                    if($date > date('Y-m-d')){
                     unset($api_data[$i]);
                   }
                   
                 } 
           } 
        $api_data = array_values($api_data);
        
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

    public function employee_mytask_statusupdate(Request $request)
    {
        $taskId = $request->input('taskId');
        $status = $request->input('status');
        $comment = $request->input('comment');
       
        $todaydate =date('Y-m-d');
        $getcheck = TaskDetails::where('id',$taskId)->first();
        if($getcheck)
        {
            // if($getcheck->task_date>$todaydate){
            //     return "Future task status can't update";
            // }else{

                if($getcheck->status!='Completed'){

                    $customer_cofirmation = TaskDetails::where('id',$taskId)->update([
                    'status' => $request->input('status')
                     ]);
                    if($customer_cofirmation){
                        $data = [
                        'task_details_id' =>$taskId,
                        'comment' => $request->input('comment'),
                        'reviewer_id' =>Auth::id(),
                        'review_date' =>date('Y-m-d'),
                        'status' =>$request->input('status'),
                    ];
                     CustomHelpers::userActionLog($action='Task Details status Update',$taskId,0);
                    $completed = TaskReview::insertGetId($data);

                    if($completed){

                        $check=Task::where('id',$getcheck->task_id)->first();
                        $users=Users::where('id',$check->created_by)->first();
                        $email = $users->email;
                        $username = $users->name.' '.$users->middle_name.' '.$users->last_name;
                        $task_name=$getcheck->task_name;
                        $review=TaskReview::where('id',$completed)->first();
                        $comment=$review->comment;

                         $email_view = 'admin.emails.task_userupdate';
                            $subject = 'Task#'.$getcheck->id.'Task status update';
                            if($email!=null){
                            
                             $sent= Mail::send($email_view, ['task_id' =>$taskId,'task_name'=>$task_name,'status'=>$status,'comment'=>$comment], function($message) use ($email,$subject,$username)
                                {
                                    $message->to($email,$username)->subject($subject);
                                });
                          
                            }

                        }
                        CustomHelpers::userActionLog($action='Task Review Add',$completed,0);
                        return 'success';

                    }
                }else{
                    return 'This task status already change'; 
                }
        //    }
        }
        else{
            return "something wen't wrong";
        }
        return 'error';
        
    }
    public function employee_assignedtask_statusupdate(Request $request)
    {
        $taskId = $request->input('taskId');
        $status = $request->input('status');
        $comment = $request->input('comment');
        $todaydate =date('Y-m-d');
        $getcheck = TaskDetails::where('id',$taskId)->first();

        if($getcheck)
        {
            if($getcheck->status=='Closed'){
                
                return "Closed Task Status Can't update"; 
            }else{

                if($getcheck->status=='Pending' &&  $status=='ReOpen'){
                     return "Pending Task Status Can't Re-Open";
                 }else{

                    $customer_cofirmation = TaskDetails::where('id',$taskId)->update([
                    'status' => $request->input('status')
                     ]);
                    if($customer_cofirmation){
                        $data = [
                        'task_details_id' =>$taskId,
                        'comment' => $request->input('comment'),
                        'reviewer_id' =>Auth::id(),
                        'review_date' =>date('Y-m-d'),
                        'status' =>$request->input('status'),

                    ];
                    CustomHelpers::userActionLog($action='Task Details status Update',$taskId,0);
                    $completed = TaskReview::insertGetId($data);

                        $users=Users::where('id',$getcheck->user_id)->first();
                        $email = $users->email;
                        $username = $users->name.' '.$users->middle_name.' '.$users->last_name;
                        $task_name=$getcheck->task_name;
                        $review=TaskReview::where('id',$completed)->first();
                        $comment=$review->comment;

                    if($completed){

                      if($status=='ReOpen'){
                        $email_view = 'admin.emails.task_reopen';
                        $subject = 'Task#'.$getcheck->id.' ReOpened';

                        if($email!=null){
                            
                            $sent= Mail::send($email_view, ['task_id' =>$taskId,'task_name'=>$task_name,'status'=>$status,'comment'=>$comment], function($message) use ($email,$subject,$username)
                                {
                                    $message->to($email,$username)->subject($subject);
                                });
                          
                            }
                        }else if($status=='RequestUpdate'){
                            $email_view = 'admin.emails.task_requestupdate';
                            $subject = 'Task#'.$getcheck->id.' RequestUpdate';
                            if($email!=null){
                            
                             $sent= Mail::send($email_view, ['task_id' =>$taskId,'task_name'=>$task_name,'status'=>$status,'comment'=>$comment], function($message) use ($email,$subject,$username)
                                {
                                    $message->to($email,$username)->subject($subject);
                                });
                          
                            }
                        }else if($status=='Closed'){
                            $email_view = 'admin.emails.task_closed';
                            $subject = 'Task#'.$getcheck->id.' Closed';
                            if($email!=null){
                            
                             $sent= Mail::send($email_view, ['task_id' =>$taskId,'task_name'=>$task_name,'status'=>$status,'comment'=>$comment], function($message) use ($email,$subject,$username)
                                {
                                    $message->to($email,$username)->subject($subject);
                                });
                          
                            }
                        }
                       
                    }
                    CustomHelpers::userActionLog($action='Task Review Add',$completed,0);
                    return 'success';
                }
                 }

            }     
            
        }
        else{
            return "something wen't wrong";
        }
        return 'error';
        
    }

}