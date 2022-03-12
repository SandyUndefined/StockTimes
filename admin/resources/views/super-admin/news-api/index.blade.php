@extends('../layout/' . $layout)

@section('subhead')
    <title>{{__('admin.news_api_list')}} - {{setting('site_name')}}</title>
@endsection

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/dt-1.10.12/datatables.min.css" />

@section('subcontent')
    @include('../layout/components/top-bar')
    <h2 class="intro-y text-lg font-medium mt-10">{{__('admin.search_news_api_post')}}</h2>
    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12 flex flex-wrap sm:flex-no-wrap items-center mt-2">

            <form method="GET">
                <input type="text" class="input mt-5 w-56 box pr-10 placeholder-theme-13" name="q"  @if(isset($_GET['q'])) value="{{$_GET['q']}}" @endif placeholder="{{__('admin.search_news_api_post')}}" >

                <input type="text" class="input mt-5 w-56 box pr-10 placeholder-theme-13" name="domains"  @if(isset($_GET['domains'])) value="{{$_GET['domains']}}" @endif placeholder="{{__('admin.domains_search_placeholder')}}" >

                <select class="input w-56 mt-5 box pr-10 placeholder-theme-13" name="sources">
                    <option value="">{{__('admin.all_source')}}</option>
                    @foreach($sources as $src)
                        <option value="{{$src['id']}}" <?php if(isset($_GET['sources'])){ if($_GET['sources']==$src['id']){ echo "selected"; } }?>>{{$src['name']}}</option>
                    @endforeach
                </select>

                <select class="input w-56 mt-5 box pr-10 placeholder-theme-13" name="language">
                    <option value="">{{__('admin.all_language')}}</option>
                    @foreach($news_api_language as $key => $value)
                        <option value="{{$key}}" <?php if(isset($_GET['language'])){ if($_GET['language']==$key){ echo "selected"; } }?>>{{$value}}</option>
                    @endforeach
                </select>

                <input type="text" class="input w-56 mt-5 box pr-10 placeholder-theme-13 datepicker" name="from" placeholder="{{__('admin.from_date')}}"  @if(isset($_GET['from'])) value="{{$_GET['from']}}" @endif>
                <input type="text" class="input w-56 mt-5 box pr-10 placeholder-theme-13 datepicker" name="to" placeholder="{{__('admin.to_date')}}"  @if(isset($_GET['to'])) value="{{$_GET['to']}}" @endif>

                <button type="submit" class="button mt-5 text-white bg-theme-1 shadow-md mr-2 ml-2">{{__('admin.search_news')}}</button>
                <button class="button ml-2 mt-2 mr-1 mb-2 border text-gray-700 dark:bg-dark-5 dark:text-gray-300 bg_white" onclick="resetFilter()">{{__('admin.reset')}}</button>
            </form>

            <div class="hidden md:block mx-auto text-gray-600">  </div>
            <div class="w-full sm:w-auto mt-3 sm:mt-0 sm:ml-auto md:ml-0">
            </div>
        </div>
        <div class="intro-y col-span-12 overflow-auto lg:overflow-visible table-manage">
            <table class="table table-report -mt-2">
                <thead>
                    <tr>
                        <th class="whitespace-no-wrap">{{__('admin.image')}}</th>
                        <th class="whitespace-no-wrap">{{__('admin.title_desc')}}</th>
                        @can('feed-item-save-post')
                        <th class="text-center whitespace-no-wrap">{{__('admin.action')}}</th>
                        @endcan
                    </tr>
                </thead>
               
                <tbody>
                    @if(count($data))
                        <?php $i=1; ?>
                        @foreach ($data as $row)

                            <tr class="intro-x row1" data-id="{{ $i }}">
                                <td class="w-40">
                                    @if(isset($row['urlToImage']) && $row['urlToImage']!=null && $row['urlToImage']!='')
                                        <img onerror="this.onerror=null;this.src='<?php echo url("upload/no-image.png") ?>';"  src="{{$row['urlToImage']}}" width="150" onerror="this.onerror=null;this.src='<?php echo url("upload/no-image.png") ?>';">
                                    @else
                                        <img onerror="this.onerror=null;this.src='<?php echo url("upload/no-image.png") ?>';"  src="{{url('upload/author/default.png')}}" width="150" onerror="this.onerror=null;this.src='<?php echo url("upload/no-image.png") ?>';">
                                    @endif
                                </td> 
                                <td >
                                    <a target="_blank" href="{{$row['url']}}" class="font-medium whitespace-no-wrap"><?php echo substr($row['title'], 0,90);?></a> 
                                    <div class="text-gray-600 text-xs"><?php echo substr($row['description'], 0,150)."........";?></div>
                                </td>
                                <td class="table-report__action w-56">
                                    <div class="flex justify-center items-center">
                                        <form method="post" action="{{url('save-news-api-post')}}">
                                            @csrf
                                            <input type="hidden" name="source" value="{{$row['source']['name']}}">
                                            <input type="hidden" name="author" value="{{$row['author']}}">
                                            <input type="hidden" name="title" value="{{$row['title']}}">
                                            <input type="hidden" name="description" value="{{$row['description']}}">
                                            <input type="hidden" name="url" value="{{$row['url']}}">
                                            <input type="hidden" name="urlToImage" value="{{$row['urlToImage']}}">
                                            <input type="hidden" name="publishedAt" value="{{$row['publishedAt']}}">
                                            <input type="hidden" name="content" value="{{$row['content']}}">
                                            <button trpe="submit" class="font-medium whitespace-no-wrap button text-white bg-theme-1"> {{__('admin.save_as_post')}}</button>
                                        </form>

                                        <!-- <a href="{{url('save-news-api-post')}}?source={{$row['source']['name']}}&author={{$row['author']}}&title={{$row['title']}}&description={{$row['description']}}&url={{$row['url']}}&urlToImage={{$row['urlToImage']}}&publishedAt={{$row['publishedAt']}}&content={{$row['content']}}" class="font-medium whitespace-no-wrap button text-white bg-theme-1"> {{__('admin.save_as_post')}}</a> -->

                                        <div class="text-gray-600 text-xs whitespace-no-wrap"></div>
                                    </div>
                                   
                                </td>
                            </tr>
                            <?php $i++; ?>
                        @endforeach
                    @else
                        <tr class="intro-x text-center text-danger">
                            <td class="w-40" colspan="4">
                                {{__('admin.no_record_found')}}
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        <div class="intro-y col-span-8 flex flex-wrap sm:flex-row sm:flex-no-wrap items-center">
            <ul class="pagination">
            </ul>
        </div>
        <div class="intro-y col-span-1 flex flex-wrap sm:flex-row sm:flex-no-wrap items-center">
            
        </div>
        <div class="intro-y col-span-3 flex flex-wrap sm:flex-row sm:flex-no-wrap items-right">
        </div>
    </div>


@endsection