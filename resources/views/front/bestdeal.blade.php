@extends($layout)

@section('title', __('Best Deal'))

@section('breadcrumb')
    <li><a href="#"><i class=""></i>{{__('Best Deal')}}</a></li>
@endsection
@section('css')
<link rel="stylesheet" href="/css/style.css">
 <link rel="stylesheet" href="/front/css/jquery-ui.css">
@endsection
@section('js')
<script>
    var dataTable;
    $('#js-msg-error').hide();
    $('#js-msg-success').hide();
    $('#js-msg-errors').hide();
    $('#js-msg-successs').hide();
    
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
$("#mobile").inputFilter(function(value) {
  return /^-?\d*$/.test(value); });

$(document).on('click','#mobilebutton',function(){
    
    var mobile=$("#mobile").val();
     $('#js-msg-errors').hide();
     $('#js-msg-successs').hide();
     intRegex = /^[789]\d{9}$/;

     if(mobile=='') {
        $('#js-msg-errors').html("Mobile No is required").show(); 
         return false;

     } else if((mobile.length < 10)) {
        $('#js-msg-errors').html("Mobile No may not be greater than 10 digits.").show(); 
             return false;
    }else if(!intRegex.test(mobile)){
         $('#js-msg-errors').html("Mobile No is invalid.").show(); 
             return false;
    } else {
        $.ajax({
            url:'/bestdealinventory/otp',
            data:{'mobile':mobile},
            method:'GET',
            success:function(result){
               if(result.type == 'success') {
                  $('#js-msg-successs').html(result.msg).show().delay(1000).fadeOut();
                  $('.mobile_div').hide();
                  $('#mobilebutton').hide();
                  $('.otp_div').show();
                  $('.otpbutton').show();
               } else if(result.type == 'error') {
                  $('#js-msg-errors').html(result.msg).show();
                  $('.mobile_div').show();
                  $('.otp_div').hide();
               } else{
                  $('#js-msg-errors').html("Something Wen't Wrong").show();
               }
            },
            error:function(error){
               $('#js-msg-errors').html("Something Wen't Wrong "+error).show();
            }
         });
      }

   });

$(document).on('click','.otpbutton',function(){
    var otp = $("#otp").val();
    var mobile = $("#mobile").val();
    var id = $("#ids").val();
     $('#js-msg-errors').hide();
     $('#js-msg-successs').hide();
      if(otp) {
        $.ajax({
            url:'/bestdealinventory/otp_match',
            data:{'otp':otp,'mobile':mobile,'id':id},
            method:'GET',
            success:function(result){
               if(result.type == 'success')
               {
                  // $('#myModal_comp_done').modal("hide");
                  $('#price').text(result.price);
                  $('#js-msg-successs').html('The price is “Negotiable”.').show();
                  $('.price_div').show();
                  $('.mobile_div').hide();
                  $('#mobilebutton').hide();
                  $('.otp_div').hide();
                  $('.otpbutton').hide();
               }
               else if(result.type ==  'error')
               {
                  
                  $('#js-msg-errors').html(result.msg).show();
                  // $('.mobile_div').show();
                  // $('.otp_div').hide();
               }
               else{
                  $('#js-msg-errors').html("Something Wen't Wrong").show();
               }
            },
            error:function(error){
               $('#js-msg-errors').html("Something Wen't Wrong "+error).show();
            }
         });
      }else{
        $('#js-msg-errors').html("OTP is required").show(); 
      }

   });

//Filter

$(document).ready(function() {
    $(document).on('click', '.pagination a', function(event){
      event.preventDefault(); 
      var page = $(this).attr('href').split('page=')[1];
      fetch_data(page);
    });
});


