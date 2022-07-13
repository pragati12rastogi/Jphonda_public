@extends($layout)
@section('title', __('Job Card Delay List'))
@section('breadcrumb')
<li><a href="#"><i class=""></i></a></li>
@endsection
@section('css')
<link rel="stylesheet" href="/css/responsive.bootstrap.css">
@endsection
@section('js')
<script src="/js/dataTables.responsive.js"></script>
<script>
   var dataTable;
   
    function DelayComment(el) {
      var id = $(el).children('i').attr('class');
      // $.ajax({  
      //           url:"/admin/service/final/inspection/get/"+id,  
      //           method:"get",  
      //           success:function(data){
      //            if(data) {
                  $("#jobcard_id").val(id);
           //        if (data.fi_status == 'ok') {
           //          $("#fi_yes").attr('checked', 'checked');
           //        }if (data.fi_status == 'notok') {
           //          $("#fi_no").attr('checked', 'checked');
           //        }
                  $('#delayModalCenter').modal("show"); 
           //        }
                      
           //      }  
           // });
    }

    $('#btnUpdate').on('click', function() {
      var id = $('#jobcard_id').val();
      var reason = $('#delay_reason').val();
      
       $.ajax({
        method: "GET",
        url: "/admin/service/delay/comment",
        data: {'jobcard_id':id,'reason':reason},
        success:function(data) {
            if (data == 'success') {
                $('#delayModalCenter').modal("hide");  
                $('#table').dataTable().api().ajax.reload();
                $("#js-msg-success").html('Delay Comment send !');
                $("#js-msg-success").show();
               setTimeout(function() {
                 $('#js-msg-success').fadeOut('fast');
                }, 4000);
            }if(data == 'error'){
                $('#delayModalCenter').modal("hide");  
                $("#js-msg-error").html('Something went wrong !');
                $("#js-msg-error").show();
               setTimeout(function() {
                 $('#js-msg-error').fadeOut('fast');
                }, 4000);
            }
            if (data == 'verror') {
              $("#js-msg-verror").html('Field is require !');
              $("#js-msg-verror").show();
            }
        },
      });
        $("#js-msg-verror").hide();
        $('#my-form')[0].reset(); 
    });
   // Data Tables'additional_services.id',
   $(document).ready(function() {
     dataTable = $('#admin_table').DataTable({
         "processing": true,
         "serverSide": true,
         "ajax": "/admin/service/delay/list/api",
         "aaSorting": [],
         "responsive": true,
         "columns": [
             { "data": "tag"},
             { "data": "frame"},
             { "data": "registration"},
             { "data": "service_in_time"},
             { "data": "service_out_time"},
             { "data": "job_card_type"},
             { "data": "customer_status"},
             { "data": "service_status"},
             { "data": "service_duration"},
             { "data": "estimated_delivery_time"},
             {
                 "targets": [ -1 ],
                 "data":"id", "render": function(data,type,full,meta)
                 {
                  var str = '';
                   // if (full.service_status == 'pending' && full.customer_status != null) {
                      str = str+'<a href="#"><button class="btn btn-primary btn-xs "  onclick="DelayComment(this)"><i class="'+data+'"></i> Delay Comment</button></a>';
                   // }
                     return str;
                 },
                 "orderable": false
             }
           ]
       });
      $('#js-msg-error').hide();
     $('#js-msg-verror').hide();
   $('#js-msg-success').hide();
   });
</script>
@endsection
@section('main_section')
<section class="content">
   <div id="app">
    <div class="alert alert-danger" id="js-msg-error">
         </div>
         <div class="alert alert-success" id="js-msg-success">
         </div>
   <div class="box box-primary">

      <!-- /.box-header -->
      <div class="box-header with-border">
                        {{-- <h3 class="box-title">{{__('customer.mytitle')}} </h3> --}}

      </div>
      <div class="box-body">
         @include('admin.flash-message')
         @yield('content')
         <table id="admin_table" class="table table-bordered table-striped">
            <thead>
               <tr>
                  <th>Tag #</th>
                  <th>Frame #</th>
                  <th>Registration #</th>
                  <th>Service Time In</th>
                  <th>Service Time Out</th>
                  <th>Job Card Type</th>
                  <th>Customer Status</th>
                  <th>Service Status</th>
                  <th>Estimate Hour</th>
                  <th>Estimate Delivery</th>
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
              <div class="modal fade" id="delayModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                  <div class="modal-content" style="margin-top: 200px!important;">
                    <div class="modal-header">
                      <h4 class="modal-title" id="exampleModalLongTitle">Delay Comment</h4>
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                      </button>
                    </div>
                    <div class="modal-body">
                      <form id="my-form"  method="POST" >
                          <div class="alert alert-danger" id="js-msg-verror">
                        </div>
                        @csrf
                        <input type="hidden" name="jobcard_id" id="jobcard_id">
                        <div class="row">
                          <div class="col-md-9">
                            <label for="advance">Delay Reason<sup>*</sup></label>
                             <textarea class="input-css" name="delay_reason" id="delay_reason"></textarea>
                            
                          </div>
                        </div>
                    </div>
                      <div class="modal-footer">
                        <button type="button" id="btnCancel" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="button" id="btnUpdate" class="btn btn-success">Add</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
   <!-- /.box -->
</section>
@endsection