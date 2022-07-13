@extends($layout)

@section('title', __('OTC'))

@section('breadcrumb')
    <li><a href="#"><i class=""></i> {{__('OTC')}} </a></li>
    
@endsection
@section('js')
<script>

    $(document).ready(function(){

        $("input[name='multi_invoice']").on('change',function(){
            var val = $(this).val();
            $(".all_invoice").hide();
            $("#"+val).show();
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
               @include('layouts.booking_tab')

            <div class="box box-primary">
                <div class="box-header">
                </div>  
                <form id="unload-data-validation" action="/admin/sale/otc" method="post">
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
                        @foreach($oldData as $k => $v)
                            <div class="col-md-2">
                                <label> <input type="radio" name="multi_invoice" {{(!isset($check_new_invoice->new_invoice_osd_id) ? (($k > 0)?'':'checked') : '' )}} class="multi_invoice" value="{{$v->id}}">  Invoice {{$k+1}} </label>
                            </div>
                        @endforeach
                        @if(isset($check_new_invoice->new_invoice_osd_id))
                        <div class="col-md-2">
                            <label> <input type="radio" name="multi_invoice" checked class="multi_invoice" value="new_invoice">  Invoice {{count($oldData)+1}} </label>
                        </div>
                        @endif
                    </div>

                    <div class="row">
                        @foreach($oldData as $k => $v)
                        <div class="col-md-6 all_invoice" style="display:{{(!isset($check_new_invoice->new_invoice_osd_id) ? (($k > 0)?'none':'') : 'none' )}}" id="{{$v->id}}">
                            <div class="col-md-12">
                                <label>{{__('Invoice Number')}} <sup>*</sup></label>
                                <input type="text" name="invoice_no_{{$v->id}}" readonly value="{{(($v->invoice_no)? $v->invoice_no : '')}}" class="input-css">
                            </div>
                            <div class="col-md-12">
                                <label>{{__('Date')}} <sup>*</sup></label>
                                <input type="text" name="date_{{$v->id}}"  readonly value="{{(($v->date)? $v->date : '')}}" class="datepicker input-css">
                            </div>
                            <div class="col-md-12">
                                <label>{{__('Amount')}} <sup>*</sup></label>
                                <input type="text" name="amount_{{$v->id}}" readonly  value="{{(($v->amount)? $v->amount : '')}}" class="input-css">
                            </div>
                        </div>
                        @endforeach
                        @if(isset($check_new_invoice->new_invoice_osd_id))
                        <div class="col-md-6 all_invoice" style="display:{{(isset($check_new_invoice->new_invoice_osd_id) ? '' : 'none' )}}" id="new_invoice">
                            <div class="col-md-12">
                                <label>{{__('Invoice Number')}} <sup>*</sup></label>
                                <input type="text" name="invoice_no" id="invoice_no" value="{{old('invoice_no')}}" class="input-css">
                                {!! $errors->first('invoice_no', '<p class="help-block">:message</p>') !!}
                            </div>
                            <div class="col-md-12">
                                <label>{{__('Date')}} <sup>*</sup></label>
                                <input type="text" name="date" id="date" value="{{old('date')}}" class="datepicker input-css">
                                {!! $errors->first('date', '<p class="help-block">:message</p>') !!}
                            </div>
                            <div class="col-md-12">
                                <label>{{__('Amount')}} <sup>*</sup></label>
                                <input type="text" name="amount" id="amount" value="{{old('amount')}}" class="input-css">
                                {!! $errors->first('amount', '<p class="help-block">:message</p>') !!}
                            </div>
                        </div>
                        @endif
                        <div class="col-md-6">
                            @if(isset($otc_accessories[0]))
                                <div class="col-md-12">
                                    <div class="col-md-3"><label>Name</label></div>
                                    <div class="col-md-3"><label>Part #</label></div>
                                    <div class="col-md-2"><label>Amount</label></div>
                                    <div class="col-md-2"><label>Discount %</label></div>
                                    <div class="col-md-2"><label>Balance</label></div>
                                </div>
                                @foreach($otc_accessories as $k => $v)
                                    <div class="col-md-12">
                                        <div class="col-md-3"><label class="text-label">{{$v['name']}}</label></div>
                                        <div class="col-md-3"><label class="text-label">{{$v['part_number']}}</label></div>
                                        <div class="col-md-2"><label class="text-label">{{$v['amount']}}</label></div>
                                        <div class="col-md-2"><label class="text-label">{{$v['discount_percent']}}</label></div>
                                        <div class="col-md-2"><label class="text-label">{{$v['pending_amount']}}</label></div>
                                    </div>
                                @endforeach
                                <div class="col-md-12">
                                    <hr>
                                    <div class="col-md-3"><label>Total Amount</label></div>
                                    <div class="col-md-3"><label>Total Discount</label></div>
                                    @if($otc_accessories_total['total_pending_discount'] > 0)
                                    <div class="col-md-3"><label>Pending Discount</label></div>
                                    @endif
                                    <div class="col-md-3"><label>Total Balance</label></div>
                                </div>
                                <div class="col-md-12">
                                    <div class="col-md-3"><label class="text-label">{{$otc_accessories_total['total_amount']}}</label></div>
                                    <div class="col-md-3"><label class="text-label">{{$otc_accessories_total['total_give_discount']}}</label></div>
                                    @if($otc_accessories_total['total_pending_discount'] > 0)
                                    <div class="col-md-3"><label class="text-label">{{$otc_accessories_total['total_pending_discount']}}</label></div>
                                    @endif
                                    <div class="col-md-3"><label class="text-label">{{$otc_accessories_total['balance']}}</label></div>
                                </div>
                            @endif
                        </div>
                    </div><br>
                   <br>
                    <div class="row" id="submit-btn">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-success">Submit</button>
                        </div>
                        <br><br>    
                    </div>  
                </form>
            </div>
      </section>
@endsection