@extends($layout)

@section('title', __('Part Movement Process Summary'))

{{-- TODO: fetch from auth --}}
@section('user', Auth::user()->name)

@section('breadcrumb')

    <li><a href="#"><i class=""></i>{{__('Part Movement Process Summary')}}</a></li> 
@endsection
@section('css')
<style>
   .content{
    padding: 30px;
  }
  .nav1>li>button {
    position: relative;
    display: block;
    padding: 10px 34px;
    background-color: white;
    margin-left: 10px;
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
  
</style>
<link rel="stylesheet" href="/css/responsive.bootstrap.css">    
@endsection
@section('js')
<script src="/js/dataTables.responsive.js"></script>
  <script>
    var dataTable;
    var CSRF = $("meta[name='csrf-token']").attr('content');

    function getdata(){
        
        if(dataTable)
            dataTable.destroy();

        dataTable = $('#partmovementSummary').DataTable({
          "processing": true,
          "serverSide": true,
          "aaSorting":[],
          "responsive": true,
          "ajax": "/admin/part/movement/process/summary/api",
          "columns": [
                
                {"data":"process_id"},
                {"data":"loader_truck"},
                {"data":"fromStore"}, 
                {"data":"toStore"}, 
                {"data": "parts_qty"},
                {"data":"moved_date", "render": function(data,type,full,meta)
                  {
                      if(data == '00-00-0000' || data == '01-01-1970'){
                        str ="";
                      }else{
                        str = data;
                      }
                      return str;
                  }}, 
                {"data":"userName"},
                {"data":"status"},
                {
                  "targets": [ -1 ],
                  "data":"process_id", "render": function(data,type,full,meta)
                  {
                    var str = '';
                    if (full.status == 'Pending') {
                       str += ' &nbsp; <a href="#" id="'+data+'"><button class="btn btn-success btn-xs" onclick ="PartMovement(this)">Move</button></a>' ;
                       str += ' &nbsp; <a href="#" id="'+data+'"><button class="btn btn-danger btn-xs" onclick ="PartMovementCancel(this)">Cancel</button></a>' ;
                    }
                    
                      return str;
                  },
                  "orderable": false
              }
                
            ],
            
          
        });
    }
   

    function PartMovement(el){
      if (confirm("Are You sure?")){
        var id = parseInt($(el).parent('a').attr('id'));
        if (id) {
          $("#loader").show();
          $.ajax({  
                url:"/admin/partmovement/summary/movement/"+id,  
                method:"get",  
                success:function(result){ 
                  $("#loader").hide(); 
                  if(result == 'success') {
                  $('#partmovementSummary').dataTable().api().ajax.reload();
                  $("#js-msg-success").html('Part Movement Done Successfully.');
                  $("#js-msg-success").show().fadeOut(10000); 
                }else {
                  $("#js-msg-error").html(result);
                  $("#js-msg-error").show().fadeOut(10000);
                }
                }  
           });
       }
      }
   }

   function PartMovementCancel(el){
      if (confirm("Are You sure?")){
        var id = parseInt($(el).parent('a').attr('id'));
        if (id) {
          $("#loader").show();
          $.ajax({  
                url:"/admin/part/movement/process/cancel/"+id,  
                method:"get",  
                success:function(result){debugger 
                  $("#loader").hide(); 
                  if(result == 'success') {
                  $('#partmovementSummary').dataTable().api().ajax.reload();
                  $("#js-msg-success").html('Part Movement Cancel Successfully.');
                  $("#js-msg-success").show().fadeOut(10000); 
                }else {
                  $("#js-msg-error").html(result);
                  $("#js-msg-error").show().fadeOut(10000);
                }
                }  
           });
       }
      }
   }

    $(document).ready(function() {
        getdata();
    });


  </script>
@endsection

@section('main_section')
    <section class="content">
        <div id="app">
            @include('admin.flash-message')
            @yield('content')
            
        </div>

        <div class="alert alert-danger" id="js-msg-error" style="display:none;">
        </div>
        <div class="alert alert-success" id="js-msg-success" style="display:none;">
          
        </div>
    <!-- Default box -->
        <div class="box">
                <!-- /.box-header -->
            <div class="box-body">
            
                    <table id="partmovementSummary" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                              <th>Id</th>
                              <th>Loader</th>
                              <th>From Store Name</th>
                              <th>To Store Name</th>
                              <th>Parts</th>
                              <th>Move Date</th>
                              <th>Move By</th>
                              <th>Status</th>
                              <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>               
                    </table>
                    <!-- /.box-body -->
            </div>
        </div>
        <!-- /.box -->
    </section>
@endsection