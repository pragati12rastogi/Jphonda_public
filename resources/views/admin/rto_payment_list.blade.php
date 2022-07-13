@extends($layout)

@section('title', __('hirise.rto_payment'))

@section('user', Auth::user()->name)

@section('breadcrumb')

    <li><a href="#"><i class=""></i>{{__('hirise.rto_payment')}}</a></li> 
@endsection
@section('css')

<link rel="stylesheet" href="/css/responsive.bootstrap.css">    
<style>

 tr.financerChanged{
  background-color: lightcoral !important;
}
</style>
@endsection
@section('js')
<script src="/js/dataTables.responsive.js"></script>
<script>
  var dataTable;

   function rtoPayment(el){
     $("#loader").show();
     $("#error").hide();
      var id = $(el).children('i').attr('class');
      $.ajax({  
                url:"/admin/rto/payment/"+id,  
                method:"get",  
                success:function(data){ 
                 if(data) {
                        $("#amount").empty();
                        draw_modal(data);
                        $('#exampleModalCenter').modal("show"); 
                    }
                },error:function(error){
                  $("#loader").hide();
                  if(error.responseJSON.hasOwnProperty('message')){
                    $("#error").text(error.responseJSON.message).show();
                  }else{
                    $("#error").text(error.responseText).show();
                  }
                }  
           }).done(function(){
              $("#loader").hide();
           });
    }

    function draw_modal(arr){

      $("#rto_id").val(arr.id);
      $("#amount").val(arr.amount);
      $("#total_amount").val(arr.rto_amount);
      $("#sale_id").val(arr.sale_id);
      $("#penalty_charge").val(arr.penalty_charge);

      var el = $("#modal-body-div");
      el.find('.sale_no').text(arr.sale_no);
      el.find('.frame').text(arr.product_frame_number);
      el.find('.customer_name').text(arr.customer_name);      
      el.find('.rto_amount').text(arr.rto_amount);
      el.find('.rto_ref').text(arr.application_number);
    }

      $('#btnPay').on('click', function() {
      var id = $('#rto_id').val();
      var sale_id = $('#sale_id').val();
      var total_amt = $('#total_amount').val();
      var amt = $('#amount').val();
      var penalty_charge = $('#penalty_charge').val();
       $.ajax({
        method: "GET",
        url: "/admin/rto/payment/update/data",
        data: {'id':id,'sale_id':sale_id,'total_amt':total_amt,'amt':amt,'pcharge':penalty_charge},
        success:function(data) {
           if(data.type == 'success') {
                $("#js-msg-success").html('RTO payment successfully paid !');
                $("#js-msg-success").show();
                $('#rtoModalCenter').modal("hide");  
                 setTimeout(function() {
                  $('#js-msg-success').fadeOut('fast');
                }, 3000);
                if (data.main_type == 'hsrp' || data.main_type == 'new') {
                  $('#hsrp_table').dataTable().api().ajax.reload();
                }else{
                  $('#pending_table').dataTable().api().ajax.reload();
                }
               
              }
            if(data.type == 'validation_error'){
              $("#js-msg-verror").html('Amount should be equal of RTO amount !');
              $("#js-msg-verror").show();
              setTimeout(function() {
                  $('#js-msg-verror').fadeOut('fast');
                }, 3000);
            }

            if (data.type == 'error') {
               $("#js-msg-error").html('Something went wronge !');
               $("#js-msg-error").show();
               setTimeout(function() {
                  $('#js-msg-error').fadeOut('fast');
                }, 3000); 

            }
            if (data.type == 'approve_erro') {
               $("#js-msg-error").html('Please firstly approve this RTO the add payment!');
               $("#js-msg-error").show();
               setTimeout(function() {
                  $('#js-msg-error').fadeOut('fast');
                }, 3000);
            }
            
        },
        error:function(data){
          $("#js-msg-error").html('Fields is require.').show();
        }
      });

    });

  $(document).ready(function() {

   function pendingRtoPayment()
    {
      $("#complete-div").css('display','none');
      $("#hsrp-div").css('display','none');
      $("#pending-div").css('display','block');
      if(dataTable)
      {
        dataTable.destroy();
      }
      dataTable = $('#pending_table').DataTable({
         "processing": true, 
         "serverSide": true,
         "ajax": "/admin/rto/payment/list/api/pending",
         "aaSorting": [],
         "responsive": true,
         'createdRow':function(row,data,dataIndex)
          {
              // $(row).addClass('rowClass');
              if(data.finance_status == 1)
              {
                $(row).addClass('financerChanged');
              }
          },
         "columns": [
              { "data": "sale_no" },
              // { "data": "sale_no","render":function(data,type,full,meta){
              //     var str = data;
              //     // console.log(full.finance_status);
              //     if(full.finance_status == 1){
              //       str = str+' <small style="color:red">(Financer Changed)</small>';
              //     }
              //     return str;
              //   }
              // },
              { "data": "customer_name" },
              { "data": "frame" },
              { "data": "rto_type",
                "render": function (data, type, row) {
                    if (data) {
                        var str = data;
                        var string = str.replace(/_/g, ' ');
                        return string.charAt(0).toUpperCase() + string.slice(1);
                      } 
                      return '';
                }
              },
              { "data": "rto_finance" },
              { "data": "rto_amount" },
              { "data": "application_number" },
              { "data": "amount" },
              { "data": "penalty_charge" },
               { "data": "approve",
              "render": function (data, type, row) {
                    if (data == 1) {
                        return 'Approved';
                      } 
                }
              },
              {
                 "targets": [ -1 ],
                 "data":"id", "render": function(data,type,full,meta)
                 {
                   return '<a href="#"><button class="btn btn-info btn-xs "  onclick="rtoPayment(this)"><i class="'+data+'"></i>RTO Payment</button></a>' ;
                 },
                 "orderable": false
             }
           ]
         
       });
    }
 

    function completeRtoPayment()
    {
      $("#complete-div").css('display','block');
      $("#pending-div").css('display','none');
      $("#hsrp-div").css('display','none');
      if(dataTable)
      {
        dataTable.destroy();
      }
      dataTable = $('#complete_table').DataTable({
         "processing": true,
         "serverSide": true,
         "ajax": "/admin/rto/payment/list/api/complete",
         "aaSorting": [],
         "responsive": true,
         'createdRow':function(row,data,dataIndex)
          {
              // $(row).addClass('rowClass');
              if(data.finance_status == 1)
              {
                $(row).addClass('financerChanged');
              }
          },
         "columns": [
              { "data": "sale_no" },
              // { "data": "sale_no","render":function(data,type,full,meta){
              //     var str = data;
              //     // console.log(full.finance_status);
              //     if(full.finance_status == 1){
              //       str = str+' <small style="color:red">(Financer Changed)</small>';
              //     }
              //     return str;
              //   }
              // },
              { "data": "customer_name" },
              { "data": "frame" },
              { "data": "rto_type",
              "render": function (data, type, row) {
                    if (data) {
                        var str = data;
                        var string = str.replace(/_/g, ' ');
                        return string.charAt(0).toUpperCase() + string.slice(1);
                      } 
                }
              },
              { "data": "rto_finance" },
              { "data": "rto_amount" },
              { "data": "application_number" },
              { "data": "amount" },
              { "data": "penalty_charge" },
               { "data": "approve",
              "render": function (data, type, row) {
                    if (data == 1) {
                        return 'Approved';
                      } 
                }
              }
             // {
             //     "targets": [ -1 ],
             //     "data":"id", "render": function(data,type,full,meta)
             //     {
             //       return '<a href="/admin/sale/pay/'+data+'"><button class="btn btn-info btn-xs"> Pay</button></a>' ;
             //     },
             //     "orderable": false
             // }
           ]
         
       });
    }

    function pendingHsrp() {
      $("#complete-div").css('display','none');
      $("#pending-div").css('display','none');
      $("#hsrp-div").css('display','block');
      if(dataTable)
      {
        dataTable.destroy();
      }
      dataTable = $('#hsrp_table').DataTable({
         "processing": true,
         "serverSide": true,
         "ajax": "/admin/rto/hsrp/payment/list/api",
         "aaSorting": [],
         "responsive": true,
         "columns": [
              { "data": "name" },
              { "data": "main_type"},
              { "data": "mobile" },
              { "data": "frame" },
              { "data": "hsrp_type" },
              { "data": "amount" },
              { "data": "penalty_charge" },
              { "data": "rto_amount" },
              { "data": "application_number" },
              { "data": "approve",
              "render": function (data, type, row) {
                    if (data == 0) {
                        return 'Pending';
                      } else{
                        return 'Approved';
                      }
                }
              },
             {
                 "targets": [ -1 ],
                 "data":"id", "render": function(data,type,full,meta) {
                  var str = '';
                  if (full.amount == null) {
                    str += '<a href="#"><button class="btn btn-info btn-xs "  onclick="rtoPayment(this)"><i class="'+data+'"></i>RTO Payment</button></a>';
                  }
                  return str;
                 },
                 "orderable": false
             }
           ]
         
       });
    }

   
  pendingRtoPayment();

      $("#complete").on('click',function(){
          $(this).css('background', 'rgb(135, 206, 250)');
          $("#pending").css('background', '#fff');
          $("#hsrp").css('background', '#fff');
          $(this).addClass('btn-color-active');
       
        completeRtoPayment();
      });

      $("#pending").on('click',function(){
          $(this).css('background', 'rgb(135, 206, 250)');
          $("#complete").css('background', '#fff');
          $("#hsrp").css('background', '#fff');
          $(this).addClass('btn-color-active');
        pendingRtoPayment();
      });

      $("#hsrp").on('click',function(){
        $(this).css('background', 'rgb(135, 206, 250)');
        $("#complete").css('background', '#fff');
        $("#pending").css('background', '#fff');
        $(this).addClass('btn-color-active');
        pendingHsrp();
      });

       $("#js-msg-error").hide();
       $("#js-msg-verror").hide();
       $("#js-msg-success").hide();

    });


