<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentProviderUser extends Model
{
    ///////////////////
    // RELATIONSHIPS //
    ///////////////////

    /**
     * Get the payment provider for this account
     */
    public function paymentProvider(): BelongsTo
    {
        return $this->belongsTo(PaymentProvider::class);
    }

    /**
     * Get the user that owns this provider account
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    //////////////
    // ACCESORS //
    //////////////

    //////////////
    // MUTATORS //
    //////////////

    ////////////
    // SCOPES //
    ////////////

    /**
     * Scope query to only include payment provider user with identifier = $identifier
     * 
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIdentifiedBy($query, $identifier)
    {
        return $query->where('identifier', $identifier);
    }

    /**
     * Scope query to only include payment provider whose name matches given provider name
     * 
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeProvider($query, $providerName)
    {
        $provider = PaymentProvider::getPaymentProvider($providerName);
        return $query->where('payment_provider_id', $provider->id);
    }

    /////////////
    // METHODS //
    /////////////

    /**
     * Returns the user with the provider identifier
     */
    public static function getUser(string $providerName, string $identifier): ?User
    {
        return optional(self::provider($providerName)->identifiedBy($identifier)->first())->user;
    }

    /**
     * Returns the user with the provider identifier or fail if not found
     *
     * @throws Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public static function getUserOrFail(string $providerName, string $identifier): User
    {
        if (!is_null($user = static::getUser($providerName, $identifier))) {
            return $user;
        }
        throw (new ModelNotFoundException())->setModel(User::class);
    }

    /**
     * Returns the user with the provider identifier or returns a new user instance if not found
     */
    public static function getUserOrNew(string $providerName, string $identifier): User
    {
        if (!is_null($user = static::getUser($providerName, $identifier))) {
            return $user;
        }
        return new User();
    }

    /**
     * Returns user query filtering users with email and have identifier for provider passed
     */
    public static function findUserByEmail(string $providerName, string $email): Builder
    {
        return User::where('email', $email)
            ->whereHas('paymentProviders', function ($query) use ($providerName) {
                $query->provider($providerName);
            });
    }
}
