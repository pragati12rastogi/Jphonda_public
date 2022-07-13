<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Auth;

use Carbon\Carbon;
use Validator;
use Illuminate\Validation\ValidationException;

use App\Model\Users;
use App\Model\Settings;
use \App\Model\CallData;
use \App\Model\CallDataDetails;
use \App\Model\CallDataRecord;

class AutoAssignCallController extends Controller
{
	public function assignAferFifty(){
		try {
			
			$setting = Settings::where('name','callAssignToOther')->first();
			// users that have made call
			$userslist = CallDataDetails::leftjoin('users','call_data_details.assigned_to','users.id')
				->leftjoin('user_details','user_details.user_id','users.id')
				->leftjoin('shift_timing','shift_timing.id','user_details.shift_timing_id')
				->leftjoin('call_data_record',function($join){
					$join->on('call_data_details.id','call_data_record.call_data_details_id')
					->whereRaw('call_data_record.id = (select max(cdr.id) from call_data_record cdr where cdr.updated_by=users.id and date_format(cdr.created_at,"%Y-%m-%d") = curdate() and cdr.call_data_details_id = call_data_details.id group by cdr.call_data_details_id)');
				})
				->whereRaw('Timestampdiff(minute,Concat(CURDATE()," ",shift_timing.shift_from), CURRENT_TIMESTAMP())>='.$setting['value'])
				->whereRaw('CURRENT_TIMESTAMP()<= CONCAT(CURDATE(), " ", shift_timing.shift_to)')
				->whereNotNull('call_data_record.id')
				->select('users.*',DB::raw('Timestampdiff(minute,Concat(CURDATE()," ",shift_timing.shift_from), CURRENT_TIMESTAMP()) as diff_time'),DB::raw('Concat(CURDATE()," ",shift_timing.shift_from) as shift'),DB::raw('CURRENT_TIMESTAMP() as cur_time'),'call_data_details.id as cdd_id',
                'call_data_record.id as cdr_id')->groupby('users.id')->get()->toArray();

				$users_not_call = array_column($userslist, 'id');
			
				$getCallData = CallDataDetails::wherenotIn('assigned_to',$users_not_call)->where('query_status','Open')->get()->toArray();
				$type = [1=>'Enquiry',2=>'Insurance',3=>'Service'];
				// distinguishing array as call_type
				$call_type_array = array();
				foreach($getCallData as $callData){
					$call_type_array[$type[$callData['type']]][] = $callData;
				}
				// echo "call_type_array " ;print_r($call_type_array);
				$call_type = ['Enquiry','Insurance','Service'];
				$get_user = Users::where('status',1)
	                                ->leftjoin('call_assign_setting','call_assign_setting.user_id','users.id')
	                                ->leftJoin('hr__leave',function($join){
	                                    $join->on('hr__leave.user_id','=','users.id')
	                                        ->whereBetween(DB::raw('CURRENT_DATE'),['hr__leave.start_date','end_date']);
	                                })
	                                ->whereNotIn('users.id',$users_not_call)
	                                ->whereIn('call_assign_setting.type',$call_type)
	                                ->whereNull('hr__leave.id')
	                                ->whereNotNull('call_assign_setting.id')
	                                ->select(
	                                    'users.id as user_id',
	                                    'call_assign_setting.type',
	                                    'call_assign_setting.noc'
	                                )->groupBy('call_assign_setting.type')
	                                ->groupBy('call_assign_setting.user_id')->get()->toArray();
	            
	            $user_data = [];
	            foreach($get_user as $k => $v){
	                if(isset($user_data[$v['type']])){
	                    $user_data[$v['type']][$v['user_id']] = ['id' => $v['user_id']];
	                }else{
	                    $user_data[$v['type']] = [];
	                    $user_data[$v['type']][$v['user_id']] = ['id' => $v['user_id']];
	                }
	            }
				// echo "user_data ";print_r($user_data);
				// loop acc. call_type
				foreach ($user_data as $calltype => $user_Ids) {
					$call_count = isset($call_type_array[$calltype])?count($call_type_array[$calltype]):0;
					$user_count = count($user_Ids);
					
					if($call_count>0){
						// call are more or equal to user
						if($call_count >= $user_count){

							$div_user = intval($call_count/$user_count);
							$pending_call = $call_count-$div_user*$user_count;

							// echo"pending_call";print_r($pending_call);
							// $count =0;
							foreach($user_Ids as $u_id => $detail){
								if(count($call_type_array[$calltype]) > 0){
                                	$old_assign = 0;
	                                // print_r($user_data[$calltype][$u_id]);die();
	                                if(!isset($user_data[$calltype][$u_id]['call_id'])){
	                                    $user_data[$calltype][$u_id]['call_id'] = [];
	                                }
	                                $range = $div_user;
                                    // print_r($range);
	                                $new_arr = array_splice($call_type_array[$calltype],0,$range);
	                                // $count++;
	                                $user_data[$calltype][$u_id]['call_id'] = array_merge($user_data[$calltype][$u_id]['call_id'],$new_arr);
                            	}
                            	
							}

							if($pending_call > 0){
                                $this_arr = $this->allot_pending_call($call_type_array[$calltype],$user_data,$pending_call,$calltype);
                                $user_data[$calltype] = $this_arr;
                                
                            }
						}else{
							
							$this_arr = $this->allot_pending_call($call_type_array[$calltype],$user_data,$call_count,$calltype);
                        	$user_data[$calltype] = $this_arr;
						}
					}
				}
                // print_r($user_data);
                // print_r($calltype);die();

				foreach($user_data as $calltype => $data){
                    // print_r($calltype);
                    foreach($data as $user_id => $call_data) {
                    	// print_r($call_data);
                    	if(isset($call_data['call_id'])){
                    		$call_ids = array_column($call_data['call_id'], 'id');
                    		
                    		$assign_call = CallDataDetails::whereIn('id',$call_ids)
                                ->update([
                                    'assigned_to'   =>  $user_id
                                ]);
                    	}
                    }
                }
			
			
		} catch (\Illuminate\Database\QueryException $ex) {
			return response()->json('some error occurred'.$ex->getMessage(),401);
		}
	}
	public function allot_pending_call($call_record,$user_data,$pending_call,$call_type){
		$sub_data = $user_data[$call_type];
		// echo " sub_data ";print_r($sub_data);
		$count = -1;
		while($pending_call > 0){
            foreach($sub_data as $user_id => $other_data){
            
            	if($pending_call > 0){
            		if(!isset($sub_data[$user_id])){
                        $sub_data[$user_id] = $other_data;
                    }
                    $old_assign = 0;
                    if(isset($other_data['call_id'])){
                        $old_assign = count($sub_data[$user_id]['call_id']); 
                    }else{
                        $sub_data[$user_id]['call_id'] = [];
                    }

                    $new_arr = array_slice($call_record,$count,1);
                    array_push($sub_data[$user_id]['call_id'],$new_arr[0]);
                    $count--;
                    array_splice($call_record,0,1);
                    $pending_call--;
                    
            	}
            }
        }
        return $sub_data;

	}
}