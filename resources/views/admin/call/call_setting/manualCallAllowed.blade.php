@extends($layout)

@section('title', __('Manual Call Allowed'))

@section('breadcrumb')
    <li><a href="#"><i class=""></i>{{__('Call Setting')}} </a></li>
@endsection
@section('css')
<link rel="stylesheet" href="/css/responsive.bootstrap.css"> 
@endsection
@section('js')

@endsection

@section('main_section')
    
    <section class="content">
      @include('admin.flash-message')
        @yield('content')
        <div id="app">
                   
        <div class="box box-primary">
            <!-- /.box-header -->
            <div class="box-header with-border">
            </div>  
            <div class="box-body">
              <form action="/admin/call/setting/manualCallAllowed" method="POST">
                @csrf
                <div class="row">
                  <div class="col-md-6">
                    <label for="select_users">Select User <sup>*</sup></label>
                    <select name="users[]" class="form-control select2" multiple="multiple" data-placeholder="Select Users"
                                style="width: 100%;">
                      @foreach($users as $key => $val )
                          @if(in_array($val['id'],$allowed_user))
                              <option value="{{$val['id']}}" selected>{{$val['name']}}</option>
                          @else
                              <option value="{{$val['id']}}">{{$val['name']}}</option>
                          @endif
                      @endforeach
                    </select>
                    {!! $errors->first('users', '<p class="help-block">:message</p>') !!}
                  </div>
                </div>
                <div class="row pt-5 margin">
                  <br>
                  <button class="btn btn-success" type="submit" name="submit">Update</button>
                </div>
              </form>
            </div>
        </div>
        <!-- /.box -->
      </section>
@endsection