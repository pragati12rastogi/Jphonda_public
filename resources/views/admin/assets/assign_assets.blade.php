@extends($layout)

@section('title', 'Assign Assets')

{{-- TODO: fetch from auth --}}
@section('user', Auth::user()->name)

@section('breadcrumb')
<li>
    <a href="{{url('/admin/assets/assign/employee/list')}}">Assign Assets List</a>
</li>
    <li><a href=""><i class=""></i>Assign Assets</a></li>
@endsection
@section('js')
<script src="/js/pages/assets.js"></script>
<script>
$(document).ready(function(){
      var date=  new Date();
      var dd=  date.getDate();
      var mm = date.getMonth()+1;
      var yy = date.getFullYear();
    $("#from_date").val(dd+"-"+mm+"-"+yy);
    $("#from_date").datepicker({
        startDate:'today',
        format: 'd-m-yyyy'
    });
});
 function filter_assets_code(i){
     
    var assets=$(i).val();
    var store = $("#store_name").children("option:selected").val();

    $('#ajax_loader_div').css('display','block');
    $.ajax({
        type:"GET",
        url:"/admin/assets/filter/assetcode/api/",
        data:{'assets':assets,'store':store},
        success: function(result){
            if (result) {
                        $("#code").empty();
                        $("#code").append(' <option value="">--Select Codes-</option>');
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
                        $("#emp").empty();
                        $("#emp").append(' <option value="">--Select Employees--</option>');
                        $.each(result, function(key, value) {
                            $("#emp").append('<option value="' + key + '">' + value + '</option>');
                        });
                        $('#ajax_loader_div').css('display','none');
                    }
        }
    })
 }
// function checktime(){
//     var datefrom = $("#from_date").val();
//     var dateto = $("#to_date").val();
//     if(datefrom > dateto){
//         $("#to_date").val(datefrom);
//     }else{
       
//     }
// }

var startDate=null;
		var endDate=null;
		$(document).ready(function(){
			$('#from_date').datepicker()
				.on('changeDate', function(ev){
					startDate=new Date(ev.date.getFullYear(),ev.date.getMonth(),ev.date.getDate(),0,0,0);
					if(endDate!=null&&endDate!='undefined'){
						if(endDate<startDate){
								$("#from_date").val("");
						}
					}
				});
			$("#to_date").datepicker()
				.on("changeDate", function(ev){
					endDate=new Date(ev.date.getFullYear(),ev.date.getMonth(),ev.date.getDate(),0,0,0);
					if(startDate!=null&&startDate!='undefined'){
						if(endDate<startDate){
							$("#to_date").val("");
						}
					}
				});
		});
// function gen(){
    
//     var cat = $(".assets_category").val();
//     var code = $("#code").val();
//     var emp =$(".assets_emp").val();
//     var from = $(".assets_from_date").val();

//     if(cat=="" || code=="" || emp == "" || from==""){
       
//         $(".err").text("fill all required field first").show();
//         return false;
//     }else{
//         $(".err").hide();

//         //     $.ajax({
//         //     type:"GET",
//         //     url:"",
//         //     // data:{'cat':cat,
//         //     // 'code' :code,
//         //     // 'emp' :emp,
//         //     // 'from' :from
//         //     // },
//         //     success: function(result){
//         //         return result;
//         //     }
//         // });
//     }
// }

</script>
@endsection

@section('main_section')
    <section class="content">
        <!-- Default box -->
        <div id="app">
                @include('admin.flash-message')
                @yield('content')
            </div>
       <form action="/admin/assets/assign/employee" method="POST" id="form" enctype="multipart/form-data">
        @csrf

       <div class="box box-header">
           <br>

            <div class="row" >

            <div class="col-md-6" style="{{(count($store) == 1)? 'display:none' : 'display:block'}}">
                     <label>Store Name<sup>*</sup></label>
                     @if(count($store) > 1)
                        <select name="store_name" onchange="filter_user(this)" class="input-css select2 selectValidation" id="store_name">
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
                    <label for="">Assets Category <sup>*</sup></label>
                    <select class="input-css assets_category select2" style="padding-top:2px" name="assets_category" onchange="filter_assets_code(this)">
                        <option value="">--Select Category--</option>
                        @foreach($asset_category as $ac)
                            <option value="{{$ac->ac_id}}">{{ $ac->category_name}}</option>
                        @endforeach
                    </select>
                    {!! $errors->first('assets_category', '<p class="help-block">:message</p>') !!} 
                </div>
               
            </div><br><br>
            <div class="row" >
                 <div class="col-md-6 {{ $errors->has('assets_code') ? ' has-error' : ''}}">
                    <label for="">Assets Codes <sup>*</sup></label>
                    <select class="input-css assets_code select2" id="code" style="padding-top:2px" name="assets_code" >
                        <option value="">--Select Code--</option>
                    </select>
                    {!! $errors->first('assets_code', '<p class="help-block">:message</p>') !!} 
                </div>
            
                <div class="col-md-6 {{ $errors->has('assets_emp') ? ' has-error' : ''}}">
                    <label for="">Employees<sup>*</sup></label>
                    <select class="input-css assets_emp select2" id="emp" style="padding-top:2px" name="assets_emp" >
                        <option value="">--Select Employee--</option>
                    </select>
                    {!! $errors->first('assets_emp', '<p class="help-block">:message</p>') !!} 
                </div>                
            </div><br>
            <div class="row">
              <div class="col-md-6 ">
                    <label for="">Duration <sup></sup></label>
                    <div class="col-md-6 {{ $errors->has('assets_from_date') ? ' has-error' : ''}}">
                        <label>From : <sup>*</sup></label>
                        <input type="text" name="assets_from_date" id="from_date" class="assets_from_date input-css" required="">
                        {!! $errors->first('assets_from_date', '<p class="help-block">:message</p>') !!} 
                        
                    </div>
                    <div class="col-md-6 {{ $errors->has('assets_to_date') ? ' has-error' : ''}}">
                        <label>To :</label>
                        <input type="text" name="assets_to_date" id="to_date" class="assets_to_date input-css" >
                        {!! $errors->first('assets_to_date', '<p class="help-block">:message</p>') !!} 
                    </div>

                </div>
                <div class="col-md-6 {{ $errors->has('assets_form') ? ' has-error' : ''}}">
                    <label >Upload Signed Asset Assign</label>
                     <input type="file" accept="application/pdf" name="assets_form" id="assets_form" class="assets_form ">
                    {!! $errors->first('assets_form', '<p class="help-block">:message</p>') !!} 
                    
                </div>


                <div class="col-md-12">
                    <label class="error err"></label>
                </div>
            </div><br><br>
       </div>
       
        <div class="row">
                <div class="col-md-12">
                     <input type="submit" class="btn btn-primary" value="Submit">
                     
                      <button type="submit" formtarget="_blank" class="btn btn-success" formmethod="GET" formaction="/admin/assets/assign/generate/form">Generate Asset Assign</button>
                </div>
            </div>
        </form>
      
      </section>
@endsection
