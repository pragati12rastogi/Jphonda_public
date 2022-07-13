<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Auth;


use App\Model\Users;
use CustomHelpers;
use \App\Model\SectionRight;
use \App\Model\UserSectionRight;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/admin/dashboard';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    // public function showLoginForm(){
    //     return view('sections.login');
    // }
    public function logout(Request $request) {
        Auth::logout();
        return redirect('/admin/login');
    }
    // protected function authenticated(Request $request, $user)
    // {
    //     $section_data = SectionRight::
    //         leftJoin('user_section_rights', function($join) use ($user) {
    //                                     $join->on('section_rights.id', '=', 'user_section_rights.section_id');
    //                                 })
    //         ->where('link','LIKE','%dashboard%')->where('user_id',$user->id)->orderby('showorder','asc')->orderby('section_rights.id','asc')->first();


    //         if(!empty($section_data->link))
    //             return redirect()->intended($section_data->link);
    //         else
    //             return redirect()->intended();
    // }
    public function username()
    {
        return 'phone';
    }
    public function login(Request $request)
    {
        $this->validateLogin($request);

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
        
         
        if(isset($userdata->id) )
        {
            // $section_data = SectionRight::
            // leftJoin('user_section_rights', function($join) use ($userdata) {
            //                             $join->on('section_rights.id', '=', 'user_section_rights.section_id');
            //                             //$join->on('user_section_rights.user_id',"$userdata->id");
            //                         })
            // ->where('link','LIKE','%dashboard%')->where('user_id',$userdata->id)->first();

            if($this->attemptLogin($request))
            {
                return $this->sendLoginResponse($request);
            }else
            {
                $this->incrementLoginAttempts($request);
                return $this->sendFailedLoginResponse($request);
            }
        } 
        else {
            $this->incrementLoginAttempts($request);
            throw ValidationException::withMessages([
                $this->username() => ['Permission denied.']
            ]);
        }
    }
}

