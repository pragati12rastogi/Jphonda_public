@extends($layout)
@section('title', __('Service Pre Booking List'))
@section('breadcrumb')
<li><a href="#"><i class=""></i></a></li>
@endsection
@section('css')
<link rel="stylesheet" href="/css/responsive.bootstrap.css">

@endsection
@section('js')
<script src="/js/dataTables.responsive.js"></script>
<script>
   var dataTable;
     $('#js-msg-error').hide();
     $('#js-msg-verror').hide();
     $('#js-msg-success').hide(); 
  
    // function ServiceDone(el) {
    //   var id = $(el).children('i').attr('class');
    //   var confirm1 = confirm('Are You Sure, You Want to done service ?');
    //   if(confirm1)
    //   {
    //   $.ajax({
    //     method: "GET",
    //     url: "/admin/service/done",
    //     data: {'jobcard_id':id},
    //     success:function(data) {
    //         if (data == 'success') {
    //             $('#exampleModalCenter').modal("hide");  
    //             $('#table').dataTable().api().ajax.reload();
    //             $("#js-msg-success").html('Service done successfully!');
    //             $("#js-msg-success").show();
    //            setTimeout(function() {
    //              $('#js-msg-success').fadeOut('fast');
    //             }, 4000);
    //         }if(data == 'error'){
    //             $('#exampleModalCenter').modal("hide");  
    //             $("#js-msg-error").html('Something went wrong !');
    //             $("#js-msg-error").show();
    //            setTimeout(function() {
    //              $('#js-msg-error').fadeOut('fast');
    //             }, 4000);
    //         }
    //     },
    //   });
    //   }
    // }
   
   $(document).ready(function() {
     dataTable = $('#table').DataTable({
         "processing": true,
         "serverSide": true,
         "ajax": "/admin/service/pre/booking/list/api",
         "aaSorting": [],
         "responsive": true,
         "columns": [
             { "data": "name"},
             { "data": "mobile"},
             { "data": "status"},
             {
                 "targets": [ -1 ],
                 "data":"id", "render": function(data,type,full,meta)
                 {
                    var str = '';
                        str = str+' <a href="/admin/service/create/booking/'+data+'"><button class="btn btn-info btn-xs"><i class="'+data+'"></i>Booking </button></a> ';
                     return str;
                 },
                 "orderable": false
             }
           ]
       });
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
      </div>
      
      <div class="box-body">
         @include('admin.flash-message')
         @yield('content')
         <table id="table" class="table table-bordered table-striped">
            <thead>
               <tr>
                  <th>Name</th>
                  <th>Mobile</th>
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
   <!-- /.box -->
</section>
@endsection