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

//use Illuminate\Http\RedirectResponse

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
        $xmlData = AmazonXml::where('page', '=', 1)->get();
    
        if (count($xmlData) == 0) {
            $xml = file_get_contents($url);
            $newXml = new AmazonXml();
            $newXml->xml = $xml;
            $newXml->page = 1;
            $newXml->save();
        } else {
            $timestamp = $xmlData[0]->updated_at->getTimestamp();
            $nowTime = time();
            $time = $nowTime - $timestamp;
            if ($time > 3600) {
                $xml = file_get_contents($url);
                $xmlData[0]->xml = $xml;
                $xmlData[0]->save();
            } else {
                $xml = $xmlData[0]->xml;
            }
        }
        $result = simplexml_load_string($xml);
        $results = array();
        foreach ($result->Items->Item as $Item) {
            $results[] = $Item;
        }
        
        //カートに入っている商品数の取得
        $totalNum = 0;
        if (! Auth::guest()) {
            $productsInCart = Cart::where('userName', '=', Auth::user()->name)->get();
            $totalNum = 0;
            foreach ($productsInCart as $product) {
                $totalNum += $product->productNum;
            }
        }
        
        //return view('users.index', ['products' => $products, 'totalNum' => $totalNum, 'count' => $count]);
        return view('users.indexAmazon', ['items' => $results, 'totalNum' => $totalNum]);
    }
    
    public function test() {
        return 'ok';
    }
    
    public function amazon() {
        
        
        $url = $this->amazonApi();
    
        //結構な頻度で取得失敗→APIの叩きすぎ
        $xmlData = AmazonXml::where('page', '=', 1)->get();
        
        if (count($xmlData) == 0) {
            $xml = file_get_contents($url);
            $newXml = new AmazonXml();
            $newXml->xml = $xml;
            $newXml->page = 1;
            $newXml->save();
        } else {
            $timestamp = $xmlData[0]->updated_at->getTimestamp();
            $nowTime = time();
            $time = $nowTime - $timestamp;
            if ($time > 3600) {
                $xml = file_get_contents($url);
                $xmlData[0]->xml = $xml;
                $xmlData[0]->save();
            } else {
                $xml = $xmlData[0]->xml;
            }
        }
        return $time;
        $result = simplexml_load_string($xml);
        $reuslts = array();
        foreach ($result->Items->Item as $Item) {
            $results[] = $Item;
        }
        
        //カートに入っている商品数の取得
        $totalNum = 0;
        if (! Auth::guest()) {
            $productsInCart = Cart::where('userName', '=', Auth::user()->name)->get();
            $totalNum = 0;
            foreach ($productsInCart as $product) {
                $totalNum += $product->productNum;
            }
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
    
    //Socialiteを使用
    public function redirectToProvider() {
        return Socialite::driver('twitter')->redirect();
    }
    
    public function handleProviderCallback() {
        $user = Socialite::driver('twitter')->user();
        
        $tuser = User::where('name', '=', $user->getNickname())->get();
        
            if (count($tuser) == 0) {
                
                return view('auth.emailRegister')->with('userInfo', $user);
                
                $newUser = new User();
                //$newUser->userId = $userId[1];
                $newUser->name = $user->getNickname();
                $newUser->email = 'dummy';
                $newUser->password = 'dummy';
                $newUser->save();
                
                $userId = $newUser->id;
            } else {
                $userId = $tuser[0]->id;
            }
        Auth::loginUsingId($userId);
        
        return redirect('/');
    }

    //地道にやったが上の方が簡単    
//    public function loginWithTwitter() {
//        $token = $this->getRequestToken();
//        $token = explode('&', $token);
//        $oauth_token = explode('=', $token[0]);
//        $oauth_token_secret = explode('=', $token[1]);
//        
//        // セッション[$_SESSION["oauth_token_secret"]]に[oauth_token_secret]を保存する
//        session_start() ;
//        session_regenerate_id( true ) ;
//        $_SESSION['oauth_token_secret'] = $oauth_token_secret[1] ;
//        // ユーザーを認証画面へ飛ばす
//        //return header( 'Location: https://api.twitter.com/oauth/authorize?oauth_token=' . $oauth_token[1] );
//        return redirect('https://api.twitter.com/oauth/authorize?oauth_token='.$oauth_token[1]);
//    }
    
//    public function getRequestToken() {
//        $api_key = "BgUCzhLCaJ9tTisgrIAXflofn";
//        $api_secret = "N8apr76C9zNjFEDto3peX6diC4QlpLnalk8jshO3Yd3rBN0TEz";
//        $accessToken = "719863859192856576-h8k4iSPDiFmB7OMcTV3i48zcR6jP4ym";
//        $secretToken = "";
//        $secret_key = rawurlencode($api_secret)."&";
//        $endpoint = "https://api.twitter.com/oauth/request_token";
//        $requestMethod = "POST";
//        
//        $params = array(
//            "oauth_callback" => "http://192.168.33.10:8000/getAccessToken",
//            "oauth_consumer_key" => $api_key,
//            "oauth_nonce" => microtime(),
//            "oauth_signature_method" => "HMAC-SHA1",
//            "oauth_timestamp" => time(),
////            "oauth_token" => $accessToken,
//            "oauth_version" => "1.0"
//        );
//        
//        $pairs = array();
//        
//        foreach ($params as $key => $value) {
//            if ($key == 'oauth_callback') {
//                continue;
//            }
//            $params[$key] = rawurlencode($value);
//        }
//        
//        ksort($params);
//        
//        //$canonical_query_string = join("&", $pairs);
//        $canonical_query_string = http_build_query($params, '', '&');
//        
//        $string_to_sign = rawurlencode($requestMethod)."&".rawurlencode($endpoint)."&".rawurlencode($canonical_query_string);
//        
//        $signature = base64_encode(hash_hmac("sha1", $string_to_sign, $secret_key, true));
//        
//        $request_uri = $endpoint.'?'.$canonical_query_string.'&oauth_signature='.rawurlencode($signature);
//        
//        $params['oauth_signature'] = $signature;
//        
//        $header_params = http_build_query($params, '', ',');
//        
//        $context = array(
//            'http' => array(
//                'method' => $requestMethod , //リクエストメソッド
//                'header' => array(			  //カスタムヘッダー
//                    'Authorization: OAuth ' . $header_params ,
//                ) ,
//            ) ,
//        ) ;
//        
//        $response = file_get_contents( $endpoint , false , stream_context_create( $context ) ) ;
//        
//        return $response;
//    }
    
//    public function getAccessToken() {
//        
//        if( isset( $_GET['oauth_token'] ) && !empty( $_GET['oauth_token'] ) && isset( $_GET['oauth_verifier'] ) && !empty( $_GET['oauth_verifier'] ) ) {
//            // アクセストークンを取得するための処理
//            $api_key = "BgUCzhLCaJ9tTisgrIAXflofn";
//            $api_secret = "N8apr76C9zNjFEDto3peX6diC4QlpLnalk8jshO3Yd3rBN0TEz";
//            $accessToken = "719863859192856576-h8k4iSPDiFmB7OMcTV3i48zcR6jP4ym";
//            $endpoint = "https://api.twitter.com/oauth/access_token";
//            $requestMethod = "POST";
//
//            $params = array(
//                "oauth_consumer_key" => $api_key,
//                "oauth_token" => $_GET['oauth_token'],
//                "oauth_verifier" => $_GET['oauth_verifier'],
//                "oauth_nonce" => microtime(),
//                "oauth_signature_method" => "HMAC-SHA1",
//                "oauth_timestamp" => time(),
//                "oauth_version" => "1.0"
//            );
//
//            session_start();
//            $request_token_secret = $_SESSION['oauth_token_secret'];
//            $secret_key = rawurlencode($api_secret)."&".$request_token_secret;
//
//            $pairs = array();
//
//            foreach ($params as $key => $value) {
//                if ($key == 'oauth_callback') {
//                    continue;
//                }
//                $params[$key] = rawurlencode($value);
//            }
//
//            ksort($params);
//
//            //$canonical_query_string = join("&", $pairs);
//            $canonical_query_string = http_build_query($params, '', '&');
//
//            $string_to_sign = rawurlencode($requestMethod)."&".rawurlencode($endpoint)."&".rawurlencode($canonical_query_string);
//
//            $signature = base64_encode(hash_hmac("sha1", $string_to_sign, $secret_key, true));
//
//            $params['oauth_signature'] = $signature;
//
//            $header_params = http_build_query($params, '', ',');
//
//            $context = array(
//                'http' => array(
//                    'method' => $requestMethod , //リクエストメソッド
//                    'header' => array(			  //カスタムヘッダー
//                        'Authorization: OAuth ' . $header_params ,
//                    ) ,
//                ) ,
//            ) ;
//
//            $response = file_get_contents( $endpoint , false , stream_context_create( $context ) ) ;
//            
//            $authInfo = explode('&', $response);
//            $oauth_token = explode('=', $authInfo[0]);
//            $oauth_token_secret = explode('=', $authInfo[1]);
//            $userId = explode('=', $authInfo[2]);
//            $userName = explode('=', $authInfo[3]);
//            $user = TwitterUser::where('userName', '=', $userName[1])->get();
//
//            if (count($user) == 0) {
//                $newUser = new TwitterUser();
//                $newUser->userId = $userId[1];
//                $newUser->userName = $userName[1];
//                $newUser->save();
//            } 
//            Auth::login();
//            return redirect('/');
//        }
//    }
    
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
    
    //カートに商品を入れる
    public function insertAmazonCart(Request $request) {
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
            $newCart->productName = $request->productName;
            $newCart->productPrice = ltrim($request->productPrice);
            $newCart->imagePath = $request->imagePath;
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
        
        $url = $this->amazonApi();
        $test = file_get_contents($url);
        //$result = simplexml_load_file($url); 
        $result = simplexml_load_string($test);
        //return var_dump($result);
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
        $xmlData = AmazonXml::where('page', '=', 1)->get();

        if (count($xmlData) == 0) {
            $xml = file_get_contents($url);
            $newXml = new AmazonXml();
            $newXml->xml = $xml;
            $newXml->page = 1;
            $newXml->save();
        } else {
            $timestamp = $xmlData[0]->updated_at->getTimestamp();
            $nowTime = time();
            $time = $nowTime - $timestamp;
            if ($time > 3600) {
                $xml = file_get_contents($url);
                $xmlData[0]->xml = $xml;
                $xmlData[0]->save();
            } else {
                $xml = $xmlData[0]->xml;
            }
        }
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
        
        $orderCounts = array();
        
        foreach ($orderHistories as $orderHistory) {
            $orderCounts[] = $orderHistory->orderAmazonDetails;
        }
        
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
                $url = $this->amazonApi();
            } else {
                //$products = Product::where('productName', 'like', '%'.$searchText.'%')->orderBy('id', 'desc')->paginate(10);
                
                //amazon版
                $url = $this->amazonApi(1, $searchText);
            }
            $xml = file_get_contents($url);
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
                foreach ($orderHistories as $orderHistory) {
                    if (count($orderHistory->orderAmazonDetails) == 0) {
                        continue;
                    }
                    $orderDetails[] = $orderHistory->orderAmazonDetails;
                }
            } else {
                //検索ワード有りで検索した場合、
                //$products = OrderHistory::where('productName', 'like', '%'.$searchText.'%')->orderBy('id', 'desc')->paginate(10);

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
            }
            //全履歴を取得
            $orderHistories = OrderHistory::where('userId', '=', Auth::user()->id)->get();

            $orderCounts = array();

            foreach ($orderHistories as $orderHistory) {
                $orderCounts[] = $orderHistory->orderAmazonDetails;
            }

            $lastPage = ceil(count($orderCounts)/10);

            return view('users.orderDetail')->with('orderDetails', $orderDetails)->with('lastPage', $lastPage);  
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
            $xmlData = AmazonXml::where('page', '=', $request->currentPage)->get();
            if (count($xmlData) == 0) {
                sleep(1);
                $xml = file_get_contents($url);
                $newXml = new AmazonXml();
                $newXml->xml = $xml;
                $newXml->page = $request->currentPage;
                $newXml->save();
            } else {
                $timestamp = $xmlData[0]->updated_at->getTimestamp();
                $nowTime = time();
                $time = $nowTime - $timestamp;
                if ($time > 3600) {
                    sleep(1);
                    $xml = file_get_contents($url);
                    $xmlData[0]->xml = $xml;
                    $xmlData[0]->save();
                } else {
                    $xml = $xmlData[0]->xml;
                }
            }
        } else {
            //$products = Product::where('productName', 'like', '%'.$searchText.'%')->orderBy('id', 'desc')->paginate(10);

            //amazon版
            sleep(1);
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
        
        //$deleteOrderHistory = OrderHistory::where('userName', '=', Auth::user()->name)->get();
        //return view('users.orderDetailPage')->with('products', $deleteOrderHistory);
    }
}
