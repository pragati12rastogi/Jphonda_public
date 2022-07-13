@extends($layout)

@section('title', __('hirise.rto_file_sub_list'))

@section('user', Auth::user()->name)

@section('breadcrumb')

    <li><a href="#"><i class=""></i>{{__('hirise.rto_file_sub_list')}}</a></li> 
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
         "ajax": "/admin/rto/file/submission/list/api",
         "aaSorting": [],
         "responsive": true,
         "columns": [
            { "data": "id" },
            { "data": "agent_name" },
            { "data": "rtocount" },
            { "data": "submission_date" },
             {
                 "targets": [ -1 ],
                 "data":"id", "render": function(data,type,full,meta)
                 {
                   return '<a href="/admin/rto/file/submission/view/'+data+'" target="_blank"><button class="btn btn-info btn-xs"> View</button></a> &nbsp; <a href="/admin/rto/file/submission/print/'+data+'" target="_blank"><button class="btn btn-primary btn-xs">Print</button></a> ';
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
                               <th>{{__('Agent Name')}}</th>                    
                              <th>{{__('Total RTO')}}</th>                    
                              <th>{{__('Handover Date')}}</th> 
                              <th>{{__('Action')}}</th>
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