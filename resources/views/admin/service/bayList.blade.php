@extends($layout)
@section('title', __('Job Card List'))
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
   .modal-header .close {
    margin-top: -33px;
}
.remove_0{
  display: none;
}
</style>
@endsection
@section('js')
<script src="/js/dataTables.responsive.js"></script>
<script src="/js/bootstrap-duration-picker.js"></script>
<meta name="csrf-token" content="{{ csrf_token() }}" />

<script>
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
</script>

<script>
   var dataTable;
   $('#js-msg-error').hide();
   $('#js-msg-success').hide();
   $('#chargesdata_div').hide();
   // Data Tables'additional_services.id',
   $(document).ready(function() {
     dataTable = $('#table').DataTable({
         "processing": true,
         "serverSide": true,
         "ajax": "/admin/service/floor/supervisor/api",
         "aaSorting": [],
         "responsive": true,
         "columns": [
             { "data": "tag"},
             { "data": "frame"},
             { "data": "registration"},
             { "data": "job_card_type"},
             { "data": "customer_status"},
             { "data": "vehicle_km"},
             { "data": "bay_name"},
             { "data": "start_time"},
             { "data": "end_time"},
             {
                 "targets": [ -1 ],
                 "data":"id", "render": function(data,type,full,meta)
                 {
                    var str = '';
                    if(((full.service_in_time)? full.service_in_time : '').length == 0 && full.status != 'Close')
                    {
                      str = str+'<a href="#" id="'+data+'" class="serviceIn"><button class="btn btn-danger btn-xs">Service In</button></a> &nbsp;';
                    }else{
                      str = str+'<a href="#" id="'+data+'" class="charges"><button data-toggle="modal" data-target="#AddModalCharges" class="btn btn-success btn-xs "> Charges</button></a> &nbsp;';
                    }

                    if(full.service_in_time != null && full.service_out_time == null && full.status != 'Close')
                    {
                      str = str+'<a href="#" id="'+data+'" class="serviceOut"><button class="btn btn-success btn-xs ">Service Out</button></a> &nbsp;'+
                              '<a href="#" job_card_id="'+data+'" ba_stime="'+full.ba_stime+'" ba_etime="'+full.ba_etime+'" class="schedule"><button class="btn btn-info btn-xs ">Schedule Service</button></a> &nbsp;';
                    }
                  
                       str = str+'<a href="#"><button class="btn btn-warning btn-xs "  onclick="ShowRecording(this)"><i class="'+data+'"></i>Service Detail</button></a>';
                  
                    if(((full.service_out_time)? full.service_out_time : '').length == 0 && full.only_wash == 'No' && full.status != 'Closed')
                    {
                        
                        str = str+"<a href='/admin/service/part/request/"+full.id+"'><button class='btn btn-info btn-xs'>Part's Request</button></a> &nbsp;";
                        // if(full.customer_confirmation != 'yes')
                        // {
                        //    str = str+'<a href="#" id="'+data+'" class="customer_confirmation"><button class="btn btn-primary btn-xs ">Customer Confirmation</button></a> &nbsp;';
                        // }
                     }
                    
                     // str = str+'<a href="#" job_card_id="'+data+'" ba_stime="'+full.ba_stime+'" ba_etime="'+full.ba_etime+'" class="schedule"><button class="btn btn-success btn-sm ">Schedule Service</button></a> &nbsp;';
                     return str;
                 },
                 "orderable": false
             }
           ]
       });
   });

   $(document).on('click','.schedule',function(){
      
      $("#schedule_alloc").hide();
      $("#schedule_show").hide();
      $("#scheduleUpdate").hide();

      var job_card_id = parseInt($(this).attr('job_card_id'));
      var ba_stime = $(this).attr('ba_stime');
      var ba_etime = $(this).attr('ba_etime');

      $.ajax({
            url:'/admin/service/floor/supervisor/get/schedule/task',
            data:{'job_card_id':job_card_id,},
            method:'GET',
            success:function(result){
               // console.log(result);
               if(Object.keys(result).length > 0)
               {
                  $("#schedule_show").show();
                  $("#schedule_show").find('.name').text(result.bay_name);
                  $("#schedule_show").find('.start_time').text(result.start_time);
                  $("#schedule_show").find('.end_time').text(result.end_time);

                  schedule_open_modal(job_card_id,ba_stime,ba_etime);

               }
               else{
                  $("#schedule_alloc").show();
                  $("#scheduleUpdate").show();
                  schedule_open_modal(job_card_id,ba_stime,ba_etime);
               }
               // console.log('result',result);
            },
            error:function(error){
               $('#js-msg-error').html("Something Wen't Wrong "+error).show();
            }
         });
      
   });

    function ShowRecording(el) {
      var id = $(el).children('i').attr('class');
      $("#audio-div").hide();
      $.ajax({  
            url:"/admin/service/final/inspection/get/"+id,  
            method:"get",  
            success:function(result){
             if(result.jobcard) {
              $(".inTime").val(result.jobcard.service_in_time);
              $(".outTime").val(result.jobcard.service_out_time);
              $(".deliveryTime").val(result.jobcard.estimated_delivery_time);
              var audio = result.jobcard.recording;
              if (audio != null) {
                $("#audio-div").show();
                $("#audio").attr("src","/upload/audio/"+audio+"");
              }
              $('#RecordingModal').modal("show");       
            } 
            } 
       });
    }

   function schedule_open_modal(job_card_id,ba_stime,ba_etime){
      
      // console.log(job_card_id,ba_stime,ba_etime);
      if(job_card_id && ba_stime && ba_etime)
      {

         ba_stime = new Date(ba_stime);
         curr_time = new Date();
         ba_etime = new Date(ba_etime);

         var res = Math.abs(ba_etime - curr_time) / 1000;

         // get hours        
         var hours = Math.floor(res / 3600) % 24;
         // get minutes
         var minutes = Math.floor(res / 60) % 60;

         hm_second = (hours+minutes)*60;

         // console.log(bay_alloc_id,res,hours,minutes,hm_second);
         if(hm_second > 0)
         {
            $("#sduration").data('durationPicker').setValue(hm_second);
         }
         // console.log(ba_etime-curr_time);
         $("#job_card_id").val(job_card_id);
         $('#scheduleModalSchedule').modal("show"); 

      }
   }

   $(document).on('click','#scheduleUpdate',function(){

      var job_card_id = $("#job_card_id").val();
      var sduration = $("#sduration").val();
      if(job_card_id && sduration)
      {
        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
         $.ajax({
            url:'/admin/service/floor/supervisor/schedule/task',
            data:{'_token':CSRF_TOKEN, 'job_card_id':job_card_id, 'sduration':sduration},
            method:'POST',
            success:function(result){
               if(result.trim() == 'success') {
                  $('#js-msg-success').html('Successfully Scheduled.').show();
                  $('#table').dataTable().api().ajax.reload();
               }else if(result.trim() == 'bay_error'){
                  $('#js-msg-error').html("Scheduled not added,because bay not free on given time.").show();
               }else{
                  $('#js-msg-error').html("Something went wrong.").show();
               }
               
            },
            error:function(error){
               $('#js-msg-error').html("Something Wen't Wrong "+error).show();
            }
         });
         $('#scheduleModalSchedule').modal("hide"); 
      }

   });


   $(document).on('click','.charges',function(e){

        var jobcard_id = $(this).attr('id');
        if(jobcard_id) {
            $.ajax({
            url:'/admin/service/floor/supervisor/jobcard',
            data:{'JobCardId':jobcard_id},
            method:'GET',
            dataType:'json',
            success:function(result){
              var data = JSON.parse(result.details);
              var value = data.charges;
              var gst = "{{ Config::get('constant.servicechargesgst')}}";
              var gstvalue = value*gst/100;
              var total = (parseFloat(gstvalue)+parseFloat(value)).toFixed(2);  
              $("#jobcardid").val(jobcard_id);
              if (total > 0) {
                $("#servicechargesvalue").html(total);
                $("#servicecharges").val(total);
              }else{
                $("#servicechargesvalue").html('0'); 
                $("#servicecharges").val('0');
              }     
               $('#js-msg-errors').hide();
            
            },
            error:function(error){
               $('#js-msg-error').html("Something Wen't Wrong "+error).show();
            }
         });

            $.ajax({
            url:'/admin/service/floor/supervisor/servicechargecheck',
            data:{'JobCardId':jobcard_id},
            method:'GET',
            dataType:'json',
            success:function(result){
              var html = '';
              if(result.length > 0){

                 $.each(result,function(key,value){
                  if(value.sub_type =='Service') {
                    $("#servicechargesvalue").html(value.amount);
                 } else if(value.sub_type =='Labour') {  
                  $("#labourchargesvalue").html(value.amount)
                  $("#labourcharges").show();
                 }

                  if(value.no_labour_charge == '1'){
                     $("#no_charge").attr('checked', true);
                        $("#append-div").hide();
                  }
                  
                if(value.sub_type != 'Service') {
                 
                  html+='<tr>'+
                        '<td>'+value.sub_type+'</td>'+
                        '<td>'+value.amount+'</td></tr>';
                        if (value.sub_type == 'Labour' ) {
                           $("#append-div").hide();
                         }else{
                           $("#append-div").show();
                         }
                }
                 $("#chargesdata").html(html);
              });
                  $("#no_labour_charge").hide();
                  $("#labourchargesvalue").show();
                  $("#servicebtn").show(0).delay(100);
                  $("#chargesdata_div").show();
                  $("#table_head").show();
                   $("#append-div").hide();
              }
              else{
                 $("#labourchargesvalue").hide();
                 $("#labourcharges").show();
                 $("#servicebtn").show();
                 $("#append-div").show();
                 $("#chargesdata").html('');
                 $("#table_head").hide();
                  
              }
            },
            error:function(error){
               $('#js-msg-error').html("Something Wen't Wrong "+error).show();
            }
         });

        }


   });

   $(document).on('click','.serviceIn',function(){
      $('#js-msg-verror').hide();
      var getJobCardId = $(this).attr('id');
      $("#jobcard_id").val(getJobCardId);
      $('#serviceModalCenter').modal("show"); 
    });

   $(document).on('click','#btnUpdate',function(){
      var getJobCardId = $("#jobcard_id").val();
      var wash_status = $("input[name='wash_status']:checked").val();
      $('#js-msg-error').hide();
      $('#js-msg-success').hide();
         $.ajax({
            url:'/admin/service/floor/supervisor/buy/work/start',
            data:{'JobCardId':getJobCardId,'wash_status':wash_status},
            method:'GET',
            success:function(result){
               if(result.trim() == 'success')
               {
                 $('#serviceModalCenter').modal("hide"); 
                  $('#js-msg-success').html('Successfully Service In.').show();
                  $('#table').dataTable().api().ajax.reload();

               }
               else if(result.trim() != 'error')
               {
                 $('#serviceModalCenter').modal("hide");
                  $('#js-msg-error').html(result).show();
               }
               else{
                  $('#js-msg-error').html("Something Wen't Wrong").show();
               }
               // console.log('result',result);
            },
            error:function(error){
               $('#js-msg-error').html("Something Wen't Wrong "+error).show();
            }
         });
      
   });

   $(document).on('click','.serviceOut',function(){
      var getJobCardId = $(this).attr('id');
      // console.log('getJobCardId',getJobCardId);
      $('#js-msg-error').hide();
      $('#js-msg-success').hide();
      var confirm1 = confirm('Are You Sure, You Want to Service Out ?');
      if(confirm1)
      {
         $.ajax({
            url:'/admin/service/floor/supervisor/buy/work/end',
            data:{'JobCardId':getJobCardId},
            method:'GET',
            success:function(result){
               if(result.trim() == 'success')
               {
                  $('#js-msg-success').html('Successfully Service Out.').show();
                  $('#table').dataTable().api().ajax.reload();
               }
               else if(result.trim() != 'error')
               {
                  $('#js-msg-error').html(result).show();
               }
               else{
                  $('#js-msg-error').html("Something Wen't Wrong").show();
               }
               // console.log('result',result);
            },
            error:function(error){
               $('#js-msg-error').html("Something Wen't Wrong "+error).show();
            }
         });
      }
      
   });

   $(document).on('click','.customer_confirmation',function(){
      var getJobCardId = $(this).attr('id');
      // console.log('getJobCardId',getJobCardId);
      $('#js-msg-error').hide();
      $('#js-msg-success').hide();
      var confirm1 = confirm('Are You Sure, You want to confirm it ?');
      if(confirm1)
      {
         $.ajax({
            url:'/admin/service/floor/supervisor/customer/confirmation',
            data:{'JobCardId':getJobCardId},
            method:'GET',
            success:function(result){
               if(result.trim() == 'success')
               {
                  $('#js-msg-success').html('Successfully confirmed.').show();
                  $('#table').dataTable().api().ajax.reload();

               }
               else if(result.trim() != 'error')
               {
                  $('#js-msg-error').html(result).show();
               }
               else{
                  $('#js-msg-error').html("Something Wen't Wrong").show();
               }
               // console.log('result',result);
            },
            error:function(error){
               $('#js-msg-error').html("Something Wen't Wrong "+error).show();
            }
         });
      }
      
   });

