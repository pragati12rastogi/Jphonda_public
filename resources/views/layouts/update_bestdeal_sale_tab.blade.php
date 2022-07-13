         <div class="">
            <div class="row " style="margin-top:10px;margin-bottom:20px;" >
                  <div class="col-md-1  " style="padding-left: 0px;">
                     <a href="/admin/bestdeal/update/sale">
                        <button class="btn {{ Request::segment(5)=='' ? 'btn-success' : ''}}" disabled id="select_book">Sale</button>
                     </a>
                     <i class="fa fa-long-arrow-right" style="padding-top:10px; padding-left:3px;"></i>
                  </div>
                  <div class="col-md-2"  style="padding-left:0px;">
                        <a href="/admin/bestdeal/update/sale/document/{{request()->route('id')}}">
                        <button class="btn {{ Request::segment(5)=='document' ? 'btn-success' : ''}}" id="select_hi-rise">Documents Page</button>
                        </a>
                        <i class="fa fa-long-arrow-right"  style="padding-top:10px; padding-left: 3px;"></i>
                  </div>
                  <div class="col-md-2"  style="padding-left: 25px;">
                        <a href="/admin/bestdeal/update/sale/hirise/{{request()->route('id')}}">
                        <button class="btn {{ Request::segment(5)=='hirise' ? 'btn-success' : ''}}" id="select_hi-rise">Hi-Rise</button>
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
                        <a href="/admin/bestdeal/update/sale/rto/{{request()->route('id')}}">
                           <button class="btn {{ Request::segment(5)=='rto' ? 'btn-success' : ''}}" id="select_rto"> RTO</button>
                        </a>
                        <i class="fa fa-long-arrow-right"  style="padding-top:10px; padding-left: 6px;"></i>
                     </div>
                     <div class="col-md-2  " style="padding-left: 6px;" >
                        <a href="/admin/bestdeal/update/sale/pending/item/{{request()->route('id')}}">
                           <button class="btn {{ Request::segment(5)=='pending' ? 'btn-success' : ''}}" id="select_pending_item"> Pending Item's</button>
                        </a>
                        {{-- <i class="fa fa-long-arrow-right"  style="padding-top:10px; padding-left: 6px;"></i> --}}
                     </div>
            </div>
         </div>