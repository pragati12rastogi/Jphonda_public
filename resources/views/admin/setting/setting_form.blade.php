@extends($layout)

@section('title', __('Settings'))

@section('user', Auth::user()->name)

@section('breadcrumb')
<li><a href="#"><i class=""></i> Settings</a></li>
@endsection
@section('css')
<link rel="stylesheet" href="/css/consignee.css">
@endsection



@section('main_section')
<section class="content">
    <div id="app">
        @include('admin.flash-message')
        @yield('content')
    </div>
    <!-- Default box -->
    <form id="form" action='/admin/setting/addform' method='post'>
        @csrf
      
    <div class="box-header with-border">
        <div class='box box-default'>  <br>
            <h2 class="box-title" style="margin-left:20px">Email setting</h2><br><br><br>
      
         <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="">{{__('settings_form.ip')}} <sup>*</sup></label>
                    <input type="text" class="form-control input-css" name="login_allowed_ip" value=" {{$settings['login_allowed_ip']}}" id="login_allowed_ip" placeholder="Login allowed ip"><br>
                     <sup class="text-muted">Add ip address seperated by comma.</sup>
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
                    <label for="">{{__('settings_form.garage_charge')}} <sup>*</sup></label>
                    <input type="text" class="form-control input-css" name="garage_charge" value=" {{$settings['garage_charge']}}" id="garage_charge" placeholder="Garage charge">
                    {!! $errors->first('garage_charge', '
               <p class="help-block">:message</p>
               ') !!}
                </div>
            </div>
             <div class="col-md-6">
                <div class="form-group">
                    <label for="">{{__('settings_form.garage_charges_after')}} <sup>*</sup></label>
                    <input type="text" class="form-control input-css" name="garage_charges_after" value=" {{$settings['garage_charges_after']}}" id="garage_charges_after" placeholder="Garage charge">
                    {!! $errors->first('garage_charges_after', '
               <p class="help-block">:message</p>
               ') !!}
                </div>
            </div>
        </div>
         <div class="row">
            <div class="col-md-6">
                 <div class="form-group">
                      <label for="">{{__('settings_form.new_product')}} <sup>*</sup></label>
                      <select name="New_Product[]" class="input-css select2" multiple="multiple" data-placeholder="Select" style="width: 100%;">
                                    <option value="">Select Product</option>
                                     <?php $product_id = explode(',', $settings['New_Product']);
                                        $count = count($product_id);
                                         ?>
                                    @foreach($product as $key)
                                        @if(in_array($key->id,$product_id))
                                        <option value="{{$key->id}}" selected>{{$key->model_name.'-'.$key->model_variant.'-'.$key->color_code}}</option>
                                        @else
                                        <option value="{{$key->id}}" >{{$key->model_name.'-'.$key->model_variant.'-'.$key->color_code}}</option>
                                        @endif
                                    @endforeach
                                   <?php 
                                    ?>
                                </select>
                 </div>
            </div>
            <div class="col-md-6">
                 <div class="form-group">
                      <label for="">{{__('settings_form.used_product')}} <sup>*</sup></label>
                      <select name="Used_Product[]" class="input-css select2" multiple="multiple" data-placeholder="Select" style="width: 100%;">
                                    <option value="">Select Product</option>
                                     <?php $user_product_id = explode(',', $settings['Used_Product']);
                                        $count = count($user_product_id);
                                         ?>
                                    @foreach($used_product as $key)
                                        @if(in_array($key->id,$user_product_id))
                                        <option value="{{$key->id}}" selected>{{$key->model.'-'.$key->variant.'-'.$key->color}}</option>
                                        @else
                                        <option value="{{$key->id}}" >{{$key->model.'-'.$key->variant.'-'.$key->color}}</option>
                                        @endif
                                    @endforeach
                                   <?php 
                                    ?>
                                </select>
                 </div>
            </div>
         </div>
          </div>
    </div>
    <div class="form-group">
        <input type="submit" name="sub" class="btn btn-primary pull-right" value="Submit">
    </div>
</form>
   
</section>
@endsection