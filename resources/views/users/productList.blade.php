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

                <button type="submit" id="insertCartBtn_{{ $product->id }}" class="insertCartBtn" onclick="insertCart({{ $product->id }})">カートに入れる</button>
            </div>
        </li>

    </div>

    <hr>
    @empty
    <li>No products yet</li>
    @endforelse
</ul>