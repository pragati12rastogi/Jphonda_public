@extends($layout)

@section('title', __('hirise.order'))

@section('breadcrumb')
    <li><a href="#"><i class=""></i> {{__('hirise.order')}} </a></li>
    
@endsection
@section('main_section')
    <section class="content">
        
            @include('admin.flash-message')
                @yield('content')
            <!-- general form elements -->
            <div class="box-header with-border">
               @include('layouts.booking_tab')

            <div class="box box-primary">
                <div class="box-header">
                </div>  
                <form id="unload-data-validation" action="/admin/sale/order" method="post">
                    @csrf
                    <div class="row">
                        <div class="col-md-4">
                            <input type="hidden" name="sale_id" value="{{$saleData->id}}">
                            <input type="hidden" name="customer_id" value="{{$saleData->customer_id}}">
                            <input type="hidden" name="store_id" value="{{$saleData->store_id}}">
                            <input type="hidden" name="total_amount" value="{{$saleData->balance}}">
                            <label>Sale Number : {{$saleData->sale_no}}</label>
                        </div>
                        <div class="col-md-4">
                            <label>Total Amount : {{$saleData->balance}}</label>
                        </div>
                   
                    <div class="row">
                        <div class="col-md-6">
                            <label>{{__('hirise.product')}} <sup>*</sup></label>
                            <input type="text" name="product_name" value="{{$saleData->model_category}}- {{$saleData->model_variant}}-{{$saleData->model_name}} " class="input-css" readonly="">
                            {!! $errors->first('product_name', '<p class="help-block">:message</p>') !!}
                        </div>
                         <div class="col-md-6">
                            <label>{{__('hirise.frame')}} <sup>*</sup></label>
                             <select name="frame" class="input-css select2" style="width:100%">
                              <option disabled="">Select Frame Number</option>
                              @foreach($frame_no as $frame)
                                @if(isset($orderData['product_frame_number']))
                                    <option value="{{$frame->frame}}" {{(old('frame')) ? ((old('frame') == $frame->frame) ? 'selected' : '' ) : ((($orderData['product_frame_number']) == $frame->frame) ? 'selected' : '' ) }} >{{$frame->frame.' ( '.$frame->frame_duration.' Days )'}}</option>
                                @else
                                    <option value="{{$frame->frame}}" {{(old('frame') == $frame->frame) ? 'selected' : '' }} >{{$frame->frame.' ( '.$frame->frame_duration.' Days )'}}</option>
                                @endif                           
                              @endforeach
                             </select>
                            {!! $errors->first('frame', '<p class="help-block">:message</p>') !!}
                        </div>
                    </div><br>
                    <div class="row">
                        <div class="col-md-6">
                            <label>{{__('hirise.store')}} <sup>*</sup></label>
                            <input type="text" name="store_name" value="{{$saleData->store_name}}-{{$saleData->store_type}}" class="input-css" readonly="">
                            {!! $errors->first('store_name', '<p class="help-block">:message</p>') !!}
                        </div>
                        <div class="col-md-6">
                            <label>{{__('hirise.customer')}} <sup>*</sup></label>
                            <input type="text" name="customer_name" value="{{$saleData->customer_name}}" class="input-css" readonly="">
                            {!! $errors->first('customer_name', '<p class="help-block">:message</p>') !!}
                        </div>
                    </div><br>
                    <div class="row">
                        @if(isset($orderData['battery_id']))
                        <div class="col-md-6">
                            <label>{{__('hirise.battery_no')}} <sup>*</sup></label>
                            <input type="text" name="battery_no" readonly value="@if(isset($orderData['battery_number'])) {{$orderData['battery_number']}} @endif" class="input-css" >
                            {!! $errors->first('battery_no', '<p class="help-block">:message</p>') !!}
                        </div>
                        @else
                        <div class="col-md-6">
                            <label>{{__('hirise.battery_no')}} <sup>*</sup></label>
                            <select name="battery_no" class="select2 input-css" id="battery_no">
                                <option value="">Select Battery Number</option>
                                @foreach($battery as $key => $val)  
                                <option value="{{$val->id}}" @if(isset($orderData['battery_id'])) {{(($orderData['battery_id'] == $val->id) ? 'selected' : '' )}} @endif > {{$val->frame}}{{' ('.(($val->year == 0)? '': $val->year.' year').' '.(($val->month == 0)? '': $val->month.' month').' '.(($val->day == 0)? '': $val->day.' day').')'}} </option>
                                @endforeach
                            </select>
                            {!! $errors->first('battery_no', '<p class="help-block">:message</p>') !!}
                        </div>
                        @endif
                        <div class="col-md-6">
                            <label>{{__('hirise.key_no')}} <sup>*</sup></label>
                            <input type="text" name="key_no" value="@if(isset($orderData['key_number'])){{$orderData['key_number']}} @endif" class="input-css" >
                            {!! $errors->first('key_no', '<p class="help-block">:message</p>') !!}
                        </div>
                    </div><br>
                    <div class="row">
                        {{-- <div class="col-md-6">
                            <label>{{__('hirise.tyre_no')}} <sup>*</sup></label>
                            <input type="text" name="tyre_no" value="@if(isset($orderData['tyre_number'])){{$orderData['tyre_number']}} @endif" class="input-css" >
                            {!! $errors->first('tyre_no', '<p class="help-block">:message</p>') !!}
                        </div> --}}
                        <div class="col-md-6">
                            <label>{{__('hirise.tyre_make')}} <sup>*</sup></label>
                            <select name="tyre_make" id="tyre_make" class="input-css select2">
                                <option value="">Select Tyre Maker</option>
                                @foreach($tyre as $key => $val)
                                    <option value="{{$val->key}}" {{ (isset($orderData['tyre_make'])) ? (($orderData['tyre_make'] == $val->key) ? 'selected' : '' ) : ((old('tyre_make') == $val->key) ? 'selected' : '' ) }} >{{$val->value}}</option>
                                @endforeach
                            </select>
                            {{-- <input type="text" name="tyre_make" value="@if(isset($orderData['tyre_make'])){{$orderData['tyre_make']}} @endif" class="input-css" > --}}
                            {!! $errors->first('tyre_make', '<p class="help-block">:message</p>') !!}
                        </div>
                    </div><br>
                    <div class="row">
                        <div class="col-md-6">
                            <label>{{__('hirise.front_tyre_no')}} <sup>*</sup></label>
                            <input type="text" name="front_tyre_no" value="@if(isset($orderData['front_tyre_no'])){{$orderData['front_tyre_no']}}@endif" class="input-css" >
                            {!! $errors->first('front_tyre_no', '<p class="help-block">:message</p>') !!}
                        </div>
                        
                        <div class="col-md-6">
                            <label>{{__('hirise.rear_tyre_no')}} <sup>*</sup></label>
                            <input type="text" name="rear_tyre_no" value="@if(isset($orderData['rear_tyre_no'])){{$orderData['rear_tyre_no']}}@endif" class="input-css" >
                            {!! $errors->first('rear_tyre_no', '<p class="help-block">:message</p>') !!}
                        </div>
                    </div><br>
                    <div class="row">
                        {{-- <div class="col-md-6">
                            <label>{{__('hirise.front_tyre_make')}} <sup>*</sup></label>
                            <input type="text" name="front_tyre_make" value="@if(isset($orderData['front_tyre_make'])){{$orderData['front_tyre_make']}} @endif" class="input-css" >
                            {!! $errors->first('front_tyre_make', '<p class="help-block">:message</p>') !!}
                        </div> --}}
                        {{-- <div class="col-md-6">
                            <label>{{__('hirise.rear_tyre_make')}} <sup>*</sup></label>
                            <input type="text" name="rear_tyre_make" value="@if(isset($orderData['rear_tyre_make'])){{$orderData['rear_tyre_make']}} @endif" class="input-css" >
                            {!! $errors->first('rear_tyre_make', '<p class="help-block">:message</p>') !!}
                        </div> --}}
                    </div><br>
                    {{-- <div class="row">
                        <div class="col-md-6">
                            <label>{{__('hirise.pending_item')}} <sup>*</sup></label>
                            <select name="pending_item[]" class="form-control select2" multiple="multiple" data-placeholder="Select sub type"
                        style="width: 100%;">
                       @foreach($accessories as $acc)
                                @if(in_array($acc->id,$pending_item))
                                <option value="{{$acc->id}}" selected>{{$acc->name}}</option>
                                @else
                                <option value="{{$acc->id}}" >{{$acc->name}}</option>
                                @endif
                              @endforeach
                            </select>
                            {!! $errors->first('pending_item', '<p class="help-block">:message</p>') !!}
                        </div>
                    </div> --}}
                    <br>
                    <div class="row">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-success">Submit</button>
                        </div>
                        <br><br>    
                    </div>  
                </form>
            </div>
      </section>
@endsection