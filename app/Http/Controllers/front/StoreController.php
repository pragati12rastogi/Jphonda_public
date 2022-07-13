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
use \App\Model\Store;



class StoreController extends Controller
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

    public function store()
    {
        $data1=Store::select('store.id','store.name','store.city','store.address','store.store_type','store.mobile')->whereIn('store_type',['Showroom','Service','Warehouse'])->groupBy('store.id')->paginate(10);
        $data=array(
            'layout'=>'front.layout'
        );
        return view('front.store_list', $data,compact('data1'));  
    }

     function fetch_data(Request $request){
      
     if($request->ajax()){

         $data1 =Store::select('store.id','store.name','store.city','store.address','store.store_type','store.mobile')->whereIn('store_type',['Showroom','Service','Warehouse'])->groupBy('store.id')->paginate(10);
      }
        return view('front.store_data', compact('data1'))->render();
    }

    function search_value(Request $request){

     if($request->ajax()){
        $search=$request->input('search');
           if($search!=''){

            $data1 =Store::select('store.id','store.name','store.city','store.address','store.store_type','store.mobile')
                    ->where('store.name','like',"%".$search."%")
                    ->orwhere('store.store_type','like',"%".$search."%")
                    ->orwhere('store.address','like',"%".$search."%")
                    ->groupBy('store.id')->paginate(10);
           }else{

                $data1=Store::select('store.id','store.name','store.city','store.address','store.store_type','store.mobile')->groupBy('store.id')->paginate(10);
           }
       }
        return view('front.store_data', compact('data1'))->render();
    }

}
