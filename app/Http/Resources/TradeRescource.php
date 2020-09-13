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
        $availableAmountFormats = CurrencyManager::allFormats($this->availableAmount, $this->from_currency);
        $exchangeAmount = CurrencyManager::convertMinor($this->availableAmount, $this->from_currency, $this->to_currency, $this->rate);
        $exchangeAmountFormats = CurrencyManager::allFormats($exchangeAmount, $this->to_currency);
        return [
            'id' => $this->uuid,
            'user' => new UserResource($this->user),
            'amount' => $amountFormats['amount'],
            'amount_formats' => $amountFormats,
            'from_currency' => $this->from_currency,
            'to_currency' => $this->to_currency,
            'rate' => $this->rate,
            'exchange_amount' => $exchangeAmountFormats['amount'],
            'exchange_amount_formats' => $exchangeAmountFormats,
            'status' => $this->status,
            'created_at' => $this->created_at->toDateTimeString(),
            'available_amount' => $availableAmountFormats['amount'],
            'available_amount_formats' => $availableAmountFormats,
            'accepted_offers_count' => $this->acceptedOffers()->count(),
            'open_offers_count' => $this->openOffers()->count(),
            'rejected_offers_count' => $this->rejectedOffers()->count(),
        ];
    }
}
