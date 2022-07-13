         <div class="">
            <div class="row " style="margin-top:10px;margin-bottom:20px;" >
                  <div class="col-md-2" style="padding-left: 0px;">
                     <a href="/admin/service/create/jobcard/{{request()->route('id')}}">
                        <button class="btn {{ Request::segment(5)== request()->route('id') ? 'btn-success' : ''}}" id="select_screen1">Basic Requirements</button>
                     </a>
                  </div>
                  <div class="col-md-1"><i class="fa fa-long-arrow-right" style="padding-top:10px;"></i></div>
                  <div class="col-md-2 " >
                        <a href="/admin/service/create/jobcard/screen2/{{request()->route('id')}}">
                        <button class="btn {{ Request::segment(5)=='screen2' ? 'btn-success' : ''}}" id="select_screen2">Problems</button>
                        </a>
                  </div>
                  <div class="col-md-1"><i class="fa fa-long-arrow-right" style="padding-top:10px;"></i></div>
                  <div class="col-md-2"  style="padding-left:0px;">
                        <a href="/admin/service/create/jobcard/screen3/{{request()->route('id')}}">
                        <button class="btn {{ Request::segment(5)=='screen3' ? 'btn-success' : ''}}" id="select_screen3">Value Added Services</button>
                        </a>
                  </div>
                  </div>
            </div>