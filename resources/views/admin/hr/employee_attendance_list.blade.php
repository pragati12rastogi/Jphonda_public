@extends($layout)

@section('title', __('Employee Attendance List'))

@section('breadcrumb')
    <li><a href="#"><i class=""></i>Employee Attendance List</a></li>
@endsection
@section('css')
<link rel="stylesheet" href="/css/responsive.bootstrap.css"> 
<link rel="stylesheet" href="/css/style.css"> 
<style>

</style>   
@endsection
@section('js')
<script src="/js/dataTables.responsive.js"></script>
  <script>
    $(document).ready( function (){
     var bootstrapTooltip = $.fn.tooltip.noConflict();
     $.fn.bstooltip = bootstrapTooltip;
     $('.mybtn').bstooltip();
     })
    // function timeToDecimal(late_by) {
    //   if (late_by == undefined) {
    //     late_by = '00:00';
    //   }else{
    //     late_by = late_by;
    //   }
    //     var arr = late_by.split(':');
    //     var time = arr[0]+":"+arr[1];
    //     var arr = time.split(':');
    //     var dec = parseInt((arr[1]/6)*10, 10);
    //     return parseFloat(parseInt(arr[0], 10) + '.' + (dec<10?'0':'') + dec);
    // } 

    function daysdifference(firstDate, secondDate){
        var startDay = new Date(firstDate);
        var endDay = new Date(secondDate);
        var millisBetween = startDay.getTime() - endDay.getTime();
        var days = millisBetween / (1000 * 3600 * 24);
        return Math.round(Math.abs(days));
    }


     var selected=[];
     var col_length =  <?php $a_date = date('Y-m-d'); $date = new DateTime($a_date); $date->modify('last day of this month'); echo $last_day = $date->format('d'); ?>;
     var col_data = 'd';
   var dataTable;
   $('#js-msg-error').hide();
    $('#js-msg-success').hide();
    function datatablefn() {

      var str = [];
      str.push({"data":"emp_id"});
      str.push({"data":"name"});
      str.push({"data":"store_name"});
      str.push({"data":"date"});
    
      for(var i = 1 ; i <= col_length ; i++) {
        str.push({
          "data": col_data+i,
          "class":"center light-red",
          "render": function(data,type,full,meta) { 
            var shift = full.timing;
            var strdata = '';
            var status = data.split(',').shift();
            var date = data.split(",")[1];
            var intime = data.split(",")[2];
            var outtime = data.split(",")[3];
            var totaltime = data.split(",")[4];
            var late_by = data.split(",")[5];
            var early_going = data.split(",")[6];
            var halfday = data.split(",")[7];
            var check1 = data.split(",")[8];
            var check2 = data.split(",")[9];
            var check3 = data.split(",")[10];
            var check4 = data.split(",")[11];

            if (check1 != null || check2 != null || check3 != null || check4 != null) {
              
              var letspan = '';
              var check = check1+check2+check3+check4;
              var letcheck =  CheckLateEarlyGoing(check);
              var l1 = letcheck.split(",").shift();
              var l2 = letcheck.split(",")[1];
              var l3 = letcheck.split(",")[2];
              var l4 = letcheck.split(",")[3];
              if (l1 != 0) {
                letspan += '<span class="grace-period">.</span>';
              }
              if (l2 != 0) {
                letspan += '<span class="grace-period">.</span>';
              }
              if (l3 != 0) {
                letspan += '<span class="grace-period">.</span>';
              }
              if (l4 != 0) {
                letspan += '<span class="grace-period">.</span>';
              }

            }else{
                var letspan = '';
            }

            console.log(letspan);
            var d = new Date();
            var month = d.getMonth()+1;
            var day = d.getDate();
            var curdate = d.getFullYear() + '-' +
            ((''+month).length<2 ? '0' : '') + month + '-' +
            ((''+day).length<2 ? '0' : '') + day;

            var days = daysdifference(date,curdate);
                   
            if(days<=3){
              if(outtime=='00:00:00'  && intime=='00:00:00' && totaltime=='00:00:00'){
                str =status;
              }else if(intime=='00:00:00'){
                str='NIP';
              }else if(outtime=='00:00:00'){
                str='NOP';
              }else{
                str =status;
              }
              return str;
            }

            if (late_by != '00:00:00' && early_going == '00:00:00' && halfday == '0') {
                  return '<span class="mybtn late-come" title="Late Come : '+late_by+'"><div class="schedule"><span class="late-come">.</span>'+letspan+'</div><div class="col-md-3"><span class="late-come">'+status+'</span></div></span>';       
            }else if (early_going != '00:00:00' && late_by == '00:00:00' && halfday == '0') {
                return '<span class="mybtn early-going" title="Early Going : '+early_going+'"><div class="schedule"><span class="early-going">.</span>'+letspan+'</div> <div class="col-md-3"><span class="early-going">'+status+'</span></div></span>';
            }else if (halfday == '1' && late_by == '00:00:00' && early_going == '00:00:00' ){
                return '<span class="mybtn" title="Half-day"><div class="schedule"><span  class="attendance-half-day">.</span>'+letspan+'</div><div class="col-md-3"><span class="mybtn attendance-half-day">'+status+'</span></div></span>'
            }else if(late_by != '00:00:00' && early_going != '00:00:00' && halfday == '0'){
              return '<span class="mybtn late-come" title="Late Come : '+late_by+' && Early Going : '+early_going+'"><div class="schedule"><span class="late-come">.</span><span class="early-going">.</span>'+letspan+'</div><div class="col-md-3"><span>'+status+'</span></div></span>'; 
            }else if(late_by != '00:00:00' && halfday == '1' && early_going == '00:00:00'){
              return '<span class="mybtn" title="Late Come : '+late_by+' && Half-day"><div class="schedule"><span  class="attendance-half-day">.</span><span class="late-come">.</span>'+letspan+'</div><div class="col-md-3"><span >'+status+'</span></div></span>'; 
            }else if(halfday == '1' && early_going != '00:00:00' && late_by == '00:00:00'){
                return '<span class="mybtn" title=" Early Going : '+early_going+' && Half-day"><div class="schedule"><span  class="attendance-half-day">.</span><span class="early-going">.</span>'+letspan+'</div><div class="col-md-3"><span>'+status+'</span></div></span>'; 
            }else if(late_by != '00:00:00' && early_going != '00:00:00' && halfday == '1'){
                return '<span class="mybtn" title="Late Come : '+late_by+' && Early Going : '+early_going+' && Half-day"><div class="schedule"><span  class="attendance-half-day">.</span><span class="late-come">.</span><span class="early-going">.</span>'+letspan+'</div><span >'+status+'</span></span>'; 
            }else{
              return status;
            }
          },
          "orderable": false
        });
      }
      
      str.push( {
                  "targets": [ -1 ],
                  "data":"user_id", "render": function(data,type,full,meta) {
                    return ''; 
                  },
                  "orderable": false
              });

    if(dataTable){
      dataTable.destroy();
    }
        
       dataTable = $('#admin_table').DataTable({
        "scrollX": true,
         "processing": true,
         "serverSide": true,
         "ajax": "",
         "aaSorting": [],
         "pageLength": 100,
         "responsive": false,
          "ajax": {
            "url": "/admin/hr/employee/attendance/list/api",
            "datatype": "json",
                "data": function (data) {
                    var user_name = $('#user_name').val();
                    data.user_name = user_name;
                    var year = $('#year').val();
                    data.year = year;
                    var month = $('#month').val();
                    data.month = month;
                },
            },        
          "columns": str
       });
}

   $(document).ready(function() {
    datatablefn();
     $('.timepicker').wickedpicker({
      twentyFour:true,
  });
   // $( "#admin_table" ).parent().css( "overflow-x", "auto" );

  });
   $('#user_name').on( 'change', function () {
      dataTable.draw();
    });
     $('#year').datepicker({
    format: "yyyy",
    weekStart: 1,
    orientation: "bottom",
    keyboardNavigation: false,
    viewMode: "years",
    minViewMode: "years",
    autoclose: true
});
   $('#month').datepicker({
    format: "mm",
    weekStart: 1,
    orientation: "bottom",
    keyboardNavigation: false,
    viewMode: "months",
    minViewMode: "months",
    autoclose: true
});
  $('#year').on('change', function () {
    datatablefn();
          });
  $('#month').on('change', function () {
    datatablefn();
          });

    for (i = new Date().getFullYear(); i > 1900; i--) {
      $('#year').append($('<option />').val(i).html(i));
  }

  function CheckLateEarlyGoing(data){
    var str = data;
              var check1 = 0;
              var check2 = 0;
              var check3 = 0;
              var check4 = 0;
              if (str) {
                var str1 = "1";
                var str2 = "2";
                var str3 = "3";
                var str4 = "4";
                if(str.indexOf(str1) != -1){
                    check1 = "1";
                }
                if(str.indexOf(str2) != -1){
                    check2 = "2";
                }
                if(str.indexOf(str3) != -1){
                    check3 = "3";
                }
                if(str.indexOf(str4) != -1){
                    check4 = "4";
                }
              }
        var check = check1+','+check2+','+check3+','+check4;
       return check;
  }

  $("#anchor").click(function(){
         geturl();
    });
  function geturl(){
        var month = $('#month').val();
        var year = $('#year').val();
        var emp_id = $("#user_name").val();
        $("#anchor").attr("href", '/admin/export/data/employee/attendance?month='+month+'&year='+year+'&emp_id='+emp_id);
   
    }

  </script>
