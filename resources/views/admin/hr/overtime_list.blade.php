@extends($layout)

@section('title', __('OT List'))

@section('breadcrumb')
    <li><a href="#"><i class=""></i>OT List</a></li>
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
         "pageLength": 50,
         "responsive": true,
          "ajax": {
            "url": "/admin/hr/user/overtime/list/api",
            "datatype": "json",
                "data": function (data) {
                    var store_name = $('#store_name').val();
                    data.store_name = store_name;
                    var year = $('#year').val();
                    data.year = year;
                    var month = $('#month').val();
                    data.month = month;
                    var date = $('#date').val();
                    data.date = date;
                    var storename = $('#storename').val();
                    data.storename = storename;
                },

            },
            "createdRow": function( row, data, dataIndex){
                if( data.status=="Approved"){
                  $(row).css('background-color','#73AC80');               
                }
                if(data.status=="Rejected"){
                  $(row).css('background-color','#F0A4AC');
                }
            }, 
          "columns": [
              // { "data": "id" },
              { "data": "emp_id" },
              { "data": "name" },
              {
                
                   data:function(data,type,full,meta)
                  {
                     var str = '';
                     var times=<?php echo $overtime;?>;
                      xx="";yy="";zz="";
                       $.each(times, function(i, field){
                          if((field.user_id==data.user_id) && (field.date==data.attendance_date) && (field.status)=='Approved'){
                             xx= "yes" ;  

                          }
                          else if((field.user_id==data.user_id) && (field.date==data.attendance_date) && (field.status)=='Rejected'){
                             yy= "yes" ;  

                          }else if((field.user_id==data.user_id) && (field.date==data.attendance_date) && (field.status)=='Pending'){
                             zz= "yes" ;  

                          }

                       });
                      if(xx=="yes"){

                         str= 'Approved';
                      }else if(yy=="yes"){

                         str= 'Rejected';
                      }else if(zz=="yes"){

                         str= 'Pending';
                      }else{
                          str= 'Pending';
                      }
                   
                     return str;
                  },
                 
              },
              { "data": "date" },
              { "data": "store_name" },
              { "data": "timing" },
              { "data": "duration" },
              { "data": "overtime" },
              {
                
                  data:function(data,type,full,meta)
                  {
                     var str = '';
                     var status_reporting=<?php echo $overtime;?>;
                      xx="";yy="";zz="";
                       $.each(status_reporting, function(i, field){
                          if((field.user_id==data.user_id) && (field.date==data.attendance_date) && (field.approve_rejected_by)!='0'){
                             xx= "yes";
                             yy=field.level1;  
                          }
                          
                       });
                      if(xx=="yes"){
                           str= yy;
                      }else{
                          str= '';
                      }
                   
                     return str;
                  },
                 
              },
              {
                
                  data:function(data,type,full,meta)
                  {
                     var str = '';
                     var status_reportings=<?php echo $overtime;?>;
                      xx="";yy="";zz="";
                       $.each(status_reportings, function(i, field){
                          if((field.user_id==data.user_id) && (field.date==data.attendance_date) && (field.status_reporting_by)=='Approved'){
                             xx= "yes";
                               
                          }else if((field.user_id==data.user_id) && (field.date==data.attendance_date) && (field.status_reporting_by)=='Rejected'){
                             yy= "yes";
                               
                          }
                          
                       });
                      if(xx=="yes"){
                           str='Approved';
                      }else if(yy=="yes"){
                          str= 'Rejected';
                      }else{
                          str= 'Pending';
                      }
                   
                     return str;
                  },
                 
              },
              {
                
                  data:function(data,type,full,meta)
                  {
                     var str = '';
                     var status_hr=<?php echo $overtime;?>;
                      xx="";yy="";zz="";
                       $.each(status_hr, function(i, field){
                          if((field.user_id==data.user_id) && (field.date==data.attendance_date) && (field.approve_rejected_by_hr)!='0'){
                             xx= "yes";
                             yy=field.level2;  
                          }
                          
                       });
                      if(xx=="yes"){
                           str= yy;
                      }else{
                          str= '';
                      }
                   
                     return str;
                  },
                 
              },
              {
                
                  data:function(data,type,full,meta)
                  {
                     var str = '';
                     var hr_reportings=<?php echo $overtime;?>;
                      xx="";yy="";zz="";
                       $.each(hr_reportings, function(i, field){
                          if((field.user_id==data.user_id) && (field.date==data.attendance_date) && (field.status_hr_by)=='Approved'){
                             xx= "yes";
                               
                          }else if((field.user_id==data.user_id) && (field.date==data.attendance_date) && (field.status_hr_by)=='Rejected'){
                             yy= "yes";
                               
                          }
                          
                       });
                      if(xx=="yes"){
                           str='Approved';
                      }else if(yy=="yes"){
                          str= 'Rejected';
                      }else{
                          str= 'Pending';
                      }
                   
                     return str;
                  },
                 
              },
              {
                  "targets": [ -1 ],
                  data:function(data,type,full,meta)
                  {
                    var x="no";
                    var i=0;
                    var str='';

                   var auth="{{Auth::id()}}";
                   var role= "{{Auth::user()->role}}";
                   var repoting=data.reporting;
                   var date=data.attendance_date;
                   var overtime=data.overtime;
                   var time=<?php echo $overtime;?>;
                   x="";y="";r="";h="";

                    $.each(time, function(i, field){
                      if((field.user_id==data.user_id) && (field.date==data.attendance_date) && (field.status)=='Approved'){
                             x= "yes" ;  

                      }else if((field.user_id==data.user_id) && (field.date==data.attendance_date) && (field.status)=='Rejected'){
                            y= "yes" ;  
                      }
                      else if((field.user_id==data.user_id) && (field.date==data.attendance_date) && (field.status_reporting_by)=='Approved'){
                           
                            if((field.user_id==data.user_id) && (field.date==data.attendance_date) && (field.status)=='Pending' && (role=='HRDManager')){
                                 h= "yes" ;  
                          
                            }else{
                               r= "yes" ; 
                            }

                      }

                    });

                   if(x=="yes"){

                         str= '<b>Approved</b>';
                      }
                    else if(y=="yes"){

                         str= '<b>Rejected</b>';
                      }
                    else if(r=="yes"){

                         str= 'You Are Not Eligible To Approve Or Reject.';
                      }
                    else if(h=="yes"){

                          str= '<button id="hr" data-hr_id="'+auth+'" data-date="'+date+'" data-user_id="'+data.user_id+'" data-repoting="'+repoting+'" data-hr="'+i+'" data-overtime="'+overtime+'"class="btn btn-primary btn-xs"> Approve</button>'; 
                      }
                      else{
                        
                       if(repoting==auth){
                          if(role=='HRDManager'){
                             str= '<button id="hr" data-hr_id="'+auth+'" data-date="'+date+'" data-user_id="'+data.user_id+'" data-repoting="'+repoting+'" data-hr="'+i+'" data-overtime="'+overtime+'"class="btn btn-primary btn-xs">Approve</button>';   
                           }else{
                             str= '<button id="repoting" data-id="" data-date="'+date+'" data-user_id="'+data.user_id+'" data-repoting="'+repoting+'" data-overtime="'+overtime+'"class="btn btn-primary btn-xs"> Approve</button>';
                           }
                        }
                        else if(role=='HRDManager'){

                          str= '<button id="hr" data-hr_id="'+auth+'" data-date="'+date+'" data-user_id="'+data.user_id+'" data-repoting="'+repoting+'" data-hr="'+i+'" data-overtime="'+overtime+'"class="btn btn-primary btn-xs">Approve</button>';                   
                         }else {
                           str= "You Are Not Eligible To Approve Or Reject.";
                         }

                      }

                    return str;  
                  },
                  "orderable": false
              }
            ]
       });
}

       $('#year').on('change', function () {
          datatablefn();
                });
       $('#month').on('change', function () {
          datatablefn();
                });
       $('.datepicker3').on('change', function () {
          datatablefn();
                });

