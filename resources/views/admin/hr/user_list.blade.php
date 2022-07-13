@extends($layout)

@section('title', __('User List'))

@section('breadcrumb')
    <li><a href="#"><i class=""></i>User List</a></li>
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
         "pageLength": 100,
         "responsive": true,
          "ajax": {
            "url": "/admin/hr/user/list/api",
            "datatype": "json",
                "data": function (data) {
                    var store_name = $('#store_name').val();
                    data.store_name = store_name;
                    var status = $('#status').val();
                    data.status = status;
                }
            },
            
          "columns": [
              { "data": "emp_id" },
              { "data": "name" },
              { "data": "dob" },
              { "data": "relation_type" },
              { "data": "relation_name" },
              { "data": "sname" },
              { "data": "city_name" },
              {
                  "targets": [ -1 ],
                  "data":"status", "render": function(data,type,full,meta)
                  {
                     var str = '';

                     if(data=='0'){
                      str = 'Inactive'
                     }else if(data=='1'){
                      str = 'Active'
                     }else if(data=='2'){
                      str = 'Resigned'
                     }

                     return str;

                  },
                 
              },
              { "data": "alias_name" },
              { "data": "aadhar" },
              { "data": "pancard" },
              { "data": "doj" },
              {
                  "targets": [ -1 ],
                  "data":"id", "render": function(data,type,full,meta)
                  { 
                    var str = '';
                        str = str+'<a href="/admin/hr/user/update/'+data+'" target="_blank"><button class="btn btn-success btn-xs"> Edit</button></a>&nbsp;';
                        str = str+'<button id="print" data-id="'+data+'" class="btn btn-warning btn-xs" data-toggle="modal" data-target="#myModal_comp_nip" data-icard="'+full.icard_issue+'"> ID Card</button>&nbsp;';
                        str = str+'<a href="/admin/hr/user/monthlysalary/'+data+'" target="_blank"><button style="margin:5px;padding:3px;"class="btn btn-danger btn-xs">Monthly Salary</button></a>&nbsp;';
                         if (full.status != '2') {
                          str = str+'<button id="mark" data-id="'+full.emp_id+'" class="btn btn-success btn-xs" data-toggle="modal" data-target="#myModal_comp_done">Resignation</button>&nbsp;';
                       }
                       
                        if (full.status == '2') {
                          str = str+'<a href="/admin/hr/user/resigned/'+data+'" target="_blank"><button style="margin:5px;padding:3px;"class="btn btn-danger btn-xs">F&F</button></a><button id="rejoin" data-id="'+data+'" class="btn btn-success btn-xs" data-toggle="modal" data-target="#myModal_comp_rejoin">Rejoin</button>&nbsp;';
                       }
                        return str;
                       
                  },
                  "orderable": false
              }
            ]
       });
}

function resigneddatatablefn()
   {

    if(dataTable){
      dataTable.destroy();
    }

       dataTable = $('#resigned_table').DataTable({
         "processing": true,
         "serverSide": true,
         "ajax": "",
         "aaSorting": [],
         "pageLength": 100,
         "responsive": true,
          "ajax": {
            "url": "/admin/hr/user/resigned/list/api",
            "datatype": "json",
                "data": function (data) {
                    var store_name = $('#store_name').val();
                    data.store_name = store_name;
                    var status = $('#status').val();
                    data.status = status;
                }
            },
            
          "columns": [
              { "data": "emp_id" },
              { "data": "name" },
              { "data": "dob" },
              { "data": "relation_type" },
              { "data": "relation_name" },
              { "data": "sname" },
              { "data": "city_name" },
              {
                  "targets": [ -1 ],
                  "data":"status", "render": function(data,type,full,meta)
                  {
                     var str = '';

                     if(data=='0'){
                      str = 'Inactive'
                     }else if(data=='1'){
                      str = 'Active'
                     }else if(data=='2'){
                      str = 'Resigned'
                     }

                     return str;

                  },
                 
              },
              { "data": "alias_name" },
              { "data": "aadhar" },
              { "data": "pancard" },
              { "data": "doj" },
              {
                  "targets": [ -1 ],
                  "data":"id", "render": function(data,type,full,meta)
                  { 
                    var str = '';
                        str = str+'<a href="/admin/hr/user/update/'+data+'" target="_blank"><button class="btn btn-success btn-xs"> Edit</button></a>&nbsp;';
                        str = str+'<button id="print" data-id="'+data+'" class="btn btn-warning btn-xs" data-toggle="modal" data-target="#myModal_comp_nip" data-icard="'+full.icard_issue+'"> ID Card</button>&nbsp;';
                        str = str+'<a href="/admin/hr/user/monthlysalary/'+data+'" target="_blank"><button style="margin:5px;padding:3px;"class="btn btn-danger btn-xs">Monthly Salary</button></a>&nbsp;';
                         if (full.status != '2') {
                          str = str+'<button id="mark" data-id="'+full.emp_id+'" class="btn btn-success btn-xs" data-toggle="modal" data-target="#myModal_comp_done">Resignation</button>&nbsp;';
                       }
                       
                        if (full.status == '2') {
                          str = str+'<a href="/admin/hr/user/resigned/'+data+'" target="_blank"><button style="margin:5px;padding:3px;"class="btn btn-danger btn-xs">F&F</button></a><button id="rejoin" data-id="'+data+'" class="btn btn-success btn-xs" data-toggle="modal" data-target="#myModal_comp_rejoin">Rejoin</button>&nbsp;';
                       }
                        return str;
                       
                  },
                  "orderable": false
              }
            ]
       });
  }

