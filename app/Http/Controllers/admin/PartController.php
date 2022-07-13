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
use \App\Model\VehicleAddonStock;
use \App\Model\Customers;
use \App\Model\StockMovementDetails;
use \App\Custom\CustomHelpers;
use App\Model\DamageDetail;
use \App\Model\Stock;
use App\Model\Users;
use \App\Model\Countries;
use \App\Model\City;
use \App\Model\Part;
use \App\Model\Locality;
use \App\Model\State;
use \App\Model\Sale;
use\App\Model\ServiceModel;
use \App\Model\Factory;
use \App\Model\PartShortage;
use \App\Model\PartOtcSale;
use \App\Model\PartStock;
use \App\Model\ShortageDetails; 
use \App\Model\PartRequest;
use \App\Model\Payment;
use \App\Model\PurchaseOrderRequest;
use \App\Model\PurchaseOrder;
use \App\Model\PurchaseOrderItem;
use\App\Model\AMCModel;
use\App\Model\VehicleAddon;
use \App\Model\ExtendedWarranty;
use \App\Model\PartMovement;
use \App\Model\PartMovementDetails;
use \App\Model\PartProcessDetails;
use \App\Model\PartProcess;
use \App\Model\FuelStockModel;
use \App\Model\FuelModel;
use \App\Http\Controllers\Product;
use DB;
use Auth;
use Hash;
use \Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\Import;
use Illuminate\Support\Facades\Validator;

class PartController extends Controller
{
    public function part_list(){

        return view('admin.part.part_data',['layout' => 'layouts.main']);
    }

