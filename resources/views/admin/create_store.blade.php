@extends($layout)

@section('title', __('store.title'))

@section('breadcrumb')
    <li><a href="#"><i class=""></i> {{__('store.title')}} </a></li>
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
                   <form action="/admin/store/create" method="post" id="stock-movement-validation">
                    @csrf
                        <div class="row">
                            <div class="col-md-12">
                                <label>Name<sup>*</sup></label>
                                <input type="text" name="name" class="input-css" value="{{old('name')}}">
                                {!! $errors->first('name', '<p class="help-block">:message</p>') !!}
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                            <label>Mobile<sup>*</sup></label>
                            <input type="mobile"  name="mobile" class="input-css mobile" id="mobile" value="{{old('mobile')}}" />
                                {!! $errors->first('mobile', '<p class="help-block">:message</p>') !!}
                          </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <label>City<sup>*</sup></label>
                                <input type="text" name="city" class="input-css" value="{{old('city')}}">
                                {!! $errors->first('city', '<p class="help-block">:message</p>') !!}
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <label>Address<sup>*</sup></label>
                                <input type="text" name="address" class="input-css" value="{{old('address')}}">
                                {!! $errors->first('address', '<p class="help-block">:message</p>') !!}
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <label>Store Type <sup>*</sup></label>
                                <select name="store_type" class="input-css select2 selectpicker" value="{{old('store_type')}}" style="width:100%">
                                    <option disabled selected value>Select Store Type</option>
                                    <option value="warehouse" {{(old('store_type') == 'warehouse')? 'selected':''}}>Warehouse</option>
                                    <option value="showroom" {{(old('store_type') == 'showroom')? 'selected':''}}>Showroom</option>
                                </select>
                                {!! $errors->first('store_type', '<p class="help-block">:message</p>') !!}
                            </div>   
                        </div>
                         <div class="submit-button">
                        <button type="submit" class="btn btn-primary mb-2">Submit</button>
                        </div>
                    </form>
                </div>
      </section>
@endsection