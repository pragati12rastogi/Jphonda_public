<?php
namespace App\Http\Controllers\admin;

use App\Model\IncentiveProgram;
use App\Model\Incentive;
use \App\Model\Sale;
use \App\Model\OtcSale;
use \App\Model\BestDealSale;
use \App\Model\IncentiveDetail;
use\App\Model\ServiceModel;
use\App\Model\Job_card;
use\App\Model\Store;
use\App\Model\FinanceCompany;
use\App\Model\Master;
use\App\Model\InsuranceRenewalOrder;
use\App\Model\RtoModel;

use \App\Custom\CustomHelpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Auth;


class IncentiveController extends Controller
{

	public function incentive($incentive_type,Request $request)
	{
        
        $store = Store::whereIn('id',CustomHelpers::user_store())
                    ->select('id',DB::raw('concat(store.name,"-",store.store_type) as name'))->get();

        $finance_company_name = FinanceCompany::orderBy('id','ASC')->get();

        $job_card_type = Master::where('type','job_card_type')
                        ->select('key','value')->get();

		$data = array('layout'=>'layouts.main',
            'store'=>$store,
            'finance_company'=>$finance_company_name,
            'job_card_type' => $job_card_type);

        if($incentive_type == '' || $incentive_type == null || $incentive_type == 'sale' || $incentive_type == '*'){
            return view('admin.incentive.incentive_program',$data);
        }elseif($incentive_type == 'bestdeal'){
            return view('admin.incentive.incentive_best_deal',$data);
        }elseif($incentive_type == 'otcsale'){
            return view('admin.incentive.incentive_otc_sale',$data);
        }elseif($incentive_type == 'service'){
            return view('admin.incentive.incentive_service',$data);
        }elseif($incentive_type == 'insurance'){
            return view('admin.incentive.incentive_insurance',$data);
        }elseif($incentive_type == 'hsrp'){
            return view('admin.incentive.incentive_hsrp',$data);
        }
		      
	}

