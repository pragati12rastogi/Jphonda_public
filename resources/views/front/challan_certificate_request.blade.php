@extends($layout)

@section('title', __('JP Honda'))
@section('css')
<link rel="stylesheet" href="/css/style.css">
<link rel="stylesheet" href="/front/css/service.css">

@endsection
@section('js')
<script>

  $(document).ready(function(){
      calculation();
   });


    $(".challanaddOther").click(function(){
                var count = $('.charge').length;
              
                $('#count').val(count);
                $(".challanappended-div").append(
                    '<div class=" row challan-condition-row charge appended-content">'+
                        '<div class="col-md-4">'+
                            '<label>{{__("front/challan.challan")}} #</label>'+
                            '<input type="text" name="challan[]" class="input-css challan" id="challan_'+count+'"/>'+
                        '</div>'+
                        '<div class="col-md-3">'+
                            '<label>{{__("front/challan.amount")}}</label>'+
                            '<input type="number" min="0" name="amount[]" class="input-css amount" id="amount_'+count+'"/>'+
                        '</div>'+
                        '<a href="javascript:void(0)" class="rm-btn"><i class="fa fa-close"></i></a>'+
                    '</div>');
                    $('select'
                ).select2();
               
            });

$(document).on('click','.rm-btn',function(e){
  $(this).parents(".appended-content").remove();

  calculation();
 
});

function calculation(){
       var de = $("#service_charge").text();
        var sum = 0;
        $(".amount").each(function(){
            sum += +$(this).val();
        });
        var def_sum=parseInt(de)+parseInt(sum);
        $("#total").text(def_sum);
        $("#total_amount").val(def_sum);
        $("#challan_amount").text(sum);
       
  }

$(document).on("keyup", ".amount", function() {
     calculation();
    });
function validate_part_no(part_no,index,class_name) {
        // find  part #
        var old_id;
        var mesg = '';
        $('.'+class_name).each(function(){
               var ele_val = $(this).val();
                el_id = $(this).attr('id');
                var res1 = el_id.split("_");
                var old_id=res1[1];

            if((part_no==ele_val) && (index != old_id))
            {
                $('#'+class_name+"_"+index).val('');
                
                mesg = "same_value";
            }
         });
        return mesg;
    }

    $(document).on('keyup','.challan',function(e){

        var challan_no = $(this).val();
        var el = $(this);
        
        var id=$(this).attr('id');
        var arr = id.split('_');
        var index = arr[1];  
        var class_name = 'challan';

        $(el).parent().find("span.challan-error").remove();

        if (challan_no != null && challan_no != undefined && challan_no != '') {
            
            var res_validate_challan_no = validate_part_no(challan_no,index,class_name);
            if(res_validate_challan_no == 'same_value'){
                $(el).parent().append("<span class='challan-error' style='color:red'>This challan Number Already taken.</span>");
                return false;
            }else{

                 $.ajax({
                    url:'/pay/challan/number/check',
                    data: {'challan':challan_no},
                    method:'GET',
                    success:function(result){
                        console.log(result);
                     if (result == 'challan_error') {
                       $(el).parent().append("<span class='challan-error' style='color:red'>This challan Number Already taken.</span>");
                        $("#challan_"+index).val('');
                          return false;
                      }
                    },
                    error:function(error){
                        alert("something wen't wrong.");
                    }
                });

            }
        }
        
    });

</script>
@endsection

