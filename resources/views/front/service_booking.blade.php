@extends($layout)

@section('title', __('JP Honda'))
@section('css')
<link rel="stylesheet" href="/front/css/service.css">

@endsection
@section('js')
<script>
  var pick_drop = @php echo json_encode((old('pick_drop')) ? old('pick_drop') : '' ); @endphp;
  if (pick_drop == 'yes') {
     $("#pick_drop").css("display","block");
  }else{
       $("#pick_drop").css("display","none"); 
    }
  $(document).on('change','.pick_drop',function(){
    var pickupdrop = $('input[name="pick_drop"]:checked').val();
    if (pickupdrop== 'yes') {
     $("#pick_drop").css("display","block");
    }else{
       $("#pick_drop").css("display","none"); 
    }
  });

</script>
@endsection

@section('main_section')
<div class="about-us-area overview-area pt-60">
  <div class="breadcrumb-area breadcrumb-area" >
                <div class="container-fluid">
                    <div class="breadcrumb-content text-center contactus_header">
                    <h2>{{__('front/service.title')}}</h2>
                 </div>
                </div>
            </div>
    <div class="container">
            <div class="col-lg-12 col-md-12">
                    <div id="app">
                        @include('admin.flash-message')
                        @yield('content')
                    </div>
                    <div class="col-lg-8 col-md-12 col-12">
                        <form action="/service/prebooking" method="POST">
                            @csrf
                            <div class="checkbox-form">  
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="checkout-form-list">
                                            <label>{{__('front/service.name')}} <span class="required">*</span></label>          
                                            <input type="text" name="name" value="{{old('name')}}">
                                            {!! $errors->first('name', '<p class="help-block">:message</p>') !!}
                                        </div>
                                    </div>
                                    <div class="col-md-6 ">
                                        <div class="checkout-form-list">
                                            <label>{{__('front/service.mobile')}} <span class="required">*</span></label>
                                            <input type="text" name="mobile" value="{{old('mobile')}}">
                                            {!! $errors->first('mobile', '<p class="help-block">:message</p>') !!}
                                        </div>
                                    </div>
                                    <div class="col-md-12 pt-30">
                                        <div class="contact-form-style">
                                        <label >Pick and Drop  <span class="required">*</span></label>
                                        <div class="col-md-6">
                                          <label><input type="radio" name="pick_drop" value="yes" class="pick_drop sceme_button" {{old('pick_drop')== 'yes'? 'checked' : ''}} >  Yes</label>
                                      
                                          <label><input type="radio" {{old('pick_drop')== 'no'? 'checked' : ''}} name="pick_drop" value="no" class="pick_drop sceme_button"> No</label>
                                          
                                        </div>
                                        {!! $errors->first('pick_drop', '<p class="help-block">:message</p>') !!}
                                      </div>
                                    </div>

                                    <div class="col-md-12 " id="pick_drop" style="display:none">
                                        <div class="col-md-12">
                                            <div class="checkout-form-list create-acc"> 
                                                <input checked name="pickup_drop[]" value="pickup" type="checkbox">
                                                <label>PickUp</label>
                                            </div>
                                            <div class="checkout-form-list create-acc"> 
                                                <input  checked name="pickup_drop[]" value="drop" type="checkbox">
                                                <label>Drop</label>
                                            </div>
                                            {!! $errors->first('pickup_drop.*', '<p class="help-block">:message</p>') !!}
                                        </div>
                                   </div>

                                   <div class="col-md-6">
                                       <button class="submit cr-btn btn-style" type="submit"><span>{{__('front/service.submit')}}</span></button>
                                   </div>
                                    </div>
                                </div>                                                  
                            </div>
                        </form>
                    </div>
        </div>
    </div>
</div>
@endsection