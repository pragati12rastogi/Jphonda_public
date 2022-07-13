@extends($layout)

@section('title', __('Sale Call Summary'))

@section('user', Auth::user()->name)

@section('breadcrumb')

    <li><a href="#"><i class=""></i>{{__('Sale Call Summary')}}</a></li> 
@endsection
@section('css')

  <link rel="stylesheet" href="/css/responsive.bootstrap.css">
  <style>
    .content{
    padding: 30px;
  }
  .nav1>li>button {
    position: relative;
    display: block;
    padding: 10px 34px;
    background-color: white;
    margin-left: 10px;
  }

  @media (max-width: 768px)  
  {
    
    .content-header>h1 {
      display: inline-block;
      
    }
  }
  @media (max-width: 425px)  
  {
    
    .content-header>h1 {
      display: inline-block;
      
    }
  }
  .nav1>li>button.btn-color-active{
    background-color: rgb(135, 206, 250);
  }
  .nav1>li>button.btn-color-unactive{
    background-color: white;
  }
  .spanred{
    color: red;
  }

  th{
    text-align: center;
  }
  .info-color{
    background-color: #87cefa;
  }
  /* datepicker css */

*{margin:0;padding:0;}

/* jQuery UI Datepicker Styles */
/* states and images */
.ui-icon {
  display: block;
  text-indent: -99999px;
  overflow: hidden;
  background-repeat: no-repeat;
}
 
/* Overlays */
.ui-widget-overlay {
  position:relative;
  width: 100%;
  height: 100%; 
}
.ui-datepicker {
  position:relative;
  width: 170px;
  height:auto;
  padding: 0;
  display: none;
  margin:20px 0 0 23px;
 

}
.ui-datepicker .ui-datepicker-header {
  position: relative;
  padding: .2em 0;
  background:#f8f8f8;

}
.ui-datepicker .ui-datepicker-prev,
.ui-datepicker .ui-datepicker-next {
  position: absolute;
  top: 5px;
  width: 1.1em;
  height: .90em;
}
 

