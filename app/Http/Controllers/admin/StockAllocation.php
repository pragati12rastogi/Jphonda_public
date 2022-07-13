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
use \App\Model\StockMovementDetails;
use \App\Custom\CustomHelpers;
use App\Model\DamageDetail;
use \App\Model\Stock;
use \App\Model\VehicleAddon;
use \App\Model\VehicleAddonStock;
use \App\Model\StockMoveProcess;
use App\Model\Users;
use \App\Model\FuelModel;
use \App\Model\FuelStockModel;
use \App\Model\StockAllocationView;
use \App\Model\SmPending;
use \App\Model\StockMoveAddon;
use Validator;
use DB;
use Auth;
use Hash;
use \Carbon\Carbon;

class StockAllocation extends Controller
{
    public function stockAllocation()
    {
        DB::enableQueryLog();

        $store = Store::select(['id as store_id',
                                'store_type',
                                // 'name as realname',
                                DB::raw('concat(name,"_",store_type) as showname'),
                                DB::raw('concat(REPLACE(name," ",""),"_",store_type) as name')])
                    ->orderBy('store_type','ASC')
                    ->whereIn('store.id',CustomHelpers::user_store())->get()->toArray();
        $strarr_store_id = [];
        $strarr_avail_qty = [];
        $strarr_avail_damage_qty = [];
        $strarr_diff_qty = [];
        $strarr_pending_qty = [];
        $strarr_pending_damage_qty = [];

        foreach($store as $key => $val)
        {
            $strarr_store_id[] = $val['store_id']." as '".$val['name']."'";
            $strarr_avail_qty[] = "IFNULL((select stock.quantity from stock where stock.product_id = ps.prod_id and stock.store_id = ".$val['store_id']."),0) as '".$val['name']."-avail'"; 
            $strarr_avail_damage_qty[] = "IFNULL((select stock.damage_quantity from stock where stock.product_id = ps.prod_id and stock.store_id = ".$val['store_id']."),0) as '".$val['name']."-avail_damage'"; 
            $strarr_diff_qty[] = "IFNULL((select stock.min_qty from stock where stock.product_id = ps.prod_id and stock.store_id = ".$val['store_id']."),0) as '".$val['name']."-diff'"; 
            $strarr_pending_qty[] = "IFNULL((SELECT IF(stock_movement_details.quantity = 1,COUNT(stock_movement_details.quantity),0) from stock_movement left Join stock_movement_details on stock_movement.id = stock_movement_details.stock_movement_id where stock_movement_details.product_id = ps.prod_id and (stock_movement_details.status = 'pending' or stock_movement_details.status = 'process' ) and stock_movement.to_store = ".$val['store_id']." limit 1 ),0) as '".$val['name']."-pending'"; 
            $strarr_pending_damage_qty[] = "IFNULL((SELECT IF(stock_movement_details.quantity = 2,COUNT(stock_movement_details.quantity),0) from stock_movement left Join stock_movement_details on stock_movement.id = stock_movement_details.stock_movement_id where stock_movement_details.product_id = ps.prod_id and (stock_movement_details.status = 'pending' or stock_movement_details.status = 'process' ) and stock_movement.to_store = ".$val['store_id']." limit 1 ),0) as '".$val['name']."-pending_damage'"; 
            // $strarr_pending_damage_qty[] = "IFNULL((select stock.quantity from stock where stock.product_id = ps.prod_id and stock.store_id = ".$val['store_id']."),0) as '".$val['name']."-pending_damage'"; 
        }
        $str = implode(',',$strarr_store_id).','.implode(',',$strarr_avail_qty).','.implode(',',$strarr_avail_damage_qty).','.implode(',',$strarr_diff_qty).','.implode(',',$strarr_pending_qty).','.implode(',',$strarr_pending_damage_qty);
        $st = "select ps.*,".$str."
        from
        (
            SELECT
            product.id AS prod_id,
            product.model_name,
            product.model_variant,
            product.color_code,
            IFNULL(
                SUM(
                    stock.quantity + stock.damage_quantity
                ),
                0
            ) AS freeStock,
            IFNULL(SUM(stock.quantity),
            0) AS ndq,
            IFNULL(SUM(stock.damage_quantity),
            0) AS dq
            
            
        FROM
            product
        LEFT JOIN stock ON product.id = stock.product_id
        WHERE
            product.type = 'product'  and isforsale = 1
        GROUP BY
            product.id
        ORDER BY
            product.model_name,
            product.model_variant
        ) ps
        ";

        $prod_data = DB::select($st);
        // print_r(DB::getQueryLog());die;
        // print_r($q);die;
        $productList = [];
        for($i = 0 ; $i < count($prod_data) ; $i++)
        {
            $productList[$i]['prodId'] = $prod_data[$i]->prod_id;
            $productList[$i]['model_name'] = $prod_data[$i]->model_name;
            $productList[$i]['model_variant'] = $prod_data[$i]->model_variant;
            $productList[$i]['color_code'] = $prod_data[$i]->color_code;
            $productList[$i]['freeStock'] = $prod_data[$i]->freeStock;
            $productList[$i]['ndq'] = $prod_data[$i]->ndq;
            $productList[$i]['dq'] = $prod_data[$i]->dq;
            for($j = 0 ; $j < count($store) ; $j++)
            {
                $store_name = $store[$j]['name'];
                $store_id = $prod_data[$i]->$store_name;
                //-------------- for difference-------------
                $name = $store[$j]['name'].'-diff';
                $productList[$i]['diff']['qty'][$store[$j]['name']] = $prod_data[$i]->$name;
                $productList[$i]['diff']['id'][$store[$j]['name']] = $store_id;

                //----------------- for available qty----------------
                $name = $store[$j]['name'].'-avail';
                $productList[$i]['available']['qty'][$store[$j]['name']] =  $prod_data[$i]->$name;
                $name = $store[$j]['name'].'-avail_damage';
                $productList[$i]['available']['damageQty'][$store[$j]['name'].'-damage'] =  $prod_data[$i]->$name;
                $productList[$i]['available']['id'][$store[$j]['name']] = $store_id;

                //--------------- for pending qty which qty is stock movement process------------
                $name = $store[$j]['name'].'-pending';
                $productList[$i]['allocate']['pending']['qty'][$store[$j]['name']] = $prod_data[$i]->$name;
                $name = $store[$j]['name'].'-pending_damage';
                $productList[$i]['allocate']['pending']['damageQty'][$store[$j]['name'].'-damage'] =  $prod_data[$i]->$name;
                $productList[$i]['allocate']['pending']['id'][$store[$j]['name']] = $store_id;
            }
        }

            $data = array(
                'layout'=>'layouts.main',
                'data' => $productList,
                'store' =>  $store
            );
            return view('admin.stockAllocation.StockMovement',$data);
    }
    public function stockAllocation_old()
    {
        echo 'pre used';die;
        $productList = ProductModel::leftJoin('stock','product.id','stock.product_id')
                        ->where('product.type','product')
                        // ->where('product.part_type','product')
                        ->select([
                        'model_name',
                        'model_variant',
                        'color_code',
                        'product.id as prodId',
                        DB::raw("sum(stock.quantity + stock.damage_quantity) as freeStock"),
                        DB::raw("sum(stock.quantity) as ndq"),
                        DB::raw("sum(stock.damage_quantity) as dq")
                        ])
                        //->where('product_details.status','ok')
                        ->orderBy('model_name','ASC')
                        ->groupBy('model_name')
                        ->groupBy('model_variant')
                        ->groupBy('product.id')->get()->toArray();
        // print_r(DB::getQueryLog());die;
        $store = Store::select(['id as store_id','store_type','name as realname',DB::raw('concat(name,"_",store_type) as name')])
                            ->orderBy('store_type','ASC')->get()->toArray();

       
        for($i = 0 ; $i < count($productList) ; $i++)
        {
            for($j = 0 ; $j < count($store) ; $j++)
            {
                $prodStoreQty = Stock::
                    where('stock.product_id',$productList[$i]['prodId'])
                    ->where('stock.store_id',$store[$j]['store_id'])
                    ->select('stock.quantity','stock.min_qty','stock.damage_quantity')
                    ->first();
                
                $productList[$i]['diff']['qty'][$store[$j]['name']] = ($prodStoreQty['min_qty'] != 0) ? $prodStoreQty['min_qty'] : 0 ;
                $productList[$i]['diff']['id'][$store[$j]['name']] = $store[$j]['store_id'];
                // echo $productList[$i]['diff']['qty'][$store[$j]['name']];
                // echo "<br>";
                // echo 'storeName-'.$store[$j]['name'].'<br>';
                // echo $productList[$i]['diff']['id'][$store[$j]['name']];
                // echo "<br>";


                $productList[$i]['available']['qty'][$store[$j]['name']] = ($prodStoreQty['quantity'] != 0) ? $prodStoreQty['quantity'] : 0 ;
                $productList[$i]['available']['damageQty'][$store[$j]['name'].'-damage'] = ($prodStoreQty['damage_quantity'] != 0) ? $prodStoreQty['damage_quantity'] : 0 ;
                $productList[$i]['available']['id'][$store[$j]['name']] = $store[$j]['store_id'];
                
                $prodMoveQty = StockMovement::leftJoin('stock_movement_details','stock_movement.id','stock_movement_details.stock_movement_id')
                                        ->where('stock_movement_details.product_id',$productList[$i]['prodId'])
                                        ->where('stock_movement.to_store',$store[$j]['store_id'])
                                        ->where('stock_movement_details.status','<>',"done")
                                        //->where('stock_movement_details.damage_qty',0)
                                        // ->select(DB::raw('sum(stock_movement_details.quantity-stock_movement_details.damage_qty) as qty'),
                                        //     DB::raw('sum(stock_movement_details.damage_qty) as damageQty'))->first();
                                        ->select(DB::raw('CASE WHEN stock_movement_details.quantity = 1 THEN count(stock_movement_details.quantity) ELSE 0 END AS qty'),
                                            DB::raw('CASE WHEN stock_movement_details.quantity = 2 THEN count(stock_movement_details.quantity) ELSE 0 END AS damageQty'))->first();
                                        

                                        
                //print_r($prodMoveQty);echo "<br>";//die;
                $productList[$i]['allocate']['pending']['qty'][$store[$j]['name']] = ($prodMoveQty['qty'] == null) ? 0 : $prodMoveQty['qty'] ;
                $productList[$i]['allocate']['pending']['damageQty'][$store[$j]['name'].'-damage'] = ($prodMoveQty['damageQty'] == 0) ? 0 : $prodMoveQty['damageQty'] ;
                $productList[$i]['allocate']['pending']['id'][$store[$j]['name']] = $store[$j]['store_id'];

            }
        }
        //
        
            $data = array(
                'layout'=>'layouts.main',
                'data' => $productList,
                'store' =>  $store
            );
            return view('admin.stockAllocation.StockMovement',$data);
    }

