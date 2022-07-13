@extends($layout)

@section('title', __('Incentive Parameters'))

@section('breadcrumb')
    <li><a href="#"><i class=""></i> {{__('Incentive Parameters')}} </a></li>
    <style>
    .rm-btn {
    font-size: 24px;
    font-weight: bold;
    position: absolute;
    left: 35px;
    color: #c20302;
    margin-top: 35px;
     }
    .all_err{
      color: red;
    }
    </style>
@endsection
@section('js')
<script>

$(document).ready(function() {

    $("#sale-inc_type_div").hide();
    $("#bestdeal-inc_type_div").hide();
    $("#otc-inc_type_div").hide();
    $("#service-inc_type_div").hide();

    @if(isset($sale_setting))
      @if(isset($sale_setting[0]['incentive_type']))
         $("input[name='sale_incent_type']").trigger('change');
      @endif
    @endif

    @if(isset($sale_setting))
      @if(isset($sale_setting[0]['incentive_type']))
         $("input[name='bestdeal_incent_type']").trigger('change');
      @endif
    @endif

    @if(isset($sale_setting))
      @if(isset($sale_setting[0]['incentive_type']))
         $("input[name='otc_incent_type']").trigger('change');
      @endif
    @endif

    @if(isset($sale_setting))
      @if(isset($sale_setting[0]['incentive_type']))
         $("input[name='service_incent_type']").trigger('change');
      @endif
    @endif

    $(".select2").on("select2:close", function(e) {
        $(this).valid();
    });
    $.validator.addMethod("notValidIfSelectFirst", function(value, element, arg) {
        return arg !== value;
    }, "This field is required.");

});
$(function(){

    $("#my-form1").validate({

        rules:
        {
          sale_incent_type:{ required:true },
          bestdeal_incent_type:{ required:true },
          otc_incent_type:{ required:true },
          service_incent_type:{ required:true },
          sale_incent_calc:{ required:true },
          bestdeal_incent_calc:{ required:true },
          otc_incent_calc:{ required:true },
          service_incent_calc:{ required:true }
        },
        messages:
        {
          sale_incent_type:
          {
            required:"Please select a Incentive Type"
          },
          bestdeal_incent_type:
          {
            required:"Please select a Incentive Type"
          },
          otc_incent_type:
          {
            required:"Please select a Incentive Type"
          },
          service_incent_type:
          {
            required:"Please select a Incentive Type"
          },
          sale_incent_calc:{
            required:"Please select a Calculation Type"
          },
          bestdeal_incent_calc:{
            required:"Please select a Calculation Type"
          },
          otc_incent_calc:{
            required:"Please select a Calculation Type"
          },
          service_incent_calc:{
            required:"Please select a Calculation Type"
          }
        },
        errorPlacement: function(error,element)
        {
            if ($(element).hasClass('select2')) {
                var place = $(element).siblings(".select2-container");
                error.insertAfter(place);
            }
            else if($(element).attr('type') == 'radio')
            {
                error.insertAfter(element.parent());
            }
            else
            error.insertAfter(element);
        }
    });
    // saleparameter,.salecondition,.salevalue,.bestdealvalue,.otcvalue,.servicevalue,.bestdealcondition,.otccondition,.servicecondition,.bestdealparameter,.otcparameter,.serviceparameter
        $(".saleparameter,.salecondition,.salevalue,.bestdealvalue,.otcvalue,.servicevalue,.bestdealcondition,.otccondition,.servicecondition,.bestdealparameter,.otcparameter,.serviceparameter,.service_percentage,.otc_percentage,.bestdeal_percentage,.salepercentage").each(function (item) {
            $(this).rules("add", {
                required: true,
            });
        });
        // $(".select2").removeClass("error");
     
   
});

     $('#js-msg-errors').hide();
     $('#js-msg-successs').hide();

   $(document).on('change','.salecondition',function(e){
     
        var data=$(this).val();
        var id=$(this).attr('id');
        var type="sale";
        between(data,id,type);
    
      });

  $(document).on('change','.bestdealcondition',function(e){
        var data=$(this).val();
        var id=$(this).attr('id');
        var type="bestdeal";
        between(data,id,type);

      });
  $(document).on('change','.otccondition',function(e){
        var data=$(this).val();
        var id=$(this).attr('id');
        var type="otc";
        between(data,id,type);

      });
  $(document).on('change','.servicecondition',function(e){
        var data=$(this).val();
        var id=$(this).attr('id');
        var type="service";
        between(data,id,type);

      });

   function between(data,id,type){
         $('#js-msg-errors').hide();
         $('#js-msg-successs').hide();
         $("#min").val('');
         $("#max").val('');

        if(data=='BETWEEN'){

             $("#mo_title").text('BETWEEN');
              var arr = id.split('_');
              var index = arr[1];   
              var msg='#'+type+'value_'+index;
              $("#ids").val(msg)
              $('#myModal').modal("show");
        }else if(data=='NOT BETWEEN'){
             $("#mo_title").text('NOT BETWEEN');
              var arr = id.split('_');
              var index = arr[1];   
              var msg='#'+type+'value_'+index;
              $("#ids").val(msg)
              $('#myModal').modal("show");
        }else{
             $('#myModal').modal("hide");
        }
   }


    $(document).on('click','#submitbutton',function(){
         var min=$("#min").val();
         var max=$("#max").val();
         var id=$("#ids").val();
      if(min==''){
         $('#js-msg-errors').html("Minimum value is required").show();   
      }else if(max==''){
        $('#js-msg-errors').html("Maximum value is required").show(); 
      }else{
            $(id).val(min+","+max);
            $('#myModal').modal("hide");
      }

    });

    $(".slaeaddOther").click(function(){
                var count = $('.charge').length;
              
                $('#count').val(count);
                $(".saleappended-div").append(
                    '<div class=" row sale-condition-row charge appended-content">'+
                        '<div class="col-md-3">'+
                            '<label>Parameters<sup>*</sup></label>'+
                            '<select id="saleparameter_'+count+'" class="saleparameter append select2 form-control" name="saleparameter[]">'+
                            '<option value="" selected>Select Parameter</option>'+
                            '<option value="ex_showroom_price">Ex Showroom Price</option>'+
                            '<option value="total_amount">Total Amount</option>'+
                            '<option value="accessories_value">Accessories Value</option>'+
                            '</select>'+
                            '<span id="saleparameter-error_'+count+'" class="all_err"></span>'+
                        '</div>'+
                        '<div class="col-md-2">'+
                            '<label>Condition<sup>*</sup></label>'+
                            '<select id="salecondition_'+count+'" name="salecondition[]" class="salecondition select2 form-control append" >'+
                                '<option value="" selected>Select Condition</option>'+
                                 '<option value="="> = </option>'+
                                 '<option value=">"> > </option>'+
                                 '<option value=">="> >= </option>'+
                                 '<option value="<"> < </option>'+
                                 '<option value="<="> <= </option>'+
                                 '<option value="!="> != </option>'+
                                 '<option value="BETWEEN">BETWEEN</option>'+
                                 '<option value="NOT BETWEEN">NOT BETWEEN</option>'+
                            '</select>'+
                        '</div>'+
                        '<div class="col-md-2">'+
                            '<div class="col-md-9">'+
                                '<label>Value<sup>*</sup></label>'+
                                '<input id="salevalue_'+count+'" class="salevalue" type="text" name="salevalue[]" />'+
                            '</div>'+
                        '</div>'+
                        '<div class="col-md-2">'+
                          '<a href="javascript:void(0)" class="rm-btn"><i class="fa fa-close"></i></a>'+
                        '</div>'+
                    '</div>');
                    $('select'
                ).select2();
               
            });

    $(".bestdealaddOther").click(function(){
                var bcount = $('.bestdealcharge').length;
              
                $('#bcount').val(bcount);
                $(".bestdealappended-div").append(
                    '<div class=" row bestdeal-condition-row bestdealcharge appended-content">'+
                        '<div class="col-md-3">'+
                            '<label>Parameters<sup>*</sup></label>'+
                            '<select id="bestdealparameter_'+bcount+'" class="bestdealparameter select2 form-control append" name="bestdealparameter[]">'+
                            '<option value="" selected>Select Parameter</option>'+
                            '<option  value="selling_price">Selling Price</option>'+
                            '<option value="value">Bought Value</option>'+
                            '</select>'+
                            '<span id="bestdealparameter-error_'+bcount+'" class="all_err"></span>'+
                        '</div>'+
                        '<div class="col-md-2">'+
                            '<label>Condition<sup>*</sup></label>'+
                            '<select id="bestdealcondition_'+bcount+'" class="bestdealcondition select2 form-control append" name="bestdealcondition[]">'+
                                '<option  value="" selected>Select Condition</option>'+
                                 '<option value="="> = </option>'+
                                 '<option value=">"> > </option>'+
                                 '<option value=">="> >= </option>'+
                                 '<option value="<"> < </option>'+
                                 '<option value="<="> <= </option>'+
                                 '<option value="!="> != </option>'+
                                 '<option value="BETWEEN">BETWEEN</option>'+
                                 '<option value="NOT BETWEEN">NOT BETWEEN</option>'+
                            '</select>'+
                        '</div>'+
                        '<div class="col-md-2">'+
                            '<div class="col-md-9">'+
                                '<label>Value<sup>*</sup></label>'+
                                '<input id="bestdealvalue_'+bcount+'" class="bestdealvalue append" type="text" name="bestdealvalue[]" />'+
                            '</div>'+
                        '</div>'+
                        '<div class="col-md-2">'+
                          '<a href="javascript:void(0)" class="rm-btn"><i class="fa fa-close"></i></a>'+
                        '</div>'+
                    '</div>');
                    $('select'
                ).select2();
            });

    $(".otcaddOther").click(function(){
                var ocount = $('.otccharge').length;
              
                $('#ocount').val(ocount);
                $(".otcappended-div").append(
                    '<div class=" row otc-condition-row otccharge appended-content">'+
                        '<div class="col-md-3">'+
                            '<label>Parameters<sup>*</sup></label>'+
                            '<select  id="otcparameter_'+ocount+'" class="otcparameter select2 form-control append" name="otcparameter[]">'+
                            '<option value="" selected>Select Parameter</option>'+
                            '<option value="total_amount">Total Amount</option>'+
                            '<option value="ew_cost">EW Cost</option>'+
                            '<option value="amc_cost">AMC Cost</option>'+
                            '<option value="hjc_cost">HJC Cost</option>'+
                            '</select>'+
                            '<span id="otcparameter-error_'+ocount+'" class="all_err"></span>'+
                        '</div>'+
                        '<div class="col-md-2">'+
                            '<label>Condition<sup>*</sup></label>'+
                            '<select id="otccondition_'+ocount+'" class="otccondition select2 form-control append" name="otccondition[]">'+
                                '<option value="" selected>Select Condition</option>'+
                                 '<option value="="> = </option>'+
                                 '<option value=">"> > </option>'+
                                 '<option value=">="> >= </option>'+
                                 '<option value="<"> < </option>'+
                                 '<option value="<="> <= </option>'+
                                 '<option value="!="> != </option>'+
                                 '<option value="BETWEEN">BETWEEN</option>'+
                                 '<option value="NOT BETWEEN">NOT BETWEEN</option>'+
                            '</select>'+
                        '</div>'+
                        '<div class="col-md-2">'+
                            '<div class="col-md-9">'+
                                '<label>Value<sup>*</sup></label>'+
                                '<input id="otcvalue_'+ocount+'" type="text" class="otcvalue append" name="otcvalue[]" />'+
                            '</div>'+
                        '</div>'+
                        '<div class="col-md-2">'+
                          '<a href="javascript:void(0)" class="rm-btn"><i class="fa fa-close"></i></a>'+
                        '</div>'+
                    '</div>');
                    $('select'
                ).select2();
                
            });

        $(".serviceaddOther").click(function(){
                var scount = $('.servicecharge').length;
              
                $('#scount').val(scount);
                $(".serviceappended-div").append(
                    '<div class=" row service-condition-row servicecharge appended-content">'+
                        '<div class="col-md-3">'+
                            '<label>Parameters<sup>*</sup></label>'+
                            '<select id="serviceparameter_'+scount+'" class="serviceparameter append select2 form-control" name="serviceparameter[]">'+
                            '<option value="" selected>Select Parameter</option>'+
                            '<option value="ex_showroom_price">Ex Showroom Price</option>'+
                            '<option value="total_amount">Total Amount</option>'+
                            '</select>'+ 
                            '<span id="serviceparameter-error_'+scount+'" class="all_err"></span>'+
                        '</div>'+
                        '<div class="col-md-2">'+
                            '<label>Condition<sup>*</sup></label>'+ 
                            '<select id="servicecondition_'+scount+'" class="servicecondition select2 form-control servicecondition append" name="servicecondition[]">'+
                                '<option value="" selected>Select Condition</option>'+
                                 '<option value="="> = </option>'+
                                 '<option value=">"> > </option>'+
                                 '<option value=">="> >= </option>'+
                                 '<option value="<"> < </option>'+
                                 '<option value="<="> <= </option>'+
                                 '<option value="!="> != </option>'+
                                 '<option value="BETWEEN">BETWEEN</option>'+
                                 '<option value="NOT BETWEEN">NOT BETWEEN</option>'+
                            '</select>'+
                        '</div>'+
                        '<div class="col-md-2">'+
                            '<div class="col-md-9">'+
                                '<label>Value<sup>*</sup></label>'+
                                '<input id="servicevalue_'+scount+'" class="servicevalue append" type="text" name="servicevalue[]" />'+
                            '</div>'+
                        '</div>'+
                        '<div class="col-md-2">'+
                          '<a href="javascript:void(0)" class="rm-btn"><i class="fa fa-close"></i></a>'+
                          '</div>'+
                    '</div>');
                    $('select'
                ).select2();   
                 
            });

