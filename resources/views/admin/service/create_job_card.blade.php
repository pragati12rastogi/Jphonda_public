@extends($layout)
@section('title', __('Create Job Card'))
@section('breadcrumb')
<li><a href="#"><i class=""></i></a></li>
@endsection
@section('css')
<link rel="stylesheet" href="/css/responsive.bootstrap.css">
<link rel="stylesheet" href="/css/bootstrap-duration-picker.css">
<style>
   tr.dangerClass{
   background-color: #f8d7da !important;
   }
   tr.successClass{
   background-color: #d4edda !important;
   }
   tr.worningClass{
   background-color: #fff3cd !important;
   }
   .wash option {
    padding: 10px;
   }
</style>
@endsection
@section('js')
<script src="/js/dataTables.responsive.js"></script>
<script src="/js/bootstrap-duration-picker.js"></script>

<script src="https://momentjs.com/downloads/moment-with-locales.js"></script>
<script>
   // $("#only_wash").hide();
    $('#js-msg-error').hide();
     $('#js-msg-success').hide();
  var start_time = @php echo json_encode($jobCard->estimated_delivery_time); @endphp;
  var oldJcType = @php echo json_encode((old('job_card_type')) ? old('job_card_type') : '' ); @endphp;
  $(document).ready(function(){
    if (start_time) {
       $('#timepicker').wickedpicker({
            twentyFour:true,
            now:start_time
        });
    }else{
       $('#estimate_hour').on('change', function() {
          var timet = $("#estimate_hour").val();
           // console.log(timet);
          var dt = new Date();
          var time = dt.getHours() + ":" + dt.getMinutes();
          // console.log(time);
          var extened_time = 0; 
          if($('#customer_status').val() == 'droping')
          {
             extened_time = 40;
          }
          else{
             extened_time = 30;
          }
          // console.log('timet',timet);
         var delivertime =  moment.utc(time,'HH:mm').add(extened_time,'minutes').add(timet,'seconds').format('HH:mm');
       
         // console.log(2, delivertime);
         $(".timepicker").val(delivertime);
         $('.timepicker').wickedpicker({
            twentyFour:true,
            now: delivertime
         }); 
      });
    }
  });
//   $('.duration-picker').durationPicker();

// // or

$('.duration-picker').durationPicker({
    
    // optional object with translations (English is used by default)
    translations: {
        day: 'dia',
        hour: 'hours',
        minute: 'minutes',
        second: 'segundo',
        days: 'dias',
        hours: 'hours',
        minutes: 'minutes',
        seconds: 'segundos',
    },

    // defines whether to show seconds or not
    showSeconds: false,

    // defines whether to show days or not
    showDays: false,

    // callback function that triggers every time duration is changed 
    //   value - duration in seconds
    //   isInitializing - bool value
    onChanged: function (value, isInitializing) {
        
        // isInitializing will be `true` when the plugin is initialized for the
        // first time or when it's re-initialized by calling `setValue` method
      //   console.log(value, isInitializing);
    }
});

 //   $('#mobile').on('keyup',function(){
 //      var mobile = $(this).val();
 //      $.ajax({
 //         url:'/admin/customer/find/mobile/'+mobile,
 //         method:'GET',
 //         data: {'mobile':mobile},
 //         success:function(res){
 //               console.log('res',res);
 //         }
 //         error:function(error){
 //            alert("something wen't wrong");
 //         }
 //      });
 //   });
 // var start_time = @php echo json_encode($jobCard->estimated_delivery_time); @endphp;
