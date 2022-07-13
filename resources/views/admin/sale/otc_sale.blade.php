@extends($layout)
@section('title', __('OTC Sale'))
@section('breadcrumb')
<li><a href="#"><i class=""></i> {{__('OTC Sale')}} </a></li>
@endsection
@section('css')
<style>
   .cost{
      text-align:right;
   }
</style>
@endsection
@section('js')
<script src="/js/pages/additional_services.js"></script>
<script>
   $("#amc_product").hide();
   $(document).ready(function(){
    $('#otc_sale_discount').val(oldDiscount);
    $('#final_hjc_disc').text(oldfinal_hjc_disc_input);
    $('#final_hjc_disc_input').val(oldfinal_hjc_disc_input);
    $('#frame_number').trigger('keyup');
   });
   var frameValidate =  @php echo json_encode((old('frame_number') == '') ? 1 : 1 ); @endphp;
   var oldAmount = @php echo json_encode((old('all_total_amount_input')) ? old('all_total_amount_input') : 0 ); @endphp;
   var oldDiscount = @php echo json_encode((old('otc_sale_discount')) ? old('otc_sale_discount') : 0 ); @endphp;
   var oldfinal_hjc_disc_input = @php echo json_encode((old('final_hjc_disc_input')) ? old('final_hjc_disc_input') : 0 ); @endphp;
   var oldModel = @php echo json_encode((old('model_name')) ? old('model_name') : '' ); @endphp;
   
   
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
   // console.log(oldAccVal);
   if(oldAmount > 0)
   {
     calculate();
   }
   if(oldModel != '')
   {
     //   console.log('old2',oldModel);
         $('#model_name').val(oldModel);
         $('#model_name').trigger('change');
         
   }

   $(document).ready(function () {
      $(".amc_product").change(function(){
          var amc_prod_id = $(".amc_product").val();
           $.ajax({
             url:'/admin/service/amc/product',
             method:'GET',
             data: {'amc_prod_id':amc_prod_id},
             success:function(res){
                if (res) {
                  $("#help-block").html('Please enter price between '+res.min_price+' - '+res.max_price+'');
                   $("#amc_cost").on('mouseup change', function () {
                       $(this).val(Math.min(res.max_price, Math.max(res.min_price, $(this).val())));
                     });
                }
             }
          });

      });
   });
   if(oldfinal_hjc_disc_input > 0){
      hjc_show();
   }
  $(".hjc_disc").blur(function(){
      hjc_show();
  }) 
   
   function hjc_show(){
    var hjc_cost =$("#hjc_cost").val();
      var hjc_voucher_in = $("#hjc_voucher_in").val();

      if($("#hjc").prop('checked') == true && hjc_cost != '' && hjc_cost != 0 && hjc_voucher_in != null && hjc_voucher_in != ''){ 
        var hjc_disc = <?php $disc = CustomHelpers::getHJCDiscount();
                $discAmt = $disc['Accessories'];
                echo($discAmt);
                ?>;
        var final_hjc_disc = parseInt(hjc_cost) * parseInt(hjc_disc)/100;
        $('#final_hjc_disc_input').val(final_hjc_disc);
        $('#final_hjc_disc').text(final_hjc_disc);
        calculate();
      }
   }
