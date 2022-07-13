@extends($layout)

@section('title', 'Return Assets')

{{-- TODO: fetch from auth --}}
@section('user', Auth::user()->name)

@section('breadcrumb')
<li>
    <a href="{{url('/admin/assets/assign/employee/list')}}">Asset Assign List</a>
</li>
<li><a href="#"><i class=""></i>Asset Return</a></li>
@endsection
@section('js')
<script src="/js/pages/assets.js"></script>
<script>
  $(document).ready(function(){
      var date=  new Date();
      var dd=  date.getDate();
      var mm = date.getMonth()+1;
      var yy = date.getFullYear();
    $(".return_date").val(dd+"-"+mm+"-"+yy);
    $(".return_date").datepicker({
        endDate:'today',
        format: 'd-m-yyyy'
    });
});
</script>
@endsection

@section('main_section')
    <section class="content">
        <!-- Default box -->
        <div id="app">
                @include('admin.flash-message')
                @yield('content')
            </div>
       <form action="" method="POST" id="form">
        @csrf

       <div class="box box-header">
           <br>

            <div class="row" >
                <div class="col-md-6 {{ $errors->has('emp') ? ' has-error' : ''}}">
                    <label for="">Employee <sup>*</sup></label>
                    <input type="text" name=""class="input-css" value="{{$assets['employee']}}" readonly="">
                    {!! $errors->first('emp', '<p class="help-block">:message</p>') !!} 
                </div>
                <div class="col-md-6 {{ $errors->has('asset_name') ? ' has-error' : ''}}">
                    <label for="">Asset Name<sup>*</sup></label>
                    <input type="text" name=""class="input-css" value="{{$assets['name'].'('.$assets['asset_code'].')'}}" readonly="">
                    {!! $errors->first('asset_name', '<p class="help-block">:message</p>') !!} 
                </div>
            
            </div><br><br>
            <div class="row" >
                <div class="col-md-6 {{ $errors->has('employee') ? ' has-error' : ''}}">
                    <label for="">Return To <sup>*</sup></label>
                   <select class="input-css employee select2" id="employee" style="padding-top:2px" name="employee" required="">
                        <option value="">--Select Employee--</option>
                        @foreach($employ as $emp){
                        <option value="{{$emp->id}}">{{$emp->name}}</option>
                        @endforeach
                    </select>
                    {!! $errors->first('employee', '<p class="help-block">:message</p>') !!} 
                </div>
                <div class="col-md-6 {{ $errors->has('return_date') ? ' has-error' : ''}}">
                    <label for="">Return Date<sup>*</sup></label>
                    <input type="text" name="return_date" id="return_date" class="return_date input-css" required="">
                    {!! $errors->first('return_date', '<p class="help-block">:message</p>') !!} 
                </div>
                
            </div><br><br>
       </div>
       
        <div class="row">
                <div class="col-md-12">
                     <input type="submit" class="btn btn-primary" value="Submit">
                </div>
            </div>
        </form>
      
      </section>
@endsection
