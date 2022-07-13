@extends($layout)

@section('title', __('Sale View'))

@section('breadcrumb')
    <li><a href="/admin/sale/list"><i class=""></i>Sale list</a></li>  
    <li><a href="#"><i class=""></i>Sale View</a></li>
@endsection
@section('css')
<link rel="stylesheet" href="/css/responsive.bootstrap.css">    
@endsection
@section('js')
<script src="/js/dataTables.responsive.js"></script>
@endsection

@section('main_section')

    <section class="content">

        <div id="app">
                   
        <!-- Default box -->
          <div class='box box-default'>
            <div class="container-fluid">
                <br>
                 <div class="row" style="border: 1px solid #f4f4f4;">
                    <div class="col-md-3"> <label>Sale Number</label></div>
                    <div class="col-md-3"> <label>Product Name</label></div>
                    <div class="col-md-3"> <label>Customer</label></div>
                    <div class="col-md-3"> <label>Store </label></div>
                </div>
                <div class="row">
                  <div class="col-md-3"> {{$saleData['sale_no']}}</div>
                  <div class="col-md-3"> {{$saleData['product_name']}} </div>
                  <div class="col-md-3"> {{$saleData['name']}}</div>
                  <div class="col-md-3"> {{$saleData['store_name']}}</div>
                </div>
                <div class="row" style="border: 1px solid #f4f4f4;">
                    <div class="col-md-3"> <label>Application Number</label></div>
                    <div class="col-md-3"> <label>Hirise Invoice</label></div>
                    <div class="col-md-3"> <label>Frame Number</label></div>
                    <div class="col-md-3"> <label>Insurance Company </label></div>
                </div>
                <div class="row">
                  <div class="col-md-3"> {{$saleData['application_number']}}</div>
                  <div class="col-md-3"> {{$saleData['invoice']}} </div>
                  <div class="col-md-3"> {{$saleData['product_frame_number']}}</div>
                  <div class="col-md-3"> {{$saleData['insurance_co']}}</div>
                </div>
                <div class="row" style="border: 1px solid #f4f4f4;">
                    <div class="col-md-3"> <label>Total Amount</label></div>
                    <div class="col-md-3"> <label>Paid Amount</label></div>
                    <div class="col-md-3"> <label>Otc Invoice</label></div>
                    <div class="col-md-3"> <label>Pending Items </label></div>
                </div>
                <div class="row">
                  <div class="col-md-3"> {{$saleData['balance']}}</div>
                  <div class="col-md-3"> {{$saleData['total_payment']}} </div>
                  <div class="col-md-3"> {{$saleData['otc_invoice']}}</div>
                  <div class="col-md-3"> {{$saleData['pending_item']}}</div>
                </div>
                <div class="row" style="border: 1px solid #f4f4f4;">
                    <div class="col-md-3"> <label>Security Amount</label></div>
                    <div class="col-md-3"> <label>Booking Number</label></div>
                    <div class="col-md-3"> <label>Fuel Quantity (Ltr.)</label></div>
                    <div class="col-md-3"> <label>Sale Discount</label></div>
                </div>
                <div class="row">
                  <div class="col-md-3"> {{$securityData}}</div>
                  <div class="col-md-3"> {{$saleData['booking_number']}} </div>
                  <div class="col-md-3"> {{$saleData['fuel_qty']}}</div>
                  <div class="col-md-3"> {{$saleData['sale_discount_am']}}</div> 
                </div>
            <br>
        </div>
        <div class="col-md-12" style="height: 30px;background-color: #ecf0f5;z-index: 1;"></div>
        <div class="box box-primary">
            <!-- /.box-header -->
            <div class="box-header with-border">
