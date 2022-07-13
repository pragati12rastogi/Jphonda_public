@extends($layout)

@section('title', __('Part Movement'))

@section('breadcrumb')
    <li><a href="#"><i class=""></i> {{__('Part Movement')}} </a></li>
    
@endsection
@section('css')
    <style type="text/css">
        .js-msg-error{
            color:red;
        }
        .rm-btn{
            position: absolute;
            top: 35px;
        }
    </style>
    
@endsection
@section('js')
<script> 

    $(document).ready(function(){
        console.log(oldaddon);
        if(oldFromStore != ''){
            filter_addonproduct(oldFromStore);
        }

    });

    var global_battery ={};

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

    $(document).on('blur','.partnumber',function(e){

        var part_no = $(this).val();
        var el = $(this);
        
        var id=$(this).attr('id');
        var arr = id.split('_');
        var index = arr[1];  
        var class_name = 'partnumber';

        $(el).parent().find("span.part-error").remove();

        if (part_no != null && part_no != undefined && part_no != '') {
            
            var res_validate_part_no = validate_part_no(part_no,index,class_name);
            if(res_validate_part_no == 'same_value'){
                $(el).parent().append("<span class='part-error' style='color:red'>This Part # Already Selected.</span>");
                return false;
            }
        }
        
        var store_id=$( "#fromStore option:selected" ).val();
        if(store_id==''){
            $('#no_store').html('Please Select Store Name').show();
            $('#fromStore').focus();
            $("#partnumber_"+index).val('').trigger('change');
            
        }else{
            
                $.ajax({
                    url:'/admin/part/check/part/available',
                    data: {'part_no':part_no,'store_id':store_id},
                    method:'GET',
                    success:function(result){
                        console.log(result);
                      if(result.quantity >= 0){
                        $('#js-msg-errors_'+index).hide();
                        $("#available_qty_"+index).text(result.quantity);
                        $(document).find("#quantity_"+index).prop('max',result.quantity);
                      }else if (result == 'part_error') {
                        $('#js-msg-errors_'+index).html('Part number not available').show();
                        $("#partnumber_"+index).val('');
                      }else{
                        $('#js-msg-errors_'+index).html('Quantity is empty or zero').show();
                      }
                      
                    },
                    error:function(error){
                        alert("something wen't wrong.");
                    }
                });
        }
    });

      $(".accessoriesaddOther").click(function(){
                var count = $('.accessoriescharge').length;
              
                $('#count').val(count);
                $(".accessoriesappended-div").append(
                    '<div class=" row accessories-condition-row accessoriescharge appended-content">'+
                        '<div class="col-md-3">'+
                            '<label>Part Number<sup>*</sup></label>'+
                            '<input type="text" name="part_number[]" required class="input-css partnumber" id="partnumber_'+count+'"/>'+
                            '<span id="js-msg-errors_'+count+'" class="js-msg-error"></span>'+
                        '</div>'+
                        '<div class="col-md-2">'+
                            '<label>Available Quantity</label>'+
                            '<span id="available_qty_'+count+'"></span>'+
                        '</div>'+
                        '<div class="col-md-3">'+
                            '<div class="col-md-10">'+
                                '<label>Enter Quantity<sup>*</sup></label>'+
                                '<input type="number" required name="quantity[]" min="1" class="input-css quantity" id="quantity_'+count+'"/>'+
                            '</div>'+
                        '</div>'+
                        '<div class="col-md-1">'+
                            '<a href="javascript:void(0)" class="rm-btn"><i class="fa fa-close"></i></a>'+
                        '</div>'+
                    '</div>');
            });

        $(".addonaddOther").click(function(){
                var acount = $('.addoncharge').length;
                $('#count').val(acount);
                $(".addonappended-div").append(
                    '<div class="row addon-condition-row addoncharge appended-content">'+

                    '<div class="col-md-3"><br>'+
                        '<select name="addon[]" required class="select2 addon" id="addon_'+acount+'" onchange="get_model_qty(this)">'+
                         '<option value="">Select Model</option>'+
                            @foreach($new_product_models as $k => $v)
                                    '<option value="{{$v['om_addon']}}" {{(old('addon') == $v['om_addon']) ? 'selected' : ''}} > {{$v['model_name']}} </option>'+
                                @endforeach
                            '</select>'+
                        '</div>'+
                        '<div class="col-md-3"><br>'+
                            '<input type="number" name="addonqty[]" min="1" class="input-css addonqty"  id="addonqty_'+acount+'">'+
                        '</div>'+
                        '<div class="col-md-1">'+
                            '<a href="javascript:void(0)" class="rm-btn"><i class="fa fa-close"></i></a>'+
                        '</div>'+
                     '</div>');
                    $('select'
                ).select2();
            });


        $(".batteryaddOther").click(function(){
                var bcount = $('.batterycharge').length;
                $('#count').val(bcount);
                var str_append_bt = '<option value="">Select Battery</option>'
                $.each(global_battery, function(key, value) {
                    str_append_bt +='<option value="' + value.id + '">' + value.frame + '</option>';
                });
                $(".batteryappended-div").append(
                    '<div class="row battery-condition-row batterycharge appended-content">'+

                        '<div class="col-md-5">'+
                            '<label>Battery<sup>*</sup></label>'+
                             '<select name="battery['+bcount+'][]" class="select2 addon" id="battery_'+bcount+'" multiple="multiple" data-placeholder="Select Battery" required>'+str_append_bt+
                               /*@foreach ($battery as $key)
                                    '<option value="{{$key['id']}}" {{(old('battery') == $key['id'])? 'selected':''}} >{{$key['frame']}}</option>'+
                                @endforeach*/
                            '</select>'+
                        '</div>'+
                        '<div class="col-md-1">'+
                            '<a href="javascript:void(0)" class="rm-btn"><i class="fa fa-close"></i></a>'+
                        '</div>'+
                     '</div>');
                    $('select'
                ).select2();
            });

      
     function filter_addonproduct(i){
         
        var store = $("#fromStore").children("option:selected").val();
        $('#no_store').hide();
        $('#ajax_loader_div').css('display','block');
        $.ajax({
            type:"GET",
            url:"/admin/part/filter/addonproduct/api/",
            data:{'store':store},
            success: function(result){
                
                console.log(result);
                if(result.battery){
                    $("#battery_0").empty();
                    if(result.battery.length>0){

                        global_battery = result.battery; 
                        var str = '<option value="">Select Battery</option>';
                        $.each(result.battery, function(key, value) {
                            if(oldbattery_arr.length>0){
                                if(oldbattery_arr[0][key] == value.id){
                                    str += '<option value="' + value.id + '"selected>' + value.frame + '</option>';
                                }else{
                                    str += '<option value="' + value.id + '">' + value.frame + '</option>';
                                }
                            }else{
                                str += '<option value="' + value.id + '">' + value.frame + '</option>';
                            }
                            
                        });
                            $("#battery_0").append(str);
                        }
                }

                if (result.vehicle_addon) {
                    console.log(result.vehicle_addon.length);
                    if(result.vehicle_addon.length>0){
                        var html='';
                        $("#addon_data").empty();
                         $.each(result.vehicle_addon, function(key, value) {
                                var inp_value=0;
                                if(value.va_key == 'mirror'){
                                     inp_value = oldmirror;
                                }else if(value.va_key == 'toolkit'){
                                     inp_value = oldtoolkit;
                                }else if(value.va_key == 'first_aid_kit'){
                                    inp_value = oldfirst_aid_kit;
                                }else if(value.va_key == 'saree_guard'){
                                    inp_value = oldsaree_guard;
                                }else if(value.va_key == 'bike_keys'){
                                    inp_value = oldbike_keys;
                                }

                                html+='<div class="col-md-6">'+
                                    '<div class="col-md-6">'+
                                        '<label class="text-label">'+value.va_name+' </label>'+
                                    '</div>'+
                                    '<div class="col-md-5 margin">'+
                                        '<input type="number" name="'+value.va_key+'" value="'+inp_value+'" min="0" max="'+value.va_qty+'" class="input-css">'+
                                   '</div>'+
                                '</div>';
                                
                            });
                         $("#addon_data").html(html);
                    }
                    
                    $('#ajax_loader_div').css('display','none');
                }
            }
        })
     }      

    function get_model_qty(i){
        console.log(i)
        var store = $("#fromStore").val();
        if(store == "" || store == null){
            $('#no_store').html('Please Select Store Name').show();
            $('#fromStore').focus();
            var form_id = "#"+i.id;
            $(document).find(form_id).val('');
            return false;
        }
        var model_selected = i.value;
        var model_id = i.id;

        if(model_selected == ''){
            divide_v = model_id.split("_");
            get_id_v = divide[1];
            input_id_v = '#addonqty_'+get_id;
            $(document).find(input_id_v).removeAttribute('max');
        }

        $.ajax({
            type:"GET",
            url:"/admin/part/addon/model/store/api",
            data:{'store':store,'om_name':model_selected},
            success: function(result){
                
                divide = model_id.split("_");
                get_id = divide[1];
                input_id = '#addonqty_'+get_id;

                if(result.qty != null && result.qty != ''){
                    $(document).find(input_id).prop('max',result.qty);
                }else{
                    $(document).find(input_id).prop('max',0);
                }

                       
            }
        })
     }

    var oldFromStore = @php echo json_encode((old('fromStore'))?old('fromStore'):''); @endphp;
    var oldbattery_arr = @php echo json_encode((old('battery'))?old('battery'):''); @endphp;
    var oldmirror = @php echo json_encode((old('mirror'))?old('mirror'):0); @endphp;
    var oldtoolkit = @php echo json_encode((old('toolkit'))?old('toolkit'):0); @endphp;
    var oldfirst_aid_kit = @php echo json_encode((old('first_aid_kit'))?old('first_aid_kit'):0); @endphp;
    var oldsaree_guard = @php echo json_encode((old('saree_guard'))?old('saree_guard'):0); @endphp;
    var oldbike_keys = @php echo json_encode((old('bike_keys'))?old('bike_keys'):0); @endphp;

    var oldaddon = @php echo json_encode((old('addon'))?old('addon'):0); @endphp;

    $(document).on('click','.rm-btn',function(e){
      $(this).parents(".appended-content").remove();
     
    });
    $(document).on('click','.remove_div',function(){
            $(this).parent().remove();
        })
