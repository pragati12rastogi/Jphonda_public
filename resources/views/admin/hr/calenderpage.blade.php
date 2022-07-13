
@extends($layout)

@section('title', 'Calendar')

@section('user', Auth::user()->name)

@section('breadcrumb')

    <li><a href="#"><i class=""></i>Calendar</a></li> 
@endsection

@section('css')
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/fullcalendar/2.2.7/fullcalendar.min.css"/>
@endsection

@section('js')
    <script src="//cdnjs.cloudflare.com/ajax/libs/moment.js/2.9.0/moment.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/fullcalendar/2.2.7/fullcalendar.min.js"></script> -->
    {!! $calendar->script() !!}
    <script>
 
 function showModal(title,url,reason,start_date,end_date,leave_status,check){

    $('#status').show();
    $('#reason').show();

    if(check=='holiday'){

    $('#status').hide();
    $('#reason').hide();
    }else if(check=='leave'){

    $('#leave_status').html(leave_status);
    $('#event').html(reason);
    }

    if(start_date==end_date){
        $('#date').html('Date:');
        $('#fromdate').html(start_date);
        $('#end_date').hide();   
    }else{
        $('#date').html('From Date:');
        $('#end_date').show();
        $('#fromdate').html(start_date);       
        $('#todate').html(end_date);
    }
    $('#modalTitle').html(title);
    $('#calendarModal').modal();

     
 }
    </script>
 @endsection


@section('main_section')
<div class="container">
    <div class="row">

        <div class="col-md-11">

            <div class="panel panel-default">
                <br><div class="col-md-12">
                    <div class="col-md-8"></div>
                    <div class="col-md-4 color-tab">
                      <ul>
                        <li> <button class="festival-btn"></button>&nbsp; Festival Holiday &nbsp;&nbsp;</li>
                        <li> <button class="leave-btn"></button>&nbsp; Leave &nbsp;&nbsp;</li>
                      </ul>
                    </div>
                  </div><br>

               <center> <div class="panel-body" style="width:100%">
                {!! $calendar->calendar() !!}

   
                </div></center>
            </div>
        </div>
    </div>
</div>
<div id="calendarModal" class="modal fade">
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span> <span class="sr-only">close</span></button>
            <h4 id="modalTitle" class="modal-title"></h4>
        </div>
        <div id="modalBody" class="modal-body"> 
            <p id="status"><b>Leave Status:</b>&nbsp;&nbsp;&nbsp;<span id="leave_status"></span></p>
            <p id="reason"><b>Reason:</b>&nbsp;&nbsp;&nbsp;<span id="event"></span></p>
            <p id="start_date"><b id="date">From Date:</b>&nbsp;&nbsp;&nbsp;<span id="fromdate"></span></p>
            <p id="end_date"><b>To Date:</b>&nbsp;&nbsp;&nbsp;<span id="todate"></span></p>
        </div>
        
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
    </div>
</div>
</div> 
@endsection
