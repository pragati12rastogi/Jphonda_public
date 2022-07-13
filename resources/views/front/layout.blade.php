<!doctype html>
<html class="no-js" lang="zxx">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="x-ua-compatible" content="ie=edge">
        <title>@yield('title')</title>
        <meta name="description" content="Live Preview Of Oswan eCommerce HTML5 Template">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- Favicon -->
        <link rel="shortcut icon" type="image/x-icon" href="/front/img/favicon.png">
        
        <!-- all css here -->
        <link rel="stylesheet" href="/front/css/bootstrap.min.css">
        <link rel="stylesheet" href="/front/css/animate.css">
        <link rel="stylesheet" href="/front/css/owl.carousel.min.css">
        <link rel="stylesheet" href="/front/css/chosen.min.css">
        <link rel="stylesheet" href="/front/css/icofont.css">
        <link rel="stylesheet" href="/front/css/themify-icons.css">
        <link rel="stylesheet" href="/front/css/font-awesome.min.css">
        <link rel="stylesheet" href="/front/css/meanmenu.min.css">
        <link rel="stylesheet" href="/front/css/bundle.css">
        <link rel="stylesheet" href="/front/css/style.css">
        <link rel="stylesheet" href="/front/css/responsive.css">
        <link rel="stylesheet" href="/front/css/jquery-ui.css">
       
        
        <script src="/front/js/vendor/modernizr-2.8.3.min.js"></script>
        <link rel="stylesheet" href="/front/css/custom.css">
        @yield('css')
        
    </head>
    <body>
        <div class="wrapper">
            <!-- header start -->
            <header>
                <div class="header-area transparent-bar header-div ">
                    <div class="container">
                        <div class="row">
                            <div class="col-lg-2 col-md-2 col-2">
                               <i class="fa fa-language"></i>
                                @foreach (config('app.available_locales') as $locale)
                                    <i class="">
                                        <a class=""
                                        href="{{ route($locale) }}"
                                            @if (app()->getLocale() == $locale) style="font-weight: bold; text-decoration: underline" @endif>{{ strtoupper($locale) }}</a>
                                    </i>
                                @endforeach
                            </div>
                            <div class="col-lg-2 col-md-2 col-2">
                                <div class="mainlogo logo-small-device">
                                    <a href="{{ url('') }}"><img alt="" src="/front/img/logo/logo.png"></a>
                                </div>
                            </div>
                            <div class="col-lg-8 col-md-8 col-8">
                                <div class="header-contact-menu-wrapper pl-45">
                                    <div class="header-contact">
                                        <p>{{__('front/front.nav_contact')}}<i class="icofont icofont-phone"></i><a href="tel:7317000402">7317000402</a> </p>
                                    </div>
                                    <div class="menu-wrapper text-center">
                                        <button class="menu-toggle">
                                            <img class="s-open" alt="" src="/front/img/icon-img/menu.png">
                                            <img class="s-close" alt="" src="/front/img/icon-img/menu-close.png">
                                        </button>
                                        <div class="main-menu">
                                            <nav>
                                                <ul>
                                                    <li><a href="/home">{{__('front/front.home')}}</a></li>
                                                    <li><a href="#">{{__('front/front.shop')}}</a>
                                                        <ul>
                                                            <li><a href="/bestdeal">{{__('front/front.bestdeal')}}</a></li>
                                                            <li><a href="/product">{{__('front/front.product')}}</a></li>
                                                        </ul>
                                                    </li>
                                                    <li><a href="#">{{__('front/front.pages')}}</a>
                                                        <ul>
                                                            <li><a href="/aboutus">{{__('front/front.about_us')}}</a></li>
                                                            <li><a href="/privacypolicy">{{__('front/front.privacypolicy')}}</a></li>
                                                            <li><a href="/career">{{__('front/front.career')}}</a></li>
                                                            <li><a href="/contactus">{{__('front/front.contactus')}}</a></li>
                                                            <li><a href="/insurancequotation">{{__('front/front.insurancequotation')}}</a></li>
                                                            <li><a href="/store">{{__('front/front.all_store')}}</a></li>
                                                            <li><a href="/testimonial">{{__('front/front.customer_testimonial')}}</a></li>
                                                            <li><a href="/vehicle/digital/quotation">{{__('front/front.vehicle_digital_quotation')}}</a></li>
                                                            <li><a href="/check/rc/plate">{{__('front/front.check_rc_plate_number')}}</a></li>
                                                            <li><a href="/service/prebooking">{{__('front/service.title')}}</a></li>
                                                            <li><a href="/service/status">{{__('front/service.title_status')}}</a></li>
                                                            <li><a href="/hsrp/request">{{__('front/front.hsrp')}}</a></li>
                                                            <li><a href="/pay/challan/request">{{__('front/challan.pay_challan_request')}}</a></li>
                                                        </ul>
                                                    </li>
                                                    <li><a href="#">{{__('front/front.customer_services')}}</a>
                                                        <ul>
                                                            <li><a  id="serviceDueDate" href="#" data-toggle="modal" data-target="#serviceDueDateModal">{{__('front/front.service_due_date')}}</a></li>
                                                            <li><a id="insuranceDueDate" href="#" data-toggle="modal" data-target="#insuranceDueDateModal">{{__('front/front.insurance_due_date')}}</a></li>
                                                            <li><a id="insuranceBooking" href="#" data-toggle="modal" data-target="#insuranceBookingModal">{{__('front/front.insurance_booking')}}</a></li>
                                                        </ul>
                                                    </li>
                                                   <li><a href="/contactus">{{__('front/front.contactus')}}</a></li>
                                                </ul>
                                            </nav>
                                        </div>
                                    </div>
                                </div>
                                <div class="header-cart cart-small-device">
                                    <button class="icon-cart">
                                        <i class="ti-shopping-cart"></i>
                                        <span class="count-style">0</span>
                                        <span class="count-price-add">$0</span>
                                    </button>
                                    <div class="shopping-cart-content">
                                        <ul>
                                            <li class="single-shopping-cart">
                                                <div class="shopping-cart-img">
                                                    <a href="#"><img alt="" src="/front/img/cart/cart-1.jpg"></a>
                                                </div>
                                                <div class="shopping-cart-title">
                                                    <h3><a href="#">Gloriori GSX 250 R </a></h3>
                                                    <span>Price: $275</span>
                                                    <span>Qty: 01</span>
                                                </div>
                                                <div class="shopping-cart-delete">
                                                    <a href="#"><i class="icofont icofont-ui-delete"></i></a>
                                                </div>
                                            </li>
                                            <li class="single-shopping-cart">
                                                <div class="shopping-cart-img">
                                                    <a href="#"><img alt="" src="/front/img/cart/cart-2.jpg"></a>
                                                </div>
                                                <div class="shopping-cart-title">
                                                    <h3><a href="#">Demonissi Gori</a></h3>
                                                    <span>Price: $275</span>
                                                    <span class="qty">Qty: 01</span>
                                                </div>
                                                <div class="shopping-cart-delete">
                                                    <a href="#"><i class="icofont icofont-ui-delete"></i></a>
                                                </div>
                                            </li>
                                            <li class="single-shopping-cart">
                                                <div class="shopping-cart-img">
                                                    <a href="#"><img alt="" src="/front/img/cart/cart-3.jpg"></a>
                                                </div>
                                                <div class="shopping-cart-title">
                                                    <h3><a href="#">Demonissi Gori</a></h3>
                                                    <span>Price: $275</span>
                                                    <span class="qty">Qty: 01</span>
                                                </div>
                                                <div class="shopping-cart-delete">
                                                    <a href="#"><i class="icofont icofont-ui-delete"></i></a>
                                                </div>
                                            </li>
                                        </ul>
                                        <div class="shopping-cart-total">
                                            <h4>total: <span>$550.00</span></h4>
                                        </div>
                                        <div class="shopping-cart-btn">
                                            <a class="btn-style cr-btn" href="#">checkout</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mobile-menu-area col-12">
                                <div class="mobile-menu">
                                    <nav id="mobile-menu-active">
                                        <ul class="menu-overflow">
                                            <li><a href="/home">{{__('front/front.home')}}</a></li>
                                            <li><a href="#">{{__('front/front.pages')}}</a>
                                                <ul>
                                                    <li><a href="/aboutus">{{__('front/front.about_us')}}</a></li>
                                                    <li><a href="/privacypolicy">{{__('front/front.privacypolicy')}}</a></li>
                                                    <li><a href="/career">{{__('front/front.career')}}</a></li>
                                                   <!--  <li><a href="cart.html">cart page</a></li>
                                                    <li><a href="checkout.html">checkout</a></li>
                                                    <li><a href="wishlist.html">wishlist</a></li>
                                                    <li><a href="login-register.html">login</a></li> -->
                                                    <li><a href="/contactus">{{__('front/front.contactus')}}</a></li>
                                                   <li><a href="/insurancequotation">{{__('front/front.insurancequotation')}}</a></li>
                                                    <li><a href="/store">{{__('front/front.all_store')}}</a></li>
                                                    <li><a href="/testimonial">{{__('front/front.customer_testimonial')}}</a></li>
                                                    <li><a href="/vehicle/digital/quotation">{{__('front/front.vehicle_digital_quotation')}}</a></li>
                                                    <li><a href="/check/rc/plate">{{__('front/front.check_rc_plate_number')}}</a></li>
                                                    <li><a href="/service/prebooking">{{__('front/service.title')}}</a></li>
                                                    <li><a href="/service/status">{{__('front/service.title_status')}}</a></li>
                                                    <li><a href="/hsrp/request">{{__('front/front.hsrp')}}</a></li>
                                                     <li><a href="/pay/challan/request">{{__('front/challan.pay_challan_request')}}</a></li>
                                                    
                                                </ul>
                                            </li>
                                            <li><a href="#">{{__('front/front.shop')}}</a>
                                                <ul>
                                                    <li><a href="/bestdeal">{{__('front/front.bestdeal')}}</a></li>
                                                    <li><a href="/product">{{__('front/front.product')}}</a></li>
                                                </ul>
                                            </li>
                                            <li><a href="#">{{__('front/front.customer_services')}}</a>
                                                <ul>
                                                    <li><a  id="serviceDueDate" href="#" data-toggle="modal" data-target="#serviceDueDateModal">{{__('front/front.service_due_date')}}</a></li>
                                                    <li><a id="insuranceDueDate" href="#" data-toggle="modal" data-target="#insuranceDueDateModal">{{__('front/front.insurance_due_date')}}</a></li>
                                                    <li><a id="insuranceBooking" href="#" data-toggle="modal" data-target="#insuranceBookingModal">{{__('front/front.insurance_booking')}}</a></li>
                                                </ul>
                                            </li>
                                            <li><a href="/contactus">{{__('front/front.contactus')}}</a></li>
                                        </ul>
                                    </nav>                          
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            @section('main_section')
            @show
            <div class="newsletter-area">
                <div class="container">
                    <div class="newsletter-wrapper-all theme-bg-2">
                        <div class="row">
                            <div class="col-lg-5 col-12 col-md-12">
                                <div class="newsletter-img bg-img" style="background-image: url(/front/img/banner/newsletter-bg.png)">
                                    <img alt="image" src="/front/img/team/newsletter-img.png">
                                </div>
                            </div>
                            <div class="col-lg-7 col-12 col-md-12 newsletterdiv">
                                <div class="newsletter-wrapper text-center">
                                    <div class="newsletter-title">
                                        <h3>Subscribe our newsletter</h3>
                                    </div>
                                    <div id="mc_embed_signup" class="subscribe-form">

                                        <form action="#" id="subscribeForm" method="post" id="#" >
                                             @csrf
                                              
                                               <div class="col-xs-12">
                                            <div id="mc_embed_signup_scroll" class="mc-form">
                                                <input type="email" value="" name="subscribe_email" class="email" placeholder="Enter your email here..." id="subscribe_email" >
                                                <div class="mc-news" aria-hidden="true"><input type="text" name="b_6bbb9b6f5827bd842d9640c82_05d85f18ef" tabindex="-1" value=""></div>
                                                <div class="clear"><input type="submit" id="subscribe_button" value="Subscribe" name="subscribe" id="mc-embedded-subscribe" class="button"></div>
                                            </div>
                                        </div>
                                        </form>
                                        <div class="alert alert-danger" id="subscribe-error" style="margin-top:7px;display:none;">
                                                </div>
                                        <div class="alert alert-success" id="subscribe-success" style="margin-top:7px;display:none;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <footer>
                <div class="footer-top pt-110 pb-1 theme-bg">
                    <div class="container">
                       <div class="row">
                            <div class="col-lg-3 col-md-6 col-12">
                                <div class="footer-widget mb-30">
                                    <div class="footer-logo">
                                        <a href="/home">
                                            <img src="/front/img/logo/logo.png" class="footer_logo_img" alt="">
                                        </a>
                                    </div>
                                    <div class="footer-about">
                                        <p><span>JPHonda</span> {{__('front/front.most_latgest_bike_store')}} </p>
                                        <div class="footer-support">
                                            <h5>{{__('front/front.for_support')}}</h5>
                                            <span><i class="icofont icofont-phone"></i><a href="tel:7317000466">7317000466</a> </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6 col-12">
                                <div class="footer-widget mb-30 pl-60">
                                    <div class="footer-widget-title">
                                        <h3>{{__('front/front.quick_link')}}</h3>
                                    </div>
                                    <div class="quick-links">
                                        <ul>
                                            <li><a href="/aboutus">{{__('front/front.about_us')}}</a></li>
                                            <li><a href="/privacypolicy">{{__('front/front.privacypolicy')}}</a></li>
                                            <li><a href="/career">{{__('front/front.career')}}</a></li>
                                            <li><a href="/contactus">{{__('front/front.contactus')}}</a></li>
                                            <li><a href="/insurancequotation">{{__('front/front.insurancequotation')}}</a></li>
                                            <li><a href="/bestdeal">{{__('front/front.bestdeal')}}</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6 col-12">
                                <div class="footer-widget mb-30">
                                    <div class="footer-widget-title">
                                        <h3>{{__('front/front.latest_tweet')}}</h3>
                                    </div>
                                    <div class="food-widget-content pr-30">
                                        <div class="single-tweet">
                                            <p><a class="twitter-timeline"
  href="https://twitter.com/jphonda_lko"
  data-width="300"
  data-height="300">
  <script async src="http://platform.twitter.com/widgets.js" charset="utf-8"></script>
