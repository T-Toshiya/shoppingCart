@forelse ($items as $item)
    <div id="product_{{ $item->ASIN }}">
        <li>
            <div class="productContainer"> 

                <div id="productImage_{{ $item->ASIN }}" class="product productImage">
                    <img src="{{ $item->SmallImage->URL }}" height="100" width="100">
                </div>
                <div id="productName_{{ $item->ASIN }}" class="product productName">
                {{ $item->ItemAttributes->Title }}<br><br>
                {{ $item->ItemAttributes->Author }}
                </div>
                <div id="productPrice_{{ $item->ASIN }}" class="product productPrice">{{ $item->OfferSummary->LowestNewPrice->FormattedPrice }}</div>
            </div>

            <div class="insertCart">
                
                数量：<select name="num" id="productNum_{{ $item->ASIN }}" class="productNum">
                @for ($i = 1; $i < 11; $i++)
                @if ($i == 1)
                <option value="{{ $i }}" selected>{{ $i }}</option>
                @else
                <option value="{{ $i }}">{{ $i }}</option>
                @endif
                @endfor  
                </select>
                
                <input type="text" value="{{ $item->ASIN }}" style="display:none;">
                @if (Auth::guest())
                {{--<button type="submit" class="insertCartBtn"><a href="{{ url('/login') }}">カートに入れる</a></button>--}}
                <button type="submit" class="insertCartBtnNonAccount" onclick="location.href='{{ url('/login') }}'">カートに入れる</button>
                @else
                <button type="submit" id="insertCartBtn_{{ $item->ASIN }}" class="insertAmazonCartBtn">カートに入れる</button>
                @endif
                
            </div>
        </li>

    </div>

    <hr>
    @empty
    <li>No products yet</li>
@endforelse