	public function incentive_DB($incentive_type,Request $request) {

        $val_arr = [];
        $val_msg =[];

        $val_arr['cat_type']='required';
        $val_msg['cat_type.required']='This is required';

        $val_arr['store_id']='required';
        $val_msg['store_id.required']='This is required';

        $category_type = $request->input('cat_type');

        if($category_type == 'sale'){
            $val_arr['saleparameter.*']='required';
            $val_arr['salecondition.*']='required';
            $val_arr['salevalue.*']='required';
            $val_arr['sale_incent_type.*']='required';
            $val_arr['salepercentage.*']='required';
            $val_arr['sale_from_date']='required';
            $val_arr['sale_to_date']='required';
            $val_arr['sale_incent_calc']='required';
            // $val_arr['sale_store']='required';
            // $val_arr['sale_store_qty']='required';

            $val_msg['saleparameter.*.required']='This is required';
            $val_msg['salecondition.*.required']='This is required';
            $val_msg['salevalue.*.required']='This is required';
            $val_msg['sale_incent_type.*.required']='This is required';
            $val_msg['salepercentage.*.required']='This is required';
            $val_msg['sale_from_date.required']='This is required';
            $val_msg['sale_to_date.required']='This is required';
            $val_msg['sale_incent_calc.required']='This is required';
            // $val_msg['sale_store.required']='This is required';
            // $val_msg['sale_store_qty.required']='This is required';

        }elseif($category_type == 'bestdeal'){
            $val_arr['bestdealparameter.*']='required';
            $val_arr['bestdealcondition.*']='required';
            $val_arr['bestdealvalue.*']='required';
            $val_arr['bestdeal_incent_type.*']='required';
            $val_arr['bestdeal_percentage.*']='required';
            $val_arr['best_from_date']='required';
            $val_arr['best_to_date']='required';
            $val_arr['bestdeal_incent_calc']='required';
            // $val_arr['best_store']='required';
            // $val_arr['best_store_qty']='required';

            $val_msg['bestdealparameter.*.required']='This is required';
            $val_msg['bestdealcondition.*.required']='This is required';
            $val_msg['bestdealvalue.*.required']='This is required';
            $val_msg['bestdeal_incent_type.*.required']='This is required';
            $val_msg['bestdeal_percentage.*.required']='This is required';
            $val_msg['best_from_date.required']='This is required';
            $val_msg['best_to_date.required']='This is required';
            $val_msg['bestdeal_incent_calc.required']='This is required';
            

        }elseif($category_type == 'otc_sale'){

            $val_arr['otcparameter.*']='required';
            $val_arr['otccondition.*']='required';
            $val_arr['otcvalue.*']='required';
            $val_arr['otc_incent_type.*']='required';
            $val_arr['otc_percentage.*']='required';
            $val_arr['otc_from_date']='required';
            $val_arr['otc_to_date']='required';
            $val_arr['otc_incent_calc']='required';
            // $val_arr['otc_store']='required';
            // $val_arr['otc_store_qty']='required';

            $val_msg['otcparameter.*.required']='This is required';
            $val_msg['otccondition.*.required']='This is required';
            $val_msg['otcvalue.*.required']='This is required';
            $val_msg['otc_incent_type.*.required']='This is required';
            $val_msg['otc_percentage.*.required']='This is required';
            $val_msg['otc_from_date.required']='This is required';
            $val_msg['otc_to_date.required']='This is required';
            $val_msg['otc_incent_calc.required']='This is required';
            // $val_msg['otc_store.required']='This is required';
            // $val_msg['otc_store_qty.required']='This is required';

        }elseif($category_type == 'service' || $category_type == 'insurance'|| $category_type == 'hsrp'){

            $val_arr['serviceparameter.*']='required';
            $val_arr['servicecondition.*']='required';
            $val_arr['servicevalue.*']='required';
            $val_arr['service_incent_type.*']='required';
            $val_arr['service_percentage.*']='required';
            $val_arr['service_from_date']='required';
            $val_arr['service_to_date']='required';
            $val_arr['service_incent_calc']='required';
            // $val_arr['service_store']='required';
            // $val_arr['service_store_qty']='required';

            $val_msg['serviceparameter.*.required']='This is required';
            $val_msg['servicecondition.*.required']='This is required';
            $val_msg['servicevalue.*.required']='This is required';
            $val_msg['service_incent_type.*.required']='This is required';
            $val_msg['service_percentage.*.required']='This is required';
            $val_msg['service_from_date.required']='This is required';
            $val_msg['service_to_date.required']='This is required';
            $val_msg['service_incent_calc.required']='This is required';
            // $val_msg['service_store.required']='This is required';
            // $val_msg['service_store_qty.required']='This is required';
        }
        
		 $validator = Validator::make($request->all(),$val_arr,$val_msg
        );

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

		 try {

            $table = ['incentive_program','incentive_program_detail'];
            CustomHelpers::resetIncrement($table);
            DB::beginTransaction();

            $all_type = ['sale','bestdeal','otc_sale','service','insurance','hsrp'];

            if($category_type == 'sale'){
    		 	if (!empty($request->input('saleparameter')) && count($request->input('saleparameter'))> 0) {
    		 		$sid = $request->input('saleparameter');
                    $scount = count($sid);
                    // $sale_array = [];

                    $insert_sale_incentive = IncentiveProgram::insertGetId([
                                'type'=>$all_type[0],
                                'start_date' =>date('Y-m-d',strtotime($request->input('sale_from_date'))),
                                'end_date'=>date('Y-m-d',strtotime($request->input('sale_to_date'))),
                                'required_condition' => $request->input('sale_incent_calc'),
                                'store_id'=> $request->input('store_id'),
                                'incentive_store'=>($request->input('sale_store') != null)?implode(',', $request->input('sale_store')):null,
                                'required_qty'=>$request->input('sale_store_qty'),
                                'created_by' => Auth::id()
                            ]);

                    if($insert_sale_incentive == 0){
                        DB::rollback();
                        return back()->with('error','Something Went Wrong')->withInput();
                    }

    		 		for($i = 0 ; $i < $scount ; $i++)
                        {
                        	 $saleparameter=$request->input('saleparameter')[$i];
                        	 $salecondition=$request->input('salecondition')[$i];
                        	 $salevalue=$request->input('salevalue')[$i];
                             $s_incent_percent = $request->input('salepercentage')[$i];
                             $s_incent_type = $request->input('sale_incent_type')[$i];

                             /*$sale_para = array($saleparameter,$salecondition, $salevalue);
                             $sale_array[$i] = $sale_para;*/
                             $insert_detail = IncentiveDetail::insertGetId([
                                'incentive_program_id'=>$insert_sale_incentive,
                                'parameter' =>$saleparameter,
                                'incentive_condition'=>$salecondition,
                                'value'=>$salevalue,
                                'incentive_type'=>$s_incent_type,
                                'incentive_value'=>$s_incent_percent,
                                'created_by' => Auth::id()
                            ]);

                            if($insert_detail == 0){
                                DB::rollback();
                                return back()->with('error','Something Went Wrong')->withInput();
                            }

                        }      
    		 	}
            }elseif ($category_type == 'bestdeal') {
                if (!empty($request->input('bestdealparameter')) && count($request->input('bestdealparameter'))> 0) {
                    $bds = $request->input('bestdealparameter');
                    
                    $bcount = count($bds);
                    $bestdeal_array =[];

                    $insert_best_incentive = IncentiveProgram::insertGetId([
                                'type'=>$all_type[1],
                                'start_date' =>date('Y-m-d',strtotime($request->input('best_from_date'))),
                                'end_date'=>date('Y-m-d',strtotime($request->input('best_to_date'))),
                                'required_condition' => $request->input('bestdeal_incent_calc'),
                                'store_id'=> $request->input('store_id'),
                                'incentive_store'=>($request->input('best_store')!=null)?implode(',', $request->input('best_store')):null,
                                'required_qty'=>$request->input('best_store_qty'),
                                'created_by' => Auth::id()
                            ]);

                    if($insert_best_incentive == 0){
                        DB::rollback();
                        return back()->with('error','Something Went Wrong')->withInput();
                    }
                    for($j = 0 ; $j < $bcount ; $j++)
                    {
                         $bestdealparameter=$request->input('bestdealparameter')[$j];
                         $bestdealcondition=$request->input('bestdealcondition')[$j];
                         $bestdealvalue=$request->input('bestdealvalue')[$j];
                         $bds_incent_percent = $request->input('bestdeal_percentage')[$j];
                         $bds_incent_type = $request->input('bestdeal_incent_type')[$j];

                         $insert_detail = IncentiveDetail::insertGetId([
                                'incentive_program_id'=>$insert_best_incentive,
                                'parameter' =>$bestdealparameter,
                                'incentive_condition'=>$bestdealcondition,
                                'value'=>$bestdealvalue,
                                'incentive_type'=>$bds_incent_type,
                                'incentive_value'=>$bds_incent_percent,
                                'created_by' => Auth::id()
                            ]);

                            if($insert_detail == 0){
                                DB::rollback();
                                return back()->with('error','Something Went Wrong')->withInput();
                            }
                         
                    }
                }
            }elseif($category_type == 'otc_sale'){
                if (!empty($request->input('otcparameter')) && count($request->input('otcparameter'))> 0) {
                    $ods = $request->input('otcparameter');
                    
                    $ocount = count($ods);
                    $otc_array =[];

                    $insert_otc_incentive = IncentiveProgram::insertGetId([
                        'type'=>$all_type[2],
                        'start_date' =>date('Y-m-d',strtotime($request->input('otc_from_date'))),
                        'end_date'=>date('Y-m-d',strtotime($request->input('otc_to_date'))),
                        'required_condition' => $request->input('otc_incent_calc'),
                        'store_id'=> $request->input('store_id'),
                        'incentive_store'=>($request->input('otc_store')!=null)?implode(',', $request->input('otc_store')):null,
                        'required_qty'=>$request->input('otc_store_qty'),
                        'created_by' => Auth::id()
                    ]);

                    if($insert_otc_incentive == 0){
                        DB::rollback();
                        return back()->with('error','Something Went Wrong')->withInput();
                    }
                     for($k = 0 ; $k < $ocount ; $k++)
                        {
                             $otcparameter=$request->input('otcparameter')[$k];
                             $otccondition=$request->input('otccondition')[$k];
                             $otcvalue=$request->input('otcvalue')[$k];
                             $ods_incent_type = $request->input('otc_incent_type')[$k];
                             $ods_incent_percent = $request->input('otc_percentage')[$k];

                             $insert_detail = IncentiveDetail::insertGetId([
                                'incentive_program_id'=>$insert_otc_incentive,
                                'parameter' =>$otcparameter,
                                'incentive_condition'=>$otccondition,
                                'value'=>$otcvalue,
                                'incentive_type'=>$ods_incent_type,
                                'incentive_value'=>$ods_incent_percent,
                                'created_by' => Auth::id()
                            ]);

                            if($insert_detail == 0){
                                DB::rollback();
                                return back()->with('error','Something Went Wrong')->withInput();
                            }
                        }
                        
                }
            }elseif($category_type == 'service'){
                if (!empty($request->input('serviceparameter')) && count($request->input('serviceparameter'))> 0) {
                    $sds = $request->input('serviceparameter');
                    
                    $scount = count($sds);
                    $service_array = [];

                    $insert_service_incentive = IncentiveProgram::insertGetId([
                        'type'=>$all_type[3],
                        'start_date' =>date('Y-m-d',strtotime($request->input('service_from_date'))),
                        'end_date'=>date('Y-m-d',strtotime($request->input('service_to_date'))),
                        'required_condition' => $request->input('service_incent_calc'),
                        'store_id'=> $request->input('store_id'),
                        'incentive_store'=>($request->input('service_store') != null)?implode(',', $request->input('service_store')):null,
                        'required_qty'=>$request->input('service_store_qty'),
                        'created_by' => Auth::id()
                    ]);

                    if($insert_service_incentive == 0){
                        DB::rollback();
                        return back()->with('error','Something Went Wrong')->withInput();
                    }
                     for($l = 0 ; $l < $scount ; $l++)
                        {
                             $serviceparameter=$request->input('serviceparameter')[$l];
                             $servicecondition=$request->input('servicecondition')[$l];
                             $servicevalue=$request->input('servicevalue')[$l];
                             $sds_incent_percent = $request->input('service_percentage')[$l];
                             $sds_incent_type = $request->input('service_incent_type')[$l];
                             
                             $insert_detail = IncentiveDetail::insertGetId([
                                'incentive_program_id'=>$insert_service_incentive,
                                'parameter' =>$serviceparameter,
                                'incentive_condition'=>$servicecondition,
                                'value'=>$servicevalue,
                                'incentive_type'=>$sds_incent_type,
                                'incentive_value'=>$sds_incent_percent,
                                'created_by' => Auth::id()
                            ]);

                            if($insert_detail == 0){
                                DB::rollback();
                                return back()->with('error','Something Went Wrong')->withInput();
                            }
                        }
                        
                }
            }elseif($category_type == 'insurance'){
                if (!empty($request->input('serviceparameter')) && count($request->input('serviceparameter'))> 0) {
                    $sds = $request->input('serviceparameter');
                    
                    $scount = count($sds);
                    $service_array = [];

                    $insert_service_incentive = IncentiveProgram::insertGetId([
                        'type'=>$all_type[4],
                        'start_date' =>date('Y-m-d',strtotime($request->input('service_from_date'))),
                        'end_date'=>date('Y-m-d',strtotime($request->input('service_to_date'))),
                        'required_condition' => $request->input('service_incent_calc'),
                        'store_id'=> $request->input('store_id'),
                        'incentive_store'=>($request->input('service_store') != null)?implode(',', $request->input('service_store')):null,
                        'required_qty'=>$request->input('service_store_qty'),
                        'created_by' => Auth::id()
                    ]);

                    if($insert_service_incentive == 0){
                        DB::rollback();
                        return back()->with('error','Something Went Wrong')->withInput();
                    }
                     for($l = 0 ; $l < $scount ; $l++)
                        {
                             $serviceparameter=$request->input('serviceparameter')[$l];
                             $servicecondition=$request->input('servicecondition')[$l];
                             $servicevalue=$request->input('servicevalue')[$l];
                             $sds_incent_percent = $request->input('service_percentage')[$l];
                             $sds_incent_type = $request->input('service_incent_type')[$l];
                             
                             $insert_detail = IncentiveDetail::insertGetId([
                                'incentive_program_id'=>$insert_service_incentive,
                                'parameter' =>$serviceparameter,
                                'incentive_condition'=>$servicecondition,
                                'value'=>$servicevalue,
                                'incentive_type'=>$sds_incent_type,
                                'incentive_value'=>$sds_incent_percent,
                                'created_by' => Auth::id()
                            ]);

                            if($insert_detail == 0){
                                DB::rollback();
                                return back()->with('error','Something Went Wrong')->withInput();
                            }
                        }
                        
                }
            }elseif($category_type == 'hsrp'){
                if (!empty($request->input('serviceparameter')) && count($request->input('serviceparameter'))> 0) {
                    $sds = $request->input('serviceparameter');
                    
                    $scount = count($sds);
                    $service_array = [];

                    $insert_service_incentive = IncentiveProgram::insertGetId([
                        'type'=>$all_type[5],
                        'start_date' =>date('Y-m-d',strtotime($request->input('service_from_date'))),
                        'end_date'=>date('Y-m-d',strtotime($request->input('service_to_date'))),
                        'required_condition' => $request->input('service_incent_calc'),
                        'store_id'=> $request->input('store_id'),
                        'incentive_store'=>($request->input('service_store') != null)?implode(',', $request->input('service_store')):null,
                        'required_qty'=>$request->input('service_store_qty'),
                        'created_by' => Auth::id()
                    ]);

                    if($insert_service_incentive == 0){
                        DB::rollback();
                        return back()->with('error','Something Went Wrong')->withInput();
                    }
                     for($l = 0 ; $l < $scount ; $l++)
                        {
                             $serviceparameter=$request->input('serviceparameter')[$l];
                             $servicecondition=$request->input('servicecondition')[$l];
                             $servicevalue=$request->input('servicevalue')[$l];
                             $sds_incent_percent = $request->input('service_percentage')[$l];
                             $sds_incent_type = $request->input('service_incent_type')[$l];
                             
                             $insert_detail = IncentiveDetail::insertGetId([
                                'incentive_program_id'=>$insert_service_incentive,
                                'parameter' =>$serviceparameter,
                                'incentive_condition'=>$servicecondition,
                                'value'=>$servicevalue,
                                'incentive_type'=>$sds_incent_type,
                                'incentive_value'=>$sds_incent_percent,
                                'created_by' => Auth::id()
                            ]);

                            if($insert_detail == 0){
                                DB::rollback();
                                return back()->with('error','Something Went Wrong')->withInput();
                            }
                        }
                        
                }
            }

		}  catch(\Illuminate\Database\QueryException $ex) {
             return redirect('/admin/admin/incentive/program/'.$incentive_type)->with('error','some error occurred'.$ex->getMessage());
        } 

        DB::commit();
        return redirect('/admin/incentive/program/'.$incentive_type)->with('success','Successfully Submitted.');
	}

