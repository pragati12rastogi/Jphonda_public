@extends($layout)

@section('title', __('Create Part'))

@section('breadcrumb')
    <li><a href="/admin/part/list"><i class=""></i> {{__('Part List')}} </a></li>

@endsection
@section('js')
{{-- <script src="/js/pages/product.js"></script> --}}
<script>
</script>
@endsection
@section('main_section')
<section class="content">
        @include('admin.flash-message')
                @yield('content')
   <!-- Default box -->
    <div class="box box-primary">
       <div class='box-header with-border'> 
       </div>

        <div class="box-body margin-bottom">
               <form files="true"  enctype="multipart/form-data" action="/admin/part/stock/create" method="POST" id="form" >
                   @csrf
                    <div  class="col-md-12 margin">
                        <div id="" class="col-md-6"  >
                            <label for="excel">Import Part Data <sup>*</sup></label>
                            <input type="file" name="excel" id="excel" class="input-css" />
                            <!-- <small class="text-muted">Accepted File Format : xls, xlt, xltm, xltx, xlsm and xlsx </small> -->
                            {!! $errors->first('excel', '<p class="help-block">:message</p>') !!}
                            <br>
                            <p>Download Sample ? 
                                <a href="{{route('sampleImport',['filename'  => 'samplePartImport.xls' ])}}">
                                    <button type="button" class="btn btn-primary"> <i class="fa fa-download"></i> </button>
                            </a>
                        </div>
                        <div class="col-md-6">
                            <label for="store">Store <sup>*</sup></label>
                            @if(count($store) > 1)
                                <select name="store" id="store" class="input-css select2">
                                    <option value="">Select Store</option>
                                    @foreach($store as $key => $val)
                                        <option value="{{$val->id}}">{{$val->name}}</option>
                                    @endforeach
                                </select>
                                {!! $errors->first('store', '<p class="help-block">:message</p>') !!}

                            @else
                            <input type="text" name="store-name" readonly id="store-name" value="{{$store[0]->name}}" class="input-css">
                            <input type="hidden" hidden readonly name="store" id="store" value="{{$store[0]->id}}" class="input-css">
                            @endif

                        </div>
                            <br />
                            
                    </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-success">Submit</button>
                        </div>
                    
                   <!--submit button row-->
               </form>
           <!--end of container-fluid-->
       </div>
       <!------end of box box-default---->
   </div>
   <!--end of box-header with-border-->
</section>
<!--end of section-->
@endsection
