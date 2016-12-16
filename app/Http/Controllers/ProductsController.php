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
        //amazon版
        $url = $this->amazonApi();

        //結構な頻度で取得失敗→APIの叩きすぎ
        $xmlData = AmazonXml::where('page', '=', 1)->first();
        
        $xml = $this->getXml($url, $xmlData, 1);

        $results = $this->getItem($xml);
        
        //カートに入っている商品数の取得 
        if (Auth::check()) {
/*ok*/
            $productsInCart = Cart::where('userName', '=', Auth::user()->name)->get();
            $totalNum = $productsInCart->sum('productNum');
/**/
        }
        
        return view('users.indexAmazon', ['items' => $results, 'totalNum' => $totalNum]);
    }
    
    public function amazon() {
        $url = $this->amazonApi();
    
        //結構な頻度で取得失敗→APIの叩きすぎ→xmlをDBに保存する(適度に更新)
        $xmlData = AmazonXml::where('page', '=', 1)->first();
        
        $xml = $this->getXml($url, $xmlData, 1);
        $results = $this->getItem($xml);
        
        //カートに入っている商品数の取得
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
    
    //カートに商品を入れる(amazonの商品)
    public function insertAmazonCart(Request $request) {
        $cart = Cart::where('userName', '=', Auth::user()->name)->where('productId', '=', $request->productId)->first();
        

        $cart = $cart ?: new Cart();
        //商品情報およびユーザー情報をカートに保存
        $cart->id = $cart->id;
        $cart->userName = Auth::user()->name;
        $cart->productId = $request->productId;
        $cart->productNum = $cart->productNum + $request->selectedNum;
        $cart->productName = $request->productName;
        $cart->productPrice = ltrim($request->productPrice);
        $cart->imagePath = $request->imagePath;
        $cart->save();
        
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
        
        //↓ここまだ
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
            //amazonの場合
            $orderDetails = new OrderAmazonDetail();
            $orderDetails->orderNum = $orderHistory->id;
            $orderDetails->userName = Auth::user()->name;
            $orderDetails->productId = $orderProduct->productId;
            $orderDetails->productName = $orderProduct->productName;
            $orderDetails->productPrice = $orderProduct->productPrice;
            $orderDetails->imagePath = $orderProduct->imagePath;
            $orderDetails->orderQuantity = $orderProduct->productNum;
            $orderDetails->save();
            $orderProduct->delete();
        }
        
        //DBに保存したxmlをとってくる
        $xmlInfo = AmazonXml::where('page', '=', 1)->first();
        $xml = $xmlInfo->xml;
        $results = $this->getItem($xml);
        
        return view('users.amazon', ['items' => $results]);
        
    }
    
    //商品一覧を表示　
    public function showProducts() {
        $url = $this->amazonApi();

        //結構な頻度で取得失敗→APIの叩きすぎ
        $xmlData = AmazonXml::where('page', '=', 1)->first();

        $xml = $this->getXml($url, $xmlData, 1);
        $results = $this->getItem($xml);
        
        return view('users.amazon', ['items' => $results]);
    }
    
    //カートを表示　
    public function showCart() {
        //SQL内で計算する
        $products = Cart::select(DB::raw('*, (productNum * productPrice) as totalPrice') )->where('userName', '=', Auth::user()->name)->get();
        //合計個数と金額
        //dd($products);
        $totalNum = $products->sum('productNum');
        $totalPrice = $products->sum('totalPrice');
    
        return view('users.cart')->with('products', $products)->with('totalNum', $totalNum)->with('totalPrice', $totalPrice);
        
    }
    
    //買い物履歴を表示
    public function showOrderHistory() {
        $orderHistories = OrderHistory::where('userId', '=', Auth::user()->id)->paginate(10);
        //return $orderHistories;
        
        $orderDetails = OrderAmazonDetail::where('userName', '=', Auth::user()->name)->orderBy('id', 'desc')->get();
        $orderDetails = $orderDetails->groupBy('orderNum');
        
        $lastPage = $orderHistories->lastPage();
        
        return view('users.orderDetail')->with('orderDetails', $orderDetails)->with('lastPage', $lastPage);
    }
    
    //商品検索
    public function search(Request $request) {
        $searchText = $request->searchText;
        $searchContent = $request->searchContent;
        
        //商品検索の場合
        if ($searchContent == "searchProduct") {
            if ($searchText == "") {
                $xmlInfo = AmazonXml::where('page', '=', 1)->first();
                $xml = $xmlInfo->xml;
            } else {
                $url = $this->amazonApi(1, $searchText);
                $xml = file_get_contents($url);
            }
            $results = $this->getItem($xml);
            return view('users.amazon', ['items' => $results]);
            
        } else {
            //注文検索の場合
            //$orderHistories = OrderHistory::where('userId', '=', Auth::user()->id)->orderBy('id', 'desc')->join('order_amazon_details', 'order_amazon_details.orderNum', '=', 'order_histories.id')->get();
            
            $orderDetails = OrderAmazonDetail::whereRaw("userName = ?", array( Auth::user()->name));
            //ユーザーIDから注文履歴の取得
            if ($searchText) {
/*ok*/
                $orderDetails = $orderDetails->where("productName", "like", "%".$searchText."%");
/**/
            }
            $orderDetails = $orderDetails->orderBy('id', 'desc')->get();
            $orderDetails = $orderDetails->groupBy('orderNum');
            
/*ok*/
            $lastPage = OrderHistory::where('userId', '=', Auth::user()->id)->paginate(10)->lastPage();
/**/
            return view('users.orderDetail')->with('orderDetails', $orderDetails)->with('lastPage', $lastPage);  
        }
    }
    
    //カート内の変更(注文個数)処理
    public function changeCart(Request $request) {
        $cart = Cart::where('userName', '=', Auth::user()->name)->where('productId', '=', $request->selectedId)->first();
        
        //変更前の値段
        $beforePrice = $cart->productNum * $cart->productPrice;
        
        $cart->productNum = $request->selectedNum;
        $cart->save();
        
        //変更後の値段
        $afterPrice = $request->selectedNum * $cart->productPrice;
        //値段の変動分
        $changePrice = $afterPrice - $beforePrice;
/*ok*/   
        $productsInCart = Cart::where('userName', Auth::user()->name)->get();
        
        $totalNum = $productsInCart->sum('productNum');
        
        $totalPrice = $request->nowPrice + $changePrice;
/**/        
        return array(number_format($afterPrice), $totalNum, number_format($totalPrice));
    }

    public function autoPaging(Request $request) {
        
        //現在のページ、検索ワード
        $url = $this->amazonApi($request->currentPage, $request->searchText);
        if ($request->searchText == "") {
            $xmlData = AmazonXml::where('page', '=', $request->currentPage)->first();
            $xml = $this->getXml($url, $xmlData, $request->currentPage);
        } else {
            $xml = file_get_contents($url);
        }
        $results = $this->getItem($xml);
        
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
    
    //xmlから商品の取得
    public function getItem($xml) {
        $result = simplexml_load_string($xml);
        $reuslts = array();
        foreach ($result->Items->Item as $Item) {
            $results[] = $Item;
        }
        return $results;
    }
}
