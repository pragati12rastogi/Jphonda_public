@extends($layout)
@section('title', __('Feedback List'))
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
 
   // Data Tables'additional_services.id',
   $(document).ready(function() {
     dataTable = $('#table').DataTable({
         "processing": true,
         "serverSide": true,
         "ajax": "/admin/service/feedback/list/api",
         "aaSorting": [],
         "responsive": true,
         "columns": [
             { "data": "tag"},
             { "data": "frame"},
             { "data": "registration"},
             { "data": "rating"},
             { "data": "feedback"},
             { "data":  "customer_problem" },
             { "data":  "guardname" },
             { "data":  "crmuser" }
           ]
       });
   });

</script>
@endsection
@section('main_section')
<section class="content">
   <div id="app">
      <div class="row">
   <div class="box box-primary">
      <div class="box-body">
         @include('admin.flash-message')
         @yield('content')
         <table id="table" class="table table-bordered table-striped">
            <thead>
               <tr>
                  <th>Tag #</th>
                  <th>Frame #</th>
                  <th>Registration #</th>
                  <th>Rating</th>
                  <th>Customer Feedback</th>
                  <th>Customer Problem</th>
                  <th>Guard</th>
                  <th>Crm User</th>
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