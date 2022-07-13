@extends($layout)

@section('title', __('hirise.rto_fillup_list'))

@section('user', Auth::user()->name)

@section('breadcrumb')

    <li><a href="#"><i class=""></i>{{__('hirise.rto_fillup_list')}}</a></li> 
@endsection
@section('css')

<link rel="stylesheet" href="/css/responsive.bootstrap.css">    
@endsection
@section('js')
<script src="/js/dataTables.responsive.js"></script>
<script>
  var dataTable;

  $(document).ready(function() {
      dataTable = $('#table').DataTable({
        "processing": true,
         "serverSide": true,
         "ajax": "/admin/rto/fillup/list/api",
         "aaSorting": [],
         "responsive": true,
         "columns": [
            { "data": "id" },
            { "data": "sale_no" },
             {
                 "targets": [ -1 ],
                 "data":"id", "render": function(data,type,full,meta)
                 {
                   return '<a href="/admin/sale/rto/'+data+'"><button class="btn btn-info btn-xs"> Create RTO</button></a> ';
                 },
                 "orderable": false
             }
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
            
        </div>
    <!-- Default box -->
        <div class="box">
                <!-- /.box-header -->
            <div class="box-body">
                    <table id="table" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                              <th>Id</th>
                              <th>{{__('hirise.sale_no')}}</th>                   
                              <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>               
                    </table>
                    <!-- /.box-body -->
            </div>
        </div>
        <!-- /.box -->
    </section>
@endsection