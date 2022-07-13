@extends($layout)

@section('title', __('Employee Task List'))

@section('breadcrumb')
    <li><a href="#"><i class=""></i>Employee Task Summary</a></li>
@endsection
@section('css')
<link rel="stylesheet" href="/css/responsive.bootstrap.css">    
@endsection
@section('js')
<script src="/js/dataTables.responsive.js"></script>
  <script>
 var dataTable;
 $('#js-msg-error').hide();
 $('#js-msg-success').hide();
  
  function datatablefn()
   {

    if(dataTable){
      dataTable.destroy();
    }

      dataTable = $('#admin_table').DataTable({
         "processing": true,
         "serverSide": true,
         "ajax": "",
         "aaSorting": [],
         "pageLength": 10,
         "responsive": true,
          "ajax": {
            "url": "/admin/employee/task/list/api",
            "datatype": "json",
                "data": function (data) {
                },

            },
            
          "columns": [
              { "data": "task_name" },
              { "data": "name" },
              {
                  "data":"priority", "render": function(data,type,full,meta)
                  {
                     var str = '';

                     if(data=='Low'){
                      str = '<span style="color:#CCCC00;">'+data+'</span>'
                     }else if(data=='Medium'){
                      str = '<span style="color:orange;">'+data+'</span>'
                     }else if(data=='High'){
                      str = '<span style="color:red;">'+data+'</span>'
                     }

                     return str;

                  },
                 
              },
              { "data": "recurrence" },
              {
                "targets": [ -1 ],
                "data":"id", "render": function(data,type,full,meta)
                  {
                     var str ='<button style="margin:5px;padding:3px;" data-toggle="modal" data-target="#myModal_comp_stat" data-id="'+data+'" data-status="'+full.status+'" data-task="'+full.task_name+'" data-assign_to="'+full.name+'" data-priority="'+full.priority+'" data-date="'+full.task_date+'" data-time="'+full.task_time+'" data-recurrence="'+full.recurrence+'" data-recurrence_type="'+full.recurrence_type+'" data-every_number="'+full.every_number+'" data-recurrence_week="'+full.recurrence_week+'" data-every_month_no="'+full.every_month_no+'" data-week="'+full.week+'" data-days="'+full.days+'" data-month="'+full.month+'" data-status="'+full.status+'" class="btn btn-info btn-xs taskstatus">Detail</button>&nbsp;';
                       // if(full.recurrence_status!=1){
                       //      str = str+'<button id="stop_recurrence" data-id="'+data+'" class="btn btn-success btn-xs">Stop Recurrence</button>';
                       // }
                       return str;  
                  },
                  "orderable": false
              }
            ]
       });
  }

$(document).ready(function() {
        datatablefn();
    });

 $(document).on('click','#stop_recurrence',function(){

    $('#js-msg-error').hide();
    $('#js-msg-success').hide();

       var task_id=$(this).attr('data-id');
       // var confirm1 = confirm('Are You Sure, You want to confirm Recurrence Stop ?');
       if(task_id){
         
         $.ajax({
            url:'/admin/employee/task/recurrence/stop',
            data:{'taskId':task_id},
            method:'GET',
            success:function(result){
               if(result.trim() == 'success')
               {
                  $('#js-msg-success').html('Successfully Recurrence Stop.').show();
                  $('#admin_table').dataTable().api().ajax.reload();
                  
               }
               else if(result.trim() != 'error')
               {
                  $('#js-msg-error').html(result).show();
               }
               else{
                  $('#js-msg-error').html("Something Wen't Wrong").show();
               }
               // console.log('result',result);
               $('#myModal_comp_stat').modal("hide");
            },
            error:function(error){
               $('#js-msg-error').html("Something Wen't Wrong "+error).show();
            }
         });
       }
 });

