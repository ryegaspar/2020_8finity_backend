<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\InvitationResource;
use App\Models\Invitation;

class InvitationsController extends Controller
{
    public function show($code)
    {
        $invitation = Invitation::findByCode($code)->first();

        abort_if($invitation->hasBeenUsed(), 404);

        return response()->json(new InvitationResource($invitation));
    }
}
