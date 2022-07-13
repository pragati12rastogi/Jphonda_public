@extends($layout)

@section('title', __('Penalty'))

@section('js')
<script src="/js/pages/hr.js"></script>
<script>

 $('#penalty').on('change' , function (){
        var ass = $('#penalty').val(); 

        $('#ajax_loader_div').css('display','block');
       filter_user(ass);
     });

 function filter_user(ass){
   var assets=ass;
     $('#ajax_loader_div').css('display','block');
     $.ajax({
         type:"GET",
         url:"/admin/hr/filter/penalty/api/",
         data:{'assets':assets},
         dataType:'json',
         success: function(result){
             if (result) {
                var value=result.amount;
                        $("#amount").val(value);
                         $('#ajax_loader_div').css('display','none');
                     }
         }
     })
  }
</script>
@endsection

@section('breadcrumb')
    <li><a href="#"><i class=""></i> {{__('Penalty')}} </a></li>
     <style>
        .nav1>li>a {
            position: relative;
            display: block;
            padding: 10px 34px;
            background-color: white;
            margin-left: 10px;
        }
        /* .nav1>li>a:hover {
            background-color:#87CEFA;
        
        } */
        </style>
@endsection
@section('main_section')
    <section class="content"> 
         <div id="app">
                    @include('admin.flash-message')
                    @yield('content')
            </div>

               <form id="my-form" action="/admin/hr/user/penalty/form" method="POST">
                @csrf
                
            <!-- general form elements -->
            <div class="box box-primary" style="padding-bottom: 25px;">
                <div class="box-header with-border">
<!--         <h3 class="box-title">{{__('customer.mytitle')}} </h3>-->
                </div>  
                <div class="row">
                        <div class="col-md-6">
                            <label>Employee Name<sup>*</sup></label>
                           <select class="input-css employee select2" id="employee" style="padding-top:2px" name="employee" >
                        <option value="">--Select Employee --</option>
                            @foreach($users as $item)
                            <option value="{{$item->id}}" {{(old('employee') == $item->id)? 'selected': ''}}>{{$item->name}} {{ $item->middle_name}} {{$item->last_name}}</option>
                            @endforeach
                    </select>
                            {!! $errors->first('employee', '<p class="help-block">:message</p>') !!}
                            <br>
                        </div>
                </div>
                <div class="row">
                        <div class="col-md-6">
                            <label>Penalty <sup>*</sup></label>
                           <select class="input-css penalty  select2" id="penalty" style="padding-top:2px" name="penalty" >
                        <option value="">--Select Penalty --</option>
                            @foreach($penalty as $item)
                            <option value="{{$item->id}}" {{(old('penalty') == $item->id)? 'selected': ''}}>{{$item->name}}</option>
                            @endforeach
                    </select>
                            {!! $errors->first('penalty', '<p class="help-block">:message</p>') !!}
                            <br>
                        </div>
                         <div class="col-md-6">
                             <label for="">Amount</label>
                            <input type="text" readonly name="amount" value="{{old('amount')}}" id="amount" class="amount input-css">
                         </div>
                </div>
                    <div class="row">
                        <div class="col-md-10">
                            <label>Remark</label>
                            <textarea class="input-css" id="remark" name="remark" rows="3">{{old('remark')}}</textarea>
                            {!! $errors->first('remark', '<p class="help-block">:message</p>') !!}
                        </div>
                </div><br/>
                </div>
                
                <div class="submit-button">
                <button type="submit" class="btn btn-primary">Submit</button>
                    
                </div>
                 </form>  
                 
            </div>
        
      </section>
@endsection