function inactivedatatablefn()
   {

    if(dataTable){
      dataTable.destroy();
    }

       dataTable = $('#inactive_table').DataTable({
         "processing": true,
         "serverSide": true,
         "ajax": "",
         "aaSorting": [],
         "pageLength": 100,
         "responsive": true,
          "ajax": {
            "url": "/admin/hr/user/inactive/list/api",
            "datatype": "json",
                "data": function (data) {
                    var store_name = $('#store_name').val();
                    data.store_name = store_name;
                    var status = $('#status').val();
                    data.status = status;
                }
            },
            
          "columns": [
              { "data": "emp_id" },
              { "data": "name" },
              { "data": "dob" },
              { "data": "relation_type" },
              { "data": "relation_name" },
              { "data": "sname" },
              { "data": "city_name" },
              {
                  "targets": [ -1 ],
                  "data":"status", "render": function(data,type,full,meta)
                  {
                     var str = '';

                     if(data=='0'){
                      str = 'Inactive'
                     }else if(data=='1'){
                      str = 'Active'
                     }else if(data=='2'){
                      str = 'Resigned'
                     }

                     return str;

                  },
                 
              },
              { "data": "alias_name" },
              { "data": "aadhar" },
              { "data": "pancard" },
              { "data": "doj" },
              {
                  "targets": [ -1 ],
                  "data":"id", "render": function(data,type,full,meta)
                  { 
                    var str = '';
                        str = str+'<a href="/admin/hr/user/update/'+data+'" target="_blank"><button class="btn btn-success btn-xs"> Edit</button></a>&nbsp;';
                        str = str+'<button id="print" data-id="'+data+'" class="btn btn-warning btn-xs" data-toggle="modal" data-target="#myModal_comp_nip" data-icard="'+full.icard_issue+'"> ID Card</button>&nbsp;';
                        str = str+'<a href="/admin/hr/user/monthlysalary/'+data+'" target="_blank"><button style="margin:5px;padding:3px;"class="btn btn-danger btn-xs">Monthly Salary</button></a>&nbsp;';
                         if (full.status != '2') {
                          str = str+'<button id="mark" data-id="'+full.emp_id+'" class="btn btn-success btn-xs" data-toggle="modal" data-target="#myModal_comp_done">Resignation</button>&nbsp;';
                       }
                       
                        if (full.status == '2') {
                          str = str+'<a href="/admin/hr/user/resigned/'+data+'" target="_blank"><button style="margin:5px;padding:3px;"class="btn btn-danger btn-xs">F&F</button></a><button id="rejoin" data-id="'+data+'" class="btn btn-success btn-xs" data-toggle="modal" data-target="#myModal_comp_rejoin">Rejoin</button>&nbsp;';
                       }
                        return str;
                       
                  },
                  "orderable": false
              }
            ]
       });
  }

$("#activeuser").on('click',function(){

    $(this).addClass('btn-color-active');
    $('#inactiveuser').removeClass('btn-color-active');
    $('#resigneduser').removeClass('btn-color-active');
     $("#inactiveuser-div").hide();
     $("#inactiveuser-div").html('');
     $("#resigneduser-div").hide();
     $("#resigneduser-div").html('');
     activeuser();
    $("#activeuser-div").show();
  });

