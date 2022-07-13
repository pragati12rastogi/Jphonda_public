@extends($layout)

@section('title', __('Old Battery Summary'))

@section('breadcrumb')
    <li><a href="/admin/stock/parts/list"><i class=""></i>Old Battery Summary</a></li>
@endsection
@section('css')
<link rel="stylesheet" href="/css/responsive.bootstrap.css">    
@endsection
@section('js')
<script src="/js/dataTables.responsive.js"></script>
  <script>
    var dataTable;
      function updateHtr(el){
      var id = $(el).children('i').attr('class');
      $.ajax({  
                url:"/admin/battery/getlist/"+id,  
                method:"get",  
                success:function(data) { 
                 if(data) {
                        $("#htrCode").empty();
                        $.each(data,function(key,value){
                          $("#product_id").val(key);
                           $("#htrCode").val(value);
                        }); 
                        $('#batteryModalCenter').modal("show"); 
                    }    
                }  
           });
    }

     $('#btnUpdate').on('click', function() {
      var pid = $('#product_id').val();
      var htrCode = $('#htrCode').val();
       $.ajax({
        method: "GET",
        url: "/admin/battery/updateOldHtr",
        data: {'id':pid,'htrCode':htrCode},
        success:function(data) {
            $("#"+pid).children('td.htr_code').html(htrCode);
            $('#batteryModalCenter').modal("hide");  
        },
        error:function(data){
          alert('try again');
        }
      });

    });
    // Data Tables
    $(document).ready(function() {
      dataTable = $('#admin_table').DataTable({
          "processing": true,
          "serverSide": true,
          "ajax": "/admin/oldbattery/list/api",
          "aaSorting": [],
          "responsive": true,
          "rowId":"id",
          "columns": [
            { "data": "load_referenace_number" }, 
              // { "data": "model_category" },
              // { "data": "model_name" },
              // { "data": "model_variant" },
              // { "data": "color_code" }, 
              { "data": "frame" },
              // { "data": "type" },
              { "data": "part_type" },
              { "data": "status" }, 
              { "data": "manufacture_date" },
              { "data": "htr_code","className":"htr_code" },
              {
                  "targets": [ -1 ],
                  "data":"id", "render": function(data,type,full,meta)
                  {
                    
                      // return "<a href='#'><button class='btn btn-primary btn-xs'> View </button></a> &nbsp;" + 
                      return '';
                    // return '<a href="#"><button class="btn btn-info btn-xs "  onclick="updateHtr(this)"><i class="'+data+'"></i> Update HTR</button></a> &nbsp;<a href="#"><button class="btn btn-success btn-xs"> Edit</button></a> ' ;
                  },
                  "orderable": false
              }
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
        
        <div class="box box-primary">
            <!-- /.box-header -->
            <div class="box-header with-border">
<!--                    <h3 class="box-title">{{__('customer.mytitle')}} </h3>-->
                </div>  
                <div class="box-body">

                  <table id="admin_table" class="table table-bordered table-striped">
                    <thead>
                    <tr>
                      <th>{{__('parts.refer')}}</th>
                      <!-- <th>{{__('parts.model_cat')}}</th>
                      <th>{{__('parts.model_name')}}</th>
                      <th>{{__('parts.model_var')}}</th>
                      <th>{{__('parts.color')}}</th> -->
                      <th>{{__('Battery Number')}}</th>
                      {{-- <th>{{__('parts.type')}}</th> --}}
                      <th>{{__('Battery Type')}}</th>
                      <th>{{__('parts.status')}}</th>
                      <th>{{__('parts.mfh_d')}}</th>
                      <th>{{__('HTR Number')}}</th>
                      <th>{{__('parts.action')}}</th>                      
                    </tr>
                    </thead>
                    <tbody>

                    </tbody>
               
                  </table>
           
                </div>
                <!-- /.box-body -->
              </div>
           <!-- Modal -->
              <div class="modal fade" id="batteryModalCenter" tabindex="-1" role="dialog" aria-labelledby="batteryModalCenterTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                  <div class="modal-content" style="margin-top: 200px!important;">
                    <div class="modal-header">
                      <h4 class="modal-title" id="batteryModalLongTitle">Update HTR Number</h4>
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                      </button>
                    </div>
                    <div class="modal-body">
                      <form id="my-form" method="POST">
                        @csrf
                        <div class="row">
                          <div class="col-md-6">
                              <label>HTR Number <sup>*</sup></label>
                               <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
                              <input id="product_id" type="hidden" name="id" class="input-css">
                              <input id="htrCode" type="text" name="htrCode" class="input-css">
                              <br>
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