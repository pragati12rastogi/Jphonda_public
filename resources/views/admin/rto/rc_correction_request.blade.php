@extends($layout)

    @section('title', __('RC Correction Request'))

@section('breadcrumb')
    <li><a href="#"><i class=""></i> {{__('RC Correction Request')}} </a></li> 
@endsection

@section('js')
<script>
    $(document).ready(function() {
       $("#staff").click(function () {
             $("#staff_detail").css({'display':'block'});
             $("#financer_box").css({'display':'none'});
             $("#dealer_box").css({'display':'none'});
             $("#customer_name").css({'display':'none'});
        });
       $("#customer").click(function () {
             $("#staff_detail").css({'display':'none'});
             $("#customer_name").css({'display':'block'});
             $("#dealer_box").css({'display':'none'});
             $("#financer_box").css({'display':'none'});
        });
       $("#dealer").click(function () {
             $("#dealer_box").css({'display':'block'});
              $("#staff_detail").css({'display':'none'});
             $("#customer_name").css({'display':'none'});
             $("#financer_box").css({'display':'none'});
        });
       $("#financer").click(function () {
             $("#financer_box").css({'display':'block'});
             $("#dealer_box").css({'display':'none'});
             $("#staff_detail").css({'display':'none'});
             $("#customer_name").css({'display':'none'});
        });
});
$(document).ready(function() {
   $("#customer_textbox").prop('readOnly', true);
    $("#customer_checkbox").on('click', function () {
        if ($(this).prop('checked')) {
            $("#customer_textbox").prop('readOnly', false);
        } else {
            $("#customer_textbox").prop('readOnly', true);
        }
    });

    $("#add_textbox").prop('readOnly', true);
    $("#add_checkbox").on('click', function () {
        if ($(this).prop('checked')) {
            $("#add_textbox").prop('readOnly', false);
        } else {
            $("#add_textbox").prop('readOnly', true);
        }
    });

    $("#rt_textbox").prop('readOnly', true);
    $("#rt_checkbox").on('click', function () {
        if ($(this).prop('checked')) {
            $("#rt_textbox").prop('readOnly', false);
        } else {
            $("#rt_textbox").prop('readOnly', true);
        }
    });

    $("#rn_textbox").prop('readOnly', true);
    $("#rn_checkbox").on('click', function () {
        if ($(this).prop('checked')) {
            $("#rn_textbox").prop('readOnly', false);
        } else {
            $("#rn_textbox").prop('readOnly', true);
        }
    });

    $("#fn_textbox").prop('readOnly', true);
    $("#fn_checkbox").on('click', function () {
        if ($(this).prop('checked')) {
            $("#fn_textbox").prop('readOnly', true);
        } else {
            $("#fn_textbox").prop('readOnly', true);
        }
    });

    $("#hypo_textbox").prop('readOnly', true);
    $("#hypo_checkbox").on('click', function () {
        if ($(this).prop('checked')) {
            $("#hypo_textbox").prop('readOnly', false);
        } else {
            $("#hypo_textbox").prop('readOnly', true);
        }
    });
});
</script>
@endsection

