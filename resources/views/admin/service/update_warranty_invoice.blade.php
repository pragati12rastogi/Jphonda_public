@extends($layout)
@section('title', __('Update Invoice Details'))
@section('breadcrumb')
<li><a href="#"><i class=""></i></a></li>
@endsection
@section('css')
<link rel="stylesheet" href="/css/responsive.bootstrap.css">
<style>
   tr.dangerClass{
   background-color: #f8d7da !important;
   }
   tr.successClass{
   background-color: #d4edda !important;
   }
   tr.worningClass{
   background-color: #fff3cd !important;
   }
</style>
@endsection
@section('js')
<script src="/js/dataTables.responsive.js"></script>
@endsection
@section('main_section')
<section class="content">
   <div id="app">

         @include('admin.flash-message')
         @yield('content')
         <div class="box-header with-border">
            <div class="box box-primary">
                <div class="box-header">
                </div>  
                <form  action="/admin/service/warranty/invoice/update" method="post">
                    @csrf
                    <div class="row">
                    <input type="hidden" name="id" value="{{$invoicedata->id}}">
                  <div class="col-md-12 margin-bottom">
                     <div class="col-md-6">
                        <label for="invoice_number">Invoice Number <sup>*</sup></label>
                        <input type="text" name="invoice_number" value="{{$invoicedata->invoice_number !=null ? $invoicedata->invoice_number : old('invoice_number')}}" id="invoice_number" placeholder="Enter Invoice Number" class="input-css">
                         {!! $errors->first('invoice_number', '<p class="help-block">:message</p> ') !!}
                     </div>
                     <div class="col-md-6">
                        <label for="courier_number">Courier Number <sup>*</sup></label>
                        <input type="text" name="courier_number" value="{{$invoicedata->courier_number !=null ? $invoicedata->courier_number: old('courier_number')}}" id="courier_number" placeholder="Enter Courier Number" class="input-css">
                         {!! $errors->first('courier_number', '<p class="help-block">:message</p> ') !!}
                     </div>
                  </div>

                   <div class="col-md-12 margin-bottom">
                     <div class="col-md-6">
                        <label for="courier_amount">Courier Amount <sup>*</sup></label>
                        <input type="number" name="courier_amount" value="{{$invoicedata->courier_amount!=null ? $invoicedata->courier_amount: old('courier_amount')}}" id="courier_amount" min="0" class="input-css">
                         {!! $errors->first('courier_amount', '<p class="help-block">:message</p> ') !!}
                     </div>
                     <div class="col-md-6" >
                     <label>Part Receive<sup>*</sup></label>
                        <select name="part_receive" class="input-css select2 selectValidation" id="part_receive">
                           <option value="yes" @if($invoicedata->part_receive == 'yes') selected @endif>Yes</option>
                           <option value="no" @if($invoicedata->part_receive == 'no') selected @endif>No</option>
                        </select>
                     {!! $errors->first('part_receive', '<p class="help-block">:message</p> ') !!}
                  </div>
                  </div>
              <div class="col-md-12 margin-bottom">
                  
                   <div class="col-md-6" >
                     <label>Invoice Receive<sup>*</sup></label>
                        <select name="invoice_receive" class="input-css select2 selectValidation" id="invoice_receive">
                           <option value="yes" @if($invoicedata->invoice_receive == 'yes') selected @endif>Yes</option>
                           <option value="no" @if($invoicedata->invoice_receive == 'no') selected @endif>No</option>
                        </select>
                     {!! $errors->first('invoice_receive', '<p class="help-block">:message</p> ') !!}
                  </div>
                  <div class="col-md-6" >
                     <label>Dispatch<sup>*</sup></label>
                        <select name="dispatch" class="input-css select2 selectValidation" id="dispatch">
                           <option value="yes" @if($invoicedata->dispatch == 'yes') selected @endif>Yes</option>
                           <option value="no" @if($invoicedata->dispatch == 'no') selected @endif>No</option>
                        </select>
                     {!! $errors->first('dispatch', '<p class="help-block">:message</p> ') !!}
                  </div>
                 </div>
                  <br>
                  <div class="col-md-12 margin">
                        <div class="col-md-6">
                            <button type="submit" class="btn btn-success">Submit</button>
                        </div>
                        <br><br>    
                    </div>
                  </div>

                </form>
            </div>
         </div>
   </div>
   
   <!-- Modal -->
   <!-- /.box -->
</section>
@endsection