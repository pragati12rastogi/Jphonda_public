@extends($layout)

@section('title', __('Incentive Parameters'))

@section('breadcrumb')
    <li><a href="#"><i class=""></i> {{__('Incentive Parameters')}} </a></li>
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

    // $("#sale-inc_type_div").hide();
    // $("#bestdeal-inc_type_div").hide();
    // $("#otc-inc_type_div").hide();
    // $("#service-inc_type_div").hide();

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
          sale_incent_type:{ required:true },
          sale_incent_calc:{ required:true },
          store_id:{required:true}
          // sale_store: {required: true},
          // sale_store_qty: {required: true}
          
        },
        messages:
        {
          sale_incent_type:
          {
            required:"Please select a Incentive Type"
          },
          sale_incent_calc:{
            required:"Please select a Calculation Type"
          },
          store_id:{
            required:"Please select Store"
          },
          // sale_store:{
          //   required:"Please select a Store"
          // },
          // sale_store_qty:{
          //   required:"Please insert a Store Qunatity"
          // }
          
        
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
    // saleparameter,.salecondition,.salevalue,.bestdealvalue,.otcvalue,.servicevalue,.bestdealcondition,.otccondition,.servicecondition,.bestdealparameter,.otcparameter,.serviceparameter,.service_percentage,.otc_percentage,.bestdeal_percentage,.salepercentage
        $(".saleparameter,.salecondition,.salevalue,.salepercentage,.sale_from_date,.sale_to_date,.sale_incent_type").each(function (item) {
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
                      
                        '<div class="col-md-2">'+
                            '<label>Parameters<sup>*</sup></label>'+
                            '<select id="saleparameter_'+count+'" class="saleparameter append select2 form-control" name="saleparameter[]">'+
                            '<option value="" selected>Select Parameter</option>'+
                            '<option value="ex_showroom_price">Ex Showroom Price</option>'+
                            '<option value="total_amount">Total Amount</option>'+
                            '<option value="accessories_total_amount">Accessories Total Amount</option>'+
                            '<option value="finance_company">Finance Company</option>'+
                            '<option value="finance_amount">Finance Amount</option>'+
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
                            '<div id="finance_append_'+count+'">'+
                                '<label>Value<sup>*</sup></label>'+
                                '<input id="salevalue_'+count+'" class="salevalue form-control all_input_value_'+count+'" type="text" name="salevalue[]" />'+
                            '</div>'+
                        '</div>'+
                          '<div class="col-md-3">'+
                            '<label>Incentive Type<sup>*</sup></label>'+
                              '<div class="col-md-6">'+
                                '<label class="radio-inline">'+
                                    '<input name="sale_incent_type['+count+']" class="sale_incent_type s_inc_type_'+count+'" value="percentage" type="radio" onclick="type_val(this)" />Percentage </label>'+
                              '</div>'+
                              '<div class="col-md-6">'+
                                '<label class="radio-inline">'+
                                  '<input name="sale_incent_type['+count+']" class="sale_incent_type s_inc_type_'+count+'" value="fixed" type="radio" onclick="type_val(this)" />Fixed</label>'+
                              '</div>'+
                            '</div>'+
                          '<div class="col-md-2" >'+
                            '<br>'+
                            '<input type="number" name="salepercentage[]" min="0" class="salepercentage form-control" id="salepercentage_'+count+'"/>'+
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

$(document).on('change','.saleparameter',function(e){
          var pvalue=$(this).val();
          var proId=$(this).attr('id');
          var arr = proId.split('_');
          var index = arr[1]; 
          var type='saleparameter';

          if(pvalue == 'finance_company'){
              $("#salecondition_"+index).empty();
              $("#salecondition_"+index).append('<option value="" selected>Select Condition</option><option value="="> = </option><option value="!="> != </option>');
              $('#finance_append_'+index).empty();
              $('#finance_append_'+index).append('<label>Value<sup>*</sup></label>'+
                                '<select class="select2 form-control salevalue" name="salevalue[]" id="salevalue_0">'+
                                    '<option value="">Select Finance Company</option>'+
                                    @foreach($finance_company as $f_in => $f_val)
                                    '<option value="{{$f_val->id}}">{{$f_val->company_name}}</option>'+
                                    @endforeach
                                '</select>');
              $('select'
                ).select2();

          }else{
              $("#salecondition_"+index).empty();
              $("#salecondition_"+index).append('<option value="" selected>Select Condition</option>'+
                                 '<option value="="> = </option>'+
                                 '<option value=">"> > </option>'+
                                 '<option value=">="> >= </option>'+
                                 '<option value="<"> < </option>'+
                                 '<option value="<="> <= </option>'+
                                 '<option value="!="> != </option>'+
                                 '<option value="BETWEEN">BETWEEN</option>'+
                                 '<option value="NOT BETWEEN">NOT BETWEEN</option>');
              $('#finance_append_'+index).empty();
              $('#finance_append_'+index).append('<label>Value<sup>*</sup></label>'+
                                '<input type="text" name="salevalue[]" class="salevalue form-control all_input_value_'+index+'" id="salevalue_'+index+'"/>');
              
          }
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
                // if(type=='saleparameter'){
                //     type='Sale Parameter';
                //   }if(type=='bestdealparameter'){
                //     type='Bestdeal Parameter';
                //   }if(type=='otcparameter'){
                //     type='Otc Sale Parameter';
                //   }if(type=='serviceparameter'){
                //     type='Service Parameter';
                //   }   
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
      var sale_type = 's_inc_type_';

      var get_value = $('input:radio.s_inc_type_'+$get_row+':checked').val();

      if(get_value == 'percentage'){
        $('#salepercentage_'+$get_row).prop('max',100);
      }else{
        $('#salepercentage_'+$get_row).removeAttr('max');
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
            <form id="my-form1" action="/admin/incentive/program/sale" method="POST">
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
                            <input name="sale_incent_calc" {{(old('sale_incent_calc')=='all')?'checked':'' }} class="sale_incent_calc" value="all" type="radio"/>All Condition Satisfy
                        </label>
                      </div>
                      <div class="col-md-6">
                        <label class="radio-inline">
                            <input name="sale_incent_calc" {{(old('sale_incent_calc')=='single')?'checked':'' }} class="sale_incent_calc" value="single" type="radio"/>
                            Anyone Condition Satisfy
                        </label>
                      </div>
                      {!! $errors->first('sale_incent_calc', '<p class="help-block">:message</p>') !!}
                    </div>
                    <div class="col-md-6">
                      <label for="">Duration <sup></sup></label>
                      <div class="col-md-5 {{ $errors->has('sale_from_date') ? ' has-error' : ''}}">
                          <label>From : <sup>*</sup></label>
                          <input type="text" name="sale_from_date" id="from_date" class="sale_from_date input-css" required="" value="{{old('sale_from_date')}}">
                          {!! $errors->first('sale_from_date', '<p class="help-block">:message</p>') !!} 
                      </div>
                      <div class="col-md-5 {{ $errors->has('sale_to_date') ? ' has-error' : ''}}">
                          <label>To :</label>
                          <input type="text" name="sale_to_date" value="{{old('sale_to_date')}}" id="to_date" class="sale_to_date input-css" >
                          {!! $errors->first('sale_to_date', '<p class="help-block">:message</p>') !!} 
                      </div>
                    </div>
                  </div><br>
                    <div class="row charge sale-condition-row">
                        <div class="col-md-2">
                            <label>Parameters<sup>*</sup></label>
                            <select class="select2 form-control saleparameter" name="saleparameter[]" id="saleparameter_0">
                                <option value="" selected>Select Parameter</option>
                                <option value="ex_showroom_price">Ex Showroom Price</option>
                                <option value="total_amount">Total Amount</option>
                                <option value="accessories_total_amount">Accessories Total Amount</option>
                                <option value="finance_company">Finance Company</option>
                                <option value="finance_amount">Finance Amount</option>
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
                            <div id="finance_append_0">
                                <label>Value<sup>*</sup></label>
                                <input type="text" name="salevalue[]" class="salevalue form-control all_input_value_0" id="salevalue_0"/>
                                
                                {!! $errors->first('salevalue.*', '<p class="help-block">:message</p>') !!}
                            </div>
                        </div>
                        <div class="col-md-3">
                          <label>Incentive Type<sup>*</sup></label>
                          <div class="col-md-6">
                            <label class="radio-inline">
                                <input name="sale_incent_type[0]" class="sale_incent_type s_inc_type_0" value="percentage" type="radio" onclick="type_val(this)" />Percentage
                            </label>
                          </div>
                          <div class="col-md-6">
                            <label class="radio-inline">
                                <input name="sale_incent_type[0]" class="sale_incent_type s_inc_type_0" value="fixed" type="radio" onclick="type_val(this)" />Fixed
                            </label>
                          </div>
                          {!! $errors->first('sale_incent_type.*', '<p class="help-block">:message</p>') !!}
                        </div>
                      <div class="col-md-2" id="sale-inc_type_div">
                        <label>Incentive Value<sup>*</sup></label>
                        <input type="number" name="salepercentage[]" min="0" class="salepercentage form-control"
                        id="salepercentage_0"/>
                        {!! $errors->first('salepercentage.*', '<p class="help-block">:message</p>') !!}
                        <!-- <span id="sale-inc_type_show">%</span> -->
                      </div>
                    </div><!-- sale-condition-row -->
                <div class="saleappended-div"></div><!-- end of appended div -->
                </div><!-- sale-condition --><br>
                <div class="row">
                    <div class="col-md-10"></div>
                    <div class="col-md-2"><i class="fa fa-plus slaeaddOther" style=" cursor:pointer">Add More</i></div>
                </div><br>
                <div class="row">
                  <div class="col-md-12">
                    <label>Mandatory Conditions</label>
                  </div>
                    <div class="col-md-4">
                        <label>Store<sup>*</sup></label>
                        <select name="sale_store[]" class="select2 sale_store" id="sale_store" multiple="multiple" data-placeholder="Select Store">
                          @foreach($store as $in => $name)
                            <option value="{{$name['id']}}">{{$name['name']}}</option>
                          @endforeach
                        </select>
                        {!! $errors->first('sale_store', '<p class="help-block">:message</p>') !!}
                    </div>
                    <div class="col-md-3">
                        <label>Total Sale Quantity<sup>*</sup></label>
                        <input type="number" min="0" name="sale_store_qty" class="form-control sale_store_qty" id="sale_store_qty" />
                        {!! $errors->first('sale_store_qty', '<p class="help-block">:message</p>') !!}
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