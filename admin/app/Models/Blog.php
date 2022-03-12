<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Auth;
use App\Models\BlogTranslation;
use App\Models\CategoryTranslation;
use App\Models\Category;

class Blog extends Model
{
    protected $table = "blog";
    protected $dates = ['deleted_at'];

    public function category(){
        return $this->hasOne('App\Models\Category',"id","category_id")->where('status',1);
    }
    
    public function image(){
        return $this->hasOne('App\Models\BlogImages',"blog_id","id");
    }
    
    public function author(){
        return $this->hasOne('App\Models\Author',"id","author_id")->where('status',1);
    }


    /**
     * Upadte Blog
     * @param Array of post data
     * @return template_id
    */
    public static function updateBlog($data) {
        try {
            $template = new self;
            $id=0;              
            if($id = $template->where('id', $data['id'])->update($data)) {
                return ['status' => true, 'message' => "Blog updated sucessfully", 'id' =>$id];
            } else {
                return ['status' => false, 'message' => "Error in updating blog" ];
            }
        }catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }

    /**
     * Get All Blog
     * @param Search data
     * @return array 
    */
    public static function getAllBlog($search = ''){
        try {
            $contact = new self;
            $pagination_no = 30;
            if(isset($search['per_page']) && !empty($search['per_page'])){
                $pagination_no = $search['per_page'];
            }
            if(isset($search['post']) && $search['post'] != ''){
                if($search['post']=='publish'){
                    $contact = $contact->where('status',1);
                }else if($search['post']=='unpublish'){
                    $contact = $contact->where('status',0);
                }else if($search['post']=='draft'){
                    $contact = $contact->where('status',2);
                }
            }

            if(isset($search['name']) && $search['name'] != ''){
              $keyword = $search['name'];
              $contact = $contact->where(function($q) use ($keyword){
                    $q->where(DB::raw('LOWER(title)'), 'like', '%'.strtolower($keyword). '%');
                });
            }

            $data = $contact->orderBy('id','DESC')->with('category')->with('image')->paginate($pagination_no)->appends('per_page', $pagination_no);
            foreach($data as $likes){
                $likes->likes = Vote::where('blog_id',$likes->id)->get();
                $likes->viewcount = BlogViewCount::where('blog_id',$likes->id)->count();
                $img = BlogImages::where('blog_id',$likes->id)->orderBy('id','DESC')->first();
                $user = User::where('id',$likes->created_by)->orderBy('id','DESC')->first();
                if($user){
                    if($user->type=='admin'){
                        $likes->created_by_name = "SuperAdmin";
                    }else{
                        $likes->created_by_name = $user->name;
                    }
                    
                }else{
                    $likes->created_by_name = "--";
                }
                if($img){
                    $likes->blog_image = url('upload/blog/banner/360/'.$img->image);
                }else{
                    $likes->blog_image = "";
                }
            }
            return $data;                

        }catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }

    /**
     * Get All Blog
     * @param Search data
     * @return array 
    */
    public static function getAllSliderBlog($search = ''){
        try {
            $contact = new self;
            $pagination_no = 30;  
            if(isset($search['per_page']) && !empty($search['per_page'])){
                $pagination_no = $search['per_page'];
            }
            if(isset($search['post']) && $search['post'] != ''){
                if($search['post']=='publish'){
                    $contact = $contact->where('status',1);
                }else if($search['post']=='unpublish'){
                    $contact = $contact->where('status',0);
                }else if($search['post']=='draft'){
                    $contact = $contact->where('status',2);
                }
            }

            if(isset($search['name']) && $search['name'] != ''){
              $keyword = $search['name'];
              $contact = $contact->where(function($q) use ($keyword){
                    $q->where(DB::raw('LOWER(title)'), 'like', '%'.strtolower($keyword). '%');
                });
            }

            $data = $contact->where('is_slider',1)->orderBy('order','ASC')->with('category')->with('image')->paginate($pagination_no)->appends('per_page', $pagination_no);
            foreach($data as $likes){
                $likes->likes = Vote::where('blog_id',$likes->id)->get();
                $likes->viewcount = BlogViewCount::where('blog_id',$likes->id)->count();
                $img = BlogImages::where('blog_id',$likes->id)->orderBy('id','DESC')->first();
                $user = User::where('id',$likes->created_by)->orderBy('id','DESC')->first();
                if($user){
                    if($user->type=='admin'){
                        $likes->created_by_name = "SuperAdmin";
                    }else{
                        $likes->created_by_name = $user->name;
                    }
                    
                }else{
                    $likes->created_by_name = "--";
                }
                if($img){
                    $likes->blog_image = url('upload/blog/banner/original/'.$img->image);
                }else{
                    $likes->blog_image = "";
                }
            }
            return $data;                

        }catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }

