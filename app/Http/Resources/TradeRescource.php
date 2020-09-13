<?php

namespace App\Http\Resources;

use App\Managers\CurrencyManager;
use Illuminate\Http\Resources\Json\JsonResource;

class TradeRescource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $amountFormats = CurrencyManager::allFormats($this->amount, $this->from_currency);
        return [
            'id' => $this->uuid,
            'user' => new UserResource($this->user),
            'amount' => $amountFormats['amount'],
            'amount_formats' => $amountFormats,
            'from_currency' => $this->from_currency,
            'to_currency' => $this->to_currency,
            'rate' => $this->rate,
            'status' => $this->status,
            'created_at' => $this->created_at->toDateTimeString(),
            'available_amount' => $this->availableAmount,
            'accepted_offers_count' => $this->acceptedOffers()->count(),
            'open_offers_count' => $this->openOffers()->count(),
            'rejected_offers_count' => $this->rejectedOffers()->count(),
        ];
    }
}
