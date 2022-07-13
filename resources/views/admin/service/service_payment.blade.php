@extends($layout)
@section('title', __('Service Payment'))
@section('breadcrumb')
    <li><a href="/admin/service/payment/list"><i class=""></i> {{__('Service Payment List')}} </a></li>  
    <li><a href="#"><i class=""></i> {{__('Service Payment')}} </a></li>    
@endsection

@section('js')
<script>
    $("document").ready(function() {
    $("#my-form").validate({
        rules: {
            transaction_charges: {
                required: true,
                number: true,
            },

        },
        messages: {
            required: "This field is mendatary**",
            password: {
                pwcheck: "use letter and digit both"
            }
        }


    });
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

                <form id="my-form" action="/admin/service/payment" method="post">
                    @csrf
                    <div class="row">
                        <div class="col-md-2">
                            <input type="hidden" name="jobcard_id" value="{{$jobcard->id}}">
                            <input type="hidden" name="store_id" value="{{$jobcard->store_id}}">
                            <label>{{__('Tag')}} : @if(isset($jobcard->tag)){{$jobcard->tag}} @endif</label>
                        </div>
                        <div class="col-md-3">
                            <label>{{__('hirise.totalamt')}} : @if(isset($total_amount)) {{$total_amount}} @endif @if($jobcard->job_card_type == 'Best Deal' || $jobcard->sub_type == 'Best Deal' || $jobcard->job_card_type == 'PDI'  || $jphonda_discount == 100) , Discount : 100% @endif</label>
                        </div>
                         <div class="col-md-2">
                            <label>{{__('hirise.paidamt')}} : @if(isset($paid)) {{$paid}} @endif </label>
                        </div>
                         <div class="col-md-2">
                            <label>{{__('Balance')}} : @if(isset($paid)){{$total_amount-$paid}} @endif</label>
                        </div>
                        <div class="col-md-2">
                            <label>{{__('Garage Charge')}} : @if(isset($jobcard->day)) {{$garaj_charge}} @endif</label>
                        </div>

                       
                    </div><br>
                    <div class="row">
                        <div class="col-md-6">
                            <label>{{__('hirise.payment_mode')}} <sup>*</sup></label>
                            <select name="payment_mode" class="input-css select2" style="width:100%">
                                <option value=" ">Select Payment Mode</option>
                                @foreach ($pay_mode as $mode)
                                    <option value="{{$mode->key}}" {{old('payment_mode') == $mode->key? 'selected':''}}>{{$mode->value}}</option>
                                @endforeach
                            </select>
                            {!! $errors->first('payment_mode', '<p class="help-block">:message</p>') !!}
                        </div>
                        <div class="col-md-6">
                            <label>{{__('hirise.transaction_number')}} <sup>*</sup></label>
                            <input type="text" name="transaction_number" value="{{old('transaction_number')}}" class="input-css" >
                            {!! $errors->first('transaction_number', '<p class="help-block">:message</p>') !!}
                        </div>
                    </div><br>
                    <div class="row">
                         
                        <div class="col-md-6">
                            <label>{{__('hirise.transaction_charges')}} <sup>*</sup></label>
                            <input type="text" name="transaction_charges" value="{{old('transaction_charges')}}" class="input-css" >
                            {!! $errors->first('transaction_charges', '<p class="help-block">:message</p>') !!}
                        </div>
                        <div class="col-md-6">
                            <label>{{__('hirise.receiver_bank_detail')}} <sup>*</sup></label>
                            <input type="text" name="receiver_bank_detail" value="{{old('receiver_bank_detail')}}" class="input-css" >
                            {!! $errors->first('receiver_bank_detail', '<p class="help-block">:message</p>') !!}
                        </div>
                    </div><br>
                    <div class="row">
                         
                        <div class="col-md-6">
                            <label>{{__('hirise.enter_amt')}} <sup>*</sup></label>
                            <input type="number" name="amount" value="{{old('amount')}}" class="input-css" >
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