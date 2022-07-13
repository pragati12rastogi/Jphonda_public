<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;
use \App\Model\Store;

use \App\Custom\CustomHelpers;

use \App\Model\Part;

use \App\Model\PartStock;
use \App\Model\PartUpload;  
use \App\Model\PartRack;  
use \App\Model\PartRow;  
use \App\Model\PartCell;  
use \App\Model\PurchaseOrder;  
use \App\Model\PoPart;  
use \App\Model\Settings;  
use \App\Http\Controllers\Product;
use DB;
use Auth;
use Hash;
use \Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\Import;
use Validator;


class PartImportController extends Controller
{

    public function CreatePart(){

        $store = Store::whereIn('id',CustomHelpers::user_store())
            ->select('id',
                DB::Raw('concat(name,"-",store_type) as name')
            )
            ->orderBy("store_type","ASC")->get();

        $data = array(
            'store' => $store,
            'layout' => 'layouts.main'
        );
        return view('admin.part.PartData',$data);
    }
    
    public function create_part_import(Request $request)
    {

        $po_part_data = [];

        if($request->input('file_name') && $request->input('po')){
            $fileName = $request->input('file_name');
            $po = $request->input('po');
            $count_po = count($po);

            for($i = 0; $i < $count_po ; $i++){

                $po_part_data[$po[$i]]   =   [
                    'purchase_order_id' =>  0,
                    'type'  =>  $request->input($po[$i])
                ];
            }
        }
        else{

            $allowed_extension = array('xls','xlsx','xlt','xltm','xltx','xlsm');
            $validator = Validator::make($request->all(),[    
                'excel'  => 'required', 
                'store'=>'required' 
    
            ],[
                'excel.required'    =>  'This field is required.',
                'store.required'=> 'This field is required.'
                
            ]);
            $validator->sometimes('excel', 'mimes:xls,xlsx,xlt,xltm,xltx,xlsm', function ($input) use($allowed_extension) {
                $extension="";
                if(isset($input->excel))
                $extension = $input->excel->getClientOriginalExtension();
                if(in_array($extension,$allowed_extension))
                    return false;
                else
                    return true;
            });
            
            if ($validator->fails()) {
                return back()->withErrors($validator)
                            ->withInput();
            }
        }
        
        try{
            
            if(!$request->input('file_name') && !$request->input('po') && $request->file('excel')){
                
                $file = $request->file('excel');
                $destinationPath = public_path().'/upload/partimport/file/';
                $extension = $file->getClientOriginalExtension();    
                $fileName = 'part_'.Auth::id().'.'.$extension;   
                $file->move($destinationPath,$fileName);

                $path = public_path().'/upload/partimport/file/'.$fileName;
            }
            else{
                $path = public_path().'/upload/partimport/file/'.$request->input('file_name');
            }

            // alter tables for autoincrements set
            $tables = ['po_part','part','part_stock'];
            CustomHelpers::resetIncrement($tables);
            DB::beginTransaction();
            $store = $request->input('store');


            if($path)
            {
                $data = Excel::toArray(new Import(),$path);
                if(count($data) > 0 && $data)
                {
                    // check purchase order and save history
                    $pre_import = $this->checkPo($data,$po_part_data);
                    if($pre_import[0] == 'pending'){
                        $data = array(
                            'file' => $fileName,
                            'store' =>  $store,
                            'po'    =>  $pre_import[1],
                            'layout' => 'layouts.main'
                        );
                        return view('admin.part.PartData',$data);
                    }
                    elseif($pre_import[0] == 'error'){
                        return back()->with('error',$pre_import[1])->withInput();
                    }
                    elseif($pre_import[0] == 'success'){
                        $all_po_part_data = $pre_import[1]; 
                    }
                    $import = $this->CreatePart_StoreDB($data,$store,$all_po_part_data);
                    $import = explode('-,',$import);
                    if($import[0] != 'success')
                    {
                        DB::rollback();
                        return back()->with('error',$import[0])->withInput();
                    }
                }
                else{
                    return back()->with('error','Error, Check your file should not be empty!!')->withInput();
                }
            }
            else{
                return back()->with('error','Error, some error occurred Please Try again!!')->withInput();
            }
        } catch(\Illuminate\Database\QueryException $ex) {
                DB::rollback();
                return redirect('/admin/part/create')->with('error','some error occurred'.$ex->getMessage())->withInput();
        }
        DB::commit();
        return redirect('/admin/part/create')->with("success","Successfully Import Part Data");
        
    }
    public function CreatePart_StoreDB($data,$store,$po_part_data)
    {
        try
        {
            if(count($data) > 0 && $data)
            {
                $error ='';
                $success = '';
                $part_import_update_setting = 0;
                $part_import_update_setting_check = Settings::where('name','PartImportUpdate')->first();
                if(isset($part_import_update_setting_check->id)){
                    $part_import_update_setting = $part_import_update_setting_check->value;
                }
                
                foreach($data as $key => $value)
                {
                    $value[1][0] = trim($value[1][0]);
                            
                        $store_id = $store;
                        $column_name_format=array('Order#','Ordered Part No','Actual Received Part #','Part Description',
                                    'Received Quantity','Unit Price','MRP');
                        $out= $this->validate_excel_format($value[0],$column_name_format);
                        $head = $out['header'];
                        $partIds = [];
                        if($out['column_name_err'] == 0)
                        {
                            $cn=0;
                            if(count($value) > 1)
                            {
                                // $createLogId = 0;
                                foreach($value as $in => $row)
                                {
                                    
                                    if(isset($row[$head['Ordered Part No']]))
                                    {
                                        if($in > 0 && $in < count($value))
                                        {
                                            //--------------  trim() ---------------------
                                            $row[$head['Order#']] = trim($row[$head['Order#']]);
                                            $row[$head['Ordered Part No']] = trim($row[$head['Ordered Part No']]);
                                            $row[$head['Actual Received Part #']] = trim($row[$head['Actual Received Part #']]);
                                            $row[$head['Part Description']] = trim($row[$head['Part Description']]);
                                            $row[$head['Received Quantity']] = trim($row[$head['Received Quantity']]);
                                            $row[$head['Unit Price']] = doubleval(str_replace(',','',trim($row[$head['Unit Price']])));
                                            $row[$head['MRP']] = doubleval(str_replace([',','Rs.','Rs'],['','',''],trim($row[$head['MRP']])));
                                            
                                            $rec_qty = trim($row[$head['Received Quantity']]);
                                            $part_no = trim($row[$head['Ordered Part No']]);
                                            $order_no = trim($row[$head['Order#']]);

                                            if($row[$head['Ordered Part No']] != $row[$head['Actual Received Part #']])
                                            {
                                                $part_no = $row[$head['Actual Received Part #']];
                                            }
                                            // validate part #
                                            if(strpos($part_no,'-+') !== false)
                                            {
                                                // string contains only english letters & digits
                                                $error = $error." Part # should be contains only Character and Number at Row :- ".($in+1);
                                            }
                                            if (!is_numeric($rec_qty)) 
                                            {
                                                // varianble contains only digits
                                                $error = $error." Received Quantity should be contains only Number at Row :- ".($in+1);
                                            }

                                            $part_data1 = array(
                                                'order_no'  =>  $order_no,
                                                'name'  =>  $row[$head['Part Description']],
                                                'part_number'   =>  $part_no,
                                                'unit_price'  => $row[$head['Unit Price']],
                                                'price'   => trim($row[$head['MRP']]),
                                                'quantity'  =>  $rec_qty
                                                );
                                                if(array_keys($part_data1,""))
                                                {
                                                    $cn++;
                                                    if($cn>150)
                                                        break;
                                                    $error = $this->errorFunction($head,$error,$value,$in);   //return which column and which indexing value error
                                                }
                                                else
                                                {   
                                                    $part_data = array(
                                                        'name'  =>  $row[$head['Part Description']],
                                                        'unit_price'  => $row[$head['Unit Price']],
                                                        'price'   => $row[$head['MRP']]
                                                        );
                                                    
                                                    $pre_entry = Part::where('part_number',$part_no)
                                                                    ->first();

                                                    if(empty($pre_entry))
                                                    {
                                                        $part_data['part_number'] = $part_no;
                                                        $insertPartId = Part::insertGetId($part_data);
                                                    }
                                                    else{
                                                        $insertPartId = $pre_entry->toArray()['id'];
                                                        if($part_import_update_setting == 1){
                                                            $updatePartDetail = Part::where('id',$insertPartId)
                                                                                ->update($part_data);
                                                        }
                                                    }
                                                    if($insertPartId)
                                                    {
                                                        // insert in po_part--------
                                                        $po_partInsert = PoPart::insertGetId([
                                                            'purchase_order_id' =>  $po_part_data[$order_no]['purchase_order_id'],
                                                            'order_number'  =>  $order_no,
                                                            'part_id'   =>  $insertPartId,
                                                            'type'  =>  $po_part_data[$order_no]['type']
                                                        ]);
                                                        // end-------------------

                                                        // for nid set ---------------
                                                        // $check_name = Part::where('name',$part_data['name'])->orderBy('id','ASC')->first();
                                                        // if($check_name){
                                                        //     $update_nid = Part::where('id',$insertPartId)->update(['nid'=>$check_name->id]);
                                                        // }
                                                        // else{
                                                        //     $update_nid = Part::where('id',$insertPartId)->update(['nid'=>$insertPartId]);
                                                        // }
                                                        //---------------------
                                                        $partIds[] = $insertPartId;
                                                        

                                                        $get_old_quantity = PartStock::where('part_id',$insertPartId)
                                                                                ->where('store_id',$store_id)->first();
                                                        if(empty($get_old_quantity))
                                                        {
                                                            $insertStockId = PartStock::insert([
                                                                                'part_id'    => $insertPartId,
                                                                                'quantity'  =>  $rec_qty,
                                                                                'store_id'  =>  $store_id
                                                                            ]);
                                                        }
                                                        else
                                                        {
                                                            $old_quan = $get_old_quantity->toArray()['quantity'];
                                                            $insertStockId = PartStock::where('part_id', $insertPartId)
                                                                            ->where('store_id',$store_id)
                                                                            ->update([
                                                                                'quantity'  =>  $old_quan+$rec_qty
                                                                            ]);
                                                        }
                                                        
                                                    }
                                                    else{
                                                        $error = $error."Server Issue, !!Try Again!!";
                                                    }
                                                }   
                                        }
                                    }
                                    else
                                    {
                                        $success = 'Successfully Uploaded File. Total '.($in).' rows Successfully Inserted.';
                                        break;
                                    }
                                            
                                }
                            }
                            else{
                                $error = $error."In your sheet have no data";
                            }
                        }
                        else{ 
                            $error = $error.$out['error'];  
                        }
                        if($out['columnCount']==1){ 
                            $error = $error.$out['error'];
                        }
                        // create part log
                        $str_partIds = join(',',$partIds);
                        $createLogId = PartUpload::insertGetId([
                            'json_record'   =>  DB::raw('json_object("part_id",json_array('.$str_partIds.'))'),
                            'store_id'  =>  $store_id
                        ]);
                               
                        if(!empty($error))
                        {
                            return $error;
                        }
                           
                    
                }
                if(!empty($error))
                {   
                        return $error;
                }
            }
            else{
                    return 'Error, Check your file should not be empty!!';
            }
        }
        catch(\Illuminate\Database\QueryException $ex) {
            return 'some error occurred'.$ex->getMessage();
        }
        return 'success-,'.$success;
    }
    public function checkpo($data,$get_po)
    {
        if(!empty($get_po[0])){
            $get_po = [];
        }
        try
        {
            $find_po = [];  // if any po not get in po table then save it.
            if(count($data) > 0 && $data)
            {
                $error ='';
                $pending_flag = 0;
                foreach($data as $key => $value)
                {
                    $value[1][0] = trim($value[1][0]);
                    
                            
                        $column_name_format=array('Order#');
                        $out= $this->validate_excel_format($value[0],$column_name_format);
                        $head = $out['header'];
                        if($out['column_name_err'] == 0)
                        {
                            $cn=0;
                            if(count($value) > 1)
                            {
                                $createLogId = 0;
                                foreach($value as $in => $row)
                                {                                    
                                    if(isset($row[$head['Order#']]))
                                    {
                                        if($in > 0 && $in < count($value))
                                        {
                                            //--------------  trim() ---------------------
                                            $row[$head['Order#']] = trim($row[$head['Order#']]);

                                            $order_no  = $row[$head['Order#']];
                                            $part_data = array(
                                                'order_no'  =>  $order_no,
                                            );
                                            if(array_keys($part_data,NULL))
                                            {
                                                $cn++;
                                                if($cn>150)
                                                    break;
                                                $error = $this->errorFunction($head,$error,$value,$in);   //return which column and which indexing value error
                                            }
                                            else
                                            {   
                                                $get_keys = array_keys($get_po);
                                                if(!in_array($order_no,$get_keys)){
                                                    $check_po = PurchaseOrder::where('po_number',$order_no)->first();
                                                    if(isset($check_po->id)){
                                                        $get_po[$order_no]  =   [
                                                            'purchase_order_id' =>  $check_po->id,
                                                            'type'  =>  null
                                                        ];
                                                    }
                                                    else{
                                                        array_push($find_po,$order_no);
                                                    }
                                                } 
                                            } 
                                        }
                                    }
                                    else
                                    {
                                        $success = 'Successfully Uploaded File. Total '.($in).' rows Successfully Inserted.';
                                        break;
                                    }   
                                }
                            }
                            else{
                                $error = $error."In your sheet have no data";
                            }
                        }
                        else{ 
                            $error = $error.$out['error'];  
                        }
                        if($out['columnCount']==1){ 
                            $error = $error.$out['error'];
                        }
                        if(!empty($error))
                        {
                            return array('error',$error);
                        }
                }
                if(!empty($error))
                {   
                    return array('error',$error);
                }
            }
            else{
                return array('error','Check your file should not be empty');
            }
            
        }
        catch(\Illuminate\Database\QueryException $ex) {

            $er = 'some error occurred'.$ex->getMessage();
            return array('error',$er);
        }
        if(!isset($find_po[0])){
            return array('success',$get_po);
        }
        return array('pending',$find_po);
    }

    
    function errorFunction($head,$error,$value,$in)
    {
        
        foreach($head as $key => $val)
        {
            if($value[$in][$head[$key]] == NULL)
            {
                $in1 = $in+1;
                $y = $this->getNameFromNumber($val);
                $error = $error." Column Name :- ".$key." should not be Empty. Error At ".$in1.",'".$y."'/ ";
            }
        }
        return $error;
    }

