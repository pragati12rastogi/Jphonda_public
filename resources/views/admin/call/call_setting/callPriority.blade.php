@extends($layout)

@section('title', __('Call Setting Priority'))

@section('breadcrumb')
    <li><a href="#"><i class=""></i>{{__('Call Setting Priority')}} </a></li>
@endsection
@section('css')
<link rel="stylesheet" href="/css/responsive.bootstrap.css"> 
<link href="https://fonts.googleapis.com/css?family=Roboto:300,300i,400,400i,500,500i,700,700i" rel="stylesheet">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  
@endsection
@section('js')
<script src="https://cdn.jsdelivr.net/gh/RubaXa/Sortable/Sortable.min.js"></script>
<script>

Sortable.create(demo1, { animation: 100, group: 'list-1', draggable: '.list-group-item', handle: '.list-group-item', sort: true, filter: '.sortable-disabled', chosenClass: 'active' });

    var oldUser = @php echo json_encode((old('select_users')) ? old('select_users') : null ); @endphp;
    window.onload = function () { 
      setTimeout(function(){
        if(oldUser != null && oldUser != '' && oldUser != undefined){
          $("#select_users").val(oldUser).trigger('change');
        }
      }, 0);
    }
    
    $("select[name='select_users']").on('change',function(){
      var val = $(this).val().trim();
      $("#demo1").empty();
      if(val != null && val != '' && val != undefined){
        getUserCallType(val);
      }
    });
    function getUserCallType(user){
      $("#error").hide();
      $("#success").hide();

      if(user){
        $("#loader").show();
        $.ajax({
          url:'/admin/setting/call/users/priority/get/calltype',
          method:'GET',
          data:{'user':user},
          success:function(res){
            if(res[0]){
              $.each(res[1],function(key,val){
                var str = '<li class="list-group-item">'+
                            '<span class="fa fa-arrows item-text"> '+val+'</span>'+
                            '<input class="form-control" type="hidden" hidden value="'+val+'" readonly name="items[]">'+
                          '</li>';
                $("#demo1").append(str);
              });             
            }else{
              var str = '<li class="list-group-item">'+
                            '<span class="fa fa-arrows item-text"> Not-Found Any Call Type </span>'+
                          '</li>';
                $("#demo1").append(str);
            }
          },
          error:function(error){
            if ((error.responseJSON).hasOwnProperty('responseText')) {
              $('#error').html(error.responseJSON.message).show();
            }else{
              $('#error').html(error.responseText).show();
            }
          }
        }).done(function(){
          $("#loader").hide();
        });
      }
    }
    
  </script>
@endsection

@section('main_section')
    
    <section class="content">
      @include('admin.flash-message')
        @yield('content')
        <div id="app">
                   
        <div class="box box-primary">
            <!-- /.box-header -->
            <div class="box-header with-border">
              <div class="alert alert-danger" id="error" style="display: none">
              </div>
              <div class="alert alert-success" id="success" style="display: none">
              </div>
            </div>  
            <div class="box-body">
              <form action="/admin/setting/call/users/priority" method="POST">
                @csrf
                <div class="row">
                  <div class="col-md-6">
                    <label for="select_users">Select User <sup>*</sup></label>
                    <select name="select_users" class="select2 select_users" id="select_users">
                      <option value="">Select User</option>
                      @foreach($users as $key => $val)
                        <option value="{{$val->id}}"  >{{$val->name}}</option>
                      @endforeach
                    </select>
                    {!! $errors->first('select_users', '<p class="help-block">:message</p>') !!}
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-6 ">
                    <label class="demo1" >Call Priority <sup>*</sup></label>
                    <ul id="demo1" class="list-group">
                      
                    </ul>
                    {!! $errors->first('items', '<p class="help-block">:message</p>') !!}
                  </div>
                </div>
                <div class="row pt-5 margin">
                  <br>
                  <button class="btn btn-success" type="submit" name="submit">Submit</button>
                </div>
              </form>
            </div>
        </div>
        <!-- /.box -->
      </section>
@endsection