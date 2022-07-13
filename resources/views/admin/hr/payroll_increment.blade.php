@extends($layout)

@section('title', __('Payroll Increment'))

@section('js')
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
         $("#net_salary").val(sum);
    });
   $(document).ready(function() {
        var sum = 0;
        $(".amt").each(function(){
            sum += +$(this).val();
        });
        $("#gross_salary").val(sum);
        $("#net_salary").val(sum);
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
    });
     $(document).ready(function() {
        var sum = 0;
        $(".deduction").each(function(){
            sum += +$(this).val();
        });
        $("#total_deduction").val(sum);
    });

    $(document).on("keyup", ".net", function() {
        sum = $("#gross_salary").val();
        $("#net_salary").val(sum);
    });

    $(document).ready(function() {
        sum = $("#gross_salary").val();
        $("#net_salary").val(sum);
    });

     $(".datepicker").datepicker({ format: 'dd/mm/yyyy' });

</script>
@endsection

@section('breadcrumb')
  <li><a href="#"><i class=""></i> {{__('Payroll Increment')}} </a></li>
@endsection
@section('main_section')
    <section class="content"> 
         <div id="app">
                    @include('admin.flash-message')
                    @yield('content')
            </div>
               <form id="my-form" action="/admin/payroll/increment" method="POST">
                @csrf
              <br>
            <!-- general form elements -->
                <div class="row">
                <div class="col-md-12">
                  <div class="box box-success" style="padding-bottom: 25px;">
                      <div class="box-header with-border">
                      </div> 
                      <div class="row">
                        <div class="row margin-bottom">
                          <div class="col-md-6">
                              <label>Employee<sup>*</sup></label>
                              <select name="employee" class="input-css select2">
                                <option selected disabled>Select Employee</option>
                                 @foreach ($userdata as $item)
                                  <option value="{{$item->id}}" {{old('employee')==$item->id ? 'selected=selected' :''}}>{{$item->name}} {{$item->middle_name}} {{$item->last_name}}</option>
                                @endforeach
                              </select>
                              {!! $errors->first('employee', '<p class="help-block">:message</p>') !!}
                              <br>
                          </div>
                          <div class="col-md-6">
                              <label>Salary</label>
                              <input type="number" min="0" step="none" name="salary" id="salary" class="amt input-css" value="{{(old('salary')) ? old('salary') : ''}}">
                              {!! $errors->first('salary', '<p class="help-block">:message</p>') !!}
                              <br>
                          </div>
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
                                    <label>Hra</label>
                                    <input type="number" min="0" step="none" name="hra" id="hra" class="amt input-css" value="{{(old('hra')) ? old('hra') : ''}}">
                                    {!! $errors->first('hra', '<p class="help-block">:message</p>') !!}
                                    <br>
                                </div>                        
                        </div>
                        <div class="row margin-bottom">
                                <div class="col-md-12">
                                    <label>Ta</label>
                                    <input type="number" min="0" step="none" name="ta" id="ta" class="amt input-css" value="{{(old('ta')) ? old('ta') : ''}}">
                                    {!! $errors->first('ta', '<p class="help-block">:message</p>') !!}
                                    <br>
                                </div>
                        </div>
                         <div class="row margin-bottom">
                                <div class="col-md-12">
                                    <label>Performance Allowance </label>
                                    <input type="number" min="0" step="none" name="perf_allowance" id="perf_allowance" class="amt input-css" value="{{(old('perf_allowance')) ? old('perf_allowance') : ''}}">
                                    {!! $errors->first('perf_allowance', '<p class="help-block">:message</p>') !!}
                                    <br>
                                </div> 
                                
                        </div>
                        <div class="row margin-bottom">
                            <div class="col-md-12">
                                <label>Other</label>
                                <input type="number" min="0" step="none" name="others" id="others" class="amt input-css" value="{{(old('others')) ? old('others') : ''}}">
                                {!! $errors->first('others', '<p class="help-block">:message</p>') !!}
                                <br>
                            </div>
                        </div>
                        <div class="row margin-bottom">
                            <div class="col-md-12">
                                <label>PF</label>
                                <input type="number" min="0" step="none" name="pf" id="pf" class="pf input-css" value="{{(old('pf')) ? old('pf') : ''}}">
                                {!! $errors->first('pf', '<p class="help-block">:message</p>') !!}
                                <br>
                            </div>
                        </div>
                          <div class="row margin-bottom">
                            <div class="col-md-12">
                                <label>Month Effective Date <sup>*</sup></label>
                                <input type="text" name="month_effective" id="month_effective" class="datepicker input-css " value="{{(old('month_effective')) ? old('month_effective') : ''}}">
                                {!! $errors->first('month_effective', '<p class="help-block">:message</p>') !!}
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
                        <div class="box box-success" style="padding-bottom: 25px;">
                          <div class="box-header with-border">
                            <h4>Provident Fund</h4>
                          </div> 
                           <div class="row margin-bottom">
                                <div class="col-md-12">
                                    <label> Total Provident Fund</label>
                                    <input type="number" min="0" step="none" name="total_provident_fund" id="total_provident_fund" class="input-css" value="{{(old('total_provident_fund')) ? old('total_provident_fund') : ''}}">
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
                                <label>Gross Salary <sup>*</sup></label>
                                <input type="number" min="0" step="none" name="gross_salary" id="gross_salary" class="net input-css" value="{{(old('gross_salary')) ? old('gross_salary') : ''}}">
                                {!! $errors->first('gross_salary', '<p class="help-block">:message</p>') !!}
                            </div>  
                        </div>
                         <div class="row margin-bottom">
                            <div class="col-md-12">
                                <label>Net Salary <sup>*</sup></label>
                                <input type="number" min="0" step="none" name="net_salary" id="net_salary" class="input-css" value="{{(old('net_salary')) ? old('net_salary') : ''}}">
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