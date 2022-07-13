@extends($layout)

@section('title', __('locality.title_view'))

@section('breadcrumb')
    <li><a href="#"><i class=""></i>Locality Summary</a></li>
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
          "ajax": "/admin/city/locality/list/api",
          "aaSorting": [],
          "responsive": true,
          "columns": [
              { "data": "id" },
              { "data": "name" },
              { "data": "city" }
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
                      <th>{{__('locality.id')}}</th>
                      <th>{{__('locality.locality_name')}}</th>
                      <th>{{__('locality.city')}}</th>                     
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