$("#no_charge").click(function(){
  if($('[name="no_charge"]').is(":checked")){
          $("#append-div").hide();
        }else{
            $("#append-div").show();
         }
       
});


$(document).on('click','#servicebtn',function(){
  var jobcard_id = $("#jobcardid").val();
  var servicharge = $("#servicecharges").val();
  var labelcharge = $("#labourcharges").val();
  var amount = $('.amount').serializeArray();
  var laburtitle = $('.laburtitle').serializeArray();
   if($('[name="no_charge"]').is(":checked")){
          var no_charge = $("#no_charge").val();
        }else{
            var no_charge = '';
         }
     $.ajax({
            url:'/admin/service/floor/supervisor/servicecharge',
            data:{'JobCardId':jobcard_id,'servicharge':servicharge,'amount':amount,'laburtitle':laburtitle,'no_charge':no_charge},
            method:'GET',
            success:function(result){
             
               if(result.trim() == 'success')
               {
                  $('#js-msg-success').html('Service Charges Successfully Add.').show();
                   $('#AddModalCharges').modal("hide"); 
                   $("#labourcharges").val('');
                   $(".sub-div").html('');
                   $('.amount').val('');
                   $('.laburtitle').val('');
               }
               else if(result.trim() != 'error')
               {
                  $('#js-msg-errors').html(result).show();
               }
               else{
                  $('#js-msg-errors').html("Something Wen't Wrong").show();
               }
            },
            error:function(error){
               $('#js-msg-errors').html("Something Wen't Wrong "+error).show();
            }
         });


});


