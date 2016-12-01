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
    数量:{{ $product->productNum }}<br>
    ¥{{ $product->productPrice * $product->productNum }}
</div>
<div class="delete">
    <a href="javascript:void(0)" onclick="destroy({{ $product->id }})">[削除]</a>
</div>
</div>
</li>
</div>

@empty
<li>No products yet</li>
@endforelse
</ul>

<div class="total">
    <p>小計({{ $totalNum }}点)：¥{{ $totalPrice }}</p>
    <p>
        <button type="submit" onclick="orderConfirm()">注文を確定する</button>
    </p>
</div>