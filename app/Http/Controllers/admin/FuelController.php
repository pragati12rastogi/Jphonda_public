<?php

namespace App\Http\Controllers\admin;
use Illuminate\Http\Request;
use \stdClass;
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
use \App\Model\State;
use \App\Model\Factory;
use \App\Model\PartShortage;
use \App\Model\ShortageDetails;
use \App\Model\Sale;
use \App\Model\SaleAccessories;
use \App\Model\SaleFinance;
use \App\Model\Scheme;
use \App\Model\Accessories;
use \App\Model\MasterAccessories;
use \App\Model\HiRise;
use \App\Model\Insurance;
use \App\Model\SaleOrder;
use \App\Model\RtoModel;
use \App\Model\Payment;
use \App\Model\OrderPendingModel;
use \App\Model\BookingModel;
use \App\Model\BestDealSale;
use \App\Model\SecurityModel;
use \App\Model\AdditionalServices;
use \App\Model\AdditionalServicesAccessories;
use \App\Model\FuelModel;
use \App\Model\FuelRequestModel;
use \App\Model\FuelStockModel;
use \App\Model\Master;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Auth;
use Hash;
use \Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\Import;

class FuelController extends Controller
{
    public function __construct()
    {
        //$this->middleware('auth');
    }
       public function add_fuel(){
        $fuel_type = Master::where('type','fuel_mode')->select('key','value')->get();
        $store = Store::whereIn('id',CustomHelpers::user_store())->get();
        $data = array(
            'store' => $store,
            'fuel_type'=>$fuel_type,
            'layout'=>'layouts.main'
        );
        return view('admin.fuel.add_fuel',$data);
    }

    public function getFuelType($mode) {
        $getdata = Master::where('type','fuel_mode')->where('key',$mode)->get()->first();
        $details = json_decode($getdata['details']);
         return response()->json($details);
    }

    public function add_fuel_db(Request $request){
         try {
            $this->validate($request,[
                'store'   =>  'required',
                'fuel_mode'   =>  'required',
                'fuel_type'   =>  'required',
                'quantity'   =>  'required|numeric|between:0,9999.9999',
            ],
            [
                'store.required'  =>  'This Field is required',
                'fuel_mode.required'  =>  'This Field is required',
                'fuel_type.required'  =>  'This Field is required',
                'quantity.required'  =>  'This Field is required',
            ]);

                $insert = FuelModel::insertGetId([
                    'fuel_mode'   =>  $request->input('fuel_mode'),
                    'fuel_type'   =>  $request->input('fuel_type'),
                    'requested_by' => Auth::id(),
                    'store_id'   =>  $request->input('store'),
                    'quantity'    =>  $request->input('quantity')
           ]);
            if ($insert == NULL) {
                return redirect('/admin/fuel/add')->with('error','some error occurred');
            }else{
                 /* Add action Log */
                 CustomHelpers::userActionLog($action='Add Fuel',$insert,0);
                 return redirect('/admin/fuel/add')->with('success','Fuel added successfully ');
            }
        }  catch(\Illuminate\Database\QueryException $ex) {
            return redirect('/admin/fuel/add')->with('error','some error occurred'.$ex->getMessage());
        }
       
    }

    public function fuel_list() {
        $data = array(
            'layout'=>'layouts.main'
        );
        return view('admin.fuel.fuel_list',$data);
    }

