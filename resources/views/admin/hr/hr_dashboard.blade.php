@extends($layout)

@section('title', __('HRD DashBoard'))


@section('breadcrumb')
    <li><a href="#"><i class=""></i>HRD DashBoard</a></li>
    
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
    <section class="content">
        <!-- Default box -->
        <div class="row">
             <div class="col-sm-6">
                <div class="box">
                  <div class="box-header with-border">
                    <h3> NOP Details </h3>
                  </div>
                  <div class="box-body">
                    <table id="example3" class="table table-bordered table-hover">
                        <thead>
                        <tr>
                          <th>Name</th>
                          <th>Date</th>
                          <th>Time</th>
                          <th>Type</th>
                          <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        	@foreach ($nop as $nop_data)
                        <tr>
                          <td>{{$nop_data->name}}</td>
                          <td>{{$nop_data->date}} </td>
                          <td>{{$nop_data->time}}</td>
                          <td>{{$nop_data->type}}</td>
                          <td>{{$nop_data->status}}</td>
                        </tr>
                         @endforeach
                                       
                    </table>  
                  </div>
                </div>
        <!-- /.box -->
            </div>
            
            <div class="col-sm-6">
                <div class="box">
                  <div class="box-header with-border">
                    <h3>Leave Details</h3>
                  </div>
                  <div class="box-body">
                    <table id="example3" class="table table-bordered table-hover">
                        <thead>
                         <thead>
                        <tr>
                          <th>Employee Id</th>
                          <th>Name</th>
                          <th>Date</th>
                          <th>Status</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($leave as $leave_data)
                        <tr>
                          <td>{{$leave_data->emp_id}}</td>
                          <td>{{$leave_data->name}} </td>
                          <td>{{$leave_data->date}}</td>
                          <td>{{$leave_data->leave_status}}</td>
                        </tr>
                         @endforeach
                     </tbody>
                        
                    </table>  
                  </div>
                </div>
        <!-- /.box -->
            </div>
          </div>
        <div class="row">
        	<div class="col-sm-5">
          		<div class="row">
		          <div class="box">
                  <div class="box-header with-border">
                    <h3>Fuel Stock Report</h3>
                  </div>
                  <div class="box-body">
                    <table id="example2" class="table table-bordered table-hover">
                        <thead>
                        <tr>
                          <th>Store</th>
                          <th>Fuel Type</th>
                          <th>Quantity</th>
                        </tr>
                        </thead>
                        <tbody>          
                       @foreach ($fuel as $fuel_data)
                        <tr>
                          <td>{{$fuel_data->name}}</td>
                          <td>{{$fuel_data->fuel_type}} </td>
                          <td>{{$fuel_data->quantity}}</td>

                        </tr>
                         @endforeach
                        </tbody>
                        
                    </table>  
                  </div>
              </div>
	        	</div>
	        	<div class="row">
                   <div class="box">
	                  <div class="box-header with-border">
	                    <h3>Today Absent</h3>
	                  </div>
	                  <div class="box-body">
	                    <table id="example4" class="table table-bordered table-hover">
	                        <thead>
	                        <tr>
	                          <th>Employee Id</th>
	                          <th>Name</th>
	                          <th>Status</th>
	                          <th>Store Name</th>
	                        </tr>
	                        </thead>
	                        <tbody>
	                        @foreach ($attendance as $attendance_data)
	                        <tr>
	                          <td>{{$attendance_data->emp_id}}</td>
	                          <td>{{$nop_data->name}} </td>
	                          <td>{{$attendance_data->status}}</td>
	                          <td>{{$attendance_data->store_name}}</td>
	                        </tr>
	                         @endforeach
	                     </tbody>
	                    </table>  
	                  </div>
              </div>
	        	</div>

        <!-- /.box -->
            </div>
        	<div class="col-sm-7">
                <div class="box">
                  <div class="box-header with-border">
                    <h3>Calendar</h3>
                  </div>
                  <div class="box-body" style="width:100%">
						 {!! $calendar->calendar() !!} 
                  </div>
                </div>
        <!-- /.box -->
            </div>

        </div>
        
      </section>
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
