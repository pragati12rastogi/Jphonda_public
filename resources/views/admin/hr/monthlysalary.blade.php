
@extends($layout)
@section('title', __('Monthly Salary'))

@section('breadcrumb')
<li><a href=""><i class="">{{__('Monthly Salary')}}</i></a></li>
<style>
 .show_details,.show_details_pre {
    cursor: pointer;
  }
  .salary_key{
    margin-left: 50px;
  }
  table, th, td {
    padding:15px;
    font-size: 14px;
  }
</style>
@endsection
@section('js')
<script>

   $('#js-msg-error').hide();
    $("#salaryDate").change(function(){
       $('#js-msg-error').hide();
        var date = $("#salaryDate").val();
        cd = (new Date()).toISOString().split('T')[0];
        if (date == '') {
          date = cd;
        }
        console.log(date);
        var id = $("#id").val();
        $.ajax({
        method: "GET",
        url: "/admin/hr/user/get/salary",
        data: {'id':id,'date':date},
        success:function(data) {
            var i = 1; var p = 0; var a = 0; var wo = 0;var k=0 ; var j=0; var msg1='';
            var ddate = new Date(date);
            var last_day = new Date(1900+ddate.getYear(), ddate.getMonth()+1, 0).getDate();
            var monthName = ddate.getMonth() + 1;
            var monthNames = ["January", "February", "March", "April", "May", "June",
                      "July", "August", "September", "October", "November", "December"
                    ];
            var month =  monthNames[ddate.getMonth()];
            var one_day_salary = data.net_salary/30;
             $(".du_msg1").hide();
             var month_basic_salary=data.basic_salary;
              var month_net_salary=data.month_net_salary;
              var details='';var pre_details='';
              var total_cr=0;var total_deduction=0;

            if(data.payroll_salary_details){
              $("#primary").hide();
                 $.each(data.payroll_salary_details, function(key, value) {
                          if(value.name=='Month Basic Salary'){
                              month_basic_salary=value.amount;
                          }if(value.name=='Month Net Salary'){
                               month_net_salary=value.amount;
                          }
                    });
                 
                 

                  $.each(data.payroll_salary_details, function(key, value) {
                          if(value.name=='Month Basic Salary'){
                            details+='<div class="col-sm-6"><div class="col-sm-6"><b>'+value.name+'</b></div><div class="col-sm-6">'+value.amount+'</div></div>';
                          }if(value.name=='Month Hra'){
                            details+='<div class="col-sm-6"><div class="col-sm-6"><b>'+value.name+'</b></div><div class="col-sm-6">'+value.amount+'</div></div>';
                          }if(value.name=='Month Ta'){
                            details+='<div class="col-sm-6"><div class="col-sm-6"><b>'+value.name+'</b></div><div class="col-sm-6">'+value.amount+'</div></div>';
                          }if(value.name=='Month Perf Allowance'){
                            details+='<div class="col-sm-6"><div class="col-sm-6"><b>'+value.name+'</b></div><div class="col-sm-6">'+value.amount+'</div></div>';
                          }if(value.name=='Month Others'){
                            details+='<div class="col-sm-6"><div class="col-sm-6"><b>'+value.name+'</b></div><div class="col-sm-6">'+value.amount+'</div></div>';
                          }if(value.name=='Month Tax Deduction'){
                            details+='<div class="col-sm-6"><div class="col-sm-6"><b>'+value.name+'</b></div><div class="col-sm-6">'+value.amount+'</div></div>';
                          }if(value.name=='Month Pf Deduction'){
                            details+='<div class="col-sm-6"><div class="col-sm-6"><b>'+value.name+'</b></div><div class="col-sm-6">'+value.amount+'</div></div>';
                          }if(value.name=='Month Other Deduction'){
                            details+='<div class="col-sm-6"><div class="col-sm-6"><b>'+value.name+'</b></div><div class="col-sm-6">'+value.amount+'</div></div>';
                          }if(value.name=='Month Net Salary'){
                            details+='<div class="col-sm-6"><div class="col-sm-6"><b>'+value.name+'</b></div><div class="col-sm-6">'+value.amount+'</div></div>';
                          }
                    });

             
               var html ='<div class="row"><br><div class="row"><div class="col-md-3 " style="text-indent:30px"><label>Employee Name</label></div><div class="col-md-3 " style="text-indent:30px">'+data.customerdata.name+'</div><div class="col-md-3 " style="text-indent:30px"><label>Employee ID</label></div><div class="col-md-3 " style="text-indent:30px">'+data.customerdata.emp_id+'</div></div><div class="row "><div class="col-md-3 " style="text-indent:30px"><label>Basic Salary</label></div><div class="col-md-3 " style="text-indent:30px">'+month_basic_salary+'</div><div class="col-md-3 " style="text-indent:30px"><label>Month</label></div><div class="col-md-3 " style="text-indent:30px">'+month+'</div></div><div class="row "><div class="col-md-3 " style="text-indent:30px;"><label> Month Net Salary</label></div><div class="col-md-3 show_details" onclick="showModalSelect()" style="text-indent:30px;">'+month_net_salary+'</div><div class="col-md-3 " style="text-indent:30px"><label>Overtime</label></div><div class="col-md-3 " style="text-indent:30px">'+data.overtime_hours+' Hours</div></div><br/>';
              

                   html+='<hr><div class="container-fluid"><div class="col-md-6 text-center"><label>Salary/Bonas <span style="font-size:12px;"> (Credit)</span></label>';
                   html+='<table id="table" class="table table-bordered table-striped"><thead><tr><th>ID</th><th>Name</th><th>Amount</th></tr></thead>';
                var dr_sum=0;
                var cr_sum=0;
                var cr=0;
                var dr=0;
                  $.each(data.payroll_salary_details, function(key, value) {
                        if(value.name=='Month Basic Salary'){
                             return true;
                        }if(value.name=='Month Ta'){
                             return true;
                        }if(value.name=='Month Hra'){
                            return true;
                        }if(value.name=='Month Perf Allowance'){
                            return true;
                        }if(value.name=='Month Others'){
                            return true;
                        }if(value.name=='Month Net Salary'){
                             return true;
                        }

                          if(value.type=='CR'){                         
                    cr++;
                        cr_sum+=value.amount;                     
                            html+='<tr><td>'+cr+'</td><td>'+value.name+'</td><td>'+value.amount+'</td></tr>';
                          }
                    });
                   html+='<tr><td colspan="2" class="text-center"><b> Total (Credit) </b></<td><td>'+cr_sum.toFixed(0)+'</td></tr></table></div><div class="col-md-6 text-center"><label>Deduction <span style="font-size:12px;"> (Debit)</span></label><table id="table" class="table table-bordered table-striped"><thead><tr><th>ID</th><th>Name</th><th>Amount</th></tr></thead>';

                    $.each(data.payroll_salary_details, function(key, value) {
                                        if(value.name=='Month Other Deduction'){
                                              return true;
                                          }if(value.name=='Month Pf Deduction'){
                                              return true;
                                          }if(value.name=='Month Tax Deduction'){
                                            return true;
                                        }
                          if(value.type=='DR'){                         
                    dr++;
                        dr_sum+=value.amount;                      
                            html+='<tr><td>'+dr+'</td><td>'+value.name+'</td><td>'+value.amount+'</td></tr>';
                          }
                    });

                    var total_a=data.net_salarys;
                      html+='<tr><td colspan="2" class="text-center"><b> Total (Debit) </b></<td><td>'+dr_sum.toFixed(0)+'</td></tr></table></div></div>';
                       html+='<div class="row justify-content-between"><div class="col-sm-10 text-right"><label>Net Amount :</label></div><div class="col-sm-2 "><label>'+total_a+'</label></div></div>'


            }else{
                
                $("#primary").hide();
                var total_dr= parseInt(data.dr_leave+data.dr_penalty+data.dr_adjustment+data.advance_amt);

                 msg1=parseFloat(data.total_absent_amt);
                 
                var html ='<div class="row"><br><div class="row"><div class="col-md-3 " style="text-indent:30px"><label>Employee Name</label></div><div class="col-md-3 " style="text-indent:30px">'+data.customerdata.name+'</div><div class="col-md-3 " style="text-indent:30px"><label>Employee ID</label></div><div class="col-md-3 " style="text-indent:30px">'+data.customerdata.emp_id+'</div></div><div class="row "><div class="col-md-3 " style="text-indent:30px"><label>Basic Salary</label></div><div class="col-md-3 " style="text-indent:30px">'+data.basic_salary+'</div><div class="col-md-3 " style="text-indent:30px"><label>Month</label></div><div class="col-md-3 " style="text-indent:30px">'+month+'</div></div><div class="row "><div class="col-md-3 " style="text-indent:30px;"><label>Month Net Salary</label></div><div class="col-md-3 show_details" onclick="showModaldetails()" style="text-indent:30px;">'+data.month_net_salary+'</div><div class="col-md-3 " style="text-indent:30px"><label>Overtime</label></div><div class="col-md-3 " style="text-indent:30px">'+data.overtime_hours+' Hours</div></div><br/>';
                 html+='<div class="row du_msg1"><div class="col-sm-3"></div><div class="col-sm-6 alert alert-danger text-center">Leave Deduction miss match for this user</span></div><div class="col-sm-3"></div></div></div>';

                   html+='<hr><div class="container-fluid"><div class="col-md-6 text-center"><label>Salary/Bonas <span style="font-size:12px;"> (Credit)</span></label>';
                   html+='<table id="table" class="table table-bordered table-striped"><thead><tr><th>ID</th><th>Status</th><th>Number</th><th>Amount</th></tr></thead>';

                   pre_details+='<div class="col-sm-6"><div class="col-sm-6"><b>Month Basic Salary</b></div><div class="col-sm-6">'+data.basic_salary+'</div></div>';
                   pre_details+='<div class="col-sm-6"><div class="col-sm-6"><b>Month Hra</b></div><div class="col-sm-6">'+data.hra+'</div></div>';
                   pre_details+='<div class="col-sm-6"><div class="col-sm-6"><b>Month Ta</b></div><div class="col-sm-6">'+data.ta+'</div></div>';
                   pre_details+='<div class="col-sm-6"><div class="col-sm-6"><b>Month Other Deduction</b></div><div class="col-sm-6">'+data.other_deduction+'</div></div>';
                   pre_details+='<div class="col-sm-6"><div class="col-sm-6"><b>Month Other</b></div><div class="col-sm-6">'+data.others+'</div></div>';
                   pre_details+='<div class="col-sm-6"><div class="col-sm-6"><b>Month Perf Allowance</b></div><div class="col-sm-6">'+data.perf_allowance+'</div></div>';
                   pre_details+='<div class="col-sm-6"><div class="col-sm-6"><b>Month Pf Deduction</b></div><div class="col-sm-6">'+data.pf_deduction+'</div></div>';
                   pre_details+='<div class="col-sm-6"><div class="col-sm-6"><b>Month Tax Deduction</b></div><div class="col-sm-6">'+data.tax_deduction+'</div></div>';
                   pre_details+='<div class="col-sm-6"><div class="col-sm-6"><b>Month Net Salary</b></div><div class="col-sm-6">'+data.month_net_salary+'</div></div>';

                   var overtime_amount = data.ot_amt;
                   var wo_salary = data.wo_amt;
                   var absent_salary = data.absent_amt;
                   var netSalary = data.net_salary;
                   var month_salary = data.month_salary;
                   var penalty_count=data.penalty_count;
                   var total_absent=data.total_absent;
                   if(penalty_count=='0'){
                      penalty_count='';
                   }else{
                     penalty_count;
                   }
                    if(total_absent=='0'){
                      total_absent='';
                   }else{
                     total_absent;
                   }
                   var dr_data=parseFloat(data.total_absent_amt+data.dr_penalty+data.dr_adjustment+data.advance_amt);
                   total_cr=parseFloat(data.total_present_amt+data.bonus+data.arrears+data.cr_adjustment+data.ot_amt);
                   total_deduction= parseInt(Math.abs(data.total_absent_amt)+data.dr_penalty+data.dr_adjustment+data.advance_amt);
                   var Total = parseFloat(netSalary);
                   if(data.user_attendance==0){
                      $('#js-msg-error').html("This month attendance 0 for this user").show();
                   }else{
                      $("#js-msg-error").hide();
                   }

                    html+='<tr><td>1</td><td>Net Salary</td><td>'+data.total_present+'</td><td>'+data.total_present_amt.toFixed(0)+'</td></tr><tr><td>2</td><td>Total Arrears</td><td></td> <td>'+data.arrears+'</td></tr><tr><td>3</td><td>Total Bonus</td><td></td> <td>'+data.bonus+'</td></tr><tr><td>4</td><td>Total OverTime</td><td>'+data.overtime_hours+' Hours'+'</td> <td>'+overtime_amount.toFixed(0)+'</td></tr><tr><td>5</td><td>Total CR Adjustment</td><td></td> <td>'+data.cr_adjustment+'</td></tr><tr><td colspan="3" class="text-center"><b> Total (Credit) </b></<td><td>'+total_cr.toFixed(0)+'</td></tr></table></div>'; 
                  
                   html+=' <div class="col-md-6 text-center"><label>Deduction <span style="font-size:12px;"> (Debit)</span></label><table id="table" class="table table-bordered table-striped"><thead><tr><th>ID</th><th>Status</th><th>Number</th><th>Amount</th></tr></thead>';
                   html+='<tr><td>1</td><td>Total Penalty</td><td>'+penalty_count+'</td> <td>'+data.dr_penalty+'</td></tr><tr><td>2</td><td>Total Leave</td><td>'+Math.abs(total_absent)+'</td> <td>'+Math.abs(data.total_absent_amt)+'</td></tr><tr><td>3</td><td>Total Advance Amount</td><td></td> <td>'+data.advance_amt+'</td></tr><tr><td>4</td><td>Total DR Adjustment</td><td></td> <td>'+data.dr_adjustment+'</td></tr><tr><td colspan="3" class="text-center"><b>Total (Debit) </b></td><td>'+total_deduction.toFixed(0)+'</td></tr></table></div></div>';
                   html+='<div class="row justify-content-between"><div class="col-sm-10 text-right"><label>Net Amount :</label></div><div class="col-sm-2 "><label>'+Total.toFixed(0)+'</label></div></div>'
            }
               
                    $('#secondary').html(html);
                    $('.select_month').html(details);
                    $('.select_pre_month').html(pre_details);
                    if(msg1<0 || total_cr<total_deduction){
                     $(".du_msg1").show();
                    }else{
                     $(".du_msg1").hide();
                    }
  
        },
          error: function(error){
             if ((error.responseJSON).hasOwnProperty('messages')) {
                    $('#js-msg-error').html(error.responseJSON.messages).show();
                  }
          },
      });
    });
