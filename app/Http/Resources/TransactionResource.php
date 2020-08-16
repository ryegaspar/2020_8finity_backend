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
            'amount'           => $this->amount,
            'amount_formatted' => $this->amountFormatted,
            'date'             => $this->formatted_date,
            'category_type'    => $this->category->type == 'in' ? 'income' : 'expense',
            'category_name'    => $this->category->description,
            'category_id'      => $this->category_id
        ];
    }
}
