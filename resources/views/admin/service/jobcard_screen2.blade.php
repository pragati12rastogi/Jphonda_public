@extends($layout)
@section('title', __('Create Job Card'))
@section('breadcrumb')
<li><a href="#"><i class=""></i></a></li>
@endsection
@section('css')
<link rel="stylesheet" href="/css/responsive.bootstrap.css">
<link rel="stylesheet" href="/css/bootstrap-duration-picker.css">

@endsection
@section('js')
<script src="/js/dataTables.responsive.js"></script>
<script src="/js/bootstrap-duration-picker.js"></script>
<meta name="csrf-token" content="{{ csrf_token() }}" />
<script src="/js/pages/moment-with-locales.js"></script>
<script src="/js/pages/RecordRTC.js"></script>
<!-- <footer style="margin-top: 20px;"><small id="send-message"></small></footer> -->
<script src="/js/pages/common.js"></script>
<script src="/js/pages/audio.js"></script>

<script>
  
  $('#js-msg-error').hide();
  $('#js-msg-success').hide();
  var start_time1 = @php echo json_encode($jobCard->estimated_delivery_time); @endphp;
  var duration = @php echo json_encode($jobCard->service_duration); @endphp;  
  var customer_status = @php echo json_encode($jobCard->customer_status); @endphp;
  var start_time = new Date(start_time1);
  var time = start_time.getHours() + ":" + start_time.getMinutes();
  $(document).ready(function(){
    if (start_time1) {
       $('#timepicker').wickedpicker({
            twentyFour:true,
            now:time
        });
     }
      });
       $('#estimate_hour').on('change', function() {
          var timet = $("#estimate_hour").val();
          var dt = new Date();
          var time = dt.getHours() + ":" + dt.getMinutes();
          var extened_time = 0; 
          if(customer_status == 'droping')
          {
             extened_time = 40;
          }
          else{
             extened_time = 30;
          }
         var delivertime =  moment.utc(time,'HH:mm').add(extened_time,'minutes').add(timet,'seconds').format('HH:mm');
         $(".timepicker").val(delivertime);
         $('.timepicker').wickedpicker({
            twentyFour:true,
            now: delivertime
         }); 
      });
  

$('.duration-picker').durationPicker({
    translations: {
        day: 'dia',
        hour: 'hours',
        minute: 'minutes',
        second: 'segundo',
        days: 'dias',
        hours: 'hours',
        minutes: 'minutes',
        seconds: 'segundos',
    },
    showSeconds: false,
    showDays: false,
    onChanged: function (value, isInitializing) {
    }
});


$("#form").validate({ 
 
    rules: { 
            "checklist[]": { 
                    required: true, 
                    minlength: $('input[name="cheklist"]').length
            } 
    }, 
    messages: { 
            "checklist[]": "Please check all check list."
    } 
}); 

$("#checkAll").change(function(){
    if(this.checked){
      $(".checklist").each(function(){
        this.checked=true;
      })              
    }else{
      $(".checklist").each(function(){
        this.checked=false;
      })              
    }
  });


 $(document).on('click','.add-div',function(){
        var main_div = $(this).parent().parent().parent().attr('id');
        var id_arr = main_div.split('-');
        $('#append_count').val(parseInt($("#append_count").val())+1);
        var high_index = $('#append_count').val();
        var ele = $(this).parent().parent().parent().html();
        ele = ele.replace('part_name-0','part_name-'+high_index);
        ele = ele.replace('part_qty-0','part_qty-'+high_index);
        ele = ele.replace('customer_conf-0','customer_conf-'+high_index);
        ele = ele.replace('call_status_div-0','call_status_div-'+high_index);
        ele = ele.replace('reason_div-0','reason_div-'+high_index);
        $('#append-div').append("<div class='col-md-12' id='div-"+high_index+"'>"+ele+"</div>");
        $("#div-"+high_index).find('label').find('.color-action').css('cursor','pointer');
        $('#call_status_div-'+high_index).hide();
         $('#reason_div-'+high_index).hide();
    });

    
    $(document).on('click','.remove-div',function(){
        var main_div = $(this).parent().parent().parent().attr('id');
        var id_arr = main_div.split('-');
        var index = id_arr[1];
        if(parseInt(index) > 0)
        {
            $("#div-"+index).remove();
        }
    });

