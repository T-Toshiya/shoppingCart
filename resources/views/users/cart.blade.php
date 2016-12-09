<ul id="productList" class="productList" style="list-style:none;">
@forelse ($products as $product)
<div id="cart_{{ $product->id }}">
<li>
<div class="productContainer"> 

<div class="cart productImage">
    {{--<img src="images/{{ $product->imagePath }}" height="100" width="100">--}}
    <img src="{{ $product->imagePath }}" height="100" width="100">
</div>
<div class="cart productName">
    {{ $product->productName }}
</div>
<div class="cart productPrice">
    数量：<select name="num" id="cartProductNum_{{ $product->id }}" class="cartProductNum" onchange="changeCart({{ $product->id }}, {{ $product->productId }})">
    
    @for ($i = 1; $i < 11; $i++)
    @if ($i == $product->productNum)
    <option value="{{ $i }}" selected>{{ $i }}</option>
    @else
    <option value="{{ $i }}">{{ $i }}</option>
    @endif
    @endfor
    
    </select>
    <div id="price_{{ $product->id }}">¥{{ number_format($product->productPrice * $product->productNum) }}</div>
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

<div class="totalDisp">
    <p id="total">小計({{ $totalNum }}点)：¥{{ number_format($totalPrice) }}</p>
    <p>
        <button id="orderConfirm" type="submit">注文を確定する</button>
    </p>
</div>