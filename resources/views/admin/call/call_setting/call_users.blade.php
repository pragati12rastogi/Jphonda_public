@extends($layout)

@section('title', __('Call Users Setting'))

@section('user', Auth::user()->name)

@section('breadcrumb')
  <li><a href="#"><i class=""></i>{{__('Call Users Setting')}}</a></li> 
@endsection
@section('css')

  <link rel="stylesheet" href="/css/responsive.bootstrap.css">
  <style>
    .nav-pills>li{
      margin: auto;
      margin-top: 10px;
    }
    .table-striped>tbody>tr.selected{
      background-color: aquamarine;
    }

    // table td input box
   table td {
    position: relative;
  }
   table td input {
    /* position: absolute; */
    display: block;
    top:0;
    left:0;
    margin: 0;
    height: 100% !important;
    width: 100%;
    border-radius: 0 !important;
    border: none;
    padding: 10px;
    box-sizing: border-box;
  }

  .append-div-close{
    cursor: pointer;
  }
  </style>

@endsection
@section('js')

<script src="/js/dataTables.responsive.js"></script>
<meta name="csrf-token" content="{{ csrf_token() }}" />
<script>

  var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
  var curr_change = {};

  function ajaxUpdateQty(noc, ele){
    $('#loader').css('display', 'block');

    var call_setting_id = curr_change.call_setting_id;
    var call_type = curr_change.call_type;

    if(call_setting_id && call_type && noc)
    {
      $.ajax({  
              url:"/admin/setting/call/users/updateNoc",  
              method:"POST",  
              data:{'_token':CSRF_TOKEN,'call_setting_id':call_setting_id,'call_type':call_type,'noc':noc},
              success:function(res){ 
                  if(res[0]) {
                    $("#success").show().fadeOut(10000);
                    $("#success").text(res[1]);
                    $(ele).attr('disabled',true);
                  }
              },
              error:function(error){
                console.log(error);
                  if(error.responseJSON.hasOwnProperty('message')){
                    $("#error").text(error.responseJSON.message).show().fadeOut(10000);
                  }else{
                    $("#error").text(error.responseJSON[1]).show().fadeOut(10000);
                  }
                  $(ele).val(curr_change.old_min_qty);
                  $(ele).attr('disabled',true);
              }  
        }).done(function(){
          curr_change = {};
          $('#loader').hide();
        });
    }
  }

  $(document).on('focusout','.update_noc',function(){
      var newInput = parseInt($(this).val());
      var numbers = /^[0-9]+$/;
      if(newInput.toString().match(numbers))
      {
          if(curr_change.old_min_qty != newInput && newInput >= 0 )
          {
            // do save new entry
            ajaxUpdateQty(newInput,this);
          }
          else{
            // never change
            $(this).val(curr_change.old_min_qty);
            $(this).attr('disabled',true);
          }
      }
      else{
          $(this).val(curr_change.old_min_qty);
          $(this).attr('disabled',true);
      }
  });


  $(document).on('dblclick','.update_noc',function(){
    $("#success").hide();
    $("#error").hide();
    // console.log('update');
    var call_setting_id = $(this).attr('call_setting_id');
    var call_type = $(this).attr('call_type');
    var min_qty = parseInt($(this).val());
    // console.log(row_col_id,str_split,min_qty);
    if(call_setting_id  && min_qty >= 0 && call_type)
    {
      if($(this).is(':disabled') && min_qty >= 0)
      {
          curr_change = {};
          $(this).removeAttr('disabled');
          $(this).focus();
          // push in arr
          curr_change.call_setting_id = call_setting_id;
          curr_change.call_type = call_type;
          curr_change.old_min_qty = min_qty;
      }
    }
  });

  var dataTable;

    $("#error").hide();
    $("#success").hide();
    
    common_tab_call('thankyou');
    function common_tab_call(tab_val) {

      if(dataTable)
      {
        dataTable.destroy();
      }
      dataTable = $('#table').DataTable({
        "processing": true,
         "serverSide": true,
         "ajax":  {
            url: "/admin/setting/call/users/api/"+tab_val,
            // data: function(d){
            //   var user = $("#updateAssign").find("#user").val();
            //   d.user = user;
            // }
         },
         "aaSorting": [],
         "responsive": true,
         "rowId":"id",
         "columns": [
            { "data": "user_name"},
            { "data": "store_name" },
            { "data": "noc", 
                  "render": function(data,type,full,meta)
                  {
                    var str = '';
                    str = str+"<input type='number' name='update_noc[]' call_type='"+full.type+"' disabled call_setting_id='"+full.id+"' id='update_noc-"+full.id+"' value='"+full.noc+"' min='0' class='update_noc' >";
                    return str;
                  }
            },
            {
                  "targets": [ -1 ],
                  "data":"id", "render": function(data,type,full,meta)
                  {
                    var str = '';
                    // str = str+'<a href="/admin/service/call/log/view?id='+data+'" class ="btn btn-info btn-xs">Update # of Call</a>';
                   
                    return str;
                  },
                  "orderable": false
            }
          ],
            "columnDefs": [
              // { "orderable": false, "targets": 7 }
            ]
         
       });
    }
      
    $('#psf').click(function(){
      $(".all-active").removeClass('btn-color-active');
      $(".all-active").removeClass('btn-color-unactive');
      $(this).addClass('btn-color-active');
      common_tab_call('psf');
    });

    $('#thankyou').click(function(){
      $(".all-active").removeClass('btn-color-active');
      $(".all-active").removeClass('btn-color-unactive');
      $(this).addClass('btn-color-active');
      common_tab_call('thankyou');
    });
    $('#service').click(function(){
      $(".all-active").removeClass('btn-color-active');
      $(".all-active").removeClass('btn-color-unactive');
      $(this).addClass('btn-color-active');
      common_tab_call('service');
    });
    $('#enquiry').click(function(){
      $(".all-active").removeClass('btn-color-active');
      $(".all-active").removeClass('btn-color-unactive');
      $(this).addClass('btn-color-active');
      common_tab_call('enquiry');
    });
    $('#insurance').click(function(){
      $(".all-active").removeClass('btn-color-active');
      $(".all-active").removeClass('btn-color-unactive');
      $(this).addClass('btn-color-active');
      common_tab_call('insurance');
    });

    $(document).on('click','#addMore',function(){
      var user_option_cp = $("#select_main_users").html();
      var str = '<div class="row margin-bottom">'+
                  '<div class="col-md-4">'+
                    '<select name="select_users[]" class="select_users" id="this_select">'+
                      ''+user_option_cp+
                    '</select>'+
                  '</div>'+
                  '<div class="col-md-4">'+
                    '<input type="number" name="noc[]" class="enter_noc input-css" min="0">'+
                  '</div>'+
                  '<div class="col-md-2">'+
                    '<i class="fa fa-close append-div-close"></i>'+
                  '</div>'+
                '</div>';
      $("#append-div").append(str);
      $("#this_select").val('').select2();
      $("#this_select").removeAttr('id');
    });

    $(document).on('click','.append-div-close',function(){
      $(this).parent().parent().remove();
    })
    $(document).on('change','.select_users',function(){
      var val = $(this).val();
      if(val != null && val != '' && val != undefined){
        var find = 0;
        $(document).find(".select_users").each(function(){
          var this_val = $(this).val();
          if(val == this_val){
            find = find+1;
            if(find > 1){
              $(this).val('').trigger('change');
              return false;
            }
          }
        });
      }
    });
