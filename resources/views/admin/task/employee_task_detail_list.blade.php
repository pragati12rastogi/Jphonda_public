@extends($layout)

@section('title', __('Employee Task Detail List'))

@section('breadcrumb')
    <li><a href="#"><i class=""></i>Employee Task Detail Summary</a></li>
@endsection
@section('css')
<link rel="stylesheet" href="/css/responsive.bootstrap.css"> 
<style>
    .content{
    padding: 30px;
  }
  .nav1>li>button {
    position: relative;
    display: block;
    padding: 10px 34px;
    background-color: white;
    margin-left: 10px;
  }

  @media (max-width: 768px)  
  {
    
    .content-header>h1 {
      display: inline-block;
      
    }
  }
  @media (max-width: 425px)  
  {
    
    .content-header>h1 {
      display: inline-block;
      
    }
  }
  .nav1>li>button.btn-color-active{
    background-color: rgb(135, 206, 250);
  }
  .nav1>li>button.btn-color-unactive{
    background-color: white;
  }
  .spanred{
    color: red;
  }

  th{
    text-align: center;
  }
  .info-color{
    padding: 7px;
    background-color: #87cefa;
  }
  /* datepicker css */

*{margin:0;padding:0;}


  </style>   
@endsection
@section('js')
<script src="/js/dataTables.responsive.js"></script>
  <script>
    var dataTable;
    $('#js-msg-error').hide();
    $('#js-msg-success').hide();
    // Data Tables

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
            "url": "/admin/employee/task/detail/list/api",
            "datatype": "json",
                "data": function (data) {
                },

            },
            
          "columns": [
              { "data": "id" },
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
              { "data": "task_date" },
              { "data": "status" },
              {
                  "targets": [ -1 ],
                "data":"id", "render": function(data,type,full,meta)
                  {
                    var str ='<button id="mark" data-id="'+data+'" class="btn btn-success btn-xs" data-toggle="modal" data-target="#myModal_comp_done">Done</button>&nbsp;';

                    if(full.status=='Completed'){
                         return str=''; 
                       }else if(full.status=='Cancel'){
                         return str=''; 
                       }else if(full.status=='Closed'){
                         return str=''; 
                       }else{
                        return str;                         
                        }
                     
                  },
                  "orderable": false
              }
            ]
       });
  }

  function assigneddatatablefn()
   {

    if(dataTable){
      dataTable.destroy();
    }

      dataTable = $('#assigned_table').DataTable({
         "processing": true,
         "serverSide": true,
         "ajax": "",
         "aaSorting": [],
         "pageLength": 10,
         "responsive": true,
          "ajax": {
            "url": "/admin/employee/assignedtask/list/api",
            "datatype": "json",
                "data": function (data) {
                },

            },
            
          "columns": [
              { "data": "id" },
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
              { "data": "task_date" },
              { "data": "status" },
              {
                  "targets": [ -1 ],
                "data":"id", "render": function(data,type,full,meta)
                  {
                    
                    var str ='<button id="review" data-id="'+data+'" class="btn btn-warning btn-xs" data-toggle="modal" data-target="#myModal_comp_review">Review</button>&nbsp;';

                    if(full.status=='Closed'){
                         return str=''; 
                       }else if(full.status=='Cancel'){
                         return str=''; 
                       }else{
                        return str;                         
                        }
                  },
                  "orderable": false
              }
            ]
       });
  }
    $(document).ready(function() {
      datatablefn();
      assigneddatatablefn();
       mytask();
});

  $(document).on('click','#mark',function(){

  $('#js-msg-errors').hide();
   $('#js-msg-successs').hide();
   var task_id=$(this).attr('data-id');
   $("#task_id").val(task_id);
 
  });

  $(document).on('click','#review',function(){

  $('#js-msg-errore').hide();
  $('#js-msg-successe').hide();
  var task_id=$(this).attr('data-id');
  $("#task_ids").val(task_id);
     $("#status").val('');
     $("#comment").val('');
 
  });

$("#assignedtask").on('click',function(){
    $(this).addClass('btn-color-active');
    $('#mytask').removeClass('btn-color-active');
     $("#mytask-div").hide();
     $("#mytask-div").html('');
     assignedtask();
  });

 $("#mytask").on('click',function(){
    $(this).addClass('btn-color-active');
    $('#assignedtask').removeClass('btn-color-active');
    $("#assigned_task_data").html('');
    $("#mytask-div").show();
      mytask();
  });

function mytask() {
  $.ajax({
        url:'mytask/',
        type: "GET",
        success: function(data){
             $("#mytask-div").html(data);
              datatablefn();
        }
    });
}
function assignedtask() {
   $.ajax({
        url:'assignedtask/',
        type: "GET",
        success: function(data){
             $("#assigned_task_data").html(data);
           assigneddatatablefn();
        }
    });
}

$(document).on('click','#mytaskbutton',function(){
     var task_id=$("#task_id").val();
     var status=$("#status").val();
     var comment=$("#comment").val();

      if(status && task_id)
      {
        $.ajax({
            url:'/admin/employee/mytask/statusupdate',
            data:{'taskId':task_id,'status':status,'comment':comment},
            method:'GET',
            success:function(result){
               if(result.trim() == 'success')
               {
                  $("#status").val('');
                  $("#comment").val('');
                  $('#myModal_comp_done').modal("hide");
                  $('#js-msg-success').html('Successfully Task Status '+status+'.').show();
                  $('#assigned_table').dataTable().api().ajax.reload();
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
$(document).on('click','#assignedtaskbutton',function(){
     var task_id=$("#task_ids").val();
     var status=$("#status").val();
     var comment=$("#comment").val();

      if(status && task_id)
      {
        $.ajax({
            url:'/admin/employee/assignedtask/statusupdate',
            data:{'taskId':task_id,'status':status,'comment':comment},
            method:'GET',
            success:function(result){
               if(result.trim() == 'success')
               {
                  $("#status").val('');
                  $("#comment").val('');
                  $('#myModal_comp_review').modal("hide");
                  $('#js-msg-success').html('Successfully Task Status '+status+'.').show();
                  $('#admin_table').dataTable().api().ajax.reload();
                  assignedtask();

               }
               else if(result.trim() != 'error')
               {
                  $('#js-msg-errore').html(result).show();
               }
               else{
                  $('#js-msg-errore').html("Something Wen't Wrong").show();
               }
            },
            error:function(error){
               $('#js-msg-errore').html("Something Wen't Wrong "+error).show();
            }
         });
         // $('#myModal_comp_done').modal("hide"); 
      }else{
        $('#js-msg-errore').html("Status is required").show(); 
      }

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
                  <div class="col-md-6">
                    <ul class="nav nav1 nav-pills">
                      <li class="nav-item">
                        <button class="nav-link1 btn-color-active" id="mytask" >My Task</button>
                      </li>
                      <li class="nav-item">
                       <button class="nav-link2" id="assignedtask" >Assigned Task</a></button>
                      </li>
                    </ul>
                  </div>
                   <div class="col-md-4" style="float: right;">
                    @include('layouts.taskpriority_tab')
                    </div>
                </div>  
                <div class="box-body" id="mytask-div">
                </div>
                <!-- /.box-body -->
                <div class="box-body" id="assigned_task_data">
                  
                </div>
              </div>
        <!-- /.box -->
      </section>
@endsection