<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RssFeed;
use App\Models\Category;
use App\Models\Blog;
use App\Models\BlogImages;
use Illuminate\Support\Facades\Validator;
use Auth;
class RssFeedController extends Controller
{

    function __construct(){
         $this->middleware('permission:rss-feed-list|rss-feed-save-post', ['only' => ['index','saveFeed']]);
         $this->middleware('permission:rss-feed-list', ['only' => ['index']]);
         $this->middleware('permission:rss-feed-save-post', ['only' => ['saveFeed']]);
    }


    /**
     * Show Category view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request,$layout = 'side-menu', $theme = 'light')
    {
        $feed = RssFeed::getAllRssFeed($request->all());
        $category = Category::where('status',1)->get();
        return view('super-admin/rss_feed.index', [
            'theme' => $theme,
            'page_name' => 'index',
            'side_menu' => array(),
            'layout' => $layout,
            'feed' => $feed,
            'category'=>$category,
            'breadcrumb'=>'<a href="'.url('/').'" class="breadcrumb">'.trans('admin.dashboard').'</a><i data-feather="chevron-right" class="breadcrumb__icon"></i><a href="'.url('/rss-feed-src/side-menu/light').'" class="breadcrumb--active">'.trans('admin.rss_feed').'</a>'

        ]);
    }

    /**
     * Show feed items.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function feedItem(Request $request,$layout = 'side-menu', $theme = 'light')
    {
    	$final_array = array();
    	if(isset($_GET['category_id']) && $_GET['category_id']!='' && isset($_GET['source']) && $_GET['source']==''){
    		$search_cat = RssFeed::where('category_id',$_GET['category_id'])->get();
    		foreach($search_cat as $detail){
    			if($detail->rss_url!='' && $detail->rss_url!=null){
    				$rss_feed = simplexml_load_file($detail->rss_url);
	    			foreach($rss_feed->channel->item as $feed_item){
	    				$feed_item->category_id = $detail->category_id;
	    				$feed_item->source = $detail->rss_url;
		    			$explode = explode("=",$feed_item->guid); 
	    			   	if(isset($explode[1])){
							$feed_item->post_id = $explode[1];
		    			   	$blog = Blog::where('post_id',$feed_item->post_id)->first();
		    			   	if($blog){
		    			   		$feed_item->is_saved = 1;
		    			   	}else{
		    			   		$feed_item->is_saved = 0;
		    			   	}
						}else{
                            $blog = Blog::where('title',$feed_item->title)->first();
                            if($blog){
                                $feed_item->is_saved = 1;
                            }else{
                                $feed_item->is_saved = 0;
                            }
						}	    			   	
	    				array_push($final_array,$feed_item);
	    			}
    			}    			
    		}
    	}else if(isset($_GET['source']) && $_GET['source']!='' && isset($_GET['category_id']) && $_GET['category_id']==''){
            $search_cat = RssFeed::where('id',$_GET['source'])->first();
    		if($search_cat){
    			if($search_cat->rss_url!='' && $search_cat->rss_url!=null){
    				$rss_feed = simplexml_load_file($search_cat->rss_url);
					foreach($rss_feed->channel->item as $feed_item){
						$feed_item->category_id = $search_cat->category_id;
						$feed_item->source = $search_cat->rss_url;
						$explode = explode("=",$feed_item->guid); 
						if(isset($explode[1])){
							$feed_item->post_id = $explode[1];
		    			   	$blog = Blog::where('post_id',$feed_item->post_id)->first();
		    			   	if($blog){
		    			   		$feed_item->is_saved = 1;
		    			   	}else{
		    			   		$feed_item->is_saved = 0;
		    			   	}
						}else{
                            $blog = Blog::where('title',$feed_item->title)->first();
                            if($blog){
                                $feed_item->is_saved = 1;
                            }else{
                                $feed_item->is_saved = 0;
                            }
						}	    			   	
	    			   	if(!isset($feed_item->url) && $feed_item->url=='' && $feed_item->url==null){
	                        $feed_item->url = url('upload/author/default.png');
	                    }
						array_push($final_array,$feed_item);
					}
    			} 
    		}			
    	}else if(isset($_GET['source']) && $_GET['source']!='' && isset($_GET['category_id']) && $_GET['category_id']!=''){
    		$search_cat = RssFeed::where('id',$_GET['source'])->where('category_id',$_GET['category_id'])->first();
    		if($search_cat){
    			if($search_cat->rss_url!='' && $search_cat->rss_url!=null){
    				$rss_feed = simplexml_load_file($search_cat->rss_url);
					foreach($rss_feed->channel->item as $feed_item){
						$feed_item->category_id = $search_cat->category_id;
						$feed_item->source = $search_cat->rss_url;
						$explode = explode("=",$feed_item->guid); 
	    			   	if(isset($explode[1])){
							$feed_item->post_id = $explode[1];
		    			   	$blog = Blog::where('post_id',$feed_item->post_id)->first();
		    			   	if($blog){
		    			   		$feed_item->is_saved = 1;
		    			   	}else{
		    			   		$feed_item->is_saved = 0;
		    			   	}
						}else{
                            $blog = Blog::where('title',$feed_item->title)->first();
                            if($blog){
                                $feed_item->is_saved = 1;
                            }else{
                                $feed_item->is_saved = 0;
                            }
						}	
	    			   	if(!isset($feed_item->url) && $feed_item->url=='' && $feed_item->url==null){
	                        $feed_item->url = url('upload/author/default.png');
	                    }
						array_push($final_array,$feed_item);
					}
    			}    			
    		}			
    	}
    	if(isset($_GET['category_id']) && $_GET['category_id']!=''){
    		$sources = RssFeed::where('status',1)->where('category_id',$_GET['category_id'])->with('categoryData')->get();
    	}else{
    		$sources = RssFeed::where('status',1)->with('categoryData')->get();
    	}
        $feed_data = RssFeed::getAllRssFeed($request->all());
        $category = Category::where('status',1)->get();
        return view('super-admin/feed_items.index', [
            'theme' => $theme,
            'page_name' => 'index',
            'side_menu' => array(),
            'layout' => $layout,
            'feed' => $final_array,
            'category'=>$category,
            'sources'=>$sources,
            'breadcrumb'=>'<a href="'.url('/').'" class="breadcrumb">'.trans("admin.dashboard").'</a><i data-feather="chevron-right" class="breadcrumb__icon"></i><a href="'.url('/feed-item/side-menu/light').'" class="breadcrumb--active">'.trans("admin.feed_items").'</a>'
        ]);
    }

    /**
     * Method to delete rss feed
     * @param array $request post data, id
    */
    public function saveFeed(Request $request,$post_id,$category_id)	
    {

    	$rss_feed = simplexml_load_file($_GET['source']);
		foreach($rss_feed->channel->item as $feed_item){
			$explode = explode("=",$feed_item->guid);  
			if(isset($explode[1])){
				$feed_item->post_id = $explode[1];
				if($feed_item->post_id==$post_id){
					$explode_title = explode(":",$feed_item->title);
					$slug = \Helpers::createSlug($feed_item->title[0],'blog',0,false);
	                $post['slug'] = $slug;
					$post['title'] = $feed_item->title[0];
			    	$post['description'] = $feed_item->description[0];
			    	$post['banner_image'] = $feed_item->url[0];
			    	$post['url'] = $feed_item->link[0];
			    	$post['category_id'] = $category_id;
			    	$post['created_at'] = date('Y-m-d H:i:s');
					$post['post_id'] = $post_id;
					$post['status'] = 2;
                    $post['created_by'] = Auth::User()->id;
			        $blog_id = Blog::insertGetId($post);  
			        if($feed_item->url!=''){
			        	$image_id = BlogImages::insertGetId(array('blog_id'=>$blog_id,'image'=>$feed_item->url,'created_at'=>date('Y-m-d H:i:s'))); 
			        }		        
				}
			}else{
				if($feed_item->link[0]==$_GET['link']){
					$explode_title = explode(":",$feed_item->title);
					$slug = \Helpers::createSlug($feed_item->title[0],'blog',0,false);
	                $post['slug'] = $slug;
					$post['title'] = $feed_item->title[0];
			    	$post['description'] = $feed_item->description[0];
			    	$post['banner_image'] = $feed_item->url[0];
			    	$post['url'] = $feed_item->link[0];
			    	$post['category_id'] = $category_id;
			    	$post['created_at'] = date('Y-m-d H:i:s');
					$post['status'] = 2;
                    $post['created_by'] = Auth::User()->id;
			        $blog_id = Blog::insertGetId($post);  
			        if($feed_item->url!=''){
			        	$image_id = BlogImages::insertGetId(array('blog_id'=>$blog_id,'image'=>$feed_item->url,'created_at'=>date('Y-m-d H:i:s'))); 
			        }
		    	}
			}		   	
		}    	   
        return back()->with('success',__('message_alerts.post_saved_success'));
    }

