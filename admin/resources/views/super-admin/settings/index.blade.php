@extends('../layout/' . $layout)

@section('subhead')
    <title>{{__('admin.settings_list')}} - {{setting('site_name')}}</title>
@endsection

@section('subcontent')
    @include('../layout/components/top-bar')

    <h2 class="intro-y text-lg font-medium mt-10">{{__('admin.settings_list')}}</h2>
    <div class="grid grid-cols-12 gap-6">
        <div class="col-span-12 lg:col-span-4 xxl:col-span-3 flex lg:block flex-col-reverse">
            <div class="intro-y box bg-theme-1 p-5 mt-6">
                <div class="border-theme-3 dark:border-dark-5 mt-5 text-white">
                    <a href="{{url('/setting/')}}/{{$layout}}/{{$theme}}/site-setting" class="flex items-center px-3 py-2 rounded-md <?php if(Request::segment(4) == 'site-setting') { echo ' bg-theme-22 dark:bg-dark-1 font-medium'; } ?>"> <i class="w-4 h-4 mr-2" data-feather="settings"></i> {{__('admin.site_settings')}} </a>
                    <a href="{{url('/setting/')}}/{{$layout}}/{{$theme}}/global" class="flex items-center px-3 py-2 mt-2 rounded-md <?php if(Request::segment(4) == 'global') { echo ' bg-theme-22 dark:bg-dark-1 font-medium'; } ?>"> <i class="w-4 h-4 mr-2" data-feather="phone"></i> {{__('admin.mobile_app_settings')}} </a>
                    <a href="{{url('/setting/')}}/{{$layout}}/{{$theme}}/local" class="flex items-center px-3 py-2 mt-2 rounded-md <?php if(Request::segment(4) == 'local') { echo ' bg-theme-22 dark:bg-dark-1 font-medium'; } ?>"> <i class="w-4 h-4 mr-2" data-feather="box"></i> {{__('admin.localization_settings')}} </a>
                    <a href="{{url('/setting/')}}/{{$layout}}/{{$theme}}/notification" class="flex items-center px-3 py-2 mt-2 rounded-md <?php if(Request::segment(4) == 'notification') { echo ' bg-theme-22 dark:bg-dark-1 font-medium'; } ?>"> <i class="w-4 h-4 mr-2" data-feather="send"></i> {{__('admin.push_notification_settings')}} </a>
                    <a href="{{url('/setting/')}}/{{$layout}}/{{$theme}}/social" class="flex items-center px-3 py-2 mt-2 rounded-md <?php if(Request::segment(4) == 'social') { echo ' bg-theme-22 dark:bg-dark-1 font-medium'; } ?>"> <i class="w-4 h-4 mr-2" data-feather="activity"></i>{{__('admin.social_media_settings')}} </a>

                    <a href="{{url('/setting/')}}/{{$layout}}/{{$theme}}/font-setting" class="flex items-center px-3 py-2 mt-2 rounded-md <?php if(Request::segment(4) == 'font-setting') { echo ' bg-theme-22 dark:bg-dark-1 font-medium'; } ?>"> <i class="w-4 h-4 mr-2" data-feather="type"></i>{{__('admin.font_setting')}} </a>

                </div>
            </div>
        </div>

        <div class="col-span-12 lg:col-span-8 xxl:col-span-9">
            <div class="intro-y box lg:mt-5">
                <div class="flex items-center px-5 py-5 sm:py-3 border-b border-gray-200 dark:border-dark-5">
                                            
                    
                    <h2 class="font-medium text-base mr-auto">
                        @if(isset($title)){{$title}}@endif
                    </h2>
                </div>
                <div class="p-5">
                    @if($page == 'permission')
                        <form method="post" action="{{url('/admin/settingPermission')}}">
                            {{ csrf_field() }}
                            <div class="mt-3">
                                <label>{{__('admin.roles')}}</label>
                                <select name="role_id" class="input w-full border mt-2">
                                    <option value="">{{__('admin.roles_placeholder')}}</option>
                                    @foreach($roles as $role)
                                        <option value="{{$role->id}}">{{ucfirst($role->name)}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mt-3">
                                <label>{{__('admin.permission')}}</label>
                                <select name="permission_id[]" class="input w-full border mt-2" multiple>
                                    <option value="">{{__('admin.permission_placeholder')}}</option>
                                    @foreach($permissions as $permission)
                                        <option value="{{$permission->id}}">{{$permission->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="text-right mt-5">
                                @can('setting-edit')
                                    <button type="submit" class="button w-24 bg-theme-1 text-white" id="createBtn">{{__('admin.save')}}</button>
                                @endcan
                            </div>
                        </form>
                    @else
                        <form method="post" action="{{url('/updateSetting')}}">
                            {{ csrf_field() }}
                            @foreach($data as $row)

                                @if($page == 'global')
                                    @if($row->key == 'blog_accent_code')
                                        <div class="mt-3">
                                            <label>{{__('admin.default_blog_accent')}}</label>
                                            <div class="mt-2">
                                                <select data-placeholder="{{__('admin.accent_plceholder')}}" name="blog_accent_code" class="tail-select w-full">
                                                    @for($c= 0; $c< count($voice_accent);$c++)
                                                        <option @if($row->value == $voice_accent[$c]) selected  @endif value="{{$voice_accent[$c]}}" >{{$voice_accent[$c]}}</option>
                                                    @endfor
                                                </select>
                                            </div>
                                        </div>
                                    @endif

                                    @if($row->key == 'app_name')
                                        <div>
                                            <label>{{__('admin.app_name')}}</label>
                                            <input type="text" class="input w-full border mt-2" name="app_name" value="{{$row->value}}" placeholder="{{__('admin.app_name_placeholder')}}">
                                        </div>
                                    @endif
                                    @if($row->key == 'app_subtitle')

                                        <div class="mt-3">
                                            <label>{{__('admin.app_subtitle')}}</label>
                                            <input type="text" class="input w-full border mt-2" name="app_subtitle" value="{{$row->value}}" placeholder="{{__('admin.app_subtitle_placeholder')}}">
                                        </div>

                                    @endif
                                    @if($row->key == 'bg_image')
                                        <?php 
                                            if(file_exists(public_path()."/upload/bg/".$row->value) && $row->value!='') { 
                                                $url = url('upload/bg').'/'.$row->value;
                                            }else{
                                                $url = url('upload/no-image.png');
                                            }
                                        ?>
                                        <input type="hidden" name="bg_image" id="bg_image" value="">
                                        <div class="mt-3">
                                            <label>{{__('admin.background_image')}}</label>
                                            <div class="col-span-12 sm:col-span-12">
                                                <input type="button" class="button w-30 bg-theme-1 text-white" value="{{__('admin.upload_background_image')}}" onclick="triggerFileInput('bguploadBtn')">
                                                <input class="bguploadBtn hide"  type="file" onchange="uploadBgImage(this,'bgimage_add','add',0);" accept="image/jpg, image/jpeg"/>
                                            </div>
                                            <div class="col-span-12 sm:col-span-12 mt-3">
                                                <img onerror="this.onerror=null;this.src='<?php echo url("upload/no-image.png") ?>';"  onerror="this.onerror=null;this.src='<?php echo url("upload/no-image.png") ?>';"  id="bgimage_add" src="{{$url}}" class="width-20" >
                                            </div>
                                        </div>
                                    @endif

                                @elseif($page == 'local')

                                    @if($row->key == 'date_format')
                                        <div>
                                            <label>{{__('admin.date_formate')}}</label>
                                            <input type="text" class="input w-full border mt-2" name="date_format" value="{{$row->value}}" placeholder="{{__('admin.date_formate_placeholder')}}">
                                        </div>
                                    @endif
                                
                                    @if($row->key == 'timezone')
                                        <div class="mt-3">
                                            <label>{{__('admin.timezone')}}</label>
                                            <div class="mt-2">
                                                <select data-placeholder="{{__('admin.select_timezone')}}" name="timezone" class="tail-select w-full">
                                                    @for($c= 0; $c< count($zones);$c++)
                                                        <option @if($row->value == $zones[$c]) selected  @endif value="{{$zones[$c]}}" >{{$zones[$c]}}</option>
                                                    @endfor
                                                </select>
                                            </div>
                                        </div>
                                    @endif

                                @elseif($page == 'font-setting')

                                    @if($row->key == 'h_1_size')
                                        <div>
                                            <label>{{__('admin.h_1_size')}}</label>
                                            <input type="number" class="input w-full border mt-2" name="h_1_size" value="{{$row->value}}" placeholder="{{__('admin.h_1_size_placeholder')}}">
                                        </div>
                                    @endif

                                    @if($row->key == 'h_2_size')
                                        <div>
                                            <label>{{__('admin.h_2_size')}}</label>
                                            <input type="number" class="input w-full border mt-2" name="h_2_size" value="{{$row->value}}" placeholder="{{__('admin.h_2_size_placeholder')}}">
                                        </div>
                                    @endif

                                    @if($row->key == 'h_3_size')
                                        <div>
                                            <label>{{__('admin.h_3_size')}}</label>
                                            <input type="number" class="input w-full border mt-2" name="h_3_size" value="{{$row->value}}" placeholder="{{__('admin.h_3_size_placeholder')}}">
                                        </div>
                                    @endif

                                    @if($row->key == 'h_4_size')
                                        <div>
                                            <label>{{__('admin.h_4_size')}}</label>
                                            <input type="number" class="input w-full border mt-2" name="h_4_size" value="{{$row->value}}" placeholder="{{__('admin.h_4_size_placeholder')}}">
                                        </div>
                                    @endif


                                    @if($row->key == 'p_size')
                                        <div>
                                            <label>{{__('admin.p_size')}}</label>
                                            <input type="number" class="input w-full border mt-2" name="p_size" value="{{$row->value}}" placeholder="{{__('admin.p_size_placeholder')}}">
                                        </div>
                                    @endif


                                    @if($row->key == 'span_size')
                                        <div>
                                            <label>{{__('admin.span_size')}}</label>
                                            <input type="number" class="input w-full border mt-2" name="span_size" value="{{$row->value}}" placeholder="{{__('admin.span_size_placeholder')}}">
                                        </div>
                                    @endif


                                    @if($row->key == 'small_size')
                                        <div>
                                            <label>{{__('admin.small_size')}}</label>
                                            <input type="number" class="input w-full border mt-2" name="small_size" value="{{$row->value}}" placeholder="{{__('admin.small_size_placeholder')}}">
                                        </div>
                                    @endif


                                    @if($row->key == 'lable_size')
                                        <div>
                                            <label>{{__('admin.lable_size')}}</label>
                                            <input type="number" class="input w-full border mt-2" name="lable_size" value="{{$row->value}}" placeholder="{{__('admin.lable_size_placeholder')}}">
                                        </div>
                                    @endif


                                    @if($row->key == 'font_family')
                                        <div class="mt-3">
                                            <label>{{__('admin.font_family')}}</label>
                                            <div class="mt-2">
                                                <select data-placeholder="{{__('admin.select_font_family')}}" name="font_family" class="tail-select w-full">
                                                    @for($c= 0; $c< count($font_family);$c++)
                                                        <option @if($row->value == $font_family[$c]) selected  @endif value="{{$font_family[$c]}}" >{{$font_family[$c]}}</option>
                                                    @endfor
                                                </select>
                                            </div>
                                        </div>
                                    @endif


                                @elseif($page == 'site-setting')
                                    @if($row->key == 'homepage_theme')
                                        <div class="mt-3 mb-10">
                                            <label>{{__('admin.home_page_theme')}}</label>
                                            <div class="mt-2">
                                                <select data-placeholder="Select Home Page Theme" name="homepage_theme" class="tail-select w-full">
                                                    <option @if($row->value == 'home_1') selected  @endif value="home_1" >{{__('admin.theme_1')}}</option>
                                                    <option @if($row->value == 'home_2') selected  @endif value="home_2" >{{__('admin.theme_2')}}</option>
                                                    <option @if($row->value == 'home_3') selected  @endif value="home_3" >{{__('admin.theme_3')}}</option>
                                                    <option @if($row->value == 'home_4') selected  @endif value="home_4" >{{__('admin.theme_4')}}</option>
                                                    <option @if($row->value == 'home_5') selected  @endif value="home_5" >{{__('admin.theme_5')}}</option>
                                                </select>
                                            </div>
                                        </div>
                                    @endif

                                    @if($row->key == 'layout')
                                        <div class="mt-3 mb-10">
                                            <label>{{__('admin.blog_datail_theme')}}</label>
                                            <div class="mt-2">
                                                <select data-placeholder="{{__('admin.blog_datail_theme_placeholder')}}" name="layout" class="tail-select w-full">
                                                    <option @if($row->value == 'layout_1') selected  @endif value="layout_1" >{{__('admin.theme_1')}}</option>
                                                    <option @if($row->value == 'layout_2') selected  @endif value="layout_2" >{{__('admin.theme_2')}}</option>
                                                </select>
                                            </div>
                                        </div>
                                    @endif

                                    @if($row->key == 'site_name')
                                        <div class="mt-3 mb-10">
                                            <label>{{__('admin.Website_name')}}</label>
                                            <input type="text" class="input w-full border mt-2" name="site_name" value="{{$row->value}}" placeholder="{{__('admin.Website_name_placeholder')}}<">
                                        </div>
                                    @endif

                                    @if($row->key == 'site_title')
                                        <div class="mt-3 mb-10">
                                            <label>{{__('admin.Website_title')}}</label>
                                            <input type="text" class="input w-full border mt-2" name="site_title" value="{{$row->value}}" placeholder="{{__('admin.Website_title_placeholder')}}">
                                        </div>
                                    @endif

                                    @if($row->key == 'from_email')
                                        <div class="mt-3 mb-10">
                                            <label>{{__('admin.email_from')}}</label>
                                            <input type="text" class="input w-full border mt-2" name="from_email" value="{{$row->value}}" placeholder="{{__('admin.email_from_placeholder')}}">
                                        </div>
                                    @endif

                                    @if($row->key == 'news_api_key')
                                        <div class="mt-3 mb-10">
                                            <label>{{__('admin.news_api_key')}}</label>
                                            <input type="text" class="input w-full border mt-2" name="news_api_key" value="{{$row->value}}" placeholder="{{__('admin.news_api_key_placeholder')}}">
                                        </div>
                                    @endif


                                    @if($row->key == 'fb_share')

                                    <h2 class="font-medium text-base mr-auto  mt-10">
                                        {{__('admin.share_on_social_media')}}
                                        <small>({{__('admin.share_on_social_media_small_text')}})</small>
                                    </h2>

                                        <div class="col-span-12 sm:col-span-3">
                                            <div class="mt-3">
                                                <label>{{__('admin.facebook_share')}}</label>
                                                <div class="mt-2">
                                                    <input type="checkbox" name="fb_share" class="input input--switch border" @if($row->value == 1) checked @endif  >
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    @if($row->key == 'twitter_share')
                                        <div class="col-span-12 sm:col-span-3">
                                            <div class="mt-3">
                                                <label>{{__('admin.twitter_share')}}</label>
                                                <div class="mt-2">
                                                    <input type="checkbox" name="twitter_share" @if($row->value == 1) checked @endif class="input input--switch border" >
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    @if($row->key == 'instagram_share')
                                        <div class="col-span-12 sm:col-span-3">
                                            <div class="mt-3">
                                                <label>{{__('admin.downloadable_instagram_image')}}</label>
                                                <div class="mt-2">
                                                    <input type="checkbox" name="instagram_share" @if($row->value == 1) checked @endif class="input input--switch border" >
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    @if($row->key == 'site_logo')
                                        <?php 
                                            if(file_exists(public_path()."/upload/logo/".$row->value) && $row->value!='') { 
                                                $url = url('upload/logo').'/'.$row->value;
                                            }else{
                                                $url = url('upload/no-image.png');
                                            }
                                        ?>
                                        <input type="hidden" name="site_logo" id="site_logo" value="">
                                        <div class="mt-3">
                                            <label>{{__('admin.website_logo')}}</label>
                                            <div class="col-span-12 sm:col-span-12">
                                                <input type="button" class="button w-30 bg-theme-1 text-white" value="{{__('admin.upload_website_logo')}}" onclick="triggerFileInput('bguploadBtn')">
                                                <input class="bguploadBtn hide" type="file" onchange="uploadWebsiteLogo(this,'website_logo','add',0);" accept="image/jpg, image/jpeg"/>
                                            </div>
                                            <div class="col-span-12 sm:col-span-12 mt-3">
                                                <img onerror="this.onerror=null;this.src='<?php echo url("upload/no-image.png") ?>';"  onerror="this.onerror=null;this.src='<?php echo url("upload/no-image.png") ?>';"  id="website_logo" src="{{$url}}" class="width-20"  >
                                            </div>
                                        </div>
                                    @endif

                                    @if($row->key == 'site_phone')
                                        <div class="mt-3 mb-10">
                                            <label>{{__('admin.top_phone_number')}}</label>
                                            <input type="text" class="input w-full border mt-2" name="site_phone" value="{{$row->value}}" placeholder="{{__('admin.top_phone_number_placeholder')}}">
                                        </div>
                                    @endif

                                    @if($row->key == 'footer_about')
                                        <div class="mt-3 mb-10">
                                            <label>{{__('admin.footer_about_us_info')}}</label>
                                            <input type="text" class="input w-full border mt-2" name="footer_about" value="{{$row->value}}" placeholder="{{__('admin.footer_about_us_info_placeholder')}}">
                                        </div>
                                    @endif

                                    @if($row->key == 'powered_by')
                                        <div class="mt-3 mb-10">
                                            <label>{{__('admin.powered_by')}}</label>
                                            <input type="text" class="input w-full border mt-2" name="powered_by" value="{{$row->value}}" placeholder="{{__('admin.powered_by_placeholder')}}">
                                        </div>
                                    @endif

                                    @if($row->key == 'site_seo_title')
                                        <div class="mt-3 mb-10">
                                            <label>{{__('admin.seo_title')}}</label>
                                            <input type="text" class="input w-full border mt-2" name="site_seo_title" value="{{$row->value}}" placeholder="{{__('admin.seo_title_placeholder')}}">
                                        </div>
                                    @endif

                                    @if($row->key == 'site_seo_description')
                                        <div class="mt-3 mb-10">
                                            <label>{{__('admin.seo_description')}}</label>
                                            <textarea type="text" class="input w-full border mt-2" name="site_seo_description" placeholder="{{__('admin.seo_description_placeholder')}}">{{$row->value}}</textarea>
                                        </div>
                                    @endif

                                    @if($row->key == 'site_seo_keyword')
                                        <div class="mt-3 mb-10">
                                            <label>{{__('admin.seo_keyword')}}</label>
                                            <textarea type="text" class="input w-full border mt-2" name="site_seo_keyword" placeholder="{{__('admin.seo_keyword_placeholder')}}">{{$row->value}}</textarea>
                                        </div>
                                    @endif

                                    @if($row->key == 'site_seo_tag')
                                        <div class="mt-3 mb-10">
                                            <label>{{__('admin.seo_tags')}}</label>
                                            <textarea type="text" class="input w-full border mt-2" name="site_seo_tag" placeholder="{{__('admin.seo_tags_placeholder')}}">{{$row->value}}</textarea>
                                        </div>
                                    @endif

                                    @if($row->key == 'preferred_site_language')
                                        <div class="mt-3 mb-10">
                                            <label>{{__('admin.preferred_site_language')}}</label>
                                            <div class="mt-2">
                                                <select data-placeholder="{{__('admin.preferred_site_language_placeholder')}}" name="preferred_site_language" class="tail-select w-full">
                                                    @foreach($languages as $language)
                                                        <option @if($row->value == $language->language) selected  @endif  value="{{$language->language}}">{{$language->name}}</option>
                                                    @endforeach

                                                </select>
                                            </div>
                                        </div>
                                    @endif

                                @elseif($page == 'notification')

                                    @if($row->key == 'enable_notifications')
                                        <div class="mt-3 mb-10">
                                            <label for="vertical-checkbox-chris-evans">{{__('admin.enable_push_notification')}}</label>
                                            <div class="flex items-center text-gray-700 dark:text-gray-500 mt-2">
                                                <input type="checkbox" class="input border mr-2" name="enable_notifications" @if($row->value == 1)  checked @endif  id="vertical-checkbox-chris-evans">
                                                <label class="cursor-pointer select-none" for="vertical-checkbox-chris-evans">{{__('admin.enable_push_notification_placeholder')}}</label>
                                            </div>
                                        </div>
                                    @endif

                                    @if($row->key == 'firebase_msg_key')
                                        <div class="mt-3 mb-10">
                                            <label>{{__('admin.firebase_msg_key')}}</label>
                                            <input type="text" class="input w-full border mt-2" name="firebase_msg_key" value="{{$row->value}}" placeholder="{{__('admin.firebase_msg_key_placeholder')}}">
                                        </div>
                                    @endif

                                    @if($row->key == 'firebase_api_key')
                                        <div class="mt-3">
                                            <label>{{__('admin.firebase_api_key')}}</label>
                                            <input type="text" class="input w-full border mt-2" name="firebase_api_key" value="{{$row->value}}" placeholder="{{__('admin.firebase_api_key_placeholder')}}">
                                        </div>
                                    @endif

                                @endif

                            @endforeach
                            <div class="text-right mt-5">                                        
                                @can('setting-edit')
                                    <button type="submit" class="button w-24 bg-theme-1 text-white" id="createBtn">{{__('admin.save')}}</button>
                                @endcan
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection