@extends($layout)

@section('title', __('JP Honda'))

@section('css')
@endsection

@section('js')
<script>
$(document).ready(function() {
       $('#home4').hide(); 
});



 $(document).on('click', '#new_bike', function(){
     $('#home3').addClass('active');
     $('#home4').removeClass('active');
       $('#home3').show();
       $('#home4').hide();

  });
 $(document).on('click', '#used_bike', function(){
     $('#home4').addClass('active');
     $('#home3').removeClass('active');

       $('#home3').hide();
       $('#home4').show();
  });

$(".details_model").on('click',function(){
      var id = $(this).attr('data-id');
      var model = $(this).attr('data-model');
      var variant = $(this).attr('data-variant');
      var color = $(this).attr('data-color');
      var frame = $(this).attr('data-frame');
      var engine_number = $(this).attr('data-engine_number');

        
      $.ajax({
         url:"/newproduct/get/details",
         data: { 'id':id
        },success:function(data) {
            var str='';var str1='';
            var i=1; 

         if(data.length > 0){  

                 $.each(data,function(key,value){
                  

                   str+= "<a href='#modal"+i+"' data-toggle='tab' role='tab' >"+
                      "<img src='/upload/product/resized/"+value.image+"' class='small_images' id='smallthumbimg_"+i+"' alt='Image' hight='70' width='70'/>"+
                    "</a>";

                    str1+="<div class='tab-pane fade' id='modal"+i+"' role='tabpanel'>"+
                      "<img src='/upload/product/"+value.image+"' class='big_images' id='bigthumbimg_"+i+"'   alt='demo' style='max-width: 300px;'/>"+
                   "</div>";
                  
                    i++;
                  });  

        }else{
            str+= "<a href='#modal"+i+"' data-toggle='tab' role='tab' >"+
                      "<img src='/front/img/product/product-default.jpg' class='small_images' id='smallthumbimg_"+i+"'  alt='Image' hight='70' width='70'/>"+
                    "</a>";
            str1+="<div class='tab-pane fade' id='modal"+i+"' role='tabpanel'>"+
                      "<img src='/front/img/product/product-default.jpg'  class='big_images' id='bigthumbimg_"+i+"'  alt='demo' style='max-width: 300px;'/>"+
                   "</div>";
        }
            $(".small_img").html(str);
            $(".modal_newimage").html(str1);
            $(".modal_newimage div:first").addClass('active show fade');
            $(".small_img a:first").addClass('active');
    
            $("#view_model").html(model);
            $("#view_variant").html(variant);
            $("#view_color").html(color);
            $("#view_frame").html('Frame Number : '+frame);
            $("#engine_number").html('Engine Number : '+engine_number);

            $(".small_images").each(function(e){
                 var smallid=$(this).attr('id');
                 $("#"+smallid).error(function() {
                     $("#"+smallid).attr('src','/front/img/product/product-default.jpg');      
                });
            });

            $(".big_images").each(function(j){
                 var bigid=$(this).attr('id');
                 $("#"+bigid).error(function() {
                     $("#"+bigid).attr('src','/front/img/product/product-default.jpg');      
                });
            });
            
         }
      });
  });


$('#subscribeForm').submit(function(e) {
    e.preventDefault();

    $("#loader").show();
    var formData = new FormData(this);
    // $('#subscribeForm').find('span').remove();
    $('#subscribeForm').next('span').remove();
    $('#subscribe-error').hide();
    $('#subscribe-success').hide();

    $.ajax({
        type: 'POST',
        url: "/web/subscribeEmail",
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        success: (data) => {
            // console.log(data);
            if (data.length > 0) {
                $('#subscribe-error').hide();
                $('#subscribe-success').text(data[1]).show();
                $('#subscribe_email').val('');
            }
        },
        error: function(error) {
            // console.log(error.responseJSON);
            if ((error.responseJSON).hasOwnProperty('message')) {
                if (error.responseJSON.errors) {
                    draw_errors(error.responseJSON.errors, 'subscribeForm');
                } else if (error.responseJSON) {
                    draw_errors(error.responseJSON, 'subscribeForm');
                }
            } else {
                $('#subscribe-error').html(error.responseJSON).show();
            }
             $('#subscribe-success').hide();
            $("#loader").hide();
        }
    }).done(function() {
        $("#loader").hide();
    });
});

function draw_errors(arr, form_id) {
    $('#subscribeForm').next('span').remove();
    $.each(arr, function(key, val) {
         $("<span style='color:red;'>" + val[0] + "</span>").insertAfter('#subscribeForm');
    });
}


   </script>
