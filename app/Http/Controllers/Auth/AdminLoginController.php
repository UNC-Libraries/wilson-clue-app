<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminLoginController extends Controller
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
     */
    protected string $redirectTo = '/admin';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    protected function guard(): Guard|StatefulGuard
    {
        return Auth::guard('admin');
    }

    public function username(): string
    {
        return 'onyen';
    }

    protected function credentials(Request $request): array
    {
        // 'samaccountname' is the attribute we are using to
        // locate users in our LDAP directory with. The
        // value of the key must be the input name of
        // our HTML input, as shown above:
        return [
            'samaccountname' => $request->get('onyen'),
            'password' => $request->get('password'),
            'fallback' => [
                'onyen' => $request->get('onyen'),
                'password' => $request->get('password'),
            ],
        ];
    }

    /**
     * Get the failed login response instance.
     */
    protected function sendFailedLoginResponse(Request $request): RedirectResponse
    {
        return redirect()->back()
            ->withInput($request->only($this->username(), 'remember'))
            ->withErrors([
                'failedLogin' => 'Invalid Onyen or Password',
            ]);
    }
}
