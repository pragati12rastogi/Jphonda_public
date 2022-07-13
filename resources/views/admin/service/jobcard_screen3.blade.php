@extends($layout)
@section('title', __('Create Job Card'))
@section('breadcrumb')
<li><a href="#"><i class=""></i></a></li>
@endsection
@section('css')
<link rel="stylesheet" href="/css/responsive.bootstrap.css">
<link rel="stylesheet" href="/css/bootstrap-duration-picker.css">
<style>
   tr.dangerClass{
   background-color: #f8d7da !important;
   }
   tr.successClass{
   background-color: #d4edda !important;
   }
   tr.worningClass{
   background-color: #fff3cd !important;
   }
   .wash option {
    padding: 10px;
   }
</style>
@endsection
@section('js')
<script src="/js/dataTables.responsive.js"></script>
<script src="/js/bootstrap-duration-picker.js"></script>

<script src="https://momentjs.com/downloads/moment-with-locales.js"></script>
<script>
   // $("#only_wash").hide();
    $('#js-msg-error').hide();
     $('#js-msg-success').hide();
  
  $(document).ready(function(){
        $("input[name='purchase_amc']").change(function(){
         
            var purchase_amc = $("input[name='purchase_amc']:checked").val();
        
            if(purchase_amc){
                if (purchase_amc == 'yes') {
                  $("#amc_product").show();
                  $("#service_type").show();
                }
              }else{
                  $("#amc_product").hide();
                  $("#service_type").hide();
                }
        });
    });


      $("#amc_prod").change(function(){

        var amc_prod_id = $("#amc_prod").val();
           $.ajax({
             url:'/admin/service/amc/product',
             method:'GET',
             data: {'amc_prod_id':amc_prod_id},
             success:function(res){
                if (res) {
                  $("#custom_price").html('<div class="col-md-12 margin-bottom" id="" "><div class="col-md-6"><label for="sub_type">AMC Amount(between '+res.min_price+' and '+res.max_price+')</label><input type="number" name="amc_amount" class="input-css" min="'+res.min_price+'" max="'+res.max_price+'"/></div></div>'
                    );
                }
             },
             error:function(error){
                alert("something wen't wrong");
             }
          });
        });

   $(document).ready(function(){
     $(".service_vas").click(function(){
       var id = $(this).attr('id');
       var id_arr = id.split('-');
       index = id_arr[1];
       var vas = $("#service_vas-"+index).val();
       if (vas == 'PICK N DROP') {
         if ($("#service_vas-"+index).is(':checked')) {
          $("#picdrop").css('display','block');
         }else{
          $("#picdrop").css('display','none');
        }
      }
            
     })
 });



</script>
@endsection
@section('main_section')
<section class="content">
   <div id="app">

         @include('admin.flash-message')
         @yield('content')

         <div class="box-header with-border">
           @include('layouts.jobcard_tab')
          <div class="row">
         <div class="alert alert-danger" id="js-msg-error" style="display: none;">
         </div>
         <div class="alert alert-success" id="js-msg-success" style="display: none;">
         </div>
       </div>

            <div class="box box-primary">
                <div class="box-header">

                </div>  
               
               <form  action="/admin/service/create/jobcard/screen3/{{$jobCardId}}" method="post" id="form-id">
                    @csrf
                  <div class="row">
                  
                     <div class="col-md-6 margin-bottom">
                        <div class="col-md-12">
                          <input type="hidden" name="jobcard_id" id="jobcard_id" value="{{$jobCardId}}">
                          <input type="hidden" name="amc" id="amc" value="{{$amc}}">
                        </div>
                        <input type="hidden" name="vehicle_type" id="vehicle_type" value="{{$jobCard->vehicle_type}}">
                          
                         </div>
                         @if($jobCard['job_card_type'] == 'Free' ||  $jobCard['job_card_type'] == 'Paid')
                          <div class="col-md-12 margin-bottom"  id="purchase_amc" @if($amc == 0) style="display: block;" @else style="display: none;" @endif>
                             <label for="purchase_amc">Purchase AMC</label>
                           <input type="checkbox" name="purchase_amc" value="yes"> Yes
                          </div>
                          @endif

                          <div class="col-md-12 margin-bottom" id="amc_product" style="display: none;">
                            <div class="col-md-6">
                            <label for="repeat">AMC Product</label>
                            <select class="select2" name="amc_prod" id="amc_prod" style="width: 100%;">
                              <option selected="" disabled="">Select job card</option>
                              @if(isset($amc_product))
                              @foreach($amc_product as $item)
                              <option value="{{$item->id}}">{{$item->name}}</option>
                              @endforeach
                            </select>
                            @endif
                          </div>
                          </div>

                            <div id="custom_price"></div>

                            <div class="col-md-12 margin-bottom" id="service_type" style="display: none;">
                              <div class="col-md-6">
                              <label for="repeat">Type</label>
                              <select class="input-css select2" name="amc_type" id="amc_type" style="width: 100%;">
                                <option selected="" disabled="">Select type</option>
                                <option value="same_service" @if($same_type['value'] != 'allowed') disabled @endif>Same Service</option>
                                <option value="next_service">Next Service</option>
                              </select>
                            </div>
                            </div>
                           @if($jobCard['job_card_type'] == 'Paid')
                          <div class="col-md-12 margin-bottom" id="purchase_ew" >
                             <label for="purchase_ew">Purchase EW</label>
                           <input type="checkbox" name="purchase_ew" value="yes"> Yes
                          </div>
                          @endif
                          @if($jobCard['job_card_type'] == 'HJC')
                          <div class="col-md-12 margin-bottom"  id="purchase_hjc" >
                             <label for="purchase_hjc">Purchase HJC</label>
                           <input type="checkbox" name="purchase_hjc" value="yes"> Yes
                          </div>
                          @endif
                          @if($ServiceCharge != '')
                          <div class="col-md-12 margin-bottom">
                            <div class="row">
                              <div class="col-md-6">
                                <label for="service_vas">Value added </label>
                                @php $i = 1; @endphp 
                                @foreach($ServiceCharge as $list)
                                @php $price =  json_decode($list->details); @endphp
                                <div class="row">
                                  <div class="col-md-6"><input type="checkbox" class="service_vas" id="service_vas-{{$i}}" name="service_vas[]" value="{{$list->value}}"> {{$list->value}} </div>
                                  <div class="col-md-6">{{$price->price}}</div>
                                </div>

                                @php $i++; @endphp
                                @endforeach
                                <br>
                                <div id="picdrop" style="display: none;">
                                    <input type="checkbox" name="pick" value="pick"> Pick  &nbsp;
                                    <input type="checkbox" name="drop" value="drop"> Drop</div>
                              </div>
                            </div>
                          </div>
                          @endif
                        <div class="col-md-12 margin">
                           <div class="col-md-6">
                               <button type="submit" class="btn btn-success">Submit</button>
                           </div>
                           <br><br>    
                        </div>
                     </div>
                     
                  </div>

                </form>
            </div>
         </div>
   </div>
   
   <!-- Modal -->
   <!-- /.box -->
</section>
@endsection