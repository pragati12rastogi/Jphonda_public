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
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Auth;
use Hash;
use PDF;
use \Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\Import;


class Performance extends Controller
{
    public function __construct()
    {
        //$this->middleware('auth');
    }
    public function rtoPerformance(){
        return view('admin.performance.rtoPerformanceList',['layout' => 'layouts.main']);
    }
    
    public function rtoPerformanceListApi(Request $request,$tab) {
        DB::enableQueryLog();
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;

            if($tab == 'submission')
            {
                $api_data = DB::table('agent_per_submission');
            }
            elseif($tab == 'total')
            {
                $api_data = DB::table('agent_per_submission')
                            ->select('id','agent_name',
                            DB::raw('SUM(totalTakeDays) as totalTakeDays'),
                            DB::raw('sum(numOfFile)	as numOfFile'),
                            DB::raw("
                                IF(sum(totalTakeDays) <= 15*SUM(numOfFile), 'good' , 
                                IF(sum(totalTakeDays) >= 20*SUM(numOfFile), 'avg' , 'bad')
                                ) as performanceStatus 
                            ")
                            )
                            ->groupBy('agent_name');
            }
            if(!empty($serach_value))
            {
                
                if($tab == 'submission')
                {
                    $api_data->where(function($query) use ($serach_value){
                        $query->where('agent_per_submission.agent_name','like',"%".$serach_value."%")
                        ->orwhere('agent_per_submission.submission_date','like',"%".$serach_value."%")
                        ->orwhere('agent_per_submission.receiving_date','like',"%".$serach_value."%")
                        ->orwhere('agent_per_submission.performanceStatus','like',"%".$serach_value."%");
                    });
                }
                elseif($tab == 'total')
                {
                    $api_data->where(function($query) use ($serach_value){
                        $query->where('agent_per_submission.agent_name','like',"%".$serach_value."%")
                        ->orwhere('agent_per_submission.performanceStatus','like',"%".$serach_value."%");
                    });
                }
            }
            if(isset($request->input('order')[0]['column']))
            {
                if($tab == 'submission')
                {
                    $data = [
                        'agent_per_submission.agent_name',
                        'agent_per_submission.submission_date',
                        'agent_per_submission.receiving_date',
                        'agent_per_submission.performanceStatus'
                    ];
                }
                elseif($tab == 'total')
                {
                    $data = [
                        'agent_per_submission.agent_name',
                        'agent_per_submission.performanceStatus'
                    ];
                }
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('agent_per_submission.agent_name','asc');   
        
        $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        $queries = DB::getQueryLog();
        //print_r($queries);die();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

}

