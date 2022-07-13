@extends($layout)

@section('title', __('JP Honda'))

@section('js')
<script>
   $(document).ready(function() {
     //fetchRecords();
   
});

$( "#search_product" ).keyup(function() {
     var search = this.value;
     search_value(search);
});

  $(document).on('click', '.pagination a', function(event){
    event.preventDefault(); 
    var page = $(this).attr('href').split('page=')[1];
    fetch_data(page);
   });

 

function fetch_data(page) {
 
    $.ajax({
       url:"/store/fetch_data?page="+page, 
     success:function(data)
     {
      $('#table_data').html(data);
     }
    });
 }

function search_value(search){

    $.ajax({
     url:"/store/search_value",
     data: {  
     search:search,  
    }, 
     success:function(data)
     {
      $('#table_data').html(data);
     }
    });
 }

</script>
@endsection
@section('css')
        <!--inputs css-->
<link rel="stylesheet" href="/css/style.css">
 <link rel="stylesheet" href="/front/css/jquery-ui.css">
 <link rel="stylesheet" href="/front/css/product.css">

@endsection
@section('main_section')
    <section class="content">
      <div class="container-fluid breadcrumb-area pt-120">
              <div class="breadcrumb-content text-center contactus_header">
                    <h2>{{__('front/front.all_store')}}</h2>
                </div>
            </div>
      <div class="shop-wrapper fluid-padding-2 pt-10">
          <div class="container-fluid">
              <div class="row">
                <div class="col-sm-4"></div>
                 <div class="col-sm-3 ">
                      <div class="sidebar-search">
                          <form action="#">
                              <input type="text" id="search_product" placeholder="Search...">
                              <button><i class="ti-search"></i></button>
                          </form>
                      </div>
                  </div>
              </div>
              <div class="row">
                  	<div id="table_data">
                        @include('front.store_data')
                      </div>
              </div>
          </div>
        </div>
      </section>
@endsection