@extends($layout)
@section('title', __('Sale'))
@section('breadcrumb')
<li><a href="#"><i class=""></i>{{__('Sale')}}</a></li>
@endsection
@section('css')
<link rel="stylesheet" href="/css/responsive.bootstrap.css">
<style>
  .content{
   padding: 30px;
 }
 .nav1>li>button {
   position: relative;
   display: block;
   padding: 10px 34px;
   background-color: white;
   margin-left: 10px;
}

@media (max-width: 768px)  
 {
   
   .content-header>h1 {
     display: inline-block;
    
   }
 }
 @media (max-width: 425px)  
 {
  
   .content-header>h1 {
     display: inline-block;
     
   }
 }
 .nav1>li>button.btn-color-active{
  background-color: rgb(135, 206, 250);
 }
 .nav1>li>button.btn-color-unactive{
  background-color: white;
 }
 .spanred{
   color: red;
 }
</style>
@endsection
@section('js')
<script src="/js/dataTables.responsive.js"></script>
{{-- <script src="/js/pages/sale/cancelSaleList.js"></script> --}}
<script>
   var dataTable;
   
    
   // Data Tables
   $(document).ready(function() {

    $("#js-msg-error").hide();
    $("#js-msg-success").hide();


    function allSale()
    {

      // $("#complete-div").css('display','none');
      $("#all-div").css('display','block');
      if(dataTable)
      {
        dataTable.destroy();
      }
      dataTable = $('#allSale_table').DataTable({
         "processing": true,
         "serverSide": true,
         "ajax": "/admin/cancel/sale/list/api",
         "aaSorting": [],
         "responsive": true,
         "columns": [
             { "data": "req_num" },
             { "data": "sale_no" },
             { "data": "reason",
                "orderable": false },
             { "data": "name" },
             { "data": "req_date" },
             { "data": "payIn","orderable": false },
             { "data": "payOut","orderable": false },
             { "data":  "status",
              "render": function(data,type,full,meta)
                 {
                    var status = ((data == 'cancelled')? 'Cancelled' : 
                                    ((data == 'cancelRequest')? 'Cancel Requested' : '' ));
                    return status;
                 },
                 className :'status'
             },
             {
                 "targets": [ -1 ],
                 "data":"id", "render": function(data,type,full,meta)
                 {
                    var str = '';
                    if(full.cancel_status == 'pending'  && (full.status != 'pending' || full.status != 'done') && (full.role == 'Superadmin')) 
                    {  
                     str += ' <a href="#" RequestId="'+data+'"><button class="btn btn-danger delete btn-xs" id="cancelRequest_'+data+'" onclick="saleCancelRequest(this)">Approve Request</button></a>' ;
                    }
                    else if(full.cancel_status == 'approve' && full.status == 'cancelled' && full.cancel_type == 'custom')
                    {
                      if(parseFloat(full.payIn) > parseFloat(full.payOut))
                      {
                        str += ' <a href="/admin/cancelSale/refundPage/'+full.saleId+'" saleId="'+full.saleId+'"><button class="btn btn-info delete btn-xs" id="cancelRequestPay_'+full.saleId+'" onclick="saleCancelRequestPay(this)">Refund Payment</button></a>' ; 
                      }
                    }
                    str += '<input id="reqAndSaleId" type="hidden" saleId="'+full.saleId+'" reqId="'+data+'">'
                   return str;
                 },
                 "orderable": false
             }
           ]
         
       });
    }
   
    allSale();

      $("#all").on('click',function(){
       
          $(this).addClass('btn-color-active');
        
        allSale();

      });
      // $("#complete").on('click',function(){
        
      //     $(this).addClass('btn-color-active');
       
      //   completeSale();
      // });

   });

   function saleCancelRequest(el){
    	
     if(confirm('Are You sure, Approve this Request ?'))
     {
        //  console.log('yes');
        var req_id = $(el).parent('a').attr('RequestId');
        var approve = approveRequest(req_id);
     }
     else{
      //  console.log('no');
     }
      // var SaleId = parseInt($(el).parent('a').attr('SaleId'));
      // // console.log(SaleId);
      // // $(".datepicker").datepicker("option", "dateFormat", "yy-mm-dd");
      
      // // $('.datepicker').datepicker({
      // //   autoclose: true,
      // //   format: 'yyyy-mm-dd HH:mm:ss'
      // // }).datepicker("minDate", getFormattedDate(new Date()));
      // // console.log(formatDate(new Date()));
      // $('#datepicker').val(formatDate(new Date()));
      // $("#SaleId").val(SaleId);
      // $(document).find('textArea[name="descSaleCancel"]').parent().find('span[class="spanred"]').remove();
      // $('#exampleModalCenter').modal("show"); 

    }
    function approveRequest(req_id)
    {
      $("#js-msg-error").hide();
      $("#js-msg-success").hide();
      $("#loader").show();
      $.ajax({
          url: "/admin/sale/request/cancel/approve",
          data: {
            'reqId': parseInt(req_id)
          },
          method: 'GET',
          success: function (result) {
            console.log(result);
            if(result != 'success' && result != 'error')
            {
              $("#js-msg-error").html(result);
              $("#js-msg-error").show();
            }
            else if(result == 'error')
            {
              $("#js-msg-error").html('Try Again, Internal Error');
              $("#js-msg-error").show();
            }
            else{
              $("#cancelRequest_"+req_id).hide();
              var status  = 'Cancelled' ;
              saleId = parseInt($("#reqAndSaleId").attr('saleId'));
              refundButton = ' <a href="/admin/cancelSale/refundPage/'+saleId+'" saleId="'+saleId+'"><button class="btn btn-info delete btn-xs" id="cancelRequestPay_'+saleId+'" onclick="saleCancelRequestPay(this)">Refund Payment</button></a>' ; 
              $("#cancelRequest_"+req_id).parent().parent().append(refundButton);
              $("#cancelRequest_"+req_id).parent().parent().siblings('.status').html(status);
              $("#js-msg-success").html('Approved Successfully');
              $("#js-msg-success").show();
              // console.log('success');
            }
          },
          error: function(ex){
            $("#loader").hide();
            alert('try again');
          }
        }).done(function(){
          $("#loader").hide();
        });
    }
   

    

