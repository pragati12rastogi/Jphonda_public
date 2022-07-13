@extends($layout)

@section('title', __('unload.title_view'))

@section('breadcrumb')

    <li><a href="#"><i class=""></i>{{__('unload.title_view')}}</a></li> 
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
    var dataTable;

    function getunloaddata(){
        
        if(dataTable)
            dataTable.destroy();

        dataTable = $('#table').DataTable({
          "processing": true,
          "serverSide": true,
          "aaSorting":[],
          "responsive": true,
          "ajax": "/admin/unload/data/list/api",
          "columns": [
                {"data":"refer"}, 
                {"data":"store"}, 
                {
                    "data":"unload_date", "render": function(data,type,full,meta)
                    {
                        return data;
                    }
                },
                {"data":"trans"}, 
                {"data":"factory"}, 
                {"data":"truck_number"}, 
                // {"data":"status"}, 
                {
                    "targets": [ -1 ],
                    "data":"id", "render": function(data,type,full,meta)
                    {
                        var str = '';
                        str = str+"<a href='/admin/unload/data/edit/"+data+"'><button class='btn btn-success btn-xs'> {{__('unload.unload_list_Edit')}} </button></a> &nbsp;";
                        if(full.owner_manual_sum > 0){
                            str = str+"<a href='#' ><button class='btn btn-warning btn-xs addOwnerManual' data="+data+" > {{__('Owner Manual')}} </button></a> &nbsp;";
                        }else{
                            str = str+"<a href='#' ><button class='btn btn-primary btn-xs addOwnerManual' data="+data+" > {{__('Add Owner Manual')}} </button></a> &nbsp;";
                        }
                        
                        if(full.product_details_id == null){
                           str = str+'<a href="/admin/battery/import?lrn='+full.id+'"><button class="btn btn-info btn-xs"> {{__("Import Battery")}} </button></a>';
                        }
                        return str;
                    },
                    "orderable": false
                }
            ]
        });
    }
   


    $(document).ready(function() {
        getunloaddata();
    });

    $(document).on('click','.addOwnerManual',function(){
        $("#addOwnerManual-div-append").empty();
        $("#addmore-div-append").empty();
      var unload_id = parseInt($(this).attr('data'));
      $("input[name='unload_id']").val(unload_id);
      $("#error").hide();
      $("#success").hide();

      draw_div(unload_id);
    });
    var model = [];
    function draw_div(unload_id){
      $("#loader").show();
      $.ajax({
        type: 'GET',
        url: "/admin/unload/list/get/unloadAddon",
        data: {'unload_id':unload_id},
        success: function(data) {
            // console.log(data);
            var pre_model = data[0];
            model = data[1];
            $("#addOwnerManual-div-append").empty();
            var append_str = '';
            $.each(pre_model,function(key,val){
                append_str = append_str+"<div class='row'><div class='col-md-6'>"+
                                            "<label>"+val.model+"</label>"+
                                        "</div>"+
                                        "<div class='col-md-6'>"+
                                            "<input type='number' name='"+val.id+"' min='0' class='input-css' value='"+val.qty+"' >"+
                                        "</div></div>";
            });
            // console.log(append_str);
            $("#addOwnerManual-div-append").append(append_str);
            $('#addOwnerManualModal').modal("show"); 
        },
        error: function(error) {
            $("#loader").hide();
            $('#error').html(error.responseJSON).show();
        }
    }).done(function() {
        $("#loader").hide();
    });
    }

    $(document).on('click','.add_more',function(){
        var option = "<option value=''>Select Model</option>";
        $.each(model,function(key,val){
            option = option+"<option value='"+val+"'>"+val+"</option>";
        });
        var append_str = "<div class='row margin-bottom'>"+
                            "<div class='col-md-6'>"+
                                "<select name='model[]' class='input-css this_select' >"+
                                option+
                                "</select>"+
                            "</div>"+
                            "<div class='col-md-5'>"+
                                "<input type='number' name='model_qty[]' min='0' class='input-css' value='0' >"+
                            "</div>"+
                            "<div class='col-md-1'>"+
                                "<i class='fa fa-times remove_div'></i>"+
                            "</div>"+
                        "</div>";
        $("#addmore-div-append").append(append_str);
        $(".this_select").removeClass('this_select').select2();
    });
    $(document).on('click','.remove_div',function(){
        $(this).parent().parent().remove();
    })

  $('#addOwnerManual-form').submit(function(e) {
    e.preventDefault();

    $("#loader").show();
    var formData = new FormData(this);
    $('#addOwnerManual-form').find('span').remove();
    $('#aom-error').hide();
    $('#aom-success').hide();

    $.ajax({
        type: 'POST',
        url: "/admin/unload/list/update/unloadAddon",
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        success: (data) => {
            // console.log(data);
            if (data.length > 0) {
                $('#success').show();
                $('#success').text(data[1]);
                $('#addOwnerManualModal').modal("hide");
            }
        },
        error: function(error) {
            $("#loader").hide();
            if ((error.responseJSON).hasOwnProperty('message')) {
                if (error.responseJSON.errors) {
                    draw_error(error.responseJSON.errors, 'addOwnerManual-form');
                }
                $('#aom-error').html(error.responseJSON.message).show();
            } else {
                $('#aom-error').html(error.responseJSON).show();
            }
            $("#loader").hide();
        }
    }).done(function() {
        $("#loader").hide();
    });
  });
  function draw_error(arr, form_id) {
    $('#'.form_id).find('span').remove();
    $.each(arr, function(key, val) {
        $("#" + key).parent().append("<span style='color:red;'>" + val[0] + "</span>");
    });
  }

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
            <div class="alert alert-danger" id="error" style="display:none;">
            </div>
            <div class="alert alert-success" id="success" style="display:none;">
            </div>
        </div>
    <!-- Default box -->
        <div class="box">
                <!-- /.box-header -->
            <div class="box-body">
            
                    <table id="table" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>{{__('unload.refer')}}</th>
                                <th>{{__('unload.store')}}</th>
                                <th>{{__('unload.date')}}</th>
                                <th>{{__('unload.trans')}}</th>
                                <th>{{__('unload.factory')}}</th>
                                <th>{{__('Truck #')}}</th>
                                {{-- <th>{{__('unload.status')}}</th> --}}
                                <th>{{__('unload.action')}}</th> 
                            </tr>
                            
                        </thead>
                        <tbody>
                        </tbody>               
                    </table>
                    <!-- /.box-body -->
            </div>
        </div>
        <!-- /.box -->

        <!-- modal -->
        <!-- service due date modal -->
        <div class="modal fade" id="addOwnerManualModal" tabindex="-1" role="dialog" aria-labelledby="addOwnerManualModalCenterTitle" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered" role="document">
              <div class="modal-content">
                  <div class="modal-header">
                      <h4 class="modal-title" id="addOwnerManualModalCenterTitle">Update Owner Manual Details</h4>
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                      </button>
                  </div>
                  <form method="POST" action="#" id="addOwnerManual-form">
                      @csrf
                      <input type="hidden" name="unload_id" hidden value="">
                      <div class="alert alert-danger" id="aom-error" style="display:none;">
                      </div>
                      <div class="alert alert-success" id="aom-success" style="display:none;">
                      </div>
                      <div class="modal-body">
                        <div class="row" id="owner_manual-div">
                            <div class="row">
                                <div class="col-md-6">
                                    <label>Model Name</label>
                                </div>
                                <div class="col-md-6">
                                    <label>Quantity</label>
                                </div>
                            </div>
                            <div class="row" id="addOwnerManual-div-append">

                            </div>
                            <div class="row" id="addmore-div-append">

                            </div>
                            <div class="row margin pt-2">
                                <i class="btn btn-info fa fa-plus add_more" style="float: right;"> Add More</i>
                            </div>
                        </div>
                      </div>
                      <div class="modal-footer">
                          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                          <button type="submit" id="btnUpdate" class="btn btn-success" >Submit</button>
                      </div>
                  </form>
              </div>
          </div>
        </div>
    </section>
@endsection