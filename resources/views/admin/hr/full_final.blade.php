@extends($layout)

@section('title', __('F&F'))

@section('js')
<script>
//For Gross Salary 
   $(document).on("keyup", ".amt", function() {
        var sum = 0;
        $(".amt").each(function(){
            sum += +$(this).val();
        });
        $("#total_amount").val(sum);
         var deduction =  $("#total_deduction").val();
        $("#net_amount").val(sum-deduction);
        $("#total_cr").text(sum);
    });
   $(document).ready(function() {
        var sum = 0;
        $(".amt").each(function(){
            sum += +$(this).val();
        });
        $("#total_amount").val(sum);
        var deduction =  $("#total_deduction").val();
        $("#net_amount").val(sum-deduction);
        $("#total_cr").text(sum);

    });

    $(document).on("keyup", ".decu", function() {
        var sum = 0;
        $(".decu").each(function(){
            sum += +$(this).val();
        });
        $("#total_deduction").val(sum);
         var total_amount =  $("#total_amount").val();
        $("#net_amount").val(total_amount-sum);
    });
     $(document).ready(function() {
        var sum = 0;
        $(".decu").each(function(){
            sum += +$(this).val();
        });
        $("#total_deduction").val(sum);
        var total_amount =  $("#total_amount").val();
        $("#net_amount").val(total_amount-sum);
    });

    $(document).on("keyup", ".net", function() {
        var sum = 0;
        sum += -$("#total_deduction").val();
        sum += +$("#total_amount").val();
        $("#net_amount").val(sum);
    });

    $(document).ready(function() {
        var sum = 0;
        sum += -$("#total_deduction").val();
        sum += +$("#total_amount").val();
        $("#net_amount").val(sum);
    });

</script>
@endsection

@section('breadcrumb')
  <li><a href="#"><i class=""></i> {{__('F&F')}} </a></li>
