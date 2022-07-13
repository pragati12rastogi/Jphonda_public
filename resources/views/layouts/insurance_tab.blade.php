         <div class="">
            <div class="row " style="margin-top:10px;margin-bottom:20px;" >
                  <div class="col-md-2" style="padding-left: 0px;">
                     <a href="/admin/insurance/renewal">
                        <button class="btn {{ Request::segment(3)== 'renewal' ? 'btn-success' : ''}}" id="insurance" {{ Request::segment(3)=='payment' || 'order' || 'breakin' ? 'disabled' : ''}}>Insurance</button>
                     </a>
                     <i class="fa fa-long-arrow-right" style="padding-top:10px; padding-left:3px;"></i>
                  </div>
                  <div class="col-md-2 "  style="padding-left:18px;">
                        <a href="/admin/insurance/payment/{{request()->route('id')}}">
                        <button class="btn {{ Request::segment(3)=='payment' ? 'btn-success' : ''}}" id="select_screen2" {{ Request::segment(3)=='renewal' ? 'disabled' : ''}}>Payment</button>
                        </a>
                        <i class="fa fa-long-arrow-right"  style="padding-top:10px; padding-left: 30px;"></i>
                  </div>
                  <div class="col-md-2"  style="padding-left:0px;">
                        <a href="/admin/insurance/order/{{request()->route('id')}}">
                        <button class="btn {{ Request::segment(3)=='order' ? 'btn-success' : ''}}" id="select_screen3" {{ Request::segment(3)=='renewal' ? 'disabled' : ''}}>Order</button>
                        </a>
                        <i class="fa fa-long-arrow-right"  style="padding-top:10px; padding-left: 30px;"></i>
                  </div>
                  <div class="col-md-2"  style="padding-left:0px;">
                        <a href="/admin/insurance/breakin/{{request()->route('id')}}">
                        <button class="btn {{ Request::segment(3)=='breakin' ? 'btn-success' : ''}}" id="select_screen3" {{ Request::segment(3)=='renewal' ? 'disabled' : ''}}>BreakIn</button>
                        </a>
                  </div>
                  </div>
            </div>