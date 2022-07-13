@extends($layout)

@section('title', __('admin.cancel_insurance'))

@section('breadcrumb')
    <li><a href="#"><i class=""></i>{{__('admin.cancel_insurance')}}</a></li>
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
          "ajax": "/admin/cancel/insurance/list/api",
          "aaSorting": [],
          "responsive": true,
          "columns": [
              // { "data": "id" },
              { "data": "sale_no" },
              { "data": "insurance_co" },
              { "data": "insurance_type" },
              { "data": "insurance_amount" },
              { "data": "policy_number" },
              { "data": "type" },
              { "data": "status","orderable":false }
              // {
              //     "targets": [ -1 ],
              //     "data":"id", "render": function(data,type,full,meta)
              //     {
              //       return '<a href="/admin/booking/pay/'+data+'"><button class="btn btn-info btn-xs"> Pay</button></a> &nbsp;<a href="/admin/booking/pay/detail/'+data+'"><button class="btn btn-success btn-xs"> Payment Detail</button></a> ';
              //     },
              //     "orderable": false
              // }
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
                      {{-- <th>Id</th> --}}
                      <th>{{__('hirise.sale_no')}}</th>
                      <th>{{__('hirise.ins_co')}}</th>                    
                      <th>{{__('hirise.ins_type')}}</th>                    
                      <th>{{__('hirise.ins_amount')}}</th>                    
                      <th>{{__('hirise.inspolicy')}}</th>                    
                      <th>{{__('Type')}}</th>                    
                      <th>{{__('hirise.status')}}</th>                    
                     <!--  <th>Action</th>   -->                   
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