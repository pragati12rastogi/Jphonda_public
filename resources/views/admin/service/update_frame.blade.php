@extends($layout)

    @section('title', __('Update Frame'))

@section('breadcrumb')
    <li><a href="/admin/service/jobcard/list"><i class=""></i> {{__('Job Card List')}} </a></li>  
    <li><a href="#"><i class=""></i> {{__('Update Frame')}} </a></li>    
@endsection

@section('js')
<script src="/js/pages/inputmask_bundle.js"></script>
<script>
  var oldModelName = @php echo json_encode((old('model_name')) ? old('model_name') : '' ); @endphp;
   var oldModelVariant = @php echo json_encode((old('model_variant')) ? old('model_variant') : '' ); @endphp;
   var oldProduct = @php echo json_encode((old('prod_name')) ? old('prod_name') : '' ); @endphp;

   var model_name= @php echo json_encode($job_card->model_name) ;@endphp;
 
   if(model_name != '') {
     $('#model_name').val(model_name);
   }

   if(oldModelName != '') {
        $('#model_name').val(oldModelName);
        GetModel(oldModelName);
   }
   if (oldModelVariant != '') {
    $('#model_variant').val(oldModelVariant);
   }
   if (oldProduct != '') {
    $('#prod_name').val(oldProduct);
   }

   $('select[name="model_name"]').on('load change', function() {
    var model_name = $('#model_name').val();
       GetModel(model_name)
   })

    function GetModel(model){
    var model_name = $('#model_name').val();
    $('#model_variant').empty().trigger('change');
    $('#prod_name').empty().trigger('change');
    if (model_name != '' && model_name != null) {
        $('#ajax_loader_div').css('display', 'block');
        // call to api and get Model Variant
        $.ajax({
            url: '/admin/get/modelVariant',
            method: 'GET',
            data: { 'model_name': model_name },
            success: function(result) {
                var str = '<option value"">Select Model Variant</option>';
                $.each(result, function(key, val) {
                    str += '<option value="' + val.model_variant + '">' + val.model_variant + '</option>';
                });
                $("#model_variant").append(str);
            },
            error: function(error) {
                $('#ajax_loader_div').css('display', 'none');
                $("#model_name").val('').trigger('change');
                alert('Error, Internal Issue Please Try Again');
            }
        }).done(function() {
            if (oldModelVariant != '') {
                $('#model_variant').val(oldModelVariant).trigger('change');
            }
            $('#ajax_loader_div').css('display', 'none');

        });
    }
    // prodAndStoreChange(null, storeId);
};

