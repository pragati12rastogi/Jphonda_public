@extends($layout)
@section('title', __('Update Service Part Tag'))
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
   $('#js-msg-verror').hide();
   $('#js-msg-success').hide();
     
  function UpdateTag(el) {
      var id = $(el).children('i').attr('class');
      if(id) {
        $.ajax({
        method: "GET",
        url: "/admin/service/part/tag/get",
        data: {'id':id},
        success:function(data) {
          $("#id").val(id);
          $("#tag").val(data.tag);
          $('#partModalCenter').modal("show"); 
        }
      });
         
      }
    }


   $('#btnUpdate').on('click', function() {
      var id = $('#id').val();
      var tag = $("#tag").val();
 
       $.ajax({
        method: "GET",
        url: "/admin/service/tag/update",
        data: {'id':id,'tag':tag},
        success:function(data) {
            if (data == 'success') {
                $('#partModalCenter').modal("hide");    
                $("#js-msg-success").html('Tag updated succefully !');
                $("#js-msg-success").show();
               setTimeout(function() {
                 location.reload();
                }, 2000);
            }if(data == 'error'){
                $('#partModalCenter').modal("hide");  
                $("#js-msg-error").html('Something went wrong !');
                $("#js-msg-error").show();
               setTimeout(function() {
                 $('#js-msg-error').fadeOut('fast');
                }, 4000);
            }if(data == 'verror'){ 
                $("#js-msg-verror").html('Field is require !');
                $("#js-msg-verror").show();
               setTimeout(function() {
                 $('#js-msg-verror').fadeOut('fast');
                }, 4000);
            }
        },
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
        {{-- <h3 class="box-title">{{__('customer.mytitle')}} </h3> --}}
      </div>
      
      <div class="box-body">
         @include('admin.flash-message')
         @yield('content')
         <table id="table" class="table table-bordered table-striped">
            <thead>
               <tr>
                  <th>Tag #</th>
                  <th>Job Card Type</th>
                  <th>Part Number</th>
                  <th>Part Name</th>
                  <th>Quantity</th>
                  <th>Price</th>
                  <th>Tampere</th>
                  <th>Warranty Type</th>
                  <th>Warranty Tag</th>
                  <th>{{__('factory.action')}}</th>
               </tr>
            </thead>
            <tbody>
              @foreach($partdata as $data)
                <tr>
                  <td>{{$data->tag}}</td>
                  <td>{{$data->job_card_type}}</td>
                  <td>{{$data->part_no}}</td>
                  <td>{{$data->part_name}}</td>
                  <td>{{$data->qty}}</td>
                  <td>{{$data->qty*$data->price}}</td>
                  <td>{{$data->tampered}}</td>
                  <td>{{$data->warranty_type}}</td>
                  <td>{{$data->part_tag}}</td>
                  <td>
                    <a href="#"><button class="btn btn-info btn-xs " onclick="UpdateTag(this)"><i class="{{$data->id}}"></i>Update Tag</button></a>

                  </td>
                </tr>
              @endforeach
            </tbody>
         </table>
      </div>
      <!-- /.box-body -->
   </div>
   <!-- Modal -->
    <div class="modal fade" id="partModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="margin-top: 200px!important;">
          <div class="modal-header">
            <h4 class="modal-title" id="exampleModalLongTitle">Update Tag</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">                   
            <form id="my-form"  method="POST" >
                <div class="alert alert-danger" id="js-msg-verror">
              </div>
              @csrf
              <input type="hidden" name="id" id="id">
              <div class="row">
                <div class="col-md-6">
              <label for="advance">Tag<sup>*</sup></label>
               <input type="text" name="part_tag" class="input-css" id="tag">                      
           </div>
         </div><br>
          </div>
          <div class="modal-footer">
            <button type="button" id="btnCancel" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="button" id="btnUpdate" class="btn btn-success">Update</button>
          </div>
          </form>
        </div>
      </div>
    </div>
   <!-- /.box -->
</section>
@endsection