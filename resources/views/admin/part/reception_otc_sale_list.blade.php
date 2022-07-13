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
          "ajax": "/admin/part/otc/receptionist/sale/list/api",
          "aaSorting": [],
          "responsive": true,
          "columns": [
              { "data": "customer_name" },
              { "data": "customer_mobile" },
              { "data": "storename" },
              { "data": "model_name" },
              { "data": "color" },
              { "data": "status" },
              { "data": "qty" },
              { "data": "price",
                 "render": function(data,type,full,meta)
                  {
                       return data*full.qty;
                  },
              },
               { "data": "paid_amount" },
              {
                  "targets": [ -1 ],
                  "data":"id", "render": function(data,type,full,meta)
                  {
                    var str = '';
                    if (full.price) {
                      str =str+ '<a href="/admin/part/otc/sale/payment/'+data+'"><button class="btn btn-success btn-xs "> Payment</button></a> &nbsp;';
                     str = str+'<a href="/admin/part/otc/sale/payment/detail/'+data+'"><button class="btn btn-info btn-xs "> Payment Detail</button></a> &nbsp;';
                     }
                     if (full.price*full.qty == full.paid_amount &&  full.price*full.qty != 0 && full.status != 'Sold') {

                      str = str+'<a href="#" id="'+data+'" class="otcConfirmation"><button class="btn btn-primary btn-xs ">Confirm Sale</button></a> &nbsp;';
                     }

                     return str;
                  },
                  "orderable": false
              }
            ],
            "columnDefs": [
            ]
          
        });
    });


 $(document).on('click','.otcConfirmation',function(){
     var id = $(this).attr('id');
     var confirm1 = confirm('Are You Sure, You Want to confirm otc sale ?');
      if(confirm1){
           $.ajax({
            url:'/admin/part/otc/sale/confirm/'+id,
            method:'GET',
            success:function(result){
              // console.log(result);
              if (result.trim() == 'success') {
                 $('#js-msg-success').html("OTC Sale confirmed successfully !").show();
                  $('#admin_table').dataTable().api().ajax.reload();
              }if(resultresult == 'error'){
                 $('#js-msg-error').html("Something Wen't Wrong !").show();
              }
            },
            error:function(error){
              $('#js-msg-error').html("Something Wen't Wrong !").show();
            },
           });
        }

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
                        <th>{{__('Status')}}</th>                     
                        <th>{{__('Quantity')}}</th>                     
                        <th>{{__('Price')}}</th>                     
                        <th>{{__('Paid Amount')}}</th>                     
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

      </section>
@endsection