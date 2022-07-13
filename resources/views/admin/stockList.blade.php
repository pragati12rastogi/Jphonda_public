@extends($layout)

@section('title', __('stocklist.title'))

@section('breadcrumb')

    <li><a href="#"><i class=""></i>{{__('stocklist.title')}}</a></li> 
@endsection
@section('css')
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
  
</style>
<link rel="stylesheet" href="/css/responsive.bootstrap.css">    
@endsection
@section('js')
<script src="/js/dataTables.responsive.js"></script>
  <script>

    var selected=[];
    //var newselected = {};
    var dataTable;
    var col_data = @php echo json_encode($number_of_store); @endphp;
    //console.log(col_data);
    var col_length = col_data.length;
    function getdata(){
    //  var table = $('#table').DataTable();
      function getMatch(stock_id,prev_color)
      {
        console.log(selected.length);
         if(selected.length > 0)
         {
          var flag = 0;
          console.log(stock_id);
          console.log("getmatch selected",selected);
          for(var i = 0 ; i< selected.length ; i++)
          {
            var row = selected[i]; 
            console.log('row val'+i,row);
            str = row.warehouse.split("_");
            console.log("str arr",str);
            console.log(str[1]+'=='+stock_id);
            if(str[1] == stock_id)
            {
              selected.splice(i,1);
              var req_quan = $("#req_quan_"+stock_id).prop("disabled",true);
              $("#req_quan_"+stock_id).val("");
              $("#req_quan_"+stock_id).removeAttr("min");
              $("#req_quan_"+stock_id).removeAttr("max");
              $("#"+row.warehouse).parent().css("background-color",prev_color);
                  // $("input[name='"+name+"']").parent().css("background-color",prev_color);
              $("#"+row.warehouse).parent().removeClass("selected");
              flag = 1;
              break;
            }
          }
         return "insert";
        }
        else{
          return "insert";
        }
      }

      $('#table tbody').on( 'click', 'td', function () {
        var rowData = dataTable.cell( this ).data();
        //dataTable.cell( ':eq(0)', null, {page: 'current'} ).select();
       // console.log(dataTable.cells('selected'));
      
          // console.log('cell value',rowData);
            var name = $(this).children("input").attr('name');
            var id = $(this).children("input").attr('id');
            // console.log("id",this);

            if(name && id)
            {
              var selectClass = $("#"+id).parent().attr('class');
              // console.log('select class',selectClass);
            //  console.log(name);
              var str = name.split("_");
              var strid = id.split("_");
              //var selected_id = id.split("_")[id.split("_").length-1];
              if(str[0] == 'store' && str[1] == 'select' && strid[1])
              {
                var stock_id = str[2];
                var prev_color = $(this).siblings().css("background-color");

                if (selectClass != 'selected') 
                {
                  var findRowValue = getMatch(stock_id,prev_color);
                 // console.log('findRowValue',findRowValue);
                  if(findRowValue == 'insert')
                  {
                  //  console.log('selected arr at insert',selected);
                    if(rowData == 0)
                    {
                      selected.push({"qty":rowData,"warehouse":id,"req_quan":0});
                      $("#req_quan_"+stock_id).attr("min",0);
                      $("#req_quan_"+stock_id).val(0);
                    }
                    else{
                      selected.push({"qty":rowData,"warehouse":id,"req_quan":1});
                      $("#req_quan_"+stock_id).val(1);
                      $("#req_quan_"+stock_id).attr("min",1);
                    }
                    
                    $("#req_quan_"+stock_id).attr("max",rowData);
                    var req_quan = $("#req_quan_"+stock_id).prop("disabled",false);
                  //  $("#"+id).parent().css("background-color",prev_color);
                    $(this).css("background-color","#3fb618");
                  //  $("#"+id).parent().removeClass("selected");
                    $(this).addClass("selected");
                    //save input hidden field
                    //saveData();
                  }

                }else 
                {
                  for(var i = 0 ; i< selected.length ; i++)
                  {
                    if(selected[i].warehouse == id )
                    {
                      selected.splice(i,1);
                      var req_quan = $("#req_quan_"+stock_id).prop("disabled",true);
                      $("#req_quan_"+stock_id).val("");
                      $("#req_quan_"+stock_id).removeAttr("min");
                      $("#req_quan_"+stock_id).removeAttr("max");
                      $(this).css("background-color",prev_color);
                      // $("input[name='"+name+"']").parent().css("background-color",prev_color);
                      $(this).removeClass("selected");
                      
                    }
                  }                  
                }
                if(selected.length < 1)
                {
                  $("#sub-btn").prop("disabled",true);
                }
                else{
                  $("#sub-btn").prop("disabled",false);
                }
              $("input[name='bookData']").val(JSON.stringify(selected));
               console.log('final data',selected);
                //$(this).toggleClass('selected');
              }
            }
        });
        
  
        if(dataTable)
            dataTable.destroy();
       
        dataTable = $('#table').DataTable({
          "processing": true,
          "serverSide": true,
          "aaSorting":[],
          "responsive": true,
          "ajax": {
            url:"/admin/stock/list/api"},
          "drawCallback": function( settings ) {
            // var api = this.api();
            // // Output the data for the visible rows to the browser's console
            // console.log( api.rows( {page:'current'} ).data() );
            if(selected.length>0)
            {
              for(var i = 0 ; i< selected.length ; i++)
              {
                var stock_id = selected[i].warehouse.split("_");
                $("#"+selected[i].warehouse).parent().css('background-color','#3fb618');
                $("#"+selected[i].warehouse).parent().addClass("selected");
                $("#req_quan_"+stock_id[1]).prop("disabled",false);
                if(selected[i].qty == 0)
                {
                  $("#req_quan_"+stock_id[1]).attr("min",0);
                  $("#req_quan_"+stock_id[1]).val(0);
                }
                else{
                    $("#req_quan_"+stock_id[1]).attr("min",1);
                    $("#req_quan_"+stock_id[1]).val(1);
                }
                    
                $("#req_quan_"+stock_id[1]).attr("max",selected[i].qty);
              }
              $("#sub-btn").prop("disabled",false);
              
            }
            else{
              $("#sub-btn").prop("disabled",true);
            }

          },
          "columns": [
                {"data":"product_name"}, 
                {"data":"color_code"},
                {
                  "data":"Jphonda",
                  "render":function(data,type,full,meta)
                  {
                    return "<input type='radio' class='form-control input-css' id ='Jphonda_"+full.id+"' name='store_select_"+full.id+"' style='opacity:0;' >"+data+"</input>";
                  }
                },
                {
                  "data":"Jhonda",
                  "render":function(data,type,full,meta)
                  {

                    return "<input type='radio' class='form-control input-css' id = 'Jhonda_"+full.id+"' name='store_select_"+full.id+"' style='opacity:0;'>"+data+"</input>";
                  }
                },
                {
                  "data":"id","render":function(data,type,full,meta)
                  {
                    return "<input type='number' class='form-control input-css' id='req_quan_"+data+"' onchange='changeRequiedQuantity(this)' name='req_quan_"+data+"' disabled>";
                  }
                },  
                // {
                //     "targets": [ -1 ],
                //     "data":"id", "render": function(data,type,full,meta)
                //     {
                //       // if data == pqr
                //          //"<a href='/admin/stock/edit/"+data+"'><button class='btn btn-success btn-xs'> {{__('stocklist.stock_edit')}} </button></a> &nbsp;"+
                //         //"<a href='/purchase/order/view/"+data+"' target='_blank'><button class='btn btn-primary btn-xs'> {{__('unload.unload_list_view')}} </button></a> &nbsp;" //+ 
                //         return '<a href="/admin/stock/book/'+data+'"><button class="btn btn-success btn-xs"> Book </button></a>' 
                //         ;
                //     },
                //     "orderable": false
                // }
            ],
            "columnDefs": [
              
                { "orderable": false, "targets": 4 }
            
            ]
            
          
        });
        // .on( 'drawCallback', function (e, settings, data) {
        // alert( "data.myCustomValue ");
        // } );
        // $('#table').on( 'page', function () {
        //   var info = dataTable.page.info();
        //   alert( 'Showing page: '+info.page+' of '+info.pages );
        // } );

    }
   
   function changeRequiedQuantity(el)
   {
    //  console.log($(el).attr('id'));
    //  console.log($(el).val());
     var id = $(el).attr('id');
     var val = $(el).val();
     for(var i = 0 ; i< selected.length ; i++)
    {
      console.log(selected[i].warehouse.split("_")[1] , id.split("_")[2]);
      if(selected[i].warehouse.split("_")[1] == id.split("_")[2] )
      {
        selected[i].req_quan = parseInt(val);
        break;
      }
    }
    $("input[name='bookData']").val(JSON.stringify(selected));    
    // console.log(selected);

   }


    $(document).ready(function() {
      
        getdata();
        
    });
    

