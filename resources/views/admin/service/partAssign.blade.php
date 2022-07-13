@extends($layout)

@section('title', __('Part Assign'))

@section('breadcrumb')
    <li><a href="/admin/service/stock/part/request/list"><i class=""></i> {{__('Part Request List')}} </a></li>
@endsection
@section('css')
<style>
    .color-action{
        color:red;
        cursor: no-drop;
    }
    .loading {
  position: fixed;
  z-index: 999;
  height: 2em;
  width: 2em;
  margin: auto;
  top: 0;
  left: 0;
  bottom: 0;
  right: 0;
}
/* Transparent Overlay */
.loading:before {
  content: '';
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0,0,0,0.3);
}
.loading:not(:required):after {
  content: '';
  display: block;
  width: 1em;
  height: 1em;
  animation: spinner 1500ms infinite linear;
  border-radius: 0.5em;
  box-shadow: rgba(0, 0, 0, 0.75) 1.5em 0 0 0, rgba(0, 0, 0, 0.75) 1.1em 1.1em 0 0, rgba(0, 0, 0, 0.75) 0 1.5em 0 0, rgba(0, 0, 0, 0.75) -1.1em 1.1em 0 0, rgba(0, 0, 0, 0.75) -1.5em 0 0 0, rgba(0, 0, 0, 0.75) -1.1em -1.1em 0 0, rgba(0, 0, 0, 0.75) 0 -1.5em 0 0, rgba(0, 0, 0, 0.75) 1.1em -1.1em 0 0;
}
/* Animation */

@-webkit-keyframes spinner {
  0% {
    -webkit-transform: rotate(0deg);
    -moz-transform: rotate(0deg);
    -ms-transform: rotate(0deg);
    -o-transform: rotate(0deg);
    transform: rotate(0deg);
  }
  100% {
    -webkit-transform: rotate(360deg);
    -moz-transform: rotate(360deg);
    -ms-transform: rotate(360deg);
    -o-transform: rotate(360deg);
    transform: rotate(360deg);
  }
}


