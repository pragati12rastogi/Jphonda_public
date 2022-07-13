@extends($layout)

@section('title', __('Call Data Import'))

@section('breadcrumb')
    <li><a href="#"><i class=""></i> {{__('Call Data Import')}} </a></li>
    
@endsection
@section('js')
<script>

</script>
@endsection

@section('main_section')
    <section class="content">
            @include('admin.flash-message')
                @yield('content')
            <!-- general form elements -->
            <div class='box'> <br>
            <div class="box-header with-border">
                <h3 class="box-title" style="font-size: 20px;font-weight:600;margin-left:20px">Import Call Data
                </h3><br><br>
                <div class="box with-transitions box-body">
                   <form files="true" enctype="multipart/form-data" action="/admin/call/data/import" method="POST" id="form">
                       @csrf
                      
                        <div class="row">
                            <div class="col-md-6">
                                <label>{{__('Select Call Type')}} <sup>*</sup></label>
                                <select name="call_type" class="input-css select2" style="width:100%">
                                        <option value="">Select Call Type</option>
                                        @foreach($call_type as $key => $val)
                                            <option value="{{$key}}" {{(old('lrn') == $key)? 'selected': '' }}>{{$val}}</option>
                                        @endforeach
                                    </select>
                                {!! $errors->first('call_type', '<p class="help-block">:message</p>') !!}
                            </div>
                            <div id="import_excel" class="col-md-6" >
                                <div class="col-md-12 {{ $errors->has('excel') ? 'has-error' : ''}}" >
                                    <label>{{__('Upload Excel')}} <sup>*</sup></label>
                                    <input type="file" name="excel" id="excel_data" />
                                    <!-- <small class="text-muted">Accepted File Format : xls, xlt, xltm, xltx, xlsm and xlsx </small> -->
                                    {!! $errors->first('excel', '<p class="help-block">:message</p>') !!}
                                    <br>
                                    <p>Download Sample ? 
                                        <a href="{{route('sampleImport',['filename'  => 'CallDataImport.xlsx' ])}}">
                                            <button type="button" class="btn btn-primary"> <i class="fa fa-download"></i> </button>
                                    </a>
                                </div>
                            </div>
                        </div>
                                <div class="margin">
                                    <button type="submit" class="btn btn-success">Upload</button>
                                </div>
                       <!--submit button row-->
                   </form>
                   <!--end of container-fluid-->
                </div>
               <!------end of box box-default---->
            </div>
        </div>
      </section>
@endsection
{{-- {{CustomHelpers::coolText('hcjsd')}} --}}