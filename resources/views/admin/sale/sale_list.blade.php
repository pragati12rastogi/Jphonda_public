@extends($layout)
@section('title', __('Sale'))
@section('breadcrumb')
<li><a href="#"><i class=""></i>{{__('Sale')}}</a></li>
@endsection
@section('css')
<link rel="stylesheet" href="/css/responsive.bootstrap.css">
<style>

</style>
@endsection
@section('js')
<script src="/js/dataTables.responsive.js"></script>
<script>
   var dataTable;
   
    
   // Data Tables
   $(document).ready(function() {

    $("#js-msg-error").hide();
    $("#js-msg-success").hide();


    function allSale()
    {

      $("#complete-div").css('display','none');
      $("#all-div").css('display','block');
      $("#pending-div").css('display','none');

      if(dataTable)
      {
        dataTable.destroy();
      }
      dataTable = $('#allSale_table').DataTable({
         "processing": true,
         "serverSide": true,
         "ajax": "/admin/sale/list/api/all",
         "aaSorting": [],
         "responsive": true,
         "columns": [
             { "data": "product_name" },
             { "data": "sale_no" },
             { "data": "name" },
             { "data": "sale_date" },
             { "data": "balance" },
             { "data": "amount", "orderable": false },
             { "data": "refunded_amount" },
             { "data":  "status",
              "render": function(data,type,full,meta)
                 {
                    var status = ((data == 'pending')? 'Pending' : ((data == 'cancelled')? 'Cancelled' : 
                                    ((data == 'cancelRequest')? 'Cancel Requested' : ((data == 'done') ? 'Complete' : '' ) )));
                    return status;
                 },
                 className :'status'
             },
             {
                 "targets": [ -1 ],
                 "data":"id", "render": function(data,type,full,meta)
                 {  
                   var str = '';
                   var cond = ((full.status == 'cancelRequest')? false : ((full.status == 'cancelled') ? false : true) );
                  //  console.log(cond);
                   if(cond)
                   {
                     if(parseFloat(full.amount) > parseFloat(full.total_amount) && (full.role == 'Superadmin' || full.role == 'Accountant' ))
                     {
                       str +='<a href="/admin/sale/refund/'+data+'"><button class="btn btn-info btn-xs"> Refund</button></a>';
                     }

                     if(parseFloat(full.total_amount) > parseFloat(full.amount) && (full.role == 'Superadmin' || full.role == 'Accountant' ))
                     {
                       str +='<a href="/admin/sale/pay/'+data+'"><button class="btn btn-info btn-xs"> Pay</button></a>';
                     }
                     if(full.role == 'Superadmin' && full.user_type == 'superadmin' )
                     {
                      // str +=  ' <a href="/admin/update/sale/'+data+'"><button class="btn btn-danger btn-xs"> Update</button></a>&nbsp;';
                      str += '<div class="btn-group">'+
                                '<button type="button" class="btn btn-danger  btn-xs">Update'+
                                '<button type="button" class="btn btn-danger  dropdown-toggle-split dropdown-toggle" data-toggle="dropdown" aria-expanded="false">'+
                                  '<span class="caret"></span>'+
                                  '<span class="sr-only">Toggle Dropdown</span>'+
                                '</button></button>'+
                                '<ul class="dropdown-menu" >'+
                                  '<li><a href="/admin/update/sale/'+data+'">Sale</a></li>'+
                                  '<li><a href="/admin/sale/update/order/'+data+'">Order</a></li>'+
                                  '<li><a href="/admin/sale/update/hirise/'+data+'">Hi-Rise</a></li>'+
                                  '<li><a href="/admin/sale/update/insurance/'+data+'">Insurance</a></li>'+
                                  '<li><a href="/admin/sale/update/rto/'+data+'">RTO</a></li>'+
                                  '<li><a href="/admin/sale/update/pending/item/'+data+'">Pending Items</a></li>'+
                                  '<li><a href="/admin/sale/update/otc/'+data+'">OTC</a></li>'+
                                '</ul>'+
                              '</div>'; 
                     }
                        //str += ' &nbsp<a href="/admin/sale/hirise/'+data+'"><button class="btn btn-info btn-xs"> Hirise</button></a>';
                        str += ' &nbsp<a href="/admin/sale/add/pending/details/'+data+'"><button class="btn btn-info btn-xs"> Add Pending Details</button></a>';
                   }
                    str += '&nbsp; <a href="/admin/sale/pay_detail/'+data+'"><button class="btn btn-success btn-xs"> Payment Detail</button></a>&nbsp;';
                    str += '&nbsp; <a href="/admin/sale/view/'+data+'"><button class="btn btn-success btn-xs">View</button></a>&nbsp;';
                   
                   if(!full.approve && (full.status == 'pending' || full.status == 'done')) 
                   {
                     str += ' &nbsp; <a href="#" SaleId="'+data+'"><button class="btn btn-danger btn-xs" id="cancelRequest_'+data+'" onclick="saleCancelRequest(this)"> Cancel Sale</button></a>' ;
                   }
                   return str;
                 },
                 "orderable": false
             }
           ]
         
       });
    }
    function completeSale()
    {
      $("#complete-div").css('display','block');
      $("#pending-div").css('display','none');
       $("#all-div").css('display','none');
      if(dataTable)
      {
        dataTable.destroy();
      }
      dataTable = $('#completeSale_table').DataTable({
         "processing": true,
         "serverSide": true,
         "ajax": "/admin/sale/list/api/done",
         "aaSorting": [],
         "responsive": true,
         "columns": [
             { "data": "product_name" },
             { "data": "sale_no" },
             { "data": "name" },
             { "data": "sale_date" },
             { "data": "balance" },
             { "data": "amount",
             "orderable": false },
             { "data":  "status",
              "render": function(data,type,full,meta)
                 {
                    var status = ((data == 'pending')? 'Pending' : ((data == 'cancelled')? 'Cancelled' : 
                                    ((data == 'cancelRequest')? 'Cancel Requested' : ((data == 'done') ? 'Complete' : '' ) )));
                    return status;
                 },
                 className :'status'
             },
             {
                 "targets": [ -1 ],
                 "data":"id", "render": function(data,type,full,meta)
                 {  
                   var str = '';
                   var cond = ((full.status == 'cancelRequest')? false : ((full.status == 'cancelled') ? false : true) );
                  //  console.log(cond);
                   if(cond)
                   {
                     if(parseFloat(full.total_amount) > parseFloat(full.amount) && (full.role == 'Superadmin' || full.role == 'Accountant' ))
                     {
                       str +='<a href="/admin/sale/pay/'+data+'"><button class="btn btn-info btn-xs"> Pay</button></a>';
                     }
                     if(full.role == 'Superadmin' && full.user_type == 'superadmin' )
                     {
                      // str +=  ' <a href="/admin/update/sale/'+data+'"><button class="btn btn-danger btn-xs"> Update</button></a>&nbsp;';
                      str += '<div class="btn-group">'+
                                '<button type="button" class="btn btn-danger btn-flat">Update</button>'+
                                '<button type="button" class="btn btn-danger btn-flat dropdown-toggle" data-toggle="dropdown" aria-expanded="false">'+
                                  '<span class="caret"></span>'+
                                  '<span class="sr-only">Toggle Dropdown</span>'+
                                '</button>'+
                                '<ul class="dropdown-menu" role="menu">'+
                                  '<li><a href="/admin/update/sale/'+data+'">Sale</a></li>'+
                                  '<li><a href="/admin/sale/update/order/'+data+'">Order</a></li>'+
                                  '<li><a href="/admin/sale/update/hirise/'+data+'">Hi-Rise</a></li>'+
                                  '<li><a href="/admin/sale/update/insurance/'+data+'">Insurance</a></li>'+
                                  '<li><a href="/admin/sale/update/rto/'+data+'">RTO</a></li>'+
                                  '<li><a href="/admin/sale/update/pending/item/'+data+'">Pending Items</a></li>'+
                                  '<li><a href="/admin/sale/update/otc/'+data+'">OTC</a></li>'+
                                '</ul>'+
                              '</div>'; 
                     }
                        //str += ' &nbsp<a href="/admin/sale/hirise/'+data+'"><button class="btn btn-info btn-xs"> Hirise</button></a>';
                        str += ' &nbsp<a href="/admin/sale/add/pending/details/'+data+'"><button class="btn btn-info btn-xs"> Add Pending Details</button></a>';
                   }
                    str += '&nbsp; <a href="/admin/sale/pay_detail/'+data+'"><button class="btn btn-success btn-xs"> Payment Detail</button></a>&nbsp;';
                    str += '&nbsp; <a href="/admin/sale/view/'+data+'"><button class="btn btn-success btn-xs">View</button></a>&nbsp;';
                   
                   if(!full.approve && (full.status == 'pending' || full.status == 'done')) 
                   {
                     str += ' &nbsp; <a href="#" SaleId="'+data+'"><button class="btn btn-danger btn-xs" id="cancelRequest_'+data+'" onclick="saleCancelRequest(this)"> Cancel Sale</button></a>' ;
                   }
                   return str;
                 },
                 "orderable": false
             }
             
           ]
         
       });
    }
     function pendingSale()
    {
      $("#complete-div").css('display','none');
      $("#pending-div").css('display','block');
       $("#all-div").css('display','none');
      if(dataTable)
      {
        dataTable.destroy();
      }
      dataTable = $('#pendingSale_table').DataTable({
         "processing": true,
         "serverSide": true,
         "ajax": "/admin/sale/list/api/pending",
         "aaSorting": [],
         "responsive": true,
         "columns": [
             { "data": "product_name" },
             { "data": "sale_no" },
             { "data": "name" },
             { "data": "sale_date" },
             { "data": "balance" },
             { "data": "amount",
             "orderable": false },
             { "data":  "status",
              "render": function(data,type,full,meta)
                 {
                    var status = ((data == 'pending')? 'Pending' : ((data == 'cancelled')? 'Cancelled' : 
                                    ((data == 'cancelRequest')? 'Cancel Requested' : ((data == 'done') ? 'Complete' : '' ) )));
                    return status;
                 },
                 className :'status'
             },
             {
                 "targets": [ -1 ],
                 "data":"id", "render": function(data,type,full,meta)
                 {

                  var str = '';
                   var cond = ((full.status == 'cancelRequest')? false : ((full.status == 'cancelled') ? false : true) );
                  //  console.log(cond);
                  // console.log('amount',full.amount);

                   if(cond)
                   {
                    //  console.log('amount',full.amount);
                     if(parseFloat(full.total_amount) > parseFloat(full.amount) && (full.role == 'Superadmin' || full.role == 'Accountant' ))
                     {
                       str +='<a href="/admin/sale/pay/'+data+'"><button class="btn btn-info btn-xs"> Pay</button></a>';
                     }
                      //  console.log(full.role, full.user_type);
                     if(full.role == 'Superadmin' && full.user_type == 'superadmin'  )
                     {
                      // str +=  ' <a href="/admin/update/sale/'+data+'"><button class="btn btn-danger btn-xs"> Update</button></a>&nbsp;';
                      str += '<div class="btn-group">'+
                                '<button type="button" class="btn btn-danger btn-flat">Update</button>'+
                                '<button type="button" class="btn btn-danger btn-flat dropdown-toggle" data-toggle="dropdown" aria-expanded="false">'+
                                  '<span class="caret"></span>'+
                                  '<span class="sr-only">Toggle Dropdown</span>'+
                                '</button>'+
                                '<ul class="dropdown-menu" role="menu">'+
                                  '<li><a href="/admin/update/sale/'+data+'">Sale</a></li>'+
                                  '<li><a href="/admin/sale/update/order/'+data+'">Order</a></li>'+
                                  '<li><a href="/admin/sale/update/hirise/'+data+'">Hi-Rise</a></li>'+
                                  '<li><a href="/admin/sale/update/insurance/'+data+'">Insurance</a></li>'+
                                  '<li><a href="/admin/sale/update/rto/'+data+'">RTO</a></li>'+
                                  '<li><a href="/admin/sale/update/pending/item/'+data+'">Pending Items</a></li>'+
                                  '<li><a href="/admin/sale/update/otc/'+data+'">OTC</a></li>'+
                                '</ul>'+
                              '</div>'; 
                     }
                     // str += ' <a href="/admin/sale/hirise/'+data+'"><button class="btn btn-info btn-xs"> Hirise</button></a>';
                   }
                    str += '&nbsp; <a href="/admin/sale/pay_detail/'+data+'"><button class="btn btn-success btn-xs"> Payment Detail</button></a>&nbsp;';
                    str += '&nbsp; <a href="/admin/sale/view/'+data+'"><button class="btn btn-success btn-xs">View</button></a>&nbsp;';
                   
                   if(!full.approve && (full.status == 'pending' || full.status == 'done')) 
                   {
                     str += ' &nbsp; <a href="#" SaleId="'+data+'"><button class="btn btn-danger btn-xs" id="cancelRequest_'+data+'" onclick="saleCancelRequest(this)"> Cancel Sale</button></a>' ;
                   }
                   return str;
                  //  return '<a href="/admin/sale/pay/'+data+'"><button class="btn btn-info btn-xs"> Pay</button></a>'+
                  //  ' &nbsp; <a href="/admin/sale/pay_detail/'+data+'"><button class="btn btn-success btn-xs"> Payment Detail</button></a>&nbsp;'+
                  //  ' <a href="/admin/sale/order/'+data+'"><button class="btn btn-danger btn-xs"> Update</button></a>&nbsp;'+
                  //  ' <a href="/admin/sale/hirise/'+data+'"><button class="btn btn-info btn-xs"> Hirise</button></a>'+
                  //  ' &nbsp; <a href="/admin/sale/request/cancel/'+data+'"><button class="btn btn-danger btn-xs"> Cancel Request</button></a>' ;
                 },
                 "orderable": false
             }
           ]
         
       });
    }
    allSale();

      $("#all").on('click',function(){
       
        $("#complete").removeClass('btn-color-active');
        $("#pending").removeClass('btn-color-active');
        $(this).removeClass('btn-color-unactive');
        $(this).addClass('btn-color-active');
        
        allSale();

      });
      $("#complete").on('click',function(){
        
        $("#all").removeClass('btn-color-active');
        $("#pending").removeClass('btn-color-active');
        $(this).removeClass('btn-color-unactive');
        $(this).addClass('btn-color-active');

       
        completeSale();
      });

      $("#pending").on('click',function(){
        
        $("#all").removeClass('btn-color-active');
        $("#complete").removeClass('btn-color-active');
          $(this).removeClass('btn-color-unactive');
          $(this).addClass('btn-color-active');
       
        pendingSale();
      });

   });

   function saleCancelRequest(el){
      var SaleId = parseInt($(el).parent('a').attr('SaleId'));
      // console.log(SaleId);
      // $(".datepicker").datepicker("option", "dateFormat", "yy-mm-dd");
      
      // $('.datepicker').datepicker({
      //   autoclose: true,
      //   format: 'yyyy-mm-dd HH:mm:ss'
      // }).datepicker("minDate", getFormattedDate(new Date()));
      // console.log(formatDate(new Date()));
      $('#datepicker').val(formatDate(new Date()));
      $("#SaleId").val(SaleId);
      $(document).find('textArea[name="descSaleCancel"]').parent().find('span[class="spanred"]').remove();
      $('#saleModalCenter').modal("show"); 

    }
    function formatDate(date) {
          var day = date.getDate();
          var month = date.getMonth() + 1;
          // console.log(date.getMonth());
          var year = date.getFullYear();
          var hou = date.getHours();
          var min = date.getMinutes();
          var ss = date.getSeconds();
          return year+'-'+month+'-'+day+' '+hou+':'+min+':'+ss;
    }

    function formSubmit(){
      var SaleId = parseInt($("#SaleId").val());
      var desc =$("#descSaleCancel").val().trim();
      var date =$("#datepicker").val();
        $(document).find('textArea[name="descSaleCancel"]').parent().find('span[class="spanred"]').remove();
      if(!desc)
      {
        $(document).find('textArea[name="descSaleCancel"]').parent().append('<span class="spanred">This Feild is required</span>');
        // return false;
      }
      else{
        $('#saleModalCenter').modal("hide"); 
        $("#loader").show();
        $("#js-msg-error").hide();
        $("#js-msg-success").hide();
        $.ajax({
          url: "/admin/sale/request/cancel",
          data: {
            'SaleId': SaleId,
            'desc'  : desc,
            'date'  : date
          },
          method: 'GET',
          success: function (result) {
            // console.log(result);
            var original_result = result.trim();
            if(result == 'success-direct' || result == 'success-request')
            {
              var resArr = result.split('-');
              result = resArr[0];
            }
            if(result != 'success')
            {
              $("#js-msg-error").html(result);
              $("#js-msg-error").show();
              $("#loader").hide();
            }
            else{
              $("#cancelRequest_"+SaleId).hide();
              var status  = ((resArr[1] == 'direct')? 'Cancelled' : ((resArr[1] == 'request')? 'Cancel Requested' : '' ));
              $("#cancelRequest_"+SaleId).parent().parent().siblings('.status').html(status);
              $("#js-msg-success").html('Sale Successfully Cancelled.');
              $("#js-msg-success").show();
              // console.log('success');
              $("#loader").hide();
            }
            dataTable.ajax.reload( null, false );
          },
          error: function(ex){
            $("#loader").hide();
            alert('try again');
          }
        });
      }
    }

