<?php

namespace App\Http\Controllers\admin;

use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Model\Hsrp;
use App\Model\Users;
use \App\Model\Customers;
use App\Model\Userlog;
use \App\Model\Store;
use App\Model\Challan;
use App\Model\PaymentRequest;
use App\Model\RtoModel;
use \App\Model\RtoSummary;
use App\Model\ChallanDetails;
use \App\Custom\CustomHelpers;
use Illuminate\Support\Facades\DB;
use Auth;


class HsrpController extends Controller
{
    public function hsrp_request_list(){
        $data = array(
            'layout'=>'layouts.main'
        );
        return view('admin.hsrp.hsrp_request_list',$data);
    }  

    public function hsrp_request_list_api(Request $request){
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
        $api_data = Hsrp::leftjoin('store','hsrp.store_id','store.id')
                    ->leftJoin('customer',function($join){
                            $join->on('hsrp.customer_id','=','customer.id');
                        })
                    ->select(
                        'hsrp.id',
                        'hsrp.name',
                        'hsrp.mobile',
                        'customer.name as customer_name',
                        'customer.mobile as customer_mobile',
                        'hsrp.frame',
                        'hsrp.engine',
                        'hsrp.fueltype',
                        'hsrp.registration',
                        'hsrp.type',
                        'hsrp.registration_date',
                        'hsrp.vechicle_type',
                        'hsrp.oem',
                        'hsrp.verified',
                        DB::raw('concat(store.name,"-",store.store_type) as store_name')
                    );
        if(!empty($serach_value))
        {
            $api_data->where(function($query) use ($serach_value){
                $query->where('hsrp.name','LIKE',"%".$serach_value."%")
                ->orWhere('hsrp.mobile','LIKE',"%".$serach_value."%")
                ->orWhere('hsrp.frame','LIKE',"%".$serach_value."%")
                ->orWhere('hsrp.engine','LIKE',"%".$serach_value."%")
                ->orWhere('hsrp.fueltype','LIKE',"%".$serach_value."%")
                ->orWhere('hsrp.registration','LIKE',"%".$serach_value."%")
                ->orWhere('hsrp.type','LIKE',"%".$serach_value."%")
                ->orWhere('hsrp.registration_date','LIKE',"%".$serach_value."%")
                ->orWhere('hsrp.vechicle_type','LIKE',"%".$serach_value."%")
                ->orWhere('hsrp.oem','LIKE',"%".$serach_value."%")
                ;
              });
     
        }
        
        if(isset($request->input('order')[0]['column']))
        {
            $data = [
            'hsrp.name',
            'hsrp.mobile',
            'hsrp.frame',
            'hsrp.engine',
            'hsrp.fueltype',
            'hsrp.registration',
            'hsrp.type',
            'hsrp.registration_date',
            'hsrp.vechicle_type',
            'hsrp.oem',
            'hsrp.verified'
                ];
            $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
            $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
        }
        else
            $api_data->orderBy('hsrp.id','desc');
            $count = count($api_data->get()->toArray());
            $api_data = $api_data->offset($offset)->limit($limit);
            $api_data = $api_data->get();
            $array['recordsTotal'] = $count;
            $array['recordsFiltered'] = $count;
            $array['data'] = $api_data;
            return json_encode($array);
    } 

    public function Hsrp_request() {
        $lastYear = date("Y-m-d", strtotime("-15 years"));
        $store = Store::whereIn('id',CustomHelpers::user_store())->whereIn('store_type',['Showroom','Service','Warehouse'])
                    ->select('id',DB::raw('concat(store.name,"-",store.store_type) as name'))->get();
        $customer  = Customers::orderBy('id','desc')->get();
        $data = [
            'lastdate'=>$lastYear,
            'store' => $store,
            'customer' => $customer,
            'layout'=>'layouts.main'
        ];
        return view('admin.hsrp.hsrp_request',$data);
    }