@endsection
@section('main_section')
<section class="content">
            <div id="app">
                    @include('admin.flash-message')
                    @yield('content')
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
                  <div class="col-md-12">
                    <div class="col-md-6"><a href="/admin/export/data/employee/attendance" id="anchor" class="btn btn-info ">Export</a></div>
                    <div class="col-md-6 color-tab">
                      <ul>
                        <li> <button class="half-day-btn"></button>&nbsp; Half Day &nbsp;&nbsp;</li>
                        <li> <button class="late-come-btn"></button>&nbsp; Late Come &nbsp;&nbsp;</li>
                        <li> <button class="early-going-btn"></button>&nbsp; Early Going &nbsp;&nbsp;</li>
                        <li> <button class="grace-period-btn"></button>&nbsp; Grace Period</li>
                      </ul>
                    </div>
                  </div>

                  <div class="col-md-3"></div>
                    <div class="col-md-3">
                       <label>User Name</label>
                       @if(count($users) > 1)
                          <select name="user_name" class="input-css select2 selectValidation" id="user_name">
                             <option value="">Select Users</option>
                             @foreach($users as $key => $val)
                             <option value="{{$val['id']}}"> {{$val['name']}} {{$val['middle_name']}} {{$val['last_name']}} </option>
                             @endforeach
                          </select>
                       @endif
                       {!! $errors->first('user_name', '
                       <p class="help-block">:message</p>
                       ') !!}
                    </div>
                    <div class="col-md-3" >
                            <label>Select Year</label>
                            <input type="text" name="year" id="year" class="input-css" autocomplete="off">
                    </div>
                    <div class="col-md-3" >
                            <label>Select Month</label>
                            <input type="text" name="month" id="month" class="input-css" autocomplete="off">
                    </div>
                </div>
                  <table id="admin_table" class="table table-bordered table-striped">
                    <thead>
                    <tr>
                      <th>Employee Id</th>
                      <th>Name</th>
                      <th>Store</th>
                      <th>Month</th>
                  <?php 
                  $a_date = date('Y-m-d');
                  $date = new DateTime($a_date);
                  $date->modify('last day of this month');
                  $last_day=$date->format('d');
                  for ($i=1; $i <= $last_day ; $i++) { 
                     echo "<th>".$i."</th>";
                  }

                ?>
                    <th>Action</th>                      
                    </tr>
                    </thead>
                    <tbody>
                      
                    </tbody>
               
                  </table>
           
                </div>
                <!-- /.box-body -->
              </div>
        <!-- /.box -->

                <div id="myModal_comp_done" class="modal fade" role="dialog">
                            <div class="modal-dialog modal-lg">
                          
                                <!-- Modal content-->
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        <h4 class="modal-title"></h4>
                                    </div>
                                    <div class="modal-body">
                                       <div class="alert alert-danger" id="js-msg-errors"></div>
                                       <div class="alert alert-success" id="js-msg-successs"></div>
                                        <!-- <form action="javascript:void(0)" id="completion_mytask_form" method="post"> -->
                                          @csrf
                                          <span id="fs_err" style="color:red; display: none;"></span>
                                          <input type="text" name="user_id" id="user_id" hidden>
                                          <input type="text" name="emp_id" id="emp_id" hidden>
                                          <input type="text" name="at_date" id="at_date" hidden>
                                          <input type="text" name="type" id="type" hidden>
                                          <div class="row">
                                            <div class="col-md-6">
                                                <label for="">Time<sup>*</sup></label>
                                                <input type="text" name="time" id="timepicker" class="input-css timepicker" value="{{old('time')}}">
                                               {!! $errors->first('time', '<p class="help-block">:message</p>') !!}
                                               
                                            </div>
                                          </div><br><br>
                                          <div class="row" id="mytask_div">
                                              <div class="col-md-6 {{ $errors->has('comment') ? 'has-error' : ''}}">
                                                <label for="">Comment</label>
                                                <textarea id="comment" name="comment" class="comment input-css" ></textarea>
                                                {!! $errors->first('comment', '<p class="help-block">:message</p>') !!}
                                              </div>
                                          </div><br>
                                          <div class="modal-footer">
                                              <input type="button" id="nopbutton" value="Submit" class="btn btn-primary">&nbsp;&nbsp;
                                              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                          </div>
                                        <!-- </form> -->
                                    </div>
                
                                </div>
                            </div>
                          </div>
              </div>
      </section>
@endsection