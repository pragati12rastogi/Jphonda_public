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
use \App\Model\ProductModel;
use \App\Model\OTP;
use \App\Model\ProductImages;


class ProductController extends Controller
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

    public function product_list()
    {
        $model = ProductModel::where('product.type','=','product')->where('product.isforsale','1')->whereIn('product.model_category', ['MC','SC',])->groupBy('product.model_name')->get();
        $variant = ProductModel::where('product.type','=','product')->where('product.isforsale','1')->whereIn('product.model_category', ['MC','SC'])->groupBy('product.model_variant')->get();
        $color = ProductModel::where('product.type','=','product')->where('product.isforsale','1')->whereIn('product.model_category', ['MC','SC',])->groupBy('product.color_code')->get();
     

        $data1 = ProductModel::leftjoin('stock','stock.product_id','product.id')
                               ->leftjoin('product_details','product_details.product_id','product.id') 
                               ->where('product.type','=','product')->where('product.isforsale','1')
                               ->whereIn('product.model_category', ['MC','SC'])
                               ->leftJoin('product_images',function($join){
                                        $join->on('product_images.product_id','product.id')
                                        ->where('product_images.default_image','1');
                                })
                               ->leftJoin('product_images as img',function($join){
                                        $join->on('img.product_id','product.id')
                                        ->where('img.default_image','0');
                                })
                               ->select(['product_images.id as  product_images_id','product.*','product_images.*','stock.id as stock_id','stock.quantity','product_details.frame','product_details.engine_number','product.id as product_ids',
                                'img.image as product_notdefault_image'

                             ])->groupBy('product.id')->paginate(9);
                              

        $data=array(
            'model'=>$model,
            'variant'=>$variant,
            'color'=>$color,
            'layout'=>'front.layout'
        );
        
        return view('front.product_list',$data,compact('data1'));
    }

   function fetch_data(Request $request){
      
     if($request->ajax()){
       $type=$request->input('type');
       $model=$request->input('model');
       $variant=$request->input('variant');
       $search=$request->input('search');
       $color=$request->input('color');

        $data1 =ProductModel::leftjoin('stock','stock.product_id','product.id')
                    ->leftjoin('product_details','product_details.product_id','product.id')
                    ->where('product.type','=','product')->where('product.isforsale','1')
                    ->leftJoin('product_images',function($join){
                              $join->on('product_images.product_id','product.id')
                              ->where('product_images.default_image','1');
                    })
                    ->leftJoin('product_images as img',function($join){
                                        $join->on('img.product_id','product.id')
                                        ->where('img.default_image','0');
                                })
                     ->select(['product_images.id as  product_images_id','product.*','product_images.*','stock.id as stock_id','stock.quantity','product_images.*','product_images.id as product_images_id','product_images.*','product_images.id as product_images_id','product.id as product_ids','product_details.frame','product_details.engine_number',
                                'img.image as product_notdefault_image']);



       if($model!='' && $variant!='' && $type!='' && $color!='' && $search!=''){

         $data1->where(function($query) use ($type,$variant,$model,$color,$search){
                        $query->where('product.model_category',$type)
                        ->where('product.model_variant',$variant)
                        ->where('product.model_name',$model)
                        ->where('product.color_code',$color) 
                        ->where('product.model_name','like',"%".$search."%")
                        ->orwhere('product.model_category','like',"%".$search."%")
                        ->where('product.model_variant','like',"%".$search."%")
                        ->where('product.color_code','like',"%".$search."%");
                        
                    });

      }elseif($model!='' && $variant!='' && $type && $search!=''){

        $data1->where(function($query) use ($type,$variant,$model,$search){
                        $query->where('product.model_category',$type)
                        ->where('product.model_variant',$variant)
                        ->where('product.model_name',$model)
                        ->where('product.model_name','like',"%".$search."%")
                        ->orwhere('product.model_category','like',"%".$search."%")
                        ->where('product.model_variant','like',"%".$search."%")
                        ->where('product.color_code','like',"%".$search."%");
                        
                    });

      }elseif($model!='' && $variant!='' && $type!='' && $color){

        $data1->where(function($query) use ($type,$variant,$model,$color){
                        $query->where('product.model_category',$type)
                        ->where('product.model_variant',$variant)
                        ->where('product.model_name',$model)
                        ->where('product.color_code',$color);
                        
                    });
      }elseif($model!='' && $variant!='' && $type!=''){


        $data1->where(function($query) use ($type,$variant,$model){
                        $query->where('product.model_category',$type)
                        ->where('product.model_variant',$variant)
                        ->where('product.model_name',$model);
                        
                    });

      }else if($model!='' && $variant!='' && $color!=''){

        $data1->where(function($query) use ($variant,$model,$color){
                        $query->where('product.model_variant',$variant)
                        ->where('product.model_name',$model)
                        ->where('product.color_code',$color);
                        
                    });

      }else if($model!='' && $type!='' && $color!=''){

        $data1->where(function($query) use ($type,$model,$color){
                        $query->where('product.model_category',$type)
                        ->where('product.model_name',$model)
                        ->where('product.color_code',$color);
                        
                    });

      }else if($model!='' && $color!=''){

        $data1->where(function($query) use ($model,$color){
                        $query->where('product.model_name',$model)
                        ->where('product.color_code',$color);
                        
                    });
  
      }else if($model!='' && $variant!=''){

        $data1->where(function($query) use ($variant,$model){
                        $query->where('product.model_variant',$variant)
                        ->where('product.model_name',$model);
                        
                    });

      }else if($model!='' && $type!=''){

        $data1->where(function($query) use ($type,$model){
                        $query->where('product.model_category',$type)
                        ->where('product.model_name',$model);
                        
                    });
      }else if($color!='' && $type!=''){

        $data1->where(function($query) use ($type,$color){
                        $query->where('product.model_category',$type)
                        ->where('product.color_code',$color);
                        
                    });
      }else if($variant!='' && $type!=''){

        $data1->where(function($query) use ($type,$variant){
                        $query->where('product.model_category',$type)
                        ->where('product.model_variant',$variant);
                        
                    });

      }else if($variant!='' && $color!=''){

          $data1->where(function($query) use ($variant,$color){
                        $query->where('product.model_variant',$variant)
                        ->where('product.color_code',$color);
                        
                    });

      }else if($model!=''){

          $data1->where(function($query) use ($model){
                        $query->where('product.model_name',$model);
                        
                    });
      }else if($variant!=''){

          $data1->where(function($query) use ($variant){
                        $query->where('product.model_variant',$variant);
                        
                    });
      }else if($type!=''){

          $data1->where(function($query) use ($type){
                        $query->where('product.model_category',$type);
                        
                    });

      }else if($color!=''){

          $data1->where(function($query) use ($color){
                        $query->where('product.color_code',$color);
                        
                    });

      }else if($search!=''){

          $data1->where(function($query) use ($search){
                        $query->where('product.model_name','like',"%".$search."%")
                          ->orwhere('product.model_category','like',"%".$search."%")
                          ->where('product.model_variant','like',"%".$search."%")
                          ->where('product.color_code','like',"%".$search."%");
                        
                    });
      }else{
        $data1 =$data1;
      }
      $data1=$data1->groupBy('product.id')->paginate(9);
        return view('front.pagination_data', compact('data1'))->render();
      }
    }
    function search_value(Request $request)
    {
     if($request->ajax())
     {
       $search=$request->input('search');

       $data1 =ProductModel::leftjoin('stock','stock.product_id','product.id')
                     ->leftjoin('product_details','product_details.product_id','product.id')
                    ->where('product.type','=','product')->where('product.isforsale','1')
                    ->whereIn('product.model_category', ['MC','SC',])
                     ->leftJoin('product_images',function($join){
                                        $join->on('product_images.product_id','product.id')
                                        ->where('product_images.default_image','1');
                                })
                     ->leftJoin('product_images as img',function($join){
                                        $join->on('img.product_id','product.id')
                                        ->where('img.default_image','0');
                                })
                    ->groupBy('product.id')->select(['stock.id as stock_id','product.*','stock.quantity','product_images.*','product_images.id as product_images_id','product.id as product_ids','product_details.frame','product_details.engine_number',
                                'img.image as product_notdefault_image']);

       if($search!=''){

         $data1->where(function($query) use ($search){
                        $query->where('product.model_name','like',"%".$search."%")
                          ->orwhere('product.model_category','like',"%".$search."%")
                          ->where('product.model_variant','like',"%".$search."%")
                          ->where('product.color_code','like',"%".$search."%");
                        
                    });

       }else{

       $data1 =$data1;
        }
      }
        $data1=$data1->groupBy('product.id')->paginate(9);
        return view('front.pagination_data', compact('data1'))->render();
    }
   function range_data(Request $request)
    {
     if($request->ajax())
     {

      $data1 =ProductModel::leftjoin('stock','stock.product_id','product.id')
                     ->leftjoin('product_details','product_details.product_id','product.id')
                    ->where('product.type','=','product')->where('product.isforsale','1')
                    ->whereIn('product.model_category', ['MC','SC',])
                    ->leftJoin('product_images',function($join){
                                        $join->on('product_images.product_id','product.id')
                                        ->where('product_images.default_image','1');
                                })
                    ->leftJoin('product_images as img',function($join){
                                        $join->on('img.product_id','product.id')
                                        ->where('img.default_image','0');
                                })
                   ->select(['stock.id as stock_id','product.*','stock.quantity','product_images.*','product_images.id as product_images_id','product.id as product_ids','product_details.frame','product_details.engine_number',
                                'img.image as product_notdefault_image']);


       $min=$request->input('min');
       $max=$request->input('max');
       if($min!='' && $max!=''){

        $data1->where(function($query) use ($min,$max){
                        $query->whereBetween('product.basic_price', array($min,$max));
                        
                    });
       }else{

         $data1 =$data1;
        }
      }
       $data1=$data1->groupBy('product.id')->paginate(9);
        return view('front.pagination_data', compact('data1'))->render();
    }

    function all_filter_data(Request $request){
     if($request->ajax())
     {
      
      $type=$request->input('type');
      $model=$request->input('model');
      $variant=$request->input('variant');
      $color=$request->input('color');

       $data1 =ProductModel::leftjoin('stock','stock.product_id','product.id')
                     ->leftjoin('product_details','product_details.product_id','product.id')
                    ->where('product.type','=','product')->where('product.isforsale','1')
                     ->leftJoin('product_images',function($join){
                                        $join->on('product_images.product_id','product.id')
                                        ->where('product_images.default_image','1');
                                })
                     ->leftJoin('product_images as img',function($join){
                                        $join->on('img.product_id','product.id')
                                        ->where('img.default_image','0');
                                })
                    ->select(['stock.id as stock_id','product.*','stock.quantity','product_images.*','product_images.id as product_images_id','product.id as product_ids','product_details.frame','product_details.engine_number','img.image as product_notdefault_image']);


      if($model!='' && $variant!='' && $type!='' && $color){

          $data1->where(function($query) use ($type,$variant,$model,$color){
                        $query->where('product.model_category',$type)
                        ->where('product.model_variant',$variant)
                        ->where('product.model_name',$model)
                        ->where('product.color_code',$color);
                        
                    });

      }elseif($model!='' && $variant!='' && $type!=''){
         
        $data1->where(function($query) use ($variant,$model,$type){
                        $query->where('product.model_category',$type)
                        ->where('product.model_variant',$variant)
                        ->where('product.model_name',$model);
                        
                    });

      }else if($model!='' && $variant!='' && $color!=''){

           $data1->where(function($query) use ($variant,$model,$color){
                        $query->where('product.color_code',$color)
                        ->where('product.model_variant',$variant)
                        ->where('product.model_name',$model);
                        
                    });

      }else if($model!='' && $type!='' && $color!=''){

           $data1->where(function($query) use ($type,$model,$color){
                        $query->where('product.color_code',$color)
                        ->where('product.model_category',$type)
                        ->where('product.model_name',$model);
                        
                    });
      }else if($model!='' && $color!=''){

          $data1->where(function($query) use ($model,$color){
                        $query->where('product.color_code',$color)
                        ->where('product.model_name',$model);
                        
                    });

      }else if($model!='' && $variant!=''){

        $data1->where(function($query) use ($model,$variant){
                        $query->where('product.model_variant',$variant)
                        ->where('product.model_name',$model);
                        
                    });

      }else if($model!='' && $type!=''){

        $data1->where(function($query) use ($model,$type){
                        $query->where('product.model_category',$type)
                        ->where('product.model_name',$model);
                        
                    });
      }else if($color!='' && $type!=''){

         $data1->where(function($query) use ($color,$type){
                        $query->where('product.model_category',$type)
                        ->where('product.color_code',$color);
                        
                    });
      }else if($variant!='' && $type!=''){

          $data1->where(function($query) use ($variant,$type){
                        $query->where('product.model_category',$type)
                        ->where('product.model_variant',$variant);
                        
                    });

      }else if($variant!='' && $color!=''){

          $data1->where(function($query) use ($variant,$color){
                        $query->where('product.color_code',$color)
                        ->where('product.model_variant',$variant);
                        
                    });

      }else if($model!=''){

         $data1->where(function($query) use ($model){
                        $query->where('product.model_name',$model);  
                    });
      }else if($variant!=''){

         $data1->where(function($query) use ($variant){
                        $query->where('product.model_variant',$variant);
                    });
      }else if($type!=''){

         $data1->where(function($query) use ($type){
                        $query->where('product.model_category',$type);
                    });

      }else if($color!=''){

         $data1->where(function($query) use ($color){
                        $query->where('product.color_code',$color);
                    });
      }else{
        $data1=$data1;
      }
      $data1=$data1->groupBy('product.id')->paginate(9);
        return view('front.pagination_data', compact('data1'))->render();
      }
    }
}
