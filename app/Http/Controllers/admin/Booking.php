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
use \App\Model\PaymentRequest;
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
use \App\Model\HiRise;
use \App\Model\Insurance;
use \App\Model\SaleOrder;
use \App\Model\RtoModel;
use \App\Model\Payment;
use \App\Model\OrderPendingModel;
use \App\Model\BookingModel;
use \App\Model\CancelRequest;
use \App\Model\Master;
use \App\Model\RefundRequest;

use Illuminate\Support\Facades\DB;
use Auth;
use Hash;
use \Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\Import;

class Booking extends Controller
{
    public function __construct()
    {
        //$this->middleware('auth');
    }
    
    public function booking() {
        $user = Users::where('id',Auth::Id())->get()->first();
        $SaleExecutive = Users::where('role','SaleExecutive')->get();
        $store = Store::All();
        $product = ProductModel::select('id','basic_price',
                        DB::raw('concat(model_name,"-",model_variant,"-",color_code) as prodName'))
                        ->where('type','product')->orderBy('model_name','ASC')->get();
        $model_name = ProductModel::where('type','product')
                        ->where('isforsale',1)
                        ->orderBy('model_name','ASC')->groupBy('model_name')->get(['model_name']);
        $data = array(
            'user' => $user,
            'SaleExecutive' => $SaleExecutive,
            'store' => $store,
            'product' => $product,
            'model_name' => $model_name,
            'layout' => 'layouts.main'
        );
         return view('admin.booking.booking',$data);
    }
      public function getModelVariant(Request $request)
    {
        $model_name = $request->input('model_name');
        $model_var = ProductModel::where('model_name',$model_name)->where('type','product')
                            ->where('isforsale',1)
                        ->orderBy('model_variant','ASC')->groupBy('model_variant')
                        ->get();
        return response()->json($model_var);

    }
    public function getModelColorCode(Request $request)
    {
        $model_var = $request->input('model_var');
        $model_name = $request->input('model_name');

        $model_color = ProductModel::where('model_variant',$model_var)
                        ->where('model_name',$model_name)->where('type','product')
                        ->where('isforsale',1)
                        ->orderBy('model_variant','ASC')
                        ->get();
        return response()->json($model_color);

    }

    public function productInfo(Request $request) {
       // print_r($request->input());die();
        $prodId = $request->input('prodId');
        $model_name = $request->input('model_name');
        $model_variant = $request->input('model_variant');
        $storeId = $request->input('storeId');
        if (!empty($request->input('storeId')) && !empty($request->input('model_name')) && empty($request->input('model_variant')) && empty($request->input('prodId')) ) {
                $product_id = ProductModel::where('model_name',$request->input('model_name'))
                                ->select('id')->get()->toArray();
                $getqty = Stock::whereIn('product_id',$product_id)->where('store_id',$storeId)
                    ->select(
                        DB::raw('IFNULL(max(stock.quantity),0) as quantity')
                    )->get()->first();
                    
        }
        if (!empty($request->input('storeId')) && !empty($request->input('model_name')) && !empty($request->input('model_variant')) && empty($request->input('prodId')) ) {
                $product_id = ProductModel::where('model_name',$request->input('model_name'))->where('model_variant',$request->input('model_variant'))->select('id')->get()->toArray();
                $getqty = Stock::whereIn('product_id',$product_id)->where('store_id',$storeId)
                    ->select(
                        'stock.product_id',
                        'stock.booking_qty',
                        DB::raw('IFNULL(max(stock.quantity),0) as quantity')
                    )->get()->first();
            }
        if (!empty($request->input('storeId')) && !empty($request->input('model_name')) && !empty($request->input('model_variant')) && !empty($request->input('prodId')) ) {
            $getqty = Stock::leftJoin('product','product.id','stock.product_id')
                    ->where('product_id',$prodId)->where('quantity','>',0)->where('store_id',$storeId)
                    ->select(
                        'product.basic_price',
                        'stock.quantity'
                    )->get()->first();
        }

        $arr = (($getqty)?$getqty->toArray():array());
        return response()->json($arr);

    }

