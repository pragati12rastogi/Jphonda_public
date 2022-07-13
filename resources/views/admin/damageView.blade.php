@extends($layout)

@section('title', __('damage_claim.titleView'))

@section('user', Auth::user()->name)

@section('breadcrumb')

    <li><a href="#"><i class=""></i>{{__('damage_claim.titleView')}}</a></li> 
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
button.active{
  background-color: #87CEFA;
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

    function getActive(el){

        $('#active').show();
        $('#settle').hide();
        $('.tab-settle').removeAttr('style');
        $('.tab-active').css("background-color","#87CEFA");

      if(dataTable)
      {
        dataTable.destroy();
      }
        dataTable = $('#active_table').DataTable({
          "processing": true,
          "serverSide": true,
          "aaSorting":[],
          "responsive": true,
          'createdRow':function(row,data,dataIndex)
          {
              $(row).addClass('del'+data.id);
          },
          "ajax": "/admin/damage/claim/list/api/active",
          "columns": [
                {"data":"load_referenace_number"},
                {"data":"name"},
                {"data":"claim_amount"}, 
                {"data":"settlement"}, 
                {"data":"received_amount"},
                {"data":"received_date"},
                {"data":"mail_sent"}, 
                {"data":"est_sent"}, 
                {"data":"claim_form_uploaded"}, 
                {"data":"hirise_bill"},  
                {
                    "targets": [ -1 ],
                    'orderable': false,
                    'className': "dt-center",
                    "data":"id", "render": function(data,type,full,meta)
                    {
                      var str = '';
                      if(full.no_of_claim == full.damaged_vehicle){
                        str = str+"<a href='/admin/damage/claim/update/"+data+"'><button class='btn btn-success btn-xs'> {{__('damage_claim.damage_claim_list_Edit')}}  </button></a> &nbsp;";
                      }
                      str = str+"<a href='/admin/damage/claim/view/"+data+"' target='_blank'><button class='btn btn-warning btn-xs'> {{__('unload.unload_list_view')}} </button></a> &nbsp"
                        //'<a href="#" id="del" onclick = "del(this,'+data+')"><button class="btn btn-primary btn-xs"> {{__("damage_claim.damage_claim_list_del")}} </button></a>' 
                        ;
                        return str;
                    },
                }
            ],
            // "columnDefs": [
              
            //     { "orderable": false, "targets": 10 }
            
            // ]
          
        });
    }
    if(dataTable){
        dataTable.destroy;
    }
    else{
        dataTable = $('#active_table').DataTable();
      function del(e,id)
      {
        var con=confirm("Are you sure you want to delete that ");
        if(con){
          $.ajax({ 
              url: '/admin/damage/claim/del',
              data: {"claim_id":id},
              type: 'GET',
              success: function(data)
              { 
                if(data == 'false')
                {
                  alert('Data Deleted Unsccessfully');
                }
                else{
                  alert('Data Deleted Successfully');
                  dataTable.draw();
                }
              },
              failure: function (msg) {
                    console.log(msg);
              }
          });
        }
        
      }
    }
   
    function getSettled(el){

      $('#active').hide();
        $('#settle').show();
        $('.tab-active').removeAttr('style');
        $('.tab-settle').css("background-color","#87CEFA");

        if(dataTable)
        {
          dataTable.destroy();
        }

        dataTable = $('#settle_table').DataTable({
          "processing": true,
          "serverSide": true,
          "aaSorting":[],
          "responsive": true,
          "ajax": "/admin/damage/claim/list/api/settle",
          "columns": [
                {"data":"load_referenace_number"},
                {"data":"name"},
                {"data":"claim_amount"}, 
                {"data":"settlement"}, 
                {"data":"received_amount"},
                {"data":"received_date"},
                {"data":"mail_sent"}, 
                {"data":"est_sent"}, 
                {"data":"claim_form_uploaded"}, 
                {"data":"hirise_bill"},  
                {
                    "targets": [ -1 ],
                    "data":"id", "render": function(data,type,full,meta)
                    {
                         //"<a href='#'><button class='btn btn-success btn-xs'> {{__('damage_claim.damage_claim_list_Edit')}} </button></a> &nbsp;"+
                         return "<a href='/admin/damage/claim/view/"+data+"' target='_blank'><button class='btn btn-warning btn-xs'> {{__('unload.unload_list_view')}} </button></a> &nbsp"+ 
                        '<a href="#"><button class="btn btn-primary btn-xs"> {{__("damage_claim.damage_claim_list_del")}} </button></a>' 
                        ;
                    },
                }
            ],
            "columnDefs": [
              
                { "orderable": false, "targets": 10 }
            
            ]
          
        });
    }

    $(document).ready(function() {
        getActive();
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
              <ul class="nav nav1 nav-pills">
                <li class="nav-item">
                  <button class="nav-link1 active tab-active"  onclick="getActive()">{{__('Active')}}</button>
                </li>
                <li class="nav-item">
                  <button class="nav-link1 settle tab-settle" onclick="getSettled()">{{__('Settled')}}</button>
                </li>
              </ul><br><br>
                  <div id="active" >
                    <table id="active_table" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Load Reference</th>
                                <th>Store</th>
                                <th>Claim Amount</th>
                                <th>Settlement</th>
                                <th>Received Amount</th>
                                <th>Received Date</th>
                                <th>Mail Sent</th>
                                <th>Estimate Sent</th>
                                <th>Claim Form Uploaded</th>
                                <th>High Rise Bill</th>

                                <th>Action</th> 
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>               
                    </table>
                  </div>
                  <div id="settle" >
                    <table id="settle_table" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Load Reference</th>
                                <th>Store</th>
                                <th>Claim Amount</th>
                                <th>Settlement</th>
                                <th>Received Amount</th>
                                <th>Received Date</th>
                                <th>Mail Sent</th>
                                <th>Estimate Sent</th>
                                <th>Claim Form Uploaded</th>
                                <th>High Rise Bill</th>

                                <th>Action</th> 
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