// $("#checkjs").click(function(){
//   if($(this).is(":checked")){
//     $(this).css("color","green");
//   }
//   else if($(this). is(":not(:checked)")){
//     $(this).css("color","black");
//   }
// });


  </script>
@endsection

@section('main_section')
    <section class="content">
        <div id="app">
            @include('admin.flash-message')
            @yield('content')
            {{-- @section('titlebutton')
                <a href="{{url('/createdispatch')}}"><button class="btn btn-primary">{{__('goods_dispatch.title')}}</button></a>
                <a href="" ><button class="btn btn-primary "  >{{__('goods_dispatch.goods_dispatch_import_btn')}}</button></a>
                <a href="" ><button class="btn btn-primary "  >{{__('goods_dispatch.goods_dispatch_export_btn')}}</button></a>
            @endsection --}}
            
        </div>
    <!-- Default box -->
        <div class="box">
                <!-- /.box-header -->
            <div class="box-body">
              <form action="/admin/sotck/list/update" method="POST">
                @csrf
                <input type="hidden" name="bookData" value="">
                <div class="row">
                  <div class="col-md-7"></div>
                  <div class="col-md-4 " >
                    <input type="text"  name ="schedule_date" class=" input-css form-control datepicker">
                  </div>
                  <div class="col-md-1">
                      <a href="#" id="btn" ><button id="sub-btn" type="submit" disabled class="btn btn-success btn-xs"> Book </button></a>

                  </div>
                </div>
                
              </form> 
                    <table id="table" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                
                                <th>Product Name</th>
                                <th>Color Code</th>
                                {{-- <th>Available Quantity</th> --}}
                                @foreach($number_of_store as $store)
                                  <th>{{$store['name']}}</th>
                                @endforeach
                                <th>Required Quantity</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>               
                    </table>
                    <!-- /.box-body -->
            </div>
        </div>
        <!-- /.box -->
    </section>
