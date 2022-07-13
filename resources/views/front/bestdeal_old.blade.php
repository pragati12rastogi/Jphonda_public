@extends($layout)

@section('title', __('Best Deal Inventory'))

@section('breadcrumb')
    <li><a href="#"><i class=""></i>{{__('Best Deal Inventory')}}</a></li>
@endsection
@section('css')
  
<link rel="stylesheet" href="/css/AdminLTE.min.css">
<link rel="stylesheet" href="/css/bootstrap.min.css">    
 <!-- Theme style -->
  

<!---Data Tables--->
  <link rel="stylesheet" href="/css/dataTables.bootstrap.min.css">
  <link rel="stylesheet" href="/js/datatables-fixedcolumns/css/fixedColumns.bootstrap4.min.css">    
  <link rel="stylesheet" href="/js/datatables-fixedheader/css/fixedHeader.bootstrap4.min.css"> 
<style type="text/css">
.text{
  font-weight: 500;
}
.modal-header {
    margin-top: 100px;
    }
.modal-body {
   display: flex; 
   flex-direction: column;
   
    }
sup {
    color: red;
}
.modal-content{
    width: 600px !important;
}
@media (max-width: 600px) {
  .modal-content {
    max-width: 300px;
}
  }

</style>
@endsection
@section('js')
<script src="/js/dataTables.responsive.js"></script>
<!-----Data Table----->
<script src="/js/jquery.dataTables.min.js"></script>
<script src="/js/dataTables.bootstrap.min.js"></script>

<script src="/js/datatables-fixedcolumns/js/dataTables.fixedColumns.min.js"></script>
<script src="/js/datatables-fixedheader/js/dataTables.fixedHeader.min.js"></script>


  <script>
    var dataTable;
    
    $('#js-msg-error').hide();
    $('#js-msg-success').hide();
    $('#js-msg-errors').hide();
    $('#js-msg-successs').hide();

    // Data Tables
    
    $(document).ready(function() {
      dataTable = $('#admin_table').DataTable({
          "processing": true,
          "serverSide": true,
          "ajax": "/best/deal/inventory/api",
          "aaSorting": [],
          "responsive": true,
          "createdRow": function (row, data, index) {
              // console.log(row,data,index);
              $(row).attr("id",'row_id-'+data.id);
          },
          "columns": [
              { "data": "model"},
              { "data": "variant"},
              { "data": "color"},
              { "data": "dos"},
              { "data": "register_no"},
              { "data": "frame"},
              { "data": "address"},
             {
                  "targets": [ -1 ],
                  "data":"id", "render": function(data,type,full,meta)
                  {
                    var str = '';
                     
                     str= '<button id="modal_getprice" data-id="'+full.id+'" data-selling_price="'+full.selling_price+'" class="btn btn-success btn-xs" data-toggle="modal" data-target="#myModal_comp_nip">Get Price</button>';
                      
                      return str; 
                    
                  },
                  "orderable": false
              }
            ],
        });
    });
    
$(document).on('click','#modal_getprice',function(){
      var best_deal_id=$(this).attr('data-id');
      var selling_price=$(this).attr('data-selling_price');
      $('#js-msg-errors').hide();
      $('#js-msg-successs').hide();
      $('.mobile_div').show();
      $('#mobilebutton').show();
      $('.otp_div').hide();
      $('.price_div').hide();
      $('#otpbutton').hide();
      $("#mobile").val('');
      $("#otp").val('');
      $("#ids").val(best_deal_id);
     
  });

(function($) {
  $.fn.inputFilter = function(inputFilter) {
    return this.on("input keydown keyup mousedown mouseup select contextmenu drop", function() {
      if (inputFilter(this.value)) {
        this.oldValue = this.value;
        this.oldSelectionStart = this.selectionStart;
        this.oldSelectionEnd = this.selectionEnd;
      } else if (this.hasOwnProperty("oldValue")) {
        this.value = this.oldValue;
        this.setSelectionRange(this.oldSelectionStart, this.oldSelectionEnd);
      } else {
        this.value = "";
      }
    });
  };

}(jQuery));

// Install input filters.
$("#mobile").inputFilter(function(value) {
  return /^-?\d*$/.test(value); });

