<?php

namespace App\Models;

use App\Traits\UuidModel;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Trade extends Model
{
    use UuidModel, SoftDeletes;

    const STATUS_OPEN = 'open';
    const STATUS_PARTIAL = 'partial';
    const STATUS_FULFILLED = 'fulfilled';
    const STATUS_CANCELLED = 'cancelled';

    const STATUS_OPEN_VALUES = [
        self::STATUS_OPEN,
        self::STATUS_PARTIAL,
    ];

    protected $fillable = [
        'amount',
        'from_currency',
        'to_currency',
        'rate',
        'user_id',
    ];

    ///////////////////
    // RELATIONSHIPS //
    ///////////////////

    /**
     * Get the transactions for the trade.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get the user that owns the trade.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    //////////////
    // MUTATORS //
    //////////////

    /**
     * Get exchange amount.
     */
    public function getExchangeAmountAttribute():int
    {
        return $this->rate * $this->amount;
    }

    /**
     * Check if trade is open for transaction.
     */
    public function getIsAcceptableAttribute()
    {
        return collect(self::STATUS_OPEN_VALUES)->contains($this->status);
    }

    ////////////
    // SCOPES //
    ////////////

    /////////////
    // METHODS //
    /////////////

    /**
     * Mark a trade as fulfilled or partial depending on amount offered.
     * Creates a buy transaction for the trade.
     */
    public function accept(User $buyer, int $amount): Transaction
    {
        if ($this->user->id == $buyer->id) {
            throw new Exception("Can not accept a trade you originated.");
        }

        if (!$this->isAcceptable) {
            throw new Exception("Can not accept a trade that is not open or partially filled.");
        }

        $this->status = self::STATUS_FULFILLED;
        $this->save();

        return $this->transactions()->create([
            'seller_id' => $this->user_id,
            'buyer_id' => $buyer->id,
            'amount' => $amount,
            'currency' => $this->from_currency,
            'type' => Transaction::TYPE_BUY,
        ]);
    }
}
