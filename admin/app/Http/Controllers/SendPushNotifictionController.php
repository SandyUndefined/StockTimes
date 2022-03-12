<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use App\Models\User;
class SendPushNotifictionController extends Controller
{
   

    function __construct(){
         $this->middleware('permission:show-notification-form|send-notification', ['only' => ['index','sendNotification']]);
         $this->middleware('permission:show-notification-form', ['only' => ['index']]);
         $this->middleware('permission:send-notification', ['only' => ['sendNotification']]);
    }



    /**
     * Show send push notification form.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

   	public function index($layout = 'side-menu', $theme = 'light'){

   		return view('super-admin/push-notification.index', [
            'theme' => $theme,
            'page_name' => 'index',
            'side_menu' => array(),
            'layout' => $layout,
            'breadcrumb'=>'<a href="'.url('/').'" class="breadcrumb">'.trans('admin.dashboard').'</a><i data-feather="chevron-right" class="breadcrumb__icon"></i><a href="'.url('/rss-feed-src/side-menu/light').'" class="breadcrumb--active">'.trans('admin.rss_feed').'</a>'

        ]);
   	}


   	   /**
     *  send push notification.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

   	public function sendNotification(Request $request){

   		try {
            $post = $request->all();
            $validate = [
                'send_to' => 'required',
                'title' => 'required',
            ];
            $validator = Validator::make($post, $validate);
            if ($validator->fails()) {
                 $data['error'] = $validator->errors();
                return redirect()->back()->with('failure',__('message_alerts.required_field_missing'));
            }else{

                if (setting('enable_notifications')) {
                    if(file_exists(public_path()."/upload/logo/".setting('site_logo'))) { 
                        $image = url('upload/logo').'/'.setting('site_logo');
                    }else{
                        $image = url('upload/no-image.png');
                    }
                	if ($post['send_to'] == 'all') {
                		$user = User::where('active',1)->get();
    	                foreach($user as $detail){
    	                    if($detail->device_token!=null){
    	                        \Helpers::sendNotification($detail->device_token,$post['description'],$post['title'],setting('firebase_msg_key'),$image);
    	                    }
    	                }
                	}else{
                		$post['email'] = explode(',', $post['email']);
                		for($c=0; $c<count($post['email']);$c++){
                			$user = User::where('email',$post['email'])->first();
                			if($user && $user->device_token!=null){
    	                        \Helpers::sendNotification($user->device_token,$post['description'],$post['title'],setting('firebase_msg_key'),$image);
    	                    }
                		}

                	}
                }
                return redirect()->back()->with('success',__('message_alerts.notification_sent'));
            }
           
        } catch (\Exception $ex) {
            return redirect()->back()->with('failure',$ex->getMessage());
        }
   	}
}
