@extends($layout)

@section('title', __('customer.title'))

@section('js')
<script src="/js/pages/customer_registration.js"></script>
<script>
    var old_country = @json((old('country') ? old('country') : '105'));
    var old_state = @json((old('state') ? old('state') : '23'));
    var old_city = @json((old('city') ? old('city') : '561'));
    var old_location = @json((old('location') ? old('location') : ''));
    

    if(old_country)
    {
        $('#country').val(old_country); 
        $("#country").trigger('change');

    }
    
</script>
@endsection

@section('breadcrumb')
    <li><a href="#"><i class=""></i> {{__('customer.title')}} </a></li>
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
               <form id="my-form" action="/admin/customer/registration" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <label>{{__('For ')}}</label>
                        <select name="cust_for" id="cust_for" class="input-css">
                            {{-- <option value="">Select Relation Type</option> --}}
                            <option value="sale" {{(old('cust_for') == 'sale')? 'selected': (($for == 'sale')? 'selected' : '') }} >Sale</option>
                            <option value="service" {{(old('cust_for') == 'service')? 'selected': (($for == 'service')? 'selected' : '') }}>Service</option>
                            <option value="otcsale" {{(old('cust_for') == 'otcsale')? 'selected': (($for == 'otcsale')? 'selected' : '') }}>OtcSale</option>
                            <option value="InsuranceRenewal" {{(old('cust_for') == 'InsuranceRenewal')? 'selected': (($for == 'InsuranceRenewal')? 'selected' : '') }}>Insurance Renewal</option>
                        </select>
                    </div>
                </div>
                <br>
                <div class="row">
                        <div class="col-md-6">
                            <label>{{__('customer.name')}} <sup>*</sup></label>
                            <input type="text" name="name" class="input-css" value="{{old('name')}}">
                            {!! $errors->first('name', '<p class="help-block">:message</p>') !!}
                            <br>
                        </div>
                        <div class="col-md-6">
                            <label>{{__('customer.contact')}} <sup>*</sup></label>
                            <input type="text" name="number" class="input-css" value="{{old('number')}}">
                            {!! $errors->first('number', '<p class="help-block">:message</p>') !!}

                        </div>
                </div>
                 <div class="row">
                     <div class="col-md-6">
                        <label>{{__('Relation Type')}}<sup class="req">*</sup></label>
                        <select name="relation_type" id="relation_type" class="input-css select2">
                            <option value="">Select Relation Type</option>
                            <option value="S/O" {{(old('relation_type') == 'S/O')? 'selected': ''}} >S/O</option>
                            <option value="D/O" {{(old('relation_type') == 'D/O')? 'selected': ''}}>D/O</option>
                            <option value="W/O" {{(old('relation_type') == 'W/O')? 'selected': ''}}>W/O</option>
                        </select>
                        {!! $errors->first('relation_type', '<p class="help-block">:message</p>') !!}
                     </div>
                     <div class="col-md-6">
                        <label>{{__('Relation Name')}}<sup class="req">*</sup></label>
                        <input type="text" name="relation" class="input-css" value="{{old('relation')}}">
                            {!! $errors->first('relation', '<p class="help-block">:message</p>') !!}
                     </div>
                 </div>
                <div class="row">
                    <div class="col-md-6">
                        <label>{{__('customer.email')}}</label>
                        <input type="email" name="email" class="input-css" value="{{old('email')}}">
                            {!! $errors->first('email', '<p class="help-block">:message</p>') !!}

                    </div>
                    {{-- <div class="col-md-6">
                        <label>{{__('customer.pass')}}</label>
                        <input type="password" name="password" class="input-css">
                    </div> --}}
                    <div class="col-md-6">
                        <label>{{__('Aadhar Number')}}<sup class="req">*</sup></label>
                        <input type="number" name="aadhar" class="input-css" value="{{old('aadhar')}}">
                        {!! $errors->first('aadhar', '<p class="help-block">:message</p>') !!}

                    </div>
                </div>
                 
                  
                <div class="row">
                    <div class="col-md-6">
                        <label>{{__('Voter Number')}}<sup class="req">*</sup></label>
                        <input type="text" name="voter" class="input-css" value="{{old('voter')}}">
                        {!! $errors->first('voter', '<p class="help-block">:message</p>') !!}

                    </div>
                    <div class="col-md-6">
                        <label>{{__('customer.add')}}<sup class="">*</sup></label>
                        <textarea class="input-css" name="address" rows="3">{{old('address')}}</textarea>
                        {!! $errors->first('address', '<p class="help-block">:message</p>') !!}

                    </div>
                </div>
                 
                <div class="row">
                    <div class="col-md-6">
                        <label>{{__('customer.country')}}<sup class="">*</sup></label>
                        <select class="form-control input-css select2" name="country" id="country">
                            <option value="" selected disabled>Select State</option>
                            @foreach ($countries as $country) 
                                <option value="{{$country->id}}"   @if($country->id == '105') selected @endif > {{$country->name}} </option>
                            @endforeach
                        </select>
                        {!! $errors->first('country', '<p class="help-block">:message</p>') !!}
                    </div>
                    <div class="col-md-6">
                        <label>{{__('customer.state')}}<sup class="">*</sup></label>
                        <select name="state" id="state" class="input-css select2" style="width:100%">
                                <option value="" selected disabled>Select State</option>
                            </select>
                            {!! $errors->first('state', '<p class="help-block">:message</p>') !!}

                    </div>
                </div>
                    
                    <div class="row">
                            <div class="col-md-6">
                                <label>{{__('customer.city')}}<sup class="">*</sup></label>
                                <select name="city" id="city" class="input-css select2" style="width:100%">
                                    <option value="" disabled>Select City</option>
                                        
                                </select>
                                {!! $errors->first('city', '<p class="help-block">:message</p>') !!}

                            </div>
                            <div class="col-md-6">
                                <label>{{__('Location')}}<sup class="">*</sup></label>
                                <select name="location" id="location" class="input-css select2" style="width:100%">
                                    <option value="" disabled>Select Location</option>
                                </select>
                                {!! $errors->first('location', '<p class="help-block">:message</p>') !!}

                            </div>
                            
                            
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label>{{__('Pin Code')}}<sup class="req">*</sup></label>
                                <input type="number" name="pin_code" id="pin_code" class="input-css" value="{{old('pin_code')}}">
                                {!! $errors->first('pin_code', '<p class="help-block">:message</p>') !!}

                            </div>
                            <div class="col-md-6">
                                <label>{{__('Reference Name')}}</label>
                                <input type="text" name="reference" class="input-css" value="{{old('reference')}}">
                              {!! $errors->first('reference', '<p class="help-block">:message</p>') !!}

                            </div>
                        </div>
                
                <div class="submit-button">
                <button type="submit" class="btn btn-primary">Submit</button>
                    
                </div>
                 </form>                 
            </div>
        
      </section>
@endsection