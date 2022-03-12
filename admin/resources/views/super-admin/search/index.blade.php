@extends('../layout/' . $layout)

@section('subhead')
    <title>{{__('admin.search_log_list')}} - {{setting('site_name')}}</title>
@endsection

@section('subcontent')
    @include('../layout/components/top-bar')

    <h2 class="intro-y text-lg font-medium mt-10">{{__('admin.search_log_list')}}</h2>
    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12 flex flex-wrap sm:flex-no-wrap items-center mt-2">
            <div class="hidden md:block mx-auto text-gray-600">  </div>
            <div class="w-full sm:w-auto mt-3 sm:mt-0 sm:ml-auto md:ml-0">
                <div class="w-56 relative text-gray-700 dark:text-gray-300">
                    <form method="GET">
                        <input type="text" class="input w-56 box pr-10 placeholder-theme-13" @if(isset($_GET['search_keyword'])) value="{{$_GET['search_keyword']}}" @endif name="search_keyword" placeholder="{{__('admin.search_by_keyword')}}">
                        <a href="javascript:;" onclick="searchClick();"><i class="w-4 h-4 absolute my-auto inset-y-0 mr-3 right-0" data-feather="search"></i></a> 
                        <input type="submit" id="search" class="hide" name="search">
                    </form>
                </div>
            </div>
            <button class="button ml-2 mt-2 mr-1 mb-2 border text-gray-700 dark:bg-dark-5 dark:text-gray-300 bg_white" onclick="resetFilter()">{{__('admin.reset')}}</button>

        </div>
        <div class="intro-y col-span-12 overflow-auto lg:overflow-visible table-manage">
            <table class="table table-report -mt-2">
                <thead>
                    <tr>
                        <th class="whitespace-no-wrap">{{__('admin.id')}}</th>
                        <th class="whitespace-no-wrap">{{__('admin.search_keyword')}}</th>
                        <th class="whitespace-no-wrap">{{__('admin.search_count')}}</th>
                        <th class="whitespace-no-wrap">{{__('admin.created_at')}}</th>
                    </tr>
                </thead>
               
                <tbody>
                    @if(count($search))
                        <?php $i=1; ?>
                        @foreach ($search as $row)
                            <tr class="intro-x">
                                <td class="w-40">
                                    {{$i}}
                                </td>
                                <td>{{ $row->search_keyword }}</td>
                                <td>{{ $row->search_count }}</td>
                                <td>@if($row->created_at!=null){{ date(setting('date_format'),strtotime($row->created_at)) }}@else -- @endif</td>
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
                {!! $search->appends(request()->except('page'))->render() !!}
            </ul>
        </div>
        <div class="intro-y col-span-1 flex flex-wrap sm:flex-row sm:flex-no-wrap items-center">
            
        </div>
        <div class="intro-y col-span-3 sm:flex-row sm:flex-no-wrap items-right">

             <p class="text-right"><?php if ($search->firstItem() != null) { ?> {{__('admin.showing')}} {{ $search->firstItem() }} {{__('admin.to')}} {{ $search->lastItem() }} {{__('admin.of')}} {{ $search->total() }} {{__('admin.entries')}} <?php }?></p>

        </div>
    </div>

@endsection