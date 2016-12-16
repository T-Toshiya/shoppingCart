<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Product;
use App\Cart;
use App\OrderHistory;
use App\OrderDetail;
use App\OrderAmazonDetail;
use App\AmazonXml;
use App\User;
use App\TwitterUser;
use DB;
use Illuminate\Support\Facades\Auth;
use Socialite;
use Illuminate\Routing\Controller;

class ProductsController extends Controller
{
    //
    //初期画面
    public function index() {
        //商品一覧の取得
//        $products = Product::orderBy('id', 'desc')->paginate(10);
//        $count = Product::count();
        
        //amazon版
        $url = $this->amazonApi();

        //結構な頻度で取得失敗→APIの叩きすぎ
        $xmlData = AmazonXml::where('page', '=', 1)->first();
        
        $xml = $this->getXml($url, $xmlData, 1);
    
        $result = simplexml_load_string($xml);
        $results = array();
        foreach ($result->Items->Item as $Item) {
            $results[] = $Item;
        }
        
        //カートに入っている商品数の取得 
        if (! Auth::guest()) {
/*ok*/
            $productsInCart = Cart::where('userName', '=', Auth::user()->name)->get();
            $totalNum = $productsInCart->sum('productNum');
/**/
        }
        
        //return view('users.index', ['products' => $products, 'totalNum' => $totalNum, 'count' => $count]);
        return view('users.indexAmazon', ['items' => $results, 'totalNum' => $totalNum]);
    }
    
    public function amazon() {
        $url = $this->amazonApi();
    
        //結構な頻度で取得失敗→APIの叩きすぎ→xmlをDBに保存する(適度に更新)
        $xmlData = AmazonXml::where('page', '=', 1)->first();
        
        $xml = $this->getXml($url, $xmlData, 1);
        
        $result = simplexml_load_string($xml);
        $reuslts = array();
        foreach ($result->Items->Item as $Item) {
            $results[] = $Item;
        }
        
        //カートに入っている商品数の取得
        $totalNum = 0;
        if (! Auth::guest()) {
            $productsInCart = Cart::where('userName', '=', Auth::user()->name)->get();
            $totalNum = $productsInCart->sum('productNum');
        }
        
        return view('users.indexAmazon', ['items' => $results, 'totalNum' => $totalNum]);
    }
    
    public function amazonApi($page = 1, $searchText='') {
        $access_key_id = "AKIAII4CP5FHAAWO44WA";
        $secret_key = "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";
        
        
        $endpoint = "ecs.amazonaws.jp";
        
        $uri = "/onca/xml";
        
        $params = array(
            "Service" => "AWSECommerceService",
            "Operation" => "ItemSearch",
            "AWSAccessKeyId" => $access_key_id,
            "AssociateTag" => "toshiya05-22",
            "SearchIndex" => "Books",
            "Keywords" => $searchText,
            "Version" => "2009-07-01",
            "ResponseGroup" => "Medium",
            "ItemPage" => $page,
            "Sort" => "salesrank",
            "Power" => "binding:not kindle"//←kindle版を除外(値段が出ないから)
        );
        
        if (!isset($params["Timestamp"])) {
            $params["Timestamp"] = gmdate('Y-m-d\TH:i:s\Z');
        }
        
        ksort($params);
        
        $pairs = array();
        
        foreach ($params as $key => $value) {
            array_push($pairs, rawurlencode($key)."=".rawurlencode($value));
        }
        
        $canonical_query_string = join("&", $pairs);
        
        $string_to_sign = "GET\n".$endpoint."\n".$uri."\n".$canonical_query_string;
        
        $signature = base64_encode(hash_hmac("sha256", $string_to_sign, $secret_key, true));
        
        $request_uri = 'http://'.$endpoint.$uri.'?'.$canonical_query_string.'&Signature='.rawurlencode($signature);
        
        return $request_uri;
    }
    
    //カートに商品を入れる(DBの商品)
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
        $totalNum = $productsInCart->sum('productNum');
        
