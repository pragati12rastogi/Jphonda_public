<?php

namespace App\Http\Controllers\admin;

use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Model\Hsrp;
use App\Model\Challan;
use App\Model\ChallanDetails;
use App\Model\Userlog;
use App\Model\Store;
use App\Model\Customers;
use App\Model\PaymentRequest;
use \App\Custom\CustomHelpers;

class ChallanController extends Controller
{
    public function get_challan_status() {

        $charge = CustomHelpers::HsrpServiceCharge();
        $store = Store::whereIn('id',CustomHelpers::user_store())->whereIn('store_type',['Showroom','Service','Warehouse'])
                    ->select('id',DB::raw('concat(store.name,"-",store.store_type) as name'))->get();
        $customer = Customers::all();
     
      $data = [
            'layout'=>'layouts.main',
            'store'=>$store,
            'customer'=>$customer,
            'service_charge' => $charge['service_charge']
        ];

        return view('admin.hsrp.get_challan_status',$data);
    }


    public function get_challan_status_DB(Request $request) {

         $valarr = [     
                
                'store'=>'required',
                'customer'=>'required_without:username',
                'username'=>'required_without:customer|required_with:mobile',
                'mobile'=>'required_with:username',
                'challan.*'=>'required|unique:challan_details,challan_number',
                'amount.*'=>'required',
                'status.*'=>'required',


            ];
            $valmsg = [
           
                'store.required'=>'This Field is required',
                'customer.required_without'=>'This Field is required',
                'username.required_without'=>'This Field is required',
                'mobile.required_with'=>'This Field is required',
                'challan.*.required'=>'This Field is required',
                'amount.*.required'=>'This Field is required',
                'status.required'=>'This Field is required',
                'challan.unique'=> 'Challan Number already taken.'

            ];
            $this->validate($request,$valarr,$valmsg);

        try {

            if (!empty($request->input('mobile'))) {
                  $validator = Validator::make($request->all(),[
                    'mobile'=>'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10|max:10',
                    ],
                    [
                            'mobile.required'=> 'This field is required.',
                            'mobile.regex'=> 'Mobile No contains digits only.',
                            'mobile.max'=> 'Mobile No may not be greater than 10 digits.',
                            'mobile.min'=> 'Mobile No must at least 10 digits.',
                    ]);

                    if ($validator->fails()) {
                        return back()->withErrors($validator)->withInput();
                    }
                }

            DB::beginTransaction();
        
            $store = $request->input('store');
            $customer = $request->input('customer');
            $name = $request->input('username');
            $mobile = $request->input('mobile');

            $challan = $request->input('challan');
            $amount = $request->input('amount');
            $total_amount = $request->input('total_amount');

            // challan
            $challan_data = Challan::insertGetId([
                    'store_id' => $store,
                    'customer_id' => $customer,
                    'name' => $name,
                    'mobile' => $mobile,
                    'total_amount'=>$total_amount
            ]);
            if(!$challan_data){
                DB::rollback();
                return back()->with('error','Something Went Wrong')->withInput();
            }

             if(count($challan)>0){
                $challan_count = 0;

                 foreach($challan as $key => $value) {
                     // challandetail table insert

                        $challan_detail = ChallanDetails::insertGetId([
                                'challan_id' => $challan_data,
                                'challan_number'=>$challan[$challan_count],
                                'amount' =>$amount[$challan_count],
                                'status' =>'Pending'
                            ]);

                        if(!$challan_detail){
                                DB::rollback();
                                return back()->with('error','Error, Something Went Wrong')->withInput();
                            }

                    $challan_count++;
                 }
             }

          }catch (\Illuminate\Database\QueryException $e) {
            DB::rollback();
            return back()->with('error','Something Went Wrong'.$e)->withInput();
        }
        DB::commit();
        CustomHelpers::userActionLog($action='Pay Challan Request Add',$challan_data,0);
        return redirect('/admin/pay/challan/request')->with("success",'Submit Successfully');
    }

     public function get_challan_status_list(){

        $data = array('layout'=>'layouts.main');
        return view('admin.hsrp.get_challan_status_list', $data);

    }

