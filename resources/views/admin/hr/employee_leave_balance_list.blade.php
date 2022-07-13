@extends($layout)

@section('title', __('Employee Leave Balance List'))

@section('breadcrumb')
    <li><a href="#"><i class=""></i>Employee Leave Balance List</a></li>
@endsection
@section('css')
<link rel="stylesheet" href="/css/responsive.bootstrap.css">    
@endsection
@section('js')
<script src="/js/dataTables.responsive.js"></script>
  <script>
   
   var dataTable;
    function datatablefn() {

    if(dataTable){
      dataTable.destroy();
    }
        
       dataTable = $('#admin_table').DataTable({
        "scrollX": false,
         "processing": true,
         "serverSide": true,
         "aaSorting": [],
         "pageLength": 10,
         "responsive": false,
          "ajax": {
            "url": "/admin/hr/employee/leave/balance/list/api",
            "datatype": "json",
                "data": function (data) {
                    var user_name = $('#user_name').val();
                    data.user_name = user_name;
                    var year = $('#years').val();
                    data.year = year;
                },

            },            
          "columns": [
              { "data": "emp_id" },
              { "data": "name" },
              { "data": "CL" },
              { "data": "PL" },
              { "data": "financial_year"}
            ]
       });
}

   $(document).ready(function() {
    datatablefn();

  });

   $('#user_name').on( 'change', function () {
      dataTable.draw();
    });
     $('#years').on('change', function () {
      datatablefn();
    });

var mySelect = $('#years');
var currentTime = new Date()
var prevYear = currentTime.getFullYear()+2;
var  startYear = currentTime.getFullYear()+1;
for (var i = 0; i < 30; i++) {
  startYear = startYear - 1;
  prevYear = prevYear - 1;
  mySelect.append(
    $('<option></option>').val(startYear + "-" + prevYear).html(startYear + "-" + prevYear)
  );
}

  </script>
@endsection
@section('main_section')
<section class="content">
      <div id="app">
        @include('admin.flash-message')
        @yield('content')
              <div class="box box-primary">
                <div class="box-header with-border"></div>  
                <div class="box-body">

                  <div class="row">
                      <div class="col-md-3">
                         <label>User Name</label>
                         @if(count($users) > 0)
                            <select name="user_name" class="input-css select2 selectValidation" id="user_name">
                               <option value="">Select Users</option>
                               @foreach($users as $key => $val)
                               <option value="{{$val['id']}}"> {{$val['name']}} {{$val['middle_name']}} {{$val['last_name']}} </option>
                               @endforeach
                            </select>
                         @endif
                      </div>
                      <div class="col-md-3" >
                          <label>Select Year</label>
                          <select id='years' class="select2">
                          </select>
                      </div>
                  </div>
                  <table id="admin_table" class="table table-bordered table-striped">
                    <thead>
                    <tr>
                      <th>Employee Id</th>
                      <th>Name</th>
                      <th>CL</th>
                      <th>PL</th>
                      <th>Financial Year</th>                      
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                  </table>
           
                </div>
                <!-- /.box-body -->
              </div>
      </div>
  <!-- /.box -->
</section>
@endsection