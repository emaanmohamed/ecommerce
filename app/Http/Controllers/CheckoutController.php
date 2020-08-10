<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use Cartalyst\Stripe\Exception\CardErrorException;
use Cartalyst\Stripe\Laravel\Facades\Stripe;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Http\Request;

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

        $contents = Cart::content()->map(function ($item){
           return $item->model->slug. ',' .$item->qty;
        })->values()->toJson();

       try {
           $charge = Stripe::charges()->create([
               'amount' => $this->getNumbers()->get('newTotal') / 100,
               'currency' => 'CAD',
               'source' => 'tok_visa',
               'description' => 'Order',
               'receipt_email' => $request->email,
               'metadata' => [
                   'contents' => $contents,
                   'quantity' => Cart::instance('default')->count(),
                   'discount' => collect(session()->get('coupon'))->toJson(),
               ],
           ]);

           Cart::instance('default')->destroy();
           session()->forget('coupon');

           return redirect()->route('confirmation.index')->with('success_message', 'Thank you! Your payment has been successfully accepted!');
       } catch (CardErrorException $e) {
           return back()->withErrors(['Error!'. $e->getMessage()]);
       }
    }

    private function getNumbers()
    {
        $tax = config('cart.tax') /100;
        $discount = session()->get('coupon')['discount'] ?? 0;
        $newSubtotal = (Cart::subtotal() - $discount);
        $newTax = $newSubtotal * $tax;
        $newTotal = $newSubtotal * (1 + $tax);

        return collect([
            'tax'         => $tax,
            'discount'    => $discount,
            'newSubtotal' => $newSubtotal,
            'newTax'      => $newTax,
            'newTotal'    => $newTotal,
        ]);
    }











}
