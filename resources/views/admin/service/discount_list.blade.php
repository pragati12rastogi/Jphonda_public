@extends($layout)
@section('title', __('Discount List'))
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

  $("#js-msg-success").hide();
  $("#js-msg-error").hide();
   var dataTable;
 
  function datatablefn() {

    if(dataTable){
      dataTable.destroy();
    }

     dataTable = $('#table').DataTable({
         "processing": true,
         "serverSide": true,

         "ajax": {
            "url": "/admin/service/discount/list/api",
            "datatype": "json",
                "data": function (data) {
                    var tag = $('#tag').val();
                    data.tag = tag;
                }
            },
         "aaSorting": [],
         "responsive": true,
         "columns": [
             { "data": "tag"},
             { "data": "frame"},
             { "data": "registration"},
             { "data": "charge_type"},
             { "data": "sub_type"},
             { "data":  "amount" },
             { "data":  "status" },
             {
                 "targets": [ -1 ],
                 "data":"id", "render": function(data,type,full,meta)
                 {
                    var str = '';
                    if(full.status == 'pending')
                    {
                      str = str+'<a href="#" id="'+data+'" class="Approve"><button class="btn btn-info btn-xs">Approve</button></a> &nbsp;';
                    }
                    
                     return str;
                 },
                 "orderable": false
             }
           ]
       });
  
 }

      $(document).on('click','.Approve',function(){
      var charge_id = $(this).attr('id');
      $('#js-msg-error').hide();
      $('#js-msg-success').hide();
      var confirm1 = confirm('Are You Sure, You want to approve ?');
      if(confirm1)
      {
         $.ajax({
            url:'/admin/service/discount/approve',
            data:{'id':charge_id},
            method:'GET',
            success:function(result){
               if(result.type == 'success')
               {  
                $('#js-msg-success').show();
                  $('#js-msg-success').html(result.msg);
                  $('#table').dataTable().api().ajax.reload();
               }
               else{
                  $('#js-msg-error').html(result.msg).show();
               }
            },
            error:function(error){
               $('#js-msg-error').html("Something Wen't Wrong ").show();
            }
         }); 
      }
      
   });

  $(document).ready(function() {
    datatablefn();
  });
  
   $('#tag').on( 'change', function () {
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
      <div class="row">
   <div class="box box-primary">
      <div class="box-body">
         @include('admin.flash-message')
         @yield('content')
           <div class="row">
              <div class="col-md-3">
                 <label>Tag</label>
                 @if(isset($jobCard))
                    <select name="tag" class="input-css select2 selectValidation" id="tag">
                       <option value="" selected="" disabled="">Select Tag</option>
                       @foreach($jobCard as $key => $val)
                       <option value="{{$val['tag']}}" {{(old('tag') == $val['tag']) ? 'selected' : '' }}> {{$val['tag']}} </option>
                       @endforeach
                    </select>
                 @endif
                 {!! $errors->first('tag', '
                 <p class="help-block">:message</p>
                 ') !!}
              </div>
          </div>
         <table id="table" class="table table-bordered table-striped">
            <thead>
               <tr>
                  <th>Tag #</th>
                  <th>Frame #</th>
                  <th>Registration #</th>
                  <th>Charge Type</th>
                  <th>Sub Type</th>
                  <th>Amount</th>
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
   <!-- /.box -->
</section>
@endsection