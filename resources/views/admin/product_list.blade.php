@extends($layout)

@section('title', __('product.title_view'))

@section('breadcrumb')
    <li><a href="#"><i class=""></i>{{__('product.title_view')}}</a></li>
@endsection
@section('css')
<link rel="stylesheet" href="/css/responsive.bootstrap.css"> 
<style type="text/css">
   .modal-header .close {
    margin-top: -33px;
}
</style>   
@endsection
@section('js')
<script src="/js/dataTables.responsive.js"></script>
  <script>
    var dataTable;
     $('#js-msg-error').hide();
     $('#js-msg-errors').hide();
     $('#js-msg-success').hide();
     function updateBasicPrice(el){
      //console.log('h');
      var id = $(el).children('i').attr('class');
      $.ajax({  
                url:"/admin/product/get_basic_price/"+id,  
                method:"get",  
                success:function(data){ 
               // console.log(data.id); 
                 if(data) {
                        $("#basic_price").empty();
                        $.each(data,function(key,value){
                          $("#product_id").val(key);
                           $("#basic_price").val(value);
                        }); 
                        $('#productModalCenter').modal("show"); 
                    }
                }  
           });
    }

 function duration(el){
      //console.log('h');
      var id = $(el).children('i').attr('class');
      $.ajax({  
                url:"/admin/product/get_duration/"+id,  
                method:"get",  
                success:function(data){ 
               // console.log(data.id); 
                 if(data) {
                        $("#st_warranty_duration").empty();
                        $.each(data,function(key,value){
                          $("#product_id").val(key);
                           $("#st_warranty_duration").val(value);
                        }); 
                        $('#productModalDuration').modal("show"); 
                        $('#js-msg-errors').html("").hide();
                    }
                }  
           });
    }

    $('#btnUpdate').on('click', function() {
      var pid = $('#product_id').val();
      var bprice = $('#basic_price').val();
       $.ajax({
        method: "GET",
        url: "/admin/product/update_basic_price/",
        data: {'product_id':pid,'basic_price':bprice},
        success:function(data) {
            $("#"+pid).children('td.basic_price').html(bprice);
            $('#productModalCenter').modal("hide");  
        },
        error:function(data){
          alert('try again');
        }
      });

    });

    $('#btnDuration').on('click', function() {

      var pid = $('#product_id').val();
      var duration = $('#st_warranty_duration').val();

      if(duration<=0)
      {
        $('#js-msg-errors').html("The st warranty duration value grather then 0").show();
      }
      else
      {
        $.ajax({
        method: "GET",
        url: "/admin/product/update_duration/",
        data: {'product_id':pid,'st_warranty_duration':duration},
        success:function(data) {
            $("#"+pid).children('td.st_warranty_duration').html(duration);
            $('#productModalDuration').modal("hide");  
            $('#js-msg-success').html('Successfully Updated Standard Warranty Duration.').show();
            $('#js-msg-errors').html("").hide();
        },
        error:function(data){
          // alert('try again');
          $('#js-msg-error').html("Something Wen't Wrong").show();
          $('#js-msg-errors').html("").hide();
        }
      });
      }
       

    });
    // Data Tables
    $(document).ready(function() {
      dataTable = $('#admin_table').DataTable({
          "processing": true,
          "serverSide": true,
          "ajax": "/admin/product/product_list/api",
          "aaSorting": [],
          "responsive": true,
          "rowId":"id",
          "columns": [
                {"data":"id"}, 
                {"data":"model_cat"}, 
                {"data":"model_name"},
                {"data":"model_var"}, 
                {"data":"color"}, 
                {"data":"basic_price","className":"basic_price"}, 
                {"data":"st_warranty_duration","className":"st_warranty_duration"}, 
              {
                  "targets": [ -1 ],
                  "data":"id", "render": function(data,type,full,meta)
                  {
                    
                      return "<a href='#'><button  class='btn btn-info btn-xs'  onclick='updateBasicPrice(this)'><i class="+data+"><i class='fa fa-pencil'></i>Update Basic Price</button></a> &nbsp<a href='#'><button  class='btn btn-success btn-xs'  onclick='duration(this)'><i class="+data+">Duration</button></a> &nbsp";
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
                <div id="refresh" class="box-body">

                  <table id="admin_table" class="table table-bordered table-striped">
                    <thead>
                    <tr>
                      <th>{{__('product.id')}}</th>
                      <th>{{__('product.model_cat')}}</th>
                      <th>{{__('product.model_name')}}</th>
                      <th>{{__('product.model_var')}}</th>
                      <th>{{__('product.color_code')}}</th>
                      <th>{{__('product.basic_price')}}</th>
                      <th>{{__('product.st_warranty_duration')}}</th>
                      <th>{{__('product.action')}}</th>                      
                    </tr>
                    </thead>
                    <tbody>
                      
                    </tbody>
               
                  </table>
           
                </div>
                <!-- /.box-body -->
              </div>
        <!-- /.box -->
        <!-- Modal -->
        <div class="modal fade" id="productModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content" style="margin-top: 200px!important;">
              <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">Update Basic Price</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <form id="my-form1" method="POST">
                  @csrf
                  <div class="row">
                    <div class="col-md-6">
                        <label>Basic Price <sup>*</sup></label>
                        <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
                        <input id="product_id" type="hidden" name="id" class="input-css">
                        <input id="basic_price" type="text" name="basic_price" class="input-css">
                        <br>
                    </div>
                  </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" id="btnUpdate" class="btn btn-primary">Update</button>
              </form>
              </div>
            </div>
          </div>
        </div>
         <!-- Modal -->
        <div class="modal fade" id="productModalDuration" tabindex="-1" role="dialog" aria-labelledby="exampleModalDurationTitle" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content" style="margin-top: 200px!important;">
              <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">Update Duration</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
                <div class="alert alert-danger" id="js-msg-errors">
            </div>
              </div>
              <div class="modal-body">
                <form id="my-form2" method="POST">
                  @csrf
                  <div class="row">
                    <div class="col-md-6">
                        <label> Standard Warranty Duration <sup>*</sup></label>
                        <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
                        <input id="product_id" type="hidden" name="id" class="input-css">
                        <input id="st_warranty_duration" type="number" min="0" name=" st_warranty_duration" class="input-css">
                        <br>
                    </div>
                  </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" id="btnDuration" class="btn btn-primary">Update</button>
              </form>
              </div>
            </div>
          </div>
        </div>
      </section>
@endsection