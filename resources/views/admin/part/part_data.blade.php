@extends($layout)

@section('title', __('Parts List'))

@section('breadcrumb')
    <li><a href="#"><i class=""></i>Parts Summary</a></li>
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
$('#js-msg-errors').hide();
   $('#js-msg-success').hide();
    // Data Tables
    $(document).ready(function() {

      $('.timepicker').wickedpicker({
            twentyFour:true,
            // show:null
        });

      $("#consumption_level").select2({width: 'resolve'});
      dataTable = $('#admin_table').DataTable({
          "processing": true,
          "serverSide": true,
          "ajax": "/admin/part/list/api",
          "aaSorting": [],
          "responsive": true,
          "columns": [
              { "data": "id" },
              { "data": "name" },
              { "data": "part_number" },
              { "data": "type" },
              { "data": "consumption_level" },
              { "data": "price" },
              { "data": "frt" },
              {
                  "targets": [ -1 ],
                  "data":"id", "render": function(data,type,full,meta)
                  {
                    
                       return '<a href="#" id="'+data+'" class="update"><button data-toggle="modal" data-target="#partModalUpdate" class="btn btn-success btn-xs "> Update</button></a> &nbsp;'  ;
                  },
                  "orderable": false
              }
            ],
            "columnDefs": [
            ]
          
        });
    });

 $(document).on('click','.update',function(){

    $('#js-msg-errors').hide();
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
              var frt=result.frt;
                  $('#consumption_level').val(data).trigger('change');
                  $('#frt_time').val(frt);
            },
            error:function(error){
              $('#js-msg-error').html("Something Wen't Wrong "+error).show();
            },

           });
        }

 });   

$(document).on('click','#updatebtn',function(){

   var consumption_level = $('#consumption_level :selected').val();
   var  frt_time= $('.frt_time').val();
   var part_id=$("#partid").val();
  if(!frt_time){

    $('#js-msg-errors').html("FRT(In Hours) Required ").show();
  }

   if(consumption_level && part_id && frt_time)
   {
      $.ajax({
            url:'/admin/part/consumption/updatedb',
            data:{'partId':part_id,'consumption_level':consumption_level,'frt_time':frt_time},
            method:'GET',
            success:function(result){
              if(result.trim() == 'success')
               {
                 $('#partModalUpdate').modal("hide"); 
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
                    {{-- <h3 class="box-title">{{__('customer.mytitle')}} </h3> --}}
                    
                </div>  
                <div class="box-body">

                  <table id="admin_table" class="table table-bordered table-striped">
                    <thead>
                    <tr>
                      <th>{{__('parts.id')}}</th>
                      <th>{{__('parts.name')}}</th>
                      <th>{{__('parts.part')}}</th>                     
                      <th>{{__('parts.type')}}</th>                     
                      <th>{{__('parts.consumption_level')}}</th>                     
                      <th>{{__('parts.price')}}</th>                     
                      <th>{{__('parts.frt')}}</th>                     
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

         <div class="modal fade" id="partModalUpdate" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content" style="margin-top: 200px!important;">
        <form id="my-form" method="POST" onsubmit="return false">
            @csrf
            <input type="hidden" name="partid" id="partid" value="">
        <div class="modal-header">
          <h4 class="modal-title" id="exampleModalLongTitle">Update Consumption Level</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button><br/>
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
           </div><br/>
           <div class="row">
             <div class="col-md-4">
                    <label> FRT(In Hours)<sup>*</sup></label>
              </div>
              <div class="col-md-5">
                   <input type="number" min="1" name="frt_time" id="frt_time" class="input-css frt_time">
                        {!! $errors->first('frt_time', '<p class="help-block">:message</p>') !!}
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