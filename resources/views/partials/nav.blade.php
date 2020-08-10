<header>
    <div class="top-nav container">
        <div class="logo"><a href="{{ route('landing-page') }}">Ecommerce</a></div>
        @if (! request()->is('checkout'))
{{--            {{ menu('main', 'partials.menus.main') }}--}}

            <ul>
                <li><a href="{{ route('shop.index') }}">Shop</a></li>
                <li><a href="#">About</a></li>
                <li><a href="#">Blog</a></li>
            </ul>
        @endif

        <div class="top-nav-right">
            @if(! request()->is('checkout'))

                <ul>
                    <li><a href="{{ route('register') }}">Sign Up</a></li>
                    <li><a href="{{ route('login') }}">Login</a></li>
                    <li><a href="{{ route('cart.index') }}">Cart
                            <span class="cart-count"><span>{{ Cart::instance('default')->count() }}</span></span>

                        </a></li>
                </ul>
            @endif
        </div>
    </div> <!-- end top-nav -->
</header>
