@extends($layout)

@section('title', __('Salary List'))

@section('breadcrumb')
    <li><a href="#"><i class=""></i>Salary List</a></li>
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
         "pageLength": 50,
         "responsive": true,
          "ajax": {
            "url": "/admin/payroll/user/salary/list/api",
            "datatype": "json",
            },
           
          "columns": [

              { "data": "emp_id" },
              { "data": "name" },
              { "data": "phone" },
              { "data": "store_name" },
              { "data": "salary_month" },
              { "data": "type" },
              { "data": "salary" },
              { "data": "actual_salary" },
               {
                  "targets": [ -1 ],
                  "data":"id", "render": function(data,type,full,meta)
                  { 
                    var str = '<a href="/admin/payroll/user/salary/view/'+data+'" target="_blank"><button class="btn btn-success btn-xs">View</button></a>';
                       
                        return str;
                       
                  },
                  "orderable": false
              }
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
                      <th>Emp ID</th>
                      <th>Name</th>
                      <th>Mobile Number</th>                    
                      <th>Store Name</th>                    
                      <th>Salary Month</th>                   
                      <th>Type</th>                   
                      <th>Salary</th>                   
                      <th>Actual Salary</th>
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