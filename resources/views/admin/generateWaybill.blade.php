@extends($layout)

@section('title', __('Generate E-WayBill'))

@section('breadcrumb')
    <li><a href="#"><i class=""></i> {{__('Generate E-WayBill')}} </a></li>
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
                <form action="/admin/stock/movement/accountant/waybill/{{$stock_id}}" method="post" id="stock-movement-validation">
                    @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <label>E-WayBill Number<sup>*</sup></label>
                                <input type="text" name="waybill" class="input-css" value="{{old('waybill')}}">
                                {!! $errors->first('waybill', '<p class="help-block">:message</p>') !!}
                            </div>
                        </div>
                         <div class="submit-button">
                        <button type="submit" class="btn btn-primary mb-2">Submit</button>
                        </div>
                    </form>
                </div>
      </section>
@endsection