$(document).ready(function() {

   $(".du_msg").hide();
   $(".du_msg1").hide();
   $('#salaryDate').datepicker({
    format: "mm",
    weekStart: 1,
    orientation: "bottom",
    keyboardNavigation: false,
    viewMode: "months",
    minViewMode: "months",
    autoclose: true
});

 var msg=@php echo $total_absent_amt@endphp ;
 var total_cr=@php echo round($total_present_amt+$arrears+$bonus+$ot_amt+$cr_adjustment)@endphp ;
 var total_dr=@php echo round(abs($total_absent_amt)+$dr_penalty+$dr_adjustment+$advance_amt)@endphp ;
 if(msg<0 || total_cr<total_dr){
 $(".du_msg").show();
 }else{
 $(".du_msg").hide();
 }
 var error=@php echo $user_attendance@endphp ;
 if(error==0){
    $('#js-msg-error').html("This month attendance 0 for this user").show();
 }else{
    $("#js-msg-error").hide();
 }
});

function showModalSelect(){
   $(".cu_month").hide();
   $(".select_month").show();
   $(".pre_month").hide();
   showModal();
}

function showModaldetails(){
   $(".cu_month").hide();
   $(".select_month").hide();
   $(".pre_month").hide();
   $(".select_pre_month").show();
   showModal();
}

