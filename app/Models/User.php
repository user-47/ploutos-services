<?php

namespace App\Models;

use App\Traits\UuidModel;
use Exception;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, UuidModel, SoftDeletes;

    protected $uuidPrefix = 'usr';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name', 'last_name', 'username', 'email', 'phone_number', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    ///////////////////
    // RELATIONSHIPS //
    ///////////////////

    /**
     * Get the buy transactions of the user
     */
    public function buyTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'buyer_id');
    }

    /**
     * Get the cards belonging to this user
     */
    public function cards(): HasMany
    {
        return $this->hasMany(Card::class);
    }

    /**
     * Get the invoices belonging to this user
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get the payment methods belonging to this user
     */
    public function paymentMethods(): HasMany
    {
        return $this->hasMany(PaymentMethod::class);
    }

    /**
     * Get the payment providers the user has account with
     */
    public function paymentProviders(): BelongsToMany
    {
        return $this->belongsToMany(PaymentProvider::class)->withPivot(['identifier'])->using(PaymentProviderUser::class)->withTimestamps();
    }

    /**
     * Get the refunds made to this user
     */
    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    /**
     * Get the sell transactions of the user
     */
    public function sellTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'seller_id');
    }

    /**
     * Get the trades for the user.
     */
    public function trades() : HasMany
    {
        return $this->hasMany(Trade::class);
    }

    //////////////
    // MUTATORS //
    //////////////

    /**
     * Gets the user's default payment method
     */
    public function getDefaultPaymentMethodAttribute(): ?PaymentMethod
    {
        return $this->paymentMethods()->default()->latest()->first();
    }

    /**
     * Checks if the user has payment method
     */
    public function getHasPaymentMethodAttribute(): bool
    {
        return $this->paymentMethods()->count() > 0;
    }

    /**
     * Checks if the user has default payment method
     */
    public function getHasDefaultPaymentMethodAttribute(): bool
    {
        return $this->paymentMethods()->default()->count() > 0;
    }

    /**
     * Checks if this user is the current authenticated user
     */
    public function getIsAuthUserAttribute(): bool
    {
        return auth()->user() && auth()->user()->id == $this->id;
    }

    /**
     * Gets collection of all transactions of the user
     */
    public function getTransactionsAttribute(): Collection
    {
        return $this->transactions()->get();
    }

    /**
     * Encrypt password value before saving.
     */
    public function setPasswordAttribute($value): void
    {
        $this->attributes['password'] = Hash::make($value);
    }

    ////////////
    // SCOPES //
    ////////////

    /////////////
    // METHODS //
    /////////////

    /**
     * Returns the user's identifier for the requested provider
     */
    public function getPaymentProviderIdentifier($providerName): ?string
    {
        $provider = PaymentProvider::getPaymentProvider($providerName);
        return optional(optional($this->paymentProviders()->wherePivot('payment_provider_id', $provider->id)->first())->pivot)->identifier;
    }

    /**
     * Returns true if user has open offer for trade
     */
    public function hasOpenOffer(Trade $trade): bool
    {
        return $trade->openOffers()->buyer($this)->count() > 0;
    }

    /**
     * Adds/Updates the user's payment provider identifier
     *
     * @throws Exception
     */
    public function setPaymentProviderIdentifier($providerName, $identifier): void
    {
        if ($this->id) {
            $provider = PaymentProvider::getPaymentProvider($providerName);
            try {
                PaymentProviderUser::updateOrCreate(
                    [
                        'payment_provider_id' => $provider->id,
                        'user_id'             => $this->id,
                    ],
                    [
                        'identifier'          => $identifier,
                    ]
                );
            } catch (Exception $e) {
                if ($e->getCode() == 23000) {
                    // Integrity constraint violation, creating an existing record
                    Log::error("ICV Error setting {$providerName} identifier from " . $this->getPaymentProviderIdentifier($providerName) . " to {$identifier} : " . $e->getMessage());
                } else {
                    throw $e;
                }
            }
        } else {
            throw new Exception("Can't set identifier on model instance that is not saved.");
        }
    }

    /**
     * Get query for all transactions of the user
     */
    public function transactions(): Builder
    {
        return Transaction::where(function($q) {
            $q->buyer($this);
        })->orWhere(function($q) {
            $q->seller($this);
        });
    }
}
