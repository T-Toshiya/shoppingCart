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
                    ¥{{ number_format($product->productPrice) }}
                </div>
            </div>

            <div class="insertCart">
                
                数量：<select name="num" id="productNum_{{ $product->id }}" class="productNum">
                @for ($i = 1; $i < 11; $i++)
                @if ($i == 1)
                <option value="{{ $i }}" selected>{{ $i }}</option>
                @else
                <option value="{{ $i }}">{{ $i }}</option>
                @endif
                @endfor  
                </select>
                
                <input type="text" value="{{ $product->id }}" style="display:none;">
                @if (Auth::guest())
                {{--<button type="submit" class="insertCartBtn"><a href="{{ url('/login') }}">カートに入れる</a></button>--}}
                <button type="submit" class="insertCartBtnNonAccount" onclick="location.href='{{ url('/login') }}'">カートに入れる</button>
                @else
                <button type="submit" id="insertCartBtn_{{ $product->id }}" class="insertCartBtn">カートに入れる</button>
                @endif
            </div>
        </li>

    </div>

    <hr>
    @empty
    <li>No products yet</li>
    @endforelse
</ul>