@endsection

@section('main_section')
<div class="slider-area">
    <div class="slider-active owl-carousel">
        <div class="single-slider slider-1" >
            <div class="container">
                <div class="slider-content slider-animated-1">
                    <div class="slider-img text-center">
                        <img class="animated" src="/front/img/slider/Shine.jpg"  alt="slider images">
                    </div>
                </div>
            </div>
        </div>
        <div class="single-slider slider-1" >
            <div class="container">
                <div class="slider-content slider-animated-1">
                    <div class="slider-img text-center">
                        <img class="animated" src="/front/img/slider/Hornet.jpg"  alt="slider images">
                    </div>
                </div>
            </div>
        </div>
        <div class="single-slider slider-1" >
            <div class="container">
                <div class="slider-content slider-animated-1">
                    <div class="slider-img text-center">
                        <img class="animated" src="/front/img/slider/Activa 6G.jpg"  alt="slider images">
                    </div>
                </div>
            </div>
        </div>
        <div class="single-slider slider-1" >
            <div class="container">
                <div class="slider-content slider-animated-1">
                    <div class="slider-img text-center">
                        <img class="animated" src="/front/img/slider/Activa 125.jpg"  alt="slider images">
                    </div>
                </div>
            </div>
        </div>
        <div class="single-slider slider-1" >
            <div class="container">
                <div class="slider-content slider-animated-1">
                    <div class="slider-img text-center">
                        <img class="animated" src="/front/img/slider/SP 125.jpg"  alt="slider images">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="slider-social">
        <ul>
            <li class="facebook"><a target="_blank" href="https://www.facebook.com/JPhondalucknow"><i class="icofont icofont-social-facebook"></i></a></li>
            <li class="twitter"><a target="_blank"  href="https://twitter.com/jphonda_lko"><i class="icofont icofont-social-twitter"></i></a></li>
            <li class="instagram"><a target="_blank"  href="https://www.instagram.com/jpmotors_honda/"><i class="icofont icofont-social-instagram"></i></a></li>
        </ul>
    </div>
</div>
<div class="overview-area pt-30">
    <div class="container">
        <div class="row">
            <div class="col-lg-6 col-md-12">
                <div class="overview-content">
                    <h1><span>JPHonda</span> </h1>
                    <h2>{{__('front/front.largest')}} <span>{{__('front/front.motorcycle_store_in_lucknow')}}</span></h2>
                    <p><span>JPHonda</span>{{__('front/front.most_latgest_bike_store')}}</p>
                    <p>{{__('front/front.best_after_sale_service')}}</p>
                    <div class="question-area">
                        <h4>{{__('front/front.have_any_question')}}</h4>
                        <div class="question-contact">
                            <div class="question-icon">
                                <i class="icofont icofont-phone"></i>
                            </div>
                            <div class="question-content-number">
                                <h6> <a href="tel:7317000466">7317000466</a></h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-md-12">
                <div class="overview-img">
                    <img class="tilter" src="/front/img/banner/banner-1.png" alt="">
                </div>
            </div>
        </div>
    </div>
