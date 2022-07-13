<?php

namespace App\Http\Controllers\admin;
use Illuminate\Http\Request;
use \stdClass;
use \App\Model\Store;
use \App\Custom\CustomHelpers;
use App\Model\Users;
use \App\Model\Countries;
use \App\Model\City;
use \App\Model\State;
use \App\Model\Settings;
use \App\Model\Master;
use \App\Model\HR_Leave;
use\App\Model\LeaveBalance;
use\App\Model\HRLeaveDetails;
use \App\Model\NOP;
use \App\Model\PenaltyMaster;
use \App\Model\UserPenalty;
use \App\Model\ShiftTiming;
use\App\Model\UserDetails;
use\App\Model\Payroll;
use\App\Model\PayrollSalary;
use\App\Model\SalaryAdvance;
use\App\Model\PayrollSalaryDetail;
use\App\Model\Attendance;
use\App\Model\HolidayFestival;
use\App\Model\OverTime;
use\App\Model\PayrollBonus;
use\App\Model\PayrollIncrement;
use\App\Model\PayrollSalaryAdjustment;
use\App\Model\PayrollPaidAdvance;
use\App\Model\PayrollSalaryAdvance;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use App\Imports\Import;
use \Carbon\Carbon;
use Auth;
use Hash;
use PDF;
use DateTime;
use Config;
use Mail;
use File;
use DateInterval;
use DatePeriod;


class PayrollController extends Controller {
    public function __construct() {
    }
  

    public function update_salary($id){
        $customerdata = Users::leftJoin('payroll','users.id','=','payroll.user_id')
                    ->where('users.id',$id)
                    ->select('payroll.id','payroll.user_id','payroll.basic_salary','payroll.hra','payroll.ta','payroll.perf_allowance','payroll.others','payroll.other_deduction','payroll.pf_deduction','payroll.tax_deduction','payroll.pf')
                    ->get()
                    ->first();
         if(!$customerdata){
             return redirect('/admin/hr/user/list/')->with('error','Id not exist');
         }else{
           $data = array(
            'customer'=>$customerdata,
            'id'=>$id,
            'layout'=>'layouts.main'
        );
        return view('admin.hr.salary_details_update',$data); 
         }

    }

    public function update_salary_DB(Request $request,$id){
         try {
           $timestamp = date('Y-m-d G:i:s');
            $this->validate($request,[
                'salary'=>'required',
                'hra'=>'required',
                'ta'=>'required',        
                'total_provident_fund'=>'required',           
                'total_deduction'=>'required', 
                'gross_salary'=>'required',           
                'net_salary'=>'required',              
            ],[
                'salary.required'=> 'This field is required.',
                'hra.required'=> 'This field is required.',
                'ta.required'=> 'This field is required.',                  
                'total_provident_fund.required'=> 'This field is required.',                 
                'total_deduction.required'=> 'This field is required.',  
                'gross_salary.required'=> 'This field is required.',                 
                'net_salary.required'=> 'This field is required.',                 
            ]);


            $checkdata = Payroll::where('user_id',$id)->get()->first();
            if ($checkdata && $checkdata['basic_salary'] != 0) {
                return redirect('/admin/payroll/user/salaryupdate/'.$id)->with('error','Salary details already updated.'); 
            }
            if ($checkdata) {
                $customerdata = Payroll::where('user_id',$id)->update([
                    'basic_salary'=>$request->input('salary'),
                    'user_id'=>$id,
                    'hra'=>$request->input('hra'),
                    'ta'=>$request->input('ta'),
                    'pf'=> ($request->input('pf') != ''?$request->input('pf'):0),
                    'net_salary'=> ($request->input('net_salary') != ''?$request->input('net_salary'):0),
                    'perf_allowance'=> ($request->input('perf_allowance') != ''?$request->input('perf_allowance'):0),
                    'others'=>$request->input('others'),
                    'tax_deduction' => $request->input('tax_deduction'),
                    'pf_deduction' => ($request->input('provident_fund') != ''?$request->input('provident_fund'):0),
                    'other_deduction' => ($request->input('other_deduction') != ''?$request->input('other_deduction'):0)
                    ]);
                if($customerdata == NULL){
                    return redirect('/admin/payroll/user/salaryupdate/'.$id)->with('error','Some Unexpected Error occurred.'); 
                }else{

                    CustomHelpers::userActionLog($action='Salary Details Add',$id,0);
                    return redirect('/admin/payroll/user/salaryupdate/'.$id)->with("success","Salary Details added successfully.");
                }
                 
            }else{
                $customerdata = Payroll::insertGetId([
                    'basic_salary'=>$request->input('salary'),
                    'user_id'=>$id,
                    'hra'=>$request->input('hra'),
                    'ta'=>$request->input('ta'),
                    'pf'=> ($request->input('pf') != ''?$request->input('pf'):0),
                    'net_salary'=> ($request->input('net_salary') != ''?$request->input('net_salary'):0),
                    'perf_allowance'=>($request->input('perf_allowance') != ''?$request->input('perf_allowance'):0),
                    'others'=> ($request->input('others') != ''?$request->input('others'):0),
                    'tax_deduction' => ($request->input('tax_deduction') != ''?$request->input('tax_deduction'):0),
                    'pf_deduction' => ($request->input('provident_fund') != ''?$request->input('provident_fund'):0),
                    'other_deduction' => ($request->input('other_deduction') != ''?$request->input('other_deduction'):0)
                    ]);
                if($customerdata == NULL){
                    return redirect('/admin/payroll/user/salaryupdate/'.$id)->with('error','Some Unexpected Error occurred.'); 
                }else{
                     CustomHelpers::userActionLog($action='Salary Details Add',$id,0);
                    return redirect('/admin/payroll/user/salaryupdate/'.$id)->with("success"," Salary Details added successfully.");
                }
             }   
          
        }  catch(\Illuminate\Database\QueryException $ex) {
            return redirect('/admin/payroll/user/salaryupdate/'.$id)->with('error','some error occurred'.$ex->getMessage())->withInput();
        }
    }

    public function salaryadvance()
    {
        $users = Users::whereIn('store_id',CustomHelpers::user_store())->get();
        $data = [
            'users'=>$users,
            'layout' => 'layouts.main'
        ];
        
        return view('admin.hr.salaryadvance',$data);
    }

