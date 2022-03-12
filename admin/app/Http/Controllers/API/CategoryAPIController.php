<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Blog;
use App\Models\Category;
use App\Models\Author;
use App\Models\SiteContent;
use App\Models\SearchLog;
use App\Models\BookMarkPost;
use App\Models\BlogViewCount;
use App\Models\BlogImages;
use App\Models\Vote;
use App\Models\CategoryTranslation;
use App\Models\BlogTranslation;
use App\Models\User;
class CategoryAPIController extends Controller
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
     * Show Category view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function list(Request $request)
    {
        try {
            $category = Category::where('status',1)->orderBy('order','ASC')->get();
            $final_arr = array();
            $i=0;
            $header = $request->header('userData');
            foreach($category as $row){

                $catTranslate = CategoryTranslation::where('category_id',$row->id)->where('language_code',$this->language)->first();
                if ($catTranslate) {
                    $row->name = $catTranslate->name;
                }

                if($row->image!=''){
                    $row->image= url('upload/category/original/'.$row->image);
                }else{
                    $row->image= url('upload/category/default.png');
                }
                $row->blog = array();
                $blog = Blog::where('status',1)->where('schedule_date',"<=",date("Y-m-d H:i:s"))->where('category_id',$row->id)->get();
                if($blog){
                    foreach ($blog as $detail) {

                        $blogTranslate = BlogTranslation::where('blog_id',$detail->id)->where('language_code',$this->language)->first();
                        if ($blogTranslate) {
                            $detail->title = $blogTranslate->title;
                            $detail->tags = $blogTranslate->tags;
                            $detail->description = $blogTranslate->description;
                            $detail->seo_title = $blogTranslate->seo_title;
                            $detail->seo_keyword = $blogTranslate->seo_keyword;
                            $detail->seo_tag = $blogTranslate->seo_tag;
                            $detail->seo_description = $blogTranslate->seo_description;
                        }

                        if(str_word_count($detail->description)>62){
                            $detail->description = substr($detail->description,0,420);
                            $detail->description = $detail->description.".....";
                        } 
                        $detail->trimed_description = strip_tags($detail->description);
                        $detail->trimed_description =  str_replace("&nbsp;",'',$detail->trimed_description);
                        $detail->trimed_description =  str_replace("&#39;","'",$detail->trimed_description);
                        $detail->created_at = date("d M Y h:i a",strtotime($detail->created_at));
                        if($detail->thumb_image!=''){
                            $detail->thumb_image= url('upload/blog/thumb/360/'.$detail->thumb_image);
                        }else{
                            $detail->thumb_image= url('upload/blog/thumb/default.png');
                        }   
                        $check_image = BlogImages::where('blog_id',$detail->id)->pluck('image');  
                        $blog_image = array();
                        if(count($check_image)){
                            foreach ($check_image as $value) {
                                $value = url('upload/blog/banner/800/'.$value);
                                array_push($blog_image,$value);
                            }
                            $detail->banner_image = $blog_image;
                        }else{
                            $blog_image[0] = url('upload/author/default.png');
                            $detail->banner_image =  $blog_image;
                        }  
                        if($header!=null){
                            $vote = Vote::where('user_id',$header)->where('blog_id',$detail->id)->first();
                            if($vote){
                                $detail->is_vote = 1;
                            }else{
                                $detail->is_vote = 0;
                            }
                            $bookmarked = BookMarkPost::where('user_id',$header)->where('blog_id',$detail->id)->first();
                            if($bookmarked){
                                $detail->is_bookmark = 1;
                            }else{
                                $detail->is_bookmark = 0;
                            }                         
                        }else{
                            $detail->is_vote = 0;
                            $detail->is_bookmark = 0;
                        }
                        $total_votes = Vote::where('blog_id',$detail->id)->count();
                        $yes_votes = Vote::where('blog_id',$detail->id)->where('vote',1)->count();
                        $no_votes = Vote::where('blog_id',$detail->id)->where('vote',0)->count();
                        $detail->view_count = BlogViewCount::where('blog_id',$detail->id)->count();
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
                        $detail->yes_percent = round($yes_percent);
                        $detail->no_percent = round($no_percent);   
                        $author = Author::where('status',1)->where('id',$detail->author_id)->first();
                        if($author){
                            $detail->author_name = $author->name;
                            if($author->image!=''){
                                $detail->image = url('upload/author/original/'.$author->image);
                            }else{
                                $detail->image = url('upload/author/default.png');
                            }
                        }else{
                            $detail->author_name = "";
                            $detail->image = url('upload/author/default.png');
                        }
                        $detail->category_name = $row->name;
                        $detail->color = $row->color;
                        $detail->time = $detail->time. " min";
                        $detail->create_date = date("d M Y // h:i a",strtotime($detail->schedule_date));
                    }
                    $row->blog = $blog;
                    if(count($blog)>0){
                        $row->index = $i;
                        array_push($final_arr,$row);
                        $i++; 
                    }  
                }                
            }
            return $this->sendResponse($final_arr, __('message_alerts.category_list'));
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 401);
        }
    }
}