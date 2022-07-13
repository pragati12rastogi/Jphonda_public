@extends($layout)

    @section('title', __('hirise.sale_refund'))



@section('breadcrumb')
    <li><a href="/admin/sale/list"><i class=""></i> {{__('Sale List')}} </a></li>    
    <li><a href="#"><i class=""></i> {{__('hirise.sale_refund')}} </a></li>    
@endsection

@section('js')
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

                <form id="my-form" action="/admin/sale/refund" method="post">
                    @csrf
                    <div class="row">
                        <div class="col-md-2">
                            <input type="hidden" name="sale_id" value="{{$saleData->id}}">
                            <input type="hidden" name="store_id" value="{{$saleData->store_id}}">
                            <label>{{__('hirise.sale_no')}} : @if(isset($saleData->sale_no)){{$saleData->sale_no}} @endif</label>
                        </div>
                        <div class="col-md-2">
                            <label>{{__('hirise.totalamt')}} : @if(isset($saleData->balance)){{$saleData->balance}} @endif</label>
                        </div>
                         <div class="col-md-2">
                            <label>{{__('hirise.paidamt')}} : @if(isset($paid)){{$paid}} @endif</label>
                        </div>
                         <div class="col-md-2">
                            <label>{{__('Extra Amount')}} : @if(isset($paid)){{$paid - ($saleData->balance + $saleData->refunded_amount)}} @endif</label>
                        </div>
                        <div class="col-md-2">
                            <label>{{__('Refunded Amount')}} : @if(isset($paid)){{ $saleData->refunded_amount}} @endif</label>
                        </div>
                       
                    </div><br>
                    <div class="row">
                        
                        <div class="col-md-6">
                            <label>{{__('hirise.payment_mode')}} <sup>*</sup></label>
                            <select name="payment_mode" class="input-css select2" style="width:100%">
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
                            <label>{{__('hirise.receiver_bank_detail')}} <sup>*</sup></label>
                            <input type="text" name="receiver_bank_detail" class="input-css" >
                            {!! $errors->first('receiver_bank_detail', '<p class="help-block">:message</p>') !!}
                        </div>
                    </div><br>
                    <div class="row">
                         
                        <div class="col-md-6">
                            <label>{{__('hirise.enter_amt')}} <sup>*</sup></label>
                            <input type="number" name="amount" class="input-css" >
                            {!! $errors->first('amount', '<p class="help-block">:message</p>') !!}
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