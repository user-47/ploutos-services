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
        $amountFormats = CurrencyManager::allFormats($this->amount, $this->currency);
        $invoiceAmountFormats = CurrencyManager::allFormats($this->invoiceAmount, $this->invoiceCurrency);
        return [
            'id' => $this->uuid,
            'seller' => new UserResource($this->seller),
            'buyer' => new UserResource($this->buyer),
            'trade' => $this->trade->uuid,
            'amount' => $amountFormats['amount'],
            'amount_formats' => $amountFormats,
            'currency' => $this->currency,
            'invoice_amount' => $invoiceAmountFormats['amount'],
            'invoice_amount_formats' => $invoiceAmountFormats,
            'type' => $this->type,
            'status' => $this->status,
            'created_at' => $this->created_at->toDateTimeString(),
            $this->mergeWhen($this->invoice, [
                'recent_invoice' => new InvoiceResource($this->invoice),
            ]),
        ];
    }
}
