@if ($message = Session::get('success'))
<div class="alert alert-success alert-block">
	<button type="button" class="close" data-dismiss="alert">×</button>	
        <strong> {{$message}}</strong>
</div>
@endif
{{-- @if ($message = Session::get('io_id'))
<div class="alert alert-info alert-block">
	<button type="button" class="close" data-dismiss="alert">×</button>	
        <strong>Internal Order Id:- {{ 'PPML/IO/'.$message }}</strong>
</div>
@endif --}}


@if ($message = Session::get('error'))
<div class="alert alert-danger alert-block">
	<button type="button" class="close" data-dismiss="alert">×</button>	
        <strong>{{ $message }}</strong>
</div>
@endif


@if ($message = Session::get('warning'))
<div class="alert alert-warning alert-block">
	<button type="button" class="close" data-dismiss="alert">×</button>	
	<strong>{{ $message }}</strong>
</div>
@endif


@if ($message = Session::get('info'))
<div class="alert alert-info alert-block">
	<button type="button" class="close" data-dismiss="alert">×</button>	
	<strong>{{ $message }}</strong>
</div>
@endif


@if ($errors->any())
<div class="alert alert-danger">
	<button type="button" class="close" data-dismiss="alert">×</button>	
	Please check the form below for errors
</div>
@endif

@if($message = Session::get('message'))
<a class="popup_message" id="popup_message" data-toggle="modal" data-target="#message" href="#"></a>
<div class="modal fade" id="message" role="dialog">
	<div class="modal-dialog modal-md">
		<div class="modal-content">
		   <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title">Successfully created</h4>
			</div>
			<div class="modal-body">
			<div class="row">
				<div class="col-md-12">
						<div class="col-md-8">
								<p style="float:left;"><span style="font-style: bold; font-size:20px">Job Card Id:</span> &nbsp;&nbsp;&nbsp; <span style="font-style: normal">{{Session::get('prefix')}}</span></p>
						</div>
						<div class="col-md-4">
								<a href="{{url('/templateJC'.'/'.Session::get('jc_id')) }}" target="_blank"><button style="float:right" type="button" class="btn btn">Print</button></a>
						</div>
				</div>

			</div>
			</div>    
		</div>
	</div>
</div>
@endif

@if($message = Session::get('internal'))
<a class="popup_message1" id="popup_message1" data-toggle="modal" data-target="#message1" href="#"></a>
<div class="modal fade" id="message1" role="dialog">
	<div class="modal-dialog modal-md">
		<div class="modal-content">
		   <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title">Successfully created Internal Order</h4>
			</div>
			<div class="modal-body">
			<div class="row">
				<div class="col-md-12">
						<div class="col-md-8">
								<p style="float:left;"><span style="font-style: bold; font-size:20px">Internal Order:</span> &nbsp;&nbsp;&nbsp; <span style="font-style: normal">{{Session::get('prefix')}}</span></p>
						</div>
						<div class="col-md-4">
						<a href="{{url('/template'.'/'.Session::get('io_id')) }}" target="_blank"><button style="float:right" type="button" class="btn btn">Print</button></a>
						</div>
				</div>

			</div>
			</div>    
		</div>
	</div>
</div>
@endif

@if($message = Session::get('tax'))
<a class="popup_message2" id="popup_message2" data-toggle="modal" data-target="#message2" href="#"></a>
<div class="modal fade" id="message2" role="dialog">
	<div class="modal-dialog modal-md">
		<div class="modal-content">
		   <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title">Successfully created Tax Invoice</h4>
			</div>
			<div class="modal-body">
			<div class="row">
				<div class="col-md-12">
						<div class="col-md-8">
								<p style="float:left;"><span style="font-style: bold; font-size:20px">Tax Invoice:</span> &nbsp;&nbsp;&nbsp; <span style="font-style: normal">{{Session::get('prefix')}}</span></p>
						</div>
						<div class="col-md-4">
								<a href="{{url('/templateTax'.'/'.Session::get('tax')) }}" target="_blank"><button style="float:right" type="button" class="btn btn">Print</button></a>
						</div>
				</div>

			</div>
			<div class="row">
				<div class="col-md-12">
						
					<p style="float:left;"><span style="font-style:italic; font-size:14px">Total Amount : {{Session::get('amntDate')}}</p>
					
				</div>
			</div>
			<div class="row">
				<div class="col-md-12">
						
					<p style="float:left;"><span style="font-style:italic;color:red; font-size:14px">{{Session::get('mesg')}}</p>
					
				</div>
			</div>
			@php
				$amntDate=Session::get('amntDate');
				$amntDate=str_replace(".","+",$amntDate);
			@endphp
				<div class="row">
					@if (Session::get('mesg'))
					<div class="col-md-2">
							<a href="{{url('/waybill/create'.'/'.Session::get('delivery_id').'/'.'Sale'.'/'.Session::get('party').'/'.Session::get('date').'/'.$amntDate) }}" target="_blank"><button style="float:right" type="button" class="btn btn-success">WayBill</button></a>
					</div>
					@endif
				</div>
			
			</div> 
			</div>    
		</div>
	</div>
