<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Mail\OrderPlaced;
use App\Order;
use App\OrderProduct;
use Cartalyst\Stripe\Exception\CardErrorException;
use Cartalyst\Stripe\Laravel\Facades\Stripe;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class CheckoutController extends Controller
{
    public function index()
    {

        if (Cart::instance('default')->count() == 0) {
            return redirect()->route('shop.index');
        }

        if (auth()->user() && request()->is('guestCheckout')) {
            return redirect()->route('checkout.index');
        }

        return view('checkout')->with([
            'discount'    => $this->getNumbers()->get('discount'),
            'newSubtotal' => $this->getNumbers()->get('newSubtotal'),
            'newTax'      => $this->getNumbers()->get('newTax'),
            'newTotal'    => $this->getNumbers()->get('newTotal'),
        ]);
    }

    public function store(CheckoutRequest $request)
    {

        $contents = Cart::content()->map(function ($item) {
            return $item->model->slug.','.$item->qty;
        })->values()->toJson();

        try {
            $charge = Stripe::charges()->create([
                'amount'        => $this->getNumbers()->get('newTotal') / 100,
                'currency'      => 'CAD',
                'source'        => 'tok_visa',
                'description'   => 'Order',
                'receipt_email' => $request->email,
                'metadata'      => [
                    'contents' => $contents,
                    'quantity' => Cart::instance('default')->count(),
                    'discount' => collect(session()->get('coupon'))->toJson(),
                ],
            ]);

            $order = $this->addToOrdersTable($request, null);
            Mail::send(new OrderPlaced($order));

            Cart::instance('default')->destroy();
            session()->forget('coupon');

            return redirect()->route('confirmation.index')->with('success_message',
                'Thank you! Your payment has been successfully accepted!');
        } catch (CardErrorException $e) {
            $this->addToOrdersTable($request, $e->getMessage());
            return back()->withErrors(['Error!'.$e->getMessage()]);
        }
    }

    protected function addToOrdersTable($request, $error)
    {
        // Insert into orders table

        $order = Order::create([
            'user_id'               => auth()->user() ? auth()->user()->id : null,
            'billing_email'         => $request->email,
            'billing_name'          => $request->name,
            'billing_address'       => $request->address,
            'billing_city'          => $request->city,
            'billing_province'      => $request->province,
            'billing_postalcode'    => $request->postalcode,
            'billing_phone'         => $request->phone,
            'billing_name_on_card'  => $request->name_on_card,
            'billing_discount'      => $this->getNumbers()->get('discount'),
            'billing_discount_code' => $this->getNumbers()->get('code'),
            'billing_subtotal'      => $this->getNumbers()->get('newSubtotal'),
            'billing_tax'           => $this->getNumbers()->get('newTax'),
            'billing_total'         => $this->getNumbers()->get('newTotal'),
            'error'                 => $error,
        ]);


        // Insert into order_product table

        foreach (Cart::content() as $item) {
            OrderProduct::create([
                'order_id'   => $order->id,
                'product_id' => $item->model->id,
                'quantity'   => $item->qty
            ]);

        }

        return $order;

    }

    private function getNumbers()
    {
        $tax         = config('cart.tax') / 100;
        $discount    = session()->get('coupon')['discount'] ?? 0;
        $newSubtotal = (Cart::subtotal() - $discount);
        $newTax      = $newSubtotal * $tax;
        $newTotal    = $newSubtotal * (1 + $tax);

        return collect([
            'tax'         => $tax,
            'discount'    => $discount,
            'newSubtotal' => $newSubtotal,
            'newTax'      => $newTax,
            'newTotal'    => $newTotal,
        ]);
    }


}
