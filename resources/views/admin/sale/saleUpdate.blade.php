@extends($layout)
@section('title', __('Update Sale'))
@section('breadcrumb')
<li><a href="/admin/sale/list"><i class=""></i> {{__('Sale List')}} </a></li>
@endsection
@section('js')
<script src="/js/pages/sale/saleUpdate.js"></script>
<script>
   var csd_discount = {{$csd_discount}}; // in percentage
   var exist_cust = @php echo json_encode($customer); @endphp;
   var scheme = @php echo json_encode($scheme); @endphp;
   var oldState = @php echo json_encode((old('state')) ? old('state') : '' ); @endphp;
   var oldCity = @php echo json_encode((old('city')) ? old('city') : '' ); @endphp;
   
   var oldLocation = @php echo json_encode((old('location')) ? old('location') : '' ); @endphp;

   var ex_showroom_price =  @php echo json_encode((old('ex_showroom_price')) ? old('ex_showroom_price') : $sale['ex_showroom_price'] ); @endphp;
   var showroomPriceHidden =  @php echo json_encode((old('showroomPriceHidden')) ? old('showroomPriceHiddens') : $sale['ex_showroom_price'] ); @endphp;
   if(oldState || oldCity)
   {
      // console.log('old',oldState,oldCity);
      $.ajax({
         url: "/admin/customer/city/"+parseInt(oldState),
         method:'GET',
         success: function(result){
            //console.log(result);
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
   var oldSaleProductId = @php echo json_encode($sale['product_id']); @endphp;
   var oldSaleStoreId = @php echo json_encode($sale['store_id']); @endphp;
   var oldCustomer = @php echo json_encode((old('select_customer_name')) ? old('select_customer_name') : $sale['customer_id'] ); @endphp;
   
   
   var checkOldExistForPartNoNameVal = @php echo json_encode((old('accessories')) ? old('accessories') : []); @endphp;
   var oldaccessories = @php echo json_encode((old('accessories')) ? old('accessories') : $accessories ); @endphp;
   var oldAccVal = @php
                    $arr = ((old('accessories')) ? old('accessories') : $accessories_data);
                    $out = [];
                    if(count($arr) > 0){
                     for($i = 0 ; $i < count($arr) ; $i++)
                     {
                           $out[$i]['id'] =  (old('accessories'))? $arr[$i] : $arr[$i]['nid'] ;  
                           // $out[$i]['nameId'] =  (old('accessories'))? $arr[$i] : $arr[$i]['accessories'] ;  
                           $out[$i]['qty'] =  (old('accessories'))? old('accessories_qty_'.$arr[$i]) : $arr[$i]['qty'] ;  
                           $out[$i]['partNo'] =  (old('accessories'))?old('accessories_partNo_'.$arr[$i]) : $arr[$i]['part_no'];  
                           $out[$i]['amount'] =  (old('accessories'))? old('accessories_amount_'.$arr[$i]) : $arr[$i]['amount'];  
                           // $out[$i]['desc'] =  (old('accessories')) ? old('accessories_desc_'.$arr[$i]) : $arr[$i]['accessories_desc'];  
                     } 
                    }
                echo json_encode($out);
                @endphp;

   var oldOtherAcc = @php echo json_encode((old('other_part_no')) ? old('other_part_no') : $other_accessories ); @endphp;
   var oldOtherAccVal = @php
                    $arr = ((old('other_part_no')) ? old('other_part_no') : $other_accessories);
                    $out = [];
                    for($i = 0 ; $i < count($arr) ; $i++)
                    {
                        $out[$i]['partNo'] =  (old('other_part_no')) ?$arr[$i] : $arr[$i]['part_no'];  
                        $out[$i]['qty'] =  (old('other_part_qty')) ? old('other_part_qty')[$i] : $arr[$i]['part_no']; 
                        $out[$i]['amount'] = (old('other_part_amount')) ? old('other_part_amount')[$i] : $arr[$i]['amount'];  
                    } 
                echo json_encode($out);
                @endphp;
   
   var oldStore = @php echo json_encode((old('store_name')) ? old('store_name') : $sale['store_id'] ); @endphp;
   var oldModelName = @php echo json_encode((old('model_name')) ? old('model_name') : $sale['model_name'] ); @endphp;
   var oldModelVariant = @php echo json_encode((old('model_variant')) ? old('model_variant') : $sale['model_variant'] ); @endphp;
   var oldProduct = @php echo json_encode((old('prod_name')) ? old('prod_name') : $sale['product_id'] ); @endphp;

   if(oldModelName != '' && oldStore != '')
   {
    //   console.log('old2',oldProduct);
        $('#model_name').val(oldModelName);
      //   $('#store_name').val(oldStore);
        $('#model_name').trigger('change');
        
   }
   
   if(oldCustomer != '')
   {
    //   console.log('old2',oldProduct);
        $('#select_customer_name').val(oldCustomer);
        $('#select_customer_name').trigger('change');
   }
   

   var financeCompanyName = @php echo json_encode((old('finance_company_name')) ? old('finance_company_name') : $sale['finance_company_name'] ); @endphp;
   var financeName = @php echo json_encode((old('finance_name')) ? old('finance_name') : $sale['finance_name'] ); @endphp;
   var customer_pay_type = @php echo json_encode((old('customer_pay_type')) ? old('customer_pay_type') : $sale['customer_pay_type'] ); @endphp;
   // console.log($("#").val());
  
   if(customer_pay_type == 'finance')
   {
      if(financeCompanyName)
      {
         $("#finance_company_name").val(financeCompanyName).trigger('change');
      }
   }

   $('#dob').datepicker({
   format: "yyyy-mm-dd"
   });
   $('.datepicker5').datepicker({
   format: "yyyy-mm-dd"
   });

   //  console.log(product);
   // console.log(exist_cust);
     
</script>
@endsection
@section('main_section')
<section class="content">
   @include('admin.flash-message')
   @if(!empty($errors->all()))
   <div class="alert alert-danger">
      @foreach($errors->all() as $key => $val)
      {{ $val }}
      @endforeach
      {{-- {{$errors}} --}}
   </div>
   @endif
  
   @yield('content')
   @include('layouts.updatebooking_tab')
   <!-- general form elements -->
   <form id="stock-movement-validation" action="/admin/update/sale/{{$sale['id']}}" method="POST">
      @csrf
      <div class="box box-primary">
         <div class="row ">
            <div class="col-md-12 prl-0 ">
               <div class="row">
                  {{-- <div class="col-md-3">
                     <label>Sale Number<sup>*</sup></label>
                     <input type="text" name="sale_no" readonly  class="input-css" value="{{(old('sale_no')) ? old('sale_no') : $sale['sale_no']}}">
                     {!! $errors->first('sale_no', '
                     <p class="help-block">:message</p>
                     ') !!}
                  </div> --}}
                  <div class="col-md-3">
                     <label>Sale Date<sup>*</sup></label>
                     <input type="text" name="sale_date" data-date-end-date="0d"  class="input-css datepicker5 " value="{{(old('sale_date')) ? old('sale_date') : $sale['sale_date']}}">
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
                       
                     </select>
                     {!! $errors->first('prod_name', '
                     <p class="help-block">:message</p>
                     ') !!}
                     <input type="hidden" hidden name="prod_basic" value="{{(old('prod_basic')) ? old('prod_basic') : 0}}">
                  </div>
                  {{-- This Product Name as a Color Code --}}
                  <div class="col-md-3">
                     <label>Store Name<sup>*</sup></label>
                     <select name="store_name" class="input-css select2 selectValidation" id="store_name">
                        @foreach($store as $key => $val)
                        {{-- @if($sale['store_id'] == $val['id']) --}}
                           <option value="{{$val['id']}}" {{(old('store_name') == $val['id']) ? 'selected' : (($sale['store_id'] == $val['id'])? 'selected' : '') }}> {{$val['name'].'-'.$val['store_type']}} </option>
                        {{-- @endif --}}
                        @endforeach
                     </select>
                     {!! $errors->first('store_name', '<p class="help-block">:message</p>') !!}
                  </div>

               </div>
            </div>
            <div class="col-md-6 prl-0  " style="margin-top:10px;">
               <div class="row box box box-solid box-default">
                  <div class="col-md-12">
                     {{-- <div class="row">
                        <label for="select_customer">Customer Details <sub>*</sub></label>
                        <div class="col-md-4">
                           <div class="radio">
                              <label><input autocomplete="off" {{(old('select_customer') == 'new_customer') ? 'checked' : '' }}  type="radio" class="" value="new_customer" name="select_customer">New</label>
                           </div>
                        </div>
                        <div class="col-md-4">
                           <div class="radio">
                              <label><input autocomplete="off" {{(old('select_customer') == 'exist_customer') ? 'checked' : '' }} {{(old('select_customer'))? 'checked' : 'checked'}} type="radio" class="" value="exist_customer" name="select_customer">Existing</label>
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
                     </div> --}}
                     <div class="row">
                        <label> Sale Type <sup>*</sup> </label>
                        @if($sale['sale_type'] == 'general')
                        <div class="col-md-6">
                           <div class="radio">
                              <label><input autocomplete="off" {{ (old('sale_type')) ? ((old('sale_type') == 'general') ? 'checked' : '') : (($sale['sale_type'] == 'general')? 'checked' : '') }} type="radio" class="" value="general" name="sale_type">General</label>
                           </div>
                        </div>
                        @elseif($sale['sale_type'] == 'corporate')
                        <div class="col-md-6">
                           <div class="radio">
                              <label><input autocomplete="off" {{ (old('sale_type')) ? ((old('sale_type') == 'corporate') ? 'checked' : '' ) : (($sale['sale_type'] == 'corporate')? 'checked' : '') }} type="radio" class="" value="corporate" name="sale_type">Corporate</label>
                           </div>
                        </div>
                        @endif
                     </div>
                     <div class="" id="customer_details">
                        {{-- <div class="row" id="pre_booking_no" style="{{(old('select_customer') == 'booking')? 'display:block' :'display:none'}}">
                           <div class="col-md-12" >
                              <label >Booking Number <sub>*</sub></label>
                              <input type="text" name="pre_booking" id="pre_booking"  class="input-css blank-customer inputValidation " placeholder="Booking Number" value="{{(old('pre_booking')) ? old('pre_booking') : '' }}">
                              {!! $errors->first('pre_booking', '
                              <p class="help-block">:message</p>
                              ') !!}
                           </div>
                           <div style="display:none">
                              <input type="hidden" hidden name="booking_id" id="booking_id" value="{{(old('booking_id')) ? old('booking_id') : '' }}">
                              <input type="hidden" hidden name="booking_number" id="booking_number" value="{{(old('booking_number')) ? old('booking_number') : '' }}">
                           </div>
                        </div> --}}
                        <div class="row" >
                           
                           <div class="col-md-6"   id="exist_customer">
                           <label>Customer Name<sup>*</sup></label>
                           <select name="select_customer_name" id="select_customer_name" class="input-css select2 selectValidation" style="width:200px;">
                              <option value="">Select Customer Name</option>
                              @foreach($customer as $key => $val)
                              <option value="{{$val['id']}}" {{(old('select_customer_name') == $val['id']) ? 'selected' : '' }} > {{$val['name'].' - '.$val['mobile']}} </option>
                              @endforeach
                           </select>
                           {!! $errors->first('select_customer_name', '
                           <p class="help-block">:message</p>
                           ') !!}
                        </div>
                        <div class="col-md-6">
                           <label> Relation <sup>*</sup></label>
                           <div class="col-md-4">
                              <select name="relation_type" id="relation_type" class="select2 input-css blank-customer selectValidation">
                                 <option value="">Select</option>
                                 <option value="S/O" {{(old('relation_type') == 'S/O') ? 'selected' : '' }}>S/O</option>
                                 <option value="D/O" {{(old('relation_type') == 'D/O') ? 'selected' : '' }}>D/O</option>
                                 <option value="W/O" {{(old('relation_type') == 'W/O') ? 'selected' : '' }}>W/O</option>
                              </select>
                              {!! $errors->first('relation_type', '
                              <p class="help-block">:message</p>
                              ') !!}
                           </div>
                           <div class="col-md-8">
                              <input type="text" name="relation" class="input-css blank-customer inputValidation " value="{{(old('relation')) ? old('relation') : '' }}">
                              {!! $errors->first('relation', '
                              <p class="help-block">:message</p>
                              ') !!}
                           </div>
                        </div>
                        @if($sale['sale_type'] == 'corporate')
                        <div class="row" id="corporate_info" style="{{ (old('sale_type')) ? ((old('sale_type') == 'corporate') ? 'display:block' : 'display:none' ) : (($sale['sale_type'] == 'corporate')? 'display:block' : 'display:none') }}">
                           <div class="col-md-6">
                              <label>Care Of <sup>*</sup></label>
                              <input type="text"  name="care_of" id="care_of"  class="input-css blank-customer {{(old('sale_type') == 'corporate') ? 'inputValidation' : (($sale['sale_type'] == 'corporate') ? 'inputValidation' : '') }} " value="{{(old('care_of')) ? old('care_of') : '' }}" placeholder="Enter Care Of"> 
                              {!! $errors->first('care_of', '<p class="help-block">:message</p>') !!}
                           </div>
                           <div class="col-md-6">
                              <label>GST Number <sup>*</sup> </label>
                              <input type="text"  name="gst" id="gst"  class="input-css blank-customer {{(old('gst')) ? 'inputValidation' : (($sale['sale_type'] == 'corporate') ? 'inputValidation' : '') }} " value="{{(old('gst')) ? old('gst') : '' }}" placeholder="Enter GST Number"> 
                              {!! $errors->first('gst', '<p class="help-block">:message</p>') !!}
                           </div>
                        </div>
                        @endif
                        <div class="row">
                           <div class="col-md-6">
                              <label>Aadhar Number <sup>*</sup></label>
                              <input type="number"  name="aadhar" id="aadhar"  class="input-css   NoChange" value="{{(old('aadhar')) ? old('aadhar') : '' }}" placeholder="Enter Aadhar Number"> 
                              {!! $errors->first('aadhar', '
                              <p class="help-block">:message</p>
                              ') !!}
                           </div>
                           <div class="col-md-6">
                              <label>Voter Number </label>
                              <input type="text"  name="voter" id="voter"  class="input-css  NoChange" value="{{(old('voter')) ? old('voter') : '' }}" placeholder="Enter Voter Number"> 
                              {!! $errors->first('voter', '
                              <p class="help-block">:message</p>
                              ') !!}
                           </div>
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
                     <div class="col-md-12">
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
                        <input type="number" name="pin" class="input-css blank-customer inputValidation " value="{{(old('pin')) ? old('pin') : '' }}">
                        {!! $errors->first('pin', '
                        <p class="help-block">:message</p>
                        ') !!}
                     </div>
                     <div class="col-md-6">
                        <label>Email<sup>*</sup></label>
                        <input type="email" name="email" class="input-css blank-customer  " value="{{(old('email')) ? old('email') : '' }}">
                        {!! $errors->first('email', '
                        <p class="help-block">:message</p>
                        ') !!}
                     </div>
                  </div>
                  <div class="row">
                     <div class="col-md-6">
                        <label>Mobile<sup>*</sup></label>
                        <input type="number" name="mobile" id="mobile" class="input-css blank-customer inputValidation " value="{{(old('mobile')) ? old('mobile') : '' }}">  
                        {!! $errors->first('mobile', '
                        <p class="help-block">:message</p>
                        ') !!}
                     </div>
                  </div>
                  <div class="row">
                     <label><u>Reference</u><sup></sup></label>
                     <div class="col-md-6">
                        <label>Reference Name<sup></sup></label>
                        <input type="text" name="reference" class="input-css  " value="{{(old('reference')) ? old('reference') : $sale['ref_name'] }}">
                        {!! $errors->first('reference', '
                        <p class="help-block">:message</p>
                        ') !!}
                     </div>
                     <div class="col-md-6">
                        <label>Relation<sup></sup></label>
                        <input type="text" name="ref_relation" class="input-css" value="{{(old('ref_relation')) ? old('ref_relation') : $sale['ref_relation'] }}">
                        {!! $errors->first('ref_relation', '
                        <p class="help-block">:message</p>
                        ') !!}
                     </div>
                  </div>
                  <div class="row" style="margin-bottom:5px;">
                     <div class="col-md-12">
                        <label>Mobile<sup></sup></label>
                        <input type="text" name="ref_mobile" class="input-css" value="{{(old('ref_mobile')) ? old('ref_mobile') : $sale['ref_mobile'] }}">
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
                        <option value="{{$val['id']}}" {{(old('sale_executive') == $val['id']) ? 'selected' : (($sale['sale_executive'] == $val['id'])? 'selected' : '') }} > {{$val['name']}} </option>
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
                     @if($sale['type_of_sale'] == null)
                     <div class="col-md-4">
                        <div class="radio">
                           <label><input autocomplete="off"  {{old('tos')?((old('tos') == 'new') ? 'checked':'') : (($sale['type_of_sale'] == null)? 'checked' : '') }}  type="radio" class="" value="new" name="tos">New</label>
                        </div>
                     </div>
                     @elseif($sale['type_of_sale'] == 'exchange')
                     <div class="col-md-4">
                        <div class="radio">
                           <label><input autocomplete="off" {{old('tos')?((old('tos') == 'exchange') ? 'checked': '' ):  (($sale['type_of_sale'] == 'exchange')? 'checked' : '') }} type="radio" class="" value="exchange" name="tos">Exchange</label>
                        </div>
                     </div>
                     @elseif($sale['type_of_sale'] == 'best_deal')
                     <div class="col-md-4">
                        <div class="radio">
                           <label><input autocomplete="off" {{old('tos')?((old('tos') == 'best_deal') ? 'checked': '' ): (($sale['type_of_sale'] == 'best_deal')? 'checked' : '') }} type="radio" class="" value="best_deal" name="tos">Best Deal</label>
                        </div>
                     </div>
                     @endif
                     {!! $errors->first('tos', '<p class="help-block">:message</p>') !!}
                  </div>
               </div>
               @if($sale['type_of_sale'] == 'exchange' || $sale['type_of_sale'] == 'best_deal')
               <div id="tos_exchange" style="{{old('tos')?((old('tos') == 'new') ? 'display:none;' : 'display:block') : (($sale['type_of_sale'] == null)? 'display:none;' : 'display:block;') }} ">
                  <div class="row" style="margin-bottom:5px;">
                     <div class="col-md-6">
                        <label>If Exchange (Model)<sup></sup></label>
                        <input type="text" name="exchange_model" class="input-css inputValidation " value="{{(old('exchange_model')) ? old('echange_model') : $sale['exBest_model'] }}">
                        {!! $errors->first('exchange_model', '
                        <p class="help-block">:message</p>
                        ') !!}
                     </div>
                     <div class="col-md-6">
                        <label>Year Of Manufacturing<sup></sup></label>
                        <input type="text" name="exchange_yom" id="exchange_yom" class="input-css inputValidation" value="{{(old('exchange_yom')) ? old('exchange_yom') : $sale['exBest_yom'] }}">
                        {!! $errors->first('exchange_yom', '
                        <p class="help-block">:message</p>
                        ') !!}
                     </div>
                  </div>
                  <div class="row" style="margin-bottom:5px;">
                     <div class="col-md-6">
                        <label>Registration Number<sup></sup></label>
                        <input type="text" name="exchange_register" class="input-css inputValidation" value="{{(old('exchange_register')) ? old('exchange_register') : $sale['exBest_register_no'] }}">
                        {!! $errors->first('exchange_register', '
                        <p class="help-block">:message</p>
                        ') !!}
                     </div>
                     <div class="col-md-6">
                        <label>Ex-Change Value<sup></sup></label>
                        <input type="text" name="exchange_value" id="exchange_value" class="input-css inputValidation" value="{{(old('exchange_value')) ? old('exchange_value') : $sale['exBest_value'] }}" onfocusout="subcalculate(this)">
                        {!! $errors->first('exchange_value', '
                        <p class="help-block">:message</p>
                        ') !!}
                     </div>
                  </div>
               </div>
               @endif
            </div>
         </div>
         <div class="row box box box-solid box-default ">
            <div class="col-md-12 ">
               <label><u>Accessories</u><sup>*</sup></label>
               <div class="row">
                  <div class="col-md-12" id="accessories_filter" >
                     {{-- <label for="acc_filter">Accessories Filter -</label> --}}
                     <div class="col-md-4">
                        <label><input type="radio" class="filter"  value="connection" name="acc_filter">Connection</label>
                     </div>
                     <div class="col-md-4">
                        <label><input type="radio" class="filter" value="hop" name="acc_filter">HOP</label>
                     </div>
                     <div class="col-md-4">
                        <label><input type="radio" class="filter" value="full" name="acc_filter">FULL</label>
                     </div>
                     {!! $errors->first('acc_filter', '
                     <p class="help-block">:message</p>
                     ') !!}
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
                        <label><input type="radio" {{old('customer_pay_type')?((old('customer_pay_type') == 'cash') ? 'checked' : '' ) : (($sale['customer_pay_type'] == 'cash') ? 'checked' : '') }}  class="customer_pay_type" value="cash" name="customer_pay_type">Cash</label>
                     </div>
                     <div class="col-md-4">
                        <label><input type="radio" {{old('customer_pay_type')?((old('customer_pay_type') == 'finance') ? 'checked': '' ): (($sale['customer_pay_type'] == 'finance') ? 'checked' : '') }} class="customer_pay_type" value="finance" name="customer_pay_type">Finance</label>
                     </div>
                     <div class="col-md-4">
                        <label><input type="radio" {{old('customer_pay_type')?((old('customer_pay_type') == 'self_finance') ? 'checked':'' ): (($sale['customer_pay_type'] == 'self_finance') ? 'checked' : '') }} class="customer_pay_type" value="self_finance" name="customer_pay_type">Self Finance</label>
                     </div>
                     {!! $errors->first('customer_pay_type', '
                     <p class="help-block">:message</p>
                     ') !!}
                  </div>
               </div>
               <div class="row" id="finance" style="{{old('customer_pay_type')?((old('customer_pay_type') == 'finance')? 'display:block;' :'display:none;') : (($sale['customer_pay_type'] == 'finance')? 'display:block;' : 'display:none;') }} margin-bottom:10px;">
                  <label><u>Financier Details</u><sup>*</sup></label>
                  <div class="col-md-6">
                     <label>Company Name<sup>*</sup></label>
                     {{-- <input type="text" name="finance_company_name" class="input-css inputValidation" value="{{(old('finance_company_name'))? old('finance_company_name') : '' }}"> --}}
                     <select name="finance_company_name" class="input-css selectValidation select2" id="finance_company_name" style="width: 100%">
                        <option value="">Select Company Name</option>
                        @foreach($company_name as $key => $val)
                           <option value="{{$val->id}}">{{$val->company_name}}</option>
                        @endforeach
                     </select>
                     {!! $errors->first('finance_company_name', '<p class="help-block">:message</p>') !!}
                  </div>
                  <div class="col-md-6">
                     <label>Financier Executive<sup>*</sup></label>
                     {{-- <input type="text" name="finance_name" class="input-css inputValidation" value="{{(old('finance_name'))? old('finance_name') : '' }}"> --}}
                     <select name="finance_name" class="input-css selectValidation select2" id="finance_name" style="width: 100%">
                        <option value="" selected>Select Financier Executive</option>
                     </select>
                     {!! $errors->first('finance_name', '
                     <p class="help-block">:message</p>
                     ') !!}
                  </div>
                  <div class="col-md-6">
                     <label>Loan Amount<sup>*</sup></label>
                     <input type="text" name="loan_amount" id="loan_amount"  value="{{(old('loan_amount'))? old('loan_amount') : $sale['loan_amount'] }}" class="input-css inputValidation " >
                     {!! $errors->first('loan_amount', '
                     <p class="help-block">:message</p>
                     ') !!}
                  </div>
                  <div class="col-md-6">
                     <label>DO Amount (Delivery Order)<sup>*</sup></label>
                     <input type="text" name="do" id="do" value="{{(old('do'))? old('do') : $sale['do'] }}" class="input-css inputValidation" onfocusout = "subcalculate(this)">
                     {!! $errors->first('do', '
                     <p class="help-block">:message</p>
                     ') !!}
                  </div>
                  <div class="col-md-6">
                     <label>DP Amount (Down Payment)<sup>*</sup></label>
                     <input type="text" name="dp" class="input-css inputValidation" value="{{(old('dp'))? old('dp') : $sale['dp'] }}">
                     {!! $errors->first('dp', '
                     <p class="help-block">:message</p>
                     ') !!}
                  </div>
                  <div class="col-md-6">
                     <label>LOS/DO Number<sup></sup></label>
                     <input type="text" name="los" class="input-css " value="{{(old('los'))? old('los') : $sale['los'] }}">
                     {!! $errors->first('los', '
                     <p class="help-block">:message</p>
                     ') !!}
                  </div>
                  <div class="col-md-6">
                     <label>ROI (Rate Of Interest)<sup>*</sup></label>
                     <input type="text" name="roi" class="input-css inputValidation" value="{{(old('roi'))? old('roi') : $sale['roi'] }}">
                     {!! $errors->first('roi', '
                     <p class="help-block">:message</p>
                     ') !!}
                  </div>
                  <div class="col-md-6">
                     <label>Payout<sup>*</sup></label>
                     <input type="text" name="payout" class="input-css inputValidation" value="{{(old('payout'))? old('payout') : $sale['payout'] }}">
                     {!! $errors->first('payout', '
                     <p class="help-block">:message</p>
                     ') !!}
                  </div>
                  <div class="col-md-6">
                     <label>PF (Processing Fees.)<sup>*</sup></label>
                     <input type="text" name="pf" class="input-css inputValidation" value="{{(old('pf'))? old('pf') : $sale['pf'] }}">
                     {!! $errors->first('pf', '
                     <p class="help-block">:message</p>
                     ') !!}
                  </div>
                  <div class="col-md-6">
                     <label>EMI<sup>*</sup></label>
                     <input type="text" name="emi" class="input-css inputValidation" value="{{(old('emi'))? old('emi') : $sale['emi'] }}">
                     {!! $errors->first('emi', '
                     <p class="help-block">:message</p>
                     ') !!}
                  </div>
                  <div class="col-md-6">
                     <label>Month Additional Charges<sup>*</sup></label>
                     <input type="text" name="monthAddCharge" class="input-css inputValidation" value="{{(old('monthAddCharge'))? old('monthAddCharge') : $sale['monthAddCharge'] }}">
                     {!! $errors->first('monthAddCharge', '
                     <p class="help-block">:message</p>
                     ') !!}
                  </div>
                  <div class="col-md-6">
                     <label>Stamp Details<sup>*</sup></label>
                     <input type="text" name="sd" class="input-css inputValidation" value="{{(old('sd'))? old('sd') : $sale['sd'] }}">
                     {!! $errors->first('sd', '
                     <p class="help-block">:message</p>
                     ') !!}
                  </div>
               </div>
               <div class="row" id="self_finance" style="{{old('customer_pay_type')?((old('customer_pay_type') == 'self_finance')? 'display:block;' : 'display:none;') : (($sale['customer_pay_type'] == 'self_finance')? 'display:block;' : 'display:none;' )}} margin-bottom:10px;">
                  <label><u>Self-Finance Details</u><sup>*</sup></label>
                  <div class="col-md-6">
                     <label>Company Name<sup>*</sup></label>
                     {{-- <input type="text" name="self_finance_company" value="{{(old('self_finance_company'))? old('self_finance_company') : (($sale['customer_pay_type'] == 'self_finance')?$sale['self_finance_company'] : '') }}" class="input-css inputValidation"  > --}}
                     <select name="self_finance_company" class="input-css selectValidation select2" id="self_finance_company" style="width: 100%">
                        <option value="">Select Company Name</option>
                        @foreach($company_name as $key => $val)
                           <option value="{{$val->id}}" {{(old('self_finance_company'))? ( (old('self_finance_company') == $val->id) ? 'selected' : '' )  : ( ($sale['self_finance_company'] == $val->id) ? 'selected' : '' ) }} >{{$val->company_name}}</option>
                        @endforeach
                     </select>
                     {!! $errors->first('self_finance_company', '<p class="help-block">:message</p>') !!}
                  </div>
                  <div class="col-md-6">
                     <label>Loan Amount<sup>*</sup></label>
                     <input type="text" name="self_finance_amount" id="self_loan_amount" value="{{(old('self_finance_amount'))? old('self_finance_amount') : (($sale['customer_pay_type'] == 'self_finance')?$sale['self_finance_amount'] : '') }}" class="input-css inputValidation" onfocusout="subcalculate(this)" >
                     {!! $errors->first('self_finance_amount', '
                     <p class="help-block">:message</p>
                     ') !!}
                  </div>
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
                     <input type="text" id="ex_showroom_price" value="{{(old('ex_showroom_price')) ? old('ex_showroom_price') : $sale['ex_showroom_price'] }}" name="ex_showroom_price" class="input-css calculationAll inputValidation  ">
                     <input type="hidden" hidden value = "{{(old('showroomPriceHidden'))? old('showroomPriceHidden') : $sale['ex_showroom_price'] }}" id="showroomPriceHidden" name="showroomPriceHidden">
                     {!! $errors->first('ex_showroom_price', '
                     <p class="help-block">:message</p>
                     ') !!}
                  </div>
               </div>
               <div class="row" id="corporate_csd" style="{{ (old('sale_type')) ? ((old('sale_type') == 'corporate') ? 'display:block' : 'display:none' ) : (($sale['sale_type'] == 'corporate') ? 'display:block' : 'display:none') }}">
                  <div class="col-md-9">
                     <label><input type="checkbox" {{(old('csd_check'))? 'checked' : ((isset($csd->amount))? 'checked' : '' ) }} name="csd_check" value="csd_check" onchange="calculate()" > CSD Discount<sup></sup></label>
                     {{-- <label>CSD Discount<sup>*</sup></label> --}}
                  </div>
                  <div class="col-md-3">
                     <label style="margin-right:10px;"> <span id="csd"> {{(old('csd')) ? old('csd') : ((isset($csd->amount))? $csd->amount : 0 ) }} </span></label>
                  </div>
               </div>
               {{-- rto --}}
               <div class="row">
                  <label>RTO</label>
                  <div class="col-md-6">
                     <label><input type="radio" name="rto" value="normal" {{(old('rto'))? ((old('rto') == 'normal' ) ? 'checked' : '') : (($sale['rto'] == 'normal')? 'checked' : '') }} class="rto"> Normal</label>
                  </div>
                  <div class="col-md-6">
                     <label><input type="radio" name="rto" value="self" {{(old('rto'))? ((old('rto') == 'self' ) ? 'checked' : '') : (($sale['rto'] == 'self')? 'checked' : '') }} class="rto"> Self</label>
                  </div>
                  {!! $errors->first('rto', '<p class="help-block">:message</p>') !!}
                  
               </div>
               <div class="row" id="handling_charge-div" style="{{(old('rto'))? ((old('rto') == 'normal' ) ? 'display:block' : 'display:none') : (($sale['rto'] == 'normal')? 'display:block' : 'display:none') }}">
                  <div class="col-md-9">
                     <label>Handling Charges <sup>*</sup></label>
                  </div>
                  <div class="col-md-3">
                     <input type="text" id="handling_charge" name="handling_charge" value="{{(old('handling_charge')) ? old('handling_charge') : (($sale['rto'] == 'normal')? $sale['handling_charge'] : 0) }}" class="input-css calculationAll  " onfocusout="calculate(this)">
                     {!! $errors->first('handling_charge', '<p class="help-block">:message</p>') !!}
                  </div>
               </div>
               <div class="row" id="affidavit-div" style="{{(old('rto'))? ((old('rto') == 'normal' ) ? 'display:block' : 'display:none') : (($sale['rto'] == 'normal')? 'display:block' : 'display:none') }}">
                  <div class="col-md-9">
                     <label><input type="checkbox" {{(old('affidavit'))? 'checked' : (($sale['affidavit_cost'] > 0)? 'checked' : '') }} name="affidavit" value="affidavit"> Affidavit</label>
                  </div>
                  <div class="col-md-3">
                     <input type="text" id="affidavit_cost" {{(old('affidavit') > 0)? '': (($sale['affidavit_cost'] > 0)? '' : 'readonly') }} name="affidavit_cost" value="{{(old('affidavit_cost')) ? old('affidavit_cost') : (($sale['affidavit_cost'] > 0)? $sale['affidavit_cost'] : 0) }}" class="input-css calculationAll  " onfocusout="calculate(this)">
                     {!! $errors->first('affidavit_cost', '<p class="help-block">:message</p>') !!}
                  </div>
               </div>
               <div class="row" id="permanent_temp-div" >
                  <label>RTO Type</label>
                  <div class="col-md-6">
                     <label><input type="radio" name="permanent_temp" {{(old('permanent_temp'))? ((old('permanent_temp') == 'permanent' ) ? 'checked' : '') : (($sale['rto_type'] == 'permanent')? 'checked' : '') }} value="permanent" class="permanent_temp"> Permanent</label>
                  </div>
                  <div class="col-md-6">
                     <label><input type="radio" name="permanent_temp" {{(old('permanent_temp'))? ((old('permanent_temp') == 'temporary' ) ? 'checked' : '') : (($sale['rto_type'] == 'temporary')? 'checked' : '') }} value="temporary" class="permanent_temp"> Temporary</label>
                  </div>
                  {!! $errors->first('permanent_temp', '<p class="help-block">:message</p>') !!}
               </div>
               <div class="row" >
                  <div class="col-md-9">
                     <label>Registration Fee & Road Tax<sup>*</sup></label>
                  </div>
                  <div class="col-md-3">
                     <input type="text" id="reg_fee_road_tax" name="reg_fee_road_tax" value="{{(old('reg_fee_road_tax')) ? old('reg_fee_road_tax') : $sale['register_price'] }}" class="input-css calculationAll inputValidation " onfocusout="calculate(this)">
                     {!! $errors->first('reg_fee_road_tax', '<p class="help-block">:message</p>') !!}
                     <label class="text">Suggestion :- <small id="sugg_rto"></small> </label>

                  </div>
               </div>
               <div class="row" >
                  <div class="col-md-12">
                     <label>Fancy # Receipt<sup></sup></label>
                     <div class="col-md-4">
                        <label><input type="radio" name="fancy" value="Yes" {{(old('fancy'))? ((old('fancy') == 'Yes' ) ? 'checked' : '') : (!empty($sale['fancy_no']) ? 'checked' : '' ) }} class="fancy"> Yes</label>
                     </div>
                     <div class="col-md-4">
                        <label><input type="radio" name="fancy" value="No" {{(old('fancy'))? ((old('fancy') == 'No' ) ? 'checked' : '') : (!empty($sale['fancy_no']) ? '' : 'checked' ) }} class="fancy"> No</label>
                     </div>
                     <div class="col-md-4">
                        <input type="text" id="fancy_no" name="fancy_no" value="{{(old('fancy_no')) ? old('fancy_no') : (!empty($sale['fancy_no']) ? $sale['fancy_no'] : '' ) }}" class="input-css" placeholder="Fancy #">
                        {!! $errors->first('fancy_no', '<p class="help-block">:message</p>') !!}
                     </div>
                  </div>
               </div>
               <div class="row">
                  <div class="col-md-6">
                     <label>Fancy Receipt Date<sup></sup></label>
                  </div>
                  <div class="col-md-6">
                     <input type="text" id="fancy_date" name="fancy_date"  value="{{(old('fancy_date')) ? old('fancy_date') : (!empty($sale['fancy_date']) ? $sale['fancy_date'] : '' ) }}" class="input-css datepicker5" data-date-end-date="0d" >
                        {!! $errors->first('fancy_date', '<p class="help-block">:message</p>') !!}
                  </div>
               </div>
               <div class="row" >
                  <label>Insurance<sup>*</sup></label>
                  <div class="col-md-6">
                     <input type="radio" name="insur_type" {{ old('insur_type')? ((old('insur_type') == 'normal') ? 'checked' : '' ) : (($insurance[0]->self_insurance == 0)? '' : 'checked' ) }} class="insur_type" value="normal"> Normal Insurance
                  </div>
                  <div class="col-md-6">
                     <input type="radio" name="insur_type" {{(old('insur_type') == 'self') ? 'checked' : (($insurance[0]->self_insurance == 1)? 'checked' : '' ) }} class="insur_type" value="self"> Self Insurance
                  </div>
               </div>
               <div class="row">
                  <label>Company Name <sup>*</sup></label>
                  <select name="insur_c" id="insur_c" class="input-css select2 selectValidation" style="padding:10px;">
                     <option value="">Select Insurance Company</option>
                     @foreach($insur_company as $k => $v)
                        <option value="{{trim($v->id)}}" {{(old('insur_c') == trim($v->id)) ? 'selected': (($insurance[0]->insurance_co == $v->id )? 'selected' : '' ) }} >{{trim($v->name)}}</option>
                     @endforeach
                  </select>
               </div>
               <div class="row" >
                  <div class="col-md-9" style="padding-left:50px;margin-top:10px;">
                     <input type="checkbox" {{(old('s_insurance') == 'zero_dep')? 'checked' : (($insurance[0]->insurance_name == 'zero_dep')? 'checked' : '') }} name="s_insurance" value="zero_dep" onclick="insuranceCheck(this)"> Zero Dep
                  </div>
                  <div class="col-md-3">
                     <input type="text" name="zero_dep_cost"  value="{{(old('s_insurance') == 'zero_dep')? old('zero_dep_cost') : (($insurance[0]->insurance_name == 'zero_dep') ? $insurance[0]->insurance_amount : 0) }}"  {{(old('s_insurance') == 'zero_dep')? '' : (($insurance[0]->insurance_name == 'zero_dep')? '' : 'readonly') }} id="zero_dep_cost" class="input-css calculationAll" onfocusout="calculate(this)">
                     {!! $errors->first('zero_dep_cost', '<p class="help-block">:message</p>') !!}
                  </div>
               </div>
               <div class="row">
                  <div class="col-md-9" style="padding-left:50px;">
                     <input type="checkbox" {{(old('s_insurance') == 'comprehensive')? 'checked' : (($insurance[0]->insurance_name == 'comprehensive')? 'checked' : '') }} name="s_insurance" value="comprehensive" onclick="insuranceCheck(this)"> Comprehensive
                  </div>
                  <div class="col-md-3">
                     <input type="text" name="comprehensive_cost" value="{{(old('s_insurance') == 'comprehensive')? old('comprehensive_cost') : (($insurance[0]->insurance_name == 'comprehensive') ? $insurance[0]->insurance_amount : 0) }}" {{(old('s_insurance') == 'comprehensive')? '' : (($insurance[0]->insurance_name == 'comprehensive')? '' : 'readonly') }} id="comprehensive_cost" class="input-css calculationAll" onfocusout="calculate(this)">
                     {!! $errors->first('comprehensive_cost', '<p class="help-block">:message</p>') !!}
                  </div>
               </div>
               <div class="row">
                  <div class="col-md-9" style="padding-left:50px;">
                     <input type="checkbox" {{(old('s_insurance') == 'ltp_5_yr')? 'checked' : (($insurance[0]->insurance_name == 'ltp_5_yr' )? 'checked' : '') }} name="s_insurance" value="ltp_5_yr" onclick="insuranceCheck(this)"> LTP 5 Yrs
                  </div>
                  <div class="col-md-3">
                     <input type="text" name="ltp_5_yr_cost" id="ltp_5_yr_cost" value="{{(old('s_insurance') == 'ltp_5_yr')? old('ltp_5_yr_cost') : (($insurance[0]->insurance_name == 'ltp_5_yr') ? $insurance[0]->insurance_amount : 0) }}" {{(old('s_insurance') == 'ltp_5_yr')? '' : (($insurance[0]->insurance_name == 'ltp_5_yr')? '' : 'readonly') }} class="input-css calculationAll" onfocusout="calculate(this)">
                     {!! $errors->first('ltp_5_yr_cost', '
                     <p class="help-block">:message</p>
                     ') !!}
                  </div>
               </div>
               <div class="row">
                  <div class="col-md-9" style="padding-left:50px;">
                     <input type="checkbox" {{(old('s_insurance') == 'ltp_zero_dep')? 'checked' : (($insurance[0]->insurance_name == 'ltp_zero_dep')? 'checked' : '') }} name="s_insurance" value="ltp_zero_dep" onclick="insuranceCheck(this)"> LTP Zero Dep
                  </div>
                  <div class="col-md-3">
                     <input type="text" name="ltp_zero_dep_cost" value="{{(old('s_insurance') == 'ltp_zero_dep')? old('ltp_zero_dep_cost') : (($insurance[0]->insurance_name == 'ltp_zero_dep') ? $insurance[0]->insurance_amount : 0) }}" id="ltp_zero_dep_cost" {{(old('s_insurance') == 'ltp_zero_dep')? '' : (($insurance[0]->insurance_name == 'ltp_zero_dep')? '' : 'readonly') }} class="input-css calculationAll" onfocusout="calculate(this)">
                     {!! $errors->first('ltp_zero_dep_cost', '
                     <p class="help-block">:message</p>
                     ') !!}
                  </div>
               </div>
               
               <div class="row" >
                  <label><input type="checkbox" {{(old('cpa_cover') == 'cpa_cover')? 'checked' : ((empty($insurance[0]->cpa_amount))? '' : 'checked') }} name="cpa_cover" value="cpa_cover" onclick="cpaCheck(this)"> CPA Cover<sup>*</sup></label>
                  <div style=" {{(old('cpa_cover') == 'cpa_cover') ? 'display:block;' : ((empty($insurance[0]->cpa_amount))? 'display:none'  : 'display:block')  }} ">
                     <div class="col-md-5" style="padding-left:50px; ">
                        {{-- <input type="text" name="cpa_company" value="{{(old('cpa_cover'))? old('cpa_company') : '' }}" id="cpa_company" class="input-css hidecpa {{(old('cpa_cover'))? 'inputValidation' : '' }}" placeholder="Company Name"> --}}
                        <select name="cpa_company" id="cpa_company" class="input-css select2 hidecpa {{(old('cpa_cover') == 'cpa_cover')? 'selectValidation' : ((empty($insurance[0]->cpa_amount))? '' : 'selectValidation') }}" style="padding:10px;">
                           <option value="">Select Insurance Company</option>
                           @foreach($cpa_company as $k => $v)
                              <option value="{{trim($v->id)}}" {{(old('insur_c') == trim($v->id)) ? 'selected': (($insurance[0]->cpa_company == trim($v->id))? 'selected' : '') }} >{{trim($v->name)}}</option>
                           @endforeach
                        </select>
                        {!! $errors->first('cpa_company', '<p class="help-block">:message</p>') !!}
                     </div>
                     <div class="col-md-4" style="padding-left:50px; ">
                        <input type="number" name="cpa_duration" value="{{(old('cpa_cover') == 'cpa_cover')? old('cpa_duration') : ((empty($insurance[0]->cpa_tenure))? '' : $insurance[0]->cpa_tenure) }}" id="cpa_duration" class="input-css hidecpa {{(old('cpa_cover') == 'cpa_cover')? 'inputValidation' : ((empty($insurance[0]->cpa_amount))? '' : 'inputValidation') }}" placeholder="Duration">
                        {!! $errors->first('cpa_duration', '<p class="help-block">:message</p>') !!}
                     </div>
                     <div class="col-md-3" style="">
                        <input type="text" name="cpa_cover_cost" value="{{(old('cpa_cover') == 'cpa_cover')? old('cpa_cover_cost') : ((empty($insurance[0]->cpa_amount))? 0 : $insurance[0]->cpa_amount) }}" id="cpa_cover_cost" class="input-css  hidecpa {{(old('cpa_cover') == 'cpa_cover')? 'inputValidation calculationAll' : ((empty($insurance[0]->cpa_amount))? '' : 'inputValidation calculationAll') }}" onfocusout="calculate(this)" >
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
                     <input type="text" readonly name="accessories_cost" value="{{(old('accessories_cost'))? old('accessories_cost') : $sale['accessories_value'] }}" id="accessories_cost" class="input-css inputValidation  calculationAll" onfocusout="calculate(this)">
                     {!! $errors->first('accessories_cost', '
                     <p class="help-block">:message</p>
                     ') !!}
                  </div>
               </div>
               <div class="row" style="{{ (old('customer_pay_type')) ? ((old('customer_pay_type') != 'cash') ? 'display:block' : 'display:none' ) : (($sale['customer_pay_type'] != 'cash')? 'display:block;' : 'display:none;' ) }}" id="hypo">
                  <div class="col-md-9" >
                     <label>Hypothecation<sup>*</sup></label>
                  </div>
                  <div class="col-md-3">
                     <input type="text" name="hypo_cost" id="hypo_cost" value="{{(old('hypo_cost'))? old('hypo_cost') : (($sale['hypo'] == 0)? 0 : $sale['hypo']) }}" class="input-css calculationAll inputValidation" onfocusout="calculate(this)">
                     {!! $errors->first('hypo_cost', '<p class="help-block">:message</p>') !!}
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
                     <input type="checkbox" {{(old('amc') == 'amc')? 'checked' : (($sale['amc_cost'] == 0)? '' : 'checked') }} name="amc" value="amc" onclick="insuranceCheck(this)"> AMC (Annual Maintenance Contract)
                  </div>
                  <div class="col-md-3">
                     <input type="text" name="amc_cost" value="{{(old('amc') == 'amc') ? old('amc_cost') : (($sale['amc_cost'] == 0)? 0 : $sale['amc_cost'])}}" id="amc_cost" {{(old('amc') == 'amc')? '' : (($sale['amc_cost'] == 0)? 'readonly' : '') }} class="input-css calculationAll" onfocusout="calculate(this)">
                     {!! $errors->first('amc_cost', '<p class="help-block">:message</p>') !!}
                  </div>
               </div>
               <div class="row">
                  <div class="col-md-4" style="padding-top:15px;">
                     <input type="checkbox" {{(old('ew'))? 'checked' : (($sale['ew_cost'] == 0)? '' : 'checked') }} name="ew" value="ew" onclick="insuranceCheck(this)"> EW 
                  </div>
                  <div class="col-md-8">
                     <div class="col-md-7">
                        <select name="ew_duration" id="ew_duration" class="input-css select2" readonly>
                           <option value="">EW Duration</option>
                           <option value="1" {{(old('ew_duration') == 1)? 'selected' : (($sale['ew_duration'] == 1)? 'selected' : '') }} >1</option>
                           <option value="2" {{(old('ew_duration') == 2)? 'selected' : (($sale['ew_duration'] == 2)? 'selected' : '') }} >2</option>
                           <option value="3" {{(old('ew_duration') == 3)? 'selected' : (($sale['ew_duration'] == 3)? 'selected' : '') }} >3</option>
                        </select>
                        {{-- <input type="text" name="ew_duration" id="ew_duration" value="{{(old('ew_duration')) ? old('ew_duration') : '' }}" placeholder="Enter EW duration" {{(old('ew'))? '' : 'readonly' }} class="input-css "> --}}
                        {!! $errors->first('ew_duration', '<p class="help-block">:message</p>') !!}
                     </div>
                     <div class="col-md-5">
                        <input type="text" name="ew_cost" id="ew_cost" value="{{(old('ew_cost')) ? old('ew_cost') : (($sale['ew_cost'] == 0)? '' : $sale['ew_cost']) }}" {{(old('ew'))? '' : (($sale['ew_cost'] == 0)? 'readonly' : '') }} class="input-css calculationAll" onfocusout="calculate(this)">
                        {!! $errors->first('ew_cost', '<p class="help-block">:message</p>') !!}
                     </div>
                  </div>
               </div>
               <div class="row">
                  <div class="col-md-9" style="padding-top:15px;">
                     <input type="checkbox" {{(old('hjc') == 'hjc')? 'checked' : (($sale['hjc_cost'] == 0)? '' : 'checked') }} name="hjc" value="hjc" onclick="insuranceCheck(this)"> HJC
                  </div>
                  <div class="col-md-3">
                     <input type="text" name="hjc_cost" value="{{(old('hjc') == 'hjc') ? old('hjc_cost') : (($sale['hjc_cost'] == 0)? '' : $sale['hjc_cost']) }}" id="hjc" {{(old('hjc') == 'hjc')? '' : (($sale['hjc_cost'] == 0)? 'readonly' : '') }} class="input-css calculationAll" onfocusout="calculate(this)">
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
                           <label><input autocomplete="off" {{old('discount')?((old('discount') == 'normal') ? 'checked': '' ): (($discount->discount_type == 'Normal')? 'checked' : '') }} type="radio"  class="" value="normal" name="discount"> Normal</label>
                        </div>
                        <div class="col-md-3">
                           <label><input autocomplete="off" {{old('discount')?((old('discount') == 'scheme') ? 'checked' : '') : (($discount->discount_type == 'Scheme') ? 'checked' : '' ) }} type="radio" class="" value="scheme" name="discount"> Scheme</label>
                        </div>
                        {!! $errors->first('discount', '
                        <p class="help-block">:message</p>
                        ') !!}
                     </div>
                     <div class="col-md-3" style="padding-top:25px">
                        <input type="text" name="discount_amount" id="discount_amount" value="{{(old('discount_amount')) ? old('discount_amount') : $discount->amount }}" class="input-css inputValidation " onfocusout="subcalculate(this)">
                        {!! $errors->first('discount_amount', '
                        <p class="help-block">:message</p>
                        ') !!}
                     </div>
                  </div>
                  <div class="row" id="onscheme" style="{{old('discount')?((old('discount') == 'scheme') ? 'display:block' :'display:none'):(($discount->discount_type == 'Scheme')? 'display:block;' : 'display:none;')}}">
                     <div class="col-md-5">
                        <select name="scheme" id="scheme" class="input-css select2 selectValidation" >
                           <option value="">Select Scheme</option>
                           @foreach($scheme as $key => $val)
                           <option value="{{$val['id']}}" {{(old('scheme') == $val['id']) ? 'selected' : (($discount->scheme_id == $val['id'])? 'selected' : '')  }} > {{$val['name']}} </option>
                           @endforeach
                        </select>
                        {!! $errors->first('scheme', '
                        <p class="help-block">:message</p>
                        ') !!}
                     </div>
                     <div class="col-md-4">
                        <input type="text" name="scheme_remark" value="{{old('scheme_remark')?((old('scheme_remark')) ? old('scheme_remark') : '') : (($discount->remark)? $discount->remark : '')}}" class="input-css inputValidation" placeholder="Remark">
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
                     <input type="text" name ="total_calculation" readonly value="{{(old('total_calculation')) ? old('total_calculation') : $sale['total_amount']  }}" id="total_calculation"  class="input-css inputValidation ">
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
                        <label style="margin-right:10px;">- <span id="cost" > {{(old('discount_amount')) ? old('discount_amount') :  $discount->amount}}</span> </label>
                     </div>
                  </div>
                  <div class="row" id="exchange_value_final" style="{{(old('tos')) ? ((old('tos') != 'new') ? 'display:block' : 'display:none') : (($sale['type_of_sale'] != '')? 'display:block;' : 'display:none;' ) }}">
                     <div class="col-md-8">
                        <label id="label">Ex-Change Value</label>
                     </div>
                     <div class="col-md-4 "  >
                        <label style="margin-right:10px;">- <span id="cost"> {{(old('exchange_value')) ? old('exchange_value') :  $sale['exchange_value']}} </span>
                        </label>
                     </div>
                  </div>
                  <div class="row" id="do_final" style="{{old('customer_pay_type')?((old('customer_pay_type') == 'finance')?'display:block':'display:none') : (($sale['customer_pay_type'] == 'finance')? 'display:block;'  : 'display:none;' )}}">
                     <div class="col-md-8">
                        <label id="label">DO (Delivery Order) Amount</label>
                     </div>
                     <div class="col-md-4">
                        <label style="margin-right:10px;">- <span id="cost"> {{(old('do')) ? old('do') :  $sale['do']}} </span></label>
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
                     <input type="text" name="balance" readonly id="balance"  class="input-css inputValidation " value="{{(old('balance'))? old('balance') : $sale['balance']}}">
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