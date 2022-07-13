@extends($layout)
@section('title', __('Frame Out List'))
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
     dataTable = $('#admin_table').DataTable({
         "processing": true,
         "serverSide": true,
         "ajax": "/admin/service/entry/list/api",
         "aaSorting": [],
         "responsive": true,
         "columns": [
             { "data": "tag"},
             { "data": "frame"},
             { "data": "registration"},
             { "data": "in_time"},
             { "data": "out_time"},
             {
                 "targets": [ -1 ],
                 "data":"id", "render": function(data,type,full,meta)
                 {
                     return '';
                 },
                 "orderable": false
             }
           ]
       });
   });
</script>
@endsection
@section('main_section')
<section class="content">
   <div id="app">
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
                  <th>Time In</th>
                  <th>Time Out</th>
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
   <!-- /.box -->
</section>
@endsection