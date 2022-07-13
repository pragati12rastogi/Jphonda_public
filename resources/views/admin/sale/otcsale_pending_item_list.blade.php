@extends($layout)

@section('title', __('OTC Sale Pending Item List'))

@section('breadcrumb')
    {{-- <li><a href="#"><i class=""></i>{{__('finance.sale_title')}} </a></li> --}}
@endsection
@section('css')
<link rel="stylesheet" href="/css/responsive.bootstrap.css"> 
@endsection
@section('js')
<script src="/js/dataTables.responsive.js"></script>
  <script>
    var dataTable;

    function create_otc(el){
      var otc_sale_id = parseInt($(el).attr('otc_sale_id'));
      $("#otc_sale_id").val(0);
      if(otc_sale_id > 0 && otc_sale_id != undefined && otc_sale_id != null){
        $("#loader").show();
        $("#js-msg-error").hide();
        $("#js-msg-success").hide();
        $("#append-modal_body").empty();

        $.ajax({
          url:'/admin/sale/otcsale/get/pending/item',
          method: 'GET',
          data: {'otc_sale_id':otc_sale_id},
          success:function(res){
            // console.log(res);
            draw_modal(res);
            $("#otc_sale_id").val(otc_sale_id);
            $("#otcsaleodalCenter").modal('show');
          },
          error:function(error){
            // console.log(error);
            $("#loader").hide();
            if(error.responseJSON.hasOwnProperty('message')){
              $("#js-msg-error").text(error.responseJSON.message).show();
            }else{
              $("#js-msg-error").text(error.responseText).show();
            }
          }

        }).done(function(){
          $("#loader").hide();
        });
      }

    }

    function draw_modal(arr){
      $("#modal-error").hide();
      $("#modal-success").hide();
      var ele = $("#append-modal_body");
      $.each(arr,function(key,val){
        var str = '<div class="col-md-12">'+
                    '<div class="col-md-1">'+
                      '<label> <input type="checkbox" name="otc_sale_detail_ids[]" value="'+val.id+'" class="input-css"> </label>'+
                    '</div>'+
                    '<div class="col-md-5">'+
                      '<label>'+val.name+'</label>'+
                    '</div>'+
                    '<div class="col-md-5">'+
                      '<label>'+val.part_number+'</label>'+
                    '</div>'+
                  '</div>';
          ele.append(str);
      })
    }

    $('#create_otc-form').submit(function(e) {
      e.preventDefault(); 
      $("#loader").show();
      var formData = new FormData(this);
      var otc_sale_id = parseInt($("#otc_sale_id").val());
      if(otc_sale_id > 0)
      {
        $.ajax({
          type:'POST',
          url: "/admin/sale/otcsale/pending/item/createotc",
          data: formData,
          cache:false,
          contentType: false,
          processData: false,
          success: (data) => {
            // this.reset();
            $('#js-msg-success').text(data).show();
            $('#otcsaleodalCenter').modal("hide");
            $('#pending_table').dataTable().api().ajax.reload();
          },
          error: function(error){
            $('#loader').hide();
            console.log(error);
            if ((error.responseJSON).hasOwnProperty('message')) {
              $('#modal-error').html(error.responseJSON.message).show();
            }else{
              $('#modal-error').html(error.responseText).show();
            }
          }
        }).done(function(){
          $("#loader").hide();
        });
      }
    });

  function datatablefn(table) {

    if(dataTable){
      dataTable.destroy();
    }

    var action = '';
    var column = [
              { "data": "sale_no"},
              { "data": "name"},
              { "data": "customer_name"},
              { "data": "part_details",'orderable':false }
            ];
    var table_name = 'complete_table';
      if(table == 'pending'){
          action = {
                  "targets": [ -1 ],
                  "data":"id", "render": function(data,type,full,meta)
                  {
                    var str = '';
                      str+='<a href="#"><button class="btn btn-success  btn-xs "  onclick="create_otc(this)" otc_sale_id="'+data+'"> Create OTC </button></a>';
                       return str;
                  },
                  "orderable": false
          };
        column.push(action);
        table_name = 'pending_table';
      }
       
      dataTable = $('#'+table_name).DataTable({
          "processing": true,
          "serverSide": true,
          "aaSorting": [],
          "responsive": true,
           "ajax": {
            "url": "/admin/sale/otcsale/pending/item/list/api",
            "datatype": "json",
                "data": function (data) {
                    data.tabVal = table;
                }
            },
          "columns": column,
            "columnDefs": [
            ]
          
      });
    }


     $(document).ready(function() {
      datatablefn('pending');
    });

    $("#pending-tab").click(function(){
      $("#pending_table").show();
      $("#complete_table").hide();
      $("#complete-tab").removeClass('btn-color-active');
      $(this).removeClass('btn-color-unactive').addClass('btn-color-active');
      datatablefn('pending');
    });
    $("#complete-tab").click(function(){
      $("#complete_table").show();
      $("#pending_table").hide();
      $("#pending-tab").removeClass('btn-color-active');
      $(this).removeClass('btn-color-unactive').addClass('btn-color-active');
      datatablefn('complete');
    });


    function draw_error(arr){
      $('.all-error').text('').hide();
      $.each(arr,function(key,val){
          $("."+key+"-error").text(val[0]).show();
      });
    }

  </script>
