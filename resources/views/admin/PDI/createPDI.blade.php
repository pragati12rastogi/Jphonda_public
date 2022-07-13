@extends($layout)
@section('title', __('PDI Claim Form'))
@section('breadcrumb')
<li><a href="#"><i class=""></i> {{__('PDI Claim Form')}} </a></li>
@endsection
@section('js')
<script src="/js/pages/PDI/pdi.js"></script>

<script>
//    $('#datepicker2').datepicker({
//        format: "yyyy",
//        viewMode: "years", 
//        minViewMode: "years"
//    });
    // $('.datepicker').datepicker({
    //     format: "yyyy-mm-dd"
    // });

      $(document).on('keyup','.part_number',function(){
                var partNumber=$(this).val();
                var part_id = $(this).attr('id');
                var arr = part_id.split('_');
                var index = arr[1];
                var repair_replace = $("#repair-replace_"+index).children("option:selected").val();
                  if(repair_replace=="Replace")
                  {
                       $.ajax({
                            url:'/admin/PDI/partnumberget',
                            data:{'partNumber':partNumber},
                            method:'GET',
                            dataType:'json',
                            success:function(result){
                              var data=result.price;
                                  $("#part-amt_"+index).val(data).attr('readonly', true);
                                   // $("#part-amt_"+index).attr('readonly', true); 
                                   calculation();
                            },
                            error:function(error){
                              $('#js-msg-error').html("Something Wen't Wrong "+error).show();
                            },

                           });
                  }

      }); 