@endsection
@section('main_section')
    <section class="content"> 
         <div id="app">
                    @include('admin.flash-message')
                    @yield('content')
            </div>
              
              <br>
            <!-- general form elements -->
                <div class="row">
                <div class="row">
                <div class="box box-success" style="padding-bottom: 25px;"><br>
                 <?php
                    $a_date = date('Y-m-d');
                    $date = new DateTime($a_date);
                    $date->modify('last day of this month');
                    $last_day=$date->format('d');
                    $monthName = $date->format('F');                     
                ?>
                <div class="row">
                    <div class="col-md-3 " style="text-indent:30px"><label>Employee Name</label></div>
                    <div class="col-md-3 " style="text-indent:30px">{{$customerdata->name}}</div>
                    <div class="col-md-3 " style="text-indent:30px"><label>Employee ID</label></div>
                    <div class="col-md-3 " style="text-indent:30px">{{$customerdata->emp_id}}</div>
                </div>                
                <div class="row ">
                    <div class="col-md-3 " style="text-indent:30px"><label>Basic Salary</label></div>
                    <div class="col-md-3 " style="text-indent:30px">{{$basic_salary}}</div>
                    <div class="col-md-3 " style="text-indent:30px"><label>Month</label></div>
                    <div class="col-md-3 " style="text-indent:30px">{{$monthName}}</div>
                </div>
                @if(isset($payroll_salary))
                <div class="row ">
                     <div class="col-md-3 " style="text-indent:30px"><label>Type</label></div>
                     <div class="col-md-3 " style="text-indent:30px">{{$payroll_salary->type}}</div>
                </div>
                @endif
                <br/>
                 <hr>
                   @if(isset($payroll_salary_details))
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
                             <div class="col-sm-2 "><label>{{$net_salarys}}</label></div>
                        </div>
                    </div>
                  
                   @else
                   <form id="my-form" method="post" action="/admin/hr/user/resigned/{{$id}}" >
                     @csrf
                   <div class="row">   
                        <div class="col-md-6">
                          <div class="text-center"><label>Salary/Bonas <span style="font-size:12px;"> (Credit)</span></label>
                            <table id="table" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Sr.No. </th>
                                            <th>Name</th>
                                            <th>Amount</th>
                                        </tr>
                                         <tr>
                                            <td>1</td>
                                            <td>Salary <sup>*</sup></td>
                                            <td><input type="number" name="salary" class="amt input-css" value="{{$total}}" readonly>
                                             {!! $errors->first('salary', '<p class="help-block">:message</p>') !!}</td>
                                        </tr>
                                         <tr>
                                            <td>2</td>
                                            <td>Over Time <sup>*</sup></td>
                                            <td> <input type="number" name="ot" class="amt input-css" value="{{$ot_amt}}" readonly>
                                            {!! $errors->first('ot', '<p class="help-block">:message</p>') !!}
                                            </td>
                                        </tr>
                                         <tr>
                                            <td>3</td>
                                            <td>Arrears<sup>*</sup></td>
                                            <td> <input type="number" name="arrears" class="amt input-css" value="{{$arrears}}" readonly>
                                            {!! $errors->first('arrears', '<p class="help-block">:message</p>') !!}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>4</td>
                                            <td>Bonus<sup>*</sup></td>
                                            <td><input type="number" name="bonus" class="amt input-css" value="{{$bonus}}" readonly>
                                            {!! $errors->first('bonus', '<p class="help-block">:message</p>') !!}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>5</td>
                                            <td>CR Adjustment<sup>*</sup></td>
                                            <td> <input type="number" name="cr_adjustment" class="amt input-css" value="{{$cr_adjustment}}" readonly>
                                             {!! $errors->first('cr_adjustment', '<p class="help-block">:message</p>') !!}
                                            </td>
                                        </tr>
                                         <tr>
                                            <td>6</td>
                                            <td>Other CR<sup>*</sup></td>
                                            <td><input type="number" name="other_cr" class="amt input-css" >
                                            {!! $errors->first('other_deduction', '<p class="help-block">:message</p>') !!}
                                            </td>
                                        </tr>
                                    <tr>
                                     <td colspan="2" class="text-center"><b> Total (Credit) </b></td>
                                     <td><span id="total_cr"></span></td>
                                    </tr>
                                    </thead>
                                    <tbody></tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-md-6">
                                 <div class="text-center"><label>Salary/Bonas <span style="font-size:12px;"> (Credit)</span></label>
                            <table id="table" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Sr.No. </th>
                                            <th>Name</th>
                                            <th>Amount</th>
                                        </tr>
                                         <tr>
                                            <td>1</td>
                                            <td>Leave <sup>*</sup></td>
                                            <td><input type="number" name="leave" class="decu input-css" value="{{$dr_leave}}" readonly>
                                            {!! $errors->first('leave', '<p class="help-block">:message</p>') !!}</td>
                                        </tr>
                                         <tr>
                                            <td>2</td>
                                            <td>Penalty<sup>*</sup></td>
                                            <td><input type="number" name="penalty" class="decu input-css" value="{{$dr_penalty}}" readonly>
                                            {!! $errors->first('penalty', '<p class="help-block">:message</p>') !!}
                                            </td>
                                        </tr>
                                         <tr>
                                            <td>3</td>
                                            <td>Advance Amount<sup>*</sup></td>
                                            <td><input type="number" name="advance_amt" class="decu input-css" value="{{$advance_amt}}" readonly>
                                            {!! $errors->first('advance_amt', '<p class="help-block">:message</p>') !!}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>4</td>
                                            <td>DR Adjustment<sup>*</sup></td>
                                            <td> <input type="number" name="dr_adjustment" class="decu input-css" value="{{$dr_adjustment}}" readonly>
                                            {!! $errors->first('dr_adjustment', '<p class="help-block">:message</p>') !!}
                                            </td>
                                         </tr>
                                        <tr>
                                             <td>5</td>
                                             <td>Adjusment</td>
                                             <td> <input type="number" name="adjustment" class="decu input-css" >
                                             {!! $errors->first('adjustment', '<p class="help-block">:message</p>') !!}</td>
                                        </tr>
                                        <tr>
                                         <td colspan="2" class="text-center"><b>Total Deduction </b></td>
                                         <td> <input type="number" min="0" step="none" name="total_deduction" id="total_deduction" class="net input-css" readonly>
                                         {!! $errors->first('total_deduction', '<p class="help-block">:message</p>') !!}</td>
                                        </tr>    

                                    </thead>
                                    <tbody></tbody>
                            </table>
                        </div>
                    </div>
                    </div>
                     <div class="row justify-content-between">
                             <div class="col-sm-10 text-right"><label>Total Amount :</label></div>
                             <div class="col-sm-2 "><input type="number" min="0" step="none" name="total_amount" id="total_amount" class="net input-css" readonly>
                                {!! $errors->first('total_amount', '<p class="help-block">:message</p>') !!}</div>
                        </div>
                        <div class="row justify-content-between">
                             <div class="col-sm-10 text-right"><label>Net Amount :</label></div>
                             <div class="col-sm-2 "><input type="number" min="0" step="none" name="net_amount" id="net_amount" class="input-css" readonly>
                                {!! $errors->first('net_amount', '<p class="help-block">:message</p>') !!}</div>
                        </div>
                        <div class="submit-button">
                        <button type="submit" class="btn btn-primary">Submit</button> 
                        </div> 
                         </form>  
                         @endif         
       </div>
    </div>
      </section>
@endsection