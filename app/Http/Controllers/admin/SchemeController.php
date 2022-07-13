<?php

namespace App\Http\Controllers\admin;

use \stdClass;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Model\Hsrp;
use App\Model\Users;
use \App\Model\Customers;
use App\Model\Userlog;
use \App\Model\Store;
use \App\Custom\CustomHelpers;
use \App\Model\ProductModel;
use \App\Model\Scheme;
use Illuminate\Support\Facades\DB;
use Auth;
use File;
use Hash;


class SchemeController extends Controller
{
    public function scheme_create(){
        $model_name = ProductModel::where('type','product')->orderBy('model_name','ASC')
                            ->whereRaw('isforsale = 1')
                            ->groupBy('model_name')->get(['model_name']);

        $data = array(
            'model_name'=>  ($model_name->toArray())? $model_name->toArray() : array(),
            'layout'=>'layouts.main'
        );
        return view('admin.master.scheme',$data);
    } 

    public function scheme_create_DB(Request $request){
         $validator = Validator::make($request->all(),[
          
           'model_name' => 'required',
           'model_variant' => 'required',
           'title' => 'required',
           'amount' => 'required',
           'upload_image' => 'required|max:8192|dimensions:ratio>=5/3,max_width=500|image|mimes:jpeg,png,jpg',
           'description' => 'required',

        ],
        [
            'model_name.required'=> 'This is required.', 
            'model_variant.required'=> 'This is required.',          
            'title.required'=> 'This is required.',                
            'amount.required'=> 'This is required.',                
            'upload_image.required'=> 'This is required.',                
            'description.required'=> 'This is required.',                
                   
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try{

             if($request->hasFile('upload_image'))
            {

                $asset_image_file ='';
                $destinationPath = public_path().'/upload/scheme';
                $file = $request->file('upload_image');
                
                $filenameWithExt=$file->getClientOriginalName();
                $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME); 
                $extension = $file->getClientOriginalExtension();
                $scheme_image_file = $filename.'_'.time().'.'.$extension;
                $path = $file->move($destinationPath, $scheme_image_file);

        }

             $data = Scheme::insertGetId([ 
                    'product_id'=>$request->input('model_variant'),
                    'name'=>$request->input('title'),
                    'amount'=>$request->input('amount'),
                    'image'=>$scheme_image_file,
                    'description' => $request->input('description')
                ]);

            if($data==NULL){
                DB::rollback();
                return redirect('/admin/master/scheme/create/')->with('error','Some Unexpected Error occurred.');
            }else{

                 CustomHelpers::userActionLog($action='Create Scheme',$data,0);
                 return redirect('/admin/master/scheme/create/')->with('success','Successfully Created Scheme.'); 
            }
            
        }
        catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
           return redirect('/admin/master/scheme/create/')->with('error','some error occurred'.$ex->getMessage())->withInput();
        } 
    } 

