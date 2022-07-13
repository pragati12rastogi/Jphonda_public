@extends($layout)

@section('title', __('Salary View Page'))

@section('js')
<script>
  $(document).ready(function() {

$(".show_details").on('click',function(){
  $('#salaryModal').modal();

});

  });
</script>
@endsection

@section('breadcrumb')
    <li><a href="#"><i class=""></i> {{__('Salary View Page')}} </a></li>
    <style>
 .show_details {
    cursor: pointer;
  }
</style>
@endsection
@section('main_section')
    <section class="content"> 
         <div id="app">
                    @include('admin.flash-message')
                    @yield('content')
            </div>
            <!-- general form elements -->
            <div class="box box-primary" style="padding-bottom: 25px;">
                <div class="box-header with-border">
<!--         <h3 class="box-title">{{__('customer.mytitle')}} </h3>-->
                </div>  

                        <div class="row">
                            <div class="col-md-6">
                                <label>Emp Id </label>
                                {{$payroll_salary->emp_id}}
                            </div>
                            <div class="col-md-6">
                                <label>Store Name</label>
                                {{$payroll_salary->store_name}}
                            </div>
                        </div> 
                        <div class="row">
                            <div class="col-md-6">
                                <label>Name</label>
                                {{$payroll_salary->name}}
                            </div>
                            <div class="col-md-6">
                                <label>Type</label>
                                {{$payroll_salary->type}}
                            </div>
                        </div> 
                        <div class="row">
                             <div class="col-md-6">
                                <label>Payment Date</label>
                                {{$payroll_salary->payment_date}}
                            </div>
                            <div class="col-md-6">
                                <label>Total Deduction</label>
                                {{$payroll_salary->total_deduction}}
                            </div>
                        </div>
                        <div class="row">
                           @foreach($payroll_salary_details as $details1)
                            @if($details1->name=='Month Basic Salary')
                              <div class="col-sm-6">
                                <label>Month Basic Salary </label>
                                {{$details1->amount}}
                                </div>
                             @endif
                            @if($details1->name=='Month Net Salary')
                              <div class="col-sm-6">
                                <label>Month Net Salary </label>
                                <span class="show_details">{{$details1->amount}}</span>
                                </div>
                             @endif
                             @endforeach
                            
                        </div>  
                        <div class="row">
                            <div class="col-md-6">
                                <label>Net Salary</label>
                                <span class="show_details">{{$payroll_salary->net_salary}}</span>
                            </div>
                            <div class="col-md-6">
                                <label>Total PF</label>
                                {{$payroll_salary->total_pf}}
                            </div>
                        </div> 
                        <div class="row">
                            <div class="col-md-6">
                                <label>Actual Salary</label>
                                {{$payroll_salary->actual_salary}}
                            </div>
                             <div class="col-md-6">
                                <label>Salary Month</label>
                              <?php 
                                  $date = new DateTime($payroll_salary->salary_month);
                                  echo $monthName = $date->format('F'); 
                                  
                              ?>
                            </div>
                        </div> 
                        <hr>
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-6 text-center"><label>Salary/Bonas <span style="font-size:12px;"> (Credit)</span></label>
                            <table id="table" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Sr.No. </th>
                                            <th>Name</th>
                                            <th>Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                       <?php $i=0;
                                        $cr_sum = 0;
                                        ?>

                                       @foreach($payroll_salary_details as $details)
                                      
                                       @php $name= $details->name;
                                        
                                        $value= ucwords(str_replace("_"," ",$name));
                                        if($details->name=='Month Basic Salary'){
                                            continue;
                                        }if($details->name=='Month Ta'){
                                            continue;
                                        }if($details->name=='Month Hra'){
                                            continue;
                                        }if($details->name=='Month Perf Allowance'){
                                            continue;
                                        }if($details->name=='Month Others'){
                                            continue;
                                        }if($details->name=='Month Net Salary'){
                                            continue;
                                        }

                                       
                                           @endphp
                                           @if($details->type=='CR')
                                           <?php $i++;
                                            $cr_sum+=$details->amount;
                                            ?>
                                           <tr>
                                               <td>{{$i}}</td>
                                               <td>{{$value}}</td>
                                               <td>{{$details->amount}}</td>
                                           </tr>
                                           @endif
                                           @endforeach
                                           <tr>
                                             <td colspan="2" class="text-center"><b> Total (Credit) </b></td>
                                             <td>{{$cr_sum}}</td>
                                            </tr>
                                    </tbody>
                          </table>
                            </div>
                        <div class="col-md-6 text-center"><label>Deduction <span style="font-size:12px;"> (Debit)</span></label>
                            <table id="table" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Sr.No. </th>
                                            <th>Name</th>
                                            <th>Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                       <?php $j=0;
                                       $dr_sum = 0; ?>

                                       @foreach($payroll_salary_details as $details)
                                      
                                       @php $name= $details->name;
                                    
                                        $value= ucwords(str_replace("_"," ",$name));
                                          if($details->name=='Month Other Deduction'){
                                                continue;
                                          }if($details->name=='Month Pf Deduction'){
                                                continue;
                                          }if($details->name=='Month Tax Deduction'){
                                            continue;
                                           }
                                           @endphp
                                           @if($details->type=='DR')
                                           <?php $j++;
                                           $dr_sum+=$details->amount;
                                           ?>
                                           <tr>
                                               <td>{{$j}}</td>
                                               <td>{{$value}}</td>
                                               <td>{{$details->amount}}</td>
                                           </tr>
                                           @endif
                                           @endforeach
                                           <tr>
                                             <td colspan="2" class="text-center"><b>Total (Debit) </b></td>
                                             <td>{{$dr_sum}}</td>
                                            </tr>
                                  </tbody>
                          </table>
                        </div>
                        </div>
                        <div class="row justify-content-between">
                             <div class="col-sm-10 text-right"><label>Net Amount :</label></div>
                             <div class="col-sm-2 "><label>{{$payroll_salary->net_salary}}</label></div>
                        </div>
                    </div>
            </div>
        
      </section>

      <div id="salaryModal" class="modal fade">
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span> <span class="sr-only">close</span></button>
            <h4 id="modalTitle" class="modal-title">Salary Details</h4>
        </div>
        <div id="modalBody" class="modal-body"> 

           @if(isset($payroll_salary_details))
           <div class="row cu_month">        
             @foreach($payroll_salary_details as $details1)
               @if($details1->name=='Month Basic Salary')
                <div class="col-sm-6"><div class="col-sm-6"><b>{{$details1->name}}</b></div><div class="col-sm-6">{{$details1->amount}}</div></div>
               @endif
               @if($details1->name=='Month Hra')
               <div class="col-sm-6"><div class="col-sm-6"><b>{{$details1->name}}</b></div><div class="col-sm-6">{{$details1->amount}}</div></div>
               @endif
               @if($details1->name=='Month Ta')
               <div class="col-sm-6"><div class="col-sm-6"><b>{{$details1->name}}</b></div><div class="col-sm-6">{{$details1->amount}}</div></div>
               @endif
               @if($details1->name=='Month Perf Allowance')
               <div class="col-sm-6"><div class="col-sm-6"><b>{{$details1->name}}</b></div><div class="col-sm-6">{{$details1->amount}}</div></div>
               @endif
               @if($details1->name=='Month Others')
               <div class="col-sm-6"><div class="col-sm-6"><b>{{$details1->name}}</b></div><div class="col-sm-6">{{$details1->amount}}</div></div>
               @endif
               @if($details1->name=='Month Tax Deduction')
               <div class="col-sm-6"><div class="col-sm-6"><b>{{$details1->name}}</b></div><div class="col-sm-6">{{$details1->amount}}</div></div>
               @endif
               @if($details1->name=='Month Pf Deduction')
               <div class="col-sm-6"><div class="col-sm-6"><b>{{$details1->name}}</b></div><div class="col-sm-6">{{$details1->amount}}</div></div>
               @endif
               @if($details1->name=='Month Other Deduction')
               <div class="col-sm-6"><div class="col-sm-6"><b>{{$details1->name}}</b></div><div class="col-sm-6">{{$details1->amount}}</div></div>
               @endif
               @if($details1->name=='Month Net Salary')
               <div class="col-sm-6"><div class="col-sm-6"><b>{{$details1->name}}</b></div><div class="col-sm-6">{{$details1->amount}}</div></div>
               @endif             
              @endforeach
            </div>
             @else
              <div class="row pre_month">
                  <div class="col-sm-6"><div class="col-sm-6"><b>Month Basic Salary</b></div><div class="col-sm-6">{{$basic_salary}}</div></div>
                  <div class="col-sm-6"><div class="col-sm-6"><b>Month Hra</b></div><div class="col-sm-6">{{$hra}}</div></div>
                  <div class="col-sm-6"><div class="col-sm-6"><b>Month Ta</b></div><div class="col-sm-6">{{$ta}}</div></div>
                  <div class="col-sm-6"><div class="col-sm-6"><b>Month Perf Allowance</b></div><div class="col-sm-6">{{$perf_allowance}}</div></div>
                  <div class="col-sm-6"><div class="col-sm-6"><b>Month Other</b></div><div class="col-sm-6">{{$others}}</div></div>
                  <div class="col-sm-6"><div class="col-sm-6"><b>Month Tax Deduction</b></div><div class="col-sm-6">{{$tax_deduction}}</div></div>
                  <div class="col-sm-6"><div class="col-sm-6"><b>Month Pf Deduction</b></div><div class="col-sm-6">{{$pf_deduction}}</div></div>
                  <div class="col-sm-6"><div class="col-sm-6"><b>Month Other Deduction</b></div><div class="col-sm-6">{{$other_deduction}}</div></div>
                  <div class="col-sm-6"><div class="col-sm-6"><b>Month Net Salary</b></div><div class="col-sm-6">{{$month_net_salary}}</div></div>
            </div>
          @endif
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
    </div>
</div>
</div> 
@endsection