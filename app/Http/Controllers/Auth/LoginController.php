<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
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
    protected $redirectTo = '/start';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    protected function guard()
    {
        return Auth::guard('player');
    }

    public function username()
    {
        return 'onyen';
    }

    protected function credentials(Request $request)
    {
        // 'samaccountname' is the attribute we are using to
        // locate users in our LDAP directory with. The
        // value of the key must be the input name of
        // our HTML input, as shown above:
        return [
            'samaccountname' => $request->get('onyen'),
            'password' => $request->get('password'),
        ];
    }

    /*public function logout()
    {
        Auth::logout();
        return redirect('/');
    }*/

    /**
     * Get the failed login response instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        print_r($request->query); exit;
        return redirect()->back()
            ->withInput($request->only($this->username(), 'remember'))
            ->withErrors([
                'failedLogin' => 'Invalid Onyen or Password',
            ]);
    }
}
