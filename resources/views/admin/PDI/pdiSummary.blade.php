@extends($layout)
@section('title', __('PDI Summary'))
@section('breadcrumb')
<li><a href="#"><i class=""></i>PDI Summary</a></li>
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
         "ajax": "/admin/PDI/summary/api",
         "aaSorting": [],
         "responsive": true,
         "columns": [
             { "data": "frame",
                "createdCell": function (td, cellData, rowData, row, col) {
                  if (rowData.status == 'Pending') {
                    $(td).parent().addClass('dangerClass');
                  }
                  else{
                    $(td).parent().addClass('successClass');
                  }
                }
              },

     

             { "data": "model_name"},
             { "data": "model_variant" },
             { "data": "color_code" },
             { "data": "date_of_damage" },
             { "data": "damage_location" },
             { "data": "date_of_repair"},
             { "data": "repair_location"},
             { "data": "responsive_emp"},
             { "data": "desc_of_accident"},
             { "data": "status"},
             {
                 "targets": [ -1 ],
                 "data":"id", "render": function(data,type,full,meta)
                 {
                  var str = '';
                      str += '<a href="/admin/PDI/view/'+data+'">'+
                       '<button class="btn btn-info btn-xs">View</button></a> &nbsp;';
                       var val = full.hirise_invoice;
                       val = ((typeof val == 'undefined')?'':((val == null)? '': val));
                       if(full.status == 'Pending' && val.length <= 0)
                       {
                          str += '<a href="/admin/PDI/edit/'+data+'"><button class="btn btn-danger btn-xs">Edit</button></a> &nbsp;';
                       }
                       if(val.length <= 0 && full.status == 'Approve')
                       {
                          str += '<a href="/admin/PDI/update/invoice/'+data+'"><button class="btn btn-success btn-xs">Update Invoice</button></a> &nbsp;';
                       }
                       return str;
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
         <!--                    <h3 class="box-title">{{__('customer.mytitle')}} </h3>-->
      </div>
      <div class="box-body">
         @include('admin.flash-message')
         @yield('content')
         <table id="admin_table" class="table table-bordered table-striped">
            <thead>
               <tr>
                  <th>Frame</th>
                  <th>Model</th>
                  <th>Variant</th>
                  <th>Color</th>
                  <th>Date of Damage</th>
                  <th>Damage Location</th>
                  <th>Date of Repair</th>
                  <th>Repair Location</th>
                  <th>Responsible Emp.</th>
                  <th>Description</th>
                  <th>Status</th>
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