Tweets by @Jphonda_Lko
</a></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6 col-12">
                                <div class="footer-widget mb-30">
                                    <div class="footer-widget-title">
                                        <h3>{{__('front/front.contact_info')}}</h3>
                                    </div>
                                    <div class="food-info-wrapper">
                                        <div class="food-address">
                                            <div class="food-info-title">
                                                <span>{{__('front/front.address')}}</span>
                                            </div>
                                            <div class="food-info-content">
                                                <p>HP Petrol Pump, Janki Prasad Agarwal & Sons, Near Daliganj Crossing</p>
                                            </div>
                                        </div>
                                        <div class="food-address">
                                            <div class="food-info-title">
                                                <span>{{__('front/front.phone')}}</span>
                                            </div>
                                            <div class="food-info-content">
                                                <p><i class="icofont icofont-phone"></i><a href="tel:7317000466">7317000466</a></p>
                                              
                                            </div>
                                        </div>
                                        <div class="food-address">
                                            <div class="food-info-title">
                                                <span>{{__('front/front.web')}}</span>
                                            </div>
                                            <div class="food-info-content">
                                                <a href="https://www.jphonda.com">https://www.jphonda.com</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="footer-bottom ptb-15 black-bg">
                    <div class="container">
                        <div class="row">
                            <div class="col-md-9 col-12">
                                <div class="copyright">
                                    <p>©Copyright, {{ now()->year }} All Rights Reserved by Automan X</a></p>
                                </div>
                            </div>
                            <div class="col-md-3 col-12">
                                <div class="copyright footer-payment-method">
                                   <p> Design & Developed by <a href="https://www.dkinfosolution.com">DKInfosolution</a></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </footer>
            
            <div class="loading" id="loader" style="display:none;z-index:9999999"></div>

            <!-- modal -->
            <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-hidden="true">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span class="icofont icofont-close" aria-hidden="true"></span>
                </button>
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-body">
                            <div class="qwick-view-left">
                                <div class="quick-view-learg-img">
                                    <div class="quick-view-tab-content tab-content">
                                        <div class="tab-pane active show fade" id="modal1" role="tabpanel">
                                            <img src="/front/img/quick-view/l1.jpg" alt="">
                                        </div>
                                        <div class="tab-pane fade" id="modal2" role="tabpanel">
                                            <img src="/front/img/quick-view/l2.jpg" alt="">
                                        </div>
                                        <div class="tab-pane fade" id="modal3" role="tabpanel">
                                            <img src="/front/img/quick-view/l3.jpg" alt="">
                                        </div>
                                    </div>
                                </div>
                                <div class="quick-view-list nav" role="tablist">
                                    <a class="active" href="#modal1" data-toggle="tab" role="tab">
                                        <img src="/front/img/quick-view/s1.jpg" alt="">
                                    </a>
                                    <a href="#modal2" data-toggle="tab" role="tab">
                                        <img src="/front/img/quick-view/s2.jpg" alt="">
                                    </a>
                                    <a href="#modal3" data-toggle="tab" role="tab">
                                        <img src="/front/img/quick-view/s3.jpg" alt="">
                                    </a>
                                </div>
                            </div>
                            <div class="qwick-view-right">
                                <div class="qwick-view-content">
                                    <h3>Aerion Carbon Helmet</h3>
                                    <div class="price">
                                        <span class="new">$90.00</span>
                                        <span class="old">$120.00  </span>
                                    </div>
                                    <div class="rating-number">
                                        <div class="quick-view-rating">
                                            <i class="fa fa-star reting-color"></i>
                                            <i class="fa fa-star reting-color"></i>
                                            <i class="fa fa-star reting-color"></i>
                                            <i class="fa fa-star reting-color"></i>
                                            <i class="fa fa-star reting-color"></i>
                                        </div>
                                    </div>
                                    <p>Lorem ipsum dolor sit amet, consectetur adip elit, sed do tempor incididun ut labore et dolore magna aliqua. Ut enim ad mi , quis nostrud veniam exercitation .</p>
                                    <div class="quick-view-select">
                                        <div class="select-option-part">
                                            <label>Size*</label>
                                            <select class="select">
                                                <option value="">- Please Select -</option>
                                                <option value="">900</option>
                                                <option value="">700</option>
                                            </select>
                                        </div>
                                        <div class="select-option-part">
                                            <label>Color*</label>
                                            <select class="select">
                                                <option value="">- Please Select -</option>
                                                <option value="">orange</option>
                                                <option value="">pink</option>
                                                <option value="">yellow</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="quickview-plus-minus">
                                        <div class="cart-plus-minus">
                                            <input type="text" value="02" name="qtybutton" class="cart-plus-minus-box">
                                        </div>
                                        <div class="quickview-btn-cart">
                                            <a class="btn-style" href="#">add to cart</a>
                                        </div>
                                        <div class="quickview-btn-wishlist">
                                            <a class="btn-hover" href="#"><i class="icofont icofont-heart-alt"></i></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- service due date modal -->
            <div class="modal fade" id="serviceDueDateModal" tabindex="-1" role="dialog" aria-labelledby="serviceDueDateModalCenterTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="serviceDueDateModalCenterTitle">{{__('front/front.service_due_date')}}</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form method="POST" action="#" id="serviceDueDateForm">
                            @csrf
                            <div class="alert alert-danger" id="sdd-error" style="display:none;">
                            </div>
                            <div class="alert alert-success" id="sdd-success" style="display:none;">
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label for="sdd-mobile">{{__('front/front.mobile')}} <sup>*</sup></label>
                                        <input type="text" name="sdd-mobile" id="sdd-mobile">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="sdd-frame">{{__('front/front.frame')}} # <sup>*</sup></label>
                                        <input type="text" name="sdd-frame" id="sdd-frame">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="sdd-reg">{{__('front/front.registration')}} # <sup>*</sup></label>
                                        <input type="text" name="sdd-reg" id="sdd-reg">
                                    </div>
                                    <div class="col-md-12 pt-5"  id="sdd-msg" style="display:none">
                                        <label>Result :- </label>
                                        <label class="msg"></label>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{__('front/front.close')}}</button>
                                <button type="submit" id="btnUpdate" class="btn btn-success" >{{__('front/front.submit')}}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- insurance due date modal -->
            <div class="modal fade" id="insuranceDueDateModal" tabindex="-1" role="dialog" aria-labelledby="insuranceDueDateModalCenterTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="insuranceDueDateModalCenterTitle">{{__('front/front.insurance_due_date')}}</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form method="POST" action="#" id="insuranceDueDateForm">
                            @csrf
                            <div class="alert alert-danger" id="idd-error" style="display:none;">
                            </div>
                            <div class="alert alert-success" id="idd-success" style="display:none;">
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label for="idd-mobile">{{__('front/front.mobile')}} <sup>*</sup></label>
                                        <input type="text" name="idd-mobile" id="idd-mobile">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="idd-frame">{{__('front/front.frame')}} # <sup>*</sup></label>
                                        <input type="text" name="idd-frame" id="idd-frame">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="idd-reg">{{__('front/front.registration')}} # <sup>*</sup></label>
                                        <input type="text" name="idd-reg" id="idd-reg">
                                    </div>
                                    <div class="col-md-12 pt-5"  id="idd-msg" style="display:none">
                                        <label>Result :- </label>
                                        <label class="msg"></label>
                                    </div>
                                </div>
                                
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{__('front/front.close')}}</button>
                                <button type="submit"  class="btn btn-success" >{{__('front/front.submit')}}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Insurance Booking modal -->
            <div class="modal fade" id="insuranceBookingModal" tabindex="-1" role="dialog" aria-labelledby="insuranceBookingModalCenterTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="insuranceBookingModalCenterTitle">{{__('front/front.insurance_booking')}}</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form action="" method="POST" id="insuranceBookingForm">
                            <div class="alert alert-danger" id="ib-error" style="display:none;">
                            </div>
                            <div class="alert alert-success" id="ib-success" style="display:none;">
                            </div>
                            <div class="modal-body">
                                    @csrf
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="ib-name">{{__('front/front.name')}} <sup>*</sup></label>
                                            <input type="text" name="ib-name" id="ib-name">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="ib-mobile">{{__('front/front.mobile')}} <sup>*</sup></label>
                                            <input type="text" name="ib-mobile" id="ib-mobile">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="ib-frame">{{__('front/front.frame')}} # <sup>*</sup></label>
                                            <input type="text" name="ib-frame" id="ib-frame">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="ib-reg">{{__('front/front.registration')}} # <sup>*</sup></label>
                                            <input type="text" name="ib-reg" id="ib-reg">
                                        </div>
                                    </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{__('front/front.close')}}</button>
                                <button type="submit"  class="btn btn-success" >{{__('front/front.submit')}}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        
        <!-- all js here -->
        <script src="/front/js/vendor/jquery-1.12.0.min.js"></script>
        <script src="/front/js/popper.js"></script>
        <script src="/front/js/bootstrap.min.js"></script>
        <script src="/front/js/isotope.pkgd.min.js"></script>
        <script src="/front/js/imagesloaded.pkgd.min.js"></script>
        <script src="/front/js/jquery.counterup.min.js"></script>
        <script src="/front/js/waypoints.min.js"></script>
        
        <script src="/front/js/owl.carousel.min.js"></script>
        <script src="/front/js/plugins.js"></script>
        <script src="/front/js/main.js"></script>
       

        @yield('js')
        <script src="/js/pages/front/customer_service.js"></script>
        
        <script type="text/javascript">

    $(document).ready(function() {
       var pre=  ' {{__("front/front.pre")}} ';
       var next=  ' {{__("front/front.next")}} ';
       
        $( ".owl-prev").html(pre);
        $( ".owl-next").html(next);

    });

