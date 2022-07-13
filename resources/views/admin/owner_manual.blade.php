@extends($layout)

    @section('title', __(''))



@section('breadcrumb')
   
@endsection

@section('js')
@endsection

@section('main_section')
    <section class="content">
            <!-- general form elements -->
            <div class="box-header with-border">
            <div class="box box-primary">
                <div class="box-header">
                </div> 
                
                <div class="box-body">
                    <table border='1' style="width:100%">
                        <thead>
                            <tr>
                                <th  style="width:40%">Addon Name</th>
                                <th  style="width:10%">Qty</th>
                                <th  style="width:10%">Sale Qty</th>
                                <th  style="width:10%">Store Id</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($vad as $key => $val)
                                <tr>
                                    <td> {{$val['addon']}} </td>
                                    <td> {{$val['qty']}} </td>
                                    <td> {{$val['sale_qty']}} </td>
                                    <td> {{$val['store_id']}} </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div> 
                <div class="box-body">
                    <table border='1' style="width:100%">
                        <thead>
                            <tr>
                                <th  style="width:40%">Addon Name</th>
                                <th  style="width:10%">Qty</th>
                                <th  style="width:10%">Sale Qty</th>
                                <th  style="width:10%">Store Id</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($vas as $key => $val)
                                <tr>
                                    <td> {{$val['vehicle_addon_key']}} </td>
                                    <td> {{$val['qty']}} </td>
                                    <td> {{$val['sale_qty']}} </td>
                                    <td> {{$val['store_id']}} </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div> 
                <hr>
                <div class="box-body">
                    <label>"stock_move_addon" <= Table Data</label>
                    <table border='1' style="width:100%">
                        <thead>
                            <tr>
                                <th  style="width:30%">Process_id</th>
                                <th  style="width:30%">Addon Name</th>
                                <th  style="width:30%">Qty</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sma as $key => $val)
                                <tr>
                                    <td> {{$val->process_id}} </td>
                                    <td> {{$val->addon}} </td>
                                    <td> {{$val->qty}} </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div> 

            </div>
      </section>
@endsection