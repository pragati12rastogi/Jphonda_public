@extends($layout)
@section('title', __('Accidental Job Card List'))
@section('breadcrumb')
<li><a href="#"><i class=""></i></a></li>
@endsection
@section('css')
<link rel="stylesheet" href="/css/responsive.bootstrap.css">
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
.wickedpicker {
       z-index: 1064 !important;
   }
</style>
@endsection
@section('js')
<script src="/js/dataTables.responsive.js"></script>
<script>
   var dataTable;
   $('#js-msg-error').hide();
   $('#js-msg-errors').hide();
   $('#js-msg-errorss').hide();
   $('#js-msg-success').hide();
   
   $(document).ready(function() {
    $('.timepicker').wickedpicker({
      twentyFour:true,
      //show: 19 : 31,
      //now:null
  });   

    table_data();

   });

   $(document).on('click','.charges',function(e){

        var jobcard_id=$(this).attr('id');
        if(jobcard_id)
        {
            $.ajax({
            url:'/admin/service/floor/supervisor/jobcard',
            data:{'JobCardId':jobcard_id},
            method:'GET',
            dataType:'json',
            success:function(result){
              var data=JSON.parse(result.details);
              var value=data.charges;
              var gst="{{ Config::get('constant.servicechargesgst')}}";
              var gstvalue = value*gst/100;
              var total=(parseFloat(gstvalue)+parseFloat(value)).toFixed(2);  
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
            
              var html='';
              if(result.length>0){
                 $.each(result,function(key,value){
                 if(value.sub_type =='Service')
                 {
                     $("#servicechargesvalue").html(value.amount);
                 }
                 else if(value.sub_type =='Labour')
                 {  
                   $("#labourchargesvalue").html(value.amount)
                    $("#labourcharges").hide();
                 }
                  
                if(value.sub_type!='Service')
                {
                 
                  html+='<tr>'+
                        '<td>'+value.sub_type+'</td>'+
                        '<td>'+value.amount+'</td></tr>';
                    
                }
                console.log(html);
                 $("#chargesdata").html(html);
                 
              });
                  $("#labourchargesvalue").show();
                  $("#servicebtn").hide(0).delay(100);
                  $("#chargesdata_div").show();
                  $("#append-div").hide();
                  $("#table_head").show();             
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
   function table_data()
   {
     if(dataTable){
      dataTable.destroy();
    }

        dataTable = $('#table').DataTable({
         "processing": true,
         "serverSide": true,
         "ajax": "/admin/accidental/jobcard/list/api",
         "aaSorting": [],
         "responsive": true,
         "columns": [
             { "data": "tag"},
             { "data": "frame"},
             { "data": "registration"},
             { "data": "job_card_type"},
             { "data": "customer_status"},
             { "data": "estimated_delivery_time"},
             { "data": "service_status"},
             {
                 "targets": [ -1 ],
                 "data":"id", "render": function(data,type,full,meta)
                 {
                    var str = '';
                    
                      str = str+'<a href="#" id="'+data+'" class="charges"><button data-toggle="modal" data-target="#jobcardModalCharges" class="btn btn-success btn-xs "> Charges</button></a> &nbsp;';

                     //  str = str+"<a href='/admin/accidental/part/request/"+full.id+"'><button class='btn btn-info btn-xs'>Part's Request</button></a> &nbsp;";

                       str = str+"<a href='/admin/accidental/part/estimation/"+full.id+"'><button class='btn btn-info btn-xs'>Part's Estimation</button></a> &nbsp;";

                        if(full.customer_confirmation != 'yes' && full.part_count > 0)
                        {
                           str = str+'<a href="#" id="'+data+'" class="customer_confirmation"><button class="btn btn-primary btn-xs ">Customer Confirmation</button></a> &nbsp;';
                        }

                        if (full.estimated_delivery_time== null && full.service_status!='done') {

                           str = str+'<a href="#" id="'+data+'" class="upadedetails"><button data-toggle="modal" data-target="#exampleModalDetails" class="btn btn-warning btn-xs "> Update Detail</button></a> &nbsp;';
                        }
                        if (full.estimated_delivery_time!= null) {
                          if(full.service_status!='start' && full.service_status!='done'){

                          str = str+'<a href="#" id="'+data+'" class="surveystart"><button class="btn btn-danger btn-xs ">Start</button></a> &nbsp;';
                         }
                        }
                        if(full.service_status=='start'){
                           str = str+'<a href="#" id="'+data+'" class="surveydone"><button class="btn btn-danger btn-xs ">Done</button></a> &nbsp;';
                        }
                    
                     return str;
                 },
                 "orderable": false
             }
           ]
       });
   }

   $(document).on('click','.upadedetails',function(){
           var jobcard_id = $(this).attr('id');
          $("#jobcard_id").val(jobcard_id);
          $('#js-msg-errorss').html("").hide();
   });

   $(document).on('click','#update_detailbtn',function(){

          var jobcard_id=$("#jobcard_id").val();
          var survey = $("input[name='survey']:checked").val();
          var estimated_date=$(".estimated_date").val();
          var estimated_time=$(".estimated_time").val();
           if (!$("#survey").prop("checked")) 
          {
             $('#js-msg-errorss').html("Survey is Required ").show();
           }
           else if(!estimated_date)
           {   
               $('#js-msg-errorss').html("Estimated Date Required ").show();
           }
            else if(!estimated_time)
           {   
               $('#js-msg-errorss').html("Estimated Time Required ").show();
           }
          else
            {
                 $.ajax({
                    url:'/admin/accidental/part/update_details',
                    data:{'jobcard_id':jobcard_id,'estimated_date':estimated_date,'estimated_time':estimated_time,'survey':survey},
                    method:'GET',
                    success:function(result){
                      // console.log(result);
                       if(result.trim() == 'success'){
                          $('#js-msg-success').html('Estimated Time Successfully Add.').show();
                           $('#table').dataTable().api().ajax.reload();
                           $('#exampleModalDetails').modal("hide"); 
                       }else if(result.trim() != 'error'){
                          $('#js-msg-errorss').html(result).show();
                       }else{
                          $('#js-msg-errorss').html("Something Wen't Wrong").show();
                       }
                       $("#survey").prop("checked", false);
                    },
                    error:function(error){
                       $('#js-msg-errorss').html("Something Wen't Wrong "+error).show();
                    }
                 });
            }
   });

$(document).on('click','.surveydone',function(){
      var jobcard_id=$(this).attr('id');

    var confirm1=confirm('Are You Sure, Service Status Done ?');
       if(confirm1)
       {
         if(jobcard_id)
         {
            $.ajax({
                url:'/admin/accidental/part/update_status',
                method:'GET',
                data:{'JobCardId':jobcard_id},
                success:function(result){
                 if(result.trim() == 'success'){
                   
                    $('#js-msg-success').html('Successfully Update Service Status Done.').show();
                     $('#table').dataTable().api().ajax.reload();
                 }
                 else if(result.trim() != 'error')
                 {
                    $('#js-msg-error').html(result).show();
                 }
                 else{
                    $('#js-msg-error').html("Something Wen't Wrong").show();
                 }
                },
                error:function(error){
                   $('#js-msg-error').html("Something Wen't Wrong "+error).show();
                },

            });
         }
     }
});

$(document).on('click','.surveystart',function(){
      var jobcard_id=$(this).attr('id');
    var confirm2=confirm('Are You Sure,Service Status Start ?');
       if(confirm2)
       {
           if(jobcard_id)
           {
              $.ajax({
                  url:'/admin/accidental/part/update_start',
                  method:'GET',
                  data:{'JobCardId':jobcard_id},
                  success:function(result){
                   if(result.trim() == 'success'){
                       $('#table').dataTable().api().ajax.reload();
                      $('#js-msg-success').html('Successfully Update Service Status Start.').show();
                   }
                   else if(result.trim() != 'error')
                   {
                      $('#js-msg-error').html(result).show();
                   }
                   else{
                      $('#js-msg-error').html("Something Wen't Wrong").show();
                   }
                  },
                  error:function(error){
                     $('#js-msg-error').html("Something Wen't Wrong "+error).show();
                  },

              });
           }
        }
});

   $(document).on('click','.serviceIn',function(){
      $('#js-msg-verror').hide();
      var getJobCardId = $(this).attr('id');
      $("#jobcard_id").val(getJobCardId);
      $('#jobcardModalCharges').modal("show"); 
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
                 $('#jobcardModalCharges').modal("hide"); 
                  $('#js-msg-sucess').html('Successfully Service In.').show();
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
                  $('#js-msg-sucess').html('Successfully Service Out.').show();
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
                  $('#js-msg-sucess').html('Successfully confirmed.').show();
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

$(document).on('click','#servicebtn',function(){
  var jobcard_id = $("#jobcardid").val();
  var servicharge = $("#servicecharges").val();
  var labelcharge = $("#labourcharges").val();

// var formData = new FormData();
//  var x = $(".amount").serializeArray();
//     $.each(x, function(i, field){
    
//        formData.append(field.name, field.value);

//     });
 
var amount = $('.amount').serializeArray();
var laburtitle = $('.laburtitle').serializeArray();
     $.ajax({
            url:'/admin/service/floor/supervisor/servicecharge',
            data:{'JobCardId':jobcard_id,'servicharge':servicharge,'amount':amount,'laburtitle':laburtitle},
            method:'GET',
            success:function(result){
             
               if(result.trim() == 'success')
               {
                  $('#js-msg-success').html('Service Charges Successfully Add.').show();
                   $('#jobcardModalCharges').modal("hide"); 
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
         <div class="alert alert-danger" id="js-msg-error" style="display: none;">
         </div>
         <div class="alert alert-success" id="js-msg-success" style="display: none;">
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
                  <th>Estimated Delivery Time</th>
                  <th>Service Status</th>
                  <th>{{__('factory.action')}}</th>
               </tr>
            </thead>
            <tbody>
            </tbody>
         </table>
      </div>
      <!-- /.box-body -->
   </div>
   <!-- /.box -->
 <div class="modal fade" id="jobcardModalCharges" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content" style="margin-top: 200px!important;">
        <form id="my-form" method="POST" onsubmit="return false">
            @csrf
        <div class="modal-header">
          <h4 class="modal-title" id="exampleModalLongTitle">Add Charges</h4>
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
<!--Drop modal-->
  <div class="modal fade" id="exampleModalDetails" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content" style="margin-top: 200px!important;">
        <form id="my-form1" method="POST" onsubmit="return false">
            @csrf
            <input type="hidden" name="jobcard_id" id="jobcard_id">
        <div class="modal-header">
          <h4 class="modal-title" id="exampleModalLongTitle">Update Details</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
          <div class="alert alert-danger" id="js-msg-errorss">
         </div>
        </div>
        <div class="modal-body">
          <div class="row">
                    <div class="col-md-2">
                        <label for="survey">Survey<sup>*</sup></label>
                      </div>
                         <div class="col-md-4">               
                            <label><input id="survey"autocomplete="off" type="radio" class="survey" value="Yes" name="survey">Yes</label>
                        </div>                    
                 </div>
           <div class="row">
             <div class="col-md-6">
                    <label>Select Estimated Date<sup>*</sup></label>
                     <input type="text" name="estimated_date" class="input-css datepicker estimated_date">
                   
              </div>
              <div class="col-md-6">
                     <label>Select Estimated Time<sup>*</sup></label>
                     <input type="text" name="estimated_time" id="timepicker" class="input-css timepicker estimated_time" >
                           
              </div>
           </div><br/><br/><br/><br/>
       </div>
        <div class="modal-footer">
          <button type="button"  class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="button" id="update_detailbtn" class="btn btn-success">Submit</button>
        </div>
        </form>
      </div>
    </div>
  </div>
</section>
@endsection