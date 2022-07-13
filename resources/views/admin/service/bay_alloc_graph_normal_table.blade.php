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
                background: #c2c9bd;
                font-weight: bold;
                color: #1C94C4;
                outline: none;
                text-align: center;
                opacity: 0.7;

            }
  .selectable .ui-select {
                background: #F39814;
                color: white;
            }

  .selectable td.ui-selected {
      background: #0f8df5;
      color: white;
  }
  .selectable td.disabled {
      /* background: red; */
      opacity: 0.2;
      color: white;
  }
  .selectable td.thead {
      background: white;
      color: black;
  }
  .selectable td.end-time-td{
    border-right-color: floralwhite;
    border-right-width: unset;
  }
  .popover-close{
    /* margin-left: 20px; */
    background: border-box;
  }
  .fa-edit{
    color:red;
    display: none;
  }
  td:hover .fa-edit{
    display: block;
  }
  .fa-trash{
    color:red;
    display: none;
  }
  td:hover .fa-trash{
    display: block;
  }
  /* .delete-time{position: absolute;
    width: 10px;} */
    .wickedpicker {
      z-index: 99999;
    }
</style>
@endsection
@section('js')
{{-- <link href = "https://code.jquery.com/ui/1.10.4/themes/ui-lightness/jquery-ui.css" --}}
         rel = "stylesheet">
{{-- <script src = "https://code.jquery.com/jquery-1.10.2.js"></script> --}}
{{-- <script src = "https://code.jquery.com/ui/1.10.4/jquery-ui.js"></script> --}}