.ui-datepicker .ui-datepicker-next {
  right: 2px;
  background: url(https://img.icons8.com/flat_round/2x/arrow-right.png) bottom no-repeat;
}
.ui-datepicker .ui-datepicker-prev {
  background: url(https://img.icons8.com/flat_round/2x/arrow-left.png) top  no-repeat;
 }

.ui-datepicker .ui-datepicker-prev span,
.ui-datepicker .ui-datepicker-next span {
  display: block;
  position: absolute;
  left: 50%;
  margin-left: -8px;
  top: 50%;
  margin-top: -8px;
}
.ui-datepicker .ui-datepicker-title {
  margin: 0 2.3em;
  line-height: 1.8em;
  text-align: center;
}
.ui-datepicker .ui-datepicker-title select {
  font-size: 1em;
  margin: 1px 0;
}
.ui-datepicker select.ui-datepicker-month,
.ui-datepicker select.ui-datepicker-year {
  width: 45%;

}
.ui-datepicker table {
  width: 100%;
  font-size: .9em;
  border-collapse: collapse;
  margin: 0 0 .4em;
}
.ui-state-disabled .ui-state-default{
  background: grey!important;
}
.ui-datepicker th {
  padding: .7em .3em;
  text-align: center;

  border: 0;
}
.ui-datepicker{
  width: 20%!important;
  position: absolute;
    top: 616px!important;
    left: 782.5px!important;
    z-index: 1;
    display: block;
}
.ui-datepicker td {
  border: 0;
  padding: 1px;
}
.ui-datepicker td span,
.ui-datepicker td a {
  display: block;
  padding: .2em;
  text-align: center;
  text-decoration: none;
}
.ui-datepicker .ui-datepicker-buttonpane {
  background-image: none;
  margin: .7em 0 0 0;
  padding: 0 .2em;
  border-left: 0;
  border-right: 0;
  border-bottom: 0;
}
.ui-datepicker .ui-datepicker-buttonpane button {
  float: right;
  margin: .5em .2em .4em;
  cursor: pointer;
  padding: .2em .6em .3em .6em;
  width: auto;
  overflow: visible;
}
.ui-datepicker .ui-datepicker-buttonpane button.ui-datepicker-current {
  float: left;
}


 
 

/* Component containers
----------------------------------*/
.ui-widget {

  font-size: 1.1em;

}
.ui-widget .ui-widget {
  font-size: 5em;
}
.ui-widget input,
.ui-widget select,
.ui-widget textarea,
.ui-widget button {
  font-size: 1em;
}
.ui-widget-content {
  border: 1px solid #dddddd;
  color: #999999; /* Days Letter Color */
}
.ui-widget-content a {
  color: yellow;
}
.ui-widget-header {
  border-bottom: 1px solid #c5c5c5;
  color: #212020;
  font-weight: bold;
}
.ui-widget-header a {
  color: #ffffff;
}

/* Interaction states
----------------------------------*/
.ui-state-default,
.ui-widget-content .ui-state-default,
.ui-widget-header .ui-state-default {
  border: 1px solid #cccccc;
  background: #f6f6f6;
  color: #300808;
}
.ui-state-default a,
.ui-state-default a:link,
.ui-state-default a:visited {
  color: #999999;
  text-decoration: none;
}
.ui-state-hover,
.ui-widget-content .ui-state-hover,
.ui-widget-header .ui-state-hover,
.ui-state-focus,
.ui-widget-content .ui-state-focus,
.ui-widget-header .ui-state-focus {
 
  background: #1ca3f4;
  color: #fff;
  text-align:center;
}
.ui-state-hover a,
.ui-state-hover a:hover,
.ui-state-hover a:link,
.ui-state-hover a:visited,
.ui-state-focus a,
.ui-state-focus a:hover,
.ui-state-focus a:link,
.ui-state-focus a:visited {
  color: #c77405;
  text-decoration: none;
}
.ui-state-active,
.ui-widget-content .ui-state-active,
.ui-widget-header .ui-state-active {
  border: 1px solid #fbd850;
  color: white;
}
.ui-state-active a,
.ui-state-active a:link,
.ui-state-active a:visited {
  /*color: #eb8f00;*/
  text-decoration: none;
}




  </style>

@endsection
@section('js')

<script src="/js/dataTables.responsive.js"></script>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.9.1/jquery-ui.js"></script>
<script>
$(document).ready(function(){

     $(function() {
      $( "#datepicker" ).datepicker({ maxDate: 15});
    });

  var type = @php echo json_encode($type); @endphp;
 // console.log(type);
  if(type == 'pending' || type == '')
  {
    $('#callForm').addClass('btn-color-active');
    $('#delivered').removeClass('btn-color-active');
    $("#callingForm").show();
    $("#deliveryForm").hide();
  }
  else if(type == 'all'){
    $('#delivered').addClass('btn-color-active');
    $('#callForm').removeClass('btn-color-active');
    $("#callingForm").hide();
    $("#deliveryForm").show();
  }

  $("#callForm").on('click',function(){
    $(this).addClass('btn-color-active');
    $('#delivered').removeClass('btn-color-active');
    $("#callingForm").show();
    $("#deliveryForm").hide();
  });
  $("#delivered").on('click',function(){
    $(this).addClass('btn-color-active');
    $('#callForm').removeClass('btn-color-active');
    $("#callingForm").hide();
    $("#deliveryForm").show();
  });

});
</script>
@endsection

@section('main_section')
    <section class="content">
        <div id="app">
            @include('admin.flash-message')
            @yield('content')
            
        </div>
        
    <!-- Default box -->
        <div class="box">
          <div class="box-header with-border">
            {{-- 
            <h3 class="box-title">{{__('customer.mytitle')}} </h3>
            --}}
            <div class="col-md-6">

              <ul class="nav nav1 nav-pills">
                <li class="nav-item">
                  <button class="nav-link1 btn-color-active" id="callForm" >Call's Form</button>
                </li>
                <li class="nav-item">
                  <button class="nav-link1 " id="delivered" >Delivered</button>
                </li> 
              </ul>
            </div>
              <div class="col-md-6">
              <a href="/admin/call/Sale/nextcall?saleId={{$data['sale_id']}}&rtoId={{$data['id']}}" class="btn btn-success" style="float:right">Next Call</a>
            </div>
          </div>
                <!-- /.box-header -->
            <div class="box-body" id="pending-div">
              <br>
              <div class="row">
                <div class="col-md-12">
                  <label for="customer_details" class="info-color"> Customer Details</label>
                  <div class="col-md-3">
                    <label for="customer_name">Customer Name</label>
                  </div>
                  <div class="col-md-3">
                    <label name="customer_name">{{$data['customer_name']}}</label>
                  </div>
                  <div class="col-md-3">
                    <label for="customer_name">Mobile</label>
                  </div>
                  <div class="col-md-3">
                    <label name="customer_name">{{$data['mobile']}}</label>
                  </div>
                </div>
                <div class="col-md-12">
                  <label for="numberPlateStatus" class="info-color">Number Plate Status</label>
                  <div class="col-md-3">
                    <label for="customer_name">Registration Number</label>
                  </div>
                  <div class="col-md-3">
                    <label name="customer_name">{{(($data['registration_number'])? $data['registration_number'] : '---------' )}}</label>
                  </div>
                  <div class="col-md-3">
                    <label for="customer_name">Received Status</label>
                  </div>
                  <div class="col-md-3">
                    <label name="customer_name">{{(($data['registration_number'])? 'Received' : 'Not Received')}}</label>
                  </div>
                  <div class="col-md-3">
                    <label for="customer_name">Delivery Status</label>
                  </div>
                  <div class="col-md-3">
                    <label name="customer_name">{{(($data['numberPlateStatus'] == 0)? 'Not Delivered' : 'Delivered')}}</label>
                  </div>
                </div>
                <div class="col-md-12">
                  <label for="numberPlateStatus" class="info-color">RC Status</label>
                  <div class="col-md-3">
                    <label for="customer_name">RC Number</label>
                  </div>
                  <div class="col-md-3">
                    <label name="customer_name">{{(($data['rc_number'])? $data['rc_number'] : '------------')}}</label>
                  </div>
                  <div class="col-md-3">
                    <label for="customer_name">Received Status</label>
                  </div>
                  <div class="col-md-3">
                    <label name="customer_name">{{(($data['rc_number'])? 'Received' : 'Not Received')}}</label>
                  </div>
                  <div class="col-md-3">
                    <label for="customer_name">Delivery Status</label>
                  </div>
                  <div class="col-md-3">
                    <label name="customer_name">{{(($data['rcStatus'] == 0)? 'Not Delivered' : 'Delivered')}}</label>
                  </div>
                </div>
              </div>
              <br><hr>
              <form action="/admin/call/Sale/updateCall" method="POST" id="callingForm">
                @csrf
                <input type="hidden" name="call_sale_id" id="call_sale_id" value="{{$data['sale_id']}}">
                <input type="hidden" name="call_rto_id" id="call_rto_id" value="{{$data['id']}}">
                <div class="modal-body">
                    <div class="row">
                      <div class="col-md-6">
                          <label>Call Status<sup>*</sup></label>
                          <input id="call_summary_id" type="hidden" name="call_summary_id" class="input-css">
                          <select id="call_status"  name="call_status" class="form-control select2">
                            <option value="">Select Call Status</option>
                            <option value="Busy">Busy</option>
                            <option value="Not Reachable">Not Reachable</option>
                            <option value="Not Received">Not Received</option>
                          </select>
                          <br>
                      </div>
                      <div class="col-md-6">
                        <label>Next Call Date<sup>*</sup></label>
                        <input id="datepicker" type="text" name="next_call_date" class="input-css ">
                        <br>
                    </div>
                      <div class="col-md-12">
                          <label>Remark <sup>*</sup></label>
                          <textArea id="remark" type="text" name="remark" class="form-control"></textArea>
                      </div>
                    </div>
                </div>
                <div class="col-md-12" style="padding-left:30px;">
                  <button type="submit" class="btn btn-success" >Submit</button>
                </div>
              </form>
              <form action="/admin/call/Sale/updateDelivery" method="POST" id="deliveryForm">
                @csrf
                <input type="hidden" name="delivery_sale_id" id="delivery_sale_id" value="{{$data['sale_id']}}">
                <input type="hidden" name="delivery_rto_id" id="delivery_rto_id" value="{{$data['id']}}">
                <div class="row">
                  <div class="col-md-12">
                    <label for="numberPlateStatus" class="info-color">Number Plate Status</label>
                    <div class="col-md-4">
                      <label for="customer_name">Delivery</label>
                    </div>
                    <div class="col-md-4">
                      <label for=""></label>
                      <div class="col-md-2">
                        <input type="radio" {{(($data['numberPlateStatus'] == 0)? '' : 'checked')}} name="numberPlateDelivery" value="yes"> Yes
                      </div>
                      <div class="col-md-2">
                        <input type="radio"   {{(($data['numberPlateStatus'] == 0)? 'checked' : '')}}  name="numberPlateDelivery" value="no" > No
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
                        <input type="radio" {{(($data['rcStatus'] == 0)? '' : 'checked')}} name="rcDelivery"   value="yes"> Yes
                      </div>
                      <div class="col-md-2">
                        <input type="radio"  {{(($data['rcStatus'] == 0)? 'checked' : '')}}  name="rcDelivery"  value="no" > No
                      </div>
                    </div>
                  </div>
                  <div class="col-md-12" style="padding-left:30px;"><br>
                    <button type="submit" class="btn btn-success" >Update Delivery</button>
                  </div>
                </div>
              </form>
              <br>
              <hr>
              <br>
                    <table id="table-pending" class="table table-bordered table-striped" style="text-align:center">
                        <thead>
                            <tr>
                              <th>Sr.No</th>
                              <th >{{__('last Call Date')}}</th>
                              <th >{{__('Next Call Date')}}</th>
                              <th >{{__('Call Satus')}}</th>
                              <th >{{__('Remark')}}</th>
                              {{-- <th rowspan="2">{{__('Next Call Date')}}</th> --}}
                              {{-- <th rowspan="2">{{__('Action')}}</th> --}}
                            </tr>
                        </thead>
                        <tbody>
                            @if(empty($oldCallData))
                              <tr>
                                <td colspan="5" >Not-Found Data</td>
                              </tr>
                            @else
                              <?php $i = 1;  ?>
                              @foreach($oldCallData as $key => $val)
                              <tr>
                                <td>{{$i}}</td>
                                <td>{{$val->call_date}}</td>
                                <td>{{$val->next_call_date}}</td>
                                <td>{{$val->call_status}}</td>
                                <td>{{$val->remark}}</td>
                                <?php $i++; ?>
                              </tr>
                              @endforeach
                            @endif
                        </tbody>               
                    </table>
                    <!-- /.box-body -->
                    <br>
                    <br>
                <div class="row">
                  <div class="col-md-12" >
                    <a href="/admin/call/Sale/nextcall?saleId={{$data['sale_id']}}&rtoId={{$data['id']}}" class="btn btn-success" style="float:right">Next Call</a>
                  </div>
                </div>
            </div>
           
        </div>
          
    </section>
@endsection