</script>
@endsection
@section('css')
<style>
.color-action{
    color:red;
    cursor: pointer;
}
</style>
@endsection
@section('main_section')
<section class="content">
   @include('admin.flash-message')
   @yield('content')
   <!-- general form elements -->
   <div class="box-header with-border">
   <div class="box box-primary">
      <div class="box-header">
         <!--<h3 class="box-title">{{__('parts.mytitle')}} </h3>-->
      </div>
      <form  action="/admin/create/PDI" method="post">
         @csrf
         <div class="row">
            <div class="col-md-6">
                <div class="col-md-12 margin-bottom">
                    <div class="col-md-4">
                        <label for="frame">Frame # <sup>*</sup></label>
                    </div>
                    <div class="col-md-8">
                        <select type="text" name="frame" id="frame" class="input-css select2 selectValidation">
                            <option value="">Select Frame #</option>
                            @foreach($frame as $key => $val)
                                <option value="{{$val['id']}}">{{$val['frame']}}</option>
                            @endforeach
                        </select>
                        {!! $errors->first('frame', '<p class="help-block">:message</p>') !!}
                    </div>
                </div>
                <div class="col-md-12 margin-bottom">
                    <div class="col-md-4">
                        <label for="model">Model <sup>*</sup></label>
                    </div>
                    <div class="col-md-8">
                        <input type="text" name="model" readonly id="model" class="input-css inputValidation">
                        {!! $errors->first('model', '<p class="help-block">:message</p>') !!}
                    </div>

                </div>
                <div class="col-md-12 margin-bottom">
                    <div class="col-md-4">
                        <label for="color">Color <sup>*</sup></label>
                    </div>
                    <div class="col-md-8">
                        <input type="text" name="color" readonly id="color"  class="input-css inputValidation">
                        {!! $errors->first('color', '<p class="help-block">:message</p>') !!}
                    </div>
                </div>
                <div class="col-md-12 margin-bottom">
                    <div class="col-md-4">
                        <label for="variant">Variant <sup>*</sup></label>
                    </div>
                    <div class="col-md-8">
                        <input type="text" name="variant" readonly id="variant"  class="input-css inputValidation">
                        {!! $errors->first('variant', '<p class="help-block">:message</p>') !!}

                    </div>
                </div>
                <div class="col-md-12 margin-bottom">
                    <div class="col-md-4">
                        <label for="date_of_damage">Date Of Damage/Reporting <sup>*</sup></label>
                    </div>
                    <div class="col-md-8">
                        <input type="text" name="date_of_damage" id="date_of_damage" data-date-end-date="0d"  class="input-css datepicker">
                        {!! $errors->first('date_of_damage', '<p class="help-block">:message</p>') !!}

                    </div>
                </div>
                <div class="col-md-12 margin-bottom">
                    <div class="col-md-4">
                        <label for="damage_location">Damage Location <sup>*</sup></label>
                    </div>
                    <div class="col-md-8">
                        <select type="text" name="damage_location" id="damage_location" class="input-css select2 selectValidation">
                            <option value="">Select Store</option>
                            @foreach($store as $key => $val)
                                <option value="{{$val['id']}}">{{$val['name']}}</option>
                            @endforeach
                        </select>
                        {!! $errors->first('damage_location', '<p class="help-block">:message</p>') !!}

                    </div>
                </div>
                <div class="col-md-12 margin-bottom">
                    <div class="col-md-4">
                        <label for="emp_name">Responsible Employee Name <sup>*</sup></label>
                    </div>
                    <div class="col-md-8">
                        <select type="text" name="emp_name" id="emp_name" class="input-css select2 selectValidation">
                            <option value="">Select Employee Name</option>
                            @foreach($responsible_emp as $key => $val)
                                <option value="{{$val['id']}}">{{$val['name']}}</option>
                            @endforeach
                        </select>
                        {!! $errors->first('emp_name', '<p class="help-block">:message</p>') !!}

                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="col-md-12 margin-bottom">
                    <div class="col-md-1">
                        <input type="radio" name="damage_reason" value="load_ref" >
                    </div>
                    <div class="col-md-3">
                        <label>Transit</label>
                    </div>
                    <div class="col-md-2">
                        <label>Load Ref#</label>
                    </div>
                    <div class="col-md-6">
                        <input type="text" name="load_ref" id="load_ref" class="input-css ">
                        {!! $errors->first('load_ref', '<p class="help-block">:message</p>') !!}

                    </div>
                </div>
                <div class="col-md-12 margin-bottom">
                    <div class="col-md-1">
                        <input type="radio" name="damage_reason" value="executive" >
                    </div>
                    <div class="col-md-3">
                        <label>Unloading</label>
                    </div>
                    <div class="col-md-2">
                        <label>Executive</label>
                    </div>
                    <div class="col-md-6">
                        <select type="text" name="executive" id="executive" class="input-css select2 ">
                            <option value="">Select Sale Executive</option>
                            @foreach($sale_exe as $key => $val)
                                <option value="{{$val['id']}}">{{$val['name']}}</option>
                            @endforeach
                        </select>
                        {!! $errors->first('executive', '<p class="help-block">:message</p>') !!}

                    </div>
                </div>
                <div class="col-md-12 margin-bottom">
                    <div class="col-md-1">
                        <input type="radio" name="damage_reason" value="location" >
                    </div>
                    <div class="col-md-3">
                        <label>Storage</label>
                    </div>
                    <div class="col-md-2">
                        <label>Location</label>
                    </div>
                    <div class="col-md-6">
                        <select type="text" name="location" id="location" class="input-css select2 ">
                            <option value="">Select Store</option>
                            @foreach($store as $key => $val)
                                <option value="{{$val['id']}}">{{$val['name']}}</option>
                            @endforeach
                        </select>
                        {!! $errors->first('location', '<p class="help-block">:message</p>') !!}

                    </div>
                </div>
                <div class="col-md-12 margin-bottom">
                    <div class="col-md-1">
                        <input type="radio" name="damage_reason" value="driver" >
                    </div>
                    <div class="col-md-3">
                        <label>Inter-Location</label>
                    </div>
                    <div class="col-md-2">
                        <label>Driver</label>
                    </div>
                    <div class="col-md-6">
                        <select type="text" name="driver" id="driver" class="input-css select2 ">
                            <option value="">Select Driver</option>
                            @foreach($emp as $key => $val)
                                <option value="{{$val['id']}}">{{$val['name']}}</option>
                            @endforeach
                        </select>
                        {!! $errors->first('driver', '<p class="help-block">:message</p>') !!}

                    </div>
                </div>
                <div class="col-md-12 margin-bottom">
                    <div class="col-md-1">
                        <input type="radio" name="damage_reason" value="item_missing_qty" >
                    </div>
                    <div class="col-md-3">
                        <label>Items Missing</label>
                    </div>
                    <div class="col-md-2">
                        <label>Quantity</label>
                    </div>
                    <div class="col-md-6">
                        <input type="text" name="item_missing_qty" id="item_missing_qty" class="input-css">
                        {!! $errors->first('item_missing_qty', '<p class="help-block">:message</p>') !!}

                    </div>
                </div>
                <div class="col-md-12 margin-bottom">
                    <div class="col-md-3">
                        <label for="date_of_repair">Date Of Repair<sup> *</sup></label>
                    </div>
                    <div class="col-md-9">
                        <input type="text" name="date_of_repair" id="date_of_repair" class=" datepicker input-css">
                        {!! $errors->first('date_of_repair', '<p class="help-block">:message</p>') !!}

                    </div>
                </div>
                <div class="col-md-12 margin-bottom">
                    <div class="col-md-3">
                        <label for="repair_loc">Repair Location<sup> *</sup></label>
                    </div>
                    <div class="col-md-9">
                        <select type="text" name="repair_loc" id="repair_loc" class="input-css select2 selectValidation">
                            <option value="">Select Repair Location</option>
                            @foreach($store as $key => $val)
                                <option value="{{$val['id']}}">{{$val['name']}}</option>
                            @endforeach
                        </select>
                        {!! $errors->first('repair_loc', '<p class="help-block">:message</p>') !!}

                    </div>
                </div>
                <div class="col-md-12 margin-bottom">
                    <div class="col-md-3">
                        <label for="desc">Description Of Accident<sup> *</sup></label>
                    </div>
                    <div class="col-md-9">
                        <textarea name="desc" id="desc" class="input-css inputValidation"></textarea>
                        {!! $errors->first('desc', '<p class="help-block">:message</p>') !!}
                    </div>
                </div>
            </div>
            <div class="col-md-12 margin">
             <hr>
            </div>
            <div class="col-md-12 margin">
                <div class="col-md-1">
                    <label >Action</label>
                </div>
                <div class="col-md-3">
                    <label>Description Of Damaged Part/Missing Item</label>
                </div>
                <div class="col-md-2">
                    <label>Repair/Replace</label>
                </div>
                 <div class="col-md-2">
                    <label>Part Number.</label>
                </div>
                <div class="col-md-2">
                    <label>Part/Paint Amt.</label>
                </div>
                <div class="col-md-1">
                    <label>LAB Amt.</label>
                </div>
                <div class="col-md-1">
                    <label>Total</label>
                </div>

            </div>
            <div class="col-md-12 margin" id="append-div">
                <input type="hidden" name="count-div" value="0" hidden id="count-div">
                <div class="row" id="main-div">
                    <div class="col-md-1">
                        <label>
                            <i class="fa fa-trash color-action margin-r-5 delete-div" ></i>
                            <i class="fa fa-plus margin add-more-div" style="color:blue;cursor: pointer;" ></i>
                        </label>
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="desc_damaged[]" id="desc-damaged_0" class="input-css inputValidation">
                        {!! $errors->first('desc_damaged.0', '<p class="help-block">:message</p>') !!}
                    </div>
                    <div class="col-md-2">
                         <select name="repair_replace[]" id="repair-replace_0" class="input-css select2 inputValidation repair_replace">
                            <option value="" selected="" disabled="">Select Repair/Replace</option>
                             <option value="Repair">Repair</option>
                             <option value="Replace">Replace</option>
                         </select>
                         {!! $errors->first('repair_replace.0', '<p class="help-block">:message</p>') !!}
                    </div>
                    <div class="col-md-2">
                        <input type="text" name="part_number[]" id="part-number_0" class="input-css inputValidation part_number">
                        {!! $errors->first('part_number.0', '<p class="help-block">:message</p>') !!}
                    </div>
                    <div class="col-md-2">
                        <input type="text" name="part_amt[]" id="part-amt_0" class="input-css inputValidation part-calculation">
                        {!! $errors->first('part_amt.0', '<p class="help-block">:message</p>') !!}
                    </div>
                    <div class="col-md-1">
                        <input type="text" name="lab_amt[]" id="lab-amt_0" class="input-css lab-calculation inputValidation">
                        {!! $errors->first('lab_amt.0', '<p class="help-block">:message</p>') !!}
                    </div>
                    <div class="col-md-1">
                        <input type="text" name="total[]" id="total_0" class="input-css inputValidation total-calculation">
                        {!! $errors->first('total.0', '<p class="help-block">:message</p>') !!}
                    </div>
                </div>
            </div>
            <div class="col-md-6" style="float:right">
                <div class="col-md-6" >
                    <label for="total_amount" style="text-align:right">Total </label>
                </div>
                <div class="col-md-6" >
                    <input type="text" name="total_amount" id="total_amount" readonly class="input-css">
                    {!! $errors->first('total_amount', '<p class="help-block">:message</p>') !!}

                </div>
            </div>
            
         </div>
         <br>
         <br>
         <div class="row">
            <div class="col-md-12">
               {{-- <button type="button" class="btn btn-info"><i class="fa fa-plus"> </i> Add More</button> --}}
               <button type="submit" class="btn btn-success">Submit</button>
            </div>
            <br><br>    
         </div>
      </form>
   </div>
</section>
@endsection
{{-- {{CustomHelpers::coolText('hcjsd')}} --}}