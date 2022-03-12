@extends('../layout/' . $layout)

@section('subhead')
    <title>{{__('admin.edit_role')}} - {{setting('site_name')}}</title>
@endsection

@section('subcontent')
    @include('../layout/components/top-bar')

<link href="{{ asset('dist/css/tagsinput.css') }}" rel="stylesheet" type="text/css">
    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">{{__('admin.edit_role')}}</h2>
    </div>
   {!! Form::model($role, ['method' => 'PATCH','route' => ['roles.update', $role->id]]) !!}
        <div class="grid grid-cols-12 gap-6 mt-5">
            <div class="intro-y col-span-12 lg:col-span-12">            
                <div class="intro-y box p-5">
                    <div class="mt-3">
                        <label>{{__('admin.title')}}</label>
                        {!! Form::text('name', null, array('placeholder' => 'Name','class' => 'input w-full border mt-2')) !!}
                    </div>

                    <div class="mt-3">
                        <label>{{__('admin.permissions')}}</label>
            
                        <div class="intro-y mt-5">
                            @foreach($permission as $value)
                                <label class="cursor-pointer select-none">{{ Form::checkbox('permission[]', $value->id, in_array($value->id, $rolePermissions) ? true : false, array('class' => 'name')) }}
                                {{ $value->name }}</label>
                            <br/>
                            @endforeach

                        </div>
                    </div>

                    <div class="text-right mt-8">
                        <a href="{{url('/sub-admin/')}}/{{$layout}}/{{$theme}}" class="button w-24 border dark:border-dark-5 text-gray-700 dark:text-gray-300 mr-1">{{__('admin.back')}}</a>
                        <button type="submit" id="createBtn" class="button w-24 bg-theme-1 text-white" >{{__('admin.save')}}</button>
                    </div>

                </div>            
            </div>
        </div> 
    {!! Form::close() !!}


@endsection