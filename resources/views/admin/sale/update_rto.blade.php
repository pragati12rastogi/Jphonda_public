@extends($layout)

@section('title', __('Update RTO'))

@section('breadcrumb')
    <li><a href="/admin/sale/list"><i class=""></i> {{__('Sale List')}} </a></li>
    
@endsection

@section('main_section')
    <section class="content">
            @include('admin.flash-message')
                @yield('content')
            <!-- general form elements -->
            <div class="box-header with-border">
                @include('layouts.updatebooking_tab')
            <div class="box box-primary">
                <div class="box-header">
                </div>  
                
                <form id="unload-data-validation" action="/admin/sale/rto" method="post">
                    @csrf
                    <div class="row">
                        <div class="col-md-4">
                            <input type="hidden" name="sale_id" value="{{$bookingData->id}}">
                            <label>Sale Number : {{$bookingData->sale_no}}</label>
                        </div>
                        <div class="col-md-4">
                            <input type="hidden" name="customer_id" value="{{$bookingData->customer_id}}">
                            <label>{{__('hirise.customer')}} : {{$bookingData->customer_name}}</label>
                        </div>
                    </div><br>
                    <div class="row">
                        <div class="col-md-6">
                            <label>{{__('hirise.rto_type')}} <sup>*</sup></label>
                            <select name="rto_type" class="input-css select2" style="width:100%">
                                <option value="">Select RTO Type</option>
                                <option value="permanent_address" {{(isset($rtoData->rto_type))? (($rtoData->rto_type == 'permanent_address') ? 'selected' : '' )  : '' }} >Permanent Address</option>
                                <option value="temporary_address"  {{(isset($rtoData->rto_type))? (($rtoData->rto_type == 'temporary_address') ? 'selected' : '' )  : '' }} >Temporary Address</option>
                             </select>
                            {!! $errors->first('rto_type', '<p class="help-block">:message</p>') !!}
                        </div>
                        <div class="col-md-6">
                            <label>{{__('hirise.rto_finance')}} <sup>*</sup></label>
                            <input type="text" name="rto_finance" value="@if(isset($rtoData->rto_finance)){{$rtoData->rto_finance}} @endif" class="input-css" >
                            {!! $errors->first('rto_finance', '<p class="help-block">:message</p>') !!}
                        </div>
                    </div><br>
                    <div class="row">
                        <div class="col-md-6">
                            <label>{{__('hirise.rto_amount')}} <sup>*</sup></label>
                            <input type="text" name="rto_amount" value="@if(isset($rtoData->rto_amount)){{$rtoData->rto_amount}} @endif"  class="input-css" >
                            {!! $errors->first('rto_amount', '<p class="help-block">:message</p>') !!}
                        </div>
                        <div class="col-md-6">
                            <label>{{__('hirise.rto_app_no')}} <sup>*</sup></label>
                            <input type="text" name="rto_app_no" value="@if(isset($rtoData->application_number)){{$rtoData->application_number}} @endif"  class="input-css" >
                            {!! $errors->first('rto_app_no', '<p class="help-block">:message</p>') !!}
                        </div>
                    </div><br>
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