    /**
     * Show Category view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function addUpdateRssFeedSrc(Request $request)
    {
        $post = $request->all();
        if(!empty($post)){
            if(!isset($post['id'])){
                $post['created_at'] = date('Y-m-d h:i:s');
                $id = RssFeed::addRssFeed($post);
                $msg = __('message_alerts.record_inserted');
            }else{
                $post['updated_at'] = date('Y-m-d h:i:s');
                $id = RssFeed::updateRssFeed($post);
                $msg = __('message_alerts.record_updated');
            }            
            return array('success'=>true,'data'=>$id,'message'=>$msg);
        }else{
            return array('success'=>false,'data'=>null,'message'=>__('message_alerts.something_went_wrong'));
        }
    }

    /**
     * Method to delete rss feed
     * @param array $request post data, id
    */
    public function deleteRssFeed(Request $request,$id)
    {
        RssFeed::where('id', $id)->delete();      
        return back()->with('success',__('message_alerts.rss_feed_deleted_success'));
    }
    /**
     * Method to change status of rss feed
     * @param array $request post data ,id ,status
    */
    public function changeRssFeedStatus(Request $request,$id,$status)
    {
        $post['status'] = $status;
        $post['id'] = $id;
        RssFeed::updateRssFeed($post);         
        return back()->with('success',__('message_alerts.status_changed_success'));  
	}
	
	public function getFeeds(Request $request){
        try {
            if($request->ajax()){
                $post = $request->all();
                if(isset($post['category_id']) && $post['category_id']!=''){
                    $data = RssFeed::where('category_id',$post['category_id'])->where('status',1)->get();
                }else{
                    $data = RssFeed::where('status',1)->get();
                }
                // $name = '';
                // if($post['image']!=''){
                //     $file=$request->file('image');
                //     $name = time() . rand() .'.'.$file->getClientOriginalExtension();
                //     $destination =  public_path('/upload/user/').$name;
                //     $c = \Helpers::compress_image($file,$destination,30);
                // }
                return response(\Helpers::sendSuccessAjaxResponse(__('message_alerts.record_updated'),$data));
            }else{
              return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.invalid_request')));
            }
        } catch (\Exception $ex) {
            return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.there_is_an_error')));
        }
    }
}
