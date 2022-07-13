  @if($data->count()>0)  
  <div class="row"> 
    @foreach($data as $val)
    <div class="product-width col-md-4 col-xl-4 col-lg-4">
                <div class="product-wrapper mb-35">
                    <div class="product-img">
                        <a href="#">
                           @if($val->image != "" || $val->image != null)
                              @if (file_exists(public_path().'/upload/bestdeal/'.$val->image )) 
                                <img src="{{ asset('upload/bestdeal')}}/{{$val->image}}" alt="" class="main-image">
                             @else
                                <img src="/front/img/product/product-default.jpg" alt="" class="main-image">
                               @endif
                          @else
                              <img src="/front/img/product/product-default.jpg" alt="" class="main-image">
                          @endif
                        </a>
                        <div class="product-item-dec">
                            <ul>
                                <li>Frame</li>
                                <li>{{$val->frame}}</li>
                                <li>Reg No.</li>
                                <li>{{$val->register_no}}</li>
                            </ul>
                        </div>
                        <div class="product-action price">
                            <a class="modal_getprice btn-style cr-btn"  data-id="{{$val->id}}" data-selling_price="{{$val->selling_price}}" onclick="function_getprice(this);">Get Price</a>
                               <a class="details_model"  data-id="{{$val->id}}" title="Quick View" onclick="function_api_call(this);"><i class=" ti-zoom-in"></i></a>
                        </div>
                        <div class="product-content-wrapper" >
                            <div class="product-title-spreed">
                                <h4>{{$val->model}}</h4>
                                <span>{{$val->variant}}</span><br>
                                <span>RC Date of Sale : {{$val->dos}}</span><br>
                                 <span>Address : {{$val->address}}</span>

                            </div>
                            <div class="product-price">
                                <span>{{$val->color}}</span>
                            </div>
                          </div>
                    </div>
                </div>
            </div>
          @endforeach
          @else
            <span>No data available </span>
          @endif
  </div> 
<div class="text-center">                              
    {!! $data->links() !!}
</div>