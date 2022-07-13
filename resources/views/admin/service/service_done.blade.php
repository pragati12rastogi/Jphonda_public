@extends($layout)
@section('title', __('Service Done List'))
@section('breadcrumb')
<li><a href="#"><i class=""></i></a></li>
@endsection
@section('css')
<link rel="stylesheet" href="/css/responsive.bootstrap.css">

@endsection
@section('js')
<script src="/js/dataTables.responsive.js"></script>
<script>
   var dataTable;
     $('#js-msg-error').hide();
     $('#js-msg-verror').hide();
     $('#js-msg-success').hide(); 
  
    function ServiceDone(el) {
      var id = $(el).children('i').attr('class');
      var confirm1 = confirm('Are You Sure, You Want to done service ?');
      if(confirm1)
      {
      $.ajax({
        method: "GET",
        url: "/admin/service/done",
        data: {'jobcard_id':id},
        success:function(data) {
            if (data == 'success') {
                $('#serviceModalCenter').modal("hide");  
                $('#table').dataTable().api().ajax.reload();
                $("#js-msg-success").html('Service done successfully!');
                $("#js-msg-success").show();
               setTimeout(function() {
                 $('#js-msg-success').fadeOut('fast');
                }, 4000);
            }if(data == 'error'){
                $('#exampleModalCenter').modal("hide");  
                $("#js-msg-error").html('Something went wrong !');
                $("#js-msg-error").show();
               setTimeout(function() {
                 $('#js-msg-error').fadeOut('fast');
                }, 4000);
            }
        },
      });
      }
    }
   
   $(document).ready(function() {
     dataTable = $('#table').DataTable({
         "processing": true,
         "serverSide": true,
         "ajax": "/admin/service/done/list/api",
         "aaSorting": [],
         "responsive": true,
         "columns": [
             { "data": "tag"},
             { "data": "frame"},
             { "data": "registration"},
             { "data": "job_card_type"},
             { "data": "customer_status"},
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
             { "data": "payment_status"},
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
             { "data":"garage_charge" },
             { "data": "status"},
             {
                 "targets": [ -1 ],
                 "data":"id", "render": function(data,type,full,meta)
                 {
                    var str = '';
                      if (full.paid_amount == full.total_amount && full.payment_status == 'done' && full.status != 'Done' && full.status != 'Close') {
                        str = str+' <a href="#"><button class="btn btn-info btn-xs "  onclick="ServiceDone(this)"><i class="'+data+'"></i>Service Done </button></a> ';
                      }
                      if ((full.job_card_type == 'Best Deal' || full.sub_type == 'Best Deal' || full.job_card_type == 'PDI' || full.JpHonda_discount == '100')  && full.status != 'Done' && full.status != 'Close') {
                         str = str+' <a href="#"><button class="btn btn-info btn-xs "  onclick="ServiceDone(this)"><i class="'+data+'"></i>Service Done </button></a> ';
                      }
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
                  <th>Jobcard Type</th>
                  <th>Customer Status</th>
                  <th>Pay Latter</th>
                  <th>FI Status</th>
                  <th>Payment Status</th>
                  <th>Invoice Number</th>
                  <th>Total Amount</th>
                  <th>Paid Amount</th>
                  <th>Garage Charge</th>
                  <th>Status</th>
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