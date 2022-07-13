@extends($layout)

@section('title', __('store.title'))

@section('breadcrumb')
    <li><a href="/admin/stock/parts/list"><i class=""></i>Store List</a></li>
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
          "ajax": "/admin/store/list/api",
          "aaSorting": [],
          "responsive": true,
          "columns": [
              { "data": "name" },
              { "data": "mobile" },
              { "data": "store_type" }, 
              { "data": "city" },
              { "data": "address" },
              {
                 "targets": [ -1 ],
                 "data":"id", "render": function(data,type,full,meta)
                 {
                            
                  return '<a href="/admin/master/store/update/'+data+'" target="_blank"><button class="btn btn-success btn-xs"> Edit</button></a>&nbsp;';
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
                      <th>Name</th>
                      <th>Mobile</th>
                      <th>Store Type</th>
                      <th>City</th>
                      <th>Address</th>       
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