$(document).on('click','#mobilebutton',function(){
    
    var mobile=$("#mobile").val();
     $('#js-msg-errors').hide();
     $('#js-msg-successs').hide();
     intRegex = /^[789]\d{9}$/;

     if(mobile==''){

        $('#js-msg-errors').html("Mobile No is required").show(); 
         return false;

     } else if((mobile.length < 10))
     {
        $('#js-msg-errors').html("Mobile No may not be greater than 10 digits.").show(); 
             return false;
    }else if(!intRegex.test(mobile)){
         $('#js-msg-errors').html("Mobile No is invalid.").show(); 
             return false;
    }
     else 
      {
        $.ajax({
            url:'/bestdealinventory/otp',
            data:{'mobile':mobile},
            method:'GET',
            success:function(result){
               if(result.type == 'success')
               {
                  // $('#myModal_comp_done').modal("hide");
                  $('#js-msg-successs').html(result.msg).show().delay(1000).fadeOut();
                  $('.mobile_div').hide();
                  $('#mobilebutton').hide();
                  $('.otp_div').show();
                  $('#otpbutton').show();
               }
               else if(result.type == 'error')
               {
                  $('#js-msg-errors').html(result.msg).show();
                  $('.mobile_div').show();
                  $('.otp_div').hide();
               }
               else{
                  $('#js-msg-errors').html("Something Wen't Wrong").show();
               }
            },
            error:function(error){
               $('#js-msg-errors').html("Something Wen't Wrong "+error).show();
            }
         });
      }

   });

$(document).on('click','#otpbutton',function(){
    var otp=$("#otp").val();
    var mobile=$("#mobile").val();
    var id=$("#ids").val();
     $('#js-msg-errors').hide();
     $('#js-msg-successs').hide();
      if(otp)
      {
        $.ajax({
            url:'/bestdealinventory/otp_match',
            data:{'otp':otp,'mobile':mobile,'id':id},
            method:'GET',
            success:function(result){
               if(result.type == 'success')
               {
                  // $('#myModal_comp_done').modal("hide");
                  $('#price').text(result.price);
                  $('#js-msg-successs').html('The price is “Negotiable”.').show();
                  $('.price_div').show();
                  $('.mobile_div').hide();
                  $('#mobilebutton').hide();
                  $('.otp_div').hide();
                  $('#otpbutton').hide();
               }
               else if(result.type ==  'error')
               {
                  
                  $('#js-msg-errors').html(result.msg).show();
                  // $('.mobile_div').show();
                  // $('.otp_div').hide();
               }
               else{
                  $('#js-msg-errors').html("Something Wen't Wrong").show();
               }
            },
            error:function(error){
               $('#js-msg-errors').html("Something Wen't Wrong "+error).show();
            }
         });
      }else{
        $('#js-msg-errors').html("OTP is required").show(); 
      }

   });

  </script>
@endsection

@section('main_section')

    <section class="content">
        <div class="container">            
            <div id="app"><br/><br><br><br><br><br>
                    @include('admin.flash-message')
                    @yield('content')
        <!-- Default box -->
            
      <div class="box box-primary">
            <!-- /.box-header -->
            <div class="box-header with-border">
                <div class="blog-hm-content">
                    <h3 class="box-title">Best Deal  Inventory </h3>
                </div>
            </div>  
            <div class="box-body">
              <table id="admin_table" class="table table-bordered table-striped">
                      <thead>
                      <tr>
                        <th>Model</th>
                        <th>Variant</th>
                        <th>Color</th>
                        <th>RC Date Of Sale</th>
                        <th>Registration Number</th>
                        <th>Frame</th>
                        <th>Address</th>          
                        <th>Action</th>          
                      </tr>
                      </thead>
                      <tbody>

                      </tbody>
                
              </table>
           
            </div> </div>
                       <div id="myModal_comp_nip" class="modal fade" role="dialog">
                            <div class="modal-dialog">
                          
                                <!-- Modal content-->
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h4 class="modal-titles">Best Deal Inventory</h4>
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    </div>
                                    <div class="modal-body">
                                         <div class="alert alert-danger" id="js-msg-errors"></div>
                                         <div class="alert alert-success" id="js-msg-successs"></div>
                                         <input type="text" name="id" id="ids" hidden>
                                          <div class="row price_div">
                                            <div class="col-md-12">
                                                <label for="">Suggested Price</label><br/>
                                                <span id="price"></span>
                                            </div>
                                          </div><br><br>
                                          <div class="row mobile_div">
                                            <div class="col-md-12">
                                                <label for="">Mobile No<sup>*</sup></label>
                                                <input type="text" placeholder="Enter Your Mobile No" name="mobile" id="mobile" maxlength="10" pattern="[1-9]{1}[0-9]{9}" class="input-css">
                                            </div>
                                          </div><br><br>
                                          <div class="row otp_div">
                                            <div class="col-md-12">
                                                <label for="">OTP<sup>*</sup></label>
                                                <input type="text" placeholder="Enter Otp" name="otp" id="otp" class="input-css">
                                            </div>
                                          </div><br><br>
                                         
                                          <div class="modal-footer">
                                           
                                            <button type="button" id="mobilebutton" class="btn btn-success">Submit</button>&nbsp;&nbsp; <button type="button" id="otpbutton" class="btn btn-success">Submit</button>&nbsp;&nbsp;
                                              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                          </div>
                                    </div>
                
                                </div>
                            </div>
                         </div>
      </section>
@endsection