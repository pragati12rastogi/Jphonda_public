@extends($layout)

@section('title', __('product.title_view'))

@section('user', Auth::user()->name)

@section('breadcrumb')

    <li><a href="#"><i class=""></i>{{__('product.title_view')}}</a></li> 
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
  tr.selected{
      background-color:#3fb618 ;
  } 

  .table-striped > tbody > tr.selected:nth-child(2n+1) > td {
   background-color: #3fb618;
}
tr.redClass{
    background-color: #ffc0cb !important;
}
  
  
</style>
<link rel="stylesheet" href="/css/responsive.bootstrap.css">    
@endsection
@section('js')
<script src="/js/dataTables.responsive.js"></script>
<script src="/js/dataTables.select.min.js"></script>

  <script>
    var dataTable;
    var selected = [];
    
    function getdata(lrn){

        //var table = $('#table').DataTable();
        // dataTable.on('select', function ( e, dt, type, indexes ) {
        //     if ( type === 'row' ) {
        //         var data = dataTable.rows( indexes ).data().pluck( 'id' );
        //         console.log(data);
        //         console.log( dataTable[ type ]( indexes ).nodes().to$().addClass( 'custom-selected' ));
        //         // do something with the ID of the selected items
        //     }
        // } );

        if(dataTable)
            dataTable.destroy();
          
        dataTable = $('#table').DataTable({
          "processing": true,
          "serverSide": true,
          "aaSorting":[],
          "responsive": true,
          'createdRow':function(row,data,dataIndex)
          {
              $(row).addClass('rowClass');
              //$(row).addClass('redClass');
              $(row).attr('id',data.frame);
              if(data.status == 'damage')
              {
                $(row).addClass('redClass');
              }
              else if(data.status == 'repairing')
              {
                $(row).addClass('redClass');
              }
          },
          "ajax": "/admin/product/list/api/"+lrn,
          "drawCallback": function( settings ) {
            // var api = this.api();
            // // Output the data for the visible rows to the browser's console
            // console.log( api.rows( {page:'current'} ).data() );
            if(selected.length>0)
            {
              for(var i = 0 ; i< selected.length ; i++)
              {
                var prod_id = selected[i].prod_id;
                var frame = selected[i].frame;
                $(document).find($('#'+frame)).addClass('selected');
              }
               $("#sub-btn").prop("disabled",false);
              
            }
            else{
               $("#sub-btn").prop("disabled",true);
            }
            $("input[name='claimData']").val(JSON.stringify(selected));    
          },
          "columns": [
           
                {"data":"refer"}, 
                {"data":"model_cat"}, 
                {"data":"model_name"},
                {"data":"model_var"}, 
                {"data":"color"}, 
                {"data":"basic_price"}, 
                {"data":"store_name"},  
                {"data":"frame"},
                {"data":"engine"},
                {
                    "targets": [ -1 ],
                    "data":"id", "render": function(data,type,full,meta)
                    {
                        // return "<a href='/admin/product/edit/"+data+"'><button class='btn btn-success btn-xs'> {{__('product.product_edit')}} </button></a> &nbsp;"
                        //"<a href='/purchase/order/view/"+data+"' target='_blank'><button class='btn btn-primary btn-xs'> {{__('unload.unload_list_view')}} </button></a> &nbsp;" //+ 
                        //'<a href="#" id="del" onclick = "del(this,'+data+')" ><button class="btn btn-primary btn-xs"> {{__("product.product_del")}} </button></a>' 
                        return '';
                    },
                    "orderable": false
                }
            ],
            
        
          });

        // dataTable.on('select.dt', function() {
        //     var array = [];
        //     dataTable.rows('.selected').every(function(rowIdx) {
        //         console.log(rowIdx);
        //         console.log(dataTable.row(rowIdx).data());
        //         array.push(dataTable.row(rowIdx).data());
        //     }) 
        //     //console.log(dataTable.data());
        //     console.log(array);
        // })

      
        // dataTable.on( 'deselect', function ( e, dt, type, indexes ) {
        //     //table[ type ]( indexes ).nodes().to$().removeClass( 'custom-selected' );
        //     if ( type === 'row' ) {
        //         //selected =[];
        //         var prod_det_id1 = dataTable.rows( indexes ).data().pluck( 'id' );
        //         var frame = dataTable.rows( indexes ).data().pluck( 'frame' );
        //         console.log(selected.length);
        //         for(var i = 0 ; i < selected.length ; i++)
        //         {
        //             console.log(selected[i].frame , frame[0] ,'&&', selected[i].prod_det_id , prod_det_id1[0]);
        //             if(selected[i].frame == frame[0] && selected[i].prod_det_id == prod_det_id1[0])
        //             {
        //                 selected.splice(i,1);
        //             }
        //             console.log(i);
        //         }
        //         //console.log(selected);  
        //     }
        //     console.log(selected);
        // } );
          
//           $(document).ready(function() {
//     var table = $('#table').DataTable();
 
//     $('#table tbody').on( 'click','tr', function () {
//         $(this).toggleClass('selected');
//         console.log( table.rows('.selected').data().toArray());
//     } );
 
//     $('#button').click( function () {
//         alert( table.rows('.selected').data().length +' row(s) selected' );
//     } );
// } );

    }
    if(dataTable){
        dataTable.destroy;
    }
    else{
        dataTable = $('#table').DataTable();
    }
        dataTable.on( 'click','td', function (  ) {

        var index = dataTable.row($(this).closest('tr'))[0][0];
        // console.log(index);
        var index1 = dataTable.row($(this).closest('tr'));
        var type = $(document).find($('.rowClass')[index]);
        // console.log('cell',$(type).attr('role'));
        var index2 = dataTable.cell($(this).closest('td'))[0][0].column;
        //console.log('cell1',index2);
        var role = $(type).attr('role');
        var classname = $(type).hasClass('selected');
        var redclassname = $(type).hasClass('redClass');

        if(index2 != 0 && index2 != 10 && role == 'row' && !redclassname)
        {
            if(classname)
            {
                //console.log('coll deselect',index);
                deselectRow(role,index);
            }
            else{
                selectRow(role,index);
            }
        }
        if(selected.length > 0)
        {
            $("#sub-btn").prop("disabled",false);
        }
        else{
            $("#sub-btn").prop("disabled",true);
        }
        //console.log(selected);
        $("input[name='claimData']").val(JSON.stringify(selected));    
        } );
    function deselectRow(type,indexes)
    {
        var prod_id1 = dataTable.rows( indexes ).data().pluck( 'id' );
        var frame = dataTable.rows( indexes ).data().pluck( 'frame' );
        // console.log(selected.length);
       // var flag = 0;
        for(var i = 0 ; i < selected.length ; i++)
        {
            // console.log(selected[i].frame , frame[0] ,'&&', selected[i].prod_det_id , prod_det_id1[0]);
            if(selected[i].frame == frame[0] && selected[i].prod_id == prod_id1[0])
            {
                selected.splice(i,1);
                dataTable[ type ]( indexes ).nodes().to$().removeClass( 'selected' );
        //        flag = 1;
            }
            // console.log(i);
        }
        //return flag;
    }
    function selectRow(type,index)
    {
        var prod_det_id = dataTable.rows( index ).data().pluck( 'detail_id' );
        var prod_id = dataTable.rows( index ).data().pluck( 'id' );
        var frame = dataTable.rows( index ).data().pluck( 'frame' );
        var refer = dataTable.rows( index ).data().pluck( 'refer' );
        // console.log(prod_det_id[0],frame[0]);
        selected.push({
            'prod_id': prod_id[0],
            'prod_det_id' : prod_det_id[0],
            'frame': frame[0],
            'lrn' : refer[0],
        });
        dataTable[ type ]( index ).nodes().to$().addClass( 'selected' );


            
            // console.log(dataTable[ type ]( indexes ).nodes().to$().removeClass( 'selected' ));
           // console.log(dataTable[ type ]( indexes ).nodes().to$()[0].firstChild.className);
            // console.log(type);
            // if ( type == 'row' ) {
            //    // selected =[];
            //     // console.log(dataTable.rows('.selected'));
            //     // dataTable.rows('.selected').every(function(index){
            //     //      console.log(index);
            //     // var prod_det_id = dataTable.rows( inde ).data().pluck( 'id' );
            //     // var frame = dataTable.rows( inde ).data().pluck( 'frame' );
            //     // console.log(prod_det_id[0],frame[0]);
            //     //     selected.push({
            //     //     'prod_det_id': prod_det_id[0],
            //     //     'frame': frame[0]
            //     //     });
            //      });
            //     console.log(selected);
            // }
    }
  
    if(dataTable){
        dataTable.destroy;
    }
    else{
        dataTable = $('#table').DataTable();
    }
      function del(e,id)
      {
        var con=confirm("Are you sure you want to delete that ");
        if(con){
          $.ajax({ 
              url: '/admin/product/del',
              data: {"product_id":id},
              type: 'GET',
              success: function(data)
              { 
                if(data == 'false')
                {
                  alert('Data Deleted Unsccessfully');
                }
                else{
                  alert('Data Deleted Successfully');
                  dataTable.draw();
                }
              },
              failure: function (msg) {
                    console.log(msg);
              }
          });
        }
      }
    

    $(document).ready(function() {
        getdata(0);
       
    });
    $("#lrnChange").on('change',function(){
        //console.log($(this).val());
        getdata($(this).val());
        selected =[];
        $("input[name='claimData']").val(JSON.stringify(selected));
        //console.log(selected);

    });

  </script>

  <script>

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
                <form action="/admin/product/list/claim" method="POST">
                    @csrf
                    <input type="hidden" name="claimData" value="">
                    <div class="row">
                      <div class="col-md-2"></div>
                      <div class="col-md-7">
                            <label>{{__('LRN')}} <sup>*</sup></label>
                            <select name="loadRefNumber" id="lrnChange" class="input-css select2" style="width:100%">
                                <option value="default" selected >Select Load Reference Number</option>
                                @if($lrn = $lrn->toArray())
                                    @foreach ($lrn as $item)
                                    <option value="{{$item['id']}}">{{$item['load_referenace_number']}}</option>   
                                    @endforeach
                                @endif
                            </select>
                      </div>
                      <div class="col-md-2"></div>
                      <div class="col-md-1">
                          <a href="#" id="btn" ><button id="sub-btn" type="submit" disabled class="btn btn-success btn-xs"> Claim </button></a>
                      </div>
                    </div>
                    
                  </form>
                    <table id="table" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                            
                                <th>{{__('product.refer')}}</th>
                                <th>{{__('product.model_cat')}}</th>
                                <th>{{__('product.model_name')}}</th>
                                <th>{{__('product.model_var')}}</th>
                                <th>{{__('product.color_code')}}</th>
                                <th>{{__('product.basic_price')}}</th>
                                <th>{{__('product.store_name')}}</th>
                                <th>{{__('product.frame')}}</th>
                                <th>{{__('product.engine')}}</th>


                                <th>{{__('product.action')}}</th> 
                            </tr>
                        </thead>
                        <tbody>
                          
                        </tbody>               
                    </table>
                    <!-- /.box-body -->
            </div>
        </div>
    
    </section>
@endsection