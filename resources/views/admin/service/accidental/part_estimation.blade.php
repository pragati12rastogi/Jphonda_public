@extends($layout)

@section('title', __('Part Estimation'))

@section('breadcrumb')
    <li><a href="#"><i class=""></i> {{__('Part Estimation')}} </a></li>
@endsection
@section('css')
<style>
    .color-action{
        color:red;
        cursor: no-drop;
    }
    .color-red{color: red;}
</style>
@endsection
@section('js')
<script>
    $('#js-msg-error').hide();
    $('#js-msg-success').hide();

    $(document).ready(function(){
      
      var partdata = @php echo json_encode($getpart); @endphp;
       var total = 0;
      $.each(partdata, function (key, val) {
        var price = val.price*val.qty;
         total = total+price;
    });

     if(total > 0){
        var part_price = total;
     }else{
       var part_price = 0;
     }

     if($("#labour_charge").val() > 0){
        var lamount = $("#labour_charge").val();
     }else{
       var lamount = 0;
     }
     if($("#painting_charge").val() > 0){
        var pamount = $("#painting_charge").val();
     }else{
       var pamount = 0;
     }
     if($("#fork_charge").val() > 0){
             var famount = $("#fork_charge").val();
     }else{
       var famount = 0;
     }
     if($("#chasis_charge").val() > 0){
             var camount = $("#chasis_charge").val();
     }else{
       var camount = 0;
     }
     var amount = parseInt(lamount)+parseInt(pamount)+parseInt(famount)+parseInt(camount)+parseInt(part_price);
     // console.log(amount);
     $("#total_amount").html(amount);
   });

    $(document).on('click','.add-div',function(){
        var main_div = $(this).parent().parent().parent().attr('id');
        var id_arr = main_div.split('-');
        // var index = id_arr[1];
        $('#append_count').val(parseInt($("#append_count").val())+1);
        var high_index = $('#append_count').val();
        // var ele = $(this).parent().parent().parent().html();
        // ele = ele.replace('part_name-0','part_name-'+high_index);
        // ele = ele.replace('part_qty-0','part_qty-'+high_index);
        $('#append-div').append('<div class="row " id="append-div">'
                        +'<div class="col-md-12" id="div-'+high_index+'">'
                            +'<div class="col-md-2">'
                                +'<label>'
                                    +'<i  class="fa fa-trash color-action margin-r-5 remove-div"  ></i>'
                                    +'<i  class="fa fa-plus margin add-div" style="color:blue;cursor:pointer" ></i>'
                               +'</label>'
                            +'</div>'
                            +'<div class="col-md-4">'
                                +'<label>Part Name</label>'
                                +'<input type="text" name="part_name[]" id="part_name-'+high_index+'" class="input-css" placeholder="Enter Part Name" >'
                            +'</div>'
                            +'<div class="col-md-2">'
                                +'<label>Quantity</label>'
                                +'<input type="number" min="0" id="part_qty-'+high_index+'" name="part_qty[]" class="input-css"placeholder="Enter Part Quantity">'
                            +'</div>'
                            +'<div class="col-md-2">'
                                +'<label>Customer Confirmation</label>'
                                +'<select name="customer_conf[]" id="customer_conf-'+high_index+'" class="input-css customer_conf">'
                                    +'<option value="yes">Yes</option>'
                                    +'<option value="no">No</option>'
                                +'</select>'
                            +'</div>'
                            +'<div class="col-md-2" id="call_status_div-'+high_index+'" style="display:none">'
                                +'<label>Call Response</label>'
                                +'<select name="call_status[]" id="call_status-'+high_index+'" class="input-css call_status">'
                                    +'<option value="call not reachable">Call Not Reachable</option>'
                                    +'<option value="busy">Busy</option>'
                                +'</select>'  
                            +'</div>'
                        +'</div>'
                    +'</div>');
        $("#div-"+high_index).find('label').find('.color-action').css('cursor','pointer');
        $("#div-"+high_index).find('label').find('.DeletePart').removeAttr('id');
        // $("#part_name-"+high_index).val('');
        // $("#part_qty-"+high_index).val('');
        // console.log('index',index);
    });
    $(document).on('click','.remove-div',function(){
        var main_div = $(this).parent().parent().parent().attr('id');
        var id_arr = main_div.split('-');
        var index = id_arr[1];
        if(parseInt(index) > 0)
        {
            $("#div-"+index).remove();
        }
    });



   //   $(document).on('click','.PartRemove',function(){
   //    var part_req_id = $(this).attr('id');
   //    $('#js-msg-error').hide();
   //    $('#js-msg-success').hide();
   //    var confirm1 = confirm('Are You Sure, You Want to remove part ?');
   //    if(confirm1)
   //    {
   //       $.ajax({
   //          url:'/admin/accidental/part/remove',
   //          data:{'part_req_id':part_req_id},
   //          method:'GET',
   //          success:function(result){
   //             if(result == 'success')
   //             {
   //                $('#js-msg-success').html('Part request remove successfully.').show();
   //                window.location.reload();
   //             }
   //             else if(result != 'error')
   //             {
   //                $('#js-msg-error').html("Something Wen't Wrong").show();
   //             }
   //             else{
   //                $('#js-msg-error').html("Something Wen't Wrong").show();
   //             }
   //          },
   //          error:function(error){
   //             $('#js-msg-error').html("Something Wen't Wrong ").show();
   //          }
   //       });
   //    }
      
   // });

        $("#labour_charge").change(function() {
            var amount =  parseInt($('#labour_charge').val());
            var total_amount =  parseInt($("#total_amount").html());
            $("#total_amount").html(amount+total_amount);
        });

        $("#painting_charge").change(function() {
            var amount =  parseInt($('#painting_charge').val());
            var total_amount =  parseInt($("#total_amount").html());
            $("#total_amount").html(amount+total_amount);
        });

  $(document).on('change','#fork_type',function(){
        var type = $("#fork_type").val();
        if (type == 'new') {
          $('#fork_qty-div').css('display', 'inline-block');
          $('#fork_charge-div').css('display', 'none');
          $('#fork_charge').val('');
        }
        if (type == 'old') {
          $('#fork_qty-div').css('display', 'none');
          $('#fork_charge-div').css('display', 'inline-block');
          $('#fork_qty').val('');
          
          $("#fork_charge").change(function() {
            var amount =  parseInt($('#fork_charge').val());
            var total_amount =  parseInt($("#total_amount").html());
            $("#total_amount").html(amount+total_amount);
          });
        }
  });

   $(document).on('change','#chasis_type',function(){
        var type = $("#chasis_type").val();
        if (type == 'new') {
          $('#chasis_qty-div').css('display', 'inline-block');
          $('#chasis_charge-div').css('display', 'none');
          $('#chasis_charge').val('');
        }
        if (type == 'old') {
          $('#chasis_qty-div').css('display', 'none');
          $('#chasis_charge-div').css('display', 'inline-block');
          $('#chasis_qty').val('');
          $("#chasis_charge").change(function() {
            var amount =  parseInt($('#chasis_charge').val());
            var total_amount =  parseInt($("#total_amount").html());
            $("#total_amount").html(amount+total_amount);
          });
        }
  });

   $(document).on('click','.DeletePart',function(){
      var getpartId = $(this).attr('id');
      if(getpartId)
      {
        $('#js-msg-error').hide();
        $('#js-msg-success').hide();
        var confirm1 = confirm('Are You Sure, You Want to delete this part ?');
        if(confirm1)
        {
           $.ajax({
              url:'/admin/accidental/part/remove',
              data:{'part_req_id':getpartId},
              method:'GET',
              success:function(result){
                 if(result.trim() == 'success') {
                   
                    $('#js-msg-success').html('Part deleted successfully .').show();
                    setTimeout(function() {
                    location.reload();
                  }, 1000);
                 } else if(result.trim() != 'error') {
                    $('#js-msg-error').html(result).show();
                 }else{
                    $('#js-msg-error').html("Something Wen't Wrong").show();
                 }
              },
              error:function(error){
                 $('#js-msg-error').html("Something Wen't Wrong ").show();
              }
           });
        } 
      }
      else{
        var main_div = $(this).parent().parent().parent().attr('id');
        var id_arr = main_div.split('-');
        var index = id_arr[1];
        if(parseInt(index) > 0)
        {
            $("#div-"+index).remove();
        }
      }
   });

     $(document).on('change','.customer_conf',function(){
        var val = $(this).val();
        // console.log(val);
        var attr = $(this).attr('id');
        var index = parseInt(attr.split('-')[1]);
        if(val == 'no')
        {
            $('#call_status_div-'+index).show();
        }
        else{
            $('#call_status_div-'+index).hide();
        }

    });