// Data Tables'additional_services.id',
   $(document).ready(function() {
    datatablefn();
 $('.timepicker').wickedpicker({
      twentyFour:true,
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

 $('.datepicker3').datepicker({
   format: "yyyy-mm-dd",
   autoclose: true
});

  });
   $('#store_name').on( 'change', function () {
      dataTable.draw();
    });
   $('#storename').on( 'change', function () {
      dataTable.draw();
    });

 $(document).on('click','#repoting',function(){
      var date=$(this).attr('data-date');
      var user_id=$(this).attr('data-user_id');
      var repoting=$(this).attr('data-repoting');
      var overtime=$(this).attr('data-overtime');
        cancel_alert_dailog1(date,user_id,repoting,overtime);
  });

 $(document).on('click','#hr',function(){
      var date=$(this).attr('data-date');
      var user_id=$(this).attr('data-user_id');
      var hr_id=$(this).attr('data-hr_id');
      var overtime=$(this).attr('data-overtime');
        cancel_alert_dailog(hr_id,user_id,date,overtime);
  });


function cancel_alert_dailog(hr_id,user_id,date,overtime)
    {
      $('#modal_div').empty().append(
            '<div id="myModal" class="modal fade" role="dialog">'+
              '<div class="modal-dialog modal-lg">'+
                '<!-- Modal content-->'+
                '<div class="modal-content">'+
                  '<div class="modal-header">'+
                    '<button type="button" class="close" data-dismiss="modal">&times;</button>'+
                    '<h4 class="modal-title">Approval/Rejection</h4>'+
                  '</div>'+
                  '<form id="infos" method="POST" action="/admin/hr/user/overtime/approve">'+
                    '@csrf'+
                    '<div class="modal-body">'+
                      '<input type="hidden" id="user_id" name="user_id" value="'+user_id+'">'+
                      '<input type="hidden" id="hr_id" name="hr_id" value="'+hr_id+'">'+
                       '<input type="hidden" id="date" name="date" value="'+date+'">'+
                      '<input type="hidden" id="overtime" name="overtime" value="'+overtime+'">'+
                      '<br><label>Please select Below OPTIONS for OT Application</label>'+
                      '<label> <input name="status" type="radio" value="Approved" required> Approved.</label>'+
                      '<label> <input name="status" type="radio" value="Rejected" required> Rejected.</label>'+
                      '<label id="status-error" class="error" for="status"></label>'+
                      '<br><br><input type="text" name="remark" class="input-css" placeholder="Please Enter Remark">'+
                    '</div>'+
                    '<div class="modal-footer">'+
                      '<button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>'+
                      '<a target="_blank"><button type="submit" class="btn btn-success"  onclick="$(\'#infos\').validate();">Submit</button></a>'+
                    '</div>'+
                    '</form>'+
                '</div>'+
              '</div>'+
            '</div>'
      );
          $(document).find('#myModal').modal("show"); 
  }

function cancel_alert_dailog1(date,user_id,repoting,overtime)
    {

      $('#modal_div').empty().append(
            '<div id="myModal" class="modal fade" role="dialog">'+
              '<div class="modal-dialog modal-lg">'+
                '<!-- Modal content-->'+
                '<div class="modal-content">'+
                  '<div class="modal-header">'+
                    '<button type="button" class="close" data-dismiss="modal">&times;</button>'+
                    '<h4 class="modal-title">Approval/Rejection</h4>'+
                  '</div>'+
                  '<form id="infos" method="POST" action="/admin/hr/user/overtime/approve">'+
                    '@csrf'+
                    '<div class="modal-body">'+
                      '<input type="hidden" id="user_id" name="user_id" value="'+user_id+'">'+
                      '<input type="hidden" id="repoting" name="repoting"  value="'+repoting+'">'+
                      '<input type="hidden" id="date" name="date" value="'+date+'">'+
                      '<input type="hidden" id="overtime" name="overtime" value="'+overtime+'">'+
                      '<br><label>Please select Below OPTIONS for OT Application</label>'+
                      '<label> <input name="status1" type="radio" value="Approved" required> Approved.</label>'+
                      '<label> <input name="status1" type="radio" value="Rejected" required> Rejected.</label>'+
                      '<label id="status-error" class="error" for="status"></label>'+
                      '<br><br><input type="text" name="remark" class="input-css" placeholder="Please Enter Remark">'+
                    '</div>'+
                    '<div class="modal-footer">'+
                      '<button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>'+
                      '<a target="_blank"><button type="submit" class="btn btn-success"  onclick="$(\'#infos\').validate();">Submit</button></a>'+
                    '</div>'+
                    '</form>'+
                '</div>'+
              '</div>'+
            '</div>'
      );
          $(document).find('#myModal').modal("show"); 
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
             <div id="modal_div"></div>
            <div class="box-header with-border">
                </div>  
                <div class="box-body">

                <div class="row">
                   <div class="col-md-2" >
                            <label>Select Year</label>
                            <input type="text" name="year" id="year" class="input-css" autocomplete="off">
                    </div>
                    <div class="col-md-2" >
                            <label>Select Month</label>
                            <input type="text" name="month" id="month" class="input-css" autocomplete="off">
                    </div>
                     <div class="col-md-2">
                            <label>Date</label>
                            <input type="text" name="date" id="date" class=" datepicker3 input-css" autocomplete="off">
                        </div>
                    <div class="col-md-3">
                       <label>User Name</label>
                       @if(count($users) > 1)
                          <select name="store_name" class="input-css select2 selectValidation" id="store_name">
                             <option value="">Select Users</option>
                             @foreach($users as $key => $val)
                             <option value="{{$val['id']}}"> {{$val['name']}} {{$val['middle_name']}} {{$val['last_name']}} </option>
                             @endforeach
                          </select>
                       @endif
                       {!! $errors->first('store_name', '
                       <p class="help-block">:message</p>
                       ') !!}
                    </div>
                    <div class="col-md-3" style="{{(count($store) == 1)? 'display:none' : 'display:block'}};float: right;">
                       <label>Store Name</label>
                       @if(count($store) > 1)
                          <select name="storename" class="input-css select2 selectValidation" id="storename">
                             <option value="">Select Store</option>
                             @foreach($store as $key => $val)
                             <option value="{{$val['id']}}" {{(old('storename') == $val['id']) ? 'selected' : '' }}> {{$val['name']}} </option>
                             @endforeach
                          </select>
                       @endif
                       @if(count($store) == 1)
                          <select name="storename" class="input-css select2 selectValidation" id="storename">
                             <option value="{{$store[0]['id']}}" selected>{{$store[0]['name']}}</option>
                          </select>
                       @endif
                       {!! $errors->first('storename', '
                       <p class="help-block">:message</p>
                       ') !!}
                    </div>
                </div>
                  <table id="admin_table" class="table table-bordered table-striped">
                    <thead>
                    <tr>
                      <th>Employee Id</th>
                      <th>Name</th>
                      <th>Status</th>
                      <th>Month </th>
                      <th>Store Name</th>
                      <th>Shift Working Hours</th>
                      <th>Total Time</th>
                      <th>Over Time</th>
                      <th>Approved By Reporting Manager</th>
                      <th>Reporting Manager Status </th>
                      <th>Approved By HR </th>
                      <th>HR Status </th>
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
      </section>
@endsection