$('select[name="model_variant"]').on('change', function() {
    var model_var = $('#model_variant').val();
    var model_name = $('#model_name').val();
    // var storeId = $(this).val();
    $('#prod_name').empty().trigger('change');
    // call to api and get Product Color Code
    if (model_var != '' && model_var != null) {
        $('#ajax_loader_div').css('display', 'block');
        $.ajax({
            url: '/admin/get/modelColorCode',
            method: 'GET',
            data: { 'model_var': model_var, 'model_name': model_name },
            success: function(result) {
                var str = '<option value"">Select Color Code</option>';
                $.each(result, function(key, val) {
                    str += '<option value="' + val.id + '">' + val.color_code + '</option>';
                });
                $("#prod_name").append(str);
            },
            error: function(error) {
                $('#ajax_loader_div').css('display', 'none');
                $("#model_variant").val('').trigger('change');
                alert('Error, Internal Issue Please Try Again');
            }
        }).done(function() {
            if (oldProduct != '') {
                $('#prod_name').val(oldProduct).trigger('change');
            }
            $('#ajax_loader_div').css('display', 'none');

        });
    }
    // prodAndStoreChange(null, storeId);
});



  var sale_date = @php echo json_encode($job_card->sale_date) ;@endphp;
  var date = @php echo json_encode(date('m/d/Y', strtotime($job_card->sale_date))) ;@endphp;
  var mfYear = @php echo json_encode((old('manufacturing_year')) ? old('manufacturing_year') : '' ); @endphp;

  if (sale_date) {
    $(".datepicker").datepicker(
   "setDate" , date );
  }else{
    var currentDate = new Date(); 
   $(".datepicker").datepicker(
   "setDate" , currentDate );
  }

  for (i = new Date().getFullYear(); i > 1900; i--) {
      $('#yearpicker').append($('<option />').val(i).html(i));
  }
  if (mfYear) {
    $('#yearpicker option[value='+mfYear+']').attr('selected','selected');
  }


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
          $("#reg_error").html('state code not found, Please enter any other state code.');
            $("#reg_error").show();
             setTimeout(function() {
               $('#reg_error').fadeOut('fast');
              }, 40000);
              return false;
        }
      }else if(reg_length == '4' ){
         var city = value.substring(2, 4);
         var city_code = city.replace(/[^a-z0-9\s]/gi, '').replace(/[_\s]/g, '-');
         var state_code = res.replace(/[^a-z0-9\s]/gi, '').replace(/[_\s]/g, '-');
         $.ajax({
             url:'/admin/service/search/city',
             method:'GET',
             data: {'state_code':state_code,'city_code':city_code},
             success:function(result){
                if (result == 0) {
                  $("#reg_error").html('city code not found, Please enter any other city code.');
                  $("#reg_error").show();
                   setTimeout(function() {
                     $('#reg_error').fadeOut('fast');
                    }, 10000);
                   return false;
                }
             },
             error:function(error){
                alert("something wen't wrong");
             }
          });
      }

    });

      $(document).ready(function(){
            var radioValue = $("input[name='reg_type']:checked").val();
            if(radioValue){
                if (radioValue == 'available') {
                  $("#register").show();
                  $("#reg").hide();
                }else{
                  $("#register").hide();
                  $("#reg").show();
                }
            }
    });

   $(document).ready(function(){
        $("input[type='radio']").click(function(){
            var radioValue = $("input[name='reg_type']:checked").val();
            if(radioValue){
                if (radioValue == 'available') {
                  $("#register").show();
                  $("#reg").hide();
                  $("#reg").val('');
                }else{
                  $("#register").hide();
                  $("#register").val('');
                  $("#reg").show();
                }
            }
        });
    });

</script>
@endsection

