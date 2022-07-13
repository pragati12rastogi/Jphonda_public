@extends($layout)

@section('title', __('JP Honda'))

@section('js')
@endsection
@section('css')
        <!--inputs css-->
<link rel="stylesheet" href="/css/style.css">
<link rel="stylesheet" href="/front/css/service.css">


@endsection
@section('main_section')
    <section class="content">
      <div class="breadcrumb-area breadcrumb-area pt-120" >
                <div class="container-fluid">
                    <div class="breadcrumb-content text-center contactus_header">
                    <h2>{{__('front/insurancequotation.insurance_quotation')}}</h2>
                 </div>
                </div>
            </div>
        <div class="overview-area pt-10">
         <div class="container">
           <div class="row">
            <div class="col-lg-12 col-md-12">
        <!-- Default box -->
        <div id="app">
                @include('admin.flash-message')
                @yield('content')
            </div>
       <form action="/insurancequotation" method="POST" id="form">
        @csrf

       <div class="box box-header">
            <div class="row" >
                 <div class="col-md-6">
                    <div class="checkout-form-list">
                        <label for="">{{__('front/insurancequotation.mobile_no')}} <sup>*</sup></label>
                        <input type="text" name="mobile" id=""   value="{{old('mobile')}}"class="mobile input-css">
                        {!! $errors->first('mobile', '<p class="help-block">:message</p>') !!} 
                    </div>
                </div>
            
               
            </div>

            <div class="row pt-20">
                <div class="col-md-6">
                    <div class="checkout-form-list">
                        <label for="">{{__('front/insurancequotation.frame_number')}}</label>     
                        <input type="text" name="frame_number" value="{{old('frame_number')}}" >
                        {!! $errors->first('frame_number', '<p class="help-block">:message</p>') !!} 
                    </div>
                </div> 
            </div>
       </div>
       
        <div class="row">
            <div class="col-md-3">
            <div class="contact-form-style">            
              <button class="submit cr-btn btn-style" type="submit"><span>{{__('front/insurancequotation.submit')}}</span></button>
            </div>
        </div>

            </div>
        </form>
      </div></div></div></div>
      </section>
@endsection