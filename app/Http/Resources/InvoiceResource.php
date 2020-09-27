<?php

namespace App\Http\Resources;

use App\Managers\CurrencyManager;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->uuid,
            'reference_no' => $this->reference_no,
            'user' => new UserResource($this->user),
            'invoice_amount' => CurrencyManager::allFormats($this->amount, $this->currency),
            'currency' => $this->currency,
            'due_date' => optional($this->due_date)->toDateTimeString(),
            'status' => $this->status,
            'created_at' => $this->created_at->toDateTimeString(),
            'refundable_amount' => CurrencyManager::allFormats($this->refundableAmount, $this->currency),
        ];
    }
}
