@extends($layout)
@section('title', __('Job Card Entry List'))
@section('breadcrumb')
<li><a href="#"><i class=""></i></a></li>
@endsection
@section('css')
<link rel="stylesheet" href="/css/responsive.bootstrap.css">
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
 var dataTable;
   
   function datatablefn()
   {

    if(dataTable){
      dataTable.destroy();
    }
        
       dataTable = $('#admin_table').DataTable({
         "processing": true,
         "serverSide": true,
         "ajax": "",
         "aaSorting": [],
         "responsive": true,
          "ajax": {
            "url": "/admin/service/jobcard/list/api",
            "datatype": "json",
                "data": function (data) {
                    var store_name = $('#store_name').val();
                    data.store_name = store_name;
                }
            },
         "columns": [
             { "data": "tag"},
             { "data": "frame"},
             { "data": "registration"},
             { "data": "in_time"},
             { "data": "out_time"},
             { "data": "job_card_type"},
             { "data": "customer_status"},
             { "data": "vehicle_km"},
             { "data": "service_duration"},
             { "data": "estimated_delivery_time"},
             {
                 "targets": [ -1 ],
                 "data":"id", "render": function(data,type,full,meta)
                 {
                  var str = '';
                  if ((full.role == 'Superadmin' || full.role == 'Receptionist') && (full.service_status == 'pending')) {
                    str += '<a href="/admin/service/update/frame/'+data+'"><button class="btn btn-primary btn-xs">Update Details</button></a> &nbsp;';
                  }
                  if (full.customer_status != null) {
                     str += '<a href="/admin/service/view/jobcard/'+data+'"><button class="btn btn-warning btn-xs">View</button></a> &nbsp;';
                   }

                  if (full.jobcard_create == 0 &&  full.service_status == 'pending' && full.sale_date != null && full.registration != null && full.customer_id != 0 && full.selling_dealer_id != null && full.product_id != null && full.frame != null && full.manufacturing_year != null ) {
                     str += '<a href="/admin/service/create/jobcard/'+data+'"><button class="btn btn-info btn-xs">Create Job Card</button></a> &nbsp;';
                   }
                   // if (full.service_status == 'pending' && full.customer_status != null) {
                   //   str += '<a href="/admin/service/create/jobcard/'+data+'"><button class="btn btn-info btn-xs">update Job Card</button></a> &nbsp;';
                   // }
                     return str;
                 },
                 "orderable": false
             }
           ]
       });
}

   $(document).ready(function() {
    datatablefn();
  });
   $('#store_name').on( 'change', function () {
      dataTable.draw();
    });
</script>
@endsection
@section('main_section')
<section class="content">
   <div id="app">
   <div class="box box-primary">
      <!-- /.box-header -->
      <div class="box-header with-border">
                        {{-- <h3 class="box-title">{{__('customer.mytitle')}} </h3> --}}

      </div>
      <div class="box-body">
         @include('admin.flash-message')
         @yield('content')
         <div class="row">
              <div class="col-md-3" style="{{(count($store) == 1)? 'display:none' : 'display:block'}};float: right;">
                 <label>Store Name</label>
                 @if(count($store) > 1)
                    <select name="store_name" class="input-css select2 selectValidation" id="store_name">
                       <option value="">Select Store</option>
                       @foreach($store as $key => $val)
                       <option value="{{$val['id']}}" {{(old('store_name') == $val['id']) ? 'selected' : '' }}> {{$val['name']}} </option>
                       @endforeach
                    </select>
                 @endif
                 @if(count($store) == 1)
                    <select name="store_name" class="input-css select2 selectValidation" id="store_name">
                       <option value="{{$store[0]['id']}}" selected>{{$store[0]['name']}}</option>
                    </select>
                 @endif
                 {!! $errors->first('store_name', '
                 <p class="help-block">:message</p>
                 ') !!}
              </div>
          </div>
         <table id="admin_table" class="table table-bordered table-striped">
            <thead>
               <tr>
                  <th>Tag #</th>
                  <th>Frame #</th>
                  <th>Registration #</th>
                  <th>Time In</th>
                  <th>Time Out</th>
                  <th>Job Card Type</th>
                  <th>Customer Status</th>
                  <th>Vehicle K.M.</th>
                  <th>Estimate Hour</th>
                  <th>Estimate Delivery</th>
                  <th>{{__('factory.action')}}</th>
               </tr>
            </thead>
            <tbody>
            </tbody>
         </table>
      </div>
      <!-- /.box-body -->
   </div>
   <!-- Modal -->
   <!-- /.box -->
</section>
@endsection