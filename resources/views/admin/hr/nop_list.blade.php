@extends($layout)

@section('title', __('NOP List'))

@section('breadcrumb')
    <li><a href="#"><i class=""></i>NOP List</a></li>
@endsection
@section('css')
<link rel="stylesheet" href="/css/responsive.bootstrap.css">    
@endsection
@section('js')
<script src="/js/dataTables.responsive.js"></script>
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
            "url": "/admin/hr/user/nop/list/api",
            "datatype": "json",
                "data": function (data) {
                    var store_name = $('#store_name').val();
                    data.store_name = store_name;
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
              { "data": "date" },
              { "data": "time" },
              { "data": "type" },
              {
                  "targets": [ -1 ],
                  data:function(data,type,full,meta)
                  {
                    var x="no";
                    var i=0;
                    var str='';

                    if(data.status=="Rejected"){
                      str= "<b>Rejected</b>";
                     
                    }
                    else if(data.status=="Approved"){
                       str= '<b>Approved</b>';
                    }else if(data.status_reporting_by=='Pending'){
                        var id=data.id;
                        var auth="{{Auth::id()}}";
                        var repoting=data.reporting;
                         var role= "{{Auth::user()->role}}";

                          if(repoting==auth && (role=='HRDManager') || role=='Superadmin'){
                             str= '<a ab onclick="cancel_alert_dailog('+id+','+auth+','+data.emp_id+')"><button class="btn btn-primary btn-xs"> Approve </button></a>';
                          }else if(repoting==auth){
                               str= '<a ab onclick="cancel_alert_dailog1('+id+','+data.emp_id+','+repoting+')"><button class="btn btn-primary btn-xs"> Approve </button></a>';
                          }else{
                          if(data.status_hr_by=='Pending'){
                              var id=data.id;
                              var auth="{{Auth::id()}}";
                              var role= "{{Auth::user()->role}}";
                              if(role=='HRDManager' || role=='Superadmin'){
                                 str= '<a ab onclick="cancel_alert_dailog('+id+','+auth+','+data.emp_id+')"><button class="btn btn-primary btn-xs"> Approve </button></a>';
                              }
                              else{
                                 str= "You Are Not Eligible To Approve Or Reject.";
                              }
                          }
                        }
                  }else if(data.status_hr_by=='Pending'){

                      var id=data.id;
                      var auth="{{Auth::id()}}";
                      var role= "{{Auth::user()->role}}";
                      if(role=='HRDManager' || role=='Superadmin'){
                         str= '<a ab onclick="cancel_alert_dailog('+id+','+auth+','+data.emp_id+')"><button class="btn btn-primary btn-xs"> Approve </button></a>';
                      }
                      else{
                         str= "You Are Not Eligible To Approve Or Reject.";
                      }

                    }else if(data.status_reporting_by!='Pending' && data.status_hr_by=='Pending'){

                       var id=data.id;
                       var auth="{{Auth::id()}}";
                       var role= "{{Auth::user()->role}}";
                       if(role=='HRDManager'){
                         str= '<a ab onclick="cancel_alert_dailog('+id+','+auth+','+data.emp_id+')"><button class="btn btn-primary btn-xs"> Approve </button></a>';
                      }
                      else{
                         str= "You Are Not Eligible To Approve Or Reject.";
                      }   
                   }
                     return  str;                
                  },
                  "orderable": false
              }
            ]
       });
}

// Data Tables'additional_services.id',
   $(document).ready(function() {
    datatablefn();
  });

 function cancel_alert_dailog(id,hr_id,emp_id)
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
                  '<form id="infos" method="POST" action="/admin/hr/user/nop/approve/'+id+'">'+
                    '@csrf'+
                    '<div class="modal-body">'+
                      '<input type="hidden" name="emp_id" value="'+emp_id+'">'+
                      '<input type="hidden" name="hr_id" value="'+hr_id+'">'+
                      '<br><label>Please select Below OPTIONS for NOP Application</label>'+
                      '<label> <input name="status" type="radio" value="Approved" required> Approved.</label>'+
                      '<label> <input name="status" type="radio" value="Rejected" required> Rejected.</label>'+
                      '<label id="status-error" class="error" for="status"></label>'+
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

   function cancel_alert_dailog1(id,emp_id,repoting)
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
                  '<form id="infos" method="POST" action="/admin/hr/user/nop/approve/'+id+'">'+
                    '@csrf'+
                    '<div class="modal-body">'+
                      '<input type="hidden" name="emp_id" value="'+emp_id+'">'+
                      '<input type="hidden" name="repoting" value="'+repoting+'">'+
                      '<br><label>Please select Below OPTIONS for NOP Application</label>'+
                      '<label> <input name="status1" type="radio" value="Approved" required> Approved.</label>'+
                      '<label> <input name="status1" type="radio" value="Rejected" required> Rejected.</label>'+
                      '<label id="status-error" class="error" for="status"></label>'+
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
                </div>  
                <div class="box-body">
                  <table id="admin_table" class="table table-bordered table-striped">
                    <thead>
                    <tr>

                      <th>Name</th>
                      <th>Date</th>
                      <th>Time</th>
                      <th>Type</th>
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