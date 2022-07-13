@extends($layout)
@section('title', __('Part Request List'))
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
         "ajax": "/admin/service/store/supervisor/bay/list/api",
         "aaSorting": [],
         "responsive": true,
         "columns": [
             { "data": "id"},
             { "data": "tag"},
             { "data": "no_of_part"},
             { "data": "job_card_type"},
             { "data": "service_in_time"},
             { "data": "service_out_time"},
             {
                 "targets": [ -1 ],
                 "data":"id", "render": function(data,type,full,meta)
                 {
                    var str = '';
                  //   if(((full.service_in_time)? full.service_in_time : '').length == 0)
                  //   {
                  //     str = str+'<a href="#" id="'+data+'" class="serviceIn"><button class="btn btn-danger btn-xs">Service In</button></a> &nbsp;';
                  //   }
                  //   if(((full.service_out_time)? full.service_out_time : '').length == 0)
                  //   {
                  //       str = str+'<a href="#" id="'+data+'" class="serviceOut"><button class="btn btn-success btn-xs ">Service Out</button></a> &nbsp;';
                  //       str = str+"<a href='/admin/service/floor/supervisor/bay/assign/part/"+full.bay_allocation_id+"'><button class='btn btn-info btn-xs'>Part's Assign</button></a> &nbsp;";
                  //       if(full.customer_confirmation != 'yes' && full.part_count > 0)
                  //       {
                  //          str = str+'<a href="#" id="'+data+'" class="customer_confirmation"><button class="btn btn-primary btn-xs ">Customer Confirmation</button></a> &nbsp;';
                  //       }
                  //    }
                     str = str+'<a href="/admin/service/store/supervisor/bay/assign/part/'+data+'" class="assign_bay"><button class="btn btn-primary btn-xs ">Part Assign</button></a> &nbsp;';
                    

                     return str;
                 },
                 "orderable": false
             }
           ]
       });
   });

   // $(document).on('click','.serviceIn',function(){
   //    var getJobCardId = $(this).attr('id');
   //    // console.log('getJobCardId',getJobCardId);
   //    $('#js-msg-error').hide();
   //    $('#js-msg-success').hide();
   //    var confirm1 = confirm('Are You Sure, You Want to Service In ?');
   //    if(confirm1)
   //    {
   //       $.ajax({
   //          url:'/admin/service/floor/supervisor/buy/work/start',
   //          data:{'JobCardId':getJobCardId},
   //          method:'GET',
   //          success:function(result){
   //             if(result.trim() == 'success')
   //             {
   //                $('#js-msg-sucess').html('Successfully Service In.').show();
   //                $('#table').dataTable().api().ajax.reload();

   //             }
   //             else if(result.trim() != 'error')
   //             {
   //                $('#js-msg-error').html(result).show();
   //             }
   //             else{
   //                $('#js-msg-error').html("Something Wen't Wrong").show();
   //             }
   //             // console.log('result',result);
   //          },
   //          error:function(error){
   //             $('#js-msg-error').html("Something Wen't Wrong "+error).show();
   //          }
   //       });
   //    }
      
   // });

   // $(document).on('click','.serviceOut',function(){
   //    var getJobCardId = $(this).attr('id');
   //    // console.log('getJobCardId',getJobCardId);
   //    $('#js-msg-error').hide();
   //    $('#js-msg-success').hide();
   //    var confirm1 = confirm('Are You Sure, You Want to Service Out ?');
   //    if(confirm1)
   //    {
   //       $.ajax({
   //          url:'/admin/service/floor/supervisor/buy/work/end',
   //          data:{'JobCardId':getJobCardId},
   //          method:'GET',
   //          success:function(result){
   //             if(result.trim() == 'success')
   //             {
   //                $('#js-msg-sucess').html('Successfully Service Out.').show();
   //                $('#table').dataTable().api().ajax.reload();
   //             }
   //             else if(result.trim() != 'error')
   //             {
   //                $('#js-msg-error').html(result).show();
   //             }
   //             else{
   //                $('#js-msg-error').html("Something Wen't Wrong").show();
   //             }
   //             // console.log('result',result);
   //          },
   //          error:function(error){
   //             $('#js-msg-error').html("Something Wen't Wrong "+error).show();
   //          }
   //       });
   //    }
      
   // });



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
                <th>id</th>
                  <th>Tag #</th>
                  <th>Number of Part's</th>
                  <th>Jobcard Type</th>
                  <th>Service In Time</th>
                  <th>Service Out Time</th>
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