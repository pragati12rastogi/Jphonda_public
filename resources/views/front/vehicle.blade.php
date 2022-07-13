@extends($layout)

@section('title', __('JP Honda'))

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.2/jquery.validate.min.js" integrity="sha512-UdIMMlVx0HEynClOIFSyOrPggomfhBKJE28LKl8yR3ghkgugPnG6iLfRfHwushZl1MOPSY6TsuBDGPK2X4zYKg==" crossorigin="anonymous"></script>
<script src="/front/js/sale.js"></script>
<script>
    var csd_discount = {{$csd}};
    var oldModelName = @php echo json_encode((old('model_name')) ? old('model_name') : '' ); @endphp;
    var amc_price = @php echo json_encode($amc_price); @endphp;
   var oldModelVariant = @php echo json_encode((old('model_variant')) ? old('model_variant') : '' ); @endphp;
   var oldProduct = @php echo json_encode((old('prod_name')) ? old('prod_name') : '' ); @endphp;
   var ex_showroom_price =  @php echo json_encode((old('ex_showroom_price')) ? old('ex_showroom_price') : 0 ); @endphp;
    var oldaccessories = @php echo json_encode((old('accessories')) ? old('accessories') : [] ); @endphp;
    var scheme = @php echo json_encode($scheme); @endphp;
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
   var model_name= '';
 
   if(model_name!='')
   {
     $('#model_name').val(model_name);
   }
   if(oldModelName != '')
   {
        $('#model_name').val(oldModelName);
        $('#model_name').trigger('change');
   }

   var financeCompanyName = @php echo json_encode((old('finance_company_name')) ? old('finance_company_name') : '' ); @endphp;
   var financeName = @php echo json_encode((old('finance_name')) ? old('finance_name') : '' ); @endphp;
   var customer_pay_type = @php echo json_encode((old('customer_pay_type')) ? old('customer_pay_type') : '' ); @endphp;
  

   if(customer_pay_type == 'finance')
   {
      if(financeCompanyName)
      {
         $("#finance_company_name").val(financeCompanyName).trigger('change');
      }
   }
   

</script>
@endsection
@section('css')
        <!--inputs css-->
<link rel="stylesheet" href="/css/style.css">
<link rel="stylesheet" href="/front/css/vehicle.css">

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />

