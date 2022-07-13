@extends($layout)
@section('title', __('Bay Allocation'))
@section('breadcrumb')
<li><a href="#"><i class=""></i></a></li>
@endsection
@section('css')
<link rel="stylesheet" href="/css/responsive.bootstrap.css">
<style>
.selectable td {
                
                font-size: 1em;
                text-align: center;
                border: 1px solid #CCC;
                background: #F6F6F6;
                font-weight: bold;
                color: #1C94C4;
                outline: none;
                text-align: center;
            }
  .selectable .ui-select {
                background: #F39814;
                color: white;
            }

  .selectable td.ui-selected {
      background: #0f8df5;
      color: white;
  }

</style>
@endsection
@section('js')
<meta name="csrf-token" content="{{ csrf_token() }}" />

{{-- <link href = "https://code.jquery.com/ui/1.10.4/themes/ui-lightness/jquery-ui.css" --}}
         rel = "stylesheet">
{{-- <script src = "https://code.jquery.com/jquery-1.10.2.js"></script> --}}
{{-- <script src = "https://code.jquery.com/ui/1.10.4/jquery-ui.js"></script> --}}
<script>
    $(document).ready(function() {


        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
        var dataTable;
        var col_data = @php echo json_encode($store); @endphp;
        var col_length = col_data.length;

        getdata();

        function getdata(){
          
          var str = [];
          str.push({"data":"product_name"});
          str.push({"data":"color_code"});
          for(var i = 0 ; i < col_length ; i++)
          {
            var avail = col_data[i]['avail'];
            var damage = col_data[i]['damage'];
            var ant = col_data[i]['alloc'];
            var sale = col_data[i]['sale'];
            var booking = col_data[i]['booking'];
            str.push({"data": avail,
                      "class":"center green ",
                      "orderable": false});
            str.push({"data": damage,
                      "class":"center red",
                      "orderable": false});
            str.push({"data": ant,
                      "class":"center yellow",
                      "orderable": false});
            str.push({"data": sale,
                      "class":"center light-info",
                      "orderable": false});
            str.push({"data": booking,
                      "class":"center light-red",
                      "orderable": false});
          }
          
            if(dataTable)
                dataTable.destroy();
          
            dataTable = $('#table').DataTable({
              "scrollX": true,
              "processing": true,
              "serverSide": true,
              "aaSorting":[],
              "responsive": false,
              "ajax": {
              "url":"/admin/stock/list/api",
              "type": "POST",
              "data": {'_token': CSRF_TOKEN},
              },
              "columns": str
                
              
            });
            
        }


      // $(document).on('click','.onclicktd',function(){
      //     console.log($(this).attr('td_id'));
      // });
      
        var clicking = false;
        var move_pointer_arr = [];
        var assign = false;
        var lastIndexId = '';
        function checkpre_filled(el){
          if($(el).hasClass('ui-selected')){
              assign = false;
          }
            else{
              assign = true;
          }
        }

        $('.onclicktd').mousedown(function(){
            move_pointer_arr = [];
            // console.log(move_pointer_arr);
            // return;
            checkpre_filled($(this));
            if(assign === false) return;
            
            //initilize
            clicking = true;
            $(document).find('td.ui-select').removeClass('ui-select');
            var indexVal = $(this).attr('index');
            lastIndexId = indexVal;
            move_pointer_arr['fix_end_time'] = '';
            move_pointer_arr['max_col_no'] = 0;
            var data_count = parseInt($(this).attr('data-count'));

            //
            move_pointer_arr['row'] = {'row_id':$(this).parent().attr('tr_id')};
            move_pointer_arr['row'].start_td_id = indexVal;
            move_pointer_arr['row'].all_move_td_id = [];
            move_pointer_arr['row'].all_move_td_id.push(indexVal);
            move_pointer_arr['row'].exact_move_td_id = cal_exact_td(move_pointer_arr['row'],indexVal,data_count);

            // console.log(move_pointer_arr);
        });

        $('.onclicktd').mouseup(function() {
          if(assign === false) return;
          
          var indexVal = $(this).attr('index');
          if((move_pointer_arr['max_col_no'] != 0))
          {
            if(parseInt(indexVal.split('-')[2]) > move_pointer_arr['max_col_no'])
            {
              indexVal = move_pointer_arr['fix_end_time'];
            }
          } 
          generate_unique_id(move_pointer_arr['row']);
          move_pointer_arr['row'].end_td_id = indexVal;
          console.log(move_pointer_arr);
          // move_pointer_arr = [];

        });
        $(document).mouseup(function(){
            clicking = false;
            assign = false;
            $(document).find('td.ui-select').removeClass('ui-select').addClass('ui-selected');
        });
        
        $('.onclicktd').mousemove(function(){
            if(clicking === false || assign === false) return;
            
            var index_row_val = $(this).parent().attr('tr_id');
            var indexVal = $(this).attr('index');
            var data_count = parseInt($(this).attr('data-count'));
            // console.log('data_count',data_count);
            
            
            if($(this).hasClass('ui-selected') && move_pointer_arr['max_col_no'] == 0)
            {
              // move_pointer_arr['max_col_no'] = parseInt(indexVal.split('-')[2])-1;
              move_pointer_arr['max_col_no'] = data_count-1;
              move_pointer_arr['fix_end_time'] = $(this).attr('row-id')+'_td-'+move_pointer_arr['max_col_no'];
              // console.log('max_col_no',max_col_no);
            }
            if(lastIndexId != indexVal)
            {
              lastIndexId = indexVal;
              if(index_row_val == move_pointer_arr['row'].row_id)
              {
                // if('all_move_td_id' in move_pointer_arr['row'] )
                // {
                  if((move_pointer_arr['max_col_no'] != 0) ? ((data_count <= move_pointer_arr['max_col_no']) ? true : false ) : true )
                  {
                    // console.log('data_count',data_count);
                    if($.inArray(indexVal,move_pointer_arr['row'].all_move_td_id) == -1)
                    {
                      move_pointer_arr['row'].all_move_td_id.push(indexVal);
                      move_pointer_arr['row'].exact_move_td_id = cal_exact_td(move_pointer_arr['row'],indexVal,data_count);
                      // td_coluring(move_pointer_arr);
                    }
                    else{
                      move_pointer_arr['row'].exact_move_td_id = cal_exact_td(move_pointer_arr['row'],indexVal,data_count);
                    }
                  }
                // }
                // else{
                //   move_pointer_arr['row'].all_move_td_id = [];
                //   move_pointer_arr['row'].all_move_td_id.push(indexVal);
                // }
                // console.log('this td',$(this).attr('index'));
              }
            }
            else{
              // console.log('pre_select and ');
            }
            
        });

        // function td_coluring(arr)
        // {
        //   var class_arr = arr['row'].all_move_td_id;
        //   console.log(class_arr); 
        //   $.each(class_arr,function(key,val){
        //     $('#'+val).addClass('ui-select');
        //   });
        // }

        function cal_exact_td(arr,lastMove,max_int){
          var cal_arr  = [];
          // max_int = parseInt(max_int);
          // var max_int = parseInt(lastMove.split('-')[2]);
          // console.log('max',max_int);
          arr = draw_all_td_id(arr,lastMove,max_int);
          // console.log('arr',arr);
          
          $.each(arr.all_move_td_id,function(key,val){
            var current_int = parseInt(val.split('-')[2]);
            // console.log('current_int',current_int);
            if(!($('#'+val).hasClass('ui-selected')))
            {
              if(move_pointer_arr['max_col_no'] != 0){
                max_int = move_pointer_arr['max_col_no'];
              }
              // console.log(current_int, max_int);

              if(current_int <= max_int ){
                cal_arr.push(val);
                // console.log(cal_arr);
                
                $('#'+val).addClass('ui-select');
              }
              else{
                $('#'+val).removeClass('ui-select');
              }
            }
          });
          // console.log(cal_arr);
          
          return cal_arr;
          
        }
        
        function draw_all_td_id(arr,lastMove,max_int,max_section){
          var min_int = parseInt(arr.start_td_id.split('_')[2]);
          var min_section = parseInt(arr.start_td_id.split('-')[2]);
          var row_id = (arr.start_td_id).split('_')[0];
          // console.log(min_int,max_int);
          for(var i = min_int ; i <= max_int ; i++)
          {
            var id = row_id+'_td-'+i;
           
            if($('#'+id).hasClass('ui-selected')){
              move_pointer_arr['max_col_no'] = parseInt(id.split('-')[2])-1;
              move_pointer_arr['fix_end_time'] = row_id+'_td-'+move_pointer_arr['max_col_no'];
              return move_pointer_arr['row'];
            }
            else{
              move_pointer_arr['max_col_no'] = 0;
              move_pointer_arr['fix_end_time'] = '';
            }
            // $('#'+id).addClass('ui-select');
            if($.inArray(id,move_pointer_arr['row'].all_move_td_id) == -1)
            {
              var id = row_id+'_td-'+i;
            
              if($('#'+id).hasClass('ui-selected')){
                move_pointer_arr['max_col_no'] = parseInt(id.split('-')[2])-1;
                move_pointer_arr['fix_end_time'] = row_id+'_td-'+move_pointer_arr['max_col_no'];
                return move_pointer_arr['row'];
              }
              // $('#'+id).addClass('ui-select');
              if($.inArray(id,move_pointer_arr['row'].all_move_td_id) == -1)
              {
                move_pointer_arr['row'].all_move_td_id.push(id);
              }
            }
          }
          return move_pointer_arr['row'];
        }

        function generate_unique_id(arr){
          arr_len = (arr.exact_move_td_id).length;
          str =  arr.exact_move_td_id[0]+'_'+arr_len;
          var all_id = "#"+(arr.exact_move_td_id).join(',#');
          $(all_id).addClass(str);

          // console.log(all_id);
          // $.each(arr.exact_move_td_id,function(key,val){
          //   $('#'+val).addClass(str);
          // });
        }
        
 });

                

