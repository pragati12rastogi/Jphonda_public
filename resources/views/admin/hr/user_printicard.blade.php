@php
    use \App\Http\Controllers\HrController;
     use \App\Model\Users;
     use \App\Model\Master;
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<style>
    .page-break {
           page-break-after: always;     
    }
    @page {
        margin-top:80px;
        footer: page-footer;
        font-family: 'Arial Narrow', Arial, sans-serif;
        font-size: 14px;
}
    .center{
        text-align: center;
    }
    .tablestyle{
        border-collapse: collapse;
        width: 100%;
    }
    .tablestyle, th, td{
        border: 1px solid black;
        padding: 4px;
    }
    .lesswidth{
        width: 35%;
    } 
    .left{
        text-align: left;
        font-weight: 100;
        font-size: 16px;
    }
    .justify{
        text-align: justify;
        font-weight: 100;
        font-size: 16px;
    }
    th{
        font-size: 14px;
    }
    </style>
     <script>
            
    </script>
       
</head>
<body>
    <div >
        
        <div class="center">
            <!-- <h2>ICARD</h3> -->
        </div>

        @foreach($customerdata as $customer)
        @php
        $roles=Master::where('key',$customer->role)->get()->first();
        @endphp

        @if($customer->role!='Mechanic')
        <h3>MANAGEMENT (FRONT)</h3>
        <table class="tablestyle" border="2px solid black"  style="width:450px;background: #E8E3E3;">
            <tr style="background: #ec1c2f;">
                <td style="width:150px;padding:15px;border:0px solid;"><img src="images/hondalogo.png" height="50" width="110"></td>
                <td style=" border: 0px solid;"><h1 style="color:white;">JP Honda</h1></td>
            </tr>
            <tr>
                <td colspan="2" style=" border: 0px solid;"><h3 style="text-align:center;"><u>SERVICE</u></h3></td>
            </tr>
            <tr>
                @if($customer->profile_photo != "" || $customer->profile_photo != null)
                @if (file_exists(public_path().'/upload/adminprofile/'.$customer->profile_photo ))                
                <td colspan="2" style=" border: 0px solid;text-align: center;"><img src="{{ asset('upload/adminprofile/')}}/{{$customer->profile_photo}}" height="150" width="150"></td>
                @else
                 <td colspan="2" style=" border: 0px solid;text-align: center;"><img src="images/avatar.png" height="150" width="150"></td>
                @endif
                @else
                 <td colspan="2" style=" border: 0px solid;text-align: center;"><img src="images/avatar.png" height="150" width="150"></td>      
            @endif
            </tr>
            <tr>
                <td colspan="2" style=" border: 0px solid;"><h3 style="text-align:center;">{{strtoupper($customer->name)}}&nbsp;{{strtoupper($customer->middle_name)}}&nbsp;{{strtoupper($customer->last_name)}}</h3></td>
            </tr>
            <tr>
                <td colspan="2" style=" border: 0px solid;"><h3 style="text-align:center;margin-top:-10">{{strtoupper($roles->value)}}</h3></td>
            </tr>
             <tr>
                <td colspan="2" style=" border: 0px solid;"><h3 style="text-align:center;margin-top:-10">@if($customer->phone)+91 {{strtoupper($customer->phone)}}@endif</h3></td>
            </tr>
            <tr>
                <td colspan="2" style=" border: 0px solid;background: #DCDCDC;"><h3 style="text-align:center;">How  can I help you ?</h3></td>
            </tr>
        </table><br/><br/>
        <div class="page-break"></div>
        <h3>MANAGEMENT (BACK)</h3>
        <table class="tablestyle" border="2px solid black"  style="width:450px;background: #E8E3E3;">
            <tr style="">
                 <td colspan="2" style=" border: 0px solid;text-align: center;padding:15px;"><img src="images/jphonda_logo.png" height="150" width="150"></td>
            </tr>
            <tr>
                 <td colspan="2" style=" border: 0px solid;"><p style="text-align:center;">EMPLOYEE CODE - <b>{{$customer->emp_id}}</b></p></td>
            </tr>
            <tr>
                <td colspan="2" style=" border: 0px solid;"><p style="text-align:center;">DATE OF JOINING – <b>
                    {{$diff = Carbon\Carbon::parse($customer->doj)->isoFormat('Do MMM  YYYY')}} 
               </b></p></td>
            </tr>
            <tr>
                 <td colspan="2" style=" border: 0px solid;text-align:center;"><b style="">JP MOTOR PVT LTD
                </b><br/><p style="text-align:center;text-indent: 20px;">78/1, Baraura Husaain Badi (Next to Charak Hospital), Hardoi Road, Chowk #9935959595, 7317000431-432
                <br/><p style="text-align:center;text-indent: 20px;">HP Petrol Pump, Janki Prasad Agarwal & Sons, Near Daliganj Crossing #7317000421-424
                </p></td>
            </tr>
        </table>
        @else
        <h3>MECHANICS (FRONT)</h3>
        <table class="tablestyle" border="2px solid black"  style="width:450px;background: #E8E3E3;">
            <tr style="background: #ec1c2f;">
                <td style="width:150px;padding:15px;border:0px solid;"><img src="images/hondalogo.png" height="50" width="110"></td>
              <td style=" border: 0px solid;"><h1 style="color:white;">JP Honda</h1></td>
            </tr>
            <tr>
                <td colspan="2" style=" border: 0px solid;"><h3 style="text-align:center;"><u>SERVICE</u></h3></td>
            </tr>
            <tr>
                @if($customer->profile_photo != "" || $customer->profile_photo != null)
                @if (file_exists(public_path().'/upload/adminprofile/'.$customer->profile_photo ))                
                <td colspan="2" style=" border: 0px solid;text-align: center;"><img src="{{ asset('upload/adminprofile/')}}/{{$customer->profile_photo}}" height="150" width="150"></td>
                @else
                 <td colspan="2" style=" border: 0px solid;text-align: center;"><img src="images/avatar.png" height="150" width="150"></td>
                @endif
                @else
                 <td colspan="2" style=" border: 0px solid;text-align: center;"><img src="images/avatar.png" height="150" width="150"></td>      
            @endif
            </tr>
            <tr>
                <td colspan="2" style=" border: 0px solid;"><h3 style="text-align:center;">{{strtoupper($customer->name)}}&nbsp;{{strtoupper($customer->middle_name)}}&nbsp;{{strtoupper($customer->last_name)}}</h3></td>
            </tr>
             <tr>
                <td colspan="2" style=" border: 0px solid;"><h3 style="text-align:center;margin-top:-5">{{strtoupper($roles->value)}}</h3></td>
            </tr>
             <tr>
                <td colspan="2" style=" border: 0px solid;margin-top:-5"><h3 style="text-align:center;">@if($customer->phone)+91 {{strtoupper($customer->phone)}}@endif</h3><br/><br/><br/></td>
            </tr>
        </table><br/>
        <div class="page-break"></div>
        <h3>MECHANICS (BACK)</h3>
            <table class="tablestyle" border="2px solid black"  style="width:450px;background: #E8E3E3;">
            <tr>
                 <td colspan="2" style=" border: 0px solid;text-align: center;padding:15px;"><img src="images/hondalogo.png" height="50" width="50"></td>
            </tr>
            <tr style="background:white;">
                <td style="width:150px;padding:15px;border:0px solid;"><img src="images/mechanics1.PNG" height="150"></td>
                <td style=" border: 0px solid;"><h1 style="font-family:"Comic Sans MS", cursive, sans-serif;">I 'm a DOCTOR of two wheeler</h1></td>
            </tr>
             <tr>
                 <td colspan="2" style=" border: 0px solid;"><p style="text-align:center;">EMPLOYEE CODE - <b>{{$customer->emp_id}}</b></p></td>
            </tr>
             <tr>
                <td colspan="2" style=" border: 0px solid;"><p style="text-align:center;margin-top:-10">DATE OF JOINING – <b>
                    {{$diff = Carbon\Carbon::parse($customer->doj)->isoFormat('Do MMM  YYYY')}} 
               </b></p></td>
            </tr>
            <tr>
                 <td colspan="2" style=" border: 0px solid;text-align:center;"><b style="">JP MOTOR PVT LTD
                </b><br/><p style="text-align:center;text-indent: 20px;">78/1, Baraura Husaain Badi (Next to Charak Hospital), Hardoi Road, Chowk #9935959595, 7317000431-432
                <br/><p style="text-align:center;text-indent: 20px;">HP Petrol Pump, Janki Prasad Agarwal & Sons, Near Daliganj Crossing #7317000421-424
                </p></td>
            </tr>
        </table>
        @endif
        @endforeach
</body>

</html>
