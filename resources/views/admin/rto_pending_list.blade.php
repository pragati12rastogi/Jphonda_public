@extends($layout)
@section('title', __('hirise.rto_list'))
@section('breadcrumb')
<li><a href="#"><i class=""></i>{{__('hirise.rto_list')}}</a></li>
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
<meta name="csrf-token" content="{{ csrf_token() }}" />
<script>
  var CSRF = $("meta[name='csrf-token']").attr('content');
   var dataTable;

    function openModal(el){
      $("#js-msg-error").hide();
      $("#js-msg-success").hide();
      $("#modal-error").hide();
      $("#modal-success").hide();
      var rto_id = parseInt($(el).attr('rto_id'));
      var conf = confirm("Are You Sure ?");
      if(conf){
          $("#loader").show();
          $.ajax({
              url:'/admin/rto/get/list/data',
              data:{'_token':CSRF,'rto_id':rto_id},
              method:'GET',
              success:function(res){
                // console.log(res);
                $("#modal-rto_id").val(res.rto_id);
                draw_modal(res);
                $("#rtoModal").modal('show');
              },  
              error:function(error){
                // console.log(error);
                $("#loader").hide();
                if(error.responseJSON.hasOwnProperty('message')){
                  $("#js-msg-error").text(error.responseJSON.message).show();
                }else{
                  $("#js-msg-error").text(error.responseText).show();
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
      el.find('.store_name').text(arr.store_name);
      el.find('.frame').text(arr.product_frame_number);
      el.find('.engine').text(arr.engine_number);
      el.find('.customer_name').text(arr.customer_name);
      el.find('.relation_name_label').text(arr.relation_type);
      el.find('.relation_name').text(arr.relation);
      el.find('.address').text(arr.address);
      if(arr.location != null && arr.location != '' && arr.location != undefined){
        el.find('.location_name').parent().parent().show();
        el.find('.location_name').text(arr.location);
      }else{
        el.find('.location_name').parent().parent().hide();
      }
      el.find('.mobile').text(arr.mobile);
      if (arr.main_type == 'rto') {
        var string = arr.rto_type.replace(/_/g, ' ');
        var rto_type =  string.charAt(0).toUpperCase() + string.slice(1);
        el.find('.rto_type').text(rto_type);
      }
      
      if(arr.customer_pay_type != 'cash'){
        el.find('.rto_finance').text('YES');
        el.find('.financer_name').text(arr.rto_finance);
      }else{
        el.find('.rto_finance').text('NO');
        el.find('.financer_name').text('NONE');
      }
      el.find('.rto_amount').text(arr.rto_amount);
      el.find('.rto_application').text(arr.application_number);
      el.find('.model_name').text(arr.model_name);
      el.find('.ex_showroom_price').text(arr.ex_showroom_price);
    }

    $("#ApproveModal").on('click',function(){
      $("#modal-error").hide();
      $("#modal-success").hide();
      var id = parseInt($("#modal-body").find('#modal-rto_id').val());
      if(id) {
          approve_fun(id);
      }
    });

    function approve_fun(id){
      $("#loader").show();
      $.ajax({  
                url:"/admin/rto/approval/"+id,  
                method:"GET",  
                data:{'_token':CSRF},
                success:function(result){ 

                  if(result.type == 'success') {
                      $("#js-msg-success").html(result.msg);
                      $("#js-msg-success").show();
                      setTimeout(function() {
                        $('#js-msg-success').fadeOut('fast');
                      }, 3000);
                      $("#rtoModal").modal('hide');
                       $("#loader").hide();
                      
                      if (result.main_type == 'hsrp' || result.main_type == 'new') {
                        $('#hsrp_table').dataTable().api().ajax.reload();
                      }else{
                        $('#pending_table').dataTable().api().ajax.reload();
                      }
                    }else{
                      $("#modal-error").html(result.msg);
                      $("#modal-error").show();
                      setTimeout(function() {
                        $('#modal-error').fadeOut('fast');
                      }, 3000);
                    }
                    $("#loader").hide();
                                    
                },
                error:function(error){
                  $("#loader").hide();
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
    }
    
   // Data Tables
   $(document).ready(function() {

    $("#js-msg-error").hide();
    $("#js-msg-success").hide();



    function approveRto() {
      $("#approve-div").css('display','block');
      $("#pending-div").css('display','none');
      $("#hsrp-div").css('display','none');
      if(dataTable)
      {
        dataTable.destroy();
      }
      dataTable = $('#approve_table').DataTable({
         "processing": true,
         "serverSide": true,
         "ajax": "/admin/rto/list/api/1",
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
              // { "data": "id" },
              { "data": "sale_no" },
              { "data": "main_type",
                "render": function (data, type, row) {
                    if (data) {
                        var str = data;
                        var string = str.replace(/_/g, ' ');
                        return string.charAt(0).toUpperCase() + string.slice(1);
                      } 
                      else{
                        return '';
                      }
                }
              },
              { "data": "rto_finance" },
              { "data": "rto_amount" },
              { "data": "application_number" },
              { "data": "approve",
              "render": function (data, type, row) {
                      if (data == 0) {
                        return 'Pending';
                      }else if(data == '2'){
                        return 'Rejected';
                      }else{
                        return 'Approved';
                      } 
                }
              }
           ]
         
       });
    }

    function pendingRto() {
      $("#approve-div").css('display','none');
      $("#pending-div").css('display','block');
      $("#hsrp-div").css('display','none');
      if(dataTable)
      {
        dataTable.destroy();
      }
      dataTable = $('#pending_table').DataTable({
         "processing": true,
         "serverSide": true,
         "ajax": "/admin/rto/list/api/0",
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
              // { "data": "id" },
              { "data": "sale_no" },
              { "data": "main_type",
              "render": function (data, type, row) {
                    if (data) {
                        var str = data;
                        var string = str.replace(/_/g, ' ');
                        return string.charAt(0).toUpperCase() + string.slice(1);
                      } 
                      else{
                        return '';
                      }
                }
              },
              { "data": "rto_finance" },
              { "data": "rto_amount" },
              { "data": "application_number" },
              { "data": "approve",
              "render": function (data, type, row) {
                    if (data == 0) {
                        return 'Pending';
                      }else if(data == '2'){
                        return 'Rejected';
                      }else{
                        return 'Approved';
                      }
                }
              },
             {
                 "targets": [ -1 ],
                 "data":"id", "render": function(data,type,full,meta)
                 {
                   str = '';
                   if(full.application_number != null && full.application_number != '' && full.application_number != undefined ){
                    str = str+'<a href="#"><button class="btn btn-info btn-xs "  onclick="openModal(this)" rto_id="'+data+'" > Approve/Reject</button></a> ';
                   }
                   return str;
                 },
                 "orderable": false
             }
           ]
         
       });
    }

    function pendingHsrp() {
      $("#approve-div").css('display','none');
      $("#pending-div").css('display','none');
      $("#hsrp-div").css('display','block');
      if(dataTable)
      {
        dataTable.destroy();
      }
      dataTable = $('#hsrp_table').DataTable({
         "processing": true,
         "serverSide": true,
         "ajax": "/admin/rto/list/hsrp/api",
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
              // { "data": "id" },
              { "data": "name" },
              { "data": "main_type"},
              { "data": "mobile" },
              { "data": "frame" },
              { "data": "hsrp_type" },
              { "data": "fueltype" },
              { "data": "vechicle_type" },
              { "data": "rto_amount" },
              { "data": "application_number" },
              { "data": "approve",
              "render": function (data, type, row) {
                    if (data == 0) {
                        return 'Pending';
                      }else if(data == '2'){
                        return 'Rejected';
                      }else{
                        return 'Approved';
                      }
                }
              },
             {
                 "targets": [ -1 ],
                 "data":"id", "render": function(data,type,full,meta)
                 {
                   str = '';
                   if(full.approve == 0){
                    str = str+'<a href="#"><button class="btn btn-info btn-xs "  onclick="openModal(this)" rto_id="'+data+'" > Approve/Reject</button></a> ';
                   }
                   return str;
                 },
                 "orderable": false
             }
           ]
         
       });
    }
   pendingRto();

     
      $("#approve").on('click',function(){
        $(this).css('background', 'rgb(135, 206, 250)');
        $("#pending").css('background', '#fff');
        $("#hsrp").css('background', '#fff');
        $(this).addClass('btn-color-active');
        approveRto();
      });

      $("#pending").on('click',function(){
        $(this).css('background', 'rgb(135, 206, 250)');
        $("#approve").css('background', '#fff');
        $("#hsrp").css('background', '#fff');
        $(this).addClass('btn-color-active');
        pendingRto();
      });

      $("#hsrp").on('click',function(){
        $(this).css('background', 'rgb(135, 206, 250)');
        $("#approve").css('background', '#fff');
        $("#pending").css('background', '#fff');
        $(this).addClass('btn-color-active');
        pendingHsrp();
      });

   });

   $("#RejectModal").on('click',function(){
    
     var rto_id = parseInt($("#modal-body").find('#modal-rto_id').val());
    if(rto_id){
        reject_fun(rto_id);
    }
   });

   function reject_fun(rto_id){
        $("#loader").show();
        var CSRF = $("meta[name='csrf-token']").attr('content');
        $.ajax({
              url:'/admin/rto/list/reject',
              data:{'_token':CSRF,'rto_id':rto_id},
              method:'POST',
              success:function(res){
                if(res.type == 'success') {
                  $("#rtoModal").modal("hide");
                  $("#js-msg-success").text("Successfully Rejected.").show();
                  if (res.main_type == 'hsrp' || res.main_type == 'new') {
                        $('#hsrp_table').dataTable().api().ajax.reload();
                      }else{
                        $('#pending_table').dataTable().api().ajax.reload();
                      }
                }
              },  
              error:function(error){
                // console.log(error);
                $("#loader").hide();
                if(error.responseJSON.hasOwnProperty('message')){
                  $("#modal-error").text(error.responseJSON.message).show();
                }else{
                  $("#modal-error").text(error.responseText).show();
                }
              }
        }).done(function(){
              $("#loader").hide();
        });
   }


</script>

@endsection
@section('main_section')
<section class="content">
   <div id="app">
    @include('admin.flash-message')
    <div class="alert alert-danger" id="js-msg-error" style="display: none">
    </div>
    <div class="alert alert-success" id="js-msg-success" style="display: none">
    </div>
    @yield('content')
    <!-- Default box -->
    <div class="box box-primary">
        <!-- /.box-header -->
        <div class="box-header with-border">
          <ul class="nav nav1 nav-pills"> 
            <li class="nav-item">
              <button class="nav-link1 btn-color-active" id="pending" >RTO Pending List</button>
            </li>
            <li class="nav-item">
              <button class="nav-link1 btn-color-anactive" id="approve" >RTO Approved List </button>
            </li> 
            <li class="nav-item">
              <button class="nav-link1 btn-color-anactive" id="hsrp" >New Correction </button>
            </li> 
          </ul>
        </div>
        <div id="approve-div" class="box-body">
          <table id="approve_table" class="table table-bordered table-striped">
              <thead>
                <tr>
                    {{-- <th>Id</th> --}}
                    <th>{{__('hirise.sale_no')}}</th>
                    <th>{{__('hirise.rto_type')}}</th>                    
                    <th>{{__('hirise.rto_finance')}}</th>                    
                    <th>{{__('hirise.rto_amount')}}</th>                    
                    <th>{{__('hirise.rto_app_no')}}</th> 
                    <th>Status</th>
                </tr>
              </thead>
              <tbody>
              </tbody>
          </table>
        </div> 
        <div id="pending-div" class="box-body" style="display:none;">
          <table id="pending_table" class="table table-bordered table-striped">
              <thead>
                <tr>
                    {{-- <th>Id</th> --}}
                    <th>{{__('hirise.sale_no')}}</th>
                    <th>{{__('hirise.rto_type')}}</th>                    
                    <th>{{__('hirise.rto_finance')}}</th>                    
                    <th>{{__('hirise.rto_amount')}}</th>                    
                    <th>{{__('hirise.rto_app_no')}}</th>                    
                    <th>Status</th>
                    <th>Action</th> 
                </tr>
              </thead>
              <tbody>
              </tbody>
          </table>
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
                    <th>{{__('Fuel Type')}}</th>                    
                    <th>{{__('Vehicle Type')}}</th>                    
                    <th>{{__('hirise.rto_amount')}}</th>                    
                    <th>{{__('hirise.rto_app_no')}}</th>                    
                    <th>Status</th>
                    <th>Action</th> 
                </tr>
              </thead>
              <tbody>
              </tbody>
          </table>
        </div>
        <!-- /.box-body -->
    </div>

     <!--Update Modal -->
    <div class="modal fade" id="rtoModal" tabindex="-1" role="dialog" aria-labelledby="rtoModalTitle" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="margin-top: 20px!important;">
          <div class="modal-header">
            <h4 class="modal-title" id="rtoModalTitle">RTO Information</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body" id="modal-body">
            <div class="alert alert-danger" id="modal-error">
            </div>
            <div class="alert alert-success" id="modal-success">
            </div>
            <input type="hidden" id="modal-rto_id" value="">
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
                  <label>Store Name </label>
                </div>
                <div class="col-md-6">
                  <label class="text-label store_name"></label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="col-md-6">
                  <label>Customer Name </label>
                </div>
                <div class="col-md-6">
                  <label class="text-label customer_name"></label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="col-md-6">
                  <label class="relation_name_label">S/O </label>
                </div>
                <div class="col-md-6">
                  <label class="text-label relation_name"></label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="col-md-6">
                  <label>Address </label>
                </div>
                <div class="col-md-6">
                  <label class="text-label address"></label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="col-md-6">
                  <label>Location </label>
                </div>
                <div class="col-md-6">
                  <label class="text-label location_name"></label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="col-md-6">
                  <label>Mobile # </label>
                </div>
                <div class="col-md-6">
                  <label class="text-label mobile"></label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="col-md-6">
                  <label>RTO Type </label>
                </div>
                <div class="col-md-6">
                  <label class="text-label rto_type"></label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="col-md-6">
                  <label>RTO Finance </label>
                </div>
                <div class="col-md-6">
                  <label class="text-label rto_finance"></label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="col-md-6">
                  <label>Financer Name </label>
                </div>
                <div class="col-md-6">
                  <label class="text-label financer_name"></label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="col-md-6">
                  <label>RTO Amount </label>
                </div>
                <div class="col-md-6">
                  <label class="text-label rto_amount"></label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="col-md-6">
                  <label>RTO Application # </label>
                </div>
                <div class="col-md-6">
                  <label class="text-label rto_application"></label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="col-md-6">
                  <label>Model Name </label>
                </div>
                <div class="col-md-6">
                  <label class="text-label model_name"></label>
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
              <div class="col-md-6">
                <div class="col-md-6">
                  <label>Engine # </label>
                </div>
                <div class="col-md-6">
                  <label class="text-label engine"></label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="col-md-6">
                  <label>Ex-Showroom Price </label>
                </div>
                <div class="col-md-6">
                  <label class="text-label ex_showroom_price"></label>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" id="ApproveModal" class="btn btn-info">Approve</button>
            <button type="button" id="RejectModal" class="btn btn-danger">Reject</button>
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          </div>
          
        </div>
      </div>
    </div>
  
   </div>
   <!-- /.box -->
</section>
@endsection