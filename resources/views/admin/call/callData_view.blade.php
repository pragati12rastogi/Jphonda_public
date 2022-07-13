@extends($layout)

@section('title', __('Call Data View'))

@section('user', Auth::user()->name)

@section('breadcrumb')

    <li><a href="/admin/call/data/list"><i class=""></i>{{__('Call Data Summery')}}</a></li> 
    <li><a href="#"><i class=""></i>{{__('Call Data View')}}</a></li> 
@endsection
@section('css')

  <link rel="stylesheet" href="/css/responsive.bootstrap.css">
  <link rel="stylesheet" href="/css/call_data.css">
  
@endsection
@section('js')
<meta name="csrf-token" content="{{ csrf_token() }}" />
<script src="/js/pages/firebase_call_data.js"></script>
<script src="/js/dataTables.responsive.js"></script>
<script>
  $(document).ready(function(){

    // var dnd_option = "{!! $errors->first('dnd_option') !!}";
    var dnd_option = JSON.parse('{!! $errors !!}');
    // console.log(dnd_option);
    if(dnd_option.hasOwnProperty('dnd_option')){
      $("#dndCenter").modal('show');
    }

    $('#request_enquiry').on('click',function(){
      var conf = confirm("Are You Sure ?");
      if(conf){
        return true;
      }
      return false;
    });

    no_call_present();
    new_call_start();

     
      @if($call_data_details['cash_finance'] == 2)
        financeSelect();
        $("#finance_company_name").trigger('change');
      @endif
    

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

  function financeSelect(){
    $fin_val =$('[name="cash_finance"]:checked').val();
    if($fin_val == 2){
      $('#financer_block').show();
    }else{
      $('#financer_block').hide();

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

  var forbidden = @php echo json_encode($forbidden); @endphp;
  var copy_forbidden = @php echo json_encode($forbidden); @endphp;
  
  var ni_setting_start_day = @php echo json_encode($ni_setting_start_day); @endphp;
  var ni_setting = @php echo json_encode($ni_setting->value); @endphp;
  // console.log(forbidden);
  var financeName = @php echo json_encode((old('finance_name')) ? old('finance_name') : '' ); @endphp;
  var call_duration = @php echo json_encode((old('call_duration')) ? old('call_duration') : '' ); @endphp;
  var ni_setting_call_type = @php echo json_encode($call_data_details->type); @endphp;

  $(document).on('change','#call_status',function(){
    var val = $(this).val();
    $(document).find('.datepicker_with_disabled').datepicker('destroy');

    if(val == 'NotInterested' && ni_setting_start_day){
      
      var last_date = ni_setting_start_day;
      
      if(ni_setting_call_type == 1){
          $(document).find('.datepicker_with_disabled').val(last_date);
          /*.datepicker({
              autoclose: true,
              format: "yyyy-mm-dd",
              startDate: new Date(last_date),
              beforeShowDay: function(Date) {
                  var curr_date = Date.toJSON().substring(0, 10);
                  if (typeof forbidden === 'object') {
                      if (forbidden.indexOf(curr_date) > -1) return false;
                  } else {
                      return true;
                  }
              }
            }).datepicker("setDate", last_date).trigger('change');*/
          $("#next_call_date").prop('readonly',true);
      }else{

        // console.log(forbidden,'forbidden');
        $(document).find('.datepicker_with_disabled').datepicker({
          autoclose: true,
          format: "yyyy-mm-dd",
          startDate: new Date(last_date),
          beforeShowDay: function(Date) {
              var curr_date = Date.toJSON().substring(0, 10);
              if (typeof forbidden === 'object') {
                  if (forbidden.indexOf(curr_date) > -1) return false;
              } else {
                  return true;
              }
          }
        });
      }

      $("#nti_info").text("Note :- Recommended selected date be after "+ni_setting+" days.")
    }else{
      $(document).find('.datepicker_with_disabled').datepicker({
        autoclose: true,
        format: "yyyy-mm-dd",
        startDate: new Date(),
        beforeShowDay: function(Date) {
            var curr_date = Date.toJSON().substring(0, 10);
            if (typeof forbidden === 'object') {
                if (forbidden.indexOf(curr_date) > -1) return false;
            } else {
                return true;
            }
        }
      });
      $("#nti_info").text("");
    }

    if(val == 'Closed'){
      $("#nextcall_div").hide();
    }else{
      $("#nextcall_div").show();
    }

  });

  $(document).on('submit', '#service_booking-form', function(e) {
        var call_data_id = $("#call_data_id").val();
        var name = $("#servicename").val();
        var mobile = $("#mobile").val();
        var phoneno = /^\d{10}$/;
        if (!name) {
            $('#model-error').text("Enter name").show();
            return false;
        } else if (!mobile) {
            $('#model-error').text("Enter mobile no").show();
            return false;
        } else {
            if (!mobile.match(phoneno)) {
                $('#model-error').text("Invalid mobile no").show();
                return false;
            }
        }
        return true;
  });

  $("#check_sold").on('click',function(){
    $("#soldSearch-error").hide();
    $("#soldSearch-success").hide();
    $("#sold_search-append_div").empty();
  });

  $('#sold_search-btn').on('click',function(e) {
    // e.preventDefault();     
    $("#soldSearch-error").hide();
    $("#soldSearch-success").hide();
    $("#sold_search-append_div").empty();

    formvalidation = true;
    count_validation = 0;
    $("#soldSearch-form").find('.sold_validation').each(function(el){
      var val = $(this).val();
      if(val != null &&  val != undefined && val != '') {
        count_validation = 1;
      }
    });
    if(count_validation == 0){
      formvalidation = false;
      $("#soldSearch-error").text('Any One Field is Required.');
      // $(this).parent().append("<span style='color:red'>This Field is Required.</span>");
      return false;
    }
    $('#soldSearch-error').hide();
    $('#soldSearch-success').hide();
    var name = $("#soldSearch-form").find('.sold_name').val();
    var mobile = $("#soldSearch-form").find('.sold_mobile').val();
    var aadhar = $("#soldSearch-form").find('.sold_aadhar').val();
    var call_data_id = $("#soldSearch-form").find('input[name="call_data_id"]').val();
    var formData = {'sold_name':name, 'sold_mobile':mobile,'sold_aadhar':aadhar,'call_data_id':call_data_id};
    if(formvalidation==true)
    {
      $('#loader').show();
      $.ajax({
        type:'GET',
        url: "/admin/call/data/view/check/sold",
        data: formData,
        success:function(data){
          if(data[0]){
            var arr = data[1];
            draw_sale_data(arr);
          }else{
            $('#soldSearch-error').text(data[1]).show();
          }
        },
        error:function(error){
          $('#loader').hide();
          // console.log(error);
          if ((error.responseJSON).hasOwnProperty('message')) {
            $('#soldSearch-error').html(error.responseJSON.message).show();
          }else{
             $('#soldSearch-error').html(error.responseText).show();
          }
        }
      }).done(function(){
        $('#loader').hide();
      });
    }
  });

  function draw_sale_data(arr){
    var append_div = $("#sold_search-append_div");
    append_div.empty();
    var str = "<hr>";
    $.each(arr,function(key,val){
      str = str+"<div class='col-md-12 margin-bottom' style='background-color:aliceblue'>"+
              "<div class='col-md-6'>"+
                "<label>Sale #</label>"+
              "</div>"+
              "<div class='col-md-6'>"+
                "<label>"+val.sale_no+"</label>"+
              "</div>"+
              "</div>";
    });
    append_div.append(str);
    var btn = '<div class="row pt-3 margin"><button type="submit" class="btn btn-sm btn-warning">Add Sold</button></div><hr>';
    append_div.append(btn);
  }
  $('#soldSearch-form').submit(function(e) {
    e.preventDefault();     
    $("#soldSearch-error").hide();
    $("#soldSearch-success").hide();
    formvalidation = true;
    count_validation = 0;
    $("#soldSearch-form").find('.sold_validation').each(function(el){
      var val = $(this).val();
      if(val != null &&  val != undefined && val != '') {
        count_validation = 1;
      }
    });
    if(count_validation == 0){
      formvalidation = false;
      $("#soldSearch-error").text('Any One Field is Required.');
      // $(this).parent().append("<span style='color:red'>This Field is Required.</span>");
      return false;
    }

    var formData = new FormData(this);
    if(formvalidation==true)
    {
      $('#loader').show();
      $.ajax({
        type:'POST',
        url: "/admin/call/data/view/check/sold",
        data: formData,
        cache:false,
        contentType: false,
        processData: false,
        success: (data) => {
          this.reset();
          $('#soldSearch-success').text(data.trim()).show();
        },
        error: function(error){
          $('#loader').hide();
          // console.log(error);
          if ((error.responseJSON).hasOwnProperty('message')) {
            $('#soldSearch-error').html(error.responseJSON.message).show();
          }else{
             $('#soldSearch-error').html(error.responseText).show();
          }
        }
      }).done(function(){
        $('#loader').hide();
      });
    }
  });

  $("#sale_enquiry-form").on('submit',function(){
    var msg = confirm("Are You Sure ?");
    if(msg){
      return true;
    }
    return false;
  });

  $("#insured-form").on('submit',function(){
    var msg = confirm("Are You Sure ?");
    if(msg){
      return true;
    }
    return false;
  });
  
  $("#csd_enquiry-form").on('submit',function(){
    var msg = confirm("Are You Sure ?");
    if(msg){
      return true;
    }
    return false;
  });
  $("#service_enquiry-form").on('submit',function(){
    var msg = confirm("Are You Sure ?");
    if(msg){
      return true;
    }
    return false;
  });

  $("#finance_company_name").on('change', function() {
    var finance_company_name = $(this).val();
    $("#finance_name").empty().trigger('change');
    $('#ajax_loader_div').css('display', 'block');
    var executive_id ='{{$call_data_details["executive_id"]}}';
    if (finance_company_name != '' && finance_company_name != null) {

        $.ajax({
            url: '/admin/get/financerExecutive',
            method: 'GET',
            data: { 'finance_company_name': finance_company_name },
            success: function(result) {
                // console.log(result);
                var str = '<option value"">Select Financier Executive</option>';
                $.each(result, function(key, val) {
                  if(val.id == executive_id){
                    str += '<option value="' + val.id + '" selected>' + val.executive_name + '</option>';
                  }else{
                    str += '<option value="' + val.id + '" >' + val.executive_name + '</option>';
                  }
                });
                $("#finance_name").append(str);
                // $('#ajax_loader_div').css('display', 'none');
            },
            error: function(error) {
                $("#finance_company_name").val('').trigger('change');
                alert('Error, Internal Issue Try Again');
                $('#ajax_loader_div').css('display', 'none');
            }
        }).done(function() {
            if (financeName != '') {
                $("#finance_name").val(financeName).trigger('change');
            }
            $('#ajax_loader_div').css('display', 'none');
        });
    }
  });

  function detail_model(model_for){
    var cust_number = $("#cust_number").text();
    $('#ajax_loader_div').css('display','block');
    if(model_for == 'sale'){

      $.ajax({
            url: '/admin/call/data/customer/sale',
            method: 'GET',
            data: {'cust_number': cust_number},
            success: function(result) {console.log(result);
              var result = JSON.parse(result);

              $('#ajax_loader_div').css('display','none');
              $('#transaction_modelLongTitle').text('Sale');
              $("#t_head_append").empty();
              if(result['head'].length > 0){
                $.each(result['head'], function(key, value) {
                    $("#t_head_append").append('<th>' + value + '</th>');
                });
              }
              $('#transaction_model').modal('show');

              $("#t_body_append").empty();
              if(result['body'].length > 0){
                $.each(result['body'], function(key, value) {
                    $("#t_body_append").append('<tr><td>' + value.sale_no + '</td><td>' + value.total_amount + '</td><td>' + value.product_frame_number + '</td><td>' + value.product_name + '</td></tr>');
                });
              }else{
                $("#t_body_append").append('<tr><td colspan ="3" align="center">No data available</td></tr>');
              }  
            }
          });

    }else if(model_for == 'otc'){
      $.ajax({
            url: '/admin/call/data/customer/otcsale',
            method: 'GET',
            data: { 'cust_number': cust_number },
            success: function(result) {
                console.log(result);
                var result = JSON.parse(result);

                $('#ajax_loader_div').css('display','none');
                $('#transaction_modelLongTitle').text('OTC Sale');
                $("#t_head_append").empty();
                if(result['head'].length > 0){
                  $.each(result['head'], function(key, value) {
                      $("#t_head_append").append('<th>' + value + '</th>');
                  });
                }
                $('#transaction_model').modal('show');

                $("#t_body_append").empty();
                if(result['body'].length > 0){
                  $.each(result['body'], function(key, value) {
                      $("#t_body_append").append('<tr><td>' + value.sale_no + '</td><td>' + value.total_amount + '</td><td>' + value.product_frame_number + '</td><td>' + value.parts + '</td></tr>');
                  });
                }else{
                  $("#t_body_append").append('<tr><td colspan ="3" align="center">No data available</td></tr>');
                } 
            }
          });
    }else if(model_for == 'service'){
      $.ajax({
            url: '/admin/call/data/customer/service',
            method: 'GET',
            data: { 'cust_number': cust_number },
            success: function(result) {
                console.log(result);
                var result = JSON.parse(result);

                $('#ajax_loader_div').css('display','none');
                $('#transaction_modelLongTitle').text('Service');
                $("#t_head_append").empty();
                if(result['head'].length > 0){
                  $.each(result['head'], function(key, value) {
                      $("#t_head_append").append('<th>' + value + '</th>');
                  });
                }
                $('#transaction_model').modal('show');

                $("#t_body_append").empty();
                if(result['body'].length > 0){
                  $.each(result['body'], function(key, value) {
                      $("#t_body_append").append('<tr><td>' + value.sale_no + '</td><td>' + value.total_amount + '</td><td>' + value.frame + '</td><td>' + value.product_name + '</td></tr>');
                  });
                }else{
                  $("#t_body_append").append('<tr><td colspan ="3" align="center">No data available</td></tr>');
                }
            }
          });
    }else if( model_for == 'insurance'){
      $.ajax({
            url: '/admin/call/data/customer/insurance',
            method: 'GET',
            data: { 'cust_number': cust_number },
            success: function(result) {
                console.log(result);
                var result = JSON.parse(result);

                $('#ajax_loader_div').css('display','none');
                $('#transaction_modelLongTitle').text('Insurance');
                $("#t_head_append").empty();
                if(result['head'].length > 0){
                  $.each(result['head'], function(key, value) {
                      $("#t_head_append").append('<th>' + value + '</th>');
                  });
                }
                $('#transaction_model').modal('show');

                $("#t_body_append").empty();
                if(result['body'].length > 0){
                  $.each(result['body'], function(key, value) {
                      $("#t_body_append").append('<tr><td>' + value.sale_no + 
                        '</td><td>' + value.insurance_amount + 
                        '</td><td>' + value.product_frame_number + 
                        '</td><td>' + value.name + 
                        '</td><td>' + value.insurance_date + 
                        '</td><td>' + value.insurance_type + 
                        '</td><td>' + value.insurance_name + 
                        '</td><td>' + value.self_inc + '</td></tr>');
                  });
                }else{
                  $("#t_body_append").append('<tr><td colspan ="3" align="center">No data available</td></tr>');
                }

            }
          });
    }
  }
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
    <!-- Default box -->
        <div class="box">
          <div class="box-header with-border">
            <div class="col-md-12">
                <h2 style="display: inline;">{{$call_data_details['cdd_type']}}</h2>
               <a href="/admin/call/data/nextcall?id={{$call_data_id}}&call_type={{$call_data_details->type}}" class="btn btn-success" style="float:right">Next Call</a>
            </div>
          </div>

                <!-- /.box-header -->
            <div class="box-body" id="pending-div">
              <br>
              <div class="row">
                <div class="col-md-6 box-shadow-info">
                  <label for="customer_details" class="info-color"> Call Details</label>
                  <div class="col-md-6">
                    <label for="customer_name">Customer Name</label>
                  </div>
                  <div class="col-md-6">
                    <label name="customer_name" id="cust_name">{{$call_data->name}}</label>
                  </div>
                  <div class="col-md-6">
                    <label for="customer_name">Mobile</label>
                  </div>
                  <div class="col-md-6">
                    <p hidden id="cust_number">{{$call_data->mobile}}</p>
                    <label name="customer_name" >{{$call_data->mobile.''.(!empty($call_data->other_mobile)? ' , '.$call_data->other_mobile : '' )}}</label>
                  </div>
                  @if(!empty($call_data->model))
                    <div class="col-md-6">
                      <label for="customer_name">Model</label>
                    </div>
                    <div class="col-md-6">
                      <label name="customer_name">{{$call_data->model}}</label>
                    </div>
                  @endif
                  @if(!empty($call_data->frame))
                    <div class="col-md-6">
                      <label for="customer_name">Frame #</label>
                    </div>
                    <div class="col-md-6">
                      <label name="customer_name">{{$call_data->frame}}</label>
                    </div>
                  @endif
                  @if(!empty($call_data->date_of_sale))
                    <div class="col-md-6">
                      <label for="customer_name">Date Of Sale</label>
                    </div>
                    <div class="col-md-6">
                      <label name="customer_name">{{$call_data->date_of_sale}}</label>
                    </div>
                  @endif
                  @if(!empty($call_data->locality))
                    <div class="col-md-6">
                      <label for="customer_name">Locality - City</label>
                    </div>
                    <div class="col-md-6">
                      <label name="customer_name">{{$call_data->locality.'-'.$call_data->city}}</label>
                    </div>
                  @endif
                  @if(!empty($call_data->source))
                    <div class="col-md-6">
                      <label for="customer_name">Source</label>
                    </div>
                    <div class="col-md-6">
                      <label name="customer_name">{{$call_data->source}}</label>
                    </div>
                  @endif
                  @if(isset($call_data_details->query_status))
                    <div class="col-md-6">
                      <label for="customer_name">Call Status</label>
                    </div>
                    <div class="col-md-6">
                      <label name="customer_name">{{$call_data_details->query_status}}</label>
                    </div>
                  @endif
                </div>
                <div class="col-md-6 margin-bottom" style="height:50%; oveflow:auto; overflow-y: scroll;">
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
                  
                              <h3 class="timeline-header"><a href="#">Short Summary({{$val['cd_type']}})</a></h3>
                  
                              <div class="timeline-body">
                                 <div class="row">
                                    <label class="col-md-3 timeline-label" >Call Type </label>
                                    <label class="col-md-3 timeline-value"> {{$val['call_type']}} </label>

                                    <label class="col-md-3 timeline-label">Call Date </label>
                                    <label class="col-md-3 timeline-value"> {{$val['call_date']}} </label>
                                 </div>
                                 <div class="row">
                                    <label class="col-md-3 timeline-label">Call status </label>
                                    <label class="col-md-3 timeline-value"> {{$val['call_status']}} </label>
                                
                                    <label class="col-md-3 timeline-label">Call Remark </label>
                                    <label class="col-md-3 timeline-value"> {{$val['remark']}} </label>
                                 </div>
                                 <div class="row">
                                  <label class="col-md-3 timeline-label">Next Call Date</label>
                                  <label class="col-md-3 timeline-value"> {{$val['next_call_date']}} </label>
                               
                                  <label class="col-md-3 timeline-label">Updated By</label>
                                  <label class="col-md-3 timeline-value"> {{$val['updated_by']}} </label>
                                </div>
                              </div>
                  
                           </div>
                        </li>
                     @endforeach
                     
                  </ul>
                </div>
              </div>

              @php
              $call_allow = 1;
              if(isset($dnd_setting->id)){
                $dnd_type= explode(',',$call_data->dnd);
                if(in_array("Call", $dnd_type))
                {
                  if($call_data->dnd_day <= $dnd_setting->value){
                    $call_allow = 0;
                  } 
                }
              }
              @endphp

              @if($call_data->request_enquiry != 1)
              <div class="row">
                <form action="/admin/call/data/requestEnquiry" method="POST" >
                  @csrf
                  <input type="hidden" name="call_data_id" value="{{$call_data_id}}" hidden>
                  <div class="col-md-12">
                    <button type="submit" id="request_enquiry" class="btn btn-info">Mobile Number Mismatch</button>
                  </div>
                </form>
              </div>
              @else
              <div class="row">
                <label class="info-color">Request Enquiry :- <small class="text-label">Pending</small></label>
              </div>
              @endif

              <div class="row margin box-shadow">
                  <div class="row">
                      <table id="table-pending" class="table table-bordered table-responsive" style="text-align:center">
                          <label class="khaki-color"  style="text-align:center;font-weight: bold;font-size: 14px;">Transactions</label>
                          <thead>
                            <tr>
                              <td style="font-weight: bold;font-size: 14px;">Type</td>
                              <td style="font-weight: bold;font-size: 14px;">Sale Number</td>
                              <td style="font-weight: bold;font-size: 14px;">Total Amount</td>
                              <td style="font-weight: bold;font-size: 14px;">Frame Number</td>
                              <td style="font-weight: bold;font-size: 14px;">Action</td>
                            </tr>
                          </thead>
                          <tbody>
                            @if(isset($last_sale))
                                <tr>
                                    <td class="transaction_tbody">
                                      Sale
                                    </td>
                                    <td class="transaction_tbody">
                                      {{$last_sale->sale_no}}
                                    </td>
                                    <td class="transaction_tbody">
                                      {{$last_sale->total_amount}}
                                    </td>
                                    <td class="transaction_tbody">
                                    {{$last_sale->product_frame_number}}
                                    </td>
                                    <td class="transaction_tbody">
                                        <button class="btn btn-info btn-xs" onclick="detail_model('sale')">View</button>
                                    </td>
                                  </tr>
                            @endif
                            @if(isset($last_otc))
                                <tr>
                                    <td class="transaction_tbody">
                                      OTC Sale
                                    </td>
                                    <td class="transaction_tbody">
                                      {{$last_otc->sale_no}}
                                    </td>
                                    <td class="transaction_tbody">
                                      {{$last_otc->total_amount}}
                                    </td>
                                    <td class="transaction_tbody">
                                    {{$last_otc->product_frame_number}}
                                    </td>
                                    <td class="transaction_tbody">
                                        <button class="btn btn-info btn-xs" onclick="detail_model('otc')">View</button>
                                    </td>
                                  </tr>
                            @endif
                            @if(isset($last_insurance))
                                <tr>
                                    <td class="transaction_tbody">
                                      Insurance
                                    </td>
                                    <td class="transaction_tbody">
                                      {{$last_insurance->sale_no}}
                                    </td>
                                    <td class="transaction_tbody">
                                      {{$last_insurance->insurance_amount}}
                                    </td>
                                    <td class="transaction_tbody">
                                    {{$last_insurance->product_frame_number}}
                                    </td>
                                    <td class="transaction_tbody">
                                        <button class="btn btn-info btn-xs" onclick="detail_model('insurance')">View</button>
                                    </td>
                                  </tr>
                            @endif
                            @if(isset($last_service))
                                <tr>
                                    <td class="transaction_tbody">
                                      Service
                                    </td>
                                    <td class="transaction_tbody">
                                      {{$last_service->sale_no}}
                                    </td>
                                    <td class="transaction_tbody">
                                      {{$last_service->total_amount}}
                                    </td>
                                    <td class="transaction_tbody">
                                    {{$last_service->frame}}
                                    </td>
                                    <td class="transaction_tbody">
                                        <button class="btn btn-info btn-xs" onclick="detail_model('service')">View</button>
                                    </td>
                                  </tr>
                            @endif
                            @if(!isset($last_sale) && !isset($last_otc) && !isset($last_insurance) && !isset($last_service))
                                <tr>
                                  <td colspan="5" >No Data Found</td>
                                </tr>
                            @endif
                          </tbody>
                      </table>
                  </div>
              </div>

              <div class="row margin box-shadow">
                {{-- <hr> --}}
                @if(isset($service_booking->id) ? (($service_booking->status != 'Booked') ? true : false ) : true )
                    {{-- <button type="submit" class="btn btn-info">Request Enquiry</button> --}}
                    <div class="col-md-3 margin ">
                      <button id="service_booking" data-toggle="modal" data-target="#exampleModalCenter"class="btn btn-success">Service Booking</button>
                    </div>
                  @else
                  <div class="row">
                    <table id="table-pending" class="table table-bordered table-responsive" style="text-align:center">
                      <label class="info-color"  style="text-align:center;font-weight: bold;font-size: 14px;">Service Booking</label>
                      <thead>
                          <tr>
                            <td style="font-weight: bold;font-size: 14px;">Name</td>
                            <td style="font-weight: bold;font-size: 14px;">Mobile No</td>
                            <td style="font-weight: bold;font-size: 14px;">Booking Status</td>
                          </tr>
                          <tr>
                            <td style="text-align:left;font-weight: bold;font-size: 14px;">
                              {{$service_booking->name}}
                            </td>
                            <td style="text-align:left;font-size: 14px;">
                              {{$service_booking->mobile}}
                            </td>
                            <td style="text-align:left;font-size: 14px;">
                            {{$service_booking->status}}
                            </td>
                          </tr>
                      </thead>               
                    </table>
                  </div>
                @endif
                <div class="row ">
                  <div class="col-md-3 margin">
                    <!-- @php if($call_allow==0) echo 'disabled'; @endphp -->
                    <button id="dnd" data-toggle="modal"  data-target="#dndCenter"class="btn btn-danger">DND</button>
                  </div>
                  @if($call_data_details['type']!=2)
                    <div class="col-md-3 margin">
                      <button id="check_sold" data-toggle="modal" data-target="#soldSearch"class="btn btn-warning">Check Sold</button>
                    </div>
                  @endif
                  @if($call_data_details['type']==2)
                    @if(isset($csd_service_enquiry->Insured) && $csd_service_enquiry->Insured == 'Yes')
                      <div class="col-md-3 margin">
                       <button type="button" class="btn btn-dropbox " disabled>Insured Done</button>
                      </div>
                    @else
                      <div class="col-md-3 margin">
                        <form method="POST" action="/admin/call/data/view/insured" id="insured-form">
                          @csrf
                          <input type="hidden" name="call_data_id" hidden value="{{$call_data_id}}">
                          <input type="hidden" name="call_data_detail_id" hidden value="{{$call_data_details->id}}">
                          <div class="col-md-6">
                            <button type="submit" class="btn btn-dropbox ">Insured</button>
                          </div>
                        </form>
                      </div>
                    @endif
                  @endif
                </div>
              </div>
              <div class="row margin box-shadow">
                <form method="POST" action="/admin/call/data/view/change_label" id="change_label-form">
                  @csrf
                  <input type="hidden" name="call_data_id" hidden value="{{$call_data_id}}">
                  <input type="hidden" name="call_data_detail_id" hidden value="{{$call_data_details->id}}">
                <label for="call_level">Call Level <sup>*</sup></label>
                <div class="col-md-6">
                  <div class="col-md-4">
                    <input type="radio" name="call_level" class="call_level" {{($call_data_details['call_level'] == 1) ? 'checked' : ''}} value="1"> Hot
                  </div>
                  <div class="col-md-4">
                    <input type="radio" name="call_level" class="call_level" value="2" {{($call_data_details['call_level'] == 2) ? 'checked' : ''}}> Warm
                  </div>
                  <div class="col-md-4">
                    <input type="radio" name="call_level" class="call_level" value="3" {{($call_data_details['call_level'] == 3) ? 'checked' : ''}}> Cold
                  </div>
                  {!! $errors->first('call_level', '<p class="help-block">:message</p>') !!}
                </div>
                <div class="col-md-3">
                  <button type="submit" class="btn btn-success btn-sm">Change Level</button>
                </div>
                </form>
              </div>
              @if($call_data_details['type']!=2)
                <div class="row margin box-shadow">
                  <form method="POST" action="/admin/call/data/view/cash_finance" id="cash_finance-form">
                    @csrf
                    <input type="hidden" name="call_data_id" hidden value="{{$call_data_id}}">
                    <input type="hidden" name="call_data_detail_id" hidden value="{{$call_data_details->id}}">
                  {{-- <label for="call_level"> <sup>*</sup></label> --}}
                    <div class=col-md-12>
                      <div class="col-md-4">
                        <div class="col-md-6">
                          <input type="radio" name="cash_finance" class="cash_finance" {{($call_data_details['cash_finance'] == 1) ? 'checked' : ''}} value="1" onclick="financeSelect()"> Cash
                        </div>
                        <div class="col-md-6">
                          <input type="radio" name="cash_finance" class="cash_finance" value="2" {{($call_data_details['cash_finance'] == 2) ? 'checked' : ''}} onclick="financeSelect()"> Finance
                        </div>
                        {!! $errors->first('cash_finance', '<p class="help-block">:message</p>') !!}
                      </div>
                      <div class="" id="financer_block" style="display: none">
                        <div class="col-md-3">
                          <select name="finance_company_name" class="input-css selectValidation select2" id="finance_company_name" width="100%">
                            <option value="">Select Company Name</option>
                            @foreach($company_name as $key => $val)
                               <option value="{{$val->id}}" {{($call_data_details['company_id'] == $val->id) ? 'selected' : ''}}>{{$val->company_name}}</option>
                            @endforeach
                          </select>
                           {!! $errors->first('finance_company_name', '
                           <p class="help-block">:message</p>
                           ') !!}
                          
                        </div>
                        
                        <div class="col-md-3">
                          <select name="finance_name" class="input-css selectValidation select2" id="finance_name">
                            <option value="">Select Financier Executive</option>
                          </select>
                           {!! $errors->first('finance_name', '
                           <p class="help-block">:message</p>
                           ') !!}
                        </div>
                         
                      </div>
                      <div class="col-md-1">
                        <button type="submit" class="btn btn-success btn-sm" style="margin-left:40px">Update</button>
                      </div>
                    </div>
                  </form>
                </div>
              @endif
              {{--@if(isset($csd_service_enquiry->CsdEnquiry) && isset($csd_service_enquiry->ServiceEnquiry) )--}}
              {{-- @if($csd_service_enquiry->CsdEnquiry == 'No' || $csd_service_enquiry->ServiceEnquiry == 'No' ) --}}
              <div class="row margin box-shadow">
                <div class="col-md-6">
                  @if($call_data_details->type != 3 && $call_data_details->type != 2)
                    @if(isset($csd_service_enquiry->CsdEnquiry) && $csd_service_enquiry->CsdEnquiry == 'Yes')
                    <div class="col-md-6">
                      <button type="button" class="btn btn-primary btn-sm" disabled>CSD Enquiry Created</button>
                    </div>
                    
                    @else
                    <form method="POST" action="/admin/call/data/view/csdEnquiry" id="csd_enquiry-form">
                      @csrf
                      <input type="hidden" name="call_data_id" hidden value="{{$call_data_id}}">
                      <input type="hidden" name="call_data_detail_id" hidden value="{{$call_data_details->id}}">

                        <div class="col-md-6">
                          <button type="submit" class="btn btn-primary btn-sm">CSD Enquiry</button>
                        </div>
                    </form>
                    @endif
                  @endif

                  @if($call_data_details->type != 3 && $call_data_details->type == 2)
                    @if(isset($csd_service_enquiry->SaleEnquiry) && $csd_service_enquiry->SaleEnquiry == 'Yes')
                    <div class="col-md-6">
                      <button type="button" class="btn btn-primary btn-sm" disabled>Sale Enquiry Created</button>
                    </div>
                    
                    @else
                    <form method="POST" action="/admin/call/data/view/saleEnquiry" id="sale_enquiry-form">
                      @csrf
                      <input type="hidden" name="call_data_id" hidden value="{{$call_data_id}}">
                      <input type="hidden" name="call_data_detail_id" hidden value="{{$call_data_details->id}}">

                        <div class="col-md-6">
                          <button type="submit" class="btn btn-primary btn-sm">Sale Enquiry</button>
                        </div>
                    </form>
                    @endif
                  @endif

                  @if($call_data_details->type != 3)
                  @if(isset($csd_service_enquiry->ServiceEnquiry) && $csd_service_enquiry->ServiceEnquiry >0)
                  <div class="col-md-6">
                    <button type="button" class="btn btn-warning btn-sm" disabled>Service Enquiry Created</button>
                  </div>
                  @else
                  <form method="POST" action="/admin/call/data/view/serviceEnquiry" id="service_enquiry-form">
                    @csrf
                    <input type="hidden" name="call_data_id" hidden value="{{$call_data_id}}">
                    <input type="hidden" name="call_data_detail_id" hidden value="{{$call_data_details->id}}">
                      <div class="col-md-6">
                        <button type="submit" class="btn btn-warning btn-sm">Service Enquiry</button>
                      </div>
                  </form>
                  
                  @endif
                  @endif
                </div>
              </div>  
              {{-- @endif --}}
              {{--@endif--}}
             
              
              @if(isset($call_data_details->id))
              @if($call_data_details->query_status != 'Closed' && $call_allow == 1 && $not_interested)
                <div class="row margin box-shadow" id="start_call-div">
                  <div class="col-md-4">
                    <a id="startcall" style="float:left;" class="btn btn-danger">Start Call</a>
                    <a id="stopcall" style="float:left;display: none;" class="btn btn-success" >Stop Call</a>
                  </div>
                  <div class="col-md-4">
                    <label id="calling_status" style="display: none" > </label>
                  </div>
                </div>
                <br>
                <div class="row box-shadow-primary margin">
                  <form action="/admin/call/data/updateCall" method="POST" id="callingForm">
                    @csrf
                    <input type="hidden" name="call_data_id" value="{{$call_data_id}}" hidden>
                    <input type="hidden" name="call_data_details_id" value="{{$call_data_details->id}}" hidden>
                    <input type="hidden" name="call_duration" id="call_duration" hidden>
                    <div class="modal-body">
                      @if(!empty($call_data_details->query_status))
                      <label> Query :- {{$call_data_details->query}} </label>
                      @endif
                        <div class="row">
                          <div class="col-md-6">
                              <label>Call Status<sup>*</sup></label>
                              <select id="call_status"  name="call_status" class="form-control select2">
                                <option value="">Select Call Status</option>
                                @foreach($call_status as $key => $val)
                                  <option value="{{$val['key_name']}}" {{(old('call_status') == $val['key_name']) ? 'selected' : ''}}> {{$val['value']}} </option>
                                @endforeach
                              </select>
                              {!! $errors->first('call_status', '<p class="help-block">:message</p>') !!}
                              <br>
                          </div>
                          <div class="col-md-6" id="nextcall_div">
                            <label>Next Call Date<sup>*</sup></label>
                            <input id="next_call_date" type="text" value="{{old('next_call_date')}}" name="next_call_date" class="input-css datepicker_with_disabled" autocomplete="off">
                            {{-- data-date-end-date="0d" --}}
                            {!! $errors->first('next_call_date', '<p class="help-block">:message</p>') !!}
                            <label class="error" id="nti_info"></label>
                            <br>
                        </div>
                          <div class="col-md-12">
                              <label>Remark <sup>*</sup></label>
                              <textArea id="remark" type="text" name="remark" class="form-control">{{old('remark')}}</textArea>
                              {!! $errors->first('remark', '<p class="help-block">:message</p>') !!}
                          </div>
                        </div>
                    </div>
                    <div class="col-md-12" style="padding-left:30px;">
                      <button type="submit" class="btn btn-success" id="call_form_submit">Submit</button>
                    </div>
                  </form>
                </div>
              @endif
              @endif
   
              <br>
                    <table id="table-pending" class="table table-bordered table-striped" style="text-align:center">
                        <thead>
                            <tr>
                              <th>Sr.No</th>
                              <th >{{__('last Call Date')}}</th>
                              <th >{{__('Call Satus')}}</th>
                              <th >{{__('Remark')}}</th>
                              <th >{{__('Call Duration')}}</th>
                              <th >{{__('Next Call Date')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(count($call_data_record) <= 0)
                              <tr>
                                <td colspan="5" >Not-Found Data</td>
                              </tr>
                            @else
                              <?php $i = 1;  ?>
                              @foreach($call_data_record as $key => $val)
                              <tr>
                                <td>{{$i}}</td>
                                <td>{{$val['call_date']}}</td>
                                <td>{{$val['call_status']}}</td>
                                <td>{{$val['remark']}}</td>
                                <td>{{gmdate("H:i:s", $val['call_duration'])}}</td>
                                <td>{{$val['next_call_date']}}</td>
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
                      <a href="/admin/call/data/nextcall?id={{$call_data_id}}&call_type={{$call_data_details->type}}" class="btn btn-success" style="float:right">Next Call</a>
                  </div>
                </div>
            </div>           
        </div>

          <!-- transaction Model -->
          <div class="modal fade" id="transaction_model" tabindex="-1" role="dialog" aria-labelledby="transaction_modelCenterTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered transaction-dialog" role="summary">
              <div class="modal-content" style="margin-top: 200px!important;">
                <div class="modal-header">
                  <h4 class="modal-title" id="transaction_modelLongTitle">Sale</h4>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body">
                  <div class="row">
                    <table class="table table-bordered table-striped" >
                        <thead>
                          <tr id="t_head_append">
                          </tr>
                        </thead>
                        <tbody id="t_body_append">
                          <tr ></tr>
                        </tbody>
                    </table>
                  </div><br>
                </div>
              </div>
            </div>
          </div>

          <!-- Modal --> 
          <div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
              <div class="modal-content" style="margin-top: 200px!important;">
                <div class="modal-header">
                  <h4 class="modal-title" id="exampleModalLongTitle">Service Booking</h4>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <form id="service_booking-form" action="/admin/call/data/serviceBooking" method="POST" >
                  <div class="modal-body">
                    <div class="alert alert-danger" id="model-error" style="display: none">

                    </div>
                    @csrf
                    <input type="hidden" name="call_data_id" value="{{$call_data_id}}" id="call_data_id">
                    <div class="row">
                      <div class="col-md-6">
                         <label>Name<sup>*</sup></label>
                         <input type="text" name="servicename" value="{{old('servicename')}}" id="servicename" class="input-css">
                         {!! $errors->first('name', '<p class="help-block">:message</p>') !!}
                      </div>
                      <div class="col-md-6">
                         <label>Mobile<sup>*</sup></label>
                          <input type="text" name="mobile" value="{{old('mobile')}}" id="mobile" class="input-css">
                         {!! $errors->first('mobile', '<p class="help-block">:message</p>') !!}
                      </div>
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="button" id="btnCancel" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" id="btnUpdate" class="btn btn-success">Submit</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
          <!-- Modal --> 
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
                  <form id="dnd-form" action="/admin/call/data/updateDnd" method="POST" >
                    <div class="alert alert-danger" id="dndmodel-error" style="display: none">
                    </div>
                    @csrf
                    <input type="hidden" name="call_data_id" value="{{$call_data_id}}" >
                    <div class="row">
                      <div class="col-md-6">
                        <input type="checkbox" name="dnd_option[]" @php if(in_array("Call", $dnd_type)) echo 'checked';  @endphp class=" dnd_option" value="Call">  Call
                      </div>
                      <div class="col-md-6">
                        <input type="checkbox" @php if(in_array("SMS", $dnd_type)) echo 'checked';  @endphp name="dnd_option[]" class=" dnd_option" value="SMS">  SMS
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

          <!-- Modal --> 
          <div class="modal fade" id="soldSearch" tabindex="-1" role="dialog" aria-labelledby="soldSearchTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
              <div class="modal-content" style="margin-top: 100px!important;">
                <div class="modal-header">
                  <h4 class="modal-title" id="soldSearchTitle">Check Sold</h4>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body">
                  <div class="alert alert-danger" id="soldSearch-error" style="display: none">
                  </div>
                  <div class="alert alert-success" id="soldSearch-success" style="display: none">
                  </div>
                  <form id="soldSearch-form" >
                    @csrf
                    <input type="hidden" name="call_data_id" value="{{$call_data_id}}" >
                    <div class="row">
                      <div class="col-md-6">
                        <label for="sold_name">Name <sup>*</sup></label>
                        <input type="text" name="sold_name" class="form-control sold_name sold_validation" >
                      </div>
                      <div class="col-md-6">
                        <label for="sold_mobile"> Mobile # <sup>*</sup></label>
                        <input type="number" name="sold_mobile" class="form-control sold_mobile sold_validation" >
                      </div>
                      <div class="col-md-6">
                        <label for="sold_aadhar">Aadhar # <sup>*</sup></label>
                        <input type="number" name="sold_aadhar" class="form-control sold_aadhar sold_validation" >
                      </div>
                    </div>
                    <div class="row margin-bottom" id="sold_search-append_div">
                    </div>
                </div>
                <div class="modal-footer">
                  <button type="button"  class="btn btn-secondary" data-dismiss="modal">Close</button>
                  <button type="button" id="sold_search-btn" class="btn btn-success">Search</button>
                </div>
                </form>
              </div>
            </div>
          </div>
    </section>
@endsection