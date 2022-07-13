@extends($layout)

@section('title', 'Leaves Levels Authority Summary')

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
   
       dataTable = $('#delivery_challan_list_table').DataTable({
          "processing": true,
          "serverSide": true,
          "aaSorting":[],
          "pageLength": 50,
          "responsive": true,
          "ajax": "/admin/hr/leave/setting/list/api",
          "columns": [
           {
                  "targets": [ -1 ],
                  data:function(data,type,full,meta)
                  {
                    return data.level1;
                  }
           }
           
            ],
            "columnDefs": [
              { "orderable": false, "targets": 0 }
            ]
          
        });
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
            @section('titlebutton')
            <a href="{{url('/admin/hr/setting')}}"><button class="btn btn-primary">Settings</button></a>

            @endsection
            <div class="box-header with-border">
            </div>
            <div class="box-body">
              <table id="delivery_challan_list_table" class="table table-bordered table-striped">
                  <thead>
                  <tr>
                    <th>Level 1</th>
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