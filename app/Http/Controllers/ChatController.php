<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\MessageModel;
use App\Users_Model;
use App\DealsModel;
//use App\ChatBlockListModel;


class ChatController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
	
	//APi Functions
	public function getProfileImage($user_id){
		$user_details = UserDetails_Model::where('user_id',$user_id)->has('category')->get();
		$image = '';
		if(count($user_details) > 0){
			foreach($user_details as $user_detail){
				if($user_detail->profile_image != ''){
					$image = url('/').'/'.$user_detail->profile_image;
					return $image;
				}
			}

		}else{
			$image = url('/').'/admin/images/users/default.jpg';
		}

		return $image;
	}
	public function checkBlockStatus($user_id,$other_user_id){

		$list = ChatBlockListModel::where('user_id',$user_id)->where('other_user_id',$other_user_id)->first();
		if($list != null){
			return '1';
		}else{
			$list2 = ChatBlockListModel::where('other_user_id',$user_id)->where('user_id',$other_user_id)->first();
			if($list2 != null){
				return '1';
			}else{
				return '0';
			}
			
		}
	}
	public function get_chat_lists(Request $request){
		$user_id = $request->input('user_id');
		if(!empty($user_id)){
			//$user = Users_Model::where('id',$user_id)->where('status','1')->first();
			$user = Users_Model::where('id',$user_id)->first();
			if(!empty($user)){
				
				$deals = DealsModel::where(function ($query) use ($user_id){
                    $query->where('request_to',$user_id)->orWhere('request_by',$user_id);
                })->where('request_status','1')
                ->whereHas('shipment', function($q){
                    $q->whereIn('status', ['2']);
                })->orderBy('id','desc')->get();
                $data = array();
                if(count($deals) > 0){
                    foreach($deals as $deal){
                        $image = '';
                        $title = '';
                        if($deal->request_by == $user_id){
                            $image = $deal->request_to_user->profile_image_url;
                            $title = $deal->request_to_user->name;
                            $other_user_id = $deal->request_to_user->id;
                            $user_type = $deal->request_to_type;
                        }else{
                            $image = $deal->request_by_user->profile_image_url;
                            $title = $deal->request_by_user->name;
                            $other_user_id = $deal->request_by_user->id;
                            $user_type = $deal->request_by_type;
                        }

                        $count = MessageModel::where('seen_status','0')->where('deal_id',$deal->id)->where('sender_id',$other_user_id)->where('receiver_id',$user_id)->count();
                        $last_msg = MessageModel::where('deal_id',$deal->id)->where(function ($query) use ($other_user_id){
                                $query->where('sender_id',$other_user_id)->orWhere('receiver_id',$other_user_id);
                            })
                            ->where(function ($query) use ($user_id){
                                $query->where('sender_id',$user_id)->orWhere('receiver_id',$user_id);
                            })->orderBy('id','desc')->first();
                        
                        $msg = 'No Messages';
                        if(!empty($last_msg)){
                            $msg = $last_msg->message; 
                        }
                        
                        
                        $data[] = array(
                            "deal_id"=>$deal->id,
                            "other_user_id"=>$other_user_id,
                            "image"=>$image,
                            "title"=>$title,
                            "message"=>$msg,
                            "date"=>date('d M Y',strtotime($deal->created_at)),
                            "time"=>date('H:i',strtotime($deal->created_at)),
                            "count"=>$count,
                            //"deal_status"=>$deal->request_status,
                            //"shipment_status"=>$deal->shipment->status,
                        );
                        
                    }
                    return response()->json(['status'=>true,'status_code'=>'200','message'=>'success','error'=>[],'data'=>$data], 200);
                }else{
                    return response()->json(['status'=>false,'status_code'=>'401','message'=>'No Chats Found','error'=>['No Chats Found'],'data'=>[]], 200);
                }
			
			}else{
			    return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid User Id','error'=>['Invalid User Id'],'data'=>[]], 200);
			}
		}else{
		    return response()->json(['status'=>false,'status_code'=>'401','message'=>'User Id is Required','error'=>['User Id is Required'],'data'=>[]], 200);
		}
	}
	public function marked_as_seen_msg(Request $request){
		$deal_id = $request->input('deal_id');
		$user_id = $request->input('user_id');
		$other_user_id = $request->input('other_user_id');
		if(!empty($user_id)){
			if(!empty($other_user_id)){
				$deal = DealsModel::where('id',$deal_id)->has('shipment')->has('trip')->first();
				if(!empty($deal)){
					//$user = Users_Model::where('id',$user_id)->where('status','1')->first();
					$user = Users_Model::where('id',$user_id)->first();
				
					if(!empty($user)){
						//$user = Users_Model::where('id',$sender_user)->where('status','1')->first();
						$user = Users_Model::where('id',$other_user_id)->first();
						if(!empty($user)){
							
							MessageModel::where('seen_status','0')->where('sender_id',$other_user_id)->where('receiver_id',$user_id)->where('deal_id',$deal_id)->update(['seen_status'=>1,'seen_time'=>date('Y-m-d H:i:s')]);
							return response()->json(['status'=>true,'status_code'=>'200','message'=>'success','error'=>[]], 200);
							
						}else{
							return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid Other User Id','error'=>['Invalid Other User Id']], 200);
						}
						
						
					}else{
						return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid User Id','error'=>['Invalid User Id']], 200);
					}
				}else{
					return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid Deal Id','error'=>['Invalid Deal Id']], 200);
				}
				
			}else{
				return response()->json(['status'=>false,'status_code'=>'401','message'=>'Other User Id is Required','error'=>['Other User Id is Required']], 200);
			}
			
		}else{
			return response()->json(['status'=>false,'status_code'=>'401','message'=>'User Id is Required','error'=>['User Id is Required']], 200);
		}
	}
	public function get_old_messages(Request $request){
		$deal_id = $request->input('deal_id');
		$user_id = $request->input('user_id');
		$other_user_id = $request->input('other_user_id');
		$skip = $request->input('skip');
		$take = $request->input('take');
		
		if($skip == ''){
			$skip = 0;
		}
		if($take == ''){
			$take = 50;
		}
		$deal = DealsModel::where('id',$deal_id)->has('shipment')->has('trip')->first();
		if(!empty($deal)){
			if(!empty($user_id)){
				if(!empty($other_user_id)){
					//$user = Users_Model::where('id',$user_id)->where('status','1')->first();
					$user = Users_Model::where('id',$user_id)->first();
					if(!empty($user)){
						//$other_user = Users_Model::where('id',$other_user_id)->where('status','1')->first();
						$other_user = Users_Model::where('id',$other_user_id)->first();
						if(!empty($other_user)){
							$messages = MessageModel::whereIn('sender_id',[$user_id,$other_user_id])->WhereIn('receiver_id',[$user_id,$other_user_id])->where('deal_id',$deal_id)->with('sender','receiver')->has('receiver')->has('sender')->skip($skip)->take($take)->orderBy('id','asc')->get();
							
							if($messages->count() > 0){
								$data = array();
								
								foreach($messages as $message){
									
									if($message->sender_id == $user_id){
										$data[]  = array(
													'id'=>$message->id,
													"msg_type"=>"send",
													"message"=>$message->message,
													"receiver_image"=>$message->receiver->profile_image_url,
													"sender_image"=>$message->sender->profile_image_url,
													"user_name"=>$message->sender->name,
													"msg_date"=>date('d M Y',strtotime($message->created_at)),
													"msg_time"=>date('H:i',strtotime($message->created_at))
												);
									}else{
										$data[]  = array(
													'id'=>$message->id,
													"msg_type"=>"receive",
													"message"=>$message->message,
													"receiver_image"=>$message->receiver->profile_image_url,
													"sender_image"=>$message->sender->profile_image_url,
													"user_name"=>$message->sender->name,
													"msg_date"=>date('d M Y',strtotime($message->created_at)),
													"msg_time"=>date('H:i',strtotime($message->created_at))
												);
									}
									
								}
								
								//$blockStatus = $this->checkBlockStatus($user_id,$other_user_id);
								
								return response()->json(['status'=>true,'status_code'=>'200','message'=>'success','error'=>[],'data'=>$data], 200);
							}else{
								return response()->json(['status'=>false,'status_code'=>'401','message'=>'No Messages Found','error'=>['No Messages Found'],'data'=>[]], 200);
							}
							
							
						}else{
							return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid Other User Id','error'=>['Invalid Other User Id'],'data'=>[]], 200);
						}
						
					}else{
						return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid User Id','error'=>['Invalid User Id'],'data'=>[]], 200);
					}
					
				}else{
					return response()->json(['status'=>false,'status_code'=>'401','message'=>'Other User Id is Required','error'=>['Other User Id is Required'],'data'=>[]], 200);
				}
				
			}else{
				return response()->json(['status'=>false,'status_code'=>'401','message'=>'User Id is Required','error'=>['User Id is Required'],'data'=>[]], 200);
			}
		}else{
			return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid Deal Id','error'=>['Invalid Deal Id'],'data'=>[]], 200);
		}
	}
	public function get_unread_messages_count(Request $request){
	    $user_id = $request->input('user_id');
	    $user = Users_Model::where('id',$user_id)->first();

		if(!empty($user)){
	        //$count = MessageModel::has('sender')->has('receiver')->has('deal')->where('seen_status','0')->where('receiver_id',$user_id)->count(); //OLD
	        //New
	        $deals = DealsModel::where(function ($query) use ($user_id){
                    $query->where('request_to',$user_id)->orWhere('request_by',$user_id);
                })->where('request_status','1')
                ->whereHas('shipment', function($q){
                    $q->whereIn('status', ['2']);
                })->orderBy('id','desc')->get();
                $data = array();
                $msgs_count = 0;
                if(count($deals) > 0){
                    foreach($deals as $deal){
                        if($deal->request_by == $user_id){
                            $other_user_id = $deal->request_to_user->id;
                        }else{
                            $other_user_id = $deal->request_by_user->id;
                        }

                        $count = MessageModel::where('seen_status','0')->where('deal_id',$deal->id)->where('sender_id',$other_user_id)->where('receiver_id',$user_id)->count();
                        $msgs_count += $count;
                    }
                }
	        
	        return response()->json(['status'=>true,'status_code'=>'200','message'=>'success','error'=>[],'count'=>$msgs_count], 200);
		}else{
			return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid User id','error'=>['Invalid User id']], 200);
		}
	}
	public function block_unblock_chat(Request $request){
		$user_id = $request->input('user_id');
		$other_user_id = $request->input('other_user_id');
		$type = $request->input('type');

		$user = Users_Model::where('id',$user_id)->first();
			
		if(!empty($user)){
			$other_user = Users_Model::where('id',$user_id)->first();
			
			if(!empty($other_user)){

				if($type == 'block'){
					$list = ChatBlockListModel::where('user_id',$user_id)->where('other_user_id',$other_user_id)->first();
					if($list != null){
						return response()->json(['status'=>true,'status_code'=>'200','message'=>'User Block Successfully','error'=>[]], 200);
					}else{
						ChatBlockListModel::where('user_id',$user_id)->where('other_user_id',$other_user_id)->create(['user_id'=>$user_id,'other_user_id'=>$other_user_id]);
						return response()->json(['status'=>true,'status_code'=>'200','message'=>'User Block Successfully','error'=>[]], 200);
					}

				}else{

					ChatBlockListModel::where('user_id',$user_id)->where('other_user_id',$other_user_id)->delete();
					return response()->json(['status'=>true,'status_code'=>'200','message'=>'User Unblock Successfully','error'=>[]], 200);
				}

			}else{
				return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid Other User id','error'=>['Invalid Other User id']], 200);
			}
		}else{
			return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid User id','error'=>['Invalid User id']], 200);
		}

	}
	public function send_chat_notification(Request $request){
	    $sender_id = $request->sender_id;
	    $receiver_id = $request->receiver_id;
	    $deal_id = $request->deal_id;
	    $message = $request->message;
	    
	    $user = Users_Model::where('id',$receiver_id)->first();
	    if(!empty($user)){
	        $msg = MessageModel::where('deal_id',$deal_id)->where('receiver_id',$receiver_id)->where('sender_id',$sender_id)->orderBy('id','desc')->first();
	        if(!empty($msg)){
	            $notification = new NotificationController();
                $notification->missedChatNotification($user,$msg);
                return 'Notification Sent';
	        }
	    }
	    
	}
}
