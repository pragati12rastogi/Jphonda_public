@extends($layout)

@section('title', __('Salary Advance Page'))

@section('js')
<script src="/js/pages/hr.js"></script>
<script>
    $('#dob').datepicker({
        format: "yyyy-mm-dd"
    });
    $('#doj').datepicker({
        format: "yyyy-mm-dd"
    });

 $(document).ready(function() {
     var check='If any other, Please specify';
     var selected = $("#purpose_loan").children("option:selected").val();
  
       if(selected==check){
        $("#any_other_loans").css("display", "block");
           
       }else{
        $("#any_other_loans").css("display", "none");
       }

 });
  

    $('#purpose_loan').on('change' , function (){
       var ass = $('#purpose_loan').val();
       var check='If any other, Please specify';
       if(ass==check){
        $("#any_other_loans").css("display", "block");
           
       }else{
        $("#any_other_loans").css("display", "none");
       }
       
});


</script>
@endsection

@section('breadcrumb')
    <li><a href="#"><i class=""></i> {{__('Salary Advance Page')}} </a></li>
     <style>
        .nav1>li>a {
            position: relative;
            display: block;
            padding: 10px 34px;
            background-color: white;
            margin-left: 10px;
        }
        /* .nav1>li>a:hover {
            background-color:#87CEFA;
        
        } */
        </style>
@endsection
@section('main_section')
    <section class="content"> 
         <div id="app">
                    @include('admin.flash-message')
                    @yield('content')
            </div>

               <form id="my-form" action="/admin/hr/user/salaryadvance" method="POST">
                @csrf
                
            <!-- general form elements -->
            <div class="box box-primary" style="padding-bottom: 25px;">
                <div class="box-header with-border">
<!--         <h3 class="box-title">{{__('customer.mytitle')}} </h3>-->
                </div>  
                <div class="row">
                        <div class="col-md-6">
                            <label>Employee Name<sup>*</sup></label>
                           <select class="input-css employee select2" id="employee" style="padding-top:2px" name="employee" >
                        <option value="">--Select Employee --</option>
                            @foreach($users as $item)
                            <option value="{{$item->id}}" {{(old('employee') == $item->id)? 'selected': ''}}>{{$item->name}} {{ $item->middle_name}} {{$item->last_name}}</option>
                            @endforeach
                    </select>
                            {!! $errors->first('employee', '<p class="help-block">:message</p>') !!}
                            <br>
                        </div>
                </div>
                <div class="row margin">
                        <label >Loan / Advance  Details ( Kindly provide the relevant details)</label>
                        <div class="col-md-12">
                            <div class="col-md-4">
                                <label>Types of loan/advance requested<sup>*</sup></label>
                                <input type="text" name="advance_equested" id="advance_equested" value="{{old('advance_equested')}}" class="input-css" placeholder="Enter loan/advance requested">
                                {!! $errors->first('advance_equested', '<p class="help-block">:message</p>') !!}
                            </div>
                            <div class="col-md-2">
                                <label>Amount applied for<sup>*</sup></label>
                                <input type="number" id="amount_applied" name="amount_applied" class="input-css" value="{{old('amount_applied')}}" placeholder="Enter Amount applied" min="0">
                                {!! $errors->first('amount_applied', '<p class="help-block">:message</p>') !!}
                            </div>
                             <div class="col-md-4">
                                <label>No. of installments( for payment)<sup>*</sup></label>
                                <input type="number" id="no_installments" name="no_installments" class="input-css" value="{{old('no_installments')}}" placeholder="Enter No. of installments" min="1">
                                {!! $errors->first('no_installments', '<p class="help-block">:message</p>') !!}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label>Purpose of Personal Loan<sup>*</sup></label>
                           <select class="input-css purpose_loan select2" id="purpose_loan" style="padding-top:2px" name="purpose_loan" >
                        <option value="">--Select Purpose of Personal Loan --</option>
                        <option value="Education" {{(old('purpose_loan') == 'Education')? 'selected': ''}}>Education</option>
                        <option value="Children’s education" {{(old('purpose_loan') == 'Children’s education')? 'selected': ''}}>Children’s education</option>
                        <option value="Holidays/Travel" {{(old('purpose_loan') == 'Holidays/Travel')? 'selected': ''}}>Holidays/Travel</option>
                        <option value="Medical expenses" {{(old('purpose_loan') == 'Medical expenses')? 'selected': ''}}>Medical expenses</option>
                        <option value="Investments" {{(old('purpose_loan') == 'Investments')? 'selected': ''}}>Investments</option>
                        <option value="Consumer durable purchases" {{(old('purpose_loan') == 'Consumer durable purchases')? 'selected': ''}}>Consumer durable purchases</option>
                        <option value="Marriage in family" {{(old('purpose_loan') == 'Marriage in family')? 'selected': ''}}>Marriage in family</option>
                        <option value="Home improvement/Renovation of home or office" {{(old('purpose_loan') == 'Home improvement/Renovation of home or office')? 'selected': ''}}></option>
                        <option value="Loan transfer" {{(old('purpose_loan') == 'Loan transfer')? 'selected': ''}}>Loan transfer</option>
                        <option value="Purchase of equipment" {{(old('purpose_loan') == 'Purchase of equipment')? 'selected': ''}}>Purchase of equipment</option>
                        <option value="If any other, Please specify" {{(old('purpose_loan') == 'If any other, Please specify')? 'selected': ''}}>If any other, Please specify</option>
                           
                    </select>
                            {!! $errors->first('purpose_loan', '<p class="help-block">:message</p>') !!}
                            <br>
                        </div>
                </div>
                <div class="row">
                        <div class="col-md-10">
                            <textarea class="input-css" id="any_other_loans" style="display: none" name="any_other_loan" rows="3">{{old('any_other_loan')}}</textarea>
                            {!! $errors->first('any_other_loan', '<p class="help-block">:message</p>') !!}
                        </div>
                </div><br/>
                <div class="row">
                        <div class="col-md-10">
                            <label>List  of documents attached with the application<sup>*</sup></label>
                            <textarea class="input-css" id="documents_attached" name="documents_attached" rows="3">{{old('documents_attached')}}</textarea>
                            {!! $errors->first('documents_attached', '<p class="help-block">:message</p>') !!}
                        </div>
                </div><br/>
                <div class="submit-button">
                <button type="submit" class="btn btn-primary">Submit</button>
                    
                </div>
                 </form>  
                 
            </div>
        
      </section>
@endsection