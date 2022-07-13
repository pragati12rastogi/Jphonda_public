@extends($layout)
@section('title', __('Handling Charge List'))
@section('breadcrumb')
<li><a href="#"><i class=""></i></a></li>
@endsection
@section('css')
@endsection
@section('js')

<script>
 
   var dataTable; 
   
  function datatablefn()
   {

    if(dataTable){
      dataTable.destroy();
    }
       
     dataTable = $('#table').DataTable({
         "processing": true,
         "serverSide": true,
         "aaSorting": [],
         "responsive": true,
         "ajax": {
            "url": "/admin/sale/handling/charge/list/api",
            "datatype": "json",
                "data": function (data) {
                    var handling_type = $('#handling_type').val();
                    data.handling_type = handling_type;
                    var agent_name = $('#agent_name').val();
                    data.agent_name = agent_name;
                }
            },
         "columns": [
             { "data": "product_frame_number"},
             { "data": "agent_name"},
             { "data": "rto_type"},
             { "data": "rto_amount"},
             { "data": "rto"},
             { "data": "handling_charge"},
             { "data": "affidavit_cost"}
           ]
       });
   };

    $(document).ready(function() {
    datatablefn();
  });

    $('#handling_type').on( 'change', function () {
      dataTable.draw();
    });
    $('#agent_name').on( 'change', function () {
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
      </div>
      
      <div class="box-body">
         @include('admin.flash-message')
         @yield('content')
         <div class="row">
          <div class="col-md-6"></div>
           <div class="col-md-3">
               <label>Agent Name</label>
                  <select name="agent_name" class="input-css select2 selectValidation" id="agent_name">
                     <option value="">Select Agent Name</option>
                     @foreach($agent_name as $age)
                      <option value="{{$age->agent_name}}">{{$age->agent_name}}</option>
                      @endforeach
                  </select>
            </div>
            <div class="col-md-3">
               <label>Handing Type</label>
                  <select name="type" class="input-css select2 selectValidation" id="handling_type">
                     <option value="">Select Handling Type</option>
                     @foreach($handling_type as $type)
                      <option value="{{$type->rto}}">{{$type->rto}}</option>
                      @endforeach
                  </select>
            </div>
        </div>
         <table id="table" class="table table-bordered table-striped">
            <thead>
               <tr>
                  <th>Frame</th>
                  <th>Agent Name</th>
                  <th>RTO Type </th>
                  <th>RTO Fees </th>
                  <th>Handling Type</th>
                  <th>Handling Fees</th>
                  <th>Affidavit</th>
               </tr>
            </thead>
            <tbody>
            </tbody>
         </table>
      </div>
      <!-- /.box-body -->
   </div>
   <!-- /.box -->
</section>
@endsection