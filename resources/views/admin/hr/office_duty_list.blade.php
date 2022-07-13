@extends($layout)

@section('title', __('Office Duty List'))

@section('breadcrumb')
    <li><a href="#"><i class=""></i>Office Duty List</a></li>
@endsection
@section('css')
<link rel="stylesheet" href="/css/responsive.bootstrap.css">  
<style>
  .wickedpicker {
       z-index: 1064 !important;
   }
</style>  
@endsection
@section('js')
<script src="/js/dataTables.responsive.js"></script>
<script>

    var dataTable;
    $('#js-msg-error').hide();
    $('#js-msg-success').hide();
    function datatablefn() {

    if(dataTable){
      dataTable.destroy();
    }
        
       dataTable = $('#admin_table').DataTable({
         "processing": true,
         "serverSide": true,
         "ajax": "",
         "aaSorting": [],
         "pageLength": 100,
         "responsive": true,
          "rowId":"id",
          "ajax": {
            "url": "/admin/hr/office/duty/list/api",
            "datatype": "json",
                "data": function (data) {
                    var store_name = $('#store_name').val();
                    data.store_name = store_name;
                },

            },
            
          "columns": [
              { "data": "emp_id" },
              { "data": "name" },
              { "data": "type" },
              { "data": "punch" },
              { "data": "punch_date" },
              { "data": "store_name" },
              
             
            ]
       });
}


// Data Tables'additional_services.id',
   $(document).ready(function() {
    datatablefn();
  });

  $('#store_name').on( 'change', function () {
    dataTable.draw();
  });




// $("#anchor").click(function(){
//          geturl();
//     });
//   function geturl(){
//         var month = $('#month').val();
//         var year = $('#year').val();
//         var date = $("#date").val();
//         var emp_id = $(".emp_id").val();
//         var store_id = $(".store_id").val();
//         $("#anchor").attr("href", '/admin/export/data/attendance?month='+month+'&year='+year+'&date='+date+'&emp_id='+emp_id+'&store='+store_id);
   
//     }

  </script>
@endsection
@section('main_section')
<section class="content">
            <div id="app">
                    @include('admin.flash-message')
                    @yield('content')
        <!-- Default box -->
        <div class="row">
         <div class="alert alert-danger" id="js-msg-error">
         </div>
         <div class="alert alert-success" id="js-msg-success">
         </div>
       </div>
        <div class="box box-primary">
            <!-- /.box-header -->
            <div class="box-header with-border">
<!--                    <h3 class="box-title">{{__('customer.mytitle')}} </h3>-->
                </div>  
                <div class="col-md-3" style="{{(count($store) == 1)? 'display:none' : 'display:block'}};float: right;">
                       <label>Store Name</label>
                       @if(count($store) > 1)
                          <select name="store_name" class="store_id input-css select2 selectValidation" id="store_name">
                             <option value="">Select Store</option>
                             @foreach($store as $key => $val)
                             <option value="{{$val['id']}}" {{(old('storename') == $val['id']) ? 'selected' : '' }}> {{$val['name']}} </option>
                             @endforeach
                          </select>
                       @endif
                       @if(count($store) == 1)
                          <select name="storename"  class="store_id input-css select2 selectValidation" id="storename">
                             <option value="{{$store[0]['id']}}" selected>{{$store[0]['name']}}</option>
                          </select>
                       @endif
                       {!! $errors->first('storename', '
                       <p class="help-block">:message</p>
                       ') !!}
                    </div>
                <div class="box-body">

                  <table id="admin_table" class="table table-bordered table-striped">
                    <thead>
                    <tr>
                      <th>Employee Id</th>
                      <th>Name</th>
                      <th>Type</th>
                      <th>Time</th>
                      <th>Date</th>
                      <th>Store Name</th>                    
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