function fetch_data(page) {
  var model= $("#model_name").children("option:selected").val();
  var variant= $("#model_variant").children("option:selected").val();
  var color= $("#model_color").children("option:selected").val();

    $.ajax({
       url:"/bestdeal/fetch/data?page="+page, 
       data: {   
       model:model,
       variant:variant,
       color:color
    }, 
     success:function(data)
     {
      $('#table_data').html(data);
     }
    });
 }

  $('#model_name').on( 'change', function () {
      var model_name= $("#model_name").children("option:selected").val();
      var model_variant= $("#model_variant").children("option:selected").val();
      var model_color= $("#model_color").children("option:selected").val();
       all_filter_data(model_name,model_variant,model_color);
         
  });

  $('#model_variant').on('change', function (){
     var model_name= $("#model_name").children("option:selected").val();
     var model_variant= $("#model_variant").children("option:selected").val();
     var model_color= $("#model_color").children("option:selected").val();
       all_filter_data(model_name,model_variant,model_color);
  });

  $('#model_color').on( 'change', function () {
     var model_name= $("#model_name").children("option:selected").val();
     var model_variant= $("#model_variant").children("option:selected").val();
     var model_color= $("#model_color").children("option:selected").val();
       all_filter_data(model_name,model_variant,model_color);
    
  });
  

  function all_filter_data(model,variant,color){
    $.ajax({
       url:"/bestdeal/filter/data",
       data: { 
       model:model,
       variant:variant,  
       color:color
      }, 
       success:function(data)
       {
        $('#table_data').html(data);
       }
      });

  }

function function_api_call(i) {

  var id = $(i).attr('data-id');
      $.ajax({
         url:"/bestdeal/get/details",
         data: { 'id':id
        },success:function(data) {

            $(".modal_image div").removeClass('active show fade');
            $(".small_image").removeClass('active');

          if (data.front == null) {
              $("#front_img").attr('src','/front/img/product/product-default.jpg');
              $("#img1").attr('src','/front/img/product/product-default.jpg');
          }else{
              $("#front_img").attr('src','/upload/bestdeal/'+data.front.image);
              $("#img1").attr('src','/upload/bestdeal/'+data.front.image);
          }
          if (data.back == null) {
              $("#back_img").attr('src','/front/img/product/product-default.jpg');
              $("#img2").attr('src','/front/img/product/product-default.jpg');
          }else{
              $("#back_img").attr('src','/upload/bestdeal/'+data.back.image);
              $("#img2").attr('src','/upload/bestdeal/'+data.back.image);
          }
          if (data.left == null) {
              $("#left_img").attr('src','/front/img/product/product-default.jpg');
              $("#img3").attr('src','/front/img/product/product-default.jpg');
          }else{
              $("#left_img").attr('src','/upload/bestdeal/'+data.left.image);
              $("#img3").attr('src','/upload/bestdeal/'+data.left.image);
          }
          if (data.right == null) {
              $("#right_img").attr('src','/front/img/product/product-default.jpg');
              $("#img4").attr('src','/front/img/product/product-default.jpg');
          }else{
              $("#right_img").attr('src','/upload/bestdeal/'+data.right.image);
              $("#img4").attr('src','/upload/bestdeal/'+data.right.image);
          }
            $("#view_model").html(data.details.model);
            $("#view_variant").html(data.details.variant);
            $("#view_color").html(data.details.color);
            $("#view_frame").html('Frame Number : '+data.details.frame);
            $("#reg_no").html('Registreation Number : '+data.details.register_no);
            $("#dos").html('RC Date of Sale : '+data.details.dos);
            $("#add").html('Address : '+data.details.address);

            $(".modal_image div:first").addClass('active show fade');
            $(".small_image a").removeClass('active');
            $(".small_image a:first").addClass('active');

            $('#front_img').error(function() {
              $("#front_img").attr('src','/front/img/product/product-default.jpg');
            });
            $('#img1').error(function() {
              $("#img1").attr('src','/front/img/product/product-default.jpg');
            });

            $('#back_img').error(function() {
              $("#back_img").attr('src','/front/img/product/product-default.jpg');
            });
            $('#img2').error(function() {
              $("#img2").attr('src','/front/img/product/product-default.jpg');
            });

            $('#left_img').error(function() {
              $("#left_img").attr('src','/front/img/product/product-default.jpg');
            });
            $('#img3').error(function() {
              $("#img3").attr('src','/front/img/product/product-default.jpg');
            });

            $('#right_img').error(function() {
              $("#right_img").attr('src','/front/img/product/product-default.jpg');
            });
            $('#img4').error(function() {
              $("#img4").attr('src','/front/img/product/product-default.jpg');
            });


            $('#bestdealModal').modal('show');
         }
      });  
}

