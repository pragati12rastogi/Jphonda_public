@extends($layout)

@section('title', __('RTO File Submission View'))

@section('breadcrumb')
    <li><a href="/admin/rto/file/submission/list"><i class=""></i>{{__('RTO File Submission List')}}</a></li>
@endsection
@section('css')
<link rel="stylesheet" href="/css/responsive.bootstrap.css">    
@endsection


@section('main_section')
    <section class="content">
            <div id="app">
                    @include('admin.flash-message')
                    @yield('content')
        <!-- Default box -->
        <div class="box box-primary">
            <!-- /.box-header -->
            <div class="box-header with-border">
<!--                    <h3 class="box-title">{{__('customer.mytitle')}} </h3>-->
                </div>  
                <div class="box-body">
                  <div class="row">
                        @foreach ($rtoFileData as $key)
                        <div class="col-md-4">
                            <label>{{__('Agent Name')}} : {{$key->agent_name}}</label>
                        </div>
                        <div class="col-md-4">
                            <label>{{__('Handover Date')}} : {{$key->submission_date}}</label>
                        </div>
                    @endforeach
                    </div><br>
                  </div>
                </div>
                <div class="box box-primary">
            <!-- /.box-header -->
            <div class="box-header with-border">
<!--                    <h3 class="box-title">{{__('customer.mytitle')}} </h3>-->
                </div>  
                <div class="box-body">
                  <table id="admin_table" class="table table-bordered table-striped">
                    <thead>
                    <tr>
                      <th>{{__('Application Number')}}</th>
                      <th>{{__('Customer Name')}}</th>
                      <th>{{__('Customer Mobile')}}</th>
                      <th>{{__('Address')}}</th>
                      <th>{{__('RTO Amount')}}</th> 
                      <th>{{__('Sale Date')}}</th>                    
                    </tr>
                    </thead>
                    <tbody>
                      @if($rtoData == "[]")
                      <tr class="text-center">
                        <td valign="top" colspan="8" class="dataTables_empty">No data available in table</td>
                      </tr>
                     @else
                      @foreach ($rtoData as $rto)
                      <tr style="background:  {{ $rto->rc_correction_id > 0 ? '#DCDCDC' : ''}}">
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
           
                </div>
                <!-- /.box-body -->
              </div>
             
        <!-- /.box -->
      </section>
@endsection