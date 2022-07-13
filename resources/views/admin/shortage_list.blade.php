@extends($layout)

@section('title', __('factory.shortage'))

@section('breadcrumb')
    <li><a href="#"><i class=""></i>Shortage Summary</a></li>
@endsection
@section('css')
<link rel="stylesheet" href="/css/responsive.bootstrap.css">    
@endsection
@section('js')
<script src="/js/dataTables.responsive.js"></script>
  <script>
    var dataTable;

    function updateHtr(el){
      //console.log('h');
      var id = $(el).children('i').attr('class');
      $.ajax({  
                url:"/admin/shortage/shortage_qty/"+id,  
                method:"get",  
                success:function(data){ 
                 if(data)
                    {
                      var qtyvalue = data['qty'];
                      var lrnvalue = data['lrn'];

                      if (qtyvalue) {
                        $.each(qtyvalue,function(key,value){
                          $("#shortage_id").val(value['id']);
                          $("#shortage_qty").val(value['shortage_qty']);
                          $("#part_type").val(value['part_type']);
                          var myStr = value['part_type'];
                          var newStr = myStr.replace(/_/g, " ");
                          $("label[for='part_type']").text(newStr);
                          $("label[for='shortage_qty']").text(value['shortage_qty']);

                        }); 

                      }if(lrnvalue){
                           $.each(lrnvalue,function(key,value){                        
                            $("#lrn").append($("<option></option>").val(value.id).html(value.load_referenace_number)); 
                        }); 
                        }
                        $('#shortageModalCenter').modal("show");  
                  }
                      
                }  
           });
    }

     function showLog(el){

      //console.log('h');
      var id = $(el).children('i').attr('class');
     // console.log(id);
      $.ajax({  
                url:"/admin/shortage/show_log/"+id,  
                method:"get",  
                dataType:'json',
                success: function(response){

                  var obj = response['data'];
                 // console.log(obj);
                 var i = 1;
                  $("#userTable tbody").html('');                
                  jQuery.each(obj, function( key, value ) {

                       var id = value.id;
                       var lrn = value.lrn;
                       var qty = value.receive_qty;
                       var rdate = value.receive_date;
                       var date=rdate.split(' ')[0];
                       
                       var tr_str = "<tr>" +
                           "<td>" + i + "</td>" +
                           "<td>" + lrn + "</td>" +
                           "<td>" + qty + "</td>" +
                           "<td>" + date + "</td>" +
                       "</tr>";

                       $("#userTable tbody").append(tr_str);
                       i++;
                    });

                    $('#showModal').modal("show"); 
                 }  
           });
    }

    // Data Tables
    $(document).ready(function() {
      dataTable = $('#admin_table').DataTable({
          "processing": true,
          "serverSide": true,
          "ajax": "/admin/shortage/list/api",
          "aaSorting": [],
          "responsive": true,
          "columns": [
              { "data": "id" },
              { "data": "name" },
              { "data": "lrn_no" },
              { "data": "part_type" ,
               "render": function (data, type, row) {
                    if (data) {
                        var str = data;
                        var string = str.replace(/_/g, ' ');
                        return string.charAt(0).toUpperCase() + string.slice(1);
                      } 
                }
              },
              { "data": "shortage_qty" },
              { "data": "created_at" },
              {
                  "targets": [ -1 ],
                  "data":"id", "render": function(data,type,full,meta)
                  {
                    
                      return '<a href="#"><button class="btn btn-info btn-xs "  onclick="updateHtr(this)"><i class="'+data+'"></i> Update Qty</button></a> &nbsp;'+'<a href="#"><button class="btn btn-success btn-xs "  onclick="showLog(this)"><i class="'+data+'"></i> Show Log</button></a> ';
                  },
                  "orderable": false
              }
            ],
            "columnDefs": [
            ]
          
        });
    });
  </script>
@endsection

