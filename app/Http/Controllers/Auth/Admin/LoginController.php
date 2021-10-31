<?php

namespace App\Http\Controllers\Auth\Admin;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
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
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Set decay minutes for login throttling
     *
     * @var int
     */
    public $decayMinutes = 20;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['guest', 'guest:admin'])->except('logout');
    }

    /**
     * Get the needed authorization credentials from the request.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    protected function credentials(Request $request)
    {
//        return $request->only($this->username(), 'password');
        return [
            $this->username() => strtolower($request->username),
            'password'        => $request->password,
            'is_active'       => true
        ];
    }

    /**
     * The user has been authenticated
     *
     * @param Request $request
     * @param $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function authenticated(Request $request, $user)
    {
        return response()->json('', 200);
    }

    public function username()
    {
        return 'username';
    }

    protected function guard()
    {
        return Auth::guard('admin');
    }
}
