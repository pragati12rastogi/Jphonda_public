@extends($layout)

@section('title', __('Salary Advance List'))

@section('breadcrumb')
    <li><a href="#"><i class=""></i>Salary Advance List</a></li>
@endsection
@section('css')
<link rel="stylesheet" href="/css/responsive.bootstrap.css">  
<style type="text/css">
    .nav1>li>button.btn-color-active{
    background-color: rgb(135, 206, 250);
  }
  .nav1>li>button.btn-color-unactive{
    background-color: white;
  }
</style>  
@endsection
@section('js')
<script src="/js/dataTables.responsive.js"></script>
<script>
 $.validator.addMethod("notValidIfSelectFirst", function(value, element, arg) {
        return arg !== value;
    }, "This field is required.");

    $('#infos').validate({ // initialize the plugin
        rules: {

            status: {
                required: true
            },
            amount: {
                required: true
            },
            
        }
    });
</script>
  <script>
    var dataTable;

    function datatablefn()
   {
    if(dataTable){
      dataTable.destroy();
    }
        
       dataTable = $('#admin_table').DataTable({
         "processing": true,
         "serverSide": true,
         "ajax": "",
         "aaSorting": [],
         "pageLength": 50,
         "responsive": true,
          "ajax": {
            "url": "/admin/payroll/salaryadvance/list/api",
            "datatype": "json",
                "data": function (data) {
                    var store_name = $('#store_name').val();
                    data.store_name = store_name;
                    var user_name = $('#user_name').val();
                    data.user_name = user_name;
                }
            },
            "createdRow": function( row, data, dataIndex){
                if( data.status=="Approved"){
                  $(row).css('background-color','#73AC80');               
                }
                if(data.status=="Rejected"){
                  $(row).css('background-color','#F0A4AC');
                }
            },
          "columns": [
              { "data": "emp_id" },
              { "data": "name",
                "data":"name", "render": function(data,type,full,meta)
                  {
                    if (full.name == null) {
                      var name = '';
                    }else{
                      name = full.name;
                    }

                    if (full.middle_name == null) {
                      var middle_name = '';
                    }else{
                      middle_name = full.middle_name;
                    }

                    if (full.last_name == null) {
                      var last_name = '';
                    }else{
                      last_name = full.last_name;
                    }
                    
                       return  name+' '+middle_name+' '+last_name ;
                  },
              },
              { "data": "loan_type" },
              { "data": "amount_requested" },
              { "data": "amount" },
              { "data": "installment" },
              { "data": "loan_purpose" },
              { "data": "document" },
              { "data": "status" },
              { "data": "level1" },

              {
                  "targets": [ -1 ],
                 data:function(data,type,full,meta)
                  {
                     
                      if(data.status=="Rejected"){
                      str= "<b>Rejected</b>";
                  
                    } else if(data.status=="Approved"){
                       str= '<b>Approved</b>';
                    } else if(data.status=="Pending"){
                        var auth="{{Auth::id()}}";
                        var role= "{{Auth::user()->role}}";
                        var user_type= "{{Auth::user()->user_type}}";

                        if(user_type=='superadmin' || role=='HRDManager'){
                           str= '<a ab id="approved" data-id="'+data.id+'" data-auth="'+auth+'"><button class="btn btn-primary btn-xs"> Approve </button></a>';

                        }else{
                          str= "You Are Not Eligible To Approve Or Reject.";
                        }

                    }
                       return str  ;
                  },
                  "orderable": false
              }
            ]
       });
}

  function completeddatatablefn()
   {
    if(dataTable){
      dataTable.destroy();
    }
        
       dataTable = $('#completed_table').DataTable({
         "processing": true,
         "serverSide": true,
         "ajax": "",
         "aaSorting": [],
         "pageLength": 50,
         "responsive": true,
          "ajax": {
            "url": "/admin/payroll/salaryadvance/list/completed/api",
            "datatype": "json",
                "data": function (data) {
                    var store_name = $('#store_name').val();
                    data.store_name = store_name;
                    var user_name = $('#user_name').val();
                    data.user_name = user_name;
                }
            },
            "createdRow": function( row, data, dataIndex){
                if( data.status=="Approved"){
                  $(row).css('background-color','#73AC80');               
                }
                if(data.status=="Rejected"){
                  $(row).css('background-color','#F0A4AC');
                }
            },
          "columns": [
              { "data": "emp_id" },
              { "data": "name",
                "data":"name", "render": function(data,type,full,meta)
                  {
                    if (full.name == null) {
                      var name = '';
                    }else{
                      name = full.name;
                    }

                    if (full.middle_name == null) {
                      var middle_name = '';
                    }else{
                      middle_name = full.middle_name;
                    }

                    if (full.last_name == null) {
                      var last_name = '';
                    }else{
                      last_name = full.last_name;
                    }
                    
                       return  name+' '+middle_name+' '+last_name ;
                  },
              },
              { "data": "loan_type" },
              { "data": "amount_requested" },
              { "data": "amount" },
              { "data": "installment" },
              { "data": "loan_purpose" },
              { "data": "document" },
              { "data": "status" },
              { "data": "level1" },

              {
                  "targets": [ -1 ],
                 data:function(data,type,full,meta)
                  {
                     
                      if(data.status=="Rejected"){
                      str= "<b>Rejected</b>";
                  
                    } else if(data.status=="Approved"){
                       str= '<b>Approved</b>';
                    } else if(data.status=="Pending"){
                        var auth="{{Auth::id()}}";
                        var role= "{{Auth::user()->role}}";
                        var user_type= "{{Auth::user()->user_type}}";

                        if(user_type=='superadmin' || role=='HRDManager'){
                           str= '<a ab id="approved" data-id="'+data.id+'" data-auth="'+auth+'"><button class="btn btn-primary btn-xs"> Approve </button></a>';

                        }else{
                          str= "You Are Not Eligible To Approve Or Reject.";
                        }

                    }
                       return str  ;
                  },
                  "orderable": false
              }
            ]
       });
}

