<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\InvitationResource;
use App\Models\Invitation;

class InvitationsController extends Controller
{
    public function __construct()
    {
//        $this->middleware('auth:admin');
    }

    public function store()
    {
        return response()->json([], 201);
    }

    public function show($code)
    {
        $invitation = Invitation::findByCode($code)->first();

        return response()->json(new InvitationResource($invitation));
    }
}
