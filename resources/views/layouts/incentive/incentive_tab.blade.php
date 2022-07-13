         <div class="">
            <div class="row" style="margin-top:10px;margin-bottom:20px;" >
                  <div class="col-md-1">
                     <a href="/admin/incentive/program/sale">
                        <button class="btn btn-group-justified {{ (Request::segment(4)== 'sale' || Request::segment(4)== '*') ? 'btn-success' : 'btn-default'}}">Sale</button>
                     </a>
                  </div>
                  
                  <div class="col-md-1 "  style="padding-left:0px;">
                        <a href="/admin/incentive/program/bestdeal">
                        <button class="btn  {{ Request::segment(4)=='bestdeal' ? 'btn-success' : 'btn-default'}}">Best Deal</button>
                        </a>
                  </div>
                  <div class="col-md-1"  style="padding-left:0px;">
                        <a href="/admin/incentive/program/otcsale">
                        <button class="btn btn-group-justified {{ Request::segment(4)=='otcsale' ? 'btn-success' : 'btn-default'}}">OTC Sale</button>
                        </a>
                  </div>
                  <div class="col-md-1"  style="padding-left:0px;">
                        <a href="/admin/incentive/program/service">
                        <button class="btn btn-group-justified {{ Request::segment(4)=='service' ? 'btn-success' : 'btn-default'}}">Service</button>
                        </a>
                  </div>
                  <div class="col-md-1"  style="padding-left:0px;">
                        <a href="/admin/incentive/program/insurance">
                        <button class="btn  {{ Request::segment(4)=='insurance' ? 'btn-success' : 'btn-default'}}">Insurance</button>
                        </a>
                  </div>
                  <div class="col-md-1"  >
                        <a href="/admin/incentive/program/hsrp">
                        <button class="btn btn-group-justified {{ Request::segment(4)=='hsrp' ? 'btn-success' : 'btn-default'}}">HSRP</button>
                        </a>
                  </div>
            </div>
         </div>