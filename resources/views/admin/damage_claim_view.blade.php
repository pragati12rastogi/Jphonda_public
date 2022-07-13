@extends($layout)

@section('title', __('damage_claim.title'))

@section('breadcrumb')
    <li><a href="#"><i class=""></i> {{__('damage_claim.title')}} View</a></li>
    
@endsection
@section('js')
@endsection

@section('main_section')
    <section class="content">
            <!-- general form elements -->
            <div class="box box-primary">
                <div class="box-header">
                    <div class="row">
                        <div class="col-md-4">
                            <p> <strong>Load Reference Number:</strong> {{$api_data['load_referenace_number']}}</p>
                        </div>
                        @if($api_data['status'] == 'pending')
                            <div class="col-md-4">
                        @else
                            <div class="col-md-4" style="background-color:">
                        @endif
                                <p><span style="background-color:#808080; color: #ffffff; padding: 5px;"> <strong>Status:</strong> {{$api_data['status']}}</span></p>
                        </div>
                        <div class="col-md-2">
                            <p> <strong>Claim Amount:</strong> {{$api_data['claim_amount']}}</p>
                        </div>
                        <div class="col-md-2">
                                <p> <strong>Repair Amount:</strong> {{$api_data['repair_amount']}}</p>
                            </div>
                        
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                                <p> <strong>Received Amount:</strong> {{$api_data['received_amount']}}</p>

                        </div>
                        <div class="col-md-4">
                                <p> <strong>Received Date:</strong> {{explode(" ",$api_data['received_date'])[0]}}</p>

                        </div>
                        <div class="col-md-4">
                                <p> <strong>Settlement:</strong> {{$api_data['settlement']}}</p>
                        </div> 
                        
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                                    <p> <strong>Mail Sent:</strong> {{$api_data['mail_sent']}}</p>
    
                        </div>
                        <div class="col-md-4">
                                <p> <strong>Mail Sent Date:</strong> {{explode(" ",$api_data['mail_date'])[0]}}</p>

                        </div>
                        <div class="col-md-4">
                                <p> <strong>Estimate Sent:</strong> {{$api_data['est_sent']}}</p>

                        </div>
                        

                    </div>
                    <div class="row">
                            <div class="col-md-4">
                                    <p> <strong>Estimate Date:</strong> {{explode(" ",$api_data['est_date'])[0]}}</p>
    
                            </div>
                        <div class="col-md-4">
                                <p> <strong>Claim Form Uploaded:</strong> {{$api_data['claim_form_uploaded']}}</p>
                        </div>
                        <div class="col-md-4">
                                <p> <strong>Uploaded Date:</strong> {{explode(" ",$api_data['uploaded_date'])[0]}}</p>
                        </div>
                        
                    </div>
                    <div class="row">
                            <div class="col-md-4">
                                    <p> <strong>High Rise Bill:</strong> {{$api_data['hirise_bill']}}</p>
                            </div>
                        <div class="col-md-4">
                                <p> <strong>High Rise Bill Date:</strong> {{explode(" ",$api_data['bill_date'])[0]}}</p>
    
                        </div>
                        
                        <div class="col-md-4">
                                <p> <strong>Qty Key:</strong> {{$api_data['qty_key']}}</p>
    
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <p> <strong>Battery:</strong> {{$api_data['battery']}}</p>
                            
                        </div>
                        <div class="col-md-4">
                                <p> <strong>Saree Guard:</strong> {{$api_data['saree_guard']}}</p>
                        </div>
                        <div class="col-md-4">      
                            <p> <strong>Owner Manual:</strong> {{$api_data['owner_manual']}}</p>

                        </div>
                    </div>
                </div>  
            </div>
            
    <div class="box box-primary">
        <div class="box-header">
            <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Frame Number</th>
                            <th>Estimate Amount</th>
                            <th>Damage Description</th>
                            <th>Status</th>
                            <th>Reapir Amount</th>
                            <th>Repair Completion Date</th>
                        </tr>
                    </thead>
                    <tbody>
                            @foreach ($claim_details as $val)
                                <tr>
                                    <td>{{$val['product_name']}}</td>
                                    <td>{{$val['frame']}}</td>
                                    <td>{{$val['est_amount']}}</td>
                                    <td>{{$val['damage_desc']}}</td>
                                    <td>{{$val['status']}}</td>
                                    <td>{{$val['repair_amount']}}</td>
                                    <td>{{$val['repair_completion_date']}}</td>
                                </tr>
                            @endforeach 
                    </tbody>
            </table> 
        </div>
    </div>
            {{-- @php
                $i=0;
            @endphp
           @if ($claim_details)
           <h3>Damage Claim Details</h3>
           @foreach ($claim_details as $val)
           @php
               $i++;
           @endphp
           <h3>Details:{{$i}}</h3>
           <div class="box box-primary">
                   <div class="box-header">
                      <p> <strong>Frame Number:</strong> {{$val['frame']}}</p>
                      <p> <strong>Estimate Amount:</strong> {{$val['est_amount']}}</p>
                      <p> <strong>Damage Description:</strong> {{$val['damage_desc']}}</p>
                      <p> <strong>Status:</strong> {{$val['status']}}</p>
                      <p> <strong>JC:</strong> {{$val['jc']}}</p>
                      <p> <strong>Repair Amount:</strong> {{$val['repair_amount']}}</p>
                      <p> <strong>Repair Completion Date:</strong> {{$val['repair_completion_date']}}</p>
                      
                   </div>  
               </div>
   
           @endforeach 
           @endif --}}
        
      </section>
@endsection
{{-- {{CustomHelpers::coolText('hcjsd')}} --}}