</style>
@endsection
@section('js')
<script>
   $('#js-msg-error').hide();
   $('#js-msg-success').hide();
    $(document).on('keyup','.part_no',function(){
        var part_no = $(this).val();
        var store_id = $("#store_id").val();
        $(this).parent().parent().find('.part_name').val('');
        $(this).parent().parent().find('.part_price').val('');
        $(this).parent().parent().find('.part_rack_row_cell').val('');
        $(this).parent().parent().find('.price').val('');

        var part_name_el = $(this).parent().parent().find('.part_name');
        var part_price_el = $(this).parent().parent().find('.part_price');
        var part_rack_row_cell_el = $(this).parent().parent().find('.part_rack_row_cell');
        var price_el = $(this).parent().parent().find('.price');
         var id = $(this).attr('id');
          var id_arr = id.split('-');
           index = id_arr[1];
          var qty = $("#part_qty-"+index).val();
          var part_availability = $("#part_availability-"+index).val();
        $.ajax({
            url:'/admin/service/part/issue/get/partinfo',
            data: {'part_no':part_no,'store_id':store_id,'qty':qty},
            amethod:'GET',
            beforeSend: function() {
              $("#loader").show();
           },
            success:function(result){
              if (result.type == 'availablity_other_location') {
                 $("#loader").hide();
                 $("#js-msg-error").show();
                     $("#js-msg-error").html('Stock available for '+part_no+' Part Number in '+result.data.store_name+' location   !');
                     
                     setTimeout(function() {
                        $('#js-msg-error').fadeOut('fast');
                      }, 6000);
                    // $('#part_availability-'+index+' option[value="2"]').attr('selected', 'selected').trigger();
                    $('#part_availability-'+index).val(2).select2().trigger('change');
                     part_name_el.val(result.data.name);
                    if ( result.row_name == undefined ) {
                        var row = 'Not Found';
                    }else{
                        var row = result.row_name;
                    }
                    if ( result.cell_name == undefined) {
                        var cell = 'Not Found';
                    }else{
                        var cell = result.cell_name; 
                    }
                    if (result.rack_name == undefined ) {
                        var rack = 'Not Found';
                    }else{
                        var rack = result.rack_name;
                    }
                    var rack_row_cell = rack+'/'+row+'/'+cell;

                    part_rack_row_cell_el.val(rack_row_cell);
                   
                    var part_price = result.data.price*qty;

                    if(part_price > 0){
                       part_price_el.val(part_price);
                       price_el.val(result.data.price);
                    }else{
                        part_price_el.val('0');
                        price_el.val('0');
                    }

              }else if (result.type == 'not_available') {
                 $("#loader").hide();
                 $('#part_availability-'+index).val(5).select2().trigger('change');
                 $("#js-msg-error").show();
                 $("#js-msg-error").html('Not available  '+part_no+' Part Number !');
                 setTimeout(function() {
                    $('#js-msg-error').fadeOut('fast');
                  }, 6000);

              }else{
                    $("#loader").hide();
                    // $('#part_availability-'+index+' option[value="1"]').attr('selected', 'selected').change();
                    $('#part_availability-'+index).val(1).select2().trigger('change');

                    $("#js-msg-success").show();
                    $("#js-msg-success").html('Available  '+part_no+' Part Number !');
                    setTimeout(function() {
                      $('#js-msg-success').fadeOut('fast');
                    }, 6000);

                    part_name_el.val(result.name);
                    if ( result.row_name == undefined ) {
                        var row = 'Not Found';
                    }else{
                        var row = result.row_name;
                    }
                    if ( result.cell_name == undefined) {
                        var cell = 'Not Found';
                    }else{
                        var cell = result.cell_name; 
                    }
                    if (result.rack_name == undefined ) {
                        var rack = 'Not Found';
                    }else{
                        var rack = result.rack_name;
                    }
                    var rack_row_cell = rack+'/'+row+'/'+cell;
                    part_rack_row_cell_el.val(rack_row_cell);

                    var part_price = result.price*qty;

                    if(part_price > 0){
                       part_price_el.val(part_price);
                       price_el.val(result.price);
                    }else{
                        part_price_el.val('0');
                        price_el.val('0');
                    }
                    
                }
                

            },
            error:function(error){
                alert("something wen't wrong.");
            }
        });
    });


      $(document).on('change','.part_qty',function(){
          var qty = $(this).val();
          var id = $(this).attr('id');
          var id_arr = id.split('-');
           index = id_arr[1];
          var price = $("#price-"+index).val();
          var part_price = $("#part_price-"+index).val('');
          part_price.val(qty*price);


      });


