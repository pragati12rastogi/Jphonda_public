@extends($layout)
@section('title', __('Final Inspection List'))
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
</style>
@endsection
@section('js')
<script src="/js/dataTables.responsive.js"></script>
<script src="/js/bootstrap-duration-picker.js"></script>

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

    onChanged: function (value, isInitializing) {
       
    }
  });

   var dataTable;
     $('#js-msg-error').hide();
   $('#js-msg-success').hide();

    $(document).on('change','.fi_status',function(){
      var fi_status = $("input[name='fi_status']:checked").val();
      var bay_alloc = $("input[name='bay_alloc']:checked").val();
      if(fi_status == 'ok')
      {
        $("#bay_required").hide();
        $("#duration_hour").hide();
        // $("input[name='bay_alloc']:checked").prop('checked',false);
      }
      else{
        $("#bay_required").show();
      }
    });
    $(document).on('change','.bay_alloc',function(){
      var fi_status = $("input[name='fi_status']:checked").val();
      var bay_alloc = $("input[name='bay_alloc']:checked").val();
      if(bay_alloc == 'required')
      {
        $("#duration_hour").show();
      }
      else{
        $("#duration_hour").hide();
      }
    });

    function FiApprove(el) {
      $("input[name='fi_status']:checked").prop('checked',false);
      $("input[name='bay_alloc']:checked").prop('checked',false);
      $("#bay_required").hide();
      $("#duration_hour").hide();
      $("#cheklist").hide();
      var id = $(el).children('i').attr('class');
      $.ajax({  
                url:"/admin/service/final/inspection/get/"+id,  
                method:"get",  
                success:function(result){
                  if (result.checklist.length > 0) {
                    $("#cheklist").show();
                    var datalist = result.checklist;
                      $("#cheklist").empty();
                      $("#cheklist").append('<label for="checklist">Service Check List </label><br>');
                      $.each(datalist, function(key, value) {
                         $("#cheklist").append('<input type="checkbox" class="checklist" name="checklist[]" value="'+value.id+'"> '+ value.name +'<br>');
                      });
                  }
                 if(result.jobcard) {             

                  $("#id").val(result.jobcard.id);
                  if (result.jobcard.fi_status == 'ok') {
                    $("#fi_yes").attr('checked', 'checked');
                  }if (result.jobcard.fi_status == 'notok') {
                    $("#bay_required").show();
                    $("#fi_no").attr('checked', 'checked');
                  }

                  if (result.jobcard.bay_alloc_required == 'required') {
                    $("#bay_alloc_yes").attr('checked', 'checked');
                    $("#duration_hour").show();

                  }if (result.jobcard.fi_status == 'not-required') {
                    $("#bay_alloc_no").attr('checked', 'checked');
                  }
                  
                  $('#fiModalCenter').modal("show"); 
                  }
                      
                }  
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

  function ShowRecording(el) {
      var id = $(el).children('i').attr('class');
        $("#audio-div").hide();
      $.ajax({  
            url:"/admin/service/final/inspection/get/"+id,  
            method:"get",  
            success:function(result){
             if(result.jobcard) {
              var audio = result.jobcard.recording;
              if (audio != null) {
                $("#audio-div").show();
                $("#audio").attr("src","/upload/audio/"+audio+"")
              }
              $('#RecordingModal').modal("show");       
            } 
            } 
       });
    }

    $('#btnUpdate').on('click', function() {
      var id = $('#id').val();
      var remarks = $('#remarks').val();
      var fi_status = $("input[name='fi_status']:checked").val();
      var bay_alloc = $("input[name='bay_alloc']:checked").val();
      if(fi_status == 'ok')
      {
        bay_alloc = null;
      }
      var duration = $("#estimate_hour").val();
       $.ajax({
        method: "GET",
        url: "/admin/service/final/inspection/approve",
        data: {'id':id,'remarks':remarks,'fi_status':fi_status,'bay_alloc':bay_alloc,'duration':duration},
        success:function(data) {
            if (data.type == 'success') {
                $('#fiModalCenter').modal("hide");  
                $('#table').dataTable().api().ajax.reload();
                $("#js-msg-success").html(data.msg);
                $("#js-msg-success").show();
               setTimeout(function() {
                 $('#js-msg-success').fadeOut('fast');
                }, 4000);
            }
            if (data.type == 'error') {
              $("#js-msg-error").html(data.msg);
              $("#js-msg-error").show();
              $('#fiModalCenter').modal("show");
            }
        },
      }); 
    });

    $('#btnCancel').on('click', function() {
    });
    function updateWash(el){
      var id = $(el).children('i').attr('class');
       if (confirm('Are you sure ?')) {
      $.ajax({  
                url:"/admin/service/final/inspection/wash/"+id,  
                method:"get",  
                success:function(data){ 
                 if(data) {
                   if(data == 'error')
                    {
                      $("#js-msg-error").html('Something went wrong !');
                      $("#js-msg-error").show();
                      setTimeout(function() {
                       $('#js-msg-error').fadeOut('fast');
                     }, 4000);                      
                    }else{
                      $('#table').dataTable().api().ajax.reload();
                      $("#js-msg-success").html('Washed !');
                      $("#js-msg-success").show();
                     setTimeout(function() {
                       $('#js-msg-success').fadeOut('fast');
                      }, 4000);
                      
                    }
                  }
                      
                }  
           });
    }
    }

 
   
   // Data Tables'additional_services.id',
   $(document).ready(function() {
     dataTable = $('#table').DataTable({
         "processing": true,
         "serverSide": true,
         "ajax": "/admin/service/final/inspection/list/api",
         "aaSorting": [],
         "responsive": true,
         "columns": [
             { "data": "tag"},
             { "data": "frame"},
             { "data": "registration"},
             { "data": "job_card_type"},
             { "data": "customer_status"},
             { "data": "wash"},
              { "data":  "fi_status",
              "render": function(data,type,full,meta)
                 {
                    var status = ((data == 'ok')? 'Ok' : ((data == 'notok')? 'Not Ok': ''));
                    return status;
                 },
             },
             {
                 "targets": [ -1 ],
                 "data":"id", "render": function(data,type,full,meta)
                 {
                    var str = '';
                    if (full.fi_status != 'ok') {
                      str = str+'<a href="#"><button class="btn btn-info btn-xs "  onclick="FiApprove(this)"><i class="'+data+'"></i> FI Approve</button></a>';
                    }
                    
                    if(full.wash != 'yes') {
                       str = str+'<a href="#"><button class="btn btn-primary btn-xs "  onclick="updateWash(this)"><i class="'+data+'"></i> Washed</button></a>';
                    }
                    if(full. recording != null) {
                       str = str+'<a href="#"><button class="btn btn-success btn-xs "  onclick="ShowRecording(this)"><i class="'+data+'"></i>Recording</button></a>';
                    }
                     
                     return str;
                 },
                 "orderable": false
             }
           ]
       });
   });

</script>
@endsection
@section('main_section')
<section class="content">
   <div id="app">
      <div class="row">
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
                  <th>Jobcard Type</th>
                  <th>Customer Status</th>
                  <th>Washed</th>
                  <th>FI Status</th>
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
    <div class="modal fade" id="fiModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="margin-top: 200px!important;">
          <div class="modal-header">
            <h4 class="modal-title" id="exampleModalLongTitle">Final Inspection Approval</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <form id="form-id" >
                <div class="alert alert-danger" id="js-msg-error">
              </div>
              @csrf
              <input type="hidden" name="id" id="id">
              <div class="col-md-6">
              <div class="row">
                {{-- <div class="col-md-9"> --}}
                  <label for="advance">Final Inspection Status<sup>*</sup></label>
                  <div class="col-md-6">
                    <label><input autocomplete="off" type="radio" class="fi_status" value="ok" id="fi_yes" name="fi_status"> Ok</label>
                  </div>
                  <div class="col-md-6">
                    <label><input autocomplete="off"  type="radio" class="fi_status" value="notok" id="fi_no" name="fi_status"> Not Ok</label>
                  </div>
                {{-- </div> --}}
              </div>
              <div class="row" id="bay_required" style="display:none">
                <label >Bay Allocate Required <sup>*</sup></label>
                <div class="col-md-6">
                  <label><input autocomplete="off" type="radio" class="bay_alloc" value="required" id="bay_alloc_yes" name="bay_alloc">Required</label>
                </div>
                <div class="col-md-6">
                  <label><input autocomplete="off"  type="radio" class="bay_alloc" value="not-required" id="bay_alloc_no" name="bay_alloc"> Not Required</label>
                </div>
                
              </div>
              <div class="col-md-6 margin" id="duration_hour">
                <label for="estimate_hour">Estimate Service Duration (HH:MM) <sup>*</sup></label>
                <input type="text" name="estimate_hour" id="estimate_hour"  class="input-css duration-picker" placeholder="Enter Estimate Hour">
                {!! $errors->first('estimate_hour', '<p class="help-block">:message</p>') !!}
              </div>
                <div class="col-md-12">
                    <label>Remarks <sup>*</sup></label>
                    <textarea class="input-css" name="remarks" id="remarks"></textarea>
                </div>
              </div>
              <div class="col-md-6" id="cheklist"></div>
          </div>
                <div class="modal-footer">
                  <button type="button" id="btnCancel" class="btn btn-secondary" data-dismiss="modal">Close</button>
                  <button type="submit" id="btnUpdate" class="btn btn-success">Approve</button>
                </div>
          
        </div>
      </div>
    </div>

     <div class="modal fade" id="RecordingModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content" style="margin-top: 200px!important;">
        <div class="modal-header">
          <h4 class="modal-title" id="exampleModalLongTitle">Show Recording</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
            <div class="row">
              <div class="col-md-6">
              <div class="row">
                 <div class="col-md-12" id="audio-div">
                    <label class="lable">Recording</label>
                    <audio controls="controls" preload="metadata" id="audio">
                    </audio>
                  </div>
              </div>
              </div>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
   <!-- /.box -->
</section>
@endsection