    public function Hsrp_request_DB(Request $request) {

        $validator = Validator::make($request->all(),[
           'name'=>'required_without_all:customer',
           'mobile'=>'required_without_all:customer',
           'frame' =>  'required',
           'fueltype' => 'required',
           'registration' => 'required',
           'type' => 'required',
           'type' => 'required',
           'date' => 'required',
           'vechicle_type' => 'required',
           'oem' => 'required',
           'store_name' => 'required',
           'customer' => 'required_without_all:name|required_without_all:mobile',
        ],
        [
            'name.required'=> 'This is required.', 
            'mobile.required'=> 'This is required.',          
            'frame.required'=> 'This is required.',                 
            'fueltype.required'=> 'This is required.',          
            'registration.required'=> 'This is required.',          
            'date.required'=> 'This is required.',          
            'vechicle_type.required'=> 'This is required.',          
            'oem.required'=> 'This is required.', 
            'store_name.required'=> 'This is required.',          
            'customer.required'=> 'This is required.',          
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

            DB::beginTransaction();
            $customer = $request->input('customer');
            $name = $request->input('name');
            $mobile = $request->input('mobile');
            $frame = $request->input('frame');
            $registration = $request->input('registration');
            if (!empty($customer)) {
                $check = HSRP::where('customer_id',$customer)->where('frame',$frame)->where('registration',$registration)->where('status','Pending')->first();
                if ($check) {
                    DB::rollback();
                    return back()->with('error','This request is already send.');
                }
            }

            if (!empty($mobile)) {
                $check = HSRP::where('mobile',$mobile)->where('frame',$frame)->where('registration',$registration)->where('status','Pending')->first();
                if ($check) {
                    DB::rollback();
                    return back()->with('error','This request is already send.');
                }
            }
            if (!empty($request->input('customer'))) {
                $customer_id = $request->input('customer');
            }else{
                $customer_id = 0;
            }

             $data = Hsrp::insertGetId([ 
                    'name'=>$request->input('name'),
                    'customer_id'=> $customer_id,
                    'mobile'=>$request->input('mobile'),
                    'frame'=>$request->input('frame'),
                    'engine'=>$request->input('engine'),
                    'fueltype'=>$request->input('fueltype'),
                    'registration'=>$request->input('registration'),
                    'type'=>$request->input('type'),
                    'registration_date'=>$request->input('date'),
                    'vechicle_type'=>$request->input('vechicle_type'),
                    'oem'=>$request->input('oem'),
                    'store_id' => $request->input('store_name')
                ]);

            if($data==NULL){
                DB::rollback();
                return redirect('/admin/hsrp/request/')->with('error','Some Unexpected Error occurred.');

            }else{
                /* Add action Log */
                CustomHelpers::userActionLog($action='Hsrp Request',$data,$customer_id);
                DB::commit();
                return redirect('/admin/hsrp/challan/details/'.$data)->with('success','HSRP Request sent Successfully.'); 

            }
            
        }
        catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
           return redirect('/admin/hsrp/request/')->with('error','some error occurred'.$ex->getMessage())->withInput();
        }

    } 

    public function HsrpChallanDetails($id) {
        $hsrp = HSRP::where('id',$id)->first();
        if ($hsrp['verified'] == '1') {
            return redirect('/admin/hsrp/request/list')->with('error','HSRP already completed.');
        }
        $charge = CustomHelpers::HsrpServiceCharge();
        $store = Store::whereIn('id',CustomHelpers::user_store())->whereIn('store_type',['Showroom','Service','Warehouse'])
                    ->select('id',DB::raw('concat(store.name,"-",store.store_type) as name'))->get();
        $challan = Challan::where('hsrp_id',$id)->first();
        $detail = ChallanDetails::leftjoin('challan','challan.id','challan_details.challan_id')->where('challan.hsrp_id',$id)->where('challan_details.deleted_at',null)->select('challan_details.*')->get();
      $data = [
            'store' => $store,
            'id' => $id,
            'hsrp' => ($hsrp ? $hsrp : ''),
            'challan' => ($challan ? $challan : ''),
            'detail' => ($detail ? $detail : ''),
            'service_charge' => $charge['service_charge'],
            'with_combo' => $charge['with_combo'],
            'without_combo' => $charge['without_combo'],
            'layout'=>'layouts.main'
        ];
        return view('admin.hsrp.hsrp_detail',$data);
    }

