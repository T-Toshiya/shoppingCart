<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Product;
use App\Cart;
use DB;
use Illuminate\Support\Facades\Auth;

class CartsController extends Controller
{
    //
    //カート初期画面
    public function index() {
        $carts = Cart::where('userName', '=', Auth::user()->name)->get();
        
        return view('carts.index', ['carts' => $carts]);
    }
}
