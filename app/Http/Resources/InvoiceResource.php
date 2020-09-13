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
        $amountFormats = CurrencyManager::allFormats($this->amount, $this->currency);
        $refundableAmountFormats = CurrencyManager::allFormats($this->refundableAmount, $this->currency);
        return [
            'id' => $this->uuid,
            'reference_no' => $this->reference_no,
            'user' => new UserResource($this->user),
            'amount' => $amountFormats['amount'],
            'amount_formats' => $amountFormats,
            'currency' => $this->currency,
            'due_date' => optional($this->due_date)->toDateTimeString(),
            'status' => $this->status,
            'created_at' => $this->created_at->toDateTimeString(),
            'refundable_amount' => $refundableAmountFormats['amount'],
            'refundable_amount_formats' => $refundableAmountFormats,
        ];
    }
}
