@forelse ($orderDetails as $orderDetail)
<div class="orderNum" style="border-style: solid ; border-width: 1px;">
    <p>購入日：@orderTime($orderDetail)</p>
    @foreach ($orderDetail as $order)
    <div class="orderDetail">
        <li>
            <div class="order"> 
                <div class="orderHistory orderImage">
                    <img src="images/{{ $order->imagePath }}" height="100" width="100">
                </div>
                <div class="orderHistory orderName">
                    {{ $order->productName }}
                </div>
                <div class="orderHistory orderQuantity">
                    数量:{{ $order->orderQuantity }}
                </div>
                <div class="orderHistory orderPrice">
                    ¥{{ number_format($order->productPrice * $order->orderQuantity) }}
                </div>
            </div>
        </li>
    </div>
    @endforeach
</div>
@empty
<li>No products yet</li>
@endforelse
