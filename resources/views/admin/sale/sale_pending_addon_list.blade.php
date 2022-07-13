@extends($layout)

@section('title', __('Pending Sale Addon List'))

@section('breadcrumb')
    <li><a href="#"><i class=""></i> </a></li> 
@endsection
@section('css')
<link rel="stylesheet" href="/css/responsive.bootstrap.css"> 
@endsection
@section('js')
<script src="/js/dataTables.responsive.js"></script>
  <script>
    var dataTable;

    function issue_item(el){
      var sale_id = parseInt($(el).attr('sale_id'));
      $("#sale_id").val(0);
      if(sale_id > 0 && sale_id != undefined && sale_id != null){
        $("#loader").show();
        $("#js-msg-error").hide();
        $("#js-msg-success").hide();
        $("#append-modal_body").empty();

        $.ajax({
          url:'/admin/sale/get/pending/addon',
          method: 'GET',
          data: {'sale_id':sale_id},
          success:function(res){
            // console.log(res);
            draw_modal(res);
            $("#sale_id").val(sale_id);
            $("#saleModalCenter").modal('show');
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
                    '<div class="col-md-2">'+
                      '<label> <input type="checkbox" name="pending_addon_ids[]" value="'+val.id+'" class="input-css"> </label>'+
                    '</div>'+
                    '<div class="col-md-10">'+
                      '<label>'+val.name+'</label>'+
                    '</div>'+
                  '</div>';
          ele.append(str);
      })
    }

    $('#issue_item-form').submit(function(e) {
      e.preventDefault(); 
      $("#loader").show();
      var formData = new FormData(this);
      var sale_id = parseInt($("#sale_id").val());
      if(sale_id > 0)
      {
        $.ajax({
          type:'POST',
          url: "/admin/sale/pending/addon/issueItem",
          data: formData,
          cache:false,
          contentType: false,
          processData: false,
          success: (data) => {
            // this.reset();
            $('#js-msg-success').text(data).show();
            $('#saleModalCenter').modal("hide");
            // $('#pending_table').dataTable().api().ajax.reload();
            dataTable.ajax.reload(null, false);
          },
          error: function(error){
            $('#loader').hide();
            // console.log(error);
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
              { "data": "store_name"},
              { "data": "customer_name"},
              { "data": "addon_details",'orderable':false }
            ];
    var table_name = 'complete_table';
      if(table == 'pending'){
          action = {
                  "targets": [ -1 ],
                  "data":"sale_id", "render": function(data,type,full,meta)
                  {
                    var str = '';
                      if(full.rto_application_no != null && full.rto_application_no != '' && full.rto_application_no != undefined){
                        str+='<a href="#"><button class="btn btn-success  btn-xs "  onclick="issue_item(this)" sale_id="'+data+'"> Issue Pending Addon </button></a>';
                      }else{
                        str+='<a href="#"><button class="btn btn-success  btn-xs " disabled title="Firstly Create RTO" > Issue Pending Addon </button></a>';
                      }
                       return str;
                  },
                  "orderable": false
          };
        column.push(action);
        table_name = 'pending_table';
      }else{
        var add_col = {
           "data": "issue_date",'orderable':false 
          };
        column.push(add_col);
        add_col = {
           "data": "issue_by",'orderable':false 
          };
        column.push(add_col);
      }
       
      dataTable = $('#'+table_name).DataTable({
          "processing": true,
          "serverSide": true,
          "aaSorting": [],
          "responsive": true,
           "ajax": {
            "url": "/admin/sale/pending/addon/list/api",
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
                  <button class="nav-link1 btn-color-active" id="pending-tab" >UnIssued Addon List</button>
                </li>
                <li class="nav-item">
                  <button class="nav-link1 btn-color-unactive" id="complete-tab" >Issued Addon List</button>
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
                      <th>Addon Details</th>
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
                      <th>Addon Details</th>
                      <th>Issue Date</th>
                      <th>Issue By</th>
                    </tr>
                    </thead>
                    <tbody>
                      
                    </tbody>
               
                  </table>
           
                </div>
                <!-- /.box-body -->
              </div>
               <!-- Modal -->
              <div class="modal fade" id="saleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                  <div class="modal-content" style="margin-top: 100px!important;">
                    <div class="modal-header">
                      <h4 class="modal-title" id="exampleModalLongTitle">Issue Addons</h4>
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                      </button>
                    </div>
                    <div class="modal-body">
                      <div class="alert alert-danger" id="modal-error" style="display:none">
                      </div>
                      <div class="alert alert-success" id="modal-success" style="display: none">
                      </div>
                      <form id="issue_item-form"  method="POST" >
                        @csrf
                        <input type="hidden" name="sale_id" id="sale_id">
                        <div class="row" id="append-modal_body">
                          
                        </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                      <button type="submit" id="btnUpdate" class="btn btn-success">Issue Items</button>
                    </div>
                    </form>
                  </div>
                </div>
              </div>
        <!-- /.box -->
      </section>
@endsection