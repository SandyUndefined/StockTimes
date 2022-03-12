<div class="top-bar">
    <div class="-intro-x breadcrumb mr-auto hidden sm:flex">
        <?php if(isset($breadcrumb)){ echo $breadcrumb;} ?>
    </div>
    <?php 
        $layout = (isset($layout))?$layout:'side-menu';
        $theme  = (isset($theme))?$theme:'light';
        $userData = Auth::user();
        $language = DB::table('languages')->get();

    ?>
    <div class="intro-x dropdown w-8 h-8">
        <div class="dropdown-toggle w-8 h-8 rounded-full overflow-hidden shadow-lg image-fit zoom-in">
            <img src="{{url('upload/user')}}/{{ $userData->photo }}" onerror="this.onerror=null;this.src='<?php echo url("upload/no-image.png") ?>';">
        </div>

        <div class="dropdown-box w-56">
            <div class="dropdown-box__content box bg-theme-38 dark:bg-dark-6 text-white">
                <div class="p-4 border-b border-theme-40 dark:border-dark-3">
                    <div class="font-medium"><a href="{{url('/profile/')}}/{{$layout}}/{{$theme}}">{{ $userData->name }}</a></div>
                    <div class="text-xs text-theme-41 dark:text-gray-600"><a href="{{url('/profile/')}}/{{$layout}}/{{$theme}}">{{ $userData->email }}</a></div>
                </div>
                <div class="p-2 border-t border-theme-40 dark:border-dark-3">
                    <a href="{{ url('/admin_logout') }}" class="flex items-center block p-2 transition duration-300 ease-in-out hover:bg-theme-1 dark:hover:bg-dark-3 rounded-md">
                        <i data-feather="toggle-right" class="w-4 h-4 mr-2"></i> {{__('admin.logout')}}
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>