<?php

namespace App\Models;

use App\Traits\UuidModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Refund extends Model
{
    use UuidModel, SoftDeletes;

    const STATUS_PENDING = 'pending';
    const STATUS_SUCCEEDED = 'succeeded';
    const STATUS_FAILED = 'failed';

    ///////////////////
    // RELATIONSHIPS //
    ///////////////////

    /**
     * Get the invoice from which this refund is generated
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get the user that owns this refund
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin that initiated this refund
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    /**
     * Get the payment provider used in processing this refund
     */
    public function paymentProvider(): BelongsTo
    {
        return $this->belongsTo(PaymentProvider::class);
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
     * Scope a query to only include successful refunds.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'succeeded');
    }

    /////////////
    // METHODS //
    /////////////
}
