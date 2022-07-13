@extends($layout)
@section('title', __('Part Warranty List'))
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
   $('#type').hide();
    $("#year").hide();
     
  function PartWarranty(el) {
      var id = $(el).children('i').attr('class');
      if(id) {
         $("#id").val(id);
         $('#warrantyModalCenter').modal("show"); 
      }
    }

     $(document).ready(function(){
        $("input[type='radio']").click(function(){
            var radioValue = $("input[name='warranty']:checked").val();
            if(radioValue){
                if (radioValue == 'no') {
                  $("#type").show();
                }else{
                  $("#type").hide();
                  $("#year").hide();
                }
            }
        });
    });


    $(document).ready(function(){
      $("#warranty_type").change(function(){
            var type = $("#warranty_type").val();
            if(type){
                if (type == 'Extended') {
                  $("#year").show();
                }else{
                  $("#year").hide();
                }
            }
      });
    });




   $('#btnUpdate').on('click', function() {
      var id = $('#id').val();
       var radioValue = $("input[name='warranty']:checked").val();
            if(radioValue){
                if (radioValue == 'no') {
                   var warranty_type = $('#warranty_type').val();
                   if (warranty_type == 'Extended') {
                       var year = $('#extended_year').val();
                   }else{
                       var year = null;
                   }
                }else{
                  var warranty_type = null;
                }
            }
 

       $.ajax({
        method: "GET",
        url: "/admin/service/warranty/update",
        data: {'id':id,'radioValue':radioValue,'warranty_type':warranty_type,'year':year},
        success:function(data) {
            if (data == 'success') {
                $('#warrantyModalCenter').modal("hide");    
                $("#js-msg-success").html('Warranty added successfully !');
                $("#js-msg-success").show();
               setTimeout(function() {
                 location.reload();
                }, 4000);
            }if(data == 'error'){
                $('#warrantyModalCenter').modal("hide");  
                $("#js-msg-error").html('Something went wrong !');
                $("#js-msg-error").show();
               setTimeout(function() {
                 $('#js-msg-error').fadeOut('fast');
                }, 4000);
            }if(data == 'date'){
                $('#warrantyModalCenter').modal("hide");  
                $("#js-msg-error").html('Your extended warranty expired !');
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
                  <td>
                   @if($data->tampered == null) <a href="#"><button class="btn btn-info btn-xs " onclick="PartWarranty(this)"><i class="{{$data->id}}"></i>Add Warranty</button></a>@endif

                  </td>
                </tr>
              @endforeach
            </tbody>
         </table>
      </div>
      <!-- /.box-body -->
   </div>
   <!-- Modal -->
    <div class="modal fade" id="warrantyModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="margin-top: 200px!important;">
          <div class="modal-header">
            <h4 class="modal-title" id="exampleModalLongTitle">Add Warranty</h4>
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
              <label for="advance">Tampere<sup>*</sup></label>
               <input type="radio" name="warranty" id="warranty_yes" value="yes"> Yes   &nbsp;&nbsp; 
               <input type="radio" name="warranty" id="warranty_no" value="no"> No                      
           </div>
         </div><br>
         <div class="row">
                <div class="col-md-6" id="type">
                    <label>Warranty Type <sup>*</sup></label>
                    <select class="input-css select2" name="warranty_type" id="warranty_type" style="width:100%;"> 
                      <option value="" selected="" disabled="">Select Warranty Type</option>
                      <option value="Standard">Standard</option>
                      <option value="Extended">Extended</option>
                      <option value="Goodwill">Goodwill</option>
                      <option value="PUD">PUD</option>
                    </select>
                </div>
                <div class="col-md-6" id="year">
                    <label>Year <sup>*</sup></label>
                    <input type="number" name="year" id="extended_year" class="input-css" min="0">
                </div>
              </div>
          </div>
          <div class="modal-footer">
            <button type="button" id="btnCancel" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="button" id="btnUpdate" class="btn btn-success">Add</button>
          </div>
          </form>
        </div>
      </div>
    </div>
   <!-- /.box -->
</section>
@endsection