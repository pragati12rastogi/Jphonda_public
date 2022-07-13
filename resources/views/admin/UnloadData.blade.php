@extends($layout)
@section('title', __('unload.title'))

@section('breadcrumb')
<li><a href="#"><i class=""></i> {{__('unload.title')}} </a></li>
@endsection
@section('js')
{{-- <script src="/js/pages/unload_data.js"></script> --}}
@endsection
@section('main_section')
<section class="content">
   @include('admin.flash-message')
   @yield('content')
   <!-- general form elements -->
   <div class="box box-primary">
      <div class="box-header with-border">
         <!--                    <h3 class="box-title">{{__('unload.mytitle')}} </h3>-->
      </div>
      <form id="unload-data-validation" action="/admin/unload/data" method="post" files="true" enctype="multipart/form-data">
         @csrf
         <div class="row">
            <div class="col-md-6">
               <label>{{__('unload.refer')}} <sup>*</sup></label>
               <input type="text" name="loadRefNum" class="input-css" value="{{old('loadRefNum')}}">
               {!! $errors->first('loadRefNum', '
               <p class="help-block">:message</p>
               ') !!}
            </div>
            <div class="col-md-6">
               <label>{{__('unload.store')}} <sup>*</sup></label>
               @if(count($store) > 1)
               <select name="store" class="input-css select2" style="width:100%">
                  <option value=" ">Select Store</option>
                  @foreach($store as $in => $key)
                  <option value="{{$key['id']}}" {{(old('store') == $key['id'])? 'selected':''}}>{{$key['name']}}</option>
                  @endforeach
               </select>
               @else
               <select name="store" class="input-css select2" style="width:100%">
                  @if(count($store) > 0)
                  <option value="{{$store[0]['id']}}" selected >{{$store[0]['name']}}</option>
                  @endif
               </select>
               @endif
               {!! $errors->first('store', '<p class="help-block">:message</p>') !!}
            </div>
         </div>
         <div class="row">
            <div class="col-md-6">
               <label>{{__('unload.date')}}<sup>*</sup></label>
               <input type="text" name="unloadDate" class="input-css datepicker"value="{{old('unloadDate')}}">
               {!! $errors->first('unloadDate', '
               <p class="help-block">:message</p>
               ') !!}
            </div>
            {{-- <div class="col-md-6">
               <label>{{__('unload.truck')}}<sup>*</sup></label>
               <input type="text" name="truckNumber" class="input-css"value="{{old('truckNumber')}}">
               {!! $errors->first('truckNumber', '
               <p class="help-block">:message</p>
               ') !!}
            </div> --}}
            <div class="col-md-6">
               <label>{{__('unload.driver')}}<sup>*</sup></label>
               <input type="text" name="driverName" class="input-css"value="{{old('driverName')}}">
               {!! $errors->first('driverName', '
               <p class="help-block">:message</p>
               ') !!}
            </div>
         </div>
         <div class="row">
            {{-- <div class="col-md-6">
               <label>{{__('unload.trans')}}<sup>*</sup></label>
               <input type="text" name="transporterName" class="input-css"value="{{old('transporterName')}}">
               {!! $errors->first('transporterName', '
               <p class="help-block">:message</p>
               ') !!}
            </div> --}}
            
         </div>
         {{-- <div class="row">
            <div class="col-md-6">
               <label>{{__('unload.qty_sc')}}</label>
               <input type="number" name="qtysc" class="input-css"value="{{old('qtysc')}}">
               {!! $errors->first('qtysc', '
               <p class="help-block">:message</p>
               ') !!}
            </div>
            <div class="col-md-6">
               <label>{{__('unload.qty_mc')}}</label>
               <input type="number" name="qtymc" class="input-css"value="{{old('qtymc')}}">
               {!! $errors->first('qtymc', '
               <p class="help-block">:message</p>
               ') !!}
            </div>
         </div> --}}
         <div class="row">
            <div class="col-md-6">
               <label>{{__('unload.mirror')}}<sup>*</sup></label>
               <input type="number" name="mirror_set" class="input-css"value="{{old('mirror_set')}}">
               {!! $errors->first('mirror_set', '
               <p class="help-block">:message</p>
               ') !!}
            </div>
            <div class="col-md-6">
               <label>{{__('unload.tool')}}<sup>*</sup></label>
               <input type="number" name="tool_kit" class="input-css" value="{{old('tool_kit')}}">
               {!! $errors->first('tool_kit', '
               <p class="help-block">:message</p>
               ') !!}
            </div>
         </div>
         <div class="row">
            {{-- <div class="col-md-6">
               <label>{{__('unload.owner')}}</label>
               <input type="number" name="owner_manual" class="input-css" value="{{old('owner_manual')}}">
               {!! $errors->first('owner_manual', '
               <p class="help-block">:message</p>
               ') !!}
            </div> --}}
            <div class="col-md-6">
               <label>{{__('unload.first')}}<sup>*</sup></label>
               <input type="number" name="first_aid" class="input-css" value="{{old('first_aid')}}">
               {!! $errors->first('first_aid', '
               <p class="help-block">:message</p>
               ') !!}
            </div>
            <div class="col-md-6">
               <label>{{__('unload.battery')}}<sup>*</sup></label>
               <input type="number" name="battery" class="input-css" value="{{old('battery')}}">
               {!! $errors->first('battery', '
               <p class="help-block">:message</p>
               ') !!}
            </div>
         </div>
         <div class="row">
            
            <div class="col-md-6">
               <label>{{__('unload.saree')}}<sup>*</sup></label>
               <input type="number" name="saree" class="input-css" value="{{old('saree')}}">
               {!! $errors->first('saree', '
               <p class="help-block">:message</p>
               ') !!}
            </div>
            <div class="col-md-6">
               <label>{{__('unload.qty_keys')}}<sup>*</sup></label>
               <input type="number" name="qty_keys" class="input-css" value="{{old('qty_keys')}}">
               {!! $errors->first('qty_keys', '
               <p class="help-block">:message</p>
               ') !!}
            </div>
         </div>
         <div class="row">
            
            {{--<div class="col-md-6">
               <label>{{__('unload.status')}}</label>
               <input type="text" name="status" class="input-css" value="{{old('status')}}">
               {!! $errors->first('status', '
               <p class="help-block">:message</p>
               ') !!}
            </div>
         </div>--}}
         <div class="row">
           
            {{-- <div class="col-md-6">
               <label>{{__('unload.factory')}}<sup>*</sup></label>
               <select name="factory" class="input-css select2" style="width:100%">
                  <option value=" ">Select Factory</option>
                  @foreach($factory as $key)
                  <option value="{{$key['id']}}" {{(old('factory') == $key['id'])? 'selected':''}}>{{$key['name']}}</option>
                  @endforeach
               </select>
               {!! $errors->first('factory', '
               <p class="help-block">:message</p>
               ') !!}
            </div> --}}
         </div>
         <div class="row">
            <div class="col-md-6">
               <label>{{__('unload.damage')}}<sup>*</sup></label>
               <input type="number" name="damagedVehicle" class="input-css" value="{{old('damagedVehicle')}}">
               {!! $errors->first('damagedVehicle', '
               <p class="help-block">:message</p>
               ') !!}
            </div>
            {{-- <div class="col-md-6">
               <label>Invoice Date <sup>*</sup></label>
               <input type="text" name="invoiceDate" class="input-css datepicker"value="{{old('invoiceDate')}}">
               {!! $errors->first('invoiceDate', '
               <p class="help-block">:message</p>
               ') !!}
            </div> --}}
            <div class="col-md-6 {{ $errors->has('excel') ? 'has-error' : ''}}" >
               <label>Import Loader Data <sup>*</sup></label>
               <input type="file" name="excel" id="excel_data" class="input-css" />
               <!-- <small class="text-muted">Accepted File Format : xls, xlt, xltm, xltx, xlsm and xlsx </small> -->
               {!! $errors->first('excel', '<p class="help-block">:message</p>') !!}
               <br>
               <p>Download Sample ? 
                  <a href="{{route('sampleImport',['filename'  => 'UnloadSample.xlsx' ])}}">
                  <button type="button" class="btn btn-primary"> <i class="fa fa-download"></i> </button>
               </a>
            </div>
         </div>
         <div class="row">
         </div>
         <br>
         <div class="row">
            <div class="col-md-12">
               <button type="submit" class="btn btn-primary">Submit</button>
            </div>
            <br><br>    
         </div>
      </form>
   </div>
</section>
@endsection
{{-- {{CustomHelpers::coolText('hcjsd')}} --}}