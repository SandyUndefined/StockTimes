<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CmsPages;
use Illuminate\Support\Facades\Validator;
use App\Models\SiteContent;
use App\Models\Category;
use App\Models\Social;
use App\Models\Blog;
use App\Models\BlogImages;
use App\Models\User;
use App\Models\BlogViewCount;
use Illuminate\Http\Request;
use anlutro\LaravelSettings\Facade as Setting;
use Illuminate\Support\Facades\Session;
use Auth;
use App\Models\Languages;
use App\Models\Epaper;
use App\Models\EpaperTranslation;
use App\Models\LiveNewsTranslation;
use App\Models\LiveNews;
use App\Models\CategoryTranslation;
use App\Models\BlogTranslation;
use App\Models\CmsPagesTranslation;

class SiteController extends VoiceRSS 
{

    public function cms($page)
    {        
        $data = array();
        $content = CmsPages::where('page_name',$page)->first();
        if($content){
            $language = \Helpers::returnUserLangCode();
            $page = CmsPagesTranslation::where('cms_id',$content->id)->where('language_code',$language)->first();
            if ($page) {
                $content->title = $page->title;
                $content->page_title = $page->page_title;
                $content->description = $page->description;
                $content->meta_char = $page->meta_char;
                $content->meta_desc = $page->meta_desc;
            }

            if($content->image){
                if(file_exists(public_path().'/upload/cms/original/'.$content->image)){
                    $content->image = url('upload/cms/original/'.$content->image);
                }else{
                    $content->image = url('site/img/1920x982.png');
                }                   
            }else{
                $content->image = url('site/img/1920x982.png');
            }
            $category = Category::getCategoryOnFilter(5,'is_featured');    
            $side_category = Category::getCategoryOnFilter(3);
            $social = Social::get();
            $recent_blog = Blog::frontBlog('recent',2);
            $side_recent_blog = Blog::frontBlog('recent',3);
            $site_content = CmsPages::getCmsPages();
            $not_featured_category = Category::getNotFeaturedCategory();
            return view('site.cms.index', [
                'category' => $category,
                'social' => $social,
                'content' => $content,
                'site_content' => $site_content,
                'side_category' => $side_category,
                'side_recent_blog' => $side_recent_blog,
                'recent_blog' => $recent_blog,
                'data' => $data,
                'not_featured_category' => $not_featured_category,
            ]);
        }else{
            return redirect('/');
        }        
    }

}
