@extends($layout)
@section('title', __('Update Invoice PDI'))
@section('breadcrumb')
<li><a href="#"><i class=""></i> {{__('Update Invoice PDI')}} </a></li>
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
</script>
@endsection
@section('css')
<style>
.color-action{
    color:red;
    cursor: no-drop;
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
    <form  action="/admin/PDI/update/invoice/{{$pdi['id']}}" method="post">
         @csrf
         <div class="row">
            <div class="col-md-6">
                <div class="col-md-12 margin-bottom">
                    <div class="col-md-4">
                        <label for="frame">Frame # <sup>*</sup></label>
                    </div>
                    <div class="col-md-8">
                        <select type="text" name="frame" disabled id="frame" class="input-css select2 selectValidation">
                            <option value="{{$pdi['frame']}}">{{$pdi['frame']}}</option>
                        </select>
                        {!! $errors->first('frame', '<p class="help-block">:message</p>') !!}
                    </div>
                </div>
                <div class="col-md-12 margin-bottom">
                    <div class="col-md-4">
                        <label for="model">Model <sup>*</sup></label>
                    </div>
                    <div class="col-md-8">
                        <input type="text" name="model" value="{{$pdi['model_name']}}" disabled id="model" class="input-css inputValidation">
                        {!! $errors->first('model', '<p class="help-block">:message</p>') !!}
                    </div>

                </div>
                <div class="col-md-12 margin-bottom">
                    <div class="col-md-4">
                        <label for="color">Color <sup>*</sup></label>
                    </div>
                    <div class="col-md-8">
                        <input type="text" name="color" disabled id="color" value="{{$pdi['color_code']}}"  class="input-css inputValidation">
                        {!! $errors->first('color', '<p class="help-block">:message</p>') !!}
                    </div>
                </div>
                <div class="col-md-12 margin-bottom">
                    <div class="col-md-4">
                        <label for="variant">Variant <sup>*</sup></label>
                    </div>
                    <div class="col-md-8">
                        <input type="text" name="variant" disabled id="variant" value="{{$pdi['model_variant']}}"  class="input-css inputValidation">
                        {!! $errors->first('variant', '<p class="help-block">:message</p>') !!}

                    </div>
                </div>
                <div class="col-md-12 margin-bottom">
                    <div class="col-md-4">
                        <label for="date_of_damage">Date Of Damage/Reporting <sup>*</sup></label>
                    </div>
                    <div class="col-md-8">
                        <input type="text" name="date_of_damage" disabled id="date_of_damage" data-date-end-date="0d" value="{{$pdi['date_of_damage']}}"  class="input-css">
                        {!! $errors->first('date_of_damage', '<p class="help-block">:message</p>') !!}

                    </div>
                </div>
                <div class="col-md-12 margin-bottom">
                    <div class="col-md-4">
                        <label for="damage_location">Damage Location <sup>*</sup></label>
                    </div>
                    <div class="col-md-8">
                        <select type="text" name="damage_location" disabled id="damage_location" class="input-css select2 selectValidation">
                            
                            <option value="{{$pdi['damage_location']}}"  >{{$pdi['damage_location']}}</option>
                            
                        </select>
                        {!! $errors->first('damage_location', '<p class="help-block">:message</p>') !!}

                    </div>
                </div>
                <div class="col-md-12 margin-bottom">
                    <div class="col-md-4">
                        <label for="emp_name">Responsible Employee Name <sup>*</sup></label>
                    </div>
                    <div class="col-md-8">
                        <select type="text" name="emp_name" disabled id="emp_name" class="input-css select2 selectValidation">
                            
                                <option value="{{$pdi['responsive_emp']}}" >{{$pdi['responsive_emp']}}</option>
                            
                        </select>
                        {!! $errors->first('emp_name', '<p class="help-block">:message</p>') !!}

                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="col-md-12 margin-bottom">
                    <div class="col-md-1">
                        <input type="radio" name="damage_reason" disabled value="load_ref" {{($pdi['load_ref'])? 'checked' : ''}}>
                    </div>
                    <div class="col-md-3">
                        <label>Transit</label>
                    </div>
                    <div class="col-md-2">
                        <label>Load Ref#</label>
                    </div>
                    <div class="col-md-6">
                        <input type="text" name="load_ref" disabled value="{{$pdi['load_ref']}}" id="load_ref" class="input-css ">
                        {!! $errors->first('load_ref', '<p class="help-block">:message</p>') !!}

                    </div>
                </div>
                <div class="col-md-12 margin-bottom">
                    <div class="col-md-1">
                        <input type="radio" name="damage_reason" disabled value="executive" {{($pdi['sale_executive'])? 'checked' : ''}}>
                    </div>
                    <div class="col-md-3">
                        <label>Unloading</label>
                    </div>
                    <div class="col-md-2">
                        <label>Executive</label>
                    </div>
                    <div class="col-md-6">
                        <select type="text" name="executive" disabled id="executive" class="input-css select2 ">
                            
                            <option value="{{$pdi['sale_executive']}}"  >{{$pdi['sale_executive']}}</option>
                        
                        </select>
                        {!! $errors->first('executive', '<p class="help-block">:message</p>') !!}

                    </div>
                </div>
                <div class="col-md-12 margin-bottom">
                    <div class="col-md-1">
                        <input type="radio" name="damage_reason" disabled {{($pdi['location'])? 'checked' : ''}} value="location" >
                    </div>
                    <div class="col-md-3">
                        <label>Storage</label>
                    </div>
                    <div class="col-md-2">
                        <label>Location</label>
                    </div>
                    <div class="col-md-6">
                        <select type="text" name="location" disabled id="location" class="input-css select2 ">
                            
                                <option value="{{$pdi['location']}}"  >{{$pdi['location']}}</option>
                            
                        </select>
                        {!! $errors->first('location', '<p class="help-block">:message</p>') !!}

                    </div>
                </div>
                <div class="col-md-12 margin-bottom">
                    <div class="col-md-1">
                        <input type="radio" name="damage_reason" disabled value="driver" {{($pdi['driver'])? 'checked' : ''}}>
                    </div>
                    <div class="col-md-3">
                        <label>Inter-Location</label>
                    </div>
                    <div class="col-md-2">
                        <label>Driver</label>
                    </div>
                    <div class="col-md-6">
                        <select type="text" name="driver"  id="driver" disabled class="input-css select2 ">
                            
                                <option value="{{$pdi['driver']}}"  >{{$pdi['driver']}}</option>
                        </select>
                        {!! $errors->first('driver', '<p class="help-block">:message</p>') !!}

                    </div>
                </div>
                <div class="col-md-12 margin-bottom">
                    <div class="col-md-1">
                        <input type="radio" name="damage_reason"  value="item_missing_qty" disabled {{($pdi['item_missing'])? 'checked' : ''}}>
                    </div>
                    <div class="col-md-3">
                        <label>Items Missing</label>
                    </div>
                    <div class="col-md-2">
                        <label>Quantity</label>
                    </div>
                    <div class="col-md-6">
                        <input type="text" name="item_missing_qty"  value="{{$pdi['item_missing']}}" disabled  id="item_missing_qty" class="input-css">
                        {!! $errors->first('item_missing_qty', '<p class="help-block">:message</p>') !!}

                    </div>
                </div>
                <div class="col-md-12 margin-bottom">
                    <div class="col-md-3">
                        <label for="date_of_repair">Date Of Repair<sup> *</sup></label>
                    </div>
                    <div class="col-md-9">
                        <input type="text" name="date_of_repair"  value="{{$pdi['date_of_repair']}}"  disabled id="date_of_repair" class="  input-css">
                        {!! $errors->first('date_of_repair', '<p class="help-block">:message</p>') !!}
                    </div>
                </div>
                <div class="col-md-12 margin-bottom">
                    <div class="col-md-3">
                        <label for="repair_loc">Repair Location<sup> *</sup></label>
                    </div>
                    <div class="col-md-9">
                        <select type="text" name="repair_loc" id="repair_loc" disabled class="input-css select2 selectValidation">
                            <option value="{{$pdi['repair_location']}}" >{{$pdi['repair_location']}}</option>
                        </select>
                        {!! $errors->first('repair_loc', '<p class="help-block">:message</p>') !!}

                    </div>
                </div>
                <div class="col-md-12 margin-bottom">
                    <div class="col-md-3">
                        <label for="desc">Description Of Accident<sup> *</sup></label>
                    </div>
                    <div class="col-md-9">
                        <textarea name="desc" id="desc" disabled  class="input-css inputValidation">{{$pdi['desc_of_accident']}}</textarea>
                        {!! $errors->first('desc', '<p class="help-block">:message</p>') !!}
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <br>
                <div class="col-md-4">
                    <label>Hi-Rise Invoice <sup>*</sup></label>
                    <input type="text" name="invoice" id="invoice" value="{{$pdi['hirise_invoice']}}" class="input-css">
                    {!! $errors->first('invoice', '<p class="help-block">:message</p>') !!}
                </div>
                <div class="col-md-4">
                    <label>Voucher Number<sup>*</sup></label>
                    <input type="text" name="voucher" id="voucher" value="{{$pdi['voucher_no']}}" class="input-css">
                     {!! $errors->first('voucher', '<p class="help-block">:message</p>') !!}
                </div>
                <div class="col-md-4">
                    <label>Debit Amount <sup>*</sup></label>
                <input type="text" name="debit_amt" value="{{$pdi['debit_amt']}}" id="debit_amt" class="input-css">
                 {!! $errors->first('debit_amt', '<p class="help-block">:message</p>') !!}
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
                <div class="col-md-1">
                    <label>Part/Paint Amt.</label>
                </div>
                <div class="col-md-1">
                    <label>LAB Amt.</label>
                </div>
                <div class="col-md-1">
                    <label>Repair Amt.</label>
                </div>
                <div class="col-md-1">
                    <label>Total</label>
                </div>

            </div>
            <div class="col-md-12 margin" id="append-div">
                <?php $count = count($pdi_details); ?>

                <input type="hidden" name="count-div" value="{{count($pdi_details)}}" hidden id="count-div">
                @if($count > 0)
                    @for($i = 0 ; $i < $count ; $i++)
                    <div class="row" id="sub-div-{{$i}}">
                        <div class="col-md-1">
                            <label>
                                <i  class="fa fa-trash color-action margin-r-5 " ></i>
                                <i  class="fa fa-plus margin " style="color:blue;cursor:no-drop;" ></i>
                            </label>
                        </div>
                        <div class="col-md-3">
                            <input type="text" name="desc_damaged[]" disabled value = "{{$pdi_details[$i]['desc_of_damage_part']}}"  id="desc-damaged_{{$i}}" class="input-css inputValidation">
                            {!! $errors->first('desc_damaged['.$i.']', '<p class="help-block">:message</p>') !!}
                        </div>
                        <div class="col-md-2">
                         <select name="repair_replace[]" disabled id="repair-replace_0" class="input-css select2 inputValidation">
                             <option value="Repair" @if($pdi_details[0]['repair_replace'] == 'Repair') selected="" @endif>Repair</option>
                             <option value="Replace" @if($pdi_details[0]['repair_replace'] == 'Replace') selected="" @endif>Replace</option>
                         </select>
                         {!! $errors->first('repair_replace.0', '<p class="help-block">:message</p>') !!}
                    </div>
                     <div class="col-md-2">
                            <input type="text" name="part_number[]" disabled value = "{{((old('part_number')[0]) ? old('part_number')[0] : $pdi_details[0]['part_number'] )}}" id="part-number_0" class="input-css inputValidation">
                            {!! $errors->first('part_number[0]', '<p class="help-block">:message</p>') !!}
                        </div>
                        <div class="col-md-1">
                        <input type="text" name="part_amt[]" disabled value = "{{$pdi_details[$i]['part_amt']}}" id="part-amt_{{$i}}" class="input-css inputValidation part-calculation">
                            {!! $errors->first('part_amt['.$i.']', '<p class="help-block">:message</p>') !!}
                        </div>
                        <div class="col-md-1">
                        <input type="text" name="lab_amt[]" disabled value = "{{$pdi_details[$i]['lab_amt']}}" id="lab-amt_{{$i}}" class="input-css lab-calculation">
                            {!! $errors->first('lab_amt['.$i.']', '<p class="help-block">:message</p>') !!}
                        </div>
                        <div class="col-md-1">
                            <input type="text" name="repair_amt[]"  value = "{{$pdi_details[$i]['repair_amt']}}" id="repair-amt_{{$i}}" class="input-css inputValidation lab-calculation">
                                {!! $errors->first('repair_amt.'.$i.'', '<p class="help-block">:message</p>') !!}
                                <input type="hidden" hidden value="{{$pdi_details[$i]['id']}}" name="repair[]">
                        </div>
                        <div class="col-md-1">
                        <input type="text" name="total[]" disabled id="total_{{$i}}" value = "{{$pdi_details[$i]['total']}}" class="input-css inputValidation total-calculation">
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
                    <input type="text" name="total_amount" disabled value="{{$pdi_details_total_amt}}" id="total_amount" readonly class="input-css">
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