        return $totalNum;
    }
    
    //カートに商品を入れる(amazonの商品)
    public function insertAmazonCart(Request $request) {
        $cart = Cart::where('userName', '=', Auth::user()->name)->where('productId', '=', $request->productId)->first();
        
        if (count($cart) !== 0) {
            $postNum = $cart->productNum + $request->selectedNum;
            $cart->productNum = $postNum;
            $cart->save();
        } else {
            //商品情報およびユーザー情報をカートに保存
            $newCart = new Cart();
            $newCart->userName = Auth::user()->name;
            $newCart->productId = $request->productId;
            $newCart->productNum = $request->selectedNum;
            $newCart->productName = $request->productName;
            $newCart->productPrice = ltrim($request->productPrice);
            $newCart->imagePath = $request->imagePath;
            $newCart->save();
        }
        
        $productsInCart = Cart::where('userName', '=', Auth::user()->name)->get();
        $totalNum = $productsInCart->sum('productNum');
        
        return $totalNum;
    }
    
    //カートから商品を削除
    public function destroy(Request $request) {
        $deleteProduct = Cart::findOrFail($request->deleteId);
        $deleteProduct->delete();
        
        $productsInCart = Cart::where('userName', '=', Auth::user()->name)->get();
        $totalNum = $productsInCart->sum('productNum');
        $totalPrice = 0;
        foreach ($productsInCart as $product) {
            $totalPrice += $product->productNum * $product->productPrice;
        }
        
        return array($totalNum, $totalPrice);
    }
    
    //決済処理
    public function confirm(Request $request) {
        $orderProducts = Cart::where('userName', '=', Auth::user()->name)->get();
        $userInfo = User::where('name', '=', Auth::user()->name)->get();
        
        if (count($orderProducts) == 0) {
            abort(404, 'カートに商品が入っていません。');
        }
        
        //注文番号の登録
        $orderHistory = new OrderHistory();
        $orderHistory->userId = Auth::user()->id;
        $orderHistory->save();
        
        foreach ($orderProducts as $orderProduct) {
//            $orderDetails = new OrderDetail();
//            $orderDetails->orderNum = $orderHistory->id;
//            $orderDetails->productId = $orderProduct->productId;
//            $orderDetails->orderQuantity = $orderProduct->productNum;
//            $orderDetails->save();
//            $orderProduct->delete();
            
            //amazonの場合
            $orderDetails = new OrderAmazonDetail();
            $orderDetails->orderNum = $orderHistory->id;
            $orderDetails->productId = $orderProduct->productId;
            $orderDetails->productName = $orderProduct->productName;
            $orderDetails->productPrice = $orderProduct->productPrice;
            $orderDetails->imagePath = $orderProduct->imagePath;
            $orderDetails->orderQuantity = $orderProduct->productNum;
            $orderDetails->save();
            $orderProduct->delete();
        }
        
        
        //商品一覧の取得
//        $products = Product::orderBy('id', 'desc')->paginate(10);
//        return view('users.productList')->with('products', $products);
        
        //DBに保存したxmlをとってくる
        $xmlInfo = AmazonXml::where('page', '=', 1)->first();
        $xml = $xmlInfo->xml;
        $result = simplexml_load_string($xml);
        $results = array();
        foreach ($result->Items->Item as $Item) {
            $results[] = $Item;
        }
        
        return view('users.amazon', ['items' => $results]);
        
    }
    
    //商品一覧を表示　
    public function showProducts() {
        //$products = Product::orderBy('id', 'desc')->paginate(10);
        //return view('users.productList')->with('products', $products);
        
        $url = $this->amazonApi();

        //結構な頻度で取得失敗→APIの叩きすぎ
        $xmlData = AmazonXml::where('page', '=', 1)->first();

        $xml = $this->getXml($url, $xmlData, 1);
        
        $result = simplexml_load_string($xml);
        $results = array();
        foreach ($result->Items->Item as $Item) {
            $results[] = $Item;
        }
        
        return view('users.amazon', ['items' => $results]);
    }
    
    //カートを表示　
    public function showCart() {
        $products = Cart::where('userName', '=', Auth::user()->name)->get();
        //合計個数と金額
        $totalNum = $products->sum('productNum');
        $totalPrice = 0;
        //どうやってforeach以外でやるのか
        foreach ($products as $product) {
            $totalPrice += $product->productPrice * $product->productNum;
        }
    
        return view('users.cart')->with('products', $products)->with('totalNum', $totalNum)->with('totalPrice', $totalPrice);
        
    }
    
    //買い物履歴を表示
    public function showOrderHistory() {
        $orderHistories = OrderHistory::where('userId', '=', Auth::user()->id)->orderBy('id', 'desc')->paginate(10);
        $orderDetails = array();
        
//        foreach ($orderHistories as $orderHistory) {
//            foreach ($orderHistory->orderDetails as $orderDetail) {
//                $productInfo = Product::where('id', '=', $orderDetail->productId)->get();
//                $orderDetail->productName = $productInfo[0]->productName;
//                $orderDetail->productPrice = $productInfo[0]->productPrice;
//                $orderDetail->imagePath = $productInfo[0]->imagePath;
//            }
//            $orderDetails[] = $orderHistory->orderDetails;
//        }
        
        //amazon用
        foreach ($orderHistories as $orderHistory) {
            if (count($orderHistory->orderAmazonDetails) == 0) {
                continue;
            }
            $orderDetails[] = $orderHistory->orderAmazonDetails;
        }
        
        
        //全履歴を取得
        $orderHistories = OrderHistory::where('userId', '=', Auth::user()->id)->get();
    
        $orderCounts = $orderHistories->count();
    
        $lastPage = ceil(count($orderCounts)/10);
        
        return view('users.orderDetail')->with('orderDetails', $orderDetails)->with('lastPage', $lastPage);
    }
    
    //商品検索
    public function search(Request $request) {
        $searchText = $request->searchText;
        $searchContent = $request->searchContent;
        //商品検索の場合
        if ($searchContent == "searchProduct") {
            if ($searchText == "") {
                //$products = Product::orderBy('id', 'desc')->paginate(10);
                
                //amazon版
                $xmlInfo = AmazonXml::where('page', '=', 1)->get();
                $xml = $xmlInfo[0]->xml;
            } else {
                //$products = Product::where('productName', 'like', '%'.$searchText.'%')->orderBy('id', 'desc')->paginate(10);
                
                //amazon版
                $url = $this->amazonApi(1, $searchText);
                $xml = file_get_contents($url);
            }
            $result = simplexml_load_string($xml);
            $results = array();
            foreach ($result->Items->Item as $Item) {
                $results[] = $Item;
            }
            return view('users.amazon', ['items' => $results]);
            
            //return view('users.productList')->with('products', $products);
        } else {
            //注文検索の場合
            
            //ユーザーIDから注文履歴の取得
            $orderHistories = OrderHistory::where('userId', '=', Auth::user()->id)->orderBy('id', 'desc')->paginate(10);
            $orderDetails = array();
        
            if ($searchText == "") {
                //検索ワードなしで検索ボタンを押した場合、注文履歴を全て表示
                //$products = OrderHistory::orderBy('id', 'desc')->paginate(10);

                //amazon用
                $orderDetails = OrderHistory::where('userId', '=', Auth::user()->id)->orderAmazonDetails;
                return $orderDetails;
            } else {
                //検索ワード有りで検索した場合、
                //$products = OrderHistory::where('productName', 'like', '%'.$searchText.'%')->orderBy('id', 'desc')->paginate(10);
/**/
                
                //amazon用
                foreach ($orderHistories as $orderHistory) {
                    //検索ワードに一致するものを注文明細から取ってくる
                    //$searchItem = $orderHistory->orderAmazonDetails->where('productName', '=', $searchText);
                    //$searchItem = $orderHistory->orderAmazonDetails->where('productName', 'like', '%'.$searchText.'%'); 
                    $searchItem = OrderAmazonDetail::where('orderNum', '=', $orderHistory->id)->where('productName', 'like', '%'.$searchText.'%')->get();
                    if (count($searchItem) == 0) {
                        continue;
                    }
                    $orderDetails[] = $searchItem;
                }
/**/
            }
            //全履歴を取得
            $orderHistories = OrderHistory::where('userId', '=', Auth::user()->id)->get();

            $orderCounts =  $orderHistories->count();
/*ok*/
            $lastPage = ceil(count($orderCounts)/10);
/**/
            return view('users.orderDetail')->with('orderDetails', $orderDetails)->with('lastPage', $lastPage);  
        }
    }
    
    //カート内の変更(注文個数)処理
    public function changeCart(Request $request) {
        $cart = Cart::where('userName', '=', Auth::user()->name)->where('productId', '=', $request->selectedId)->first();
        
        //変更前の値段
        $beforePrice = $cart->productNum * $cart->productPrice;
        
        $cart[0]->productNum = $request->selectedNum;
        $cart[0]->save();
        
        //変更後の値段
        $afterPrice = $request->selectedNum * $cart->productPrice;
        //値段の変動分
        $changePrice = $afterPrice - $beforePrice;
/*ok*/   
        $productsInCart = DB::table('carts')->where('userName', Auth::user()->name)->get();
        
        $totalNum = $productsInCart->sum('productNum');
        
        $totalPrice = $request->nowPrice + $changePrice;
/**/        
        return array(number_format($afterPrice), $totalNum, number_format($totalPrice));
    }
    
    //スクロールによって自動読み込み
