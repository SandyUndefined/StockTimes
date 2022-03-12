<?php

use App\Models\User;
use App\Models\Blog;
use App\Models\Author;
use App\Models\Category;
use App\Models\SiteContent;
use UploadImage as Image;

class Helpers {

    public static function returnUserLangCode() {
        $language = setting('preferred_site_language');
        if (auth()->user() && auth()->user()->type == 'user') {
            if (auth()->user()->lang_code != '') {
                $language = auth()->user()->lang_code;
            }
        }else{
            if(isset($_COOKIE['lang_code']) && $_COOKIE['lang_code'] != '') {
                $language = $_COOKIE['lang_code'];
            }
        }
        return $language;
    }

    public static function get_file_extension($file_name) {
        return substr(strrchr($file_name,'.'),1);
    }

    public static function generateWatermarkImage($oldimg,$image_name,$type){
        $siteLogo =  SiteContent::where('key','site_logo')->first();
        $watermark = public_path('upload/watermark.png');
        if ($siteLogo && $siteLogo->value != '') {
            $file = public_path('upload/logo/').$siteLogo->value;
            if (is_file($file)) {
                $watermark = $file;
            }
        }
        $c = pathinfo($watermark, PATHINFO_EXTENSION);
        if (strtolower($c) == 'jpg') {
            $stamp = imagecreatefromjpeg($watermark);
        }else{
            $stamp = imagecreatefrompng($watermark);
        }

        $im = imagecreatefromjpeg($oldimg);
        $sx = imagesx($stamp);
        $sy = imagesy($stamp);

        header('Content-type: image/png');

        // for instagaram
        // ********************* use ths code for top right ******************/
            // imagecopy($im, $stamp, imagesx($im) - $sx, 0, 0, 0, imagesx($stamp), imagesy($stamp));
        // ********************* use ths code for top righ ******************/
        // $insta = public_path('upload/social-media-post/instagram/').$image_name;
        // imagepng($im,$insta);
        // imagedestroy($im);

        // for fb and twitter
        // ********************* use ths code for left bottom ******************/
            imagecopy($im, $stamp, 20, imagesy($im) - $sy - 20, 0, 0, imagesx($stamp), imagesy($stamp));
        // ********************* use ths code for top righ ******************/

        if($type == 'facebook'){
            $fb = public_path('upload/social-media-post/facebook/').$image_name;
            imagepng($im,$fb);
        }else if($type == 'twitter'){
            $twitter = public_path('upload/social-media-post/twitter/').$image_name;
            imagepng($im,$twitter);
        }else if($type == 'instagram'){
            $instagram = public_path('upload/social-media-post/instagram/').$image_name;
            imagepng($im,$instagram);
        }else{
            return false;
        }

        imagedestroy($im);
        return true;
    }


     public static function compress_image($source_url, $destination_url, $quality = 30, $name ='',$basePath='', $type = false){
        $info = getimagesize($source_url);
        if ($info['mime'] == 'image/jpeg') $image = imagecreatefromjpeg($source_url);
        elseif ($info['mime'] == 'image/gif') $image = imagecreatefromgif($source_url);
        elseif ($info['mime'] == 'image/png') $image = imagecreatefrompng($source_url);
        elseif ($info['mime'] == 'image/webp') $image = imagecreatefromwebp($source_url);
        $c = imagejpeg($image, $destination_url, $quality);
        if ($type) {
            $img = Image::make($destination_url);

            $img->resize(800, null, function ($constraint) {
                $constraint->aspectRatio();
            })->save($basePath.'800/'.$name);

            $img->resize(360, null, function ($constraint) {
                $constraint->aspectRatio();
            })->save($basePath.'360/'.$name);
           
        }
        return $c;
    }

            /*
        check an create slug
    */
    public static function createSlug($title,$in='blog',$whr=0,$alphaNum = false){
        if($alphaNum){
            $slug = Str::slug($title, '-');
        }else{
            $slug = Str::slug($title, '-');
        }
        if($in == 'blog'){            
            $slugExist = Blog::where(DB::raw('LOWER(slug)'),strtolower($slug))->where('id','!=',$whr)->get();
        }else if($in == 'category'){
            $slugExist = Category::where(DB::raw('LOWER(slug)'),strtolower($slug))->where('id','!=',$whr)->get();
        }
        if(count($slugExist)){
            $slug = Str::slug($title.'-'.Str::random(5).'-'.Str::random(5), '-');
            return $slug;
        }else{
            return $slug;
        }
    }

