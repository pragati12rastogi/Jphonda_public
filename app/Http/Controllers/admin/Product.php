<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;

use \App\Model\Store;
use \App\Model\Unload;
use \App\Model\DamageClaim;
use \App\Model\DamageDetail;
use \App\Model\ProductModel;
use \App\Model\ProductDetails;
use \App\Model\Loader;
use \App\Model\Waybill;
use \App\Model\PartShortage;
use \App\Model\ShortageDetails;
use \App\Model\FuelModel;
use \App\Model\Stock;
use \App\Model\UnloadAddon;
use \App\Model\IdealStockProduct;
use \App\Model\SmPending;
use \App\Custom\CustomHelpers;
use DB;
use Auth;
use Hash;
use Validator;
use \Carbon\Carbon;
//use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\Import;
use App\Model\StockMovement;
use App\Model\StockMovementDetails;
use App\Model\Users;
use App\Model\VehicleAddonStock;
use App\Model\Factory;
use App\Model\VehicleAddon;
use App\Model\ProductAudit;

use App\User;

class Product extends Controller
{
    

    public function CreateProduct(){

        $data = array(
            'layout' => 'layouts.main'
        );
        return view('admin.ProductData',$data);
    }
    public function unload_storeDB(Request $request){
        
        $allowed_extension = array('xls','xlsx','xlt','xltm','xltx','xlsm');
        $validator = Validator::make($request->all(),[    
            'excel'  => 'required', 
            'loadRefNum'=>'required',
            'store'=>'required',
            'unloadDate'=>'required',
            'driverName' => 'required',
            'mirror_set' => 'required',
            'tool_kit' => 'required',
            'first_aid' => 'required',
            'battery' => 'required',
            'saree' => 'required',
            'qty_keys' => 'required',
            'damagedVehicle' => 'required',
        ],[
            'loadRefNum.required'=> 'This field is required.',
            'excel.required'    =>  'This field is required.',
            'store.required'=> 'This field is required.',
            'unloadDate.required'=> 'This field is required.',
            'driverName.required'=> 'This field is required.',
            'mirror_set.required'=> 'This field is required.',
            'tool_kit.required'=> 'This field is required.',
            'first_aid.required'=> 'This field is required.',
            'battery.required'=> 'This field is required.',
            'saree.required'=> 'This field is required.',
            'qty_keys.required'=> 'This field is required.',
            'damagedVehicle.required'=> 'This field is required.',
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
            return redirect('admin/unload/data')
                        ->withErrors($validator)
                        ->withInput();
        }

        try{
            $table = array('product','product_details','stock','unload');
            CustomHelpers::resetIncrement($table);  //resetIncrement
            DB::beginTransaction();
            $id=Auth::id();
            $date = Carbon::now();

            //----------- trim() ---------------
            $loadRefNum = trim($request->input('loadRefNum'));
            $driverName = trim($request->input('driverName'));
            //------------------------------------

            $checkLRN = Unload::where('load_referenace_number',$loadRefNum)->first();
            if(!empty($checkLRN))
            {
                DB::rollback();
                return back()->with('error','This LRN already Used, Pls Enter Different LRN')->withInput();
            }

            $qtysc = 0;
            $qtymc = 0;
            $model_name = [];
            $factory = '';
            $invoice_date = '';
            $truckNumber = '';
            $transporterName = '';

            // for get quantity of SC and MC from excel sheet
            if($request->file('excel'))
            {
                $path = $request->file('excel');      
                
                $data = Excel::toArray(new Import(),$path);
                if(count($data) > 0 && $data)
                {
                    $lrn = $loadRefNum;
                    $import = $this->get_qty_sc_mc($data,$lrn);
                    if($import[0] != 'success')
                    {
                        DB::rollback();
                        return back()->with('error',$import[0])->withInput();
                    }
                    else{
                        $qtysc = $import[1];
                        $qtymc = $import[2];
                        $model_name = $import[3];
                        $factory = $import[4];
                        $invoice_date = $import[5];
                        $truckNumber = $import[6];
                        $transporterName = $import[7];
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
            $get_factory = Factory::where('name',$factory)->first();
            if(!isset($get_factory->id)){
                DB::rollback();
                return back()->with('error','Factory Not Found.')->withInput();
            }
            $factory_id = $get_factory->id;
            if($qtysc <= 0 && $qtymc <= 0)
            {
                DB::rollback();
                return back()->with('error',"SC or MC Couldn't find in Excel Sheet, Please Check It.")->withInput();
            }
            $data = Unload::insertGetId(
                [
                    'load_referenace_number'=>$loadRefNum,
                    'store'=>$request->input('store'),
                    'unloading_date'=>CustomHelpers::showDate($request->input('unloadDate'),'Y-m-d'),
                    'truck_number'=>$truckNumber,
                    'transporter_name'=>CustomHelpers::capitalize($transporterName,'name'),
                    'driver_name'=>CustomHelpers::capitalize($driverName,'name'),
                    'factory'=>$factory_id,
                    'qty_sc'=>$qtysc,
                    'qty_mc'=>$qtymc,
                    'mirror_set'=>$request->input('mirror_set'),
                    'toolkits'=>$request->input('tool_kit'),
                    'first_aid_kit'=>$request->input('first_aid'),
                    'battery'=>$request->input('battery'),
                    'saree_guard'=>$request->input('saree'),
                    'qty_keys'=>$request->input('qty_keys'),
                    'damaged_vehicle'=>$request->input('damagedVehicle'),
                    'invoice_number'=>$truckNumber,
                    'invoice_date'  =>CustomHelpers::showDate($invoice_date,'Y-m-d')
                ]
            );
            $UnloadId = $data;
            
            // saved in unload_addon for owner_manual
            foreach($model_name as $model){
                $ins = UnloadAddon::insertGetId([
                    'unload_id' =>  $UnloadId,
                    'addon_name'    =>  'owner_manual',
                    'model' =>  $model,
                    'qty'   =>  0
                ]);
            }

            // saved vehicle addon stock
            $prod_data = [
                'mirror' => $request->input('mirror_set'),
                'toolkit' => $request->input('tool_kit'),
                'first_aid_kit' => $request->input('first_aid'),
                'saree_guard' => $request->input('saree'),
                'bike_keys' => $request->input('qty_keys')
            ];
            $store_id = $request->input('store');
            foreach($prod_data as $k => $v){
                $col = $k;
                $qty = $v;

                $find = VehicleAddonStock::where('vehicle_addon_key',$col)
                                                ->where('store_id',$store_id)->first();
                if(isset($find->id)){
                    $update = VehicleAddonStock::where('id',$find->id)
                                                    ->increment('qty',$qty);
                }else{
                    $insert =   VehicleAddonStock::insertGetId([
                        'vehicle_addon_key' =>  $col,
                        'qty'   =>  $qty,
                        'sale_qty'  =>  0,
                        'store_id'  =>  $store_id
                    ]);
                }

            }

            $factarray = array(
                 array('mirror_set','scmc' ), 
                 array('tool_kit','scmc' ),
                //  array('owner_manual','scmc' ),
                 array('first_aid','scmc' ),
                 array('battery','scmc' ),
                 array('qty_keys','scmc' ), 
                 array('saree','mc' )
            );
            foreach ($factarray as $key) {

                if ($key['1'] == 'scmc') {
                    $quantity = $qtysc + $qtymc;
                }
                if($key['1'] == 'mc'){
                    $quantity = $qtymc;
                }
                if($key['1'] == 'sc'){
                    $quantity = $qtysc;
                }
                $shortage = $quantity - $request->input($key['0']);

                if($shortage>0)
                {

                    // check already saved for that factry and part
                   $factdata =  PartShortage::where('factory_id',$factory_id)->where('part_type',$key['0'])->get()->first();

                    if (!empty($factdata)) {

                        $added_qty = $factdata->shortage_qty + $shortage;
                        $shortagedata = PartShortage::where('factory_id',$factory_id)->where('part_type',$key['0'])->update(
                            [
                                'shortage_qty'=>$added_qty
                            ]
                        );

                        $part_short_id= $factdata->id;

                    }
                      // insert
                    else{
                        $shortagedata = PartShortage::insertGetId(
                            [
                                'load_referenace_number'    =>  $UnloadId,
                                'factory_id'=>$factory_id,
                                'part_type'=>$key['0'],
                                'shortage_qty'=>$shortage
                            ]
                        );

                        $part_short_id= $shortagedata;

                        if ($shortagedata == 0) {
                            db::rollback();
                            return redirect('/admin/unload/data')->with('error','Some Unexpected Error occurred.');
                        }
                    }

                    $sdata = ShortageDetails::insertGetId(
                        [
                            'receive_date'=>$date,
                            'receive_qty'=>$shortage,
                            'part_shortage_id'=>$part_short_id,
                            'unload_id'=>$UnloadId,
                            'created_by' => Auth::id()
                        ]
                    ); 

                }
            }
           
            if($request->file('excel'))
            {
                $path = $request->file('excel');      
                $data = Excel::toArray(new Import(),$path);
                if(count($data) > 0 && $data)
                {
                    $lrn = $loadRefNum;
                    $import = $this->CreateProduct_StoreDB($data, $lrn, $UnloadId);
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
                return redirect('/admin/unload/data')->with('error','some error occurred'.$ex->getMessage())->withInput();
            }
            /* Add action Log */
            CustomHelpers::userActionLog($action='Create Unload',$UnloadId);
            DB::commit();
            return redirect('/admin/product/list')->with("success",$import[1].", Please Claim Damage Product's.");
    }
    public function CreateProduct_StoreDB($data, $lrn, $unloadId)
    {
       try
        {
                if(count($data) > 0 && $data)
                {
                    $header = 0;
                    $error ='';
                    $success = '';
                    foreach($data as $key => $value)
                    {
                        $value[1][0] = trim($value[1][0]);
                        if(isset($value[1][0]))
                        {
                            if($lrn == $value[1][0])
                            {
                                $unique_load_ref_no = $value[1][0]; // match all rows in a sheet
                                $get_unload_id = Unload::where('id',$unloadId)->first();

                                if(!empty($get_unload_id)  )
                                {
                                    $unload_id = $get_unload_id->toArray()['id'];
                                    $store_id = $get_unload_id->toArray()['store'];
                                    $column_name_format=array('HMSI/InterDealer Load Reference No',
                                    'Model Category','Model Name','Model Variant','Color Code','MTOC','Frame #',
                                    'Engine No','Chassis Status','Key No','Manufacturing Date');

                                    $out= $this->validate_excel_format($value[0],$column_name_format);
                                    $head = $out['header'];
                                    if($out['column_name_err'] == 0)
                                    {
                                        $cn=0;
                                        if(count($value) > 1)
                                        {
                                            foreach($value as $in => $row)
                                            {
                                                //--------------  trim() ---------------------
                                                $value[$in][$head['Model Category']] = trim($value[$in][$head['Model Category']]);
                                                $value[$in][$head['Model Name']] = trim($value[$in][$head['Model Name']]);
                                                $value[$in][$head['Model Variant']] = trim($value[$in][$head['Model Variant']]);
                                                $value[$in][$head['Color Code']] = trim($value[$in][$head['Color Code']]);
                                                $value[$in][$head['MTOC']] = trim($value[$in][$head['MTOC']]);
                                                $value[$in][$head['Chassis Status']] = trim($value[$in][$head['Chassis Status']]);
                                                $value[$in][$head['Key No']] = trim($value[$in][$head['Key No']]);
                                                $value[$in][$head['Frame #']] = trim($value[$in][$head['Frame #']]);
                                                $value[$in][$head['Engine No']] = trim($value[$in][$head['Engine No']]);
                                                //----------------------------
                                                if(isset($row[0]))
                                                {
                                                    if($in > 0 && $in < count($value)){
                                                        $c = array(
                                                            array(
                                                                'row'   =>  $in,
                                                                'col'   =>  $head['Frame #'],
                                                                'col_name'  =>  'Frame #',
                                                                'val'   =>  $value[$in][$head['Frame #']]
                                                            ),
                                                            array(
                                                                'row'   =>  $in,
                                                                'col'   =>  $head['Frame #'],
                                                                'col_name'  =>  'Engine No',
                                                                'val'   =>  $value[$in][$head['Engine No']]
                                                            )
                                                        );
                                                        $checkVlookUp = $this->checkVlookUp($c);    // check vlookup and special character
                                                        if($checkVlookUp != 'success')
                                                        {
                                                            $error = $error.$checkVlookUp;
                                                            return $error;
                                                        }
                                                        $key_no = $value[$in][$head['Key No']];
                                                        if($unique_load_ref_no == $row[0])
                                                        {
                                                                $product_data = array(
                                                                    'model_category'  => $value[$in][$head['Model Category']],
                                                                    'model_name'   => $value[$in][$head['Model Name']],
                                                                    'model_variant'   => $value[$in][$head['Model Variant']],
                                                                    'color_code'    => $value[$in][$head['Color Code']]
                                                                    // 'mtoc'  => $value[$in][$head['MTOC']],
                                                                    // 'chasis_status'   => $value[$in][$head['Chassis Status']],
                                                                    // 'key_no'   => $value[$in][$head['Key No']],
                                                                    );
                                                                if(array_keys($product_data,NULL))
                                                                {
                                                                    $cn++;
                                                                    if($cn>150)
                                                                        break;
                                                                    $error = $this->errorFunction($head,$error,$value,$in);   //return which column and which indexing value error
                                                                }
                                                                else
                                                                {   
                                                                    $pre_entry = ProductModel::where('model_category',$value[$in][$head['Model Category']])
                                                                            ->where('model_name',$value[$in][$head['Model Name']])
                                                                            ->where('model_variant', $value[$in][$head['Model Variant']])
                                                                            ->where('color_code', $value[$in][$head['Color Code']])
                                                                            ->first();

                                                                    if(empty($pre_entry))
                                                                    {                                     
                                                                        // $insertProductId = ProductModel::insertGetId($product_data);
                                                                        $x = $in+1;
                                                                        $error = $error."Excel Row :- ".$x." Model / Variant / Color Code Name Couldn't Match in Master. ";
                                                                    }
                                                                    else{
                                                                        $insertProductId = $pre_entry->toArray()['id'];
                                                                        if($insertProductId)
                                                                        {
                                                                            $get_old_quantity = Stock::where('product_id',$insertProductId)
                                                                            ->where('store_id',$store_id)->first();
                                                                            if(empty($get_old_quantity))
                                                                            {
                                                                                $insertStockId = Stock::insert([
                                                                                    'product_id'    => $insertProductId ,
                                                                                    'quantity'  =>  1,
                                                                                    'store_id'  =>  $store_id
                                                                                ]);
                                                                            }
                                                                            else
                                                                            {
                                                                                $old_quan = $get_old_quantity->toArray()['quantity'];
                                                                                $insertStockId = Stock::where('product_id', $insertProductId)
                                                                                ->where('store_id',$store_id)
                                                                                ->update([
                                                                                    'quantity'  =>  $old_quan+1
                                                                                ]);
                                                                            }
                                                                            $pre_pro_entry = ProductDetails::where('frame',$value[$in][$head['Frame #']])
                                                                                ->orwhere('engine_number',$value[$in][$head['Engine No']])
                                                                                ->first(['frame','engine_number']);
                                                                            $product_detail_data = array(
                                                                                'unload_id' => $unload_id,
                                                                                'store_id'  =>  $store_id,
                                                                                'manufacture_date'  =>  CustomHelpers::excelDate($value[$in][$head['Manufacturing Date']]),
                                                                                'product_id' => $insertProductId,
                                                                                'frame' =>  $value[$in][$head['Frame #']],
                                                                                'engine_number' =>  $value[$in][$head['Engine No']],
                                                                                'status'    =>  'ok',
                                                                                'key_no'    =>  $key_no
                                                                            );
                                                                            if(array_keys($product_detail_data,NULL)) 
                                                                            {
                                                                                $cn++;
                                                                                if($cn>150)
                                                                                    break;
                                                                                $error = $this->errorFunction($head,$error,$value,$in);   //return which column and which indexing value error
                                                                            }
                                                                            else
                                                                            {   
                                                                                if(empty($pre_pro_entry))
                                                                                {
                                                                                    $insertProductDetailId = ProductDetails::insertGetId($product_detail_data);
                                                                                    if(!$insertProductDetailId)
                                                                                    {
                                                                                        $error = $error."Server Issue, !!Try Again!!";
                                                                                    }
                                                                                    $success = 'Successfully Uploaded File. Total '.($in).' rows Successfully Inserted.';
                                                                                }
                                                                                else
                                                                                {
                                                                                    $error = $this->checkDuplicateEntry($pre_pro_entry->toArray(),$head,$error,$value,$in);
                                                                                }
                                                                                
                                                                            }

                                                                        }
                                                                        else{
                                                                            $error = $error."Server Issue, !!Try Again!!";
                                                                        }
                                                                    }
                                                                }
                                                        }
                                                        else{
                                                            $error = $error."LRN Should be same to other's LRN at row ".$in." and column 'A'";
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
                                }
                                else
                                {
                                    $error = $error."Check Load Referance Number should be match to Unload data";
                                }
                                if(!empty($error))
                                {
                                    return $error;
                                }
                            }
                            else{
                                $error = $error.'Enter LRN have not matched in excel sheet, Pls check it';
                            }
                        }
                        else{
                            $error = $error.' Error, Check your File should not be Empty';
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

    public function get_qty_sc_mc($data,$lrn)
    {
        if (count($data) > 0 && $data) {
            $model_name = [];
            $header = 0;
            $error = '';
            $sc_count = 0;
            $mc_count = 0;
            $success = '';
            $invoice_date = '';
            $factory = '';
            $truckNumber = '';
            $transporterName = '';
            foreach($data as $key => $value) {
                // print_r($value);die;
                $value[1][0] = trim($value[1][0]);
                if (isset($value[1][0])) {
                    if ($lrn != $value[1][0]) {
                        $error = $error.'Enter LRN have not matched in excel sheet, Pls check it';
                    }
                    $column_name_format = array('HMSI/InterDealer Load Reference No','Invoice Date','Transporter Name',
                    'Shipment Truck #','Plant Code','Model Category', 'Model Name', 'Model Variant', 
                    'Color Code', 'MTOC', 'Frame #','Engine No', 'Chassis Status', 'Key No', 'Manufacturing Date');
    
                    $out = $this->validate_excel_format($value[0], $column_name_format);
                    $head = $out['header'];
                    if ($out['column_name_err'] != 0) {
                        $error = $error.$out['error'];
                    }
                    $cn = 0;
                    if (count($value) > 1) {
                        foreach($value as $in => $row) {
                            //--------------  trim() ---------------------
                            if($in > 0){
                                $value[$in][$head['Model Category']] = trim($value[$in][$head['Model Category']]);
                                $value[$in][$head['Model Name']] = trim($value[$in][$head['Model Name']]);
                                $data = [
                                    'model_cat' => trim($value[$in][$head['Model Category']]),
                                    'model_name'    => trim($value[$in][$head['Model Name']]),
                                    'invoice_date'  =>  trim($value[$in][$head['Invoice Date']]),
                                    'factory'   =>  trim($value[$in][$head['Plant Code']]),
                                    'truck_num' =>  trim($value[$in][$head['Shipment Truck #']]),
                                    'transporter_name'  =>  trim($value[$in][$head['Transporter Name']])
                                ]; 

                                if(array_keys($data,NULL))
                                {
                                    $cn++;
                                    if($cn>150)
                                        break;
                                    $error = $this->errorFunction($head,$error,$value,$in);   //return which column and which indexing value error
                                }

                                if(empty($invoice_date)){
                                    $invoice_date = $data['invoice_date'];
                                    
                                    $invoice_date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp($invoice_date);
                                    $invoice_date = date('Y-m-d',$invoice_date);

                                    /*
                                    $invoice_date_arr = explode('/',$invoice_date);
                                    if(count($invoice_date_arr)<2)
                                    {
                                        $error = $error." Invoice Date format is not correct.";
                                    }
                                    else
                                    {
                                        $invoice_date = $invoice_date_arr[2].'-'.$invoice_date_arr[1].'-'.$invoice_date_arr[0];

                                        $invoice_timestamp = strtotime($invoice_date);
                                        if(empty($invoice_timestamp))
                                        {
                                            $error = $error." Invoice Date is not correct.";
                                        }
                                    }*/
                                    
                                }if(empty($factory)){
                                    $factory = $data['factory'];
                                }if(empty($truckNumber)){
                                    $truckNumber = $data['truck_num'];
                                }if(empty($transporterName)){
                                    $transporterName = $data['transporter_name'];
                                }
                                //----------------------------
                                if($data['model_cat'] == 'SC')
                                {
                                    $sc_count++;
                                }
                                elseif($data['model_cat'] == 'MC')
                                {
                                    $mc_count++;
                                }
                                // update model name qty
                                if(!in_array($data['model_name'],$model_name) && !empty($data['model_name']) ){
                                    array_push($model_name,$data['model_name']);
                                }
                            }
                        }
                    } else {
                        $error = $error."In your sheet have no data";
                    }
    
                    if ($out['columnCount'] == 1) {
                        $error = $error.$out['error'];
                    }
    
    
                    if (!empty($error)) {
                        return array($error);
                    }
    
                } else {
                    $error = $error.' Error, Check your File should not be Empty';
                }
            }
            if (!empty($error)) {
                return array($error);
            }
        } else {
            return array('Error, Check your file should not be empty!!');
        }
        $ret_arr = array('success',$sc_count,$mc_count,$model_name,$factory,$invoice_date,$truckNumber,$transporterName);
        return $ret_arr;
    }

    function checkVlookUp($arr)
    {
        $flag = 0;
        $error = '';
        foreach($arr as $k => $v)
        {
            $row = $v['row'];
            $col = $v['col'];
            $val = $v['val'];
            $col_name = $v['col_name'];

            if (strpos($val, 'VLOOKUP') == true) { 

                $y = $this->getNameFromNumber($col);
                $error = $error." Column Name :- ".$col_name." should be contain only Character or Number . Error At ".$row.",'".$y."'/ ";
                $flag = 1;
            } 
            elseif (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $val))
            {
                $y = $this->getNameFromNumber($col);
                $error = $error." Column Name :- ".$col_name." should be contain only Character or Number . Error At ".$row.",'".$y."'/ ";
                $flag = 1;
            }

        }
        if($flag == 0)
        {
            return 'success';
        }
        else{
            return $error;
        }
        
    }

    function checkDuplicateEntry($pre_pro_entry,$head,$error,$value,$in)
    {
        
        foreach($head as $key=>$val)
        {
            foreach($pre_pro_entry as $key1 => $val1){
                if($value[$in][$val] == $val1){
                    $in1 = $in+1;
                    $error = $error." !! Column Name :- ".$key." not allow dublicate entries. Error At ".$in1.",".$this->getNameFromNumber($head[$key])." !! ";
                    break;
                }
            }   
        }
        return $error;
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
                $flag =0;   
                $val = trim($val);
                    if(!in_array($val,$column_name))
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


    public function productView(){
        $getData = ProductDetails::leftJoin('unload','unload.id','product_details.unload_id')
                    ->select(['unload.load_referenace_number','unload.id as id'])
                    ->whereIn('unload.store',CustomHelpers::user_store())
                    ->groupBy('product_details.unload_id')->get();
        
        $data=array(
                'layout'=>'layouts.main',
                'lrn'   =>  $getData
        );
        return view('admin.productView',$data);
           
    }
    public function productView_api(Request $request,$lrn){
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
        
            $api_data= ProductModel::leftJoin('product_details','product_details.product_id','product.id')
            ->leftJoin('unload','unload.id','product_details.unload_id')
            ->whereIn('unload.store',CustomHelpers::user_store())
            ->leftJoin('store','store.id','product_details.store_id')
            ->where('product_details.unload_id',$lrn)
            ->where('type','product')
            
            ->select(
                DB::raw('product_details.id as id'),
                DB::raw('product.id as prodId'),
                DB::raw('unload.load_referenace_number as refer'),
                DB::raw('product.model_category as model_cat'),
                DB::raw('product.model_name as model_name'),
                DB::raw('product.model_variant as model_var'),
                DB::raw('product.color_code as color'),
                DB::raw('product.basic_price as basic_price'),
                DB::raw('concat(store.name,"-",store.store_type) as store_name'),
                DB::raw('product_details.id as detail_id'),                
                DB::raw('product_details.frame as frame'),
                DB::raw('product_details.status as status'),
                DB::raw('product_details.engine_number as engine'),
                DB::raw('product_details.created_at as created_at')
                );
            if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('unload.load_referenace_number','like',"%".$serach_value."%")
                    ->orwhere('product.model_category','like',"%".$serach_value."%")
                    ->orwhere('product.model_name','like',"%".$serach_value."%")
                    ->orwhere('product.model_variant','like',"%".$serach_value."%")
                    ->orwhere('product.color_code','like',"%".$serach_value."%")
                    ->orwhere('product.basic_price','like',"%".$serach_value."%")
                    ->orwhere('store.name','like',"%".$serach_value."%")
                    ->orwhere('product_details.frame','like',"%".$serach_value."%")
                    ->orwhere('product_details.engine_number','like',"%".$serach_value."%");
                });
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                    //'unload.id as id',
                    'unload.load_referenace_number',
                    'product.model_category',
                    'product.model_name',
                    'product.model_variant',
                    'product.color_code',
                    'product.basic_price',
                    'store.name',
                    'product_details.frame',
                    'product_details.engine_number'
                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('id','asc');      
        
        $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);  
    }

    public function productViewDel(Request $request) {
        echo "Not Permited";die;
        //return $request->input('product_id');
        DB::beginTransaction();
        $id = $request->input('product_id');
        $del = ProductModel::where("id",$id)->delete();
        if($del)
        {
            $del1 = ProductDetails::where("product_id",$id)->delete();
            $del2 = Stock::where('product_id',$id)->delete();
            if($del1 && $del2){
                DB::commit();
                //return back()->with('success','Data deleted successfully');
                return "true";
            }
            DB::rollBack();
            //return back()->with('error','Data deleted Unsuccessfully');
            return "false";
        }
        // return back()->with('error','Data deleted Unsuccessfully');;
        return "false";
    }

    public function stockList(){

        // $api_data= Stock::leftJoin('product','product.id','stock.product_id')
        //     ->leftJoin('store','store.id','stock.store_id')
        //     ->select(
        //         DB::raw('stock.id as id'),
        //         DB::raw('concat(product.model_category,"-",product.model_name,"-",product.model_variant) as product_name'),
        //         DB::raw('product.color_code as color'),
        //         DB::raw('stock.quantity as quan'),
        //         DB::raw('stock.store_id as store_id')
        //         )->orderBy('stock.store_id','ASC')->get();
        //         echo "<pre>";
        // return $api_data->toArray();die;
        
        $number_of_store = Store::where("store_type",'warehouse')->orderBy("id","ASC")->get();
        $data=array(
            'number_of_store'   =>  $number_of_store->toArray(),
            'layout'=>'layouts.main');
        return view('admin.stockList',$data);   
    }
    public function stockList_api(Request $request){
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
        
        $api_data= Stock::leftJoin('product','product.id','stock.product_id')
            ->leftJoin('store','store.id','stock.store_id')
            ->where('store.store_type','warehouse')
            ->select(
                DB::raw('stock.id as id'),
                DB::raw('concat(product.model_category,"-",product.model_name,"-",product.model_variant) as product_name'),
                DB::raw('product.color_code as color_code'),
                DB::raw('stock.quantity as quan'),
                DB::raw('store.id as stor_id'),
                DB::raw('(CASE WHEN 1 = store.id THEN stock.quantity ELSE 0 END) AS Jphonda '),
                DB::raw('(CASE WHEN 3 = store.id THEN stock.quantity ELSE 0 END) AS Jhonda ')
                );
            if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('product.model_category','like',"%".$serach_value."%")
                    ->orwhere('product.color_code','like',"%".$serach_value."%")
                    ->orwhere('stock.quantity','like',"%".$serach_value."%");
                });
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                    'store.id',
                    'product.model_category',
                    'product.color_code',
                    'stock.quantity',
                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('id','asc');      
        
        $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);  
    }
    public function stockListNew(){
        
        $number_of_store = Store::whereIn('id',CustomHelpers::user_store())
            ->select('id','store_type',
            DB::Raw('concat(name,"-",store_type) as name'),
            DB::Raw('concat(name,"-",store_type,"-avail") as avail'),
            DB::Raw('concat(name,"-",store_type,"-damage") as damage'),
            DB::Raw('concat(name,"-",store_type,"-alloc") as alloc'),
            DB::Raw('concat(name,"-",store_type,"-sale") as sale'),
            DB::Raw('concat(name,"-",store_type,"-booking") as booking')
            )
            ->orderBy("store_type","ASC")->get();
        $data=array(
            'store'   =>  $number_of_store->toArray(),
            'layout'=>'layouts.main');
        return view('admin.stockListNew',$data);   
    }
    public function stockListNew_api(Request $request){

        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
           
        $productList = ProductModel::leftJoin('stock','product.id','stock.product_id')
        ->where('product.type','product')
        ->where('product.isforsale',1)
        ->select(
            DB::raw('concat(product.model_category,"-",product.model_name,"-",product.model_variant) as product_name'),
            'product.id as prodId',
            'color_code'
        )
        ->groupBy('model_name')
        ->groupBy('model_variant')
        ->groupBy('product.id');

            if(!empty($serach_value))
            {
                $productList->where(function($query) use ($serach_value){
                    $query->where('product.model_category','like',"%".$serach_value."%")
                    ->orwhere('product.color_code','like',"%".$serach_value."%")
                    ->orwhere('product.model_variant','like',"%".$serach_value."%")
                    ->orwhere('product.model_name','like',"%".$serach_value."%");
                });
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                    'product.model_category',
                    'product.color_code'
                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $productList->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $productList->orderBy('product.model_name','asc')->orderBy('product.color_code','asc');      
        
        $count = count( $productList->get()->toArray());

        $productList = $productList->offset($offset)->limit($limit)->get()->toArray();
        
        $store = Store::whereIn('id',CustomHelpers::user_store())
                    ->select('id as store_id','store_type',
                    DB::raw('concat(name,"-",store_type) as name'))
                        ->orderBy('store_type','ASC')->get()->toArray();

        for($i = 0 ; $i < count($productList) ; $i++)
        {
            for($j = 0 ; $j < count($store) ; $j++)
            {
            $prodMoveQty = SmPending::where('product_id',$productList[$i]['prodId'])
                    ->where('to_store',$store[$j]['store_id'])
                    ->select(DB::raw('IFNULL(sum(smdQty),0) as smdQty'),
                                DB::raw('IFNULL(sum(smddQty),0) as smddQty'))
                    ->first();
            $prodMoveQtyfrom = SmPending::where('product_id',$productList[$i]['prodId'])
                    ->where('from_store',$store[$j]['store_id'])
                    ->select(DB::raw('IFNULL(sum(smdQty),0) as smdQty'),
                                DB::raw('IFNULL(sum(smddQty),0) as smddQty'))
                    ->first();
            $prodStockQty = Stock::where('product_id',$productList[$i]['prodId'])
                            ->where('store_id',$store[$j]['store_id'])
                            ->select(DB::raw('IFNULL(sum(quantity),0) as quantity'),
                            DB::raw('IFNULL(sum(damage_quantity),0) as damage_quantity'),
                            DB::raw('IFNULL(sum(sale_qty),0) as sale'),
                            DB::raw('IFNULL(sum(booking_qty),0) as booking'))
                            ->first();
                
            if($prodMoveQty->smdQty > 0){
                    $qty = $prodStockQty->quantity-$prodMoveQty->smdQty;
                    $real_qty = $qty;
                    $howmuch = 0;
                    if($qty < 0){
                        $real_qty = 0;
                        $howmuch = $prodMoveQty->smdQty-$prodStockQty->quantity;
                    }
                    if($howmuch > 0){
                        $productList[$i][$store[$j]['name'].'-avail_title'] = 'Sale Quantity '.$howmuch.' Out of '.$prodMoveQty->smdQty;
                    }
                    $productList[$i][$store[$j]['name'].'-avail'] = $real_qty.' / '.$prodMoveQty->smdQty;
            }else{
                    $productList[$i][$store[$j]['name'].'-avail'] = $prodStockQty->quantity-$prodMoveQty->smdQty;
            }
            if($prodMoveQty->smddQty > 0){
                $productList[$i][$store[$j]['name'].'-damage'] = $prodStockQty->damage_quantity-$prodMoveQty->smddQty.' / '.$prodMoveQty->smddQty;
            }else{
                $productList[$i][$store[$j]['name'].'-damage'] = $prodStockQty->damage_quantity-$prodMoveQty->smddQty ;
            }
            $productList[$i][$store[$j]['name'].'-alloc'] = $prodMoveQtyfrom->smdQty+$prodMoveQtyfrom->smddQty ;
            $productList[$i][$store[$j]['name'].'-sale'] = $prodStockQty->sale;
            $productList[$i][$store[$j]['name'].'-booking'] = $prodStockQty->booking;
            $productList[$i][$store[$j]['name']] = $store[$j]['name'];
            
            }
        }
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $productList; 
        return json_encode($array);  
    }   
    public function stockListDel(Request $request,$id)
    {
        DB::beginTransaction();
        $del = ProductModel::where("id",$id)->delete();
        if($del)
        {
            $del1 = ProductDetails::where("product_id",$id)->delete();
            if($del1){
                DB::commit();
                return back()->with('success','Data deleted successfully');
            }
            DB::rollBack();
            return back()->with('error','Data deleted Unsuccessfully');
        }
        return back()->with('error','Data deleted Unsuccessfully');;
    }
    public function stockListUpdate(Request $request)
    {
        
        $arr = json_decode($request->input("bookData"),true);
        $schedule_date = $request->input("schedule_date");
        $user_id = Auth::id();
        $user_store_id = Users::where('id',$user_id)->first('store_id')->toArray()['store_id'];
      
        DB::beginTransaction();
        if(count($arr) > 0)
        {
            $i =0;$check_first_entry = 0;
            $temp_arr = array();
            try
            {
                foreach($arr as $key => $val)
                {
                    if($val['req_quan'] > 0)
                    {
                        $temp= array();$temp1 = array();
                        $str1 = $val['warehouse'];
                        $str = explode("_",$str1);
                        $stock_id = $str[1];
                        $store_name = $str[0];
                        $req_qty = $val['req_quan'];
                        $getstock = Stock::where('id',$stock_id)->first()->toArray();
                        $product_id = $getstock['product_id'];
                        $product_name = ProductModel::where('id',$product_id)->select(
                            DB::raw("concat(product.model_category,'-',product.model_name,'-',product.model_variant,'-',product.color_code) as product_name")
                            )->first()->toArray()['product_name'];
                        $temp_store_id = Store::where('name',$store_name)->first();
                        if($temp_store_id)
                        {
                            $store_id = $temp_store_id->toArray()['id'];
                        }
                        else
                        {
                            return back()->with('error','wrong store name');
                        }
                        $pre_qty = $getstock['quantity'];
                        //$getEngineFrame = ProductDetails::where('product_id',$product_id)->where("status",'ok')->get()->toArray();
                        
                        //$temp['store_name'] = $store_name;
                        //$temp['product_name'] = $product_name;
                        
                        //for stock movement table
                        $temp['quantity'] = $req_qty;                
                        $temp['from_store'] = $store_id;
                        $temp['to_store'] = $user_store_id;
                        $temp['waybill_id'] = 0;
                        $temp['loader_id'] = 0; 
                        $temp['moved_by']  = 0;
                        $temp['schedule_date'] = CustomHelpers::showDate($schedule_date,'Y-m-d G:i:s');
                        $temp['moved_date'] = CustomHelpers::showDate($schedule_date,'Y-m-d G:i:s');
                        $temp['status'] = 'pending';
                        
                            if(empty($temp_arr[$store_name]))
                            {
                                //new entry in stock movemrnt
                                $getStockMovementId = StockMovement::insertGetId($temp);
                                $temp_arr[$store_name] = $getStockMovementId;
                                $temp_arr[$store_name.'_total_qty'] = $temp['quantity'];
                                if(!$getStockMovementId)
                                {
                                    DB::rollBack();
                                }
                                else
                                {
                                    //process to entry in stock movement details
                                    for($i =0 ; $i < $req_qty ; $i++)
                                    {
                                        $temp1['stock_movement_id'] = $getStockMovementId;
                                        $temp1['product_id']=$product_id;
                                        $temp1['product_details_id'] = 0;
                                        $temp1['quantity'] = 1;
                                        
                                        $getStockMovementDeatilsId = StockMovementDetails::insertGetId($temp1);
                                        if(!$getStockMovementDeatilsId)
                                        {
                                            DB::rollBack();
                                        }
                                    }
                                }
                            }
                            else
                            {
                                //update quantity in stock movement
                                $temp_arr[$store_name.'_total_qty'] = $req_qty+$temp_arr[$store_name.'_total_qty'];
                                $temp1['req_qty'] = $temp_arr[$store_name.'_total_qty'];
                                $stockMovementId = $temp_arr[$store_name];
                                $stockupdateId = StockMovement::where("id",$stockMovementId)->update(['quantity'=>$temp1['req_qty']]);
                                //for stock movement details table
                                if(!$stockupdateId)
                                {
                                    DB::rollBack();
                                }
                                else
                                {

                                    for($i =0 ; $i < $req_qty ; $i++)
                                    {
                                        $temp2['product_id']=$product_id;
                                        $temp2['stock_movement_id'] = $stockMovementId;
                                        $temp2['product_details_id'] = 0;
                                        $temp2['quantity'] = 1;                            
                                        $getStockMovementDeatilsId = StockMovementDetails::insertGetId($temp2);

                                        if(!$getStockMovementDeatilsId)
                                        {
                                            DB::rollBack();
                                        }
                                    }
                                }

                            }
                            //update stock table from store
                            $updateStockQty_from = Stock::where('product_id',$product_id)
                            ->where('store_id',$store_id)->where('id',$stock_id)
                            ->update(['quantity'=>$pre_qty-$req_qty]);
                            if($updateStockQty_from)
                            {

                                //update stock table to store
                                $searchStockQty_to = Stock::where('product_id',$product_id)
                                ->where('store_id',$user_store_id)->first();
                                if(!$searchStockQty_to)
                                {
                                    $updateStockQty_to = Stock::insertGetID([
                                        'quantity'  =>  $req_qty,
                                        'product_id'    =>  $product_id,
                                        'store_id'  =>  $user_store_id
                                        ]);
                                    }
                                    else
                                    {
                                        $updateStockQty_to = Stock::where('product_id',$product_id)
                                        ->where('store_id',$user_store_id)
                                        ->update(['quantity'=>$pre_qty+$req_qty]);
                                    }
                            }
                        $i++;
                    }
                }
            }
            catch(\Illuminate\Database\QueryException $ex) {
                DB::rollBack();
                return back()->with('error','some error occurred'.$ex->getMessage())->withInput();
            }
        }
            DB::commit();
            
            // $data=array(
            //     'book_data'   =>  $temp,
            //     'product_id'    =>  1,
            //     'layout'=>'layouts.main');
            // return view("admin.stockListBook",$data);
    
            return back()->with("success","Your Request has been generated");
        
        
    }
    public function stockListAccept(Request $request,$stock_id)
    {
        //return $request;
        $get_data_by_stock_id = StockMovement::leftJoin('stock_movement_details','stock_movement_details.stock_movement_id','stock_movement.id')
                                ->where('stock_movement.id',$stock_id)
                                ->leftJoin('store as fromstore','fromstore.id','stock_movement.from_store')
                                ->leftJoin('store as tostore','tostore.id','stock_movement.to_store')
                                ->leftJoin('product','stock_movement_details.product_id','product.id')
                                // ->select(
                                // DB::raw("concat(product.model_category,'-',product.model_name,'-',product.model_variant,'-',product.color_code) as product_name"),

                                // )
                                ->groupBy('stock_movement_details.product_id')
                                ->select(['stock_movement.id as stock_mov_id',
                                'stock_movement_details.id as stock_mov_det_id',
                                'product_id',
                                //  'concat(product.model_category,"-",product.model_name,"-",product.model_variant,"-",product.color_code) as product_name',
                                DB::raw("concat(product.model_category,'-',product.model_name,'-',product.model_variant,'-',product.color_code) as product_name"),
                                DB::raw("count(stock_movement_details.product_id) as req_qty"),
                                'stock_movement.quantity',
                                'from_store',
                                'fromstore.name as from_store_name',
                                'tostore.name as to_store_name',
                                'to_store',
                                'stock_movement.status']);

        if($get_data_by_stock_id->get())
        {
            $getData = $get_data_by_stock_id->get()->toArray();
            if($getData[0]['status'] == 'pending')
            {
                $i=0;$temp = array();
                foreach($getData as $key => $val)
                {
                    $temp[$i]['stock_mov_id'] = $val['stock_mov_id'];
                    $temp[$i]['stock_mov_det_id'] = $val['stock_mov_det_id'];
                    $temp[$i]['product_id'] = $val['product_id'];
                    $temp[$i]['product_name'] = $val['product_name'];
                    $temp[$i]['req_qty'] = $val['req_qty'];
                    $temp[$i]['quantity'] = $val['quantity'];
                    $temp[$i]['from_store'] = $val['from_store'];
                    $temp[$i]['from_store_name'] = $val['from_store_name'];
                    $temp[$i]['to_store_name'] = $val['to_store_name'];
                    $temp[$i]['to_store'] = $val['to_store'];
                    $temp[$i]['status'] = $val['status'];

                    $getEngineFrame = ProductDetails::where('product_id',$val['product_id'])
                                                    ->where('store_id',$val['from_store'])
                                                    ->where("status",'ok')->get()->toArray();
                    $j=0;
                    foreach($getEngineFrame as $key1 => $val1)
                    {
                        $temp[$i]['framedata'][$j]['frame'] = $val1['frame'];
                        $temp[$i]['framedata'][$j]['engine'] = $val1['engine_number'];
                        $temp[$i]['framedata'][$j]['product_details_id'] = $val1['id'];
                        $j++;
                    }
                    $i++;
                }
            }
            else
            {
                return back()->with("error",'Already Submit It');
            }
        }
        $loader = Loader::where('status','Active')->get(['id','truck_number'])->toArray();
        $data =array(
            'loader' => $loader,
            'book_data' => $temp,
            'layout'=>'layouts.main'
        );
        return view('admin.stockListBook',$data);

    }

    public function stockListAccept_DB(Request $request,$stock_id)
    {
        $product_id = $request->input('prod_name');
        $no_of_prod = count($request->input('prod_name'));
        $valarr = [     
            'from_store'=>'required',
            'to_store'=>'required',
            'loader'=>'required|numeric',
            'prod_name.*'=>'required',
            'req_qty.*' => 'required',
        ];
        $valmsg = [
            'loader.required'=>'This Field is required',
            'loader.numeric'=>'This Field is required',
        ];
        for($i = 0; $i < $no_of_prod ; $i++)
        {
            $valarr =  array_merge($valarr ,array(
                'frame_'.$product_id[$i].'_.*'    =>  'required|numeric'
            ));
            $valmsg =  array_merge($valmsg ,array(
                'frame_'.$product_id[$i].'_.*.required'    =>  'This Field is Required',
                'frame_'.$product_id[$i].'_.*.numeric'    =>  'Frame This Field is Required'
            ));
        }
        $this->validate($request,$valarr,$valmsg);
        
        DB::beginTransaction();
        try
        {

            $from_store_id = $request->input('from_store');
            $to_store_id = $request->input('to_store'); 
            
            $loader_id = $request->input('loader');
            $update_stock_mov = StockMovement::where("id",$stock_id)
            ->update(['loader_id'=>$loader_id,'status' => 'running']);
           
            if($update_stock_mov)
            {
                for($i = 0; $i < $no_of_prod ; $i++)
                {
                    $frames = $request->input('frame_'.$product_id[$i].'_');
                    
                    for($j = 0; $j < count($frames) ; $j++)
                    {
                        $normal_id = StockMovementDetails::where("stock_movement_id",$stock_id)
                                            ->where("product_id",$product_id[$i])
                                            ->where("product_details_id",0)->first('id')['id'];

                        $update_stock_mov_d =StockMovementDetails::where("id",$normal_id)->update(["product_details_id"=>$frames[$j]]);
                        if($update_stock_mov_d)
                        {

                            $update_prod_d = ProductDetails::where("id",$frames[$j])
                            ->update(["store_id"=>$to_store_id]);
                        }
                        else
                        {
                            DB::rollBack();
                        }
                   }
                }
            }
            else
            {
                DB::rollBack();
            }
        }
        catch(\Illuminate\Database\QueryException $ex) {
            DB::rollBack();
            return back()->with('error','some error occurred'.$ex->getMessage())->withInput();
        }
        DB::commit();
        return redirect('/admin/stock/movement/list')->with("success",'submit successfully'); 
    }

    public function generateWaybill(Request $request, $stock_id)
    {
        $data = array(
            'stock_id' => $stock_id,
            'layout'    =>  'layouts.main'
        );
        return view('admin.generateWaybill',$data);
    }
    public function waybill_DB(Request $request, $stock_id)
    {
        $this->validate($request,[
            'waybill' => 'required'
        ],[
            'waybill.required' => 'This Field is Required'
        ]);
        try{
            DB::beginTransaction();
            $user_id = Auth::Id();
            $waybill = $request->input('waybill');
            $id = Waybill:: insertGetId([
                'waybill_number' => $waybill,
                'amount'    =>  20000,
                'created_by'    =>  $user_id,
                'stock_movement_id' => $stock_id
            ]);
            if($id)
            {
                $update_stoc_mov = StockMovement::where('id',$stock_id)->where('status','running')
                                    ->update(['waybill_id' => $id]);
                if(!$update_stoc_mov)
                {
                    DB::rollBack();
                    return back()->with('error','!! Try Again !!')->withInput();
                }
            }
            else
            {
                DB::rollBack();
                return back()->with('error','!! Try Again !!')->withInput();
            }
        }
        catch(\Illuminate\Database\QueryException $ex) {
            DB::rollBack();
            return back()->with('error','some error occurred'.$ex->getMessage())->withInput();
        }
        DB::commit();
        return redirect('/admin/stock/movement/list')->with("success",'submit successfully'); 
   

    }
    public function stockMoved($stock_id)
    {
        try{
            DB::beginTransaction();
            $moved = StockMovement::where("id",$stock_id)
                    ->update(['status'=>'moved']);
            if(!$moved)
            {
                DB::rollBack();
                return back()->with('error','!! Try Again !!')->withInput();
            }
        }
        catch(\Illuminate\Database\QueryException $ex) {
            DB::rollBack();
            return back()->with('error','some error occurred'.$ex->getMessage())->withInput();
        }
        DB::commit();
        return redirect('/admin/stock/movement/list')->with("success",'Submit Successfully'); 

    }
    
    public function damageProduct(Request $request)
    {
        $arr = json_decode($request->input("claimData"),true);
        $totalFill = 0;
        $damageId = 0;
        $total = count($arr);
        try{
            DB::beginTransaction();
            if(count($arr)>0)
            {
                    $insertDamageClaim =0 ;
                    for($i =0 ; $i<count($arr) ; $i++)
                    {
                        $commonData = array('product_details_id'    =>  $arr[$i]['prod_det_id'],
                        'est_amount'    =>  0,
                        'status'    =>  'pending',
                        'repair_amount' =>  0,
                        );
                        $getProIdStoreId = ProductDetails::where('id',$arr[$i]['prod_det_id'])
                                        ->first(['product_id','store_id']);
                        $product_id = $getProIdStoreId->toArray()['product_id'];
                        $store_id = $getProIdStoreId->toArray()['store_id'];
                        $updateStatus = ProductDetails::where('id',$arr[$i]['prod_det_id'])
                                        ->update(['status'=>'damage']);
                        $getPreviousQty = Stock::where('product_id',$product_id)
                                        ->where('store_id',$store_id)
                                        ->first(['quantity','damage_quantity'])->toArray();
                        $updateQty = Stock::where('product_id',$product_id)
                                    ->where('store_id',$store_id)
                                    ->update(['quantity'=>$getPreviousQty['quantity']-1,
                                                'damage_quantity'=>$getPreviousQty['damage_quantity']+1]);
                        $findLRN = DamageClaim::leftJoin('unload','damage_claim.unload_id','unload.id')
                                    ->where('unload.load_referenace_number',$arr[$i]['lrn'])
                                    ->where('damage_claim.status','pending')
                                    ->where('damage_claim.invoice',null)
                                    ->select('damage_claim.id as damage_id')
                                    ->first();

                        if(empty($findLRN))
                        {
                            if($i == 0)
                            {
                                $insertDamageClaim = DamageClaim::insertGetId([
                                    'unload_id' =>  Unload::where('load_referenace_number',$arr[$i]['lrn'])
                                                            ->first('id')->toArray()['id'],
                                    'settlement'    =>  'claim',
                                    'status'    =>  'pending',
                                ]);
                                $id = array(
                                    'damage_claim_id' =>  $insertDamageClaim
                                );
                                $dataMerge = array_merge($id,$commonData); 
                                
                                $insertDamageDetail = DamageDetail::insertGetId($dataMerge);
                                $damageDetailId = $insertDamageDetail;
                            }
                        }
                        else{
                            $insertDamageClaim = $findLRN->toArray()['damage_id'];
                            
                            $id = array(
                                'damage_claim_id' =>  $insertDamageClaim
                            );
                            $dataMerge = array_merge($id,$commonData); 
                            $insertDamageDetail = DamageDetail::insertGetId($dataMerge);

                            
                        }
                        $damageId = $insertDamageClaim;
                    }
               // }
            }
        }
        catch(\Illuminate\Database\QueryException $ex) {
            DB::rollBack();
            return back()->with('error','some error occurred'.$ex->getMessage())->withInput();
        }
        DB::commit();
        //$this->damageForm($request,$damageId);
        return redirect('/admin/damage/claim/request/'.$damageId);

    }
    public function damageForm(Request $request, $damage_claim_id)
    {
       DB::beginTransaction();
        if ($request->isMethod('post')) {
            
            $this->validate($request,[
                'est_amount' => 'required',
                'description'   =>  'required'
            ],[
                'est_amount.required' => 'This Field is Required',
                'description.required'  =>  'This Field is Required'
            ]);
            $damageDetailId = $request->input('damage_detail_id'); 
            $est_amount = $request->input('est_amount'); 
            $desc = $request->input('description'); 
            $rep_amount = 0;
            if($request->input('rep_amount')){
                $rep_amount = $request->input('rep_amount'); 
            }
            
            if($damage_claim_id && $damageDetailId && $est_amount && $desc )
            {
                $updateDamageClaimDetail = DamageDetail::where('id',$damageDetailId)
                                            ->update(['est_amount'=>$est_amount,
                                                        'damage_desc' => $desc,
                                                        'repair_amount' => $rep_amount]);
                if($updateDamageClaimDetail)
                {
                    $getPreviousAmount = DamageClaim::where('id',$damage_claim_id)
                                        ->first(['claim_amount'])->toArray()['claim_amount'];
                    $updateClaimAmount = DamageClaim::where('id',$damage_claim_id)
                                        ->update(['claim_amount' => $getPreviousAmount+$est_amount]);
                    if($updateClaimAmount)
                    {
                        DB::commit();
                    }
                }
            }

        }
       // DB::enableQueryLog();
        $get_damageDetail = DamageDetail::leftJoin('product_details','product_details.id','damage_details.product_details_id')
                            ->leftJoin('product','product.id','product_details.product_id')
                            ->where('damage_details.est_amount',0)
                            ->where('damage_claim_id',$damage_claim_id)
                            ->where('damage_details.damage_desc',null)
                            ->select(
                                DB::raw('concat(product.model_category,"-",product.model_name,"-",product.model_variant) as product_name'),
                                DB::raw('product.color_code as color_code'),
                                DB::raw('product_details.frame'),
                                DB::raw('product_details.engine_number'),
                                DB::raw('damage_details.id as damage_detail_id')
                            )->get();
        // dd(DB::getQueryLog());
        
        if(count($get_damageDetail) > 0)
        {
            $getDataForNewEntry = $get_damageDetail->toArray();
            
            $data = array(
                'reuqestData'  =>  $getDataForNewEntry[0],
                'damage_claim_id'   =>  $damage_claim_id,
                'layout'    =>  'layouts.main'                
            );
           
            return view('admin.damageForm',$data);
        }
        else{
            return redirect('/admin/product/list')->with('success','Data Successfully Claimed');
        }
    }
    
    // battery

    function create_battery(Request $request)
    {
        $select_lrn = '';
        if($request->input('lrn')){
            $select_lrn = $request->input('lrn');
        }
        $store = CustomHelpers::user_store();
        $user_store = join(',',$store);
        $query = "SELECT
                    unload.id,
                    unload.load_referenace_number,
                    (select product.id from product left join product_details on product_details.product_id = product.id 
                                            where product.type = 'battery' and product_details.unload_id = unload.id and 
                                            product_details.store_id = unload.store LIMIT 1) as pd_id
                    
                FROM
                    `unload`
                LEFT JOIN `store` ON `unload`.`store` = `store`.`id` 
                WHERE
                    `store`.`id` IN (".$user_store.")
                ORDER BY
                    `id`
                DESC";
        $lrn = DB::select(DB::raw('select * from ('.$query.') sq where pd_id is null'));
        $load=Unload::all();
        $store = Store::whereIn('id',CustomHelpers::user_store())
                    ->select('id','store_type',DB::raw('concat(store.name,"-",store.store_type) as name'))
                    ->orderBy('store_type','ASC')
                    ->get();
        $data = array(
            'load'=>$load,
            'store' =>  $store,
            'lrn'   =>  $lrn,
            'select_lrn'    =>  $select_lrn,
            'layout'=>'layouts.main'
        );
        return view('admin.battery.create_battery',$data);
    }
    public function create_battery_db(Request $request){
        DB::beginTransaction();
        try {
            $this->validate($request,[
                'loadRefNum'=>'required',
                // 'store'=>'required',
                'part' =>  'required',
                'part_type'=>'required',
                'mfh_d'=>'required'
            ],[
                'loadRefNum.required'=> 'This is required.',
                // 'store.required'=> 'This is required.',
                'part_type.required'=> 'This is required.',
                'mfh_d.required'=> 'This is required.',
                'part.required'=> 'This is required.'
            ]);
            // $date2 = str_replace('/', '-', date_format(date_create($request->input('mfh_d')),"Y/m/d"));
            // $date1 = str_replace('/', '-', date("Y/m/d"));
            // $diff = abs(strtotime($date2) - strtotime($date1));
            // $years = floor($diff / (365*60*60*24));
            // $months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));
            // $days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));

            $mfh = str_replace('/','-',date_format(date_create($request->input('mfh_d')),"Y/m/d"));
            $now = date('Y-m-d');
            $to = \Carbon\Carbon::createFromFormat('Y-m-d',$mfh);
            $from = \Carbon\Carbon::createFromFormat('Y-m-d',$now);
            $months = $to->diffInMonths($from);
           
            
            if ($months > 3) {
                $status = "expired";
            } else { 
                $status = "ok";
            }
            $get = ProductModel::where('type','battery')
                    ->where('part_type',$request->input('part_type'))
                    ->first();

            $frame = ProductDetails::where('frame',$request->input('part'))
                    ->first();
            
            $unload = Unload::where('id',$request->input('loadRefNum'))->first();
            if(!isset($unload->id)){
                return back()->with('error','Unload Data Not Found.')->withInput();
            }

            if(!$frame)
            {
                if($get)
                {
                    $get = $get->toArray();
                    $battery = ProductDetails::insertGetId(
                        [
                            'unload_id' => $request->input('loadRefNum'),
                            'product_id'    =>  $get['id'],
                            'frame' =>  $request->input('part'),
                            'engine_number' =>  'nothing',
                            'store_id'  =>  $unload->store,
                            'manufacture_date'  => date("Y-m-d", strtotime($request->input('mfh_d'))) ,
                            'status'  => $status

                        ]
                    );
                    $getStock = Stock::where('product_id',$get['id'])
                                        ->where('store_id',$unload->store)->first();
                    if($getStock)
                    {
                        Stock::where('id',$getStock['id'])
                            ->update([
                                'quantity'  =>  $getStock->toArray()['quantity']+1
                            ]);
                        
                    }
                    else
                    {
                        Stock::insertGetId(
                                [
                                    'product_id'    =>  $get['id'],
                                    'quantity'  =>  1,
                                    'store_id'  =>  $unload->store
                                ]);
                    }
                }
                else
                {
                    $battery = ProductModel::insertGetId(
                        [
                            'model_name'=>'battery',
                            'model_variant'=>$request->input('part_type'),
                            'color_code'=>'',
                            'type'=>'battery',
                            'part_type'=>$request->input('part_type'),
                        ]
                    );
                    ProductDetails::insertGetId(
                        [
                            'unload_id' => $request->input('loadRefNum'),
                            'product_id'    =>  $battery,
                            'frame' =>  $request->input('part'),
                            'engine_number' =>  'nothing',
                            'store_id'  =>  $unload->store,
                            'manufacture_date'  => date("Y-m-d", strtotime($request->input('mfh_d'))) ,
                            'status'  => $status
                        ]
                    );
                    
                    $stock = Stock::insertGetId(
                            [
                                'product_id'    =>  $battery,
                                'quantity'  =>  1,
                                'store_id'  =>  $unload->store
                            ]);
                }
            }
            else
            {
                DB::rollback();
                return redirect('/admin/battery/create')->with('error','Part Number Already Exits.')->withInput();
            }
            if($battery==NULL) 
            {
                DB::rollback();
                return redirect('/admin/battery/create')->with('error','Some Unexpected Error occurred.')->withInput();
            }
            else{
                DB::commit();
                return redirect('/admin/battery/create')->with('success','Successfully Created Battery Form.'); 
             }    
        
        }  catch(\Illuminate\Database\QueryException $ex) {
            DB::rollBack();
            return redirect('/admin/battery/create')->with('error','some error occurred'.$ex->getMessage())->withInput();
        }
    }
    public function battery_list(){

        $store = Store::whereIn('id',CustomHelpers::user_store())
                    ->select('id',DB::raw('concat(store.name,"-",store.store_type) as name'))->get();
        $data=array('store'=>$store,'layout'=>'layouts.main');
        return view('admin.battery.battery_summary', $data);   
    }

    public function oldbattery_list(){
        $data=array('layout'=>'layouts.main');
        return view('admin.oldbattery_list', $data); 
    }

    public function battery_list_api(Request $request) {      
        $search = $request->input('search');
        $store_name = $request->input('store_name');
        $store_count = $request->input('store_count');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
           
           $store_id = explode(',',Auth::user()->store_id);

            $api_data= ProductModel::RightJoin('product_details','product.id','product_details.product_id')
                ->leftJoin('product_audit','product_details.id','product_audit.product_id')
                ->leftJoin('unload','product_details.unload_id','unload.id')
                ->leftJoin('store','product_details.store_id','store.id')
                 ->whereIn('store.id',$store_id)
                ->where('type','battery')
            ->select(
                'product.id as product_id',
                'product_audit.id as product_audit_id',
                'product_audit.status as audit_status',
                'product_audit.comments',
                'product_details.id',
                'product.model_category',
                'product.model_name',
                'product.model_variant',
                'product.color_code',
                'product_details.htr_code',
                'product_details.frame',
                'product.type',
                'product.part_type',
                'product_details.status',
                DB::raw('concat(store.name,"-",store.store_type) as store'),
                DB::raw('TIMESTAMPDIFF( YEAR, product_details.manufacture_date, now() ) as year'),
                DB::raw('TIMESTAMPDIFF( MONTH, product_details.manufacture_date, now() ) % 12 as month'),
                DB::raw('ROUND(TIMESTAMPDIFF( DAY, product_details.manufacture_date, now() ) % 30.4375) as day'),
                // DB::raw('datediff(curdate(),product_details.manufacture_date) as battery_age'),
                'product_details.manufacture_date',
                'unload.load_referenace_number',
                DB::raw('DATE(product_details.created_at) as create_date')
            ); 
            if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('product.model_category','like',"%".$serach_value."%")
                    ->orwhere('product.model_name','like',"%".$serach_value."%")
                    ->orwhere('product.model_variant','like',"%".$serach_value."%")
                    ->orwhere('product_audit.status','like',"%".$serach_value."%")
                    ->orwhere('product.color_code','like',"%".$serach_value."%")
                    ->orwhere('product_details.frame','like',"%".$serach_value."%")
                    ->orwhere('product.type','like',"%".$serach_value."%")
                    ->orwhere('product.part_type',"%".$serach_value."%")
                    ->orwhere('product_details.htr_code','like',"%".$serach_value."%")
                    ->orwhere('product_details.status','like',"%".$serach_value."%")
                    ->orwhere('unload.load_referenace_number','like',"%".$serach_value."%")
                    ->orwhere('store.name','like',"%".$serach_value."%")
                    ->orwhere('product_details.created_at','like',"%".$serach_value."%");
                });
            }
            if(!empty($store_name))
            {             
                if($store_count > 1){

                 $api_data->where(function($query) use ($store_name){
                        $query->whereIn('store.id',$store_name);
                    });
                }else{

                     $api_data->where(function($query) use ($store_name){
                        $query->where('store.id',$store_name);
                    });
                }
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                    'unload.load_referenace_number',
                    'product_details.frame',
                    'day',
                    'product.part_type',
                    'product_details.status',
                    'product.type',
                    'product_details.status',
                    'audit_status',
                    'product_details.manufacture_date',
                    'product_details.htr_code',
                    'product_details.created_at',
                    'store.name'
                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('product_details.id','desc');      
        
        $count = count($api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array); 
    }

    public function oldbattery_list_api(Request $request) {        
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
           DB::enableQueryLog();
            $api_data= ProductModel::leftJoin('product_details','product.id','product_details.product_id')
            ->leftJoin('unload','product_details.unload_id','unload.id')
            ->where('type','battery')
            ->where(DB::raw('DATEDIFF( CURDATE(),product_details.manufacture_date)'),'>=',80)
            ->select(
                'product_details.id',
                'product.model_category',
                'product_details.htr_code',
                'product.model_name',
                'product.model_variant',
                'product.color_code',
                'product_details.frame',
                'product.type',
                'product.part_type',
                'product_details.status',
                DB::raw('DATEDIFF( CURDATE(),product_details.manufacture_date) as day'),
                'product_details.manufacture_date',
                'unload.load_referenace_number'
            );
            if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('product.model_category','like',"%".$serach_value."%")
                    ->orwhere('product.model_name','like',"%".$serach_value."%")
                    ->orwhere('product.model_variant','like',"%".$serach_value."%")
                    ->orwhere('product.color_code','like',"%".$serach_value."%")
                    ->orwhere('product_details.frame','like',"%".$serach_value."%")
                    ->orwhere('product.type','like',"%".$serach_value."%")
                    ->orwhere('product.part_type',"%".$serach_value."%")
                    ->orwhere('product_details.status','like',"%".$serach_value."%")
                    ->orwhere('product_details.htr_code','like',"%".$serach_value."%")
                    ->orwhere('unload.load_referenace_number','like',"%".$serach_value."%");
                });
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                    'unload.load_referenace_number',
                    'product.type',
                    'product.part_type',
                    'product_details.status',
                    'product.model_category',
                    'product.model_name',
                    'product.model_variant',
                    'product.color_code',
                    'product_details.manufacture_date',
                    'product_details.htr_code',
                    'product_details.part_number',
                  
                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('product_details.unload_id','desc');      
        
        $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);   
  
    }

    public function battery_get($id){
        $product = ProductDetails::where("id",$id)
                    ->pluck("htr_code","id");
        return response()->json($product);
    }
    // public function batteryHtrupdate(Request $request){
    //     try {
    //         $this->validate($request,[
    //             'id'=>'required',
    //             'htrCode'=>'required',
    //         ],[
    //             'id.required'=> 'This is required.',
    //             'htrCode.required'=> 'This is required.',  
    //         ]);
    //         $htrdata = ProductDetails::where('id',$request->input('id'))->update(
    //             [
    //                 'htr_code'=>$request->input('htrCode'),
    //             ]
    //         );
    //         if($htrdata==NULL) 
    //         {
    //             DB::rollback();
    //             return redirect('/admin/battery/list')->with('error','Some Unexpected Error occurred.');
    //         }
    //         else{
    //             return redirect('/admin/battery/list')->with('success','Successfully Updated HTR Number .'); 
    //          }    
        
    // }  catch(\Illuminate\Database\QueryException $ex) {
    //     return redirect('/admin/battery/list')->with('error','some error occurred'.$ex->getMessage());
    // }
    // }
    public function batteryOldHtrUpdate(Request $request){
        try {
            $this->validate($request,[
                'id'=>'required',
                'htrCode'=>'required',
            ],[
                'id.required'=> 'This is required.',
                'htrCode.required'=> 'This is required.',  
            ]);
            $battery_id = $request->input('id');
            $htrdata = ProductDetails::where('id',$request->input('id'))->update(
                [
                    'htr_code'=>$request->input('htrCode'),
                ]
            );
            if($htrdata==NULL) 
            {
                DB::rollback();
                return redirect('/admin/oldbattery/list')->with('error','Some Unexpected Error occurred.');
            }
            else{
                /* Add action Log */
                CustomHelpers::userActionLog($action='Battery HTR Update',$battery_id);
                return redirect('/admin/oldbattery/list')->with('success','Successfully Updated HTR Number .'); 
            }    
        
        }  catch(\Illuminate\Database\QueryException $ex) {
            return redirect('/admin/oldbattery/list')->with('error','some error occurred'.$ex->getMessage());
        }
    }

    public function getBasicPrice($id) {
         $product = ProductModel::where("id",$id)
                    ->pluck("basic_price","id");
        return response()->json($product);
    }
    public function getDuration($id) {
         $product = ProductModel::where("id",$id)
                    ->pluck("st_warranty_duration","id");
        return response()->json($product);
    }

    public function updateBasicPrice(Request $request){
        $id = $request->input('product_id');
        $bprice = $request->input('basic_price');
        try {
            $this->validate($request,[
                'product_id'=>'required',
                'basic_price'=>'required',
            ],[
                'product_id.required'=> 'This is required.',
                'basic_price.required'=> 'This is required.',  
            ]);
            $updatedata = ProductModel::where('id',$request->input('product_id'))->update(
                [
                    'basic_price'=>$request->input('basic_price'),
                ]
            );
            if($updatedata==NULL) 
            {
                   return redirect('/admin/product/list')->with('error','some error occurred'.$ex->getMessage());
            }
            else{
                  return response()->json($updatedata); 
             }    
        
        }  catch(\Illuminate\Database\QueryException $ex) {
            return redirect('/admin/product/list')->with('error','some error occurred'.$ex->getMessage());
        }
    }

        public function updateDuration(Request $request){
        $id = $request->input('product_id');
        $bprice = $request->input('st_warranty_duration');
        try {
            $this->validate($request,[
                'product_id'=>'required',
                'st_warranty_duration'=>'required|numeric|min:0',
            ],[
                'product_id.required'=> 'This is required.',
                'st_warranty_duration.required'=> 'This is required.',  
            ]);
            $updatedata = ProductModel::where('id',$request->input('product_id'))->update(
                [
                    'st_warranty_duration'=>$request->input('st_warranty_duration'),
                ]
            );
            if($updatedata==NULL) 
            {
                   return redirect('/admin/product/list')->with('error','some error occurred'.$ex->getMessage());
            }
            else{
                  return response()->json($updatedata); 
             }    
        
    }  catch(\Illuminate\Database\QueryException $ex) {
        return redirect('/admin/product/list')->with('error','some error occurred'.$ex->getMessage());
    }
    }

    public function idealStock_old() {
        $data=array('layout'=>'layouts.main');
        return view('admin.idealstock_list_old', $data); 
    }

   

    public function idealstock_list_old(Request $request) {
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
        
      $api_data = Stock::leftJoin('product','stock.product_id','product.id')
            ->leftJoin('store','stock.store_id','store.id')
            ->whereIn('store.id',CustomHelpers::user_store())
            ->select(
                'stock.id',
                'stock.quantity',
                'stock.damage_quantity',
                'stock.min_qty',
                'product.model_category',
                'product.model_variant',
                'product.model_name',
                DB::raw('concat(store.name,"-",store.store_type) as name'),
                'stock.created_at'
            );
            if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('stock.quantity','like',"%".$serach_value."%")
                    ->orwhere('stock.damage_quantity','like',"%".$serach_value."%")
                    ->orwhere('stock.min_qty','like',"%".$serach_value."%")
                    ->orwhere('product.model_category','like',"%".$serach_value."%")
                    ->orwhere('product.model_variant','like',"%".$serach_value."%")
                    ->orwhere('product.model_name','like',"%".$serach_value."%")
                    ->orwhere('store.name','like',"%".$serach_value."%")
                    
                    ;
                });
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                'stock.id',
                'stock.quantity',
                'stock.damage_quantity',
                'stock.min_qty',
                'product.model_category',
                'product.model_variant',
                'product.model_name',
                'store.name',
                'stock.created_at'
                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('stock.id','desc');      
        $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

    public function idealStock() {

        $number_of_store = Store::whereIn('id',CustomHelpers::user_store())
            ->select('id','store_type',
            DB::Raw('concat(name,"-",store_type) as name'),
            DB::Raw('concat(name,"-",store_type,"-min_qty") as min_qty'),
            DB::Raw('concat(name,"-",store_type,"-quantity") as quantity')
            )
            ->orderBy("store_type","ASC")->get();

        $data=array(
            'store' =>  $number_of_store,
            'layout'=>'layouts.main'
        );
        return view('admin.idealstock_list', $data); 
    }

    public function idealstock_list(Request $request) {
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;

        $col_data = $request->input('col_data');
        $sort_data_query = array();
        foreach($col_data as $k => $v)
        {
            $query[$k] = "IFNULL((select min_qty from stock where stock.product_id = prodId and stock.store_id = ".$v['id']."),0) as '".$v['min_qty']."'".
                    ",IFNULL((select quantity from stock where stock.product_id = prodId and stock.store_id = ".$v['id']."),0) as '".$v['quantity']."'";
            
            $sort_data_query[$k] = $v['min_qty'];
        }
        $query = join(",",$query);
        
        $productList = IdealStockProduct::select('prodId',
                                    DB::raw('concat("prodId-",prodId) as prodIdClass'),
                                    'product_name',
                                    DB::raw($query)
                    );

            if(!empty($serach_value))
            {
                $productList->where(function($query) use ($serach_value){
                    $query->where('product_name','like',"%".$serach_value."%");
                });
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = array_merge([
                    //'store.id',
                    'product_name'
                ],$sort_data_query);
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $productList->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else 
                $productList->orderBy('product_name','asc');  
        
        $count = $productList->count();
        $productList = $productList->offset($offset)->limit($limit)->get()->toArray();
        
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $productList; 
        return json_encode($array);
    }
    public function updateminqty(Request $request){

        $prodId = $request->input('prodId');
        $storeId = $request->input('storeId');
        $min_qty = $request->input('min_qty');
        try{
            $find = Stock::where('product_id',$prodId)
                    ->where('store_id',$storeId)->first();
            if(isset($find->id))
            {
                $data =  Stock::where('id',$find->id)
                    ->update(['min_qty' =>  $min_qty]);
            }
            else{
                // insert 
                $data = Stock::insertGetId(['product_id'    =>  $prodId,
                                'quantity'  =>  0,
                                'min_qty'   =>  $min_qty,
                                'store_id'  =>  $storeId
                        ]);
            }
            if(!$data)
            {
                return 'error';
            }
        }
        catch(\Illuminate\Database\QueryException $ex) {
            return 'some error occurred'.$ex->getMessage();
        }
        
        return 'success';

    }

    function import_battery(Request $request)
    {
        $select_lrn = '';
        if($request->input('lrn')){
            $select_lrn = $request->input('lrn');
        }
        $store = CustomHelpers::user_store();
        $user_store = join(',',$store);
        $query = "SELECT
                    unload.id,
                    unload.load_referenace_number,
                    (select product.id from product left join product_details on product_details.product_id = product.id 
                                            where product.type = 'battery' and product_details.unload_id = unload.id and 
                                            product_details.store_id = unload.store LIMIT 1) as pd_id
                    
                FROM
                    `unload`
                LEFT JOIN `store` ON `unload`.`store` = `store`.`id` 
                WHERE
                    `store`.`id` IN (".$user_store.")
                ORDER BY
                    `id`
                DESC";
        $lrn = DB::select(DB::raw('select * from ('.$query.') sq where pd_id is null'));
        $load=Unload::all();
        $store = Store::whereIn('id',CustomHelpers::user_store())
                    ->select('id','store_type',DB::raw('concat(store.name,"-",store.store_type) as name'))
                    ->orderBy('store_type','ASC')
                    ->get();
        $data = array(
            'load'=>$load,
            'store' =>  $store,
            'lrn'   =>  $lrn,
            'select_lrn'    =>  $select_lrn,
            'layout'=>'layouts.main'
        );
        return view('admin.battery.batteryImport',$data);
    }
   
    public function import_battery_db(Request $request) {

       $this->validate($request, [
            'excel'  => 'required|mimes:xls,xlsx',
            'lrn'   =>  'required'
            ],
            [
                'excel.required'  =>  'This Field is Required',
                'lrn.required'  =>  'This Field is Required'
            ]);
        try
        {
            $table = array('product','product_details','stock');
            CustomHelpers::resetIncrement($table);

            // $store = $request->input('store');
            $lrn = $request->input('lrn');
            $get_lrn = Unload::where('id',$lrn)->first();
            if(!isset($get_lrn->id)){
                return back()->with('error','Error, Load Referenace # is Wrong.')->withInput();
            }
            $no_of_battery = $get_lrn->battery; 
            $count_batteryImport = 0;
            $enter_lrn = $get_lrn->load_referenace_number;
            $enter_unloadId = $get_lrn->id;
            if($request->file('excel'))                
            {
                $path = $request->file('excel');                
                $data = Excel::toArray(new Import(),$path);
                
                if(count($data) > 0 && $data)
                 {
                    DB::beginTransaction();
                    $header = 0;
                    $error ='';
                    //print_r($data); die();
                    foreach($data as $key => $value) {
                        $unique_load_ref_no = $value[1][0]; // match all rows in a sheet
                        $get_unload_id = Unload::where('load_referenace_number',$value[1][0])->first();
                        $get_store_id = $get_unload_id;

                       // echo $get_unload_id->id." == ".$enter_unloadId." == ".$get_unload_id->id;
                        if(isset($get_unload_id->id) && ($enter_unloadId == $get_unload_id->id))
                        {
                            //die("@1");
                            $unload_id = $get_unload_id->toArray()['id'];
                            $store_id = $get_store_id->toArray()['store'];
                            $column_name_format=array('Load Reference No',
                                    'Battery Type','Battery Number','Manufacturing Date');

                            $out= $this->validate_excel_format($value[0],$column_name_format);
                            $head = $out['header'];
                             if($out['column_name_err'] == 0)
                            {
                                foreach($value as $in => $row)
                                {
                                    if(count($value) > 1)
                                    {
                                        if($in > 0 && $in < count($value) && ($row[$head['Load Reference No']] == $unique_load_ref_no))
                                        {
                                            $manfact_date = CustomHelpers::excelDate($value[$in][$head['Manufacturing Date']]);
                                            $todate  = date("Y-m-d");
                                            $month = CustomHelpers::getMonth($manfact_date, $todate);

                                            $battery_type = trim($value[$in][$head['Battery Type']]);
                                            $battery_num = trim($value[$in][$head['Battery Number']]);


                                            if ($month > 3) {
                                                $status = "expired";
                                            } else { 
                                                $status = "ok";
                                            }

                                            $get = ProductModel::where('model_name','battery')
                                                    ->where('model_variant',$battery_type)
                                                    ->where('type','battery')
                                                    ->where('part_type',$battery_type)
                                                    ->first();
                                            $frame = ProductDetails::where('frame',$battery_num)
                                                    ->first();
                                            if(!$frame)
                                            {
                                                if(isset($get->id))
                                                {
                                                    $get = $get->toArray();
                                                    $battery = ProductDetails::insertGetId(
                                                        [
                                                            'unload_id' => $unload_id,
                                                            'product_id'    =>  $get['id'],
                                                            'frame' =>  $battery_num,
                                                            'engine_number' =>  'nothing',
                                                            'store_id'  =>  $store_id,
                                                            'manufacture_date'  =>  $manfact_date,
                                                            'status'  => $status

                                                        ]
                                                    );
                                                    $count_batteryImport++;
                                                    $getStock = Stock::where('product_id',$get['id'])
                                                                        ->where('store_id',$store_id)->first();
                                                    if($getStock)
                                                    {
                                                        Stock::where('id',$getStock->id)
                                                            ->increment('quantity',1);
                                                    }
                                                    else
                                                    {
                                                        Stock::insertGetId(
                                                                [
                                                                    'product_id'    =>  $get['id'],
                                                                    'quantity'  =>  1,
                                                                    'store_id'  =>  $store_id
                                                                ]
                                                                );
                                                    }
                                                
                                                }
                                                else
                                                {
                                                    $battery = ProductModel::insertGetId(
                                                        [
                                                            'model_category'=>'',
                                                            'model_name'=>'battery',
                                                            'model_variant'=>$battery_type,
                                                            'color_code'=>'',
                                                            'type'=>'battery',
                                                            'part_type'=> $battery_type,
                                                        ]
                                                    );
                                                    ProductDetails::insertGetId(
                                                        [
                                                            'unload_id' => $unload_id,
                                                            'product_id'    =>  $battery,
                                                            'frame' =>  $battery_num,
                                                            'engine_number' =>  'nothing',
                                                            'store_id'  =>  $store_id,
                                                            'manufacture_date'  => $manfact_date,
                                                            'status'  => $status

                                                        ]
                                                    );
                                                    $count_batteryImport = $count_batteryImport+1;
                                                    
                                                    Stock::insertGetId([
                                                                'product_id'    =>  $battery,
                                                                'quantity'  =>  1,
                                                                'store_id'  =>  $store_id
                                                    ]);
                                                }
                                            }
                                            else
                                            {
                                                DB::rollback();
                                                $x = $in+1;
                                                $y = $this->getNameFromNumber($head['Battery Number']);
                                                $error = $error." Part Number Already Exits. At :- row : ".$x.", col : ".$y.". ";
                                            }
                                                
                                        }
                                    }
                                    else{
                                        $error = $error."In your sheet have no data";
                                    }
                                }
                            }
                             else{ 
                                $error = $error.$out['error'];  
                            }
                            if($out['columnCount']==1){ 
                                $error = $error.$out['error'];
                            }
                        }
                         else
                        {
                            //die("@2");
                            $error = $error."Check Referance Number should be match to Unload data";
                        }
                        if(!empty($error))
                        {
                            DB::rollBack();
                            return back()->with('error',"Error, ".$error."");
                        }
                    }
                }
                else{
                    return back()->with('error','Error, Check your file should not be empty!!');
                }
            }
            else{
                return back()->with('error', 'some error occurred Please Try again!!');
            }
            if($count_batteryImport != $no_of_battery){
                DB::rollback();
                return back()->with('error','Number of Battey Product Should be equal to '.$no_of_battery.' ')->withInput();
            }   
        } 
        catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return redirect('/admin/battery/create')->with('error','some error occurred'.$ex->getMessage())->withInput();
        }
        DB::commit();
        return back()->with('success', 'Excel Data Imported successfully.');
    }

    public function product_list() {
        $data = array(
            'layout'=>'layouts.main'
        );
        return view('admin.product_list',$data);
    }

    public function product_list_api(Request $request) {
         $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
        
      $api_data=  ProductModel::where('type','product')
            ->where('isforsale',1)
            ->select(
                DB::raw('product.id as id'),
                DB::raw('product.model_category as model_cat'),
                DB::raw('product.model_name as model_name'),
                DB::raw('product.model_variant as model_var'),
                DB::raw('product.color_code as color'),
                DB::raw('product.basic_price as basic_price'),
                DB::raw('product.st_warranty_duration as st_warranty_duration'),
                // DB::raw('product.chasis_status as chasis'),
                DB::raw('product.created_at as created_at')
            );
            if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('product.model_category','like',"%".$serach_value."%")
                    ->orwhere('product.model_name','like',"%".$serach_value."%")
                    ->orwhere('product.model_variant','like',"%".$serach_value."%")
                    ->orwhere('product.color_code','like',"%".$serach_value."%")
                    ->orwhere('product.basic_price','like',"%".$serach_value."%")
                    ->orwhere('product.st_warranty_duration','like',"%".$serach_value."%")
                    // ->orwhere('product.chasis_status','like',"%".$serach_value."%")
                    ->orwhere('product.created_at','like',"%".$serach_value."%")
                    
                    ;
                });
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                'product.id',
                'product.model_category',
                'product.model_name',
                'product.model_variant',
                'product.color_code',
                'product.basic_price',
                'product.st_warranty_duration',
                // 'product.chasis_status',
                'product.created_at'
                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('product.id','desc');      
        
        $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

     public function frame_list(){

        $store = Store::whereIn('id',CustomHelpers::user_store())
                    ->select('id',DB::raw('concat(store.name,"-",store.store_type) as name'))->get();

        $data=array('store'=>$store,'layout'=>'layouts.main');
        return view('admin.frame_list', $data); 
    }

    public function frame_list_api(Request $request) { 

        $search = $request->input('search');
        $serach_value = $search['value'];
        $store_name = $request->input('store_name');
        $store_count = $request->input('store_count');
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;

        $store_id = explode(',',Auth::user()->store_id);
        

            $api_data= ProductDetails::leftJoin('store','product_details.store_id','store.id')
                ->leftJoin('unload','product_details.unload_id','unload.id')
                ->leftJoin('product','product_details.product_id','product.id')
                ->whereIn('store.id',$store_id)
                ->where('product.type', '!=' , 'battery')
            ->select(
                DB::raw('product_details.id'),
                DB::raw('product_details.frame'),
                DB::raw('unload.load_referenace_number'),
                DB::raw('product.model_name'),
                DB::raw('product.model_variant'),
                DB::raw('product.color_code'),
                DB::raw('concat(store.name,"-",store.store_type) as store'),
                DB::raw('product_details.engine_number'),
                DB::raw('product_details.manufacture_date'),
                DB::raw('product_details.htr_code'),
                DB::raw('product_details.status')
                );

            if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('product_details.id','like',"%".$serach_value."%")
                    ->orwhere('product_details.frame','like',"%".$serach_value."%")
                    ->orwhere('unload.load_referenace_number','like',"%".$serach_value."%")
                    ->orwhere('store.name','like',"%".$serach_value."%")
                    ->orwhere('product_details.engine_number','like',"%".$serach_value."%")
                    ->orwhere('product_details.manufacture_date','like',"%".$serach_value."%")
                    ->orwhere('product_details.htr_code','like',"%".$serach_value."%")
                    ->orwhere('product_details.status','like',"%".$serach_value."%")
                    ->orwhere('product.model_name','like',"%".$serach_value."%")
                    ->orwhere('product.model_variant','like',"%".$serach_value."%")
                    ->orwhere('product.color_code','like',"%".$serach_value."%");
                });
            }

            if(!empty($store_name))
            {             
                if($store_count > 1){

                 $api_data->where(function($query) use ($store_name){
                        $query->whereIn('store.id',$store_name);
                    });
                }else{

                     $api_data->where(function($query) use ($store_name){
                        $query->where('store.id',$store_name);
                    });
                }
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                    'unload.load_referenace_number',
                    'store.name',
                    'product_details.frame',
                    'product_details.engine_number',
                    'product.model_name',
                    'product.model_variant',
                    'product.color_code',
                    'product_details.status',
                    'product_details.manufacture_date',
                    'product_details.htr_code'
                   
                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('id','desc');      

        $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);  
    }

    public function battery_audit(Request $request) {
        try {
            $this->validate($request,[
                'status'=>'required',
            ],[
                'status.required'=> 'This is required.' 
            ]);

            $prod_audit_id = ProductAudit::insertGetId(
                [ 
                    'product_id'=>$request->input('product_id'),
                    'comments'=>$request->input('comments'),
                    'status'=>$request->input('status'),
                    'audit_by'=>Auth::Id() 
                ]
            );

            if($prod_audit_id==NULL) {
                DB::rollback();
                return redirect('/admin/battery/list')->with('error','Some Unexpected Error occurred.');
            }
            else{
                /* Add action Log */
                CustomHelpers::userActionLog($action='Create Battery Audit',$prod_audit_id);
                return redirect('/admin/battery/list')->with('success','Successfully Created Battery Audit.'); 
             }    
        
        }  catch(\Illuminate\Database\QueryException $ex) {
            return redirect('/admin/battery/list')->with('error','some error occurred'.$ex->getMessage())->withInput();
        }
    }

    public function battery_update($id) {

        $load=Unload::all();
        $store = Store::whereIn('id',CustomHelpers::user_store())
                    ->select('id','store_type',DB::raw('concat(store.name,"-",store.store_type) as name'))
                    ->orderBy('store_type','ASC')
                    ->get();
        $productdata= ProductModel::leftJoin('product_details','product.id','product_details.product_id')
                ->leftJoin('unload','product_details.unload_id','unload.id')
                ->leftJoin('product_audit','product_audit.product_id','product_details.id')
                ->select("product.*",'product_details.*','unload.*','product_audit.status as audit_status')
                ->where('product_details.id',$id)->get()->first();
        if($productdata){
            if($productdata['status'] == 'sale' || !empty($productdata['audit_status'])){
                return back()->with('error','Product is not Editable.');
            }
            $data = array(
                'id'=>$id,
                'load'=>$load,
                'store'=>$store,
                'productdata'=>$productdata,
                'layout'=>'layouts.main'
            );
            return view('admin.battery.battery_update',$data);
       }
       else{
          
        return redirect('/admin/battery/list')->with('error','Id not Exits');
      }        

    }

    public function battery_update_db(Request $request,$id){
        try {
            $this->validate($request,[
                // 'loadRefNum'=>'required',
                'store'=>'required',
                'part' =>  'required',
                'part_type'=>'required',
                'mfh_d'=>'required'
            ],[
                // 'loadRefNum.required'=> 'This is required.',
                'store.required'=> 'This is required.',
                'part_type.required'=> 'This is required.',
                'mfh_d.required'=> 'This is required.',
                'part.required'=> 'This is required.'
            ]);
            
             $mfh=Carbon::createFromFormat('m/d/Y', $request->input('mfh_d'))->format('Y-m-d');
             $now = date('Y-m-d');
             $to = Carbon::createFromFormat('Y-m-d',$mfh);
             $from =Carbon::createFromFormat('Y-m-d',$now);
             $months = $to->diffInMonths($from);

            if ($months > 3) {
                $status = "expired";
            } else { 
                $status = "ok";
            }

            $get = ProductModel::where('type','battery')
                    ->where('model_name','battery')
                    ->where('model_variant',$request->input('part_type'))
                    ->where('part_type',$request->input('part_type'))
                    ->first();
            $frame = ProductDetails::where('id',$id)
                    ->first();        
            DB::beginTransaction();
            
            if (isset($frame->id)) 
            {
                if(isset($get->id))   // when battery type change
                {
                    $get = $get->toArray();

                    $new_prodId = $get['id'];
                    $old_prodId = $frame->product_id;
                    $old_storeId = $frame->store_id;
                    $new_storeId = $request->input('store');
                    $arr = [];
                    if($request->input('part') != $frame->frame ){
                        $arr['frame']   = $request->input('part');
                    }
                    if($frame->product_id != $get['id'])
                    {
                        $arr['product_id']    =   $get['id'];
                    }
                    if($request->input('store') != $frame->store_id)
                    {
                        $arr['store_id']    =   $request->input('store');
                    }
                    $battery = ProductDetails::where('id',$id)->update(
                        array_merge($arr,[
                            'manufacture_date'  => date("Y-m-d", strtotime($request->input('mfh_d'))) ,
                            'status'  => $status
                        ])
                    );
                    // echo $battery;die;
                    if(isset($arr['store_id']) || isset($arr['product_id']))
                    {
                        // decrement
                        $dec = Stock::where('product_id',$old_prodId)
                            ->where('store_id',$old_storeId)
                            ->decrement('quantity',1);
                        // increment

                        $getStock = Stock::where('product_id',$new_prodId)
                                            ->where('store_id',$new_storeId)->first();
                        if(isset($getStock->id))
                        {
                            $inc = Stock::where('id',$getStock->id)
                                ->increment('quantity',1);
                        }
                        else
                        {
                            $inc = Stock::insertGetId(
                                    [
                                        'product_id'    =>  $new_prodId,
                                        'quantity'  =>  1,
                                        'store_id'  =>  $new_storeId
                                    ]);
                        }
                            
                    }
                }
                else
                {
                        $new_product_id = ProductModel::insertGetId(
                            [
                                'model_name'=>'battery',
                                'model_variant'=>$request->input('part_type'),
                                'color_code'=>'',
                                'type'=>'battery',
                                'part_type'=>$request->input('part_type'),
                            ]
                        );
                        $arr = [];
                        if($request->input('part') != $frame->frame ){
                            $arr['frame']   = $request->input('part');
                        }
                        if($request->input('store') != $frame->store_id)
                        {
                            $arr['store_id']    =   $request->input('store');
                        }
                        $battery = ProductDetails::where('id',$frame->id)->update(
                            array_merge($arr,[
                                'manufacture_date'  => date("Y-m-d", strtotime($request->input('mfh_d'))) ,
                                'status'  => $status,
                                'product_id'    =>  $new_product_id
                            ])
                        );
                    
                        // decrement
                        $dec = Stock::where('product_id',$frame->product_id)
                            ->where('store_id',$frame->store_id)
                            ->decrement('quantity',1);
                        // increment
                        $inc = Stock::insertGetId(
                            [
                                'product_id'    =>  $new_product_id,
                                'quantity'  =>  1,
                                'store_id'  =>  $request->input('store')
                            ]);  
                }
            }
            else
            {
                DB::rollback();
                return redirect('/admin/battery/list')->with('error','Part Number Already Exits.')->withInput();
            }
            if($battery==NULL) 
            {
                DB::rollback();
                return redirect('/admin/battery/list')->with('error','Some Unexpected Error occurred.')->withInput();
            }
            else{
                /* Add action Log */
                CustomHelpers::userActionLog($action='Battery Update',$frame->id);
                DB::commit();
                return redirect('/admin/battery/list')->with('success','Successfully Updated Battery.'); 
            }    
             
        }  catch(\Illuminate\Database\QueryException $ex) {
            DB::rollBack();
            return redirect('/admin/battery/list')->with('error','some error occurred'.$ex->getMessage())->withInput();
        }
    }

    public function unloadAddon_list(){
            
        return view('admin.unload_addon_list', ['layout'=>'layouts.main']);
    }

    public function unloadAddon_listapi(Request $request) {
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;

        $api_data=UnloadAddon::leftJoin('unload','unload_addon.unload_id','unload.id')->
                               leftjoin('store','unload.store','store.id')->
                            select(
                                'unload_addon.id',
                                'unload.load_referenace_number',
                                DB::raw("UPPER(REPLACE(unload_addon.addon_name,'_',' ')) as addon_name"),
                                'unload_addon.model',
                                'unload_addon.qty',
                                 DB::raw('concat(store.name,"-",store.store_type) as store_name')
                            );

            if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('unload.load_referenace_number','like',"%".$serach_value."%")
                        ->orwhere(DB::raw("REPLACE(unload_addon.addon_name,'_',' ')"),'like',"%".$serach_value."%")
                     ->orwhere('unload_addon.model','like',"%".$serach_value."%")
                     ->orwhere('unload_addon.qty','like',"%".$serach_value."%")
                     ->orwhere('store.name','like',"%".$serach_value."%")
                    ;
                });
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                    'unload.load_referenace_number',
                    'addon_name',
                    'unload_addon.model',
                    'unload_addon.qty',
                    'store_name'
                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('unload_addon.id','asc');      
        
        $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();

        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }
}


?>

