@extends($layout)

@section('title', __('Create Task'))

@section('breadcrumb')
    <li><a href="#"><i class=""></i> {{__('Create Task')}} </a></li>
    
@endsection
@section('css')
@endsection
<style>
  p.help-block{
    color: red !important;
  }
</style>
@section('js')
<script>
    var today = new Date();
$('.datepicker3').datepicker({
   format: "dd/mm/yyyy",
   autoclose: true,
   startDate: today 
});
$('.timepicker').wickedpicker({
            twentyFour:true,
            show:null
        });

(function($) {
  $.fn.inputFilter = function(inputFilter) {
    return this.on("input keydown keyup mousedown mouseup select contextmenu drop", function() {
      if (inputFilter(this.value)) {
        this.oldValue = this.value;
        this.oldSelectionStart = this.selectionStart;
        this.oldSelectionEnd = this.selectionEnd;
      } else if (this.hasOwnProperty("oldValue")) {
        this.value = this.oldValue;
        this.setSelectionRange(this.oldSelectionStart, this.oldSelectionEnd);
      } else {
        this.value = "";
      }
    });
  };

}(jQuery));
// Install input filters.

$("#time").inputFilter(function(value) {
  return /^-?\d*[.]?\d*$/.test(value); 
});

 $('#recurrence').on( 'change', function () {
      
      var data= $('#recurrence').val();

    });

  $(document).on('change', '.recurrence', function() {
    var val = $(this).val();
    if (val == 'Daily') {
        $('#recurrence_daily').show();
        $('#recurrence_weekly').hide();
        $('#recurrence_monthly').hide();
        $('#recurrence_yearly').hide();

    } else if(val == 'Weekly') {
        $("#recurrence_daily").hide();
        $('#recurrence_weekly').show();
        $('#recurrence_monthly').hide();
        $('#recurrence_yearly').hide();

    }else if(val == 'Monthly') {
        $("#recurrence_daily").hide();
        $('#recurrence_weekly').hide();
        $('#recurrence_yearly').hide();
        $('#recurrence_monthly').show();

    }else if(val == 'Yearly') {
        $("#recurrence_daily").hide();
        $('#recurrence_weekly').hide();
        $('#recurrence_monthly').hide();
        $('#recurrence_yearly').show();

    }
});

</script>
@endsection

