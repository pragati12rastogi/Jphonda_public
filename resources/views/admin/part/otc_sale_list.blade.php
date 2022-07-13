@extends($layout)

@section('title', __('OTC Sale List'))

@section('breadcrumb')
    <li><a href="#"><i class="">OTC Sale List</i></a></li>
@endsection
@section('css')
<link rel="stylesheet" href="/css/responsive.bootstrap.css">  
<style>
   .modal-header .close {
    margin-top: -33px;
}
#consumption_level {
    width: 200px;       
}
</style>  
@endsection
@section('js')
<script src="/js/dataTables.responsive.js"></script>
<script>
var dataTable;
$('#js-msg-error').hide();
$('#js-msg-errors').hide();
   $('#js-msg-success').hide();
    // Data Tables
    $(document).ready(function() {
      dataTable = $('#admin_table').DataTable({
          "processing": true,
          "serverSide": true,
          "ajax": "/admin/part/otc/sale/list/api",
          "aaSorting": [],
          "responsive": true,
          "columns": [
              { "data": "customer_name" },
              { "data": "customer_mobile" },
              { "data": "storename" },
              { "data": "model_name" },
              { "data": "color" },
              { "data": "qty" },
              { "data": "status" },
              {
                  "targets": [ -1 ],
                  "data":"id", "render": function(data,type,full,meta)
                  {
                    
                       return '<a href="#" id="'+data+'" class="update"><button data-toggle="modal" data-target="#exampleModalUpdate" class="btn btn-success btn-xs "> Update</button></a> &nbsp;'  ;
                  },
                  "orderable": false
              }
            ],
            "columnDefs": [
            ]
          
        });
    });

 $(document).on('click','.update',function(){
     var request_id = $(this).attr('id');

     if(request_id) {
           $.ajax({
            url:'/admin/part/get/otc/sale',
            data:{'req_id':request_id},
            method:'GET',
            dataType:'json',
            success:function(result){
              $('#otc_id').val(request_id);
              $('#quantity').val(result.qty);
            },
            error:function(error){
              $('#js-msg-error').html("Something Wen't Wrong !").show();
            },
           });
        }

 });   

$(document).on('click','#updatebtn',function(){
   var otc_id = $("#otc_id").val();
   var part_id = $("#part_id").val();
   var store_id = $("#store_id").val();
   var available_qty = $("#available_qty").val();
   var quantity = $("#quantity").val();
   if(available_qty <= quantity){
   $.ajax({
            url:'/admin/part/otc/sale/update',
            data:{'partId':part_id,'store_id':store_id,'qty':available_qty,'otc_id':otc_id},
            method:'GET',
            success:function(result){
              if(result.trim() == 'success')
               {
                 $('#exampleModalUpdate').modal("hide"); 
                  $('#js-msg-success').html('Otc sale updated successdully.').show();
                  $('#admin_table').dataTable().api().ajax.reload();
               }
               else if(result.trim() != 'error')
               {
                  $('#js-msg-errors').html(result).show();
               }
               else{
                  $('#js-msg-errors').html("Something Wen't Wrong").show();
               }
            },
            error:function(error){
              $('#js-msg-errors').html("Something Wen't Wrong "+error).show();
            },
           });
      }else{
         $('#js-msg-errors').html("Quantity is too more .").show();
      }
});


  $(document).on('keyup','#part_number',function(e){
        var part_no = $(this).val();
        var id = $("#otc_id").val();
        var qty = $("#quantity").val();
        $.ajax({
            url:'/admin/part/otc/check/part',
            data: {'part_no':part_no,'id':id,'quantity':qty},
            method:'GET',
            success:function(result){
              $("#part_id").val(result.part_id);
              $("#store_id").val(result.store_id);
              if(result.quantity >= qty){
                $('#js-msg-errors').hide();
                $("#available_qty").val(qty);
              }else if(result == 'stock_error'){
                  $('#js-msg-errors').html('Part not available in stock').show();
              }else if (result == 'part_error') {
                $('#js-msg-errors').html('Part number not available').show();
              }else{
                $('#js-msg-errors').html('Something went wrong !').show();
                
              }
              
            },
            error:function(error){
                alert("something wen't wrong.");
            }
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
                        <th>{{__('Customer Name')}}</th>
                        <th>{{__('Mobile')}}</th>
                        <th>{{__('Store Name')}}</th>                     
                        <th>{{__('Model Name')}}</th>                     
                        <th>{{__('Color')}}</th>                     
                        <th>{{__('Quantity')}}</th>                     
                        <th>{{__('Status')}}</th>                     
                        <th>{{__('parts.action')}}</th>                     
                      </tr>
                    </thead>

                    <tbody>
                    </tbody>
               
                  </table>
           
                </div>
                <!-- /.box-body -->
              </div>
        <!-- /.box -->

         <div class="modal fade" id="exampleModalUpdate" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
              <div class="modal-content" style="margin-top: 200px!important;">
                <form id="my-form" method="POST" onsubmit="return false">
                    @csrf
                    <input type="hidden" name="otc_id" id="otc_id" >
                    <input type="hidden" name="quantity" id="quantity" >
                    <input type="hidden" name="part_id" id="part_id" >
                    <input type="hidden" name="store_id" id="store_id" >
                    <div class="modal-header">
                      <h4 class="modal-title" id="exampleModalLongTitle">Check Part</h4>
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                      </button>
                      <br>
                      <div class="alert alert-danger" id="js-msg-errors">
                     </div>
                    </div>
                    <div class="modal-body">
                      <div class="row">
                         <div class="col-md-6">
                            <label>Part Number</label>
                            <input type="text" name="part_number" id="part_number" class="input-css">
                         </div>
                         <div class="col-md-6">
                            <label>Available Quantity</label>
                            <input type="number" name="available_qty" id="available_qty" class="input-css">
                         </div>
                      </div>    
                   </div>
                <div class="modal-footer">
                  <button type="button"  class="btn btn-secondary" data-dismiss="modal">Close</button>
                  <button type="button" id="updatebtn" class="btn btn-success">Submit</button>
                </div>
                </form>
              </div>
            </div>
          </div>
      </section>
@endsection