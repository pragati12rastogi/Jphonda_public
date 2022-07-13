@extends($layout)
@section('title', __('Job Card Charges'))
@section('breadcrumb')
<li><a href="#"><i class=""></i></a></li>
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
<script>
  

   $(document).ready(function(){
      var job_card_type = @php echo json_encode($job_card_type); @endphp;
      var partdata = @php echo json_encode($partcharges); @endphp;
      var EnginePart = @php echo json_encode($EnginePart); @endphp;
      var charge = @php echo json_encode($charges); @endphp;
      var service_charge = @php echo json_encode($service_charge); @endphp;
      var labourcharge = @php echo json_encode($labourcharge); @endphp;
      var pickup_charge = @php echo json_encode($pickup_charge); @endphp;
      var garaj_amount = @php echo json_encode($garaj_amount); @endphp;
      var getRate = @php echo json_encode($getRate); @endphp;
      var amcDisc = @php echo json_encode($AmcDisc); @endphp;

      //Part Charges
      var totalPart = 0;
      $.each(partdata, function (key, val) {
        var price = val.price*val.issue_qty;
        var tampered = val.tampered;
        if (tampered == 'no') {
          price = 0;
        }
        totalPart = totalPart+price;
      });
        if (job_card_type == 'HJC' && getRate['partRate'] && totalPart > 0) {
          totalPart = totalPart-totalPart*(getRate['partRate']/100)
        }
        if (job_card_type == 'AMC' && amcDisc['part'] && totalPart > 0) {
          totalPart = totalPart-totalPart*(amcDisc['part']/100)
        }


     //Engine Part Charges
      var totalEngine = 0;
      $.each(EnginePart, function (key, val) {
        var price = val.price*val.issue_qty;
        var tampered = val.tampered;
        if (tampered == 'no') {
          price = 0;
        }
        totalEngine = totalEngine+price;
      });

      if (job_card_type == 'HJC' && getRate['engineRate'] && totalEngine > 0) {
          totalEngine = totalEngine-totalEngine*(getRate['engineRate']/100)
      }
        //Charges
      var charges = 0;
      $.each(charge, function (key, val) {
        var price = val.amount;
         charges = charges+price;
      });
      //Service Charges
      var ServiceChargeAmt = 0;
      $.each(service_charge, function (key, val) {
        var price = val.amount;
         ServiceChargeAmt = ServiceChargeAmt+price;
      });

      if (job_card_type == 'HJC' && getRate['serviceRate'] && ServiceChargeAmt > 0) {
          ServiceChargeAmt = ServiceChargeAmt-ServiceChargeAmt*(getRate['serviceRate']/100)
      }

      if (job_card_type == 'AMC' && amcDisc['service'] && ServiceChargeAmt > 0) {
          ServiceChargeAmt = ServiceChargeAmt-ServiceChargeAmt*(amcDisc['service']/100)
        }
      //Labour Charges
      var LabourChargeAmt = 0;
      $.each(labourcharge, function (key, val) {
        var price = val.amount;
         LabourChargeAmt = LabourChargeAmt+price;
      });

      if (job_card_type == 'HJC' && getRate['labourRate'] && LabourChargeAmt > 0) {
          LabourChargeAmt = LabourChargeAmt-LabourChargeAmt*(getRate['labourRate']/100)
      }

      if (job_card_type == 'AMC' && amcDisc['labour'] && LabourChargeAmt > 0) {
          LabourChargeAmt = LabourChargeAmt-LabourChargeAmt*(amcDisc['labour']/100)
        }
      
       //Pickup Charges
      var PickupChargeAmt = 0;
      $.each(pickup_charge, function (key, val) {
        var price = val.amount;
         PickupChargeAmt = PickupChargeAmt+price;
      });

      if (job_card_type == 'HJC' && getRate['pickupRate'] && PickupChargeAmt > 0) {
          PickupChargeAmt = PickupChargeAmt-PickupChargeAmt*(getRate['pickupRate']/100)
      }


     var discount = @php echo json_encode($discount); @endphp;
     var total_amount = parseInt(totalPart)+parseInt(totalEngine)+parseInt(charges)+parseInt(ServiceChargeAmt)+parseInt(LabourChargeAmt)+parseInt(garaj_amount)+parseInt(PickupChargeAmt);
     var grand_total = parseInt(total_amount)-parseInt(discount);
     $("#total").html(total_amount);
     $("#grand_total").html(grand_total);

   });


