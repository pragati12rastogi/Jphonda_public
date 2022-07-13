@extends($layout)

@section('title', __('Currection Request Payment Details'))

@section('breadcrumb')
    <li><a href="/admin/rto/rc/correction/request/list/summary"><i class=""></i>{{__('RC Correction List')}}</a></li>
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
                        @foreach ($correctionData as $key)
                        <div class="col-md-4">
                            <label>{{__('hirise.totalamt')}} : {{$key->payment_amount}}</label>
                        </div>
                    @endforeach
                    </div><br>

                  <table id="admin_table" class="table table-bordered table-striped">
                    <thead>
                    <tr>
                      <th>{{__('hirise.payment_mode')}}</th>
                      <th>{{__('hirise.transaction_number')}}</th>
                      <th>{{__('hirise.transaction_charges')}}</th>                     
                      <th>{{__('hirise.receiver_bank_detail')}}</th>                     
                      <th>Amount</th>                     
                    </tr>
                    </thead>
                    <tbody>
                      @if($payData == "[]")
                      <tr class="text-center">
                        <td valign="top" colspan="8" class="dataTables_empty">No data available in table</td>
                      </tr>
                     @else
                      @foreach ($payData as $pay)
                      <tr>
                        <td>{{ucfirst(str_replace('_', ' ', $pay->payment_mode))}}</td>
                        <td>{{$pay->transaction_number}}</td>
                        <td>{{$pay->transaction_charges}}</td>
                        <td>{{$pay->receiver_bank_detail}}</td>
                        <td>{{$pay->amount}}</td>
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