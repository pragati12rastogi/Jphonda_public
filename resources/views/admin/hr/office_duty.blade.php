@extends($layout)

@section('title', __('Office Duty'))

@section('js')
<script>
  var time=@php echo json_encode(old('start_time')) @endphp;

  $(document).ready(function(){
       if(time!=null){
        $('.timepicker').wickedpicker({
          twentyFour:true,
          now:time
      });
    }else{
      $('.timepicker').wickedpicker({
      twentyFour:true,

      }); 
    }
  });

</script>
@endsection

@section('breadcrumb')
    <li><a href="#"><i class=""></i> {{__('Office Duty')}} </a></li>
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
               <form id="my-form" files="true" action="/admin/hr/office/duty" method="POST">
                @csrf
                <div class="row">    
                     <?php 
                         $count = count($store); ?>
                          @if ($count <= 1) 
                              <div class="col-md-6" style="display: none;">
                                <label>{{__('admin.store')}} <sup>*</sup></label>
                                <select class="input-css" name="store" id="store">
                                    <option value=" ">Select store</option>
                                    @foreach ($store as $item)
                                    <option value="{{$item->id}}" selected="">{{$item->name}}</option>
                                    @endforeach
                                </select>
                                {!! $errors->first('store', '<p class="help-block">:message</p>') !!}
                            </div>
                         @else
                       <div class="col-md-6">
                            <label>{{__('fuel.store')}} <sup>*</sup></label>
                            <select name="store" class="input-css select2" style="width:100%">
                                <option value=" ">Select store</option>
                                @foreach ($store as $item)
                               
                                    <option value="{{$item->id}}"  {{old('store')==$item->id ? 'selected=selected':''}}>{{$item->name}}</option>
                                @endforeach
                            </select>
                            {!! $errors->first('store', '<p class="help-block">:message</p>') !!}

                        </div>
                        @endif
                       <div class="col-md-6">
                            <label>Employee Name<sup>*</sup></label>
                            <select name="employee" id="employee" class="input-css select2" style="width:100%">
                                <option value=" ">Select employee</option>
                                @foreach($employee as $emp)
                                	<option value="{{$emp->emp_id}}" {{old('employee')==$emp->emp_id ? 'selected=selected':''}}>{{$emp->name}}</option>
                                @endforeach
                                
                            </select>
                            {!! $errors->first('employee', '<p class="help-block">:message</p>') !!}

                        </div>
                   </div><br>
                <div class="row">
                        <div class="col-md-6">
                            <label>Pnch In / Punch Out<sup>*</sup></label>
                            <select name="punch" id="punch" class="input-css select2" style="width:100%">
                                <option value=" ">Select</option>
                                	<option value="PunchIn" {{old('punch')=='PunchIn' ? 'selected=selected' : ''}} >Punch In</option>
                                	<option value="PunchOut" {{old('punch')=='PunchOut' ? 'selected=selected' : ''}}>Punch Out</option>
                            </select>
                            {!! $errors->first('punch', '<p class="help-block">:message</p>') !!}
                        </div>
                        <div class="col-md-6">
                        <label>{{__('bay.start_time')}}</label>
                        <input type="text" name="start_time" id="timepicker" class="input-css timepicker" value="{{old('start_time')}}">
                            {!! $errors->first('start_time', '<p class="help-block">:message</p>') !!}
                     </div>
                        
                </div>
                
                <div class="submit-button">
                <button type="submit" class="btn btn-primary">Submit</button>
                    
                </div>
                 </form>                 
            </div>
        
      </section>
@endsection