@extends($layout)

@section('title', __('Salary Details Page'))

@section('js')
<!-- <script src="/js/pages/hr.js"></script> -->
<script>
    $('#dob').datepicker({
        format: "yyyy-mm-dd"
    });
    $('#doj').datepicker({
        format: "yyyy-mm-dd"
    });
//For Gross Salary 
   $(document).on("keyup", ".amt", function() {
        var sum = 0;
        $(".amt").each(function(){
            sum += +$(this).val();
        });
        $("#gross_salary").val(sum);
         var deduction =  $("#total_deduction").val();
        $("#net_salary").val(sum-deduction);
    });
   $(document).ready(function() {
        var sum = 0;
        $(".amt").each(function(){
            sum += +$(this).val();
        });
        $("#gross_salary").val(sum);
        var deduction =  $("#total_deduction").val();
        $("#net_salary").val(sum-deduction);
    });

   $(document).on("keyup", ".pf", function() {
        var sum = 0;
        $(".pf").each(function(){
            sum += +$(this).val();
        });
        $("#total_provident_fund").val(sum);
    });

      $(document).ready(function() {
        var sum = 0;
        $(".pf").each(function(){
            sum += +$(this).val();
        });
        $("#total_provident_fund").val(sum);
    });

    $(document).on("keyup", ".deduction", function() {
        var sum = 0;
        $(".deduction").each(function(){
            sum += +$(this).val();
        });
        $("#total_deduction").val(sum);
         var gross_salary =  $("#gross_salary").val();
        $("#net_salary").val(gross_salary-sum);
    });
     $(document).ready(function() {
        var sum = 0;
        $(".deduction").each(function(){
            sum += +$(this).val();
        });
        $("#total_deduction").val(sum);
        var gross_salary =  $("#gross_salary").val();
        $("#net_salary").val(gross_salary-sum);
    });

    $(document).on("keyup", ".net", function() {
        var sum = 0;
        sum += -$("#total_deduction").val();
        sum += +$("#gross_salary").val();
        $("#net_salary").val(sum);
    });

    $(document).ready(function() {
        var sum = 0;
        sum += -$("#total_deduction").val();
        sum += +$("#gross_salary").val();
        $("#net_salary").val(sum);
    });

</script>
@endsection

@section('breadcrumb')
  <li><a href="#"><i class=""></i> {{__('Salary Details Page')}} </a></li>
