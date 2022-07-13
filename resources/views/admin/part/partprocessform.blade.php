@extends($layout)

@section('title', __('Part Process Form'))

@section('breadcrumb')
    <li><a href="#"><i class=""></i> {{__('Part Process Form')}} </a></li>
    
@endsection
@section('js')
<script> 
    $("#fuel").hide();
    var old_loader = @php echo json_encode((old('loader')) ? old('loader') : '' ); @endphp;
    if(old_loader != '')
    {
        showFuel();
    }
   $("#loader").on('change',function(){
    showFuel();
    });

    function showFuel()
    {
        var id = $("#loader").val();
    //   console.log(id);
       $.ajax({  
            url:"/admin/stock/get/loader/"+id,  
            method:"get",  
            success:function(data){ 
                if(data.type == 'ByRoad') {
                    $("#fuel").show();
                }else{
                    $("#fuel").hide();
                }
            }  
       });
    }
</script>
@endsection

@section('main_section')
    <section class="content">
            @include('admin.flash-message')
                @yield('content')
            <!-- general form elements -->
            <div class="box-header with-border">
            <form id="unload-data-validation" action="" method="post">
             @csrf
            <div class="box box-primary">
                <div class="box-header">
                    <input type="hidden" name="part_movement_id" value="{{$part_movement_id}}">
                </div>  
                <div class="row">
                    <div class="col-md-6">
                        
                            <label>From Store <sup>*</sup></label>
                            <input type="hidden" name="from_store" value="{{$part_movement['from_store']}}">
                            <span>{{$fromStore}}</span>
                        {!! $errors->first('fromStore', '<p class="help-block">:message</p>') !!}
                    </div>
                    <div class="col-md-6">
                        <label>To Store<sup>*</sup></label>
                        <span>{{$toStore}} </span>
                        {!! $errors->first('toStore', '<p class="help-block">:message</p>') !!}
                    </div>
            </div><br>
            @if($status == 'Pending')
            <div class="row">
                <div class="col-md-6">
                    <label>Loader Number<sup>*</sup></label>
                    <select name="loader" id="loader" class=" loader input-css select2 selectpicker" style="width:100%">
                            <option value="" disabled="" selected> Select Loader Number </option>
                            @for($j = 0 ; $j < count($loader) ; $j++)
                                <option value ="{{$loader[$j]['id']}}" {{ old('loader') == $loader[$j]['id'] ? 'selected' : '' }}>{{$loader[$j]['truck_number']}}</option>
                            @endfor
                    </select>
                            {!! $errors->first("loader", '<p class="help-block">:message</p>') !!}
                </div>
                <div class="col-md-4" id="fuel" >
                        <label>Fuel</label>
                        <input type="text" name="fuel" id="fuel" value="{{old('fuel')}}" class="input-css">
                        {!! $errors->first("fuel", '<p class="help-block">:message</p>') !!}
                </div>
            </div><br>
            @endif
            </div>
            @if(count($parts)>0)
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Accessories</h3>
                    </div>
                    <div class="box-body accessories-condition">

                        @foreach($parts as $index => $part_detail)

                          @if($part_detail['qty']>0)
                            <div class="row accessoriescharge accessories-condition-row">
                                <div class="col-md-3">
                                    <label>Part Number<sup>*</sup></label>
                                    <input type="hidden" name="part_number[]" value="{{$part_detail['id']}}" class="input-css partnumber" id="partnumber_{{$index}}"/>
                                    <input type="text" name ="part_name[]" value="{{((old('part_name')[$index])?(old('part_name')[$index]):$part_detail['partName'])}}" class="input-css partnumber" disabled="disabled" />
                                    {!! $errors->first('partnumber.*', '<p class="help-block">:message</p>') !!}
                                </div>
                                <div class="col-md-2">
                                    <label>Requested Quantity</label>
                                    <span id="available_qty_{{$index}}">{{$part_detail['qty']}}</span>
                                     
                                </div>
                                <div class="col-md-3">
                                    <div class="col-md-10">
                                        <label>Enter Quantity<sup>*</sup></label>
                                        <input type="number" name="quantity[]" min="0" value="{{((old('quantity')[$index])?(old('quantity')[$index]):$part_detail['qty'])}}" class="input-css quantity" id="quantity_{{$index}}" max="{{$part_detail['qty']}}"/>  
                                        {!! $errors->first('quantity.*', '<p class="help-block">:message</p>') !!}
                                    </div>
                                </div>
                            </div><!-- accessories-condition-row -->
                          @else
                            <div class="row accessoriescharge accessories-condition-row">
                                <div class="col-md-3">
                                    <label>Part Number<sup>*</sup></label>
                                     <span>{{$part_detail['partName']}}</span>
                                    {!! $errors->first('partnumber.*', '<p class="help-block">:message</p>') !!}
                                </div>
                                <div class="col-md-2">
                                    <label>Available Quantity</label>
                                    <span id="">{{$part_detail['move_qty']}}</span>
                                     
                                </div>
                                <div class="col-md-3">
                                    <div class="col-md-10">
                                        <label>Enter Quantity<sup>*</sup></label>
                                        <span>{{$part_detail['processed_qty']}}</span> 
                                        {!! $errors->first('quantity.*', '<p class="help-block">:message</p>') !!}
                                    </div>
                                </div>
                            </div>
                          @endif
                        @endforeach
                    </div><!-- accessories-condition -->
                    
                    <!-- /.box-body -->
                    <div class="box-footer">
                        
                    </div><!-- /.footer -->
                </div>
            @endif
            @if(count($addon)>0)
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Addon</h3>
                </div>
                 <div class="row margin" id="addon_data">
                    @foreach($addon as $key=>$addon_detail)
                    @if($addon_detail['qty'] > 0)
                    <div class="col-md-6">
                        <div class="col-md-6">
                            <label class="text-label">{{$addon_detail['display_name']}} <b>(max: {{$addon_detail['qty']}}) </b></label>
                        </div>
                        <div class="col-md-5 margin">
                            <input type="number" name="addon[{{$addon_detail['id']}}]" value="{{old('addon')?old('addon')[$addon_detail['id']]:$addon_detail['qty']}}" min="0"
                            max="{{$addon_detail['qty']}}" class="input-css">
                        </div>
                    </div>
                    @else
                    <div class="col-md-6">
                        <div class="col-md-6">
                            <label class="text-label">{{$addon_detail['display_name']}}</label>
                        </div>
                        <div class="col-md-5 margin">
                            <span>{{$addon_detail['processed_qty']}}</span>
                        </div>
                    </div>
                    @endif
                    @endforeach
                    
                 </div>
                <!-- /.box-body -->
                <div class="box-footer">
                </div><!-- /.footer -->
            </div>
            @endif
            @if(count($battery)>0)
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Battery</h3>
                </div>
                <div class="box-body battery-condition">
                    <div class="row batterycharge battery-condition-row">
                        <div class="col-md-5">
                            <label>Battery<sup>*</sup></label>
                            @if($status == 'Pending')
                                <select name="battery[]" class="select2 battery" id="battery_0" multiple="multiple" data-placeholder="Select Battery">
                                    @foreach($battery as $key=>$value)
                                        @if($value['qty'] > 0)
                                            @if(old('battery'))
                                                @if(in_array($value['id'],old('battery')))
                                                <option value="{{$value['id']}}" selected>{{$value['display_name']}}</option>
                                                @else
                                                <option value="{{$value['id']}}" >{{$value['display_name']}}</option>
                                                @endif
                                            @else
                                                <option value="{{$value['id']}}" selected>{{$value['display_name']}}</option>
                                            @endif
                                        @endif
                                    @endforeach
                               </select>
                            @else
                                <div class="col-md-6">
                                @foreach($battery as $key=>$value)
                                    <div class="col-md-6">
                                        <label class="text-label"><b>{{$value['display_name']}}</b></label>
                                    </div>
                                    <div class="col-md-5 margin">
                                        <span>{{$value['qty']}}</span>
                                    </div>
                                @endforeach
                                </div>
                            @endif
                            {!! $errors->first('battery.*', '<p class="help-block">:message</p>') !!}
                        </div>
                    </div><!-- battery-condition-row -->
                   <!-- end of appended div -->
                </div><!-- battery-condition -->
                
                <!-- /.box-body -->
                <div class="box-footer">
                    
                </div><!-- /.footer -->
            </div>
            @endif
            <div class="row">
                <div class="col-md-12">
                    @if($status == 'Pending')
                    <button type="submit" class="btn btn-success">Submit</button>
                    @endif
                </div>
                <br><br>    
            </div>  
        </form>
      </section>
@endsection