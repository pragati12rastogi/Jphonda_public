@extends($layout)

@section('title', __('Create Asset Category'))

@section('breadcrumb')
    <li><a href="#"><i class=""></i> {{__('Create Asset Category')}} </a></li>
    
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
                <form id="unload-data-validation" action="/admin/assets/category" method="post">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <label>Category Name <sup>*</sup></label>
                            <input type="text" name="category_name" class="input-css" >
                            {!! $errors->first('category_name', '<p class="help-block">:message</p>') !!}
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