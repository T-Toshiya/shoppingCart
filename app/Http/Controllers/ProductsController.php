<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Product;
use App\Cart;
use App\OrderHistory;
use DB;
use Illuminate\Support\Facades\Auth;

class ProductsController extends Controller
{
    //
    //初期画面
    public function index() {
        //商品一覧の取得
        $products = Product::all();
        
        //カートに入っている商品数の取得
        $productsInCart = Cart::where('userName', '=', Auth::user()->name)->get();
        $totalNum = 0;
        foreach ($productsInCart as $product) {
            $totalNum += $product->productNum;
        }
        
        return view('users.index', ['products' => $products, 'totalNum' => $totalNum]);
    }
    
    //カート初期画面　ページを変えないほうがいい？
    public function cart() {
        $products = Cart::where('userName', '=', Auth::user()->name)->get();
        //合計個数と金額
        $totalNum = 0;
        $totalMoney = 0;
        
        foreach ($products as $product) {
            $totalNum += $product->productNum;
            $totalMoney += $product->productPrice * $product->productNum;
        }
        
        return view('users.cart', ['products' => $products, 'totalNum' => $totalNum, 'totalMoney' => $totalMoney]);
    }
    
    //カートに商品を入れる
    public function insertCart(Request $request) {
        //商品情報を取得
        $product = Product::findOrFail($request->productId);
        
        $cart = Cart::where('userName', '=', $request->userName)->where('productId', '=', $request->productId)->get();
        
        if (count($cart) !== 0) {
            $postNum = $cart[0]->productNum + $request->selectedNum;
            $cart[0]->productNum = $postNum;
            $cart[0]->save();
        } else {
            //商品情報およびユーザー情報をカートに保存
            $newCart = new Cart();
            $newCart->userName = $request->userName;
            $newCart->productId = $request->productId;
            $newCart->productNum = $request->selectedNum;
            $newCart->productName = $product->productName;
            $newCart->productPrice = $product->productPrice;
            $newCart->imagePath = $product->imagePath;
            $newCart->save();
        }
        
        $productsInCart = Cart::where('userName', '=', Auth::user()->name)->get();
        $totalNum = 0;
        foreach ($productsInCart as $product) {
            $totalNum += $product->productNum;
        }
        
        return $totalNum;
    }
    
    //カートから商品を削除
    public function destroy(Request $request) {
        $deleteProduct = Cart::findOrFail($request->deleteId);
        $deleteProduct->delete();
        
        $productsInCart = Cart::where('userName', '=', Auth::user()->name)->get();
        $totalNum = 0;
        foreach ($productsInCart as $product) {
            $totalNum += $product->productNum;
        }
        return $totalNum;
    }
    
    //決済処理
    public function confirm(Request $request) {
        $orderProducts = Cart::where('userName', '=', Auth::user()->name)->get();
        
        foreach ($orderProducts as $orderProduct) {
            $orderHistory = new OrderHistory();
            $orderHistory->userName = $orderProduct->userName;
            $orderHistory->productName = $orderProduct->productName;
            $orderHistory->totalNum = $orderProduct->productNum;
            //totalPriceに変更する
            $orderHistory->totalMoney = $orderProduct->productPrice * $orderProduct->productNum;
            $orderHistory->imagePath = $orderProduct->imagePath;
            $orderHistory->save();
            $orderProduct->delete();
        }
        
        
        //商品一覧の取得
        $products = Product::all();
        
        return view('users.index', ['products' => $products, 'totalNum' => 0]);
    }
    
    //買い物履歴　ページを変えないようにしたほうがいい？
    public function showOrderHistory() {
        $orderHistory = OrderHistory::where('userName', '=', Auth::user()->name)->orderBy('created_at', 'desc')->get();
        
        return view('users.orderHistory', ['products' => $orderHistory]);
    }
}