</script>
@endsection
@section('main_section')
    <section class="content">
        <div id="app">
      <div class="row">
         <div class="alert alert-danger" id="js-msg-error" style="display: none;">
         </div>
         <div class="alert alert-success" id="js-msg-success" style="display: none;">
         </div>
       </div>
   </div>
   <?php $partname = ''; ?>
            @include('admin.flash-message')
                @yield('content')

            <!-- general form elements -->
            <div class="box-header with-border">

            <div class="box box-primary">
    
                <div class="box-header">
                </div>  

                <form  action="/admin/accidental/part/estimation/{{$job_card_id}}" method="post" >
                    @csrf
                    <input type="hidden" hidden name="append_count" id="append_count" value="0">
                     <div class="col-md-12">
                        <div class="col-md-3">
                            <label >CUSTOMER NAME :-  {{$customer['name']}}</label>
                        </div>
                        <div class="col-md-3">
                            <label >CONTACT NUMBER :- {{$customer['mobile']}} </label>
                        </div>
                    </div>
                   
                   {{-- <div class="col-md-6 margin">
                        <label for="customer_confirmation">Customer Confirmation <sup>*</sup></label>
                        <select name='customer_confirmation' class="input-css select2">
                            <option value="" selected="" disabled="">Select Customer Confirmation</option>
                            @if(isset($jobcard->customer_confirmation))
                              <option value="yes" @if($jobcard->customer_confirmation == "yes") selected @endif>Yes</option>
                               <option value="no" @if($jobcard->customer_confirmation == "no") selected @endif>No</option>
                            @else
                            <option value="yes">Yes</option>
                            <option value="no">No</option>
                            @endif
                        </select>
                         {!! $errors->first('customer_confirmation', '<p class="help-block">:message</p>') !!}
                    </div>--}}

                    <div class="row margin">
                        <label >Request Part's</label>
                    </div>
                   
                        @if($getpart != '[]')
                         <div class="row margin" >
                      <div class="col-md-12">
                        <div class="row"  id="append-div">
                        <?php $i = 0; ?>
                        @foreach($getpart as $part)
                       <?php if (isset($part->part_name)) {
                          if ($part->part_name == 'Fork Repair') {
                            $fork = $part->part_name;  
                          }
                          if ($part->part_name == 'Chasis Repair') {
                            $chasis = $part->part_name;  
                          }   
                       }
                        ?>
                        <div class="col-md-12" id="div-0">
                            <div class="col-md-2">
                                <label>
                                    <a href="#" class="DeletePart" id="@if(isset($part->id)){{$part->id}}@endif"><i class="fa fa-trash margin-r-5" style="color: red;" ></i></a>
                                     <i  class="fa fa-plus margin add-div" style="color:blue;cursor:pointer" ></i>
                                </label>
                            </div>
                            <div class="col-md-4">
                                <label>Part Name</label>
                                <input type="text" name="part_name[]" id="part_name-{{$i}}" class="input-css" placeholder="Enter Part Name" value="@if(isset($part->part_name)) {{$part->part_name}} @endif">
                            </div>
                            <div class="col-md-2">
                                <label>Quantity</label>
                                <input type="number" name="part_qty[]" id="part_qty-{{$i}}" class="input-css" value="@if(isset($part->qty)){{$part->qty}}@endif" min="0" placeholder="Enter Part Quantity">
                            </div>

                           <div class="col-md-2">
                                  <label>Price</label>
                                  <input type="number" min="0" name="price[]" class="input-css price" value="@if(isset($part->price)){{$part->qty*$part->price}}@endif" readonly="">
                            </div>
                             <div class="col-md-2">
                                  <label>Customer Confirmation</label>
                                  <input type="text" class="input-css price" value="@if($part->approved == 1) Yes @else No @endif" readonly="">
                            </div>
                            <div class="col-md-2" style="display:none">
                                <label>Customer Confirmation</label>
                                <select name="customer_conf[]" id="customer_conf-0" class="input-css customer_conf">
                                    {{-- <option value="">Select Customer Confirmation</option> --}}
                                    <option value="yes">Yes</option>
                                    <option value="no">No</option>
                                </select>
                                {!! $errors->first('customer_conf.*', '<p class="help-block">:message</p>') !!}
                            </div>
                            <div class="col-md-2" id="call_status_div-0" style="display:none">
                                <label>Call Response</label>
                                <select name="call_status[]" id="call_status-0" class="input-css call_status">
                                    {{-- <option value="">Select Customer Confirmation</option> --}}
                                    <option value="call not reachable">Call Not Reachable</option>
                                    <option value="busy">Busy</option>
                                </select>
                                {!! $errors->first('call_status.*', '<p class="help-block">:message</p>') !!}
                            </div>
                        </div>
                        
                          <br>
                          <?php $i++; ?>
                        @endforeach
                        <br>
                         <input type="hidden" hidden name="append_count" id="append_count" value="{{$i}}">
                         </div>
                        </div>
                      </div>
                    @else
                     <input type="hidden" hidden name="append_count" id="append_count" value="0">
                    <div class="row margin" id="append-div">
                        <div class="col-md-12" id="div-0">
                            <div class="col-md-2">
                                <label>
                                    <i  class="fa fa-trash color-action margin-r-5 remove-div"  ></i>
                                    <i  class="fa fa-plus margin add-div" style="color:blue;cursor:pointer" ></i>
                                </label>
                            </div>
                            <div class="col-md-4">
                                <label>Part Name</label>
                                <input type="text" name="part_name[]" id="part_name-0" class="input-css" placeholder="Enter Part Name" >
                                 {!! $errors->first('part_name.*', '<p class="help-block">:message</p>') !!}
                            </div>
                            <div class="col-md-2">
                                <label>Quantity</label>
                                <input type="number" id="part_qty-0" min="0" name="part_qty[]" class="input-css"placeholder="Enter Part Quantity">
                                 {!! $errors->first('part_qty.*', '<p class="help-block">:message</p>') !!}
                            </div>
                            <div class="col-md-2">
                                <label>Customer Confirmation</label>
                                <select name="customer_conf[]" id="customer_conf-0" class="input-css customer_conf">
                                    {{-- <option value="">Select Customer Confirmation</option> --}}
                                    <option value="yes">Yes</option>
                                    <option value="no">No</option>
                                </select>
                                {!! $errors->first('customer_conf.*', '<p class="help-block">:message</p>') !!}
                            </div>
                            <div class="col-md-2" id="call_status_div-0" style="display:none">
                                <label>Call Response</label>
                                <select name="call_status[]" id="call_status-0" class="input-css call_status">
                                    {{-- <option value="">Select Customer Confirmation</option> --}}
                                    <option value="call not reachable">Call Not Reachable</option>
                                    <option value="busy">Busy</option>
                                </select>
                                {!! $errors->first('call_status.*', '<p class="help-block">:message</p>') !!}
                            </div>
                        </div>
                    </div>
                    @endif
                    <div class="row margin">
                        <label >Charges</label>
                    </div>

                    <div class="row margin">
                        <div class="col-md-8" >
                            <div class="col-md-2">
                            </div>
                            <div class="col-md-6">
                                <label>Part Name</label>
                                <input type="text" name="charge_type[]" class="input-css" value="Labour" readonly="">
                                 {!! $errors->first('charge_type.*', '<p class="help-block">:message</p>') !!}
                            </div>
                            <div class="col-md-4">

                                <label>Charge</label>
                                <input type="number" min="0" name="charge[]" id="labour_charge" value="@if(isset($chargedata['Labour']['sub_type'])){{$chargedata['Labour']['amount']}}@endif" class="input-css" placeholder="Enter Labour Charge">
                                 {!! $errors->first('labour_charge', '<p class="help-block">:message</p>') !!}
                            </div>
                        </div>
                    </div>
                     <div class="row margin">
                        <div class="col-md-8" >
                            <div class="col-md-2">
                            </div>
                            <div class="col-md-6">
                                <label>Part Name</label>
                                <input type="text" name="charge_type[]" class="input-css" value="Painting" readonly="">
                                 {!! $errors->first('charge_type.*', '<p class="help-block">:message</p>') !!}
                            </div>
                            <div class="col-md-4">
                                <label>Charge</label>
                                <input type="number" min="0" name="charge[]" id="painting_charge" value="@if(isset($chargedata['Painting']['sub_type'])){{$chargedata['Painting']['amount']}}@endif" class="input-css" placeholder="Enter Painting Charge">
                                 {!! $errors->first('charge.*', '<p class="help-block">:message</p>') !!}
                            </div>
                        </div>
                    </div>

                <?php 
                 if (empty($fork)) { 
                 ?>
                    <div class="row ">
                      <div class="col-md-12">
                        <div class="row">
                        <div class="col-md-8" >
                            <div class="col-md-2">
                            </div>
                            <div class="col-md-6">
                                <label>Part Name</label>
                                <input type="text" name="fork_repair" class="input-css" value="Fork Repair" readonly="">
                                 {!! $errors->first('fork_repair', '<p class="help-block">:message</p>') !!}
                            </div>
                            <div class="col-md-4">
                              <label>Type</label>
                              <select name="fork_type" id="fork_type" class="select2">
                                <option selected="" disabled="">Select Type</option>
                                <option value="new">New</option>
                                <option value="old" @if(isset($chargedata['Fork Repair']['sub_type'])) selected="" @endif>Old</option>
                              </select>
                              {!! $errors->first('fork_type', '<p class="help-block">:message</p>') !!}
                            </div>
                           
                        </div>
                        <div class="col-md-3" id="fork_qty-div" style="display: none;">
                                <label>Quantity</label>
                                <input type="number" min="0" name="fork_qty" id="fork_qty" class="input-css" placeholder="Enter Quantity">
                                 {!! $errors->first('fork_qty', '<p class="help-block">:message</p>') !!}
                          </div>
                         <div class="col-md-3" id="fork_charge-div" style="@if(isset($chargedata['Fork Repair']['sub_type'])){{(($chargedata['Fork Repair']['sub_type']) ? 'display:block;' : 'display: none;')}} @endif">
                                <label>Charge</label>
                                <input type="number" min="0" name="fork_charge" id="fork_charge" class="input-css" value="@if(isset($chargedata['Fork Repair']['sub_type'])){{$chargedata['Fork Repair']['amount']}}@endif" placeholder="Enter Amount">
                                 {!! $errors->first('fork_charge', '<p class="help-block">:message</p>') !!}
                          </div>
                          </div>
                        </div>
                      </div>
                  <?php }
                   if ( empty($chasis )) { ?>
                    <div class="row ">
                      <div class="col-md-12">
                        <div class="row">
                        <div class="col-md-8" >
                            <div class="col-md-2">
                            </div>
                            <div class="col-md-6">
                                <label>Part Name</label>
                                <input type="text" name="chasis_repair" class="input-css" value="Chasis Repair" readonly="">
                                 {!! $errors->first('fork_repair', '<p class="help-block">:message</p>') !!}
                            </div>
                            <div class="col-md-4">
                              <label>Type</label>
                              <select class="select2" name="chasis_type" id="chasis_type">
                                <option selected="" disabled="">Select Type</option>
                                <option value="new">New</option>
                                <option value="old" @if(isset($chargedata['Chasis Repair']['sub_type'])) selected="" @endif>Old</option>
                              </select>
                              {!! $errors->first('chasis_type', '<p class="help-block">:message</p>') !!}
                            </div>
                           
                        </div>
                         <div class="col-md-3" id="chasis_qty-div" style="display: none;">
                                <label>Quantity</label>
                                <input type="number" min="0" name="chasis_qty" id="painting_qty" class="input-css" placeholder="Enter Quantity">
                                 {!! $errors->first('chasis_qty', '<p class="help-block">:message</p>') !!}
                          </div>
                         <div class="col-md-3" id="chasis_charge-div"  style="@if(isset($chargedata['Chasis Repair']['sub_type'])){{(($chargedata['Chasis Repair']['sub_type']) ? 'display:block;' : 'display: none;')}}@endif">
                                <label>Charge</label>
                                <input type="number" min="0" name="chasis_charge"  value="@if(isset($chargedata['Chasis Repair']['sub_type'])){{$chargedata['Chasis Repair']['amount']}}@endif" id="chasis_charge" class="input-css" placeholder="Enter Amount">
                                 {!! $errors->first('chasis_charge', '<p class="help-block">:message</p>') !!}
                          </div>
                          </div>
                        </div>
                      </div>
                    <?php } ?>
                      <br><br>
                    <div class="col-md-3 pull-right">
                      <label>Total Amount: <span id="total_amount">0
                    </span></label>
                    </div>
                   <br>
                    <div class="row margin-bottom">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-success">Submit</button>
                        </div>
                         
                    </div>  
                </form>
                 <br>
            </div>
            </div>
      </section>
@endsection