@extends($layout)

@section('title', __('Late Early Going List'))

@section('breadcrumb')
    <li><a href="#"><i class=""></i>Late Early Going List</a></li>
@endsection
@section('css')
<link rel="stylesheet" href="/css/responsive.bootstrap.css">  
<style>
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
    $('#js-msg-success').hide();
    function datatablefn() {

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
          'createdRow':function(row,data,dataIndex)
          {
              $(row).addClass('rowClass');
              if(data.late_by != '00:00:00') {
                  $(row).find('td:eq(6)').css('background-color', '#FF9800');
              }
              if(data.early_going != '00:00:00') {
                  $(row).find('td:eq(7)').css('background-color', '#e53935');
              }
              if(data.half_day == '1') {
                  $(row).find('td:eq(8)').css('background-color', '#29B6F6');
              }
              var str = data.late_early_status;
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
              if((data.late_by != '00:00:00' && (check1 == "1" || check3 == "3")) || (data.late_by != '00:00:00' && (data.late_early_status == "1" || data.late_early_status == "3"))) {
                  $(row).find('td:eq(6)').css('background-color', '#AB47BC');
              }
              if((data.early_going != '00:00:00' && (check2 == "2" || check4 == "4")) || (data.early_going != '00:00:00' && (data.late_early_status == "2" || data.late_early_status == "4"))) {
                   $(row).find('td:eq(7)').css('background-color', '#AB47BC');
              }
             
          },
          "rowId":"id",
          "ajax": {
            "url": "/admin/hr/user/late/earlygoing/list/api",
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
            
          "columns": [
              // { "data": "id" },
              { "data": "emp_id" },
              { "data": "name" },
              {
                  "data":"status", "render": function(data,type,full,meta)
                  {
                     var str = '';
                   if(full.days<=3){
                    if(full.outtime=='00:00:00'  && full.intime=='00:00:00' && full.totaltime=='00:00:00'){
                        str =data;
                     }else if(full.intime=='00:00:00'){

                           str='NIP';

                        }else if(full.outtime=='00:00:00'){
                          str='NOP';

                        }else{
                          str =data;
                        }

                     return str;
                   }else{

                   }
                     return data;
                  },
                 
              },
              { "data": "intime" },
              { "data": "outtime" },
              { "data": "totaltime" },
              { "data": "late_by"},
              { "data": "early_going"},
              { "data": "half_day",
                "render": function(data,type,full,meta){
                  if (data == '1') {
                     return 'Yes';
                  }else{
                     return 'No';
                  }
                }
              },
              { "data": "date" },
              { "data": "store_name" },
              {
                  "targets": [ -1 ],
                  "data":"user_id", "render": function(data,type,full,meta)
                  {

                   var str='';
                    if(full.approve=='0'){

                   	str= '<button id="approve" data-id="'+full.id+'" data-empid="'+full.emp_id+'" class="btn btn-success btn-xs" data-toggle="modal" data-target="#myModal_comp_approve">Approve</button>';
                   }
                   else{

                     str='';                     
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

 $(document).on('click','#approve',function(){

 	$('#js-msg-errors').hide();
    $('#js-msg-successs').hide();

      var id=$(this).attr('data-id');
      var empid=$(this).attr('data-empid');
      $("#ids").val(id);
  });

  $(document).on('click','#approvelbutton',function(){
      var id=$("#ids").val();
      var late='';
      var early_going='';
	    if( $("#late").is(":checked")){
	    	 late=$("#late").val();	
	    }if( $("#early_going").is(":checked")){
	    	 early_going=$("#early_going").val();	
	    }
   
      if(late || early_going){

        $.ajax({
            url:'/admin/hr/user/late/earlygoing/approve',
            data:{'id':id,'late':late,'early_going':early_going},
            method:'GET',
            success:function(result){
               if(result.trim() == 'success')
               {
               	  $('#myModal_comp_approve').modal("hide");
                  $('#js-msg-success').html('Successfully Approved').show();
                  datatablefn();
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
            }
         });
      }else{
      	$('#js-msg-errors').html('Approvel is required. ').show();
      }
  });

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
                    <div class="col-md-6"></div>
                    <div class="col-md-6 color-tab">
                      <ul>
                        <li> <button class="half-day-btn"></button>&nbsp; Half Day &nbsp;&nbsp;</li>
                        <li> <button class="late-come-btn"></button>&nbsp; Late Come &nbsp;&nbsp;</li>
                        <li> <button class="early-going-btn"></button>&nbsp; Early Going &nbsp;&nbsp;</li>
                        <li> <button class="grace-period-btn"></button>&nbsp; Grace Period</li>
                      </ul>
                    </div>
                  </div>
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
                      <th>Intime</th>
                      <th>Outtime</th>
                      <th>Totaltime</th>
                      <th>Late Coming</th>
                      <th>Early Going</th>
                      <th>Half Day</th>
                      <th>Month </th>
                      <th>Store Name</th>
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

      <div id="myModal_comp_approve" class="modal fade" role="dialog">
        <div class="modal-dialog">
      
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-titles">Approvel</h4>
                </div>
                <div class="modal-body">
                	 <div class="alert alert-danger" id="js-msg-errors"></div>
                     <div class="alert alert-success" id="js-msg-successs"></div>
                	<input type="text" name="ids" id="ids" hidden>
                      <div class="row">
                        <div class="col-md-12">
                        <label>Approvel For<sup>*</sup></label>
                        <div class="col-md-3">                          
                           <input type="checkbox" name="approvel[]" value="1" id="late"> Late 
                         </div>
                         <div class="col-md-3" >
                           <input type="checkbox" name="approvel[]" value="2" id="early_going"> Early Going 
                         </div>
                        </div>
                      </div><br><br>
                      <div class="modal-footer">
                      	<input type="button" id="approvelbutton" value="Submit" class="btn btn-primary">&nbsp;&nbsp;
                          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                      </div>
                </div>

            </div>
        </div>
      </div>
@endsection