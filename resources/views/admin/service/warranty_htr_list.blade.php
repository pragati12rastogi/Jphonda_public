@extends($layout)
@section('title', __('Warranty Part List'))
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
         "ajax": "/admin/service/warranty/htr/list/api",
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
             { "data": "htr_number" },
             { "data": "warranty_approve", 
                "render": function (data,type,full,meta) {
                  var approvel = (( data == 1 ? 'Yes': (data == 0 ? 'No':'')));
                   return approvel;
                },
            },
             { "data": "approved" },
             { "data": "rejection" },
             {
                 "targets": [ -1 ],
                 "data":"id", "render": function(data,type,full,meta)
                 {
                    var str = '';
                 
                      str = str+' <a href="#"><button class="btn btn-info btn-xs " onclick="UpdateHtr(this)"><i class="'+data+'"></i>Update Htr</button></a>';
                      if (full.invoice_id != null && full.approved == 'no' && full.rejection == 'no') {
                        str = str+' <a href="#"><button class="btn btn-success btn-xs " onclick="WarrantyApprove(this)"><i class="'+data+'"></i>Approve</button></a>';
                        str = str+' <a href="#"><button class="btn btn-primary btn-xs " onclick="WarrantyReject(this)"><i class="'+data+'"></i>Rejection</button></a>';
                      }
               
                     return str;
                 }, 
                 "orderable": false
             }
           ]
       });
       });

    function WarrantyApprove(el) {
      var id = $(el).children('i').attr('class');
      if(id) {
      var confirm1 = confirm('Are You Sure, You Want to approve warranty part ?');
      if(confirm1)
      {
        $.ajax({
        method: "GET",
        url: "/admin/service/warranty/approve",
        data: {'id':id},
        success:function(data) {
          if (data == 'success') {
                $('#warrantyModalCenter').modal("hide");    
                $("#js-msg-success").html('Part warranty approved successfully !');
                $("#js-msg-success").show();
                $('#table').dataTable().api().ajax.reload();
            }if(data == 'error'){
                $('#warrantyModalCenter').modal("hide");  
                $("#js-msg-error").html('Something went wrong !');
                $("#js-msg-error").show();
               setTimeout(function() {
                 $('#js-msg-error').fadeOut('fast');
                }, 4000);
            }
        }
      }); 
      }   
      }
    }

    function WarrantyReject(el) {
      var id = $(el).children('i').attr('class');
      if(id) {
      var confirm1 = confirm('Are You Sure, You Want to reject warranty part ?');
      if(confirm1)
      {
        $.ajax({
        method: "GET",
        url: "/admin/service/warranty/reject",
        data: {'id':id},
        success:function(data) {
          if (data == 'success') {
                $('#warrantyModalCenter').modal("hide");    
                $("#js-msg-success").html('Part warranty rejected !');
                $("#js-msg-success").show();
                $('#table').dataTable().api().ajax.reload();
            }if(data == 'error'){
                $('#warrantyModalCenter').modal("hide");  
                $("#js-msg-error").html('Something went wrong !');
                $("#js-msg-error").show();
               setTimeout(function() {
                 $('#js-msg-error').fadeOut('fast');
                }, 4000);
            }
        }
      }); 
      }   
      }
    }

   function UpdateHtr(el) {
      var id = $(el).children('i').attr('class');
      if(id) {
        $.ajax({
        method: "GET",
        url: "/admin/service/part/htr/get",
        data: {'id':id},
        success:function(data) {
          $("#id").val(id);
          $("#htr").val(data.htr_number);
          $('#warrantyModalCenter').modal("show"); 
        }
      });    
      }
    }


   $('#btnUpdate').on('click', function() {
      var id = $('#id').val();
      var htr = $("#htr").val();
 
       $.ajax({
        method: "GET",
        url: "/admin/service/htr/update",
        data: {'id':id,'htr':htr},
        success:function(data) {
            if (data == 'success') {
                $('#warrantyModalCenter').modal("hide");    
                $("#js-msg-success").html('HTR updated successfully !');
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
                  <th>Tag #</th>
                  <th>Job Card Type</th>
                  <th>Part Name</th>
                  <th>Part Number</th>
                  <th>Quantity</th>
                  <th>Price</th>
                  <th>Tampere</th>
                  <th>Warranty Type</th>
                  <th>Htr Number</th>
                  <th>Warranty Approve</th>
                  <th>Part Warranty Approve</th>
                  <th>Part Warranty Reject</th>
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
            <h4 class="modal-title" id="exampleModalLongTitle">Update Htr</h4>
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
              <label for="advance">HTR Number<sup>*</sup></label>
               <input type="text" name="htr" class="input-css" id="htr">                      
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