@extends($layout)
@section('title', __('Create Purchase Order '))
@section('breadcrumb')
<li><a href="#"><i class=""></i></a></li>
@endsection
@section('css')
<link rel="stylesheet" href="/css/responsive.bootstrap.css">
<style>
   tr.dangerClass{
   background-color: #f8d7da !important;
   }
   tr.successClass{
   background-color: #d4edda !important;
   }
   tr.worningClass{
   background-color: #fff3cd !important;
   }
   .modal-header .close {
    margin-top: -33px;
  }
  .wickedpicker {
    z-index: 1064 !important;
   }
</style>
@endsection
@section('js')
<script src="/js/dataTables.responsive.js"></script>
<script>
   var dataTable;
   $('#js-msg-error').hide();
   $('#js-msg-verror').hide();
   $('#js-msg-success').hide();
  $(document).on('change','.part_id',function(){
      var id = $(this).val();
      $('#ajax_loader_div').css('display', 'block');
      $(this).parent().parent().find('.part_number').val('');
      $(this).parent().parent().find('.part_price').val('');
      $(this).parent().parent().find('.fms').val('');
       var part_number_el = $(this).parent().parent().find('.part_number');
       var part_fms_el = $(this).parent().parent().find('.fms');
       var part_price_el = $(this).parent().parent().find('.part_price');

    if (id) {
        $.ajax({
            type: "get",
            url: "/admin/part/get/partdetails/" + id, 
            success: function(res) {
                if (res) {
                part_number_el.val(res.part_number);
                part_price_el.val(res.price);
                part_fms_el.val(res.consumption_level);
                }
            }

        }).done(function() {
            $('#ajax_loader_div').css('display', 'none');
        });
    }

})
</script>

 
 
</script>
@endsection
@section('main_section')
<section class="content">
   <div id="app">
      <div class="row">
         <div class="alert alert-danger" id="js-msg-error" style="display: none;">
         </div>
         <div class="alert alert-success" id="js-msg-success" style="display: none;">
         </div>
       </div>
   <div class="box box-primary">
      <!-- /.box-header -->
      <div class="box-header with-border">
          {{-- <h3 class="box-title">{{__('customer.mytitle')}} </h3> --}}
      </div>
      
      <div class="box-body">
         @include('admin.flash-message')
         @yield('content')
         <table id="table" class="table table-bordered">
            <thead>
               <tr>
                  <th></th>
                  <th>Model Name</th>
                  <th>Color</th>
                  <th>Part Name</th>
                  <th>Part Number</th>
                  <th>Part Price</th>
                  <th>Quantity</th>
                  <th>FMS</th>
               </tr>
            </thead>
            <tbody>
              <form action="/admin/part/purchase/order/create" method="post">
                @csrf

               
              @foreach($request as $key => $item)
                <tr>
                  <td><input type="checkbox" name="po_request[]" value="{{$item->model_name}}">   {!! $errors->first('po_request', '<p class="help-block">:message</p>') !!}</td>

                  <td>{{$item->model_name}}</td>
                  <td>{{$item->color}}</td>

                  <td>@if($item->name == 0)
                    <select class="input-css select2 part_id" name="part_id{{$key}}" id="part_id-{{$key}}" style="width:100%;">
                    <option selected="" disabled="">Select Part</option>
                    @foreach($part as $pt)
                      <option value="{{$pt->id}}">{{$pt->name}}</option>
                    @endforeach
                    </select> 
                     {!! $errors->first('part_id', '<p class="help-block">:message</p>') !!}
                    @else{{$item->name}}@endif</td>

                  <td> @if($item->part_number == null) 
                    <input type="text" class="form-control part_number" id="part_number-{{$key}}" name="part_number{{$key}}"  style="border: none;padding: 6px;pointer-events: none;">
                       {!! $errors->first('part_number', '<p class="help-block">:message</p>') !!} @else{{$item->part_number}}@endif</td>

                       <td> @if($item->price == null) 
                    <input type="text" class="form-control part_price" id="part_price-{{$key}}" name="part_price{{$key}}"  style="border: none;padding: 6px;pointer-events: none;">
                       {!! $errors->first('part_price', '<p class="help-block">:message</p>') !!} @else{{$item->price}}@endif</td>

                  <td> @if($item->qty == null)
                   <input type="text" class="form-control" name="qty{{$key}}" id="qty-{{$key}}">
                     {!! $errors->first('qty', '<p class="help-block">:message</p>') !!}
                   @else{{$item->qty}}@endif</td>

                  <td> @if($item->consumption_level == null) 
                    <input type="text" class="form-control fms" name="fms{{$key}}" id="fms-{{$key}}" style="border: none;padding: 6px;pointer-events: none;" > 
                      {!! $errors->first('fms', '<p class="help-block">:message</p>') !!}
                    @else{{$item->consumption_level}}@endif</td>

                </tr>
              @endforeach
               
            </tbody>
         </table><br>
         <button class="btn btn-info pull-right" type="submit" >Submit</button>
            </form>
            
      </div>
      <!-- /.box-body -->
   </div>
   <!-- /.box -->
   <!-- Modal -->
  
</section>
@endsection