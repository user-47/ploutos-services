<?php

namespace App\Models;

use App\Payments\Contracts\PaymentGateway;
use App\Traits\UuidModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentMethod extends Model
{
    use UuidModel, SoftDeletes;

    const TYPE_CARD = 'card';

    protected $fillable = ['user_id', 'payment_provider_id', 'type', 'token', 'default'];

    ///////////////////
    // RELATIONSHIPS //
    ///////////////////

    /**
     * Get the user that owns this payment method
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the payment provider of this payment method
     */
    public function paymentProvider(): BelongsTo
    {
        return $this->belongsTo(PaymentProvider::class);
    }

    //////////////
    // MUTATORS //
    //////////////

    /**
     * Checks if payment method is a card
     */
    public function getIsCardAttribute(): bool
    {
        return $this->type == self::TYPE_CARD;
    }

    /**
     * Returns the provider name and payment method type
     */
    public function getPaymentInfoAttribute(): string
    {
        return strtoupper($this->paymentProvider->name) . ' - ' . strtoupper($this->type);
    }

    ////////////
    // SCOPES //
    ////////////

    /**
     * Scope a query to only include default payment methods.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDefault($query)
    {
        return $query->where('default', 1);
    }

    /**
     * Scope a query to only include payment methods of type card.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCard($query)
    {
        return $query->where('type', self::TYPE_CARD);
    }

    /////////////
    // METHODS //
    /////////////

    /**
     * Returns the payment gateway implementation of the payment provider for this payment method
     */
    public function getPaymentGateway(): PaymentGateway
    {
        return PaymentProvider::getPaymentGateway($this->paymentProvider->name);
    }
}