</script>
@endsection
@section('main_section')
<section class="content">
   <div id="app">
    @include('admin.flash-message')
    <div class="alert alert-danger" id="js-msg-error" style="display: none">
    </div>
    <div class="alert alert-success" id="js-msg-success" style="display: none">
    </div>
    @yield('content')
    <!-- Default box -->
    <div class="box box-primary">
        <!-- /.box-header -->
        <div class="box-header with-border">
          {{-- 
          <h3 class="box-title">{{__('customer.mytitle')}} </h3>
          --}}
          <ul class="nav nav1 nav-pills">
            <li class="nav-item">
              <button class="nav-link1 btn-color-active" id="all" >All Sale</button>
            </li>
            <li class="nav-item">
              <button class="nav-link1 btn-color-unactive" id="complete" >Complete Sale</button>
            </li> 
            <li class="nav-item">
              <button class="nav-link1 btn-color-unactive" id="pending" >Pending Sale</button>
            </li> 
          </ul>
        </div>
        <div class="box-body" id="all-div">
          <table id="allSale_table" class="table table-bordered table-striped">
              <thead>
                <tr>
                    <th>{{__('hirise.product')}}</th>
                    <th>{{__('Sale Number')}}</th>
                    <th>{{__('Customer Name')}}</th>
                    <th>{{__('Sale Date')}}</th>
                    <th>{{__('hirise.totalamt')}}</th>
                    <th>{{__('hirise.paidamt')}}</th>
                    <th>{{__('Refunded Amount')}}</th>
                    <th>{{__('Status')}}</th>
                    <th>Action</th>
                </tr>
              </thead>
              <tbody>
              </tbody>
          </table>
        </div>
        <div id="complete-div" class="box-body" style="display:none;">
          <table id="completeSale_table" class="table table-bordered table-striped">
              <thead>
                <tr>
                    <th>{{__('hirise.product')}}</th>
                    <th>{{__('Sale Number')}}</th>
                    <th>{{__('Customer Name')}}</th>
                    <th>{{__('Sale Date')}}</th>
                    <th>{{__('hirise.totalamt')}}</th>
                    <th>{{__('hirise.paidamt')}}</th>
                    <th>{{__('Status')}}</th>
                    <th>{{__('Action')}}</th>
                </tr>
              </thead>
              <tbody>
              </tbody>
          </table>
        </div> 
        <div id="pending-div" class="box-body" style="display:none;">
          <table id="pendingSale_table" class="table table-bordered table-striped">
              <thead>
                <tr>
                    <th>{{__('hirise.product')}}</th>
                    <th>{{__('Sale Number')}}</th>
                    <th>{{__('Customer Name')}}</th>
                    <th>{{__('Sale Date')}}</th>
                    <th>{{__('hirise.totalamt')}}</th>
                    <th>{{__('hirise.paidamt')}}</th>
                    <th>{{__('Status')}}</th>
                    <th>{{__('Action')}}</th>
                </tr>
              </thead>
              <tbody>
              </tbody>
          </table>
        </div> 
        <!-- /.box-body -->
    </div>
    <div class="modal fade" id="saleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="margin-top: 200px!important;">
          <div class="modal-header">
            <h4 class="modal-title" id="exampleModalLongTitle">Cancel Sale</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <form id="my-form"  method="POST" >
              @csrf
              <div class="row">
                <div class="col-md-12">
                    <label>Date <sup>*</sup></label>
                      <input type="text" value="" name="date" class="form-control " readonly id="datepicker" required >
                    <br>
                </div>
              </div>
              <div class="row">
                <div class="col-md-12">
                    <label>Description <sup>*</sup></label>
                    <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
                    <input id="SaleId" type="hidden" name="SaleId" class="form-control" hidden>
                    <textarea name="descSaleCancel" class="form-control" id="descSaleCancel" required rows="6"></textarea>
                    <br>
                </div>
              </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="button" id="btnUpdate" class="btn btn-success" onclick="formSubmit()">Submit</button>
          </div>
          </form>
        </div>
      </div>
    </div>
   </div>
   <!-- /.box -->
</section>
@endsection