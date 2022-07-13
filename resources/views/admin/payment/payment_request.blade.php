@extends($layout)

    @section('title', __('Payment'))

@section('breadcrumb')
    <li><a href="/admin/payment/request/list"><i class=""></i> {{__('Payment Request List')}} </a></li>    
    <li><a href="#"><i class=""></i> {{__('Payment')}} </a></li>    
@endsection

@section('js')
<script>
    $("#payment_mode").change(function(){
        var mode = $("#payment_mode").val();
        if (mode == 'Cash') {
            $("#receiver_bank_detail").hide();
        }else{
            $("#receiver_bank_detail").show();
        }
    });
    $('.datepicker5').datepicker({
        format: "yyyy-mm-dd"
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
                </div>  

                <form id="my-form" action="/admin/payment/request" method="post">
                    @csrf
                   
                    <div class="row">
                         @if($payment->type == 'security')
                        <div class="col-md-3">
                            <label>{{__('Security Number')}} : @if(isset($details)){{$details->security_number}} @endif</label>
                        </div>
                        @endif
                         @if($payment->type == 'RcCorrection')
                            <div class="col-md-3">
                                    <label>{{__('Mistake By')}} : @if(isset($details->mistake_by)){{$details->mistake_by}} @endif</label>
                            </div>
                         @endif

                       
                        @if($payment->type == 'booking')
                        <div class="col-md-3">
                            <label>{{__('Booking Number')}} : @if(isset($details)){{$details->booking_number}} @endif</label>
                        </div>
                        @endif

                         @if($payment->type == 'sale')
                        <div class="col-md-3">
                            <label>{{__('Sale Number')}} : @if(isset($details)){{$details->sale_no}} @endif</label>
                        </div>
                        @endif
                        <div class="col-md-3">
                            <input type="hidden" name="payment_request_id" value="{{$payment->id}}">
                            <input type="hidden" name="type" value="{{$payment->type}}">
                            <input type="hidden" name="type_id" value="{{$payment->type_id}}">
                            <label>{{__('Total Amount')}} : @if(isset($payment->amount))  @if($payment->type == 'booking' || $payment->type == 'security') {{$paid}} @else {{$payment->amount}} @endif @endif</label>
                        </div>
                         <div class="col-md-3">
                            <label>{{__('hirise.paidamt')}} : @if(isset($paid)){{$paid}} @endif</label>
                        </div>

                        <div class="col-md-3">
                            <label>{{__('Balance')}} : @if(isset($paid))@if(isset($payment->amount))  @if($payment->type == 'booking' || $payment->type == 'security') {{$paid - $paid}} @else {{$payment->amount - $paid}} @endif @endif @endif</label>
                        </div>
                       
                    </div><br>
                    <div class="row">
                        @if($payment->type == 'RcCorrection')
                            @if($details->mistake_by == 'staff') 
                                <div class="col-md-3">
                                <b>Voucher Signed : &nbsp;&nbsp; </b>
                                    <input type="checkbox" name="boucher_signed" value="yes" required> Yes
                                </div>
                            @endif
                        @endif
                            <div class="col-md-6"></div>
                    </div><br>
                    <div class="row">
                        
                        <div class="col-md-6" >
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
                        <div class="col-md-6" id="receiver_bank_detail"> 
                            <label>{{__('hirise.receiver_bank_detail')}} <sup>*</sup></label>
                            <select name="receiver_bank_detail" class="input-css select2" >
                                <option selected="" disabled="">Select recierver bank</option>
                                @foreach($receiver_bank as $bank)
                                    <option value="{{$bank->key}}">{{$bank->value}}</option>
                                @endforeach
                            </select>
                            {!! $errors->first('receiver_bank_detail', '<p class="help-block">:message</p>') !!}
                        </div>
                    </div><br>
                    <div class="row">
                        <div class="col-md-6">
                            <label>{{__('hirise.enter_amt')}} <sup>*</sup></label>
                            <input type="number" name="amount" class="input-css" >
                            {!! $errors->first('amount', '<p class="help-block">:message</p>') !!}
                        </div>
                        <div class="col-md-6">
                            <label>{{__('Payment Date')}} <sup>*</sup></label>
                            <input type="text" name="pay_date" data-date-end-date="0d" class="input-css datepicker5" >
                            {!! $errors->first('pay_date', '<p class="help-block">:message</p>') !!}
                        </div>
                    </div><br>
                    <div class="row">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-success">Pay</button>
                        </div>
                        <br><br>    
                    </div>  
                </form>
            </div>
      </section>
@endsection