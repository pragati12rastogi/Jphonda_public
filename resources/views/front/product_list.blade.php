@extends($layout)

@section('title', __('JP Honda'))

@section('js')
<script>
   $(document).ready(function() {
     //fetchRecords();

$("#categories li").click(function() {
      var check=this.id;
       $('#categories li').removeAttr('check');
       $('#bike').removeClass('active');
       $('#scooty').removeClass('active');
       $(this).attr("check", "selected");
      var type='';
      if(check!=''){
        if(check=='bikes'){
          $('#bikes').hasClass('active');
          $(".mc").css( "color", "#ffb52f" );
          $(".sc").css( "color", "black" );
          type='MC';
        }else if(check=='scootys'){
          $('#scootys').hasClass('active');
          $(".sc").css( "color", "#ffb52f" );
          $(".mc").css( "color", "black" );
          type='SC';
        }
        var model_name= $("#model_name").children("option:selected").val();
        var model_variant= $("#model_variant").children("option:selected").val();
        var model_color= $("#model_color").children("option:selected").val();
        all_filter_data(type,model_name,model_variant,model_color);
      }
   
});


  $(document).on('click', '.pagination a', function(event){
    event.preventDefault(); 
    var page = $(this).attr('href').split('page=')[1];
    fetch_data(page);
   });

 $(document).on('click', '#bike', function(){
     
   $('#scootys').removeAttr('check');
    $(".mc").css( "color", "black" );
    $(".sc").css( "color", "black" );
   var type='MC';
   var model_name= $("#model_name").children("option:selected").val();
   var model_variant= $("#model_variant").children("option:selected").val();
   var model_color= $("#model_color").children("option:selected").val();
    all_filter_data(type,model_name,model_variant,model_color);
    
   });

 $(document).on('click', '#scooty', function(){
    $(".mc").css( "color", "black" );
    $(".sc").css( "color", "black" );
    $('#bikes').removeAttr('check');
    var type='SC';
    var model_name= $("#model_name").children("option:selected").val();
    var model_variant= $("#model_variant").children("option:selected").val();
     var model_color= $("#model_color").children("option:selected").val();
    all_filter_data(type,model_name,model_variant,model_color);
   });

 $( "#search_product" ).keyup(function() {
     var search = this.value;
     search_value(search);
});

});

$('#model_name').on( 'change', function () {
   
    var type="";
    if($('#bike').hasClass('active')){
       type="MC";
    }if($('#scooty').hasClass('active')){
     type="SC";
    }
    var model_name= $("#model_name").children("option:selected").val();
    var model_variant= $("#model_variant").children("option:selected").val();
    var model_color= $("#model_color").children("option:selected").val();
     all_filter_data(type,model_name,model_variant,model_color);
       
    });

  $('#model_variant').on( 'change', function () {

   var type="";
   if($('#bike').hasClass('active')){
       type="MC";
    }if($('#scooty').hasClass('active')){
     type="SC";
    }
     var model_name= $("#model_name").children("option:selected").val();
     var model_variant= $("#model_variant").children("option:selected").val();
     var model_color= $("#model_color").children("option:selected").val();
       all_filter_data(type,model_name,model_variant,model_color);
    
    });

$('#model_color').on( 'change', function () {

   var type="";
   if($('#bike').hasClass('active')){
       type="MC";
    }if($('#scooty').hasClass('active')){
     type="SC";
    }
     var model_name= $("#model_name").children("option:selected").val();
     var model_variant= $("#model_variant").children("option:selected").val();
     var model_color= $("#model_color").children("option:selected").val();
       all_filter_data(type,model_name,model_variant,model_color);
    
    });

function fetch_data(page) {
  var type="";
  var model= $("#model_name").children("option:selected").val();
  var variant= $("#model_variant").children("option:selected").val();
  var color= $("#model_color").children("option:selected").val();
  var search=$("#search_product").val();


   if($('#bike').hasClass('active') || $('#bikes').attr( "check" )){
       type="MC";
    }
   if($('#scooty').hasClass('active') || $('#scootys').attr( "check" )){
     type="SC";
    }
    $.ajax({
       url:"/product/fetch_data?page="+page, 
       data: {  
       type:type,  
       model:model,
       variant:variant,
       color:color,
       search:search,
    }, 
     success:function(data)
     {
      $('#table_data').html(data);
      
     }
    });
 }

 
  function search_value(search){

    $.ajax({
     url:"/product/search_value",
     data: {  
     search:search,  
    }, 
     success:function(data)
     {
      $('#table_data').html(data);
     }
    });
 }
 

