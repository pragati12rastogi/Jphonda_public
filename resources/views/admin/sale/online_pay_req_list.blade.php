@extends($layout)
@section('title', __('Online Payment Request Listing'))
@section('breadcrumb')
<li><a href="#"><i class=""></i>{{__('Online Payment Request Listing')}}</a></li>
@endsection
@section('css')
<link rel="stylesheet" href="/css/responsive.bootstrap.css">
<style>

</style>
@endsection
@section('js')
<script src="/js/dataTables.responsive.js"></script>
{{-- <script src="/js/pages/sale/cancelSaleList.js"></script> --}}
<script>
   var dataTable;
   
   var error = JSON.parse($('#error-modal').val());
   if(!jQuery.isEmptyObject(error))
   {
      $('#onlinepayModalCenter').modal("show"); 
   }
    
   // Data Tables
  
    $("#js-msg-error").hide();
    $("#js-msg-success").hide();

    online_req_pay();

    function online_req_pay()
    {

      if(dataTable)
      {
        dataTable.destroy();
      }
      dataTable = $('#table').DataTable({
         "processing": true,
         "serverSide": true,
         "ajax": "/admin/online/payment/request/list/api",
         "aaSorting": [],
         "responsive": true,
         "columns": [
             { "data": "cancel_type" },
             { "data": "acc_name" },
             { "data": "ifsc" },
             { "data": "account_num" },
             { "data": "req_money"},
             { "data": "bank_name"},
             { "data": "pay_mode"},
             { "data": "trans_charge"},
             { "data": "trans_num"},
             { "data": "paid_date"},
             {
                 "targets": [ -1 ],
                 "data":"id", "render": function(data,type,full,meta)
                 {
                   var str = '';
                   if(full.role == 'Superadmin')
                   {
                     if(full.paid_date == null || full.paid_date == '')
                     {
                       str += '<button id="paidmoney" onclick="refundPay(this)" refundId="'+full.id+'" class="btn btn-info"> Pay</button>';
                     }
                   }
                    return str;
                 },
                 "orderable": false
             }
           ]
         
       });
    }

     
   

   function refundPay(el){
      // console.log('hi');
      var refundId = $(el).attr('refundId');
      $('#refundId').val(refundId);
      $("#payment_mode").select2("destroy").select2();
      $('#onlinepayModalCenter').modal("show"); 

      // $.ajax({
      //   url: "/admin/online/payment/request/pay",
      //    method:'GET',
      //    data: {'refundId':refundId},
      //    success: function(result){
      //       //console.log(result);
            
      //    },
      //    error:function(data)
      //    {

      //    }
      // });

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
          <input type="hidden" hidden name="error-modal" id="error-modal" value="{{$errors}}">
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
        <div class="box-body" id="div">
          <table id="table" class="table table-bordered table-striped"> 
              <thead>
                <tr>
                    <th>{{__('Type')}}</th>
                    <th>{{__('Account Name')}}</th>
                    <th>{{__('IFSC')}}</th>
                    <th>{{__('Account Number')}}</th>
                    <th>{{__('Requested Amount')}}</th>
                    <th>{{__('Bank Name')}}</th>
                    <th>{{__('Payment Mode')}}</th>
                    <th>{{__('Trans Charge')}}</th>
                    <th>{{__('Trans Number')}}</th>
                    <th>{{__('Trans Date')}}</th>
                    <th>Action</th>
                </tr>
              </thead>
              <tbody>
              </tbody>
          </table>
        </div>
        
        <!-- /.box-body -->
    </div>
    <div class="modal fade" id="onlinepayModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="margin-top: 200px!important;">
          <div class="modal-header">
            <h4 class="modal-title" id="exampleModalLongTitle">Online Request Payment</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <form id="my-form"  method="POST" action="/admin/online/payment/request/pay" >
              @csrf
              <div class="row">
                <input type="hidden" hidden name="refundId" id="refundId">
                <div class="col-md-6">
                    <label>{{__('hirise.payment_mode')}} <sup>*</sup></label>
                    <select name="payment_mode" id="payment_mode" class="input-css select2" style="width:100%">
                        <option value=" ">Select Payment Mode</option>
                        @foreach ($pay_mode as $mode)
                          <option value="{{$mode->key}}">{{$mode->value}}</option>
                        @endforeach
                    </select>
                    {!! $errors->first('payment_mode', '<p class="help-block">:message</p>') !!}

                </div>
                <div class="col-md-6">
                    <label>{{__('hirise.transaction_number')}} <sup>*</sup></label>
                    <input type="text" name="transaction_number" class="input-css" >
                    {!! $errors->first('transaction_number', '<p class="help-block">:message</p>') !!}
                </div>
            </div><br>
            <div class="row">
                
                <div class="col-md-6">
                    <label>{{__('hirise.transaction_charges')}} <sup>*</sup></label>
                    <input type="text" name="transaction_charges" class="input-css" >
                    {!! $errors->first('transaction_charges', '<p class="help-block">:message</p>') !!}
                </div>
                <div class="col-md-6">
                    <label>{{__('Bank Name')}} <sup>*</sup></label>
                    <input type="text" name="bank_name" class="input-css" >
                    {!! $errors->first('bank_name', '<p class="help-block">:message</p>') !!}
                </div>
            </div><br>
            {{-- <div class="row">
                
                <div class="col-md-6">
                    <label>{{__('hirise.enter_amt')}} <sup>*</sup></label>
                    <input type="number" name="amount" class="input-css" >
                    {!! $errors->first('amount', '<p class="help-block">:message</p>') !!}
                </div>
            </div><br> 
          </div> --}}
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="submit" id="btnUpdate" class="btn btn-success" >Submit</button>
          </div>
          </form>
        </div>
      </div>
    </div>
   </div>
   <!-- /.box -->
</section>
@endsection