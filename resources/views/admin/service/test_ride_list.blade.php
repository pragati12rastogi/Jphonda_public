@extends($layout)
@section('title', __('Test Ride List'))
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
     $('#js-msg-verror').hide();
   $('#js-msg-success').hide();
    function TestRideOut(el) {
      var id = $(el).children('i').attr('class');
      // $.ajax({  
      //           url:"/admin/service/test/ride/get/"+id,  
      //           method:"get",  
      //           success:function(data){
      //            if(data) {
                  $("#jobcard_id").val(id);
                  $('#rideModalCenter').modal("show"); 
           //        }
           //      }  
           // });
    }

    $('#btnSubmit').on('click', function() {
      var id = $('#jobcard_id').val();
      var test_ride_type = $('#test_ride_type').val();
      
       $.ajax({
        method: "GET",
        url: "/admin/service/test/ride",
        data: {'jobcard_id':id,'type':test_ride_type},
        success:function(data) {
            if (data == 'success') {
                $('#rideModalCenter').modal("hide");  
                $('#table').dataTable().api().ajax.reload();
                $("#js-msg-success").html('Test ride out succefully !');
                $("#js-msg-success").show();
               setTimeout(function() {
                 $('#js-msg-success').fadeOut('fast');
                }, 4000);
            }if(data == 'error'){
                $('#rideModalCenter').modal("hide");  
                $("#js-msg-error").html('Something went wrong !');
                $("#js-msg-error").show();
               setTimeout(function() {
                 $('#js-msg-error').fadeOut('fast');
                }, 4000);
            }
            if (data == 'verror') {
              $("#js-msg-verror").html('Field is require !');
              $("#js-msg-verror").show();
            }
            if (data == 'found') {
              $("#js-msg-verror").html('This Type of test ride already added !');
              $("#js-msg-verror").show();
            }
        },
      });
        $("#js-msg-verror").hide();
        $('#my-form')[0].reset(); 
    });

    $('#btnCancel').on('click', function() {
      $('#my-form')[0].reset(); 
       $("#js-msg-verror").hide();
    });


    function TestRideIn(el){
      var id = $(el).children('i').attr('class');
       if (confirm('Are you sure ?')) {
      $.ajax({  
                url:"/admin/service/test/ride/in/"+id,  
                method:"get",  
                success:function(data){ 
                 if(data) {
                   if(data == 'error')
                    {
                      $("#js-msg-error").html('Something went wrong !');
                      $("#js-msg-error").show();
                      setTimeout(function() {
                       $('#js-msg-error').fadeOut('fast');
                     }, 4000);                      
                    }else{
                      $('#table').dataTable().api().ajax.reload();
                      $("#js-msg-success").html('In !');
                      $("#js-msg-success").show();
                     setTimeout(function() {
                       $('#js-msg-success').fadeOut('fast');
                      }, 4000);
                      
                    }
                  }
                      
                }  
           });
    }
    }
   
   // Data Tables'additional_services.id',
   $(document).ready(function() {
     dataTable = $('#table').DataTable({
         "processing": true,
         "serverSide": true,
         "ajax": "/admin/service/test/ride/list/api",
         "aaSorting": [],
         "responsive": true,
         "columns": [
             { "data": "tag"},
             { "data": "frame"},
             { "data": "registration"},
             { "data": "customer_status"},
             { "data": "tr_type"},
             { "data":  "tr_out_time" },
             { "data":  "tr_in_time" },
             {
                 "targets": [ -1 ],
                 "data":"id", "render": function(data,type,full,meta)
                 {
                    var str = '';
                    if (full.tr_in_time != null || full.tr_out_time == null) {
                     str = str+' <a href="#"><button class="btn btn-info btn-xs "  onclick="TestRideOut(this)"><i class="'+data+'"></i>Test Ride Out</button></a> ';
                     }
                     if (full.tr_out_time != null && full.tr_in_time == null) {
                      str = str+' <a href="#"><button class="btn btn-primary btn-xs" onclick="TestRideIn(this)"><i class="'+data+'"></i>Test Ride In</button></a> ';  
                      }   
                      str = str+' <a href="/admin/service/test/ride/view/'+data+'"><button class="btn btn-success btn-xs">View</button></a> ';                  
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
                        {{-- <h3 class="box-title">{{__('customer.mytitle')}} </h3> --}}

      </div>
      
      <div class="box-body">
         @include('admin.flash-message')
         @yield('content')
         <table id="table" class="table table-bordered table-striped">
            <thead>
               <tr>
                  <th>Tag #</th>
                  <th>Frame #</th>
                  <th>Registration #</th>
                  <th>Customer Status</th>
                  <th>Test Ride Type</th>
                  <th>Out Time</th>
                  <th>In Time</th>
                  <th>{{__('factory.action')}}</th>
               </tr>
            </thead>
            <tbody>
            </tbody>
         </table>
      </div>
      <!-- /.box-body -->
   </div>
   <!-- Modal -->  <!-- Modal -->
              <div class="modal fade" id="rideModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                  <div class="modal-content" style="margin-top: 200px!important;">
                    <div class="modal-header">
                      <h4 class="modal-title" id="exampleModalLongTitle">Test Ride</h4>
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                      </button>
                    </div>
                    <div class="modal-body">
                      <form id="my-form"  method="POST" >
                          <div class="alert alert-danger" id="js-msg-verror">
                        </div>
                        @csrf
                        <input type="hidden" name="jobcard_id" id="jobcard_id">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="test_ride_type">Test Ride Type <sup>*</sup></label>
                                <select name="test_ride_type" id="test_ride_type" class="form-control">
                                   <option value="" selected="" disabled="">Select Type</option>
                                   <option value="Initail" >Initail</option>
                                   <option value="Final">Final</option>
                                   <option value="Pre Delivery">Pre Delivery</option>
                                </select>
                                {!! $errors->first('test_ride_type', '<p class="help-block">:message</p>') !!}
                            </div>
                        </div>
                  </div>
                    <div class="modal-footer">
                      <button type="button" id="btnCancel" class="btn btn-secondary" data-dismiss="modal">Close</button>
                      <button type="button" id="btnSubmit" class="btn btn-success">Submit</button>
                    </div>
                    </form>
                  </div>
                </div>
              </div>
   <!-- /.box -->
</section>
@endsection