</script>
@endsection

@section('main_section')
    <section class="content">
        <div id="app">
            @include('admin.flash-message')
            @yield('content')
        </div>
        <div class="box">
          <div class="box-header with-border">
            <label>Add User's</label>
          </div>
          <div class="box-body" >
              <form action="/admin/setting/call/users/addUsers" method="POST">
                @csrf
                <div class="row">
                  <div class="col-md-4">
                    <label>Select Call Type <sup>*</sup></label>
                    <select class="select2" name="call_type" id="call_type">
                      <option value="">Select Call Type</option>
                      @foreach($call_type as $k => $v )
                        <option value="{{$k}}" {{(old('call_type') == $k) ? 'selected' : ''}} >{{$v}}</option>
                      @endforeach
                    </select>
                    {!! $errors->first('call_type', '<p class="help-block">:message</p>') !!}
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-4">
                    <label>Select Users</label>
                  </div>
                  <div class="col-md-4">
                    <label># Of Call's</label>
                  </div>
                </div>
                <div class="main-div row margin-bottom" id="main-div-copy">
                  <div class="col-md-4">
                    <select name="select_users[]" class="select2 select_users" id="select_main_users">
                      <option value="">Select User's</option>
                      @foreach($users as $key => $val)
                        <option value="{{$val->id}}" {{(old('select_users.0') == $val->id) ? 'selected' : ''}} >{{$val->name}}</option>
                      @endforeach
                    </select>
                    {!! $errors->first('select_users.0', '<p class="help-block">:message</p>') !!}
                  </div>
                  <div class="col-md-4">
                    <input type="number" name="noc[]" class='enter_noc input-css' value="{{old('noc.0')}}">
                    {!! $errors->first('noc.0', '<p class="help-block">:message</p>') !!}
                  </div>
                </div>
                <div class="append-div row margin-bottom" id="append-div">
                  @if(!empty(old('select_users')))
                  @foreach(old('select_users') as $ind => $name)
                    @if($ind > 0)
                    <div class="row margin-bottom">
                      <div class="col-md-4">
                        <select name="select_users[]" class="select2 select_users">
                          <option value="">Select User's</option>
                          @foreach($users as $key => $val)
                            <option value="{{$val->id}}" {{(old('select_users')[$ind] == $val->id) ? 'selected' : ''}} >{{$val->name}}</option>
                          @endforeach
                        </select>
                        {!! $errors->first('select_users.'.$ind, '<p class="help-block">:message</p>') !!}
                      </div>
                      <div class="col-md-4">
                        <input type="number" name="noc[]" class='enter_noc input-css' value="{{old('noc')[$ind]}}">
                        {!! $errors->first('noc.'.$ind, '<p class="help-block">:message</p>') !!}
                      </div>
                      <div class="col-md-2">
                        <i class="fa fa-close append-div-close"></i>
                      </div>
                    </div>
                    @endif
                  @endforeach
                  @endif
                </div>
                <div class="col-md-8 margin">
                  <button type="submit" class="btn btn-success" style="float: left">Submit</button>
                  <button type="button" class="btn btn-info" id="addMore" style="float: right"><i class="fa fa-plus"> More</i></button>
                </div>
              </form>
          </div>
        </div>
        <div class="row">
          <div class="alert alert-danger" id="error" style="display: none">
          </div>
          <div class="alert alert-success" id="success" style="display: none">
          </div>
        </div>
    <!-- Default box -->
        <div class="box">
          <div class="box-header with-border">
            <div class="col-md-12">
              <ul class="nav nav1 nav-pills">
                <li class="nav-item">
                  <button class="nav-link1 all-active btn-color-active" id="thankyou" >Thank You User's</button>
                </li>
                <li class="nav-item">
                  <button class="nav-link1 all-active btn-color-unactive" id="psf" >PSF User's</button>
                </li> 
                <li class="nav-item">
                  <button class="nav-link1 all-active btn-color-unactive" id="service" >Services User's</button>
                </li> 
                <li class="nav-item">
                  <button class="nav-link1 all-active btn-color-unactive" id="enquiry" >Enquiry User's</button>
                </li> 
                <li class="nav-item">
                  <button class="nav-link1 all-active btn-color-unactive" id="insurance" >Insurance User's</button>
                </li> 
              </ul>
            </div>
          </div>
                <!-- /.box-header -->
            <div class="box-body" id="table-div">
              <table id="table" class="table table-bordered table-striped" style="width:100%">
                  <thead>
                      <tr>
                        {{-- <th><input type="checkbox" name="table_thankyou-select_all" id="table_thankyou-select_all" class="select_all"></th> --}}
                        <th> {{__('Name')}}</th>
                        <th>{{__('Store Name')}}</th>
                        <th>{{__("# of Call's")}}</th>
                        <th>{{__('Action')}}</th> 
                      </tr>
                  </thead>
                  <tbody>
                  </tbody>               
              </table>
            </div>
        </div>
          <!-- Modal -->
              <div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                  <div class="modal-content" style="margin-top: 200px!important;">
                    <div class="modal-header">
                      <h4 class="modal-title" id="exampleModalLongTitle">Update Call Response</h4>
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                      </button>
                    </div>
                    <div class="modal-body">
                      <span id="msg" style="color:red;">All Field are Required</span>
                        <div class="row">
                          <div class="col-md-6">
                              <label>Call Status<sup>*</sup></label>
                              <input id="call_summary_id" type="hidden" name="call_summary_id" class="input-css">
                              <select id="call_status"  name="call_status" class="form-control select2">
                                <option value="">Select Call Status</option>
                                <option value="Busy">Busy</option>
                                <option value="Not Reachable">Not Reachable</option>
                                <option value="Not Received">Not Received</option>
                              </select>
                              <br>
                          </div>
                          <div class="col-md-6">
                            <label>Next Call Date<sup>*</sup></label>
                            <input id="next_call_date" type="date" name="next_call_date" class="input-css">
                            <br>
                        </div>
                          <div class="col-md-12">
                              <label>Remark <sup>*</sup></label>
                              <textArea id="remark" type="text" name="remark" class="form-control"></textArea>
                          </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                      <button type="button" id="btnUpdate" onclick="btnUpdate()" class="btn btn-success">Update</button>
                    </div>
                  </div>
                </div>
              </div>
        <!-- /.box -->
    </section>
@endsection