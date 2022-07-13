@extends($layout)
@section('title', __('Part Receive List'))
@section('breadcrumb')
<li><a href="#"><i class=""></i></a></li>
@endsection
@section('css')
<link rel="stylesheet" href="/css/responsive.bootstrap.css">
<style>
   tr.dangerClass{background-color: #f8d7da !important;}
   tr.successClass{background-color: #d4edda !important;}
   tr.worningClass{background-color: #fff3cd !important;}
</style>
@endsection
@section('js')
<script src="/js/dataTables.responsive.js"></script>
<script>
   var dataTable;
   $('#js-msg-error').hide();
   $('#js-msg-success').hide();
   $(document).ready(function() {
     dataTable = $('#table').DataTable({
         "processing": true,
         "serverSide": true,
         "ajax": "/admin/accidental/part/receive/list/api",
         "aaSorting": [],
         "responsive": true,
         "columns": [
             { "data": "tag"},
             { "data": "part_number"},
             { "data": "part_name"},
             { "data": "qty"},
             { "data": "price",
               "render": function(data,type,full,meta)
                 {
                     return data*full.qty;
                 },
             },
             { "data": "approved",
               "render": function(data,type,full,meta)
                 {
                  var approved = ((data == '0')? 'No' : ((data == '1')? 'Yes':''));
                    return approved;
                 },
             },
            { "data": "part_return_status",
             "render": function(data,type,full,meta)
                 {
                    var status = ((data == 'return')? 'Return' : ((data == 'received')? 'Received':''));
                    return status;
                 }
             },
             {
                 "targets": [ -1 ],
                 "data":"id", "render": function(data,type,full,meta)
                 {
                    var str = '';if (full.part_return_status != 'received' && full.part_return_status != null){
                      str = str+' <a href="#"><button class="btn btn-success btn-xs "  onclick="PartReceive(this)"><i class="'+data+'"></i>Part Receive</button></a>';
                    }
                    return str;
                 },
                 "orderable": false
             }
           ]
       });
   });

  function PartReceive(el) {
      var id = $(el).children('i').attr('class');
      var confirm1 = confirm('Are You Sure, You Want to receive part ?');
      if(confirm1)
      {
        $.ajax({  
                url:"/admin/accidental/part/receive/"+id,  
                method:"get",  
                success:function(result){
                 if(result.trim() == 'success') {
                  $('#js-msg-success').html('Part received successfully .').show();
                  $('#table').dataTable().api().ajax.reload();
               } else if(result.trim() != 'error') {
                  $('#js-msg-error').html(result).show();
               }else{
                  $('#js-msg-error').html("Something Wen't Wrong !").show();
               }
            },
            error:function(error){
               $('#js-msg-error').html("Something Wen't Wrong !").show();
            }     
           });
      }
    }
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
                  <th>Part Number</th>
                  <th>Part Name</th>
                  <th>Quantity</th>
                  <th>Price</th>
                  <th>Approve</th>
                  <th>Return Status</th>
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