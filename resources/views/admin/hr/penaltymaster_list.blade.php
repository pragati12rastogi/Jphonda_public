@extends($layout)

@section('title', __('Penalty Master List'))

@section('breadcrumb')
    <li><a href="#"><i class=""></i>Penalty Master List</a></li>
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
            "url": "/admin/hr/user/penalty/master/list/api",
            "datatype": "json",
            },
          "columns": [

              { "data": "name" },
              { "data": "amount" }
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
                      <th>Amount</th>                     
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