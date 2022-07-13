@extends($layout)

@section('title', __('hirise.create_ins'))

@section('breadcrumb')
    <li><a href="#"><i class=""></i> {{__('hirise.create_ins')}} </a></li>
    
@endsection

@section('main_section')
    <section class="content">
            @include('admin.flash-message')
                @yield('content')
            <!-- general form elements -->
            <div class="box-header with-border">
                  @include('layouts.booking_tab')
            <div class="box box-primary"> 
                <div class="box box-body">
                    <form id="unload-data-validation" action="/admin/sale/insurance" method="post">
                        @csrf
                        <div class="row">
                            <div class="col-md-4">
                                <input type="hidden" name="sale_id" value="{{$bookingData->id}}">
                                <label>Sale Number : {{$bookingData->sale_no}}</label>
                            </div>
                        </div><br>
                        <div class="row">
                            <label>Insurance</label>
                            <div class="row">
                                <div class="col-md-6">
                                    <label>{{__('hirise.ins_co')}} <sup>*</sup></label>
                                    <input type="text" name="ins_co" readonly value="@if(isset($insData->insurance_co)){{$insData->insur_company_name}}@endif" class="input-css" >
                                    {!! $errors->first('ins_co', '<p class="help-block">:message</p>') !!}
                                </div>
                                <div class="col-md-6">
                                    <label>{{__('hirise.inspolicy')}} <sup>*</sup></label>
                                    <input type="text" name="ins_policy" value="@if(isset($insData->policy_number)){{$insData->policy_number}}@endif" class="input-css " >
                                    {!! $errors->first('ins_policy', '<p class="help-block">:message</p>') !!}
                                </div>
                            </><br>
                            <div class="row">
                                <div class="col-md-6">
                                    <label>{{__('hirise.ins_type')}} <sup>*</sup></label>
                                    <input type="text" name="ins_type" readonly value="@if(isset($insData->insur_type)){{$insData->insur_type}}@endif" class="input-css" >
                                    {!! $errors->first('ins_type', '<p class="help-block">:message</p>') !!}
                                </div>
                                <div class="col-md-6">
                                    <label>{{__('hirise.ins_amount')}} <sup>*</sup></label>
                                    <input type="text" name="ins_amount" readonly value="@if(isset($insData->insurance_amount)){{$insData->insurance_amount}}@endif" class="input-css " >
                                    {!! $errors->first('ins_amount', '<p class="help-block">:message</p>') !!}
                                </div>
                            </div><br>
                        </div>
                        @if(!empty($insData->cpa_company))
                        <div class="row">
                            <label>CPA Cover</label>
                            <div class="row">
                                <div class="col-md-6">
                                    <label>{{__('hirise.ins_co')}} <sup>*</sup></label>
                                    <input type="text" name="cpa-ins_co" readonly value="@if(isset($insData->cpa_company)){{$insData->cpa_company_name}}@endif" class="input-css" >
                                    {!! $errors->first('cpa-ins_co', '<p class="help-block">:message</p>') !!}
                                </div>
                                @if($insData->insurance_co != $insData->cpa_company)
                                <div class="col-md-6">
                                    <label>{{__('hirise.inspolicy')}} <sup>*</sup></label>
                                    <input type="text" name="cpa-ins_policy" value="@if(isset($insData->cpa_policy_number)){{$insData->cpa_policy_number}}@endif" class="input-css " >
                                    {!! $errors->first('cpa-ins_policy', '<p class="help-block">:message</p>') !!}
                                </div>
                                @endif
                                <div class="col-md-6">
                                    <label>{{__('hirise.ins_amount')}} <sup>*</sup></label>
                                    <input type="text" name="cpa-ins_amount" readonly value="@if(isset($insData->cpa_amount)){{$insData->cpa_amount}}@endif" class="input-css " >
                                    {!! $errors->first('cpa-ins_amount', '<p class="help-block">:message</p>') !!}
                                </div>
                            </div><br>
                        </div>
                        @endif
                        <div class="row">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-success">Submit</button>
                            </div>
                            <br><br>    
                        </div>  
                    </form>
                </div>

            </div>
      </section>
@endsection