</script>
@endsection
@section('main_section')
   <div class="loading" id="loader" style="display:none"></div>
    <section class="content">
        
            @include('admin.flash-message')
                @yield('content')
                <div class="row">
         <div class="alert alert-danger" id="js-msg-error">
         </div>
         <div class="alert alert-success" id="js-msg-success">
         </div>
       </div>
            <!-- general form elements -->
            <div class="box-header with-border">

            <div class="box box-primary">
                <div class="box-header">
                </div>  
                <form  action="/admin/service/store/assign/part/{{$jobcardId}}" method="post" >
                    @csrf
                    <input type="hidden" hidden name="append_count" id="append_count" value="0">
                    {{-- <div class="col-md-6 margin">
                        <label for="customer_confirmation">Customer Confirmation <sup>*</sup></label>
                        <select name='customer_confirmation' class="input-css select2">
                            <option value="">Select Customer Confirmation</option>
                            <option value="yes">Yes</option>
                            <option value="no">No</option>
                        </select>
                    </div> --}}
                    <div class="row margin">
                      <div class="col-md-3"> <label>Jobcard Number : {{$tag->tag}} </label> </div>
                      <div class="col-md-3"> <label>Manufacturing Year : {{$tag->manufacturing_year}} </label> </div>
                      <div class="col-md-3"> <label> Frame : {{$tag->frame}}  </label> </div>
                      <div class="col-md-3"> <label> Model Number : {{$tag->model_name}} </label> </div>
                      <div class="col-md-3"> <label>Model Varient : {{$tag->model_variant}} </label> </div>
                      <div class="col-md-3"> <label>Color Code : {{$tag->color_code}} </label> </div>
                      <div class="col-md-3"> <label>Store Name : {{$tag->store_name}} </label> </div>
                        <label >Part Assign</label>
                    </div>
                    @if($partInfo == '[]')
                       <div class="row text-center table table-bordered table-striped"><h4>Part not found for assign</h4></div><br>
                    @else
                    <input type="hidden"  name="part_count" value="{{count($partInfo)}}">
                    <div class="row margin" id="append-div">
                         <div class="row" >
                                <div class="col-md-2">
                                    <label>Generic Part Name / Requested Quantity</label>
                                </div>
                                 <div class="col-md-2">
                                    <label>Part Number</label>
                                </div>
                                <div class="col-md-2">
                                    <label>Part Name</label>
                                </div>
                                <div class="col-md-1">
                                    <label>Quantity</label>
                                </div>
                                <div class="col-md-1">
                                    <label>Price</label>
                                </div>
                                <div class="col-md-2">
                                    <label>Rack/Row/Cell</label>
                                </div>
                                <div class="col-md-2">
                                    <label>Part Availability</label>
                                </div>
                               
                            </div><br>
                        <input type="hidden" name="jobcard_id" value="{{$tag->id}}">
                    
                        @foreach($partInfo as $key => $val)

                            <div class="row" id="div-{{$key}}">
                                <div class="col-md-2">
                                    <label>
                                   <!-- <input type="checkbox" name="part_issue[]" value="{{$val->id}}">  -->
                                   {{$val->part_name}}/{{$val->qty}}</label>
                                   <input type="hidden" name="part_request_id{{$key}}" value="{{$val->id}}">
                                     <input type="hidden" name="store_id" id="store_id" value="{{$tag->store_id}}">
                                </div>
                                 <div class="col-md-2">
                                    <input type="text" id="part_number-{{$key}}" name="part_number{{$key}}" class="input-css part_no" placeholder="Enter Part Number">
                                </div>
                                <div class="col-md-2">
                                    <input type="text" id="part_name-{{$key}}" name="part_name{{$key}}" class="input-css part_name" placeholder="Enter Part Name">
                                </div>
                                <div class="col-md-1">
                                    <input type="number" id="part_qty-{{$key}}" name="part_qty{{$key}}" value="{{$val->qty}}" min="0" class="input-css part_qty" placeholder="Enter Part Quantity" >
                                </div>
                                <div class="col-md-1">
                                  <input type="hidden" id="price-{{$key}}" name="price{{$key}}" value="{{$val->price}}" class="input-css price"  readonly>

                                    <input type="text" id="part_price-{{$key}}" name="part_price{{$key}}" value="{{$val->price}}" class="input-css part_price"  readonly>
                                </div>
                                <div class="col-md-2">
                                    <input type="text" id="part_rack_row_cell-{{$key}}" name="part_rack_row_cell{{$key}}" class="input-css part_rack_row_cell"  readonly>
                                </div>
                                <div class="col-md-2"> 
                                    <select class="input-css select2" name="part_availability{{$key}}" id="part_availability-{{$key}}" >
                                      <option>Select Part Availability</option>
                                      @foreach($available_type as $key=>$val)
                                      <option value="{{$key}}">{{$val}}</option>
                                      @endforeach
                                    </select>
                                </div>
                            </div><br>
                        @endforeach
                         </div>

                         
                    <div class="row margin-bottom">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-success">Assign</button>
                        </div>
                        <br><br>    
                    </div>
                    @endif
                     
                </form>
            </div>
             <div class="box box-primary">
                <div class="box-header">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Part Number</th>
                                <th>Part Name</th>
                                <th>Requested Quantity</th>
                                <th>Assign Quantity</th>
                               {{-- <th>Issue Quantity</th>--}}
                                <th>Price</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($partdetails as $part)
                            <tr>
                                <td>{{$part->part_number}}</td>
                                <td>{{$part->part_name}}</td>
                                <td>{{$part->qty}}</td>
                                <td>{{$part->assign_qty}}</td>
                                <td>@if($part->issue_qty != 0){{$part->price*$part->issue_qty}}@else {{$part->price*$part->assign_qty}} @endif</td>

                                <td>@if($part->assign_qty > 0){{$part->status}} @else 
                                   @foreach($available_type as $key=>$val)
                                      @if($key == $part->part_availability)
                                      {{$val}}
                                      @endif
                                      @endforeach
                                 @endif</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            </div>
      </section>
@endsection