</script>

    

@endsection

@section('main_section')
    <section class="content">
            @include('admin.flash-message')
                @yield('content')
            <!-- general form elements -->
            <div class="box-header with-border">
            <form id="unload-data-validation" action="/admin/part/movement" method="post">
                    @csrf
            <div class="box box-primary">
                <div class="box-header">
                </div>  
                <div class="row">
                    <div class="col-md-6">
                        <label>From Store <sup>*</sup></label>
                        <select name="fromStore" class="input-css select2 selectpicker fromStore" id="fromStore"  onchange="filter_addonproduct(this)">
                            <option selected value=''>Select From Store</option>
                            @foreach($store as $key)
                                <option value="{{$key['id']}}" {{(old('fromStore') == $key['id'])? 'selected':''}}>{{$key['name']}}</option>
                            @endforeach
                        </select>
                        <span id="no_store" style="color:red;display:none" ></span>
                        {!! $errors->first('fromStore', '<p class="help-block">:message</p>') !!}
                    </div>
                    <div class="col-md-6">
                        <label>To Store<sup>*</sup></label>
                        <select name="toStore" class="input-css select2 selectpicker" id="toStore">
                        <option selected value=''>Select To Store</option>
                            @foreach($store as $key)
                            <option value="{{$key['id']}}" {{(old('toStore') == $key['id'])? 'selected':''}}>{{$key['name']}}</option>
                            @endforeach
                        </select>
                        {!! $errors->first('toStore', '<p class="help-block">:message</p>') !!}
                    </div>
            </div><br>
            </div>
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Accessories</h3>
                </div>
                <div class="box-body accessories-condition">
                    <div class="row accessoriescharge accessories-condition-row">
                        <div class="col-md-3">
                            <label>Part Number<sup>*</sup></label>
                             <input type="text" name="part_number[]" value="{{((old('part_number')[0])?(old('part_number')[0]):'')}}" class="input-css partnumber" id="partnumber_0"/>
                             <span id="js-msg-errors_0" class="js-msg-error"></span>
                            {!! $errors->first('partnumber.*', '<p class="help-block">:message</p>') !!}
                        </div>
                        <div class="col-md-2">
                            <label>Available Quantity</label>
                            <span id="available_qty_0"></span>
                             
                        </div>
                        <div class="col-md-3">
                            <div class="col-md-10">
                                <label>Enter Quantity<sup>*</sup></label>
                                <input type="number" name="quantity[]" min="0" value="{{((old('quantity')[0])?(old('quantity')[0]):0)}}" class="input-css quantity" id="quantity_0"/>  
                                {!! $errors->first('quantity.*', '<p class="help-block">:message</p>') !!}
                            </div>
                        </div>
                    </div><!-- accessories-condition-row -->
                   <div class="accessoriesappended-div">
                       @if(old('part_number'))
                            @if(count(old('part_number')) > 1)
                            @for($i=1;$i < count(old('part_number'));$i++)
                            <div class="row accessoriescharge accessories-condition-row appended-content">
                                <div class="col-md-3">
                                    <label>Part Number<sup>*</sup></label>
                                     <input type="text" name="part_number[]" value="{{((old('part_number')[$i])?(old('part_number')[$i]):'')}}" class="input-css partnumber" id="partnumber_{{$i}}"/>
                                    {!! $errors->first('partnumber.*', '<p class="help-block">:message</p>') !!}
                                </div>
                                <div class="col-md-2">
                                    <label>Available Quantity</label>
                                    <span id="available_qty_{{$i}}"></span>
                                     
                                </div>
                                <div class="col-md-3">
                                    <div class="col-md-10">
                                        <label>Enter Quantity<sup>*</sup></label>
                                        <input type="number" name="quantity[]" min="0" value="{{((old('quantity')[$i])?(old('quantity')[$i]):0)}}" class="input-css quantity" id="quantity_{{$i}}"/>  
                                        {!! $errors->first('quantity.*', '<p class="help-block">:message</p>') !!}
                                    </div>
                                </div>
                            </div>
                            @endfor
                            @endif
                        @endif
                   </div><!-- end of appended div -->
                </div><!-- accessories-condition -->
                <div class="row">
                    <div class="col-md-7"></div>
                    <div class="col-md-2"><i class="fa fa-plus accessoriesaddOther" style=" cursor:pointer">Add More</i></div>
                </div>
                <!-- /.box-body -->
                <div class="box-footer">
                    
                </div><!-- /.footer -->
            </div>
            
        <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Addon</h3>
                </div>
                 <div class="row margin" id="addon_data">
                     <div class="col-md-6">
                        <div class="col-md-6">
                            <label class="text-label">MIRROR SET </label>
                        </div>
                        <div class="col-md-5 margin">
                            <input type="number" name="mirror" value="{{old('mirror')?old('mirror'):0}}" min="0" class="input-css">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="col-md-6">
                            <label class="text-label">TOOLKIT </label>
                        </div>
                        <div class="col-md-5 margin">
                            <input type="number" name="toolkit" value="{{old('toolkit')?old('toolkit'):0}}" min="0" class="input-css">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="col-md-6">
                            <label class="text-label">FIRST AID KIT </label>
                        </div>
                        <div class="col-md-5 margin">
                            <input type="number" name="first_aid_kit" value="{{old('first_aid_kit')?old('first_aid_kit'):0}}" min="0" class="input-css">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="col-md-6">
                            <label class="text-label">SAREE GUARD </label>
                        </div>
                        <div class="col-md-5 margin">
                            <input type="number" name="saree_guard" value="{{old('saree_guard')?old('saree_guard'):0}}" min="0" class="input-css">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="col-md-6">
                            <label class="text-label">BIKE KEYS </label>
                        </div>
                        <div class="col-md-5 margin">
                            <input type="number" name="bike_keys" value="{{old('bike_keys')?old('bike_keys'):0}}" min="0" class="input-css">
                        </div>
                    </div>
                 </div>
                <div class="box-body addon-condition">
                    <div class="box-header with-border">
                    <h3 class="box-title">Owner Manual</h3>
                    </div>
                    <div class="row addoncharge addon-condition-row ">
                        <div class="col-md-3">
                            <label>Model Name<sup>*</sup></label>
                            <select name='addon[]' class='input-css select2 addon' id="addon_0" onchange="get_model_qty(this)">
                                <option value="">Select</option>
                                 @foreach($new_product_models as $k => $v)
                                    <option value="{{$v['om_addon']}}" {{(old('addon')[0] == $v['om_addon']) ? 'selected' : ''}} > {{$v['model_name']}} </option>
                                @endforeach

                            </select>
                            {!! $errors->first('addon.*', '<p class="help-block">:message</p>') !!}
                        </div>
                        <div class="col-md-3">
                            <label>Quantity<sup>*</sup></label>
                               <input type='number' name='addonqty[]' min="0" class='input-css addonqty' id="addonqty_0" value='{{old("addonqty")[0]}}'>
                                {!! $errors->first('addonqty.*', '<p class="help-block">:message</p>') !!}
                        </div>
                        
                    </div><!-- addon-condition-row -->
                    <div class="addonappended-div">
                        @if(old('addon'))
                            @if(count(old('addon')) > 1)
                            @for($i=1;$i < count(old('addon'));$i++)
                            <div class="row addoncharge addon-condition-row appended-content">
                                <div class="col-md-3">
                                    <label>Model Name<sup>*</sup></label>
                                    <select name='addon[]' class='input-css select2 addon' id="addon_{{$i}}" onchange="get_model_qty(this)">
                                        <option value="">Select</option>
                                         @foreach($new_product_models as $k => $v)
                                            <option value="{{$v['om_addon']}}" {{(old('addon')[$i] == $v['om_addon']) ? 'selected' : ''}} > {{$v['model_name']}} </option>
                                        @endforeach
                                    </select>
                                    {!! $errors->first('addon.*', '<p class="help-block">:message</p>') !!}
                                </div>
                                <div class="col-md-3">
                                    <label>Quantity<sup>*</sup></label>
                                       <input type='number' name='addonqty[]' min="0" class='input-css addonqty' id="addonqty_{{$i}}" value='{{old("addonqty")[$i]}}'>
                                        {!! $errors->first('addonqty.*', '<p class="help-block">:message</p>') !!}
                                </div>
                                <a href="javascript:void(0)" class="remove_div"><i class="fa fa-close"></i></a>
                            </div>
                            @endfor
                            @endif
                        @endif
                   </div><!-- end of appended div -->
                </div><!-- addon-condition -->
                <div class="row">
                    <div class="col-md-7"></div>
                    <div class="col-md-2"><i class="fa fa-plus addonaddOther" style=" cursor:pointer">Add More</i></div>
                </div>
                <!-- /.box-body -->
                <div class="box-footer">
                    
                </div><!-- /.footer -->
            </div>
        <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Battery</h3>
                </div>
                <div class="box-body battery-condition">
                    <div class="row batterycharge battery-condition-row">
                        <div class="col-md-5">
                            <label>Battery<sup>*</sup></label>
                            <select name="battery[0][]" class="select2 battery" id="battery_0" multiple="multiple" data-placeholder="Select Battery">
                                @foreach($battery as $key=>$value)
                                    @if(old('battery')[0])
                                        @if(in_array($value['id'],old('battery')[0]))
                                        <option value="{{$value['id']}}" selected>{{$value['frame']}}</option>
                                        @else
                                        <option value="{{$value['id']}}" >{{$value['frame']}}</option>
                                        @endif
                                    @else
                                        <option value="{{$value['id']}}" >{{$value['frame']}}</option>
                                    @endif
                                @endforeach
                           </select>
                            {!! $errors->first('battery.*', '<p class="help-block">:message</p>') !!}
                        </div>
                    </div><!-- battery-condition-row -->
                   <div class="batteryappended-div">
                       @if(old('battery'))
                            @if(count(old('battery')) > 1)
                            @for($i=1;$i < count(old('battery'));$i++)
                            <div class="row battery-condition-row batterycharge appended-content">
                                <div class="col-md-5">
                                    <label>Battery<sup>*</sup></label>
                                    <select name="battery[{{$i}}][]" class="select2 battery" id="battery_{{$i}}" multiple="multiple" data-placeholder="Select Battery">
                                        @foreach($battery as $key=>$value)
                                            @for($y=0;$y < count(old('battery')[$i]);$y++)
                                            <option value="{{$value['id']}}" {{(old('battery')[$i][$y] == $value['id'])? 'selected':''}}>{{$value['frame']}}</option>
                                            @endfor
                                        @endforeach
                                    </select>
                                    {!! $errors->first('battery.*', '<p class="help-block">:message</p>') !!}
                                </div>
                                <a href="javascript:void(0)" class="remove_div"><i class="fa fa-close"></i></a>
                            </div>
                            @endfor
                            @endif
                        @endif
                   </div><!-- end of appended div -->
                </div><!-- battery-condition -->
                <div class="row">
                    <div class="col-md-5"></div>
                    <div class="col-md-2"><i class="fa fa-plus batteryaddOther" style=" cursor:pointer">Add More</i></div>
                </div>
                <!-- /.box-body -->
                <div class="box-footer">
                    
                </div><!-- /.footer -->
            </div>
            <div class="row">
                <div class="col-md-12">
                    <button type="submit" class="btn btn-success">Submit</button>
                </div>
                <br><br>    
            </div>  
        </form>
      </section>
@endsection