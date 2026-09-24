<!doctype html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <link rel="icon" type="image/png" href="/matabgaleb/assets/fav/favicon-48x48.png" sizes="48x48" />
    <link rel="icon" type="image/svg+xml" href="/matabgaleb/assets/fav/favicon.svg" />
    <link rel="shortcut icon" href="/matabgaleb/assets/fav/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/matabgaleb/assets/fav/apple-touch-icon.png" />
    <link rel="manifest" href="/matabgaleb/assets/fav/site.webmanifest" />

    <link href="{{ asset('matabgaleb/assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <script src="{{ asset('matabgaleb/assets/js/chart.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('matabgaleb/style.css') }}?v=6">
    <meta name="theme-color" content="#137c72">
    <script>if (window.Chart) { Chart.defaults.font.family = 'iran, sans-serif'; Chart.defaults.color = '#6c807d'; }</script>
    <title>
        @yield('header_title', 'پنل مدیریت کودک طب')
    </title>
</head>

<body>

    @if (session('success_login'))
        <style>
            .father {
                display: grid;
                opacity: 1;
                transition: opacity 1s ease-in-out;
            }

            .father.show {
                display: grid;
                opacity: 1;
            }
        </style>

        @if (auth()->user()->email != '09143046229')
            <div class="container-fluid">
                <div class="row justify-content-center align-items-center w-100" style="height: 100vh"
                    id="video-container">
                    <div class="col-lg-10 text-center">
                        <video id="logo-video" autoplay muted playsinline style="width:600px;height:600px"
                            class="mx-auto">
                            <source src="{{ asset('matabgaleb/assets/videos/welcome.mp4') }}" type="video/mp4">
                        </video>

                        <h4 class="text-center text-primary">
                            به پنل مدیریت کودک طب خوش آمدید
                        </h4>
                    </div>
                </div>
            </div>
        @endif

        @if (auth()->user()->email != '09143046229')
            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    var video = document.getElementById("logo-video");
                    function closeWelcome() {
                        document.getElementById('video-container').style.display = 'none';
                    }
                    video.addEventListener('error', closeWelcome);
                    video.addEventListener('click', closeWelcome);
                    setTimeout(closeWelcome, 8000);
                    video.onended = function() {
                        setTimeout(function() {
                            document.getElementById("video-container").style.display = "none";
                            var fatherElement = document.getElementById("father");
                            fatherElement.style.display = "grid";

                            // Add the 'show' class to trigger the fade-in effect
                            setTimeout(function() {
                                fatherElement.classList.add("show");
                            }, 220); // Small delay to ensure 'display' has taken effect before animation
                        }, 1250);
                    };
                });
            </script>
        @endif
    @endif

    @if (session('success_login'))
        @if (auth()->user()->email == '09143046229')
            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    let father = document.getElementById('father');
                    if (father) {
                        father.style.display = "grid";
                        father.classList.add("show");
                    }
                });
            </script>
        @endif
    @endif
