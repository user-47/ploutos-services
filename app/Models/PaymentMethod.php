<?php

namespace App\Models;

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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function paymentProvider(): BelongsTo
    {
        return $this->belongsTo(PaymentProvider::class);
    }

    //////////////
    // MUTATORS //
    //////////////

    public function getIsCardAttribute()
    {
        return $this->type == self::TYPE_CARD;
    }

    public function getPaymentInfoAttribute()
    {
        return strtoupper($this->paymentProvider->name) . ' - ' . strtoupper($this->type);
    }

    ////////////
    // SCOPES //
    ////////////

    public function scopeDefault($query)
    {
        return $query->where('default', 1);
    }

    public function scopeCard($query)
    {
        return $query->where('type', self::TYPE_CARD);
    }

    /////////////
    // METHODS //
    /////////////

    public function getPaymentGateway()
    {
        return PaymentProvider::getPaymentGateway($this->paymentProvider->name);
    }
}
