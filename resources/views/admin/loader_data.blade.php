@extends($layout)

@section('title', __('loader.title_view'))

@section('breadcrumb')
    <li><a href="#"><i class=""></i>Loader Summary</a></li>
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
          "ajax": "/admin/loader/list/api",
          "aaSorting": [],
          "responsive": true,
          "columns": [
              { "data": "id" },
              { "data": "type" },
              { "data": "truck_number" },
              { "data": "capacity" },
              { "data": "status"},
              {
                  "targets": [ -1 ],
                  "data":"id", "render": function(data,type,full,meta)
                  {
                    
                      return '<a href="/admin/loader/update/'+data+'"><button class="btn btn-danger  btn-xs"> Update</button></a> &nbsp;'  ;
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
                      <th>{{__('loader.id')}}</th>
                      <th>{{__('loader.loader_type')}}</th>
                      <th>{{__('loader.loader_truknumber')}}</th>
                      <th>{{__('loader.loader_capacity')}}</th>
                      <th>{{__('loader.status')}}</th>
                      <th>{{__('loader.action')}}</th>                      
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