function all_filter_data(type,model,variant,color){
 
 var type=type;

  $.ajax({
     url:"/product/all_filter_data",
     data: { 
     type:type,
     model:model,
     variant:variant,  
     color:color,
    }, 
     success:function(data)
     {
      $('#table_data').html(data);
     }
    });

}
function function_api_call(i) {
  var id = $(i).attr('data-id');
  var model = $(i).attr('data-model');
  var variant = $(i).attr('data-variant');
  var color = $(i).attr('data-color');
  var frame = $(i).attr('data-frame');
  var engine_number = $(i).attr('data-engine_number');

        
      $.ajax({
         url:"/newproduct/get/details",
         data: { 'id':id
        },success:function(data) {
            var str='';var str1='';
            var i=1; 

         if(data.length > 0){  

            $.each(data,function(key,value){
                  $("#big_img").attr('src','/upload/product/'+value.image);
                  $("#small_imges").attr('src','/upload/product/'+value.image);
                   str+= "<a href='#modal"+i+"' data-toggle='tab' role='tab' >"+
                      "<img src='/upload/product/resized/"+value.image+"' class='small_images' id='smallthumbimg_"+i+"' alt='Image' hight='70' width='70'/>"+
                    "</a>";

                    str1+="<div class='tab-pane fade' id='modal"+i+"' role='tabpanel'>"+
                      "<img src='/upload/product/"+value.image+"' class='big_images' id='bigthumbimg_"+i+"' alt='demo' style='max-width: 300px;'/>"+
                   "</div>";
                  
                    i++;
            });  

        }else{
            str+= "<a href='#modal"+i+"' data-toggle='tab' role='tab' >"+
                      "<img src='/front/img/product/product-default.jpg' class='small_images' id='smallthumbimg_"+i+"' alt='Image' hight='70' width='70'/>"+
                    "</a>";
            str1+="<div class='tab-pane fade' id='modal"+i+"' role='tabpanel'>"+
                      "<img src='/front/img/product/product-default.jpg' class='big_images' id='bigthumbimg_"+i+"' alt='demo' style='max-width: 300px;'/>"+
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


            $('#productModal').modal('show');

         }
      });
  
}

</script>
@endsection
@section('css')
        <!--inputs css-->
<link rel="stylesheet" href="/css/style.css">
 <link rel="stylesheet" href="/front/css/jquery-ui.css">
 <link rel="stylesheet" href="/front/css/product.css">
@endsection
@section('main_section')
    <section class="content">
      <div class="breadcrumb-area pt-120">
         <div class="container-fluid">
              <div class="breadcrumb-content text-center contactus_header">
                    <h2>{{__('front/front.product')}}</h2>
                </div>
            </div>
       <div class="shop-wrapper fluid-padding-2 pt-50">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-lg-3">
                            <div class="product-sidebar-area pr-60">
                                <div class="sidebar-widget pb-55">
                                    <h3 class="sidebar-widget">Search Products</h3>
                                    <div class="sidebar-search">
                                        <form action="#">
                                            <input type="text" id="search_product" placeholder="Search Products...">
                                            <button><i class="ti-search"></i></button>
                                        </form>
                                    </div>
                                </div>
                                <div class="sidebar-widget pb-50">
                                    <h3 class="sidebar-widget">by categories</h3>
                                    <div class="widget-categories">
                                        <ul id="categories">
                                            <li class="mc" id="bikes">MC</li>
                                            <li class="sc" id="scootys">SC</li>
                                           
                                        </ul>
                                    </div>
                                </div>
                                <div class="sidebar-widget mb-55">
                                    <h3 class="sidebar-widget">by price</h3>
                                    <div class="price_filter mr-60">
                                        <div id="slider-range"></div>
                                        <div class="price_slider_amount">
                                            <div class="label-input">
                                                <label>price : </label>
                                                <input type="text" readonly id="amount" name="price"  placeholder="Add Your Price" style="width:150px;"/>
                                            </div>
                                            <br/>
                                            <button type="button" id="range">Filter</button> 
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-9">
                            <div class="shop-topbar-wrapper">
                                <div class="grid-list-options">
                                    
                                </div>
                                <div class="product-sorting">
                                    <div class="shop-product-sorting nav">
                                        <a   data-toggle="tab" id="bike" href="#new-product">BIKES </a>
                                        <a data-toggle="tab" id="scooty" href="#accessory-product">SCOOTY</a>
                                    </div>
                                    <div class="sorting sorting-bg-1">
                                      <select   class="input-css select2 selectValidation" name="model_name"  id="model_name">
                                       <option value="">Select Model Name</option>
                                       @foreach($model as $key => $val)
                                       <option value="{{$val['model_name']}}" {{(old('model_name') == $val['model_name']) ? 'selected' : '' }}> {{$val['model_name']}} </option>
                                       @endforeach
                                    </select>
                                    </div>&nbsp;&nbsp;
                                     <div class="sorting sorting-bg-1">
                                      <select   class="input-css select2 selectValidation" name="model_variant"  id="model_variant">
                                       <option value="">Select Model Variant</option>
                                       @foreach($variant as $key => $val)
                                       <option value="{{$val['model_variant']}}" {{(old('model_variant') == $val['model_variant']) ? 'selected' : '' }}> {{$val['model_variant']}} </option>
                                       @endforeach
                                    </select>
                                    </div>&nbsp;&nbsp;
                                    <div class="sorting sorting-bg-1">
                                      <select class="input-css select2 selectValidation" name="model_color"  id="model_color">
                                       <option value="">Select Color</option>
                                       @foreach($color as $key => $val)
                                       <option value="{{$val['color_code']}}" {{(old('color_code') == $val['color_code']) ? 'selected' : '' }}> {{$val['color_code']}} </option>
                                       @endforeach
                                    </select>
                                    </div>&nbsp;&nbsp;
                                </div>
                            </div>
                            <div class="grid-list-product-wrapper tab-content">
                              <div class="row">
                                  <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                      <div class="table-content ">
                                        <div id="table_data">
                                          @include('front.pagination_data')
                                         </div>
                                      </div>
                                  </div>
                              </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
          <div class="modal fade product_modals" id="productModal" tabindex="-1" role="dialog" aria-hidden="true">
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
      </div>
      </section>
@endsection