@section('main_section')
    <section class="content">
        
            @include('admin.flash-message')
                @yield('content')
            <!-- general form elements -->
            <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <form action="/admin/rto/rc/correction/request" method="POST">
                    @csrf
                  <div class="col-md-12" style="margin-bottom: 15px!important;">
                    <label>Mistake by:</label>
                    <div class="col-md-3">
                        <input type="radio" name="mistake_by" id="customer" value="customer"> Customer
                    </div>
                    <div class="col-md-3">
                        <input type="radio" name="mistake_by" id="staff" value="staff"> Staff
                    </div>
                    <div class="col-md-3">
                        <input type="radio" name="mistake_by" id="dealer" value="dealer"> Dealer
                    </div>
                    <div class="col-md-3">
                        <input type="radio" name="mistake_by" id="financer" value="financer"> Financer
                    </div>                    
                </div>
                <div class="row" >
                    <div class="col-md-6" id="staff_detail" style="display: none">&nbsp;&nbsp;
                      &nbsp;  <input type="checkbox" name="mistake_create" value="{{$rtoData['created_by']}}"> {{$rtoData['create_staff_name']}} (Enter By ) &nbsp;
                      <input type="checkbox" name="mistake_approve" value="{{$rtoData['approved_by']}}"> {{$rtoData['approve_staff_name']}} (Approved By )
                      <!-- <select name="staff_detail[]" class="input-css select2" multiple="multiple" data-placeholder="Select" style="width: 100%;">
                            <option value="{{$rtoData['created_by']}}">{{$rtoData['create_staff_name']}} (Enter By )</option>
                            <option value="{{$rtoData['approved_by']}}">{{$rtoData['approve_staff_name']}} (Approved By )</option>
                    </select> -->
                    </div>
                    <div class="col-md-6" id="financer_box" style="display: none;">
                        <input type="text" name="financer_name" value="@if(isset($financer_name['financer_name'])){{$financer_name['financer_name']}}@endif"  class="form-control"  readonly="">
                    </div>
                     <div class="col-md-6" id="dealer_box" style="display: none;">
                        <input type="text" name="dealer_name" class="form-control"  value="JPHONDA" readonly="">
                    </div>
                    <div class="col-md-6" id="customer_name" style="display: none;">
                        <input type="text" name="customer_name" value="{{$rtoData['name']}}" class="form-control"  readonly="">
                    </div>
                    
                </div>
                <div class="row">
                    <div class="col-md-6">
                    <label>Payment Amount:</label>
                    <div class="form">
                      <input type="number" name="payment_amount" class="form-control" value="">
                      {!! $errors->first('payment_amount', '<p class="help-block">:message</p>') !!}
                    </div>
               </div></div><div class="row">
                <div class="col-md-12">
                    <label>Reason for correction:</label>
                    <div class="form">
                      <textarea class="form-control" name="correction_reason" rows="4" cols="50"></textarea>
                      {!! $errors->first('correction_reason', '<p class="help-block">:message</p>') !!}
                    </div>
                </div>
               
               
                </div><br>
            </div>
        </div>
        <div class="col-md-6">

          <div class="box box-danger">
            <div class="box-header">
              <h3 class="box-title">RC Corection Details</h3>
            </div>
            <div class="box-body">
              <div class="row">
                  <div class="col-md-12">
                        <label>Name:</label>
                          <input type="text" class="form-control" value="{{$rtoData['name']}}" readonly="">
                  </div>
                   <div class="col-md-12">
                        <label>Address:</label>
                          <input type="text" class="form-control" value="{{$rtoData['address']}}" readonly="">
                  </div>
                   <div class="col-md-12">
                        <label>Relation(S/o, D/o):</label>
                          <input type="text" class="form-control" value="{{$rtoData['relation_type']}}" readonly="">
                  </div>
                   <div class="col-md-12">
                        <label>Relation name:</label>
                          <input type="text" class="form-control" value="{{$rtoData['relation']}}" readonly="">
                  </div>
                   <div class="col-md-12">
                        <label> Frame number :</label>
                          <input type="text" class="form-control" value="{{$rtoData['product_frame_number']}}" readonly="">
                  </div>
                   <div class="col-md-12">
                        <label>Hypo(bank name):</label>
                          <input type="text" class="form-control" value="{{$rtoData['hypo']}}" readonly="">
                  </div>
              </div>

            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->

         

        </div>
        <!-- /.col (left) -->
        <div class="col-md-6">
          <div class="box box-primary">
            <div class="box-header">
              <h3 class="box-title">RC Correction Request</h3>
            </div>
            <div class="box-body">
              <input type="hidden" name="rto_id" value="{{$rtoData['id']}}">
              <input type="hidden" name="mobile" value="{{$rtoData['mobile']}}">
                <label>Name:</label>
                <div class="input-group date">
                  <div class="input-group-addon">
                    <input type="checkbox" id="customer_checkbox" name="customer_checkbox">
                  </div>
                  <input type="text" name="customer" id="customer_textbox" class="form-control" value="{{$rtoData['name']}}" >
                </div>
                <label>Address:</label>

                <div class="input-group">
                  <div class="input-group-addon">
                   <input type="checkbox" id="add_checkbox" name="add_checkbox">
                  </div>
                  <input type="text" name="address" class="form-control" id="add_textbox" value="{{$rtoData['address']}}">
                </div>


                <label>Relation(S/o, D/o):</label>
                <div class="input-group">
                  <div class="input-group-addon">
                   <input type="checkbox" id="rt_checkbox" name="rt_checkbox">
                  </div>
                  <input type="text" id="rt_textbox" name="relation_type" class="form-control" value="{{$rtoData['relation_type']}}">
                </div>
                <label>Relation name:</label>
                <div class="input-group">
                  <div class="input-group-addon">
                   <input type="checkbox" id="rn_checkbox" name="rn_checkbox">
                  </div>
                  <input type="text" id="rn_textbox" name="relation_name" class="form-control" value="{{$rtoData['relation']}}">
                </div>
                <label>Frame number:</label>
                <div class="input-group">
                  <div class="input-group-addon">
                   <input type="checkbox" id="fn_checkbox" name="fn_checkbox">
                  </div>
                  <input type="text" id="fn_textbox" name="frame_number" class="form-control" value="{{$rtoData['product_frame_number']}}">
                </div>
                <label>Hypo(bank name):</label>
                <div class="input-group">
                  <div class="input-group-addon">
                   <input type="checkbox" id="hypo_checkbox" name="hypo_checkbox">
                  </div>
                  <input type="text" id="hypo_textbox" name="hypo" class="form-control" value="{{$rtoData['hypo']}}">
                </div>
            </div>
          </div>
        </div>
        <button class="btn btn-success pull-right" type="submit">Submit</button>
    </form>
      </div>
      </section>
@endsection