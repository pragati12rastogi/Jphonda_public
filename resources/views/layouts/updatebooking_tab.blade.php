         <div class="">
            <div class="row " style="margin-top:10px;margin-bottom:20px;" >
                  <div class="col-md-1  " style="padding-left: 0px;">
                     <a href="/admin/update/sale/{{request()->route('id')}}">
                        <button class="btn {{ Request::segment(3)=='sale' ? 'btn-success' : ''}}"   id="select_sale">Sale</button>
                     </a>
                     <i class="fa fa-long-arrow-right" style="padding-top:10px; padding-left:3px;"></i>
                  </div>
                  {{-- <div class="col-md-2 "  style="padding-left:18px;">
                        <a href="/admin/sale/pay/{{request()->route('id')}}">
                        <button class="btn {{ Request::segment(3)=='pay' ? 'btn-success' : ''}}" id="select_hi-rise">Payment</button>
                        </a>
                        <i class="fa fa-long-arrow-right"  style="padding-top:10px; padding-left: px;"></i>
                  </div> --}}
                  <div class="col-md-1"  style="padding-left:0px;">
                        <a href="/admin/sale/update/order/{{request()->route('id')}}">
                        <button class="btn {{ Request::segment(4)=='order' ? 'btn-success' : ''}}" id="select_hi-rise">Order</button>
                        </a>
                        <i class="fa fa-long-arrow-right"  style="padding-top:10px; padding-left: 3px;"></i>
                  </div>
                  <div class="col-md-2"  style="padding-left: 25px;">
                        <a href="/admin/sale/update/hirise/{{request()->route('id')}}">
                        <button class="btn {{ Request::segment(4)=='hirise' ? 'btn-success' : ''}}" id="select_hi-rise">Hi-Rise</button>
                        </a>
                        <i class="fa fa-long-arrow-right"  style="padding-top:10px; padding-left: 3px;"></i>
                  </div>
                  
                  <div class="col-md-2 " style="padding-left: 6px;" >
                        <a href="/admin/sale/update/insurance/{{request()->route('id')}}">
                        <button class="btn {{ Request::segment(4)=='insurance' ? 'btn-success' : ''}}" id="select_insur"> Insurance</button>
                        </a>
                        <i class="fa fa-long-arrow-right"  style="padding-top:10px; padding-left: 6px;"></i>
                     </div>
                     
                     <div class="col-md-1 " style="padding-left: 6px;" >
                        <a href="/admin/sale/update/rto/{{request()->route('id')}}">
                           <button class="btn {{ Request::segment(4)=='rto' ? 'btn-success' : ''}}" id="select_rto"> RTO</button>
                        </a>
                        <i class="fa fa-long-arrow-right"  style="padding-top:10px; padding-left: 6px;"></i>
                     </div>
                     <div class="col-md-2  " style="padding-left: 6px;" >
                        <a href="/admin/sale/update/pending/item/{{request()->route('id')}}">
                           <button class="btn {{ Request::segment(4)=='pending' ? 'btn-success' : ''}}" id="select_pending_item"> Pending Item's</button>
                        </a>
                        <i class="fa fa-long-arrow-right"  style="padding-top:10px; padding-left: 6px;"></i>
                  </div>
                  <div class="col-md-1 prl-0 " style="padding-left: 6px;" >
                     <a href="/admin/sale/update/otc/{{request()->route('id')}}">
                     <button class="btn {{ Request::segment(4)=='otc' ? 'btn-success' : ''}}" id="select_otc"> OTC</button>
                     </a>
                  </div>
            </div>
         </div>