<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index()
    {
        return auth()->user()->unreadNotifications;
    }

    public function destroy($id)
    {
        auth()->user()->notifications()->findOrFail($id)->markAsRead();
    }
}