    public function add_incentive_cron(){

        $incetive_program = IncentiveProgram::whereRaw('incentive_program.end_date < CURRENT_DATE()')
                            ->where('incentive_program.incentive_status',0)
                            ->get();

        foreach ($incetive_program as $master_key => $master_detail) {

            $incentive_program_detail = IncentiveDetail::where('incentive_program_id',$master_detail['id'])->get();
            $incentive_store_ids =[];
            
            $sale_store_query = Sale::where('sale.status','done')
                            ->whereRaw('sale.sale_date BETWEEN "'.$master_detail['start_date'].'" AND "'.$master_detail['end_date'].'"')
                            ->select('sale.store_id',Db::raw('Count(*) as sale_qty')
                            )->groupBy('sale.store_id');
           
            
            $bestdeal_store_query = BestDealSale::leftjoin('sale','sale.id','best_deal_sale.sale_id')
                            ->where('best_deal_sale.status','Sale')
                            ->where('best_deal_sale.tos','best_deal')
                            ->where('sale.status','done')
                            ->whereRaw('sale.sale_date BETWEEN "'.$master_detail['start_date'].'" AND "'.$master_detail['end_date'].'"')
                            ->select('sale.store_id',Db::raw('Count(*) as sale_qty')
                            )->groupBy('sale.store_id');
            
            $otc_store_query = OtcSale::leftjoin('payment_request',function($join){
                        $join->on("payment_request.type_id","=","otc_sale.id")
                        ->where("payment_request.type","otcsale");
                    })->where('payment_request.status','Done')
                    ->whereRaw('otc_sale.created_at BETWEEN "'.$master_detail['start_date'].'" AND "'.$master_detail['end_date'].'"')
                    ->where('otc_sale.created_by','<>',0)
                    ->select('otc_sale.store_id',Db::raw('Count(*) as otc_qty')
                    )->groupBy('otc_sale.store_id');

          
            $service_store_query = Job_card::whereRaw('job_card.created_at BETWEEN "'.$master_detail['start_date'].'" AND "'.$master_detail['end_date'].'"')
                    ->where('job_card.created_by','<>',0)
                    ->where('job_card.service_status','done')
                    ->select('job_card.store_id',Db::raw('Count(*) as service_qty')
                    )->groupBy('job_card.store_id');

            
            $insurance_store_query= InsuranceRenewalOrder::whereRaw('insurance_renewal_order.created_at BETWEEN "'.$master_detail['start_date'].'" AND "'.$master_detail['end_date'].'"')
                ->where('insurance_renewal_order.created_by','<>',0)
                ->where('insurance_renewal_order.status','Done')
                ->leftjoin('insurance_data','insurance_data.id','insurance_renewal_order.insurance_data_id')
                ->select('insurance_data.store_id',Db::raw('Count(*) as insurance_renewal_qty'))
                ->groupBy('insurance_data.store_id');
            
            $hsrp_store_query = RtoModel::where('rto.main_type','hsrp')
                ->leftjoin('hsrp','rto.type_id','hsrp.id')
                ->where('rto.approve',3)
                ->whereRaw('DATE_FORMAT(hsrp.created_at,"%Y-%m-%d") BETWEEN "'.$master_detail['start_date'].'" AND "'.$master_detail['end_date'].'"')
                ->select('hsrp.store_id',Db::raw('Count(*) as hsrp_qty'))
                ->groupBy('hsrp.store_id');
            
            // not empty mandatory condition satisfy
            if($master_detail['incentive_store'] != null && $master_detail['required_qty'] != null && $master_detail['incentive_store'] != '' && $master_detail['required_qty'] != ''){

                $incent_for_store = explode(',',  $master_detail['incentive_store']);

                if($master_detail['type'] == 'sale'){

                    $sale_count = $sale_store_query->whereIn('sale.store_id',$incent_for_store)
                                ->get()->toArray();

                    foreach ($sale_count as $sale_count_index => $sale_count_value) {
                            
                        if($sale_count_value['sale_qty'] >= $master_detail['required_qty']){
                            $incentive_store_ids[]=$sale_count_value['store_id'];
                        }
                    }
           
                }

                if($master_detail['type'] == 'bestdeal'){

                    $bestdeal_count = $bestdeal_store_query->whereIn('sale.store_id',$incent_for_store)
                            ->get()->toArray();

                    foreach ($bestdeal_count as $best_count_index => $best_count_value) {
                            
                        if($best_count_value['sale_qty'] >= $master_detail['required_qty']){
                            $incentive_store_ids[]=$best_count_value['store_id'];
                        }
                    }
           
                }

                if($master_detail['type'] == 'otc_sale'){

                    $otc_count = $otc_store_query->whereIn('otc_sale.store_id',$incent_for_store)
                            ->get()->toArray();

                    foreach ($otc_count as $otc_count_index => $otc_count_value) {
                            
                        if($otc_count_value['otc_qty'] >= $master_detail['required_qty']){
                            $incentive_store_ids[]=$otc_count_value['store_id'];
                        }
                    }
           
                }

                if($master_detail['type'] == 'service'){

                    $service_count = $service_store_query->whereIn('job_card.store_id',$incent_for_store)
                            ->get()->toArray();

                    foreach ($service_count as $service_count_index => $service_count_value) {
                            
                        if($service_count_value['service_qty'] >= $master_detail['required_qty']){
                            $incentive_store_ids[]=$service_count_value['store_id'];
                        }
                    }
                }

                if($master_detail['type'] == 'insurance'){

                    $insurance_count = $insurance_store_query->whereIn('insurance_data.store_id',$incent_for_store)
                            ->get()->toArray();

                    foreach ($insurance_count as $insurance_count_index => $insurance_count_value) {
                            
                        if($insurance_count_value['insurance_renewal_qty'] >= $master_detail['required_qty']){
                            $incentive_store_ids[]=$insurance_count_value['store_id'];
                        }
                    }
                }

                if($master_detail['type'] == 'hsrp'){

                    $hsrp_count = $hsrp_store_query->whereIn('hsrp.store_id',$incent_for_store)
                            ->get()->toArray();

                    foreach ($hsrp_count as $hsrp_count_index => $hsrp_count_value) {
                            
                        if($hsrp_count_value['hsrp_qty'] >= $master_detail['required_qty']){
                            $incentive_store_ids[]=$hsrp_count_value['store_id'];
                        }
                    }
                }


            }else{ 

                $type_count = [];
                // for no mandatory
                if($master_detail['type'] == 'sale'){

                    $type_count = $sale_store_query->get()->toArray();
           
                }

                if($master_detail['type'] == 'bestdeal'){

                    $type_count = $bestdeal_store_query->get()->toArray();
           
                }

                if($master_detail['type'] == 'otc_sale'){

                    $type_count = $otc_store_query->get()->toArray();
           
                }

                if($master_detail['type'] == 'service'){

                    $type_count = $service_store_query->get()->toArray();
           
                }

                if($master_detail['type'] == 'insurance'){

                    $type_count = $insurance_store_query->get()->toArray();
                }

                if($master_detail['type'] == 'hsrp'){

                    $type_count = $hsrp_store_query->get()->toArray();
                }

                foreach ($type_count as $type_count_index => $type_count_value) {
                            
                    $incentive_store_ids[]=$type_count_value['store_id'];
                    
                }
                
            }

            $query = [];
            // mandatory condition check
            if($master_detail['type'] == 'sale'){
                    
                // for all store
                $query = Sale::where('sale.status','done')
                    ->whereRaw('sale.sale_date BETWEEN "'.$master_detail['start_date'].'" AND "'.$master_detail['end_date'].'"')
                    ->whereIn('sale.store_id',$incentive_store_ids)
                    ->leftjoin('sale_finance_info','sale_finance_info.sale_id','sale.id')
                    ->leftjoin('finance_company','finance_company.id','sale_finance_info.company')
                    ->select('sale.id',
                        'sale.total_amount',
                        'sale.ex_showroom_price',
                        'sale.store_id',
                        'sale.sale_executive',
                        'sale.accessories_value',
                        'sale.sale_date',
                        'sale_finance_info.do',
                        'sale_finance_info.company',
                        'finance_company.company_name'
                    )->get()->toArray();
            }

            if($master_detail['type'] == 'bestdeal'){
                    
                // for all store
                $query = BestDealSale::leftjoin('sale','sale.id','best_deal_sale.sale_id')
                    ->where('best_deal_sale.status','Sale')
                    ->where('best_deal_sale.tos','best_deal')
                    ->where('sale.status','done')
                    ->whereRaw('sale.sale_date BETWEEN "'.$master_detail['start_date'].'" AND "'.$master_detail['end_date'].'"')
                    ->whereIn('sale.store_id',$incentive_store_ids)
                    ->leftjoin('sale_finance_info','sale_finance_info.sale_id','sale.id')
                    ->leftjoin('finance_company','finance_company.id','sale_finance_info.company')
                    ->select('sale.id',
                        'sale.total_amount',
                        'sale.store_id',
                        'sale.sale_executive',
                        'sale.sale_date',
                        'sale_finance_info.do',
                        'sale_finance_info.company',
                        'finance_company.company_name'
                    )->get()->toArray();

            }

            if($master_detail['type'] == 'otc_sale'){
                
                $query = OtcSale::leftjoin('payment_request',function($join){
                        $join->on("payment_request.type_id","=","otc_sale.id")
                        ->where("payment_request.type","=","otcsale");
                    })
                    ->leftjoin('otc','otc_sale.id','otc.otc_sale_id')
                    ->leftjoin('payment_request as sale_otc_pay',function($join){
                        $join->on("sale_otc_pay.type_id","=","otc.sale_id")
                        ->where("sale_otc_pay.type","=","sale");
                    })
                    ->whereRaw('(payment_request.status = "Done" or sale_otc_pay.status = "Done")')
                    ->whereRaw('DATE_FORMAT(otc_sale.created_at,"%Y-%m-%d") BETWEEN "'.$master_detail['start_date'].'" AND "'.$master_detail['end_date'].'"')
                    ->where('otc_sale.created_by','<>',0)
                    ->whereIn('otc_sale.store_id',$incentive_store_ids)
                    ->leftjoin('otc_sale_detail',function($join){
                        $join->on("otc_sale_detail.otc_sale_id","=","otc_sale.id")
                        ->where('otc_sale_detail.type','Part')
                        ->where('otc_sale_detail.otc_status',1);
                    })->select('otc_sale.id',
                        'otc_sale.created_by as sale_executive',
                        'otc_sale.amc_cost',
                        'otc_sale.ew_cost',
                        'otc_sale.hjc_cost',
                        'otc_sale.total_amount',
                        DB::raw('Sum(otc_sale_detail.amount) as accessories_value')
                    )->groupBy('otc_sale.id')->get()->toArray();
                                
            }

            if($master_detail['type'] == 'service'){
                
                $masterEng = Master::where('type','engine_oil_part')->where('key','Engine Oil')->get()->first();
                $engin = explode(',', $masterEng['value']);

                $query = Job_card::whereRaw('job_card.created_at BETWEEN "'.$master_detail['start_date'].'" AND "'.$master_detail['end_date'].'"')
                    ->whereIn('job_card.store_id',$incentive_store_ids)
                    ->where('job_card.created_by','<>',0)
                    ->where('job_card.service_status','done')
                    ->leftjoin('service_charge',function($join){
                        $join->on('service_charge.job_card_id','job_card.id')
                        ->where('charge_type','Charge');
                    })
                    ->leftjoin('service_part_request','service_part_request.job_card_id','job_card.id')
                    ->leftJoin('part','part.id','service_part_request.part_id')
                    ->leftjoin('service_part_request as part_spr',function($join)use($engin){
                        $join->on('part_spr.job_card_id','job_card.id')
                        ->whereNotIn('part.part_number',$engin)
                        ->where('part_spr.deleted_at',NULL)
                        ->where('part_spr.approved','1')
                        ->where('part_spr.part_issue','yes')
                        ->where('part_spr.warranty_approve',0)
                        ->where('part_spr.tampered','<>','no')
                        ->where('part_spr.confirmation','<>','insurance');
                    })
                    ->leftjoin('service_part_request as engine_spr',function($join)use($engin){
                        $join->on('engine_spr.job_card_id','job_card.id')
                        ->whereIn('part.part_number',$engin)
                        ->where('engine_spr.deleted_at',NULL)
                        ->where('engine_spr.approved','1')
                        ->where('engine_spr.part_issue','yes')
                        ->where('engine_spr.warranty_approve',0)
                        ->where('engine_spr.tampered','<>','no')
                        ->where('engine_spr.confirmation','<>','insurance');
                    })
                    ->select('job_card.id','job_card.job_card_type',
                        'job_card.total_amount',
                        DB::raw('Sum(service_charge.amount) as value_added_services'),
                        DB::raw('sum(part.price*part_spr.issue_qty) as part_amount'),
                        DB::raw('sum(part.price*engine_spr.issue_qty) as engine_amount'),
                        DB::raw('iFNUll(sum(part.price*part_spr.issue_qty),0)+iFNUll(sum(part.price*engine_spr.issue_qty),0) as accessories_value'),
                        'job_card.created_by as sale_executive'
                    )->groupBy('job_card.id')->get()->toArray();
                
            }

            if($master_detail['type'] == 'insurance'){
                
                $query = InsuranceRenewalOrder::whereRaw('insurance_renewal_order.created_at BETWEEN "'.$master_detail['start_date'].'" AND "'.$master_detail['end_date'].'"')
                ->where('insurance_renewal_order.created_by','<>',0)
                ->whereIn('insurance_data.store_id',$incentive_store_ids)
                ->where('insurance_renewal_order.status','Done')
                ->leftjoin('insurance_data','insurance_data.id','insurance_renewal_order.insurance_data_id')
                ->select(DB::raw('(select count(iro.id) from insurance_renewal_order as iro 
                    where iro.status ="Done" and iro.created_by = insurance_renewal_order.created_by) as user_renewal'),'insurance_renewal_order.total_amount',
                'insurance_renewal_order.created_by as sale_executive')
                ->get()->toArray();     
                
            }

            if($master_detail['type'] == 'hsrp'){
                
                $query = RtoModel::where('rto.main_type','hsrp')
                ->leftjoin('hsrp','rto.type_id','hsrp.id')
                ->where('rto.approve',3)
                ->whereRaw('DATE_FORMAT(hsrp.created_at,"%Y-%m-%d") BETWEEN "'.$master_detail['start_date'].'" AND "'.$master_detail['end_date'].'"')
                ->select(DB::raw('(select count(temp_rto.id) from rto as temp_rto 
                    where temp_rto.approve = 3 and temp_rto.main_type = "hsrp" and temp_rto.created_by = rto.created_by) as total_hsrp'),'rto.rto_amount as total_amount',
                'rto.created_by as sale_executive')
                ->get()->toArray();
                  
                
            }
            

            if($master_detail['type'] == 'sale' || $master_detail['type'] == 'bestdeal' || $master_detail['type'] == 'otc_sale' || $master_detail['type'] == 'service' || $master_detail['type'] == 'insurance' || $master_detail['type'] == 'hsrp'){

                
                if($master_detail['required_condition'] == 'all'){
                    
                    $user_all_arr = $this->sale_program($query,$incentive_program_detail,$master_detail['required_condition'],$master_detail['id']);
                
                    foreach ($user_all_arr as $user_id => $all_arr) {
                        $user_amounts = array_column($all_arr, 'amount');

                        $insert_t = Incentive::insertGetId([
                            'user_id' =>$user_id,
                            'amount' =>max($user_amounts),
                            'incentive_program_id' =>$master_detail['id']
                        ]);
                    }  

                }elseif($master_detail['required_condition'] == 'single'){

                    $program_fn = $this->sale_program($query,$incentive_program_detail,$master_detail['required_condition'],$master_detail['id']);

                    foreach ($program_fn as $user_id => $all_arr) {
                        $user_amounts = array_column($all_arr, 'amount');

                        $all_para_ar =['ex_showroom_price','total_amount','accessories_total_amount','finance_company','finance_amount','ew_cost','amc_cost','hjc_cost','job_card_type','value_added_services','no_of_insurance_renewal','no_of_hsrp'];

                        $para_amt =[];
                        foreach ($user_amounts as $key => $value) {
                            foreach ($all_para_ar as $in => $parameter) {

                                if (array_key_exists($parameter,$value))
                                {
                                    $para_amt[$parameter][] = $value[$parameter][0];
                                }

                            }
                            
                        }

                        foreach ($para_amt as $para_name => $all_amount) {
                            $insert_t = Incentive::insertGetId([
                                'user_id' =>$user_id,
                                'amount' =>max($all_amount),
                                'incentive_program_id' =>$master_detail['id']
                            ]);
                        }
                        
                    }

                }
                
                $update_program_master = IncentiveProgram::where('id',$master_detail['id'])
                                ->update(['incentive_status'=>1]);
            }
            
        }
    }

