@extends('../layout/base')

@section('body')
    <body class="app">
        @yield('content')
        @include('../layout/components/dark-mode-switcher')
        <script src="{{ asset('dist/js/app.js') }}"></script>
        <script src="{{asset('js/jquery-v3.6.0.js')}}"></script>
        <script src="{{ asset('dist/js/tagsinput.js') }}"></script>
        <script src="{{ asset('js/admin.js') }}"></script>
        <script type="text/javascript" src="{{ url('plugin/toastr/toastr.min.js')}}"></script>
        <script src="{{ url('plugin/magnific-popup/dist/jquery.magnific-popup.min.js') }}"></script>
        <script src="{{asset('js/jquery-ui.js')}}"></script>
        <script src="{{ asset('/vendor/translation/js/app.js') }}"></script>
        @yield('script')
    </body>
@endsection