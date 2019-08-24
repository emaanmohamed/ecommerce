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
        return view('checkout');
    }

    public function store(CheckoutRequest $request)
    {
        $contents = Cart::content()->map(function ($item){
           return $item->model->slug. ',' .$item->qty;
        })->values()->toJson();
       try {
           $charge = Stripe::charges()->create([
               'amount' => Cart::total() / 100,
               'currency' => 'CAD',
               'source' => 'tok_visa',
               'description' => 'Order',
               'receipt_email' => $request->email,
               'metadata' => [
                   'contents' => $contents,
                   'quantity' => Cart::instance('default')->count(),
               ],
           ]);

           return redirect()->route('confirmation.index')->with('success_message', 'Thank you! Your payment has been successfully accepted!');
       } catch (CardErrorException $e) {
           return back()->withErrors(['Error!'. $e->getMessage()]);
       }
    }
}
