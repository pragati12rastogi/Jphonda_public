@extends($layout)

@section('title', 'Update Assets')

{{-- TODO: fetch from auth --}}
@section('user', Auth::user()->name)

@section('breadcrumb')
<li><a href="{{url('/admin/assets/list')}}"><i class=""></i>Assets List</a></li>
    <li><a href="#"><i class=""></i> Update Assets</a></li>
   
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
<script src="/js/pages/assets.js"></script>
<script>
$(document).ready(function(){
    
    $(".assets_purchase_date").datepicker({
        endDate:'today',
        format: 'd-m-yyyy'
    });
});

</script>
@endsection
@section('main_section')
    <section class="content">
        <!-- Default box -->
        <div id="app">
                @include('admin.flash-message')
                @yield('content')
            </div>
       <form action="/admin/assets/edit/{{$id}}" method="POST" id="form" enctype="multipart/form-data">
        @csrf

       <div class="box box-header">
           <br>
         <div class="row">
         {{--<div class="col-md-6 ">
                        <label>{{__('layout.update_reason')}}<sup>*</sup></label>
                        <input type="text" name="update_reason" required class="input-css" id="update_reason">
                        {!! $errors->first('update_reason', '<p class="help-block">:message</p>') !!}
            </div>--}}<!--col-md-4-->
             <div class="col-md-6" style="{{(count($store) == 1)? 'display:none' : 'display:block'}}">
                     <label>Store Name<sup>*</sup></label>
                     @if(count($store) > 1)
                        <select name="store_name" class="input-css select2 selectValidation" id="store_name">
                           <option value="">Select Store</option>
                           @foreach($store as $key => $val)
                           <option value="{{$val['id']}}"  {{ ( ($val['id']) == ($assets->store_id)) ? 'selected' : '' }}> {{$val['name']}} </option>
                           @endforeach
                        </select>
                     @endif
                     @if(count($store) == 1)
                        <select name="store_name" class="input-css select2 selectValidation" id="store_name">
                           <option value="{{$store[0]['id']}}" {{ ( ($store[0]['id']) == ($assets->store_id)) ? 'selected' : '' }} >{{$store[0]['name']}}</option>
                        </select>
                     @endif
                     {!! $errors->first('store_name', '
                     <p class="help-block">:message</p>
                     ') !!}
                  </div>
         </div>
                    <br>
        <div class="row" >
            <div class="col-md-6 {{ $errors->has('assets_category') ? ' has-error' : ''}}">
                <label for="">Assets Category <sup>*</sup></label>
                <select class="input-css assets_category select2" style="padding-top:2px" name="assets_category" >
                    <option value="">--Select Category--</option>
                    @foreach($asset_category as $ac){
                    <option value="{{$ac->ac_id}}" {{ ( ($ac->ac_id) == ($assets->asset_category_id)) ? 'selected' : '' }}>{{ $ac->category_name}}</option>
                    @endforeach
                </select>
                {!! $errors->first('assets_category', '<p class="help-block">:message</p>') !!} 
            </div>
            <div class="col-md-6 {{ $errors->has('assets_name') ? ' has-error' : ''}}">
                <label for="">Assets Name <sup>*</sup></label>
                <input type="text" name="assets_name" value="{{$assets->name}}" id="" class="assets_name input-css" >
                {!! $errors->first('assets_name', '<p class="help-block">:message</p>') !!} 
            </div>
            
     </div><br><br>
     <div class="row" >
            <div class="col-md-6 {{ $errors->has('assets_brand') ? ' has-error' : ''}}">
                <label for="">Assets Brand <sup>*</sup></label>
                <input type="text" name="assets_brand" value="{{$assets->brand}}" id="" class="assets_brand input-css" >
                {!! $errors->first('assets_brand', '<p class="help-block">:message</p>') !!} 
            </div>
            <div class="col-md-6 {{ $errors->has('assets_number') ? ' has-error' : ''}}">
                <label for="">Assets Model Number <sup>*</sup></label>
                <input type="text" name="assets_number" id="" value="{{$assets->model_number}}" class="assets_number input-css" >
                {!! $errors->first('assets_number', '<p class="help-block">:message</p>') !!} 
            </div>
     </div><br><br>
     <div class="row" >
            <div class="col-md-6 {{ $errors->has('assets_desc') ? ' has-error' : ''}}">
                <label for="">Assets Description <sup>*</sup></label>
                <input type="text" name="assets_desc" value="{{$assets->description}}" id="" class="assets_desc input-css" >
                {!! $errors->first('assets_desc', '<p class="help-block">:message</p>') !!} 
            </div>
            <div class="col-md-6 {{ $errors->has('assets_bill_no') ? ' has-error' : ''}}">
                <label for="">Assets Bill Number <sup>*</sup></label>
                <input type="text" name="assets_bill_no" id="" value="{{$assets->asset_bill_no}}" class="assets_bill_no input-css" maxlength="100" >
                {!! $errors->first('assets_bill_no', '<p class="help-block">:message</p>') !!} 
            </div>
     </div><br><br>
     <div class="row" >
        <div class="col-md-6 {{ $errors->has('assets_purchase_date') ? ' has-error' : ''}}">
            <label for="">Assets Purchase Date <sup>*</sup></label>
            <input type="text" name="assets_purchase_date" value="{{date('d-m-Y',strtotime($assets->purchase_date))}}" id="" class=" assets_purchase_date input-css">
            {!! $errors->first('assets_purchase_date', '<p class="help-block">:message</p>') !!} 
        </div>
        <div class="col-md-6 {{ $errors->has('assets_value') ? ' has-error' : ''}}">
            <label for="">Assets Value <sup>*</sup></label>
            <input type="number" name="assets_value" id="" value="{{$assets->asset_value}}" class="assets_value input-css" step="0.01" onKeyPress="if(this.value.length==10) return false;" >
            {!! $errors->first('assets_value', '<p class="help-block">:message</p>') !!} 
        </div>
        
    </div><br><br>
    <div class="row" >
        <div class="col-md-6 {{ $errors->has('upd_assets_photo') ? ' has-error' : ''}}">
            <label for="">Assets Photo Upload </label>
            @if($assets->asset_photo_upload != "" || $assets->asset_photo_upload != null)
                @if (file_exists(public_path().'/upload/assets/'.$assets->asset_photo_upload ))
                    <img src="{{ asset('upload/assets')}}/{{$assets->asset_photo_upload}}" height="50" width="100">
                @endif
            @endif
            <br>
            <br>
            <input type="file" accept="image/x-png,image/gif,image/jpeg" name="upd_assets_photo" value="{{$assets->asset_photo_upload}}" id="" class="upd_assets_photo ">
            {!! $errors->first('upd_assets_photo', '<p class="help-block">:message</p>') !!} 
        </div>
        <div class="col-md-6 {{ $errors->has('upd_assets_bill') ? ' has-error' : ''}}">
            <label for="">Assets Bill Upload </label>
            @if($assets->asset_bill_upload != "" || $assets->asset_bill_upload != null)
                @if (file_exists(public_path().'/upload/assets/'.$assets->asset_bill_upload ))
                    <?php $ext = pathinfo(storage_path().'/upload/assets/'.$assets->asset_bill_upload, PATHINFO_EXTENSION); ?>
                    @if ($ext == 'pdf')
                        <a href="{{ asset('upload/assets')}}/{{$assets->asset_bill_upload}}" target="_blank">See Asset Bill</a>
                    @else
                        <img src="{{ asset('upload/assets')}}/{{$assets->asset_bill_upload}}" height="50" width="100">
                    @endif
                @endif
            @endif
            <br>
            <br>
            <input type="file" accept="image/x-png,image/gif,image/jpeg,application/pdf" name="upd_assets_bill" value="{{$assets->asset_bill_upload}}" id="" class="upd_assets_bill ">
            {!! $errors->first('upd_assets_bill', '<p class="help-block">:message</p>') !!} 
        </div>
       <input type="text" name="old_image" value="{{$assets->asset_photo_upload}}" hidden>
       <input type="text" name="old_bill" value="{{$assets->asset_bill_upload}}" hidden>
        
    </div><br><br>
       </div>
       
 
        <div class="row">
                <div class="col-md-12">
                     <input type="submit" class="btn btn-primary" value="Submit">
                </div>
            </div>
        </form>
      
      </section>
@endsection
