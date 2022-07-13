@extends($layout)
@section('title', __('Part Issue List'))
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
</style>
@endsection
@section('js')
<script src="/js/dataTables.responsive.js"></script>
<script>
   var dataTable;
   $('#js-msg-error').hide();
   $('#js-msg-success').hide();
   
   // Data Tables'additional_services.id',
   $(document).ready(function() {
     dataTable = $('#table').DataTable({
         "processing": true,
         "serverSide": true,
         "ajax": "/admin/service/part/issue/list/api",
         "aaSorting": [],
         "responsive": true,
         "columns": [
             { "data": "tag"},
             { "data": "no_of_part"},
             { "data": "job_card_type"},
             { "data": "service_status"},
             // { "data": "service_out_time"},
             {
                 "targets": [ -1 ],
                 "data":"id", "render": function(data,type,full,meta)
                 {
                    var str = '';
                       str = str+' <a href="/admin/service/part/issue/'+data+'" ><button class="btn btn-info btn-xs ">Part Issue</button></a>';
                     return str;
                 },
                 "orderable": false
             }
           ]
       });
   });

  $(document).on('click','.PartIssue',function(){
      var getJobCardId = $(this).attr('id');
      // console.log('getJobCardId',getJobCardId);
      $('#js-msg-error').hide();
      $('#js-msg-success').hide();
      var confirm1 = confirm('Are You Sure, You Want to issue all part ?');
      if(confirm1)
      {
         $.ajax({
            url:'/admin/accidental/part/issue',
            data:{'JobCardId':getJobCardId},
            method:'GET',
            success:function(result){
               if(result.trim() == 'success') {
                  $('#js-msg-success').html('All part issue successfully .').show();
                  $('#table').dataTable().api().ajax.reload();
               } else if(result.trim() != 'error') {
                  $('#js-msg-error').html(result).show();
               }else{
                 $('#js-msg-error').html("Something Wen't Wrong ").show();
               }
            },
            error:function(error){
               $('#js-msg-error').html("Something Wen't Wrong ").show();
            }
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
   <div class="box box-primary">
      <!-- /.box-header -->
      <div class="box-header with-border">
        {{-- <h3 class="box-title">{{__('customer.mytitle')}} </h3> --}}
      </div>
      
      <div class="box-body">
         @include('admin.flash-message')
         @yield('content')
         <table id="table" class="table table-bordered table-striped">
            <thead>
               <tr>
                  <th>Tag #</th>
                  <th>Number of Part's</th>
                  <th>Job card type</th>
                  <th>Service Status</th>
                  <!--<th>Service Out Time</th> -->
                  <th>{{__('factory.action')}}</th>
               </tr>
            </thead>
            <tbody>
            </tbody>
         </table>
      </div>
      <!-- /.box-body -->
   </div>
   <!-- Modal -->
   <!-- /.box -->
</section>
@endsection