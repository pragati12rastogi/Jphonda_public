@extends($layout)

@section('title', __('product.title'))

@section('breadcrumb')
    <li><a href="#"><i class=""></i> {{__('product.title')}} </a></li>

@endsection
@section('js')
{{-- <script src="/js/pages/product.js"></script> --}}
@endsection
@section('main_section')
<section class="content">
        @include('admin.flash-message')
                @yield('content')
   <!-- Default box -->
   <div class="box-header with-border">
       <div class='box box-default'> <br>
           <h2 class="box-title" style="font-size: 28px;margin-left:20px">Import Product </h2><br><br><br>
           <div class="container-fluid">
               <form files="true" enctype="multipart/form-data" action="/admin/product/create" method="POST" id="form">
                   @csrf
                  
                   <div class="row">
                    <div id="import_excel"  >
                        <div class="col-md-12 {{ $errors->has('excel') ? 'has-error' : ''}}" >
                          <input type="file" name="excel" id="excel_data" />
                          <!-- <small class="text-muted">Accepted File Format : xls, xlt, xltm, xltx, xlsm and xlsx </small> -->
                        {!! $errors->first('excel', '<p class="help-block">:message</p>') !!}
                        
                      </div>
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
