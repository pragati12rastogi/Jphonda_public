
<!DOCTYPE html>
<html lang="en">
<head>
<link rel="stylesheet" href="/css/io_templates.css">  
        <style>
* {
    margin: 0px;
    padding: 0px;
}

.col {
    float: left;
    position: relative;
}

.body {
    margin: 0px;
    padding: 0px;
}

.tablestyle {
    border: 3px black;
    font-family: 'Arial Narrow', Arial, sans-serif;
    font-size: 12px;
}

.tablestyle td {
    border: 1px solid;
}

.tablestyle td h4 {
    margin-bottom: 0;
}

.colcenter {
    text-align: center;
}

.partyaddress {
    width: 100%;
}

tr:hover {
    background-color: #f5f5f5;
}

.box-title {
    text-align: right;
}

table {
    border-collapse: collapse;
}

table,
td,
th {
    text-align: left;
    border: 1px solid black;
    padding: 5px;
}

body {
    padding: 1rem;
}

.foot {
    text-align: right;
}

.head {
    text-align: center;
}
@page {
  footer: page-footer;
}
        .tablestyle1 {
    border:none !important;
}
.tablestyle1 tr td{
border:none;
border-bottom: none;
font-size: 15px;
}
.tablestyle1 td{
        
}
        </style>
</head>
<body>
                    
    <div class="box">  
        <div class="box-body">
                                <div class="box-header with-border">
                                    <div class="col" style="width:700px;height: 30px;text-align:center">
                                   
                                        <img src="{{URL::asset('/images/favicon.png')}}"  class="logopp" style="width:80px;">
                                        
                                            
                                    </div>
                                  
                                    
                            </div>
                            <div  style="float: left; margin-right: 100px;text-align: center;height:30px;width:100%">
                                <p style="padding-top:0px;"><b><u>Leave Application</u></b></p>
                                
                                </div>
        </div>
        <!-- /.box-body -->
    
    </div>
<br>
<div class="box">
    <div class="box-body">
        <table class="tablestyle1" style="width:100%;">
            <tr>
                <td>
                    <p><b>Employee Name : </b> {{$format['emp']}}</p>
                </td>
                <td>
                    <p><b>Date : </b>{{$format['leave_d']}} </p>
                </td>
        </table><br><br>
    </div>
</div>
<div class="box">  
    <div class="box-body">
                 <div class="row">
                     <div class="col-md-12">
                        <center><h4 style="text-align:left">Period Of Leave Application</h4></center>
                     </div>
                 </div>
                            <table class="tables" style="width:100%;">
                                <tr>
                                    <th>
                                        <p>Start Date</p>
                                    </th>
                                    <th>
                                        <p>End Date </p>
                                    </th>
                                    <th>
                                        <p>No. Of Days</p>
                                    </th>
                                    <th>
                                            <p>Join On Date</p>
                                        </th>
                                   
                                   
                                </tr>
                                
                                  <tr>
                                  <td>{{$format['start_date']}}</td>
                                  <td>{{$format['end_date']}}</td>
                                  <td>{{$format['st_date']+1}}</td>
                                  <td>{{date('d-m-Y', strtotime($format['end_date'] . ' +1 day'))}}</td>
                                  </tr>
                                
                            </table><br><br>

                            
    </div>
    <!-- /.box-body -->

</div>
<div class="box">  
        <div class="box-body">
                     <div class="row">
                         <div class="col-md-12">
                         </div>
                     </div>
                                <table class="tables" style="width:100%;">
                                    <tr>
                                        <td>
                                           <p> <b>Reason For Leave :  &nbsp;&nbsp;      </b>{{$format['reason']}} </p>
                                        </td>
                                       
                                </table><br><br>
    
                                
        </div>
        <!-- /.box-body -->
    
    </div>

