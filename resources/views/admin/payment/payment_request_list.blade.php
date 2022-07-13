@extends($layout)
@section('title', __('Payment Request List'))
@section('breadcrumb')
<li><a href="#"><i class=""></i>{{__('Payment Request List')}}</a></li>
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
   
      $("#js-msg-verror").hide();
      $("#js-msg-error").hide();
      $("#js-msg-success").hide();
    
    var req_type = "{{request()->get('type')}}";
    var req_type_id = "{{request()->get('typeId')}}";

    datatablefn('pending');

    function datatablefn(table)
    {
      $("#pending-table").hide();
      $("#done-table").hide();

      if(dataTable)
      {
        dataTable.destroy();
      }
      $("#"+table+'-table').show();
      var all_col = [
              { "data": "type" },
             { "data": "customer_name","orderable": false },
             { "data": "date","orderable": false },
             { "data": "amount",
                  "data":"id", "render": function(data,type,full,meta)
                  {   if (full.type == 'security' || full.type == 'booking') {
                           return full.paid_amount;
                         }else{
                           return full.amount;
                         }
                  }
             },
             { "data": "paid_amount"},
             { "data": "status" }
        ];
      var action = {
                    "targets": [ -1 ],
                    "data":"id", "render": function(data,type,full,meta)
                    {
                            return '<a href="/admin/payment/detail/'+data+'"><button class="btn btn-info btn-xs">Payment Details</button></a>' ;
                    },
                    "orderable": false
                  };
      pay_status = 'Done';

      if(table == 'pending'){
        pay_status = 'Pending';

        action = {
          "targets": [ -1 ],
                    "data":"id", "render": function(data,type,full,meta)
                    {
                            return '<a href="/admin/payment/request/'+data+'"><button class="btn btn-success btn-xs">Pay</button></a>'+
                            ' &nbsp;<a href="/admin/payment/detail/'+data+'"><button class="btn btn-info btn-xs">Payment Details</button></a>' ;
                    },
                    "orderable": false
        };
      }
      all_col.push(action);

      dataTable = $('#'+table+'-table').DataTable({
         "processing": true,
         "serverSide": true,
         "ajax": {
           "url": "/admin/payment/request/list/api",
            "type": "GET",
            "data": {'req_type':req_type,'req_type_id':req_type_id,'pay_status':pay_status},
          },
         "aaSorting": [],
         "responsive": true,
         "columns": all_col
         
       });
    }

    $("#pending-id").on('click',function(){
      $(".all-active").removeClass('btn-color-active');
      $(".all-active").removeClass('btn-color-unactive');
      $(this).addClass('btn-color-active');
      datatablefn('pending');
    });
    $("#done-id").on('click',function(){
      $(".all-active").removeClass('btn-color-active');
      $(".all-active").removeClass('btn-color-unactive');
      $(this).addClass('btn-color-active');
      datatablefn('done');
    });
  
</script>
@endsection
@section('main_section')
<section class="content">
   <div id="app">
    @include('admin.flash-message')
    <div class="alert alert-danger" id="js-msg-error" style="display: none">
    </div>
    <div class="alert alert-success" id="js-msg-success" style="display: none">
    </div>
    @yield('content')
    <!-- Default box -->
    <div class="box box-primary">
        <!-- /.box-header -->
        <div class="box-header with-border">
          <ul class="nav nav1 nav-pills">
            <li class="nav-item">
              <button class="nav-link1 all-active btn-color-active" id="pending-id" >Pending Payment List</button>
            </li>
            <li class="nav-item">
              <button class="nav-link1 all-active btn-color-unactive" id="done-id" >Complete Payment</button>
            </li>
          </ul>
        </div>
        <div class="box-body" id="div">
          <table id="pending-table" class="table table-bordered table-striped">
              <thead>
                <tr>
                    <th>{{__('Type')}}</th>
                    <th>{{__('Customer Name')}}</th>
                    <th>{{__('Date')}}</th>
                    <th>{{__('Amount')}}</th>
                    <th>{{__('Paid Amount')}}</th>
                    <th>{{__('Status')}}</th>
                    <th>Action</th>
                </tr>
              </thead>
              <tbody>
              </tbody>
          </table>
          <table id="done-table" class="table table-bordered table-striped" style="display: none;">
            <thead>
              <tr>
                  <th>{{__('Type')}}</th>
                  <th>{{__('Customer Name')}}</th>
                  <th>{{__('Date')}}</th>
                  <th>{{__('Amount')}}</th>
                  <th>{{__('Paid Amount')}}</th>
                  <th>{{__('Status')}}</th>
                  <th>Action</th>
              </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
        </div>
       
        
        <!-- /.box-body -->
    </div>
    
   </div>

   <!-- /.box -->
</section>
@endsection