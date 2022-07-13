@extends($layout)

@section('title', __('Update Hi-Rise'))

@section('breadcrumb')
    <li><a href="/admin/sale/list"><i class=""></i> {{__('Update Hi-Rise')}} </a></li>
    
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


                <form id="unload-data-validation" action="/admin/sale/update/hirise/{{$saleData->id}}" method="post">
                    @csrf
                     <div class="row">
                        <div class="col-md-4">
                            <input type="hidden" name="sale_id" value="{{$saleData->id}}">
                            <input type="hidden" name="showroom_price" value="{{$saleData->ex_showroom_price}}">
                            <input type="hidden" name="hirise_id" value="@if(isset($hiriseData->id)){{$hiriseData->id}} @endif" class="input-css " >
                            <label>Sale Number : {{$saleData->sale_no}}</label>
                        </div>
                         <div class="col-md-4">
                            <label>{{__('hirise.showroom')}} : {{$saleData->ex_showroom_price}}</label>
                        </div>
                        <div class="col-md-4">
                            <label>{{__('hirise.totalamt')}} : {{$saleData->balance}}</label>
                        </div>
                    </div><br>

                    <div class="row">
                        <div class="col-md-6">
                            <label>{{__('hirise.invoice')}} <sup>*</sup></label>
                            <input type="text" name="invoice" value="@if(isset($hiriseData->invoice)){{$hiriseData->invoice}} @endif" class="input-css " >
                            {!! $errors->first('invoice', '<p class="help-block">:message</p>') !!}
                        </div>
                    </div><br>
                    <div class="row">
                        <div class="col-md-6">
                            <label>{{__('hirise.amount')}} <sup>*</sup></label>
                             <input type="text" name="amount"value="@if(isset($hiriseData->amount)){{$hiriseData->amount}} @endif" class="input-css " >
                            {!! $errors->first('amount', '<p class="help-block">:message</p>') !!}
                        </div>
                    </div><br>
                    <input type="hidden" hidden readonly name="ew_invoice" value="{{(isset($extendedWarrantyData['id'])) ? 'yes' : 'no' }}">
                    @if(isset($extendedWarrantyData['id']))
                    <div class="row">
                        <div class="col-md-6">
                            <label>{{__('hirise.extended_warranty_invoice')}} <sup>*</sup></label>
                             <input type="text" name="extended_warranty_invoice"value="@if(isset($extendedWarrantyData->invoice_number)){{$extendedWarrantyData->invoice_number}}@endif" class="input-css " >
                            {!! $errors->first('extended_warranty_invoice', '<p class="help-block">:message</p>') !!}
                        </div>
                    </div><br>
                    @endif
                    <div class="row">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-success">Update</button>
                        </div>
                        <br><br>    
                    </div>  
                </form>
                
            </div>
      </section>
@endsection