@section('main_section')
    <section class="content">
        
            @include('admin.flash-message')
                @yield('content')
               
            <!-- general form elements -->
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
                <form id="my-form" action="/admin/service/update/frame/{{$job_card->id}}" method="post">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <label>{{__('Frame Number')}} <sup>*</sup></label>
                            <input type="text" name="frame" value="{{old('frame')?old('frame'):$job_card->frame}}" class="input-css"  @if($count>1 && $job_card->frame != null) readonly @endif >
                            {!! $errors->first('frame', '<p class="help-block">:message</p>') !!}
                        </div>
                         <div class="col-md-6"><br>
                          <div class="col-md-6">
                            <div class="col-md-3"><br><br>
                              <input type="radio"  @if($job_card->registration != null) checked @endif name="reg_no" class="reg_type" value="available"> 
                            </div>
                            <div class="col-md-9">
                              <label>{{__('Registration Number')}} <sup>*</sup></label>
                              <input type="text" id="registration" name="registration" @if($job_card->registration == null) value="UP32" @else value="{{$job_card->registration}}" @endif class="input-css" @if($count>1 && $job_card->registration != null ) readonly @endif>
                              <p id="reg_error" class="help-block"> @if ($message = Session::get('state_error')) {{$message}} @endif @if ($message = Session::get('city_error')) {{$message}} @endif</p>
                              {!! $errors->first('registration', '<p class="help-block" >:message</p>') !!}
                            </div>
                            
                          </div>
                          <div class="col-md-6" @if($job_card->month < '6') style="display:block;" @else style= "display:none" @endif ><br><br>
                            <input type="radio" name="reg_no" value="NA"  @if($job_card->registration == null) checked @endif> NA  
                          </div> 
                          </div>
                    </div><br>
                    <div class="row">
                        <div class="col-md-6">
                            <label>{{__(' Customer')}} <sup>*</sup></label>
                            <select name="customer" class="input-css select2">
                                <option selected="" disabled="">Select Customer</option>
                                @foreach($customer as $cust)
                                @if($cust->id == $job_card->customer_id)
                                <option value="{{$cust->id}}" selected="">{{$cust->name}}-{{$cust->mobile}}-{{$cust->aadhar_id}}</option>
                                @else
                                  <option value="{{$cust->id}}" {{old('customer')== $cust->id ? 'selected=selected' : ''}}>{{$cust->name}}-{{$cust->mobile}}-{{$cust->aadhar_id}}</option>
                                  @endif
                                @endforeach
                            </select>
                            <small>Create Customer
                                <a href="/admin/customer/registration?for=service" target="_blank">Click Here..</a>
                            </small>
                            {!! $errors->first('customer', '<p class="help-block">:message</p>') !!}
                        </div>
                        <div class="col-md-6">
                         <label>Model Name<sup>*</sup></label>
                         <select name="model_name" class="input-css select2 selectValidation" id="model_name" >
                           <option  selected="" disabled="">Select Model Name</option>
                            @foreach($model_name as $key => $val)
                            @if($job_card->model_name == $val['model_name'])
                             <option value="{{$val['model_name']}}" selected=""> {{$val['model_name']}} </option>
                            @else
                            <option value="{{$val['model_name']}}" {{old('model_name')== $val['model_name']? 'selected=selected' : ''}}> {{$val['model_name']}} </option>
                            @endif
                            @endforeach
                         </select>
                         {!! $errors->first('model_name', '
                         <p class="help-block">:message</p>
                         ') !!}
                      </div> 
                    </div><br>
                    <div class="row">
                        <div class="col-md-6">
                        <label>Model Variant<sup>*</sup></label>
                        <select name="model_variant" class="input-css select2 selectValidation" id="model_variant" >
                            <option value=""> Select Model Variant</option>
                         @if($job_card->model_variant)
                            <option  selected value="{{$job_card->model_variant}}" >{{$job_card->model_variant}}</option>
                         @endif
                        </select>
                        {!! $errors->first('model_variant', '
                        <p class="help-block">:message</p>
                        ') !!}
                        </div>
                         {{-- This Product Name as a Color Code  --}}
                          <div class="col-md-6">
                             <label>Color Code<sup>*</sup></label>
                             <select name="prod_name" class="input-css select2 selectValidation" id="prod_name" >
                                <option value=""> Select Color Code</option>
                                @if($job_card->product_id)
                                 <option  selected value="{{$job_card->product_id}}">{{$job_card->color_code}}</option>
                                 @endif
                             </select>
                             {!! $errors->first('prod_name', '
                             <p class="help-block">:message</p>
                             ') !!}
                             <input type="hidden" hidden name="prod_basic" value="{{(old('prod_basic')) ? old('prod_basic') : 0}}">
                          </div>
                    </div><br/>
                     <div class="row">
                        <div class="col-md-6">
                            <label>{{__('Sale Date')}} <sup>*</sup></label>
                            <input type="text" name="sale_date" value="{{date('d/m/Y', strtotime($job_card->sale_date))}}" class="input-css datepicker" id="datepicker">
                            {!! $errors->first('sale_date', '<p class="help-block">:message</p>') !!}
                        </div>
                        @if($sellingDealer)
                        <div class="col-md-6">
                            <label>{{__('Selling Dealer')}} <sup>*</sup></label>
                            <select name="selling_dealer_id" class="input-css select2">
                              <option selected disabled>Select selling dealer</option>
                            @foreach($sellingDealer as $dealer)
                              @if($dealer->Id == $job_card->selling_dealer_id)
                              <option value="{{$dealer->Id}}" selected>{{$dealer->name}}</option>
                              @else
                              <option value="{{$dealer->Id}}" {{old('selling_dealer_id')==$dealer->Id ? 'selected = selected' : ''}}>{{$dealer->name}}</option>
                              @endif
                            @endforeach
                          </select>
                            {!! $errors->first('selling_dealer_id', '<p class="help-block">:message</p>') !!}
                        </div>
                        @endif
                      </div><br>
                      <div class="row">
                        <div class="col-md-6">
                          <label>{{__('Manufacturing Year')}}</label>
                          <select name="manufacturing_year" id="yearpicker" class="input-css select2">
                            <option value="{{$job_card->manufacturing_year}}" {{old('manufacturing_year')== $job_card->manufacturing_year ? 'selected=selected' : ''}}>{{$job_card->manufacturing_year}}</option>
                          </select>
                        </div>
                      </div><br>
                    <div class="row">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-success">Submit</button>
                        </div>
                        <br><br>    
                    </div>  
                </form>
            </div>
      </section>
@endsection