     public function get_challan_status_list_api(Request $request){

        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;


        $jobdata = Challan::leftjoin('challan_details','challan.id','challan_details.challan_id')
                    ->leftjoin('hsrp','hsrp.id','challan.hsrp_id')
                    ->leftjoin('customer','challan.customer_id','customer.id')
                    ->leftJoin('customer as hsrpcustomer','hsrpcustomer.id','hsrp.customer_id')
                    ->select(
                        'challan.id',
                        'challan.name',
                        'challan.mobile',
                        'challan.total_amount',
                        'customer.name as customer_name',
                        'customer.mobile as customer_mobile',
                        'challan.status',
                        'hsrp.verified as hsrp_verified',
                        'challan.hsrp_id as challan_hsrp_id',
                        'hsrp.id as hsrp_id',
                        'hsrpcustomer.name as hsrpcustomer_name',
                        'hsrpcustomer.mobile as hsrpcustomer_mobile',
                        DB::raw('Group_Concat(challan_details.challan_number)as challan_no')
                    ) ->groupBy('challan.id');

        if(!empty($serach_value))
        {
            $jobdata->where(function($query) use ($serach_value){
                $query->where('challan_details.challan_number','LIKE',"%".$serach_value."%")
                 ->orwhere('challan.name','LIKE',"%".$serach_value."%")
                 ->orwhere('challan.mobile','LIKE',"%".$serach_value."%")
                 ->orwhere('challan.status','LIKE',"%".$serach_value."%")
                ;
              });
     
        }
        
        if(isset($request->input('order')[0]['column']))
        {
            $data = [
            'name',
            'mobile',
            'challan_no',
            'total_amount',
            'challan_hsrp_id',
            'status',
            'customer_name',
            'customer_mobile',
            'hsrpcustomer_name',
            'hsrpcustomer_mobile'
                ];
            $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
            $jobdata->orderBy($data[$request->input('order')[0]['column']], $by);
        }
        else
            $jobdata->orderBy('id','desc');
            $count = count($jobdata->get()->toArray());
            $jobdata = $jobdata->offset($offset)->limit($limit);
            $jobdata=$jobdata->get();
            $array['recordsTotal'] = $count;
            $array['recordsFiltered'] = $count;
            $array['data'] = $jobdata;
            return json_encode($array);
    }

     public function get_challan_status_Details(Request $request)
    {
        $challan_id = $request->input('challan_id');
        $challan_data =ChallanDetails::where('challan_id','=',$challan_id)->get();
         return response()->json($challan_data);

    }

   public function challan_status_verify(Request $request){
        $challan_id = $request->input('challan_id');
        
        $getcheck = Challan::where('id',$challan_id)->first();
        DB::beginTransaction();
        if($getcheck){
            $store=$getcheck['store_id'];
            $paymentType='Challan';
            $type_id=$challan_id;
            $total_amount=$getcheck['total_amount'];
            $challan = Challan::where('id',$challan_id)->update(['status' => 'Verify']);
            
            if ($challan) {
               
                $payment = CustomHelpers::PaymentRequest($store,$type_id,$paymentType,$total_amount);
                if ($payment == 0) {
                    DB::rollback();
                    return'Something Went Wrong';
                }else{
                    CustomHelpers::userActionLog($action='Pay Challan Request status verify',$challan_id,0);
                    DB::commit();
                     return 'success'; 
                }

            }else{
                return 'error';
            }

        }else{

            return "Challan not exist";
        }    
    }

    public function challan_status_update(Request $request){
        $challan_id = $request->input('challan_id');
        $getcheck = Challan::where('id',$challan_id)->first();
        
        if($getcheck){
            $store=$getcheck['store_id'];
            $type_id=$challan_id;
            if($getcheck['hsrp_id'] != 0){
                $hsrp_verified = Hsrp::where('id',$getcheck['hsrp_id'])->first();
                if($hsrp_verified['verified'] == 1){
                    $paymentcheck = PaymentRequest::where('type_id',$getcheck['hsrp_id'])
                    ->where('type','hsrp')->where('store_id',$store)->first();
                }
            }else{
                $paymentcheck = PaymentRequest::where('type_id',$challan_id)
                ->where('type','challan')->where('store_id',$store)->first();
            }
            
            if($paymentcheck){
                    if($paymentcheck['status']=='Done'){
                       $challan = Challan::where('id',$challan_id)->update(['status' => 'done']);
                        if ($challan) {
                              $challan_details = ChallanDetails::where('challan_id',$challan_id)->update(['status' => 'done']);
                            CustomHelpers::userActionLog($action='Pay Challan Request status done',$challan_id,0);
                            return 'success'; 
                        }else{
                            return 'error';
                        }
                    }else{
                        return "Challan Payment not complete.";
                    }
            }else{
                return "Challan Payment Request not created.";
            }
        }else{

            return "Challan not exist";
        }    
    }

    public function checkChallan_number (Request $request) {
       $challan_no = $request->input('challan');
      
       $check = ChallanDetails::
                       where('challan_number',$challan_no)
                        ->get()->first();
       if ($check) {
        return "challan_error";
       }
       
    }

    public function challan_status_view($id) {
        
         $charge = CustomHelpers::HsrpServiceCharge();
         $challan_data = Challan::leftJoin('challan_details','challan.id','challan_details.challan_id')->leftjoin('customer','challan.customer_id','customer.id')
                    ->leftJoin('store','challan.store_id','store.id')  
                    ->where('challan.id',$id)         
                    ->select(
                        'challan.id',
                        'challan.name',
                        'challan.mobile',
                        'challan.registration_no',
                        'challan.total_amount',
                        'customer.name as customer_name',
                        'customer.mobile as customer_mobile',
                        DB::raw('concat(store.name,"-",store.store_type) as store_name'),
                        'challan.status',
                        'challan.charge'
                    )->get()->first();

        if(!$challan_data){
            return redirect('/admin/pay/challan/request/list')->with('error','Id not exist.');
        }
       else{

         $challan_id=$challan_data->id;
         $challan_details = ChallanDetails::where('challan_id',$challan_id)->get();

           $data=array(
            'charge' => $charge,
            'challan_data'=>$challan_data,
            'challan_details'=>$challan_details,
            'layout' => 'layouts.main'
           );
            return view('admin.hsrp.pay_challan_view',$data);
      }        
    }  

}
