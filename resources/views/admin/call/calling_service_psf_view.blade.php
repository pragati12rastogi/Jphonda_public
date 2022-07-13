@extends($layout)

@section('title', __('Call Log View'))

@section('user', Auth::user()->name)

@section('breadcrumb')

    <li><a href="/admin/call/log/list"><i class=""></i>{{__('Call Log Summery')}}</a></li> 
    <li><a href="#"><i class=""></i>{{__('Call Log View')}}</a></li> 
@endsection
@section('css')

  <link rel="stylesheet" href="/css/responsive.bootstrap.css">
  <style>
    .content{
    padding: 30px;
  }
  .nav1>li>button {
    position: relative;
    display: block;
    padding: 10px 34px;
    background-color: white;
    margin-left: 10px;
  }

  @media (max-width: 768px)  
  {
    
    .content-header>h1 {
      display: inline-block;
      
    }
  }
  @media (max-width: 425px)  
  {
    
    .content-header>h1 {
      display: inline-block;
      
    }
  }
  .nav1>li>button.btn-color-active{
    background-color: rgb(135, 206, 250);
  }
  .nav1>li>button.btn-color-unactive{
    background-color: white;
  }
  .spanred{
    color: red;
  }

  th{
    text-align: center;
  }
  .info-color{
    padding: 7px;
    background-color: #87cefa;
  }
  /* datepicker css */

*{margin:0;padding:0;}


  </style>

@endsection
@section('js')

<script src="/js/dataTables.responsive.js"></script>
<script>
$(document).ready(function(){

  $('#next_call_date').datepicker({
   format: "yyyy-mm-dd",
   startDate: "dateToday"
});

$('.datepicker3').datepicker({
   format: "yyyy-mm-dd",
   startDate: "dateToday"
});

$('.timepicker').wickedpicker({
            twentyFour:true,
            show:null
        });

  var type = @php echo json_encode($calling['status']); @endphp;
 // console.log(type);
$("#callingForm").hide();
$("#bookingForm").hide();
  if(type == 'pending' || type == '')
  {
    $('#callForm').addClass('btn-color-active');
    $('#booking').removeClass('btn-color-active');
    $("#callingForm").show();
    $("#bookingForm").hide();
  }
  else if(type == 'all'){
    $('#booking').addClass('btn-color-active');
    $('#callForm').removeClass('btn-color-active');
    $("#callingForm").hide();
    $("#bookingForm").show();
  }

  $("#callForm").on('click',function(){
    $(this).addClass('btn-color-active');
    $('#booking').removeClass('btn-color-active');
    $("#callingForm").show();
    $("#bookingForm").hide();
  });
  $("#booking").on('click',function(){
    $(this).addClass('btn-color-active');
    $('#callForm').removeClass('btn-color-active');
    $("#callingForm").hide();
    $("#bookingForm").show();
  });
  $(document).on('change','.pick_drop',function(){
    $("#pick_drop").toggle('display');
  });
});
</script>
@endsection

@section('main_section')
    <section class="content">
        <div id="app">
            @include('admin.flash-message')
            @yield('content')
            
        </div>
    <!-- Default box -->
        <div class="box">
          <div class="box-header with-border">
            {{-- 
            <h3 class="box-title">{{__('customer.mytitle')}} </h3>
            --}}
            <div class="col-md-6">

              <ul class="nav nav1 nav-pills">
                <li class="nav-item">
                  <button class="nav-link1 btn-color-active" id="callForm" >Call's Form</button>
                </li>
                @if($calling['type'] == 'service')
                <li class="nav-item">
                  <button class="nav-link1" id="booking" >Booking</button>
                </li> 
                @endif
              </ul>
            </div>
              <div class="col-md-6">
               <a href="/admin/call/log/nextcall?id={{$calling['id']}}&userid={{$calling['assigned_to']}}&call_type={{$calling['call_type']}}" class="btn btn-success" style="float:right">Next Call</a>
            </div>
          </div>

                <!-- /.box-header -->
            <div class="box-body" id="pending-div">
              <br>
              <div class="row">
                <div class="col-md-12">
                  <label for="customer_details" class="info-color"> Customer Details</label>
                  <div class="col-md-3">
                    <label for="customer_name">Customer Name</label>
                  </div>
                  <div class="col-md-3">
                    <label name="customer_name">{{$calling['customer_name']}}</label>
                  </div>
                  <div class="col-md-3">
                    <label for="customer_name">Mobile</label>
                  </div>
                  <div class="col-md-3">
                    <label name="customer_name">{{$calling['mobile']}}</label>
                  </div>
                </div>
                
              </div>
             {{$calling['amount']}}
               @if($calling['call_status'] != 'done')
                <hr>
              <form action="/admin/call/log/updateCall" method="POST" id="callingForm">
                @csrf
                <input type="hidden" name="type_id" id="type_id" value="{{$calling['type_id']}}">
                <input type="hidden" name="type" id="type" value="{{$calling['type']}}">
                <input type="hidden" name="call_type" id="call_type" value="{{$calling['call_type']}}">
                <input type="hidden" name="id" id="id" value="{{$calling['id']}}">
                <div class="modal-body">
                    <div class="row">
                      <div class="col-md-6">
                          <label>Call Status<sup>*</sup></label>
                          <input id="call_summary_id" type="hidden" name="call_summary_id" class="input-css">
                          <select id="call_status"  name="call_status" class="form-control select2">
                            <option value="">Select Call Status</option>
                            <option value="Busy">Busy</option>
                            <option value="Not Reachable">Not Reachable</option>
                            <option value="Not Received">Not Received</option>
                            <option value="Received">Received</option>
                            <option value="Done">Received & done</option>
                          </select>
                          {!! $errors->first('call_status', '
                        <p class="help-block">:message</p>
                        ') !!}
                          <br>
                      </div>
                      <div class="col-md-6">
                        <label>Next Call Date<sup>*</sup></label>
                        <input id="next_call_date" type="text" name="next_call_date" minDate="0" class="input-css ">
                        {!! $errors->first('next_call_date', '
                        <p class="help-block">:message</p>
                        ') !!}
                        <br>
                    </div>
                      <div class="col-md-12">
                          <label>Remark <sup>*</sup></label>
                          <textArea id="remark" type="text" name="remark" class="form-control"></textArea>
                          {!! $errors->first('remark', '
                        <p class="help-block">:message</p>
                        ') !!}
                      </div>
                    </div>
                </div>
                <div class="col-md-12" style="padding-left:30px;">
                  <button type="submit" class="btn btn-success" >Submit</button>
                </div>
              </form>
              @endif
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
                          <div class="col-md-12" style="{{(count($store) == 1)? 'display:none' : 'display:block'}}">
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
           
        </div>
          
    </section>
@endsection