@section('main_section')
<div class="about-us-area overview-area pt-60">
     <div class="breadcrumb-area breadcrumb-area pb-10">
                <div class="container-fluid">
                    <div class="breadcrumb-content text-center contactus_header">
                    <h2>{{__('front/challan.pay_challan_request')}}</h2>
                 </div>
                </div>
            </div>
    <div class="container">
            <div class="col-lg-12 col-md-12">
                    <div id="app">
                        @include('admin.flash-message')
                        @yield('content')
                    </div>
                    <div class="col-lg-8 col-md-12 col-12">
                        <form action="/pay/challan/request" method="POST">
                            @csrf
                            <div class="checkbox-form">  
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="checkout-form-list">
                                            <label>{{__('front/challan.name')}} <span class="required">*</span></label>          
                                            <input type="text" name="name" value="{{old('name')}}">
                                            {!! $errors->first('name', '<p class="help-block">:message</p>') !!}
                                        </div>
                                    </div>
                                    <div class="col-md-6 ">
                                        <div class="checkout-form-list">
                                            <label>{{__('front/challan.mobile')}} <span class="required">*</span></label>
                                            <input type="text" name="mobile" value="{{old('mobile')}}">
                                            {!! $errors->first('mobile', '<p class="help-block">:message</p>') !!}
                                        </div>
                                    </div>
                                    </div>
                                    <div class="row pt-20">
                                       <div class="col-md-6 ">
                                            <div class="checkout-form-list">
                                            	<label>{{__('front/challan.registration')}}<span class="required">*</span></label>          
                                                <input type="text" name="registration_no" value="{{old('registration_no')}}">
                                                {!! $errors->first('registration_no', '<p class="help-block">:message</p>') !!}
                                            </div>
                                        </div>
                                      
                                    </div>
					                   <div class="row charge challan-condition-row pt-20">
					                        <div class="col-md-4">
					                          <div class="checkout-form-list">
					                            <label>{{__('front/challan.challan')}} #</label>
					                            <input type="text" name="challan[]" class="input-css challan" id="challan_0" value="@if(old('challan')){{((old('challan')[0])?(old('challan')[0]):'')}}@endif"/>
					                            
					                            {!! $errors->first('challan.*', '<p class="help-block">:message</p>') !!}
					                          </div>
					                        </div>
					                        <div class="col-md-3">
					                           <div class="checkout-form-list">
					                            <label>{{__('front/challan.amount')}}</label>
					                            <input type="number" min="0" name="amount[]"  class="input-css amount" id="amount_0" value="@if(old('challan')){{((old('amount')[0])?(old('amount')[0]):'')}}@endif"/>
					                            {!! $errors->first('amount.*', '<p class="help-block">:message</p>') !!}
					                           </div>
					                        </div>
					                    </div><!-- challan-condition-row -->
										<div class="challanappended-div">
							                @if(old('challan'))
					                            @if(count(old('challan')) > 1)
					                            @for($i=1;$i < count(old('challan'));$i++)
					                            <div class="row challan-condition-row charge appended-content">
					                                <div class="col-md-4">
					                                   <div class="checkout-form-list">
					                                    <label>{{__('front/challan.challan')}} #</label>
					                                     <input type="text" name="challan[]" value="{{((old('challan')[$i])?(old('challan')[$i]):'')}}" class="input-css challan" id="challan_{{$i}}"/>
					                                    {!! $errors->first('challan.*', '<p class="help-block">:message</p>') !!}
					                                  </div>	
					                                </div>
					                                <div class="col-md-3">
							                           <div class="checkout-form-list">
							                            <label>{{__('front/challan.amount')}}</label>
							                            <input type="number" min="0" name="amount[]"  value="{{((old('amount')[$i])?(old('amount')[$i]):'')}}"  class="input-css amount" id="amount_{{$i}}"/>
							                            {!! $errors->first('amount.*', '<p class="help-block">:message</p>') !!}
							                           </div>
					                        		</div>				               
                                                <a href="javascript:void(0)" class="rm-btn"><i class="fa fa-close"></i></a>
					                            </div>
					                            @endfor
					                            @endif
					                        @endif
										</div><!-- end of appended div -->
						                <div class="row">
						                    <div class="col-md-8"></div>
						                    <div class="col-md-2"><i class="fa fa-plus challanaddOther" style=" cursor:pointer">Add More</i></div>
						                </div>
                                         <div class="row">
                                    
                                          <div class="col-md-4"></div>
                                          <div class="col-md-4"> <label>{{__('front/challan.service_charge')}}: <span id="service_charge">100</span></label></div>
                                        </div>
						                <div class="row">
						                  <input type="hidden" name="total_amount" id="total_amount" value="0">
                                          <div class="col-md-4"></div>
					                      <div class="col-md-4"> <label>{{__('front/challan.challan_amount')}}: <span id="challan_amount"></span></label></div>
					                    </div>                                      
					                     <div class="row">
					                      <div class="col-md-4"> <label>{{__('front/challan.total_amount')}} : <span id="total"></span></label></div>
					                    </div>
                                    <div class="row">
                                       <div class="contact-form-style">            
                                          <button class="submit cr-btn btn-style" type="submit"><span>{{__('front/front.submit')}}</span></button>
                                        </div>
                                    </div>
                                </div>                                                  
                            </div>
                        </form>
                    </div>
        </div>
    </div>
</div>
@endsection