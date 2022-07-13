@extends($layout)

@section('title', __('Manage Part Location'))

@section('breadcrumb')
    <li><a href="#"><i class=""></i> {{__('Manage Part Location')}} </a></li>

@endsection
@section('js')
{{-- <script src="/js/pages/product.js"></script> --}}

<script>
    $(document).ready(function() {

        $('#rack').on('change',function(){
            var rack_id = $(this).val();

            $('#row').empty().append('<option value="">Select Row</option>');
            $('#cell').empty().append('<option value="">Select Cell</option>');

            console.log(rack_id);
            $('#ajax_loader_div').css('display', 'block');

            $.ajax({
                url: '/admin/get/part/row/',
                method: 'GET',
                data: { 'rack_id': rack_id },
                success: function(result) {
                    // console.log(result);
                    if(result.length > 0)
                    {
                        var str = '<option value="">Select Row </option>';
                        $.each(result,function(key,val){
                            str = str+"<option value='"+val.id+"'>"+val.row_name+"</option>";
                        });
                        $('#row').empty();
                        $("#row").append(str);
                        $('#row').select2('destroy').select2();
                    }
                },
                error: function(error) {
                        // $('#ajax_loader_div').css('display', 'none');
                        alert('Something Went Wrong');
                }
            }).done(function() {
                $('#ajax_loader_div').css('display', 'none');
            });
        });


        $('#row').on('change',function(){
            var row_id = $(this).val();
            // console.log(row_id);
            $('#ajax_loader_div').css('display', 'block');

            $.ajax({
                url: '/admin/get/part/cell/',
                method: 'GET',
                data: { 'row_id': row_id },
                success: function(result) {
                    // console.log(result);
                    if(result.length > 0)
                    {
                        var str = '<option value="">Select Cell</option>';
                        $.each(result,function(key,val){
                            str = str+"<option value='"+val.id+"'>"+val.cell_name+"</option>";
                        });
                        $('#cell').empty();
                        $("#cell").append(str);
                        $('#cell').select2('destroy').select2();
                    }
                },
                error: function(error) {
                        // $('#ajax_loader_div').css('display', 'none');
                        alert('Something Went Wrong');
                }
            }).done(function() {
                $('#ajax_loader_div').css('display', 'none');
            });
        });


    });
</script>

@endsection
@section('main_section')
<section class="content">
        @include('admin.flash-message')
                @yield('content')
   <!-- Default box -->
   <div class="box-header with-border">
       <div class='box box-default'> <br>
           {{-- <h2 class="box-title" style="font-size: 28px;margin-left:20px">Import Part Data</h2><br><br><br> --}}
           <div class="container-fluid">
               <form files="true" action="/admin/part/create/location" method="POST" id="form">
                   @csrf
                  
                   <div class="row" class="col-md-12 margin">
                    <div class="col-md-6">
                        <label for="store">Store <sup>*</sup></label>
                        @if(count($store) > 1)
                            <select name="store" id="store" class="input-css select2">
                                <option value="">Select Store</option>
                                @foreach($store as $key => $val)
                                    <option value="{{$val->id}}">{{$val->name}}</option>
                                @endforeach
                            </select>
                            {!! $errors->first('store', '<p class="help-block">:message</p>') !!}

                        @else
                        <input type="text" name="store-name" readonly id="store-name" value="{{$store[0]->name}}" class="input-css">
                        <input type="hidden" hidden readonly name="store" id="store" value="{{$store[0]->id}}" class="input-css">
                        @endif

                    </div>
                        <br />
                   </div>
                   <div class="row">
                       <div class="col-md-6">
                           <label for="part">Select Part <sup>*</sup></label>
                           <select name="part" id="part" class="input-css select2">
                                <option value="">Select Part</option>
                               @foreach($part as $key => $val)
                                <option value="{{$val->id}}">{{$val->part_name}}</option>
                               @endforeach
                           </select>
                           {!! $errors->first('part', '<p class="help-block">:message</p>') !!}
                       </div>
                        <div class="col-md-6">
                            <label for="rack">Select Rack <sup>*</sup></label>
                            <select name="rack" id="rack" class="input-css select2">
                                <option value="">Select Rack</option>
                                @foreach($rack as $key => $val)
                                    <option value="{{$val->id}}">{{$val->rack_name}}</option>
                                @endforeach
                            </select>
                            {!! $errors->first('rack', '<p class="help-block">:message</p>') !!}
                        </div>
                        <div class="col-md-6">
                            <label for="row">Select Row <sup>*</sup></label>
                            <select name="row" id="row" class="input-css select2">
                                <option value="">Select Row</option>
                            </select>
                            {!! $errors->first('row', '<p class="help-block">:message</p>') !!}
                        </div>
                        <div class="col-md-6">
                            <label for="cell">Select Cell <sup>*</sup></label>
                            <select name="cell" id="cell" class="input-css select2">
                                <option value="">Select Cell</option>
                            </select>
                            {!! $errors->first('cell', '<p class="help-block">:message</p>') !!}
                        </div>
                   </div>
                <div class="row">
                    <div class="box-footer margin">
                        <button type="submit" class="btn btn-success">Submit</button>
                    </div>
                </div>
                   <!--submit button row-->
               </form>
           <!--end of container-fluid-->
       </div>
       <!------end of box box-default---->
   </div>
   <!--end of box-header with-border-->
</section>
<!--end of section-->
@endsection
