@extends($layout)
@section('title', __('Create Tag'))
@section('breadcrumb')
<li><a href="#"><i class=""></i></a></li>
@endsection
@section('css')
<link rel="stylesheet" href="/css/responsive.bootstrap.css">
<style>
   tr.dangerClass{
   background-color: #f8d7da !important;
   }
   tr.successClass{
   background-color: #d4edda !important;
   }
   tr.worningClass{
   background-color: #fff3cd !important;
   }
</style>
@endsection
@section('js')
<script src="/js/dataTables.responsive.js"></script>
<script src="/js/pages/inputmask_bundle.js"></script>
<script>
  $('#js-msg-error').hide();
  $('#js-msg-success').hide();
   var state = @php echo json_encode($state); @endphp;

    var input_type = true;
    Inputmask("A{2}9{2}A{2}9{4}").mask($('#registration'));

    $("#registration").keyup(function(){
      var value = $("#registration").val();
      var number = value.replace(/[^a-z0-9\s]/gi, '').replace(/[_\s]/g, '-');
      var res = value.substring(0, 2);
      reg_length = number.length;
      if (reg_length == '2') {
        var state_match = false;
        
        $.each(state, function(key, val){
          if (number == val.state_code) {
            state_match = true;
            input_type = true;
          }
        });
        if(!state_match){
          input_type = false;

          $("#reg_error").html('state code not found, Please enter any other state code!');
            $("#reg_error").show();
             setTimeout(function() {
               $('#reg_error').fadeOut('fast');
              }, 4000);
              return false;
        }

      }else if(reg_length == '4'){
         var city = value.substring(2, 4);
         var city_code = city.replace(/[^a-z0-9\s]/gi, '').replace(/[_\s]/g, '-');
         var state_code = res.replace(/[^a-z0-9\s]/gi, '').replace(/[_\s]/g, '-');
         $.ajax({
             url:'/admin/service/search/city',
             method:'GET',
             data: {'state_code':state_code,'city_code':city_code},
             success:function(result){
                if (result == 0) {
                  $("#reg_error").html('city code not found, Please enter any other city code!');
                  $("#reg_error").show();
                   setTimeout(function() {
                     $('#reg_error').fadeOut('fast');
                    }, 4000);
                   return false;
                }
             },
             error:function(error){
                alert("something wen't wrong");
             }
          });
      }

    });


</script>
@endsection
@section('main_section')
<section class="content">
   <div id="app">

         @include('admin.flash-message')
         @yield('content')
         <div class="box-header with-border">
           <div class="row">
           <div class="alert alert-danger" id="js-msg-error" style="display: none;">
           </div>
           <div class="alert alert-success" id="js-msg-success" style="display: none;">
           </div>
         </div>
            <div class="box box-primary">
                <div class="box-header">
                </div>  
                <form  action="/admin/service/create/tag" method="post">
                    @csrf
                    <div class="row">
                  <div class="col-md-12 margin-bottom">
                     <div class="col-md-6">
                        <label for="frame">Frame Number <sup>*</sup></label>
                        <input type="text" name="frame" id="frame" placeholder="Enter Frame Number" class="input-css" value="{{(old('frame'))?old('frame') : ''}}">
                        {!! $errors->first('frame', '<p class="help-block">:message</p>') !!}
                     </div>
                  </div>
                  <div class="col-md-12 margin-bottom">
                     <div class="col-md-6">
                        <label for="registration">Registraion Number <sup>*</sup></label>
                        <input type="text" name="registration" id="registration" value="{{(old('registration'))?old('registration') : 'UP32'}}" placeholder="Enter Registration Number" class="input-css">
                         <p id="reg_error" class="help-block"> @if ($message = Session::get('state_error')) {{$message}} @endif @if ($message = Session::get('city_error')) {{$message}} @endif</p>
                         {!! $errors->first('registration', '<p class="help-block">:message</p>') !!}
                     </div>
                  </div>
              <div class="col-md-12 margin-bottom">
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
                  <div class="col-md-12 margin">
                        <div class="col-md-6">
                            <button type="submit" class="btn btn-success">Submit</button>
                        </div>
                        <br><br>    
                    </div>
                  </div>

                </form>
            </div>
         </div>
   </div>
   
   <!-- Modal -->
   <!-- /.box -->
</section>
@endsection