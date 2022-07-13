<?php

namespace App\Http\Controllers\admin;
use Illuminate\Http\Request;
use \stdClass;

use \App\Model\Store;
use\App\Model\AmcBookletStock;
use\App\Model\AmcBookletNumber;
use\App\Model\AmcBooklet;
use\App\Model\AmcBookletIssue;
use\App\Model\ProductModel;
use\App\Model\AmcBookletMovement;
use\App\Model\AmcBookletMovementDetail;
use \App\Custom\CustomHelpers;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\DB;
use \Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\Import;

use DateTime;
use Mail;
use Auth;
use Hash;
use PDF;



class AMCBookletController extends Controller
{
    public function __construct() {
        //$this->middleware('auth');
    }

    public function AmcBooklateNo_create() {
        $amc_booklet = ProductModel::groupBy('model_category')->where('model_category','<>','')->get();
        $store = Store::whereIn('id',CustomHelpers::user_store())
                    ->select('id',DB::raw('concat(store.name,"-",store.store_type) as name'))->get();
        $data = [
            'store' => $store,
            'amc_booklet' => $amc_booklet,
            'layout' => 'layouts.main'
        ];
        
        return view('admin.amc_booklet.amc_booklet_number',$data);
    }

    public function getEngineType(Request $request){
        $model_category = $request->input('model_category');
        $model_data = ProductModel::where('model_category',$model_category)
                        ->where('engine_capacity','<>',0)
                        ->orderBy('engine_capacity','ASC')
                        ->get();
        return response()->json($model_data);
    }

     public function getType(Request $request){
        $engine_type = $request->input('model_var');
        $model_category = $request->input('model_category');
        $engine_data = ProductModel::where('model_category',$model_category)->where('engine_capacity',$engine_type)
                        ->get();   
        return response()->json($engine_data);
    }

