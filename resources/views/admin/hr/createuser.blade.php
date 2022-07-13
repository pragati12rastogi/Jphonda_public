@extends($layout)

@section('title', __('Create User'))

@section('js')
<script src="/js/pages/hr.js"></script>
<script>
    var old_state = @json((old('state') ? old('state') : '23'));
    var old_city = @json((old('city') ? old('city') : '561'));
$(document).ready(function() {
    var id = '105';
    if (id) {
        $.ajax({
            type: "get",
            url: "/admin/customer/state/" + id, //Please see the note at the end of the post**
            success: function(res) {
                if (res) {
                    $("#state").empty();
                    $("#city").empty();
                    $("#state").append('<option>Select State</option>');
                    $.each(res, function(key, value) {
                        $("#state").append('<option value="' + key + '">' + value + '</option>');
                    });
                }
            }

        }).done(function() {

            if (old_state) {
                $('#state').val(old_state).trigger('change');
            }
            $('#ajax_loader_div').css('display', 'none');
        });
    }
});

$('.datepicker3').datepicker({
   format: "dd/mm/yyyy",
   autoclose: true
});
  
</script>
@endsection

@section('breadcrumb')
    <li><a href="#"><i class=""></i> {{__('Create User')}} </a></li>
@endsection

@section('main_section')
    <section class="content"> 
         <div id="app">
                    @include('admin.flash-message')
                    @yield('content')
            </div>
            <!-- general form elements -->
            <div class="box box-primary" style="padding-bottom: 25px;">
                <div class="box-header with-border">