$(document).on('click', '.add-more-div', function() {
    var div_count = parseInt($("#count-div").val());
    $("#count-div").val(div_count + 1);
    div_count = div_count + 1;
    // console.log('div_count', div_count);
    var ele = $('#main-div').html();
    // console.log(ele);
    var str = '<div class="row sub-div" id="sub-div-' + div_count + '">' + ele + '</div>';
    var main_div_name = ['laburtitle_0', 'amount_0','remove_0'];
    var current_sub_div_name = ['laburtitle_' + div_count, 'amount_' + div_count,'remove_' +div_count
    ];
    // console.log(main_div_name);
    str = replaceId(main_div_name, current_sub_div_name, str);
    $("#append-div").append(str);
    removeError(main_div_name, div_count);
    removeVal(current_sub_div_name, div_count);

    // $("#repair-replace_" + div_count).removeClass('select2-hidden-accessible');
    // $("#repair-replace_" + div_count).siblings('span').remove();

    // $('select').select2();
    // $('#repair-replace_' + div_count).select2({ dropdownCssClass: 'bigdrop' });


});
// replace str when need to change to id with new Id.
function replaceId(oldId, newId, str) {
    $.each(oldId, function(key, val) {
        str = str.replace(oldId[key], newId[key]);

    });
    return str;
}
//remove error label when append errro-div
function removeError(oldId, div_count) {
    $.each(oldId, function(key, val) {
        $("#sub-div-" + div_count).find('#' + oldId[key] + '-error').remove();
    });
}
//remove val in update form
function removeVal(oldId, div_count) {
    $.each(oldId, function(key, val) {
        $("#sub-div-" + div_count).find('#' + oldId[key]).val('').trigger('change');
    });
}

