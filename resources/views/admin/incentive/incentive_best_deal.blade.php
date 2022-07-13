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
          best_from_date : {required: true},
          best_to_date : {required: true},
          store_id:{required:true},
          bestdeal_incent_calc:{ required:true }
          // otc_incent_calc:{ required:true },
          // service_incent_calc:{ required:true }
        },
        messages:
        {
          best_from_date:{
            required:"Please select Start Date"
          },
          best_to_date:{
            required:"Please select a End Date"
          },
          // best_store:{
          //   required:"Please select a Store"
          // },
          // best_store_qty:{
          //   required:"Please insert a Store Qunatity"
          // },
          
          store_id:{
            required:"Please select store"
          },
          bestdeal_incent_calc:{
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
    // saleparameter,.salecondition,.salevalue,.bestdealvalue,.otcvalue,.servicevalue,.bestdealcondition,.otccondition,.servicecondition,.bestdealparameter,.otcparameter,.serviceparameter,.service_percentage,.otc_percentage,.bestdeal_percentage,.salepercentage
        $(".bestdealparameter,.bestdealcondition,.bestdealvalue,.bestdeal_percentage,.best_from_date,.best_to_date,.bestdeal_incent_type").each(function (item) {
            $(this).rules("add", {
                required: true,
            });
        });
        // $(".select2").removeClass("error");
     
   
});

     $('#js-msg-errors').hide();
     $('#js-msg-successs').hide();


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


    $(".bestdealaddOther").click(function(){
                var bcount = $('.bestdealcharge').length;
              
                $('#bcount').val(bcount);
                $(".bestdealappended-div").append(
                    '<div class=" row bestdeal-condition-row bestdealcharge appended-content">'+
                        '<div class="col-md-2">'+
                            '<label>Parameters<sup>*</sup></label>'+
                            '<select id="bestdealparameter_'+bcount+'" class="bestdealparameter select2 form-control append" name="bestdealparameter[]">'+
                            '<option value="" selected>Select Parameter</option>'+
                            '<option value="total_amount">Total Amount</option>'+                            '<option value="finance_company">Finance Company</option>'+
                            '<option value="finance_amount">Finance Amount</option>'+
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
                            '<div class="finance_append_'+bcount+'">'+
                                '<label>Value<sup>*</sup></label>'+
                                '<input id="bestdealvalue_'+bcount+'" class="bestdealvalue form-control append" type="text" name="bestdealvalue[]" />'+
                            '</div>'+
                        '</div>'+
                        '<div class="col-md-3">'+
                            '<label>Incentive Type<sup>*</sup></label>'+
                              '<div class="col-md-6">'+
                                '<label class="radio-inline">'+
                                    '<input name="bestdeal_incent_type['+bcount+']" class="bestdeal_incent_type b_inc_type_'+bcount+'" value="percentage" type="radio" onclick="type_val(this)" />Percentage </label>'+
                              '</div>'+
                              '<div class="col-md-6">'+
                                '<label class="radio-inline">'+
                                  '<input name="bestdeal_incent_type['+bcount+']" class="bestdeal_incent_type b_inc_type_'+bcount+'" value="fixed" type="radio" onclick="type_val(this)" />Fixed</label>'+
                              '</div>'+
                            '</div>'+
                          '<div class="col-md-2" >'+
                            '<br>'+
                            '<input type="number" name="bestdeal_percentage[]" min="0" class="bestdeal_percentage form-control" id="bestdeal_percentage_'+bcount+'"/>'+
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

$(document).on('change','.bestdealparameter',function(e){
          var pvalue=$(this).val();
          var proId=$(this).attr('id');
          var arr = proId.split('_');
          var index = arr[1]; 
          var type='bestdealparameter';

          if(pvalue == 'finance_company'){
              $("#bestdealcondition_"+index).empty();
              $("#bestdealcondition_"+index).append('<option value="" selected>Select Condition</option><option value="="> = </option><option value="!="> != </option>');
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
              $("#bestdealcondition_"+index).empty();
              $("#bestdealcondition_"+index).append('<option value="" selected>Select Condition</option>'+
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
      
      var get_value = $('input:radio.b_inc_type_'+$get_row+':checked').val();

      if(get_value == 'percentage'){
        $('#bestdeal_percentage_'+$get_row).prop('max',100);
      }else{
        $('#bestdeal_percentage_'+$get_row).removeAttr('max');
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
            <form id="my-form1" action="/admin/incentive/program/bestdeal" method="POST">
                @csrf
            <input type="text" name="cat_type" value="bestdeal" class="cat_type" style="opacity:0">
            {!! $errors->first('cat_type', '<p class="help-block">:message</p>') !!}

            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Bestdeal</h3>
                </div>
                <div class="box-body bestdeal-condition">
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
                            <input name="bestdeal_incent_calc" {{(old('bestdeal_incent_calc')=='all')?'checked':'' }} class="bestdeal_incent_calc" value="all" type="radio"/>All Condition Satisfy
                        </label>
                      </div>
                      <div class="col-md-6">
                        <label class="radio-inline">
                            <input name="bestdeal_incent_calc" {{(old('bestdeal_incent_calc')=='single')?'checked':'' }} class="bestdeal_incent_calc" value="single" type="radio"/>
                            Anyone Condition Satisfy
                        </label>
                      </div>
                      {!! $errors->first('bestdeal_incent_calc', '<p class="help-block">:message</p>') !!}
                    </div>
                    <div class="col-md-6">
                      <label for="">Duration <sup></sup></label>
                      <div class="col-md-5 {{ $errors->has('best_from_date') ? ' has-error' : ''}}">
                          <label>From : <sup>*</sup></label>
                          <input type="text" name="best_from_date" id="from_date" class="best_from_date input-css" required="" value="{{old('best_from_date')}}">
                          {!! $errors->first('best_from_date', '<p class="help-block">:message</p>') !!} 
                      </div>
                      <div class="col-md-5 {{ $errors->has('best_to_date') ? ' has-error' : ''}}">
                          <label>To :</label>
                          <input type="text" name="best_to_date" value="{{old('best_to_date')}}" id="to_date" class="best_to_date input-css" >
                          {!! $errors->first('best_to_date', '<p class="help-block">:message</p>') !!} 
                      </div>
                    </div>
                  </div><br>
                  <div class="row bestdealcharge bestdeal-condition-row">
                      <div class="col-md-2">
                          <label>Parameters<sup>*</sup></label>
                          <select class="bestdealparameter select2 form-control" name="bestdealparameter[]" id="bestdealparameter_0">
                              <option value="" selected>Select Parameter</option>
                                <option value="total_amount">Total Amount</option>
                                <option value="finance_company">Finance Company</option>
                                <option value="finance_amount">Finance Amount</option>
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
                          <div class="finance_append_0">
                              <label>Value<sup>*</sup></label>
                              <input type="text" name="bestdealvalue[]"  class="bestdealvalue form-control" id="bestdealvalue_0"/>
                              {!! $errors->first('bestdealvalue.*', '<p class="help-block">:message</p>') !!}
                          </div>
                      </div>
                      <div class="col-md-3">
                          <label>Incentive Type<sup>*</sup></label>
                          <div class="col-md-6">
                            <label class="radio-inline">
                                <input name="bestdeal_incent_type[0]" class="bestdeal_incent_type b_inc_type_0" value="percentage" type="radio" onclick="type_val(this)" />Percentage
                            </label>
                          </div>
                          <div class="col-md-6">
                            <label class="radio-inline">
                                <input name="bestdeal_incent_type[0]" class="bestdeal_incent_type b_inc_type_0" value="fixed" type="radio" onclick="type_val(this)" />Fixed
                            </label>
                          </div>
                          {!! $errors->first('sale_incent_type.*', '<p class="help-block">:message</p>') !!}
                      </div>
                      <div class="col-md-2" id="best-inc_type_div">
                        <label>Incentive Value<sup>*</sup></label>
                        <input type="number" name="bestdeal_percentage[]" min="0" class="bestdeal_percentage form-control"
                        id="bestdeal_percentage_0"/>
                        {!! $errors->first('salepercentage.*', '<p class="help-block">:message</p>') !!}
                        <!-- <span id="sale-inc_type_show">%</span> -->
                      </div>
                  </div><!-- bestdeal-condition-row -->
                  
                  <div class="bestdealappended-div"></div><!-- end of appended div -->
                </div><!-- bestdeal-condition --><br>
                <div class="row">
                    <div class="col-md-10"></div>
                    <div class="col-md-2"><i class="fa fa-plus bestdealaddOther" style=" cursor:pointer">Add More</i></div>
                </div><br>
                <div class="row">
                  <div class="col-md-12">
                    <label>Mandatory Conditions</label>
                  </div>
                    <div class="col-md-4">
                        <label>Store<sup>*</sup></label>
                        <select name="best_store[]" class="select2 best_store" id="best_store" multiple="multiple" data-placeholder="Select Store">
                          @foreach($store as $in => $name)
                            <option value="{{$name['id']}}">{{$name['name']}}</option>
                          @endforeach
                        </select>
                        {!! $errors->first('best_store', '<p class="help-block">:message</p>') !!}
                    </div>
                    <div class="col-md-3">
                        <label>Total Best Deal Sale Quantity<sup>*</sup></label>
                        <input type="number" min="0" name="best_store_qty" class="form-control best_store_qty" id="best_store_qty" />
                        {!! $errors->first('best_store_qty', '<p class="help-block">:message</p>') !!}
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