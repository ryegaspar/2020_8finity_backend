<?php

namespace App\Http\Controllers\Auth\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\Admin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
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
        request()->validate([
            'first_name' => 'required|string',
            'last_name'  => 'required|string',
            'username'   => 'required|string|min:6|max:255|unique:admins,username|regex:/^([a-zA-Z\_\.]+)(\d+)?$/u',
            'password'   => 'required|string|min:8|confirmed',
        ]);

        $invitation = Invitation::findByCode(request('invitation_code'));
        abort_if($invitation->hasBeenUsed(), 404);

        $admin = Admin::create([
            'first_name' => request('first_name'),
            'last_name'  => request('last_name'),
            'username'   => request('username'),
            'email'      => $invitation->email,
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
