@extends($layout)

@section('title', __('Update Part Price'))

@section('breadcrumb')
    <li><a href="#"><i class=""></i> {{__('Update Part Price')}} </a></li>

@endsection
@section('js')
{{-- <script src="/js/pages/product.js"></script> --}}

<script>
    $(document).ready(function() {
    });
</script>

@endsection
@section('main_section')
<section class="content">
        @include('admin.flash-message')
                @yield('content')
   <!-- Default box -->
   <div class="box-header with-border">
       <div class='box box-default'> <br>
           {{-- <h2 class="box-title" style="font-size: 28px;margin-left:20px">Import Part Data</h2><br><br><br> --}}
           <div class="container-fluid">
            <form files="true" enctype="multipart/form-data" action="/admin/part/update/price" method="POST" >
                @csrf
               
                <div class="row" >
                 <div id="" class="col-md-6"  >
                     <label for="excel">Update Part Price<sup>*</sup></label>
                       <input type="file" name="excel" id="" class="input-css" />
                       <!-- <small class="text-muted">Accepted File Format : xls, xlt, xltm, xltx, xlsm and xlsx </small> -->
                     {!! $errors->first('excel', '<p class="help-block">:message</p>') !!}
                     <br>
                     <p>Download Sample ? 
                         <a href="{{route('sampleImport',['filename'  => 'samplePartUpdateImport.xls' ])}}">
                             <button type="button" class="btn btn-primary"> <i class="fa fa-download"></i> </button>
                     </a>
                 </div>
                     <br />
                 <div class="box-footer">
                     <button type="submit" class="btn btn-success">Submit</button>
                 </div>
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
