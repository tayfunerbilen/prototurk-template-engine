<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    @yield('meta')
    @yield('style')
</head>
<body>

<aside class="sidebar">
    @yield('sidebar')
</aside>

<main class="content">

    @page('contact')
        <h6>Şu an iletişim sayfasındasınız!</h6>
    @endpage

    @yield('content')
</main>

@yield('script')

</body>
</html>