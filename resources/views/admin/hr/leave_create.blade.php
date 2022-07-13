@extends($layout)

@section('title', 'Create Leave')

{{-- TODO: fetch from auth --}}
@section('user', Auth::user()->name)

@section('breadcrumb')
    <li><a href="#"><i class=""></i> Leave</a></li>
   
@endsection

​
@section('js')
<script src="/js/pages/leave.js"></script>
<script>
    var leave_type=@php echo json_encode( old('type')) @endphp;
$(document).ready(function() {

    var name = $("#employee_name").children("option:selected").val();
    if(name){
        filter_leave();
    }
    
});
var currentDate = new Date();
$('.datepickers').datepicker({
    format: 'dd-mm-yyyy',
        autoclose: true,
        startDate:currentDate,
});

$(".datepickers").change(function(){

    var st_date = $(".datepickers").val().split('-');
    var date = new Date(st_date[2],(st_date[1]-1),st_date[0]);
    $('.datepickerr').datepicker({
        format: 'dd-mm-yyyy',
        autoclose: true,
        startDate:date,
    });
    $('.datepickerr').removeAttr("disabled");
})

 function filter_leave(){
    var name = $("#employee_name").children("option:selected").val();

    $('#ajax_loader_div').css('display','block');

    if(name){

        $.ajax({
            type:"GET",
            url:"/admin/hr/leave/balance/check/api",
            data:{'name':name},
            success: function(result){
                if (result) {

                      var email= result.email; 
                      $("#email").val(email);
                      var given_cl= parseInt(result.given_cl);
                      var given_pl= parseInt(result.given_pl);
                      var balance_cl= parseInt(result.cl);
                      var balance_pl= parseInt(result.pl);
                        $("#type").empty();
                        $("#type").append('<option value="">-Select Type-</option>');
                            
                        if((balance_cl!=0)){
                            $("#type").append('<option value="cl">CL</option>');
                        }
                        if((balance_pl!=0)){
                            $("#type").append('<option value="pl">PL</option>');
                        }
                        if( balance_cl<=0  && balance_pl<=0 ){

                            $("#type").append('<option value="lwp">LWP</option>');
                        }
                        if(leave_type!=null){
                           $('#type option[value="'+leave_type+'"]').attr('selected', 'selected').change();
                        }
                        $('#ajax_loader_div').css('display','none');
                 }
            }
        });
    }
 }
</script>
@endsection
@section('main_section')
    <section class="content">
        <!-- Default box -->
        <div id="app">
                @include('admin.flash-message')
                @yield('content')
            </div>
       <form action="/admin/hr/leave/create" method="POST" id="form">
        @csrf
        <div class="box-header with-border">
            <div class='box box-default'> <br>
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-6 {{ $errors->has('employee_name') ? 'has-error' : ''}}">
                                <label>Name <sup>*</sup></label><br>
                                <select name="employee_name" id="employee_name" class="employee_name select2 input-css" onchange="filter_leave();">
                                    <option value="">Select Name</option>
                                    @foreach ($emp as $item)
                                        <option value="{{$item->id}}" {{old('employee_name')==$item->id ? 'selected=selected' :''}}>{{$item->name}} {{$item->middle_name}} {{$item->last_name}}</option>
                                    @endforeach
                                </select>
                                {!! $errors->first('employee_name', '<p class="help-block">:message</p>') !!}
                        </div>
                        <div class="col-md-6 {{ $errors->has('type') ? 'has-error' : ''}}">
                                <label>Type <sup>*</sup></label><br>
                                <select name="type" id="type" class="type select2 input-css">
                                    <option value="">Select Type</option>
                                </select>
                                {!! $errors->first('type', '<p class="help-block">:message</p>') !!}
                        </div>
                    </div><br><br>
                    <div class="row">
                            <div class="col-md-6 {{ $errors->has('email') ? 'has-error' : ''}}">
                                    <label>Email Address</label><br>
                                    <input type="text" name="email" id="email" class="email input-css" value="{{old('email')}}">
                                    {!! $errors->first('email', '<p class="help-block">:message</p>') !!}
                            </div>
                            <div class="col-md-6 {{ $errors->has('contact') ? 'has-error' : ''}}">
                                    <label>Contact number when on leave <sup>*</sup></label><br>
                            <input type="number" name="contact" id="contact" class="input-css contact" value="{{old('contact')}}">
                                    {!! $errors->first('contact', '<p class="help-block">:message</p>') !!}
                            </div>
                    </div><br><br>
                    <div class="row">
                            <div class="col-md-6 {{ $errors->has('start_date') ? 'has-error' : ''}}">
                                    <label>Start Date <sup>*</sup></label><br>
                                    <input type="text" name="start_date" autocomplete="off" id="start_date" value="{{old('start_date')}}" class="start_date input-css datepickers">
                                    {!! $errors->first('start_date', '<p class="help-block">:message</p>') !!}
                            </div>
                        
                            <div class="col-md-6 {{ $errors->has('end_date') ? 'has-error' : ''}}">
                                    <label>End Date <sup>*</sup></label><br>
                                    <input type="text"  value="{{old('end_date')}}" autocomplete="off" name="end_date" id="end_date" class="end_date input-css datepickerr" >
                                    {!! $errors->first('end_date', '<p class="help-block">:message</p>') !!}
                            </div>
                    </div><br><br>
                    <div class="row">
                        <div class="col-md-12 { $errors->has('reason') ? 'has-error' : ''}}" >
                                <label>Reason For Leave <sup>*</sup></label><br>
                                <textarea name="reason" id="reason" class="input-css reason" cols="30" rows="5">{{old('reason')}}</textarea>
                        </div>
                    </div>
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