</script>
@endsection
@section('main_section')
<section class="content">
   <div id="app">
    @include('admin.flash-message')
    <div class="alert alert-danger" id="js-msg-error">
    </div>
    <div class="alert alert-success" id="js-msg-success">
    </div>
    @yield('content')
    <!-- Default box -->
    <div class="box box-primary">
        <!-- /.box-header -->
        <div class="box-header with-border">
          {{-- 
          <h3 class="box-title">{{__('customer.mytitle')}} </h3>
          --}}
          {{-- <ul class="nav nav1 nav-pills">
            <li class="nav-item">
              <button class="nav-link1 btn-color-active" id="all" >All Sale</button>
            </li> --}}
            {{-- <li class="nav-item">
              <button class="nav-link1 btn-color-unactive" id="complete" >Complete Sale</button>
            </li> --}}
          {{-- </ul> --}}
        </div>
        <div class="box-body" id="all-div">
          <table id="allSale_table" class="table table-bordered table-striped"> 
              <thead>
                <tr>
                    <th>{{__('Request Number')}}</th>
                    <th>{{__('Sale Number')}}</th>
                    <th>{{__('Reason')}}</th>
                    <th>{{__('Seller Name')}}</th>
                    <th>{{__('Request Date')}}</th>
                    <th>{{__('Refund Amount')}}</th>
                    <th>{{__('Refunded Amount')}}</th>
                    <th>{{__('Status')}}</th>
                    <th>Action</th>
                </tr>
              </thead>
              <tbody>
              </tbody>
          </table>
        </div>
        {{-- <div id="complete-div" class="box-body" style="display:none;">
          <table id="completeSale_table" class="table table-bordered table-striped">
              <thead>
                <tr>
                    <th>{{__('hirise.product')}}</th>
                    <th>{{__('Sale Number')}}</th>
                    <th>{{__('Sale Date')}}</th>
                    <th>{{__('hirise.totalamt')}}</th>
                    <th>{{__('hirise.paidamt')}}</th>
                    
                </tr>
              </thead>
              <tbody>
              </tbody>
          </table>
        </div> --}}
        <!-- /.box-body -->
    </div>
    <div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
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
                    <textarea name="descSaleCancel" class="form-control" id="descSaleCancel" required rows="6">
                    </textarea>
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