    public function stockAllocBook(Request $request)
    {
        $fromStoreId =  $request->input('fromStoreId');
        $fromStoreName =  $request->input('fromStoreName');
        $toStoreId =  $request->input('toStoreId');
        // $toStoreName =  $request->input('toStoreName');
        $newInput =  $request->input('newInput');
        $prodId =  $request->input('prodId');
        $qtyType =  $request->input('qty');

        $error = "";
        $reset_table = ['stock_movement','stock_movement_details'];
        CustomHelpers::resetIncrement($reset_table);
        DB::beginTransaction();

        // $fromStoreName = str_replace(['_'],' ',$fromStoreName);//

        $incrDec = $this->incrDecr($qtyType,$fromStoreId,$fromStoreName,$toStoreId,$newInput,$prodId);
        $incrDecr = $incrDec;
        // print_r($incrDec);die;
        // $newInput = intval($incrDecr[1]);
        if($incrDecr[0] == 'dec'){
            $stockMoveQty = $incrDecr[3];
        }
        elseif($incrDecr[0] == 'inc')
        {
            $stockMoveQty = $incrDecr[2];
        }
        if($incrDecr[0] != 'error')
        {
            $check = $this->validateData($qtyType,$stockMoveQty,$fromStoreId,$fromStoreName,$toStoreId,$newInput,$prodId,$incrDecr);
            // print_r($incrDecr);die;
            if($check == 'success')
            {  
                if($incrDecr[0] == 'dec')
                {
                    $newInput = intval($incrDecr[1]);
                    $noOfDel = intval($incrDecr[1]);
                    $stockMoveId = intval($incrDecr[2]);
                    // print_r($incrDecr);die;
                    // DB::enableQueryLog();
                    $del = StockMovementDetails::where('stock_movement_id',$stockMoveId)
                        ->where('stock_movement_details.product_id',$prodId)
                        ->where('stock_movement_details.status','pending')
                        ->orderBy('stock_movement_details.id');
                        if($qtyType == 'ndq'){ $del = $del->where('stock_movement_details.quantity',1); }
                        elseif($qtyType == 'dq'){ $del = $del->where('stock_movement_details.quantity',2); }
                        $del = $del->limit($noOfDel)
                        ->delete();

                    // $find = StockMovement::leftJoin('stock_movement_details','stock_movement_details.stock_movement_id','stock_movement.id')
                    //     ->where('stock_movement.from_store',$fromStoreId)
                    //     ->where('stock_movement.to_store',$toStoreId)
                    //     ->where('stock_movement.id',$stockMoveId);
                    //     // ->where('stock_movement_details.product_id',$prodId)
                    //     // ->where('stock_movement_details.status','pending');
                    //     if($qtyType == 'ndq'){ $find = $find->where('stock_movement_details.quantity',1)->first('stock_movement.quantity'); }
                    //     elseif($qtyType == 'dq'){ $find = $find->where('stock_movement_details.quantity',2)->first('stock_movement.quantity'); }
                        
                    // if(empty($find))
                    // {
                    //     $del = StockMovement::where('stock_movement.from_store',$fromStoreId)
                    //             ->where('stock_movement.to_store',$toStoreId)
                    //             ->where('stock_movement.status','pending')
                    //             ->update(['quantity'    =>  0]);
                    // }
                    // else{
                        $updat = StockMovement::where('id',$stockMoveId)
                                                ->decrement('quantity',$newInput);
                                    // where('stock_movement.from_store',$fromStoreId)
                                // ->where('stock_movement.to_store',$toStoreId)
                                // ->where('stock_movement.status','pending')
                                // ->update(['quantity' => intval($find->toArray()['quantity'])-$newInput]);
                    // }
                    //print_r(DB::getQueryLog());die;
                    $updStock = $this->updateStock($qtyType,$toStoreId,$fromStoreId,$noOfDel,$prodId);
                    // echo $updStock;die;

                    if($updStock == 'success')
                    {
                        /* Add action Log */
                        CustomHelpers::userActionLog($action='Stock De-Allocation',$stockMoveId,0,'Stock Movement');
                        $error = 'success';
                    }
                    else
                    {
                        $error = $updStock;
                    }
                }
                elseif($incrDecr[0] == 'inc')
                {
                    $newInput = intval($incrDecr[1]);
                    //print_r($newInput);die;
                    $error = $check;
                    $insertStockMovement = $this->insertStockMovement($fromStoreId,$fromStoreName,$toStoreId,$newInput,$prodId); 
                    //print_r($insertStockMovement);
                    $insertStockMovement = explode('-',$insertStockMovement);
                    // print_r($insertStockMovement);die;

                    if($insertStockMovement[0] == 'success')
                    {
                        $error = $insertStockMovement[0];
                        $stockMoveId = $insertStockMovement[1];
                        $newOrOld = $insertStockMovement[2];
                        
                        $insertStockMovementDet = $this->insertStockMovementDet($qtyType,$newOrOld,$stockMoveId,$fromStoreId,$fromStoreName,$toStoreId,$newInput,$prodId); 
                        // echo $insertStockMovementDet;die;
                        
                        if($insertStockMovementDet == 'success')
                        {
                            $error = $insertStockMovementDet;
                            //DB::enableQueryLog();

                            $updateStock = $this->updateStock($qtyType,$fromStoreId,$toStoreId,$newInput,$prodId);
                            // print_r(DB::getQueryLog());
                            // die;
                            if($updateStock == 'success')
                            {
                                /* Add action Log */
                                CustomHelpers::userActionLog($action='Stock Allocation',$stockMoveId,0,'Stock Movement');
                                $error = $updateStock;
                            }
                            else
                            {
                                $error = $updateStock;
                            }
                        }
                        else{
                            $error =  $insertStockMovementDet;
                        }
                        
                    }
                    else{
                        $error = $insertStockMovement[0];
                    }
                }
                else
                {
                    return $error = $incrDecr[0];
                }
            }
            else
            {
                $error = $check;
            }
        }
        else
        {
            $error = $incrDecr[1];
        }
        
        if($error == 'success')
        {
            DB::commit();
            return $error;
        }
        
        DB::rollback();
        return $error;

    }
    public function incrDecr($qtyType,$fromStoreId,$fromStoreName,$toStoreId,$newInput,$prodId)
    {
        $incdec = StockMovement::leftJoin('stock_movement_details','stock_movement_details.stock_movement_id','stock_movement.id')
                    ->where('stock_movement.from_store',$fromStoreId)
                    ->where('stock_movement.to_store',$toStoreId)
                    ->where('stock_movement_details.product_id',$prodId)
                    ->where('stock_movement_details.status','pending');
                    if($qtyType == 'ndq'){ $incdec = $incdec->where('stock_movement_details.quantity',1); }
                    elseif($qtyType == 'dq'){ $incdec = $incdec->where('stock_movement_details.quantity',2); }
                    $incdec = $incdec->count('stock_movement_details.quantity');

        $getId = StockMovement::where('stock_movement.from_store',$fromStoreId)
                    ->where('stock_movement.to_store',$toStoreId)
                    ->where('stock_movement.status','pending')
                    ->first('id');
                    
        if($incdec > $newInput )
        {
            if(!isset($getId->id)){
                return ['error','Stock Movement Not Found.'];
            }
            //dec
            $val = 'dec-'.(intval($incdec)-intval($newInput)).'-'.$getId->toArray()['id'].'-'.intval($incdec);
            // return $val;
            return ['dec',intval($incdec)-intval($newInput),$getId->toArray()['id'],intval($incdec)];
        }
        elseif($incdec == $newInput)
        {
            return ['error','That Quantity are Same to the Available Quantity.'];
        }
        elseif($incdec < $newInput){
            //inc
            // return 'inc-'.(intval($newInput)-intval($incdec)).'-'.intval($incdec);
            return ['inc',intval($newInput)-intval($incdec),intval($incdec)];
        }
        else
        {
            return ['error','Something Went Wrong.'];
        }
        
    }
    public function validateData($qtyType,$stockMoveQty,$fromStoreId,$fromStoreName,$toStoreId,$newInput,$prodId,$incdec)
    {
        $error = 'Stock Not Found.';
        $checkAvailQty = Stock::where('stock.product_id',$prodId)
                            ->where('stock.store_id',$fromStoreId);

        if($qtyType == 'ndq'){ $checkAvailQty = $checkAvailQty->select('quantity')->first(); }

        elseif($qtyType == 'dq'){ $checkAvailQty = $checkAvailQty->select('damage_quantity as quantity')->first(); }
                            
        if(isset($checkAvailQty->quantity))
        {
            $checkAvailQty = $checkAvailQty->toArray();
            $qty = $checkAvailQty['quantity'];
            
            // check actual quantity in from store
            //get qty received 'pending' or 'process' of from_store as a to_store 
            $get_from_pending_qty = SmPending::where('to_store',$fromStoreId)
                                        ->where('product_id',$prodId);
            if($qtyType == 'ndq'){ $get_from_pending_qty = $get_from_pending_qty->sum('smdQty'); }
            elseif($qtyType == 'dq'){ $get_from_pending_qty = $get_from_pending_qty->sum('smddQty'); }
            
            $qty = $qty-$get_from_pending_qty;
            
            if($incdec[0] == 'dec'){
                $howmuch_want_to_dec = $incdec[1];
                // validate it, when we decrease the qty to to_store then we are check to_store have qty or it's 
                                // used in sale_qty or not, means calculate to get to_store actual qty and check 
                                // able to decrement action perform or not 
                // check actual quantity in to store
                //get stock qty
                $get_to_store = Stock::where('stock.product_id',$prodId)
                                ->where('stock.store_id',$toStoreId);
                if($qtyType == 'ndq'){ $get_to_store = $get_to_store->select('quantity')->first(); }
                elseif($qtyType == 'dq'){ $get_to_store = $get_to_store->select('damage_quantity as quantity')->first(); }
                
                if(!isset($get_to_store->quantity)){
                    $store_name = Store::where('id',$toStoreId)->select('name')->first();
                    return "Stock Not Found at '".$store_name->name."' Store ";
                }
                
                $to_store_qty = $get_to_store->quantity;
                if($to_store_qty <= 0){
                    return "This Quantity Already Used So, You are not able to Decrease Quantity.";
                }
                $last_pending_alloc_qty = $stockMoveQty;
                if($to_store_qty < $howmuch_want_to_dec){  // if actual qty is less to decrement qty that means some qty are already used. for ex :-  in sale_aty
                    $howmuch = $howmuch_want_to_dec-$to_store_qty;
                    $error = "You are able Decrease only '".$to_store_qty."' Quantity";
                    return $error;
                }
                
            }
           
            $qty = $qty+$stockMoveQty;

            if($qty >= $newInput)
            {
                $error = 'success';
            }
            else{
                $error = "Enter value should be less or equal to available Quantity on allocate from ".$fromStoreName." Store";
            }
        }
        else{
            $error = "Stock Not Available from ".$fromStoreName." Store.";
        }
        return $error;

    }
    public function insertStockMovement($fromStoreId,$fromStoreName,$toStoreId,$newInput,$prodId)
    {

        $PreStockMovement = StockMovement::where('stock_movement.from_store',$fromStoreId)
                                ->where('stock_movement.to_store',$toStoreId)
                                ->where('stock_movement.status','pending')
                                ->select('stock_movement.id as stockMoveId','stock_movement.quantity')->first();
        if($PreStockMovement)
        {
            $PreStockMovement = $PreStockMovement->toArray();
            $id = StockMovement::where('id',$PreStockMovement['stockMoveId'])
                    ->update(['quantity'=>$PreStockMovement['quantity']+$newInput,
                                'created_at'    =>  DB::raw('CURRENT_TIMESTAMP')]);
            return 'success-'.$PreStockMovement['stockMoveId'].'-old';
        }
        else{
            //$data = 
            $getId = DB::table('stock_movement')->InsertGetId([
                    'quantity' =>  $newInput,
                    'from_store'    =>  $fromStoreId,
                    'to_store' =>  $toStoreId,
                    'status' =>  'pending'
            ]);
            if($getId)
            {
                $val = 'success-'.$getId.'-new';
                return $val;
            }
            else{
                return 'Stock Movement Not Updated';
            }
        }
        return 'Stock Movement Not Updated';
    }
    public function updateStock($qtyType,$fromStoreId,$toStoreId,$newInput,$prodId)
    {
        // echo $newInput;die;
        //DB::enableQueryLog();
        $error = 0;
        $getQty = Stock::where('product_id',$prodId)
                    ->where('store_id',$fromStoreId);
        if($qtyType == 'ndq'){ $getQty = $getQty->select('id','quantity')->first(); }
        elseif($qtyType == 'dq'){ $getQty = $getQty->select('id','damage_quantity as quantity')->first(); }

        if(!isset($getQty->id)){
            $error = "From Stock Not Found.";
        }
        $fromStock = Stock::where('id',$getQty->id);

        if($qtyType == 'ndq'){ $fromStock = $fromStock->update(['quantity'=>$getQty['quantity']-$newInput]); }
        elseif($qtyType == 'dq'){$fromStock = $fromStock->update(['damage_quantity'=>$getQty['quantity']-$newInput]);}

        if($fromStock == 1)
        {
            $existToStore = Stock::where('product_id',$prodId)
                            ->where('store_id',$toStoreId);
            if($qtyType == 'ndq'){ $existToStore = $existToStore->select('id','quantity')->first(); }
            elseif($qtyType == 'dq'){ $existToStore = $existToStore->select('id','damage_quantity as quantity')->first(); }

            if(isset($existToStore->quantity))
            {
                $toStock = Stock::where('id',$existToStore->id);

                if($qtyType == 'ndq'){ $toStock = $toStock->update(['quantity'=>$existToStore->toArray()['quantity']+$newInput]); }
                elseif($qtyType == 'dq'){$toStock = $toStock->update(['damage_quantity'=>$existToStore->toArray()['quantity']+$newInput]);}
                        // ->update(['quantity'=>$existToStore->toArray()['quantity']+$newInput]);
                if(!$toStock){
                    $error = "To Store Not Update";
                }
            }
            else
            {
                $arr = [];
                if($qtyType == 'ndq'){ $arr = ['quantity'  =>  $newInput, 'damage_quantity'   =>  0]; }
                elseif($qtyType == 'dq'){$arr = ['quantity'  => 0 , 'damage_quantity'   => $newInput];}
                 
                $id = Stock::insertGetId(array_merge([
                    'product_id'    =>  $prodId,
                    'min_qty'   =>  0,
                    'store_id'  =>  $toStoreId
                ],$arr));
                if(!$id){
                    $error = "To Store Not Update";
                }
            }
        }
        else{
            $error == "From Stock Not Update.";
        }

        if($error == 0)
        {
            return 'success';
        }
        else{
            return $error;
        }
    }
    public function insertStockMovementDet($qtyType,$newOrOld,$stockMoveId,$fromStoreId,$fromStoreName,$toStoreId,$newInput,$prodId)
    {
        
        $data = array(
                'stock_movement_id' => $stockMoveId,
                'product_id'    =>  $prodId,
                'product_details_id'    =>  0,
                //'quantity'  =>  1,
                'status'    =>  'pending',  
                'created_by'    =>  Auth::id()    
        );
        $arr = [];
        if($qtyType == 'ndq'){ $arr = [ 'quantity'   =>  1]; }
        elseif($qtyType == 'dq'){$arr = ['quantity'   => 2];}
        // DB::enableQueryLog();
        try{
            $data = array_merge($data,$arr);
            // print_r($data);die;
            $error = 0;
            for($i = 0 ; $i < $newInput ; $i++)
            {
                $id = StockMovementDetails::insertGetId($data);
                if(!$id)
                {
                    $error++;
                }
                // echo $id;die;
            }
        }
        catch(\Illuminate\Database\QueryException $ex) {
                //print_r($ex->getMessage());die;
            return 'Something went wrong.'.$ex->getMessage();
        }
        // print_r(DB::getQueryLog());
        if($error === 0)
        {
            return 'success';
        }
        return 'Internal Error';
    }
    public function onchangeQty(Request $request)   
    {
       
        $storeId = $request->input('storeId');
        $qtyType = $request->input('qty');
        $presentPageProdId = $request->input('presentPageProdId');
        if(!empty($presentPageProdId)){
            $presentPageProdId = explode(',',$presentPageProdId);
        }else{
            $presentPageProdId = [];
        }
        $store = Store::select(['id as store_id','store_type',
                            // 'name as realname',
                            DB::raw('concat(name,"_",store_type) as showname'),
                            DB::raw('concat(REPLACE(name," ",""),"_",store_type) as name')])
                    ->orderBy('store_type','ASC')->get()->toArray();
        $strarr_store_id = [];
        $strarr_avail_qty = [];
        $strarr_avail_damage_qty = [];
        $strarr_only_avail = [];
        $strarr_prod_move_qty = [];

        foreach($store as $key => $val)
        {
            $strarr_store_id[] = $val['store_id']." as '".$val['name']."'";
            $strarr_avail_qty[] = "IFNULL((select stock.quantity from stock where stock.product_id = stock_allocation.prod_id and stock.store_id = ".$val['store_id']."),0) as '".$val['name']."-avail'"; 
            $strarr_avail_damage_qty[] = "IFNULL((select stock.damage_quantity from stock where stock.product_id = stock_allocation.prod_id and stock.store_id = ".$val['store_id']."),0) as '".$val['name']."-avail_damage'"; 
            // $strarr_diff_qty[] = "IFNULL((select stock.min_qty from stock where stock.product_id = stock_allocation.prod_id and stock.store_id = ".$val['store_id']."),0) as '".$val['name']."-diff'"; 
            $strarr_only_avail[] = "(SELECT COUNT(stock_movement_details.quantity) AS count FROM stock_movement
                            JOIN stock_movement_details ON stock_movement.id = stock_movement_details.stock_movement_id
                            WHERE stock_movement_details.product_id = stock_allocation.prod_id AND stock_movement.to_store = ".$val['store_id']."
                            AND stock_movement_details.quantity = ".(($qtyType == 'ndq')? 1 : 2)." AND (stock_movement_details.status = 'pending' or stock_movement_details.status = 'process'))
                            as '".$val['name']."-only_avail'"; 
            $strarr_prod_move_qty[] = "(SELECT COUNT(stock_movement_details.quantity) AS count FROM stock_movement
                        JOIN stock_movement_details ON stock_movement.id = stock_movement_details.stock_movement_id
                        WHERE stock_movement_details.product_id = stock_allocation.prod_id AND stock_movement.to_store = ".$val['store_id']."
                            AND stock_movement_details.quantity = ".(($qtyType == 'ndq')? 1 : 2)." AND (stock_movement_details.status = 'pending')
                            ".(($storeId != 0)? 'AND  stock_movement.from_store ='.$storeId : '' ).")
                        as '".$val['name']."-prod_move_qty'"; 
            
