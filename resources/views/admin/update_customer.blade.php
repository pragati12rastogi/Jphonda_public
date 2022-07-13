@extends($layout)

@section('title', __('customer.edit'))

@section('js')
<script src="/js/pages/customer_registration.js"></script>
<script>
</script>
@endsection

@section('breadcrumb')
    <li><a href="#"><i class=""></i> {{__('customer.edit')}} </a></li>
@endsection

@section('main_section')
    <section class="content"> 
         <div id="app">
                    @include('admin.flash-message')
                    @yield('content')
            </div>
            <!-- general form elements -->
{{-- {{$errors}} --}}
            <div class="box box-primary" style="padding-bottom: 25px;">
                <div class="box-header with-border">
<!--         <h3 class="box-title">{{__('customer.mytitle')}} </h3>-->
                </div>
                @foreach($customerdata as $customer)
               <form id="my-form" action="/admin/customer/update/{{$customer->id}}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <label>{{__('For ')}}</label>
                        <select name="cust_for" id="cust_for" class="input-css">
                            {{-- <option value="">Select Relation Type</option> --}}
                            <option value="sale" {{(old('cust_for') == 'sale')? 'selected': '' }} >Sale</option>
                            <option value="service" {{(old('cust_for') == 'service')? 'selected': '' }}>Service</option>
                        </select>
                    </div>
                </div>
                <div class="row">

                    {{-- {{$customer->cname}} --}}
                        <div class="col-md-6">
                            <label>{{__('customer.name')}} <sup>*</sup></label>
                            <input type="text" name="name" value="{{$customer->name}}" {{($flag == 0)? 'readonly': '' }} class="input-css">
                            {!! $errors->first('name', '<p class="help-block">:message</p>') !!}

                            <br>
                        </div>
                        <div class="col-md-6">
                            <label>{{__('customer.contact')}} <sup>*</sup></label>
                            <input type="text" name="number" value="{{$customer->mobile}}" class="input-css">
                            {!! $errors->first('number', '<p class="help-block">:message</p>') !!}

                        </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                       <label>{{__('Relation Type')}}<sup class="req">*</sup></label>
                       <select name="relation_type" id="relation_type" class="input-css select2">
                           <option value="">Select Relation Type</option>
                           <option value="S/O" {{(old('relation_type') == 'S/O')? 'selected': (($customer->relation_type == 'S/O')?'selected' : '')}} >S/O</option>
                           <option value="D/O" {{(old('relation_type') == 'D/O')? 'selected': (($customer->relation_type == 'D/O')?'selected' : '')}}>D/O</option>
                           <option value="W/O" {{(old('relation_type') == 'W/O')? 'selected': (($customer->relation_type == 'W/O')?'selected' : '')}}>W/O</option>
                       </select>
                       {!! $errors->first('relation_type', '<p class="help-block">:message</p>') !!}
                    </div>
                    <div class="col-md-6">
                       <label>{{__('Relation Name')}}<sup class="req">*</sup></label>
                       <input type="text" name="relation" class="input-css" value="{{old('relation')?old('relation'):$customer->relation}}">
                           {!! $errors->first('relation', '<p class="help-block">:message</p>') !!}
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <label>{{__('customer.email')}}</label>
                        <input type="email" name="email" value="{{$customer->email_id}}" class="input-css">
                        {!! $errors->first('email', '<p class="help-block">:message</p>') !!}

                    </div>

                    <div class="col-md-6">
                        <label>{{__('Aadhar Number')}}<sup class="req">*</sup></label>
                        <input type="number" name="aadhar" class="input-css" {{($flag == 0)? (($customer->aadhar_id)? 'readonly': ''): '' }} value="{{old('aadhar')?old('aadhar'):$customer->aadhar_id}}">
                        {!! $errors->first('aadhar', '<p class="help-block">:message</p>') !!}

                    </div>
                </div>
                 
                  
                <div class="row">
                    <div class="col-md-6">
                        <label>{{__('Voter Number')}}<sup class="req">*</sup></label>
                        <input type="text" name="voter" class="input-css" {{($flag == 0)? (($customer->voter_id)? 'readonly': '') : '' }} value="{{old('voter')?old('voter'):$customer->voter_id}}">
                        {!! $errors->first('voter', '<p class="help-block">:message</p>') !!}

                    </div>
                    <div class="col-md-6">
                        <label>{{__('customer.add')}}<sup>*</sup></label>
                        <textarea class="input-css" name="address" rows="3">{{$customer->address}}</textarea>
                        {!! $errors->first('address', '<p class="help-block">:message</p>') !!}

                    </div>
                </div>
                 
                <div class="row">
                    <div class="col-md-6">
                        <label>{{__('customer.country')}}<sup>*</sup></label>
                        <select class="form-control input-css select2" name="country" id="country">
                            <option value="{{$customer->country}}" selected>{{$customer->cname}}</option>
                            @foreach ($countries as $country) 
                                <option value="{{$country->id}}"> {{$country->name}} </option>
                            @endforeach
                        </select>
                        {!! $errors->first('country', '<p class="help-block">:message</p>') !!}

                    </div>
                    <div class="col-md-6">
                        <label>{{__('customer.state')}}<sup>*</sup></label>
                        <select name="state" id="state" class="input-css select2" style="width:100%">
                                
                                <option value="" selected disabled>Select State</option>
                                <option value="{{$customer->state}}" selected>{{$customer->sname}}</option>
                            </select>
                            {!! $errors->first('state', '<p class="help-block">:message</p>') !!}

                    </div>
                </div>
                    
                    <div class="row">
                            <div class="col-md-6">
                                <label>{{__('customer.city')}}<sup>*</sup></label>
                                <select name="city" id="city" class="input-css select2" style="width:100%">
                                    <option value="{{$customer->city}}" selected="">{{$customer->city_name}}</option>
                                    <option value="" disabled>Select City</option>
        
                                </select>
                                {!! $errors->first('city', '<p class="help-block">:message</p>') !!}

                            </div>
                            <div class="col-md-6">
                                <input type="hidden" id="location_id" value="{{$customer->location}}">
                                <label>{{__('Location')}}<sup>*</sup></label>
                                <select name="location" id="location" class="input-css select2" style="width:100%">
                                    <option value="" disabled>Select Location</option>
        
                                </select>
                                {!! $errors->first('location', '<p class="help-block">:message</p>') !!}

                            </div>
                            
                            
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label>{{__('Pin Code')}}<sup class="req">*</sup></label>
                                <input type="number" name="pin_code" id="pin_code" class="input-css" value="{{old('pin_code')?old('pin_code'):$customer->pin_code}}">
                                {!! $errors->first('pin_code', '<p class="help-block">:message</p>') !!}

                            </div>
                            <div class="col-md-6">
                                <label>{{__('customer.view_ref')}}</label>
                              <input type="text" name="reference" value="{{$customer->reference}}" class="input-css">
                              {!! $errors->first('reference', '<p class="help-block">:message</p>') !!}

                            </div>
                        </div>
                
                <div class="submit-button">
                <button type="submit" class="btn btn-primary">Update</button>
                    
                </div>
                 </form>   
                 @endforeach              
            </div>
        
      </section>
@endsection