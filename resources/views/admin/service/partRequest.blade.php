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
</style>
@endsection
@section('js')
<script>

    $("#js-msg-success").hide();
    $("#js-msg-error").hide();
    $("#js-msg-verror").hide();
    $(document).on('click','.add-div',function(){
        var main_div = $(this).parent().parent().parent().attr('id');
        var id_arr = main_div.split('-');
        // var index = id_arr[1];
        $('#append_count').val(parseInt($("#append_count").val())+1);
        var high_index = $('#append_count').val();
        var ele = $(this).parent().parent().parent().html();
        // console.log($('#append_count').val(), high_index, ele);
        ele = ele.replace('part_name-0','part_name-'+high_index);
        ele = ele.replace('part_qty-0','part_qty-'+high_index);
        // ele = ele.replace('customer_conf-0','customer_conf-'+high_index);
        // ele = ele.replace('call_status-0','call_status-'+high_index);
        // ele = ele.replace('call_status_div-0','call_status_div-'+high_index);
        // ele = ele.replace('reason_div-0','reason_div-'+high_index);
    /*ele = ele+"<div class='col-md-2' id='call_status_div-"+high_index+"' style='display:none'>"+
                                "<label>Call Response</label>"+
                                "<select name='call_status[]' id='call_status-"+high_index+"' class='input-css call_status'>"+
                                    "<option value='call not reachable'>Call Not Reachable</option>"+
                                    "<option value='busy'>Busy</option>"+
                                "</select>"+
                            "</div>";*/
        $('#append-div').append("<div class='col-md-12' id='div-"+high_index+"'>"+ele+"</div>");
        $("#div-"+high_index).find('label').find('.color-action').css('cursor','pointer');
        // $('#call_status_div-'+high_index).hide();
         // $('#reason_div-'+high_index).hide();
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
   
    $(document).on('change','#customer_conf',function(){
        var customer_conf = $("#customer_conf").val();
        if(customer_conf == 'no') {
            $("#call_status-div").show();
        } else{
           $("#call_status-div").hide();
           $("#reason-div").hide();
        }

    });

      $(document).on('change','#call_status',function(){

        var val = $(("#call_status")).val();
        if(val == 'refused') {
            $("#reason-div").show();
        } else{
            $("#reason-div").hide();
        }

    });

     function CustomerConformation(el) {
      var id = $("#id").val();
           $('#partModalCenter').modal("show"); 
    }


     $('#btnSubmit').on('click', function() {
      var id = $('#id').val();
      var confirmation = $('#customer_conf').val();
      var call_status = $('#call_status').val();
      var refused_reason = $('#refused_reason').val();
       $.ajax({
        method: "GET",
        url: "/admin/service/customer/confirmation",
        data: {'id':id,'confirmation':confirmation,'call_status':call_status,'refused_reason':refused_reason},
        success:function(data) {
            if (data.type == 'success') {
                $('#partModalCenter').modal("hide");  
                $("#js-msg-success").html(data.msg);
                $("#js-msg-success").show();
                 setTimeout(function() {
                 location.reload();
                }, 3000);
               setTimeout(function() {
                 $('#js-msg-success').fadeOut('fast');
                }, 4000);
            }if(data.type == 'error'){
                $('#partModalCenter').modal("show");  
                $("#js-msg-verror").html(data.msg);
                $("#js-msg-verror").show();
               setTimeout(function() {
                 $('#js-msg-verror').fadeOut('fast');
                }, 4000);
            }
        }
      });
    });

 $(document).ready(function() {
    $('form').on('submit', function(e) {
        $('.inputValidation').each(function(e) {
            $(this).rules("add", {
                required: true,
                messages: {
                    required: "This field is required"
                }
            });
        });
    });
    valx = $('form').validate();


});

</script>
@endsection
@section('main_section')
    <section class="content">
        
            @include('admin.flash-message')
                @yield('content')
                <div class="alert alert-success" id="js-msg-success">
              </div><div class="alert alert-danger" id="js-msg-error">
              </div>
            <!-- general form elements -->
            <div class="box-header with-border">

            <div class="box box-primary">
                <div class="box-header">
                </div>  
                <form id="form" action="/admin/service/part/request/{{$job_card_id}}" method="post" >
                    @csrf
                    <input type="hidden" hidden name="append_count" id="append_count" value="0">
                    <div class="col-md-12">
                        <div class="col-md-3">
                            <label >CUSTOMER NAME : {{$customer['name']}} </label>
                        </div>
                        <div class="col-md-3">
                            <label >CONTACT NUMBER : {{$customer['mobile']}}</label>
                        </div>
                    </div>
                    <div class="row margin">
                        <label >Request Part's</label>
                    </div>
                    <div class="row margin" id="append-div">
                        <div class="col-md-12" id="div-0">
                            <div class="col-md-1">
                                <label>
                                    <i  class="fa fa-trash color-action margin-r-5 remove-div"  ></i>
                                    <i  class="fa fa-plus margin add-div" style="color:blue;cursor:pointer" ></i>
                                </label>
                            </div>
                            <div class="col-md-2">
                                <label>Part Name</label>
                                <input type="text" name="part_name[]" id="part_name-0" class="input-css inputValidation" placeholder="Enter Part Name">
                                {!! $errors->first('part_name.*', '<p class="help-block">:message</p>') !!}
                            </div>
                            <div class="col-md-2">
                                <label>Quantity</label>
                                <input type="number" id="part_qty-0" name="part_qty[]" class="input-css inputValidation" placeholder="Enter Part Quantity" min="0">
                                {!! $errors->first('part_qty.*', '<p class="help-block">:message</p>') !!}
                            </div>
                          
                    </div>
                </div>
                   <br>
                    <div class="row margin-bottom">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-success">Request</button>
                        </div> 
                    </div> <br> 
                </form>
            </div>
            </div>
            <br>
            <div class="box box-primary">
                <div class="box-header">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <th>Part Number</th>
                            <th>Part Name</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Confirmation</th>
                            <th>Status</th>
                            <th>Action</th>
                        </thead>
                        <tbody>
                        @if($getpart)
                            @foreach($getpart as $part)
                            <tr>
                                <td>{{$part->part_number}}</td>
                                <td>{{$part->part_name}}</td>
                                <td>{{$part->qty}}</td>
                                <td>@if($part->issue_qty != 0){{$part->price*$part->issue_qty}}@else {{$part->price*$part->assign_qty}} @endif</td>
                                <td>{{$part->confirmation}} - @if($part->approved == 1) Yes @else No @endif</td>
                                <td>{{$part->status}}</td>
                                <td>@if($part->approved == 0 && $part->part_number != null)<a href="#"><button class="btn btn-info btn-xs" onclick="CustomerConformation()"> <input type="hidden" id="id" value="{{$part->id}}">Customer Confirmation</button></a>@endif</td>
                            </tr>
                            @endforeach
                        @endif
                        </tbody>
                    </table>
                </div>
            </div>

             <div class="modal fade" id="partModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="margin-top: 200px!important;">
          <div class="modal-header">
            <h4 class="modal-title" id="exampleModalLongTitle">Customer Confirmation</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">                   
            <form id="my-form"  method="POST" >
                <div class="alert alert-danger" id="js-msg-verror">
              </div>
              @csrf
              <div class="row">
                <div class="col-md-6">
                  <label for="advance">Customer Confirmation<sup>*</sup></label>
                  <select name="customer_conf" id="customer_conf" class="input-css select2" style="width: 70%;">
                      <option selected disabled>Select confirmation</option>
                      <option value="yes">Yes</option>
                      <option value="no">No</option>
                  </select>                      
               </div>
               <div class="col-md-6" id="call_status-div" style="display: none;">
                  <label for="advance">Call Status<sup>*</sup></label>
                  <select name="call_status" id="call_status" class="input-css select2" style="width: 70%;" >
                      <option selected disabled>Select call status</option>
                      <option value="call not reachable">Call Not Reachable</option>
                      <option value="busy">Busy</option>
                      <option value="refused">Refused</option>
                  </select>                      
               </div>
         </div><br>
          <div class="row">
            <div class="col-md-6" id="reason-div" style="display: none;">
                <label>Refused Reason <sup>*</sup></label>
                <textarea class="form-control" name="refused_reason" id="refused_reason"></textarea>
            </div>
          </div>
          </div>
          <div class="modal-footer">
            <button type="button" id="btnCancel" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="button" id="btnSubmit" class="btn btn-success">Submit</button>
          </div>
          </form>
        </div>
      </div>
    </div>
      </section>
@endsection