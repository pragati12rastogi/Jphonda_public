@extends($layout)

@section('title', 'Incentive Program List')

{{-- TODO: fetch from auth --}}
@section('user', Auth::user()->name)

@section('breadcrumb')

    <li><a href="#"><i class=""></i>Incentive Program List</a></li> 
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
  
</style>
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
        
       dataTable = $('#incentive').DataTable({
         "processing": true,
         "serverSide": true,
         "ajax": "/admin/incentive/program/list/api",
         "aaSorting": [],
         "responsive": true,  
          "columns": [
              {"data":"id"},
              {"data":"type"},
              {"data":"start_date"},
              {"data":"end_date"},
              {"data":"required_condition"},
              {"data":"required_qty"},
              {"data":"incentive_store"},
              {"data":"incent_status"}
            ]
       });
}
   $(document).ready(function() {
    datatablefn();
  });
  

  </script>
@endsection

@section('main_section')
    <section class="content">
            <div id="app">
                    @include('admin.flash-message')
                    @yield('content')
        <!-- Default box -->
        <div class="box">
                <!-- /.box-header -->
                <div class="box-body">
                    @section('titlebutton')
                    
                    @endsection
                    
                    <table id="incentive" class="table table-bordered table-striped">
                    <thead>
                    <tr>
                      <th>Id</th>
                      <th>Type</th>
                      <th>Start Date</th>
                      <th>End Date</th>
                      <th>Required Condition</th>
                      <th>Required Qty</th>
                      <th>Incentive Store</th>
                      <th>Status</th>
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