$(document).on('click','.rm-btn',function(e){
  $(this).parents(".appended-content").remove();
 
});

$(document).on('change','.saleparameter',function(e){
          var pvalue=$(this).val();
          var proId=$(this).attr('id');
          var arr = proId.split('_');
          var index = arr[1]; 
          var type='saleparameter';
          product_check(pvalue,index,type)
    });

$(document).on('change','.bestdealparameter',function(e){
          var pvalue=$(this).val();
          var proId=$(this).attr('id');
          var arr = proId.split('_');
          var index = arr[1]; 
          var type='bestdealparameter';
          product_check(pvalue,index,type)
    });
$(document).on('change','.otcparameter',function(e){
          var pvalue=$(this).val();
          var proId=$(this).attr('id');
          var arr = proId.split('_');
          var index = arr[1]; 
          var type='otcparameter';
          product_check(pvalue,index,type)
    });
$(document).on('change','.serviceparameter',function(e){
          var pvalue=$(this).val();
          var proId=$(this).attr('id');
          var arr = proId.split('_');
          var index = arr[1]; 
          var type='serviceparameter';
          product_check(pvalue,index,type)
    });


function product_check(pvalue,index,type)
    {
        var old_id;
        $('.'+type).each(function(){
               var ele_val = $(this).children("option:selected").val();
                el_id = $(this).attr('id');
                var res1 = el_id.split("_");
                var old_id=res1[1];

            if((pvalue==ele_val) && (index != old_id))
            {
                $('#'+type+"_"+index).val('').trigger('change');
                $('#'+type+"-error_"+index).html('Already Selected!');
                if(type=='saleparameter'){
                    type='Sale Parameter';
                  }if(type=='bestdealparameter'){
                    type='Bestdeal Parameter';
                  }if(type=='otcparameter'){
                    type='Otc Sale Parameter';
                  }if(type=='serviceparameter'){
                    type='Service Parameter';
                  }

                 
            }
         });
    }
    $(document).on('click','.remove_div',function(){
        $(this).parent().parent().remove();
    })

