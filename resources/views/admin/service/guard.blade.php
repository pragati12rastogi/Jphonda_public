@extends($layout)

@section('title', __('Entry Point'))

@section('breadcrumb')
    <li><a href="#"><i class=""></i> {{__('Entry Point')}} </a></li>
@endsection
@section('main_section')
    <section class="content">
        
            @include('admin.flash-message')
                @yield('content')
            <!-- general form elements -->
            <div class="box-header with-border">

            <div class="box box-primary">
                <div class="box-header">
                </div>  
                <form  action="#" method="post">
                    @csrf
                    <div class="col-md-3">
                        <a href="/admin/service/create/tag">
                            <div class="small-box bg-green">
                                <div class="inner">
                                    <h1>FRAME IN</h1>
                                </div>
                                {{-- <div class="icon">
                                    <i class="fa fa-plus" ></i>
                                </div> --}}
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="/admin/service/frame/out/list">
                            <div class="small-box bg-danger">
                                <div class="inner">
                                    <h1>FRAME OUT</h1>
                                </div>
                                {{-- <div class="icon">
                                    <i class="fa fa-plus" ></i>
                                </div> --}}
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="/admin/service/test/ride/list">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h1>TEST RIDE</h1></h1>
                                </div>
                                {{-- <div class="icon">
                                    <i class="fa fa-plus" ></i>
                                </div> --}}
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="/admin/service/create/booking">
                            <div class="small-box bg-green">
                                <div class="inner">
                                    <h1>BOOKING</h1>
                                </div>
                                {{-- <div class="icon">
                                    <i class="fa fa-plus" ></i>
                                </div> --}}
                            </div>
                        </a>
                    </div>
                   <br>
                    <div class="row">
                        {{-- <div class="col-md-12">
                            <button type="submit" class="btn btn-success">Submit</button>
                        </div> --}}
                        <br><br>    
                    </div>  
                </form>
            </div>
            </div>
      </section>
@endsection