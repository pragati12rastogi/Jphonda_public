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
      <div class="breadcrumb-area pt-120 pb-170">
            <div class="container-fluid">
              <div class="breadcrumb-content text-center contactus_header">
                    <h2>{{__('front/contact_us.contactus')}}</h2>
                </div>
            </div>
                <div class="overview-area contactus_outer pt-10">
                     <div class="container">
                           <div class="row">
                             <div class="col-lg-6">
                                <div class="contact-info-wrapper">
                                  <h4 class="contact-title">{{__('front/contact_us.information')}}</h4>
                                 <div class="communication-info">
                                    <div class="single-communication">
                                        <div class="communication-icon">
                                            <i class="ti-home" aria-hidden="true"></i>
                                        </div>
                                        <div class="communication-text">
                                            <h4>{{__('front/front.address')}}:</h4>
                                            <p>HP Petrol Pump, Janki Prasad Agarwal & Sons, Near Daliganj Crossing</p>
                                        </div>
                                    </div>
                                    <div class="single-communication">
                                        <div class="communication-icon">
                                            <i class="ti-mobile" aria-hidden="true"></i>
                                        </div>
                                        <div class="communication-text">
                                            <h4>{{__('front/front.phone')}}:</h4>
                                            <p><i class="icofont icofont-phone"></i><a href="tel:7317000421">7317000421</a>-<a href="tel:7317000424">424</a></p>
                                        </div>
                                    </div>
                                    <div class="single-communication">
                                        <div class="communication-icon">
                                            <i class="ti-email" aria-hidden="true"></i>
                                        </div>
                                        <div class="communication-text">
                                            <h4>{{__('front/contact_us.email')}}:</h4>
                                            <p><a href="mailto:gm.jphonda@gmail.com">gm.jphonda@gmail.com</a></p>
                                        </div>
                                    </div>
                                    <div class="single-communication">
                                        <div class="communication-icon">
                                            <i class="ti-world" aria-hidden="true"></i>
                                        </div>
                                        <div class="communication-text">
                                            <h4>{{__('front/contact_us.website')}}:</h4>
                                            <p><a href="https://www.jphonda.com">https://www.jphonda.com</a></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="contact-message-wrapper">
                                <h4 class="contact-title">{{__('front/contact_us.get_in_touch')}}</h4>
                                 <div id="app">
                                  @include('admin.flash-message')
                                  @yield('content')
                                </div>

                                <div class="contact-message">
                                   <form action="/contactus" method="POST" id="form">
                                    @csrf
                                        <div class="row">
                                            <div class="col-lg-6">
                                                <div class="contact-form-style mb-20">
                                                     <input type="text"  placeholder="Name" name="name" value="{{old('name')}}"> 
                                                    {!! $errors->first('name', '<p class="help-block">:message</p>') !!}
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="contact-form-style mb-20">
                                                    <input type="text"  placeholder="Email Address" name="email"  value="{{old('email')}}">
                                                     {!! $errors->first('email', '<p class="help-block">:message</p>') !!} 

                                                </div>
                                            </div>
                                            <div class="col-lg-12">
                                                <div class="contact-form-style mb-20">
                                                  <input type="text" name="mobile" placeholder="Mobile" class="mobile"   value="{{old('mobile')}}">
                                                   {!! $errors->first('mobile', '<p class="help-block">:message</p>') !!}
                                                </div>
                                            </div>
                                            <div class="col-lg-12">
                                                <div class="contact-form-style">
                                                     <textarea type="text" placeholder="Massage" name="message" id="" class="message" maxlength="500">{{old('message')}}</textarea>
                                                     {!! $errors->first('message', '<p class="help-block">:message</p>') !!} 

                                                    <button class="submit cr-btn btn-style" type="submit"><span>{{__('front/contact_us.send_message')}}</span></button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                    <p class="form-messege"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
             </div>
            </div>
@endsection