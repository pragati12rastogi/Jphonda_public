<?php

namespace App\Http\Middleware;
use Closure;
use Auth;
use Route;
use DB;
use Response;
use App\Custom\CustomHelpers;
use App\Model\SectionRight;
class UserRights
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next){

        $user_id = Auth::id();
        $userAlloweds = [];
        if(!CustomHelpers::checkUserIp())
            abort('403',"Ip Address Not Allowed");

        $user = Auth::user();
        $user_type = $user['user_type'];
        $link_active=0;

        $current_url = '/'.preg_replace('/{[a-zA-Z0-9_]*}/','*',Route::getFacadeRoot()->current()->uri());
        
        $userSections = \App\Model\SectionRight::leftjoin('user_section_rights',function($join){ 
                $join->on('section_rights.id','=','user_section_rights.section_id');
                    })
        ->where('user_section_rights.user_id', $user_id)
        ->where(function ($query) use ($current_url) {
            $query->where('show_menu','=',1)
            ->orwhere('section_rights.link','=',$current_url);
            })
        ->where('user_section_rights.user_id', $user_id);

        $userSections = $userSections->select('section_rights.*','user_section_rights.section_id','user_section_rights.allowed')
        ->orderBy('section_rights.showorder')
        ->orderBy('section_rights.id')
        ->get()->toarray();
        foreach($userSections as $userSection){
            // if($request->is(ltrim($userSection['link'], '/'))){    
            //     $userAlloweds['section'] =  explode(',',$userSection['allowed']);
            // }

            if($userSection['link']==$current_url){    
                $userAlloweds['section'] =  explode(',',$userSection['allowed']);
                $link_active = $userSection['status']==1?1:0;
            }        
            $key = $userSection['name'].$userSection['pid'];
            if($userSection['pid']==0)
                $layout[$userSection['id']] = $userSection;
            else
                $layout[$userSection['pid']]['child'][] = $userSection;
        }

        if($user_type=="superadmin")
        {
            $userAlloweds['section']=[1,3];
            if($link_active!=1)
            {
                $current_url_data = SectionRight::where('link', 'like', '%' . $current_url . '%')->get('status')->first();
                $link_active = isset($current_url_data->status) && $current_url_data->status==1?1:0;

            }
        }
            
        if(empty($userAlloweds['section']) && $user_type!="superadmin")
        {   
            return abort(403,'You are not authorised to access this page.');
        }
        if($link_active!=1)
        {
            return abort(302,'Page is Under Maintainance.');
        }

        $layout = $this->buildTree($userSections);
        $userAlloweds['layout'] = $layout;
        $request->merge(compact('userAlloweds'));
        return $next($request);
    }
    

    
    public function buildTree(array &$elements, $parentId = 0) {

        $branch = array();    
        foreach ($elements as $element) {
            if($element['show_menu']==1)
            {
                if ($element['pid'] == $parentId) {
                    $children = $this->buildTree($elements, $element['id']);
                    if ($children) {
                        $element['child'] = $children;
                    }
                    $branch[$element['id']] = $element;
                }
            }
        }
        return $branch;
    }
}