    function getNameFromNumber($num) {
        $numeric = $num % 26;
        $letter = chr(65 + $numeric);
        $num2 = intval($num / 26);
        if ($num2 > 0) {
            return $this->getNameFromNumber($num2 - 1) . $letter;
        } else {
            return $letter;
        }
    }
    public function validate_excel_format($a,$column_name,$sheet="")
    {
        ini_set('max_execution_time', 18000);
        $index=0;
        $column_name_err=0;
        $char = '0';
        $side=0;
        $error="";
        $data_inserted=0;
        $header = array();
        if(count($a)>=count($column_name))
        {
            foreach($a as $key => $val){
                $val = trim($val);
                    if(!in_array($val,$column_name))//$val != $value)
                    {
                        $column_name_err++;
                        $error=$error."Column Name not in provided format. Error At  ".$this->getNameFromNumber($char)."1.";
                    }
                    else
                    {
                        $header[$val] = $key;
                    }
                    $index++;
                    $char++;
                if(count($column_name) == count($header)){
                    break;
                }
            }
            if(count($column_name) == count($header)){
                $column_name_err = 0;
                $error="";
            }
        }
        else
        {
            $column_name_err++;
            $error=$error."Required Column Name not in provided Sheet. Please Correct Sheet and try again";
            $side=1;
        }
        $data = array(
            'column_name_err' => $column_name_err,
            'error' =>  $error,
            'columnCount'  =>  $side,
            'header'    =>  $header
        );
        return $data;
        //return $header;
    }
    public function create_part_loc()
    {
        $store = Store::whereIn('id',CustomHelpers::user_store())
            ->select('id',
                DB::Raw('concat(name,"-",store_type) as name')
            )
            ->orderBy("store_type","ASC")->get();
        $part = Part::join('part_stock','part_stock.part_id','part.id')
                        ->join('store','store.id','part_stock.store_id')
                        ->whereIn('part_stock.store_id',CustomHelpers::user_store())
                        ->where('part_stock.cell_id',0)
                    ->select('part.id',
                        DB::raw('concat(part.name," (",part.part_number, ") ( ", store.name ," - ", store.store_type ," )"  ) as part_name'))
                        ->get();
        $rack = PartRack::get();
        $data = array(
            'store' => $store,
            'part'  =>  $part,
            'rack'  =>  $rack,
            'layout' => 'layouts.main'
        );
        return view('admin.part.create_part_loc',$data);
    }
    public function create_part_loc_db(Request $request)
    {

        $validator = Validator::make($request->all(),[    
            'store'  => 'required', 
            'part'=>'required' ,
            'rack'=>'required' ,
            'row'=>'required' ,
            'cell'=>'required' 
        ],[
            'store.required'    =>  'This field is required.',
            'part.required'=> 'This field is required.',
            'rack.required'=> 'This field is required.',
            'row.required'=> 'This field is required.',
            'cell.required'=> 'This field is required.' 
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)
                        ->withInput();
        }

        try{
            $part_id = $request->input('part');
            $store_id = $request->input('store');
            $data = [
                'cell_id'   =>  $request->input('cell')
            ];
            $update = PartStock::where('part_id',$part_id)
                                ->where('store_id',$store_id)
                                ->update($data);
        }
         catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return redirect('/admin/part/create/location')->with('error','some error occurred'.$ex->getMessage())->withInput();
        }
        DB::commit();
        return redirect('/admin/part/create/location')->with('success','Successfully Part Located');
    }
    public function get_part_row(Request $request)
    {
        $rack_id = $request->input('rack_id');

        $row = PartRow::where('rack_id',$rack_id)->get();
        return response()->json($row);
    }
    public function get_part_cell(Request $request)
    {
        $row_id = $request->input('row_id');

        $cell = PartCell::where('row_id',$row_id)->get();
        return response()->json($cell);
    }


   public  function partLocationList(Request $request)
   {
         $store = Store::whereIn('id',CustomHelpers::user_store())
                    ->select('id',DB::raw('concat(store.name,"-",store.store_type) as name'))->get();
        $data = [
            'store'=>$store,
            'layout' => 'layouts.main'
        ];
        
        return view('admin.part.part_location_list',$data);
   }

    public function partLocationListApi(Request $request){

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
            ->leftJoin('part_cell','part_stock.cell_id','part_cell.id')
            ->leftJoin('part_row','part_cell.id','part_row.id')
            ->leftJoin('part_rack','part_row.rack_id','part_rack.id')
            ->select(
                'part_stock.id',
                'part_stock.quantity',
                'part_stock.min_qty',
                'part_stock.sale_qty',
                'part.part_number',
                'part.price',
                'part.name',
                'part_rack.rack_name',
                'part_row.row_name',
                'part_cell.cell_name',
                DB::raw('concat(store.name,"-",store.store_type) as store_name')
            );

        if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('part.name','like',"%".$serach_value."%")
                    ->orwhere('rack_name','like',"%".$serach_value."%") 
                    ->orwhere('row_name','like',"%".$serach_value."%")
                    ->orwhere('cell_name','like',"%".$serach_value."%")
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
                'part_rack.rack_name',
                'part_row.row_name',
                'part_cell.cell_name',
                'store_name'

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


    //part update import

    public function updatePartPrice(){

        // $store = Store::whereIn('id',CustomHelpers::user_store())
        //     ->select('id',
        //         DB::Raw('concat(name,"-",store_type) as name')
        //     )
        //     ->orderBy("store_type","ASC")->get();

        $data = array(
            // 'store' => $store,
            'layout' => 'layouts.main'
        );
        return view('admin.part.UpdatePartPrice',$data);
    }

    public function PartPriceUpdateImport(Request $request)
    {
        $allowed_extension = array('xls','xlsx','xlt','xltm','xltx','xlsm');
        $validator = Validator::make($request->all(),[    
            'excel'  => 'required'

        ],[
            'excel.required'    =>  'This field is required.'
            
        ]);
        $validator->sometimes('excel', 'mimes:xls,xlsx,xlt,xltm,xltx,xlsm', function ($input) use($allowed_extension) {
            $extension="";
            if(isset($input->excel))
            $extension = $input->excel->getClientOriginalExtension();
            if(in_array($extension,$allowed_extension))
                return false;
            else
                return true;
        });
        if ($validator->fails()) {
            return back()->withErrors($validator)
                        ->withInput();
        }
        try{
            $reset_table = ['part'];
            CustomHelpers::resetIncrement($reset_table);

            DB::beginTransaction();
            if($request->file('excel'))
            {
                $path = $request->file('excel');      
                //$data = array();          
                $data = Excel::toArray(new Import(),$path);
                if(count($data) > 0 && $data)
                {
                    $import = $this->PartPriceUpdateImportDB($data);
                    $import = explode('-,',$import);
                    if($import[0] != 'success')
                    {
                        DB::rollback();
                        return back()->with('error',$import[0])->withInput();
                    }
                }
                else{
                    DB::rollback();
                        return back()->with('error','Error, Check your file should not be empty!!')->withInput();
                }
            }
            else{
                DB::rollback();
                return back()->with('error','Error, some error occurred Please Try again!!')->withInput();
            }
            

        } catch(\Illuminate\Database\QueryException $ex) {
                DB::rollback();
                return redirect('/admin/part/list')->with('error','some error occurred'.$ex->getMessage())->withInput();
        }
        DB::commit();
        return redirect('/admin/part/list')->with("success","Successfully Import Part Data");
        
    }
    public function PartPriceUpdateImportDB($data)
    {
        try
        {
            if(count($data) > 0 && $data)
            {
                //    DB::beginTransaction();
                $header = 0;
                $error ='';
                $success = '';
                foreach($data as $key => $value)
                {
                    $value[1][0] = trim($value[1][0]);
                    
                            
                        //print_r($unload_id);die;
                        $column_name_format=array('Product','MRP','Part Category','Description Text');

                        $out= $this->validate_excel_format($value[0],$column_name_format);
                        $head = $out['header'];
                        // print_r($out);die;
                        if($out['column_name_err'] == 0)
                        {
                            // echo "<pre>";
                            // print_r($value);die;
                            $cn=0;
                            if(count($value) > 1)
                            {
                                $createLogId = 0;
                                foreach($value as $in => $row)
                                {
                                    
                                    // echo "<pre>";
                                    // print_r($in);//die;
                                    if(isset($row[$head['Product']]))
                                    {
                                        if($in > 0 && $in < count($value))
                                        {

                                            //--------------  trim() ---------------------
                                            $row[$head['Product']] = trim($row[$head['Product']]);
                                            $row[$head['Description Text']] = trim($row[$head['Description Text']]);
                                            $row[$head['Part Category']] = trim($row[$head['Part Category']]);
                                            // for part price---------
                                            $row[$head['MRP']] = trim($row[$head['MRP']]);
                                            $price = trim(doubleval(str_replace([',','Rs.','Rs'],['','',''],$row[$head['MRP']])));

                                            // for part category------
                                            $row[$head['Part Category']] = trim($row[$head['Part Category']]);
                                            $part_cat_arr = explode('-',$row[$head['Part Category']]);
                                            $part_cat = null;
                                            if(!isset($part_cat_arr[1])){
                                                $part_cat = trim($part_cat_arr[0]);
                                            }else{
                                                $part_cat = trim($part_cat_arr[1]);
                                            }
                                            //----------------------------

                                            $part_no = trim($row[$head['Product']]);

                                            // validate part #
                                            if(strpos($part_no,'-+') !== false)
                                            {
                                                // string contains only english letters & digits
                                                $error = $error." Part # should be contains only Character and Number at Row :- ".($in+1);
                                            }
                                            
                                            $part_data = array(
                                                'name'  =>  $row[$head['Description Text']],
                                                'part_number'   =>  $part_no,
                                                'type'  => $part_cat,
                                                'price'   => $price
                                                );
                                                if(array_keys($part_data,NULL))
                                                {
                                                    $cn++;
                                                    if($cn>150)
                                                        break;
                                                    $error = $this->errorFunction($head,$error,$value,$in);   //return which column and which indexing value error
                                                }
                                                else
                                                {   
                                                    $PartDetail = Part::where('part_number',$part_no)->first();
                                                    if($PartDetail)
                                                    {
                                                        $update_data = ['price' =>  $price];
                                                        if(empty($PartDetail->type)){
                                                            $update_data = array_merge(['type'  =>  $part_cat],$update_data);
                                                        }

                                                        $updatePartDetail = Part::where('part_number',$part_no)
                                                                        ->update($update_data);
                                                        // if(!$updatePartDetail)
                                                        // {
                                                        //     $error = $error." Price has not be update for this Part Number :- ".$part_no];
                                                        // }
                                                        $insertPartId = $PartDetail->id;
                                                    }
                                                    else{
                                                        //insert part number
                                                        
                                                        $insertPartId = Part::insertGetId($part_data);
                                                    }
                                                    // if($insertPartId)
                                                    // {
                                                    //     // for nid set ---------------
                                                    //     $check_name = Part::where('name',$part_data['name'])->orderBy('id','ASC')->first();
                                                    //     if($check_name){
                                                    //         $update_nid = Part::where('id',$insertPartId)->update(['nid'=>$check_name->id]);
                                                    //     }
                                                    //     else{
                                                    //         $update_nid = Part::where('id',$insertPartId)->update(['nid'=>$insertPartId]);
                                                    //     }
                                                    //     //---------------------
                                                    // }
                                                }
                                                
                                            
                                        }
                                    }
                                    else
                                    {
                                        $success = 'Successfully Uploaded File. Total '.($in).' rows Successfully Inserted.';
                                        break;
                                    }
                                            
                                }
                            }
                            else{
                                $error = $error."In your sheet have no data";
                            }
                        }
                        else{ 
                            $error = $error.$out['error'];  
                        }
                        if($out['columnCount']==1){ 
                            $error = $error.$out['error'];
                        }
                                
                        // print_r($error);die;
                        if(!empty($error))
                        {
                            return $error;
                        }
                           
                    
                }
                if(!empty($error))
                {   
                        return $error;
                }
            }
            else{
                    //return back()->with('error','Error, Check your file should not be empty!!');
                    return 'Error, Check your file should not be empty!!';
            }
            // }
            // else{
            //     //return back()->with('error', 'some error occurred Please Try again!!');
            //     return 'Error, some error occurred Please Try again!!';
            // }
            
        }
        catch(\Illuminate\Database\QueryException $ex) {
          //  return redirect('/admin/product/create')->with('error','some error occurred'.$ex->getMessage())->withInput();
            return 'some error occurred'.$ex->getMessage();
        }
        //DB::commit();
        //return back()->with('success', 'Excel Data Imported successfully.');
        // return 'success';
        return 'success-,'.$success;
    }


    // part stock import

    public function CreatePartStock(){

        $store = Store::whereIn('id',CustomHelpers::user_store())
            ->select('id',
                DB::Raw('concat(name,"-",store_type) as name')
            )
            ->orderBy("store_type","ASC")->get();

        $data = array(
            'store' => $store,
            'layout' => 'layouts.main'
        );
        return view('admin.part.PartStockData',$data);
    }

    public function CreatePartStock_Store(Request $request)
    {

            $allowed_extension = array('xls','xlsx','xlt','xltm','xltx','xlsm');
            $validator = Validator::make($request->all(),[    
                'excel'  => 'required', 
                'store'=>'required' 
    
            ],[
                'excel.required'    =>  'This field is required.',
                'store.required'=> 'This field is required.'
                
            ]);
            $validator->sometimes('excel', 'mimes:xls,xlsx,xlt,xltm,xltx,xlsm', function ($input) use($allowed_extension) {
                $extension="";
                if(isset($input->excel))
                $extension = $input->excel->getClientOriginalExtension();
                if(in_array($extension,$allowed_extension))
                    return false;
                else
                    return true;
            });
            
            if ($validator->fails()) {
                return back()->withErrors($validator)
                            ->withInput();
            }
        
        try{
            
            $path = $request->file('excel');
            // alter tables for autoincrements set
            $tables = ['part_stock','part'];
            CustomHelpers::resetIncrement($tables);

            DB::beginTransaction();
            $store = $request->input('store');
            if($path)
            {
                $data = Excel::toArray(new Import(),$path);
                if(count($data) > 0 && $data)
                {
                    $import = $this->CreatePartStock_StoreDB($data,$store);
                    $import = explode('-,',$import);
                    if($import[0] != 'success')
                    {
                        DB::rollback();
                        return back()->with('error',$import[0])->withInput();
                    }
                }
                else{
                    return back()->with('error','Error, Check your file should not be empty!!')->withInput();
                }
            }
            else{
                return back()->with('error','Error, some error occurred Please Try again!!')->withInput();
            }
        } catch(\Illuminate\Database\QueryException $ex) {
                DB::rollback();
                return back()->with('error','some error occurred'.$ex->getMessage())->withInput();
        }
        DB::commit();
        return back()->with("success","Successfully Import Part Data");
        
    }

    public function CreatePartStock_StoreDB($data,$store)
    {
        try
        {
            if(count($data) > 0 && $data)
            {
                $error ='';
                $success = '';
                
                foreach($data as $key => $value)
                {
                        $store_id = $store;
                        $column_name_format=array('Ordered Part No','Actual Received Part #',
                                    'Part Description','MRP','Received Quantity');
                        $out= $this->validate_excel_format($value[0],$column_name_format);
                        $head = $out['header'];
                        if($out['column_name_err'] == 0)
                        {
                            $cn=0;
                            if(count($value) > 1)
                            {
                                // $createLogId = 0;
                                foreach($value as $in => $row)
                                {
                                    
                                    if(isset($row[$head['Ordered Part No']]))
                                    {
                                        if($in > 0 && $in < count($value))
                                        {
                                            //--------------  trim() ---------------------
                                            $row[$head['Ordered Part No']] = trim($row[$head['Ordered Part No']]);
                                            $row[$head['Actual Received Part #']] = trim($row[$head['Actual Received Part #']]);
                                            $row[$head['Received Quantity']] = trim($row[$head['Received Quantity']]);
                                            $row[$head['MRP']] = doubleval(str_replace([',','Rs.','Rs'],['','',''],trim($row[$head['MRP']])));
                                            $row[$head['Part Description']] = trim($row[$head['Part Description']]);

                                            
                                            $rec_qty = trim($row[$head['Received Quantity']]);
                                            $part_no = trim($row[$head['Ordered Part No']]);
                                            $price = trim($row[$head['MRP']]);
                                            if($row[$head['Ordered Part No']] != $row[$head['Actual Received Part #']])
                                            {
                                                $part_no = $row[$head['Actual Received Part #']];
                                            }
                                            // validate part #
                                            if(strpos($part_no,'-+') !== false)
                                            {
                                                // string contains only english letters & digits
                                                $error = $error." Part # should be contains only Character and Number at Row :- ".($in+1);
                                            }
                                            if (!is_numeric($rec_qty)) 
                                            {
                                                // varianble contains only digits
                                                $error = $error." Received Quantity should be contains only Number at Row :- ".($in+1);
                                            }

                                                $part_data1 = array(
                                                // 'name'  => $row[$head['Part Description']],
                                                'part_number'   =>  $part_no,
                                                'quantity'  =>  $rec_qty,
                                                'price' =>  $price
                                                );
                                                if(array_keys($part_data1,""))
                                                {
                                                    $cn++;
                                                    if($cn>150)
                                                        break;
                                                    $error = $this->errorFunction($head,$error,$value,$in);   //return which column and which indexing value error
                                                }
                                                else
                                                {   

                                                    $pre_entry = Part::where('part_number',$part_no)
                                                                    ->first();

                                                    if(!isset($pre_entry->id))
                                                    {
                                                        // $error = $error." ".$part_no." this Part # Not Found. ";
                                                        $insertPartId = Part::insertGetId([
                                                            'name'  =>  $row[$head['Part Description']],
                                                            'part_number'   =>  $part_no,
                                                            'type'  =>  'Accessory',
                                                            'price' =>  $price
                                                        ]);
                                                    }
                                                    else{
                                                        $insertPartId = $pre_entry->toArray()['id'];
                                                    }
                                                    if($insertPartId)
                                                    {

                                                        $get_old_quantity = PartStock::where('part_id',$insertPartId)
                                                                                ->where('store_id',$store_id)->first();
                                                        if(empty($get_old_quantity))
                                                        {
                                                            $insertStockId = PartStock::insert([
                                                                                'part_id'    => $insertPartId,
                                                                                'quantity'  =>  $rec_qty,
                                                                                'store_id'  =>  $store_id
                                                                            ]);
                                                        }
                                                        else
                                                        {
                                                            $old_quan = $get_old_quantity->toArray()['quantity'];
                                                            $insertStockId = PartStock::where('part_id', $insertPartId)
                                                                            ->where('store_id',$store_id)
                                                                            ->update([
                                                                                'quantity'  =>  $old_quan+$rec_qty
                                                                            ]);
                                                        }
                                                        
                                                    }
                                                }   
                                        }
                                    }
                                    else
                                    {
                                        $success = 'Successfully Uploaded File. Total '.($in).' rows Successfully Inserted.';
                                        break;
                                    }
                                            
                                }
                            }
                            else{
                                $error = $error."In your sheet have no data";
                            }
                        }
                        else{ 
                            $error = $error.$out['error'];  
                        }
                        if($out['columnCount']==1){ 
                            $error = $error.$out['error'];
                        }
                               
                        if(!empty($error))
                        {
                            return $error;
                        }
                           
                }
                if(!empty($error))
                {   
                        return $error;
                }
            }
            else{
                return 'Error, Check your file should not be empty!!';
            }
        }
        catch(\Illuminate\Database\QueryException $ex) {
            return 'some error occurred '.$ex->getMessage();
        }
        return 'success-,'.$success;
    }

}