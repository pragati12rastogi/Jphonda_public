@extends($layout)
@section('title', __('Part Approve List'))
@section('breadcrumb')
<li><a href="#"><i class=""></i></a></li>
@endsection
@section('css')
<link rel="stylesheet" href="/css/responsive.bootstrap.css">
<style>
   tr.dangerClass{background-color: #f8d7da !important;}
   tr.successClass{background-color: #d4edda !important;}
   tr.worningClass{background-color: #fff3cd !important;}
</style>
@endsection
@section('js')
<script src="/js/dataTables.responsive.js"></script>
<script>
   var dataTable;
   $('#js-msg-error').hide();
   $('#js-msg-success').hide();
     function PartCustomerApprove(el) {
      var id = $(el).children('i').attr('class');
      var confirm1 = confirm('Are You Sure, You Want to approve part ?');
      if(confirm1)
      {
        $.ajax({  
                url:"/admin/accidental/part/customer/approve/"+id,  
                method:"get",  
                success:function(result){
                  console.log(result);
                 if(result.trim() == 'success') {
                  $('#js-msg-success').html('Part approved successfully .').show();
                   window.location.reload();
               } else if(result.trim() != 'error') {
                  $('#js-msg-error').html(result).show();
               }else{
                  $('#js-msg-error').html("Something Wen't Wrong").show();
               }
            },
            error:function(error){
               $('#js-msg-error').html("Something Wen't Wrong "+error).show();
            }     
           });
      }
    }

  function PartApprove(el) {
      var id = $(el).children('i').attr('class');
      var confirm1 = confirm('Are You Sure, You Want to approve part ?');
      if(confirm1)
      {
        $.ajax({  
                url:"/admin/accidental/part/approve/"+id,  
                method:"get",  
                success:function(result){
                 if(result.trim() == 'success') {
                  $('#js-msg-success').html('Part approved successfully .').show();
                   window.location.reload();
               } else if(result.trim() != 'error') {
                  $('#js-msg-error').html(result).show();
               }else{
                  $('#js-msg-error').html("Something Wen't Wrong").show();
               }
            },
            error:function(error){
               $('#js-msg-error').html("Something Wen't Wrong "+error).show();
            }     
           });
      }
    }

  function PartReturn(el) {
    var id = $(el).children('i').attr('class');
    var confirm1 = confirm('Are You Sure, You Want to return part ?');
    if(confirm1)
    {
      $.ajax({  
              url:"/admin/accidental/part/return/"+id,  
              method:"get",  
              success:function(result){
               if(result.trim() == 'success') {
                $('#js-msg-success').html('Part return successfully .').show();
                 window.location.reload();
             } else if(result.trim() != 'error') {
                $('#js-msg-error').html(result).show();
             }else{
                $('#js-msg-error').html("Something Wen't Wrong").show();
             }
          },
          error:function(error){
             $('#js-msg-error').html("Something Wen't Wrong "+error).show();
          }     
         });
    }
  }

</script>
@endsection
@section('main_section')
<section class="content">
   <div id="app">
      <div class="row">
         <div class="alert alert-danger" id="js-msg-error">
         </div>
         <div class="alert alert-success" id="js-msg-success">
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
                  <th>Tag #</th>
                  <th>Part Number</th>
                  <th>Part Name</th>
                  <th>Quantity</th>
                  <th>Price</th>
                  <th>Approve By</th>
                  <th>Approve</th>
                  <th>Return Status</th>
                  <th>{{__('factory.action')}}</th>
               </tr>
            </thead>
            <tbody>
              @foreach($partdata as $data)
                <tr>
                  <td>{{$data->tag}}</td>
                  <td>{{$data->part_no}}</td>
                  <td>{{$data->part_name}}</td>
                  <td>{{$data->qty}}</td>
                  <td>{{$data->qty*$data->price}}</td>
                  <td>{{$data->confirmation}}</td>
                  <td>@if($data->approved == 1) Yes @else No @endif</td>
                  <td>{{$data->part_return_status}}</td>
                  <td>
                   @if($data->approved == 0) <a href="#"><button class="btn btn-info btn-xs " onclick="PartApprove(this)"><i class="{{$data->id}}"></i>Insurance Approve</button></a>
                   <a href="#"><button class="btn btn-warning btn-xs " onclick="PartCustomerApprove(this)"><i class="{{$data->id}}"></i>Customer Approve</button></a>@endif

                   @if($data->part_return_status == null && $data->approved == 1)  <a href="#"><button class="btn btn-primary btn-xs"  onclick="PartReturn(this)"><i class="{{$data->id}}"></i>Part Return</button></a>@endif
                  </td>
                </tr>
              @endforeach
            </tbody>
         </table>
      </div>
      <!-- /.box-body -->
   </div>
   <!-- Modal -->
   <!-- /.box -->
</section>
@endsection