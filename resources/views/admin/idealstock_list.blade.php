@extends($layout)

@section('title', __('factory.ideal_stock'))

@section('breadcrumb')
    <li><a href="#"><i class=""></i>Ideal Stock Summary</a></li>
@endsection
@section('css')
<link rel="stylesheet" href="/css/responsive.bootstrap.css">
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
.table-striped>tbody>tr.white{
  background-color: white;
}

// table td input box
   table td {
    position: relative;
  }
   table td input {
    /* position: absolute; */
    display: block;
    top:0;
    left:0;
    margin: 0;
    height: 100% !important;
    width: 100%;
    border-radius: 0 !important;
    border: none;
    padding: 10px;
    box-sizing: border-box;
  }
</style>    
<style>
  .content{
   padding: 30px;
 }
 .nav1>li>button {
   position: relative;
   display: block;
   padding: 10px 34px;
   background-color: white;
   margin-left: 10px;
 }
 .dataTables_scrollHeadInner .table tr:first-child th:first-child , .dataTables_scrollHeadInner 
 {
     background-color: #ffffff;
     position: sticky;
 }
 .dataTables_scrollHeadInner .table tr:first-child th:first-child
 {
   left: 1px;
 }
 /* .dataTables_scrollHeadInner .table tr:first-child th:nth-child(2)
 {
   left: 97px; 
 } */
 .dataTables_scrollBody
 {
   height: 400px;
 
 }

 .dataTables_scrollBody #table tbody tr td:first-child , .dataTables_scrollBody 
 {
   background-color: #c20302 !important;
   color: #ffffff;
   position: sticky;
   
 }
 .dataTables_scrollBody #table tbody tr td:first-child
 {
   left: 1px;
 }
 /* .dataTables_scrollBody #table tbody tr td:nth-child(2)
 {
   left: 97px; 
 } */



 .clone thead, .clone tfoot{background:transparent;}
 @media (max-width: 768px)  
   {
     
     .content-header>h1 {
       display: inline-block;
     
     }
   }
   @media (max-width: 425px)  
   {
   
     .content-header>h1 {
       display: inline-block;
       
     }
   }
   td.center {
     text-align: center;
   }
   span .red
   {
     color: red;
   }
   th,td{
   text-align:center;
   }

  
</style>
@endsection
@section('js')
<meta name="csrf-token" content="{{ csrf_token() }}" />

