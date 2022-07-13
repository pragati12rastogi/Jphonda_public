@extends($layout)

@section('title', __('RC Correction List'))

@section('user', Auth::user()->name)

@section('breadcrumb')

    <li><a href="#"><i class=""></i>{{__('RC Correction List')}}</a></li> 
@endsection
@section('css')
<link rel="stylesheet" href="/css/responsive.bootstrap.css">  
<style>
tr.dangerClass{
  background-color: #FFCDD2 !important;
}
tr.successClass{
  background-color: #C8E6C9 !important;
}
</style>   
@endsection
@section('js')
<script src="/js/dataTables.responsive.js"></script>
<script>
  var dataTable;
   $("#js-msg-error").hide();
   $("#js-msg-success").hide();
   function approveCurrection(el){
      var id = $(el).children('i').attr('class');
      var confirm1 = confirm('Are You Sure, You Want to Approved ?');
      if(confirm1) {
      $.ajax({  
                url:"/admin/rto/rc/correction/approve/"+id,  
                method:"get",  
                success:function(data){ 
                 if(data == 'success') {
                      $('#table').dataTable().api().ajax.reload();
                      $("#js-msg-success").html('RC correction request approved !');
                      $("#js-msg-success").show();
                    }else{
                      $("#js-msg-error").html('Something went wronge !');
                      $("#js-msg-error").show();
                    }  
                  }
           });
        }
    }


  $(document).ready(function() {
      dataTable = $('#table').DataTable({
        "processing": true,
         "serverSide": true,
         "ajax": "/admin/rto/rc/correction/request/list/api",
         "aaSorting": [],
         "responsive": true,
         'createdRow':function(row,data,dataIndex)
          {
              $(row).addClass('rowClass');
              if(data.status == 'approved')
              {
                $(row).addClass('successClass');
              }
              else if(data.status == 'pending')
              {
                $(row).addClass('dangerClass');
              }
          },
         "columns": [
            { "data": "mistake_by" },
            { "data": "customer" },
            { "data": "address" },
            { "data": "relation_type" },
            { "data": "relation_name" },
            { "data": "frame_number" },
            { "data": "hypo" },
            { "data": "payment_amount" },
            { "data": "correction_reason" },
            { "data": "amount" },
            { "data": "status" },
             {
                 "targets": [ -1 ],
                 "data":"id", "render": function(data,type,full,meta)
                 {
                  if(full.status == 'pending'){
                    return '<a href="#"><button class="btn btn-info btn-xs"  onclick="approveCurrection(this)"><i class="'+data+'"></i> Approve</button></a>';
                  }else{
                   // return '<a href="/admin/rto/rc/correction/request/pay/'+data+'"><button class="btn btn-success btn-xs "> Pay</button></a> '
                     return '<a href="/admin/rto/rc/correction/request/pay/details/'+data+'"><button class="btn btn-primary btn-xs "> Payment Details</button></a>';
                  }
                   
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
            <div class="row">
            <div class="alert alert-danger" id="js-msg-error">
            </div>
            <div class="alert alert-success" id="js-msg-success">
            </div>
          </div>
            
        </div>
    <!-- Default box -->
        <div class="box">
                <!-- /.box-header -->
            <div class="box-body">
                    <table id="table" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                              <th>Mistake By</th>                   
                              <th>Customer</th>                   
                              <th>Address</th>                   
                              <th>Relation Type</th>                   
                              <th>Relation</th>                   
                              <th>Frame No.</th>                   
                              <th>Hypo</th>                   
                              <th>Payment Amount</th>                   
                              <th>Reason</th>
                              <th>Paid Amount</th>
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