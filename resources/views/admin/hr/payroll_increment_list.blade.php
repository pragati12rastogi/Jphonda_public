@extends($layout)

@section('title', __('Payroll Increment List'))

@section('breadcrumb')
    <li><a href="#"><i class=""></i>Payroll Increment List</a></li>
@endsection
@section('css')
<link rel="stylesheet" href="/css/responsive.bootstrap.css">    
@endsection
@section('js')
<script src="/js/dataTables.responsive.js"></script>
  <script>
    var dataTable;

    function datatablefn()
   {

    if(dataTable){
      dataTable.destroy();
    }
       dataTable = $('#admin_table').DataTable({
         "processing": true,
         "serverSide": true,
         "ajax": "",
         "aaSorting": [],
         "pageLength": 10,
         "responsive": true,
          "ajax": {
            "url": "/admin/payroll/increment/list/api",
            "datatype": "json",
            },
          "columns": [
              { "data": "name" },
              { "data": "basic_salary" },
              { "data": "hra" },
              { "data": "ta" },
              { "data": "perf_allowance" },
              { "data": "others" },
              { "data": "pf" },
              { "data": "effective_month" },
              { "data": "comment" }
            ]
       });
}

// Data Tables'additional_services.id',
   $(document).ready(function() {
    datatablefn();
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
            <div id="modal_div"></div>
            <div class="box-header with-border">
                </div>  
                <div class="box-body">
                  <table id="admin_table" class="table table-bordered table-striped">
                    <thead>
                    <tr>
                      <th>Name</th>
                      <th>Basic Salary</th>
                      <th>HRA</th>  
                      <th>TA</th>  
                      <th>Performance Allownce</th>  
                      <th>Other</th>   
                      <th>Pf</th>   
                      <th>Effective Month</th> 
                      <th>Comments</th>           
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