
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ appName() }}</title>
        <meta name="description" content="@yield('meta_description', appName())">
        <meta name="author" content="@yield('meta_author', 'Anthony Rappa')">
        @yield('meta')

        @stack('before-styles')
        <link rel="dns-prefetch" href="//fonts.gstatic.com">
        <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">
        <link href="{{ mix('css/frontend.css') }}" rel="stylesheet">
        <style>
            html, body {
                background-color: #fff;
                color: #636b6f;
                font-family: 'Nunito', sans-serif;
                font-weight: 200;
                height: 100vh;
                margin: 0;
            }

            .full-height {
                height: 100vh;
            }

            .flex-center {
                align-items: center;
                display: flex;
                justify-content: center;
            }

            .position-ref {
                position: relative;
            }

            .top-right {
                position: absolute;
                right: 10px;
                top: 18px;
            }

            .content {
                text-align: center;
            }

            .title {
                font-size: 84px;
            }

            .links > a {
                color: #636b6f;
                padding: 0 25px;
                font-size: 13px;
                font-weight: 600;
                letter-spacing: .1rem;
                text-decoration: none;
                text-transform: uppercase;
            }

            .m-b-md {
                margin-bottom: 30px;
            }
        </style>
        @stack('after-styles')

        @include('includes.partials.ga')
    </head>
    <body>

        <div id="app" class="flex-center position-ref">

            <div class="content mt-4">
                
                @if (count($images))   
                <div class="w-50 m-auto">
                    @foreach ($images as $image)
                    <img class="w-100 border p-3" src="data: image/jpeg;base64, {{$image}}">
                    @endforeach
        
                    <h3 class="m-b-md text-right">
                    <small class="text-muted">Made with</small> <img src="/img/icons/Itsrapid_logo_tight.png" style="width: 139px">
                    </h3><!--title-->
                </div>
                @else
                     <h1><i clasS="fas fa-exclamation-triangle text-warning"></i></h1>
                     <h3 class="m-b-md">
                        The link is not valid or has been deleted.
                    </h3>
                @endif
                

                </div><!--content-->
        </div><!--app-->

    
    </body>
</html>
