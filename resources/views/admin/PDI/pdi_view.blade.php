@extends($layout)

@section('title', __('PDI View'))

@section('breadcrumb')
    <li><a href="#"><i class=""></i>PDI View</a></li>
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
                    <div class="col-md-3"> <label>Frame</label></div>
                    <div class="col-md-3"> <label>Model</label></div>
                    <div class="col-md-3"> <label>Variant</label></div>
                    <div class="col-md-3"> <label>Color</label></div>
                </div>
                <div class="row ">
                <div class="col-md-12 ">
                    <div class="col-md-3" ><label style="font-weight: 600!important">{{$pdi['frame']}}</label></div>
                    <div class="col-md-3" ><label style="font-weight: 600!important">{{$pdi['model_name']}}</label></div>
                    <div class="col-md-3" ><label style="font-weight: 600!important">{{$pdi['model_variant']}}</label></div>
                    <div class="col-md-3" ><label style="font-weight: 600!important">{{$pdi['color_code']}}</label></div>
                </div>
                </div>
                <div class="row" style="border: 1px solid #f4f4f4;">
                    <div class="col-md-3"> <label>Date of Damage</label></div>
                    <div class="col-md-3"> <label>Damage Location</label></div>
                    <div class="col-md-3"> <label>Date of Repair</label></div>
                    <div class="col-md-3"> <label>Repair Location</label></div>
                </div>
                 <div class="row ">
                <div class="col-md-12 ">
                    <div class="col-md-3" ><label style="font-weight: 600!important">{{date('d-m-Y',strtotime($pdi['date_of_damage']))}}</label></div>
                    <div class="col-md-3" ><label style="font-weight: 600!important">{{$pdi['damage_location']}}</label></div>
                    <div class="col-md-3" ><label style="font-weight: 600!important">{{date('d-m-Y',strtotime($pdi['date_of_repair']))}}</label></div>
                    <div class="col-md-3" ><label style="font-weight: 600!important">{{$pdi['repair_location']}}</label></div>
                </div>
                </div>
                 <div class="row" style="border: 1px solid #f4f4f4;">
                    <div class="col-md-3"> <label>Responsible Emp.</label></div>
                    <div class="col-md-3"> <label>Description</label></div>
                    <div class="col-md-3"> <label>Status</label></div>
                    <div class="col-md-3"> <label>Invoice</label></div>
                </div>
                 <div class="row ">
                <div class="col-md-12 ">
                    <div class="col-md-3" ><label style="font-weight: 600!important">{{$pdi['responsive_emp']}}</label></div>
                    <div class="col-md-3" ><label style="font-weight: 600!important">{{$pdi['desc_of_accident']}}</label></div>
                    <div class="col-md-3" ><label style="font-weight: 600!important">{{$pdi['status']}}</label></div>
                    <div class="col-md-3" ><label style="font-weight: 600!important">{{$pdi['hirise_invoice']}}</label></div>
                </div>
                </div>

                <div class="row" style="border: 1px solid #f4f4f4;">
                    <div class="col-md-3"> <label>Voucher Number</label></div>
                    <div class="col-md-3"> <label>Debit Amount</label></div>
                    <div class="col-md-3"> <label>
                      @if($pdi['load_ref'])
                        Transit Load Reference
                      @endif
                      @if($pdi['sale_executive_id'])
                        Unloading Executive
                      @endif
                      @if($pdi['location'])
                       Storage Location
                      @endif
                      @if($pdi['driver'])
                        Inter-Location Driver
                      @endif
                      @if($pdi['item_missing'])
                       Items Missing Quantity
                      @endif


                    </label></div>
                </div>
                <div class="row ">
                <div class="col-md-12 ">
                    <div class="col-md-3" ><label style="font-weight: 600!important">{{$pdi['voucher_no']}}</label></div>
                    <div class="col-md-3" ><label style="font-weight: 600!important">{{$pdi['debit_amt']}}</label></div>
                    <div class="col-md-3" ><label style="font-weight: 600!important">{{$detail}}</label></div>
                </div>
                </div>
            <br>
        </div>
        <div class="col-md-12" style="height: 30px;background-color: #ecf0f5;z-index: 1;"></div>
        <div class="box box-primary">
            <!-- /.box-header -->
            <div class="box-header with-border">
<!--                    <h3 class="box-title">{{__('customer.mytitle')}} </h3>-->
                </div>  
                <div class="box-body">
                   @include('admin.flash-message')
                    @yield('content')
                  <table id="admin_table" class="table table-bordered table-striped">
                    <thead>
                       @php
                        $count = count($pdi_details);
                       @endphp
                   
                    <tr>
                      <th>Part Number</th>
                      <th>Description Of Damaged Part/Missing Item</th>
                      <th>Repair/Replace</th>
                      <th>Part/Paint Amount</th>
                      <th>LAB Amount</th>
                      <th>Total</th>                     
                    </tr>
                    
                    </thead>
                    <tbody>
                       @for($i = 0 ; $i < $count ; $i++)
                       <tr>
                         <td>{{$pdi_details[$i]['part_number']}}</td>
                         <td>{{$pdi_details[$i]['desc_of_damage_part']}}</td>
                         <td>{{$pdi_details[$i]['repair_replace']}}</td>
                         <td>{{$pdi_details[$i]['part_amt']}}</td>
                         <td>{{$pdi_details[$i]['lab_amt']}}</td>
                         <td>{{$pdi_details[$i]['total']}}</td>
                       </tr>
                       @endfor
                       <tr>
                         <td></td>
                         <td></td>
                         <td></td>
                         <td></td>
                         <td colspan="2"><label>Total : {{$total_amount}}</label></td>
                       </tr>
                      
                    </tbody>
               
                  </table>
           
                </div>
                <!-- /.box-body -->
              </div>
              
        <!-- /.box -->
      </section>
@endsection
