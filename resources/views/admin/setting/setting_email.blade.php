@extends($layout)

@section('title', __('Email Setting'))

@section('user', Auth::user()->name)

@section('breadcrumb')
<li><a href="#"><i class=""></i> Email Setting</a></li>
@endsection
@section('css')
<link rel="stylesheet" href="/css/consignee.css">
@endsection

@section('js')

 <script>

$(document).ready(function() {
$.validator.addMethod("notValidIfSelectFirst", function(value, element, arg) {
        return arg !== value;
    }, "This field is required.");

    $('#form').validate({ // initialize the plugin
        rules: {

            sale_cancel_email: {
                required: true
            },
        }
    });
});
</script>
@endsection
@section('main_section')
<section class="content">
    <div id="app">
        @include('admin.flash-message')
        @yield('content')
    </div>
    <!-- Default box -->
    <form id="form" action='/admin/setting/email' method='post'>
        @csrf
      
    <div class="box-header with-border">
        <div class='box box-default'>  <br>
            <h2 class="box-title" style="margin-left:20px">Email setting</h2><br><br><br>
      
         <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="">{{__('settings_form.sale_cancel_email')}} <sup>*</sup></label>
                    <input type="text" class="form-control input-css" name="sale_cancel_email" value=" {{$settings['sale_cancel_email']}}" id="sale_cancel_email" placeholder="Sale Cancel Email">
                </div>
            </div>
             <div class="col-md-6">
                <div class="form-group">
                    <label for="">{{__('settings_form.refund_email')}} <sup>*</sup></label>
                    <input type="text" class="form-control input-css" name="refund_email" value=" {{$settings['refund_email']}}" id="refund_email" placeholder="Refund Email">
                </div>
            </div>
        </div><br/>
         <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="">{{__('settings_form.breakin_mail')}} <sup>*</sup></label>
                    <input type="text" class="form-control input-css" name="breakin_mail" value=" {{$settings['breakin_mail']}}" id="breakin_mail" placeholder="Breakin Email">
                </div>
            </div>
             <div class="col-md-6">
                <div class="form-group">
                    <label for="">{{__('settings_form.admin_mail')}} <sup>*</sup></label>
                    <input type="text" class="form-control input-css" name="admin_email" value=" {{$settings['admin_email']}}" id="admin_email" placeholder="Admin Email">
                </div>
            </div>
        </div><br/>
          </div>
    </div>
    <div class="form-group">
        <input type="submit" name="sub" class="btn btn-primary pull-right" value="Submit">
    </div>
</form>
   
</section>
@endsection