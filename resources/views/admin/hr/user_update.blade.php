@extends($layout)

@section('title', __('User Update Page'))

@section('js')
<script src="/js/pages/hr.js"></script>
<script>

    $('#dob').datepicker({
        format: "dd/mm/yyyy"
    });
    $('#doj').datepicker({
        format: "dd/mm/yyyy"
    });

</script>
@endsection

@section('breadcrumb')
    <li><a href="#"><i class=""></i> {{__('User Update Page')}} </a></li>
     <style>
        .nav1>li>a {
            position: relative;
            display: block;
            padding: 10px 34px;
            background-color: white;
            margin-left: 10px;
        }
        /* .nav1>li>a:hover {
            background-color:#87CEFA;
        
        } */
        </style>
@endsection
@section('main_section')
    <section class="content"> 
         <div id="app">
                    @include('admin.flash-message')
                    @yield('content')
            </div>
               <form id="my-form" action="/admin/hr/user/update/{{$id}}" method="POST">
                @csrf
                  @include('layouts.user_tab')
              <br>
            <!-- general form elements -->
            <div class="box box-primary" style="padding-bottom: 25px;">
                <div class="box-header with-border">
<!--         <h3 class="box-title">{{__('customer.mytitle')}} </h3>-->
                </div>  
                <input type="hidden" name="user_details_id" class="input-css" value="{{$id}}">
                <div class="row">
                        <div class="col-md-6">
                            <label>First Name <sup>*</sup></label>
                            <input type="text" name="first_name" class="input-css"  value="{{isset($customer)==1?($customer->name):''}}">
                            {!! $errors->first('first_name', '<p class="help-block">:message</p>') !!}
                            <br>
                        </div>
                        <div class="col-md-6">
                            <label>Middle Name</label>
                            <input type="text" name="middle_name" class="input-css" value="{{isset($customer)==1?($customer->middle_name):''}}">
                            {!! $errors->first('middle_name
                            ', '<p class="help-block">:message</p>') !!}

                        </div>
                </div>
                <div class="row">
                        <div class="col-md-6">
                            <label>Last Name</label>
                            <input type="text" name="last_name" class="input-css"
                            value="{{isset($customer)==1?($customer->last_name):''}}">
                            {!! $errors->first('last_name', '<p class="help-block">:message</p>') !!}
                            <br>
                        </div>
                        <div class="col-md-6">
                            <label>Employee Id<sup>*</sup></label>
                            <input type="emp_id" name="emp_id" class="input-css" 
                             value="{{isset($customer)==1?($customer->emp_id):''}}">
                            {!! $errors->first('emp_id', '<p class="help-block">:message</p>') !!}
                            <br>
                        </div>
                        
                </div>
                  <div class="row">
                        <div class="col-md-6">
                            <label>Email</label>
                            <input type="email" name="email" autocomplete="off" class="input-css" value="{{isset($customer)==1?($customer->email):''}}">
                            {!! $errors->first('email', '<p class="help-block">:message</p>') !!}
                            <br>
                        </div> 
                         <div class="col-md-6">
                            <label>Phone<sup>*</sup></label>
                            <input type="phone" name="phone" class="input-css" value="{{isset($customer)==1?($customer->phone):''}}">
                            {!! $errors->first('phone', '<p class="help-block">:message</p>') !!}
                            <br>
                        </div>
                                              
                </div>
                  <div class="row">
                    <div class="col-md-6 {{ $errors->has('gender') ? 'has-error' : ''}}">
                            <label>Gender<sup>*</sup></label>
                           
                            

                            <input type="radio" class="gender" name="gender" value="Male"  <?php if(isset($customer)==1) {if ($customer->gender == 'Male') { echo "checked";
                            }}?> >Male 
                            <input type="radio" class="gender" name="gender" value="Female" style="margin-left: 15px;"  <?php if(isset($customer)==1) {if ($customer->gender == 'Female') { echo "checked";
                            }}?>> Female
                            {!! $errors->first('gender', '<p class="help-block">:message</p>') !!}
                        </div>
                    <div class="col-md-6">
                       <label>{{__('Relation Type')}}</label>
                       <select name="relation_type" id="relation_type" class="input-css select2">
                           <option value="">Select Relation Type</option>
                           <option value="S/O"   {{isset($customer)==1? ($customer->relation_type=='S/O'? 'selected' : ''):''}} >S/O</option>
                           <option value="D/O" {{isset($customer)==1? ($customer->relation_type=='D/O'? 'selected' : ''):''}}>D/O</option>
                           <option value="W/O" {{isset($customer)==1? ($customer->relation_type=='W/O'? 'selected' : ''):''}}>W/O</option>
                       </select>
                       {!! $errors->first('relation_type', '<p class="help-block">:message</p>') !!}
                    </div>
                    
                </div>
                 <div class="row">
                    <div class="col-md-6">
                       <label>{{__('Relation Name')}}</label>
                       <input type="text" name="relation" class="input-css"  value="{{isset($customer)==1?($customer->relation_name):''}}" >
                           {!! $errors->first('relation', '<p class="help-block">:message</p>') !!}
                    </div>
                    <div class="col-md-6">
                        <label>Alias Name</label>
                        <input type="text" name="alias_aadhar" class="input-css" value="{{isset($customer)==1?($customer->alias_name):''}}">
                        {!! $errors->first('alias_aadhar', '<p class="help-block">:message</p>') !!}

                    </div>
                </div>
                 
                <div class="row">
                    <div class="col-md-6">
                        <label>JPM reference</label>
                       <input type="text" name="reference_jpm" class="input-css" value="{{isset($customer)==1?($customer->jpm_reference):''}}">
                        {!! $errors->first('reference_jpm', '<p class="help-block">:message</p>') !!}

                    </div>
                    <div class="col-md-6">
                        <label>{{__('customer.state')}}</label>
                        <select name="state" id="state" class="input-css select2" style="width:100%">
                              @foreach($state as $item)
                                        <option value="{{$item->id}}" 
                                            {{isset($customer)==1? ($item->id==$customer->state? 'selected' : ''):''}}>{{$item->name}}</option>
                                    @endforeach
                            </select>
                            {!! $errors->first('state', '<p class="help-block">:message</p>') !!}

                    </div>
                </div>
                    <div class="row">
							<div class="col-md-6">
								<label>{{__('customer.city')}}</label>
								 <select name="city" id="city" class="input-css select2" style="width:100%">

                                     @foreach($city as $items)
                                        <option value="{{$items->id}}" 
                                            {{isset($customer)==1? ($items->city==$customer->city_name? 'selected' : ''):''}}>{{$items->city}}</option>
                                    @endforeach

                                    <option value="" disabled>Select City</option>
        
                                </select>
								{!! $errors->first('city', '<p class="help-block">:message</p>') !!}
							</div>
							<div class="col-md-6">
                                <label>{{__('Pin Code')}}</label>
                                <input type="number" name="pin_code" id="pin_code" class="input-css" value="{{isset($customer)==1?($customer->pincode):''}}">
                                {!! $errors->first('pin_code', '<p class="help-block">:message</p>') !!}
                            </div>
                    </div>
                <div class="row">
                    <div class="col-md-6">
                        <label>Aadhar</label>
                        <input type="number" name="aadhar" class="input-css"
                        value="{{isset($customer)==1?($customer->aadhar):''}}">
                        {!! $errors->first('aadhar', '<p class="help-block">:message</p>') !!}

                    </div>
                    <div class="col-md-6">
                        <label>Pan Card</label>
                        <input type="text" name="pancard" class="input-css"  value="{{isset($customer)==1?($customer->pancard):''}}">
                        {!! $errors->first('pancard', '<p class="help-block">:message</p>') !!}

                    </div>
                </div>
                 <div class="row">
                    <div class="col-md-6">
                        <label>Date Of Joining</label>
                        <input type="text" name="doj" id="doj" autocomplete="off" class="input-css" value="{{isset($customer)==1?(date('d/m/Y', strtotime($customer->doj))):''}}">
                        {!! $errors->first('doj', '<p class="help-block">:message</p>') !!}

                    </div>
                    <div class="col-md-6">
                            <label>DOB <sup>*</sup></label>
                            <input type="text" name="dob" id="dob"  autocomplete="off" class="input-css"value="{{isset($customer)==1?(date('d/m/Y', strtotime($customer->dob))):''}}">
                        {!! $errors->first('dob', '<p class="help-block">:message</p>') !!}

                        </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                            <label>Address <sup>*</sup></label>
                            <textarea class="input-css" name="address" rows="3">{{isset($customer)==1?($customer->address):''}}</textarea>
                            {!! $errors->first('address', '<p class="help-block">:message</p>') !!}
                        </div>
                    @if(isset($customer)==1)
                     @if($customer->status!='2')
                    <div class="col-md-6 {{ $errors->has('status') ? 'has-error' : ''}}">
                        <label>Status <sup>*</sup></label>
                        <select  class="input-css select2 status" name="status">
                            <option value="">Select status</option>
                            <option value="0"  {{isset($customer)==1? ($customer->status=='0'? 'selected' : ''):''}} >InActive</option>
                            <option value="1" {{isset($customer)==1? ($customer->status=='1'? 'selected' : ''):''}} >Active</option>
                           
                        </select>
                        {!! $errors->first('status', '<p class="help-block">:message</p>') !!}
                    </div>
                    @endif
                    @endif
                </div>
                <div class="submit-button">
                <button type="submit" class="btn btn-primary">Submit</button>
                    
                </div>
                 </form>                  
            </div>
        
      </section>
@endsection