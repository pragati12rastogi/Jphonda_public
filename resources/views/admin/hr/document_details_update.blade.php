@extends($layout)

@section('title', __('Bank/PF Details Page'))

@section('js')
<script src="/js/pages/hr.js"></script>
<script>
    $('#dob').datepicker({
        format: "yyyy-mm-dd"
    });
    $('#doj').datepicker({
        format: "yyyy-mm-dd"
    });

</script>
@endsection

@section('breadcrumb')
    <li><a href="#"><i class=""></i> {{__('Bank/PF Details Page')}} </a></li>
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
               <form id="my-form" action="/admin/hr/user/documentupdate/{{$customer->id}}" method="POST">
                @csrf
                  @include('layouts.user_tab')
              <br>
              
            <!-- general form elements -->
            <div class="box box-primary" style="padding-bottom: 25px;">
                <div class="box-header with-border">
<!--         <h3 class="box-title">{{__('customer.mytitle')}} </h3>-->
                </div>  
                <div class="row">
                    <input type="hidden" name="user_details_id" class="input-css" value="{{isset($customer)==1?($customer->user_details_id):''}}">
                        <div class="col-md-6">
                            <label>Bank Name<sup>*</sup></label>
                            <input type="text" name="bank_name" class="input-css"
                            value="{{isset($customer)==1?($customer->bank_name):''}}">
                            {!! $errors->first('bank_name', '<p class="help-block">:message</p>') !!}
                            <br>
                        </div>
                        <div class="col-md-6">
                            <label>Account Number<sup>*</sup></label>
                            <input type="text" name="account_number" class="input-css" value="{{isset($customer)==1?($customer->account_number):''}}">
                            {!! $errors->first('account_number', '<p class="help-block">:message</p>') !!}

                        </div>
                </div>
                <div class="row">
                        <div class="col-md-6">
                            <label>IFSC Code <sup>*</sup></label>
                            <input type="text" name="ifsc" class="input-css" value="{{isset($customer)==1?($customer->ifsc):''}}">
                            {!! $errors->first('ifsc', '<p class="help-block">:message</p>') !!}
                            <br>
                        </div>
                        <div class="col-md-6">
                            <label>PF</label>
                            <input type="text" name="pf" class="input-css" value="{{isset($customer)==1?($customer->pf):''}}">
                            {!! $errors->first('pf', '<p class="help-block">:message</p>') !!}
                            <br>
                        </div>
                        
                </div>
                 <div class="row">
                        <div class="col-md-6">
                            <label>ESI</label>
                            <input type="text" name="esi" class="input-css" value="{{isset($customer)==1?($customer->esi):''}}">
                            {!! $errors->first('esi', '<p class="help-block">:message</p>') !!}
                            <br>
                        </div> 
                </div>
                <div class="submit-button">
                <button type="submit" class="btn btn-primary">Submit</button>
                    
                </div>
                 </form>                
            </div>
        
      </section>
@endsection