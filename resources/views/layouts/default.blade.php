<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8" name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('title')</title>
        <link href="css/styles.css" rel="stylesheet" type="text/css">
        <link href="/css/app.css" rel="stylesheet">
    </head>
    <body>
    <div id="fb-root"></div>
    <script>(function(d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) return;
        js = d.createElement(s); js.id = id;
        js.src = "//connect.facebook.net/ja_JP/sdk.js#xfbml=1&version=v2.8&appId=1203789486382619";
        fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));</script>
       @header()
       <div class="container">
           @yield('content')
       </div>
        <p id="pageTop" style="position:fixed;right:5px;bottom:5px"><a href="#">PAGE TOP</a></p>
       <div class="loading"></div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script type="text/javascript" src="js/script.js"></script>
    <script type="text/javascript" src="js/jquery.bottom-1.0.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.1/js/bootstrap.min.js"></script>
    </body>
</html>