// Data Tables'additional_services.id',
   $(document).ready(function() {
    datatablefn();
    completeddatatablefn();
    pending_data();
  });
   $('#store_name').on( 'change', function () {
      dataTable.draw();
    });
 $('#user_name').on( 'change', function () {
      dataTable.draw();
    });


 $(document).on('click','#approved',function(){
   var id=$(this).attr('data-id');
   var auth_id=$(this).attr('data-auth');
   cancel_alert_dailog(id,auth_id);
   $('#amount_div').hide();

 });

 $(document).on('change', '.status', function() {
    var val = $(this).val();
     
     if(val=='Approved'){
      $('#amount_div').show();
     }else{
      $('#amount_div').hide();
     }
  });

  function cancel_alert_dailog(id,auth_id)
    {

      $('#modal_div').empty().append(
            '<div id="myModal" class="modal fade" role="dialog">'+
              '<div class="modal-dialog modal-lg">'+
                '<!-- Modal content-->'+
                '<div class="modal-content">'+
                  '<div class="modal-header">'+
                    '<button type="button" class="close" data-dismiss="modal">&times;</button>'+
                    '<h4 class="modal-title">Approval/Rejection</h4>'+
                  '</div>'+
                  '<form id="infos" method="POST" action="/admin/payroll/salaryadvance/approve/'+id+'">'+
                    '@csrf'+
                    '<div class="modal-body">'+
                      '<input type="hidden" name="auth_id" value="'+auth_id+'">'+
                      '<br><label>Please select Below OPTIONS for Salary Advance Application</label>'+
                      '<label> <input name="status" class="status" type="radio" value="Approved" required> Approved.</label>'+
                      '<label> <input name="status" class="status" type="radio" value="Rejected" required> Rejected.</label>'+
                      '<label id="status-error" class="error" for="status"></label>'+
                      ' <div class="row" id="amount_div"><div class="col-md-6"><input type="number" name="amount" class="input-css" min="1" placeholder="Please Enter Approve amount" required>'+
                       '<label id="amount-error" class="error" for="amount"></label>'+
                      '</div></div>'+
                      '<br><br><input type="text" name="remark" class="input-css" placeholder="Please Enter Remark">'+
                    '</div>'+
                    '<div class="modal-footer">'+
                      '<button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>'+
                      '<a target="_blank"><button type="submit" class="btn btn-success"  onclick="$(\'#infos\').validate();">Submit</button></a>'+
                    '</div>'+
                    '</form>'+
                '</div>'+
              '</div>'+
            '</div>'
      );
          $(document).find('#myModal').modal("show"); 
  }

  $("#pending").on('click',function(){
    $(this).addClass('btn-color-active');
    $('#completed').removeClass('btn-color-active');
     $("#completed-div").html('');
     $("#completed-div").hide();
     pending_data();
  });

 $("#completed").on('click',function(){
    $(this).addClass('btn-color-active');
    $('#pending').removeClass('btn-color-active');
    $("#pending_data").html('');
    $("#completed-div").show();
      completed_data();
  });

 function pending_data() {
   $.ajax({
        url:'pending/',
        type: "GET",
        success: function(data){
          $("#pending_data").html(data);
           datatablefn();
        }
    });
}

 function completed_data() {
   $.ajax({
        url:'completed/',
        type: "GET",
        success: function(data){
          $("#completed-div").html(data);
          completeddatatablefn();
        }
    });
}

