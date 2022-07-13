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
use \App\Model\ProductModel;
use \App\Model\BestDealPhoto;


class BestDealController extends Controller
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

    public function best_deal_inventory() {
        $checklist = BestDealMaster::all();
          $data = BestDealSale::leftjoin('product','product.id','best_deal_sale.product_id')
                    ->leftJoin('best_deal_photo',function($join){
                        $join->on('best_deal_photo.best_deal_id','best_deal_sale.id')
                            ->where('best_deal_photo.type','front');
                    })
                    ->where('best_deal_sale.status','Ok')
                    ->where('best_deal_sale.repaire_status','allok')
                    ->where('best_deal_sale.sale_id','0')
                     ->where('best_deal_sale.tos','best_deal')
                    ->where('best_deal_sale.selling_price','>',0)
                    ->where('best_deal_sale.status','<>','Pending')
                    ->select('best_deal_sale.id as id',
                        'best_deal_photo.image',
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
            )->paginate(9); 
            $model = ProductModel::where('product.type','=','product')->whereIn('product.model_category', ['MC','SC',])->groupBy('product.model_name')->get();
            $variant = ProductModel::where('product.type','=','product')->whereIn('product.model_category', ['MC','SC'])->groupBy('product.model_variant')->get();
            $color = ProductModel::where('product.type','=','product')->whereIn('product.model_category', ['MC','SC',])->where('product.color_code','<>','')->groupBy('product.color_code')->get();
        $data = [
            'model' => $model,
            'variant' => $variant,
            'color' => $color,
            'data' => $data,
            'layout'=>'front.layout'
        ];
        return view('front.bestdeal',$data,compact('data'));
    }

    public function bkp() {
        $checklist = BestDealMaster::all();
        $data = array(
            'checklist' =>  $checklist,
            'layout'=>'front.layout'
        );
        
        return view('front.bestdeal_old',$data,compact('data'));
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

    public function best_deal_inventory_otp(Request $request)  {
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

    public function BestdealFilterData(Request $request) {
      if($request->ajax()) {
        $model = $request->input('model');
        $variant = $request->input('variant');
        $color = $request->input('color');

                    $data = BestDealSale::leftjoin('product','product.id','best_deal_sale.product_id')
                    ->leftJoin('best_deal_photo',function($join){
                        $join->on('best_deal_photo.best_deal_id','best_deal_sale.id')
                            ->where('best_deal_photo.type','front');
                    })
                    ->where('best_deal_sale.status','Ok')
                    ->where('best_deal_sale.repaire_status','allok')
                    ->where('best_deal_sale.sale_id','0')
                     ->where('best_deal_sale.tos','best_deal')
                    ->where('best_deal_sale.selling_price','>',0)
                    ->where('best_deal_sale.status','<>','Pending')
                    ->select('best_deal_sale.id as id',
                        'best_deal_photo.image',
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

        if (!empty($model) && !empty($variant) && !empty($color)) {

             $data->where(function($query) use ($variant,$model,$color){
                        $query->where('product.model_name',$model)
                        ->where('product.model_variant',$variant)
                        ->where('product.color_code',$color);
                        
                    });
           
        }elseif (!empty($model) && $color == '' && $variant == '') {

                $data->where(function($query) use ($model){
                        $query->where('product.model_name',$model);
                        
                });
           
        }elseif (!empty($variant) && $color == '' && $model == '') {

             $data->where(function($query) use ($variant){
                        $query->where('product.model_variant',$variant);
                        
                    });

        }elseif (!empty($color) && $variant == '' && $model == '') {

             $data->where(function($query) use ($color){
                        $query->where('product.color_code',$color);
                        
                    });

        }elseif (!empty($model) && !empty($variant)) {

             $data->where(function($query) use ($variant,$model){
                        $query->where('product.model_name',$model)
                        ->where('product.model_variant',$variant);
                        
                    });

        }elseif (!empty($variant) && !empty($color)) {

             $data->where(function($query) use ($variant,$color){
                        $query->where('product.model_variant',$variant)
                        ->where('product.color_code',$color);
                        
                    });

        }elseif (!empty($model) && !empty($color)) {

             $data->where(function($query) use ($model,$color){
                        $query->where('product.model_name',$model)
                        ->where('product.color_code',$color);
                        
                    });

        }else{
            $data = $data; 
        }
            $data=$data->paginate(9);
          return view('front.bestdeal_product',compact('data'))->render();
        }
    }

    function BestdealFetchData(Request $request){
      
        if($request->ajax()){
           $model = $request->input('model');
           $variant = $request->input('variant');
           $search = $request->input('search');
           $color = $request->input('color');

           $data = BestDealSale::leftjoin('product','product.id','best_deal_sale.product_id')
                    ->leftJoin('best_deal_photo',function($join){
                        $join->on('best_deal_photo.best_deal_id','best_deal_sale.id')
                            ->where('best_deal_photo.type','front');
                    })
                    ->where('best_deal_sale.status','Ok')
                    ->where('best_deal_sale.repaire_status','allok')
                    ->where('best_deal_sale.sale_id','0')
                     ->where('best_deal_sale.tos','best_deal')
                    ->where('best_deal_sale.selling_price','>',0)
                    ->where('best_deal_sale.status','<>','Pending')
                    ->select('best_deal_sale.id as id',
                        'best_deal_photo.image',
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

           if (!empty($model) && !empty($variant) && !empty($color)) {

            $data->where(function($query) use ($variant,$model,$color){
                        $query->where('product.model_name',$model)
                        ->where('product.model_variant',$variant)
                        ->where('product.color_code',$color);
                        
                    });
        }else{
            $data =$data; 
        }
            $data=$data->paginate(9);
        return view('front.bestdeal_product', compact('data'))->render();
        }
    }

    public function BestdealGetDetails(Request $request) {
        $id = $request->input('id');
        $front = BestDealPhoto::where('best_deal_id',$id)->where('type','front')->get()->first();
        $back = BestDealPhoto::where('best_deal_id',$id)->where('type','back')->get()->first();
        $left = BestDealPhoto::where('best_deal_id',$id)->where('type','left')->get()->first();
        $right = BestDealPhoto::where('best_deal_id',$id)->where('type','right')->get()->first();
        $details = BestDealSale::leftjoin('product','product.id','best_deal_sale.product_id')
                    ->leftJoin('best_deal_photo',function($join){
                        $join->on('best_deal_photo.best_deal_id','best_deal_sale.id')
                            ->where('best_deal_photo.type','front');
                    })
                    ->where('best_deal_sale.status','Ok')
                    ->where('best_deal_sale.repaire_status','allok')
                    ->where('best_deal_sale.sale_id','0')
                     ->where('best_deal_sale.tos','best_deal')
                    ->where('best_deal_sale.selling_price','>',0)
                    ->where('best_deal_sale.status','<>','Pending')
                    ->where('best_deal_sale.id',$id)
                    ->select('best_deal_sale.id as id',
                        'best_deal_photo.image',
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
            )->get()->first();
        $data = [
            'details' => $details,
                'front' => $front,
                'back' => $back,
                'left' => $left,
                'right' => $right
            ];
        return response()->json($data);
    }


}
