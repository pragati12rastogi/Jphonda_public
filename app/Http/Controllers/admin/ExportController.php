<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;
use DB;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use\App\Model\Attendance;
use\App\Model\Users;
use \Carbon\Carbon;
use App\Exports\DataExport;
use App\Exports\DataExportSheet;
use \App\Custom\CustomHelpers;
use Auth;


class ExportController extends Controller {
	

	public function getTableData($table,$field) {
		if($field=="created_time"){
			$data = DB::table($table)->select(DB::raw('DATE_FORMAT(created_time,"%Y-%m-%d") as data'))->distinct()->get();
		}
		else if($field=="closed_date"){
			$data = DB::table($table)->select(DB::raw('DATE_FORMAT(closed_date,"%Y-%m-%d") as data'))->distinct()->get();
		}
		else if($field=="created_at"){
			$data = DB::table($table)->select(DB::raw('DATE_FORMAT(created_at,"%Y-%m-%d") as data'))->distinct()->get();
		}
		else if($table=="created_user"){
			$data = DB::table('users')->select('users.name as data')->distinct()->get();
		}
		else if($table=="closed_user"){
			$data = DB::table('users')->select('users.name as data')->distinct()->get();
		}
		else{
			$data = DB::table($table)->select($field.' as data')->distinct()->get();
		}
		
		return $data;
	}


	public function export_data_attendance(){
		if(isset($_GET["month"]))
			$from = $_GET["month"];
		else 
			$from = date('m');
		if(isset($_GET["year"]))
			$to = $_GET["year"];
		else 
			$to = date('Y');
		if(isset($_GET["date"]))
			$date = $_GET["date"];
		else 
			$date = '';
		if(isset($_GET["emp_id"]))
			$emp_id = $_GET["emp_id"];
		else 
			$emp_id = 0;
		if(isset($_GET["store"]))
			$store = $_GET["store"];
		else 
			$store = 0;
		return $this->export_data('attendance',$from,$to,$date,$emp_id,$store);
	}

	public function export_data_report_attendance(){
		if(isset($_GET["month"]))
			$from = $_GET["month"];
		else 
			$from = date('m');
		if(isset($_GET["year"]))
			$to = $_GET["year"];
		else 
			$to = date('Y');
		if(isset($_GET["date"]))
			$date = $_GET["date"];
		else 
			$date = '';
		if(isset($_GET["emp_id"]))
			$emp_id = $_GET["emp_id"];
		else 
			$emp_id = 0;
		return $this->export_data('reportAttendance',$from,$to,$date,$emp_id);
	}

	public function export_data_employee_attendance(){
		$date = '';
		if(isset($_GET["month"]))
			$from = $_GET["month"];
		else 
			$from = date('m');
		if(isset($_GET["year"]))
			$to = $_GET["year"];
		else 
			$to = date('Y');
		if(isset($_GET["emp_id"]))
			$emp_id = $_GET["emp_id"];
		else 
			$emp_id = 0;
		return $this->export_data('EmployeeAttendance',$from,$to,$date,$emp_id);
	}

	public function export_data_user(){
		if(isset($_GET["store"]))
			$from = $_GET["store"];
		else 
			$from = 0;
		return $this->export_data('user',$from);
	}

