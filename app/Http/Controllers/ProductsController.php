<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Product;
use App\Cart;
use App\OrderHistory;
use DB;
use Illuminate\Support\Facades\Auth;
//use Illuminate\Http\RedirectResponse

class ProductsController extends Controller
{
    //
    //初期画面
    public function index() {
        //商品一覧の取得
        $products = Product::orderBy('id', 'desc')->paginate(10);
        $count = Product::count();
        
        //カートに入っている商品数の取得
        $totalNum = 0;
        if (! Auth::guest()) {
            $productsInCart = Cart::where('userName', '=', Auth::user()->name)->get();
            $totalNum = 0;
            foreach ($productsInCart as $product) {
                $totalNum += $product->productNum;
            }
        }
        
        return view('users.index', ['products' => $products, 'totalNum' => $totalNum, 'count' => $count]);
    }
    
    //カートに商品を入れる
    public function insertCart(Request $request) {
        if (Auth::guest()) {
            //return redirect()->route('login');
            return Redirect::to('/login');
        }
        
        //商品情報を取得
        $product = Product::findOrFail($request->productId);
        
        $cart = Cart::where('userName', '=', Auth::user()->name)->where('productId', '=', $request->productId)->get();
        
        if (count($cart) !== 0) {
            $postNum = $cart[0]->productNum + $request->selectedNum;
            $cart[0]->productNum = $postNum;
            $cart[0]->save();
        } else {
            //商品情報およびユーザー情報をカートに保存
            $newCart = new Cart();
            $newCart->userName = Auth::user()->name;
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
        $totalPrice = 0;
        foreach ($productsInCart as $product) {
            $totalNum += $product->productNum;
            $totalPrice += $product->productNum * $product->productPrice;
        }
        
        return array($totalNum, $totalPrice);
    }
    
    //決済処理
    public function confirm(Request $request) {
        $orderProducts = Cart::where('userName', '=', Auth::user()->name)->get();
        
        if (count($orderProducts) == 0) {
            abort(404, 'カートに商品が入っていません。');
        }
        
        foreach ($orderProducts as $orderProduct) {
            $orderHistory = new OrderHistory();
            $orderHistory->userName = $orderProduct->userName;
            $orderHistory->productName = $orderProduct->productName;
            $orderHistory->totalNum = $orderProduct->productNum;
            //totalPriceに変更する
            $orderHistory->totalPrice = $orderProduct->productPrice * $orderProduct->productNum;
            $orderHistory->imagePath = $orderProduct->imagePath;
            $orderHistory->save();
            $orderProduct->delete();
        }
        
        
        //商品一覧の取得
        $products = Product::orderBy('id', 'desc')->paginate(10);
        return view('users.productList')->with('products', $products);
        
    }
    
    //商品一覧を表示　
    public function showProducts() {
        $products = Product::orderBy('id', 'desc')->paginate(10);

        return view('users.productList')->with('products', $products);
    }
    
    //カートを表示　
    public function showCart() {
        $products = Cart::where('userName', '=', Auth::user()->name)->get();
        //合計個数と金額
        $totalNum = 0;
        $totalPrice = 0;

        foreach ($products as $product) {
            $totalNum += $product->productNum;
            $totalPrice += $product->productPrice * $product->productNum;
        }
    
        return view('users.cart')->with('products', $products)->with('totalNum', $totalNum)->with('totalPrice', $totalPrice);
        
    }
    
    //買い物履歴を表示
    public function showOrderHistory() {
        $orderHistory = OrderHistory::where('userName', '=', Auth::user()->name)->orderBy('id', 'desc')->paginate(10);
        
        return view('users.orderHistory')->with('products', $orderHistory);
    }
    
    public function orderGroup($orderHistory) {
        
    }
    
    //商品検索
    public function search(Request $request) {
        $searchText = $request->searchText;
        $searchContent = $request->searchContent;
        
        if ($searchContent == "searchProduct") {
            if ($searchText == "") {
                $products = Product::orderBy('id', 'desc')->paginate(10);
            } else {
                $products = Product::where('productName', 'like', '%'.$searchText.'%')->orderBy('id', 'desc')->paginate(10);
            }
            return view('users.productList')->with('products', $products);
        } else {
            if ($searchText == "") {
                $products = OrderHistory::orderBy('id', 'desc')->paginate(10);
            } else {
                $products = OrderHistory::where('productName', 'like', '%'.$searchText.'%')->orderBy('id', 'desc')->paginate(10);
            }
            return view('users.orderHistory')->with('products', $products);
        }
    }
    
    //カート内の変更処理
    public function changeCart(Request $request) {
        $cart = Cart::where('userName', '=', Auth::user()->name)->where('productId', '=', $request->selectedId)->get();
        
        $cart[0]->productNum = $request->selectedNum;
        $cart[0]->save();
        
        $postPrice = $request->selectedNum * $cart[0]->productPrice;
    
        $productsInCart = Cart::where('userName', '=', Auth::user()->name)->get();
        
        $totalNum = 0;
        $totalPrice = 0;
        foreach ($productsInCart as $product) {
            $totalNum += $product->productNum;
            $totalPrice += $product->productPrice * $product->productNum;
        }
        
        return array(number_format($postPrice), $totalNum, number_format($totalPrice));
    }
    
    //スクロールによって自動読み込み
    public function autoPaging(Request $request) {
        if ($request->currentMenu == 'products') {
            if ($request->searchText !== '') {
                $products = Product::where('productName', 'like', '%'.$request->searchText.'%')->orderBy('id', 'desc')->skip(($request->currentPage-1)*10)->take(10)->get();
            } else {
                $products = Product::orderBy('id', 'desc')->skip(($request->currentPage-1)*10)->take(10)->get();
            }
        } elseif (Auth::guest()) {
            $products = Product::orderBy('id', 'desc')->skip(($request->currentPage-1)*10)->take(10)->get();
        } else {
            $products = OrderHistory::where('userName', '=', Auth::user()->name)->orderBy('id', 'desc')->skip(($request->currentPage-1)*10)->take(10)->get();
        }
        return view('users.productList')->with('products', $products)->with('page', $request->currentPage);
    }
    
    public function deleteOrderHistory(Request $request) {
        $deleteOrderHistory = OrderHistory::where('userName', '=', Auth::user()->name)->delete();
        
        $deleteOrderHistory = OrderHistory::where('userName', '=', Auth::user()->name)->get();
        return view('users.orderHistory')->with('products', $deleteOrderHistory);
    }
}