    public function scheme_list() {

      
        $data=array('layout'=>'layouts.main');
        return view('admin.master.scheme_list', $data); 
    }
    public function scheme_list_api(Request $request){

        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;

        $api_data= Scheme::leftJoin('product','product.id','scheme.product_id')
            ->select(
                'scheme.id',
                'product.model_name',
                'product.model_variant',
                'scheme.name',
                'scheme.amount',
                'scheme.description' 
            );
         if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('product.model_name','like',"%".$serach_value."%")
                    ->orwhere('product.model_variant','like',"%".$serach_value."%")
                    ->orwhere('scheme.name','like',"%".$serach_value."%")
                    ->orwhere('scheme.amount','like',"%".$serach_value."%")
                    ->orwhere('scheme.description','like',"%".$serach_value."%")
                    ;
                });
            }
         if(isset($request->input('order')[0]['column']))
            {
                $data = [
                    'product.model_name',
                    'product.model_variant',
                    'scheme.name',
                    'scheme.amount',
                    'scheme.description'
                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else 
                $api_data->orderBy('scheme.id','desc');      

        $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 

        return json_encode($array);

    }

    public function scheme_view($id) {
        
         $scheme_data = Scheme::leftJoin('product','product.id','scheme.product_id')->where('scheme.id',$id)         
                    ->select(
                        'scheme.*',
                        'product.model_name',
                        'product.model_variant'

                    )->get()->first();

        if(!$scheme_data){
            return redirect('/admin/master/scheme/list')->with('error','Id not exist.');
        }
       else{

           $data=array(
            'scheme_data'=>$scheme_data,
            'layout' => 'layouts.main'
           );
            return view('admin.master.scheme_view',$data);
      }        
    }  

    public function scheme_update($id) {
        
        $model_name = ProductModel::where('type','product')->orderBy('model_name','ASC')
                            ->whereRaw('isforsale = 1')
                            ->groupBy('model_name')->get(['model_name']);
        $scheme_data = Scheme::leftJoin('product','product.id','scheme.product_id')->where('scheme.id',$id)         
                    ->select(
                        'scheme.*',
                        'product.model_name as modelname',
                        'product.model_variant as modelvariant'

                    )->get()->first();
        if(!$scheme_data){
            return redirect('/admin/master/scheme/list')->with('error','Id not exist.');
        }
       else{

           $data=array(
            'id'=>$id,
            'model_name'=>  ($model_name->toArray())? $model_name->toArray() : array(),
            'scheme_data'=>$scheme_data,
            'layout' => 'layouts.main'
           );
            return view('admin.master.scheme_update',$data);
      }        
    } 

    public function scheme_update_DB(Request $request,$id){
         $validator = Validator::make($request->all(),[
          
           'model_name' => 'required',
           'model_variant' => 'required',
           'title' => 'required',
           'amount' => 'required',
           // 'upload_image' => 'required|max:8192|dimensions:ratio>=5/3,max_width=500|image|mimes:jpeg,png,jpg',
           'description' => 'required',

        ],
        [
            'model_name.required'=> 'This is required.', 
            'model_variant.required'=> 'This is required.',          
            'title.required'=> 'This is required.',                
            'amount.required'=> 'This is required.',                
            // 'upload_image.required'=> 'This is required.',                
            'description.required'=> 'This is required.',                
                   
        ]);
        if ($validator->fails()) {
             return redirect('/admin/master/scheme/update/'.$id)->withErrors($errors);
        }

        try{

           $scheme_image_file ='';
           $file = $request->file('upload_image');
              
            if(!isset($file)||$file == null){
                    $scheme_image_file = $request->input('old_image');
                }else{

                if (!empty($request->file('upload_image'))) {
                  $validator = Validator::make($request->all(),[
                    'upload_image'=>'required|max:8192|dimensions:ratio>=5/3,max_width=500|image|mimes:jpeg,png,jpg',
                    ],
                    [
                            'upload_image.required'=> 'This field is required.',
                    ]);
                    if ($validator->fails()) {
                         return back()->withErrors($validator)->withInput();
                    }
                }

                $destinationPath = public_path().'/upload/scheme/';
                $filenameWithExt = $request->file('upload_image')->getClientOriginalName();
                $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension = $request->file('upload_image')->getClientOriginalExtension();
                $scheme_image_file = $filename.'_'.time().'.'.$extension;
                $path = $file->move($destinationPath, $scheme_image_file);
                File::delete($destinationPath.$request->input('old_image'));
            }

             $data = Scheme::where('id',$id)->update([
                            'product_id'=>$request->input('model_variant'),
                            'name'=>$request->input('title'),
                            'amount'=>$request->input('amount'),
                            'image'=>$scheme_image_file,
                            'description' => $request->input('description')
                        ]);

            if($data==NULL){
                DB::rollback();
                return redirect('/admin/master/scheme/update/'.$id)->with('error','Some Unexpected Error occurred.');
            }else{

                 CustomHelpers::userActionLog($action='Scheme Update',$id,0);
                 return redirect('/admin/master/scheme/update/'.$id)->with('success','Successfully Updated Scheme.'); 
            }
            
        }
        catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
           return redirect('/admin/master/scheme/update/'.$id)->with('error','some error occurred'.$ex->getMessage())->withInput();
        } 
    }  
}