    public function part_list_api(Request $request){
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
        
        $api_data = Part::select(
                                'id',
                                'name',
                                'part_number',
                                'consumption_level',
                                'type',
                                'price',
                                'frt'
                            );

        if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('name','like',"%".$serach_value."%")
                    ->orwhere('consumption_level','like',"%".$serach_value."%") 
                    ->orwhere('type','like',"%".$serach_value."%") 
                    ->orwhere('price','like',"%".$serach_value."%") 
                    ->orwhere('frt','like',"%".$serach_value."%") 
                    ->orwhere('part_number','like',"%".$serach_value."%");
                });
            }
        if(isset($request->input('order')[0]['column']))
            {
                $data = [
                'id',
                'name',
                'part_number',
                'type',
                'consumption_level',
                'price',
                'frt'

                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
        else
                $api_data->orderBy('part.id','asc');      
        
        $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);

    }

    public function consumption_update(Request $request){
        $part_id = $request->input('partId');
        $part = Part::where('id',$part_id)->get()->first();
        return response()->json($part);
    }

    public function consumption_updateDB(Request $request){
        $part_id = $request->input('partId');
        $consumption_level = $request->input('consumption_level');
        $frt_time = $request->input('frt_time');
        $part = Part::where('id',$part_id)->get()->first();
        if(!$part){
             return 'Part id not exists .';
        }else {
            $partdata= Part::where('id',$part_id)->update([
                        'consumption_level' => $consumption_level,
                        'frt'=>$frt_time
                    ]);
           if($partdata == NULL) {
                return 'error';
            }else{                
                return 'success';               
            }
        }
    }

    public function enquiry_statusupdate(Request $request){

        $part_requestid = $request->input('partrequestId');
        $part_number = $request->input('part_number');
        $partstore = $request->input('partstore');
        
        if(!$part_number){

            return response()->json('Part Number is required .');
        }else {

            $check_data=Part::where('part_number',$part_number)->get()->first();
            if(!$check_data){
                return response()->json('Part Number not exists .');

            }else{
              
             $partId=$check_data->id;
             $check_avilable=PartStock::where('part_id',$partId)->where('store_id',$partstore)->get()->first();
             if(!$check_avilable){
                $partdata= PartRequest::where('id',$part_requestid)->update([
                        'status' =>'Not Available'
                        ]); 
                if($partdata == NULL) {
                      return response()->json('error');
                    }else{      

                      return response()->json('success');                        
                    }
                }else{
                    $quantity=$check_avilable->quantity;
                    if($quantity>0){
                        $partdata= PartRequest::where('id',$part_requestid)->update([
                        'status' =>'Available'

                    ]);                    
                    }
                     else{

                        $partdata= PartRequest::where('id',$part_requestid)->update([
                        'status' =>'Not Available'
                        ]); 
                     }          
                    if($partdata == NULL) {
                      return response()->json('error');
                    }else{                
                      return response()->json('success');             
                    }

                }
            }
        }
    }

    public function otc_sale() {
        $user = Users::where('id',Auth::Id())->get()->first();
        $store = Store::whereIn('id',CustomHelpers::user_store())->get();
        $data = array(
            'store' => $store,
            'user' => $user,
            'layout' => 'layouts.main'
        );
         return view('admin.part.otc_sale',$data);
    }

    public function otcSale_DB(Request $request) {
         try {
            $this->validate($request,[
                'sale_type'=>'required',
                'customer_name'=>'required',
                'mobile'=>'required',
                // 'qty'=>'required',
                'model_name'=>'required',
                'color_code'=>'required',
                'store'=>'required',
            ],[
                'sale_type.required' => 'This field is required.', 
                'customer_name.required' => 'This field is required.', 
                'mobile.required' => 'This field is required.', 
                // 'qty.required' => 'This field is required.', 
                'model_name.required' => 'This field is required.', 
                'color_code.required' => 'This field is required.',
                'store.required' => 'This field is required.',
            ]); 
            $sale_type = $request->input('sale_type');
            if ($sale_type == 'Part') {
                $request = PartOtcSale::insertGetId([
                    'sale_type' => $request->input('sale_type'),
                    'customer_name' => $request->input('customer_name'),
                    'customer_mobile' => $request->input('mobile'),
                    'qty' => $request->input('qty'),
                    'model_name' => $request->input('model_name'),
                    'store_id' => $request->input('store'),
                    'color' => $request->input('color_code')
                ]);
                if ($request == NULL) {
                    return redirect('/admin/part/otc/sale')->with('error','Something went wrong');
                }else{
                    return redirect('/admin/part/otc/sale')->with('success','Otc Sale Request send successfully');
                }
            }else if ($sale_type == 'AMC') {
               $amc_charge = '500';
               $start_date = date('Y-m-d');
               $date = strtotime($start_date);
               $new_date = strtotime('+ 1 year', $date);
               $end_date = date('Y-m-d', $new_date);

                if ($request->input('service_id')) {
                    DB::beginTransaction();
                    $checkAmc = AMCModel::where('service_id',$request->input('service_id'))->get();
                    if (count($checkAmc) == 0) {
                        $insertAmc = AMCModel::insertGetId([
                            'service_id' => $request->input('service_id'),
                            'start_date' => $start_date,
                            'end_date' => $end_date
                         ]);
                        }

                        $request = PartOtcSale::insertGetId([
                            'sale_type' => $request->input('sale_type'),
                            'customer_name' => $request->input('customer_name'),
                            'customer_mobile' => $request->input('mobile'),
                            'model_name' => $request->input('model_name'),
                            'store_id' => $request->input('store'),
                            'color' => $request->input('color_code'),
                            'price' => $amc_charge
                        ]);
                        if ($request == NULL) {
                            DB::rollback();
                            return redirect('/admin/part/otc/sale')->with('error','Something went wrong');
                        }else{
                            DB::commit();
                            return redirect('/admin/part/otc/sale')->with('success','Otc Sale Request send successfully');
                        }
                }else{
                    $serviceInsert = ServiceModel::insertGetId([
                        'frame' => $request->input('frame'),
                        'registration' => $request->input('registration'),
                    ]);
                    if ($serviceInsert) {
                        $insertAmc = AMCModel::insertGetId([
                            'service_id' => $request->input('service_id'),
                            'start_date' => $start_date,
                            'end_date' => $end_date
                         ]);
                    
                        $request = PartOtcSale::insertGetId([
                            'sale_type' => $request->input('sale_type'),
                            'customer_name' => $request->input('customer_name'),
                            'customer_mobile' => $request->input('mobile'),
                            'model_name' => $request->input('model_name'),
                            'store_id' => $request->input('store'),
                            'color' => $request->input('color_code'),
                            'price' => $amc_charge
                        ]);
                        if ($request == NULL) {
                            DB::rollback();
                            return redirect('/admin/part/otc/sale')->with('error','Something went wrong');
                        }else{
                            DB::commit();
                            return redirect('/admin/part/otc/sale')->with('success','Otc Sale Request send successfully');
                        }
                    }
                }
            }else if ($sale_type == 'Extended Warranty') {
               $ew_charge = '500';
               $start_date = date('Y-m-d');
               $date = strtotime($start_date);
               $year = $request->input('year');
               $new_date = strtotime('+ '.$year.' year', $date);
               $end_date = date('Y-m-d', $new_date);

                if ($request->input('service_id')) {
                    DB::beginTransaction();
                    $checkEw = ExtendedWarranty::where('service_id',$request->input('service_id'))->get();
                    if (count($checkEw) == 0) {
                        $insertAmc = ExtendedWarranty::insertGetId([
                            'service_id' => $serviceInsert,
                            'start_date' => $start_date,
                            'end_date' => $end_date
                         ]);
                        }

                        $request = PartOtcSale::insertGetId([
                            'sale_type' => $request->input('sale_type'),
                            'customer_name' => $request->input('customer_name'),
                            'customer_mobile' => $request->input('mobile'),
                            'model_name' => $request->input('model_name'),
                            'store_id' => $request->input('store'),
                            'color' => $request->input('color_code'),
                            'price' => $ew_charge
                        ]);
                        if ($request == NULL) {
                            DB::rollback();
                            return redirect('/admin/part/otc/sale')->with('error','Something went wrong');
                        }else{
                            DB::commit();
                            return redirect('/admin/part/otc/sale')->with('success','Otc Sale Request send successfully');
                        }
                }else{
                    $serviceInsert = ServiceModel::insertGetId([
                        'frame' => $request->input('frame'),
                        'registration' => $request->input('registration'),
                    ]);
                    if ($serviceInsert) {
                        $insertEw = ExtendedWarranty::insertGetId([
                            'service_id' => $serviceInsert,
                            'start_date' => $start_date,
                            'end_date' => $end_date
                         ]);
                    
                        $request = PartOtcSale::insertGetId([
                            'sale_type' => $request->input('sale_type'),
                            'customer_name' => $request->input('customer_name'),
                            'customer_mobile' => $request->input('mobile'),
                            'model_name' => $request->input('model_name'),
                            'store_id' => $request->input('store'),
                            'color' => $request->input('color_code'),
                            'price' => $ew_charge
                        ]);
                        if ($request == NULL) {
                            DB::rollback();
                            return redirect('/admin/part/otc/sale')->with('error','Something went wrong');
                        }else{
                            DB::commit();
                            return redirect('/admin/part/otc/sale')->with('success','Otc Sale Request send successfully');
                        }
                    }else{
                        DB::rollback();
                        return redirect('/admin/part/otc/sale')->with('error','Something went wrong');
                    }
                }
            }

        }  catch(\Illuminate\Database\QueryException $ex) {
            return redirect('/admin/part/otc/sale')->with('error','some error occurred'.$ex->getMessage());
        }
    }

    public function otcSale_list() {
        $data = array(
            'layout' => 'layouts.main'
        );
         return view('admin.part.otc_sale_list',$data);
    }

    public function otcSaleList_api(Request $request) {
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
        
        $api_data = PartOtcSale::leftjoin('store','part_otc_sale.store_id','store.id')
                    ->select(
                                'part_otc_sale.id',
                                'part_otc_sale.customer_name',
                                'part_otc_sale.customer_mobile',
                                'part_otc_sale.model_name',
                                'part_otc_sale.color',
                                'part_otc_sale.qty',
                                'part_otc_sale.status',
                                DB::raw('concat(store.name,"-",store.store_type) as storename')
                            );

        if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('part_otc_sale.customer_name','like',"%".$serach_value."%")
                    ->orwhere('part_otc_sale.model_name','like',"%".$serach_value."%") 
                    ->orwhere('part_otc_sale.customer_mobile','like',"%".$serach_value."%") 
                    ->orwhere('part_otc_sale.color','like',"%".$serach_value."%") 
                    ->orwhere('part_otc_sale.qty','like',"%".$serach_value."%") 
                    ->orwhere('part_otc_sale.status','like',"%".$serach_value."%");
                });
            }
        if(isset($request->input('order')[0]['column']))
            {
                $data = [
                'part_otc_sale.id',
                'part_otc_sale.customer_name',
                'part_otc_sale.customer_mobile',
                'store.name',
                'part_otc_sale.model_name',
                'part_otc_sale.color',
                'part_otc_sale.qty',
                'part_otc_sale.status'

                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
        else
                $api_data->orderBy('part_otc_sale.id','desc');      
        
        $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

    public function getOtcSale(Request $request) {
        $req_id = $request->input('req_id');
        $request = PartOtcSale::where('id',$req_id)->get()->first();
        return response()->json($request);
    }

    public function checkPart(Request $request) {
       $part_no = $request->input('part_no');
       $id = $request->input('id');
       $quantity = $request->input('quantity');
       $getstore = PartOtcSale::where('id',$id)->get()->first();
       $store_id = $getstore['store_id'];
       $check = Part::where('part_number',$part_no)->get()->first();
       if ($check) {
           $part_id = $check['id'];
           $checkstock = PartStock::where('store_id',$store_id)->where('part_id',$part_id)->get()->first();
           if ($checkstock['quantity'] >= $quantity) {
               return response()->json($checkstock);
           }else{
            return "stock_error";
           }
       }else{
        return "part_error";
       }
       
    }

    public function createRequisition() {
         $store = Store::all();
         $data = [
            'store'=>$store,    
            'layout' => 'layouts.main'
        ];
        
        return view('admin.part.create_requisition',$data);
    }

    public function createRequisitionDB(Request $request) {
         $validator = Validator::make($request->all(),[
           'requisition_type'  =>  'required',
           'store'  =>  'required',
           'model_name'=>'required',
           'color'=>'required',
        ],
        [
            'requisition_type.required'  => 'This Field is required',
            'store.required'=>'This field is required.',
            'model_name.required'  =>  'This Field is required',
            'color.required'=>'This field is required.',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
         try {

               $requisition_type = $request->input('requisition_type');
               $store = $request->input('store');
               $model_name = $request->input('model_name');
               $color = $request->input('color');

             if($requisition_type=='enquiry'){
                    $data = PartRequest::insertGetId([
                    'model_name' => $model_name,
                    'color' => $color,
                    'store_id' => $store
                    ]);
                
                 if($data == NULL) {
                    return redirect('/admin/part/requisition/')->with('error','some error occurred');
                    } else{
                    return redirect('/admin/part/requisition/')->with('success','Create Part Requisition Successfully  .');
                    }
             }
             else if($requisition_type=='purchase'){   
                  $data = PurchaseOrderRequest::insertGetId([
                    'model_name' => $model_name,
                    'color' => $color,
                    'store_id' => $store
                    ]);   
                if($data == NULL) {
                    return redirect('/admin/part/requisition/')->with('error','some error occurred');
                    } else{
                    return redirect('/admin/part/requisition/')->with('success','Create Part Requisition Successfully  .');
                    }
             }

         } catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return redirect('/admin/part/requisition/')->with('error','some error occurred');
        }
    }

    public function RequisitionEnquiryList(){

        $store = Store::whereIn('id',CustomHelpers::user_store())
                    ->select('id',DB::raw('concat(store.name,"-",store.store_type) as name'))->get();
        $data = [
            'store'=>$store,
            'layout' => 'layouts.main'
        ];
        
        return view('admin.part.part_enquirylist',$data);
    }

    public function RequisitionEnquiryListApi(Request $request){

        $search = $request->input('search');
        $store_name=$request->input('store_name');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
        
        $store = Store::whereIn('id',CustomHelpers::user_store())
                    ->select('id')->get()->toArray();

        $api_data= PartRequest::leftJoin('store','part_request.store_id','store.id')
            ->whereIn('part_request.store_id',$store)
            ->select(
                'part_request.id',
                'part_request.model_name',
                'part_request.color',
                'part_request.status',
                'part_request.store_id',
                 DB::raw('concat(store.name,"-",store.store_type) as store_name')
            );

        if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('model_name','like',"%".$serach_value."%")
                    ->orwhere('color','like',"%".$serach_value."%") 
                    ->orwhere('status','like',"%".$serach_value."%")
                    ->orwhere('store.name','like',"%".$serach_value."%"); 
                });
            }
        if(!empty($store_name))
            {
               $api_data->where(function($query) use ($store_name){
                        $query->where('part_request.store_id','like',"%".$store_name."%");
                    });
               
            }
        if(isset($request->input('order')[0]['column']))
            {
                $data = [
                'part_request.id',
                'part_request.model_name',
                'part_request.color',
                'part_request.status',
                'store.store_name',
                'part_request.store_id'

                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
        else
            $api_data->orderBy('part_request.id','asc');      
        
        $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);

    }

    public function RequisitionPurchaseList(){

        $store = Store::whereIn('id',CustomHelpers::user_store())
                    ->select('id',DB::raw('concat(store.name,"-",store.store_type) as name'))->get();
        $data = [
            'store'=>$store,
            'layout' => 'layouts.main'
        ];
        
        return view('admin.part.purchase_order_requestlist',$data);

    }

        public function RequisitionPurchaseListApi(Request $request){

        $search = $request->input('search');
        $store_name=$request->input('store_name');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
        
         $store = Store::whereIn('id',CustomHelpers::user_store())
                    ->select('id')->get()->toArray();

        $api_data= PurchaseOrderRequest::leftJoin('store','purchase_order_request.store_id','store.id')
            ->whereIn('purchase_order_request.store_id',$store)
            ->select(
                'purchase_order_request.id',
                'purchase_order_request.model_name',
                'purchase_order_request.color',
                'purchase_order_request.status',
                // 'store.name as store_name',
                 DB::raw('concat(store.name,"-",store.store_type) as store_name')
            );

        if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('model_name','like',"%".$serach_value."%")
                    ->orwhere('color','like',"%".$serach_value."%") 
                    ->orwhere('status','like',"%".$serach_value."%")
                    ->orwhere('store.name','like',"%".$serach_value."%"); 
                });
            }
        if(!empty($store_name))
            {
               $api_data->where(function($query) use ($store_name){
                        $query->where('purchase_order_request.store_id','like',"%".$store_name."%");
                    });
               
            }
        if(isset($request->input('order')[0]['column']))
            {
                $data = [
                'purchase_order_request.model_name',
                'purchase_order_request.color',
                'purchase_order_request.status'

                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
        else
            $api_data->orderBy('purchase_order_request.id','asc');      
        
        $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);

    }

      public function forReceptionistotcSale_list() {
        $data = array(
            'layout' => 'layouts.main'
        );
         return view('admin.part.reception_otc_sale_list',$data);
    }

    public function forReceptionistotcSaleList_api(Request $request) {
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
        
        $api_data = PartOtcSale::leftjoin('store','part_otc_sale.store_id','store.id')
                    ->leftjoin('part','part_otc_sale.part_id','part.id')
                    ->leftJoin('payment',function($join){
                    $join->on(DB::raw('payment.type = "PartOtcSale" and payment.type_id'),'=','part_otc_sale.id')->where('payment.status','received');
                    })
                    ->select(
                                'part_otc_sale.id',
                                'part_otc_sale.customer_name',
                                'part_otc_sale.customer_mobile',
                                'part_otc_sale.model_name',
                                'part_otc_sale.color',
                                'part_otc_sale.qty',
                                'part_otc_sale.status',
                                'part.price',
                                DB::raw('IFNULL(sum(payment.amount),0) as paid_amount'),
                                DB::raw('concat(store.name,"-",store.store_type) as storename')
                            )->groupBy('part_otc_sale.id');

        if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('part_otc_sale.customer_name','like',"%".$serach_value."%")
                    ->orwhere('part_otc_sale.model_name','like',"%".$serach_value."%") 
                    ->orwhere('part_otc_sale.customer_mobile','like',"%".$serach_value."%") 
                    ->orwhere('part_otc_sale.color','like',"%".$serach_value."%") 
                    ->orwhere('part_otc_sale.qty','like',"%".$serach_value."%") 
                    ->orwhere('part_otc_sale.status','like',"%".$serach_value."%");
                });
            }
        if(isset($request->input('order')[0]['column']))
            {
                $data = [
                'part_otc_sale.id',
                'part_otc_sale.customer_name',
                'part_otc_sale.customer_mobile',
                'store.name',
                'part_otc_sale.model_name',
                'otc_sale.color',
                'part_otc_sale.qty',
                'part_otc_sale.status',
                'part_otc_sale.price',
                'part_otc_sale.qty',
                'payment.amount',
                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
        else
                $api_data->orderBy('part_otc_sale.id','desc');      
        
        $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

    public function otcSale_update(Request $request) {
       $part_id = $request->input('partId');
       $store_id = $request->input('store_id');
       $otc_id = $request->input('otc_id');
       $qty = $request->input('qty');

       if ($part_id && $store_id) {

           $stock = PartStock::where('store_id',$store_id)->where('part_id',$part_id)->get()->first();
           $stockqty = $stock['quantity'];
           $updateStock = PartStock::where('store_id',$store_id)->where('part_id',$part_id)->update([
                'quantity' => $stockqty-$qty
           ]);
           if ($updateStock) {
               $updateotc = PartOtcSale::where('id',$otc_id)->update([
                'part_id' => $part_id,
                'status' => 'Available'
               ]);
               if ($updateotc == NULL) {
                   DB::rollback();
                   return 'error';
               }else{
                DB::commit();
                return 'success';
               }
           }else{
            DB::rollback();
           }
       }else{
        DB::rollback();
            return 'error';
       }
    }

    public function otcSale_payment($id) {
        $paid = Payment::where('type','PartOtcSale')->where('type_id',$id)->sum('amount');
        $pay_mode = DB::table('master')->where('type','payment_mode')->select('key','value')->get();
        $otcsale = PartOtcSale::leftjoin('part','part_otc_sale.part_id','part.id')
            ->where('part_otc_sale.id',$id)
            ->select('part_otc_sale.qty','part.price','part_otc_sale.store_id')
            ->get()
            ->first();
        $data = array(
            'id' => $id,
            'pay_mode' => $pay_mode,
            'otcsale' => $otcsale,
            'paid' => $paid,
            'layout' => 'layouts.main'
        );
        return view('admin.part.otc_sale_payment',$data);
    }

    public function otcSalePayment_DB(Request $request) {
        try {
            $timestamp = date('Y-m-d G:i:s');
            $this->validate($request,[
                'amount'=>'required',
                'payment_mode'=>'required',
                'transaction_number'=>'required',
                'transaction_charges'=>'required',
                'receiver_bank_detail'=>'required',
            ],[
                'amount.required'=> 'This field is required.', 
                'payment_mode.required'=> 'This field is required.', 
                'transaction_number.required'=> 'This field is required.', 
                'transaction_charges.required'=> 'This field is required.', 
                'receiver_bank_detail.required'=> 'This field is required.', 
            ]);
            DB::beginTransaction();           
                $paid = Payment::where('type','PartOtcSale')->where('type_id',$request->input('otc_id'))->sum('amount');
                $otcsale = PartOtcSale::leftjoin('part','part_otc_sale.part_id','part.id')
                    ->where('part_otc_sale.id',$request->input('otc_id'))
                    ->select('part_otc_sale.qty','part.price')
                    ->get()
                    ->first();

                $total = $otcsale['price']*$otcsale['qty'];
                $paid_amount = $paid + $request->input('amount');                
              
                
                if ($total < $paid_amount) {
                    return redirect('/admin/part/otc/sale/payment/'.$request->input('otc_id'))->with('error','Your Amount too more .');
                }else{
                    $arr = [];
                    if($request->input('payment_mode') == 'Cash')
                    {
                        $arr = [
                            'status' => 'received'
                        ];
                    }
                    $paydata = Payment::insertGetId(
                    array_merge($arr,[
                        'type_id'=> $request->input('otc_id'),
                        'type' => 'PartOtcSale',
                        'payment_mode' => $request->input('payment_mode'),
                        'transaction_number' => $request->input('transaction_number'),
                        'transaction_charges' => $request->input('transaction_charges'),
                        'receiver_bank_detail' => $request->input('receiver_bank_detail'),
                        'amount' => $request->input('amount'),
                        'store_id' => $request->input('store_id'),
                    ])
                    );
                    if($paydata == NULL) {
                        DB::rollback();
                        return redirect('/admin/part/otc/sale/payment/'.$request->input('otc_id'))->with('error','some error occurred');
                    }else{
                        DB::commit();
                        return redirect('/admin/part/otc/sale/payment/'.$request->input('otc_id'))->with('success','Amount Successfully Paid .');
                    }

                }
            
        }  catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return redirect('/admin/part/otc/sale/payment/'.$request->input('otc_id'))->with('error','some error occurred'.$ex->getMessage());
        }
        
    }

    public function otcSalePayment_detail($id) {
        $otcsale = PartOtcSale::leftjoin('part','part_otc_sale.part_id','part.id')
            ->where('part_otc_sale.id',$id)
            ->select('part_otc_sale.qty','part.price')
            ->get()
            ->first();
        $payData = Payment::where('type_id',$id)->where('type','PartOtcSale')->get();
        $data = array(
            'otcsale' => $otcsale,
            'payData' => $payData,
            'layout' => 'layouts.main'
        );
         return view('admin.part.otc_sale_payment_detail',$data);
    }

    public function otcSale_confirm($id) {
        if ($id) {
            $update = PartOtcSale::where('id',$id)->update([
                'status' => 'Sold'
            ]);
            if ($update) {
                return 'success';
            }else{
                return 'error';
            }
        }
    }

    public function PartStockList(){


        $store = Store::whereIn('id',CustomHelpers::user_store())
                    ->select('id',DB::raw('concat(store.name,"-",store.store_type) as name'))->get();
        $data = [
            'store'=>$store,
            'layout' => 'layouts.main'
        ];
        
        return view('admin.part.part_stock_list',$data);
    }

    public function PartStockListApi(Request $request){

        $search = $request->input('search');
        $store_name=$request->input('store_name');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
        
        $store = Store::whereIn('id',CustomHelpers::user_store())
                    ->select('id')->get()->toArray();
        $api_data= PartStock::leftJoin('store','part_stock.store_id','store.id')
            ->whereIn('part_stock.store_id',$store)
            ->leftJoin('part','part_stock.part_id','part.id')
            ->select(
                'part_stock.id',
                'part_stock.quantity',
                'part_stock.min_qty',
                'part_stock.sale_qty',
                'part.part_number',
                'part.price',
                'part.name',

                // 'store.name as store_name',
                DB::raw('concat(store.name,"-",store.store_type) as store_name')


            );

        if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('quantity','like',"%".$serach_value."%")
                    ->orwhere('min_qty','like',"%".$serach_value."%") 
                    ->orwhere('sale_qty','like',"%".$serach_value."%")
                    ->orwhere('part_number','like',"%".$serach_value."%")
                    ->orwhere('price','like',"%".$serach_value."%")
                    ->orwhere('store.name','like',"%".$serach_value."%"); 
                });
            }
        if(!empty($store_name))
            {
               $api_data->where(function($query) use ($store_name){
                        $query->where('part_stock.store_id','like',"%".$store_name."%");
                    });
               
            }
        if(isset($request->input('order')[0]['column']))
            {
                $data = [
                'part.name',
                'part.part_number',
                'part_stock.quantity',
                'part_stock.min_qty',
                'part_stock.sale_qty',
                'part.price',
                'store.store_name'

                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
        else
            $api_data->orderBy('part_stock.id','asc');      
        
        $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

    public function otcSaleGet_frame(Request $request) {
         try{
           DB::beginTransaction();

               $frame = $request->input('frame');
               $registration = $request->input('registration');
               
                // Search service data with frame and registration

               if (!empty($frame)) {
                   $servicedata = ServiceModel::where('frame', 'Like',"%{$frame}%")->select('service.id as service_id','product_id','service.registration','service.frame','service.customer_id')->get()->first();
                    if ($servicedata) {
                       $getProduct = ProductModel::where('id',$servicedata['product_id'])->get()->first();
                       $customer = Customers::where('id',$servicedata['customer_id'])->get()->first();
                        $data = array_merge($servicedata->toArray(),$getProduct->toArray(),$customer->toArray());
                        return response()->json($data);
                   }else{
                    return 'not-found';
                   }
                   
               }else if (!empty($registration)) {
                  $servicedata = ServiceModel::where('registration', $registration)->select('service.id as service_id','product_id','service.registration','service.frame','service.customer_id')->get()->first();
                  if ($servicedata) {
                       $getProduct = ProductModel::where('id',$servicedata['product_id'])->get()->first();
                       $customer = Customers::where('id',$servicedata['customer_id'])->get()->first();
                        $data = array_merge($servicedata->toArray(),$getProduct->toArray(),$customer->toArray());
                        return response()->json($data);
                   }else{
                     return 'not-found';
                   }
               }else{
                 return 'error';
               }
            }catch(\Illuminate\Database\QueryException $ex) {
               return 'error';
            }
    }


    public function CreatePurchaseOrder() {
        $request = PurchaseOrderRequest::leftjoin('part','part.id','purchase_order_request.part_id')
                ->select('part.name','part.price','part.part_number','part.consumption_level','purchase_order_request.*')->where('purchase_order_request.part_id',0)->groupBy('purchase_order_request.model_name')->get();
        $part = Part::all();
        $data = [
            'request'=>$request,
            'part' => $part,
            'layout' => 'layouts.main'
        ];
        
        return view('admin.part.create_po',$data);
    }

    public function GetPartDetails($id) {
        $part = Part::where('id',$id)->get()->first();
        return response()->json($part);
    }

    public function CreatePurchaseOrder_DB(Request $request) {
        try {
            $timestamp = date('Y-m-d G:i:s');
            $po_request = $request->input('po_request');
            if (empty($po_request)) {
               return back()->with('error', 'Please check atleast one box !');
            }
            $count = count($po_request);

            for($i = 0 ; $i < $count ; $i++)
            {
                $part_no  = $request->input('part_number'.$i);
                $part_id  = $request->input('part_id'.$i);
                $part_price  = $request->input('part_price'.$i);
                $part_qty = $request->input('qty'.$i);
                if ($part_qty == null) {
                     return back()->with('error', 'Please enter quantity !');
                }
                $model_name  = $request->input('po_request')[$i];
                $price = $part_price*$part_qty;

                $getdata = Part::where('id',$part_id)->get()->first();
                if($getdata)
                {
                    $po = PurchaseOrder::insertGetId([
                        'amount' => $price,
                        'purchase_order_date' => $timestamp
                        ]);

                    if ($po) {
                        $insertItem = PurchaseOrderItem::insertGetId([
                            'purchase_order_id' => $po,
                            'qty' => $part_qty,
                            'part_id' => $part_id
                        ]);
                        if ($insertItem) {
                            $update = PurchaseOrderRequest::where('model_name',$model_name)->update([
                                'part_id' => $part_id,
                                'po_id' => $po
                            ]);
                            if ($update == null) {
                                DB::rollback();
                                return back()->with('error','Something went wrong !');
                            }
                        }else{
                         DB::rollback();
                        return back()->with('error','Something went wrong !');
                    }
                    }else{
                         DB::rollback();
                        return back()->with('error','Something went wrong !');
                    }
                }else{
                    DB::rollback();
                    return back()->with('error','Something went wrong !');
                }

            }

            if ($update) {
                DB::commit();
                return redirect('/admin/part/purchase/order/create')->with('success','Purchase Order Created Successfully.');
                }
     
        }  catch(\Illuminate\Database\QueryException $ex) {
            return redirect('/admin/part/purchase/order/create')->with('error','some error occurred'.$ex->getMessage());
        }
        
    }

    public function Partmovement(){
            $store = Store::select(
                                'id',
                                DB::raw("concat(name,' - ',store_type) as name")
                            )
                        ->where('active',1)->get();
            $addon=VehicleAddon::get();
            $battery=ProductModel::RightJoin('product_details','product.id','product_details.product_id')->where('product.type','battery')->where('status','<>','sale')->get();

            $new_product_models = ProductModel::select(
                                                'id','model_name',
                                            DB::raw(" concat('owner_manual','_',REPLACE(LOWER(product.model_name),' ','_')) as om_addon")
                                                )
                                            ->groupBy('model_name')->orderBy('model_name')->get()->toArray();

       $data = array(
            'store' => $store,
            'addon'=>$addon,
            'battery'=>$battery,
            'new_product_models'=>$new_product_models,
            'layout' => 'layouts.main'
        );
        return view('admin.part.partmovement',$data);
    }
    public function checkPartAvailable (Request $request) {
       $part_no = $request->input('part_no');
       $store_id =  $request->input('store_id');
       $check = Part::leftjoin('part_stock','part.id','part_stock.part_id')
                       ->where('part.part_number',$part_no)
                       ->where('part_stock.store_id',$store_id)
                       ->where('part_stock.quantity','>',0)
                        ->get()->first();
       if ($check) {
           return response()->json($check);
       }else{
        return "part_error";
       }
       
    }

    public function filter_addonproduct_api(Request $request){

        $from_store=$request->input('store');
    
        $vehicle_addon = VehicleAddon::leftJoin('vehicle_addon_stock','vehicle_addon_stock.vehicle_addon_key','vehicle_addon.key_name')
                                    ->whereRaw("vehicle_addon.key_name <> 'owner_manual'")
                                    ->where('vehicle_addon_stock.store_id',$from_store)
                                    ->select('vehicle_addon.key_name as va_key',
                                        'vehicle_addon.prod_name as va_name',
                                        'vehicle_addon_stock.qty as va_qty'    
                                )->get();

        $battery=ProductModel::RightJoin('product_details','product.id','product_details.product_id')
            ->where('product.type','battery')
            ->where('product_details.store_id',$from_store)
            ->where('status','<>','sale')->get()->toArray();  

        $data = array('vehicle_addon'=>$vehicle_addon,'battery'=>$battery);
               
        return response()->json($data);
    }

    public function filter_addonmodel_api(Request $request){
        $store=$request->input('store');
        $om = $request->input('om_name');

        $qty = VehicleAddonStock::where('vehicle_addon_key',$om)
                            ->where('store_id',$store)
                            ->select('qty')->first();
        $data = array('qty'=>$qty['qty']);
        return response()->json($data);
    }

    public function PartmovementDB(Request $request){
        $valarr = [     
                'fromStore'=>'required',
                'toStore'=>'required'
            ];
            $valmsg = [
                'fromStore.required'=>'This Field is required',
                'toStore.required'=>'This Field is required'
            ];
            $this->validate($request,$valarr,$valmsg);

        try {
            
            DB::beginTransaction();

            $from_store_id = $request->input('fromStore');
            $to_store_id = $request->input('toStore');

            if($from_store_id == $to_store_id){
                DB::rollback();
                return back()->with('error','Error, Movement in same store can not be done')->withInput();
            }
            $part_no = $request->input('part_number');
            $part_qty = $request->input('quantity');
            $addons = $request->input('addon');
            $addon_qty = $request->input('addonqty');
            $battery = $request->input('battery');
            $pm_detail = 0;

            // part insert
            $part_movement = PartMovement::insertGetId([
                    'from_store' => $from_store_id,
                    'to_store' => $to_store_id
            ]);

            if(!$part_movement){
                DB::rollback();
                return back()->with('error','Something Went Wrong')->withInput();
            }

            if(count($part_no)>0){
                $part_count = 0;
                foreach($part_no as $key => $value) {
                    if($value != ""){
                        $getPartDetails = Part::leftjoin('part_stock','part.id','part_stock.part_id')
                            ->select('part.*','part_stock.id as ps_id',
                                'part_stock.part_id',
                                'part_stock.quantity',
                                'part_stock.min_qty','part_stock.sale_qty','part_stock.store_id')
                            ->where('part.part_number',$value)
                            ->where('part_stock.store_id',$from_store_id)
                            ->first();
                            
                        if($getPartDetails != null){
                            $get_moveqty = $part_qty[$part_count];
                            $get_qty = $getPartDetails['quantity'];

                            if($get_moveqty <= 0){
                                DB::rollback();
                                return back()->with('error','Error, Please Enter Part No. Quantity greater than zero.')->withInput();
                            }

                            if($get_qty>=$get_moveqty){

                                $getpart_tostore = PartStock::where('part_id',$getPartDetails['id'])
                                    ->where('store_id',$to_store_id)->first();
                                    
                                $decrement = PartStock::where('part_id',$getPartDetails['id'])
                                    ->where('store_id',$from_store_id)
                                    ->decrement('quantity',$get_moveqty);
                                if($getpart_tostore != null){
                                    // updation for to store
                                    $increment = PartStock::where('part_id',$getPartDetails['id'])
                                    ->where('store_id',$to_store_id)
                                    ->increment('quantity',$get_moveqty);

                                }else{
                                    // Insert for to store
                                    $to_store_qty_insert = PartStock::insertGetId([
                                        'part_id'=> $getPartDetails['id'],
                                        'quantity'=> $get_moveqty,
                                        'store_id'=> $to_store_id
                                    ]);

                                    if($to_store_qty_insert == 0){
                                        DB::rollback();
                                        return back()->with('error','Something Went Wrong')->withInput();
                                    }
                                }

                            }else{
                                DB::rollback();
                                return back()->with('error','Error, Quantity Inserted For Part No "'.$value.'"Is Greater Than Available Quantity.')->withInput();
                            }

                            // partdetail table insert
                            $pm_detail = PartMovementDetails::insertGetId([
                                'part_movement_id' => $part_movement,
                                'type' => 1,
                                'type_id'=>$getPartDetails['id'],
                                'qty' =>$get_moveqty
                            ]);
                            if(!$pm_detail){
                                DB::rollback();
                                return back()->with('error','Error, Something Went Wrong')->withInput();
                            }

                        }else{
                            DB::rollback();
                            return back()->with('error','Error, Part No. '.$value.' not Available')->withInput();
                        }
                        $part_count++;
                    }
                }
            }

            // addons
            $mirror = $request->input('mirror');
            $toolkit = $request->input('toolkit');
            $first_aid_kit = $request->input('first_aid_kit');
            $saree_guard = $request->input('saree_guard');
            $keys = $request->input('bike_keys');

            $stockProcessData = [];

            //if requested qty is greater than 0
            if($mirror > 0){ $stockProcessData['mirror'] = $mirror; }
            if($toolkit > 0){ $stockProcessData['toolkit'] = $toolkit; }
            if($first_aid_kit > 0){ $stockProcessData['first_aid_kit'] = $first_aid_kit; }
            if($saree_guard > 0){ $stockProcessData['saree_guard'] = $saree_guard; }
            if($keys > 0){ $stockProcessData['bike_keys'] = $keys; }

            
            if(count($stockProcessData)>0){
                foreach ($stockProcessData as $name => $moveQty) {

                    $getaddon = VehicleAddon::where('key_name',$name)->first();
                    $getaddon_fromStore = VehicleAddonStock::where('vehicle_addon_key',$name)
                            ->where('store_id',$from_store_id)
                            ->first();
                    if($getaddon_fromStore != null){
                        $qty_present = $getaddon_fromStore['qty'];
                        if($qty_present >= $moveQty){

                            // check for to store addon
                            $getaddon_toStore = VehicleAddonStock::where('vehicle_addon_key',$name)
                            ->where('store_id',$to_store_id)
                            ->first();

                            // from store addon qty decrement
                            $decrement = VehicleAddonStock::where('vehicle_addon_key',$name)
                                ->where('store_id',$from_store_id)
                                ->decrement('qty',$moveQty);

                            if($getaddon_toStore != null){

                                $increment = VehicleAddonStock::where('vehicle_addon_key',$name)
                                ->where('store_id',$to_store_id)
                                ->increment('qty',$moveQty);

                            }else{

                                $insertaddon = VehicleAddonStock::insertGetId([
                                    'vehicle_addon_key'=>$name,
                                    'qty'=>$moveQty,
                                    'store_id'=>$to_store_id,
                                ]);
                                if(!$insertaddon){
                                    DB::rollback();
                                    return back()->with('error','Something Went Wrong')->withInput();
                          
                                }
                            }

                        }else{
                            DB::rollback();
                            return back()->with('error','Error, Enter Quantity For "'.$name.'" Is Greater Than Available Quantity.')->withInput();
                        }

                        // partdetail table insert
                        $pm_detail = PartMovementDetails::insertGetId([
                            'part_movement_id' => $part_movement,
                            'type' => 2,
                            'type_id'=>$getaddon['id'],
                            'qty' =>$moveQty
                        ]);
                        if(!$pm_detail){
                            DB::rollback();
                            return back()->with('error','Error, Something Went Wrong')->withInput();
                        }

                    }else{
                        DB::rollback();
                        return back()->with('error','Error, Addon. '.$name.' not Available')->withInput();
                    }
                    
                }
            }
            
            // owner manual
            $sum_qty =0;
            if(count($addons)>0){
                $om_count = 0;
                foreach($addons as $index => $name){

                    if($name != ''){
                        $getaddon = VehicleAddon::where('key_name','owner_manual')->first();
                        $getaddon_fromStore = VehicleAddonStock::where('vehicle_addon_key',$name)
                                ->where('store_id',$from_store_id)
                                ->first();

                        if($getaddon_fromStore != null){
                            $moveQty = $addon_qty[$om_count];

                            $sum_qty = $sum_qty+$moveQty;

                            if($addon_qty[$om_count]==''){
                                DB::rollback();
                                return back()->with('error','Qty not inserted For Selected')->withInput(); 
                            }
                            $qty_present = $getaddon_fromStore['qty'];
                            if($qty_present >= $moveQty){
                                // check for to store addon
                                $getaddon_toStore = VehicleAddonStock::where('vehicle_addon_key',$name)
                                ->where('store_id',$to_store_id)
                                ->first();
                                // from store addon qty decrement
                                $decrement = VehicleAddonStock::where('vehicle_addon_key',$name)
                                    ->where('store_id',$from_store_id)
                                    ->decrement('qty',$moveQty);

                                if($getaddon_toStore != null){

                                    $increment = VehicleAddonStock::where('vehicle_addon_key',$name)
                                    ->where('store_id',$to_store_id)
                                    ->increment('qty',$moveQty);

                                }else{

                                    $insertaddon = VehicleAddonStock::insertGetId([
                                        'vehicle_addon_key'=>$name,
                                        'qty'=>$moveQty,
                                        'store_id'=>$to_store_id,
                                    ]);
                                    if(!$insertaddon){
                                        DB::rollback();
                                        return back()->with('error','Something Went Wrong')->withInput();
                              
                                    }
                                }

                            }else{
                                DB::rollback();
                                return back()->with('error','Error, Qunatity Inserted For '.$name.' Is Greater Than Available Stock Qunatity.')->withInput();
                            }

                            // partdetail table insert
                            $pm_detail = PartMovementDetails::insertGetId([
                                'part_movement_id' => $part_movement,
                                'type' => 2,
                                'type_id'=>$getaddon['id'],
                                'addon_name'=> $name,
                                'qty' =>$moveQty
                            ]);
                            if(!$pm_detail){
                                DB::rollback();
                                return back()->with('error','Error, Something Went Wrong')->withInput();
                            }
                        }else{

                            DB::rollback();
                            return back()->with('error','Error, Addon. '.$name.' not Found')->withInput();
                        }
                        $om_count++;
                    }

                }
                
            }

            if($sum_qty > 0){

                $getaddon_toStore = VehicleAddonStock::where('vehicle_addon_key','owner_manual')
                                ->where('store_id',$to_store_id)
                                ->first();
                                // from store addon qty decrement
                $decrement = VehicleAddonStock::where('vehicle_addon_key','owner_manual')
                                    ->where('store_id',$from_store_id)
                                    ->decrement('qty',$sum_qty);
                if($getaddon_toStore != null){

                    $increment = VehicleAddonStock::where('vehicle_addon_key','owner_manual')
                    ->where('store_id',$to_store_id)
                    ->increment('qty',$sum_qty);

                }else{

                    $insertaddon = VehicleAddonStock::insertGetId([
                        'vehicle_addon_key'=>'owner_manual',
                        'qty'=>$sum_qty,
                        'store_id'=>$to_store_id,
                    ]);
                    if(!$insertaddon){
                        DB::rollback();
                        return back()->with('error','Something Went Wrong')->withInput();
              
                    }
                }
            }
            
            
            if(isset($battery)){

                $new_battery_ar= array();
                foreach ($battery as $key => $value) {
                    $new_battery_ar = array_merge($new_battery_ar,$value);
                }
                
                $battery_new_arr = array_count_values($new_battery_ar);
                foreach ($battery_new_arr as $name => $battery_qty) {

                    $getProductId = ProductDetails::where('id',$name)
                                    ->where('store_id',$from_store_id)
                                    ->first();
                                    
                    if($getProductId != null){
                        
                        if($battery_qty == 1){

                            $updateProductDetails = ProductDetails::where('id',$name)
                                                    ->where('store_id',$from_store_id)
                                                    ->update([
                                                        'store_id'  =>  $to_store_id,
                                                        'move_status'   =>  1
                                                    ]);

                            $updateStockQty_dec = Stock::where('product_id',$getProductId->product_id)
                                            ->where('store_id',$from_store_id)
                                            ->decrement('quantity',1);

                            $check_inc = Stock::where('product_id',$getProductId->product_id)
                                            ->where('store_id',$to_store_id)->first();
                            if(isset($check_inc->id))
                            {
                                // increment update
                                $updateStockQty_inc = Stock::where('product_id',$getProductId->product_id)
                                                        ->where('store_id',$to_store_id)
                                                        ->increment('quantity',1);
                            }
                            else{
                                // increment insert
                                $updateStockQty_inc = Stock::insertGetId([
                                    'product_id'    =>  $getProductId->product_id,
                                    'quantity'  =>  1,
                                    'store_id'  =>  $to_store_id
                                ]);
                                if(!$updateStockQty_inc){
                                    DB::rollback();
                                    return back()->with('error','Error, Something Went Wrong')->withInput();
                                }
                            }

                            $pm_detail = PartMovementDetails::insertGetId([
                                'part_movement_id' => $part_movement,
                                'type' => 3,
                                'type_id'=>$getProductId->id,
                                'qty' =>$battery_qty
                            ]);
                            if(!$pm_detail){
                                DB::rollback();
                                return back()->with('error','Error, Something Went Wrong')->withInput();
                            }
                        }else{

                            DB::rollback();
                            return back()->with('error','Error, Same '.$getProductId['frame'].' battery selected twice')->withInput();
                        }
                    }
                    else{

                        $ntProduct = ProductDetails::where('id',$name)
                                    ->first();
                        DB::rollback();
                        return back()->with('error','Battery  "'.$ntProduct['frame'].'"  is not Available!!')->withInput();
                    }
                        
                }
                    
            }

            if(!$pm_detail){
                DB::rollback();
                return back()->with('error','No Movement done Please select any For Movement!!')->withInput();
            }

        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollback();
            return back()->with('error','Something Went Wrong'.$e)->withInput();
        }
        CustomHelpers::userActionLog($action='Part Movement',$part_movement,0);
        DB::commit();
        return redirect('/admin/part/movement')->with("success",'Submit Successfully');
    }

    public function part_movement_summary(){
        $data =array('layout'=>'layouts.main');
        return view('admin.part.partmovement_summary',$data);
    }
    public function part_movement_summary_api(Request $request){
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;

        $part_move = PartMovement::leftJoin('store as fr_store','fr_store.id','part_movement.from_store')
                ->leftJoin('store as to_store','to_store.id','part_movement.to_store')
                ->leftjoin('users','users.id','part_movement.moved_by')
                ->select('part_movement.id',
                    DB::Raw("concat(fr_store.name,' - ',fr_store.store_type) as fromStore"),
                    DB::Raw("concat(to_store.name,' - ',to_store.store_type) as toStore"),
                    DB::Raw('Date_Format(part_movement.moved_date,"%d-%m-%Y") as moved_date'),
                    'part_movement.moved_by','part_movement.status',
                    DB::Raw("concat(IfNull(users.name,''),' ',IfNull(users.middle_name,''),' ',IfNull(users.last_name,'')) as userName"),DB::Raw('Date_Format(part_movement.created_at,"%d-%m-%Y") as created_date'));

        if(!empty($serach_value))
            {
                $part_move->where(function($query) use ($serach_value){
                    $query->where('fr_store.store_type','like',"%".$serach_value."%")
                    ->orwhere('fr_store.name','like',"%".$serach_value."%")
                    ->orwhere('part_movement.status','like',"%".$serach_value."%")
                    ->orwhere('users.name','like',"%".$serach_value."%"); 
                });
            }

        if(isset($request->input('order')[0]['column']))
            {
                $data = [
                    'part_movement.id',
                    'created_date',
                'fromStore',
                'toStore',
                'moved_date',
                'userName',
                'part_movement.status'
                ];

                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $part_move->orderBy($data[$request->input('order')[0]['column']], $by);
            }
        else
            $part_move->orderBy('part_movement.id','desc'); 

        $count = count( $part_move->get()->toArray());
        $part_move = $part_move->offset($offset)->limit($limit)->get()->toArray();

        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $part_move; 
        return json_encode($array);

    }
    public function part_movement_update($id){
        try {

            DB::beginTransaction();

            $get_data = PartProcess::where('id',$id)->first();
            if($get_data){
                $divide_id = explode(',',$get_data['part_process_detail_ids']);

                
                $update_part_process = PartProcessDetails::whereIn('id',$divide_id)->update([
                    'status' => 'Moved'
                ]);
                

                $get_ppd = PartProcessDetails::whereIn('id',$divide_id)->get()->toArray();
                if(count($get_ppd)==0){
                    DB::rollback();
                    return "Process Details Not Found.";
                }

                $get_pmd = PartMovementDetails::where('part_movement_details.part_movement_id',$get_ppd[0]['part_movement_id'])
                        ->leftjoin('part_process_details','part_process_details.part_movement_details_id','part_movement_details.id')
                        ->select('part_movement_details.id',
                            'part_movement_details.part_movement_id',
                            DB::raw('part_movement_details.qty - sum(IfNull(part_process_details.qty,0)) as qty')
                        )->groupBy('part_movement_details.id')
                        ->get()->toArray();
                
                $pps_status = PartProcessDetails::where('part_movement_id',$get_ppd[0]['part_movement_id'])
                            ->get()->toArray();

                
                $qty_array = array_column($get_pmd, 'qty');
                $check_temp = array_filter($qty_array);
                
                $check_status = array_column($pps_status, 'status');

                if(empty($check_temp) && !in_array("process", $check_status)){
                    $upd = PartMovement::where('part_movement.id',$get_ppd[0]['part_movement_id'])->update([
                        'status' => 'Moved',
                        'moved_date' => date('Y-m-d'),
                        'moved_by'=> Auth::id()
                    ]);
                }

            }
            $update_part = PartProcess::where('id',$id)->update([
                'status' => 'Moved',
                'moved_date' =>date('Y-m-d'),
                'moved_by' => Auth::id()
            ]);

            if($update_part == null){
                DB::rollback();
                return "Something went wrong.";
            }else{
                CustomHelpers::userActionLog($action='Part Movement Process Update',$update_part,0);
                DB::commit();
                return 'success';
            }
            
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollback();
            return 'Something Went Wrong'.$e;
        }
    }

    public function part_movement_process($id){
        $part_movement = PartMovement::where('part_movement.id',$id)->leftJoin('store as fr_store','fr_store.id','part_movement.from_store')
                ->leftJoin('store as to_store','to_store.id','part_movement.to_store')
                ->select('part_movement.id',
                    'part_movement.status',
                    'part_movement.from_store',
                    DB::Raw("concat(fr_store.name,' - ',fr_store.store_type) as fromStore"),
                    DB::Raw("concat(to_store.name,' - ',to_store.store_type) as toStore"))->first();

        $part_m_detail = PartMovementDetails::where('part_movement_details.part_movement_id',$id)
                        ->leftjoin('part_process_details','part_process_details.part_movement_details_id','part_movement_details.id')
                        ->select('part_movement_details.id',
                            'part_movement_details.part_movement_id',
                            'part_movement_details.type',
                            'part_movement_details.type_id',
                            'part_movement_details.addon_name',
                            DB::raw('part_movement_details.qty - sum(IfNull(part_process_details.qty,0)) as qty'),
                            DB::raw('sum(IfNull(part_process_details.qty,0)) as processed_qty'),
                            DB::raw('part_movement_details.qty as move_qty')
                        )->groupBy('part_movement_details.id')
                        ->get()->toArray();

        $loader = Loader::where('status','Active')->get(['id','truck_number'])->toArray();

        $addon =array();
        $part =array();
        $battery =array();
        
        if(count($part_m_detail)>0){

           foreach ($part_m_detail as $key => $data) {
                if($data['type'] == 1){
                    $part[$key] = $data;
                }else if($data['type'] == 2){
                    $addon[$key] = $data;
                }else if($data['type'] == 3){
                    $battery[$key] = $data;
                }
            } 

            foreach ($addon as $key => &$addons) {
                if($addons['type_id'] == 3){
                    
                    $name = $addons['addon_name'];

                }else{
                    $vehicle_addon = VehicleAddon::where('id',$addons['type_id'])->get()->first();

                    $name = $vehicle_addon['key_name'];
                }
                $addons['display_name'] = $name;

            }
           
            foreach ($part as $key => &$parts) {
                $part_query = Part::leftjoin('part_stock','part.id','part_stock.part_id')
                                ->select('part.part_number','part.name','part.id')
                                ->where('part.id',$parts['type_id'])
                                ->first();
                $parts['partName'] = $part_query['part_number'];
            }

            foreach ($battery as $key => &$value) {
                $battery_name = ProductDetails::where('id',$value['type_id'])
                                    ->first();
                $value['display_name'] = $battery_name['frame'];
            }
            
            $data = array(
                'loader' => $loader,
                'fromStore'=>$part_movement['fromStore'],
                'toStore'=>$part_movement['toStore'],
                'status' =>$part_movement['status'],
                'part_movement'=>$part_movement,
                'parts' =>$part,
                'battery' =>$battery,
                'addon' =>$addon,
                'part_movement_id' =>$id,
                'layout'=>'layouts.main'
            );

            return view('admin.part.partprocessform',$data);
        }else{
            return back()->with('error','No Part Movement Details Found!!');
        }
        
    }
    public function part_movement_process_db(Request $request){
        $valarr = [   
            'loader'=>'required',
        ];
        $valmsg = [
            'loader.required'=>'This Field is required',
            
        ];
        
        $loader = Loader::where('id',$request->input('loader'))->first();
        if(isset($loader->id)){
            if ($loader->truck_number == 'ByRoad') {
                $valarr = array_merge($valarr,[     
                'fuel'=>'required|numeric|between:0,1',
                ]);
                $valmsg = array_merge($valmsg,[
                    'fuel.required'=>'This Field is required',
                ]);
            }
        }
        
        $this->validate($request,$valarr,$valmsg);
        try {
            $table = ['part_process_details','part_process'];
            CustomHelpers::resetIncrement($table);
            DB::beginTransaction();

            $from_store_id = $request->input('from_store');

            if(!isset($loader->id)){
                return back()->with('error','Loader Not Found.')->withInput();
            }

            $checkfuelStock = FuelStockModel::where('fuel_type','Petrol')
                                ->where('store_id',$from_store_id)->first();
            if ($loader->truck_number == 'ByRoad') {
                if(isset($checkfuelStock->id)){
                    if($checkfuelStock->quantity < $request->input('fuel')){
                        return back()->with('error','Fuel Stock is not Available.')->withInput();
                    }
                }else{
                    return back()->with('error','Fuel Stock is not Available.')->withInput();
                }
            }

            $loader_id = $request->input('loader');

            $part_movement_id = $request->input('part_movement_id');
            
            $addons = $request->input('addon');

            $part_number = $request->input('part_number');
            $quantity = $request->input('quantity');

            $battery = $request->input('battery');

            $process_ids = [];

            if(isset($part_number) && isset($quantity) && count($part_number) == count($quantity)){
                $part_array = array_combine($part_number, $quantity);
                foreach ($part_array as $pmd_id => $qty) {
                    if($qty > 0){
                        $check_qty = PartMovementDetails::leftjoin('part_process_details','part_process_details.part_movement_details_id','part_movement_details.id')
                            ->select('part_movement_details.id',
                                'part_movement_details.type',
                                'part_movement_details.type_id',
                                DB::raw('part_movement_details.qty - sum(IfNull(part_process_details.qty,0)) as qty')
                            )->groupBy('part_movement_details.id')->where('part_movement_details.id',$pmd_id)
                            ->first();

                        $left_qty = $check_qty['qty'];
                        $enter_qty = $qty;

                        if($left_qty>= $enter_qty){

                            $to_store_qty_insert = PartProcessDetails::insertGetId([
                                            'part_movement_id'=> $part_movement_id,
                                            'part_movement_details_id'=> $pmd_id,
                                            'type'=> $check_qty['type'],
                                            'type_id'=>$check_qty['type_id'],
                                            'qty'=>$enter_qty
                                        ]);
                            if($to_store_qty_insert == 0){
                                DB::rollback();
                                return back()->with('error','Something Went Wrong')->withInput();
                            }

                            $process_ids = array_merge($process_ids,array($to_store_qty_insert));
                        }else{
                            DB::rollback();
                            return back()->with('error','Error, Wrong Qty Enter in Part.')->withInput(); 
                        }
                    }
                }
            }

            if(isset($addons) && count($addons)>0){
                foreach ($addons as $pmd_id => $qty) {
                    if($qty > 0){
                        $check_qty = PartMovementDetails::leftjoin('part_process_details','part_process_details.part_movement_details_id','part_movement_details.id')
                            ->select('part_movement_details.id',
                                'part_movement_details.type',
                                'part_movement_details.type_id',
                                DB::raw('part_movement_details.qty - sum(IfNull(part_process_details.qty,0)) as qty')
                            )->groupBy('part_movement_details.id')->where('part_movement_details.id',$pmd_id)
                            ->first();
                        $left_qty = $check_qty['qty'];
                        $enter_qty = $qty;

                        if($left_qty>= $enter_qty){

                            $to_store_qty_insert = PartProcessDetails::insertGetId([
                                            'part_movement_id'=> $part_movement_id,
                                            'part_movement_details_id'=> $pmd_id,
                                            'type'=> $check_qty['type'],
                                            'type_id'=>$check_qty['type_id'],
                                            'qty'=>$enter_qty
                                        ]);
                            if($to_store_qty_insert == 0){
                                DB::rollback();
                                return back()->with('error','Something Went Wrong')->withInput();
                            }

                            $process_ids = array_merge($process_ids,array($to_store_qty_insert));
                        }else{
                            DB::rollback();
                            return back()->with('error','Error, Wrong Qty Enter in Addon.')->withInput(); 
                        }
                    }     
                }
            }
            
            if(isset($battery) && count($battery)>0){
                foreach ($battery as $index => $pmd_id) {
                
                    $check_qty = PartMovementDetails::leftjoin('part_process_details','part_process_details.part_movement_details_id','part_movement_details.id')
                        ->select('part_movement_details.id',
                            'part_movement_details.type',
                            'part_movement_details.type_id',
                            DB::raw('part_movement_details.qty - sum(IfNull(part_process_details.qty,0)) as qty')
                        )->groupBy('part_movement_details.id')->where('part_movement_details.id',$pmd_id)
                        ->first();
                    $left_qty = $check_qty['qty'];
                    $enter_qty = 1;

                    if($left_qty>= $enter_qty){

                        $to_store_qty_insert = PartProcessDetails::insertGetId([
                                        'part_movement_id'=> $part_movement_id,
                                        'part_movement_details_id'=> $pmd_id,
                                        'type'=> $check_qty['type'],
                                        'type_id'=>$check_qty['type_id'],
                                        'qty'=>$enter_qty
                                    ]);
                        if($to_store_qty_insert == 0){
                            DB::rollback();
                            return back()->with('error','Something Went Wrong')->withInput();
                        }

                        $process_ids = array_merge($process_ids,array($to_store_qty_insert));
                    }else{
                        DB::rollback();
                        return back()->with('error','Error, Wrong Qty Enter in Battery.')->withInput(); 
                    }
                       
                }
            }

            $update_part_movement = PartMovementDetails::where('part_movement_details.part_movement_id',$part_movement_id)
                        ->leftjoin('part_process_details','part_process_details.part_movement_details_id','part_movement_details.id')
                        ->select('part_movement_details.id',
                            'part_movement_details.part_movement_id',
                            DB::raw('part_movement_details.qty - sum(IfNull(part_process_details.qty,0)) as qty')
                        )->groupBy('part_movement_details.id')
                        ->get()->toArray();

            $qty_array = array_column($update_part_movement, 'qty');
            $check_temp = array_filter($qty_array);
            
            if(empty($check_temp)){
                $upd = PartMovement::where('part_movement.id',$part_movement_id)->update([
                    'status' => 'Process'
                ]);
            }

            if ($loader->truck_number == 'ByRoad') {
                if(!isset($checkfuelStock->id)){
                    DB::rollback();
                    return back()->with('error','Fuel Stock is not Available.')->withInput();
                }
                $addfuel = FuelModel::insertGetId([
                    'type_id' => $part_movement_id,
                    'store_id' => $from_store_id,
                    'fuel_mode'    =>  'Transfer',
                    'fuel_type'    =>  'Petrol',
                    'quantity' => $request->input('fuel'),
                    'requested_by' => Auth::id()
                ]);
                $fuelStock = FuelStockModel::where('id',$checkfuelStock->id)->decrement('quantity',$request->input('fuel'));
            }

            if(count($process_ids)>0){
                $ids = implode(',', $process_ids);
                $entry = PartProcess::insertGetId([
                                        'part_process_detail_ids'=> $ids,
                                        'status'=> 'Pending',
                                        'loader_id'=>$loader_id
                                    ]);
                if($entry == 0){
                    DB::rollback();
                    return back()->with('error','Something Went Wrong In Creating Process.')->withInput();
                }
            }else{
                DB::rollback();
                return back()->with('error','No Process Done, Please Enter Quantity For Process.')->withInput(); 
            }

        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollback();
            return back()->with('error','Something Went Wrong'.$e)->withInput();
        }
        CustomHelpers::userActionLog($action='Part Movement Process',$entry,0);
        DB::commit();
        return redirect('/admin/part/movement/process/summary')->with("success",'Part Movement Process Done Successfully');
    }

    public function part_movement_process_summary(){
        $data =array('layout'=>'layouts.main');
        return view('admin.part.partmovementprocess_summary',$data);
    }

    public function part_movement_process_summary_api(Request $request){
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;

        $part_move = PartProcess::leftjoin('part_process_details',function($join){
                $join->on(DB::raw('find_in_set(part_process_details.id,part_process.part_process_detail_ids)'),'>',DB::raw("0"));
            })->leftjoin('part_movement','part_movement.id','part_process_details.part_movement_id')
            ->leftJoin('store as fr_store','fr_store.id','part_movement.from_store')
            ->leftJoin('store as to_store','to_store.id','part_movement.to_store')
            ->leftjoin('users','users.id','part_process.moved_by')
            ->leftJoin('loader','loader.id','part_process.loader_id')
            ->select('part_process.id as process_id',
                'part_process.part_process_detail_ids',
                'part_process.status',
                'part_process.moved_by',
                DB::raw('date_format(part_process.moved_date,"%d-%m-%Y") as moved_date'),
                'part_process_details.part_movement_id',
                DB::Raw("concat(IfNull(users.name,''),' ',IfNull(users.middle_name,''),' ',IfNull(users.last_name,'')) as userName"),
                DB::Raw("concat(fr_store.name,' - ',fr_store.store_type) as fromStore"),
                DB::Raw("concat(to_store.name,' - ',to_store.store_type) as toStore"),
                DB::raw('loader.truck_number as loader_truck')
            )
            ->groupBy('part_process.id');

            
        if(!empty($serach_value))
            {
                $part_move->where(function($query) use ($serach_value){
                    $query->where('fr_store.store_type','like',"%".$serach_value."%")
                    ->orwhere('fr_store.name','like',"%".$serach_value."%")
                    ->orwhere('part_process.status','like',"%".$serach_value."%")
                    ->orwhere('loader.truck_number','like',"%".$serach_value."%")
                    ->orwhere('users.name','like',"%".$serach_value."%");
                });
            }

        if(isset($request->input('order')[0]['column']))
            {
                $data = [
                    'process_id',
                    'loader_truck',
                    'fromStore',
                    'toStore',
                    'part_process_detail_ids',
                    'moved_date',
                    'userName',
                    'part_process.status'
                ];

                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $part_move->orderBy($data[$request->input('order')[0]['column']], $by);
            }
        else
            $part_move->orderBy('process_id','asc'); 

        $count = count( $part_move->get()->toArray());
        $part_move = $part_move->offset($offset)->limit($limit)->get()->toArray();

            foreach ($part_move as $index => &$value) {
                $ids_array = explode(',', $value['part_process_detail_ids']);
                $part_process_details = PartProcessDetails::whereIn('part_process_details.id',$ids_array)
                                    ->leftjoin('part_movement_details','part_process_details.part_movement_details_id','part_movement_details.id')
                                    ->select('part_movement_details.id',
                                        'part_movement_details.type',
                                        'part_movement_details.type_id',
                                        'part_movement_details.addon_name',
                                        'part_process_details.qty')->get()->toArray();
                $parts =[];
                foreach ($part_process_details as $process_index => $process_data) {
                    if($process_data['type'] == 1){
                        $part_query = Part::leftjoin('part_stock','part.id','part_stock.part_id')
                                ->select('part.part_number','part.name','part.id')
                                ->where('part.id',$process_data['type_id'])
                                ->first();
                        $parts = array_merge($parts,array($part_query['part_number']."(".$process_data['qty'].")"));

                    }else if($process_data['type'] == 2){
                        if($process_data['type_id'] == 3){
                        
                            $name = $process_data['addon_name'];

                        }else{
                            $vehicle_addon = VehicleAddon::where('id',$process_data['type_id'])->get()->first();
                                
                            $name = $vehicle_addon['key_name'];
                        }
                        $parts = array_merge($parts,array($name."(".$process_data['qty'].")"));
                        
                    }else if($process_data['type'] == 3){
                        $battery_name = ProductDetails::where('id',$process_data['type_id'])
                                    ->first();
                        
                        $parts = array_merge($parts,array($battery_name['frame']."(".$process_data['qty'].")"));
                    }

                }

                $string_parts_qty = implode(', ', $parts);
                $value['parts_qty'] = $string_parts_qty;
            }
        
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $part_move; 
        return json_encode($array);
    }

    public function part_movement_cancel(Request $request){

        $part_move_id = $request->input('partsMoveid');

        $not_process = PartMovementDetails::where('part_movement_details.part_movement_id',$part_move_id)->leftjoin('part_movement','part_movement_details.part_movement_id','part_movement.id')
                        ->where('part_movement.status','<>','Moved')
                        ->leftjoin('part_process_details',function($join){
                            $join->on('part_process_details.part_movement_details_id','part_movement_details.id')
                            
                            ;
                        })
                        ->select('part_movement_details.id',
                            'part_movement.from_store',
                            'part_movement.to_store',
                            'part_movement_details.part_movement_id',
                            'part_movement_details.type',
                            'part_movement_details.type_id',
                            'part_movement_details.addon_name',
                            DB::raw('part_movement_details.qty - sum(IfNull(part_process_details.qty,0)) as qty'),
                            DB::raw('sum(IfNull(part_process_details.qty,0)) as processed_qty'),
                            DB::raw('part_movement_details.qty as move_qty')
                        )->groupBy('part_movement_details.id')
                        ->get()->toArray();

        $addon =array();
        $part =array();
        $battery =array();
        
        if(count($not_process)>0){

           foreach ($not_process as $key => $data) {
                if($data['type'] == 1){
                    $part[$key] = $data;
                }else if($data['type'] == 2){
                    $addon[$key] = $data;
                }else if($data['type'] == 3){
                    $battery[$key] = $data;
                }
            } 

            foreach ($addon as $key => &$addons) {
                if($addons['type_id'] == 3){
                    
                    $name = $addons['addon_name'];

                }else{
                    $vehicle_addon = VehicleAddon::where('id',$addons['type_id'])->get()->first();

                    $name = $vehicle_addon['key_name'];
                }

                $addon_qty_used = VehicleAddonStock::where('vehicle_addon_key',$name)
                            ->where('store_id',$addons['to_store'])
                            ->first();

                if($addon_qty_used['qty'] < $addons['qty']){
                    $not_cancel_addon = $addons['qty']-$addon_qty_used['qty'];
                    $addons['qty'] = $addons['qty']-$not_cancel_addon;
                }
                           
                $addons['display_name'] = $name;
                
            }
            
            foreach ($part as $key => &$parts) {
                $part_query = Part::select('part.part_number','part.name','part.id')
                                ->where('part.id',$parts['type_id'])
                                ->first();
                
                $check_qty_used = Part::leftjoin('part_stock','part.id','part_stock.part_id')
                                ->select('part_stock.quantity')
                                ->where('part.id',$parts['type_id'])
                                ->where('part_stock.store_id',$parts['to_store'])
                                ->first();
                
                if($check_qty_used['quantity'] < $parts['qty']){
                    $not_cancel_part = $parts['qty']-$check_qty_used['quantity'];
                    $parts['qty'] = $parts['qty']-$not_cancel_part;
                }
                $parts['partName'] = $part_query['part_number'];
            }
            
            foreach ($battery as $key => &$value) {
                $battery_name = ProductDetails::where('id',$value['type_id'])
                                    ->first();

                $battery_qty = ProductDetails::where('id',$value['type_id'])
                                ->select('frame','id')->where('status','sale')->where('move_status',1)->get();

                if(isset($battery_qty[0])){
                    $value['qty'] = 0;
                }

                $value['display_name'] = $battery_name['frame'];
            }

            $processed_data = PartProcess::leftjoin('part_process_details',function($join){
                        $join->on(DB::raw('find_in_set(part_process_details.id,part_process.part_process_detail_ids)'),'>',DB::raw("0"));
                    })->leftJoin('loader','loader.id','part_process.loader_id')
                    ->where('part_process_details.part_movement_id',$part_move_id)
                    ->select(DB::raw('loader.truck_number as loader_truck'),
                        DB::raw('date_format(part_process.created_at,"%d-%m-%Y") as process_date'),
                        'part_process_detail_ids','part_process.id'
                    )->where('part_process.status','Pending')
                    ->groupBy('part_process.id')
                    ->get();

            foreach ($processed_data as $index => &$value) {
                $ids_array = explode(',', $value['part_process_detail_ids']);
                $part_process_details = PartProcessDetails::whereIn('part_process_details.id',$ids_array)
                            ->leftjoin('part_movement_details','part_process_details.part_movement_details_id','part_movement_details.id')
                            ->select('part_movement_details.id',
                                'part_movement_details.type',
                                'part_movement_details.type_id',
                                'part_movement_details.addon_name',
                                'part_process_details.qty')->get()->toArray();
                $processparts =[];
                foreach ($part_process_details as $process_index => $process_data) {
                    if($process_data['type'] == 1){
                        $part_query = Part::leftjoin('part_stock','part.id','part_stock.part_id')
                                ->select('part.part_number','part.name','part.id')
                                ->where('part.id',$process_data['type_id'])
                                ->first();
                        $processparts['parts'][] = array('name'=>$part_query['part_number'],
                            'quantity'=>$process_data['qty']);

                    }else if($process_data['type'] == 2){
                        if($process_data['type_id'] == 3){
                        
                            $name = $process_data['addon_name'];

                        }else{
                            $vehicle_addon = VehicleAddon::where('id',$process_data['type_id'])->get()->first();
                                
                            $name = $vehicle_addon['key_name'];
                        }
                        $processparts['addon'][] =array('name'=>$name,'quantity'=>$process_data['qty']);
                        
                        
                    }else if($process_data['type'] == 3){
                        $battery_name = ProductDetails::where('id',$process_data['type_id'])
                                    ->first();
                        
                        $processparts['battery'][] = array('name'=>$battery_name['frame'],'quantity'=>$process_data['qty']);
                    }

                }

                $value['all_parts'] = $processparts;
            }
            
            $data = array(
                'parts_not_process' =>$part,
                'battery_not_process' =>$battery,
                'addon_not_process' =>$addon,
                'processed_data' => $processed_data,
                'part_movement_id' =>$part_move_id,
                'layout'=>'layouts.main'
            );
            
            return view('admin.part.partmovement_cancel',$data);
        }else{
            return back()->with('error','No Part Movement Details Found!!');
        }
    }

    public function part_movement_cancelDB(Request $request){

        $part_move_id = $request->input('partsMoveid');

        $pm_data = PartMovement::where('part_movement.id',$part_move_id)
                        ->where('part_movement.status','<>','moved')->first();
        if(!isset($pm_data->id)){
            return back()->with('error','Part Movement Not Found.')->withInput();
        }

        $pmd_data_temp = PartMovementDetails::where('part_movement_id',$part_move_id)
                                        ->get();
        if(!isset($pmd_data_temp[0])){
            return back()->with('error','Part Movement Product Not Found.')->withInput();
        }

        $parts_id = $request->input('part_number');
        $part_qty = $request->input('quantity');
        $part_ar =[];
        if(isset($parts_id) && isset($part_qty) && count($parts_id) && count($part_qty)){
            $part_ar = array_combine($parts_id, $part_qty);

        }
        $addon  = $request->input('addon');
        $battery = $request->input('battery');
        $processed_id = $request->input('processed_id');

        try {
            
            DB::beginTransaction();

            $from_store_id = $pm_data['from_store'];
            $to_store_id = $pm_data['to_store'];

            $update_something = [];

            if(isset($processed_id) && count($processed_id)){
                foreach ($processed_id as $key => $process_id) {
                    $part_pro =PartProcess::leftJoin('loader','loader.id','part_process.loader_id')->where('part_process.id',$process_id)->where('part_process.status','Pending')
                        ->select(DB::raw('loader.truck_number as loader_truck'),
                            DB::raw('date_format(part_process.created_at,"%d-%m-%Y") as process_date'),
                            'part_process.part_process_detail_ids','part_process.id'
                        )->first();
                    if(isset($part_pro->id)){
                        $part_process_details = PartProcessDetails::whereIn('part_process_details.id',explode(',', $part_pro['part_process_detail_ids']))
                            ->where('part_process_details.part_movement_id',$part_move_id)
                            ->leftjoin('part_movement_details','part_process_details.part_movement_details_id','part_movement_details.id')
                            ->select('part_process_details.id',
                                'part_movement_details.type',
                                'part_movement_details.id as pmd_id',
                                'part_movement_details.type_id',
                                'part_movement_details.addon_name',
                                'part_process_details.qty')->get()->toArray();

                        if(count($part_process_details)==0){
                            DB::rollback();
                            return back()->with('error','Process data Not Found')->withInput();
                        }

                        $not_ff_err = [];
                        $all_process_arr = [];
                        foreach ($part_process_details as $process_index => $process_data) {
                            if($process_data['type'] == 1){
                                $part_check = Part::leftjoin('part_stock','part.id','part_stock.part_id')
                                        ->select('part_stock.quantity','part_stock.part_id as ps_part_id','part.part_number')
                                        ->where('part.id',$process_data['type_id'])
                                        ->where('part_stock.store_id',$to_store_id)
                                        ->first();
                                if($part_check['quantity'] < $process_data['qty']){
                                    $not_ff_err[] =array("loader"=>$part_pro['loader_truck'],"part"=>$part_check['part_number']); 
                                }else{
                                    
                                    $part_inc = PartStock::where('part_id',$part_check['ps_part_id'])
                                                ->where('store_id',$from_store_id)
                                                ->increment('quantity',$process_data['qty']);
                                    $part_dec = PartStock::where('part_id',$part_check['ps_part_id'])
                                                ->where('store_id',$to_store_id)
                                                ->decrement('quantity',$process_data['qty']);
                                    $pps_upd = PartProcessDetails::where('id',$process_data['id'])
                                                ->update(['status'=>'cancel']);
                                    $pps_dec =  PartProcessDetails::where('id',$process_data['id'])
                                                ->decrement('qty',$process_data['qty']);
                                    $pmd_dec = PartMovementDetails::where('id',$process_data['pmd_id'])
                                                ->decrement('qty',$process_data['qty']);  

                                    if(!$part_inc && !$part_dec && !$pps_upd && !$pps_dec && !$pmd_dec){
                                        DB::rollback();
                                        return back()->with('error','Something Went Wrong Part Not Cancel')->withInput();
                                    }
                                }
                            }else if($process_data['type'] == 2){
                                if($process_data['type_id'] == 3){
                                
                                    $name = $process_data['addon_name'];

                                }else{
                                    $vehicle_addon = VehicleAddon::where('id',$process_data['type_id'])->get()->first();
                                        
                                    $name = $vehicle_addon['key_name'];
                                }
                               
                                $addon_qty_used = VehicleAddonStock::where('vehicle_addon_key',$name)
                                            ->where('store_id',$to_store_id)
                                            ->first();
                                if($addon_qty_used['qty'] < $process_data['qty']){
                                    $not_ff_err[] = array("loader"=>$part_pro['loader_truck'],"part"=>$name);  
                                }else{

                                    $veh_inc = VehicleAddonStock::where('vehicle_addon_key',$name)
                                                ->where('store_id',$from_store_id)
                                                ->increment('qty',$process_data['qty']);

                                    $veh_dec = VehicleAddonStock::where('vehicle_addon_key',$name)
                                                ->where('store_id',$to_store_id)
                                                ->decrement('qty',$process_data['qty']);

                                    if($process_data['type_id'] == 3){
                        
                                        $owner_to_dec = VehicleAddonStock::where('vehicle_addon_key','owner_manual')
                                        ->where('store_id',$to_store_id)
                                        ->decrement('qty',$process_data['qty']);
                                        
                                        $owner_fr_inc = VehicleAddonStock::where('vehicle_addon_key','owner_manual')
                                            ->where('store_id',$from_store_id)
                                            ->increment('qty',$process_data['qty']);

                                        if(!$owner_to_dec && !$owner_fr_inc){
                                            DB::rollback();
                                            return back()->with('error','Something Went Wrong Addons Not Cancel')->withInput();
                                        }

                                    }

                                    $pps_upd = PartProcessDetails::where('id',$process_data['id'])
                                                ->update(['status'=>'cancel']);

                                    $pps_dec =  PartProcessDetails::where('id',$process_data['id'])
                                                ->decrement('qty',$process_data['qty']);

                                    $pmd_upd = PartMovementDetails::where('id',$process_data['pmd_id'])
                                                ->decrement('qty',$process_data['qty']);

                                    if(!$veh_inc && !$veh_dec && !$pps_upd && !$pps_dec && !$pmd_upd){
                                        DB::rollback();
                                        return back()->with('error','Something Went Wrong Addon Not Cancel')->withInput();
                                    }
                                }
                                
                                
                            }else if($process_data['type'] == 3){
                                $battery_name = ProductDetails::where('id',$process_data['type_id'])
                                            ->where('store_id',$to_store_id)
                                            ->where('status','<>','sale')
                                            ->first();
                                

                                if(!isset($battery_name->id)){
                                    $battery_name2 = ProductDetails::where('id',$process_data['type_id'])
                                            ->first();
                                    $not_ff_err[] = array("loader"=>$part_pro['loader_truck'],"part"=>$battery_name2['frame']);
                                }else{
                                    $battery_upd = ProductDetails::where('id',$process_data['type_id'])
                                            ->update([
                                                'move_status'   =>  0,
                                                'store_id'  =>  $from_store_id
                                            ]);

                                    $updateStockInc = Stock::where('product_id',$battery_name['product_id'])
                                                        ->where('store_id',$from_store_id)
                                                        ->increment('quantity',1);
                                    $updateStockDec = Stock::where('product_id',$battery_name['product_id'])
                                                        ->where('store_id',$to_store_id)
                                                        ->decrement('quantity',1);

                                    $pps_dec =  PartProcessDetails::where('id',$process_data['id'])
                                                ->decrement('qty',$process_data['qty']);
                                                
                                    $pmd_dec = PartMovementDetails::where('id',$process_data['pmd_id'])
                                                ->decrement('qty',$process_data['qty']);

                                    $pps_upd = PartProcessDetails::where('id',$process_data['id'])
                                            ->update(['status'=>'cancel']);

                                    if(!$battery_upd && !$updateStockInc && !$updateStockDec && !$pps_dec && !$pmd_dec && !$pps_upd){
                                        DB::rollback();
                                        return back()->with('error','Something Went Wrong Battery Not Cancel')->withInput();
                                    }
                                }
                                
                            }
                        }
                        
                        if(count($not_ff_err)>0){
                            $loader_n = array_column($not_ff_err, 'loader');
                            $part_n =array_column($not_ff_err, 'part');

                            DB::rollback();
                            return back()->with('error','Processed Parts selected Loader '.$loader_n[0].' parts "'.implode(',', $part_n).'" not Available')->withInput();
                        }else{
                            $update_process = PartProcess::where('id',$process_id)->update(['status'=>'Cancel']);

                            $update_something[] = $update_process;
                        }

                    }else{
                        DB::rollback();
                        return back()->with('error','Processed Parts for Loader '.$part_pro['loader_truck'].' are moved or cancelled')->withInput();
                    }
                }
            }
            
            if(isset($part_ar) && count($part_ar)>0){
                foreach ($part_ar as $pm_id => $part_c_qty) {
                    if($part_c_qty > 0 && $part_c_qty != ''){
                        $part_md =  PartMovementDetails::where('part_movement_details.id',$pm_id)->leftjoin('part_movement','part_movement.id','part_movement_details.part_movement_id')
                            ->whereIn('part_movement.status',['Pending','Process'])
                            ->first();

                        if(!isset($part_md->id)){
                            DB::rollback();
                            return back()->with('error','Cannot Cancel, Part Movement already Done')->withInput();
                        }

                        $check_part_qty = Part::leftjoin('part_stock','part.id','part_stock.part_id')
                                ->select('part_stock.quantity','part_stock.part_id as ps_part_id','part.part_number')
                                ->where('part.id',$part_md['type_id'])
                                ->where('part_stock.store_id',$to_store_id)
                                ->first();

                        $part_qty_validation = PartMovementDetails::where('part_movement_details.id',$pm_id)
                            ->leftjoin('part_process_details','part_process_details.part_movement_details_id','part_movement_details.id')
                            ->select(DB::raw('part_movement_details.qty - sum(IfNull(part_process_details.qty,0)) as qty')
                            )->groupBy('part_movement_details.id')
                            ->first();

                        if($part_qty_validation['qty'] < $part_c_qty){
                            DB::rollback();
                            return back()->with('error','For Part '.$check_part_qty['part_number'].' Maximum Qty is '.$part_qty_validation['qty'])->withInput();
                        }

                        if($check_part_qty['quantity'] < $part_c_qty){
                            DB::rollback();
                            return back()->with('error','For Part '.$check_part_qty['part_number'].' Maximum Qty is '.$check_part_qty['quantity'])->withInput();
                        }else{

                            $part_to_dec = PartStock::where('part_id',$check_part_qty['ps_part_id'])
                                    ->where('store_id',$to_store_id)->decrement('quantity',$part_c_qty);
                                    
                            $part_from_inc = PartStock::where('part_id',$check_part_qty['ps_part_id'])
                                    ->where('store_id',$from_store_id)
                                    ->increment('quantity',$part_c_qty);

                            $pmd_dec = PartMovementDetails::where('id',$pm_id)
                                    ->decrement('qty',$part_c_qty);

                            if(!$part_to_dec && !$part_from_inc && !$pmd_dec)
                            {
                                DB::rollback();
                                return back()->with('error','Some Error Occurred.')->withInput();
                            }
                        }

                        $update_something[] = $pmd_dec;
                    }
                }
            }

            if(isset($addon) && count($addon)>0){
                foreach ($addon as $pm_id => $addon_c_qty) {
                    if($addon_c_qty > 0 && $addon_c_qty != ''){
                        $addon_md =  PartMovementDetails::where('part_movement_details.id',$pm_id)->leftjoin('part_movement','part_movement.id','part_movement_details.part_movement_id')
                            ->whereIn('part_movement.status',['Pending','Process'])
                            ->first();

                        if(!isset($addon_md->id)){
                            DB::rollback();
                            return back()->with('error','Cannot Cancel, Part Movement already Done')->withInput();
                        }

                        if($addon_md['type_id'] == 3){
                        
                            $name = $addon_md['addon_name'];

                        }else{
                            $vehicle_addon = VehicleAddon::where('id',$addon_md['type_id'])->get()->first();
                                
                            $name = $vehicle_addon['key_name'];
                        }

                        $part_qty_validation = PartMovementDetails::where('part_movement_details.id',$pm_id)
                            ->leftjoin('part_process_details','part_process_details.part_movement_details_id','part_movement_details.id')
                            ->select(DB::raw('part_movement_details.qty - sum(IfNull(part_process_details.qty,0)) as qty')
                            )->groupBy('part_movement_details.id')
                            ->first();
                            
                        if($part_qty_validation['qty'] < $addon_c_qty){
                            DB::rollback();
                            return back()->with('error','For Addons '.$name.' Maximum Qty is '.$part_qty_validation['qty'])->withInput();
                        }

                        $addon_qty_used = VehicleAddonStock::where('vehicle_addon_key',$name)
                                            ->where('store_id',$to_store_id)
                                            ->first();

                        if($addon_qty_used['qty'] < $addon_c_qty){
                            DB::rollback();
                            return back()->with('error','For Addons '.$name.' Maximum Qty is '.$addon_qty_used['qty'])->withInput();
                        }else{

                            $addon_to_dec = VehicleAddonStock::where('vehicle_addon_key',$name)
                                    ->where('store_id',$to_store_id)->decrement('qty',$addon_c_qty);
                                    
                            $addon_from_inc = VehicleAddonStock::where('vehicle_addon_key',$name)
                                ->where('store_id',$from_store_id)
                                ->increment('qty',$addon_c_qty);

                            if($addon_md['type_id'] == 3){
                        
                                $owner_to_dec = VehicleAddonStock::where('vehicle_addon_key','owner_manual')
                                ->where('store_id',$to_store_id)
                                ->decrement('qty',$addon_c_qty);
                                
                                $owner_fr_inc = VehicleAddonStock::where('vehicle_addon_key','owner_manual')
                                    ->where('store_id',$from_store_id)
                                    ->increment('qty',$addon_c_qty);

                            }

                            $pmd_dec = PartMovementDetails::where('id',$pm_id)
                                    ->decrement('qty',$addon_c_qty);

                            if(!$addon_to_dec && !$addon_from_inc && !$pmd_dec)
                            {
                                DB::rollback();
                                return back()->with('error','Some Error Occurred.')->withInput();
                            }
                        }

                        $update_something[] = $pmd_dec;
                    }
                }
            }

            if(isset($battery) && count($battery)>0){
                foreach ($battery as $ind => $pm_id) {
                    $battery_pm_d = PartMovementDetails::where('part_movement_details.id',$pm_id)->leftjoin('part_movement','part_movement.id','part_movement_details.part_movement_id')
                            ->whereIn('part_movement.status',['Pending','Process'])
                            ->first();

                    if(!isset($battery_pm_d->id)){
                        DB::rollback();
                        return back()->with('error','Cannot Cancel, Part Movement already Done')->withInput();
                    } 

                    $getProductId = ProductDetails::where('id',$battery_pm_d['type_id'])
                                    ->where('store_id',$to_store_id)
                                    ->where('status','<>','sale')
                                    ->first();

                    if(isset($getProductId->id)){

                        $updateProductDetails = ProductDetails::where('id',$battery_pm_d['type_id'])
                                                    ->where('store_id',$to_store_id)
                                                    ->update([
                                                        'store_id'  =>  $from_store_id,
                                                        'move_status'   =>  0
                                                    ]);

                        $updateStockQty_dec = Stock::where('product_id',$getProductId->product_id)
                                            ->where('store_id',$to_store_id)
                                            ->decrement('quantity',1);

                        $updateStockQty_inc = Stock::where('product_id',$getProductId->product_id)
                                            ->where('store_id',$from_store_id)
                                            ->increment('quantity',1);

                        $pmd_dec = PartMovementDetails::where('id',$pm_id)
                                    ->decrement('qty',1);

                        if(!$updateStockQty_dec && !$updateStockQty_inc && !$pmd_dec)
                        {
                            DB::rollback();
                            return back()->with('error','Some Error Occurred.')->withInput();
                        }

                    }else{

                        $bt_name = ProductDetails::where('id',$battery_pm_d['type_id'])
                                    ->where('store_id',$to_store_id)
                                    ->first();
                        DB::rollback();
                        return back()->with('error','Battery '.$bt_name['frame'].' Not Found')->withInput();

                    }
                    $update_something[] = $pmd_dec;
                }
            }

            if(count($update_something)>0){
                
                $get_pmd = PartMovementDetails::where('part_movement_details.part_movement_id',$part_move_id)
                        ->leftjoin('part_process_details','part_process_details.part_movement_details_id','part_movement_details.id')
                        ->select('part_movement_details.id',
                            'part_movement_details.part_movement_id',
                            DB::raw('part_movement_details.qty - sum(IfNull(part_process_details.qty,0)) as qty')
                        )->groupBy('part_movement_details.id')
                        ->get()->toArray();
                
                $pps_status = PartProcessDetails::where('part_movement_id',$part_move_id)
                            ->get()->toArray();

                
                $qty_array = array_column($get_pmd, 'qty');
                $check_temp = array_filter($qty_array);
                
                $check_status = array_column($pps_status, 'status');

                if(empty($check_temp) && !in_array("process", $check_status) && !in_array("moved", $check_status)){
                    $upd = PartMovement::where('part_movement.id',$part_move_id)->update([
                        'status' => 'Cancel'
                    ]);
                }elseif(empty($check_temp) && !in_array("process", $check_status) && in_array("moved", $check_status)){

                    $upd = PartMovement::where('part_movement.id',$part_move_id)->update([
                        'status' => 'Moved',
                        'moved_date' => date('Y-m-d'),
                        'moved_by'=> Auth::id()
                    ]);
                }
            }else{
                DB::rollback();
                return back()->with('error','Required To Select Minimum One Product or Accessories.');
            }

        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollback();
            return back()->with('error','Something Went Wrong'.$e)->withInput();
        }
        
        CustomHelpers::userActionLog($action='Part Movement Cancel',$part_move_id,0);
        DB::commit();
        return redirect('/admin/part/movement/summary')->with('success','Successfully Cancelled');
    }

    public function part_process_cancel(Request $request,$id){
        try {
            DB::beginTransaction();

            $process_id = $id;
            if($id != null && $id != ''){
                $update_something=[];
                $part_pro =PartProcess::leftJoin('loader','loader.id','part_process.loader_id')->where('part_process.id',$process_id)->where('part_process.status','Pending')
                    ->select(DB::raw('loader.truck_number as loader_truck'),
                        DB::raw('date_format(part_process.created_at,"%d-%m-%Y") as process_date'),
                        'part_process.part_process_detail_ids','part_process.id'
                    )->first();

                $part_m_id =0;

                if(isset($part_pro->id)){

                    $get_processDetailData = explode(',',$part_pro['part_process_detail_ids']);

                    $part_process_details = PartProcessDetails::whereIn('part_process_details.id',$get_processDetailData)
                        ->leftjoin('part_movement_details','part_process_details.part_movement_details_id','part_movement_details.id')
                        ->select('part_process_details.id',
                            'part_movement_details.type',
                            'part_movement_details.id as pmd_id',
                            'part_movement_details.type_id',
                            'part_movement_details.addon_name',
                            'part_process_details.qty',
                            'part_process_details.part_movement_id')->get()->toArray();

                    if(count($part_process_details)==0){
                        DB::rollback();
                        return 'Process data Not Found';
                    }

                    $part_movement_record = PartMovement::where('part_movement.id',$part_process_details[0]['part_movement_id'])->first();

                    $part_m_id = $part_process_details[0]['part_movement_id'];
                    $to_store_id =$part_movement_record['to_store'];
                    $from_store_id =$part_movement_record['from_store'];
                    

                    $not_ff_err = [];
                    $all_process_arr = [];
                    foreach ($part_process_details as $process_index => $process_data) {
                        if($process_data['type'] == 1){
                            $part_check = Part::leftjoin('part_stock','part.id','part_stock.part_id')
                                    ->select('part_stock.quantity','part_stock.part_id as ps_id','part.part_number')
                                    ->where('part.id',$process_data['type_id'])
                                    ->where('part_stock.store_id',$to_store_id)
                                    ->first();
                            if($part_check['quantity'] < $process_data['qty']){
                                $not_ff_err[] =array("loader"=>$part_pro['loader_truck'],"part"=>$part_check['part_number']); 
                            }else{

                                $part_inc = PartStock::where('part_id',$part_check['ps_id'])
                                            ->where('store_id',$from_store_id)
                                            ->increment('quantity',$process_data['qty']);
                                $part_dec = PartStock::where('part_id',$part_check['ps_id'])
                                            ->where('store_id',$to_store_id)
                                            ->decrement('quantity',$process_data['qty']);
                                $pps_upd = PartProcessDetails::where('id',$process_data['id'])
                                            ->update(['status'=>'cancel']);
                                $pps_dec =  PartProcessDetails::where('id',$process_data['id'])
                                            ->decrement('qty',$process_data['qty']);
                                $pmd_dec = PartMovementDetails::where('id',$process_data['pmd_id'])
                                            ->decrement('qty',$process_data['qty']); 

                                if(!$part_inc && !$part_dec && !$pps_upd && !$pps_dec && !$pmd_dec){
                                    DB::rollback();
                                    return 'Something Went Wrong Part Not Cancel';
                                } 
                            }
                        }else if($process_data['type'] == 2){
                            if($process_data['type_id'] == 3){
                            
                                $name = $process_data['addon_name'];

                            }else{
                                $vehicle_addon = VehicleAddon::where('id',$process_data['type_id'])->get()->first();
                                    
                                $name = $vehicle_addon['key_name'];
                            }
                           
                            $addon_qty_used = VehicleAddonStock::where('vehicle_addon_key',$name)
                                        ->where('store_id',$to_store_id)
                                        ->first();
                            if($addon_qty_used['qty'] < $process_data['qty']){
                                $not_ff_err[] = array("loader"=>$part_pro['loader_truck'],"part"=>$name);  
                            }else{

                                $veh_inc = VehicleAddonStock::where('vehicle_addon_key',$name)
                                            ->where('store_id',$from_store_id)
                                            ->increment('qty',$process_data['qty']);

                                $veh_dec = VehicleAddonStock::where('vehicle_addon_key',$name)
                                            ->where('store_id',$to_store_id)
                                            ->decrement('qty',$process_data['qty']);

                                if($process_data['type_id'] == 3){
                    
                                    $owner_to_dec = VehicleAddonStock::where('vehicle_addon_key','owner_manual')
                                    ->where('store_id',$to_store_id)
                                    ->decrement('qty',$process_data['qty']);
                                    
                                    $owner_fr_inc = VehicleAddonStock::where('vehicle_addon_key','owner_manual')
                                        ->where('store_id',$from_store_id)
                                        ->increment('qty',$process_data['qty']);

                                    if(!$owner_to_dec && !$owner_fr_inc){
                                        DB::rollback();
                                        return back()->with('error','Something Went Wrong Addons Not Cancel')->withInput();
                                    }

                                }

                                $pps_upd = PartProcessDetails::where('id',$process_data['id'])
                                            ->update(['status'=>'cancel']);

                                $pps_dec =  PartProcessDetails::where('id',$process_data['id'])
                                            ->decrement('qty',$process_data['qty']);

                                $pms_dec = PartMovementDetails::where('id',$process_data['pmd_id'])
                                            ->decrement('qty',$process_data['qty']);

                                if(!$veh_inc && !$veh_dec && !$pps_upd && !$pps_dec && !$pms_dec){
                                    DB::rollback();
                                    return 'Something Went Wrong Addons Not Cancel';
                                }
                            }
                            
                            
                        }else if($process_data['type'] == 3){
                            $battery_name = ProductDetails::where('id',$process_data['type_id'])
                                        ->where('store_id',$to_store_id)
                                        ->where('status','<>','sale')
                                        ->first();
                            

                            if(!isset($battery_name->id)){
                                $battery_name2 = ProductDetails::where('id',$process_data['type_id'])
                                        ->first();
                                $not_ff_err[] = array("loader"=>$part_pro['loader_truck'],"part"=>$battery_name2['frame']);
                            }else{
                                $battery_upd = ProductDetails::where('id',$process_data['type_id'])
                                        ->update([
                                            'move_status'   =>  0,
                                            'store_id'  =>  $from_store_id
                                        ]);

                                $updateStockInc = Stock::where('product_id',$battery_name['product_id'])
                                                    ->where('store_id',$from_store_id)
                                                    ->increment('quantity',1);
                                $updateStockDec = Stock::where('product_id',$battery_name['product_id'])
                                                    ->where('store_id',$to_store_id)
                                                    ->decrement('quantity',1);

                                $pps_dec =  PartProcessDetails::where('id',$process_data['id'])
                                            ->decrement('qty',$process_data['qty']);
                                            
                                $pmd_dec = PartMovementDetails::where('id',$process_data['pmd_id'])
                                            ->decrement('qty',$process_data['qty']);

                                $pps_upd = PartProcessDetails::where('id',$process_data['id'])
                                            ->update(['status'=>'cancel']);

                                if(!$battery_upd && !$updateStockInc && !$updateStockDec && !$pps_dec && !$pmd_dec && !$pps_upd){
                                    DB::rollback();
                                    return 'Something Went Wrong Battery Not Cancel';
                                }
                            }
                            
                        }
                    }
                    
                    if(count($not_ff_err)>0){
                        $loader_n = array_column($not_ff_err, 'loader');
                        $part_n =array_column($not_ff_err, 'part');

                        DB::rollback();
                        return 'Processed Parts "'.implode(',', $part_n).'" cannot Cancel Because Sale Is Made.';
                    }else{
                        $update_process = PartProcess::where('id',$process_id)->update(['status'=>'Cancel']);
                        $update_something[] = $update_process;
                    }

                }else{
                    DB::rollback();
                    return 'Processed Parts are moved or cancelled';
                }
                
                $get_pmd = PartMovementDetails::where('part_movement_details.part_movement_id',$part_m_id)
                        ->leftjoin('part_process_details','part_process_details.part_movement_details_id','part_movement_details.id')
                        ->select('part_movement_details.id',
                            'part_movement_details.part_movement_id',
                            DB::raw('part_movement_details.qty - sum(IfNull(part_process_details.qty,0)) as qty')
                        )->groupBy('part_movement_details.id')
                        ->get()->toArray();
                
                $pps_status = PartProcessDetails::where('part_movement_id',$part_m_id)
                            ->get()->toArray();

                
                $qty_array = array_column($get_pmd, 'qty');
                $check_temp = array_filter($qty_array);
                
                $check_status = array_column($pps_status, 'status');

                if(empty($check_temp) && !in_array("process", $check_status) && !in_array("moved", $check_status)){
                    $upd = PartMovement::where('part_movement.id',$part_m_id)->update([
                        'status' => 'Cancel'
                    ]);
                }elseif(empty($check_temp) && !in_array("process", $check_status) && in_array("moved", $check_status)){

                    $upd = PartMovement::where('part_movement.id',$part_m_id)->update([
                        'status' => 'Moved',
                        'moved_date' => date('Y-m-d'),
                        'moved_by'=> Auth::id()
                    ]);
                }

                if(count($update_something)>0){
                    DB::commit();
                    CustomHelpers::userActionLog($action='Part Movement Process Cancel',$id,0);
                    return 'success';
                }else{
                    DB::rollback();
                    return "Something went wrong In Updation of Status.";
                }

            }else{
                DB::rollback();
                return "Something Went wrong.";
            }
               
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollback();
            return back()->with('error','Something Went Wrong'.$e)->withInput();
        }

    }
}