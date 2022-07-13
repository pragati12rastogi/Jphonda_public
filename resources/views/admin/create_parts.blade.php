@extends($layout)

@section('title', __('parts.title'))

@section('breadcrumb')
    <li><a href="/admin/stock/parts/create"><i class=""></i> {{__('parts.title')}} </a></li>
    
@endsection
@section('js')
<script>
$('.datepicker2').datepicker({
    format: "yyyy",
    viewMode: "years", 
    minViewMode: "years"
});
</script>
@endsection

@section('main_section')
    <section class="content">
            @include('admin.flash-message')
                @yield('content')
            <!-- general form elements -->
            <div class="box box-primary">
                <div class="box-header">
<!--                    <h3 class="box-title">{{__('parts.mytitle')}} </h3>-->
                </div>  
            <form id="unload-data-validation" action="/admin/stock/parts/create" method="post">
                @csrf
                <div class="row">
                        <div class="col-md-6">
                            <label>{{__('parts.refer')}} <sup>*</sup></label>
                            <select name="loadRefNum" class="input-css select2" style="width:100%">
                                    <option value=" ">Select Load Reference</option>
                                    @foreach($load as $key)
                                        <option value="{{$key['id']}}" {{(old('store') == $key['id'])? 'selected':''}}>{{$key['load_referenace_number']}}</option>
                                    @endforeach
                                </select>
                            {!! $errors->first('loadRefNum', '<p class="help-block">:message</p>') !!}
                        </div>
                        
                        <div class="col-md-6">
                            <label>{{__('parts.model_cat')}} <sup>*</sup></label>
                            <input type="text" name="model_cat" class="input-css" value="{{old('model_cat')}}">
                            {!! $errors->first('model_cat', '<p class="help-block">:message</p>') !!}
                        </div>
                </div><br>
                <div class="row">
                        <div class="col-md-6">
                            <label>{{__('parts.model_name')}} <sup>*</sup></label>
                            <input type="text" name="model_name" class="input-css" value="{{old('model_name')}}">
                            {!! $errors->first('model_name', '<p class="help-block">:message</p>') !!}
                        </div>
                        <div class="col-md-6">
                                <label>{{__('parts.model_var')}} <sup>*</sup></label>
                                <input type="text" name="model_var" class="input-css" value="{{old('model_var')}}">
                                {!! $errors->first('model_var', '<p class="help-block">:message</p>') !!}
                            </div>
                </div><br>
                <div class="row">
                        <div class="col-md-6">
                            <label>{{__('parts.color')}} <sup>*</sup></label>
                            <input type="text" name="color" class="input-css" value="{{old('color')}}">
                            {!! $errors->first('color', '<p class="help-block">:message</p>') !!}
                        </div>
                        <div class="col-md-6">
                                <label>{{__('parts.part')}} <sup>*</sup></label>
                                <input type="text" name="part" class="input-css" value="{{old('part')}}">
                                {!! $errors->first('part', '<p class="help-block">:message</p>') !!}
                            </div>
                </div><br>
                <div class="row">
                        <div class="col-md-6">
                            <label>{{__('parts.type')}} <sup>*</sup></label>
                            <select name="type" class="input-css select2">
                                    <option value="">Select Type</option>
                                    <option value="battery" {{old('type')=="battery" ? 'selected=selected' : ''}}>Battery</option> 
                                </select>
                            {!! $errors->first('type', '<p class="help-block">:message</p>') !!}
                        </div>
                        <div class="col-md-6">
                                <label>{{__('parts.part_type')}} <sup>*</sup></label>
                                <select name="part_type" class="input-css select2">
                                        <option value="">Select Part Type</option>
                                        <option value="small" {{old('part_type')=="small" ? 'selected=selected' : ''}}>Small</option> 
                                    </select>
                                {!! $errors->first('part_type', '<p class="help-block">:message</p>') !!}
                            </div>
                </div><br>
                <div class="row">
                        <div class="col-md-6">
                            <label>{{__('parts.status')}} <sup>*</sup></label>
                            <select name="status" class="input-css select2">
                                    <option value="">Select Status</option>
                                    <option value="ok" {{old('status')=="ok" ? 'selected=selected' : ''}}>Ok</option>
                                    <option value="expired" {{old('status')=="expired" ? 'selected=selected' : ''}}>Expired</option>
                                   
                                </select>
                            {!! $errors->first('status', '<p class="help-block">:message</p>') !!}
                        </div>
                        <div class="col-md-6">
                            <label>{{__('parts.mfh_d')}} <sup>*</sup></label>
                            <input type="text" name="mfh_d" class="input-css datepicker1" value="{{old('mfh_d')}}">
                            {!! $errors->first('mfh_d', '<p class="help-block">:message</p>') !!}
                        </div>
                        {{-- <div class="col-md-6">
                                <label>{{__('parts.mfh_y')}} <sup>*</sup></label>
                                <input type="text" name="mfh_y" class="input-css datepicker2" value="{{old('mfh_y')}}">
                                {!! $errors->first('mfh_y', '<p class="help-block">:message</p>') !!}
                            </div> --}}
                </div><br>
                <div class="row">
                        
                       
                </div>
             <br>
                <div class="row">
                        <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">Submit</button>
                                </div>
                                <br><br>    
                </div>  
                </form>
            </div>


        
      </section>
@endsection
{{-- {{CustomHelpers::coolText('hcjsd')}} --}}