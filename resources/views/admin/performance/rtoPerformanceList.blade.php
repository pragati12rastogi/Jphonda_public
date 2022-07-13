@extends($layout)

@section('title', __('RTO Performance Summary'))

@section('user', Auth::user()->name)

@section('breadcrumb')

    <li><a href="#"><i class=""></i>{{__('RTO Performance Summary')}}</a></li> 
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

  th{
    text-align: center;
  }
  .table-striped>tbody>tr.good{
    background-color: #3fb618;
  }
  .table-striped>tbody>tr.avg{
    background-color: #ffc107;
  }
  .table-striped>tbody>tr.poor{
    background-color: #ec1c2f;
  }

  </style>

@endsection
@section('js')
<script src="/js/dataTables.responsive.js"></script>
<script>
  var dataTable;

    $("#js-msg-error").hide();
    $("#js-msg-success").hide();

    submission();
    function submission()
    {
      $("#total-div").css('display','none');
      $("#submission-div").css('display','block');
      if(dataTable)
      {
        dataTable.destroy();
      }
      dataTable = $('#table-submission').DataTable({
        "processing": true,
         "serverSide": true,
         "ajax": "/admin/performance/rto/api/submission",
         "aaSorting": [],
         "responsive": true,
         "rowId":"id",
         "columns": [
            { "data": "agent_name" },
            { "data": "submission_date" },
            { "data": "receiving_date" },
            { "data": "performanceStatus",
              "render": function (data, type, row) {
                    return ((data == 'good')? 'Good' : ((data == 'avg')? 'Average' : 'Poor') );
                }
            }
           ],
           rowCallback:function(row,data)
           {
             console.log('data',data);
             console.log('row',row);
             if(data.performanceStatus == 'good')
             {
               $(row).addClass('good');
             }
             else if(data.performanceStatus == 'avg')
             {
               $(row).addClass('avg');
             }
             else if(data.performanceStatus == 'bad')
             {
               $(row).addClass('poor');
             }
           }
         
       });
    }
    function total()
    {
      $("#submission-div").css('display','none');
      $("#total-div").css('display','block');
      if(dataTable)
      {
        dataTable.destroy();
      }
      dataTable = $('#table-total').DataTable({
        "processing": true,
         "serverSide": true,
         "ajax": "/admin/performance/rto/api/total",
         "aaSorting": [],
         "responsive": true,
         "rowId":"id",
         "columns": [
            { "data": "agent_name" },
            { "data": "performanceStatus",
              "render": function (data, type, row) {
                    return ((data == 'good')? 'Good' : ((data == 'avg')? 'Average' : 'Poor') );
                }
            }
           ],
           rowCallback:function(row,data)
           {
             console.log('data',data);
             console.log('row',row);
             if(data.performanceStatus == 'good')
             {
               $(row).addClass('good');
             }
             else if(data.performanceStatus == 'avg')
             {
               $(row).addClass('avg');
             }
             else if(data.performanceStatus == 'bad')
             {
               $(row).addClass('poor');
             }
           }
         
       });
    }
      
    $('#submissionWise').click(function(){
      $('#submissionWise').addClass('btn-color-active');
      $('#totalSubmission').removeClass('btn-color-active');
      submission();
    });
    $('#totalSubmission').click(function(){
      $('#totalSubmission').addClass('btn-color-active');
      $('#submissionWise').removeClass('btn-color-active');
      total();
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
          <div class="box-header with-border">
            {{-- 
            <h3 class="box-title">{{__('customer.mytitle')}} </h3>
            --}}
            <ul class="nav nav1 nav-pills">
              <li class="nav-item">
                <button class="nav-link1 btn-color-active" id="submissionWise" >Submission Wise</button>
              </li>
              <li class="nav-item">
                <button class="nav-link1 " id="totalSubmission" >Total Submission</button>
              </li> 
            </ul>
          </div>
                <!-- /.box-header -->
            <div class="box-body" id="submission-div">
                    <table id="table-submission" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                              <th> {{__('Agent Name')}}</th>
                              <th> {{__('Submission Date')}}</th>
                              <th> {{__('Receiving Date')}}</th>
                              <th> {{__('Performance')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>               
                    </table>
                    <!-- /.box-body -->
            </div>
            <div class="box-body" id="total-div">
              <table id="table-total" class="table table-bordered table-striped">
                <thead>
                  <tr>
                    <th> {{__('Agent Name')}}</th>
                    <th> {{__('Performance')}}</th>
                  </tr>
                </thead>
                  <tbody>
                  </tbody>               
              </table>
              <!-- /.box-body -->
      </div>
        </div>
         
    </section>
@endsection