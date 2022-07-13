@extends($layout)

@section('title', __('JP Honda'))
@section('css')
<link rel="stylesheet" href="/front/css/service.css">

@endsection
@section('js')
<script>
    $("#msg").hide();
    $("#details").hide();
     $("#button").click(function(){
        var frame = $("#frame").val();
        var registration = $("#registration").val();
        var tag = $("#tag").val();
        if (frame || tag || registration) {
            $("#error").hide();
        $.ajax({
            url:'/service/check/status',
            data:{'frame': frame,'tag':tag, 'registration':registration},
            method:'GET',
            success:function(result){
              if (Object.keys(result).length > 0) {
                 var str = '';
                 $("#msg").hide();
                $.each(result, function(key, value) {   
                         str += '<div class="col-md-12">';
                            str +='<div class="single-communication"><div class="communication-text col-md-4"><h4>Tag : <span id="tag" class="details">'+value.tag+'</span></h4></div><div class="communication-text col-md-4"><h4>Registration Number: <span id="plate_no" class=" details" >'+value.registration+'</span></h4></div><div class="communication-text col-md-4"><h4>Frame Number: <span id="frame" class=" details" >'+value.frame+'</span></h4></div></div>';

                            str += '<div class="single-communication"><div class="communication-text col-md-4"><h4>Customer Status : <span id="cust_status" class="details">'+value.customer_status+'</span></h4></div><div class="communication-text col-md-4"><h4>Jobcard Type : <span id="job_card_type" class="details">'+value.job_card_type+'</span></h4></div><div class="communication-text col-md-4"><h4>Estimate Service Duration(HH:MM) :  <span id="duration" class="details">'+value.service_duration+'</span></h4></div></div>';

                           

                            str +='<div class="single-communication"><div class="communication-text col-md-4"><h4>Estimate Delivery: <span id="delivery" class="details">'+value.estimated_delivery_time+'</span></h4></div><div class="communication-text col-md-4"><h4>Oil change (Infront of Customer):  <span id="oil_change"  class="details">'+value.oilinfornt_customer+'</span></h4></div><div class="communication-text col-md-4"><h4>Status :  <span id="oil_change"  class="details">'+value.status+'</span></h4></div></div>';
                           str += '<div class="single-communication"><div class="communication-text col-md-4"><h4>Customer: <span id="delivery" class="details">'+value.customer_name+'</span></h4></div><div class="communication-text col-md-4"><h4>Product :  <span id="oil_change"  class="details">'+value.product_name+'</span></h4></div><div class="communication-text col-md-4"><h4>Store :  <span id="oil_change"  class="details">'+value.store_name+'</span></h4></div></div>';

                           str +='</div>';

                            

                            str += '<div class="col-md-12 hr"></div>';

                    });
                       $("#details").show();
                       $("#details").html(str);  

              }else{
                $("#details").hide();
                $('#msg').html("Record not found.").show();
              }
              
            },
            error:function(error){
               $('#err').html("Something Wen't Wrong. ").show();
            }
        });
    }else{
        if (frame == '' || tag == '' || registration == '') {
           $("#details").hide();
            $("#error").html("Field is required.").show();
        }
        
    }
    });
</script>
@endsection

@section('main_section')
<div class="about-us-area overview-area pt-50">
   <div class="breadcrumb-area breadcrumb-area" >
                <div class="container-fluid">
                    <div class="breadcrumb-content text-center contactus_header">
                    <h2>{{__('front/service.title_status')}}</h2>
                 </div>
                </div>
            </div>
    <div class="container">
        <div class="row">
            <div class="col-lg-12 col-md-12">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="row">
                        <div class="col-md-12">
                           <div class="contact-form-style">
                            <div class="btn-div">
                                    <div class="row">
                                        <div class="col-md-3 checkout-form-list">
                                            <label>{{__('front/service.frame')}}</label>
                                           <input type="text" id="frame" name="frame" class="input-css" >
                                        </div>
                                        <div class=" col-md-3 checkout-form-list">
                                            <label>{{__('front/service.registration')}}</label>
                                           <input type="text" id="registration" name="registration" class="input-css" >
                                        </div>
                                        <div class=" col-md-3 checkout-form-list">
                                            <label>{{__('front/service.tag')}}</label>
                                           <input type="text" id="tag" name="tag" class="input-css" >
                                        </div>
                                          <div class=" col-md-3 checkout-form-list"><br>
                                              <button class="submit cr-btn btn-style" id="button" style="margin-top: 0px!important"><span>{{__('front/rc_status.status')}}</span></button>
                                          </div>
                                    </div><p class="help-block" id="error"></p>
                            </div>
                          </div>
                        </div>
                        <div class="col-md-12 pt-60">
                            <div class="table-content">
                              <div id="rc_table">
                                <div class="grid-list-product-wrapper tab-content">
                                    <div id="new-product" class="product-grid product-view tab-pane active">
                                         <span id="msg" style="background:#f7f7f7;padding:10px;margin-top:20px;color:red"></span> 
                                        <div class="row communication-info"  id="details">
                                             
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