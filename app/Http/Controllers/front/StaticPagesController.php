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
use \App\Model\Contact;
use \App\Model\Users;
use \App\Model\InsurancQuotation;
use Mail;
use \App\Model\BestDealMaster;
use \App\Model\BestDealSale;
use \App\Model\Product;
use \App\Model\OTP;
use \App\Model\Settings;


class StaticPagesController extends Controller
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

    public function index()
    {
        $data=array(
            'layout'=>'front.layout'
        );
        return view('front.index', $data);  
    }

    public function about_us()
    {
        $data=array(
            'layout'=>'front.layout'
        );
        return view('front.about_us', $data);  
    }
    public function privacy_policy()
    {
        $data=array(
            'layout'=>'front.layout'
        );
        return view('front.privacy_policy', $data);  
    }
    public function career()
    {
        $data=array(
            'layout'=>'front.layout'
        );
        return view('front.career', $data);  
    }
    public function contact_us()
    {
        $data=array(
            'layout'=>'front.layout'
        );
        return view('front.contact', $data);  
    }
    
    public function contact_us_Db(Request $request){
        
         $validator = Validator::make($request->all(),[
               'name'=>'required',
                'mobile'=>'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10|max:10',
                'message'=>'required',
                'email'=>'required|email'

        ],
        [
                'name.required'=> 'This is required.',
                'message.required'=> 'This is required.',
                'email.required'=> 'This is required.',
                'mobile.required'=> 'This is required.',
                'mobile.regex'=> 'Mobile No contains digits only.',
                'mobile.max'=> 'Mobile No may not be greater than 10 digits.',
                'mobile.min'=> 'Mobile No must be at least 10 digits.',
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
           

            $customer = Contact::insertGetId(
                [ 
                    'name'=>$request->input('name'),
                    'mobile'=>$request->input('mobile'),
                    'message'=>$request->input('message'),
                    'email'=>$request->input('email')
                ]
            );

            if($customer==NULL) 
            {
                DB::rollback();
                return redirect('/contactus/')->with('error','Some Unexpected Error occurred.');
            }
            else{

                $admin_mail = Settings::where('name','admin_email')->first();

                $email_view = 'admin.emails.contact';
                $subject = 'Contact';
                $name=$request->input('name');
                $email=$request->input('email');
                $mobile=$request->input('mobile');
                $messages=$request->input('message');
                if($admin_mail){
                $emails = $admin_mail->value;
                   
               
                if($emails!=null){
                    
                   $sent= Mail::send($email_view, ['name' =>$name,'email'=>$email,'mobile'=>$mobile,'messages'=>$messages], function($message) use ($emails,$subject,$name)
                        {
                            $message->to($emails,$name)->subject($subject);
                        });
                  
                 }
              }
                return redirect('/contactus/')->with('success','Message sent Successfully, we will get back to you soon.'); 
             }    
        
        }  catch(\Illuminate\Database\QueryException $ex) {
            return redirect('/contactus/')->with('error','some error occurred'.$ex->getMessage())->withInput();
        }
    }
    public function insurance_quotation()
    {
        $data=array(
            'layout'=>'front.layout'
        );
        return view('front.insurance_quotation', $data);  
    }

        public function insurance_quotation_Db(Request $request){
        
         $validator = Validator::make($request->all(),[
               
                'mobile'=>'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10|max:10',

        ],
        [
                'mobile.required'=> 'This is required.',
                'mobile.regex'=> 'Mobile No contains digits only.',
                'mobile.max'=> 'Mobile No may not be greater than 10 digits.',
                'mobile.min'=> 'Mobile No must be at least 10 digits.',
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
           

            $customer = InsurancQuotation::insertGetId(
                [ 
                    'mobile'=>$request->input('mobile'),
                    'frame_number'=>$request->input('frame_number')
                ]
            );

            if($customer==NULL) 
            {
                DB::rollback();
                return redirect('/insurancequotation/')->with('error','Some Unexpected Error occurred.');
            }
            else{

                return redirect('/insurancequotation/')->with('success','Message sent Successfully, we will get back to you soon.'); 
             }    
        
        }  catch(\Illuminate\Database\QueryException $ex) {
            return redirect('/insurancequotation/')->with('error','some error occurred'.$ex->getMessage())->withInput();
        }
    }
    public function best_deal_inventory()
    {
        $checklist = BestDealMaster::all();
        $data=array(
            'checklist' =>  $checklist,
            'layout'=>'front.layout'
        );
        
        return view('front.bestdeal',$data);
    }

    public function best_deal_inventory_api(Request $request) {
       
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;

           
            $api_data = BestDealSale::leftjoin('product','product.id','best_deal_sale.product_id')
                                        ->where('best_deal_sale.status','Ok')
                                        ->where('best_deal_sale.repaire_status','allok')
                                        ->where('best_deal_sale.sale_id','0')
                                         ->where('best_deal_sale.tos','best_deal')
                                        ->where('best_deal_sale.selling_price','>',0)
                                        ->where('best_deal_sale.status','<>','Pending')
                                        ->select('best_deal_sale.id as id',
                                        DB::raw('IF(best_deal_sale.product_id = 0,best_deal_sale.model,product.model_name) as model'),        
                                        DB::raw('IF(best_deal_sale.product_id = 0,best_deal_sale.variant,product.model_variant) as variant'),        
                                        DB::raw('IF(best_deal_sale.product_id = 0,best_deal_sale.color,product.color_code) as color'),
                                        'best_deal_sale.selling_price',
                                        'best_deal_sale.dos',
                                        'best_deal_sale.register_no',
                                        'best_deal_sale.frame',
                                        'best_deal_sale.address','best_deal_sale.position','best_deal_sale.number',
                                        'best_deal_sale.name','best_deal_sale.aadhar','best_deal_sale.voter',
                                        'best_deal_sale.km','best_deal_sale.pan','best_deal_sale.maker',
                                        'best_deal_sale.yom','best_deal_sale.no_of_key',
                                        'best_deal_sale.rc_status','best_deal_sale.value','best_deal_sale.profit',
                                        'best_deal_sale.receive_amount','best_deal_sale.status','best_deal_sale.repaire_status',
                                        'best_deal_sale.created_at'
            );
                                    
            
            // DB::enableQueryLog();
            if(!empty($serach_value))
            {
               $api_data->where(function($query) use ($serach_value){
                        $query->where('variant','like',"%".$serach_value."%")
                        ->orwhere('model','like',"%".$serach_value."%")
                        ->orwhere('color','like',"%".$serach_value."%")
                        ->orwhere('selling_price','like',"%".$serach_value."%")
                        ->orwhere('dos','like',"%".$serach_value."%")
                        ->orwhere('register_no','like',"%".$serach_value."%")
                        ->orwhere('frame','like',"%".$serach_value."%")
                        ->orwhere('address','like',"%".$serach_value."%");
                        
                    });
               
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                    'model',
                    'variant',
                    'color',
                    'dos',
                    'register_no',
                    'frame',
                    'address'
                ];
                
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('best_deal_sale.id','asc');   
        
        $count = $api_data->count();
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

    public function best_deal_inventory_otp(Request $request)
    {
        $mobile = $request->input('mobile');

        $getcheck = OTP::where('mobile',$mobile)->first();

        $otp=mt_rand(1000,9999);

        if(!$getcheck){
             $data = [
                    'mobile' =>$mobile,
                    'otp' =>$otp
                ];

             $otp=OTP::insertGetId($data);

              if($otp){
                 return array('type' => 'success', 'msg' => 'Successfully Otp Send.');
              }else{
                 return array('type' => 'error', 'msg' => 'Something went wrong.'); 
              }

        }else{
             $otpupdate = OTP::where('mobile',$mobile)->update(['otp' =>$otp]);
             if($otpupdate){
                   return array('type' => 'success', 'msg' => 'Successfully Otp Send.');
              }else{
                 return array('type' => 'error', 'msg' => 'Something went wrong.');  
              }
            
        }    
    }

  public function best_deal_inventory_otpmatch(Request $request)
    {
        $otp = $request->input('otp');
        $mobile = $request->input('mobile');
        $id = $request->input('id');

        $getcheck = OTP::where('otp',$otp)->where('mobile',$mobile)->first();

        if($getcheck){
            
            $getprice = BestDealSale::where('id',$id)->first();
            if($getprice){
             $price= $getprice->selling_price;
             return array('type' => 'success', 'msg' => 'Successfully.','price'=>$price);

            }

        }else{
          return array('type' => 'error', 'msg' => 'OTP is not match');  
        }    
    }

     public function testimonial()
    {
        $data=array(
            'layout'=>'front.layout'
        );
        return view('front.customer_testimonial', $data);  
    }


}