$("input[name='sale_incent_type']").change(function(){
  $selected_value = $("input[name='sale_incent_type']:checked").val();
  if($selected_value == 'percentage'){
    $("#sale-inc_type_div").show();
    $("#sale-inc_type_show").show();
  }else{
    $("#sale-inc_type_div").show();
    $("#sale-inc_type_show").hide();
  }
    
});

$("input[name='bestdeal_incent_type']").change(function(){
  $selected_value = $("input[name='bestdeal_incent_type']:checked").val();
  if($selected_value == 'percentage'){
    $("#bestdeal-inc_type_div").show();
    $("#bestdeal-inc_type_show").show();
  }else{
    $("#bestdeal-inc_type_div").show();
    $("#bestdeal-inc_type_show").hide();
  }
    
});

$("input[name='otc_incent_type']").change(function(){
  $selected_value = $("input[name='otc_incent_type']:checked").val();
  if($selected_value == 'percentage'){
    $("#otc-inc_type_div").show();
    $("#otc-inc_type_show").show();
  }else{
    $("#otc-inc_type_div").show();
    $("#otc-inc_type_show").hide();
  }
    
});

$("input[name='service_incent_type']").change(function(){
  $selected_value = $("input[name='service_incent_type']:checked").val();
  if($selected_value == 'percentage'){
    $("#service-inc_type_div").show();
    $("#service-inc_type_show").show();
  }else{
    $("#service-inc_type_div").show();
    $("#service-inc_type_show").hide();
  }
    
});

</script>

@endsection

