@extends($layout)

@section('title', __('JP Honda'))
@section('css')
 <link rel="stylesheet" href="/front/css/service.css">

@endsection
@section('js')
<script>
    $(document).ready(function () {
        RCStatus();
    });

    $("#rc_btn").click(function(){
        RCStatus();
    });

    function RCStatus() {
        $("#rc_table").show();
        $("#rc_details").hide();
    }

    $("#rc_btn").click(function(){
        var frame = $("#frame").val();
        var application_number = $("#application_number").val();
        if (frame || application_number) {
            $("#frame_error").hide();
        $.ajax({
            url:'/check/rc/status',
            data:{'frame': frame,'application_number':application_number},
            method:'GET',
            success:function(result){
              if (Object.keys(result).length > 0) {
                $("#err").hide();
                $("#rc_details").show();
                if (result.main_type == 'rto') {
                    $("#sale").show();
                    $("#sale_no").html(result.sale_no);
                }else{
                    $("#sale").hide();
                }
                $("#rc_no").html(result.rc_number)
                $("#app_no").html(result.application_number)
                $("#plate_no").html(result.registration_number)
                $("#front_lid").html(result.front_lid)
                $("#rear_lid").html(result.rear_lid)
                if (result.approve == '1' && result.rc_number != null) {
                    $("#status").html(' Received At '+result.store_name);
                }else if(result.approve == '1' && result.rc_number == null){
                    $("#status").html('File Sent To RTO');
                }else{
                   $("#status").html('Pending'); 
                }
                
              }else{
                $("#rc_details").hide();
                $("#err").html("Record not found.").show();
              }
              
            },
            error:function(error){
               $('#err').html("Something Wen't Wrong. ").show();
            }
        });
    }else{
        if (frame == '' || application_number == '') {
            $("#frame_error").html("Field is required.").show();
        }
        
    }
    });
</script>
@endsection

@section('main_section')
<div class="about-us-area overview-area pt-30">
     <div class="breadcrumb-area breadcrumb-area" >
                <div class="container-fluid">
                    <div class="breadcrumb-content text-center contactus_header">
                    <h2>{{__('front/rc_status.rc_status_plate_number')}}</h2>
                 </div>
                </div>
            </div>
    <div class="container">
        <div class="row">
            <div class="col-lg-12 col-md-12">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 top">
                    <div class="row">
                        <div class="col-md-6">
                           <div class="contact-form-style">
                            <div class="btn-div">
                                <div class="col-md-12">
                                    <div class="row">
                                        <div class="checkout-form-list">
                                            <label>{{__('front/rc_status.frame_number')}}</label>
                                           <input type="text" id="frame" name="frame" class="input-css" >
                                           <p class="help-block" id="frame_error"></p>
                                        </div>&nbsp;&nbsp;
                                        <div class="checkout-form-list">
                                            <label>{{__('front/rc_status.application_number')}}</label>
                                           <input type="text" id="application_number" name="application_number" class="input-css" >
                                           <p class="help-block" id="appNo_error"></p>
                                        </div>
                                           
                                    </div><p class="help-block" id="err"></p>
                                    <div class="row">
                                        <button class="submit cr-btn btn-style" id="rc_btn" style="margin-top: 0px!important"><span>{{__('front/rc_status.status')}}</span></button>
                                    </div>
                                </div>
                            </div>
                          </div>
                        </div>
                        <div class="col-md-6">
                            <div class="table-content">
                              <div id="rc_table">
                                <div class="grid-list-product-wrapper tab-content">
                                    <div id="new-product" class="product-grid product-view tab-pane active">
                                            <div class="row communication-info main-div"  id="rc_details">
                                                <div class="col-md-12 ">
                                                <div class="single-communication">
                                                    <div class="communication-text col-md-6" id="sale">
                                                        <h4>Sale Number: <span id="sale_no" class="details"></span></h4>
                                                    </div>
                                                    <div class="communication-text col-md-6">
                                                        <h4>Registration Number: <span id="plate_no" class=" details" ></span></h4>
                                                    </div>

                                                </div>
                                                <div class="single-communication">
                                                    <div class="communication-text col-md-6">
                                                        <h4>RC Number: <span id="rc_no" class="details"></span></h4>
                                                    </div>
                                                     <div class="communication-text col-md-6">
                                                        <h4>Application Number: <span id="app_no" class="details"></span></h4>
                                                    </div>
                                                </div>
                                                <div class="single-communication">
                                                    <div class="communication-text col-md-6">
                                                        <h4>Front LID: <span id="front_lid" class="details"></span></h4>
                                                    </div><div class="communication-text col-md-6">
                                                        <h4>Rear LID:  <span id="rear_lid" class="details"></span></h4>
                                                    </div>
                                                </div>
                                                <div class="single-communication">
                                                    <div class="communication-text col-md-6">
                                                        <h4>Status: <span id="status" class="details"></span></h4>
                                                    </div>
                                                </div>
                                            </div>
                                          </div>
                                    </div>
                                </div>
                              </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
           
        </div>
    </div>
</div>
@endsection