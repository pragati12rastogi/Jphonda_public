@extends($layout)

@section('title', __('hirise.rto_file'))

@section('user', Auth::user()->name)

@section('breadcrumb')

<li><a href="#"><i class=""></i>{{__('hirise.rto_file')}}</a></li> 
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
<meta name="csrf-token" content="{{ csrf_token() }}" />

<script>
  var dataTable;
  var CSRF = $("meta[name='csrf-token']").attr('content');

   function fileUploade(el){
      var id = $(el).children('i').attr('class');
      if(id > 0){
        $("#rto_id").val(id);
        $("#loader").show();
            $.ajax({
                url:'/admin/rto/get/list/data',
                data:{'rto_id':id},
                method:'get',
                success:function(res){
                  // console.log(res);
                   $("#js-msg-error").hide();
                  draw_modal(res);
                  $('#rtoModalCenter').modal("show"); 
                },  
                error:function(error){
                  // console.log(error);
                  // $("#loader").hide();
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
      
    }
    function draw_modal(arr){
      var el = $("#modal-body-data");
      el.find('.sale_no').text(arr.sale_no);
      el.find('.frame').text(arr.product_frame_number);
      el.find('.customer_name').text(arr.customer_name);
      el.find('.rto_ref').text(arr.application_number);
    }

      $('#btnUpload').on('click', function() {
       
      var id = $('#rto_id').val();
      var file_status = $("input[type='radio']:checked").val();
       $.ajax({
        method: "GET",
        url: "/admin/rto/file/upload",
        data: {'id':id,'file_status':file_status},
        success:function(response) {
           if(response.type == 'success')
              {
                $('#pending_table').dataTable().api().ajax.reload();
                $("#js-msg-success").html(response.msg);
                $("#js-msg-success").show();
                $('#rtoModalCenter').modal("hide"); 
                setTimeout(function() {
                  $('#js-msg-success').fadeOut('fast');
                }, 3000);
              }
            if (response.type == 'error') {
               $("#js-msg-error").html(response.msg);
               $("#js-msg-error").show();
            }
        },
        error:function(error){
          
          if(error.responseJSON.hasOwnProperty('message')){
            $("#js-msg-error").text(error.responseJSON.message).show();
          }else{
            $("#js-msg-error").text(error.responseText).show();
          }
          setTimeout(function() {
            $('#js-msg-error').fadeOut('fast');
          }, 3000);
        }
      });

    });

  $(document).ready(function() {

   function pendingRtoPayment()
    {
      $("#uploaded-div").css('display','none');
      $("#pending-div").css('display','block');
      if(dataTable)
      {
        dataTable.destroy();
      }
      dataTable = $('#pending_table').DataTable({
         "processing": true, 
         "serverSide": true,
         "ajax": "/admin/rto/file/list/api/pending",
         "aaSorting": [],
         "responsive": true,
         "columns": [
              // { "data": "id" },
              { "data": "sale_no" },
              { "data": "rto_type",
              "render": function (data, type, row) {
                    if (data == null) {
                        return '';
                    } else{
                      var str = data;
                      var string = str.replace(/_/g, ' ');
                      return string.charAt(0).toUpperCase() + string.slice(1);
                    }
                }
              },
              { "data": "rto_finance" },
              { "data": "rto_amount" },
              { "data": "application_number" },
              { "data": "file_uploaded", 
              "render": function (data, type, row) {
                    if (data == 0) {
                        return 'No';
                      } 
                }
              },
             {
                 "data":"id", "render": function(data,type,full,meta)
                 {
                   return '<a href="#"><button class="btn btn-info btn-xs " onclick="fileUploade(this)"><i class="'+data+'"></i>File Upload</button></a>' ;
                 },
                
                 "orderable": false
             }
           ]
         
       });
    }
 

    function uploadedRtoPayment()
    {
      $("#uploaded-div").css('display','block');
      $("#pending-div").css('display','none');
      if(dataTable)
      {
        dataTable.destroy();
      }
      dataTable = $('#uploaded_table').DataTable({
         "processing": true,
         "serverSide": true,
         "ajax": "/admin/rto/file/list/api/uploaded",
         "aaSorting": [],
         "responsive": true,
         "columns": [
              // { "data": "id" },
              { "data": "sale_no" },
              { "data": "rto_type",
              "render": function (data, type, row) {
                     if (data == null) {
                        return '';
                    } else{
                      var str = data;
                      var string = str.replace(/_/g, ' ');
                      return string.charAt(0).toUpperCase() + string.slice(1);
                    }
                }
              },
              { "data": "rto_finance" },
              { "data": "rto_amount" },
              { "data": "application_number" },
              { "data": "file_uploaded", 
              "render": function (data, type, row) {
                    if (data == 1) {
                        return 'Yes';
                      } 
                }
               },
              { "data": "uploaded_date" }
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

   
  pendingRtoPayment();

      $("#uploaded").on('click',function(){
          $(this).css('background', 'rgb(135, 206, 250)');
          $("#pending").css('background', '#fff');
          $(this).addClass('btn-color-active');
       
        uploadedRtoPayment();
      });

      $("#pending").on('click',function(){
          $(this).css('background', 'rgb(135, 206, 250)');
          $("#uploaded").css('background', '#fff');
          $(this).addClass('btn-color-active');
       
        pendingRtoPayment();
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
              <button class="nav-link1 btn-color-active" id="pending" >Pending RTO File</button>
            </li> 
            <li class="nav-item">
              <button class="nav-link1 btn-color-anactive" id="uploaded" >Uploaded RTO File</button>
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
                              <th>{{__('RTO Type')}}</th>
                              <th>{{__('hirise.rto_finance')}}</th>
                              <th>{{__('hirise.rto_amount')}}</th>
                              <th>{{__('hirise.rto_app_no')}}</th>
                              <th>{{__('File Upload')}}</th>
                              <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>               
                    </table>
                    <!-- /.box-body -->
            </div>
              <div id="uploaded-div" class="box-body" style="display:none;">
                    <table id="uploaded_table" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                              {{-- <th>Id</th> --}}
                              <th>{{__('Sale Number')}}</th>
                              <th>{{__('RTO Type')}}</th>
                              <th>{{__('hirise.rto_finance')}}</th>
                              <th>{{__('hirise.rto_amount')}}</th>
                              <th>{{__('hirise.rto_app_no')}}</th>
                              <th>{{__('File Upload')}}</th>
                              <th>{{__('File Upload Date')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>               
                    </table>
                    <!-- /.box-body -->
            </div>
             
          </div>
        </div>
          <!-- Modal -->
              <div class="modal fade" id="rtoModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                  <div class="modal-content" style="margin-top: 200px!important;">
                    <div class="modal-header">
                      <h4 class="modal-title" id="exampleModalLongTitle">RTO File Upload</h4>
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                      </button>
                    </div>
                    <div class="modal-body">
                       <div class="alert alert-danger" id="js-msg-error">
                        </div>
                        <div class="alert alert-danger" id="js-msg-verror">
                        </div>
                      <form id="my-form"  method="POST">
                        @csrf
                        <div class="row" id="modal-body-data">
                          <div class="col-md-6">
                            <div class="col-md-6">
                              <label>Sale # </label>
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
                              <label>Frame # </label>
                            </div>
                            <div class="col-md-6">
                              <label class="text-label frame"></label>
                            </div>
                          </div>
                        </div>
                        <div class="row">
                          <div class="col-md-6">
                              <label>File Uploaded<sup>*</sup></label>
                              <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
                              <input id="rto_id" type="hidden" name="rto_id" class="input-css">
                              <input  class="file" type="radio" name="file" value="1"> Yes 
                              <br>
                          </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                      <button type="button" id="btnUpload" class="btn btn-success">Upload</button>
                    </div>
                    </form>
                  </div>
                </div>
              </div>
        <!-- /.box -->
    </section>
@endsection