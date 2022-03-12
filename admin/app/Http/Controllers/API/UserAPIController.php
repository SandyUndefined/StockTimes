<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Auth;
use UploadImage;

class UserAPIController extends Controller
{
     /**
     * Registration Req
     */
    public function register(Request $request)
    {
        try {
            $post = $request->all();
            $validate = [
                'name' => 'required',
                'email' => 'required',
                'password' => 'required',
            ];
            $validator = Validator::make($post, $validate);
            if ($validator->fails()) {
                $data['error'] = $validator->errors();

                return response(\Helpers::sendFailureAjaxResponse($data['error']));
            }else{
                $emailExist = User::where('email',$request->email)->get();
                if(count($emailExist)){
                    return response(\Helpers::sendFailureAjaxResponse('Email already used'));
                }else if(strlen($post['phone'])<10){
                    return response(\Helpers::sendFailureAjaxResponse('Please enter at least 10 digit for phone.'));
                }else if(strlen($post['phone'])>10){
                    return response(\Helpers::sendFailureAjaxResponse('Please enter 10 digit for phone.'));
                }else if(strlen($post['name'])<4){
                    return response(\Helpers::sendFailureAjaxResponse('Please enter at least 4 charater for username.'));
                }else if(strlen($post['password'])<=7){
                    return response(\Helpers::sendFailureAjaxResponse('Password must be 8 digit.'));
                }
                if(isset($post['photo']) && $post['photo']!=''){
                    $images = $post['photo'];
                    $post['photo'] = time() . rand() .'.'.$images->getClientOriginalExtension();
                    $img = \UploadImage::make($images->getRealPath());
                    $destinationPath = public_path('/user');
                    $images->move($destinationPath, $post['photo']);
                }
                $img = '';
                if(isset($post['photo']) && $post['photo'] !=''){
                    $img = $post['photo'];
                }
                if(isset($request->phone) && $request->phone !=''){
                    $phone = $request->phone;
                }else{
                    $phone = "";
                }
                $user = User::create([
                    'type'=>'user',
                    'name' => $request->name,
                    'email' => $request->email,
                    'gender'=>$request->gender,
                    'photo'=>$img,
                    'phone'=>$phone,
                    'active' => 1,
                    'lang_code' => Setting('preferred_site_language'),
                    'password' => bcrypt($request->password),
                ]);
                $data = $user;
                $token = $user->createToken('LaravelAuthApp')->accessToken;
                $data->api_token = $token;
                $data->isNewUser = true;
                $data->id = (string)$data->id;
                $data->otp = (string)$data->otp;
                return response(\Helpers::sendSuccessAjaxResponse(__('message_alerts.registerd_successfully'),$data));
            }
        } catch (\Exception $ex) {
            return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.there_is_an_error')));
        }
    }

    /**
     * Login Req
     */
    public function login(Request $request)
    {
        try{
            $user = User::where('email',$request->email)->first();
            if($user){
                if($user->type=='user'){
                    $data = [
                        'email' => $request->email,
                        'password' => $request->password
                    ];
                    if($request->password==""){
                        $token = $user->createToken('LaravelAuthApp')->accessToken;
                        $data = $user;
                        $data->api_token = $token;
                        $data->id = (string)$data->id;
                        $data->otp = (string)$data->otp;
                        $data->isNewUser = true;
                        return response(\Helpers::sendSuccessAjaxResponse(__('message_alerts.registerd_successfully'),$data));
                    }else{
                        if (auth()->attempt($data)) {
                            $token = auth()->user()->createToken('LaravelAuthApp')->accessToken;
                            $data = auth()->user();
                            $data->api_token = $token;
                            $data->id = (string)$data->id;
                            $data->otp = (string)$data->otp;
                            return response(\Helpers::sendSuccessAjaxResponse('Login Successfully.',$data));
                        }else{
                            return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.unauthorized')));
                        }
                    }
                }else{
                    return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.use_valid_email_password')));
                }
            }else{
                return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.user_not_found')));
            }            
        }catch(\Exception $ex){
            return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.unauthorized')));
        }
    }

    /**
     * forgetPassword
     */
    public function forgetPassword(Request $request)
    {
        try {
            $post = $request->all();
            if(!empty($post)){
                $emailexist = User::where('email',$post['email'])->first();
                if($emailexist){
                    $otp = rand(1000,9999); 
                    //todo Email
                    $data = array();
                    $data['otp'] = $otp;
                    $c = \Helpers::sendEmail('emails.forgot-password',$data,$emailexist->email,'','Otp',setting('site_name'),'','');
                    return response(\Helpers::sendSuccessAjaxResponse('OTP Successfully sent.',$data));
                }else{
                    return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.something_went_wrong')));
                }
            }else{
                return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.something_went_wrong')));
            }
        } catch (\Exception $ex) {
            return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.there_is_an_error')));
        }
    }

