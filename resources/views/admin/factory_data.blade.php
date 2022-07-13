@extends($layout)

@section('title', __('factory.title_view'))

@section('breadcrumb')
    <li><a href="#"><i class=""></i>Factory Summary</a></li>
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
          "ajax": "/admin/factory/list/api",
          "aaSorting": [],
          "responsive": true,
          "columns": [
              { "data": "id" },
              { "data": "name" },
              { "data": "created_at" },
              {
                  "targets": [ -1 ],
                  "data":"id", "render": function(data,type,full,meta)
                  {
                    
                      return '<a href="#"'+data+'"><button class="btn btn-success btn-xs"> Edit</button></a> &nbsp;'  ;
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
                      <th>{{__('factory.id')}}</th>
                      <th>{{__('factory.factory_name')}}</th>
                      <th>{{__('factory.created_at')}}</th>
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