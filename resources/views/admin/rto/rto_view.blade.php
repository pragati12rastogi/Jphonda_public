@extends($layout)

@section('title', __('RTO View'))

@section('breadcrumb')
    <li><a href="/admin/rto/rc/list"><i class=""></i> {{__('RTO RC List')}} </a></li>
@endsection
@section('js')

@endsection

@section('main_section')
    <section class="content">
            @include('admin.flash-message')
                @yield('content')
            <!-- general form elements -->
            <div class="box box-primary">
                <div class="box-body rto-condition">
                     <div class="row ">
                <div class="col-md-12 ">
                  <div class="row">
                      <div class="col-md-3"> <label>Farme Number</label></div>
                      @if(isset($frameData['sale_no'])) 
                        <div class="col-md-3"> <label>Sale Number</label></div>
                      @endif
                  </div>
                  <div class="row">
                      <div class="col-md-3" ><label style="font-weight: 600!important">
                      {{$frameData['frame']}}</label></div>
                      @if(isset($frameData['sale_no'])) 
                      <div class="col-md-3" ><label style="font-weight: 600!important">{{$frameData['sale_no']}}</label></div>
                      @endif
                  </div>

                  <div class="row">
                      <div class="col-md-3"> <label>Name</label></div>
                      <div class="col-md-3"> <label>Mobile</label></div>
                      <div class="col-md-3"> <label>Type</label></div>
                      <div class="col-md-3"> <label>Registration No. (Number Plate)</label></div>
                  </div>
                  <div class="row">
                      <div class="col-md-3" ><label style="font-weight: 600!important">{{$rto['name']}}</label></div>
                      <div class="col-md-3" ><label style="font-weight: 600!important">{{$rto['mobile']}}</label></div>
                      <div class="col-md-3" ><label style="font-weight: 600!important">{{$rto['main_type']}}</label></div>
                      <div class="col-md-3" ><label style="font-weight: 600!important">{{$rto['registration_number']}}</label></div>
                  </div>
                
                <div class="row">
                  <div class="col-md-3"> <label> Type</label></div>
                  <div class="col-md-3"> <label>Rto Amount</label></div>
                  <div class="col-md-3"> <label>Penalty Charge</label></div>
                  <div class="col-md-3"> <label>Registration Date</label></div>
                </div>
                <div class="row">
                     <div class="col-md-3" ><label style="font-weight: 600!important">{{$rto['rto_type']}}</label></div>
                    <div class="col-md-3" ><label style="font-weight: 600!important">{{$rto['rto_amount']}}</label></div>
                    <div class="col-md-3" ><label style="font-weight: 600!important">{{$rto['penalty_charge']}}</label></div>
                    <div class="col-md-3" ><label style="font-weight: 600!important">@if(($rto['registration_date'] != '0000-00-00 00:00:00')) {{date('d-m-Y', strtotime($rto['registration_date']))}} @else 00:00:000 @endif  </label></div>
                </div>

                <div class="row">
                  <div class="col-md-3"> <label>Application Number</label></div>
                  <div class="col-md-3"> <label>RC Number</label></div>
                  <div class="col-md-3"> <label>Receiving Date</label></div>
                  <div class="col-md-3"> <label>Front Lid</label></div>
                </div>
                <div class="row">
                     <div class="col-md-3" ><label style="font-weight: 600!important">{{$rto['application_number']}}</label></div>
                    <div class="col-md-3" ><label style="font-weight: 600!important">{{$rto['rc_number']}}</label></div>
                    <div class="col-md-3" ><label style="font-weight: 600!important">  @if($rto['receiving_date']) {{date('d-m-Y', strtotime($rto['receiving_date']))}} @else 00:00:000 @endif  </label></div>
                    <div class="col-md-3" ><label style="font-weight: 600!important">{{$rto['front_lid']}}</label></div>
                </div>
                <div class="row">
                  <div class="col-md-3"> <label>Rear Lid</label></div>
                  <div class="col-md-3"> <label>Approve(Status)</label></div>
                  <div class="col-md-3"> <label>Approval Date</label></div>
                  <div class="col-md-3"> <label>File Uploaded</label></div>
                </div>
                <div class="row">
                  <div class="col-md-3" ><label style="font-weight: 600!important">{{$rto['rear_lid']}}</label></div>
                  <div class="col-md-3" ><label style="font-weight: 600!important">@if($rto['approve'] == 1) Approved @endif
                  @if($rto['approve'] == 3) Done @endif
                  @if($rto['approve'] == 2) Rejected @endif
                  @if($rto['approve'] == 0) Pending @endif</label></div>
                  <div class="col-md-3" ><label style="font-weight: 600!important">@if($rto['approval_date'] != '0000-00-00 00:00:00') {{date('d-m-Y', strtotime($rto['approval_date']))}} @else 00:00:000 @endif  </label></div>
                  <div class="col-md-3" ><label style="font-weight: 600!important"> @if($rto['file_uploaded'] == 1) Uploded @else Pending @endif</label></div>
                </div>

                <div class="row">
                  <div class="col-md-3"> <label>File Uploaded Date</label></div>
                  <div class="col-md-3"> <label>File Submission</label></div>
                  <div class="col-md-3"> <label>Plate Number Recieve Date</label></div>
                  <div class="col-md-3"> <label>Approve By</label></div>
                </div>
                <div class="row">
                  <div class="col-md-3" ><label style="font-weight: 600!important">@if($rto['uploaded_date'] != '0000-00-00 00:00:00') {{date('d-m-Y', strtotime($rto['uploaded_date']))}} @else 00:00:000 @endif  </label></div>
                  <div class="col-md-3" ><label style="font-weight: 600!important"> @if($rto['file_submission'] == 1) Submited @else Pending @endif</label></div>
                  <div class="col-md-3" ><label style="font-weight: 600!important">{{$rto['plate_receive_date']}}</label></div>
                  <div class="col-md-3" ><label style="font-weight: 600!important">{{$rto['approved_by']}}</label></div>
                </div>

                <div class="row">
                  <div class="col-md-3"> <label>Number Plate Status</label></div>
                  <div class="col-md-3"> <label>Number Plate Delivery Date</label></div>
                  <div class="col-md-3"> <label>RC Status</label></div>
                  <div class="col-md-3"> <label>RC Delivery Date</label></div>
                </div>
                <div class="row">
                  <div class="col-md-3" ><label style="font-weight: 600!important">@if($rto['numberPlateStatus'] == 1) Received @else Not Receive @endif </label></div>
                  <div class="col-md-3" ><label style="font-weight: 600!important"> @if($rto['numberPlateDeliveryDate'] != '') {{date('d-m-Y', strtotime($rto['numberPlateDeliveryDate']))}} @else 00:00:000 @endif</label></div>
                  <div class="col-md-3" ><label style="font-weight: 600!important">@if($rto['rcStatus'] == 1) Received @else Not Receive @endif</label></div>
                  <div class="col-md-3" ><label style="font-weight: 600!important">@if($rto['rcDeliveryDate'] != '') {{date('d-m-Y', strtotime($rto['rcDeliveryDate']))}} @else 00:00:000 @endif</label></div>
                </div>

              </div>
            </div>
            <hr>
           
             <form action="/admin/rto/update/delivery/status" method="POST" id="form">
                @csrf
                <input type="hidden" name="rto_id" id="rto_id" value="{{$rto['id']}}">
                <div class="row">
                  <div class="col-md-12">
                    <label for="numberPlateStatus" class="info-color">Number Plate Status</label>
                    <div class="col-md-4">
                      <label for="customer_name">Delivery</label>
                    </div>
                     <div class="col-md-4">
                      <label for=""></label>
                      <div class="col-md-2">
                        <input type="radio" {{(($rto['numberPlateStatus'] == 0)? '' : 'checked')}} name="numberPlateDelivery" value="yes"> Yes
                      </div>
                      <div class="col-md-2">
                        <input type="radio" {{(($rto['numberPlateStatus'] == 0)? 'checked' : '')}} name="numberPlateDelivery" value="no" > No
                      </div>
                    </div>
                  </div>
                  <div class="col-md-12">
                    <label for="numberPlateStatus" class="info-color">RC Status</label>
                    <div class="col-md-4">
                      <label for="customer_name">Delivery</label>
                    </div>
                     <div class="col-md-4">
                      <label for=""></label>
                      <div class="col-md-2">
                        <input type="radio" name="rcDelivery" {{(($rto['rcStatus'] == 0)? '' : 'checked')}}  value="yes"> Yes
                      </div>
                      <div class="col-md-2">
                        <input type="radio"  {{(($rto['rcStatus'] == 0)? 'checked' : '')}} name="rcDelivery"  value="no" > No
                      </div>
                    </div>
                  </div>
                  <div class="col-md-12" style="padding-left:30px;"><br>
                    <button type="submit" class="btn btn-success" >Update Delivery</button>
                  </div>
                </div>
              </form>
          
                <br>
            </div>
      </section>
@endsection