$(document).on('click', '.delete-div', function() {
    if ($(this).parent().parent().parent().attr('id') != 'main-div') {
        $(this).parent().parent().parent().remove();
    }
});

</script>
@endsection
@section('main_section')
<section class="content">
   <div id="app">
      <div class="row">
         <div class="alert alert-danger" id="js-msg-error">
         </div>
         <div class="alert alert-success" id="js-msg-success">
         </div>
       </div>
   <div class="box box-primary">
      <!-- /.box-header -->
      <div class="box-header with-border">
                        {{-- <h3 class="box-title">{{__('customer.mytitle')}} </h3> --}}

      </div>
      
      <div class="box-body">
         @include('admin.flash-message')
         @yield('content')
         <table id="table" class="table table-bordered table-striped">
            <thead>
               <tr>
                  <th>Tag #</th>
                  <th>Frame #</th>
                  <th>Registration #</th>
                  <th>Job Card Type</th>
                  <th>Customer Status</th>
                  <th>Vehicle K.M.</th>
                  <th>Bay Name</th>
                  <th>Bay Start Time</th>
                  <th>Bay End Time</th>
                  <th>{{__('factory.action')}}</th>
               </tr>
            </thead>
            <tbody>
            </tbody>
         </table>
      </div>
      <!-- /.box-body -->
   </div>
   <!-- Modal --> 
  <div class="modal fade" id="serviceModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content" style="margin-top: 200px!important;">
        <div class="modal-header">
          <h4 class="modal-title" id="exampleModalLongTitle">Service In</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">

       
          <form id="my-form"  method="POST" >
              <div class="alert alert-danger" id="js-msg-verror">
            </div>
            @csrf
            <input type="hidden" name="jobcard_id" id="jobcard_id">
            <div class="row">
              <div class="col-md-9">
            <label for="advance">Washed Status<sup>*</sup></label>
             <div class="col-md-3">
               <label><input autocomplete="off" type="radio" value="yes" name="wash_status"> Yes</label>
            </div>
            <div class="col-md-3">
               <label><input autocomplete="off"  type="radio" value="no" name="wash_status"> No</label>
            </div>
            
         </div>
       </div>
        </div>
        <div class="modal-footer">
          <button type="button" id="btnCancel" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="button" id="btnUpdate" class="btn btn-success">Submit</button>
        </div>
        </form>
      </div>
    </div>
  </div>
   <!-- /.box -->
   <div class="modal fade" id="AddModalCharges" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
         <div class="modal-content" style="margin-top: 200px!important;">
         <form id="my-form" method="POST" onsubmit="return false">
               @csrf
         <div class="modal-header">
            <h4 class="modal-title" id="exampleModalLongTitle">Add Labour Charges</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
               <span aria-hidden="true">&times;</span>
            </button>
            <div class="alert alert-danger" id="js-msg-errors">
            </div>
         </div>
         <div class="modal-body">
            <div class="row">
               <div class="col-md-6">
                     <label>Service Charge </label>
                     <label>Labour Charge </label>
               </div>
               <div class="col-md-6">
                        <span id="servicechargesvalue"></span>
                        <input type="hidden" name="jobcardid" id="jobcardid" value="
                        ">
                        <input type="hidden" name="servicecharges" id="servicecharges" value="
                        ">
               </div>
            </div>
            <div id="chargesdata_div" class="row">
               <table id="table_data" class="table table-bordered table-striped">
                     <thead id="table_head">
                     <tr>
                        <th>Title</th>                                                
                        <th>Amount</th>              
                     </tr>
                     </thead>
                     <tbody id="chargesdata">
                        
                     </tbody>
                  
                     </table>
            </div><br>
          <div class="col-md-6" id="no_labour_charge">
            <label>No Labour Charge</label>
              <input type="checkbox" name="no_charge" id="no_charge" value="1">
          </div>
            <div class="col-md-12 margin" id="append-div">

                  <input type="hidden" name="count-div" value="0" hidden id="count-div">
                  <div class="col-md-12 margin">
                  <div class="col-md-6 text-center">
                     <label>Title </label>
                  </div>
                  <div class="col-md-6 text-center">
                     <label>Amount </label>
                  </div>
               </div>
            <div class="row" id="main-div">
               <div class="col-md-2">
                           <label>
                              <i class="fa fa-trash color-action margin-r-5 delete-div remove_0" ></i>
                              <i class="fa fa-plus margin add-more-div" style="color:blue;cursor: pointer;" ></i>
                           </label>
               </div>
               <div class="col-md-5">
                     <span id="labourchargesvalue"></span>
                        <input type="text"  name="laburtitle[]" id="laburtitle_0" class="input-css inputValidation laburtitle" >
                           {!! $errors->first('laburtitle', '<p class="help-block">:message</p>') !!}
               </div>
               <div class="col-md-5">
                     <input type="number" min="1" name="amount[]" id="amount_0" class="input-css inputValidation amount" >
                           {!! $errors->first('amount', '<p class="help-block">:message</p>') !!}
               </div>
            </div>
               </div>
         </div>
         <div class="modal-footer">
            <button type="button"  class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="button" id="servicebtn" class="btn btn-success">Submit</button>
         </div>
         </form>
         </div>
      </div>
   </div>

   <div class="modal fade" id="scheduleModalSchedule" tabindex="-1" role="dialog" aria-labelledby="exampleModalScheduleTitle" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="margin-top: 200px!important;">
          <div class="modal-header">
            <h4 class="modal-title" id="exampleModalLongTitle">Schedule Service</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
               <div class="alert alert-danger" id="js-msg-serror" style="display:none">
               </div>
              @csrf
              <input type="hidden" hidden name="job_card_id" id="job_card_id">
              <div class="row" id="schedule_show" style="display:none">
                  <div class="col-md-2">
                     <label>Bay</label>
                     <label class="name"></label>
                  </div>
                  <div class="col-md-5">
                     <label>Start Time</label>
                     <label class="start_time"></label>
                  </div>
                  <div class="col-md-5">
                     <label>End Time</label>
                     <label class="end_time"></label>
                  </div>
              </div>
               <div class="row" id="schedule_alloc" style="display:none">
                  <div class="col-md-12">
                     <label for="advance">Duration <sup>*</sup></label>
                     <div class="col-md-12">
                        <input type="text" name="sduration" id="sduration"  class="input-css duration-picker" >
                     </div>
                  </div>
               </div>
         </div>
         <div class="modal-footer">
            <button type="button" id="scheduleUpdateCancel" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="button" id="scheduleUpdate" class="btn btn-success">Submit</button>
         </div>
         
        </div>
      </div>
    </div>

    <div class="modal fade" id="RecordingModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content" style="margin-top: 200px!important;">
        <div class="modal-header">
          <h4 class="modal-title" id="exampleModalLongTitle">Show Details</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
              <div class="row">
                <div class="col-md-6">
                  <label>In Time</label>
                  <input type="text" class="inTime input-css">
                </div>
                 <div class="col-md-6">
                  <label>Out Time</label>
                  <input type="text" class="outTime input-css">
                </div>
                 <div class="col-md-6">
                  <label>Delivery Time</label>
                  <input type="text" class="deliveryTime input-css">
                </div><br>
                  <div class="col-md-12" id="audio-div">
                    <label class="lable">Recording</label>
                    <audio controls="controls" preload="metadata" id="audio">
                    </audio>
                  </div>
              </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>


</section>
@endsection