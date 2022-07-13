@extends($layout)

@section('title', __('Task Detail List'))

@section('breadcrumb')
    <li><a href="#"><i class=""></i>Task Detail Summary</a></li>
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
            "url": "/admin/task/detail/list/api",
            "datatype": "json",
                "data": function (data) {
                },

            },
            
          "columns": [
              { "data": "task_name" },
              { "data": "name" },
              { "data": "assignedfrom" },
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
                       var auth="{{Auth::id()}}";
                       // var todaydate ="{{date('Y-m-d')}}";
                       var user_type="{{Auth::user()->user_type}}";
                       var str ='<button id="mark" data-id="'+data+'" class="btn btn-success btn-xs">Done</button>&nbsp;';

                     if(full.status!='Completed'){ 

                       if(full.status=='Reassign' || full.status=='Pending'){
                         str = str+'<button style="margin:5px;padding:3px;" data-toggle="modal" data-target="#myModal_comp_stat" data-id="'+data+'" data-status="'+full.status+'" class="btn btn-info btn-xs review"> Review</button>'  ;

                          return str;
                       }else if(full.status=='Done'){
                          if((full.user_id!=auth) && (user_type!='user')){
                            str = '<button style="margin:5px;padding:3px;" data-toggle="modal" data-target="#myModal_comp_stat" data-id="'+data+'" data-status="'+full.status+'" class="btn btn-info btn-xs review"> Review</button>'  ;
                             return str;
                          }else{
                            return str='';
                          }
                       }else if(full.status=='Cancel'){
                          return str='';
                       }else if(full.status=='Closed'){
                          return str='';
                       }else{
                        return '<button style="margin:5px;padding:3px;" data-toggle="modal" data-target="#myModal_comp_stat" data-id="'+data+'" data-status="'+full.status+'" class="btn btn-info btn-xs review"> Review</button>';                         
                        }
                     }else{
                        return str='';
                     }
                  },
                  "orderable": false
              }
            ]
       });
  }
    $(document).ready(function() {
      datatablefn();
       
       $('#completion_st_form').validate({ // initialize the plugin
        rules: {

            status: {
                required: true
            },
             comment: {
                required: function (el) {
                    return $(el).closest('form').find('.status').val() == 'reassign';
                }
            },
            
        }
    });
  
    });

 $(document).on('click','#mark',function(){

    $('#js-msg-error').hide();
    $('#js-msg-success').hide();

       var task_id=$(this).attr('data-id');
       var confirm1 = confirm('Are You Sure, You want to confirm Status Done ?');
       if(confirm1){
         
         $.ajax({
            url:'/admin/task/detail/confirmation',
            data:{'taskId':task_id},
            method:'GET',
            success:function(result){
               if(result.trim() == 'success')
               {
                  $('#js-msg-success').html('Successfully Status Done.').show();
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
            },
            error:function(error){
               $('#js-msg-error').html("Something Wen't Wrong "+error).show();
            }
         });
       }
 });

$(document).on('click','.review',function(){

var task_id=$(this).attr('data-id');
var status=$(this).attr('data-status');
if(status=='Pending'){ 
  $('#status').html('<option value="completed">Completed</option><option value="cancel">Cancel</option>');

}else{
  $('#status').html('<option value="completed">Completed</option><option value="reassign">Reassign</option><option value="cancel">Cancel</option>');
}
$("#task_id").val(task_id);

});


  function show_as_stat(){ 
    var status = $("#status").val();
    if(status == 'completed'){
      $("#reassign_div").hide();
      $("#comment").removeAttr('required');
    }else if(status == 'reassign'){
      $("#reassign_div").show();
      $("#comment").attr('required','required');
    }else{
      $("#reassign_div").hide();
      $("#comment").removeAttr('required');
    }
  }
 $('#myModal_comp_stat').on('hidden.bs.modal', function(){
    $(this).find('form')[0].reset();
    $(".select2").val('').trigger("change");
  });

   $('#completion_st_form').submit(function(e){     

    e.preventDefault();
    var formvalidation=$("#completion_st_form").valid();
    var formData = new FormData(this);
    
      if(formvalidation==true)
      {
        $('#ajax_loader_div').css('display','block');
        $.ajax({
            type:'POST',
            url: "/admin/task/review/add",
            data: formData,
            cache:false,
            contentType: false,
            processData: false,
            success:function(result) {
            // debugger;
              $('#ajax_loader_div').css('display','none');
              if((result.error).length > 0){
                $("#fs_err").text(result.error).show();
                setTimeout(function() { 
                    $('#fs_err').fadeOut('fast'); 
                }, 8000);
              }else if((result.msg).length > 0){
                 $('#myModal_comp_stat').modal('hide');
                datatablefn();
                 $(".goodmsg").show();
                 $("#mesg").text(result.msg);
              }
            }
          });
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
                      <th>Assigned From</th>                     
                      <th>Priority</th>                                                   
                      <th>Task Date</th>                     
                      <th>Status</th>                                       
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
                                        <h4 class="modal-title">Add Status</h4>
                                    </div>
                                    <div class="modal-body">
                                        <form action="javascript:void(0)" id="completion_st_form" method="post">
                                          @csrf
                                          <span id="fs_err" style="color:red; display: none;"></span>
                                          <input type="text" name="task_id" id="task_id" hidden>
                                          <div class="row">
                                            <div class="col-md-6 {{ $errors->has('status') ? 'has-error' : ''}}">
                                                <label for="">Status<sup>*</sup></label>
                                                <select name="status" id="status" class="input-css select2 status" onchange="show_as_stat()" required="">
                                                  <option value="">Select Status</option>
                                                  <!-- <option value="completed">Completed</option>
                                                  <option value="reassign">Reassign</option> -->
                                                </select>
                                                {!! $errors->first('status', '<p class="help-block">:message</p>') !!}
                                            </div>
                                          </div><br><br>
                                          <div class="row" id="reassign_div" style="display: none;">
                                              <div class="col-md-6 {{ $errors->has('comment') ? 'has-error' : ''}}">
                                                <label for="">Comment<sup>*</sup></label>
                                                <textarea id="comment" name="comment" class="comment input-css" ></textarea>
                                                {!! $errors->first('comment', '<p class="help-block">:message</p>') !!}
                                              </div>
                                          </div><br>
                                          <div class="modal-footer">
                                              <input type="submit" value="Update" class="btn btn-primary">&nbsp;&nbsp;
                                              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
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