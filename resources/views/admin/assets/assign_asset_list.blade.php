@extends($layout)

@section('title', 'Assets Assign List')

{{-- TODO: fetch from auth --}}
@section('user', Auth::user()->name)

@section('breadcrumb')

    <li><a href="#"><i class=""></i>Assets Assign List</a></li> 
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
            "url": "/admin/assets/assign/employee/list/api",
            "datatype": "json",
                "data": function (data) {
                    var store_name = $('#store_name').val();
                    data.store_name = store_name;
                }
            },
            
            "columns": [
              {"data":"employee"},
              {"data":"asset_code"},
              {"data":"category_name"},
              {"data":"store_name"},
              {"data":"name"},
              {"data":"model_number"},
              {"data":"asset_value"},
              {"data":"from_date"},
              {"data":"to_date", "render": function(data,type,full,meta)
                  {
                    console.log(data);
                    if(data == "1970-01-01")
                      return "";
                    else  
                      return data;
                  },},
                   {"data":"asset_form", "render": function(data,type,full,meta)
                  {
                    console.log(data);
                    if(data == "")
                      return "";
                    else  
                      return '<a href="/upload/assets/form/'+data+'" target="_blank">See Asset Receipt Form</a>';
                  },},
                  {"data":"status", "render": function(data,type,full,meta)
                  {
                    
                    if(data == null)
                      return "Assigned";
                    else  
                      return data;
                  },},
              {
                  "targets": [ -1 ],
                  "data":"aa_id", "render": function(data,type,full,meta)
                  {
                    if(full.status == null || full.status == ""){
                      return "<a href='/admin/asset/return/"+data+"' target='_blank'><button class='btn btn-primary btn-xs'> Return </button></a> &nbsp;"
                      ;
                  }else{
                    return"";
                  }
                }
              }
              
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

    // // Data Tables
    // $(document).ready(function() {
    //   dataTable = $('#table').DataTable({
    //       "processing": true,
    //       "serverSide": true,
    //       "aaSorting":[],
    //       "responsive": true,
    //       "ajax": "/admin/assets/assign/employee/list/api",
    //       "columns": [
    //           {"data":"employee"},
    //           {"data":"asset_code"},
    //           {"data":"category_name"},
    //           {"data":"name"},
    //           {"data":"model_number"},
    //           {"data":"asset_value"},
    //           {"data":"from_date"},
    //           {"data":"to_date", "render": function(data,type,full,meta)
    //               {
    //                 console.log(data);
    //                 if(data == "1970-01-01")
    //                   return "";
    //                 else  
    //                   return data;
    //               },},
    //                {"data":"asset_form", "render": function(data,type,full,meta)
    //               {
    //                 console.log(data);
    //                 if(data == "")
    //                   return "";
    //                 else  
    //                   return '<a href="/upload/assets/form/'+data+'" target="_blank">See Asset Receipt Form</a>';
    //               },},
    //               {"data":"status", "render": function(data,type,full,meta)
    //               {
                    
    //                 if(data == null)
    //                   return "Assigned";
    //                 else  
    //                   return data;
    //               },},
    //           {
    //               "targets": [ -1 ],
    //               "data":"aa_id", "render": function(data,type,full,meta)
    //               {
    //                 if(full.status == null || full.status == ""){
    //                   return "<a href='/admin/asset/return/"+data+"' target='_blank'><button class='btn btn-primary btn-xs'> Return </button></a> &nbsp;"
    //                   ;
    //               }else{
    //                 return"";
    //               }
    //             }
    //           }
              
    //         ],
    //         "columnDefs": [
             
    //           { "orderable": false, "targets": 10 }
    //         ]
          
    //     });
    // });


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
                    <a href="{{url('/admin/assets/assign/employee')}}"><button class="btn btn-primary">Assign Assets</button></a>
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

                      <th>Employee Name</th>
                      <th>Assets Code</th>
                      <th>Assets Category</th>
                      <th>Store Name</th>
                      <th>Assets Name</th>
                      <th>Assets Model Number</th>
                      <th>Asset Value</th>
                      <th>From Date</th>
                      <th>To Date</th>
                      <th>Asset Form</th>
                      <th>Status</th>
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