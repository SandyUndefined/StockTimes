
@extends('../layout/' . $layout)

@section('subhead')
    <title>{{__('admin.languages_list')}} - {{setting('site_name')}}</title>
@endsection

@section('subcontent')
    @include('../layout/components/top-bar')


    <h2 class="intro-y text-lg font-medium mt-10">{{__('admin.languages_list')}}</h2>
    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12 flex flex-wrap sm:flex-no-wrap items-center mt-2">

                <a href="{{ url('languages/language/create') }}?layout={{$layout}}&theme={{$theme}}" class="button text-white bg-theme-1 shadow-md mr-2">{{__('admin.add_language')}}</a>

                <a href="{{ url('languages/translations') }}?layout={{$layout}}&theme={{$theme}}" class="button text-white bg-theme-1 shadow-md mr-2">{{__('admin.manage_translations')}}</a>

            <div class="hidden md:block mx-auto text-gray-600"> </div>            
            
        </div>
        <div class="intro-y col-span-12 overflow-auto lg:overflow-visible table-manage">
            <table class="table table-report -mt-2">
                <thead>
                    <tr>
                        <th class="whitespace-no-wrap">{{__('admin.language')}}</th>
                        <th class="whitespace-no-wrap">{{__('admin.iso_code')}}</th>
                    </tr>
                </thead>
            
                <tbody>
                    @foreach ($languages as $language => $name)
                        <tr class="intro-x" >
                           
                        
                            <td>{{ $name }}</td>
                            <td>
                                <a href="{{ route('languages.translations.index', $language) }}">
                                    {{ $language }}
                                </a>
                            </td>
                            
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection