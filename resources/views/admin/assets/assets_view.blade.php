@extends($layout)

@section('title', 'Assets View')

{{-- TODO: fetch from auth --}}
@section('user', Auth::user()->name)

@section('breadcrumb')
<li><a href="{{url('/admin/assets/list')}}"><i class=""></i>Assets List</a></li>
    <li><a href="#"><i class=""></i>Assets View</a></li>
   
@endsection
@section('css')
<style>
    input[type=number]::-webkit-inner-spin-button, 
    input[type=number]::-webkit-outer-spin-button { 
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        margin: 0; 
    }
</style>
@endsection
@section('js')
<script src="/js/Employee/assets.js"></script>
@endsection
@section('main_section')
    <section class="content">
        <!-- Default box -->
        <div id="app">
                @include('admin.flash-message')
                @yield('content')
            </div>
      
        @csrf

       <div class="box box-header">
           
        <div class="row" >
            <div class="col-md-6 {{ $errors->has('assets_category') ? ' has-error' : ''}}">
                <label for="">Assets Category <sup>*</sup></label>
                <p>{{$assets->category_name}}</p>
               
            </div>
            <div class="col-md-6 {{ $errors->has('assets_name') ? ' has-error' : ''}}">
                <label for="">Assets Name <sup>*</sup></label>
                <p>{{$assets->name}}</p>
            </div>
            
     </div><br><br>
     <div class="row" >
            <div class="col-md-6 {{ $errors->has('assets_brand') ? ' has-error' : ''}}">
                <label for="">Assets Brand <sup>*</sup></label>
                <p>{{$assets->brand}}</p>
            </div>
            <div class="col-md-6 {{ $errors->has('assets_number') ? ' has-error' : ''}}">
                <label for="">Assets Model Number <sup>*</sup></label>
                <p>{{$assets->model_number}}</p>
            </div>
     </div><br><br>
     <div class="row" >
            <div class="col-md-6 {{ $errors->has('assets_desc') ? ' has-error' : ''}}">
                <label for="">Assets Description <sup>*</sup></label>
                <p>{{$assets->description}}</p> 
            </div>
            <div class="col-md-6 {{ $errors->has('assets_bill_no') ? ' has-error' : ''}}">
                <label for="">Assets Bill Number <sup>*</sup></label>
                <p>{{$assets->asset_bill_no}}</p>
            </div>
     </div><br><br>
     <div class="row" >
        <div class="col-md-6 {{ $errors->has('assets_purchase_date') ? ' has-error' : ''}}">
            <label for="">Assets Purchase Date <sup>*</sup></label>
           <p>{{$assets->purchase_date}}</p>
        </div>
        <div class="col-md-6 {{ $errors->has('assets_value') ? ' has-error' : ''}}">
            <label for="">Assets Value <sup>*</sup></label>
           <p>{{$assets->asset_value}}</p> 
        </div>
        
    </div><br><br>
    <div class="row" >
        <div class="col-md-6 {{ $errors->has('upd_assets_photo') ? ' has-error' : ''}}">
            <label for="">Assets Photo Upload </label>
            @if($assets->asset_photo_upload != "" || $assets->asset_photo_upload != null)
                @if (file_exists(public_path().'/upload/assets/'.$assets->asset_photo_upload ))
                    <img src="{{ asset('upload/assets')}}/{{$assets->asset_photo_upload}}" height="50" width="100" data-toggle="modal" data-target="#myModal_img">
                @endif
            @endif
            
        </div>
        <div class="col-md-6 {{ $errors->has('upd_assets_bill') ? ' has-error' : ''}}">
            <label for="">Assets Bill Upload </label>
            @if($assets->asset_bill_upload != "" || $assets->asset_bill_upload != null)
                @if (file_exists(public_path().'/upload/assets/'.$assets->asset_bill_upload ))
                    <?php $ext = pathinfo(storage_path().'/upload/assets/'.$assets->asset_bill_upload, PATHINFO_EXTENSION); ?>
                    @if ($ext == 'pdf')
                        <a href="{{ asset('upload/assets')}}/{{$assets->asset_bill_upload}}" target="_blank" data-toggle="modal" data-target="#myModal_ad">See Asset Bill</a>
                    @else
                        <img src="{{ asset('upload/assets')}}/{{$assets->asset_bill_upload}}" height="50" width="100"
                        target="_blank" data-toggle="modal" data-target="#myModal_ad">
                    @endif
                @endif
            @endif
            
        </div>
       <div id="myModal_ad" class="modal fade" role="dialog">
                    <div class="modal-dialog modal-lg">
        
                        <!-- Modal content-->
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                <h4 class="modal-title">Asset Bill</h4>
                            </div>
                            <div class="modal-body" style="overflow:auto;height:450px;">
                               
                   @if($assets->asset_bill_upload != "" || $assets->asset_bill_upload != null)
                    @if (file_exists(public_path().'/upload/assets/'.$assets->asset_bill_upload ))
                        <?php $ext = pathinfo(storage_path().'/upload/assets/'.$assets->asset_bill_upload, PATHINFO_EXTENSION); ?>
                        @if ($ext == 'pdf')
                        
                            <embed src="{{ asset('upload/assets')}}/{{$assets->asset_bill_upload}}" frameborder="0" width="100%" height="400px" >
                        @else
                            <img src="{{ asset('upload/assets')}}/{{$assets->asset_bill_upload}}" width="100%">
                        @endif
                    @endif
                @endif

                        </div>
                            <div class="modal-footer">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                </div>
                        </div>
                    </div>
                </div>
                <div id="myModal_img" class="modal fade" role="dialog">
                    <div class="modal-dialog modal-lg">
        
                        <!-- Modal content-->
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                <h4 class="modal-title">Asset Image</h4>
                            </div>
                            <div class="modal-body" style="overflow:auto;height:450px;">
                               
                 @if($assets->asset_photo_upload != "" || $assets->asset_photo_upload != null)
                @if (file_exists(public_path().'/upload/assets/'.$assets->asset_photo_upload ))
                    <img src="{{ asset('upload/assets')}}/{{$assets->asset_photo_upload}}"  width="100%" data-toggle="modal" data-target="#myModal_ad">
                @endif
            @endif
                        </div>
                            <div class="modal-footer">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                </div>
                        </div>
                    </div>
                </div>
    </div><br><br>
       </div>
       
       
      
      </section>
@endsection
