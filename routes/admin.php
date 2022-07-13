<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::group(['middleware' => ['auth','rights'],'namespace'=>'admin','prefix' => 'admin'], function () {

	Route::get('/','Customer@dashboard');
	Route::get('/dashboard','Customer@dashboard');
	Route::get('/customer/getCountries','Customer@getCountries');
	Route::get('/customer/state/{id}','Customer@getStates');
	Route::get('/customer/city/{id}','Customer@getCities');
	Route::get('/customer/location/{id}','Customer@getLocation');
	//Route::get('/customer/find/mobile/{id}','Customer@findMobile');
	Route::get('/customer/registration','Customer@registration');
	Route::post('/customer/registration','Customer@registration_Db');
	Route::get('/customer/list','Customer@customer_list');
	Route::get('/customer/list/api','Customer@customer_data');
	Route::get('/customer/update/{id}','Customer@customer_update');
	Route::post('/customer/update/{id}','Customer@customer_update_Db');
	
	//Unload data
	Route::get('/unload/data','Customer@unload_data');
	Route::post('/unload/data','Product@unload_storeDB');
	Route::get('/unload/data/list','Customer@unloadView');
	Route::get('/unload/data/list/api','Customer@unloadView_api');
	Route::get('/unload/data/edit/{id}','Customer@unload_update');
	Route::post('/unload/data/edit/{id}','Customer@unload_update_Db');
	// Route::get('/unload/data/del/{type}','Customer@unloadViewDel');
	Route::get('/unload/list/get/unloadAddon','Customer@getUnloadAddon');
	Route::post('/unload/list/update/unloadAddon','Customer@uploadUnloadAddon');

	//damage claim
	// Route::get('/damage/claim','Customer@damage_claim');		//damage claim form
	// Route::post('/damage/claim','Customer@damage_claim_storeDB');	//damage claim form
	Route::get('/damage/claim/list','Customer@damageView');
	Route::get('/damage/claim/list/api/{type}','Customer@damageView_api');
	
	Route::get('/damage/claim/del','Customer@damageViewDel');

	Route::get('/damage/claim/view/{id}','Customer@damageclaim_view');
	Route::get('/damage/claim/view/api/{id}','Customer@damageclaim_view_api');

	Route::get('/damage/claim/view/update/{id}/{det_id}','Customer@damageForm');
	Route::post('/damage/claim/view/update/{id}/{det_id}','Customer@damageForm');

	Route::get('/damage/claim/update/{id}','Customer@damageclaimUpdate');
	Route::post('/damage/claim/update/{id}','Customer@damageclaimUpdate_DB');

	Route::get('/damage/claim/request/{id}','Product@damageForm');
	Route::post('/damage/claim/request/{id}','Product@damageForm');

	//Factory
	Route::get('/factory/list','Customer@factory_list');
	Route::get('/factory/list/api','Customer@factory_list_api');
	Route::get('/factory/create','Customer@createFactory');
	Route::post('/factory/create','Customer@createFactory_db');

	//Loader
	Route::get('/loader/list','Customer@loader_list');
	Route::get('/loader/list/api','Customer@loader_list_api');
	Route::get('/loader/update/{id}','Customer@loader_update');
	Route::post('/loader/update/{id}','Customer@loader_updateDb');

	//Finance
	Route::get('/finance/company/list','MastersController@finance_company_list');
	Route::get('/finance/company/list/api','MastersController@finance_company_list_api');
	Route::get('/finance/company/update/getdata/{id}','MastersController@GetFinance_Company');
	Route::get('/finance/company/update','MastersController@UpdateFinanceCompany_DB');

    //Vehicle 
	Route::get('/master/product/create','MastersController@createVehicle');
	Route::post('/master/product/create','MastersController@createVehicle_db');
	Route::get('/master/product/list','MastersController@vehicle_list');
	Route::get('/master/product/list/api','MastersController@vehicle_list_api');
	Route::get('/master/product/view/{id}','MastersController@vehicle_view');
	Route::post('/master/product/uploadpic','MastersController@vehicle_Uploadpic');
	Route::get('/master/product/uploadpicdata','MastersController@uploadpic_data');
	Route::get('/master/product/uploadpicdelete','MastersController@uploadpic_delete');
	Route::get('/master/product/uploadpicsetdefault','MastersController@uploadpic_setdefault');

	// Master 
	Route::get('/master/list','MastersController@master_list');
	Route::get('/master/list/api','MastersController@master_list_api');


	// vehicle addon stock List

	Route::get('/vehicle/addon/stock/list','MastersController@vehicleAddon_stocklist');
	Route::get('/vehicle/addon/stock/list/api','MastersController@vehicleAddon_stocklistapi');

   // Sub Dealer Master List

	Route::get('/sub/dealer/master/list','MastersController@subdealer_masterlist');
	Route::get('/sub/dealer/master/list/api','MastersController@subdealer_masterlistapi');

	// Master accessories
	Route::get('/master/accessories/list','MastersController@master_accessories_list');
	Route::get('/master/accessories/list/api','MastersController@master_accessories_list_api');

	// Master insurance
	Route::get('/master/insurance/company/list','MastersController@master_insurance_company_list');
	Route::get('master/insurance/company/list/api','MastersController@master_insurance_company_list_api');
	

	// AMC Product  
	Route::get('/amc/product/list','MastersController@amc_product_list');
	Route::get('/amc/product/list/api','MastersController@amc_product_list_api');

	//scheme
	Route::get('/master/scheme/create','SchemeController@scheme_create');
	Route::post('/master/scheme/create','SchemeController@scheme_create_DB');
	Route::get('/master/scheme/list','SchemeController@scheme_list');
	Route::get('/master/scheme/list/api','SchemeController@scheme_list_api');
	Route::get('/master/scheme/view/{id}','SchemeController@scheme_view');
	Route::get('/master/scheme/update/{id}','SchemeController@scheme_update');
	Route::post('/master/scheme/update/{id}','SchemeController@scheme_update_DB');

	//AMC Booklet
	Route::get('/amc/booklet/number','AMCBookletController@AmcBooklateNo_create');
	Route::get('/amc/get/engine/type','AMCBookletController@getEngineType');
	Route::get('/amc/get/type','AMCBookletController@getType');
	Route::post('/amc/booklet/number','AMCBookletController@AmcBooklateNoCreate_DB');
	Route::get('/amc/booklet/issue/list','AMCBookletController@AmcBooklate_list');
	Route::get('/amc/booklet/issue/list/api/{type}','AMCBookletController@AmcBooklateList_api');
	Route::get('/amc/get/booklet/number','AMCBookletController@AmcBookletNumber_Get');
	Route::get('/amc/booklet/number/issue','AMCBookletController@AmcBookletNumber_Issue');
	Route::get('/amc/booklet/movement','AMCBookletController@AmcBooklate_movement');
	Route::post('/amc/booklet/movement','AMCBookletController@AmcBooklateMovement_DB');
	Route::get('/amc/booklet/movement/list','AMCBookletController@AmcBooklateMovement_list');
	Route::get('/amc/booklet/movement/list/api','AMCBookletController@AmcBooklateMovementList_api');
	Route::get('/amc/booklet/movement/view/{id}','AMCBookletController@AmcBooklateMovement_view');
	Route::get('/amc/booklet/number/movement/{id}','AMCBookletController@AmcBooklateMovement');





	//Part
	Route::get('/part/create','PartImportController@CreatePart');
	Route::post('/part/create','PartImportController@create_part_import');
	Route::get('/part/list','PartController@part_list');
	Route::get('/part/list/api','PartController@part_list_api');
	Route::get('/part/consumption/update','PartController@consumption_update');
	Route::get('/part/consumption/updatedb','PartController@consumption_updateDB');
	Route::get('/part/enquiry/statusupdate','PartController@enquiry_statusupdate');

	Route::get('/part/movement','PartController@Partmovement');
	Route::post('/part/movement','PartController@PartmovementDB');
	Route::get('/part/movement/summary','PartController@part_movement_summary');
	Route::get('/part/movement/summary/api','PartController@part_movement_summary_api');
	Route::get('/partmovement/summary/movement/{id}','PartController@part_movement_update');
	Route::get('/part/movement/process/form/{id}','PartController@part_movement_process');
	Route::post('/part/movement/process/form/{id}','PartController@part_movement_process_db');
	Route::get('/part/movement/process/summary','PartController@part_movement_process_summary');
	Route::get('/part/movement/process/summary/api','PartController@part_movement_process_summary_api');
	Route::get('/part/movement/cancel/form','PartController@part_movement_cancel');
	Route::post('/part/movement/cancel/form','PartController@part_movement_cancelDB');

	Route::get('/part/movement/process/cancel/{id}','PartController@part_process_cancel');


	Route::get('/part/check/part/available','PartController@checkPartAvailable');
	Route::get('/part/filter/addonproduct/api', 'PartController@filter_addonproduct_api');
	Route::get('/part/addon/model/store/api', 'PartController@filter_addonmodel_api');
	

	// part stock import
	Route::get('/part/stock/create','PartImportController@CreatePartStock');
	Route::post('/part/stock/create','PartImportController@CreatePartStock_Store');

	// Route::get('/part/otc/sale','PartController@otc_sale');
	// Route::post('/part/otc/sale','PartController@otcSale_DB');
	// Route::get('/otc/sale/list','PartController@otcSale_list');
	// Route::get('/otc/sale/list/api','PartController@otcSaleList_api');
	// Route::get('/otc/receptionist/sale/list','PartController@forReceptionistotcSale_list');
	// Route::get('/otc/receptionist/sale/list/api','PartController@forReceptionistotcSaleList_api');
	// Route::get('/get/otc/sale','PartController@getOtcSale');
	// Route::get('/otc/check/part','PartController@checkPart');
	// Route::get('/otc/sale/update','PartController@otcSale_update');
	// Route::get('/otc/sale/payment/{id}','PartController@otcSale_payment');
	// Route::post('/otc/sale/payment','PartController@otcSalePayment_DB');
	// Route::get('/otc/sale/payment/detail/{id}','PartController@otcSalePayment_detail');
	// Route::get('/otc/sale/confirm/{id}','PartController@otcSale_confirm');
	// Route::get('/otc/sale/get/frame','PartController@otcSaleGet_frame');

	Route::get('/part/otc/sale','PartController@otc_sale');
	Route::post('/part/otc/sale','PartController@otcSale_DB');
	Route::get('/part/otc/sale/list','PartController@otcSale_list');
	Route::get('/part/otc/sale/list/api','PartController@otcSaleList_api');
	Route::get('/part/otc/receptionist/sale/list','PartController@forReceptionistotcSale_list');
	Route::get('/part/otc/receptionist/sale/list/api','PartController@forReceptionistotcSaleList_api');
	Route::get('/part/get/otc/sale','PartController@getOtcSale');
	Route::get('/part/otc/check/part','PartController@checkPart');
	Route::get('/part/otc/sale/update','PartController@otcSale_update');
	Route::get('/part/otc/sale/payment/{id}','PartController@otcSale_payment');
	Route::post('/part/otc/sale/payment','PartController@otcSalePayment_DB');
	Route::get('/part/otc/sale/payment/detail/{id}','PartController@otcSalePayment_detail');
	Route::get('/part/otc/sale/confirm/{id}','PartController@otcSale_confirm');
	Route::get('/part/otc/sale/get/frame','PartController@otcSaleGet_frame');



	// part location
	Route::get('/part/create/location','PartImportController@create_part_loc');
	Route::post('/part/create/location','PartImportController@create_part_loc_db');
	Route::get('/get/part/row','PartImportController@get_part_row');
	Route::get('/get/part/cell','PartImportController@get_part_cell');
	Route::get('/part/location/list','PartImportController@partLocationList');
	Route::get('/part/location/list/api','PartImportController@partLocationListApi');
	
	// part price update using import
	Route::get('/part/update/price','PartImportController@updatePartPrice');
	Route::post('/part/update/price','PartImportController@PartPriceUpdateImport');


 
    //Part Requisition 
    Route::get('/part/requisition','PartController@createRequisition');
    Route::post('/part/requisition','PartController@createRequisitionDB');
    Route::get('/part/requisition/enquiry/list','PartController@RequisitionEnquiryList');
    Route::get('/part/requisition/enquiry/list/api','PartController@RequisitionEnquiryListApi');
    Route::get('/part/requisition/purchase/list','PartController@RequisitionPurchaseList');
    Route::get('/part/requisition/purchase/list/api','PartController@RequisitionPurchaseListApi');
    Route::get('/part/stock/list','PartController@PartStockList');
    Route::get('/part/stock/list/api','PartController@PartStockListApi');

    Route::get('/part/purchase/order/create','PartController@CreatePurchaseOrder');
    Route::get('/part/get/partdetails/{id}','PartController@GetPartDetails');
    Route::post('/part/purchase/order/create','PartController@CreatePurchaseOrder_DB');


	//Locality
    Route::get('/city/location','CityController@create_locality');
    Route::get('/city/locality/search','CityController@locality_search');
    Route::post('/city/location/','CityController@create_db');
    Route::get('/city/locality/list','CityController@locality_list');
    Route::get('/city/locality/list/api','CityController@locality_list_api');
    Route::get('/city/list','MastersController@city_list');
    Route::get('/city/list/api','MastersController@city_list_api');


    // Financer Exicutive
    Route::get('/master/finance/executive','MastersController@Finance_Executive');
    Route::get('/master/finance/executive/{id}','MastersController@Finance_Executive');
    Route::get('/master/get/executive/{id}','MastersController@GetFinance_Exicutive');
	Route::post('/master/finance/executive','MastersController@FinanceExecutive_DB');
	Route::get('/master/executive/list/api','MastersController@financerList_api');

	//Shortage
	Route::get('/shortage/list','Customer@shortage_list');
	Route::get('/shortage/list/api','Customer@shortage_list_api');
	Route::get('/shortage/shortage_qty/{id}','Customer@get_shortage_qty');
	Route::post('/shortage/update_shortage','Customer@update_shortage_qty');
	Route::get('/shortage/show_log/{id}','Customer@show_shortage_log');


	//Insurance

	Route::get('/insurance/renewal','InsuranceController@InsuranceRenewal');
	Route::get('/insurance/frame/search','InsuranceController@InsuranceSearchFrame');
	Route::get('/insurance/od/policynumber/search','InsuranceController@SearchODPolicyNumber');
	Route::get('/insurance/tp/policynumber/search','InsuranceController@SearchTPPolicyNumber');
	Route::post('/insurance/renewal','InsuranceController@InsuranceRenewal_DB');
	Route::get('/insurance/payment/{id}','InsuranceController@InsurancePayment');
	Route::get('/insurance/order/{id}','InsuranceController@InsuranceOrder');
	Route::post('/insurance/order','InsuranceController@InsuranceOrder_DB');
	Route::get('/insurance/breakin/{id}','InsuranceController@InsuranceBreakIn');
	Route::post('/insurance/breakin/{id}','InsuranceController@InsuranceBreakIn_DB');
	// Route::get('/insurance/renewal/list/{id}','InsuranceController@Renewal_list');
	Route::get('/insurance/renewal/cancel/{id}','InsuranceController@InsuranceRenewal_cancel');
	Route::get('/insurance/renewal/update/{id}','InsuranceController@InsuranceRenewal_edit');
	Route::post('/insurance/renewal/update/{id}','InsuranceController@InsuranceRenewal_update');
	Route::get('/insurance/renewal/list','InsuranceController@InsuranceRenewal_list');
	Route::get('/insurance/renewal/list/api','InsuranceController@InsuranceRenewalList_api');
	Route::get('/insurance/approval/request/edit/{id}','InsuranceController@InsuranceApprovalRequest_edit');
	Route::get('/insurance/approval/request/cancel/{id}','InsuranceController@InsuranceApprovalRequest_cancel');
	Route::get('/insurance/renewal/view/{id}','InsuranceController@InsuranceRenewal_view');




	//stock movement
	Route::get('/stock/movement','Customer@stock_movement');
	Route::post('/stock/movement','Customer@stock_movement_storeDB');
	//Route::get('/stock/movement/list','Customer@stockView');
	//Route::get('/stock/movement/list/api','Customer@stockView_api');
	Route::get('/stock/movement/del/{type}','Customer@stockViewDel');
	Route::get('/stock/movement/store/accept/{stock_id}','Product@stockListAccept');
	Route::post('/stock/movement/store/accept/{stock_id}','Product@stockListAccept_DB');
	Route::get('/stock/movement/accountant/waybill/{stock_id}','Product@generateWaybill');
	Route::post('/stock/movement/accountant/waybill/{stock_id}','Product@waybill_DB');
	Route::get('/stock/movement/store/moved/{stock_id}','Product@stockMoved');

	// Stock Allocation
	Route::get('/stock/allocation','StockAllocation@stockAllocation');
	Route::get('/stock/product/allocation','StockAllocation@stockAllocationDatatable');
	Route::post('/stock/product/allocation/api','StockAllocation@stockAllocation_api');
	Route::get('/stock/allocation/header/fix','StockAllocation@stockAllocationHeaderFix');
	Route::get('/stock/allocation/book','StockAllocation@stockAllocBook');
	Route::get('/stock/allocation/fromchange','StockAllocation@onchangeStore');
	Route::get('/stock/allocation/qtychange','StockAllocation@onchangeQty');
	Route::get('/stock/allocation/list','StockAllocation@stockAllocView');
	Route::get('/stock/allocation/list/api','StockAllocation@stockAllocView_api');
	Route::get('/stock/allocation/store/accept/{stock_id}','StockAllocation@stockAllocAccept');
	Route::get('/stock/get/loader/{id}','StockAllocation@stockGetLoader');
	Route::post('/stock/allocation/store/accept/{stock_id}','StockAllocation@stockAllocAccept_DB');
	Route::get('/stock/allocation/process/list','StockAllocation@stockAllocProcess');
	Route::get('/stock/allocation/process/list/api','StockAllocation@stockAllocProcessView_api');
	Route::get('/stock/allocation/accountant/waybill/{stock_id}','StockAllocation@generateWaybill');
	Route::post('/stock/allocation/accountant/waybill/{stock_id}','StockAllocation@waybill_DB');
	Route::get('/stock/allocation/store/moved/{stock_id}','StockAllocation@stockMoved');

	// stock allocation cancel
	Route::get('/stock/allocation/cancel','StockAllocation@stockCancel');
	Route::post('/stock/allocation/cancel','StockAllocation@stockCancel_DB');
	//cancel after generate waybill
	Route::get('/stock/allocation/waybill/cancel','StockAllocation@stockmWaybillCancel');



	/* Sale */
	Route::get('/sale','SaleController@sale');
	Route::post('/sale','SaleController@saleDB');
	Route::post('/sale/best/deal','SaleController@sale_best_dealDB');
	Route::get('/sale/best/deal/get/product_info','SaleController@sale_best_deal_getProductInfo');
	Route::get('/sale/get/product/info','SaleController@productInfo');
	Route::get('/sale/get/accessories','SaleController@findAccessories');
	Route::get('/sale/get/accessoriespartNo','SaleController@accessoriespartNo');
	Route::get('/sale/findBookingNo','SaleController@findBookingNo');
	Route::get('/sale/hirise/{id}','SaleController@createHirise');
	Route::get('/sale/ew/invoice/{id}','SaleController@createInvoice');
	Route::post('/sale/hirise','SaleController@createHirise_DB');
	Route::post('/sale/ew/invoice','SaleController@createEwInvoice_DB');
	Route::get('/sale/insurance/{id}','SaleController@createInsurance');
	Route::post('/sale/insurance','SaleController@createInsurance_db');
	Route::get('/cancel/insurance/list','SaleController@cancelInsuranceList');
	Route::get('/cancel/insurance/list/api','SaleController@cancelInsuranceList_api');
	Route::get('/sale/check/partNumber','SaleController@checkPartNumber');

	//pending item
	Route::get('/sale/pending/item/{id}','SaleController@createPendingItem');
	Route::post('/sale/pending/item','SaleController@createPendingItem_db');

	// pending sale Itmes 
	Route::get('/sale/pending/addon/list','SaleController@salePendingAddonlist');
	Route::get('/sale/pending/addon/list/api','SaleController@salePendingAddon_api');
	Route::get('/sale/get/pending/addon','SaleController@get_sale_pendingAddon');
	Route::post('/sale/pending/addon/issueItem','SaleController@issue_pendingAddonDB');

	// Handling Charge
	Route::get('/sale/handling/charge/list','SaleController@HandlingCharge_list');
	Route::get('/sale/handling/charge/list/api','SaleController@HandlingChargeList_api');



	/* Sale Update */
	Route::get('/update/sale/{id}','SaleController@saleUpdate');
	Route::post('/update/sale/{id}','SaleController@saleUpdateDB');
	Route::get('/sale/update/get/product/info','SaleController@productInfoAtUpdate');
	Route::get('/sale/update/order/{id}','SaleController@orderUpdate');
	Route::post('/sale/update/order/{id}','SaleController@orderUpdateDB');
	Route::get('/sale/update/hirise/{id}','SaleController@hiriseUpdate');
	Route::post('/sale/update/hirise/{id}','SaleController@hiriseUpdateDB');

	Route::get('/sale/update/insurance/{id}','SaleController@insuranceUpdate');
	Route::post('/sale/update/insurance/{id}','SaleController@insuranceUpdateDB');

	Route::get('/sale/update/rto/{id}','RtoController@rtoUpdate');
	Route::post('/sale/update/rto/{id}','RtoController@rtoUpdateDB');
	Route::get('/sale/update/pending/item/{id}','SaleController@pendingItemUpdate');
	Route::post('/sale/update/pending/item/{id}','SaleController@pendingItemUpdateDB');
	Route::get('/sale/update/otc/{id}','SaleController@otcUpdate');
	Route::post('/sale/update/otc/{id}','SaleController@otcUpdateDB');


	/* RTO */
	Route::get('/sale/rto/{id}','RtoController@createRto');
	Route::post('/sale/rto','RtoController@createRto_db');
	Route::get('/rto/list','RtoController@rtoPendingList');
	Route::get('/rto/list/api/{type}','RtoController@rtoPendingList_api');
	Route::get('/rto/list/hsrp/api','RtoController@rtoHsrpPendingList_api');
	Route::get('/rto/approval/{id}','RtoController@rtoApproval');
	Route::post('/rto/list/reject','RtoController@rtoRejectApproval');
	Route::get('/rto/fillup/list','RtoController@rtoList');
	Route::get('/rto/fillup/list/api','RtoController@rtoList_api');
	Route::get('/rto/difference/amount/list','RtoController@rtoDifferenceAmountList');
	Route::get('/rto/difference/amount/list/api','RtoController@rtoDifferenceAmountList_api');
	Route::get('/rto/payment/list','RtoController@rtoPayment_list');
	Route::get('/rto/payment/list/api/{type}','RtoController@rtoPaymentList_api');
	Route::get('/rto/hsrp/payment/list/api','RtoController@RtoHsrpPaymentList_api');
	Route::get('/rto/payment/{id}','RtoController@rtoPayment');
	Route::get('/rto/payment/update/{id}','RtoController@rtoPayment_Db');
	Route::get('/rto/file/list','RtoController@rtoFile_list');
	Route::get('/rto/file/list/api/{type}','RtoController@rtoFileList_api');
	
	Route::get('/rto/get/file/data/{id}','RtoController@rtoList_data');
	Route::get('/rto/file/upload','RtoController@rtoFile_upload');
	Route::get('/rto/get/list/data','RtoController@rtoListData');
	

	/*  Best Deal */
	Route::get('/sale/create/best/deal','BestDealController@create_best_deal');
	Route::post('/sale/create/best/deal','BestDealController@create_best_dealDB');
	Route::get('/sale/best/deal/list','BestDealController@best_deal_list');
	Route::get('/sale/best/deal/list/api','BestDealController@best_deal_list_api');
	Route::post('/sale/best/deal/uploadpic','BestDealController@best_dealUploadpic');
	Route::get('/sale/best/deal/uploadpic_data/','BestDealController@uploadpic_data');
	Route::get('/sale/best/deal/uploadpic_delete/','BestDealController@uploadpic_delete');

	Route::get('/sale/exchange/list','BestDealController@sale_exchange_list');
	Route::get('/sale/exchange/list/api','BestDealController@sale_exchange_list_api');

	// best deal inspection
	Route::get('/sale/best/deal/get/inspection','BestDealController@getInspection');
	Route::post('/sale/best/deal/inspectiondb','BestDealController@inspectionDB');
	// best deal inventory
	Route::get('/sale/best/deal/inventory','BestDealController@best_deal_inventory');
	Route::get('/sale/best/deal/inventory/api','BestDealController@best_deal_inventory_api');
	Route::get('/sale/best/deal/inventory/buy','BestDealController@best_deal_inventory_buy');
	Route::post('/sale/best/deal/inventory/buy','BestDealController@best_deal_inventory_buyDB');

	// bestdeal sale view
	Route::get('/bestdeal/sale/view/{id}','BestDealController@bestdeal_sale_view');

	//Best Deal sale list
	Route::get('/bestdeal/sale/list','BestDealController@BestDealSale_list');
	Route::get('/bestdeal/sale/list/api/{type}','BestDealController@BestDealSaleList_api');
	//best deal sale redirection page 
	Route::get('/bestdeal/sale/add/pending/details/{id}','BestDealController@fillPendingDetails');
	// best deal sale payment
	Route::get('/bestdeal/sale/pay/{id}','BestDealController@sale_pay');	
	Route::get('/bestdeal/sale/pay_detail/{id}','BestDealController@sale_pay_detail');

	// bestdeal sale document
	Route::get('/bestdeal/sale/document/{id}','BestDealController@sale_document_page');
	Route::post('/bestdeal/sale/document','BestDealController@sale_document_page_db');

	//  bestdeal sale hirise/wings
	Route::get('/bestdeal/sale/hirise/{id}','BestDealController@createHirise');
	Route::post('/bestdeal/sale/hirise','BestDealController@createHirise_db');

	//bestdeal rto
	Route::get('/bestdeal/sale/rto/{id}','BestDealController@createRto');
	Route::post('/bestdeal/sale/rto','BestDealController@createRto_db');

	//bestdeal pending item
	Route::get('/bestdeal/sale/pending/item/{id}','BestDealController@createPendingItem');
	Route::post('/bestdeal/sale/pending/item','BestDealController@createPendingItem_db');


	// bestdeal sale update
	Route::get('/bestdeal/update/sale/{id}','BestDealController@update_bestdeal_sale');
	Route::post('/bestdeal/update/sale','BestDealController@update_bestdeal_saleDB');

	// bestdeal sale document update
	Route::get('/bestdeal/update/sale/document/{id}','BestDealController@update_document');
	Route::post('/bestdeal/update/sale/document','BestDealController@update_document_db');

	//  bestdeal sale hirise/wings  update
	Route::get('/bestdeal/update/sale/hirise/{id}','BestDealController@updateHirise');
	Route::post('/bestdeal/update/sale/hirise','BestDealController@updateHirise_db');

	//bestdeal rto  update
	Route::get('/bestdeal/update/sale/rto/{id}','BestDealController@updateRto');
	Route::post('/bestdeal/update/sale/rto','BestDealController@updateRto_db');

	//bestdeal pending item  update
	Route::get('/bestdeal/update/sale/pending/item/{id}','BestDealController@updatePendingItem');
	Route::post('/bestdeal/update/sale/pending/item','BestDealController@updatePendingItem_db');



	// download sample = ''
	Route::get('/rto/download/{filename}', function($filename)
	{
		// Check if file exists in app/storage/file folder
		$file_path = public_path() .'/file/'. $filename;
		// echo $file_path;die;
		if (file_exists($file_path))
		{
			// Send Download
			return Response::download($file_path, $filename, [
				'Content-Length: '. filesize($file_path),
				'Content-Disposition' => 'inline',
                'Cache-Control'=>'public,no-cache, no-store'
			]);
		}
		else
		{
			// Error
			exit('Requested file does not exist on our server!');
		}
	})->name('sampleImport')
	->where('filename', '[A-Za-z0-9\_\.]+');

	//OTC
	Route::get('/sale/otc/{id}','SaleController@createOtc');
	Route::post('/sale/otc','SaleController@createOtc_db');


	//pending number plate
	Route::get('/rto/numberPlate/pending','RtoController@numberPlate');
	Route::get('/rto/list/numberPlate/pending','RtoController@numberPlate_list');
	Route::get('/rto/hsrp/list/numberPlate/pending','RtoController@HsrpNumberPlate_list');
	//import
	Route::post('/rto/import/numberPlate/pending','RtoController@numberPlateImport');
	//RTO file submission
	Route::get('/rto/file/submission','RtoController@rtoFile_submission');
	Route::post('/rto/file/submission','RtoController@rtoFileSubmission_Db');
	Route::get('/rto/file/submission/list','RtoController@rtoFileSubmission_list');
	Route::get('/rto/file/submission/list/api','RtoController@rtoFileSubmissionList_api');
	Route::get('/rto/file/submission/view/{id}','RtoController@rtoFileSubmissionList_view');
	Route::get('/rto/file/submission/print/{id}','RtoController@rtoFileSubmissionPDF_print');
	//RTO RC 
	Route::get('/rto/rc/list','RtoController@rtoRc_list');
	Route::get('/rto/rc/list/api/{type}','RtoController@rtoRcList_api');
	Route::get('/rto/rc/hsrp/list/api','RtoController@HsrpRcList_api');
	Route::get('/rto/get/rcnumber/{id}','RtoController@getRcNumber');
	Route::get('/rto/rc/update','RtoController@updateRcNumber');
	Route::get('/rto/rc/correction/request/{id}','RtoController@rcCorrectionRequest');
	Route::post('/rto/rc/correction/request','RtoController@rcCorrectionRequest_Db');
	Route::get('/rto/rc/correction/request/list/summary','RtoController@rcCorrectionRequest_list');
	Route::get('/rto/rc/correction/request/list/api','RtoController@rcCorrectionRequestList_api');
	Route::get('/rto/rc/correction/approve/{id}','RtoController@rcCorrectionRequest_Approve');
	Route::get('/rto/rc/correction/request/pay/{id}','RtoController@rcCorrectionRequest_Pay');
	Route::post('/rto/rc/correction/request/pay','RtoController@rcCorrectionRequestPay_DB');
	Route::get('/rto/rc/correction/request/pay/details/{id}','RtoController@rcCorrectionRequestPay_Detail');
	Route::get('/rto/rc/view/{id}','RtoController@RtoView');
	Route::post('/rto/update/delivery/status','RtoController@UpdateDeliveryStatus');

	Route::get('/sale/list','SaleController@sale_list');
	// Route::get('/sale/list/api','SaleController@sale_list_api');
	Route::get('/sale/view/{typde}','SaleController@sale_view');
	Route::get('/sale/list/api/{type}','SaleController@sale_list_api');
	Route::get('/sale/payment/list','SaleController@salePayment_list');
	Route::get('/sale/payment/list/api/{type}','SaleController@salePaymentList_api');
	Route::get('/sale/pay/{id}','SaleController@sale_pay');	
	Route::post('/sale/pay','SaleController@sale_pay_db');
	Route::get('/sale/pay_detail/{id}','SaleController@sale_pay_detail');
	Route::get('/sale/order/{id}','SaleController@sale_order');
	Route::post('/sale/order','SaleController@sale_order_db');



	// Route::get('/sale/otc','OTCSaleController@additional_services');
	// Route::post('/sale/otc','SaleController@additional_servicesDB');
	// Route::get('/sale/otc/search','SaleController@search_frame_number');
	// Route::get('/sale/otc/sale/accessories','SaleController@get_accessories');
	// Route::get('/sale/otc/sale/accessoriespartNo','SaleController@get_accessoriespartNo');
	// Route::get('/sale/additional/services/list','SaleController@additionalServices_list');
	// Route::get('/sale/additional/services/list/api','SaleController@additionalServicesList_api');
	// Route::get('/sale/additional/services/view/{id}','SaleController@additionalServices_view');
	// Route::get('/sale/additional/services/pay/{id}','SaleController@additionalServices_pay');
	// Route::post('/sale/additional/services/pay','SaleController@additionalServicesPay_Db');
	// Route::get('/sale/additional/services/pay/details/{id}','SaleController@additionalServicesPay_Details');


	Route::get('/sale/otcsale','OTCSaleController@OtcSale');
	Route::post('/sale/otcsale','OTCSaleController@OtcSale_DB');
	Route::get('/sale/otcsale/search','OTCSaleController@search_frame_number');
	Route::get('/sale/otc/sale/accessories','OTCSaleController@get_accessories');
	Route::get('/sale/otc/sale/accessoriespartNo','OTCSaleController@get_accessoriespartNo');
	Route::get('/sale/otcsale/list','OTCSaleController@OtcSale_list');
	Route::get('/sale/otcsale/list/api','OTCSaleController@OtcSaleList_api');
	Route::get('/sale/otcsale/view/{id}','OTCSaleController@OtcSale_view');
	// Route::get('/sale/otcsale/allow/pay/letter','OTCSaleController@PayLetter_allow');
	Route::get('/sale/otcsale/get/discount/{id}','OTCSaleController@getJpHondaDiscount');
	Route::get('/sale/otcsale/jphonda/discount','OTCSaleController@AddJpHonda_discount');

	Route::get('/sale/otcsale/pending/item/list','OTCSaleController@OtcSalePendingItem_list');
	Route::get('/sale/otcsale/pending/item/list/api','OTCSaleController@OtcSalePendingItem_api');
	Route::get('/sale/otcsale/get/pending/item','OTCSaleController@get_otcsale_pendingItem');
	Route::post('/sale/otcsale/pending/item/createotc','OTCSaleController@otcsale_pendingItemDB');


	// Route::get('/sale/additional/services/pay/{id}','SaleController@additionalServices_pay');
	// Route::post('/sale/additional/services/pay','SaleController@additionalServicesPay_Db');
	Route::get('/sale/otcsale/pay/details/{id}','OTCSaleController@OtcSalePay_Details');
	

	//Sale payment refund
	Route::get('/sale/refund/{id}','SaleController@sale_refund');	
	Route::post('/sale/refund','SaleController@sale_refund_db');


	// get API in sale
	Route::get('/get/customer','SaleController@getCustomer');
	Route::get('/get/aadhar_voter/customer','SaleController@getAadharVoterCustomer');
	Route::get('/get/all/model','SaleController@getAllModel');
	Route::get('/get/modelAllVariant','SaleController@getAllModelVariant');
	Route::get('/get/modelAllColorCode','SaleController@getAllModelColorCode');
	Route::get('/get/modelVariant','SaleController@getModelVariant');
	Route::get('/get/modelColorCode','SaleController@getModelColorCode');
	Route::get('/get/financerExecutive','SaleController@financerExecutive');


	// Payment 

	Route::get('/payment/request/list','PaymentController@PaymentRequest_list');
	Route::get('/payment/request/list/api','PaymentController@PaymentRequestList_api');
	Route::get('/payment/request/{id}','PaymentController@Payment_Request');
	Route::post('/payment/request','PaymentController@PaymentRequest_DB');
	Route::get('/payment/detail/{id}','PaymentController@Payment_Details');



	// sale payment confirmation
	Route::get('/sale/payment/confirmation/list','SaleController@paymentConfirmation_list');
	Route::get('/sale/payment/confirmation/list/api','SaleController@paymentConfirmationList_api');
	Route::get('/sale/payment/confirmation/received/{id}','SaleController@paymentConfirmation_received');
	Route::get('/sale/payment/confirmation/cancel/{id}','SaleController@paymentConfirmation_cancel');
	
	//sale redirection page 
	Route::get('/sale/add/pending/details/{id}','SaleController@fillPendingDetails');

	// sale cancel request
	Route::get('/cancel/sale/list','SaleController@cancelSale_list');
	Route::get('/cancel/sale/list/api','SaleController@cancelSale_list_api');
	Route::get('/sale/request/cancel/approve','SaleController@cancelSaleApprove');
	Route::get('/sale/request/cancel','SaleController@saleCancelRequest');
	
	//refund cancel sale
	Route::get('/cancelSale/refundPage/{saleId}','SaleController@refundMoney');
	Route::post('/cancelSale/refundPay','SaleController@refundMoneyDB');
	Route::post('/cancelSale/refundPay/onlineRequest','SaleController@refundRequestMoneyDB');

	// online payment request list 
	Route::get('/online/payment/request/list','SaleController@online_req_pay_list');
	Route::get('/online/payment/request/list/api','SaleController@online_req_pay_api');
	Route::post('/online/payment/request/pay','SaleController@online_req_pay_db');

	//security amount 
	Route::get('/sale/security/amount','SaleController@security_amount_create');
	Route::get('/sale/salenumber/search','SaleController@salenumber_search');
	Route::post('/sale/security/amount','SaleController@security_amount_create_DB');
	Route::get('/sale/security/amount/list','SaleController@security_amount_list');
	Route::get('/sale/security/amount/list/api','SaleController@security_amount_list_api');
	Route::get('/sale/security/amount/pay','SaleController@security_amount_pay');
	Route::post('/sale/security/amount/pay','SaleController@security_amount_pay_DB');
	Route::get('/sale/security/amount/pay/detail/{id}','SaleController@security_payment_details');
	Route::get('/sale/security/amount/refund','SaleController@security_amount_refund');



	// booking 
	Route::get('/booking','Booking@booking');
	Route::post('/booking','Booking@booking_db');
	Route::get('/booking/list','Booking@booking_list');
	Route::get('/booking/list/api','Booking@booking_list_api');
	Route::get('/booking/pay/{id}','Booking@booking_pay');
	Route::post('/booking/pay/','Booking@booking_pay_db');
	Route::get('/booking/pay/detail/{id}','Booking@booking_pay_detail');
	// Route::get('/booking/cancel','Booking@bookingCancelRequest');
	
	Route::get('/booking/get/modelVariant','Booking@getModelVariant');
	Route::get('/booking/get/modelColorCode','Booking@getModelColorCode');
	Route::get('/booking/get/product/info','Booking@productInfo');

	//booking cancel
	Route::get('/cancel/booking/list','Booking@cancelBooking_list');
	Route::get('/cancel/booking/list/api','Booking@cancelBooking_list_api');
	Route::get('/booking/cancel','Booking@bookingCancel');
	
	//refund cancel booking
	Route::get('/cancelBooking/refundPage/{saleId}','Booking@refundMoney');
	Route::post('/cancelBooking/refundPay','Booking@refundMoneyDB');
	Route::post('/cancelBooking/refundPay/onlineRequest','Booking@refundRequestMoneyDB');


	//Finance
	Route::get('/finance/sale/finance/list','FinanceController@saleFinancer_list');
	Route::get('/finance/sale/finance/list/api','FinanceController@SalefinancerList_api');
	Route::get('/finance/financer/list','FinanceController@financer_list');
	Route::get('/finance/financer/list/api','FinanceController@financerList_api');
	Route::get('/finance/sale/disbursement/{id}','FinanceController@getDisbursementAmount');
	Route::get('/finance/sale/disbursement/update/{id}','FinanceController@getDisbursementAmount_update');
	Route::get('/finance/detail/{id}','FinanceController@finance_details');
	// finance convert ?
	Route::post('/finance/sale/finance/list/convert','FinanceController@sale_finance_convert');

	Route::get('/finance/sale/finance/shortExcess','FinanceController@saleFinancerShortExcess_list');
	Route::get('/finance/sale/finance/shortExcess/api','FinanceController@saleFinancerShortExcessList_api');

	// Finance TA Adjustment
	Route::get('/finance/ta/adjustment','FinanceController@ta_adjustment');
	Route::post('/finance/ta/adjustment','FinanceController@ta_adjustmentDB');
	// get data
	Route::get('/finance/ta/adjustment/get/data','FinanceController@ta_getData');
	// finance payout list
	Route::get('/finance/sale/payout/list','FinanceController@finance_payout_list');
	Route::get('/finance/sale/payout/list/api','FinanceController@financePayOutList_api');
	// finance Interest list
	Route::get('/finance/sale/interest/list','FinanceController@finance_interest_list');
	Route::get('/finance/sale/interest/list/api','FinanceController@financeInterestList_api');
	// TA Payment List
	Route::get('/finance/ta/payment/list','FinanceController@finance_ta_payment_list');
	Route::get('/finance/ta/payment/list/api','FinanceController@finance_ta_paymentList_api');
	// TA Adjustment  List
	Route::get('/finance/ta/adjustment/list','FinanceController@finance_ta_adjustment_list');
	Route::get('/finance/ta/adjustment/list/api','FinanceController@finance_ta_adjustmentList_api');




	Route::get('/store/create','StoreController@create_store');
	Route::post('/store/create','StoreController@create_store_db');
	Route::get('/master/store/update/{id}','StoreController@update_store');
	Route::post('/master/store/update/{id}','StoreController@update_store_db');
	/*updating following pages*/
	Route::get('/admin/create', 'AdminController@create');
	Route::post('/admin/create', 'AdminController@create_db');
	Route::get('/admin/list','AdminController@admin');
    Route::get('/admin/view/{id}','AdminController@admin_view');
    Route::get('/admin/update/{id}','AdminController@admin_update');
    Route::post('/admin/update/{id}','AdminController@admin_update_db');
    Route::get('/admindata','AdminController@admindata');
    Route::get('/admin/permission/denied','AdminController@permission_denied');
    Route::get('/admin/permission/{id}','AdminController@permission');
    Route::post('/admin/setpermission','AdminController@setpermission');
	Route::get('/getadminpermission/{id}','AdminController@getadminpermission');
	Route::get('/admin/userupdate', 'AdminController@userupdate');
	Route::get('/admin/userupdate/type', 'AdminController@userupdate_db');
	// Route::get('/admin/update/password/{id}', 'AdminController@update_password');
	// Route::post('/admin/user/update/password', 'AdminController@UpdatePassword_DB');
	Route::get('/owner_manual', 'AdminController@owner_manual');
	
	Route::get('/view/profile/{id}','AdminController@admin_view_profile');
    Route::get('/profile/update/','AdminController@user_profile_update');


	Route::get('/stock/parts/create', 'StoreController@create_parts');
	Route::post('/stock/parts/create', 'StoreController@create_parts_db');
	Route::get('/stock/parts/list','StoreController@parts_list');
	Route::get('/stock/parts/list/api','StoreController@parts_list_api');

	Route::get('/store/list','StoreController@store_list');
	Route::get('/store/list/api','StoreController@store_list_api');

	//Route::get('/user/log','MastersController@userlog');
	//Route::get('/user/log/api','MastersController@logdata');

	// Route for view/blade file.
	Route::get('/product/create','Product@CreateProduct');
	// Route for export/download tabledata to .csv, .xls or .xlsx
	//Route::get('/product/create/{type}', 'Product@Product_ExportExcel');
	// Route for import excel data to database.
	//Route::get('/product/create','Product@CreateProduct_StoreDB');
	Route::get('/product/list','Product@productView');
	Route::get('/product/list/api/{lrn}','Product@productView_api');
	Route::get('/product/del','Product@productViewDel');
	Route::get('/product/get_basic_price/{id}','Product@getBasicPrice');
	Route::get('/product/get_duration/{id}','Product@getDuration');
	Route::get('/product/update_basic_price','Product@updateBasicPrice');
	Route::get('/product/update_duration','Product@updateDuration');
	Route::get('/product/product_list','Product@product_list');
	Route::get('/product/product_list/api','Product@product_list_api');
	// Route::get('/stock/list','Product@stockList');
	// Route::get('/stock/list/api','Product@stockList_api');
	Route::get('/stock/list','Product@stockListNew');
	Route::post('/stock/list/api','Product@stockListNew_api');
	Route::get('/stock/del/{type}','Product@stockListDel');
	Route::post('/sotck/list/update', 'Product@stockListUpdate');
	Route::post('/product/list/claim', 'Product@damageProduct');
	Route::get('/stock/idealstock/old', 'Product@idealStock_old');
	Route::get('/stock/idealstock_list/old/api', 'Product@idealstock_list_old');
	Route::get('/stock/stock_min_qty', 'StockAllocation@get_stock_minqty');
	Route::get('/stock/update_minqty/', 'StockAllocation@update_minqty');
	Route::get('/unload/addon/list','Product@unloadAddon_list');
	Route::get('/unload/addon/list/api','Product@unloadAddon_listapi');

	//
	// new ideal stock list create
	Route::get('/stock/idealstock/', 'Product@idealStock');
	Route::post('/stock/idealstock_list/api', 'Product@idealstock_list');  
	Route::post('/stock/update/minqty', 'Product@updateminqty');

	
	// battery
	Route::get('/battery/create', 'Product@create_battery');
	Route::post('/battery/create', 'Product@create_battery_db');
	Route::get('/battery/import', 'Product@import_battery');
	Route::post('/battery/import', 'Product@import_battery_db');
	Route::get('/battery/list','Product@battery_list');
	Route::get('/battery/getlist/{id}','Product@battery_get');
	Route::post('/battery/audit','Product@battery_audit');
	Route::get('/battery/update/{id}','Product@battery_update');
	Route::post('/battery/update/{id}','Product@battery_update_db');
	

	//Route::post('/battery/updateHtr/','Product@batteryHtrupdate');
	Route::get('/battery/updateOldHtr/','Product@batteryOldHtrUpdate');
	Route::get('/battery/list/api','Product@battery_list_api');
	Route::get('/oldbattery/list','Product@oldbattery_list');
	Route::get('/oldbattery/list/api','Product@oldbattery_list_api');
	//frame
	Route::get('/frame/list','Product@frame_list');
	Route::get('/frame/list/api','Product@frame_list_api');

	//Fuel 

	Route::get('/fuel/add', 'FuelController@add_fuel');
	Route::get('/fuel/get/type/{mode}','FuelController@getFuelType');
	Route::post('/fuel/add', 'FuelController@add_fuel_db');
	Route::get('/fuel/list','FuelController@fuel_list');
    Route::get('/fuel/list/api','FuelController@fuel_list_api');
    Route::get('/fuel/approval/{id}','FuelController@fuel_approve');
    Route::get('/fuel/reject/{id}','FuelController@fuel_reject');
    Route::get('/fuel/request', 'FuelController@fuel_request');
	Route::post('/fuel/request', 'FuelController@fuel_request_DB');
	Route::get('/fuel/request/list','FuelController@fuel_request_list');
    Route::get('/fuel/request/list/api','FuelController@fuel_request_list_api');
    Route::get('/fuel/request/approval/{id}','FuelController@fuel_request_approve');
    Route::get('/fuel/request/reject/{id}','FuelController@fuel_request_reject');
    Route::get('/fuel/stock/list','FuelController@fuel_stock_list');
    Route::get('/fuel/stock/list/api','FuelController@fuel_stock_list_api');
	
	
	// call summaryt
	
	// for sale
	// Route::get('/call/Sale','Call@forSale');
	// Route::get('/call/list/api/{tab}','Call@forSale_api');
	// Route::get('/call/Sale/view','Call@forSale_view');
	// Route::post('/call/Sale/updateCall','Call@updateCall_DB');
	// Route::post('/call/Sale/updateDelivery','Call@updateDelivery_DB');
	// Route::get('/call/Sale/nextcall','Call@nextCall');

	Route::get('/call/log/list','Call@callList');
	Route::get('/call/log/list/api/{tab}','Call@callList_api');
	Route::get('/call/log/view','Call@callList_view');
	Route::get('/call/log/view/rcdelivery/{id}','Call@callList_ViewRcdelivery');
	Route::get('/call/log/rcdelivery/service_book','Call@call_RcdeliveryServiceBook');
	Route::post('/call/log/updateCall','Call@callLog_update');
	Route::get('/call/log/nextcall','Call@nextCalllog');
	Route::post('/call/log/updateDelivery','Call@updateCallLogDelivery');
	Route::post('/call/log/updateDnd','Call@callLogUpdateDnd');
	
	// auto update call
	
	Route::post('/call/log/updateCall/auto','Call@callLog_auto_update');
	
	// call data assign to another user
	Route::post('/call/log/assign','Call@callLogAssign');

	Route::get('/service/call/log/view','Call@service_callList_view');
	Route::post('/service/call/log/booking','Call@service_call_book');
	// performance
	
	// auto call assign
	Route::get('/call/data/afterfifty/updateCall/auto','AutoAssignCallController@assignAferFifty');

	// call data import

	Route::get('/call/data/import','Call@callDataImport');
	Route::post('/call/data/import','Call@callDataImportDb');
	Route::get('/call/data/list','Call@callDataList');
	Route::get('/call/data/list/api/{tab}','Call@callDataList_api');
	Route::get('/call/data/view','Call@callData_view');
	Route::post('/call/data/updateCall','Call@callDataLog_update');
	Route::get('/call/data/nextcall','Call@nextCallDatalog');
	Route::post('/call/data/requestEnquiry','Call@callDataRequestEnquiry');
	Route::post('/call/data/serviceBooking','Call@callDataServiceBook');
	Route::post('/call/data/updateDnd','Call@callDataUpdateDnd');


	// auto call data update
	Route::post('/call/data/updateCall/auto','Call@callData_auto_update');


	Route::get('/call/data/view/check/sold','Call@callDataCheckSold');
	Route::post('/call/data/view/check/sold','Call@callDataCheckSoldDB');
	
	Route::post('/call/data/view/change_label','Call@callDataChangeLevelDB');
	Route::post('/call/data/view/cash_finance','Call@callDataCashFinacneDB');
	Route::post('/call/data/view/csdEnquiry','Call@callDataCsdEnquiryDB');
	Route::post('/call/data/view/serviceEnquiry','Call@callDataServiceEnquiryDB');
	Route::post('/call/data/view/insured','Call@callDataInsuredDB');
	Route::post('/call/data/view/saleEnquiry','Call@callDataSaleEnquiryDB');

	// call data transaction summary
	Route::get('/call/data/customer/{transactionType}','Call@transaction_by_customer');
	

	// Call Data Request Enquiry
	Route::get('/call/data/requestEnquiry/list','Call@callDataRequestEnquiryList');
	Route::get('/call/data/requestEnquiry/list/api','Call@callDataRequestEnquiryList_api');
	
	// call data assign to another user
	Route::post('/call/data/assign','Call@callDataAssign');

	// fcm notification
	Route::get('/fcm/push/calldata/notification', 'Call@push_calldata_notification');
	Route::get('/fcm/push/calling/notification', 'Call@push_calling_notification');

	// call setting
	Route::get('/setting/call/users', 'CallSettingController@callUsers');
	Route::get('/setting/call/users/api/{type}', 'CallSettingController@callUsers_api');
	Route::post('/setting/call/users/updateNoc', 'CallSettingController@callUsersUpdateNoc');
	Route::post('/setting/call/users/addUsers', 'CallSettingController@callUsersAddUsers');
	
	Route::get('/setting/call/users/priority', 'CallSettingController@callPriority');
	Route::post('/setting/call/users/priority', 'CallSettingController@callPriority_DB');
	Route::get('/setting/call/users/priority/get/calltype', 'CallSettingController@getCallType');
	Route::get('/setting/call/users/autoassign', 'CallSettingController@autoCallAssign');
	
	// manual call allowed setting
	Route::get('/call/setting/manualCallAllowed', 'CallSettingController@manualCallAllowed');
	Route::post('/call/setting/manualCallAllowed', 'CallSettingController@manualCallAllowed_DB');

	//rto performance
	Route::get('/performance/rto','Performance@rtoPerformance');
	Route::get('/performance/rto/api/{tab}','Performance@rtoPerformanceListApi');



	// PDI
	Route::get('/create/PDI','PDIController@createPDI');
	Route::post('/create/PDI','PDIController@createPDIDB');
	Route::get('/create/PDI/get/frame/details','PDIController@frameChange');
	Route::get('/PDI/summary','PDIController@pdiSummary');
	Route::get('/PDI/summary/api','PDIController@pdiSummary_api');
	Route::get('/PDI/approve/summary','PDIController@pdiSummaryApprove');
	Route::get('/PDI/approve/summary/{id}','PDIController@pdiSummaryApprove_api');
	Route::get('/PDI/partnumberget','PDIController@PartnumberGet');
	Route::get('/PDI/view/{id}','PDIController@pdiView');

	//edit PDI
	Route::get('/PDI/edit/{id}','PDIController@pdiEdit');
	Route::post('/PDI/edit/{id}','PDIController@pdiEdit_db');

	//update Invoice PDI
	Route::get('/PDI/update/invoice/{id}','PDIController@pdiUpdateInvoice');
	Route::post('/PDI/update/invoice/{id}','PDIController@pdiUpdateInvoiceDB');

	//Services Module
	Route::get('/service/guard/dashboard','Service@guradDashboard');
	Route::get('/service/entry/list','Service@frameEntryList');
	Route::get('/service/entry/list/api','Service@frameEntryList_api');
	Route::get('/service/create/tag','Service@createTag');
	Route::get('/service/search/city','Service@searchcity');
	Route::post('/service/create/tag','Service@createTagDB');
	
	//Jobcard 
	Route::get('/service/jobcard/list','JobCardController@jobCardList');
	Route::get('/service/jobcard/list/api','JobCardController@jobCardList_api');
	Route::get('/service/create/jobcard/{id}','JobCardController@jobcard_screen1');
	Route::get('/service/create/jobcard/screen2/{id}','JobCardController@jobcard_screen2');
	Route::get('/service/create/jobcard/screen3/{id}','JobCardController@jobcard_screen3');
	Route::post('/service/create/jobcard/screen1/{id}','JobCardController@JobCardScreen1_DB');
	Route::post('/service/create/jobcard/screen2/{id}','JobCardController@JobCardScreen2_DB');
	Route::post('/service/create/jobcard/screen3/{id}','JobCardController@JobCardScreen3_DB');
	Route::post('/service/upload/audio','JobCardController@SaveAudio');
	// Route::post('/service/create/jobcard/{id}','JobCardController@createJobCardDB');
	Route::get('/service/check/jobcard','JobCardController@checkAMC');
	Route::get('/service/amc/product','JobCardController@AmcProduct');
	Route::get('/service/check/hjc','JobCardController@checkHJC');
	Route::get('/service/check/list','JobCardController@GetServiceCheck_list');
	Route::get('/service/update/frame/{id}','JobCardController@updateFrame');
	Route::post('/service/update/frame/{id}','JobCardController@updateFrame_DB');

	Route::get('/service/create/booking','Service@createBooking');
	// Route::post('/service/create/booking','Service@createPrebooking_DB');
	Route::get('/service/booking/list','Service@ServiceBooking_list');
	Route::get('/service/booking/list/api','Service@ServiceBookingList_api');

	Route::get('/service/booking/pickuptime','Service@BookingPickuptime');
	Route::get('/service/booking/droptime','Service@BookingDroptime');
	Route::get('/service/view/jobcard/{id}','Service@viewJobCard');

	// floor floorSupervisorPartAssign
	Route::get('/service/floor/supervisor','Service@bayList');
	Route::get('/service/floor/supervisor/api','Service@bayList_api');
	Route::get('/service/floor/supervisor/buy/work/start','Service@floorSupervisorBuyWorkStart');
	Route::get('/service/floor/supervisor/buy/work/end','Service@floorSupervisorBuyWorkEnd');
	Route::get('/service/floor/supervisor/customer/confirmation','Service@floorSupervisorCustomerConfirmation');
	Route::get('/service/part/request/{id}','Service@partRequest');
	Route::post('/service/part/request/{id}','Service@partRequest_DB');
	Route::get('/service/customer/confirmation','Service@CustomerConfirmation');
	Route::get('/service/floor/supervisor/jobcard','Service@floorSupervisorJobcard');
	Route::get('/service/floor/supervisor/servicecharge','Service@floorSupervisorServicecharge_DB');
	Route::get('/service/floor/supervisor/servicechargecheck','Service@floorSupervisorServicechargeCheck');

	// schedule task 
	Route::post('/service/floor/supervisor/schedule/task','Service@floorSupervisorScheduleTask');
	Route::get('/service/floor/supervisor/get/schedule/task','Service@get_schedule_bay_alloc');



	Route::get('/service/final/inspection/list','Service@fiList');
	Route::get('/service/final/inspection/list/api','Service@fi_list_api');
	Route::get('/service/final/inspection/wash/{id}','Service@fiWash');
	Route::get('/service/final/inspection/get/{id}','Service@getJobCard');
	Route::get('/service/final/inspection/approve','Service@fiApprove');
	Route::get('/service/hirise/list','Service@serviceHirise_list');
	Route::get('/service/hirise/list/api','Service@serviceHiriseList_api');
	Route::get('/service/hirise/get/{id}','Service@getJobCard');
	Route::get('/service/hirise/update','Service@updareServiceHirise');
	Route::get('/service/payment/list','Service@servicePayment_list');
	Route::get('/service/payment/list/api','Service@servicePaymentList_api');
	Route::get('/service/payment/{id}','Service@service_payment');
	Route::post('/service/payment','Service@servicePayment_DB');
	Route::get('/service/payment/detail/{id}','Service@servicePayment_detail');
	Route::get('/service/get/discount/{id}','Service@getServiceDiscount');
	Route::get('/service/add/discount','Service@serviceAdd_discount');
	Route::get('/service/discount/list','Service@serviceDiscount_list');
	Route::get('/service/discount/list/api','Service@serviceDiscountList_api');
	Route::get('/service/discount/approve','Service@serviceDiscount_approve');
	Route::get('/service/charge/detail/{id}','Service@serviceCharge_detail');

	Route::get('/service/part/issue/list','Service@PartIssue_list');
	Route::get('/service/part/issue/list/api','Service@PartIssueList_api');
	Route::get('/service/part/issue/{id}','Service@Part_issue');
	Route::get('/service/part/issue/get/partinfo','Service@PartStoreGetPartInfo');
	Route::post('/service/part/issue','Service@PartIssue_DB');

	//Part Request Approval

	Route::get('/service/partrequest/approval/list','Service@PartRequstApproval_list');
	Route::get('/service/partrequest/approval/list/api','Service@PartRequstApproval_list_api');
	Route::get('/service/partrequest/approval/list/data/{id}','Service@PartRequstApproval_list_data');
	Route::get('/service/partrequest/approval/list/approve','Service@PartRequstApproval_DB');


	//Allow Pay Letter
	Route::get('/service/pay/letter/list','Service@servicePayLetter_list');
	Route::get('/service/pay/letter/list/api','Service@servicePayLetterList_api');
	Route::get('/service/allow/pay/letter','Service@servicePayLetter_allow');


	//JpHonda Discount
	Route::get('/service/jphonda/discount/list','Service@JpHondaDiscount_list');
	Route::get('/service/jphonda/discount/list/api','Service@JpHondaDiscountList_api');
	Route::get('/service/jphonda/discount','Service@JpHonda_discount');


	//store supervisor
	
	Route::get('/service/stock/part/request/list','Service@partRequest_list');
	Route::get('/service/stock/part/request/list/api','Service@PartRequestList_api');
	Route::get('/service/store/assign/part/{id}','Service@servicePartAssign');
	Route::get('/service/store/supervisor/get/partinfo','Service@storeSupervisorGetPartInfo');
	Route::post('/service/store/assign/part/{id}','Service@servicePartAssign_DB');

// Service Out 
	Route::get('/service/frame/out/list','Service@frameOutList');
	Route::get('/service/frame/out/list/api','Service@frameOutListApi');
	Route::get('/service/done/list','Service@serviceDone_list');
	Route::get('/service/done/list/api','Service@serviceDoneList_api');
	Route::get('/service/done/','Service@service_done');
	Route::get('/service/history/list','Service@serviceHistory_list');
	Route::get('/service/history/list/api','Service@serviceHistoryList_api');
	Route::get('/service/history/update','Service@serviceHistory_update');
	Route::get('/service/update/part/tag/{id}','Service@UpdatePart_tag');
	Route::get('/service/part/tag/get','Service@getPart_tag');
	Route::get('/service/tag/update','Service@UpdatePartTag_DB');


// Service Test ride
	Route::get('/service/test/ride/list','Service@testRideList');
	Route::get('/service/test/ride/list/api','Service@testRideList_api');
	//Route::get('/service/test/ride/get/{id}','Service@testRideGet');
	Route::get('/service/test/ride','Service@testRideOut');
	Route::get('/service/test/ride/in/{id}','Service@testRideIn');
	Route::get('/service/test/ride/view/{id}','Service@testRideView');
	Route::get('/service/feedback','Service@feedback');
	Route::get('/service/crm/feedback','Service@crmFeedback');
	Route::get('/service/feedback/list','Service@feedbackList');
	Route::get('/service/feedback/list/api','Service@feedbackListApi');

// Service Delay

	Route::get('/service/delay/list','Service@JcdelayList');
	Route::get('/service/delay/list/api','Service@JcdelayList_api');	
	Route::get('/service/delay/comment','Service@JcdelayComment');	



// Accidental Service 
	Route::get('/accidental/jobcard/list','Accidental@AccidentalJobCardList');
	Route::get('/accidental/jobcard/list/api','Accidental@AccidentalJobCardList_api');
	Route::get('/accidental/part/request/{id}','Accidental@AccidentalPartRequest');
	Route::post('/accidental/part/request/{id}','Accidental@AccidentalPartRequest_DB');
	Route::get('/accidental/part/remove','Accidental@AccidentalPartRequest_Remove');
	Route::get('/accidental/part/request','Accidental@PartRequest_List');
	Route::get('/accidental/part/request/list/api','Accidental@PartRequestList_Api');
	Route::get('/accidental/assign/part/{id}','Accidental@PartAssign');
	Route::post('/accidental/assign/part/{id}','Accidental@PartAssign_DB');
	Route::get('/accidental/part/approve','Accidental@Part_Approve');
	Route::get('/accidental/part/approve/list/{id}','Accidental@PartApprove_List');
	// Route::get('/accidental/part/approve/list/api','Accidental@PartApproveList_Api');
	Route::get('/accidental/part/approve/{id}','Accidental@Part_Approve');
	Route::get('/accidental/part/customer/approve/{id}','Accidental@PartCustomer_Approve');
	Route::get('/accidental/part/return/{id}','Accidental@Part_Return');
	Route::get('/accidental/part/receive/{id}','Accidental@Part_Receive');
	Route::get('/accidental/part/receive','Accidental@PartReceive_List');
	Route::get('/accidental/part/receive/list/api','Accidental@PartReceiveList_Api');
	Route::get('/accidental/part/issue','Accidental@Part_Issue');
	Route::get('/accidental/jobcard/approve/list','Accidental@JobcardApprove_List');
	Route::get('/accidental/jobcard/approve/list/api','Accidental@JobcardApproveList_Api');
	Route::get('/accidental/part/update_details','Accidental@AccidentalPartUpdateDetails');
	Route::get('/accidental/part/update_status','Accidental@AccidentalPartUpdateStatus');
	Route::get('/accidental/part/update_start','Accidental@AccidentalPartUpdateStart');

	Route::get('/accidental/part/estimation/{id}','Accidental@PartEstimation');
	Route::post('/accidental/part/estimation/{id}','Accidental@PartEstimation_DB');


// bay manage
	Route::get('/bay/create','BayController@createBay');
	Route::post('/bay/create','BayController@createBay_DB');
	Route::get('/bay/list','BayController@bayList');
	Route::get('/bay/list/api','BayController@bayList_api');
	Route::get('/bay/update/{id}','BayController@bayUpdate');
	Route::post('/bay/update/{id}','BayController@bayUpdate_DB');
	Route::get('/bay/allocation/manual/old','Service@bay_alloc_graph_old');
	Route::get('/service/bay/allocation/manual','Service@bay_alloc_graph');
	Route::post('/bay/allocation/manual','Service@bay_alloc_graph_DB');
	Route::post('/bay/allocation/manual/delete','Service@bay_alloc_graph_delete_DB');
	Route::post('/bay/allocation/manual/edit','Service@bay_alloc_graph_edit_DB');
	Route::post('/bay/leave','BayController@bayLeave');
	
	Route::get('/service/bay/rearrange','Service@re_arrange_bay');

// Warranty
	Route::get('/service/warranty/list','Service@warranty_list');
	Route::get('/service/warranty/list/api','Service@warrantyList_api');
	Route::get('/service/part/warranty/{id}','Service@warranty_part');
	Route::get('/service/warranty/update','Service@warrantyPart_update');

	Route::get('/service/warranty/approval/list','Service@warrantyApprove_list');
	Route::get('/service/warranty/approval/list/api','Service@warrantyListApprove_api');
	Route::get('/service/warranty/part/approve','Service@warrantyPart_approve');

	Route::get('/service/warranty/htr/list','Service@warrantyHtr_list');
	Route::get('/service/warranty/htr/list/api','Service@warrantyHtrList_api');
	Route::get('/service/part/htr/get','Service@getPart_htr');
	Route::get('/service/htr/update','Service@UpdatePartHtr_DB');

	Route::get('/service/warranty/invoice/create','Service@warrantyInvoice_create');
	Route::post('/service/warranty/invoice/create','Service@warrantyInvoiceCreate_DB');
	Route::get('/service/warranty/invoice/list','Service@warrantyInvoice_list');
	Route::get('/service/warranty/invoice/list/api','Service@warrantyInvoiceList_api');
	// Route::get('/service/get/warranty/invoice','Service@warrantyInvoice_get');
	Route::get('/service/warranty/invoice/update/{id}','Service@warrantyInvoice_update');
	Route::post('/service/warranty/invoice/update','Service@warrantyInvoiceUpdate_DB');
	Route::get('/service/warranty/approve','Service@warranty_approve');
	Route::get('/service/warranty/reject','Service@warranty_reject');


	//Service Booking
	Route::get('/service/pre/booking/list','Service@PreBooking_list');
	Route::get('/service/pre/booking/list/api','Service@PreBookingList_api');
	Route::get('/service/create/booking/{id}','Service@createBooking');
	Route::post('/service/create/booking','Service@createBooking_DB');

	//hr 
	Route::get('/hr/create/user','HrController@createuser');
	Route::post('/hr/create/user','HrController@createuser_DB');
	Route::get('/hr/user/list','HrController@user_list');
	Route::get('/hr/user/list/api','HrController@user_data');
	Route::get('/hr/user/inactive/list/api','HrController@user_inactive_api');
	Route::get('/hr/user/resigned/list/api','HrController@user_resigned_api');
	Route::get('/hr/user/activeuser','HrController@activeuser');
	Route::get('/hr/user/inactiveuser','HrController@inactiveuser');
	Route::get('/hr/user/resigneduser','HrController@resigneduser_list');

	Route::get('/hr/user/update/{id}','HrController@user_update');
	Route::post('/hr/user/update/{id}','HrController@user_update_Db');
	Route::get('/hr/user/documentupdate/{id}','HrController@documentupdate');
	Route::post('/hr/user/documentupdate/{id}','HrController@documentupdate_DB');
	Route::get('/hr/user/otherdocumentupdate/{id}','HrController@otherdocumentupdate');
	Route::post('/hr/user/otherdocumentupdate/{id}','HrController@otherdocumentupdate_DB');
	Route::get('/hr/filter/user/api/', 'HrController@filter_user_api');
	Route::get('/payroll/user/salaryupdate/{id}','PayrollController@update_salary'); 
	Route::post('/payroll/user/salaryupdate/{id}','PayrollController@update_salary_DB'); 
	Route::get('/hr/user/printicard/{id}','HrController@user_printicard');
	Route::get('/hr/user/salaryadvance','HrController@salaryadvance');
	Route::post('/hr/user/salaryadvance','HrController@salaryadvance_DB');
	Route::get('/payroll/salaryadvance/list','PayrollController@salaryadvance_list');
	Route::get('/payroll/salaryadvance/list/api','PayrollController@salaryadvance_data');
	Route::get('/payroll/salaryadvance/pending/','PayrollController@salaryadvance_pending');
	Route::get('/payroll/salaryadvance/completed/','PayrollController@salaryadvance_completed');
	Route::get('/payroll/salaryadvance/list/completed/api','PayrollController@salaryadvance_completed_list');

	Route::post('/payroll/salaryadvance/approve/{id}', 'PayrollController@salaryadvance_approve');
	Route::get('/hr/leave/create', 'HrController@create_leave');
	Route::get('/hr/leave/balance/check/api', 'HrController@leave_balance_check');
	Route::post('/hr/leave/create', 'HrController@create_leaveDb');
	Route::get('/hr/leave/list', 'HrController@leave_list');
    Route::get('/hr/leave/list/api', 'HrController@leave_list_api');
    Route::post('/hr/leave/approve/{id}', 'HrController@leave_approve');
	Route::get('/hr/leave/print/{id}','HrController@leave_print');
	Route::get('/hr/leave/setting/list', 'HrController@leave_setting_list');
    Route::get('/hr/leave/setting/list/api', 'HrController@leave_setting_list_api');
    Route::get('/hr/setting', 'HrController@setting');
    Route::post('/hr/setting', 'HrController@setting_Db');
    Route::get('/hr/user/attendance', 'HrController@attendance');
    Route::post('/hr/user/attendance', 'HrController@attendance_Db');
    Route::get('/hr/user/attendance/list', 'HrController@attendance_list');
    Route::get('/hr/user/attendance/list/api', 'HrController@attendance_list_api');

    
    Route::get('/export/data/report/attendance','ExportController@export_data_report_attendance');
    Route::post('/export/reportAttendance','ExportController@reportAttendance');

    Route::get('/export/data/attendance','ExportController@export_data_attendance');
    Route::post('/export/attendance','ExportController@attendance');

    Route::get('/export/data/employee/attendance','ExportController@export_data_employee_attendance');
    Route::post('/export/EmployeeAttendance','ExportController@EmployeeAttendance');

    Route::get('/export/data/user','ExportController@export_data_user');
    Route::post('/export/user','ExportController@user');

    Route::get('/getTableData/{table}/{cloumn}','ExportController@getTableData');


    Route::get('/hr/user/nop', 'HrController@nop');
    Route::post('/hr/user/nop', 'HrController@nop_Db');
    Route::get('/hr/user/nop/list', 'HrController@nop_list');
    Route::get('/hr/user/nop/list/api', 'HrController@nop_list_api');
    Route::post('/hr/user/nop/approve/{id}', 'HrController@nop_approve');
    Route::get('/hr/user/penalty/import', 'HrController@penalty');
    Route::post('/hr/user/penalty/import', 'HrController@penalty_Db');
    Route::get('/hr/user/penalty/form', 'HrController@penaltyform');
    Route::post('/hr/user/penalty/form', 'HrController@penaltyform_Db');
    Route::get('/hr/filter/penalty/api/', 'HrController@filter_penalty_api');
    Route::get('/hr/user/monthlysalary/{id}','HrController@monthlysalary');
    Route::get('/hr/user/get/salary','HrController@Usermonthlysalary');

    Route::get('/hr/user/reported/attendance/list', 'HrController@reported_attendance_list');
    Route::get('/hr/user/reported/attendance/list/api', 'HrController@reported_attendance_list_api');
    Route::get('/hr/employee/attendance/list', 'HrController@employee_attendance_list');
    Route::get('/hr/employee/attendance/list/api', 'HrController@employee_attendance_list_api');
    Route::get('/hr/user/penalty/master/list','HrController@penaltymaster_list');
	Route::get('/hr/user/penalty/master/list/api','HrController@penaltymaster_api');
	Route::get('/payroll/user/import/salary', 'PayrollController@ImportSalary');
    Route::post('/payroll/user/import/salary', 'PayrollController@ImportSalary_DB');

    Route::get('/payroll/user/salary/list', 'PayrollController@salary_list');
    Route::get('/payroll/user/salary/list/api', 'PayrollController@salary_list_api');
    Route::get('/payroll/user/salary/view/{id}','PayrollController@salary_view');

    Route::get('/hr/calenderpage', 'HrController@calenderpage');
    Route::get('/hr/employee/leave/list', 'HrController@EmployeeLeave_list');
    Route::get('/hr/employee/leave/list/api', 'HrController@leaveList_api');

    Route::get('/hr/office/duty', 'HrController@OfficeDuty');
    Route::post('/hr/office/duty', 'HrController@OfficeDuty_DB');
    Route::get('/hr/office/duty/list', 'HrController@OfficeDutyList');
    Route::get('/hr/office/duty/list/api', 'HrController@OfficeDutyList_api');


    Route::get('/payroll/increment', 'PayrollController@PayrollIncrement');
    Route::post('/payroll/increment', 'PayrollController@PayrollIncrement_DB');
    Route::get('/payroll/increment/list', 'PayrollController@PayrollIncrement_list');
    Route::get('/payroll/increment/list/api', 'PayrollController@PayrollIncrementList_api');
    Route::get('/hr/user/resignation','HrController@user_resignation_DB');
    Route::get('/hr/userinactive', 'HrController@user_inactive');
    Route::get('/hr/user/attendance/nop','HrController@user_attendance_nop_DB');
    Route::get('/hr/user/icard/issue','HrController@user_icard_issue');
    Route::get('/hr/user/icard/issueagain','HrController@user_icard_issueagain');
    Route::get('/hr/user/reporting/attendance/nop','HrController@user_reporting_attendance_nop_DB');
    Route::get('/hr/user/resigned/{id}','HrController@ResignedUser');
    Route::post('/hr/user/resigned/{id}','HrController@ResignedUser_DB');
    Route::get('/hr/user/overtime/list', 'HrController@overtime_list');
    Route::get('/hr/user/overtime/list/api', 'HrController@overtime_list_api');
    Route::post('/hr/user/overtime/approve', 'HrController@overtime_approve');

    Route::get('/hr/employee/leave/balance/list', 'HrController@EmployeeLeaveBalance_list');
    Route::get('/hr/employee/leave/balance/list/api', 'HrController@EmployeeLeaveBalancelist_api');
    Route::get('/hr/pl/balance/list', 'HrController@BalancePl_list');
    Route::get('/hr/pl/balance/list/api', 'HrController@BalancePllist_api');

    Route::get('/hr/user/leave/balance/import', 'HrController@leave_balance');
    Route::post('/hr/user/leave/balance/import', 'HrController@leave_balance_Db');

    Route::get('/hr/dashboard','HrController@dashboard');
    Route::get('/hr/user/rejoin','HrController@user_rejoin_DB');

    Route::get('/hr/user/late/earlygoing/list', 'AttendanceController@late_earlygoing_list');
    Route::get('/hr/user/late/earlygoing/list/api', 'AttendanceController@late_earlygoing_list_api');
    Route::get('/hr/user/late/earlygoing/approve', 'AttendanceController@late_earlygoing_approve');

	// Setting
	//Setting Form View
	Route::get('/settings', 'MastersController@setting');
	//Setting Form insert
	Route::post('/setting/addform', 'MastersController@settingaddform');

	Route::get('/setting/email', 'MastersController@setting_email');
	Route::post('/setting/email', 'MastersController@setting_email_DB');

	// incentive
	Route::get('/incentive/program/{incentive_type}','IncentiveController@incentive');
	Route::post('/incentive/program/{incentive_type}','IncentiveController@incentive_DB');
	Route::get('/incentive/program/run/cron','IncentiveController@add_incentive_cron');
	Route::get('/incentive/program/summary/list','IncentiveController@incentive_list');
	Route::get('/incentive/program/list/api','IncentiveController@incentive_list_api');
	//assets

	Route::get('/assets/category', 'AssetController@create_assets_category');
	Route::post('/assets/category', 'AssetController@create_assets_categoryDb');
	Route::get('/assets/category/list','AssetController@assets_category_list');
	Route::get('/assets/category/list/api','AssetController@assets_category_list_api');

	Route::get('/assets/create', 'AssetController@create_assets');
	Route::post('/assets/create', 'AssetController@create_assetsDb');
	Route::get('/assets/validate/bill/no', 'AssetController@assets_unique_bill_no');
	Route::get('/assets/list', 'AssetController@assets_list');
	Route::get('/assets/list/api', 'AssetController@assets_list_api');
	Route::get('/assets/view/{id}', 'AssetController@assets_view');
	Route::get('/assets/edit/{id}', 'AssetController@update_assets');
	Route::post('/assets/edit/{id}', 'AssetController@update_assetsDb');
	Route::get('/assets/assign/employee', 'AssetController@asset_issue_to_employee');
	Route::post('/assets/assign/employee', 'AssetController@asset_issue_to_employeeDb');
	Route::get('/assets/filter/assetcode/api', 'AssetController@filter_asset_code_api');
	Route::get('/assets/filter/user/api/', 'AssetController@filter_user_api');
	Route::get('/assets/assign/employee/list','AssetController@asset_issue_to_employee_list');
	Route::get('/assets/assign/employee/list/api', 'AssetController@asset_issue_to_employee_api');

	Route::get('/assets/disposal','AssetController@asset_disposal');
	Route::post('/assets/disposal','AssetController@asset_disposal_db');
	Route::get('/assets/disposal/list','AssetController@asset_disposal_list');
	Route::get('/assets/disposal/list/api','AssetController@asset_disposal_list_api');
	//asset issue
	Route::get('/assets/assign/generate/form', 'AssetController@asset_assign_form');

	// asset return
	Route::get('/asset/return/{id}', 'AssetController@return_asset');
	Route::post('/asset/return/{id}', 'AssetController@return_asset_db');


	// approval settings
	Route::get('/approval', 'ApprovalController@approval_list');
	Route::get('/approval/api', 'ApprovalController@approval_list_api');
	Route::get('/get/approval/info', 'ApprovalController@approval_list_info');
	Route::post('/approval/approve', 'ApprovalController@newapproveDB');
	Route::post('/approval/cancel', 'ApprovalController@newcancelDB');
	
	Route::get('/setting/approval/list', 'ApprovalController@approvalSetting_list');
	Route::get('/setting/approval/list/api', 'ApprovalController@approvalSettingList_api');
	Route::get('/setting/approve/update/{id}', 'ApprovalController@approvalSetting_update');
	Route::post('/setting/approve/update', 'ApprovalController@approvalSettingUpdate_DB');

	// task
	Route::get('/task/create', 'TaskController@create_task');
	Route::post('/task/create', 'TaskController@create_task_DB');
	Route::get('/task/list', 'TaskController@task_list');
	Route::get('/task/list/api', 'TaskController@task_list_api');
	Route::get('/task/recurrence/create', 'TaskController@create_recurrenceTask');

	Route::get('/task/detail/list', 'TaskController@task_detail_list');
	Route::get('/task/detail/list/api', 'TaskController@task_detail_list_api');
	Route::get('/task/detail/confirmation', 'TaskController@task_detailConfirmation');
	Route::post('/task/review/add', 'TaskController@update_review_db');
	Route::post('/task/status/update', 'TaskController@update_taskstatus_db');

	Route::get('/employee/task/list', 'TaskController@employee_task_list');
	Route::get('/employee/task/list/api', 'TaskController@employee_task_list_api');
	Route::get('/employee/task/recurrence/stop', 'TaskController@task_recurrenceStop');
	Route::get('/employee/task/detail/list', 'TaskController@employee_task_detail_list');
	Route::get('/employee/task/detail/list/api', 'TaskController@employee_task_detail_list_api');
	Route::get('/employee/task/detail/assignedtask/','TaskController@employee_assignedtask');
	Route::get('/employee/task/detail/mytask/','TaskController@employee_mytask');
	Route::get('/employee/assignedtask/list/api', 'TaskController@employee_assignedtask_list_api');
	Route::get('/employee/mytask/statusupdate','TaskController@employee_mytask_statusupdate');
	Route::get('/employee/assignedtask/statusupdate','TaskController@employee_assignedtask_statusupdate');

	//uniform
	Route::get('/uniform/create', 'UniformController@create_uniform');
	Route::post('/uniform/create', 'UniformController@create_uniformDb');
	Route::get('/uniform/validate/bill/no', 'UniformController@uniform_unique_bill_no');
	Route::get('/uniform/list', 'UniformController@uniform_list');
	Route::get('/uniform/list/api', 'UniformController@uniform_list_api');
	Route::get('/uniform/issue/employee', 'UniformController@uniform_issue_to_employee');
	Route::post('/uniform/issue/employee', 'UniformController@uniform_issue_to_employeeDb');
	Route::get('/uniform/filter/uniformcode/api', 'UniformController@filter_uniform_code_api');
	Route::get('/uniform/filter/user/api/', 'UniformController@filter_user_api');

	Route::get('/uniform/issued/employee/list','UniformController@uniform_issue_to_employee_list');
	Route::get('/uniform/issued/employee/list/api', 'UniformController@uniform_issue_to_employee_api');
	Route::get('/uniform/view/{id}', 'UniformController@uniform_view');
	Route::get('/uniform/edit/{id}', 'UniformController@update_uniform');
	Route::post('/uniform/edit/{id}', 'UniformController@update_uniformDb');

	// hsrp
	Route::get('/hsrp/request','HsrpController@Hsrp_request');
	Route::post('/hsrp/request','HsrpController@Hsrp_request_DB');
	Route::get('/hsrp/request/list', 'HsrpController@hsrp_request_list');
	Route::get('/hsrp/request/list/api', 'HsrpController@hsrp_request_list_api');
	Route::get('/hsrp/challan/details/{id}','HsrpController@HsrpChallanDetails');
	Route::post('/hsrp/challan/details/{id}','HsrpController@HsrpChallanDetails_DB');
	Route::get('/hsrp/status/verify/{id}','HsrpController@HsrpVerification');
	Route::post('/hsrp/status/verify/{id}','HsrpController@HsrpVerification_DB');
	Route::get('/hsrp/request/view/{id}','HsrpController@HsrpRequestView');
	Route::get('/hsrp/request/update/{id}','HsrpController@HsrpRequest_Update');
	Route::post('/hsrp/request/update/{id}','HsrpController@HsrpRequestUpdate_DB');
	Route::get('/hsrp/challan/detail/delete/{id}','HsrpController@HsrpChallanDetails_delete');	


	//challan
	Route::get('/pay/challan/request','ChallanController@get_challan_status');
	Route::post('/pay/challan/request','ChallanController@get_challan_status_DB');
	Route::get('/pay/challan/request/list','ChallanController@get_challan_status_list');
	Route::get('/pay/challan/request/list/api','ChallanController@get_challan_status_list_api');
	Route::get('/pay/challan/request/status/details','ChallanController@get_challan_status_Details');
	Route::get('/pay/challan/request/status/verify','ChallanController@challan_status_verify');
	Route::get('/pay/challan/request/status/update','ChallanController@challan_status_update');
	Route::get('/pay/challan/number/check','ChallanController@checkChallan_number');
	Route::get('/pay/challan/request/view/{id}','ChallanController@challan_status_view');


	// user log
	Route::get('/user/log','UserController@userlog');
    Route::get('/user/log/api','UserController@logdata');



});

//Auth::routes();

Route::group(['prefix' => 'admin'], function() {
    Route::auth();
    Route::get('/logout', function(){
	   Auth::logout();
	   return Redirect::to('admin/login');
	});
});