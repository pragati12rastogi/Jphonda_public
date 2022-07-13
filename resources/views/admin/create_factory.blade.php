@extends($layout)

@section('title', __('factory.title_create'))

@section('breadcrumb')
    <li><a href="#"><i class=""></i> {{__('factory.title_create')}} </a></li>
    
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
            <div class="box-header with-border">
            <div class="box box-primary">
                <div class="box-header">
                </div>  
                <form id="unload-data-validation" action="/admin/factory/create" method="post">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <label>{{__('factory.factory_name')}} <sup>*</sup></label>
                            <input type="text" name="factory_name" class="input-css" >
                            {!! $errors->first('factory_name', '<p class="help-block">:message</p>') !!}
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