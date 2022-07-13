@extends($layout)

@section('title', 'Create NOP')
@section('user', Auth::user()->name)

@section('breadcrumb')
    <li><a href="#"><i class=""></i> NOP</a></li>
   
@endsection

​
@section('js')
<script src="/js/pages/leave.js"></script>
<script>
 var currentDate = new Date();
    $('#date').datepicker({
        format: "dd/mm/yyyy",
         autoclose: true,
         endDate: "currentDate",
    });
 $('.timepicker').wickedpicker({
      twentyFour:true,
      //show: 19 : 31,
      //now:null
  }); 
</script> 
@endsection
@section('main_section')
    <section class="content">
        <!-- Default box -->
        <div id="app">
                @include('admin.flash-message')
                @yield('content')
            </div>
       <form action="/admin/hr/user/nop" method="POST" id="form">
        @csrf
        <div class="box-header with-border">
            <div class='box box-default'> <br>
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-6 {{ $errors->has('employee_name') ? 'has-error' : ''}}">
                                <label>Name <sup>*</sup></label><br>
                                <select name="employee_name" id="employee_name" class="employee_name select2 input-css">
                                    <option value="">Select Name</option>
                                    @foreach ($emp as $item)
                                        <option value="{{$item->id}}" {{old('employee_name')==$item->id ? 'selected=selected' :''}}>{{$item->name}} {{ $item->middle_name}} {{$item->last_name}}</option>
                                    @endforeach
                                </select>
                                {!! $errors->first('employee_name', '<p class="help-block">:message</p>') !!}
                        </div>
                         <div class="col-md-6">
                            <label>Date <sup>*</sup></label>
                            <input type="text" name="date" id="date" autocomplete="off" class="input-css" value="{{old('date')}}">
                        {!! $errors->first('date', '<p class="help-block">:message</p>') !!}

                        </div>
                    </div><br><br>
                    <div class="row">
                            <div class="col-md-6 {{ $errors->has('type') ? 'has-error' : ''}}">
                            <label>Type<sup>*</sup></label>
                            <input type="radio" class="type" name="type" value="PunchIn" 
                            {{(old('type') == 'PunchIn')? 'checked': ''}}> PunchIn 
                            <input type="radio" class="type" name="type" value="PunchOut"style="margin-left: 15px;" {{(old('type') == 'PunchOut')? 'checked': ''}}> PunchOut
                            {!! $errors->first('type', '<p class="help-block">:message</p>') !!}
                        </div>
							<div class="col-md-6 {{ $errors->has('contact') ? 'has-error' : ''}}">
                             <label for="">Time<sup>*</sup>   </label>
                             <input type="text" name="time" id="timepicker" class="input-css timepicker" value="{{old('time')}}">
                            {!! $errors->first('time', '<p class="help-block">:message</p>') !!}
                            </div>
                    </div><br><br>
                </div>  <br>  <br>
            </div>
        </div>
        <div class="row">
                <div class="col-md-12">
                     <input type="submit" style="float:right" class="btn btn-primary" value="Submit">
                </div>
            </div>
        </form>
      
      </section>
@endsection
