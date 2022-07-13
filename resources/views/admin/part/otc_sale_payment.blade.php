@extends($layout)

    @section('title', __('OTC Sale Payment'))

@section('breadcrumb')
    <li><a href="/admin/otc/receptionist/sale/list"><i class=""></i> {{__('OTC Sale List')}} </a></li>  
    <li><a href="#"><i class=""></i> {{__('OTC Sale Payment')}} </a></li>    
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

                <form id="my-form" action="/admin/otc/sale/payment" method="post">
                    @csrf
                    <div class="row">
                            <input type="hidden" name="otc_id" value="@if(isset($id)){{$id}}@endif">
                            <input type="hidden" name="store_id" value="@if(isset($otcsale->store_id)){{$otcsale->store_id}}@endif">
                        <div class="col-md-3">
                            <label>{{__('hirise.totalamt')}} : @if(isset($otcsale->price)){{$otcsale->price*$otcsale->qty}} @endif</label>
                        </div>
                         <div class="col-md-3">
                            <label>{{__('hirise.paidamt')}} : @if(isset($paid)){{$paid}} @endif</label>
                        </div>
                         <div class="col-md-3">
                            <label>{{__('Balance')}} : @if(isset($paid)){{$otcsale->price*$otcsale->qty-$paid}} @endif</label>
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