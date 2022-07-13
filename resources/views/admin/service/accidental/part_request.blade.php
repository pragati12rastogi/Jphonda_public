@extends($layout)

@section('title', __('Part Request'))

@section('breadcrumb')
    <li><a href="#"><i class=""></i> {{__('Part Request')}} </a></li>
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
    $(document).on('click','.add-div',function(){
        var main_div = $(this).parent().parent().parent().attr('id');
        var id_arr = main_div.split('-');
        // var index = id_arr[1];
        $('#append_count').val(parseInt($("#append_count").val())+1);
        var high_index = $('#append_count').val();
        var ele = $(this).parent().parent().parent().html();
        // console.log(ele);
        ele = ele.replace('part_name-0','part_name-'+high_index);
        ele = ele.replace('part_qty-0','part_qty-'+high_index);
         ele = ele.replace('customer_conf-0','customer_conf-'+high_index);
        ele = ele+"<div class='col-md-2' id='call_status_div-"+high_index+"' style='display:none'>"+
                                "<label>Call Response</label>"+
                                "<select name='call_status[]' id='call_status-"+high_index+"' class='input-css call_status'>"+
                                    "<option value='call not reachable'>Call Not Reachable</option>"+
                                    "<option value='busy'>Busy</option>"+
                                "</select>"+
                            "</div>";
        $('#append-div').append("<div class='col-md-12' id='div-"+high_index+"'>"+ele+"</div>");
        $("#div-"+high_index).find('label').find('.color-action').css('cursor','pointer');
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

     $(document).on('click','.PartRemove',function(){
      var part_req_id = $(this).attr('id');
      $('#js-msg-error').hide();
      $('#js-msg-success').hide();
      var confirm1 = confirm('Are You Sure, You Want to remove part ?');
      if(confirm1)
      {
         $.ajax({
            url:'/admin/accidental/part/remove',
            data:{'part_req_id':part_req_id},
            method:'GET',
            success:function(result){
               if(result == 'success')
               {
                  $('#js-msg-success').html('Part request remove successfully.').show();
                  window.location.reload();
               }
               else if(result != 'error')
               {
                  // $('#js-msg-error').html("Something Wen't Wrong").show();
                 $('#js-msg-error').html(result).show();
               }
               else{
                  $('#js-msg-error').html("Something Wen't Wrong").show();
               }
            },
            error:function(error){
               $('#js-msg-error').html("Something Wen't Wrong "+error).show();
            }
         });
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
         <div class="alert alert-danger" id="js-msg-error">
         </div>
         <div class="alert alert-success" id="js-msg-success">
         </div>
       </div>
   </div>
            @include('admin.flash-message')
                @yield('content')

            <!-- general form elements -->
            @if($service_status->service_status!='done')
            <div class="box-header with-border">

            <div class="box box-primary">
    
                <div class="box-header">
                </div>  
            
                <form  action="/admin/accidental/part/request/{{$job_card_id}}" method="post" >
                    @csrf
                    <input type="hidden" hidden name="append_count" id="append_count" value="0">
                     <div class="col-md-12">
                        <div class="col-md-3">
                            <label >Customer Name</label>
                            <label> {{$customer['name']}} </label>
                        </div>
                        <div class="col-md-3">
                            <label >Contact Number</label>
                            <label> {{$customer['mobile']}} </label>
                        </div>
                    </div>
                    {{--<div class="col-md-6 margin">
                        <label for="customer_confirmation">Customer Confirmation <sup>*</sup></label>
                        <select name='customer_confirmation' class="input-css select2">
                            <option value="">Select Customer Confirmation</option>
                            <option value="yes">Yes</option>
                            <option value="no">No</option>
                        </select>
                         {!! $errors->first('customer_confirmation', '<p class="help-block">:message</p>') !!}
                    </div> --}}
                    <div class="row margin">
                        <label >Request Part's</label>
                    </div>
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
                                <input type="text" name="part_name[]" id="part_name-0" class="input-css" placeholder="Enter Part Name">
                                 {!! $errors->first('part_name.*', '<p class="help-block">:message</p>') !!}
                            </div>
                            <div class="col-md-2">
                                <label>Quantity</label>
                                <input type="number" id="part_qty-0" name="part_qty[]" class="input-css" placeholder="Enter Part Quantity">
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
                   <br>
                    <div class="row margin-bottom">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-success">Request</button>
                        </div>
                        <br><br>    
                    </div>  
                </form>

            </div>
            </div> @endif
            <div class="box box-primary">
                <div class="box-header">
                    <table id="table" class="table table-bordered table-striped">
                        <thead>
                            <th>Part Number</th>
                            <th>Part Name</th>
                            <th>Quantity</th>
                            <th>Remove</th>
                        </thead>
                        <tbody>
                            @if($getpart)
                                @foreach($getpart as $part)
                                    <tr>
                                        <td>{{$part->part_no}}</td>
                                        <td>{{$part->part_name}}</td>
                                        <td>{{$part->qty}}</td>
                                        <td><a href="#" id="{{$part->id}}" class="PartRemove"><i class="fa fa-trash color-red margin-r-5 "></i></a></td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
      </section>
@endsection