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
        return [
            'id' => $this->uuid,
            'user' => new UserResource($this->user),
            'trade_amount' => CurrencyManager::allFormats($this->amount, $this->from_currency),
            'from_currency' => $this->from_currency,
            'to_currency' => $this->to_currency,
            'rate' => $this->rate,
            'exchange_amount' => CurrencyManager::allFormats($this->exchangeAmount, $this->to_currency),
            'status' => $this->status,
            'created_at' => $this->created_at->toDateTimeString(),
            'available_amount' => CurrencyManager::allFormats($this->availableAmount, $this->from_currency),
            'accepted_offers_count' => $this->acceptedOffers()->count(),
            'open_offers_count' => $this->openOffers()->count(),
            'rejected_offers_count' => $this->rejectedOffers()->count(),
        ];
    }
}