$("#inactiveuser").on('click',function(){
    $(this).addClass('btn-color-active');
    $('#activeuser').removeClass('btn-color-active');
    $('#resigneduser').removeClass('btn-color-active');
     $("#activeuser-div").hide();
     $("#activeuser-div").html('');
     $("#resigneduser-div").hide();
     $("#resigneduser-div").html('');
     inactiveuser();
     $("#inactiveuser-div").show();
  });

$("#resigneduser").on('click',function(){
    $(this).addClass('btn-color-active');
    $('#activeuser').removeClass('btn-color-active');
    $('#inactiveuser').removeClass('btn-color-active');
     $("#activeuser-div").hide();
     $("#activeuser-div").html('');
     $("#inactiveuser-div").hide();
     $("#inactiveuser-div").html('');
     resigneduser();
      $("#resigneduser-div").show();
  });

function activeuser() {
  $.ajax({
        url:'activeuser/',
        type: "GET",
        success: function(data){
             $("#activeuser-div").html(data);
               datatablefn();
        }
    });
}

function inactiveuser() {
  $.ajax({
        url:'inactiveuser/',
        type: "GET",
        success: function(data){
             $("#inactiveuser-div").html(data);
              inactivedatatablefn();
        }
    });
}

function resigneduser() {
  $.ajax({
        url:'resigneduser/',
        type: "GET",
        success: function(data){
             $("#resigneduser-div").html(data);
              resigneddatatablefn();
        }
    });
}


// Data Tables'additional_services.id',
   $(document).ready(function() {
    datatablefn();
    activeuser();
  });

$('.datepicker3').datepicker({
   format: "yyyy-mm-dd",
   autoclose: true
});

   $('#store_name').on( 'change', function () {
      dataTable.draw();
    });
    $('#status').on( 'change', function () {
      dataTable.draw();
    });
  $(document).on('click','#mark',function(){

     $("#statuss").val('').trigger('change');
     $("#comment").val('');

   $('#js-msg-errors').hide();
   $('#js-msg-successs').hide();
   var emp_id=$(this).attr('data-id');
   $("#emp_id").val(emp_id);
 
  });

  $(document).on('click','#rejoin',function(){

   $('#js-msg-erro').hide();
   $('#js-msg-succe').hide();
   $("#comment_rejoin").val('');
   $("#mistake").prop('checked', false);
   var user_id=$(this).attr('data-id');
   $("#user_id").val(user_id);
 
  });

    $(document).on('click','#print',function(){

     $('#js-msg-errora').hide();
     $('#fire_div').hide();
     $('#js-msg-succesa').hide(); 
     var id=$(this).attr('data-id');
     $("#printicard").attr("href", "/admin/hr/user/printicard/"+id);
    $("#date").val('');
    $("#fire_number").val('');
     var icard=$(this).attr('data-icard');

      if(icard!='null'){
        $('#fire_div').show();
          $("#issuecard").html('<button id="issueagain" data-id="'+id+'" class="btn btn-warning btn-xs">ID Card Issue Again</button>');
      }else{
        $('#fire_div').hide();
          $("#issuecard").html('<button id="issue" data-id="'+id+'" class="btn btn-warning btn-xs">ID Card Issue</button>');
      }

 
  });

$(document).on('click','#issue',function(){
    var user_id=$(this).attr('data-id');
      if(user_id)
      {
        $.ajax({
            url:'/admin/hr/user/icard/issue',
            data:{'userId':user_id},
            method:'GET',
            success:function(result){
               if(result.trim() == 'success')
               {
                  // $('#myModal_comp_nip').modal("hide");
                  setTimeout(function(){
                  $('#myModal_comp_nip').modal('hide')
                  }, 3000);

                  $('#js-msg-succesa').html('Successfully Id card issued').show();
                  datatablefn();
               }
               else if(result.trim() != 'error')
               {
                  $('#js-msg-errora').html(result).show();
               }
               else{
                  $('#js-msg-errors').html("Something Wen't Wrong").show();
               }
            },
            error:function(error){
               $('#js-msg-errora').html("Something Wen't Wrong "+error).show();
            }
         });
         // $('#myModal_comp_done').modal("hide"); 
      }else{
        $('#js-msg-errors').html("Status is required").show(); 
      }

   });

