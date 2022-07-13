@extends($layout)

@section('title', __('Part Cancel Form'))

@section('breadcrumb')
    <li><a href="#"><i class=""></i> {{__('Part Process Form')}} </a></li>
    
@endsection
@section('js')
<script> 
    
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
            <input type="hidden" name="part_movement_id" value="{{$part_movement_id}}">
                <div class="box box-primary">
                    <div class="box-header with-border">
                    </div>
                    @if(count($parts_not_process)>0)
                    <div class="box-header with-border">
                        <h3 class="box-title">Accessories</h3>
                    </div>
                        @php $check_part = array_column($parts_not_process,'qty');
                             $part_sh_qty = array_sum($check_part);
                        @endphp
                        @if($part_sh_qty>0)
                        <div class="box-body accessories-condition">

                            @foreach($parts_not_process as $index => $part_detail)

                              @if($part_detail['qty']>0)
                                <div class="row accessoriescharge accessories-condition-row">
                                    <div class="col-md-3">
                                        <label>Part Number<sup>*</sup></label>
                                        <input type="hidden" name="part_number[]" value="{{$part_detail['id']}}" class="input-css partnumber" id="partnumber_{{$index}}"/>
                                        <input type="text" name ="part_name[]" value="{{((old('part_name')[$index])?(old('part_name')[$index]):$part_detail['partName'])}}" class="input-css partnumber" disabled="disabled" />
                                        {!! $errors->first('partnumber.*', '<p class="help-block">:message</p>') !!}
                                    </div>
                                    <div class="col-md-2">
                                        <label>Available Quantity</label>
                                        <span id="available_qty_{{$index}}">{{$part_detail['qty']}}</span>
                                         
                                    </div>
                                    <div class="col-md-3">
                                        <div class="col-md-10">
                                            <label>Enter Quantity<sup>*</sup></label>
                                            <input type="number" name="quantity[]" min="0" value="{{((old('quantity')[$index])?(old('quantity')[$index]):0)}}" class="input-css quantity" id="quantity_{{$index}}" max="{{$part_detail['qty']}}"/>  
                                            {!! $errors->first('quantity.*', '<p class="help-block">:message</p>') !!}
                                        </div>
                                    </div>
                                </div><!-- accessories-condition-row -->
                              @endif
                            @endforeach
                        </div>
                        @else
                            <div class="box-body battery-condition">
                                <div>
                                    No Data Found.
                                </div>
                            </div>
                        @endif
                    @endif
                    @if(count($addon_not_process)>0)
                        <div class="box-header with-border">
                            <h3 class="box-title">Addon</h3>   
                        </div>
                        @php $check_addon = array_column($addon_not_process,'qty');
                             $addon_sh_qty = array_sum($check_addon);
                        @endphp
                        @if($addon_sh_qty>0)
                        <div class="row margin" id="addon_data">
                            @foreach($addon_not_process as $key=>$addon_detail)
                            @if($addon_detail['qty'] > 0)
                            <div class="col-md-6">
                                <div class="col-md-6">
                                    <label class="text-label">{{$addon_detail['display_name']}} <b>(max: {{$addon_detail['qty']}}) </b></label>
                                </div>
                                <div class="col-md-5 margin">
                                    <input type="number" name="addon[{{$addon_detail['id']}}]" value="{{old('addon')?old('addon')[$addon_detail['id']]:0}}" min="0"
                                    max="{{$addon_detail['qty']}}" class="input-css">
                                </div>
                            </div>
                            
                            @endif
                            @endforeach
                        
                        </div>
                        @else
                            <div class="box-body battery-condition">
                                <div>
                                    No Data Found.
                                </div>
                            </div>
                        @endif
                    @endif
                    @if(count($battery_not_process)>0)
                        <div class="box-header with-border">
                            <h3 class="box-title">Battery</h3>
                        </div>
                        @php $check = array_column($battery_not_process,'qty');
                             $battery_sh_qty = array_sum($check);
                        @endphp

                        @if($battery_sh_qty > 0)
                        <div class="box-body battery-condition">
                            <div class="row batterycharge battery-condition-row">
                                <div class="col-md-5">
                                    <label>Battery<sup>*</sup></label>
                                    
                                        <select name="battery[]" class="select2 battery" id="battery_0" multiple="multiple" data-placeholder="Select Battery">
                                            @foreach($battery_not_process as $key=>$value)
                                                @if($value['qty'] > 0)
                                                    @if(old('battery'))
                                                        @if(in_array($value['id'],old('battery')))
                                                        <option value="{{$value['id']}}" selected>{{$value['display_name']}}</option>
                                                        @else
                                                        <option value="{{$value['id']}}" >{{$value['display_name']}}</option>
                                                        @endif
                                                    @else
                                                        <option value="{{$value['id']}}" >{{$value['display_name']}}</option>
                                                    @endif
                                                @endif
                                            @endforeach
                                       </select>
                                    
                                    {!! $errors->first('battery.*', '<p class="help-block">:message</p>') !!}
                                </div>
                            </div><!-- battery-condition-row -->
                           <!-- end of appended div -->
                        </div>
                        @else
                            <div class="box-body battery-condition">
                                <div>
                                    No Data Found.
                                </div>
                            </div>
                        @endif
                    @endif
                    <!-- /.box-body -->
                    <div class="box-footer">
                        
                    </div><!-- /.footer -->
                </div>
            
            @if(count($processed_data)>0)
                @foreach($processed_data as $in => $detail)
                    <div class="box box-primary">
                        <div class="box-header with-border">
                           <div class="col-md-6">
                              <div class="col-md-6">
                                <label> Process Date </label>
                              </div>
                              <div class="col-md-6">
                                <label class="text-label"> {{$detail['process_date']}} </label>
                              </div>
                            </div>
                            <div class="col-md-6">
                              <div class="col-md-6">
                                <label> Loader # </label>
                              </div>
                              <div class="col-md-6">
                                <label class="text-label"> {{$detail['loader_truck']}} </label>
                              </div>
                            </div> 
                        </div>
                        <div class="box-body processed-condition">

                            <div class="row processed charge processed-condition-row">
                                <div class="col-md-7">
                                    <div class="checkbox">
                                      <label><input type="checkbox" name="processed_id[]" class="processed_id" id="processed_id_{{$in}}" value="{{$detail['id']}}">Select this process to cancel. (This will cancel whole process.)</label>
                                    </div>
                                </div><br><br>
                            @foreach($detail['all_parts'] as $partsname => $stock)
                                <div class="row">
                                    <div class=" col-md-12 box-header with-border">
                                        <h3 class="box-title">{{ucwords($partsname)}}</h3>
                                    </div>
                                    <!-- <div class="col-md-12">
                                        <label></label>
                                    </div> -->
                                </div><br>
                                <div class="row"> 
                                @foreach($stock as $n => $v)
                                <div class="col-md-3">
                                    <label style="display: inline;">{{$v['name']}}</label>
                                    <input type="text" class="input-css" disabled="" value="{{$v['quantity']}}">
                                </div>
                                @endforeach
                                </div><br>
                                
                            @endforeach
                            </div>
                        </div>
                        <!-- /.box-body -->
                        <div class="box-footer">
                        </div><!-- /.footer -->
                    </div>
                @endforeach
            @endif
            <div class="row">
                <div class="col-md-12">
                    <button type="submit" class="btn btn-success">Submit</button>
                    
                </div>
                <br><br>    
            </div>  
        </form>
      </section>
@endsection