</script>
@endsection
@section('main_section')
<section class="content">
   <div id="searchResult"></div>
   @include('admin.flash-message')
   @yield('content')
   <!-- general form elements -->
   <div class="box-header with-border">
   <div class="box box-primary">
      <div class="box-header">
      </div>
      <form id="my-form" action="/admin/sale/otcsale" method="post">
         @csrf
        
         <div class="row">
            <div class="col-md-6">
               <div class="row">
                  <div class="col-md-12">
                     <label>{{__('services.frame_number')}} ( Optional ) <sup></sup></label>
                     <input type="text" id="frame_number" name="frame_number" class="form-control" placeholder="search frame number" value="{{old('frame_number')}}">
                     {!! $errors->first('frame_number', '
                     <p class="help-block">:message</p>
                     ') !!}
                     <span id="frame_msg" style="color:red"></span>
                     <input type="hidden" name="sale_id" id="sale_id" value="">
                  </div>
               </div>
               <div class="row">
                  <div class="col-md-12">
                  <div style="{{(count($store) == 1)? 'display:none' : 'display:block'}}">
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
                           <option value="{{$store[0]['id']}} selected">{{$store[0]['name']}}</option>
                        </select>
                     @endif
                     {!! $errors->first('store_name', '
                     <p class="help-block">:message</p>
                     ') !!}
                  </div>
                 </div>
               </div>
               <div class="row">
                  <div class="col-md-12">
                     <label>{{__('services.name')}} <sup>*</sup></label>
                     {{-- <input type="text" name="customer_name" id="customer_name"  class="input-css inputValidation " value="{{old('customer_name')}}" > --}}
                     <select name="customer_name" class="input-css select2 selectValidation" id="customer_name" style="width: 100%">
                        <option selected="" disabled="">Select Customer</option>
                        @foreach($customer as $cust)
                          <option value="{{$cust->id}}" {{old('customer_name')== $cust->id ? 'selected=selected' : ''}}>{{$cust->name}}-{{$cust->mobile}}{{($cust->aadhar_id)? '-'.$cust->aadhar_id : ''}}</option>
                        @endforeach
                    </select>
                    <small>Create Customer
                        <a href="/admin/customer/registration?for=otcsale" target="_blank">Click Here..</a>
                    </small>
                     {!! $errors->first('customer_name', '<p class="help-block">:message</p>') !!}
                  </div>
               </div>

               {{-- <div class="row">
                  <div class="col-md-12">
                     <label>{{__('services.mobile')}} <sup>*</sup></label>
                     <input type="text" name="number" id="number" class="input-css inputValidation" value="{{old('number')}}">
                     {!! $errors->first('number', '
                     <p class="help-block">:message</p>
                     ') !!}
                  </div>
               </div> --}}
               <br>
               <div class="row">
                  <div class="col-md-3">
                     <div class="radio">
                        <label><input autocomplete="off" {{(old('ew'))? 'checked' : ''}} type="checkbox" class="" value="ew" name="ew" id="ew">EW</label>
                     </div>
                  </div>
                  <div class="col-md-4">
                     <div class="ratio">
                        <select name="ew_duration" id="ew_duration" class="input-css select2">
                           <option value=""> Select EW Duration</option>
                           <option value="1" {{(old('ew_duration') == 1)? 'selected' : ''}} >1</option>
                           <option value="2" {{(old('ew_duration') == 2)? 'selected' : ''}}>2</option>
                           <option value="3" {{(old('ew_duration') == 3)? 'selected' : ''}}>3</option>
                        </select>
                     </div>
                     {!! $errors->first('ew_duration', '
                     <p class="help-block">:message</p>
                     ') !!}
                  </div>
                  <div class="col-md-5">
                     <div class="radio">
                        <input type="text" class="input-css calculationAll" {{(old('ew'))? '' : 'readonly'}} placeholder="Enter price"  name="ew_cost" id="ew_cost" value="{{old('ew_cost')}}">
                     </div>
                     {!! $errors->first('ew_cost', '
                     <p class="help-block">:message</p>
                     ') !!}
                  </div>
               </div>
              
               <div class="row">
                  <div class="col-md-3">
                     <div class="radio">
                        <label><input autocomplete="off" {{(old('amc'))? 'checked' : ''}} type="checkbox" class="" value="amc" id="amc" name="amc">AMC</label>
                     </div>
                  </div>
                  <div class="col-md-9">
                     <span id="help-block" style="color: red"></span>
                     <div class="radio">
                        <input type="text" class="input-css calculationAll" {{(old('amc'))? '' : 'readonly'}} placeholder="Enter price" name="amc_cost" id="amc_cost" value="{{old('amc_cost')}}">
                        {!! $errors->first('amc_cost', '
                        <p class="help-block">:message</p>
                        ') !!}
                     </div>
                  </div>
               </div>
                <div  id="amc_product" style="display: none;">
                  <div class="col-md-12">
                     <label>AMC Product</label>
                     <select class="input-css select2 amc_product" name="amc_product"  style="width: 100%;">
                        @if(isset($amcProduct))
                         <option selected="" disabled="">Select AMC Product</option>
                           @foreach($amcProduct as $item)
                              <option value="{{$item['id']}}"  {{(old('amc_product') == $item['id']) ? 'selected' : '' }}>{{$item['name']}}</option>
                           @endforeach
                        @endif
                     </select>
                  </div>
               </div><br>
               <div class="row">
                  <div class="col-md-3">
                     <div class="radio">
                        <label><input autocomplete="off" {{(old('hjc'))? 'checked' : ''}}  type="checkbox" class="hjc_disc" value="hjc" id="hjc" name="hjc">HJC</label>
                     </div>
                  </div>
                  <div class="col-md-9">
                     <div class="radio">
                        <input type="text" class="input-css calculationAll hjc_disc" {{(old('hjc'))? '' : 'readonly'}} placeholder="Enter price" name="hjc_cost" id="hjc_cost" value="{{old('hjc_cost')}}">
                        {!! $errors->first('hjc_cost', '
                        <p class="help-block">:message</p>
                        ') !!}
                     </div>
                  </div>
               </div>
                <div  id="hjc_voucher" style="display:none ;">
                  <div class="col-md-12">
                     <label>HJC Voucher</label>
                     <input type="text" name="hjc_voucher" id="hjc_voucher_in" value="{{(old('hjc_voucher') ? old('hjc_voucher') : '')}}" class="input-css hjc_disc" placeholder="Enter HJC voucher">
                  </div>
               </div><br>
               <hr>
               <div class="row">
                  <div class="col-md-4">
                     <label>Name</label>
                  </div>
                  <div class="col-md-4 cost">
                     <label>Qty</label>
                  </div>
                  <div class="col-md-4 cost">
                     <label>Amount</label>
                  </div>
               </div>
               <hr>
               <div class="row">
                  <div class="col-md-4">
                     <label>Total Accessories</label>
                  </div>
                  <div class="col-md-4 cost">
                     <label id="total_qty">0</label>
                  </div>
                  <div class="col-md-4 cost" >
                     <label id="total_amount">0</label>
                  </div>
               </div>
               <div class="row">
                  <div class="col-md-4">
                     <label>EW</label>
                  </div>
                  <div class="col-md-4 cost">
                     <label id="total_qty_ew">{{(old('ew')) ? 1 : 0}} </label>
                  </div>
                  <div class="col-md-4 cost">
                     <label id="ew_total_cost"> {{(old('ew_cost')) ? old('ew_cost') : 0}} </label>
                  </div>
               </div>
               <div class="row">
                  <div class="col-md-4">
                     <label>AMC</label>
                  </div>
                  <div class="col-md-4 cost">
                     <label id="total_qty_amc">{{(old('amc')) ? 1 : 0}} </label>
                  </div>
                  <div class="col-md-4 cost">
                     <label id="amc_total_cost">{{(old('amc_cost')) ? old('amc_cost') : 0}} </label>
                  </div>
               </div>
               <div class="row">
                  <div class="col-md-4">
                     <label>HJC</label>
                  </div>
                  <div class="col-md-4 cost">
                     <label id="total_qty_hjc">{{(old('hjc')) ? 1 : 0}} </label>
                  </div>
                  <div class="col-md-4 cost">
                     <label id="hjc_total_cost">{{(old('hjc_cost')) ? old('hjc_cost') : 0}} </label>
                  </div>
               </div>
               <hr>
               <div class="row">
                  <div class="col-md-12">
                    <div class="col-md-6">
                       <label>Discount</label>
                    </div>
                    <div class="col-md-6 discount">
                       <input type="number" class="input-css right" min="0" name="otc_sale_discount" id="otc_sale_discount" value="{{old('otc_sale_discount')}}">
                    </div>
                  </div>
                  <div class="col-md-12">
                    <div class="col-md-6">
                       <label>Total Amount</label>
                    </div>
                    <div class="col-md-6 cost">
                       <label id="all_total_amount" > {{old('all_total_amount_input')}} </label>
                       <input type="hidden" hidden name="all_total_amount_input" id="all_total_amount_input" value="{{old('all_total_amount_input')}}">
                    </div>
                  </div>
               </div>
               <div class="row">
                  <div class="col-md-12">
                    <div class="col-md-6">
                      <label>Discount Amount(-)</label>
                    </div>
                    <div class="col-md-6 cost">
                       <label id="otc_final_discount" >{{old('otc_final_discount')}} </label>
                    </div>
                  </div>
                  <div class="col-md-12">
                    <div class="col-md-6">
                      <label>HJC Discount(-)</label>
                    </div>
                    <div class="col-md-6 cost">
                       <label id="final_hjc_disc" >{{old('final_hjc_disc')}} </label>
                       <input type="hidden" hidden name="final_hjc_disc_input" id="final_hjc_disc_input" value="{{old('final_hjc_disc_input')}}">
                    </div>
                  </div>
                  <div class="col-md-12">
                    <div class="col-md-6">
                      <label>Balance</label>
                    </div>
                    <div class="col-md-6 cost">
                       <label id="otc_balance"> {{old('otc_balance_input')}} </label>
                       <input type="hidden" hidden name="otc_balance_input" id="otc_balance_input" value="{{old('otc_balance_input')}}">
                    </div>
                  </div>
               </div>
               <hr>
            </div>
            <div class="col-md-6">
               <div class="row">
                  <label>{{__('Model Name')}} <sup>*</sup></label>
                  {{-- 
                  <select name="accessories[]" id="accessories" class="form-control select2" multiple="multiple" data-placeholder="Select a pending item"
                     style="width: 100%;">
                     <option value=" ">Select Accessories</option>
                     @foreach($accessories as $acc)
                     <option value="{{$acc->id}}" >{{$acc->name}}</option>
                     @endforeach
                  </select>
                  --}}
                  <select name="model_name" id="model_name" class="select2 input-css" onchange="model_nameChange(this)">
                     <option value="">Select Model Name</option>
                     @foreach ($model_name as $item)
                     <option value="{{$item['model_name']}}">{{$item['model_name']}}</option>
                     @endforeach
                  </select>
                  {!! $errors->first('model_name', '
                  <p class="help-block">:message</p>
                  ') !!}
               </div>
               <br><br>
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
               <div class="row" id="append_accessories">
               </div>
            </div>
         </div>
         <br>
         <div class="row">
            <div class="col-md-6">
               <button type="submit" class="btn btn-success">Submit</button>
            </div>
            <br><br>    
         </div>
      </form>
   </div>
</section>
@endsection