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
    <a href="{{ url('/register') }}" class="authLink">Register</a>
</div>
@endif
<ul>
    <li class="menu" id="currentMenu" style="display:none"><a href="javascript:void(0)" id="products"></a></li>
</ul>
<hr>
<div id="userDisp" data-lastpage="{{$products->lastPage()}}">
@include('users.productList')
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

<div id="userDisp" data-lastpage="{{$products->lastPage()}}">
@include('users.productList')
</div>
</div>
@endif
@endsection