@extends($layout)
@section('title', __('Create Booking'))
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
   .datepicker {
       z-index: 1064 !important;
   }
</style>
@endsection
@section('js')
<script src="/js/dataTables.responsive.js"></script>
<script type="text/javascript">

  var oldtime=@php echo json_encode(old('service_time')); @endphp;

  $(document).ready(function(){

    if (oldtime==null) {
       $('.timepicker').wickedpicker({
            twentyFour:true,
        });
    }

     if (oldtime!=null) {
       $('.timepicker').wickedpicker({
            twentyFour:true,
            now:oldtime,
        });
    }

  });
  var today = new Date();
    $('.datepicker3').datepicker({
   format: "yyyy-mm-dd",
   startDate: today,
   autoclose: true
});


$(document).on('change','.pick_drop',function(){
    $("#pick_drop").toggle('display');
  });
</script>
@endsection
@section('main_section')
<section class="content">
   <div id="app">

         @include('admin.flash-message')
         @yield('content')
         <div class="box-header with-border">

            <div class="box box-primary">
                <div class="box-header">
                </div>
                <form  action="/admin/service/create/booking" method="post">
                    @csrf

                    @if($booking != '')
                    <div class="col-md-12">
                        <div class="col-md-3">
                            <label >CUSTOMER NAME : {{$booking['name']}} </label>
                        </div>
                        <div class="col-md-3">
                            <label >CONTACT NUMBER : {{$booking['mobile']}}</label>
                        </div>
                        <input type="hidden" name="booking_id" value="{{$booking['id']}}">
                    </div>
                    @endif
                    <div class="row">

                  <div class="col-md-12 margin-bottom">
                     <div class="col-md-6">
                        <label for="frame">Frame Number <sup>*</sup></label>
                        <input type="text" name="frame" value="{{old('frame')}}" id="frame" placeholder="Enter Frame Number" class="input-css">
                        {!! $errors->first('frame', '<p class="help-block">:message</p>') !!}
                     </div>
                      <div class="col-md-6">
                        <label for="registration">Registraion Number <sup>*</sup></label>
                        <input type="text" name="registration" value="{{old('registration')}}" id="registration" placeholder="Enter Registration Number" class="input-css">
                        {!! $errors->first('registration', '<p class="help-block">:message</p>') !!}
                     </div>
                  </div>
              <div class="col-md-12 margin-bottom">
                   <div class="col-md-6">
                        <label >Service Type <sup>*</sup></label>
                        <select name="service_type" id="service_type" class="input-css select2">
                          <option value="">Select service Type</option>
                          @foreach($jobcard_type as $k => $v)
                            <option value="{{$v->key}}" @if(old('service_type') == $v->key) selected @endif>{{$v->value}}</option>
                          @endforeach
                        </select>
                        {!! $errors->first('remark', '<p class="help-block">:message</p>') !!}
                      </div>
                     <div class="col-md-6">
                        <label >Service Date <sup>*</sup></label>
                        <input type="text" name="service_date" value="{{old('service_date')}}" id="service_date" autocomplete="off" class="input-css datepicker3" >
                        {!! $errors->first('service_date', '<p class="help-block">:message</p>') !!}
                      </div>
               </div>
                <div class="col-md-12 margin-bottom">
                    <div class="col-md-6">
                        <label >Service Time <sup>*</sup></label>
                        <input type="text" name="service_time" value="{{old('service_time')}}" id="service_time" class="input-css timepicker">
                        {!! $errors->first('service_time', '<p class="help-block">:message</p>') !!}
                      </div>
                      <div class="col-md-6">
                        <label >Pick and Drop <sup>*</sup></label>
                        <div class="col-md-6">
                          <label><input type="radio" name="pick_drop" value="yes" @if(old('pick_drop') == 'yes') selected @endif class="pick_drop">  Yes</label>
                        </div>
                        <div class="col-md-6">
                          <label><input type="radio" checked name="pick_drop" value="no" class="pick_drop"> No</label>
                          
                        </div>
                        {!! $errors->first('pick_drop', '<p class="help-block">:message</p>') !!}
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
                    <div class="col-md-6" id="pick_drop" style="display:none">
                       <label>
                          <input type="checkbox" checked name="pickup_drop[]" value="pick"  class=""> PickUp
                       </label>
                       <label>
                          <input type="checkbox" checked name="pickup_drop[]" value="drop" class=""> Drop
                       </label>
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