@endsection


{{--  $('#table tbody').on( 'click', 'td', function () {
        var rowData = dataTable.cell( this ).data();
        //dataTable.cell( ':eq(0)', null, {page: 'current'} ).select();
       // console.log(dataTable.cells('selected'));
        console.log(rowData);
            var name = $(this).children("input").attr('name');
            var id = $(this).children("input").attr('id');
            if(name && id)
            {
              var selectClass = $(this).attr('class');
            //  console.log(selectClass);
            //  console.log(name);
              var str = name.split("_");
              var strid = id.split("_");
              if(str[0] == 'store' && str[1] == 'select' && strid[1])
              {
                var stock_id = str[2];
              //  console.log(stock_id);
              //  var index = $.inArray(name,selected);
                console.log(name in selected);
                var prev_color = $(this).siblings().css("background-color");
                var findRowValue = getMatch();
                if (!selected.hasOwnProperty(name) || selectClass != 'selected') 
                {
                  selected[name] = rowData;
                  var req_quan = $("#req_quan_"+stock_id).prop("disabled",false);
                  $("#req_quan_"+stock_id).val(rowData);
                  $("input[name='"+name+"']").parent().css("background-color",prev_color);
                  $(this).css("background-color","#3fb618");
                  $("input[name='"+name+"']").parent().removeClass("selected");
                  $(this).addClass("selected");
                  //save input hidden field
                  //saveData();

                }else 
                {
                  delete selected[name];
                  var req_quan = $("#req_quan_"+stock_id).prop("disabled",true);
                  $("#req_quan_"+stock_id).val("");
                  $(this).css("background-color",prev_color);
                  // $("input[name='"+name+"']").parent().css("background-color",prev_color);
                  $(this).removeClass("selected");

                }
                console.log(selected);
                //$(this).toggleClass('selected');
              }
            }
        });
        $('#btn').click(function (){
            var dataArr = [];
            var rows = $('td.selected');
            var rowData = dataTable.rows( rows ).data();
            $.each($(rowData),function(key,value){
              dataArr.push(value["name"]);    
            });
            console.log(dataArr);
        });
  
        if(dataTable)
            dataTable.destroy();
       
        dataTable = $('#table').DataTable({
          "processing": true,
          "serverSide": true,
          "aaSorting":[],
          "responsive": true,
          "ajax": {
            url:"/admin/stock/list/api"},
          "columns": [
                {"data":"product_name"}, 
                {"data":"color_code"},
                {
                  "data":"Jphonda",
                  "render":function(data,type,full,meta)
                  {
                    
                      return "<input type='radio' id ='Jphonda_"+full.id+"' name='store_select_"+full.id+"' style='opacity:0;' >"+data+"</input>";
                    //  $("#Jphonda"+full.id).parent().css('background-color','#3fb618');
                  
                  }
                },
                {
                  "data":"Jhonda",
                  "render":function(data,type,full,meta)
                  {
                    return "<input type='radio' id = 'Jhonda_"+full.id+"' name='store_select_"+full.id+"' style='opacity:0;'>"+data+"</input>";
                  }
                },
                {
                  "data":"id","render":function(data,type,full,meta)
                  {
                    return "<input type='number' id='req_quan_"+data+"' name='req_quan_"+data+"' disabled>";
                  }
                },  
                // {
                //     "targets": [ -1 ],
                //     "data":"id", "render": function(data,type,full,meta)
                //     {
                //       // if data == pqr
                //          //"<a href='/admin/stock/edit/"+data+"'><button class='btn btn-success btn-xs'> {{__('stockList.stock_edit')}} </button></a> &nbsp;"+
                //         //"<a href='/purchase/order/view/"+data+"' target='_blank'><button class='btn btn-primary btn-xs'> {{__('unload.unload_list_view')}} </button></a> &nbsp;" //+ 
                //         return '<a href="/admin/stock/book/'+data+'"><button class="btn btn-success btn-xs"> Book </button></a>' 
                //         ;
                //     },
                //     "orderable": false
                // }
            ],
            // "columnDefs": [
              
            //     { "orderable": false, "targets": 6 }
            
            // ]
          
        });
    } --}}