         <div class="">
            <div class="row " style="margin-top:10px;margin-bottom:20px;" >
                  <div class="col-md-2" style="padding-left: 10px;">
                     <a href="/admin/hsrp/request">
                        <button class="btn {{ Request::segment(3)== 'request' ? 'btn-success' : ''}}" id="insurance" {{ Request::segment(4)=='detail' || 'verify'  ? 'disabled' : ''}}>HSRP Request</button>
                     </a>
                    
                  </div>
                  <div class="col-md-1"> <i class="fa fa-long-arrow-right" style="padding-top:10px;"></i></div>
                  <div class="col-md-2"  style="padding-left:0px;">
                        <a href="/admin/hsrp/challan/details/{{request()->route('id')}}">
                        <button class="btn {{ Request::segment(4)=='details' ? 'btn-success' : ''}}" id="select_screen2" {{ Request::segment(3)=='request' || Request::segment(4)=='verify' ? 'disabled' : ''}}>HSRP Challan Details</button>
                        </a>
                  </div>
                   <div class="col-md-1"> <i class="fa fa-long-arrow-right" style="padding-top:10px;"></i></div>
                  <div class="col-md-2"  style="padding-left:0px;">
                        <a href="/admin/hsrp/status/verify/{{request()->route('id')}}">
                        <button class="btn {{ Request::segment(4)=='verify' ? 'btn-success' : ''}}" id="select_screen3" {{ Request::segment(3)=='request' || Request::segment(4)=='details' ? 'disabled' : ''}}>Status verify</button>
                        </a>
                  </div>
                  </div>
            </div>