    public function fuel_list_api(Request $request){
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
        $api_data = FuelModel::leftJoin('store','store.id','fuel.store_id')
            ->select(
                'fuel.id',
                'fuel.fuel_mode',
                'fuel.fuel_type',
                'fuel.quantity',
                'fuel.status',
                DB::raw('concat(store.name,"-",store.store_type) as name')
            );
            if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('fuel.fuel_mode','like',"%".$serach_value."%")                    
                    ->orwhere('fuel.fuel_type','like',"%".$serach_value."%")                    
                    ->orwhere('fuel.quantity','like',"%".$serach_value."%")                    
                    ->orwhere('fuel.status','like',"%".$serach_value."%")                    
                    ;
                });
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                'fuel.id',
                'fuel.fuel_mode',
                'fuel.fuel_type',
                'quantity',              
                'status',              
                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('fuel.id','desc');      
        
        $count = count($api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
       
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

    public function fuel_approve($id) {
          try {
            $getrequst = FuelModel::where('id',$id)->get()->first();
            $fuelstock = FuelStockModel::where('store_id',$getrequst['store_id'])->where('fuel_type',$getrequst['fuel_type'])->get()->first();
           DB::beginTransaction();
           $checkStore = CustomHelpers::CheckAuthStore($getrequst['store_id']);
            if ($checkStore) {

            if ($fuelstock['quantity'] > $getrequst['quantity']) {
                $update = FuelStockModel::where('store_id',$getrequst['store_id'])->where('fuel_type',$getrequst['fuel_type'])->update([
                    'quantity' => $fuelstock['quantity'] - $getrequst['quantity']
                ]);
                $data = FuelModel::where('id',$id)->where('store_id',$getrequst['store_id'])->update(
                [
                    'status' => 'approved',
                    'approved_by' => Auth::id()
                ]
                );

                if($data) {
                     DB::commit();
                      /* Add action Log */
                      CustomHelpers::userActionLog($action='Fuel Requisition Approve',$id,0);
                     return  array('type' => 'success', 'msg' => 'Fuel Requisition approved Successfully .');
                } else{
                   DB::rollBack();
                    return  array('type' => 'error', 'msg' => 'Something went wrong.');
                }
            }else{
                 DB::rollBack();
               return  array('type' => 'error', 'msg' => 'Fuel not available in store.');  
            }
          }else{
                DB::rollBack();
               return  array('type' => 'error', 'msg' => 'You Are Not Authorized For Cancel This Request.'); 
          }
        }  catch(\Illuminate\Database\QueryException $ex) {
           DB::rollBack();
           return  array('type' => 'error', 'msg' => 'Something went wrong.');  
        }
    }



        public function fuel_reject($id) {
          try {
            $getrequst = FuelModel::where('id',$id)->get()->first();
            $checkStore = CustomHelpers::CheckAuthStore($getrequst['store_id']);
            if ($checkStore) {
               
                $data = FuelModel::where('id',$id)->where('store_id',$getrequst['store_id'])->update([
                    'status' => 'reject'
                ]);

                if($data) {
                    /* Add action Log */
                    CustomHelpers::userActionLog($action='Fuel Requisition Reject',$id,0);
                    return  array('type' => 'success', 'msg' => 'Fuel Requisition Rejected Successfully .');
                } else{
                     return  array('type' => 'error', 'msg' => 'Something went wrong.');              
                }
            }else{
                  return  array('type' => 'error', 'msg' => 'You Are Not Authorized For Cancel This Request.');
            }
            
        }  catch(\Illuminate\Database\QueryException $ex) {
            return  array('type' => 'error', 'msg' => 'Something went wrong.');      
        }
    }

    public function fuel_request() {
        $store = Store::whereIn('id',CustomHelpers::user_store())->get();
        $data = array(
            'store'=>$store,
            'layout'=>'layouts.main'
        );
        return view('admin.fuel.fuel_request',$data);
    }

    public function fuel_request_DB(Request $request) {
         try {
            $this->validate($request,[
                'store'   =>  'required',
                'fuel_type'   =>  'required',
                'quantity'   =>  'required|numeric|between:0,99999.99999',
            ],
            [
                'store.required'  =>  'This Field is required',
                'fuel_type.required'  =>  'This Field is required',
                'quantity.required'  =>  'This Field is required',
            ]);
          //  DB::beginTransaction();
                $insert = FuelRequestModel::insertGetId([
                    'store_id'   =>  $request->input('store'),
                    'requested_by' => Auth::id(),
                    'quantity'    =>  $request->input('quantity'),
                    'fuel_type'    =>  $request->input('fuel_type')
           ]);
            if ($insert == NULL) {
                return redirect('/admin/fuel/request')->with('error','some error occurred');
            }else{
                 /* Add action Log */
                 CustomHelpers::userActionLog($action='Add Fuel Request',$insert,0);
                 return redirect('/admin/fuel/request')->with('success','Fuel added successfully ');
            }
        }  catch(\Illuminate\Database\QueryException $ex) {
            return redirect('/admin/fuel/add')->with('error','some error occurred'.$ex->getMessage());
        }
    }

    public function fuel_request_list() {
        $data = array(
            'layout'=>'layouts.main'
        );
        return view('admin.fuel.fuel_request_list',$data);
    }

     public function fuel_request_list_api(Request $request){
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
        $api_data = FuelRequestModel::leftJoin('store','store.id','fuel_stock_request.store_id')
            ->select(
                'fuel_stock_request.id',
                'fuel_stock_request.quantity',
                'fuel_stock_request.fuel_type',
                'fuel_stock_request.status',
                 DB::raw('concat(store.name,"-",store.store_type) as name')
            );
            if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('fuel_stock_request.id','like',"%".$serach_value."%")
                    ->orwhere('fuel_stock_request.quantity','like',"%".$serach_value."%")
                    ->orwhere('fuel_stock_request.fuel_type','like',"%".$serach_value."%")
                    ->orwhere('fuel_stock_request.status','like',"%".$serach_value."%");
                });
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                'fuel_stock_request.id',
                'fuel_stock_request.quantity',              
                'fuel_stock_request.fuel_type',              
                'fuel_stock_request.status',              
                'store.name',              
                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('fuel_stock_request.id','desc');      
        
        $count = count($api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
       
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

    public function fuel_request_approve($id) {
          try {
            $getrequst = FuelRequestModel::where('id',$id)->get()->first();
            $fuelstock = FuelStockModel::where('store_id',$getrequst['store_id'])->where('fuel_type',$getrequst['fuel_type'])->get()->first();
              DB::beginTransaction();
            $checkStore = CustomHelpers::CheckAuthStore($getrequst['store_id']);
            if ($checkStore) {
                if ($fuelstock) {
                    $update = FuelStockModel::where('store_id',$getrequst['store_id'])->where('fuel_type',$getrequst['fuel_type'])->update([
                        'quantity' => $fuelstock['quantity'] + $getrequst['quantity']
                    ]);
                }else{
                    $insert = FuelStockModel::insertGetId([
                        'store_id' => $getrequst['store_id'],
                        'fuel_type' => $getrequst['fuel_type'],
                        'quantity' => $getrequst['quantity']
                    ]);
                }

                $data = FuelRequestModel::where('id',$id)->update([
                    'status' => 'approved',
                    'approved_by' => Auth::id()
                ]);
                if($data) {
                    DB::commit();
                    /* Add action Log */
                    CustomHelpers::userActionLog($action='Fuel Request Approve',$id,0);
                   return  array('type' => 'success', 'msg' => 'Fuet request approved successfully.');
                } else{
                    DB::rollBack();
                   return  array('type' => 'error', 'msg' => 'Something went wrong.');             
                }
            }else{
                DB::rollBack();
                return  array('type' => 'error', 'msg' => 'You Are Not Authorized For Cancel This Request.');
            }
        
        }  catch(\Illuminate\Database\QueryException $ex) {
            DB::rollBack();
            return  array('type' => 'error', 'msg' => 'Something went wrong.');
        }
    }


    public function fuel_request_reject($id) {
          try {
            $getrequst = FuelRequestModel::where('id',$id)->get()->first();
            $fuelstock = FuelStockModel::where('store_id',$getrequst['store_id'])->where('fuel_type',$getrequst['fuel_type'])->get()->first();
              DB::beginTransaction();
            $checkStore = CustomHelpers::CheckAuthStore($getrequst['store_id']);
            if ($checkStore) {

                $data = FuelRequestModel::where('id',$id)->update([
                    'status' => 'reject'
                ]);
                if($data) {
                    DB::commit();
                   /* Add action Log */
                   CustomHelpers::userActionLog($action='Fuel Request Reject',$id,0);
                   return  array('type' => 'success', 'msg' => 'Fuet request reject successfully.');
                } else{
                    DB::rollBack();
                   return  array('type' => 'error', 'msg' => 'Something went wrong.');             
                }
            }else{
                DB::rollBack();
                return  array('type' => 'error', 'msg' => 'You Are Not Authorized For Cancel This Request.');
            }
        
        }  catch(\Illuminate\Database\QueryException $ex) {
            DB::rollBack();
            return  array('type' => 'error', 'msg' => 'Something went wrong.');
        }
    }

    public function fuel_stock_list() {
        $data = array(
            'layout'=>'layouts.main'
        );
        return view('admin.fuel.fuel_stock_list',$data);
    }

    public function fuel_stock_list_api(Request $request) {
         $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
        $api_data = FuelStockModel::leftJoin('store','store.id','fuel_stock.store_id')
            ->select(
                'fuel_stock.id',
                'fuel_stock.quantity',
                'fuel_stock.fuel_type',
                DB::raw('concat(store.name,"-",store.store_type) as name')
            );
            if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('fuel_stock.id','like',"%".$serach_value."%")                    
                    ->orwhere('fuel_stock.quantity','like',"%".$serach_value."%")                   
                    ->orwhere('fuel_stock.fuel_type','like',"%".$serach_value."%")                   
                    ;
                });
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                'fuel_stock.id',
                'fuel_stock.quantity',              
                'fuel_stock.fuel_type',              
                'store.name',              
                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('fuel_stock.id','desc');      
        
        $count = count($api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
       
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

}
?>