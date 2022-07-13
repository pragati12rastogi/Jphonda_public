@extends($layout)
@section('title', __('Booking List'))
@section('breadcrumb')
<li><a href="#"><i class=""></i></a></li>
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
.modal-header .close {
    margin-top: -33px;
}
   .wickedpicker {
       z-index: 1064 !important;
   } 
</style>
@endsection
@section('js')
<script src="/js/dataTables.responsive.js"></script>
<script>

  var dataTable;
    $('#js-msg-error').hide();
    $('#js-msg-success').hide();
   function datatablefn()
   {

    if(dataTable){
      dataTable.destroy();
    }
        
       dataTable = $('#admin_table').DataTable({
         "processing": true,
         "serverSide": true,
         "ajax": "",
         "aaSorting": [],
         "responsive": true,
          "ajax": {
            "url": "/admin/service/booking/list/api",
            "datatype": "json",
                "data": function (data) {
                    var store_name = $('#store_name').val();
                    data.store_name = store_name;
                }
            },
          "columns": [
                   { "data": "frame"},
                   { "data": "registration"},
                   { "data": "pickup_time"},
                   { "data": "drop_time"},
                   { "data": "name"},
                   { "data": "status"},
                   
                   {
                       "targets": [ -1 ],
                       "data":"pickup_id", "render": function(data,type,full,meta)
                       {

                        var str = '';
                            if (full.pickup_time== null) {

                              str = str+' <a href="#" id="'+data+'" class="pickup"><button class="btn btn-info btn-xs">Pickup</button></a> ';
                            }
                            if (full.drop_time== null){
                              if(full.pickup_time!= null)
                              {
                               str = str+' <a href="#" id="'+data+'" class="drop">&nbsp;&nbsp<button class="btn btn-danger btn-xs">Drop</button></a>';
                              }
                            }
                           return str;
                       },
                       "orderable": false
                   }
                 ]
       });
}


$(document).on('click','.pickup',function(e){

  var pickup_id=$(this).attr('id');
  $("#pickup_id").val(pickup_id);

  var confirm1=confirm('Are You Sure, You Want to Add Pickup Time ?');
       if(confirm1)
       {
            $.ajax({
            url:'/admin/service/booking/pickuptime',
            data:{'PickupId':pickup_id},
            method:'GET',
            success:function(result){
              // console.log(result);
               if(result.trim() == 'success'){
                  $('#js-msg-success').html('Pickup Time Successfully Add.').show();
               }else if(result.trim() != 'error'){
                  $('#js-msg-error').html(result).show();
               }else{
                  $('#js-msg-error').html("Something Wen't Wrong").show();
               }
               datatablefn();
            },
            error:function(error){
               $('#js-msg-errors').html("Something Wen't Wrong "+error).show();
            }
         });

       }
});

$(document).on('click','.drop',function(e){

  var pickup_id=$(this).attr('id');
  $("#pickup_id").val(pickup_id);
  var confirm2=confirm('Are You Sure, You Want to Add Drop Time ?');
       if(confirm2)
       {
          $.ajax({
              url:'/admin/service/booking/droptime',
              data:{'PickupId':pickup_id},
              method:'GET',
              success:function(result){
                // console.log(result);
                 if(result.trim() == 'success'){
                    $('#js-msg-success').html('Drop Time Successfully Add.').show();
                 }else if(result.trim() != 'error'){
                    $('#js-msg-error').html(result).show();
                 }else{
                    $('#js-msg-error').html("Something Wen't Wrong").show();
                 }
                 datatablefn();
              },
              error:function(error){
                 $('#js-msg-errors').html("Something Wen't Wrong "+error).show();
              }
           });
         }

});


   // Data Tables'additional_services.id',
   $(document).ready(function() {
    datatablefn();
  });
   $('#store_name').on( 'change', function () {
      dataTable.draw();
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
                        {{-- <h3 class="box-title">{{__('customer.mytitle')}} </h3> --}}

      </div>
      <div class="box-body">
         @include('admin.flash-message')
         @yield('content')
          <div class="row">
              <div class="col-md-3" style="{{(count($store) == 1)? 'display:none' : 'display:block'}};float: right;">
                 <label>Store Name</label>
                 @if(count($store) > 1)
                    <select name="store_name" class="input-css select2 selectValidation" id="store_name">
                       <option value="">Select Store</option>
                       @foreach($store as $key => $val)
                       <option value="{{$val['id']}}" {{(old('store_name') == $val['id']) ? 'selected' : '' }}> {{$val['name']}} </option>
                       @endforeach
                    </select>
                 @endif
                 @if(count($store) == 1)
                    <select name="store_name" class="input-css select2 selectValidation" id="store_name">
                       <option value="{{$store[0]['id']}}" selected>{{$store[0]['name']}}</option>
                    </select>
                 @endif
                 {!! $errors->first('store_name', '
                 <p class="help-block">:message</p>
                 ') !!}
              </div>
          </div>
         <table id="admin_table" class="table table-bordered table-striped">
            <thead>
               <tr>
                  <th>Frame #</th>
                  <th>Registration #</th>
                  <th>Pickup Time</th>
                  <th>Drop Time</th>
                  <th>User name</th>
                  <th>Status</th>
                  <th>{{__('factory.action')}}</th>
               </tr>
            </thead>
            <tbody>
            </tbody>
         </table>
      </div>
      <!-- /.box-body -->
   </div>
</section>
@endsection