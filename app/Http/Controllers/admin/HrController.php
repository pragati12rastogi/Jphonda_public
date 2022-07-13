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
use \App\Model\NOP;
use \App\Model\PenaltyMaster;
use \App\Model\UserPenalty;
use \App\Model\ShiftTiming;
use\App\Model\UserDetails;
use\App\Model\Payroll;
use\App\Model\PayrollSalary;
use\App\Model\SalaryAdvance;
use\App\Model\PayrollSalaryDetail;
use\App\Model\IdCardIssue;
use\App\Model\PayrollSalaryAdjustment;
use\App\Model\PayrollPaidAdvance;
use\App\Model\PayrollSalaryAdvance;
use\App\Model\Attendance;
use\App\Model\OfficeDuty;
use\App\Model\HolidayFestival;
use\App\Model\PayrollBonus;
use\App\Model\PayrollIncrement;
use\App\Model\Resignation;
use\App\Model\OverTime;
use\App\Model\LeaveBalance;
use\App\Model\HRLeaveDetails;
use \App\Model\Rejoin;
use \App\Model\FuelStockModel;
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


class HrController extends Controller {
    public function __construct() {
    }
  
    public function createuser()
    {
        $countries= DB::table("countries")->get();
        $store = Store::whereIn('id',CustomHelpers::user_store())
            ->select('id',
                DB::Raw('concat(name,"-",store_type) as name')
            )
            ->orderBy("store_type","ASC")->get();
        $data = [
            'countries'=>$countries,
            'store'=>$store,
            'layout' => 'layouts.main'
        ];
        
        return view('admin.hr.createuser',$data);
    }

    public function createuser_DB(Request $request){
        
         try {
            $timestamp=date('Y-m-d G:i:s');
            $this->validate($request,[
                'first_name'=>'required',
                'emp_id'=>'required|unique:users,emp_id',
                'dob'=>'required',
                'relation_type'=>'required',
                'relation'=>'required',
                'gender'=>'required',
                'phone'=>'required|digits:10|unique:users,phone',
                'address'=>'required',
                'state'=>'required',
                'city'=>'required',
                'pin_code'=>'required',
                'aadhar'=>'required',
                'pancard'=>'required',
                'store'=>'required'

            ],[
                'first_name.required'=> 'This is required.',
                'emp_id.required'=> 'This is required.',
                'emp_id.unique'=> 'Employee Id already taken.',
                'dob.required'=> 'This is required.',
                'gender.required'=> 'This is required.',
                'relation_type.required'=> 'This Field is required.',
                'relation.required'=> 'This Field is required.',
                'phone.required'=> 'Phone Number is required.',
                'phone.digits'=> 'phone Number contains digits only.',
                'phone.unique'=> 'phone Number already taken.',
                'address.required'=> 'This Field is required.',
                'state.required'=> 'This is required.',
                'city.required'=> 'This is required.',
                'pin_code.required'=> 'This is required.',
                'aadhar.required'=> 'This is required.',
                'pancard.required'=> 'This is required.',
                'store.required'=> 'This field is required.' 
            ]);
            $dob=Carbon::createFromFormat('d/m/Y', $request->input('dob'))->format('Y-m-d');
            $doj=Carbon::createFromFormat('d/m/Y', $request->input('doj'))->format('Y-m-d');

            $email = $request->input('email');
            $email = $email!=""?$email:'';
            $store = $request->input('store');
            $customer = Users::insertGetId(
                [ 
                    'name'=>$request->input('first_name'),
                    'middle_name'=>$request->input('middle_name'),
                    'last_name'=>$request->input('last_name'),
                    'email'=>$email,
                    'store_id'=>$store,
                    'phone'=>$request->input('phone'),
                    'user_type'=>'user',
                    'dob'=>$dob,
                    'emp_id'=>$request->input('emp_id'),
                    'gender'=>$request->input('gender'),
                    'relation_type'=>$request->input('relation_type'),
                    'relation_name'=>$request->input('relation'),
                    'alias_name'=>$request->input('alias_aadhar'),
                    'jpm_reference'=>$request->input('reference_jpm'),
                    'address'=>$request->input('address'),
                    'state'=>$request->input('state'),
                    'city'=>$request->input('city'),
                    'pincode'=>$request->input('pin_code'),
                    'aadhar'=>$request->input('aadhar'),
                    'pancard'=>$request->input('pancard'),
                    'doj'=>$doj,
                    'created_at'=>$timestamp,
                ]
            );

            $pfesi=UserDetails::insertGetId([
                'id'=>NULL,
                'user_id'=>$customer
            ]);

             $salary=Payroll::insertGetId([
                'id'=>NULL,
                'user_id'=>$customer
            ]);            

            if($pfesi==NULL){
                DB::rollback();
                return redirect('/admin/hr/create/user/')->with('error','Some Unexpected Error occurred.');  
            }

            if($customer==NULL) 
            {
                DB::rollback();
                return redirect('/admin/hr/create/user/')->with('error','Some Unexpected Error occurred.');
            }
            else{
                 CustomHelpers::userActionLog($action='User Add',$customer,0);
                return redirect('/admin/hr/create/user/')->with('success','Successfully Created User.'); 
             }    
        
        }  catch(\Illuminate\Database\QueryException $ex) {
            return redirect('/admin/hr/create/user/')->with('error','some error occurred'.$ex->getMessage())->withInput();
        }
    }

     public function user_list() {
        $store = Store::whereIn('id',CustomHelpers::user_store())
                    ->select('id',DB::raw('concat(store.name,"-",store.store_type) as name'))->get();
        $data = array('store'=>$store,'layout'=>'layouts.main');
        return view('admin.hr.user_list', $data); 
    }

