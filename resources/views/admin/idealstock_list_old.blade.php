@extends($layout)

@section('title', __('factory.ideal_stock'))

@section('breadcrumb')
    <li><a href="#"><i class=""></i>Ideal Stock Summary</a></li>
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

    function updateMinQty(el){
      //console.log('h');
      var id = $(el).children('i').attr('class');
      $.ajax({  
                url:"/admin/stock/stock_min_qty/"+id,  
                method:"get",  
                success:function(data){ 
                 if(data) {
                        $("#stock_min_qty").empty();
                        $.each(data,function(key,value){
                          $("#stock_id").val(data['id']);
                          $("#stock_min_qty").val(data['min_qty']);
                          $("#store_id").val(data['product_id']);
                          $("#product_id").val(data['store_id']);
                        }); 
                        $('#stockModalCenter').modal("show"); 
                    }
                      
                }  
           });
    }

    $('#btnUpdate').on('click', function() {
      var id = $('#stock_id').val();
      var pid = $('#product_id').val();
      var sid = $('#store_id').val();
      var minQty = $('#stock_min_qty').val();
       $.ajax({
        method: "GET",
        url: "/admin/stock/update_minqty",
        data: {'id':id,'sid':sid,'pid':pid,'stock_min_qty':minQty},
        success:function(data) {
            $("#"+id).children('td.min_qty').html(minQty);
            $('#stockModalCenter').modal("hide");  
            $('#admin_table').dataTable( ).api().ajax.reload();
        },
        error:function(data){
          alert('try again');
        }
      });

    });

    // Data Tables
    $(document).ready(function() {
      dataTable = $('#admin_table').DataTable({
          "processing": true,
          "serverSide": true,
          "ajax": "/admin/stock/idealstock_list/old/api",
          "aaSorting": [],
          "responsive": true,
          'createdRow':function(row,data,dataIndex)
          {
              $(row).addClass('rowClass');
              if(data.quantity == data.min_qty)
              {
                $(row).addClass('worningClass');
              }
              else if(data.quantity > data.min_qty)
              {
                $(row).addClass('successClass');
              }
              else if(data.quantity < data.min_qty)
              {
                $(row).addClass('dangerClass');
              }
          },
          "rowId":"id",
          "columns": [
              { "data": "id" },
              { "data": "name"},
              { "data": "model_category" },
              { "data": "model_variant" },
              { "data": "model_name" },
              { "data": "quantity" },
              { "data": "damage_quantity" },
              { "data": "min_qty","className":"min_qty" },
              {
                  "targets": [ -1 ],
                  "data":"id", "render": function(data,type,full,meta)
                  {
                    
                      return '<a href="#"><button class="btn btn-info btn-xs "  onclick="updateMinQty(this)"><i class="'+data+'"></i> Update Min Qty</button></a> &nbsp;'  ;
                  },
                  "orderable": false
              }
            ],
            "columnDefs": [
            ]
          
        });
    });
  </script>
@endsection

@section('main_section')

    <section class="content">

        <div id="app">
                   
        <div class="box box-primary">
            <!-- /.box-header -->
            <div class="box-header with-border">
<!--                    <h3 class="box-title">{{__('customer.mytitle')}} </h3>-->
                </div>  
                <div class="box-body">
                   @include('admin.flash-message')
                    @yield('content')
                  <table id="admin_table" class="table table-bordered table-striped">
                    <thead>
                    <tr>
                      <th>id</th>
                      <th>Store Name</th>
                      <th>Model Category</th>
                      <th>Model Variant</th>
                      <th>Model Name</th>
                      <th>Quantity</th>
                      <th>Damage Quantity</th>
                      <th>Min Quantity</th>
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
              <div class="modal fade" id="stockModalCenter" tabindex="-1" role="dialog" aria-labelledby="stockModalCenterTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                  <div class="modal-content" style="margin-top: 200px!important;">
                    <div class="modal-header">
                      <h4 class="modal-title" id="stockModalLongTitle">Update Min Quantity</h4>
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                      </button>
                    </div>
                    <div class="modal-body">
                      <form id="my-form"  method="POST">
                        @csrf
                        <div class="row">
                          <div class="col-md-6">
                              <label>Quantity <sup>*</sup></label>
                              <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
                              <input id="stock_id" type="hidden" name="id" class="input-css">
                              <input id="store_id" type="hidden" name="sid" class="input-css">
                              <input id="product_id" type="hidden" name="pid" class="input-css">
                              <input id="stock_min_qty" type="number" name="stock_min_qty" class="form-control">
                              <br>
                          </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                      <button type="button" id="btnUpdate" class="btn btn-success">Update</button>
                    </div>
                    </form>
                  </div>
                </div>
              </div>
        <!-- /.box -->
      </section>
@endsection