@section('main_section')
    <section class="content">
            @include('admin.flash-message')
                @yield('content')
            <!-- general form elements -->
            <div class="box-header with-border">
            <div class="box box-primary">
                <div class="box-header">
                </div>  
                <form id="unload-data-validation" action="/admin/task/create" method="post">
                    @csrf
                    <div class="row">
                       <div class="col-md-12">
                        <div class="col-md-5">
                             <label>Task<sup>*</sup></label>
                            <textarea class="input-css" name="task" rows="3">{{old('task')}}</textarea>
                            {!! $errors->first('task', '<p class="help-block">:message</p>') !!}
                        </div>
                        <div class="col-md-7">
                          <div class="row">
                           <div class="col-md-12">
                             <label>Recurrence<sup>*</sup></label>
                               <input type="radio" class="recurrence" name="recurrence"  {{(old('recurrence') == 'Daily')? 'checked': ''}} value="Daily">Daily 
                                <input type="radio" class="recurrence" name="recurrence" {{(old('recurrence') == 'Weekly')? 'checked': ''}} value="Weekly" style="margin-left: 15px;" > Weekly
                                <input type="radio" class="recurrence" name="recurrence" {{(old('recurrence') == 'Monthly')? 'checked': ''}} value="Monthly" style="margin-left: 15px;" > Monthly
                                <input type="radio" class="recurrence" name="recurrence" {{(old('recurrence') == 'Yearly')? 'checked': ''}} value="Yearly" style="margin-left: 15px;" > Yearly
                            {!! $errors->first('recurrence', '<p class="help-block">:message</p>') !!}
                         </div>
                        </div>
                        <div class="row" id="recurrence_daily" style="{{(old('recurrence'))?((old('recurrence') == 'Daily') ? 'display:block' : 'display:none'  ) : 'display:none' }}">
                      <div class="col-md-12">
                        <div class="col-md-3">
                           <input type="radio" class="daily-every" name="daily-every"  {{(old('daily-every') == 'EveryDays')? 'checked': ''}} value="EveryDays">Every 
                         </div>
                         <div class="col-md-2">
                             <input id="daily-every-days" type="number" name="daily-every-days" class="input-css "  min="1" max="31" value="{{(old('daily-every-days')) ? old('daily-every-days') : ''  }}">
                         {!! $errors->first('daily-every-days', '<p class="help-block">:message</p>') !!}
                         </div>
                         <div class="col-md-7">
                          <label>day(s) </label>
                         </div>
                      </div>
                       <div class="col-md-12">
                         <div class="col-md-12">
                           <input type="radio" class="daily-every" name="daily-every"  {{(old('daily-every') == 'EveryWeekDay')? 'checked': ''}} value="EveryWeekDay">On Working Day 
                         </div>
                       </div>
                       <div class="col-md-12">
                         <div class="col-md-5">
                           <input type="radio" class="daily-every" name="daily-every"  {{(old('daily-every') == 'Regenerate')? 'checked': ''}} value="Regenerate">Regenerate new task
                         </div>
                         <div class="col-md-2">
                             <input id="daily-regenerate-every-days" type="number" name="daily-regenerate-every-days" class="input-css "  min="1" max="31" value="{{(old('daily-regenerate-every-days')) ? old('daily-regenerate-every-days') : ''  }}">
                          {!! $errors->first('daily-regenerate-every-days', '<p class="help-block">:message</p>') !!}
                         </div>
                         <div class="col-md-5">
                          <label>day(s) after each task is completed</label>
                         </div>
                       </div>
                       {!! $errors->first('daily-every', '<p class="help-block">:message</p>') !!}
                       
                     </div>
                     <div class="row" id="recurrence_weekly" style="{{(old('recurrence'))?((old('recurrence') == 'Weekly') ? 'display:block' : 'display:none'  ) : 'display:none' }}">
                      <div class="col-md-12">
                        <div class="col-md-3">
                           <input type="radio" class="weekly-every" name="weekly-every"  {{(old('weekly-every') == 'EveryWeekOn')? 'checked': ''}} value="EveryWeekOn">Every 
                         </div>
                         <div class="col-md-2">
                             <input id="weekly-every-days" type="number" name="weekly-every-days" class="input-css "  min="1" max="5" value="{{(old('weekly-every-days')) ? old('weekly-every-days') : ''  }}">
                          {!! $errors->first('weekly-every-days', '<p class="help-block">:message</p>') !!}
                         </div>
                         <div class="col-md-7">
                          <label>week(s) on </label>
                         </div>
                      </div>
                       <div class="col-md-12">
                         <div class="col-md-4">
                          
                           <input type="checkbox" @if(is_array(old('weekday')) && in_array('Monday', old('weekday'))) checked @endif name="weekday[]" value="Monday">Monday 
                         </div>
                         <div class="col-md-4">
                           <input type="checkbox" @if(is_array(old('weekday')) && in_array('Tuesday', old('weekday'))) checked @endif name="weekday[]" value="Tuesday">Tuesday 
                         </div>
                         <div class="col-md-4">
                           <input type="checkbox" @if(is_array(old('weekday')) && in_array('Wednesday', old('weekday'))) checked @endif name="weekday[]" value="Wednesday">Wednesday 
                         </div>
                       </div>
                       <div class="col-md-12">
                         <div class="col-md-4">
                           <input type="checkbox" @if(is_array(old('weekday')) && in_array('Thursday', old('weekday'))) checked @endif name="weekday[]" value="Thursday">Thursday 
                         </div>
                         <div class="col-md-4">
                           <input type="checkbox" @if(is_array(old('weekday')) && in_array('Friday', old('weekday'))) checked @endif name="weekday[]" value="Friday">Friday 
                         </div>
                         <div class="col-md-4">
                           <input type="checkbox" @if(is_array(old('weekday')) && in_array('Saturday', old('weekday'))) checked @endif name="weekday[]" value="Saturday">Saturday 
                         </div>
                       </div>
                       {!! $errors->first('weekday', '<p class="help-block">:message</p>') !!}
                      <div class="col-md-12">
                         <div class="col-md-5">
                           <input type="radio" class="weekly-every" name="weekly-every"  {{(old('weekly-every') == 'Regenerate')? 'checked': ''}} value="Regenerate">Regenerate new task
                         </div>
                         <div class="col-md-2">
                            <input id="weekly-regenerate-every-days" type="number" name="weekly-regenerate-every-days" class="input-css "  min="1" max="5" value="{{(old('weekly-regenerate-every-days')) ? old('weekly-regenerate-every-days') : ''  }}">
                            {!! $errors->first('weekly-regenerate-every-days', '<p class="help-block">:message</p>') !!}
                         </div>
                         <div class="col-md-5">
                          <label>week(s) after each task is completed</label>
                         </div>
                       </div>
                        {!! $errors->first('weekly-every', '<p class="help-block">:message</p>') !!}
                     </div>
                     <div class="row" id="recurrence_monthly" style="{{(old('recurrence'))?((old('recurrence') == 'Monthly') ? 'display:block' : 'display:none'  ) : 'display:none' }}">
                        <div class="col-md-12">
                          <div class="col-md-2">
                             <input type="radio" class="monthly-every" name="monthly-every"  {{(old('monthly-every') == 'Day')? 'checked': ''}} value="Day">Day
                           </div>
                           <div class="col-md-2">
                               <input id="monthly-every-days" type="number" name="monthly-every-days" class="input-css "  min="1" max="31" value="{{(old('monthly-every-days')) ? old('monthly-every-days') : ''  }}">
                               {!! $errors->first('monthly-every-days', '<p class="help-block">:message</p>') !!}
                           </div>
                           <div class="col-md-3">
                            <label>on every</label>
                           </div>
                           <div class="col-md-2">
                             <input id="monthly-every-month" type="number" name="monthly-every-month" class="input-css "  min="1" max="12" value="{{(old('monthly-every-month')) ? old('monthly-every-month') : ''  }}">
                             {!! $errors->first('monthly-every-month', '<p class="help-block">:message</p>') !!}
                           </div>
                           <div class="col-md-2">
                            <label>month(s)</label>
                           </div>
                        </div>
                        <div class="col-md-12">
                          <div class="col-md-2">
                             <input type="radio" class="monthly-every" name="monthly-every"  {{(old('monthly-every') == 'SelectedWeek')? 'checked': ''}} value="SelectedWeek">The
                           </div>
                           <div class="col-md-3">
                            <select name="monthly-week" id="monthly-week" class="input-css select2">
                            <option value="">Select Week</option>
                            <option value="First" {{(old('monthly-week') == 'First')? 'selected': ''}} >First</option>
                            <option value="Second" {{(old('monthly-week') == 'Second')? 'selected': ''}}>Second</option>
                            <option value="Third" {{(old('monthly-week') == 'Third')? 'selected': ''}}>Third</option>
                            <option value="Fourth" {{(old('monthly-week') == 'Fourth')? 'selected': 'Fourth'}}>Fourth</option>
                            <option value="Fifth" {{(old('monthly-week') == 'Fifth')? 'selected': ''}}>Fifth</option>
                        </select>
                        {!! $errors->first('monthly-week', '<p class="help-block">:message</p>') !!}
                           </div>
                           <div class="col-md-3">
                              <select name="monthly-days" id="monthly-days" class="input-css select2">
                                <option value="">Select Days</option>
                                <option value="Monday" {{(old('monthly-days') == 'Monday')? 'selected': ''}}>Monday</option>
                                <option value="Tuesday" {{(old('monthly-days') == 'Tuesday')? 'selected': ''}}>Tuesday</option>
                                <option value="Wednesday" {{(old('monthly-days') == 'Wednesday')? 'selected': ''}}>Wednesday</option>
                                <option value="Thursday" {{(old('monthly-days') == 'Thursday')? 'selected': ''}}>Thursday</option>
                                <option value="Friday" {{(old('monthly-days') == 'Friday')? 'selected': ''}}>Friday</option>
                                <option value="Saturday" {{(old('monthly-days') == 'Saturday')? 'selected': ''}}>Saturday</option>
                            </select>
                             {!! $errors->first('monthly-days', '<p class="help-block">:message</p>') !!}
                        </div>
                          <div class="col-md-1">
                            <label>on every</label>
                           </div>
                           <div class="col-md-2">
                             <input id="monthly-every-month-days" type="number" name="monthly-every-month-days" class="input-css "  min="1" max="12" value="{{(old('monthly-every-month-days')) ? old('monthly-every-month-days') : ''  }}">
                            {!! $errors->first('monthly-every-month-days', '<p class="help-block">:message</p>') !!}
                           </div>
                           <div class="col-md-1">
                            <label>month(s)</label>
                           </div>
                      </div>
                       <div class="col-md-12">
                         <div class="col-md-5">
                           <input type="radio" class="monthly-every" name="monthly-every"  {{(old('monthly-every') == 'Regenerate')? 'checked': ''}} value="Regenerate">Regenerate new task
                         </div>
                         <div class="col-md-2">
                             <input id="monthly-regenerate-every-days" type="number" name="monthly-regenerate-every-days" class="input-css "  min="1" max="12" value="{{(old('monthly-regenerate-every-days')) ? old('monthly-regenerate-every-days') : ''  }}">
                            {!! $errors->first('monthly-regenerate-every-days', '<p class="help-block">:message</p>') !!}
                         </div>
                         <div class="col-md-5">
                          <label>month(s) after each task is completed</label>
                         </div>
                       </div>
                       {!! $errors->first('monthly-every', '<p class="help-block">:message</p>') !!}
                    </div>
                    <div class="row" id="recurrence_yearly" style="{{(old('recurrence'))?((old('recurrence') == 'Yearly') ? 'display:block' : 'display:none'  ) : 'display:none' }}">
                      <div class="col-md-12">
                          <div class="col-md-3">
                             <input type="radio" class="yearly-every" name="yearly-every"  {{(old('yearly-every') == 'Every')? 'checked': ''}} value="Every">Every
                           </div>
                           <div class="col-md-4">
                            <select name="yearly-month-name" id="yearly-month-name" class="input-css select2">
                              <option value="">Select Month</option>
                              <option value="January" {{(old('yearly-month-name') == 'January')? 'selected': ''}} >January</option>
                              <option value="February" {{(old('yearly-month-name') == 'February')? 'selected': ''}}>February</option>
                              <option value="March" {{(old('yearly-month-name') == 'March')? 'selected': ''}}>March</option>
                              <option value="April" {{(old('yearly-month-name') == 'April')? 'selected': ''}}>April</option>
                              <option value="May " {{(old('yearly-month-name') == 'May')? 'selected': ''}}>May </option>
                              <option value="June" {{(old('yearly-month-name') == 'June')? 'selected': ''}}>June</option>
                              <option value="July" {{(old('yearly-month-name') == 'July')? 'selected': ''}}>July</option>
                              <option value="August" {{(old('yearly-month-name') == 'August')? 'selected': ''}}>August</option>
                              <option value="September" {{(old('yearly-month-name') == 'September')? 'selected': ''}}>September</option>
                              <option value="October" {{(old('yearly-month-name') == 'October')? 'selected': ''}}>October</option>
                              <option value="November" {{(old('yearly-month-name') == 'November')? 'selected': ''}}>November</option>
                              <option value="December" {{(old('yearly-month-name') == 'December')? 'selected': ''}}>December</option>
                           </select>
                            {!! $errors->first('yearly-month-name', '<p class="help-block">:message</p>') !!}
                           </div>
                           <div class="col-md-2">
                             <input id="yearly-every-day" type="number" name="yearly-every-day" class="input-css "  min="1" max="31" value="{{(old('yearly-every-day')) ? old('yearly-every-day') : ''  }}">
                            {!! $errors->first('yearly-every-day', '<p class="help-block">:message</p>') !!}
                           </div>
                        </div>
                        <div class="col-md-12" style="margin-top:20px;">
                          <div class="col-md-2">
                             <input type="radio" class="yearly-every" name="yearly-every"  {{(old('yearly-every') == 'SelectedWeek')? 'checked': ''}} value="SelectedWeek">The
                           </div>
                           <div class="col-md-3">
                            <select name="yearly-week" id="yearly-week" class="input-css select2">
                            <option value="">Select Week</option>
                            <option value="First" {{(old('yearly-week') == 'First')? 'selected': ''}} >First</option>
                            <option value="Second" {{(old('yearly-week') == 'Second')? 'selected': ''}}>Second</option>
                            <option value="Third" {{(old('yearly-week') == 'Third')? 'selected': ''}}>Third</option>
                            <option value="Fourth" {{(old('yearly-week') == 'Fourth')? 'selected': 'Fourth'}}>Fourth</option>
                            <option value="Fifth" {{(old('yearly-week') == 'Fifth')? 'selected': ''}}>Fifth</option>
                        </select>
                        {!! $errors->first('yearly-week', '<p class="help-block">:message</p>') !!}
                           </div>
                           <div class="col-md-3">
                              <select name="yearly-days" id="yearly-days" class="input-css select2">
                                <option value="">Select Days</option>
                                <option value="Monday" {{(old('yearly-days') == 'Monday')? 'selected': ''}}>Monday</option>
                                <option value="Tuesday" {{(old('yearly-days') == 'Tuesday')? 'selected': ''}}>Tuesday</option>
                                <option value="Wednesday" {{(old('yearly-days') == 'Wednesday')? 'selected': ''}}>Wednesday</option>
                                <option value="Thursday" {{(old('yearly-days') == 'Thursday')? 'selected': ''}}>Thursday</option>
                                <option value="Friday" {{(old('yearly-days') == 'Friday')? 'selected': ''}}>Friday</option>
                                <option value="Saturday" {{(old('monthly-days') == 'Saturday')? 'selected': ''}}>Saturday</option>
                            </select>
                             {!! $errors->first('yearly-days', '<p class="help-block">:message</p>') !!}
                        </div>
                          <div class="col-md-1">
                            <label>of</label>
                           </div>
                           <div class="col-md-3">
                            <select name="yearly-month-week" id="yearly-month-week" class="input-css select2">
                                <option value="">Select Month</option>
                                <option value="January" {{(old('yearly-month-week') == 'January')? 'selected': ''}} >January</option>
                                <option value="February" {{(old('yearly-month-week') == 'February')? 'selected': ''}}>February</option>
                                <option value="March" {{(old('yearly-month-week') == 'March')? 'selected': ''}}>March</option>
                                <option value="April" {{(old('yearly-month-name') == 'April')? 'selected': ''}}>April</option>
                                <option value="May" {{(old('yearly-month-week') == 'May')? 'selected': ''}}>May </option>
                                <option value="June" {{(old('yearly-month-week') == 'June')? 'selected': ''}}>June</option>
                                <option value="July" {{(old('yearly-month-week') == 'July')? 'selected': ''}}>July</option>
                                <option value="August" {{(old('yearly-month-week') == 'August')? 'selected': ''}}>August</option>
                                <option value="September" {{(old('yearly-month-week') == 'September')? 'selected': ''}}>September</option>
                                <option value="October" {{(old('yearly-month-week') == 'October')? 'selected': ''}}>October</option>
                                <option value="November" {{(old('yearly-month-week') == 'November')? 'selected': ''}}>November</option>
                                <option value="December" {{(old('yearly-month-week') == 'December')? 'selected': ''}}>December</option>
                            </select>
                            {!! $errors->first('yearly-month-week', '<p class="help-block">:message</p>') !!}
                           </div>
                      </div>
                      <div class="col-md-12">
                         <div class="col-md-5">
                           <input type="radio" class="yearly-every" name="yearly-every"  {{(old('yearly-every') == 'Regenerate')? 'checked': ''}} value="Regenerate">Regenerate new task
                         </div>
                         <div class="col-md-2">
                             <input id="yearly-regenerate-every-days" type="number" name="yearly-regenerate-every-days" class="input-css "  min="1" max="20" value="{{(old('yearly-regenerate-every-days')) ? old('yearly-regenerate-every-days') : ''  }}">
                          {!! $errors->first('yearly-regenerate-every-days', '<p class="help-block">:message</p>') !!}
                         </div>
                         <div class="col-md-5">
                          <label>year(s) after each task is completed</label>
                         </div>
                       </div>
                       {!! $errors->first('yearly-every', '<p class="help-block">:message</p>') !!}
                    </div>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-12">
                       <div class="col-md-5 {{ $errors->has('users') ? 'has-error' : ''}}">
                            <label>Assign To <sup>*</sup></label>
                            <select id="users" name="users" class="input-css select2" data-placeholder="Select User" style="width: 100%;">
                                <option value="">Select User</option>
                               @foreach ($users as $key)
                            <option value="{{$key->id}}" {{old('users')==$key->id ? 'selected=selected' : ''}}>{{$key->name}} {{$key->middle_name}} {{$key->last_name}} - {{$key->emp_id}}</option>
                               @endforeach
                               
                            </select>
                            {!! $errors->first('users', '<p class="help-block">:message</p>') !!}
                        </div>
                      </div>
                 </div><br>
                 <div class="row">
                     <div class="col-md-12">
                      <div class="col-md-6">
                            <label>Priority<sup>*</sup></label>
                            @include('layouts.taskpriority_tab')
                            <br>
                            <input type="radio" class="priority" name="priority"  {{(old('priority') == 'Low')? 'checked': ''}} value="Low">Low 
                            <input type="radio" class="priority" name="priority" {{(old('priority') == 'Medium')? 'checked': ''}} value="Medium" style="margin-left: 15px;" > Medium
                            <input type="radio" class="priority" name="priority" {{(old('priority') == 'High')? 'checked': ''}} value="High " style="margin-left: 15px;" > High

                            {!! $errors->first('priority', '<p class="help-block">:message</p>') !!}
                        </div>
                      </div>
                    </div><br>
                  <div class="row">
                    <div class="col-md-12">
                     <div class="col-md-12">
                            <label>Complete By</label>
                       <div class="col-md-3" style="margin-left:-10px;">
                        <label>Date<sup>*</sup></label>
                            <input type="text" autocomplete="off" value="{{old('date')}}" name="date" id="date" class="datepicker3 input-css">
                        {!! $errors->first('date', '<p class="help-block">:message</p>') !!}
                       </div>
                       <div class="col-md-3">
                         <label>Time<sup>*</sup></label>
                       <input type="text" name="time" value="{{old('time')}}" autocomplete="off" maxlength="6" id="time" class="input-css timepicker">
                        {!! $errors->first('time', '<p class="help-block">:message</p>') !!}
                       </div></div>
                     </div>
                    </div><br>
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

