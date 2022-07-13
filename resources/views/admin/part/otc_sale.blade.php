@extends($layout)

@section('title', __('OTC Sale'))

@section('breadcrumb')
    <li><a href="#"><i class=""></i> {{__('OTC Sale')}} </a></li>
    
@endsection
@section('js')
<script>
     $('#js-msg-error').hide();
     $('#js-msg-success').hide();

    $(document).ready(function () {
        $("#amc-div").hide();
        $("#year-div").hide();
        $("#part-div").show();
        $("#sale_type").change(function () {
            var sale_type = $("#sale_type").val();
            if (sale_type == 'AMC') {
                $("#part-div").hide();
                $("#amc-div").show();
            }else if(sale_type == 'Part'){
                $("#part-div").show();
                $("#amc-div").hide();
            }else if (sale_type == 'Extended Warranty') {
                $("#part-div").hide();
                $("#amc-div").show();
                $("#year-div").show();
            }else{
                 $("#part-div").show();
                 $("#amc-div").hide();
            }
        });
    });

    $(document).ready(function() {
        $("#frame").change(function () {
            var frame = $("#frame").val();
            $.ajax({
                method: "GET",
                url: "/admin/part/otc/sale/get/frame",
                data: {'frame':frame},
                success:function(data) {
                    if(data == 'error')
                    {
                      $("#js-msg-error").html('Something went wrong !');
                      $("#js-msg-error").show();
                      setTimeout(function() {
                       $('#js-msg-error').fadeOut('fast');
                     }, 4000);                      
                    }if (data == 'not-found') {
                      $('#table').dataTable().api().ajax.reload();
                      $("#js-msg-error").html('Frame number not found !');
                      $("#js-msg-error").show();
                     setTimeout(function() {
                       $('#js-msg-error').fadeOut('fast');
                      }, 4000); 
                    }else{
                        $("#frame").val(data.frame);
                        $("#registration").val(data.registration);
                        $("#model_name").val(data.model_name);
                        $("#color_code").val(data.color_code);
                        $("#name").val(data.name);
                        $("#mobile").val(data.mobile);
                        $("#service_id").val(data.service_id);
                    }
                }
            });
        });
    }); 

      $(document).ready(function() {
        $("#registration").change(function () {
            var registration = $("#registration").val();
            $.ajax({
                method: "GET",
                url: "/admin/part/otc/sale/get/frame",
                data: {'registration':registration},
                success:function(data) {
                    if(data == 'error')
                    {
                      $("#js-msg-error").html('Something went wrong !');
                      $("#js-msg-error").show();
                      setTimeout(function() {
                       $('#js-msg-error').fadeOut('fast');
                     }, 4000);                      
                    }if (data == 'not-found') {
                      $('#table').dataTable().api().ajax.reload();
                      $("#js-msg-error").html('Registration number not found !');
                      $("#js-msg-error").show();
                     setTimeout(function() {
                       $('#js-msg-error').fadeOut('fast');
                      }, 4000); 
                    }else{
                        $("#frame").val(data.frame);
                        $("#registration").val(data.registration);
                        $("#model_name").val(data.model_name);
                        $("#color_code").val(data.color_code);
                        $("#name").val(data.name);
                        $("#mobile").val(data.mobile);
                        $("#service_id").val(data.service_id);
                    }
                }
            });
        });
    }); 

</script>
@endsection