    public function user_data(Request $request){
        $search = $request->input('search');
        $store_name = $request->input('store_name');
        $status = $request->input('status');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 100 : $limit ;

        $store = CustomHelpers::user_store();
        $api_data = Users::leftJoin('user_details','users.id','user_details.user_id')
                        ->leftJoin('states','users.state','states.id')
                        ->leftJoin('cities','users.city','cities.id')
                        ->leftJoin('id_card_issue','users.id','id_card_issue.user_id')
                        ->where('users.status',1)
                        ->select(
                            'users.id',
                            DB::raw('concat(users.name," ",ifnull( users.middle_name," ")," ",ifnull( users.last_name," ")) as name'),
                            'users.emp_id',
                            DB::raw("DATE_FORMAT(users.dob, '%d-%m-%Y') as dob"),
                            'users.relation_type',
                            'users.relation_name',
                            'states.name as sname',
                            'cities.city as city_name',
                            'users.alias_name',
                            'users.aadhar',
                            'users.pancard',
                            'users.address',
                            'users.state',
                            'users.city',
                            'users.status',
                            'id_card_issue.id as icard_issue',
                            DB::raw("DATE_FORMAT(users.doj, '%d-%m-%Y') as doj"),
                            'user_details.user_id'
                        );
            if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('users.name','like',"%".$serach_value."%")
                    ->orwhere('users.relation_type','like',"%".$serach_value."%")
                    ->orwhere('users.relation_name','like',"%".$serach_value."%")
                    ->orwhere('users.aadhar','like',"%".$serach_value."%")
                    ->orwhere('users.pancard','like',"%".$serach_value."%")
                    ->orwhere('users.alias_name','like',"%".$serach_value."%")
                    ->orwhere('states.name','like',"%".$serach_value."%")
                    ->orwhere('cities.city','like',"%".$serach_value."%")
                    ->orwhere('users.doj','like',"%".$serach_value."%")
                    ->orwhere('users.status','like',"%".$serach_value."%")
                    ->orwhere('users.emp_id','like',"%".$serach_value."%");
                });
            }

            if(!empty($status)){   
            
                $api_data->where(function($query) use ($status){
                        $query->whereIn('users.status',$status);
                    });
            }
            if(!empty($store_name))
            {
               $api_data->where(function($query) use ($store_name){
                        $query->where('users.store_id','like',"%".$store_name."%");
                    });
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                'users.emp_id',
                'users.name',
                'users.dob',
                'users.relation_type',
                'users.relation_name',
                'sname',
                'city_name',
                'users.status',
                'users.alias_name',
                'users.aadhar',
                'users.pancard',
                'users.doj'
                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else            
                $api_data->orderBy('users.emp_id','asc');

        $count = count($api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

    public function user_update($id) {
    
        $customerdata = Users::leftJoin('user_details','users.id','=','user_details.user_id')->where('users.id',$id)->leftJoin('cities','users.city','=','cities.id') ->leftJoin('states','users.state','=','states.id')->get(['users.*','states.name as sname','cities.city as city_name'])->first();

        $state_id = $customerdata['state'];
        $city = City::where('state_id',$state_id)->get();
        $state = State::all();
        $check_user = Users::where('id',$id)->get();
        if(!$check_user){
            return redirect('/admin/hr/user/list')->with('error','Id not exist.');
        }
        if($customerdata){
            $data = array(
                'customer'=>$customerdata,
                'city'=>$city,
                'state'=>$state,
                'id'=>$id,
                'layout'=>'layouts.main'
            );
            return view('admin.hr.user_update',$data);
       }
       else{
           $data=array(
            'id'=>$id,
            'city'=>$city,
            'state'=>$state,
            'layout' => 'layouts.main'
           );
            return view('admin.hr.user_update',$data);
      }        
    }

    public function user_update_Db(Request $request,$id){

         try {
           $timestamp=date('Y-m-d G:i:s');
            $this->validate($request,[
                'first_name'=>'required',
                'emp_id'=>'required|unique:users,emp_id,'.$id,
                'dob'=>'required',
                'doj'=>'required',
                'relation'=>'required',
                'gender'=>'required',
                'phone'=>'required|unique:users,phone,'.$id,
                'address'=>'required',
                'state'=>'required',
                'city'=>'required',
                'pin_code'=>'required',
                'aadhar'=>'required',
                'pancard'=>'required',

            ],[
                'first_name.required'=> 'This is required.',
                'emp_id.required'=> 'This is required.',
                'emp_id.unique'=> 'Employee Id already taken.',
                'dob.required'=> 'This is required.',
                'doj.required'=> 'This is required.',
                'relation.required'=> 'This Field is required.',
                'address.required'=> 'This Field is required.',
                'state.required'=> 'This is required.',
                'city.required'=> 'This is required.',
                'pin_code.required'=> 'This is required.',
                'aadhar.required'=> 'This is required.',
                'pancard.required'=> 'This is required.'   
            ]);

            $dob=Carbon::createFromFormat('d/m/Y', $request->input('dob'))->format('Y-m-d');
            $doj=Carbon::createFromFormat('d/m/Y', $request->input('doj'))->format('Y-m-d');
            $user_details_id=$request->input('user_details_id');

             $check_user = Users::where('id',$user_details_id)->get()->first();
             if(!$check_user){
                    return redirect('/admin/hr/user/list')->with('error','Id not exist.');
             }else{

                if($check_user['status']==2){
                    $status=2;
                }else{
                    $status=$request->input('status');
                }
             }

                $customer=Users::where('id','=',$id)->update([
                    'name'=>$request->input('first_name'),
                    'middle_name'=>$request->input('middle_name'),
                    'last_name'=>$request->input('last_name'),
                    'emp_id'=>$request->input('emp_id'),
                    'phone'=>$request->input('phone'),
                    'email'=>$request->input('email'),
                    'gender'=>$request->input('gender'),
                    'dob'=>$dob,
                    'relation_type'=>$request->input('relation_type'),
                    'relation_name'=>$request->input('relation'),
                    'alias_name'=>$request->input('alias_aadhar'),
                    'jpm_reference'=>$request->input('reference_jpm'),
                    'address'=>$request->input('address'),
                    'state'=>$request->input('state'),
                    'city'=>$request->input('city'),
                    'pincode'=>$request->input('pin_code'),
                    'aadhar'=>$request->input('aadhar'),
                    'pancard'=>$request->input('pancard'),
                    'status'=>$status,
                    'doj'=>$doj
            ]);
       if($customer==NULL) 
            {
                DB::rollback();
                return redirect('/admin/hr/user/update/'.$user_details_id)->with('error','Some Unexpected Error occurred.');
            }
            else{
                CustomHelpers::userActionLog($action='User Details Update',$customer,0);
                return redirect('/admin/hr/user/update/'.$user_details_id)->with('success','Successfully Updated User Details.'); 
             }    
        
        }  catch(\Illuminate\Database\QueryException $ex) {
            return redirect('/admin/hr/user/update/'.$user_details_id)->with('error','some error occurred'.$ex->getMessage())->withInput();
        }
    }

    public function documentupdate($id) {
        $customerdata = Users::leftJoin('user_details','users.id','=','user_details.user_id')->where('users.id',$id)->get(['users.*','user_details.id as user_details_id','user_details.pf','user_details.esi','user_details.bank_name','user_details.account_number','user_details.ifsc','user_details.user_id'])->first();
        $check_user=Users::where('id',$id)->get();
        
        if(!$check_user){
            return redirect('/admin/hr/user/list')->with('error','Id not exist.');
        }
        if($customerdata){
            $data = array(
                'customer'=>$customerdata,
                'id'=>$id,
                'layout'=>'layouts.main'
            );
             return view('admin.hr.document_details_update',$data); 
        }
        else{
            $data=array(
            'id'=>$id,
            'layout' => 'layouts.main'
            );
            return view('admin.hr.document_details_update',$data); 
        }        
       
    }

    public function documentupdate_DB(Request $request,$id){
        $user_details_id=$request->input('user_details_id');
         try {
           $timestamp=date('Y-m-d G:i:s');
            $this->validate($request,[
                'bank_name'=>'required',
                'account_number'=>'required|unique:user_details,account_number,'.$user_details_id,
                'ifsc'=>'required|unique:user_details,ifsc,'.$user_details_id,
                'pf'=>'nullable|unique:user_details,pf,'.$user_details_id,
                'esi'=>'nullable|unique:user_details,esi,'.$user_details_id,

            ],[
                'bank_name.required'=> 'This is required.',
                'account_number.required'=> 'This is required.',
                'ifsc.required'=> 'This is required.'
                 
            ]);
        $check_user=UserDetails::where('user_id',$id)->get()->first();
        if($check_user){
            $customer=UserDetails::where('user_id','=',$id)->update([
                    'bank_name'=>$request->input('bank_name'),
                    'account_number'=>$request->input('account_number'),
                    'ifsc'=>$request->input('ifsc'),
                    'pf'=>$request->input('pf'),
                    'esi'=>$request->input('esi')
            ]);
        if($customer==NULL){
                DB::rollback();
                return redirect('/admin/hr/user/documentupdate/'.$id)->with('error','Some Unexpected Error occurred.');
            }
             CustomHelpers::userActionLog($action='User Bank Details Update',$customer,0);
            return redirect('/admin/hr/user/documentupdate/'.$id)->with('success','Successfully Updated Bank/PF Details.'); 
           
        } 
        else{
            $customerdata=UserDetails::insertGetId([
                'bank_name'=>$request->input('bank_name'),
                'user_id'=>$id,
                'account_number'=>$request->input('account_number'),
                'ifsc'=>$request->input('ifsc'),
                'pf'=>$request->input('pf'),
                'esi'=>$request->input('esi')
                
            ]);
            if($customerdata==NULL){
                DB::rollback();
                return redirect('/admin/hr/user/documentupdate/'.$id)->with('error','Some Unexpected Error occurred.'); 
            }
            CustomHelpers::userActionLog($action='User Bank Details Add',$customerdata,0);
            return redirect('/admin/hr/user/documentupdate/'.$id)->with("success","Successfully inserted Bank/PF Details.");
         }          
        }catch(\Illuminate\Database\QueryException $ex) {
            return redirect('/admin/hr/user/documentupdate/'.$id)->with('error','some error occurred'.$ex->getMessage())->withInput();
        }
    }

    public function otherdocumentupdate($id) {
      
          $store = Store::whereIn('id',CustomHelpers::user_store())
                      ->get();
                    
          $role = Master::where('type','user_role')->get();

          $customerdata = Users::leftJoin('user_details','users.id','=','user_details.user_id')
                ->leftJoin('shift_timing','shift_timing.id','=','user_details.shift_timing_id')
                ->where('users.id',$id)
                ->get(['users.*','user_details.id as user_details_id','user_details.biometric','user_details.shifting_timing','user_details.hirise','user_details.reporting_manager','user_details.user_id','user_details.shift_timing_id'])->first();
        $users = Users::get();

        $shift_timing =  ShiftTiming::select('id',DB::Raw('concat(shift_timing.shift_name," - ",shift_timing.shift_from," ",shift_to) as shift_timing'))->get();

         if(!$customerdata->toArray()){
           
            return redirect('/admin/hr/user/list/')->with('error','Id not exist');

         }else{
           $data = array(
            'role' => $role,
            'store'=>$store,
            'users'=>$users,
            'shift_timing'=>$shift_timing,
            'id'=>$id,
            'customer'=>$customerdata,
            'layout'=>'layouts.main'
        );
        return view('admin.hr.other_details_update',$data); 
         }
       
    }

    public function filter_user_api(Request $request){

        $assets = Users::whereIn('store_id', $request->input('assets'))->pluck('name','id');

     return response()->json($assets);
    }

    public function otherdocumentupdate_DB(Request $request,$id){

        try 
        {
           $timestamp = date('Y-m-d G:i:s');
            $this->validate($request,[
                'store_name'=>'required',
                'hirise'=>'required',
                'biometric'=>'required',
                 'reporting_manager'=>'required',
                'role'=>'required',

            ],[
                'store_name.required'=> 'This is required.',
                'hirise.required'=> 'This is required.',
                'biometric.required'=> 'This is required.',
                'role.required'=> 'This is required.'
                 
            ]);
                        $store_id = implode(',', $request->input('store_name'));
                        $time = $request->input('from').' to '.$request->input('to');
                        $asset_image_file ='';
                        $file = $request->file('upd_photo');
                        
                        if(!isset($file)||$file == null){
                            $asset_image_file = $request->input('old_image');
                        }else{
                            $destinationPath = public_path().'/upload/adminprofile';
                            $filenameWithExt = $request->file('upd_photo')->getClientOriginalName();
                            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                            $extension = $request->file('upd_photo')->getClientOriginalExtension();
                            $asset_image_file = $filename.'_'.time().'.'.$extension;
                            $path = $file->move($destinationPath, $asset_image_file);
                            File::delete($destinationPath.$request->input('old_image'));
                        }
            $check_user = UserDetails::where('user_id','=',$id)->get()->first();
            $shift_timing = $request->input('shift_timing');

            if($check_user){
                $customer = UserDetails::where('user_id','=',$id)->update([
                    'hirise' => $request->input('hirise'),
                    'shifting_timing' => $time,
                    'biometric' => $request->input('biometric'),
                    'reporting_manager' => $request->input('reporting_manager'),
                    'shift_timing_id' => $shift_timing
                    
                ]);
              
                if($customer == NULL) {
                    DB::rollback();
                    return redirect('/admin/hr/user/otherdocumentupdate/'.$id)->with('error','Some Unexpected Error occurred.');
                }else{
                      $users = Users::where('id',$request->input('user_id'))->update([
                            'store_id' => $store_id,
                            'role' => $request->input('role'),
                            'profile_photo' => $asset_image_file,
                            
                        ]);
                      if($users==NULL) {
                        DB::rollback();
                        return redirect('/admin/hr/user/otherdocumentupdate/'.$id)->with('error','Some Unexpected Error occurred.');
                    }else{
                        CustomHelpers::userActionLog($action='User Other Details Update',$users,0);
                        return redirect('/admin/hr/user/otherdocumentupdate/'.$id)->with('success','Successfully Updated Other Details.'); 
                    }
                }
    
            } 
            else{
                    $customerdata = UserDetails::insertGetId([
                        'hirise'=>$request->input('hirise'),
                        'user_id'=>$id,
                        'shifting_timing'=>$time,
                        'biometric'=>$request->input('biometric'),
                        'reporting_manager'=>$request->input('reporting_manager'),
                     ]);
                    
                if($customerdata==NULL){
                    DB::rollback();
                    return redirect('/admin/hr/user/otherdocumentupdate/'.$id)->with('error','Some Unexpected Error occurred.');
                }else{
                    $users = Users::where('id',$id)->update([
                        'store_id'=>$store_id,
                        'role'=>$request->input('role'),
                        'profile_photo'=>$asset_image_file,
                        
                    ]);
                    if($users==NULL){
                            DB::rollback();
                            return redirect('/admin/hr/user/otherdocumentupdate/'.$id)->with('error','Some Unexpected Error occurred.');
                        }else{
                            CustomHelpers::userActionLog($action='User Other Details Add',$users,0);
                             return redirect('/admin/hr/user/otherdocumentupdate/'.$id)->with('success','Successfully Insert Other Details.'); 
                            }

                }
            }    
        
        }  catch(\Illuminate\Database\QueryException $ex) {
            return redirect('/admin/hr/user/otherdocumentupdate/'.$id)->with('error','some error occurred'.$ex->getMessage())->withInput();
        }
    }

    public function user_printicard($id) {

        $customerdata = UserDetails::leftJoin('users','user_details.user_id','=','users.id')->where('user_details.user_id',$id)->leftJoin('cities','users.city','=','cities.id') ->leftJoin('states','users.state','=','states.id')->get(['users.*','states.name as sname','cities.city as city_name','user_details.id as user_details_id','user_details.user_id']);

        
        if(!$customerdata->toArray()){
           
            return redirect('/admin/hr/user/list/')->with('error','Id not exist');
            
         }else{
                $data = array(
                'customerdata'=>$customerdata,
                'layout'=>'layouts.main'
            );
                 $pdfFilePath = "asset form.pdf";
                 $pdf = PDF::loadView('admin.hr.user_printicard',$data);
                 return $pdf->stream($pdfFilePath);
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
                return redirect('/admin/hr/user/salaryadvance/')->with('success','Successfully Salary Advance Added.'); 
             }    
        
        }  catch(\Illuminate\Database\QueryException $ex) {
            return redirect('/admin/hr/user/salaryadvance/')->with('error','some error occurred'.$ex->getMessage())->withInput();
        }
    }

     public function create_leave(){

        $emp=Users::select('id','name','middle_name','last_name')->orderBy('emp_id')->get();
        $data=[
            'layout' => 'layouts.main',
            'emp'=>$emp,
        ];
        return view('admin.hr.leave_create',$data);
    }

    public function create_leaveDb(Request $request){
        try {
            $validerrarr =[
                'employee_name'=>'required',
                'contact'=>'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10|max:10',
                'start_date'=>'required',
                'end_date'=>'required', 
                'reason'=>'required',
                'type'=>'required',
            ];
            $validmsgarr =[
                'employee_name.required'=>'This field is required',
                'contact.required'=>'This field is required',
                'start_date.required'=>'This field is required',
                'end_date.required'=>'This field is required',
                'reason.required'=>'This field is required',
                'type.required'=>'This field is required',
                'contact.regex'=> 'First Mobile No contains digits only.',
                'contact.max'=> 'First Mobile No may not be greater than 10 digits.',
                'contact.min'=> 'First Mobile No must be at least 10 digits.',
            ];
            $this->validate($request,$validerrarr,$validmsgarr);
                
            if (!empty($request->input('contact'))) {
                  $validator = Validator::make($request->all(),[
                   'contact'=>'required|regex:/^([6-9][0-9]{9}[\s\-\+\(\)]*)$/|min:10|max:10',
                    ],
                    [
                            'contact.required'=> 'This field is required.',
                            'contact.regex'=> 'Mobile No contains digits only.',
                            'contact.max'=> 'Mobile No may not be greater than 10 digits.',
                            'contact.min'=> 'Mobile No must at least 10 digits.',
                    ]);

                    if ($validator->fails()) {
                        return back()->withErrors($validator)->withInput();
                    }
            }

            $start_date=strtotime($request->input('start_date'));
            $end_date=strtotime($request->input('end_date'));

             $diff = (($end_date - $start_date)/60/60/24)+1;
             $date=date_create(date("Y-m-d")); 
             $type=$request->input('type');  
             $user_id=$request->input('employee_name');

             $financial_year = CustomHelpers::getFinancialYear($date);
             $balancee_leave=LeaveBalance::where('user_id',$user_id)->where('type',$type)->where('financial_year',$financial_year)->get()->first();

             $check_applyleave=HR_Leave::leftjoin('hr__leave_details','hr__leave_details.leave_id','hr__leave.id')->where('hr__leave.user_id',$user_id)->where('hr__leave_details.date',date("Y-m-d",$start_date))->get()->first();
           
             if($check_applyleave){
                 return back()->with('error','you have already applied leave on this date.')->withInput();
             }
             
            if($type=='cl' || $type=='pl'){
                if($balancee_leave==null){
                     return back()->with('error',"leave balance has not been created, so can't apply ".$type." leave for this user .")->withInput();

                }else{
                      if(!($diff<= $balancee_leave['balance'])){
                         return back()->with('error','The number of days you have applied leave is less than the leave balance.')->withInput();
                      }
                   
                 }
             }

            $hr=HR_Leave::insertGetId([
                'id'=>NULL,
                'user_id'=>$user_id,
                'type'=>$type,
                'leave_apply_date'=>date('Y-m-d'),
                'email'=>$request->input('email'),
                'contact'=>$request->input('contact'),
                'start_date'=>date('Y-m-d',strtotime($request->input('start_date'))),
                'end_date'=>date('Y-m-d',strtotime($request->input('end_date'))),
                'reason'=>$request->input('reason'),
                'created_by'=>Auth::id(),

            ]);
            if($hr==NULL){
                return redirect('/admin/hr/leave/create')->with('error','some error occurred')->withInput();
            }
            else{
                
               $start= new DateTime($request->input('start_date'));
               $end  = new DateTime($request->input('end_date'));
               $intervals = new DateInterval('P1D');
               $end->add($intervals);

               $interval = DateInterval::createFromDateString('1 days');
               $period   = new DatePeriod($start, $interval, $end);
               foreach ($period as $dt) {
                
                 $date = $dt->format("Y-m-d");
                 $leave_deta=HRLeaveDetails::insertGetId([
                    'id'=>NULL,
                    'leave_id'=>$hr,
                    'date'=>date('Y-m-d',strtotime($date)),
                    'is_adjusted'=>0,
                    'status'=>'Pending',

                  ]);
                            
                 }

                CustomHelpers::userActionLog($action='Leave Apply',$hr,0);
                return redirect('/admin/hr/leave/create')->with('success','Successfully Leave Applied.');
            }
        } 
        catch(\Illuminate\Database\QueryException $ex) 
        {
            return redirect('/admin/hr/leave/create')->with('error','some error occurred'.$ex->getMessage())->withInput();
        }
    }

     //----------------leave list-------------------------------------
    public function leave_list(){
        $hr1=Settings::where('name','HR_Leave_Level1')->select('value','name')->get()->first();
        $hr=$hr1['value'];
        $data=[
            'layout' => 'layouts.main',
            'hr'=>$hr,
        ];
        return view('admin.hr.leave_summary',$data);
    }
    public function leave_list_api(Request $request){
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
        $jobdata = HR_Leave::leftJoin('users','users.id','hr__leave.user_id')
        ->leftJoin('users as level1','level1.id','hr__leave.status_level1_by')
        ->leftJoin('users as level2','level2.id','hr__leave.status_level2_by')
         ->leftJoin('user_details','user_details.user_id','hr__leave.user_id')
        ->select(
            'hr__leave.id',
            'users.name as emp',
            'users.name',
            'users.middle_name',
            'users.last_name',
            DB::raw('DATE_FORMAT(leave_apply_date,"%d-%m-%Y") as leave_apply_date'),
            'hr__leave.email',
            'contact',
            DB::raw('DATE_FORMAT(start_date,"%d-%m-%Y") as start_date'),
            DB::raw('DATE_FORMAT(end_date,"%d-%m-%Y") as end_date'),
            'hr__leave.reason',
            'status_level1',
            'status_level2',
            'leave_status',
            'level1.name as level1',
            'level2.name as level2',
            'user_details.reporting_manager as reporting'

        );
        if(!empty($serach_value))
        {
            $jobdata->where(function($query) use ($serach_value){
                $query->where('users.name','LIKE',"%".$serach_value."%")
                ->orWhere('leave_apply_date','LIKE',"%".$serach_value."%")
                ->orWhere('hr__leave.email','LIKE',"%".$serach_value."%")
                ->orWhere('contact','LIKE',"%".$serach_value."%")
                ->orWhere('start_date','LIKE',"%".$serach_value."%")
                ->orWhere('end_date','LIKE',"%".$serach_value."%")
                ->orWhere('hr__leave.reason','LIKE',"%".$serach_value."%")
                ->orWhere('level1.name','LIKE',"%".$serach_value."%")
                ->orWhere('level2.name','LIKE',"%".$serach_value."%")
                ;
              });
     
        }
        
        if(isset($request->input('order')[0]['column']))
        {
            $data = [
            'emp',
            'leave_apply_date',
            'hr__leave.email',
            'hr__leave.contact',
            'start_date',
            'end_date',
            'reason',
            'status_level1',
            'status_level2',
            'leave_status',
            'level1',
            'level2'
                ];
            $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
            $jobdata->orderBy($data[$request->input('order')[0]['column']], $by);
        }
        else
            $jobdata->orderBy('hr__leave.id','desc');
            $count = count($jobdata->get()->toArray());
            $jobdata = $jobdata->offset($offset)->limit($limit);
            $jobdata=$jobdata->get();
            $array['recordsTotal'] = $count;
            $array['recordsFiltered'] = $count;
            $array['data'] = $jobdata;
            return json_encode($array);
    }

    public function leave_print($id){
      
      $format=HR_Leave::where('hr__leave.id',$id)->leftJoin('users','users.id','hr__leave.user_id')
          ->leftJoin('users as level1','level1.id','hr__leave.status_level1_by')
          ->leftJoin('users as level2','level2.id','hr__leave.status_level2_by')
          ->select(
          'hr__leave.id',
          'users.name as emp',
          DB::raw('DATE_FORMAT(leave_apply_date,"%d/%m/%Y") as leave_d'),
          'hr__leave.email',
          'contact',
          DB::raw('DATE_FORMAT(start_date,"%d-%m-%Y") as start_date'),
          DB::raw('DATE_FORMAT(end_date,"%d-%m-%Y") as end_date'),
          'reason',
          'status_level1',
          'status_level2',
          'leave_status',
          'remark',
          DB::raw('DATE_FORMAT(status_level1_date,"%d-%m-%Y") as status_level1_date'),
          DB::raw('DATE_FORMAT(status_level2_date,"%d-%m-%Y") as status_level2_date'),
          'level1.name as level1',
          'level2.name as level2',
          DB::raw('(DATEDIFF(end_date,start_date)) as st_date')

      )->get()->first();
        if($format != null){
            $data = [
                'format' => $format
                ];
            $pdfFilePath = "Leave Application.pdf";
            $pdf = PDF::loadView('admin.hr.leave_pdf', $data);
            return $pdf->stream($pdfFilePath);
            return view('admin.hr.leave_pdf',$data);
        }
        else{
            $message="No Leave Application Exist!!";
            return redirect('/admin/hr/leave/list')->with('error',$message);
        }
    }

     public function leave_approve($id,Request $request){
        $hr1=Settings::where('name','HR_Leave_Level1')->select('value','name')->get()->first();
        $level1=explode(',',$hr1['value']);
        $status_level_2=$request->input('repoting');
        $lev='';
         if($request->input('status')){            
           foreach ($level1 as $key => $value) {
            if($value==$id){
                $lev="yes";
              }
            }
            if($lev=="yes"){
                $arr=[
                'status_level1'=>$request->input('status'),
                'status_level1_by'=>$id,
                'status_level1_date'=>date('Y-m-d'),
                'leave_status'=>$request->input('status'),
                'remark'=>$request->input('remark')

                ];
                  $msg='Successfully Leave '.$request->input('status');
                  $hr=HR_Leave::where('id',$request->input('leave_id'))->update($arr);
            }else{

                  return redirect('/admin/hr/leave/list')->with('error','Not Authority for leave Approvel');
              }
           
             $adjusted=0;
             $data=[
                  'status' => $request->input('status'),
             ];
             if($hr && $request->input('status')=='Approved'){
                $check_leave=HR_Leave::where('id',$request->input('leave_id'))->get()->first();

                if($check_leave){
                     if($check_leave['type']!='LWP'){
                       $adjusted=1;

                        $data = array_merge($data,[
                        'is_adjusted' =>$adjusted,
                       ]);

                      }

                    $start_date= strtotime($check_leave['start_date']);
                    $end_date= strtotime($check_leave['end_date']);
                    $user_id= $check_leave['user_id'];
                    $diff = (($end_date - $start_date)/60/60/24)+1;
                    $type= $check_leave['type'];
                    if($type!='LWP'){

                       $date=date_create(date("Y-m-d"));   
                       $financial_year = CustomHelpers::getFinancialYear($date);
                       $balancee_leave=LeaveBalance::where('user_id',$user_id)->where('type',$type)->where('financial_year',$financial_year)->get()->first();

                        if($balancee_leave){
                            $balance=$balancee_leave['balance']-$diff;
                            $leavebalance = LeaveBalance::where('user_id',$user_id)
                                           ->where('type',$type)
                                           ->update([
                                            'balance' => $balance
                                    ]);
                        }
                    }

                }
             }
              $leave_detail = HRLeaveDetails::where('leave_id',$request->input('leave_id'))
                       ->update($data);

                CustomHelpers::userActionLog($action='Leave Approve',$request->input('leave_id'),0);
              return redirect('/admin/hr/leave/list')->with('success',$msg);

         }
         elseif($request->input('status1'))
         {
            if($request->input('status1')=='Cancelled'){
                 $arr=[
                'status_level2'=>$request->input('status1'),
                'status_level2_by'=>$request->input('repoting'),
                'status_level2_date'=>date('Y-m-d'),
                'leave_status'=>$request->input('status1'),
                'remark'=>$request->input('remark')

                ];
            $leave_detail = HRLeaveDetails::where('leave_id',$request->input('leave_id'))
                       ->update([
                         'status' => $request->input('status1')
             ]);

            }else{
                 $arr=[
                'status_level2'=>$request->input('status1'),
                'status_level2_by'=>$request->input('repoting'),
                'status_level2_date'=>date('Y-m-d'),
                'remark'=>$request->input('remark')

                ];
            }
            
            $msg='Successfully Leave '.$request->input('status1');
            $hr=HR_Leave::where('id',$request->input('leave_id'))->update($arr);
             return redirect('/admin/hr/leave/list')->with('success',$msg);
        }  
    }

    public function leave_setting_list(){
        $data=[
            'layout' => 'layouts.main',
          
        ];
        return view('admin.hr.leave_setting_summary',$data);
    }
    public function leave_setting_list_api(Request $request){
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;

        $jobdata = Settings::leftJoin('users',function($join){
            $join->on(DB::raw("find_in_set(users.id,settings.value)"),'>',DB::raw("0"));
            $join->where('settings.name','HR_Leave_Level1');
        })->select(
            DB::raw('group_concat(users.name," ",ifnull( users.middle_name," ")," ",ifnull( users.last_name," ")) as level1')
        )->groupBy('users.id');
        if(!empty($serach_value))
        {
            $jobdata->where(function($query) use ($serach_value){
                $query->where('users.name','LIKE',"%".$serach_value."%")
                 ->orwhere('users.middle_name','like',"%".$serach_value."%")
                  ->orwhere('users.last_name','like',"%".$serach_value."%")
                ;
              });
     
        }
        
        if(isset($request->input('order')[0]['column']))
        {
            $data = [
                'level1'
                ];
            $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
            $jobdata->orderBy($data[$request->input('order')[0]['column']], $by);
        }
        else
            $jobdata->orderBy('settings.id','desc');
            $count = count($jobdata->get()->toArray());
            $jobdata = $jobdata->offset($offset)->limit($limit);
            $jobdata=$jobdata->get();
            if(!empty($serach_value)){
                $array['recordsTotal'] = $count;
                $array['recordsFiltered'] = $count;
            }else{
                
            $array['recordsTotal'] = $count-1;
            $array['recordsFiltered'] = $count-1;
            }
            $array['data'] = $jobdata; 
            return json_encode($array);
    }

    public function setting(){
        $emp = Users::select('id',DB::raw('concat(users.name," ",ifnull( users.middle_name," ")," ",ifnull( users.last_name," ")) as name'))->get();
        $hr1=Settings::where('name','HR_Leave_Level1')->select('value','name')->get()->first();
        $hr2=Settings::where('name','HR_Leave_Level2')->select('value','name')->get()->first();
        $data=[
            'layout' => 'layouts.main',
            'emp'=>$emp,
            'hr1'=>$hr1,
            'hr2'=>$hr2
        ];
        return view('admin.hr.setting',$data);
    }
    public function setting_Db(Request $request){
        try {
            $validerrarr =[
                'name1.*'=>'required',
            ];
            $validmsgarr =[
                'name1.*.required'=>'This field is required'
            ];
            $this->validate($request,$validerrarr,$validmsgarr);
                $hr1=Settings::where('name','HR_Leave_Level1')->update([
                    'value'=>implode(',',$request->input('name1')),
                ]);
            if($hr1==NULL){
                return redirect('/admin/hr/setting')->with('error','some error occurred')->withInput();
            }
            else{
                CustomHelpers::userActionLog($action='Leave Auhority Update',$hr1,0);
                return redirect('/admin/hr/setting')->with('success','Successfully Level Auhority for Leaves Created.');
            }
        } 
        catch(\Illuminate\Database\QueryException $ex) 
        {
            return redirect('/admin/hr/setting')->with('error','some error occurred'.$ex->getMessage())->withInput();
        }
    }

     public function attendance()
     {

        $store = Store::whereIn('id',CustomHelpers::user_store())
                    ->select('id',DB::raw('concat(store.name,"-",store.store_type) as name'))->get();
        $data = [
            'layout' => 'layouts.main',
            'store' =>  $store
        ];
        
        return view('admin.hr.attendance',$data);
    }

     public function attendance_Db(Request $request){
        
        $allowed_extension = array('xls','xlsx','xlt','xltm','xltx','xlsm');
        $validator = Validator::make($request->all(),[    
            'attendance'  => 'required',
        ],[
            'attendance.required'    =>  'This field is required.',
        ]);
        $validator->sometimes('attendance', 'mimes:xls,xlsx,xlt,xltm,xltx,xlsm', function ($input) use($allowed_extension) {
            $extension="";
            if(isset($input->attendance))
            $extension = $input->attendance->getClientOriginalExtension();
            if(in_array($extension,$allowed_extension))
                return false;
            else
                return true;
        });
        if ($validator->fails()) {
            return redirect('admin/hr/user/attendance')
                        ->withErrors($validator)
                        ->withInput();
        }

       $redirect_to = '/admin/hr/user/attendance';

         try {
            DB::beginTransaction();

            $get_user = Users::orderBy('id','ASC')->where('status','<>',2)->select('emp_id','store_id')->get()->toArray();
            $user_data = [];
            foreach($get_user as $k => $v){
                $user_data[$v['emp_id']] = $v;
            }

            if(empty($user_data)){
                return back()->with('error','User Record Not Found');
            }

            if($request->file('attendance'))                
            {
                $shift_error = '';
                $path = $request->file('attendance');                
                $data = Excel::toArray(new Import(),$path);
                if(count($data) > 0 && $data && isset($data[0][1])){
                    // getting from_date to to_date  ---------------------------------
                    // $date = $data[0][1][1];
                    $date = $data[0][1];
                    $attdate = [];
                    foreach($date as $link) {
                        if($link != '') {
                            $attdate[] = $link;
                        }
                        if($link == '') {
                            unset($link);
                        }
                    }
                    $datestr = implode('', $attdate);
                    $date = explode('To',$datestr);
                    if(!isset($date[0]) || !isset($date[1])){
                        return back()->with('error','Please Upload Correct Excel File Format.');
                    }

                    $from_date = trim($date[0]); 
                    $to_date = trim($date[1]); 

                    $from_date = explode(' ',$from_date);
                    $to_date = explode(' ',$to_date);
                    // check from and to date
                    if(!isset($from_date[0]) || !isset($from_date[1]) || !isset($from_date[2]) || !isset($to_date[0]) || !isset($to_date[1]) || !isset($to_date[2])){
                        return back()->with('error','Please Upload Correct Excel File Format.')->withInput();
                    }

                    $from_date = $from_date[0].' '.$from_date[1].' '.$from_date[2];
                    $to_date = $to_date[0].' '.$to_date[1].' '.$to_date[2];

                    $from_date = date('Y-m-d', strtotime($from_date));
                    $to_date = date('Y-m-d', strtotime($to_date));
                    
                    // -------------------------------------------------

                    // calculate number of days
                    $no_day = CustomHelpers::getDay($from_date,$to_date);
                    $no_day = $no_day+1;
                    // -------------------

                    $days = [];
                    foreach($data as $data_key => $data_value) {
                        foreach($data_value as $key => $val){
                            // check days column
                            if($val[0] == 'Days')
                            {
                                $days = $val;
                            }
                            // --------------
                            // check employee code
                            if($val[0] == 'Emp. Code :')
                            {
                                $col_end = $no_day;

                                $emp_id = trim($val[3]);
                                if(empty($emp_id)){
                                    return back()->with('error','Please Upload Correct Excel File Format.')->withInput();
                                }
                                $status_row = $key+1;
                                $inTime_row = $status_row+1;
                                $outTime_row = $inTime_row+1;
                                $totalTime_row = $outTime_row+1;
                                $index = 2;
                                // check employee id exist in our Users table for respective store
                                if(array_key_exists($emp_id,$user_data) && !empty($emp_id)){
                                    if(!empty($user_data[$emp_id]['store_id'])){
                                        $store_id = explode(',',$user_data[$emp_id]['store_id'])[0];
                                    }else{
                                        $store_id = 0;
                                    }
                                    $getShift = Users::leftjoin('user_details','user_details.user_id','users.id')
                                        ->leftjoin('shift_timing','user_details.shift_timing_id','shift_timing.id')
                                        ->where('users.emp_id',$emp_id)
                                        ->select('shift_timing.id','shift_timing.shift_from','shift_timing.shift_to',DB::raw("TIMESTAMPDIFF(hour,shift_timing.shift_from,shift_timing.shift_to) as total_shift"))
                                        ->first();
                                    if (empty($getShift['id'])) {
                                         $shift_error = $shift_error.$emp_id.'.';
                                    }

                                    for($i = 0; $i < $col_end ; $i++) {
                                        if(isset($days[$index])){
                                            $status = $data_value[$status_row][$index];
                                            $inTime = $data_value[$inTime_row][$index];
                                            $outTime = $data_value[$outTime_row][$index];
                                            $totalTime = $data_value[$totalTime_row][$index];
                                            // Get date
                                            $d = explode(' ',$days[$index])[0];
                                            $m = date('m',strtotime($from_date));
                                            $y = date('Y',strtotime($from_date));
                                            if($d == 1 && $i > 0){
                                                $m = date('m',strtotime($to_date));
                                            }
                                            $atten_date = $y.'-'.$m.'-'.$d;
                                            $lateCal = CustomHelpers::LateEarlyDuration($emp_id,$store_id,$atten_date,$inTime,$outTime,$status);
                                        }
                                        elseif(empty($days[$index])){
                                            $col_end = $col_end+1;
                                        }
                                        $index++;
                                    }

                                }
                                $key = $totalTime_row;
                            }
                        }
                    }
                }
                else{
                    DB::rollback();
                    return back()->with('error','Error, Check your file should not be empty.');
                }
            }
             else{
                 DB::rollback();
                return back()->with('error', 'Some Error Occurred Please Try again.');
            }
           
        
        }  catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return redirect($redirect_to)->with('error','some error occurred'.$ex->getMessage())->withInput();
        }
        DB::commit();

        if(empty($shift_error)) {  
            CustomHelpers::userActionLog($action = 'Attendance import',0,0);
            return redirect($redirect_to)->with('success','Attendance imported successfully .');
        }else{
            CustomHelpers::userActionLog($action = 'Attendance import',0,0);
            return redirect($redirect_to)->with('success','Attendance imported successfully, but some employee shift timing could not be find, such as :-'.$shift_error.'.');
        }
        CustomHelpers::userActionLog($action = 'Attendance import',0,0);
        return redirect($redirect_to)->with('success','Attendance imported successfully.');

    }

    public function attendance_list() {

        $store = Store::whereIn('id',CustomHelpers::user_store())
                    ->select('id',DB::raw('concat(store.name,"-",store.store_type) as name'))->get();
        $users= Users::select('emp_id as id','name','middle_name','last_name')->get();
        $nop=NOP::select('emp_id','user_id','date','time','id','comment')->get();
        $data=array('users'=>$users,'store'=>$store,'nop'=>$nop,'layout'=>'layouts.main');
        return view('admin.hr.attendance_list', $data); 
    }

   public function attendance_list_api(Request $request){
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
        $limit =  empty($limit) ? 100 : $limit ;
        $store = CustomHelpers::user_store();
        
        $user = Auth::user();
        $user_type = $user['user_type'];
        $role = $user['role'];
        if($user_type=="superadmin" || $role=='HRDManager'){


          $api_data= Attendance::leftJoin('users','attendance.emp_id','users.emp_id')
            ->leftJoin('store','attendance.store_id','store.id')
            ->leftjoin('user_details','user_details.user_id','users.id')
            ->leftjoin('shift_timing','user_details.shift_timing_id','shift_timing.id')
            ->select(
                 DB::raw('concat(users.name," ",ifnull( users.middle_name," ")," ",ifnull( users.last_name," ")) as name'),
                'users.emp_id',
                'users.id as user_id',
                'attendance.status',
                'attendance.intime',
                'attendance.outtime',
                'attendance.totaltime',
                'attendance.late_by',
                'attendance.half_day',
                'attendance.early_going',
                'attendance.late_early_status',
                'shift_timing.shift_to',
                'attendance.date as attendance_date',
                DB::raw("MonthName(attendance.date) date"),
                DB::raw("DATEDIFF(now(),attendance.date) as days"),
                DB::raw('concat(store.name,"-",store.store_type) as store_name'),
                DB::raw("TIMESTAMPDIFF(hour,shift_timing.shift_from,shift_timing.shift_to) as timing")
            );

        }else{

            $api_data= Attendance::leftJoin('users','attendance.emp_id','users.emp_id')
            ->leftJoin('store','attendance.store_id','store.id')
            ->where('attendance.emp_id',Auth::user()->emp_id)
            ->select(
                 DB::raw('concat(users.name," ",ifnull( users.middle_name," ")," ",ifnull( users.last_name," ")) as name'),
                'users.emp_id',
                'users.id as user_id',
                'attendance.status',
                'attendance.intime',
                'attendance.outtime',
                'attendance.totaltime',
                'attendance.half_day',
                'attendance.late_by',
                'attendance.early_going',
                'attendance.late_early_status',
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
        foreach($api_data as $key => $val) {
           $date = $val['attendance_date'];
           $user_id = $val['user_id'];
           $leavedata = HRLeaveDetails::leftjoin('hr__leave','hr__leave_details.leave_id','hr__leave.id')
                ->where('hr__leave.user_id',$user_id)
                ->where('hr__leave_details.date',$date)
                ->where('hr__leave_details.status','Approved')
                ->where('hr__leave.leave_status','Approved')
                ->select('hr__leave.type as leave_type')->first();
            if ($leavedata) {
                $api_data[$key]['leave_type'] = $leavedata['leave_type'];
            }else{
                $api_data[$key]['leave_type'] = '';
            }
        }
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 

        return json_encode($array);
    }

     public function nop()
     {
        $emp=Users::select('id','name','middle_name','last_name')->orderBy('emp_id')->get();
        $data = [
            'layout' => 'layouts.main',
            'emp'=>$emp
        ];
        return view('admin.hr.nop',$data);
    }

    public function nop_Db(Request $request){
        try {
            $validerrarr =[
                'employee_name'=>'required',
                'date'=>'required',
                'type'=>'required',
                'time'=>'required',
            ];
            $validmsgarr =[
                'employee_name.required'=>'This field is required',
                'date.required'=>'This field is required',
                'type.required'=>'This field is required',
                'time.required'=>'This field is required'
            ];
            $this->validate($request,$validerrarr,$validmsgarr);
            $status='Pending';
            $time = $request->input('time');
            $time1  = str_replace(' ','',$time);
            
            $users=Users::where('id',$request->input('employee_name'))->get()->first();

            if($users){

                $emp_id=$users['emp_id'];
                $date=Carbon::createFromFormat('d/m/Y', $request->input('date'))->format('Y-m-d');

                 $checknop=NOP::where('user_id',$request->input('employee_name'))->where('date',$date)->first();
                 if($checknop){
                    $type=$checknop->type;
                    
                    return redirect('/admin/hr/user/nop')->with('error', 'NOP already applied on this date')->withInput();
                 }else{

                        $hr=NOP::insertGetId([
                            'id'=>NULL,
                            'user_id'=>$request->input('employee_name'),
                            'emp_id'=>$emp_id,
                            'type'=>$request->input('type'),
                            'date'=>$date,
                            'time'=>$time1,
                            'status'=>$status

                        ]);
                        if($hr==NULL){
                            return redirect('/admin/hr/user/nop')->with('error','some error occurred')->withInput();
                        }
                        else{
                             CustomHelpers::userActionLog($action='Nop Apply',$hr,0);
                            return redirect('/admin/hr/user/nop')->with('success','Successfully NOP Applied.');
                        }
                 }

            }else{
                 return redirect('/admin/hr/user/nop')->with('error','some error occurred')->withInput();
            }

        } 
        catch(\Illuminate\Database\QueryException $ex) 
        {
            return redirect('/admin/hr/user/nop')->with('error','some error occurred'.$ex->getMessage())->withInput();
        }
    }

 public function nop_list() {

        $hr1=Settings::where('name','HR_Leave_Level1')->select('value','name')->get()->first();
        $hr=$hr1['value'];
        
        $data=array('layout'=>'layouts.main','hr'=>$hr);
        return view('admin.hr.nop_list', $data); 
    }

   public function nop_list_api(Request $request){
        
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
                   
      $api_data= NOP::leftJoin('users','nop.user_id','users.id')
             ->leftJoin('user_details','nop.user_id','user_details.user_id')
            ->select(
                'nop.user_id',
                'nop.approve_rejected_by_hr',
                'nop.approve_rejected_by',
                'nop.status_hr_by',
                'nop.status_reporting_by',
                'users.name',
                'users.middle_name',
                'users.last_name',
                'users.emp_id',
                'nop.date',
                'nop.time',
                'nop.type',
                'nop.status',
                'nop.id',
                'user_details.reporting_manager as reporting'

            );
            if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('users.name','like',"%".$serach_value."%")
                    ->orwhere('nop.date','like',"%".$serach_value."%")
                    ->orwhere('nop.time','like',"%".$serach_value."%")
                    ->orwhere('nop.type','like',"%".$serach_value."%")
                    
                    ;
                });
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                'users.name',
                'nop.date',
                'nop.time',
                'nop.type',
                'users.emp_id',
                'nop.status'

                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('users.emp_id','asc'); 

        $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

    public function nop_approve($id,Request $request){

        $check=NOP::where('id',$id)->select('user_id','type','date','time')->get()->first();
        if(!$check){
            
            return redirect('/admin/hr/user/nop/list')->with('error','ID not exist');
        }
        else{
                
            $type=$check->type;
            $date=$check->date;
            $time=$check->time;
            $check_attendance=Attendance::where(['emp_id'=>$request->input('emp_id'),'date'=>$date])->select('id','intime','outtime','totaltime')->get()->first();

        if($request->input('status')){ 

            if($request->input('status')=='Approved'){

             if($check_attendance){

                $attendance_intime=$check_attendance->intime;
                $attendance_id=$check_attendance->id;
                $attendance_outtime=$check_attendance->outtime;
                $attendance_totaltime=$check_attendance->totaltime;
                $start_time = new DateTime($attendance_intime); 
                $finish_time = new DateTime($attendance_outtime); 
            
                $emp_id=$request->input('emp_id');

                    if($type=='PunchIn'){
                        $start_time = new DateTime($time);
                        $diff = $start_time->Diff($finish_time);
                        $hours = $diff->format("%H:%i");
                             $attendance=[
                             'intime'=>$time,
                             'status'=>'P',
                             'totaltime'=>$hours
                        ];
                    }elseif($type=='PunchOut'){
                      $finish_time = new DateTime($time);
                      $diff = $start_time->Diff($finish_time);
                      $hours = $diff->format("%H:%i");
                          $attendance=[
                          'outtime'=>$time,
                          'status'=>'P',
                          'totaltime'=>$hours
                          ];
                    }
                    $attendance=Attendance::where('id',$attendance_id)->update($attendance);
                
                    $arr=[
                    'status_hr_by'=>$request->input('status'),
                    'approve_rejected_by_hr'=>$request->input('hr_id'),
                    'status'=>$request->input('status'),
                    'remark'=>$request->input('remark')
                    ];

                }else{
                     return redirect('/admin/hr/user/nop/list')->with('error',"User not attendance on this date"); 
                }
            }else if($request->input('status')=='Rejected'){
                     $arr=[
                    'status_hr_by'=>$request->input('status'),
                    'approve_rejected_by_hr'=>$request->input('hr_id'),
                    'status'=>$request->input('status'),
                    'remark'=>$request->input('remark')
                    ];
             
            }
         $msg='Successfully NOP '.$request->input('status');

        }else if($request->input('status1')){

            if($request->input('status1')=='Approved'){
                 if($check_attendance){
                    $arr=[
                    'status_reporting_by'=>$request->input('status1'),
                    'approve_rejected_by'=>$request->input('repoting'),
                    'remark'=>$request->input('remark')
                    ];
                }else{
                     return redirect('/admin/hr/user/nop/list')->with('error',"User not attendance on this date"); 
                }
                
              }else if($request->input('status1')=='Rejected'){
                 $arr=[
                    'status_reporting_by'=>$request->input('status1'),
                    'approve_rejected_by'=>$request->input('repoting'),
                    'status'=>$request->input('status1'),
                    'remark'=>$request->input('remark')
                    ];
              }
               $msg='Successfully NOP '.$request->input('status1');
            }

             $hr=NOP::where('id',$id)->update($arr);
             if($hr){
                CustomHelpers::userActionLog($action='NOP Approve',$hr,0);
              return redirect('/admin/hr/user/nop/list')->with('success',$msg); 
             }
             else{
                DB::rollback();
                return back()->with('error','Some Error Occurred.')->withInput();
             }
        }
           
    }

     public function penalty()
     {

        $data = [
            'layout' => 'layouts.main'
        ];
        
        return view('admin.hr.penalty',$data);
    }

     public function penalty_Db(Request $request){
        
        $allowed_extension = array('xls','xlsx','xlt','xltm','xltx','xlsm');
        $validator = Validator::make($request->all(),[    
            'penalty'  => 'required'
        ],[
            'penalty.required'    =>  'This field is required.'
        ]);
        $validator->sometimes('penalty', 'mimes:xls,xlsx,xlt,xltm,xltx,xlsm', function ($input) use($allowed_extension) {
            $extension="";
            if(isset($input->penalty))
            $extension = $input->penalty->getClientOriginalExtension();
            if(in_array($extension,$allowed_extension))
                return false;
            else
                return true;
        });
        if ($validator->fails()) {
            return redirect('admin/hr/user/penalty/import')
                        ->withErrors($validator)
                        ->withInput();
        }

       $redirect_to = '/admin/hr/user/penalty/import';

         try {
            DB::beginTransaction();

            if($request->file('penalty'))                
            {
                $path = $request->file('penalty');                
                $data = Excel::toArray(new Import(),$path);
                
                if(count($data) > 0 && $data && isset($data[0][1])){
                  foreach($data[0] as $key => $value){
                            
                    if($key>=4 && !empty($value[1])){
                             $insert_data = PenaltyMaster::insertGetId(
                                [
                                  'name'  => $value[1],
                                  'amount'   => $value[2]
                                ]);
                        }
                      }
                     
                       DB::commit();
                       CustomHelpers::userActionLog($action='Penalty Import',0,0);
                       return redirect($redirect_to)->with('success','Successfully Uploaded.');
                }
                else{
                    DB::rollback();
                    return back()->with('error','Error, Check your file should not be empty.');
                }
            }
             else{
                 DB::rollback();
                return back()->with('error', 'Some Error Occurred Please Try again.');
            }
           
        
        }  catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return redirect($redirect_to)->with('error','some error occurred'.$ex->getMessage())->withInput();
        }
    }
    public function penaltyform()
    {
        $users = Users::orderBy('emp_id')->get();
        $penalty = PenaltyMaster::get();
        $data = [
            'users'=>$users,
            'penalty'=>$penalty,
            'layout' => 'layouts.main'
        ];
        
        return view('admin.hr.penaltyform',$data);
    }

    public function filter_penalty_api(Request $request){

        $assets = PenaltyMaster::where('id', $request->input('assets'))->get('amount')->first();
        return response()->json($assets);
    }

      public function penaltyform_Db(Request $request){
        
         try {
            $this->validate($request,[
                'employee'=>'required',
                'penalty'=>'required',
            ],[
                'employee.required'=> 'This is required.',
                'penalty.required'=> 'This is required.'  
            ]);

            $customer = UserPenalty::insertGetId(
                [ 
                    'user_id'=>$request->input('employee'),
                    'penalty_id'=>$request->input('penalty'),
                    'amount'=>$request->input('amount'),
                    'remark'=>$request->input('remark')
                    
                ]
            );

            if($customer==NULL) {
                DB::rollback();
                return redirect('/admin/hr/user/penalty/form')->with('error','Some Unexpected Error occurred.');
            }
            else{

                CustomHelpers::userActionLog($action='User Penalty Add',$customer,0);
                return redirect('/admin/hr/user/penalty/form')->with('success','Successfully Created Penalty.'); 
             }    
        
    }  catch(\Illuminate\Database\QueryException $ex) {
        return redirect('/admin/hr/user/penalty/form')->with('error','some error occurred'.$ex->getMessage())->withInput();
    }
    }

    public function monthlysalary($id){
        $month=date('m');
        $arrears_amount=0;$bonus_amount=0;$cr_adjustment=0;$dr_adjustment=0;$dr_penalty=0;$advance_amt=0;$ot_amt=0;
        $dr_leave=0;$advance=0;$net_salary=0;$basic_salary=0;$overtime_hours=0;$present=0;$absent=0;$wo=0;
        $present_amt=0;$absent_amt=0;$wo_amt=0;$month_salary=0;$total_absent=0;$netSalary=0;$total_deduction=0;
        $total_absent_amt=0;$penalty_count=0;$total_present_amt=0;$total_present=0;$approvel_leave=0;$user_attendance=1;

        $all_calculation=CustomHelpers::salaryCalculation($id,$month,$advance);
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
                            $penalty_count=$all_calculation['penalty']['count'];
                        }if(array_key_exists("leave",$all_calculation)){
                            $dr_leave=$all_calculation['leave']['amount'];
                        }if(array_key_exists("advance",$all_calculation)){
                            $advance_amt=$all_calculation['advance']['amount'];
                        }if(array_key_exists("overtime",$all_calculation)){
                            $ot_amt=$all_calculation['overtime']['amount'];
                        }if(array_key_exists("overtime",$all_calculation)){
                            $overtime_hours=$all_calculation['overtime']['overtime_hours'];
                        }if(array_key_exists("user_payroll",$all_calculation)){
                             $net_salary=$all_calculation['user_payroll']['Net Salary'];
                             $basic_salary=$all_calculation['user_payroll']['Basic Salary'];
                             $hra=$all_calculation['user_payroll']['Hra'];
                             $ta=$all_calculation['user_payroll']['Ta'];
                             $perf_allowance=$all_calculation['user_payroll']['Perf Allowance'];
                             $others=$all_calculation['user_payroll']['Others'];
                             $tax_deduction=$all_calculation['user_payroll']['Tax Deduction'];
                             $pf_deduction=$all_calculation['user_payroll']['Pf Deduction'];
                             $other_deduction=$all_calculation['user_payroll']['Other Deduction'];
                        }
                        if(array_key_exists("attendance",$all_calculation)){
                             $present=$all_calculation['attendance']['present'];
                             $absent=$all_calculation['attendance']['absent'];
                             $approvel_leave=$all_calculation['attendance']['approvel_leave'];
                             $wo=$all_calculation['attendance']['wo'];
                             $present_amt=$all_calculation['attendance']['present_amt'];
                             $absent_amt=$all_calculation['attendance']['absent_amt'];
                             $total_absent_amt=$all_calculation['attendance']['total_absent_amt'];
                             $total_present_amt =$all_calculation['attendance']['total_present_amt'];
                             $total_present =$all_calculation['attendance']['total_present'];
                             $wo_amt=$all_calculation['attendance']['wo_amt'];
                             $month_salary=$all_calculation['attendance']['salary'];
                             $total_absent=$all_calculation['attendance']['total_absent'];
                             
                        }if(array_key_exists("net_salary",$all_calculation)){
                            $netSalary=$all_calculation['net_salary']['total'];

                        }if(array_key_exists("deduction",$all_calculation)){
                            $total_deduction=$all_calculation['deduction']['total'];

                        }if(array_key_exists("user_attendance",$all_calculation)){
                             $user_attendance=$all_calculation['user_attendance']['total'];
                        }  
                    }else{
                        $message=$all_calculation['message'];
                        DB::rollback();
                        return redirect('/admin/hr/user/list/')->with('error','Error, '.$message.' for this User Id. '.$id.'')->withInput();
                      }
                }
             }
          }
        $customerdata =Users::where('id',$id)->get([DB::raw('concat(users.name," ",ifnull( users.middle_name," ")," ",ifnull( users.last_name," ")) as name'),'users.emp_id'])->first();
         if(!$customerdata){
             return redirect('/admin/hr/user/list/')->with('error','Basic salary is not set for this user');

         }else{

          $payroll_salary = PayrollSalary::leftJoin('users','payroll_salary.user_id','users.id')
                    ->where('payroll_salary.user_id',$id) 
                    ->whereMonth('payroll_salary.salary_month', date('m'))
                    ->whereYear('payroll_salary.salary_month', date('Y'))        
                    ->select(
                        'payroll_salary.id',
                        'payroll_salary.*'

                    )->get()->first();

        $data = array(
            'customerdata' => $customerdata,
            'id' => $id,
            'arrears'=>$arrears_amount,
            'bonus'=>$bonus_amount,
            'cr_adjustment'=>$cr_adjustment,
            'dr_adjustment'=>$dr_adjustment,
            'dr_penalty'=>$dr_penalty,
            'dr_leave'=>$dr_leave,
            'advance_amt'=>$advance_amt,
            'ot_amt'=>$ot_amt,
            'month_net_salary'=>$net_salary,
            'overtime_hours'=>$overtime_hours,
            'net_salary'=>$netSalary,
            'basic_salary'=>$basic_salary,
            'hra'=>$hra,
            'ta'=>$ta,
            'perf_allowance'=>$perf_allowance,
            'others'=>$others,
            'tax_deduction'=>$tax_deduction,
            'pf_deduction'=>$pf_deduction,
            'other_deduction'=>$other_deduction,
            'present'=>$present,
            'absent'=>$absent,
            'approvel_leave'=>$approvel_leave,
            'present_amt'=>$present_amt,
            'absent_amt'=>$absent_amt,
            'wo_amt'=>$wo_amt,
            'month_salary'=>$month_salary,
            'total_absent_amt'=>$total_absent_amt,
            'total_present_amt'=>$total_present_amt,
            'total_present'=>$total_present,
            'total_absent'=>$total_absent,
            'user_attendance'=>$user_attendance,
            'penalty_count'=>$penalty_count,
            'layout' => 'layouts.main'
        );

        if($payroll_salary){
               
            $payroll_id=$payroll_salary->id;
            $payroll_salary_details = PayrollSalaryDetail::where('payroll_salary_id',$payroll_id)->get();
            $data = array_merge($data,[
                            'payroll_salary'=>$payroll_salary,
                            'net_salarys'=>$payroll_salary->net_salary,
                            'payroll_salary_details'=>$payroll_salary_details
                        ]);
            return view('admin.hr.monthlysalary',$data); 
        }else{
           return view('admin.hr.monthlysalary',$data); 
        }
          
      }
    }

    public function Usermonthlysalary(Request $request) {
        $id = $request->input('id');
        $salarydate = $request->input('date');
        $arrears_amount=0;$bonus_amount=0;$cr_adjustment=0;$dr_adjustment=0;$dr_penalty=0;$advance_amt=0;
        $ot_amt=0;$dr_leave=0;$advance=0;$net_salary=0;$basic_salary=0;$overtime_hours=0;$total_absent_amt=0;
        $present=0;$absent=0;$wo=0;$present_amt=0;$absent_amt=0;$wo_amt=0;$month_salary=0;$user_attendance=1;
        $total_absent=0;$penalty_count=0;$total_present_amt=0;$total_present=0;$approvel_leave=0;

        $all_calculation=CustomHelpers::salaryCalculation($id,$salarydate,$advance);

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
                            $penalty_count=$all_calculation['penalty']['count'];
                        }if(array_key_exists("leave",$all_calculation)){
                            $dr_leave=$all_calculation['leave']['amount'];
                        }if(array_key_exists("advance",$all_calculation)){
                            $advance_amt=$all_calculation['advance']['amount'];
                        }if(array_key_exists("overtime",$all_calculation)){
                            $ot_amt=$all_calculation['overtime']['amount'];
                        }if(array_key_exists("overtime",$all_calculation)){
                            $overtime_hours=$all_calculation['overtime']['overtime_hours'];
                        }if(array_key_exists("user_payroll",$all_calculation)){
                             $net_salary=$all_calculation['user_payroll']['Net Salary'];
                             $basic_salary=$all_calculation['user_payroll']['Basic Salary'];
                             $hra=$all_calculation['user_payroll']['Hra'];
                             $ta=$all_calculation['user_payroll']['Ta'];
                             $perf_allowance=$all_calculation['user_payroll']['Perf Allowance'];
                             $others=$all_calculation['user_payroll']['Others'];
                             $tax_deduction=$all_calculation['user_payroll']['Tax Deduction'];
                             $pf_deduction=$all_calculation['user_payroll']['Pf Deduction'];
                             $other_deduction=$all_calculation['user_payroll']['Other Deduction'];
                        }
                        if(array_key_exists("attendance",$all_calculation)){
                             $present=$all_calculation['attendance']['present'];
                             $absent=$all_calculation['attendance']['absent'];
                             $wo=$all_calculation['attendance']['wo'];
                             $present_amt=$all_calculation['attendance']['present_amt'];
                             $absent_amt=$all_calculation['attendance']['absent_amt'];
                             $total_absent_amt=$all_calculation['attendance']['total_absent_amt'];
                             $wo_amt=$all_calculation['attendance']['wo_amt'];
                             $month_salary=$all_calculation['attendance']['salary'];
                             $total_absent=$all_calculation['attendance']['total_absent'];
                             $total_present_amt =$all_calculation['attendance']['total_present_amt'];
                             $total_present =$all_calculation['attendance']['total_present'];
                             $approvel_leave=$all_calculation['attendance']['approvel_leave'];

                        } if(array_key_exists("net_salary",$all_calculation)){
                            $netSalary=$all_calculation['net_salary']['total'];

                        }if(array_key_exists("deduction",$all_calculation)){
                            $total_deduction=$all_calculation['deduction']['total'];

                        }if(array_key_exists("user_attendance",$all_calculation)){
                            $user_attendance=$all_calculation['user_attendance']['total'];

                        }                           
                    }else{
                         $message=$all_calculation['message'];
                         return response()->json(['messages' =>$message.' for this User Id. '.$id],422);
                      }
                }
             }
          }

        $customerdata =Users::where('id',$id)->get([DB::raw('concat(users.name," ",ifnull( users.middle_name," ")," ",ifnull( users.last_name," ")) as name'),'users.emp_id'])->first();
    
         $payroll_salary = PayrollSalary::leftJoin('users','payroll_salary.user_id','users.id')
                    ->where('payroll_salary.user_id',$id) 
                    ->whereMonth('payroll_salary.salary_month',$salarydate)
                    ->whereYear('payroll_salary.salary_month', date('Y'))        
                    ->select(
                        'payroll_salary.id',
                        'payroll_salary.*'

                    )->get()->first();

         $data = array(
            'customerdata' => $customerdata,
            'id' => $id,
            'arrears'=>$arrears_amount,
            'bonus'=>$bonus_amount,
            'cr_adjustment'=>$cr_adjustment,
            'dr_adjustment'=>$dr_adjustment,
            'dr_penalty'=>$dr_penalty,
            'dr_leave'=>$dr_leave,
            'advance_amt'=>$advance_amt,
            'ot_amt'=>$ot_amt,
            'overtime_hours'=>$overtime_hours,
            'net_salary'=>$netSalary,
            'basic_salary'=>$basic_salary,
            'hra'=>$hra,
            'ta'=>$ta,
            'perf_allowance'=>$perf_allowance,
            'others'=>$others,
            'tax_deduction'=>$tax_deduction,
            'pf_deduction'=>$pf_deduction,
            'other_deduction'=>$other_deduction,
            'present'=>$present,
            'absent'=>$absent,
            'approvel_leave'=>$approvel_leave,
            'present_amt'=>$present_amt,
            'absent_amt'=>$absent_amt,
            'wo_amt'=>$wo_amt,
            'month_salary'=>$month_salary,
            'total_absent'=>$total_absent,
            'penalty_count'=>$penalty_count,
            'total_absent_amt'=>$total_absent_amt,
            'total_present_amt'=>$total_present_amt,
            'total_present'=>$total_present,
            'user_attendance'=>$user_attendance,
            'month_net_salary'=>$net_salary,

        );

        if($payroll_salary){
               
            $payroll_id=$payroll_salary->id;
            $payroll_salary_details = PayrollSalaryDetail::where('payroll_salary_id',$payroll_id)->get();

            $data = array_merge($data,[
                    'payroll_salary'=>$payroll_salary,
                    'net_salarys'=>$payroll_salary->net_salary,
                    'payroll_salary_details'=>$payroll_salary_details
                  ]); 
            return response()->json($data);
        }else{
         return response()->json($data);
        }
    }

    public function reported_attendance_list() {

        $users = Users::leftJoin('user_details','users.id','user_details.user_id')
                         ->where('user_details.reporting_manager',Auth::id())
                         ->select('emp_id as id','name','middle_name','last_name')->get();
        $nop=NOP::select('emp_id','user_id','date','time','id','comment')->get();
        $data = array('users'=>$users,'layout'=>'layouts.main','nop'=>$nop);
        return view('admin.hr.reporting_manager_attendance_list', $data); 
    }

   public function reported_attendance_list_api(Request $request){
        $search = $request->input('search');
        $user_name=$request->input('store_name');
        $year=$request->input('year');
        $month=$request->input('month');
        $date=$request->input('date');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
        $api_data= Attendance::leftJoin('users','attendance.emp_id','users.emp_id')
            ->leftJoin('user_details','users.id','user_details.user_id')
            ->leftjoin('shift_timing','user_details.shift_timing_id','shift_timing.id')
            ->leftJoin('store','attendance.store_id','store.id')
            ->where('user_details.reporting_manager',Auth::id())
            ->select(
                 DB::raw('concat(users.name," ",ifnull( users.middle_name," ")," ",ifnull( users.last_name," ")) as name'),
                'users.emp_id',
                'attendance.status',
                'attendance.intime',
                'attendance.outtime',
                'attendance.totaltime',
                'attendance.half_day',
                'users.id as user_id',
                'attendance.late_by',
                'attendance.early_going',
                'attendance.late_early_status',
                'shift_timing.shift_to',
                'attendance.date as attendance_date',
                DB::raw("DATEDIFF(now(),attendance.date) as days"),
                DB::raw('concat(store.name,"-",store.store_type) as store_name'),
                DB::raw("TIMESTAMPDIFF(hour,shift_timing.shift_from,shift_timing.shift_to) as timing")
            );

             if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('attendance.id','like',"%".$serach_value."%")
                    ->orwhere('attendance.status','like',"%".$serach_value."%")
                    ->orwhere('attendance.intime','like',"%".$serach_value."%")
                    ->orwhere('attendance.outtime','like',"%".$serach_value."%")
                    ->orwhere('attendance.totaltime','like',"%".$serach_value."%")
                    ->orwhere('attendance.late_by','like',"%".$serach_value."%")
                    ->orwhere('attendance.early_going','like',"%".$serach_value."%")
                    ->orwhere('attendance.half_day','like',"%".$serach_value."%")
                    ->orwhere('attendance.late_early_status','like',"%".$serach_value."%")
                    ->orwhere('users.emp_id','like',"%".$serach_value."%")
                    ->orwhere('store.name','like',"%".$serach_value."%")
                    
                    ;
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
                    'users.name',
                    'attendance.status',
                    'attendance.intime',
                    'attendance.outtime',
                    'attendance.totaltime',
                    'attendance.late_by',
                    'attendance.early_going',
                    'attendance.half_day',
                    'attendance_date',
                    'store_name',
                    'attendance.late_early_status',
                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('attendance.date','desc');      

        $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

    public function employee_attendance_list() {
        $nop=NOP::select('emp_id','user_id','date','time','id','comment')->get();
        $users = Users::select('emp_id as id','name','middle_name','last_name')->get();
        $data = array('nop'=>$nop,'users' => $users,'layout'=>'layouts.main');
        return view('admin.hr.employee_attendance_list', $data); 
    }

    public function employee_attendance_list_api(Request $request){
        $search = $request->input('search');
        $user_name = $request->input('user_name');
        $month = $request->input('month');
        $year = $request->input('year');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start;
        $limit = empty($limit) ? 10 : $limit;
        $user = attendance::select('emp_id')->get()->toArray();
        $a_date = date('Y-m-d');
        if ($month) {
            $month_year = date('Y-'.$month);
        }else if ($month && $year) {
            $month_year = date($year.'-'.$month);
        }else{
            $month_year = date('Y-m');
        }
        
        $date = new DateTime($a_date);
        $date->modify('last day of this month');
        $last_day = $date->format('d'); 
        DB::enableQueryLog();
        $sort_data_query = array();

       for ($j = 1; $j <= $last_day ; $j++) {           
            $mdate = '"'.$month_year.'-'.$j.'"';
            
            $query[$j] = "IFNULL((SELECT CONCAT(att.status,',',att.date,',',att.intime,',',att.outtime,',',att.totaltime,',',att.late_by,',',att.early_going,',',att.half_day,',',att.late_early_status) FROM attendance att WHERE att.emp_id = attendance.emp_id AND att.date = ".$mdate."),'') as d".$j." ";


        }
        $query = join(",",$query);

        $date = Carbon::now();
        if ($month) {
            $month_name = $date->format($month);
        }else{
            $month_name = date('m');  
        }
        $api_data = Attendance::leftJoin('users','attendance.emp_id','users.emp_id')
            ->leftJoin('store','attendance.store_id','store.id')
            ->leftjoin('user_details','user_details.user_id','users.id')
            ->leftjoin('shift_timing','user_details.shift_timing_id','shift_timing.id')
            ->whereMonth('attendance.date', date($month_name))
            ->select(
                 DB::raw('concat(users.name," ",ifnull( users.middle_name," ")," ",ifnull( users.last_name," ")) as name'),
                'users.emp_id',
                'users.id as user_id',
                'attendance.status',
                'attendance.intime',
                'attendance.outtime',
                'attendance.totaltime',
                'attendance.date as attendance_date',
                DB::raw("DATEDIFF(now(),attendance.date) as days"),
                DB::raw('DATE_FORMAT(attendance.date,"%Y-%m") as date'),
                DB::raw("TIMESTAMPDIFF(hour,shift_timing.shift_from,shift_timing.shift_to) as timing"),
                DB::raw('concat(store.name,"-",store.store_type) as store_name'),
                DB::raw($query)
            )->groupBy('attendance.emp_id');

             if(!empty($serach_value)) {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('attendance.id','like',"%".$serach_value."%")
                    ->orwhere('attendance.status','like',"%".$serach_value."%")
                    ->orwhere('attendance.intime','like',"%".$serach_value."%")
                    ->orwhere('attendance.outtime','like',"%".$serach_value."%")
                    ->orwhere('attendance.totaltime','like',"%".$serach_value."%")
                    ->orwhere('attendance.emp_id','like',"%".$serach_value."%")
                    ->orwhere('store.name','like',"%".$serach_value."%");
                });
            }
            if(isset($user_name)) {
               $api_data->where(function($query) use ($user_name){
                        $query->where('attendance.emp_id',$user_name);
                    });               
            }
            if(isset($year) ) {
               $api_data->where(function($query) use ($year){
                        $query->whereYear('attendance.date', date($year));
                    });
            }
            if(isset($month) ) {
               $api_data->where(function($query) use ($month){
                        $query->whereMonth('attendance.date', date($month));
                    });
            }

            if(isset($request->input('order')[0]['column'])) {

                $data = [
                    'users.emp_id',
                    'users.name',
                    'attendance.status',
                    'attendance.date',
                    'store_name'
                ];

                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('users.emp_id','asc');
                $count = count( $api_data->get()->toArray());
                $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray(); 
                $array['recordsTotal'] = $count;
                $array['recordsFiltered'] = $count;
                $array['data'] = $api_data; 
                return json_encode($array);
    }

     public function penaltymaster_list() {

        $data = array('layout'=>'layouts.main');
        return view('admin.hr.penaltymaster_list', $data); 
    }

   public function penaltymaster_api(Request $request){
        
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
                   
      $api_data=PenaltyMaster::
                select(
                'penalty_master.id',
                'penalty_master.name',
                'penalty_master.amount'

            );
            if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('penalty_master.name','like',"%".$serach_value."%")
                    ->orwhere('penalty_master.amount','like',"%".$serach_value."%")
                    
                    ;
                });
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                'penalty_master.name',
                'penalty_master.amount'

                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('penalty_master.id','asc'); 

        $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

       public function validate_excel_format($row,$a,$column_name,$sheet="")
    {
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
        $user = HR_Leave::select('user_id as employee')->get()->toArray();
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
        $api_data = HR_Leave::leftJoin('users','hr__leave.user_id','users.id')
            ->whereMonth('hr__leave.start_date', date($month_name))
            ->select(
                DB::raw('concat(users.name," ",ifnull( users.middle_name," ")," ",ifnull( users.last_name," ")) as name'),
                'hr__leave.id',
                'users.emp_id as emp_id',
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
            $current_date = date('m/d/Y');
            $start_date = strtotime($month_effective); 
            $end_date = strtotime($current_date); 
            $diff = ($end_date - $start_date)/60/60/24; 

            $checkdata = Payroll::where('user_id','=',$user_id)->get()->first();

            $salary = $request->input('salary');
            $hra = $request->input('hra');
            $ta = $request->input('ta');
            $perf_allowance = $request->input('perf_allowance');
            $others = $request->input('others');

            if ($request->input('month_effective') && $diff >= 31) {
                $getMonth = CustomHelpers::UpdateAllMonthArrears($month_effective,$user_id,$salary,$hra,$ta,$perf_allowance,$others);
            }
            if ($checkdata && $diff >= 31) {
                $update = Payroll::where('user_id', $user_id)->update([
                    'basic_salary' => $checkdata['basic_salary']+$request->input('salary'),
                    'hra' => $checkdata['hra']+$request->input('hra'),
                    'ta' => $checkdata['ta']+$request->input('ta'),
                    'perf_allowance' => $checkdata['perf_allowance']+$request->input('perf_allowance'),
                    'others' => $checkdata['others']+$request->input('others'),
                ]);
                if ($update == null) {
                    DB::rollback();
                    return redirect('/admin/hr/user/salaryupdate/'.$id)->with('error','Some Unexpected Error occurred.'); 
                }
            }      
            if ($diff <= '-31') {
                 $data = [
                'basic_salary' => ($request->input('salary') != '') ? $request->input('salary'): 0,
                'user_id' => $user_id,
                'hra' => ($request->input('hra') != '') ? $request->input('hra'): 0,
                'ta' => ($request->input('ta') != '') ? $request->input('ta'): 0,
                'perf_allowance' => ($request->input('perf_allowance') != '') ? $request->input('perf_allowance'): 0,
                'others' => ($request->input('others') != '') ? $request->input('others'): 0,
                'effective_month' => date('Y-m-d',strtotime($month_effective)),
                'comment' => 'Arrears added for increment salary.',
                'added_insalary' => '1'
                ];
             } else{
                $data = [
                'basic_salary' => ($request->input('salary') != '') ? $request->input('salary'): 0,
                'user_id' => $user_id,
                'hra' => ($request->input('hra') != '') ? $request->input('hra'): 0,
                'ta' => ($request->input('ta') != '') ? $request->input('ta'): 0,
                'perf_allowance' => ($request->input('perf_allowance') != '') ? $request->input('perf_allowance'): 0,
                'others' => ($request->input('others') != '') ? $request->input('others'): 0,
                'effective_month' => date('Y-m-d',strtotime($month_effective)),
                'comment' => 'Arrears added for increment salary.',
                'added_insalary' => '0'
                ];
             }
            $customerdata = PayrollIncrement::insertGetId($data);
                if($customerdata == NULL){
                    DB::rollback();
                    return redirect('/admin/hr/payroll/increment')->with('error','Some Unexpected Error occurred.'); 
                }else{
                    DB::commit();
                     CustomHelpers::userActionLog($action='Payroll Increment Add',$customerdata,0);  
                  return redirect('/admin/hr/payroll/increment')->with("success","Payroll increment added successfully.");  
                }
                
        }  catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return redirect('/admin/hr/payroll/increment')->with('error','some error occurred'.$ex->getMessage())->withInput();
        }
    }

    public function user_resignation_DB(Request $request)
    {
        $empId = $request->input('empId');
        $status = $request->input('status');
        $comment = $request->input('comment');
        $todaydate =date('Y-m-d');
        $getcheck = Resignation::where('emp_id',$empId)->first();


        $check_user=Users::where('emp_id',$empId)->get()->first();
        
        if(!$check_user){
            return 'Id not exist';
    
        }else{
            $user_id=$check_user['id'];
            $users = Users::where('id',$user_id)
                         ->update([
                            'status' =>'2',
                         ]);
            if($users!=null){

                    $data = [
                        'emp_id' =>$empId,
                        'comment' =>$comment,
                        'status' =>$status,

                     ];
                    $completed = Resignation::insertGetId($data);
                     if($completed){
                        CustomHelpers::userActionLog($action='User Resign Add',$completed,0);

                      return 'success';   
                    }
                   else{
                    return 'error';
                   }
            }else{
                return 'error'; 
            }
        
        }  
    }

     public function user_inactive() {

        $attendance=Attendance::where('status','A')->get();

        $today = date('Y-m-d');
        $first_pre = date('Y-m-d',strtotime("-1 days"));
        $second_pre = date('Y-m-d',strtotime("-2 days"));
        $third_pre = date('Y-m-d',strtotime("-3 days"));
        $four_pre = date('Y-m-d',strtotime("-4 days"));
        $five_pre = date('Y-m-d',strtotime("-5 days"));
        $six_pre = date('Y-m-d',strtotime("-6 days"));

       foreach ($attendance as $checkattendance) {

            $emp_id= $checkattendance['emp_id'];
            $date= $checkattendance['date'];

           $checkleave=HR_Leave::where('employee',$emp_id)->orderBy('id', 'DESC')->first();

           if($checkleave){

               $date_from = $checkleave['start_date']; 
               $date_from = strtotime($date_from);  
               $date_to = $today;
               $date_to = strtotime($date_to); 

            for ($i=$date_from; $i<=$date_to; $i+=86400) {  
                 $checkdate = date("Y-m-d", $i);
                  
                if((($today != $checkdate) && ($first_pre != $checkdate) && ($second_pre != $checkdate) && ($third_pre != $checkdate) && ($four_pre != $checkdate) && ($five_pre != $checkdate) && ($six_pre != $checkdate)) ) {

                    $user = Users::where('emp_id',$emp_id)->where('user_type','!=','superadmin')->update(['status' => '0']);
                   
                   }
             }

           }else{

                 if(($today != $date) || ($first_pre != $date) || ($second_pre != $date) || ($third_pre != $date) || ($four_pre != $date) || ($five_pre != $date) || ($six_pre != $date) ) {

                    $user = Users::where('emp_id',$emp_id)->where('user_type','!=','superadmin')->update(['status' => '0']);
                }
           }           
        }
    }
    
    public function user_attendance_nop_DB(Request $request)
    {
        $empId = $request->input('empId');
        $type = $request->input('type');
        $comment = $request->input('comment');
        $user_id = $request->input('user_id');
        $date = $request->input('date');
        $time = $request->input('time');

        $todaydate =date('Y-m-d');
        $getcheck = Attendance::where('emp_id',$empId)->where('date',$date)->first();
        
        if($getcheck){

          $checknop=NOP::where('user_id',$user_id)->where('date',$date)->first();
          if(!$checknop){

               $to = Carbon::createFromFormat('Y-m-d',$date);
               $from = Carbon::createFromFormat('Y-m-d',$todaydate);
               $diff_in_days = $to->diffInDays($from);
               $time1= str_replace(" : ",":",$time);
                 
            if($diff_in_days<=3){   

               $data = [
                    'user_id' =>$user_id,
                    'emp_id' =>$empId,
                    'date' =>$date,
                    'type' =>$type,
                    'time' =>$time1,
                    'status'=>'Pending',
                    'comment'=>$comment,
                ];
                $nop=NOP::insertGetId($data);

              if($nop){
                  CustomHelpers::userActionLog($action='NOP Apply',$nop,0);
                  return 'success'; 
              }else{
                return 'error'; 
              }
            }
            else

            return 'Nop can apply within 3 days ';
          }else {
            if($type=='PunchIn'){
                   $type='NIP';
                 }else if($type=='PunchOut'){
                    $type='NOP';
                 }
             return ''.$type.' already applied ';
          }
        }else{

            return "User not exist";
        }    
    }
    public function user_reporting_attendance_nop_DB(Request $request)
    {
        $empId = $request->input('empId');
        $type = $request->input('type');
        $comment = $request->input('comment');
        $user_id = $request->input('user_id');
        $date = $request->input('date');
        $time = $request->input('time');

        $todaydate =date('Y-m-d');
        $getcheck = Attendance::where('emp_id',$empId)->where('date',$date)->first();
        
        if($getcheck){

          $checknop=NOP::where('user_id',$user_id)->where('date',$date)->first();
          if(!$checknop){

               $to = Carbon::createFromFormat('Y-m-d',$date);
               $from = Carbon::createFromFormat('Y-m-d',$todaydate);
               $diff_in_days = $to->diffInDays($from);
               $time1= str_replace(" : ",":",$time);
                 
            if($diff_in_days<=3){   

               $data = [
                    'user_id' =>$user_id,
                    'emp_id' =>$empId,
                    'date' =>$date,
                    'type' =>$type,
                    'time' =>$time1,
                    'status'=>'Pending',
                    'comment'=>$comment,
                ];
                $nop=NOP::insertGetId($data);

              if($nop){
                  CustomHelpers::userActionLog($action='NOP Apply',$nop,0);
                  return 'success'; 
              }else{
                return 'error'; 
              }
            }
            else

            return 'Nop can apply within 3 days ';
          }else {
            if($type=='PunchIn'){
                   $type='NIP';
                 }else if($type=='PunchOut'){
                    $type='NOP';
                 }
             return ''.$type.' already applied ';
          }
        }else{

            return "User not exist";
        }    
    }

    public function user_icard_issue(Request $request)
    {
        $userId = $request->input('userId');
        $getcheck = UserDetails::where('user_id',$userId)->first();
        
        if($getcheck){
            $checkid = IdCardIssue::where('user_id',$userId)->first();
             if(!$checkid){
                 $data = [
                    'user_id' =>$userId,
                    'status' =>1,
                ];
                $icard=IdCardIssue::insertGetId($data);
                 if($icard){

                  CustomHelpers::userActionLog($action='User ICard Issue Add',$icard,0);
                  return 'success'; 
              }else{
                return 'error'; 
              }
             }else{
                return 'ICard already issued';
             }
        }else{
            
            return 'User not Exist';
        }    
    }

    public function user_icard_issueagain(Request $request)
    {
        $userId = $request->input('userId');
        $fire_number = $request->input('fire_number');
        $date = $request->input('date');
        $getcheck = UserDetails::where('user_id',$userId)->first();
        
        if($getcheck){
              $data = [
                    'user_id' =>$userId,
                    'name' =>'IDCard Replacement',
                    'amount' =>100,
                    'type' =>'DR',
                    'created_by'=>Auth::id(),
                ];
                $nop=PayrollSalaryAdjustment::insertGetId($data);

              if($nop){
                $user = IdCardIssue::where('user_id',$userId)->update([
                    'fire_number' => $fire_number,
                    'fire_date' =>$date,
            ]);
                 CustomHelpers::userActionLog($action='User ICard Issue Again',$getcheck['id'],0);
                  return 'success'; 

              }else{
                return 'error'; 
              }
        }else{
            
            return 'User not Exist';
        }    
    }

    public function ResignedUser($id) {

        $date=strtotime(date('Y-m-d'));
        $month = date('m', $date);
        $advance=0;$arrears_amount=0;$bonus_amount=0;$cr_adjustment=0;$dr_adjustment=0;$dr_penalty=0;$advance_amt=0;
        $ot_amt=0;$dr_leave=0;$net_salary=0;$basic_salary=0;$overtime_hours=0;$total_deduction=0;$total_absent_amt=0;

        $customerdata =Users::where('id',$id)->get([DB::raw('concat(users.name," ",ifnull( users.middle_name," ")," ",ifnull( users.last_name," ")) as name'),'users.emp_id'])->first();

        $all_calculation=CustomHelpers::salaryCalculation($id,$month,$advance);
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
                        }if(array_key_exists("leave",$all_calculation)){
                            $dr_leave=$all_calculation['leave']['amount'];
                        }if(array_key_exists("advance",$all_calculation)){
                            $advance_amt=$all_calculation['advance']['amount'];
                        }if(array_key_exists("overtime",$all_calculation)){
                            $ot_amt=$all_calculation['overtime']['amount'];
                        }if(array_key_exists("overtime",$all_calculation)){
                            $overtime_hours=$all_calculation['overtime']['overtime_hours'];
                        }if(array_key_exists("user_payroll",$all_calculation)){
                             $net_salary=$all_calculation['user_payroll']['Net Salary'];
                             $basic_salary=$all_calculation['user_payroll']['Basic Salary'];
                        }
                    }else{
                        $message=$all_calculation['message'];
                        DB::rollback();
                        return redirect('/admin/hr/user/list/')->with('error','Error, '.$message.' for this User Id. '.$id.'')->withInput();
                      }
                }
             }
          }

        $getPayrollSalary = PayrollSalary::where('user_id',$id)->where('type','FinalPayment')->get()->first();
        if ($getPayrollSalary) {
             $payroll_id=$getPayrollSalary->id;
             $payroll_salary_details = PayrollSalaryDetail::where('payroll_salary_id',$payroll_id)->get();

           $data=array(
            'customerdata' => $customerdata,
            'payroll_salary'=>$getPayrollSalary,
            'payroll_salary_details'=>$payroll_salary_details,
            'net_salarys'=>$getPayrollSalary->net_salary,
            'basic_salary'=>$basic_salary,
            'layout' => 'layouts.main'
           );
            return view('admin.hr.full_final',$data);

        }else{
                   
           $salary = CustomHelpers::UserTotalSalary($id); 
           if(!empty($salary)){
              if(array_key_exists("salary",$salary)){
                   $total_salary=$salary['salary'];
              }if(array_key_exists("leave",$salary)){
                  $dr_leave=$salary['leave'];
              }
           }
          
           $total_deduction = round(abs($dr_leave)+$dr_adjustment+$dr_penalty+$advance_amt,0);
           $data = array(
            'id' => $id,
            'customerdata' => $customerdata,
            'total' => ($total_salary != ''?$total_salary:0),
            'arrears'=>$arrears_amount,
            'bonus'=>$bonus_amount,
            'cr_adjustment'=>$cr_adjustment,
            'dr_adjustment'=>$dr_adjustment,
            'dr_penalty'=>$dr_penalty,
            'dr_leave'=>$dr_leave,
            'advance_amt'=>$advance_amt,
            'ot_amt'=>$ot_amt,
            'overtime_hours'=>$overtime_hours,
            'net_salary'=>$net_salary,
            'basic_salary'=>$basic_salary,
            'total_deduction'=>$total_deduction,
            'layout'=>'layouts.main'
        );
        return view('admin.hr.full_final',$data);
      }
        
    }

    public function ResignedUser_DB(Request $request,$id) {
       try {
            $this->validate($request,[
                'ot'=>'required',
                'salary'  =>  'required',
                'bonus'  =>  'required',
                'arrears'  =>  'required',
                'leave'  =>  'required',
                'penalty'  =>  'required',
                'advance_amt'  =>  'required',
                'cr_adjustment'  =>  'required',
                'dr_adjustment'  =>  'required',

            ],[
                'ot.required'=> 'This field is required.',
                'salary.required_without_all'  =>  'This Field is required',
                'bonus.required_without_all'  =>  'This Field is required',
                'arrears.required_without_all'  =>  'This Field is required',
                'leave.required_without_all'  =>  'This Field is required',
                'penalty.required_without_all'  =>  'This Field is required',
                'advance_amt.required_without_all'  =>  'This Field is required',
                'cr_adjustment.required_without_all'  =>  'This Field is required',
                'dr_adjustment.required_without_all'  =>  'This Field is required',          
                    
            ]);
            DB::beginTransaction();
         
            $total_deduction = $request->input('total_deduction');
            $net_salary = $request->input('net_amount');
            $total_amount = $request->input('total_amount');

            $getUser = Users::where('id',$id)->get()->first();

            $getPayrollSalary = PayrollSalary::where('user_id',$id)->where('type','FinalPayment')->get()->first();
            $emp_id = $getUser['emp_id'];
            if ($getPayrollSalary) {
                return redirect('/admin/hr/user/resigned/'.$id)->with('error','F&F already added.'); 
            }
            $payrollSalary = PayrollSalary::insertGetId([
                'salary_month' => date('Y-m-d'),
                'type' => 'FinalPayment',
                'user_id' => $id,
                'total_deduction' => $total_deduction,
                'net_salary' => $net_salary,
                'total_pf' => 0,
                'actual_salary' => $total_amount,
                'payment_date' => date('Y-m-d')
            ]);
                if($payrollSalary == NULL){
                    DB::rollback();
                    return redirect('/admin/hr/user/resigned/'.$id)->with('error','Some Unexpected Error occurred.'); 
                }else{

                    if($request->input('salary')>0){
                       $salary = PayrollSalaryDetail::insertGetId([
                        'payroll_salary_id' => $payrollSalary,
                        'type' =>'CR',
                        'name' =>'Net Salary', 
                        'amount' =>$request->input('salary')
                       ]);  
                    }if($request->input('arrears')>0){
                      $arrears = PayrollSalaryDetail::insertGetId([
                        'payroll_salary_id' => $payrollSalary,
                        'type' =>'CR',
                        'name' =>'Arrears', 
                        'amount' =>$request->input('arrears')
                      ]);  
                      if($arrears!=null){
                        $update_arrears = PayrollBonus::where('user_id',$id)->where('status',0)->where('type','Arrears')->update([
                            'status' => '1'
                            ]);
                      }
                    }if($request->input('bonus')>0){
                       $bonus = PayrollSalaryDetail::insertGetId([
                        'payroll_salary_id' => $payrollSalary,
                        'type' =>'CR',
                        'name' =>'Bonus', 
                        'amount' =>$request->input('bonus')
                      ]);
                       if($bonus!=null){
                        $update_bonus = PayrollBonus::where('user_id',$id)->where('status',0)->where('type','Bonus')->update([
                            'status' => '1'
                            ]);
                       }
                    }if($request->input('ot')){
                      $overtime = PayrollSalaryDetail::insertGetId([
                        'payroll_salary_id' => $payrollSalary,
                        'type' =>'CR',
                        'name' =>'Overtime', 
                        'amount' =>$request->input('ot')
                      ]);
                        if($overtime!=null){
                             $update_overtime = OverTime::where('user_id',$id)->where('adjust_status',0)->where('status','Approved')->update([
                                 'adjust_status' => '1'
                                 ]);
                        }
                    }if($request->input('cr_adjustment')>0) {
                       $cr_adjustment = PayrollSalaryDetail::insertGetId([
                        'payroll_salary_id' => $payrollSalary,
                        'type' =>'CR',
                        'name' =>'CR Adjustment', 
                        'amount' =>$request->input('cr_adjustment')
                      ]); 
                       if($cr_adjustment!=null){
                         $updateCrAdjusment = PayrollSalaryAdjustment::where('user_id',$id)->where('status','0')->where('type','CR')->update([
                            'status' => '1'
                         ]);
                       }
                    }if($request->input('other_cr')>0){
                      $other_cr = PayrollSalaryDetail::insertGetId([
                        'payroll_salary_id' => $payrollSalary,
                        'type' =>'CR',
                        'name' =>'Other CR', 
                        'amount' =>$request->input('other_cr')
                      ]); 
                    }if($request->input('penalty')>0){
                        $penalty = PayrollSalaryDetail::insertGetId([
                        'payroll_salary_id' => $payrollSalary,
                        'type' =>'DR',
                        'name' =>'Penalty Deduction', 
                        'amount' =>$request->input('penalty')
                     ]);
                        if($penalty!=null){
                          $update_penalty = UserPenalty::where('user_id',$id)
                               ->where('status',0)->update([
                                    'status' => '1'
                                ]);
                        }
                    }if($request->input('leave')>0){
                       $leave = PayrollSalaryDetail::insertGetId([
                        'payroll_salary_id' => $payrollSalary,
                        'type' =>'DR',
                        'name' =>'Leave Deduction', 
                        'amount' =>$request->input('leave')
                     ]);
                     if($leave!=null){

                        $update_leave =HR_Leave::leftjoin('hr__leave_details','hr__leave_details.leave_id','hr__leave.id')->where('hr__leave.user_id',$id)->where('hr__leave_details.status','Approved')->where('hr__leave_details.is_adjusted',0)->update([
                                'is_adjusted' => '2'
                            ]);
                     }  
                    }if($request->input('advance_amt')>0){
                      $advance_amt = PayrollSalaryDetail::insertGetId([
                        'payroll_salary_id' => $payrollSalary,
                        'type' =>'DR',
                        'name' =>'Advance Amount Deduction', 
                        'amount' =>$request->input('advance_amt')
                     ]);
                    }if($request->input('dr_adjustment')>0){
                       $dr_adjustment = PayrollSalaryDetail::insertGetId([
                        'payroll_salary_id' => $payrollSalary,
                        'type' =>'DR',
                        'name' =>'DR Adjustment', 
                        'amount' =>$request->input('dr_adjustment')
                     ]);
                       if($dr_adjustment=!null){
                            $updateDrAdjusment = PayrollSalaryAdjustment::where('user_id',$id)->where('status','0')->where('type','DR')->update([
                                    'status' => '1'
                                ]);
                       }
                    }if($request->input('adjustment')>0){
                      $adjustment = PayrollSalaryDetail::insertGetId([
                        'payroll_salary_id' => $payrollSalary,
                        'type' =>'DR',
                        'name' =>'Adjustment', 
                        'amount' =>$request->input('adjustment')
                      ]);  
                    }

                    $update = Resignation::where('emp_id',$emp_id)->update([
                        'payroll_salary_id' => $payrollSalary
                    ]);
                    if ($update == null) {
                         DB::rollback();
                         return redirect('/admin/hr/user/resigned/'.$id)->with('error','Some Unexpected Error occurred.'); 
                    }else{
                        DB::commit();
                        CustomHelpers::userActionLog($action='F&F Add',$payrollSalary,0);
                        return redirect('/admin/hr/user/resigned/'.$id)->with("success", "F&F added successfully.");
                      }  
                }
                
        }  catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return redirect('/admin/hr/user/resigned/'.$id)->with('error','some error occurred'.$ex->getMessage())->withInput();
        }
    }

    public function overtime_list(){
        
        $store = Store::whereIn('id',CustomHelpers::user_store())
                    ->select('id',DB::raw('concat(store.name,"-",store.store_type) as name'))->get();
        $users= Users::select('emp_id as id','name','middle_name','last_name')->get();
        $overtime=OverTime::leftJoin('users as level1','level1.id','overtime.approve_rejected_by')
        ->leftJoin('users as level2','level2.id','overtime.approve_rejected_by_hr')->
        select('overtime.date','overtime.user_id','overtime.comment','overtime.status','overtime.status_hr_by','overtime.status_reporting_by','overtime.approve_rejected_by','level1.name as level1','level2.name as level2')->get();

        $hr=Users::where('role','HRDManager')->select('emp_id','id')->get();
        $data=array('users'=>$users,'store'=>$store,'overtime'=>$overtime,'hr'=>$hr,'layout'=>'layouts.main');
        return view('admin.hr.overtime_list', $data);
    }

    public function overtime_list_api(Request $request){
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
        
          $api_data= Attendance::leftJoin('users','attendance.emp_id','users.emp_id')
            ->leftJoin('store','attendance.store_id','store.id')
            ->leftJoin('user_details','users.id','user_details.user_id')
            ->leftJoin('shift_timing','user_details.shift_timing_id','shift_timing.id')
            ->where('attendance.status','P')
            ->where('user_details.shift_timing_id','!=','0')
            ->where(DB::raw('date_format(totaltime,"%H")'),'>',DB::raw("TIMESTAMPDIFF(hour,shift_timing.shift_from,shift_timing.shift_to)"))
            ->select(
                 DB::raw('concat(users.name," ",ifnull( users.middle_name," ")," ",ifnull( users.last_name," ")) as name'),
                 DB::raw("DATEDIFF(now(),attendance.date) as days"),
                 DB::raw("TIMESTAMPDIFF(hour,shift_timing.shift_from,shift_timing.shift_to) as timing"),
                 DB::raw('date_format(totaltime,"%H")  duration'),
                'users.emp_id',
                'users.id as user_id',
                'user_details.reporting_manager as reporting',
                'attendance.status',
                'attendance.intime',
                'attendance.outtime',
                'attendance.totaltime',
                'attendance.id',
                'attendance.date as attendance_date',
                 DB::raw("MonthName(attendance.date) date"),
                 DB::raw("DATEDIFF(now(),attendance.date) as days"),
                 DB::raw('concat(store.name,"-",store.store_type) as store_name')
            );

             if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('attendance.id','like',"%".$serach_value."%")
                    ->orwhere('users.name','like',"%".$serach_value."%")
                    ->orwhere('attendance.status','like',"%".$serach_value."%")
                    ->orwhere('attendance.intime','like',"%".$serach_value."%")
                    ->orwhere('attendance.outtime','like',"%".$serach_value."%")
                    ->orwhere('attendance.totaltime','like',"%".$serach_value."%")
                    ->orwhere('attendance.emp_id','like',"%".$serach_value."%")
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
                    'attendance.date',
                    'store_name',
                    'timing',
                    'duration',
                    'days'
                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else  
                $api_data->orderBy('attendance.id','desc');      

        $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
         $i = -1;
         foreach ($api_data as $datas) {
          $i++;
                  
                  
                $str = $datas['duration'];
                $time=$datas['timing'];
                $duration = ltrim($str, '0'); 
                $overtime=$str-$time;
                
                 $api_data[$i] = array_replace($datas,[
                       
                    'duration' => $duration,
                ]);

                $api_data[$i] = array_merge($datas,[
                       
                    'overtime' => $overtime
                ]);
                
          } 
          $j = -1;
         foreach ($api_data as $datass) {
          $j++;

            $duration = $datass['duration'];
            $time=$datass['timing'];
          

            if($time<0){
              unset($api_data[$j]);
                        
            }
       }
       $api_data = array_values($api_data);

        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 

        return json_encode($array);
    }

     public function overtime_approve(Request $request){

        $check=OverTime::where(['user_id'=>$request->input('user_id'),'date'=>$request->input('date')])->get()->first();
        if($check){

          if($check->status=='Pending'){

           if($request->input('status')){ 

            if($request->input('status')=='Approved'){                
                $arr=[
                'status_hr_by'=>$request->input('status'),
                'approve_rejected_by_hr'=>$request->input('hr_id'),
                'user_id'=>$request->input('user_id'),
                'status'=>$request->input('status'),
                'date'=>$request->input('date'),
                'overtime'=>$request->input('overtime'),
                'remark'=>$request->input('remark')
                ];


            }else if($request->input('status')=='Rejected'){
                     $arr=[
                    'status_hr_by'=>$request->input('status'),
                    'approve_rejected_by_hr'=>$request->input('hr_id'),
                    'user_id'=>$request->input('user_id'),
                    'date'=>$request->input('date'),
                    'overtime'=>$request->input('overtime'),
                    'status'=>$request->input('status'),
                    'remark'=>$request->input('remark')
                    ];
             
            }
           $msg='Successfully OT '.$request->input('status');
        }else if($request->input('status1')){
            if($request->input('status1')=='Approved'){
                $arr=[
                    'status_reporting_by'=>$request->input('status1'),
                    'approve_rejected_by'=>$request->input('repoting'),
                    'date'=>$request->input('date'),
                    'user_id'=>$request->input('user_id'),
                    'overtime'=>$request->input('overtime'),
                    'remark'=>$request->input('remark')
                    ];
              }else if($request->input('status1')=='Rejected'){
                 $arr=[
                    'status_reporting_by'=>$request->input('status1'),
                    'approve_rejected_by'=>$request->input('repoting'),
                    'date'=>$request->input('date'),
                    'user_id'=>$request->input('user_id'),
                    'overtime'=>$request->input('overtime'),
                    'status'=>$request->input('status1'),
                    'remark'=>$request->input('remark')
                    ];
              }
               $msg='Successfully OT '.$request->input('status1');
            }

              $overtime=OverTime::where(['user_id'=>$request->input('user_id'),'date'=>$request->input('date')])->update($arr);

             if($overtime){
              CustomHelpers::userActionLog($action='Overtime Approve',$check['id'],0);
              return redirect('/admin/hr/user/overtime/list')->with('success',$msg); 
             }
             else{
                DB::rollback();
                return back()->with('error','Some Error Occurred.')->withInput();
             }

            }else if($check->status=='Rejected'){

            return redirect('/admin/hr/user/overtime/list')->with('error','ID already Rejected');

            }else if($check->status=='Approved'){

            return redirect('/admin/hr/user/overtime/list')->with('error','ID already Approved');
                
            }
        }
        else{
                
        if($request->input('status')){ 

            if($request->input('status')=='Approved'){                
                $arr=[
                'status_hr_by'=>$request->input('status'),
                'approve_rejected_by_hr'=>$request->input('hr_id'),
                'user_id'=>$request->input('user_id'),
                'status'=>$request->input('status'),
                'date'=>$request->input('date'),
                'overtime'=>$request->input('overtime'),
                'remark'=>$request->input('remark')
                ];


            }else if($request->input('status')=='Rejected'){
                     $arr=[
                    'status_hr_by'=>$request->input('status'),
                    'approve_rejected_by_hr'=>$request->input('hr_id'),
                    'user_id'=>$request->input('user_id'),
                    'date'=>$request->input('date'),
                    'overtime'=>$request->input('overtime'),
                    'status'=>$request->input('status'),
                    'remark'=>$request->input('remark')
                    ];
             
            }
           $msg='Successfully OT '.$request->input('status');
        }else if($request->input('status1')){
            if($request->input('status1')=='Approved'){
                $arr=[
                    'status_reporting_by'=>$request->input('status1'),
                    'approve_rejected_by'=>$request->input('repoting'),
                    'date'=>$request->input('date'),
                    'user_id'=>$request->input('user_id'),
                    'overtime'=>$request->input('overtime'),
                    'remark'=>$request->input('remark')
                    ];
              }else if($request->input('status1')=='Rejected'){
                 $arr=[
                    'status_reporting_by'=>$request->input('status1'),
                    'approve_rejected_by'=>$request->input('repoting'),
                    'date'=>$request->input('date'),
                    'user_id'=>$request->input('user_id'),
                    'overtime'=>$request->input('overtime'),
                    'status'=>$request->input('status1'),
                    'remark'=>$request->input('remark')
                    ];
              }
               $msg='Successfully OT '.$request->input('status1');
            }

             $overtime = OverTime::insertGetId($arr);

             if($overtime){
             CustomHelpers::userActionLog($action='Overtime Add',$overtime,0);
              return redirect('/admin/hr/user/overtime/list')->with('success',$msg); 
             }
             else{
                DB::rollback();
                return back()->with('error','Some Error Occurred.')->withInput();
             }

     }
           
    }

    public function leave_balance_check(Request $request){

            $cl=0;
            $pl=0;
            $given_cl=0;
            $given_pl=0;
            $email='';
        $checkcl =LeaveBalance::where('user_id',$request->input('name'))->where('type','CL')->get()->first();
        $checkpl =LeaveBalance::where('user_id',$request->input('name'))->where('type','PL')->get()->first();

        $check_email =Users::where('id',$request->input('name'))->get()->first();    
        if($check_email){
          $email=$check_email['email'];
        }
        if($checkcl){
          $cl=$checkcl['balance'];
          $given_cl=$checkcl['given'];
        }if($checkpl){
          $pl=$checkpl['balance'];
          $given_pl=$checkpl['given'];
        }
                 
      $data=[
        'cl'=>$cl,
        'pl'=>$pl,
        'given_cl'=>$given_cl,
        'given_pl'=>$given_pl,
        'email'=>$email,
      ];
         return response()->json($data);
    }


    public function EmployeeLeaveBalance_list() {
        $users = Users::select('id','name','middle_name','last_name')->get();
        $data = [
                'users' => $users,
                'layout'=>'layouts.main'
            ];
        return view('admin.hr.employee_leave_balance_list', $data);
    }

    public function EmployeeLeaveBalancelist_api(Request $request) {
        $search = $request->input('search');
        $user_name = $request->input('user_name');
        $year = $request->input('year');
        $search = $request->input('search');
        $store_name = $request->input('store_name');
        $status = $request->input('status');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;

        $user = HR_Leave::select('user_id as employee')->get()->toArray();
        if ($year) {
             $year = $year;
        }else{
            $lyear =  date("Y",strtotime("+1 year"));
            $yr = date('Y');
            $year = $yr."-".$lyear;
        }
        
        $api_data = LeaveBalance::leftJoin('users','leave_balance.user_id','users.id')
            ->where('leave_balance.financial_year',$year)
            ->select(
                DB::raw('concat(users.name," ",ifnull( users.middle_name," ")," ",ifnull( users.last_name," ")) as name'),
                'leave_balance.id',
                'users.emp_id',
                'leave_balance.type', 
                'leave_balance.given',
                'leave_balance.balance',
                'leave_balance.paid',
                'leave_balance.financial_year',
                 DB::raw('IFNULL((select balance from leave_balance where user_id = users.id and type = "CL" and financial_year = "'.$year.'"),0) as CL'),
                 DB::raw('IFNULL((select balance from leave_balance where user_id = users.id and type = "PL" and financial_year = "'.$year.'" ),0) as PL')
            )->groupBy('leave_balance.user_id');
             if(!empty($serach_value)) {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('leave_balance.type','like',"%".$serach_value."%")
                    ->orwhere('leave_balance.given','like',"%".$serach_value."%")
                    ->orwhere('users.emp_id','like',"%".$serach_value."%")
                    ->orwhere('leave_balance.balance','like',"%".$serach_value."%")
                    ->orwhere('leave_balance.paid','like',"%".$serach_value."%")
                    ->orwhere('leave_balance.financial_year','like',"%".$serach_value."%")
                    ->orwhere('users.name','like',"%".$serach_value."%");
                });
            }
            if(isset($user_name)) {
               $api_data->where(function($query) use ($user_name){
                        $query->where('leave_balance.user_id',$user_name);
                    });               
            }
            if(isset($year) ) {
               $api_data->where(function($query) use ($year){
                        $query->where('leave_balance.financial_year', $year);
                    });
            }

            if(isset($request->input('order')[0]['column'])) {

                $data = [
                    'users.emp_id', 
                    'users.name',
                    'leave_balance.type',
                    'leave_balance.type',
                    'leave_balance.financial_year'
                ];

                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('leave_balance.id','desc');
                $count = count( $api_data->get()->toArray());
                $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
            $array['recordsTotal'] = $count;
            $array['recordsFiltered'] = $count;
            $array['data'] = $api_data; 
            return json_encode($array);
    }


  public function user_inactive_api(Request $request){

        $search = $request->input('search');
        $store_name = $request->input('store_name');
        $status = $request->input('status');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;

        $store = CustomHelpers::user_store();
        $api_data = Users::leftJoin('user_details','users.id','user_details.user_id')
                        ->leftJoin('states','users.state','states.id')
                        ->leftJoin('cities','users.city','cities.id')
                        ->leftJoin('id_card_issue','users.id','id_card_issue.user_id')
                        ->where('users.status',0)
                        ->select(
                            'users.id',
                            DB::raw('concat(users.name," ",ifnull( users.middle_name," ")," ",ifnull( users.last_name," ")) as name'),
                            'users.emp_id',
                            DB::raw("DATE_FORMAT(users.dob, '%d-%m-%Y') as dob"),
                            'users.relation_type',
                            'users.relation_name',
                            'states.name as sname',
                            'cities.city as city_name',
                            'users.alias_name',
                            'users.aadhar',
                            'users.pancard',
                            'users.address',
                            'users.state',
                            'users.city',
                            'users.status',
                            'id_card_issue.id as icard_issue',
                            DB::raw("DATE_FORMAT(users.doj, '%d-%m-%Y') as doj"),
                            'user_details.user_id'
                        );
            if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('users.name','like',"%".$serach_value."%")
                    ->orwhere('users.relation_type','like',"%".$serach_value."%")
                    ->orwhere('users.relation_name','like',"%".$serach_value."%")
                    ->orwhere('users.aadhar','like',"%".$serach_value."%")
                    ->orwhere('users.pancard','like',"%".$serach_value."%")
                    ->orwhere('users.alias_name','like',"%".$serach_value."%")
                    ->orwhere('states.name','like',"%".$serach_value."%")
                    ->orwhere('cities.city','like',"%".$serach_value."%")
                    ->orwhere('users.doj','like',"%".$serach_value."%")
                    ->orwhere('users.status','like',"%".$serach_value."%")
                    ->orwhere('users.emp_id','like',"%".$serach_value."%");
                });
            }

            if(!empty($status)){   
            
                $api_data->where(function($query) use ($status){
                        $query->whereIn('users.status',$status);
                    });
            }
            if(!empty($store_name))
            {
               $api_data->where(function($query) use ($store_name){
                        $query->where('users.store_id','like',"%".$store_name."%");
                    });
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                'users.emp_id',
                'users.name',
                'users.dob',
                'users.relation_type',
                'users.relation_name',
                'sname',
                'city_name',
                'users.status',
                'users.alias_name',
                'users.aadhar',
                'users.pancard',
                'users.doj'
                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else            
                $api_data->orderBy('users.emp_id','asc');

        $count = count($api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }
    public function user_resigned_api(Request $request){

        $search = $request->input('search');
        $store_name = $request->input('store_name');
        $status = $request->input('status');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
        

        $store = CustomHelpers::user_store();
        $api_data = Users::leftJoin('user_details','users.id','user_details.user_id')
                        ->leftJoin('states','users.state','states.id')
                        ->leftJoin('cities','users.city','cities.id')
                        ->leftJoin('id_card_issue','users.id','id_card_issue.user_id')
                        ->where('users.status',2)
                        ->select(
                            'users.id',
                            DB::raw('concat(users.name," ",ifnull( users.middle_name," ")," ",ifnull( users.last_name," ")) as name'),
                            'users.emp_id',
                            DB::raw("DATE_FORMAT(users.dob, '%d-%m-%Y') as dob"),
                            'users.relation_type',
                            'users.relation_name',
                            'states.name as sname',
                            'cities.city as city_name',
                            'users.alias_name',
                            'users.aadhar',
                            'users.pancard',
                            'users.address',
                            'users.state',
                            'users.city',
                            'users.status',
                            'id_card_issue.id as icard_issue',
                            DB::raw("DATE_FORMAT(users.doj, '%d-%m-%Y') as doj"),
                            'user_details.user_id'
                        );
            if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('users.name','like',"%".$serach_value."%")
                    ->orwhere('users.relation_type','like',"%".$serach_value."%")
                    ->orwhere('users.relation_name','like',"%".$serach_value."%")
                    ->orwhere('users.aadhar','like',"%".$serach_value."%")
                    ->orwhere('users.pancard','like',"%".$serach_value."%")
                    ->orwhere('users.alias_name','like',"%".$serach_value."%")
                    ->orwhere('states.name','like',"%".$serach_value."%")
                    ->orwhere('cities.city','like',"%".$serach_value."%")
                    ->orwhere('users.doj','like',"%".$serach_value."%")
                    ->orwhere('users.status','like',"%".$serach_value."%")
                    ->orwhere('users.emp_id','like',"%".$serach_value."%");
                });
            }

            if(!empty($status)){   
            
                $api_data->where(function($query) use ($status){
                        $query->whereIn('users.status',$status);
                    });
            }
            if(!empty($store_name))
            {
               $api_data->where(function($query) use ($store_name){
                        $query->where('users.store_id','like',"%".$store_name."%");
                    });
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                'users.emp_id',
                'users.name',
                'users.dob',
                'users.relation_type',
                'users.relation_name',
                'sname',
                'city_name',
                'users.status',
                'users.alias_name',
                'users.aadhar',
                'users.pancard',
                'users.doj'
                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else            
                $api_data->orderBy('users.emp_id','asc');

        $count = count($api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

    public function activeuser(){
        $data = array('layout'=>'layouts.main');
        return view('admin.hr.active_user_list', $data);
    }
    public function inactiveuser(){
        $data = array('layout'=>'layouts.main');
        return view('admin.hr.inactive_user_list', $data);
    }
    public function resigneduser_list(){
        $data = array('layout'=>'layouts.main');
        return view('admin.hr.resigned_user_list', $data);
    }

        public function BalancePl_list() {
        $users = Users::select('id','name','middle_name','last_name')->get();
        $data = [
                'users' => $users,
                'layout'=>'layouts.main'
            ];
        return view('admin.hr.pl_balance_list', $data);
    }

    public function BalancePllist_api(Request $request) {
        $user_name = $request->input('user_name');
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;

        $user = HR_Leave::select('user_id as employee')->get()->toArray();
       
        $api_data = LeaveBalance::leftJoin('users','leave_balance.user_id','users.id')
            ->leftJoin('hr__leave',function($join){
                $join->on('leave_balance.user_id','hr__leave.user_id')
                ->where('hr__leave.leave_status','Approved')
                ->where('hr__leave.type','PL');
            })
            ->where('leave_balance.type','PL')
            ->select(
                DB::raw('concat(users.name," ",ifnull( users.middle_name," ")," ",ifnull( users.last_name," ")) as name'),
                'leave_balance.id',
                'users.emp_id',
                'leave_balance.type', 
                'leave_balance.given',
                'leave_balance.balance',
                'leave_balance.financial_year',
                DB::raw('group_concat(hr__leave.id) as hr_id'),
                 // DB::raw('IFNULL((select count(id) from hr__leave_details where leave_id = hr__leave.id and status = "Approved" ),0) as paid'),
                 DB::raw('IFNULL((select sum(balance) from leave_balance where user_id = users.id and type = "PL" ),0) as PL')
            )->groupBy('leave_balance.user_id');

             if(!empty($serach_value)) {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('leave_balance.type','like',"%".$serach_value."%")
                    ->orwhere('leave_balance.given','like',"%".$serach_value."%")
                    ->orwhere('leave_balance.balance','like',"%".$serach_value."%")
                    ->orwhere('leave_balance.paid','like',"%".$serach_value."%")
                    ->orwhere('leave_balance.financial_year','like',"%".$serach_value."%")
                    ->orwhere('users.name','like',"%".$serach_value."%");
                });
            }
            if(isset($user_name)) {
               $api_data->where(function($query) use ($user_name){
                        $query->where('leave_balance.user_id',$user_name);
                    });               
            }

            if(isset($request->input('order')[0]['column'])) {

                $data = [
                    'users.emp_id', 
                    'users.name',
                    'leave_balance.type',
                    'leave_balance.type'
                ];

                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('leave_balance.id','desc');
                $count = count( $api_data->get()->toArray());
                $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
                foreach ($api_data as &$value) {
                  if ($value['hr_id']) {
                     $hr_id = explode(',', $value['hr_id']);
                     $paid = 0;
                     for ($i = 0; $i < count($hr_id); $i++) {
                        $getLeave = HRLeaveDetails::where('leave_id',$hr_id[$i])->where('status','Approved')->get();
                        $paid = $paid+count($getLeave);
                     }
                    $value['paid'] = $paid;
                  }else{
                    $value['paid'] = 0;
                  }
                }
            $array['recordsTotal'] = $count;
            $array['recordsFiltered'] = $count;
            $array['data'] = $api_data; 
            return json_encode($array);
    }

    public function leave_balance() {

        $data = [
            'layout' => 'layouts.main'
        ];
        
        return view('admin.hr.leave_balance',$data);
    }

     public function leave_balance_Db(Request $request){
        
        $allowed_extension = array('xls','xlsx','xlt','xltm','xltx','xlsm');
        $validator = Validator::make($request->all(),[    
            'leave_balance'  => 'required'
        ],[
            'leave_balance.required'    =>  'This field is required.'
        ]);
        $validator->sometimes('leave_balance', 'mimes:xls,xlsx,xlt,xltm,xltx,xlsm', function ($input) use($allowed_extension) {
            $extension="";
            if(isset($input->leave_balance))
            $extension = $input->leave_balance->getClientOriginalExtension();
            if(in_array($extension,$allowed_extension))
                return false;
            else
                return true;
        });
        if ($validator->fails()) {
            return redirect('admin/hr/user/leave/balance/import')
                        ->withErrors($validator)
                        ->withInput();
        }

       $redirect_to = '/admin/hr/user/leave/balance/import';

         try {
            DB::beginTransaction();

            if($request->file('leave_balance'))                
            {
                $emp_error = '';
                $path = $request->file('leave_balance');                
                $data = Excel::toArray(new Import(),$path);
                $date=date('Y');
                $year = substr($date, 0, 2);
                $date1=date_create(date("Y-m-d"));   

                if(count($data) > 0 && $data && isset($data[0][1])){

                     if(!isset($data[0][1][4]) || !isset($data[0][1][5]) || !isset($data[0][1][6])){
                        return back()->with('error','Please Upload Correct Excel File.');
                    }

                     $pre_pl_year = $data[0][1][4];
                     $cur_pl_year = $data[0][1][5];
                     $cur_cl_year = $data[0][1][6];
                    
                    $year_con=substr($pre_pl_year, -5);
                    $string= $year.$year_con;

                    $year_con1=substr($cur_pl_year, -5);
                    $string1= $year.$year_con1;

                    $year_con=substr($pre_pl_year, -5);
                    $string= $year.$year_con;

                    $previous_pl_year=substr_replace( $string,'20', 5, 0 ); 
                    $current_pl_year=substr_replace( $string1,'20', 5, 0 ); 
                    $financial_year = CustomHelpers::getFinancialYear($date1);
                     $row =1;
                  foreach($data[0] as $key => $value){
                            
                   if($key>=2 && !empty($value[1])){

                      $user=Users::where('emp_id',$value[0])->get()->first();

                        if(empty($value[0])) {

                            DB::rollback();
                            return back()->with('error','Error, Check your file should not be empty. At Row number '. $row.'')->withInput();
                        }if($value[4] != '0'){                           
                             if(empty($value[4])){

                                DB::rollback();
                                return back()->with('error','Error, Check your file should not be empty. At Row number '. $row.'')->withInput();
                            }
                        }if($value[5] != '0'){                           
                             if(empty($value[5])){

                                DB::rollback();
                                return back()->with('error','Error, Check your file should not be empty. At Row number '. $row.'')->withInput();
                            }
                        }if($value[6] != '0'){                           
                             if(empty($value[6])){

                                DB::rollback();
                                return back()->with('error','Error, Check your file should not be empty. At Row number '. $row.'')->withInput();
                            }
                        }

                      if ($user == null) {
                                DB::rollback();
                                return back()->with('error','Error, Employee number could not be find .'.$value[0].'')->withInput();
                        }

                       if($user){
                           $user_id=$user['id'];

                            $p_pl_year = LeaveBalance::where('user_id',$user_id)->where('type','PL')->where('financial_year',$previous_pl_year)->first();
                            $c_pl_year = LeaveBalance::where('user_id',$user_id)->where('type','PL')->where('financial_year',$current_pl_year)->first();
                            $c_cl_year = LeaveBalance::where('user_id',$user_id)->where('type','CL')->where('financial_year',$financial_year)->first();
                                
                                if(isset($p_pl_year) || isset($c_pl_year) || isset($c_cl_year)) {
                                    $emp_error = $emp_error.'But some employees Leave Balance is already made, Employee Number '.$value[0].'';
                                }else{
                                $insert_pre_pl_data = LeaveBalance::insertGetId(
                                        [
                                          'user_id' => $user_id,
                                          'type'    => 'PL',
                                          'given'   =>$value[4],
                                          'balance' =>$value[4],
                                          'paid'    =>0,
                                          'financial_year'=>$previous_pl_year
                                        ]);

                                $insert_cur_pl_data = LeaveBalance::insertGetId(
                                        [
                                          'user_id' => $user_id,
                                          'type'    => 'PL',
                                          'given'   =>$value[5],
                                          'balance' =>$value[5],
                                          'paid'    =>0,
                                          'financial_year'=>$current_pl_year
                                        ]);

                                $insert_cur_cl_data = LeaveBalance::insertGetId(
                                        [
                                          'user_id' => $user_id,
                                          'type'    => 'CL',
                                          'given'   =>$value[6],
                                          'balance' =>$value[6],
                                          'paid'    =>0,
                                          'financial_year'=>$financial_year
                                        ]);
                                }
                                 
                            }
                         }
                         $row++;
                      }
                }
                else{
                    DB::rollback();
                    return back()->with('error','Error, Check your file should not be empty.');
                }
            }
             else{
                 DB::rollback();
                return back()->with('error', 'Some Error Occurred Please Try again.');
            }
           
        }  catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return redirect($redirect_to)->with('error','some error occurred'.$ex->getMessage())->withInput();
        }
         DB::commit();
        if(empty($emp_error)) {
            CustomHelpers::userActionLog($action='Leave Balance Import',0,0);  
            return redirect($redirect_to)->with('success','Leave Balance added successfully');
        }else{
            CustomHelpers::userActionLog($action='Leave Balance Import',0,0);  
            return redirect($redirect_to)->with('success',' Leave Balance added successfully,'.$emp_error);
        }
        CustomHelpers::userActionLog($action='Leave Balance Import',0,0);  
        return redirect($redirect_to)->with('success','Leave Balance added successfully, but some employee number could not be find, such as :-'.$emp_error);
    }

     public function dashboard(){

         $date = date('Y-m-d');
         $next_date = date('Y-m-d',strtotime("1 days", strtotime($date)));

         $nop= NOP::leftJoin('users','nop.user_id','users.id')
             ->leftJoin('user_details','nop.user_id','user_details.user_id')
            ->select(
                'nop.user_id',
                 DB::raw('concat(users.name," ",ifnull( users.middle_name," ")," ",ifnull( users.last_name," ")) as name'),
                'users.emp_id',
                'nop.date',
                'nop.time',
                'nop.type',
                'nop.status',
                'nop.id'

            )->orderBy('nop.id', 'DESC')->limit(5)->get();

        $attendance= Attendance::leftJoin('users','attendance.emp_id','users.emp_id')
            ->leftJoin('store','attendance.store_id','store.id')
            ->where('attendance.status','A')
            ->where('attendance.date',$date)
            ->select(
                 DB::raw('concat(users.name," ",ifnull( users.middle_name," ")," ",ifnull( users.last_name," ")) as name'),
                'users.emp_id',
                'attendance.status',
                DB::raw('concat(store.name,"-",store.store_type) as store_name')
               
            )->orderBy('attendance.id', 'DESC')->limit(3)->get();

        $leave_data=HRLeaveDetails::leftJoin('hr__leave','hr__leave.id','hr__leave_details.leave_id')->leftJoin('users','users.id','hr__leave.user_id')
            ->whereBetween('hr__leave_details.date', array($date,$next_date))
            ->select(
                'hr__leave.id',
                'users.emp_id',
                DB::raw('concat(users.name," ",ifnull( users.middle_name," ")," ",ifnull( users.last_name," ")) as name'),
                'hr__leave_details.date',
                'hr__leave.leave_status'
            )->orderBy('hr__leave_details.date', 'ASC')->limit(5)->get();

         $fuel_data = FuelStockModel::leftJoin('store','store.id','fuel_stock.store_id')
            ->select(
                'fuel_stock.id',
                'fuel_stock.quantity',
                'fuel_stock.fuel_type',
                DB::raw('concat(store.name,"-",store.store_type) as name')
            )->orderBy('fuel_stock.id', 'DESC')->limit(4)->get();

        $events = [];
        $leaves = [];
        $data = HolidayFestival::select('start_date','end_date','name','id')->get();
        $leave=HR_Leave::leftJoin('users','users.id','hr__leave.user_id')->select('hr__leave.start_date','hr__leave.end_date','hr__leave.reason','hr__leave.id',DB::raw('concat(users.name," ",ifnull( users.middle_name," ")," ",ifnull( users.last_name," ")) as name'),'hr__leave.leave_status')->get();
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

        return view('admin.hr.hr_dashboard',
            ['layout' => 'layouts.main',
            'nop'=>$nop,
            'attendance'=>$attendance,
            'leave'=>$leave_data,
            'fuel'=>$fuel_data,
            'calendar'=>$calendar
        ]);
    }

    public function user_rejoin_DB(Request $request)
    {
        $user_id = $request->input('user_id');
        $mistake = $request->input('mistake');
        $comment = $request->input('comment');
        $date =date('Y-m-d');

        $check_user=Users::where('id',$user_id)->get()->first();
        
        if(!$check_user){
            return 'Id not exist';
    
        }else{
            $empId=$check_user['emp_id'];

            if($check_user['status']==2){

                $users = Users::where('id',$user_id)
                             ->update([
                                'status' =>'1',
                             ]);
                    if($users!=null){

                        if($mistake=='true'){
                                
                            $resigned_data = Resignation::where('emp_id',$empId)->orderBy("id", "desc")->take(1)->delete();
                            if($resigned_data){
                                return 'success'; 
                            }

                        }else if($mistake=='false'){

                                $data = [
                                    'user_id' =>$user_id,
                                    'comment' =>$comment,
                                    'date' =>$date
                                ];

                            $rejoin=Rejoin::insertGetId($data);
                            if($rejoin){
                               CustomHelpers::userActionLog($action='User Rejoin Add',$rejoin,0); 
                               return 'success'; 
                            }
                        }

                    }else{
                        return 'error'; 
                    }
        }else{
            return 'user not resigned'; 
        }
        }
  
    }

    public function OfficeDuty() {
        $store = Store::whereIn('id',CustomHelpers::user_store())->whereIn('store_type',['Showroom','Service','Warehouse'])
                    ->select('id',DB::raw('concat(store.name,"-",store.store_type) as name'))->get();
        $employee = Users::where('status',1)->select('emp_id','id',DB::raw('concat(name," ",ifnull( middle_name," ")," ",ifnull(last_name," ")) as name'))->get();
       $data = [
            'store' => $store,
            'employee' => $employee,
            'layout' => 'layouts.main'
        ];
        
        return view('admin.hr.office_duty',$data);
    }

    public function OfficeDuty_DB(Request $request) {
         try {
            $this->validate($request,[
                'store'=>'required',
                'employee'=>'required',
                'punch'=>'required',
                'start_time'=>'required'

            ],[
                'store.required'=> 'This is required.',
                'employee.required'=> 'This is required.',
                'punch.required'=> 'This is required.',
                'start_time.required'=> 'This is required.',
            ]);
            DB::beginTransaction();
            $employee = $request->input('employee');
            $store = $request->input('store');
            $punch = $request->input('punch');
            $start_time  = str_replace(' ','',$request->input('start_time'));
            $date = date('Y-m-d');
            $getPunch = OfficeDuty::where('emp_id',$employee)->where('punch_date',$date)->where('store_id',$store)->where('type',$punch)->first();
            if ($getPunch) {
                return redirect('/admin/hr/office/duty/')->with('error','This employee already punch in /punch out.');
            } 
            $getShift = Users::leftjoin('user_details','user_details.user_id','users.id')
                            ->leftjoin('shift_timing','user_details.shift_timing_id','shift_timing.id')
                            ->where('users.emp_id',$employee)
                            ->select('shift_timing.id','shift_timing.shift_from','shift_timing.shift_to',DB::raw("TIMESTAMPDIFF(hour,shift_timing.shift_from,shift_timing.shift_to) as total_shift"))
                            ->first();
            if (empty($getShift['id'])) {
                return redirect('/admin/hr/office/duty/')->with('error','Shift not created for this employee.');
            }         

            if ($punch == 'PunchIn') {
                $inTime = $start_time;
                $outTime = null;
                $status = 'P';
                $lateCal = CustomHelpers::LateEarlyDuration($employee,$store,$date,$inTime,$outTime,$status,$punch);
                if ($lateCal > 0) {
                    $insert = OfficeDuty::insertGetId([ 
                    'emp_id' => $employee,
                    'store_id' => $store,
                    'type' => $punch,
                    'punch' => $start_time,
                    'punch_date' => $date,
                    'created_by' => Auth::id()
                ]);
                    if($insert == NULL) {
                        DB::rollback();
                        return redirect('/admin/hr/office/duty/')->with('error','Some Unexpected Error occurred.');
                    }else{
                        DB::commit();
                        CustomHelpers::userActionLog($action = 'Office Duty Add',$insert,0);
                        return redirect('/admin/hr/office/duty/')->with('success','Punch In successfully.');
                    }
                }else{
                    DB::rollback();
                    return redirect('/admin/hr/office/duty/')->with('error','Some Unexpected Error occurred.');
                }
                 
                
            }else{
                $inTime = null;
                $outTime = $start_time;
                $status = 'P';
                $lateCal = CustomHelpers::LateEarlyDuration($employee,$store,$date,$inTime,$outTime,$status,$punch);
                if ($lateCal > 0) {
                    $insert = OfficeDuty::insertGetId([ 
                    'emp_id' => $employee,
                    'store_id' => $store,
                    'type' => $punch,
                    'punch' => $start_time,
                    'punch_date' => $date,
                    'created_by' => Auth::id()
                ]);
                   if($insert == NULL) {
                        DB::rollback();
                        return redirect('/admin/hr/office/duty/')->with('error','Some Unexpected Error occurred.');
                    }else{
                        DB::commit();
                        CustomHelpers::userActionLog($action = 'Office Duty Add',$insert,0);
                        return redirect('/admin/hr/office/duty/')->with('success','Punch In successfully.');
                    }
                }else{
                    DB::rollback();
                    return redirect('/admin/hr/office/duty/')->with('error','Some Unexpected Error occurred.');
                }

            }

        }  catch(\Illuminate\Database\QueryException $ex) {
            return redirect('/admin/hr/office/duty/')->with('error','some error occurred'.$ex->getMessage())->withInput();
        }
    }

    public function OfficeDutyList() {
        $store = Store::whereIn('id',CustomHelpers::user_store())->whereIn('store_type',['Showroom','Service','Warehouse'])
                    ->select('id',DB::raw('concat(store.name,"-",store.store_type) as name'))->get();
       $data = [
            'store' => $store,
            'layout' => 'layouts.main'
        ];
        return view('admin.hr.office_duty_list',$data);
    }

    public function OfficeDutyList_api(Request $request) {
        $search = $request->input('search');
        $store_name=$request->input('store_name');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 100 : $limit ;
        $store = CustomHelpers::user_store();
        
        $user = Auth::user();
        $user_type = $user['user_type'];
        $role = $user['role'];
        if($user_type=="superadmin" || $role=='HRDManager'){
          $api_data= OfficeDuty::leftJoin('users','office_duty.emp_id','users.emp_id')
            ->leftJoin('store','office_duty.store_id','store.id')
            ->select(
                DB::raw('concat(users.name," ",ifnull( users.middle_name," ")," ",ifnull( users.last_name," ")) as name'),
                'users.emp_id',
                'users.id as user_id',
                'office_duty.type',
                'office_duty.punch',
                'office_duty.punch_date',
                DB::raw('concat(store.name,"-",store.store_type) as store_name')
            );

        }else{
            $api_data= OfficeDuty::leftJoin('users','office_duty.emp_id','users.emp_id')
            ->leftJoin('store','office_duty.store_id','store.id')
            ->where('office_duty.emp_id',Auth::user()->emp_id)
           ->select(
                DB::raw('concat(users.name," ",ifnull( users.middle_name," ")," ",ifnull( users.last_name," ")) as name'),
                'users.emp_id',
                'users.id as user_id',
                'office_duty.type',
                'office_duty.punch',
                'office_duty.punch_date',
                DB::raw('concat(store.name,"-",store.store_type) as store_name')
            );
        }

             if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('office_duty.id','like',"%".$serach_value."%")
                    ->orwhere('users.emp_id','like',"%".$serach_value."%")
                    ->orwhere('users.name','like',"%".$serach_value."%")
                    ->orwhere('office_duty.type','like',"%".$serach_value."%")
                    ->orwhere('office_duty.punch','like',"%".$serach_value."%")
                    ->orwhere('office_duty.punch_date','like',"%".$serach_value."%")
                    ->orwhere('store.name','like',"%".$serach_value."%")
                    
                    ;
                });
            }
             if(!empty($store_name)) {
               $api_data->where(function($query) use ($store_name){
                        $query->where('office_duty.store_id','=',$store_name);
                    });
            }

            
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                'office_duty.emp_id',
                'users.name',
                'office_duty.type',
                'office_duty.punch',
                'office_duty.punch_date',
                'store.name'
                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else 
                $api_data->orderBy('office_duty.id','desc');      

        $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }
}