	public function export_data($id,$d1='',$d2='',$date='',$emp_id='',$store='')
	{
		$form=$id;
		$table = array(
			'consignee'=>'consignee'
				);
        $name=array(
			"attendance"=>"Attendance Export",
			"reportAttendance" => "Report Attendance Export",
			"EmployeeAttendance" => "Employee Attendance Export",
			"user" => "User Export"
		);
		$sheet_name=array(
			"attendance"=>array('Attendance Export'),
			"reportAttendance"=>array('Report Attendance Export'),
			"EmployeeAttendance"=>array('Employee Attendance Export'),
			"user"=>array('Active Users','Inactive User','Resigned User')
			);
		$column =
		array(
			"attendance"=>array(
					array('attendance.emp_id'=>'Employee Id.','users.name'=>'Name','attendance.status'=>'Status','attendance.intime' => 'In Time', 'attendance.outtime' => 'Out Time', 'attendance.late_by' => 'Late Coming', 'attendance.early_going' => 'Early Going','attendance.halfday'=>'Half Day','attendance.date'=>'Month','store.name'=>'Store Name' )
			),
			"reportAttendance"=>array(
					array('attendance.emp_id'=>'Employee Id.','users.name'=>'Name','attendance.status'=>'Status','attendance.intime' => 'In Time', 'attendance.outtime' => 'Out Time', 'attendance.late_by' => 'Late Coming', 'attendance.early_going' => 'Early Going','attendance.halfday'=>'Half Day','attendance.date'=>'Month','store.name'=>'Store Name' )
			),
			"EmployeeAttendance"=>array(
					array('attendance.emp_id'=>'Employee Id.','users.name'=>'Name')
			),
			"user"=>array(
				array(
					'users.emp_id' => 'Employee Id','users.name'=>'Name','users.phone'=>'Mobile No.','users.dob'=>'DOB','users.relation_type'=>'Relation Type',
					'users.relation_name'=>'Relation Name','users.state'=>'State',
					'users.city'=>'City',
					'users.status'=>'Status','users.alias_name'=>'Alias Name',
					'users.aadhar'=>'Aadhar','users.pancard'=>'Pincode','users.doj'=>'Date of Joining'),
				array(
					'users.emp_id' => 'Employee Id','users.name'=>'Name','users.phone'=>'Mobile No.','users.dob'=>'DOB','users.relation_type'=>'Relation Type',
					'users.relation_name'=>'Relation Name','users.state'=>'State',
					'users.city'=>'City',
					'users.status'=>'Status','users.alias_name'=>'Alias Name',
					'users.aadhar'=>'Aadhar','users.pancard'=>'Pincode','users.doj'=>'Date of Joining'),
				array(
					'users.emp_id' => 'Employee Id','users.name'=>'Name','users.phone'=>'Mobile No.','users.dob'=>'DOB','users.relation_type'=>'Relation Type',
					'users.relation_name'=>'Relation Name','users.state'=>'State',
					'users.city'=>'City',
					'users.status'=>'Status','users.alias_name'=>'Alias Name',
					'users.aadhar'=>'Aadhar','users.pancard'=>'Pincode','users.doj'=>'Date of Joining')
				),


		);
        if(isset($name[$id]))
        {
			$title='Export '.$name[$id];
			$columns = $column[$id];
            $data=array(
                'layout'=>'layouts.main',
				'title'=>$title,
				'sheet_name'=>$sheet_name[$id],
				'form'=>$form,
				'd1'=>$d1,
				'd2'=>$d2,
				'date' => $date,
				'emp_id'=>$emp_id,
				'store'=>$store,
				'columns'=>$columns,
            );
            return view('admin.export.export_form', $data);
		}
        else
            return abort(404);

	}