@section('main_section')
    <section class="content">
        <div class="row">
         <div class="alert alert-danger" id="js-msg-error">
         </div>
         <div class="alert alert-success" id="js-msg-success">
         </div>
       </div>
            @include('admin.flash-message')
                @yield('content')
            <div class="box-header with-border">
            <div class="box box-primary">
                <div class="box-header">
                </div>  
            <form id="unload-data-validation" action="/admin/part/otc/sale" method="POST">
                @csrf
                <input type="hidden" name="service_id" id="service_id">
                <div class="row">
                    <?php $store_id = explode(',', $user->store_id);
                         $count = count($store_id);
                         if ($count < 2) { ?>
                              <div class="col-md-6" style="display: none;">
                                <label>{{__('admin.store')}} <sup>*</sup></label>
                                <select class="input-css select2" name="store" >
                                       @foreach ($store as $key)
                                            <option value="{{$key->id}} selected" >{{$key->name.'-'.$key->store_type}}</option>
                                       @endforeach
                                </select>
                                {!! $errors->first('store', '<p class="help-block">:message</p>') !!}
                            </div>
                         <?php }else{ ?>
                            <div class="col-md-6">
                                <label>{{__('admin.store')}} <sup>*</sup></label>
                                <select class="input-css select2" name="store">
                                <option value="">Select Store</option>
                                @foreach ($store as $key)
                                    <option value="{{$key->id}}" >{{$key->name.'-'.$key->store_type}}</option>
                                @endforeach
                                </select>
                                {!! $errors->first('store', '<p class="help-block">:message</p>') !!}
                            </div>
                    <?php  } ?>
                    <div class="col-md-6">
                        <label>{{__('Type Of Sale')}} <sup>*</sup></label>
                        <select name="sale_type" id="sale_type" class="input-css select2">
                            <option selected="" disabled="">Select Purchase Type</option>
                            <option value="Part">Part</option>
                            <option value="AMC">AMC</option>
                            <option value="Extended Warranty">Extended Warranty</option>
                        </select>
                        {!! $errors->first('mobile', '<p class="help-block">:message</p>') !!}
                    </div>
                    </div><br>

                    <div class="row" id="year-div">
                        <div class="col-md-6">
                        <label>{{__('year')}} <sup>*</sup></label>
                        <input type="number" name="year" id="year" min="0" class="input-css" value="{{old('year')}}">
                        {!! $errors->first('year', '<p class="help-block">:message</p>') !!}
                    </div>
                    </div>
                     <div class="row" id="amc-div">
                    <div class="col-md-6">
                        <label>{{__('Frame')}} <sup>*</sup></label>
                        <input type="text" name="frame" id="frame" class="input-css" value="{{old('frame')}}">
                        {!! $errors->first('frame', '<p class="help-block">:message</p>') !!}
                    </div>
                    <div class="col-md-6">
                        <label>{{__('Registration Number')}} <sup>*</sup></label>
                        <input type="text" name="registration" id="registration" class="input-css" value="{{old('registration')}}">
                        {!! $errors->first('registration', '<p class="help-block">:message</p>') !!}
                    </div>
                </div><br>
                <div class="row">
                    <div class="col-md-6">
                        <label>{{__('parts.model_name')}} <sup>*</sup></label>
                        <input type="text" name="model_name" id="model_name" class="input-css" value="{{old('modeel_name')}}">
                        {!! $errors->first('model_name', '<p class="help-block">:message</p>') !!}
                    </div>
                    <div class="col-md-6">
                        <label>{{__('parts.color')}} <sup>*</sup></label>
                        <input type="text" name="color_code" id="color_code" class="input-css" value="{{old('color_code')}}">
                        {!! $errors->first('color_code', '<p class="help-block">:message</p>') !!}
                    </div>
                </div><br>
                <div class="row">
                    <div class="col-md-6">
                        <label>{{__('Customer Name')}} <sup>*</sup></label>
                        <input type="text" name="customer_name" id="name" class="input-css" value="{{old('customer_name')}}">
                        {!! $errors->first('customer_name', '<p class="help-block">:message</p>') !!}
                    </div>
                    <div class="col-md-6">
                        <label>{{__('Mobile')}} <sup>*</sup></label>
                        <input type="number" name="mobile" id="mobile" class="input-css" value="{{old('mobile')}}">
                        {!! $errors->first('mobile', '<p class="help-block">:message</p>') !!}
                    </div>
                </div><br>
                <div id="part-div">
                    
                <div class="row">
                    <div class="col-md-6">
                        <label>{{__('Quantity')}} <sup>*</sup></label>
                        <input type="number" name="qty" class="input-css" value="{{old('qty')}}">
                        {!! $errors->first('qty', '<p class="help-block">:message</p>') !!}
                    </div>
                </div><br>
                </div>
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