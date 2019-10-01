{{--<ul>--}}

{{--        @foreach($items as $menu_item)--}}
{{--                <li><a href="{{ $menu_item->link() }}">--}}
{{--                                {{ $menu_item->title }}--}}
{{--                                @if ($menu_item->title == 'Cart')--}}
{{--                                        @if (Cart::instance('default')->count() > 0)--}}
{{--                                        <span><span style="display: inline-block; background-color: #FFD94D; line-height: 0; border-radius: 50%; font-size: 14px; color: #212121; ">{{ Cart::instance('default')->count() }}</span></span>--}}
{{--                                        @endif--}}
{{--                                @endif--}}

{{--                        </a>--}}
{{--                </li>--}}
{{--        @endforeach--}}
{{--</ul>--}}


<ul>
    <li><a href="{{ route('shop.index') }}">Shop</a></li>
    <li><a href="#">About</a></li>
    <li><a href="#">Blog</a></li>
    <li><a href="{{ route('cart.index') }}">Cart <span class="cart-count">
                                @if (Cart::instance('default')->count() > 0)
                    <span>{{ Cart::instance('default')->count() }}</span></span>
            @endif
        </a>
    </li>
</ul>