@extends($layout)

@section('title', __('OTC Sale View'))

@section('breadcrumb')
    <li><a href="/admin/sale/additional/services/list"><i class=""></i>OTC Sale List</a></li>
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
          <div class='box box-primary'>
            <div class="container-fluid">
                <div class="row ">
                <div class="col-md-12 ">
                  <div class="row">
                  <div class="col-md-3"> <label>Sale Number</label></div>
                  <div class="col-md-3" ><label style="font-weight: 600!important">{{$servicesData->sale_no}}</label></div>
                  <div class="col-md-3"> <label>Name</label></div>
                  <div class="col-md-3" ><label style="font-weight: 600!important">{{$servicesData->name}}</label></div>
                </div>
                <div class="row">
                  <div class="col-md-3"> <label>Mobile Number</label></div>
                  <div class="col-md-3" ><label style="font-weight: 600!important">{{$servicesData->mobile}}</label></div>
                  <div class="col-md-3"> <label>Total Amount</label></div>
                  <div class="col-md-3" ><label style="font-weight: 600!important">{{$servicesData->total_amount}}</label></div>
                </div>
                <div class="row">
                  <div class="col-md-3"> <label>EW Cost</label></div>
                  <div class="col-md-3" ><label style="font-weight: 600!important">{{$servicesData->ew_cost}}</label></div>
                  <div class="col-md-3"> <label>EW Duration</label></div>
                  <div class="col-md-3" ><label style="font-weight: 600!important">{{$servicesData->ew_duration}}</label></div>
                </div>
                <div class="row">
                  <div class="col-md-3"> <label>AMC Cost</label></div>
                  <div class="col-md-3" ><label style="font-weight: 600!important">{{$servicesData->amc_cost}}</label></div>
                  <div class="col-md-3"> <label>HJC Cost</label></div>
                  <div class="col-md-3" ><label style="font-weight: 600!important">{{$servicesData->hjc_cost}}</label></div>
                </div>
                <div class="row">
                  <div class="col-md-3"> <label>Balance</label></div>
                  <div class="col-md-3" ><label style="font-weight: 600!important">{{$servicesData->balance}}</label></div>
                </div>
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
                    <tr>
                      <th>{{__('Accessories Name')}}</th>
                      <th>{{__('Type')}}</th>
                      <th>{{__('Part Number')}}</th>
                      <th>{{__('Quantity')}}</th>
                      <th>{{__('Amount')}}</th>                    
                    </tr>
                    </thead>
                    <tbody>
                      @if($accessriesData == "[]")
                      <tr class="text-center">
                        <td valign="top" colspan="8" class="dataTables_empty">No data available in table</td>
                      </tr>
                     @else
                        @foreach($accessriesData as $acc)
                        <tr>
                          <td>{{$acc['name']}}</td>
                          <td>{{$acc['type']}}</td>
                          <td>{{$acc['part_no']}}</td>
                          <td>{{$acc['qty']}}</td>
                          <td>{{$acc['amount']}}</td>
                        </tr>
                        @endforeach
                        @if(count($saleDiscount)>0)
                          @foreach($saleDiscount as $saledisc)
                            <tr>
                              <td>Discount(-)</td>
                              <td>{{$saledisc['mod_dis_type']}}</td>
                              <td></td>
                              <td></td>
                              <td>{{$saledisc['amount']}}</td>
                            </tr>
                          @endforeach
                        @endif
                          <tr>
                            <td>Balance</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>{{$servicesData->balance}}</td>
                          </tr>
                      @endif
                    </tbody>
               
                  </table>
           
                </div>
                <!-- /.box-body -->
              </div>
        <!-- /.box -->
      </section>
@endsection
