<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Validator;
use \App\Custom\FirebaseNotification;
use \App\Custom\CustomHelpers;

use App\Model\Users;
use App\Model\API\UsersAPI;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use AuthenticatesUsers;
    public $successStatus = 200;
    public $errorStatus = 401;

    public function username()
    {
        return 'phone';
    }
    public function validateLogin(Request $request){
        $request->validate([
            $this->username() => 'required|string',
            'password' => 'required|string',
            '_token' => 'required|string'
        ]);
    }
    public function login(Request $request)
    {
        $this->validateLogin($request);

        CustomHelpers::apiActionLog('Api Admin Login',1,0);

        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        $credentials = $this->credentials($request);
        $userdata = Users::where('phone',$credentials['phone'])
            ->where(function($query){
                $query->where('user_type','admin')
                ->orWhere('user_type','superadmin');
            })
            ->where('status',1)
            ->first();
        $send_data = [
            'status'    =>  '',
            'message'   =>  ''
        ];
        if(isset($userdata->id) )
        {
            $old_token = $userdata->firebase_token;
            $new_token = $request->input('_token');
            if(!empty($old_token)){
                if($old_token != $new_token){
                    // send notification
                    $notification = array(
                        'title'     => 'LogIn',
                        'body'      => 'New Device Login Found.',
                        'click_action' => ".MainActivity"
                    );
            
                    $data = array(
                        "title" => "Logout Data",
                        "notificationType"	=>	"logout",
                        "body" => [
                            'name'	=>	$userdata->name,
                            'phone'	=>	$userdata->phone
                        ],
                        "content_available" => true,
                        "priority" => "high"
                    );
                    $res = FirebaseNotification::sendNotfication($notification,$data,$old_token);
                    if($res['success'] <= 0){
                        $send_data['status'] = 'error';
                        $send_data['message'] = 'Invalid Old Token';
                        //return response()->json($send_data,$this->errorStatus);
                    }
                    // update in users_api
                    $update_token = Users::where('id',$userdata->id)->update([
                        'firebase_token' =>  $new_token,
                        'login_time'=> date('Y-m-d H:i:s')
                    ]);
                    if(!$update_token){
                        $send_data['status'] = 'error';
                        $send_data['message'] = 'Internal Error';
                        return response()->json($send_data,$this->errorStatus);
                    }
                }else{
                    $update_token = Users::where('id',$userdata->id)->update([
                        'firebase_token' =>  $new_token,
                        'login_time'=> date('Y-m-d H:i:s')
                    ]);
                    if(!$update_token){
                        $send_data['status'] = 'error';
                        $send_data['message'] = 'Internal Error';
                        return response()->json($send_data,$this->errorStatus);
                    }
                }
            }else{
                // insert it token
                $insert_token = Users::where('id',$userdata->id)->update([
                    'firebase_token' =>  $new_token,
                    'login_time'=> date('Y-m-d H:i:s')
                ]);
                if(!$insert_token){
                    $send_data['status'] = 'error';
                    $send_data['message'] = 'Internal Error';
                    return response()->json($send_data,$this->errorStatus);
                }
            }
            if($this->attemptLogin($request))
            {
                $send_data['status'] = 'success';
                $send_data['data'] = [
                    'id'    =>  $userdata->id,
                    '_token'    =>  $new_token,
                    'name'    =>  $userdata->name,
                    'emp_id'    =>  $userdata->emp_id,
                    'email' =>  $userdata->email,
                    'phone' =>  $userdata->phone,
                    'store_id'  =>  $userdata->store_id
                ];
                return response()->json($send_data,$this->successStatus);
            }else
            {
                $send_data['status'] = 'error';
                $send_data['message'] = 'Login Failed';
                // $send_data['message'] = 'The given data was invalid, These credentials do not match our records';
             
                $this->incrementLoginAttempts($request);
                return response()->json($send_data,$this->errorStatus);
            }
        } 
        else {
            $this->incrementLoginAttempts($request);
            $send_data['status'] = 'error';
            $send_data['message'] = 'Login Failed';
            
            return response()->json($send_data,$this->errorStatus);
        }
    }
    
}