</div>
<div class="product-area pb-30">
    <div class="container">
        <div class="section-title text-center mb-50">
            <h2>{{__('front/front.choose_your_bike')}}</h2>
            <p><span>JPHonda</span> {{__('front/front.most_latgest_bike_sell')}}</p>
        </div>
        <div class="product-tab-list text-center mb-80 nav product-menu-mrg" role="tablist">
            <a class="active" id="new_bike" href="#home1" data-toggle="tab" >
                <h4>{{__('front/front.new_bike')}}</h4>
            </a>
            <a href="#home2" id="used_bike"data-toggle="tab">
                <h4> {{__('front/front.used_bike')}} </h4>
            </a>
        </div>
        <div class="tab-content jump">
           
              <div class="tab-pane active" id="home3">
                      @if(isset($product))
                        @if($product->count()>0)      
                 <div class="product-slider-active owl-carousel">
                        @foreach($product as $row)
                        <div class="product-wrapper-bundle">
                        <div class="product-wrapper">
                           <div class="product-img image_list">
                             <a href="#">

                               @if($row->image != "" || $row->image != null)
                                     @if (file_exists(public_path().'/upload/product/resized/'.$row->image )) 
                                        <img src="{{ asset('upload/product/resized/')}}/{{$row->image}}"  id="product_{{$row->product_ids}}" alt="">
                                     @else
                                        <img src="/front/img/product/product-default.jpg" id="product_{{$row->product_ids}}"  alt="">
                                     @endif

                                @elseif($row->product_notdefault_image != "" || $row->product_notdefault_image != null)
                                    
                                     @if (file_exists(public_path().'/upload/product/resized/'.$row->product_notdefault_image )) 
                                        <img src="{{ asset('upload/product/resized/')}}/{{$row->product_notdefault_image}}" id="product_{{$row->product_ids}}" alt="">
                                     @else
                                        <img src="/front/img/product/product-default.jpg" id="product_{{$row->product_ids}}"  alt="">
                                     @endif

                                @else
                                <img src="/front/img/product/product-default.jpg"  id="product_{{$row->product_ids}}"  alt="">
                                @endif

                                                    </a>
                                                    <div class="product-item-dec">
                                                        <ul>
                                                            <li>{{ $row->model_category }}</li>
                                                        </ul>
                                                       @if($row->quantity>0) 
                                                       <span id="available">Available</span>
                                                       @endif
                                                    </div><div class="row">
                                                    </div> 
                                                    <div class="product-action">
                                                         <a class="action-reload details_model"  data-id="{{$row->product_id}}" data-model="{{ $row->model_name }}" data-variant="{{ $row->model_variant }}" data-color="{{ $row->color_code }}" data-frame="{{ $row->frame }}"  data-engine_number="{{ $row-> engine_number}}"  title="Quick View" data-toggle="modal" data-target="#NewBikeModal" href="#">
                                                        <i class=" ti-zoom-in"></i>
                                                    </a>
                                                      
                                                    </div>
                                                    <div class="product-content-wrapper">
                                                        <div class="product-title-spreed">
                                                            <h4>{{ $row->model_name }}</h4>
                                                            <span>{{ $row->model_variant }}</span>
                                                        </div>
                                                        <div class="product-price">
                                                            <span>{{ $row->color_code }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                          </div>
                        @endforeach
                        
                    </div>
                     @else
                        <span>No data available </span>
                         @endif
                    @endif
             </div>
            
            <div class="tab-pane" id="home4">
                 @if(isset($used_product))
                    @if($used_product->count()>0)     
              <div class="product-slider-active owl-carousel">
                 @foreach($used_product as $row)
                <div class="product-wrapper-bundle">
                        <div class="product-wrapper">
                            <div class="product-img image_list">
                              <a href="#">
                                @if($row->image != "" || $row->image != null)
                                @if (file_exists(public_path().'/upload/product/resized/'.$row->image )) 
                                            <img src="{{ asset('upload/product/resized/')}}/{{$row->image}}" alt="" data-id="{{$row->product_ids}}" data-default_image="{{$row->default_image}}" class="products_{{$row->product_id}}">
                                             @else
                                              <img src="/front/img/product/product-default.jpg" alt="" data-id="{{$row->product_ids}}" data-default_image="{{$row->default_image}}" class="products_{{$row->product_id}}">
                                               @endif
                                                @else
                                              <img src="/front/img/product/product-default.jpg" alt="" data-id="{{$row->product_ids}}" data-default_image="{{$row->default_image}}" class="products_{{$row->product_id}}">
                                               @endif
                                                    </a>
                                                    <div class="product-item-dec">
                                                        <ul>
                                                            <li>{{ $row->model_category }}</li>
                                                        </ul>
                                                       @if($row->quantity>0) 
                                                       <span id="available">Available</span>
                                                       @endif
                                                    </div><div class="row">
                                                    </div> 
                                                    <div class="product-action">
                                                         <a class="action-reload details_model"  data-id="{{$row->product_id}}" data-model="{{ $row->model_name }}" data-variant="{{ $row->model_variant }}" data-color="{{ $row->color_code }}" data-frame="{{ $row->frame }}"  data-engine_number="{{ $row-> engine_number}}"  title="Quick View" data-toggle="modal" data-target="#NewBikeModal" href="#">
                                                        <i class=" ti-zoom-in"></i>
                                                    </a>
                                                      
                                                    </div>
                                                    <div class="product-content-wrapper">
                                                        <div class="product-title-spreed">
                                                            <h4>{{ $row->model_name }}</h4>
                                                            <span>{{ $row->model_variant }}</span>
                                                        </div>
                                                        <div class="product-price">
                                                            <span>{{ $row->color_code }}</span>
                                                        </div>
                                                    </div>
                            </div>
                        </div>

                    </div>
                     @endforeach
                </div>
                   @else
                    <span>No data available </span> 
                       @endif
                    @endif
            </div>

        </div>
    </div>
</div>
<div class="latest-product-area pt-40 pb-40 bg-img" style="background-image: url(/front/img/banner/banner-4.jpg)">
    <div class="container-fluid">
        @if(isset($scheme))
            @if($scheme->count()>0)   
        <div class="latest-product-slider owl-carousel">
             @foreach($scheme as $row)
            <div class="single-latest-product slider-animated-2">
                <div class="row">
                    <div class="col-lg-7 col-md-12 col-12">
                        <div class="latest-product-img">
                            @if($row->image != "" || $row->image != null)
                                @if (file_exists(public_path().'/upload/scheme/'.$row->image )) 
                                    <img class="animated" src="{{ asset('upload/scheme/')}}/{{$row->image}}" alt="">
                                             @else
                                        <img class="animated" src="/front/img/banner/banner-2.png" alt="">
                                               @endif
                                                @else
                                        <img class="animated" src="/front/img/banner/banner-2.png" alt="">
                                               @endif
                        </div>
                    </div>
                    <div class="col-lg-5 col-md-12 col-12">
                        <div class="latest-product-content">
                            <h2 class="animated">LATEST OFFER <br>{{$row->name}}</h2>
                            <p class="animated">{{$row->description}}</p>
                            <div class="latest-price">
                                <h3 class="animated">Discount <span>&#8377;{{$row->amount}}</span></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
            <span>No data available </span>
             @endif
        @endif
    </div>
</div>
<div class="testimonial-area pt-50">
    <div class="container">
        <div class="section-title-2 section-title-position pt-30">
            <h2>{{__('front/front.our_clients_review')}} </h2>
        </div>
        <div class="testimonial-active owl-carousel">
            <div class="single-testimonial">
                <div class="row">
                    <div class="col-lg-5">
                        <div class="testimonial-img pl-75">
                            <img alt="image" src="/front/img/team/testimonial-1.jpg">
                        </div>
                    </div>
                    <div class="col-lg-7">
                        <div class="testimonial-content">
                            <div class="testimonial-dec">
                                <p><span>OSWAN</span> the most latgest bike store in the wold can serve you latest  qulity of motorcycle also you can sell here your motorcycle it quo minus iduod maxie placeat facere possimus, omnis voluptas assumenda est, omnis dolor llendus. Temporibus autem quibusdam quoten</p>
                            </div>
                            <div class="name-designation">
                                <h4>Rayed Ayash Hisham</h4>
                                <span>COO, ASEKHA</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="single-testimonial">
                <div class="row">
                    <div class="col-lg-5 col-md-12 col-12">
                        <div class="testimonial-img pl-75">
                            <img alt="image" src="/front/img/team/testimonial-2.png">
                        </div>
                    </div>
                    <div class="col-lg-7 col-md-12 col-12">
                        <div class="testimonial-content">
                            <div class="testimonial-dec">
                                <p><span>OSWAN</span> Lorem ipsum dolor sit amet, consectetur adipisicing , sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex commodo consequat. Duis dolor in reprehenderit.</p>
                            </div>
                            <div class="name-designation">
                                <h4>James Momen Nirob</h4>
                                <span>CEO, ASEKHA</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="googlereview-area">
    <div class="container">
        <div class="section-title ml-150 mt-20 text-center">
            <h2>{{__('front/front.google_review')}}</h2>
        </div>
        
        <div class="blog-hm-content">
             @include('front.google_review')
        </div>
    </div>
</div>
          <div class="modal fade product_modals" id="NewBikeModal" tabindex="-1" role="dialog" aria-hidden="true">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span class="icofont icofont-close" aria-hidden="true"></span>
            </button>
            <div class="modal-dialog" role="document">
                <div class="modal-content">

                    <div class="modal-body">
                      
                        <div class="qwick-view-left">
                            <div class="quick-view-learg-img">
                                <div class="quick-view-tab-content tab-content modal_newimage">
                                
                                </div>
                            </div>
                            <div class="quick-view-list nav small_img" role="tablist">
                               
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
                                            <span id="view_frame"></span> <br> 
                                            <span id="engine_number"></span>
                                           
                                            
                                            
                                        </div>
                                    </div>
                                </div>
                            </div>
                    </div>
                </div>
            </div>
        </div>
@endsection