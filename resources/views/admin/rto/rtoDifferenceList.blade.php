@extends($layout)

@section('title', __('RTO Difference List'))

@section('user', Auth::user()->name)

@section('breadcrumb')

    <li><a href="#"><i class=""></i>{{__('RTO Difference List')}}</a></li> 
@endsection
@section('css')

<link rel="stylesheet" href="/css/responsive.bootstrap.css">
<style>

</style>

@endsection
@section('js')
<script src="/js/dataTables.responsive.js"></script>
<script>
  var dataTable;
   
    $("#js-msg-error").hide();
    $("#js-msg-success").hide();

    rot_difference_list();
    function rot_difference_list()
    {

      if(dataTable)
      {
        dataTable.destroy();
      }
      dataTable = $('#table-allDiff').DataTable({
        "processing": true,
         "serverSide": true,
         "ajax": "/admin/rto/difference/amount/list/api",
         "aaSorting": [],
         "responsive": true,
         "rowId":"id",
         "columns": [
            { "data": "sale_no" },
            { "data": "customer_name" },
            { "data": "frame" },
            { "data": "rto_type",
              "render": function (data, type, row) {
                    if (data) {
                        var str = data;
                        var string = str.replace(/_/g, ' ');
                        return string.charAt(0).toUpperCase() + string.slice(1);
                      } 
                      return '';
                }
              },
            { "data": "application_number" },
            { "data": "rto_amount_in_sale" },
            { "data": "rto_amount" },
            { "data": "difference_rto_amount" },
            // {
            //      "targets": [ -1 ],
            //      "data":"id", "render": function(data,type,full,meta)
            //      {
            //        return '<a href="#"><button class="btn btn-info btn-xs ">Action</button></a>';
            //      },
            //      "orderable": false
            // }
           ]
         
       });
    }
 


</script>
@endsection

@section('main_section')
    <section class="content">
        <div id="app">
            @include('admin.flash-message')
            @yield('content')
            <div class="row">
            <div class="alert alert-danger" id="js-msg-error">
            </div>
            <div class="alert alert-success" id="js-msg-success">
            </div>
          </div>
            
        </div>
        
    <!-- Default box -->
        <div class="box">
          <div class="box-header with-border">
            {{-- 
            <h3 class="box-title">{{__('customer.mytitle')}} </h3>
            --}}
            {{-- <ul class="nav nav1 nav-pills">
              <li class="nav-item">
                <button class="nav-link1 btn-color-active" id="pending" >Pending RC</button>
              </li>
              <li class="nav-item">
                <button class="nav-link1 btn-color-unactive" id="done" >Received RC</button>
              </li> 
            </ul> --}}
          </div>
                <!-- /.box-header -->
            <div class="box-body" >
                    <table id="table-allDiff" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                              <th>{{__('Sale Number')}}</th>
                              <th>{{__('Customer Name')}}</th>
                              <th>{{__('Frame #')}}</th>
                              <th>{{__('RTO Type')}}</th>
                              <th>{{__('RTO Application #')}}</th>
                              <th>{{__('RTO Amount in Sale')}}</th>
                              <th>{{__('RTO Amount in Create RTO')}}</th>
                              <th>{{__('Difference RTO Amount')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>               
                    </table>
                    <!-- /.box-body -->
            </div>
        </div>
          <!-- Modal -->
              <div class="modal fade" id="rtoModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                  <div class="modal-content" style="margin-top: 200px!important;">
                    <div class="modal-header">
                      <h4 class="modal-title" id="exampleModalLongTitle">Update RC Number</h4>
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                      </button>
                    </div>
                    <div class="modal-body">
                      
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                      <button type="button" id="btnUpdate" class="btn btn-success">Update</button>
                    </div>
                  </div>
                </div>
              </div>
        <!-- /.box -->
    </section>
@endsection