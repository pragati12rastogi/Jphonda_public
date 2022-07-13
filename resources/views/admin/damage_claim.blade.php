@extends($layout)
@section('title', __('damage_claim.title'))

@section('breadcrumb')
<li><a href="/admin/damage/claim/list"><i class=""></i> {{__('Damage Claim Summary')}} </a></li>
@endsection
@section('css')
<style>
   .content{
   padding: 30px;
   }
   .nav1>li>button {
   position: relative;
   display: block;
   padding: 10px 34px;
   background-color: white;
   margin-left: 10px;
   }
   @media (max-width: 768px)  
   {
   .content-header>h1 {
   display: inline-block;
   }
   }
   @media (max-width: 425px)  
   {
   .content-header>h1 {
   display: inline-block;
   }
   }
</style>
<link rel="stylesheet" href="/css/responsive.bootstrap.css">
@endsection
@section('js')
<script src="/js/pages/damage_claim.js"></script>
<script src="/js/dataTables.responsive.js"></script>
<script>
   function callalert(data)
   {
       alert('Damage Description :- '+data);
   }
   var dataTable;
   
   function getActive(){
     if(dataTable)
     {
       dataTable.destroy();
     }
       dataTable = $('#table').DataTable({
         "processing": true,
         "serverSide": true,
         "aaSorting":[],
         "responsive": true,
         "ajax": "/admin/damage/claim/view/api/<?php echo $damageClaimId; ?>", 
         "columns": [
               {"data":"invoice"},
               {"data":"product_name"},
               {"data":"frame"},
               // {"data":"engine_number"}, 
               // {"data":"status"}, 
               {"data":"est_amount"},
               {
                   "data":"damage_desc",
                   "render":function(data,type,full,meta)
                   {
                      if(data.length > 13)
                       {
                           return data.split(' ')[0]+'<a href="#"><span onclick="callalert(\''+data+'\')">    ..<i class="fa fa-angle-right right-arrow"></i></span></a>';
                       }
                       else{
                           return data;
                       }
                     }
                     
               },
               {"data":"status"}, 
               {"data":"repair_amount"}, 
               {"data":"repair_completion_date"}, 
               {
                   "targets": [ -1 ],
                   "data":"id", "render": function(data,type,full,meta)
                   {
                       return "<a href='/admin/damage/claim/view/update/<?php echo $damageClaimId; ?>/"+data+"'><button class='btn btn-success btn-xs'> {{__('damage_claim.damage_claim_list_Edit')}}  </button></a> &nbsp;"
                       ;
                   },
               }
           ],
           "columnDefs": [
             
               { "orderable": false, "targets": 7 }
           
           ]
         
       });
   }
   $(document).ready(function() {
       getActive();
   });

   $("input[name='sett']").on('change',function(){
      var sett = $(this).val();
      if(sett == 'Cash'){
         $(".hide_mech").hide();
      }else{
         $(".hide_mech").show();
      }
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
         <!--                    <h3 class="box-title">{{__('damage_claim.mytitle')}} </h3>-->
      </div>
      <form id="damage-claim-validation" method="post" action="/admin/damage/claim/update/{{$damageClaimId}}" class="customise-radio">
         @csrf
         <div class="col-md-6">
            <div class="row">
               <label>{{__('damage_claim.refer')}} <sup>*</sup></label>
               <select name="loadRefNumber" class="form-control" style="width:100%">
                  <option value="{{$claimData['unload_id']}}" selected>{{$claimData['refer']}}</option>
               </select>
               {!! $errors->first('loadRefNumber', '<p class="help-block">:message</p>') !!}
            </div>
            <div class="row">
               <div class="col-md-6 " >
                  <label>{{__('damage_claim.amt')}} <sup>*</sup></label>
                  <input type="number" readonly name="claimAmount" class="input-css"  value="{{$claimData['claim_amount']}}">
                  {!! $errors->first('claimAmount', '<p class="help-block">:message</p>') !!}
               </div>
               <div class="col-md-6 " >
                  <label>{{__('Repair Amount')}} <sup>*</sup></label>
                  <input type="number" readonly name="RepairAmount" class="input-css"  value="{{$claimData['repair_amount']}}">
                  {{-- {!! $errors->first('claimAmount', '
                  <p class="help-block">:message</p>
                  ') !!} --}}
               </div>
               
            </div>
            <div class="row">
               <div class="col-md-6">
                  <label>{{__('damage_claim.rec_amt')}}</label>
                  <input type="number" name="recievedAmount" class="input-css" value="{{$claimData['received_amount']}}">
                  {{-- {!! $errors->first('recievedAmount', '
                  <p class="help-block">:message</p>
                  ') !!} --}}
               </div>
               <div class="col-md-6">
                  <label>{{__('damage_claim.rec_date')}}</label>
                  <input type="text" name="recievedDate" class="input-css datepicker1" data-date-end-date="0d"  value="{{($claimData['received_date'] != null) ? (date('m/d/Y',strtotime($claimData['received_date']))) : null}}">
                  {{-- {!! $errors->first('recievedDate', '
                  <p class="help-block">:message</p>
                  ') !!} --}}
               </div>
            </div>
            {{-- <div class="row">
               <div class="col-md-6">
                  <label>{{__('Qty Keys')}}</label>
                  <input type="number" name="qtyKey" class="input-css" value="{{$claimData['qty_key']}}">
                  
               </div>
               <div class="col-md-6">
                  <label>{{__('Battery')}}</label>
                  <input type="number" name="battery" class="input-css "  value="{{$claimData['battery']}}">
               </div>
            </div>
            <div class="row">
               <div class="col-md-6">
                  <label>{{__('Saree Guard')}}</label>
                  <input type="number" name="saree"  class="input-css" value="{{$claimData['saree_guard']}}">
               </div>
               <div class="col-md-6">
                  <label>{{__('Owner Manuals')}}</label>
                  <input type="number" name="manual" class="input-css"  value="{{$claimData['owner_manual']}}">
               </div>
            </div> --}}
            <div class="row">
               {{-- <div class="col-md-6">
                  <label>{{__('Invoice Number')}} <sup>*</sup></label>
                  <input type="text" name="invoice" class="input-css"  value="{{$claimData['invoice']}}">
               </div> --}}
               <div class="col-md-6">
                  <label>Status<sup>*</sup></label>
                  <select name="status"  class="input-css select2" >
                  <option {{($claimData['status'] == 'pending') ? 'selected' : '' }}>pending</option>
                  <option {{($claimData['status'] == 'claim') ? 'selected' : '' }}>claim</option>
                  <option {{($claimData['status'] == 'done') ? 'selected' : '' }}>done</option>
                  </select>
                  {{-- {!! $errors->first('status', '
                  <p class="help-block">:message</p>
                  ') !!} --}}
               </div>
               <div class="col-md-6">
                  <label>{{__('damage_claim.sett')}}</label>
                  <div class="col-md-4">
                     <div class="radio">
                        <label><input autocomplete="off" type="radio" class="" {{(ucwords($claimData['settlement']) == 'Cash')?'checked':''}}  value="Cash" name="sett">Cash</label>
                     </div>
                  </div>
                  <div class="col-md-3">
                     <div class="radio">
                        <label><input  autocomplete="off" type="radio" {{( ucwords($claimData['settlement'])== 'Claim')?'checked':''}}  class="" value="Claim" name="sett">Claim</label>
                     </div>
                  </div>
                  {{-- {!! $errors->first('claimAmount', '
                  <p class="help-block">:message</p>
                  ') !!} --}}
               </div>
            </div>

         </div>
         <div class="col-md-6">
            
            <div class="row hide_mech">
               <div class="col-md-6">
                  <label>{{__('damage_claim.mail')}}</label>
                  <div class="col-md-6">
                     <div class="radio">
                        <label><input autocomplete="off" type="radio" {{(ucwords($claimData['mail_sent']) == 'Yes')? 'checked':''}}  class="" value="Yes" name="mail">Yes</label>
                     </div>
                  </div>
                  <div class="col-md-6">
                     <div class="radio">
                        <label><input  autocomplete="off" type="radio" {{(ucwords($claimData['mail_sent']) == 'No')? 'checked':''}}  class="" value="No" name="mail">No</label>
                     </div>
                  </div>
                  {{-- {!! $errors->first('claimAmount', '
                  <p class="help-block">:message</p>
                  ') !!} --}}
               </div>
               <div class="col-md-6">
                  <label>{{__('damage_claim.mail_date')}}</label>
                  <input type="text" name="mailDate" class="input-css datepicker1" data-date-end-date="0d" value="{{($claimData['mail_date'] != null) ? (date('m/d/Y',strtotime($claimData['mail_date']))) : null}}">
                  {!! $errors->first('mailDate', '<p class="help-block">:message</p>') !!}
               </div>
            </div>
            <div class="row hide_mech">
               <div class="col-md-6">
                  <label>{{__('Estimation Send')}}</label>
                  <div class="col-md-6">
                     <div class="radio">
                        <label><input autocomplete="off" type="radio" class="" {{(ucwords($claimData['est_sent']) == 'Yes')? 'checked':''}} value="Yes" name="est">Yes</label>
                     </div>
                  </div>
                  <div class="col-md-6">
                     <div class="radio">
                        <label><input  autocomplete="off" type="radio" class="" {{(ucwords($claimData['est_sent']) == 'No')? 'checked':''}} value="No" name="est">No</label>
                     </div>
                  </div>
                  {{-- {!! $errors->first('claimAmount', '
                  <p class="help-block">:message</p>
                  ') !!} --}}
               </div>
               <div class="col-md-6">
                  <label>{{__('damage_claim.est_date')}}</label>
                  <input type="text" name="estDate" class="input-css datepicker1"  data-date-end-date="0d" value="{{($claimData['est_date'] != null) ? (date('m/d/Y',strtotime($claimData['est_date']))) : null}}">
                  {!! $errors->first('estDate', '<p class="help-block">:message</p>') !!}
               </div>
            </div>
            <div class="row hide_mech">
               <div class="col-md-6">
                  <label>{{__('damage_claim.form')}}</label>
                  <div class="col-md-6">
                     <div class="radio">
                        <label><input autocomplete="off" type="radio" class="" {{(ucwords($claimData['claim_form_uploaded']) == 'Yes')? 'checked':''}} value="Yes" name="form">Yes</label>
                     </div>
                  </div>
                  <div class="col-md-6">
                     <div class="radio">
                        <label><input  autocomplete="off" type="radio" class="" {{(ucwords($claimData['claim_form_uploaded']) == 'No')? 'checked':''}} value="No" name="form">No</label>
                     </div>
                  </div>
                  {{-- {!! $errors->first('claimAmount', '
                  <p class="help-block">:message</p>
                  ') !!} --}}
               </div>
               <div class="col-md-6">
                  <label>Uploaded Date</label>
                  <input type="text" name="uploaded" class="input-css datepicker1" data-date-end-date="0d" value="{{($claimData['uploaded_date'] != null) ? (date('m/d/Y',strtotime($claimData['uploaded_date']))) : null}}">
                  {!! $errors->first('uploaded', '<p class="help-block">:message</p>') !!}
               </div>
            </div>
            <div class="row hide_mech">
               <div class="col-md-6">
                  <label>{{__('damage_claim.hirise')}}</label>
                  <div class="col-md-6">
                     <div class="radio">
                        <label><input autocomplete="off" type="radio" class="" {{(ucwords($claimData['hirise_bill']) == 'Yes')? 'checked':''}} value="Yes" name="hirise">Yes</label>
                     </div>
                  </div>
                  <div class="col-md-6">
                     <div class="radio">
                        <label><input  autocomplete="off" type="radio" class="" {{(ucwords($claimData['hirise_bill']) == 'No')? 'checked':''}} value="No" name="hirise">No</label>
                     </div>
                  </div>
                  {{-- {!! $errors->first('claimAmount', '
                  <p class="help-block">:message</p>
                  ') !!} --}}
               </div>
               <div class="col-md-6">
                  <label>{{__('damage_claim.bill')}}</label>
                  <input type="text" name="billDate" class="input-css datepicker1" data-date-end-date="0d" value="{{($claimData['bill_date'] != null) ? (date('m/d/Y',strtotime($claimData['bill_date']))) : null}}">
                  {!! $errors->first('billDate', '<p class="help-block">:message</p>') !!}
               </div>
            </div>
         </div>
       
         <br>     
         <div class="row">
            <div class="col-md-12" style="margin-bottom: 20px;">
               <button type="submit" class="btn btn-primary">Submit</button>
            </div>
            <br><br>    
         </div>
      </form>
   </div>
   <div class="box box-primary">
      <div class="box-body">
         <table id="table" class="table table-bordered table-striped">
            <thead>
               <tr>
                  <th>Invoice #</th>
                  <th>Product Name</th>
                  <th>Frame Number</th>
                  {{-- 
                  <th>Engine Number</th>
                  <th>Damage Status</th>
                  --}}
                  <th>Estimate Amount</th>
                  <th>Damage Description</th>
                  <th>Status</th>
                  <th>Repair Amount</th>
                  <th>Repair Completion Date</th>
                  <th>Action</th>
               </tr>
            </thead>
            <tbody>
            </tbody>
         </table>
      </div>
   </div>
   <!-- css for erroe message in radio button -->
   <style>
      #damage-claim-validation label.error
      {
      padding-left: 0px !important;
      font-weight: 700;
      }
       .customise-radio .radio
       {
           margin-top: 0px;
           margin-bottom: 0px;
       }
       .customise-radio .row
       {
           margin-bottom: 20px;
       }
       .right-arrow 
       {
           margin-left: 10px;
           font-weight: bold;
           color: #c1c1c1;
       }
   </style>
   <!-- /css for erroe message in radio button -->
</section>
@endsection
{{-- {{CustomHelpers::coolText('hcjsd')}} --}}