@extends($layout)

    @section('title', __('Refund Money'))



@section('breadcrumb')
    <li><a href="#"><i class=""></i> {{__('Refund Money')}} </a></li>    
@endsection

@section('css')
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
   </style>
@endsection

@section('js')
<script>
    $(document).ready(function(){
        $("#online-form").hide();
    })
    $('#offline').on('click',function(){
        $("#offline-form").show();
        $("#online-form").hide();
        $(this).removeClass('btn-color-unactive');
        $(this).addClass('btn-color-active');
        $("#online").removeClass('btn-color-active');
    });
    $('#online').on('click',function(){
        $("#offline-form").hide();
        $("#online-form").show();
        $(this).removeClass('btn-color-unactive');
        $(this).addClass('btn-color-active');
        $("#offline").removeClass('btn-color-active');
    });

    function callModel(){
        $("#refundModalCenter").modal("show");
        // return false;
    }
    $("#payment_mode").on('change',function(){
        var val = $(this).val();
        if(val == 'Cash'){
            $("#bank_details").hide();
        }
        else{
            $("#bank_details").show();
        }
    });
</script>
@endsection


@section('main_section')
    <section class="content">
        
            @include('admin.flash-message')
                @yield('content')
            <!-- general form elements -->
            <div class="box-header with-border">
            <div class="box box-primary">
                <div class="box-header">
                    <ul class="nav nav1 nav-pills">
                        <li class="nav-item">
                          <button class="nav-link1 btn-color-active" id="offline" >Payment</button>
                        </li>
                        <li class="nav-item">
                          <button class="nav-link1 btn-color-unactive" id="online" >Online Request</button>
                        </li>
                    </ul>
                </div>  
                <div class="row">
                    <div class="col-md-12" >
                        <button type="buttons" class="btn btn-info" id="old_pay" onclick=" callModel(); return false; "  style="float:right;">View Refunded Amount</button>
                    </div>
                    <div class="col-md-12"><br>
                        <div class="col-md-6">
                            <div class="col-md-3" >
                                <label for="sale_no " ><u>Sale Number</u> :-</label>
                            </div>
                            <div class="col-md-3" >
                                <label >{{$saleData->sale_no}}</label>
                            </div>
                            <div class="col-md-3">
                                <label for="requested_id"><u>Requested Number</u> :-</label>
                            </div>
                            <div class="col-md-3">
                                <label>{{$saleData->req_num}}</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="col-md-3" >
                                <label for="sale_no " ><u>Sale Amount</u>:-</label>
                            </div>
                            <div class="col-md-3" >
                                <label >{{$saleData->sale_total_amount}}</label>
                            </div>
                            <div class="col-md-3" >
                                <label for="sale_no " ><u>Paid Amount</u>:-</label>
                            </div>
                            <div class="col-md-3" >
                                <label >{{$saleData->paid_amount}}</label>
                            </div>
                            <div class="col-md-2" >
                                <label for="sale_no " ><u>Refund Amount</u>:-</label>
                            </div>
                            <div class="col-md-2" >
                                <label >{{$refund_amount}}</label>
                            </div>
                            <div class="col-md-2" >
                                <label for="sale_no " ><u>Refunded Amount</u>:-</label>
                            </div>
                            <div class="col-md-2" >
                                <label >{{$refunded_amount}}</label>
                            </div>
                             <div class="col-md-2" >
                                <label for="sale_no " ><u>Balance Amount</u>:-</label>
                            </div>
                            <div class="col-md-2" >
                                <label >{{$refund_amount - $refunded_amount}}</label>
                            </div> 
                        </div>
                       
                    </div>
                </div><br>
                <div id="offline-form">

                    <form  action="/admin/cancelSale/refundPay" method="post">
                        <input type="hidden" name="sale_id" value="{{$saleData->id}}">
                        <input type="hidden" name="store_id" value="{{$saleData->store_id}}">
                        <input type="hidden" name="requested_id" value="{{$saleData->req_id}}" id="requested_id" readonly class="input-css">

                        @csrf
                        {{-- <div class="row">
                            @foreach ($saleData as $book)
                            <div class="col-md-4">
                                <input type="hidden" name="sale_id" value="{{$book->id}}">
                                <label>{{__('hirise.booking_no')}} : @if(isset($book->booking_no)){{$book->booking_no}} @endif</label>
                            </div>
                            <div class="col-md-4">
                                <label>{{__('hirise.totalamt')}} : @if(isset($book->total_amount)){{$book->total_amount}} @endif</label>
                            </div>
                        @endforeach
                            <div class="col-md-4">
                                <label>{{__('hirise.paidamt')}} : @if(isset($paid)){{$paid}} @endif</label>
                            </div>
                        </div><br> --}}
                        <div class="row">
                            
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
                            <div class="col-md-6" id="bank_details">
                                <label>{{__('hirise.receiver_bank_detail')}} <sup>*</sup></label>
                                <input type="text" name="receiver_bank_detail" class="input-css" >
                                {!! $errors->first('receiver_bank_detail', '<p class="help-block">:message</p>') !!}
                            </div>
                            <div class="col-md-6">
                                <label>{{__('hirise.enter_amt')}} <sup>*</sup></label>
                                <input type="number" name="amount" class="input-css" value="{{$refund_amount - $refunded_amount}}">
                                {!! $errors->first('amount', '<p class="help-block">:message</p>') !!}
                            </div>
                        </div><br>
                        <div class="row">
                            <div class="col-md-12">
                                <input type="submit" class="btn btn-success" value="Pay">
                            </div>
                            <br><br>    
                        </div>  
                    </form>
                </div>
                <div id="online-form">
                    <form  action="/admin/cancelSale/refundPay/onlineRequest" method="post">
                        
                        @csrf
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row" style="display:none">
                                    <div class="col-md-6">
                                        <input type="hidden" name="sale_no" value="{{$saleData->id}}" id="sale_no" readonly class="input-css">
                                    </div>
                                    <div class="col-md-6">
                                        <input type="hidden" name="requested_id" value="{{$saleData->req_id}}" id="requested_id" readonly class="input-css">
                                    </div>
                                    
                                </div>
                                <div class="col-md-6">
                                    <div class="col-md-12"><br>
                                        <label for="acc_num">Requested Money</label>
                                        <input type="text" class="input-css" name="req_money" id="req_money">
                                    </div>
                                    <div class="col-md-12"><br>
                                        <label for="acc_num">Account Number</label>
                                        <input type="text" class="input-css" name="acc_num" id="acc_num">
                                    </div>
                                    <div class="col-md-12">
                                        <label for="ifsc">IFSC Code</label>
                                        <input type="text" class="input-css" name="ifsc" id="ifsc">
                                    </div>
                                    <div class="col-md-12">
                                        <label for="acc_name">Account Name</label>
                                        <input type="text" class="input-css" name="acc_name" id="acc_name">
                                    </div>
                                </div><br>
                            </div>
                            <div class="col-md-12">
                                <div class="col-md-12"><br>
                                    <button type="submit" class="btn btn-success">Request</button>
                                </div>
                                <br>
                            </div> 
                            <br> 
                        </div>
                    </form>
                </div>
                <div class="row">           
                    <div id="refundModalCenter" class="modal  fade" role="dialog">
                        <div class="modal-dialog modal-lg">
                      
                          <!-- Modal content-->
                          <div class="modal-content">
                            <div class="modal-header">
                              <button type="button" class="close" data-dismiss="modal">&times;</button>
                              <h4 class="modal-title">Refund History</h4>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-2">
                                        <label>Payment Mode</label>
                                    </div>
                                    <div class="col-md-2">
                                        <label>Transaction Number</label>
                                    </div>
                                    <div class="col-md-2">
                                        <label>Transaction Charges</label>
                                    </div>
                                    <div class="col-md-2">
                                        <label>Receiver Bank Name</label>
                                    </div>
                                    <div class="col-md-2">
                                        <label>Status</label>
                                    </div>
                                    <div class="col-md-2">
                                        <label>Amount</label>
                                    </div>
                                </div><br>
                                <div class="row">
                                    @if($paid->toArray())
                                        @foreach($paid as $key => $item)
                                        <hr>
                                            <div class="col-md-2">
                                                <span>{{$item->payment_mode}}</span>
                                            </div>
                                            <div class="col-md-2">
                                                <span>{{$item->transaction_number}}</span>
                                            </div>
                                            <div class="col-md-2">
                                                <span>{{$item->transaction_charges}}</span>
                                            </div>
                                            <div class="col-md-2">
                                                <span>{{$item->receiver_bank_detail}}</span>
                                            </div>
                                            <div class="col-md-2">
                                                <span>{{ucfirst($item->status)}}</span>
                                            </div>
                                            <div class="col-md-2">
                                                <span>{{$item->amount}}</span>
                                            </div>
                                            <hr>
                                        @endforeach
                                    @endif
                                </div>
                            
                            </div>
                            <div class="modal-footer">
                              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            </div>
                          </div>
                      
                        </div>
                    </div>
                </div>
                <br>
            </div>
      </section>
@endsection
