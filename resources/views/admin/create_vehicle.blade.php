@extends($layout)

@section('title', __('Add Product'))

@section('breadcrumb')
    <li><a href="#"><i class=""></i> {{__('Add Product')}} </a></li>
    
@endsection
@section('js')
@endsection
@section('main_section')
@php
  $model_category='';
  $model_name='';
  $model_varient='';
  $color_code='';

  if(session()->has('error')=='This Product Already exists.')
  {
    $model_category = session()->get('model_category');
    $model_name = session()->get('model_name');
    $model_varient = session()->get('model_varient');
    $color_code = session()->get('color_code');
  }
@endphp
    <section class="content">
            @include('admin.flash-message')
                @yield('content')
            <!-- general form elements -->
            <div class="box-header with-border">
            <div class="box box-primary">
                <div class="box-header">
                </div>  
                <form id="unload-data-validation" action="/admin/master/product/create" method="post">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <label>Model Category<sup>*</sup></label>
                             <select name="model_category" id="model_category" class="input-css select2">
                               

                                <option value="">Select Model Category</option>
                                <option value="SC" {{old('model_category')=='SC'? 'selected' : ''}} {{$model_category=='SC'? 'selected' : ''}}>SC</option>
                                <option value="MC" {{old('model_category')=='MC'? 'selected' : ''}} {{$model_category=='MC'? 'selected' : ''}} >MC</option>
                            </select>
                            {!! $errors->first('model_category', '<p class="help-block">:message</p>') !!}
                        </div>
                         <div class="col-md-6">
                            <label>Model Name<sup>*</sup></label>
                            <input type="text" name="model_name" value="{{old('model_name',$model_name)}}" class="input-css" >
                            {!! $errors->first('model_name', '<p class="help-block">:message</p>') !!}
                        </div>
                    </div><br>
                          <div class="row">
                        <div class="col-md-6">
                            <label>Model Varient<sup>*</sup></label>
                            <input type="text" name="model_varient" value="{{old('model_varient',$model_varient)}}" class="input-css" >
                            {!! $errors->first('model_varient', '<p class="help-block">:message</p>') !!}
                        </div>
                         <div class="col-md-6">
                            <label>Color code<sup>*</sup></label>
                            <input type="text" name="color_code" value="{{old('color_code',$color_code)}}" class="input-css" >
                            {!! $errors->first('color_code', '<p class="help-block">:message</p>') !!}
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