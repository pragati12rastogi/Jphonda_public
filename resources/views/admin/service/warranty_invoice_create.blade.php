@extends($layout)
@section('title', __('Create Invoice '))
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
         <table id="table" class="table table-bordered table-striped">
            <thead>
               <tr>
                  <th></th>
                  <th>Tag #</th>
                  <th>Job Card Type</th>
                  <th>Part Name</th>
                  <th>Part Number</th>
                  <th>Quantity</th>
                  <th>Warranty</th>
                  <th>Warranty Type</th>
                  <th>Htr Number</th>
                  <th>Warranty Tag</th>
               </tr>
            </thead>
            <tbody>
              <form action="/admin/service/warranty/invoice/create" method="post">
                @csrf
              @foreach($invoicedata as $item)
                <tr>
                  <td><input type="checkbox" name="warranty_part_id[]" value="{{$item->id}}"></td>
                  <td>{{$item->jctag}}</td>
                  <td>{{$item->job_card_type}}</td>
                  <td>{{$item->part_name}}</td>
                  <td>{{$item->part_number}}</td>
                  <td>{{$item->qty}}</td>
                  <td>{{$item->tampered}}</td>
                  <td>{{$item->warranty_type}}</td>
                  <td>{{$item->htr_number}}</td>
                  <td>{{$item->tag}}</td>
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