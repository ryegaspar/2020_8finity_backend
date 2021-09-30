<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LogsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'             => $this->id,
            'loggable_type'  => $this->loggable_type,
            'loggable_id'    => $this->loggable_id,
            'admin_id'       => $this->admin_id,
            'admin_username' => $this->admin->username,
            'action'         => $this->action,
            'changes'        => $this->changes,
            'created_at'     => $this->created_at,
        ];
    }
}
