@extends($layout)

@section('title', __('stock.title_view'))

@section('user', Auth::user()->name)

@section('breadcrumb')

    <li><a href="#"><i class=""></i>{{__('stock.title_view')}}</a></li> 
@endsection
@section('css')
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
  
</style>
<link rel="stylesheet" href="/css/responsive.bootstrap.css">    
@endsection
@section('js')
<script src="/js/dataTables.responsive.js"></script>
  <script>
    var dataTable;

    function getdata(){
        
        if(dataTable)
            dataTable.destroy();

        dataTable = $('#table').DataTable({
          "processing": true,
          "serverSide": true,
          "aaSorting":[],
          "responsive": true,
          "ajax": "/admin/stock/movement/list/api",
          "columns": [
                {"data":"fromstore"}, 
                {"data":"tostore"},
                {"data":"quan"},
                {"data":"waybill_no"}, 
                {"data":"loader_truck"}, 
                {"data":"schedule_date"}, 
                {
                  "data":"status",
                  render:function(data,type,full,meta)
                  {
                    if(data == 'pending')
                    {
                      return "Pending";
                    }
                    else if(data == 'running' && full.waybill_no == null)
                    {
                      return "E-waybill Required";
                    }
                    else if(data == 'running' && full.waybill_no != null)
                    {
                      return "E-waybill Updated";
                    }
                    else if(data == 'moved')
                    {
                      return "Sent";
                    }
                  }
                }, 
                {
                    "targets": [ -1 ],
                    "data":"id", "render": function(data,type,full,meta)
                    {
                     // return '<a href="/admin/stock/movement/store/accept/'+data+'"><button class="btn btn-success btn-xs"> Accept </button></a>' ;

                      if(full.type == 'superadmin')
                      {
                        // return '<a href="/admin/stock/movement/del/'+data+'"><button class="btn btn-primary btn-xs"> {{__("stock.stock_del")}} </button></a>'
                        // '<a href="/admin/stock/movement/store/accept/'+data+'"><button class="btn btn-success btn-xs"> Accept </button></a>' 
                        // ;
                        if(full.status == 'pending')  // when salesmanager requested booking
                        {
                          return '<a href="/admin/stock/movement/store/accept/'+data+'"><button class="btn btn-success btn-xs"> Accept </button></a>' ;
                        }
                        else if(full.status == 'running' && full.waybill_no == null) // when e-waybill generated
                        {
                          return '<a href="/admin/stock/movement/accountant/waybill/'+data+'"><button class="btn btn-success btn-xs"> E-waybill Generate </button></a>' ;
                        }
                        else if(full.status == 'running' && full.waybill_no != null) // when e-waybill generated
                        {
                          return '<a href="/admin/stock/movement/store/moved/'+data+'"><button class="btn btn-success btn-xs"> Move </button></a>' ;
                        }
                        else if(full.status == 'moved' && full.waybill_no != null) // when store manager moved loader
                        {
                          return '<a href="#"><button disabled class="btn btn-success btn-xs"> Moved </button></a>' ;
                        }
                      }
                    //   else{
                    //  return '<a href="/admin/stock/movement/store/accept/'+data+'"><button class="btn btn-success btn-xs"> Accept </button></a>' ;

                    //  }
                      else if(full.user_role == 'WarehouseManager' && full.type == 'admin' )
                      {
                        if(full.status == 'pending')  // when salesmanager requested booking
                        {
                          return '<a href="/admin/stock/movement/store/accept/'+data+'"><button class="btn btn-success btn-xs"> Accept </button></a>' ;
                        }
                        else if(full.status == 'running' && full.waybill_no == null) // when e-waybill generated
                        {
                          return '<a href="#"><button disabled class="btn btn-success btn-xs"> E-WayBill Required </button></a>' ;
                        }
                        else if(full.status == 'running' && full.waybill_no != null) // when e-waybill generated
                        {
                          return '<a href="/admin/stock/movement/store/moved/'+data+'"><button class="btn btn-success btn-xs"> Move </button></a>' ;
                        }
                        else if(full.status == 'moved' && full.waybill_no != null) // when store manager moved loader
                        {
                          return '<a href="#"><button disabled class="btn btn-success btn-xs"> Moved </button></a>' ;
                        }
                      }
                      else if(full.user_role == 'Accountant' && full.type == 'admin' )
                      {
                        if(full.status == 'pending' && full.waybill_no == null  || full.loader_truck == null )
                        {
                          return '<a href="#"><button disabled class="btn btn-success btn-xs"> Loader Required </button></a>' ;
                        }
                        else if(full.waybill_no != null)
                        {
                          return '<a href="#"><button disabled class="btn btn-success btn-xs"> E-waybill Generated </button></a>' ;
                        }
                        else if(full.status == 'running' && full.loader_truck != null )
                        {
                          return '<a href="/admin/stock/movement/accountant/waybill/'+data+'"><button class="btn btn-success btn-xs"> E-waybill Generate </button></a>' ;
                        }
                        else if(full.status == 'moved')
                        {
                          return '<a href="#"><button disabled class="btn btn-success btn-xs"> Moved </button></a>' ;
                        }
                      }
                        // "<a href='/admin/stock/movement/edit/"+data+"'><button class='btn btn-success btn-xs'> {{__('stock.stock_edit')}} </button></a> &nbsp;"+
                        //"<a href='/purchase/order/view/"+data+"' target='_blank'><button class='btn btn-primary btn-xs'> {{__('unload.unload_list_view')}} </button></a> &nbsp;" //+ 
                        // return '<a href="/admin/stock/movement/del/'+data+'"><button class="btn btn-primary btn-xs"> {{__("stock.stock_del")}} </button></a>'
                        // '<a href="/admin/stock/movement/store/accept/'+data+'"><button class="btn btn-primary btn-xs">  </button></a>' 
                        // ;
                    },
                    "orderable": false
                }
            ],
            // "columnDefs": [
              
            //     { "orderable": false, "targets": 4 }
            
            // ]
          
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
            <div class="box-body">
            
                    <table id="table" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                {{-- <th>{{__('stock.product_name')}}</th> --}}
                                <th>{{__('stock.from')}}</th>
                                <th>{{__('stock.to')}}</th>
                                <th>{{__('stock.quantity')}}</th>

                                <th>{{__('stock.waybill')}}</th>
                                <th>{{__('stock.loader')}}</th>
                                <th>{{__('stock.schedule')}}</th>
                                {{-- <th>{{__('stock.moved_date')}}</th> --}}
                                <th>{{__('stock.status')}}</th>

                                <th>{{__('stock.action')}}</th> 
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>               
                    </table>
                    <!-- /.box-body -->
            </div>
        </div>
        <!-- /.box -->
    </section>
@endsection