    public function booking_db(Request $request) {
         try {
            $this->validate($request,[
                'name'=>'required',
                'number'=>'required',
                'model_name'=>'required',
                'sale_exicutive'=>'required',
                'store_name'=>'required',
            ],[
                'name.required'=> 'This field is required.', 
                'number.required'=> 'This field is required.',
                'model_name.required'=> 'This field is required.',
                'sale_exicutive.required'=> 'This field is required.',
                'store_name.required'=> 'This field is required.',
            ]);
            DB::beginTransaction();
            $no = "BOOK-";
            $book_no = CustomHelpers::generateBookNo($no);

            if (!empty($request->input('model_name')) && empty($request->input('model_variant')) && empty($request->input('prod_name')) ) {
                $product_id = ProductModel::where('model_name',$request->input('model_name'))
                                ->select('id')->get()->toArray();
                $getqty = Stock::whereIn('product_id',$product_id)->where('store_id',$request->input('store_name'))
                    ->select(
                        'stock.product_id',
                        'stock.booking_qty',
                        DB::raw('IFNULL(max(stock.quantity),0) as quantity')
                    )->get()->first()->toArray();
                    //print_r($getqty);die();
                    $prodId = $getqty['product_id']; 
                    $prod_name = NULL;
                    $model_variant = NULL;                    
                  
               if($getqty <= 0)
               {
                    return back()->with('error','Quantity is not available')->withInput();
               }
            }

            if (!empty($request->input('model_name')) && !empty($request->input('model_variant')) && empty($request->input('prod_name')) ) {
                $product_id = ProductModel::where('model_name',$request->input('model_name'))->where('model_variant',$request->input('model_variant'))->select('id')->get()->toArray();
            
                $getqty = Stock::whereIn('product_id',$product_id)->where('store_id',$request->input('store_name'))
                    ->select(
                        'stock.product_id',
                        'stock.booking_qty',
                        DB::raw('IFNULL(max(stock.quantity),0) as quantity')
                    )->get()->first()->toArray();
                   // print_r($getqty);die();
                    $prodId = $getqty['product_id']; 
                    $prod_name = NULL;
                    $model_variant = NULL;                    
                  
               if($getqty <= 0)
               {
                    return back()->with('error','Quantity is not available')->withInput();
               }
            }
            
            if ($request->input('prod_name')) {
                $prod_name = ProductModel::where('id',$request->input('prod_name'))->get('color_code')->first();
                $prodId = $request->input('prod_name');
                $prod_name = $prod_name['color_code'];
                $model_variant = $request->input('model_variant');
            }
            
            $storeId = $request->input('store_name');
            $getqty = Stock::leftJoin('product','product.id','stock.product_id')
                    ->where('product_id',$prodId)->where('stock.quantity','>',0)
                        ->where('store_id',$storeId)
                    ->select(
                        DB::raw("IFNULL(stock.quantity,0) as quantity"),
                        'stock.booking_qty'
                    )->first();
            if (isset($getqty->quantity)) {
                $bookdata = BookingModel::insertGetId(
                [
                    'booking_number' => $book_no,
                    'product_id' => $prodId,
                    'name'=> $request->input('name'),
                    'mobile' => $request->input('number'),
                    'store_id' => $request->input('store_name'),
                    'sale_executive' => $request->input('sale_exicutive'),
                    'model_name' => $request->input('model_name'),
                    'model_variant' => $model_variant,
                    'color_code' => $prod_name,
                ]);

                if ($bookdata) {

                    //   $request = PaymentRequest::insertGetId([
                    //     'booking_id' => $bookdata,
                    //     'type' => 'booking',
                    //     'store_id' => $request->input('store_name'),
                    //     'amount' => 0,
                    //     'status' => 'pending'
                    // ]);

                    // if ($request == NULL) {
                    //     return redirect('/admin/sale/security/amount')->with('error','some error occurred')->withInput();
                    // }
                    $updatestock = Stock::where('product_id',$prodId)->where('store_id',$storeId)->update([
                        'booking_qty' => $getqty->booking_qty+1,
                        'quantity' => $getqty->quantity-1
                    ]);

                     if ($bookdata != NULL ) {
                        $calldate = date('Y-m-d');
                        $type = 'sale_booking';
                        $calltype = 'booking';
                        $next_call_date = date('Y-m-d', strtotime('+1 day', strtotime($calldate)));
                        $redirect = CustomHelpers::CreateCalling($type,$calltype,$storeId,$bookdata,$next_call_date);
                        
                            if ($redirect == NULL) {
                               DB::rollback();
                                return back()->with('error','Something went wrong.')->withInput();
                            }
                       }
                }
                if(empty($updatestock) || empty($bookdata)) {
                    DB::rollback();
                    return redirect('/admin/booking/')->with('error','some error occurred')->withInput();
                    } 

                
            }else{
                DB::rollback();
                return redirect('/admin/booking/')->with('error','This Product is not an Stock, Please Choose another Product.')->withInput();
            }

        }  catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return redirect('/admin/booking/')->with('error','some error occurred'.$ex->getMessage());
        }
        /* Add action Log */
        CustomHelpers::userActionLog($action='Create Booking',$bookdata,0);
        DB::commit();
        return redirect('/admin/booking/')->with('success','Booking Successfully added .');
    }

    public function booking_list() {
         $data = array(
            'layout' => 'layouts.main'
        );
         return view('admin.booking.booking_list',$data);
    }

    public function booking_list_api(Request $request) {
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
        
      $api_data= BookingModel::leftJoin('store','booking.store_id','store.id')
            ->leftJoin('users','users.id',DB::raw(Auth::id()))
            ->leftJoin('payment',function($join){
                $join->on(DB::raw('payment.booking_id'),'=','booking.id') 
                ->where('payment.status','<>','cancelled');
            })
            ->select(
                'booking.id',
                'booking.booking_number',
                'booking.name',
                 DB::raw('concat(store.name,"-",store.store_type) as store_name'),
                'booking.mobile',
                'booking.status',
                DB::raw('IFNULL(payment.type,"booking") as pay_type'),
                DB::raw('sum(payment.amount) as amount'),
                'users.user_type',
                'users.role'
            )
            -> groupBy('booking.id');
            $api_data->whereIn('booking.store_id',explode(',',Auth::user()->store_id));
            // if(Auth::user()->user_type != 'superadmin' && Auth::user()->role != 'Superadmin')
            // {
            //     $api_data->whereIn('booking.store_id',explode(',',Auth::user()->store_id));
            // }
            if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('booking.name','like',"%".$serach_value."%")
                    ->orwhere('store.name','like',"%".$serach_value."%")
                    ->orwhere('booking.mobile','like',"%".$serach_value."%")                    
                    ->orwhere('booking.status','like',"%".$serach_value."%")                    
                    ->orwhere('booking.booking_number','like',"%".$serach_value."%")                    
                    ;
                });
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                'booking.id',
                'booking.name',
                'store.name',
                'booking.mobile',
                'booking.status',
                'booking.booking_number',
                
                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('booking.id','desc');      
        
        $count = count($api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

    public function booking_pay($id) {
        $bookingData = BookingModel::where("id",$id)->where('status','booked')->get(); 
        $paid = Payment::where('booking_id',$id)->where('type','booking')->sum('amount');
        $pay_mode = Master::where('type','payment_mode')->get();

        if(!isset($bookingData[0])) {
            return redirect('/admin/booking/list')->with('error','Error, Please Check Booking Number');
        }
        $data = array(
            'bookingData' => $bookingData, 
            'paid' => $paid,
            'pay_mode'  =>  $pay_mode,
            'layout' => 'layouts.main'
        );
        return view('admin.booking.booking_pay',$data);
    }

    public function booking_pay_db(Request $request) {
         try {
            $timestamp = date('Y-m-d G:i:s');
            $this->validate($request,[
                'amount'=>'required',
                'payment_mode'=>'required',
                'transaction_number'=>'required|unique:payment,transaction_number',
                'transaction_charges'=>'required',
                'pay_date'=>'required',
                'receiver_bank_detail'=>'required_unless:payment_mode,Cash'
            ],[
                'amount.required'=> 'This field is required.', 
                'payment_mode.required'=> 'This field is required.', 
                'transaction_number.required'=> 'This field is required.', 
                'transaction_charges.required'=> 'This field is required.', 
                'pay_date.required'=> 'This field is required.', 
                'receiver_bank_detail.required_unless'=> 'This field is required.', 
            ]);
                $arr = [];
                if(in_array($request->input('payment_mode'),CustomHelpers::pay_mode_received())) {
                        $arr = [
                            'status'    =>  'received'
                        ];
                }
                $paydata = Payment::insertGetId(
                array_merge($arr,[
                    'booking_id'=> $request->input('booking_id'),
                    'type' => 'booking',
                    'payment_mode' => $request->input('payment_mode'),
                    'transaction_number' => $request->input('transaction_number'),
                    'transaction_charges' => $request->input('transaction_charges'),
                    'receiver_bank_detail' => $request->input('receiver_bank_detail'),
                    'amount' => $request->input('amount'),
                    'store_id' => $request->input('store_id'),
                    'payment_date'  =>  $request->input('pay_date')
                ])
                );
                if($paydata == NULL) {
                       return redirect('/admin/booking/pay/'.$request->input('booking_id'))->with('error','some error occurred'.$ex->getMessage());
                } else{

                    /* Add action Log */
                   CustomHelpers::userActionLog($action='Add Pay Booking Amount',$paydata,0);
                      return redirect('/admin/booking/pay/'.$request->input('booking_id'))->with('success','Amount Successfully Paid .');
                }
               
        
        }  catch(\Illuminate\Database\QueryException $ex) {
            return redirect('/admin/booking/pay/'.$request->input('booking_id'))->with('error','some error occurred'.$ex->getMessage());
        }
    }

    public function booking_pay_detail($id) {
        $bookingData = BookingModel::where('id',$id)->get();
        $payData = Payment::where('booking_id',$id)->where('type','booking')->get();
        $data = array(
            'bookingData' => $bookingData,
            'payData' => $payData,
            'layout' => 'layouts.main'
        );
         return view('admin.booking.booking_paymet_detail',$data);
    }

    public function bookingCancel(Request $request) {
        $bookingId = $request->input('BookingId');
        $desc = $request->input('desc');
        $date = $request->input('date');
        try{
            DB::beginTransaction();
            $checkBooking = $this->checkBooking($bookingId);
            if($checkBooking)
            {
                $checkStore = CustomHelpers::CheckAuthStore($checkBooking['store_id']);
                if ($checkStore) {
                    $checkall = $this->checkall($bookingId);
                    if($checkall)
                    {
                        
                        if($checkall['booking_status'] == 'cancelled') {
                            return 'Info, This Booking Already Cancelled';
                        } if($checkall['payment_type'] == 'booking' || empty($checkall['payment_type'])) {
                                // direct booking cancel
                                $bookingCancel = $this->bookingCancelDirect($bookingId,$desc,$date);
                                if($bookingCancel != 'success')
                                {
                                    DB::rollBack();
                                    return $bookingCancel;
                                }
                                /* Add action Log */
                                CustomHelpers::userActionLog($action='Cancel Booking',$bookingId);
                                DB::commit();
                                return $bookingCancel;
                        }
                        elseif($checkall['payment_type'] == 'sale'){
                            return 'This Booking is Already in Sale so, will not be Cancelled on Booking.';
                        }else{
                           return 'Info, This Booking Not Cancelled';  
                        }
                    }
                    else{
                        DB::rollback();
                        return 'Data Not-Found for This Booking Number';
                    }
                }else{
                    return 'You Are Not Authorized For Cancel This Booking';
                }
            }else{
                return 'This Booking number was not found';
            }
           
        }
        catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return 'some error occurred'.$ex->getMessage();
        }
        return 'Something Went Wrong';
    }
    public function checkBooking($bookingId)
    {
        $data =  BookingModel::where('id',$bookingId)->get()->first();
        if($data)
        {
            return $data;
        }
        else
        {
            return 0;
        }
    }
    public function checkall($bookingId)
    {
        $check = BookingModel::leftJoin('payment',DB::raw('payment.booking_id'),'booking.id')
                        ->where('booking.id',$bookingId)
                        ->where('booking.status','booked')
                        ->select('booking.id',
                                'booking.status as booking_status',
                                'payment.id as payment_id',
                                'payment.type as payment_type'
                            )
                        ->first();
        if($check)
        {
            return $check->toArray();
        }
        else
        {
            return 0;
        }
    }
    public function bookingCancelDirect($bookingId,$desc,$datetime)
    {
        try{
            $date1 = explode(' ',$datetime);
            $date = $date1[0];
            $arr = [
                    'request_id'    =>  'Booking-'.$bookingId.'-'.$date,
                    'cancel_type'    =>  'booking',
                    'type_id'   =>  $bookingId,
                    'desc'  =>  $desc,
                    'user_id'   =>  Auth::Id(),
                    'request_date'  =>  $datetime,
                    'type'  =>  'auto',
                    'status'    =>  'approve'
            ];
            $insertId = CancelRequest::insertGetId($arr);
            $updateBooking = BookingModel::where('id',$bookingId)->update(['status'=>'cancelled']);
            if($insertId && $updateBooking)
            {
                /* Add action Log */
                   CustomHelpers::userActionLog($action='Cancel Booking Request',$insertId,0);
                return 'success';
            }
            else
            {
                return 'error';
            }
        }  catch(\Illuminate\Database\QueryException $ex) {
            return 'some error occurred'.$ex->getMessage();
        }
        
    }
    // cancel booking request listing
    public function cancelBooking_list() {
        return view('admin.booking.cancelBooking_list',['layout' => 'layouts.main']);
   }

   public function cancelBooking_list_api(Request $request) {
       $search = $request->input('search');
       $serach_value = $search['value'];
       $start = $request->input('start');
       $limit = $request->input('length');
       $offset = empty($start) ? 0 : $start ;
       $limit =  empty($limit) ? 10 : $limit ;
        $api_data= CancelRequest::leftJoin('booking',DB::raw('booking.status = "cancelled" and booking.id'),'=','cancel_request.type_id')
                ->where('cancel_request.cancel_type' , "booking")
                ->leftJoin('users','users.id','cancel_request.user_id')
                ->leftJoin('payment as paymentIn',function($join){
                    $join->on(DB::raw('paymentIn.type = "booking" and paymentIn.booking_id'),'=','booking.id');
                })
                ->leftJoin('users as user','user.id',DB::raw(Auth::id()))
           ->select(
               'cancel_request.id',
            //    DB::raw("group_concat(cancel_request.id SEPARATOR ',') as all_cancel_id"),
               'booking.id as bookingId',
               'booking.status',
               'booking.booking_number',
               'cancel_request.request_id as req_num',
               'cancel_request.desc as reason',
               'cancel_request.request_date as req_date',
               'cancel_request.status as cancel_status',
               'cancel_request.type as cancel_type',
               'users.name',
                DB::raw('IFNULL(sum(paymentIn.amount),0) as payIn'),
                'users.user_type',
                'users.role'
                // DB::raw('sum("paymentOut.amount") as payOut')
           )
           -> groupBy('booking.id');
           if(!empty($serach_value))
           {
               $api_data->where(function($query) use ($serach_value){
                   $query->where('cancel_request.request_id','like',"%".$serach_value."%")
                   ->orwhere('booking.booking_number','like',"%".$serach_value."%")
                   ->orwhere('cancel_request.desc','like',"%".$serach_value."%")
                   ->orwhere('users.name','like',"%".$serach_value."%")
                   ->orwhere('cancel_request.request_date','like',"%".$serach_value."%")
                   ->orwhere('booking.status','like',"%".$serach_value."%")                    
                   ;
               });
           }
           if(isset($request->input('order')[0]['column']))
           {
               $data = [
               'cancel_request.request_id',
               'booking.booking_number',
               'cancel_request.desc',
               'users.name',
               'cancel_request.request_date',
               'payment.amount',
               'payment.amount',
               'booking.status'
               ];
               $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
               $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
           }
           else
               $api_data->orderBy('cancel_request.request_date','desc');      
       
       $count = count( $api_data->get()->toArray());
       $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
       foreach($api_data as $key => $val)
       {
           $cancel_id = $val['id'];
           $refund_amount = Payment::where('type','bookingRefund')
                            ->where('type_id',$cancel_id)
                            ->sum('amount');
            $api_data[$key]['payOut'] =  $refund_amount;  
       }

       $array['recordsTotal'] = $count;
       $array['recordsFiltered'] = $count;
       $array['data'] = $api_data; 
       return json_encode($array);
   }
   //cancel booking refund page
   public function refundMoney($bookingId)
   {
        $bookingData = BookingModel::where("booking.id",$bookingId)->where('booking.status','cancelled')
                        ->leftJoin('cancel_request',DB::raw('cancel_request.cancel_type = "booking" and booking.id'),'=','cancel_request.type_id')
                        // ->leftJoin('payment','payment.booking_id','booking.id')
                        ->leftJoin('payment',function($join){
                            $join->on(DB::raw('payment.booking_id'),'=','booking.id')
                                ->where('payment.type','booking');
                        })
                        ->select('cancel_request.id as req_id',
                            'cancel_request.request_id as req_num',
                            DB::raw('sum(payment.amount) as paid_amount'),
                            'booking.*')->first(); 
        $paid = Payment::where('type','bookingRefund')
                        ->where('type_id',$bookingData->req_id)
                        ->get();
        $refunded_amount = Payment::where('type','bookingRefund')
                        ->where('type_id',$bookingData->req_id)
                        ->sum('amount');
                        
        $refund_amount = Payment::where('type','booking')
                        ->where('booking_id',$bookingId)
                        ->where('status','received')
                        ->sum('amount');                        
                        $pay_mode = Master::where('type','payment_mode')->get();
        $data = array(
            'bookingData' => $bookingData, 
            'paid' => $paid,
            'refund_amount' =>  $refund_amount,
            'refunded_amount' =>  $refunded_amount,
            'pay_mode'  =>  $pay_mode,
            'layout' => 'layouts.main'
        );
        return view('admin.booking.refundPay',$data);

   }
   public function refundMoneyDB(Request $request)
   {
    try {
        $timestamp = date('Y-m-d G:i:s');
        $this->validate($request,[
            'amount'=>'required',
            'payment_mode'=>'required',
            'transaction_number'=> 'required|unique:payment,transaction_number',
            'transaction_charges'=>'required',
            'receiver_bank_detail'=>'required_unless:payment_mode,Cash',
             'pay_date'  =>  'required'
        ],[
            'amount.required'=> 'This field is required.', 
            'payment_mode.required'=> 'This field is required.', 
            'transaction_number.required'=> 'This field is required.', 
            'transaction_charges.required'=> 'This field is required.', 
            'receiver_bank_detail.required'=> 'This field is required.', 
            'pay_date.required'=> 'This field is required.', 
        ]);
     
        $total_amount = Payment::where('booking_id',$request->input('booking_id'))->where('type','booking')->where('status','received')->sum('amount');
        
        $paid_amount = Payment::where('type_id',$request->input('requested_id'))
                                ->where('type','bookingRefund')->sum('amount');
        if ($total_amount < $paid_amount+intval($request->input('amount'))) {
            return redirect('/admin/cancelBooking/refundPage/'.$request->input('booking_id'))->with('error','Your Amount too more .')->withInput();
        }else if ($request->input('amount') == 0) {
            return redirect('/admin/cancelBooking/refundPage/'.$request->input('booking_id'))->with('error',' Your amount should be greater than 0 .')->withInput();
        } else{

            $arr = [];                 
                if(in_array($request->input('payment_mode'),CustomHelpers::pay_mode_received())) {
                }else{
                        $arr = [
                            'receiver_bank_detail' => $request->input('receiver_bank_detail')
                        ];
                }

            $paydata = array_merge($arr,[
                'type' => 'bookingRefund',
                'type_id' => $request->input('requested_id'),
                'payment_mode' => $request->input('payment_mode'),
                'transaction_number' => $request->input('transaction_number'),
                'transaction_charges' => $request->input('transaction_charges'),
                'amount' => $request->input('amount'),
                'store_id' => $request->input('store_id'),
                'payment_date'  =>  $request->input('pay_date'),
                'payment_type'  =>  'Refund',
                'status'    =>  'received'
            ]);
            
            $AddData = Payment::insertGetId($paydata);
            if($AddData == NULL) {
                   return redirect('/admin/cancelBooking/refundPage/'.$request->input('booking_id'))->with('error','some error occurred')->withInput();
            } else{
                if($total_amount == $paid_amount+intval($request->input('amount')))
                {   
                     /* Add action Log */
                    CustomHelpers::userActionLog($action='Add Refund Money',$AddData,0);
                    return redirect('/admin/cancel/booking/list')->with('success','Money has been Successfully refunded');
                }
                return redirect('/admin/cancelBooking/refundPage/'.$request->input('booking_id'))->with('success','Amount Successfully Paid .');
            }

        }
        
    }  catch(\Illuminate\Database\QueryException $ex) {
        return redirect('/admin/cancelBooking/refundPage/'.$request->input('booking_id'))->with('error','some error occurred'.$ex->getMessage());
    }
   }
   public function refundRequestMoneyDB(Request $request)
   {
    try {
        $timestamp=date('Y-m-d G:i:s');
        $this->validate($request,[
            'acc_num'=>'required|numeric',
            'acc_name'=>'required',
            'ifsc'=>'required',
        ],[
            'acc_num.required'=> 'This field is required.', 
            'acc_num.numeric'=> 'This field contain only only numeric.', 
            'acc_name.required'=> 'This field is required.', 
            'ifsc.required'=> 'This field is required.', 
        ]);

        $total_amount = Payment::where('booking',$request->input('booking_id'))->where('type','booking')->sum('amount');

        $paid_amount = Payment::where('type_id',$request->input('requested_id'))
                                ->where('type','bookingRefund')->sum('amount');

        if ($total_amount < $paid_amount+intval($request->input('req_money'))) {
            return redirect('/admin/cancelBooking/refundPage/'.$request->input('booking_id'))->with('error','Your Requested Amount too more.')->withInput();
        }else{
            $paydata = RefundRequest::insertGetId(
            [
                'cancel_req_id' => $request->input('requested_id'),
                'req_money' => $request->input('req_money'),
                'account_num' => $request->input('acc_num'),
                'ifsc' => $request->input('ifsc'),
                'acc_name' => $request->input('acc_name')
            ]
            );
            if($paydata == NULL) {
                   return redirect('/admin/cancelBooking/refundPage/'.$request->input('booking_id'))->with('error','some error occurred')->withInput();
            } else{
                 /* Add action Log */
                 CustomHelpers::userActionLog($action='Add Refund Money Request',$paydata,0);
                return redirect('/admin/cancelBooking/refundPage/'.$request->input('booking_id'))->with('success','Amount Successfully Requested .');
            }

        }
        
    }  catch(\Illuminate\Database\QueryException $ex) {
        return redirect('/admin/cancelBooking/refundPage/'.$request->input('booking_id'))->with('error','some error occurred'.$ex->getMessage());
    }
   }


}
/*
try{
    }catch(\Illuminate\Database\QueryException $ex) {
            return back()->with('error','Something went wrong.'.$ex->getMessage())->withInput();
        }