@endsection
@section('main_section')
    <section class="content">
       <div class="breadcrumb-area breadcrumb-area pt-120" >
                <div class="container-fluid">
                    <div class="breadcrumb-content text-center contactus_header">
                    <h2>{{__('front/front.vehicle_digital_quotation')}}</h2>
                 </div>
                </div>
            </div>

        <div class="overview-area pt-30">
         <div class="container">
           <div class="row">
            <div class="col-lg-12 col-md-12">
        <!-- Default box -->
        <div id="app">
                @include('admin.flash-message')
                @yield('content')
            </div>
       <form action="#" method="POST" id="form">
        @csrf

       <div class="box box-header">
            <div class="row" >
                <div class="col-md-4 {{ $errors->has('model_name') ? ' has-error' : ''}}">
                  <div class="country-select">
                    <label for=""> Model Name <sup>*</sup></label>
                    <select name="model_name" id="model_name" >
                        <option>Select model name</option>
                        @foreach($model_name as $key => $val)
                            <option value="{{$val['model_name']}}" {{old('model_name')== $val['model_name']? 'selected=selected' : ''}}> {{$val['model_name']}} </option>
                        @endforeach
                    </select>
                    {!! $errors->first('model_name', '<p class="help-block">:message</p>') !!} 
                </div>
              </div>

                 <div class="col-md-4 {{ $errors->has('model_variant') ? ' has-error' : ''}}">
                  <div class="country-select">
                    <label for="">Model Variant  <sup>*</sup></label>
                    <select name="model_variant" id="model_variant" class="">
                        <option>Select variant</option>
                    </select>
                    {!! $errors->first('model_variant', '<p class="help-block">:message</p>') !!} 
                </div>
              </div>
                <div class="col-md-4 {{ $errors->has('prod_name') ? ' has-error' : ''}}">
                  <div class="country-select">
                    <label for="">Color Code  <sup>*</sup></label>
                    <select name="prod_name" id="prod_name" class="">
                        <option>Select color code</option>
                    </select>
                    {!! $errors->first('prod_name', '<p class="help-block">:message</p>') !!} 
                    <input type="hidden" hidden name="prod_basic" value="{{(old('prod_basic')) ? old('prod_basic') : 0}}">
                </div>
                </div>
            </div>
            {{--<div class="row">
                <div class="col-md-4 {{ $errors->has('store') ? ' has-error' : ''}}">
                  <div class="country-select">
                    <label for=""> Store Name <sup>*</sup></label>
                    <select name="store_name" id="store_name" class="">
                        <option>Select store</option>
                        @foreach($store as $key => $val)
                            <option value="{{$val['id']}}" {{old('store')== $val['id']? 'selected=selected' : ''}}> {{$val['name']}} - {{$val['store_type']}} </option>
                        @endforeach
                    </select>
                    {!! $errors->first('model_name', '<p class="help-block">:message</p>') !!} 
                </div>
                </div>
            </div><br> --}}
            <div class="row">
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="row">
                                 <h5 class="margin-bottom">Accessories</h5>
                                 <div class="col-md-12">
                                    <div class="row">
                                    <div class="col-md-4">
                                        <label>Accessories</label>
                                    </div>
                                    <div class="col-md-4">
                                        <label>Part Number</label>
                                    </div>
                                    <div class="col-md-2">
                                        <label>Quantity</label>
                                    </div>
                                    <div class="col-md-2">
                                        <label>Amount</label>
                                    </div>
                                    </div>
                                    <div id="accessories-append" style="margin-top:5px;margin-bottom:5px;">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                          <div class="col-md-12" >
                             <div class="row finance" style="margin-bottom:10px;">
                              <div class="contact-form-style">
                                <div class="col-md-12">
                                  <div class="row">

                                   <label>Customer Payment Type<sup>*</sup></label>
                                   <div class="col-md-4">
                                      <label><input type="radio" {{(old('customer_pay_type') == 'cash') ? 'checked' : '' }} {{(old('customer_pay_type')) ? '' : '' }} class="customer_pay_type sceme_button" value="cash" name="customer_pay_type">Cash</label>
                                   </div>
                                   <div class="col-md-6">
                                      <button class="submit cr-btn btn-style"  value="finance" data-toggle="modal" data-target="#exampleModalCenter"><span>Finance Quotation</span></button>
                                   </div>
                                   
                                    </div>
                                   {!! $errors->first('customer_pay_type', '
                                   <p class="help-block">:message</p>
                                   ') !!}
                                </div>
                                </div>
                             </div>
                             <div class="row" id="finance" style="{{(old('customer_pay_type') == 'finance')? 'display:block;' :'display:none;'}} margin-bottom:10px;">
                              
                             </div>
                          </div>
                          <div class="row" >
                            <div class="col-md-9">
                               <label>Ex-Showroom Price<sup>*</sup></label>
                            </div>
                            
                            <div class="col-md-3">
                              <div class="checkout-form-list">
                               <input type="text" id="ex_showroom_price" value="{{(old('ex_showroom_price')) ? old('ex_showroom_price') : 0.0 }}" name="ex_showroom_price" class="input-css calculationAll inputValidation" readonly>
                               <input type="hidden" hidden value = "{{(old('showroomPriceHidden'))? old('showroomPriceHidden') : 0 }}" id="showroomPriceHidden" name="showroomPriceHidden">
                               {!! $errors->first('ex_showroom_price', '<p class="help-block">:message</p>') !!}
                            </div>
                          </div>
                         </div>
                          <div class="row">
                            <label>RTO</label>
                            <div class="col-md-3">
                               <label><input type="radio" name="rto" value="normal" {{(old('rto'))? ((old('rto') == 'normal' ) ? 'checked' : '') : '' }} class="rto sceme_button"> Normal</label>
                            </div>
                            <div class="col-md-3">
                               <label><input type="radio" name="rto" value="self" {{(old('rto'))? ((old('rto') == 'self' ) ? 'checked' : '') : '' }} class="rto sceme_button"> Self</label>
                            </div>
                            {!! $errors->first('rto', '<p class="help-block">:message</p>') !!}
                         </div>
                         <div class="row" id="handling_charge-div" style="{{(old('rto'))? ((old('rto') == 'normal' ) ? 'display:block' : 'display:none') : 'display:none' }}">
                          <div class="col-md-9">
                             <label>Handling Charges <sup>*</sup></label>
                          </div>
                          <div class="col-md-3">
                            <div class="checkout-form-list">
                             <input type="text" id="handling_charge" name="handling_charge" value="" class="input-css calculationAll  " onfocusout="calculate(this)" readonly>
                             {!! $errors->first('handling_charge', '<p class="help-block">:message</p>') !!}
                           </div>
                          </div>
                       </div>
                       <div class="row" id="affidavit-div" style="{{(old('rto'))? ((old('rto') == 'normal' ) ? 'display:block' : 'display:none') : 'display:none' }}">
                          <div class="col-md-9">
                             <input type="checkbox" {{(old('affidavit'))? 'checked' : '' }} name="affidavit" class="sceme_button" value="affidavit"><b> Affidavit</b>
                          </div>
                          <div class="col-md-3">

                            <div class="checkout-form-list">
                             <input type="text" id="affidavit_cost" {{(old('affidavit'))? '': 'readonly' }} name="affidavit_cost" value="{{(old('affidavit_cost')) ? old('affidavit_cost') : '' }}" class="input-css calculationAll  " onfocusout="calculate(this)">
                             {!! $errors->first('affidavit_cost', '<p class="help-block">:message</p>') !!}
                          </div>
                        </div>
                       </div>
                       <div class="row" id="permanent_temp-div" >
                          <label>RTO Type</label>
                          <div class="col-md-3">
                             <label><input type="radio" name="permanent_temp" {{(old('permanent_temp'))? ((old('permanent_temp') == 'permanent' ) ? 'checked' : '') : '' }} value="permanent" class="permanent_temp sceme_button"> Permanent</label>
                          </div>
                          <div class="col-md-3">
                             <label><input type="radio" name="permanent_temp" {{(old('permanent_temp'))? ((old('permanent_temp') == 'temporary' ) ? 'checked' : '') : '' }} value="temporary" class="permanent_temp sceme_button"> Temporary</label>
                          </div>
                          {!! $errors->first('permanent_temp', '<p class="help-block">:message</p>') !!}
                       </div>
                       <div class="row" >
                          <div class="col-md-9">
                             <label>Registration Fee & Road Tax<sup>*</sup></label>
                          </div>
                          <div class="col-md-3">
                            <div class="checkout-form-list">
                             <input type="text" id="reg_fee_road_tax" name="reg_fee_road_tax" value="{{(old('reg_fee_road_tax')) ? old('reg_fee_road_tax') : '' }}" class="input-css calculationAll inputValidation " onfocusout="calculate(this)" readonly>
                             {!! $errors->first('reg_fee_road_tax', '<p class="help-block">:message</p>') !!}
                             <label class="text">Suggestion :- <small id="sugg_rto"></small> </label>
                           </div>
                          </div>
                       </div>
                            <div class="row">
                              <div class="col-md-9" style="padding-top:15px;">
                                 <input type="checkbox" {{(old('amc'))? 'checked' : '' }} name="amc" value="amc" onclick="AmcnormalCheck(this)" id="acc_checkbox"> AMC (Annual Maintenance Contract)
                              </div>
                              <div class="col-md-3 ">
                                <div class="checkout-form-list">
                                 <input type="text" name="amc_cost" value="{{(old('amc_cost')) ? old('amc_cost') : 0.0}}" id="amc_cost" {{(old('amc'))? 'readonly' : 'readonly' }} class="calculationAll" onfocusout="calculate(this)" >
                                 {!! $errors->first('amc_cost', '
                                 <p class="help-block">:message</p>
                                 ') !!}
                               </div>
                              </div>
                           </div>
                           <div class="row">
                              <div class="col-md-2" style="padding-top:15px;">
                                <br>
                                 <input type="checkbox" {{(old('ew'))? 'checked' : '' }} name="ew" class="ew" value="ew" onclick="EwnormalCheck(this)" id="acc_checkbox"> EW 
                              </div>
                              <div class="col-md-10">
                                <div class="row">
                                 <div class="col-md-6">
                                  <div class="country-select">
                                   <br>
                                    <select name="ew_duration" id="ew_duration" class="select2" readonly>
                                       <option value="">EW Duration</option>
                                       <option value="1" {{(old('ew_duration') == 1)? 'selected' : '' }} selected="">1</option>
                                       <option value="2" {{(old('ew_duration') == 2)? 'selected' : '' }} >2</option>
                                       <option value="3" {{(old('ew_duration') == 3)? 'selected' : '' }} >3</option>
                                    </select>
                                    {!! $errors->first('ew_duration', '
                                    <p class="help-block">:message</p>
                                    ') !!}
                                  </div>
                                 </div>
                                 <div class="col-md-6"><br>
                                   <div class="checkout-form-list">
                                    <input type="text" name="ew_cost" id="ew_cost" value="{{(old('ew_cost')) ? old('ew_cost') : 0.0 }}" {{(old('ew'))? '' : 'readonly' }} class=" calculationAll" onfocusout="calculate(this)" readonly>
                                    {!! $errors->first('ew_cost', '
                                    <p class="help-block">:message</p>
                                    ') !!}
                                  </div>
                                 </div>
                                 </div>
                              </div>
                           </div>
                            <div class="row">
                               <div class="col-md-9">
                                <div class="row">
                                 &nbsp; <label for="advance">Discount<sub>*</sub></label>
                                {{--  <div class="col-md-3">
                                     <label><input autocomplete="off" {{(old('discount') == 'normal') ? 'checked' : '' }} {{(old('discount')) ? '' : 'checked' }} type="radio"  class="sceme_button" value="normal" name="discount"> Normal</label>
                                  </div> --}}
                                  <div class="col-md-3">
                                     <label><input autocomplete="off" {{(old('discount') == 'scheme') ? 'checked' : '' }} type="radio" class="sceme_button" value="scheme" name="discount"> Scheme</label>
                                  </div>
                                  {!! $errors->first('discount', '
                                  <p class="help-block">:message</p>
                                  ') !!}
                                </div>
                               </div>
                               <div class="col-md-3" style="padding-top:25px">
                                 <div class="checkout-form-list">
                                  <input type="text" name="discount_amount" id="discount_amount" value="{{(old('discount_amount')) ? old('discount_amount') : 0.0 }}" class=" inputValidation " onfocusout="subcalculate(this)" readonly>
                                  {!! $errors->first('discount_amount', '
                                  <p class="help-block">:message</p>
                                  ') !!}
                                </div>
                               </div>
                            
                            <div class="row" id="onscheme" style="width: 60%;{{(old('discount') == 'scheme') ? 'display:block' :'display:none'}}">
                              <div class="col-md-12">
                                <div class="row">
                                <div class="col-md-10">
                                <div class="country-select"><br>
                                  <select name="scheme" id="scheme" class="selectValidation" >
                                     <option value="" p>Select Scheme</option>
                                     @foreach($scheme as $key => $val)
                                     <option value="{{$val['id']}}" {{(old('scheme') == $val['id']) ? 'selected' : ''  }} > {{$val['name']}} </option>
                                     @endforeach
                                  </select>
                                  {!! $errors->first('scheme', '
                                  <p class="help-block">:message</p>
                                  ') !!}
                                </div>
                               </div>
                             </div>
                            </div>
                         </div>
                         </div>
                            <div class="row">
                              <div class="col-md-9">
                                 <label>Accessories Value<sup>*</sup></label>
                              </div>
                              <div class="col-md-3">
                                 <div class="checkout-form-list">
                                 <input type="text" readonly name="accessories_cost" value="{{(old('accessories_cost'))? old('accessories_cost') : 0.0 }}" id="accessories_cost" class=" inputValidation  calculationAll" onfocusout="calculate(this)">
                                 {!! $errors->first('accessories_cost', '
                                 <p class="help-block">:message</p>
                                 ') !!}
                               </div>
                              </div>
                           </div>
                            <div class="row" style="padding-top:20px;padding-bottom:10px;">
                              <div class="col-md-4">
                                 <label for="total_calculation">Total Amount Rs.</label>
                              </div>
                              <div class="col-md-8">
                                 <div class="checkout-form-list">
                                 <input type="text" name ="total_calculation" readonly value="{{(old('total_calculation')) ? old('total_calculation') : 0 }}" id="total_calculation"  class=" inputValidation ">
                                 {!! $errors->first('total_calculation', '
                                 <p class="help-block">:message</p>
                                 ') !!}
                               </div>
                              </div>
                           </div>
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
                             {{-- <div class="row" id="do_final" style="{{(old('customer_pay_type') == 'finance')?'display:block':'display:none'}}">
                                 <div class="col-md-8">
                                    <label id="label">DO (Delivery Order) Amount</label>
                                 </div>
                                 <div class="col-md-4">
                                    <label style="margin-right:10px;">- <span id="cost"> {{(old('do')) ? old('do') :  0.0}} </span></label>
                                 </div>
                              </div> --}}
                           </div>
                           <div class="row">
                              <div class="col-md-9">
                                 <label for="balance">Balance</label>
                              </div>
                              <div class="col-md-3">
                                 <div class="checkout-form-list">
                                 <input type="text" name="balance" readonly id="balance"  class=" inputValidation " value="{{(old('balance'))? old('balance') : 0.0}}">
                                 {!! $errors->first('balance', '
                                 <p class="help-block">:message</p>
                                 ') !!}
                               </div>
                              </div><br>
                           </div>
                        </div>
                    </div>
                </div>
            </div>
       </div>
       
        </form>
      </div></div></div></div>


      <div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="margin-top: 100px!important;">
          <div class="modal-header">
            <h4 class="modal-title" id="exampleModalLongTitle">Financier Details</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          
          <div class="modal-body"> 
            
              <div class="col-md-12">
              <div class="row">
                <div class="col-md-12">
                <div class="alert alert-danger" id="finance_error">
                </div>
              </div>
              </div>
                <div class="row">
                  <div class="col-md-6">
                    <div class="country-select">
                     <label>Company Name<sup>*</sup></label>
                     <select name="finance_company_name" class="selectValidation select2" id="finance_company_name">
                        <option value="">Select Company Name</option>
                        @foreach($company_name as $val)
                           <option value="{{$val['id']}}">{{$val['company_name']}}</option>
                        @endforeach
                     </select>
                     {!! $errors->first('finance_company_name', '
                     <p class="help-block">:message</p>
                     ') !!}
                   </div>
                  </div>
                  <div class="col-md-6">
                    <div class="country-select">
                     <label>Financier Executive<sup>*</sup></label>
                     <select name="finance_name" class="selectValidation select2" id="finance_name">
                        <option value="">Select Financier Executive</option>
                       {{-- <option value="{{$fe['id']}}" >{{$fe['executive_name']}}</option>--}}
                     </select>
                     {!! $errors->first('finance_name', '
                     <p class="help-block">:message</p>
                     ') !!}
                  </div>
                  </div>
                </div>
                  <div class="row">
                  <div class="col-md-6">
                    <div class="checkout-form-list">
                     <label>Loan Amount<sup>*</sup></label>
                     <input type="text" name="loan_amount" id="loan_amount"  value="{{(old('loan_amount'))}}" class="input-css inputValidation " readonly>
                     {!! $errors->first('loan_amount', '
                     <p class="help-block">:message</p>
                     ') !!}
                  </div>
                  </div>
                  <div class="col-md-6">
                    <div class="checkout-form-list">
                     <label>DO Amount (Delivery Order)<sup>*</sup></label>
                     <input type="text" name="do" id="do" value="{{(old('do'))? old('do') : 0.0}}" class="input-css inputValidation" onfocusout = "subcalculate(this)"  readonly>
                     {!! $errors->first('do', '
                     <p class="help-block">:message</p>
                     ') !!}
                  </div>
                </div>
                </div>
                <div class="row">
                  <div class="col-md-6">
                    <div class="checkout-form-list">
                     <label>DP Amount (Down Payment)<sup>*</sup></label>
                     <input type="text" name="dp" class="input-css inputValidation" value="{{(old('dp'))? old('dp') : 0}}"  readonly>
                     {!! $errors->first('dp', '
                     <p class="help-block">:message</p>
                     ') !!}
                  </div>
                </div>
                  <div class="col-md-6">
                    <div class="checkout-form-list">
                     <label>LOS/DO Number<sup></sup></label>
                     <input type="text" name="los" id="los" class="input-css " value="{{(old('los'))? old('los') : 0 }}"  readonly>
                     {!! $errors->first('los', '
                     <p class="help-block">:message</p>
                     ') !!}
                  </div>
                </div>
              </div>
              <div class="row">
                  <div class="col-md-6">
                    <div class="checkout-form-list">
                     <label>ROI (Rate Of Interest)<sup>*</sup></label>
                     <input type="text" name="roi" class="input-css inputValidation" value="{{(old('roi'))? old('roi') :  $rate }}"  readonly>
                     {!! $errors->first('roi', '
                     <p class="help-block">:message</p>
                     ') !!}
                  </div>
                </div>
                  {{--<div class="col-md-6">
                    <div class="checkout-form-list">
                     <label>Payout<sup>*</sup></label>
                     <input type="text" name="payout" id="payout" class="input-css inputValidation" value="{{(old('payout'))? old('payout') : 0 }}"  readonly>
                     {!! $errors->first('payout', '
                     <p class="help-block">:message</p>
                     ') !!}
                  </div>
                </div>--}}
                <div class="col-md-6">
                    <div class="checkout-form-list">
                     <label>Stamp Details<sup>*</sup></label>
                     <input type="text" name="sd" id="sd" class="input-css inputValidation" value="{{(old('sd'))? old('sd') : 0 }}"  readonly>
                     {!! $errors->first('sd', '
                     <p class="help-block">:message</p>
                     ') !!}
                  </div>
                </div>
              </div>
              <div class="row">
                  <div class="col-md-6">
                    <div class="checkout-form-list">
                     <label>PF (Processing Fees.)<sup>*</sup></label>
                     <input type="text" name="pf" id="pf" class="input-css inputValidation" value="{{(old('pf'))? old('pf') : 0 }}"  readonly>
                     {!! $errors->first('pf', '
                     <p class="help-block">:message</p>
                     ') !!}
                  </div>
                </div>
                  <div class="col-md-6">
                    <div class="checkout-form-list">
                     <label>EMI<sup>*</sup></label>
                     <input type="text" name="emi" id="emi" class="input-css inputValidation" value="{{(old('emi'))? old('emi') : 0 }}"  readonly>
                     {!! $errors->first('emi', '
                     <p class="help-block">:message</p>
                     ') !!}
                  </div>
                </div>
              </div>
              <div class="row">
                  <div class="col-md-6">
                    <div class="checkout-form-list">
                     <label>Tenure <sup>*</sup></label>
                     <input type="text" name="monthAddCharge" class="input-css inputValidation" value="{{(old('monthAddCharge'))? old('monthAddCharge') : 0.0 }}"  readonly>
                     {!! $errors->first('monthAddCharge', '
                     <p class="help-block">:message</p>
                     ') !!}
                  </div>
                </div>
              </div>
              </div>
         </div>
        </div>
      </div>
    </div>
      </section>
@endsection