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
Route::get('/', function () {
    return redirect(app()->getLocale());
});

Route::get('/en', function() {
	session(['locale' => 'en']);
    return back();
})->name('en');
Route::get('/hi', function() {
	session(['locale' => 'hi']);
    return back();
})->name('hi');

Route::group(['middleware' => ['setlocale'],'namespace'=>'front',
				'prefix' => ''], function () {

	Route::get('/','HomeController@index')->name('home');
	Route::get('/home','HomeController@index')->name('home');
	
	Route::post('/web/checkServiceDueDate','HomeController@checkServiceDueDate')->name('checkServiceDueDate');
	Route::post('/web/checkInsuranceDueDate','HomeController@checkInsuranceDueDate')->name('checkInsuranceDueDate');
	Route::post('/web/regInsuranceBooking','HomeController@regInsuranceBooking')->name('regInsuranceBooking');
	//static page
	Route::get('/aboutus', 'StaticPagesController@about_us');
	Route::get('/privacypolicy', 'StaticPagesController@privacy_policy');
	Route::get('/career', 'StaticPagesController@career');
	Route::get('/contactus', 'StaticPagesController@contact_us');
	Route::post('/contactus', 'StaticPagesController@contact_us_Db');
	Route::get('/testimonial', 'StaticPagesController@testimonial');
	Route::get('/insurancequotation', 'StaticPagesController@insurance_quotation');
	Route::post('/insurancequotation', 'StaticPagesController@insurance_quotation_Db');

	Route::get('/bestdeal','BestDealController@best_deal_inventory');
	Route::get('/bestdeal/old','BestDealController@bkp');
	Route::get('/best/deal/inventory/api','BestDealController@best_deal_inventory_api');
	Route::get('/bestdealinventory/otp','BestDealController@best_deal_inventory_otp');
	Route::get('/bestdealinventory/otp_match','BestDealController@best_deal_inventory_otpmatch');
	Route::get('/bestdeal/filter/data','BestDealController@BestdealFilterData');
	Route::get('/bestdeal/fetch/data','BestDealController@BestdealFetchData');
	Route::get('/bestdeal/get/details','BestDealController@BestdealGetDetails');

	Route::get('/vehicle/digital/quotation', 'VehicleController@vehicle');
	Route::get('/vehicle/get/modelVariant','VehicleController@getModelVariant');
	Route::get('/vehicle/get/modelColorCode','VehicleController@getModelColorCode');
	Route::get('/vehicle/get/product/info','VehicleController@productInfo');
	Route::get('/vehicle/get/accessories','VehicleController@findAccessories');
	Route::get('/vehicle/get/financerExecutive','VehicleController@financerExecutive');

	Route::get('/product','ProductController@product_list');
	Route::get('/product/list/api','ProductController@product_list_api');
	Route::get('/product/fetch_data','ProductController@fetch_data');
	Route::get('/product/search_value','ProductController@search_value');
	Route::get('/product/all_filter_data','ProductController@all_filter_data');
	Route::get('/product/range_data','ProductController@range_data');

	//Check RC Status & Check Plate
	Route::get('/check/rc/plate','HomeController@CheckRcPlate');
	Route::get('/check/rc/status','HomeController@CheckRcStatus');
	Route::get('/check/plate/number','HomeController@CheckNumberPlade');

	//Service
	Route::get('/service/prebooking','Service@Booking');
	Route::post('/service/prebooking','Service@Booking_DB');
	Route::get('/service/status','Service@ServiceStatus');
	Route::get('/service/check/status','Service@CheckServiceStatus');

	//HSRP
	Route::get('/hsrp/request','HsrpController@Hsrp_request');
	Route::post('/hsrp/request','HsrpController@Hsrp_request_DB');

	//Challan
	Route::get('/pay/challan/request','ChallanController@challan_certificate_request');
	Route::post('/pay/challan/request','ChallanController@challan_certificate_request_DB');
	Route::get('/pay/challan/number/check','ChallanController@checkChallan_number');




	Route::get('/store','StoreController@store');
	Route::get('/store/fetch_data','StoreController@fetch_data');
	Route::get('/store/search_value','StoreController@search_value');

	Route::get('/newproduct/get/details','HomeController@ProductGetDetails');
	Route::post('/web/subscribeEmail','HomeController@SubscribeEmail')->name('SubscribeEmail');
	
});
