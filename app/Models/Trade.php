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

    protected static function booted()
    {
        static::saving(function (Trade $trade) {

            // Prevent saving a trade with invalid currencies
            $availableCurrencies = collect(Currency::AVAILABLE_CURRENCIES);
            if (!$availableCurrencies->contains(strtolower($trade->from_currency)) || !$availableCurrencies->contains(strtolower($trade->to_currency))) {
                throw new Exception("Invalid currencies");
            }

            // Prevent saving a trade with the same from and to currency
            if (strtolower($trade->from_currency) == strtolower($trade->to_currency)) {
                throw new Exception("Can not place a trade with the same currency");
            }
        });
    }

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
     * Get accepted or paid transactions
     */
    public function acceptedTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class)->closed()->orderBy('id', 'asc');
    }

    /**
     * Get all offer transactions
     */
    public function offers()
    {
        return $this->hasMany(Transaction::class)->buy();
    }

    /**
     * Get accepted or paid offer transactions
     */
    public function acceptedOffers(): HasMany
    {
        return $this->hasMany(Transaction::class)->buy()->closed();
    }

    /**
     * Get open or paid offer transactions
     */
    public function openOffers(): HasMany
    {
        return $this->hasMany(Transaction::class)->buy()->open();
    }

    /**
     * Get rejected offer transactions
     */
    public function rejectedOffers(): HasMany
    {
        return $this->hasMany(Transaction::class)->buy()->rejected();
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
     * Get offer amount still open for acceptance
     */
    public function getAvailableAmountAttribute()
    {
        return $this->amount - $this->acceptedOffers->sum('amount');
    }

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

    /**
     * Check is trade is fulfilled
     */
    public function getIsFulfilledAttribute()
    {
        return $this->status == self::STATUS_FULFILLED;
    }

    ////////////
    // SCOPES //
    ////////////

    /**
     * Limit query to only open or partial trades
     * 
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAcceptable($query)
    {
        return $query->whereIn('status', self::STATUS_OPEN_VALUES);
    }

    /**
     * Limit query to only open trades
     * 
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOpen($query)
    {
        return $query->where('status', self::STATUS_OPEN);
    }

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

        if ($amount > $this->availableAmount) {
            throw new Exception("Can not accept with an offer amount gretter than the outstanding requested amount");
        }

        if ($buyer->hasOpenOffer($this)) {
            throw new Exception("Can not accept trade when you still have an offer open.");
        }

        return $this->transactions()->create([
            'seller_id' => $this->user_id,
            'buyer_id' => $buyer->id,
            'amount' => $amount,
            'currency' => $this->from_currency,
            'type' => Transaction::TYPE_BUY,
        ]);
    }
}