//   $(document).ready(function() {
//     $('form').on('submit', function(e) {
//         $('.inputValidation').each(function(e) {
//             $(this).rules("add", {
//                 required: true,
//                 messages: {
//                     required: "This field is required"
//                 }
//             });
//         });
//     });
//     valx = $('form').validate();


// });

</script>
@endsection
@section('main_section')

<section class="content">
   <div id="app">

         @include('admin.flash-message')
         @yield('content')

         <div class="box-header with-border">
           @include('layouts.jobcard_tab')
          <div class="row">
         <div class="alert alert-danger" id="js-msg-error" style="display: none;">
         </div>
         <div class="alert alert-success" id="js-msg-success" style="display: none;">
         </div>
       </div>

            <div class="box box-primary">
                <div class="box-header">

                </div>  
               
               <form  action="/admin/service/create/jobcard/screen2/{{$jobCardId}}" method="post" id="form" enctype="multipart/form-data">
                    @csrf
                  <div class="row">
                   <!--  <audio controls="controls">
                      <source src="/upload/audio/{{$jobCard['recording']}}"/>;
                    </audio> -->
                     <div class="col-md-6 margin-bottom">
                        <div class="col-md-12">
                          <input type="hidden" name="jobcard_id" id="jobcard_id" value="{{$jobCardId}}">
                        </div>
                        <div class="col-md-12">
                          <button id="btn-start-recording" class="btn btn-info btn-xs">Start Recording</button>
                          <button id="btn-stop-recording" class="btn btn-info btn-xs" disabled>Stop Recording</button>
                          <button id="btn-release-microphone" class="btn btn-info btn-xs" disabled>Release Microphone</button>
                          <button id="btn-download-recording" class="btn btn-info btn-xs" disabled>Save</button>
                          <hr>
                          <div>
                            <audio controls autoplay playsinline name="audio_data"></audio>
                          </div>
                        </div>
                        <br>
                           <div id="duration_div" style="display: block;">
                           <div class="col-md-12 margin-bottom">
                              <label for="estimate_hour">Estimate Service Duration (HH:MM) <sup>*</sup></label>
                              <input type="text" name="estimate_hour" id="estimate_hour"  class="input-css duration-picker" placeholder="Enter Estimate Hour">
                              {!! $errors->first('estimate_hour', '<p class="help-block">:message</p>') !!}
                           </div>
         
                           <div class="col-md-12 margin-bottom">
                              <label for="estimate_delivery">Estimate Delivery <sup>*</sup></label>
                              <input type="text" name="estimate_delivery" value="{{$jobCard->estimated_delivery_time}}" id="timepicker" class="input-css timepicker inputValidation" placeholder="Enter Estimate Delivery">
                              {!! $errors->first('estimate_delivery', '<p class="help-block">:message</p>') !!}
                           </div>
                         </div>
                        @if($jobCard->oilinfornt_customer == null)
                        
                           <div class="col-md-12 margin-bottom">
                              <label for="oilinfornt_customer">Oil change (Infront of Customer)<sup>*</sup></label>
                              
                              <div class="col-md-2">
                                 <div class="radio">
                                    <label><input autocomplete="off" {{(old('oilinfornt_customer') == 'Yes') ? 'checked' : '' }} type="radio" class="" value="Yes" name="oilinfornt_customer">Yes</label>
                                 </div>
                              </div>
                              <div class="col-md-2">
                                 <div class="radio">
                                    <label><input autocomplete="off"  {{(old('oilinfornt_customer') == 'No') ? 'checked' : '' }}  type="radio" class="" value="No" name="oilinfornt_customer">No</label>
                                 </div>
                              </div>
                           </div>
                           {!! $errors->first('oilinfornt_customer', '
                              <p class="help-block">:message</p>
                              ') !!}
                           @else
                           <input type="hidden" name="oilinfornt_customer" value="{{$jobCard->oilinfornt_customer}}">
                             @if($jobCard->oilinfornt_customer == 'Yes')
                             <label for="oilinfornt_customer">Oil change (Infront of Customer) : {{$jobCard->oilinfornt_customer}}</label>
                             @else
                             @endif
                           @endif
                          <div class="col-md-12 margin-bottom">
                            <label for="oilinfornt_customer">Part Request</label>
                            <input type="hidden" hidden name="append_count" id="append_count" value="0">

                            <div class="row" id="append-div">
                              <div class="col-md-12" id="div-0">
                                  <div class="col-md-2">
                                      <label>
                                          <i  class="fa fa-trash color-action margin-r-5 remove-div"  ></i>
                                          <i  class="fa fa-plus add-div" style="color:blue;cursor:pointer" ></i>
                                      </label>
                                  </div>
                                  <div class="col-md-6">
                                      <label>Part Name</label>
                                      <input type="text" name="part_name[]" id="part_name-0" class="input-css inputValidation" value="@if(old('part_name')){{((old('part_name')[0])?(old('part_name')[0]):'')}}@endif" placeholder="Enter Part Name">
                                      {!! $errors->first('part_name.*', '<p class="help-block">:message</p>') !!}
                                  </div>
                                  <div class="col-md-4">
                                      <label>Quantity</label>
                                      <input type="number" id="part_qty-0" name="part_qty[]" class="input-css inputValidation" value="@if(old('part_qty')){{((old('part_qty')[0])?(old('part_qty')[0]):'')}}@endif" placeholder="Enter Part Quantity" min="0">
                                      {!! $errors->first('part_qty.*', '<p class="help-block">:message</p>') !!}
                                  </div>
                                 </div>
                            </div>
                          </div><br>
                          @if(isset($PartRequest))
                           @if($PartRequest != '[]')
                          <div class="col-md-12 margin-bottom">
                             <table class="table table-bordered table-striped">
                                  <thead>
                                      <th>Part Name</th>
                                      <th>Quantity</th>
                                      <th>Confirmation</th>
                                      <th>Status</th>
                                  </thead>
                                  <tbody>
                                     
                                          @foreach($PartRequest as $part)
                                              <tr>
                                                  <td>{{$part->part_name}}</td>
                                                  <td>{{$part->qty}}</td>
                                                  <td>{{$part->confirmation}} - @if($part->approved == 1) Yes @else No @endif</td>
                                                  <td>{{$part->status}}</td>
                                              </tr>
                                          @endforeach
                                     
                                  </tbody>
                              </table>
                          </div>
                           @endif
                          @endif
                        <div class="col-md-12">
                         <button type="submit" class="btn btn-success pull-right">Next</button>
                     </div>
                   </div>
                   @if($checklist != '' && ($jobCard['job_card_type'] == 'Paid' || $jobCard['job_card_type'] == 'Free' || $jobCard['job_card_type'] == 'General') )
                      <div class="col-md-6 margin-bottom" id="cheklist">
                         <label for="checklist">Service Check List </label><br>
                         <input type="checkbox" name="checkAll" id="checkAll" class="checklist"> Check All<br>
                         @foreach($checklist as $list)
                         @if($list != '')
                          @foreach($list as $val)
                            <input type="checkbox" class="checklist" name="checklist[]" value="{{$val->id}}"> {{$val->name}}<br>
                          @endforeach
                          @endif
                         @endforeach
                      </div>
                    @endif
                  </div>
                </form>
            </div>
         </div>
   </div>
   
   <!-- Modal -->
   <!-- /.box -->
</section>
@endsection
