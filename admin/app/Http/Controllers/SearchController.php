<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Blog;
use App\Models\Category;
use App\Models\Author;
use App\Models\SiteContent;
use App\Models\User;
use App\Models\SearchLog;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{

    function __construct(){
         $this->middleware('permission:search-log-list|rssfeed-save-post', ['only' => ['index']]);
         $this->middleware('permission:search-log-list', ['only' => ['index']]);
    }


    /**
     * Show Blog view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request,$layout = 'side-menu', $theme = 'light')
    {
        $search = SearchLog::getAllLogs($request->all());
        return view('super-admin/search.index', [
            'theme' => $theme,
            'page_name' => 'index',
            'side_menu' => array(),
            'layout' => $layout,
            'search'=>$search,
            'breadcrumb'=>'<a href="'.url('/').'" class="breadcrumb">'.trans("admin.dashboard").'</a><i data-feather="chevron-right" class="breadcrumb__icon"></i><a href="'.url('/search-log/side-menu/light').'" class="breadcrumb--active">'.trans("admin.search_log_list").'</a>'
            
        ]);
    }

    /**
     * Show Blog view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function addBlog()
    {
        $category = Category::getAllActiveCategory();
        $author = Author::getAllActiveAuthors();
        return view('super-admin/blog.create', [
            'theme' => 'light',
            'page_name' => 'create',
            'side_menu' => array(),
            'layout' => 'side-menu',
            'category'=>$category,
            'author'=>$author
        ]);
    }



    /**
     * Show Edit Blog view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function editBlog($id)
    {
        $category = Category::getAllActiveCategory();
        $author = Author::getAllActiveAuthors();
        $blog = Blog::find($id);
        return view('super-admin/blog.edit', [
            'theme' => 'light',
            'page_name' => 'create',
            'side_menu' => array(),
            'layout' => 'side-menu',
            'category'=>$category,
            'author'=>$author,
            'blog'=>$blog
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
                    $c = \Helpers::compress_image($file,$destination,30);
                    $post['image'] = url('/upload/user').'/'.$name;
                    User::updateUser($post);
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
                    $c = \Helpers::compress_image($file,$destination,30);
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
                $data['prefield'] = $post;
                $validate = [
                    'category_id' => 'required',
                    'author_id' => 'required',
                    'title' => 'required',
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
                    unset($post['_token']);
                    $postData = array(
                        'category_id' => \Helpers::checkEmpty($post['category_id']),
                        'author_id' => \Helpers::checkEmpty($post['author_id']),
                        'title' =>$post['title'],
                    );
                    if(isset($post['description']) && $post['description'] != ''){
                        $postData['description'] = $post['description'];
                    }
                    if(isset($post['thumb_image']) && $post['thumb_image'] != ''){
                        $postData['thumb_image'] = \Helpers::checkEmpty($post['thumb_image']);
                    }
                    if(isset($post['banner_image']) && $post['banner_image'] != ''){
                        $postData['banner_image'] = \Helpers::checkEmpty($post['banner_image']);
                    }
                    if(isset($post['time']) && $post['time'] != ''){
                        $postData['time'] = $post['time'];
                    }
                    if(isset($post['is_featured']) && $post['is_featured'] == 'on'){
                        $postData['is_featured'] = 1;
                    }
                    if(isset($post['id']) && $post['id'] !='' & $post['id'] != 0){

                        $slug = \Helpers::createSlug($postData['title'],'blog',$post['id'],false);
                        $postData['slug'] = $slug;

                        Blog::where('id', $post['id'])->update($postData);
                    }else{
                        $slug = \Helpers::createSlug($postData['title'],'blog',0,false);
                        $postData['slug'] = $slug;
                        Blog::insertGetId($postData);

                        if (setting('enable_notifications')) {

                            $user = User::where('active',1)->get();
                            $setting = SiteContent::where('key','firebase_msg_key')->first();
                            foreach($user as $detail){
                                if($detail->device_token!=null){
                                    \Helpers::sendNotification($detail->device_token,$postData['title'],'New blog arrived',$setting->value);
                                }
                            }   
                        }                     
                    }
                    return response(\Helpers::sendSuccessAjaxResponse(__('message_alerts.record_updated')));
                }
            }else{
                return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.invalid_request')));
            }
        } catch (\Exception $ex) {
            return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.there_is_an_error')));
        }
    }


    /**
     * Method to delete Blog
     * @param array $request post data, id
    */
    public function deleteBlog(Request $request,$id)
    {
        Blog::where('id', $id)->delete();      
        return back()->with('success',__('message_alerts.blog_deleted_success'));
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
}
