@extends($layout)

@section('title', 'Booking Request')

@section('breadcrumb')
    <li><a href="#"><i class=""></i> Booking Request </a></li>
@endsection

@section('js')
 <script src="/js/pages/stockListBook.js"></script> 
<script>

// function removedisable()
// {
//     $(".disable").removeAttr("disabled");
//     console.log($('.frame').parent().children("label.error"));
//    // return true;
// }
// $(document).on('load',function(){

// $(".disable").attr("disabled","disabled");
// });
// $(".frame").on("change",function(){
//     //console.log(this.val());
//     $(".frame").each(function () {
//         console.log(this.value);
//     //return this.value == el.val();
//     });
// });
function framechange(el)
{   
    var count =0 ;
    $(".frame").each(function () {
        if(el.value == this.value && this.value != "default")
        {
            count=count+1;
        }
    });
    //console.log(count);
    if (count > 1) { 
        //alert("this frame number already selected");
        //console.log($(el).val());
       
        $(el).val("default").trigger("change");
        var e = $(el).parent();
        $(e).append("<span id='error' style='color:red'>Frame Number already selected</span>");
    }
    else{
         $(el).parent().children('span#error').remove();
    }
}
</script>
@endsection

@section('main_section')
    <section class="content">
            <!-- general form elements -->
            <div id="app">
                @include('admin.flash-message')
                @yield('content')
            </div>
        <form action="/admin/stock/movement/store/accept/{{$book_data[0]['stock_mov_id']}}" onsubmit="removedisable();" method="post" id="stock-movement-validation">
            @csrf

            <div class="box box-primary " style="padding-bottom:20px;">
                <div class="row">

                    <div class="col-md-4">
                        <label>From Store Name<sup>*</sup></label>
                        <select name="from_store" disabled class=" disable input-css select2 selectpicker" style="width:100%">
                                <option  selected value ="{{$book_data[0]['from_store']}}">{{$book_data[0]['from_store_name']}}</option>        
                        </select>
                    </div>
                    <div class="col-md-4">
                            <label>To Store Name<sup>*</sup></label>
                            <select name="to_store" disabled class="disable input-css select2 selectpicker" style="width:100%">
                                    <option  selected value ="{{$book_data[0]['to_store']}}">{{$book_data[0]['to_store_name']}}</option>        
                            </select>
                    </div>
                    <div class="col-md-4">
                            <label>Loader Number<sup>*</sup></label>
                            <select name="loader" class=" loader input-css select2 selectpicker" style="width:100%">
                                    <option value="default" selected> Select Loader Number </option>
                                    @for($j = 0 ; $j < count($loader) ; $j++)
                                        <option value ="{{$loader[$j]['id']}}" {{ old('loader') == $loader[$j]['id'] ? 'selected' : '' }}>{{$loader[$j]['truck_number']}}</option>
                                    @endfor
                                </select>
                                {!! $errors->first("loader", '<p class="help-block">:message</p>') !!}
                    </div>
                </div>
            </div>
            @php 
                //print_r($arr);
                //print_r($book_data[0]);
                foreach ($book_data as $key => $value) {
                    if($value['req_qty'] != 0 )
                    {
            @endphp
            
                <div class="box box-primary " style="padding-bottom:20px;">
                        <div class="row">
                            <div class="col-md-6">
                                <label>Product Name<sup>*</sup></label>
                                <select name="prod_name[]" disabled class="disable input-css select2 selectpicker" style="width:100%">
                                    <option  selected value ="{{$value['product_id']}}">{{$value['product_name']}}</option>        
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label>Required Quantity<sup>*</sup></label>
                                <input type="text" disabled name="req_qty[]" class="disable input-css" value="{{$value['req_qty']}}">
                            </div>
                        </div>
                   
                            
                        @for($i = 0 ; $i < $value['req_qty'] ; $i++)
                            <div class="row">
                                <div class="col-md-6">
                                    <label>Frame Number <sup>*</sup></label>
                                    <select name="frame_{{$value['product_id']}}_[]" id="frame_{{$value['product_id'].'_'.$i}}" onchange="framechange(this);" class="frame input-css select2 selectpicker" style="width:100%">
                                        <option value="default" selected> Select Frame Number </option>
                                        @for($j = 0 ; $j < count($value['framedata']) ; $j++)
                                            <option value="{{$value['framedata'][$j]['product_details_id']}}" {{old("frame_".$value['product_id']."_.".$i) == $value['framedata'][$j]['product_details_id'] ? 'selected' : ''}} >{{$value['framedata'][$j]['frame']}}</option>
                                        @endfor 
                                    </select>
                                    {!! $errors->first("frame_".$value['product_id']."_.".$i, '<p class="help-block">:message</p>') !!}
                                </div> 
                            </div>
                        @endfor
                         
                </div>
            @php
                    }
                }
                @endphp

            <div class="submit-button">
            <button type="submit" class="btn btn-primary mb-2">Submit</button>
            </div>
        </form>
                
      </section>
@endsection

{{-- // <div class="col-md-6">
                                //     <label>Engine Number <sup>*</sup></label>
                                //     <select name="engine_{{$value['product_id']}}_[]" class="engine input-css select2 selectpicker" style="width:100%">
                                //             <option value="default" selected> Select Engine Number </option>                                        
                                //         @for($j = 0 ; $j < count($value['framedata']) ; $j++)
                                //             <option value = "{{$value['framedata'][$j]['product_details_id']}}">{{$value['framedata'][$j]['engine']}}</option>
                                //         @endfor
                                //     </select>
                                //     {!! $errors->first("frame_".$value['product_id']."_".$i, '<p class="help-block">:message</p>') !!}
                                // </div> --}}