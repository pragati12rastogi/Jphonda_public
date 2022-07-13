@extends($layout)
@section('title', __('Service Payment List'))
@section('breadcrumb')
<li><a href="#"><i class=""></i></a></li>
@endsection
@section('css')
<link rel="stylesheet" href="/css/responsive.bootstrap.css">
<style>
   tr.dangerClass{
   background-color: #f8d7da !important;
   }
   tr.successClass{
   background-color: #d4edda !important;
   }
   tr.worningClass{
   background-color: #fff3cd !important;
   }
</style>
@endsection
@section('js')
<script src="/js/dataTables.responsive.js"></script>
<script>
   var dataTable;
     $('#js-msg-error').hide();
     $('#js-msg-success').hide();   

 
   
   // Data Tables'additional_services.id',
   $(document).ready(function() {
     dataTable = $('#table').DataTable({
         "processing": true,
         "serverSide": true,
         "ajax": "/admin/service/payment/list/api",
         "aaSorting": [],
         "responsive": true,
         "columns": [
             { "data": "tag"},
             { "data": "frame"},
             { "data": "registration"},
             { "data": "customer_status"},
             { "data": "job_card_type"},
             { "data": "service_status"},
             { "data": "hirise_amount"},
             { "data":  "fi_status",
              "render": function(data,type,full,meta)
                 {
                    var status = ((data == 'ok')? 'Ok' : ((data == 'notok')? 'Not Ok': ''));
                    return status;
                 },
             },
             { "data": "invoice_no"},
             { "data": "total_amount",
                 "render": function(data,type,full,meta)
                 {
                    if (full.sub_type == 'Best Deal' || full.job_card_type == 'PDI' || full.job_card_type == 'Best Deal' || full.JpHonda_discount == '100') {
                      return  ''+full.total_amount+' (Discount 100%)';
                    }else{
                      return data;
                    }
                 },
             },
             { "data": "paid_amount"},
             { "data": "garage_charge"},
             {
                 "targets": [ -1 ],
                 "data":"id", "render": function(data,type,full,meta)
                 {
                    var str = '';
                      str = str+'<a href="/admin/service/payment/'+data+'"><button class="btn btn-info btn-xs">Payment</button></a> &nbsp;&nbsp; <a href="/admin/service/payment/detail/'+data+'"><button class="btn btn-primary btn-xs">Payment Detail</button></a>&nbsp; <a href="/admin/service/charge/detail/'+data+'"><button class="btn btn-success btn-xs">Charge Detail</button></a>';
                     return str;
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
      <div class="row">
         <div class="alert alert-danger" id="js-msg-error">
         </div>
         <div class="alert alert-success" id="js-msg-success">
         </div>
       </div>
   <div class="box box-primary">
      <!-- /.box-header -->
      <div class="box-header with-border">
      </div>
      
      <div class="box-body">
         @include('admin.flash-message')
         @yield('content')
         <table id="table" class="table table-bordered table-striped">
            <thead>
               <tr>
                  <th>Tag #</th>
                  <th>Frame #</th>
                  <th>Registration #</th>
                  <th>Customer Status</th>
                  <th>Jobcard Type</th>
                  <th>Status</th>
                  <th>Hirise Amount</th>
                  <th>FI Status</th>
                  <th>Invoice Number</th>
                  <th>Total Amount</th>
                  <th>Paid Amount</th>
                  <th>Garage Charge</th>
                  <th>{{__('factory.action')}}</th>
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