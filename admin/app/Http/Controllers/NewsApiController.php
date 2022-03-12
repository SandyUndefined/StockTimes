<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RssFeed;
use App\Models\Category;
use App\Models\Blog;
use App\Models\BlogImages;
use Illuminate\Support\Facades\Validator;
use Auth;
use UploadImage as Image;

class NewsApiController extends Controller
{
    
	function __construct(){
         $this->middleware('permission:news-api-post-list|news-api-post-save', ['only' => ['index','saveNewsApiPost']]);
         $this->middleware('permission:news-api-post-list', ['only' => ['index']]);
         $this->middleware('permission:news-api-post-save', ['only' => ['saveNewsApiPost']]);
    }


    /**
     * Show Category view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request,$layout = 'side-menu', $theme = 'light')
    {
		$sources = [];
		$sources_url ='https://newsapi.org/v2/sources';
		$sourcesfields = array(
			'apiKey'=>setting('news_api_key'),
		);
		$sources_url = $sources_url. '/?' . http_build_query($sourcesfields);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_URL,$sources_url);
		$sources_result=curl_exec($ch);
		curl_close($ch);
		$sources_result = json_decode($sources_result, true);
		if ($sources_result['status'] == 'ok') {
			$sources = $sources_result['sources'];
		}
		
    	$data = [];
    	if(isset($_GET['q']) && $_GET['q']!=''){
	    	$url ='https://newsapi.org/v2/everything';
	        $fields = array(
			    'q' => $_GET['q'],
			    'from' => date('Y-m-d'),
			    'sortBy'=>'publishedAt',
			    'apiKey'=>setting('news_api_key'),
			);

			if(isset($_GET['sources']) && $_GET['sources'] != ''){
				$fields['sources'] = $_GET['sources'];
			}

			if(isset($_GET['domains']) && $_GET['domains'] != ''){
				$fields['domains'] = $_GET['domains'];
			}

			if(isset($_GET['language']) && $_GET['language'] != ''){
				$fields['language'] = $_GET['language'];
			}

			if(isset($_GET['from']) && $_GET['from'] != ''){
				$fields['from'] = $_GET['from'];
			}

			if(isset($_GET['to']) && $_GET['to'] != ''){
				$fields['to'] = $_GET['to'];
			}

	        $url = $url. '/?' . http_build_query($fields);
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_URL,$url);
			$result=curl_exec($ch);
			curl_close($ch);
			$result = json_decode($result, true);
			if ($result['status'] == 'ok') {
				$data = $result['articles'];
			}
    	}
		// echo json_encode($data);exit;
		$news_api_language = config('constant.news_api_language');
        return view('super-admin/news-api.index', [
            'theme' => $theme,
            'page_name' => 'index',
            'side_menu' => array(),
            'layout' => $layout,
            'data' => $data,
            'sources' => $sources,
            'news_api_language' => $news_api_language,
            'breadcrumb'=>'<a href="'.url('/').'" class="breadcrumb">'.trans("admin.dashboard").'</a><i data-feather="chevron-right" class="breadcrumb__icon"></i><a href="'.url('/rss-feed-src/side-menu/light').'" class="breadcrumb--active">'.trans("admin.news_api").'</a>'

        ]);
    }

	/**
     * Method to save news api post
     * @param array $request
    */
    public function saveNewsApiPost(Request $request){
        try {
        	$post = $request->all();
        	
        	$post['urlToImage'] = strtok($post['urlToImage'], '?');
        // echo json_encode($post);exit;
        	$slug = \Helpers::createSlug($post['title'],'blog',0,false);
        	$category = Category::where('status',1)->first();
        	$inject = array(
				'slug'=>$slug,
				'title'=>$post['title'],
				'short_description'=>$post['description'],
				'url'=>$post['url'],
				'description'=>$post['content'],
				'created_by'=>Auth::User()->id,
				'category_id'=>$category->id,
				'content_type'=>'text',
				'status'=>2,
                'blog_accent_code'=>setting('blog_accent_code'),
				'created_at'=>date('Y-m-d H:i:s')
			);
        	$blog_id = Blog::insertGetId($inject);
        	
    		if($post['urlToImage']!=''){
    			$file = $post['urlToImage'];
                $ext = \Helpers::get_file_extension($file);
                $name = time() . rand() .'.'.$ext;
                $destination =  public_path('/upload/blog/banner/original/').$name;
                $basePath = public_path('/upload/blog/banner/');
                $info = getimagesize($file);
                if ($info['mime'] == 'image/jpeg') $image = imagecreatefromjpeg($file);
                elseif ($info['mime'] == 'image/gif') $image = imagecreatefromgif($file);
                elseif ($info['mime'] == 'image/png') $image = imagecreatefrompng($file);
                $c = imagejpeg($image, $destination, 30);
                // $destinationPath_360 = $basePath.'360/'.$name;
                $img = Image::make($destination);
    
                $img->resize(800, null, function ($constraint) {
                    $constraint->aspectRatio();
                })->save($basePath.'800/'.$name);
    
                $img->resize(360, null, function ($constraint) {
                    $constraint->aspectRatio();
                })->save($basePath.'360/'.$name);
                // return $c;
                // $c = \Helpers::compress_image($file,$destination,30,$name,$basePath,true);
            	$image_id = BlogImages::insertGetId(array('blog_id'=>$blog_id,'image'=>$name,'created_at'=>date('Y-m-d H:i:s'))); 
            }
            
            return redirect()->back()->with('success',__('message_alerts.success'));
        } catch (\Exception $e) {
             return redirect()->back()->with('success',__('message_alerts.success'));
            // return redirect()->back()->with('error',__('message_alerts.there_is_an_error'));
            // return $this->sendError($e->getMessage(), 401);
        }

    }

	
}
