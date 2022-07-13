@extends($layout)

@section('title', __('Pending Number Plate'))

@section('user', Auth::user()->name)

@section('breadcrumb')

    <li><a href="#"><i class=""></i>{{__('Pending Number Plate')}}</a></li> 
@endsection
@section('css')

<link rel="stylesheet" href="/css/responsive.bootstrap.css">    
@endsection
@section('js')
<script src="/js/dataTables.responsive.js"></script>
<script>
  var dataTable;

  // $(document).ready(function() {
    function pendingRto(){
      $("#rto-div").css('display','block');
      $("#hsrp-div").css('display','none');
      if(dataTable) {
        dataTable.destroy();
      }
      dataTable = $('#rto_table').DataTable({
        "processing": true,
         "serverSide": true,
         "ajax": "/admin/rto/list/numberPlate/pending",
         "aaSorting": [],
         "responsive": true,
         "columns": [
            { "data": "sale_no" },
            { "data": "main_type" },
            { "data": "application_number" },
            { "data": "rto_amount" },
            { "data": "registration_number" },
            { "data": "amount" },
            { "data": "penalty_charge" },
            { "data": "front_lid" },
            { "data": "rear_lid" },
            {
                 "targets": [ -1 ],
                 "data":"id", "render": function(data,type,full,meta)
                 {
                //    return '<a href="/admin/sale/rto/'+data+'"><button class="btn btn-info btn-xs"> Create RTO</button></a> ';
                    return '';
                 },
                 "orderable": false
             }
           ]
         
       });
  };

   function PendingHsrp(){
      $("#rto-div").css('display','none');
      $("#hsrp-div").css('display','block');
      if(dataTable) {
        dataTable.destroy();
      }
      dataTable = $('#hsrp_table').DataTable({
        "processing": true,
         "serverSide": true,
         "ajax": "/admin/rto/hsrp/list/numberPlate/pending",
         "aaSorting": [],
         "responsive": true,
         "columns": [
            { "data": "name" },
            { "data": "main_type" },
            { "data": "application_number" },
            { "data": "rto_amount" },
            { "data": "registration_number" },
            { "data": "amount" },
            { "data": "penalty_charge" },
            { "data": "front_lid" },
            { "data": "rear_lid" },
            {
                 "targets": [ -1 ],
                 "data":"id", "render": function(data,type,full,meta)
                 {
                //    return '<a href="/admin/sale/rto/'+data+'"><button class="btn btn-info btn-xs"> Create RTO</button></a> ';
                    return '';
                 },
                 "orderable": false
             }
           ]
         
       });
  };
 
 pendingRto();

     
      $("#hsrp").on('click',function(){
        $(this).css('background', 'rgb(135, 206, 250)');
        $("#rto").css('background', '#fff');
        $(this).addClass('btn-color-active');
        PendingHsrp();
      });

      $("#rto").on('click',function(){
        $(this).css('background', 'rgb(135, 206, 250)');
        $("#hsrp").css('background', '#fff');
        $(this).addClass('btn-color-active');
        pendingRto();
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
            <form action="/admin/rto/import/numberPlate/pending" method="POST" files="true" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-md-5">
                        <label>Enter Start Row Number</label>
                        <input type="number" name="startRow" id="startRow" class="input-css">
                        {!! $errors->first('startRow', '<p class="help-block">:message</p>') !!}
                        <p style="color:red">Hint : Enter row number, where data has been started. for example :- 8</p>
                            <p>Download Sample ? 
                            <a href="{{route('sampleImport',['filename'  =>  'sampleImportNumberPlates.xlsx'])}}">
                                <button type="button" class="btn btn-primary"> <i class="fa fa-download"></i> </button>
                            </a>
                        </p>
                        
                    </div>
                    <div class="col-md-4">
                        <label>File Upload</label>
                        <input type="file" name="fileUpload" id="fileUpload" class="input-css">
                        {!! $errors->first('fileUpload', '<p class="help-block">:message</p>') !!}

                    </div>
                </div><br>
                <div class="row" style="padding-left:10px;">
                    <button type="submit" class="btn btn-success">Upload</button>
                </div>
            </form>
            <div class="box-header with-border">
              <ul class="nav nav1 nav-pills"> 
                <li class="nav-item">
                  <button class="nav-link1 btn-color-active" id="rto" >RTO List</button>
                </li>
                <li class="nav-item">
                  <button class="nav-link1 btn-color-anactive" id="hsrp" >New Correction </button>
                </li> 
              </ul>
            </div>
                <!-- /.box-header -->
            <div class="box-body">
                <div id="rto-div" class="box-body" style="display:none;">
                    <table id="rto_table" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Sale Number</th>
                                <th>Rto Type</th>
                                <th>Application Number</th>                 
                                <th>RTO Amount</th>                 
                                <th>Number Plate</th>                 
                                <th>Amount</th>                 
                                <th>Penality Charge</th>                 
                                <th>Front LID</th>
                                <th>Rear LID</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>               
                    </table>
                </div>
                <div id="hsrp-div" class="box-body" style="display:none;">
                    <table id="hsrp_table" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Rto Type</th>
                                <th>Application Number</th>                 
                                <th>RTO Amount</th>                 
                                <th>Number Plate</th>                 
                                <th>Amount</th>                 
                                <th>Penality Charge</th>                 
                                <th>Front LID</th>
                                <th>Rear LID</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>               
                    </table>
                </div>
                    <!-- /.box-body -->
            </div>
        </div>
        <!-- /.box -->
    </section>
@endsection