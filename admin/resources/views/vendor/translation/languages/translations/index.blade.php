<?php
   $layout = (isset($layout))?$layout:'side-menu';
   $theme  = (isset($theme))?$theme:'light';
?>
@extends('../layout/' . $layout)
@section('subhead')
<title>{{__('admin.translations_list')}} - {{setting('site_name')}}</title>
@endsection
@section('subcontent')
@include('../layout/components/top-bar')
<h2 class="intro-y text-lg font-medium mt-10">{{__('admin.content_manager')}} <a href="{{url('languages')}}?layout={{$layout}}&theme={{$theme}}" class="button text-white bg-theme-1 shadow-md mr-2 pull-right font-size15" value="Reset"> {{__('admin.manage_languages')}} </a> </h2>
<div class="grid grid-cols-12 gap-6 mt-5">
   <div class="intro-y col-span-12 flex flex-wrap sm:flex-no-wrap items-center mt-2">
      <form method="GET">
         <input type="hidden" name="layout" value="{{$layout}}">
         <input type="hidden" name="theme" value="{{$theme}}">

         <input type="text" class="input mt-5 w-56 box pr-10 placeholder-theme-13" name="key"  @if(isset($_GET['key'])) value="{{$_GET['key']}}" @endif placeholder="{{__('admin.search_keyword')}}" >
         <select class="input w-56 mt-5 box pr-10 placeholder-theme-13" name="language_id">
            <option value="">{{__('admin.select_language')}}</option>
            @foreach($languages as $row)
            <option value="{{$row->id}}" <?php if(isset($_GET['language_id'])){ if($_GET['language_id']==$row->id){ echo "selected"; } }?>>{{$row->name}}</option>
            @endforeach
         </select>
         <select class="input w-56 mt-5 box pr-10 placeholder-theme-13" name="group">
            <option value="">{{__('admin.select_group')}}</option>
            @foreach($groups as $row)
            <option value="{{$row->group}}" <?php if(isset($_GET['group'])){ if($_GET['group']==$row->group){ echo "selected"; } }?>>{{$row->group}}</option>
            @endforeach
         </select>
         <input  name="search" type="submit" class="button mt-5 text-white bg-theme-1 shadow-md mr-2 ml-3" value="{{__('admin.search')}}">
         <!-- <input type="button" class="button mt-5 text-white bg-theme-1 shadow-md mr-2"  onclick="resetFilter()" value="{{__('admin.reset')}}"> -->

         <button class="button mr-1 mb-2 border text-gray-700 dark:bg-dark-5 dark:text-gray-300 bg_white" onclick="resetFilter()">{{__('admin.reset')}}</button>

      </form>
   </div>
<div class="intro-y col-span-12 overflow-auto lg:overflow-visible table-manage">
   <table class="table table-report -mt-2">
      <thead>
         <tr>
            <th class="uppercase font-thin">{{__('admin.keywords')}}</th>
            <th class="uppercase font-thin">{{__('admin.value')}}</th>
            <th class="uppercase font-thin">{{__('admin.edit')}}</th>
         </tr>
      </thead>
      <tbody>
         @if(count($translations))
         <?php $i=1; ?>
         @foreach($translations as $row)
         <tr>
            <td>{{ $row->keyword }} </td>
            <td>{{ $row->value }}</td>
            <td>
               <a class="flex items-center mr-3" title="{{__('admin.edit')}}" href="javascript:;" onclick="getTranslationValues('{{$row->id}}')" data-toggle="modal" data-target="#header-footer-modal-preview">
               <i data-feather="check-square" class="w-4 h-4 mr-1"></i> 
               </a>
            </td>
         </tr>
         <?php $i++; ?>
         @endforeach
         @else
         <tr class="intro-x text-center text-danger">
            <td class="w-40" colspan="7">
               {{__('admin.no_record_found')}}
            </td>
         </tr>
         @endif
      </tbody>
   </table>
   <div class="modal" id="header-footer-modal-preview">
      <div class="modal__content">
         <div class="flex items-center px-5 py-5 sm:py-3 border-b border-gray-200 dark:border-dark-5">
            <h2 class="font-medium text-base mr-auto" id="content-key">{{__('admin.edit_content')}}</h2>
         </div>
         <div class="flex items-center px-5 py-5 sm:py-3 ajax-msg hide">
         </div>
         <form id="addcategoryform" action="{{url('languages/translations/update')}}" method="post">
            @csrf
            <div id="append" class="pb-4">
            </div>
            <div class="px-5 py-3 text-right border-t border-gray-200 dark:border-dark-5">
               <button type="button" data-dismiss="modal" class="button w-20 border text-gray-700 dark:border-dark-5 dark:text-gray-300 mr-1">{{__('admin.cancel')}}</button>
               <input type="submit" class="button w-20 bg-theme-1 text-white" value="{{__('admin.save')}}">
            </div>
         </form>
      </div>
   </div>
</div>
<div class="intro-y col-span-8 flex flex-wrap sm:flex-row sm:flex-no-wrap items-center">
   <ul class="pagination">
      {!! $translations->appends(request()->except('page'))->render() !!}
   </ul>
</div>
<div class="intro-y col-span-1 flex flex-wrap sm:flex-row sm:flex-no-wrap items-center">
</div>
<div class="intro-y col-span-3 sm:flex-row sm:flex-no-wrap items-right">

    <p class="text-right"><?php if ($translations->firstItem() != null) { ?> {{__('admin.showing')}} {{ $translations->firstItem() }} {{__('admin.to')}} {{ $translations->lastItem() }} {{__('admin.of')}} {{ $translations->total() }} {{__('admin.entries')}} <?php }?></p>

</div>
</div>

@endsection