<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Socialite;
use App\User;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest', ['except' => 'logout']);
    }
    
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:6|confirmed',
        ]);
    }

    public function selfAuth() {
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            // 認証に成功した
            return redirect()->intended('dashboard');
        }
    }
    
    //Socialiteを使用
    public function redirectToProvider(Request $request) {
        if ($request->sns == 'twitter') {
            return Socialite::driver('twitter')->redirect();
        } elseif ($request->sns == 'facebook') {
            return Socialite::driver('facebook')->redirect();
        }
    }
    
    public function handleProviderCallback(Request $request) {
        if ($request->sns == 'twitter') {
            $user = Socialite::driver('twitter')->user();
        } elseif ($request->sns == 'facebook') {
            $user = Socialite::driver('facebook')->user();
        }

        $tuser = User::where('name', '=', $user->name)->get();

        if (count($tuser) == 0) {
            return view('auth.emailRegister')->with('userInfo', $user);
        } else {
            $userId = $tuser[0]->id;
        }
        Auth::loginUsingId($userId);

        return redirect('/');
    }
    
}
