@extends($layout)
@section('title', __('unload.update'))

@section('breadcrumb')
<li><a href="#"><i class=""></i> {{__('unload.update')}} </a></li>
@endsection
@section('css')
<style type="text/css">
   .datepicker {
       z-index: 1049 !important;
   }
</style>
   
@endsection
@section('js')
 <!-- <script src="/js/pages/unload_data.js"></script>  -->

<script type="text/javascript">
        $(document).ready(function(){

            var unloading_date="{{$upload->unloading_date=='0000-00-00'?'':date('d-m-Y', strtotime($upload->unloading_date))}}";
            var invoiceDate="{{date('d-m-Y', strtotime($upload->invoice_date))}}";
            $( ".unloadDate" ).datepicker({
            autoclose: true,
            format: 'dd-mm-yyyy',

            }).datepicker("setDate", unloading_date);

            $( ".invoiceDate" ).datepicker({
            autoclose: true,
            format: 'd/m/yyyy',

            }).datepicker( "setDate", invoiceDate );

        });
</script>
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
      <form id="unload-data-validation" action="/admin/unload/data/edit/{{$upload->id}}" method="post">
         @csrf
         <div class="row">
            <div class="col-md-6">
               <label>{{__('unload.refer')}} <sup>*</sup></label>
               <input type="text" disabled="disabled" name="loadRefNum" class="input-css" value="{{$upload->load_referenace_number}}">
               {!! $errors->first('loadRefNum', '
               <p class="help-block">:message</p>
               ') !!}
            </div>
            <div class="col-md-6">
               <label>{{__('unload.store')}} <sup>*</sup></label>
               @if(count($store) > 1)
               <select name="store" class="input-css select2" style="width:100%">
                  <option value=" ">Select Store</option>
                  @foreach($store as $key)
                  <option value="{{$key['id']}}" {{$upload->store==$key['id'] ? 'selected=selected' : ''}}>{{$key['name']}}</option>
                  @endforeach
               </select>
               @else
               <select name="store" class="input-css select2" style="width:100%">
                  <option value="{{$store[0]['id']}}" {{$upload->store==$store[0]['id'] ? 'selected' : ''}}>{{$store[0]['name']}}</option>
               </select>
               @endif
               {!! $errors->first('store', '<p class="help-block">:message</p>') !!}
            </div>
         </div>
         <div class="row">
            <div class="col-md-6">
               <label>{{__('unload.date')}}<sup>*</sup></label>
               <input type="text" name="unloadDate" class="input-css unloadDate" autocomplete="off" value="{{$upload->unloading_date=='0000-00-00'?'':date('d-m-Y', strtotime($upload->unloading_date))}}">
               {!! $errors->first('unloadDate', '<p class="help-block">:message</p>') !!}
            </div>
            <div class="col-md-6">
               <label>{{__('unload.driver')}}<sup>*</sup></label>
               <input type="text" name="driverName" class="input-css"value="{{$upload->driver_name}}">
               {!! $errors->first('driverName', '
               <p class="help-block">:message</p>
               ') !!}
            </div>
            {{-- <div class="col-md-6">
               <label>{{__('unload.truck')}}<sup>*</sup></label>
               <input type="text" name="truckNumber" class="input-css"value="{{$upload->truck_number}}">
               {!! $errors->first('truckNumber', '
               <p class="help-block">:message</p>
               ') !!}
            </div> --}}
         </div>
         <div class="row">
            {{-- <div class="col-md-6">
               <label>{{__('unload.trans')}}<sup>*</sup></label>
               <input type="text" name="transporterName" class="input-css"value="{{$upload->transporter_name}}">
               {!! $errors->first('transporterName', '
               <p class="help-block">:message</p>
               ') !!}
            </div> --}}
            
         </div>
         <div class="row">
            <div class="col-md-6">
               <label>{{__('unload.mirror')}}<sup>*</sup></label>
               <input type="number" name="mirror_set" class="input-css"value="{{$upload->mirror_set}}">
               {!! $errors->first('mirror_set', '
               <p class="help-block">:message</p>
               ') !!}
            </div>
            <div class="col-md-6">
               <label>{{__('unload.tool')}}<sup>*</sup></label>
               <input type="number" name="tool_kit" class="input-css" value="{{$upload->toolkits}}">
               {!! $errors->first('tool_kit', '
               <p class="help-block">:message</p>
               ') !!}
            </div>
         </div>
         <div class="row">
            {{-- <div class="col-md-6">
               <label>{{__('unload.owner')}}</label>
               <input type="number" name="owner_manual" class="input-css" value="{{$upload->owners_manual}}">
               {!! $errors->first('owner_manual', '
               <p class="help-block">:message</p>
               ') !!}
            </div> --}}
            <div class="col-md-6">
               <label>{{__('unload.first')}}<sup>*</sup></label>
               <input type="number" name="first_aid" class="input-css" value="{{$upload->first_aid_kit}}">
               {!! $errors->first('first_aid', '
               <p class="help-block">:message</p>
               ') !!}
            </div>
            <div class="col-md-6">
               <label>{{__('unload.battery')}}<sup>*</sup></label>
               <input type="number" name="battery" class="input-css" value="{{$upload->battery}}">
               {!! $errors->first('battery', '
               <p class="help-block">:message</p>
               ') !!}
            </div>
         </div>
         <div class="row">
            
            <div class="col-md-6">
               <label>{{__('unload.saree')}}<sup>*</sup></label>
               <input type="number" name="saree" class="input-css" value="{{$upload->saree_guard}}">
               {!! $errors->first('saree', '
               <p class="help-block">:message</p>
               ') !!}
            </div>
            <div class="col-md-6">
               <label>{{__('unload.qty_keys')}}<sup>*</sup></label>
               <input type="number" name="qty_keys" class="input-css" value="{{$upload->qty_keys}}">
               {!! $errors->first('qty_keys', '
               <p class="help-block">:message</p>
               ') !!}
            </div>
         </div>
         <div class="row">
            
             {{-- <div class="col-md-6">
               <label>{{__('unload.factory')}}<sup>*</sup></label>
               <select disabled="disabled" name="factory" class="input-css select2" style="width:100%">
                  <option value=" ">Select Factory</option>
                  @foreach($factory as $key)
                  <option value="{{$key['id']}}" {{($upload->factory == $key['id'])? 'selected':''}}>{{$key['name']}}</option>
                  @endforeach
               </select>
               {!! $errors->first('factory', '
               <p class="help-block">:message</p>
               ') !!}
            </div> --}}
           {{-- <div class="col-md-6">
               <label>{{__('unload.status')}}</label>
               <input type="text" name="status" class="input-css" value="{{$upload->status}}">
               {!! $errors->first('status', '
               <p class="help-block">:message</p>
               ') !!}
            </div>--}}
         </div>
         <div class="row">
         </div>
         <div class="row">
            <div class="col-md-6">
               <label>{{__('unload.damage')}}<sup>*</sup></label>
               <input type="number" name="damagedVehicle" class="input-css" value="{{$upload->damaged_vehicle}}">
               {!! $errors->first('damagedVehicle', '
               <p class="help-block">:message</p>
               ') !!}
            </div>
            {{-- <div class="col-md-6">
               <label>Invoice Date <sup>*</sup></label>
               <input type="text" name="invoiceDate" class="input-css invoiceDate"value="{{$upload->invoice_date}}">
               {!! $errors->first('invoiceDate', '
               <p class="help-block">:message</p>
               ') !!}
            </div> --}}
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