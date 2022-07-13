<section class="content">
    <!-- Default box -->
              <div class="row">
                  <div class="col-md-12">
                  <div class="col-md-3">
                    <label for="customer_name">Customer Name</label>
                     {{$calling['customer_name']}}
                  </div>
                  <div class="col-md-3">
                    <label for="model">Model</label>
                    {{$calling['model_name']}}
                  </div>
                  <div class="col-md-3">
                    <label for="frame">Frame</label>
                     {{$calling['product_frame_number']}}
                  </div>
                  <div class="col-md-3">
                    <label for="dosale">DO Sale</label>
                    {{$calling['sale_date']}}
                  </div>
                </div>
                {{--<div class="col-md-12">
                   <div class="col-md-3">
                    {{$calling['customer_name']}}
                  </div>
                  <div class="col-md-3">
                   {{$calling['model_name']}}
                  </div>
                  <div class="col-md-3">
                   {{$calling['product_frame_number']}}
                  </div>
                  <div class="col-md-3">
                    {{$calling['sale_date']}}
                  </div>
                </div>--}}
              </div><br>
               <div class="row">
                  <div class="col-md-12">
                  <div class="col-md-3">
                    <label for="so">{{$calling['relation_type']}}</label>
                     {{$calling['relation']}}
                  </div>
                   @if($calling['customer_pay_type'] == 'finance')
                  <div class="col-md-1">
                    <label for="finance">Finance</label>
                  </div>
                  <div class="col-md-3">
                   {{$calling['finance_name']}}
                  </div>
                  @endif
                   @if(empty($service_booking))
                  <div class="col-md-1">
                      <button id="service_booking" data-toggle="modal" data-target="#exampleModalCenter"class="btn btn-success">Service Booking</button>
                  </div>
                     @else
                    <div class="col-md-4">
                      <table id="table-pending" class="table table-bordered table-responsive" style="text-align:center">
                        <label  style="text-align:center;font-weight: bold;font-size: 14px;">Service Booking</label>
                        <thead>
                            <tr>
                              <td style="font-weight: bold;font-size: 14px;">Name</td>
                              <td style="font-weight: bold;font-size: 14px;">Mobile No</td>
                              <td style="font-weight: bold;font-size: 14px;">Booking Status</td>
                            </tr>
                             <tr>
                              <td style="text-align:left;font-weight: bold;font-size: 14px;">
                                {{$service_booking->name}}
                              </td>
                              <td style="text-align:left;font-size: 14px;">
                                {{$service_booking->mobile}}
                              </td>
                              <td style="text-align:left;font-size: 14px;">
                               {{$service_booking->status}}
                              </td>
                            </tr>
                        </thead>               
                      </table>
                    </div>
                     @endif
                </div>
              </div><br/>
              <div class="row">
                  <div class="col-md-10">
                    <table id="table-pending" class="table table-bordered table-responsive" style="text-align:center">
                        <thead>
                            <tr>
                              <td></td>
                              <td style="font-weight: bold;font-size: 14px;">Received</td>
                              <td style="font-weight: bold;font-size: 14px;">Date of file Submission RTO</td>
                              <td style="font-weight: bold;font-size: 14px;">Days</td>
                            </tr>
                             <tr>
                              <td style="text-align:left;font-weight: bold;font-size: 14px;">
                                Number Plate Status&nbsp;
                              </td>
                              <td style="text-align:left;font-size: 14px;">
                                {{(($calling['numberPlateStatus'] == 0)? 'No' : 'Yes')}}
                              </td>
                              <td style="text-align:left;font-size: 14px;">
                                @if(!empty($calling['rto_file_submission_date']))
                                {{date('d-m-Y', strtotime($calling['rto_file_submission_date']))}}
                                @endif
                              </td>
                              <td style="text-align:left;font-size: 14px;">
                                {{$diff = Carbon\Carbon::parse($calling['plate_receive_date'])->diffInDays() }}
                              </td>
                            </tr>
                            <tr>
                              <td style="text-align:left;font-weight: bold;font-size: 14px;">
                                RC Status &nbsp;-
                              </td>
                              <td style="text-align:left;font-size: 14px;">
                                {{(($calling['numberPlateStatus'] == 0)? 'No' : 'Yes')}}
                              </td>
                              <td style="text-align:left;font-size: 14px;">
                                @if(!empty($calling['rto_file_submission_date']))
                                {{date('d-m-Y', strtotime($calling['rto_file_submission_date']))}}
                                @endif
                              </td>
                              <td style="text-align:left;font-size: 14px;">
                                {{$diff = Carbon\Carbon::parse($calling['rto_file_submission_date'])->diffInDays() }}
                              </td>
                            </tr>
                        </thead>               
                    </table>
                </div>
              </div><br/>
              <div class="row">
                 <div class="col-md-12">
                  <div class="col-md-4">
                    <label for="outstanding_payment">Outstanding Payment</label>
                  </div>
                  <div class="col-md-3">
                    <label for="plate_number">Number Plate Frame &nbsp;&nbsp;</label>
                  </div>
                  
                </div>
              </div><br/>
              <div class="row">
                 <div class="col-md-12">
                 {{-- <div class="col-md-3">
                    <label style="text-align:center">Pending Items</label>
                  </div>
                  <div class="col-md-1">
                     <label for="delivered">Delivered</label>
                  </div>
                  <div class="col-md-6">
                     <label  style="text-align:center">Service</label>
                  </div>--}}
                </div>
                <div class="col-md-12">
                  <div class="col-md-4">
                     <table id="table-pending" class="table table-bordered table-striped" style="text-align:center">
                      <thead>
                            <tr>
                              <td colspan=2 style="text-align:center;font-weight: bold;font-size: 14px;">Pending Items</td>
                              <td style="font-weight: bold;font-size: 14px;">Delivered</td>
                            </tr>
                      </thead>
                     @if(sizeof($allPendingData)==0)
                            <tr>
                              <th colspan="3" style="font-weight: bold;font-size: 14px;">No item</th>
                            </tr>
                       @else
                            <tr>
                              <th>Name</th>
                              <th>Status</th>
                              <th></th>
                            </tr>
                          @foreach($allPendingData as $key => $val)
                          <tr>
                            <td>{{$val->accessories_name}}</td>
                            @if($val->quantity > $val->qty)
                            <td>Available</td>
                            @else
                            <td>Not Available</td>
                            @endif
                            <td><input type="checkbox" name="delivered" value="delivered"></td>
                            </tr>
                          @endforeach
                          @endif
                       </tbody>               
                    </table>
                  </div>
                    <div class="col-md-2"></div>
                      <div class="col-md-6">
                        <div class="col-sm-12"> 
                          <div class="col-md-2"></div>
                          <div class="col-md-10">
                            <label>Service</label>
                          </div>
                          
                        </div>
                        <div class="col-sm-12">
                          <div class="col-md-4"><label>Due Date</label></div>
                          <div class="col-md-6">
                            <label>
                               @php $diff = Carbon\Carbon::parse($job_card['job_card_date'])->addDays(90);

                               echo date('d-m-Y', strtotime($diff));
                            @endphp
                            </label>
                          </div>
                      </div>
                      <div class="col-sm-12">
                          <div class="col-md-4"><label>Last Service Rating</label></div>
                          <div class="col-md-6">
                            <label>{{$job_card['rating']}}</label>
                          </div>
                      </div>
                      </div>
                </div>
              </div><br/>
              <div class="row">
                <hr>
                 <div class="col-md-12">
                    <label for="plate_number">Reminder Call History</label>
                  </div>
                  <div class="col-md-8">
                    <table id="table-pending" class="table table-bordered table-striped" style="text-align:center">
                        <thead>
                            <tr>
                              <th>Sr.No</th>
                              <th >Date & Time</th>
                              <th >Remark</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(empty($allLastCallData))
                              <tr>
                                <td colspan="5" >Not-Found Data</td>
                              </tr>
                            @else
                              <?php $i = 1;  ?>
                              @foreach($allLastCallData as $key => $val)
                              <tr>
                                <td>Call {{$i}}</td>
                                <td>{{$val->created_at}}</td>
                                <td>{{$val->remark}}</td>
                                <?php $i++; ?>
                              </tr>
                              @endforeach
                            @endif
                        </tbody>               
                    </table>
                  </div>
                    <!-- /.box-body -->
                    <br>
                    <br>
        </div>

           <!-- Modal --> 
          <div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
              <div class="modal-content" style="margin-top: 200px!important;">
                <div class="modal-header">
                  <h4 class="modal-title" id="exampleModalLongTitle">Service Booking</h4>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body">
                  <form id="my-form"  method="POST" >
                      <div id="app">
                      @include('admin.flash-message')
                      @yield('content')
                      </div>
                      <div class="alert alert-danger" id="js-msg-verror">
                    </div>
                    @csrf
                    <input type="hidden" name="call_id" value="<?php echo $call_id;?>" id="call_id">
                    <div class="row">
                      <div class="col-md-6">
                         <label>Name<sup>*</sup></label>
                         <input type="text" name="servicename" id="name" class="input-css">
                     </div>
                     <div class="col-md-6">
                         <label>Mobile<sup>*</sup></label>
                         <input type="text" name="mobile" id="mobile" class="input-css">
                     </div>
                 </div>
               </div>
                <div class="modal-footer">
                  <button type="button" id="btnCancel" class="btn btn-secondary" data-dismiss="modal">Close</button>
                  <button type="button" id="btnUpdate" class="btn btn-success">Submit</button>
                </div>
                </form>
              </div>
            </div>
          </div>
    </section>