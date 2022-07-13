@extends($layout)

@section('title', __('Unload Addon List'))

@section('breadcrumb')
    <li><a href="#"><i class=""></i>Unload Addon Summary</a></li>
@endsection
@section('css')
<link rel="stylesheet" href="/css/responsive.bootstrap.css">  
<style>
   .modal-header .close {
    margin-top: -33px;
}
#status {
    width: 250px;       
}
</style>  
@endsection
@section('js')
<script src="/js/dataTables.responsive.js"></script>
 <script>

    var dataTable;
    $('#js-msg-error').hide();
    $('#js-msg-success').hide();
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
         "responsive": true,
          "ajax": {
            "url": "/admin/unload/addon/list/api",
            "datatype": "json",
            },
          "columns": [
              { "data": "load_referenace_number" },
              { "data": "addon_name" },
              { "data": "model" },
              { "data": "qty" },
              { "data": "store_name" },


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
              <div class="row">
         <div class="alert alert-danger" id="js-msg-error">
         </div>
         <div class="alert alert-success" id="js-msg-success">
         </div>
       </div>
                    @include('admin.flash-message')
                    @yield('content')
        <!-- Default box -->
        
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
                      <th>Load Referenace Number</th>
                      <th>Addon Name</th>
                      <th>Model</th>                                                          
                      <th>Quantity</th>                                    
                      <th>Store Name</th>                                    
                    </tr>
                    </thead>
                    <tbody>
                      
                    </tbody>
               
                  </table>
           
                </div>
                <!-- /.box-body -->
              </div>
      </section>
@endsection