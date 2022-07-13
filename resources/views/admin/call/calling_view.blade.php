@extends($layout)

@section('title', __('Call Log View'))

@section('user', Auth::user()->name)

@section('breadcrumb')

    <li><a href="/admin/call/log/list"><i class=""></i>{{__('Call Log Summery')}}</a></li> 
    <li><a href="#"><i class=""></i>{{__('Call Log View')}}</a></li> 
@endsection
@section('css')

  <link rel="stylesheet" href="/css/responsive.bootstrap.css">
  <link rel="stylesheet" href="/css/callLog.css">
  

@endsection
@section('js')
<meta name="csrf-token" content="{{ csrf_token() }}" />
<script src="/js/pages/firebase.js"></script>

<script src="/js/pages/calling.js"></script>
<script src="/js/dataTables.responsive.js"></script>
<script>

var dnd_option = JSON.parse('{!! $errors !!}');
    // console.log(dnd_option);
    if(dnd_option.hasOwnProperty('dnd_option')){
      $("#dndCenter").modal('show');
    }

var forbidden = @php echo json_encode($forbidden); @endphp;

$(document).ready(function(){
   $('#js-msg-error').hide();
   $('#js-msg-verror').hide();
   $('#js-msg-success').hide();
   $("#deliveryForm").hide();
   $("#bookingForm").hide();
  var type = @php echo json_encode($calling['status']); @endphp;
 // console.log(type);

 var call_id=<?php echo $call_id;?>;
  $("#rcdelivery").on('click',function(){
    $(this).addClass('btn-color-active');
    $("#delivered").removeClass('btn-color-active');
    $('#callForm').removeClass('btn-color-active');
    $("#callingForm").hide();
    
    $("#deliveryForm").show();
     $("#pending-div").hide();
      $.ajax({
        url:'view/rcdelivery/'+call_id,
        type: "GET",
        success: function(data){
          $("#rc_delivery").html(data);
        }
    });
  });

  no_call_present();
  new_call_start();
});
  
  function no_call_present(){
    var no_call_msg = @php echo(isset($_GET["nocall"])?$_GET["nocall"]:0); @endphp;
    if(no_call_msg){
      $('#js-msg-error').html('No Next Call Present.').show();
      setTimeout(function() {
         $('#js-msg-error').hide();
      }, 30000);
    }
  }

  function new_call_start(){
    var new_call_start = @php echo(isset($_GET["newcall"])?$_GET["newcall"]:0); @endphp;
    if(new_call_start){
      $("#stopcall").show();
      $("#startcall").hide();
      start_call();
    }
  }

  $("#remain_open").click(function(){
      if (this.checked) {
        $('#change_btn').val('Remain Open');
        $("#show_open").show();
      } else {
        $('#change_btn').val('Close Call');
        $("#show_open").hide();
      }
  })

  $("#booking").on('click',function(){
    $(this).addClass('btn-color-active');
    $('#callForm').removeClass('btn-color-active');
    $("#callingForm").hide();
    $("#bookingForm").show();
  });
  $("#callForm").on('click',function(){
    $(this).addClass('btn-color-active');
    $('#booking').removeClass('btn-color-active');
    $("#callingForm").show();
    $("#bookingForm").hide();
  });

  // $("#call_form_submit").on('click', function(e) {
    
  //   var call_status = $("#call_status").val();
  //   var next_call_date = $("#next_call_date").val();
  //   var remark = $("#remark").val();

    
  //   if(next_call_date == ''){
  //     $(".next_call_date-err").html("This field is required");
  //   }
  //   if(remark == ''){

  //     $(".remark-err").html("This field is required");

  //   }else if( next_call_date != '' && remark !='' ){
      
  //     $('#remain_open_model').modal('show');
  //     $(".next_call_date-err").html('');
  //     $(".remark-err").html('');
  //   }
    

  // });

  // $('#change_btn').click(function(){debugger

  //   var call_data = {};
  //   call_data.type_id = $("#type_id").val();
  //   call_data.type = $("#type").val();
  //   call_data.call_type = $("#call_type").val();
  //   call_data.id = $("#status_id").val();
  //   call_data.call_summary_id = $("#call_summary_id").val();
  //   call_data.call_status = $("#call_status").val();
  //   call_data.next_call_date = $("#next_call_date").val();
  //   call_data.remark = $("#remark").val();
  //   call_data.remain_open = $("#remain_open").val();
  //   call_data.open_query = $("#open_query").val();
  //   $.ajax({
  //     url:'/admin/call/log/updateCall',
  //     data: call_data,
  //     method:'POST',
  //     success:function(result){
  //       console.log();
  //     },error:function(error){
  //       // console.log(error);
  //       $('#js-msg-error').html(error.responseJSON.message).show();
  //     }

  //   })
  // })

 $(document).on('click','#service_booking',function(e){

  $('#js-msg-verror').hide();
 });
 $(document).on('click','#btnUpdate',function(e){
       var call_id= $("#call_id").val();
       var name= $("#name").val();
       var mobile= $("#mobile").val();
       var phoneno = /^\d{10}$/;

       if(!name){

          $('#js-msg-verror').html("Enter name").show();
       }else if(!mobile){
          $('#js-msg-verror').html("Enter mobile no").show();
       }
       else{
            if(!mobile.match(phoneno)){

                $('#js-msg-verror').html("Invalid mobile no").show();
           }
           else
           {
          $.ajax({
            url:'rcdelivery/service_book',
            data:{'call_id':call_id,'name':name,'mobile':mobile},
            method:'GET',
            success:function(result){
               if(result.trim() == 'success'){
                  $('#js-msg-success').html('Successfully Service Booking.').show();
                  $('#exampleModalCenter').modal("hide"); 
                   $("#name").val('');
                   $("#mobile").val('');
                      $.ajax({
                      url:'view/rcdelivery/'+call_id,
                      type: "GET",
                      success: function(data){
                      $("#rc_delivery").html(data);
                      }
                      });

               }else if(result.trim() != 'error'){
                  $('#js-msg-verror').html(result).show();
               }else{
                  $('#js-msg-error').html("Something Wen't Wrong").show();
                  $('#exampleModalCenter').modal("hide");
               }
               // console.log('result',result);
            },
            error:function(error){
               $('#js-msg-error').html("Something Wen't Wrong "+error).show();
            }
         });
           }
       }
    
    });

