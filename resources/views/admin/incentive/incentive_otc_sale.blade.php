@extends($layout)

@section('title', __('Incentive Programs'))

@section('breadcrumb')
    <li><a href="#"><i class=""></i> {{__('Incentive Programs')}} </a></li>
    <style>
    .rm-btn {
    font-size: 24px;
    font-weight: bold;
    position: absolute;
    left: 10px;
    color: #c20302;
    margin-top: 25px;
     }
    .all_err{
      color: red;
    }

    </style>
@endsection
@section('js')
<script>

$(document).ready(function() {


    $(".select2").on("select2:close", function(e) {
        $(this).valid();
    });
    $.validator.addMethod("notValidIfSelectFirst", function(value, element, arg) {
        return arg !== value;
    }, "This field is required.");

    $(".first_hide").hide();

});
$(function(){

    $("#my-form1").validate({ 

        rules:
        {
          otc_from_date : {required: true},
          otc_to_date : {required: true},
          // otc_store: {required: true},
          // otc_store_qty: {required: true},
          store_id:{required:true},
          otc_incent_calc:{ required:true }
        },
        messages:
        {
          otc_from_date:{
            required:"Please select Start Date"
          },
          otc_to_date:{
            required:"Please select a End Date"
          },
          store_id:{
            required:"Please select Store"
          },
          // otc_store:{
          //   required:"Please select a Store"
          // },
          // otc_store_qty:{
          //   required:"Please insert a Store Qunatity"
          // },
          otc_incent_calc:{
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
    
        $(".otcparameter,.otccondition,.otcvalue,.otc_percentage,.otc_from_date,.otc_to_date,.otc_store,.otc_incent_type").each(function (item) {
            $(this).rules("add", {
                required: true,
            });
        });
        // $(".select2").removeClass("error");
     
   
});

     $('#js-msg-errors').hide();
     $('#js-msg-successs').hide();

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


    $(".otcaddOther").click(function(){
                var ocount = $('.otccharge').length;
              
                $('#ocount').val(ocount);
                $(".otcappended-div").append(
                    '<div class=" row otc-condition-row otccharge appended-content">'+
                        '<div class="col-md-2">'+
                            '<label>Parameters<sup>*</sup></label>'+
                            '<select  id="otcparameter_'+ocount+'" class="otcparameter select2 form-control append" name="otcparameter[]">'+
                            '<option value="" selected>Select Parameter</option>'+
                            '<option value="total_amount">Total Amount</option>'+
                            '<option value="accessories_total_amount">Accessories Total Amount</option>'+
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
                            '<div class="">'+
                                '<label>Value<sup>*</sup></label>'+
                                '<input id="otcvalue_'+ocount+'" type="text" class="otcvalue append form-control" name="otcvalue[]" />'+
                            '</div>'+
                        '</div>'+
                        '<div class="col-md-3">'+
                            '<label>Incentive Type<sup>*</sup></label>'+
                              '<div class="col-md-6">'+
                                '<label class="radio-inline">'+
                                    '<input name="otc_incent_type['+ocount+']" class="otc_incent_type o_inc_type_'+ocount+'" value="percentage" type="radio" onclick="type_val(this)" />Percentage </label>'+
                              '</div>'+
                              '<div class="col-md-6">'+
                                '<label class="radio-inline">'+
                                  '<input name="otc_incent_type['+ocount+']" class="otc_incent_type o_inc_type_'+ocount+'" value="fixed" type="radio" onclick="type_val(this)" />Fixed</label>'+
                              '</div>'+
                            '</div>'+
                          '<div class="col-md-2" >'+
                            '<br>'+
                            '<input type="number" name="otc_percentage[]" min="0" class="otc_percentage form-control" id="otc_percentage_'+ocount+'"/>'+
                          '</div>'+
                        '<div class="col-md-1">'+
                          '<a href="javascript:void(0)" class="rm-btn"><i class="fa fa-close"></i></a>'+
                        '</div>'+
                    '</div>');
                    $('select'
                ).select2();
                
            });


$(document).on('click','.rm-btn',function(e){
  $(this).parents(".appended-content").remove();
 
});

 // var finance_comp = @php echo json_encode($finance_company); @endphp;

$(document).on('change','.otcparameter',function(e){
          var pvalue=$(this).val();
          var proId=$(this).attr('id');
          var arr = proId.split('_');
          var index = arr[1]; 
          var type='otcparameter';
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
                  
            }else{
              $('#'+type+"-error_"+index).html('');
            }
         });
    }
    $(document).on('click','.remove_div',function(){
        $(this).parent().parent().remove();
    })
    var startDate=null;
    var endDate=null;
    $(document).ready(function(){
      $('#from_date').datepicker({
         format: "dd-mm-yyyy"
         })
        .on('changeDate', function(ev){
          startDate=new Date(ev.date.getFullYear(),ev.date.getMonth(),ev.date.getDate(),0,0,0);
          if(endDate!=null&&endDate!='undefined'){
            if(endDate<startDate){
                $("#from_date").val("");
            }
          }
        });
      $("#to_date").datepicker({
         format: "dd-mm-yyyy"
         })
        .on("changeDate", function(ev){
          endDate=new Date(ev.date.getFullYear(),ev.date.getMonth(),ev.date.getDate(),0,0,0);
          if(startDate!=null&&startDate!='undefined'){
            if(endDate<startDate){
              $("#to_date").val("");
            }
          }
        });
    });

    function type_val(e){
      $get_name = $(e).attr('name');
      $split1 = $get_name.split('[');
      $split2 = $split1[1].split(']');
      $get_row = $split2[0];
      
      var get_value = $('input:radio.o_inc_type_'+$get_row+':checked').val();

      if(get_value == 'percentage'){
        $('#otc_percentage_'+$get_row).prop('max',100);
      }else{
        $('#otc_percentage_'+$get_row).removeAttr('max');
      }

    }
</script>

@endsection

@section('main_section')
    <section class="content">
            @include('admin.flash-message')
                @yield('content')

                @include('layouts.incentive.incentive_tab')
            <!-- general form elements -->
            <form id="my-form1" action="/admin/incentive/program/otcsale" method="POST">
                @csrf
            <input type="text" name="cat_type" value="otc_sale" class="cat_type" style="opacity:0">
            {!! $errors->first('cat_type', '<p class="help-block">:message</p>') !!}

            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">OTC Sale</h3>
                </div>
                <div class="box-body otc-condition">
                  <div class="row">
                    <div class="col-md-6">
                        <label for="">Store <sup>*</sup></label>
                        <select class="select2 form-control store_id" name="store_id" id="store_id">
                            <option value="" selected>Select Store</option>
                            @foreach($store as $in => $name)
                              <option value="{{$name['id']}}">{{$name['name']}}</option>
                            @endforeach
                        </select>
                        {!! $errors->first('store_id', '<p class="help-block">:message</p>') !!}
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-6">
                      <label>Required Condition<sup>*</sup></label>
                      <div class="col-md-6">
                        <label class="radio-inline">
                            <input name="otc_incent_calc" {{(old('otc_incent_calc')=='all')?'checked':'' }} class="otc_incent_calc" value="all" type="radio"/>All Condition Satisfy
                        </label>
                      </div>
                      <div class="col-md-6">
                        <label class="radio-inline">
                            <input name="otc_incent_calc" {{(old('otc_incent_calc')=='single')?'checked':'' }} class="otc_incent_calc" value="single" type="radio"/>
                            Anyone Condition Satisfy
                        </label>
                      </div>
                      {!! $errors->first('otc_incent_calc', '<p class="help-block">:message</p>') !!}
                    </div>
                    <div class="col-md-6">
                      <label for="">Duration <sup></sup></label>
                      <div class="col-md-5 {{ $errors->has('otc_from_date') ? ' has-error' : ''}}">
                          <label>From : <sup>*</sup></label>
                          <input type="text" name="otc_from_date" id="from_date" class="otc_from_date input-css" required="" value="{{old('otc_from_date')}}">
                          {!! $errors->first('otc_from_date', '<p class="help-block">:message</p>') !!} 
                      </div>
                      <div class="col-md-5 {{ $errors->has('otc_to_date') ? ' has-error' : ''}}">
                          <label>To :</label>
                          <input type="text" name="otc_to_date" value="{{old('otc_to_date')}}" id="to_date" class="otc_to_date input-css" >
                          {!! $errors->first('otc_to_date', '<p class="help-block">:message</p>') !!} 
                      </div>
                    </div>
                  </div><br>
                    <div class="row otccharge otc-condition-row">
                        <div class="col-md-2">
                            <label>Parameters<sup>*</sup></label>
                            <select class="otcparameter select2 form-control" name="otcparameter[]" id="otcparameter_0">
                                <option value="" selected>Select Parameter</option>
                                <option value="total_amount">Total Amount</option>
                                <option value="accessories_total_amount">Accessories Total Amount</option>
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
                            <div class="">
                                <label>Value<sup>*</sup></label>
                                <input type="text" name="otcvalue[]" class="otcvalue form-control" id="otcvalue_0"/>
                                 {!! $errors->first('otcvalue.*', '<p class="help-block">:message</p>') !!}
                            </div>
                        </div>
                        <div class="col-md-3">
                          <label>Incentive Type<sup>*</sup></label>
                          <div class="col-md-6">
                            <label class="radio-inline">
                                <input name="otc_incent_type[0]" class="otc_incent_type o_inc_type_0" value="percentage" type="radio"  onclick="type_val(this)"/>Percentage
                            </label>
                          </div>
                          <div class="col-md-6">
                            <label class="radio-inline">
                                <input name="otc_incent_type[0]" class="otc_incent_type o_inc_type_0" value="fixed" type="radio" onclick="type_val(this)"/>Fixed
                            </label>
                          </div>
                          {!! $errors->first('otc_incent_type.*', '<p class="help-block">:message</p>') !!}
                        </div>
                        <div class="col-md-2" id="otc-inc_type_div">
                          <label>Incentive Value<sup>*</sup></label>
                          <input type="number" name="otc_percentage[]" min="0" class="otc_percentage form-control"
                          id="otc_percentage_0"/>
                          {!! $errors->first('otc_percentage.*', '<p class="help-block">:message</p>') !!}
                          <!-- <span id="sale-inc_type_show">%</span> -->
                        </div>
                    </div><!-- otc-condition-row -->
                  
                   <div class="otcappended-div"></div><!-- end of appended div -->
                </div><!-- otc-condition -->
                <div class="row">
                    <div class="col-md-10"></div>
                    <div class="col-md-2"><i class="fa fa-plus otcaddOther" style=" cursor:pointer">Add More</i></div>
                </div><br>
                <div class="row">
                  <div class="col-md-12">
                    <label>Mandatory Conditions</label>
                  </div>
                    <div class="col-md-4">
                        <label>Store<sup>*</sup></label>
                        <select name="otc_store[]" class="select2 otc_store" id="otc_store" multiple="multiple" data-placeholder="Select Store">
                          @foreach($store as $in => $name)
                            <option value="{{$name['id']}}">{{$name['name']}}</option>
                          @endforeach
                        </select>
                        {!! $errors->first('otc_store', '<p class="help-block">:message</p>') !!}
                    </div>
                    <div class="col-md-3">
                        <label>Total Otc Sale Quantity<sup>*</sup></label>
                        <input type="number" min="0" name="otc_store_qty" class="form-control otc_store_qty" id="otc_store_qty" />
                        {!! $errors->first('otc_store_qty', '<p class="help-block">:message</p>') !!}
                    </div>
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