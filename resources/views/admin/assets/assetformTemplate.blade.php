@php
    use \App\Http\Controllers\AssetController;
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<style>
    .page-break {
            page-break-inside: avoid;       
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
            <h2>ASSET RECEIPT FORM</h3>
        </div>
        
        <table class="tablestyle">
            <tr>
                <td class="lesswidth">ASSET RECEIPT FORM NO</td>
                <td><?php echo $form; ?></td>
            </tr>
            <tr>
                <td class="lesswidth">RECEIVED ON DATE</td>
                <td><?php echo $recieved; ?></td>
            </tr>
            <tr>
                <td class="lesswidth"> RECEIVED BY</td>
                <td><?php echo($emp['name']) ?></td>
            </tr>
            <tr>
                <td class="lesswidth">STORE</td>
                <td> <?php echo $store['name']."-".$store['store_type'] ?></td>
            </tr>
        </table>
        <br/>
        <p class="left">Following asset(s) received:</p>
        <table class="tablestyle">
            <tr>
                <th> S. No.</th>
                <th>ASSET CATEGORY</th>
                <th>ASSET NAME</th>
                <th>ASSET CODE</th>
                <th>ASSET MODEL NO.</th>
                <th>ASSET VALUE</th>
            </tr>
            
                <tr>
                    <td class="center"> 1 </td>
                    <td class="center"> <?php echo $category['category_name']; ?> </td>
                    <td class="center"> <?php echo($code['name']) ?></td>
                    <td class="center"> <?php echo($code['asset_code']) ?></td>
                    <td class="center"> <?php echo($code['model_number']) ?></td>
                    <td class="center"><?php echo($code['asset_value']) ?></td>
                </tr>
        </table>
        <br/>
        <p class="left">DECLARATION :</p>
        <p class="justify">
            I acknowledge that I have received the above items that are required in performing regular functions of my designation & it is my responsibility to hold them in good working condition and in order. I am hereby completely responsible for the assets and replacement costs. In case any assets are not used, I promise to return without delay.
        </p>

        <p style="width:50%;float:left;" class="left">Employee Name :<span> <?php echo($emp['name']) ?></span></p>
        <p style="width:50%;" class="left">Head of Department :<span></span></p>

        <p style="width:50%;float:left;" class="left">Sign :<span></span></p>
        <p style="width:50%;" class="left">Sign :<span></span></p>
    </div>
 
    <htmlpagefooter name="page-footer">
        Page {PAGENO} of {nbpg}
    </htmlpagefooter>
    
   
</body>

</html>