<script src="/js/dataTables.responsive.js"></script>
  <script>
    var dataTable;

    var curr_change = {};

    function ajaxUpdateQty(min_qty, ele){
      $('#loader').css('display', 'block');

      var prodId = curr_change.prodId;
      var storeId = curr_change.storeId;
      if(prodId && storeId && min_qty)
      {
        $.ajax({  
                url:"/admin/stock/update/minqty",  
                method:"POST",  
                data:{'_token':CSRF_TOKEN,'prodId':prodId,'storeId':storeId,'min_qty':min_qty},
                success:function(res){ 
                    if(res.trim() == 'success') {
                       $("#success").show().fadeOut(10000);
                       $("#msg-success").text('Successfully Updated');
                        $(ele).attr('disabled',true);
                    }
                    else{
                      $("#error").show().fadeOut(10000);
                       $("#msg-error").text('Some Error Occurred');
                       $(ele).val(curr_change.old_min_qty);
                        $(ele).attr('disabled',true);
                    }
                },
                error:function(error){
                    $("#error").show().fadeOut(10000);
                    $("#msg-error").text(error);
                    $(ele).val(curr_change.old_min_qty);
                    $(ele).attr('disabled',true);
                }  
           }).done(function(){
             curr_change = {};
             $('#loader').css('display', 'none');

           });
      }
    }

    $(document).on('focusout','.idealStockUpdate',function(){
        var newInput = parseInt($(this).val());
        var numbers = /^[0-9]+$/;
        if(newInput.toString().match(numbers))
        {
            if(curr_change.old_min_qty != newInput && newInput >= 0 )
            {
              // do save new entry
              ajaxUpdateQty(newInput,this);
            }
            else{
              // never change
              $(this).val(curr_change.old_min_qty);
              $(this).attr('disabled',true);

            }
        }
        else{
            $(this).val(curr_change.old_min_qty);
            $(this).attr('disabled',true);
        }
    });

    // ideal stock update

    $(document).on('dblclick','.idealStockUpdate',function(){
      $("#success").hide();
      $("#error").hide();

      var row_col_id = $(this).attr('name');
      var str_split = row_col_id.split('_');
      var min_qty = parseInt($(this).val());
      // console.log(row_col_id,str_split,min_qty);
      if(row_col_id && str_split && min_qty >= 0)
      {
        var str_row = str_split[0]; 
        var str_col = str_split[1]; 
        // console.log(str_row,str_col);
        if($(this).is(':disabled') && min_qty >= 0)
        {
            curr_change = {};
            var prodId = str_row.split("-")[1]; 
            var storeId = str_col.split("-")[1]; 
            $(this).removeAttr('disabled');
            $(this).focus();
            // push in arr
            curr_change.prodId = prodId;
            curr_change.storeId = storeId;
            curr_change.old_min_qty = min_qty;
            
        }
      }
    });

    // Data Tables
   
      var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
      // var dataTable;
      var col_data = @php echo json_encode($store); @endphp;
      var col_length = col_data.length;

      var str = [];
      var query = [];
      str.push({"data":"product_name"});
      for(var i = 0 ; i < col_length ; i++)
      {
        var min_qty = col_data[i]['min_qty'];
        var quantity = col_data[i]['quantity'];

        str.push({"data": min_qty,
                  "render": function(data,type,full,meta)
                          {
                              return '<input type="number" disabled class="idealStockUpdate" value="'+data+'">';
                          },
                  "class" : 'storeId-'+col_data[i]['id']+'',
                  "orderable": true});
      }

      dataTable = $('#table').DataTable({
          "scrollX": true,
          "processing": true,
          "serverSide": true,
          "aaSorting": [],
          "responsive": false,
          'createdRow':function(row,data,dataIndex)
          {
              $(row).addClass('white');
              $.each($('td', row), function (colIndex) {
                  var get_class = $(this)[0].classList[0];  // col_id
                  // For example, adding data-* attributes to the cell
                  $(this).attr('col_id', get_class);
                  $(this).find('input').attr('name',data.prodIdClass+'_'+get_class);
                  $(this).find('input').attr('id',data.prodIdClass+'_'+get_class);
            });
          },
          "rowId":"prodIdClass",
          "ajax": {
            "url":"/admin/stock/idealstock_list/api",
            "type": "POST",
            "data": {'_token': CSRF_TOKEN, 'col_data':col_data},
          },
          "columns": str
          
        });
  
  </script>
@endsection

@section('main_section')

    <section class="content">

        <div id="app">
                   
        <div class="box box-primary">
            <!-- /.box-header -->
            <div class="box-header with-border">
<!--                    <h3 class="box-title">{{__('customer.mytitle')}} </h3>-->
                  <div class="alert alert-success alert-block" id="success" style="display:none">
                    <button type="button" class="close" data-dismiss="alert">×</button>	
                        <strong id="msg-success"></strong>
                  </div>
                  <div class="alert alert-danger alert-block" id="error" style="display:none">
                    <button type="button" class="close" data-dismiss="alert">×</button>	
                        <strong id="msg-error"></strong>
                  </div>
                </div>  
                <div class="box-body main-table">
                   @include('admin.flash-message')
                    @yield('content')
                  <table id="table" class="table table-bordered table-striped cell-border">
                    <thead>
                      <tr>
                        <th rowspan="1">Product Name</th>
                        {{-- <th>Available Quantity</th> --}}
                        @foreach($store as $store1)
                          <th>{{$store1['name']}}</th>
                        @endforeach
                        
                        {{-- <th>Required Quantity</th> --}}
                    </tr>
                    
                    </thead>
                    <tbody>
                      
                    </tbody>
               
                  </table>
           
                </div>
                <!-- /.box-body -->
              </div>
               <!-- Modal -->
              
        <!-- /.box -->
      </section>
@endsection