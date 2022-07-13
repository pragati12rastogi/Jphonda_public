@extends($layout)

    @section('title', __('hirise.security_pay'))



@section('breadcrumb')
    <li><a href="/admin/sale/security/amount/list"><i class=""></i> {{__('Security Amount List')}} </a></li>    
    <li><a href="#"><i class=""></i> {{__('hirise.security_pay')}} </a></li>    
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
            <div class="box box-primary">
                <div class="box-header">
                </div>  

                <form id="my-form" action="/admin/sale/security/amount/pay" method="post">
                    @csrf
                    <div class="row">
                        @foreach ($securityData as $security)
                        <div class="col-md-4">
                            <input type="hidden" name="sale_id" value="{{$security->sale_id}}">
                            <input type="hidden" name="security_id" value="{{$security->id}}">
                            <input type="hidden" name="store_id" value="{{$security->store_id}}">
                            <label>{{__('hirise.security_no')}} : @if(isset($security->security_number)){{$security->security_number}} @endif</label>
                        </div>
                         <div class="col-md-4">
                            <label>{{__('hirise.paidamt')}} : @if(isset($paid)){{$paid}} @endif</label>
                        </div>
                    @endforeach
                       
                    </div><br>
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