@extends($layout)

@section('title', __('customer.title_view'))

@section('breadcrumb')
    <li><a href="#"><i class=""></i>Customer Summary</a></li>
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
          "ajax": "/admin/customer/list/api",
          "aaSorting": [],
          "responsive": true,
          "columns": [
              
              { "data": "name" }, 
              { "data": "mobile" },
              { "data": "aadhar_id" },
              { "data": "voter_id" },
              { "data": "email_id" },
              { "data": "sname" }, 
              { "data": "city_name" },
              { "data": "address" },
              { "data": "reference" },
              { "data": "created_at",
                "render": function(data,type,full,meta)
                    {
                        return showdate(data);
                    }
                },
              {
                  "targets": [ -1 ],
                  "data":"id", "render": function(data,type,full,meta)
                  {
                      return '<a href="/admin/customer/update/'+data+'" target="_blank"><button class="btn btn-success btn-xs"> Edit</button></a> &nbsp;'  ;
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
                      
                      <th>{{__('customer.view_name')}}</th>
                      <th>{{__('customer.view_contact')}}</th>
                      <th>{{__('Aadhar Number')}}</th>
                      <th>{{__('Voter Number')}}</th>
                      <th>{{__('customer.view_email')}}</th>
                      <th>{{__('customer.view_state')}}</th>
                      <th>{{__('customer.view_city')}}</th>
                      <th>{{__('customer.view_add')}}</th>
                      <th>{{__('customer.view_ref')}}</th>
                      <th>{{__('customer.createdat')}}</th>
                      <th>{{__('customer.action')}}</th>                      
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