$(document).on('click','.taskstatus',function(){
  $(".dailyevery").hide();
  $(".everyweek").hide();
  $(".everyregentask").hide();
  $(".weeklyevery").hide();
  $(".weekday").hide();
  $(".weekregen").hide();
  $(".monthevery").hide();
  $(".monthweek").hide();
  $(".monthgen").hide();
  $(".yearevery").hide();
  $(".yearweek").hide();
  $(".yeargen").hide();
  $(".completeby").hide();
  $(".recurrencecheck").show();

var task_id=$(this).attr('data-id');
var status=$(this).attr('data-status');
var task=$(this).attr('data-task');
var assign_to=$(this).attr('data-assign_to');
var priority=$(this).attr('data-priority');
var date=$(this).attr('data-date');
var time=$(this).attr('data-time');
var recurrence=$(this).attr('data-recurrence');
var recurrence_type=$(this).attr('data-recurrence_type');
var every_number=$(this).attr('data-every_number');
var recurrence_week=$(this).attr('data-recurrence_week');
var every_month_no=$(this).attr('data-every_month_no');
var week=$(this).attr('data-week');
var days=$(this).attr('data-days');
var month=$(this).attr('data-month');
$("#stop_recurrence").attr("data-id",task_id);

 $("#daily-every-days").val('');
 $("#daily-regenerate-every-days").val('');
 $("#weekly-every-days").val('');
 $("#weekly-regenerate-every-days").val('');
 $("#monthly-every-days").val('');
 $("#monthly-every-month").val('');
 $("#monthly-every-month-days").val('');
 $("#monthly-regenerate-every-days").val('');
 $("#yearly-month-name").val('').change();
 $("#monthly-week").val('').change();
 $("#monthly-days").val('').change();
 $("#yearly-week").val('').change();
 $("#yearly-days").val('').change();
 $("#yearly-month-week").val('').change();
 $("#yearly-every-day").val('');

 $(".weekday").find('input:checkbox').prop("checked", false);

if(recurrence=='Daily'){
    if(recurrence_type=='EveryDays'){
       $("#everydays").prop("checked", true);
       $("#daily-every-days").val(every_number);
       $(".dailyevery").show();
       
    }else if(recurrence_type=='EveryWeekDay'){
      $("#everyweekday").prop("checked", true);
      $(".everyweek").show();
  
    }else if(recurrence_type='Regenerate'){
      $("#everyregenerate").prop("checked", true);
      $("#daily-regenerate-every-days").val(every_number);
       $(".everyregentask").show();
    }
  $("#recurrence_daily").show();
  $("#recurrence_weekly").hide();
  $("#recurrence_monthly").hide();
  $("#recurrence_yearly").hide();

}else if(recurrence=='Weekly'){
  if(recurrence_type=='EveryWeekOn'){
    if(recurrence_week!=null){
     
      var values = recurrence_week.split(',');
      $(".weekday").find('[value=' + values.join('], [value=') + ']').prop("checked", true);
    }
   $("#everyweekon").prop("checked", true);
   $("#weekly-every-days").val(every_number);
   $(".weeklyevery").show();
   $(".weekday").show();

  }else if(recurrence_type=='Regenerate'){
     $("#weekly_regenerate").prop("checked", true);
     $("#weekly-regenerate-every-days").val(every_number);
     $(".weekregen").show();

  }
  $("#recurrence_daily").hide();
  $("#recurrence_weekly").show();
  $("#recurrence_monthly").hide();
  $("#recurrence_yearly").hide();

}else if(recurrence=='Monthly'){

  if(recurrence_type=='Day'){
   $("#everymonth").prop("checked", true);
   $("#monthly-every-days").val(every_number);
   $("#monthly-every-month").val(every_month_no);
   $(".monthevery").show();

  }else if(recurrence_type=='SelectedWeek'){
      $("#monthly-Week").prop("checked", true);
      $("#monthly-week").val(week).change();
      $("#monthly-days").val(days).change();
      $("#monthly-every-month-days").val(every_month_no);
      $(".monthweek").show();       
         
  }else if(recurrence_type=='Regenerate'){
      $("#monthly-regenerate").prop("checked", true);
      $("#monthly-regenerate-every-days").val(every_number);
      $(".monthgen").show();

  }
  $("#recurrence_daily").hide();
  $("#recurrence_weekly").hide();
  $("#recurrence_yearly").hide();
  $("#recurrence_monthly").show();

}else if(recurrence=='Yearly'){
  if(recurrence_type=='Every'){
    $("#every_year").prop("checked", true);
    $("#yearly-month-name").val(month).change();
    $("#yearly-every-day").val(every_number);
    $(".yearevery").show();
       
    
  }else if(recurrence_type=='SelectedWeek'){
    $("#year-week").prop("checked", true);
    $("#yearly-week").val(week).change();
    $("#yearly-days").val(days).change();
    $("#yearly-month-week").val(month).change();
    $(".yearweek").show();
      

  }else if(recurrence_type=='Regenerate'){
    $("#regenerate-year").prop("checked", true);
    $("#yearly-regenerate-every-days").val(every_number);
    $(".yeargen").show();
  }
  $("#recurrence_daily").hide();
  $("#recurrence_weekly").hide();
  $("#recurrence_monthly").hide();
  $("#recurrence_yearly").show();
}else{
   $(".completeby").show();
   $(".recurrencecheck").hide();

}

$("#task_id").val(task_id);
$(".task_name").text(task);
$("#assign_to").text(assign_to);
$("#priority").text(priority);
$("#date").text(date);
$("#time").text(time);
$("#recurrence").text(recurrence);

});

