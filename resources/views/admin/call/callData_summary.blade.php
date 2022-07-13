@extends($layout)

@section('title', __('Call Data Summary'))

@section('user', Auth::user()->name)

@section('breadcrumb')
  <li><a href="#"><i class=""></i>{{__('Call Data Summary')}}</a></li> 
@endsection
@section('css')

  <link rel="stylesheet" href="/css/responsive.bootstrap.css">
  <style>
    .table-striped>tbody>tr.selected{
      background-color: aquamarine;
    }
  </style>

@endsection
@section('js')
<script src="/js/dataTables.responsive.js"></script>
<script>
  var dataTable;

    $("#js-msg-error").hide();
    $("#js-msg-success").hide();

    All();
    function All() {
      $("#insurance-div").css('display','none');
      $("#enquiry-div").css('display','none');
      $("#all-div").css('display','block');
      $("#service_enquiry-div").hide();

      if(dataTable)
      {
        dataTable.destroy();
      }
      dataTable = $('#table-all').DataTable({
        "processing": true,
         "serverSide": true,
         "ajax":  {
            url: "/admin/call/data/list/api/all",
            data: function(d){
              var user = $("#updateAssign").find("#user").val();
              d.user = user;
            }
        },
         "aaSorting": [],
         "responsive": true,
         "rowId":"cdd_id",
         "columns": [
            { "data": "call_type",
              "createdCell": function(td, cellData, rowData, row, col){
                $(td).attr('colspan',2);
              },
              "orderable":false
            },
            { "data": "assign_to" },
            { "data": "customer_name" },
            { "data": "mobile" },
            { "data": "source" },
            { "data": "call_status" },
            { "data": "remark" },
            { "data": "next_call_date" },
            // { "data": "status" },
            { "data": "cdd_status" },
             {
                  "targets": [ -1 ],
                  "data":"id", "render": function(data,type,full,meta)
                  {
                    var str = '';
                    str = str+'<a href="/admin/call/data/view?id='+data+'&cdd='+full.cdd_id+'" target="_blanck" class ="btn btn-info btn-xs">View</a>';
                    return str;
                  },
                  "orderable": false
              }
            ]
         
       });
    }
    // enquiry();
    function enquiry() {
      $("#insurance-div").hide();
      $("#all-div").hide();
      $("#service_enquiry-div").hide();  
      $("#enquiry-div").show();
      if(dataTable)
      {
        dataTable.destroy();
      }
      dataTable = $('#table-enquiry').DataTable({
        "processing": true,
         "serverSide": true,
         "ajax":  {
            url: "/admin/call/data/list/api/enquiry",
            data: function(d){
              var user = $("#updateAssign").find("#user").val();
              d.user = user;
            }
        },
         "aaSorting": [],
         "responsive": true,
         "rowId":"cdd_id",
         "columns": [
            { "data": "assign_to",
              "createdCell": function(td, cellData, rowData, row, col){
                $(td).attr('colspan',2);
              },
              "orderable":false
            },
            { "data": "customer_name" },
            { "data": "mobile" },
            { "data": "source" },
            { "data": "call_status" },
            { "data": "remark" },
            { "data": "next_call_date" },
            // { "data": "status" },
            { "data": "cdd_status" },
             {
                  "targets": [ -1 ],
                  "data":"id", "render": function(data,type,full,meta)
                  {
                       return '<a href="/admin/call/data/view?id='+data+'&cdd='+full.cdd_id+'" target="_blanck" class ="btn btn-info btn-xs">View</a>'  ;
                  },
                  "orderable": false
              }
            ],
            "columnDefs": [
            { "orderable": false, "targets": 7 }
            ]
         
       });
    }
    function insurance()
    {
      $("#enquiry-div").hide();
      $("#all-div").hide();
      $("#insurance-div").show();
      $("#service_enquiry-div").hide();
      if(dataTable)
      {
        dataTable.destroy();
      }
      dataTable = $('#table-insurance').DataTable({
        "processing": true,
         "serverSide": true,
        "ajax":  {
            url: "/admin/call/data/list/api/insurance",
            data: function(d){
              var user = $("#updateAssign").find("#user").val();
              d.user = user;
            }
        },
         "aaSorting": [],
         "responsive": true,
         "rowId":"cdd_id",
        "columns": [
            { "data": "assign_to" ,
              "createdCell": function(td, cellData, rowData, row, col){
                $(td).attr('colspan',2);
              },
              "orderable":false
            },
            { "data": "customer_name" },
            { "data": "mobile" },
            { "data": "source" },
            { "data": "call_status" },
            { "data": "remark" },
            { "data": "next_call_date" },
            // { "data": "status" },
            { "data": "cdd_status" },
            {
                  "targets": [ -1 ],
                  "data":"id", "render": function(data,type,full,meta)
                  {
                    var str = '';
                      str = str+'<a href="/admin/call/data/view?id='+data+'&cdd='+full.cdd_id+'" target="_blanck" class ="btn btn-info btn-xs">View</a>';
                     return str;
                  },
                  "orderable": false
            }
          ]
         
       });
    }
    function service_enquiry() {
      $("#insurance-div").hide();
      $("#all-div").hide();  
      $("#enquiry-div").hide();
      $("#service_enquiry-div").show();
      if(dataTable)
      {
        dataTable.destroy();
      }
      dataTable = $('#table-service_enquiry').DataTable({
        "processing": true,
         "serverSide": true,
         "ajax":  {
            url: "/admin/call/data/list/api/service_enquiry",
            data: function(d){
              var user = $("#updateAssign").find("#user").val();
              d.user = user;
            }
        },
         "aaSorting": [],
         "responsive": true,
         "rowId":"cdd_id",
         "columns": [
            { "data": "assign_to",
              "createdCell": function(td, cellData, rowData, row, col){
                $(td).attr('colspan',2);
              },
              "orderable":false
            },
            { "data": "customer_name" },
            { "data": "mobile" },
            { "data": "source" },
            { "data": "call_status" },
            { "data": "remark" },
            { "data": "next_call_date" },
            // { "data": "status" },
            { "data": "cdd_status" },
             {
                  "targets": [ -1 ],
                  "data":"id", "render": function(data,type,full,meta)
                  {
                       return '<a href="/admin/call/data/view?id='+data+'&cdd='+full.cdd_id+'" target="_blanck" class ="btn btn-info btn-xs">View</a>'  ;
                  },
                  "orderable": false
              }
            ],
            "columnDefs": [
            { "orderable": false, "targets": 7 }
            ]
         
       });
    } 
    $('#insurance').click(function(){
      $(".all-active").removeClass('btn-color-active');
      $(".all-active").removeClass('btn-color-unactive');
      $(this).addClass('btn-color-active');
      insurance();
      recover_all_actions();
    });

    $('#enquiry').click(function(){
      $(".all-active").removeClass('btn-color-active');
      $(".all-active").removeClass('btn-color-unactive');
      $(this).addClass('btn-color-active');
      enquiry();
      recover_all_actions();
    });
    $('#all').click(function(){
      $(".all-active").removeClass('btn-color-active');
      $(".all-active").removeClass('btn-color-unactive');
      $(this).addClass('btn-color-active');
      All();
      recover_all_actions();
    });
    $('#service_enquiry').click(function(){
      $(".all-active").removeClass('btn-color-active');
      $(".all-active").removeClass('btn-color-unactive');
      $(this).addClass('btn-color-active');
      service_enquiry();
      recover_all_actions();
    });
    $('table tbody').on( 'click', 'tr', function () {
        var user = $("#updateAssign").find('#user').val();
        // console.log(user);
        if(user){
          $(this).toggleClass('selected');
          var select_len = dataTable.rows('.selected').data().length;
          display_selected_text();

          // console.log(all_data);
          if(select_len > 0){
            $("#updateAssign").find('button').attr('disabled',false);
          }else{
            $("#updateAssign").find('button').attr('disabled',true);
          }
        }else{
          $("#updateAssign").find('button').attr('disabled',true);
        }
    });
    function display_selected_text(){
      var select_len = dataTable.rows('.selected').data().length;
      var page_info = dataTable.page.info();
      var total_record = page_info.recordsTotal;
      if(select_len > 0){ 
        $("#updateAssign").find('#select-text').text(select_len+" Call's Selected Out Of "+total_record);
      }else{
        $("#updateAssign").find('#select-text').text('');
      }
    }
    $('#updateAssign').submit( function () {
        // alert( table.rows('.selected').data().length +' row(s) selected' );
        var selected_row = dataTable.rows('.selected');
        var row_data = selected_row.data();
        var row_length = row_data.length;

        if(row_length <= 0){
          return false;
        }

        // console.log(selected_row,row_data,row_length);
        $("#callData-append-div").empty();
        for(var i = 0 ; i < row_length ; i++){
          var str = "<input type='hidden' name='assign_ids[]' value='"+row_data[i].cdd_id+"'>";
          $("#callData-append-div").append(str);
        }
        return true;
        // return false;
    });

    $("#updateAssign").find('#user').on('change',function(){
      var user_id = $(this).val();
      dataTable.ajax.reload();
      recover_all_actions();

      if(user_id != null && user_id != '' && user_id != undefined){

        // display_selected_text();
      }
    });
    function recover_all_actions(){
        $("#callData-append-div").empty();
        $("#updateAssign").find("button").attr('disabled',true);
        $("#updateAssign").find("#select-text").text('');
        $(document).find(".select_all").prop('checked',false);
    }
    $(document).find(".select_all").click(function(){
      // var row_data = dataTable.rows('tr');
      var user = $("#updateAssign").find('#user').val();
      if(user != null && user != '' && user != undefined){
        dataTable.rows().every( function ( rowIdx, tableLoop, rowLoop ) {
          $(this.node()).trigger('click');
        });
      }else{
        $(document).find(".select_all").prop('checked',false);
      }
    });
