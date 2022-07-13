@extends($layout)
@section('title', __('Jobcard Process List'))
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
     $('#js-msg-derror').hide();
     $('#js-msg-verror').hide();
     $('#js-msg-success').hide();
    function FiApprove(el) {
      var id = $(el).children('i').attr('class');
      $.ajax({  
                url:"/admin/service/hirise/get/"+id,  
                method:"get",  
                success:function(data){
                 if(data) {
                  $("#id").val(data.jobcard.id);
                  $("#invoice_no").val(data.jobcard.invoice_no);
                  $("#amount").val(data.jobcard.hirise_amount);
                  $('#hiriseModalCenter').modal("show"); 
                  }
                      
                }  
           });
    }

    function AddDiscount(el) {
      var id = $(el).children('i').attr('class');
        $.ajax({  
                url:"/admin/service/get/discount/"+id,  
                method:"get",  
                success:function(data){
                 if(data) {
                    $("#jc_id").val(id);
                    var string='<table width="100%"class="table table-bordered table-striped"><tr><th>Charge Type</th><th>Sub Type</th> <th>Amount</th><tr>';
                    $.each( data, function( key, value ) { 
                     string += "<tr> <td>"+value['charge_type'] + "</td><td>"+value['sub_type']+'</td>  \
                               <td>'+value['amount']+"</td> </tr>"; 
                         }); 
                        string += '</table>'; 
                     $("#records").html(string); 
                    $('#DiscountModel').modal("show"); 
                  }   
                }  
           });
    }



    $('#btnUpdate').on('click', function() {
      var id = $('#id').val();
      var amount = $('#amount').val();
      var invoice_no = $('#invoice_no').val();
       $.ajax({
        method: "GET",
        url: "/admin/service/hirise/update",
        data: {'id':id,'amount':amount,'invoice_no':invoice_no},
        success:function(data) {
            if (data == 'success') {
                $('#hiriseModalCenter').modal("hide");    
                $('#table').dataTable().api().ajax.reload();
                $("#js-msg-success").html('Hirise updated succefully !');
                $("#js-msg-success").show();
               setTimeout(function() {
                 $('#js-msg-success').fadeOut('fast');
                }, 4000);
            }if(data == 'error'){
                $('#hiriseModalCenter').modal("hide");  
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

    $('#DiscountBtnUpdate').on('click', function() {
      var jobcard_id = $('#jc_id').val();
      var amount = $('#discount_amount').val();
      var sub_type = $('#sub_type').val();
       $.ajax({
        method: "GET",
        url: "/admin/service/add/discount",
        data: {'jobcard_id':jobcard_id,'amount':amount,'sub_type':sub_type},
        success:function(data) {
            if (data == 'success') {
                $('#DiscountModel').modal("hide");  
                $('#table').dataTable().api().ajax.reload();
                $("#js-msg-success").html('Discount amount added succefully !');
                $("#js-msg-success").show();
               setTimeout(function() {
                 $('#js-msg-success').fadeOut('fast');
                }, 4000);
            }if(data == 'error'){
                $('#DiscountModel').modal("hide");  
                $("#js-msg-error").html('Something went wrong !');
                $("#js-msg-error").show();
               setTimeout(function() {
                 $('#js-msg-error').fadeOut('fast');
                }, 4000);
            }if(data == 'verror'){ 
                $("#js-msg-derror").html('Field is require !');
                $("#js-msg-derror").show();
               setTimeout(function() {
                 $('#js-msg-derror').fadeOut('fast');
                }, 4000);
            }if(data == 'find'){
                $('#DiscountModel').modal("hide");  
                $("#js-msg-error").html(sub_type+' discount type already added !');
                $("#js-msg-error").show();
               setTimeout(function() {
                 $('#js-msg-error').fadeOut('fast');
                }, 4000);
            }
        },
      });
    });

    $('#btnCancel').on('click', function() {
      $('#my-form')[0].reset(); 
    });
   

 
   
   // Data Tables'additional_services.id',
   $(document).ready(function() {
     dataTable = $('#table').DataTable({
         "processing": true,
         "serverSide": true,
         "ajax": "/admin/service/hirise/list/api",
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
             { "data": "hirise_amount"},
             {
                 "targets": [ -1 ],
                 "data":"id", "render": function(data,type,full,meta)
                 {
                    var str = '';
                      str = str+'<a href="#"><button class="btn btn-info btn-xs "  onclick="FiApprove(this)"><i class="'+data+'"></i>Update Hirise</button></a> ';
                        str = str+' <a href="#"><button class="btn btn-primary btn-xs "  onclick="AddDiscount(this)"><i class="'+data+'"></i>Discount</button></a>';
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
                  <th>Amount</th>
                  <th>{{__('factory.action')}}</th>
               </tr>
            </thead>
            <tbody>
            </tbody>
         </table>
      </div>
      <!-- /.box-body -->
   </div>
   <!-- Hirise  Modal --> 
    <div class="modal fade" id="hiriseModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="margin-top: 200px!important;">
          <div class="modal-header">
            <h4 class="modal-title" id="exampleModalLongTitle">Update Hirise</h4>
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
               <input type="text" name="invoice_no" id="invoice_no" class="input-css">                      
           </div>
         </div><br>
         <div class="row">
                <div class="col-md-6">
                    <label>Amount <sup>*</sup></label>
                    <input type="number" name="amount" id="amount" class="input-css">
                </div>
              </div>
          </div>
          <div class="modal-footer">
            <button type="button" id="btnCancel" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="button" id="btnUpdate" class="btn btn-success">Add</button>
          </div>
          </form>
        </div>
      </div>
    </div>

    <!---Discount Model --->
    <div class="modal fade" id="DiscountModel" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="margin-top: 200px!important;">
          <div class="modal-header">
            <h4 class="modal-title" id="exampleModalLongTitle">Add Discount</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">                   
            <form id="discount-form"  method="POST" >
              @csrf
              <div class="alert alert-danger" id="js-msg-derror"></div>
              <input type="hidden" name="jc_id" id="jc_id">
              <div class="col-md-12">
              <div class="row">
                <div class="col-md-6">
                  <div class="row">
                    <div class="col-md-12">
                      <label for="advance">Discount Sub Type<sup>*</sup></label>
                       <select name="sub_type" id="sub_type" class="form-control">
                         <option selected="" disabled="">Select Discount type</option>
                         <option value="Parts">Parts</option>
                         <option value="Labour">Labour</option>
                         <option value="Other">Other</option>
                       </select>        
                    </div>
                  </div><br>
                 <div class="row">
                    <div class="col-md-12">
                        <label>Amount <sup>*</sup></label>
                        <input type="number" name="discount_amount" id="discount_amount" class="input-css">
                    </div>
                  </div>
                </div>
                <div class="col-md-6" id="records">
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" id="Cancel" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="button" id="DiscountBtnUpdate" class="btn btn-success">Add</button>
          </div>
          </form>
        </div>
      </div>
    </div>
   <!-- /.box -->
</section>
@endsection