@endsection

@section('main_section')

    <section class="content">

        <div id="app">
                   
        <div class="box box-primary">
            <!-- /.box-header -->
            <div class="box-header with-border">
<!--                    <h3 class="box-title">{{__('customer.mytitle')}} </h3>-->
              <ul class="nav nav1 nav-pills">
                <li class="nav-item">
                  <button class="nav-link1 btn-color-active" id="pending-tab" >Pending OTC Sale List</button>
                </li>
                <li class="nav-item">
                  <button class="nav-link1 btn-color-unactive" id="complete-tab" >Complete OTC Sale List</button>
                </li>
              </ul>
                </div>  
                <div class="box-body">
                   @include('admin.flash-message')
                   <div class="alert alert-danger" id="js-msg-error" style="display: none">
                    </div>
                    <div class="alert alert-success" id="js-msg-success" style="display: none">
                    </div>
                    @yield('content')
                     <div class="row">
                      <div class="col-md-6">
                      </div>
                    </div>
                  <table id="pending_table" class="table table-bordered table-striped">
                    <thead>
                    <tr>
                      <th>Sale Number</th>
                      <th>Store Name</th>
                      <th>Customer Name</th>
                      <th>Part Details</th>
                      <th>Action</th>                 
                    </tr>
                    </thead>
                    <tbody>
                      
                    </tbody>
               
                  </table>
                  <table id="complete_table" class="table table-bordered table-striped" style="display: none">
                    <thead>
                    <tr>
                      <th>Sale Number</th>
                      <th>Store Name</th>
                      <th>Customer Name</th>
                      <th>Part Details</th>
                    </tr>
                    </thead>
                    <tbody>
                      
                    </tbody>
               
                  </table>
           
                </div>
                <!-- /.box-body -->
              </div>
               <!-- Modal -->
              <div class="modal fade" id="otcsaleodalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                  <div class="modal-content" style="margin-top: 100px!important;">
                    <div class="modal-header">
                      <h4 class="modal-title" id="exampleModalLongTitle">Create OTC</h4>
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                      </button>
                    </div>
                    <div class="modal-body">
                      <div class="alert alert-danger" id="modal-error" style="display:none">
                      </div>
                      <div class="alert alert-success" id="modal-success" style="display: none">
                      </div>
                      <form id="create_otc-form"  method="POST" >
                        @csrf
                        <input type="hidden" name="otc_sale_id" id="otc_sale_id">
                        <div class="row" id="append-modal_body">
                          
                        </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                      <button type="submit" id="btnUpdate" class="btn btn-success">Create OTC</button>
                    </div>
                    </form>
                  </div>
                </div>
              </div>
        <!-- /.box -->
      </section>
@endsection