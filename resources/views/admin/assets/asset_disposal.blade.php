@extends($layout)

@section('title', 'Disposal Assets')

{{-- TODO: fetch from auth --}}
@section('user', Auth::user()->name)

@section('breadcrumb')
<li>
    <a href="{{url('/admin/assets/disposal/list')}}">Asset Disposal List</a>
</li>
<li><a href="{{url('/admin/assets/disposal')}}"><i class=""></i>Asset Disposal</a></li>
@endsection
@section('js')
<script src="/js/pages/assets.js"></script>
<script>
    
 function filter_assets_code(i){
     
    var assets=$(i).val();
    var store = $("#store_name").children("option:selected").val();
    $('#ajax_loader_div').css('display','block');
    $.ajax({
        type:"GET",
        url:"/admin/assets/filter/assetcode/api",
        data:{'assets':assets,'store':store},
        success: function(result){
            if (result) {
                        $("#code").empty();
                        $("#code").append(' <option value="">--Select Code--</option>');
                        $.each(result, function(key, value) {
                            $("#code").append('<option value="' + key + '">' + value + '</option>');
                        });
                        $('#ajax_loader_div').css('display','none');
                    }
        }
       
    })
 }

  function filter_user(i){
     
    var assets=$(i).val();
    $('#ajax_loader_div').css('display','block');
    $.ajax({
        type:"GET",
        url:"/admin/assets/filter/user/api/",
        data:{'assets':assets},
        success: function(result){
            if (result) {
                        $("#empid").empty();
                        $("#empid").append(' <option value="">--Select Employees--</option>');
                        $.each(result, function(key, value) {
                            $("#empid").append('<option value="' + key + '">' + value + '</option>');
                        });
                        $('#ajax_loader_div').css('display','none');
                    }
        }
    })
 }

 // function filter_employee(i){
     
 //     var assets=$("#category").val();
 //     var code=$(i).val();
 //     $.ajax({
 //         type:"GET",
 //         url:"/admin/assets/filter/employee/api",
 //         data:{'assets':assets,'code':code},
 //         success: function(result){
 //             if (result) {
 //                         $("#empid").empty();
 //                         $("#empid").append(' <option value="">--Select Employees--</option>');
 //                         $.each(result, function(key, value) {
 //                             $("#empid").append('<option value="' + key + '">' + value + '</option>');
 //                         });
 //                     }
 //         }
        
 //     })
 //  }
  $(document).ready(function(){
      var date=  new Date();
      var dd=  date.getDate();
      var mm = date.getMonth()+1;
      var yy = date.getFullYear();
    $(".assets_disposal_date").val(dd+"-"+mm+"-"+yy);
    $(".assets_disposal_date").datepicker({
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
       <form action="" method="POST" id="form">
        @csrf

       <div class="box box-header">
           <br>
            <div class="row" >

                <div class="col-md-6" style="{{(count($store) == 1)? 'display:none' : 'display:block'}}">
                     <label>Store Name<sup>*</sup></label>
                     @if(count($store) > 1)
                        <select name="store_name"  onchange="filter_user(this)"  class="input-css select2 selectValidation" id="store_name">
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
                <div class="col-md-6 {{ $errors->has('assets_category') ? ' has-error' : ''}}">
                    <label for="">Asset Category <sup>*</sup></label>
                    <select class="input-css assets_category select2" id="category" style="padding-top:2px" name="assets_category" onchange="filter_assets_code(this)">
                        <option value="">--Select Category--</option>
                        @foreach($asset_category as $ac){
                        <option value="{{$ac->ac_id}}">{{ $ac->category_name}}</option>
                        @endforeach
                    </select>
                    {!! $errors->first('assets_category', '<p class="help-block">:message</p>') !!} 
                </div>
            </div><br><br>
            <div class="row" >
                <div class="col-md-6 {{ $errors->has('assets_code') ? ' has-error' : ''}}">
                    <label for="">Asset Codes <sup>*</sup></label>
                    <select class="input-css assets_code select2"
                     id="code" style="padding-top:2px" name="assets_code">
                        <option value="">--Select Code--</option>
                    </select>
                    {!! $errors->first('assets_code', '<p class="help-block">:message</p>') !!} 
                </div>
                <div class="col-md-6 {{ $errors->has('assets_disposal_date') ? ' has-error' : ''}}">
                    <label for="">Asset Disposal Date <sup>*</sup></label>
                    <input type="text" name="assets_disposal_date" id="" class="assets_disposal_date input-css">
                    {!! $errors->first('assets_disposal_date', '<p class="help-block">:message</p>') !!} 
                </div>
                
            </div><br><br>
            <div class="row">
                 <div class="col-md-6 {{ $errors->has('assets_disposal_to') ? ' has-error' : ''}}">
                    <label for="">Asset Disposal To<sup>*</sup></label>

                     <select class="input-css assets_disposal_to select2" id="empid" style="padding-top:2px" name="assets_disposal_to">
                        <option value="">--Select Employee--</option>
                       
                    </select>

                    <!-- <input type="text" class="input-css assets_disposal_to" name="assets_disposal_to" id="empid"> -->
                    {!! $errors->first('assets_disposal_to', '<p class="help-block">:message</p>') !!} 
                </div>
                <div class="col-md-6 {{ $errors->has('assets_disposal_reason') ? ' has-error' : ''}}">
                    <label>Asset Disposal Reason<sup>*</sup></label>
                    <input type="text" name="assets_disposal_reason" required class="input-css" id="update_reason">
                    {!! $errors->first('assets_disposal_reason', '<p class="help-block">:message</p>') !!}
                </div>
            </div>
       </div>
       
        <div class="row">
                <div class="col-md-12">
                     <input type="submit" class="btn btn-primary" value="Submit">
                </div>
            </div>
        </form>
      
      </section>
@endsection