</script>
@endsection

@section('main_section')
    <section class="content">
        <div id="app">
            @include('admin.flash-message')
            @yield('content')
            
        </div>
        
    <!-- Default box -->
        <div class="box">
          <div class="box-header with-border">
            <div class="col-md-6">
              <ul class="nav nav1 nav-pills">
                <li class="nav-item">
                  <button class="nav-link1 all-active btn-color-active" id="all" >All Call's</button>
                </li>
                {{-- <li class="nav-item">
                  <button class="nav-link1 all-active btn-color-unactive" id="all_pending" >All Pending Call's</button>
                </li> --}}
                <li class="nav-item">
                  <button class="nav-link1 all-active btn-color-unactive" id="enquiry" >Enquiry Call's</button>
                </li>
                <li class="nav-item">
                  <button class="nav-link1 all-active btn-color-unactive" id="insurance" >Insurance Call's</button>
                </li>
                <li class="nav-item">
                  <button class="nav-link1 all-active btn-color-unactive" id="service_enquiry" >Service Enquiry Call's</button>
                </li> 
              </ul>
            </div>
            @if($auth_role == 'Superadmin')
            <form action="/admin/call/data/assign" method="POST" id="updateAssign">
              @csrf
              <div class="col-md-6" style="display: none" id="callData-append-div">

              </div>
              <div class="col-md-6">
                <div class="col-md-8">
                  <label>Select Users</label>
                  <select name="user" id="user" class="form-control select2">
                    <option value="">Select Users</option>
                    @foreach($call_users as $key => $val)
                      <option value="{{$val->id}}"> {{$val->name}} </option>
                    @endforeach
                  </select>
                </div>
                <div class="col-md-4">
                  <button class="btn btn-success" style="float: right" disabled type="submit">Assign</button>
                </div>
                <div class="col-md-6">
                  <label> <small id="select-text"></small> </label>
                </div>
              </div>
            </form>
            @endif
          </div>
                <!-- /.box-header -->
           <div class="box-body" id="all-div">
              <table id="table-all" class="table table-bordered table-striped" style="width:100%">
                  <thead>
                      <tr>
                        <th><input type="checkbox" name="table_all-select_all" id="table_all-select_all" class="select_all"></th>
                        <th>Call Type</th>
                        <th> {{__('Assigned To')}}</th>
                        <th>Name</th>
                        <th>Mobile</th>
                        <th>Source</th>
                        <th>{{__('Call Satus')}}</th>
                        <th>{{__('Remark')}}</th>
                        <th>{{__('Next Call Date')}}</th> 
                        <th>{{__('Status')}}</th> 
                        <th>{{__('Action')}}</th> 
                      </tr>
                  </thead>
                  <tbody>
                  </tbody>               
              </table>
              <!-- /.box-body -->
            </div>
            <div class="box-body" id="enquiry-div">
                    <table id="table-enquiry" class="table table-bordered table-striped" style="width:100%">
                        <thead>
                            <tr>
                              <th><input type="checkbox" name="table_enquiry-select_all" id="table_enquiry-select_all" class="select_all"></th>
                              <th> {{__('Assigned To')}}</th>
                              <th>Name</th>
                              <th>Mobile</th>
                              <th>Source</th>
                              <th>{{__('Call Satus')}}</th>
                              <th>{{__('Remark')}}</th>
                              <th>{{__('Next Call Date')}}</th> 
                              <th>{{__('Status')}}</th> 
                              <th>{{__('Action')}}</th> 
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>               
                    </table>
                    <!-- /.box-body -->
            </div>
            <div class="box-body" id="insurance-div">
              <table id="table-insurance" class="table table-bordered table-striped" style="width:100%">
                <thead>
                  <tr>
                    <th><input type="checkbox" name="table_insurance-select_all" id="table_insurance-select_all" class="select_all"></th>
                    <th> {{__('Assigned To')}}</th>
                    <th>Name</th>
                    <th>Mobile</th>
                    <th>Source</th>
                    <th>{{__('Call Satus')}}</th>
                    <th>{{__('Remark')}}</th>
                    <th>{{__('Next Call Date')}}</th> 
                    <th>{{__('Satus')}}</th>
                    <th>{{__('Action')}}</th> 
                  </tr>
                </thead>
                  <tbody>
                  </tbody>               
              </table>
              <!-- /.box-body -->
            </div>
            <div class="box-body" id="service_enquiry-div">
                    <table id="table-service_enquiry" class="table table-bordered table-striped" style="width:100%">
                        <thead>
                            <tr>
                              <th><input type="checkbox" name="table_enquiry-select_all" id="table_enquiry-select_all" class="select_all"></th>
                              <th> {{__('Assigned To')}}</th>
                              <th>Name</th>
                              <th>Mobile</th>
                              <th>Source</th>
                              <th>{{__('Call Satus')}}</th>
                              <th>{{__('Remark')}}</th>
                              <th>{{__('Next Call Date')}}</th> 
                              <th>{{__('Status')}}</th> 
                              <th>{{__('Action')}}</th> 
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>               
                    </table>
                    <!-- /.box-body -->
            </div>
        </div>
          <!-- Modal -->
              <div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                  <div class="modal-content" style="margin-top: 200px!important;">
                    <div class="modal-header">
                      <h4 class="modal-title" id="exampleModalLongTitle">Update Call Response</h4>
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                      </button>
                    </div>
                    <div class="modal-body">
                      <span id="msg" style="color:red;">All Field are Required</span>
                        <div class="row">
                          <div class="col-md-6">
                              <label>Call Status<sup>*</sup></label>
                              <input id="call_summary_id" type="hidden" name="call_summary_id" class="input-css">
                              <select id="call_status"  name="call_status" class="form-control select2">
                                <option value="">Select Call Status</option>
                                <option value="Busy">Busy</option>
                                <option value="Not Reachable">Not Reachable</option>
                                <option value="Not Received">Not Received</option>
                              </select>
                              <br>
                          </div>
                          <div class="col-md-6">
                            <label>Next Call Date<sup>*</sup></label>
                            <input id="next_call_date" type="date" name="next_call_date" class="input-css">
                            <br>
                        </div>
                          <div class="col-md-12">
                              <label>Remark <sup>*</sup></label>
                              <textArea id="remark" type="text" name="remark" class="form-control"></textArea>
                          </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                      <button type="button" id="btnUpdate" onclick="btnUpdate()" class="btn btn-success">Update</button>
                    </div>
                  </div>
                </div>
              </div>
        <!-- /.box -->
    </section>
@endsection