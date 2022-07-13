@extends($layout)

@section('title', __('JP Honda'))
@section('css')
<link rel="stylesheet" href="/css/style.css">
<link rel="stylesheet" href="/front/css/service.css">
<link rel="stylesheet" href="/css/bootstrap-datepicker.min.css">


@endsection
@section('js')
<script src="/js/bootstrap-datepicker.min.js"></script>
<script>

  $(document).ready(function(){
        var last_date = @php echo json_encode($lastdate); @endphp;

        $('.datepicker3').datepicker({
           format: "yyyy-mm-dd",
           autoclose: true,
            startDate: new Date(last_date),
        });
   });


</script>
@endsection

@section('main_section')
<div class="about-us-area overview-area pt-60">
    <div class="breadcrumb-area breadcrumb-area pb-10">
                <div class="container-fluid">
                    <div class="breadcrumb-content text-center contactus_header">
                    <h2>{{__('front/front.hsrp')}}</h2>
                 </div>
                </div>
            </div>
    <div class="container">
            <div class="col-lg-12 col-md-12">
                    <div id="app">
                        @include('admin.flash-message')
                        @yield('content')
                    </div>
                    <div class="col-lg-8 col-md-12 col-12">
                        <form action="/hsrp/request" method="POST">
                            @csrf
                            <div class="checkbox-form">  
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="checkout-form-list">
                                            <label>{{__('front/hsrp.name')}} <span class="required">*</span></label>          
                                            <input type="text" name="name" value="{{old('name')}}">
                                            {!! $errors->first('name', '<p class="help-block">:message</p>') !!}
                                        </div>
                                    </div>
                                    <div class="col-md-6 ">
                                        <div class="checkout-form-list">
                                            <label>{{__('front/hsrp.mobile')}} <span class="required">*</span></label>
                                            <input type="text" name="mobile" value="{{old('mobile')}}">
                                            {!! $errors->first('mobile', '<p class="help-block">:message</p>') !!}
                                        </div>
                                    </div>
                                    </div>
                                    <div class="row pt-20">
                                        <div class="col-md-6">
                                            <div class="checkout-form-list">
                                                <label>{{__('front/hsrp.frame')}} #<span class="required">*</span></label>          
                                                <input type="text" name="frame" value="{{old('frame')}}">
                                                {!! $errors->first('frame', '<p class="help-block">:message</p>') !!}
                                            </div>
                                        </div>
                                        <div class="col-md-6 ">
                                            <div class="checkout-form-list">
                                                <label>{{__('front/hsrp.engine')}} #</label>
                                                <input type="text" name="engine" value="{{old('engine')}}">
                                                {!! $errors->first('engine', '<p class="help-block">:message</p>') !!}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row pt-20">
                                        <div class="col-md-6 ">
                                            <div class="checkout-form-list">
                                                <label>{{__('front/hsrp.fueltype')}}<span class="required">*</span></label>
                                                <select name="fueltype" class="input-css select2" id="fueltype">
                                                   <option value="">Select FuelType</option>
                                                    <option value="petrol" {{(old('fueltype') == 'petrol')? 'selected': ''}} >Petrol</option>
                                                   <!-- <option value="diesel" {{(old('fueltype') == 'diesel')? 'selected': ''}}>Diesel</option> -->
                                                </select>
                                                {!! $errors->first('fueltype', '<p class="help-block">:message</p>') !!}
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="checkout-form-list">
                                                <label>{{__('front/hsrp.registration')}} #<span class="required">*</span></label>          
                                                <input type="text" name="registration" value="{{old('registration')}}">
                                                {!! $errors->first('registration', '<p class="help-block">:message</p>') !!}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row pt-20">
                                      <div class="col-md-6 ">
                                            <div class="checkout-form-list">
                                                <label>{{__('front/hsrp.type')}}<span class="required">*</span></label>
                                                <select name="type" class="input-css select2" id="type">
                                                   <option value="">Select Type</option>
                                                    <option value="private" {{(old('type') == 'private')? 'selected': ''}}>Private</option>
                                                   <option value="commercial" {{(old('type') == 'commercial')? 'selected': ''}} >Commercial</option>
                                                </select>
                                                {!! $errors->first('type', '<p class="help-block">:message</p>') !!}
                                            </div>
                                        </div>
                                       <div class="col-md-6">
                                            <div class="checkout-form-list">
                                                <label>{{__('front/hsrp.date')}} <span class="required">*</span></label>          
                                                <input type="text" autocomplete="off" value="{{old('date')}}" name="date" id="date" class="datepicker3 input-css">
                                                {!! $errors->first('date', '<p class="help-block">:message</p>') !!}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row pt-20">
                                       <div class="col-md-6 ">
                                            <div class="checkout-form-list">
                                                <label>{{__('front/hsrp.vechicle_type')}}<span class="required">*</span></label>
                                                <select name="vechicle_type" class="input-css select2" id="vechicle_type">
                                                   <option value="">Select Vechicle Type</option>
                                                    <option value="SC" {{(old('vechicle_type') == 'SC')? 'selected': ''}}>Scooty</option>
                                                   <option value="MC"  {{(old('vechicle_type') == 'MC')? 'selected': ''}}>Motorcycle</option>
                                                </select>
                                                {!! $errors->first('vechicle_type', '<p class="help-block">:message</p>') !!}
                                            </div>
                                        </div>
                                       <div class="col-md-6">
                                            <div class="checkout-form-list">
                                                <label>{{__('front/hsrp.oem')}} <span class="required">*</span></label>          
                                                <input type="text" name="oem" value="{{old('oem')}}">
                                                {!! $errors->first('oem', '<p class="help-block">:message</p>') !!}
                                            </div>
                                        </div>
                                       
                                    </div>
                                    <div class="row">
                                       <div class="contact-form-style">            
                                          <button class="submit cr-btn btn-style" type="submit"><span>{{__('front/front.submit')}}</span></button>
                                        </div>
                                    </div>
                                </div>                                                  
                            </div>
                        </form>
                    </div>
        </div>
    </div>
</div>
@endsection