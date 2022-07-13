@extends($layout)

@section('title', __('Task List'))

@section('breadcrumb')
    <li><a href="#"><i class=""></i>Task Summary</a></li>
@endsection
@section('css')
<link rel="stylesheet" href="/css/responsive.bootstrap.css">    
@endsection
@section('js')
<script src="/js/dataTables.responsive.js"></script>
  <script>
 var dataTable;
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
            "url": "/admin/task/list/api",
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
              { "data": "recurrence" },
              { "data": "recurrence_type" },
              {
                  "data":"status", "render": function(data,type,full,meta)
                  {
                     var str = '';

                     if(data=='0'){
                      str = 'InActive'
                     }else if(data=='1'){
                      str = 'Active'
                     }

                     return str;

                  },
                 
              },
              { "data": "every_number" },
              { "data": "week" },
              { "data": "days" },
              { "data": "month" },
              { "data": "recurrence_week" },
              { "data": "every_month_no" },
              {
                "targets": [ -1 ],
                "data":"id", "render": function(data,type,full,meta)
                  {
                      return '<button style="margin:5px;padding:3px;" data-toggle="modal" data-target="#myModal_comp_stat" data-id="'+data+'" data-status="'+full.status+'" class="btn btn-info btn-xs taskstatus">Status</button>'; 
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
        }
    });

    });

$(document).on('click','.taskstatus',function(){

var task_id=$(this).attr('data-id');
var status=$(this).attr('data-status');
console.log(status);
if(status==0){
  $("#status").val("InActive").change();
}else if(status==1){
  $("#status").val("Active").change();
}
$("#task_id").val(task_id);
});

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
            url: "/admin/task/status/update",
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
                      <th>Recurrence</th>                     
                      <th>Recurrence Type</th>                     
                      <th>Status</th>                     
                      <th>Every</th>                     
                      <th>Week</th>                     
                      <th>Days</th>                     
                      <th>Month</th>                                    
                      <th>Recurrence Week Days</th>                     
                      <th>Every Month</th>                                       
                      <th>Action</th>                                       
                    </tr>
                    </thead>
                    <tbody>
                      
                    </tbody>
               
                  </table>
                        <div id="myModal_comp_stat" class="modal fade" role="dialog">
                            <div class="modal-dialog">
                          
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
                                                <select name="status" id="status" class="input-css select2 status" required="">
                                                  <option value="">Select Status</option>
                                                  <option value="Active">Active</option>
                                                  <option value="InActive">InActive</option>
                                                </select>
                                                {!! $errors->first('status', '<p class="help-block">:message</p>') !!}
                                            </div>
                                          </div><br><br>
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