@endsection
@section('main_section')
    <section class="content"> 
         <div id="app">
                    @include('admin.flash-message')
                    @yield('content')
            </div>
               <form id="my-form" action="/admin/payroll/user/salaryupdate/{{$id}}" method="POST">
                @csrf
                  @include('layouts.user_tab')
              <br>
            <!-- general form elements -->
                <div class="row">
                <div class="col-md-12">
                  <div class="box box-success" style="padding-bottom: 25px;">
                      <div class="box-header with-border">
                      </div> 
                        <div class="row margin-bottom">
                          <div class="col-md-8">
                              <label>Basic Salary<sup>*</sup></label>
                              <input type="number" min="0" step="none" name="salary" id="salary" class="amt input-css" value="{{(old('salary')) ? old('salary') : $customer->basic_salary}}">
                              {!! $errors->first('salary', '<p class="help-block">:message</p>') !!}
                              <br>
                          </div>
                        </div>
                  </div>
                </div>

                  <div class="col-md-6">
                    <div class="row">
                      <div class="box box-success" style="padding-bottom: 25px;">
                        <div class="box-header with-border">
                          <h4>Allowances</h4>
                        </div> 
                        <div class="row margin-bottom">
                                <div class="col-md-12">
                                    <label>Hra <sup>*</sup></label>
                                    <input type="number" min="0" step="none" name="hra" id="hra" class="amt input-css" value="{{(old('hra')) ? old('hra') : $customer->hra}}">
                                    {!! $errors->first('hra', '<p class="help-block">:message</p>') !!}
                                    <br>
                                </div>                        
                        </div>
                        <div class="row margin-bottom">
                                <div class="col-md-12">
                                    <label>Ta<sup>*</sup></label>
                                    <input type="number" min="0" step="none" name="ta" id="ta" class="amt input-css" value="{{(old('ta')) ? old('ta') : $customer->ta}}">
                                    {!! $errors->first('ta', '<p class="help-block">:message</p>') !!}
                                    <br>
                                </div>
                        </div>
                         <div class="row margin-bottom">
                                <div class="col-md-12">
                                    <label>Performance Allowance</label>
                                    <input type="number" min="0" step="none" name="perf_allowance" id="perf_allowance" class="amt input-css" value="{{(old('perf_allowance')) ? old('perf_allowance') : $customer->perf_allowance}}">
                                    {!! $errors->first('perf_allowance', '<p class="help-block">:message</p>') !!}
                                    <br>
                                </div> 
                                
                        </div>
                        <div class="row margin-bottom">
                            <div class="col-md-12">
                                <label>Other</label>
                                <input type="number" min="0" step="none" name="others" id="others" class="amt input-css" value="{{(old('others')) ? old('others') : $customer->others}}">
                                {!! $errors->first('others', '<p class="help-block">:message</p>') !!}
                                <br>
                            </div>
                        </div>
                        <div class="row margin-bottom">
                            <div class="col-md-12">
                                <label>PF</label>
                                <input type="number" min="0" step="none" name="pf" id="pf" class="pf input-css" value="{{(old('pf')) ? old('pf') : $customer->pf}}">
                                {!! $errors->first('pf', '<p class="help-block">:message</p>') !!}
                                <br>
                            </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="row">
                      <div class="col-md-12">
                        <div class="row">
                        <div class="box box-warning" style="padding-bottom: 25px;">
                          <div class="box-header with-border">
                             <h4>Deductions</h4>
                          </div> 
                           <div class="row margin-bottom">
                                <div class="col-md-12">
                                    <label>Tax Deduction</label>
                                    <input type="number" min="0" step="none" name="tax_deduction" class="deduction input-css" id="tax_deduction" value="{{(old('tax_deduction')) ? old('tax_deduction') : $customer->tax_deduction}}">
                                    {!! $errors->first('tax_deduction', '<p class="help-block">:message</p>') !!}
                                </div>
                        </div>
                         <div class="row margin-bottom">
                                <div class="col-md-12">
                                    <label>Provident Fund Deduction </label>
                                    <input type="number" min="0" step="none" name="provident_fund" id="provident_fund" class="pf deduction input-css" value="{{(old('provident_fund')) ? old('provident_fund') : $customer->pf_deduction}}">
                                    {!! $errors->first('provident_fund', '<p class="help-block">:message</p>') !!}
                                </div> 
                                
                        </div>
                        <div class="row margin-bottom">
                            <div class="col-md-12">
                                <label>Other Deduction</label>
                                <input type="number" min="0" step="none" name="other_deduction" class="deduction input-css" id="other_deduction" value="{{(old('other_deduction')) ? old('other_deduction') : $customer->other_deduction}}">
                                {!! $errors->first('other_deduction', '<p class="help-block">:message</p>') !!}
                            </div>
                        </div>
                        </div>
                        </div>
                      </div>
                       <div class="col-md-12">
                        <div class="row">
                        <div class="box box-success" style="padding-bottom: 25px;">
                          <div class="box-header with-border">
                            <h4>Provident Fund</h4>
                          </div> 
                           <div class="row margin-bottom">
                                <div class="col-md-12">
                                    <label> Total Provident Fund  <sup>*</sup></label>
                                    <input type="number" min="0" step="none" name="total_provident_fund" id="total_provident_fund" class="input-css" value="{{(old('total_provident_fund')) ? old('total_provident_fund') : $customer->total_provident_fund}}">
                                    {!! $errors->first('total_provident_fund', '<p class="help-block">:message</p>') !!}
                                </div> 
                                
                        </div>
                        </div>
                        </div>
                      </div>
                       <div class="col-md-12">
                        <div class="row">
                        <div class="box box-primary" style="padding-bottom: 25px;">
                          <div class="box-header with-border">
                            <h4>Total Salary Details</h4>
                          </div> 
                          <div class="row margin-bottom">
                                <div class="col-md-12">
                                    <label>Total Deduction  <sup>*</sup></label>
                                    <input type="number" min="0" step="none" name="total_deduction" id="total_deduction" class="net input-css" value="{{(old('total_deduction')) ? old('total_deduction') : $customer->total_deduction}}">
                                    {!! $errors->first('total_deduction', '<p class="help-block">:message</p>') !!}
                                </div> 
                                
                        </div>
                        <div class="row margin-bottom">
                            <div class="col-md-12">
                                <label>Gross Salary <sup>*</sup></label>
                                <input type="number" min="0" step="none" name="gross_salary" id="gross_salary" class="net input-css" value="{{(old('gross_salary')) ? old('gross_salary') : $customer->gross_salary}}">
                                {!! $errors->first('gross_salary', '<p class="help-block">:message</p>') !!}
                            </div>  
                        </div>
                         <div class="row margin-bottom">
                            <div class="col-md-12">
                                <label>Net Salary <sup>*</sup></label>
                                <input type="number" min="0" step="none" name="net_salary" id="net_salary" class="input-css" value="{{(old('net_salary')) ? old('net_salary') : $customer->net_salary}}">
                                {!! $errors->first('net_salary', '<p class="help-block">:message</p>') !!}
                            </div>  
                        </div>
                        </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                
                <div class="submit-button">
                <button type="submit" class="btn btn-primary">Submit</button>
                    
                </div>
                 </form>                 
       
      </section>
@endsection