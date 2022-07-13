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
use \App\Model\Hsrp;
use Mail;



class HsrpController extends Controller
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

     public function Hsrp_request() {

    
       
      $lastYear = date("Y-m-d", strtotime("-15 years"));

      $data = [
            'lastdate'=>$lastYear,
            'layout'=>'front.layout'
        ];
        
        return view('front.hsrp_request',$data);
    }

    public function Hsrp_request_DB(Request $request)
    {

        $validator = Validator::make($request->all(),[
           'name'=>'required',
           'mobile'=>'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10|max:10',
           'frame' =>  'required',
           // 'engine' => 'required',
           'fueltype' => 'required',
           'registration' => 'required',
           'type' => 'required',
           'type' => 'required',
           'date' => 'required',
           'vechicle_type' => 'required',
           'oem' => 'required',
        ],
        [
            'name.required'=> 'This is required.', 
            'mobile.required'=> 'This is required.',          
            'frame.required'=> 'This is required.',          
            // 'engine.required'=> 'This is required.',          
            'fueltype.required'=> 'This is required.',          
            'registration.required'=> 'This is required.',          
            'date.required'=> 'This is required.',          
            'vechicle_type.required'=> 'This is required.',          
            'oem.required'=> 'This is required.',
            'mobile.regex'=> 'Mobile No contains digits only.',
            'mobile.max'=> 'Mobile No may not be greater than 10 digits.',
            'mobile.min'=> 'Mobile No must be at least 10 digits.',          
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

             $data = Hsrp::insertGetId(
                [ 
                    'name'=>$request->input('name'),
                    'mobile'=>$request->input('mobile'),
                    'frame'=>$request->input('frame'),
                    'engine'=>$request->input('engine'),
                    'fueltype'=>$request->input('fueltype'),
                    'registration'=>$request->input('registration'),
                    'type'=>$request->input('type'),
                    'registration_date'=>$request->input('date'),
                    'vechicle_type'=>$request->input('vechicle_type'),
                    'oem'=>$request->input('oem'),
                ]
            );

             if($data==NULL) 
            {
                DB::rollback();
                return redirect('/hsrp/request/')->with('error','Some Unexpected Error occurred.');
            }else{
                 return redirect('/hsrp/request/')->with('success','Request sent Successfully, we will get back to you soon.'); 
            }
            
        }
        catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
           return redirect('/hsrp/request/')->with('error','some error occurred'.$ex->getMessage())->withInput();
        }
    }

}
