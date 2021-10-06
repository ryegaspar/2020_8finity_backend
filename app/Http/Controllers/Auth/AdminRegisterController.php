<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\Admin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminRegisterController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function register()
    {
        $invitation = Invitation::findByCode(request('invitation_code'));

        $admin = Admin::create([
            'first_name' => request('first_name'),
            'last_name'  => request('last_name'),
            'username'   => request('username'),
            'email'      => request('email'),
            'password'   => Hash::make(request('password'))
        ]);

        $invitation->update([
            'admin_id' => $admin->id
        ]);

        return response()->json([], 201);
    }

    /**
     * Get the guard to be used during registration.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard('admin');
    }
}