    public function salaryadvance_DB(Request $request){

        $validateZero = [];
        $validateZeroMsg = [];
        $this->validate($request,array_merge($validateZero,[  
                'employee'=>'required',
                'advance_equested'=>'required',
                'amount_applied'=>'required',
                'no_installments'=>'required',
                'purpose_loan'=>'required',
                'documents_attached'=>'required',

            ]),array_merge($validateZeroMsg,[
                'employee.required'=> 'This is required.',
                'advance_equested.required'=> 'This is required.',
                'amount_applied.required'=> 'This is required.',
                'no_installments.required'=> 'This is required.',
                'purpose_loan.required'=> 'This Field is required.',
                'documents_attached.required'=> 'This Field is required.',
                'any_other_loan.required_if'=> 'This Field is required.'
            ]));
         try {
        
            if($request->input('purpose_loan') == 'If any other, Please specify' && (!$request->input('any_other_loan')))
            {
               return back()->with('error','Any Other loan Field is required, Please Enter Any Other loan')->withInput();
            }

            
            $customer = PayrollSalaryAdvance::insertGetId(
                [ 
                    'user_id'=>$request->input('employee'),
                    'loan_type'=>$request->input('advance_equested'),
                    'amount_requested'=>$request->input('amount_applied'),
                    'installment'=>$request->input('no_installments'),
                    'loan_purpose'=>$request->input('purpose_loan'),
                    'document'=>$request->input('documents_attached'),
                    'any_other_loan'=>$request->input('any_other_loan'),
               
                ]
            );

            if($customer==NULL) 
            {
                DB::rollback();
                return redirect('/admin/hr/user/salaryadvance/')->with('error','Some Unexpected Error occurred.');
            }
            else{

                CustomHelpers::userActionLog($action='Salary Advance Add',$customer,0);
                return redirect('/admin/hr/user/salaryadvance/')->with('success','Successfully Salary Advance.'); 
             }    
        
        }  catch(\Illuminate\Database\QueryException $ex) {
            return redirect('/admin/hr/user/salaryadvance/')->with('error','some error occurred'.$ex->getMessage())->withInput();
        }
    }

    public function salaryadvance_list() {


        $store = Store::whereIn('id',CustomHelpers::user_store())
                    ->select('id',DB::raw('concat(store.name,"-",store.store_type) as name'))->get();
        $users= Users::select('emp_id as id','name','middle_name','last_name')->get();
        $data=array('users'=>$users,'store'=>$store,'layout'=>'layouts.main');
        return view('admin.hr.salaryadvance_list', $data); 
    }

