@extends($layout)

@section('title', __('stocklist.title'))

@section('user', Auth::user()->name)

@section('breadcrumb')

    <li><a href="#"><i class=""></i>{{__('stocklist.title')}}</a></li> 
@endsection
@section('css')
<style>
    td.center {
      text-align: center;
    }
    span .red
    {
      color: red;
    }
    th,td{
    text-align:center;
    }
    td.green{
      background-color: #087b08;
      border-left-color: black; 
      border-left-width: thin; 
      color: white;
    }
    td.red{
      background-color: #f71b10fa;
      color: white;

    }
    td.yellow{
      background-color: #fd9a07fc;
      border-right-color: black;  
      border-right-width: thin;  
      color: white;


    }
    td.light-info{
      background-color: #3498db;
      color: white;

    }
    td.light-red{
      background-color: #FA8072;
      color: white;

    }

    th, td { white-space: nowrap; }
    div.dataTables_wrapper {
        /* width: 800px; */
        margin: 0 auto;
    }
    
</style>
<link rel="stylesheet" href="/css/responsive.bootstrap.css">    
@endsection
@section('js')
<meta name="csrf-token" content="{{ csrf_token() }}" />
<script src="/js/dataTables.responsive.js"></script>
  <script>
    var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
    var dataTable;
    var col_data = @php echo json_encode($store); @endphp;
    var col_length = col_data.length;
    function getdata(){
      
      var str = [];
      str.push({"data":"product_name"});
      str.push({"data":"color_code"});
      for(var i = 0 ; i < col_length ; i++)
      {
        var store_name = col_data[i]['name'];
        var damage = col_data[i]['damage'];
        var ant = col_data[i]['alloc'];
        var sale = col_data[i]['sale'];
        var booking = col_data[i]['booking'];
        str.push({"data": store_name,
                  "createdCell": function(td, cellData, rowData, row, col){
                    var str = cellData+'-avail';
                    $(td).html(rowData[str]);
                    if(rowData.hasOwnProperty(cellData+'-avail_title')){
                      $(td).attr('title',rowData[cellData+'-avail_title']);
                    }
                  },
                  "class":"center green ",
                  "orderable": false});
        str.push({"data": damage,
                  "class":"center red",
                  "orderable": false});
        str.push({"data": ant,
                  "class":"center yellow",
                  "orderable": false});
        str.push({"data": sale,
                  "class":"center light-info",
                  "orderable": false});
        str.push({"data": booking,
                  "class":"center light-red",
                  "orderable": false});
      }
      
        if(dataTable)
            dataTable.destroy();
       
        dataTable = $('#table').DataTable({
          "scrollX": true,
          "scrollY": "500px",
          "processing": true,
          "serverSide": true,
          "scrollCollapse": true,
          fixedHeader: true,
          fixedColumns: {
            leftColumns: 2
          },
          "aaSorting":[],
          "responsive": false,
          "ajax": {
           "url":"/admin/stock/list/api",
          "type": "POST",
          "data": {'_token': CSRF_TOKEN},
          },
          "columns": str
            
          
        });
        
    }
   


    $(document).ready(function() {
      
        getdata();
        
    });

    

   

   


  </script>
@endsection

@section('main_section')
    <section class="content">
        <div id="app">
            @include('admin.flash-message')
            @yield('content')
            {{-- @section('titlebutton')
                <a href="{{url('/createdispatch')}}"><button class="btn btn-primary">{{__('goods_dispatch.title')}}</button></a>
                <a href="" ><button class="btn btn-primary "  >{{__('goods_dispatch.goods_dispatch_import_btn')}}</button></a>
                <a href="" ><button class="btn btn-primary "  >{{__('goods_dispatch.goods_dispatch_export_btn')}}</button></a>
            @endsection --}}
            
        </div>
    <!-- Default box -->
        <div class="box">
                <!-- /.box-header -->
            <div class="box-body main-table">
            
                <table id="table" class="table table-bordered table-striped cell-border">
                        <thead>
                            <tr>
                                <th rowspan="2">Product Name</th>
                                <th rowspan="2">Color Code</th>
                                {{-- <th>Available Quantity</th> --}}
                                @foreach($store as $store1)
                                  <th colspan="5">{{$store1['name']}}</th>
                                @endforeach
                                {{-- <th>Required Quantity</th> --}}
                            </tr>
                            <tr>
                                @for($i = 0 ; $i < count($store) ; $i++)
                                  <th class='green' style="border-left-color: black; 
                                      border-left-width: thin;">Stock Qty</th>
                                  <th class='red'>Damage Qty</th>
                                  <th class='yellow' >ANT Qty</th>
                                  <th class='light-info' >Sale Qty</th>
                                  <th class='light-red' style="border-right-color: black; 
                                      border-right-width: thin;">Booking Qty</th>
                                @endfor
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


