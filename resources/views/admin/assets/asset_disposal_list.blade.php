@extends($layout)

@section('title', 'Asset Disposal List')

{{-- TODO: fetch from auth --}}
@section('user', Auth::user()->name)

@section('breadcrumb')

    <li><a href="#"><i class=""></i>Asset Disposal List</a></li> 
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
        
       dataTable = $('#table').DataTable({
         "processing": true,
         "serverSide": true,
         "ajax": "",
         "aaSorting": [],
         "responsive": true,
          "ajax": {
            "url": "/admin/assets/disposal/list/api",
            "datatype": "json",
                "data": function (data) {
                    var store_name = $('#store_name').val();
                    data.store_name = store_name;
                }
            },
            
           "columns": [
              {"data":"category_name"},
              {"data":"asset_code"},
              {"data":"asset"},
              {"data":"asset_value"},
              {"data":"store_name"},
              {"data":"employee"},
              {"data":"disposal_on"},
              {"data":"disposal_reason"},
                 
            ]
       });
}

// Data Tables'additional_services.id',
   $(document).ready(function() {
    datatablefn();
  });
   $('#store_name').on( 'change', function () {
      dataTable.draw();
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
                    <a href="{{url('/admin/assets/disposal')}}"><button class="btn btn-primary">Disposal Assets</button></a>
                      @endsection
                                        <div class="row">
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
                    <table id="table" class="table table-bordered table-striped">
                    <thead>
                    <tr>

                      <th>Assets Category</th>
                      <th>Assets Code</th>
                      <th>Asset Name</th>
                      <th>Asset Value</th>
                      <th>Store Name</th>
                      <th>Disposal To</th>
                      <th>Disposal On</th>
                      <th>Disposal Reason</th>
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