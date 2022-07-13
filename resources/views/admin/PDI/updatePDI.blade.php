@extends($layout)
@section('title', __('Edit PDI'))
@section('breadcrumb')
<li><a href="#"><i class=""></i> {{__('Edit PDI')}} </a></li>
@endsection
@section('js')
<script src="/js/pages/PDI/pdi.js"></script>

<script>
//    $('#datepicker2').datepicker({
//        format: "yyyy",
//        viewMode: "years", 
//        minViewMode: "years"
//    });
    $('#date_of_repair').datepicker({
        format: "yyyy-mm-dd"
    });
    $('#date_of_damage').datepicker({
        format: "yyyy-mm-dd"
    });

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
    <form  action="/admin/PDI/edit/{{$pdi['id']}}" method="post">
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
                                <option value="{{$val['id']}}" {{((old('frame'))?((old('frame') == $val['id'])?'selected':'') : (($pdi['product_details_id'] == $val['id'])? 'selected' : '')  )}} >{{$val['frame']}}</option>
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
                        <input type="text" name="model" value="{{(old('model')?old('model'): $pdi['model_name'] )}}" readonly id="model" class="input-css inputValidation">
                        {!! $errors->first('model', '<p class="help-block">:message</p>') !!}
                    </div>

                </div>
                <div class="col-md-12 margin-bottom">
                    <div class="col-md-4">
                        <label for="color">Color <sup>*</sup></label>
                    </div>
                    <div class="col-md-8">
                        <input type="text" name="color" readonly id="color" value="{{(old('color')?old('color'): $pdi['color_code'] )}}"  class="input-css inputValidation">
                        {!! $errors->first('color', '<p class="help-block">:message</p>') !!}
                    </div>
                </div>
                <div class="col-md-12 margin-bottom">
                    <div class="col-md-4">
                        <label for="variant">Variant <sup>*</sup></label>
                    </div>
                    <div class="col-md-8">
                        <input type="text" name="variant" readonly id="variant" value="{{(old('variant')?old('variant'): $pdi['model_variant'] )}}"  class="input-css inputValidation">
                        {!! $errors->first('variant', '<p class="help-block">:message</p>') !!}

                    </div>
                </div>
                <div class="col-md-12 margin-bottom">
                    <div class="col-md-4">
                        <label for="date_of_damage">Date Of Damage/Reporting <sup>*</sup></label>
                    </div>
                    <div class="col-md-8">
                        <input type="text" name="date_of_damage" id="date_of_damage" data-date-end-date="0d" value="{{(old('date_of_damage')?old('date_of_damage'): $pdi['date_of_damage'] )}}"  class="input-css">
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
                                <option value="{{$val['id']}}" {{((old('damage_location'))?((old('damage_location') == $val['id'])?'selected':'') : (($pdi['damage_location'] == $val['id'])? 'selected' : '')  )}} >{{$val['name']}}</option>
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
                                <option value="{{$val['id']}}" {{((old('emp_name'))?((old('emp_name') == $val['id'])?'selected':'') : (($pdi['responsive_emp_id'] == $val['id'])? 'selected' : '')  )}} >{{$val['name']}}</option>
                            @endforeach
                        </select>
                        {!! $errors->first('emp_name', '<p class="help-block">:message</p>') !!}

                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="col-md-12 margin-bottom">
                    <div class="col-md-1">
                        <input type="radio" name="damage_reason" value="load_ref" {{((old('damage_reason'))?((old('damage_reason') == 'load_ref')?'checked':'') : (($pdi['load_ref'] == null)? '' : 'checked')  )}}>
                    </div>
                    <div class="col-md-3">
                        <label>Transit</label>
                    </div>
                    <div class="col-md-2">
                        <label>Load Ref#</label>
                    </div>
                    <div class="col-md-6">
                        <input type="text" name="load_ref" value="{{(old('load_ref')?old('load_ref'): $pdi['load_ref'] )}}" id="load_ref" class="input-css ">
                        {!! $errors->first('load_ref', '<p class="help-block">:message</p>') !!}

                    </div>
                </div>
                <div class="col-md-12 margin-bottom">
                    <div class="col-md-1">
                        <input type="radio" name="damage_reason" value="executive" {{((old('damage_reason'))?((old('damage_reason') == 'executive')?'checked':'') : (($pdi['sale_executive_id'] == null)? '' : 'checked')  )}}>
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
                                <option value="{{$val['id']}}" {{((old('executive'))?((old('executive') == $val['id'])?'selected':'') : (($pdi['sale_executive_id'] == $val['id'])? 'selected' : '')  )}} >{{$val['name']}}</option>
                            @endforeach
                        </select>
                        {!! $errors->first('executive', '<p class="help-block">:message</p>') !!}

                    </div>
                </div>
                <div class="col-md-12 margin-bottom">
                    <div class="col-md-1">
                        <input type="radio" name="damage_reason" {{((old('damage_reason'))?((old('damage_reason') == 'location')?'checked':'') : (($pdi['location'] == null)? '' : 'checked')  )}} value="location" >
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
                                <option value="{{$val['id']}}" {{((old('location'))?((old('location') == $val['id'])?'selected':'') : (($pdi['location'] == $val['id'])? 'selected' : '')  )}} >{{$val['name']}}</option>
                            @endforeach
                        </select>
                        {!! $errors->first('location', '<p class="help-block">:message</p>') !!}

                    </div>
                </div>
                <div class="col-md-12 margin-bottom">
                    <div class="col-md-1">
                        <input type="radio" name="damage_reason" value="driver" {{((old('damage_reason'))?((old('damage_reason') == 'driver')?'checked':'') : (($pdi['driver'] == null)? '' : 'checked')  )}}>
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
                                <option value="{{$val['id']}}" {{((old('driver'))?((old('driver') == $val['id'])?'selected':'') : (($pdi['driver'] == $val['id'])? 'selected' : '')  )}} >{{$val['name']}}</option>
                            @endforeach
                        </select>
                        {!! $errors->first('driver', '<p class="help-block">:message</p>') !!}

                    </div>
                </div>
                <div class="col-md-12 margin-bottom">
                    <div class="col-md-1">
                        <input type="radio" name="damage_reason" value="item_missing_qty" {{((old('damage_reason'))?((old('damage_reason') == 'item_missing_qty')?'checked':'') : (($pdi['item_missing'] == null)? '' : 'checked')  )}}>
                    </div>
                    <div class="col-md-3">
                        <label>Items Missing</label>
                    </div>
                    <div class="col-md-2">
                        <label>Quantity</label>
                    </div>
                    <div class="col-md-6">
                        <input type="text" name="item_missing_qty" value="{{(old('item_missing_qty')?old('item_missing_qty'): $pdi['item_missing'] )}}"  id="item_missing_qty" class="input-css">
                        {!! $errors->first('item_missing_qty', '<p class="help-block">:message</p>') !!}

                    </div>
                </div>
                <div class="col-md-12 margin-bottom">
                    <div class="col-md-3">
                        <label for="date_of_repair">Date Of Repair<sup> *</sup></label>
                    </div>
                    <div class="col-md-9">
                        <input type="text" name="date_of_repair" value="{{(old('date_of_repair')?old('date_of_repair'): $pdi['date_of_repair'] )}}" id="date_of_repair" class="  input-css">
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
                                <option value="{{$val['id']}}" {{((old('repair_loc'))?((old('repair_loc') == $val['id'])?'selected':'') : (($pdi['repair_location'] == $val['id'])? 'selected' : '')  )}}>{{$val['name']}}</option>
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
                        <textarea name="desc" id="desc"  class="input-css inputValidation">{{(old('desc')?old('desc'): $pdi['desc_of_accident'] )}}</textarea>
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
                <?php $count = ((old('desc_damaged'))? count(old('desc_damaged')) : count($pdi_details) ); ?>

                <input type="hidden" name="count-div" value="{{(old('count-div')?old('count-div'): count($pdi_details) )}}" hidden id="count-div">
                @if($count > 0)
                    <div class="row" id="main-div">
                        <div class="col-md-1">
                            <label>
                                <i class="fa fa-trash color-action margin-r-5 delete-div" ></i>
                                <i class="fa fa-plus margin add-more-div" style="color:blue;cursor: pointer;" ></i>
                            </label>
                        </div>
                        <div class="col-md-3">
                        <input type="text" name="desc_damaged[]" value = "{{((old('desc_damaged')[0]) ? old('desc_damaged')[0] : $pdi_details[0]['desc_of_damage_part'] )}}" id="desc-damaged_0" class="input-css inputValidation">
                            {!! $errors->first('desc_damaged[0]', '<p class="help-block">:message</p>') !!}
                        </div>
                        <div class="col-md-2">
                         <select name="repair_replace[]" id="repair-replace_0" class="input-css select2 inputValidation repair_replace">
                             <option value="" disabled="">Select Repair/Replace</option>
                             <option value="Repair" @if($pdi_details[0]['repair_replace'] == 'Repair') selected="" @endif>Repair</option>
                             <option value="Replace" @if($pdi_details[0]['repair_replace'] == 'Replace') selected="" @endif>Replace</option>
                         </select>
                         {!! $errors->first('repair_replace.0', '<p class="help-block">:message</p>') !!}
                    </div>
                        <div class="col-md-2">
                            <input type="text" name="part_number[]" value = "{{((old('part_number')[0]) ? old('part_number')[0] : $pdi_details[0]['part_number'] )}}" id="part-number_0" class="input-css inputValidation part_number">
                            {!! $errors->first('part_number[0]', '<p class="help-block">:message</p>') !!}
                        </div>
                        <div class="col-md-2">
                            <input type="text" name="part_amt[]" value = "{{((old('part_amt')[0]) ? old('part_amt')[0] : $pdi_details[0]['part_amt'] )}}" id="part-amt_0" class="input-css inputValidation part-calculation">
                            {!! $errors->first('part_amt[0]', '<p class="help-block">:message</p>') !!}
                        </div>
                        <div class="col-md-1">
                            <input type="text" name="lab_amt[]" value = "{{((old('lab_amt')[0]) ? old('lab_amt')[0] : $pdi_details[0]['lab_amt'] )}}" id="lab-amt_0" class="input-css lab-calculation">
                            {!! $errors->first('lab_amt[0]', '<p class="help-block">:message</p>') !!}
                        </div>
                        <div class="col-md-1">
                            <input type="text" name="total[]" id="total_0" value = "{{((old('total')[0]) ? old('total')[0] : $pdi_details[0]['total'] )}}" class="input-css inputValidation total-calculation">
                            {!! $errors->first('total[0]', '<p class="help-block">:message</p>') !!}
                        </div>
                    </div>
                @endif
                @if($count > 1)
                    @for($i = 1 ; $i < $count ; $i++)
                    <div class="row" id="sub-div-{{$i}}">
                        <div class="col-md-1">
                            <label>
                                <i class="fa fa-trash color-action margin-r-5 delete-div" ></i>
                                <i class="fa fa-plus margin add-more-div" style="color:blue;cursor: pointer;" ></i>
                            </label>
                        </div>
                        <div class="col-md-3">
                            <input type="text" name="desc_damaged[]" value = "{{((old('desc_damaged')[$i]) ? old('desc_damaged')[$i] : $pdi_details[$i]['desc_of_damage_part'] )}}"  id="desc-damaged_{{$i}}" class="input-css inputValidation">
                            {!! $errors->first('desc_damaged['.$i.']', '<p class="help-block">:message</p>') !!}
                        </div>
                        <div class="col-md-2">
                         <select name="repair_replace[]" id="repair-replace_{{$i}}" class="input-css select2 inputValidation repair_replace">
                             <option value="" disabled="">Select Repair/Replace</option>
                             <option value="Repair" @if($pdi_details[$i]['repair_replace'] == 'Repair') selected="" @endif>Repair</option>
                             <option value="Replace" @if($pdi_details[$i]['repair_replace'] == 'Replace') selected="" @endif>Replace</option>
                         </select>
                         {!! $errors->first('repair_replace['.$i.']', '<p class="help-block">:message</p>') !!}
                    </div>
                    <div class="col-md-2">
                            <input type="text" name="part_number[]" value = "{{((old('part_number')[$i]) ? old('part_number')[$i] : $pdi_details[$i]['part_number'] )}}" id="part-number_{{$i}}" class="input-css inputValidation part_number">
                            {!! $errors->first('part_number['.$i.']', '<p class="help-block">:message</p>') !!}
                        </div>
                        <div class="col-md-2">
                        <input type="text" name="part_amt[]" value = "{{((old('part_amt')[$i]) ? old('part_amt')[$i] : $pdi_details[$i]['part_amt'] )}}" id="part-amt_{{$i}}" class="input-css inputValidation part-calculation">
                            {!! $errors->first('part_amt['.$i.']', '<p class="help-block">:message</p>') !!}
                        </div>
                        <div class="col-md-1">
                        <input type="text" name="lab_amt[]" value = "{{((old('lab_amt')[$i]) ? old('lab_amt')[$i] : $pdi_details[$i]['lab_amt'] )}}" id="lab-amt_{{$i}}" class="input-css lab-calculation">
                            {!! $errors->first('lab_amt['.$i.']', '<p class="help-block">:message</p>') !!}
                        </div>
                        
                        <div class="col-md-1">
                        <input type="text" name="total[]" id="total_{{$i}}" value = "{{((old('total')[$i]) ? old('total')[$i] : $pdi_details[$i]['total'] )}}" class="input-css inputValidation total-calculation">
                            {!! $errors->first('total['.$i.']', '<p class="help-block">:message</p>') !!}
                        </div>
                    </div>
                    @endfor
                @endif
            </div>
            <div class="col-md-6" style="float:right">
                <div class="col-md-6" >
                    <label for="total_amount" style="text-align:right">Total </label>
                </div>
                <div class="col-md-6" >
                    <input type="text" name="total_amount" value="{{((old('total_amount'))? old('total_amount') : $pdi_details_total_amt)}}" id="total_amount" readonly class="input-css">
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