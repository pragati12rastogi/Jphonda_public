<?php

namespace App\Http\Controllers\admin;
// namespace App\Http\Controllers;

use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use DB;
use Illuminate\Http\Request;
use App\Model\Users;
use App\Model\Userlog;
use \App\Model\Store;
use \App\Custom\CustomHelpers;
use Auth;


class UserController extends Controller
{
    function user_create()
    {
        $depart = array();//Department::all();
        $data = array(
            'dept'=>$depart,
            'layout'=>'layouts.main'
        );
        return view('sections.user.user_create',$data);
    }    
    function user_create_db(Request $request)
    {
        $this->validate($request,[
            'username'=>'required',
            'email'=>'required|email|unique:users,email',
            'pass'=>'required|min:6',
            're_pass'=>'required_with:pass|same:pass',
            'user_type'=>'required',
            'phone'=>'required|digits:10|unique:users,phone',
            'landline'=>'nullable|numeric',
            'profile_pic' => 'image|max:2048000'
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
        if($request->hasFile('profile_pic'))
        {
            $file = $request->file('profile_pic');
            $destinationPath = public_path().'/userimages';
            $ts=date('Y-m-d G:i:s');
            $filename=$file->getClientOriginalName();
            $file->move($destinationPath,$filename);
        }
        else
            $filename='avatar.png';
            $id = Users::insertGetId(
                [
                    'id'=>NULL,
                    'active'=>'1',
                    'created_at'=>date('Y-m-d G:i:s'),
                    'user_type'=>$request->input('user_type'),
                    'password'=>Hash::make($request->input('pass')),
                    'name'=>$request->input('username'),
                    //'department_id'=>2,
                    'profile_photo'=>$filename,
                    'email'=>$request->input('email'),
                    'phone'=>$request->input('phone'),
                    //'home_landline'=>$request->input('landline'),
                ]
            );  
        /* Add action Log */
        CustomHelpers::userActionLog($action='Add User',$id,0);  
        return redirect('/admin/permission/'.$id)->with("success","User registered successfully.Pls set user acesss permission.");
    } 

    public function userlog()
    {
        $users = Users::get();
        $data=array('layout'=>'layouts.main','users'=>$users);
        return view('admin.admin.userlog_list', $data);      
    }
    public function logdata(Request $request)
    {   
        $user_name=$request->input('user_name');
        $date=$request->input('date');
        $search = $request->input('search');
        $serach_value = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $offset = empty($start) ? 0 : $start ;
        $limit =  empty($limit) ? 10 : $limit ;

        $userlog=Userlog::leftJoin('userlog_details','userlog.id','=','userlog_details.userlog_id')
         ->leftjoin('users','users.id','=','userlog.userid')
        ->select('userlog.id', DB::raw('concat(users.name," ",ifnull( users.middle_name," ")," ",ifnull( users.last_name," ")) as name'),'action','data_id','content_changes','createdon');
         
        if(!empty($serach_value))
        {
            $userlog = $userlog->where('userlog.action','LIKE',"%".$serach_value."%")
                        ->orwhere('users.name','LIKE',"%".$serach_value."%")
                        ->orwhere('userlog_details.content','LIKE',"%".$serach_value."%");
        }

        if(isset($user_name) )
            {
               $userlog->where(function($query) use ($user_name){
                        $query->where('userlog.userid',$user_name);
                    });               
            }
        if(isset($date) )
            {
               $userlog->where(function($query) use ($date){
                        $query->whereDate('createdon', '=', date($date));
                    });
            }

      if(isset($request->input('order')[0]['column']))
            {
                $data = [
                    'id',
                    'name',
                    'action',
                    'data_id',
                    'content_changes',
                    'createdon'
              
                ];
                $by = ($request->input('order')[0]['dir'] == 'desc')? 'desc': 'asc';
                $userlog->orderBy($data[$request->input('order')[0]['column']], $by);
            }
            else
                $userlog->orderBy('userlog.id','asc');      
        
       
        $count = count( $userlog->get()->toArray());
        $userlog = $userlog->offset($offset)->limit($limit)->get()->toArray();

        $array['recordsTotal'] = $count;
        $array['recordsFiltered'] = $count;
        $array['data'] = $userlog; 
        return json_encode($array);

    }   


}
