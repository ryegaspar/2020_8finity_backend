<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationCollection;

class NotificationsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index()
    {
        return response()->json(new NotificationCollection(auth()->user()->unreadNotifications));
    }

    public function destroy($id)
    {
        auth()->user()->notifications()->findOrFail($id)->markAsRead();
    }
}
