@extends($layout)

    @section('title', __('hirise.sale_pay'))



@section('breadcrumb')
    <li><a href="#"><i class=""></i> {{__('hirise.sale_pay')}} </a></li>    
@endsection

@section('js')

<script>
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
               @include('layouts.booking_tab')
            </div>
            <div class="box box-primary">
                <div class="box-header">
                </div>  

                {{-- <form id="my-form" action="/admin/sale/pay" method="post"> --}}
                    {{-- @csrf --}}
                    <div class="row">
                        @foreach ($saleData as $book)
                        <div class="col-md-3">
                            <input type="hidden" name="sale_id" value="{{$book->id}}">
                            <input type="hidden" name="store_id" value="{{$book->store_id}}">
                            <label>{{__('hirise.sale_no')}} : @if(isset($book->sale_no)){{$book->sale_no}} @endif</label>
                        </div>
                        <div class="col-md-3">
                            <label>{{__('hirise.totalamt')}} : @if(isset($book->balance)){{$book->balance}} @endif</label>
                        </div>
                         <div class="col-md-3">
                            <label>{{__('hirise.paidamt')}} : @if(isset($paid)){{$paid}} @endif</label>
                        </div>
                         <div class="col-md-3">
                            <label>{{__('Balance')}} : @if(isset($paid)){{$book->balance - $paid}} @endif</label>
                        </div>
                        @if($pending_am > 0)
                        <div class="col-md-3">
                            <label>{{__('Pending Amount For Confirmation')}} : {{$pending_am}}</label>
                        </div>
                        @endif
                        {{-- <div class="col-md-3">
                            <label>{{__('Security Amount')}} : {{$secAmt}} </label>
                        </div> --}}
                        @endforeach
                       
                    </div><br>
                    @if(($book->balance - $paid) > 0 && isset($pay_req_id->id))
                    <div class="row margin">
                        <label>
                            Make Payment :- <a href="/admin/payment/request/list?type=sale&&typeId={{$book->id}}"> Click Here.. </a>
                        </label>
                    </div>
                    @endif
                    {{-- <div class="row">
                        <div class="col-md-6">
                            <label for="security_amount">Security Amount</label>
                            <div class="col-md-6">
                                <input type="radio" name="security_amount" id="security_amount" value="yes"> Yes
                            </div>
                            <div class="col-md-6">
                                <input type="radio" checked name="security_amount" id="security_amount" value="no"> No
                            </div>
                        </div>
                        {!! $errors->first('security_amount', '<p class="help-block">:message</p>') !!}

                    </div> --}}
                    {{-- <div class="row">
                        
                        <div class="col-md-6">
                            <label>{{__('hirise.payment_mode')}} <sup>*</sup></label>
                            <select name="payment_mode" class="input-css select2" style="width:100%" id="payment_mode">
                                <option value="">Select Payment Mode</option>
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
                            <label>{{__('hirise.receiver_bank_detail')}}</label>
                            <input type="text" name="receiver_bank_detail" class="input-css" >
                            {!! $errors->first('receiver_bank_detail', '<p class="help-block">:message</p>') !!}
                        </div>
                        <div class="col-md-6">
                            <label>{{__('hirise.enter_amt')}} <sup>*</sup></label>
                            <input type="number" name="amount" class="input-css" >
                            {!! $errors->first('amount', '<p class="help-block">:message</p>') !!}
                        </div>
                    </div><br>
                    <div class="row">
                         
                        
                    </div><br>
                    <div class="row">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-success">Pay</button>
                        </div>
                        <br><br>    
                    </div>  
                </form> --}}
            </div>
      </section>
@endsection