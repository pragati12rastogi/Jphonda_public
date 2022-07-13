@extends($layout)
@section('title', __('Warranty Approve List'))
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
   .modal-header .close {
    margin-top: -33px;
}
.wickedpicker {
       z-index: 1064 !important;
   }
</style>
@endsection
@section('js')
<script src="/js/dataTables.responsive.js"></script>
<script>
   var dataTable;
   $('#js-msg-error').hide();
   $('#js-msg-errors').hide();
   $('#js-msg-errorss').hide();
   $('#js-msg-success').hide();
   
    $(document).ready(function() {
     dataTable = $('#table').DataTable({
         "processing": true,
         "serverSide": true,
         "ajax": "/admin/service/warranty/approval/list/api",
         "aaSorting": [],
         "responsive": true,
         "columns": [
             { "data": "tag"},
             { "data": "job_card_type"},
             { "data": "part_name"},
             { "data": "part_number"},
             { "data": "qty"},
             { "data": "price",
                "render": function(data,type,full,meta)
                 {
                    var amount = data*full.qty;
                    return amount;
                 },
             },
             { "data": "tampered"},
             { "data": "warranty_type"},
             { "data": "warranty_approve",
              "render": function(data,type,full,meta)
                 {
                    var approval = ((data == 1)? 'Yes' : ((data == 0)? 'No': ''));
                    return approval;
                 },
             },
             {
                 "targets": [ -1 ],
                 "data":"id", "render": function(data,type,full,meta)
                 {
                    var str = '';
                    if (full.warranty_approve == 0) {
                      str = str+'<a href="#"><button class="btn btn-info btn-xs " onclick="PartWarrantyApprove(this)"><i class="'+data+'"></i>Approve</button></a>';
                    }
                     return str;
                 },
                 "orderable": false
             }
           ]
       });
       });

     function PartWarrantyApprove(el) {
      var id = $(el).children('i').attr('class');
      var confirm1 = confirm('Are You Sure, You want to approve part warranty ?');
      if(confirm1) {
        $.ajax({
          method: "GET",
          url: "/admin/service/warranty/part/approve",
          data: {'id':id},
          success:function(data) {
              if (data == 'success') {
                  $('#warrantyModalCenter').modal("hide");    
                  $('#table').dataTable().api().ajax.reload();
                  $("#js-msg-success").html('Part warranty approved successfully. !');
                  $("#js-msg-success").show();
                 setTimeout(function() {
                   $('#js-msg-success').fadeOut('fast');
                  }, 4000);
              }if(data == 'error'){
                  $('#warrantyModalCenter').modal("hide");  
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




 
</script>
@endsection
@section('main_section')
<section class="content">
   <div id="app">
      <div class="row">
         <div class="alert alert-danger" id="js-msg-error" style="display: none;">
         </div>
         <div class="alert alert-success" id="js-msg-success" style="display: none;">
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
                  <th>Job Card Type</th>
                  <th>Part Name</th>
                  <th>Part Number</th>
                  <th>Quantity</th>
                  <th>Price</th>
                  <th>Tampere</th>
                  <th>Warranty Type</th>
                  <th>Warranty Approve</th>
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