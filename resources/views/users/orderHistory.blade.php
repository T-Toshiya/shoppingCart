<button type="submit" onclick="deleteOrderHistory()">履歴削除</button>

<ul id="productList" class="productList" style="list-style:none;">
    @count()
    @forelse ($products as $product)
    <div id="cart_{{ $product->id }}">
        <li>
            <div class="productContainer"> 
                @orderGroup($product)
                <div class="cart productImage">
                    <img src="images/{{ $product->imagePath }}" height="100" width="100">
                </div>
                <div class="cart productName">
                    {{ $product->productName }}
                </div>
                <div class="cart productPrice">
                    数量:{{ $product->totalNum }}<br>
                    ¥{{ number_format($product->totalPrice) }}
                </div>
                <div class="cart productTime">
                    購入日:@orderTime($product)
                </div>
            </div>
        </li>
    </div>
    @empty
    <li>No products yet</li>
    @endforelse
</ul>