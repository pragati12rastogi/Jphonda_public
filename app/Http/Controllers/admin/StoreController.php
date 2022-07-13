<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;
// use \App\Model\Users;
// use \App\Model\UserLayoutRight;
// use \App\Model\SectionRight;
// use \App\Model\UserSectionRight;
use \App\Model\Store;
use \App\Model\Unload;
use \App\Model\Parts;
use \App\Custom\CustomHelpers;
use DB;
use Auth;
use Hash;
use \Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class StoreController extends Controller
{

    function create_store()
    {
        $data = array(
            'layout'=>'layouts.main'
        );
        return view('admin.create_store',$data);
    }
    function create_parts()
    {
        $load=Unload::all();
        $data = array(
            'load'=>$load,
            'layout'=>'layouts.main'
        );
        return view('admin.create_parts',$data);
    }
    public function create_parts_db(Request $request){
        // print_r($request->input());
        try {
            $this->validate($request,[
                'loadRefNum'=>'required',
                'part_type'=>'required',
                'type'=>'required',
                'status'=>'required',
                'mfh_d'=>'required'
            ],[
                'loadRefNum.required'=> 'This is required.',
                'part_type.required'=> 'This is required.',
                'type.required'=> 'This is required.',
                'status.required'=> 'This is required.',
                'mfh_d.required'=> 'This is required.'
            ]);
            $part = Parts::insertGetId(
                [
                    'id'=>NULL,
                    'unload_id'=>$request->input('loadRefNum'),
                    'model_category'=>$request->input('model_cat'),
                    'model_name'=>$request->input('model_name'),
                    'model_variant'=>$request->input('model_var'),
                    'color_code'=>$request->input('color'),
                    'part_number'=>$request->input('part'),
                    'type'=>$request->input('type'),
                    'part_type'=>$request->input('part_type'),
                    'status'=>$request->input('status'),
                    'mfh_year'=>'2019',
                    'mfh_date'=>date("Y-m-d", strtotime($request->input('mfh_d'))),
                ]
            );
            if($part==NULL) 
            {
                DB::rollback();
                return redirect('/admin/stock/parts/create')->with('error','Some Unexpected Error occurred.');
            }
            else{
                return redirect('/admin/stock/parts/create')->with('success','Successfully Created Parts Form.'); 
             }    
        
    }  catch(\Illuminate\Database\QueryException $ex) {
        return redirect('/admin/stock/parts/create')->with('error','some error occurred'.$ex->getMessage());
    }
    }
    public function parts_list(){
        $data=array('layout'=>'layouts.main');
        return view('admin.parts_summary', $data);   
    }

    public function parts_list_api(Request $request)
    {        
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
           
            $api_data= Parts::leftJoin('unload','parts.unload_id','unload.id')
            ->select(
                'parts.id',
                'parts.model_category',
                'parts.model_name',
                'parts.model_variant',
                'parts.color_code',
                'parts.part_number',
                'parts.type',
                'parts.part_type',
                'parts.status',
                'parts.mfh_year',
                'parts.mfh_date',
                'unload.load_referenace_number'
            );
            if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('parts.model_category','like',"%".$serach_value."%")
                    ->orwhere('parts.model_name','like',"%".$serach_value."%")
                    ->orwhere('parts.model_variant','like',"%".$serach_value."%")
                    ->orwhere('parts.color_code','like',"%".$serach_value."%")
                    ->orwhere('parts.part_number','like',"%".$serach_value."%")
                    ->orwhere('parts.type','like',"%".$serach_value."%")
                    ->orwhere('parts.part_type',"%".$serach_value."%")
                    ->orwhere('parts.status','like',"%".$serach_value."%")
                    ->orwhere('parts.mfh_year','like',"%".$serach_value."%")
                    ->orwhere('parts.mfh_date','like',"%".$serach_value."%")
                    ->orwhere('unload.load_referenace_number','like',"%".$serach_value."%")
                    
                    ;
                });
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                    'parts.id',
                'parts.model_category',
                'parts.model_name',
                'parts.model_variant',
                'parts.color_code',
                'parts.part_number',
                'parts.type',
                'parts.part_type',
                'parts.status',
                'parts.mfh_year',
                'parts.mfh_date',
                'unload.load_referenace_number'
                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('parts.id','desc');      
        
        $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array); 
        
  
    }
    public function store_list(){
        $data=array('layout'=>'layouts.main');
        return view('admin.store_summary', $data);   
    }

    public function store_list_api(Request $request)
    {        
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
           
            $api_data= Store::select(
                'store.id',
                'store.name',
                'store.mobile',
                'store.city',
                'store.address',
                'store.store_type'
            );
            if(!empty($serach_value))
            {
                $api_data->where(function($query) use ($serach_value){
                    $query->where('store.name','like',"%".$serach_value."%")
                    ->orwhere('store.city','like',"%".$serach_value."%")
                    ->orwhere('store.mobile','like',"%".$serach_value."%")
                    ->orwhere('store.address','like',"%".$serach_value."%")
                    ->orwhere('store.store_type','like',"%".$serach_value."%")
                  
                    
                    ;
                });
            }
            if(isset($request->input('order')[0]['column']))
            {
                $data = [
                    'store.id',
                    'store.name',
                    'store.mobile',
                    'store.store_type',
                    'store.city',
                    'store.address'
                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('id','desc');      
        
        $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array); 
        
  
    }
    function create_store_db(Request $request)
    {
        $this->validate($request,[
            'name'=>'required',
            'mobile'=>'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10|max:10',
            'city'=>'required',
            'address'=>'required',
            'store_type'=>'required'
        ],[
            'name.required'=> 'Name is required.',
            'mobile.required'=> 'Mobile No is required.',
            'city.required'=> 'City is required.',
            'address.required'=> 'Address is required.',
            'store_type.required'=> 'Store Type is required.',
            'mobile.regex'=> 'Mobile No contains digits only.',
            'mobile.max'=> 'Mobile No may not be greater than 10 digits.',
            'mobile.min'=> 'Mobile No must be at least 10 digits.',
        ]);
        try{

            if (!empty($request->input('mobile'))) {
                  $validator = Validator::make($request->all(),[
                    'mobile'=>'required|regex:/^([6-9][0-9]{9}[\s\-\+\(\)]*)$/|min:10|max:10',
                    ],
                    [
                            'mobile.required'=> 'This field is required.',
                            'mobile.regex'=> 'Mobile No contains digits only.',
                            'mobile.max'=> 'Mobile No may not be greater than 10 digits.',
                            'mobile.min'=> 'Mobile No must at least 10 digits.',
                    ]);

                    if ($validator->fails()) {
                        return back()->withErrors($validator)->withInput();
                    }
            }

        $id=Auth::id();
        $date = Carbon::now();

        $id = Store::insertGetId(
            [
                'id'=>NULL,
                'name'=>$request->input('name'),
                'mobile'=>$request->input('mobile'),
                'city'=>$request->input('city'),
                'address'=>$request->input('address'),
                'store_type'=>$request->input('store_type'),
                'created_at'=>$date,
                'created_by'=>$id
            ]
        );
        } catch(\Illuminate\Database\QueryException $ex) {
                return redirect('/admin/store/create')->with('error','some error occurred'.$ex->getMessage())->withInput();
        }
         CustomHelpers::userActionLog($action='Scheme Update',$id,0);
        return redirect('/admin/store/create')->with("success","Store created successfully.");
    } 
    public function admin_update()
    {
         
    }
    public function permission_denied()
    {
         $data=array('layout'=>'layouts.main');
        return view('sections.user.admin_permssion_denied', $data);  
    }
    public function permission($id){
       /*
        1=>View,2=>Create,3=>Update,4=>Print,5=>Summary,6=>Import,7=>Export,8=>Other
       */
        if($id!=Auth::id())
        {
            $menudata = SectionRight::leftJoin('user_section_rights',function($join) use ($id){
                $join->on(DB::raw('user_section_rights.user_id = '.$id.' and section_rights.id'),'=','user_section_rights.section_id');
            })
            
            ->where('show_permission','=',1)
    //        ->where('section_rights.linkfor','<>',0)
            ->select(['section_rights.*','user_section_rights.user_id'])
            ->orderBy('showorder')
            ->orderBy('linkfor')
            ->get()->toarray();
            $menudata = CustomHelpers::menuTree($menudata);
                $data=array(
                    'layout'=>'layouts.main',
                    'id'=>$id,
                    'menudata'=>$menudata,
                );
                return view('admin.admin.admin_permission',$data);   
        }
        else
            return abort('403',"You can not change your own Access Rigths.");
    }
    public function getadminpermission($id){
        DB::enableQueryLog();
        $menudata = SectionRight::leftJoin('user_section_rights',function($join) use ($id){
            $join->on(DB::raw('user_section_rights.user_id = '.$id.' and section_rights.id'),'=','user_section_rights.section_id');
        })
        ->where('show_permission','=',1)
        ->select([
            'section_rights.permission_pid as pid',
            'section_rights.name',
            'section_rights.map_id',
            'section_rights.showorder',
            'section_rights.linkfor',
            'section_rights.show_menu',
            'section_rights.id',
            'user_section_rights.user_id'
            ])
        ->orderBy('showorder')
        ->orderBy('linkfor')
        ->get()->toarray();
    
        $menudata = CustomHelpers::menuTree($menudata);
        //print_r($menudata); die();
        $resp = '<table class="table table-condensed ">
            <thead>
                <tr>
                    <th class="info">Name</th>
                    <th class="info">View</th>
                    <th class="info">Create</th>
                    <th class="info">Update</th>
                    <th class="info">Print</th>
                    <th class="info">Summary</th>
                    <th class="info">Import</th>
                    <th class="info">Export</th>
                    <th class="info">Other</th>
                </tr>
            </thead>
        <tbody>';
        $menuitems=array_keys($menudata); 
//        $resp =$resp .$this->generate_table($menudata);
        $indent='';
        $class='checkboxLeader';
        $master_class='';
        for($i=0;$i<count($menuitems);$i++)
            $resp =$resp .$this->generate_table(array('2'=> $menudata[$menuitems[$i]]),$indent,$class,$master_class);
        $resp = $resp.'</tbody>
                    </table>';
        return $resp;
    }
    
    function generate_table($menudata,$indent,$class)
    {
     //   echo '<>';
        $resp='';
     
        foreach ($menudata as $menu)
        {
            if($menu['map_id']=='all')
                $resp = $resp.'<input type="hidden" name="menu[]" value="'.$menu['id'].'">';
            else
            {
               
                    $val1 = $menu['id'];
                    $checked1='';
                    if($menu['map_id']!=0 )
                        $val1 = $val1.",".$menu['map_id'];
                    if($menu['user_id'] != NULL)
                        $checked1='checked';
                    $inp='<input type="checkbox"  class="'.$class.'" id="'.str_replace(['.', ' ', '/'],'_',$menu['name']).'" name="menu[]" value="'.$val1.'" '.$checked1.'>';
                
                $resp = $resp.'<tr><td style="display:inline">'.$indent.$inp.'<label style="display:inline"> '.$menu['name'].'</label></td>';
                if(isset($menu['children']))
                {
                    $var=0;
                    $menukey = array_keys($menu['children']);
                    for($i=0;$i<count($menu['children']);)
                    {
                        if(isset($menu['children'][$menukey[$i]]['children']))
                        {
                            $indent=$indent.'&nbsp&nbsp&nbsp&nbsp&nbsp';
                            $class=$class.' '.str_replace(['.', ' ', '/'],'_',$menu['name']);
                            $menuitems=array_keys($menu['children']);
                            for($i=0;$i<count($menuitems);$i++)
                            {
                                $resp =$resp .$this->generate_table(array($menu['children'][$menuitems[$i]]),$indent,$class);
                            }  
                            return $resp;
                        }
                        else
                        {
                            
                            if( $var==9)
                            {
                                $var=0;
                                $resp = $resp."</tr><tr> <td >".$menu['name'];
                            }
                            $var = $var+1;
                            if($menu['children'][$menukey[$i]]['linkfor']!=$var){
                                $resp = $resp.'<td></td>';
                            }   
                            else if( $menu['children'][$menukey[$i]]['linkfor']==$var)
                            {
                                $resp = $resp.'<td>';
                                do{
                                $val = $menu['children'][$menukey[$i]]['id'];
                                if($menu['children'][$menukey[$i]]['map_id']!=0 )
                                    $val = $val.",".$menu['children'][$menukey[$i]]['map_id'];
                                $checked='';
                                if($menu['children'][$menukey[$i]]['user_id'] != NULL)
                                    $checked='checked';
                                $resp = $resp.'<input type="checkbox" class="'.str_replace(['.', ' ', '/'],'_',$menu['name']).'" name="menu[]" value="'.$val.'" '.$checked.' title="'.$menu['children'][$menukey[$i]]['name'].'">';
                                $i++;
                                }while( $i<count($menu['children']) && $menu['children'][$menukey[$i]]['linkfor'] ==  $menu['children'][$menukey[$i-1]]['linkfor']);
                                $resp = $resp.'</td>';
                            }
                        }
                    }                           
                    $resp = $resp.'</tr>'; 
                }
                else 
                {
                    $resp = $resp.'</tr>';
                }    
            }
        
        }
        return $resp;
    }
    public function setpermission(Request $request){
        ini_set('max_execution_time', 18000);

        $id = $request->input('id');
        $menudata = $request->input('menu');
        DB::beginTransaction();
        DB::enableQueryLog();
        try{
            if($menudata)
            {
                UserSectionRight::where('user_id','=',$id)
                    ->whereNotIn('section_id',$menudata)
                    ->delete();
                foreach ($menudata as $key => $value) {
                    $str = explode(',',$value);
                    foreach($str as $key1=>$val)
                    {
                        $userlayout = UserSectionRight::updateOrCreate(['section_id'=>$val,'user_id'=>$id]);
                        $userlayout->section_id = $val;
                        $userlayout->user_id = $id;
                        $userlayout->allowed = 1;
                        $userlayout->save();
                    }
                }
            }
            else {
                UserSectionRight::where('user_id','=',$id)
                ->delete();    
            }
                
                DB::commit();
                return redirect('/admin/permission/'.$id)->with('success','Permission has been set successfully.'); 
        }catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return redirect('/admin/permission/'.$id)->with('error','Something went wrong.'.$ex->getMessage());
        }
    }
    public function admin(){
        $data=array('layout'=>'layouts.main');
        return view('admin.admin.admin', $data);   
    }

    public function admindata(Request $request)
    {        
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;
        
        $userlog = Users::where('id','>','0');
        if(!empty($serach_value))
        {
            $userlog = $userlog->where('email','LIKE',"%".$serach_value."%")
                        ->orwhere('name','LIKE',"%".$serach_value."%")
                        ->orwhere('phone','LIKE',"%".$serach_value."%");
        }
        
        $count = $userlog->count();
        $userlog = $userlog->offset($offset)->limit($limit);

        if(isset($request->input('order')[0]['column'])){
            $data = ['id','name','email','user_type','created_at'];
            $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
            $userlog->orderBy($data[$request->input('order')[0]['column']], $by);
        }
        else
        {
            $userlog->orderBy('id','desc');
        }
        $userlogdata = $userlog->select('id','name','email','user_type','created_at')->get();
        
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count ;
        $array['data'] = $userlogdata; 
        return json_encode($array);
    }

    public function admin_view($id){
        $data = Users::where('id','=',$id)->get([
            'name',
            'email',
            'phone',
            'user_type',
            'profile_photo',
            'created_at',
            'updated_at',

        ])->first();
    $data=array(
        'layout'=>'layouts.main',
        'data' => $data
    );
    return view('admin.admin.admin_view', $data);   
      
    }

    public function update_store($id) {
        
        $store = Store::where('id',$id)->get()->first();
        if(!$store){
            return redirect('/admin/store/list')->with('error','Id not exist.');
        }
       else{

           $data=array(
            'id'=>$id,
            'store'=>$store,
            'layout' => 'layouts.main'
           );
            return view('admin.master.store_update',$data);
      }        
    } 

    public function update_store_db(Request $request,$id){

         $this->validate($request,[
            'name'=>'required',
            'mobile'=>'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10|max:10',
            'city'=>'required',
            'address'=>'required',
            'store_type'=>'required'
        ],[
            'name.required'=> 'Name is required.',
            'mobile.required'=> 'Mobile No is required.',
            'city.required'=> 'City is required.',
            'address.required'=> 'Address is required.',
            'store_type.required'=> 'Store Type is required.',
            'mobile.regex'=> 'Mobile No contains digits only.',
            'mobile.max'=> 'Mobile No may not be greater than 10 digits.',
            'mobile.min'=> 'Mobile No must be at least 10 digits.',
        ]);
        try{

            if (!empty($request->input('mobile'))) {
                  $validator = Validator::make($request->all(),[
                    'mobile'=>'required|regex:/^([6-9][0-9]{9}[\s\-\+\(\)]*)$/|min:10|max:10',
                    ],
                    [
                            'mobile.required'=> 'This field is required.',
                            'mobile.regex'=> 'Mobile No contains digits only.',
                            'mobile.max'=> 'Mobile No may not be greater than 10 digits.',
                            'mobile.min'=> 'Mobile No must at least 10 digits.',
                    ]);

                    if ($validator->fails()) {
                        return back()->withErrors($validator)->withInput();
                    }
            }

        $auth_id=Auth::id();
        $date = Carbon::now();

        $data = Store::where('id',$id)->update([
                           'name'=>$request->input('name'),
                            'mobile'=>$request->input('mobile'),
                            'city'=>$request->input('city'),
                            'address'=>$request->input('address'),
                            'store_type'=>$request->input('store_type'),
                            'created_at'=>$date,
                            'created_by'=>$auth_id
                        ]);

            if($data==NULL){
                DB::rollback();
                return redirect('/admin/master/store/update/'.$id)->with('error','Some Unexpected Error occurred.');
            }else{

                 CustomHelpers::userActionLog($action='Scheme Update',$id,0);
                 return redirect('/admin/master/store/update/'.$id)->with('success','Successfully Updated Store.'); 
            }

        } catch(\Illuminate\Database\QueryException $ex) {
                return redirect('/admin/master/store/update/'.$id)->with('error','some error occurred'.$ex->getMessage())->withInput();
        }
    }

}