<!--         <h3 class="box-title">{{__('customer.mytitle')}} </h3>-->
                </div>  
               <form id="my-form" action="/admin/hr/create/user" method="POST">
                @csrf
                <div class="row">
                        <div class="col-md-6">
                        <label for="store">Store <sup>*</sup></label>
                        @if(count($store) > 1)
                            <select name="store" id="store" class="input-css select2">
                                <option value="">Select Store</option>
                                @foreach($store as $key => $val)
                                    <option value="{{$val->id}}" {{old('store')==$val->id ? 'selected=selected' : ''}} >{{$val->name}}</option>
                                @endforeach
                            </select>
                            {!! $errors->first('store', '<p class="help-block">:message</p>') !!}

                        @else
                        <input type="text" name="store-name" readonly id="store-name" value="{{$store[0]->name}}" class="input-css">
                        <input type="hidden" hidden readonly  name="store"  id="store" value="{{$store[0]->id}}" class="input-css" {{old('store')==$store[0]->name ? 'selected=selected' : ''}} >
                        @endif

                    </div>
                       
                </div>
                <div class="row">
                        <div class="col-md-6">
                            <label>First Name <sup>*</sup></label>
                            <input type="text" name="first_name" class="input-css" value="{{old('first_name')}}">
                            {!! $errors->first('first_name', '<p class="help-block">:message</p>') !!}
                            <br>
                        </div>
                        <div class="col-md-6">
                            <label>Middle Name</label>
                            <input type="text" name="middle_name" class="input-css" value="{{old('middle_name')}}">
                            {!! $errors->first('middle_name
                            ', '<p class="help-block">:message</p>') !!}

                        </div> 
                </div>
                <div class="row">
                        <div class="col-md-6">
                            <label>Last Name</label>
                            <input type="text" name="last_name" class="input-css" value="{{old('last_name')}}">
                            {!! $errors->first('last_name', '<p class="help-block">:message</p>') !!}
                            <br>
                        </div> 
                         <div class="col-md-6">
                            <label>Employee Id<sup>*</sup></label>
                            <input type="emp_id" name="emp_id" class="input-css" value="{{old('emp_id')}}">
                            {!! $errors->first('emp_id', '<p class="help-block">:message</p>') !!}
                            <br>
                        </div>
                                              
                </div>
                  <div class="row">
                        <div class="col-md-6">
                            <label>Email</label>
                            <input type="email" name="email" autocomplete="off" class="input-css" value="{{old('email')}}">
                            {!! $errors->first('email', '<p class="help-block">:message</p>') !!}
                            <br>
                        </div> 
                         <div class="col-md-6">
                            <label>Phone<sup>*</sup></label>
                            <input type="phone" name="phone" class="input-css" value="{{old('phone')}}">
                            {!! $errors->first('phone', '<p class="help-block">:message</p>') !!}
                            <br>
                        </div>
                                              
                </div>
                           
                  <div class="row">
                   <div class="col-md-6 {{ $errors->has('gender') ? 'has-error' : ''}}">
                            <label>Gender<sup>*</sup></label>
                            <input type="radio" class="gender" name="gender"  {{(old('gender') == 'Male')? 'checked': ''}} value="Male">Male 
                            <input type="radio" class="gender" name="gender" {{(old('gender') == 'Female')? 'checked': ''}} value="Female" style="margin-left: 15px;" > Female
                            {!! $errors->first('gender', '<p class="help-block">:message</p>') !!}
                        </div>
                     <div class="col-md-6">
                        <label>{{__('Relation Type')}} <sup>*</sup></label>
                        <select name="relation_type" id="relation_type" class="input-css select2">
                            <option value="">Select Relation Type</option>
                            <option value="S/O" {{(old('relation_type') == 'S/O')? 'selected': ''}} >S/O</option>
                            <option value="D/O" {{(old('relation_type') == 'D/O')? 'selected': ''}}>D/O</option>
                            <option value="W/O" {{(old('relation_type') == 'W/O')? 'selected': ''}}>W/O</option>
                        </select>
                        {!! $errors->first('relation_type', '<p class="help-block">:message</p>') !!}
                     </div>
                    
                 </div>
                 <div class="row">
                     <div class="col-md-6">
                        <label>{{__('Relation Name')}}  <sup>*</sup></label>
                        <input type="text" name="relation" class="input-css" value="{{old('relation')}}">
                            {!! $errors->first('relation', '<p class="help-block">:message</p>') !!}
                     </div>
                    <div class="col-md-6">
                        <label>Alias Name</label>
                        <input type="text" name="alias_aadhar" class="input-css" value="{{old('alias_aadhar')}}">
                        {!! $errors->first('alias_aadhar', '<p class="help-block">:message</p>') !!}

                    </div>
                </div>
                 
                <div class="row">
                     <div class="col-md-6">
                        <label>JPM reference</label>
                       <input type="text" name="reference_jpm" class="input-css" value="{{old('reference_jpm')}}">
                        {!! $errors->first('reference_jpm', '<p class="help-block">:message</p>') !!}

                    </div>
                    <div class="col-md-6">
                        <label>{{__('customer.state')}}</label>
                        <select name="state" id="state" class="input-css select2" style="width:100%">
                                <option value="" selected disabled>Select State</option>
                            </select>
                            {!! $errors->first('state', '<p class="help-block">:message</p>') !!}

                    </div>
                </div>
                    <div class="row">
                            <div class="col-md-6">
                                <label>{{__('customer.city')}}<sup>*</sup></label>
                                <select name="city" id="city" class="input-css select2" style="width:100%">
                                <option value="" disabled>Select City</option>

                                </select>
                                {!! $errors->first('city', '<p class="help-block">:message</p>') !!}
                            </div>
                            <div class="col-md-6">
                                <label>{{__('Pin Code')}} <sup>*</sup></label>
                                <input type="number" name="pin_code" id="pin_code" class="input-css" value="{{old('pin_code')}}">
                                {!! $errors->first('pin_code', '<p class="help-block">:message</p>') !!}
                            </div>
                    </div>
                <div class="row">
                    <div class="col-md-6">
                        <label>Aadhar <sup>*</sup></label>
                        <input type="number" name="aadhar" class="input-css" value="{{old('aadhar')}}">
                        {!! $errors->first('aadhar', '<p class="help-block">:message</p>') !!}

                    </div>
                    <div class="col-md-6">
                        <label>Pan Card  <sup>*</sup></label>
                        <input type="text" name="pancard" class="input-css" value="{{old('pancard')}}">
                        {!! $errors->first('pancard', '<p class="help-block">:message</p>') !!}

                    </div>
                </div>
                 <div class="row">
                    <div class="col-md-6">
                        <label>Date Of Joining <sup>*</sup></label>
                        <input type="text" name="doj" id="doj" autocomplete="off" value="{{old('doj')}}" class=" datepicker3 input-css">
                        {!! $errors->first('doj', '<p class="help-block">:message</p>') !!}

                    </div>
                     <div class="col-md-6">
                            <label>DOB <sup>*</sup></label>
                            <input type="text" name="dob" value="{{old('dob')}}" autocomplete="off" id="dob" class=" datepicker3 input-css">
                        {!! $errors->first('dob', '<p class="help-block">:message</p>') !!}

                        </div>
                </div>  
                <div class="row">
                     <div class="col-md-6">
                            <label>Address<sup>*</sup></label>
                            <textarea class="input-css" name="address" rows="3">{{old('address')}}</textarea>
                            {!! $errors->first('address', '<p class="help-block">:message</p>') !!}
                        </div>
                </div>              
                <div class="submit-button">
                <button type="submit" class="btn btn-primary">Submit</button>
                    
                </div>
                 </form>                 
            </div>
        
      </section>
@endsection