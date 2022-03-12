@extends('../layout/' . $layout)

@section('subhead')
    <title>{{__('admin.cms_pages_list')}} - {{setting('site_name')}}</title>
@endsection

@section('subcontent')
    @include('../layout/components/top-bar')

    <h2 class="intro-y text-lg font-medium mt-10">{{__('admin.cms_pages_list')}}</h2>
    <div class="grid grid-cols-12 gap-6 mt-5">
        
        <div class="intro-y col-span-12 overflow-auto lg:overflow-visible">
            <table class="table table-report -mt-2">
                <thead>
                    <tr>
                        <th class="whitespace-no-wrap">{{__('admin.id')}}</th>
                        <th class="whitespace-no-wrap">{{__('admin.image')}}</th>
                        <th class="whitespace-no-wrap">{{__('admin.title')}}</th>
                        <th class="whitespace-no-wrap">{{__('admin.created_at')}}</th>
                        <th class="text-center whitespace-no-wrap">{{__('admin.action')}}</th>
                    </tr>
                </thead>
              
                <tbody>
                    @if(count($cms))
                        <?php $i=1; ?>
                        @foreach ($cms as $row)
                            <tr class="intro-x">
                                <td class="w-40">
                                    {{$i}}
                                </td>
                         

                                <?php 
                                    if(file_exists(public_path()."/upload/cms/original/".$row->image) && $row->image!='') { 
                                        $url = url('upload/cms/original').'/'.$row->image;
                                    }else{
                                        $url = url('upload/no-image.png');
                                    }
                                ?>

                                <td>
                                    <a href="{{$url}}" class="image-popup" title="{{$row->image}}">
                                        <img src="{{$url}}" class="thumb-img-list" alt="{{$row->image}}" onerror="this.onerror=null;this.src='<?php echo url("upload/no-image.png") ?>';">
                                    </a>
                                </td>


                                <td>{{ $row->page_title }}</td>
                                <td>@if($row->created_at!=null){{ date(setting('date_format'),strtotime($row->created_at)) }}@elseif($row->updated_at!=null){{ date(setting('date_format'),strtotime($row->updated_at))}} @else -- @endif</td>
                               
                                <td class="table-report__action w-56">
                                        

                                    

                                        <div class="flex justify-center items-center">

                                            <a class="flex items-center mr-3 text-theme-3 font-size23" href="{{url('edit-cms-page-translation')}}/{{$layout}}/{{$theme}}/{{$row->id}}"  title="{{__('admin.translate')}}">
                                            <i data-feather="edit-3" class="w-4 h-4 mr-1"></i>
                                            <small class="font-size15 ml-2">{{__('admin.translate')}}</small>
                                        </a>

                                            @can('cms-pages-edit')
                                                <a class="flex items-center mr-3" href="{{url('edit-cms-page')}}/{{$layout}}/{{$theme}}/{{$row->id}}"  title="{{__('admin.edit')}}">
                                                    <i data-feather="check-square" class="w-4 h-4 mr-1"></i> {{__('admin.edit')}}
                                                </a>
                                            @endcan
                                        </div>

                                    
                             
                                </td>
                            </tr>
                            <?php $i++; ?>
                        @endforeach
                    @else
                        <tr class="intro-x text-center text-danger">
                            <td class="w-40" colspan="5">
                                {{__('admin.no_record_found')}}
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
       
    </div>

@endsection