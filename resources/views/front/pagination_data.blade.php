                              <div class="grid-list-product-wrapper tab-content">
                                <div id="new-product" class="product-grid product-view tab-pane active">

                                    @if($data1->count()>0)       
                                     <div class="row">                          
                                      @foreach($data1 as $row)
                                     
                                        <div class="product-width col-md-6 col-xl-4 col-lg-6">
                                            <div class="product-wrapper mb-35">
                                                 <div class="product-img image_list">
                                                    <a href="#">

                                @if($row->image != "" || $row->image != null)
                                     @if (file_exists(public_path().'/upload/product/resized/'.$row->image )) 
                                        <img src="{{ asset('upload/product/resized/')}}/{{$row->image}}"  id="product_{{$row->product_ids}}" alt="">
                                     @else
                                        <img src="/front/img/product/product-default.jpg" id="product_{{$row->product_ids}}"  alt="">
                                     @endif

                                @elseif($row->product_notdefault_image != "" || $row->product_notdefault_image != null)
                                    
                                     @if (file_exists(public_path().'/upload/product/resized/'.$row->product_notdefault_image )) 
                                        <img src="{{ asset('upload/product/resized/')}}/{{$row->product_notdefault_image}}" id="product_{{$row->product_ids}}" alt="">
                                     @else
                                        <img src="/front/img/product/product-default.jpg" id="product_{{$row->product_ids}}"  alt="">
                                     @endif

                                @else
                                <img src="/front/img/product/product-default.jpg"  id="product_{{$row->product_ids}}"  alt="">
                                @endif

                                                    </a>
                                                    <div class="product-item-dec">
                                                       <!--  <ul>
                                                            <li>{{ $row->model_category }}</li>
                                                        </ul>
                                                       @if($row->quantity>0) 
                                                       <span id="available">Available</span>
                                                       @endif -->
                                                    </div><div class="row">
                                                    </div> 
                                                    <div class="product-action"style="cursor: pointer;">
                                                        <!-- <a class="action-plus-2 p-action-none" title="Add To Cart" href="#">
                                                            <i class=" ti-shopping-cart"></i>
                                                        </a>
                                                        <a class="action-cart-2" title="Wishlist" href="#">
                                                            <i class=" ti-heart"></i>
                                                        </a> -->
                                                         <a class="action-reload details_model"  data-id="{{$row->product_ids}}" data-model="{{ $row->model_name }}" data-variant="{{ $row->model_variant }}" data-color="{{ $row->color_code }}" data-frame="{{ $row->frame }}"  data-engine_number="{{ $row-> engine_number}}" data-default_image="{{$row->default_image}}"  title="Quick View" onclick="function_api_call(this);">
                                                        <i class=" ti-zoom-in"></i></a>

                                                    </div>
                                                    <div class="product-content-wrapper">
                                                        <div class="product-title-spreed">
                                                            <h4>{{ $row->model_name }}
                                                            @if($row->quantity>0) 
                                                               <span id="available">(Available)</span>
                                                            @endif
                                                            </h4>
                                                            <span>{{ $row->model_variant }}</span>
                                                        </div>
                                                        <div class="product-price">
                                                            <span>{{ $row->color_code }}</span>
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
                                </div>
                              </div>
<div class="text-center">                              
    {!! $data1->links() !!}
</div>
