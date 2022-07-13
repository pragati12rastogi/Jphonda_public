@extends($layout)

@section('title', __('Other Details Page'))

@section('js')
<script src="/js/pages/hr.js"></script>
<script>

   $('.to').on('click' , function (){
        $('.to').wickedpicker({
         now: "12:00", 
  });
});
$('.from').on('click' , function (){
        $('.from').wickedpicker({
        now: "10:00", 
  });
});

</script>
@endsection

@section('breadcrumb')
    <li><a href="#"><i class=""></i> {{__('Other Details Page')}} </a></li>
    
@endsection
@section('main_section')
    <section class="content"> 
         <div id="app">
                    @include('admin.flash-message')
                    @yield('content')
            </div>
               <form id="my-form" action="/admin/hr/user/otherdocumentupdate/{{$id}}" method="POST" enctype="multipart/form-data">
                @csrf
                  @include('layouts.user_tab')
                   @php
              $shift=explode(' to ',$customer['shifting_timing']);
          @endphp
              <br>
            <!-- general form elements -->
            <div class="box box-primary" style="padding-bottom: 25px;">
                <div class="box-header with-border">
                </div>  
                <div class="row">
                    <input type="hidden" name="user_id" class="input-css" value="{{$id}}">
                   <div class="col-md-6 {{ $errors->has('store_name') ? 'has-error' : ''}}">
                                <label>{{__('admin.store')}} <sup>*</sup></label>
                                <select name="store_name[]" id="store_name" class="input-css select2" multiple="multiple" data-placeholder="Select" style="width: 100%;">
                                    <option value="">Select Store</option>
                                     <?php $store_id = explode(',', $customer['store_id']);
                                        $count = count($store_id);
                                         ?>
                                    @foreach($store as $key)
                                        @if(in_array($key->id,$store_id))
                                        <option value="{{$key->id}}" selected>{{$key->name.'-'.$key->store_type}}</option>
                                        @else
                                        <option value="{{$key->id}}" >{{$key->name.'-'.$key->store_type}}</option>
                                        @endif
                                    @endforeach
                                   <?php 
                                    ?>
                                </select>
                                {!! $errors->first('store_name', '<p class="help-block">:message</p>') !!}
                            </div>
                        <div class="col-md-6">
                            <label>Hirise ID<sup>*</sup></label>
                            <input type="text" name="hirise" class="input-css" value="{{(old('hirise')) ? old('hirise') : $customer['hirise']}}">
                            {!! $errors->first('hirise', '<p class="help-block">:message</p>') !!}

                        </div>
                </div>
                <div class="row">
                         <div class="col-md-6 {{ $errors->has('biometric') ? 'has-error' : ''}}">
                            <label>BioMetric Created<sup>*</sup></label>
                            <input type="radio" class="biometric" name="biometric" value="Yes" <?php if ($customer['biometric'] == 'Yes') { echo "checked";
                            }?> > Yes 
                            <input type="radio" class="biometric" name="biometric" value="NO" <?php  if ($customer['biometric'] == 'No') { echo "checked";
                            }?> style="margin-left: 15px;" > No
                            {!! $errors->first('biometric', '<p class="help-block">:message</p>') !!}
                        </div>
                        <div class="col-md-6 {{ $errors->has('upd_photo') ? ' has-error' : ''}}">
                        <label for="">Photo Upload </label>
                        @if($customer['profile_photo'] != "" || $customer['profile_photo'] != null)
                        @if (file_exists(public_path().'/upload/adminprofile/'.$customer['profile_photo'] ))
                        <img src="{{ asset('upload/adminprofile')}}/{{$customer['profile_photo']}}" height="50" width="100">
                        @endif
                        @endif
                        <br>
                        <br>
                        <input type="file" accept="image/png,image/jpeg" name="upd_photo" value="{{$customer['profile_photo']}}" id="" class="upd_photo ">
                        {!! $errors->first('upd_photo', '<p class="help-block">:message</p>') !!} 
                        <input type="text" name="old_image" value="{{$customer['profile_photo']}}" hidden>
                        </div>
                        
                </div>
                 <div class="row">
                  
                    <div class="col-md-3 {{ $errors->has('shift_timing') ? 'has-error' : ''}}">
                        <label>{{__('Shift Timing')}} </label>
                        <select class="input-css shift_timing select2" name="shift_timing" >
                            <option value="">--Select Shift Timing--</option>
                                @foreach($shift_timing as $time)
                                <option value="{{$time->id}}" {{ ( ($time->id) == ($customer['shift_timing_id'])) ? 'selected' : '' }}>{{$time->shift_timing}}</option>
                                @endforeach
                        </select>
                    </div>
                   
                    <div class="col-md-3 {{ $errors->has('role') ? 'has-error' : ''}}">
                        <label>{{__('admin.role')}} <sup>*</sup></label>
                        <select  class="input-css select2 role" name="role">
                            <option selected disabled>Select Role</option>
                            @foreach($role as $item)
                            <option value="{{$item->key}}" {{ ( ($item->key) == ($customer['role'])) ? 'selected' : '' }}>{{$item->value}}</option>
                            @endforeach
                        </select>
                        {!! $errors->first('role', '<p class="help-block">:message</p>') !!}
                    </div>
                </div>
                <div class="row">
                     <div class="col-md-6 {{ $errors->has('reporting_manager') ? ' has-error' : ''}}">
                    <label for="">Reporting Manager <sup>*</sup></label>
                    <select class="input-css reporting_manager select2" id="reporting_manager" style="padding-top:2px" name="reporting_manager" >
                        <option value="">--Select Reporting Manager--</option>
                            @foreach($users as $item)
                            <option value="{{$item->id}}" {{ ( ($item->id) == ($customer['reporting_manager'])) ? 'selected' : '' }}>{{$item->name}} {{$item->middle_name}} {{$item->last_name}}</option>
                            @endforeach
                    </select>
                    {!! $errors->first('reporting_manager', '<p class="help-block">:message</p>') !!} 
                </div> 
                </div>
                <div class="submit-button">
                <button type="submit" class="btn btn-primary">Submit</button>
                    
                </div>
                 </form>                   
            </div>
        
      </section>
@endsection

