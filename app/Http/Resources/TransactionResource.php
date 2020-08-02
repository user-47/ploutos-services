<?php

namespace App\Http\Resources;

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
            'amount' => $this->amount,
            'currency' => $this->currency,
            'type' => $this->type,
            'status' => $this->status,
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
