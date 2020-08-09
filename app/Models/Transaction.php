<?php

namespace App\Models;

use App\Events\TradeTransactionsAccepted;
use App\Events\TradeTransactionsRejected;
use App\Traits\UuidModel;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use UuidModel, SoftDeletes;

    const TYPE_BUY = 'buy';
    const TYPE_SELL = 'sell';

    const STATUS_OPEN = 'open';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_PAID = 'paid';
    const STATUS_REJECTED = 'rejected';
    const STATUS_CANCELLED = 'cancelled';

    const STATUS_CLOSED = [
        self::STATUS_ACCEPTED,
        self::STATUS_PAID
    ];

    protected $uuidPrefix = 'txn';

    protected $fillable = [
        'seller_id',
        'buyer_id',
        'amount',
        'currency',
        'type',
    ];

    ///////////////////
    // RELATIONSHIPS //
    ///////////////////

    /**
     * Get the buyer for the transaction.
     */
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    /**
     * Get the payments for the transaction.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get the seller for the transaction.
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * Get the trade that owns the transaction.
     */
    public function trade(): BelongsTo
    {
        return $this->belongsTo(Trade::class);
    }

    //////////////
    // MUTATORS //
    //////////////

    /**
     * Check if tranaction is a buy.
     */
    public function getIsBuyAttribute(): bool
    {
        return $this->type == self::TYPE_BUY;
    }

    /**
     * Check if tranaction is a sell.
     */
    public function getIsSellAttribute(): bool
    {
        return $this->type == self::TYPE_SELL;
    }

    /**
     * Check if tranaction is open.
     */
    public function getIsOpenAttribute(): bool
    {
        return $this->status == self::STATUS_OPEN;
    }

    /**
     * Get the payer of the transaction.
     */
    public function getPayerAttribute(): User
    {
        return $this->isBuy ? $this->buyer : $this->seller;
    }

    /**
     * Get amount to be paid.
     */
    public function getPaymentAmountAttribute(): int
    {
        return $this->isBuy ? $this->trade->rate * $this->amount : $this->amount;
    }

    /**
     * Get currency to be paid in.
     */
    public function getPaymentCurrencyAttribute(): string
    {
        return $this->isBuy ? $this->trade->to_currency : $this->currency;
    }

    ////////////
    // SCOPES //
    ////////////

    /**
     * Scope a query to only include buy transactions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBuy($query)
    {
        return $query->where('type', self::TYPE_BUY);
    }

    /**
     * Scope a query to only include accepted transactions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAccepted($query)
    {
        return $query->where('status', self::STATUS_ACCEPTED);
    }

    /**
     * Scope a query to only include open transactions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOpen($query)
    {
        return $query->where('status', self::STATUS_OPEN);
    }

    /**
     * Scope a query to only include accepted and paid transactions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeClosed($query)
    {
        return $query->whereIn('status', self::STATUS_CLOSED);
    }

    /**
     * Scope a query to only include rejected transactions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    /**
     * Scope a query to only include sell transactions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSell($query)
    {
        return $query->where('type', self::TYPE_SELL);
    }

    /**
     * Scope a query to only include transactions without payment.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithoutPayment($query)
    {
        return $query->whereDoesntHave('payments');
    }

    /**
     * Scope a query to only include transactions where user is buyer.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBuyer($query, User $user)
    {
        return $query->where('buyer_id', $user->id);
    }

    /**
     * Scope a query to only include transactions where user is seller.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSeller($query, User $user)
    {
        return $query->where('seller_id', $user->id);
    }

    /**
     * Scope a query to only include transactions where user is buyer or seller.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUser($query, User $user)
    {
        return $query->where(function($q) use ($user) {
            return $q->where('seller_id', $user->id)
                ->orWhere('buyer_id', $user->id);
        });
    }

    /////////////
    // METHODS //
    /////////////

    /**
     * Mark transaction as accepted.
     * Creates replica transaction of opposite type.
     */
    public function accept(User $seller): Transaction
    {
        if ($this->buyer->id == $seller->id) {
            throw new Exception("Can not accept a transaction you originated.");
        }

        if (!$this->isOpen) {
            throw new Exception("Can not accept a transaction that is not open.");
        }

        $this->status = Transaction::STATUS_ACCEPTED;
        $this->save();

        $transaction = $this->replicate();
        $transaction->type = $this->isBuy ? Transaction::TYPE_SELL : Transaction::TYPE_BUY;
        $transaction->save();

        event(new TradeTransactionsAccepted($this->trade));

        return $transaction;
    }

    /**
     * Mark transaction as rejected
     */
    public function reject()
    {
        if (!$this->isOpen) {
            throw new Exception("Can not reject a transaction that is not open.");
        }

        $this->status = Transaction::STATUS_REJECTED;
        $this->save();

        event(new TradeTransactionsRejected($this));
    }

    /**
     * Creates payment for transaction.
     */
    public function createPayment()
    {
        $this->payments()->create([
            'user_id' => $this->payer->id,
            'currency' => $this->paymentCurrency,
            'amount' => $this->paymentAmount,
        ]);
    }
}
