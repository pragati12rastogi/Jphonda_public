@extends($layout)

@section('title', __('Stock Movement'))

@section('breadcrumb')
    {{-- <li><a href="#"><i class=""></i> {{__('Stock Movement')}} </a></li> --}}
@endsection

@section('js')
<script src="/js/pages/stock_movement.js"></script>
<script>
    var to_store = @php echo json_encode($to_store); @endphp;
    // console.log(to_store);

    var old_to_store = @php echo json_encode(old('toStore') ? old('toStore') : ''); @endphp;
    // console.log(old_to_store);
    if(old_to_store){
        // $("#toStore").val(old_to_store).trigger('change');
        change_to_store(old_to_store)
    }
    $(document).on('change','#toStore',function(){
        var val = $(this).val();
        change_to_store(val);
    }); 

    function change_to_store(val){
        $("#codealer_store, #subdealer_store").hide();
        if(val > 0){
            $.each(to_store,function(key,data){
                if(data.id == val){
                    if(data.store_type == 'SubDealer'){
                        $("#subdealer_store").show();
                    }else if(data.store_type == 'CoDealer'){
                        $("#codealer_store").show();
                    }
                }
            });
        }
    }

</script>
@endsection

@section('main_section')
    <section class="content">
    @include('admin.flash-message')
                @yield('content')
        <form id="stock-movement-validation" method="post" action="/admin/stock/movement">
                @csrf
            <!-- general form elements -->
            <div class="box box-primary">
                    
                    <div class="row">
                            <div class="col-md-6">
                                <label>From Store <sup>*</sup></label>
                                <select name="fromStore" class="input-css select2 selectpicker">
                                    <option selected value=''>Select From Store</option>
                                    @foreach($store as $key)
                                        <option value="{{$key['id']}}" {{(old('fromStore') == $key['id'])? 'selected':''}}>{{$key['name']}}</option>
                                    @endforeach
                                </select>
                                {!! $errors->first('fromStore', '<p class="help-block">:message</p>') !!}
                            </div>
                            <div class="col-md-6">
                                <label>To Store<sup>*</sup></label>
                                <select name="toStore" class="input-css select2 selectpicker" id="toStore">
                                <option selected value=''>Select To Store</option>
                                    @foreach($to_store as $key)
                                    <option value="{{$key['id']}}" {{(old('toStore') == $key['id'])? 'selected':''}}>{{$key['name']}}</option>
                                    {{-- <option value="{{$key['id']}}">{{$key['name']}}</option> --}}
                                    @endforeach
                                </select>
                                {!! $errors->first('toStore', '<p class="help-block">:message</p>') !!}
                            </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6" id="codealer_store" style="display:none;">
                            <label>Co-Dealer <sup>*</sup></label>
                            <select name="codealer" class="input-css select2 selectpicker" style=" width:100%">
                            <option selected value=''>Select Co-Dealer</option>
                                @foreach($codealer as $key)
                                    <option value="{{$key['id']}}" {{(old('codealer') == $key['id'])? 'selected':''}}>{{$key['name']}}</option>
                                @endforeach
                            </select>
                            {!! $errors->first('codealer', '<p class="help-block">:message</p>') !!}
                        </div>
                        <div class="col-md-6" id="subdealer_store" style="display:none;">
                            <label>Sub-Dealer<sup>*</sup></label>
                            <select name="subdealer" class="input-css select2 selectpicker" style=" width:100%">
                            <option selected value=''>Select Sub-Dealer</option>
                                @foreach($subdealer as $key)
                                    <option value="{{$key['id']}}" {{(old('subdealer') == $key['id'])? 'selected':''}}>{{$key['name']}}</option>
                                @endforeach
                            </select>
                            {!! $errors->first('subdealer', '<p class="help-block">:message</p>') !!}
                        </div>
                    </div>
                    
                    <div class="row" id="copy">
                        <div class="col-md-6">
                            <label>Product <sup>*</sup></label>
                            <select name="stockProduct[]" class="input-css select2 selectpicker">
                            <option value="" selected>Select Product</option>
                                @foreach($product as $key)
                                    <option value="{{$key['id']}}" {{(old('stockProduct.0') == $key['id'])? 'selected':''}}>{{$key['model_category']}}-{{$key['model_name']}}-{{$key['model_variant']}}-{{$key['color_code']}}</option>
                                @endforeach
                            </select>
                            {!! $errors->first('stockProduct.0', '<p class="help-block">:message</p>') !!}
                        </div>
                        <div class="col-md-6">
                            <label>Quantity <sup>*</sup></label>
                            <input type="number" name="stockQuantity[]" value="{{old('stockQuantity.0')}}" class="input-css">
                            {!! $errors->first('stockQuantity.0', '<p class="help-block">:message</p>') !!}
                        </div>
                    </div>
                    
                   
                    <br><br>
            </div>
                @php
                    $c = session()->pull('countAddMore');
                    
                @endphp
            <div id="count" style="display:none;">@if($c) 
            {{$c-1}} 
            @else 
            0
            @endif </div>
            <div id="appendData"></div>
                
                @for($i=1;$i<$c;$i++)

                <div class="box box-primary">
                    <div class="row" id="appendData_{{$i}}"  style="padding-bottom:20px;">
                        <button type="button" class="close" onclick="$(this).parent().parent().remove();" style="padding-right:20px;" id="removeconsignee">X
                        </button><br>
                        <div class="row" id="appendData">
                                <div class="col-md-6">
                                    <label>Product <sup>*</sup></label>
                                    <select name="stockProduct[]" class="input-css select2 selectpicker">
                                        <option value="" selected>Select Product</option>
                                        @foreach($product as $key)
                                            <option value="{{$key['id']}}" {{(old('stockProduct.'.$i) == $key['id'])? 'selected':''}}>{{$key['model_category']}}-{{$key['model_name']}}-{{$key['model_variant']}}-{{$key['color_code']}}</option>
                                        @endforeach
                                    </select>
                                    {!! $errors->first('stockProduct.'.$i, '<p class="help-block">:message</p>') !!}
                                </div>
                                <div class="col-md-6">
                                    <label>Quantity <sup>*</sup></label>
                                    <input type="number" name="stockQuantity[]" value="{{old('stockQuantity.'.$i)}}" class="input-css">
                                    {!! $errors->first('stockQuantity.'.$i, '<p class="help-block">:message</p>') !!}
                                </div>
                        </div>
                    </div>
                </div>
           

                @endfor
                            <div style="display:block">
                <div class="submit-button" style="float:right">
                    <button type="button" onclick = "add_more()" class="btn btn-primary">Add More</button>
                </div>
                <div class="submit-button">
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </div>
        </form>
      </section>
@endsection