@extends($layout)

@section('title', __('Vehicle'))

@section('breadcrumb')
    <li><a href="#"><i class=""></i>Vehicle listing</a></li>
@endsection
@section('css')
<link rel="stylesheet" href="/css/responsive.bootstrap.css">    
@endsection
@section('js')
<script src="/js/dataTables.responsive.js"></script>
  <script>
    var dataTable;

    // Data Tables
    $(document).ready(function() {
      dataTable = $('#admin_table').DataTable({
          "processing": true,
          "serverSide": true,
          "ajax": "/admin/vehicle/list/api",
          "aaSorting": [],
          "responsive": true,
          "columns": [
              { "data": "model_category" },
              { "data": "model_name" },
              { "data": "model_variant" },
              { "data": "color_code" },
              {
                  "targets": [ -1 ],
                  "data":"id", "render": function(data,type,full,meta)
                  {
                    
                      return ''  ;
                  },
                  "orderable": false
              }
            ],
            "columnDefs": [
            ]
          
        });
    });
  </script>
@endsection

@section('main_section')
    <section class="content">
            <div id="app">
                    @include('admin.flash-message')
                    @yield('content')
        <!-- Default box -->
        
        <div class="box box-primary">
            <!-- /.box-header -->
            <div class="box-header with-border">
<!--                    <h3 class="box-title">{{__('customer.mytitle')}} </h3>-->
                </div>  
                <div class="box-body">

                  <table id="admin_table" class="table table-bordered table-striped">
                    <thead>
                    <tr>
                      
                      <th>Model Category</th>
                      <th>Model Name</th>
                      <th>Model Variant</th>
                      <th>Color Code</th>
                      <th>Action</th>                      
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