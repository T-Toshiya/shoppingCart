@extends('layouts.default')

@section('title')
shoppingCart
@endsection

@section('content')

<h1>ショッピングカート</h1>

@if (Auth::guest()) 
<!--非ログインユーザーの場合-->

@if (Route::has('login'))
<div class="top-right links">
    <a href="{{ url('/login') }}" class="authLink">Login</a>
    <a href="{{ url('/register') }}" class="authLink">Register</a><br>
    <a href="{{ url('/social/twitter') }}"><img src="images/sign-in-with-twitter.png"></a>
    <a href="{{ url('/social/facebook') }}"><img src="images/login_facebook.png"></a>
</div>
@endif
<ul>
    <li class="menu" id="currentMenu" style="display:none"><div id="products"></div></li>
</ul>

<div id="userMenu">
    <div id="searchContents" style="display: block;">
        <input type="text" id="searchText" class="searchProduct" placeholder="タイトルで検索">
        <input type="submit" id="searchBtn" value="商品検索">
    </div>
</div>

<hr>
<div class="lastPage" data-lastpage=6>
<div id="userDisp">
<ul id="productList" class="productList" style="list-style:none;">
@include('users.amazon')
</ul>
</div>
</div>
@else
<!--ログインユーザーの場合-->

@if (Route::has('login'))
<div class="top-right links">
    <a href="{{ url('/logout') }}" class="logoutLink">Logout</a>
</div>
@endif
<div id="userContents">
<p>ユーザー名: {{ Auth::user()->name }}</p>
<div id="userMenu">
    <div id="searchContents" style="display: block;">
    <input type="text" id="searchText" class="searchProduct" placeholder="タイトルで検索">
    <input type="submit" id="searchBtn" value="商品検索">
    </div>
    <ul>
    <li class="menu" id="currentMenu">
        <div id="products"><a href="javascript:void(0)">商品一覧</a></div>
    </li>
    <li class="menu">
    @if ($totalNum == 0)
        <div id="cart">カート({{ $totalNum }}点)</div>
    @else
        <div id="cart"><a href="javascript:void(0)">カート({{ $totalNum }}点)</a></div>
    @endif
    </li>
    <li class="menu">
        <div id="orderHistory"><a href="javascript:void(0)">注文履歴</a></div>
    </li>
    <button id="deleteOrderHistory" type="submit" onclick="deleteOrderHistory()" style="display: none;">履歴削除</button>
    </ul>
</div>

<hr>
<div class="lastPage" data-lastpage=6>
<div id="userDisp">
<ul id="productList" class="productList" style="list-style:none;">
@include('users.amazon')
</ul>
</div>
</div>
</div>
@endif
@endsection