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
use \App\Model\RtoFileSubmission;
use \App\Model\RtoFileSubmissionDetails;
use \App\Model\RcCorrectionRequest;
use \App\Model\RtoSummary;
use \App\Model\PDI;
use \App\Model\Part;
use \App\Model\PDI_details;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Auth;
use Hash;
use PDF;
use \Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\Import;


class PDIController extends Controller
{
    public function __construct()
    {
        //$this->middleware('auth');
    }
    public function createPDI(){

        $frame = ProductDetails::Join('product','product.id','product_details.product_id')
                ->where('product.type','product')
                ->where('product_details.status','ok')
                // ->where(function($query){
                //     $query->where('product_details.status','damage')
                //             ->orwhere('product_details.status','ok');
                // })
                ->select('product_details.id','product_details.frame')
                ->orderBy('model_name')->orderBy('model_variant')->orderBy('color_code')->get();
        $store = Store::select('id','store_type',DB::raw('concat(name,"-",store_type) as name'))->orderBy('name','Asc')->get();
        $responsible_emp = Users::select('id','name')->get();
        $employee = Users::where('user_type','user')->where('role','employee')->select('id','name')->get();
        $sale_executive = Users::where('user_type','admin')->where('role','SaleExecutive')->select('id','name')->get();
        $data = [
            'frame' =>  $frame,
            'store' =>  $store,
            'emp'   =>  $employee,
            'responsible_emp' =>    $responsible_emp,
            'sale_exe'  =>  $sale_executive,
            'layout' => 'layouts.main'
        ];
        
        return view('admin.PDI.createPDI',$data);
    }

    public function PartnumberGet(Request $request){

        $partNumber= $request->input('partNumber');
       
        $part=Part::where('part_number','=',$partNumber)->get()->first();

        return response()->json($part);
    }

