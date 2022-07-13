@extends($layout)

@section('title', __('hirise.rto_file_submission'))

@section('user', Auth::user()->name)

@section('breadcrumb')

    <li><a href="#"><i class=""></i>{{__('hirise.rto_file_submission')}}</a></li> 
@endsection
@section('css')

<link rel="stylesheet" href="/css/responsive.bootstrap.css">    
@endsection
@section('js')
<script src="/js/dataTables.responsive.js"></script>

@endsection

@section('main_section')
    <section class="content">
        <div id="app">
            @include('admin.flash-message')
            @yield('content')
            
        </div>
    <!-- Default box -->
        <div class="box">
                <!-- /.box-header -->
            <div class="box-body">
              <form class="form" action="/admin/rto/file/submission" method="POST">
                @csrf
                    <table id="table" class="table table-bordered table-striped">
                        <thead>
                          <div class="col-sm-4">
                            <select class="form-controll" name="agent_name" style="margin-bottom: 20px;padding: 10px;">
                              <option value="">Select Agent</option>
                              @foreach($agent_name as $k => $v)
                                <option value="{{$v->key}}">{{$v->value}}</option>
                              @endforeach
                            </select>
                             {!! $errors->first('agent_name', '<p class="help-block">:message</p>') !!}
                          </div>
                            <tr>
                              <th>Id</th>                    
                              <th>{{__('hirise.rto_app_no')}}</th> 
                              <th>{{__('hirise.customer')}}</th>                    
                              <th>{{__('hirise.mobile')}}</th>                    
                              <th>{{__('Address')}}</th>                    
                              <th>{{__('hirise.rto_amount')}}</th>
                              <th>{{__('Sale Date')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                          @if($rtoData == "[]")
                          <tr class="text-center">
                            <td valign="top" colspan="8" class="dataTables_empty">No data available in table</td>
                          </tr>
                         @else
                          @foreach($rtoData as $rto)
                          <tr style="background:  {{ $rto->rc_correction_id > 0 ? '#DCDCDC' : ''}}">
                            <td><input type="checkbox" name="rto_id[]" value="{{$rto->id}}">
                               {!! $errors->first('rto_id', '<p class="help-block">:message</p>') !!}
                            </td>
                            <td>{{$rto->application_number}}</td>
                            <td>{{$rto->customer_name}}</td>
                            <td>{{$rto->mobile}}</td>
                            <td>{{$rto->address}}</td>
                            <td>{{$rto->rto_amount}}</td>
                            <td>{{$rto->sale_date}}</td>
                          </tr>
                          @endforeach
                          @endif
                        </tbody>               
                    </table>
                    <button class="btn btn-info" type="submit">Submit</button>
                  </form>
                    <!-- /.box-body -->
            </div>
        </div>
        <!-- /.box -->
    </section>
@endsection