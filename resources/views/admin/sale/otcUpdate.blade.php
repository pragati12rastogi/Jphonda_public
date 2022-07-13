@extends($layout)

@section('title', __('OTC'))

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
                <form id="unload-data-validation" action="/admin/sale/update/otc/{{$sale_id}}" method="post">
                    @csrf
                    <input type="hidden" hidden name="sale_id" id="sale_id" value="{{$sale_id}}">
                    <div class="row">
                        <div class="col-md-4">
                            <label>Sale Number : {{$sale_no}}</label>
                        </div>
                        <br>
                        <br>
                        <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <label>{{__('Invoice Number')}} <sup>*</sup></label>
                            <input type="text" name="invoice_no" id="invoice_no" value="{{(($oldData)? $oldData->invoice_no : '')}}" class="input-css">
                            {!! $errors->first('invoice_no', '<p class="help-block">:message</p>') !!}
                        </div>
                        <div class="col-md-6">
                            <label>{{__('Date')}} <sup>*</sup></label>
                            <input type="text" name="date" id="date" value="{{(($oldData)? $oldData->date : '')}}" class="datepicker input-css">
                            {!! $errors->first('date', '<p class="help-block">:message</p>') !!}
                        </div>
                        <div class="col-md-6">
                            <label>{{__('Amount')}} <sup>*</sup></label>
                            <input type="text" name="amount" id="amount" value="{{(($oldData)? $oldData->amount : '')}}" class="input-css">
                            {!! $errors->first('amount', '<p class="help-block">:message</p>') !!}
                        </div>
                    </div><br>
                   <br>
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