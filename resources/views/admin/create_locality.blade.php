@extends($layout)

@section('title', __('locality.title_create'))

@section('breadcrumb')
    <li><a href="#"><i class=""></i> {{__('locality.title_create')}} </a></li>
    
@endsection
<style>
  .list-group-item{
    background: aliceblue;
    padding: 5px!important;
    list-style: none;
    border: none!important;
  }
</style>
@section('js')
<script>
   
$(document).ready(function(){


$('#state').val(23).trigger("change");
state(state_id=23);
$('#state').change(function() {
        var state_id = $(this).val();
        state(state_id);
        
    });

 function state(state_id)
 {
    var sid=state_id;
    if (sid) {
            $.ajax({
                type: "get",
                url: "/admin/customer/city/" + sid, //Please see the note at the end of the post
                success: function(res) {
                    if (res) {
                        $("#city").empty();
                        $("#city").append('<option>Select City</option>');
                        $.each(res, function(key, value) {
                            $("#city").append('<option value="' + key + '">' + value + '</option>');
                        });
                        $('#city').val(561).trigger("change");
                    }
                }

            });
        }
 }

$('#locality').on('change',function() {
        var text = $('#locality').val();
        var city_id = $("#city").val();
        $("#btnSubmit").attr("disabled", true);
         $("#result").html('');
            $.ajax({
                type: "GET",
                url: '/admin/city/locality/search',
                data: { text: text, city_id:city_id },
                success: function(response) {
                     console.log(response);
                      $("#btnSubmit").attr("disabled", false);
                     if (response != '') {
                         $("#result").append('<li class="list-group-item" style="color:grey;"> Added similar locality :</li>');
                      }
                      $.each(response,function(key, value){
                         $("#result").append('<li class="list-group-item">'+value.name+'</li>');
                         //var cities = value.city.toLowerCase();
                           //if (cities = text.toLowerCase()) {
                            // $("#msg").append('This city is already added !');
                            //}
                      });  
                },
                error: function() {
                    $("#msg").html('');
                    $("#msg").append('Internal Issue Try Again');
                }
            });
      
    });
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
            <form action="/admin/city/location" method="POST">
        @csrf

       <div class="box box-header">
           <br>
            <div class="row" >
                <div class="col-md-6 {{ $errors->has('country') ? 'has-error' : ''}}">
                    <label>Country<sup>*</sup></label>
                    <select name="country" id="country" class="select2 design input-css">
                        <option value="{{$country->id}}" selected="">{{$country->name}}</option>
                    </select>
                    {!! $errors->first('country', '<p class="help-block">:message</p>') !!}
                </div>
                <div class="col-md-6 {{ $errors->has('state') ? 'has-error' : ''}}">
                    <label>State<sup>*</sup></label>
                    <select name="state" id="state" class="select2 design input-css">
                        <option value="">Select State</option>
                        @foreach ($state as $item)
                        <option value="{{$item->id}}">{{$item->name}}</option>
                        @endforeach
                    </select>
                    {!! $errors->first('state', '<p class="help-block">:message</p>') !!}
                </div>
                
         </div><br><br>
         <div class="row">
            <div class="col-md-6">
                <label>{{__('customer.city')}}<sup>*</sup></label>
                <select name="city" id="city" class="input-css select2" style="width:100%">
                    <option value="" disabled>Select City</option>

                </select>
                {!! $errors->first('city', '<p class="help-block">:message</p>') !!}
             </div>

             <div class="col-md-6 {{ $errors->has('locality') ? ' has-error' : ''}}">
                <label for="">Locality <sup>*</sup></label>
                <span id="msg" style="color:red"></span>
                <input type="text" name="locality" id="locality" class="dept input-css">
                <ul class="list-group" id="result"></ul>
                {!! $errors->first('locality', '<p class="help-block">:message</p>') !!} 
            </div>
         </div>
        
       </div>
        <div class="row">
                <div class="col-md-12">
                     <input type="submit" id="btnSubmit" class="btn btn-primary" value="Submit">
                </div><br><br><br><br>
            </div>
        </form>
            </div>
      </section>
@endsection