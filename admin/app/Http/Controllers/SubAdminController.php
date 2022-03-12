<?php

namespace App\Http\Controllers;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
Use DB;

class SubAdminController extends Controller
{
    function __construct(){
         $this->middleware('permission:sub-admin-list|sub-admin-edit|sub-admin-create|sub-admin-delete', ['only' => ['index','addUpdateSubAdmin','deleteSubAdmin']]);
         $this->middleware('permission:sub-admin-list', ['only' => ['index']]);
         $this->middleware('permission:sub-admin-edit', ['only' => ['addUpdateSubAdmin']]);
         $this->middleware('permission:sub-admin-create', ['only' => ['addUpdateSubAdmin']]);
         $this->middleware('permission:sub-admin-delete', ['only' => ['deleteSubAdmin']]);
    }

    /**
     * Show Category view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request,$layout = 'side-menu', $theme = 'light')
    {
        $category = User::getAllSubadmin($request->all());
        return view('sub_admin.index', [
            'theme' => $theme,
            'page_name' => 'index',
            'side_menu' => array(),
            'layout' => $layout,
            'category'=>$category,
            'breadcrumb'=>'<a href="'.url('/').'" class="breadcrumb">'.trans("admin.dashboard").'</a><i data-feather="chevron-right" class="breadcrumb__icon"></i><a href="'.url('/sub-admin/side-menu/light').'" class="breadcrumb--active">Sub Admin List</a>'
        ]);
    }

    /**
     * Show Category view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function addUpdateSubAdmin(Request $request)
    {
        $post = $request->all();
        if(!empty($post)){
            if (isset($post['image']) && $post['image'] != '') {
               $post['photo'] = $post['image'];
               unset($post['image']);
            }else{
                unset($post['image']);
            }

            if(!isset($post['id'])){
                $emailExist = User::where('email',$post['email'])->first();
                if ($emailExist) {
                    return array('success'=>false,'data'=>null,'message'=>'Email Id already exist !');
                }

                $post['active'] = 1;
                $post['type'] = "subadmin";
                $post['created_at'] = date('Y-m-d h:i:s');
                $post['password'] = bcrypt($post['password']);
                $id = User::addUser($post);
                // echo json_encode($id);exit;
                $user= User::find($id['id']);
                $role = Role::where('name','subadmin')->first();
                $permissions = DB::table('role_has_permissions')->where('role_id',$role->id)->pluck('permission_id')->all();
                $role->syncPermissions($permissions);
                $user->assignRole([$role->id]);
                $msg = __('message_alerts.record_inserted');
            }else{
                $emailExist = User::where('id','!=',$post['id'])->where('email',$post['email'])->first();
                if ($emailExist) {
                    return array('success'=>false,'data'=>null,'message'=>'Email Id already exist !');
                }
                $post['updated_at'] = date('Y-m-d h:i:s');
                if(isset($post['password'])){
                    $post['password'] = bcrypt($post['password']);
                }
                $id = User::updateUser($post);
                $msg = __('message_alerts.record_updated');
            }            
            return array('success'=>true,'data'=>$id,'message'=>$msg);
        }else{
            return array('success'=>false,'data'=>null,'message'=>__('message_alerts.something_went_wrong'));
        }
    }

    /**
     * upload category thumb image
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function uploadSubAdminThumbImage(Request $request){
        try {
            if($request->ajax()){
                $post = $request->all();
                $name = '';
                if($post['image']!=''){
                    $file=$request->file('image');
                    $name = time() . rand() .'.'.$file->getClientOriginalExtension();
                    $destination =  public_path('/upload/user/').$name;
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
     * Method to delete category
     * @param array $request post data, id
    */
    public function deleteSubAdmin(Request $request,$id)
    {
        User::where('id', $id)->delete();      
        return back()->with('success',__('message_alerts.sub_admin_deleted_success'));
    }

    /**
     * Method to change status of category
     * @param array $request post data ,id ,status
    */
    public function changeSubAdminStatus(Request $request,$id,$status)
    {
        $post['active'] = $status;
        $post['id'] = $id;
        $user = User::updateUser($post);
        return back()->with('success',__('message_alerts.status_changed_success'));  
    }  

    public function setpermissions(Request $request)
    {
        $array = array(
            [
                'name'=>'dashboard'
            ],
            [
                'name'=>'feed-item'
            ]
        );
        $user = Permission::create([
                'name'=>'change-status-sub-admin'
            ]);
        return back()->with('success',__('message_alerts.status_changed_success'));  
    } 
}