    /**
     * resetPassword
     */
    public function resetPassword(Request $request)
    {
        try {
            $post = $request->all();
            if(!empty($post)){
                $inject = array();
                if(isset($post['password']) && $post['password'] != ''){
                    $inject['password'] = bcrypt($post['password']);
                }
                $user = User::where('email',$post['email'])->first();
                User::where('id',$user->id)->update($inject);
                return response(\Helpers::sendSuccessAjaxResponse(__('message_alerts.password_reset_success'),$post));
            }else{
                return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.something_went_wrong')));
            }
        } catch (\Exception $ex) {
            return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.there_is_an_error')));
        }
    }
    /**
     * updateProfile
     */
    public function updateProfile(Request $request)
    {
        try {
            $post = $request->all();
            if(!empty($post)){
                $emailexist = User::where('email',$post['email'])->where('id','!=',$post['id'])->get();
                if(count($emailexist)){
                    return response(\Helpers::sendFailureAjaxResponse('Email already taken'));
                }
                $name = '';
                $img = '';

                $file=$request->file('photo');
                if($file!=''){
	                $name = time() . rand() .'.'.$file->getClientOriginalExtension();
	                $destination =  public_path('/upload/user/').$name;
	                $c = \Helpers::compress_image($file,$destination,30);
	            }

                $inject = array();
                if(isset($post['photo']) && $post['photo'] !=''){
                	$img = $post['photo'];
                    $inject['photo'] =$post['photo'];
                }
                if(isset($post['name']) && $post['name'] != ''){
                    $inject['name'] = $post['name'];
                }
                if(isset($post['email']) && $post['email'] != ''){
                    $inject['email'] = $post['email'];
                }
                if(isset($post['gender']) && $post['gender'] != ''){
                    $inject['gender'] = $post['gender'];
                }
                if(isset($post['phone']) && $post['phone'] != ''){
                    $inject['phone'] = $post['phone'];
                }
                if(isset($post['password']) && $post['password'] != ''){
                    $inject['password'] = $post['password'];
                }
                if(isset($post['lang_code']) && $post['lang_code'] != ''){
                    $inject['lang_code'] = $post['lang_code'];
                }
                User::where('id',$post['id'])->update($inject);
                $data = User::where('id',$post['id'])->first();
                if($data){
	                $data->id = (string)$data->id;
	                $data->otp = (string)$data->otp;
	            }
                return response(\Helpers::sendSuccessAjaxResponse(__('message_alerts.profile_updated_success'),$data));
            }else{
                return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.something_went_wrong')));
            }
        } catch (\Exception $ex) {
            return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.there_is_an_error')));
        }
    }

    /**
     * Update Profile Picture 
     */
    public function updateProfilePicture(Request $request)
    {
        try {
            $post = $request->all();
            if(!empty($post)){
                if(isset($_FILES['photo'])){
                    $image = $_FILES['photo'];
                    $name = time() . rand() .'.png';
                    $imagepath = public_path('upload/user/');
                    $tmp_name = $_FILES["photo"]["tmp_name"];
                    $path = $imagepath.$name;
                    $post['photo'] = url('upload/user/').'/'.$name;
                    move_uploaded_file($_FILES['photo']['tmp_name'], $path);
                }
                $img = '';
                $inject = array();
                if(isset($post['photo']) && $post['photo'] !=''){
                    $img = $post['photo'];
                    $inject['photo'] =$post['photo'];
                }
                User::where('id',$post['id'])->update($inject);
                $data = User::where('id',$post['id'])->first();    
                return response(\Helpers::sendSuccessAjaxResponse(__('message_alerts.profile_updated_success'),$data));
            }else{
                return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.something_went_wrong')));
            }
        } catch (\Exception $ex) {
            return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.there_is_an_error')));
        }
    }