    public function frameChange(Request $request)
    {
        $prodDetId = $request->input('prodId');
        $all_details = ProductModel::Join('product_details','product_details.product_id','product.id')
                        ->where('product_details.id',$prodDetId)
                    ->select('product.id', 'product.model_name','product.model_variant','product.color_code')
                   ->orderBy('model_name')->orderBy('model_variant')->orderBy('color_code')->first();

        return response()->json($all_details);
    }
    public function createPDIDB(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'frame'   =>  'required',
            'model'   =>  'required',
            'color'   =>  'required',
            'variant'   =>  'required',
            'date_of_damage'   =>  'required',
            'damage_location'   =>  'required',
            'emp_name'   =>  'required',
            // 'employee_code'   =>  'required',
            'date_of_repair'   =>  'required',
            'repair_loc'   =>  'required',
            'desc'   =>  'required',
            'load_ref'  =>  'required_if:damage_reason,load_ref',
            'executive'  =>  'required_if:damage_reason,executive',
            'location'  =>  'required_if:damage_reason,location',
            'driver'  =>  'required_if:damage_reason,driver',
            'item_missing_qty'  =>  'required_if:damage_reason,item_missing_qty',
            'desc_damaged.*'   =>  'required',
            'repair_replace.*'   =>  'required',
            'part_amt.*'   =>  'required',
            'part_number.*'   =>  'required',
            'lab_amt.*'   =>  'required',
            'total.*'   =>  'required',
            'total_amount'  =>  'required|gt:0'
        ],
        [
            'frame.required'  =>  'This Field is required',
            'model.required'  =>  'This Field is required',
            'color.required'  =>  'This Field is required',
            'variant.required'  =>  'This Field is required',
            'date_of_damage.required'  =>  'This Field is required',
            'damage_location.required'  =>  'This Field is required',
            'emp_name.required'  =>  'This Field is required',
            // 'employee_code.required'  =>  'This Field is required',
            'date_of_repair.required'  =>  'This Field is required',
            'desc.required'  =>  'This Field is required',
            'load_ref.required_if'  =>  'This Field is required',
            'executive.required_if'  =>  'This Field is required',
            'location.required_if'  =>  'This Field is required',
            'driver.required_if'  =>  'This Field is required',
            'item_missing_qty.required_if'  =>  'This Field is required',
            'desc_damaged.*.required'  =>  'This Field is required',
            'repair_replace.*.required'  =>  'This Field is required',
            'part_amt.*.required'  =>  'This Field is required',
            'part_number.*.required'  =>  'This Field is required',
            'lab_amt.*.required'  =>  'This Field is required',
            'total.*.required'  =>  'This Field is required',
            'total_amount.required'  =>  'This Field is required',
            'total_amount.gt'  =>  'Amount should be greater than 0.',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        try{
            DB::beginTransaction();
             
            $insertPDI = [
                            'product_details_id'    =>  $request->input('frame'),
                            'date_of_damage'    => date('Y-m-d',strtotime($request->input('date_of_damage'))),
                            'damage_location'    =>  $request->input('damage_location'),
                            'date_of_repair'    =>   date('Y-m-d',strtotime($request->input('date_of_repair'))),
                            'repair_location'    =>  $request->input('repair_loc'),
                            'responsive_emp_id'    =>  $request->input('emp_name'),
                            'desc_of_accident'    =>  $request->input('desc'),
                            'load_ref'  =>  ($request->input('damage_reason') == 'load_ref')? $request->input('load_ref') : null,
                            'sale_executive_id'  =>  ($request->input('damage_reason') == 'executive')? $request->input('executive') : null,
                            'location'  =>  ($request->input('damage_reason') == 'location')? $request->input('location') : null,
                            'driver'  =>  ($request->input('damage_reason') == 'driver')? $request->input('driver') : null,
                            'item_missing'  =>  ($request->input('damage_reason') == 'item_missing_qty')? $request->input('item_missing_qty') : null
                        ];
            
            
            $pdi_id = PDI::insertGetId($insertPDI);
            if($pdi_id)
            {
                $getData = ProductDetails::where('id',$request->input('frame'))->first();
                if(!empty($getData))
                {
                    $update = ProductDetails::where('id',$request->input('frame'))
                                ->where('status','ok')
                                ->update(['status'=>'damage']);
                    if($update)
                    {
                        $updateStock = Stock::where('product_id',$getData['product_id'])
                                            ->where('store_id',$getData['store_id']);
                        $updateqty = $updateStock->decrement('quantity',1);
                        $updateDamageqty = $updateStock->increment('damage_quantity',1);
                    }
                }
            }
            $rows = count($request->input('desc_damaged'));
            for($i = 0 ; $i < $rows ; $i++)
            {
                $insert_pdi_detail = [
                                    'pdi_id'    =>  $pdi_id,
                                    'desc_of_damage_part'   =>  $request->input('desc_damaged')[$i],
                                    'repair_replace'   =>  $request->input('repair_replace')[$i],
                                    'part_number'   =>  $request->input('part_number')[$i],
                                    'part_amt'   =>  $request->input('part_amt')[$i],
                                    'lab_amt'   =>  $request->input('lab_amt')[$i],
                                    'total'   =>  $request->input('total')[$i]
                                ];
                $insert = PDI_details::insertGetId($insert_pdi_detail);
            }
            

        }catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return back()->with('error','some error occurred'.$ex->getMessage());
        }
        DB::commit();
        return back()->with('success','Successfully Created.');

    }

    public function pdiSummary(Request $request){
        $data = [
            'layout' => 'layouts.main'
        ];
        return view('admin.PDI.pdiSummary',$data);
    }
    public function pdiSummary_api(Request $request) {
       
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;

           
            $api_data = PDI::leftJoin('product_details','product_details.id','pdi.product_details_id')
                                ->leftJoin('product','product.id','product_details.product_id')
                                ->leftJoin('store as damage_store','damage_store.id','pdi.damage_location')
                                ->leftJoin('store as repair_store','repair_store.id','pdi.repair_location')
                                ->leftJoin('users as employee', 'employee.id','pdi.responsive_emp_id')
                                ->select(   
                                            'pdi.id',
                                            'product_details.frame',
                                            'product.model_name',
                                            'product.model_variant',
                                            'product.color_code',
                                            'pdi.date_of_damage',
                                            'damage_store.name as damage_location',
                                            'pdi.date_of_repair',
                                            'repair_store.name as repair_location',
                                            // 'employee.name as responsive_emp',
                                             DB::raw('concat(employee.name," ",ifnull( employee.middle_name," ")," ",ifnull( employee.last_name," ")) as responsive_emp'),
                                            'pdi.desc_of_accident',
                                            'pdi.hirise_invoice',
                                            DB::raw('IF(pdi.approved_status = 0, "Pending","Approve") as status')
                        );
            
            if(!empty($serach_value))
            {
               $api_data->where(function($query) use ($serach_value){
                        $query->where('product_details.frame','like',"%".$serach_value."%")
                        ->orwhere('product.model_name','like',"%".$serach_value."%")
                        ->orwhere('product.model_variant','like',"%".$serach_value."%")
                        ->orwhere('product.color_code','like',"%".$serach_value."%")
                        ->orwhere('pdi.date_of_damage','like',"%".$serach_value."%")
                        ->orwhere('damage_store.name','like',"%".$serach_value."%")
                        ->orwhere('pdi.date_of_repair','like',"%".$serach_value."%")
                        ->orwhere('repair_store.name','like',"%".$serach_value."%")
                        ->orwhere('employee.name','like',"%".$serach_value."%")
                        ->orwhere('pdi.desc_of_accident','like',"%".$serach_value."%")
                        ->orwhere('pdi.approved_status','like',"%".((strpos('Pending',$serach_value) === FALSE )? '1' : '0')."%");
                    });
               
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                    'product_details.frame',
                    'product.model_name',
                    'product.model_variant',
                    'product.color_code',
                    'pdi.date_of_damage',
                    'damage_store.name',
                    'pdi.date_of_repair',
                    'repair_store.name',
                    'employee.name',
                    'pdi.desc_of_accident',
                    'pdi.approved_status'
                ];
                
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('pdi.created_at','desc');   
        
        $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray(); 
        foreach ($api_data as &$value) {
                $damage_date = $value['date_of_damage'];
                $repair_date = $value['date_of_repair'];
                if ($damage_date != null) {
                    $value['date_of_damage'] = date('d-m-Y', strtotime($damage_date));
                }else{
                    $value['date_of_damage'] = '';
                }
                if ($repair_date != null) {
                    $value['date_of_repair'] = date('d-m-Y', strtotime($repair_date));
                }else{
                    $value['date_of_repair'] = '';
                }
        }       
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }
    public function pdiSummaryApprove(Request $request){
        $data = [
            'layout' => 'layouts.main'
        ];
        return view('admin.PDI.pdiSummaryApprove',$data);
    }
    public function pdiSummaryApprove_api(Request $request)
    {
        $pdiId = $request->input('pdiId');
        $update = PDI::where('id',$pdiId)
                    ->where('approved_status',0)
                    ->update([
                        'approved_status'   =>  1,
                        'approved_by'   =>  Auth::Id()
                    ]);
        if($update)
        {
            return 'success';
        }
        return 'error';
    }

    //edit PDI
    public function pdiEdit($pdiId){
        $checkInvoice = PDI::where('id',$pdiId)->whereNull('hirise_invoice')->first();
        if(empty($checkInvoice))
        {
            return back()->with('error','Invoice has been generated so, PDI will not be update')->withInput();
        }
        if($checkInvoice['approved_status'] == 1)
        {
            $authUser = Auth::user()->role;
            if($authUser != 'Superadmin')
            {
                return back()->with('error','you are not authorized for edit PDI, after approved of PDI.');
            }
        }
       
        $pdi = PDI::leftJoin('product_details','product_details.id','pdi.product_details_id')
                    ->leftJoin('product','product.id','product_details.product_id')
                    ->where('pdi.id',$pdiId)
                    ->select('pdi.*','product.model_name','product.model_variant','product.color_code')
                    ->first();
        $pdi_details = PDI_details::where('pdi_id',$pdiId)->get();
        $pdi_details_total_amt = PDI_details::where('pdi_id',$pdiId)->sum('total');
        $frame = ProductDetails::Join('product','product.id','product_details.product_id')
                ->where('product.type','product')
                ->where(function($query){
                    $query->where('product_details.status','damage')
                            ->orwhere('product_details.status','ok');
                })
                ->select('product_details.id','product_details.frame')
                ->orderBy('model_name')->orderBy('model_variant')->orderBy('color_code')->get();
        $store = Store::select('id','store_type',DB::raw('concat(name,"-",store_type) as name'))->orderBy('name','Asc')->get();
        $responsible_emp = Users::select('id','name')->get();
        $employee = Users::where('user_type','user')->where('role','employee')->select('id','name')->get();
        $sale_executive = Users::where('user_type','admin')->where('role','SaleExecutive')->select('id','name')->get();
        $data = [
            'pdi'   =>  $pdi,
            'pdi_details'   =>  $pdi_details,
            'frame' =>  $frame,
            'store' =>  $store,
            'emp'   =>  $employee,
            'responsible_emp' =>    $responsible_emp,
            'sale_exe'  =>  $sale_executive,
            'pdi_details_total_amt' =>  $pdi_details_total_amt,
            'layout' => 'layouts.main'
        ];
        
        return view('admin.PDI.updatePDI',$data);
    }
    public function pdiEdit_db(Request $request,$pdiId)
    {
        $validator = Validator::make($request->all(),[
            'frame'   =>  'required',
            'model'   =>  'required',
            'color'   =>  'required',
            'variant'   =>  'required',
            'date_of_damage'   =>  'required',
            'damage_location'   =>  'required',
            'emp_name'   =>  'required',
            // 'employee_code'   =>  'required',
            'date_of_repair'   =>  'required',
            'repair_loc'   =>  'required',
            'desc'   =>  'required',
            'load_ref'  =>  'required_if:damage_reason,load_ref',
            'executive'  =>  'required_if:damage_reason,executive',
            'location'  =>  'required_if:damage_reason,location',
            'driver'  =>  'required_if:damage_reason,driver',
            'item_missing_qty'  =>  'required_if:damage_reason,item_missing_qty',
            'desc_damaged.*'   =>  'required',
            'part_amt.*'   =>  'required',
            'repair_replace.*'   =>  'required',
            'part_number.*'   =>  'required',
            // 'lab_amt.*'   =>  'required',
            'total.*'   =>  'required',
            'total_amount'  =>  'required|gt:0'
        ],
        [
            'frame.required'  =>  'This Field is required',
            'model.required'  =>  'This Field is required',
            'color.required'  =>  'This Field is required',
            'variant.required'  =>  'This Field is required',
            'date_of_damage.required'  =>  'This Field is required',
            'damage_location.required'  =>  'This Field is required',
            'emp_name.required'  =>  'This Field is required',
            // 'employee_code.required'  =>  'This Field is required',
            'date_of_repair.required'  =>  'This Field is required',
            'desc.required'  =>  'This Field is required',
            'load_ref.required_if'  =>  'This Field is required',
            'executive.required_if'  =>  'This Field is required',
            'location.required_if'  =>  'This Field is required',
            'driver.required_if'  =>  'This Field is required',
            'item_missing_qty.required_if'  =>  'This Field is required',
            'desc_damaged.*.required'  =>  'This Field is required',
            'repair_replace.*.required'  =>  'This Field is required',
            'part_number.*.required'  =>  'This Field is required',
            'part_amt.*.required'  =>  'This Field is required',
            // 'lab_amt.*.required'  =>  'This Field is required',
            'total.*.required'  =>  'This Field is required',
            'total_amount.required'  =>  'This Field is required',
            'total_amount.gt'  =>  'Amount should be greater than 0.',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        $checkInvoice = PDI::where('id',$pdiId)->whereNull('hirise_invoice')->first();
        if(empty($checkInvoice))
        {
            return back()->with('error','Invoice has been generated so, PDI will not be update')->withInput();
        }
        if($checkInvoice['approved_status'] == 1)
        {
            $authUser = Auth::user()->role;
            if($authUser != 'Superadmin')
            {
                return back()->with('error','you are not authorized for edit PDI, after approved of PDI.');
            }
        }
        try{
            DB::beginTransaction();
            $insertPDI = [
                            'product_details_id'    =>  $request->input('frame'),
                            'date_of_damage'    =>  date('Y-m-d',strtotime($request->input('date_of_damage'))),
                            'damage_location'    =>  $request->input('damage_location'),
                            'date_of_repair'    =>  date('Y-m-d',strtotime($request->input('date_of_repair'))),
                            'repair_location'    =>  $request->input('repair_loc'),
                            'responsive_emp_id'    =>  $request->input('emp_name'),
                            'desc_of_accident'    =>  $request->input('desc'),
                            'load_ref'  =>  ($request->input('damage_reason') == 'load_ref')? $request->input('load_ref') : null,
                            'sale_executive_id'  =>  ($request->input('damage_reason') == 'executive')? $request->input('executive') : null,
                            'location'  =>  ($request->input('damage_reason') == 'location')? $request->input('location') : null,
                            'driver'  =>  ($request->input('damage_reason') == 'driver')? $request->input('driver') : null,
                            'item_missing'  =>  ($request->input('damage_reason') == 'item_missing_qty')? $request->input('item_missing_qty') : null
                        ];
            
            $pdi_id = PDI::WithoutTimestamps()->where('id',$pdiId)->update($insertPDI);
            if($pdi_id)
            {
                $getData = ProductDetails::where('id',$request->input('frame'))->first();
                $getOldData = ProductDetails::where('id',$checkInvoice['product_details_id'])->first();
                if(!empty($getData))
                {
                    $resetUpdate = ProductDetails::where('id',$checkInvoice['product_details_id'])
                                ->where('status','damage')
                                ->update(['status'=>'ok']);
                    $update = ProductDetails::where('id',$request->input('frame'))
                                ->where('status','ok')
                                ->update(['status'=>'damage']);
                    if($update && $resetUpdate)
                    {
                        // reset old stock
                        $resetStock = Stock::where('product_id',$getOldData['product_id'])
                                            ->where('store_id',$getOldData['store_id']);
                        $resetqty = $resetStock->increment('quantity',1);
                        $resetDamageqty = $resetStock->decrement('damage_quantity',1);
                        $updateStock = Stock::where('product_id',$getData['product_id'])
                                            ->where('store_id',$getData['store_id']);
                        $updateqty = $updateStock->decrement('quantity',1);
                        $updateDamageqty = $updateStock->increment('damage_quantity',1);
                    }
                    else{
                        DB::rollback();
                        return back()->with('error',"Something Wen't Wrong")->withInput();
                    }
                }
            }
            // WithoutTimestamps
            $rows = count($request->input('desc_damaged'));
            $insertId = [];
            for($i = 0 ; $i < $rows ; $i++)
            {
                $update_pdi_detail = [
                                    'desc_of_damage_part'   =>  $request->input('desc_damaged')[$i],
                                    'repair_replace'   =>  $request->input('repair_replace')[$i],
                                    'part_number'   =>  $request->input('part_number')[$i],
                                    'part_amt'   =>  $request->input('part_amt')[$i],
                                    'lab_amt'   =>  $request->input('lab_amt')[$i],
                                    'total'   =>  $request->input('total')[$i]
                                ];
                $oldrow = PDI_details::where('pdi_id',$pdiId)->where($update_pdi_detail)->first();
                
                $update = PDI_details::WithoutTimestamps()->where('id',$oldrow[$i]['id'])->update($update_pdi_detail);
                if(empty($oldrow))
                {
                    $update = PDI_details::insertGetId(array_merge(['pdi_id' => $pdiId],$update_pdi_detail));
                    $insertId[] = $update;
                }
                if(!empty($oldrow))
                {
                    $insertId[] = $oldrow['id'];
                }
            }
            PDI_details::where('pdi_id',$pdiId)->whereNotIn('id',$insertId)->delete();
            
        }catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return redirect('/admin/PDI/edit/'.$pdiId)->with('error','some error occurred'.$ex->getMessage());
        }
        DB::commit();
        return redirect('/admin/PDI/summary')->with('success','Successfully Updated.');

    }

    // PDI update invoice
    public function pdiUpdateInvoice($pdiId){
        $checkInvoice = PDI::where('id',$pdiId)->first();
        if(!empty($checkInvoice['hirise_invoice']))
        {
            $authUser = Auth::user()->role;
            if($authUser != 'Superadmin')
            {
                return back()->with('error','Invoice has been generated so, PDI will not be update')->withInput();
            }
        }
        if($checkInvoice['approved_status'] == 0)
        {
            return back()->with('error','This PDI Claim Required to Approved by SuperAdmin.');
        }

        $pdi = PDI::leftJoin('product_details','product_details.id','pdi.product_details_id')
                    ->leftJoin('product','product.id','product_details.product_id')
                    ->leftJoin('store as damage_store','damage_store.id','pdi.damage_location')
                    ->leftJoin('store as repair_store','repair_store.id','pdi.repair_location')
                    ->leftJoin('store as location','location.id','pdi.location')
                    ->leftJoin('users as employee', 'employee.id','pdi.responsive_emp_id')
                    ->leftJoin('users as sale_executive', 'sale_executive.id','pdi.sale_executive_id')
                    ->leftJoin('users as driver', 'driver.id','pdi.driver')
                    ->where('pdi.id',$pdiId)
                    ->select('pdi.id','product.model_name','product.model_variant','product.color_code',
                            'product_details.frame','pdi.date_of_damage',
                            DB::raw('concat(damage_store.name,"-",damage_store.store_type) as damage_location'),
                            'pdi.date_of_repair',
                            DB::raw('concat(repair_store.name,"-",repair_store.store_type) as repair_location'),
                            'employee.name as responsive_emp',
                            'pdi.desc_of_accident' ,
                            'pdi.load_ref',
                            'sale_executive.name as sale_executive',
                            'driver.name as driver',
                            'pdi.item_missing',
                            'pdi.voucher_no',
                            'pdi.debit_amt',
                            'pdi.hirise_invoice'
                            )
                    ->first();
        $pdi_details = PDI_details::where('pdi_id',$pdiId)->get();
        $pdi_details_total_amt = PDI_details::where('pdi_id',$pdiId)->sum('total');
        // $frame = ProductDetails::Join('product','product.id','product_details.product_id')
        //         ->where('product.type','product')
        //         ->where(function($query){
        //             $query->where('product_details.status','damage')
        //                     ->orwhere('product_details.status','ok');
        //         })
        //         ->select('product_details.id','product_details.frame')
        //         ->orderBy('model_name')->orderBy('model_variant')->orderBy('color_code')->get();
        // $store = Store::select('id','store_type',DB::raw('concat(name,"-",store_type) as name'))->orderBy('name','Asc')->get();
        // $responsible_emp = Users::select('id','name')->get();
        // $employee = Users::where('user_type','user')->where('role','employee')->select('id','name')->get();
        // $sale_executive = Users::where('user_type','admin')->where('role','SaleExecutive')->select('id','name')->get();
        $data = [
            'pdi'   =>  $pdi,
            'pdi_details'   =>  $pdi_details,
            // 'frame' =>  $frame,
            // 'store' =>  $store,
            // 'emp'   =>  $employee,
            // 'responsible_emp' =>    $responsible_emp,
            // 'sale_exe'  =>  $sale_executive,
            'pdi_details_total_amt' =>  $pdi_details_total_amt,
            'layout' => 'layouts.main'
        ];
        // print_r($data);die;
        return view('admin.PDI.updateInvoicePDI',$data);
    }
    public function pdiUpdateInvoiceDB(Request $request, $pdiId)
    {

        $checkInvoice = PDI::where('id',$pdiId)->whereNull('hirise_invoice')->first();
        if(!empty($checkInvoice))
        {
            $authUser = Auth::user()->role;
            if($authUser != 'Superadmin')
            {
                return back()->with('error','Invoice has been generated so, PDI will not be update')->withInput();
            }
        }
       
        if(!empty($checkInvoice) && $checkInvoice['approved_status'] == 0){
            return back()->with('error','This PDI Claim Required to Approved by SuperAdmin.');
        }
        $validator = Validator::make($request->all(),[
            
            'invoice'   =>  'required',
            'voucher'   =>  'required',
            'debit_amt'   =>  'required',
            'repair_amt.*'   =>  'required|gt:0'
        ],
        [
           
            'invoice.required'  =>  'This Field is required',
            'voucher.required'  =>  'This Field is required',
            'debit_amt.required'  =>  'This Field is required',
            'repair_amt.*.required'  =>  'This Field is required',
            'repair_amt.*.gt'  =>  'Amount should be greater than 0.'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try{

            $data = [
                'voucher_no'    =>  $request->input('voucher'),
                'debit_amt'    =>  $request->input('debit_amt'),
                'hirise_invoice'    =>  $request->input('invoice'),
                'invoiceUpdatedBy'  =>  Auth::id()  
            ];

            $updatePdi = PDI::where('id',$pdiId)->update($data);

            $count = count($request->input('repair_amt'));
            for($i = 0 ; $i < $count ; $i++)
            {
                $pdi_detailsId = $request->input('repair')[$i];
                $repair_amt = $request->input('repair_amt')[$i];

                PDI_details::where('id',$pdi_detailsId)->update(['repair_amt' => $repair_amt]);
            }

        }
        catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return redirect('/admin/PDI/update/invoice/'.$pdiId)->with('error','some error occurred'.$ex->getMessage());
        }
        DB::commit();
        return redirect('/admin/PDI/summary')->with('success','Successfully Updated.');

    }

    public function pdiView($id) {
         $pdi = PDI::leftJoin('product_details','product_details.id','pdi.product_details_id')
                                ->leftJoin('product','product.id','product_details.product_id')
                                ->leftJoin('store as damage_store','damage_store.id','pdi.damage_location')
                                ->leftJoin('store as repair_store','repair_store.id','pdi.repair_location')
                                ->leftJoin('users as employee', 'employee.id','pdi.responsive_emp_id')
                                ->where('pdi.id',$id)
                                ->select(   
                                            'pdi.id',
                                            'product_details.frame',
                                            'product.model_name',
                                            'product.model_variant',
                                            'product.color_code',
                                            'pdi.date_of_damage',
                                            'damage_store.name as damage_location',
                                            'pdi.date_of_repair',
                                            'repair_store.name as repair_location',
                                            // 'employee.name as responsive_emp',
                                            DB::raw('concat(employee.name," ",ifnull( employee.middle_name," ")," ",ifnull( employee.last_name," ")) as responsive_emp'),
                                            'pdi.desc_of_accident',
                                            'pdi.hirise_invoice',
                                            'pdi.load_ref',
                                            'pdi.sale_executive_id',
                                            'pdi.location',
                                            'pdi.driver',
                                            'pdi.voucher_no',
                                            'pdi.debit_amt',
                                            'pdi.item_missing',
                                            DB::raw('IF(pdi.approved_status = 0, "Pending","Approve") as status')
                        )->get()->first();
        $pdi_details = PDI_details::where('pdi_id',$id)->get();
        $pdi_details_total_amt = PDI_details::where('pdi_id',$id)->sum('total');
        $detail = '';
        if ($pdi['load_ref']) {
            $detail = $pdi['load_ref'];
        }
        if ($pdi['sale_executive_id']) {
             $sale_executive = Users::where('user_type','admin')->where('id',$pdi['sale_executive_id'])->where('role','SaleExecutive')->select('id',DB::raw('concat(users.name," ",ifnull( users.middle_name," ")," ",ifnull( users.last_name," ")) as name'))->get()->first();
            $detail = $sale_executive['name'];
        }
        if ($pdi['location']) {
            $store = Store::where('id',$pdi['location'])->select('id','store_type',DB::raw('concat(name,"-",store_type) as name'))->orderBy('name','Asc')->get()->first();
            $detail = $store['name'];
        }
        if ($pdi['driver']) {
            $employee = Users::where('user_type','user')->where('id',$pdi['driver'])->where('role','employee')->select('id',DB::raw('concat(users.name," ",ifnull( users.middle_name," ")," ",ifnull( users.last_name," ")) as name'))->get()->first();
            $detail = $employee['name'];
        }

        if ($pdi['item_missing']) {
           $detail = $pdi['item_missing'];
        }    

        $data = [
            'pdi' => $pdi,
            'pdi_details' => $pdi_details,
            'total_amount' => $pdi_details_total_amt,
            'detail' => $detail,
        'layout' => 'layouts.main'
        ];
        
        return view('admin.PDI.pdi_view',$data);

    }

}
