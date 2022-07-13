@extends($layout)

@section('title', __('hirise.rto_rc'))

@section('user', Auth::user()->name)

@section('breadcrumb')

    <li><a href="#"><i class=""></i>{{__('hirise.rto_rc')}}</a></li> 
@endsection
@section('css')

<link rel="stylesheet" href="/css/responsive.bootstrap.css">
<style>

</style>

@endsection
@section('js')
<script src="/js/dataTables.responsive.js"></script>
<script>
  var dataTable;
   function updateRC(el){
      // console.log('h');
      var id = $(el).children('i').attr('class');
      $.ajax({  
                url:"/admin/rto/get/rcnumber/"+id,  
                method:"get",  
                success:function(data){ 
                 if(data) {
                  //  console.log(data);
                        $("#rc_number").val('');
                        $("#receiving_date").val('');
                        $.each(data,function(key,value){
                          $("#rto_id").val(data['id']);
                          $("#rc_number").val(data['rc_number']);
                          $("#receiving_date").val(data['receiving_date']);
                        }); 
                        $('#rtoModalCenter').modal("show"); 
                    }
                      
                }  
           });
    }

     $('#btnUpdate').on('click', function() {
      var id = $('#rto_id').val();
      var rc_number = $('#rc_number').val();
      var receiving_date = $('#receiving_date').val();
       $.ajax({
        method: "GET",
        url: "/admin/rto/rc/update",
        data: {'rto_id':id,'rc_number':rc_number,'receiving_date':receiving_date},
        success:function(data) {
            if (data == 'success') {
              $("#"+id).children('td.rc_number').html(rc_number);
              $('#rtoModalCenter').modal("hide");  
              $("#js-msg-success").html('RC Number Updated !');
              $("#js-msg-success").show();
              $('#table').dataTable( ).api().ajax.reload();
            } if (data == 'error') {
               $("#js-msg-error").html('Something went wronge !');
               $("#js-msg-error").show();
               $('#rtoModalCenter').modal("hide");  

            }
        },
        error:function(data){
          // console.log(data);
          alert('These field is require');
        }
      });

    });

 
    $("#js-msg-error").hide();
    $("#js-msg-success").hide();

    pending();
    function pending()
    {
      $("#done-div").css('display','none');
      $("#hsrp-div").css('display','none');
      $("#pending-div").css('display','block');
      if(dataTable)
      {
        dataTable.destroy();
      }
      dataTable = $('#table-pending').DataTable({
        "processing": true,
         "serverSide": true,
         "ajax": "/admin/rto/rc/list/api/pending",
         "aaSorting": [],
         "responsive": true,
         "rowId":"id",
         "columns": [
            { "data": "id" },
            { "data": "sale_no" },
            { "data": "rto_type",
              "render": function (data, type, row) {
                    if (data) {
                        var str = data;
                        var string = str.replace(/_/g, ' ');
                        return string.charAt(0).toUpperCase() + string.slice(1);
                      } 
                      return '';
                }
              },
            { "data": "rto_finance" },
            { "data": "rto_amount" },
            { "data": "application_number" },
            { "data": "front_lid" },
            { "data": "rear_lid" },
            { "data": "rc_number","className":"rc_number"  },
             {
                 "targets": [ -1 ],
                 "data":"id", "render": function(data,type,full,meta)
                 {
                  var str = '';
                    str += '<a href="/admin/rto/rc/view/'+data+'"><button class="btn btn-success btn-xs"> View</button></a>';
                   str +='<a href="#"><button class="btn btn-info btn-xs "  onclick="updateRC(this)"><i class="'+data+'"></i> Update RC</button></a>';
                   return str;
                 },
                 "orderable": false
             }
           ]
         
       });
    }
    function done()
    {
      $("#pending-div").css('display','none');
      $("#hsrp-div").css('display','none');
      $("#done-div").css('display','block');
    
      if(dataTable)
      {
        dataTable.destroy();
      }
      dataTable = $('#table-done').DataTable({
        "processing": true,
         "serverSide": true,
         "ajax": "/admin/rto/rc/list/api/done",
         "aaSorting": [],
         "responsive": true,
         "rowId":"id",
         "columns": [
            { "data": "sale_no" },
            { "data": "rto_type",
              "render": function (data, type, row) {
                    if (data) {
                        var str = data;
                        var string = str.replace(/_/g, ' ');
                        return string.charAt(0).toUpperCase() + string.slice(1);
                      } 
                      return '';
                }
              },
            { "data": "rto_finance" },
            { "data": "rto_amount" },
            { "data": "application_number" },
            { "data": "front_lid" },
            { "data": "rear_lid" },
            { "data": "rc_number","className":"rc_number"  },
            {
                 "targets": [ -1 ],
                 "data":"id", "render": function(data,type,full,meta)
                 {
                   return '<a href="/admin/rto/rc/view/'+data+'"><button class="btn btn-success btn-xs"> View</button></a>&nbsp;<a href="/admin/rto/rc/correction/request/'+data+'"><button class="btn btn-info btn-xs"> Correction Request</button></a>';
                 },
                 "orderable": false
             }
             
           ]
         
       });
    }

     function HsrpPending() {
      $("#done-div").css('display','none');
      $("#pending-div").css('display','none');
      $("#hsrp-div").css('display','block');
      if(dataTable) {
        dataTable.destroy();
      }
      dataTable = $('#table-hsrp').DataTable({
        "processing": true,
         "serverSide": true,
         "ajax": "/admin/rto/rc/hsrp/list/api",
         "aaSorting": [],
         "responsive": true,
         "rowId":"id",
         "columns": [
            { "data": "id" },
            { "data": "main_type"},
            { "data": "rto_type",
              "render": function (data, type, row) {
                    if (data) {
                        var str = data;
                        var string = str.replace(/_/g, ' ');
                        return string.charAt(0).toUpperCase() + string.slice(1);
                      } 
                      return '';
                }
              },
            { "data": "rto_finance" },
            { "data": "rto_amount" },
            { "data": "application_number" },
            { "data": "front_lid" },
            { "data": "rear_lid" },
            { "data": "rc_number","className":"rc_number"  },
             {
                 "targets": [ -1 ],
                 "data":"id", "render": function(data,type,full,meta)
                 {
                  var str = '';
                    str += '<a href="/admin/rto/rc/view/'+data+'"><button class="btn btn-success btn-xs"> View</button></a>';
                  if (full.rc_number == null) {
                    str +='<a href="#"><button class="btn btn-info btn-xs "  onclick="updateRC(this)"><i class="'+data+'"></i> Update RC</button></a>';
                  }
                   return str;
                 },
                 "orderable": false
             }
           ]
         
       });
    }
      
    $('#done').click(function(){
      $(this).css('background', 'rgb(135, 206, 250)');
          $("#pending").css('background', '#fff');
          $("#hsrp").css('background', '#fff');
      done();
    });
    $('#pending').click(function(){
      $(this).css('background', 'rgb(135, 206, 250)');
          $("#done").css('background', '#fff');
          $("#hsrp").css('background', '#fff');
      pending();
    });

    $('#hsrp').click(function(){
      $(this).css('background', 'rgb(135, 206, 250)');
          $("#done").css('background', '#fff');
          $("#pending").css('background', '#fff');
       HsrpPending();
    });

  



