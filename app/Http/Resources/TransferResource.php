<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TransferResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id'                => $this->id,
            'from_account'      => $this->from_account,
            'from_account_name' => $this->fromAccount->name,
            'to_account'        => $this->to_account,
            'to_account_name'   => $this->toAccount->name,
            'admin_id'          => $this->admin_id,
            'admin_username'    => $this->admin->username,
            'amount'            => $this->amount,
            'description'       => $this->description,
            'notes'             => $this->notes,
            'date'              => $this->formatted_date,
        ];
    }
}