$(document).on('click','#issueagain',function(){
    var user_id=$(this).attr('data-id');
    var date=$("#date").val();
    var fire_number=$("#fire_number").val();
    var conf = confirm('Are You Sure , You Want to Icard Again Issue ?');
      if(conf && user_id)
      {
        $.ajax({
            url:'/admin/hr/user/icard/issueagain',
            data:{'userId':user_id,'date':date,'fire_number':fire_number},
            method:'GET',
            success:function(result){
               if(result.trim() == 'success')
               {
                 setTimeout(function(){
                  $('#myModal_comp_nip').modal('hide')
                  }, 3000);

                  // $('#myModal_comp_nip').modal("hide");
                  $('#js-msg-succesa').html('Successfully Id card issued again').show();
                  datatablefn();
               }
               else if(result.trim() != 'error')
               {
                  $('#js-msg-errora').html(result).show();
               }
               else{
                  $('#js-msg-errora').html("Something Wen't Wrong").show();
               }
            },
            error:function(error){
               $('#js-msg-errors').html("Something Wen't Wrong "+error).show();
            }
         });
         // $('#myModal_comp_done').modal("hide"); 
      }else{
        $('#js-msg-errors').html("Status is required").show(); 
      }

   });

$(document).on('click','#resignationbutton',function(){
     var emp_id=$("#emp_id").val();
     var status=$("#statuss").val();
     var comment=$("#comment").val();

      if(status && emp_id)
      {
        $.ajax({
            url:'/admin/hr/user/resignation',
            data:{'empId':emp_id,'status':status,'comment':comment},
            method:'GET',
            success:function(result){
               if(result.trim() == 'success')
               {
                 
                  $('#myModal_comp_done').modal("hide");
                  $('#js-msg-success').html('Successfully user '+status).show();
                  datatablefn();
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
         // $('#myModal_comp_done').modal("hide"); 
      }else{
        $('#js-msg-errors').html("Status is required").show(); 
      }

   });

$(document).on('click','#rejoinbutton',function(){
     var user_id=$("#user_id").val();
     var comment=$("#comment_rejoin").val();
    var mistake=$("#mistake").prop("checked");
      if(comment && emp_id)
      {
        $.ajax({
            url:'/admin/hr/user/rejoin',
            data:{'user_id':user_id,'comment':comment,'mistake':mistake},
            method:'GET',
            success:function(result){
               if(result.trim() == 'success')
               {
                 
                  $('#myModal_comp_rejoin').modal("hide");
                  $('#js-msg-success').html('Successfully User Rejoin.').show();
                  resigneddatatablefn();
               }
               else if(result.trim() != 'error')
               {
                  $('#js-msg-erro').html(result).show();
               }
               else{
                  $('#js-msg-erro').html("Something Wen't Wrong").show();
               }
            },
            error:function(error){
               $('#js-msg-erro').html("Something Wen't Wrong "+error).show();
            }
         });
      }else{
        $('#js-msg-erro').html("Comment is required").show(); 
      }

   });

$("#anchor").click(function(){
         geturl();
    });
  function geturl(){
        var store_id = $("#store_name").val();
        $("#anchor").attr("href", '/admin/export/data/user?store='+store_id);
   
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
              <div class="col-md-6">
                    <ul class="nav nav1 nav-pills">
                      <li class="nav-item">
                        <button class="nav-link1 btn-color-active" id="activeuser" >Active</button>
                      </li>
                      <li class="nav-item">
                       <button class="nav-link2" id="inactiveuser">Inactive</a></button>
                      </li>
                      <li class="nav-item">
                       <button class="nav-link3" id="resigneduser">Resigned</a></button>
                      </li>
                    </ul>
                  </div>
                   <div class="col-md-1 pull-right"><a href="/admin/export/data/user" id="anchor" class="btn btn-info ">Export</a></div>
<!--                    <h3 class="box-title">{{__('customer.mytitle')}} </h3>-->
                </div>  
                <div class="row">
                   <div class="col-md-3" style="float: right;">
                      <label>Status</label>
                       <select name="status[]" class="input-css select2 selectValidation" id="status" multiple="multiple" data-placeholder="Select Status" >
                             <option value="">Select Status</option>
                              <option value="1">Active </option>
                             <option value="0">Inactive </option>
                             <option value="2">Resigned</option>
                          </select>

                   </div>
                    <div class="col-md-3" style="{{(count($store) == 1)? 'display:none' : 'display:block'}};float: right;">
                       <label>Store Name</label>
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
                             <option value="{{$store[0]['id']}}" selected>{{$store[0]['name']}}</option>
                          </select>
                       @endif
                       {!! $errors->first('store_name', '
                       <p class="help-block">:message</p>
                       ') !!}
                    </div>

                </div>
                <div class="box-body" id="activeuser-div"></div>
                <div class="box-body" id="inactiveuser-div"></div>
                <div class="box-body" id="resigneduser-div"></div>
                <div class="box-body">
                <!-- /.box-body -->
              </div>

              <div id="myModal_comp_done" class="modal fade" role="dialog">
                            <div class="modal-dialog modal-lg">
                          
                                <!-- Modal content-->
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        <h4 class="modal-title">Add Resignation</h4>
                                    </div>
                                    <div class="modal-body">
                                       <div class="alert alert-danger" id="js-msg-errors"></div>
                                       <div class="alert alert-success" id="js-msg-successs"></div>
                                        <!-- <form action="javascript:void(0)" id="completion_mytask_form" method="post"> -->
                                          @csrf
                                          <span id="fs_err" style="color:red; display: none;"></span>
                                          <input type="text" name="emp_id" id="emp_id" hidden>
                                          <div class="row">
                                            <div class="col-md-6">
                                                <label for="">Status<sup>*</sup></label>
                                                <select name="status" id="statuss" class="input-css select2 status">
                                                  <option value="" >Select Status</option>
                                                  <option value="resignation">Resignation</option>
                                                  <option value="termination">Termination</option>
                                                </select>
                                                {!! $errors->first('status', '<p class="help-block">:message</p>') !!}
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
                                              <input type="button" id="resignationbutton" value="Update" class="btn btn-primary">&nbsp;&nbsp;
                                              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                          </div>
                                        <!-- </form> -->
                                    </div>
                
                                </div>
                            </div>
                          </div>
                        
                        <div id="myModal_comp_nip" class="modal fade" role="dialog">
                            <div class="modal-dialog modal-xs">
                          
                                <!-- Modal content-->
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        <h4 class="modal-title">Icard Details</h4>
                                    </div>
                                    <div class="modal-body">
                                        <div class="alert alert-danger" id="js-msg-errora"></div>
                                       <div class="alert alert-success" id="js-msg-succesa"></div>

                                          <div class="row">
                                            <div class="col-md-6">
                                               <a id="printicard" href="" target="_blank"><button class="btn btn-success btn-xs"> ID Card Print</button></a>
                                            </div>
                                          </div><br><br>
                                          <div class="row" id="fire_div">
                                            <div class="col-md-6">
                                                <label>Fire Number</label>
                                                <input type="text" name="fire_number" id="fire_number" class="input-css">
                                            </div>
                                             <div class="col-md-6">
                                                <label>Fire Date</label>
                                                <input type="text" name="date" id="date" class=" datepicker3 input-css" autocomplete="off">
                                            </div><br/><br/>
                                          </div>

                                          <div class="modal-footer">
                                             <span id="issuecard" style="padding:15px;"></span> <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                          </div>
                                    </div>
                
                                </div>
                            </div>
                          </div>        <!-- /.box -->
                    <div id="myModal_comp_rejoin" class="modal fade" role="dialog">
                            <div class="modal-dialog">
                          
                                <!-- Modal content-->
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        <h4 class="modal-title">Rejoin</h4>
                                    </div>
                                    <div class="modal-body">
                                       <div class="alert alert-danger" id="js-msg-erro"></div>
                                       <div class="alert alert-success" id="js-msg-succe"></div>
                                          <input type="text" name="user_id" id="user_id" hidden>

                                          <div class="row">
                                              <div class="col-md-6">
                                                <label for="">Comment</label>
                                                <textarea id="comment_rejoin" name="comment_rejoin" class="comment_rejoin input-css" ></textarea>
                                              </div>
                                          </div><br>
                                          <div class="row">
                                            <div class="col-md-6">
                                              <label>Resigned By Mistake</label>
                                            <input type="radio" name="mistake" id="mistake" value="1"> Yes 
                                            </div>
                                        </div><br>
                                          <div class="modal-footer">
                                              <input type="button" id="rejoinbutton" value="Submit" class="btn btn-primary">&nbsp;&nbsp;
                                              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                          </div>
                                    </div>
                
                                </div>
                            </div>
                          </div>
      </section>
@endsection