</script>
@endsection
@section('main_section')
<section class="content">
   <div id="app">

         @include('admin.flash-message')
         @yield('content')
         <div class="box-header with-border">
         </div>
   </div>
   
  
    <div class="box-header with-border">
        <div class="box box-default">
            <div class="container-fluid">
            <div class="box-body">
                    <table id="table" class="table table-bordered table-striped">
                        
                        <tbody>
                          <thead>
                            <th colspan="3">Charge</th>
                            <th>Sub Type</th>
                            <th>Amount</th>
                          </thead>
                          @php $i = 1 @endphp
                          @if(isset($charges))
                          @foreach($charges as $charge)
                            <tr>
                                <td colspan="3">{{$charge->charge_type}}</td>
                                <td>{{$charge->sub_type}}</td>
                                <td>{{$charge->amount}}</td>
                            </tr>
                            @php $i++ @endphp
                            @endforeach
                          @endif

                          @if(isset($labourcharge))
                          @foreach($labourcharge as $charge)
                            <tr>
                                <td colspan="3">{{$charge->charge_type}}</td>
                                <td>{{$charge->sub_type}}</td>
                                <td>{{$charge->amount}}</td>
                            </tr>
                            @php $i++ @endphp
                            @endforeach
                          @endif

                          @if(isset($service_charge))
                          @foreach($service_charge as $charge)
                            <tr>
                                <td colspan="3">{{$charge->charge_type}}</td>
                                <td>{{$charge->sub_type}}</td>
                                <td>{{$charge->amount}}</td>
                            </tr>
                            @php $i++ @endphp
                            @endforeach
                          @endif

                          @if(isset($pickup_charge))
                          @foreach($pickup_charge as $charge)
                            <tr>
                                <td colspan="3">{{$charge->charge_type}}</td>
                                <td>{{$charge->sub_type}}</td>
                                <td>{{$charge->amount}}</td>
                            </tr>
                            @php $i++ @endphp
                            @endforeach
                          @endif

                            <thead>
                            <th colspan="3">Part Number</th>
                            <th>Quantity</th>
                            <th>Amount</th>
                          </thead>
                          
                              @php $i = 1 @endphp
                              @if(isset($partcharges))
                             @foreach($partcharges as $part)
                            <tr>
                                <td colspan="3">{{$part['part_number']}}</td>
                                <td>{{$part['issue_qty']}}</td>
                                <td id="part_amount-{{$i}}">@if($part['tampered'] == 'no') 0 @else {{$part['issue_qty']*$part['price']}} @endif</td>
                            </tr>
                            @php $i++ @endphp
                            @endforeach
                            @endif
                             @if(isset($EnginePart))
                             @foreach($EnginePart as $part)
                            <tr>
                                <td colspan="3">{{$part['part_number']}}</td>
                                <td>{{$part['issue_qty']}}</td>
                                <td id="part_amount-{{$i}}">@if($part['tampered'] == 'no') 0 @else {{$part['issue_qty']*$part['price']}} @endif</td>
                            </tr>
                            @php $i++ @endphp
                            @endforeach
                            @endif
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <th> Garage Charge : </th>
                                <td>{{$garaj_amount}}</td>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <th> Total : </th>
                                <td id="total"></td>
                            </tr>
                             <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <th> Discount : </th>
                                <td>{{$discount}}</td>
                            </tr>
                             
                        </tbody>               
                    </table>
                    <!-- /.box-body -->
                    <table id="table" class="table table-bordered table-striped">
                        <tbody>
                            <tr>
                                <td colspan="3">&nbsp;</td>
                                <th>Grand Total : </th>
                                <td id="grand_total"></td>
                            </tr>
                           
                        </tbody>               
                    </table>
            </div>
        </div>
    </div>
</div>
   <!-- Modal -->
   <!-- /.box -->
</section>
@endsection