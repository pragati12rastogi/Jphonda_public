<div class="">
   <div class="row " style="margin-top:10px;margin-bottom:20px;" >
         <div class="col-md-11 sale-tab " style="padding-left: 0px;">
            <a href="/admin/sale">
               <button class="btn {{ Request::segment(3)=='' ? 'btn-success' : ''}}" disabled id="select_book">Sale</button>
            </a>
            <i class="fa fa-long-arrow-right" style="padding-top:10px; padding-left:3px;"></i>
         </div>
         <div class="col-md-1 sale-tab " style="">
               <a href="/admin/sale/pay/{{request()->route('id')}}">
               <button class="btn {{ Request::segment(3)=='pay' ? 'btn-success' : ''}}" id="select_hi-rise">Payment</button>
               </a>
               <i class="fa fa-long-arrow-right"  style="padding-top:10px; padding-left: 0px;"></i>
         </div>
         <div class="col-md-1 sale-tab "  style="">
               <a href="/admin/sale/order/{{request()->route('id')}}">
               <button class="btn {{ Request::segment(3)=='order' ? 'btn-success' : ''}}" id="select_hi-rise">Order</button>
               </a>
               <i class="fa fa-long-arrow-right"  style="padding-top:10px; padding-left: 3px;"></i>
         </div>
         <div class="col-md-1 sale-tab "  style="">
               <a href="/admin/sale/hirise/{{request()->route('id')}}">
               <button class="btn {{ Request::segment(3)=='hirise' ? 'btn-success' : ''}}" id="select_hi-rise">Hi-Rise</button>
               </a>
               <i class="fa fa-long-arrow-right"  style="padding-top:10px; padding-left: 3px;"></i>
         </div>

         <div class="col-md-2 sale-tab "  style="">
               <a href="/admin/sale/ew/invoice/{{request()->route('id')}}">
               <button class="btn {{ Request::segment(4)=='invoice' ? 'btn-success' : ''}}" id="select_hi-rise">EW-Invoice</button>
               </a>
               <i class="fa fa-long-arrow-right"  style="padding-top:10px; padding-left: 3px;"></i>
         </div>
         
         <div class="col-md-2 sale-tab " >
               <a href="/admin/sale/insurance/{{request()->route('id')}}">
               <button class="btn {{ Request::segment(3)=='insurance' ? 'btn-success' : ''}}" id="select_insur"> Insurance</button>
               </a>
               <i class="fa fa-long-arrow-right"  style="padding-top:10px; padding-left: 6px;"></i>
            </div>
            
            <div class="col-md-2 sale-tab "  >
               <a href="/admin/sale/rto/{{request()->route('id')}}">
                  <button class="btn {{ Request::segment(3)=='rto' ? 'btn-success' : ''}}" id="select_rto"> RTO</button>
               </a>
               <i class="fa fa-long-arrow-right"  style="padding-top:10px; padding-left: 6px;"></i>
            </div>
            <div class="col-md-2 sale-tab " >
               <a href="/admin/sale/pending/item/{{request()->route('id')}}">
                  <button class="btn {{ Request::segment(3)=='pending' ? 'btn-success' : ''}}" id="select_pending_item"> Pending Item's</button>
               </a>
               <i class="fa fa-long-arrow-right"  style="padding-top:10px; padding-left: 6px;"></i>
         </div>
         <div class="col-md-11 prl-0  sale-tab " style="" >
            <a href="/admin/sale/otc/{{request()->route('id')}}">
            <button class="btn {{ Request::segment(3)=='otc' ? 'btn-success' : ''}}" id="select_otc"> OTC</button>
            </a>
         </div>
   </div>
</div>