    public function sale_program($sale_query,$incentive_program_detail,$required_condition,$program_id){

        $user_all_arr =array();

        foreach ($sale_query as $s_index => $s_detail) {
            $sale_high_incen =[];
            $sale_all_true =[];
            
            foreach ($incentive_program_detail as $index => $program_detail) {
                
                if($program_detail['parameter'] == 'ex_showroom_price'){

                    // arg0=>value to match, arg1=> value for %
                    $parameter_fn = $this->check_parameters($s_detail['ex_showroom_price'],$s_detail['ex_showroom_price'],$program_detail['incentive_condition'],$program_detail['value'],$program_detail['incentive_type'],$program_detail['incentive_value'],$sale_all_true,$sale_high_incen,$required_condition,$program_detail['parameter']);

                    
                        $sale_high_incen = $parameter_fn[1];
                        $sale_all_true = $parameter_fn[0];
                    

                }elseif($program_detail['parameter'] == 'total_amount'){

                    // arg0=>value to match, arg1=> value for %
                    $parameter_fn = $this->check_parameters($s_detail['total_amount'],$s_detail['total_amount'],$program_detail['incentive_condition'],$program_detail['value'],$program_detail['incentive_type'],$program_detail['incentive_value'],$sale_all_true,$sale_high_incen,$required_condition,$program_detail['parameter']);

                        $sale_high_incen = $parameter_fn[1];
                        $sale_all_true = $parameter_fn[0];
                    

                }elseif($program_detail['parameter'] == 'accessories_total_amount'){

                    // arg0=>value to match, arg1=> value for %
                    $parameter_fn = $this->check_parameters($s_detail['accessories_value'],$s_detail['accessories_value'],$program_detail['incentive_condition'],$program_detail['value'],$program_detail['incentive_type'],$program_detail['incentive_value'],$sale_all_true,$sale_high_incen,$required_condition,$program_detail['parameter']);

                        $sale_high_incen = $parameter_fn[1];
                        $sale_all_true = $parameter_fn[0];
                    

                }elseif($program_detail['parameter'] == 'finance_company'){

                    // arg0=>value to match, arg1=> value for %
                    $parameter_fn = $this->check_parameters($s_detail['company'],$s_detail['do'],$program_detail['incentive_condition'],$program_detail['value'],$program_detail['incentive_type'],$program_detail['incentive_value'],$sale_all_true,$sale_high_incen,$required_condition,$program_detail['parameter']);

                    
                        $sale_high_incen = $parameter_fn[1];
                        $sale_all_true = $parameter_fn[0];
                    

                }elseif($program_detail['parameter'] == 'finance_amount'){

                    $parameter_fn = $this->check_parameters($s_detail['do'],$s_detail['do'],$program_detail['incentive_condition'],$program_detail['value'],$program_detail['incentive_type'],$program_detail['incentive_value'],$sale_all_true,$sale_high_incen,$required_condition,$program_detail['parameter']);

                    
                        $sale_high_incen = $parameter_fn[1];
                        $sale_all_true = $parameter_fn[0];
                    
                }elseif($program_detail['parameter'] == 'ew_cost'){

                    // arg0=>value to match, arg1=> value for %
                    $parameter_fn = $this->check_parameters($s_detail['ew_cost'],$s_detail['ew_cost'],$program_detail['incentive_condition'],$program_detail['value'],$program_detail['incentive_type'],$program_detail['incentive_value'],$sale_all_true,$sale_high_incen,$required_condition,$program_detail['parameter']);

                        $sale_high_incen = $parameter_fn[1];
                        $sale_all_true = $parameter_fn[0];
                    

                }elseif($program_detail['parameter'] == 'amc_cost'){

                    // arg0=>value to match, arg1=> value for %
                    $parameter_fn = $this->check_parameters($s_detail['amc_cost'],$s_detail['amc_cost'],$program_detail['incentive_condition'],$program_detail['value'],$program_detail['incentive_type'],$program_detail['incentive_value'],$sale_all_true,$sale_high_incen,$required_condition,$program_detail['parameter']);

                        $sale_high_incen = $parameter_fn[1];
                        $sale_all_true = $parameter_fn[0];
                    

                }elseif($program_detail['parameter'] == 'hjc_cost'){

                    // arg0=>value to match, arg1=> value for %
                    $parameter_fn = $this->check_parameters($s_detail['hjc_cost'],$s_detail['hjc_cost'],$program_detail['incentive_condition'],$program_detail['value'],$program_detail['incentive_type'],$program_detail['incentive_value'],$sale_all_true,$sale_high_incen,$required_condition,$program_detail['parameter']);

                        $sale_high_incen = $parameter_fn[1];
                        $sale_all_true = $parameter_fn[0];
                    

                }elseif($program_detail['parameter'] == 'job_card_type'){

                    // arg0=>value to match, arg1=> value for %
                    $parameter_fn = $this->check_parameters($s_detail['job_card_type'],$s_detail['total_amount'],$program_detail['incentive_condition'],$program_detail['value'],$program_detail['incentive_type'],$program_detail['incentive_value'],$sale_all_true,$sale_high_incen,$required_condition,$program_detail['parameter']);

                        $sale_high_incen = $parameter_fn[1];
                        $sale_all_true = $parameter_fn[0];
                    

                }elseif($program_detail['parameter'] == 'value_added_services'){

                    // arg0=>value to match, arg1=> value for %
                    $parameter_fn = $this->check_parameters($s_detail['value_added_services'],$s_detail['value_added_services'],$program_detail['incentive_condition'],$program_detail['value'],$program_detail['incentive_type'],$program_detail['incentive_value'],$sale_all_true,$sale_high_incen,$required_condition,$program_detail['parameter']);

                        $sale_high_incen = $parameter_fn[1];
                        $sale_all_true = $parameter_fn[0];
                    

                }elseif($program_detail['parameter'] == 'no_of_insurance_renewal'){

                    // arg0=>value to match, arg1=> value for %
                    $parameter_fn = $this->check_parameters($s_detail['user_renewal'],$s_detail['total_amount'],$program_detail['incentive_condition'],$program_detail['value'],$program_detail['incentive_type'],$program_detail['incentive_value'],$sale_all_true,$sale_high_incen,$required_condition,$program_detail['parameter']);

                        $sale_high_incen = $parameter_fn[1];
                        $sale_all_true = $parameter_fn[0];
                    

                }elseif($program_detail['parameter'] == 'no_of_hsrp'){

                    // arg0=>value to match, arg1=> value for %
                    $parameter_fn = $this->check_parameters($s_detail['total_hsrp'],$s_detail['total_amount'],$program_detail['incentive_condition'],$program_detail['value'],$program_detail['incentive_type'],$program_detail['incentive_value'],$sale_all_true,$sale_high_incen,$required_condition,$program_detail['parameter']);

                        $sale_high_incen = $parameter_fn[1];
                        $sale_all_true = $parameter_fn[0];
                    

                }
                
            }

            if($required_condition == 'single'){
                $user_all_arr[$s_detail['sale_executive']][] =array('user_id' =>$s_detail['sale_executive'],'amount' => ($sale_high_incen),'condition'=>$sale_all_true);
            }
            elseif(!in_array(0, $sale_all_true)){

                $user_all_arr[$s_detail['sale_executive']][] =array('user_id' =>$s_detail['sale_executive'],'amount' => max($sale_high_incen));
            }

            
        }
        
        return $user_all_arr;
    }

