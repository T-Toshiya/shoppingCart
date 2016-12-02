@extends('layouts.default')

@section('title')
shoppingCart
@endsection

@section('content')

<h1>ショッピングカート</h1>

<!--ログインユーザーの場合-->

<div class="top-right links">
    <a href="{{ url('/logout') }}" class="logoutLink">Logout</a>
</div>

<p>ユーザー名: {{ Auth::user()->name }}</p>
<p>
<input type="text" id="productSearch" placeholder="商品検索">
<input type="submit" value="検索">
</p>

<hr>

<ul id="productList" class="productList" style="list-style:none;">
@forelse ($products as $product)
<div id="cart_{{ $product->id }}">
<li>
<div class="productContainer"> 

<div class="cart productImage">
    <img src="images/{{ $product->imagePath }}" height="100" width="100">
</div>
<div class="cart productName">
    {{ $product->productName }}
</div>
<div class="cart productPrice">
    数量:{{ $product->totalNum }}<br>
    ¥{{ $product->totalMoney }}
</div>
<div class="cart productTime">
    購入日:{{ $product->created_at }}
</div>

</div>
</li>
</div>

@empty
<li>No products yet</li>
@endforelse
</ul>

@endsection