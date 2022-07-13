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
  </style>

@endsection
@section('js')
<script src="/js/dataTables.responsive.js"></script>
<script>
  var dataTable;

    $("#js-msg-error").hide();
    $("#js-msg-success").hide();

    pending();
    function pending()
    {
      $("#done-div").css('display','none');
      $("#pending-div").css('display','block');
      if(dataTable)
      {
        dataTable.destroy();
      }
      dataTable = $('#table-pending').DataTable({
        "processing": true,
         "serverSide": true,
         "ajax": "/admin/call/list/api/pending",
         "aaSorting": [],
         "responsive": true,
         "rowId":"call_summary_id",
         "columns": [
            { "data": "customer_name" },
            { "data": "mobile" },
            { "data": "registration_number" },
            { "data": "numberPlateStatus",
              "render": function (data, type, row) {
                    return ((data == 0)? 'Not Delivered' : 'Delivered' );
                }
              },
              { "data": "rc_number" },
              { "data": "rcStatus",
              "render": function (data, type, row) {
                  return ((data == 0)? 'Not Delivered' : 'Delivered' );
                }
              },
            { "data": "call_date" },
            { "data": "call_status" },
            { "data": "remark" },
             {
                 "targets": [ -1 ],
                 "data":"sale_id", "render": function(data,type,full,meta)
                 {
                   return '<a href="/admin/call/Sale/view?saleId='+data+'&rtoId='+full.id+'&type=pending" target="_blank"><button class="btn btn-success btn-xs"><i class="'+data+'"></i> View</button></a>';
                 },
                 "orderable": false
             }
           ]
         
       });
    }
    function done()
    {
      $("#pending-div").css('display','none');
      $("#done-div").css('display','block');
    
      if(dataTable)
      {
        dataTable.destroy();
      }
      dataTable = $('#table-done').DataTable({
        "processing": true,
         "serverSide": true,
         "ajax": "/admin/call/list/api/done",
         "aaSorting": [],
         "responsive": true,
         "rowId":"id",
         "columns": [
            { "data": "customer_name" },
            { "data": "mobile" },
            { "data": "registration_number" },
            { "data": "numberPlateStatus",
              "render": function (data, type, row) {
                    return ((data == 0)? 'Not Delivered' : 'Delivered' );
                }
            },
            { "data": "rc_number" },
            { "data": "rcStatus",
              "render": function (data, type, row) {
                  return ((data == 0)? 'Not Delivered' : 'Delivered' );
                }
            },
            { "data": "call_date" },
            { "data": "call_status" },
            { "data": "remark" },
            {
                 "targets": [ -1 ],
                 "data":"sale_id", "render": function(data,type,full,meta)
                 {
                   var str = '';
                   if((full.numberPlateStatus != 1 || full.rcStatus != 1) && (full.registration_number != null || full.rc_number != null))
                   {
                      // str = '<a href="#"><button class="btn btn-info btn-xs "><i class="'+data+'"></i> Delivered</button></a>'; 
                      str = '<a href="/admin/call/Sale/view?saleId='+data+'&rtoId='+full.id+'&type=all"><button class="btn btn-success btn-xs "><i class="'+data+'"></i> View</button></a>'; 
                   }
                   return str;
                 },
                 "orderable": false
            }
           ]
         
       });
    }
      
    $('#done').click(function(){
      done();
    });
    $('#pending').click(function(){
      pending();
    });
 
</script>
@endsection

@section('main_section')
    <section class="content">
        <div id="app">
            @include('admin.flash-message')
            @yield('content')
            <div class="row">
            <div class="alert alert-danger" id="js-msg-error">
            </div>
            <div class="alert alert-success" id="js-msg-success">
              Successfully Call Response Updated
            </div>
          </div>
            
        </div>
        
    <!-- Default box -->
        <div class="box">
          <div class="box-header with-border">
            {{-- 
            <h3 class="box-title">{{__('customer.mytitle')}} </h3>
            --}}
            <ul class="nav nav1 nav-pills">
              <li class="nav-item">
                <button class="nav-link1 btn-color-active" id="pending" >Pending Call's</button>
              </li>
              <li class="nav-item">
                <button class="nav-link1 btn-color-unactive" id="done" >All Call's</button>
              </li> 
            </ul>
          </div>
                <!-- /.box-header -->
            <div class="box-body" id="pending-div">
                    <table id="table-pending" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                              <th rowspan="2"> {{__('Customer Name')}}</th>
                              <th rowspan="2">{{__('Mobile')}}</th>
                              <th colspan="2">{{__('Number Plate Status')}}</th>
                              <th colspan="2">{{__('RC Status')}}</th>
                              <th rowspan="2">{{__('last Call Date')}}</th>
                              <th rowspan="2">{{__('Call Satus')}}</th>
                              <th rowspan="2">{{__('Remark')}}</th>
                              {{-- <th rowspan="2">{{__('Next Call Date')}}</th> --}}
                              <th rowspan="2">{{__('Action')}}</th>
                            </tr>
                            <tr>
                              <th>Registration Number</th>
                              <th>Delivery Status</th>
                              <th>RC Number</th>
                              <th>Delivery Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>               
                    </table>
                    <!-- /.box-body -->
            </div>
            <div class="box-body" id="done-div">
              <table id="table-done" class="table table-bordered table-striped">
                <thead>
                  <tr>
                    <th rowspan="2"> {{__('Customer Name')}}</th>
                    <th rowspan="2">{{__('Mobile')}}</th>
                    <th colspan="2">{{__('Number Plate Status')}}</th>
                    <th colspan="2">{{__('RC Status')}}</th>
                    <th rowspan="2">{{__('last Call Date')}}</th>
                    <th rowspan="2">{{__('Call Satus')}}</th>
                    <th rowspan="2">{{__('Remark')}}</th>
                    {{-- <th rowspan="2">{{__('Next Call Date')}}</th> --}}
                    <th rowspan="2">{{__('Action')}}</th>
                  </tr>
                  <tr>
                    <th>Registration Number</th>
                    <th>Delivery Status</th>
                    <th>RC Number</th>
                    <th>Delivery Status</th>
                  </tr>
                </thead>
                  <tbody>
                  </tbody>               
              </table>
              <!-- /.box-body -->
      </div>
        </div>
          <!-- Modal -->
              <div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                  <div class="modal-content" style="margin-top: 200px!important;">
                    <div class="modal-header">
                      <h4 class="modal-title" id="exampleModalLongTitle">Update Call Response</h4>
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                      </button>
                    </div>
                    <div class="modal-body">
                      <span id="msg" style="color:red;">All Field are Required</span>
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
                            <input id="next_call_date" type="date" name="next_call_date" class="input-css">
                            <br>
                        </div>
                          <div class="col-md-12">
                              <label>Remark <sup>*</sup></label>
                              <textArea id="remark" type="text" name="remark" class="form-control"></textArea>
                          </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                      <button type="button" id="btnUpdate" onclick="btnUpdate()" class="btn btn-success">Update</button>
                    </div>
                  </div>
                </div>
              </div>
        <!-- /.box -->
    </section>
@endsection