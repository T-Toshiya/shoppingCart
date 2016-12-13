<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

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
    public function redirectToProvider() {
        return Socialite::driver('twitter')->redirect();
    }
    
    public function handleProviderCallback() {
        $user = Socialite::driver('twitter')->user();
        
        Auth::login($user);
        return redirect('/');
    }
    
}