    /**
     * Get All Blog
     * @param Search data
     * @return array 
    */
    public static function getAllActiveBlog($search = ''){
        try {
            $blog = new self;
            $pagination_no = 10;
            if(isset($search['per_page']) && !empty($search['per_page'])){
                $pagination_no = $search['per_page'];
            }
            $data = $blog->where('status',1)->orderBy('id','DESC')
                    ->paginate($pagination_no)->appends('per_page', $pagination_no);
            return $data;
        }catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }

    /**
     * Get All Blog
     * @param Search data
     * @return array 
    */
    public static function getAllActiveBlogForDash($search = ''){
        try {
            $blog = new self;
            $data = $blog->where('status',1)->orderBy('id','DESC')
                    ->get();
            return $data;
        }catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }

    /**
     * Get All Blog Details
     * @param blog id
     * @return object 
    */
    public static function getActiveBlogDetail($id){
        try {
            $blog = new self;
            $data = $blog->where('id',$id)->first();
            return $data;
        }catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }

    /**
     * Get All Blog
     * @param Search data
     * @return array 
    */
    public static function frontBlog($type,$limit){
        try {
            $catslug = (isset($_GET['category']))?($_GET['category']!='')?$_GET['category']:'':'';
            $contact = new self;
            $contact = $contact->where('status',1)->where('schedule_date',"<=",date("Y-m-d H:i:s"))->with('category')->with('image')->with('author');
            if($catslug!=''){
                $category = Category::where('status',1)->where('slug',$catslug)->first();
                if($category){
                    $contact = $contact->where('category_id',$category->id);
                }
            }
            if($type=='slider'){
                $contact = $contact->where('is_slider',1)->orderBy('order','ASC');
            }else if($type=='editor'){
                $contact = $contact->where('is_editor_picks',1)->orderBy('schedule_date','DESC');
            }else if($type=='top_of_week'){
                $contact = $contact->where('is_weekly_top_picks',1)->orderBy('schedule_date','DESC');
            }else{
                $contact = $contact->orderBy('schedule_date','DESC');
            }
            $data = $contact->limit($limit)->get();
            $language = \Helpers::returnUserLangCode();
            $finalResponse = array();
            foreach($data as $likes){
                $likes->post_show = false;
                $blogTranslate = BlogTranslation::where('blog_id',$likes->id)->where('language_code',$language)->first();
                if ($blogTranslate) {
                    $likes->post_show = true;
                    $likes->title = $blogTranslate->title;
                    $likes->tags = $blogTranslate->tags;
                    $likes->description = $blogTranslate->description;
                    $likes->seo_title = $blogTranslate->seo_title;
                    $likes->seo_keyword = $blogTranslate->seo_keyword;
                    $likes->seo_tag = $blogTranslate->seo_tag;
                    $likes->seo_description = $blogTranslate->seo_description;
                }

                if ($likes->category) {
                    $catTranslate = CategoryTranslation::where('category_id',$likes->category->id)->where('language_code',$language)->first();
                    if ($catTranslate) {
                        $likes->category->name = $catTranslate->name;
                    }
                }

                $likes->likes = Vote::where('blog_id',$likes->id)->get();
                $likes->viewcount = BlogViewCount::where('blog_id',$likes->id)->count();
                $img = BlogImages::where('blog_id',$likes->id)->orderBy('id','DESC')->first();
                $user = User::where('id',$likes->created_by)->first();
                if($user){
                    if($user->type=='admin'){
                        $likes->created_by_name = "SuperAdmin";
                    }else{
                        $likes->created_by_name = $user->name;
                    }
                    
                }else{
                    $likes->created_by_name = "--";
                }
                if($img){
                    $likes->blog_image = $img->image;
                }
                if($likes->post_show){
                    array_push($finalResponse, $likes);
                }
            }
            return $finalResponse;               

        }catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }

    /**
     * Get All Blog
     * @param Search data
     * @return array 
    */
    public static function getBlogDetail($slug){
        try {
            $contact = new self;
             $flag = true;
            $data = $contact->where('status',1)->where('schedule_date',"<=",date("Y-m-d H:i:s"))->where('slug',$slug)->with('category')->with('image')->with('author')->first();
            if($data){


                              
                $data->likes = Vote::where('blog_id',$data->id)->get();
                $data->viewcount = BlogViewCount::where('blog_id',$data->id)->count();
                if(Auth::user()){
                    $bookmark = BookMarkPost::where('blog_id',$data->id)->where('user_id',Auth::user()->id)->first();

                    if($bookmark){
                        $data->isBookmarked = 1;
                    }else{
                        $data->isBookmarked = 0;
                    }
                }else{
                    $data->isBookmarked = 0;
                }                
                $img = BlogImages::where('blog_id',$data->id)->orderBy('id','DESC')->first();
                $data->blog_image = '';
                if($img){
                    $data->blog_image = $img->image;
                }
                $language = \Helpers::returnUserLangCode();

                $blogTranslate = BlogTranslation::where('blog_id',$data->id)->where('language_code',$language)->first();
                if ($blogTranslate) {
                    $data->title = $blogTranslate->title;
                    $data->tags = $blogTranslate->tags;
                    $data->description = $blogTranslate->description;
                    $data->seo_title = $blogTranslate->seo_title;
                    $data->seo_keyword = $blogTranslate->seo_keyword;
                    $data->seo_tag = $blogTranslate->seo_tag;
                    $data->seo_description = $blogTranslate->seo_description;
                }else{
                     $flag = false;
                }
                if ($data->category) {
                    $catTranslate = CategoryTranslation::where('category_id',$data->category->id)->where('language_code',$language)->first();
                    if ($catTranslate) {
                        $data->category->name = $catTranslate->name;
                    }
                }

                if($data->tags!=''){
                    $data->tags = explode(",", $data->tags);
                }  
            }

            if ($flag) {
                return $data; 
            }else{
                return $flag;   
            }

                        
        }catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }

    public static function getSingleBlog($id){
        try {
            $contact = new self;
            $data = $contact->where('id',$id)->with('category')->with('image')->with('author')->first();
            if($data){
                $language = \Helpers::returnUserLangCode();
                $blogTranslate = BlogTranslation::where('blog_id',$data->id)->where('language_code',$language)->first();
                if ($blogTranslate) {
                    $data->title = $blogTranslate->title;
                    $data->tags = $blogTranslate->tags;
                    $data->description = $blogTranslate->description;
                    $data->seo_title = $blogTranslate->seo_title;
                    $data->seo_keyword = $blogTranslate->seo_keyword;
                    $data->seo_tag = $blogTranslate->seo_tag;
                    $data->seo_description = $blogTranslate->seo_description;
                }
                if ($data->category) {
                    $catTranslate = CategoryTranslation::where('category_id',$data->category->id)->where('language_code',$language)->first();
                    if ($catTranslate) {
                        $data->category->name = $catTranslate->name;
                    }
                }

                if($data->tags!=''){
                    $data->tags = explode(",", $data->tags);
                }                
                $data->likes = Vote::where('blog_id',$data->id)->get();
                $data->viewcount = BlogViewCount::where('blog_id',$data->id)->count();
                $img = BlogImages::where('blog_id',$data->id)->orderBy('id','DESC')->first();
                if($img){
                    $data->blog_image = $img->image;
                }
            }
            return $data;               

        }catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }

    /**
     * Get All Blog
     * @param Search data
     * @return array 
    */
    public static function getRelatedBlog($blogid='',$category_id='',$limit=4){
        try {
            $contact = new self;
            if(isset($blogid) && $blogid != ''){
                $contact = $contact->where('id','!=',$blogid);
            }

            if(isset($category_id) && $category_id != ''){
                $contact = $contact->where('category_id',$category_id);
            }

            if(isset($limit) && $limit != ''){
                $contact = $contact->limit($limit);
            }
            $data = $contact->where('status',1)->where('schedule_date',"<=",date("Y-m-d H:i:s"))->orderBy('id','DESC')->with('category')->with('image')->with('author')->get();
            foreach($data as $likes){
                $likes->post_show = false;
                $language = \Helpers::returnUserLangCode();
                $blogTranslate = BlogTranslation::where('blog_id',$likes->id)->where('language_code',$language)->first();
                if ($blogTranslate) {
                    $likes->post_show = true;
                    $likes->title = $blogTranslate->title;
                    $likes->tags = $blogTranslate->tags;
                    $likes->description = $blogTranslate->description;
                    $likes->seo_title = $blogTranslate->seo_title;
                    $likes->seo_keyword = $blogTranslate->seo_keyword;
                    $likes->seo_tag = $blogTranslate->seo_tag;
                    $likes->seo_description = $blogTranslate->seo_description;
                }
                if ($likes->category) {
                    $catTranslate = CategoryTranslation::where('category_id',$likes->category->id)->where('language_code',$language)->first();
                    if ($catTranslate) {
                        $likes->category->name = $catTranslate->name;
                    }
                }

                $likes->likes = Vote::where('blog_id',$likes->id)->get();
                $likes->viewcount = BlogViewCount::where('blog_id',$likes->id)->count();
                $img = BlogImages::where('blog_id',$likes->id)->orderBy('id','DESC')->first();
                $user = User::where('id',$likes->created_by)->orderBy('id','DESC')->first();
                if($user){
                    if($user->type=='admin'){
                        $likes->created_by_name = "SuperAdmin";
                    }else{
                        $likes->created_by_name = $user->name;
                    }
                    
                }else{
                    $likes->created_by_name = "--";
                }
                $likes->blog_image = '';
                if($img){
                    $likes->blog_image = $img->image;
                }
            }
            return $data;               

        }catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }
}
