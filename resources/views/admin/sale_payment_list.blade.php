@extends($layout)

@section('title', __('hirise.sale_peyment'))

@section('user', Auth::user()->name)

@section('breadcrumb')

    <li><a href="#"><i class=""></i>{{__('hirise.sale_peyment')}}</a></li> 
@endsection
@section('css')

<link rel="stylesheet" href="/css/responsive.bootstrap.css">    
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
 .nav1>li>button.btn-color-active{
  background-color: rgb(135, 206, 250);
 }
 .nav1>li>button.btn-color-unactive{
  background-color: white;
 }
 .spanred{
   color: red;
 }
</style>
@endsection
@section('js')
<script src="/js/dataTables.responsive.js"></script>
<script>
  var dataTable;

     function completeSalePayment()
    {
      $("#complete-div").css('display','block');
      $("#pending-div").css('display','none');
      if(dataTable)
      {
        dataTable.destroy();
      }
      dataTable = $('#complete_table').DataTable({
         "processing": true,
         "serverSide": true,
         "ajax": "/admin/sale/payment/list/api/complete",
         "aaSorting": [],
         "responsive": true,
         "columns": [
             { "data": "sale_no" },
             { "data": "name" },
             { "data": "sale_date" },
             { "data": "total_amount" },
             { "data": "payment_amount" }
            //  { "data": "status" },
             // {
             //     "targets": [ -1 ],
             //     "data":"id", "render": function(data,type,full,meta)
             //     {
             //       return '<a href="/admin/sale/pay/'+data+'"><button class="btn btn-info btn-xs"> Pay</button></a>' ;
             //     },
             //     "orderable": false
             // }
           ]
         
       });
    }

     function pendingSalePayment()
    {
      $("#complete-div").css('display','none');
      $("#pending-div").css('display','block');
      if(dataTable)
      {
        dataTable.destroy();
      }
      dataTable = $('#pending_table').DataTable({
         "processing": true,
         "serverSide": true,
         "ajax": "/admin/sale/payment/list/api/pending",
         "aaSorting": [],
         "responsive": true,
         "columns": [
             { "data": "sale_no" },
             { "data": "name" },
             { "data": "sale_date" },
             { "data": "total_amount" },
             { "data": "payment_amount" },
            //  { "data": "status" },
             {
                 "targets": [ -1 ],
                 "data":"id", "render": function(data,type,full,meta)
                 {
                   return '<a href="/admin/sale/pay/'+data+'"><button class="btn btn-info btn-xs"> Pay</button></a>' ;
                 },
                 "orderable": false
             }
           ]
         
       });
    }
 
  pendingSalePayment();

      $("#complete").on('click',function(){
        $(this).css('background', 'rgb(135, 206, 250)');
        $("#pending").css('background', '#fff');
          $(this).addClass('btn-color-active');
       
        completeSalePayment();
      });

      $("#pending").on('click',function(){
        $(this).css('background', 'rgb(135, 206, 250)');
        $("#complete").css('background', '#fff');
          $(this).addClass('btn-color-active');
       
        pendingSalePayment();
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
        <div class="box-header with-border">
          <ul class="nav nav1 nav-pills">
            <li class="nav-item">
              <button class="nav-link1 btn-color-active" id="pending" >Pending Sale Payment</button>
            </li> 
            <li class="nav-item">
              <button class="nav-link1 btn-color-anactive" id="complete" >Complete Sale Payment</button>
            </li> 
            
          </ul>
        </div>
            <div class="box-body">
              <div id="complete-div" class="box-body">
                    <table id="complete_table" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                              <th>{{__('Sale Number')}}</th>
                              <th>Customer Name</th>
                              <th>{{__('Sale Date')}}</th>
                              <th>{{__('hirise.totalamt')}}</th>
                              <th>{{__('hirise.paidamt')}}</th>
                              {{-- <th>{{__('Status')}}</th> --}}
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>               
                    </table>
                    <!-- /.box-body -->
            </div>
              <div id="pending-div" class="box-body" style="display:none;">
                    <table id="pending_table" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                              <th>{{__('Sale Number')}}</th>
                              <th>Customer Name</th>
                              <th>{{__('Sale Date')}}</th>
                              <th>{{__('hirise.totalamt')}}</th>
                              <th>{{__('hirise.paidamt')}}</th>
                              {{-- <th>{{__('Status')}}</th> --}}
                              <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>               
                    </table>
                    <!-- /.box-body -->
            </div>
          </div>
        </div>
        <!-- /.box -->
    </section>
@endsection