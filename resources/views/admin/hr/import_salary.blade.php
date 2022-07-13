@extends($layout)

@section('title', __('User Salary Import'))

@section('js')
<script src="/js/pages/hr.js"></script>

@endsection

@section('breadcrumb')
    <li><a href="#"><i class=""></i> {{__('User Salary')}} </a></li>
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
                            <input type="file" name="salary" class="input-css" value="{{old('salary')}}">
                            {!! $errors->first('salary', '<p class="help-block">:message</p>') !!}
                            <br>
                            <p>Download Sample ? 
                                <a href="{{route('sampleImport',['filename'  => 'SalarySample.xlsx' ])}}">
                                    <button type="button" class="btn btn-primary"> <i class="fa fa-download"></i> </button>
                            </a>
                        </div>
                        
                </div>
                
                <div class="submit-button">
                <button type="submit" class="btn btn-primary">Submit</button>
                    
                </div>
                 </form>                 
            </div>
        
      </section>
@endsection