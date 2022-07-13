@extends($layout)

@section('title', __('parts.requistion'))

@section('breadcrumb')
<li><a href="#"><i class="">{{__('parts.mytitle')}}</i></a></li>
<li><a href="#"><i class="">{{__('parts.create_requistion')}}</i></a></li>
@endsection

@section('css')
<!-- <link rel="stylesheet" href="/css/party.css"> -->
@endsection

@section('js')
<script src="/js/pages/part.js"></script> 
@endsection

@section('main_section')
<section class="content">
    <div id="app">
        @include('admin.flash-message')
        @yield('content')
    </div>
    <!-- Default box -->
    <div class="box box-primary">
        <div class="box-header with-border"> <br>
            
                <form  action="/admin/part/requisition"  method="POST" id="form" files="true">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 {{ $errors->has('requisition_type') ? 'has-error' : ''}}">
                            <div class="form-group">
                                <label>Type<sup>*</sup></label>
                                <select class="input-css select2 requisition_type"  id="requisition_type" style="width: 100%;" name="requisition_type">
                                        <option disabled selected value>Select Type</option>
                                            <option value="enquiry">Enquiry</option>
                                            <option value="purchase">Purchase</option>
                                </select>
                                <label id="requisition_type-error" class="error" for="requisition_type"></label>
                            </div>
                            {!! $errors->first('requisition_types', '<p class="help-block">:message</p>') !!}
                        </div>
                        <!--col-md-4-->
                        <div class="col-md-6 {{ $errors->has('store') ? 'has-error' : ''}}">
                            <div class="form-group">
                                <label>{{__('admin.store')}} <sup>*</sup></label>
                                <select  name="store" class="input-css select2 store" id="store" data-placeholder="Select" style="width: 100%;">
                                    <option value="">Select Store</option>
                                   @foreach ($store as $key)
                                <option value="{{$key->id}}" {{old('store')==$key->id ? 'selected=selected' : ''}}>{{$key->name.'-'.$key->store_type}}</option>
                                   @endforeach
                                </select>
                                <label id="store-error" class="error" for="store"></label>
                            </div>
                                {!! $errors->first('store', '<p class="help-block">:message</p>') !!}
                            </div>
                        <!--col-md-4-->
                        <!--col-md-4-->
                    </div>
                    <div class="row">
                        <div class="col-md-6 {{ $errors->has('model_name') ? 'has-error' : ''}}">
                            <label>Model Name<sup>*</sup></label>
                            <input type="text" class="input-css" name="model_name"
                                value="{{ old('model_name') }}">
                            {!! $errors->first('model_name', '<p class="help-block">:message</p>') !!}
                        </div>
                        <!--col-md-4-->
                        <div class="col-md-6 {{ $errors->has('color') ? 'has-error' : ''}}">
                            <label>Color <sup>*</sup></label>
                            <input autocomplete="off" type="text" class="input-css" name="color"
                                value="{{ old('color') }}">
                            {!! $errors->first('color', '<p class="help-block">:message</p>') !!}
                        </div>
                    </div>

                        <div class="row row">
                            <div class="box-footer">
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </div>
                        </div>
        
                </form>
            </div>
            <!--end of container-fluid-->
        </div>
        <!------end of box box-default---->
    
    <!--end of box-header with-border-->
</section>
<!--end of section-->
@endsection
{{-- {{CustomHelpers::coolText('hcjsd')}} --}}