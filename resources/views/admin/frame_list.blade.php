@extends($layout)

@section('title', __('Frame List'))

@section('breadcrumb')

    <li><a href="#"><i class=""></i>{{__('Frame List')}}</a></li> 
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

    function getunloaddata(){
        
        if(dataTable)
            dataTable.destroy();

        dataTable = $('#table').DataTable({
          "processing": true,
          "serverSide": true,
          "aaSorting":[],
          "responsive": true,
          "ajax": {
            "url": "/admin/frame/list/api",
            "datatype": "json",
                "data": function (data) {
                    var store_name = $('#store_name').val();
                    data.store_name = store_name;
                    var store_count = $('#store_count').val();
                    data.store_count = store_count;
                }
            },
          "columns": [
                {"data":"load_referenace_number"}, 
                {"data":"store"},
                {"data":"frame"}, 
                {"data":"engine_number"},
                {"data":"model_name"},
                {"data":"model_variant"},
                {"data":"color_code"},
                {"data":"status"},
                {"data":"manufacture_date"}, 
                {"data":"htr_code"}
               
                
            ]
        });
    }
   
    $(document).ready(function() {
        getunloaddata();
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
        </div>
    <!-- Default box -->
        <div class="box">
                <!-- /.box-header -->
            <div class="box-body">
                <div class="row">
                   <input type="hidden" name="store_count" id="store_count"  value="{{count($store)}}">
                    <div class="col-md-8" style="{{(count($store) == 1)? 'display:none' : 'display:block'}};float: right;">
                       <label>Store Name </label>
                       @if(count($store) > 1)
                          <select name="store_name[]" multiple="multiple" data-placeholder="Select Store"  class="input-css select2 selectValidation" id="store_name">
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
                                <th>Load Referenace Number</th>
                                <th>Store Name</th>
                                <th>Frame Number</th>
                                <th>Engine Number</th>
                                <th>Model Name</th>
                                <th>Model Variant</th>
                                <th>Color Code</th>
                                <th>Status</th>
                                <th>Manufacture Date</th>
                                <th>HTR Code</th>
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