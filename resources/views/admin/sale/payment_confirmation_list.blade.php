@extends($layout)

@section('title', __('Payment Confirmation List'))

@section('breadcrumb')
    <li><a href="#"><i class=""></i>{{__('Payment Confirmation List')}} </a></li>
@endsection
@section('css')
<link rel="stylesheet" href="/css/responsive.bootstrap.css"> 
<style>
</style>
@endsection
@section('js')
<script src="/js/dataTables.responsive.js"></script>
  <script>
  
    var dataTable;
    $("#js-msg-error").hide();
    $("#js-msg-success").hide();

    function ReceivedPayment(el){
      $('#loader').show();
      var id = $(el).children('i').attr('class');
      if (confirm("Are you sure?")) {
      $.ajax({  
                url:"/admin/sale/payment/confirmation/received/"+id,  
                method:"get",  
                success:function(data){ 
                  //  console.log(data);
                  if(data[0]) {
                    $('#admin_table').dataTable().api().ajax.reload();
                    $("#js-msg-success").html(data[1]);
                    $("#js-msg-success").show();
                    setTimeout(function() {
                     $('#js-msg-success').fadeOut('fast');
                   }, 4000); 
                  }else {
                      $("#js-msg-error").html(data[1]);
                      $("#js-msg-error").show();
                      setTimeout(function() {
                       $('#js-msg-error').fadeOut('fast');
                     }, 4000);                      
                    } 
                },
                error:function(error){
                  if ((error.responseJSON).hasOwnProperty('message')) {
                    $('#js-msg-error').html(error.responseJSON.message).show();
                  }else{
                    $('#js-msg-error').html(error.responseText).show();
                  }
                }
            }).done(function(){
              $('#loader').hide();
            }); 
        }
    }


      function CancelPayment(el){
        $('#loader').show();
      var id = $(el).children('i').attr('class');
      if (confirm("Do you really want to cancel this payment?")) {
      $.ajax({  
                url:"/admin/sale/payment/confirmation/cancel/"+id,  
                method:"get",  
                success:function(data){ 
                    if(data[0]) {
                        $('#admin_table').dataTable().api().ajax.reload();
                        $("#js-msg-success").html(data[1]);
                          $("#js-msg-success").show();
                          setTimeout(function() {
                           $('#js-msg-success').fadeOut('fast');
                         }, 4000); 
                    }else{
                      $("#js-msg-error").html(data[1]);
                      $("#js-msg-error").show();
                      setTimeout(function() {
                       $('#js-msg-error').fadeOut('fast');
                     }, 4000);                      
                    }
                    },
                    error:function(error){
                      // alert('try again');
                      if ((error.responseJSON).hasOwnProperty('message')) {
                        $('#js-msg-error').html(error.responseJSON.message).show();
                      }else{
                        $('#js-msg-error').html(error.responseText).show();
                      }
                    }
            }).done(function(){
              $('#loader').hide();
            }); 
        }
    }

    $(document).ready(function() {

      function getData($type){

        if(dataTable)
        {
          dataTable.destroy();
        }
        var admin_id = $('#admin').val();

          dataTable = $('#admin_table').DataTable({
              "processing": true,
              "serverSide": true,
              "ajax": {
                "url": "/admin/sale/payment/confirmation/list/api",
                "data": {
                    "type": $type
                }
              },
              "aaSorting": [],
              "responsive": true,
              "columns": [
                  { "data": "type" },
                  { "data": "payment_mode",
                    "render": function (data, type, row) {
                        if (data) {
                            var str = data;
                            var string = str.replace(/_/g, ' ');
                            return string.charAt(0).toUpperCase() + string.slice(1);
                          } 
                      }
                  },
                  { "data": "transaction_number" },
                  { "data": "transaction_charges" },
                  { "data": "receiver_bank_detail" },
                  { "data": "amount" },
                  { "data": "security_amount" },
                  { "data": "status"},
                  {
                      "targets": [ -1 ],
                      "data": "id", "render": function(data,type,full,meta)
                      {
                      if(admin_id > 0){ 
                            if(full.status == 'pending'){ 
                              return '<a href="#"><button class="btn btn-info btn-xs" onclick="ReceivedPayment(this)"><i class="'+data+'"></i> receive Payment</button></a> &nbsp;<a href="#"><button class="btn btn-primary btn-xs" onclick="CancelPayment(this)"><i class="'+data+'"></i> Cancel Payment</button></a>';
                            }
                            else{
                              return '';
                            }
                            if(full.status == 'cancelled'){ 
                              return '<a href="#"><button class="btn btn-primary btn-xs" disabled>Cancelled</button></a>';
                            }else{return '';}
                        }else{
                            return '';
                        }
                    },
                      "orderable": false
                  }
                ]
              
            });
      }

      getData('pending');

      $("#pending").on('click',function(){

        // set received tab
        $('#received').removeClass('btn-color-active');
        $('#received').addClass('btn-color-unactive');
         $('#cancelled').removeClass('btn-color-active');
        $('#cancelled').addClass('btn-color-unactive');

        // set pending tab
        $('#pending').removeClass('btn-color-unactive');
        $('#pending').addClass('btn-color-active');

        getData('pending');
      }); 

      $("#received").on('click',function(){
        // set pending tab
        $('#pending').removeClass('btn-color-active');
        $('#pending').addClass('btn-color-unactive');
        $('#cancelled').removeClass('btn-color-active');
        $('#cancelled').addClass('btn-color-unactive');

        // set received tab
        $('#received').removeClass('btn-color-unactive');
        $('#received').addClass('btn-color-active');

        getData('received');
      }); 

       $("#cancelled").on('click',function(){
        // set received tab
        $('#received').removeClass('btn-color-active');
        $('#received').addClass('btn-color-unactive');
         $('#pending').removeClass('btn-color-active');
        $('#pending').addClass('btn-color-unactive');
        // set pending tab
        $('#cancelled').removeClass('btn-color-unactive');
        $('#cancelled').addClass('btn-color-active');

        getData('cancelled');
      }); 
    });

  </script>
@endsection

@section('main_section')

    <section class="content">

        <div id="app">
        <input type="hidden" value="{{$admin}}" id="admin">  
        <div class="box box-primary">
            <!-- /.box-header -->
            <div class="box-header with-border">
              <ul class="nav nav1 nav-pills">
                <li class="nav-item">
                  <button class="nav-link1 btn-color-active" id="pending" >Pending Payment</button>
                </li>
                <li class="nav-item">
                  <button class="nav-link1 btn-color-unactive" id="received" >Received Payment</button>
                </li> 
                 <li class="nav-item">
                  <button class="nav-link1 btn-color-unactive" id="cancelled" >Cancelled Payment</button>
                </li> 
              </ul>
            </div>  
                <div class="box-body">
                   @include('admin.flash-message')
                    <div class="alert alert-danger" id="js-msg-error">
                    </div>
                    <div class="alert alert-success" id="js-msg-success">
                    </div>
                    @yield('content')
                  <table id="admin_table" class="table table-bordered table-striped">
                    <thead>
                    <tr>
                      <th>Type</th>
                      <th>Mode</th>
                      <th>Transaction Number</th>
                      <th>Transaction Charges</th>
                      <th>Receiver Bank Detail</th>
                      <th>Amount</th>
                      <th>Security Amount</th>
                      <th>status</th>
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
      </section>
@endsection