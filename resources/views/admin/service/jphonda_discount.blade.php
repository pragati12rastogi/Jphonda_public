@extends($layout)
@section('title', __('JpHonda Discount List'))
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
  

    function AddDiscount(el) {
      var id = $(el).children('i').attr('class');
      var amount = '100';
      var sub_type = 'JpHonda';
       if (confirm('Are you sure, You want to add JpHonda discount ? ')) {
       $.ajax({
        method: "GET",
        url: "/admin/service/jphonda/discount",
        data: {'jobcard_id':id,'amount':amount,'sub_type':sub_type},
        success:function(data) {
            if (data.type == 'success') {
                $('#DiscountModel').modal("hide");  
                $('#table').dataTable().api().ajax.reload();
                $("#js-msg-success").html(data.msg);
                $("#js-msg-success").show();
               setTimeout(function() {
                 $('#js-msg-success').fadeOut('fast');
                }, 4000);
            }if(data.type == 'error'){  
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

   $(document).ready(function() {
     dataTable = $('#table').DataTable({
         "processing": true,
         "serverSide": true,
         "ajax": "/admin/service/jphonda/discount/list/api",
         "aaSorting": [],
         "responsive": true,
         "columns": [
             { "data": "tag"},
             { "data": "frame"},
             { "data": "registration"},
             { "data": "job_card_type"},
             { "data": "customer_status"},
             { "data": "service_status"},
             { "data": "wash"},
             { "data":  "fi_status",
              "render": function(data,type,full,meta)
                 {
                    var status = ((data == 'ok')? 'Ok' : ((data == 'notok')? 'Not Ok': ''));
                    return status;
                 },
             },
             { "data": "invoice_no"},
             { "data": "discount_amount",
              "render": function(data,type,full,meta)
                 {
                  if (data == null) {
                    return 0;
                  }else{
                    return data +'%';
                  }
                 },
             },
             {
                 "targets": [ -1 ],
                 "data":"id", "render": function(data,type,full,meta)
                 {
                    var str = '';
                    if (full.discount_amount == null) {
                        str = str+' <a href="#"><button class="btn btn-primary btn-xs "  onclick="AddDiscount(this)"><i class="'+data+'"></i>Discount</button></a>';
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
         <div class="alert alert-error" id="js-msg-error">
         </div>
         <div class="alert alert-success" id="js-msg-success">
         </div>
       </div>
   <div class="box box-primary">
      <!-- /.box-header -->
      <div class="box-header with-border">
                        {{-- <h3 class="box-title">{{__('customer.mytitle')}} </h3> --}}

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
                  <th>Status</th>
                  <th>Wash</th>
                  <th>FI Status</th>
                  <th>Invoice Number</th>
                  <th>Discount Amount</th>
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