	public function attendance(Request $request,$column=[]){
		$outcolumn = [];
		$emp = $_GET["emp_id"];
		$year = $_GET["to"];
		$month = $_GET["from"];
		if ($year == null) {
			$year = date('Y');
		}
		if ($month == null) {
			$month = date('m');
		}
		
		$date = $_GET["date"];
		$store = $_GET["store"];
    	$outcolumn = [];
		$outcolumn1 =['attendance.emp_id'=>'Employee Id.','users.name'=>'Name','attendance.status'=>'Status','attendance.intime' => 'In Time', 'attendance.outtime' => 'Out Time', 'attendance.late_by' => 'Late Coming', 'attendance.early_going' => 'Early Going','attendance.half_day'=>'Half Day','attendance.date'=>'Month','store.name'=>'Store Name' ];
		if($request->input('columns_in_excel0')=='')
		{
			$column =['attendance.emp_id',DB::raw('concat(users.name," ",ifnull( users.middle_name," ")," ",ifnull( users.last_name," ")) as name'),'attendance.status','attendance.intime', 'attendance.outtime', 'attendance.late_by', 'attendance.early_going',DB::raw('IF(attendance.half_day = 1, "Yes","No") as half_day'),DB::raw("MonthName(attendance.date) date"),DB::raw('concat(store.name,"-",store.store_type) as store_name') ];
			$outcolumn =['Employee Id.','Name','Status','In Time', 'Out Time','Late Coming','Early Going','Half Day','Month','Store Name'];
		}
		else
		{
			$column = $request->input('columns_in_excel0');
			foreach($column as $k)
				$outcolumn =array_merge($outcolumn,array($outcolumn1[$k]));
		}
        
        $user = Auth::user();
        $user_type = $user['user_type'];
        $role = $user['role'];
        if($user_type=="superadmin" || $role=='HRDManager'){
          $db_data = Attendance::leftJoin('users','attendance.emp_id','users.emp_id')
            ->leftJoin('store','attendance.store_id','store.id')
            ->leftjoin('user_details','user_details.user_id','users.id')
            ->leftjoin('shift_timing','user_details.shift_timing_id','shift_timing.id')
            ->whereYear('attendance.date',$year)
            ->whereMonth('attendance.date',$month);
        }else{
            $db_data = Attendance::leftJoin('users','attendance.emp_id','users.emp_id')
            ->leftJoin('store','attendance.store_id','store.id')
            ->where('attendance.emp_id',Auth::user()->emp_id)
            ->whereYear('attendance.date',$year)
            ->whereMonth('attendance.date',$month);
        }
        if ($emp != 0 || $emp != null) {
        	$db_data->where(function($query) use ($emp){
				$query->where('attendance.emp_id','=',$emp);
			}); 
        }
        if ($date != '' || $date != null) {
        	$db_data->where(function($query) use ($date){
				$query->where('attendance.date','=',$date);
			}); 
        }

        if ($store != 0 || $store != null) {
        	$db_data->where(function($query) use ($store){
				$query->where('attendance.store_id','=',$store);
			}); 
        }

       
		if($request->input('search_in_excel0')!='')
		{
			$db_data = $db_data->where(explode(' ',$request->input('search_in_excel0'))[0],$request->input('search_val_in_excel0')); 
		}
	
		if($request->input('order_in_excel0')!='')
		{
			foreach($request->input('order_in_excel0') as $order)
			{
				$col = explode(' ',$order)[0];
				$by= explode(' ',$order)[1];
				$db_data = $db_data->orderBy($col,$by);
			}
		}
		else
		{
			$db_data = $db_data->orderBy('attendance.id','desc');			
		}
		$db_data=$db_data->select($column)->get();
		return Excel::download(new DataExport($db_data,$outcolumn,'attendance'), 'attendance.xlsx');
    }