</script>
@endsection

@section('main_section')
    <section class="content">
            <div id="app">
                    @include('admin.flash-message')
                    @yield('content')
            <div class="alert alert-success alert-block goodmsg" style="display: none;">
              <button type="button" class="close" data-dismiss="alert">×</button> 
                    <strong id="mesg"></strong>
            </div>
        <!-- Default box -->
        <div class="row">
         <div class="alert alert-danger" id="js-msg-error">
         </div>
         <div class="alert alert-success" id="js-msg-success">
         </div>
       </div>
        <div class="box box-primary">
            <!-- /.box-header -->
            <div class="box-header with-border">
<!--                    <h3 class="box-title">{{__('customer.mytitle')}} </h3>-->
                </div>  
                <div class="box-body">
                  <div class="row">
                    <div class="col-md-4" style="float: right;">
                    @include('layouts.taskpriority_tab')
                    </div>
                  </div>
                  <table id="admin_table" class="table table-bordered table-striped">
                    <thead>
                    <tr>
                      <th>Task</th>                     
                      <th>Assigned To</th>                     
                      <th>Priority</th>                     
                      <th>Recurrence</th>                                                            
                      <th>Action</th>                                       
                    </tr>
                    </thead>
                    <tbody>
                      
                    </tbody>
               
                  </table>
                        <div id="myModal_comp_stat" class="modal fade" role="dialog">
                            <div class="modal-dialog modal-lg">
                          
                                <!-- Modal content-->
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        <h4 class="modal-title">Show Details</h4>
                                    </div>
                                    <div class="modal-body">
                                        <form action="javascript:void(0)" id="completion_st_form" method="post">
                                          @csrf
                                          <span id="fs_err" style="color:red; display: none;"></span>
                                          <input type="text" name="task_id" id="task_id" hidden>
                                          <div class="row">
                                           <div class="col-md-12">
                                            <div class="col-md-5">
                                               <label>Task</label>
                                              <textarea class="input-css task_name"  readonly name="task" rows="3"></textarea>
                                          </div>
                                          <div class="col-md-7">
                                            <div class="row recurrencecheck">
                                              <label>Recurrence</label>
                                              <p id="recurrence"></p>
                                             </div>
                                          <div class="row" id="recurrence_daily">
                                            <div class="col-md-12 dailyevery">
                                              <div class="col-md-3">
                                                 <input type="radio" class="daily-every" id="everydays" name="daily-every"  value="EveryDays">Every 
                                               </div>
                                               <div class="col-md-2">
                                                   <input id="daily-every-days" type="number" readonly name="daily-every-days" class="input-css">
                                               </div>
                                               <div class="col-md-7">
                                                <label>day(s) </label>
                                               </div>
                                            </div>
                                           <div class="col-md-12 everyweek">
                                             <div class="col-md-12">
                                               <input type="radio" class="daily-every" name="daily-every" id="everyweekday" value="EveryWeekDay">On Working Day 
                                             </div>
                                           </div>
                                           <div class="col-md-12 everyregentask">
                                             <div class="col-md-5">
                                               <input type="radio" class="daily-every" name="daily-every" id="everyregenerate" value="Regenerate">Regenerate new task
                                             </div>
                                             <div class="col-md-2">
                                                 <input id="daily-regenerate-every-days" type="number" name="daily-regenerate-every-days" readonly class="input-css ">
                                             </div>
                                             <div class="col-md-5">
                                              <label>day(s) after each task is completed</label>
                                             </div>
                                           </div>
                                         </div>

                                        <div class="row" id="recurrence_weekly">
                                          <div class="col-md-12 weeklyevery">
                                            <div class="col-md-3">
                                               <input type="radio" readonly class="weekly-every" name="weekly-every" id="everyweekon" value="EveryWeekOn">Every 
                                             </div>
                                             <div class="col-md-2">
                                                 <input id="weekly-every-days" readonly type="number" name="weekly-every-days" class="input-css">
                                             </div>
                                             <div class="col-md-7">
                                              <label>week(s) on </label>
                                             </div>
                                          </div>
                                           <div class="col-md-12 weekday">
                                             <div class="col-md-4">                         
                                               <input type="checkbox" name="weekday[]" value="Monday">Monday 
                                             </div>
                                             <div class="col-md-4">
                                               <input type="checkbox" name="weekday[]" value="Tuesday">Tuesday 
                                             </div>
                                             <div class="col-md-4">
                                               <input type="checkbox" name="weekday[]" value="Wednesday">Wednesday 
                                             </div>
                                           </div>
                                           <div class="col-md-12 weekday">
                                            <div class="col-md-4">
                                               <input type="checkbox" name="weekday[]" value="Thursday">Thursday 
                                             </div>
                                             <div class="col-md-4">
                                               <input type="checkbox" name="weekday[]" value="Friday">Friday 
                                             </div>
                                             <div class="col-md-4">
                                               <input type="checkbox" name="weekday[]" value="Saturday">Saturday 
                                             </div>
                                           </div>
                                          <div class="col-md-12 weekregen">
                                             <div class="col-md-5">
                                               <input type="radio" readonly class="weekly-every" name="weekly-every" id="weekly_regenerate">Regenerate new task
                                             </div>
                                             <div class="col-md-2">
                                                <input id="weekly-regenerate-every-days" readonly type="number" name="weekly-regenerate-every-days" class="input-css">
                                             </div>
                                             <div class="col-md-5">
                                              <label>week(s) after each task is completed</label>
                                             </div>
                                           </div>
                                         </div>
                                      <div class="row" id="recurrence_monthly">
                                          <div class="col-md-12 monthevery">
                                            <div class="col-md-2">
                                               <input type="radio" id="everymonth" class="monthly-every" name="monthly-every" value="Day">Day
                                             </div>
                                             <div class="col-md-2">
                                                 <input id="monthly-every-days" readonly type="number" name="monthly-every-days" class="input-css">
                                             </div>
                                             <div class="col-md-3">
                                              <label>on every</label>
                                             </div>
                                             <div class="col-md-2">
                                               <input id="monthly-every-month" readonly type="number" name="monthly-every-month" class="input-css">
                                             </div>
                                             <div class="col-md-2">
                                              <label>month(s)</label>
                                             </div>
                                          </div>
                                          <div class="col-md-12 monthweek">
                                            <div class="col-md-2">
                                               <input type="radio" class="monthly-every" name="monthly-every" id="monthly-Week" value="SelectedWeek">The
                                             </div>
                                             <div class="col-md-3">
                                              <select name="monthly-week" id="monthly-week" class="input-css select2">
                                              <option value="">Select Week</option>
                                              <option value="First">First</option>
                                              <option value="Second">Second</option>
                                              <option value="Third">Third</option>
                                              <option value="Fourth">Fourth</option>
                                              <option value="Fifth">Fifth</option>
                                          </select>
                                             </div>
                                             <div class="col-md-3">
                                                <select name="monthly-days" id="monthly-days" class="input-css select2">
                                                  <option value="">Select Days</option>
                                                  <option value="Monday">Monday</option>
                                                  <option value="Tuesday">Tuesday</option>
                                                  <option value="Wednesday">Wednesday</option>
                                                  <option value="Thursday">Thursday</option>
                                                  <option value="Friday">Friday</option>
                                                  <option value="Saturday">Saturday</option>
                                              </select>
                                          </div>
                                            <div class="col-md-1">
                                              <label>on every</label>
                                             </div>
                                             <div class="col-md-2">
                                               <input id="monthly-every-month-days" readonly type="number" name="monthly-every-month-days" class="input-css">
                                             </div>
                                             <div class="col-md-1">
                                              <label>month(s)</label>
                                             </div>
                                        </div>
                                         <div class="col-md-12 monthgen">
                                           <div class="col-md-5">
                                             <input type="radio" class="monthly-every" name="monthly-every" id="monthly-regenerate" value="Regenerate">Regenerate new task
                                           </div>
                                           <div class="col-md-2">
                                               <input id="monthly-regenerate-every-days" type="number" name="monthly-regenerate-every-days" class="input-css">
                                           </div>
                                           <div class="col-md-5">
                                            <label>month(s) after each task is completed</label>
                                           </div>
                                         </div>
                                      </div>
                                     <div class="row" id="recurrence_yearly">
                                      <div class="col-md-12 yearevery">
                                          <div class="col-md-3">
                                             <input type="radio" id="every_year" class="yearly-every" name="yearly-every" value="Every">Every
                                           </div>
                                           <div class="col-md-4">
                                            <select name="yearly-month-name" id="yearly-month-name" class="input-css select2">
                                              <option value="">Select Month</option>
                                              <option value="January">January</option>
                                              <option value="February">February</option>
                                              <option value="March">March</option>
                                              <option value="April">April</option>
                                              <option value="May">May </option>
                                              <option value="June">June</option>
                                              <option value="July">July</option>
                                              <option value="August">August</option>
                                              <option value="September">September</option>
                                              <option value="October">October</option>
                                              <option value="November">November</option>
                                              <option value="December">December</option>
                                           </select>
                                           </div>
                                           <div class="col-md-2">
                                             <input id="yearly-every-day" type="number" name="yearly-every-day" class="input-css">
                                           </div>
                                        </div>
                                        <div class="col-md-12 yearweek" style="margin-top:20px;">
                                          <div class="col-md-2">
                                             <input type="radio" class="yearly-every" name="yearly-every" id="year-week" value="SelectedWeek">The
                                           </div>
                                           <div class="col-md-3">
                                            <select name="yearly-week" id="yearly-week" class="input-css select2">
                                            <option value="">Select Week</option>
                                            <option value="First">First</option>
                                            <option value="Second">Second</option>
                                            <option value="Third">Third</option>
                                            <option value="Fourth">Fourth</option>
                                            <option value="Fifth">Fifth</option>
                                        </select>
                                        </div>
                                           <div class="col-md-3">
                                              <select name="yearly-days" id="yearly-days" class="input-css select2">
                                                <option value="">Select Days</option>
                                                <option value="Monday">Monday</option>
                                                <option value="Tuesday">Tuesday</option>
                                                <option value="Wednesday">Wednesday</option>
                                                <option value="Thursday">Thursday</option>
                                                <option value="Friday">Friday</option>
                                                <option value="Saturday">Saturday</option>
                                            </select>
                                          </div>
                                          <div class="col-md-1">
                                            <label>of</label>
                                           </div>
                                           <div class="col-md-3">
                                            <select name="yearly-month-week" id="yearly-month-week" class="input-css select2">
                                                <option value="">Select Month</option>
                                                <option value="January" >January</option>
                                                <option value="February">February</option>
                                                <option value="March">March</option>
                                                <option value="April">April</option>
                                                <option value="May">May </option>
                                                <option value="June">June</option>
                                                <option value="July">July</option>
                                                <option value="August">August</option>
                                                <option value="September">September</option>
                                                <option value="October">October</option>
                                                <option value="November">November</option>
                                                <option value="December">December</option>
                                            </select>                           
                                           </div>
                                      </div>
                                      <div class="col-md-12 yeargen">
                                         <div class="col-md-5">
                                           <input type="radio" class="yearly-every" name="yearly-every" id="regenerate-year" value="Regenerate">Regenerate new task
                                         </div>
                                         <div class="col-md-2">
                                             <input id="yearly-regenerate-every-days" type="number" name="yearly-regenerate-every-days" class="input-css">                        
                                         </div>
                                         <div class="col-md-5">
                                          <label>year(s) after each task is completed</label>
                                         </div>
                                       </div>
                                    </div>
                                    
                                            </div>
                                           </div>
                                          </div>
                                          <div class="row">
                                           <div class="col-md-6">
                                                <label>Assign To</label>
                                                 <p id="assign_to"></p>
                                            </div>
                                            <div class="col-md-6">
                                              <br><br>
                                            <button id="stop_recurrence" class="btn btn-success btn-sm">Stop Recurrence</button>
                                          </div>
                                           </div>
                                          <div class="row">
                                           <div class="col-md-6">
                                                <label>Priority</label>
                                                 <p id="priority"></p>
                                            </div>
                                          </div>
                                          <div class="row datetime">
                                             <div class="col-md-12 completeby">
                                              <label>Complete By</label>
                                               <div class="col-md-3">
                                                <label>Date</label>
                                                  <p id="date"></p>
                                               </div>
                                               <div class="col-md-3">
                                                 <label>Time</label>
                                                   <p id="time"></p>
                                                 </div></div>
                                             </div>
                                          <div class="modal-footer">
                                              <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                                          </div>
                                        </form>
                                    </div>
                
                                </div>
                            </div>
                          </div>
                </div>
                <!-- /.box-body -->
              </div>
        <!-- /.box -->
      </section>
@endsection