@extends($layout)

@section('title', 'Leaves Summary')

{{-- TODO: fetch from auth --}}
@section('user', Auth::user()->name)

@section('breadcrumb')

    <li><a href="#"><i class=""></i>Leaves Summary</a></li> 
@endsection
@section('css')
<style>
   .content{
    padding: 30px;
  }
 
@media (max-width: 768px)  
  {
    
    .content-header>h1 {
      display: inline-block;
     
    }
  }
  @media (max-width: 425px)  
  {
   
    .content-header>h1 {
      display: inline-block;
      
    }
  }
   .nav-pills>li {
    
    border: 1px solid #a9a0a0;
}

</style>
<link rel="stylesheet" href="/css/responsive.bootstrap.css">    
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
            
        }
    });
</script>
  <script>
    var dataTable;
    var hr;
    $(document).ready(function()  {
     hr="{{$hr}}";
     hr=hr.split(",");
     console.log(hr);
     
       dataTable = $('#delivery_challan_list_table').DataTable({
          "processing": true,
          "serverSide": true,
          "aaSorting":[],
          "pageLength": 50,
          "responsive": true,
          "ajax": "/admin/hr/leave/list/api",
          "createdRow": function( row, data, dataIndex){
                if( data.leave_status=="Approved"){
                  $(row).css('background-color','#73AC80');
                    
                    
                }
                if(data.leave_status=="Cancelled"){
                  $(row).css('background-color','#F0A4AC');
                }
            },
          "columns": [
            {"data":"emp",
              "data":"emp", "render": function(data,type,full,meta)
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
            { "data": "leave_apply_date" }, 
            { "data": "email" }, 
            { "data": "contact" }, 
            {"data":"start_date"},
            {"data":"end_date"},
            {"data":"reason"},
            {"data":"leave_status"},
            {"data":"status_level1"},
            {"data":"level1"},
            {"data":"status_level2"},
            {"data":"level2"},
            {
                  "targets": [ -1 ],
                  data:function(data,type,full,meta)
                  {
                    // return "111";
                    var x="no";
                    var i=0;
                    var str='';
                    if(data.leave_status=="Cancelled" ){
                      str= "<b>Cancelled</b>"+'<a href="/admin/hr/leave/print/'+data.id+'" target="_blank"><button class="btn btn-danger btn-xs"> Print </button></a> &nbsp;';
                     
                    }
                    else if(data.leave_status=="Approved"){
                      str= '<b>Approved</b>'+'&nbsp;'+
                      '<a href="/admin/hr/leave/print/'+data.id+'" target="_blank"><button class="btn btn-danger btn-xs"> Print </button></a> &nbsp;'
                      ;
                    }
                    else if(data.status_level2=='Pending'){

                      var id=data.id;
                      var auth="{{Auth::id()}}";
                      var repoting=data.reporting;
                      for (var index = 0; index < hr.length; index++) {
                                var ind=hr[index];
                                  if(auth==ind){
                                    i=hr[index];
                                    var level=1;
                                    x= "yes" ;
                                  }
                              }

                      if(repoting==auth && x=="yes"){
                         str= '<a ab onclick="cancel_alert_dailog('+i+','+id+')"><button class="btn btn-primary btn-xs"> Approve </button></a> &nbsp;'+
                                '<a href="/admin/hr/leave/print/'+data.id+'" target="_blank"><button class="btn btn-danger btn-xs"> Print </button></a> &nbsp;';
                      }
                      else if(repoting==auth){
                        str= '<a onclick="cancel_alert_dailog1('+id+','+repoting+')"><button class="btn btn-primary btn-xs"> Approve </button></a> &nbsp;'+
                        '<a href="/admin/hr/leave/print/'+data.id+'" target="_blank"><button class="btn btn-danger btn-xs"> Print </button></a> &nbsp;';
                      }else{
                          if(data.status_level1=='Pending'){
                              var id=data.id;
                              var auth="{{Auth::id()}}";
                              for (var index = 0; index < hr.length; index++) {
                                var ind=hr[index];
                                  if(auth==ind){
                                    i=hr[index];
                                    var level=1;
                                    x= "yes" ;
                                  }
                              }
                              if(x=="yes"){
                                str= '<a ab onclick="cancel_alert_dailog('+i+','+id+')"><button class="btn btn-primary btn-xs"> Approve </button></a> &nbsp;'+
                                '<a href="/admin/hr/leave/print/'+data.id+'" target="_blank"><button class="btn btn-danger btn-xs"> Print </button></a> &nbsp;';
                              }else{
                                str= "You Are Not Eligible To Approve Or Cancel Leave Application."+
                                '<a href="/admin/hr/leave/print/'+data.id+'" target="_blank"><button class="btn btn-danger btn-xs"> Print </button></a> &nbsp;';
                              }

                          }
                        }
                  }else if(data.status_level1=='Pending' ){

                      var id=data.id;
                      var auth="{{Auth::id()}}";
                      for (var index = 0; index < hr.length; index++) {
                        var ind=hr[index];
                          if(auth==ind){
                            i=hr[index];
                            var level=1;
                            x= "yes" ;
                          }
                      }
                      if(x=="yes"){
                        str= '<a ab onclick="cancel_alert_dailog('+i+','+id+')"><button class="btn btn-primary btn-xs"> Approve </button></a> &nbsp;'+
                        '<a href="/admin/hr/leave/print/'+data.id+'" target="_blank"><button class="btn btn-danger btn-xs"> Print </button></a> &nbsp;';
                      }
                      else{
                        str= "You Are Not Eligible To Approve Or Cancel Leave Application."+
                        '<a href="/admin/hr/leave/print/'+data.id+'" target="_blank"><button class="btn btn-danger btn-xs"> Print </button></a> &nbsp;';
                      }
                    }

                    else if(data.status_level2!='Pending' && data.leave_status=='Pending'){

                      var id=data.id;
                      var auth="{{Auth::id()}}";
                      for (var index = 0; index < hr.length; index++) {
                        var ind=hr[index];
                          if(auth==ind){
                            i=hr[index];
                            var level=1;
                            x= "yes" ;
                          }
                      }
                      if(x=="yes"){
                        str= '<a ab onclick="cancel_alert_dailog('+i+','+id+')"><button class="btn btn-primary btn-xs"> Approve </button></a> &nbsp;'+
                        '<a href="/admin/hr/leave/print/'+data.id+'" target="_blank"><button class="btn btn-danger btn-xs"> Print </button></a> &nbsp;';
                      }
                      else{
                        str= "You Are Not Eligible To Approve Or Cancel Leave Application."+
                        '<a href="/admin/hr/leave/print/'+data.id+'" target="_blank"><button class="btn btn-danger btn-xs"> Print </button></a> &nbsp;';
                      }
                    }
                    return str;
                  },
                  "orderable": false
              }
            ],
            "columnDefs": [
              
            ]
          
        });
    });
    
    function cancel_alert_dailog(id,leave_id)
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
                  '<form id="infos" method="POST" action="/admin/hr/leave/approve/'+id+'">'+
                    '@csrf'+
                    '<div class="modal-body">'+
                      '<input type="hidden" name="leave_id" value="'+leave_id+'">'+
                      '<br><label>Please select Below OPTIONS for Leave Application</label>'+
                      '<label> <input name="status" type="radio" value="Approved" required> Approved.</label>'+
                      '<label> <input name="status" type="radio" value="Cancelled" required> Cancelled.</label>'+
                      '<label id="status-error" class="error" for="status"></label>'+
                      '<br><br><input type="text" name="remark" class="input-css" placeholder="Please Enter Remark" required>'+
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

     function cancel_alert_dailog1(leave_id,repoting)
    {
      $('#modal_div').empty().append(
            '<div id="myModal1" class="modal fade" role="dialog">'+
              '<div class="modal-dialog modal-lg">'+
                '<!-- Modal content-->'+
                '<div class="modal-content">'+
                  '<div class="modal-header">'+
                    '<button type="button" class="close" data-dismiss="modal">&times;</button>'+
                    '<h4 class="modal-title">Approval/Rejection</h4>'+
                  '</div>'+
                  '<form id="infos" method="POST" action="/admin/hr/leave/approve/'+leave_id+'">'+
                    '@csrf'+
                    '<div class="modal-body">'+
                      '<input type="hidden" name="leave_id" value="'+leave_id+'">'+
                      '<input type="hidden" name="repoting" value="'+repoting+'">'+
                      '<br><label>Please select Below OPTIONS for Leave Application</label>'+
                      '<label> <input name="status1" type="radio" value="Approved" required> Approved.</label>'+
                      '<label> <input name="status1" type="radio" value="Cancelled" required> Cancelled.</label>'+
                      '<label id="status-error" class="error" for="status"></label>'+
                      '<br><br><input type="text" name="remark" class="input-css" placeholder="Please Enter Remark" required>'+
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
          $(document).find('#myModal1').modal("show"); 
  }
  </script>
@endsection

@section('main_section')
    <section class="content">
            <div id="app">
                    @include('admin.flash-message')
                    @yield('content')
        <!-- Default box -->
        <div class="box">
            <div id="modal_div"></div>
            <div class="box-header with-border">
            </div>
            <div class="box-body">
              <table id="delivery_challan_list_table" class="table table-bordered table-striped">
                  <thead>
                  <tr>
                    <th>Employee Name</th>
                    <th>Leave Apply Date</th>
                    <th>Email</th>
                    <th>Contact</th>
                    <th>From Date</th>
                    <th>To Date</th>
                    <th>Reason</th>
                    <th>Level Status</th>
                    <th>Status Level1</th>
                    <th>Status Level1 By</th>
                    <th>Status Level2</th>
                    <th>Status Level2 By</th>
                    <th>Action</th>
                  </tr>
                  </thead>
                  <tbody>
  
                  </tbody>
             
              </table>
            </div>
          </div>
        <!-- /.box -->
      </section>
@endsection