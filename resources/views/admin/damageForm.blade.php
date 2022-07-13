@extends($layout)

@section('title', __('Damage Claim'))

@section('breadcrumb')
    <li><a href="#"><i class=""></i> {{__('Damage Claim')}} </a></li>
@endsection

@section('js')
<!-- <script src="/js/pages/stock_movement.js"></script> -->
@endsection

@section('main_section')
    <section class="content">
            <!-- general form elements -->
            <div id="app">
                @include('admin.flash-message')
                @yield('content')
            </div>
                <div class="box box-primary">
                <form action="/admin/damage/claim/request/{{$damage_claim_id}}" method="post" id="stock-movement-validation">
                    @csrf
                        <input type="text" hidden name="damage_detail_id" value="{{$reuqestData['damage_detail_id']}}">
                        <div class="row">
                            <div class="col-md-4">
                                <label>Product Name<sup>*</sup></label>
                            <input type="text" disabled name="product" value="{{$reuqestData['product_name']}}-{{$reuqestData['color_code']}}" class="input-css" value="{{old('product')}}">
                                {!! $errors->first('product', '<p class="help-block">:message</p>') !!}
                            </div>
                        
                            <div class="col-md-4">
                                <label>Frame Number<sup>*</sup></label>
                                <input type="text" disabled name="frame" value="{{$reuqestData['frame']}}" class="input-css" value="{{old('frame')}}">
                                {!! $errors->first('frame', '<p class="help-block">:message</p>') !!}
                            </div>
                        
                            <div class="col-md-4">
                                <label>Engine Number<sup>*</sup></label>
                            <input type="text" disabled name="engine" value="{{$reuqestData['engine_number']}}" class="input-css" value="{{old('engine')}}">
                                {!! $errors->first('engine', '<p class="help-block">:message</p>') !!}
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label>Estimate Amount<sup>*</sup></label>
                                <input type="number" name="est_amount" class="input-css" value="{{old('est_amount')}}">
                                {!! $errors->first('est_amount', '<p class="help-block">:message</p>') !!}
                            </div>
                            <div class="col-md-6">
                                <label>Repair Amount<sup></sup></label>
                                <input type="number" name="rep_amount" class="input-css" value="{{old('rep_amount')}}">
                                {!! $errors->first('rep_amount', '<p class="help-block">:message</p>') !!}
                            </div>
                        
                            <div class="col-md-6">
                                <label>Description<sup>*</sup></label>
                                <input type="textArea" name="description" class="input-css" value="{{old('description')}}">
                                {!! $errors->first('description', '<p class="help-block">:message</p>') !!}
                            </div>
                        </div>
                         <div class="submit-button">
                        <button type="submit" class="btn btn-primary mb-2">Submit</button>
                        </div>
                        
                    </form>
                </div>
      </section>
@endsection