<!--                    <h3 class="box-title">{{__('customer.mytitle')}} </h3>-->
                </div>  
                <div class="panel-group" id="accordion">
                
                
                <div class="panel panel-default">
                  <div class="panel-heading">
                  <a data-toggle="collapse" data-parent="#accordion" href="#collapseProduct">
                    <h4 class="panel-title">
                      Product Detail
                    </h4></a>
                  </div>
                  <div id="collapseProduct" class="panel-collapse collapse in">
                    <div class="panel-body">
                      <div class="row">
                        <div class="col-md-3"> <label>Model Category</label></div>
                        <div class="col-md-3"> <label>Model Name</label></div>
                        <div class="col-md-3"> <label>Model Variant</label></div>
                        <div class="col-md-3"> <label>Color Code</label></div>
                      </div>
                      <div class="row">
                        <div class="col-md-3"> {{$saleData['model_category']}}</div>
                        <div class="col-md-3"> {{$saleData['model_name']}} </div>
                        <div class="col-md-3"> {{$saleData['model_variant']}}</div>
                        <div class="col-md-3"> {{$saleData['color_code']}}</div>
                      </div>
                       <div class="row">
                        <div class="col-md-3"> <label>Basic Price</label></div>
                        {{-- <div class="col-md-3"> <label>MTOC</label></div> --}}
                      </div>
                      <div class="row">
                        <div class="col-md-3"> {{$saleData['basic_price']}}</div>
                        {{-- <div class="col-md-3"> {{$saleData['mtoc']}} </div> --}}
                      </div>
                 
                    </div>
                  </div>
                </div>
                <div class="panel panel-default">
                  <div class="panel-heading">
                    <a data-toggle="collapse" data-parent="#accordion" href="#collapseOrder">
                      <h4 class="panel-title">Customer Detail </h4>
                    </a>
                  </div>
                  <div id="collapseOrder" class="panel-collapse collapse">
                    <div class="panel-body">
                      @if($saleData['name'] == "")
                      <div class="row">
                        <div class="col-md-12 text-center">No data available </div>
                      </div>
                     @else
                      <div class="row" >
                      <div class="col-md-3"> <label>Customer</label></div>
                      <div class="col-md-3"> <label>Mobile</label></div>
                      <div class="col-md-3"> <label>Email</label></div>
                      <div class="col-md-3"> <label>Address</label></div>
                    </div>
                    <div class="row">
                      <div class="col-md-3"> {{$saleData['name']}}</div>
                      <div class="col-md-3"> {{$saleData['mobile']}} </div>
                      <div class="col-md-3"> {{$saleData['email_id']}}</div>
                      <div class="col-md-3"> {{$saleData['address']}}</div>
                    </div>
                    <div class="row" >
                      <div class="col-md-3"> <label>Relation</label></div>
                      <div class="col-md-3"> <label>Relation Type</label></div>
                      <div class="col-md-3"> <label>Pincode</label></div>
                      <div class="col-md-3"> <label>Reference</label></div>
                    </div>
                    <div class="row">
                      <div class="col-md-3"> {{$saleData['relation']}}</div>
                      <div class="col-md-3"> {{$saleData['relation_type']}} </div>
                      <div class="col-md-3"> {{$saleData['pin_code']}}</div>
                      <div class="col-md-3"> {{$saleData['reference']}}</div>
                    </div>
                    <div class="row" >
                      <div class="col-md-3"> <label>Aadhar Id</label></div>
                      <div class="col-md-3"> <label>Voter Id</label></div>
                      <!-- <div class="col-md-3"> <label>Pincode</label></div>
                      <div class="col-md-3"> <label>Reference</label></div> -->
                    </div>
                    <div class="row">
                      <div class="col-md-3"> {{$saleData['aadhar_id']}}</div>
                      <div class="col-md-3"> {{$saleData['voter_id']}} </div>
                     <!--  <div class="col-md-3"> {{$saleData['pin_code']}}</div>
                      <div class="col-md-3"> {{$saleData['reference']}}</div> -->
                    </div>
                    @endif
                    </div>
                    </div>
                </div>
                @if($saleData['financer_name'])
                <div class="panel panel-default">
                  <div class="panel-heading">
                    <a data-toggle="collapse" data-parent="#accordion" href="#collapseFinance">
                      <h4 class="panel-title">Finance Detail </h4>
                    </a>
                  </div>
                  <div id="collapseFinance" class="panel-collapse collapse">
                    <div class="panel-body">
                      @if($saleData['financer_name'] == "")
                      <div class="row">
                        <div class="col-md-12 text-center">No data available </div>
                      </div>
                     @else
                      <div class="row" >
                      <div class="col-md-3"> <label>Financer Name</label></div>
                      <div class="col-md-3"> <label>Company</label></div>
                      <div class="col-md-3"> <label>Amount</label></div>
                      <div class="col-md-3"> <label>Disbursement Amount</label></div>
                    </div>
                    <div class="row">
                      <div class="col-md-3"> {{$saleData['financer_name']}}</div>
                      <div class="col-md-3"> {{$saleData['company']}} </div>
                      <div class="col-md-3"> {{$saleData['loan_amount']}}</div>
                      <div class="col-md-3"> {{$saleData['disbursement_amount']}}</div>
                    </div>
                    <div class="row" >
                      <div class="col-md-3"> <label>Pending Disbursement Amount</label></div>
                    </div>
                    <div class="row">
                      <div class="col-md-3"> {{$saleData['pending_disbursement_amount']}}</div>
                    </div>
                    @endif
                    </div>
                  </div>
                </div>
                @endif
                <div class="panel panel-default">
                  <div class="panel-heading">
                  <a data-toggle="collapse" data-parent="#accordion" href="#collapsePayment">
                    <h4 class="panel-title">
                      Payment Detail
                    </h4></a>
                  </div>
                  <div id="collapsePayment" class="panel-collapse collapse">
                    <div class="panel-body">
                      <div class="row">
                        <div class="col-md-3"> <label>Paid Amount</label></div>
                        <div class="col-md-3"> {{$saleData['payment_amount']}}</div>
                      </div>
                      <table class="table table-bordered table-striped">
                      <thead>
                        <tr>
                        <th>Payment Mode</th>
                        <th>Type</th>
                        <th>Transaction Number</th>
                        <th>Transaction Charges</th>
                        <th>Receiver Bank Detail</th>
                        <th>Amount</th>
                        </tr>
                      </thead>
                      <tbody>
                      @if(isset($payData)) 
                        @if($payData == [])
                          <tr class="text-center">
                            <td valign="top" colspan="8" class="dataTables_empty">No data available in table</td>
                          </tr>
                        @else
                          @foreach($payData as $item)
                            <tr>
                              <td>@if(isset($item->payment_mode))  {{ucfirst(str_replace('_', ' ', $item->payment_mode))}} @endif</td>
                              <td>@if(isset($item->type))  {{$item->type}} @endif</td>
                              <td>@if(isset($item->transaction_number))  {{$item->transaction_number}} @endif</td>
                              <td>@if(isset($item->transaction_charges))  {{$item->transaction_charges}} @endif</td>
                              <td>@if(isset($item->receiver_bank_detail))  {{$item->receiver_bank_detail}} @endif</td>
                              <td>@if(isset($item->amount))  {{$item->amount}} @endif</td>
                            </tr>
                          @endforeach
                        @endif
                      @endif
                      </tbody>
                    </table>
                 
                    </div>
                  </div>
                </div>
                <div class="panel panel-default">
                  <div class="panel-heading">
                    <a data-toggle="collapse" data-parent="#accordion" href="#collapseCustomer">
                       <h4 class="panel-title"> Order Detail </h4>
                    </a>
                  </div>
                  <div id="collapseCustomer" class="panel-collapse collapse ">
                    <div class="panel-body">
                       @if($saleData['product_frame_number'] == "")
                      <div class="row">
                        <div class="col-md-12 text-center">No data available </div>
                      </div>
                     @else
                      <div class="row" >
                        <div class="col-md-3"> <label>Frame Number</label></div>
                        <div class="col-md-3"> <label>Battery Number</label></div>
                        <div class="col-md-3"> <label>Key Number</label></div>
                        <div class="col-md-3"> <label>Tyre Make</label></div>
                      </div>
                      <div class="row">
                        <div class="col-md-3"> {{$saleData['product_frame_number']}}</div>
                        <div class="col-md-3"> {{$saleData['battery_number']}} </div>
                        <div class="col-md-3"> {{$saleData['key_number']}}</div>
                        <div class="col-md-3"> {{$saleData['tyre_make']}}</div>
                      </div>
                      <div class="row" >
                        <div class="col-md-3"> <label>Front Tyre Number</label></div>
                        <div class="col-md-3"> <label>Rear Tyre Number</label></div>
                        <div class="col-md-3"> <label>Quantity</label></div>
                        <div class="col-md-3"> <label>Amount</label></div>
                      </div>
                      <div class="row">
                        <div class="col-md-3"> {{$saleData['front_tyre_no']}}</div>
                        <div class="col-md-3"> {{$saleData['rear_tyre_no']}}</div>
                        <div class="col-md-3"> {{$saleData['quantity']}} </div>
                        <div class="col-md-3"> {{$saleData['amount']}}</div>
                      </div>
                      <div class="row">
                        <div class="col-md-3"> <label>Order Date</label></div>
                      </div>
                      <div class="row">
                        <div class="col-md-3"> {{$saleData['order_date']}}</div>
                      </div>
                      @endif
                    </div>
                  </div>
                </div>
                <div class="panel panel-default">
                  <div class="panel-heading">
                    <a data-toggle="collapse" data-parent="#accordion" href="#collapseHirise">
                      <h4 class="panel-title">Hirise Detail </h4>
                    </a>
                  </div>
                  <div id="collapseHirise" class="panel-collapse collapse">
                    <div class="panel-body">
                      @if($saleData['amount'] == "")
                      <div class="row">
                        <div class="col-md-12 text-center">No data available </div>
                      </div>
                     @else
                      <div class="row" >
                        <div class="col-md-3"> <label>Hirise Invoice Number</label></div>
                        <div class="col-md-3"> <label>Amount</label></div>
                      </div>
                      <div class="row">
                        <div class="col-md-3"> {{$saleData['invoice']}}</div>
                        <div class="col-md-3"> {{$saleData['amount']}} </div>
                      </div>
                      @endif
                    </div>
                  </div>
                </div>
                <div class="panel panel-default">
                  <div class="panel-heading">
                    <a data-toggle="collapse" data-parent="#accordion" href="#collapseIns">
                     <h4 class="panel-title">Insurance Detail</h4>
                    </a>
                  </div>
                  <div id="collapseIns" class="panel-collapse collapse">
                    <div class="panel-body">
                      @if($saleData['policy_number'] == "")
                      <div class="row">
                        <div class="col-md-12 text-center">No data available </div>
                      </div>
                     @else
                      <div class="row" >
                        <div class="col-md-3"> <label>Policy Number</label></div>
                        <div class="col-md-3"> <label>Insurance Company</label></div>
                        <div class="col-md-3"> <label>Insurance Type</label></div>
                        <div class="col-md-3"> <label>Insurance Amount</label></div>
                      </div>
                      <div class="row">
                        <div class="col-md-3"> {{$saleData['policy_number']}}</div>
                        <div class="col-md-3"> {{$saleData['insurance_co']}} </div>
                        <div class="col-md-3"> {{$saleData['insurance_type']}}</div>
                        <div class="col-md-3"> {{$saleData['insurance_amount']}}</div>
                      </div>
                      @endif
                    </div>
                  </div>
                </div>
                <div class="panel panel-default">
                  <div class="panel-heading">
                    <a data-toggle="collapse" data-parent="#accordion" href="#collapseRto">
                      <h4 class="panel-title">  RTO Detail  </h4>
                    </a>
                  </div>
                  <div id="collapseRto" class="panel-collapse collapse">
                    <div class="panel-body">
                      @if($saleData['application_number'] == "")
                      <div class="row">
                        <div class="col-md-12 text-center">No data available </div>
                      </div>
                     @else
                      <div class="row" >
                        <div class="col-md-3"> <label>Application Number</label></div>
                        <div class="col-md-3"> <label>Rto Finance</label></div>
                        <div class="col-md-3"> <label>Rc Number</label></div>
                        <div class="col-md-3"> <label>Rto Amount</label></div>
                      </div>
                      <div class="row">
                        <div class="col-md-3"> {{$saleData['application_number']}}</div>
                        <div class="col-md-3"> {{$saleData['rto_finance']}} </div>
                        <div class="col-md-3"> {{$saleData['rc_number']}}</div>
                        <div class="col-md-3"> {{$saleData['rto_amount']}}</div>
                      </div>
                      <div class="row" >
                        <div class="col-md-3"> <label>Registration Number</label></div>
                        <div class="col-md-3"> <label>Rto Type</label></div>
                        <div class="col-md-3"> <label>Penalty Charge</label></div>
                        <div class="col-md-3"> <label>File Submission</label></div>
                      </div>
                      <div class="row">
                        <div class="col-md-3"> {{$saleData['registration_number']}}</div>
                        <div class="col-md-3"> {{ucfirst(str_replace('_', ' ', $saleData['rto_type']))}}</div>
                        <div class="col-md-3"> {{$saleData['penalty_charge']}}</div>
                        <div class="col-md-3">@if($saleData['file_submission'] == 1) Yes @else No @endif</div>
                      </div>
                       <div class="row" >
                        <div class="col-md-3"> <label>Rto Approve</label></div>
                        <div class="col-md-3"> <label>Approve Date</label></div>
                        <div class="col-md-3"> <label>File Uploaded</label></div>
                        <div class="col-md-3"> <label>Uploaded Date</label></div>
                      </div>
                      <div class="row">
                        <div class="col-md-3">@if($saleData['approve'] == 1) Yes @else No @endif</div>
                        <div class="col-md-3"> {{$saleData['approval_date']}} </div>
                        <div class="col-md-3">@if($saleData['file_uploaded'] == 1) Yes @else No @endif</div>
                        <div class="col-md-3"> {{$saleData['uploaded_date']}}</div>
                      </div>
                       <div class="row" >
                        <div class="col-md-3"> <label>Front Lid</label></div>
                        <div class="col-md-3"> <label>Rear Lid</label></div>
                        <div class="col-md-3"> <label>Receiving Date</label></div>
                       <!--  <div class="col-md-3"> <label>Uploaded Date</label></div> -->
                      </div>
                      <div class="row">
                        <div class="col-md-3"> {{$saleData['front_lid']}}</div>
                        <div class="col-md-3"> {{$saleData['rear_lid']}} </div>
                        <div class="col-md-3"> {{$saleData['receiving_date']}}</div>
                       <!--  <div class="col-md-3"> {{$saleData['uploaded_date']}}</div> -->
                      </div>
                      @endif
                    </div>
                  </div>
                </div>

                <div class="panel panel-default">
                  <div class="panel-heading">
                  <a data-toggle="collapse" data-parent="#accordion" href="#collapsePending">
                    <h4 class="panel-title">
                      Pending Items Detail
                    </h4></a>
                  </div>
                  <div id="collapsePending" class="panel-collapse collapse">
                    <div class="panel-body">
                       @if($saleData['pending_other'] == "")
                      <div class="row">
                        <div class="col-md-12 text-center">No data available </div>
                      </div>
                     @else
                      <div class="row">
                        <div class="col-md-3"> <label>Other Accessories</label></div>
                        <div class="col-md-3"> {{$saleData['pending_other']}}</div>
                      </div>
                      <table class="table table-bordered table-striped">
                      <thead>
                        <tr>
                        <th>Accessories Name</th>
                        <th>Accessories Part No.</th>
                        <th>Unit Price</th>
                        </tr>
                      </thead>
                      <tbody>
                        @if(isset($accData)) 
                        @foreach($accData as $item)
                        <tr>
                        <td>@if(isset($item->name))  {{$item->name}} @endif</td>
                        <td>@if(isset($item->part_number))  {{$item->part_number}} @endif</td>
                        <td>@if(isset($item->unit_price))  {{$item->unit_price}} @endif</td>
                      </tr>
                        @endforeach
                        @endif
                      </tbody>
                    </table>
                    @endif
                    </div>
                  </div>
                </div>
                 <div class="panel panel-default">
                  <div class="panel-heading">
                  <a data-toggle="collapse" data-parent="#accordion" href="#collapseOtc">
                    <h4 class="panel-title">
                      OTC Detail
                    </h4></a>
                  </div>
                  <div id="collapseOtc" class="panel-collapse collapse">
                    <div class="panel-body">
                      @if($saleData['otc_invoice'] == "")
                      <div class="row">
                        <div class="col-md-12 text-center">No data available </div>
                      </div>
                     @else
                      <div class="row" >
                        <div class="col-md-3"> <label>Invoice Number</label></div>
                        <div class="col-md-3"> <label>Date</label></div>
                        <div class="col-md-3"> <label>OTC Amount</label></div>
                      </div>
                      <div class="row">
                        <div class="col-md-3"> {{$saleData['otc_invoice']}}</div>
                        <div class="col-md-3"> {{$saleData['otc_date']}} </div>
                        <div class="col-md-3"> {{$saleData['otc_amount']}}</div>
                      </div>
                      @endif
                    </div>
                  </div>
                </div>
                </div>
              </div>
                
        </div>
                <!-- /.box-body -->
              </div>
              
        <!-- /.box -->
      </section>
@endsection
