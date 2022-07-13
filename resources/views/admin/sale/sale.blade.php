@extends($layout)
@section('title', __('Sale'))
@section('breadcrumb')
<li><a href="#"><i class=""></i> {{__('Sales')}} </a></li>
@endsection
@section('js')
<script src="/js/pages/sale/sale.js"></script>
<script>
   
   var csd_discount = {{$csd}}; // in percentage

   var scheme = @php echo json_encode($scheme); @endphp;
   var oldState = @php echo json_encode((old('state')) ? old('state') : '' ); @endphp;
   var oldCity = @php echo json_encode((old('city')) ? old('city') : '' ); @endphp;
   var oldLocation = @php echo json_encode((old('location')) ? old('location') : '' ); @endphp;
   var ex_showroom_price =  @php echo json_encode((old('ex_showroom_price')) ? old('ex_showroom_price') : 0 ); @endphp;
   var showroomPriceHidden =  @php echo json_encode((old('showroomPriceHidden')) ? old('showroomPriceHiddens') : 0 ); @endphp;
   if(oldState || oldCity)
   {
      $.ajax({
         url: "/admin/customer/city/"+parseInt(oldState),
         method:'GET',
         success: function(result){
            $("select[name='city']").html('');
            var str = '';
            $.each(result,function(key,val){
               str += "<option value ="+key+"> "+val+" </option>";
            });
            $("select[name='city']").append("<option value=''>Select City</option>"+str);
            $("select[name='city']").val(oldCity).trigger('change');
         }
      });
   }
   var oldModelName = @php echo json_encode((old('model_name')) ? old('model_name') : '' ); @endphp;
   var oldModelVariant = @php echo json_encode((old('model_variant')) ? old('model_variant') : '' ); @endphp;
   var oldProduct = @php echo json_encode((old('prod_name')) ? old('prod_name') : '' ); @endphp;
 
   var oldaccessories = @php echo json_encode((old('accessories')) ? old('accessories') : [] ); @endphp;
   var oldAccVal = @php
                    $arr = ((old('accessories')) ? old('accessories') : []);
                    $out = [];
                    for($i = 0 ; $i < count($arr) ; $i++)
                    {
                        $out[$i]['id'] =  $arr[$i];  
                        $out[$i]['qty'] =  old('accessories_qty_'.$arr[$i]);  
                        $out[$i]['partNo'] =  old('accessories_partNo_'.$arr[$i]);  
                        $out[$i]['amount'] =  old('accessories_amount_'.$arr[$i]);  
                        $out[$i]['desc'] =  old('accessories_desc_'.$arr[$i]);  
                    } 
                echo json_encode($out);
                @endphp;
   var oldOtherAcc = @php echo json_encode((old('other_part_no')) ? old('other_part_no') : [] ); @endphp;
   var oldOtherAccVal = @php
                    $arr = ((old('other_part_no')) ? old('other_part_no') : []);
                    $out = [];
                    for($i = 0 ; $i < count($arr) ; $i++)
                    {
                        $out[$i]['partNo'] =  $arr[$i];  
                        $out[$i]['qty'] =  old('other_part_qty')[$i];  
                        $out[$i]['amount'] =  old('other_part_amount')[$i];  
                    } 
                echo json_encode($out);
                @endphp;
   if(oldModelName != '')
   {
    //   console.log('old2',oldProduct);
        $('#model_name').val(oldModelName);
        $('#model_name').trigger('change');
   }
   
   var selectCust = @php echo json_encode((old('select_customer')) ? old('select_customer') : '' ); @endphp;
   var selectCustName = @php echo json_encode((old('select_customer_name')) ? old('select_customer_name') : '' ); @endphp;
      // old customer details
      var old_customer = [];
   // if(selectCust == 'exist_customer')
   // {
   //    if(selectCustName)
   //    {
   //       $("#select_customer_name").val(selectCustName).trigger('change');
         
   //    }
   // }

   var financeCompanyName = @php echo json_encode((old('finance_company_name')) ? old('finance_company_name') : '' ); @endphp;
   var financeName = @php echo json_encode((old('finance_name')) ? old('finance_name') : '' ); @endphp;
   var customer_pay_type = @php echo json_encode((old('customer_pay_type')) ? old('customer_pay_type') : '' ); @endphp;
   // console.log($("#").val());
  

   if(customer_pay_type == 'finance')
   {
      if(financeCompanyName)
      {
         $("#finance_company_name").val(financeCompanyName).trigger('change');
      }
   }
   
   
   //  console.log(product);
   // console.log(exist_cust);
   $('#dob').datepicker({
   format: "yyyy-mm-dd"
   });
   $('.datepicker5').datepicker({
   format: "mm-dd-yyyy"
   });


   // best deal 

   var old_best_make = @json((old('best-make')) ? old('best-make') : '');
   var old_best_model = @json((old('best-model')) ? old('best-model') : '');
   var old_best_variant = @json((old('best-variant')) ? old('best-variant') : '');
   var old_best_color = @json((old('best-color')) ? old('best-color') : '');
   // console.log('hi',best_model);
   if(old_best_make == 'Honda')
   {
      $("#best-make").val(old_best_make).trigger('change');
   }

</script>
@endsection
@section('css')
<style>
   .select2{
      width:100%;
   }
   #ajax_loader_div{
      z-index: 9999;
   }
   .part-success{
      background-color:aquamarine;
   }
