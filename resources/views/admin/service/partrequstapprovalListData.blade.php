@extends($layout)

@section('title', __('Part Request Approval List'))

@section('breadcrumb')
    <li><a href="#"><i class=""></i> {{__('Part Request Approval List')}} </a></li>
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
    $('#js-msg-error').hide();
   $('#js-msg-success').hide();
$(document).on('click','.approve',function(){

    var id = $(this).attr('id');
     var confirm1 = confirm('Are You Sure, You Want to Approved ?');
      if(confirm1)
      {
         $.ajax({
            url:'/admin/service/partrequest/approval/list/approve',
            data:{'part_request_id':id},
            method:'GET',
            success:function(result){

               if(result.trim() == 'success')
               {
                  $('#js-msg-success').html('Successfully Service Part Request Approved.').show();
               }
               else if(result.trim() != 'error')
               {
                  $('#js-msg-error').html(result).show();
               }
               else{
                  $('#js-msg-error').html("Something Wen't Wrong").show();
               }
               // console.log('result',result);
               location.reload();
            },
            error:function(error){
               $('#js-msg-error').html("Something Wen't Wrong "+error).show();
            }
         });
      }
   

});
</script>
@endsection
@section('main_section')
    <section class="content">
        
        <div class="row">
         <div class="alert alert-danger" id="js-msg-error">
         </div>
         <div class="alert alert-success" id="js-msg-success">
         </div>
       </div>
            @include('admin.flash-message')
                @yield('content')
            <!-- general form elements -->
            <div class="box-header with-border">

            <div class="box box-primary">
                <div class="box-header">
               
                    <div class="col-md-12">
                        <div class="col-md-3">
                            <label >CUSTOMER NAME :-  {{$customer['name']}} </label>
                        </div>
                        <div class="col-md-3">
                            <label >CONTACT NUMBER :- {{$customer['mobile']}}</label>
                        </div>
                    </div>
                    <br><br>
                     @if($getpart == '[]')
                     @else
                    <table class="table table-bordered table-striped"><br><br>
                        <thead>
                            <th>Part Name</th>
                            <th>Part Number</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Confirmation</th>
                            <th>Call Status</th>
                            <th>Status</th>
                            <th>Action</th>
                        </thead>
                        <tbody>
                                @foreach($getpart as $part)
                                    <tr>
                                        <td>{{$part->part_name}}</td>
                                        <td>{{$part->part_number}}</td>
                                        <td>@if($part->issue_qty > 0) {{$part->issue_qty}} @else {{$part->assign_qty}} @endif</td>
                                        <td>@if($part->issue_qty > 0) {{$part->price*$part->issue_qty}} @else {{$part->price*$part->assign_qty}} @endif</td>
                                        <td>{{$part->confirmation}}</td>
                                        <td>{{$part->call_status}}</td>
                                        <td>
                                           @if($part->approved=='1')
                                                Approved
                                          @else
                                              Pending
                                           @endif
                                        </td>
                                        <td>
                                        
                                         @if($part->approved!='1')
                                         <button id='{{$part->id}}' class='btn btn-info btn-xs approve'>Approve</button>
                                         @endif
                                            
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
               
                  </div>           
            </div>
            </div>
            <div class="box box-primary">
                <div class="box-header">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <th>Part Name</th>
                            <th>Part Number</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Confirmation</th>
                            <th>Call Status</th>
                            <th>Status</th>
                        </thead>
                        <tbody>
                            @if($partdata)
                                @foreach($partdata as $part)
                                    <tr>
                                        <td>{{$part->part_name}}</td>
                                        <td>{{$part->part_number}}</td>
                                        <td>@if($part->issue_qty > 0) {{$part->issue_qty}} @else {{$part->assign_qty}} @endif</td>
                                        <td>@if($part->issue_qty > 0) {{$part->price*$part->issue_qty}} @else {{$part->price*$part->assign_qty}} @endif</td>
                                        <td>{{$part->confirmation}}</td>
                                        <td>{{$part->call_status}}</td>
                                        <td>
                                           @if($part->approved=='1')
                                                Approved
                                          @else
                                              Pending
                                           @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
      </section>
@endsection