</script>
@endsection

@section('main_section')
    <section class="content">
        <div id="app">
            @include('admin.flash-message')
            @yield('content')
             <div class="row">
            <div class="alert alert-success" id="js-msg-success" style="display:none">
            </div>
            <div class="alert alert-danger" id="error" style="display:none">
            </div>
          </div>
            
        </div>
    <!-- Default box -->
        <div class="box">
                <!-- /.box-header -->
        <div class="box-header with-border">
          <ul class="nav nav1 nav-pills">
            <li class="nav-item">
              <button class="nav-link1 btn-color-active" id="pending" >Pending RTO Payment</button>
            </li> 
            <li class="nav-item">
              <button class="nav-link1 btn-color-anactive" id="complete" >Complete RTO Payment</button>
            </li> 
            <li class="nav-item">
              <button class="nav-link1 btn-color-anactive" id="hsrp" >New Correction </button>
            </li> 
          </ul>
        </div>

            <div class="box-body">
               <div id="pending-div" class="box-body" >
                    <table id="pending_table" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                              {{-- <th>Id</th> --}}
                              <th>{{__('Sale Number')}}</th>
                              <th>{{__('Customer Name')}}</th>
                              <th>{{__('Frame #')}}</th>
                              <th>{{__('RTO Type')}}</th>
                              <th>{{__('hirise.rto_finance')}}</th>
                              <th>{{__('hirise.rto_amount')}}</th>
                              <th>{{__('hirise.rto_app_no')}}</th>
                              <th>{{__('hirise.totalamt')}}</th>
                              <th>{{__('Penalty charge')}}</th>
                              <th>{{__('Approval')}}</th>
                              <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>               
                    </table>
                    <!-- /.box-body -->
            </div>
              <div id="complete-div" class="box-body" style="display:none;">
                    <table id="complete_table" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                              {{-- <th>Id</th> --}}
                              <th>{{__('Sale Number')}}</th>
                              <th>{{__('Customer Name')}}</th>
                              <th>{{__('Frame #')}}</th>
                              <th>{{__('RTO Type')}}</th>
                              <th>{{__('hirise.rto_finance')}}</th>
                              <th>{{__('hirise.rto_amount')}}</th>
                              <th>{{__('hirise.rto_app_no')}}</th>
                              <th>{{__('hirise.totalamt')}}</th>
                              <th>{{__('Penalty charge')}}</th>
                              <th>{{__('Approval')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>               
                    </table>
                    <!-- /.box-body -->
            </div>

            <div id="hsrp-div" class="box-body" style="display:none;">
          <table id="hsrp_table" class="table table-bordered table-striped">
              <thead>
                <tr>
                    <th>{{__('Name')}}</th>
                    <th>{{__('hirise.rto_type')}}</th>                    
                    <th>{{__('Mobile')}}</th>                    
                    <th>{{__('Frame')}}</th>                    
                    <th>{{__('Type')}}</th>                    
                    <th>{{__('Amount')}}</th>                    
                    <th>{{__('Penalty Charge')}}</th>                    
                    <th>{{__('hirise.rto_amount')}}</th>                    
                    <th>{{__('hirise.rto_app_no')}}</th>                    
                    <th>Approve</th>
                    <th>Action</th> 
                </tr>
              </thead>
              <tbody>
              </tbody>
          </table>
        </div>
             
          </div>
        </div>
          <!-- Modal -->
              <div class="modal fade" id="rtoModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                  <div class="modal-content" style="margin-top: 100px!important;">
                    <div class="modal-header">
                      <h4 class="modal-title" id="exampleModalLongTitle">RTO Payment</h4>
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                      </button>
                    </div>
                    <div class="modal-body" >
                      <div class="alert alert-danger" id="js-msg-error">
                      </div>
                      <div class="alert alert-danger" id="js-msg-verror">
                      </div>
                      <form id="my-form"  method="POST">
                        @csrf
                        <div class="row" id="modal-body-div">
                          <div class="col-md-6">
                            <div class="col-md-6">
                              <label>Sale #</label>
                            </div>
                            <div class="col-md-6">
                              <label class="text-label sale_no"></label>
                            </div>
                          </div>
                          <div class="col-md-6">
                            <div class="col-md-6">
                              <label>Customer Name</label>
                            </div>
                            <div class="col-md-6">
                              <label class="text-label customer_name"></label>
                            </div>
                          </div>
                          <div class="col-md-6">
                            <div class="col-md-6">
                              <label>RTO Reference #</label>
                            </div>
                            <div class="col-md-6">
                              <label class="text-label rto_ref"></label>
                            </div>
                          </div>
                          <div class="col-md-6">
                            <div class="col-md-6">
                              <label>Frame #</label>
                            </div>
                            <div class="col-md-6">
                              <label class="text-label frame"></label>
                            </div>
                          </div>
                          <div class="col-md-6">
                            <div class="col-md-6">
                              <label>RTO Amount</label>
                            </div>
                            <div class="col-md-6">
                              <label class="text-label rto_amount"></label>
                            </div>
                          </div>
                        </div>
                        <div class="row">
                          <div class="col-md-6">
                              <label>Amount <sup>*</sup></label>
                              <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
                              <input id="total_amount" type="hidden" name="total_amount" class="input-css">
                              <input id="rto_id" type="hidden" name="rto_id" class="input-css">
                              <input id="sale_id" type="hidden" name="sale_id" class="input-css">
                              <input id="amount" type="number" name="amount" class="input-css" min="0">
                              <br>
                          </div>
                            <div class="col-md-6">
                              <label>Penalty Charge <sup>*</sup></label>
                              <input id="penalty_charge" type="number" name="penalty_charge" class="input-css" min="0">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                      <button type="button" id="btnPay" class="btn btn-success">Pay</button>
                    </div>
                    </form>
                  </div>
                </div>
              </div>
        <!-- /.box -->
    </section>
@endsection