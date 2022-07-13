@extends($layout)

@section('title', __('Finance Company'))

@section('breadcrumb')
    <li><a href="#"><i class=""></i>Finance Company listing</a></li>
@endsection
@section('css')
<link rel="stylesheet" href="/css/responsive.bootstrap.css">    
@endsection
@section('js')
<script src="/js/dataTables.responsive.js"></script>
  <script>
     $('#js-msg-error').hide();
     $('#js-msg-success').hide();

    function GetFinanceCompany(el) {
      var id = $(el).children('i').attr('class');

      $.ajax({  
                url:"/admin/finance/company/update/getdata/"+id,  
                method:"get",  
                success:function(data){
                 if(data) {
                  $("#js-msg-error").html('');
                  $('#js-msg-error').hide();
                  $("#id").val(data.id);
                  $("#payout").val(data.payout);
                  $('#trade_type').select2().val(data.trade_type).trigger('change');
                  $("#amount").val(data.amount);
                  $('#financeModalCenter').modal("show"); 
                  }    
                }  
           });
    }


    $('#btnUpdate').on('click', function() {
      var id = $('#id').val();
      var amount = $('#amount').val();
      var payout = $('#payout').val();
      var trade_type = $('#trade_type').val();
       $.ajax({
        method: "GET",
        url: "/admin/finance/company/update",
        data: {'id':id,'amount':amount,'payout':payout,'trade_type':trade_type},
        success:function(data) {
            if (data.type == 'success') {
                $('#financeModalCenter').modal("hide");    
                $('#admin_table').dataTable().api().ajax.reload();
                $("#js-msg-success").html(data.msg);
                $("#js-msg-success").show();
               setTimeout(function() {
                 $('#js-msg-success').fadeOut('fast');
                }, 4000);
            }if(data.type == 'errors'){ 
                $("#js-msg-error").html(data.msg);
                $("#js-msg-error").show();
            }
            if(data.type == 'error'){
                $('#financeModalCenter').modal("hide");  
                $("#js-msg-error").html(data.msg);
                $("#js-msg-error").show();
               setTimeout(function() {
                 $('#js-msg-error').fadeOut('fast');
                }, 4000);
            }
        },
      });
    });


    var dataTable;

    // Data Tables
    $(document).ready(function() {

      $("#financeModalCenter").removeAttr("tabindex");

      dataTable = $('#admin_table').DataTable({
          "processing": true,
          "serverSide": true,
          "ajax": "/admin/finance/company/list/api",
          "aaSorting": [],
          "responsive": true,
          "columns": [
              { "data": "company_name" },
              { "data": "payout" },
              { "data": "trade_type" },
              { "data": "amount" },
              {
                  "targets": [ -1 ],
                  "data":"Id", "render": function(data,type,full,meta)
                  {
                    var str = '';
                      str = str+'<a href="#"><button class="btn btn-info btn-xs "  onclick="GetFinanceCompany(this)"><i class="'+data+'"></i>Update Details</button></a> ';
                    
                      return str;
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
                    @include('admin.flash-message')
                    @yield('content')
        <!-- Default box -->
         <div class="row">
           <div class="alert alert-success" id="js-msg-success">
           </div>
         </div>
        <div class="box box-primary">
            <!-- /.box-header -->
            <div class="box-header with-border">
<!--                    <h3 class="box-title">{{__('customer.mytitle')}} </h3>-->
                </div>  
                <div class="box-body">

                  <table id="admin_table" class="table table-bordered table-striped">
                    <thead>
                    <tr>
                      
                      <th>Company Name</th>
                      <th>Payout</th>
                      <th>Trade Type</th>
                      <th>Amount</th>
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
           <div class="modal fade" id="financeModalCenter" tabindex="-1" role="dialog" aria-labelledby="financeModalCenterTitle" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="margin-top: 200px!important;">
          <div class="modal-header">
            <h4 class="modal-title" id="financeModalLongTitle">Update Finance Company</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">                   
            <form id="my-form"  method="POST" >
                <div class="alert alert-danger" id="js-msg-error">
              </div>
              @csrf
              <input type="hidden" name="id" id="id">
              <div class="row">
                <div class="col-md-6">
                  <label for="advance">Trade Type<sup>*</sup></label>
                  <select name="trade_type" id="trade_type" class="input-css select2" style="width:100%">
                    <option selected disabled>Select Type</option>
                    <option value="TA">TA</option>
                    <option value="IF">IF</option>
                    <option value="Account">Account</option>
                  </select>
               </div>
               <div class="col-md-6">
                    <label>Payout <sup>*</sup></label>
                    <input type="number" name="payout" id="payout" class="input-css" min="0">
                </div>
             </div><br>
               <div class="row">
                <div class="col-md-6">
                    <label>Amount <sup>*</sup></label>
                    <input type="number" name="amount" id="amount" class="input-css" min="0">
                </div>
              </div>
          </div>
          <div class="modal-footer">
            <button type="button" id="btnCancel" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="button" id="btnUpdate" class="btn btn-success">Update</button>
          </div>
          </form>
        </div>
      </div>
    </div>
      </section>
@endsection