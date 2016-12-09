<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8" name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('title')</title>
        <link href="css/styles.css" rel="stylesheet" type="text/css">
        <link href="/css/app.css" rel="stylesheet">
    </head>
    <body>
       @header()
       <div class="container">
           @yield('content')
       </div>
       <div class="loading"></div>
       <p id="pageTop"><a href="#">PAGE TOP</a></p>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script type="text/javascript" src="js/script.js"></script>
    <script type="text/javascript" src="js/jquery.bottom-1.0.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.1/js/bootstrap.min.js"></script>
    </body>
</html>