            // $strarr_pro_qty[] = "IFNULL((SELECT IF(stock_movement_details.quantity = 2,COUNT(stock_movement_details.quantity),0) from stock_movement left Join stock_movement_details on stock_movement.id = stock_movement_details.stock_movement_id where stock_movement_details.product_id = stock_allocation.prod_id and stock_movement_details.status <> 'done' and stock_movement.to_store = ".$val['store_id']." limit 1 ),0) as '".$val['name']."-pending_damage'"; 
            // $strarr_pending_damage_qty[] = "IFNULL((select stock.quantity from stock where stock.product_id = stock_allocation.prod_id and stock.store_id = ".$val['store_id']."),0) as '".$val['name']."-pending_damage'"; 
        }
        $str = implode(',',$strarr_store_id).','.implode(',',$strarr_avail_qty).','.implode(',',$strarr_avail_damage_qty).','.implode(',',$strarr_only_avail).','.implode(',',$strarr_prod_move_qty);
        

        $prod_data = StockAllocationView::whereIn('stock_allocation.prod_id',$presentPageProdId)->selectRaw("stock_allocation.*, ".$str."")->get();

        $productList = [];
        for($i = 0 ; $i < count($prod_data) ; $i++)
        {
            $productList[$i]['prodId'] = $prod_data[$i]->prod_id;
            for($j = 0 ; $j < count($store) ; $j++)
            {
                $store_name = $store[$j]['name'];
                $store_id = $prod_data[$i]->$store_name;
                
                //----------------- for available qty----------------
                $qty = $store[$j]['name'].'-avail';
                $damage_qty = $store[$j]['name'].'-avail_damage';
                $name = ($qtyType == 'ndq') ? $qty : $damage_qty ;
                // print_r($name);die;
                $productList[$i]['available']['qty'][$store[$j]['name']] =  $prod_data[$i]->$name;
                $productList[$i]['available']['id'][$store[$j]['name']] = $store_id;

                // ------------------
                
                $name = $store[$j]['name'].'-prod_move_qty';
                $productList[$i]['allocate']['qty'][$store[$j]['name']] = intval($prod_data[$i]->$name) ;
                $name = $store[$j]['name'].'-only_avail';
                $productList[$i]['forAvailAlloc']['qty'][$store[$j]['name']] = intval($prod_data[$i]->$name) ;
                $productList[$i]['allocate']['id'][$store[$j]['name']] = $store_id;
            }
        }
        
        // print_r($productList);

       
        // var_dump($productList); die;
         return response()->json($productList);

    }
    public function onchangeQty_old(Request $request)   // this function used when stock allocation method used many for loop's
    {
        echo "not used";
        // echo 'start:- '.date('Y-m-d H:i:s');
        $storeId = $request->input('storeId');
        $qtyType = $request->input('qty');
        //$qtyType = 'dq';
        $productList = ProductModel::leftJoin('stock','product.id','stock.product_id')
        ->where('product.type','product')
        ->select('product.id as prodId')
        ->orderBy('model_name','ASC')
        ->groupBy('model_name')
        ->groupBy('model_variant')
        ->groupBy('product.id')->get()->toArray();

        $store = Store::select(['id as store_id','store_type','name as realname',
                                DB::raw('concat(name,"_",store_type) as name')])
                    ->orderBy('store_type','ASC')->get()->toArray();
        DB::enableQueryLog();
        for($i = 0 ; $i < count($productList) ; $i++)
        {
            for($j =0 ; $j < count($store) ; $j++)
            {
                $prodStoreQty = Stock::
                    where('stock.product_id',$productList[$i]['prodId'])
                    ->where('stock.store_id',$store[$j]['store_id'])
                    ->select('stock.quantity','stock.min_qty','stock.damage_quantity')
                    ->first();
                if( $qtyType == 'ndq')
                {
                    $productList[$i]['available']['qty'][$store[$j]['name']] = ($prodStoreQty['quantity'] != 0) ? $prodStoreQty['quantity'] : 0 ;

                }
                elseif( $qtyType == 'dq')
                {
                    $productList[$i]['available']['qty'][$store[$j]['name']] = ($prodStoreQty['damage_quantity'] != 0) ? $prodStoreQty['damage_quantity'] : 0 ;
                }
               
                // $productList[$i]['available']['qty'][$store[$j]['name']] = ($prodStoreQty['quantity'] != 0) ? $prodStoreQty['quantity'] : 0 ;
                // $productList[$i]['available']['damageQty'][$store[$j]['name'].'-damage'] = ($prodStoreQty['damage_quantity'] != 0) ? $prodStoreQty['damage_quantity'] : 0 ;
                $productList[$i]['available']['id'][$store[$j]['name']] = $store[$j]['store_id'];
                // DB::enableQueryLog();
                $prodMoveQty = StockMovement::Join('stock_movement_details','stock_movement.id','stock_movement_details.stock_movement_id')
                ->where('stock_movement_details.product_id',$productList[$i]['prodId']);
                
                $prodMoveQty = $prodMoveQty->where('stock_movement.to_store',$store[$j]['store_id']);
                if( $qtyType == 'ndq')
                {
                    // echo "ndq<br>";
                    $prodMoveQty = $prodMoveQty->where('stock_movement_details.quantity',1);
                }
                elseif( $qtyType == 'dq')
                {
                    // echo "dq";
                    $prodMoveQty = $prodMoveQty->where('stock_movement_details.quantity',2);
                }
                $prodMoveQty = $prodMoveQty->where('stock_movement_details.status','pending');
                $onlyavail = $prodMoveQty->count('stock_movement_details.quantity');
                if($storeId != 0 )
                {
                    // echo "storeId =0";
                    $prodMoveQty = $prodMoveQty->where('stock_movement.from_store',$storeId);
                }
                $prodMoveQty = $prodMoveQty->count('stock_movement_details.quantity');
                // print_r(DB::getQueryLog());die;
                // print_r($prodMoveQty);echo "-n<br>".$productList[$i]['prodId'];//die;
                $productList[$i]['allocate']['qty'][$store[$j]['name']] = ($prodMoveQty == null) ? 0 : intval($prodMoveQty) ;
                $productList[$i]['forAvailAlloc']['qty'][$store[$j]['name']] = ($onlyavail == null) ? 0 : intval($onlyavail) ;
                $productList[$i]['allocate']['id'][$store[$j]['name']] = $store[$j]['store_id'];
            }
        }
        // var_dump($productList); die;
         print_r($productList);
        // echo 'end:- '.date('Y-m-d H:i:s');
        // die;
         return $productList;

    }
    public function onchangeStore(Request $request)
    {
        echo "not use";
        $storeId = $request->input('storeId');

        $productList = ProductModel::leftJoin('stock','product.id','stock.product_id')
        ->where('product.type','product')
        ->whereRaw('product.isforsale = 1')
        ->select('product.id as prodId')
        ->orderBy('model_name','ASC')
        ->groupBy('model_name')
        ->groupBy('model_variant')
        ->groupBy('product.id')->get()->toArray();

        $store = Store::where('id','<>',$storeId)->select(['id as store_id','store_type','name as realname',
                        DB::raw('concat(name,"_",store_type) as showname'),
                        DB::raw('concat(REPLACE(name," ",""),"_",store_type) as name')])
                    ->orderBy('store_type','ASC')->get()->toArray();

        for($i = 0 ; $i < count($productList) ; $i++)
        {
            for($j =0 ; $j < count($store) ; $j++)
            {
                $prodMoveQty = StockMovement::leftJoin('stock_movement_details','stock_movement.id','stock_movement_details.stock_movement_id')
                ->where('stock_movement_details.product_id',$productList[$i]['prodId']);
                if($storeId != 0)
                {
                    $prodMoveQty = $prodMoveQty->where('stock_movement.from_store',$storeId);
                }
                $prodMoveQty = $prodMoveQty->where('stock_movement.to_store',$store[$j]['store_id'])
                ->where('stock_movement_details.status','pending')
                ->count('stock_movement_details.quantity');
                                
                //print_r($prodMoveQty);echo "<br>";//die;
                $productList[$i]['qty'][$store[$j]['name']] = ($prodMoveQty == null) ? 0 : $prodMoveQty ;
                $productList[$i]['id'][$store[$j]['name']] = $store[$j]['store_id'];
            }
        }

        return $productList;

    }
    public function stockAllocView(){
       
        $data=array('layout'=>'layouts.main');
        return view('admin.stockAllocation.stockAllocView',$data);   
    }
    public function stockAllocView_api(Request $request){
        
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
        
        $user_role_q = Users::where('id',Auth::Id())->first(['user_type','role','store_id']);
        $user_role = $user_role_q->toArray()['role'];
        $user_type = $user_role_q->toArray()['user_type'];
        $user_store = explode(',',$user_role_q->toArray()['store_id']); 

            $api_data= StockMovement:://leftJoin('stock_movement_details','stock_movement_details.stock_movement_id','stock_movement.id')
            leftJoin('store as from_store','from_store.id','stock_movement.from_store')
            ->leftJoin('store as to_store','to_store.id','stock_movement.to_store')
            // ->where('stock_movement.quantity','<>',0)
            ->whereRaw('(select count(id) from stock_movement_details where stock_movement_details.stock_movement_id = stock_movement.id and stock_movement_details.status <> "cancel" ) > 0')
            // ->WhereNotNull('stock_movement_details.id')
            ->leftJoin('users',function($join) {
                    $join->on('users.id',DB::raw(Auth::Id()));
            })
            ->where(function($query) use($user_store) {
                $query->whereIn('stock_movement.from_store',$user_store)
                ->orWhereIn('stock_movement.to_store',$user_store);
            });
            
            $api_data = $api_data->where('users.id',Auth::Id())
                    ->select(
                    DB::raw('stock_movement.id as id'),
                    //DB::raw("concat(product.model_category,'-',product.model_name,'-',product.model_variant,'-',product.color_code) as refer"),
                    DB::raw('stock_movement.quantity as quan'),
                    DB::raw('concat(from_store.name,"-",from_store.store_type) as fromstore'),
                    DB::raw('concat(to_store.name,"-",to_store.store_type) as tostore'),
                    //DB::raw('stock_movement.moved_date as moved_date'),
                    DB::raw('stock_movement.status as status'),
                    DB::raw('date(stock_movement.created_at) as alloc_date'),
                    DB::raw('users.role as user_role'),
                    DB::raw('users.user_type as type'),
                    DB::raw('users.store_id as selfstoreid')
            );

            
            if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('stock_movement.quantity','like',"%".$serach_value."%")
                    ->orwhere('from_store.name','like',"%".$serach_value."%")
                    ->orwhere('to_store.name','like',"%".$serach_value."%")
                    ->orwhere('stock_movement.created_at','like',"%".$serach_value."%")
                    ->orwhere('stock_movement.status','like',"%".$serach_value."%");
                });
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                'stock_movement.id',
                'from_store.name',
                'to_store.name',
                'to_store.created_at',
                'stock_movement.quantity',
                'stock_movement.status',
                'users.role',
                'users.role',
                'users.store_id'
                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('stock_movement.created_at','desc');      
        
        $count = $api_data->count();
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        // print_r($api_data);die;
        foreach($api_data as $key => $val)
        {
            $getpending =  StockMovementDetails::where('stock_movement_id',$val['id'])
                            ->where('status','pending')->count('id');
            $getprocess =  StockMovementDetails::where('stock_movement_id',$val['id'])
                            ->where('status','process')->count('id');
            $getdone =  StockMovementDetails::where('stock_movement_id',$val['id'])
                            ->where('status','done')->count('id');
            $getproduct_name =  StockMovementDetails::join('product','stock_movement_details.product_id','product.id')
                                    ->whereIn('stock_movement_details.status',array('pending','process','done'))
                                    ->where('stock_movement_details.stock_movement_id',$val['id'])
                                ->select(
                                    'stock_movement_details.stock_movement_id',
                                    // DB::raw("concat(product.model_name,'-',product.model_variant,'-',product.color_code) as product_name"),
                                    DB::raw("count(stock_movement_details.id) as count"),
                                    DB::raw("concat(product.model_name,'-',product.model_variant,'-',product.color_code) as prod_name")
                                )
                                ->groupBy('stock_movement_details.product_id')->get();
            $api_data[$key]['pending']   = $getpending;
            $api_data[$key]['process']   = $getprocess;
            $api_data[$key]['done']   = $getdone;

            $str = null;
            foreach($getproduct_name as $ind => $data){
                if(empty($str)){
                    $str = $data->prod_name.' ('.$data->count.') ';
                }else{
                    $str = $str.','.$data->prod_name.' ('.$data->count.') ';
                }
            }   

            $api_data[$key]['prod_name']   = $str;

        }
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);  
    }

    public function stockAllocAccept(Request $request,$stock_id)
    {
        $get_data_by_stock_id = StockMovementDetails::join('stock_movement','stock_movement_details.stock_movement_id','stock_movement.id')
                                ->where('stock_movement.id',$stock_id)
                                ->where('stock_movement.status','pending')
                                ->where('stock_movement_details.status','pending')
                                ->whereNull('stock_movement_details.loader_id')
                                ->join('store as fromstore','fromstore.id','stock_movement.from_store')
                                ->join('store as tostore','tostore.id','stock_movement.to_store')
                                ->join('product','stock_movement_details.product_id','product.id')
                               
                                ->select(['stock_movement.id as stock_mov_id',
                                // 'stock_movement_details.id as stock_mov_det_id',
                                //'stock_movement_details.damage_qty',
                                'stock_movement_details.product_id',
                                //  'concat(product.model_category,"-",product.model_name,"-",product.model_variant,"-",product.color_code) as product_name',
                                DB::raw("concat(product.model_category,'-',product.model_name,'-',product.model_variant,'-',product.color_code) as product_name"),
                                // DB::raw("sum(stock_movement_details.quantity)-sum(stock_movement_details.damage_qty) as req_qty"),
                                // DB::raw("CASE WHEN stock_movement_details.quantity = 1 THEN count(stock_movement_details.quantity) ELSE 0 END as req_qty"),
                                // DB::raw("CASE WHEN stock_movement_details.quantity = 2 THEN count(stock_movement_details.quantity) ELSE 0 END as reqd_qty"),
                                DB::raw("IFNULL((select count(smd.quantity) from stock_movement_details smd where smd.stock_movement_id = stock_movement.id and smd.product_id = stock_movement_details.product_id and smd.quantity = 1 and smd.status = 'pending') , 0) as req_qty"),
                                DB::raw("IFNULL((select count(smd.quantity) from stock_movement_details smd where smd.stock_movement_id = stock_movement.id and smd.product_id = stock_movement_details.product_id and smd.quantity = 2 and smd.status = 'pending') , 0) as reqd_qty"),
                                // DB::raw("CASE WHEN stock_movement_details.quantity = 2 THEN count(stock_movement_details.quantity) ELSE 0 END as reqd_qty"),
                                'stock_movement.quantity',
                                'from_store',
                                DB::raw('concat(fromstore.name,"-",fromstore.store_type) as from_store_name'),
                                DB::raw('concat(tostore.name,"-",tostore.store_type) as to_store_name'),
                                'to_store',
                                'stock_movement.status'])
                                ->groupBy('stock_movement_details.product_id')
                                ->orderBy('stock_movement_details.id')
                                // ->groupBy('stock_movement_details.quantity')
                                // ->groupBy('stock_movement_details.id')
        ;


        $get_data_by_stock_id = $get_data_by_stock_id->get();
        $product_ids = [];
        if(isset($get_data_by_stock_id[0]))
        {
            $getData = $get_data_by_stock_id->toArray();

            // print_r($getData);die;
            if($getData[0]['status'] == 'pending')
            {
                $i=0;$temp = array();
                foreach($getData as $key => $val)
                {   
                    if(!in_array($val['product_id'],$product_ids)){
                        array_push($product_ids,$val['product_id']);
                    }
                    // print_r($val);die;
                    $temp[$i]['stock_mov_id'] = $val['stock_mov_id'];
                    // $temp[$i]['stock_mov_det_id'] = $val['stock_mov_det_id'];
                    $temp[$i]['product_id'] = $val['product_id'];
                    $temp[$i]['product_name'] = $val['product_name'];
                    $temp[$i]['req_qty'] = $val['req_qty'];
                    $temp[$i]['quantity'] = $val['quantity'];
                    $temp[$i]['from_store'] = $val['from_store'];
                    $temp[$i]['from_store_name'] = $val['from_store_name'];
                    $temp[$i]['to_store_name'] = $val['to_store_name'];
                    $temp[$i]['to_store'] = $val['to_store'];
                    $temp[$i]['status'] = $val['status'];
                    $temp[$i]['reqd_qty'] = $val['reqd_qty'];
                    
                    if($val['req_qty'] > 0){
                        
                        $getEngineFrame = ProductDetails::where('product_id',$val['product_id'])
                            ->where('store_id',$val['from_store'])
                            ->where("status",'ok')
                            //->orwhere("status",'damage')
                            ->where("move_status",0)
                            ->orderBy('manufacture_date')
                            ->select('product_details.*',
                                DB::raw('DATEDIFF(CURRENT_DATE,manufacture_date) as frame_duration')
                            )
                            ->get()->toArray();
                            // print_r($getEngineFrame);die;
                        $j=0;
                            foreach($getEngineFrame as $key1 => $val1)
                            {
                                $temp[$i]['framedata'][$j]['frame'] = $val1['frame'];
                                $temp[$i]['framedata'][$j]['engine'] = $val1['engine_number'];
                                $temp[$i]['framedata'][$j]['product_details_id'] = $val1['id'];
                                $temp[$i]['framedata'][$j]['frame_duration'] = $val1['frame_duration'];
                                $temp[$i]['framedata'][$j]['status'] = ($val1['status'] == 'damage' ? 'damage' : '' );
                                $j++;
                            }
                    }
                        //$i++;
                    if($val['reqd_qty'] > 0){
                        $getEngineFrame = ProductDetails::where('product_id',$val['product_id'])
                            ->where('store_id',$val['from_store'])
                            // ->where("status",'ok')
                            ->where("status",'damage')
                            ->where("move_status",0)
                            ->select('product_details.*',
                                DB::raw('DATEDIFF(CURRENT_DATE,manufacture_date) as frame_duration')
                            )
                            ->orderBy('manufacture_date')->get()->toArray();
                        $j=0;
                            foreach($getEngineFrame as $key1 => $val1)
                            {
                                $temp[$i]['damage']['framedata'][$j]['frame'] = $val1['frame'];
                                $temp[$i]['damage']['framedata'][$j]['engine'] = $val1['engine_number'];
                                $temp[$i]['damage']['framedata'][$j]['product_details_id'] = $val1['id'];
                                $temp[$i]['framedata'][$j]['frame_duration'] = $val1['frame_duration'];
                                $temp[$i]['damage']['framedata'][$j]['status'] = ($val1['status'] == 'damage' ? 'damage' : '' );
                                $j++;
                            }
                    }
                        $i++;
                    
                    
                }
            }
            else
            {
                return back()->with("error",'Already Submit It');
            }
        }
        else
        {
            return back()->with('error','Some Error Occured');
        }
        $loader = Loader::where('status','Active')->get(['id','truck_number'])->toArray();

        $product_data = ProductModel::whereIn('id',$product_ids)
                                        ->select(
                                            'model_name',
                                            DB::raw(" concat('owner_manual','_',REPLACE(LOWER(product.model_name),' ','_')) as new_addon_om")
                                        )->groupBy('model_name');
        $product_models = $product_data->pluck('model_name')->toArray();
        $new_product_models = ProductModel::whereNotIn('model_name',$product_models)
                                            ->select(
                                                'id','model_name',
                                            DB::raw(" concat('owner_manual','_',REPLACE(LOWER(product.model_name),' ','_')) as om_addon")
                                                )
                                            ->groupBy('model_name')->orderBy('model_name')->get()->toArray();
        // print_r($product_models);die;
        
        $om_addon = VehicleAddonStock::rightJoinSub($product_data,'om_addon',function($join) use($getData){
                                            $join->on('om_addon.new_addon_om','=','vehicle_addon_stock.vehicle_addon_key')
                                            ->where('vehicle_addon_stock.store_id',$getData[0]['from_store'])
                                            ;
                                        })
                                        ->select('om_addon.new_addon_om as va_key',
                                            'om_addon.model_name as va_name',
                                            DB::raw('IFNULL(vehicle_addon_stock.qty,0) as va_qty')    
                                    )->get();
        // print_r($om_addon->toArray());die;                            
        $vehicle_addon = VehicleAddon::leftJoin('vehicle_addon_stock','vehicle_addon_stock.vehicle_addon_key','vehicle_addon.key_name')
                                    ->whereRaw("vehicle_addon.key_name <> 'owner_manual'")
                                    ->where('vehicle_addon_stock.store_id',$getData[0]['from_store'])
                                    ->select('vehicle_addon.key_name as va_key',
                                        'vehicle_addon.prod_name as va_name',
                                        'vehicle_addon_stock.qty as va_qty'    
                                )->get();
        
        // check battery old allocation which is in a stock movement process
        $old_battery_alloc_qty = StockMovement::where('stock_movement.to_store',$getData[0]['from_store'])
                            ->leftJoin('stock_movement_details','stock_movement_details.stock_movement_id','stock_movement.id')
                            ->leftJoin('stock_movement_process','stock_movement_process.id','stock_movement_details.stock_movement_process_id')
                            ->where('stock_movement_details.status','process')
                            ->selectRaw(
                                "GROUP_CONCAT(stock_movement_process.battery_id, ',') as battery_id"
                            )->groupBy('stock_movement.to_store')->first();
        
        $battery_ids =  [];
        if(isset($old_battery_alloc_qty->battery_id))
        {
            $ids = $old_battery_alloc_qty->battery_id;
            $battery_ids = !empty($ids)? explode(',',$ids) : [];
        }
        $battery = ProductModel::join('product_details','product_details.product_id','product.id')
                                ->where('product.type','battery')
                                ->where('product_details.store_id',$getData[0]['from_store'])
                                ->whereIn('product_details.status',array('ok','damage','expired'))
                                ->whereNotIn('product_details.id',$battery_ids)
                                ->select(
                                    'product_details.id',
                                    'product_details.frame',
                                    DB::raw('TIMESTAMPDIFF( YEAR, product_details.manufacture_date, now() ) as year'),
                                    DB::raw('TIMESTAMPDIFF( MONTH, product_details.manufacture_date, now() ) % 12 as month'),
                                    DB::raw('ROUND(TIMESTAMPDIFF( DAY, product_details.manufacture_date, now() ) % 30.4375) as day')
                                )->orderBy('product_details.manufacture_date','asc')
                                ->get();

        $data =array(
            'loader' => $loader,
            'book_data' => $temp,
            'battery'   =>  $battery,
            'vehicle_addon' =>  $vehicle_addon,
            'new_product_models'    =>  $new_product_models,
            'om_addon'  =>  $om_addon,
            'layout'=>'layouts.main'
        );
        // print_r($data);die;
        return view('admin.stockAllocation.stockAllocListBook',$data);

    }

    public function stockAllocAccept_DB(Request $request,$stock_id)
    {
        $product_id = $request->input('prod_name');
        $no_of_prod = count($product_id);

        if($no_of_prod <= 0 ){
            return back()->with('error','Please select minimum one frame number')->withInput();
        }
        $frames = [];
        $total_no_frame = 0;
        for($k = 0 ; $k < $no_of_prod ; $k++){
            $frame_data = $request->input('frame_'.$product_id[$k].'_');
            $frame_data = empty($frame_data) ? [] : $frame_data ;
            $frames[$product_id[$k]] = $frame_data;
            $total_no_frame = $total_no_frame+count($frame_data);
        }
        // print_r($total_no_frame);die;
        if($total_no_frame <= 0 ){
            return back()->with('error','Please select minimum one frame number')->withInput();
        }

        $move_date = $request->input('move_date');
        //print_r($request->input('fuel'));die;
        $valarr = [     
            'from_store'=>'required',
            'to_store'=>'required',
            'loader'=>'required',
            'prod_name.*'=>'required',
            'move_date'=>'required'
        ];
        $valmsg = [
            'loader.required'=>'This Field is required',
            'move_date.required'=>'This Field is required'
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
        
        try
        {
            DB::beginTransaction();

            $from_store_id = $request->input('from_store');
            $to_store_id = $request->input('to_store');     
            
            if(!isset($loader->id)){
                return back()->with('error','Loader Not Found.')->withInput();
            }
            // check fuel stock
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
            // loader capacity validation
            $loader_id = $request->input('loader');
            $loader_capacity = $loader->capacity;
            if($loader_capacity < $total_no_frame){
                return back()->with('error','Loader Capacity Should be '.$loader_capacity)->withInput();
            }
            //insert vehicle_addon stock movement
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

            $om_addon = $request->input('model');
            $om_addon = !empty($om_addon)?$om_addon : [];
            $om_addon_qty = $request->input('model_qty');
            $om_addon_qty = !empty($om_addon_qty)? $om_addon_qty : [];
            
            // validate vehicle addon
            $check_arr = VehicleAddon::whereRaw("key_name <> 'owner_manual' ")->get()->toArray();
            // $all_addon_list = array_merge($check_arr,$om_addon);

            $old_addon_alloc_qty = StockMovement::leftJoin('stock_movement_details','stock_movement_details.stock_movement_id','stock_movement.id')
                            ->leftJoin('stock_movement_process','stock_movement_process.id','stock_movement_details.stock_movement_process_id')
                            // ->leftJoin('stock_move_addon','stock_movement_process.id','stock_move_addon.stock_movement_process_id')
                            ->where('stock_movement_details.status','process')
                            ->where('stock_movement.to_store',$from_store_id)
                            ->selectRaw(
                                "group_concat( DISTINCT stock_movement_process.id, '') as process_id,
                                IFNULL(sum(stock_movement_process.mirror),0) as 'mirror',
                                IFNULL(sum(stock_movement_process.toolkit),0) as 'toolkit',
                                IFNULL(sum(stock_movement_process.first_aid_kit),0) as 'first_aid_kit',
                                IFNULL(sum(stock_movement_process.saree_guard),0) as 'saree_guard',
                                IFNULL(sum(stock_movement_process.bike_keys),0) as 'bike_keys'"
                            )->groupBy('stock_movement.to_store')->first();
            $process_ids = [];
            if(isset($old_addon_alloc_qty->process_id)){
                $old_addon_alloc_qty = $old_addon_alloc_qty->toArray();
                $process_ids = !empty($old_addon_alloc_qty['process_id']) ? explode(',',$old_addon_alloc_qty['process_id']) : [];
            }else{
                $old_addon_alloc_qty = [];
            }
            $get_old_om_addon_alloc_qty = StockMoveAddon::whereIn('stock_movement_process_id',$process_ids)
                                                        ->whereIn('addon_name',$om_addon)
                                                        ->selectRaw(
                                                            "addon_name,
                                                            IFNULL(sum(qty),0) as qty"
                                                        )->groupBy('addon_name')
                                                    ->get()->toArray();
            $old_om_addon_rec_qty = [];
            foreach($get_old_om_addon_alloc_qty as $k => $v){
                $old_om_addon_rec_qty[trim($v['addon_name'])] = $v['qty'];
            }
            $old_addon_alloc_qty = array_merge($old_addon_alloc_qty,$old_om_addon_rec_qty);

            $all_addon_req = [];
            foreach($check_arr as $ind => $val){
                $qty = $request->input($val['key_name']);
                if($qty > 0){
                    $all_addon_req[$val['key_name']] = $qty;

                    $check_qty = VehicleAddonStock::where('vehicle_addon_key',$val['key_name'])
                                            ->where('store_id',$from_store_id)->first();
                    if(!isset($check_qty->qty)){
                        DB::rollback();
                        return back()->with('error','Error, '.$val['prod_name'].' Have Not In Stock.')->withInput();
                    }
                    $old_alloc_qty = 0;
                    if(isset($old_addon_alloc_qty[$val['key_name']])){
                        $old_alloc_qty = $old_addon_alloc_qty[$val['key_name']];
                    }
                    $stock_qty = $check_qty->qty-$old_alloc_qty;
                    // check stock qty
                    if($stock_qty < $qty){
                        DB::rollback();
                        return back()->with('error','Error, '.$val['prod_name'].' Have Not In Stock.')->withInput();
                    }
                }
            }
            $total_om_qty = 0;
            $stock_move_addon_data = [];
            foreach($om_addon as $ind => $key_name){
                $qty = $om_addon_qty[$ind];
                if($qty > 0){
                    $total_om_qty = $total_om_qty+$qty;
                    $stock_move_addon_data[$key_name] = $qty;

                    $all_addon_req[$key_name] = $qty;
                    $all_addon_req['owner_manual'] = $total_om_qty;

                    $check_qty = VehicleAddonStock::where('vehicle_addon_key',$key_name)
                                            ->where('store_id',$from_store_id)->first();
                    $model_name = trim(strtoupper(str_replace(['owner_manual_','_'],['',' '],$key_name)));

                    if(!isset($check_qty->qty)){
                        DB::rollback();
                        return back()->with('error','Error, Model '.$model_name.' :- Owner Manual Have Not In Stock.')->withInput();
                    }
                    $old_alloc_qty = 0;
                    if(isset($old_addon_alloc_qty[$key_name])){
                        $old_alloc_qty = $old_addon_alloc_qty[$key_name];
                    }
                    $stock_qty = $check_qty->qty-$old_alloc_qty;
                    // check stock qty
                    if($stock_qty < $qty){
                        DB::rollback();
                        return back()->with('error','Error, Model '.$model_name.' :- Owner Manual Have Not In Stock.')->withInput();
                    }
                }
            }

            // get battery selected
            $battery = $request->input('battery_no');
            if(isset($battery[0])){ 
                $stockProcessData['battery_id'] = implode(',',$battery); 
                foreach($battery as $k => $v)
                {
                    $stock_col = '';
                    //get product id
                    $getProductId = ProductDetails::where('id',$v)
                                                    ->first();
                    //update battery stock
                    $updateProductDetails = ProductDetails::where('id',$v)
                                                ->update([
                                                    'store_id'  =>  $to_store_id,
                                                    'move_status'   =>  1
                                                ]);
                                                
                    $updateStockQty_dec = Stock::where('product_id',$getProductId->product_id)
                                            ->where('store_id',$from_store_id)
                                            ->decrement('quantity',1);
                    $stock_col = 'quantity';
                    $check_inc = Stock::where('product_id',$getProductId->product_id)
                                            ->where('store_id',$to_store_id)->first();
                    if(isset($check_inc->id))
                    {
                        // increment update
                        $updateStockQty_inc = Stock::where('product_id',$getProductId->product_id)
                                                ->where('store_id',$to_store_id)
                                                ->increment($stock_col,1);
                    }
                    else{
                        // increment insert
                        $updateStockQty_inc = Stock::insertGetId([
                            'product_id'    =>  $getProductId->product_id,
                            $stock_col  =>  1,
                            'store_id'  =>  $to_store_id
                        ]);
                    }
                }
            }

            $stockProcessData['owner_manual']   =  $total_om_qty;
            $stockProcessData['date']   =  $move_date;
            $smva_insert = StockMoveProcess::insertGetId($stockProcessData);
            // insert in stock move addon
            foreach($stock_move_addon_data as $key_name => $qty){
                $sma_data = [
                    'stock_movement_process_id' => $smva_insert,
                    'addon_name'    =>  $key_name,
                    'qty'   =>  $qty
                ];
                StockMoveAddon::insertGetId($sma_data);
            }
            // va stock qty inc or dec
            foreach($all_addon_req  as $key_name => $req_qty)
            {
                                                    
                $stockUpdate_dec = VehicleAddonStock::where('vehicle_addon_key',$key_name)
                                                ->where('store_id',$from_store_id)
                                                ->decrement('qty',$req_qty);
                $check_inc = VehicleAddonStock::where('vehicle_addon_key',$key_name)
                                                ->where('store_id',$to_store_id)->first();
                if(isset($check_inc->id))
                {
                    // update increment
                    $stockUpdate_inc = VehicleAddonStock::where('vehicle_addon_key',$key_name)
                                            ->where('store_id',$to_store_id)
                                            ->increment('qty',$req_qty);
                }
                else{
                    // insert increment
                    $stockUpdate_inc = VehicleAddonStock::insertGetId([
                                                'vehicle_addon_key' =>  $key_name,
                                                'qty'   =>  $req_qty,
                                                'store_id'  =>  $to_store_id
                                        ]);
                }
            
            }

            if($smva_insert)
            {
                $flag = 0;
                for($i = 0; $i < $no_of_prod ; $i++)
                {
                    $frames = $request->input('frame_'.$product_id[$i].'_');
                    // print_r($frames);die;
                   if(isset($frames[0]))
                   {
                   // print_r($frames);die;
                        for($j = 0; $j < count($frames) ; $j++)
                        {
                            
                            if($frames[$j] != 'default' && is_numeric($frames[$j]))
                            {
                                $pd_data = ProductDetails::where('id',$frames[$j])->first();
                                if(!isset($pd_data->id)){
                                    DB::rollBack();
                                    return back()->with('error','Product Not Found.')->withInput();
                                }
                                $check_qty = 1;
                                if($pd_data->status == 'damage'){
                                    $check_qty = 2;
                                }
                                $normal_id = StockMovementDetails::where("stock_movement_id",$stock_id)
                                                    ->where("product_id",$product_id[$i])->where("product_details_id",0)
                                                    ->where("status",'pending')->where('quantity',$check_qty)
                                                    ->whereNull("waybill_id")->whereNull("loader_id")
                                                    ->first('id');
                                if(!isset($normal_id->id)){
                                    DB::rollBack();
                                    return back()->with('error','Stock Movement Record Not Found.')->withInput();
                                }
                                $normal_id = $normal_id['id'];
                                $update_stock_mov_d =StockMovementDetails::where("id",$normal_id)
                                                        // ->where('stock_movement_process_id',0)
                                                    ->update(["product_details_id"=>$frames[$j],
                                                            'status' => 'process',
                                                            'loader_id' =>  $loader_id,
                                                            'stock_movement_process_id' =>  $smva_insert
                                                    ]);
                                if($update_stock_mov_d)
                                {
                                    $update_prod_d = ProductDetails::where("id",$frames[$j])
                                    ->update(["store_id"=>$to_store_id,
                                            "move_status"=>1]);
                                    $flag = 1;
                                }
                                else
                                {
                                    DB::rollBack();
                                    return back()->with('error','some error occurred')->withInput();
                                }
                            }
                        }
                    }
                }
                if($flag == 0)
                {
                    return back()->with('error','Please select minimum one frame number.')->withInput();
                }
                
                // if ( (isset($loader->type)) ? (($loader->type == 'ByRoad') ? true : false ) : false) {
                if ($loader->truck_number == 'ByRoad') {
                    if(!isset($checkfuelStock->id)){
                        DB::rollback();
                        return back()->with('error','Fuel Stock is not Available.')->withInput();
                    }
                    $addfuel = FuelModel::insertGetId([
                        'type_id' => $stock_id,
                        'store_id' => $from_store_id,
                        'fuel_mode'    =>  'Transfer',
                        'fuel_type'    =>  'Petrol',
                        'quantity' => $request->input('fuel'),
                        'requested_by' => Auth::id()
                    ]);
                    $fuelStock = FuelStockModel::where('id',$checkfuelStock->id)->decrement('quantity',$request->input('fuel'));
                }

                $check = StockMovementDetails::where('stock_movement_id',$stock_id)
                            ->where('status','pending')
                            ->whereNull('loader_id')->first();

                if(empty($check))
                {
                    $update_stock_mov = StockMovement::where("id",$stock_id)
                                        ->update(['status' => 'running']);
                }
            }
            else{
                DB::rollback();
                return back()->with('error','Something Went Wrong')->withInput();
            }
               
        }
        catch(\Illuminate\Database\QueryException $ex) {
            DB::rollBack();
            return back()->with('error','some error occurred'.$ex->getMessage())->withInput();
        }
        /* Add action Log */
        CustomHelpers::userActionLog($action='Stock Movement Process',$smva_insert,0,'Stock Movement');
        DB::commit();
        return redirect('/admin/stock/allocation/list')->with("success",'Submit Successfully'); 
    }

    //stock allocation cancel
    public function stockCancel(Request $request)
    {
        // echo "under construction";die;
        $stockId = $request->input('stockId');
        $allocated_qty = StockMovement::where('stock_movement.id',$stockId)
                        ->where('stock_movement.status','<>','moved')
                        ->where('stock_movement.quantity','>',0)
                        ->leftJoin("stock_movement_details",function($join){
                            $join->on("stock_movement_details.stock_movement_id","=","stock_movement.id")
                                    ->whereNull('stock_movement_details.waybill_id');
                        })
                        ->leftJoin('product','product.id','stock_movement_details.product_id')
                        ->where(function($query){
                            $query->where('stock_movement_details.status','pending')
                                    ->orwhere('stock_movement_details.status','process');
                        })->where('stock_movement_details.quantity',1)
                        ->select(
                            DB::raw("GROUP_CONCAT(stock_movement_details.id) as ids"),
                            DB::raw("count(stock_movement.id) as alloc_qty"),
                            'stock_movement_details.product_id',
                            DB::raw("concat(product.model_name,'-',product.model_variant,'-',product.color_code) as prod_name"),
                            'stock_movement.to_store'
                        )->groupBy('stock_movement_details.product_id')->get();

        $notCancelIds = [];   
        foreach($allocated_qty as $k => $v){
            // check qty will be used to to_store or not
            $checkqtyused = Stock::where('product_id',$v->product_id)
                                ->where('store_id',$v->to_store)->pluck('quantity');
            if($checkqtyused[0] < $v->alloc_qty){
                $howmany = $v->alloc_qty-$checkqtyused[0];
                $notCancelIds[] = [
                    'prodId' => $v->product_id,
                    'qty'   =>  $howmany,
                    'prod_name' =>  $v->prod_name
                ];
                // $ids = explode(',',$v->ids);
                // for($j = 0; $j < $howmany; $j++){
                //     array_push($notCancelIds,$ids[$j]);
                // }
            }
        }
        // print_r($notCancelIds);die;

        $stock_data = StockMovement::where('stock_movement.id',$stockId)
                        ->where('stock_movement.status','<>','moved')
                        ->where('stock_movement.quantity','>',0)
                        ->leftJoin("stock_movement_details",function($join){
                            $join->on("stock_movement_details.stock_movement_id","=","stock_movement.id")
                                    ->whereNull('stock_movement_details.waybill_id');
                        })
                        // ->leftJoin("stock_movement_process",function($join){
                        //     $join->on("stock_movement_process.id","=","stock_movement_details.stock_movement_process_id");
                        // })
                        // ->whereNotIn('stock_movement_details.id',$notCancelIds)
                        ->where(function($query){
                            $query->where('stock_movement_details.status','pending')
                                    ->orwhere('stock_movement_details.status','process');
                        })
                        ->leftJoin('product','product.id','stock_movement_details.product_id')
                        ->leftJoin('product_details','product_details.id','stock_movement_details.product_details_id')
                        ->where(function($query){
                            $query->where('product_details.status','<>','sale')
                                    ->orwhereNull('product_details.status');
                        })
                        ->select('stock_movement.id as stockmId','stock_movement.quantity as stockmQty','stock_movement.from_store',
                                'stock_movement.to_store','stock_movement.status as stockmStatus',
                                'stock_movement_details.*',
                                DB::raw('concat(product.model_category," - ",product.model_name," - ",product.model_variant," - ",product.color_code) as prod_name'),
                                'product_details.frame'               
                    )->orderBy('stock_movement_details.stock_movement_process_id','ASC')->get();

        $stock_process_data = [];
        foreach($stock_data as $key => $val){
            $process_id = $val->stock_movement_process_id;
            if(!isset($stock_process_data[$process_id])){
                $stock_process_data[$process_id] = [];
            }elseif(!isset($stock_process_data[$process_id]['ndq']) && !isset($stock_process_data[$process_id]['dq'])){
                $stock_process_data[$process_id]['ndq'] = [];
                $stock_process_data[$process_id]['dq'] = [];
            }

            if($val->quantity == 1){
                $stock_process_data[$process_id]['ndq'][] = $val->toArray();
            }elseif($val->quantity == 2){
                $stock_process_data[$process_id]['dq'][] = $val->toArray();
            }
        }
        // print_r($stock_process_data);die;
        // $not_process_ndq_data = $stock_data->where('stock_movement_process_id',0)->get();
        // $not_process_dq_data = $stock_data->where('stock_movement_process_id',0)->get();
        // $ndq_data = $stock_data->where('stock_movement_details.quantity',1)->get();
        // $dq_data = $stock_data->where('stock_movement_details.quantity',2)->get();

        // print_r($stock_data->toArray());die;

        $addon = VehicleAddon::whereNotIn('key_name',['owner_manual'])->get();
        $select_str = [];
        foreach($addon as $a => $b){
            array_push($select_str,'IFNULL(stock_movement_process.'.$b->key_name.',0) as '.$b->key_name);
        }
        $select_addon_str = join(',',$select_str);
        // battery data
        $alloc_process_data = StockMovement::where('stock_movement.id',$stockId)
                ->leftJoin('stock_movement_details','stock_movement_details.stock_movement_id','stock_movement.id')
                ->leftJoin('loader','loader.id','stock_movement_details.loader_id')
                ->Join('stock_movement_process','stock_movement_process.id','stock_movement_details.stock_movement_process_id')
                ->where('stock_movement_details.status','process')
                ->select(
                    DB::raw($select_addon_str),                    
                    'stock_movement_process.battery_id',
                    'stock_movement_process.id',
                    DB::raw('DATE(stock_movement_process.date) as process_date'),
                    'loader.truck_number',
                    'stock_movement.to_store',
                    DB::raw("GROUP_CONCAT(stock_movement_details.product_details_id) AS pd_id")
                )->groupBy('stock_movement_process.id')->get();
        $process_data = [];
        $battery_data = [];
        $unload_addon_data = [];
        $total_alloc_addon = [];
        $total_sort_addon = [];
        // print_r($alloc_process_data->toArray());die;
        if(isset($alloc_process_data[0])){
            $to_store = $alloc_process_data[0]->to_store;
            foreach($alloc_process_data as $k => $v){
                $process_data[$v->id] = $v->toArray();
                // create battery data
                $battery_data[$v->id] = [];
                $battery_ids = !empty($v->battery_id)?explode(',',$v->battery_id):[];
                $battery_data_q = ProductDetails::whereIn('id',$battery_ids)
                                ->select('frame','id')->where('status','<>','sale')->where('move_status',1)->get();

                if(isset($battery_data_q[0])){
                    $battery_data[$v->id] = $battery_data_q->toArray();
                }
    
                // unload addon stock allocation data
                $unload_addon_data[$v->id] = [];
                foreach($addon as $a => $b){
                    $key = $b->key_name;
                    if($v->$key > 0){
                        $unload_addon_data[$v->id][$key] = $v->$key;
                        if(!isset($total_alloc_addon[$key])){
                            $total_alloc_addon[$key] = $v->$key;
                        }else{
                            $total_alloc_addon[$key] = $total_alloc_addon[$key]+$v->$key;
                        }
                    }
                }
            }
            // print_r($total_alloc_addon);
            // print_r($unload_addon_data);die;
            // get owner manuals respective stock_movement_process_id
            $process_ids = array_keys($unload_addon_data);
            $get_old_om_addon_alloc_qty = StockMoveAddon::whereIn('stock_movement_process_id',$process_ids)
                                                        ->whereRaw("qty > 0")
                                                        ->select(
                                                            "stock_movement_process_id as process_id",
                                                            "addon_name",
                                                            "qty"
                                                        )->get()->toArray();
            $old_om_addon_qty = [];

            foreach($get_old_om_addon_alloc_qty as $k => $v){
                $old_om_addon_qty[trim($v['addon_name'])] = $v['qty'];

                $unload_addon_data[$v['process_id']][$v['addon_name']] = $v['qty'];

                if(!isset($total_alloc_addon[$v['addon_name']])){
                    $total_alloc_addon[$v['addon_name']] = $v['qty'];
                }else{
                    $total_alloc_addon[$v['addon_name']] = $total_alloc_addon[$v['addon_name']]+$v['qty'];
                }
            }
            // print_r($old_om_addon_qty);
            // print_r($total_alloc_addon);die;

            // check which addon stock, should not be cancel
            foreach($total_alloc_addon as $name => $qty){
                if($qty > 0){
                    $check = VehicleAddonStock::where('vehicle_addon_key',$name)
                                                ->where('store_id',$to_store)
                                                ->select('qty')->first();
                    if(isset($check->qty)){
                        if($check->qty < $qty){
                            $howmany = $qty-$check->qty;
                            if(!isset($total_sort_addon[$name])){
                                $total_sort_addon[$name] = $howmany;
                            }else{
                                $total_sort_addon[$name] = $total_sort_addon[$name]+$howmany;
                            }
                        }
                    }else{
                        return back()->with('error','Something Went Wrong.');
                    }
                }
            }
        }
        // print_r($process_data);die;
            // print_r($battery_data);
            // print_r($unload_addon_data);
            // print_r($total_alloc_addon);
            // print_r($total_sort_addon);die;
                                
        $data=array(
            'layout'=>'layouts.main',
            'notCancelIds' =>  $notCancelIds,
            'stock_process_data' =>  $stock_process_data,
            'battery_data'  =>  $battery_data,
            'process_data'  =>  $process_data,
            'unload_addon_data' =>  $unload_addon_data,
            'total_alloc_addon' =>  $total_alloc_addon,
            'total_sort_addon'  =>  $total_sort_addon
        );
        return view('admin.stockAllocation.stockAllocCancel',$data); 

    }

    public function stockCancel_DB(Request $request)
    {
        $stockmId = $request->input('stockmId');
        $sm_data = StockMovement::where('stock_movement.id',$stockmId)
                        ->where('stock_movement.status','<>','moved')
                        ->where('stock_movement.quantity','>',0)
                        ->first();
        if(!isset($sm_data->id)){
            return back()->with('error','Stock Movement Not Found.')->withInput();
        }
        $smd_data_temp = StockMovementDetails::where('stock_movement_id',$stockmId)
                                        ->whereIn('status',['pending','process'])
                                        ->get();
        if(!isset($smd_data_temp[0])){
            return back()->with('error','Stock Movement Product Not Found.')->withInput();
        }
        // print_r($smd_data->toArray());die;
        $total_prod_alloc = count($smd_data_temp);
        $smd_data = [];   // create stock_movement_details data, which easy to access to stock_movement_details_id
        $process_ids = [];
        foreach($smd_data_temp as $in => $val){
            $smd_data[$val->id] =   $val;
            if($val->status == 'process' && $val->stock_movement_process_id > 0){
                if(!in_array($val->stock_movement_process_id,$process_ids)){
                    array_push($process_ids,$val->stock_movement_process_id);
                }
            }
        }
        $smd_ids = array_keys($smd_data);
        // validations
        // get total ndq selected data

        $cancel_battery_data = $request->input('battery_check');
        if(empty($cancel_battery_data)){ $cancel_battery_data = [];}

        $ndq = $request->input('ndq_check');
        $dq = $request->input('dq_check');
        if(empty($ndq)){ $ndq = []; }
        if(empty($dq)){ $dq = []; }
        $total_prod_cancel = count($ndq)+count($dq);
        $all_prod_cancel_req = array_merge($ndq,$dq);

        $ndq_prod_ids = [];$cancel_ndq_prod_qty = [];
        foreach($ndq as $i => $y){
            $prod_id = $request->input('prod_id'.$y);
            if(!in_array($prod_id,$ndq_prod_ids)){
                array_push($ndq_prod_ids,$prod_id);
                $cancel_ndq_prod_qty[$prod_id] = 1;
            }else{
                $cancel_ndq_prod_qty[$prod_id] = $cancel_ndq_prod_qty[$prod_id]+1;
            }
        }
        
        // if cancel any ndq product_id then check stock validation respected product_id
        foreach($ndq_prod_ids as $k => $prod_id){
            $prod_name = $request->input('prod_name'.$prod_id);
            $stock = Stock::where('product_id',$prod_id)->where('store_id',$sm_data->to_store)
                            ->first();
            if(!isset($stock->id)){
                return back()->with('error','Stock Not Found.')->withInput();
            }     
            if($cancel_ndq_prod_qty[$prod_id] > $stock->quantity){
                return back()->with('error',' For Product :- '.$prod_name.', You have Cancel Maximum Quantity Should be :- '.$stock->quantity)->withInput();
            }           
        }
        // print_r($ndq_prod_ids);die;
        // validate unload addon stock qty
        $addon = VehicleAddon::where('key_name','<>','owner_manual')->select('key_name','prod_name')->get();
        $select_str = [];
        foreach($addon as $a => $b){
            array_push($select_str,'IFNULL(stock_movement_process.'.$b->key_name.',0) as '.$b->key_name);
        }
        $select_addon_str = join(',',$select_str);
        $get_process_data = StockMovementDetails::where('stock_movement_details.status','process')
                                            ->whereIn('stock_movement_details.id',$smd_ids)
                                            ->where('stock_movement_details.stock_movement_process_id','>',0)
                                            ->groupBy('stock_movement_details.stock_movement_process_id')
                                            ->Join('stock_movement_process','stock_movement_process.id','stock_movement_details.stock_movement_process_id')
                                            ->select(
                                                'stock_movement_details.stock_movement_process_id as process_id',
                                                'stock_movement_process.battery_id',
                                                DB::raw("GROUP_CONCAT(stock_movement_details.product_id, '') as prod_id"),
                                                DB::raw("GROUP_CONCAT(stock_movement_details.product_details_id, '') as prod_det_id"),
                                                DB::raw("GROUP_CONCAT(stock_movement_details.id, '') as smd_id"),
                                                DB::raw($select_addon_str)
                                            )
                                            ->orderBy('process_id')
                                            ->get()->toArray();
        $get_old_om_addon = StockMoveAddon::whereIn('stock_movement_process_id',$process_ids)
                                                        ->select(
                                                            'stock_movement_process_id  as process_id',
                                                            'addon_name',
                                                            'qty'
                                                        )->get()->toArray();
        $old_om_addon = [];
        foreach($get_old_om_addon as $k => $v){
            if(!isset($old_om_addon[$v['process_id']])){
                $old_om_addon[$v['process_id']] = [];
            }
            $old_om_addon[$v['process_id']][trim($v['addon_name'])] = $v['qty'];
        }
        // print_r($old_om_addon);die;
        // get product data according process_id
        $check_prod_cancel_process = [];  // get how many cancel qty requested and already allocated for addon/battery validations
        $battery_process_data = [];  // get battery process data 
        // print_r($check_prod_cancel_process);die;
        $total_cancel_req = [];
        $cancel_process_addon_req = [];
        if(isset($get_process_data[0])){
            // print_r($request->input('mirror_12'));die;
            $validator = Validator::make($request->all(),[],[]);
            // stock movement detail ids
            foreach($get_process_data as $ind => $data){
                $total_pending_cancel_qty = 0;  // validation , if unload addon qty move 1 or more and product not move then mandatory to move addon with min 1 product

                $process_id = $data['process_id'];

                $check_prod_cancel_process[$process_id] = [];
                $alloc_smd_id = explode(',',$data['smd_id']);

                $count_alloc_smd_id = count($alloc_smd_id);
                $count_cancel_smd_id = 0;
                // we need to get how many product cancel requested for this process id
                foreach($alloc_smd_id as $k => $smd_id){
                    if(in_array($smd_id,$all_prod_cancel_req)){  // check when product_details_id are assigned
                        $count_cancel_smd_id++;
                    }
                }
                $check_prod_cancel_process[$process_id] = [
                    'alloc_prod_qty' =>  $count_alloc_smd_id,
                    'cancel_prod_qty' =>  $count_cancel_smd_id
                ];
                $this_all_addons = $addon->toArray();
                // add owner manual addons 
                $allocated_om_addon = [];
                $total_cancel_om_addon = 0;
                if(isset($old_om_addon[$process_id])){
                    $data = array_merge($data,$old_om_addon[$process_id]);
                    $this_om_addons = array_keys($old_om_addon[$process_id]);
                    $allocated_om_addon = array_keys($old_om_addon[$process_id]);
                    foreach($this_om_addons as $k => $v){
                        $this_om_addons[$k] = [];
                        $this_om_addons[$k]['key_name'] = $v;
                    }

                    $this_all_addons = array_merge($this_all_addons,$this_om_addons);
                }
                // print_r($data);
                // ------------------- for addon stock validation
                $cancel_process_addon_req[$process_id] = [];
                foreach($this_all_addons as $a => $b){
                    $key = $b['key_name'];

                    $cancel_qty = $request->input($key.'_'.$process_id);
                    $alloc_qty = $data[$key];
                    if($alloc_qty != $cancel_qty){
                        $total_pending_cancel_qty = $total_pending_cancel_qty+1;
                    }
                    if($cancel_qty > 0){
                        if($cancel_qty > $alloc_qty && $alloc_qty > 0){
                            // return back()->with('error','')->withInput();
                            $arr = [$key,$process_id,$alloc_qty];
                            $validator->after(function ($validator) use($arr) {
                                $validator->errors()
                                ->add($arr[0].'_'.$arr[1], 'This Field Maximum Cancel Quantity is :- '.$arr[2]);     
                            });
                        }
                        if(!isset($total_cancel_req[$key])){
                            $total_cancel_req[$key] = $cancel_qty;
                        }else{
                            $total_cancel_req[$key] = $total_cancel_req[$key]+$cancel_qty;
                        }
                        
                        $cancel_process_addon_req[$process_id][$key] = $cancel_qty;

                        if(in_array($key,$allocated_om_addon)){
                            $total_cancel_om_addon = $total_cancel_om_addon+$cancel_qty;
                            $cancel_process_addon_req[$process_id]['owner_manual'] = $total_cancel_om_addon;
                        }
                    }
                }
                // --------------------
                // --------------- for battery validation
                $battery_process_data[$process_id] = [];
                $battery = (!empty($data['battery_id']))? explode(',',$data['battery_id']) : [] ;
                $battery_process_data[$process_id]['alloc_battery_id'] = $battery;
                $count_battery_cancel = 0;
                $battery_cancel = [];
                foreach($battery as $q => $w){
                    if(in_array($w,$cancel_battery_data)){
                        $count_battery_cancel++;
                        array_push($battery_cancel,$w);
                    }
                }
                $battery_process_data[$process_id] = [
                    'alloc_qty' => count($battery),
                    'cancel_qty' => $count_battery_cancel,
                    'cancel_battery_id' =>  $battery_cancel
                ];

                // ------------------------
                // validation , if unload addon qty move 1 or more and product not move then mandatory to move addon with min 1 product
                if($total_pending_cancel_qty != 0 || ($battery_process_data[$process_id]['alloc_qty'] != $battery_process_data[$process_id]['cancel_qty']) ){
                    if($check_prod_cancel_process[$process_id]['alloc_prod_qty'] == $check_prod_cancel_process[$process_id]['cancel_prod_qty'] ){
                        // return back()->with('error','You have to move addons with minimum 1 Product.')->withInput();
                        $validator->after(function ($validator) use($process_id) {
                            $validator->errors()
                            ->add('process_id-'.$process_id,"In this Process, Product Shouldn't Cancel minimum 1 Qty, when Addon/Battery remaining to move.");     
                        });
                    }
                }
            }
            
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            // check which addon stock, should not be cancel
            foreach($total_cancel_req as $name => $qty){
                if($qty > 0){
                    $check = VehicleAddonStock::where('vehicle_addon_key',$name)
                                                ->where('store_id',$sm_data->to_store)
                                                ->select('qty')->first();
                    if(isset($check->qty)){
                        if($check->qty < $qty){
                            $addon_name = ucwords(str_replace('_',' ',$name));
                            return back()->with('error','Addon "'.$addon_name.'", Total Cancel Quantity should be less or equal to '.$check->qty)->withInput();
                        }
                    }else{
                        return back()->with('error','Something Went Wrong.');
                    }
                }
            }
        }
        // print_r($get_process_data);
        // print_r($cancel_process_addon_req);die;

        $ndq_data = $ndq;
        $dq_data = $dq;
        
        // print_r($ndq_data);print_r($dq_data);print_r($cancel_battery_data);print_r($cancel_process_addon_req);die;
        try{
            DB::beginTransaction();
            if((count($ndq_data) > 0 || count($dq_data) > 0 ))
            {
                $getProcessId = array();
                if(count($ndq_data) > 0)
                {
                    //non-damage stock cancelled
                    foreach($ndq_data as $k => $v)
                    {
                        $get_data = $smd_data[$v];

                        // print_r($get_data);die;
                        if(!isset($get_data->id))
                        {
                            DB::rollBack();
                            return back()->with('error','Stock Movement Product Not Found.')->withInput();
                        }
                        if(!in_array($get_data->stock_movement_process_id,$getProcessId))
                        {
                            $smp_id = $get_data->stock_movement_process_id; 
                            array_push($getProcessId,$smp_id);
                            // print_r($getProcessId);die;
                        }
                        $update_stockmd_status = StockMovementDetails::where('id',$get_data->id)
                                                ->update(['status'  =>  'cancel']);
                        $inc_stock_qty    =    Stock::where('product_id',$get_data->product_id)
                                                ->where('store_id',$sm_data->from_store)->increment('quantity',1);
                        
                        $dec_stock_qty    =    Stock::where('product_id',$get_data->product_id)
                                                    ->where('store_id',$sm_data->to_store)->decrement('quantity',1);
                        $dec_stockm_qty    =    StockMovement::where('id',$sm_data->id)
                                                    ->decrement('quantity',1);

                        if($get_data->product_details_id > 0){

                            $update_product_details = ProductDetails::where('id',$get_data->product_details_id)     
                            ->update(['store_id'    =>  $sm_data->from_store,
                                'move_status'   =>  0
                            ]);
                            if(!$update_product_details){
                                DB::rollback();
                                return back()->with('error','Some Error Occurred.')->withInput();
                            }
                        }
                        if(!$update_stockmd_status || !$inc_stock_qty || !$dec_stock_qty || !$dec_stockm_qty)
                        {
                            DB::rollback();
                            return back()->with('error','Some Error Occurred.')->withInput();
                        }
                    }
                }

                if(count($dq_data) > 0)
                {
                    //non-damage stock cancelled
                    foreach($dq_data as $k => $v)
                    {
                        $get_data = $smd_data[$v];

                        if(!isset($get_data->id))
                        {
                            DB::rollBack();
                            return back()->with('error','Stock Movement Product Not Found.')->withInput();
                        }

                        if(!in_array($get_data->stock_movement_process_id,$getProcessId))
                        {
                            array_push($getProcessId,$get_data->stock_movement_process_id);
                        }
                        $update_stockmd_status = StockMovementDetails::where('id',$get_data->id)
                                                ->update(['status'  =>  'cancel']);

                        $inc_stock_qty    =    Stock::where('product_id',$get_data->product_id)
                                                ->where('store_id',$sm_data->from_store)->increment('damage_quantity',1);
                        
                        $dec_stock_qty    =    Stock::where('product_id',$get_data->product_id)
                                                    ->where('store_id',$sm_data->to_store)->decrement('damage_quantity',1);
                        $dec_stockm_qty    =    StockMovement::where('id',$sm_data->id)
                                                    ->decrement('quantity',1);

                        if($get_data->product_details_id > 0){
                            $update_product_details = ProductDetails::where('id',$get_data->product_details_id)     
                                                        ->update(['store_id'    =>  $sm_data->from_store,
                                                                    'move_status'   =>  0
                                                        ]);
                            if(!$update_product_details)
                            {
                                DB::rollback();
                                return back()->with('error','Some Error Occurred.')->withInput();
                            }
                        }
                        if(!$update_stockmd_status && !$inc_stock_qty && !$dec_stock_qty && !$dec_stockm_qty)
                        {
                            DB::rollback();
                            return back()->with('error','Some Error Occurred.')->withInput();
                        }
                        
                    }
                }

                // below code not used removed it later
                $get_stock_m  = $sm_data;
                // $va_col = VehicleAddon::get();      // vehicle addon list
                if(!empty($getProcessId) && isset($get_stock_m->from_store))
                {
                    // foreach($getProcessId as $process_id)
                    // {
                    //     // print_r($process_id);die;
                    //     if($process_id)
                    //     {
                    //         $getprocessdata = StockMoveProcess::where('id',$process_id)->first();
                    //         // update vehicle qty
                    //         if(isset($getprocessdata->id))
                    //         {
                    //             foreach($va_col as $key1 => $val1)
                    //             {
                    //                 $col_name = $val1->key_name;
                    //                 $inc = VehicleAddonStock::where('vehicle_addon_key',$col_name)
                    //                                                     ->where('store_id',$get_stock_m->from_store)
                    //                                                     ->increment('qty',$getprocessdata->$col_name);
                    //                 $dec = VehicleAddonStock::where('vehicle_addon_key',$col_name)
                    //                                                 ->where('store_id',$get_stock_m->to_store)
                    //                                                 ->decrement('qty',$getprocessdata->$col_name);
                    //             }
        
                    //             // update battery
                    //             $battery_data = $getprocessdata->battery_id;
                    //             $battery_id = explode(',',$battery_data);     // actually it's product_details.id
                    //             if(isset($battery_id[0]))
                    //             {
    
                    //                 $get_nondamageId = ProductDetails::Join('stock',function($join) {
                    //                                                             $join->on('stock.product_id','product_details.product_id');
                    //                                                     })
                    //                                                 ->whereIn('product_details.id',$battery_id)
                    //                                                 ->whereIn('product_details.status',array('ok','expired'))
                    //                                                 ->select(DB::Raw("GROUP_CONCAT(DISTINCT product_details.product_id SEPARATOR ',') as b_id"))
                    //                                                 ->first();

                    //                 $get_damageId = ProductDetails::Join('stock',function($join) {
                    //                                                         $join->on('stock.product_id','product_details.product_id');
                    //                                                 })
                    //                                             ->whereIn('product_details.id',$battery_id)
                    //                                             ->whereIn('product_details.status',array('damage'))
                    //                                             ->select(DB::Raw("GROUP_CONCAT(DISTINCT product_details.product_id SEPARATOR ',') as b_id"))
                    //                                             ->first();
    
                    //                 // update all store id in prosuct details
                    //                 $updateProductDetails = ProductDetails::whereIn('id',$battery_id)
                    //                                                         ->update([
                    //                                                             'store_id'  =>  $get_stock_m->from_store
                    //                                                         ]);
                    //                 // update nondamage qty stock
                    //                 if(isset($get_nondamageId->b_id))
                    //                 {
    
                    //                     $updateStockInc = Stock::whereIn('product_id',explode(',',$get_nondamageId->b_id))
                    //                                                     ->where('store_id',$get_stock_m->from_store)
                    //                                                     ->increment('quantity',1);
                    //                     $updateStockDec = Stock::whereIn('product_id',explode(',',$get_nondamageId->b_id))
                    //                                                     ->where('store_id',$get_stock_m->to_store)
                    //                                                     ->decrement('quantity',1);
                    //                 }
                                    
                    //                 // update damage qty stock
                    //                 if(isset($get_damageId->b_id))
                    //                 {
                    //                     $updateStockInc = Stock::whereIn('product_id',explode(',',$get_damageId->b_id))
                    //                                                     ->where('store_id',$get_stock_m->from_store)
                    //                                                     ->increment('damage_quantity',1);
                    //                     $updateStockDec = Stock::whereIn('product_id',explode(',',$get_damageId->b_id))
                    //                                                     ->where('store_id',$get_stock_m->to_store)
                    //                                                     ->decrement('damage_quantity',1);
                    //                 }
                                                                            
                    //             }
    
                    //         }
                    //         else{
                    //             DB::rollback();
                    //             return back()->with('error','Error, Something Went Wrong')->withInput();
                    //         }
                    //     }
                                                                   
                        
                    // }
                }
            }
            // else{
            //     DB::rollback();
            //     return back()->with('error','Error, Please Select At Least One Product Name.');
            // }

            // reset battery stock in product_details table
            if(count($cancel_battery_data) > 0){
                // $temp_cancel_battery = $cancel_battery_data;
                foreach($cancel_battery_data as $in => $pd_id){
                    $battery_id = $pd_id;
                    foreach($get_process_data as $ind => $data){
                        $alloc_battery_id = $data['battery_id'];
                        $alloc_battery_id_arr =  !empty($alloc_battery_id) ? explode(',',$alloc_battery_id) : [];
                        if(in_array($battery_id,$alloc_battery_id_arr)){
                            array_splice($cancel_battery_data,$in,1);

                            foreach($alloc_battery_id_arr as $i => $y){  // for  remaining battery_ids
                                if($y == $battery_id){
                                    array_splice($alloc_battery_id_arr,$i,1);
                                }
                            }
                            $remaining_battery_ids = join(',',$alloc_battery_id_arr);

                            $pd_data = ProductDetails::where('id',$battery_id)->first();
                            if(!isset($pd_data->id)){
                                DB::rollBack();
                                return back()->with('error','Battery Movement Record Not Found.')->withInput();
                            }
                            $product_id = $pd_data->product_id;
                            // update stock_movement_process table
                            $updateProcess = StockMoveProcess::where('id',$process_id)
                                                    ->update([
                                                        'battery_id'    =>  $remaining_battery_ids
                                                    ]);
                            
                            $updateProductDetails = ProductDetails::where('id',$battery_id)
                                                                ->update([
                                                                    'move_status'   =>  0,
                                                                    'store_id'  =>  $sm_data->from_store
                                                                ]);

                            $updateStockInc = Stock::where('product_id',$product_id)
                                                        ->where('store_id',$sm_data->from_store)
                                                        ->increment('quantity',1);
                            $updateStockDec = Stock::where('product_id',$product_id)
                                                        ->where('store_id',$sm_data->to_store)
                                                        ->decrement('quantity',1);

                            if(!$updateProcess || !$updateProductDetails || !$updateStockInc || !$updateStockDec)
                            {
                                DB::rollback();
                                return back()->with('error','Some Error Occurred.')->withInput();
                            }
                        }
                    }
                }
                if(count($cancel_battery_data) > 0){ // when all requested battery ids not cancelled. 
                    DB::rollBack();
                    return back()->with('error','Battery Movement Record Not Found.')->withInput();
                }   
            }
            $any_addon_cancel = 0;
            // reset unload addon stock
            if(count($cancel_process_addon_req) > 0){
                foreach($cancel_process_addon_req as $process_id => $pro_data){
                    
                    if(isset($old_om_addon[$process_id])){
                        $om_addons_move = array_keys($old_om_addon[$process_id]);
                    }else{
                        $om_addons_move = [];
                    }
                    if(count($pro_data) > 0){
                        $any_addon_cancel = 1;
                    }
                    // owner manual addons cancel
                    foreach($pro_data as $key_name => $qty){
                        $inc = VehicleAddonStock::where('vehicle_addon_key',$key_name)
                                                            ->where('store_id',$sm_data->from_store)
                                                            ->increment('qty',$qty);
                        $dec = VehicleAddonStock::where('vehicle_addon_key',$key_name)
                                                        ->where('store_id',$sm_data->to_store)
                                                        ->decrement('qty',$qty);
                        if(in_array($key_name,$om_addons_move)){
                            // update stock_move_addon
                            $update_sma = StockMoveAddon::where('stock_movement_process_id',$process_id)
                                                            ->where('addon_name',$key_name)
                                                            ->decrement('qty',$qty);
                        }else{
                            // update in stock_movement_process
                            $updateStockProcess = StockMoveProcess::where('id',$process_id)
                                                    ->decrement($key_name,$qty);
                        }
                    }
                }
            }

            if(count($ndq_data) == 0 && count($dq_data) == 0 && count($cancel_battery_data) == 0 && $any_addon_cancel == 0){
                return back()->with('error','Required To Select Minimum One Product or Accessories.');
            }

            // stock movement reset status
            $check_data = StockMovement::where('stock_movement.id',$stockmId)
                                ->join('stock_movement_details','stock_movement_details.stock_movement_id','stock_movement.id')
                                ->select(
                                    'stock_movement.id as stockmId',
                                    'stock_movement.quantity',
                                    DB::raw('(select count(smd.id) from stock_movement_details smd WHERE smd.stock_movement_id = '.$stockmId.' and smd.status = "process") AS process_count'),
                                    DB::raw('(select count(smd.id) from stock_movement_details smd WHERE smd.stock_movement_id = '.$stockmId.' and smd.status = "pending") AS pending_count ')
                                )
                                ->first();
                // print_r($check_data);die;
            if($check_data)
            {
                    if($check_data->quantity > 0)
                    {
                        if($check_data->pending_count > 0)  // if any product still pending status
                        {
                            //status = pending
                            $update_sm = StockMovement::where('id',$stockmId)->update(['status' => 'pending']);
                            
                        }
                        elseif($check_data->process_count == 0 && $check_data->pending_count == 0)  // if all product are move status
                        {
                            //status = moved
                            $update_sm = StockMovement::where('id',$stockmId)->update(['status' => 'moved']);

                        }
                        elseif($check_data->process_count > 0 && $check_data->pending_count == 0)       // if all product are running status
                        {
                            //status = running
                            $update_sm = StockMovement::where('id',$stockmId)->update(['status' => 'running']);

                        }

                    }
                    else{
                        $update_sm = StockMovement::where('id',$stockmId)->update(['status' => 'pending']);
                    }
            }
        }
        catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return back()->with('error','some error occurred'.$ex->getMessage());
        }
        /* Add action Log */
        CustomHelpers::userActionLog($action='Stock Movement Cancel',$sm_data->id,0,'Stock Movement');
        DB::commit();
        return redirect('/admin/stock/allocation/list')->with('success','Successfully Cancelled');

    }
    public function stockmWaybillCancel(Request $request)
    {
        $stockmdId = $request->input('stockmdId');
        if(!$stockmdId)
        {
            return 'Some Error Occurred.';
        }
        $stockmdId = explode('-',$stockmdId);

        $smd_data_temp = StockMovementDetails::whereIn('id',$stockmdId)
                                        ->whereIn('status',['process'])
                                        ->get();
        if(!isset($smd_data_temp[0])){
            return back()->with('error','Stock Movement Product Not Found.')->withInput();
        }
        // print_r($smd_data->toArray());die;
        $total_prod_alloc = count($smd_data_temp);
        $smd_data = [];   // create stock_movement_details data, which easy to access to stock_movement_details_id
        $pd_id = [];
        $prod_ids = [];
        $process_id = 0;
        foreach($smd_data_temp as $in => $val){
            $smd_data[$val->id] =   $val;
            array_push($pd_id,$val->product_details_id);
            array_push($prod_ids,$val->product_id);
            $process_id = $val->stock_movement_process_id;
        }
        $sm_data = StockMovement::where('stock_movement.id',$smd_data_temp[0]->stock_movement_id)
                        ->where('stock_movement.status','<>','moved')
                        ->where('stock_movement.quantity','>',0)
                        ->first();
        if(!isset($sm_data->id)){
            return back()->with('error','Stock Movement Not Found.')->withInput();
        }
        // validate product
        $ndq_prod_ids = [];$cancel_ndq_prod_qty = [];
        foreach($prod_ids as $i => $prod_id){
            if(!in_array($prod_id,$ndq_prod_ids)){
                array_push($ndq_prod_ids,$prod_id);
                $cancel_ndq_prod_qty[$prod_id] = 1;
            }else{
                $cancel_ndq_prod_qty[$prod_id] = $cancel_ndq_prod_qty[$prod_id]+1;
            }
        }
        // print_r($cancel_ndq_prod_qty);
        // print_r($ndq_prod_ids);die;
        foreach($ndq_prod_ids as $in => $prod_id){

            $stock = Stock::where('product_id',$prod_id)->where('store_id',$sm_data->to_store)
                            ->first();
            if(!isset($stock->id)){
                return 'Stock Not Found.';
            }     
            if($cancel_ndq_prod_qty[$prod_id] > $stock->quantity){
                return 'This Process Will Not be Cancel.';
            }
        }

        if($process_id <= 0){
            return 'Stock Movement Process Data Not Found.';
        }
        // addon validation
        $addon = VehicleAddon::where('key_name','<>','owner_manual')->select('key_name','prod_name')->get();
        $select_str = [];
        foreach($addon as $a => $b){
            array_push($select_str,'IFNULL('.$b->key_name.',0) as '.$b->key_name);
        }
        $select_addon_str = join(',',$select_str);
        $get_process_data = StockMoveProcess::where('id',$process_id)->selectRaw($select_addon_str)->get();
        // print_r($get_process_data->toArray());die;
        if(!isset($get_process_data[0])){
            return 'Stock Process Data Not Found.';
        }
        $get_process_data = $get_process_data->toArray();
        // owner manual addons
        $get_old_om_addon = StockMoveAddon::where('stock_movement_process_id',$process_id)
                                                        ->select(
                                                            'addon_name',
                                                            'qty'
                                                        )->get()->toArray();

        $om_addon = [];
        $total_om_qty = 0;
        foreach($get_old_om_addon as $k => $v){
            $om_addon[trim($v['addon_name'])] = $v['qty'];
            $total_om_qty = $total_om_qty+$v['qty'];
        }                                        
        $get_process_data = array_merge($get_process_data[0],$om_addon); 
        // print_r($get_process_data);die;
        foreach($get_process_data as $name => $qty){
            if($qty > 0){
                $check = VehicleAddonStock::where('vehicle_addon_key',$name)
                                            ->where('store_id',$sm_data->to_store)
                                            ->select('qty')->first();
                if(!isset($check->qty)){
                    return 'Stock Movement Process Not Found.';
                }
                if($check->qty < $qty){
                    return 'This Process Will not be Cancel.';
                    // $addon_name = ucwords(str_replace('_',' ',$name));
                    // return back()->with('error','Addon "'.$addon_name.'", Total Cancel Quantity should be less or equal to '.$qty)->withInput();
                }
            }
        }
        // battery_ids validation
        $get_battery_ids = StockMoveProcess::where('id',$process_id)->select('battery_id')->first();
        $battery_ids = !empty($get_battery_ids->battery_id) ? explode(',',$get_battery_ids->battery_id) : [];
        $del_battery = [];
        foreach($battery_ids as $in => $ids){
            $get_prod_id = ProductDetails::where('id',$ids)->select('product_id')
                                    ->where('status','<>','sale')->where('move_status',1)->first();
            if(!isset($get_prod_id->product_id)){
                return 'Battery Stock is Used, so Process Not Cancel.';
            }
            $check_stock = Stock::where('product_id',$get_prod_id->product_id)
                                    ->where('store_id',$sm_data->to_store)
                                    ->where('quantity','>',0)
                                    ->first();
            if(!isset($check_stock->id)){
                return 'Battery Stock is Used, so Process Not Cancel.';
            }
            $del_battery[$ids] = $get_prod_id->product_id;
        }
        try{
            DB::beginTransaction();
            $stockmId = 0;
            $getProcessId = [];
            foreach($stockmdId as $k => $v )   //stock movement details id
            {
                $get_data = $smd_data[$v];

                if(isset($get_data->id))
                {
                    if(!in_array($get_data->stock_movement_process_id,$getProcessId))
                    {
                        array_push($getProcessId,$get_data->stock_movement_process_id);
                    }
                  
                    $stockmId = $get_data->stock_movement_id;
                    
                    // print_r($stockmId);die;
                    $update_stockmd_status = StockMovementDetails::where('id',$get_data->id)
                                                        ->where('status','process')
                                                        ->update(['status'  =>  'cancel']);

                    $dec_stockm_qty    =    StockMovement::where('id',$stockmId)
                                                            ->decrement('quantity',1);

                    $update_product_details = ProductDetails::where('id',$get_data->product_details_id)     
                                                            ->update(['store_id'    =>  $sm_data->from_store,
                                                                        'move_status'   =>  0
                                                            ]);
                    $waybill_update = Waybill::where('id',$get_data->waybill_id)
                                            ->update(['status' => 'cancel']);
                    if($get_data->quantity == 1)  // for non-damage qty
                    {
                            $inc_stock_qty    =    Stock::where('product_id',$get_data->product_id)
                                                            ->where('store_id',$sm_data->from_store)->increment('quantity',1);
                                    
                            $dec_stock_qty    =    Stock::where('product_id',$get_data->product_id)
                                                            ->where('store_id',$sm_data->to_store)->decrement('quantity',1);
                    }
                    elseif($get_data->quantity == 2){   // for damage qty
                        $inc_stock_qty    =    Stock::where('product_id',$get_data->product_id)
                                                            ->where('store_id',$get_data->from_store)->increment('damage_quantity',1);
                                    
                        $dec_stock_qty    =    Stock::where('product_id',$get_data->product_id)
                                                            ->where('store_id',$sm_data->to_store)->decrement('damage_quantity',1);
                    }
                   
                    if(!$update_stockmd_status || !$inc_stock_qty || !$dec_stock_qty || !$dec_stockm_qty || !$update_product_details)
                    {
                            DB::rollback();
                            return 'Some Error Occurred.';
                    }
                    
                }else{
                    DB::rollback();
                    return 'Stock Movement Product Record Not Found.';
                }
            }
            

            // reset all vehicle addon which assign with these frames
            $get_stock_m  = $sm_data;
            $va_col = VehicleAddon::get();      // vehicle addon list
            if(!empty($getProcessId) && isset($get_stock_m->from_store))
            {
                foreach($getProcessId as $process_id)
                {
                    if($process_id)
                    {
                        $getprocessdata = StockMoveProcess::where('id',$process_id)->first();
                        // update vehicle qty
                        if(isset($getprocessdata->id))
                        {
                            foreach($va_col as $key1 => $val1)
                            {
                                $col_name = $val1->key_name;
                                if($getprocessdata->$col_name > 0){

                                    $inc = VehicleAddonStock::where('vehicle_addon_key',$col_name)
                                                                        ->where('store_id',$get_stock_m->from_store)
                                                                        ->increment('qty',$getprocessdata->$col_name);
                                    $dec = VehicleAddonStock::where('vehicle_addon_key',$col_name)
                                                                    ->where('store_id',$get_stock_m->to_store)
                                                                    ->decrement('qty',$getprocessdata->$col_name);
                                    // update in process data
                                    $updateProcess = StockMoveProcess::where('id',$process_id)
                                                                    ->decrement($col_name,$getprocessdata->$col_name);
                                }
                            }
                            foreach($om_addon as $key_name => $qty)
                            {
                                $col_name = $key_name;
                                if($qty > 0){
                                    $inc = VehicleAddonStock::where('vehicle_addon_key',$col_name)
                                                                        ->where('store_id',$get_stock_m->from_store)
                                                                        ->increment('qty',$qty);
                                    $dec = VehicleAddonStock::where('vehicle_addon_key',$col_name)
                                                                    ->where('store_id',$get_stock_m->to_store)
                                                                    ->decrement('qty',$qty);
                                    // update in stock_move_addon
                                    $update_sma = StockMoveAddon::where('stock_movement_process_id',$process_id)
                                                                    ->where('addon_name',$col_name)
                                                                    ->decrement('qty',$qty);
                                }
                            }
    
                        }
                        else{
                            DB::rollback();
                            return 'Something Went Wrong';
                        }
                    }
 
                }
                // update battery stock
                foreach($del_battery as $pd_id => $prod_id){
                     // update in process data
                     $updateProcess = StockMoveProcess::where('id',$process_id)
                                        ->update([
                                            'battery_id' => null
                                        ]);

                    // update all store id in prosuct details
                    $updateProductDetails = ProductDetails::where('id',$pd_id)
                                        ->update([
                                            'store_id'  =>  $get_stock_m->from_store,
                                            'move_status'   =>  0
                    ]);                    

                    // stock Update
                    $updateStockInc = Stock::where('product_id',$prod_id)
                                                ->where('store_id',$get_stock_m->from_store)
                                                ->increment('quantity',1);
                    $updateStockDec = Stock::where('product_id',$prod_id)
                                                    ->where('store_id',$get_stock_m->to_store)
                                                    ->decrement('quantity',1);
                    
                    if(!$updateProcess || !$updateProductDetails || !$updateStockInc || !$updateStockDec){
                        DB::rollback();
                        return "Battery Stock Not Updated.";
                    }                                
                }
            }
         
            // update stock movement 
                $check_data = StockMovement::where('stock_movement.id',$stockmId)
                                ->join('stock_movement_details','stock_movement_details.stock_movement_id','stock_movement.id')
                                ->select(
                                    'stock_movement.id as stockmId',
                                    'stock_movement.quantity',
                                    DB::raw('(select count(smd.id) from stock_movement_details smd WHERE smd.stock_movement_id = '.$stockmId.' and smd.status = "process") AS process_count'),
                                    DB::raw('(select count(smd.id) from stock_movement_details smd WHERE smd.stock_movement_id = '.$stockmId.' and smd.status = "pending") AS pending_count ')
                                )
                                ->first();
                if($check_data)
                {
                    if($check_data->quantity > 0)
                    {
                        if($check_data->pending_count > 0)
                        {
                            //status = pending
                            $update_sm = StockMovement::where('id',$stockmId)->update(['status' => 'pending']);

                        }
                        elseif($check_data->process_count == 0 && $check_data->pending_count == 0)
                        {
                            //status = moved
                            $update_sm = StockMovement::where('id',$stockmId)->update(['status' => 'moved']);

                        }
                        elseif($check_data->process_count > 0 && $check_data->pending_count == 0)
                        {
                            //status = running
                            $update_sm = StockMovement::where('id',$stockmId)->update(['status' => 'running']);

                        }

                    }
                    else{
                        $update_sm = StockMovement::where('id',$stockmId)->update(['status' => 'pending']);
                    }
                    // if(!$update_sm)
                    // {
                    //     DB::rollback();
                    //     return 'Some Error Occurred';
                    // }
                }

        }
        catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return 'some error occurred'.$ex->getMessage();
        }
        /* Add action Log */
        CustomHelpers::userActionLog($action='Stock Movement Process Cancel',$process_id,0,'Stock Movement');
        DB::commit();
        return 'success';
    }

    public function stockGetLoader($id) {
       $loader = Loader::where('id',$id)->where('type','ByRoad')->get()->first();
       return response()->json($loader);
    }
    
    public function stockAllocProcess()
    {
        $data=array('layout'=>'layouts.main');
        return view('admin.stockAllocation.stockAllocProcessView',$data); 
    }
    public function stockAllocProcessView_api(Request $request){
        
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
        
        $time = $request->input('time');

        $user_role_q = Users::where('id',Auth::Id())->first(['user_type','role','store_id']);
        $user_role = $user_role_q->toArray()['role'];
        $user_type = $user_role_q->toArray()['user_type'];
        $user_store = explode(',',$user_role_q->toArray()['store_id']);

        // if($user_role == 'WarehouseManager' && $user_type == 'admin' )
        // {
            $api_data= StockMovementDetails::leftJoin('stock_movement','stock_movement_details.stock_movement_id','stock_movement.id')
            ->leftJoin('product','stock_movement_details.product_id','product.id')
            // ->leftJoin('')
            ->leftJoin('store as from_store','from_store.id','stock_movement.from_store')
            ->leftJoin('store as to_store','to_store.id','stock_movement.to_store')
            ->leftJoin('waybill','waybill.id','stock_movement_details.waybill_id')
            ->leftJoin('loader','loader.id','stock_movement_details.loader_id')
            ->leftJoin('stock_movement_process','stock_movement_process.id','stock_movement_details.stock_movement_process_id')
            ->leftJoin('users',function($join) {
                    $join->on('users.id',DB::raw(Auth::Id()));
            })
            ->where(function($query) use($user_store) {
                $query->whereIn('stock_movement.from_store',$user_store)
                ->orWhereIn('stock_movement.to_store',$user_store);
            });
            // ->where(function($query) {
            //     $query->where('stock_movement_details.status','process')
            //     ->orWhere('stock_movement_details.status','done');
            // });
                // if($user_role == 'Accountant')
                // {
                //     $api_data = $api_data->where('loader.truck_number','<>','ByRoad');
                // }
                // if($user_type == 'superadmin')
                // {
                //     $api_data=  $api_data->leftJoin('users',function($join) use($user_type){
                //         $join->on('user_type',DB::raw('\''.$user_type.'\''));
                //     });
                // }
                // else if($user_type == 'admin')
                // {
                //     $api_data=  $api_data->leftJoin('users',function($join) use($user_type){
                //         $join->on('users.store_id','stock_movement.from_store');
                //     });
                    
                // }
            if($user_type != 'superadmin')
                {
                    // $api_data=  $api_data->leftJoin('users',function($join) use($user_type){
                    //     $join->on('users.id',DB::raw(Auth::Id()))
                    //             ->on('stock_movement.from_store','IN',DB::raw('users.store_id'));
                    // });
                    $api_data->whereIn('stock_movement.from_store',$user_store);
                    
                }
                if($time == 'today')
                {
                    $api_data = $api_data->whereRaw("date(stock_movement_process.date) = current_date");
                }

                $api_data = $api_data->where('users.id',Auth::Id())
                    ->select(
                    DB::raw('stock_movement.id as id'),
                    //DB::raw("concat(product.model_category,'-',product.model_name,'-',product.model_variant,'-',product.color_code) as refer"),
                    DB::raw('stock_movement.quantity as quan'),
                    DB::raw('concat(from_store.name,"-",from_store.store_type) as fromstore'),
                    DB::raw('concat(to_store.name,"-",to_store.store_type) as tostore'),
                    DB::raw('waybill.waybill_number as waybill_no'),
                    DB::raw('waybill.file as file'),
                    DB::raw('waybill.id as waybill_id'),
                    DB::raw('loader.truck_number as loader_truck'),
                    DB::raw('loader.waybill_required as waybill_req'),
                    DB::raw('DATE(stock_movement_process.move_date) as moved_date'),
                    DB::raw('stock_movement_details.status as status'),
                    DB::raw('users.role as user_role'),
                    DB::raw('users.user_type as type'),
                    DB::raw('date(stock_movement_process.date) as acc_date'),
                    DB::raw('stock_movement_details.stock_movement_process_id'),
                    // DB::raw('users.store_id as selfstoreid'),
                    DB::raw("group_concat(stock_movement_details.id SEPARATOR '-') as stockMDId"),
                    DB::raw('count(stock_movement_details.stock_movement_id) as total'),
                    DB::raw("group_concat(concat(product.model_name,'-',product.model_variant,'-',product.color_code) SEPARATOR ' , ') as prod_name")
                )
                ->whereNotIn('stock_movement_details.status',['pending'])
                ->groupBy('stock_movement_details.stock_movement_process_id')
                ->groupBy('stock_movement_details.status');

                //print_r($api_data->get()->toArray());die;
            if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->
                    where('loader.truck_number','like',"%".$serach_value."%")
                    ->orwhere('waybill.waybill_number','like',"%".$serach_value."%")
                    ->orwhere('from_store.name','like',"%".$serach_value."%")
                    ->orwhere('to_store.name','like',"%".$serach_value."%")
                    ->orwhere('stock_movement.quantity','like',"%".$serach_value."%")
                    ->orwhere(DB::raw('DATE(stock_movement_process.move_date)'),'like',"%".$serach_value."%")
                    ->orwhere('stock_movement_details.status','like',"%".$serach_value."%");
                });
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                    'stock_movement_process_id',
                    'loader_truck',
                    'waybill_no',
                    'prod_name',
                    'fromstore',
                    'tostore',
                    'quan',
                    'moved_date',
                    'status'
                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('status','asc');      
        
        $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        // print_r($api_data);die;
        foreach($api_data as $key => $val)
        {
            $prod_name_array = explode(',',$val['prod_name']);
            $prearr = [];
            $prearrCount = [];
            $j = 0;
            $str = '';
            for($i = 0 ; $i < count($prod_name_array) ; $i++)
            {
                $prod_val = trim($prod_name_array[$i]);
                
                if(in_array($prod_val,$prearr))
                {
                    // echo $prod_val;die;
                    $index = array_search($prod_val,$prearr);
                    $prearrCount[$index] = $prearrCount[$index] + 1;
                }
                else{
                    $prearr[$j] = trim($prod_name_array[$i]);
                    $prearrCount[$j] = 1;
                    $j++;
                }
            }
            if(!empty($prearr))
            {
                foreach($prearr as $k => $v)
                {
                    // $str =  $prearr[$k].' ( '.$prearrCount[$k].' ) ';
                    // echo $str;
                    if($str == '')
                    {
                        $str = $prearr[$k].' ( '.$prearrCount[$k].' ) ';
                    }
                    else{
                        $str = $str.' , '.$prearr[$k].' ( '.$prearrCount[$k].' ) ';
                    }
                }
                $api_data[$key]['prod_name'] = $str;
            }
        }
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);  
    }
    public function generateWaybill(Request $request, $ids) {
        $SMdata = StockMovementDetails::where('id',$ids)->get()->first();
        $SMid= $SMdata['stock_movement_process_id'];
        $details = StockMoveProcess::leftJoin('stock_movement_details','stock_movement_details.stock_movement_process_id','stock_movement_process.id')
                    ->leftJoin('stock_movement','stock_movement.id','stock_movement_details.stock_movement_id')
                    ->leftJoin('product','product.id','stock_movement_details.product_id')
                    ->leftJoin('product_details','product_details.id','stock_movement_details.product_details_id')
                    ->leftJoin('loader','loader.id','stock_movement_details.loader_id')
                    ->leftJoin('store as fromstore','stock_movement.from_store','fromstore.id')
                    ->leftJoin('store as tostore','stock_movement.to_store','tostore.id')
                    ->where('stock_movement_details.stock_movement_process_id',$SMid)
                    ->select(DB::raw('concat(fromstore.name,"-",fromstore.store_type) as fromstore_name'),DB::raw('concat(tostore.name,"-",tostore.store_type) as tostore_name'),'loader.truck_number','product.type','product_details.frame','product.basic_price','product.model_category','stock_movement_process.date as process_date')->get();
        $data = array(
            'details' => $details,
            'stock_id' => $ids,
            'layout'    =>  'layouts.main'
        );
        return view('admin.stockAllocation.generateWaybill',$data);
    }
    public function waybill_DB(Request $request, $ids)
    {
        $SMDetailid = explode('-',$ids);
        $allowed_extension = array('pdf');

        $validator = Validator::make($request->all(),[
            'waybill' => 'required',
            'file' => 'required'
        ],[
            'waybill.required' => 'This Field is Required',
             'file.required' => 'This Field is Required'
        ]);

        $validator->sometimes('file', 'mimes:pdf', function ($input) use($allowed_extension) {
            $extension="";
            if(isset($input->file))
            $extension = $input->file->getClientOriginalExtension();
            if(in_array($extension,$allowed_extension))
                return false;
            else
                return true;
        });
        if ($validator->fails()) {
            return redirect('admin/stock/allocation/accountant/waybill/'.$ids)
                        ->withErrors($validator)
                        ->withInput();
        }
        try{
          //  DB::enableQueryLog();
            DB::beginTransaction();
            $user_id = Auth::Id();
            $waybill = $request->input('waybill');

            if($request->hasFile('file'))
                    {
                       
                            $file = $request->file('file');
                            $destinationPath = public_path().'/upload/stockallocation';
                            $filenameWithExt=$file->getClientOriginalName();
                            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME); 
                            $extension = $file->getClientOriginalExtension();    
                            $fileNameToStore = $filename.'_'.time().'.'.$extension;   
                            $resume=$fileNameToStore;
                            $file->move($destinationPath,$fileNameToStore);
                    }
                    else{
                        DB::rollBack();
                        return back()->with('error','!! Try Again !!')->withInput();
                    }

            $id = Waybill:: insertGetId([
                'waybill_number' => $waybill,
                'amount'    =>  20000,
                'file' => $resume,
                'created_by'    =>  $user_id,
                'stock_movement_details_id' => $ids
            ]);
           // print_r(DB::getQueryLog());die;
            $process_id = 0;
            $smd_data = StockMovementDetails::whereIn('id',$SMDetailid)
                                                ->first();
            if(!isset($smd_data->id)){
                DB::rollBack();
                return back()->with('error','Stock Movement Process Not Found.');
            }
            $process_id = $smd_data->stock_movement_process_id;
            if($id)
            {
                $update_stoc_mov = StockMovementDetails::where('stock_movement_process_id',$process_id)
                                    ->where('status','process')
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
        /* Add action Log */
        CustomHelpers::userActionLog($action='Stock Movement Generate Waybill',$process_id,0,'Stock Movement');
        DB::commit();
        return redirect('/admin/stock/allocation/process/list')->with("success",'submit successfully'); 
   
    }
    public function stockMoved($ids)
    {
        $SMDetailid = explode('-',$ids);
        $lastIndex = $SMDetailid[count($SMDetailid)-1];
        if($lastIndex == 'ByRoad')
        {
            array_splice($SMDetailid,count($SMDetailid)-1,1);
        }
        $loader = Loader::where('truck_number',$lastIndex)->where('status','Active')->first();
        if(!isset($loader->id)){
            return back()->with('error','Loader Not Found.')->withInput();
        }
        // print_r($loader);die;
        try{
            DB::beginTransaction();
            $date = date('Y-m-d');
            $getprocessId = StockMovementDetails::whereIn('id',$SMDetailid)
                                ->whereNotNull('loader_id')
                                ->where('status','process')
                                ->get();
            if(!isset($getprocessId[0])){
                return back()->with('error','Stock Movement Process Not Found.')->withInput();
            }
            $process_id = $getprocessId[0]->stock_movement_process_id;
            $sm_id = $getprocessId[0]->stock_movement_id;
            $stockmoveProcessId = StockMoveProcess::where('id',$process_id)->first();
            if(!isset($stockmoveProcessId->id)){
                return back()->with('error','Stock Movement Process Not Found.')->withInput();
            }
            // update stock movement process move_date
            $updateMoveDate = StockMoveProcess::where('id',$process_id)
                                                    ->update(['move_date'   =>  $stockmoveProcessId->date]);
            $battery_ids = !empty($stockmoveProcessId->battery_id)? explode(',',$stockmoveProcessId->battery_id) : [];
            if(isset($battery_ids[0])){
                $updateMovestatus = ProductDetails::whereIn('id',$battery_ids)->update([ 'move_status' => 0 ]);
            }
            foreach($getprocessId as $ind => $data){
                $stock_move = StockMovementDetails::where("id",$data->id)
                                ->whereNotNull('loader_id')
                                ->where('status','process');
                if($loader->waybill_required == 'Yes')
                {
                    $stock_move = $stock_move->whereNotNull('waybill_id');
                }
                $stock_move = $stock_move->update([
                    'status'    =>  'done',
                    // 'moved_date' => $stockmoveProcessId->date,
                    'moved_by'  =>  Auth::Id()
                ]);
                // update in product details
                $updateProdDet = ProductDetails::where('id',$data->product_details_id)
                                            ->update(['move_status' =>  0]);
                if(!$updateProdDet){
                    DB::rollBack();
                    return back()->with('error','Something went Wrong, Product Record Not Update.')->withInput();
                }
                if(!$stock_move){
                    DB::rollBack();
                    return back()->with('error','Something went Wrong, Stock Move Record Not Update.')->withInput();
                }
            }
            // check and update in stock movement 
            $check_sm = StockMovementDetails::where('stock_movement_id',$sm_id)
                            ->whereIn('status',['pending','process'])
                            ->first();
            if(!isset($check_sm->id)){
                $update_sm = StockMovement::where('id',$sm_id)
                                        ->where('status','<>','moved')
                                        ->update([
                                            'status'    =>  'moved'
                                        ]);
                if(!$update_sm){
                    DB::rollBack();
                    return back()->with('error','Something Went Wrong.')->withInput();
                }
            }

        }
        catch(\Illuminate\Database\QueryException $ex) {
            DB::rollBack();
            return back()->with('error','some error occurred'.$ex->getMessage())->withInput();
        }
        /* Add action Log */
        CustomHelpers::userActionLog($action='Stock Moved',$process_id,0,'Stock Movement');
        DB::commit();
        return redirect('/admin/stock/allocation/process/list')->with("success",'Submit Successfully'); 

    }

     public function get_stock_minqty($id) {
        $data =  DB::table("stock")
                    ->where('id',$id)
                    ->select("id","min_qty","product_id","store_id")->get()->first();
        return response()->json($data);
    }

    public function update_minqty(Request $request) {
        // print_r($request->input());
        //  die();
        try {
            $this->validate($request,[
                'stock_min_qty'=>'required',
            ],[
                'stock_min_qty.required'=> 'This is required.', 
            ]);

           if ($request->input('stock_min_qty') > 0) {
            $stockdata = Stock::where('id',$request->input('id'))->update(
                [
                    'min_qty'=>$request->input('stock_min_qty'),
                ]
            );
            }else{
                return redirect('/admin/stock/idealstock')->with('error','Min Quantity should be greater than 0 .'); 
             } 

           
            if($stockdata==NULL) 
            {
                //DB::rollback();
                return redirect('/admin/stock/idealstock')->with('error','Some Unexpected Error occurred.');
            }
            else{
                return redirect('/admin/stock/idealstock')->with('success','Successfully Updated Min Quantity .'); 
             }    
        
        }  catch(\Illuminate\Database\QueryException $ex) {
            return redirect('/admin/stock/idealstock')->with('error','some error occurred'.$ex->getMessage());
        }
    }

    public function stockAllocationHeaderFix()
    {
        // DB::enableQueryLog();
        $productList = ProductModel::leftJoin('stock','product.id','stock.product_id')
                        ->where('product.type','product')
                        ->select([
                        'model_name',
                        'model_variant',
                        'color_code',
                        'product.id as prodId',
                        DB::raw("sum(stock.quantity + stock.damage_quantity) as freeStock"),
                        DB::raw("sum(stock.quantity) as ndq"),
                        DB::raw("sum(stock.damage_quantity) as dq")
                        ])
                        //->where('product_details.status','ok')
                        ->orderBy('model_name','ASC')
                        ->groupBy('model_name')
                        ->groupBy('model_variant')
                        ->groupBy('product.id')->get()->toArray();
        // print_r(DB::getQueryLog());die;
        $store = Store::select(['id as store_id','store_type','name as realname',
                                DB::raw('concat(name,"_",store_type) as showname'),
                                DB::raw('concat(REPLACE(name," ",""),"_",store_type) as name')])
                            ->orderBy('store_type','ASC')->get()->toArray();

       
        for($i = 0 ; $i < count($productList) ; $i++)
        {
            for($j = 0 ; $j < count($store) ; $j++)
            {
                $prodStoreQty = Stock::
                    where('stock.product_id',$productList[$i]['prodId'])
                    ->where('stock.store_id',$store[$j]['store_id'])
                    ->select('stock.quantity','stock.min_qty','stock.damage_quantity')
                    ->first();
                
                $productList[$i]['diff']['qty'][$store[$j]['name']] = ($prodStoreQty['min_qty'] != 0) ? $prodStoreQty['min_qty'] : 0 ;
                $productList[$i]['diff']['id'][$store[$j]['name']] = $store[$j]['store_id'];
                // echo $productList[$i]['diff']['qty'][$store[$j]['name']];
                // echo "<br>";
                // echo 'storeName-'.$store[$j]['name'].'<br>';
                // echo $productList[$i]['diff']['id'][$store[$j]['name']];
                // echo "<br>";


                $productList[$i]['available']['qty'][$store[$j]['name']] = ($prodStoreQty['quantity'] != 0) ? $prodStoreQty['quantity'] : 0 ;
                $productList[$i]['available']['damageQty'][$store[$j]['name'].'-damage'] = ($prodStoreQty['damage_quantity'] != 0) ? $prodStoreQty['damage_quantity'] : 0 ;
                $productList[$i]['available']['id'][$store[$j]['name']] = $store[$j]['store_id'];
                
                $prodMoveQty = StockMovement::leftJoin('stock_movement_details','stock_movement.id','stock_movement_details.stock_movement_id')
                                        ->where('stock_movement_details.product_id',$productList[$i]['prodId'])
                                        ->where('stock_movement.to_store',$store[$j]['store_id'])
                                        ->where('stock_movement_details.status','<>',"done")
                                        //->where('stock_movement_details.damage_qty',0)
                                        // ->select(DB::raw('sum(stock_movement_details.quantity-stock_movement_details.damage_qty) as qty'),
                                        //     DB::raw('sum(stock_movement_details.damage_qty) as damageQty'))->first();
                                        ->select(DB::raw('CASE WHEN stock_movement_details.quantity = 1 THEN count(stock_movement_details.quantity) ELSE 0 END AS qty'),
                                            DB::raw('CASE WHEN stock_movement_details.quantity = 2 THEN count(stock_movement_details.quantity) ELSE 0 END AS damageQty'))->first();
                                        

                                        
                //print_r($prodMoveQty);echo "<br>";//die;
                $productList[$i]['allocate']['pending']['qty'][$store[$j]['name']] = ($prodMoveQty['qty'] == null) ? 0 : $prodMoveQty['qty'] ;
                $productList[$i]['allocate']['pending']['damageQty'][$store[$j]['name'].'-damage'] = ($prodMoveQty['damageQty'] == 0) ? 0 : $prodMoveQty['damageQty'] ;
                $productList[$i]['allocate']['pending']['id'][$store[$j]['name']] = $store[$j]['store_id'];

            }
        }
        // die;
        //     echo "<pre>";

        //    print_r($productList);die;
        
        $data = array(
            'layout'=>'layouts.main',
            'data' => $productList,
            'store' =>  $store
        );
        return view('admin.stockAllocation.StockMovementHeaderFix',$data);
    }

    // stock allocation using datatable
    public function stockAllocationDatatable(){

        $store = Store::select(['id as store_id',
                                'store_type',
                                // 'name as realname',
                                DB::raw('concat(name,"_",store_type) as showname'),
                                DB::raw('concat(REPLACE(name," ",""),"_",store_type) as name')]
                                )
                    ->whereNotIn('store_type',['SubDealer','CoDealer'])
                    ->orderBy('store_type','ASC')->get()->toArray();

        $data = array(
            'layout'=>'layouts.main',
            'store' =>  $store
        );
        return view('admin.stockAllocation.stockMovementDatatable',$data);
    }
    public function stockAllocation_api(Request $request)
    {
        $search = $request->input('search');
        $serach_value = isset($search['value'])?$search['value']:null;
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
        $storeId = $request->input('storeId');
        $qtyType = $request->input('ndq_dq');

        $store = Store::select(['id as store_id',
                                'store_type',
                                // 'name as realname',
                                DB::raw('concat(name,"_",store_type) as showname'),
                                DB::raw('concat(REPLACE(name," ",""),"_",store_type) as name')])
                                ->whereNotIn('store_type',['SubDealer','CoDealer'])
                    ->orderBy('store_type','ASC')->get()->toArray();
        // print_r($store);die;
        $strarr_store_id = [];
        $strarr_avail_qty = [];
        $strarr_avail_damage_qty = [];
        $strarr_diff_qty = [];
        $strarr_from_move_qty = [];
        $strarr_pending_damage_qty = [];
        
        foreach($store as $key => $val)
        {
            $strarr_store_id[] = $val['store_id']." as '".$val['name']."'";
            $strarr_avail_qty[] = "IFNULL((select stock.quantity from stock where stock.product_id = stock_allocation.prod_id and stock.store_id = ".$val['store_id']."),0) as '".$val['name']."-avail'"; 
            $strarr_avail_damage_qty[] = "IFNULL((select stock.damage_quantity from stock where stock.product_id = stock_allocation.prod_id and stock.store_id = ".$val['store_id']."),0) as '".$val['name']."-avail_damage'"; 

            $strarr_diff_qty[] = "IFNULL((select stock.min_qty from stock where stock.product_id = stock_allocation.prod_id and stock.store_id = ".$val['store_id']."),0) as '".$val['name']."-diff'"; 

            $strarr_process_qty[] = "IFNULL((SELECT IFNULL(COUNT(stock_movement_details.quantity),0) from stock_movement
                    left Join stock_movement_details on stock_movement.id = stock_movement_details.stock_movement_id where 
                    stock_movement_details.product_id = stock_allocation.prod_id and stock_movement_details.status in ('process','pending') 
                    and stock_movement_details.quantity = 1 
                    and stock_movement.to_store = ".$val['store_id']." limit 1 ),0) as '".$val['name']."-process'"; 
            $strarr_process_damage_qty[] = "IFNULL((SELECT IFNULL(COUNT(stock_movement_details.quantity),0) from stock_movement
                    left Join stock_movement_details on stock_movement.id = stock_movement_details.stock_movement_id where 
                    stock_movement_details.product_id = stock_allocation.prod_id and stock_movement_details.status in ('process','pending') 
                    and stock_movement_details.quantity = 2 
                    and stock_movement.to_store = ".$val['store_id']." limit 1 ),0) as '".$val['name']."-process_damage'"; 

            $strarr_from_move_qty[] = "IFNULL((SELECT IFNULL(COUNT(stock_movement_details.quantity),0) from stock_movement 
                    left Join stock_movement_details on stock_movement.id = stock_movement_details.stock_movement_id where 
                    stock_movement_details.product_id = stock_allocation.prod_id and stock_movement_details.status = 'pending'
                    ".(($storeId != 0)? "AND  stock_movement.from_store =".$storeId : "" )."
                    and stock_movement_details.quantity = ".(($qtyType == 'ndq')? 1 : 2)."
                     and stock_movement.to_store = ".$val['store_id']." limit 1 ),0) as '".$val['name']."-from_move'"; 

            // $strarr_pending_damage_qty[] = "IFNULL((SELECT IF(stock_movement_details.quantity = 2,COUNT(stock_movement_details.quantity),0) from stock_movement 
            //         left Join stock_movement_details on stock_movement.id = stock_movement_details.stock_movement_id where 
            //         stock_movement_details.product_id = stock_allocation.prod_id and (stock_movement_details.status = 'pending' ) and 
            //         stock_movement.to_store = ".$val['store_id']." limit 1 ),0) as '".$val['name']."-pending_damage'"; 
            // $strarr_process_damage_qty[] = "IFNULL((SELECT IF(stock_movement_details.quantity = 2,COUNT(stock_movement_details.quantity),0) from stock_movement left 
            //         Join stock_movement_details on stock_movement.id = stock_movement_details.stock_movement_id where 
            //         stock_movement_details.product_id = stock_allocation.prod_id and (stock_movement_details.status = 'process' ) and 
            //         stock_movement.to_store = ".$val['store_id']." limit 1 ),0) as '".$val['name']."-process_damage'"; 
            // $strarr_pending_damage_qty[] = "IFNULL((select stock.quantity from stock where stock.product_id = stock_allocation.prod_id and stock.store_id = ".$val['store_id']."),0) as '".$val['name']."-pending_damage'"; 
        }
        // $str = implode(',',$strarr_store_id).','.implode(',',$strarr_avail_qty).','.implode(',',$strarr_avail_damage_qty).','.implode(',',$strarr_diff_qty).','.implode(',',$strarr_pending_qty).','.implode(',',$strarr_process_qty).','.implode(',',$strarr_pending_damage_qty).','.implode(',',$strarr_process_damage_qty);
        $str = implode(',',$strarr_store_id).','.implode(',',$strarr_avail_qty).','.implode(',',$strarr_avail_damage_qty).','.implode(',',$strarr_diff_qty).','.implode(',',$strarr_from_move_qty).','.implode(',',$strarr_process_qty).','.implode(',',$strarr_process_damage_qty);
        
        $api_data = StockAllocationView::selectRaw("stock_allocation.*, ".$str."");

        if(!empty($serach_value))
            {
               $api_data->where(function($query) use ($serach_value){
                        $query->where('stock_allocation.model_name','like',"%".$serach_value."%")
                        ->orwhere('stock_allocation.model_variant','like',"%".$serach_value."%")
                        ->orwhere('stock_allocation.color_code','like',"%".$serach_value."%");
                    });
               
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                    'stock_allocation.model_name',
                    'stock_allocation.model_variant',
                    'stock_allocation.color_code'
                ];
                
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('stock_allocation.model_name')
                        ->orderBy('stock_allocation.model_variant')
                        ->orderBy('stock_allocation.color_code');
        
        $count = $api_data->count();
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        $productList = array();
        for($i = 0 ; $i < count($api_data) ; $i++)
        {
            $productList[$i]['prodId'] = $api_data[$i]['prod_id'];
            $productList[$i]['model_name'] = $api_data[$i]['model_name'];
            $productList[$i]['model_variant'] = $api_data[$i]['model_variant'];
            $productList[$i]['color_code'] = $api_data[$i]['color_code'];
            $productList[$i]['freeStock'] = $api_data[$i]['freeStock'];
            $productList[$i]['ndq'] = $api_data[$i]['ndq'];
            $productList[$i]['dq'] = $api_data[$i]['dq'];
            for($j = 0 ; $j < count($store) ; $j++)
            {
                $store_name = $store[$j]['name'];
                $store_id = $api_data[$i][$store_name];
                //-------------- for difference-------------
                $diffname = $store[$j]['name'].'-diff';
                $productList[$i]['diff']['qty'][$store[$j]['name']] = $api_data[$i][$diffname];
                // $productList[$i]['diff']['id'][$store[$j]['name']] = $store_id;
                
                //----------------- for available qty----------------
                $availname = $store[$j]['name'].'-avail';
                $availDamagename = $store[$j]['name'].'-avail_damage';
                $avail_ndq_dq = ($qtyType == 'ndq')? $availname : $availDamagename;
                $productList[$i]['available']['qty'][$store[$j]['name']] =  $api_data[$i][$avail_ndq_dq];
                // $productList[$i]['available']['damageQty'][$store[$j]['name'].'-damage'] =  $api_data[$i][$availDamagename];
                // $productList[$i]['available']['id'][$store[$j]['name']] = $store_id;
                
                //--------------- for pending qty which qty is stock movement pending------------
                // $allocatename = $store[$j]['name'].'-pending';
                // $productList[$i]['allocate']['pending']['qty'][$store[$j]['name']] = $api_data[$i][$allocatename];
                // $allocateDamagename = $store[$j]['name'].'-pending_damage';
                // $productList[$i]['allocate']['pending']['damageQty'][$store[$j]['name'].'-damage'] =  $api_data[$i][$allocateDamagename];
                // $productList[$i]['allocate']['pending']['id'][$store[$j]['name']] = $store_id;

                //--------------- for pending qty which qty is stock movement process------------
                $allocatename = $store[$j]['name'].'-process';
                $allocateDamagename = $store[$j]['name'].'-process_damage';
                $allocate_ndq_dq = ($qtyType == 'ndq') ? $allocatename : $allocateDamagename;

                $productList[$i]['allocate']['process']['qty'][$store[$j]['name']] = $api_data[$i][$allocate_ndq_dq];
                // $productList[$i]['allocate']['process']['damageQty'][$store[$j]['name'].'-damage'] =  $api_data[$i][$allocateDamagename];

                //--------------- for pending qty which qty is stock movement pending------------
                $from_move = $store[$j]['name'].'-from_move';
                $productList[$i]['allocate']['from_move']['qty'][$store[$j]['name']] = $api_data[$i][$from_move];
                
                // for diff calculate ----------
                $totalgreaterAvail = 1;
                if((($api_data[$i][$availname]+$api_data[$i][$availDamagename])-($api_data[$i][$allocatename]+$api_data[$i][$allocateDamagename])) <= 0){
                    $totalgreaterAvail = 0;
                }
                // cal some data for datatable
                $productList[$i]['diff'][$store[$j]['name']] = $api_data[$i][$diffname].'/'.(((($totalgreaterAvail>0)?$api_data[$i][$availname]:0)+$api_data[$i][$availDamagename])-$api_data[$i][$diffname]-(($totalgreaterAvail>0)?$api_data[$i][$allocatename]:0)-$api_data[$i][$allocateDamagename]);
                
                // for product available -----------
                $greaterAvail = 1;
                if(($api_data[$i][$avail_ndq_dq]-$api_data[$i][$allocate_ndq_dq]) <= 0){
                    $greaterAvail = 0;
                }
                $productList[$i]['available'][$store[$j]['name']] = ($greaterAvail > 0)?$api_data[$i][$avail_ndq_dq]-$api_data[$i][$allocate_ndq_dq]:0;
                
                $productList[$i][$store_name] = $store_name;
                $productList[$i]['store_id'][$store_name] = $store_id;
            }
        }
        // print_r($productList);die;
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $productList; 
        return json_encode($array);
        
    }

}
?>

 
