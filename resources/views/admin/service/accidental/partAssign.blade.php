@extends($layout)

@section('title', __('Part Assign'))

@section('breadcrumb')
    <li><a href="/admin/accidental/part/request"><i class=""></i> {{__('Part Request List')}} </a></li>
@endsection
@section('css')
<style>
    .color-action{
        color:red;
        cursor: no-drop;
    }
</style>
@endsection
@section('js')
<script>
    $(document).on('keyup','.part_no',function(e){
        var part_no = $(this).val();
        $(this).parent().parent().find('.part_name').val('');
        // $(this).parent().parent().find('.part_qty').val('');
        $(this).parent().parent().find('.part_price').val('');
        var part_name_el = $(this).parent().parent().find('.part_name');
        // var part_qty_el = $(this).parent().parent().find('.part_qty');
        var part_price_el = $(this).parent().parent().find('.part_price');
        var id = e.target.id;
        id = id.split('-');
        id = id[1];
        $.ajax({
            url:'/admin/service/store/supervisor/get/partinfo',
            data: {'part_no':part_no},
            method:'GET',
            success:function(result){
                part_name_el.val(result.name);
                var qty_id = "part_qty-"+id;
                 var qty = $("#"+qty_id).val();
                var part_price = result.price*qty;
                if(part_price > 0){
                   part_price_el.val(part_price);
                }else{
                    part_price_el.val('0');
                }
            },
            error:function(error){
                alert("something wen't wrong.");
            }
        });
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
                <form  action="/admin/accidental/assign/part/{{$job_card_id}}" method="post" >
                    @csrf
                    <input type="hidden" hidden name="append_count" id="append_count" value="0">
                    {{-- <div class="col-md-6 margin">
                        <label for="customer_confirmation">Customer Confirmation <sup>*</sup></label>
                        <select name='customer_confirmation' class="input-css select2">
                            <option value="">Select Customer Confirmation</option>
                            <option value="yes">Yes</option>
                            <option value="no">No</option>
                        </select>
                    </div> --}}
                    <div class="row margin">
                        <label>Jobcard Number : {{$tag->tag}}</label>
                        <label >Request Part's</label>
                    </div>
                    <input type="hidden" hidden name="part_count" value="{{count($partInfo)}}">
                    <div class="row margin" id="append-div">
                        <div class="row" >
                                <div class="col-md-2">
                                    <label>Generic Part Name</label>
                                </div>
                                <div class="col-md-1">
                                    <label>Quantity</label>
                                </div>
                                 <div class="col-md-2">
                                    <label>Part Number</label>
                                </div>
                                <div class="col-md-3">
                                    <label>Part Name</label>
                                </div>
                                <div class="col-md-2">
                                    <label>Quantity</label>
                                </div>
                                <div class="col-md-2">
                                    <label>Price</label>
                                </div>
                               
                            </div><br>
                        <input type="hidden" name="jobcard_id" value="{{$tag->id}}">
                       @if($partInfo == '[]')
                       <div class="row text-center table table-bordered table-striped"><h4>Part not found for assign</h4></div>
                       @else
                        @foreach($partInfo as $key => $val)
                            <div class="row" id="div-{{$key}}">
                                <div class="col-md-2">
                                    <label>{{$val->part_name}}</label>
                                <input type="hidden" name="part_request_id{{$key}}" value="{{$val->id}}">
                                    {{-- <input type="text" name="part_name{{$key}}" id="part_name-{{$key}}" class="input-css" placeholder="Enter Part Name"> --}}
                                </div>
                                <div class="col-md-1">
                                    <label>{{$val->qty}}</label>
                                    {{-- <input type="number" id="part_qty-{{$key}}" name="part_qty{{$key}}" class="input-css" placeholder="Enter Part Quantity"> --}}
                                </div>
                                 <div class="col-md-2">
                                    <input type="text" id="part_number-{{$key}}" name="part_number{{$key}}" class="input-css part_no" placeholder="Enter Part Number">
                                </div>
                                <div class="col-md-3">
                                    <input type="text" id="part_name-{{$key}}" name="part_name{{$key}}" class="input-css part_name" placeholder="Enter Part Name">
                                </div>
                                <div class="col-md-2">
                                    <input type="text" id="part_qty-{{$key}}" name="part_qty{{$key}}" value="{{$val->qty}}" class="input-css part_qty" placeholder="Enter Part Quantity" readonly>
                                </div>
                                <div class="col-md-2">
                                    <input type="text" id="part_price-{{$key}}" name="part_price{{$key}}" value="{{$val->price}}" class="input-css part_price" placeholder="Enter Part Price" readonly>
                                </div>
                               
                            </div><br>
                        @endforeach
                        <br>
                        <div class="row margin-bottom">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-success">Assign</button>
                        </div>
                        <br><br>    
                    </div> 
                        @endif
                    </div>
                 
                     
                </form>
            </div>



            <div class="box box-primary">
                <div class="box-header">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Part Number</th>
                                <th>Part Name</th>
                                <th>Quantity</th>
                                <th>Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($partdetails as $part)
                            <tr>
                                <td>{{$part->part_number}}</td>
                                <td>{{$part->part_name}}</td>
                                <td>{{$part->qty}}</td>
                                <td>{{$part->price*$part->qty}}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            </div>
      </section>
@endsection