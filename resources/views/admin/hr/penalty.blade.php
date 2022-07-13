@extends($layout)

@section('title', __('Penalty'))

@section('js')
<script src="/js/pages/hr.js"></script>
<script>
  
</script>
@endsection

@section('breadcrumb')
    <li><a href="#"><i class=""></i> {{__('Penalty')}} </a></li>
@endsection

@section('main_section')
    <section class="content"> 
         <div id="app">
                    @include('admin.flash-message')
                    @yield('content')
            </div>
            <!-- general form elements -->
            <div class="box box-primary" style="padding-bottom: 25px;">
                <div class="box-header with-border">
<!--         <h3 class="box-title">{{__('customer.mytitle')}} </h3>-->
                </div>  
               <form id="my-form" files="true" enctype="multipart/form-data" action="" method="POST">
                @csrf
                <div class="row">
                        <div class="col-md-6">
                            <label>Import <sup>*</sup></label>
                            <input type="file" name="penalty" class="input-css" value="{{old('penalty')}}">
                            {!! $errors->first('penalty', '<p class="help-block">:message</p>') !!}
                            <br>
                        </div>
                </div>
                
                <div class="submit-button">
                <button type="submit" class="btn btn-primary">Submit</button>
                    
                </div>
                 </form>                 
            </div>
        
      </section>
@endsection