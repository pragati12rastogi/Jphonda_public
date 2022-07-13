@extends($layout)

@section('title', __('Call Data Mismatch Enquiry'))

@section('user', Auth::user()->name)

@section('breadcrumb')
  <li><a href="#"><i class=""></i>{{__('Call Data Summary')}}</a></li> 
@endsection
@section('css')

  <link rel="stylesheet" href="/css/responsive.bootstrap.css">
  <style>

  </style>

@endsection
@section('js')
<script src="/js/dataTables.responsive.js"></script>
<script>
  var dataTable;

    All();
    function All() {
      
      if(dataTable)
      {
        dataTable.destroy();
      }
      dataTable = $('#table-all').DataTable({
        "processing": true,
         "serverSide": true,
         "ajax": "/admin/call/data/requestEnquiry/list/api",
         "aaSorting": [],
         "responsive": true,
         "rowId":"id",
         "columns": [
            { "data": "call_type" },
            { "data": "customer_name" },
            { "data": "mobile" },
            { "data": "other_mobile" },
            { "data": "source" },
            { "data": "status" },
             {
                  "targets": [ -1 ],
                  "data":"id", "render": function(data,type,full,meta)
                  {
                    var str = '';
                    // str = str+'<a href="#" class ="btn btn-info btn-xs">Approve</a>';
                    return str;
                  },
                  "orderable": false
              }
            ]
         
       });
    }
   
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
          <div class="box-header with-border">
            
          </div>
                <!-- /.box-header -->
           <div class="box-body" id="all-div">
              <table id="table-all" class="table table-bordered table-striped">
                  <thead>
                      <tr>
                        <th>Call Type</th>
                        <th>Name</th>
                        <th>Mobile</th>
                        <th>Alternative Mobile</th>
                        <th>Source</th>
                        <th>{{__('Status')}}</th> 
                        <th>{{__('Action')}}</th> 
                      </tr>
                  </thead>
                  <tbody>
                  </tbody>               
              </table>
              <!-- /.box-body -->
            </div>
        </div>
    </section>
@endsection