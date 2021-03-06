<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'username' => $this->username,
            $this->mergeWhen($this->isAuthUser, [
                'email' => $this->email,
                'phone_number' => $this->phone_number,
            ]),
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
