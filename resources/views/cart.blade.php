@extends('layout')

@section('title', 'Shopping Cart')

@section('extra-css')

@endsection

@section('content')

    @component('components.breadcrumbs')
            <a href="#">Home</a>
            <i class="fa fa-chevron-right breadcrumb-separator"></i>
            <span>Shopping Cart</span>
    @endcomponent
    <div class="cart-section container">
        <div>
            @if (session()->has('success_message'))
                <div class="alert alert-success">
                    {{ session()->get('success_message') }}
                </div>
            @endif
            @if(count($errors) > 0)
                <div class="alert alert-danger" style="margin-top: 20px">
                    <ul>
                        @foreach($errors->all() as $error )
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (Cart::count() > 0)

            <h2>{{ Cart::count() }} item(s) in Shopping Cart</h2>

            <div class="cart-table">
                @foreach(Cart::content() as $item)
                <div class="cart-table-row">
                    <div class="cart-table-row-left">
                        <a href="{{ route('shop.show', $item->model->slug) }}"><img src="{{ asset('img/products/'.$item->model->slug.'.jpg')  }}" alt="item"
                                         class="cart-table-img"></a>
                        <div class="cart-item-details">
                            <div class="cart-table-item"><a href="{{ route('shop.show', $item->model->slug) }}">{{ $item->model->name }}</a></div>
                            <div class="cart-table-description">{{ $item->model->details }}</div>
                        </div>
                    </div>
                    <div class="cart-table-row-right">
                        <div class="cart-table-actions">
                            <form action="{{ route('cart.destroy', $item->rowId) }}" method="POST">
                                {{ csrf_field() }}
                                {{ method_field('DELETE') }}
                                <button type="submit" class="cart-options">Remove</button>
                            </form>
                            <form action="{{ route('cart.switchToSaveForLater', $item->rowId) }}" method="POST">
                                {{ csrf_field() }}
                                <button type="submit" class="cart-options">Save for later</button>
                            </form>
                        </div>
                        <div>
                            <select class="quantity" data-id="{{ $item->rowId }}">
                                @for ($i= 1; $i <= 5 ; $i ++)
                                    <option {{ $item->qty == $i ? 'selected' : '' }}>{{ $i }}</option>
                                    @endfor
{{--                                <option {{ $item->qty == 1 ? 'selected' : '' }}>1</option>--}}
{{--                                <option {{ $item->qty == 2 ? 'selected' : '' }}>2</option>--}}
{{--                                <option {{ $item->qty == 3 ? 'selected' : '' }}>3</option>--}}
{{--                                <option {{ $item->qty == 4 ? 'selected' : '' }}>4</option>--}}
{{--                                <option {{ $item->qty == 5 ? 'selected' : '' }}>5</option>--}}
                            </select>
                        </div>
                        <div>{{ presentPrice($item->subtotal) }}</div>
                    </div>
                </div> <!-- end cart-table-row -->
                @endforeach
            <!-- end cart-table-row -->

            </div> <!-- end cart-table -->

                @if (! session()->has('coupon'))

            <a href="#" class="have-code">Have a Code?</a>

            <div class="have-code-container">
                <form action="{{ route('coupon.store') }}" method="POST">
                    {{ csrf_field() }}
                    <input type="text" name="coupon_code" id="coupon_code">
                    <input type="submit" class="button" value="Apply">
                </form>
            </div> <!-- end have-code-container -->
                    @endif

            <div class="cart-totals">
                <div class="cart-totals-left">
                    Shipping is free because we’re awesome like that. Also because that’s additional stuff I don’t feel
                    like figuring out :).
                </div>

                <div class="cart-totals-right">
                    <div>
                        Subtotal <br>
                        Discount ({{ (session()->get('coupon')['name']) ? session()->get('coupon')['name'] : ' ' }}) <br>
                        Tax (13%)<br>
                        <span class="cart-totals-total">Total</span>
                    </div>
                    <div class="cart-totals-subtotal">
                        {{ PresentPrice(Cart::subtotal())  }} <br>
                        {{session()->get('coupon')['name'] }} <br>
                        {{ PresentPrice(Cart::tax()) }} <br>
                        <span class="cart-totals-total">{{ PresentPrice(Cart::total()) }}</span>
                    </div>
                </div>
            </div> <!-- end cart-totals -->

            <div class="cart-buttons">
                <a href="{{ route('shop.index') }}" class="button">Continue Shopping</a>
                <a href="{{ route('checkout.index') }}" class="button-primary">Proceed to Checkout</a>
            </div>
                @else
                    <h3>No items in Cart</h3>
                <div class="spacer"></div>
                <a href="{{ route('shop.index') }}" class="button"> Continue Shopping</a>
                    <div class="spacer"></div>
                    @endif

                @if (Cart::instance('SaveForLater')->count() > 0)

            <h2> {{ Cart::instance('SaveForLater')->count()  }} item(s) Saved For Later</h2>

            <div class="saved-for-later cart-table">
                @foreach(Cart::instance('SaveForLater')->content() as $item)
                <div class="cart-table-row">
                    <div class="cart-table-row-left">
                        <a href="{{ route('shop.show', $item->model->slug) }}"><img src="{{ asset('storage/'.$item->model->image)  }}" alt="item" class="cart-table-img"></a>
                        <div class="cart-item-details">
                            <div class="cart-table-item"><a href="{{ route('shop.show', $item->model->slug) }}">{{ $item->model->name }}</a></div>
                            <div class="cart-table-description">{{ $item->model->details }}</div>
                        </div>
                    </div>
                    <div class="cart-table-row-right">
                        <div class="cart-table-actions">
                            <form action="{{ route('SaveForLater.destroy', $item->rowId) }}" method="Post">
                                {{ csrf_field() }}
                                {{ method_field('DELETE') }}
                                <button type="submit" class="cart-options">Remove</button>
                            </form>
                            <form action="{{ route('SaveForLater.switchToCart', $item->rowId) }}" method="POST">
                                {{ csrf_field() }}
                                <button type="submit" class="cart-options">Move to cart</button>
                            </form>
{{--                            <a href="#">Remove</a> <br>--}}
{{--                            <a href="#">Move to cart</a>--}}
                        </div>
                        {{-- <div>
                            <select class="quantity">
                                <option selected="">1</option>
                                <option>2</option>
                                <option>3</option>
                                <option>4</option>
                                <option>5</option>
                            </select>
                        </div> --}}
                        <div>{{ $item->model->PresentPrice() }}</div>
                    </div>
                </div> <!-- end cart-table-row -->
                @endforeach
            </div> <!-- end saved-for-later -->
                @else
                    <h3>You have no items saved for Later. </h3>
                @endif
        </div>

    </div> <!-- end cart-section -->

    @include('partials.might-like')

@endsection

@section('extra-js')
    <script src="{{asset('js/app.js')}}"></script>
    <script>
        (function () {
            const classname = document.querySelectorAll('.quantity');

            Array.from(classname).forEach(function(element) {
                element.addEventListener('change', function() {
                    const id = element.getAttribute('data-id');
                    console.log(id);
                    axios.post('cart/' + id, {
                        quantity: this.value,
                        _method: 'patch'
                        })
                        .then(function (response) {
                            window.location.href = '{{ route('cart.index') }}'
                        })
                        .catch(function (error) {
                            window.location.href = '{{ route('cart.index') }}'
                        });
                })
            })
        })();
    </script>
    @endsection






















