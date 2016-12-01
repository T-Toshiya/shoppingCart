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
                    購入日:@orderTime($product)
                </div>
            </div>
        </li>
    </div>

    @empty
    <li>No products yet</li>
    @endforelse
</ul>