</script>
@endsection
@section('main_section')
<section class="content">
   <div id="app">

         @include('admin.flash-message')
         @yield('content')
         <div class="box-header with-border">

            <div class="box box-primary">
                <div class="box-header">
                </div>  
                <form  action="#" method="post">
                    @csrf
                  {{-- <div class="container">
                    <div class="col-container">
                        <div class="col" style="background:orange">
                          <h2>Column 1</h2>
                        </div>
                        <div class="col" style="background:yellow">
                          <h2>Column 2</h2>
                        </div>
                        <div class="col" style="background:orange">
                          <h2>Column 3</h2>
                        </div>
                    </div>
                    <div class="col-container">
                        <div class="col" style="background:orange">
                          <h2>Column 1</h2>
                        </div>
                        <div class="col" style="background:yellow">
                          <h2>Column 2</h2>
                        </div>
                        <div class="col" style="background:orange">
                          <h2>Column 3</h2>
                        </div>
                    </div>
                  </div> --}}
                  {{-- <div class="card-shadow" style="overflow-x:scroll; height: 100%; overflow-y:scroll;"> --}}
                    <div class="card-shadow" >
                    <table id="table" class="table table-bordered table-striped cell-border">
                      <thead>
                          <tr>
                            <th></th>
                            <?php
                              $time_count = 1;
                              // $time_arr = array();
                            ?>
                            @while($start < $end)
                              <th class="" colspan="{{$colspan}}" start="{{$start}}">{{date('H:i',$start)}}</th>
                              <?php 
                                  // $time_arr[$time_count] = $start;
                                  $start = strtotime($interval,$start); $time_count++;  
                              ?>
                            @endwhile
                          </tr>
                      </thead>
                      <tbody>
                      </tbody>  
                    </table> 
                  </div>
                </form>
            </div>
         </div>
   </div>
   
   <!-- Modal -->
   <!-- /.box -->
</section>
@endsection