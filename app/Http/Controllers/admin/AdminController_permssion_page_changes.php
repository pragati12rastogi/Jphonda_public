<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \App\Model\Users;
use \App\Model\UserLayoutRight;
use \App\Model\SectionRight;
use \App\Model\UserSectionRight;
use \App\Custom\CustomHelpers;
use DB;
use Auth;
class AdminController extends Controller
{

    public function admin_update()
    {
         
    }
    public function permission_denied()
    {
         $data=array('layout'=>'layouts.main');
        return view('sections.user.admin_permssion_denied', $data);  
    }
    public function permission($id){
       
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
                return view('sections.user.admin_permission',$data);   
        }
        else
            return abort('403',"You can not change your own Access Rigths.");
    }
    public function getadminpermission($id){
        DB::enableQueryLog();
        //die();
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
        // echo '<pre>';
        // print_r($menudata);
        // die("asd");
    
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
    
    function generate_table($menudata,$indent,$class,$master_class)
    {
     //   echo '<>';
        $resp='';
        $pid=0;
        $id=0;
        $cond=0;
        $prid=0;
        //$menudata = array();
        //$master_class='';
        $j=0;
        foreach ($menudata as $menu)
        {

            echo $menu['name'].' = '.$master_class.' == '.$class.'<br>';
            if($menu['linkfor']>0)
            {
                // for($i=0;$i<9;$i++)
                // {
                //     if($i==$menu['linkfor'])
                //     {
                //          $resp = $resp.'<td>';
                        
                //         $val = $menu['id'];
                //         if($menu['map_id']!=0 )
                //             $val = $val.",".$menu['map_id'];
                //         $checked='';
                //         if($menu['user_id'] != NULL)
                //             $checked='checked';
                //         $resp = $resp.'<input type="checkbox" class="'.str_replace(['.', ' ', '/'],'_',$menu['name']).'" name="menu[]" value="'.$val.'" '.$checked.' title="'.$menu['name'].'">';
                //         $resp = $resp.'</td>';
                //     }
                //     else
                //     {
                //         $resp = $resp.'<td></td>';
                //     }
                // }
                // $resp = $resp.'</tr>';
            }
            else
            {
                 $val1 = $menu['id'];
                        $checked1='';
                        if($menu['map_id']!=0 )
                            $val1 = $val1.",".$menu['map_id'];
                        if($menu['user_id'] != NULL)
                            $checked1='checked';
                        $inp='<input type="checkbox"  class="dd '.$class.' '.$master_class.'" id="'.str_replace(['.', ' ', '/'],'_',$menu['name']).'" name="menu[]" value="'.$val1.'" '.$checked1.'>';
                    if($pid!=$menu['pid'])
                    {
                       // $indent='';
                        $resp = $resp.'<tr class=" new '.$pid.'='.$menu['pid'].'">';
                        
                    }
                    else
                    {$master_class = str_replace(['.', ' ', '/'],'_',$menu['name']);
                       $resp = $resp.'</tr>';
                    }
                    if(($prid!=$menu['pid'] || $indent=="") && ($menu['pid']!=0))
                    {
                            if($menu['name']!="Dashboard")
                            {
                               // echo $menu['name'].' = '.$prid."==".$menu['pid'].'<br>'; 
                                //echo $menu['name']; die('@123');
                            }
                        $indent=$indent.'&nbsp&nbsp&nbsp&nbsp&nbsp';
                        
                    }
                    
                    $resp = $resp.'<td class=" '.$pid.'='.$menu['pid'].'" style="display:inline">'.$indent.$inp.'<label style="display:inline"> '.$menu['name'].$master_class.'</label></td>';
                    $pid= $menu['pid'];
                    $id = $menu['id'];
                    //echo $menu['name'].' = '.$pid.'==<br>'; 
                    if(($prid!=$menu['pid']))
                    {
                        $master_class = str_replace(['.', ' ', '/'],'_',$menu['name']);
                        
                        //$master_class = str_replace(['.', ' ', '/'],'_',$menu['name']);
                    }
                if(isset($menu['children']))
                {
                    if(($prid!=$menu['pid']))
                    {
                        //$master_class = str_replace(['.', ' ', '/'],'_',$menu['name']);
                        
                        //$master_class = str_replace(['.', ' ', '/'],'_',$menu['name']);
                    }
                    if($menu['name']!="Dashboard")
                    {
                        //echo $menu['name'].' = '.$master_class.' == '.$class.'<br>';
                     //echo $master_class."---"; //die("@123");
                    }
                    // if(($pid1!=$menu['pid'] || $indent=="") && ($menu['pid']!=0))
                    // {
                    //     if($menu['name']!="Dashboard")
                    //     {
                    //         echo $menu['name'].' = '.$pid1."==".$menu['pid'].'<br>'; 
                    //         //echo $menu['name']; die('@123');
                    //     }
                    //     $indent=$indent.'&nbsp&nbsp&nbsp&nbsp&nbsp';
                    // }
                    
                    //$class=$class.' '.str_replace(['.', ' ', '/'],'_',$menu['name']);
                    //$menuitems=array_keys($menu['children']);
                    $firstitem = current($menu['children']);
                    if(($prid!=$menu['pid']) || $pid!=$menu['id'])
                    {
                        //echo $menu['name'].' = '.$prid."==".$menu['pid'].'<br>'; 
                       // $master_class = str_replace(['.', ' ', '/'],'_',$menu['name']);
                    }
                    $prid= $menu['pid'];
                    if($firstitem['linkfor']>0)
                    {
                        $var=0;
                        // $prid = $firstitem['pid'];
                        // //if($prid>1)
                        // //echo $menu['name'].' = '.$pid."==".$prid.'<br>'; 
                        // //continue;
                        // if($pid==$prid)
                        // {
                        //     echo $menu['name']; die;
                        //     $indent= '&nbsp&nbsp&nbsp&nbsp&nbsp';
                        // }
                        
                        $menukey = array_keys($menu['children']);
                        for($i=0;$i<count($menu['children']);)
                        {
                            if(isset($menu['children'][$menukey[$i]]['children']))
                            {
                                // $indent=$indent.'&nbsp&nbsp&nbsp&nbsp&nbsp';
                                // $class=$class.' '.str_replace(['.', ' ', '/'],'_',$menu['name']);
                                // $menuitems=array_keys($menu['children']);
                                // $master_class = str_replace(['.', ' ', '/'],'_',$menu['name']);
                                // echo $master_class; die("@123");
                                // for($i=0;$i<count($menuitems);$i++)
                                // {
                                //     $resp =$resp .$this->generate_table(array($menu['children'][$menuitems[$i]]),$indent,$class,$master_class);
                                // }  
                                // return $resp;
                            }
                            else
                            {
                                
                                if( $var==9)
                                {
                                    $var=0;
                                    //$resp = $resp."</tr> <tr class=' nw ".$pid."=".$menu['pid']."'> <td >".$menu['name'];
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

                    $master_class = str_replace(['.', ' ', '/'],'_',$menu['name']);
                    // if($menu['name']!="Dashboard")
                    // {
                    //     echo $menu['name'].' = '.$master_class.' == '.$class.'<br>';
                    //  //echo $master_class."---"; //die("@123");
                    // }
                    $resp =$resp .$this->generate_table($menu['children'],$indent,$class,$master_class);
                    //return $resp;
                }
                else
                {
                    //$menu = $menu['children'];
                    for($i=0;$i<9;$i++)
                    {
                        if($i==$menu['linkfor'])
                        {
                             $resp = $resp.'<td>';
                            
                            $val = $menu['id'];
                            if($menu['map_id']!=0 )
                                $val = $val.",".$menu['map_id'];
                            $checked='';
                            if($menu['user_id'] != NULL)
                                $checked='checked';
                            $resp = $resp.'<input type="checkbox" class="'.str_replace(['.', ' ', '/'],'_',$menu['name']).'" name="menu[]" value="'.$val.'" '.$checked.' title="'.$menu['name'].'">';
                            $resp = $resp.'</td>';
                        }
                        else
                        {
                            $resp = $resp.'<td></td>';
                        }
                    }
                    //$resp = $resp.'</tr>';
                }
            }
            //return $resp;
        }
        return $resp;
        exit;
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
        return view('sections.user.admin', $data);   
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
            'home_landline',
            'user_type',
            'login',
            'profile_photo',
            'created_at',
            'updated_at',

        ])->first();
    $data=array(
        'layout'=>'layouts.main',
        'data' => $data
    );
    return view('sections.admin_view', $data);   
      
    }

}