    public function HsrpChallanDetails_DB(Request $request) {
        $id = $request->input('id');
        $getChallan = Challan::where('hsrp_id',$id)->first();
         if ($getChallan) {
                $reqData[] = $request->all();
                $data = $this->UpdateChallanDetail($reqData,$getChallan['id'],$id);
                if ($data == 1) {
                    return redirect('/admin/hsrp/challan/details/'.$id)->with('success','HSRP Challan details updated Successfully.');
                }else{
                    return redirect('/admin/hsrp/challan/details/'.$id)->with('error','Something went Wrong.')->withInput();
                }
         }
         $validator = Validator::make($request->all(),[
           'type'=>'required',
           'challan.*' => 'required_if:typechallan,yes|unique:challan_details,challan_number',
           'amount.*' => 'required_if:typechallan,yes',
           'plate_number' => 'required',
        ],
        [
            'type.required'=> 'This is required.',                       
            'challan.*.required'=> 'This is required.',          
            'amount.*.required'=> 'This is required.',       
            'plate_number.required'=> 'This is required.',        
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        } 
        try{
            DB::beginTransaction();
            $id = $request->input('id');
            $type = $request->input('type');
            $challan = $request->input('challan');
            $amount = $request->input('amount');
            $plate_number = $request->input('plate_number');
            $fitment_location = $request->input('store');
            $total_amount = $request->input('total_amount');
            $typechallan = $request->input('typechallan');
            $getHsrp = HSRP::where('id',$id)->first();
            if ($getHsrp['customer_id'] != 0) {
                $customer_id = $getHsrp['customer_id'];
            }else{
                $customer_id = 0;
            }
            if ($getHsrp['verified'] == '1') {
                return redirect('/admin/hsrp/request/list')->with('error','HSRP already completed.');
            }
            if ($plate_number == 'yes') {
                $combo = '2';
            }else{
                $combo = '1';
            }
            $challan_data = Challan::insertGetId([
                    'hsrp_id' => $id,
                    'total_amount' => $total_amount,
                    'store_id' => $getHsrp['store_id'],
                    'charge' => $combo,
                    'customer_id' => $customer_id,
                    'name' => $getHsrp['name'],
                    'mobile' => $getHsrp['mobile'],
                    'registration_no'=>$getHsrp['registration']
            ]);
            if(!$challan_data){
                DB::rollback();
                return back()->with('error','Something Went Wrong')->withInput();
            }
            if ($typechallan == 'yes') {
             if(count($challan)>0){
                $challan_count = 0;
                 foreach($challan as $key => $value) {
                     $total_amount = $total_amount+$amount[$challan_count];
                     // challandetail table insert
                        $challan_detail = ChallanDetails::insertGetId([
                                'challan_id' => $challan_data,
                                'challan_number'=>$challan[$challan_count],
                                'amount' =>$amount[$challan_count]
                            ]);

                        if(!$challan_detail){
                                DB::rollback();
                                return back()->with('error','Error, Something Went Wrong')->withInput();
                            }
                    $challan_count++;
                 }
             }
         }
            $update = Hsrp::where('id',$id)->update([
                'hsrp_type' => $type,
                'fitment_location' => $fitment_location
            ]);
            if ($update == null) {
                DB::rollback();
               return back()->with('error','Error, Something Went Wrong')->withInput();
            }else{

                 /* Add action Log */
                CustomHelpers::userActionLog($action='Add hsrp challan details ',$id,$customer_id);
                DB::commit();
                return redirect('/admin/hsrp/status/verify/'.$id)->with("success",'Submit Successfully');
            }

          }catch (\Illuminate\Database\QueryException $e) {
            DB::rollback();
            return back()->with('error','Something Went Wrong'.$e)->withInput();
        }
        
    }

    function UpdateChallanDetail($request,$challan_id,$hsrp_id){
        $getHsrp = HSRP::where('id',$hsrp_id)->first();
        if ($getHsrp['customer_id'] != 0) {
            $customer_id = $getHsrp['customer_id'];
        }else{
            $customer_id = 0;
        }
        $req = $request[0];
        $typechallan = $req['typechallan'];
        $challan = $req['challan'];
        $amount = $req['amount'];
        $status = $req['status'];
        $total_amount = $req['total_amount'];
        
        DB::beginTransaction();
        if ($typechallan == 'no') {
             $delete = tap(ChallanDetails::where('challan_id',$challan_id))->delete();
             if ($delete == null) {
                DB::rollback();
                return false;
             }else{
                /* Add action Log */
                CustomHelpers::userActionLog($action='Update hsrp challan details ',$hsrp_id,$customer_id);
                DB::commit();
                return true;
             }
        }else{
            foreach($challan as $key => $value) {
                $getDetails = ChallanDetails::where('challan_id',$challan_id)->where('challan_number',$challan[$key])->where('deleted_at',null)->first();
                if ($challan[$key] == $getDetails['challan_number']) {
                        $UpdateData = [
                            'amount' => $amount[$key],
                            'status' => $status[$key]
                        ];
                    $update = tap(ChallanDetails::where('challan_id',$challan_id)->where('challan_number',$challan[$key])->where('deleted_at',null))->update($UpdateData)->first();
                    if($update == null){
                        DB::rollback();
                        return false;
                    } 
                }else{
                     $insert = ChallanDetails::insertGetId([
                            'challan_id' => $challan_id,
                            'challan_number' => $challan[$key],
                            'amount' => $amount[$key]
                        ]);

                    if($insert == null){
                        DB::rollback();
                        return false;
                    } 
                }
            }
            $challanUpdate = tap(Challan::where('hsrp_id',$hsrp_id)->where('id',$challan_id))->update([
                'total_amount' => $total_amount
            ]);
            if ($challanUpdate == null) {
                DB::rollback();
                return false;
            }else{
                /* Add action Log */
                    CustomHelpers::userActionLog($action='Update hsrp challan details ',$hsrp_id,$customer_id);
                DB::commit();
                return true;
            }
        }
    }

    public function HsrpVerification($id) {
        $hsrp = HSRP::where('id',$id)->first();
        if ($hsrp['verified'] == '1') {
            return redirect('/admin/hsrp/request/list')->with('error','HSRP already completed.');
        }
        $HsrpChallan = Challan::where('hsrp_id',$id)->first();
        if (empty($HsrpChallan)) {
            return redirect('/admin/hsrp/challan/details/'.$id)->with('error','Please firstly create hsrp challan details.');
        }
        $charge = CustomHelpers::HsrpServiceCharge();
        $challan = Challan::leftjoin('hsrp','hsrp.id','challan.hsrp_id')
                    ->leftjoin('store as store','store.id','hsrp.store_id')
                    ->leftjoin('store as fitment','fitment.id','challan.store_id')
                    ->leftjoin('customer','customer.id','hsrp.customer_id')
                    ->where('hsrp.id',$id)
                    ->select(
                        'hsrp.*',
                        'customer.name as cust_name',
                        'customer.mobile as cust_mobile',
                        'challan.total_amount',
                        'hsrp.hsrp_type',
                        'challan.charge',
                        DB::raw('concat(store.name,"-",store.store_type) as store_name'),
                        DB::raw('concat(fitment.name,"-",fitment.store_type) as fitment_location')
                    )->first();
        $detail = ChallanDetails::leftjoin('challan','challan.id','challan_details.challan_id')->where('challan.hsrp_id',$id)->select('challan_details.*')->get();
        $data = [
            'charge' => $charge,
            'challan' => $challan,
            'detail' => $detail,
            'id' => $id,
            'layout' => 'layouts.main'
        ];
        return view('admin.hsrp.hsrp_verification',$data);
    }

    public function HsrpVerification_DB(Request $request) {
          $validator = Validator::make($request->all(),[
           'verify'=>'required',
        ],
        [
            'verify.required'=> 'This field is required.',        
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        } 
        try{
            DB::beginTransaction();
            $id = $request->input('id');
            $hsrp = HSRP::where('id',$id)->first();
            if ($hsrp['verified'] == '1') {
                return redirect('/admin/hsrp/request/list')->with('error','HSRP already completed.');
            }
            if ($hsrp->customer_id == 0 || $hsrp->customer_id == null) {
                return redirect('/admin/hsrp/request/list')->with('error','Firstly add customer for this hsrp request .');
            }
            $HsrpChallan = Challan::where('hsrp_id',$id)->first();
            if (empty($HsrpChallan)) {
                return redirect('/admin/hsrp/challan/details/'.$id)->with('error','Please firstly create hsrp challan details.');
            }
            $verify = $request->input('verify');
            $total_amount = $request->input('total_amount');
            $update = Challan::where('hsrp_id',$id)->update([
                'total_amount' => $total_amount,
                'status' => 'Verify'
            ]);
            if($update == null){
                DB::rollback();
                return back()->with('error','Something Went Wrong')->withInput();
            }
            $data = HSRP::where('id',$id)->update([
                    'verified' => $verify
            ]);
            if($data == null){
                DB::rollback();
                return back()->with('error','Something Went Wrong')->withInput();
            }else{
                $paymentType = 'hsrp';
                $type_id = $id;
                $paymentStatus = 'pending';
                $hsrp_store = $hsrp->store_id;
                $payment  = CustomHelpers::PaymentRequest($hsrp_store,$type_id,$paymentType,$total_amount);
                if ($payment == 0) {
                    DB::rollback();
                    return back()->with('error','Something Went Wrong')->withInput();
                }else{
                    $rto = RtoModel::insertGetId([
                        'main_type' => 'hsrp',
                        'type_id' => $id,
                        'customer_id' => $hsrp['customer_id'],
                        'rto_amount' => $total_amount,
                        'registration_date' => $hsrp->registration_date,
                        'created_by' => Auth::id()

                    ]);
                    if ($rto == null) {
                        DB::rollback();
                        return back()->with('error','Something Went Wrong')->withInput();
                    }else{
                        $customerData = Customers::where('id',$hsrp['customer_id'])->first();
                        $rtosummery = RtoSummary::insertGetId([
                            'rto_id' => $rto,
                            'customer_name' => $customerData->name,
                            'relation_type' => $customerData->relation_type,
                            'relation' => $customerData->relation,
                            'mobile' => $customerData->mobile,
                            'address' => $customerData->address,
                            'created_by' => Auth::id(),
                            'numberPlateStatus' => 0,
                            'rcStatus' => 0,
                            'currentStatus' => 1

                        ]);
                        if ($rtosummery == null) {
                            DB::rollback();
                            return back()->with('error','Something Went Wrong')->withInput();
                        }else{
                            $verifyChallan =  tap(Challan::where('id',$HsrpChallan->id)->where('hsrp_id',$id))->update([
                                'status' => 'Verify' 
                            ])->first();
                            if ($verifyChallan == null) {
                                DB::rollback();
                                return back()->with('error','Something Went Wrong')->withInput();
                            }else{
                                /* Add action Log */
                            CustomHelpers::userActionLog($action='Hsrp verify ',$id,$hsrp['customer_id']);
                            DB::commit();
                            return redirect('/admin/hsrp/request/list')->with('success','HSRP verify Successfully.');
                            }
                            
                        }
                    }
                }
            }

          }catch (\Illuminate\Database\QueryException $e) {
            DB::rollback();
            return back()->with('error','Something Went Wrong'.$e)->withInput();
        }
    }

    public function HsrpRequestView($id) {
        $charge = CustomHelpers::HsrpServiceCharge();
        $challan = HSRP::leftjoin('challan','hsrp.id','challan.hsrp_id')
                    ->leftjoin('store as store','store.id','hsrp.store_id')
                    ->leftjoin('store as fitment','fitment.id','challan.store_id')
                    ->leftjoin('customer','customer.id','hsrp.customer_id')
                    ->where('hsrp.id',$id)
                    ->select(
                        'hsrp.*',
                        'customer.name as cust_name',
                        'customer.mobile as cust_mobile',
                        'challan.total_amount',
                         'hsrp.hsrp_type',
                        'challan.charge',
                        DB::raw('concat(store.name,"-",store.store_type) as store_name'),
                        DB::raw('concat(fitment.name,"-",fitment.store_type) as fitment_location')
                    )->first();
        $detail = ChallanDetails::leftjoin('challan','challan.id','challan_details.challan_id')->where('challan.hsrp_id',$id)->where('challan_details.deleted_at',null
    )->select('challan_details.*')->get();
        $data = [
            'charge' => $charge,
            'challan' => $challan,
            'detail' => $detail,
            'id' => $id,
            'layout' => 'layouts.main'
        ];
        return view('admin.hsrp.hsrp_request_view',$data); 
    }

    public function HsrpChallanDetails_delete($id) {
         $getdata = ChallanDetails::where('id',$id)->first();
         $getChallan = Challan::where('id',$getdata['challan_id'])->first();
         $hsrp = HSRP::where('id',$getChallan['hsrp_id'])->first();
         if ($hsrp['customer_id'] != 0) {
            $customer_id = $hsrp['customer_id'];
         }else{
            $customer_id = 0;
         }
        try{
            $del = ChallanDetails::where('id',$id)->delete();
            if ($del == null) {
                 return back()->with('error',"Something Wen't Wrong. ");
            }else{
                /* Add action Log */
                CustomHelpers::userActionLog($action='Delete Challan details',$del,$customer_id);
                 return redirect('/admin/hsrp/challan/details/'.$getChallan['hsrp_id'])->with('success',"Challan deleted Successfully.");
            }
        }
        catch(\Illuminate\Database\QueryException $ex) {
            return back()->with('error',"Something Wen't Wrong. ".$ex);
        } 
    }

    public function HsrpRequest_Update($id) {
        $hsrp = HSRP::where('id',$id)->first();
        $lastYear = date("Y-m-d", strtotime("-15 years"));
        $store = Store::whereIn('id',CustomHelpers::user_store())->whereIn('store_type',['Showroom','Service','Warehouse'])
                    ->select('id',DB::raw('concat(store.name,"-",store.store_type) as name'))->get();
        $customer  = Customers::orderBy('id','desc')->get();
        if ($hsrp['verified'] == '1') {
            return redirect('/admin/hsrp/request/list/')->with('error',"Hsrp request is already verified.");
        }
        $data = [
            'lastdate'=>$lastYear,
            'hsrp' => $hsrp,
            'store' => $store,
            'customer' => $customer,
            'layout'=>'layouts.main'
        ];
        return view('admin.hsrp.hsrp_request_update',$data);
    }

    public function HsrpRequestUpdate_DB(Request $request) {
        $validator = Validator::make($request->all(),[
           'name'=>'required_without_all:customer',
           'mobile'=>'required_without_all:customer',
           'frame' =>  'required',
           'fueltype' => 'required',
           'registration' => 'required',
           'type' => 'required',
           'type' => 'required',
           'date' => 'required',
           'vechicle_type' => 'required',
           'oem' => 'required',
           'store_name' => 'required',
           'customer' => 'required_without_all:name|required_without_all:mobile',
        ],
        [
            'name.required'=> 'This is required.', 
            'mobile.required'=> 'This is required.',          
            'frame.required'=> 'This is required.',                 
            'fueltype.required'=> 'This is required.',          
            'registration.required'=> 'This is required.',          
            'date.required'=> 'This is required.',          
            'vechicle_type.required'=> 'This is required.',          
            'oem.required'=> 'This is required.', 
            'store_name.required'=> 'This is required.',          
            'customer.required'=> 'This is required.',          
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
            DB::beginTransaction();
            $id = $request->input('id');
            $customer = $request->input('customer');
            if (!empty($customer)) {
                $customer_id = $customer;
            }else{
                $customer_id = 0;
            }
            $name = $request->input('name');
            $mobile = $request->input('mobile');
            $frame = $request->input('frame');
            $registration = $request->input('registration');
            $hsrp = HSRP::where('id',$id)->first();
            if ($hsrp['verified'] == '1') {
                return redirect('/admin/hsrp/request/list/')->with('error',"Hsrp request is already verified.");
            }
            if (!empty($customer)) {
                $check = HSRP::where('customer_id',$customer)->where('frame',$frame)->where('registration',$registration)->where('status','Pending')->where('id','<>',$id)->first();
                if ($check) {
                    return back()->with('error','This request is already send.');
                }
            }

            if (!empty($mobile)) {
                $check = HSRP::where('mobile',$mobile)->where('frame',$frame)->where('registration',$registration)->where('status','Pending')->where('id','<>',$id)->first();
                if ($check) {
                    return back()->with('error','This request is already send.');
                }
            }


             $update = Hsrp::where('id',$id)->update([ 
                    'name'=>$request->input('name'),
                    'customer_id'=>$request->input('customer'),
                    'mobile'=>$request->input('mobile'),
                    'frame'=>$request->input('frame'),
                    'engine'=>$request->input('engine'),
                    'fueltype'=>$request->input('fueltype'),
                    'registration'=>$request->input('registration'),
                    'type'=>$request->input('type'),
                    'registration_date'=>$request->input('date'),
                    'vechicle_type'=>$request->input('vechicle_type'),
                    'oem'=>$request->input('oem'),
                    'store_id' => $request->input('store_name')
                ]);

            if($update == NULL){
                DB::rollback();
                return redirect('/admin/hsrp/request/update/'.$id)->with('error','Some Unexpected Error occurred.');
            }else{

                /* Add action Log */
                CustomHelpers::userActionLog($action='Hsrp update',$id,$customer_id);
                 DB::Commit();
                 return redirect('/admin/hsrp/request/update/'.$id)->with('success','HSRP Request Updated Successfully.'); 

            }
            
        }
        catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
           return redirect('/admin/hsrp/request/update/'.$id)->with('error','some error occurred'.$ex->getMessage())->withInput();
        }
    }

}
