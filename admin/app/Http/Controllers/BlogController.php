<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Blog;
use App\Models\Category;
use App\Models\Author;
use App\Models\SiteContent;
use App\Models\User;
use App\Models\BlogImages;
use App\Models\Languages;
use App\Models\BlogTranslation;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Facebook\Exceptions\FacebookSDKException as FacebookSDKException;
use Facebook\Facebook as Facebook;
use UploadImage;
use File;
use Twitter;
use Auth;

class BlogController extends Controller
{

    private $api;
    private $accessToken;
    private $pageToken;
    private $pageId;
    
    public function __construct(Facebook $fb)
    {
        $this->middleware(function ($request, $next) use ($fb) {
            $this->accessToken = config('services.facebook.user_token');
            $fb->setDefaultAccessToken($this->accessToken);
            $this->api = $fb;
            return $next($request);
        });


        $this->middleware('permission:blog-list|blog-create|blog-edit|blog-delete', ['only' => ['index','addUpdateblog','editBlog','deleteBlog']]);
        $this->middleware('permission:blog-create', ['only' => ['editBlog','addUpdateblog','addBlog']]);
        $this->middleware('permission:blog-edit', ['only' => ['addUpdateCategory']]);
        $this->middleware('permission:blog-delete', ['only' => ['deleteBlog']]);
    }

    /**
     * Show Blog view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request,$layout = 'side-menu', $theme = 'light')
    {
        $blog = Blog::getAllBlog($request->all());
        return view('super-admin/blog.index', [
            'theme' => $theme,
            'page_name' => 'index',
            'side_menu' => array(),
            'layout' => $layout,
            'blog'=>$blog,
            'breadcrumb'=>'<a href="'.url('/').'" class="breadcrumb">'.trans("admin.dashboard").'</a><i data-feather="chevron-right" class="breadcrumb__icon"></i><a href="'.url('/blog/side-menu/light').'" class="breadcrumb--active">'.trans("admin.blog_post").'</a>'
        ]);
    }

    /**
     * Show Blog view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function addBlog($layout = 'side-menu', $theme = 'light')
    {
        $category = Category::getAllActiveCategory();
        $author = Author::getAllActiveAuthors();
        $Languages = Languages::get();

        return view('super-admin/blog.create', [
            'theme' => $theme,
            'page_name' => 'create',
            'side_menu' => array(),
            'layout' => $layout,
            'category'=>$category,
            'languages'=>$Languages,
            'voice_accent'=>config('constant.voice_accent'),
            'author'=>$author,
            'breadcrumb'=>'<a href="'.url('/').'" class="breadcrumb">'.trans("admin.dashboard").'</a><i data-feather="chevron-right" class="breadcrumb__icon"></i><a href="'.url('/blog/side-menu/light').'" class="breadcrumb">'.trans("admin.blog_post").'</a><i data-feather="chevron-right" class="breadcrumb__icon"></i><a href="'.url('/add-blog').'/" class="breadcrumb--active">'.trans("admin.add_blog").'</a>'

        ]);
    }

    /**
     * Show Edit Blog view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function editBlog($layout = 'side-menu', $theme = 'light',$id)
    {
        $category = Category::getAllActiveCategory();
        $author = Author::getAllActiveAuthors();
        $blog = Blog::find($id);
        $blog->schedule_time = date("H:i",strtotime($blog->schedule_date));
        $blog->schedule_date = date("Y-m-d",strtotime($blog->schedule_date));
        $blogImages = BlogImages::where('blog_id',$id)->get();

        $language =  Languages::where('language',setting('preferred_site_language'))->first();

        return view('super-admin/blog.edit', [
            'theme' => $theme,
            'page_name' => 'create',
            'side_menu' => array(),
            'layout' => $layout,
            'category'=>$category,
            'author'=>$author,
            'voice_accent'=>config('constant.voice_accent'),
            'blogImages'=>$blogImages,
            'language'=>$language,
            'blog'=>$blog,
            'breadcrumb'=>'<a href="'.url('/').'" class="breadcrumb">Dashboard</a><i data-feather="chevron-right" class="breadcrumb__icon"></i><a href="'.url('/blog/side-menu/light').'" class="breadcrumb">'.trans("admin.blog_post").'</a><i data-feather="chevron-right" class="breadcrumb__icon"></i><a href="'.url('/edit-blog').'/'.$id.'" class="breadcrumb--active">'.trans("admin.edit_blog").'</a>'

        ]);
    }


    /**
     * upload blog thumb image
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */


    public function uploadBlogThumbImage(Request $request){
        try {
            if($request->ajax()){
                $post = $request->all();
                $name = '';
                if($post['image']!=''){
                    $file=$request->file('image');
                    $name = time() . rand() .'.'.$file->getClientOriginalExtension();
                    $destination =  public_path('/upload/blog/thumb/original/').$name;
                    $basePath = public_path('/upload/blog/thumb/');
                    $c = \Helpers::compress_image($file,$destination,30,$name,$basePath,true);
                }
                return response(\Helpers::sendSuccessAjaxResponse(__('message_alerts.record_updated'),$name));
            }else{
              return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.invalid_request')));
            }
        } catch (\Exception $ex) {
            return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.there_is_an_error')));
        }
    }


         /**
     * upload banner image
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function uploadBannerImage(Request $request){
        try {
            if($request->ajax()){
                $post = $request->all();
                $name = '';
                if($post['image']!=''){
                    $file=$request->file('image');
                    $name = time() . rand() .'.'.$file->getClientOriginalExtension();
                    $destination =  public_path('/upload/blog/banner/original/').$name;
                    $basePath = public_path('/upload/blog/banner/');
                    $c = \Helpers::compress_image($file,$destination,30,$name,$basePath,true);
                }
                return response(\Helpers::sendSuccessAjaxResponse(__('message_alerts.record_updated'),$name));
            }else{
              return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.invalid_request')));
            }
        } catch (\Exception $ex) {
            return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.there_is_an_error')));
        }
    }


    /**
     * upload audio file
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function uploadAudioFIle(Request $request){
        try {
            if($request->ajax()){
                $post = $request->all();
                $name = '';
                $data = [];
                if($post['audio_file']!=''){
                    if($post['audio_file']!=''){
                        $images = $post['audio_file'];
                        $post['audio_file'] = time() . rand() .'.'.$images->getClientOriginalExtension();
                        $destinationPath = public_path('/upload/blog/audio');
                        $images->move($destinationPath, $post['audio_file']);
                        $name = $post['audio_file'];
                        $data['fullpath'] = url('/upload/blog/audio').'/'.$post['audio_file'];
                        $data['name'] = $name;
                    }
                }
                return response(\Helpers::sendSuccessAjaxResponse(__('message_alerts.record_updated'),$data));
            }else{
              return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.invalid_request')));
            }
        } catch (\Exception $ex) {
            return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.there_is_an_error')));
        }
    }


     /**
     * Function to Upload Category image.
     * @return Response
     */

    public function uploadMultipleBannerImage(Request $request){
        try {
            if($request->ajax()){
                $post = $request->all();
                $data['images']=[];
                $data['images_url']=[];
                $files=$request->file('image');
                foreach($files as $file){
                    $name = time() . rand() .'.'.$file->getClientOriginalExtension();
                    array_push($data['images'], $name);
                    $destination =  public_path('/upload/blog/banner/original/').$name;
                    $basePath = public_path('/upload/blog/banner/');
                    $c = \Helpers::compress_image($file,$destination,30,$name,$basePath,true);
                    if ($c) {
                        $img_url = url('/upload/blog/banner/original').'/'.$name;
                        array_push($data['images_url'], $img_url);
                    }
                }
                return response(\Helpers::sendSuccessAjaxResponse(__('message_alerts.record_updated'),$data));
            }else{
              return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.invalid_request')));
            }
        } catch (\Exception $ex) {
            return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.there_is_an_error')));
        }
    }