</script>
@endsection
@section('main_section')
<section class="content">
            <div id="app">
                    @include('admin.flash-message')
                    @yield('content')
        <!-- Default box -->
        
        <div class="box box-primary">
            <!-- /.box-header -->
             <div id="modal_div"></div>
            <div class="box-header with-border">
<!--                    <h3 class="box-title">{{__('customer.mytitle')}} </h3>-->
 <div class="col-md-6">
                    <ul class="nav nav1 nav-pills">
                      <li class="nav-item">
                        <button class="nav-link1 btn-color-active" id="pending" >Pending</button>
                      </li>
                      <li class="nav-item">
                       <button class="nav-link2" id="completed" >Completed </a></button>
                      </li>
                    </ul>
                  </div>
                </div>  
                <div class="box-body">

                <div class="row">
                    <div class="col-md-3" style="float: right;">
                       <label>User Name</label>
                       @if(count($users) > 1)
                          <select name="user_name" class="input-css select2 selectValidation" id="user_name">
                             <option value="">Select Users</option>
                             @foreach($users as $key => $val)
                             <option value="{{$val['id']}}"> {{$val['name']}} {{$val['middle_name']}} {{$val['last_name']}} </option>
                             @endforeach
                          </select>
                       @endif
                       {!! $errors->first('user_name', '
                       <p class="help-block">:message</p>
                       ') !!}
                    </div>
                    <div class="col-md-3" style="{{(count($store) == 1)? 'display:none' : 'display:block'}};float: right;">
                       <label>Store Name</label>
                       @if(count($store) > 1)
                          <select name="store_name" class="input-css select2 selectValidation" id="store_name">
                             <option value="">Select Store</option>
                             @foreach($store as $key => $val)
                             <option value="{{$val['id']}}" {{(old('store_name') == $val['id']) ? 'selected' : '' }}> {{$val['name']}} </option>
                             @endforeach
                          </select>
                       @endif
                       @if(count($store) == 1)
                          <select name="store_name" class="input-css select2 selectValidation" id="store_name">
                             <option value="{{$store[0]['id']}}" selected>{{$store[0]['name']}}</option>
                          </select>
                       @endif
                       {!! $errors->first('store_name', '
                       <p class="help-block">:message</p>
                       ') !!}
                    </div>
                </div>

            <div class="box-body" id="completed-div"></div>
            <div class="box-body" id="pending_data"></div>
                <!-- /.box-body -->
              </div>
        <!-- /.box -->
      </section>
@endsection