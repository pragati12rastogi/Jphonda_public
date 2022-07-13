@extends($layout)
@section('title', __('Warranty Job Card List'))
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
   .modal-header .close {
    margin-top: -33px;
}
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
   $('#js-msg-errors').hide();
   $('#js-msg-errorss').hide();
   $('#js-msg-success').hide();
   
   $(document).ready(function() {
    $('.timepicker').wickedpicker({
      twentyFour:true,
      //show: 19 : 31,
      //now:null
  });   

    table_data();

   });

   function table_data()
   {
     if(dataTable){
      dataTable.destroy();
    }

        dataTable = $('#table').DataTable({
         "processing": true,
         "serverSide": true,
         "ajax": "/admin/service/warranty/list/api",
         "aaSorting": [],
         "responsive": true,
         "columns": [
             { "data": "tag"},
             { "data": "frame"},
             { "data": "registration"},
             { "data": "job_card_type"},
             { "data": "customer_status"},
             { "data": "estimated_delivery_time"},
             { "data": "service_status"},
             {
                 "targets": [ -1 ],
                 "data":"id", "render": function(data,type,full,meta)
                 {
                    var str = '';
                    
                      str = str+'<a href="/admin/service/part/warranty/'+data+'" class="charges"><button class="btn btn-info btn-xs "> Warranty</button></a> &nbsp;';
                    
                     return str;
                 },
                 "orderable": false
             }
           ]
       });
   }


</script>
@endsection
@section('main_section')
<section class="content">
   <div id="app">
      <div class="row">
         <div class="alert alert-danger" id="js-msg-error" style="display: none;">
         </div>
         <div class="alert alert-success" id="js-msg-success" style="display: none;">
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
                  <th>Frame #</th>
                  <th>Registration #</th>
                  <th>Job Card Type</th>
                  <th>Customer Status</th>
                  <th>Estimated Delivery Time</th>
                  <th>Service Status</th>
                  <th>{{__('factory.action')}}</th>
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