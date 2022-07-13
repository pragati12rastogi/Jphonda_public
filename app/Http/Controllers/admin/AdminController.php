<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;
use \App\Model\Users;
use \App\Model\Store;
use \App\Model\UserLayoutRight;
use \App\Model\SectionRight;
use \App\Model\UserSectionRight;
use \App\Model\UserMaster;
use \App\Model\VehicleAddonStock;
use \App\Model\Settings;
use \App\Custom\CustomHelpers;
use DB;
use Auth;
use File;
use Hash;
class AdminController extends Controller
{

    public function owner_manual(){
        $unload_addon = DB::select(DB::raw("select concat(ua.addon_name,'_',Lower(REPLACE(ua.model,' ','_'))) as addon ,sum(qty) as qty,unload.store
                                from unload_addon ua
                                    LEFT JOIN unload on unload.id = ua.unload_id
                                where ua.model != ''  
                                GROUP BY addon, store
                                ORDER BY store ASC")
                            );
        // print_r($unload_addon);die;

        $stock_move_from = DB::select(DB::raw("select addon,sum(IF(qty > owner_manual,owner_manual,qty)) as qty, from_store as store_id from
        (SELECT sm.from_store,sm.to_store,smp.id as process_id, 
                concat('owner_manual','_',REPLACE(lower(product.model_name),' ','_')) as addon,
                IF(smp.owner_manual = 0,0,count(product.model_name)) as qty, 
                 smp.owner_manual
                from stock_movement sm 
                join stock_movement_details smd on smd.stock_movement_id = sm.id
                join stock_movement_process smp on smp.id = smd.stock_movement_process_id
                join product on product.id = smd.product_id
                where smd.status in ('process','done')
                and smd.stock_movement_process_id <> 0
                and sm.quantity > 0
                GROUP BY addon, smp.id
                ORDER by process_id
         ) owner_manual
         GROUP by addon,from_store"));
        $stock_move_to = DB::select(DB::raw("select addon,sum(IF(qty > owner_manual,owner_manual,qty)) as qty, to_store as store_id from
        (SELECT sm.from_store,sm.to_store,smp.id as process_id, 
                concat('owner_manual','_',REPLACE(lower(product.model_name),' ','_')) as addon,
                IF(smp.owner_manual = 0,0,count(product.model_name)) as qty, 
                 smp.owner_manual
                from stock_movement sm 
                join stock_movement_details smd on smd.stock_movement_id = sm.id
                join stock_movement_process smp on smp.id = smd.stock_movement_process_id
                join product on product.id = smd.product_id
                where smd.status in ('process','done')
                and smd.stock_movement_process_id <> 0
                and sm.quantity > 0
                GROUP BY addon, smp.id
                ORDER by process_id
         ) owner_manual
         GROUP by addon,to_store"));
        $sale = DB::select(DB::raw("SELECT concat('owner_manual_',REPLACE(lower(prod.model_name),' ','_')) as addon, count(prod.id) as qty, sale.store_id from product prod 
        left join sale on sale.product_id = prod.id
        where sale.status in ('done','pending')
        GROUP BY addon,store_id
        ORDER BY store_id"));

        $vad = [];
        $vas = VehicleAddonStock::where('vehicle_addon_key','owner_manual')
                                ->orderBy('store_id')
                                ->get()->toArray();
        foreach($unload_addon as $key => $val){
            $index = $val->addon.'-'.$val->store;
            
            $vad[$index] = [
                'addon' =>  $val->addon,
                'qty'   =>  $val->qty,
                'sale_qty'  =>  0,
                'store_id'  =>  $val->store
            ];
        }
        // print_r($unload_addon);
        // print_r($vad);
        // die;
        foreach($stock_move_from as $key => $val){
            $index = $val->addon.'-'.$val->store_id;
            if(!isset($vad[$index])){
                $vad[$index] = [
                    'addon' =>  $val->addon,
                    'qty'   =>  0-$val->qty,
                    'sale_qty'  =>  0,
                    'store_id'  =>  $val->store_id
                ];
            }else{
                $old_qty = $vad[$index]['qty'];
                $vad[$index]['qty'] = $old_qty-$val->qty;
            }
        }
        // print_r($vad);
        // die;
        // print_r($stock_move_from);die;
        foreach($stock_move_to as $key => $val){
            $index = $val->addon.'-'.$val->store_id;
            if(!isset($vad[$index])){
                $vad[$index] = [
                    'addon' =>  $val->addon,
                    'qty'   =>  $val->qty,
                    'sale_qty'  =>  0,
                    'store_id'  =>  $val->store_id
                ];
            }else{
                $old_qty = $vad[$index]['qty'];
                $vad[$index]['qty'] = $old_qty+$val->qty;
            }
        }
        // print_r($stock_move_to);die;
        // print_r($vad);die;
        foreach($sale as $key => $val){
            $index = $val->addon.'-'.$val->store_id;
            if(!isset($vad[$index])){
                $vad[$index] = [
                    'addon' =>  $val->addon,
                    'qty'   =>  0-$val->qty,
                    'sale_qty'  =>  $val->qty,
                    'store_id'  =>  $val->store_id
                ];
            }else{
                $old_qty = $vad[$index]['qty'];
                $vad[$index]['qty'] = $old_qty-$val->qty;
                $vad[$index]['sale_qty'] = $val->qty;
            }
        }

        // stock_move_addon table data
        $sma = DB::select(
            DB::raw("select process_id ,addon,sum(IF(qty > owner_manual,owner_manual,qty)) as qty from
            (SELECT sm.from_store,sm.to_store,smp.id as process_id, 
                    concat('owner_manual','_',REPLACE(lower(product.model_name),' ','_')) as addon,
                    IF(smp.owner_manual = 0,0,count(product.model_name)) as qty, 
                     smp.owner_manual
                    from stock_movement sm 
                    join stock_movement_details smd on smd.stock_movement_id = sm.id
                    join stock_movement_process smp on smp.id = smd.stock_movement_process_id
                    join product on product.id = smd.product_id
                    where smd.status in ('process','done')
                    and smd.stock_movement_process_id <> 0
                    and sm.quantity > 0
                    GROUP BY addon, smp.id
                    ORDER by process_id
             ) owner_manual
             GROUP BY addon, process_id
             HAVING sum(IF(qty > owner_manual,owner_manual,qty)) > 0
             order by process_id")
        );
        
        // print_r($sma);die;

        $data = array(
            'vad' => $vad,
            'vas' => $vas,
            'sma'   =>  $sma,
            'layout'=>'layouts.main'
        );
        return view('admin.owner_manual',$data);
    }

    function create()
    {
        $depart = array();//Department::all();
        $store = Store::all();
        $role = UserMaster::where('type','user_role')->get();
        $data = array(
            'role' => $role,
            'dept'=>$depart,
            'store'=>$store,    
            'layout'=>'layouts.main'
        );
        return view('admin.admin.create',$data);
    }    
    function create_db(Request $request)
    {
        $this->validate($request,[
            'username'=>'required',
            'email'=>'required',
            'pass'=>'required|min:6',
            're_pass'=>'required_with:pass|same:pass',
            'user_type'=>'required',
            'phone'=>'required|digits:10|unique:users,phone',
            'landline'=>'nullable|numeric',
            'profile_picture' => 'image|max:2048000'
        ],[
            'username.required'=> 'User Name is required.',
            'email.required'=> 'Email is required.',
            'email.email'=> 'Email must be of valid  format.',
            'pass.required'=> 'Password is required.',
            'pass.min'=> 'Password min lenght is :min.',
            're_pass.required_with'=> 'Confirm Password is required with Password.',
            're_pass.same'=> 'Confirm Password should be same as Password.',
            'user_type.required'=> 'User Type is required.',
            'phone.required'=> 'Phone Number is required.',
            'landline.digits'=> 'Landline Number contains digits only.',
            'phone.digits'=> 'phone Number contains digits only.',
            'phone.unique'=> 'phone Number already taken.'

        ]);
        if($request->hasFile('profile_picture'))
        {
            $file = $request->file('profile_picture');
            $destinationPath = public_path().'/upload/adminprofile';
            $ts=date('Y-m-d G:i:s');
            $filename=$file->getClientOriginalName();
            $file->move($destinationPath,$filename);

        }else{
              $filename='avatar.png';
        }
            $store_id = implode(',', $request->input('store'));          
            $id = Users::insertGetId(
                [
                    'id'=>NULL,
                    'status'=>'1',
                    'store_id'=> $store_id,
                    'created_at'=>date('Y-m-d G:i:s'),
                    'user_type'=>$request->input('user_type'),
                    'password'=>Hash::make($request->input('pass')),
                    'name'=>$request->input('username'),
                    'role'=> $request->input('role'),
                    'profile_photo'=>$filename,
                    'email'=>$request->input('email'),
                    'phone'=>$request->input('phone'),
                    //'home_landline'=>$request->input('landline'),
                ]
            );  
            return redirect('/admin/admin/permission/'.$id)->with("success","User registered successfully.Pls set user acesss permission.");  
     
        
    } 
    public function admin_update($id,$type="admin") {
        $authUser = Users::where('id',Auth::id())->first();
        $depart = array();//Department::all();
        $detail=Users::where('id',$id)->get();
        $role = UserMaster::where('type','user_role')->get();
        $store=Store::all();
       if($detail->toArray()){
        $data = array(
            'dept'=>$depart,
            'detail'=>$detail,
            'id'=>$id,
            'store'=>$store,    
            'role' =>  $role,
            'authUser' => $authUser,
            'layout'=>'layouts.main',
            'type'=>$type
        );
        // return $detail;
        return view('admin.admin.update_admin', $data);  
       }
       else{
        return redirect('/admin/admin/create/')->with('error','No Form With this ID Exist.');
       }
    }
    public function admin_update_db(Request $request,$id){
        $ImageSize = Settings::where('name','image_size')->get()->first();
        $size = $ImageSize['value'];
         if(!isset($size)){
              return redirect('/admin/admin/update/'.$id)->with('error','Image Size For Validation Master Not Found.');
        }
        try {
            $arr = []; $arr_msg = [];
            $user_type = Users::where('id',Auth::id())->first('user_type')->user_type;
            $role = Users::where('id',Auth::id())->first('role')->role;
            $store = Users::where('id',Auth::id())->first('store_id')->store_id;
            if($user_type == 'superadmin' || $role == 'Superadmin')
            {
                $arr = ['user_type'=>'required',
                        'role'=>'required',];
                $arr_msg = [
                    'role.required'=> 'Role is required.',
                    'user_type.required'=> 'User Type is required.',
                ];
            }

            $this->validate($request,array_merge($arr,[
                'username'=>'required',
                // 'email'=>'required',
                'store'=>'required',
                'phone'=>'required|digits:10|unique:users,phone,'.$id,
                'profile_picture' => 'image|max:'.$size.''
            ]),array_merge($arr_msg,[
                'username.required'=> 'User Name is required.',
                // 'email.required'=> 'Email is required.',
                // 'email.email'=> 'Email must be of valid  format.',
                // 'profile_picture.max' => "Maximum file size to upload is 8MB (8192 KB).",
                'store.required'=> 'Store is required.',
                'phone.required'=> 'Phone Number is required.',
                'phone.digits'=> 'phone Number contains digits only.',
                'phone.unique'=> 'phone Number already taken.'
            ]));

            $image_name=$request->input('hidden_image');
            $file=$request->file('profile_picture');
           
            if($file != ''){
              
                $request->validate([
                   'profile_picture'=>'required|image|max:'.$size.''
                ]);
                
                $filename=$file->getClientOriginalName();
                $destinationPath = public_path().'/upload/adminprofile';
                
                if(File::exists($destinationPath))
                    File::delete($destinationPath,$filename); 
                    $file->move($destinationPath,$filename);      
                    
            } else{
            $filename=$image_name;
            }
            if($request->input('type')=='admin'){
              $redirect_to='/admin/admin/update/'.$id;
            }else{
              $redirect_to='/admin/profile/update/';
            }

            if($user_type == 'superadmin' || $role == 'Superadmin') {
                $usertype = $request->input('user_type');
                $userrole = $request->input('role');
                $store_id = implode(',', $request->input('store'));
            }else{
                $usertype = $user_type;
                $userrole = $role;
                $store_id = $store;
            }

                
                $data1 = [];
                if(!empty($arr))
                {
                    $data1 = [
                        'user_type'=> $usertype,
                        'role'=> $userrole,
                    ];
                }
                if (!empty($request->input('password'))) {
                    $data1 = ['password' => Hash::make($request->input('password'))];
                }
                $user = Users::where('id',$id)->update(
                    array_merge($data1,
                    [
                        'name'=>$request->input('username'),
                        'profile_photo'=>$filename,
                        'email'=>$request->input('email'),
                        'phone'=>$request->input('phone'),
                        'store_id'=>$store_id,
                        'status'=>$request->input('status'),
                    ])
                );
                if($user==NULL) 
                {
                    DB::rollback();
                    return redirect($redirect_to)->with('error','Some Unexpected Error occurred.');
                }
                else{
                    CustomHelpers::userActionLog($request->input()['update_reason'],$id,"User Updated");
                    return redirect($redirect_to)->with('success','User updated successfully.'); 
                 }    
            
        }  catch(\Illuminate\Database\QueryException $ex) {
            return redirect($redirect_to)->with('error','some error occurred'.$ex->getMessage());
        }
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
        $user = Auth::user();
        $user_type = $user['user_type'];
        $user_role = $user['user_role'];
        
        if($id!=Auth::id() || ($user_type=="superadmin" && $user_role="Superadmin"))
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
                return redirect('/admin/admin/permission/'.$id)->with('success','Permission has been set successfully.'); 
        }catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return redirect('/admin/admin/permission/'.$id)->with('error','Something went wrong.'.$ex->getMessage());
        }
    }
    public function admin(){

         $store = Store::whereIn('id',CustomHelpers::user_store())
                    ->select('id',DB::raw('concat(store.name,"-",store.store_type) as name'))->get();

        $data = array(
            'store'=>$store,
            'layout'=>'layouts.main'
        );
        return view('admin.admin.admin', $data);   
    }

    public function admindata(Request $request) {        
       $search = $request->input('search');
        $store_name = $request->input('store_name');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start;
        $limit =  empty($limit) ? 10 : $limit;

            $store = Store::whereIn('id',CustomHelpers::user_store())
                    ->select('id')->get()->toArray();

            $api_data = Users::whereIn('users.store_id',$store)
                    ->whereIn('users.user_type',['superadmin','admin'])
                    ->select('users.id',
                            DB::raw('concat(users.name," ",ifnull( users.middle_name," ")," ",ifnull( users.last_name," ")) as name'),
                            'users.emp_id',
                            'users.email',
                            'users.phone',
                            'users.user_type',
                            'users.status',
                            'users.role',
                            'users.created_at'
                          );
            
            if(!empty($serach_value))
            {
               $api_data->where(function($query) use ($serach_value){
                        $query->where('users.name','like',"%".$serach_value."%")
                        ->orwhere('users.email','like',"%".$serach_value."%")
                        ->orwhere('users.phone','like',"%".$serach_value."%")
                        ->orwhere('users.user_type','like',"%".$serach_value."%")
                        ->orwhere('users.status','like',"%".$serach_value."%")
                        ->orwhere('users.role','like',"%".$serach_value."%")
                        ->orwhere('users.created_at','like',"%".$serach_value."%");
                    });
               
            }
             if(!empty($store_name))
            {
               $api_data->where(function($query) use ($store_name){
                        $query->where('users.store_id','like',"%".$store_name."%");
                    });
            }

            if(isset($request->input('order')[0]['column'])) {
                $data = [
                    'users.emp_id',
                    'users.name',
                    'users.email',
                    'users.phone',
                    'users.user_type',
                    'users.status',
                    'users.role',
                    'users.created_at'
                ];
                
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $api_data->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $api_data->orderBy('users.emp_id','asc');   
        
        $count = count( $api_data->get()->toArray());
        $api_data = $api_data->offset($offset)->limit($limit)->get()->toArray();       
        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $api_data; 
        return json_encode($array);
    }

    public function admin_view($id){
        $data = Users::where('id','=',$id)->get([
             DB::raw('concat(name," ",ifnull(middle_name," ")," ",ifnull(last_name," ")) as name'),
            'email',
            'phone',
            'user_type',
            'profile_photo',
            'created_at',
            'updated_at',

        ])->first();
    $data = array(
        'layout'=>'layouts.main',
        'data' => $data
    );
    return view('admin.admin.admin_view', $data);   
      
    }

    public  function userupdate() {  
        $users = Users::whereIn('store_id',CustomHelpers::user_store())->where('user_type','=','user')->where('status','!=','2')->get();
        $data = array(   
            'users'=>$users, 
            'layout'=>'layouts.main'
        );
        return view('admin.admin.user_update',$data);
    }  

public function userupdate_db(Request $request)
    {   
        $userid = $request->input('userId');
        $password = $request->input('password');

        $getcheck =  Users::where('id',$userid)->first();
        if($getcheck)
        {
           $update = Users::where('id',$userid)
                    ->update([
                         'user_type'  =>  'admin',
                         'password'=>Hash::make($password)
                ]); 
                    $queries = DB::getQueryLog();

            if($update)
            {
                return 'success';
            }
        }
        else{
            return 'User not exists';
        }
        return 'error';
    }

    // public function update_password($id) {
    //     $users = Users::where('id',$id)->get()->first();
    //      $data = array(   
    //         'users' => $users, 
    //         'layout' => 'layouts.main'
    //     );
    //     return view('admin.admin.update_password',$data);
    // }

    // public function UpdatePassword_DB(Request $request) {
    //      try {
    //         $this->validate($request,[
    //             'password' => 'required',
    //         ],[
    //             'password.required'=> 'Field is required.',
    //         ]);

    //         $id = $request->input('id');
    //             $user = Users::where('id',$id)->update(['password' => Hash::make($request->input('password'))]);
    //             if($user == NULL) {
    //                 return redirect('/admin/admin/update/password/'.$id)->with('error','Some Unexpected Error occurred.');
    //             } else {
    //                 return redirect('/admin/admin/update/password/'.$id)->with('success','Password Updated Successfully !'); 
    //             }    
            
    //     }  catch(\Illuminate\Database\QueryException $ex) {
    //         return redirect('/admin/admin/update/'.$id)->with('error','some error occurred'.$ex->getMessage());
    //     }
    // }

     public function admin_view_profile($id){
        $data = Users::where('id','=',$id)->get([
            DB::raw('concat(name," ",ifnull(middle_name," ")," ",ifnull(last_name," ")) as name'),
            'email',
            'phone',
            'user_type',
            'profile_photo',
            'created_at',
            'updated_at',

        ])->first();
    $data = array(
        'layout'=>'layouts.main',
        'data' => $data
    );
    return view('admin.admin.admin_view_profile', $data);   
      
    }

    public function user_profile_update()
    {
        return $this->admin_update(Auth::id(),"user");
    }
}

