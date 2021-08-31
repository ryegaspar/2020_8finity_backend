<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
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
            'id'               => $this->id,
            'description'      => $this->description,
            'notes'            => $this->notes,
            'amount'           => $this->amount,
            'amount_formatted' => $this->amountFormatted,
            'date'             => $this->formatted_date,
            'category_type'    => $this->category->type == 'in' ? 'income' : 'expense',
            'category_icon'    => $this->category->icon,
            'category_name'    => $this->category->name,
            'category_id'      => $this->category_id,
            'admin_id'         => $this->admin->id,
            'admin_username'   => $this->admin->username,
            'account_id'       => $this->account->id,
            'account_name'     => $this->account->name,
        ];
    }
}