@section('main_section')

    <section class="content">

        <div id="app">
                   
        <!-- Default box -->
          <div class='box box-default'>
            <div class="container-fluid">
                <br>
                 <div class="row" style="border: 1px solid #f4f4f4;">
                    <div class="col-md-3"> <label>Part Type</label></div>
                    <div class="col-md-3"> <label>Shortage Quantity</label></div>
                    <div class="col-md-3"> <label>Part Type</label></div>
                    <div class="col-md-3"> <label>Shortage Quantity</label></div>
                </div>
                <div class="row ">
                <div class="col-md-12 ">

                @foreach($shortage as $key)
                <?php
                if ($key->part_type == 'battery') {
                  $type ="Battery ";
                }if ($key->part_type == 'mirror_set') {
                  $type ="Mirror Set ";
                }if ($key->part_type == 'tool_kit') {
                  $type ="Tool Kit ";
                }if ($key->part_type == 'owner_manual') {
                  $type ="Owner Manual ";
                }if ($key->part_type == 'first_aid') {
                  $type ="First Aid ";
                }if ($key->part_type == 'qty_keys') {
                  $type ="Quantity Kyes";
                }if ($key->part_type == 'saree') {
                  $type ="Saree Gourd ";
                }
                 ?>
                    <div class="col-md-3" ><label style="font-weight: 600!important">{{$type}}</label></div>
                    <div class="col-md-3" ><label style="font-weight: 600!important">{{$key->qty}}</label></div>
                    
                @endforeach
                </div>
                </div>
            <br>
        </div>
        <div class="col-md-12" style="height: 30px;background-color: #ecf0f5;z-index: 1;"></div>
        <div class="box box-primary">
            <!-- /.box-header -->
            <div class="box-header with-border">
<!--                    <h3 class="box-title">{{__('customer.mytitle')}} </h3>-->
                </div>  
                <div class="box-body">
                   @include('admin.flash-message')
                    @yield('content')
                  <table id="admin_table" class="table table-bordered table-striped">
                    <thead>
                    <tr>
                      <th>{{__('factory.id')}}</th>
                      <th>{{__('factory.factory_name')}}</th>
                      <th>{{__('factory.lrn_no')}}</th>
                      <th>{{__('factory.shortage_type')}}</th>
                      <th>{{__('factory.qty')}}</th>
                      <th>{{__('factory.created_at')}}</th>
                      <th>{{__('factory.action')}}</th>                      
                    </tr>
                    </thead>
                    <tbody>
                      
                    </tbody>
               
                  </table>
           
                </div>
                <!-- /.box-body -->
              </div>
               <!--Update Modal -->
              <div class="modal fade" id="shortageModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                  <div class="modal-content" style="margin-top: 200px!important;">
                    <div class="modal-header">
                      <h4 class="modal-title" id="exampleModalLongTitle">Update Quantity</h4>
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                      </button>
                    </div>
                    <div class="modal-body">
                      <form  action="/admin/shortage/update_shortage" method="POST">
                        @csrf
                        <div class="row">
                          <input type="hidden" name="id" id="shortage_id">
                          <div class="col-md-6">
                              <div class="col-md-6"><div class="row"><label >Part Type : </label></div></div>
                               <div class="col-md-6"><div class="row"><label for="part_type" style="font-weight: 600!important"></label></div></div>
                              <input id="part_type" type="hidden" name="part_type" class="form-control" readonly="">
                              <br>
                          </div>
                          <div class="col-md-6">
                               <div class="col-md-9"><div class="row"><label >Shortage Quantity : </label></div></div>
                               <div class="col-md-3"><div class="row"><label for="shortage_qty" style="font-weight: 600!important"></label></div></div>
                              <input id="shortage_qty" type="hidden" name="shortage_qty" class="form-control" readonly="">
                          </div>
                        </div><hr>
                        <div class="row">
                          <div class="col-md-6">
                              <label>Load Reference Number <sup>*</sup></label>
                              <select id="lrn" class="form-control" name="lrn_id">
                                <option value="0" >--SELECT LRN--</option>
                              </select>
                          </div>
                          <div class="col-md-6">
                              <label>Received Quantity <sup>*</sup></label>
                              <input id="qty" type="number" name="qty" class="form-control">
                              <br>
                          </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                      <button type="submit" class="btn btn-success">Update</button>
                    </div>
                    </form>
                  </div>
                </div>
              </div>

              <!--- Show Data Model -->
              <!-- Modal -->
              <div class="modal fade" id="showModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                  <div class="modal-content" style="margin-top: 200px!important;">
                    <div class="modal-header">
                      <h4 class="modal-title" id="exampleModalLongTitle">Shortage Log List</h4>
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                      </button>
                    </div>
                    <div class="modal-body">
                      <table border='1' id='userTable' class="table table-bordered table-striped">
                         <thead>
                          <tr>
                            <th>S.no</th>
                            <th>LRN</th>                            
                            <th>Quantity</th>
                            <th>Date</th>
                          </tr>
                         </thead>
                         <tbody></tbody>
                       </table>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                  </div>
                </div>
              </div>
        <!-- /.box -->
      </section>
@endsection