//    public function autoPaging(Request $request) {
//        if ($request->currentMenu == 'products') {
//            if ($request->searchText !== '') {
//                $products = Product::where('productName', 'like', '%'.$request->searchText.'%')->orderBy('id', 'desc')->skip(($request->currentPage-1)*10)->take(10)->get();
//            } else {
//                $products = Product::orderBy('id', 'desc')->skip(($request->currentPage-1)*10)->take(10)->get();
//            }
//        } elseif (Auth::guest()) {
//            $products = Product::orderBy('id', 'desc')->skip(($request->currentPage-1)*10)->take(10)->get();
//        } else {
//            $orderHistories = OrderHistory::where('userId', '=', Auth::user()->id)->orderBy('id', 'desc')->skip(($request->currentPage-1)*10)->take(10)->get();
//            
//            $orderDetails = array();
//
//            foreach ($orderHistories as $orderHistory) {
//                foreach ($orderHistory->orderDetails as $orderDetail) {
//                    $productInfo = Product::where('id', '=', $orderDetail->productId)->get();
//                    $orderDetail->productName = $productInfo[0]->productName;
//                    $orderDetail->productPrice = $productInfo[0]->productPrice;
//                    $orderDetail->imagePath = $productInfo[0]->imagePath;
//                }
//                $orderDetails[] = $orderHistory->orderDetails;
//            }
//            return view('users.orderDetailPage')->with('orderDetails', $orderDetails)->with('page', $request->currentPage);
//        }
//        return view('users.productList')->with('products', $products)->with('page', $request->currentPage);
//    }
    
    //amazon用自動ページング
    public function autoPaging(Request $request) {
        
        //現在のページ、検索ワード
        $url = $this->amazonApi($request->currentPage, $request->searchText);
        if ($request->searchText == "") {
            //$products = Product::orderBy('id', 'desc')->paginate(10);

            //amazon版
            $xmlData = AmazonXml::where('page', '=', $request->currentPage)->first();
            $xml = $this->getXml($url, $xmlData, $request->currentPage);
        } else {
            //$products = Product::where('productName', 'like', '%'.$searchText.'%')->orderBy('id', 'desc')->paginate(10);

            //amazon版
            $xml = file_get_contents($url);
        }
    
        $result = simplexml_load_string($xml);
        $results = array();
        foreach ($result->Items->Item as $Item) {
            $results[] = $Item;
        }
        
        return view('users.amazon')->with('items', $results)->with('page', $request->currentPage);
    }
    
    //購入履歴を削除
    public function deleteOrderHistory(Request $request) {
        $deleteOrderHistories = OrderHistory::where('userId', '=', Auth::user()->id)->delete();
        $deleteOrderDetails = OrderDetail::where('id', '=', Auth::user()->id)->delete();
    }
    
    //xmlの取得
    public function getXml($url, $xmlData, $page) {
        if (count($xmlData) == 0) {
            $xml = file_get_contents($url);
            $newXml = new AmazonXml();
            $newXml->xml = $xml;
            $newXml->page = $request->currentPage;
            $newXml->save();
        } else {
            $timestamp = $xmlData->updated_at->getTimestamp();
            $nowTime = time();
            $time = $nowTime - $timestamp;
            if ($time > 3600) {
                $xml = file_get_contents($url);
                $xmlData->xml = $xml;
                $xmlData->save();
            } else {
                $xml = $xmlData->xml;
            }
        }
        return $xml;
    }
}