       /**
     * add update blog
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function addUpdateblog(Request $request)
    {
         try {
            if($request->ajax()){
                $post = $request->all();
                $languages = [];
                if (isset($post['language_code'])) {
                    $languages = $post['language_code'];
                    unset($post['language_code']);
                }

                if (isset($post['language[]'])) {
                    unset($post['language[]']);
                }
                $data['prefield'] = $post;
                $submittype = (!isset($post['submittype']))?'save':$post['submittype'];

                $add_blog_image = array();
                unset($post['_token']);
                if(isset($post['submittype'])){
                    unset($post['submittype']);
                }
                if(!isset($post['status'])){
                    $post['status'] = 1;
                }
                $postData = $post;

                if(isset($postData['image'])){
                    unset($postData['image']);
                }

                if(isset($post['is_featured']) && $post['is_featured'] == 'on'){
                    $postData['is_featured'] = 1;
                }else{
                    $postData['is_featured'] = 0;
                }

                if (isset($post['audio_file_upload'])) {
                    $postData['audio_file'] = $post['audio_file_upload'];
                }

                if(isset($post['video_url']) && $post['video_url'] != ''){
                    $postData['content_type'] = 'video';
                }elseif(isset($post['audio_file_upload']) && $post['audio_file_upload'] != ''){
                    $postData['content_type'] = 'audio';
                }else{
                    $postData['content_type'] = 'text';
                }

                if(isset($post['is_slider']) && $post['is_slider'] == 'on'){
                    $postData['is_slider'] = 1;
                }else{
                    $postData['is_slider'] = 0;
                }
                if(isset($post['is_editor_picks']) && $post['is_editor_picks'] == 'on'){
                    $postData['is_editor_picks'] = 1;
                }else{
                    $postData['is_editor_picks'] = 0;
                }
                if(isset($post['is_weekly_top_picks']) && $post['is_weekly_top_picks'] == 'on'){
                    $postData['is_weekly_top_picks'] = 1;
                }else{
                    $postData['is_weekly_top_picks'] = 0;
                }

                if(isset($post['is_voting_enable']) && $post['is_voting_enable'] == 'on'){
                    $postData['is_voting_enable'] = 1;
                }else{
                    $postData['is_voting_enable'] = 0;
                }
                $postData['created_by'] = Auth::User()->id;
                if(isset($post['schedule_date']) && $post['schedule_date'] != ''){
                    if(isset($post['schedule_time']) && $post['schedule_time'] != ''){
                        $date = date("Y-m-d",strtotime($post['schedule_date']));
                        $time = date("H:i:s",strtotime($post['schedule_time']));
                        $postData['schedule_date'] = $date." ".$time;
                    }else{
                        $postData['schedule_date'] = date("Y-m-d H:i:s");
                    }
                }

                unset($postData['schedule_time']);
                unset($postData['audio_file_upload']);

                if($submittype=='draft'){
                    $validate = [
                        'category_id' => 'required',
                        'title' => 'required',
                        'slug' => 'required',
                    ];
                    $validator = Validator::make($post, $validate);
                    if ($validator->fails()) {
                        $data['error'] = $validator->errors();
                        $error = '';
                        $errors = (array)$data['error'];
                        foreach ($errors as $row) {
                            foreach ($validate as $key => $value) {
                                if(isset($row[$key])){
                                    $error = $row[$key];
                                }
                            }
                        }
                        return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.there_is_an_error'),$data['prefield']));
                    }else{
                        $add_blog_image = array();
                        if(isset($post['schedule_date']) && $post['schedule_date'] != ''){
                            if(isset($post['schedule_time']) && $post['schedule_time'] != ''){
                                $date = date("Y-m-d",strtotime($post['schedule_date']));
                                $time = date("H:i:s",strtotime($post['schedule_time']));
                                $postData['schedule_date'] = $date." ".$time;
                            }else{
                                $postData['schedule_date'] = date("Y-m-d H:i:s");
                            }
                        }else{
                            $postData['schedule_date'] = date("Y-m-d H:i:s");
                        }

                        if (isset($post['slug'])) {
                            $slugExist = Blog::where(DB::raw('LOWER(slug)'),strtolower($post['slug']))->where('id','!=',0)->count();
                            if ($slugExist) {
                                return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.slug_exist'),[]));
                            }
                            $slug = $post['slug'];
                        }else{
                            $slug = \Helpers::createSlug($postData['title'],'blog',0,false);
                        }

                        $postData['slug'] = $slug;
                        $postData['status'] = 2;

                        $id = Blog::insertGetId($postData);

                        if($id){
                            if(isset($post['image'])){
                                if(count($post['image'])){
                                    for($v=0;$v<count($post['image']);$v++){
                                        $image_id = BlogImages::insertGetId(array('blog_id'=>$id,'image'=>$post['image'][$v],'created_at'=>date('Y-m-d H:i:s')));
                                        array_push($add_blog_image,$image_id);
                                    }
                                }
                            }

                            for($g=0; $g<count($languages);$g++){
                                $injectTransLation = array(
                                    'blog_id' =>$id,
                                    'language_code' =>$languages[$g],
                                    'title' =>$postData['title'],
                                    'tags' =>$postData['tags'],
                                    'description' =>$postData['description'],
                                    'seo_title' =>$postData['seo_title'],
                                    'seo_keyword' =>$postData['seo_keyword'],
                                    'seo_tag' =>$postData['seo_tag'],
                                    'seo_description' =>$postData['seo_description'],
                                    'created_at' =>date("Y-m-d H:i:s"),
                                );
                                BlogTranslation::insertGetId($injectTransLation);
                            }
                            return response(\Helpers::sendSuccessAjaxResponse(__('message_alerts.record_updated')));
                        }else{
                            return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.there_is_an_error')));
                        }
                    }
                }else{
                    if($post['status']==2){
                        if(isset($post['schedule_date']) && $post['schedule_date'] != ''){
                            if(isset($post['schedule_time']) && $post['schedule_time'] != ''){
                                $date = date("Y-m-d",strtotime($post['schedule_date']));
                                $time = date("H:i:s",strtotime($post['schedule_time']));
                                $postData['schedule_date'] = $date." ".$time;
                            }else{
                                $postData['schedule_date'] = date("Y-m-d H:i:s");
                            }                            
                        }else{
                            $postData['schedule_date'] = date("Y-m-d H:i:s");
                        }
                        $postData['status'] = 1;
                    }
                    if(isset($post['id']) && $post['id'] !='' & $post['id'] != 0){
                        unset($postData['created_by']);
                        if(isset($post['image'])){
                            if(count($post['image'])){
                                for($v=0;$v<count($post['image']);$v++){
                                    $image_id = BlogImages::insert(array('blog_id'=>$post['id'],'image'=>$post['image'][$v],'created_at'=>date('Y-m-d H:i:s')));
                                    array_push($add_blog_image,$image_id);
                                }
                            }
                        }

                        if (isset($post['slug'])) {
                            $slugExist = Blog::where(DB::raw('LOWER(slug)'),strtolower($post['slug']))->where('id','!=',$post['id'])->count();
                            if ($slugExist) {
                                return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.slug_exist'),[]));
                            }
                            $slug = $post['slug'];
                        }else{
                            $slug = \Helpers::createSlug($postData['title'],'blog',$post['id'],false);
                        }
                        $postData['slug'] = $slug;

                        if (isset($postData['language'])) {
                            unset($postData['language']);
                        }
                        Blog::where('id', $post['id'])->update($postData);
                        $blogtransExist = BlogTranslation::where('blog_id',$post['id'])->where('language_code',$post['language'])->first();
                         $injectTransLation = array(
                            'blog_id' =>$post['id'],
                            'language_code' =>$post['language'],
                            'title' =>$postData['title'],
                            'tags' =>$postData['tags'],
                            'description' =>$postData['description'],
                            'seo_title' =>$postData['seo_title'],
                            'seo_keyword' =>$postData['seo_keyword'],
                            'seo_tag' =>$postData['seo_tag'],
                            'seo_description' =>$postData['seo_description'],
                            
                        );
                        if ($blogtransExist) {
                           BlogTranslation::where('id',$blogtransExist->id)->update($injectTransLation);
                        }else{
                            $injectTransLation['created_at'] = date("Y-m-d H:i:s");
                            BlogTranslation::insertGetId($injectTransLation);
                        }
                    }else{


                        if(isset($post['schedule_date']) && $post['schedule_date'] != ''){
                            if(isset($post['schedule_time']) && $post['schedule_time'] != ''){
                                $date = date("Y-m-d",strtotime($post['schedule_date']));
                                $time = date("H:i:s",strtotime($post['schedule_time']));
                                $postData['schedule_date'] = $date." ".$time;
                            }else{
                                $postData['schedule_date'] = date("Y-m-d H:i:s");
                            }
                        }else{
                            $postData['schedule_date'] = date("Y-m-d H:i:s");
                        }


                        if (isset($post['slug'])) {
                            $slugExist = Blog::where(DB::raw('LOWER(slug)'),strtolower($post['slug']))->where('id','!=',0)->count();
                            if ($slugExist) {
                                return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.slug_exist'),[]));
                            }
                            $slug = $post['slug'];
                        }else{
                            $slug = $slug = \Helpers::createSlug($postData['title'],'blog',0,false);
                        }
                        
                        $postData['slug'] = $slug;
                        $id = Blog::insertGetId($postData);
                        $post['id'] = $id;

                        if($id){

                            for($g=0; $g<count($languages);$g++){

                                $injectTransLation = array(
                                    'blog_id' =>$id,
                                    'language_code' =>$languages[$g],
                                    'title' =>$postData['title'],
                                    'tags' =>$postData['tags'],
                                    'description' =>$postData['description'],
                                    'seo_title' =>$postData['seo_title'],
                                    'seo_keyword' =>$postData['seo_keyword'],
                                    'seo_tag' =>$postData['seo_tag'],
                                    'seo_description' =>$postData['seo_description'],
                                    'created_at' =>date("Y-m-d H:i:s"),
                                );
                                BlogTranslation::insertGetId($injectTransLation);
                            }


                            if(isset($post['image'])){

                                if(count($post['image'])){
                                    for($v=0;$v<count($post['image']);$v++){
                                        $image_id = BlogImages::insertGetId(array('blog_id'=>$id,'image'=>$post['image'][$v],'created_at'=>date('Y-m-d H:i:s')));
                                        array_push($add_blog_image,$image_id);
                                    }
                                }
                            }


                            $this->uploadPostOnSocial($id);
                            $blog_data = BlogImages::whereIn('id',$add_blog_image)->get();
                            foreach($blog_data as $blog_image_notification){
                                if($blog_image_notification->image!=null || $blog_image_notification->image!=''){
                                    $blog_image_notification->image = url('upload/blog/banner/original/'.$blog_image_notification->image);
                                }else{
                                    $blog_image_notification->image = url('upload/blog/banner/default.jpg');
                                }
                            }

                            if (setting('enable_notifications')) {

                                if(date("Y-m-d H:i",strtotime($postData['schedule_date'])) <= date("Y-m-d H:i")){
                                    $user = User::where('active',1)->get();
                                    $setting = SiteContent::where('key','firebase_msg_key')->first();
                                    foreach($user as $detail){
                                        if($detail->device_token!=null){
                                            \Helpers::sendNotification($detail->device_token,$postData['title'],'',$setting->value,$blog_data[0]->image);
                                        }
                                    }
                                }
                            }
                        }else{
                            return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.there_is_an_error')));
                        }
                    }

                    /*
                        Send Notifications
                    */
                    if($post['status']==2){
                        if(isset($post['image'])){                           
                            $image = url('upload/blog/banner/default.jpg');
                            $blogImageInfo = BlogImages::where('blog_id',$post['id'])->first();
                            if($blogImageInfo){
                                $image = url('upload/blog/banner/original/'.$blogImageInfo->image);
                            }

                            if (setting('enable_notifications')) {

                                if(date("Y-m-d H:i",strtotime($postData['schedule_date'])) <= date("Y-m-d H:i")){
                                    $user = User::where('active',1)->get();
                                    $setting = SiteContent::where('key','firebase_msg_key')->first();
                                    foreach($user as $detail){
                                        if($detail->device_token!=null){
                                            \Helpers::sendNotification($detail->device_token,$postData['title'],'',$setting->value,$image);
                                        }
                                    }
                                }
                            }
                        }else{
                            $image = url('upload/blog/banner/default.jpg');
                            $blogImageInfo = BlogImages::where('blog_id',$post['id'])->first();
                            if($blogImageInfo){
                                $image = url('upload/blog/banner/original/'.$blogImageInfo->image);
                            }

                            if (setting('enable_notifications')) {
                                if(date("Y-m-d H:i",strtotime($postData['schedule_date'])) <= date("Y-m-d H:i")){
                                    $user = User::where('active',1)->get();
                                    $setting = SiteContent::where('key','firebase_msg_key')->first();
                                    foreach($user as $detail){
                                        if($detail->device_token!=null){
                                            \Helpers::sendNotification($detail->device_token,$postData['title'],'',$setting->value,$image);
                                        }
                                    }
                                }
                            }
                        }
                        $this->uploadPostOnSocial($post['id']);
                    }
                    return response(\Helpers::sendSuccessAjaxResponse(__('message_alerts.record_updated')));
                }
            }else{
                return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.invalid_request')));
            }
        } catch (\Exception $ex) {
            return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.there_is_an_error').$ex));
        }
    }



    /**
     * Method to delete Blog
     * @param array $request post data, id
    */
    public function deleteBlog(Request $request,$id)
    {
        Blog::where('id', $id)->delete(); 
        BlogTranslation::where('blog_id',$id)->delete();     
        return back()->with('success',__('message_alerts.blog_deleted_success'));
    }

    /**
     * Method to delete Blog Image
     * @param array $request post data, id
    */
    public function deleteBlogImage(Request $request,$id)
    {
        $BlogImagesData = BlogImages::find($id);
        BlogImages::where('id', $id)->delete();   
        $remainImages =  BlogImages::where('blog_id',$BlogImagesData->blog_id)->get();
        return response(\Helpers::sendSuccessAjaxResponse(__('message_alerts.banner_image_deleted_success'),[]));   
    }

    /**
     * Method to change status of blog
     * @param array $request post data ,id ,status
    */
    public function changeBlogStatus(Request $request,$id,$status)
    {
        $post['status'] = $status;
        $post['id'] = $id;
        Blog::updateBlog($post);         
        return back()->with('success',__('message_alerts.status_changed_success'));  
    }

    public function executeCron(Request $request){

        if (setting('enable_notifications')) {
            $blog = Blog::where('schedule_date','>=',date("Y-m-d H:i"))->where('schedule_date','<',date('Y-m-d H:i',strtotime("+ 15 minutes")))->get();
            $image = url('upload/blog/banner/default.jpg');
            foreach ($blog as $row) {
                $blogImageInfo = BlogImages::where('blog_id',$row->id)->first();
                if($blogImageInfo){
                    $image = url('upload/blog/banner/original/'.$blogImageInfo->image);
                }
                $user = User::where('active',1)->get();
                $setting = SiteContent::where('key','firebase_msg_key')->first();
                foreach($user as $detail){
                    if($detail->device_token!=null){
                        \Helpers::sendNotification($detail->device_token,$row->title,'',$setting->value,$image);
                    }
                }
            }
        }
    }

    

    /**
     * upload post in social media
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */


    function uploadPostOnSocial($blog_id){
        $img = false;
        $imgName = false;
        $row =  BlogImages::where('blog_id',$blog_id)->first();
        $lastblog = Blog::find($blog_id);
        $img = public_path()."/upload/blog/banner/original/".$row->image;
        if (is_file($img)) {
            $imgName = $row->image;
            $id = Blog::where('id',$blog_id)->update(['scial_media_image'=>$imgName]);

            if (setting('fb_share') == 1) {
                $facebook = public_path('upload/social-media-post/facebook/').$imgName;
                $this->load($img);
                $this->resize(1200,630);                        
                $this->save($facebook, IMAGETYPE_JPEG);
                sleep(2);
                \Helpers::generateWatermarkImage($facebook,$imgName,'facebook');
            }

            if (setting('twitter_share') == 1) {
                $twitter = public_path('upload/social-media-post/twitter/').$imgName;
                $this->load($img);
                $this->resize(1024,512);                        
                $this->save($twitter, IMAGETYPE_JPEG); 
                sleep(2);
                \Helpers::generateWatermarkImage($twitter,$imgName,'twitter');
            }

            if (setting('instagram_share') == 1) {

                $instagram = public_path('upload/social-media-post/instagram/').$imgName;
                $this->load($img);
                $this->resize(1080,1080);                        
                $this->save($instagram, IMAGETYPE_JPEG); 
                sleep(2);
                \Helpers::generateWatermarkImage($instagram,$imgName,'instagram');
            }

            $blogDesc = 'Follow @'.setting('site_name').'Latest News & Updates App. Stay Blessed! Stay Connected!';

            $tags =  '';
            if($lastblog->tags != ''){
                $explodeTag =  explode(',',$lastblog->tags);
                for($c=0;$c<count($explodeTag); $c++){
                    $tags = $tags.' #'.$explodeTag[$c];
                }
            }
            $status =  $lastblog->title.' '.$blogDesc.' '.$tags;

            // facebook page post 

            if (setting('fb_share') == 1) {
                $fbImage = public_path("/upload/social-media-post/facebook/").$row->image;
                if (is_file($fbImage)) {
                    $fbUserId = config('services.facebook.user_id');
                    $pages =  $this->api->get(
                        '/'.$fbUserId.'/accounts',
                        $this->accessToken
                    );
                    $pagesResponse = $pages->getDecodedBody();
                    if(isset($pagesResponse['data'][0]) && !empty($pagesResponse['data'][0])){
                        $this->pageToken = $pagesResponse['data'][0]['access_token'];
                        $this->pageId = $pagesResponse['data'][0]['id'];
                        $img = $fbImage;
                        $this->api->setDefaultAccessToken($this->pageToken);
                        $response = $this->api->post('/'.$this->pageId.'/photos', [
                            'message' => $status,
                            'source'    =>  $this->api->fileToUpload($img),
                        ])->getGraphNode()->asArray();
                    }
                }
            }

            if (setting('twitter_share') == 1) {
             // twitter post 
                $tweetImage = public_path()."/upload/social-media-post/twitter/".$row->image;
                if (is_file($tweetImage)) {
                    $uploaded_media = Twitter::uploadMedia(['media' => File::get($tweetImage)]);
                    $tweets = Twitter::postTweet(['status' =>  $status, 'media_ids' => $uploaded_media->media_id_string]);
                    Blog::where('id',$blog_id)->update(array('tweet_published'=>1));
                }
            }
        }
    }


    /**
     * convert image size functions
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    function load($filename) {  
        $image_info = getimagesize($filename);
        $this->image_type = $image_info[2];
        if( $this->image_type == IMAGETYPE_JPEG ) {  
            $this->image = imagecreatefromjpeg($filename);
        } elseif( $this->image_type == IMAGETYPE_GIF ) { 
            $this->image = imagecreatefromgif($filename);
        } elseif( $this->image_type == IMAGETYPE_PNG ) { 
            $this->image = imagecreatefrompng($filename);
        }
        unset($image_info);
    }
 
    function save($filename, $image_type=IMAGETYPE_JPEG, $compression=75, $permissions=null) {  
        if( $image_type == IMAGETYPE_JPEG ) {
            imagejpeg($this->image,$filename,$compression);
        } elseif ( $image_type == IMAGETYPE_GIF ) {  
            imagegif($this->image,$filename);
        } elseif ( $image_type == IMAGETYPE_PNG ) {  
            imagepng($this->image,$filename);
        }
        if( $permissions != null) {  
            chmod($filename,$permissions); 
        }
    }
         
    function output($image_type=IMAGETYPE_JPEG) {  
        if( $image_type == IMAGETYPE_JPEG ) {
            imagejpeg($this->image);
        } elseif ( $image_type == IMAGETYPE_GIF ) {  
            imagegif($this->image);
        } elseif ( $image_type == IMAGETYPE_PNG ) {  
            imagepng($this->image);
        }
    }
     
    function getWidth() {  
        return imagesx($this->image);
    }
     
    function getHeight() {  
        return imagesy($this->image);
    }
     
    function resizeToHeight($height) {  
        $ratio = $height / $this->getHeight();
        $width = $this->getWidth() * $ratio;
        $this->resize($width,$height);
    }  
     
    function resizeToWidth($width) {
        $ratio = $width / $this->getWidth();
        $height = $this->getheight() * $ratio; $this->resize($width,$height);
    }  
     
    function scale($scale) {
        $width = $this->getWidth() * $scale/100;
        $height = $this->getheight() * $scale/100;
        $this->resize($width,$height);
    }  
     
    function resize($width,$height) {
        $new_image = imagecreatetruecolor($width, $height);
        imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight()); $this->image = $new_image;
    } 

     /**
     * Show Blog view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function slider(Request $request,$layout = 'side-menu', $theme = 'light')
    {
        $blog = Blog::getAllSliderBlog($request->all());
        return view('super-admin/blog.slider', [
            'theme' => $theme,
            'page_name' => 'index',
            'side_menu' => array(),
            'layout' => $layout,
            'blog'=>$blog,
            'breadcrumb'=>'<a href="'.url('/').'" class="breadcrumb">'.trans("admin.dashboard").'</a><i data-feather="chevron-right" class="breadcrumb__icon"></i><a href="'.url('/blog/side-menu/light').'" class="breadcrumb--active">'.trans("admin.slider_post").'</a>'
        ]);
    }

    /**
     * update category
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function update(Request $request){
        $posts = Blog::all();
        foreach ($posts as $post) {
            foreach ($request->order as $order) {
                if ($order['id'] == $post->id) {
                    Blog::where('id',$post->id)->update(['order' => $order['position']]);
                }
            }
        }
        return response(__('message_alerts.record_updated'), 200);
    }

    /**
     * Method to change status of blog
     * @param array $request post data ,id ,status
    */
    public function deleteSliderPost(Request $request,$id)
    {
        $post['is_slider'] = 0;
        $post['id'] = $id;
        Blog::updateBlog($post);         
        return back()->with('success',__('message_alerts.slider_post_deleted_success'));  
    }


    /**
     * send notification of specific blog
     * @param array $request post data ,id ,status
    */
    public function sendBlogNotification($id)
    {
        if (setting('enable_notifications')) {

            $blog = Blog::findOrfail($id);
            $image = url('upload/blog/banner/default.jpg');
            $blogImageInfo = BlogImages::where('blog_id',$blog->id)->first();
            if($blogImageInfo){
                $image = url('upload/blog/banner/original/'.$blogImageInfo->image);
            }
            $user = User::where('active',1)->get();
            foreach($user as $detail){
                if($detail->device_token!=null){
                    \Helpers::sendNotification($detail->device_token,$blog->title,'',setting('firebase_msg_key'),$image);
                }
            }
        }
        return back()->with('success',__('message_alerts.notification_sent'));  
    }

    

    public function convertimage(Request $request){
        $posts = Blog::orderBy('id','DESC')->get();
        foreach ($posts as $post) {
            $blgImg = BlogImages::where('blog_id',$post->id)->get();
            foreach ($blgImg as $blgImgRow) {
                $destination_url = public_path('/upload/blog/banner/original/').$blgImgRow->image;
                $name = $blgImgRow->image;
                if (is_file($destination_url)) {
                    $basePath = public_path('/upload/blog/banner/');
                    $img = \UploadImage::make($destination_url);
                        
                    $img->resize(800, null, function ($constraint) {
                        $constraint->aspectRatio();
                    })->save($basePath.'800/'.$name);
                    
                    $img->resize(360, null, function ($constraint) {
                        $constraint->aspectRatio();
                    })->save($basePath.'360/'.$name);
                }
            }
        }
        echo 'done'; die;
    }

    public function validateSlug(Request $request){
        try {
            if($request->ajax()){
                $post = $request->all();
                if($post['slug']!=''){
                    $slugExist = Blog::where(DB::raw('LOWER(slug)'),strtolower($post['slug']))->count();
                    if ($slugExist) {
                        return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.slug_exist'),[]));
                    }else{
                        return response(\Helpers::sendSuccessAjaxResponse(__('message_alerts.record_updated'),[]));
                    }
                    // $file=$request->file('image');
                    // $name = time() . rand() .'.'.$file->getClientOriginalExtension();
                    // $destination =  public_path('/upload/user/').$name;
                    // $c = \Helpers::compress_image($file,$destination,30);
                }else{
                    return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.invalid_request')));                   
                }
            }else{
              return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.invalid_request')));
            }
        } catch (\Exception $ex) {
            return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.there_is_an_error')));
        }
    }

}
