@extends($layout)

@section('title', __('User Attendance'))

@section('js')
<script src="/js/pages/hr.js"></script>
<script>
$(document).ready(function() {
    var id = '105';
    if (id) {
        $.ajax({
            type: "get",
            url: "/admin/customer/state/" + id, //Please see the note at the end of the post**
            success: function(res) {
                if (res) {
                    $("#state").empty();
                    $("#city").empty();
                    $("#state").append('<option>Select State</option>');
                    $.each(res, function(key, value) {
                        $("#state").append('<option value="' + key + '">' + value + '</option>');
                    });
                }
            }

        }).done(function() {

            if (old_state) {
                $('#state').val(old_state).trigger('change');
            }
            $('#ajax_loader_div').css('display', 'none');
        });
    }
});
  
</script>
@endsection

@section('breadcrumb')
    <li><a href="#"><i class=""></i> {{__('User Attendance')}} </a></li>
@endsection

@section('main_section')
    <section class="content"> 
         <div id="app">
                    @include('admin.flash-message')
                    @yield('content')
            </div>
            <!-- general form elements -->
            <div class="box box-primary" style="padding-bottom: 25px;">
                <div class="box-header with-border">
<!--         <h3 class="box-title">{{__('customer.mytitle')}} </h3>-->
                </div>  
               <form id="my-form" files="true" enctype="multipart/form-data" action="" method="POST">
                @csrf
                <div class="row">
                        <div class="col-md-6">
                            <label>Import <sup>*</sup></label>
                            <input type="file" name="attendance" class="input-css" value="{{old('attendance')}}">
                            {!! $errors->first('attendance', '<p class="help-block">:message</p>') !!}
                            <br>
                            <p>Download Sample ? 
                                <a href="{{route('sampleImport',['filename'  => 'AttendanceSample.xls' ])}}">
                                    <button type="button" class="btn btn-primary"> <i class="fa fa-download"></i> </button>
                            </a>
                        </div>
                        
                </div>
                
                <div class="submit-button">
                <button type="submit" class="btn btn-primary">Submit</button>
                    
                </div>
                 </form>                 
            </div>
        
      </section>
@endsection