    public function salaryadvance_data(Request $request){

        $search = $request->input('search');
        $store_name=$request->input('store_name');
        $user_name=$request->input('user_name');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
        
        $store = CustomHelpers::user_store();
                   
        $api_data= Users::Join('payroll_salary_advance','users.id','payroll_salary_advance.user_id')
                 ->leftJoin('users as level1','level1.id','payroll_salary_advance.approve_rejected_by')
                  ->whereIn('users.store_id',$store)
                  ->where('payroll_salary_advance.status','Pending')
                  ->leftJoin('store','users.store_id','store.id')
                    ->select(
                    'payroll_salary_advance.id',
                    'users.name',
                    'users.middle_name',
                    'users.last_name',
                    'users.emp_id',
                    'payroll_salary_advance.loan_type',
                    'payroll_salary_advance.amount_requested',
                    'payroll_salary_advance.installment',
                    'payroll_salary_advance.loan_purpose',
                    'payroll_salary_advance.document',
                    'payroll_salary_advance.amount',
                    'payroll_salary_advance.user_id',
                    'payroll_salary_advance.status',
                    'level1.name as level1'
                    );
            if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('users.name','like',"%".$serach_value."%")
                    ->orwhere('payroll_salary_advance.loan_type','like',"%".$serach_value."%")
                    ->orwhere('payroll_salary_advance.amount_requested','like',"%".$serach_value."%")
                    ->orwhere('payroll_salary_advance.installment','like',"%".$serach_value."%")
                    ->orwhere('payroll_salary_advance.loan_purpose','like',"%".$serach_value."%")
                    ->orwhere('payroll_salary_advance.document','like',"%".$serach_value."%")
                    ->orwhere('payroll_salary_advance.amount','like',"%".$serach_value."%")
                    ->orwhere('payroll_salary_advance.status','like',"%".$serach_value."%")
                    ->orwhere('level1.name','like',"%".$serach_value."%")
                    ->orwhere('users.emp_id','like',"%".$serach_value."%")
                    
                    ;
                });
            }
            if(!empty($store_name))
            {
                $api_data->where(function($query) use ($store_name){
                        $query->where('users.store_id','like',"%".$store_name."%");
                    });
               
            } if(isset($user_name) )
            {
               $api_data->where(function($query) use ($user_name){
                        $query->where('users.emp_id',$user_name);
                    });               
            }
            
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                'payroll_salary_advance.id',
                'users.emp_id',
                'users.name',
                'payroll_salary_advance.loan_type',
                'payroll_salary_advance.amount_requested',
                'payroll_salary_advance.amount',
                'payroll_salary_advance.installment',
                'payroll_salary_advance.loan_purpose',
                'payroll_salary_advance.document',
                'payroll_salary_advance.status',
                'level1'
                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('payroll_salary_advance.id','desc'); 
                $count = count( $api_data->get()->toArray());
                $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
                $array['recordsTotal'] = $count;
                $array['recordsFiltered'] = $count;
                $array['data'] = $api_data; 
                return json_encode($array);
    }

   

    public function ImportSalary() {
        $store = Store::whereIn('id',CustomHelpers::user_store())
                    ->select('id',DB::raw('concat(store.name,"-",store.store_type) as name'))->get();
        $data = [
            'layout' => 'layouts.main',
            'store' =>  $store
        ];
        
        return view('admin.hr.import_salary',$data);
    }

    public function checkExcelFile($file_ext){
        $allowed_extension = array('xls','xlsx','xlt','xltm','xltx','xlsm');
        return in_array($file_ext,$allowed_extension) ? true : false;
    }

    public function ImportSalary_DB(Request $request) {
        $validator = Validator::make($request->all(),[    
            'salary'  => 'required', 
        ],[
            'salary.required'=> 'This field is required.',
        ]);
        if($request->hasFile('salary')) {
            $validator->after(function ($validator) use ($request){
                if($this->checkExcelFile($request->file('salary')->getClientOriginalExtension()) == false) {
                    $validator->errors()->add('salary', 'The file must be a file of type: xls, xlsx, xlt, xltm, xltx, xlsm');
                }
            });
        }
        if ($validator->fails()) {
            return redirect('/admin/payroll/user/import/salary')
                        ->withErrors($validator)
                        ->withInput();
        }
        DB::beginTransaction();
        try{
            if($request->file('salary')) {
                $index = array('EMP ID' => 1, 'MONTH' => 2, 'ACTUAL SALARY' => 3,'ADVANCE AMOUNT'=>4);
                $path = $request->file('salary');      
                $data = Excel::toArray(new Import(),$path);
                $error = '';
                $emp_error = '';
                $i = 0;
                $fl = 0;
                $char = 0;
               
               $month_arr = array('january','february','march','april','may','june','july','august','september','october','november','december');

                if(count($data[0]) > 0 && $data) {
                    if(count($data[0]) >= 2) {
                    $i = 1;
                    $out= $this->validate_excel_format($i,$data[0][$i-1],$index);

                     if($out['column_name_err'] == 0) {
                        foreach($data[0] as $key => $value) {
                                $sr_no = $value[0];
                                $emp_id = $value[$index['EMP ID']];
                                $month =$value[$index['MONTH']];
                                $amount = $value[$index['ACTUAL SALARY']];
                                $advance = $value[$index['ADVANCE AMOUNT']];
                              
                                if ($key > 0){
                                    $year = date('Y');
                                    $month=strtolower($month);
                                
                                if(!in_array($month, $month_arr)){
                                    DB::rollback();
                                    return back()->with('error','Error, Check your file, month name is Wrong.')->withInput();
                                }
                                    $salary_month = date('m',strtotime($month));

                                    if(empty($emp_id)) {
                                        $emp_error = $emp_error.'Error, Check your file should not be empty.'.$emp_id.'';
                                    }if(empty($advance)) {
                                          $advance=0;
                                    }
                                    $GetEmp = Users::where('emp_id',$emp_id)->get()->first();
                                      if ($GetEmp == null) {
                                            DB::rollback();
                                            return back()->with('error','Error, Employee number could not be find .'.$emp_id.'')->withInput();
                                        }
                                   
                                $s_date = $year.'-'.$salary_month.'-'.date('d');
                               
                                $findEmp = PayrollSalary::where('user_id',$GetEmp->id)->whereYear('payroll_salary.salary_month',$year)->whereMonth('payroll_salary.salary_month',$salary_month)->first();
                                
                                   if(isset($findEmp)) {
                                            $emp_error = $emp_error.'But some employees salary is already made, Employee Number '.$emp_id.'';
                                    } else{

                                    $arrears_amount=0;$bonus_amount=0;$cr_adjustment=0;$dr_adjustment=0;$dr_penalty=0;$advance_amt=0;$ot_amt=0;$dr_leave=0;$present=0;$absent=0;$wo=0;$present_amt=0;$absent_amt=0;$total_absent_amt=0;$wo_amt=0;$netSalary=0;$approvel_leave=0;$total_present_amt=0;$total_present=0;
                                    $total_deduction=0;$late_early_going_count=0;$late_early_going_amount=0;
                                    $dr_penalty_count=0;

                                    $all_calculation=CustomHelpers::salaryCalculation($GetEmp->id,$month,$advance);

                                    if(!empty($all_calculation)){
                                          foreach($all_calculation as $key) {
                                                if(array_key_exists("success",$all_calculation)){
                                                  if($all_calculation['success']==1){
                                              
                                                    if(array_key_exists("adjustment_cr",$all_calculation)){
                                                       $cr_adjustment=$all_calculation['adjustment_cr']['amount'];
                                                    }if(array_key_exists("adjustment_dr",$all_calculation)){
                                                        $dr_adjustment=$all_calculation['adjustment_dr']['amount'];
                                                    }if(array_key_exists("arrears",$all_calculation)){
                                                        $arrears_amount=$all_calculation['arrears']['amount'];
                                                    }if(array_key_exists("bonus",$all_calculation)){
                                                        $bonus_amount=$all_calculation['bonus']['amount'];
                                                    }if(array_key_exists("penalty",$all_calculation)){
                                                        $dr_penalty=$all_calculation['penalty']['amount'];
                                                        $dr_penalty_count=$all_calculation['penalty']['count'];
                                                    }if(array_key_exists("late_early_going",$all_calculation)){
                                                        $late_early_going_count=$all_calculation['late_early_going']['count'];
                                                        $late_early_going_amount=$all_calculation['late_early_going']['amount'];
                                                    }if(array_key_exists("advance",$all_calculation)){
                                                        $advance_amt=$all_calculation['advance']['amount'];
                                                    }if(array_key_exists("overtime",$all_calculation)){
                                                        $ot_amt=$all_calculation['overtime']['amount'];
                                                    }if(array_key_exists("user_payroll",$all_calculation)){
                                                         $net_salarys=$all_calculation['user_payroll']['Net Salary'];
                                                         $basic_salary=$all_calculation['user_payroll']['Basic Salary'];
                                                         $hra=$all_calculation['user_payroll']['Hra'];
                                                         $ta=$all_calculation['user_payroll']['Ta'];
                                                         $perf_allowance=$all_calculation['user_payroll']['Perf Allowance'];
                                                         $others=$all_calculation['user_payroll']['Others'];
                                                         $tax_deduction=$all_calculation['user_payroll']['Tax Deduction'];
                                                         $pf_deduction=$all_calculation['user_payroll']['Pf Deduction'];
                                                         $pf=$all_calculation['user_payroll']['Pf'];
                                                         $other_deduction=$all_calculation['user_payroll']['Other Deduction'];
                                                    }if(array_key_exists("attendance",$all_calculation)){
                                                         $present=$all_calculation['attendance']['present'];
                                                         $absent=$all_calculation['attendance']['absent'];
                                                         $approvel_leave=$all_calculation['attendance']['approvel_leave'];
                                                         $wo=$all_calculation['attendance']['wo'];
                                                         $present_amt=$all_calculation['attendance']['present_amt'];
                                                         $total_present=$all_calculation['attendance']['total_present'];
                                                         $total_present_amt=$all_calculation['attendance']['total_present_amt'];
                                                         $absent_amt=$all_calculation['attendance']['absent_amt'];
                                                         $total_absent_amt=$all_calculation['attendance']['total_absent_amt'];
                                                         $wo_amt=$all_calculation['attendance']['wo_amt'];
                                                         $month_salary=$all_calculation['attendance']['salary'];
                                                         if($approvel_leave>$absent){
                                                            DB::rollback();
                                                             return back()->with('error','Error, Absent & Applied Leave is not same for this user Employee number. '.$emp_id.'')->withInput();
                                                            }

                                                    }if(array_key_exists("net_salary",$all_calculation)){
                                                        $netSalary=$all_calculation['net_salary']['total'];

                                                    }if(array_key_exists("deduction",$all_calculation)){
                                                        $total_deduction=$all_calculation['deduction']['total'];

                                                    }if(array_key_exists("user_attendance",$all_calculation)){
                                                         $user_attendance=$all_calculation['user_attendance']['total'];
                                                         if($user_attendance==0){
                                                            DB::rollback();
                                                             return back()->with('error','Error, This month attendance 0 for this user Employee number. '.$emp_id.'')->withInput();
                                                         }       
                                                    }
                                                }else{
                                                     $message=$all_calculation['message'];
                                                      DB::rollback();
                                                             return back()->with('error','Error, '.$message.' for this user Employee number. '.$emp_id.'')->withInput();
                                                }
                                             }
                                            }
                                    }
                                   
                                    $salarydata = array('Month Basic Salary' => $basic_salary,'Month Hra' => $hra,'Month Ta' => $ta,'Month Perf Allowance' => $perf_allowance, 'Month Others' => $others, 'Month Tax Deduction' => $tax_deduction, 'Month Pf Deduction' => $pf_deduction,'Month Other Deduction' => $other_deduction,'Month Net Salary' => $net_salarys,);                                    
                                    
                                    $insert = PayrollSalary::insertGetId([
                                        'user_id' =>$GetEmp->id,
                                        'actual_salary' => $amount,
                                        'salary_month' => $s_date,
                                        'total_deduction' => $total_deduction,
                                        'total_pf' => ($pf != '') ? $pf: 0,
                                        'net_salary' => $netSalary,
                                        'payment_date' => date('Y-m-d')
                                    ]);
                                    if($insert == NULL) {
                                         $emp_id = $emp_id.'Salary Will Not be Updated for this Employee '.$user_id['name'].$user_id['middle_name'].$user_id['last_name'].', Please Check Manually And Fix It.  ';
                                    }else{
                                        
                                    $count = count($salarydata);
                                        for ($i=0; $i < $count; $i++) { 
                                            $key = array_keys($salarydata);
                                                if ($key[$i] == 'Month Tax Deduction' || $key[$i] == 'Month Other Deduction' || $key[$i] == 'Month Pf Deduction') {
                                                    $type = 'DR';
                                                }else{
                                                    $type = 'CR';
                                                }
                                                if($salarydata[$key[$i]]>0){
                                                      $insers_salary = PayrollSalaryDetail::insertGetId([
                                                        'payroll_salary_id' => $insert,
                                                        'type' => $type,
                                                        'name' => $key[$i],
                                                        'amount' => ($salarydata[$key[$i]] != '') ? $salarydata[$key[$i]]: 0,
                                                      ]);
                                                   if ($insers_salary == NULL) {
                                                        $emp_id = $emp_id.'Salary Will Not be Updated for this Employee '.$user_id['name'].$user_id['middle_name'].$user_id['last_name'].', Please Check Manually And Fix It.  ';
                                                    } 
                                                }
                                            
                                        }
                                    if ($advance_amt > 0) {
                                       foreach($all_calculation['advance']['data'] as $advance_id => $amount) { 
                                            $advance_pay = PayrollPaidAdvance::insertGetId([
                                                    'advance_id' =>$advance_id,
                                                    'amount_paid' => $amount,
                                                    'paid_category' =>'byHand',
                                                    'created_by' =>Auth::id()
                                                ]);
                                        }
                                        $advance_amt_deduction = PayrollSalaryDetail::insertGetId([
                                                'payroll_salary_id' => $insert,
                                                'type' => 'DR',
                                                'name' => 'Advance Amount Deduction',
                                                'amount' => $advance_amt
                                           ]);
                                        if ($advance_amt_deduction == null) {
                                            $emp_id = $emp_id.'Salary Will Not be Updated for this Employee '.$user_id['name'].$user_id['middle_name'].$user_id['last_name'].', Please Check Manually And Fix It.  ';
                                        }
                                    }if($ot_amt > 0) {
                                        $overtime_amt = PayrollSalaryDetail::insertGetId([
                                                'payroll_salary_id' => $insert,
                                                'type' => 'CR',
                                                'name' => 'Overtime',
                                                'amount' => $ot_amt
                                           ]);
                                        if ($overtime_amt == null) {
                                            $emp_id = $emp_id.'Salary Will Not be Updated for this Employee '.$user_id['name'].$user_id['middle_name'].$user_id['last_name'].', Please Check Manually And Fix It.  ';
                                        }else{
                                             foreach($all_calculation['overtime']['data'] as $id => $amount) {
                                                 $update_overtime = OverTime::where('id',$id)->where('adjust_status',0)->where('status','Approved')->update([
                                                 'adjust_status' => '1'
                                                 ]);
                                                if ($update_overtime == null) {
                                                    $emp_id = $emp_id.'Salary Will Not be Updated for this Employee '.$user_id['name'].$user_id['middle_name'].$user_id['last_name'].', Please Check Manually And Fix It.  ';
                                                }

                                            }                                           
                                        }
                                    }if($bonus_amount > 0) {
                                            $bonus_amt = PayrollSalaryDetail::insertGetId([
                                                'payroll_salary_id' => $insert,
                                                'type' => 'CR',
                                                'name' => 'Bonus',
                                                'amount' => $bonus_amount
                                           ]);
                                        if ($bonus_amt == null) {
                                            $emp_id = $emp_id.'Salary Will Not be Updated for this Employee '.$user_id['name'].$user_id['middle_name'].$user_id['last_name'].', Please Check Manually And Fix It.  ';
                                        }else{

                                            foreach($all_calculation['bonus']['ids'] as $id => $date) { 
                                                $update_bonus_amt = PayrollBonus::where('id',$id)->where('status',0)->where('type','Bonus')->update([
                                                'status' => '1'
                                                ]);
                                              if ($update_bonus_amt == null) {
                                                  $emp_id = $emp_id.'Salary Will Not be Updated for this Employee '.$user_id['name'].$user_id['middle_name'].$user_id['last_name'].', Please Check Manually And Fix It.  ';
                                               }
                                            }                                           
                                            
                                        }
                                    }if($arrears_amount > 0) {
                                            $arrears_amt = PayrollSalaryDetail::insertGetId([
                                                'payroll_salary_id' => $insert,
                                                'type' => 'CR',
                                                'name' => 'Arrears',
                                                'amount' => $arrears_amount
                                            ]);
                                        if ($arrears_amt == null) {
                                            $emp_id = $emp_id.'Salary Will Not be Updated for this Employee '.$user_id['name'].$user_id['middle_name'].$user_id['last_name'].', Please Check Manually And Fix It.  ';
                                        }else{

                                            foreach($all_calculation['arrears']['ids'] as $id => $date) { 
                                                $update_arrears_amt = PayrollBonus::where('id',$id)->where('status',0)->where('type','Arrears')->update([
                                                'status' => '1'
                                                ]);
                                              if ($update_arrears_amt == null) {
                                                  $emp_id = $emp_id.'Salary Will Not be Updated for this Employee '.$user_id['name'].$user_id['middle_name'].$user_id['last_name'].', Please Check Manually And Fix It.  ';
                                               }
                                            }
                                        }
                                    }if($total_absent_amt > 0) {
                                            $leave_amt = PayrollSalaryDetail::insertGetId([
                                                'payroll_salary_id' => $insert,
                                                'type' => 'DR',
                                                'name' => 'Leave Deduction',
                                                'amount' => $total_absent_amt
                                            ]);
                                        if ($leave_amt == null) {
                                            $emp_id = $emp_id.'Salary Will Not be Updated for this Employee '.$user_id['name'].$user_id['middle_name'].$user_id['last_name'].', Please Check Manually And Fix It.  ';
                                        }else{

                                            if($dr_leave > 0) {
                                                
                                            foreach($all_calculation['leave']['data'] as $id => $date) { 
                                                $update_leave = HRLeaveDetails::where('id',$id)
                                                               ->where('status','Approved')
                                                               ->where('is_adjusted',0)->update([
                                                                    'is_adjusted' => '2'
                                                                ]);
                                                if ($update_leave == null) {
                                                    $emp_id = $emp_id.'Salary Will Not be Updated for this Employee '.$user_id['name'].$user_id['middle_name'].$user_id['last_name'].', Please Check Manually And Fix It.  ';
                                                }
                                            }
                                         }
                                        }
                                    }if($dr_penalty_count > 1 || $late_early_going_count > 0) {
                                        $late_early_going_amt='';
                                         $penalty_amt = PayrollSalaryDetail::insertGetId([
                                                'payroll_salary_id' => $insert,
                                                'type' => 'DR',
                                                'name' => 'Penalty Deduction',
                                                'amount' => $dr_penalty
                                           ]);

                                        if($late_early_going_count > 0){
                                           $late_early_going_amt = UserPenalty::insertGetId([  
                                                'user_id' =>$GetEmp->id,
                                                'penalty_id' =>0,
                                                'amount' =>$late_early_going_amount,
                                                'remark' => 'Late Early going',
                                                'status' => '1',
                                           ]);
                                            
                                        }                                 
                                     if ($late_early_going_amt!= null){
                                        $update_attendance = Attendance:: where([
                                                    ['attendance.emp_id',$emp_id],
                                                    ['attendance.approve','0'],
                                                    ['attendance.is_adjusted','0'],
                                                    ['attendance.early_going','!=','00:00:00'],
                                                    ])->whereIn('attendance.late_early_status',['5','6','5,6','6,5'])
                                                    ->update([
                                                            'is_adjusted' => '1'
                                                        ]);
                                        $update_attendance_late_by = Attendance:: where([
                                                    ['attendance.emp_id',$emp_id],
                                                    ['attendance.approve','0'],
                                                    ['attendance.is_adjusted','0'],
                                                    ['attendance.late_by','!=','00:00:00']
                                                    ])->whereIn('attendance.late_early_status',['5','6','5,6','6,5'])
                                                    ->update([
                                                            'is_adjusted' => '1'
                                                        ]);

                                     }if ($dr_penalty_count > 1) {
                                            foreach($all_calculation['penalty']['data'] as $id => $amount) { 
                                                $update_penalty = UserPenalty::where('id',$id)
                                                               ->where('status',0)->update([
                                                                    'status' => '1'
                                                                ]);
                                                if ($update_penalty == null) {
                                                    $emp_id = $emp_id.'Salary Will Not be Updated for this Employee '.$user_id['name'].$user_id['middle_name'].$user_id['last_name'].', Please Check Manually And Fix It.  ';
                                                }
                                            }
                                        }
                                    }

                                // if($late_early_going_count > 0) {
                                //         $late_early_going_amt = UserPenalty::insertGetId([
                                               
                                //                 'user_id' =>$GetEmp->id,
                                //                 'penalty_id' =>0,
                                //                 'amount' =>$late_early_going_amount,
                                //                 'remark' => 'Late Early going',
                                //                 'status' => '1',
                                //            ]);
                                //    if ($late_early_going_amt == null) {

                                //             $emp_id = $emp_id.'Salary Will Not be Updated for this Employee '.$user_id['name'].$user_id['middle_name'].$user_id['last_name'].', Please Check Manually And Fix It.  ';
                                //         }else{
                                             
                                //                 $update_attendance = Attendance:: where([
                                //                     ['attendance.emp_id',$emp_id],
                                //                     ['attendance.approve','0'],
                                //                     ['attendance.is_adjusted','0'],
                                //                     ])->whereIn('attendance.late_early_status',['5','6','5,6','6,5'])
                                //                    ->where('attendance.early_going','!=','00:00:00')
                                //                    ->orwhere('attendance.late_by','!=','00:00:00')
                                //                     ->update([
                                //                             'is_adjusted' => '1'
                                //                         ]);
                                               
                                //         }
                                //     }

                                    if($cr_adjustment>0){
                                        foreach($all_calculation['adjustment_cr']['data'] as $index => $value) { 
                                              $crSalary = PayrollSalaryDetail::insertGetId([
                                                        'payroll_salary_id' => $insert,
                                                        'type' =>'CR',
                                                        'name' =>$index, 
                                                        'amount' =>$value
                                                   ]);
                                                if ($crSalary == NULL) {
                                                        $emp_id = $emp_id.'Salary Will Not be Updated for this Employee '.$user_id['name'].$user_id['middle_name'].$user_id['last_name'].', Please Check Manually And Fix It.  ';
                                                }else {
                                                    foreach($all_calculation['adjustment_cr']['ids'] as $id => $value) {

                                                         $updateCrAdjusment = PayrollSalaryAdjustment::where('id',$id)->where('status','0')->where('type','CR')->update([
                                                            'status' => '1'
                                                         ]); 
                                                    }
                                                   
                                            } 
                                        }
                                     }if($dr_adjustment>0){
                                            foreach($all_calculation['adjustment_dr']['data'] as $index => $value) { 
                                                 $drSalary = PayrollSalaryDetail::insertGetId([
                                                        'payroll_salary_id' => $insert,
                                                        'type' =>'DR',
                                                        'name' =>$index, 
                                                        'amount' =>$value
                                                   ]);
                                                if ($drSalary == NULL) {
                                                    $emp_id = $emp_id.'Salary Will Not be Updated for this Employee '.$user_id['name'].$user_id['middle_name'].$user_id['last_name'].', Please Check Manually And Fix It.  ';
                                                }else {

                                                    foreach($all_calculation['adjustment_dr']['ids'] as $id => $value) {

                                                        $updateDrAdjusment = PayrollSalaryAdjustment::where('id',$id)->where('status','0')->where('type','DR')->update([
                                                            'status' => '1'
                                                        ]);
                                                    }
                                                } 
                                        }
                                     }
                                     if($total_present>0){                                   
                                         $presentSalary = PayrollSalaryDetail::insertGetId([
                                                'payroll_salary_id' => $insert,
                                                'type' =>'CR',
                                                'name' =>'Net Salary', 
                                                'amount' =>$total_present_amt
                                           ]);
                                        if ($presentSalary == NULL) {
                                            $emp_id = $emp_id.'Salary Will Not be Updated for this Employee '.$user_id['name'].$user_id['middle_name'].$user_id['last_name'].', Please Check Manually And Fix It.  ';
                                        }
                                    }
                                }
                            }
                       
                        }
                    
                    }
                    
                 } else{ 
                    $error = $error.$out['error'];  
                }
                } else{
                    DB::rollback();
                        return back()->with('error','Error, Check your file should not be empty.')->withInput();
                }
                } else{
                    DB::rollback();
                        return back()->with('error','Error, Check your file should not be empty.')->withInput();
                }
            } else{
                DB::rollback();
                return back()->with('error','Error, some error occurred Please Try again.')->withInput();
            }
        } catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return redirect('/admin/payroll/user/import/salary')->with('error','some error occurred'.$ex->getMessage())->withInput();
        }
        if(!empty($error)) {
            DB::rollback();
            return redirect('/admin/payroll/user/import/salary')->with('error',$error)->withInput();
        }
        
        DB::commit();
        if(empty($emp_error)) {  
            CustomHelpers::userActionLog($action='User Salary Import',$insert,0);
            return redirect('/admin/payroll/user/import/salary')->with('success','Salary added successfully');
        }else{
            CustomHelpers::userActionLog($action='User Salary Import',$insert,0);
            return redirect('/admin/payroll/user/import/salary')->with('success','Salary added successfully,'.$emp_error);
        }
          CustomHelpers::userActionLog($action='User Salary Import',$insert,0);
        return redirect('/admin/payroll/user/import/salary')->with('success','Salary added successfully, but some employee number could not be find, such as :-'.$emp_error);
    }

    public function validate_excel_format($row,$a,$column_name,$sheet="") {
        ini_set('max_execution_time', 18000);
        $index=0;
        $row = $row-2;
        $column_name_with_index = $column_name;
        $column_name = array_keys($column_name);
        $column_name_err=0;
        $char = '0';
        $side=0;
        $error="";
        $data_inserted=0;
        $header = array();
        if(count($a)>=count($column_name))
        {
            foreach($column_name_with_index as $key1 => $val1)
            {
                $heading = preg_replace("/\r|\n|\t|\s+/", " ", $a[$val1]);
                if($key1 == $heading)
                {
                    $header[$key1] = $val1;
                }
                else{
                    $column_name_err++;
                    $error=$error."Column Name not in provided format. Error At  ".$this->getNameFromNumber($val1)."".$row.".";
                }
                $index++;
            } 
            if(count($column_name) == count($header)){
                $column_name_err = 0;
                $error="";
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
    }
    function getNameFromNumber($num) {
        $numeric = $num % 26;
        $letter = chr(65 + $numeric);
        $num2 = intval($num / 26);
        if ($num2 > 0) {
            return $this->getNameFromNumber($num2 - 1) . $letter;
        } else {
            return $letter;
        }
    }

    public function salary_list() {

        $data=array('layout'=>'layouts.main');
        return view('admin.hr.salary_list', $data); 
    }

   public function salary_list_api(Request $request){
        
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
                   
        $api_data = PayrollSalary::leftJoin('users','payroll_salary.user_id','users.id')
                    ->leftJoin('store','users.store_id','store.id')           
            ->select(
                'payroll_salary.id',
                'payroll_salary.user_id',
                 DB::raw('concat(users.name," ",ifnull( users.middle_name," ")," ",ifnull( users.last_name," ")) as name'),
                'users.emp_id',
                 DB::raw('concat(store.name,"-",store.store_type) as store_name'),
                'users.phone',
                'payroll_salary.net_salary as salary',
                'payroll_salary.salary_month',
                'payroll_salary.actual_salary',
                'payroll_salary.type'

            );
            if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('users.name','like',"%".$serach_value."%")
                    ->orwhere('users.emp_id','like',"%".$serach_value."%")
                    ->orwhere('users.phone','like',"%".$serach_value."%")
                    ->orwhere('payroll_salary.net_salary','like',"%".$serach_value."%")
                    ->orwhere('payroll_salary.salary_month','like',"%".$serach_value."%")
                    ->orwhere('payroll_salary.actual_salary','like',"%".$serach_value."%")
                    ->orwhere('payroll_salary.type','like',"%".$serach_value."%")
                    ->orwhere('store.name','like',"%".$serach_value."%")
                    
                    ;
                });
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                'users.emp_id',
                'users.name',
                'users.phone',
                'store_name',
                'salary_month',
                'type',
                'payroll_salary.net_salary',
                'payroll_salary.actual_salary'

                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('payroll_salary.id','asc'); 

        $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

    public function calenderpage()
    {
        $events = [];
        $leaves = [];
        $data = HolidayFestival::select('start_date','end_date','name','id')->get();
        $leave=HR_Leave::leftJoin('users','users.id','hr__leave.user_id')->select('hr__leave.start_date','hr__leave.end_date','hr__leave.reason','hr__leave.id','users.name','hr__leave.leave_status')->get();
        if($data->count()) {
            foreach ($data as $key => $value) {
                $events[] = \Calendar::event(
                    $value->name,
                    true,
                    new \DateTime($value->start_date),
                    new \DateTime($value->end_date.' +1 day'),
                    $value->id,
                    // Add color and link on event
                    [
                        'color' => '#f05050',
                        'url' => '#',
                        'start_date'=>$value->start_date,
                        'end_date'=>$value->end_date,
                        'check'=>'holiday',
                    ]
                );
            }
        }
        if($leave->count()) {
            foreach ($leave as $key => $value) {
                $leaves[] = \Calendar::event(
                    $value->name,
                    true,
                    new \DateTime($value->start_date),
                    new \DateTime($value->end_date.' +1 day'),
                    $value->id,
                    // Add color and link on event
                    [
                        'color' => '#3c8dbc',
                        'url' => '#',
                        'reason'=>$value->reason,
                        'start_date'=>$value->start_date,
                        'end_date'=>$value->end_date,
                        'leave_status'=>$value->leave_status,
                        'check'=>'leave',
                    ]
                );
            }
        }
        $calendar = \Calendar::addEvents($events)
        ->setCallbacks([
            'eventClick' => 'function(event){
                showModal(event.title,event.id,event.check,event.start_date,event.end_date);
            }',
        ]);

        $calendar = \Calendar::addEvents($leaves)
        ->setCallbacks([
            'eventClick' => 'function(event){
                showModal(event.title,event.id,event.reason,event.start_date,event.end_date,event.leave_status,event.check);
            }',
        ]);

        return view('admin.hr.calenderpage',array('calendar'=>$calendar,'layout' => 'layouts.main'));
    }

      public function EmployeeLeave_list() {

        $users = Users::select('emp_id as id','name','middle_name','last_name')->get();
        $data = [
                    'users' => $users,
                    'layout'=>'layouts.main'
                ];
        return view('admin.hr.employee_leave_list', $data); 
    }

    public function leaveList_api(Request $request){
        $search = $request->input('search');
        $user_name = $request->input('user_name');
        $month = $request->input('month');
        $year = $request->input('year');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
        $user = HR_Leave::select('employee')->get()->toArray();
        $a_date = date('Y-m-d');
        $date = new DateTime($a_date);
        $date->modify('last day of this month');
        $last_day = $date->format('d'); 
       
        $sort_data_query = array();

         if ($month) {
            $month_year = date('Y-'.$month);
        }else if ($month && $year) {
            $month_year = date($year.'-'.$month);
        }else{
            $month_year = date('Y-m');
        }
        DB::enableQueryLog();
       for ($j = 1; $j <= $last_day ; $j++) {
        
            $mdate = '"'.$month_year.'-'.$j.'"';
            $query[$j] = "IFNULL((SELECT att.leave_status FROM hr__leave att WHERE att.user_id = hr__leave.user_id AND start_date <= ".$mdate." AND end_date >= ".$mdate."),'') as d".$j." ";
        }

        $query = join(",",$query);
        $date = Carbon::now();      
        if ($month) {
            $month_name = $date->format($month);
        }else{
            $month_name = date('m');  
        }
        $api_data = HR_Leave::leftJoin('users','hr__leave.user_id','users.emp_id')
            ->whereMonth('hr__leave.start_date', date($month_name))
            ->select(
                DB::raw('concat(users.name," ",ifnull( users.middle_name," ")," ",ifnull( users.last_name," ")) as name'),
                'hr__leave.id',
                'hr__leave.user_id as emp_id',
                'hr__leave.leave_status', 
                'hr__leave.start_date',
                'hr__leave.end_date',
                DB::raw('DATE_FORMAT(hr__leave.start_date,"%Y-%m") as date'),
                DB::raw($query)
            )->groupBy('hr__leave.user_id');

             if(!empty($serach_value)) {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('hr__leave.id','like',"%".$serach_value."%")
                    ->orwhere('hr__leave.leave_status','like',"%".$serach_value."%")
                    ->orwhere('hr__leave.leave_apply_date','like',"%".$serach_value."%")
                    ->orwhere('hr__leave.start_date','like',"%".$serach_value."%")
                    ->orwhere('hr__leave.end_date','like',"%".$serach_value."%")
                    ->orwhere('hr__leave.user_id','like',"%".$serach_value."%");
                });
            }
            if(isset($user_name)) {
               $api_data->where(function($query) use ($user_name){
                        $query->where('hr__leave.user_id',$user_name);
                    });               
            }
            if(isset($year) ) {
               $api_data->where(function($query) use ($year){
                        $query->whereYear('hr__leave.start_date', date($year));
                    });
            }
            if(isset($month) ) {
               $api_data->where(function($query) use ($month){
                        $query->whereMonth('hr__leave.start_date', date($month));
                    });
            }

            if(isset($request->input('order')[0]['column'])) {

                $data = [
                    'hr__leave.id',
                    'hr__leave.user_id',
                    'users.name',
                    'attendance.leave_status',
                    'attendance.leave_apply_date'
                ];

                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('hr__leave.id','desc');
                $count = count( $api_data->get()->toArray());
                $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
            $array['recordsTotal'] = $count;
            $array['recordsFiltered'] = $count;
            $array['data'] = $api_data; 
            return json_encode($array);
    }

    public function PayrollIncrement_list() {
        $data = array(
            'layout'=>'layouts.main'
        );
        return view('admin.hr.payroll_increment_list',$data); 
    }

    public function PayrollIncrementList_api(Request $request) {
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
                   
      $api_data= PayrollIncrement::leftJoin('users','payroll_increment.user_id','users.id')
            ->select(
                'payroll_increment.id',
                DB::raw('concat(users.name," ",ifnull( users.middle_name," ")," ",ifnull( users.last_name," ")) as name'),
                'payroll_increment.basic_salary',
                'payroll_increment.hra',
                'payroll_increment.ta',
                'payroll_increment.pf',
                'payroll_increment.perf_allowance',
                'payroll_increment.others',
                'payroll_increment.effective_month',
                'payroll_increment.comment'

            );
            if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('payroll_increment.basic_salary','like',"%".$serach_value."%")
                    ->orwhere('payroll_increment.hra','like',"%".$serach_value."%")
                    ->orwhere('name','like',"%".$serach_value."%")
                    ->orwhere('payroll_increment.ta','like',"%".$serach_value."%")
                    ->orwhere('payroll_increment.perf_allowance','like',"%".$serach_value."%")
                    ->orwhere('payroll_increment.others','like',"%".$serach_value."%")
                    ->orwhere('payroll_increment.pf','like',"%".$serach_value."%")
                    ->orwhere('payroll_increment.effective_month','like',"%".$serach_value."%")
                    ->orwhere('payroll_increment.comment','like',"%".$serach_value."%")
                    
                    ;
                });
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                'users.name',
                'payroll_increment.basic_salary',
                'payroll_increment.hra',
                'payroll_increment.ta',
                'payroll_increment.perf_allowance',
                'payroll_increment.others',
                'payroll_increment.pf',
                'payroll_increment.effective_month',
                'payroll_increment.comment',

                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('payroll_increment.id','desc'); 

        $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

    public function PayrollIncrement(){
        $userdata = Users::all();
           $data = array(
            'userdata'=>$userdata,
            'layout'=>'layouts.main'
        );
        return view('admin.hr.payroll_increment',$data); 
    }

    public function PayrollIncrement_DB(Request $request) {
         try {
            $this->validate($request,[
                'employee'=>'required',
                'salary'  =>  'required_without_all:hra,ta,perf_allowance,others',
                'hra'  =>  'required_without_all:salary,ta,perf_allowance,others',
                'ta'  =>  'required_without_all:hra,salary,perf_allowance,others',
                'perf_allowance'  =>  'required_without_all:hra,ta,salary,others',
                'others'  =>  'required_without_all:hra,ta,perf_allowance,salary',
                'month_effective'=>'required',            

            ],[
                'employee.required'=> 'This field is required.',
                'hra.required_without_all'  =>  'This Field is required',
                'salary.required_without_all'  =>  'This Field is required',
                'ta.required_without_all'  =>  'This Field is required',
                'perf_allowance.required_without_all'  =>  'This Field is required',
                'others.required_without_all'  =>  'This Field is required',
                'month_effective.required'=> 'This field is required.',                
            ]);
            DB::beginTransaction();
            $user_id = $request->input('employee');
            $month_effective = $request->input('month_effective');
            $pf = $request->input('pf');
            $current_date = date('m/d/Y');
            
            $ts1 = strtotime($current_date);
            $ts2 = strtotime($month_effective);

            $year1 = date('Y', $ts1);
            $year2 = date('Y', $ts2);

            $month1 = date('m', $ts1);
            $month2 = date('m', $ts2);

            $diff = (($year2 - $year1) * 12) + ($month2 - $month1);
  
            $checkdata = Payroll::where('user_id','=',$user_id)->get()->first();

            if(!$checkdata){
                 return redirect('/admin/payroll/increment/')->with('error','Basic salary is not set for this user');
            }else {

            $salary = $request->input('salary');
            $hra = $request->input('hra');
            $ta = $request->input('ta');
            $perf_allowance = $request->input('perf_allowance');
            $others = $request->input('others');
            //when previous month 
            if ($request->input('month_effective') && ($diff <0)) {
                $getMonth = CustomHelpers::UpdateAllMonthArrears($month_effective,$user_id,$salary,$hra,$ta,$perf_allowance,$others,$pf);
            }
            $net_salary=$request->input('salary')+$request->input('hra')+$request->input('ta')+$request->input('perf_allowance')+$request->input('others');

            if ($checkdata && $diff <=0) { //when previous month or eqal
                $update = Payroll::where('user_id', $user_id)->update([
                    'basic_salary' => $checkdata['basic_salary']+$request->input('salary'),
                    'hra' => $checkdata['hra']+$request->input('hra'),
                    'ta' => $checkdata['ta']+$request->input('ta'),
                    'perf_allowance' => $checkdata['perf_allowance']+$request->input('perf_allowance'),
                    'others' => $checkdata['others']+$request->input('others'),
                    'pf' => $checkdata['pf']+$request->input('pf'),
                    'net_salary' => $checkdata['net_salary']+$net_salary,
                ]);
                if ($update == null) {
                    DB::rollback();
                    return redirect('/admin/payroll/increment')->with('error','Some Unexpected Error occurred.'); 
                }
            }      
            if ($diff >0) { //when next month
                 $data = [
                'basic_salary' => ($request->input('salary') != '') ? $request->input('salary'): 0,
                'user_id' => $user_id,
                'hra' => ($request->input('hra') != '') ? $request->input('hra'): 0,
                'ta' => ($request->input('ta') != '') ? $request->input('ta'): 0,
                'perf_allowance' => ($request->input('perf_allowance') != '') ? $request->input('perf_allowance'): 0,
                'others' => ($request->input('others') != '') ? $request->input('others'): 0,
                'pf' => ($request->input('pf') != '') ? $request->input('pf'): 0,
                'effective_month' => date('Y-m-d',strtotime($month_effective)),
                'comment' => 'Arrears added for increment salary.',
                'added_insalary' => '0'
                ];
             } else{
                $data = [
                'basic_salary' => ($request->input('salary') != '') ? $request->input('salary'): 0,
                'user_id' => $user_id,
                'hra' => ($request->input('hra') != '') ? $request->input('hra'): 0,
                'ta' => ($request->input('ta') != '') ? $request->input('ta'): 0,
                'perf_allowance' => ($request->input('perf_allowance') != '') ? $request->input('perf_allowance'): 0,
                'others' => ($request->input('others') != '') ? $request->input('others'): 0,
                'pf' => ($request->input('pf') != '') ? $request->input('pf'): 0,
                'effective_month' => date('Y-m-d',strtotime($month_effective)),
                'comment' => 'Arrears added for increment salary.',
                'added_insalary' => '1'
                ];
             }
            $customerdata = PayrollIncrement::insertGetId($data);
                if($customerdata == NULL){
                    DB::rollback();
                    return redirect('/admin/payroll/increment')->with('error','Some Unexpected Error occurred.'); 
                }else{
                    DB::commit();
                    CustomHelpers::userActionLog($action='Payroll increment Add',$customerdata,0);
                    return redirect('/admin/payroll/increment')->with("success","Payroll increment added successfully.");  
                }
          }
                
        }  catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return redirect('/admin/payroll/increment')->with('error','some error occurred'.$ex->getMessage())->withInput();
        }
    }

    public function salary_view($id) {
    
         $payroll_salary = PayrollSalary::leftJoin('users','payroll_salary.user_id','users.id')
                    ->leftJoin('store','users.store_id','store.id')  
                    ->where('payroll_salary.id',$id)         
                    ->select(
                        'payroll_salary.id',
                         DB::raw('concat(users.name," ",ifnull( users.middle_name," ")," ",ifnull( users.last_name," ")) as name'),
                        'users.emp_id',
                         DB::raw('concat(store.name,"-",store.store_type) as store_name'),
                        'users.phone',
                        'payroll_salary.*'

                    )->get()->first();

        if(!$payroll_salary){
            return redirect('/admin/payroll/user/salary/list')->with('error','Id not exist.');
        }
       else{

         $payroll_id=$payroll_salary->id;
         $payroll_salary_details = PayrollSalaryDetail::where('payroll_salary_id',$payroll_id)->get();

           $data=array(
            'payroll_salary'=>$payroll_salary,
            'payroll_salary_details'=>$payroll_salary_details,
            'layout' => 'layouts.main'
           );
            return view('admin.hr.salary_view',$data);
      }        
    }

    public function salaryadvance_approve($id,Request $request){

        $check=PayrollSalaryAdvance::where('id',$id)->get()->first();
        if(!$check){
            
            return redirect('/admin/payroll/salaryadvance/list')->with('error','ID not exist');
        }
        else{                        
            
            if($check->status=='Rejected'){

                return redirect('/admin/payroll/salaryadvance/list')->with('error','ID already Rejected');
            }else if($check->status=='Approved'){

                return redirect('/admin/payroll/salaryadvance/list')->with('error','ID already Approved');
                
            }else if($check->status=='Pending'){

                if($request->input('amount')>$check->amount_requested){
                    return redirect('/admin/payroll/salaryadvance/list')->with('error','Approve Amount Should not be greater than  Requested Amount');
                }else{
                    if($request->input('status')){ 
                        $arr=[
                        'status'=>$request->input('status'),
                        'approve_rejected_by'=>$request->input('auth_id'),
                        'status'=>$request->input('status'),
                        'remark'=>$request->input('remark'),
                        'amount'=>$request->input('amount')
                        ];
                
                       $msg='Successfully Salary Advance '.$request->input('status');
                     }

                     $data=PayrollSalaryAdvance::where('id',$id)->update($arr);
                     if($data){
                           CustomHelpers::userActionLog($action='Salary Advance Approve',$id,0);
                           return redirect('/admin/payroll/salaryadvance/list')->with('success',$msg); 
                       }else{
                           DB::rollback();
                           return back()->with('error','Some Error Occurred.')->withInput();
                     } 
                }
            }else{
                DB::rollback();
                return back()->with('error','Some Error Occurred.')->withInput();
             } 
             
         }
           
    }
     public function salaryadvance_pending(){
        
        $data = array('layout'=>'layouts.main');
        return view('admin.hr.salaryadvance_list_pending', $data);
    }
    public function salaryadvance_completed(){
        
        $data = array('layout'=>'layouts.main');
        return view('admin.hr.salaryadvance_list_completed', $data);
    }

     public function salaryadvance_completed_list(Request $request){

        $search = $request->input('search');
        $store_name=$request->input('store_name');
        $user_name=$request->input('user_name');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
        
        $store = CustomHelpers::user_store();
                   
        $api_data= Users::Join('payroll_salary_advance','users.id','payroll_salary_advance.user_id')
                 ->leftJoin('users as level1','level1.id','payroll_salary_advance.approve_rejected_by')
                  ->whereIn('users.store_id',$store)
                  ->whereIn('payroll_salary_advance.status',array('Approved','Rejected'))
                  ->leftJoin('store','users.store_id','store.id')
                    ->select(
                    'payroll_salary_advance.id',
                    'users.name',
                    'users.middle_name',
                    'users.last_name',
                    'users.emp_id',
                    'payroll_salary_advance.loan_type',
                    'payroll_salary_advance.amount_requested',
                    'payroll_salary_advance.installment',
                    'payroll_salary_advance.loan_purpose',
                    'payroll_salary_advance.document',
                    'payroll_salary_advance.amount',
                    'payroll_salary_advance.user_id',
                    'payroll_salary_advance.status',
                    'level1.name as level1'
                    );
            if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('users.name','like',"%".$serach_value."%")
                    ->orwhere('payroll_salary_advance.loan_type','like',"%".$serach_value."%")
                    ->orwhere('payroll_salary_advance.amount_requested','like',"%".$serach_value."%")
                    ->orwhere('payroll_salary_advance.installment','like',"%".$serach_value."%")
                    ->orwhere('payroll_salary_advance.loan_purpose','like',"%".$serach_value."%")
                    ->orwhere('payroll_salary_advance.document','like',"%".$serach_value."%")
                    ->orwhere('payroll_salary_advance.amount','like',"%".$serach_value."%")
                    ->orwhere('payroll_salary_advance.status','like',"%".$serach_value."%")
                    ->orwhere('level1.name','like',"%".$serach_value."%")
                    ->orwhere('users.emp_id','like',"%".$serach_value."%")
                    
                    ;
                });
            }
            if(!empty($store_name))
            {
                $api_data->where(function($query) use ($store_name){
                        $query->where('users.store_id','like',"%".$store_name."%");
                    });
               
            } if(isset($user_name) )
            {
               $api_data->where(function($query) use ($user_name){
                        $query->where('users.emp_id',$user_name);
                    });               
            }
            
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                'payroll_salary_advance.id',
                'users.emp_id',
                'users.name',
                'payroll_salary_advance.loan_type',
                'payroll_salary_advance.amount_requested',
                'payroll_salary_advance.amount',
                'payroll_salary_advance.installment',
                'payroll_salary_advance.loan_purpose',
                'payroll_salary_advance.document',
                'payroll_salary_advance.status',
                'level1'
                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('payroll_salary_advance.id','desc'); 
                $count = count( $api_data->get()->toArray());
                $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
                $array['recordsTotal'] = $count;
                $array['recordsFiltered'] = $count;
                $array['data'] = $api_data; 
                return json_encode($array);
    }
}