@section('main_section')
    <section class="content">
            @include('admin.flash-message')
                @yield('content')
            <!-- general form elements -->
            <form id="my-form1" action="/admin/setting/incentive" method="POST">
                @csrf
            <input type="text" name="cat_type" value="sale" class="cat_type" style="opacity:0">
            {!! $errors->first('cat_type', '<p class="help-block">:message</p>') !!}
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Sale</h3>
                </div>
                <div class="box-body sale-condition">
                  <div class="row">
                    <div class="col-md-6">
                      <label>Incentive Type<sup>*</sup></label>
                      <div class="col-md-3">
                        <label class="radio-inline">
                            <input name="sale_incent_type" class="sale_incent_type" value="percentage" type="radio" {{isset($sale_setting)?(isset($sale_setting[0]['incentive_type'])&& $sale_setting[0]['incentive_type'] == 'percentage')?'checked':'':''}}/>Percentage
                        </label>
                      </div>
                      <div class="col-md-3">
                        <label class="radio-inline">
                            <input name="sale_incent_type" class="sale_incent_type" value="fixed" type="radio" {{isset($sale_setting)?(isset($sale_setting[0]['incentive_type'])&& $sale_setting[0]['incentive_type'] == 'fixed')?'checked':'':''}}/>Fixed
                        </label>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <label>Incentive Calculation<sup>*</sup></label>
                      <div class="col-md-6">
                        <label class="radio-inline">
                            <input name="sale_incent_calc" class="sale_incent_calc" value="single" type="radio" {{isset($sale_setting)?(isset($sale_setting[0]['calculation_type'])&& $sale_setting[0]['calculation_type'] == 'single')?'checked':'':''}}/>Individual Condition
                        </label>
                      </div>
                      <div class="col-md-6">
                        <label class="radio-inline">
                            <input name="sale_incent_calc" class="sale_incent_calc" value="all" type="radio" {{isset($sale_setting)?(isset($sale_setting[0]['calculation_type'])&& $sale_setting[0]['calculation_type'] == 'all')?'checked':'':''}}/>All Condition
                        </label>
                      </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-3" id="sale-inc_type_div">
                      <br>
                      <input type="number" name="salepercentage" min="0" value="{{isset($sale_setting)?(isset($sale_setting[0]['incentive_percent'])?$sale_setting[0]['incentive_percent']:''):''}}" class="salepercentage form-control-static"/>
                      <span id="sale-inc_type_show">%</span>
                    </div>
                  </div>
                  @if(isset($sale_setting) && count($sale_setting)>0)
                  @foreach(json_decode($sale_setting[0]['parameter']) as $index => $para)
                    <div class="row charge sale-condition-row">
                        <div class="col-md-3">
                            <label>Parameters<sup>*</sup></label>
                            <select class="select2 form-control saleparameter" name="saleparameter[]" id="saleparameter_{{$index}}">
                                <option value="" >Select Parameter</option>
                                <option  value="ex_showroom_price" {{$para[0]=='ex_showroom_price'?'selected':''}}>Ex Showroom Price</option>
                                <option value="total_amount" {{$para[0]=='total_amount'?'selected':''}}>Total Amount</option>
                                <option value="accessories_value" {{$para[0]=='accessories_value'?'selected':''}}>Accessories Value</option>
                            </select>
                            <span id="saleparameter-error_{{$index}}" class="all_err"></span>
                            {!! $errors->first('saleparameter.*', '<p class="help-block">:message</p>') !!}
                        </div>
                        <div class="col-md-2">
                            <label>Condition<sup>*</sup></label>
                            <select class="select2 form-control salecondition" name="salecondition[]" id="salecondition_{{$index}}">
                                <option value="">Select Condition</option>
                                 <option value="=" {{$para[1]== "=" ?'selected':''}}> = </option>
                                 <option value=">" {{$para[1]== ">" ?'selected':''}}> > </option>
                                 <option value=">=" {{$para[1]== ">=" ?'selected':''}}> >= </option>
                                 <option value="<" {{$para[1]== "<" ?'selected':''}}> < </option>
                                 <option value="<=" {{$para[1]== "<=" ?'selected':''}}> <= </option>
                                 <option value="!=" {{$para[1]== "!=" ?'selected':''}}> != </option>
                                 <option value="BETWEEN" {{$para[1]== "BETWEEN" ?'selected':''}}>BETWEEN</option>
                                 <option value="NOT BETWEEN" {{$para[1]== "NOT BETWEEN" ?'selected':''}}>NOT BETWEEN</option>
                            </select>
                             {!! $errors->first('salecondition.*', '<p class="help-block">:message</p>') !!}
                        </div>
                        <div class="col-md-2">
                            <div class="col-md-9">
                                <label>Value<sup>*</sup></label>
                                <input type="text" name="salevalue[]" value="{{$para[2]}}" class="salevalue" id="salevalue_{{$index}}"/>
                                
                                {!! $errors->first('salevalue.*', '<p class="help-block">:message</p>') !!}
                            </div>
                        </div>
                        @if($index > 0)
                        <div class="col-md-2">
                          <a href="javascript:void(0)" class="rm-btn remove_div"><i class="fa fa-close"></i></a>
                        </div>
                        @endif
                    </div>
                  @endforeach
                  @else
                    <div class="row charge sale-condition-row">
                        <div class="col-md-3">
                            <label>Parameters<sup>*</sup></label>
                            <select class="select2 form-control saleparameter" name="saleparameter[]" id="saleparameter_0">
                                <option value="" selected>Select Parameter</option>
                                <option  value="ex_showroom_price">Ex Showroom Price</option>
                                <option value="total_amount">Total Amount</option>
                                <option value="accessories_value">Accessories Value</option>
                            </select>
                            <span id="saleparameter-error_0" class="all_err"></span>
                            {!! $errors->first('saleparameter.*', '<p class="help-block">:message</p>') !!}
                        </div>
                        <div class="col-md-2">
                            <label>Condition<sup>*</sup></label>
                            <select class="select2 form-control salecondition" name="salecondition[]" id="salecondition_0">
                                <option value="" selected>Select Condition</option>
                                 <option value="="> = </option>
                                 <option value=">"> > </option>
                                 <option value=">="> >= </option>
                                 <option value="<"> < </option>
                                 <option value="<="> <= </option>
                                 <option value="!="> != </option>
                                 <option value="BETWEEN">BETWEEN</option>
                                 <option value="NOT BETWEEN">NOT BETWEEN</option>
                            </select>
                             {!! $errors->first('salecondition.*', '<p class="help-block">:message</p>') !!}
                        </div>
                        <div class="col-md-2">
                            <div class="col-md-9">
                                <label>Value<sup>*</sup></label>
                                <input type="text" name="salevalue[]" class="salevalue" id="salevalue_0"/>
                                {!! $errors->first('salevalue.*', '<p class="help-block">:message</p>') !!}
                            </div>
                        </div>
                    </div><!-- sale-condition-row -->
                  @endif
                <div class="saleappended-div"></div><!-- end of appended div -->
                </div><!-- sale-condition -->
                <div class="row">
                    <div class="col-md-7"></div>
                    <div class="col-md-2"><i class="fa fa-plus slaeaddOther" style=" cursor:pointer">Add More</i></div>
                </div>
                <!-- /.box-body -->
                <div class="box-footer">
                    
                </div><!-- /.footer -->
            </div>

            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Bestdeal</h3>
                </div>
                <div class="box-body bestdeal-condition">
                  <div class="row">
                    <div class="col-md-6">
                      <label>Incentive Type<sup>*</sup></label>
                      <div class="col-md-3">
                        <label class="radio-inline">
                            <input name="bestdeal_incent_type" class="bestdeal_incent_type" value="percentage" type="radio" {{isset($best_setting)?(isset($best_setting[0]['incentive_type'])&& $best_setting[0]['incentive_type'] == 'percentage')?'checked':'':''}}/>Percentage
                        </label>
                      </div>
                      <div class="col-md-3">
                        <label class="radio-inline">
                            <input name="bestdeal_incent_type" class="bestdeal_incent_type" value="fixed" type="radio" {{isset($best_setting)?(isset($best_setting[0]['incentive_type'])&& $best_setting[0]['incentive_type'] == 'fixed')?'checked':'':''}}/>Fixed
                        </label>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <label>Incentive Calculation<sup>*</sup></label>
                      <div class="col-md-6">
                        <label class="radio-inline">
                            <input name="bestdeal_incent_calc" class="bestdeal_incent_calc" value="single" type="radio" {{isset($best_setting)?(isset($best_setting[0]['calculation_type'])&& $best_setting[0]['calculation_type'] == 'single')?'checked':'':''}}/>Individual Condition
                        </label>
                      </div>
                      <div class="col-md-6">
                        <label class="radio-inline">
                            <input name="bestdeal_incent_calc" class="bestdeal_incent_calc" value="all" type="radio" {{isset($best_setting)?(isset($best_setting[0]['calculation_type'])&& $best_setting[0]['calculation_type'] == 'all')?'checked':'':''}}/>All Condition
                        </label>
                      </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-3" id="bestdeal-inc_type_div">
                      <br>
                      <input type="number" name="bestdeal_percentage" min="0" value="{{isset($best_setting)?(isset($best_setting[0]['incentive_percent'])?$best_setting[0]['incentive_percent']:''):''}}" class="bestdeal_percentage form-control-static"/>
                      <span id="bestdeal-inc_type_show">%</span>
                    </div>
                  </div>
                  @if(isset($best_setting) && count($best_setting)>0)
                  @foreach(json_decode($best_setting[0]['parameter']) as $index => $bestpara)
                    <div class="row bestdealcharge bestdeal-condition-row">
                        <div class="col-md-3">
                            <label>Parameters<sup>*</sup></label>
                            <select class="bestdealparameter select2 form-control" name="bestdealparameter[]" id="bestdealparameter_{{$index}}">
                                <option value="" >Select Parameter</option>
                                <option  value="selling_price" {{$bestpara[0]=='selling_price'?'selected':''}}>Selling Price</option>
                                <option value="value" {{$bestpara[0]=='value'?'selected':''}}>Bought Value</option>
                            </select>
                            <span id="bestdealparameter-error_{{$index}}" class="all_err"></span>
                            {!! $errors->first('bestdealparameter.*', '<p class="help-block">:message</p>') !!}
                        </div>
                        <div class="col-md-2">
                            <label>Condition<sup>*</sup></label>
                            <select class="select2 form-control bestdealcondition" name="bestdealcondition[]" id="bestdealcondition_{{$index}}">
                                <option value="" >Select Condition</option>
                                 <option value="=" {{$bestpara[1]== "=" ?'selected':''}}> = </option>
                                 <option value=">" {{$bestpara[1]== ">" ?'selected':''}}> > </option>
                                 <option value=">=" {{$bestpara[1]== ">=" ?'selected':''}}> >= </option>
                                 <option value="<" {{$bestpara[1]== "<" ?'selected':''}}> < </option>
                                 <option value="<=" {{$bestpara[1]== "<=" ?'selected':''}}> <= </option>
                                 <option value="!=" {{$bestpara[1]== "!=" ?'selected':''}}> != </option>
                                 <option value="BETWEEN" {{$bestpara[1]== "BETWEEN" ?'selected':''}}>BETWEEN</option>
                                 <option value="NOT BETWEEN" {{$bestpara[1]== "NOT BETWEEN" ?'selected':''}}>NOT BETWEEN</option>
                            </select>
                             {!! $errors->first('bestdealcondition.*', '<p class="help-block">:message</p>') !!}
                        </div>
                        <div class="col-md-2">
                            <div class="col-md-9">
                                <label>Value<sup>*</sup></label>
                                <input type="text" name="bestdealvalue[]" value={{$bestpara[2]}}  class="bestdealvalue" id="bestdealvalue_{{$index}}"/>
                                {!! $errors->first('bestdealvalue.*', '<p class="help-block">:message</p>') !!}
                            </div>
                        </div>
                        @if($index > 0)
                        <div class="col-md-2">
                          <a href="javascript:void(0)" class="rm-btn remove_div"><i class="fa fa-close"></i></a>
                        </div>
                        @endif
                    </div>
                  @endforeach
                  @else
                    <div class="row bestdealcharge bestdeal-condition-row">
                        <div class="col-md-3">
                            <label>Parameters<sup>*</sup></label>
                            <select class="bestdealparameter select2 form-control" name="bestdealparameter[]" id="bestdealparameter_0">
                                <option value="" selected>Select Parameter</option>
                                <option  value="selling_price">Selling Price</option>
                                <option value="value">Bought Value</option>
                            </select>
                            <span id="bestdealparameter-error_0" class="all_err"></span>
                            {!! $errors->first('bestdealparameter.*', '<p class="help-block">:message</p>') !!}
                        </div>
                        <div class="col-md-2">
                            <label>Condition<sup>*</sup></label>
                            <select class="select2 form-control bestdealcondition" name="bestdealcondition[]" id="bestdealcondition_0">
                                <option value="" selected>Select Condition</option>
                                 <option value="="> = </option>
                                 <option value=">"> > </option>
                                 <option value=">="> >= </option>
                                 <option value="<"> < </option>
                                 <option value="<="> <= </option>
                                 <option value="!="> != </option>
                                 <option value="BETWEEN">BETWEEN</option>
                                 <option value="NOT BETWEEN">NOT BETWEEN</option>
                            </select>
                             {!! $errors->first('bestdealcondition.*', '<p class="help-block">:message</p>') !!}
                        </div>
                        <div class="col-md-2">
                            <div class="col-md-9">
                                <label>Value<sup>*</sup></label>
                                <input type="text" name="bestdealvalue[]"  class="bestdealvalue" id="bestdealvalue_0"/>
                                {!! $errors->first('bestdealvalue.*', '<p class="help-block">:message</p>') !!}
                            </div>
                        </div>
                    </div><!-- bestdeal-condition-row -->
                  @endif
                   <div class="bestdealappended-div"></div><!-- end of appended div -->
                </div><!-- bestdeal-condition -->
                <div class="row">
                    <div class="col-md-7"></div>
                    <div class="col-md-2"><i class="fa fa-plus bestdealaddOther" style=" cursor:pointer">Add More</i></div>
                </div>
                <!-- /.box-body -->
                <div class="box-footer">
                    
                </div><!-- /.footer -->
            </div>

            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">OTC Sale</h3>
                </div>
                <div class="box-body otc-condition">
                  <div class="row">
                    <div class="col-md-6">
                      <label>Incentive Type<sup>*</sup></label>
                      <div class="col-md-3">
                        <label class="radio-inline">
                            <input name="otc_incent_type" class="otc_incent_type" value="percentage" type="radio" {{isset($otc_setting)?(isset($otc_setting[0]['incentive_type'])&& $otc_setting[0]['incentive_type'] == 'percentage')?'checked':'':''}}/>Percentage
                        </label>
                      </div>
                      <div class="col-md-3">
                        <label class="radio-inline">
                            <input name="otc_incent_type" class="otc_incent_type" value="fixed" type="radio" {{isset($otc_setting)?(isset($otc_setting[0]['incentive_type'])&& $otc_setting[0]['incentive_type'] == 'fixed')?'checked':'':''}}/>Fixed
                        </label>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <label>Incentive Calculation<sup>*</sup></label>
                      <div class="col-md-6">
                        <label class="radio-inline">
                            <input name="otc_incent_calc" class="otc_incent_calc" value="single" type="radio" {{isset($otc_setting)?(isset($otc_setting[0]['calculation_type'])&& $otc_setting[0]['calculation_type'] == 'single')?'checked':'':''}}/>Individual Condition
                        </label>
                      </div>
                      <div class="col-md-6">
                        <label class="radio-inline">
                            <input name="otc_incent_calc" class="otc_incent_calc" value="all" type="radio" {{isset($otc_setting)?(isset($otc_setting[0]['calculation_type'])&& $otc_setting[0]['calculation_type'] == 'all')?'checked':'':''}}/>All Condition
                        </label>
                      </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-3" id="otc-inc_type_div">
                      <br>
                      <input type="number" name="otc_percentage" min="0" value="{{isset($otc_setting)?(isset($otc_setting[0]['incentive_percent'])?$otc_setting[0]['incentive_percent']:''):''}}" class="otc_percentage form-control-static"/>
                      <span id="otc-inc_type_show">%</span>
                    </div>
                  </div>
                  @if(isset($otc_setting) && count($otc_setting)>0)
                  @foreach(json_decode($otc_setting[0]['parameter']) as $index => $otcpara)
                    <div class="row otccharge otc-condition-row">
                        <div class="col-md-3">
                            <label>Parameters<sup>*</sup></label>
                            <select class="otcparameter select2 form-control" name="otcparameter[]" id="otcparameter_{{$index}}">
                                <option value="" >Select Parameter</option>
                                <option value="total_amount" {{$otcpara[0]== "total_amount" ?'selected':''}}>Total Amount</option>
                                <option value="ew_cost" {{$otcpara[0]== "ew_cost" ?'selected':''}}>EW Cost</option>
                                <option value="amc_cost" {{$otcpara[0]== "amc_cost" ?'selected':''}}>AMC Cost</option>
                                <option value="hjc_cost" {{$otcpara[0]== "hjc_cost" ?'selected':''}}>HJC Cost</option>
                            </select>
                            <span id="otcparameter-error_{{$index}}" class="all_err"></span>
                             {!! $errors->first('otcparameter.*', '<p class="help-block">:message</p>') !!}
                        </div>
                        <div class="col-md-2">
                            <label>Condition<sup>*</sup></label>
                            <select class="otccondition select2 form-control" name="otccondition[]" id="otccondition_{{$index}}">
                                <option value="" >Select Condition</option>
                                 <option value="=" {{$otcpara[1]== "=" ?'selected':''}}> = </option>
                                 <option value=">" {{$otcpara[1]== ">" ?'selected':''}}> > </option>
                                 <option value=">=" {{$otcpara[1]== ">=" ?'selected':''}}> >= </option>
                                 <option value="<" {{$otcpara[1]== "<" ?'selected':''}}> < </option>
                                 <option value="<=" {{$otcpara[1]== "<=" ?'selected':''}}> <= </option>
                                 <option value="!=" {{$otcpara[1]== "!=" ?'selected':''}}> != </option>
                                 <option value="BETWEEN" {{$otcpara[1]== "BETWEEN" ?'selected':''}}>BETWEEN</option>
                                 <option value="NOT BETWEEN" {{$otcpara[1]== "NOT BETWEEN" ?'selected':''}}>NOT BETWEEN</option>
                            </select>
                            {!! $errors->first('otccondition.*', '<p class="help-block">:message</p>') !!}
                        </div>
                        <div class="col-md-2">
                            <div class="col-md-9">
                                <label>Value<sup>*</sup></label>
                                <input type="text" name="otcvalue[]" value="{{$otcpara[2]}}" class="otcvalue" id="otcvalue_{{$index}}"/>
                                 {!! $errors->first('otcvalue.*', '<p class="help-block">:message</p>') !!}
                            </div>
                        </div>
                        @if($index > 0)
                        <div class="col-md-2">
                          <a href="javascript:void(0)" class="rm-btn remove_div"><i class="fa fa-close"></i></a>
                        </div>
                        @endif
                    </div>
                  @endforeach
                  @else
                    <div class="row otccharge otc-condition-row">
                        <div class="col-md-3">
                            <label>Parameters<sup>*</sup></label>
                            <select class="otcparameter select2 form-control" name="otcparameter[]" id="otcparameter_0">
                                <option value="" selected>Select Parameter</option>
                                <option value="total_amount">Total Amount</option>
                                <option value="ew_cost">EW Cost</option>
                                <option value="amc_cost">AMC Cost</option>
                                <option value="hjc_cost">HJC Cost</option>
                            </select>
                            <span id="otcparameter-error_0" class="all_err"></span>
                             {!! $errors->first('otcparameter.*', '<p class="help-block">:message</p>') !!}
                        </div>
                        <div class="col-md-2">
                            <label>Condition<sup>*</sup></label>
                            <select class="otccondition select2 form-control" name="otccondition[]" id="otccondition_0">
                                <option value="" selected>Select Condition</option>
                                 <option value="="> = </option>
                                 <option value=">"> > </option>
                                 <option value=">="> >= </option>
                                 <option value="<"> < </option>
                                 <option value="<="> <= </option>
                                 <option value="!="> != </option>
                                 <option value="BETWEEN">BETWEEN</option>
                                 <option value="NOT BETWEEN">NOT BETWEEN</option>
                            </select>
                            {!! $errors->first('otccondition.*', '<p class="help-block">:message</p>') !!}
                        </div>
                        <div class="col-md-2">
                            <div class="col-md-9">
                                <label>Value<sup>*</sup></label>
                                <input type="text" name="otcvalue[]" class="otcvalue" id="otcvalue_0"/>
                                 {!! $errors->first('otcvalue.*', '<p class="help-block">:message</p>') !!}
                            </div>
                        </div>
                    </div><!-- otc-condition-row -->
                  @endif
                   <div class="otcappended-div"></div><!-- end of appended div -->
                </div><!-- otc-condition -->
                <div class="row">
                    <div class="col-md-7"></div>
                    <div class="col-md-2"><i class="fa fa-plus otcaddOther" style=" cursor:pointer">Add More</i></div>
                </div>
                <!-- /.box-body -->
                <div class="box-footer">
                    
                </div><!-- /.footer -->
            </div>

            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Service</h3>
                </div>
                <div class="box-body service-condition">
                  <div class="row">
                    <div class="col-md-6">
                      <label>Incentive Type<sup>*</sup></label>
                      <div class="col-md-3">
                        <label class="radio-inline">
                            <input name="service_incent_type" class="service_incent_type" value="percentage" type="radio" {{isset($service_setting)?(isset($service_setting[0]['incentive_type'])&& $sale_setting[0]['incentive_type'] == 'percentage')?'checked':'':''}}/>Percentage
                        </label>
                      </div>
                      <div class="col-md-3">
                        <label class="radio-inline">
                            <input name="service_incent_type" class="service_incent_type" value="fixed" type="radio" {{isset($service_setting)?(isset($service_setting[0]['incentive_type'])&& $service_setting[0]['incentive_type'] == 'fixed')?'checked':'':''}}/>Fixed
                        </label>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <label>Incentive Calculation<sup>*</sup></label>
                      <div class="col-md-6">
                        <label class="radio-inline">
                            <input name="service_incent_calc" class="service_incent_calc" value="single" type="radio" {{isset($service_setting)?(isset($service_setting[0]['calculation_type'])&& $service_setting[0]['calculation_type'] == 'single')?'checked':'':''}}/>Individual Condition
                        </label>
                      </div>
                      <div class="col-md-6">
                        <label class="radio-inline">
                            <input name="service_incent_calc" class="service_incent_calc" value="all" type="radio" {{isset($service_setting)?(isset($service_setting[0]['calculation_type'])&& $service_setting[0]['calculation_type'] == 'all')?'checked':'':''}}/>All Condition
                        </label>
                      </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-3" id="service-inc_type_div">
                      <br>
                      <input type="number" name="service_percentage" value="{{isset($service_setting)?(isset($service_setting[0]['incentive_percent'])?$service_setting[0]['incentive_percent']:''):''}}" min="0" class="service_percentage form-control-static"/>
                      <span id="service-inc_type_show">%</span>
                    </div>
                  </div>
                  @if(isset($service_setting) && count($service_setting)>0)
                  @foreach(json_decode($service_setting[0]['parameter']) as $index => $servicepara)
                  <div class="row servicecharge service-condition-row">
                        <div class="col-md-3">
                            <label>Parameters<sup>*</sup></label>
                            <select class="serviceparameter select2 form-control" name="serviceparameter[]" id="serviceparameter_{{$index}}">
                                <option value="" >Select Parameter</option>
                                <option value="job_card_type" {{$servicepara[0]== "job_card_type" ?'selected':''}}>Job Card Type</option>
                                <option value="total_amount" {{$servicepara[0]== "total_amount" ?'selected':''}}>Total Amount</option>
                            </select>
                            <span id="serviceparameter-error_{{$index}}" class="all_err"></span>
                            {!! $errors->first('serviceparameter.*', '<p class="help-block">:message</p>') !!}
                        </div>
                        <div class="col-md-2">
                            <label>Condition<sup>*</sup></label>
                            <select class="servicecondition select2 form-control" name="servicecondition[]" id="servicecondition_{{$index}}">
                                <option value="" >Select Condition</option>
                                 <option value="=" {{$servicepara[1]== "=" ?'selected':''}}> = </option>
                                 <option value=">"  {{$servicepara[1]== ">" ?'selected':''}}> > </option>
                                 <option value=">=" {{$servicepara[1]== ">=" ?'selected':''}}> >= </option>
                                 <option value="<" {{$servicepara[1]== "<" ?'selected':''}}> < </option>
                                 <option value="<=" {{$servicepara[1]== "<=" ?'selected':''}}> <= </option>
                                 <option value="!=" {{$servicepara[1]== "!=" ?'selected':''}}> != </option>
                                 <option value="BETWEEN" {{$servicepara[1]== "BETWEEN" ?'selected':''}}>BETWEEN</option>
                                 <option value="NOT BETWEEN" {{$servicepara[1]== "NOT BETWEEN" ?'selected':''}}>NOT BETWEEN</option>
                            </select>
                             {!! $errors->first('servicecondition.*', '<p class="help-block">:message</p>') !!}
                        </div>
                        <div class="col-md-2">
                            <div class="col-md-9">
                                <label>Value<sup>*</sup></label>
                                <input type="text" name="servicevalue[]" value="{{$servicepara[2]}}" class="servicevalue" id="servicevalue_{{$index}}"/>  
                                {!! $errors->first('servicevalue.*', '<p class="help-block">:message</p>') !!}
                            </div>
                        </div>
                        @if($index > 0)
                        <div class="col-md-2">
                          <a href="javascript:void(0)" class="rm-btn remove_div"><i class="fa fa-close"></i></a>
                        </div>
                        @endif
                    </div>
                  @endforeach
                  @else
                    <div class="row servicecharge service-condition-row">
                        <div class="col-md-3">
                            <label>Parameters<sup>*</sup></label>
                            <select class="serviceparameter select2 form-control" name="serviceparameter[]" id="serviceparameter_0">
                                <option value="" selected>Select Parameter</option>
                                <option value="ex_showroom_price">Ex Showroom Price</option>
                                <option value="total_amount">Total Amount</option>
                            </select>
                            <span id="serviceparameter-error_0" class="all_err"></span>
                            {!! $errors->first('serviceparameter.*', '<p class="help-block">:message</p>') !!}
                        </div>
                        <div class="col-md-2">
                            <label>Condition<sup>*</sup></label>
                            <select class="servicecondition select2 form-control" name="servicecondition[]" id="servicecondition_0">
                                <option value="" selected>Select Condition</option>
                                 <option value="="> = </option>
                                 <option value=">"> > </option>
                                 <option value=">="> >= </option>
                                 <option value="<"> < </option>
                                 <option value="<="> <= </option>
                                 <option value="!="> != </option>
                                 <option value="BETWEEN">BETWEEN</option>
                                 <option value="NOT BETWEEN">NOT BETWEEN</option>
                            </select>
                             {!! $errors->first('servicecondition.*', '<p class="help-block">:message</p>') !!}
                        </div>
                        <div class="col-md-2">
                            <div class="col-md-9">
                                <label>Value<sup>*</sup></label>
                                <input type="text" name="servicevalue[]" class="servicevalue" id="servicevalue_0"/>  
                                {!! $errors->first('servicevalue.*', '<p class="help-block">:message</p>') !!}
                            </div>
                        </div>
                    </div><!-- service-condition-row -->
                  @endif
                   <div class="serviceappended-div"></div><!-- end of appended div -->
                </div><!-- service-condition -->
                <div class="row">
                    <div class="col-md-7"></div>
                    <div class="col-md-2"><i class="fa fa-plus serviceaddOther" style=" cursor:pointer">Add More</i></div>
                </div>
                <!-- /.box-body -->
                <div class="box-footer">
                    
                </div><!-- /.footer -->
            </div>

            <div class="row">
               <div class="submit-button">
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div><br><br>
            </div>
            </form>
      </section>

            <div id="myModal" class="modal fade" role="dialog">
                    <div class="modal-dialog">
                        <!-- Modal content-->
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                <h4 id="mo_title" class="modal-titles"></h4>
                            </div>
                            <div class="modal-body">
                                  <div class="alert alert-danger" id="js-msg-errors"></div>
                                  <div class="alert alert-success" id="js-msg-successs"></div>
                                  <div class="row">
                                    <div class="col-md-6">
                                          <input type="hidden" name="ids" id="ids"  class="input-css">
                                        <label for="">Minimum value<sup>*</sup></label>
                                        <input type="number" name="min" id="min" min='0' class="input-css">
                                    </div>
                                  </div><br><br>
                                  <div class="row">
                                      <div class="col-md-6">
                                        <label for="">Maximum value<sup>*</sup></label>
                                         <input type="number" name="max" id="max" min='0' class="input-css">
                                      </div>
                                  </div><br>
                                  <div class="modal-footer">
                                      <input type="button" id="submitbutton" value="Submit" class="btn btn-primary">&nbsp;&nbsp;
                                      <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                  </div>
                            </div>
        
                        </div>
                    </div>
            </div>
@endsection