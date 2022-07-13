@extends($layout)
@section('title', __('View Job Card'))
@section('breadcrumb')
<li><a href="/admin/service/jobcard/list"><i class=""></i>Job Card Entry List</a></li>
@endsection
@section('css')
<link rel="stylesheet" href="/css/responsive.bootstrap.css">
<link rel="stylesheet" href="/css/bootstrap-duration-picker.css">
<style>
   tr.dangerClass{
   background-color: #f8d7da !important;
   }
   tr.successClass{
   background-color: #d4edda !important;
   }
   tr.worningClass{
   background-color: #fff3cd !important;
   }
</style>
@endsection
@section('js')
<script src="/js/dataTables.responsive.js"></script>
<script src="/js/bootstrap-duration-picker.js"></script>

<script src="https://momentjs.com/downloads/moment-with-locales.js"></script>
@endsection
@section('main_section')
<section class="content">
   <div id="app">

         @include('admin.flash-message')
         @yield('content')
         <div class="box-header with-border">
         </div>
   </div>
   
   <!-- Default box --> 
    <div class="box-header with-border">
        <div class='box box-default'>
            <div class="container-fluid">
                <br>
                  <div class="row" style="border: 1px solid #f4f4f4;">
                    <div class="col-md-3"> <label>Tag</label></div>
                    <div class="col-md-3"> <label>Frame</label></div>
                    <div class="col-md-3"> <label>Registration</label></div>
                    <div class="col-md-3"> <label>Product Name</label></div>
                  </div>
                  <div class="row">
                    <div class="col-md-3">{{$jobCard->tag}}</div>
                    <div class="col-md-3"> {{$jobCard->frame}} </div>
                    <div class="col-md-3">{{$jobCard->registration}}</div>
                    <div class="col-md-3">{{$jobCard->product_name}}</div>
                  </div><br>
                  <div class="row" style="border: 1px solid #f4f4f4;">
                    <div class="col-md-3"> <label>Store Name</label></div>
                    <div class="col-md-3"> <label>Selling Dealer</label></div>
                    <div class="col-md-3"> <label>Manufacturing Year</label></div>
                    <div class="col-md-3"> <label>Sale Date</label></div>
                  </div>
                  <div class="row">
                    <div class="col-md-3">{{$jobCard->store_name}}</div>
                    <div class="col-md-3"> {{$jobCard->selling_dealer_name}} </div>
                    <div class="col-md-3">{{$jobCard->manufacturing_year}}</div>
                    <div class="col-md-3">{{$jobCard->sale_date}}</div>
                  </div><br>
                  <div class="row" style="border: 1px solid #f4f4f4;">
                    <div class="col-md-3"> <label>Customer Name</label></div>
                    <div class="col-md-3"> <label>Mobile No.</label></div>
                    <div class="col-md-3"> <label>Job Card Type</label></div>
                    <div class="col-md-3"> <label>Customer Status</label></div>
                  </div>
                  <div class="row">
                    <div class="col-md-3">{{$jobCard->customer_name}}</div>
                    <div class="col-md-3"> {{$jobCard->mobile}} </div>
                    <div class="col-md-3">{{$jobCard->job_card_type}}</div>
                    <div class="col-md-3">{{$jobCard->customer_status}}</div>
                  </div><br>
                  <div class="row" style="border: 1px solid #f4f4f4;">
                    <div class="col-md-3"> <label>Service In Time</label></div>
                    <div class="col-md-3"> <label>Service Out Time</label></div>
                    <div class="col-md-3"> <label>Vehicle K.M.</label></div>
                    <div class="col-md-3"> <label>Status</label></div>
                  </div>
                  <div class="row">
                    <div class="col-md-3">{{date('H:i', strtotime($jobCard->service_in_time))}}</div>
                    <div class="col-md-3">{{date('H:i', strtotime($jobCard->service_out_time))}}</div>
                    <div class="col-md-3">{{$jobCard->vehicle_km}}</div>
                    <div class="col-md-3">{{$jobCard->status}}</div>
                  </div><br>
                  <div class="row" style="border: 1px solid #f4f4f4;">
                    <div class="col-md-3"> <label>Estimate Service Duration(HH:MM)</label></div>
                    <div class="col-md-3"> <label>Estimate Delivery</label></div>
                    <div class="col-md-3"> <label>Oil change (Infront of Customer)</label></div>
                    <div class="col-md-3"> <label>Service Status</label></div>
                  </div>
                <div class="row">
                  <div class="col-md-3"> {{$jobCard->service_duration}}</div>
                   @php $time = date('H:i', strtotime($jobCard->estimated_delivery_time)) @endphp
                  <div class="col-md-3"> {{$time}} </div>
                  <div class="col-md-3"> {{$jobCard->oilinfornt_customer}}</div>
                  <div class="col-md-3"> {{$jobCard->service_status}}</div>
                </div><br>
                <div class="row" style="border: 1px solid #f4f4f4;">
                    <div class="col-md-3"> <label>Wash</label></div>
                    <div class="col-md-3"> <label>Only Wash</label></div>
                    <div class="col-md-3"> <label>Final Inspection Status</label></div>
                    <div class="col-md-3"> <label>Payment Status</label></div>
                </div>
                <div class="row">
                    <div class="col-md-3">{{$jobCard->wash}}</div>
                    <div class="col-md-3">{{$jobCard->only_wash}}</div>
                    <div class="col-md-3">{{$jobCard->fi_status}}</div>
                    <div class="col-md-3">{{$jobCard->payment_status}}</div>
                </div><br>
                <div class="row" style="border: 1px solid #f4f4f4;">
                    <div class="col-md-3"> <label>Invoice No.</label></div>
                    <div class="col-md-3"> <label>Hirise Amount</label></div>
                    <div class="col-md-3"> <label>Pay Latter</label></div>
                    <div class="col-md-3"> <label>Mejor Job</label></div>
                </div>
                <div class="row">
                    <div class="col-md-3">{{$jobCard->invoice_no}}</div>
                    <div class="col-md-3">{{$jobCard->hirise_amount}}</div>
                    <div class="col-md-3">@if($jobCard->pay_letter == 1) Allowed @else Not Allowed @endif</div>
                    <div class="col-md-3">{{$jobCard->mejor_job}}</div>
                </div><br>
                <div class="row" style="border: 1px solid #f4f4f4;">
                    <div class="col-md-3"> <label>Amount</label></div>
                    <div class="col-md-3"> <label>Insurance Availability</label></div>
                    <div class="col-md-3"> <label>
                    @if($jobCard->insurance == 'Yes') Insurance Date @else Insurance Available Type @endif
                    </label></div>
                </div>
                <div class="row">
                    <div class="col-md-3">@php echo $service_chaege_total->total_amount + $part_details_total->total_amount @endphp</div>
                    <div class="col-md-3">{{$jobCard->insurance}}</div>
                    <div class="col-md-3">@if($jobCard->insurance == 'Yes') {{$jobCard->insurance_date}} @else {{$jobCard->insurance_available_type}}
                    @endif
                    </div>
                </div>
                <br><br>
             </div>
               <div class="col-md-12" style="height: 30px;background-color: #ecf0f5;z-index: 1;"></div>
                 <div class="box box-primary">
                    <!-- /.box-header -->
                    <div class="box-header with-border">
                    </div> 
                     <div class="panel-group" id="accordion">
                      @if (!empty($jobCard->recording) && file_exists(public_path().'/upload/audio/'.$jobCard->recording))
                        <div class="panel panel-default">
                          <div class="panel-heading">
                            <a data-toggle="collapse" data-parent="#accordion" href="#audio">
                               <h4 class="panel-title">Recording</h4>
                            </a>
                          </div>
                          <div id="audio" class="panel-collapse collapse">
                            <div class="panel-body">
                              <div class="row">
                              <table class="table table-bordered table-striped">
                              <thead>
                                <tr>
                                <th>Recording</th>
                                </tr>
                              </thead>
                              <tbody>
                                <tr>
                                  <td><audio controls="controls" preload="metadata">
                                      <source src="/upload/audio/{{$jobCard->recording}}"/>
                                  </audio></td>
                                </tr>
                              </tbody>
                            </table>
                         
                            </div>
                            
                            </div>
                          </div>
                        </div>
                      @endif

                      <div class="panel panel-default">
                        <div class="panel-heading">
                          <a data-toggle="collapse" data-parent="#accordion" href="#collapseCharges">
                             <h4 class="panel-title">Service Charges  &nbsp;&nbsp;(Total Charge {{$service_chaege_total->total_amount}})</h4>
                          </a>
                        </div>
                        <div id="collapseCharges" class="panel-collapse collapse in">
                          <div class="panel-body">
                            <table class="table table-bordered table-striped">
                            <thead>
                              <tr>
                              <th>Charge Type</th>
                              <th>Sub Type</th>
                              <th>Amount</th>
                              </tr>
                            </thead>
                            <tbody>
                              @if(isset($servicharge)) 
                              @if($servicharge =='[]')
                            <tr class="text-center">
                              <td valign="top" colspan="8" class="dataTables_empty">No data available in table</td>
                            </tr>
                           @else
                              @foreach($servicharge as $item)
                              <tr>
                              <td>@if(isset($item->charge_type))  {{$item->charge_type}} @endif</td>
                              <td>@if(isset($item->sub_type))  {{$item->sub_type}} @endif</td>
                              <td>@if(isset($item->amount))  {{$item->amount}} @endif</td>
                            </tr>
                              @endforeach
                              @endif
                                 @endif
                                 <tr>
                                  <td><b>Total Charge: {{$service_chaege_total->total_amount-$service_discount_total->total_amount}}</b></td>
                                   <td><b>Total Charge Discount: {{$service_discount_total->total_amount}}</b></td><td></td>
                                 </tr>
                            </tbody>
                          </table>
                       
                          </div>
                          
                          </div>
                        </div>
                      </div>
                      <div class="panel panel-default">
                        <div class="panel-heading">
                          <a data-toggle="collapse" data-parent="#accordion" href="#collapseparts">
                             <h4 class="panel-title">Parts Details  &nbsp;&nbsp;(Total Charge :  {{$part_details_total['total_amount']}} )</h4>
                          </a>
                        </div>
                        <div id="collapseparts" class="panel-collapse collapse">
                          <div class="panel-body">
                            <div class="row">
                            <table class="table table-bordered table-striped">
                            <thead>
                              <tr>
                              <th>Part Name</th>
                              <th>Part Number</th>
                              <th>Amount</th>
                              </tr>
                            </thead>
                            <tbody>
                              @if(isset($partdetails)) 
                              @if($partdetails =='[]')
                            <tr class="text-center">
                              <td valign="top" colspan="8" class="dataTables_empty">No data available in table</td>
                            </tr>
                           @else
                              @foreach($partdetails as $item)
                              <tr>
                              <td>@if(isset($item->part_name))  {{$item->part_name}} @endif</td>
                              <td>@if(isset($item->part_number))  {{$item->part_number}} @endif</td>
                              <td>@if(isset($item->price))  {{$item->price}} @endif</td>
                            </tr>
                              @endforeach
                              @endif
                                 @endif
                            </tbody>
                          </table>
                       
                          </div>
                          
                          </div>
                        </div>
                      </div>
                  </div>
                </div> 

           
        </div>
    </div>
   <!-- Modal -->
   <!-- /.box -->
</section>
@endsection