@extends($layout)

@section('title', 'Create Assets')

{{-- TODO: fetch from auth --}}
@section('user', Auth::user()->name)

@section('breadcrumb')
    <li><a href="#"><i class=""></i> Create Assets</a></li>
   
@endsection
@section('js')
<script src="/js/pages/assets.js"></script>
<script>
$(document).ready(function(){
      var date=  new Date();
      var dd=  date.getDate();
      var mm = date.getMonth()+1;
      var yy = date.getFullYear();
    $(".assets_purchase_date").val(dd+"-"+mm+"-"+yy);
    $(".assets_purchase_date").datepicker({
        endDate:'today',
        format: 'd-m-yyyy'
    });
});
$("#assets_bill_no").focusout(function(){
    debugger;
    var bill = $(this).val();
    $.ajax({
        type:"GET",
        url:"/admin/assets/validate/bill/no",
        data:{'bill':bill},
        success: function(result){
            if (result == 0) {
                $("#bill_err").hide();
            }else{
                $("#bill_err").text("Bill number already exist!").show();
                $("#assets_bill_no").val('');
                $("#assets_bill_no").focus();
            }
        }
    })
})
</script>
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
@section('main_section')
    <section class="content">
        <!-- Default box -->
        <div id="app">
                @include('admin.flash-message')
                @yield('content')
            </div>
       <form action="/admin/assets/create" method="POST" id="form" enctype="multipart/form-data">
        @csrf

       <div class="box box-header">

         <div class="row">
                <div class="col-md-6" style="{{(count($store) == 1)? 'display:none' : 'display:block'}}">
                     <label>Store Name<sup>*</sup></label>
                     @if(count($store) > 1)
                        <select name="store_name" class="input-css select2 selectValidation" id="store_name">
                           <option value="">Select Store</option>
                           @foreach($store as $key => $val)
                           <option value="{{$val['id']}}" {{(old('store_name') == $val['id']) ? 'selected' : '' }}> {{$val['name']}} </option>
                           @endforeach
                        </select>
                     @endif
                     @if(count($store) == 1)
                        <select name="store_name" class="input-css select2 selectValidation" id="store_name">
                           <option value="{{$store[0]['id']}}" selected>{{$store[0]['name']}}</option>
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
                        <option value="{{$ac->ac_id}}">{{ $ac->category_name}}</option>
                        @endforeach
                    </select>
                    {!! $errors->first('assets_category', '<p class="help-block">:message</p>') !!} 
                </div>
                <div class="col-md-6 {{ $errors->has('assets_name') ? ' has-error' : ''}}">
                    <label for="">Assets Name <sup>*</sup></label>
                    <input type="text" name="assets_name" id="" class="assets_name input-css" maxlength="35" >
                    {!! $errors->first('assets_name', '<p class="help-block">:message</p>') !!} 
                </div>
            
            </div><br><br>
            <div class="row" >
                <div class="col-md-6 {{ $errors->has('assets_brand') ? ' has-error' : ''}}">
                    <label for="">Assets Brand <sup>*</sup></label>
                    <input type="text" name="assets_brand" id="" class="assets_brand input-css" maxlength="100" >
                    {!! $errors->first('assets_brand', '<p class="help-block">:message</p>') !!} 
                </div>
                <div class="col-md-6 {{ $errors->has('assets_number') ? ' has-error' : ''}}">
                    <label for="">Assets Model Number <sup>*</sup></label>
                    <input type="text" name="assets_number" id="" class="assets_number input-css" maxlength="50" >
                    {!! $errors->first('assets_number', '<p class="help-block">:message</p>') !!} 
                </div>
                
            </div><br><br>
            <div class="row" >
                <div class="col-md-6 {{ $errors->has('assets_desc') ? ' has-error' : ''}}">
                    <label for="">Assets Description <sup>*</sup></label>
                    <textarea type="text"  name="assets_desc" id="" class="assets_desc input-css" maxlength="500"></textarea>
                    {!! $errors->first('assets_desc', '<p class="help-block">:message</p>') !!} 
                </div>
                <div class="col-md-6 {{ $errors->has('assets_bill_no') ? ' has-error' : ''}}">
                    <label for="">Assets Bill Number <sup>*</sup></label>
                    <input type="text" name="assets_bill_no" id="assets_bill_no" class="assets_bill_no input-css" maxlength="100" >
                    <span style="color: red;" id="bill_err"></span>
                    {!! $errors->first('assets_bill_no', '<p class="help-block">:message</p>') !!} 
                </div>
                
            </div><br><br>
            <div class="row" >
                <div class="col-md-6 {{ $errors->has('assets_purchase_date') ? ' has-error' : ''}}">
                    <label for="">Assets Purchase Date <sup>*</sup></label>
                    <input type="text" name="assets_purchase_date" id="" class="assets_purchase_date input-css">
                    {!! $errors->first('assets_purchase_date', '<p class="help-block">:message</p>') !!} 
                </div>
                <div class="col-md-6 {{ $errors->has('assets_value') ? ' has-error' : ''}}">
                    <label for="">Assets Value <sup>*</sup></label>
                    <input type="number" name="assets_value" id="" class="assets_value input-css" step="0.01" onKeyPress="if(this.value.length==10) return false;" >
                    {!! $errors->first('assets_value', '<p class="help-block">:message</p>') !!} 
                </div>
                
            </div><br><br>
            <div class="row" >
                <div class="col-md-6 {{ $errors->has('assets_photo') ? ' has-error' : ''}}">
                    <label for="">Assets Photo Upload </label>
                    <input type="file" accept="image/x-png,image/gif,image/jpeg" name="assets_photo" id="" class="assets_photo ">
                    {!! $errors->first('assets_photo', '<p class="help-block">:message</p>') !!} 
                </div>
               <div class="col-md-6 {{ $errors->has('assets_bill_ph') ? ' has-error' : ''}}">
                    <label for="">Assets Bill Upload </label>
                    <input type="file" accept="image/x-png,image/gif,image/jpeg,application/pdf" name="assets_bill_ph" id="" class="assets_bill_ph ">
                    {!! $errors->first('assets_bill_ph', '<p class="help-block">:message</p>') !!} 
                </div>
                
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