function function_getprice(i) {

  var best_deal_id = $(i).attr('data-id');
      var selling_price=$(i).attr('data-selling_price');
      $('#js-msg-errors').hide();
      $('#js-msg-successs').hide();
      $('.mobile_div').show();
      $('#mobilebutton').show();
      $('.otp_div').hide();
      $('.price_div').hide();
      $('.otpbutton').hide();
      $("#mobile").val('');
      $("#otp").val('');
      $("#ids").val(best_deal_id);
   
      $('#myModal_comp_nip').modal('show');

}

  </script>
@endsection

@section('main_section')

<section class="content">
      <div class="container-fluid breadcrumb-area pt-120">
              <div class="breadcrumb-content text-center contactus_header">
                    <h2>{{__('front/front.bestdeal')}}</h2>
                </div>
            </div>

  <div class="container">            
      <div id="app" style="margin-top: 50px;margin-bottom:50px;">
              @include('admin.flash-message')
              @yield('content')
        <!-- Default box -->
            
      <div class="box box-primary">
      <!-- /.box-header -->
      <div class="grid-list-product-wrapper tab-content">
        <div class="row">
          <div class="col-md-12">
          <div class="shop-topbar-wrapper">
            <div class="product-sorting">
                <div class="col-md-4">
                  <div class="sorting sorting-bg-1">
                      <select class="input-css select2 selectValidation" name="model_name"  id="model_name">
                         <option value="">Select Model Name</option>
                         @foreach($model as $key => $val)
                         <option value="{{$val['model_name']}}" {{(old('model_name') == $val['model_name']) ? 'selected' : '' }}> {{$val['model_name']}} </option>
                         @endforeach
                      </select>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="sorting sorting-bg-1">
                    <select class="input-css select2 selectValidation" name="model_variant"  id="model_variant">
                       <option value="">Select Model Variant</option>
                       @foreach($variant as $key => $val)
                       <option value="{{$val['model_variant']}}" {{(old('model_variant') == $val['model_variant']) ? 'selected' : '' }}> {{$val['model_variant']}} </option>
                       @endforeach
                    </select>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="sorting sorting-bg-1">
                    <select class="input-css select2 selectValidation" name="model_color"  id="model_color">
                       <option value="">Select Color</option>
                       @foreach($color as $key => $val)
                       <option value="{{$val['color_code']}}" {{(old('color_code') == $val['color_code']) ? 'selected' : '' }}> {{$val['color_code']}} </option>
                       @endforeach
                    </select>
                  </div>
                </div>
            </div>
          </div>
        </div>
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="table-content ">
                  <div id="table_data">
                    <div class="grid-list-product-wrapper tab-content">
                        <div id="new-product" class="product-grid product-view tab-pane active">
                           @include('front.bestdeal_product')
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


      <!-- Modal-->
       
     <div id="myModal_comp_nip" class="modal fade" role="dialog">
        <div class="modal-dialog modal-sm">
        
              <!-- Modal content-->
              <div class="modal-content">
                  <div class="modal-header">
                      <h4 class="modal-titles">Get Price</h4>
                      <button type="button" class="close" data-dismiss="modal">&times;</button>
                  </div>
                  <div class="modal-body otp-body">
                       <div class="alert alert-danger" id="js-msg-errors"></div>
                       <div class="alert alert-success" id="js-msg-successs"></div>
                       <input type="text" name="id" id="ids" hidden>
                        <div class="row price_div">
                          <div class="col-md-12">
                              <label for="">Suggested Price</label><br/>
                              <span id="price"></span>
                          </div>
                        </div>
                        <div class="row mobile_div">
                          <div class="col-md-12">
                            <div class="checkout-form-list">
                              <label for="">Mobile No<sup>*</sup></label>
                              <input type="text" placeholder="Enter Your Mobile No" name="mobile" id="mobile" maxlength="10" pattern="[1-9]{1}[0-9]{9}" class="input-css">
                            </div>
                          </div>
                        </div>
                        <div class="row otp_div">
                          <div class="col-md-12">
                            <div class="checkout-form-list">
                              <label for="">OTP<sup>*</sup></label>
                              <input type="text" placeholder="Enter Otp" name="otp" id="otp" class="input-css">
                            </div>
                          </div>
                        </div>
                       
                        <div class="modal-footer">
                            <button type="button" id="mobilebutton" class="btn-style cr-btn">Submit</button>&nbsp;&nbsp;
                            <button type="button" id="otpbutton" class="otpbutton btn-style cr-btn">Submit</button>
                           &nbsp;&nbsp;
                            <button type="button" class="btn-style cr-btn" data-dismiss="modal">Close</button>
                        </div>
                  </div>
              </div>
          </div>
       </div>

          <div class="modal fade product_modals" id="bestdealModal" tabindex="-1" role="dialog" aria-hidden="true">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span class="icofont icofont-close" aria-hidden="true"></span>
            </button>
            <div class="modal-dialog" role="document">
                <div class="modal-content">

                    <div class="modal-body">
                      
                        <div class="qwick-view-left">
                            <div class="quick-view-learg-img">
                                <div class="quick-view-tab-content tab-content modal_image">
                                    <div class="tab-pane active show fade" id="modal1" role="tabpanel">
                                       <img src="" id="front_img" style="max-width: 300px;max-height:250px">
                                    </div>
                                    <div class="tab-pane fade" id="modal2" role="tabpanel">
                                       <img src="" id="back_img" style="max-width: 300px;max-height:250px">
                                    </div>
                                    <div class="tab-pane fade" id="modal3" role="tabpanel">
                                       <img src="" id="left_img" style="max-width: 300px;max-height:250px">
                                    </div>
                                    <div class="tab-pane fade" id="modal4" role="tabpanel">
                                       <img src="" id="right_img" style="max-width: 300px;max-height:250px">
                                    </div>
                                </div>
                            </div>
                            <div class="quick-view-list nav small_image" role="tablist">
                                <a class="active" href="#modal1" data-toggle="tab" role="tab">
                                    <img src="" alt="" id="img1" style="height: 70px;width: 70px;">
                                </a>
                                <a href="#modal2" data-toggle="tab" role="tab">
                                    <img src="" alt="" id="img2" style="height: 70px;width: 70px;">
                                </a>
                                <a href="#modal3" data-toggle="tab" role="tab">
                                    <img src="" alt="" id="img3" style="height: 70px;width: 70px;">
                                </a>
                                <a href="#modal4" data-toggle="tab" role="tab">
                                    <img src="" alt="" id="img4" style="height: 70px;width: 70px;">
                                </a>
                            </div>
                        </div>
                       
                        <div class="qwick-view-right">
                                <div class="qwick-view-content">
                                    <h3 id="view_model"></h3>
                                    <div class="price">
                                        <span class="new" id="view_variant"></span>
                                        <span class="price" id="view_color"></span>
                                    </div>
                                    <div class="rating-number">
                                        <div class="quick-view-rating">
                                            <span id="view_frame"></span> , <span id="add"></span>
                                            <span id="reg_no"></span><br>
                                            <span id="dos"> </span><br>
                                            
                                        </div>
                                    </div>
                                </div>
                            </div>
                    </div>
                </div>
            </div>
        </div>


</section>
@endsection