</script>  

@endsection

@section('main_section')
    <section class="content">
        <div id="app">
            @include('admin.flash-message')
            @yield('content')
          <div class="row">
           <div class="alert alert-danger" id="js-msg-error" style="display: none">
           </div>
           <div class="alert alert-success" id="js-msg-success" style="display: none">
           </div>
         </div>
      </div>
      
        <div class="box">
          <div class="box-header with-border">
            <h2 class="box-title">{{ucwords(str_replace('_',' ',$calling['type']))}}({{ucwords($calling['call_type'])}}) </h3>
          </div>
          <div class="box-header with-border">
            {{-- 
            <h3 class="box-title">{{__('customer.mytitle')}} </h3>
            --}}
            <div class="col-md-6">

              <ul class="nav nav1 nav-pills">
                <li class="nav-item">
                  <button class="nav-link1 btn-color-active" id="callForm" >Call's Form</button>
                </li>
                @if($calling['type'] != 'service_booking' && $calling['type'] != 'sale_booking' && $calling['type'] != 'service')
                  @if($calling['type'] == 'sale' || $calling['type'] == 'bestdeal')
                  <li class="nav-item">
                   <button class="nav-link1" id="delivered" >Delivered</button>
                  </li> 
                  @endif
                  <li class="nav-item">
                   <button class="nav-link2" id="rcdelivery" >Rc delivery</a></button>
                  </li>
                @endif
                @if($calling['type'] == 'service')
                <li class="nav-item">
                  <button class="nav-link1" id="booking" >Booking</button>
                </li> 
                @endif
              </ul>
            </div>
            <div class="col-md-6">
                <p id="caller_id" hidden="hidden">{{$calling['id']}}</p>
                <a href="/admin/call/log/nextcall?id={{$calling['id']}}&userid={{$calling['assigned_to']}}&call_type={{$calling['call_type']}}" class="btn btn-success" style="float:right">Next Call</a>
              </div>
          </div>

                <!-- /.box-header -->
            <div class="box-body" id="pending-div">
              <br>
              <div class="row">
                <div class="col-md-6 details_scroll" id="style-2">
                  <div class="">
                    <label for="customer_details" class="info-color"> Customer Details</label>
                    <div class="col-md-3">
                      <label for="customer_name">Customer Name</label>
                    </div>
                    <div class="col-md-3">
                      <label name="customer_name" id="cust_name"><i>{{$calling['customer_name']}}</i></label>
                    </div>
                    <div class="col-md-3">
                      <label for="customer_name">Mobile</label>
                    </div>
                    <div class="col-md-3">
                      <label name="customer_name" id="cust_number"><i>{{$calling['mobile']}}</i></label>
                    </div>
                  </div>

                  @if($calling['type'] == 'sale_booking')
                  <div class="">

                    <label for="customer_details" class="info-color"> Booking Details</label>
                    <div class="row">
                      <div class="col-md-3">
                        <label for="booking_number">Booking Number</label>
                      </div>
                      <div class="col-md-3">
                        <label name="booking_number"><i>{{$calling['booking_number']}}</i></label>
                      </div>
                      <div class="col-md-3">
                        <label for="booking_status">Status</label>
                      </div>
                      <div class="col-md-3">
                        <label name="booking_status"><i>{{$calling['status']}}</i></label>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-3">
                        <label for="booking_number">Total Amount</label>
                      </div>
                      <div class="col-md-3">
                        <label name="booking_number"><i>{{$calling['total_amt']}}</i></label>
                      </div>
                      <div class="col-md-3">
                        <label for="booking_status">Recieved Amount</label>
                      </div>
                      <div class="col-md-3">
                        <label name="booking_status"><i>{{$calling['received_amt']}}</i></label>
                      </div>
                    </div>
                  </div>
                  @endif

                  @if($calling['type'] == 'sale' || $calling['type'] == 'bestdeal')
                  <div class="">
                    <label for="customer_details" class="info-color"> Sale Details</label>
                    <div class="col-md-3">
                      <label for="sale_number">Sale Number</label>
                    </div>
                    <div class="col-md-3">
                      <label name="sale_number"><i>{{$calling['sale_no']}}</i></label>
                    </div>
                    <div class="col-md-3">
                      <label for="sale_status">Status</label>
                    </div>
                    <div class="col-md-3">
                      <label name="sale_status"><i>{{$calling['status']}}</i></label>
                    </div>
                  </div>
                  <div class="">
                    <label for="numberPlateStatus" class="info-color">Number Plate Status</label>
                    <div class="row">
                      <div class="col-md-3">
                        <label for="customer_name">Registration Number</label>
                      </div>
                      <div class="col-md-3">
                        <label name="customer_name"><i>{{(($calling['registration_number'])? $calling['registration_number'] : '---------' )}}</i></label>
                      </div>
                      <div class="col-md-3">
                        <label for="customer_name">Received Status</label>
                      </div>
                      <div class="col-md-3">
                        <label name="customer_name"><i>{{(($calling['registration_number'])? 'Received' : 'Not Received')}}</i></label>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-3">
                        <label for="customer_name">Delivery Status</label>
                      </div>
                      <div class="col-md-3">
                        <label name="customer_name"><i>{{(($calling['numberPlateStatus'] == 0)? 'Not Delivered' : 'Delivered')}}</i></label>
                      </div>
                    </div>
                  </div>
                  <div class="">
                    <label for="numberPlateStatus" class="info-color">RC Status</label>
                    <div class="row">
                      <div class="col-md-3">
                        <label for="customer_name">RC Number</label>
                      </div>
                      <div class="col-md-3">
                        <label name="customer_name"><i>{{(($calling['rc_number'])? $calling['rc_number'] : '------------')}}</i></label>
                      </div>
                      <div class="col-md-3">
                        <label for="customer_name">Received Status</label>
                      </div>
                      <div class="col-md-3">
                        <label name="customer_name"><i>{{(($calling['rc_number'])? 'Received' : 'Not Received')}}</i></label>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-3">
                        <label for="customer_name">Delivery Status</label>
                      </div>
                      <div class="col-md-3">
                        <label name="customer_name"><i>{{(($calling['rcStatus'] == 0)? 'Not Delivered' : 'Delivered')}}</i></label>
                      </div>
                    </div>
                  </div>
                  @endif
                </div>
                <div class="col-md-6 margin-bottom" style="height:280px;overflow-y: scroll;">
                  <ul class="timeline">
                    @foreach($timeline as $key => $val)
                        <li class="time-label">
                           <span class="bg-red">
                              {{$val['call_date']}}
                           </span>
                        </li>
                        <li>
                           <i class="fa fa-list-alt bg-blue"></i>
                           <div class="timeline-item">
                              {{-- <span class="time"><i class="fa fa-clock-o"></i> 12:05</span> --}}
                  
                              <h3 class="timeline-header"><a href="#">Short Summary({{ucwords(str_replace('_',' ',$val['cd_type']))}})</a></h3>
                  
                              <div class="timeline-body">
                                 <div class="row">
                                    <label class="col-md-3 timeline-label" >Call Type </label>
                                    <label class="col-md-3 timeline-value"><i>{{ucwords($val['call_type'])}}</i> </label>

                                    <label class="col-md-3 timeline-label">Call Date </label>
                                    <label class="col-md-3 timeline-value"><i> {{$val['call_date']}}</i> </label>
                                 </div>
                                 <div class="row">
                                    <label class="col-md-3 timeline-label">Call status </label>
                                    <label class="col-md-3 timeline-value"><i> {{$val['call_status']}}</i> </label>
                                
                                    <label class="col-md-3 timeline-label">Call Remark </label>
                                    <label class="col-md-3 timeline-value"> <i>{{$val['remark']}}</i> </label>
                                 </div>
                                 <div class="row">
                                  <label class="col-md-3 timeline-label">Next Call Date</label>
                                  <label class="col-md-3 timeline-value"><i> {{$val['summary_next_date']}} </i></label>
                               
                                  <label class="col-md-3 timeline-label">Updated By</label>
                                  <label class="col-md-3 timeline-value"><i> {{$val['updated_by']}}</i></label>
                                </div>
                              </div>
                  
                           </div>
                        </li>
                  @endforeach 
                     
                  </ul>
                </div>
              </div>

             {{$calling['amount']}}
              @if($calling['call_type'] == 'Insurance')
              <hr>
              <div class="row">
                  <div class="col-md-4">
                    <button id="dnd" data-toggle="modal" data-target="#dndCenter"class="btn btn-success">DND</button>
                  </div>
              </div>
              @endif
              <hr>
              @php
              $call_allow = 1;
              if(isset($dnd_setting->id)){
                if($calling['dnd'] == 'Call')
                {
                  if($calling['dnd_day'] <= $dnd_setting->value){
                    $call_allow = 0;
                  } 
                }
              }
              @endphp
              @if($calling['call_status'] != 'done' && $call_allow == 1)
              
              <form action="/admin/call/log/updateCall" method="POST" id="callingForm">
                <div class="row" id="start_call-div">
                  <div class="col-md-4">
                    <a id="startcall" style="float:left;" class="btn btn-danger">Start Call</a>
                    <a id="stopcall" style="float:left;display: none;" class="btn btn-success" >Stop Call</a>
                  </div>
                  <div class="col-md-4">
                    <label id="calling_status" style="display: none" > </label>
                  </div>
                </div>
                @csrf
                <input type="hidden" name="type_id" id="type_id" value="{{$calling['type_id']}}">
                <input type="hidden" name="type" id="type" value="{{$calling['type']}}">
                <input type="hidden" name="call_type" id="call_type" value="{{$calling['call_type']}}">
                <input type="hidden" name="id" id="status_id"  value="{{$calling['id']}}">
                <div class="modal-body">
                  @if($calling['call_type'] == 'thankyou' || $calling['call_type'] == 'psf')
                    <div class="row">
                      <div class="col-md-6">
                        <div class="col-md-12 checkbox-inline">
                          <label for="remain_open"><input type="checkbox" id="remain_open" {{old('remain_open')?'checked':''}}  name="remain_open" value="1">Remain Open</label>
                        </div>
                        <div class="col-md-12" id="show_open" style="display: none">
                          <label for="open_query">Query:</label>
                          <textArea id="open_query" type="text" name="open_query" class="form-control">{{old('open_query')}}</textArea> 
                          
                          {!! $errors->first('open_query', '<p class="help-block">:message</p>') !!}
                        </div>
                      </div>
                    </div>
                  @endif


                    <div class="row">
                      <input id="call_summary_id" type="hidden" name="call_summary_id" class="input-css">
                      
                      <div class="col-md-6">
                          <label>Call Status<sup>*</sup></label>
                          
                          <select id="call_status"  name="call_status" class="form-control select2">
                              @foreach($call_status as $key => $val)
                                <option value="{{$val['key_name']}}" {{(old('call_status') == $val['key_name']) ? 'selected' : ''}}> {{$val['value']}} </option>
                              @endforeach
                          </select>
                          {!! $errors->first('call_status', '<p class="help-block">:message</p>') !!}
                          <br>
                      </div>
                     
                      <div class="col-md-6">
                        <label>Next Call Date<sup>*</sup></label>
                        <input id="next_call_date" type="text" name="next_call_date" class="input-css datepicker_with_disabled" autocomplete="off">
                        <span class="next_call_date-err" style="color: red"></span>
                        {!! $errors->first('next_call_date', '<p class="help-block">:message</p>') !!}
                        <br>
                    </div>
                      <div class="col-md-12">
                          <label>Remark <sup>*</sup></label>
                          <textArea id="remark" type="text" name="remark" class="form-control"></textArea>
                          <span class="remark-err" style="color: red"></span>
                          {!! $errors->first('remark', '<p class="help-block">:message</p>') !!}
                      </div>
                    </div>

                </div>
                <div class="col-md-12" style="padding-left:30px;">
                    <button type="submit" class="btn btn-success" id="call_form_submit" style="margin-bottom: 10px;" >Submit</button>
                  </div>
              </form>
              @endif
              @if($calling['type'] == 'service')
                @if(!isset($job_card['id']))
                <form action="/admin/service/call/log/booking" method="POST" id="bookingForm">
                  @csrf
                  <input type="hidden" name="type_id" id="type_id" value="{{$calling['type_id']}}">
                  <input type="hidden" name="type" id="type" value="{{$calling['type']}}">
                  <input type="hidden" name="call_type" id="call_type" value="{{$calling['call_type']}}">
                  <input type="hidden" name="id" id="id" value="{{$calling['id']}}">
                  <div class="modal-body">
                      <div class="row">
                        <div class="col-md-6">
                          <label >Service Type <sup>*</sup></label>
                          <select name="service_type" id="service_type" class="input-css select2">
                            <option value="">Select service Type</option>
                            @foreach($jobcard_type as $k => $v)
                              <option value="{{$v->key}}">{{$v->value}}</option>
                            @endforeach
                          </select>
                          {!! $errors->first('remark', '<p class="help-block">:message</p>') !!}
                        </div>
                        <div class="col-md-6">
                          <label >Service Date <sup>*</sup></label>
                          <input type="text" name="service_date" id="service_date" class="input-css datepicker3">
                          {!! $errors->first('service_date', '<p class="help-block">:message</p>') !!}
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-md-6">
                          <label >Service Time <sup>*</sup></label>
                          <input type="text" name="service_time" id="service_time" class="input-css timepicker">
                          {!! $errors->first('service_time', '<p class="help-block">:message</p>') !!}
                        </div>
                        <div class="col-md-6">
                          <label >Pick and Drop <sup>*</sup></label>
                          <div class="col-md-6">
                            <label><input type="radio" name="pick_drop" value="yes" class="pick_drop">  Yes</label>
                          </div>
                          <div class="col-md-6">
                            <label><input type="radio" checked name="pick_drop" value="no" class="pick_drop"> No</label>
                            
                          </div>
                          {!! $errors->first('pick_drop', '<p class="help-block">:message</p>') !!}
                        </div>
                      </div>
                      <div class="row" >
                        <div class="col-md-6">
                            <div class="" style="{{(count($store) == 1)? 'display:none' : 'display:block'}}">
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
                                     <option value="{{$store[0]['id']}} selected">{{$store[0]['name']}}</option>
                                  </select>
                               @endif
                               {!! $errors->first('store_name', '
                               <p class="help-block">:message</p>
                               ') !!}
                            </div>
                           
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
                      </div>
                      <div class="col-md-12" style="padding-left:30px;">
                        <button type="submit" class="btn btn-success" >Book</button>
                      </div>
                  </div>
                </form>
                @else
                  <label id="bookingForm" style="margin-left:45%"> Already Booked !</label>
                @endif
              @endif
              @if($calling['type'] == 'sale')
              <form action="/admin/call/log/updateDelivery" method="POST" id="deliveryForm">
                @csrf
                <input type="hidden" name="delivery_sale_id" id="delivery_sale_id" value="{{$calling['type_id']}}">
                <input type="hidden" name="id" value="{{$calling['id']}}">
                <input type="hidden" name="delivery_rto_id" id="delivery_rto_id" value="{{$calling['rto_id']}}">
                <div class="row">
                  <div class="col-md-12">
                    <label for="numberPlateStatus" class="info-color">Number Plate Status</label>
                    <div class="col-md-4">
                      <label for="customer_name">Delivery</label>
                    </div>
                     <div class="col-md-4">
                      <label for=""></label>
                      <div class="col-md-2">
                        <input type="radio" {{(($calling['numberPlateStatus'] == 0)? '' : 'checked')}} name="numberPlateDelivery" value="yes"> Yes
                      </div>
                      <div class="col-md-2">
                        <input type="radio"   {{(($calling['numberPlateStatus'] == 0)? 'checked' : '')}}  name="numberPlateDelivery" value="no" > No
                      </div>
                    </div>
                  </div>
                  <div class="col-md-12">
                    <label for="numberPlateStatus" class="info-color">RC Status</label>
                    <div class="col-md-4">
                      <label for="customer_name">Delivery</label>
                    </div>
                     <div class="col-md-4">
                      <label for=""></label>
                      <div class="col-md-2">
                        <input type="radio" {{(($calling['rcStatus'] == 0)? '' : 'checked')}} name="rcDelivery"   value="yes"> Yes
                      </div>
                      <div class="col-md-2">
                        <input type="radio"  {{(($calling['rcStatus'] == 0)? 'checked' : '')}}  name="rcDelivery"  value="no" > No
                      </div>
                    </div>
                  </div>
                  <div class="col-md-12" style="padding-left:30px;"><br>
                    <button type="submit" class="btn btn-success" >Update Delivery</button>
                  </div>
                </div>
              </form>
              @endif
              <br>
              <hr>
              <br>
                    <table id="table-pending" class="table table-bordered table-striped" style="text-align:center">
                        <thead>
                            <tr>
                              <th>Sr.No</th>
                              <th >{{__('last Call Date')}}</th>
                              <th >{{__('Next Call Date')}}</th>
                              <th >{{__('Call Satus')}}</th>
                              <th >{{__('Remark')}}</th>
                              {{-- <th rowspan="2">{{__('Next Call Date')}}</th> --}}
                              {{-- <th rowspan="2">{{__('Action')}}</th> --}}
                            </tr>
                        </thead>
                        <tbody>
                            @if(empty($allLastCallData))
                              <tr>
                                <td colspan="5" >Not-Found Data</td>
                              </tr>
                            @else
                              <?php $i = 1;  ?>
                              @foreach($allLastCallData as $key => $val)
                              <tr>
                                <td>{{$i}}</td>
                                <td>{{$val->call_date}}</td>
                                <td>{{$val->next_call_date}}</td>
                                <td>{{$val->call_status}}</td>
                                <td>{{$val->remark}}</td>
                                <?php $i++; ?>
                              </tr>
                              @endforeach
                            @endif
                        </tbody>               
                    </table>
                    <!-- /.box-body -->
                    <br>
                    <br>
                <div class="row">
                  <div class="col-md-12" >
                      <a href="/admin/call/log/nextcall?id={{$calling['id']}}&userid={{$calling['assigned_to']}}&call_type={{$calling['call_type']}}" class="btn btn-success" style="float:right">Next Call</a>
                  </div>
                </div>
            </div>
            <div class="box-body" id="rc_delivery"></div>
           
        </div>
        <div class="modal fade" id="dndCenter" tabindex="-1" role="dialog" aria-labelledby="dndCenterTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
              <div class="modal-content" style="margin-top: 200px!important;">
                <div class="modal-header">
                  <h4 class="modal-title" id="dndModalLongTitle">DND</h4>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body">
                  <form id="dnd-form" action="/admin/call/log/updateDnd" method="POST" >
                    <div class="alert alert-danger" id="dndmodel-error" style="display: none">
                    </div>
                    @csrf
                    <input type="hidden" name="calling_id" value="{{$calling['id']}}" >
                    <div class="row">
                      <div class="col-md-6">
                        <input type="radio" name="dnd_option" class=" dnd_option" value="Call">  Call
                      </div>
                      <div class="col-md-6">
                        <input type="radio" name="dnd_option" class=" dnd_option" value="SMS">  SMS
                      </div>
                      {!! $errors->first('dnd_option', '<p class="help-block">:message</p>') !!}
                    </div>
               </div>
                <div class="modal-footer">
                  <button type="button"  class="btn btn-secondary" data-dismiss="modal">Close</button>
                  <button type="submit" class="btn btn-success">Submit</button>
                </div>
                </form>
              </div>
            </div>
          </div>
    </section>
@endsection