<script>
  
    $(document).ready(function() {

      $('.timepicker').wickedpicker({
            twentyFour:true
        });
      // $(document).on('click','.onclicktd',function(){
      //     console.log($(this).attr('td_id'));
      // });
      
        var clicking = false;
        var move_pointer_arr = [];
        var assign = false;
        var lastIndexId = '';
        function checkpre_filled(el){
          if($(el).hasClass('ui-selected') || $(el).hasClass('disabled')){
              assign = false;
          }
            else{
              assign = true;
          }
        }
        function check_job_card_val()
        {
          if($('#job_card_id').val() != '')
          {
            assign = ((assign == true)?true:false);
            return true;
          }
          else{
            $('#msg-error').text('Please Select Tag Number');
            $('#error').fadeIn(2500).fadeOut(2500);
            assign = false;
            return false;
          }
        }

        $('.onclicktd').mousedown(function(){
            move_pointer_arr = [];
            // console.log(move_pointer_arr);
            // return;
            checkpre_filled($(this));
            if(assign === false) return;
            if(check_job_card_val() === false || assign === false) return;
            
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
          // checkpre_filled($(this));
          // return check_job_card_val();
          // console.log($(this)[0].localName);
          var where_mouse_up = $(this)[0].localName;  // only mouse up on td
          var which_row_mouse_up = $(this).parent().attr('tr_id');
          if(!($(this).hasClass('disabled')))
          {
            if(where_mouse_up == 'td' && which_row_mouse_up == move_pointer_arr['row'].row_id)
            {
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
              // console.log(move_pointer_arr);
              // move_pointer_arr = [];
              call_confirmation(move_pointer_arr);
            }
            else{
              deselect();
            }
          }
          else{
            deselect();
          }
        });
        $(document).mouseup(function(){
          // return check_job_card_val();
          // checkpre_filled($(this));
            clicking = false;
            assign = false;
            $(document).find('td.ui-select').removeClass('ui-select').addClass('ui-selected');
        });
        function deselect(){

          var arr = move_pointer_arr['row'];
          arr_len = (arr.exact_move_td_id).length;
          str =  arr.exact_move_td_id[0]+'_'+arr_len;
          var all_id = "#"+(arr.exact_move_td_id).join(',#');
          $(all_id).removeClass(str);

            clicking = false;
            assign = false;
            $(document).find('td.ui-select').removeClass('ui-select')
        }
        
        $('.onclicktd').mousemove(function(){
          // checkpre_filled($(this));
            if(clicking === false || assign === false) return;
            
            if($(this).hasClass('disabled')) return;

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
                    var old_start_index = parseInt((move_pointer_arr['row'].start_td_id).split('-')[2]);
                    if(old_start_index > data_count)
                    {
                      move_pointer_arr['row'].start_td_id = indexVal;
                    }
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
        
        function draw_all_td_id(arr,lastMove,max_int){
          var min_int = parseInt(arr.start_td_id.split('-')[2]);
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
              move_pointer_arr['row'].all_move_td_id.push(id);
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
        function pageRedirect() 
        {
          window.location.replace($("#store_id :selected").val());
          // window.location.reload();
        }  
        function call_confirmation(move_pointer_arr)
        {
          var bay_name = $('#'+move_pointer_arr['row'].start_td_id).parent().attr('bay-name');
          var bay_id = ($('#'+move_pointer_arr['row'].start_td_id).parent().attr('tr_id')).split('-')[1];
          var start_time = $('#'+move_pointer_arr['row'].start_td_id).attr('start-time');
          var end_time = $('#'+move_pointer_arr['row'].end_td_id).attr('end-time');
          var job_card_id = $('#job_card_id :selected').val();
            // console.log('job_card_id',job_card_id);
          if(bay_name.trim() != '' && bay_id.trim() != '' && start_time.trim() != '' && end_time.trim() != '' && job_card_id != '')
          {
            var conf = confirm("Are U Sure, Allocate Bay :- "+bay_name+" From :- "+start_time+" To :- "+end_time);
            if(conf)
            {
              $('#start_time').val(start_time);
              $('#end_time').val(end_time);
              $('#bay_id').val(bay_id);
              var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
              // console.log(CSRF_TOKEN);
              $.ajax({
                url:'/admin/bay/allocation/manual',
                method:'POST',
                data: {'_token': CSRF_TOKEN, 'start_time':start_time, 'end_time':end_time, 'bay_id':bay_id, 'job_card_id':job_card_id},
                success:function(res){
                  $('#msg-success').text('Successfully Allocated');
                  $('#success').fadeIn(500).delay(3000).fadeOut(2000);
                  
                  setTimeout(pageRedirect(), 6000);
                      // console.log('res',res);
                },
                error:function(error){
                  $('#error').fadeIn(2500).delay(6000).fadeOut(2500);
                  $('#msg-error').text(error);
                    // alert(error);
                    deselect();
                }
              });

              
              // console.log('yes');
            }
            else{
              // console.log('no');
              deselect();
            }
          }
          else{
            if(job_card_id == '')
            {
              // alert('Please Select Tag Number');
              $('#error').fadeIn(2500).delay(3000).fadeOut(2500);
              $('#msg-error').text('Please Select Tag Number');
            }else{
              $('#error').fadeIn(2500).delay(3000).fadeOut(2500);
              $('#msg-error').text("something wen't wrong. Try Again");
              // alert("something wen't wrong. Try Again");
            }
            deselect();
          }
        }

        // delete time -------------------

        $('.delete-time').on('click',function(){

          // if(!($(this).parent().hasClass('disabled')))
          // {
            // $('#exampleModalCenter').modal("show");

            var get_same_class = $(this).parent().attr('same-class');  
            var get_same_class = $(this).parent().attr('same-class');  
                // console.log(get_same_class);
            var job_card_id = parseInt(get_same_class.split('_')[2]);
            if(job_card_id != '' || job_card_id != 'undefined')
            {
              var conf = confirm("Are U Sure, Delete Allocate Bay");
              if(conf)
              {
                
                var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
                    // console.log(CSRF_TOKEN);
                    $.ajax({
                      url:'/admin/bay/allocation/manual/delete',
                      method:'POST',
                      data: {'_token': CSRF_TOKEN, 'job_card_id':job_card_id},
                      success:function(res){
                        $('#msg-success').text('Successfully Deleted');
                        $('#success').fadeIn(500).delay(3000).fadeOut(2000);
                        
                        setTimeout(pageRedirect(), 6000);
                            // console.log('res',res);
                      },
                      error:function(error){
                        $('#error').fadeIn(2500).delay(6000).fadeOut(2500);
                        $('#msg-error').text(error);
                          // alert(error);
                          deselect();
                      }
                    });
              }
            }
            else{
              alert("Something wen't wrong");
            }
          // }
        });

        function cal_start_end_time(get_same_class)
        {
          // console.log(get_same_class);
          var min_time = 0;
          var max_time = 0;
          var i = 0;
          $('.'+get_same_class).each(function(){
            if(i == 0)
            {
              min_time = $(this).attr('start-time');
              max_time = $(this).attr('end-time');
              i++;
            }
            else{
              max_time = $(this).attr('end-time');
            }
          });
          var arr = [min_time,max_time];
          return arr;
        }

        $(document).on('click','.action-time',function(){
          // console.log('action',$(this).parent().hasClass('disabled'));
          if(!($(this).parent().hasClass('disabled')))
          {
            // var get_same_class = $(this).parent().attr('same-class');  
              var get_same_class = $(this).parent().attr('same-class');  
              var job_card_id = parseInt(get_same_class.split('_')[2]);
              if(job_card_id != '' || job_card_id != 'undefined')
              {
                var get_start_end_time = cal_start_end_time(get_same_class);

                $("#delete_bay_start_time").val(get_start_end_time[0]);
                $("#delete_bay_end_time").val(get_start_end_time[1]);
                $("#pre_bay_start_time").val(get_start_end_time[0]);
                $("#pre_bay_end_time").val(get_start_end_time[1]);

                $("#this_job_card_id").val(job_card_id);
                $("#this_job_card_id").attr('same_class',get_same_class);
                $('#bayModalCenter').modal("show");
              }
          }
        });
      // $('.action-time').on('click',function(){
        
      // });

        $("#btnUpdate").on('click',function(){
          var job_card_id = parseInt($("#this_job_card_id").val());
          var action = $(".update_bay:checked").val();
          var class_name = $('#this_job_card_id').attr('same_class');
          // console.log(action);
          if(action == 'delete')
          {
            delete_pre_allocation(job_card_id,class_name);
          }
          else if(action == 'edit')
          {
            // $("#js-msg-verror").hide();
            var new_start_time = $('#edit_bay_start_time').val();
            var new_end_time = $('#edit_bay_end_time').val();
            edit_pre_allocation(job_card_id,class_name,new_start_time,new_end_time);
          }
          // var res = 
          // console.log(res);
        });
        function delete_pre_allocation(job_card_id,class_name)
        {
          // return 'success';
          // $("#js-msg-verror").hide();

          if(job_card_id != '' || job_card_id != 'undefined')
            {
              
                var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
                    // console.log(CSRF_TOKEN);
                    $.ajax({
                      url:'/admin/bay/allocation/manual/delete',
                      method:'POST',
                      data: {'_token': CSRF_TOKEN, 'job_card_id':job_card_id},
                      success:function(res){
                        if(res > 0)
                        {
                          $('#msg-success').text('Successfully Deleted');
                          $('#success').fadeIn(500).delay(3000).fadeOut(2000);
                          $('#bayModalCenter').modal("hide");
                          unselect_bay_alloc(class_name);
                        }
                        else{
                          $('#js-msg-verror').fadeIn(2500).delay(6000).fadeOut(2500);
                          $('#js-msg-verror').text(res);
                        }
                        // console.log(res);
                        // return res;
                        // setTimeout(pageRedirect(), 6000);
                            // console.log('res',res);
                      },
                      error:function(error){
                        // return error;
                          $('#js-msg-verror').fadeIn(2500).delay(6000).fadeOut(2500);
                          $('#js-msg-verror').text(error);
                        // $('#error').fadeIn(2500).delay(6000).fadeOut(2500);
                        // $('#msg-error').text(error);
                          // alert(error);
                      }
                    });
              
            }
            else{
              return "Something wen't wrong";
            }
        }
        function edit_pre_allocation(job_card_id,class_name, new_start_time, new_end_time)
        {
         
          // console.log(job_card_id,class_name,new_start_time,new_end_time);
          if(job_card_id != '' || job_card_id != 'undefined')
            {
                var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
                    // console.log(CSRF_TOKEN);
                    $.ajax({
                      url:'/admin/bay/allocation/manual/edit',
                      method:'POST',
                      data: {'_token': CSRF_TOKEN, 'job_card_id':job_card_id,'new_start_time':new_start_time,'new_end_time':new_end_time },
                      success:function(res){
                        // console.log(res);
                        if(res.length == 3)
                        {
                          // var all_id_arr = get_all_id(class_name);
                          var row_ele = $('.'+class_name).parent().find('.onclicktd');
                          unselect_bay_alloc(class_name);
                          // select new allocation
                          get_new_select_id(res,row_ele);

                          $('#msg-success').text('Successfully Updated');
                          $('#success').fadeIn(500).delay(3000).fadeOut(2000);
                          $('#bayModalCenter').modal("hide");
                          // setTimeout($('#exampleModalCenter').modal("hide"), 6000);
                          // select_bay_alloc(all_id_arr,class_name);
                          // setTimeout(pageRedirect(), 6000);
                        }
                        else{
                          $('#js-msg-verror').fadeIn(2500).delay(6000).fadeOut(2500);
                          $('#js-msg-verror').text(res);
                        }
                        // console.log(res);
                        // return res;
                        // setTimeout(pageRedirect(), 6000);
                            // console.log('res',res);
                      },
                      error:function(error){
                        // return error;
                          $('#js-msg-verror').fadeIn(2500).delay(6000).fadeOut(2500);
                          $('#js-msg-verror').text(error);
                        // $('#error').fadeIn(2500).delay(6000).fadeOut(2500);
                        // $('#msg-error').text(error);
                          // alert(error);
                      }
                    });
              
            }
            else{
              return "Something wen't wrong";
            }
        }

        function get_new_select_id(res,row_ele)
        {
          
          // console.log(row_ele);
          res.job_card_id = parseInt(res[0]);
          res.start_time = parseInt(res[1]);
          res.end_time = parseInt(res[2]);
          var arr = [];
          var i = 0, sflag = 1, eflag = 0;
          // console.log(row_ele);
          // console.log('res',res);
          var class_name = '';
          $(row_ele).each(function(){

            var id = $(this).attr('id');
            var start = parseInt($(this).find('.start').attr('id'));
            var end = parseInt($(this).find('.end').attr('id'));
            // console.log(id,start,end);
            if(!$(this).hasClass('disabled'))
            {
              if(res.start_time > start && res.start_time < end && sflag == 1)
              {
                //first time draw
                class_name = class_name = $(this).attr('index')+'_'+res.job_card_id;
                select_bay_alloc(id,class_name);
                eflag = 1;
                sflag = 0;
              }
              
              if(res.end_time > start && res.end_time <= end && eflag == 1){
                  // end time draw
                  select_bay_alloc(id,class_name);
                  $('#'+id).addClass('end-time-td');
                  eflag = 0;
              }
              else if(sflag == 0 && eflag == 1){
                  // draw
                  select_bay_alloc(id,class_name);
              }
              
            }
            // arr[i] = {'start':start,'end':end};
            // i++;
          });
          // console.log(arr);

        }
        
        // not use-----------------------
        function get_all_id(class_name)
        {
          var arr = [];var i = 0;
          $('.'+class_name).each(function(){
            arr[i] = $(this).attr('id');
            i++;
          });
          // var all_id = "#"+(arr).join(',#');
          return arr;

        }
        //---------------------------\
        
        function unselect_bay_alloc(class_name)
        {
          // console.log('un',class_name);
          $('.'+class_name).each(function(){
            $(this).removeAttr('same-class');
            $(this).removeClass('showPopover');
            $(this).removeClass('ui-selected');
            $(this).removeClass('end-time-td');
            $(this).removeClass(class_name);
            $(this).find('.fa').remove();
            // $(this).empty();
          });
        }
        function select_bay_alloc(id,class_name)
        {
          $('#'+id).attr('same-class',class_name);
          $('#'+id).addClass('showPopover');
          $('#'+id).addClass('ui-selected');
          // $('#'+val).removeClass('end-time-td');
          // $('#'+val).addClass('end-time-td');
          $('#'+id).addClass(class_name);
          if(!$("#"+id).find('.fa').hasClass('fa'))
          {
            $('#'+id).append('<i class="fa fa-edit action-time"></i>');
          }
        }

        $(document).on('change','.update_bay',function(){
          var select_bay_action = $(this).val();
            // var get_same_class = $(this).parent().attr('same-class');  
            // var job_card_id = parseInt(get_same_class.split('_')[2]);
          if(select_bay_action == 'delete')
          {
              $('#edit_bay').hide();
              $('#delete_bay').show();
          }
          else if(select_bay_action == 'edit')
          {
            $('#delete_bay').hide();
            $('#edit_bay').show();

          }
        });
        
 });

 $(document).on('click','.rearrange',function(){
    var bay_id = parseInt($(this).attr('id'));
    var conf = confirm('Are You Sure, You Want to Re-Arrange It ?');
    if(conf)
    {
      var url = "{{Request::url()}}";
      var arr = url.split('/');
      var new_url = arr[0]+'//'+arr[2]+'/'+arr[3]+'/'+arr[4]+'/'+arr[5]+'/rearrange?bay_id='+bay_id;
      // console.log(new_url);
      window.location.replace(new_url);
    }
 });
 

  // $(function() {
    
  //   var createPopover = function (item, title) {
                        
  //         var $pop = $(item);
  //         console.log($pop);
  //         $pop.popover({
  //             placement: 'bottom',
  //             title: ( title || '&nbsp;' ) + ' <a class="close" href="#">&times;</a>',
  //             trigger: 'click',
  //             html: true,
  //             container: $('body'),
  //             content: function () {
  //                 return $('#popup-content').html();
  //             }
  //         }).on('shown.bs.popover', function(e) {
  //             //console.log('shown triggered');
  //             // 'aria-describedby' is the id of the current popover
  //             var current_popover = '#' + $(e.target).attr('aria-describedby');
  //             var $cur_pop = $(current_popover);
            
  //             $cur_pop.find('.close').click(function(){
  //                 //console.log('close triggered');
  //                 $pop.popover('hide');
  //             });
            
  //             $cur_pop.find('.OK').click(function(){
  //                 //console.log('OK triggered');
  //                 $pop.popover('hide');
  //             });
  //         });

  //         return $pop;
  //     };

  //   // create popover
  //   createPopover('.showPopover', 'Demo popover!');
  //   // $(document).on('click','.showPopover',function(){
  //   //   var getId = $(this).attr('id');
  //   //   console.log(getId);
  //   //   createPopover('#'+getId,'Bay1');
  //   // });

    
  // });



    // var $elements = $('.showPopover');
    // $elements.each(function () {
    // var $element = $(this);
    // var this_id = $($element).attr('same-class'); 
    // console.log(this_id);
    // $element.popover({
    //     html: true,
    //     // trigger:'click',
    //     title: 'bay-1',
    //     placement: 'bottom',
    //     container: $('body') // This is just so the btn-group doesn't get messed up... also makes sorting the z-index issue easier
    //     // content: '<div class="media">'+
    //     //           '<a href="#" class="pull-left">'+
    //     //           '<img src="/examples/images/avatar-tiny.jpg" class="media-object" alt="Sample Image">'+
    //     //           '</a><div class="media-body"><h4 class="media-heading">Jhon Carter</h4>'+
    //     //           '<p>Excellent Bootstrap popover! I really love it.</p></div></div>'
    // });
    
    // $element.on('shown.bs.popover', function () {
    //     var popover = $element.data('bs.popover');
    //     if (typeof popover !== "undefined") {
    //         var $tip = popover.tip();
    //         zindex = $tip.css('z-index');
    //         // console.log('tip----',$('#'+$($tip).attr('id')));
    //         $('#'+$($tip).attr('id')).find('.popover-title').append('<button class="popover-close">X</button>');
    //         $tip.find('.popover-close').bind('click', function () {
    //             popover.hide();

    //         });
            
    //         $tip.mouseover(function () {
    //             $tip.css('z-index', function () {
    //                 return zindex + 1;
    //             });
    //         })
    //             .mouseout(function () {
    //             $tip.css('z-index', function () {
    //                 return zindex;
    //             });
    //         });
    //     }
    // });
  // });

</script>
@endsection
@section('main_section')
<section class="content">
   <div id="app">

         @include('admin.flash-message')
        <div class="alert alert-success alert-block" id="success" style="display:none">
                <button type="button" class="close" data-dismiss="alert">×</button>	
                    <strong id="msg-success"></strong>
        </div>
        <div class="alert alert-danger alert-block" id="error" style="display:none">
          <button type="button" class="close" data-dismiss="alert">×</button>	
              <strong id="msg-error"></strong>
        </div>
         @yield('content')
        
         <div class="box-header with-border">
          <meta name="csrf-token" content="{{ csrf_token() }}" />

            <div class="box box-primary">
                <div class="box-header">
                  {{-- <input type="text"  name="edit_bay_start_time"  class="input-css timepicker" > --}}

                </div>  
                {{-- <form  action="#" method=""> --}}
                   <div class="col-md-12">
                     <div class="row">
                       <div class="col-md-6">
                      <label>Store Name <sup>*</sup></label>
                      <select name="store_id" id="store_id" class="input-css select2" onchange="this.options[this.selectedIndex].value && (window.location = this.options[this.selectedIndex].value);">
                        @foreach($store as $key => $val)
                          <option value="{{url()->current().'?store_id='.$val->store_id}}" {{(($val->store_id == $store_id) ? 'selected' : '')}} >{{$val->name}}</option>
                        @endforeach
                      </select>
                      <br>
                    </div>
                    <div class="col-md-6">
                      <label>Select Tag Number <sup>*</sup></label>
                      <select name="job_card_id" id="job_card_id" class="input-css select2">
                        <option value="">Select Tag Number</option>
                        @foreach($job_card as $key => $val)
                          <option value="{{$val->id}}">{{$val->tag}}</option>
                        @endforeach
                      </select>
                      <input type="hidden" name="start_time" id="start_time" hidden>
                      <input type="hidden" name="end_time"  id="end_time" hidden>
                      <input type="hidden" name="bay_id"  id="bay_id" hidden>
                      <br>
                    </div>
                     </div>
                   </div>
                  <div class="container margin p-5">
                    
                  </div>
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
                  <div class="card-shadow margin" style="overflow-x:scroll; height: 100%; overflow-y:scroll;">
                    <table class="table" border="1">
                      <thead>
                        <th>Action</th>
                        <th></th>
                        <?php
                        $time_count = 1;
                        $time_arr = array();
                        ?>
                        @while($start < $end)
                          <th class="" colspan="{{$colspan}}" start="{{$start}}">{{date('H:i',$start)}}</th>
                         <?php 
                            $time_arr[$time_count] = $start;
                            $start = strtotime($interval,$start); $time_count++;  
                          ?>
                        @endwhile
                      </thead>
                      <tbody>
                        <?php $bay_count = count($custom_arr); ?>
                        @for($i = 1 ; $i <= $bay_count ; $i++)
                          <tr class="selectable" tr_id="bay-{{$custom_arr[$i-1]['bay']['id']}}" bay-name = "{{$custom_arr[$i-1]['bay']['name']}}" >
                            <td class="thead"><button class="btn-sm btn-primary rearrange" id="{{$custom_arr[$i-1]['bay']['id']}}">ReArrange</button></td>
                            <td class="thead">{{$custom_arr[$i-1]['bay']['name']}}</td>
                            <?php $count = 0; $start_flag = 0; $disable=''; $end_flag = 0; $job_card_id = 0; $same_class = 0; 
                            $bay_end_time = strtotime($custom_arr[$i-1]['bay']['end_time']);
                            $bay_alloc_status = ''; // need when bay is inactive
                             ?>
                            @for($j = 1 ; $j < $time_count ; $j++)
                              @for($k = 1 ; $k <= $colspan ; $k++)
                                <?php 
                                  $prev_time = (($k > 1)? ($time_cal) : $time_arr[$j] ); 
                                  $time_cal = strtotime('+'.($min_duration*$k).' minutes' ,$time_arr[$j]);
                                  $count++;  ?>
                                @if($total_td >= $count)
                                  @if(empty($custom_arr[$i-1]['data']))
                                    <td class="onclicktd {{($custom_arr[$i-1]['bay']['status'] == 'inactive') ? 'disabled' : ''}} 
                                        {{($current_time <= $prev_time)?'':'disabled'}}
                                        {{($bay_end_time < $time_cal)?'disabled':''}} " 
                                        td_id="{{$time_cal.'-'.$count}}"  index="tr-{{$custom_arr[$i-1]['bay']['id']}}_td-{{$count}}" 
                                        id="tr-{{$custom_arr[$i-1]['bay']['id']}}_td-{{$count}}" col-id="{{'td-'.$j}}" 
                                        row-id="{{'tr-'.$custom_arr[$i-1]['bay']['id']}}" data-count="{{$count}}" 
                                        between-td-duration="{{$min_duration}}" start-time="{{date('H:i',$prev_time)}}" end-time="{{date('H:i',$time_cal)}}" >
                                        <i class="start" id="{{$prev_time}}"></i>
                                          <i class="end" id="{{$time_cal}}"></i>
                                    </td>
                                  @else
                                    @foreach($custom_arr[$i-1]['data'] as $key => $val)
                                      @if($start_flag == 0)   {{-- when start time not get --}}
                                        @if($val->unix_start > $prev_time && $val->unix_start < $time_cal)
                                          @php $start_flag = 1; $job_card_id = $val->job_card_id; $same_class = $count; $bay_alloc_status = $val->bay_alloc_status;
                                            if($current_time >= $prev_time){$disable = 'disabled';}else{$disable = '';}
                                          @endphp
                                        @endif
                                        @if($val->unix_end > $prev_time && $val->unix_end <= $time_cal)
                                          @php 
                                            $start_flag = 0; $end_flag = 1; $job_card_id = $val->job_card_id;$bay_alloc_status = $val->bay_alloc_status;
                                            break;
                                          @endphp
                                        @endif
                                      @else   {{-- when start time get and find end time --}}
                                        @if($val->unix_end > $prev_time && $val->unix_end <= $time_cal)
                                          @php 
                                            $start_flag = 0; $end_flag = 1; $job_card_id = $val->job_card_id;$bay_alloc_status = $val->bay_alloc_status; 
                                          @endphp
                                        @endif
                                      @endif
                                    @endforeach
                                    {{-- ///////////////// --}}
                                    @if($start_flag == 0 && $end_flag == 0)
                                      <td class="onclicktd {{($custom_arr[$i-1]['bay']['status'] == 'inactive') ? 'disabled' : ''}} 
                                      {{($current_time <= $prev_time)?'':'disabled'}}
                                      {{($bay_end_time < $time_cal)?'disabled':''}}" 
                                        td_id="{{$time_cal.'-'.$count}}"  index="tr-{{$custom_arr[$i-1]['bay']['id']}}_td-{{$count}}" 
                                        id="tr-{{$custom_arr[$i-1]['bay']['id']}}_td-{{$count}}" col-id="{{'td-'.$j}}" 
                                        row-id="{{'tr-'.$custom_arr[$i-1]['bay']['id']}}" data-count="{{$count}}" 
                                        between-td-duration="{{$min_duration}}" start-time="{{date('H:i',$prev_time)}}" end-time="{{date('H:i',$time_cal)}}">
                                        <i class="start" id="{{$prev_time}}"></i>
                                          <i class="end" id="{{$time_cal}}"></i>
                                      </td>
                                    @elseif($start_flag == 1 && $end_flag == 0)
                                      <td title="{{$custom_arr[$i-1]['data'][0]->tag}}" class="onclicktd {{($custom_arr[$i-1]['bay']['status'] == 'inactive') ? 'disabled' : ''}} 
                                        {{$disable}}
                                        {{($bay_end_time < $time_cal)?'disabled':''}} ui-selected {{'tr-'.$custom_arr[$i-1]['bay']['id'].'_td-'.$same_class.'_'.$job_card_id}} showPopover"
                                        {{-- data-title="{{date('H:i',$prev_time).' to '.date('H:i',$time_cal)}}"   --}}
                                        {{-- data-html="true" data-toggle="popover" --}}
                                        same-class="{{'tr-'.$custom_arr[$i-1]['bay']['id'].'_td-'.$same_class.'_'.$job_card_id}}"
                                        td_id="{{$time_cal.'-'.$count}}"  index="tr-{{$custom_arr[$i-1]['bay']['id']}}_td-{{$count}}" 
                                        id="tr-{{$custom_arr[$i-1]['bay']['id']}}_td-{{$count}}" col-id="{{'td-'.$j}}" 
                                        row-id="{{'tr-'.$custom_arr[$i-1]['bay']['id']}}" data-count="{{$count}}" 
                                        between-td-duration="{{$min_duration}}" start-time="{{date('H:i',$prev_time)}}" end-time="{{date('H:i',$time_cal)}}">
                                        <i class="start" id="{{$prev_time}}"></i>
                                          <i class="end" id="{{$time_cal}}"></i>
                                          {{-- @if(empty($disable) || ($custom_arr[$i-1]['bay']['status'] == 'inactive' && $bay_alloc_status == 'pending'))
                                            <i class="fa fa-edit action-time" ></i>
                                          @endif --}}
                                        @if($custom_arr[$i-1]['bay']['status'] == 'inactive')
                                          @if($bay_alloc_status == 'pending')
                                            <i class="fa fa-trash delete-time"></i>
                                          @endif
                                        @elseif(empty($disable))
                                          <i class="fa fa-edit action-time"></i>
                                        @endif
                                        {{-- <small>{{$custom_arr[$i-1]['bay']['status'].'/'.$bay_alloc_status}}</small> --}}
                                      </td>
                                      
                                    @elseif($start_flag == 0 && $end_flag == 1 )
                                      <td title="{{$custom_arr[$i-1]['data'][0]->tag}}" class="onclicktd end-time-td {{($custom_arr[$i-1]['bay']['status'] == 'inactive') ? 'disabled' : ''}} 
                                      {{$disable}}
                                      {{($bay_end_time < $time_cal)?'disabled':''}} ui-selected {{'tr-'.$custom_arr[$i-1]['bay']['id'].'_td-'.$same_class.'_'.$job_card_id}} showPopover" 
                                        same-class="{{'tr-'.$custom_arr[$i-1]['bay']['id'].'_td-'.$same_class.'_'.$job_card_id}}"
                                        td_id="{{$time_cal.'-'.$count}}"  index="tr-{{$custom_arr[$i-1]['bay']['id']}}_td-{{$count}}" 
                                        id="tr-{{$custom_arr[$i-1]['bay']['id']}}_td-{{$count}}" col-id="{{'td-'.$j}}" 
                                        row-id="{{'tr-'.$custom_arr[$i-1]['bay']['id']}}" data-count="{{$count}}" 
                                        between-td-duration="{{$min_duration}}" start-time="{{date('H:i',$prev_time)}}" end-time="{{date('H:i',$time_cal)}}">
                                          <i class="start" id="{{$prev_time}}"></i>
                                          <i class="end" id="{{$time_cal}}"></i>
                                        @if($custom_arr[$i-1]['bay']['status'] == 'inactive')
                                          @if($bay_alloc_status == 'pending')
                                          <i class="fa fa-trash delete-time"></i>
                                          @endif
                                        @elseif(empty($disable))
                                          <i class="fa fa-edit action-time"></i>
                                        @endif
                                      </td>
                                      @php $end_flag = 0; $same_class = 0; $disable = ''; $bay_alloc_status = '';  @endphp
                                    @endif

                                    {{-- //////////////// --}}
                                  @endif
                                @endif
                              @endfor
                            @endfor
                          </tr>
                        @endfor
                      </tbody>
                    </table>
                  </div>
                {{-- </form> --}}
                {{-- Modal --}}
                <div class="modal fade" id="bayModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content" style="margin-top: 200px!important;">
                      <div class="modal-header">
                        <h4 class="modal-title" id="exampleModalLongTitle">Update Bay Allocation</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                          <span aria-hidden="true">&times;</span>
                        </button>
                      </div>
                      <div class="modal-body">
                        <div class="alert alert-danger" id="js-msg-verror" style="display:none">
                        </div>
                        <form id="my-form"  method="POST">
                          <input type="hidden" hidden id="this_job_card_id" value="">
                          <div class="row">
                            <div class="col-md-12">
                                <div class="col-md-6">
                                  <input type="radio" name="update_bay" class="update_bay"  value="delete"> Delete Bay Allocation 
                                </div>
                                <div class="col-md-6">
                                  <input type="radio" name="update_bay" class="update_bay"  value="edit">  Edit Bay Allocation 
                                </div>
                            </div>
                            <div class="col-md-12" style="display:none" id="delete_bay">
                              <label>Delete Allocated Time <sup>*</sup></label>
                              <div class="col-md-6">
                                <input type="text" id="delete_bay_start_time" disabled value="" name="delete_bay_start_time" class="form-control">
                              </div>
                              <div class="col-md-6">
                                <input type="text" id="delete_bay_end_time" disabled value="" name="delete_bay_end_time" class="form-control">
                              </div>
                            </div>
                            <div class="col-md-12" style="display:none" id="edit_bay">
                              <div class="col-md-12">
                                <label>Pre-Allocated Time <sup>*</sup></label>
                                <div class="col-md-6">
                                  <input type="text" id="pre_bay_start_time" disabled value="" name="pre_bay_start_time" class="form-control">
                                </div>
                                <div class="col-md-6">
                                  <input type = "text" id="pre_bay_end_time" disabled value="" name="pre_bay_end_time" class="form-control">
                                </div>
                              </div>
                              <div class="col-md-12">
                                <label>Edit Pre-Allocated Time <sup>*</sup></label>
                                <div class="col-md-6">
                                  <input type="text" id="edit_bay_start_time"  name="edit_bay_start_time"  class="input-css timepicker" >
                                </div>
                                <div class="col-md-6">
                                  <input type="text" id="edit_bay_end_time"  name="edit_bay_end_time"  class="input-css timepicker">
                                </div>
                              </div>
                            </div>
                          </div>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="button" id="btnUpdate" class="btn btn-success">Update</button>
                      </div>
                      </form>
                    </div>
                  </div>
                </div>
                {{-- end modal --}}
            </div>
         </div>
   </div>
   
   <!-- Modal -->
   <!-- /.box -->
</section>
@endsection