      /**
     * function for check empty value
     * @param $value
     */
    public static function checkEmpty($value = NULL){
        if(isset($value) && !empty($value)){
            $data = trim(strip_tags($value));
            return iconv('ISO-8859-1','ASCII//IGNORE',$data);
        }
        else {
            return NULL;
        }
    }


    /**
     * Send success ajax response
     *
     * @param string $message
     * @param array $result
     * @return array
     */
    public static function sendSuccessAjaxResponse($message = '', $result = []) {
        $response = [
            'status' => true,
            'message' => $message,
            'data' => $result
        ];

        return $response;
    }

    /**
     * Send failure ajax response
     *
     * @param string $message
     * @return array
     */
    public static function sendFailureAjaxResponse($message = '',$data=[]) {
        $message = $message == '' ? config('app.message.default_error') : $message;

        $response = [
            'status' => false,
            'message' => $message,
            'data' => $data
        ];

        return $response;
    }

    /**
     * function for send email
     */
    public static function sendEmail($template, $data, $toEmail, $toName, $subject, $fromName = '', $fromEmail = '',$attachment = '') {
        if ($fromEmail == '') {
            $fromEmail =setting('from_email');
        }
        try {
            $fromName = setting('site_name');
            $data = \Mail::send($template, $data, function ($message) use($toEmail, $toName, $subject, $data, $fromName, $fromEmail, $attachment) {
                $message->to($toEmail, $toName);
                $message->subject($subject);
                if ($fromEmail != '' && $fromName != '') {
                    $message->from($fromEmail, $fromName);
                }
                if($attachment != ''){
                    $message->attach($attachment);
                }
            });
            return 1;
        } catch (\Exception $ex) {
            return $ex;
        }
    }


    /**
     * Format Date
     * @param $date
     * @return formatted date
     */
    public static function formatDate($date, $not_available = true) {
        if ($date) {
            return date(config('app.date_format_php'), strtotime($date));
        } else {
            if ($not_available == false) {
                return '';
            }
            return '';
        }
    }

    /**
     * Format Date
     * @param $date
     * @return formatted date
     */
    public static function formatDateTime($date, $not_available = true) {
        if ($date) {
            return date(config('app.date_time_format_php'), strtotime($date));
        } else {
            if ($not_available == false) {
                return NULL;
            }
            return NULL;
        }
    }

    /**
     * Show error page
     * @return \Illuminate\Http\Response
     */
    public static function showErrorPage() {
        return response()->view('errors.error', [], 500);
    }
    
    /**
     * function for send push notification
     * @param $id device token
     * @param $msg message tobe sent for push notification 
     * @param $title title 
     * @param $key key for API
     * @return Generated new file name
     */
    public static function sendNotification($id,$msg,$title,$key,$image=false){  
        $url = "https://fcm.googleapis.com/fcm/send";
        $notification = array('title' =>$title , 'body' => $msg, 'sound' => 'default', 'badge' => '1',"image"=> $image);
        $arrayToSend = array('to' => $id, 'notification' => $notification,'priority'=>'high');
        $json = json_encode($arrayToSend);
        $headers = array();
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Authorization: key='. $key;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        curl_close($ch); 
    }

    public static function getYoutubeEmbedUrl($url){

        if (strpos($url, 'youtube') > 0) {
            $shortUrlRegex = '/youtu.be\/([a-zA-Z0-9_]+)\??/i';
            $longUrlRegex = '/youtube.com\/((?:embed)|(?:watch))((?:\?v\=)|(?:\/))(\w+)/i';
            if (preg_match($longUrlRegex, $url, $matches)) {
                $youtube_id = $matches[count($matches) - 1];
            }
            if (preg_match($shortUrlRegex, $url, $matches)) {
                $youtube_id = $matches[count($matches) - 1];
            }
            return 'https://www.youtube.com/embed/' . $youtube_id;
        }else{
            return false;
        }
    }
}