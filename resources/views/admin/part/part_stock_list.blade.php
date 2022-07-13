@extends($layout)

@section('title', __('parts.stock_list'))

@section('breadcrumb')
    <li><a href="#"><i class=""></i>Parts Stock Summary</a></li>
@endsection
@section('css')
<link rel="stylesheet" href="/css/responsive.bootstrap.css">  
<style>
   .modal-header .close {
    margin-top: -33px;
}
#status {
    width: 250px;       
}
</style>  
@endsection
@section('js')
<script src="/js/dataTables.responsive.js"></script>
 <script>

    var dataTable;
    $('#js-msg-error').hide();
    $('#js-msg-success').hide();
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
            "url": "/admin/part/stock/list/api",
            "datatype": "json",
                "data": function (data) {
                    var store_name = $('#store_name').val();
                    data.store_name = store_name;
                }
            },
          "columns": [
              { "data": "name" },
              { "data": "part_number" },
              { "data": "quantity" },
              { "data": "min_qty" },
              { "data": "sale_qty" },
              { "data": "price" },
              { "data": "store_name" }

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
  </script>
  @endsection

@section('main_section')
    <section class="content">
            <div id="app">
              <div class="row">
         <div class="alert alert-danger" id="js-msg-error">
         </div>
         <div class="alert alert-success" id="js-msg-success">
         </div>
       </div>
                    @include('admin.flash-message')
                    @yield('content')
        <!-- Default box -->
        
        <div class="box box-primary">
            <!-- /.box-header -->
            <div class="box-header with-border">
<!--                    <h3 class="box-title">{{__('customer.mytitle')}} </h3>-->
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
                      <th>Part Name</th>
                      <th>Part Number</th> 
                      <th>Quantity</th>
                      <th>Min Quantity</th>                                      
                      <th>Sale Quantity</th>                                                
                      <th>Price</th>              
                      <th>Store Name</th>             
                    
                    </tr>
                    </thead>
                    <tbody>
                      
                    </tbody>
               
                  </table>
           
                </div>
                <!-- /.box-body -->
              </div>
      </section>
@endsection