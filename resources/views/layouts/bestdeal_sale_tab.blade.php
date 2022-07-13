         <div class="">
            <div class="row " style="margin-top:10px;margin-bottom:20px;" >
                  <div class="col-md-1  " style="padding-left: 0px;">
                     <a href="/admin/bestdeal/sale">
                        <button class="btn {{ Request::segment(4)=='' ? 'btn-success' : ''}}" disabled id="select_book">Sale</button>
                     </a>
                     <i class="fa fa-long-arrow-right" style="padding-top:10px; padding-left:3px;"></i>
                  </div>
                  <div class="col-md-2 "  style="padding-left:18px;">
                        <a href="/admin/bestdeal/sale/pay/{{request()->route('id')}}">
                        <button class="btn {{ Request::segment(4)=='pay' ? 'btn-success' : ''}}" id="select_hi-rise">Payment</button>
                        </a>
                        <i class="fa fa-long-arrow-right"  style="padding-top:10px; padding-left: px;"></i>
                  </div>
                  <div class="col-md-2"  style="padding-left:0px;">
                        <a href="/admin/bestdeal/sale/document/{{request()->route('id')}}">
                        <button class="btn {{ Request::segment(4)=='document' ? 'btn-success' : ''}}" id="select_hi-rise">Documents Page</button>
                        </a>
                        <i class="fa fa-long-arrow-right"  style="padding-top:10px; padding-left: 3px;"></i>
                  </div>
                  <div class="col-md-2"  style="padding-left: 25px;">
                        <a href="/admin/bestdeal/sale/hirise/{{request()->route('id')}}">
                        <button class="btn {{ Request::segment(4)=='hirise' ? 'btn-success' : ''}}" id="select_hi-rise">Hi-Rise</button>
                        </a>
                        <i class="fa fa-long-arrow-right"  style="padding-top:10px; padding-left: 3px;"></i>
                  </div>
                  
                     {{-- <div class="col-md-2 " style="padding-left: 6px;" >
                        <a href="/admin/bestdeal/sale/insurance/{{request()->route('id')}}">
                        <button class="btn {{ Request::segment(4)=='insurance' ? 'btn-success' : ''}}" id="select_insur"> Insurance</button>
                        </a>
                        <i class="fa fa-long-arrow-right"  style="padding-top:10px; padding-left: 6px;"></i>
                     </div> --}}
                     
                     <div class="col-md-1 " style="padding-left: 6px;" >
                        <a href="/admin/bestdeal/sale/rto/{{request()->route('id')}}">
                           <button class="btn {{ Request::segment(4)=='rto' ? 'btn-success' : ''}}" id="select_rto"> RTO</button>
                        </a>
                        <i class="fa fa-long-arrow-right"  style="padding-top:10px; padding-left: 6px;"></i>
                     </div>
                     <div class="col-md-2  " style="padding-left: 6px;" >
                        <a href="/admin/bestdeal/sale/pending/item/{{request()->route('id')}}">
                           <button class="btn {{ Request::segment(4)=='pending' ? 'btn-success' : ''}}" id="select_pending_item"> Pending Item's</button>
                        </a>
                        {{-- <i class="fa fa-long-arrow-right"  style="padding-top:10px; padding-left: 6px;"></i> --}}
                     </div>
            </div>
         </div>