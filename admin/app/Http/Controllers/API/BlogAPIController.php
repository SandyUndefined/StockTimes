<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Blog;
use App\Models\Category;
use App\Models\Author;
use App\Models\SiteContent;
use App\Models\SearchLog;
use App\Models\BookMarkPost;
use App\Models\BlogViewCount;
use App\Models\BlogImages;
use App\Models\Vote;
use App\Models\User;
use App\Models\BlogTranslation;
use App\Models\CategoryTranslation;

class BlogAPIController extends Controller
{    

    private $language;
    public function __construct(Request $request){
        $this->request = $request;
        $newUserId = $this->request->header('userData');

        $user  =  User::find($newUserId);
        if ($user && $user->lang_code != '') {
           $this->language = ($request->header('lang-code') && $request->header('lang-code') != '' ? $request->header('lang-code') : $user->lang_code);
        }else{
            $this->language = ($request->header('lang-code') && $request->header('lang-code') != '' ? $request->header('lang-code') : setting('preferred_site_language'));
        }
    }


    /**
     * Show 7 Blogs.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function list(Request $request)
    {
        try {
            $header = $request->header('userData');
            $blog_image = array();
            $final_blog = array();
            $blog = Blog::where('status',1)->where('is_featured',1)->where('schedule_date',"<=",date("Y-m-d H:i:s"))->orderBy('schedule_date','DESC')->limit(14)->get();
            if($blog){
                foreach ($blog as $row) {
                    $flag = false;
                    $blogTranslate = BlogTranslation::where('blog_id',$row->id)->where('language_code',$this->language)->first();
                    if ($blogTranslate) {
                        $flag = true;

                        $row->title = $blogTranslate->title;
                        $row->tags = $blogTranslate->tags;
                        $row->description = $blogTranslate->description;
                        $row->seo_title = $blogTranslate->seo_title;
                        $row->seo_keyword = $blogTranslate->seo_keyword;
                        $row->seo_tag = $blogTranslate->seo_tag;
                        $row->seo_description = $blogTranslate->seo_description;
                    }

                    if(str_word_count($row->description)>62){
                        $row->description = substr($row->description,0,420);
                        $row->description = $row->description.".....";
                    } 
                    $row->trimed_description = strip_tags($row->description);
                    $row->trimed_description =  str_replace("&nbsp;",'',$row->trimed_description); 
                    $row->trimed_description =  str_replace("&#39;","'",$row->trimed_description);

                    if($row->thumb_image!=''){
                        $row->thumb_image= url('upload/blog/thumb/360/'.$row->thumb_image);
                    }else{
                        $row->thumb_image= url('upload/blog/thumb/default.png');
                    } 
                    $check_image = BlogImages::where('blog_id',$row->id)->pluck('image');  
                    $blog_image = array();
                    if(count($check_image)){
                        foreach ($check_image as $value) {
                            $value = url('upload/blog/banner/800/'.$value);
                            array_push($blog_image,$value);
                        }
                        $row->banner_image = $blog_image;
                    }else{
                        $blog_image[0] = url('upload/author/default.png');
                        $row->banner_image =  $blog_image;
                    }
                    if($header!=null){
                        $vote = Vote::where('user_id',$header)->where('blog_id',$row->id)->first();
                        if($vote){
                            $row->is_vote = 1;
                        }else{
                            $row->is_vote = 0;
                        }  
                        $bookmarked = BookMarkPost::where('user_id',$header)->where('blog_id',$row->id)->first();
                        if($bookmarked){
                            $row->is_bookmark = 1;
                        }else{
                            $row->is_bookmark = 0;
                        }                       
                    }else{
                        $row->is_vote = 0;
                        $row->is_bookmark = 0;
                    }
                    $row->view_count = BlogViewCount::where('blog_id',$row->id)->count();
                    $total_votes = Vote::where('blog_id',$row->id)->count();
                    $yes_votes = Vote::where('blog_id',$row->id)->where('vote',1)->count();
                    $no_votes = Vote::where('blog_id',$row->id)->where('vote',0)->count();
                    if($yes_votes!=0){
                        $yes_percent = ($yes_votes/$total_votes)*100;
                    }else{
                        $yes_percent = 0;
                    }
                    if($no_votes!=0){
                        $no_percent = ($no_votes/$total_votes)*100;
                    }else{
                        $no_percent = 0;
                    }                 
                    $row->yes_percent = round($yes_percent);
                    $row->no_percent = round($no_percent); 
                    $author = Author::where('id',$row->author_id)->first();
                    if($author){                    
                        $row->author_name = $author->name;
                        if($author->image!=null || $author->image!=''){
                            $row->image = url('upload/author/original/'.$author->image);
                        }else{
                            $row->image = url('upload/author/default.png');
                        }
                    }else{
                        $row->author_name = "";
                        $row->image = url('upload/author/default.png');
                    }
                    $category = Category::where('id',$row->category_id)->first();
                    if($category){ 
                        $catTranslate = CategoryTranslation::where('category_id',$category->id)->where('language_code',$this->language)->first();
                        if ($catTranslate) {
                            $category->name = $catTranslate->name;
                        }

                        $row->category_name = $category->name;
                        $row->color = $category->color;
                    }else{
                        $row->category_name = "";
                        $row->color = "";
                    }
                    $row->time = $row->time. " min";
                    $row->created_at = date("d-M-Y",strtotime($row->created_at));
                    $row->create_date = date("d M Y // h:i a",strtotime($row->schedule_date));
                    $blog_image = array();

                    if ($flag) {
                        array_push($final_blog,$row);                     
                    }
                }
                return $this->sendResponse($final_blog, __('message_alerts.blog_list'));
            }else{
                $blog= array();
                return $this->sendResponse($blog, __('message_alerts.blog_list'));
            }            
            
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage().$e->getLine(), 401);
        }
    }

    /**
     * Show All Blogs.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function allBloglist(Request $request)
    {
        try {
            $header = $request->header('userData');
            $blog_image = array();
            $final_blog = array();

            $blog = Blog::where('status',1)->where('schedule_date',"<=",date("Y-m-d H:i:s"))->orderBy('schedule_date','DESC')->get();
            if($blog){
                foreach ($blog as $row) {
                    $flag = false;
                    $blogTranslate = BlogTranslation::where('blog_id',$row->id)->where('language_code',$this->language)->first();
                    if ($blogTranslate) {
                        $flag = true;
                        $row->title = $blogTranslate->title;
                        $row->tags = $blogTranslate->tags;
                        $row->description = $blogTranslate->description;
                        $row->seo_title = $blogTranslate->seo_title;
                        $row->seo_keyword = $blogTranslate->seo_keyword;
                        $row->seo_tag = $blogTranslate->seo_tag;
                        $row->seo_description = $blogTranslate->seo_description;
                    }

                    if(str_word_count($row->description)>62){
                        $row->description = substr($row->description,0,420);
                        $row->description = $row->description.".....";
                    } 
                    $row->trimed_description = strip_tags($row->description);
                    $row->trimed_description =  str_replace("&nbsp;",'',$row->trimed_description); 
                    $row->trimed_description =  str_replace("&#39;","'",$row->trimed_description);
                    $row->created_at = date("d M Y h:i a",strtotime($row->created_at));
                    if($row->thumb_image!=''){
                        $row->thumb_image= url('upload/blog/thumb/360/'.$row->thumb_image);
                    }else{
                        $row->thumb_image= url('upload/blog/thumb/default.png');
                    } 
                    $check_image = BlogImages::where('blog_id',$row->id)->pluck('image');  
                    $blog_image = array();
                    if(count($check_image)){
                        foreach ($check_image as $value) {
                            $value = url('upload/blog/banner/800/'.$value);
                            array_push($blog_image,$value);
                        }
                        $row->banner_image = $blog_image;
                    }else{
                        $blog_image[0] = url('upload/author/default.png');
                        $row->banner_image =  $blog_image;
                    } 
                    if($header!=null){
                        $vote = Vote::where('user_id',$header)->where('blog_id',$row->id)->first();
                        if($vote){
                            $row->is_vote = 1;
                        }else{
                            $row->is_vote = 0;
                        } 
                        $bookmarked = BookMarkPost::where('user_id',$header)->where('blog_id',$row->id)->first();
                        if($bookmarked){
                            $row->is_bookmark = 1;
                        }else{
                            $row->is_bookmark = 0;
                        }                        
                    }else{
                        $row->is_vote = 0;
                        $row->is_bookmark = 0;
                    }
                    $total_votes = Vote::where('blog_id',$row->id)->count();
                    $yes_votes = Vote::where('blog_id',$row->id)->where('vote',1)->count();
                    $no_votes = Vote::where('blog_id',$row->id)->where('vote',0)->count();
                    $row->view_count = BlogViewCount::where('blog_id',$row->id)->count();
                    if($yes_votes!=0){
                        $yes_percent = ($yes_votes/$total_votes)*100;
                    }else{
                        $yes_percent = 0;
                    }
                    if($no_votes!=0){
                        $no_percent = ($no_votes/$total_votes)*100;
                    }else{
                        $no_percent = 0;
                    }                 
                    $row->yes_percent = round($yes_percent);
                    $row->no_percent = round($no_percent);  
                    $author = Author::where('id',$row->author_id)->first();
                    if($author){                    
                        $row->author_name = $author->name;
                        if($author->image!=null || $author->image!=''){
                            $row->image = url('upload/author/original/'.$author->image);
                        }else{
                            $row->image = url('upload/author/default.png');
                        }
                    }else{
                        $row->author_name = "";
                        $row->image = url('upload/author/default.png');
                    }
                    $category = Category::where('id',$row->category_id)->first();
                    if($category){  

                        $catTranslate = CategoryTranslation::where('category_id',$category->id)->where('language_code',$this->language)->first();
                        if ($catTranslate) {
                            $category->name = $catTranslate->name;
                        }

                        $row->category_name = $category->name;
                        $row->color = $category->color;
                    }else{
                        $row->category_name = "";
                        $row->color = "";
                    }
                    $row->time = $row->time. " min";
                    $row->create_date = date("d M Y // h:i a",strtotime($row->schedule_date));
                    if ($flag) {
                        array_push($final_blog,$row);                     
                    }
                }
                return $this->sendResponse($final_blog, __('message_alerts.blog_list'));
            }else{
                $blog= array();
                return $this->sendResponse($blog, __('message_alerts.blog_list'));
            }            
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 401);
        }
    }

    /**
     * Show Blog view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function detail($id,Request $request)
    {
        try {
            $blog = Blog::getActiveBlogDetail($id);
            if ($blog) {
                $blogTranslate = BlogTranslation::where('blog_id',$blog->id)->where('language_code',$this->language)->first();
                if ($blogTranslate) {
                    $blog->title = $blogTranslate->title;
                    $blog->tags = $blogTranslate->tags;
                    $blog->description = $blogTranslate->description;
                    $blog->seo_title = $blogTranslate->seo_title;
                    $blog->seo_keyword = $blogTranslate->seo_keyword;
                    $blog->seo_tag = $blogTranslate->seo_tag;
                    $blog->seo_description = $blogTranslate->seo_description;
                    return $this->sendResponse($blog, __('message_alerts.blog_list'));
                }
                return $this->sendError(__('message_alerts.blog_not_found'), 401);
            }
            
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 401);
        }
    }
    /**
     * Show Blog Search Result.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function searchBlog(Request $request)
    {
        try {
            $post = $request->all();
            $validate = [
                'title' => 'required|min:1',
            ];
            $validator = Validator::make($post, $validate);
            if ($validator->fails()) {
                $data['error'] = $validator->errors();
                return response(\Helpers::sendFailureAjaxResponse($data['error']));
            }else{
                $header = $request->header('userData');
                $blog_image = array();
                $final_blog = array();
                $blog = Blog::where('title','like', '%'.trim($post['title']). '%')->where('status',1)->where('schedule_date',"<=",date("Y-m-d H:i:s"))->get();
                if(count($blog)==0){
                    $blog = Blog::where('tags','like', '%'.trim($post['title']). '%')->where('status',1)->where('schedule_date',"<=",date("Y-m-d H:i:s"))->get();
                }
                if(count($blog)){
                    foreach ($blog as $row) {
                        $flag = false;
                        $blogTranslate = BlogTranslation::where('blog_id',$row->id)->where('language_code',$this->language)->first();
                        if ($blogTranslate) {
                            $flag = true;
                            $row->title = $blogTranslate->title;
                            $row->tags = $blogTranslate->tags;
                            $row->description = $blogTranslate->description;
                            $row->seo_title = $blogTranslate->seo_title;
                            $row->seo_keyword = $blogTranslate->seo_keyword;
                            $row->seo_tag = $blogTranslate->seo_tag;
                            $row->seo_description = $blogTranslate->seo_description;
                        }

                        if(str_word_count($row->description)>62){
                            $row->description = substr($row->description,0,420);
                            $row->description = $row->description.".....";
                        }                        
                        $row->trimed_description = strip_tags($row->description);
                        $row->trimed_description =  str_replace("&nbsp;",'',$row->trimed_description); 
                        $row->trimed_description =  str_replace("&#39;","'",$row->trimed_description);
                        $row->created_at = date("d M Y h:i a",strtotime($row->created_at));
                        if($row->thumb_image!=''){
                            $row->thumb_image= url('upload/blog/thumb/360/'.$row->thumb_image);
                        }else{
                            $row->thumb_image= url('upload/blog/thumb/default.png');
                        }
                        $check_image = BlogImages::where('blog_id',$row->id)->pluck('image');  
                        $blog_image = array();
                        if(count($check_image)){
                            foreach ($check_image as $value) {
                                $value = url('upload/blog/banner/800/'.$value);
                                array_push($blog_image,$value);
                            }
                            $row->banner_image = $blog_image;
                        }else{
                            $blog_image[0] = url('upload/author/default.png');
                            $row->banner_image =  $blog_image;
                        }  
                        if($header!=null){
                        $vote = Vote::where('user_id',$header)->where('blog_id',$row->id)->first();
                        if($vote){
                            $row->is_vote = 1;
                        }else{
                            $row->is_vote = 0;
                        } 
                        $bookmarked = BookMarkPost::where('user_id',$header)->where('blog_id',$row->id)->first();
                        if($bookmarked){
                            $row->is_bookmark = 1;
                        }else{
                            $row->is_bookmark = 0;
                        }                        
                    }else{
                        $row->is_vote = 0;
                        $row->is_bookmark = 0;
                    }
                    $total_votes = Vote::where('blog_id',$row->id)->count();
                    $yes_votes = Vote::where('blog_id',$row->id)->where('vote',1)->count();
                    $no_votes = Vote::where('blog_id',$row->id)->where('vote',0)->count();
                    $row->view_count = BlogViewCount::where('blog_id',$row->id)->count();
                    if($yes_votes!=0){
                        $yes_percent = ($yes_votes/$total_votes)*100;
                    }else{
                        $yes_percent = 0;
                    }
                    if($no_votes!=0){
                        $no_percent = ($no_votes/$total_votes)*100;
                    }else{
                        $no_percent = 0;
                    }                 
                    $row->yes_percent = round($yes_percent);
                    $row->no_percent = round($no_percent);  
                        $author = Author::where('id',$row->author_id)->first();
                        if($author){                    
                            $row->author_name = $author->name;
                            if($author->image!=null || $author->image!=''){
                                $row->image = url('upload/author/original/'.$author->image);
                            }else{
                                $row->image = url('upload/author/default.png');
                            }
                        }else{
                            $row->author_name = "";
                            $row->image = url('upload/author/default.png');
                        }
                        $category = Category::where('id',$row->category_id)->first();
                        if($category){          
                            $catTranslate = CategoryTranslation::where('category_id',$category->id)->where('language_code',$this->language)->first();
                            if ($catTranslate) {
                                $category->name = $catTranslate->name;
                            }
                            $row->category_name = $category->name;
                            $row->color = $category->color;
                        }else{
                            $row->category_name = "";
                            $row->color = "";
                        }
                        $row->time = $row->time. " min";
                        $row->create_date = date("d M Y // h:i a",strtotime($row->schedule_date));
                        if ($flag) {
                            array_push($final_blog,$row);                     
                        }

                    }
                    $log = array(
                        'user_id'=>$post['user_id'],
                        'search_keyword'=>$post['title'],
                        'search_count'=>count($blog),
                        'created_at'=>date('Y-m-d h:i:s')
                    );
                    SearchLog::addSearchLog($log);
                    return response(\Helpers::sendSuccessAjaxResponse(__('message_alerts.record_found'),$final_blog));
                }else{
                    return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.record_not_found')));
                }
            }
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 401);
        }
    }
    /**
     * Show list of settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function settingList(Request $request)
    {
        try {
            $app_name = SiteContent::where('key','app_name')->first();
            $app_subtitle = SiteContent::where('key','app_subtitle')->first();
            $app_image = SiteContent::where('key','bg_image')->first();   
            if($app_image){
                if($app_image->value!=''){
                    $app_image->value = url('upload/bg/'.$app_image->value);
                }else{
                    $app_image->value = url('upload/bg/default.jpg');
                }
            }
            $settings = array('app_name'=>$app_name->value,'app_image'=>$app_image->value,'app_subtitle'=>$app_subtitle->value);         
            return $this->sendResponse($settings, __('message_alerts.setting_list'));
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 401);
        }
    }


    /**
     * bookmarkPost
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
  
    public function bookmarkPost(Request $request)
    {
        try {
            $post = $request->all();
            $validate = [
                'user_id' => 'required',
                'blog_id' => 'required',
            ];
            $validator = Validator::make($post, $validate);
            if ($validator->fails()) {
                $data['error'] = $validator->errors();

                return response(\Helpers::sendFailureAjaxResponse($data['error']));
            }else{
                $alreadyBookmarked = BookMarkPost::where('user_id',$post['user_id'])->where('blog_id',$post['blog_id'])->count();
                if ($alreadyBookmarked) {
                    return $this->sendResponse([], 'Already Bookmarked');
                }else{
                    $data['blog'] = BookMarkPost::insertGetID(array('user_id'=>$post['user_id'],'blog_id'=>$post['blog_id'])); 
                    $bookmarked = BookMarkPost::where('user_id',$post['user_id'])->where('blog_id',$post['blog_id'])->first();
                    if($bookmarked){
                        $data['is_bookmark'] = 1;
                    }else{
                        $data['is_bookmark'] = 0;
                    } 
                   if ($data['blog']) {
                        return $this->sendResponse($data, __('message_alerts.bookmarked_success'));
                   }else{
                        return $this->sendError($data['blog'], 500);
                   }
                }
            }
        } catch (\Exception $ex) {
           return $this->sendError($ex->getMessage(), 401);
        }
    }


    /**
     * Delete bookmarkPost
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
  

    public function deleteBookmarkPost(Request $request)
    {
        try {
            $post = $request->all();
            $validate = [
                'user_id' => 'required',
                'blog_id' => 'required',
            ];
            $validator = Validator::make($post, $validate);
            if ($validator->fails()) {
                $data['error'] = $validator->errors();
                return response(\Helpers::sendFailureAjaxResponse($data['error']));
            }else{
                BookMarkPost::where('user_id',$post['user_id'])->where('blog_id',$post['blog_id'])->delete();
                return $this->sendResponse([], __('message_alerts.bookmark_removed'));
            }
        } catch (\Exception $ex) {
           return $this->sendError($ex->getMessage(), 401);
        }
    }
     /**
     * All bookmarked Post
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

     public function AllBookmarkPost(Request $request)
    {
        try {
            $post = $request->all();
            $blogs = array();
            $final_blog = array();

            $header = $request->header('userData');
            if (isset($post['category_id']) && $post['category_id'] != '' && $post['category_id'] !=0) {
                $blogs = Blog::where('category_id',$post['category_id'])->where('schedule_date',"<=",date("Y-m-d H:i:s"))->orderBy('schedule_date','DESC')->get();
                foreach($blogs as $row){
                    $flag = false;

                    $blogTranslate = BlogTranslation::where('blog_id',$row->id)->where('language_code',$this->language)->first();
                    if ($blogTranslate) {

                        $flag = true;
                        $row->title = $blogTranslate->title;
                        $row->tags = $blogTranslate->tags;
                        $row->description = $blogTranslate->description;
                        $row->seo_title = $blogTranslate->seo_title;
                        $row->seo_keyword = $blogTranslate->seo_keyword;
                        $row->seo_tag = $blogTranslate->seo_tag;
                        $row->seo_description = $blogTranslate->seo_description;
                    }


                    if(str_word_count($row->description)>62){
                        $row->description = substr($row->description,0,420);
                        $row->description = $row->description.".....";
                    } 
                    $row->trimed_description = strip_tags($row->description);
                    $row->trimed_description =  str_replace("&nbsp;",'',$row->trimed_description); 
                    $row->trimed_description =  str_replace("&#39;","'",$row->trimed_description);
                    $row->created_at = date("d M Y h:i a",strtotime($row->created_at));
                    if($row->thumb_image!=''){
                        $row->thumb_image= url('upload/blog/thumb/360/'.$row->thumb_image);
                    }else{
                        $row->thumb_image= url('upload/blog/thumb/default.png');
                    }  
                    $check_image = BlogImages::where('blog_id',$row->id)->pluck('image');  
                    $blog_image = array();
                    if(count($check_image)){
                        foreach ($check_image as $value) {
                            $value = url('upload/blog/banner/800/'.$value);
                            array_push($blog_image,$value);
                        }
                        $row->banner_image = $blog_image;
                    }else{
                        $blog_image[0] = url('upload/author/default.png');
                        $row->banner_image =  $blog_image;
                    } 
                    if($header!=null){
                        $vote = Vote::where('user_id',$header)->where('blog_id',$row->id)->first();
                        if($vote){
                            $row->is_vote = 1;
                        }else{
                            $row->is_vote = 0;
                        }
                        $bookmarked = BookMarkPost::where('user_id',$header)->where('blog_id',$row->id)->first();
                        if($bookmarked){
                            $row->is_bookmark = 1;
                        }else{
                            $row->is_bookmark = 0;
                        }                         
                    }else{
                        $row->is_vote = 0;
                        $row->is_bookmark = 0;
                    }
                    $total_votes = Vote::where('blog_id',$row->id)->count();
                    $yes_votes = Vote::where('blog_id',$row->id)->where('vote',1)->count();
                    $no_votes = Vote::where('blog_id',$row->id)->where('vote',0)->count();
                    $row->view_count = BlogViewCount::where('blog_id',$row->id)->count();
                    if($yes_votes!=0){
                        $yes_percent = ($yes_votes/$total_votes)*100;
                    }else{
                        $yes_percent = 0;
                    }
                    if($no_votes!=0){
                        $no_percent = ($no_votes/$total_votes)*100;
                    }else{
                        $no_percent = 0;
                    }                 
                    $row->yes_percent = round($yes_percent);
                    $row->no_percent = round($no_percent); 
                    $author = Author::where('id',$row->author_id)->first();
                    if($author){                    
                        $row->author_name = $author->name;
                        if($author->image!=null || $author->image!=''){
                            $row->image = url('upload/author/original/'.$author->image);
                        }else{
                            $row->image = url('upload/author/default.png');
                        }
                    }else{
                        $row->author_name = "";
                        $row->image = url('upload/author/default.png');
                    }
                    $category = Category::where('id',$row->category_id)->first();
                    if($category){      
                        $catTranslate = CategoryTranslation::where('category_id',$category->id)->where('language_code',$this->language)->first();
                        if ($catTranslate) {
                            $category->name = $catTranslate->name;
                        } 
                        $row->category_name = $category->name;
                        $row->color = $category->color;
                    }else{
                        $row->category_name = "";
                        $row->color = "";
                    }
                    $row->time = $row->time. " min";
                    $row->create_date = date("d M Y // h:i a",strtotime($row->schedule_date));

                    if ($flag) {
                        array_push($final_blog,$row);                     
                    }
                }
                return $this->sendResponse($final_blog,__('message_alerts.record_found'));
            }else{                
                $data = BookMarkPost::where('user_id',$post['user_id'])->OrderBy('created_at','DESC')->get();
                foreach ($data as $row) {
                    $blogdata = Blog::where('id',$row->blog_id)->first();
                    if($blogdata){
                        $flag = false;
                        $blogTranslate = BlogTranslation::where('blog_id',$blogdata->id)->where('language_code',$this->language)->first();
                        if ($blogTranslate) {
                            $flag = true;
                            $row->title = $blogTranslate->title;
                            $row->tags = $blogTranslate->tags;
                            $row->description = $blogTranslate->description;
                            $row->seo_title = $blogTranslate->seo_title;
                            $row->seo_keyword = $blogTranslate->seo_keyword;
                            $row->seo_tag = $blogTranslate->seo_tag;
                            $row->seo_description = $blogTranslate->seo_description;
                        }

                        if(str_word_count($blogdata->description)>62){
                            $blogdata->description = substr($blogdata->description,0,420);
                            $blogdata->description = $blogdata->description.".....";
                        } 
                        $blogdata->trimed_description = strip_tags($blogdata->description);
                        $blogdata->trimed_description =  str_replace("&nbsp;",'',$blogdata->trimed_description); 
                        $blogdata->trimed_description =  str_replace("&#39;","'",$blogdata->trimed_description);
                        $blogdata->created_at = date("d M Y h:i a",strtotime($blogdata->created_at));
                        if($blogdata->thumb_image!=''){
                            $blogdata->thumb_image= url('upload/blog/thumb/360/'.$blogdata->thumb_image);
                        }else{
                            $blogdata->thumb_image= url('upload/blog/thumb/default.png');
                        } 
                        $check_image = BlogImages::where('blog_id',$blogdata->id)->pluck('image');  
                        $blog_image = array();
                        if(count($check_image)){
                            foreach ($check_image as $value) {
                                $value = url('upload/blog/banner/800/'.$value);
                                array_push($blog_image,$value);
                            }
                            $blogdata->banner_image = $blog_image;
                        }else{
                            $blog_image[0] = url('upload/author/default.png');
                            $blogdata->banner_image =  $blog_image;
                        } 
                        if($header!=null){
                            $vote = Vote::where('user_id',$header)->where('blog_id',$blogdata->id)->first();
                            if($vote){
                                $blogdata->is_vote = 1;
                            }else{
                                $blogdata->is_vote = 0;
                            } 
                            $bookmarked = BookMarkPost::where('user_id',$header)->where('blog_id',$blogdata->id)->first();
                            if($bookmarked){
                                $blogdata->is_bookmark = 1;
                            }else{
                                $blogdata->is_bookmark = 0;
                            }                        
                        }else{
                            $blogdata->is_vote = 0;
                            $blogdata->is_bookmark = 0;
                        }
                        $total_votes = Vote::where('blog_id',$blogdata->id)->count();
                        $yes_votes = Vote::where('blog_id',$blogdata->id)->where('vote',1)->count();
                        $no_votes = Vote::where('blog_id',$blogdata->id)->where('vote',0)->count();
                        $blogdata->view_count = BlogViewCount::where('blog_id',$blogdata->id)->count();
                        if($yes_votes!=0){
                            $yes_percent = ($yes_votes/$total_votes)*100;
                        }else{
                            $yes_percent = 0;
                        }
                        if($no_votes!=0){
                            $no_percent = ($no_votes/$total_votes)*100;
                        }else{
                            $no_percent = 0;
                        }                 
                        $blogdata->yes_percent = round($yes_percent);
                        $blogdata->no_percent = round($no_percent);  
                        $author = Author::where('id',$blogdata->author_id)->first();
                        if($author){                    
                            $blogdata->author_name = $author->name;
                            if($author->image!=null || $author->image!=''){
                                $blogdata->image = url('upload/author/original/'.$author->image);
                            }else{
                                $blogdata->image = url('upload/author/default.png');
                            }
                        }else{
                            $blogdata->author_name = "";
                            $blogdata->image = url('upload/author/default.png');
                        }
                        $category = Category::where('id',$blogdata->category_id)->first();
                        if($category){     
                            $catTranslate = CategoryTranslation::where('category_id',$category->id)->where('language_code',$this->language)->first();
                            if ($catTranslate) {
                                $category->name = $catTranslate->name;
                            }

                            $blogdata->category_name = $category->name;
                            $blogdata->color = $category->color;
                        }else{
                            $blogdata->category_name = "";
                            $blogdata->color = "";
                        }
                        $blogdata->time = $blogdata->time. " min";
                        $blogdata->create_date = date("d M Y // h:i a",strtotime($blogdata->schedule_date));
                        //array_push($blogs,$blogdata);
                        if ($flag) {
                            array_push($final_blog,$blogdata);                     
                        }
                    }
                }
                return $this->sendResponse($final_blog,__('message_alerts.record_found'));
            }
        } catch (\Exception $ex) {
           return $this->sendError($ex->getMessage(), 401);
        }
    }


    /**
     * increaseBlogViewCount
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function increaseBlogViewCount(Request $request)
    {
        try {
            $post = $request->all();
            $validate = [
                'user_id' => 'required',
                'blog_id' => 'required',
            ];
            $validator = Validator::make($post, $validate);
            if ($validator->fails()) {
                $data['error'] = $validator->errors();
                return response(\Helpers::sendFailureAjaxResponse($data['error']));
            }else{

               $alreadyBookmarked = BlogViewCount::where('user_id',$post['user_id'])->where('blog_id',$post['blog_id'])->count();
                if ($alreadyBookmarked) {
                    return $this->sendResponse([],  __('message_alerts.already_viewed'));
                }else{
                   $blog = BlogViewCount::insertGetID(array('user_id'=>$post['user_id'],'blog_id'=>$post['blog_id'])); 
                   if ($blog) {
                        return $this->sendResponse($blog, __('message_alerts.successfully_viewed'));
                   }else{
                        return $this->sendError($blog, 500);
                   }
                }
            }
        } catch (\Exception $ex) {
           return $this->sendError($ex->getMessage(), 401);
        }
    }

    /**
     * Vote for the blog
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function addBlogVote(Request $request)
    {
        try {
            $post = $request->all();
            $validate = [
                'user_id' => 'required',
                'blog_id' => 'required',
            ];
            $validator = Validator::make($post, $validate);
            if ($validator->fails()) {
                $data['error'] = $validator->errors();
                return response(\Helpers::sendFailureAjaxResponse($data['error']));
            }else{
               $alreadyVoted = Vote::where('user_id',$post['user_id'])->where('blog_id',$post['blog_id'])->count();
                if ($alreadyVoted) {
                    return $this->sendResponse([], 'Already Viewed');
                }else{
                    $post['created_at'] = date("Y-m-d h:i:s");
                   $vote = Vote::addVote($post); 
                   if ($vote) {
                        return $this->sendResponse($vote,__('message_alerts.successfully_voted'));
                   }else{
                        return $this->sendError($vote, 500);
                   }
                }
            }
        } catch (\Exception $ex) {
           return $this->sendError($ex->getMessage(), 401);
        }
    }

     /**
     * For Swipe next blog
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

     public function blogSwipe(Request $request)
    {
        try {
            $header = $request->header('userData');
            $post = $request->all();
            $validate = [
                'blog_id' => 'required',
            ];
            $validator = Validator::make($post, $validate);
            if ($validator->fails()) {
                $data['error'] = $validator->errors();
                return response(\Helpers::sendFailureAjaxResponse($data['error']));
            }else{
                $blog_image = array();
                $final_blog = array();

                $blogs = array();
                $header = $request->header('userData');
                $singleBlog = Blog::where('id',$post['blog_id'])->where('schedule_date',"<=",date("Y-m-d H:i:s"))->first();
                if($singleBlog){
                    $flag =false;
                    $blogTranslate = BlogTranslation::where('blog_id',$singleBlog->id)->where('language_code',$this->language)->first();
                    if ($blogTranslate) {
                        $flag = true;
                        $singleBlog->title = $blogTranslate->title;
                        $singleBlog->tags = $blogTranslate->tags;
                        $singleBlog->description = $blogTranslate->description;
                        $singleBlog->seo_title = $blogTranslate->seo_title;
                        $singleBlog->seo_keyword = $blogTranslate->seo_keyword;
                        $singleBlog->seo_tag = $blogTranslate->seo_tag;
                        $singleBlog->seo_description = $blogTranslate->seo_description;
                    }

                    if(str_word_count($singleBlog->description)>62){
                            $singleBlog->description = substr($singleBlog->description,0,420);
                            $singleBlog->description = $singleBlog->description.".....";
                        } 
                    $singleBlog->trimed_description = strip_tags($singleBlog->description);
                    $singleBlog->trimed_description =  str_replace("&nbsp;",'',$singleBlog->trimed_description); 
                    $singleBlog->trimed_description =  str_replace("&#39;","'",$singleBlog->trimed_description);
                    $singleBlog->created_at = date("d M Y h:i a",strtotime($singleBlog->created_at));
                    if($singleBlog->thumb_image!=''){
                        $singleBlog->thumb_image= url('upload/blog/thumb/360/'.$singleBlog->thumb_image);
                    }else{
                        $singleBlog->thumb_image= url('upload/blog/thumb/default.png');
                    }  
                    $check_image = BlogImages::where('blog_id',$singleBlog->id)->pluck('image');  
                    $blog_image = array();
                    if(count($check_image)){
                        foreach ($check_image as $value) {
                            $value = url('upload/blog/banner/800/'.$value);
                            array_push($blog_image,$value);
                        }
                        $singleBlog->banner_image = $blog_image;
                    }else{
                        $blog_image[0] = url('upload/author/default.png');
                        $singleBlog->banner_image =  $blog_image;
                    } 
                    if($header!=null){
                        $vote = Vote::where('user_id',$header)->where('blog_id',$singleBlog->id)->first();
                        if($vote){
                            $singleBlog->is_vote = 1;
                        }else{
                            $singleBlog->is_vote = 0;
                        } 
                        $bookmarked = BookMarkPost::where('user_id',$header)->where('blog_id',$singleBlog->id)->first();
                        if($bookmarked){
                            $singleBlog->is_bookmark = 1;
                        }else{
                            $singleBlog->is_bookmark = 0;
                        }                        
                    }else{
                        $singleBlog->is_vote = 0;
                        $singleBlog->is_bookmark = 0;
                    }
                    $total_votes = Vote::where('blog_id',$singleBlog->id)->count();
                    $yes_votes = Vote::where('blog_id',$singleBlog->id)->where('vote',1)->count();
                    $no_votes = Vote::where('blog_id',$singleBlog->id)->where('vote',0)->count();
                    $singleBlog->view_count = BlogViewCount::where('blog_id',$row->id)->count();
                    if($yes_votes!=0){
                        $yes_percent = ($yes_votes/$total_votes)*100;
                    }else{
                        $yes_percent = 0;
                    }
                    if($no_votes!=0){
                        $no_percent = ($no_votes/$total_votes)*100;
                    }else{
                        $no_percent = 0;
                    }                 
                    $singleBlog->yes_percent = round($yes_percent);
                    $singleBlog->no_percent = round($no_percent); 
                    $author = Author::where('id',$singleBlog->author_id)->first();
                    if($author){                    
                        $singleBlog->author_name = $author->name;
                        if($author->image!=null || $author->image!=''){
                            $singleBlog->image = url('upload/author/original/'.$singleBlog->image);
                        }else{
                            $singleBlog->image = url('upload/author/default.png');
                        }
                    }else{
                        $singleBlog->author_name = "";
                        $singleBlog->image = url('upload/author/default.png');
                    }
                    $category = Category::where('id',$singleBlog->category_id)->first();
                    if($category){  
                        $catTranslate = CategoryTranslation::where('category_id',$category->id)->where('language_code',$this->language)->first();
                        if ($catTranslate) {
                            $category->name = $catTranslate->name;
                        }                
                        $singleBlog->category_name = $category->name;
                        $singleBlog->color = $category->color;
                    }else{
                        $singleBlog->category_name = "";
                        $singleBlog->color="";
                    }
                    $singleBlog->time = $singleBlog->time. " min";
                    $singleBlog->create_date = date("d M Y // h:i a",strtotime($singleBlog->schedule_date));
                    // $blogs[0] = $singleBlog;

                    if ($flag) {
                        array_push($final_blog,$singleBlog);                     
                    }

                }                
                $getBlog = Blog::where('status',1)->where('id','!=',$post['blog_id'])->where('schedule_date',"<=",date("Y-m-d H:i:s"))->orderBy('schedule_date','DESC')->get();
                foreach ($getBlog as $row) {
                    $newFlag = false;
                    $blogTranslate = BlogTranslation::where('blog_id',$row->id)->where('language_code',$this->language)->first();
                    if ($blogTranslate) {
                        $newFlag = true;
                        $row->title = $blogTranslate->title;
                        $row->tags = $blogTranslate->tags;
                        $row->description = $blogTranslate->description;
                        $row->seo_title = $blogTranslate->seo_title;
                        $row->seo_keyword = $blogTranslate->seo_keyword;
                        $row->seo_tag = $blogTranslate->seo_tag;
                        $row->seo_description = $blogTranslate->seo_description;
                    }

                    if(str_word_count($row->description)>62){
                        $row->description = substr($row->description,0,420);
                        $row->description = $row->description.".....";
                    } 
                    $row->trimed_description = strip_tags($row->description);
                    $row->trimed_description =  str_replace("&nbsp;",'',$row->trimed_description); 
                    $row->trimed_description =  str_replace("&#39;","'",$row->trimed_description);
                    $row->created_at = date("d M Y h:i a",strtotime($row->created_at));
                    if($row->thumb_image!=''){
                        $row->thumb_image= url('upload/blog/thumb/360/'.$row->thumb_image);
                    }else{
                        $row->thumb_image= url('upload/blog/thumb/default.png');
                    }  
                    $check_image = BlogImages::where('blog_id',$row->id)->pluck('image');  
                    $blog_image = array();
                    if(count($check_image)){
                        foreach ($check_image as $value) {
                            $value = url('upload/blog/banner/800/'.$value);
                            array_push($blog_image,$value);
                        }
                        $row->banner_image = $blog_image;
                    }else{
                        $blog_image[0] = url('upload/author/default.png');
                        $row->banner_image =  $blog_image;
                    } 
                    if($header!=null){
                        $votes = Vote::where('user_id',$header)->where('blog_id',$row->id)->first();
                        if($votes){
                            $row->is_vote = 1;
                        }else{
                            $row->is_vote = 0;
                        } 
                        $bookmarked = BookMarkPost::where('user_id',$header)->where('blog_id',$row->id)->first();
                        if($bookmarked){
                            $row->is_bookmark = 1;
                        }else{
                            $row->is_bookmark = 0;
                        }                        
                    }else{
                        $row->is_vote = 0;
                        $row->is_bookmark = 0;
                    }
                    $total_votes = Vote::where('blog_id',$row->id)->count();
                    $yes_votes = Vote::where('blog_id',$row->id)->where('vote',1)->count();
                    $no_votes = Vote::where('blog_id',$row->id)->where('vote',0)->count();
                    $row->view_count = BlogViewCount::where('blog_id',$row->id)->count();
                    if($yes_votes!=0){
                        $yes_percent = ($yes_votes/$total_votes)*100;
                    }else{
                        $yes_percent = 0;
                    }
                    if($no_votes!=0){
                        $no_percent = ($no_votes/$total_votes)*100;
                    }else{
                        $no_percent = 0;
                    }                 
                    $row->yes_percent = round($yes_percent);
                    $row->no_percent = round($no_percent);  
                    $author = Author::where('id',$row->author_id)->first();
                    if($author){                    
                        $row->author_name = $author->name;
                        if($author->image!=null || $author->image!=''){
                            $row->image = url('upload/author/original/'.$author->image);
                        }else{
                            $row->image = url('upload/author/default.png');
                        }
                    }else{
                        $row->author_name = "";
                        $row->image = url('upload/author/default.png');
                    }
                    $category = Category::where('id',$row->category_id)->first();
                    if($category){  
                        $catTranslate = CategoryTranslation::where('category_id',$category->id)->where('language_code',$this->language)->first();
                        if ($catTranslate) {
                            $category->name = $catTranslate->name;
                        }                
                        $row->category_name = $category->name;
                        $row->color = $category->color;
                    }else{
                        $row->category_name = "";
                        $row->color = "";
                    }
                    $row->time = $row->time. " min";
                    $row->create_date = date("d M Y // h:i a",strtotime($row->schedule_date));
                    // array_push($blogs,$row);
                    if ($newFlag) {
                        array_push($final_blog,$row);                     
                    }
                }
                return $this->sendResponse($final_blog,__('message_alerts.record_found'));
            }
        } catch (\Exception $ex) {
           return $this->sendError($ex->getMessage(), 401);
        }
    }

    /**
     * Show Blog votes.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getBlogVote(Request $request)
    {
        try {
            $post = $request->all();
            $validate = [
                'blog_id' => 'required|min:1',
            ];
            $validator = Validator::make($post, $validate);
            if ($validator->fails()) {
                $data['error'] = $validator->errors();
                return response(\Helpers::sendFailureAjaxResponse($data['error']));
            }else{
                $header = $request->header('userData');
                $blog_image = array();
                $blog = Blog::where('id',$post['blog_id'])->where('schedule_date',"<=",date("Y-m-d H:i:s"))->first();
                if($blog){

                    $flag = false;

                        $blogTranslate = BlogTranslation::where('blog_id',$blog->id)->where('language_code',$this->language)->first();

                        if ($blogTranslate) {

                            $flag = true;


                            $blog->title = $blogTranslate->title;
                            $blog->tags = $blogTranslate->tags;
                            $blog->description = $blogTranslate->description;
                            $blog->seo_title = $blogTranslate->seo_title;
                            $blog->seo_keyword = $blogTranslate->seo_keyword;
                            $blog->seo_tag = $blogTranslate->seo_tag;
                            $blog->seo_description = $blogTranslate->seo_description;
                        }

                        if(str_word_count($blog->description)>62){
                            $blog->description = substr($blog->description,0,420);
                            $blog->description = $blog->description.".....";
                        } 

                        $blog->trimed_description = strip_tags($blog->description);
                        $blog->trimed_description =  str_replace("&nbsp;",'',$blog->trimed_description); 
                        $blog->trimed_description =  str_replace("&#39;","'",$blog->trimed_description);
                        $blog->created_at = date("d M Y h:i a",strtotime($blog->created_at)) ;
                        if($blog->thumb_image!=''){
                            $blog->thumb_image= url('upload/blog/thumb/360/'.$blog->thumb_image);
                        }else{
                            $blog->thumb_image= url('upload/blog/thumb/default.png');
                        }
                        $check_image = BlogImages::where('blog_id',$blog->id)->pluck('image');  
                        $blog_image = array();
                        if(count($check_image)){
                            foreach ($check_image as $value) {
                                $value = url('upload/blog/banner/800/'.$value);
                                array_push($blog_image,$value);
                            }
                            $blog->banner_image = $blog_image;
                        }else{
                            $blog_image[0] = url('upload/author/default.png');
                            $blog->banner_image =  $blog_image;
                        }  
                        if($header!=null){
                        $vote = Vote::where('user_id',$header)->where('blog_id',$blog->id)->first();
                        if($vote){
                            $blog->is_vote = 1;
                        }else{
                            $blog->is_vote = 0;
                        }   
                        $bookmarked = BookMarkPost::where('user_id',$header)->where('blog_id',$blog->id)->first();
                        if($bookmarked){
                            $blog->is_bookmark = 1;
                        }else{
                            $blog->is_bookmark = 0;
                        }                      
                    }else{
                        $blog->is_vote = 0;
                        $blog->is_bookmark = 0;
                    }
                    $total_votes = Vote::where('blog_id',$blog->id)->count();
                    $yes_votes = Vote::where('blog_id',$blog->id)->where('vote',1)->count();
                    $no_votes = Vote::where('blog_id',$blog->id)->where('vote',0)->count();
                    $blog->view_count = BlogViewCount::where('blog_id',$blog->id)->count();
                    if($yes_votes!=0){
                        $yes_percent = ($yes_votes/$total_votes)*100;
                    }else{
                        $yes_percent = 0;
                    }
                    if($no_votes!=0){
                        $no_percent = ($no_votes/$total_votes)*100;
                    }else{
                        $no_percent = 0;
                    } 
                    $blog->yes_percent = round($yes_percent);
                    $blog->no_percent = round($no_percent);  
                        $author = Author::where('id',$blog->author_id)->first();
                        if($author){                    
                            $blog->author_name = $author->name;
                            if($author->image!=null || $author->image!=''){
                                $blog->image = url('upload/author/original/'.$author->image);
                            }else{
                                $blog->image = url('upload/author/default.png');
                            }
                        }else{
                            $blog->author_name = "";
                            $blog->image = url('upload/author/default.png');
                        }
                        $category = Category::where('id',$blog->category_id)->first();
                        if($category){ 


                            $catTranslate = CategoryTranslation::where('category_id',$category->id)->where('language_code',$this->language)->first();
                            if ($catTranslate) {
                                $category->name = $catTranslate->name;
                            }

                            $blog->category_name = $category->name;
                            $blog->color = $category->color;
                        }else{
                            $blog->category_name = "";
                            $blog->color = "";
                        }
                        $blog->time = $blog->time. " min";
                        $blog->create_date = date("d M Y // h:i a",strtotime($blog->schedule_date));

                        if ($flag) {
                            return response(\Helpers::sendSuccessAjaxResponse(__('message_alerts.record_found'),$blog));
                        }
                        return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.blog_not_found')));
                }else{
                    return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.record_not_found')));
                }
            }
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 401);
        }
    }
    /**
     * Show Next and Previous Blog.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function nextPreviousBlog(Request $request)
    {
        try {
            $post = $request->all();
            $validate = [
                'blog_id' => 'required|min:1',
            ];
            $validator = Validator::make($post, $validate);
            if ($validator->fails()) {
                $data['error'] = $validator->errors();
                return response(\Helpers::sendFailureAjaxResponse($data['error']));
            }else{
                $header = $request->header('userData');
                $blog_image = array();
                $blog_image = array();
                if($post['type']=='next'){
                    $blog = Blog::where('id', '>',$post['blog_id'])->where('schedule_date',"<=",date("Y-m-d H:i:s"))->where('deleted_at',null)->limit(1)->get();
                }else{
                    $blog = Blog::where('id', '<',$post['blog_id'])->where('schedule_date',"<=",date("Y-m-d H:i:s"))->where('deleted_at',null)->orderBy('schedule_date','desc')->limit(1)->get();
                }
                if($blog){
                    foreach($blog as $row){
                        $flag = false;
                        $blogTranslate = BlogTranslation::where('blog_id',$row->id)->where('language_code',$this->language)->first();

                        if ($blogTranslate) {

                            $flag = true;

                            $row->title = $blogTranslate->title;
                            $row->tags = $blogTranslate->tags;
                            $row->description = $blogTranslate->description;
                            $row->seo_title = $blogTranslate->seo_title;
                            $row->seo_keyword = $blogTranslate->seo_keyword;
                            $row->seo_tag = $blogTranslate->seo_tag;
                            $row->seo_description = $blogTranslate->seo_description;
                        }

                        if(str_word_count($row->description)>62){
                            $row->description = substr($row->description,0,420);
                            $row->description = $row->description.".....";
                        } 

                        $row->trimed_description = strip_tags($row->description);
                        $row->trimed_description =  str_replace("&nbsp;",'',$row->trimed_description); 
                        $row->trimed_description =  str_replace("&#39;","'",$row->trimed_description);
                        $row->created_at = date("d M Y h:i a",strtotime($row->created_at));
                        if($row->thumb_image!=''){
                            $row->thumb_image= url('upload/blog/thumb/360/'.$row->thumb_image);
                        }else{
                            $row->thumb_image= url('upload/blog/thumb/default.png');
                        }
                        $check_image = BlogImages::where('blog_id',$row->id)->pluck('image');  
                        $blog_image = array();
                        if(count($check_image)){
                            foreach ($check_image as $value) {
                                $value = url('upload/blog/banner/800/'.$value);
                                array_push($blog_image,$value);
                            }
                            $row->banner_image = $blog_image;
                        }else{
                            $blog_image[0] = url('upload/author/default.png');
                            $row->banner_image =  $blog_image;
                        }  
                        if($header!=null){
                            $vote = Vote::where('user_id',$header)->where('blog_id',$row->id)->first();
                            if($vote){
                                $row->is_vote = 1;
                            }else{
                                $row->is_vote = 0;
                            }   
                            $bookmarked = BookMarkPost::where('user_id',$header)->where('blog_id',$row->id)->first();
                            if($bookmarked){
                                $row->is_bookmark = 1;
                            }else{
                                $row->is_bookmark = 0;
                            }                      
                        }else{
                            $row->is_vote = 0;
                            $row->is_bookmark = 0;
                        }
                        $total_votes = Vote::where('blog_id',$row->id)->count();
                        $yes_votes = Vote::where('blog_id',$row->id)->where('vote',1)->count();
                        $no_votes = Vote::where('blog_id',$row->id)->where('vote',0)->count();
                        $row->view_count = BlogViewCount::where('blog_id',$row->id)->count();
                        if($yes_votes!=0){
                            $yes_percent = ($yes_votes/$total_votes)*100;
                        }else{
                            $yes_percent = 0;
                        }
                        if($no_votes!=0){
                            $no_percent = ($no_votes/$total_votes)*100;
                        }else{
                            $no_percent = 0;
                        }                 
                        $row->yes_percent = round($yes_percent);
                        $row->no_percent = round($no_percent); 
                        $author = Author::where('id',$row->author_id)->first();
                        if($author){                    
                            $row->author_name = $author->name;
                            if($author->image!=null || $author->image!=''){
                                $row->image = url('upload/author/original/'.$author->image);
                            }else{
                                $row->image = url('upload/author/default.png');
                            }
                        }else{
                            $row->author_name = "";
                            $row->image = url('upload/author/default.png');
                        }
                        $category = Category::where('id',$row->category_id)->first();
                        if($category){  

                            $catTranslate = CategoryTranslation::where('category_id',$category->id)->where('language_code',$this->language)->first();
                            if ($catTranslate) {
                                $category->name = $catTranslate->name;
                            }

                            $row->category_name = $category->name;
                            $row->color = $category->color;
                        }else{
                            $row->category_name = "";
                            $row->color = "";
                        }
                        $row->time = $row->time. " min";
                        $row->create_date = date("d M Y // h:i a",strtotime($row->schedule_date));

                        if ($flag) {
                            array_push($final_blog,$row);                     
                        }

                    }                        
                    return response(\Helpers::sendSuccessAjaxResponse(ucfirst($post['type']).' '.__('message_alerts.record_found'),$final_blog));
                }else{
                    return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.record_not_found')));
                }
            }
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 401);
        }
    }

    /**
     * Show Blog votes.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getSingleData(Request $request)
    {
        try {
            $post = $request->all();
            $validate = [
                'user_id' => 'required',
            ];
            $validator = Validator::make($post, $validate);
            if ($validator->fails()) {
                $data['error'] = $validator->errors();
                return response(\Helpers::sendFailureAjaxResponse($data['error']));
            }else{
                $blogs = array();
                $header = $request->header('userData');
                if (isset($post['category_id']) && $post['category_id'] != '' && $post['category_id'] !=0) {
                    $blog = Blog::where('category_id',$post['category_id'])->where('schedule_date',"<=",date("Y-m-d H:i:s"))->first();
                    if($blog){
                        $flag = false;
                        $blogTranslate = BlogTranslation::where('blog_id',$blog->id)->where('language_code',$this->language)->first();
                        if ($blogTranslate) {
                            $flag = true;
                            $blog->title = $blogTranslate->title;
                            $blog->tags = $blogTranslate->tags;
                            $blog->description = $blogTranslate->description;
                            $blog->seo_title = $blogTranslate->seo_title;
                            $blog->seo_keyword = $blogTranslate->seo_keyword;
                            $blog->seo_tag = $blogTranslate->seo_tag;
                            $blog->seo_description = $blogTranslate->seo_description;
                        }

                        if(str_word_count($blog->description)>62){
                            $blog->description = substr($blog->description,0,420);
                            $blog->description = $blog->description.".....";
                        } 
                        $blog->trimed_description = strip_tags($blog->description);
                        $blog->trimed_description =  str_replace("&nbsp;",'',$blog->trimed_description); 
                        $blog->trimed_description =  str_replace("&#39;","'",$blog->trimed_description);
                        $blog->created_at = date("d M Y h:i a",strtotime($blog->created_at));
                        if($blog->thumb_image!=''){
                            $blog->thumb_image= url('upload/blog/thumb/360/'.$blog->thumb_image);
                        }else{
                            $blog->thumb_image= url('upload/blog/thumb/default.png');
                        }  
                        $check_image = BlogImages::where('blog_id',$blog->id)->pluck('image');  
                        $blog_image = array();
                        if(count($check_image)){
                            foreach ($check_image as $value) {
                                $value = url('upload/blog/banner/800/'.$value);
                                array_push($blog_image,$value);
                            }
                            $blog->banner_image = $blog_image;
                        }else{
                            $blog_image[0] = url('upload/author/default.png');
                            $blog->banner_image =  $blog_image;
                        } 
                        if($header!=null){
                            $vote = Vote::where('user_id',$header)->where('blog_id',$blog->id)->first();
                            if($vote){
                                $blog->is_vote = 1;
                            }else{
                                $blog->is_vote = 0;
                            }
                            $bookmarked = BookMarkPost::where('user_id',$header)->where('blog_id',$blog->id)->first();
                            if($bookmarked){
                                $blog->is_bookmark = 1;
                            }else{
                                $blog->is_bookmark = 0;
                            }                         
                        }else{
                            $blog->is_vote = 0;
                            $blog->is_bookmark = 0;
                        }
                        $total_votes = Vote::where('blog_id',$blog->id)->count();
                        $yes_votes = Vote::where('blog_id',$blog->id)->where('vote',1)->count();
                        $no_votes = Vote::where('blog_id',$blog->id)->where('vote',0)->count();
                        $blog->view_count = BlogViewCount::where('blog_id',$blog->id)->count();
                        if($yes_votes!=0){
                            $yes_percent = ($yes_votes/$total_votes)*100;
                        }else{
                            $yes_percent = 0;
                        }
                        if($no_votes!=0){
                            $no_percent = ($no_votes/$total_votes)*100;
                        }else{
                            $no_percent = 0;
                        }                 
                        $blog->yes_percent = round($yes_percent);
                        $blog->no_percent = round($no_percent); 
                        $author = Author::where('id',$blog->author_id)->first();
                        if($author){                    
                            $blog->author_name = $author->name;
                            if($author->image!=null || $author->image!=''){
                                $blog->image = url('upload/author/original/'.$author->image);
                            }else{
                                $blog->image = url('upload/author/default.png');
                            }
                        }else{
                            $blog->author_name = "";
                            $blog->image = url('upload/author/default.png');
                        }
                        $category = Category::where('id',$blog->category_id)->first();
                        if($category){   
                            $catTranslate = CategoryTranslation::where('category_id',$category->id)->where('language_code',$this->language)->first();
                            if ($catTranslate) {
                                $category->name = $catTranslate->name;
                            }               
                            $blog->category_name = $category->name;
                            $blog->color = $category->color;
                        }else{
                            $blog->category_name = "";
                            $blog->color = "";
                        }
                        $blog->time = $blog->time. " min";
                        $blog->create_date = date("d M Y // h:i a",strtotime($blog->schedule_date));
                    }
                }else{
                    
                    $blog = Blog::where('status',1)->where('schedule_date',"<=",date("Y-m-d H:i:s"))->orderBy('schedule_date','DESC')->first();
                    if($blog){

                            $flag = false;


                        $blogTranslate = BlogTranslation::where('blog_id',$blog->id)->where('language_code',$this->language)->first();
                        if ($blogTranslate) {
                            $flag = true;

                            $blog->title = $blogTranslate->title;
                            $blog->tags = $blogTranslate->tags;
                            $blog->description = $blogTranslate->description;
                            $blog->seo_title = $blogTranslate->seo_title;
                            $blog->seo_keyword = $blogTranslate->seo_keyword;
                            $blog->seo_tag = $blogTranslate->seo_tag;
                            $blog->seo_description = $blogTranslate->seo_description;
                        }

                        if(str_word_count($blog->description)>62){
                            $blog->description = substr($blog->description,0,420);
                            $blog->description = $blog->description.".....";
                        } 
                        $blog->trimed_description = strip_tags($blog->description);
                        $blog->trimed_description =  str_replace("&nbsp;",'',$blog->trimed_description); 
                        $blog->trimed_description =  str_replace("&#39;","'",$blog->trimed_description);
                        $blog->created_at = date("d M Y h:i a",strtotime($blog->created_at));
                        if($blog->thumb_image!=''){
                            $blog->thumb_image= url('upload/blog/thumb/360/'.$blog->thumb_image);
                        }else{
                            $blog->thumb_image= url('upload/blog/thumb/default.png');
                        } 
                        $check_image = BlogImages::where('blog_id',$blog->id)->pluck('image');  
                        $blog_image = array();
                        if(count($check_image)){
                            foreach ($check_image as $value) {
                                $value = url('upload/blog/banner/800/'.$value);
                                array_push($blog_image,$value);
                            }
                            $blog->banner_image = $blog_image;
                        }else{
                            $blog_image[0] = url('upload/author/default.png');
                            $blog->banner_image =  $blog_image;
                        } 
                        if($header!=null){
                            $vote = Vote::where('user_id',$header)->where('blog_id',$blog->id)->first();
                            if($vote){
                                $blog->is_vote = 1;
                            }else{
                                $blog->is_vote = 0;
                            } 
                            $bookmarked = BookMarkPost::where('user_id',$header)->where('blog_id',$blog->id)->first();
                            if($bookmarked){
                                $blog->is_bookmark = 1;
                            }else{
                                $blog->is_bookmark = 0;
                            }                        
                        }else{
                            $blog->is_vote = 0;
                            $blog->is_bookmark = 0;
                        }
                        $total_votes = Vote::where('blog_id',$blog->id)->count();
                        $yes_votes = Vote::where('blog_id',$blog->id)->where('vote',1)->count();
                        $no_votes = Vote::where('blog_id',$blog->id)->where('vote',0)->count();
                        $blog->view_count = BlogViewCount::where('blog_id',$blog->id)->count();
                        if($yes_votes!=0){
                            $yes_percent = ($yes_votes/$total_votes)*100;
                        }else{
                            $yes_percent = 0;
                        }
                        if($no_votes!=0){
                            $no_percent = ($no_votes/$total_votes)*100;
                        }else{
                            $no_percent = 0;
                        }                 
                        $blog->yes_percent = round($yes_percent);
                        $blog->no_percent = round($no_percent);  
                        $author = Author::where('id',$blog->author_id)->first();
                        if($author){                    
                            $blog->author_name = $author->name;
                            if($author->image!=null || $author->image!=''){
                                $blog->image = url('upload/author/original/'.$author->image);
                            }else{
                                $blog->image = url('upload/author/default.png');
                            }
                        }else{
                            $blog->author_name = "";
                            $blog->image = url('upload/author/default.png');
                        }
                        $category = Category::where('id',$blog->category_id)->first();
                        if($category){    

                            $catTranslate = CategoryTranslation::where('category_id',$category->id)->where('language_code',$this->language)->first();
                            if ($catTranslate) {
                                $category->name = $catTranslate->name;
                            }

                            $blog->category_name = $category->name;
                            $blog->color = $category->color;
                        }else{
                            $blog->category_name = "";
                            $blog->color = "";
                        }
                        $blog->time = $blog->time. " min";
                        $blog->create_date = date("d M Y // h:i a",strtotime($blog->schedule_date));
                    }
                }

                if ($flag) {
                    return $this->sendResponse($blog,__('message_alerts.record_found'));
                }else{
                    return $this->sendError(__('message_alerts.blog_not_found'), 401);
                }

            }
        } catch (\Exception $ex) {
           return $this->sendError($ex->getMessage(), 401);
        }
    }
}