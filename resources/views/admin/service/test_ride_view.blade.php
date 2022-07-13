@extends($layout)

@section('title', __('Test Ride Details'))

@section('breadcrumb')
   <li><a href="/admin/service/test/ride/list"><i class=""></i> {{__('Test Ride list')}} </a></li>
    <li><a href="#"><i class=""></i>{{__('Test Ride Details')}}</a></li>
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

                  <table id="" class="table table-bordered table-striped">
                    <thead>
                    <tr>
                      <th>{{__('Tag')}}</th>
                      <th>{{__('Frame')}}</th>
                      <th>{{__('Registration')}}</th>                     
                      <th>{{__('Test Ride Type')}}</th>                     
                      <th>{{__('Out Time')}}</th>                     
                      <th>{{__('In Time')}}</th>                   
                    </tr>
                    </thead>
                    <tbody>
                    @if($testRide == "[]")
                      <tr class="text-center">
                        <td valign="top" colspan="8" class="dataTables_empty">No data available in table</td>
                      </tr>
                     @else
                      @foreach ($testRide as $item)                   
                     <tr>
                        <td>{{$item->tag}}</td>
                        <td>{{$item->frame}}</td>
                        <td>{{$item->registration}}</td>
                        <td>{{$item->tr_type}}</td>
                        <td>{{$item->tr_out_time}}</td>
                        <td>{{$item->tr_in_time}}</td>
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