    public function check_parameters($type_value,$inc_amount_value,$program_condition,$program_value,$program_incentive_type,$program_incentive_value,$all_true,$type_high_incen,$required_condition,$parameter_name){

        $inc_type_all = $this->check_incentive($type_value,$program_condition,$program_value);

        $all_true = array_merge($all_true,array($inc_type_all));

        if($inc_type_all){
            if($program_incentive_type == 'percentage'){

                $incentive = round($inc_amount_value*($program_incentive_value/100),2);
                
                if($required_condition == 'single'){

                    $type_high_incen[$parameter_name][] = $incentive;
                
                }else{
                    $type_high_incen = array_merge($type_high_incen,array($incentive));

                }

            }else{
                // fixed condition
                $incentive = $program_incentive_value;

                if($required_condition == 'single'){

                    $type_high_incen[$parameter_name][] = $incentive;
                
                }else{
                    $type_high_incen = array_merge($type_high_incen,array($incentive));

                }
            }

            
        }
          
        return [$all_true,$type_high_incen];                                  
    }


    public function check_incentive($amount,$condition,$setting_amount){
        if($condition == "="){
            if($amount == $setting_amount){
                return true;
            }else{
                return false;
            }
        }else if($condition == ">"){
            if($amount > (int)$setting_amount){
                return true;
            }else{
                return false;
            }
        }else if($condition == ">="){
            if($amount >= (int)$setting_amount){
                return true;
            }else{
                return false;
            }
        }else if($condition == "<"){
            if($amount < (int)$setting_amount){
                return true;
            }else{
                return false;
            }
        }else if($condition == "<="){
            if($amount <= (int)$setting_amount){
                return true;
            }else{
                return false;
            }
        }else if($condition == "!="){
            if($amount != $setting_amount){
                return true;
            }else{
                return false;
            }
        }else if($condition == "BETWEEN"){
            $seperate_value = explode(',', $setting_amount);
            if($seperate_value[0] <= $amount && $seperate_value[1] >= $amount){
                return true;
            }else{
                return false;
            }
        }else if($condition == "NOT BETWEEN"){
            $seperate_value = explode(',', $setting_amount);
            if($seperate_value[0] >= $amount || $seperate_value[1] <= $amount){
                return true;
            }else{
                return false;
            }
        }  
    }

