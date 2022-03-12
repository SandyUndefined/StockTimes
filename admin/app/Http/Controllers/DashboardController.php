<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Author;
use App\Models\Blog;
use App\Models\Category;
use Auth;
use App;
use DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Session;

class DashboardController extends Controller
{
    /**
     * Show Dashboard view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index($layout = 'side-menu', $theme = 'light')
    {
        $User = User::getAllUsersForDash('');
        $Author = Author::getAllAuthorsForDash('');
        $Blog = Blog::getAllActiveBlogForDash('');
        $Category = Category::getAllforDashCategory('');
        return view('super-admin/dashboard.index', [
            'theme' => $theme,
            'page_name' => 'index',
            'side_menu' => array(),
            'layout' => $layout,
            'user' => count($User),
            'author' => count($Author),
            'blog' => count($Blog),
            'category' => count($Category),
            'breadcrumb'=>'<a href="'.url('/').'" class="breadcrumb--active">'.trans("admin.dashboard").'</a>'
        ]);
    }



}
