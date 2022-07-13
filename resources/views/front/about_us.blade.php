@extends($layout)

@section('title', __('JP Honda'))

@section('css')


@endsection

@section('main_section')
<div class="about-us-area overview-area pt-10">
    <div class="breadcrumb-area breadcrumb-area pt-10">
                <div class="container-fluid">
                    <div class="breadcrumb-content text-center contactus_header">
                    <h2>{{__('front/front.about_us')}}</h2>
                 </div>
                </div>
            </div>
    <div class="container">
        <div class="row">
            <div class="col-lg-12 col-md-12">
                 <div class="blog-hm-content">
                    <p>JP Motor Pvt. Ltd {{__('front/about_us.about_us_content_first')}}</p>
                </div>
                <div class="blog-hm-content">
                    <p>{{__('front/about_us.about_us_content_second')}} </p>
                </div>

            </div>
           
        </div>
    </div>
</div>
@endsection