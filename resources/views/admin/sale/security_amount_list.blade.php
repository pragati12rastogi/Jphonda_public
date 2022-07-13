@extends($layout)
@section('title', __('Security Amount List'))
@section('breadcrumb')
<li><a href="#"><i class=""></i>{{__('Security Amount List')}}</a></li>
@endsection
@section('css')
<link rel="stylesheet" href="/css/responsive.bootstrap.css">
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
 .nav1>li>button.btn-color-active{
  background-color: rgb(135, 206, 250);
 }
 .nav1>li>button.btn-color-unactive{
  background-color: white;
 }
 .spanred{
   color: red;
 }
</style>
@endsection
@section('js')
<script src="/js/dataTables.responsive.js"></script>
<script>
   var dataTable;

   function RefundPayment(el){
      var id = $(el).children('i').attr('class');
      $("#security_id").val(id);
      $('#securityModalCenter').modal("show"); 
    }

      $('#btnUpload').on('click', function() {
      var id = $('#security_id').val();
      var refund_amount = $("#refund_amount").val();
      var comment = $("#refund_comment").val();
       $.ajax({
        method: "GET",
        url: "/admin/sale/security/amount/refund",
        data: {'id':id,'refund':refund_amount,'comment':comment},
        success:function(data) {
         // console.log(data);
           if(data[0] == 'success')
              {
                $('#table').dataTable().api().ajax.reload();
                $("#js-msg-success").html(data[1]).show();
                $('#securityModalCenter').modal("hide"); 
                setTimeout(function() {
                  $('#js-msg-success').fadeOut('fast');
                }, 3000);
               
              }
            if (data[0] == 'error') {
               $("#js-msg-error").html(data[1]).show();
              $('#securityModalCenter').modal("hide");
               setTimeout(function() {
                  $('#js-msg-error').fadeOut('fast');
                }, 3000);

            }
            if (data[0] == 'required') {
               $("#js-msg-verror").html(data[1]).show();
               setTimeout(function() {
                  $('#js-msg-verror').fadeOut('fast');
                }, 3000);
            }
        },
        error:function(data){
          $("#js-msg-error").html(data.responseText);
          $("#js-msg-error").show();
          setTimeout(function() {
            $('#js-msg-error').fadeOut('fast');
          }, 3000);

        }
      });

    });
 
      $("#js-msg-verror").hide();
      $("#js-msg-error").hide();
       $("#js-msg-success").hide();
    security_amount();
    function security_amount()
    {

      if(dataTable)
      {
        dataTable.destroy();
      }
      dataTable = $('#table').DataTable({
         "processing": true,
         "serverSide": true,
         "ajax": "/admin/sale/security/amount/list/api",
         "aaSorting": [],
         "responsive": true,
         "columns": [
             { "data": "security_number" },
             { "data": "sale_no" },
             { "data": "store_name" },
             { "data": "name" },
             { "data": "mobile" },
             { "data": "reason" },
             { "data": "total_amount" },
             { "data": "refund_amount" },
             { "data": "security_amount" },
             {
                  "targets": [ -1 ],
                  "data":"id", "render": function(data,type,full,meta)
                  {
                    var str = '';
                    var disabled = '';
                    if(full.security_amount <= 0){
                      disabled = 'disabled';
                    }

                    str = str+'<a href="/admin/sale/security/amount/pay?id='+data+'"><button class="btn btn-success btn-xs">Pay</button></a>&nbsp;';
                    
                    str = str+' &nbsp;<a href="/admin/sale/security/amount/pay/detail/'+data+'"><button '+disabled+' class="btn btn-info btn-xs">Payment Details</button></a>'+
                           ' &nbsp;<a href="#"><button '+disabled+' class="btn btn-danger btn-xs " onclick="RefundPayment(this)"><i class="'+data+'"></i>Refund</button></a> '  ;
                    
                    
                    return str;
                  },
                  "orderable": false
             }
           ]
         
       });
    }
  
</script>
@endsection
@section('main_section')
<section class="content">
   <div id="app">
    @include('admin.flash-message')
    <div class="alert alert-danger" id="js-msg-error">
    </div>
    <div class="alert alert-success" id="js-msg-success">
    </div>
    @yield('content')
    <!-- Default box -->
    <div class="box box-primary">
        <!-- /.box-header -->
        <div class="box-header with-border">
          {{-- 
          <h3 class="box-title">{{__('customer.mytitle')}} </h3>
          --}}
          {{-- <ul class="nav nav1 nav-pills">
            <li class="nav-item">
              <button class="nav-link1 btn-color-active" id="all" >All Sale</button>
            </li>
            <li class="nav-item">
              <button class="nav-link1 btn-color-unactive" id="complete" >Complete Sale</button>
            </li> 
            <li class="nav-item">
              <button class="nav-link1 btn-color-unactive" id="pending" >Pending Sale</button>
            </li> 
          </ul> --}}
        </div>
        <div class="box-body" id="div">
          <table id="table" class="table table-bordered table-striped">
              <thead>
                <tr>
                    <th>{{__('Security Number')}}</th>
                    <th>{{__('Sale Number')}}</th>
                    <th>{{__('hirise.store')}}</th>
                    <th>{{__('hirise.customer')}}</th>
                    <th>{{__('Mobile')}}</th>
                    <th>{{__('Reason')}}</th>
                    <th>{{__('Total Received Amount')}}</th>
                    <th>{{__('Refund Amount')}}</th>
                    <th>{{__('Received Amount')}}</th>
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

   <!-- Modal -->
              <div class="modal fade" id="securityModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                  <div class="modal-content" style="margin-top: 200px!important;">
                    <div class="modal-header">
                      <h4 class="modal-title" id="exampleModalLongTitle">Refund Payment</h4>
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                      </button>
                    </div>
                    <div class="modal-body">
                       <div class="alert alert-danger" id="js-msg-verror">
                        </div>
                      <form id="my-form"  method="POST">
                        @csrf
                        <div class="row">
                          <div class="col-md-6">
                            <label>Refund Amount <sup>*</sup></label>
                            <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
                            <input id="security_id" type="hidden" name="security_id" class="input-css">
                            <input class="form-control" type="number" name="refund_amount" id="refund_amount"> 
                          </div>
                        </div>
                        <div class="row">
                        <div class="col-md-12">
                            <label>{{__('Comment')}} <sup>*</sup></label>
                            <textarea class="form-control" name="refund_comment" id="refund_comment" rows="3" cols="10">{{(old('reason')) ? old('reason') : '' }}</textarea>
                            {!! $errors->first('reason', '<p class="help-block">:message</p>') !!}
                        </div>
                    </div><br>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                      <button type="button" id="btnUpload" class="btn btn-success">Pay</button>
                    </div>
                    </form>
                  </div>
                </div>
              </div>
   <!-- /.box -->
</section>
@endsection