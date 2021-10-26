<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\AdminCollection;
use App\Http\Resources\AdminResource;
use App\Models\Admin;

class ListsAdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function __invoke()
    {
        return AdminResource::collection(Admin::all());
    }
}
