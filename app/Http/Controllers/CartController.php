<?php

namespace App\Http\Controllers;

use App\Product;
use http\Env\Response;
use Illuminate\Http\Request;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Support\Facades\Validator;
use function foo\func;

class CartController extends Controller
{
    public function index()
    {
        $mightAlsoLike = Product::MightAlsoLike()->get();
        return view('cart', compact('mightAlsoLike'));
    }

    public function store(Request $request)
    {
        $duplicates = Cart::search( function ($cartItem, $rowId) use ($request) {
           return $cartItem->id === $request->id;
        });

        if ($duplicates->isNotEmpty()) {
            return redirect()->route('cart.index')->with('success_message', 'Item is already in your cart!');
        }

        Cart::add($request->id, $request->name, 1, $request->price)
            ->associate('App\Product');
        return redirect()->route('cart.index')->with('success_message', 'Item was added to your cart');
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|numeric|between:1,5'
        ]);

        if ($validator->fails()) {
            session()->flash('errors', collect(['Quantity must be between 1 and 5.']));
            return response()->json(['success' => false], 400);
        }
        Cart::update($id, $request->quantity);
        session()->flash('success_message','Quantity was updated successfully!');
        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
       Cart::remove($id);
       return back()->with('success_message', 'Item has been removed!');
    }

    public function switchToSaveForLater($id)
    {
        $item = Cart::get($id);
        Cart::remove($id);
        $duplicates = Cart::instance('saveForLater')->search(function ($cartItem, $rowId) use ($id) {
            return $rowId === $id;
        });

        if ($duplicates->isNotEmpty()) {
            return redirect()->route('cart.index')->with('success_message', 'Item is already Saved For Later! ');
        }
        Cart::instance('SaveForLater')->add($item->id, $item->name,1, $item->price)
            ->associate('App\Product');
        return redirect()->route('cart.index')->with('success_message', 'Item has saved for later!');
    }
}