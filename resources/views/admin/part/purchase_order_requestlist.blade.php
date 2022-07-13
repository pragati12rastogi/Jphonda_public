@extends($layout)

@section('title', __('parts.requistion_purchase'))

@section('breadcrumb')
    <li><a href="#"><i class=""></i>Parts Requistion Purchase Summary</a></li>
@endsection
@section('css')
<link rel="stylesheet" href="/css/responsive.bootstrap.css">  
<style>
   .modal-header .close {
    margin-top: -33px;
}
#consumption_level {
    width: 200px;       
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
            "url": "/admin/part/requisition/purchase/list/api",
            "datatype": "json",
                "data": function (data) {
                    var store_name = $('#store_name').val();
                    data.store_name = store_name;
                }
            },
          "columns": [
              { "data": "model_name" },
              { "data": "color" },
              { "data": "status" },
             

              {
                  "targets": [ -1 ],
                  "data":"id", "render": function(data,type,full,meta)
                  {
                    
                       return '<a href="#" id="'+data+'"><button class="btn btn-success btn-xs "> Update</button></a> &nbsp;'  ;
                  },
                  "orderable": false
              }
            ]
       });
}

 // Data Tables'additional_services.id',
   $(document).ready(function() {
     $("#consumption_level").select2({width: 'resolve'});
    datatablefn();
  });
   $('#store_name').on( 'change', function () {
      dataTable.draw();
    });


 $(document).on('click','.update',function(){

     var part_id=$(this).attr('id');
     $("#partid").val(part_id);
     if(part_id)
        {
           $.ajax({
            url:'/admin/part/consumption/update',
            data:{'partId':part_id},
            method:'GET',
            dataType:'json',
            success:function(result){
              var data=result.consumption_level;
                  $('#consumption_level').val(data).trigger('change');
            },
            error:function(error){
              $('#js-msg-error').html("Something Wen't Wrong "+error).show();
            },

           });
        }

 });   

$(document).on('click','#updatebtn',function(){

   var consumption_level = $('#consumption_level :selected').val();
   var part_id=$("#partid").val();

   if(consumption_level && part_id)
   {
      $.ajax({
            url:'/admin/part/consumption/updatedb',
            data:{'partId':part_id,'consumption_level':consumption_level},
            method:'GET',
            success:function(result){
              if(result.trim() == 'success')
               {
                 $('#partconsumptionModalUpdate').modal("hide"); 
                  $('#js-msg-success').html('Successfully Update Consumption Level.').show();
                  $('#admin_table').dataTable().api().ajax.reload();
               }
               else if(result.trim() != 'error')
               {
                  $('#js-msg-error').html(result).show();
               }
               else{
                  $('#js-msg-error').html("Something Wen't Wrong").show();
               }
            },
            error:function(error){
              $('#js-msg-error').html("Something Wen't Wrong "+error).show();
            },
           });
  }


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
                <div class="box-body">
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
                      <th>{{__('parts.model_name')}}</th>
                      <th>{{__('parts.part_color')}}</th>                                      
                      <th>{{__('parts.status')}}</th>    
                      <th>{{__('parts.action')}}</th>                     
                    </tr>
                    </thead>
                    <tbody>
                      
                    </tbody>
               
                  </table>
           
                </div>
                <!-- /.box-body -->
              </div>
        <!-- /.box -->

         <div class="modal fade" id="partconsumptionModalUpdate" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content" style="margin-top: 200px!important;">
        <form id="my-form" method="POST" onsubmit="return false">
            @csrf
            <input type="hidden" name="partid" id="partid" value="">
        <div class="modal-header">
          <h4 class="modal-title" id="exampleModalLongTitle">Update Consumption Level</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
          <div class="alert alert-danger" id="js-msg-errors">
         </div>
        </div>
        <div class="modal-body">
           <div class="row">
             <div class="col-md-4">
                    <label>Consumption Level<sup>*</sup></label>
              </div>
              <div class="col-md-8">
                   <select name="consumption_level" id="consumption_level" class="input-css select2 consumption_level">
                            <option value="" disabled="">Select Consumption Level</option>
                            <option value="Fast">Fast</option>
                            <option value="Medium">Medium</option>
                            <option value="Slow">Slow</option>
                        </select>
              </div>
           </div>
              
       </div>
        <div class="modal-footer">
          <button type="button"  class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="button" id="updatebtn" class="btn btn-success">Submit</button>
        </div>
        </form>
      </div>
    </div>
  </div>
      </section>
@endsection