@extends($layout)

@section('title', __('Leave Balance'))

@section('js')
<script src="/js/pages/hr.js"></script>
<script>
  
</script>
@endsection

@section('breadcrumb')
    <li><a href="#"><i class=""></i> {{__('Leave Balance')}} </a></li>
@endsection

@section('main_section')
    <section class="content"> 
         <div id="app">
                    @include('admin.flash-message')
                    @yield('content')
            </div>
            <div class="box box-primary" style="padding-bottom: 25px;">
                <div class="box-header with-border">
                </div>  
               <form id="my-form" files="true" enctype="multipart/form-data" action="" method="POST">
                @csrf
                <div class="row">
                        <div class="col-md-6">
                            <label>Import <sup>*</sup></label>
                            <input type="file" name="leave_balance" class="input-css" value="{{old('leave_balance')}}">
                            {!! $errors->first('leave_balance', '<p class="help-block">:message</p>') !!}
                            <br>
                            <p>Download Sample ? 
                                <a href="{{route('sampleImport',['filename'  => 'LeavebalanceSample.xlsx' ])}}">
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