    public function reportAttendance(Request $request,$column=[]){
    	$outcolumn = [];
    	$emp = $_GET["emp_id"];
		$year = $_GET["to"];
		$month = $_GET["from"];
		if ($year == null) {
			$year = date('Y');
		}
		if ($month == null) {
			$month = date('m');
		}
		$date = $_GET["date"];
    	$outcolumn = [];
		$outcolumn1 =['attendance.emp_id'=>'Employee Id.','users.name'=>'Name','attendance.status'=>'Status','attendance.intime' => 'In Time', 'attendance.outtime' => 'Out Time', 'attendance.late_by' => 'Late Coming', 'attendance.early_going' => 'Early Going','attendance.half_day'=>'Half Day','attendance.date'=>'Month','store.name'=>'Store Name' ];
		if($request->input('columns_in_excel0')=='')
		{
			$column =['attendance.emp_id',DB::raw('concat(users.name," ",ifnull( users.middle_name," ")," ",ifnull( users.last_name," ")) as name'),'attendance.status','attendance.intime', 'attendance.outtime', 'attendance.late_by', 'attendance.early_going',DB::raw('IF(attendance.half_day = 1, "Yes","No") as half_day'),DB::raw("MonthName(attendance.date) date"),DB::raw('concat(store.name,"-",store.store_type) as store_name') ];
			$outcolumn =['Employee Id.','Name','Status','In Time', 'Out Time','Late Coming','Early Going','Half Day','Month','Store Name'];
		}
		else
		{
			$column = $request->input('columns_in_excel0');
			foreach($column as $k)
				$outcolumn =array_merge($outcolumn,array($outcolumn1[$k]));
		}


		$db_data = Attendance::leftJoin('users','attendance.emp_id','users.emp_id')
            ->leftJoin('user_details','users.id','user_details.user_id')
            ->leftjoin('shift_timing','user_details.shift_timing_id','shift_timing.id')
            ->leftJoin('store','attendance.store_id','store.id')
            ->where('user_details.reporting_manager',Auth::id())
            ->whereMonth('attendance.date',$month)
            ->whereYear('attendance.date',$year);

        if ($emp != 0 || $emp != null) {
        	$db_data->where(function($query) use ($emp){
				$query->where('attendance.emp_id','=',$emp);
			}); 
        }
        if ($date != '' || $date != null) {
        	$db_data->where(function($query) use ($date){
				$query->where('attendance.date','=',$date);
			}); 
        }

		if($request->input('search_in_excel0')!='')
		{
			$db_data = $db_data->where(explode(' ',$request->input('search_in_excel0'))[0],$request->input('search_val_in_excel0')); 
		}
	
		if($request->input('order_in_excel0')!='')
		{
			foreach($request->input('order_in_excel0') as $order)
			{
				$col = explode(' ',$order)[0];
				$by= explode(' ',$order)[1];
				$db_data = $db_data->orderBy($col,$by);
			}
		}
		else
		{
			$db_data = $db_data->orderBy('attendance.id','desc');			
		}
		$db_data=$db_data->select($column)->get();

		return Excel::download(new DataExport($db_data,$outcolumn,'attendance'), 'attendance.xlsx');
    }

    public function EmployeeAttendance(Request $request,$column=[]){
    	$outcolumn = [];
    	$emp = $_GET["emp_id"];
		$yr = $_GET["to"];
		$mon = $_GET["from"];
		if ($yr == null) {
			$yr = date('Y');
		}
		if ($mon == null) {
			$mon = date('m');
		}
        $days = date('t',strtotime($yr));
        $month_name = date('M',strtotime($yr));
        $year = date('Y',strtotime($yr));
        $days_arr=Array();
        for ($j = 1; $j <= $days ; $j++) {
            if($j<10){$md="0".$j."_".$month_name;}  
            else{$md=$j."_".$month_name;}
            $date=$j."-".$mon."-".$year;
            $date=date('Y-m-d',strtotime($date));
           $days_arr[$md]=$md;

        }

		$outcolumn1 = [	
			  	'users.name' => 'Name'
		];
		if($request->input('columns_in_excel0') == '') {
			$column = [
			    'users.name'
			];
			$col = array(
				'Employee Id' => 'Employee Id',
				'Name' => 'Name',
				'Store'=> 'Store',
		        'Month' => 'Month',
			);
			$coll = array_merge($col,$days_arr);
		$outcolumn = $coll;
			
		} else {
			$column = $request->input('columns_in_excel0');
			foreach($column as $k)
				$outcolumn = array_merge($outcolumn,array($outcolumn1[$k]));
		}
       

      
        $leavess=Array();
        for ($j = 1; $j <= $days ; $j++) {
            if($j<10){$md="0".$j."_".$month_name;}  
            else{$md=$j."_".$month_name;}
                
            $date=$j."-".$mon."-".$year;
            $date=date('Y-m-d',strtotime($date));
            $query[$j] = "IFNULL((SELECT att.status FROM attendance att WHERE  att.emp_id = attendance.emp_id AND YEAR(att.date)=".$year.".  AND att.date = '".$date."' ),'') as ".$md." ";

        }
        
        $query = join(",",$query);
        $db_data = Attendance::leftJoin('users','attendance.emp_id','users.emp_id')
            ->leftJoin('store','attendance.store_id','store.id')
            ->leftjoin('user_details','user_details.user_id','users.id')
            ->leftjoin('shift_timing','user_details.shift_timing_id','shift_timing.id')
            ->whereMonth('attendance.date', date($mon))
            ->select(
            	'users.emp_id',
                 DB::raw('concat(users.name," ",ifnull( users.middle_name," ")," ",ifnull( users.last_name," ")) as name'),
                DB::raw('concat(store.name,"-",store.store_type) as store_name'),
                DB::raw('DATE_FORMAT(attendance.date,"%Y-%m") as date'),
                DB::raw($query)
            )->groupBy('attendance.emp_id');

        if($emp != 0) {
            $db_data->where(function($query) use ($emp){
                $query->where('attendance.emp_id','=',$emp);
            });
        } 


		if($request->input('search_in_excel0') != '') {
			$db_data = $db_data->where(explode(' ',$request->input('search_in_excel0'))[0],$request->input('search_val_in_excel0')); 
		}
		if($request->input('order_in_excel0') != '') {
			foreach($request->input('order_in_excel0') as $order) {
				$col = explode(' ',$order)[0];
				$by = explode(' ',$order)[1];
				$db_data = $db_data->orderBy($col,$by);
			}
		} else {
			$db_data = $db_data->orderBy('attendance.id','desc');			
		}
		$db_data = $db_data->get();
		
		return Excel::download(new DataExport($db_data,$outcolumn,'EmployeeAttendance'), 'EmployeeAttendance.xlsx');

    }

