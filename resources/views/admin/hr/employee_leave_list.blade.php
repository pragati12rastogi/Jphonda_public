@extends($layout)

@section('title', __('Employee Leave List'))

@section('breadcrumb')
    <li><a href="#"><i class=""></i>Employee Leave List</a></li>
@endsection
@section('css')
<link rel="stylesheet" href="/css/responsive.bootstrap.css">    
@endsection
@section('js')
<script src="/js/dataTables.responsive.js"></script>
  <script>
  $('#year').datepicker({
      format: "yyyy",
      weekStart: 1,
      orientation: "bottom",
      keyboardNavigation: false,
      viewMode: "years",
      minViewMode: "years",
      autoclose: true
  });
   $('#month').datepicker({
      format: "mm",
      weekStart: 1,
      orientation: "bottom",
      keyboardNavigation: false,
      viewMode: "months",
      minViewMode: "months",
      autoclose: true
  });
    $('#year').on('change', function () {
      datatablefn();
    });
    $('#month').on('change', function () {
      datatablefn(length);
    });

    var selected = [];
    var col_length =  <?php $a_date = date('Y-m-d'); $date = new DateTime($a_date); $date->modify('last day of this month'); 
    echo $last_day = $date->format('d'); ?>;
    var col_data = 'd';

   var dataTable;
    function datatablefn() {
      var str = [];
      str.push({"data":"emp_id"});
      str.push({"data":"name"});
      str.push({"data":"date"});
       for(var i = 1 ; i <= col_length ; i++) {
        str.push({"data": col_data+i,
                  "class":"center light-red",
                  "orderable": false});
      }
      
      str.push( {
                  "targets": [ -1 ],
                  "data":"emp_id", "render": function(data,type,full,meta) {
                    return '';
                  },
                  "orderable": false
              });

    if(dataTable){
      dataTable.destroy();
    }
        
       dataTable = $('#admin_table').DataTable({
        "scrollX": true,
         "processing": true,
         "serverSide": true,
         "ajax": "",
         "aaSorting": [],
         "pageLength": 50,
         "responsive": false,
          "ajax": {
            "url": "/admin/hr/employee/leave/list/api",
            "datatype": "json",
                "data": function (data) {
                    var user_name = $('#user_name').val();
                    data.user_name = user_name;
                    var year = $('#year').val();
                    data.year = year;
                    var month = $('#month').val();
                    data.month = month;
                },

            },
          "drawCallback": function( settings ) {
              for(var i =1 ; i<=col_length ; i++){

              }
          
          },
        
            
          "columns": str
       });
}

   $(document).ready(function() {
    datatablefn();
   // $( "#admin_table" ).parent().css( "overflow-x", "auto" );

  });

   $('#user_name').on( 'change', function () {
      dataTable.draw();
    });

    for (i = new Date().getFullYear(); i > 1900; i--) {
      $('#year').append($('<option />').val(i).html(i));
  }
  </script>
@endsection
@section('main_section')
<section class="content">
            <div id="app">
                    @include('admin.flash-message')
                    @yield('content')
        <!-- Default box -->
        
        <div class="box box-primary">
            <!-- /.box-header -->
            <div class="box-header with-border">
<!--                    <h3 class="box-title">{{__('customer.mytitle')}} </h3>-->
                </div>  
                <div class="box-body">

                <div class="row">
                  <div class="col-md-3"></div>
                    <div class="col-md-3">
                       <label>User Name</label>
                       @if(count($users) > 1)
                          <select name="user_name" class="input-css select2 selectValidation" id="user_name">
                             <option value="">Select Users</option>
                             @foreach($users as $key => $val)
                             <option value="{{$val['id']}}"> {{$val['name']}} {{$val['middle_name']}} {{$val['last_name']}} </option>
                             @endforeach
                          </select>
                       @endif
                       {!! $errors->first('user_name', '
                       <p class="help-block">:message</p>
                       ') !!}
                    </div>
                    <div class="col-md-3" >
                            <label>Select Year</label>
                            <input type="text" name="year" id="year" class="input-css" autocomplete="off">
                    </div>
                    <div class="col-md-3" >
                            <label>Select Month</label>
                            <input type="text" name="month" id="month" class="input-css" autocomplete="off">
                    </div>

                </div>
                  <table id="admin_table" class="table table-bordered table-striped">
                    <thead>
                    <tr>
                      <th>Employee Id</th>
                      <th>Name</th>
                      <!-- <th>Status</th> -->
                      <th>Date</th>
                  <?php 
                  $a_date = date('Y-m-d');
                  $date = new DateTime($a_date);
                  $date->modify('last day of this month');
                  $last_day=$date->format('d');
                  for ($i=1; $i <= $last_day ; $i++) { 
                     echo "<th>".$i."</th>";
                  }

                ?>
                    <th>Action</th>                      
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