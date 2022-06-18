<!doctype html>
<html lang="{{ htmlLang() }}" @langrtl dir="rtl" @endlangrtl>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ appName() }} - @yield('title')</title>
    <meta name="description" content="@yield('meta_description', appName())">
    <meta name="author" content="@yield('meta_author', 'Anthony Rappa')">
    <link rel="stylesheet" href="{{ asset('css/fonts.css') }}">
    @yield('meta')

    @stack('before-styles')
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
    <link href="{{ mix('css/frontend.css') }}" rel="stylesheet">
    <livewire:styles />
    @stack('after-styles')

    @include('includes.partials.ga')
</head>
<body class="c-app">
    @if ($logged_in_user)
        @include('frontend.includes.sidebar')
        <input type="hidden" id="is_download_draft" value="{{ $logged_in_user->is_download_draft }}" />
        <input type="hidden" id="is_download_project" value="{{ $logged_in_user->is_download_project }}" />
    @endif
    
    <div id="app" class="c-wrapper c-fixed-components">
        @include('frontend.includes.header')
        @include('includes.partials.read-only')
        @include('includes.partials.logged-in-as')
        @include('includes.partials.announcements')

        <div id="app" class="c-body">
            <main class="c-main">
                <div class="container-fluid">
                    @include('includes.partials.messages')
                    @if (Route::is('frontend.banner.group.*'))
                        @include('backend.includes.partials.breadcrumbs')
                    @endif
                    @yield('content')
                </div>
            </main>
        </div><!--app-->
    </div>

    @yield('modals')

    @stack('before-scripts')
    <script src="{{ mix('js/manifest.js') }}"></script>
    <script src="{{ mix('js/vendor.js') }}"></script>
    <script src="{{ mix('js/frontend.js') }}"></script>
    <livewire:scripts />
    @stack('after-scripts')
</body>
</html>
