@extends($layout)
@section('title', __('Service Frame Out List'))
@section('breadcrumb')
<li><a href="#"><i class=""></i></a></li>
@endsection
@section('css')
<link rel="stylesheet" href="/css/responsive.bootstrap.css">
<style>


* {
  -webkit-box-sizing:border-box;
  -moz-box-sizing:border-box;
  box-sizing:border-box;
}

*:before, *:after {
-webkit-box-sizing: border-box;
-moz-box-sizing: border-box;
box-sizing: border-box;
}

.clearfix {
  clear:both;
}

.text-center {text-align:center;}

a {
  color: tomato;
  text-decoration: none;
}

a:hover {
  color: #2196f3;
}

pre {
display: block;
padding: 9.5px;
margin: 0 0 10px;
font-size: 13px;
line-height: 1.42857143;
color: #333;
word-break: break-all;
word-wrap: break-word;
background-color: #F5F5F5;
border: 1px solid #CCC;
border-radius: 4px;
}

.header {
  padding:20px 0;
  position:relative;
  margin-bottom:10px;
  
}

.header:after {
  content:"";
  display:block;
  height:1px;
  background:#eee;
  position:absolute; 
  left:30%; right:30%;
}

.header h2 {
  font-size:3em;
  font-weight:300;
  margin-bottom:0.2em;
}

.header p {
  font-size:14px;
}



#a-footer {
  margin: 20px 0;
}

.new-react-version {
  padding: 20px 20px;
  border: 1px solid #eee;
  border-radius: 20px;
  box-shadow: 0 2px 12px 0 rgba(0,0,0,0.1);
  
  text-align: center;
  font-size: 14px;
  line-height: 1.7;
}

.new-react-version .react-svg-logo {
  text-align: center;
  max-width: 60px;
  margin: 20px auto;
  margin-top: 0;
}

.success-box > div {
  vertical-align:top;
  display:inline-block;
  color:#888;
}

/* Rating Star Widgets Style */
.rating-stars ul {
  list-style-type:none;
  padding:0;
  
  -moz-user-select:none;
  -webkit-user-select:none;
}
.rating-stars ul > li.star {
  display:inline-block;
  
}

/* Idle State of the stars */
.rating-stars ul > li.star > i.fa {
  font-size:2.5em; /* Change the size of the stars */
  color:#ccc; /* Color on idle state */
}

/* Hover state of the stars */
.rating-stars ul > li.star.hover > i.fa {
  color:#FFCC36;
}

