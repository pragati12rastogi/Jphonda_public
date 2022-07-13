<?php

namespace App\Http\Controllers\front;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App;
use Illuminate\Routing\Route;
use DB;
use \Carbon\Carbon;
use Auth;
use Hash;
use \App\Model\Challan;
use \App\Model\ChallanDetails;
use Mail;



class ChallanController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth');
    }

     public function challan_certificate_request() {

      $data = [
            'layout'=>'front.layout'
        ];
        
        return view('front.challan_certificate_request',$data);
    }

    public function challan_certificate_request_DB(Request $request)
    {

       $validator = Validator::make($request->all(),[    
                'name'=>'required',
                'mobile'=>'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10|max:10',
                'registration_no'=>'required'
            ],
            [
                'name.required'=>'This Field is required',
                'mobile.required'=>'This Field is required',
                'mobile.regex'=> 'Mobile No contains digits only.',
                'mobile.max'=> 'Mobile No may not be greater than 10 digits.',
                'mobile.min'=> 'Mobile No must be at least 10 digits.',
                'registration_no.required'=>'This Field is required'
            ]);
           if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        } 
     
        try {

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
            $name = $request->input('name');
            $mobile = $request->input('mobile');
            $registration_no = $request->input('registration_no');
            $total_amount = $request->input('total_amount');

            $challan = $request->input('challan');
            $amount = $request->input('amount');

            // challan
            $challan_data = Challan::insertGetId([
                    'name' => $name,
                    'mobile' => $mobile,
                    'registration_no' => $registration_no,
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

                        if(isset($challan[$challan_count])){
                            
                            $challan_detail = ChallanDetails::insertGetId([
                                'challan_id' => $challan_data,
                                'challan_number'=>$challan[$challan_count],
                                'amount' =>$amount[$challan_count],
                                'status' =>'Pending'
                            ]);
                        }
                        
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
        return redirect('/pay/challan/request')->with("success",'Submit Successfully');
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

}
