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

@else

<!--ログインユーザーの場合-->

@if (Route::has('login'))
<div class="top-right links">
    <a href="{{ url('/logout') }}" class="logoutLink">Logout</a>
</div>
@endif
<p>ユーザー名: {{ Auth::user()->name }}</p>
<p>
<input type="text" id="productSearch" placeholder="商品検索">
<input type="submit" value="検索">
<a href="{{ url('/cart') }}" id="cart">カート({{ $totalNum }}点)</a>
<a href="{{ url('/orderHistory') }}" id="orderHistory">注文履歴</a>
</p>

<hr>

<ul id="productList" class="productList" style="list-style:none;">
@forelse ($products as $product)
<div id="product_{{ $product->id }}">
<li>
<div class="productContainer"> 

<div class="product productImage">
    <img src="images/{{ $product->imagePath }}" height="100" width="100">
</div>
<div class="product productName">
    {{ $product->productName }}
</div>
<div class="product productPrice">
    ¥{{ $product->productPrice }}
</div>
</div>

<div class="insertCart">

数量：<select name="num" id="productNum_{{ $product->id }}" class="productNum">
    <option value="1" selected>1</option>
    <option value="2">2</option>
    <option value="3">3</option>
    <option value="4">4</option>
</select>

<button type="submit" id="insertCartBtn_{{ $product->id }}" class="insertCartBtn" onclick="insertCart({{ $product->id }}, '{{ Auth::user()->name }}')">カートに入れる</button>
</div>
</li>

</div>

<hr>
@empty
<li>No products yet</li>
@endforelse
</ul>

@endif
@endsection