    public function user(Request $request,$column=[]){
    	$outcolumn = [];
		$sheet = array('active','inactive','resigned');
		$outcolumn1 = array(
			'active' =>array( 'users.emp_id' => 'Employee Id','users.name'=>'Name','users.phone'=>'Mobile No.','users.dob'=>'DOB','users.relation_type'=>'Relation Type','users.relation_name'=>'Relation Name','users.state'=>'State','users.city'=>'City','users.status'=>'Status','users.alias_name'=>'Alias Name','users.aadhar'=>'Aadhar','users.pancard'=>'Pincode','users.doj'=>'Date of Joining'),
			'inactive' =>array('users.emp_id' => 'Employee Id','users.name'=>'Name','users.phone'=>'Mobile No.','users.dob'=>'DOB','users.relation_type'=>'Relation Type','users.relation_name'=>'Relation Name','users.state'=>'State','users.city'=>'City','users.status'=>'Status','users.alias_name'=>'Alias Name','users.aadhar'=>'Aadhar','users.pancard'=>'Pincode','users.doj'=>'Date of Joining'),
			'resigned' =>array( 'users.emp_id' => 'Employee Id','users.name'=>'Name','users.phone'=>'Mobile No.','users.dob'=>'DOB','users.relation_type'=>'Relation Type','users.relation_name'=>'Relation Name','users.state'=>'State','users.city'=>'City','users.status'=>'Status','users.alias_name'=>'Alias Name','users.aadhar'=>'Aadhar','users.pancard'=>'Pincode','users.doj'=>'Date of Joining')
			
		);

		$col =array(
			'active'=>array('users.emp_id',DB::raw('concat(users.name," ",ifnull( users.middle_name," ")," ",ifnull( users.last_name," ")) as name'),'users.phone',DB::raw("DATE_FORMAT(users.dob, '%d-%m-%Y') as dob"),'users.relation_type','users.relation_name','states.name as state_name','cities.city as city_name',DB::raw('IF(users.status = 1, "Active"," ") as status'),'users.alias_name','users.aadhar','users.pincode', DB::raw("DATE_FORMAT(users.doj, '%d-%m-%Y') as doj")),

			'inactive' =>array('users.emp_id',DB::raw('concat(users.name," ",ifnull( users.middle_name," ")," ",ifnull( users.last_name," ")) as name'),'users.phone',DB::raw("DATE_FORMAT(users.dob, '%d-%m-%Y') as dob"),'users.relation_type','users.relation_name','states.name as state_name','cities.city as city_name',DB::raw('IF(users.status = 0, "Inactive"," ") as status'),'users.alias_name','users.aadhar','users.pincode', DB::raw("DATE_FORMAT(users.doj, '%d-%m-%Y') as doj")),

			'resigned'=>array('users.emp_id',DB::raw('concat(users.name," ",ifnull( users.middle_name," ")," ",ifnull( users.last_name," ")) as name'),'users.phone',DB::raw("DATE_FORMAT(users.dob, '%d-%m-%Y') as dob"),'users.relation_type','users.relation_name','states.name as state_name','cities.city as city_name',DB::raw('IF(users.status = 2, "Resigned"," ") as status'),'users.alias_name','users.aadhar','users.pincode', DB::raw("DATE_FORMAT(users.doj, '%d-%m-%Y') as doj"))
				
		);
		$outcol =array(
			'active' =>array('Employee Id','Name','Mobile No.','DOB','Relation Type','Relation Name','State','City','Status','Alias Name','Aadhar','Pincode','Date of Joining'
			),

			'inactive' =>array('Employee Id','Name','Mobile No.','DOB','Relation Type','Relation Name','State','City','Status','Alias Name','Aadhar','Pincode','Date of Joining'),

			'resigned' =>array('Employee Id','Name','Mobile No.','DOB','Relation Type','Relation Name','State','City','Status','Alias Name','Aadhar','Pincode','Date of Joining'
			)

			
		);		

		for($i=0;$i<count($sheet);$i++)
		{
			$column[$sheet[$i]]=[];
			$outcolumn[$sheet[$i]]=[];
			
			if($request->input('columns_in_excel'.$i)=='')
			{
				$column[$sheet[$i]] = $col[$sheet[$i]];
				$outcolumn[$sheet[$i]] =$outcol[$sheet[$i]];
			}
			else
			{
				$column[$sheet[$i]] = $request->input('columns_in_excel'.$i);
				foreach($column[$sheet[$i]] as $k)
					$outcolumn[$sheet[$i]] =array_merge($outcolumn[$sheet[$i]],array($outcolumn1[$sheet[$i]][$k]));
			}
		}
		

		$db_data['active'] = Users::leftJoin('states','users.state','states.id')
                        ->leftJoin('cities','users.city','cities.id')
                        ->where('users.status','1');
        $db_data['inactive'] = Users::leftJoin('states','users.state','states.id')
                        ->leftJoin('cities','users.city','cities.id')
                        ->where('users.status','0');
        $db_data['resigned'] = Users::leftJoin('states','users.state','states.id')
                        ->leftJoin('cities','users.city','cities.id')
                        ->where('users.status','2');



		for($i=0;$i<count($sheet);$i++)
			if($request->input('search_in_excel'.$i)!='')
			{
				$db_data[$sheet[$i]] = $db_data[$sheet[$i]]->where(explode(' ',$request->input('search_in_excel'.$i))[0],$request->input('search_val_in_excel'.$i)); 
			}
		if($request->input('order_in_excel0')!='')
		{
			foreach($request->input('order_in_excel0') as $order)
			{
				$col = explode(' ',$order)[0];
				$by= explode(' ',$order)[1];
				$db_data[$sheet[0]] = $db_data[$sheet[0]]->orderBy($col,$by);
				$db_data[$sheet[1]] = $db_data[$sheet[1]]->orderBy($col,$by);
				$db_data[$sheet[2]] = $db_data[$sheet[2]]->orderBy($col,$by);
				}
			}
		else
		{
			$db_data[$sheet[0]] = $db_data[$sheet[0]]->orderBy('users.id','desc');			
			$db_data[$sheet[1]] = $db_data[$sheet[1]]->orderBy('users.id','desc');			
			$db_data[$sheet[2]] = $db_data[$sheet[2]]->orderBy('users.id','desc');			
		}

		$data = $db_data[$sheet[1]]->select(DB::raw('users.id'))->get();		
		$db_data[$sheet[0]] = $db_data[$sheet[0]]->select($column[$sheet[0]])->get();

		for($i=1;$i<count($sheet);$i++)
			$db_data[$sheet[$i]] = $db_data[$sheet[$i]]
			->select($column[$sheet[$i]])->get();

		return Excel::download(new DataExportSheet($db_data,$outcolumn,$sheet), 'user.xlsx');
    }

}
?>