</script>
@endsection

@section('main_section')
    <section class="content">
        <div id="app">
            @include('admin.flash-message')
            @yield('content')
            <div class="row">
            <div class="alert alert-danger" id="js-msg-error">
            </div>
            <div class="alert alert-success" id="js-msg-success">
            </div>
          </div>
            
        </div>
        
    <!-- Default box -->
        <div class="box">
          <div class="box-header with-border">
            {{-- 
            <h3 class="box-title">{{__('customer.mytitle')}} </h3>
            --}}
            <ul class="nav nav1 nav-pills">
              <li class="nav-item">
                <button class="nav-link1 btn-color-active" id="pending" >Pending RC</button>
              </li>
              <li class="nav-item">
                <button class="nav-link1 btn-color-unactive" id="done" >Received RC</button>
              </li> 
              <li class="nav-item">
                <button class="nav-link1 btn-color-unactive" id="hsrp" >HSRP RC</button>
              </li>
            </ul>
          </div>
                <!-- /.box-header -->
            <div class="box-body" id="pending-div">
                    <table id="table-pending" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                              <th>Id</th>
                              <th>{{__('Sale Number')}}</th>
                              <th>{{__('RTO Type')}}</th>
                              <th>{{__('hirise.rto_finance')}}</th>
                              <th>{{__('hirise.rto_amount')}}</th>
                              <th>{{__('hirise.rto_app_no')}}</th>
                              <th>{{__('Front Lid')}}</th>
                              <th>{{__('Rear Lid')}}</th>
                              <th>{{__('RC Number')}}</th>
                              <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>               
                    </table>
                    <!-- /.box-body -->
            </div>
            <div class="box-body" id="done-div">
              <table id="table-done" class="table table-bordered table-striped">
                  <thead>
                      <tr>
                        <th>{{__('Sale Number')}}</th>
                        <th>{{__('RTO Type')}}</th>
                        <th>{{__('hirise.rto_finance')}}</th>
                        <th>{{__('hirise.rto_amount')}}</th>
                        <th>{{__('hirise.rto_app_no')}}</th>
                        <th>{{__('Front Lid')}}</th>
                        <th>{{__('Rear Lid')}}</th>
                        <th>{{__('RC Number')}}</th>
                        <th>{{__('Action')}}</th>
                      </tr>
                  </thead>
                  <tbody>
                  </tbody>               
              </table>
              
              <!-- /.box-body -->
      </div>
      <div class="box-body" id="hsrp-div">
                    <table id="table-hsrp" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                              <th>Id</th>
                              <th>{{__('Type')}}</th>
                              <th>{{__('RTO Type')}}</th>
                              <th>{{__('hirise.rto_finance')}}</th>
                              <th>{{__('hirise.rto_amount')}}</th>
                              <th>{{__('hirise.rto_app_no')}}</th>
                              <th>{{__('Front Lid')}}</th>
                              <th>{{__('Rear Lid')}}</th>
                              <th>{{__('RC Number')}}</th>
                              <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>               
                    </table>
                    <!-- /.box-body -->
            </div>
        </div>
          <!-- Modal -->
              <div class="modal fade" id="rtoModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                  <div class="modal-content" style="margin-top: 200px!important;">
                    <div class="modal-header">
                      <h4 class="modal-title" id="exampleModalLongTitle">Update RC Number</h4>
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                      </button>
                    </div>
                    <div class="modal-body">
                      <form id="my-form"  method="POST">
                        @csrf
                        <div class="row">
                          <div class="col-md-6">
                              <label>RC Number <sup>*</sup></label>
                              <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
                              <input id="rto_id" type="hidden" name="rto_id" class="input-css">
                              <input id="rc_number" type="text" name="rc_number" class="form-control">
                              <br>
                          </div>
                          <div class="col-md-6">
                              <label>Receiving Date <sup>*</sup></label>
                              <input id="receiving_date" type="date" name="receiving_date" class="form-control">
                          </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                      <button type="button" id="btnUpdate" class="btn btn-success">Update</button>
                    </div>
                    </form>
                  </div>
                </div>
              </div>
        <!-- /.box -->
    </section>
@endsection