    public function AmcBooklateNoCreate_DB(Request $request) {
         try {
            $this->validate($request,[
                'model_category'=>'required',
                'engine_type'=>'required',
                'type'=>'required',
                'store_name'=>'required',
                'start_bookletNo'=>'required',
                'end_bookletNo'=>'required',
            ],[
                'model_category.required'=> 'This field is required.', 
                'engine_type.required'=> 'This field is required.',
                'type.required'=> 'This field is required.',
                'store_name.required'=> 'This field is required.',
                'start_bookletNo.required'=> 'This field is required.',
                'end_bookletNo.required'=> 'This field is required.',
            ]);
            DB::beginTransaction();
            $model_category = $request->input('model_category');
            $engine_type = $request->input('engine_type');
            $type = $request->input('type');
            $store_name = $request->input('store_name');
            $start_bookletNo = $request->input('start_bookletNo');
            $end_bookletNo = $request->input('end_bookletNo');
            if ($end_bookletNo < $start_bookletNo || $end_bookletNo == $start_bookletNo) {
                DB::rollback();
                    return redirect('/admin/amc/booklet/number')->with('error','End booklet number should be greater than start booklet number.')->withInput();
            }
            $count = $end_bookletNo - $start_bookletNo;
            $count = $count+1;
            for ($i=0; $i < $count; $i++) { 
                $booklet_no = AmcBookletNumber::where('booklet_number',$start_bookletNo+$i)->get()->first();
                if ($booklet_no) {
                    DB::rollback();
                    return redirect('/admin/amc/booklet/number')->with('error',$start_bookletNo+$i.' Booklet Number Already Added.')->withInput();
                }
                $insert = AmcBookletNumber::insertGetId([
                    'product_id' => $type,
                    'store_id' => $store_name,
                    'booklet_number' => $start_bookletNo+$i
                ]);

                if ($insert == null) {
                    DB::rollback();
                    return redirect('/admin/amc/booklet/number')->with('error','some error occurred')->withInput();
                }
            }

            $getStock = AmcBookletStock::where('product_id',$type)->where('store_id',$store_name)->get()->first();
            if ($getStock) {
                $update = AmcBookletStock::where('product_id',$type)->where('store_id',$store_name)->update([
                    'stock' => $getStock['stock']+$count
                ]);
                if ($update == null) {
                    DB::rollback();
                    return redirect('/admin/amc/booklet/number')->with('error','some error occurred')->withInput();
                }else{
                    DB::commit();
                    /* Add action Log */
                    CustomHelpers::userActionLog($action='Add AMC Booklet Number',$update,0);
                    return redirect('/admin/amc/booklet/number')->with('success','AMC Booklet Number Added Successfully');
                }
            }else{
                $stock = AmcBookletStock::insertGetId([
                    'stock' => $count,
                    'product_id' => $type,
                    'store_id' => $store_name
                ]);
                if ($stock == null) { 
                    DB::rollback();
                    return redirect('/admin/amc/booklet/number')->with('error','some error occurred')->withInput();
                }else{
                    DB::commit();
                    /* Add action Log */
                    CustomHelpers::userActionLog($action='Add AMC Booklet Number',$stock,0);
                    return redirect('/admin/amc/booklet/number')->with('success','AMC Booklet Number Added Successfully');
                }
            }

            
        }  catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return redirect('/admin/amc/booklet/number')->with('error','some error occurred'.$ex->getMessage())->withInput();
        }
        /* Add action Log */
        // CustomHelpers::userActionLog($action='Create Booking',$bookdata,0);
        // DB::commit();
        // return redirect('/admin/booking/')->with('success','Booking Successfully added .');
    }

    public function AmcBooklate_list() {
        return view('admin.amc_booklet.amc_booklet_issue_list',['layout' => 'layouts.main']);
   }

   public function AmcBooklateList_api(Request $request, $type) {
       $search = $request->input('search');
       $serach_value = $search['value'];
       $start = $request->input('start');
       $limit = $request->input('length');
       $offset = empty($start) ? 0 : $start ;
       $limit =  empty($limit) ? 10 : $limit ;
        if ($type == 'issued') {
            $api_data = AmcBookletIssue:: leftJoin('store','store.id','amc_booklet_issue.store_id')
                ->leftJoin('amc_booklet_number',function($join){
                        $join->on('amc_booklet_number.issue_id','=','amc_booklet_issue.id');
                    })
            ->where('amc_booklet_issue.status','<>','0')
           ->select(
            'amc_booklet_issue.id',
            'amc_booklet_issue.status',
            'amc_booklet_issue.type',
            'amc_booklet_issue.total_booklet',
            'amc_booklet_number.booklet_number',
            'store.id as store_id',
            DB::raw('concat(store.name,"-",store.store_type) as store_name')

           );
        }else{
            $api_data = AmcBookletIssue:: leftJoin('store','store.id','amc_booklet_issue.store_id')
                ->leftJoin('amc_booklet_number',function($join){
                       $join->on('amc_booklet_number.issue_id','=','amc_booklet_issue.id');
                    })
            ->where('amc_booklet_issue.status','0')
           ->select(
            'amc_booklet_issue.id',
            'amc_booklet_issue.status',
            'amc_booklet_issue.type',
            'amc_booklet_issue.total_booklet',
            'amc_booklet_number.booklet_number',
            'store.id as store_id',
            DB::raw('concat(store.name,"-",store.store_type) as store_name')

           );
        }
           if(!empty($serach_value))
           {
               $api_data->where(function($query) use ($serach_value){
                   $query->where('amc_booklet_issue.status','like',"%".$serach_value."%")
                   ->orwhere('amc_booklet_issue.type','like',"%".$serach_value."%")
                   ->orwhere('store.name','like',"%".$serach_value."%")
                   ->orwhere('amc_booklet_issue.total_booklet','like',"%".$serach_value."%")
                   ->orwhere('amc_booklet_number.booklet_number','like',"%".$serach_value."%")                  
                   ;
               });
           }
           if(isset($request->input('order')[0]['column']))
           {
               $data = [
                'store.name',
                'amc_booklet_number.booklet_number',
                'amc_booklet_issue.total_booklet',
                'amc_booklet_issue.type',
                'amc_booklet_issue.status'
                
               ];
               $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
               $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
           }
           else
               $api_data->orderBy('amc_booklet_issue.id','desc');      
       
       $count = count($api_data->get()->toArray());
       $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();

       $array['recordsTotal'] = $count;
       $array['recordsFiltered'] = $count;
       $array['data'] = $api_data; 
       return json_encode($array);
   }

   public function AmcBookletNumber_Get() {
     $getdata = AmcBookletNumber::where('status','Pending')->where('issue_id',0)->get();
         return response()->json($getdata);
   }

   public function AmcBookletNumber_Issue(Request $request){
        $issue_id = $request->input('issue_id');
        $store_id = $request->input('store_id');
        $booklet_no = $request->input('booklet_no');
        $booklet_no2 = $request->input('booklet_no2');
        try{
            DB::beginTransaction();
            if (empty($booklet_no)) { 
                return 'Booklet Number is required.';   
            }
            $checkIssue = $this->checkBookletIssue($issue_id);
            if($checkIssue) {
                if ($booklet_no != null && $booklet_no2 != null) {
                    if ($booklet_no == $booklet_no2) {
                        return 'Please select diffrent booklet number .';  
                    }
                    $status = 2;
                }else{
                    $status = 1;
                }

                $checkNo = AmcBookletNumber::where('id',$booklet_no)->where('store_id',$store_id)->where('status','<>','Issue')->where('issue_id',0)->get()->first();
                if ($checkNo == null) {
                      return 'This Booklet number already issued.';
                }else{
                  $checkStock1 = AmcBookletStock::where('store_id',$store_id)->where('product_id',$checkNo['product_id'])->get()->first();

                    if ($checkStock1['stock'] < 0) {
                        return 'Stock not available for "'.$booklet_no.'" booklet number.'; 
                    }

                    $updateNo =  AmcBookletNumber::where('id',$booklet_no)->where('store_id',$store_id)->update([
                        'issue_id' => $issue_id,
                        'status' => 'Issue'
                    ]);
                    if ($updateNo == null) {
                        DB::rollback();
                         return 'Something went wrong.';
                    }else{
                        $stockupdate = AmcBookletStock::where('store_id',$store_id)->where('product_id',$checkNo['product_id'])->update([
                                'stock' => $checkStock1['stock']-1,
                                'issued' => $checkStock1['issued']+1
                            ]);

                            if ($stockupdate == null) {
                                DB::rollback();
                                return 'Something went wrong.';
                             }
                    }
                }
                 if ($booklet_no2 != null) {
                        $checkNo2 = AmcBookletNumber::where('id',$booklet_no2)->where('store_id',$store_id)->where('status','<>','Issue')->where('issue_id',0)->get()->first();

                           if ($checkNo2 == null) {
                               return 'Booklet Number 2 is already issued.';  
                           }else{
                                $checkStock2 = AmcBookletStock::where('store_id',$store_id)->where('product_id',$checkNo2['product_id'])->get()->first();
                                if ($checkStock2['stock'] < 0) {
                                    return 'Stock not available for "'.$booklet_no2.'" booklet number.'; 
                                }
                           }
                            $updateNo2 =  AmcBookletNumber::where('id',$booklet_no2)->where('store_id',$store_id)->update([
                                'issue_id' => $issue_id,
                                'status' => 'Issue'
                            ]);
                             if ($updateNo2 == null) {
                                DB::rollback();
                                return 'Something went wrong.';
                             }
                             else{
                                $stockupdate2 = AmcBookletStock::where('store_id',$store_id)->where('product_id',$checkNo2['product_id'])->update([
                                    'stock' => $checkStock2['stock']-1,
                                    'issued' => $checkStock2['issued']+1
                                ]);
                                if ($stockupdate2 == null) {
                                    DB::rollback();
                                    return 'Something went wrong.';
                                 }

                             }
                    }

                    $updateIssue = AmcBookletIssue::where('id',$issue_id)->update([
                        'status' => $status
                    ]);
                    if ($updateIssue == null) {
                        DB::rollback();
                         return 'Something went wrong.';
                    }else{
                        DB::commit();
                        /* Add action Log */
                        CustomHelpers::userActionLog($action='AMC Booklet Number Issued',$updateIssue,0);
                        return 'success';
                    }
            }else{
                DB::rollback();
                return 'This Booklet number issue was not found';
            }
        }catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return 'some error occurred'.$ex->getMessage();
        }
   }


     public function checkBookletIssue($issue_id) {
        $data =  AmcBookletIssue::where('id',$issue_id)->get()->first();
        if($data)
        {
            return $data;
        }
        else
        {
            return 0;
        }
    }

    public function AmcBooklate_movement() {
        $store = Store::whereIn('id',CustomHelpers::user_store())
                    ->select('id',DB::raw('concat(store.name,"-",store.store_type) as name'))->get();
        $data = [
            'store' => $store,
            'layout' => 'layouts.main'
        ];
        
        return view('admin.amc_booklet.amc_booklet_movement',$data);
    }

    public function AmcBooklateMovement_DB(Request $request) {
          $validator = Validator::make($request->all(),[
           'from_store'  =>  'required',
           'to_store'  =>  'required',
           'start_bookletNo.*'  =>  'required',
           'end_bookletNo.*'  =>  'required'
        ],
        [
            
            'from_store.required'  =>  'This Field is required',
            'to_store.required'  =>  'This Field is required',
            'start_bookletNo.*.required'  =>  'This Field is required',
            'end_bookletNo.*.required'  =>  'This Field is required'
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        try {
         
            $from_store = $request->input('from_store');
            $to_store = $request->input('to_store');
            $start_no = $request->input('start_bookletNo');
            $end_no = $request->input('end_bookletNo');
            $count = count($start_no);
            DB::beginTransaction();
            if ($from_store == $to_store) {
                return redirect('/admin/amc/booklet/movement')->with('error','Please select diffrent store.')->withInput();
            }else{
                $insert = AmcBookletMovement::insertGetId([
                    'from_store' => $from_store,
                    'to_store' => $to_store,
                    'movement_date' => date('Y-m-d'),
                    'status' => 'Pending',
                    'created_by' => Auth::id()
                ]);
                if ($insert == null) {
                    DB::rollback();
                    return redirect('/admin/amc/booklet/movement')->with('error','Something went wrong.')->withInput();
                 }

            }
            $product_id = 0;
            for ($i=0; $i < $count; $i++) { 
                $diff = $end_no[$i]-$start_no[$i];

                for ($j=0; $j < $diff; $j++) { 
                    $getNo = AmcBookletNumber::where('store_id',$from_store)->where('booklet_number',$start_no[$i]+$j)->where('status','Pending')->get()->first();
                    $no = $start_no[$i]+$j;
                    $product_id = $getNo['product_id'];
                    if ($getNo == null) {
                        DB::rollback();
                        return redirect('/admin/amc/booklet/movement')->with('error','Booklet number '.$no.' not found.')->withInput();
                    }
                }
                if ($product_id != 0) {
                    $checkStock = AmcBookletStock::where('store_id',$from_store)->where('product_id',$product_id)->get()->first();
                    if ($checkStock['stock'] < $diff) {
                        DB::rollback();
                        return redirect('/admin/amc/booklet/movement')->with('error','Stock not available for booklet number'.$start_no[$i].' to booklet number'.$end_no[$i].'.')->withInput();
                    }
                }

                $insertNo = AmcBookletMovementDetail::insertGetId([
                    'movement_id' => $insert,
                    'start_booklet_no' => $start_no[$i],
                    'end_booklet_no' => $end_no[$i]
                 ]);
                 if ($insertNo == null) {
                    DB::rollback();
                    return redirect('/admin/amc/booklet/movement')->with('error','Something went wrong.')->withInput();
                 }
            }

        }catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return redirect('/admin/amc/booklet/movement')->with('error','some error occurred'.$ex->getMessage())->withInput();
        }
        DB::commit();
        /* Add action Log */
        CustomHelpers::userActionLog($action='AMC booklet movement',0,0);
        return redirect('/admin/amc/booklet/movement')->with('success','AMC booklet movement created Successfully.');
    }

    public function AmcBooklateMovement_list() {
        $data = [
            'layout' => 'layouts.main'
        ];
        
        return view('admin.amc_booklet.amc_booklet_movement_list',$data);
    }

    public function AmcBooklateMovementList_api(Request $request) {
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;

        $api_data = AmcBookletMovement::leftJoin('store as from_store','from_store.id','amc_booklet_movement.from_store')
           ->leftJoin('store as to_store','to_store.id','amc_booklet_movement.to_store')
           ->leftJoin('users','amc_booklet_movement.moved_by','users.id')
           ->select(
            'amc_booklet_movement.id',
            'amc_booklet_movement.status',
            'amc_booklet_movement.movement_date',
            'amc_booklet_movement.moved_date',
            'amc_booklet_movement.moved_by',
            DB::raw('concat(users.name," ",ifnull( users.middle_name," ")," ",ifnull( users.last_name," ")) as moved_by_name'),
            DB::raw('concat(from_store.name,"-",from_store.store_type) as from_store_name'),
            DB::raw('concat(to_store.name,"-",to_store.store_type) as to_store_name')
           );
       
           if(!empty($serach_value)) {
               $api_data->where(function($query) use ($serach_value){
                   $query->where('amc_booklet_movement.status','like',"%".$serach_value."%")
                   ->orwhere('amc_booklet_movement.from_store','like',"%".$serach_value."%")
                   ->orwhere('amc_booklet_movement.to_store','like',"%".$serach_value."%")
                   ->orwhere('amc_booklet_movement.movement_date','like',"%".$serach_value."%")
                   ->orwhere('amc_booklet_movement.moved_date','like',"%".$serach_value."%");
               });
           }
           if(isset($request->input('order')[0]['column'])) {
               $data = [
                'amc_booklet_movement.from_store',
                'amc_booklet_movement.to_store',
                'amc_booklet_movement.movement_date',
                'amc_booklet_movement.moved_date',
                'amc_booklet_movement.moved_by',
                'amc_booklet_movement.status'
                
               ];
               $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
               $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
           }
           else
               $api_data->orderBy('amc_booklet_movement.id','desc');      
       
       $count = count($api_data->get()->toArray());
       $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();

        foreach ($api_data as &$value) {
                $movement_date = $value['movement_date'];
                $moved_date = $value['moved_date'];
                if ($movement_date != null) {
                    $value['movement_date'] = date('d-m-Y', strtotime($movement_date));
                }else{
                    $value['movement_date'] = '';
                }
                if ($moved_date != null) {
                    $value['moved_date'] = date('d-m-Y', strtotime($moved_date));
                }else{
                    $value['moved_date'] = '';
                }
        }
       $array['recordsTotal'] = $count;
       $array['recordsFiltered'] = $count;
       $array['data'] = $api_data; 
       return json_encode($array);
    }

    public function AmcBooklateMovement_view($id) {
        $movement = AmcBookletMovement::leftJoin('store as from_store','from_store.id','amc_booklet_movement.from_store')
           ->leftJoin('store as to_store','to_store.id','amc_booklet_movement.to_store')
           ->leftJoin('users','amc_booklet_movement.moved_by','users.id')
           ->select(
            'amc_booklet_movement.id',
            'amc_booklet_movement.status',
            'amc_booklet_movement.movement_date',
            DB::raw('concat(users.name,"-",users.middle_name,"-",users.last_name) as moved_by_name'),
            DB::raw('concat(from_store.name,"-",from_store.store_type) as from_store_name'),
            DB::raw('concat(to_store.name,"-",to_store.store_type) as to_store_name')
           )->get()->first();
        $movement_detail = AmcBookletMovementDetail::leftJoin('amc_booklet_movement','amc_booklet_movement.id','amc_booklet_movement_detail.movement_id')
           ->leftJoin('store as from_store','from_store.id','amc_booklet_movement.from_store')
           ->leftJoin('store as to_store','to_store.id','amc_booklet_movement.to_store')
           ->where('amc_booklet_movement.id',$id)
           ->select(
            'amc_booklet_movement.id',
            'amc_booklet_movement.status',
            'amc_booklet_movement_detail.start_booklet_no',
            'amc_booklet_movement_detail.end_booklet_no',
            'amc_booklet_movement.movement_date',
            DB::raw('concat(from_store.name,"-",from_store.store_type) as from_store_name'),
            DB::raw('concat(to_store.name,"-",to_store.store_type) as to_store_name')
           )->get();
        $data = [
            'movement_detail' => $movement_detail,
            'movement' => $movement,
            'layout' => 'layouts.main'
        ];
        
        return view('admin.amc_booklet.amc_booklet_movement_view',$data);
    }

    public function AmcBooklateMovement($id) {
        try {
           DB::beginTransaction();
            $checkId = $this->checkBookletMoveId($id);
            if ($checkId) {
                $movement = AmcBookletMovement::where('id',$id)->first();
                $movement_detail = AmcBookletMovementDetail::leftJoin('amc_booklet_movement','amc_booklet_movement.id','amc_booklet_movement_detail.movement_id')
                   ->leftJoin('store as from_store','from_store.id','amc_booklet_movement.from_store')
                   ->leftJoin('store as to_store','to_store.id','amc_booklet_movement.to_store')
                   ->where('amc_booklet_movement.id',$id)
                   ->where('amc_booklet_movement.status','Pending')
                   ->select(
                    'amc_booklet_movement_detail.start_booklet_no',
                    'amc_booklet_movement_detail.end_booklet_no'
                   )->get();
                   $store_id = $movement->from_store;
                   $to_store = $movement->to_store;
                   $product_id = 0;
                    foreach ($movement_detail as $key => $detail) {
                      $booklet_number = AmcBookletNumber::where('booklet_number','>=',$detail->start_booklet_no)->where('booklet_number','<=',$detail->end_booklet_no)->where('status','Pending')->get();
                      $diff = count($booklet_number);
                      foreach ($booklet_number as  $booklet) {
                       
                        $product_id = $booklet->product_id;
                          if($detail->start_booklet_no == $booklet->booklet_number && $booklet->store_id == $store_id){
                             $updateBooklet = AmcBookletNumber::where('store_id',$store_id)->where('product_id',$booklet->product_id)->where('booklet_number',$booklet->booklet_number)->update([
                                'store_id' => $to_store
                             ]);
                             if ($updateBooklet == null) {
                                DB::rollback();
                                return 'Something went wrong.';
                             }
                          }else{
                            DB::rollback();
                            return $detail->start_booklet_no.' Booklet number not found';
                          }
                          $detail->start_booklet_no +=1; 

                      }

                      $checkStock = AmcBookletStock::where('store_id',$store_id)->where('product_id',$product_id)->where('stock','>=',$diff)->first();
                      if ($checkStock) {
                          $updateStock = AmcBookletStock::where('store_id',$store_id)->where('product_id',$product_id)->decrement('stock',$diff);
                          if ($updateStock == null) {
                              DB::rollback();
                             return 'Something went wrong.';
                          }
                      }else{
                         DB::rollback();
                         return 'Stock not available';
                      }

                      $checkToStock = AmcBookletStock::where('store_id',$to_store)->where('product_id',$product_id)->first();
                      if ($checkToStock) {
                           $updateToStock = AmcBookletStock::where('store_id',$to_store)->where('product_id',$product_id)->increment('stock',$diff);
                          if ($updateToStock == null) {
                              DB::rollback();
                             return 'Something went wrong.';
                          }
                      }else{
                         $insertToStock = AmcBookletStock::insertGetId([
                                'stock' => $diff,
                                'store_id' => $to_store,
                                'product_id' => $product_id
                          ]);
                          if ($insertToStock == null) {
                              DB::rollback();
                             return 'Something went wrong.';
                          }
                      }

                   }

                   $update = AmcBookletMovement::where('id',$id)->where('from_store',$store_id)->update([
                        'moved_date' => date('Y-m-d'),
                        'status' => 'Moved',
                        'moved_by' => Auth::id()
                   ]);
                   if ($update == null) {
                       DB::rollback();
                       return 'Something went wrong.';
                   }else{
                        DB::commit();
                        /* Add action Log */
                        CustomHelpers::userActionLog($action='AMC Booklet Number Moved',0,0);
                        return 'success';
                   }
                   
            }else{
                DB::rollback();
                return 'Amc booklet movement is not found.';
            }
            
        }catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return 'Something went wrong.';
        }
    }


    public function checkBookletMoveId($id) {
        $data =  AmcBookletMovement::where('id',$id)->get()->first();
        if($data) {
            return $data;
        } else {
            return 0;
        }
    }



}