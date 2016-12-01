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
<hr>
<div id="userDisp">
@include('users.productList')
</div>
@else
<!--ログインユーザーの場合-->

@if (Route::has('login'))
<div class="top-right links">
    <a href="{{ url('/logout') }}" class="logoutLink">Logout</a>
</div>
@endif
<p>ユーザー名: {{ Auth::user()->name }}</p>
<div id="userMenu">
    <input type="text" id="searchText" placeholder="商品検索">
    <input type="submit" id="searchBtn" value="検索">
    <ul>
    {{--<li class="menu" id="currentMenu"><a href="javascript:void(0)" id="products" onclick="showProducts()">商品一覧</a></li>
    <li class="menu"><a href="javascript:void(0)" id="cart" onclick="showCart()">カート({{ $totalNum }}点)</a></li>
    <li class="menu"><a href="javascript:void(0)" id="orderHistory" onclick="showOrderHistory()">注文履歴</a></li>--}}
    <li class="menu" id="currentMenu"><a href="javascript:void(0)" id="products">商品一覧</a></li>
    <li class="menu"><a href="javascript:void(0)" id="cart">カート({{ $totalNum }}点)</a></li>
    <li class="menu"><a href="javascript:void(0)" id="orderHistory">注文履歴</a></li>
    </ul>
</div>
<hr>
<div id="userDisp" data-lastpage="{{$products->lastPage()}}">
@include('users.productList')
</div>
@endif
@endsection