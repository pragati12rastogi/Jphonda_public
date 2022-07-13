<?php

namespace App\Http\Controllers\front;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App;
use Illuminate\Routing\Route;
use App\Model\Job_card;
use App\Model\ServiceModel;
use App\Model\Insurance;
use App\Model\InsuranceBooking;
use App\Model\Sale;
use App\Model\SaleOrder;
use App\Model\EnquiryService;
use App\Model\Store;
use App\Model\ProductModel;
use App\Model\Stock;
use App\Model\MasterAccessories;
use App\Model\Scheme;
use App\Model\Finance;
use App\Model\FinanceCompany;
use App\Model\FinancierExecutive;
use App\Model\SaleFinance;
use App\Model\AMCProduct;
use \App\Custom\CustomHelpers;
use DB;


class VehicleController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    
    public function Vehicle() {
        $model_name = ProductModel::where('isforsale',1)->where('type','product')->orderBy('model_name','ASC')->groupBy('model_name')->get(['model_name']);
         $store = Store::all();
         $scheme = Scheme::all();
         $company_name = FinanceCompany::all();
         // $fe = FinancierExecutive::where('id',$company_name['finance_executive_id'])->get()->first();
         $financeRate = CustomHelpers::getFinanceRate();
         $amcPrice = AMCProduct::where('duration',1)->get()->first();
         $amc_price = $amcPrice['price'];
         $rate = $financeRate[1]['interest'];
        $data = array(
            'scheme' => $scheme,
            'store' => $store,
            // 'fe' => $fe,
            'amc_price' => $amc_price,
            'rate' => $rate,
            'company_name' => $company_name,
            'model_name' => $model_name,
            'csd'   =>  VehicleController::csd_amount(),
            'layout'=>'front.layout'
        );
        return view('front.vehicle', $data);  
    }

    public function csd_amount() {
        return 2.7;
    }
     public function getModelVariant(Request $request) {
        $model_name = $request->input('model_name');
        $model_var = ProductModel::where('model_name',$model_name)->where('type','product')
                        ->where('isforsale',1)
                        ->orderBy('model_variant','ASC')->groupBy('model_variant')
                        ->get();
        return response()->json($model_var);

    }
    public function getModelColorCode(Request $request) {
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
        $prodId = $request->input('prodId');

        $getqty = ProductModel::where('id',$prodId)
                    ->select(
                        'product.basic_price'
                    )->first();
        $arr = (($getqty)?$getqty->toArray():array());
        return response()->json($arr);

    }

    public function findAccessories(Request $request) {
        $prodId = $request->input('prodId');
        $filter = $request->input('filter');
        $getCat = ProductModel::select('model_name')->where('id',$prodId)->first()->toArray();
        $getAccessories =  MasterAccessories::leftJoin('part',function($join){
                                    $join->on(DB::raw("FIND_IN_SET(part.id,master_accessories.part_id)"),">",DB::raw('0'));
                                })
                                ->where(function($query) use($getCat){
                                    $query->where('master_accessories.model_name','like',$getCat['model_name'])
                                            ->orwhereNull('master_accessories.model_name');
                                })
                            ->whereRaw('master_accessories.part_id <> ""')
                            ->whereNotNull('master_accessories.part_id')
                            ->select('part.id',
                                    DB::raw("IFNULL(master_accessories.accessories_name,part.name) as name"),
                                    'part.part_number as part_no',
                                    DB::raw('1 as qty'),'price as mrp','unit_price' ,
                                    'master_accessories.id as nid',
                                    'master_accessories.connection','master_accessories.hop','master_accessories.full',
                                    'master_accessories.model_name',
                                    'master_accessories.accessories_name',
                                    'master_accessories.part_id as accessories_id')
                                ->orderBy('nid','ASC')
                                ->get();
        return response()->json($getAccessories);

    }

     public function financerExecutive(Request $request) {
        $company_name = $request->input('finance_company_name');
        $data = SaleFinance::leftJoin('financier_executive','financier_executive.id','sale_finance_info.finance_executive_id')
        ->where('sale_finance_info.company',$company_name)
        ->where('sale_finance_info.finance_executive_id','<>',0)
        ->select(
                'sale_finance_info.loan_amount',
                'sale_finance_info.do',
                'sale_finance_info.dp',
                'sale_finance_info.los',
                'sale_finance_info.roi',
                'sale_finance_info.payout',
                'sale_finance_info.emi',
                'sale_finance_info.pf',
                'sale_finance_info.sd',
                'financier_executive.executive_name',
                'sale_finance_info.finance_executive_id'
        )->orderBy('sale_finance_info.id','DESC')->get();
        // $q = FinancierExecutive::where('finance_company_id',$company_name)->get();
        return response()->json($data);
    }



}