/* Selected state of the stars */
.rating-stars ul > li.star.selected > i.fa {
  color:#FF912C;
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
     $(document).ready(function(){
  
  /* 1. Visualizing things on Hover - See next part for action on click */
  $('#stars li').on('mouseover', function(){
    var onStar = parseInt($(this).data('value'), 10); // The star currently mouse on
   
    // Now highlight all the stars that's not after the current hovered star
    $(this).parent().children('li.star').each(function(e){
      if (e < onStar) {
        $(this).addClass('hover');
      }
      else {
        $(this).removeClass('hover');
      }
    });
    
  }).on('mouseout', function(){
    $(this).parent().children('li.star').each(function(e){
      $(this).removeClass('hover');
    });
  });
  
  
  /* 2. Action to perform on click */
  $('#stars li').on('click', function(){
    var onStar = parseInt($(this).data('value'), 10); // The star currently selected
    var stars = $(this).parent().children('li.star');
    for (i = 0; i < stars.length; i++) {
      $(stars[i]).removeClass('selected');
    }
    
    for (i = 0; i < onStar; i++) {
      $(stars[i]).addClass('selected');
    }
    
    // JUST RESPONSE (Not needed)
    var ratingValue = parseInt($('#stars li.selected').last().data('value'), 10);
    $('#rate').val(ratingValue);
    var msg = "";
    if (ratingValue > 1) {
        msg = "Thanks! You rated this " + ratingValue + " stars.";
    }
    else {
        msg = "We will improve ourselves. You rated this " + ratingValue + " stars.";
    }
    responseMessage(msg);
    
  });
  
  
});


function responseMessage(msg) {
  $('.success-box').fadeIn(200);  
  $('.success-box div.text-message').html("<span>" + msg + "</span>");
}

  
    function Feedback(el) {
      var id = $(el).children('i').attr('class');
        $("#jobcard_id").val(id);
        $('#serviceModalCenter').modal("show");
    }

    $('#btnSubmit').on('click', function() {
      var id = $('#jobcard_id').val();
      var rate = $('#rate').val();
      var feedback = $('#feedback').val();
       $.ajax({
        method: "GET",
        url: "/admin/service/feedback",
        data: {'jobcard_id':id,'rate':rate,'feedback':feedback},
        success:function(data) {
            if (data == 'success') {
                $('#serviceModalCenter').modal("hide");  
                $('#table').dataTable().api().ajax.reload();
                $("#js-msg-success").html('Thank you for rating and feedback !');
                $("#js-msg-success").show();
               setTimeout(function() {
                 $('#js-msg-success').fadeOut('fast');
                }, 4000);
            }if(data == 'error'){
                $('#serviceModalCenter').modal("hide");  
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
            if (data == 'added') {
              $("#js-msg-verror").html('Your rating already added !');
              $("#js-msg-verror").show();
            }
        },
      });
        $("#js-msg-verror").hide();
        $('#my-form')[0].reset(); 
    }); 

     function CrmFeedback(el) {
      var id = $(el).children('i').attr('class');
        $("#jobcard_id").val(id);
        $('#CrmFeedback').modal("show");
    } 

    $('#btnCrmSubmit').on('click', function() {
      var id = $('#jobcard_id').val();
      var cust_problem = $('#cust_problem').val();
       $.ajax({
        method: "GET",
        url: "/admin/service/crm/feedback",
        data: {'jobcard_id':id,'problem':cust_problem},
        success:function(data) {
            if (data == 'success') {
                $('#CrmFeedback').modal("hide");  
                $('#table').dataTable().api().ajax.reload();
                $("#js-msg-success").html('Customer Problem send to crm !');
                $("#js-msg-success").show();
               setTimeout(function() {
                 $('#js-msg-success').fadeOut('fast');
                }, 4000);
            }if(data == 'error'){
                $('#CrmFeedback').modal("hide");  
                $("#js-msg-error").html('Something went wrong !');
                $("#js-msg-error").show();
               setTimeout(function() {
                 $('#js-msg-error').fadeOut('fast');
                }, 4000);
            }
        },
      });
        $("#js-msg-verror").hide();
        $('#my-form')[0].reset(); 
    }); 
 
   
   // Data Tables'additional_services.id',
   $(document).ready(function() {
     dataTable = $('#table').DataTable({
         "processing": true,
         "serverSide": true,
         "ajax": "/admin/service/frame/out/list/api",
         "aaSorting": [],
         "responsive": true,
         "columns": [
             { "data": "tag"},
             { "data": "frame"},
             { "data": "registration"},
             { "data": "customer_status"},
             { "data": "hirise_amount"},
              { "data":  "fi_status",
              "render": function(data,type,full,meta)
                 {
                    var status = ((data == 'ok')? 'Ok' : ((data == 'notok')? 'Not Ok': ''));
                    return status;
                 },
             },
             { "data": "invoice_no"},
             { "data": "payment_status"},
             { "data": "pay_letter",
                "render": function(data,type,full,meta)
                 {
                    var pay = ((data == 1)? 'Allowed' : ((data == 0)? 'Not Allowed': ''));
                    return pay;
                 },
             },
             { "data": "customer_problem"},
             { "data": "status"},
             {
                 "targets": [ -1 ],
                 "data":"id", "render": function(data,type,full,meta)
                 {
                    var str = '';
                      if (data != full.jobcard_id) {
                        str = str+' <a href="#"><button class="btn btn-info btn-xs "  onclick="Feedback(this)"><i class="'+data+'"></i>Out/Feedback</button></a> ';
                      }if (full.rating  != null && full.rating < 3 && full.customer_problem  == null){
                         str = str+' <a href="#"><button class="btn btn-primary btn-xs "  onclick="CrmFeedback(this)"><i class="'+data+'"></i>Crm Feedback</button></a>';
                      }
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
                  <th>Tag #</th>
                  <th>Frame #</th>
                  <th>Registration #</th>
                  <th>Customer Status</th>
                  <th>Hirise Amount</th>
                  <th>FI Status</th>
                  <th>Invoice Number</th>
                  <th>Payment Status</th>
                  <th>Pay Latter</th>
                  <th>Customer Problem</th>
                  <th>Status</th>
                  <th>{{__('factory.action')}}</th>
               </tr>
            </thead>
            <tbody>
            </tbody>
         </table>
      </div>
      <!-- /.box-body -->
      <!-- Modal -->
              <div class="modal fade" id="serviceModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                  <div class="modal-content" style="margin-top: 200px!important;">
                    <div class="modal-header">
                      <h4 class="modal-title" id="exampleModalLongTitle">Customer Feedback</h4>
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
                          <section class='rating-widget'>
                            <!-- Rating Stars Box -->
                            <div class="col-md-10">
                              <label for="rating">Rating<sup>*</sup></label>
                            <div class='rating-stars'>
                              <ul id='stars'>
                                <li class='star' title='Poor' data-value='1'>
                                  <i class='fa fa-star fa-fw'></i>
                                </li>
                                <li class='star' title='Fair' data-value='2'>
                                  <i class='fa fa-star fa-fw'></i>
                                </li>
                                <li class='star' title='Good' data-value='3'>
                                  <i class='fa fa-star fa-fw'></i>
                                </li>
                                <li class='star' title='Excellent' data-value='4'>
                                  <i class='fa fa-star fa-fw'></i>
                                </li>
                                <li class='star' title='WOW!!!' data-value='5'>
                                  <i class='fa fa-star fa-fw'></i>
                                </li>
                              </ul>
                            </div>
                            </div>
                             <div class='success-box'>
                                <div class='clearfix'></div>
                                <div class='text-message'></div>
                                <div class='clearfix'></div>
                              </div>
                          </section>
                        </div>
                        <div class="row">
                          <input type="hidden" name="rate" id="rate">
                          <input type="hidden" name="jobcard_id" id="jobcard_id">
                          <div class="col-md-10">
                              <label for="Feedback">Feedback <sup>*</sup></label>
                              <textarea class="form-control" id="feedback" name="feedback"></textarea>
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
               <div class="modal fade" id="CrmFeedback" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                  <div class="modal-content" style="margin-top: 200px!important;">
                    <div class="modal-header">
                      <h4 class="modal-title" id="exampleModalLongTitle">Crm Feedback</h4>
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                      </button>
                    </div>
                    <div class="modal-body">
                      <form id="my-form"  method="POST" >
                        @csrf
                        <input type="hidden" name="jobcard_id" id="jobcard_id">
                        <div class="row">
                          <input type="hidden" name="jobcard_id" id="jobcard_id">
                          <div class="col-md-10">
                              <label for="Customer Problem">Customer Problem <sup>*</sup></label>
                              <textarea class="form-control" id="cust_problem" name="cust_problem"></textarea>
                            </div>
                        </div>
                  </div>
                    <div class="modal-footer">
                      <button type="button" id="btnCancel" class="btn btn-secondary" data-dismiss="modal">Close</button>
                      <button type="button" id="btnCrmSubmit" class="btn btn-success">Submit</button>
                    </div>
                    </form>
                  </div>
                </div>
              </div>
   </div>
   <!-- /.box -->
</section>
@endsection