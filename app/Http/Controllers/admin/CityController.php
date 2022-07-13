<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;
use \App\Model\Store;
use \App\Model\Unload;
use \App\Model\DamageClaim;
use \App\Model\DamageDetails;
use \App\Model\ProductModel;
use \App\Model\ProductDetails;
use \App\Model\Waybill;
use \App\Model\Loader;
use \App\Model\StockMovement;
use \App\Model\Customers;
use \App\Model\StockMovementDetails;
use \App\Custom\CustomHelpers;
use App\Model\DamageDetail;
use \App\Model\Stock;
use App\Model\Users;
use \App\Model\Countries;
use \App\Model\City;
use \App\Model\Locality;
use \App\Model\State;
use \App\Model\Sale;
use \App\Model\Factory;
use \App\Model\PartShortage;
use \App\Model\ShortageDetails;
use \App\Http\Controllers\Product;
use DB;
use Auth;
use Hash;
use \Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\Import;

class CityController extends Controller
{
    public function create_locality() {
        $country = Countries::where('id',105)->get()->first();
        $state = State::where('country_id',105)->get();
        $data = array(
            'country'=>$country,
            'state'=>$state,
            'layout'=>'layouts.main'
        );
        return view('admin.create_locality',$data);
    }

    public function locality_search(Request $request) {
        $text = $request->input('text');
        if ($text != '') {

            $locality_name=Locality::where('locality.city_id', $request->input('city_id'))->where('locality.name', 'Like', "%$text%")->select('locality.*')->take(5)->get();
          return response()->json($locality_name);

        }
    }

    public function create_db(Request $request){
         try {
             $this->validate($request,[
                        'city'=>'required',
                        'country'=>'required',
                        'state'=>'required',
                        'locality'=>'required',

                     ],[
                        'city.required'=>'This Field is required',
                        'country.required'=>'This Field is required',
                        'state.required'=>'This Field is required',
                        'locality.required'=>'This Field is required',
                 ]);

                    $getlocality=Locality::where('city_id',$request->input('city'))->select('name')->get()->first();

                     $old_locality=strtolower($getlocality['name']);
                     $locality_new=strtolower($request->input('locality'));
                     if ($old_locality == $locality_new) {
                            return redirect('/admin/city/location/')->with('error','This locality is already added.');
                        }else{
                            $locality = Locality::insertGetId([
                                'name'=>$request->input('locality'),
                                'city_id'=>$request->input('city')
                                
                            ]);
                        }
                        if($locality == NULL) 
                        {
                           DB::rollback();
                           return redirect('/admin/city/location/')->with('error','Some Unexpected Error occurred.');
                        }
                        else{
                             /* Add action Log */
                             CustomHelpers::userActionLog($action='Add Locality',$locality,0);
                            return redirect('/admin/city/location/')->with('success','Locality Successfully added.');
                        }
        }  catch(\Illuminate\Database\QueryException $ex) {
            return redirect('/admin/city/location/')->with('error','some error occurred'.$ex->getMessage());
        }
    }


    public function locality_list(){

        return view('admin.locality_data',['layout' => 'layouts.main']);
    }

    public function locality_list_api(Request $request){

        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
        
        $api_data=Locality::leftjoin('cities','locality.city_id','cities.id')
                            ->select(
                                'locality.id',
                                'locality.name',
                                'cities.city'
                            );


        if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('locality.name','like',"%".$serach_value."%")
                    ->orwhere('cities.city','like',"%".$serach_value."%");
                });
            }
        if(isset($request->input('order')[0]['column']))
            {
                $data = [
                'locality.id',
                'locality.name',
                'cities.city'
                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
        else
                $api_data->orderBy('locality.id','asc');      
        
        $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);


    }
}