    public function incentive_list(){

        return view('admin.incentive.incentive_list',['layout' => 'layouts.main']);
    }
    public function incentive_list_api(Request $request){
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
        
        $store = Store::whereIn('id',CustomHelpers::user_store())
                    ->select('id')->get()->toArray();
        $userlog = IncentiveProgram::whereIn('incentive_program.store_id',$store)
        ->leftJoin('store','incentive_program.store_id','store.id')
        ->leftJoin('store as inc_store',function($join){
            $join->on(DB::raw("find_in_set(inc_store.id,incentive_program.incentive_store)"),'>',DB::raw("0"));
        })
        ->select('incentive_program.id',
            'incentive_program.type',
            DB::raw('Date_Format(incentive_program.start_date,"%d-%m-%Y") as start_date'),
            DB::raw('Date_Format(incentive_program.end_date,"%d-%m-%Y") as end_date'),
            DB::raw('(Case when incentive_program.required_condition = "all" then "All Conditions Satisfy" when incentive_program.required_condition = "single" then "Anyone Condition Satisfy" End) as required_condition'),
            'incentive_program.required_qty',
            DB::raw('(Case when incentive_program.incentive_status = 1 then "Created" Else "Pending" End) as incent_status'),
            DB::raw('concat(store.name,"-",store.store_type) as store_name'),
            DB::raw('Group_concat(inc_store.name,"-",inc_store.store_type) as incentive_store'))->groupBy('incentive_program.id');

        if(!empty($serach_value))
        {
            $userlog = $userlog->where('incentive_program.type','LIKE',"%".$serach_value."%")
                        ->orwhere('store.name','LIKE',"%".$serach_value."%")
                        ->orwhere('incentive_program.required_condition','LIKE',"%".$serach_value."%")
                        ;
        }

        $count = $userlog->count();
        $userlog = $userlog->offset($offset)->limit($limit);

        if(isset($request->input('order')[0]['column'])){
            $data = ['incentive_program.id','incentive_program.type','start_date','end_date',
            'required_condition','required_qty','incent_status','store_name','incentive_store'];
            $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
            $userlog->orderBy($data[$request->input('order')[0]['column']], $by);
        }
        else
        {
            $userlog->orderBy('incentive_program.id','desc');
        }
        $userlogdata = $userlog->get();
        
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count ;
        $array['data'] = $userlogdata; 
        return json_encode($array);
    }
}

?>