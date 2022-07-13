@extends($layout)

@section('title', __('dashboard.title'))


@section('breadcrumb')
    <li><a href="#"><i class=""></i> DashBoard</a></li>
    
@endsection
@section('js')
<!--
<script>
  $(function () {
    $('#example1').DataTable()
    $('#example2 , #example3 , #example4').DataTable({
      'paging'      : true,
      'lengthChange': false,
      'searching'   : false,
      'ordering'    : true,
      'info'        : true,
      'autoWidth'   : false
    })
  })
</script>
-->
@endsection
@section('main_section')
    <section class="content">
        <div class="row">
            <div class="col-lg-3 col-xs-6">
              <!-- small box -->
              <div class="small-box bg-aqua">
                <div class="inner">
                  <h3>150</h3>

                  <p>New Orders</p>
                </div>
<!--
                <div class="icon">
                  <i class="ion ion-bag"></i>
                </div>
-->
                <a href="#" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
              </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-xs-6">
              <!-- small box -->
              <div class="small-box bg-green">
                <div class="inner">
                  <h3>53<sup style="font-size: 20px; color: #ffffff;">%</sup></h3>

                  <p>Bounce Rate</p>
                </div>
<!--
                <div class="icon">
                  <i class="ion ion-stats-bars"></i>
                </div>
-->
                <a href="#" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
              </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-xs-6">
              <!-- small box -->
              <div class="small-box bg-yellow">
                <div class="inner">
                  <h3>44</h3>

                  <p>User Registrations</p>
                </div>
<!--
                <div class="icon">
                  <i class="ion ion-person-add"></i>
                </div>
-->
                <a href="#" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
              </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-xs-6">
              <!-- small box -->
              <div class="small-box bg-red">
                <div class="inner">
                  <h3>65</h3>

                  <p>Unique Visitors</p>
                </div>
<!--
                <div class="icon">
                  <i class="ion ion-pie-graph"></i>
                </div>
-->
                <a href="#" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
              </div>
            </div>
            <!-- ./col -->
        </div>
        <!-- Default box -->
        <div class="row">
            <div class="col-sm-6">
                <div class="box">
                  <div class="box-header with-border">
                    <h3>
                        Stock Movement
                          
                    </h3>
                  </div>
                  <div class="box-body">
                    <table id="example2" class="table table-bordered table-hover">
                        <thead>
                        <tr>
                          <th>From</th>
                          <th>To</th>
                          <th>Quantity</th>
                          <th>Status</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr class="pending">
                          <td>Store1</td>
                          <td>Store2</td>
                          <td>2</td>
                          <td>Pending</td>
                        </tr>
                        <tr class="moved">
                          <td>Store1</td>
                          <td>Store2</td>
                          <td>2</td>
                          <td>Moved</td>
                        </tr>
                    </table>  
                  </div>
                    <form action='/masters/create/Users' method='post'>
                        @csrf


                    </form>
         
                        <!-- /.box-body -->
         
                <!-- /.box-footer-->
                </div>
        <!-- /.box -->
            </div>
            
            <div class="col-sm-6">
                <div class="box">
                  <div class="box-header with-border">
                    <h3>
                        Unload Details
                    </h3>
                  </div>
                  <div class="box-body">
                    <table id="example3" class="table table-bordered table-hover">
                        <thead>
                        <tr>
                          <th>LRN</th>
                          <th>Storename</th>
                          <th>Unload Details</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                          <td>1811-12-02742</td>
                          <td>Store1</td>
                          <td>2019-10-10</td>
                        </tr>
                        <tr>
                          <td>1811-12-02742</td>
                          <td>Store1</td>
                          <td>2019-10-10</td>
                        </tr>   
                        
                        
                    </table>  
                  </div>
                    <form action='/masters/create/Users' method='post'>
                        @csrf


                    </form>
         
                        <!-- /.box-body -->
         
                <!-- /.box-footer-->
                </div>
        <!-- /.box -->
            </div>
            
            <div class="col-sm-6">
                <div class="box">
                  <div class="box-header with-border">
                    <h3>
                        Damage Claim
                    </h3>
                  </div>
                  <div class="box-body">
                    <table id="example4" class="table table-bordered table-hover">
                        <thead>
                        <tr>
                          <th>Product Name</th>
                          <th>Estimate Amount</th>
                          <th>Status</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr class="pending">
                          <td>SC-ACTIVA 5G-OX-NHB63</td>
                          <td>2000</td>
                          <td>Pending</td>
                        </tr>
                        <tr class="pending">
                          <td>SC-ACTIVA 5G-OX-NHB63</td>
                          <td>2000</td>
                          <td>Pending</td>
                        </tr>   
                    </table>  
                  </div>
                    <form action='/masters/create/Users' method='post'>
                        @csrf


                    </form>
         
                        <!-- /.box-body -->
         
                <!-- /.box-footer-->
                </div>
        <!-- /.box -->
            </div>
            
            <div class="col-sm-6">
                <div class="box">
                  <div class="box-header with-border">
                    <h3>
                        Battery Details
                    </h3>
                  </div>
                  <div class="box-body">
                    <table id="example2" class="table table-bordered table-hover">
                        <thead>
                        <tr>
                          <th>Battery No</th>
                          <th>Manufacture Date</th>
                          <th>Status</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr class="expired">
                          <td>GHG5434</td>
                          <td>2019-10-1</td>
                          <td>Expired</td>
                        </tr>
                            
                       <tr class="expired">
                          <td>KGF547</td>
                          <td>2019-10-20</td>
                          <td>Expired</td>
                        </tr>
                        
                        
                    </table>  
                  </div>
                    <form action='/masters/create/Users' method='post'>
                        @csrf


                    </form>
         
                        <!-- /.box-body -->
         
                <!-- /.box-footer-->
                </div>
        <!-- /.box -->
            </div>
        </div>
        
      </section>

        <style>
            .expired , expired:hover
            {
                background: #f39c12 !important;
                color: #ffffff;
            }
            .pending , .pending:hover
            {
                background: #ec1c2f !important;
                color: #ffffff;
            }
            .moved , .moved:hover
            {
                background: green !important;
                color: #ffffff;
            }
        </style>

@endsection
{{-- {{CustomHelpers::coolText('hcjsd')}} --}}