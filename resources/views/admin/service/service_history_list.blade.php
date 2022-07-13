@extends($layout)
@section('title', __('Service History List'))
@section('breadcrumb')
<li><a href="#"><i class=""></i></a></li>
@endsection
@section('css')
<link rel="stylesheet" href="/css/responsive.bootstrap.css">
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
<script>
   var dataTable;
   $('#js-msg-error').hide();
   $('#js-msg-verror').hide();
   $('#js-msg-success').hide();
   
   // Data Tables'additional_services.id',
   $(document).ready(function() {
     dataTable = $('#table').DataTable({
         "processing": true,
         "serverSide": true,
         "ajax": "/admin/service/history/list/api",
         "aaSorting": [],
         "responsive": true,
         "columns": [
             { "data": "tag"},
             { "data": "no_of_part"},
             { "data": "job_card_type"},
             { "data": "service_in_time"},
             { "data": "service_out_time"},
             { "data": "service_history"},
             {
                 "targets": [ -1 ],
                 "data":"id", "render": function(data,type,full,meta)
                 {
                    var str = '';
                    if (full.service_history == null) {
                     str = str+'<a href="#"><button class="btn btn-info btn-xs " onclick="PartWarranty(this)"><i class="'+data+'"></i>Service History</button></a>';
                    }
                    if (full.warranty_approve != 0) {
                     str = str+'<a href="/admin/service/update/part/tag/'+data+'"><button class="btn btn-info btn-xs">Update Tag</button></a>';
                   }
                     return str;
                 },
                 "orderable": false
             }
           ]
       });
   });
 function PartWarranty(el) {
      var id = $(el).children('i').attr('class');
      if(id) {
         $("#id").val(id);
         $('#serviceModalCenter').modal("show"); 
      }
    }


   $('#btnUpdate').on('click', function() {
      var id = $('#id').val();
      var history = $("input[name='history']:checked").val();
 
       $.ajax({
        method: "GET",
        url: "/admin/service/history/update",
        data: {'id':id,'history':history},
        success:function(data) {
            if (data == 'success') {
                $('#serviceModalCenter').modal("hide");    
                $('#table').dataTable().api().ajax.reload();
                $("#js-msg-success").html('Service history updated succefully !');
                $("#js-msg-success").show();
               setTimeout(function() {
                 $('#js-msg-success').fadeOut('fast');
                }, 4000);
            }if(data == 'error'){
                $('#serviceModalCenter').modal("hide");  
                $("#js-msg-error").html('Something went wrong !');
                $("#js-msg-error").show();
               setTimeout(function() {
                 $('#js-msg-error').fadeOut('fast');
                }, 4000);
            }if(data == 'verror'){ 
                $("#js-msg-verror").html('Field is require !');
                $("#js-msg-verror").show();
               setTimeout(function() {
                 $('#js-msg-verror').fadeOut('fast');
                }, 4000);
            }
        },
      });
    });
 
</script>
@endsection
@section('main_section')
<section class="content">
   <div id="app">
      <div class="row">
         <div class="alert alert-danger" id="js-msg-error">
         </div>
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
                  <th>Number of Part's</th>
                  <th>Jobcard Type</th>
                  <th>Service In Time</th>
                  <th>Service Out Time</th>
                  <th>Service History</th>
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
      <div class="modal fade" id="serviceModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="margin-top: 200px!important;">
          <div class="modal-header">
            <h4 class="modal-title" id="exampleModalLongTitle">Add Service History</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">                   
            <form id="my-form"  method="POST" >
                <div class="alert alert-danger" id="js-msg-verror">
              </div>
              @csrf
              <input type="hidden" name="id" id="id">
              <div class="row">
                <div class="col-md-6">
              <label for="advance">Service History<sup>*</sup></label>
               <input type="radio" name="history"  value="ok"> Ok   &nbsp;&nbsp; 
               <input type="radio" name="history"  value="notok"> Not Ok                      
           </div>
         </div><br>
          </div>
          <div class="modal-footer">
            <button type="button" id="btnCancel" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="button" id="btnUpdate" class="btn btn-success">Update</button>
          </div>
          </form>
        </div>
      </div>
    </div>
   <!-- /.box -->
</section>
@endsection