@extends($layout)

@section('title', 'Assets List')

{{-- TODO: fetch from auth --}}
@section('user', Auth::user()->name)

@section('breadcrumb')

    <li><a href="#"><i class=""></i>Assets List</a></li> 
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
            "url": "/admin/assets/list/api",
            "datatype": "json",
                "data": function (data) {
                    var store_name = $('#store_name').val();
                    data.store_name = store_name;
                }
            },
            
          "columns": [
              {"data":"asset_code"},
              {"data":"category_name"},
              {"data":"name"},
              {"data":"brand"},
              {"data":"asset_bill_no"},
              {"data":"model_number"},
              // {"data":"description"},
              {"data":"asset_value"},
              {"data":"store_name"},
              {"data":"allot_status", "render": function(data,type,full,meta)
                  {
                    
                    if(data == "not assign")
                      return "";
                    else if(data == "Assigned") 
                      return data;
                    else if(data == "Disposed")
                      return data;
                  },},
                {
                  "targets": [ -1 ],
                  "data":"asset_id", "render": function(data,type,full,meta)
                  {
                    
                    // var x="";
                    // if(full.asset_bill_upload != "" && full.asset_bill_upload != null){
                    //   x= "<a href='/upload/assets/"+full.asset_bill_upload+"' target='_blank'><button class='btn btn-success btn-xs'>Bill</button></a> &nbsp;";
                    // }else{
                    //    x='';
                    // }
                    // if(full.asset_photo_upload != "" && full.asset_photo_upload != null){
                    //   x+= "<a href='/upload/assets/"+full.asset_photo_upload+"' target='_blank'><button class='btn btn-flickr btn-xs'>Image</button></a> &nbsp;";
                    // }else{
                    //    x+='';
                    // }
                    return "<a href='/admin/assets/edit/"+data+"' target='_blank'><button class='btn btn-primary btn-xs'>Edit</button></a> &nbsp;"
                    + "<a href='/admin/assets/view/"+data+"' target='_blank'><button class='btn btn-flickr btn-xs'>View</button></a> &nbsp;"
                    ;
                  }
              }
            ]
       });
}
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
    //       "ajax": "/admin/assets/list/api",
    //       "createdRow": function( row, data, dataIndex){
    //             if( data.allot_status ==  'Disposed'){
    //                 $(row).addClass('bg-gray');
    //             }
    //         },
    //       "columns": [
    //           {"data":"asset_code"},
    //           {"data":"category_name"},
    //           {"data":"name"},
    //           {"data":"brand"},
    //           {"data":"asset_bill_no"},
    //           {"data":"model_number"},
    //           // {"data":"description"},
    //           {"data":"asset_value"},
    //           {"data":"allot_status", "render": function(data,type,full,meta)
    //               {
                    
    //                 if(data == "not assign")
    //                   return "";
    //                 else if(data == "Assigned") 
    //                   return data;
    //                 else if(data == "Disposed")
    //                   return data;
    //               },},
    //             {
    //               "targets": [ -1 ],
    //               "data":"asset_id", "render": function(data,type,full,meta)
    //               {
                    
    //                 // var x="";
    //                 // if(full.asset_bill_upload != "" && full.asset_bill_upload != null){
    //                 //   x= "<a href='/upload/assets/"+full.asset_bill_upload+"' target='_blank'><button class='btn btn-success btn-xs'>Bill</button></a> &nbsp;";
    //                 // }else{
    //                 //    x='';
    //                 // }
    //                 // if(full.asset_photo_upload != "" && full.asset_photo_upload != null){
    //                 //   x+= "<a href='/upload/assets/"+full.asset_photo_upload+"' target='_blank'><button class='btn btn-flickr btn-xs'>Image</button></a> &nbsp;";
    //                 // }else{
    //                 //    x+='';
    //                 // }
    //                 return "<a href='/admin/assets/edit/"+data+"' target='_blank'><button class='btn btn-primary btn-xs'>Edit</button></a> &nbsp;"
    //                 + "<a href='/admin/assets/view/"+data+"' target='_blank'><button class='btn btn-flickr btn-xs'>View</button></a> &nbsp;"
    //                 ;
    //               }
    //           }
    //         ],
    //         "columnDefs": [
             
    //           { "orderable": false, "targets": 8 }
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
                    <a href="{{url('/admin/assets/create')}}"><button class="btn btn-primary">Create Assets</button></a>
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
                      <th>Assets Code</th>
                      <th>Assets Category</th>
                      <th>Assets Name</th>
                      <th>Assets Brand</th>
                      <th>Assets Bill Number</th>
                      <th>Assets Model Number</th>
                      <!-- <th>Description</th> -->
                      <th>Asset Value</th>
                      <th>Store Name</th>
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