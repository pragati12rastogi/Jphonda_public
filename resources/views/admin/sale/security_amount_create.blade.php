@extends($layout)

@section('title', __('hirise.security_amount'))

@section('breadcrumb')
    <li><a href="#"><i class=""></i> {{__('hirise.security_amount')}} </a></li>
    
@endsection
@section('js')
<script>

     $("document").ready(function() {
    $("#my-form").validate({
        rules: {
            name: {
                required: true,
                // lettersonly: true,
                minlength: 2,
                noSpace: true

            },
            number: {
                required: true,
                number: true,
                maxlength: 10,
                minlength: 10,
                noSpace: true
            },
            sale_no: {
                required: true
            },
            store: {
                required: true
            },
            reason: {
                required: true
            },
        },
        messages: {
            required: "This field is mendatary**",
            password: {
                pwcheck: "use letter and digit both"
            }
        }


    });
});
    $('#sale_no').on('keyup', function() {
        var text = $('#sale_no').val();
        $("#sale_id").val('');
        if (text == '') {
            frameValidate = 1;
            $("#sale_msg").html('');
        } else {

            $.ajax({
                type: "GET",
                url: '/admin/sale/salenumber/search',
                data: { text: text },
                success: function(response) {
                    var arr = Object.values(response);
                    if (arr.length > 0) {
                        $("#sale_msg").hide();
                        $("#name").val(response.name);
                        $("#sale_id").val(response.sale_id);
                        $("#number").val(response.mobile);
                    }else{
                        $("#sale_msg").html('').show();
                        $("#sale_msg").append('Sale number not found.');
                    }
                        

                },
                error: function() {
                    frameValidate = 0;
                    $("#sale_msg").html('');
                    $("#sale_msg").append('Internal Issue Try Again');
                }
            });
        }
    });
</script>
@endsection


@section('main_section')
    <section class="content">
            @include('admin.flash-message')
                @yield('content')
            <!-- general form elements -->
            <div class="box-header with-border">
            <div class="box box-primary">
                <div class="box-header">
                </div> 
                <form id="my-form" action="/admin/sale/security/amount" method="post">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <label>{{__('hirise.sale_no')}} <sup>*</sup></label>
                            <input type="text" name="sale_no" id="sale_no" class="input-css " value="{{(old('sale_no')) ? old('sale_no') : '' }}" >
                             {!! $errors->first('sale_no', '<p class="help-block">:message</p>') !!}
                            <span id="sale_msg" style="color:red"></span>
                            <input type="hidden" hidden name="sale_id" id="sale_id" value="">
                        </div>
                    <?php $store_id = explode(',', $user->store_id);
                         $count = count($store_id);
                         if ($count < 2) { ?>
                              <div class="col-md-6" style="display: none;">
                                <label>{{__('admin.store')}} <sup>*</sup></label>
                                <select class="input-css" name="store" >
                                       @foreach ($store as $key)
                                            <option value="{{$key->id}} selected" >{{$key->name.'-'.$key->store_type}}</option>
                                       @endforeach
                                </select>
                                {!! $errors->first('store', '<p class="help-block">:message</p>') !!}
                            </div>
                         <?php }else{ ?>
                            <div class="col-md-6">
                                <label>{{__('admin.store')}} <sup>*</sup></label>
                                <select class="input-css select2" name="store">
                                <option value="">Select Store</option>
                                @foreach ($store as $key)
                                    <option value="{{$key->id}}" >{{$key->name.'-'.$key->store_type}}</option>
                                @endforeach
                                </select>
                                {!! $errors->first('store', '<p class="help-block">:message</p>') !!}
                            </div>
                    <?php  } ?>
                    </div><br>
                    <div class="row">
                        <div class="col-md-6">
                            <label>{{__('hirise.customer')}} <sup>*</sup></label>
                            <input type="text" name="name" id="name" class="input-css " value="{{(old('name')) ? old('name') : '' }}">
                            {!! $errors->first('name', '<p class="help-block">:message</p>') !!}
                        </div>
                        <div class="col-md-6">
                            <label>{{__('hirise.mobile')}} <sup>*</sup></label>
                             <input type="text" name="number" id="number" class="input-css " value="{{(old('number')) ? old('number') : '' }}">
                            {!! $errors->first('number', '<p class="help-block">:message</p>') !!}
                        </div>
                    </div><br>
                    <div class="row">
                        <div class="col-md-12">
                            <label>{{__('Reason for security')}} <sup>*</sup></label>
                            <textarea class="form-control" name="reason" rows="4" cols="50">{{(old('reason')) ? old('reason') : '' }}</textarea>
                            {!! $errors->first('reason', '<p class="help-block">:message</p>') !!}
                        </div>
                    </div><br>
                    

                    <div class="row">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-success">Submit</button>
                        </div>
                        <br><br>    
                    </div>  
                </form>
            </div>
      </section>
@endsection