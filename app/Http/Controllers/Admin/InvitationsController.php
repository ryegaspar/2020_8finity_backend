<?php

namespace App\Http\Controllers\Admin;

use App\Facades\InvitationCode;
use App\Http\Controllers\Controller;
use App\Http\Requests\InviteAdminRequest;
use App\Http\Resources\InvitationResource;
use App\Models\Invitation;

class InvitationsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin')->except('show');
    }

    public function store(InviteAdminRequest $request)
    {
        Invitation::create([
            'code'  => InvitationCode::generate(),
            'email' => $request->email
        ])->send();

        return response()->json([], 201);
    }

    public function show($code)
    {
        $invitation = Invitation::findByCode($code)->first();

        abort_if($invitation->hasBeenUsed(), 404);

        return response()->json(new InvitationResource($invitation));
    }
}
