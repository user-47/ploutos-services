<?php

namespace App\Models;

use App\Traits\UuidModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Trade extends Model
{
    use UuidModel, SoftDeletes;

    const STATUS_OPEN = 'open';
    const STATUS_PARTIAL = 'partial';
    const STATUS_FULFILLED = 'fulfilled';
    const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'amount',
        'from_currency',
        'to_currency',
        'rate',
        'user_id',
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function accept(User $buyer, int $amount)
    {
        return $this->transactions()->create([
            'seller_id' => $this->user_id,
            'buyer_id' => $buyer->id,
            'amount' => $amount,
            'currency' => $this->from_currency,
            'type' => Transaction::TYPE_BUY,
        ]);
    }
}