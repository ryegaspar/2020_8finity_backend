<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;

class NotificationsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index()
    {
        return NotificationResource::collection(auth()->user()->unreadNotifications);
    }

    public function destroy()
    {
        auth()->user()->notifications()->update(['read_at' => now()]);
    }
}
