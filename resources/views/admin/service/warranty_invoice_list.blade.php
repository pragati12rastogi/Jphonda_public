@extends($layout)
@section('title', __('Warranty Invoice List'))
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
   $('#js-msg-verror').hide();
   $('#js-msg-success').hide();
   
    $(document).ready(function() {
     dataTable = $('#table').DataTable({
         "processing": true,
         "serverSide": true,
         "ajax": "/admin/service/warranty/invoice/list/api",
         "aaSorting": [],
         "responsive": true,
         "columns": [
             { "data": "invoice_number"},
             { "data": "courier_number"},
             { "data": "courier_amount"},
             { "data": "part_receive"},
             { "data": "invoice_receive"},
             { "data": "dispatch" },
             {
                 "targets": [ -1 ],
                 "data":"id", "render": function(data,type,full,meta)
                 {
                    var str = '';
                 
                      str = str+' <a href="/admin/service/warranty/invoice/update/'+data+'"><button class="btn btn-info btn-xs " ></i>Update Details</button></a>';               
                     return str;
                 }, 
                 "orderable": false
             }
           ]
       });
       });

   function UpdateHtr(el) {
      var id = $(el).children('i').attr('class');
      if(id) {
        $.ajax({
        method: "GET",
        url: "/admin/service/get/warranty/invoice",
        data: {'id':id},
        success:function(data) {
          $("#id").val(id);
          $("#invoice_number").val(data.invoice_number);
          $("#courier_number").val(data.courier_number);
          $("#courier_amount").val(data.courier_amount);
          $('#warrantyModalCenter').modal("show"); 
        }
      });    
      }
    }


   $('#btnUpdate').on('click', function() {
      var id = $('#id').val();
      var invoice_number = $("#invoice_number").val();
      var courier_number = $("#courier_number").val();
      var courier_amount = $("#courier_amount").val();
 
       $.ajax({
        method: "GET",
        url: "/admin/service/warranty/invoice/update",
        data: {'id':id,'invoice_number':invoice_number,'courier_number':courier_number,'courier_amount':courier_amount,},
        success:function(data) {
            if (data == 'success') {
                $('#warrantyModalCenter').modal("hide");    
                $("#js-msg-success").html('Details updated successfully !');
                $("#js-msg-success").show();
                $('#table').dataTable().api().ajax.reload();
            }if(data == 'error'){
                $('#warrantyModalCenter').modal("hide");  
                $("#js-msg-error").html('Something went wrong !');
                $("#js-msg-error").show();
               setTimeout(function() {
                 $('#js-msg-error').fadeOut('fast');
                }, 4000);
            }if(data == 'verror'){ 
                $("#js-msg-verror").html('Field is require !');
                $("#js-msg-verror").show();
               setTimeout(function() {
                 $('#js-msg-verror').fadeOut('fast');
                }, 4000);
            }
        },
      });
    });

 
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
                  <th>Invoice Number</th>
                  <th>Courier Number</th>
                  <th>Courier Amount</th>
                  <th>Part Receive</th>
                  <th>Invoice Receive</th>
                  <th>Dispatch</th>
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
   <!-- Modal -->
    <div class="modal fade" id="warrantyModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="margin-top: 200px!important;">
          <div class="modal-header">
            <h4 class="modal-title" id="exampleModalLongTitle">Update Details</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">                   
            <form id="my-form"  method="POST" >
                <div class="alert alert-danger" id="js-msg-verror">
              </div>
              @csrf
              <input type="hidden" name="id" id="id">
              <div class="row">
                <div class="col-md-6">
                  <label for="advance">Invoice Number<sup>*</sup></label>
                  <input type="text" name="invoice_number" class="input-css" id="invoice_number">
                </div>
                 <div class="col-md-6">
                  <label for="advance">Courier Number<sup>*</sup></label>
                  <input type="text" name="courier_number" class="input-css" id="courier_number">
                </div>
              </div><br>
              <div class="row">
                <div class="col-md-6">
                  <label for="advance">Courier Amount<sup>*</sup></label>
                  <input type="number" min="0" name="courier_amount" class="input-css" id="courier_amount">
                </div>
              </div><br>
          </div>
          <div class="modal-footer">
            <button type="button" id="btnCancel" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="button" id="btnUpdate" class="btn btn-success">Update</button>
          </div>
          </form>
        </div>
      </div>
    </div>
</section>
@endsection