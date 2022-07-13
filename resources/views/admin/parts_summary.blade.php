@extends($layout)

@section('title', __('parts.title'))

@section('breadcrumb')
    <li><a href="/admin/stock/parts/list"><i class=""></i>Parts Summary</a></li>
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
          "ajax": "/admin/stock/parts/list/api",
          "aaSorting": [],
          "responsive": true,
          "columns": [
            { "data": "load_referenace_number" }, 
              { "data": "model_category" },
              { "data": "model_name" },
              { "data": "model_variant" },
              { "data": "color_code" }, 
              { "data": "part_number" },
              { "data": "type" },
              { "data": "part_type" },
              { "data": "status" }, 
              { "data": "mfh_date" },
              {
                  "targets": [ -1 ],
                  "data":"id", "render": function(data,type,full,meta)
                  {
                    
                      return "<a href='#'><button class='btn btn-primary btn-xs'> View </button></a> &nbsp;" + 
                    '<a href="#"><button class="btn btn-success btn-xs"> Edit</button></a> ' ;
                  },
                 
              }
            ],
            "columnDefs": [
              { "orderable": false, "targets": 10 },
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
                      <th>{{__('parts.refer')}}</th>
                      <th>{{__('parts.model_cat')}}</th>
                      <th>{{__('parts.model_name')}}</th>
                      <th>{{__('parts.model_var')}}</th>
                      <th>{{__('parts.color')}}</th>
                      <th>{{__('parts.part')}}</th>
                      <th>{{__('parts.type')}}</th>
                      <th>{{__('parts.part_type')}}</th>
                      <th>{{__('parts.status')}}</th>
                      <th>{{__('parts.mfh_d')}}</th>
                      <th>{{__('parts.action')}}</th>                      
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