$('#subscribeForm').submit(function(e) {
    e.preventDefault();

    $("#loader").show();
    var formData = new FormData(this);
    $('#subscribeForm').next('span').remove();
    $('#subscribe-error').hide();
    $('#subscribe-success').hide();

    $.ajax({
        type: 'POST',
        url: "/web/subscribeEmail",
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        success: (data) => {
            if (data.length > 0) {
                 $('#subscribe-error').hide();
                 $('#subscribe-success').text(data[1]).show();
                $('#subscribe_email').val('');
            }
        },
        error: function(error) {
            if ((error.responseJSON).hasOwnProperty('message')) {
                if (error.responseJSON.errors) {
                    draw_errors(error.responseJSON.errors, 'subscribeForm');
                } else if (error.responseJSON) {
                    draw_errors(error.responseJSON, 'subscribeForm');
                }
            } else {
                $('#subscribe-error').html(error.responseJSON).show();
            }
            $('#subscribe-success').hide();
            $("#loader").hide();
        }
    }).done(function() {
        $("#loader").hide();
    });

});

function draw_errors(arr, form_id) {
    $('#subscribeForm').next('span').remove();
    $.each(arr, function(key, val) {
         $("<span style='color:red;'>" + val[0] + "</span>").insertAfter('#subscribeForm');
    });
}
        </script>
    <script type="text/javascript">
        (function() {
    if (!localStorage.getItem('cookieconsent')) {
        document.body.innerHTML += '\
        <div class="cookieconsent" style="position:fixed;padding:30px;left:0;bottom:0;background-color:#000;color:#FFF;text-align:center;width:100%;z-index:99999;font-size:20px">\
            This site uses cookies. By continuing to use this website, you agree to their use. \
            <button class="agree" style="color:Green;border;border: none;">I Agree</button>\
        </div>\
        ';
        document.querySelector('.cookieconsent .agree').onclick = function(e) {
            
            e.preventDefault();
            document.querySelector('.cookieconsent').style.display = 'none';
            localStorage.setItem('cookieconsent', true);
        };
    }
})();
    </script>
    </body>
</html>