</style>
@endsection
@section('main_section')
<section class="content">
   @include('admin.flash-message')
   @if(!empty($errors->all()))
   <div class="alert alert-danger">
      @foreach($errors->all() as $key => $val)
      {{ $val }}
      @endforeach
   </div>
   @endif
   @yield('content')
   {{-- @include('layouts.booking_tab') --}}
   <!-- general form elements -->
   <form id="stock-movement-validation" action="/admin/sale" method="POST">
      @csrf
      <div class="box box-primary">
         <div class="row ">
            <div class="col-md-12 prl-0 ">
               <div class="row">
                  {{-- <div class="col-md-3">
                     <label>Sale Number<sup>*</sup></label>
                     <input type="text" name="sale_no" readonly  class="input-css" value="{{(old('sale_no')) ? old('sale_no') : $sr_no}}">
                     {!! $errors->first('sale_no', '
                     <p class="help-block">:message</p>
                     ') !!}
                  </div> --}}
                  <div class="col-md-3">
                     <label>Sale Date<sup>*</sup></label>
                     <input type="text" name="sale_date" data-date-end-date="0d"  class="input-css datepicker5 " value="{{(old('sale_date')) ? old('sale_date') : date('Y-m-d')}}">
                     {!! $errors->first('sale_date', '
                     <p class="help-block">:message</p>
                     ') !!}
                  </div>
                  <div class="col-md-3">
                     <label>Model Name<sup>*</sup></label>
                     <select name="model_name" class="input-css select2 selectValidation" id="model_name" >
                        <option value="">Select Model Name</option>
                        @foreach($model_name as $key => $val)
                        <option value="{{$val['model_name']}}" > {{$val['model_name']}} </option>
                        @endforeach
                     </select>
                     {!! $errors->first('model_name', '
                     <p class="help-block">:message</p>
                     ') !!}
                  </div>
                  <div class="col-md-3">
                     <label>Model Variant<sup>*</sup></label>
                     <select name="model_variant" class="input-css select2 selectValidation" id="model_variant" >
                        <option value="">Select Model Variant</option>
                     </select>
                     {!! $errors->first('model_variant', '
                     <p class="help-block">:message</p>
                     ') !!}
                  </div>
                  {{-- This Product Name as a Color Code  --}}
                  <div class="col-md-3">
                     <label>Color Code<sup>*</sup></label>
                     <select name="prod_name" class="input-css select2 selectValidation" id="prod_name" >
                        <option value="">Select Color Code</option>
                        {{-- @foreach($product as $key => $val)
                        <option value="{{$val['id']}}" {{(old('prod_name') == $val['id']) ? 'selected' : '' }} > {{$val['prodName']}} </option>
                        @endforeach --}}
                     </select>
                     {!! $errors->first('prod_name', '
                     <p class="help-block">:message</p>
                     ') !!}
                     <input type="hidden" hidden name="prod_basic" value="{{(old('prod_basic')) ? old('prod_basic') : 0}}">
                  </div>
                  {{-- This Product Name as a Color Code --}}
                  <div class="col-md-3" style="{{(count($store) == 1)? 'display:none' : 'display:block'}}">
                     <label>Store Name<sup>*</sup></label>
                     @if(count($store) > 1)
                        <select name="store_name" class="input-css select2 selectValidation" id="store_name">
                           <option value="">Select Store</option>
                           @foreach($store as $key => $val)
                           <option value="{{$val['id']}}" {{(old('store_name') == $val['id']) ? 'selected' : '' }}> {{$val['name']}} </option>
                           @endforeach
                        </select>
                     @endif
                     @if(count($store) == 1)
                        <select name="store_name" class="input-css select2 selectValidation" id="store_name">
                           <option value="{{$store[0]['id']}}" selected>{{$store[0]['name']}}</option>
                        </select>
                     @endif
                     {!! $errors->first('store_name', '
                     <p class="help-block">:message</p>
                     ') !!}
                  </div>
               </div>
            </div>
            <div class="col-md-6 prl-0  " style="margin-top:10px;">
               <div class="row box box box-solid box-default">
                  <div class="col-md-12">
                     <div class="row">
                        <label> Sale Type <sup>*</sup> </label>
                        <div class="col-md-6">
                           <div class="radio">
                              <label><input autocomplete="off" {{ (old('sale_type')) ? ((old('sale_type') == 'general') ? 'checked' : '') : 'checked' }} type="radio" class="" value="general" name="sale_type">General</label>
                           </div>
                        </div>
                        <div class="col-md-6">
                           <div class="radio">
                              <label><input autocomplete="off" {{ (old('sale_type')) ? ((old('sale_type') == 'corporate') ? 'checked' : '' ) : '' }} type="radio" class="" value="corporate" name="sale_type">Corporate</label>
                           </div>
                        </div>
                     </div>
                     <div class="row">
                        <label for="select_customer">Customer Details <sup>*</sup></label>
                        <div class="col-md-4">
                           <div class="radio">
                              <label><input autocomplete="off" {{(old('select_customer') == 'new_customer') ? 'checked' : '' }} {{(old('select_customer')) ? '' : 'checked' }} type="radio" class="" value="new_customer" name="select_customer">New</label>
                           </div>
                        </div>
                        <div class="col-md-4">
                           <div class="radio">
                              <label><input autocomplete="off" {{(old('select_customer') == 'exist_customer') ? 'checked' : '' }} type="radio" class="" value="exist_customer" name="select_customer">Existing</label>
                           </div>
                        </div>
                        <div class="col-md-4">
                           <div class="radio">
                              <label><input autocomplete="off"  {{(old('select_customer') == 'booking') ? 'checked' : '' }} type="radio" class="" value="booking" name="select_customer">Pre Booking</label>
                           </div>
                        </div>
                        {!! $errors->first('select_customer', '
                        <p class="help-block">:message</p>
                        ') !!}
                     </div>
                     <div class="" id="customer_details">
                        <div class="row" id="pre_booking_no" style="{{(old('select_customer') == 'booking')? 'display:block' :'display:none'}}">
                           <div class="col-md-12" >
                              <label >Booking Number <sub>*</sub></label>
                              <input type="text" name="pre_booking" id="pre_booking"  class="input-css blank-customer inputValidation " placeholder="Booking Number" value="{{(old('pre_booking')) ? old('pre_booking') : '' }}">
                              {!! $errors->first('pre_booking', '
                              <p class="help-block">:message</p>
                              ') !!}
                              <input type="hidden" hidden name="booking_cust_id" class="blank-customer" value="{{(old('booking_cust_id'))?old('booking_cust_id'):''}}" id="booking_cust_id">
                           </div>
                           <div style="display:none">
                              <input type="hidden" hidden name="booking_id" id="booking_id" value="{{(old('booking_id')) ? old('booking_id') : '' }}">
                              <input type="hidden" hidden name="booking_number" id="booking_number" value="{{(old('booking_number')) ? old('booking_number') : '' }}">
                           </div>
                        </div>
                        <div class="row" >
                           <div class="col-md-6" style="{{(old('select_customer')) ? (old('select_customer') != 'exist_customer') ? "display:block;" : "display:none;" : "display:block" }}" id="new_customer">
                              <label>Customer Name <sup>*</sup></label>
                              {{-- {{(old('select_customer') == '' ) ? '' : 'style="display:block"' }} {{(old('select_customer') == '' ) ? '' : 'style="display:none"' }}--}}
                              <input type="text" name="enter_customer_name"  id="enter_customer_name" value="{{(old('enter_customer_name')) ? old('enter_customer_name') : '' }}" class="input-css blank-customer inputValidation"  placeholder="Customer Name">
                              {!! $errors->first('enter_customer_name', '
                              <p class="help-block">:message</p>
                              ') !!}
                           </div>
                        <div class="col-md-6"  style="{{(old('select_customer') == 'exist_customer') ? "display:block;" : "display:none;"}} {{(old('select_customer')) ? '' : "display:none" }}" id="exist_customer">
                        <label>Customer Name<sup>*</sup></label>
                        <select name="select_customer_name" id="select_customer_name" class="input-css select2 selectValidation" style="width:200px;">
                           <option value="">Select Customer Name</option>
                           @foreach($customer as $key => $val)
                              <option value="{{$val['id']}}" {{((old('select_customer_name') == $val['id']) ? 'selected' : '')}} > {{$val['name'].' - '.$val['mobile']}} </option>
                           @endforeach
                        </select>
                        {!! $errors->first('select_customer_name', '
                        <p class="help-block">:message</p>
                        ') !!}
                     </div>
                     <div class="col-md-6">
                        <label> Relation <sup>*</sup></label>
                        <div class="col-md-5">
                           <select name="relation_type" id="relation_type" class="select2 input-css blank-customer selectValidation" style="width:100%;">
                              <option value="">Select</option>
                              <option value="S/O" {{(old('relation_type') == 'S/O') ? 'selected' : '' }}>S/O</option>
                              <option value="D/O" {{(old('relation_type') == 'D/O') ? 'selected' : '' }}>D/O</option>
                              <option value="W/O" {{(old('relation_type') == 'W/O') ? 'selected' : '' }}>W/O</option>
                           </select>
                           {!! $errors->first('relation_type', '
                           <p class="help-block">:message</p>
                           ') !!}
                        </div>
                        <div class="col-md-7">
                           <input type="text" name="relation" class="input-css blank-customer inputValidation " value="{{(old('relation')) ? old('relation') : '' }}" placeholder="Relation Name">
                           {!! $errors->first('relation', '
                           <p class="help-block">:message</p>
                           ') !!}
                        </div>
                     </div>
                  </div>
                  <div class="row" id="corporate_info" style="{{ (old('sale_type')) ? ((old('sale_type') == 'corporate') ? 'display:block' : 'display:none' ) : 'display:none' }}">
                     <div class="col-md-6">
                        <label>Care Of <sup>*</sup></label>
                        <input type="text"  name="care_of" id="care_of"  class="input-css blank-customer {{(old('care_of')) ? 'inputValidation' : '' }} " value="{{(old('care_of')) ? old('care_of') : '' }}" placeholder="Enter Care Of"> 
                        {!! $errors->first('care_of', '<p class="help-block">:message</p>') !!}
                     </div>
                     <div class="col-md-6">
                        <label>GST Number <sup>*</sup> </label>
                        <input type="text"  name="gst" id="gst"  class="input-css blank-customer {{(old('gst')) ? 'inputValidation' : '' }} " value="{{(old('gst')) ? old('gst') : '' }}" placeholder="Enter GST Number"> 
                        {!! $errors->first('gst', '<p class="help-block">:message</p>') !!}
                     </div>
                  </div>
                  <div class="row">
                     <div class="col-md-6">
                        <label>Aadhar Number <sup>*</sup></label>
                        <input type="number"  name="aadhar" id="aadhar"  class="input-css blank-customer  NoChange" value="{{(old('aadhar')) ? old('aadhar') : '' }}" placeholder="Enter Aadhar Number"> 
                        {!! $errors->first('aadhar', '
                        <p class="help-block">:message</p>
                        ') !!}
                     </div>
                     <div class="col-md-6">
                        <label>Voter Number </label>
                        <input type="text"  name="voter" id="voter"  class="input-css blank-customer NoChange" value="{{(old('voter')) ? old('voter') : '' }}" placeholder="Enter Voter Number"> 
                        {!! $errors->first('voter', '
                        <p class="help-block">:message</p>
                        ') !!}
                     </div>
                  </div>
                  <div class="row">
                     <div class="col-md-12">
                        <label>Address<sup>*</sup></label>
                        <textarea  name="address" class="input-css blank-customer inputValidation "> {{(old('address')) ? old('address') : '' }} </textarea>
                        {!! $errors->first('address', '
                        <p class="help-block">:message</p>
                        ') !!}
                     </div>
                  </div>
                  <div class="row">
                     <div class="col-md-6">
                        <label for="dob">DOB <sup>*</sup></label>
                        <input type="text" name="dob" id="dob" value="{{old('dob')?old('dob'):''}}" class="input-css ">
                     </div>
                     <div class="col-md-6">
                        <label>State<sup>*</sup></label>
                        {{-- <input type="text" name="state" class="input-css blank-customer"> --}}
                        <select name="state" class="input-css  select2 selectValidation" id="state" >
                           <option value="">Select State</option>
                           @foreach($state as $key => $val)
                           <option value="{{$val['id']}}" {{(old('state'))? (old('state') == $val['id']) ? 'selected' : '' : '' }} >{{$val['name']}}</option>
                           @endforeach
                        </select>
                        {!! $errors->first('state', '
                        <p class="help-block">:message</p>
                        ') !!}
                     </div>
                  </div>
                  <div class="row">
                     <div class="col-md-6">
                        <label>City<sup>*</sup></label>
                        {{-- <input type="text" name="city" class="input-css blank-customer"> --}}
                        <select name="city" class="input-css  select2 selectValidation" id="city">
                           <option value="">Select City</option>
                        </select>
                        {!! $errors->first('city', '
                        <p class="help-block">:message</p>
                        ') !!}
                     </div>
                     <div class="col-md-6">
                        <label>Location<sup></sup></label>
                        {{-- <input type="text" name="city" class="input-css blank-customer"> --}}
                        <select name="location" class="input-css  select2 " id="location">
                           <option value="">Select Location</option>
                        </select>
                        {!! $errors->first('location', '
                        <p class="help-block">:message</p>
                        ') !!}
                     </div>
                     
                  </div>
                  <div class="row">
                     <div class="col-md-6">
                        <label>Pin Code<sup>*</sup></label>
                        <input type="number" name="pin" class="input-css blank-customer inputValidation " value="{{(old('pin')) ? old('pin') : 0 }}" placeholder="Enter Pin Code">
                        {!! $errors->first('pin', '<p class="help-block">:message</p>') !!}
                     </div>
                     <div class="col-md-6">
                        <label>Mobile<sup>*</sup></label>
                        <input type="number" name="mobile" id="mobile" class="input-css blank-customer inputValidation " value="{{(old('mobile')) ? old('mobile') : '' }}" placeholder="Enter Mobile Number">  
                        {!! $errors->first('mobile', '
                        <p class="help-block">:message</p>
                        ') !!}
                     </div>
                  </div>
                  <div class="row">
                     <div class="col-md-6">
                        <label>Email</label>
                        <input type="email" name="email" class="input-css blank-customer " value="{{(old('email')) ? old('email') : '' }}" placeholder="Enter Email">
                        {!! $errors->first('email', '
                        <p class="help-block">:message</p>
                        ') !!}
                     </div>
                  </div>
                  <div class="row">
                     <label><u>Reference</u><sup></sup></label>
                     <div class="col-md-6">
                        <label>Reference Name<sup></sup></label>
                        <input type="text" name="reference" class="input-css  " value="{{(old('reference')) ? old('reference') : '' }}">
                        {!! $errors->first('reference', '
                        <p class="help-block">:message</p>
                        ') !!}
                     </div>
                     <div class="col-md-6">
                        <label>Relation<sup></sup></label>
                        <input type="text" name="ref_relation" class="input-css" value="{{(old('ref_relation')) ? old('ref_relation') : '' }}">
                        {!! $errors->first('ref_relation', '
                        <p class="help-block">:message</p>
                        ') !!}
                     </div>
                  </div>
                  <div class="row" style="margin-bottom:5px;">
                     <div class="col-md-12">
                        <label>Mobile<sup></sup></label>
                        <input type="text" name="ref_mobile" class="input-css" value="{{(old('ref_mobile')) ? old('ref_mobile') : '' }}">
                        {!! $errors->first('ref_mobile', '
                        <p class="help-block">:message</p>
                        ') !!}
                     </div>
                  </div>
               </div>
            </div>
         </div>
         <div class="row box box box-solid box-default">
            <div class="col-md-12">
                
               
               
               <div class="row">
                  <div class="col-md-12">
                     <label>Sales Executive<sup>*</sup></label>
                     <select name="sale_executive" class="input-css select2 selectValidation"  id="sale_executive">
                        <option value="">Select Sales Executive</option>
                        @foreach($sale_user as $key => $val)
                        <option value="{{$val['id']}}" {{(old('sale_executive') == $val['id']) ? 'selected' : '' }} > {{$val['name']}} </option>
                        @endforeach
                     </select>
                     {!! $errors->first('sale_executive', '
                     <p class="help-block">:message</p>
                     ') !!}
                  </div>
               </div>
               <div class="row">
                  {{-- <label><u>Reference</u><sup>*</sup></label> --}}
                  <div class="col-md-12">
                     <label>Type Of Sale<sup>*</sup></label>
                     <div class="col-md-4">
                        <div class="radio">
                           <label><input autocomplete="off"  {{(old('tos') == 'new') ? 'checked' : '' }} {{(old('tos')) ? '' : 'checked' }} type="radio" class="" value="new" name="tos">New</label>
                        </div>
                     </div>
                     <div class="col-md-4">
                        <div class="radio">
                           <label><input autocomplete="off" {{(old('tos') == 'exchange') ? 'checked' : '' }} type="radio" class="" value="exchange" name="tos">Exchange</label>
                        </div>
                     </div>
                     <div class="col-md-4">
                        <div class="radio">
                           <label><input autocomplete="off" {{(old('tos') == 'best_deal') ? 'checked' : '' }} type="radio" class="" value="best_deal" name="tos">Best Deal</label>
                        </div>
                     </div>
                     {!! $errors->first('tos', '
                     <p class="help-block">:message</p>
                     ') !!}
                  </div>
               </div>
               <div id="tos_exchange" style="{{(old('tos') != 'new') ? 'display:block;' :'display:none;' }} {{(old('tos'))? : 'display:none'}}">
                  <div class="row" style="margin-bottom:5px;">
                     <div class="col-md-6">
                        <label>If Exchange (Model)<sup></sup></label>
                        <input type="text" name="exchange_model" readonly class="input-css inputValidation " value="{{(old('exchange_model')) ? old('exchange_model') : '' }}">
                        {!! $errors->first('exchange_model', '
                        <p class="help-block">:message</p>
                        ') !!}
                     </div>
                     <div class="col-md-6">
                        <label>Year Of Manufacturing<sup></sup></label>
                        <input type="text" name="exchange_yom" readonly class="input-css inputValidation" id="exchange_yom" value="{{(old('exchange_yom')) ? old('exchange_yom') : '' }}">
                        {!! $errors->first('exchange_yom', '<p class="help-block">:message</p>') !!}
                     </div>
                  </div>
                  <div class="row" style="margin-bottom:5px;">
                     <div class="col-md-6">
                        <label>Registration Number<sup></sup></label>
                        <input type="text" name="exchange_register" readonly class="input-css inputValidation" value="{{(old('exchange_register')) ? old('exchange_register') : '' }}">
                        {!! $errors->first('exchange_register', '
                        <p class="help-block">:message</p>
                        ') !!}
                     </div>
                     <div class="col-md-6">
                        <label>Ex-Change Value<sup></sup></label>
                        <input type="text" name="exchange_value" id="exchange_value" readonly class="input-css inputValidation" value="{{(old('exchange_value')) ? old('exchange_value') : 0 }}" onchange="subcalculate(this)">
                        {!! $errors->first('exchange_value', '
                        <p class="help-block">:message</p>
                        ') !!}
                     </div>
                  </div>
               </div>
            </div>
         </div>
         <div class="row box box box-solid box-default ">
            <div class="col-md-12 ">
               <label><u>Accessories</u><sup>*</sup></label>
               <div class="row">
                  <div class="col-md-12" id="accessories_filter" >
                     <div class="col-md-4">
                        <label><input type="radio" class="filter" value="connection" name="acc_filter" {{(old('acc_filter') == 'connection') ? 'checked' : '' }} >Connection</label>
                     </div>
                     <div class="col-md-4">
                        <label><input type="radio" class="filter" value="hop" name="acc_filter">HOP</label>
                     </div>
                     <div class="col-md-4">
                        <label><input type="radio" class="filter" value="full" name="acc_filter">FULL</label>
                     </div>
                     {!! $errors->first('acc_filter', '<p class="help-block">:message</p>') !!}
                  </div> 
               </div>
               <hr>
               <div class="row" id="accessories-head-div">
                  <div class="col-md-4">
                     <label>Accessories<sup>*</sup></label>
                  </div>
                  <div class="col-md-4">
                     <label>Part Number<sup>*</sup></label>
                  </div>
                  <div class="col-md-2">
                     <label>Quantity<sup>*</sup></label>
                  </div>
                  <div class="col-md-2">
                     <label>Amount<sup>*</sup></label>
                  </div>
               </div>
               <div class="row" id="accessories-append" style="margin-top:5px;margin-bottom:5px;">
               </div>
               {{-- 
               <div class="row" style="margin-top:5px;margin-bottom:5px;">
                  <div class="col-md-12" >
                     <button type="button" style="float:right" id="accessories-btn" class="btn btn-info">
                     <i class="fa fa-plus"> </i>
                     </button>
                  </div>
               </div>
               --}}
               <div class="row" style="margin-top:5px;margin-bottom:5px;">
                  <div class="col-md-4" >
                     <label>Total :-</label>
                     {{-- <input type="text" class="input-css"> --}}
                  </div>
                  <div class="col-md-4">
                  </div>
                  <div class="col-md-2" >
                     {{-- <label>Total Quantity<sup>*</sup></label> --}}
                     <input type="text" id="total_accessories_qty" name="total_accessories_qty" readonly class="input-css">
                  </div>
                  <div class="col-md-2" >
                     {{-- <label>Total Amount<sup>*</sup></label> --}}
                     <input type="text" id="total_accessories_amount" name="total_accessories_amount" readonly class="input-css">
                  </div>
               </div>
            </div>
         </div>
         {{-- 
            findExactSlotBay
            fix_time_validate(
         <div class="row box box box-solid box-default ">
            <div class="col-md-12 ">
               <label for="pending"><u>Pending Items</u></label>
               <div class="row" id="pending-div">
                  <div class="col-md-5">
                     <label>Pending Item (If Any)<sup></sup></label>
                     <input type="text" name="pending_item[]" class="input-css pending_remark">
                  </div>
                  <div class="col-md-6">
                     <label>Remarks<sup></sup></label>
                     <input type="text" name="pending_remark[]" class="input-css pending_remark">
                  </div>
               </div>
               <div id="pending_append" style="margin-top:5px;margin-bottom:5px;">
               </div>
               <div class="row" style="margin-top:5px;margin-bottom:5px;">
                  <div class="col-md-12" >
                     <button type="button" style="float:right" id="pending-btn" class="btn btn-info">
                     <i class="fa fa-plus" > </i>
                     </button>
                  </div>
               </div>
            </div>
         </div>
         --}}
      </div>
      <div class="col-md-6 prl-0  " style="margin-top:10px;">
         <div class="row box box box-solid box-default">
            <div class="col-md-12">
               <div class="row" style="margin-bottom:10px;">
                  <div class="col-md-12">
                     <label>Customer Payment Type<sup>*</sup></label>
                     <div class="col-md-4">
                        <label><input type="radio" {{(old('customer_pay_type') == 'cash') ? 'checked' : '' }} {{(old('customer_pay_type')) ? '' : 'checked' }} class="customer_pay_type" value="cash" name="customer_pay_type">Cash</label>
                     </div>
                     <div class="col-md-4">
                        <label><input type="radio" {{(old('customer_pay_type') == 'finance') ? 'checked' : '' }} class="customer_pay_type" value="finance" name="customer_pay_type">Finance</label>
                     </div>
                     <div class="col-md-4">
                        <label><input type="radio" {{(old('customer_pay_type') == 'self_finance') ? 'checked' : '' }} class="customer_pay_type" value="self_finance" name="customer_pay_type">Self Finance</label>
                     </div>
                     {!! $errors->first('customer_pay_type', '
                     <p class="help-block">:message</p>
                     ') !!}
                  </div>
               </div>
               <div class="row" id="finance" style="{{(old('customer_pay_type') == 'finance')? 'display:block;' :'display:none;'}} margin-bottom:10px;">
                  <label><u>Financier Details</u><sup>*</sup></label>
                  <div class="col-md-6">
                     <label>Company Name<sup>*</sup></label>
                     {{-- <input type="text" name="finance_company_name" class="input-css inputValidation" value="{{(old('finance_company_name'))? old('finance_company_name') : '' }}"> --}}
                     <select name="finance_company_name" class="input-css selectValidation select2" id="finance_company_name">
                        <option value="">Select Company Name</option>
                        @foreach($company_name as $key => $val)
                           <option value="{{$val->id}}">{{$val->company_name}}</option>
                        @endforeach
                     </select>
                     {!! $errors->first('finance_company_name', '
                     <p class="help-block">:message</p>
                     ') !!}
                  </div>
                  <div class="col-md-6">
                     <label>Financier Executive<sup>*</sup></label>
                     {{-- <input type="text" name="finance_name" class="input-css inputValidation" value="{{(old('finance_name'))? old('finance_name') : '' }}"> --}}
                     <select name="finance_name" class="input-css selectValidation select2" id="finance_name">
                        <option value="">Select Financier Executive</option>
                     </select>
                     {!! $errors->first('finance_name', '
                     <p class="help-block">:message</p>
                     ') !!}
                  </div>
                  
                  <div class="col-md-6">
                     <label>Loan Amount<sup>*</sup></label>
                     <input type="text" name="loan_amount" id="loan_amount"  value="{{(old('loan_amount'))? old('loan_amount') : 0.0 }}" class="input-css inputValidation " >
                     {!! $errors->first('loan_amount', '
                     <p class="help-block">:message</p>
                     ') !!}
                  </div>
                  <div class="col-md-6">
                     <label>DO Amount (Delivery Order)<sup>*</sup></label>
                     <input type="text" name="do" id="do" value="{{(old('do'))? old('do') : 0.0 }}" class="input-css inputValidation" onfocusout = "subcalculate(this)">
                     {!! $errors->first('do', '
                     <p class="help-block">:message</p>
                     ') !!}
                  </div>
                  <div class="col-md-6">
                     <label>DP Amount (Down Payment)<sup>*</sup></label>
                     <input type="text" name="dp" class="input-css inputValidation" value="{{(old('dp'))? old('dp') : 0.0 }}">
                     {!! $errors->first('dp', '
                     <p class="help-block">:message</p>
                     ') !!}
                  </div>
                  <div class="col-md-6">
                     <label>LOS/DO Number<sup></sup></label>
                     <input type="text" name="los" class="input-css " value="{{(old('los'))? old('los') : '' }}">
                     {!! $errors->first('los', '
                     <p class="help-block">:message</p>
                     ') !!}
                  </div>
                  <div class="col-md-6">
                     <label>ROI (Rate Of Interest)<sup>*</sup></label>
                     <input type="text" name="roi" class="input-css inputValidation" value="{{(old('roi'))? old('roi') : 0.0 }}">
                     {!! $errors->first('roi', '
                     <p class="help-block">:message</p>
                     ') !!}
                  </div>
                  <div class="col-md-6">
                     <label>Payout<sup>*</sup></label>
                     <input type="text" name="payout" class="input-css inputValidation" value="{{(old('payout'))? old('payout') : 0.0 }}">
                     {!! $errors->first('payout', '
                     <p class="help-block">:message</p>
                     ') !!}
                  </div>
                  <div class="col-md-6">
                     <label>PF (Processing Fees.)<sup>*</sup></label>
                     <input type="text" name="pf" class="input-css inputValidation" value="{{(old('pf'))? old('pf') : 0.0 }}">
                     {!! $errors->first('pf', '
                     <p class="help-block">:message</p>
                     ') !!}
                  </div>
                  <div class="col-md-6">
                     <label>EMI<sup>*</sup></label>
                     <input type="text" name="emi" class="input-css inputValidation" value="{{(old('emi'))? old('emi') : 0.0 }}">
                     {!! $errors->first('emi', '
                     <p class="help-block">:message</p>
                     ') !!}
                  </div>
                  <div class="col-md-6">
                     <label>Tenure <sup>*</sup></label>
                     <input type="text" name="monthAddCharge" class="input-css inputValidation" value="{{(old('monthAddCharge'))? old('monthAddCharge') : 0.0 }}">
                     {!! $errors->first('monthAddCharge', '
                     <p class="help-block">:message</p>
                     ') !!}
                  </div>
                  <div class="col-md-6">
                     <label>Stamp Details<sup>*</sup></label>
                     <input type="text" name="sd" class="input-css inputValidation" value="{{(old('sd'))? old('sd') : '' }}">
                     {!! $errors->first('sd', '
                     <p class="help-block">:message</p>
                     ') !!}
                  </div>
               </div>
               <div class="row" id="self_finance" style="{{(old('customer_pay_type') == 'self_finance')? 'display:block;' :'display:none;'}} margin-bottom:10px;">
                  <label><u>Self-Finance Details</u><sup>*</sup></label>
                  <div class="col-md-6">
                     <label>Company Name<sup>*</sup></label>
                     {{-- <input type="text" name="self_finance_company" value="{{(old('self_finance_company'))? old('self_finance_company') : '' }}" class="input-css inputValidation"  > --}}
                     <select name="self_finance_company" class="input-css selectValidation select2" id="self_finance_company " style="width: 100%">
                        <option value="">Select Company Name</option>
                        @foreach($company_name as $key => $val)
                           <option value="{{$val->id}}" {{(old('self_finance_company') == $val->id)? 'selected' : ''}} >{{$val->company_name}}</option>
                        @endforeach
                     </select>
                     {!! $errors->first('self_finance_company', '<p class="help-block">:message</p>') !!}
                  </div>
                  <div class="col-md-6">
                     <label>Loan Amount<sup>*</sup></label>
                     <input type="text" name="self_finance_amount" id="self_loan_amount" value="{{(old('self_finance_amount'))? old('self_finance_amount') : '' }}" class="input-css inputValidation" onfocusout="subcalculate(this)" >
                     {!! $errors->first('self_finance_amount', '
                     <p class="help-block">:message</p>
                     ') !!}
                  </div>
                  {{-- <div class="col-md-6">
                     <label>Bank Name<sup>*</sup></label>
                     <input type="text" name="self_finance_bank" value="{{(old('self_finance_bank'))? old('self_finance_bank') : '' }}" class="input-css inputValidation">
                     {!! $errors->first('self_finance_bank', '
                     <p class="help-block">:message</p>
                     ') !!}
                  </div> --}}
               </div>
            </div>
         </div>
         <div class="row box box box-solid box-default ">
            <div class="col-md-12 ">
               <div class="row" >
                  <div class="col-md-9">
                     <label><u>Calculation Items</u><sup>*</sup></label>
                     {{-- <input type="text" name="accessories[]" class="input-css"> --}}
                  </div>
                  <div class="col-md-3">
                     <label><u>Cost (Rs.)</u><sup>*</sup></label>
                     {{-- <input type="number" name="[]" class="input-css accessories_qty"> --}}
                  </div>
               </div>
               <div class="row" >
                  <div class="col-md-9">
                     <label>Ex-Showroom Price<sup>*</sup></label>
                  </div>
                  <div class="col-md-3">
                     <input type="text" id="ex_showroom_price" value="{{(old('ex_showroom_price')) ? old('ex_showroom_price') : 0.0 }}" name="ex_showroom_price" class="input-css calculationAll inputValidation  ">
                     <input type="hidden" hidden value = "{{(old('showroomPriceHidden'))? old('showroomPriceHidden') : 0 }}" id="showroomPriceHidden" name="showroomPriceHidden">
                     {!! $errors->first('ex_showroom_price', '<p class="help-block">:message</p>') !!}
                  </div>
               </div>
               <div class="row" id="corporate_csd" style="{{ (old('sale_type')) ? ((old('sale_type') == 'corporate') ? 'display:block' : 'display:none' ) : 'display:none' }}">
                  <div class="col-md-9">
                     <label><input type="checkbox" {{(old('csd_check'))? 'checked' : '' }} name="csd_check" value="csd_check" onchange="calculate()" > CSD Discount<sup></sup></label>
                     {{-- <label>CSD Discount<sup>*</sup></label> --}}
                  </div>
                  <div class="col-md-3">
                     <label style="margin-right:10px;"> <span id="csd"> {{(old('csd')) ? old('csd') :  0.0}} </span></label>
                  </div>
               </div>
               {{-- rto --}}
               <div class="row">
                  <label>RTO</label>
                  <div class="col-md-6">
                     <label><input type="radio" name="rto" value="normal" {{(old('rto'))? ((old('rto') == 'normal' ) ? 'checked' : '') : '' }} class="rto"> Normal</label>
                  </div>
                  <div class="col-md-6">
                     <label><input type="radio" name="rto" value="self" {{(old('rto'))? ((old('rto') == 'self' ) ? 'checked' : '') : '' }} class="rto"> Self</label>
                  </div>
                  {!! $errors->first('rto', '<p class="help-block">:message</p>') !!}
                  
               </div>
               <div class="row" id="handling_charge-div" style="{{(old('rto'))? ((old('rto') == 'normal' ) ? 'display:block' : 'display:none') : 'display:none' }}">
                  <div class="col-md-9">
                     <label>Handling Charges <sup>*</sup></label>
                  </div>
                  <div class="col-md-3">
                     <input type="text" id="handling_charge" name="handling_charge" value="{{(old('handling_charge')) ? old('handling_charge') : '' }}" class="input-css calculationAll  " onfocusout="calculate(this)">
                     {!! $errors->first('handling_charge', '<p class="help-block">:message</p>') !!}
                  </div>
               </div>
               <div class="row" id="affidavit-div" style="{{(old('rto'))? ((old('rto') == 'normal' ) ? 'display:block' : 'display:none') : 'display:none' }}">
                  <div class="col-md-9">
                     <input type="checkbox" {{(old('affidavit'))? 'checked' : '' }} name="affidavit" value="affidavit"><b> Affidavit</b>
                  </div>
                  <div class="col-md-3">
                     <input type="text" id="affidavit_cost" {{(old('affidavit'))? '': 'readonly' }} name="affidavit_cost" value="{{(old('affidavit_cost')) ? old('affidavit_cost') : '' }}" class="input-css calculationAll  " onfocusout="calculate(this)">
                     {!! $errors->first('affidavit_cost', '<p class="help-block">:message</p>') !!}
                  </div>
               </div>
               <div class="row" id="permanent_temp-div" >
                  <label>RTO Type</label>
                  <div class="col-md-6">
                     <label><input type="radio" name="permanent_temp" {{(old('permanent_temp'))? ((old('permanent_temp') == 'permanent' ) ? 'checked' : '') : '' }} value="permanent" class="permanent_temp"> Permanent</label>
                  </div>
                  <div class="col-md-6">
                     <label><input type="radio" name="permanent_temp" {{(old('permanent_temp'))? ((old('permanent_temp') == 'temporary' ) ? 'checked' : '') : '' }} value="temporary" class="permanent_temp"> Temporary</label>
                  </div>
                  {!! $errors->first('permanent_temp', '<p class="help-block">:message</p>') !!}
               </div>
               <div class="row" >
                  <div class="col-md-9">
                     <label>Registration Fee & Road Tax<sup>*</sup></label>
                  </div>
                  <div class="col-md-3">
                     <input type="text" id="reg_fee_road_tax" name="reg_fee_road_tax" value="{{(old('reg_fee_road_tax')) ? old('reg_fee_road_tax') : '' }}" class="input-css calculationAll inputValidation " onfocusout="calculate(this)">
                     {!! $errors->first('reg_fee_road_tax', '<p class="help-block">:message</p>') !!}
                     <label class="text">Suggestion :- <small id="sugg_rto"></small> </label>
                  </div>
               </div>
               <div class="row" >
                  <div class="col-md-12">
                     <label>Fancy # Receipt<sup></sup></label>
                     <div class="col-md-4">
                        <label><input type="radio" name="fancy" value="Yes" {{(old('fancy'))? ((old('fancy') == 'Yes' ) ? 'checked' : '') : '' }} class="fancy"> Yes</label>
                     </div>
                     <div class="col-md-4">
                        <label><input type="radio" name="fancy" value="No" {{(old('fancy'))? ((old('fancy') == 'No' ) ? 'checked' : '') : 'checked' }} class="fancy"> No</label>
                     </div>
                     <div class="col-md-4">
                        <input type="text" id="fancy_no" name="fancy_no" value="{{(old('fancy_no')) ? old('fancy_no') : '' }}" class="input-css" placeholder="Fancy #">
                        {!! $errors->first('fancy_no', '<p class="help-block">:message</p>') !!}
                     </div>
                  </div>
               </div>
               <div class="row">
                  <div class="col-md-6">
                     <label>Fancy Receipt Date<sup></sup></label>
                  </div>
                  <div class="col-md-6">
                     <input type="text" id="fancy_date" name="fancy_date"  value="{{(old('fancy_date')) ? old('fancy_date') : '' }}" class="input-css datepicker5" data-date-end-date="0d" >
                        {!! $errors->first('fancy_date', '<p class="help-block">:message</p>') !!}
                  </div>
               </div>
               <div class="row" >
                  <label>Insurance<sup>*</sup></label>
                  <div class="col-md-6">
                     <input type="radio" name="insur_type" {{ old('insur_type')? ((old('insur_type') == 'normal') ? 'checked' : '' ) : 'checked' }} class="insur_type" value="normal"> Normal Insurance
                  </div>
                  <div class="col-md-6">
                     <input type="radio" name="insur_type" {{(old('insur_type') == 'self') ? 'checked' : '' }} class="insur_type" value="self"> Self Insurance
                  </div>
               </div>
               <div class="row">
                  <label>Company Name <sup>*</sup></label>
                  <select name="insur_c" id="insur_c" class="input-css select2 selectValidation" style="padding:10px;">
                     <option value="">Select Insurance Company</option>
                     @foreach($insur_company as $k => $v)
                        <option value="{{trim($v->id)}}" {{(old('insur_c') == trim($v->id)) ? 'selected': ''}} >{{trim($v->name)}}</option>
                     @endforeach
                  </select>
               </div>
               <div class="row">
                  <div class="col-md-9" style="padding-left:50px; margin-top:10px;">
                     <input type="radio" {{(old('s_insurance'))? ((old('s_insurance') == 'zero_dep' ) ? 'checked' : '') : 'checked' }} name="s_insurance" value="zero_dep" onclick="insuranceCheck(this)" > Zero Dep
                  </div>
                  <div class="col-md-3">
                     <input type="text" name="zero_dep_cost"  value="{{(old('zero_dep_cost'))? old('zero_dep_cost') : 0.0 }}"  {{(old('s_insurance'))? ((old('s_insurance') == 'zero_dep' ) ? '' : 'readonly') : '' }} id="zero_dep_cost" class="input-css calculationAll s_insurance" onfocusout="calculate(this)">
                     {!! $errors->first('zero_dep_cost', '
                     <p class="help-block">:message</p>
                     ') !!}
                  </div>
               </div>
               <div class="row">
                  <div class="col-md-9" style="padding-left:50px;">
                     <input type="radio" {{(old('s_insurance'))? ((old('s_insurance') == 'comprehensive' ) ? 'checked' : '') : '' }} name="s_insurance" value="comprehensive" onclick="insuranceCheck(this)"> Comprehensive
                  </div>
                  <div class="col-md-3">
                     <input type="text" name="comprehensive_cost" value="{{(old('comprehensive_cost'))? old('comprehensive_cost') : 0.0 }}" {{(old('s_insurance'))? ((old('s_insurance') == 'comprehensive' ) ? '' : 'readonly') : 'readonly' }} id="comprehensive_cost" class="input-css calculationAll s_insurance" onfocusout="calculate(this)">
                     {!! $errors->first('comprehensive_cost', '<p class="help-block">:message</p>') !!}
                  </div>
               </div>
               <div class="row">
                  <div class="col-md-9" style="padding-left:50px;">
                     <input type="radio" {{(old('s_insurance'))? ((old('s_insurance') == 'ltp_5_yr' ) ? 'checked' : '') : '' }} name="s_insurance" value="ltp_5_yr" onclick="insuranceCheck(this)"> LTP 5 Yrs
                  </div>
                  <div class="col-md-3">
                     <input type="text" name="ltp_5_yr_cost" id="ltp_5_yr_cost" value="{{(old('ltp_5_yr_cost'))? old('ltp_5_yr_cost') : 0.0 }}" {{(old('s_insurance'))? ((old('s_insurance') == 'ltp_5_yr' ) ? '' : 'readonly') : 'readonly' }} class="input-css calculationAll s_insurance" onfocusout="calculate(this)">
                     {!! $errors->first('ltp_5_yr_cost', '
                     <p class="help-block">:message</p>
                     ') !!}
                  </div>
               </div>
               <div class="row">
                  <div class="col-md-9" style="padding-left:50px;">
                     <input type="radio" {{(old('s_insurance'))? ((old('s_insurance') == 'ltp_zero_dep' ) ? 'checked' : '') : '' }} name="s_insurance" value="ltp_zero_dep" onclick="insuranceCheck(this)"> LTP Zero Dep
                  </div>
                  <div class="col-md-3">
                     <input type="text" name="ltp_zero_dep_cost" value="{{(old('ltp_zero_dep_cost'))? old('ltp_zero_dep_cost') : 0.0 }}" id="ltp_zero_dep_cost" {{(old('s_insurance'))? ((old('s_insurance') == 'ltp_zero_dep' ) ? '' : 'readonly') : 'readonly' }} class="input-css calculationAll s_insurance" onfocusout="calculate(this)">
                     {!! $errors->first('ltp_zero_dep_cost', '
                     <p class="help-block">:message</p>
                     ') !!}
                  </div>
               </div>
               <div class="row" >
                  <label><input type="checkbox" {{(old('cpa_cover'))? 'checked' : '' }} name="cpa_cover" value="cpa_cover" onclick="cpaCheck(this)"> CPA Cover<sup>*</sup></label>
                  <div style=" {{(old('cpa_cover') == 'cpa_cover') ? 'display:block;' : 'display:none;' }} ">
                     <div class="col-md-5" style="padding-left:50px; ">
                        {{-- <input type="text" name="cpa_company" value="{{(old('cpa_cover'))? old('cpa_company') : '' }}" id="cpa_company" class="input-css hidecpa {{(old('cpa_cover'))? 'inputValidation' : '' }}" placeholder="Company Name"> --}}
                        <select name="cpa_company" id="cpa_company" class="input-css select2 hidecpa {{(old('cpa_cover'))? 'selectValidation' : '' }}" style="padding:10px;">
                           <option value="">Select Insurance Company</option>
                           @foreach($cpa_company as $k => $v)
                              <option value="{{trim($v->id)}}" {{(old('insur_c') == trim($v->id)) ? 'selected': ''}} >{{trim($v->name)}}</option>
                           @endforeach
                        </select>
                        {!! $errors->first('cpa_company', '
                        <p class="help-block">:message</p>
                        ') !!}
                     </div>
                     <div class="col-md-4" style="padding-left:50px; ">
                        <input type="number" name="cpa_duration" value="{{(old('cpa_cover'))? old('cpa_duration') : '' }}" id="cpa_duration" class="input-css hidecpa {{(old('cpa_cover'))? 'inputValidation' : '' }}" placeholder="Duration">
                        {!! $errors->first('cpa_duration', '
                        <p class="help-block">:message</p>
                        ') !!}
                     </div>
                     <div class="col-md-3" style="">
                        <input type="text" name="cpa_cover_cost" value="{{(old('cpa_cover'))? old('cpa_cover_cost') : 0.0 }}" id="cpa_cover_cost" class="input-css  hidecpa {{(old('cpa_cover'))? 'inputValidation calculationAll' : '' }}" onfocusout="calculate(this)" >
                        {!! $errors->first('cpa_cover_cost', '
                        <p class="help-block">:message</p>
                        ') !!}
                     </div>
                  </div>
               </div>
               <div class="row">
                  <div class="col-md-9">
                     <label>Accessories Value<sup>*</sup></label>
                  </div>
                  <div class="col-md-3">
                     <input type="text" readonly name="accessories_cost" value="{{(old('accessories_cost'))? old('accessories_cost') : 0.0 }}" id="accessories_cost" class="input-css inputValidation  calculationAll" onfocusout="calculate(this)">
                     {!! $errors->first('accessories_cost', '
                     <p class="help-block">:message</p>
                     ') !!}
                  </div>
               </div>
               <div class="row" style="{{ (old('customer_pay_type')) ? (old('customer_pay_type') != 'cash') ? 'display:block' : 'display:none'  :'display:none'}}" id="hypo" >
                  <div class="col-md-9" >
                     <label>Hypothecation<sup>*</sup></label>
                  </div>
                  <div class="col-md-3">
                     <input type="text" name="hypo_cost" id="hypo_cost" value="{{(old('hypo_cost'))? old('hypo_cost') : 0.0 }}" class="input-css calculationAll inputValidation" onfocusout="calculate(this)">
                     {!! $errors->first('hypo_cost', '
                     <p class="help-block">:message</p>
                     ') !!}
                  </div>
               </div>
               {{-- 
               <div class="row">
                  <div class="col-md-9" >
                     <label>Less Purchase Value<sup>*</sup></label>
                  </div>
                  <div class="col-md-3">
                     <input type="text" name="less_purchase_cost" id="less_purchase_cost" class="input-css calculationAll" onfocusout="calculate(this)">
                  </div>
               </div>
               --}}
               <div class="row">
                  <div class="col-md-9" style="padding-top:15px;">
                     <input type="checkbox" {{(old('amc'))? 'checked' : '' }} name="amc" value="amc" onclick="normalCheck(this)"> AMC (Annual Maintenance Contract)
                  </div>
                  <div class="col-md-3">
                     <input type="text" name="amc_cost" value="{{(old('amc_cost')) ? old('amc_cost') : 0.0}}" id="amc_cost" {{(old('amc'))? '' : 'readonly' }} class="input-css calculationAll" onfocusout="calculate(this)">
                     {!! $errors->first('amc_cost', '
                     <p class="help-block">:message</p>
                     ') !!}
                  </div>
               </div>
               <div class="row">
                  <div class="col-md-4" style="padding-top:15px;">
                     <input type="checkbox" {{(old('ew'))? 'checked' : '' }} name="ew" value="ew" onclick="normalCheck(this)"> EW 
                  </div>
                  <div class="col-md-8">
                     <div class="col-md-7">
                        <select name="ew_duration" id="ew_duration" class="input-css select2" readonly>
                           <option value="">EW Duration</option>
                           <option value="1" {{(old('ew_duration') == 1)? 'selected' : '' }} >1</option>
                           <option value="2" {{(old('ew_duration') == 2)? 'selected' : '' }} >2</option>
                           <option value="3" {{(old('ew_duration') == 3)? 'selected' : '' }} >3</option>
                        </select>
                        {{-- <input type="text" name="ew_duration" id="ew_duration" value="{{(old('ew_duration')) ? old('ew_duration') : '' }}" placeholder="Enter EW duration" {{(old('ew'))? '' : 'readonly' }} class="input-css "> --}}
                        {!! $errors->first('ew_duration', '
                        <p class="help-block">:message</p>
                        ') !!}
                     </div>
                     <div class="col-md-5">
                        <input type="text" name="ew_cost" id="ew_cost" value="{{(old('ew_cost')) ? old('ew_cost') : 0.0 }}" {{(old('ew'))? '' : 'readonly' }} class="input-css calculationAll" onfocusout="calculate(this)">
                        {!! $errors->first('ew_cost', '
                        <p class="help-block">:message</p>
                        ') !!}
                     </div>
                  </div>
               </div>
               <div class="row">
                  <div class="col-md-9" style="padding-top:15px;">
                     <input type="checkbox" {{(old('hjc'))? 'checked' : '' }} name="hjc" value="hjc" onclick="normalCheck(this)"> HJC
                  </div>
                  <div class="col-md-3">
                     <input type="text" name="hjc_cost" value="{{(old('hjc_cost')) ? old('hjc_cost') : 0.0 }}" id="hjc" {{(old('hjc'))? '' : 'readonly' }} class="input-css calculationAll" onfocusout="calculate(this)">
                     {!! $errors->first('hjc_cost', '
                     <p class="help-block">:message</p>
                     ') !!}
                  </div>
               </div>
               <div class="row">
                  <div class="row">
                     <div class="col-md-9">
                        <label for="advance">Discount<sub>*</sub></label>
                        <div class="col-md-3">
                           <label><input autocomplete="off" {{(old('discount') == 'normal') ? 'checked' : '' }} {{(old('discount')) ? '' : 'checked' }} type="radio"  class="" value="normal" name="discount"> Normal</label>
                        </div>
                        <div class="col-md-3">
                           <label><input autocomplete="off" {{(old('discount') == 'scheme') ? 'checked' : '' }} type="radio" class="" value="scheme" name="discount"> Scheme</label>
                        </div>
                        {!! $errors->first('discount', '
                        <p class="help-block">:message</p>
                        ') !!}
                     </div>
                     <div class="col-md-3" style="padding-top:25px">
                        <input type="text" name="discount_amount" id="discount_amount" value="{{(old('discount_amount')) ? old('discount_amount') : 0.0 }}" class="input-css inputValidation " onfocusout="subcalculate(this)">
                        {!! $errors->first('discount_amount', '
                        <p class="help-block">:message</p>
                        ') !!}
                     </div>
                  </div>
                  <div class="row" id="onscheme" style="{{(old('discount') == 'scheme') ? 'display:block' :'display:none'}}">
                     <div class="col-md-5">
                        <select name="scheme" id="scheme" class="input-css select2 selectValidation" >
                           <option value="">Select Scheme</option>
                           @foreach($scheme as $key => $val)
                           <option value="{{$val['id']}}" {{(old('scheme') == $val['id']) ? 'selected' : ''  }} > {{$val['name']}} </option>
                           @endforeach
                        </select>
                        {!! $errors->first('scheme', '
                        <p class="help-block">:message</p>
                        ') !!}
                     </div>
                     <div class="col-md-4">
                        <input type="text" name="scheme_remark" value="{{(old('scheme_remark')) ? old('scheme_remark') : ''}}" class="input-css inputValidation" placeholder="Remark">
                        {!! $errors->first('scheme_remark', '
                        <p class="help-block">:message</p>
                        ') !!}
                     </div>
                     {{-- 
                     <div class="col-md-3" style="">
                        <input type="text" name="scheme_discount_amount" id="scheme_discount_amount" class="input-css calculationAll">
                     </div>
                     --}}
                  </div>
               </div>
               
               <div class="row" style="padding-top:20px;padding-bottom:10px;">
                  <div class="col-md-4">
                     <label for="total_calculation">Total Amount Rs.</label>
                  </div>
                  <div class="col-md-8">
                     <input type="text" name ="total_calculation" readonly value="{{(old('total_calculation')) ? old('total_calculation') : 0 }}" id="total_calculation"  class="input-css inputValidation ">
                     {!! $errors->first('total_calculation', '
                     <p class="help-block">:message</p>
                     ') !!}
                  </div>
               </div>
               {{-- 
               <div class="row">
                  <div class="col-md-9">
                     <label for="tentative_date">Tentative Date Of Delivery</label>
                  </div>
                  <div class="col-md-3">
                     <input type="text" name="tentative_date" id="tentative_date" class="datepicker input-css">
                     {!! $errors->first('tentative_date', '
                     <p class="help-block">:message</p>
                     ') !!}
                  </div>
               </div>
               --}}
               <div id="append_summary">
                  <div class="row" id="discount_final" >
                     <div class="col-md-8">
                        <label id="label"> Discount Amount</label>
                     </div>
                     <div class="col-md-4">
                        <label style="margin-right:10px;">- <span id="cost" > {{(old('discount_amount')) ? old('discount_amount') :  0.0}}</span> </label>
                     </div>
                  </div>
                  <div class="row" id="exchange_value_final" style="{{(old('tos')) ? (old('tos') != 'new') ? 'display:block' : 'display:none' : 'display:none' }}">
                     <div class="col-md-8">
                        <label id="label">Ex-Change Value</label>
                     </div>
                     <div class="col-md-4 "  >
                        <label style="margin-right:10px;">- <span id="cost"> {{(old('exchange_value')) ? old('exchange_value') :  0.0}} </span>
                        </label>
                     </div>
                  </div>
                  <div class="row" id="do_final" style="{{(old('customer_pay_type') == 'finance')?'display:block':'display:none'}}">
                     <div class="col-md-8">
                        <label id="label">DO (Delivery Order) Amount</label>
                     </div>
                     <div class="col-md-4">
                        <label style="margin-right:10px;">- <span id="cost"> {{(old('do')) ? old('do') :  0.0}} </span></label>
                     </div>
                  </div>
                  {{-- 
                  <div class="row" id="payout_final" style="{{ old('finance') ? 'display:block' : 'display:none'}}">
                     <div class="col-md-6">
                        <label id="label"></label>
                     </div>
                     <div class="col-md-6">
                        <label id="cost">{{(old('payout')) ? old('do') :  0.0}}</label>
                     </div>
                  </div>
                  --}}
               </div>
               {{-- 
               <div class="row">
                  <div class="col-md-9">
                     <label for="advance">Advance<sub>*</sub></label>
                  </div>
                  <div class="col-md-3">
                     <input type="text" name="advance" id="advance" class="input-css">
                  </div>
               </div>
               --}}
               <div class="row">
                  <div class="col-md-9">
                     <label for="balance">Balance</label>
                  </div>
                  <div class="col-md-3">
                     <input type="text" name="balance" readonly id="balance"  class="input-css inputValidation " value="{{(old('balance'))? old('balance') : 0.0}}">
                     {!! $errors->first('balance', '
                     <p class="help-block">:message</p>
                     ') !!}
                  </div>
               </div>
            </div>
         </div>
      </div>
      </div>
      <div class="submit-button">
         <button type="submit" class="btn btn-primary">Submit</button>
      </div>
      <br><br>
      </div>
      <!-- Modal -->
      <div class="modal fade" id="saleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
         <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
           <div class="modal-content" style="width: 100%;">
             <div class="modal-header">
               <h4 class="modal-title" id="exampleModalLongTitle">Best Deal Information</h4>
               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                 <span aria-hidden="true">&times;</span>
               </button>
             </div>
               <div class="modal-body" >
                  <div class="alert alert-danger" id="modal-msg" style="display:none">
                  </div>
                  <div class="alert alert-success" id="modal-smsg" style="display:none">
                  </div>
                   <meta name="csrf-token" content="{{ csrf_token() }}" />

                     <input type="hidden" name="best-id" value="{{(old('best-id')) ? old('best-id') : 0 }}" id="best-id" hidden>
                     <input type="hidden" name="best-error" value="{{(old('best-error')) ? old('best-error') : 'error' }}" id="best-error" hidden>
                     <label>Owner Information</label>
                     <div class="row">
                        <div class="col-md-6">
                           <label>Owner Name <sup>*</sup></label>
                           <input id="best-owner_name" type="text"  name="best-owner_name" class="input-css " value="{{(old('best-owner_name')) ? old('best-owner_name') : '' }}">
                           <br>
                        </div>
                        <div class="col-md-6">
                           <label>Owner Position <sup>*</sup></label>
                           <select id="best-owner_pos"  name="best-owner_pos" class="input-css select2" style="width:100%;">
                              <option value="" >Select Owner Position</option>
                              <option value="first" {{(old('best-owner_pos') == 'first') ? 'selected' : '' }} >First Owner</option>
                              <option value="second" {{(old('best-owner_pos') == 'second') ? 'selected' : '' }} >Second Owner</option>
                              <option value="third" {{(old('best-owner_pos') == 'third') ? 'selected' : ''}} >Third Owner</option>
                           </select>
                           <br>
                        </div>
                        <div class="col-md-6">
                           <label>Mobile Number<sup>*</sup></label>
                           <input id="best-mobile_number" type="number" name="best-mobile_number" class="input-css " value="{{(old('best-mobile_number')) ? old('best-mobile_number') : '' }}">
                           <br>
                        </div>
                        <div class="col-md-6">
                           <label>Address <sup>*</sup></label>
                           <textArea id="best-address" type="text" name="best-address" class="form-control ">{{(old('best-address')) ? old('best-address') : ''}}</textArea>
                        </div>
                        <div class="col-md-6">
                           <label>Aadhar Card <sup>*</sup></label>
                           <input id="best-aadhar" type="number" name="best-aadhar" class="input-css " value="{{(old('best-aadhar')) ? old('best-aadhar') : '' }}">
                           <br>
                        </div>
                        <div class="col-md-6">
                           <label>Voter Card <sup>*</sup></label>
                           <input id="best-voter" type="text" name="best-voter" class="input-css" value="{{(old('best-voter')) ? old('best-voter') : '' }}">
                           <br>
                        </div>
                        <div class="col-md-6">
                           <label>Kilometer Reading <sup>*</sup></label>
                           <input id="best-km" type="number" name="best-km" class="input-css " value="{{(old('best-km')) ? old('best-km') : '' }}">
                           <br>
                        </div>
                        <div class="col-md-6">
                           <label>Pan Number <sup>*</sup></label>
                           <input id="best-pan" type="text" name="best-pan" class="input-css " value="{{(old('best-pan')) ? old('best-pan') : '' }}">
                           <br>
                        </div>
                     </div>
                     <br>
                     <label>Product Information</label>
                     <div class="row">
                        <div class="row">
                           <div class="col-md-6">
                              <label>Product Maker <sup>*</sup></label>
                              <select id="best-make"  name="best-make" class="input-css select2" style="width:100%;">
                                 <option value="">Select Maker</option>
                                 @foreach($maker as $k => $v)
                                    <option value="{{$v->key}}" {{(old('best-make') == $v->key) ? 'selected' : '' }} >{{$v->value}}</option>
                                 @endforeach
                              </select>
                              <br>
                           </div>
                           <div class="col-md-6">
                              <label>Frame Number <sup>*</sup></label>
                              <input id="best-frame" type="text" name="best-frame" class="input-css" value="{{(old('best-frame')) ? old('best-frame') : '' }}">
                           </div>
                           
                        </div>
                        <div class="row">
                           <div class="col-md-6">
                              <label>Registration Number <sup>*</sup></label>
                              <input id="best-register" type="" name="best-register" class="input-css" value="{{(old('best-register')) ? old('best-register') : '' }}">
                           </div>
                           <div class="col-md-6" id="best-model_append">
                                 <label>Product Model <sup>*</sup></label>
                                 <input id="best-model" type="text" name="best-model" class="input-css"value="{{(old('best-model')) ? old('best-model') : '' }}">
                                 {{-- <input id="best-model" type="text" name="best-model" class="input-css" readonly> --}}
                                 <br>
                           </div>
                        </div>
                        <div class="col-md-6" id="best-variant_append">
                           <label>Product Variant <sup>*</sup></label>
                           <input id="best-variant" type="text" name="best-variant" class="input-css" value="{{(old('best-variant')) ? old('best-variant') : '' }}">
                           {{-- <input id="best-variant" type="text" name="best-variant" class="input-css" readonly> --}}
                           <br>
                        </div>
                        <div class="col-md-6" id="best-color_append">
                           {{-- <input type="text" name="best-product_id" id="best-product_id" value="{{(old('best-product_id')) ? old('best-product_id') : '' }}"> --}}
                           <label>Product Color Code <sup>*</sup></label>
                           <input id="best-color" type="text" name="best-color" class="input-css" value="{{(old('best-color')) ? old('best-color') : '' }}">
                           {{-- <input id="best-color" type="text" name="best-color" class="input-css" readonly> --}}
                           <br>
                        </div>
                        <div class="col-md-6">
                           <label>Year Of Manufacturing <sup>*</sup></label>
                           <input id="best-yom" type="text" name="best-yom" class="input-css" value="{{(old('best-yom')) ? old('best-yom') : '' }}">
                           <br>
                        </div>
                        <div class="col-md-6">
                           <label>Date Of Sale On RC <sup>*</sup></label>
                           <input id="best-sale_date" type="text" name="best-sale_date" class="input-css datepicker5" value="{{(old('best-sale_date')) ? old('best-sale_date') : '' }}">
                           <br>
                        </div>
                        
                     </div>
                     <br>
                     <label>Other's Information</label>
                     <div class="col-md-12">
                        {{-- hypo start --}}
                        <div class="col-md-4">
                           <label>Hypothecation <sup>*</sup></label>
                           <div class="col-md-6">
                              <p> <input type="radio" name="best-hypo" value="yes" class=" best-hypo"  {{(old('best-hypo'))?((old('best-hypo') == 'yes') ? 'checked' : ''  ) : '' }} > Yes</p>
                           </div>
                           <div class="col-md-6">
                              <input type="radio" name="best-hypo" value="no" class=" best-hypo" {{(old('best-hypo'))?((old('best-hypo') == 'no') ? 'checked' : ''  ) : 'checked' }}> No
                           </div>
                           <br>
                           {{-- hypo Info --}}
                           <div class="col-md-12" id="best-hypo_yes" style="{{(old('best-hypo'))?((old('best-hypo') == 'yes') ? 'display:block' : 'display:none'  ) : 'display:none' }}">
                              <div class="col-md-6">
                                 <label> Bank Name <sup>*</sup></label>
                                 <select name="best-hypo_bank" class="input-css " id="best-hypo_bank">
                                    <option value="">Select Company Name</option>
                                    @foreach($company_name as $key => $val)
                                       <option value="{{$val->id}} {{(old('best-hypo_bank') == $v->id) ? 'selected' : '' }} ">{{$val->company_name}}</option>
                                    @endforeach
                                 </select>
                                 {{-- <input id="best-hypo_bank" type="text" name="best-hypo_bank" class="input-css" value="{{(old('best-hypo_bank')) ? old('best-hypo_bank') : '' }}"> --}}
                                 <br>
                              </div>
                              <div class="col-md-6">
                                 <label> NOC Status <sup>*</sup></label>
                                 <select name="best-hypo_noc" id="best-hypo_noc" class="input-css select2">
                                    <option value="">Status</option>
                                    <option value="Done" {{((old('best-hypo_noc') == 'Done') ? 'selected' : '' )}} >Done</option>
                                    <option value="NotDone" {{((old('best-hypo_noc') == 'NotDone') ? 'selected' : '' )}} >Not Done</option>
                                    <option value="NotRequired" {{((old('best-hypo_noc') == 'NotRequired') ? 'selected' : '' )}} >Not Required</option>
                                 </select>
                                 {{-- <input id="best-hypo_noc" type="text" name="best-hypo_noc" class="input-css" value="{{(old('best-hypo_noc')) ? old('best-hypo_noc') : ''}}"> --}}
                                 <br>
                              </div>
                           </div>
                        </div>
                        {{-- warrenty status --}}
                        <div class="col-md-4">
                           <label>warrenty Status <sup>*</sup></label>
                           <div class="col-md-6">
                              <p> <input type="radio" name="best-warrenty" value="yes" class=" best-warrenty" {{(old('best-warrenty'))?((old('best-warrenty') == 'yes') ? 'checked' : ''  ) : '' }} > Yes</p>
                           </div>
                           <div class="col-md-6">
                              <input type="radio" name="best-warrenty" value="no" class=" best-warrenty" {{(old('best-warrenty'))?((old('best-warrenty') == 'no') ? 'checked' : ''  ) : 'checked' }}> No
                           </div>
                           <br>
                           {{-- warrenty Info --}}
                           <div class="col-md-12" id="best-warrenty_yes" style="{{(old('best-warrenty'))?((old('best-warrenty') == 'yes') ? 'display:block' : 'display:none'  ) : 'display:none' }}">
                              {{-- <div class="col-md-6">
                                 <label> Start Date <sup>*</sup></label>
                                 <input id="best-warrenty_start" type="text" name="best-warrenty_start" class="input-css datepicker5" value="{{(old('best-warrenty_start')) ? old('best-warrenty_start') : ''   }}">
                                 <br>
                              </div> --}}
                              <div class="col-md-12">
                                 <label> End Date <sup>*</sup></label>
                                 <input id="best-warrenty_end" type="text" name="best-warrenty_end" class="input-css datepicker5" value="{{(old('best-warrenty_end')) ? old('best-warrenty_end') : '' }}">
                                 <br>
                              </div>
                           </div>
                           {{-- warrenty Info end --}}
                        </div>
                        {{-- Insurance status --}}
                        <div class="col-md-4">
                           <label>Insurance Status <sup>*</sup></label>
                           <div class="col-md-6">
                              <p> <input type="radio" name="best-insurance" value="yes" class=" best-insurance" {{(old('best-insurance'))?((old('best-insurance') == 'yes') ? 'checked' : ''  ) : '' }}> Yes</p>
                           </div>
                           <div class="col-md-6">
                              <input type="radio" name="best-insurance" value="no" class=" best-insurance" {{(old('best-insurance'))?((old('best-insurance') == 'no') ? 'checked' : ''  ) : 'checked' }}> No
                           </div>
                           <br>
                        </div>
                     </div>
                     {{-- Insurance Info --}}
                     <div class="row" id="best-insurance_yes" style="{{(old('best-insurance'))?((old('best-insurance') == 'yes') ? 'display:block' : 'display:none'  ) : 'display:none' }}">
                        <label>Insurance Information <sup>*</sup></label>
                        <div class="col-md-4 card-border">
                           <label>OD <sup>*</sup></label>
                           <div class="col-md-6">
                              <label>Policy Number<sup>*</sup></label>
                              <input id="od-best-insurance_policy" type="text" name="od-best-insurance_policy" class="input-css " value="{{(old('od-best-insurance_policy')) ? old('od-best-insurance_policy') : ''  }}">
                                 {!! $errors->first('od-best-insurance_policy', '<p class="help-block">:message</p>') !!}

                             <br>
                           </div>
                           <div class="col-md-6">
                              <label> Company Name <sup>*</sup></label>
                              <select name="od-best-insurance_company" class="input-css " id="od-best-insurance_company">
                                 <option value="">Select Company Name</option>
                                 @foreach($insur_company as $key => $val)
                                    <option value="{{$val->id}}" {{(old('od-best-insurance_company') == $val->id) ? 'selected' : '' }} >{{trim($val->name)}}</option>
                                 @endforeach
                              </select>
                              {{-- <input id="od-best-insurance_company" type="text" name="od-best-insurance_company" class="input-css " value="{{(old('od-best-insurance_company')) ? old('od-best-insurance_company') : ''   }}"> --}}
                              {!! $errors->first('od-best-insurance_company', '<p class="help-block">:message</p>') !!}

                              <br>
                           </div>
                           <div class="col-md-6">
                              <label> Date Of Policy Start <sup>*</sup></label>
                              <input id="od-best-insurance_policy_start" type="text" name="od-best-insurance_policy_start" data-date-end-date="0d" onchange="policy_start_change(this)" class="input-css policy-start datepicker5 " value="{{(old('od-best-insurance_policy_start')) ? old('od-best-insurance_policy_start') : ''  }}">
                              {!! $errors->first('od-best-insurance_policy_start', '<p class="help-block">:message</p>') !!}
                              <br>
                           </div>
                           <div class="col-md-6">
                              <label> Date Of Policy End <sup>*</sup></label>
                              <input id="od-best-insurance_policy_end" type="text" name="od-best-insurance_policy_end"  class="input-css datepicker5 policy-end" value="{{(old('od-best-insurance_policy_end')) ? old('od-best-insurance_policy_end') : ''  }}">
                              {!! $errors->first('od-best-insurance_policy_end', '<p class="help-block">:message</p>') !!}

                              <br>
                           </div>
                        </div>
                        <div class="col-md-4 card-border">
                           <label>
                              <div class="col-md-2">
                                 TP
                              </div>
                              <div class="col-md-5">
                                 <input type="checkbox" name="ta-check" value="od"> Copy Of OD
                              </div>
                           </label>
                           <div class="col-md-6">
                              <label>Policy Number<sup>*</sup></label>
                              <input id="ta-best-insurance_policy" type="text" name="ta-best-insurance_policy" class="input-css " value="{{(old('ta-best-insurance_policy')) ? old('ta-best-insurance_policy') : ''  }}">
                                 {!! $errors->first('ta-best-insurance_policy', '<p class="help-block">:message</p>') !!}
                             <br>
                           </div>
                           <div class="col-md-6">
                              <label> Company Name <sup>*</sup></label>
                              <select name="ta-best-insurance_company" class="input-css " id="ta-best-insurance_company">
                                 <option value="">Select Company Name</option>
                                 @foreach($insur_company as $key => $val)
                                    <option value="{{$val->id}}" {{(old('ta-best-insurance_company') == $val->id) ? 'selected' : '' }} >{{trim($val->name)}}</option>
                                 @endforeach
                              </select>
                              {{-- <input id="ta-best-insurance_company" type="text" name="ta-best-insurance_company" class="input-css " value="{{(old('ta-best-insurance_company')) ? old('ta-best-insurance_company') : ''   }}"> --}}
                              {!! $errors->first('ta-best-insurance_company', '<p class="help-block">:message</p>') !!}
                              <br>
                           </div>
                           <div class="col-md-6">
                              <label> Date Of Policy Start <sup>*</sup></label>
                              <input id="ta-best-insurance_policy_start" type="text" name="ta-best-insurance_policy_start" data-date-end-date="0d" onchange="policy_start_change(this)" class="input-css policy-start datepicker5 " value="{{(old('ta-best-insurance_policy_start')) ? old('ta-best-insurance_policy_start') : ''  }}">
                              {!! $errors->first('ta-best-insurance_policy_start', '<p class="help-block">:message</p>') !!}

                              <br>
                           </div>
                           <div class="col-md-6">
                              <label> Date Of Policy End <sup>*</sup></label>
                              <input id="ta-best-insurance_policy_end" type="text" name="ta-best-insurance_policy_end" class="input-css policy-end datepicker5 " value="{{(old('ta-best-insurance_policy_end')) ? old('ta-best-insurance_policy_end') : ''  }}">
                              {!! $errors->first('ta-best-insurance_policy_end', '<p class="help-block">:message</p>') !!}

                              <br>
                           </div>
                        </div>
                        <div class="col-md-4 card-border">
                           <label>
                              <div class="col-md-2">
                                 CPA 
                              </div>
                              <div class="col-md-5">
                                 <input type="checkbox" name="cpa-check" value="od"> Copy Of OD
                              </div>
                           </label>
                           <div class="col-md-6">
                              <label>Policy Number<sup>*</sup></label>
                              <input id="cpa-best-insurance_policy" type="text" name="cpa-best-insurance_policy" class="input-css " value="{{(old('cpa-best-insurance_policy')) ? old('cpa-best-insurance_policy') : ''  }}">
                                 {!! $errors->first('cpa-best-insurance_policy', '<p class="help-block">:message</p>') !!}

                             <br>
                           </div>
                           <div class="col-md-6">
                              <label> Company Name <sup>*</sup></label>
                              <select name="cpa-best-insurance_company" class="input-css " id="cpa-best-insurance_company">
                                 <option value="">Select Company Name</option>
                                 @foreach($insur_company as $key => $val)
                                    <option value="{{$val->id}}" {{(old('cpa-best-insurance_company') == $val->id) ? 'selected' : '' }} >{{trim(trim($val->name))}}</option>
                                 @endforeach
                              </select>
                              {{-- <input id="cpa-best-insurance_company" type="text" name="cpa-best-insurance_company" class="input-css " value="{{(old('cpa-best-insurance_company')) ? old('cpa-best-insurance_company') : ''   }}"> --}}
                              {!! $errors->first('cpa-best-insurance_company', '<p class="help-block">:message</p>') !!}

                              <br>
                           </div>
                           <div class="col-md-6">
                              <label> Date Of Policy Start <sup>*</sup></label>
                              <input id="cpa-best-insurance_policy_start" type="text" name="cpa-best-insurance_policy_start" data-date-end-date="0d" onchange="policy_start_change(this)" class="input-css policy-start datepicker5 " value="{{(old('cpa-best-insurance_policy_start')) ? old('cpa-best-insurance_policy_start') : ''  }}">
                              {!! $errors->first('cpa-best-insurance_policy_start', '<p class="help-block">:message</p>') !!}

                              <br>
                           </div>
                           <div class="col-md-6">
                              <label> Date Of Policy End <sup>*</sup></label>
                              <input id="cpa-best-insurance_policy_end" type="text" name="cpa-best-insurance_policy_end" class="input-css policy-end datepicker5 "  value="{{(old('cpa-best-insurance_policy_end')) ? old('cpa-best-insurance_policy_end') : ''  }}">
                              {!! $errors->first('cpa-best-insurance_policy_end', '<p class="help-block">:message</p>') !!}

                              <br>
                           </div>
                        </div>
                     </div>
                     {{-- Insurance Info end --}}
   
                     <div class="row">
                        <div class="col-md-6">
                           <label>Number Of Key's <sup>*</sup></label>
                           <input type="number" name="best-key" id="best-key" class="input-css" value="{{(old('best-key')) ? old('best-key') : 0 }}">
                        </div>
                        <div class="col-md-6">
                           <label>Purchase Price <sup>*</sup></label>
                           <input type="text" name="best-purchase" value="{{(old('best-purchase')) ? old('best-purchase') : 0 }}" id="best-purchase" class="input-css">
                        </div>
                     </div>
                     <div class="row">
                        <div class="col-md-6">
                           <label>RC Status <sup>*</sup></label>
                           <select name="best-rc_status" id="best-rc_status" class="input-css select2 select2-dropdown--above select2-container--above" style="width:100%;">
                              <option value="missing" {{(old('best-rc_status') == 'missing') ? 'selected' : '' }} >Missing</option>
                              <option value="original" {{(old('best-rc_status') == 'original') ? 'selected' : '' }} >Original</option>
                              <option value="available" {{(old('best-rc_status') == 'available') ? 'selected' : '' }} >Available</option>
                           </select>
                        </div>
                     </div>
                  
                  
                     <div class="modal-footer">
                     <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                     <button type="button" id="btnUpdate" onclick="bestDealUpdate()" class="btn btn-success">Submit</button>
                     </div>
                  
               </div>
           </div>
         </div>
       </div>
   </form>

   <!-- Start css for padding in iframe box --->
   <style>
      .iframe-col .box
      {
      padding: 15px 0px;
      }
      .iframe-col .box iframe
      {
      width: 100%;
      height: 60%;
      }
      .prl-0
      {
      padding-left: 0px;
      padding-right: 0px;
      }
      .prl-10
      {
      padding-left: 10px;
      padding-right: 10px;
      }
   </style>
   <!-- End css for padding in iframe box --->        
</section>
@endsection