console.log("Jc:"+oldJcType)
 if (oldJcType != '') {
  GetJobCardType(oldJcType);
 }
  $("#job_card_type").on('change',function(){
    var type = $('#job_card_type').val();
    GetJobCardType(type);
  });
  $(document).ready(function(){
    $("#hjc_discount").css('display','none');
    if ($('#job_card_type').val() == 'Free') {
      $("#free_JC").css('display','block');
      $("#purchase_amc").css('display','none');
    }

   function GetJobCardType(type){
      var type = $('#job_card_type').val();
      if(type == 'HJC'){
        $("#hjc_discount").css('display','block');
        $("#purchase_amc").css('display','none');
        $("#duration_div").css('display','block');
        $("#free_JC").css('display','none')
        $("#only_wash").hide();
        $("#general_sub_type").css('display','none')
        $("#mejor_job").css('display','none');
        $("#acc_JC").css('display','none');
        var jcId = $("#jobcard_id").val();
           $.ajax({
             url:'/admin/service/check/hjc',
             method:'GET',
             data: {'type':type,'JcId':jcId},
             success:function(res){
                   if (res == 'success') {
        $("#hjc_discount").css('display','block');
                    
                       $("#only_wash").css('display','none');
                       $("#mejor_job").css('display','none');
                       $("#js-msg-success").html('You are valid for HJC  !');
                       $("#js-msg-success").show();
                       setTimeout(function() {
                         $('#js-msg-success').fadeOut('fast');
                        }, 4000);
                   }else{
        $("#hjc_discount").css('display','block');
                    
                    $("#purchase_amc").css('display','none');
                    $("#only_wash").css('display','none');
                    $("#purchase_hjc").css('display','block');
                    $("#js-msg-error").html('You are not  valid for HJC. Please firstly purchase !');
                        $("#js-msg-error").show();
                   }
             },
             error:function(error){
                alert("something wen't wrong");
             }
          });

      }
      if(type == 'AMC'){
        $("#hjc_discount").css('display','none');
        $("#duration_div").css('display','block');
        $("#free_JC").css('display','none')
        $("#only_wash").hide();
        $("#general_sub_type").css('display','none')
        $("#mejor_job").css('display','none');
        $("#acc_JC").css('display','none');
        var jcId = $("#jobcard_id").val();
           $.ajax({
             url:'/admin/service/check/jobcard',
             method:'GET',
             data: {'type':type,'JcId':jcId},
             success:function(res){
                   if (res == 'success') {
                       $("#only_wash").css('display','block');
                       $("#mejor_job").css('display','none');
                        $("#js-msg-success").html('You are valid for AMC !');
                        $("#js-msg-success").show();
                       setTimeout(function() {
                         $('#js-msg-success').fadeOut('fast');
                        }, 4000);
                   }else if (res == 'allowed'){
                     $("#only_wash").css('display','none');
                     $("#mejor_job").css('display','none');
                        $("#purchase_amc").css('display','block');
                        $("#js-msg-error").html('You are not valid for AMC. Please firstly purchase and select any other job card type !');
                        $("#js-msg-error").show();
                   }else{
                    $("#js-msg-error").html('You are not valid for AMC. Please firstly purchase !');
                        $("#js-msg-error").show();
                   }
             },
             error:function(error){
                alert("something wen't wrong");
             }
          });

      }else if(type == 'Paid'){
        $("#hjc_discount").css('display','none');
         $("#purchase_amc").css('display','block');
          $("#mejor_job").css('display','block');
          $("#duration_div").css('display','block');
          $("#free_JC").css('display','none')
          $("#only_wash").hide();
          $("#general_sub_type").css('display','none')
          $("#Old_JC").css('display','none');
          $("#acc_JC").css('display','none');
          var vehicle_type = $("#vehicle_type").val();
           GetServiceChecklist(type,vehicle_type);
      }else if(type == 'Accident'){
        $("#hjc_discount").css('display','none');
         $("#purchase_amc").css('display','none');
          $("#duration_div").css('display','none');
          $("#free_JC").css('display','none')
          $("#only_wash").hide();
          $("#general_sub_type").css('display','none')
          $("#mejor_job").css('display','none');
          $("#Old_JC").css('display','none');
          $("#acc_JC").css('display','block');
      }else if(type == 'General'){
        $("#hjc_discount").css('display','none');
         $("#purchase_amc").css('display','none');
        $("#general_sub_type").css('display','block')
        $("#duration_div").css('display','block');
        $("#free_JC").css('display','none')
        $("#only_wash").hide();
        $("#mejor_job").css('display','none');
        $("#acc_JC").css('display','none');
        $("#sub_type").select2({"placeholder":"Select sub type"});
      }else if(type == 'Free'){
        $("#hjc_discount").css('display','none');
        $("#free_JC").css('display','block')
        $("#duration_div").css('display','block');
        $("#only_wash").hide();
        $("#general_sub_type").css('display','none')
        $("#mejor_job").css('display','none');
        $("#Old_JC").css('display','none');
        $("#acc_JC").css('display','none');
        $("#purchase_amc").css('display','none');
      }else{
        $("#hjc_discount").css('display','none');
         $("#purchase_amc").css('display','none');
        $("#duration_div").css('display','block');
        $("#free_JC").css('display','none')
        $("#only_wash").hide();
        $("#general_sub_type").css('display','none')
        $("#mejor_job").css('display','none');
        $("#Old_JC").css('display','none');
        $("#acc_JC").css('display','none');
      }
   }
 };

   $(document).ready(function(){
     $("#general_sub_type").on('change',function(){

      $('#sub_type :selected').each(function(i, sel){ 
         if ($(sel).val() == 'Repeat Job') {
            $("#Old_JC").css('display','block');
          }else{
             $("#Old_JC").css('display','none');
          }
      });

     })
 });


  $(document).ready(function(){
        $("input[name='purchase_amc']").change(function(){
         
            var purchase_amc = $("input[name='purchase_amc']:checked").val();
        
            if(purchase_amc){
                if (purchase_amc == 'yes') {
                  $("#amc_product").show();
                  $("#service_type").show();
                }
              }else{
                  $("#amc_product").hide();
                  $("#service_type").hide();
                }
        });
    });


   $(document).ready(function(){
        $("input[name='insurance']").change(function(){
         
            var insurance = $("input[name='insurance']:checked").val();
        
            if(insurance){
                if (insurance == 'Yes') {
                  $("#renewal-div").show();
                  $("#available-div").hide();
                }
              }if (insurance == 'No') {
                  $("#renewal-div").hide();
                  $("#available-div").show();
                }
        });
    });



      $("#amc_prod").change(function(){

        var amc_prod_id = $("#amc_prod").val();
           $.ajax({
             url:'/admin/service/amc/product',
             method:'GET',
             data: {'amc_prod_id':amc_prod_id},
             success:function(res){
                if (res) {
                  $("#custom_price").html('<div class="col-md-12 margin-bottom" id="" "><label for="sub_type">AMC Amount(between '+res.min_price+' and '+res.max_price+')</label><input type="number" name="amc_amount" class="input-css" min="'+res.min_price+'" max="'+res.max_price+'"/></div>'
                    );
                }
             },
             error:function(error){
                alert("something wen't wrong");
             }
          });
        });




  var renewl_date = @php echo json_encode($renewl_date) ;@endphp;
  var date = @php echo json_encode(date('m/d/Y', strtotime($renewl_date))) ;@endphp;


if (renewl_date) {
  $(".datepicker").datepicker(
 "setDate" , date );
}else{
  var currentDate = new Date(); 
 $(".datepicker").datepicker(
 "setDate" , currentDate );
}

 $("#free_sub_type").change(function(){
  var type = $("#free_sub_type").val();
  var vehicle_type = $("#vehicle_type").val();
  if (type == 'FS1' || type == 'FS2' || type == 'FS3' || type == 'FS4') {
    GetServiceChecklist(type,vehicle_type);
  }else{
     $("#cheklist").empty();
  }
 });

 $("#sub_type").change(function(){
  var type = $("#sub_type").val();
  var vehicle_type = $("#vehicle_type").val();
  if (type == 'FS1' || type == 'FS2' || type == 'FS3' || type == 'FS4') {
    GetServiceChecklist(type,vehicle_type);
  }else{
     $("#cheklist").empty();
  }
 });

 function GetServiceChecklist(ServiceType,VehicleType) {
  $('#ajax_loader_div').css('display', 'block');
     $.ajax({
       url:'/admin/service/check/list',
       method:'GET',
       data: {'service_type':ServiceType,'vehicle_type':VehicleType},
       success:function(res){
          if (res) {
            $("#cheklist").empty();
                    $("#cheklist").append('<label for="checklist">Service Check List </label><br>');
                 $.each(res, function(key, value) {
                         $("#cheklist").append('<input type="checkbox" class="checklist" name="checklist[]" value="'+value.id+'"> '+ value.name +'<br>');
                    });
          }
       },
       error:function(error){
          alert("something wen't wrong");
       }
    }).done(function() {
            $('#ajax_loader_div').css('display', 'none');            
        });
 }


$("#form-id").validate({ 
 
    rules: { 
            "checklist[]": { 
                    required: true, 
                    minlength: $('input[name="cheklist"]').length
            } 
    }, 
    messages: { 
            "checklist[]": "Please check all check list."
    } 
}); 



</script>
@endsection
@section('main_section')
<section class="content">
   <div id="app">

         @include('admin.flash-message')
         @yield('content')

         <div class="box-header with-border">
           @include('layouts.jobcard_tab')
          <div class="row">
         <div class="alert alert-danger" id="js-msg-error" style="display: none;">
         </div>
         <div class="alert alert-success" id="js-msg-success" style="display: none;">
         </div>
       </div>

            <div class="box box-primary">
                <div class="box-header">

                </div>  
               
               <form  action="/admin/service/create/jobcard/{{$jobCardId}}" method="post" id="form-id">
                    @csrf
                  <div class="row">
                  
                     <div class="col-md-6 margin-bottom">
                        <div class="col-md-12">
                          <input type="hidden" name="jobcard_id" id="jobcard_id" value="{{$jobCardId}}">
                          <input type="hidden" name="amc" id="amc" value="{{$amc}}">
                           <label for="job_card_type">Job Card Type <sup>*</sup></label>
                           <select name="job_card_type" id="job_card_type" class="input-css select2">
                              <option value="" selected="" disabled="">Select Job Card Type</option>
                              @foreach($jobcard_type as $key => $val)
                              @if($val->key == $jobCard->job_card_type)
                                 <option value="{{$val->key}}" {{(old('job_card_type') == $key)? 'selected' : '' }}  selected="">{{$val->value}}</option>
                                 @else
                                 <option value="{{$val->key}}" {{(old('job_card_type') == $key)? 'selected' : '' }} >{{$val->value}}</option>
                              @endif
                              @endforeach
                           </select>
                           {!! $errors->first('job_card_type', '<p class="help-block">:message</p>') !!}

                        </div>
                        <div class="col-md-12 margin-bottom">
                          <div class="col-md-6"  id="only_wash" style="display: none;">
                             <label for="only_wash">Only Wash</label>
                           <input type="radio" name="only_wash" value="Yes" @if($jobCard['only_wash'] == 'Yes') checked="" @endif> Yes
                          </div>

                           <div class="col-md-12 margin-bottom"  id="purchase_amc" @if($amc == 0) style="display: block;" @else style="display: none;" @endif>
                             <label for="purchase_amc">Purchase AMC</label>
                           <input type="checkbox" name="purchase_amc" value="yes"> Yes
                          </div>


                          <div class="col-md-12 margin-bottom" id="amc_product" style="display: none;">
                              <label for="repeat">AMC Product</label>
                              <select class="select2" name="amc_prod" id="amc_prod" style="width: 100%;">
                                <option selected="" disabled="">Select job card</option>
                                @if(isset($amc_product))
                                @foreach($amc_product as $item)
                                <option value="{{$item->id}}">{{$item->name}}</option>
                                @endforeach
                              </select>
                              @endif
                            </div>
                            <div id="custom_price"></div>

                            <div class="col-md-12 margin-bottom" id="service_type" style="display: none;">
                              <label for="repeat">Type</label>
                              <select class="input-css select2" name="amc_type" id="amc_type" style="width: 100%;">
                                <option selected="" disabled="">Select type</option>
                                <option value="same_service" @if($same_type->value != 'allowed') disabled @endif>Same Service</option>
                                <option value="next_service">Next Service</option>
                              </select>
                            </div>

                           <div class="col-md-6"  id="purchase_hjc" style="display: none;">
                             <label for="purchase_hjc">Purchase HJC</label>
                           <input type="checkbox" name="purchase_hjc" value="Yes"> Yes
                          </div>

                           <div class="col-md-6"  id="mejor_job" style="display: none;">
                             <label for="mejor_job">Mejor Job</label>
                           <input type="checkbox" name="mejor_job" value="Yes"> Yes
                          </div> 
  
                        </div>
                        <input type="hidden" name="vehicle_type" id="vehicle_type" value="{{$jobCard->vehicle_type}}">
                          <div class="col-md-12 margin-bottom" id="general_sub_type" style="display: none;">
                             <label for="sub_type">Sub Type</label>
                              <select name="sub_type[]" id="sub_type" class="form-control select2" multiple="multiple" style="width: 100%;">
                                  <option value="FS1">Complimentary Service - Fs1 Punched</option>
                                  <option value="FS2">  Complimentary Service - Fs2 Punched</option>
                                  <option value="FS3">Complimentary Service - Fs3 Punched</option>
                                  <option value="FS4">Complimentary Service - Fs4 Punched</option>
                                  <option value=" Minor Changes"> Minor Changes</option>
                                  <option value="Part Change">Part Change</option>
                                  <option value="Mileage Related Issue">Mileage Related Issue</option>
                                  <option value="Complaint Related - Psf">Complaint Related - Psf</option>
                                  <option value="Complaint Related - Honda">Complaint Related - Honda</option>
                                  <option value="Complaint Related - Others">Complaint Related - Others</option>
                                  <option value="Complaint Related - Sub-Dealer">Complaint Related - Sub-Dealer</option>
                                  <option value="Painting Related">Painting Related</option>
                                  <option value="Complimentary Service">Complimentary Service</option>
                                  <option value="Value Added Services">Value Added Services</option>
                                  <option value="Oil Change">Oil Change</option>
                                  <option value="Pud">Pud</option>
                                  <option value="Warranty">Warranty</option>
                                  <option value="Accessories Change">Accessories Change</option>
                                  <option value="Repeat Job">Repeat Job</option>
                              </select>
                            </div>

                            <div class="col-md-12 margin-bottom" id="Old_JC" style="display: none;">
                              <label for="repeat">Old Jobcard</label>
                              <select class="select2" name="old_job_card" id="old_jobcard" style="width: 100%;">
                                <option selected="" disabled="">Select job card</option>
                                @if(isset($repeatJC))
                                @foreach($repeatJC as $item)
                                <option value="{{$item->id}}">{{$item->tag}}</option>
                                @endforeach
                              </select>
                              @endif
                            </div>

                            <div class="col-md-12 margin-bottom" id="free_JC" style="display: none;">
                              <label for="repeat">Sub Type</label>
                              <select class="input-css select2" name="free_sub_type" id="free_sub_type" style="width: 100%;">
                                <option selected="" disabled="">Select sub type</option>
                                <option value="FS1">Complimentary Service - Fs1 Punched</option>
                                  <option value="FS2">  Complimentary Service - Fs2 Punched</option>
                                  <option value="FS3">Complimentary Service - Fs3 Punched</option>
                                  <option value="FS4">Complimentary Service - Fs4 Punched</option>
                              </select>
                            </div>

                            <div class="col-md-12 margin-bottom" id="acc_JC" style="display: none;">
                              <label for="repeat">Sub Type</label>
                              <select class="input-css select2" name="acc_sub_type" id="acc_sub_type" style="width: 100%;">
                                <option selected="" disabled="">Select sub type</option>
                                <option value="Best Deal">Best Deal</option>
                              </select>
                            </div>



                           <div class="col-md-12 margin-bottom">
                              <label for="customer_status">Customer Status <sup>*</sup></label>
                              <select name="customer_status" id="customer_status" class="input-css select2">
                                 <option value="">Select Customer Status</option>
                                 <option value="waiting" @if($jobCard->customer_status == 'waiting') selected @endif {{(old('customer_status') == 'waiting')? 'selected' : ''}} >Wait</option>
                                 <option value="droping" @if($jobCard->customer_status == 'droping') selected @endif {{(old('drop') == 'droping')? 'selected' : ''}} >Drop</option>
                              </select>
                              {!! $errors->first('customer_status', '<p class="help-block">:message</p>') !!}
                              
                           </div>
                           @if($jobCard->customer_id != 0)
                           <input type="hidden" name="customer_id" value="{{$jobCard->customer_id}}">
                           <div class="col-md-12 margin-bottom">
                              <label for="mobile">Customer Mobile Number <sup>*</sup></label>
                              <input type="text" name="mobile" id="mobile" value="{{(old('mobile'))?old('mobile') : ''}}" class="input-css" placeholder="Enter Mobile Number">
                              {!! $errors->first('mobile', '<p class="help-block">:message</p>') !!}
                              
                           </div>
                           @endif
                           <div class="col-md-12 margin-bottom">
                              <label for="frame_km">Vehicle K.M. <sup>*</sup></label>
                              <input type="text" name="frame_km" id="frame_km" value="{{$jobCard->vehicle_km}}" class="input-css" placeholder="Enter Vehicle KiloMeter">
                              {!! $errors->first('frame_km', '<p class="help-block">:message</p>') !!}
                           </div>
                           <div id="duration_div" style="display: block;">
                           <div class="col-md-12 margin-bottom">
                              <label for="estimate_hour">Estimate Service Duration (HH:MM) <sup>*</sup></label>
                              <input type="text" name="estimate_hour" id="estimate_hour"  class="input-css duration-picker" placeholder="Enter Estimate Hour">
                              {!! $errors->first('estimate_hour', '<p class="help-block">:message</p>') !!}
                           </div>
                      
                       
                           <div class="col-md-12 margin-bottom">
                              <label for="estimate_delivery">Estimate Delivery <sup>*</sup></label>
                              <input type="text" name="estimate_delivery" value="{{$jobCard->estimated_delivery_time}}" id="timepicker" class="input-css timepicker" placeholder="Enter Estimate Delivery">
                              {!! $errors->first('estimate_delivery', '<p class="help-block">:message</p>') !!}
                           </div>
                         </div>
                        
                        
                           <div class="col-md-12 margin-bottom">
                              <label for="oilinfornt_customer">Oil change (Infront of Customer)<sup>*</sup></label>
                              {!! $errors->first('oilinfornt_customer', '
                              <p class="help-block">:message</p>
                              ') !!}
                              <div class="col-md-2">
                                 <div class="radio">
                                    <label><input autocomplete="off" {{(old('oilinfornt_customer') == 'Yes') ? 'checked' : '' }} type="radio" class="" value="Yes" name="oilinfornt_customer">Yes</label>
                                 </div>
                              </div>
                              <div class="col-md-2">
                                 <div class="radio">
                                    <label><input autocomplete="off"  {{(old('oilinfornt_customer') == 'No') ? 'checked' : '' }}  type="radio" class="" value="No" name="oilinfornt_customer">No</label>
                                 </div>
                              </div>
                           </div>


                           <div class="col-md-12 margin-bottom">
                              <label for="oilinfornt_customer">Insurance<sup>*</sup></label>
                              {!! $errors->first('insurance', '
                              <p class="help-block">:message</p>
                              ') !!}
                              <div class="col-md-2">
                                 <div class="radio">
                                    <label><input autocomplete="off" @if(isset($getInsurance->id))  {{(isset($getInsurance->id)) ? 'checked' : '' }}  @endif type="radio" class="" value="Yes" name="insurance">Yes</label>
                                 </div>
                              </div>
                              <div class="col-md-2">
                                 <div class="radio">
                                    <label><input autocomplete="off"  {{(old('insurance') == 'No') ? 'checked' : '' }}  type="radio" class="" value="No" name="insurance">No</label>
                                 </div>
                              </div>
                           </div>


                           <div class="col-md-12 margin-bottom" id="renewal-div" @if(isset($renewl_date)) style="display: block;" @else style="display: none;" @endif >
                              <label for="oilinfornt_customer">Renewal Date<sup>*</sup></label>
                              <input type="text" value="{{date('d/m/Y', strtotime($renewl_date))}}" name="renewal_date" id="renewal_date" class="input-css datepicker1">
                           </div>

                           <div class="col-md-12 margin-bottom" id="available-div" style="display: none;">
                              <label for="oilinfornt_customer">Availability<sup>*</sup></label>
                              <select class="input-css select2" name="available_type" style="width: 100%;">
                                <option selected="" disabled="">Select type</option>
                                <option value="Doc Not Available">Doc Not Available</option>
                                <option value="Insurance Not Available">Insurance Not Available</option>
                              </select>
                           </div>
                           <div class="col-md-6"></div>
                        
                        <br>
                        <div class="col-md-12 margin">
                           <div class="col-md-6">
                               <button type="submit" class="btn btn-success">Submit</button>
                           </div>
                           <br><br>    
                        </div>
                     </div>
                      <div class="col-md-6 margin-bottom" id="hjc_discount" style="display: none;">
                        <label for="job_card_type">Discount Type </label>
                        <div class="row">
                        <div class="col-md-3">
                          <input type="checkbox" name="part_discount" value="Parts"> Parts
                           </div>
                           <div class="col-md-9">
                            <input type="text" name="part_promo" class="input-css" placeholder="Enter Promo Code">  
                          </div>
                         </div><br>
                         <div class="row">
                        <div class="col-md-3">
                          <input type="checkbox" name="service_discount" value="Service"> Service
                           </div>
                           <div class="col-md-9">
                            <input type="text" name="service_promo" class="input-css" placeholder="Enter Promo Code">  
                          </div>
                         </div><br>
                        <div class="row">
                        <div class="col-md-3">
                          <input type="checkbox" name="labour_discount" value="Labour"> Labour
                           </div>
                           <div class="col-md-9">
                            <input type="text" name="labour_promo" class="input-css" placeholder="Enter Promo Code">  
                          </div>
                         </div><br>
                          <div class="row">
                        <div class="col-md-3">
                          <input type="checkbox" name="engine_discount" value="Engine Oil"> Engine Oil
                           </div>
                           <div class="col-md-9">
                            <input type="text" name="engine_promo" class="input-css" placeholder="Enter Promo Code">  
                          </div>
                         </div><br>
                          <div class="row">
                        <div class="col-md-3">
                          <input type="checkbox" name="pickup_discount" value="Pickup & Drop"> Pickup & Drop
                           </div>
                           <div class="col-md-9">
                            <input type="text" name="pickup_promo" class="input-css" placeholder="Enter Promo Code">  
                          </div>
                           {!! $errors->first('job_card_type', '<p class="help-block">:message</p>') !!}
                         </div>
                      </div><br>
                      <div class="col-md-6 margin-bottom" id="cheklist"></div>
                     <div class="col-md-6 margin-bottom" style="height:100%; oveflow:auto;">

                        <ul class="timeline">
                           @foreach($history_service as $key => $val)
                              <li class="time-label">
                                 <span class="bg-red">
                                    {{$val->created_at}}
                                 </span>
                              </li>
                              <li>
                                 <i class="fa fa-list-alt bg-blue"></i>
                                 <div class="timeline-item">
                                    {{-- <span class="time"><i class="fa fa-clock-o"></i> 12:05</span> --}}
                        
                                    <h3 class="timeline-header"><a href="#">Short Summary</a></h3>
                        
                                    <div class="timeline-body">
                                       <div class="row">
                                          <label class="col-md-6">Vehicle Km </label>
                                          <label class="col-md-4"> {{$val->vehicle_km}} </label>
                                       </div>
                                       <div class="row">
                                          <label class="col-md-6">Service Amount </label>
                                          <label class="col-md-4"> {{$val->hirise_amount}} </label>
                                       </div>
                                       <div class="row">
                                          <label class="col-md-6">Service Suggestion </label>
                                          <label class="col-md-4"> {{$val->service_suggest}} </label>
                                       </div>
                                       <div class="row">
                                          <label class="col-md-6">Customer Feedback </label>
                                          <label class="col-md-4"> {{$val->feedback}} </label>
                                       </div>
                                    </div>
                        
                                    {{-- <div class="timeline-footer">
                                          <a class="btn btn-primary btn-xs"></a>
                                    </div> --}}
                                 </div>
                              </li>
                           @endforeach
                           
                       </ul>
                       
                     </div>
                     
                  </div>

                </form>
            </div>
         </div>
   </div>
   
   <!-- Modal -->
   <!-- /.box -->
</section>
@endsection