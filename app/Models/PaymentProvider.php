<?php

namespace App\Models;

use App\Payments\Contracts\PaymentGateway;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PaymentProvider extends Model
{
    protected $fillable = ['name', 'display_name', 'description'];

    // Entries of gateway class path for each provider
    const PROVIDER_GATEWAYS = [];

    // Entries of admin dashboard base url for each provider
    public static function getProviderDashboardUrls()
    {
        return [];
    }

    // Entries of invoice (sub) path for each provider
    public static function getProviderInvoicePaths()
    {
        return [];
    }

    ///////////////////
    // RELATIONSHIPS //
    ///////////////////

    /**
     * Get the users that have accounts with this payment provider
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot(['identifier'])->using(PaymentProviderUser::class)->withTimestamps();
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
     * Scope query to only include payment provider whose name matches given provider name
     * 
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeProvider($query, string $providerName)
    {
        $provider = self::getPaymentProvider($providerName);
        return $query->where('payment_provider_id', $provider->id);
    }

    /////////////
    // METHODS //
    /////////////
    /**
     * Get the local payment provider record for the specified provider
     */
    public static function getPaymentProvider(string $providerName): PaymentProvider
    {
        return self::where('name', $providerName)->firstOrFail();
    }

    /**
     * Returns the instance of the provider's payment gateway class
     *
     * @throws Exception
     */
    public static function getPaymentGateway(string $providerName): PaymentGateway
    {
        if (!static::isValidPaymentGateway($providerName)) {
            throw new Exception("Invalid provider {$providerName}. Unknown implementation.");
        }
        return app(self::PROVIDER_GATEWAYS[$providerName]);
    }

    /**
     * Checks if provider is specified in PROVIDER_GATEWAYS and the class exists
     */
    public static function isValidPaymentGateway($providerName): bool
    {
        if (isset(self::PROVIDER_GATEWAYS[$providerName]) && class_exists(self::PROVIDER_GATEWAYS[$providerName])) {
            return true;
        }
        return false;
    }
}