</div>
@endif


@if($message = Session::get('delivery'))

<a class="popup_message3" id="popup_message3" data-toggle="modal" data-target="#message3" href="#"></a>
<div class="modal fade" id="message3" role="dialog">
	<div class="modal-dialog modal-md">
		<div class="modal-content">
		   <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title">Successfully Done</h4>
			</div>
			<div class="modal-body">
			<div class="row">
				<div class="col-md-12">
						<div class="col-md-8">
								<p style="float:left;"><span style="font-style: bold; font-size:20px">Delivery Challan:</span> &nbsp;&nbsp;&nbsp; <span style="font-style: normal">{{Session::get('delivery_prefix')}}</span></p>
						</div>
						<div class="col-md-4">
								<a href="{{url('/templateDelivery'.'/'.Session::get('delivery')) }}" target="_blank"><button style="float:right" type="button" class="btn btn">Print</button></a>
						</div>
				</div>

			</div>
			<div class="row">
				<div class="col-md-12">
						
					<p style="float:left;"><span style="font-style:italic; font-size:14px">Total Amount : {{Session::get('amntDate')}}</p>
					
				</div>
			</div>
			<div class="row">
				<div class="col-md-12">
						
					<p style="float:left;"><span style="font-style:italic;color:red; font-size:14px">{{Session::get('mesg')}}</p>
					
				</div>
			</div>
				<div class="row">
					@if (Session::get('mesg'))
					@php
				$amntDate=Session::get('amntDate');
				$amntDate=str_replace(".","+",$amntDate);
			@endphp
					<div class="col-md-2">
						<a href="{{url('/waybill/create'.'/'.Session::get('delivery_id').'/'.'Challan'.'/'.Session::get('party').'/'.Session::get('date').'/'.$amntDate) }}" target="_blank"><button style="float:right" type="button" class="btn btn-success">WayBill</button></a>
					</div>
					@endif
				</div>
			
			</div>    
		</div>
	</div>
</div>
@endif



@if($message = Session::get('pass'))
<a class="popup_message" id="message1" data-toggle="modal" data-target="#message" href="#"></a>
<div class="modal fade" id="message" role="dialog">
	<div class="modal-dialog modal-md">
		<div class="modal-content">
		   <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title">Successfully created</h4>
			</div>
			<div class="modal-body">
			<div class="row">
				<div class="col-md-12">
						<div class="col-md-8">
								<p style="float:left;"><span style="font-style: bold; font-size:20px">Gate Pass For:</span> &nbsp;&nbsp;&nbsp; <span style="font-style: normal">{{Session::get('pass')}}</span></p>
						</div>
						@if ($message=="Material")
						<div class="col-md-4">
							<a href="{{url('/mgatepass/template'.'/'.Session::get('pass_id')) }}" target="_blank"><button style="float:right" type="button" class="btn btn">Print</button></a>
					</div>
						@endif
						@if ($message=="Employee")
						<div class="col-md-4">
							<a href="{{url('/egatepass/template'.'/'.Session::get('pass_id')) }}" target="_blank"><button style="float:right" type="button" class="btn btn">Print</button></a>
					</div>
						@endif
						@if ($message=="Returnable")
						<div class="col-md-4">
							<a href="{{url('/rgatepass/template'.'/'.Session::get('pass_id')) }}" target="_blank"><button style="float:right" type="button" class="btn btn">Print</button></a>
					</div>
						@endif
						
				</div>

			</div>
			</div>    
		</div>
	</div>
</div>
@endif
