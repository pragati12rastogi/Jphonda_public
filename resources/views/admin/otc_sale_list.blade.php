@extends($layout)

@section('title', __('OTC Sale List'))

@section('breadcrumb')
    <li><a href="#"><i class=""></i>OTC Sale List</a></li>
@endsection
@section('css')
<link rel="stylesheet" href="/css/responsive.bootstrap.css"> 
@endsection
@section('js')
<script src="/js/dataTables.responsive.js"></script>
  <script>
    var dataTable;
     $("#js-msg-error").hide();
     $("#js-msg-verror").hide();
      $("#js-msg-success").hide();
 
    $(document).ready(function() {
      dataTable = $('#admin_table').DataTable({
          "processing": true,
          "serverSide": true,
          "ajax": "/admin/sale/otcsale/list/api",
          "aaSorting": [],
          "responsive": true,
          "columns": [
              { "data": "sale_no"},
              { "data": "name" },
              { "data": "mobile" },
              { "data": "ew_cost" },
              { "data": "ew_duration" },
              { "data": "amc_cost" },
              { "data": "hjc_cost"},
              { "data": "jphonda_discount",
                "render": function(data,type,full,meta)
                 {
                    if (data == null) {
                      return 0;
                    }else{
                      return data +'%';
                    }
                 },
              },
              { "data": "amount"},
              { "data": "total_amount"},
              { "data": "balance"},
              { "data": "pay_later",
                "render": function(data,type,full,meta)
                 {
                    var pay = ((data == 1)? 'Allowed' : ((data == 0)? 'Not Allowed': ''));
                    return pay;
                 },
             },
              {
                  "targets": [ -1 ],
                  "data":"id", "render": function(data,type,full,meta)
                  {
                    var str = '';
                      str = str+'<a href="/admin/sale/otcsale/view/'+data+'"><button class="btn btn-success btn-xs">View</button></a> '; 

                      // str = str+' <a href="/admin/sale/additional/services/pay/'+data+'"><button class="btn btn-primary btn-xs">Pay</button></a> ';

                      str = str+' <a href="/admin/sale/otcsale/pay/details/'+data+'"><button class="btn btn-primary btn-xs">Payment Details</button></a>';
                      if (full.pay_later == 0 || full.jphonda_discount == 0) {
                      str = str+' <a href="#"><button class="btn btn-info btn-xs "  onclick="AddDiscount(this)"><i class="'+data+'"></i>Pay Later / Discount</button></a>';
                      }
                      return str;
                  },
                  "orderable": false
              }
            ],
            "columnDefs": [
            ]
          
        });
    });

      function AddDiscount(el) {
        var id = $(el).children('i').attr('class');
        $("input[name='pay_discount']").removeAttr('checked');
        if(id){
          $("#loader").show();
          $.ajax({  
                  url:"/admin/sale/otcsale/get/discount/"+id,  
                  method:"get",  
                  success:function(data){
                  if(data.hasOwnProperty('id')) {
                      $('.PayByJPHonda').attr('checked',true);
                    }   
                      $("#discount").val(data.amount);
                      $("#otc_sale_id").val(id);
                      $('#DiscountModel').modal("show"); 
                  },error:function(error){
                    $("#loader").hide();
                    alert(error);
                  }
            }).done(function(){
              $("#loader").hide();
            });
        }
      }



  
    $('#DiscountBtnUpdate').on('click', function() {
      $("#loader").show();
      var otc_sale_id = $('#otc_sale_id').val();
      var pay_discount =  $('input[name="pay_discount"]:checked').val();
       $.ajax({
        method: "GET",
        url: "/admin/sale/otcsale/jphonda/discount",
        data: {'otc_sale_id':otc_sale_id,'pay_discount':pay_discount},
        success:function(data) {
            if (data.type == 'success') {
                $('#DiscountModel').modal("hide");  
                 $('#admin_table').dataTable().api().ajax.reload();
                $("#js-msg-success").html(data.msg);
                $("#js-msg-success").show();
               setTimeout(function() {
                 $('#js-msg-success').fadeOut('fast');
                }, 4000);
            }if(data.type == 'error'){  
                $('#DiscountModel').modal("hide");  
                $("#js-msg-error").html(data.msg);
                $("#js-msg-error").show();
               setTimeout(function() {
                 $('#js-msg-error').fadeOut('fast');
                }, 4000);
            }
        },
        error:function(error){
          $("#loader").hide();
          alert(error);
        }
      }).done(function(){
        $("#loader").hide();
      });
    });

    $('#btnCancel').on('click', function() {
      $('#my-form')[0].reset(); 
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
                      <th>Sale Number</th>
                      <th>Name</th>
                      <th>Mobile</th>
                      <th>EW Cost</th>
                      <th>EW Duration</th>
                      <th>AMC Cost</th>
                      <th>HJC Cost</th>
                      <th>JpHonda Discount</th>
                      <th>Paid Amount</th>
                      <th>Total Amount</th>
                      <th>Balance</th>
                      <th>Pay Later</th>
                      <th>{{__('factory.action')}}</th>                      
                    </tr>
                    </thead>
                    <tbody>
                      
                    </tbody>
               
                  </table>
           
                </div>
                <!-- /.box-body -->
              </div>
              <!---Discount Model --->
    <div class="modal fade" id="DiscountModel" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="margin-top: 200px!important;">
          <div class="modal-header">
            <h4 class="modal-title" id="exampleModalLongTitle">Add JpHonda Discount</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">                   
            <form id="discount-form"  method="POST" >
              @csrf
              <div class="alert alert-danger" id="js-msg-verror"></div>
              <input type="hidden" name="otc_sale_id" id="otc_sale_id">
                 <div class="row">
                  <div class="col-md-6">
                    <label>Pay By JpHonda </label>
                    <input type="radio" name="pay_discount" value="PayByJpHonda" class="pay_discount PayByJpHonda">
                  </div>
                  <div class="col-md-6">
                    <label>Pay Later </label>
                    <input type="radio" name="pay_discount" value="1" class="pay_discount payLater">
                  </div>
                </div>
          </div>
          <div class="modal-footer">
            <button type="button" id="Cancel" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="button" id="DiscountBtnUpdate" class="btn btn-success">Add</button>
          </div>
          </form>
        </div>
      </div>
    </div>
      </section>
@endsection