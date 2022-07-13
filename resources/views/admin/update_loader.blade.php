@extends($layout)

@section('title', __('loader.title_update'))

@section('breadcrumb')
    <li><a href="#"><i class=""></i> {{__('loader.title_update')}} </a></li>
    
@endsection
@section('js')
@endsection

@section('main_section')
    <section class="content">
            @include('admin.flash-message')
                @yield('content')
            <!-- general form elements -->
            <div class="box-header with-border">
            <div class="box box-primary">
                <div class="box-header">
                </div>  
                <form id="unload-data-validation" action="/admin/loader/update/{{$id}}" method="post">
                    @csrf
                    @foreach ($loader as $item)
                        
                    @endforeach
                    <div class="row">
                        <div class="col-md-6">
                            <label>{{__('loader.loader_type')}} <sup>*</sup></label>
                            <input type="text" name="loader_type" disabled class="input-css" value="{{ $item->type }}">
                            {!! $errors->first('loader_type', '<p class="help-block">:message</p>') !!}
                        </div>
                        <div class="col-md-6">
                            <label>{{__('loader.loader_truknumber')}} <sup>*</sup></label>
                            <input type="text" name="loader_truknumber" disabled class="input-css" value="{{ $item->truck_number }}">
                            {!! $errors->first('loader_truknumber', '<p class="help-block">:message</p>') !!}
                        </div>
                    </div><br>
                    <div class="row">
                        <div class="col-md-6">
                           <label>{{__('loader.loader_capacity')}} <sup>*</sup></label>
                            <input type="number" name="loader_capacity" class="input-css" value="{{ $item->capacity }}" min="1">
                            {!! $errors->first('loader_capacity', '<p class="help-block">:message</p>') !!}
                        </div>
                        <div class="col-md-6">
                            <label>{{__('loader.status')}} <sup>*</sup></label>
                                <select  class="input-css select2 status" name="status">
                                    <option value="" disabled>Select status</option>
                                     <option value="Active" {{$item->status=="Active" ? 'selected=selected' : ''}}>Active</option>
                                    <option value="Inactive" {{$item->status=="Inactive" ? 'selected=selected' : ''}}>InActive</option>
                                   
                                   
                                </select>
                                {!! $errors->first('status', '<p class="help-block">:message</p>') !!}
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