$(".show_details").on('click',function(){
  $(".pre_month").hide();
  $(".select_month").hide();
  $(".cu_month").show();
  showModal();

});
$(".show_details_pre").on('click',function(){
  
  $(".cu_month").hide();
  $(".select_month").hide();
  $(".pre_month").show();

  showModal();

});

function showModal(){
  $('#salaryModal').modal();
}

</script>
@endsection
@section('main_section')
    <section class="content">
    <div id="app">
        @include('admin.flash-message')
        @yield('content')
    </div>
<!-- Default box --> 
  
    <div class="box-header with-border">
        <div class='box box-default'>
            <div class="row">
               <div class="row">
                <br/><br/>
                <div class="alert alert-danger" id="js-msg-error">
                </div>
            <div class="col-md-3">
                    <label>Check previous month salary</label>
                    <input type="hidden" name="id" id="id" value="{{$id}}">
                     <input type="text" name="salaryDate" id="salaryDate" class="form-control" autocomplete="off">

                    <!-- <input type="text" name="salaryDate" id="salaryDate" class="datepicker form-control" data-provide="datepicker" data-date-end-date="0d"> -->
                </div>
            </div>
            <div id="primary">
              <?php $i = 1; $p=0; $a=0; $wo=0;
                    $a_date = date('Y-m-d');
                    $date = new DateTime($a_date);
                    $monthName = $date->format('F'); 
                    $one_day_salary=$net_salary/30;
                    $month_net_sal=$month_net_salary;
                    $month_basic_salary=$basic_salary;
                ?>
       @if(isset($payroll_salary_details))
         @foreach($payroll_salary_details as $details1)
              @if($details1->name=='Month Net Salary')
                  @php $month_net_salary=$details1->amount @endphp
              @endif
               @if($details1->name=='Month Basic Salary')
                  @php $month_basic_salary=$details1->amount @endphp
              @endif
         @endforeach
            <div class="row">
                <br>
                <div class="row">
                    <div class="col-md-3 " style="text-indent:30px"><label>Employee Name</label></div>
                    <div class="col-md-3 " style="text-indent:30px">{{$customerdata->name}}</div>
                    <div class="col-md-3 " style="text-indent:30px"><label>Employee ID</label></div>
                    <div class="col-md-3 " style="text-indent:30px">{{$customerdata->emp_id}}</div>
                </div>                
                <div class="row ">
                    <div class="col-md-3 " style="text-indent:30px"><label>Basic Salary</label></div>
                    <div class="col-md-3 " style="text-indent:30px">{{$month_basic_salary}}</div>
                    <div class="col-md-3 " style="text-indent:30px"><label>Month</label></div>
                    <div class="col-md-3 " style="text-indent:30px">{{$monthName}}</div>
                </div>
                <div class="row">
                  <div class="col-md-3 " style="text-indent:30px"><label>Month Net Salary</label></div>
                  <div class="col-md-3 show_details" style="text-indent:30px">{{$month_net_salary}}</div>
                  <div class="col-md-3 " style="text-indent:30px"><label>Overtime</label></div>
                  <div class="col-md-3 " style="text-indent:30px">{{$overtime_hours}} @if($overtime_hours > 0) Hours @endif</div>
                </div>
                <br/>
                 
        </div>
         <div class="row du_msg">
              <div class="col-sm-3"></div>
               <div class="col-sm-6 alert alert-danger text-center">Leave Deduction miss match for this user</span></div>
               <div class="col-sm-3"></div>
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
                             <div class="col-sm-2 "><label>{{$net_salarys}}</label></div>
                        </div>
                    </div>
       @else
                   <div class="row">
                <br>
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
                <div class="row">
                  <div class="col-md-3 " style="text-indent:30px"><label>Month Net Salary</label></div>
                  <div class="col-md-3 show_details_pre" style="text-indent:30px">{{$month_net_salary}}</div>
                  <div class="col-md-3 " style="text-indent:30px"><label>Overtime</label></div>
                  <div class="col-md-3 " style="text-indent:30px">{{$overtime_hours}} @if($overtime_hours > 0) Hours @endif</div>
                </div>
                <br/>
                 
        </div>
         <div class="row du_msg">
              <div class="col-sm-3"></div>
               <div class="col-sm-6 alert alert-danger text-center">Leave Deduction miss match for this user</span></div>
               <div class="col-sm-3"></div>
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
                                    <th>Number</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                           
                                   <tr>
                                        <td>1</td> 
                                        <td>Net Salary</td>
                                        <td>{{$total_present}}</td>
                                        <td>{{$total_present_amt}}</td>
                                    </tr>
                                   <tr>
                                      <td>2</td> 
                                      <td>Total Arrears</td>
                                      <td></td>
                                      <td>{{$arrears}}</td>
                                  </tr>
                                  <tr>
                                      <td>3</td> 
                                      <td>Total Bonus</td>
                                      <td></td>
                                      <td>{{$bonus}}</td>
                                  </tr>
                                   <tr>
                                        <td>4</td> 
                                        <td>Total OverTime</td>
                                        <td>{{$overtime_hours}} Hours</td>
                                        <td>{{$ot_amt}}</td>
                                    </tr>
                                      <tr>
                                        <td>5</td> 
                                        <td>Total CR Adjustment</td>
                                        <td></td>
                                        <td>{{$cr_adjustment}}</td>
                                    </tr>
                                  
                                   <tr>
                                     <td colspan="3" class="text-center"><b> Total (Credit) </b></td>
                                     <td id="cr_total">{{round($total_present_amt+$arrears+$bonus+$ot_amt+$cr_adjustment)}}</td>
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
                                            <th>Number</th>
                                            <th>Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                       
                                            <tr>
                                              <td>1</td> 
                                              <td>Total Penalty</td>
                                              <td>@if($penalty_count=='0')&nbsp;@else{{$penalty_count}}@endif</td>
                                              <td>{{$dr_penalty}}</td>
                                          </tr>
                                           <tr>
                                              <td>2</td> 
                                              <td>Total Leave</td>
                                              <td>@if($total_absent=='0')&nbsp;
                                              @else{{abs($total_absent)}}@endif</td>
                                              <td>{{abs($total_absent_amt)}}</td>
                                          </tr>
                                          <tr>
                                            <td>3</td> 
                                            <td>Total Advance Amount</td>
                                            <td></td>
                                            <td>{{$advance_amt}}</td>
                                        </tr>
                                        <tr>
                                          <td>4</td> 
                                          <td>Total DR Adjustment</td>
                                          <td></td>
                                          <td>{{$dr_adjustment}}</td>
                                        </tr>
                                         
                                           <tr>
                                             <td colspan="3" class="text-center"><b>Total (Debit) </b></td>
                                             <td id="dr_total">{{round(abs($total_absent_amt)+$dr_penalty+$dr_adjustment+$advance_amt)}}</td>
                                            </tr>
                                    </tbody>
                          </table>
                        </div>
                        </div>
                        <div class="row justify-content-between">
                             <div class="col-sm-10 text-right"><label>Net Amount :</label></div>
                             <div class="col-sm-2 "><label>{{round($net_salary)}}</label></div>
                        </div>
                    </div>
       @endif


          </div>
          <div id="secondary"> </div>
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
            <table>
           
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
               </table>
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
            <div class="row select_month"></div>
            <div class="row select_pre_month"></div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
    </div>
</div>
</div> 
@endsection
