@extends($layout)
@section('title', __('Service Pay Latter List'))
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
         "ajax": "/admin/service/pay/letter/list/api",
         "aaSorting": [],
         "responsive": true,
         "columns": [
             { "data": "tag"},
             { "data": "frame"},
             { "data": "registration"},
             { "data": "customer_status"},
             { "data": "job_card_type"},
             { "data": "service_status"},
             { "data": "pay_letter",
                "render": function(data,type,full,meta)
                 {
                    var pay = ((data == 1)? 'Allowed' : ((data == 0)? 'Not Allowed': ''));
                    return pay;
                 },
             },
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
                    if (full.sub_type == 'Best Deal' || full.job_card_type == 'PDI') {
                      return  ''+full.total_amount+' (Discount 100%)';
                    }else{
                      return data;
                    }
                 },
             },
             { "data": "paid_amount"},
             { "data": "garaj_charge",
                 "render": function(data,type,full,meta)
                 {
                  if (full.garage_charges_after < full.day ) {
                      var day = full.day - full.garage_charges_after;
                    return data*day;
                  }else{
                    return '0';
                  }
                    
                 },
             },
             {
                 "targets": [ -1 ],
                 "data":"id", "render": function(data,type,full,meta)
                 {
                    var str = '';
                    if (full.pay_letter == 0) {
                      str = str+'<a href="#"><button class="btn btn-info btn-xs "  onclick="AllowPayLetter(this)"><i class="'+data+'"></i>Allow Pay Letter </button></a>';
                    }
                     return str;
                 },
                 "orderable": false
             }
           ]
       });
   });
 function AllowPayLetter(el) {
      var id = $(el).children('i').attr('class');
      var confirm1 = confirm('Are You Sure, You Want to allow pay letter ?');
      if(confirm1) {
      $.ajax({
        method: "GET",
        url: "/admin/service/allow/pay/letter",
        data: {'jobcard_id':id},
        success:function(data) {
            if (data.type == 'success') {
                $('#payModalCenter').modal("hide");  
                $('#table').dataTable().api().ajax.reload();
                $("#js-msg-success").html(data.msg);
                $("#js-msg-success").show();
               setTimeout(function() {
                 $('#js-msg-success').fadeOut('fast');
                }, 4000);
            }if(data.type == 'error'){
                $('#payModalCenter').modal("hide");  
                $("#js-msg-error").html(data.msg);
                $("#js-msg-error").show();
               setTimeout(function() {
                 $('#js-msg-error').fadeOut('fast');
                }, 4000);
            }
        },
      });
      }
    }
   
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
                  <th>Pay Latter</th>
                  <th>FI Status</th>
                  <th>Invoice Number</th>
                  <th>Total Amount</th>
                  <th>Paid Amount</th>
                  <th>Garaj Charge</th>
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