    /**
     * updateProfile
     */
    public function getProfile(Request $request)
    {
        try {
            $post = $request->all();
            if(isset($post['id'])){
                $userData = User::where('id',$post['id'])->first();
                $userData->id = (string)$userData->id;
                $userData->otp = (string)$userData->otp;
                return response(\Helpers::sendSuccessAjaxResponse(__('message_alerts.profile_updated_success'),$userData));
            }else{
                return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.something_went_wrong')));
            }
        } catch (\Exception $ex) {
            return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.there_is_an_error')));
        }
    }
    /**
     * updateProfile
     */
    public function updateToken(Request $request)
    {

        try {
            $post = $request->all();
            if(!empty($post)){
                $inject = array();
                if(isset($post['device_token']) && $post['device_token'] != ''){
                    $inject['device_token'] = $post['device_token'];
                }
                User::where('id',$post['id'])->update($inject);
                $data = User::where('id',$post['id'])->first();
                $data->id = (string)$data->id;
                $data->otp = (string)$data->otp;
                return response(\Helpers::sendSuccessAjaxResponse(__('message_alerts.device_updated_success'),$data));
            }else{
                return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.something_went_wrong')));
            }
        } catch (\Exception $ex) {
            return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.there_is_an_error')));
        }
    }

    /**
     * Delete Account
     */
    public function deleteAccount(Request $request)
    {
        try {
            $post = $request->all();
            if(!empty($post)){
                User::where('id',$post['id'])->delete();
                return response(\Helpers::sendSuccessAjaxResponse(__('message_alerts.account_deleted_success'),[]));
            }else{
                return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.something_went_wrong')));
            }
        } catch (\Exception $ex) {
            return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.there_is_an_error')));
        }
    }

     /**
     * Registration Req
     */
    public function socialMediaLogin(Request $request)
    {
        try {
            $post = $request->all();
            $validate = [
                'name' => 'required|min:4',
                'email' => 'required',
            ];
            $validator = Validator::make($post, $validate);
            if ($validator->fails()) {
                $data['error'] = $validator->errors();

                return response(\Helpers::sendFailureAjaxResponse($data['error']));
            }else{
                $emailexist = User::where('email',$post['email'])->first();
                if($emailexist){
                    $data = $emailexist;
                    if($data->photo==null){
                        $data->photo = url('upload/user/default.png');
                    }
                    $data->isNewUser = false;

                    $token = $data->createToken('LaravelAuthApp')->accessToken;
                    $data->api_token = $token;
                    if($data->photo==null){
                        $data->photo = url('upload/user/default.png');
                    }
                    $data->isNewUser = true;
                    $data->id = (string)$data->id;
                    $data->otp = (string)$data->otp;
                    return response(\Helpers::sendSuccessAjaxResponse(__('message_alerts.registerd_successfully'),$data));

                }else{

                    $login_from = 'facebook';
                    if(isset($post['login_from'])){
                        $login_from = $post['login_from'];
                    }

                    if(isset($request->phone) && $request->phone !=''){
                        $phone = $request->phone;
                    }else{
                        $phone = "";
                    }

                    if(isset($post['fb_token']) && $post['fb_token'] !=''){
                        $fb_token = $post['fb_token'];
                    }else{
                        $fb_token = "";
                    }

                    if(isset($post['google_token'])){
                        $google_token = $post['google_token'];
                    }else{
                        $google_token = "";
                    }

                    if(isset($post['device_token'])){
                        $device_token = $post['device_token'];
                    }else{
                        $device_token = "";
                    }
                    $image = '';
                    $saved_image = '';
                    if($login_from=='facebook' && $request->fb_id!=''){
                        $image = "https://graph.facebook.com/v2.9/".$request->fb_id."/picture?width=360&height=360";
                    }else{
                        $image = $request->image;
                    }
                    if($image!=''){
                        $content = file_get_contents($image);
                        $name = time() . rand() .'.png';
                        $imagepath = public_path('upload/user/');
                        $fp = fopen($imagepath.''.$name, "w");
                        fwrite($fp, $content);
                        fclose($fp);
                        $saved_image = url('upload/user/').'/'.$name;
                    }
                    
                    $id = User::insertGetId([
                        'name' => $request->name,
                        'email' => $request->email,
                        'photo'=>$saved_image,
                        'fb_token'=>$fb_token,
                        'google_token'=>$google_token,
                        'login_from'=>$login_from,
                        'active' => 1,
                    ]);
                    $data = User::where('id',$id)->first();
                    
                    $token = $data->createToken('LaravelAuthApp')->accessToken;
                    $data->api_token = $token;
                    if($data->photo==null){
                        $data->photo = url('upload/user/default.png');
                    }
                    $data->isNewUser = true;
                    $data->id = (string)$data->id;
                    $data->otp = (string)$data->otp;
                    return response(\Helpers::sendSuccessAjaxResponse(__('message_alerts.registerd_successfully'),$data));
                }             
            }
        } catch (\Exception $ex) {
            return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.there_is_an_error')));
        }
    }

}