<div class="box">  
        <div class="box-body">
                     <div class="row">
                         <div class="col-md-12">
                            <center><h4 style="text-align:left"> Leave Status :{{$format['leave_status']}}</h4></center>
                         </div>
                      </div>     
                      <table class="tables" style="width:100%;">
                                <tr>
                                    <th>
                                        <p>Level</p>
                                    </th>
                                    <th>
                                        <p>Approved By</p>
                                    </th>
                                    <th>
                                         <p>Approved Date</p>
                                    </th>
                                     <th>
                                         <p>Approved status</p>
                                    </th>
                                   
                                   
                                </tr>
                                
                                  <tr>
                                  <td>Level 1</td>
                                  <td>{{$format['level1']}}</td>
                                  <td>{{$format['status_level1_date']}}</td>
                                  <td>{{$format['status_level1']}}</td>
                                  </tr>
                                  <tr>
                                  <td>Level 2</td>
                                  <td>{{$format['level2']}}</td>
                                  <td>{{$format['status_level2_date']}}</td>
                                  <td>{{$format['status_level2']}}</td>
                                  </tr>
                                
                            </table><br><br>  
        </div>
        <!-- /.box-body -->
    
    </div>

        <!-- /.box -->
      <br>
 <!--    
        <div class="box">
                <div class="box-body">
                        <table class="noBorder" cellspacing="0" cellpadding="0" border="0" style="border-collapse: collapse; border: none; width:100%;border:none">
                            <tr style="border: none;height:80px">
                                @if ($format['status_level1']=="Rejected" || $format['status_level2']=="Rejected")
                                <td style="text-align:left;border: none;width:200px"><b>Leave Status : </b>Rejected</td>
                                @elseif ($format['status_level1']=="Approved")
                                    <td style="text-align:left;border: none;width:200px"><b>Leave Status : </b>Pending</td>
                                @elseif ($format['status_level1']=="Approved" && $format['status_level2']=="Approved")
                                    <td style="text-align:left;border: none;width:200px"><b>Leave Status : </b>Approved</td>
                                @else
                                    <td style="text-align:left;border: none;width:200px"><b>Leave Status : </b></td>
                                @endif
                                
                            </tr>
                        </table><br><br>
                        <table class="noBorder" cellspacing="0" cellpadding="0" border="0" style="border-collapse: collapse; border: none; width:100%;border:none">
                                <tr style="border: none;height:80px">
                                        @if ($format['status_level1']=="Rejected")
                                            <td style="text-align:left;border: none;width:200px"><b>Approved/Rejected By : </b>{{$format['level1']}}</td>
                                        @elseif ($format['status_level2']=="Rejected")
                                            <td style="text-align:left;border: none;width:200px"><b>Approved/Rejected By : </b>{{$format['level2']}}</td>
                                        @elseif ($format['status_level1']=="Approved")
                                            <td style="text-align:left;border: none;width:200px"><b>Approved/Rejected By : </b>{{$format['level1']}}</td>
                                        @else
                                            <td style="text-align:left;border: none;width:200px"><b>Approved/Rejected By : </b></td>
                                        @endif
                                        
                                    </tr>
                            </table><br><br>
                            <table class="noBorder" cellspacing="0" cellpadding="0" border="0" style="border-collapse: collapse; border: none; width:100%;border:none">
                                    <tr style="border: none;height:80px">
                                            @if ($format['status_level1']=="Rejected")
                                                <td style="text-align:left;border: none;width:200px"><b>Date : </b>{{$format['status_level1_date']}}</td>
                                            @elseif ($format['status_level2']=="Rejected")
                                                <td style="text-align:left;border: none;width:200px"><b>Date : </b>{{$format['status_level2_date']}}</td>
                                            @elseif ($format['status_level1']=="Approved")
                                                <td style="text-align:left;border: none;width:200px"><b>Date : </b>{{$format['status_level1_date']}}</td>
                                            @else
                                                <td style="text-align:left;border: none;width:200px"><b>Date : </b></td>
                                            @endif
                                            
                                        </tr>
                                </table><br><br>
                </div>-->
                <!-- /.box-body -->

        <!-- </div>  -->
        <!-- /.box -->
       {{-- <div class="row">
               <div class="col-md-12">
       <div class="col-md-6"style="float:left">
                <h4>Created By</h4>

                <h4>Approved By</h4>
       </div>
               </div>
       </div> --}}




</body>

</html>