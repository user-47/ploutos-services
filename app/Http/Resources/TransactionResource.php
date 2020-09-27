<?php

namespace App\Http\Resources;

use App\Managers\CurrencyManager;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
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
            'seller' => new UserResource($this->seller),
            'buyer' => new UserResource($this->buyer),
            'trade' => $this->trade->uuid,
            'trade_amount' => CurrencyManager::allFormats($this->amount, $this->currency),
            'currency' => $this->currency,
            'invoice_amount' => CurrencyManager::allFormats($this->invoiceAmount, $this->invoiceCurrency),
            'transaction_amount' => CurrencyManager::allFormats($this->transactionAmount, $this->invoiceCurrency),
            'transaction_fee' => CurrencyManager::allFormats($this->transactionFee, $this->invoiceCurrency),
            'type' => $this->type,
            'status' => $this->status,
            'created_at' => $this->created_at->toDateTimeString(),
            $this->mergeWhen($this->invoice, [
                'recent_invoice' => new InvoiceResource($this->invoice),
            ]),
        ];
    }
}
