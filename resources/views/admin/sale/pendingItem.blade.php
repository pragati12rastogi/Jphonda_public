@extends($layout)

@section('title', __('Pending Item'))

@section('breadcrumb')
    <li><a href="#"><i class=""></i> {{__('Pending Item')}} </a></li>
    
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
                {{-- {{$errors}} --}}
                <form id="unload-data-validation" action="/admin/sale/pending/item" method="post">
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
                                <label> Pending Item's <sup>*</sup></label>
                                <div class="col-md-6">
                                    <input type="radio" name="select_pending_item" {{(old('select_pending_item'))?((old('select_pending_item') == 'yes')?'checked': ''):(($select_pending_item == 'yes')? 'checked':'')}} value="yes" > Yes
                                </div>
                                <div class="col-md-6">
                                    <input type="radio" name="select_pending_item" {{(old('select_pending_item'))?((old('select_pending_item') == 'no')?'checked': ''):(($select_pending_item == 'no')? 'checked':'')}} value="no" > No
                                </div>
                                {!! $errors->first('select_pending_item', '<p class="help-block">:message</p>') !!}
                            </div>

                             

                        </div>
                        <br>
                    <div class="row">
                        @if(isset($accessories[0]) && count($pending_item) > 0)
                        <div class="col-md-6">
                            <label>{{__('Pending Accessories Items')}} <sup></sup></label>
                            <select name="pending_item[]" disabled aria-disabled="true" class="form-control select2" multiple="multiple" data-placeholder="Select a pending item"
                                style="width: 100%;">
                                @foreach($accessories as $acc)
                                    @if(in_array($acc->part_id,$pending_item))
                                        <option value="{{$acc->part_id}}" selected>{{$acc->accessories_name}}</option>
                                    @else
                                        <option value="{{$acc->part_id}}" >{{$acc->accessories_name}}</option>
                                    @endif
                                @endforeach
                            </select>
                            {!! $errors->first('pending_item', '<p class="help-block">:message</p>') !!}
                        </div>
                        @endif
                        <div class="col-md-6">
                            <label>{{__(' Fuel')}} <sub>(In leter.)</sub> <sup>*</sup></label>
                            <input type="text" name="fuel" id="fuel" value="@if(isset($fuel->quantity)){{$fuel->quantity}}@endif" class="form-control">
                            {!! $errors->first('fuel', '<p class="help-block">:message</p>') !!}
                        </div>
                    </div><br>
                    <div class="row">
                        <div class="col-md-6">
                            <label>{{__('Pending Addons')}} </label>
                            <select name="addon[]" class="form-control select2" multiple="multiple" data-placeholder="Select Item's"
                                style="width: 100%;">
                                @foreach($addon_all as $key => $val )
                                    @if(in_array($val['key_name'],$addon_pending_label))
                                        <option value="{{$val['key_name']}}" disabled >{{$val['prod_name']}}</option>
                                    @elseif(in_array($val['key_name'],$addon_pending))
                                        <option value="{{$val['key_name']}}" selected>{{$val['prod_name']}}</option>
                                    @else
                                        <option value="{{$val['key_name']}}">{{$val['prod_name']}}</option>
                                    @endif
                                @endforeach
                            </select>
                            {!! $errors->first('addon', '<p class="help-block">:message</p>') !!}
                        </div>
                    </div><br>
                    <div class="row">
                        <div class="col-md-6">
                            <label>{{__('Pending Other Items (If any)')}} </label>
                            <select name="other[]" class="form-control select2" multiple="multiple" data-placeholder="Select Item's"
                                style="width: 100%;">
                                @foreach($other_item as $key => $val )
                                    @if(!in_array($val,$other_pending_issued))
                                        @if(in_array($val,$pending_other))
                                        <option value="{{$val}}" selected>{{$val}}</option>
                                        @else
                                        <option value="{{$val}}" >{{$val}}</option>
                                        @endif
                                    @endif
                                @endforeach
                            </select>
                            {!! $errors->first('other', '<p class="help-block">:message</p>') !!}
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